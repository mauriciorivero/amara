<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/ProgramaDAO.php';

try {
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 25;
    $offset = ($page - 1) * $limit;

    // Construir filtros
    $filters = [];
    
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $filters['search'] = $_GET['search'];
    }
    
    if (isset($_GET['esPropio'])) {
        $filters['esPropio'] = filter_var($_GET['esPropio'], FILTER_VALIDATE_BOOLEAN);
    }
    
    if (isset($_GET['aliadoId']) && $_GET['aliadoId'] !== '') {
        $filters['aliadoId'] = (int) $_GET['aliadoId'];
    }
    
    if (isset($_GET['estado']) && $_GET['estado'] !== '') {
        $filters['estado'] = $_GET['estado'];
    }

    $dao = new ProgramaDAO();
    $programas = $dao->getAll($limit, $offset, $filters);
    $total = $dao->countAll($filters);

    echo json_encode([
        'success' => true,
        'data' => $programas,
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

