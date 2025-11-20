<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/Ayuda.php';
require_once __DIR__ . '/../model/Madre.php';
require_once __DIR__ . '/../model/Bebe.php';

class AyudaDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todas las ayudas con filtros opcionales
     */
    public function getAll($limit = 100, $offset = 0, $filters = [])
    {
        $sql = "SELECT a.*, 
                       m.id as madre_id_real, m.primer_nombre as madre_primer_nombre, 
                       m.segundo_nombre as madre_segundo_nombre, m.primer_apellido as madre_primer_apellido,
                       m.segundo_apellido as madre_segundo_apellido,
                       b.id as bebe_id_real, b.nombre as bebe_nombre
                FROM ayudas a
                INNER JOIN madres m ON a.madre_id = m.id
                LEFT JOIN bebes b ON a.bebe_id = b.id
                WHERE 1=1";

        $params = [];

        // Aplicar filtros
        if (!empty($filters['madreId'])) {
            $sql .= " AND a.madre_id = :madreId";
            $params[':madreId'] = $filters['madreId'];
        }

        if (!empty($filters['bebeId'])) {
            $sql .= " AND a.bebe_id = :bebeId";
            $params[':bebeId'] = $filters['bebeId'];
        }

        if (!empty($filters['tipoAyuda'])) {
            $sql .= " AND a.tipo_ayuda = :tipoAyuda";
            $params[':tipoAyuda'] = $filters['tipoAyuda'];
        }

        if (!empty($filters['origenAyuda'])) {
            $sql .= " AND a.origen_ayuda = :origenAyuda";
            $params[':origenAyuda'] = $filters['origenAyuda'];
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND a.estado = :estado";
            $params[':estado'] = $filters['estado'];
        }

        if (!empty($filters['fechaDesde'])) {
            $sql .= " AND a.fecha_recepcion >= :fechaDesde";
            $params[':fechaDesde'] = $filters['fechaDesde'];
        }

        if (!empty($filters['fechaHasta'])) {
            $sql .= " AND a.fecha_recepcion <= :fechaHasta";
            $params[':fechaHasta'] = $filters['fechaHasta'];
        }

        $sql .= " ORDER BY a.fecha_recepcion DESC, a.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        $ayudas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ayudas[] = $this->mapRowToAyuda($row);
        }
        return $ayudas;
    }

    /**
     * Obtener una ayuda por ID
     */
    public function getById($id)
    {
        $sql = "SELECT a.*, 
                       m.id as madre_id_real, m.primer_nombre as madre_primer_nombre, 
                       m.segundo_nombre as madre_segundo_nombre, m.primer_apellido as madre_primer_apellido,
                       m.segundo_apellido as madre_segundo_apellido,
                       b.id as bebe_id_real, b.nombre as bebe_nombre
                FROM ayudas a
                INNER JOIN madres m ON a.madre_id = m.id
                LEFT JOIN bebes b ON a.bebe_id = b.id
                WHERE a.id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->mapRowToAyuda($row);
        }
        return null;
    }

    /**
     * Obtener todas las ayudas de una madre
     */
    public function getByMadreId($madreId)
    {
        $sql = "SELECT a.*, 
                       m.id as madre_id_real, m.primer_nombre as madre_primer_nombre, 
                       m.segundo_nombre as madre_segundo_nombre, m.primer_apellido as madre_primer_apellido,
                       m.segundo_apellido as madre_segundo_apellido,
                       b.id as bebe_id_real, b.nombre as bebe_nombre
                FROM ayudas a
                INNER JOIN madres m ON a.madre_id = m.id
                LEFT JOIN bebes b ON a.bebe_id = b.id
                WHERE a.madre_id = :madreId
                ORDER BY a.fecha_recepcion DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':madreId', $madreId, PDO::PARAM_INT);
        $stmt->execute();

        $ayudas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ayudas[] = $this->mapRowToAyuda($row);
        }
        return $ayudas;
    }

    /**
     * Obtener todas las ayudas de un bebé específico
     */
    public function getByBebeId($bebeId)
    {
        $sql = "SELECT a.*, 
                       m.id as madre_id_real, m.primer_nombre as madre_primer_nombre, 
                       m.segundo_nombre as madre_segundo_nombre, m.primer_apellido as madre_primer_apellido,
                       m.segundo_apellido as madre_segundo_apellido,
                       b.id as bebe_id_real, b.nombre as bebe_nombre
                FROM ayudas a
                INNER JOIN madres m ON a.madre_id = m.id
                LEFT JOIN bebes b ON a.bebe_id = b.id
                WHERE a.bebe_id = :bebeId
                ORDER BY a.fecha_recepcion DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':bebeId', $bebeId, PDO::PARAM_INT);
        $stmt->execute();

        $ayudas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ayudas[] = $this->mapRowToAyuda($row);
        }
        return $ayudas;
    }

    /**
     * Crear una nueva ayuda
     */
    public function create(Ayuda $ayuda): bool
    {
        // Validar que si es ayuda de bebé, el bebé pertenece a la madre
        if ($ayuda->getBebeId() !== null) {
            if (!$this->validarBebePerteneceAMadre($ayuda->getBebeId(), $ayuda->getMadreId())) {
                throw new Exception("El bebé especificado no pertenece a la madre indicada");
            }
        }

        $sql = "INSERT INTO ayudas (
            madre_id, bebe_id, tipo_ayuda, origen_ayuda, fecha_recepcion, 
            valor, estado, observaciones
        ) VALUES (
            :madreId, :bebeId, :tipoAyuda, :origenAyuda, :fechaRecepcion, 
            :valor, :estado, :observaciones
        )";

        $stmt = $this->conn->prepare($sql);
        $params = $this->mapParams($ayuda);

        $result = $stmt->execute($params);

        if ($result) {
            $ayuda->setId((int) $this->conn->lastInsertId());
        }

        return $result;
    }

    /**
     * Actualizar una ayuda existente
     */
    public function update(Ayuda $ayuda): bool
    {
        // Validar que si es ayuda de bebé, el bebé pertenece a la madre
        if ($ayuda->getBebeId() !== null) {
            if (!$this->validarBebePerteneceAMadre($ayuda->getBebeId(), $ayuda->getMadreId())) {
                throw new Exception("El bebé especificado no pertenece a la madre indicada");
            }
        }

        $sql = "UPDATE ayudas SET 
            madre_id = :madreId,
            bebe_id = :bebeId,
            tipo_ayuda = :tipoAyuda,
            origen_ayuda = :origenAyuda,
            fecha_recepcion = :fechaRecepcion,
            valor = :valor,
            estado = :estado,
            observaciones = :observaciones
            WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $params = $this->mapParams($ayuda);
        $params[':id'] = $ayuda->getId();

        return $stmt->execute($params);
    }

    /**
     * Eliminar una ayuda
     */
    public function delete($id): bool
    {
        $sql = "DELETE FROM ayudas WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Contar total de ayudas con filtros
     */
    public function countAll($filters = []): int
    {
        $sql = "SELECT COUNT(*) as total 
                FROM ayudas a
                INNER JOIN madres m ON a.madre_id = m.id
                WHERE 1=1";

        $params = [];

        // Aplicar mismos filtros que getAll
        if (!empty($filters['madreId'])) {
            $sql .= " AND a.madre_id = :madreId";
            $params[':madreId'] = $filters['madreId'];
        }

        if (!empty($filters['bebeId'])) {
            $sql .= " AND a.bebe_id = :bebeId";
            $params[':bebeId'] = $filters['bebeId'];
        }

        if (!empty($filters['tipoAyuda'])) {
            $sql .= " AND a.tipo_ayuda = :tipoAyuda";
            $params[':tipoAyuda'] = $filters['tipoAyuda'];
        }

        if (!empty($filters['origenAyuda'])) {
            $sql .= " AND a.origen_ayuda = :origenAyuda";
            $params[':origenAyuda'] = $filters['origenAyuda'];
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND a.estado = :estado";
            $params[':estado'] = $filters['estado'];
        }

        if (!empty($filters['fechaDesde'])) {
            $sql .= " AND a.fecha_recepcion >= :fechaDesde";
            $params[':fechaDesde'] = $filters['fechaDesde'];
        }

        if (!empty($filters['fechaHasta'])) {
            $sql .= " AND a.fecha_recepcion <= :fechaHasta";
            $params[':fechaHasta'] = $filters['fechaHasta'];
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
     * Obtener suma total de valores de ayudas por madre
     */
    public function getTotalValorByMadre($madreId): float
    {
        $sql = "SELECT COALESCE(SUM(valor), 0) as total 
                FROM ayudas 
                WHERE madre_id = :madreId AND estado = 'entregada'";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':madreId', $madreId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) $row['total'];
    }

    /**
     * Obtener estadísticas por tipo de ayuda
     */
    public function getEstadisticasPorTipo()
    {
        $sql = "SELECT tipo_ayuda, COUNT(*) as total, COALESCE(SUM(valor), 0) as valor_total 
                FROM ayudas 
                GROUP BY tipo_ayuda 
                ORDER BY total DESC";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener estadísticas por origen de ayuda
     */
    public function getEstadisticasPorOrigen()
    {
        $sql = "SELECT origen_ayuda, COUNT(*) as total, COALESCE(SUM(valor), 0) as valor_total 
                FROM ayudas 
                GROUP BY origen_ayuda 
                ORDER BY total DESC";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener estadísticas por estado
     */
    public function getEstadisticasPorEstado()
    {
        $sql = "SELECT estado, COUNT(*) as total, COALESCE(SUM(valor), 0) as valor_total 
                FROM ayudas 
                GROUP BY estado 
                ORDER BY total DESC";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener top madres con más ayudas
     */
    public function getTopMadresConMasAyudas($limit = 5)
    {
        $sql = "SELECT m.id, 
                       CONCAT(m.primer_nombre, ' ', IFNULL(m.segundo_nombre, ''), ' ', 
                              m.primer_apellido, ' ', IFNULL(m.segundo_apellido, '')) as nombre_completo,
                       COUNT(a.id) as total_ayudas,
                       COALESCE(SUM(a.valor), 0) as valor_total
                FROM madres m
                INNER JOIN ayudas a ON m.id = a.madre_id
                GROUP BY m.id
                ORDER BY total_ayudas DESC
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener ayudas del mes actual
     */
    public function getAyudasMesActual()
    {
        $sql = "SELECT COUNT(*) as total, COALESCE(SUM(valor), 0) as valor_total
                FROM ayudas 
                WHERE MONTH(fecha_recepcion) = MONTH(CURRENT_DATE())
                  AND YEAR(fecha_recepcion) = YEAR(CURRENT_DATE())";

        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Validar que un bebé pertenece a una madre
     */
    private function validarBebePerteneceAMadre($bebeId, $madreId): bool
    {
        $sql = "SELECT COUNT(*) as existe 
                FROM bebes 
                WHERE id = :bebeId AND madre_id = :madreId";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':bebeId', $bebeId, PDO::PARAM_INT);
        $stmt->bindParam(':madreId', $madreId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['existe'] > 0;
    }

    /**
     * Mapear parámetros del objeto Ayuda a array
     */
    private function mapParams(Ayuda $ayuda): array
    {
        return [
            ':madreId' => $ayuda->getMadreId(),
            ':bebeId' => $ayuda->getBebeId(),
            ':tipoAyuda' => $ayuda->getTipoAyuda(),
            ':origenAyuda' => $ayuda->getOrigenAyuda(),
            ':fechaRecepcion' => $ayuda->getFechaRecepcion(),
            ':valor' => $ayuda->getValor(),
            ':estado' => $ayuda->getEstado(),
            ':observaciones' => $ayuda->getObservaciones()
        ];
    }

    /**
     * Mapear fila de BD a objeto Ayuda
     */
    private function mapRowToAyuda($row): Ayuda
    {
        // Crear objeto Madre
        $madre = new Madre('0000-00-00', (int) $row['madre_id_real']);
        if (isset($row['madre_primer_nombre'])) {
            $madre->setPrimerNombre($row['madre_primer_nombre']);
        }
        if (isset($row['madre_segundo_nombre'])) {
            $madre->setSegundoNombre($row['madre_segundo_nombre']);
        }
        if (isset($row['madre_primer_apellido'])) {
            $madre->setPrimerApellido($row['madre_primer_apellido']);
        }
        if (isset($row['madre_segundo_apellido'])) {
            $madre->setSegundoApellido($row['madre_segundo_apellido']);
        }

        // Crear objeto Bebe si existe
        $bebe = null;
        if (!empty($row['bebe_id_real'])) {
            $bebe = new Bebe(0, (int) $row['madre_id_real'], (int) $row['bebe_id_real']);
            if (isset($row['bebe_nombre'])) {
                $bebe->setNombre($row['bebe_nombre']);
            }
        }

        // Crear objeto Ayuda
        $ayuda = new Ayuda(
            (int) $row['madre_id'],
            $row['tipo_ayuda'],
            $row['fecha_recepcion'],
            (int) $row['id'],
            $madre,
            !empty($row['bebe_id']) ? (int) $row['bebe_id'] : null,
            $bebe,
            $row['origen_ayuda'],
            $row['valor'] !== null ? (float) $row['valor'] : 0.0,
            $row['estado'],
            $row['observaciones'],
            $row['created_at'],
            $row['updated_at']
        );

        return $ayuda;
    }
}

