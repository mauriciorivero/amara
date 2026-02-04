<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/SesionFormacionDAO.php';

try {
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 25;
    $offset = ($page - 1) * $limit;

    $filters = [];
    
    if (isset($_GET['programaId']) && $_GET['programaId'] !== '') {
        $filters['programaId'] = (int) $_GET['programaId'];
    }
    
    if (isset($_GET['tipoSesion']) && $_GET['tipoSesion'] !== '') {
        $filters['tipoSesion'] = $_GET['tipoSesion'];
    }
    
    if (isset($_GET['fechaDesde']) && $_GET['fechaDesde'] !== '') {
        $filters['fechaDesde'] = $_GET['fechaDesde'];
    }
    
    if (isset($_GET['fechaHasta']) && $_GET['fechaHasta'] !== '') {
        $filters['fechaHasta'] = $_GET['fechaHasta'];
    }
    
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $filters['search'] = $_GET['search'];
    }

    $dao = new SesionFormacionDAO();
    $sesiones = $dao->getAll($limit, $offset, $filters);
    $total = $dao->countAll($filters);

    echo json_encode([
        'success' => true,
        'data' => $sesiones,
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
