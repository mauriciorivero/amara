<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/OrientadoraDAO.php';

try {
    // Obtener datos JSON del body
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id']) || empty($data['id'])) {
        throw new Exception("ID requerido");
    }

    $id = (int) $data['id'];
    $dao = new OrientadoraDAO();

    // El DAO lanzará una excepción si hay madres asignadas
    $result = $dao->delete($id);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Orientadora eliminada correctamente'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception("Error al eliminar la orientadora");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
