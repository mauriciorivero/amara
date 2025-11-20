<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../dao/MadreProgramaDAO.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || empty($data['id'])) {
        throw new Exception('El parámetro id es requerido');
    }

    $id = (int) $data['id'];
    $dao = new MadreProgramaDAO();

    // Verificar que la inscripción existe
    $inscripcion = $dao->getById($id);
    if (!$inscripcion) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Inscripción no encontrada'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result = $dao->delete($id);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Inscripción eliminada correctamente'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Error al eliminar la inscripción');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

