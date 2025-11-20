<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/EmbarazoDAO.php';

try {
    // Validar que se reciba el ID
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Se requiere el parámetro id'
        ]);
        exit;
    }

    $id = (int) $_GET['id'];
    $conBebes = isset($_GET['con_bebes']) && $_GET['con_bebes'] === 'true';

    $dao = new EmbarazoDAO();
    
    if ($conBebes) {
        $resultado = $dao->getEmbarazoConBebes($id);
        
        if (!$resultado) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Embarazo no encontrado'
            ]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => $resultado
        ], JSON_UNESCAPED_UNICODE);
    } else {
        $embarazo = $dao->getById($id);

        if (!$embarazo) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Embarazo no encontrado'
            ]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => $embarazo
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

