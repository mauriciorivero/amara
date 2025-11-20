<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/BebeDAO.php';

try {
    if (empty($_GET['madreId'])) {
        throw new Exception('ID de madre requerido');
    }

    $madreId = (int) $_GET['madreId'];
    $dao = new BebeDAO();
    $lista = $dao->getByMadreId($madreId);

    echo json_encode([
        'success' => true,
        'data' => $lista
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
