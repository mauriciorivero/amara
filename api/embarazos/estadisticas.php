<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../dao/EmbarazoDAO.php';
require_once __DIR__ . '/../../dao/BebeDAO.php';

header('Content-Type: application/json');

try {
    $database = Database::getInstance();
    $db = $database->getConnection();

    // Consultas directas para estadísticas rápidas
    // Total Embarazos
    $stmtEmbarazos = $db->query("SELECT COUNT(*) as total FROM embarazos");
    $totalEmbarazos = $stmtEmbarazos->fetch(PDO::FETCH_ASSOC)['total'];

    // Total Bebés Nacidos
    $stmtBebes = $db->query("SELECT COUNT(*) as total FROM bebes WHERE estado = 'Nacido'");
    $totalBebesNacidos = $stmtBebes->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'success' => true,
        'data' => [
            'totalEmbarazos' => $totalEmbarazos,
            'totalBebesNacidos' => $totalBebesNacidos
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
