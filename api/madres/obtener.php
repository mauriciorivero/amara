<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/MadreDAO.php';

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID de madre no proporcionado');
    }

    $id = (int) $_GET['id'];
    $dao = new MadreDAO();
    $madre = $dao->getById($id);

    if ($madre) {
        echo json_encode([
            'success' => true,
            'data' => $madre
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Madre no encontrada'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
