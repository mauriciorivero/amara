<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/OrientadoraDAO.php';
require_once __DIR__ . '/../../model/Orientadora.php';

try {
    // Obtener datos JSON del body
    $data = json_decode(file_get_contents('php://input'), true);

    // Validaciones básicas
    if (empty($data['nombre'])) {
        throw new Exception("El nombre es requerido");
    }

    $dao = new OrientadoraDAO();

    // Determinar si es creación o actualización
    $id = isset($data['id']) && $data['id'] ? (int) $data['id'] : null;
    $nombre = trim($data['nombre']);
    $activa = isset($data['activa']) ? (bool) $data['activa'] : true;

    if ($id) {
        // Actualizar orientadora existente
        $orientadora = $dao->getById($id);

        if (!$orientadora) {
            throw new Exception("Orientadora no encontrada");
        }

        $orientadora->setNombre($nombre);
        $orientadora->setActiva($activa);

        $result = $dao->update($orientadora);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Orientadora actualizada correctamente',
                'id' => $id
            ], JSON_UNESCAPED_UNICODE);
        } else {
            throw new Exception("Error al actualizar la orientadora");
        }
    } else {
        // Crear nueva orientadora
        $orientadora = new Orientadora($nombre, null, $activa);
        $result = $dao->create($orientadora);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Orientadora registrada correctamente',
                'id' => $orientadora->getId()
            ], JSON_UNESCAPED_UNICODE);
        } else {
            throw new Exception("Error al registrar la orientadora");
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
