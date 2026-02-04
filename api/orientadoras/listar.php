<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/OrientadoraDAO.php';

try {
    // Parámetros de paginación
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 25;
    $offset = ($page - 1) * $limit;

    // Filtros
    $filters = [];
    if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
        $filters['search'] = trim($_GET['search']);
    }
    if (isset($_GET['activa']) && $_GET['activa'] !== '') {
        $filters['activa'] = $_GET['activa'];
    }

    $dao = new OrientadoraDAO();
    $lista = $dao->getAll($limit, $offset, $filters);
    $total = $dao->countAll($filters);

    echo json_encode([
        'success' => true,
        'data' => $lista,
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
