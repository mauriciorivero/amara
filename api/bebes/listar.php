<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/BebeDAO.php';

try {
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 100;
    $offset = ($page - 1) * $limit;

    $dao = new BebeDAO();
    $bebes = $dao->getAll($limit, $offset);
    $total = $dao->countAll();

    // Mapear bebés a formato JSON serializable
    $bebesData = array_map(function ($bebe) {
        $madre = $bebe->getMadre();
        return [
            'id' => $bebe->getId(),
            'embarazoId' => $bebe->getEmbarazoId(),
            'madreId' => $bebe->getMadreId(),
            'madreNombre' => $madre ? $madre->getNombreCompleto() : 'Desconocida',
            'nombre' => $bebe->getNombre(),
            'sexo' => $bebe->getSexo(),
            'fechaNacimiento' => $bebe->getFechaNacimiento(),
            'esMellizo' => $bebe->isEsMellizo(),
            'estado' => $bebe->getEstado(),
            'fechaIncidente' => $bebe->getFechaIncidente(),
            'observaciones' => $bebe->getObservaciones(),
            'hasNacido' => $bebe->hasNacido(),
            'createdAt' => $bebe->getCreatedAt(),
            'updatedAt' => $bebe->getUpdatedAt()
        ];
    }, $bebes);

    echo json_encode([
        'success' => true,
        'data' => $bebesData,
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

