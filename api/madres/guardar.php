<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../../dao/MadreDAO.php';
require_once __DIR__ . '/../../model/Madre.php';

try {
    // Obtener datos del cuerpo de la solicitud
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('No se recibieron datos válidos');
    }

    // Validar campos obligatorios mínimos
    if (empty($input['primerNombre']) || empty($input['primerApellido']) || empty($input['fechaIngreso'])) {
        throw new Exception('Faltan campos obligatorios (Nombre, Apellido, Fecha Ingreso)');
    }

    $dao = new MadreDAO();

    // Crear objeto Madre
    // Si viene ID, es una actualización, pero el objeto Madre requiere fechaIngreso en el constructor
    $madre = new Madre(
        $input['fechaIngreso'],
        !empty($input['madreId']) ? (int) $input['madreId'] : null
    );

    // Asignar valores
    $madre->setPrimerNombre($input['primerNombre']);
    $madre->setSegundoNombre($input['segundoNombre'] ?? null);
    $madre->setPrimerApellido($input['primerApellido']);
    $madre->setSegundoApellido($input['segundoApellido'] ?? null);
    $madre->setTipoDocumento($input['tipoDocumento'] ?? null);
    $madre->setNumeroDocumento($input['numeroDocumento'] ?? null);
    $madre->setFechaNacimiento(!empty($input['fechaNacimiento']) ? $input['fechaNacimiento'] : null);
    $madre->setEdad(!empty($input['edad']) ? (int) $input['edad'] : null);
    $madre->setNumeroTelefono($input['numeroTelefono'] ?? null);
    $madre->setOtroContacto($input['otroContacto'] ?? null);
    $madre->setEsVirtual(isset($input['esVirtual']) && $input['esVirtual'] == '1');

    $madre->setEstadoCivil($input['estadoCivil'] ?? null);
    $madre->setOcupacion($input['ocupacion'] ?? null);
    $madre->setNivelEstudio($input['nivelEstudio'] ?? null);
    $madre->setReligion($input['religion'] ?? null);

    $madre->setEpsId(!empty($input['epsId']) ? (int) $input['epsId'] : null);
    $madre->setSisben($input['sisben'] ?? null);
    $madre->setOrientadoraId(!empty($input['orientadoraId']) ? (int) $input['orientadoraId'] : null);
    $madre->setAliadoId(!empty($input['aliadoId']) ? (int) $input['aliadoId'] : null);

    // Guardar
    if ($madre->getId()) {
        $resultado = $dao->update($madre);
        $mensaje = 'Madre actualizada correctamente';
    } else {
        $resultado = $dao->create($madre);
        $mensaje = 'Madre registrada correctamente';
    }

    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => $mensaje
        ]);
    } else {
        throw new Exception('Error al guardar en la base de datos');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
