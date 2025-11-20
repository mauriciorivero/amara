<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/Embarazo.php';
require_once __DIR__ . '/../model/Madre.php';

class EmbarazoDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los embarazos
     */
    public function getAll($limit = 100, $offset = 0, $search = '')
    {
        $sql = "SELECT e.*, 
                       m.id as madre_id_real, m.primer_nombre, m.segundo_nombre, m.primer_apellido, m.segundo_apellido,
                       o.id as orientadora_id, o.nombre as orientadora_nombre
                FROM embarazos e
                INNER JOIN madres m ON e.madre_id = m.id
                LEFT JOIN orientadoras o ON m.orientadora_id = o.id";

        // Agregar filtro de búsqueda si existe
        if (!empty($search)) {
            $sql .= " WHERE (m.primer_nombre LIKE :search 
                      OR m.primer_apellido LIKE :search 
                      OR o.nombre LIKE :search)";
        }

        $sql .= " ORDER BY e.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        
        if (!empty($search)) {
            $searchParam = '%' . $search . '%';
            $stmt->bindValue(':search', $searchParam, PDO::PARAM_STR);
        }
        
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        $embarazos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $embarazos[] = $this->mapRowToEmbarazo($row);
        }
        return $embarazos;
    }

    /**
     * Obtener embarazos por madre
     */
    public function getByMadreId($madreId)
    {
        $sql = "SELECT e.*, 
                       m.id as madre_id_real, 
                       CONCAT(m.primer_nombre, ' ', IFNULL(m.segundo_nombre, ''), ' ', 
                              m.primer_apellido, ' ', IFNULL(m.segundo_apellido, '')) as madre_nombre
                FROM embarazos e
                INNER JOIN madres m ON e.madre_id = m.id
                WHERE e.madre_id = :madreId
                ORDER BY e.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':madreId', $madreId, PDO::PARAM_INT);
        $stmt->execute();

        $embarazos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $embarazos[] = $this->mapRowToEmbarazo($row);
        }
        return $embarazos;
    }

    /**
     * Obtener embarazos activos (con bebés por nacer)
     */
    public function getEmbarazosActivos($madreId = null)
    {
        $sql = "SELECT e.*, 
                       m.id as madre_id_real, 
                       CONCAT(m.primer_nombre, ' ', IFNULL(m.segundo_nombre, ''), ' ', 
                              m.primer_apellido, ' ', IFNULL(m.segundo_apellido, '')) as madre_nombre
                FROM embarazos e
                INNER JOIN madres m ON e.madre_id = m.id
                WHERE e.total_bebes_por_nacer > 0";

        if ($madreId !== null) {
            $sql .= " AND e.madre_id = :madreId";
        }

        $sql .= " ORDER BY e.created_at DESC";

        $stmt = $this->conn->prepare($sql);

        if ($madreId !== null) {
            $stmt->bindParam(':madreId', $madreId, PDO::PARAM_INT);
        }

        $stmt->execute();

        $embarazos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $embarazos[] = $this->mapRowToEmbarazo($row);
        }
        return $embarazos;
    }

    /**
     * Obtener un embarazo por ID
     */
    public function getById($id)
    {
        $sql = "SELECT e.*, 
                       m.id as madre_id_real, 
                       CONCAT(m.primer_nombre, ' ', IFNULL(m.segundo_nombre, ''), ' ', 
                              m.primer_apellido, ' ', IFNULL(m.segundo_apellido, '')) as madre_nombre
                FROM embarazos e
                INNER JOIN madres m ON e.madre_id = m.id
                WHERE e.id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->mapRowToEmbarazo($row);
        }
        return null;
    }

    /**
     * Obtener embarazo con sus bebés
     */
    public function getEmbarazoConBebes($embarazoId)
    {
        require_once __DIR__ . '/BebeDAO.php';

        $embarazo = $this->getById($embarazoId);
        if (!$embarazo) {
            return null;
        }

        $bebeDAO = new BebeDAO();
        $bebes = $bebeDAO->getByEmbarazoId($embarazoId);

        return [
            'embarazo' => $embarazo,
            'bebes' => $bebes
        ];
    }

    /**
     * Crear un nuevo embarazo
     */
    public function create(Embarazo $embarazo): bool
    {
        $sql = "INSERT INTO embarazos (
            madre_id, total_bebes_nacidos, total_bebes_por_nacer, 
            bebes_no_nacidos, bebes_fallecidos, es_multiple
        ) VALUES (
            :madreId, :totalBebesNacidos, :totalBebesPorNacer, 
            :bebesNoNacidos, :bebesFallecidos, :esMultiple
        )";

        $stmt = $this->conn->prepare($sql);
        $params = $this->mapParams($embarazo);

        $result = $stmt->execute($params);

        if ($result) {
            $embarazo->setId((int) $this->conn->lastInsertId());
        }

        return $result;
    }

    /**
     * Actualizar un embarazo existente
     */
    public function update(Embarazo $embarazo): bool
    {
        $sql = "UPDATE embarazos SET 
            madre_id = :madreId,
            total_bebes_nacidos = :totalBebesNacidos,
            total_bebes_por_nacer = :totalBebesPorNacer,
            bebes_no_nacidos = :bebesNoNacidos,
            bebes_fallecidos = :bebesFallecidos,
            es_multiple = :esMultiple
            WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $params = $this->mapParams($embarazo);
        $params[':id'] = $embarazo->getId();

        return $stmt->execute($params);
    }

    /**
     * Actualizar contadores automáticamente basado en bebés registrados
     */
    public function actualizarContadores($embarazoId): bool
    {
        $sql = "UPDATE embarazos e
                SET 
                    total_bebes_nacidos = (
                        SELECT COUNT(*) FROM bebes 
                        WHERE embarazo_id = :embarazoId1 
                        AND estado = 'Nacido'
                    ),
                    total_bebes_por_nacer = (
                        SELECT COUNT(*) FROM bebes 
                        WHERE embarazo_id = :embarazoId2 
                        AND estado = 'Por nacer'
                    ),
                    bebes_no_nacidos = (
                        SELECT COUNT(*) FROM bebes 
                        WHERE embarazo_id = :embarazoId3 
                        AND estado IN ('Aborto', 'Muerte gestacional')
                    ),
                    bebes_fallecidos = (
                        SELECT COUNT(*) FROM bebes 
                        WHERE embarazo_id = :embarazoId4 
                        AND estado = 'Fallecido'
                    ),
                    es_multiple = (
                        SELECT CASE WHEN COUNT(*) > 1 THEN 1 ELSE 0 END 
                        FROM bebes 
                        WHERE embarazo_id = :embarazoId5
                    )
                WHERE id = :embarazoId6";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':embarazoId1', $embarazoId, PDO::PARAM_INT);
        $stmt->bindValue(':embarazoId2', $embarazoId, PDO::PARAM_INT);
        $stmt->bindValue(':embarazoId3', $embarazoId, PDO::PARAM_INT);
        $stmt->bindValue(':embarazoId4', $embarazoId, PDO::PARAM_INT);
        $stmt->bindValue(':embarazoId5', $embarazoId, PDO::PARAM_INT);
        $stmt->bindValue(':embarazoId6', $embarazoId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Eliminar un embarazo (también elimina bebés por CASCADE)
     */
    public function delete($id): bool
    {
        $sql = "DELETE FROM embarazos WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Contar total de embarazos
     */
    public function countAll($search = ''): int
    {
        $sql = "SELECT COUNT(*) as total 
                FROM embarazos e
                INNER JOIN madres m ON e.madre_id = m.id
                LEFT JOIN orientadoras o ON m.orientadora_id = o.id";
        
        // Agregar filtro de búsqueda si existe
        if (!empty($search)) {
            $sql .= " WHERE (m.primer_nombre LIKE :search 
                      OR m.primer_apellido LIKE :search 
                      OR o.nombre LIKE :search)";
        }
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($search)) {
            $searchParam = '%' . $search . '%';
            $stmt->bindValue(':search', $searchParam, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['total'];
    }

    /**
     * Contar embarazos por madre
     */
    public function countByMadreId($madreId): int
    {
        $sql = "SELECT COUNT(*) as total FROM embarazos WHERE madre_id = :madreId";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':madreId', $madreId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['total'];
    }

    /**
     * Obtener estadísticas de embarazos
     */
    public function getEstadisticas()
    {
        $sql = "SELECT 
                    COUNT(*) as total_embarazos,
                    SUM(total_bebes_nacidos) as total_bebes_nacidos,
                    SUM(total_bebes_por_nacer) as total_bebes_por_nacer,
                    SUM(bebes_no_nacidos) as total_bebes_no_nacidos,
                    SUM(bebes_fallecidos) as total_bebes_fallecidos,
                    SUM(CASE WHEN es_multiple = 1 THEN 1 ELSE 0 END) as embarazos_multiples
                FROM embarazos";

        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mapear parámetros del objeto Embarazo a array
     */
    private function mapParams(Embarazo $embarazo): array
    {
        return [
            ':madreId' => $embarazo->getMadreId(),
            ':totalBebesNacidos' => $embarazo->getTotalBebesNacidos(),
            ':totalBebesPorNacer' => $embarazo->getTotalBebesPorNacer(),
            ':bebesNoNacidos' => $embarazo->getBebesNoNacidos(),
            ':bebesFallecidos' => $embarazo->getBebesFallecidos(),
            ':esMultiple' => $embarazo->isEsMultiple() ? 1 : 0
        ];
    }

    /**
     * Mapear fila de BD a objeto Embarazo
     */
    private function mapRowToEmbarazo($row): Embarazo
    {
        $embarazo = new Embarazo(
            (int) $row['madre_id'],
            (int) $row['id']
        );

        $embarazo->setTotalBebesNacidos((int) $row['total_bebes_nacidos']);
        $embarazo->setTotalBebesPorNacer((int) $row['total_bebes_por_nacer']);
        $embarazo->setBebesNoNacidos((int) $row['bebes_no_nacidos']);
        $embarazo->setBebesFallecidos((int) $row['bebes_fallecidos']);
        $embarazo->setEsMultiple((bool) $row['es_multiple']);
        $embarazo->setCreatedAt($row['created_at']);
        $embarazo->setUpdatedAt($row['updated_at']);

        // Map Madre info if available
        if (isset($row['madre_id_real'])) {
            $madre = new Madre('0000-00-00', (int) $row['madre_id_real']);
            if (isset($row['primer_nombre']))
                $madre->setPrimerNombre($row['primer_nombre']);
            if (isset($row['segundo_nombre']))
                $madre->setSegundoNombre($row['segundo_nombre']);
            if (isset($row['primer_apellido']))
                $madre->setPrimerApellido($row['primer_apellido']);
            if (isset($row['segundo_apellido']))
                $madre->setSegundoApellido($row['segundo_apellido']);

            // Map Orientadora if available
            if (!empty($row['orientadora_id'])) {
                $orientadora = new Orientadora($row['orientadora_nombre'], (int) $row['orientadora_id']);
                $madre->setOrientadora($orientadora);
            }

            $embarazo->setMadre($madre);
        }

        return $embarazo;
    }
}

