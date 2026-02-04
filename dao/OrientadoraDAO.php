<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/Orientadora.php';

class OrientadoraDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todas las orientadoras con filtros opcionales
     */
    public function getAll($limit = 25, $offset = 0, $filters = []): array
    {
        $sql = "SELECT * FROM orientadoras WHERE 1=1";
        $params = [];

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $sql .= " AND nombre LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['activa']) && $filters['activa'] !== '') {
            $sql .= " AND activa = :activa";
            $params[':activa'] = ($filters['activa'] === 'activa' || $filters['activa'] === '1' || $filters['activa'] === 1) ? 1 : 0;
        }

        $sql .= " ORDER BY nombre ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        $lista = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lista[] = $this->mapRowToOrientadora($row);
        }
        return $lista;
    }

    /**
     * Obtener una orientadora por ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM orientadoras WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->mapRowToOrientadora($row);
        }
        return null;
    }

    /**
     * Crear una nueva orientadora
     */
    public function create(Orientadora $orientadora): bool
    {
        // Validar que el nombre no esté vacío
        if (empty(trim($orientadora->getNombre()))) {
            throw new Exception("El nombre es requerido");
        }

        $sql = "INSERT INTO orientadoras (nombre, activa) VALUES (:nombre, :activa)";

        $stmt = $this->conn->prepare($sql);
        $params = $this->mapParams($orientadora);

        $result = $stmt->execute($params);

        if ($result) {
            $orientadora->setId((int) $this->conn->lastInsertId());
        }

        return $result;
    }

    /**
     * Actualizar una orientadora existente
     */
    public function update(Orientadora $orientadora): bool
    {
        // Validar que el nombre no esté vacío
        if (empty(trim($orientadora->getNombre()))) {
            throw new Exception("El nombre es requerido");
        }

        $sql = "UPDATE orientadoras SET
            nombre = :nombre,
            activa = :activa
            WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $params = $this->mapParams($orientadora);
        $params[':id'] = $orientadora->getId();

        return $stmt->execute($params);
    }

    /**
     * Eliminar una orientadora
     */
    public function delete($id): bool
    {
        // Verificar que no tenga madres asignadas
        $sqlCheck = "SELECT COUNT(*) as count FROM madres WHERE orientadora_id = :id";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($row['count'] > 0) {
            throw new Exception("No se puede eliminar la orientadora porque tiene " . $row['count'] . " madres asignadas");
        }

        $sql = "DELETE FROM orientadoras WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Contar total de orientadoras con filtros
     */
    public function countAll($filters = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM orientadoras WHERE 1=1";
        $params = [];

        // Aplicar mismos filtros que getAll
        if (!empty($filters['search'])) {
            $sql .= " AND nombre LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['activa']) && $filters['activa'] !== '') {
            $sql .= " AND activa = :activa";
            $params[':activa'] = ($filters['activa'] === 'activa' || $filters['activa'] === '1' || $filters['activa'] === 1) ? 1 : 0;
        }

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['total'];
    }

    /**
     * Obtener solo orientadoras activas para selectores
     */
    public function getActivos(): array
    {
        $sql = "SELECT * FROM orientadoras WHERE activa = 1 ORDER BY nombre ASC";
        $stmt = $this->conn->query($sql);

        $lista = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lista[] = $this->mapRowToOrientadora($row);
        }
        return $lista;
    }

    /**
     * Obtener estadísticas generales
     */
    public function getEstadisticas(): array
    {
        // Total de orientadoras
        $sqlTotal = "SELECT COUNT(*) as total FROM orientadoras";
        $stmtTotal = $this->conn->query($sqlTotal);
        $total = (int) $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

        // Orientadoras activas
        $sqlActivas = "SELECT COUNT(*) as activas FROM orientadoras WHERE activa = 1";
        $stmtActivas = $this->conn->query($sqlActivas);
        $activas = (int) $stmtActivas->fetch(PDO::FETCH_ASSOC)['activas'];

        // Orientadoras inactivas
        $sqlInactivas = "SELECT COUNT(*) as inactivas FROM orientadoras WHERE activa = 0";
        $stmtInactivas = $this->conn->query($sqlInactivas);
        $inactivas = (int) $stmtInactivas->fetch(PDO::FETCH_ASSOC)['inactivas'];

        // Madres atendidas (con orientadora asignada)
        $sqlMadres = "SELECT COUNT(DISTINCT id) as madres_atendidas
                      FROM madres
                      WHERE orientadora_id IS NOT NULL";
        $stmtMadres = $this->conn->query($sqlMadres);
        $madresAtendidas = (int) $stmtMadres->fetch(PDO::FETCH_ASSOC)['madres_atendidas'];

        return [
            'total' => $total,
            'activas' => $activas,
            'inactivas' => $inactivas,
            'madresAtendidas' => $madresAtendidas
        ];
    }

    /**
     * Mapear parámetros del objeto Orientadora a array
     */
    private function mapParams(Orientadora $orientadora): array
    {
        return [
            ':nombre' => $orientadora->getNombre(),
            ':activa' => $orientadora->isActiva() ? 1 : 0
        ];
    }

    /**
     * Mapear fila de BD a objeto Orientadora
     */
    private function mapRowToOrientadora($row): Orientadora
    {
        return new Orientadora(
            $row['nombre'],
            (int) $row['id'],
            (bool) $row['activa'],
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }

    // ========================================
    // MÉTODOS PARA HISTORIAL DE ASIGNACIONES
    // ========================================

    /**
     * Obtener historial de madres asignadas a una orientadora
     */
    public function getHistorialMadres($orientadoraId, $soloActivas = false): array
    {
        $sql = "SELECT om.*,
                       m.primer_nombre, m.segundo_nombre, m.primer_apellido, m.segundo_apellido,
                       m.numero_documento, m.numero_telefono, m.fecha_ingreso
                FROM orientadoras_madres om
                INNER JOIN madres m ON om.madre_id = m.id
                WHERE om.orientadora_id = :orientadoraId";

        if ($soloActivas) {
            $sql .= " AND om.activa = 1";
        }

        $sql .= " ORDER BY om.fecha_asignacion DESC, om.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':orientadoraId', $orientadoraId, PDO::PARAM_INT);
        $stmt->execute();

        $historial = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $historial[] = $this->mapRowToOrientadoraMadre($row, true);
        }
        return $historial;
    }

    /**
     * Obtener historial de orientadoras de una madre
     */
    public function getHistorialPorMadre($madreId): array
    {
        $sql = "SELECT om.*,
                       o.nombre as orientadora_nombre, o.activa as orientadora_activa
                FROM orientadoras_madres om
                INNER JOIN orientadoras o ON om.orientadora_id = o.id
                WHERE om.madre_id = :madreId
                ORDER BY om.fecha_asignacion DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':madreId', $madreId, PDO::PARAM_INT);
        $stmt->execute();

        $historial = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $historial[] = $this->mapRowToOrientadoraMadre($row, true);
        }
        return $historial;
    }

    /**
     * Crear una nueva asignación y sincronizar con madres.orientadora_id
     */
    public function createAsignacion(
        int $orientadoraId,
        int $madreId,
        string $fechaAsignacion,
        ?string $motivoCambio = null,
        ?string $observaciones = null
    ): bool {
        try {
            $this->conn->beginTransaction();

            // 1. Desactivar asignación anterior si existe
            $sqlDesactivar = "UPDATE orientadoras_madres
                             SET activa = 0, fecha_fin = :fechaFin
                             WHERE madre_id = :madreId AND activa = 1";
            $stmtDesactivar = $this->conn->prepare($sqlDesactivar);
            $stmtDesactivar->bindValue(':fechaFin', $fechaAsignacion);
            $stmtDesactivar->bindValue(':madreId', $madreId, PDO::PARAM_INT);
            $stmtDesactivar->execute();

            // 2. Crear nueva asignación en historial
            $sqlInsert = "INSERT INTO orientadoras_madres
                         (orientadora_id, madre_id, fecha_asignacion, activa, motivo_cambio, observaciones)
                         VALUES (:orientadoraId, :madreId, :fechaAsignacion, 1, :motivoCambio, :observaciones)";
            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->bindValue(':orientadoraId', $orientadoraId, PDO::PARAM_INT);
            $stmtInsert->bindValue(':madreId', $madreId, PDO::PARAM_INT);
            $stmtInsert->bindValue(':fechaAsignacion', $fechaAsignacion);
            $stmtInsert->bindValue(':motivoCambio', $motivoCambio);
            $stmtInsert->bindValue(':observaciones', $observaciones);
            $stmtInsert->execute();

            // 3. Actualizar madres.orientadora_id (campo actual)
            $sqlUpdateMadre = "UPDATE madres SET orientadora_id = :orientadoraId WHERE id = :madreId";
            $stmtUpdateMadre = $this->conn->prepare($sqlUpdateMadre);
            $stmtUpdateMadre->bindValue(':orientadoraId', $orientadoraId, PDO::PARAM_INT);
            $stmtUpdateMadre->bindValue(':madreId', $madreId, PDO::PARAM_INT);
            $stmtUpdateMadre->execute();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw new Exception("Error creando asignación: " . $e->getMessage());
        }
    }

    /**
     * Desasignar una madre de su orientadora actual
     */
    public function desasignarMadre(int $madreId, string $fechaFin, ?string $motivoCambio = null): bool
    {
        try {
            $this->conn->beginTransaction();

            // 1. Desactivar asignación en historial
            $sql = "UPDATE orientadoras_madres
                    SET activa = 0, fecha_fin = :fechaFin, motivo_cambio = :motivoCambio
                    WHERE madre_id = :madreId AND activa = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':fechaFin', $fechaFin);
            $stmt->bindValue(':motivoCambio', $motivoCambio);
            $stmt->bindValue(':madreId', $madreId, PDO::PARAM_INT);
            $stmt->execute();

            // 2. Limpiar madres.orientadora_id
            $sqlMadre = "UPDATE madres SET orientadora_id = NULL WHERE id = :madreId";
            $stmtMadre = $this->conn->prepare($sqlMadre);
            $stmtMadre->bindValue(':madreId', $madreId, PDO::PARAM_INT);
            $stmtMadre->execute();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw new Exception("Error desasignando madre: " . $e->getMessage());
        }
    }

    /**
     * Contar madres activamente asignadas a una orientadora
     */
    public function countMadresActivas($orientadoraId): int
    {
        $sql = "SELECT COUNT(*) as total
                FROM orientadoras_madres
                WHERE orientadora_id = :orientadoraId AND activa = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':orientadoraId', $orientadoraId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['total'];
    }

    /**
     * Contar total de madres en historial (incluyendo inactivas)
     */
    public function countMadresTotales($orientadoraId): int
    {
        $sql = "SELECT COUNT(DISTINCT madre_id) as total
                FROM orientadoras_madres
                WHERE orientadora_id = :orientadoraId";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':orientadoraId', $orientadoraId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['total'];
    }

    /**
     * Mapear fila de BD a objeto OrientadoraMadre
     */
    private function mapRowToOrientadoraMadre($row, $includeRelations = false): OrientadoraMadre
    {
        require_once __DIR__ . '/../model/OrientadoraMadre.php';

        $madre = null;
        $orientadora = null;

        if ($includeRelations) {
            if (isset($row['primer_nombre'])) {
                // Construir objeto Madre con información básica
                $madre = new Madre($row['fecha_ingreso'] ?? '0000-00-00', (int) $row['madre_id']);
                $madre->setPrimerNombre($row['primer_nombre'] ?? '');
                $madre->setSegundoNombre($row['segundo_nombre'] ?? null);
                $madre->setPrimerApellido($row['primer_apellido'] ?? '');
                $madre->setSegundoApellido($row['segundo_apellido'] ?? null);
                $madre->setNumeroDocumento($row['numero_documento'] ?? null);
                $madre->setNumeroTelefono($row['numero_telefono'] ?? null);
            }

            if (isset($row['orientadora_nombre'])) {
                // Construir objeto Orientadora con información básica
                $orientadora = new Orientadora(
                    $row['orientadora_nombre'],
                    (int) $row['orientadora_id'],
                    (bool) ($row['orientadora_activa'] ?? true)
                );
            }
        }

        return new OrientadoraMadre(
            (int) $row['orientadora_id'],
            (int) $row['madre_id'],
            $row['fecha_asignacion'],
            (int) $row['id'],
            $orientadora,
            $madre,
            $row['fecha_fin'] ?? null,
            (bool) $row['activa'],
            $row['motivo_cambio'] ?? null,
            $row['observaciones'] ?? null,
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}
