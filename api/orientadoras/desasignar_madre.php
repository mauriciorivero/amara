<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/OrientadoraDAO.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['madreId'])) {
        throw new Exception('madreId es requerido');
    }

    $madreId = (int) $input['madreId'];
    $fechaFin = $input['fechaFin'] ?? date('Y-m-d');
    $motivoCambio = $input['motivoCambio'] ?? null;

    $dao = new OrientadoraDAO();
    $resultado = $dao->desasignarMadre($madreId, $fechaFin, $motivoCambio);

    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Madre desasignada correctamente'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('No se pudo desasignar la madre');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
