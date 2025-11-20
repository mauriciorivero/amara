<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../dao/AliadoDAO.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Datos JSON inválidos');
    }

    // Validar campos requeridos
    if (empty($data['nombre'])) {
        throw new Exception('El campo nombre es requerido');
    }

    // Crear o actualizar
    $dao = new AliadoDAO();
    $isUpdate = !empty($data['id']);

    if ($isUpdate) {
        // Actualizar aliado existente
        $aliado = $dao->getById($data['id']);
        if (!$aliado) {
            throw new Exception('Aliado no encontrado');
        }

        $aliado->setNombre($data['nombre']);
        $aliado->setDescripcion($data['descripcion'] ?? null);
        $aliado->setPersonaContactoExterno($data['personaContactoExterno'] ?? null);
        $aliado->setUsuarioRegistroId($data['usuarioRegistroId'] ?? null);
        $aliado->setTelefono($data['telefono'] ?? null);
        $aliado->setCorreo($data['correo'] ?? null);
        $aliado->setDireccion($data['direccion'] ?? null);
        $aliado->setEstado($data['estado'] ?? 'activo');
        $aliado->setActivo($data['activo'] ?? true);

        $result = $dao->update($aliado);
        $message = 'Aliado actualizado correctamente';
        $aliadoId = $aliado->getId();
    } else {
        // Crear nuevo aliado
        $aliado = new Aliado(
            $data['nombre'],
            null,
            $data['descripcion'] ?? null,
            $data['personaContactoExterno'] ?? null,
            $data['usuarioRegistroId'] ?? null,
            $data['telefono'] ?? null,
            $data['correo'] ?? null,
            $data['direccion'] ?? null,
            $data['estado'] ?? 'activo',
            $data['activo'] ?? true
        );

        $result = $dao->create($aliado);
        $message = 'Aliado registrado correctamente';
        $aliadoId = $aliado->getId();
    }

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => $message,
            'id' => $aliadoId
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Error al guardar el aliado');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

