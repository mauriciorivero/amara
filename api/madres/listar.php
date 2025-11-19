<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/MadreDAO.php';

try {
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 25;
    $offset = ($page - 1) * $limit;

    $filters = [
        'search' => $_GET['search'] ?? '',
        'estado' => $_GET['estado'] ?? '',
        'orientadora' => $_GET['orientadora'] ?? '',
        'eps' => $_GET['eps'] ?? '',
        'edad' => $_GET['edad'] ?? ''
    ];

    $dao = new MadreDAO();
    $madres = $dao->getAll($limit, $offset, $filters);
    $total = $dao->countAll($filters);

    echo json_encode([
        'success' => true,
        'data' => $madres,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
