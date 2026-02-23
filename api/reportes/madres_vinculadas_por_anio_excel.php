<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../dao/MadreDAO.php';
require_once __DIR__ . '/../../config/Database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

try {
    // Validar parámetro de año
    if (!isset($_GET['anio']) || empty($_GET['anio'])) {
        throw new Exception('El parámetro año es requerido');
    }
    
    $anio = (int) $_GET['anio'];
    
    if ($anio < 2000 || $anio > 2100) {
        throw new Exception('Año no válido');
    }
    
    $conn = Database::getInstance()->getConnection();
    
    // Obtener madres vinculadas del año especificado
    $sql = "SELECT m.*, 
                   o.nombre as orientadora_nombre,
                   a.nombre as aliado_nombre,
                   e.nombre as eps_nombre
            FROM madres m
            LEFT JOIN orientadoras o ON m.orientadora_id = o.id
            LEFT JOIN aliados a ON m.aliado_id = a.id
            LEFT JOIN eps e ON m.eps_id = e.id
            WHERE (m.desvinculo IS NULL OR m.desvinculo = '')
            AND YEAR(m.fecha_ingreso) = :anio
            ORDER BY m.fecha_ingreso DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
    $stmt->execute();
    $madres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Madres Vinculadas ' . $anio);
    
    // Encabezados
    $headers = [
        'A1' => 'ID',
        'B1' => 'Fecha Ingreso',
        'C1' => 'Primer Nombre',
        'D1' => 'Segundo Nombre',
        'E1' => 'Primer Apellido',
        'F1' => 'Segundo Apellido',
        'G1' => 'Tipo Documento',
        'H1' => 'Número Documento',
        'I1' => 'Fecha Nacimiento',
        'J1' => 'Edad',
        'K1' => 'Sexo',
        'L1' => 'Teléfono',
        'M1' => 'Otro Contacto',
        'N1' => 'Número Hijos',
        'O1' => 'Pérdidas',
        'P1' => 'Estado Civil',
        'Q1' => 'Nombre Pareja',
        'R1' => 'Teléfono Pareja',
        'S1' => 'Nivel Estudio',
        'T1' => 'Ocupación',
        'U1' => 'Religión',
        'V1' => 'EPS',
        'W1' => 'SISBEN',
        'X1' => 'Orientadora',
        'Y1' => 'Aliado',
        'Z1' => 'Novedades'
    ];
    
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }
    
    // Estilo de encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '27ae60']
        ],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ];
    $sheet->getStyle('A1:Z1')->applyFromArray($headerStyle);
    
    // Datos
    $row = 2;
    foreach ($madres as $madre) {
        $sheet->setCellValue('A' . $row, $madre['id']);
        $sheet->setCellValue('B' . $row, $madre['fecha_ingreso']);
        $sheet->setCellValue('C' . $row, $madre['primer_nombre']);
        $sheet->setCellValue('D' . $row, $madre['segundo_nombre']);
        $sheet->setCellValue('E' . $row, $madre['primer_apellido']);
        $sheet->setCellValue('F' . $row, $madre['segundo_apellido']);
        $sheet->setCellValue('G' . $row, $madre['tipo_documento']);
        $sheet->setCellValue('H' . $row, $madre['numero_documento']);
        $sheet->setCellValue('I' . $row, $madre['fecha_nacimiento']);
        $sheet->setCellValue('J' . $row, $madre['edad']);
        $sheet->setCellValue('K' . $row, $madre['sexo']);
        $sheet->setCellValue('L' . $row, $madre['numero_telefono']);
        $sheet->setCellValue('M' . $row, $madre['otro_contacto']);
        $sheet->setCellValue('N' . $row, $madre['numero_hijos']);
        $sheet->setCellValue('O' . $row, $madre['perdidas']);
        $sheet->setCellValue('P' . $row, $madre['estado_civil']);
        $sheet->setCellValue('Q' . $row, $madre['nombre_pareja']);
        $sheet->setCellValue('R' . $row, $madre['telefono_pareja']);
        $sheet->setCellValue('S' . $row, $madre['nivel_estudio']);
        $sheet->setCellValue('T' . $row, $madre['ocupacion']);
        $sheet->setCellValue('U' . $row, $madre['religion']);
        $sheet->setCellValue('V' . $row, $madre['eps_nombre']);
        $sheet->setCellValue('W' . $row, $madre['sisben']);
        $sheet->setCellValue('X' . $row, $madre['orientadora_nombre']);
        $sheet->setCellValue('Y' . $row, $madre['aliado_nombre']);
        $sheet->setCellValue('Z' . $row, $madre['novedades']);
        $row++;
    }
    
    // Autoajustar columnas
    foreach (range('A', 'Z') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Generar archivo
    $filename = 'madres_vinculadas_' . $anio . '_' . date('Y-m-d_His') . '.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
}
