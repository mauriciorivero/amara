<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/ProgramaDAO.php';

try {
    if (!isset($_GET['aliadoId']) || empty($_GET['aliadoId'])) {
        throw new Exception('El parámetro aliadoId es requerido');
    }

    $aliadoId = (int) $_GET['aliadoId'];
    $dao = new ProgramaDAO();
    $programas = $dao->getByAliadoId($aliadoId);

    echo json_encode([
        'success' => true,
        'data' => $programas,
        'total' => count($programas)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

