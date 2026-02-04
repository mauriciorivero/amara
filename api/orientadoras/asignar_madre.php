<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/OrientadoraDAO.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['orientadoraId']) || !isset($input['madreId'])) {
        throw new Exception('orientadoraId y madreId son requeridos');
    }

    $orientadoraId = (int) $input['orientadoraId'];
    $madreId = (int) $input['madreId'];
    $fechaAsignacion = $input['fechaAsignacion'] ?? date('Y-m-d');
    $motivoCambio = $input['motivoCambio'] ?? null;
    $observaciones = $input['observaciones'] ?? null;

    $dao = new OrientadoraDAO();
    $resultado = $dao->createAsignacion(
        $orientadoraId,
        $madreId,
        $fechaAsignacion,
        $motivoCambio,
        $observaciones
    );

    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Madre asignada correctamente'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('No se pudo crear la asignación');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
