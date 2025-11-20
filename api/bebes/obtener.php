<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/BebeDAO.php';

try {
    if (empty($_GET['id'])) {
        throw new Exception('ID de bebé requerido');
    }

    $dao = new BebeDAO();
    $bebe = $dao->getById($_GET['id']);

    if ($bebe) {
        echo json_encode([
            'success' => true,
            'data' => $bebe
        ]);
    } else {
        throw new Exception('Bebé no encontrado');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
