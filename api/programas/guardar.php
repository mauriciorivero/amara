<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../dao/ProgramaDAO.php';

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

    if (empty($data['responsableNombre'])) {
        throw new Exception('El campo responsableNombre es requerido');
    }

    // Validar lógica es_propio
    $esPropio = $data['esPropio'] ?? false;
    $aliadoId = $data['aliadoId'] ?? null;

    if ($esPropio && $aliadoId !== null) {
        throw new Exception('Un programa propio no puede tener aliado asociado');
    }

    if (!$esPropio && $aliadoId === null) {
        throw new Exception('Un programa de aliado debe tener un aliado asociado');
    }

    // Crear o actualizar
    $dao = new ProgramaDAO();
    $isUpdate = !empty($data['id']);

    if ($isUpdate) {
        // Actualizar programa existente
        $programa = $dao->getById($data['id']);
        if (!$programa) {
            throw new Exception('Programa no encontrado');
        }

        $programa->setNombre($data['nombre']);
        $programa->setDescripcion($data['descripcion'] ?? null);
        $programa->setEsPropio($esPropio);
        $programa->setAliadoId($aliadoId);
        $programa->setResponsableNombre($data['responsableNombre']);
        $programa->setResponsableTelefono($data['responsableTelefono'] ?? null);
        $programa->setResponsableCorreo($data['responsableCorreo'] ?? null);
        $programa->setResponsableCargo($data['responsableCargo'] ?? null);
        $programa->setEstado($data['estado'] ?? 'activo');
        $programa->setFechaInicio($data['fechaInicio'] ?? null);
        $programa->setFechaFin($data['fechaFin'] ?? null);

        $result = $dao->update($programa);
        $message = 'Programa actualizado correctamente';
        $programaId = $programa->getId();
    } else {
        // Crear nuevo programa
        $programa = new Programa(
            $data['nombre'],
            $data['responsableNombre'],
            null,
            $data['descripcion'] ?? null,
            $esPropio,
            $aliadoId,
            null,
            $data['responsableTelefono'] ?? null,
            $data['responsableCorreo'] ?? null,
            $data['responsableCargo'] ?? null,
            $data['estado'] ?? 'activo',
            $data['fechaInicio'] ?? null,
            $data['fechaFin'] ?? null
        );

        $result = $dao->create($programa);
        $message = 'Programa registrado correctamente';
        $programaId = $programa->getId();
    }

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => $message,
            'id' => $programaId
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Error al guardar el programa');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

