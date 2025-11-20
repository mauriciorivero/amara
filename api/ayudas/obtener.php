<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/AyudaDAO.php';

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('El parámetro id es requerido');
    }

    $id = (int) $_GET['id'];
    $dao = new AyudaDAO();
    $ayuda = $dao->getById($id);

    if (!$ayuda) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Ayuda no encontrada'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $madre = $ayuda->getMadre();
    $bebe = $ayuda->getBebe();

    $data = [
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

    echo json_encode([
        'success' => true,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

