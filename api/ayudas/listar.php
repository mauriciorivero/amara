<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/AyudaDAO.php';

try {
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 100;
    $offset = ($page - 1) * $limit;

    // Construir filtros
    $filters = [];
    
    if (isset($_GET['madreId']) && $_GET['madreId'] !== '') {
        $filters['madreId'] = (int) $_GET['madreId'];
    }
    
    if (isset($_GET['bebeId']) && $_GET['bebeId'] !== '') {
        $filters['bebeId'] = (int) $_GET['bebeId'];
    }
    
    if (isset($_GET['tipoAyuda']) && $_GET['tipoAyuda'] !== '') {
        $filters['tipoAyuda'] = $_GET['tipoAyuda'];
    }
    
    if (isset($_GET['origenAyuda']) && $_GET['origenAyuda'] !== '') {
        $filters['origenAyuda'] = $_GET['origenAyuda'];
    }
    
    if (isset($_GET['estado']) && $_GET['estado'] !== '') {
        $filters['estado'] = $_GET['estado'];
    }
    
    if (isset($_GET['fechaDesde']) && $_GET['fechaDesde'] !== '') {
        $filters['fechaDesde'] = $_GET['fechaDesde'];
    }
    
    if (isset($_GET['fechaHasta']) && $_GET['fechaHasta'] !== '') {
        $filters['fechaHasta'] = $_GET['fechaHasta'];
    }

    $dao = new AyudaDAO();
    $ayudas = $dao->getAll($limit, $offset, $filters);
    $total = $dao->countAll($filters);

    // Mapear datos para respuesta
    $data = array_map(function ($ayuda) {
        $madre = $ayuda->getMadre();
        $bebe = $ayuda->getBebe();

        return [
            'id' => $ayuda->getId(),
            'madreId' => $ayuda->getMadreId(),
            'madreNombre' => $madre ? $madre->getNombreCompleto() : 'Desconocida',
            'bebeId' => $ayuda->getBebeId(),
            'bebeNombre' => $bebe ? $bebe->getNombre() : null,
            'tipoAyuda' => $ayuda->getTipoAyuda(),
            'origenAyuda' => $ayuda->getOrigenAyuda(),
            'fechaRecepcion' => $ayuda->getFechaRecepcion(),
            'valor' => $ayuda->getValor(),
            'estado' => $ayuda->getEstado(),
            'observaciones' => $ayuda->getObservaciones(),
            'esParaBebe' => $ayuda->esParaBebe(),
            'createdAt' => $ayuda->getCreatedAt(),
            'updatedAt' => $ayuda->getUpdatedAt()
        ];
    }, $ayudas);

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

