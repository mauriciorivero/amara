<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/EmbarazoDAO.php';

try {
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 100;
    $offset = ($page - 1) * $limit;

    $dao = new EmbarazoDAO();
    $embarazos = $dao->getAll($limit, $offset);
    $total = $dao->countAll();

    $data = array_map(function ($embarazo) {
        $madre = $embarazo->getMadre();
        $orientadora = $madre ? $madre->getOrientadora() : null;

        return [
            'id' => $embarazo->getId(),
            'madreId' => $embarazo->getMadreId(),
            'madreNombre' => $madre ? $madre->getNombreCompleto() : 'Desconocida',
            'orientadoraNombre' => $orientadora ? $orientadora->getNombre() : 'Sin asignar',
            'totalBebesNacidos' => $embarazo->getTotalBebesNacidos(),
            'totalBebesPorNacer' => $embarazo->getTotalBebesPorNacer(),
            'bebesNoNacidos' => $embarazo->getBebesNoNacidos(),
            'bebesFallecidos' => $embarazo->getBebesFallecidos(),
            'esMultiple' => $embarazo->isEsMultiple(),
            'totalBebes' => $embarazo->getTotalBebes(),
            'createdAt' => $embarazo->getCreatedAt()
        ];
    }, $embarazos);

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
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
