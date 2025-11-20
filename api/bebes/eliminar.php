<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../../dao/BebeDAO.php';
require_once __DIR__ . '/../../dao/EmbarazoDAO.php';

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (empty($data['id'])) {
        throw new Exception('ID de bebé requerido');
    }

    $bebeDAO = new BebeDAO();

    // Obtener el bebé antes de eliminar para saber a qué embarazo pertenece
    $bebe = $bebeDAO->getById($data['id']);

    if (!$bebe) {
        throw new Exception('Bebé no encontrado');
    }

    $result = $bebeDAO->delete($data['id']);

    if ($result) {
        // Actualizar contadores del embarazo asociado
        $embarazoDAO = new EmbarazoDAO();
        $embarazoDAO->actualizarContadores($bebe->getEmbarazoId());

        echo json_encode([
            'success' => true,
            'message' => 'Bebé eliminado correctamente'
        ]);
    } else {
        throw new Exception('Error al eliminar el bebé');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
