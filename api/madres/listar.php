<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // Para desarrollo local

require_once __DIR__ . '/../../dao/MadreDAO.php';

try {
    $dao = new MadreDAO();
    $madres = $dao->getAll();

    echo json_encode([
        'success' => true,
        'count' => count($madres),
        'data' => $madres
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
