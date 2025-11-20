<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../dao/AyudaDAO.php';

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

    if (empty($data['tipoAyuda'])) {
        throw new Exception('El campo tipoAyuda es requerido');
    }

    if (empty($data['fechaRecepcion'])) {
        throw new Exception('El campo fechaRecepcion es requerido');
    }

    // Validar que si es ayuda de bebé, bebeId es obligatorio
    $tiposParaBebe = ['kit_recien_nacido', 'salud_recien_nacido', 'elementos_recien_nacido'];
    if (in_array($data['tipoAyuda'], $tiposParaBebe)) {
        if (empty($data['bebeId'])) {
            throw new Exception('El campo bebeId es requerido para ayudas de recién nacido');
        }
    }

    // Validar que fecha no sea futura
    $fechaRecepcion = new DateTime($data['fechaRecepcion']);
    $hoy = new DateTime();
    if ($fechaRecepcion > $hoy) {
        throw new Exception('La fecha de recepción no puede ser futura');
    }

    // Validar valor
    if (isset($data['valor']) && $data['valor'] < 0) {
        throw new Exception('El valor de la ayuda no puede ser negativo');
    }

    // Crear o actualizar
    $dao = new AyudaDAO();
    $isUpdate = !empty($data['id']);

    if ($isUpdate) {
        // Actualizar ayuda existente
        $ayuda = $dao->getById($data['id']);
        if (!$ayuda) {
            throw new Exception('Ayuda no encontrada');
        }

        $ayuda->setMadreId((int) $data['madreId']);
        $ayuda->setBebeId(!empty($data['bebeId']) ? (int) $data['bebeId'] : null);
        $ayuda->setTipoAyuda($data['tipoAyuda']);
        $ayuda->setOrigenAyuda($data['origenAyuda'] ?? 'corporacion');
        $ayuda->setFechaRecepcion($data['fechaRecepcion']);
        $ayuda->setValor(isset($data['valor']) ? (float) $data['valor'] : 0.0);
        $ayuda->setEstado($data['estado'] ?? 'pendiente');
        $ayuda->setObservaciones($data['observaciones'] ?? null);

        $result = $dao->update($ayuda);
        $message = 'Ayuda actualizada correctamente';
        $ayudaId = $ayuda->getId();
    } else {
        // Crear nueva ayuda
        $ayuda = new Ayuda(
            (int) $data['madreId'],
            $data['tipoAyuda'],
            $data['fechaRecepcion'],
            null,
            null,
            !empty($data['bebeId']) ? (int) $data['bebeId'] : null,
            null,
            $data['origenAyuda'] ?? 'corporacion',
            isset($data['valor']) ? (float) $data['valor'] : 0.0,
            $data['estado'] ?? 'pendiente',
            $data['observaciones'] ?? null
        );

        $result = $dao->create($ayuda);
        $message = 'Ayuda registrada correctamente';
        $ayudaId = $ayuda->getId();
    }

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => $message,
            'id' => $ayudaId
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Error al guardar la ayuda');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

