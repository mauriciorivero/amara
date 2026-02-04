<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/MadreDAO.php';

try {
    $search = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    if (empty($search)) {
        echo json_encode([
            'success' => true,
            'data' => []
        ]);
        exit;
    }
    
    $dao = new MadreDAO();
    
    // Buscar madres con el término de búsqueda
    $filters = ['search' => $search];
    $madres = $dao->getAll(10, 0, $filters); // Limitar a 10 resultados
    
    // Formatear resultados para autocompletado
    $results = array_map(function($madre) {
        return [
            'id' => $madre->getId(),
            'nombreCompleto' => $madre->getNombreCompleto(),
            'tipoDocumento' => $madre->getTipoDocumento(),
            'numeroDocumento' => $madre->getNumeroDocumento(),
            'telefono' => $madre->getNumeroTelefono(),
            'activa' => $madre->isActiva()
        ];
    }, $madres);
    
    echo json_encode([
        'success' => true,
        'data' => $results
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
