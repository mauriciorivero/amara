<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../dao/MadreProgramaDAO.php';

try {
    $dao = new MadreProgramaDAO();

    // Total de inscripciones
    $totalInscripciones = $dao->countAll();

    // Estadísticas por estado
    $estados = ['inscrita', 'activa', 'completada', 'abandonada'];
    $porEstado = [];
    foreach ($estados as $estado) {
        $count = $dao->countAll(['estado' => $estado]);
        $porEstado[$estado] = $count;
    }

    // Programas con más madres (top 5)
    $conn = Database::getInstance()->getConnection();
    
    $sqlProgramas = "SELECT p.id, p.nombre, p.es_propio, COUNT(mp.id) as total_madres
                     FROM programas p
                     INNER JOIN madres_programas mp ON p.id = mp.programa_id
                     GROUP BY p.id
                     ORDER BY total_madres DESC
                     LIMIT 5";
    $stmtProgramas = $conn->query($sqlProgramas);
    $topProgramas = $stmtProgramas->fetchAll(PDO::FETCH_ASSOC);

    // Madres en múltiples programas (top 5)
    $sqlMadres = "SELECT m.id, 
                         CONCAT(m.primer_nombre, ' ', IFNULL(m.segundo_nombre, ''), ' ', 
                                m.primer_apellido, ' ', IFNULL(m.segundo_apellido, '')) as nombre_completo,
                         COUNT(mp.id) as total_programas
                  FROM madres m
                  INNER JOIN madres_programas mp ON m.id = mp.madre_id
                  GROUP BY m.id
                  HAVING total_programas > 1
                  ORDER BY total_programas DESC
                  LIMIT 5";
    $stmtMadres = $conn->query($sqlMadres);
    $topMadres = $stmtMadres->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'totalInscripciones' => $totalInscripciones,
            'porEstado' => $porEstado,
            'topProgramas' => $topProgramas,
            'topMadresMultiplesProgramas' => $topMadres
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

