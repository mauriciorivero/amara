<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/AyudaDAO.php';

try {
    if (!isset($_GET['madreId']) || empty($_GET['madreId'])) {
        throw new Exception('El parámetro madreId es requerido');
    }

    $madreId = (int) $_GET['madreId'];
    $dao = new AyudaDAO();
    
    $ayudas = $dao->getByMadreId($madreId);
    $totalValor = $dao->getTotalValorByMadre($madreId);

    // Mapear datos para respuesta
    $data = array_map(function ($ayuda) {
        $madre = $ayuda->getMadre();
        $bebe = $ayuda->getBebe();

        return [
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
    }, $ayudas);

    echo json_encode([
        'success' => true,
        'data' => $data,
        'totalAyudas' => count($ayudas),
        'totalValor' => $totalValor
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

