<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/Programa.php';
require_once __DIR__ . '/../model/Aliado.php';

class ProgramaDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los programas con filtros opcionales
     */
    public function getAll($limit = 25, $offset = 0, $filters = [])
    {
        $sql = "SELECT p.*, a.nombre as aliado_nombre
                FROM programas p
                LEFT JOIN aliados a ON p.aliado_id = a.id
                WHERE 1=1";
        $params = [];

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $sql .= " AND (p.nombre LIKE :search OR p.descripcion LIKE :search OR p.responsable_nombre LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['esPropio'])) {
            $sql .= " AND p.es_propio = :esPropio";
            $params[':esPropio'] = $filters['esPropio'] ? 1 : 0;
        }

        if (!empty($filters['aliadoId'])) {
            $sql .= " AND p.aliado_id = :aliadoId";
            $params[':aliadoId'] = $filters['aliadoId'];
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND p.estado = :estado";
            $params[':estado'] = $filters['estado'];
        }

        $sql .= " ORDER BY p.nombre ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        $programas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $programas[] = $this->mapRowToPrograma($row);
        }
        return $programas;
    }

    /**
     * Obtener un programa por ID con datos de aliado
     */
    public function getById($id)
    {
        $sql = "SELECT p.*, a.*,
                       a.id as aliado_id_real, a.nombre as aliado_nombre
                FROM programas p
                LEFT JOIN aliados a ON p.aliado_id = a.id
                WHERE p.id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->mapRowToPrograma($row, true);
        }
        return null;
    }

    /**
     * Obtener programas por aliado
     */
    public function getByAliadoId($aliadoId): array
    {
        $sql = "SELECT * FROM programas WHERE aliado_id = :aliadoId ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':aliadoId', $aliadoId, PDO::PARAM_INT);
        $stmt->execute();

        $programas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $programas[] = $this->mapRowToPrograma($row);
        }
        return $programas;
    }

    /**
     * Obtener solo programas propios de la Corporación
     */
    public function getPropios(): array
    {
        $sql = "SELECT * FROM programas WHERE es_propio = 1 ORDER BY nombre ASC";
        $stmt = $this->conn->query($sql);

        $programas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $programas[] = $this->mapRowToPrograma($row);
        }
        return $programas;
    }

    /**
     * Obtener programas agrupados por aliado
     */
    public function getByAliado(): array
    {
        $sql = "SELECT p.*, a.nombre as aliado_nombre
                FROM programas p
                LEFT JOIN aliados a ON p.aliado_id = a.id
                WHERE p.es_propio = 0
                ORDER BY a.nombre ASC, p.nombre ASC";

        $stmt = $this->conn->query($sql);
        
        $programasPorAliado = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $aliadoNombre = $row['aliado_nombre'] ?? 'Sin Aliado';
            if (!isset($programasPorAliado[$aliadoNombre])) {
                $programasPorAliado[$aliadoNombre] = [];
            }
            $programasPorAliado[$aliadoNombre][] = $this->mapRowToPrograma($row);
        }
        return $programasPorAliado;
    }

    /**
     * Crear un nuevo programa
     */
    public function create(Programa $programa): bool
    {
        // Validar lógica es_propio vs aliado_id
        if ($programa->isEsPropio() && $programa->getAliadoId() !== null) {
            throw new Exception("Un programa propio no puede tener aliado asociado");
        }
        if (!$programa->isEsPropio() && $programa->getAliadoId() === null) {
            throw new Exception("Un programa de aliado debe tener un aliado asociado");
        }

        $sql = "INSERT INTO programas (
            nombre, descripcion, es_propio, aliado_id, responsable_nombre,
            responsable_telefono, responsable_correo, responsable_cargo,
            estado, fecha_inicio, fecha_fin
        ) VALUES (
            :nombre, :descripcion, :esPropio, :aliadoId, :responsableNombre,
            :responsableTelefono, :responsableCorreo, :responsableCargo,
            :estado, :fechaInicio, :fechaFin
        )";

        $stmt = $this->conn->prepare($sql);
        $params = $this->mapParams($programa);

        $result = $stmt->execute($params);

        if ($result) {
            $programa->setId((int) $this->conn->lastInsertId());
        }

        return $result;
    }

    /**
     * Actualizar un programa existente
     */
    public function update(Programa $programa): bool
    {
        // Validar lógica es_propio vs aliado_id
        if ($programa->isEsPropio() && $programa->getAliadoId() !== null) {
            throw new Exception("Un programa propio no puede tener aliado asociado");
        }
        if (!$programa->isEsPropio() && $programa->getAliadoId() === null) {
            throw new Exception("Un programa de aliado debe tener un aliado asociado");
        }

        $sql = "UPDATE programas SET 
            nombre = :nombre,
            descripcion = :descripcion,
            es_propio = :esPropio,
            aliado_id = :aliadoId,
            responsable_nombre = :responsableNombre,
            responsable_telefono = :responsableTelefono,
            responsable_correo = :responsableCorreo,
            responsable_cargo = :responsableCargo,
            estado = :estado,
            fecha_inicio = :fechaInicio,
            fecha_fin = :fechaFin
            WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $params = $this->mapParams($programa);
        $params[':id'] = $programa->getId();

        return $stmt->execute($params);
    }

    /**
     * Eliminar un programa
     */
    public function delete($id): bool
    {
        // Verificar que no tenga madres inscritas activas
        $sqlCheck = "SELECT COUNT(*) as count FROM madres_programas 
                     WHERE programa_id = :id AND estado IN ('inscrita', 'activa')";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($row['count'] > 0) {
            throw new Exception("No se puede eliminar el programa porque tiene madres inscritas activas");
        }

        $sql = "DELETE FROM programas WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Contar total de programas con filtros
     */
    public function countAll($filters = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM programas p WHERE 1=1";
        $params = [];

        // Aplicar mismos filtros que getAll
        if (!empty($filters['search'])) {
            $sql .= " AND (p.nombre LIKE :search OR p.descripcion LIKE :search OR p.responsable_nombre LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['esPropio'])) {
            $sql .= " AND p.es_propio = :esPropio";
            $params[':esPropio'] = $filters['esPropio'] ? 1 : 0;
        }

        if (!empty($filters['aliadoId'])) {
            $sql .= " AND p.aliado_id = :aliadoId";
            $params[':aliadoId'] = $filters['aliadoId'];
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND p.estado = :estado";
            $params[':estado'] = $filters['estado'];
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
     * Obtener solo programas activos para selectores
     */
    public function getActivos(): array
    {
        $sql = "SELECT p.*, a.nombre as aliado_nombre
                FROM programas p
                LEFT JOIN aliados a ON p.aliado_id = a.id
                WHERE p.estado = 'activo'
                ORDER BY p.es_propio DESC, p.nombre ASC";
        $stmt = $this->conn->query($sql);

        $programas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $programas[] = $this->mapRowToPrograma($row);
        }
        return $programas;
    }

    /**
     * Mapear parámetros del objeto Programa a array
     */
    private function mapParams(Programa $programa): array
    {
        return [
            ':nombre' => $programa->getNombre(),
            ':descripcion' => $programa->getDescripcion(),
            ':esPropio' => $programa->isEsPropio() ? 1 : 0,
            ':aliadoId' => $programa->getAliadoId(),
            ':responsableNombre' => $programa->getResponsableNombre(),
            ':responsableTelefono' => $programa->getResponsableTelefono(),
            ':responsableCorreo' => $programa->getResponsableCorreo(),
            ':responsableCargo' => $programa->getResponsableCargo(),
            ':estado' => $programa->getEstado(),
            ':fechaInicio' => $programa->getFechaInicio(),
            ':fechaFin' => $programa->getFechaFin()
        ];
    }

    /**
     * Mapear fila de BD a objeto Programa
     */
    private function mapRowToPrograma($row, $includeAliado = false): Programa
    {
        $aliado = null;
        if ($includeAliado && !empty($row['aliado_id_real'])) {
            $aliado = new Aliado(
                $row['aliado_nombre'],
                (int) $row['aliado_id_real']
            );
        }

        return new Programa(
            $row['nombre'],
            $row['responsable_nombre'],
            (int) $row['id'],
            $row['descripcion'] ?? null,
            (bool) $row['es_propio'],
            isset($row['aliado_id']) ? (int) $row['aliado_id'] : null,
            $aliado,
            $row['responsable_telefono'] ?? null,
            $row['responsable_correo'] ?? null,
            $row['responsable_cargo'] ?? null,
            $row['estado'] ?? 'activo',
            $row['fecha_inicio'] ?? null,
            $row['fecha_fin'] ?? null,
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}

