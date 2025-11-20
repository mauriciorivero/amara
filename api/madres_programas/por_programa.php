<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/MadreProgramaDAO.php';

try {
    if (!isset($_GET['programaId']) || empty($_GET['programaId'])) {
        throw new Exception('El parámetro programaId es requerido');
    }

    $programaId = (int) $_GET['programaId'];
    $dao = new MadreProgramaDAO();
    $inscripciones = $dao->getByProgramaId($programaId);

    // Calcular estadísticas por estado
    $estadisticas = [
        'inscrita' => 0,
        'activa' => 0,
        'completada' => 0,
        'abandonada' => 0
    ];

    $data = array_map(function($inscripcion) use (&$estadisticas) {
        $madre = $inscripcion->getMadre();
        
        // Contar por estado
        $estado = $inscripcion->getEstado();
        if (isset($estadisticas[$estado])) {
            $estadisticas[$estado]++;
        }

        return [
            'inscripcionId' => $inscripcion->getId(),
            'madreId' => $inscripcion->getMadreId(),
            'madreNombre' => $madre ? $madre->getNombreCompleto() : 'Desconocida',
            'fechaInscripcion' => $inscripcion->getFechaInscripcion(),
            'estado' => $estado,
            'observaciones' => $inscripcion->getObservacionesSeguimiento()
        ];
    }, $inscripciones);

    echo json_encode([
        'success' => true,
        'data' => $data,
        'estadisticas' => $estadisticas,
        'total' => count($inscripciones)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

