<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/OrientadoraDAO.php';

try {
    // Obtener ID del query parameter
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception("ID requerido");
    }

    $id = (int) $_GET['id'];
    $dao = new OrientadoraDAO();
    $orientadora = $dao->getById($id);

    if ($orientadora) {
        echo json_encode([
            'success' => true,
            'data' => $orientadora
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Orientadora no encontrada'
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
