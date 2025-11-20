<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../dao/MadreProgramaDAO.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Datos JSON inválidos');
    }

    // Validar campos requeridos
    if (empty($data['madreId'])) {
        throw new Exception('El campo madreId es requerido');
    }

    if (empty($data['programaId'])) {
        throw new Exception('El campo programaId es requerido');
    }

    if (empty($data['fechaInscripcion'])) {
        throw new Exception('El campo fechaInscripcion es requerido');
    }

    // Crear o actualizar
    $dao = new MadreProgramaDAO();
    $isUpdate = !empty($data['id']);

    if ($isUpdate) {
        // Actualizar inscripción existente
        $inscripcion = $dao->getById($data['id']);
        if (!$inscripcion) {
            throw new Exception('Inscripción no encontrada');
        }

        $inscripcion->setMadreId((int) $data['madreId']);
        $inscripcion->setProgramaId((int) $data['programaId']);
        $inscripcion->setFechaInscripcion($data['fechaInscripcion']);
        $inscripcion->setEstado($data['estado'] ?? 'inscrita');
        $inscripcion->setObservacionesSeguimiento($data['observacionesSeguimiento'] ?? null);

        $result = $dao->update($inscripcion);
        $message = 'Inscripción actualizada correctamente';
        $inscripcionId = $inscripcion->getId();
    } else {
        // Crear nueva inscripción
        $inscripcion = new MadrePrograma(
            (int) $data['madreId'],
            (int) $data['programaId'],
            $data['fechaInscripcion'],
            null,
            null,
            null,
            $data['estado'] ?? 'inscrita',
            $data['observacionesSeguimiento'] ?? null
        );

        $result = $dao->create($inscripcion);
        $message = 'Inscripción registrada correctamente';
        $inscripcionId = $inscripcion->getId();
    }

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => $message,
            'id' => $inscripcionId
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Error al guardar la inscripción');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

