<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../dao/SesionFormacionDAO.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $id = $data['id'] ?? $_GET['id'] ?? null;

    if (!$id) {
        throw new Exception('El parámetro id es requerido');
    }

    $dao = new SesionFormacionDAO();
    
    $sesion = $dao->getById($id);
    if (!$sesion) {
        throw new Exception('Sesión no encontrada');
    }

    $result = $dao->delete($id);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Sesión eliminada correctamente'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Error al eliminar la sesión');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
