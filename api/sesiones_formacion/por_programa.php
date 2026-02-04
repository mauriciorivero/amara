<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/SesionFormacionDAO.php';

try {
    if (!isset($_GET['programaId']) || empty($_GET['programaId'])) {
        throw new Exception('El parámetro programaId es requerido');
    }

    $programaId = (int) $_GET['programaId'];
    $dao = new SesionFormacionDAO();
    $sesiones = $dao->getByProgramaId($programaId);
    $estadisticas = $dao->getEstadisticasByPrograma($programaId);

    echo json_encode([
        'success' => true,
        'data' => $sesiones,
        'estadisticas' => $estadisticas,
        'total' => count($sesiones)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
