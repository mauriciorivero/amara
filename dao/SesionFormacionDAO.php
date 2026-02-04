<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/SesionFormacion.php';

class SesionFormacionDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todas las sesiones con filtros opcionales
     */
    public function getAll($limit = 25, $offset = 0, $filters = [])
    {
        $sql = "SELECT sf.*, p.nombre as programa_nombre
                FROM sesiones_formacion sf
                INNER JOIN programas p ON sf.programa_id = p.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['programaId'])) {
            $sql .= " AND sf.programa_id = :programaId";
            $params[':programaId'] = $filters['programaId'];
        }

        if (!empty($filters['tipoSesion'])) {
            $sql .= " AND sf.tipo_sesion = :tipoSesion";
            $params[':tipoSesion'] = $filters['tipoSesion'];
        }

        if (!empty($filters['fechaDesde'])) {
            $sql .= " AND sf.fecha_sesion >= :fechaDesde";
            $params[':fechaDesde'] = $filters['fechaDesde'];
        }

        if (!empty($filters['fechaHasta'])) {
            $sql .= " AND sf.fecha_sesion <= :fechaHasta";
            $params[':fechaHasta'] = $filters['fechaHasta'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (sf.responsables LIKE :search OR sf.temas_tratados LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY sf.fecha_sesion DESC, sf.id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        $sesiones = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sesion = $this->mapRowToSesion($row);
            $sesion->setMadresAsistentes($this->getAsistentesBySesionId($row['id']));
            $sesiones[] = $sesion;
        }
        return $sesiones;
    }

    /**
     * Obtener una sesión por ID
     */
    public function getById($id)
    {
        $sql = "SELECT sf.*, p.nombre as programa_nombre
                FROM sesiones_formacion sf
                INNER JOIN programas p ON sf.programa_id = p.id
                WHERE sf.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $sesion = $this->mapRowToSesion($row);
            $sesion->setMadresAsistentes($this->getAsistentesBySesionId($id));
            return $sesion;
        }
        return null;
    }

    /**
     * Obtener sesiones por programa
     */
    public function getByProgramaId($programaId): array
    {
        $sql = "SELECT sf.*, p.nombre as programa_nombre
                FROM sesiones_formacion sf
                INNER JOIN programas p ON sf.programa_id = p.id
                WHERE sf.programa_id = :programaId
                ORDER BY sf.fecha_sesion DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':programaId', $programaId, PDO::PARAM_INT);
        $stmt->execute();

        $sesiones = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sesion = $this->mapRowToSesion($row);
            $sesion->setMadresAsistentes($this->getAsistentesBySesionId($row['id']));
            $sesiones[] = $sesion;
        }
        return $sesiones;
    }

    /**
     * Obtener asistentes de una sesión
     */
    public function getAsistentesBySesionId($sesionId): array
    {
        $sql = "SELECT m.id, 
                       CONCAT(m.primer_nombre, ' ', COALESCE(m.segundo_nombre, ''), ' ', m.primer_apellido, ' ', COALESCE(m.segundo_apellido, '')) as nombre,
                       m.numero_documento as documento, 
                       sfa.asistio
                FROM sesiones_formacion_asistencia sfa
                INNER JOIN madres m ON sfa.madre_id = m.id
                WHERE sfa.sesion_id = :sesionId
                ORDER BY m.primer_nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':sesionId', $sesionId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crear una nueva sesión de formación
     */
    public function create(SesionFormacion $sesion): bool
    {
        $this->conn->beginTransaction();
        
        try {
            $sql = "INSERT INTO sesiones_formacion (
                programa_id, tipo_sesion, fecha_sesion, responsables, temas_tratados, observaciones
            ) VALUES (
                :programaId, :tipoSesion, :fechaSesion, :responsables, :temasTratados, :observaciones
            )";

            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ':programaId' => $sesion->getProgramaId(),
                ':tipoSesion' => $sesion->getTipoSesion(),
                ':fechaSesion' => $sesion->getFechaSesion(),
                ':responsables' => $sesion->getResponsables(),
                ':temasTratados' => $sesion->getTemasTratados(),
                ':observaciones' => $sesion->getObservaciones()
            ]);

            if ($result) {
                $sesionId = (int) $this->conn->lastInsertId();
                $sesion->setId($sesionId);

                if ($sesion->getMadresAsistentes() && count($sesion->getMadresAsistentes()) > 0) {
                    $this->registrarAsistentes($sesionId, $sesion->getMadresAsistentes());
                }

                $this->conn->commit();
                return true;
            }

            $this->conn->rollBack();
            return false;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Actualizar una sesión existente
     */
    public function update(SesionFormacion $sesion): bool
    {
        $this->conn->beginTransaction();
        
        try {
            $sql = "UPDATE sesiones_formacion SET 
                programa_id = :programaId,
                tipo_sesion = :tipoSesion,
                fecha_sesion = :fechaSesion,
                responsables = :responsables,
                temas_tratados = :temasTratados,
                observaciones = :observaciones
                WHERE id = :id";

            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ':programaId' => $sesion->getProgramaId(),
                ':tipoSesion' => $sesion->getTipoSesion(),
                ':fechaSesion' => $sesion->getFechaSesion(),
                ':responsables' => $sesion->getResponsables(),
                ':temasTratados' => $sesion->getTemasTratados(),
                ':observaciones' => $sesion->getObservaciones(),
                ':id' => $sesion->getId()
            ]);

            if ($result) {
                $this->eliminarAsistentes($sesion->getId());
                if ($sesion->getMadresAsistentes() && count($sesion->getMadresAsistentes()) > 0) {
                    $this->registrarAsistentes($sesion->getId(), $sesion->getMadresAsistentes());
                }

                $this->conn->commit();
                return true;
            }

            $this->conn->rollBack();
            return false;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Eliminar una sesión
     */
    public function delete($id): bool
    {
        $sql = "DELETE FROM sesiones_formacion WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Contar total de sesiones con filtros
     */
    public function countAll($filters = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM sesiones_formacion sf WHERE 1=1";
        $params = [];

        if (!empty($filters['programaId'])) {
            $sql .= " AND sf.programa_id = :programaId";
            $params[':programaId'] = $filters['programaId'];
        }

        if (!empty($filters['tipoSesion'])) {
            $sql .= " AND sf.tipo_sesion = :tipoSesion";
            $params[':tipoSesion'] = $filters['tipoSesion'];
        }

        if (!empty($filters['fechaDesde'])) {
            $sql .= " AND sf.fecha_sesion >= :fechaDesde";
            $params[':fechaDesde'] = $filters['fechaDesde'];
        }

        if (!empty($filters['fechaHasta'])) {
            $sql .= " AND sf.fecha_sesion <= :fechaHasta";
            $params[':fechaHasta'] = $filters['fechaHasta'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (sf.responsables LIKE :search OR sf.temas_tratados LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
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
     * Registrar asistentes a una sesión
     */
    private function registrarAsistentes($sesionId, $madresIds): void
    {
        $sql = "INSERT INTO sesiones_formacion_asistencia (sesion_id, madre_id, asistio) VALUES (:sesionId, :madreId, 1)";
        $stmt = $this->conn->prepare($sql);

        foreach ($madresIds as $madreId) {
            $id = is_array($madreId) ? $madreId['id'] : $madreId;
            $stmt->execute([':sesionId' => $sesionId, ':madreId' => $id]);
        }
    }

    /**
     * Eliminar asistentes de una sesión
     */
    private function eliminarAsistentes($sesionId): void
    {
        $sql = "DELETE FROM sesiones_formacion_asistencia WHERE sesion_id = :sesionId";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':sesionId' => $sesionId]);
    }

    /**
     * Obtener estadísticas de sesiones por programa
     */
    public function getEstadisticasByPrograma($programaId): array
    {
        $sql = "SELECT 
                    COUNT(DISTINCT sf.id) as total_sesiones,
                    COUNT(DISTINCT sfa.madre_id) as total_madres_unicas,
                    SUM(CASE WHEN sf.tipo_sesion = 'discipulado' THEN 1 ELSE 0 END) as sesiones_discipulado,
                    SUM(CASE WHEN sf.tipo_sesion = 'consejeria' THEN 1 ELSE 0 END) as sesiones_consejeria,
                    SUM(CASE WHEN sf.tipo_sesion = 'capacitacion' THEN 1 ELSE 0 END) as sesiones_capacitacion,
                    SUM(CASE WHEN sf.tipo_sesion = 'reunion_tematica' THEN 1 ELSE 0 END) as sesiones_reunion
                FROM sesiones_formacion sf
                LEFT JOIN sesiones_formacion_asistencia sfa ON sf.id = sfa.sesion_id
                WHERE sf.programa_id = :programaId";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':programaId', $programaId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mapear fila de BD a objeto SesionFormacion
     */
    private function mapRowToSesion($row): SesionFormacion
    {
        return new SesionFormacion(
            (int) $row['programa_id'],
            $row['tipo_sesion'],
            $row['fecha_sesion'],
            $row['responsables'],
            (int) $row['id'],
            $row['programa_nombre'] ?? null,
            $row['temas_tratados'] ?? null,
            $row['observaciones'] ?? null,
            null,
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}
