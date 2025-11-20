<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../dao/BebeDAO.php';
require_once __DIR__ . '/../../dao/EmbarazoDAO.php';
require_once __DIR__ . '/../../model/Bebe.php';

try {
    // Obtener datos del cuerpo de la solicitud
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Datos inválidos o vacíos');
    }

    // Validar campos requeridos
    if (empty($data['embarazoId']) || empty($data['madreId'])) {
        throw new Exception('ID de embarazo y madre son requeridos');
    }

    $bebeDAO = new BebeDAO();
    $embarazoDAO = new EmbarazoDAO();

    // Crear objeto Bebe
    $bebe = new Bebe(
        (int) $data['embarazoId'],
        (int) $data['madreId'],
        !empty($data['id']) ? (int) $data['id'] : null
    );

    // Asignar valores opcionales
    if (isset($data['nombre']))
        $bebe->setNombre($data['nombre']);
    if (isset($data['sexo']))
        $bebe->setSexo($data['sexo']);

    // Manejo de fechas: convertir string vacío a null
    $fechaNac = !empty($data['fechaNacimiento']) ? $data['fechaNacimiento'] : null;
    $bebe->setFechaNacimiento($fechaNac);

    if (isset($data['esMellizo']))
        $bebe->setEsMellizo((bool) $data['esMellizo']);
    if (isset($data['estado']))
        $bebe->setEstado($data['estado']);

    $fechaInc = !empty($data['fechaIncidente']) ? $data['fechaIncidente'] : null;
    $bebe->setFechaIncidente($fechaInc);

    if (isset($data['observaciones']))
        $bebe->setObservaciones($data['observaciones']);

    // Guardar o Actualizar
    if ($bebe->getId()) {
        $result = $bebeDAO->update($bebe);
        $message = 'Bebé actualizado correctamente';
    } else {
        $result = $bebeDAO->create($bebe);
        $message = 'Bebé registrado correctamente';
    }

    if ($result) {
        // Actualizar contadores del embarazo
        $embarazoDAO->actualizarContadores($bebe->getEmbarazoId());

        echo json_encode([
            'success' => true,
            'message' => $message,
            'id' => $bebe->getId()
        ]);
    } else {
        throw new Exception('Error al guardar el bebé en la base de datos');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
