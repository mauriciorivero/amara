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

    public function getAll(): array
    {
        $sql = "SELECT * FROM orientadoras ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $lista = [];

        foreach ($resultados as $row) {
            $orientadora = new Orientadora(
                $row['nombre'],
                (int) $row['id'],
                (bool) $row['activa'],
                $row['created_at'],
                $row['updated_at']
            );
            $lista[] = $orientadora;
        }

        return $lista;
    }
}
