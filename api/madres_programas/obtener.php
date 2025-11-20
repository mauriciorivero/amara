<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/MadreProgramaDAO.php';

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('El parámetro id es requerido');
    }

    $id = (int) $_GET['id'];
    $dao = new MadreProgramaDAO();
    $inscripcion = $dao->getById($id);

    if (!$inscripcion) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Inscripción no encontrada'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $madre = $inscripcion->getMadre();
    $programa = $inscripcion->getPrograma();

    $data = [
        'id' => $inscripcion->getId(),
        'madreId' => $inscripcion->getMadreId(),
        'madreNombre' => $madre ? $madre->getNombreCompleto() : 'Desconocida',
        'programaId' => $inscripcion->getProgramaId(),
        'programaNombre' => $programa ? $programa->getNombre() : 'Desconocido',
        'programaDescripcion' => $programa ? $programa->getDescripcion() : null,
        'programaEsPropio' => $programa ? $programa->isEsPropio() : false,
        'fechaInscripcion' => $inscripcion->getFechaInscripcion(),
        'estado' => $inscripcion->getEstado(),
        'observacionesSeguimiento' => $inscripcion->getObservacionesSeguimiento(),
        'createdAt' => $inscripcion->getCreatedAt(),
        'updatedAt' => $inscripcion->getUpdatedAt()
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

