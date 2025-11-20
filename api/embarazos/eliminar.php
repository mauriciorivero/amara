<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../../dao/EmbarazoDAO.php';

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (empty($data['id'])) {
        throw new Exception('ID de embarazo requerido');
    }

    $dao = new EmbarazoDAO();
    $result = $dao->delete($data['id']);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Embarazo eliminado correctamente'
        ]);
    } else {
        throw new Exception('Error al eliminar el embarazo');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
