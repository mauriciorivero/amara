<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/AyudaDAO.php';

try {
    $dao = new AyudaDAO();

    // Total de ayudas registradas
    $totalAyudas = $dao->countAll();

    // Obtener estadísticas por tipo
    $porTipo = $dao->getEstadisticasPorTipo();

    // Obtener estadísticas por origen
    $porOrigen = $dao->getEstadisticasPorOrigen();

    // Obtener estadísticas por estado
    $porEstado = $dao->getEstadisticasPorEstado();

    // Top 5 madres con más ayudas
    $topMadres = $dao->getTopMadresConMasAyudas(5);

    // Ayudas del mes actual
    $mesActual = $dao->getAyudasMesActual();

    // Calcular valor total entregado (solo ayudas con estado 'entregada')
    $totalValorEntregado = 0;
    foreach ($porEstado as $estadoData) {
        if ($estadoData['estado'] === 'entregada') {
            $totalValorEntregado = (float) $estadoData['valor_total'];
            break;
        }
    }

    // Respuesta completa
    $data = [
        'totalAyudas' => $totalAyudas,
        'totalValorEntregado' => $totalValorEntregado,
        'estadisticasPorTipo' => $porTipo,
        'estadisticasPorOrigen' => $porOrigen,
        'estadisticasPorEstado' => $porEstado,
        'topMadresConMasAyudas' => $topMadres,
        'ayudasMesActual' => [
            'total' => (int) $mesActual['total'],
            'valorTotal' => (float) $mesActual['valor_total']
        ]
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

