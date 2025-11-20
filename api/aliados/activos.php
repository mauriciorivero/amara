<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/AliadoDAO.php';

try {
    $dao = new AliadoDAO();
    $aliados = $dao->getActivos();

    echo json_encode([
        'success' => true,
        'data' => $aliados
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

