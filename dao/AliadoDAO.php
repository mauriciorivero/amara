<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/Aliado.php';

class AliadoDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los aliados con filtros opcionales
     */
    public function getAll($limit = 25, $offset = 0, $filters = [])
    {
        $sql = "SELECT * FROM aliados WHERE 1=1";
        $params = [];

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $sql .= " AND (nombre LIKE :search OR descripcion LIKE :search OR persona_contacto_externo LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND estado = :estado";
            $params[':estado'] = $filters['estado'];
        }

        $sql .= " ORDER BY nombre ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        $aliados = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $aliados[] = $this->mapRowToAliado($row);
        }
        return $aliados;
    }

    /**
     * Obtener un aliado por ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM aliados WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->mapRowToAliado($row);
        }
        return null;
    }

    /**
     * Crear un nuevo aliado
     */
    public function create(Aliado $aliado): bool
    {
        // Validar email si existe
        if ($aliado->getCorreo() && !filter_var($aliado->getCorreo(), FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El correo electrónico no es válido");
        }

        $sql = "INSERT INTO aliados (
            nombre, descripcion, persona_contacto_externo, usuario_registro_id,
            telefono, correo, direccion, estado, activo
        ) VALUES (
            :nombre, :descripcion, :personaContactoExterno, :usuarioRegistroId,
            :telefono, :correo, :direccion, :estado, :activo
        )";

        $stmt = $this->conn->prepare($sql);
        $params = $this->mapParams($aliado);

        $result = $stmt->execute($params);

        if ($result) {
            $aliado->setId((int) $this->conn->lastInsertId());
        }

        return $result;
    }

    /**
     * Actualizar un aliado existente
     */
    public function update(Aliado $aliado): bool
    {
        // Validar email si existe
        if ($aliado->getCorreo() && !filter_var($aliado->getCorreo(), FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El correo electrónico no es válido");
        }

        $sql = "UPDATE aliados SET 
            nombre = :nombre,
            descripcion = :descripcion,
            persona_contacto_externo = :personaContactoExterno,
            usuario_registro_id = :usuarioRegistroId,
            telefono = :telefono,
            correo = :correo,
            direccion = :direccion,
            estado = :estado,
            activo = :activo
            WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $params = $this->mapParams($aliado);
        $params[':id'] = $aliado->getId();

        return $stmt->execute($params);
    }

    /**
     * Eliminar un aliado
     */
    public function delete($id): bool
    {
        // Verificar que no tenga programas activos
        $sqlCheck = "SELECT COUNT(*) as count FROM programas 
                     WHERE aliado_id = :id AND estado = 'activo'";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($row['count'] > 0) {
            throw new Exception("No se puede eliminar el aliado porque tiene programas activos asociados");
        }

        $sql = "DELETE FROM aliados WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Contar total de aliados con filtros
     */
    public function countAll($filters = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM aliados WHERE 1=1";
        $params = [];

        // Aplicar mismos filtros que getAll
        if (!empty($filters['search'])) {
            $sql .= " AND (nombre LIKE :search OR descripcion LIKE :search OR persona_contacto_externo LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND estado = :estado";
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
     * Obtener solo aliados activos para selectores
     */
    public function getActivos(): array
    {
        $sql = "SELECT * FROM aliados WHERE estado = 'activo' ORDER BY nombre ASC";
        $stmt = $this->conn->query($sql);

        $aliados = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $aliados[] = $this->mapRowToAliado($row);
        }
        return $aliados;
    }

    /**
     * Mapear parámetros del objeto Aliado a array
     */
    private function mapParams(Aliado $aliado): array
    {
        return [
            ':nombre' => $aliado->getNombre(),
            ':descripcion' => $aliado->getDescripcion(),
            ':personaContactoExterno' => $aliado->getPersonaContactoExterno(),
            ':usuarioRegistroId' => $aliado->getUsuarioRegistroId(),
            ':telefono' => $aliado->getTelefono(),
            ':correo' => $aliado->getCorreo(),
            ':direccion' => $aliado->getDireccion(),
            ':estado' => $aliado->getEstado(),
            ':activo' => $aliado->isActivo() ? 1 : 0
        ];
    }

    /**
     * Mapear fila de BD a objeto Aliado
     */
    private function mapRowToAliado($row): Aliado
    {
        return new Aliado(
            $row['nombre'],
            (int) $row['id'],
            $row['descripcion'] ?? null,
            $row['persona_contacto_externo'] ?? null,
            isset($row['usuario_registro_id']) ? (int) $row['usuario_registro_id'] : null,
            $row['telefono'] ?? null,
            $row['correo'] ?? null,
            $row['direccion'] ?? null,
            $row['estado'] ?? 'activo',
            (bool) $row['activo'],
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null
        );
    }
}
