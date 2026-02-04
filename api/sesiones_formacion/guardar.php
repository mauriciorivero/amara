<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../dao/SesionFormacionDAO.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Datos JSON inválidos');
    }

    if (empty($data['programaId'])) {
        throw new Exception('El campo programaId es requerido');
    }
    if (empty($data['tipoSesion'])) {
        throw new Exception('El campo tipoSesion es requerido');
    }
    if (empty($data['fechaSesion'])) {
        throw new Exception('El campo fechaSesion es requerido');
    }
    if (empty($data['responsables'])) {
        throw new Exception('El campo responsables es requerido');
    }

    $tiposValidos = ['discipulado', 'consejeria', 'capacitacion', 'reunion_tematica'];
    if (!in_array($data['tipoSesion'], $tiposValidos)) {
        throw new Exception('Tipo de sesión no válido');
    }

    $dao = new SesionFormacionDAO();
    $isUpdate = !empty($data['id']);

    if ($isUpdate) {
        $sesion = $dao->getById($data['id']);
        if (!$sesion) {
            throw new Exception('Sesión no encontrada');
        }

        $sesion->setProgramaId((int) $data['programaId']);
        $sesion->setTipoSesion($data['tipoSesion']);
        $sesion->setFechaSesion($data['fechaSesion']);
        $sesion->setResponsables($data['responsables']);
        $sesion->setTemasTratados($data['temasTratados'] ?? null);
        $sesion->setObservaciones($data['observaciones'] ?? null);
        $sesion->setMadresAsistentes($data['madresAsistentes'] ?? []);

        $result = $dao->update($sesion);
        $message = 'Sesión actualizada correctamente';
        $sesionId = $sesion->getId();
    } else {
        $sesion = new SesionFormacion(
            (int) $data['programaId'],
            $data['tipoSesion'],
            $data['fechaSesion'],
            $data['responsables'],
            null,
            null,
            $data['temasTratados'] ?? null,
            $data['observaciones'] ?? null,
            $data['madresAsistentes'] ?? []
        );

        $result = $dao->create($sesion);
        $message = 'Sesión registrada correctamente';
        $sesionId = $sesion->getId();
    }

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => $message,
            'id' => $sesionId
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Error al guardar la sesión');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
