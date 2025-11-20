<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/ProgramaDAO.php';

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('El parámetro id es requerido');
    }

    $id = (int) $_GET['id'];
    $dao = new ProgramaDAO();
    $programa = $dao->getById($id);

    if (!$programa) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Programa no encontrado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => $programa
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

