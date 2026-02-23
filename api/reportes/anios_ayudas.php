<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/Database.php';

try {
    $conn = Database::getInstance()->getConnection();
    
    // Obtener años únicos de created_at de ayudas
    $sql = "SELECT DISTINCT YEAR(created_at) as anio 
            FROM ayudas 
            WHERE created_at IS NOT NULL
            ORDER BY anio DESC";
    
    $stmt = $conn->query($sql);
    $anios = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'data' => $anios
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
