<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/MadreProgramaDAO.php';

try {
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 25;
    $offset = ($page - 1) * $limit;

    // Construir filtros
    $filters = [];
    
    if (isset($_GET['madreId']) && $_GET['madreId'] !== '') {
        $filters['madreId'] = (int) $_GET['madreId'];
    }
    
    if (isset($_GET['programaId']) && $_GET['programaId'] !== '') {
        $filters['programaId'] = (int) $_GET['programaId'];
    }
    
    if (isset($_GET['estado']) && $_GET['estado'] !== '') {
        $filters['estado'] = $_GET['estado'];
    }

    $dao = new MadreProgramaDAO();
    $inscripciones = $dao->getAll($limit, $offset, $filters);
    $total = $dao->countAll($filters);

    // Mapear datos para respuesta
    $data = array_map(function ($inscripcion) {
        $madre = $inscripcion->getMadre();
        $programa = $inscripcion->getPrograma();

        return [
            'id' => $inscripcion->getId(),
            'madreId' => $inscripcion->getMadreId(),
            'madreNombre' => $madre ? $madre->getNombreCompleto() : 'Desconocida',
            'programaId' => $inscripcion->getProgramaId(),
            'programaNombre' => $programa ? $programa->getNombre() : 'Desconocido',
            'programaEsPropio' => $programa ? $programa->isEsPropio() : false,
            'fechaInscripcion' => $inscripcion->getFechaInscripcion(),
            'estado' => $inscripcion->getEstado(),
            'observacionesSeguimiento' => $inscripcion->getObservacionesSeguimiento(),
            'createdAt' => $inscripcion->getCreatedAt(),
            'updatedAt' => $inscripcion->getUpdatedAt()
        ];
    }, $inscripciones);

    echo json_encode([
        'success' => true,
        'data' => $data,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

