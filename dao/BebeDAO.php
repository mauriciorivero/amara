<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/Bebe.php';
require_once __DIR__ . '/../model/Embarazo.php';
require_once __DIR__ . '/../model/Madre.php';

class BebeDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los bebés
     */
    public function getAll($limit = 100, $offset = 0)
    {
        $sql = "SELECT b.*, 
                       m.id as madre_id_real, 
                       CONCAT(m.primer_nombre, ' ', IFNULL(m.segundo_nombre, ''), ' ', 
                              m.primer_apellido, ' ', IFNULL(m.segundo_apellido, '')) as madre_nombre,
                       e.id as embarazo_id_real
                FROM bebes b
                INNER JOIN madres m ON b.madre_id = m.id
                INNER JOIN embarazos e ON b.embarazo_id = e.id
                ORDER BY b.fecha_nacimiento DESC, b.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        $bebes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $bebes[] = $this->mapRowToBebe($row);
        }
        return $bebes;
    }

    /**
     * Obtener bebés por madre
     */
    public function getByMadreId($madreId)
    {
        $sql = "SELECT b.*, 
                       m.id as madre_id_real, 
                       CONCAT(m.primer_nombre, ' ', IFNULL(m.segundo_nombre, ''), ' ', 
                              m.primer_apellido, ' ', IFNULL(m.segundo_apellido, '')) as madre_nombre,
                       e.id as embarazo_id_real
                FROM bebes b
                INNER JOIN madres m ON b.madre_id = m.id
                INNER JOIN embarazos e ON b.embarazo_id = e.id
                WHERE b.madre_id = :madreId
                ORDER BY b.fecha_nacimiento DESC, b.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':madreId', $madreId, PDO::PARAM_INT);
        $stmt->execute();

        $bebes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $bebes[] = $this->mapRowToBebe($row);
        }
        return $bebes;
    }

    /**
     * Obtener bebés por embarazo
     */
    public function getByEmbarazoId($embarazoId)
    {
        $sql = "SELECT b.*, 
                       m.id as madre_id_real, 
                       CONCAT(m.primer_nombre, ' ', IFNULL(m.segundo_nombre, ''), ' ', 
                              m.primer_apellido, ' ', IFNULL(m.segundo_apellido, '')) as madre_nombre,
                       e.id as embarazo_id_real
                FROM bebes b
                INNER JOIN madres m ON b.madre_id = m.id
                INNER JOIN embarazos e ON b.embarazo_id = e.id
                WHERE b.embarazo_id = :embarazoId
                ORDER BY b.fecha_nacimiento DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':embarazoId', $embarazoId, PDO::PARAM_INT);
        $stmt->execute();

        $bebes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $bebes[] = $this->mapRowToBebe($row);
        }
        return $bebes;
    }

    /**
     * Obtener un bebé por ID
     */
    public function getById($id)
    {
        $sql = "SELECT b.*, 
                       m.id as madre_id_real, 
                       CONCAT(m.primer_nombre, ' ', IFNULL(m.segundo_nombre, ''), ' ', 
                              m.primer_apellido, ' ', IFNULL(m.segundo_apellido, '')) as madre_nombre,
                       e.id as embarazo_id_real
                FROM bebes b
                INNER JOIN madres m ON b.madre_id = m.id
                INNER JOIN embarazos e ON b.embarazo_id = e.id
                WHERE b.id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->mapRowToBebe($row);
        }
        return null;
    }

    /**
     * Crear un nuevo bebé
     */
    public function create(Bebe $bebe): bool
    {
        $sql = "INSERT INTO bebes (
            embarazo_id, madre_id, nombre, sexo, fecha_nacimiento, es_mellizo, 
            estado, fecha_incidente, observaciones
        ) VALUES (
            :embarazoId, :madreId, :nombre, :sexo, :fechaNacimiento, :esMellizo, 
            :estado, :fechaIncidente, :observaciones
        )";

        $stmt = $this->conn->prepare($sql);
        $params = $this->mapParams($bebe);
        
        $result = $stmt->execute($params);
        
        if ($result) {
            $bebe->setId((int) $this->conn->lastInsertId());
        }
        
        return $result;
    }

    /**
     * Actualizar un bebé existente
     */
    public function update(Bebe $bebe): bool
    {
        $sql = "UPDATE bebes SET 
            embarazo_id = :embarazoId,
            madre_id = :madreId,
            nombre = :nombre,
            sexo = :sexo,
            fecha_nacimiento = :fechaNacimiento,
            es_mellizo = :esMellizo,
            estado = :estado,
            fecha_incidente = :fechaIncidente,
            observaciones = :observaciones
            WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $params = $this->mapParams($bebe);
        $params[':id'] = $bebe->getId();

        return $stmt->execute($params);
    }

    /**
     * Eliminar un bebé
     */
    public function delete($id): bool
    {
        $sql = "DELETE FROM bebes WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Contar total de bebés
     */
    public function countAll(): int
    {
        $sql = "SELECT COUNT(*) as total FROM bebes";
        $stmt = $this->conn->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['total'];
    }

    /**
     * Contar bebés por madre
     */
    public function countByMadreId($madreId): int
    {
        $sql = "SELECT COUNT(*) as total FROM bebes WHERE madre_id = :madreId";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':madreId', $madreId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['total'];
    }

    /**
     * Obtener estadísticas de bebés por estado
     */
    public function getEstadisticasPorEstado()
    {
        $sql = "SELECT estado, COUNT(*) as total 
                FROM bebes 
                GROUP BY estado 
                ORDER BY total DESC";
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mapear parámetros del objeto Bebe a array
     */
    private function mapParams(Bebe $bebe): array
    {
        return [
            ':embarazoId' => $bebe->getEmbarazoId(),
            ':madreId' => $bebe->getMadreId(),
            ':nombre' => $bebe->getNombre(),
            ':sexo' => $bebe->getSexo(),
            ':fechaNacimiento' => $bebe->getFechaNacimiento(),
            ':esMellizo' => $bebe->isEsMellizo() ? 1 : 0,
            ':estado' => $bebe->getEstado(),
            ':fechaIncidente' => $bebe->getFechaIncidente(),
            ':observaciones' => $bebe->getObservaciones()
        ];
    }

    /**
     * Mapear fila de BD a objeto Bebe
     */
    private function mapRowToBebe($row): Bebe
    {
        $bebe = new Bebe(
            (int) $row['embarazo_id'],
            (int) $row['madre_id'],
            (int) $row['id']
        );

        $bebe->setNombre($row['nombre']);
        $bebe->setSexo($row['sexo']);
        $bebe->setFechaNacimiento($row['fecha_nacimiento']);
        $bebe->setEsMellizo((bool) $row['es_mellizo']);
        $bebe->setEstado($row['estado']);
        $bebe->setFechaIncidente($row['fecha_incidente']);
        $bebe->setObservaciones($row['observaciones']);
        $bebe->setCreatedAt($row['created_at']);
        $bebe->setUpdatedAt($row['updated_at']);

        return $bebe;
    }
}

