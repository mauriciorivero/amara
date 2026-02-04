<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/SesionFormacionDAO.php';

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('El parámetro id es requerido');
    }

    $id = (int) $_GET['id'];
    $dao = new SesionFormacionDAO();
    $sesion = $dao->getById($id);

    if ($sesion) {
        echo json_encode([
            'success' => true,
            'data' => $sesion
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Sesión no encontrada');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
