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

    public function getAll(): array
    {
        $sql = "SELECT * FROM aliados WHERE activo = 1 ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $lista = [];

        foreach ($resultados as $row) {
            $aliado = new Aliado(
                $row['id'],
                $row['nombre'],
                (bool) $row['activo'],
                $row['created_at'],
                $row['updated_at']
            );
            $lista[] = $aliado;
        }

        return $lista;
    }
}
