<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/EmbarazoDAO.php';

try {
    $madreId = isset($_GET['madre_id']) ? (int) $_GET['madre_id'] : null;

    $dao = new EmbarazoDAO();
    $embarazos = $dao->getEmbarazosActivos($madreId);

    echo json_encode([
        'success' => true,
        'data' => $embarazos,
        'total' => count($embarazos)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

