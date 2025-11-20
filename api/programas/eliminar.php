<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../dao/ProgramaDAO.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || empty($data['id'])) {
        throw new Exception('El parámetro id es requerido');
    }

    $id = (int) $data['id'];
    $dao = new ProgramaDAO();

    // Verificar que el programa existe
    $programa = $dao->getById($id);
    if (!$programa) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Programa no encontrado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result = $dao->delete($id);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Programa eliminado correctamente'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Error al eliminar el programa');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

