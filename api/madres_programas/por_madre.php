<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/MadreProgramaDAO.php';

try {
    if (!isset($_GET['madreId']) || empty($_GET['madreId'])) {
        throw new Exception('El parámetro madreId es requerido');
    }

    $madreId = (int) $_GET['madreId'];
    $dao = new MadreProgramaDAO();
    $resultado = $dao->getProgramasMadreAgrupados($madreId);

    // Formatear datos de programas propios
    $propiosFormateados = array_map(function($inscripcion) {
        $programa = $inscripcion->getPrograma();
        return [
            'inscripcionId' => $inscripcion->getId(),
            'programaId' => $inscripcion->getProgramaId(),
            'programaNombre' => $programa ? $programa->getNombre() : 'Desconocido',
            'fechaInscripcion' => $inscripcion->getFechaInscripcion(),
            'estado' => $inscripcion->getEstado(),
            'observaciones' => $inscripcion->getObservacionesSeguimiento()
        ];
    }, $resultado['propios']);

    // Formatear datos de programas de aliados
    $aliadosFormateados = [];
    foreach ($resultado['aliados'] as $aliadoNombre => $inscripciones) {
        $aliadosFormateados[$aliadoNombre] = array_map(function($inscripcion) {
            $programa = $inscripcion->getPrograma();
            return [
                'inscripcionId' => $inscripcion->getId(),
                'programaId' => $inscripcion->getProgramaId(),
                'programaNombre' => $programa ? $programa->getNombre() : 'Desconocido',
                'fechaInscripcion' => $inscripcion->getFechaInscripcion(),
                'estado' => $inscripcion->getEstado(),
                'observaciones' => $inscripcion->getObservacionesSeguimiento()
            ];
        }, $inscripciones);
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'propios' => $propiosFormateados,
            'aliados' => $aliadosFormateados
        ],
        'totales' => [
            'propios' => $resultado['totalPropios'],
            'aliados' => $resultado['totalAliados'],
            'total' => $resultado['totalPropios'] + $resultado['totalAliados']
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

