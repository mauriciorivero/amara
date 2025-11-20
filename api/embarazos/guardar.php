<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../dao/EmbarazoDAO.php';
require_once __DIR__ . '/../../model/Embarazo.php';

try {
    // Obtener datos del request
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Datos inválidos'
        ]);
        exit;
    }

    // Validar campo requerido
    if (!isset($input['madreId'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Se requiere madreId'
        ]);
        exit;
    }

    $dao = new EmbarazoDAO();

    // Determinar si es creación o actualización
    $esActualizacion = isset($input['id']) && !empty($input['id']);

    // Crear objeto Embarazo
    $embarazo = new Embarazo(
        (int) $input['madreId'],
        $esActualizacion ? (int) $input['id'] : null
    );

    // Setear propiedades
    $embarazo->setTotalBebesNacidos($input['totalBebesNacidos'] ?? 0);
    $embarazo->setTotalBebesPorNacer($input['totalBebesPorNacer'] ?? 0);
    $embarazo->setBebesNoNacidos($input['bebesNoNacidos'] ?? 0);
    $embarazo->setBebesFallecidos($input['bebesFallecidos'] ?? 0);
    $embarazo->setEsMultiple($input['esMultiple'] ?? false);

    // Ejecutar operación
    if ($esActualizacion) {
        $resultado = $dao->update($embarazo);
        $mensaje = 'Embarazo actualizado exitosamente';
    } else {
        $resultado = $dao->create($embarazo);
        $mensaje = 'Embarazo registrado exitosamente';
    }

    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => $mensaje,
            'id' => $embarazo->getId()
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'No se pudo guardar el embarazo'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

