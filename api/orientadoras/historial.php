<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/OrientadoraDAO.php';

try {
    if (!isset($_GET['orientadoraId']) || empty($_GET['orientadoraId'])) {
        throw new Exception('El parámetro orientadoraId es requerido');
    }

    $orientadoraId = (int) $_GET['orientadoraId'];
    $soloActivas = isset($_GET['soloActivas']) && $_GET['soloActivas'] === 'true';

    $dao = new OrientadoraDAO();
    $historial = $dao->getHistorialMadres($orientadoraId, $soloActivas);

    // Formatear datos
    $historialFormateado = array_map(function($asignacion) {
        $madre = $asignacion->getMadre();
        $madreNombre = 'Desconocido';

        if ($madre) {
            $nombres = [];
            if ($madre->getPrimerNombre()) $nombres[] = $madre->getPrimerNombre();
            if ($madre->getSegundoNombre()) $nombres[] = $madre->getSegundoNombre();
            if ($madre->getPrimerApellido()) $nombres[] = $madre->getPrimerApellido();
            if ($madre->getSegundoApellido()) $nombres[] = $madre->getSegundoApellido();
            $madreNombre = implode(' ', $nombres);
        }

        // Calcular duración en días
        $fechaInicio = new DateTime($asignacion->getFechaAsignacion());
        $fechaFinal = $asignacion->getFechaFin() ? new DateTime($asignacion->getFechaFin()) : new DateTime();
        $duracionDias = $fechaFinal->diff($fechaInicio)->days;

        return [
            'asignacionId' => $asignacion->getId(),
            'madreId' => $asignacion->getMadreId(),
            'madreNombre' => $madreNombre,
            'madreDocumento' => $madre ? $madre->getNumeroDocumento() : null,
            'madreTelefono' => $madre ? $madre->getNumeroTelefono() : null,
            'fechaAsignacion' => $asignacion->getFechaAsignacion(),
            'fechaFin' => $asignacion->getFechaFin(),
            'activa' => $asignacion->getActiva(),
            'motivoCambio' => $asignacion->getMotivoCambio(),
            'observaciones' => $asignacion->getObservaciones(),
            'duracionDias' => $duracionDias
        ];
    }, $historial);

    // Calcular estadísticas
    $madresActivas = count(array_filter($historialFormateado, fn($a) => $a['activa']));
    $madresHistoricas = count($historialFormateado);

    echo json_encode([
        'success' => true,
        'data' => $historialFormateado,
        'estadisticas' => [
            'madresActivas' => $madresActivas,
            'madresHistoricas' => $madresHistoricas,
            'madresInactivas' => $madresHistoricas - $madresActivas
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
