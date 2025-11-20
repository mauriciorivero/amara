<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/MadrePrograma.php';
require_once __DIR__ . '/../model/Madre.php';
require_once __DIR__ . '/../model/Programa.php';

class MadreProgramaDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todas las inscripciones con filtros opcionales
     */
    public function getAll($limit = 25, $offset = 0, $filters = [])
    {
        $sql = "SELECT mp.*, 
                       m.primer_nombre, m.segundo_nombre, m.primer_apellido, m.segundo_apellido,
                       p.nombre as programa_nombre, p.es_propio
                FROM madres_programas mp
                INNER JOIN madres m ON mp.madre_id = m.id
                INNER JOIN programas p ON mp.programa_id = p.id
                WHERE 1=1";
        $params = [];

        // Aplicar filtros
        if (!empty($filters['madreId'])) {
            $sql .= " AND mp.madre_id = :madreId";
            $params[':madreId'] = $filters['madreId'];
        }

        if (!empty($filters['programaId'])) {
            $sql .= " AND mp.programa_id = :programaId";
            $params[':programaId'] = $filters['programaId'];
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND mp.estado = :estado";
            $params[':estado'] = $filters['estado'];
        }

        $sql .= " ORDER BY mp.fecha_inscripcion DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        $inscripciones = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $inscripciones[] = $this->mapRowToMadrePrograma($row);
        }
        return $inscripciones;
    }

    /**
     * Obtener una inscripción por ID
     */
    public function getById($id)
    {
        $sql = "SELECT mp.*, 
                       m.primer_nombre, m.segundo_nombre, m.primer_apellido, m.segundo_apellido,
                       p.nombre as programa_nombre, p.es_propio, p.descripcion as programa_descripcion
                FROM madres_programas mp
                INNER JOIN madres m ON mp.madre_id = m.id
                INNER JOIN programas p ON mp.programa_id = p.id
                WHERE mp.id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->mapRowToMadrePrograma($row, true);
        }
        return null;
    }

    /**
     * Obtener todos los programas de una madre
     */
    public function getByMadreId($madreId): array
    {
        $sql = "SELECT mp.*, 
                       p.nombre as programa_nombre, p.es_propio, p.descripcion as programa_descripcion,
                       p.estado as programa_estado, p.responsable_nombre,
                       a.nombre as aliado_nombre
                FROM madres_programas mp
                INNER JOIN programas p ON mp.programa_id = p.id
                LEFT JOIN aliados a ON p.aliado_id = a.id
                WHERE mp.madre_id = :madreId
                ORDER BY mp.fecha_inscripcion DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':madreId', $madreId, PDO::PARAM_INT);
        $stmt->execute();

        $inscripciones = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $inscripciones[] = $this->mapRowToMadrePrograma($row, true);
        }
        return $inscripciones;
    }

    /**
     * Obtener todas las madres de un programa
     */
    public function getByProgramaId($programaId): array
    {
        $sql = "SELECT mp.*, 
                       m.primer_nombre, m.segundo_nombre, m.primer_apellido, m.segundo_apellido,
                       m.numero_documento, m.numero_telefono
                FROM madres_programas mp
                INNER JOIN madres m ON mp.madre_id = m.id
                WHERE mp.programa_id = :programaId
                ORDER BY mp.fecha_inscripcion DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':programaId', $programaId, PDO::PARAM_INT);
        $stmt->execute();

        $inscripciones = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $inscripciones[] = $this->mapRowToMadrePrograma($row, true);
        }
        return $inscripciones;
    }

    /**
     * Crear una nueva inscripción
     */
    public function create(MadrePrograma $mp): bool
    {
        // Validar que la madre existe
        $sqlMadre = "SELECT COUNT(*) as count FROM madres WHERE id = :madreId";
        $stmtMadre = $this->conn->prepare($sqlMadre);
        $stmtMadre->bindValue(':madreId', $mp->getMadreId(), PDO::PARAM_INT);
        $stmtMadre->execute();
        $rowMadre = $stmtMadre->fetch(PDO::FETCH_ASSOC);
        
        if ($rowMadre['count'] == 0) {
            throw new Exception("La madre especificada no existe");
        }

        // Validar que el programa existe
        $sqlPrograma = "SELECT COUNT(*) as count FROM programas WHERE id = :programaId";
        $stmtPrograma = $this->conn->prepare($sqlPrograma);
        $stmtPrograma->bindValue(':programaId', $mp->getProgramaId(), PDO::PARAM_INT);
        $stmtPrograma->execute();
        $rowPrograma = $stmtPrograma->fetch(PDO::FETCH_ASSOC);
        
        if ($rowPrograma['count'] == 0) {
            throw new Exception("El programa especificado no existe");
        }

        // Verificar duplicados (aunque existe constraint UNIQUE)
        $sqlDup = "SELECT COUNT(*) as count FROM madres_programas 
                   WHERE madre_id = :madreId AND programa_id = :programaId";
        $stmtDup = $this->conn->prepare($sqlDup);
        $stmtDup->bindValue(':madreId', $mp->getMadreId(), PDO::PARAM_INT);
        $stmtDup->bindValue(':programaId', $mp->getProgramaId(), PDO::PARAM_INT);
        $stmtDup->execute();
        $rowDup = $stmtDup->fetch(PDO::FETCH_ASSOC);
        
        if ($rowDup['count'] > 0) {
            throw new Exception("La madre ya está inscrita en este programa");
        }

        $sql = "INSERT INTO madres_programas (
            madre_id, programa_id, fecha_inscripcion, estado, observaciones_seguimiento
        ) VALUES (
            :madreId, :programaId, :fechaInscripcion, :estado, :observacionesSeguimiento
        )";

        $stmt = $this->conn->prepare($sql);
        $params = $this->mapParams($mp);

        $result = $stmt->execute($params);

        if ($result) {
            $mp->setId((int) $this->conn->lastInsertId());
        }

        return $result;
    }

    /**
     * Actualizar una inscripción existente
     */
    public function update(MadrePrograma $mp): bool
    {
        $sql = "UPDATE madres_programas SET 
            madre_id = :madreId,
            programa_id = :programaId,
            fecha_inscripcion = :fechaInscripcion,
            estado = :estado,
            observaciones_seguimiento = :observacionesSeguimiento
            WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $params = $this->mapParams($mp);
        $params[':id'] = $mp->getId();

        return $stmt->execute($params);
    }

    /**
     * Eliminar una inscripción
     */
    public function delete($id): bool
    {
        $sql = "DELETE FROM madres_programas WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Contar total de inscripciones con filtros
     */
    public function countAll($filters = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM madres_programas mp WHERE 1=1";
        $params = [];

        // Aplicar mismos filtros que getAll
        if (!empty($filters['madreId'])) {
            $sql .= " AND mp.madre_id = :madreId";
            $params[':madreId'] = $filters['madreId'];
        }

        if (!empty($filters['programaId'])) {
            $sql .= " AND mp.programa_id = :programaId";
            $params[':programaId'] = $filters['programaId'];
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND mp.estado = :estado";
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
     * Contar programas de una madre
     */
    public function countByMadre($madreId): int
    {
        $sql = "SELECT COUNT(*) as total FROM madres_programas WHERE madre_id = :madreId";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':madreId', $madreId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['total'];
    }

    /**
     * Contar madres de un programa
     */
    public function countByPrograma($programaId): int
    {
        $sql = "SELECT COUNT(*) as total FROM madres_programas WHERE programa_id = :programaId";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':programaId', $programaId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['total'];
    }

    /**
     * Obtener programas de una madre agrupados por propios/aliados
     */
    public function getProgramasMadreAgrupados($madreId)
    {
        $sql = "SELECT mp.*, 
                       p.nombre as programa_nombre, p.es_propio, p.descripcion as programa_descripcion,
                       p.estado as programa_estado, p.responsable_nombre,
                       a.id as aliado_id, a.nombre as aliado_nombre
                FROM madres_programas mp
                INNER JOIN programas p ON mp.programa_id = p.id
                LEFT JOIN aliados a ON p.aliado_id = a.id
                WHERE mp.madre_id = :madreId
                ORDER BY p.es_propio DESC, a.nombre ASC, p.nombre ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':madreId', $madreId, PDO::PARAM_INT);
        $stmt->execute();

        $propios = [];
        $aliados = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $inscripcion = $this->mapRowToMadrePrograma($row, true);
            
            if ((bool) $row['es_propio']) {
                $propios[] = $inscripcion;
            } else {
                $aliadoNombre = $row['aliado_nombre'] ?? 'Sin Aliado';
                if (!isset($aliados[$aliadoNombre])) {
                    $aliados[$aliadoNombre] = [];
                }
                $aliados[$aliadoNombre][] = $inscripcion;
            }
        }

        return [
            'propios' => $propios,
            'aliados' => $aliados,
            'totalPropios' => count($propios),
            'totalAliados' => array_sum(array_map('count', $aliados))
        ];
    }

    /**
     * Mapear parámetros del objeto MadrePrograma a array
     */
    private function mapParams(MadrePrograma $mp): array
    {
        return [
            ':madreId' => $mp->getMadreId(),
            ':programaId' => $mp->getProgramaId(),
            ':fechaInscripcion' => $mp->getFechaInscripcion(),
            ':estado' => $mp->getEstado(),
            ':observacionesSeguimiento' => $mp->getObservacionesSeguimiento()
        ];
    }

    /**
     * Mapear fila de BD a objeto MadrePrograma
     */
    private function mapRowToMadrePrograma($row, $includeRelations = false): MadrePrograma
    {
        $madre = null;
        $programa = null;

        if ($includeRelations) {
            if (isset($row['primer_nombre'])) {
                $madre = new Madre('0000-00-00', (int) $row['madre_id']);
                $madre->setPrimerNombre($row['primer_nombre'] ?? '');
                $madre->setSegundoNombre($row['segundo_nombre'] ?? null);
                $madre->setPrimerApellido($row['primer_apellido'] ?? '');
                $madre->setSegundoApellido($row['segundo_apellido'] ?? null);
            }

            if (isset($row['programa_nombre'])) {
                $programa = new Programa(
                    $row['programa_nombre'],
                    $row['responsable_nombre'] ?? '',
                    (int) $row['programa_id'],
                    $row['programa_descripcion'] ?? null,
                    (bool) $row['es_propio'],
                    null,
                    null,
                    null,
                    null,
                    null,
                    $row['programa_estado'] ?? 'activo'
                );
            }
        }

        return new MadrePrograma(
            (int) $row['madre_id'],
            (int) $row['programa_id'],
            $row['fecha_inscripcion'],
            (int) $row['id'],
            $madre,
            $programa,
            $row['estado'] ?? 'inscrita',
            $row['observaciones_seguimiento'] ?? null,
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}

