<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/Database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

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
    
    // Obtener ayudas del año especificado
    $sql = "SELECT a.*, 
                   m.primer_nombre, m.segundo_nombre, m.primer_apellido, m.segundo_apellido,
                   b.nombre as bebe_nombre
            FROM ayudas a
            LEFT JOIN madres m ON a.madre_id = m.id
            LEFT JOIN bebes b ON a.bebe_id = b.id
            WHERE YEAR(a.created_at) = :anio
            ORDER BY a.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
    $stmt->execute();
    $ayudas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Ayudas ' . $anio);
    
    // Encabezados
    $headers = [
        'A1' => 'ID',
        'B1' => 'Fecha Recepción',
        'C1' => 'Madre ID',
        'D1' => 'Nombre Madre',
        'E1' => 'Bebé',
        'F1' => 'Tipo de Ayuda',
        'G1' => 'Origen',
        'H1' => 'Valor ($)',
        'I1' => 'Estado',
        'J1' => 'Observaciones',
        'K1' => 'Fecha Registro'
    ];
    
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }
    
    // Estilo de encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '8E44AD']
        ],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ];
    $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);
    
    // Mapeo de tipos de ayuda
    $tiposAyuda = [
        'kit_recien_nacido' => 'Kit Recién Nacido',
        'salud_recien_nacido' => 'Salud Recién Nacido',
        'elementos_recien_nacido' => 'Elementos Recién Nacido',
        'apoyo_economico' => 'Apoyo Económico',
        'alimentacion' => 'Alimentación',
        'ropa' => 'Ropa',
        'medicamentos' => 'Medicamentos',
        'transporte' => 'Transporte',
        'otro' => 'Otro'
    ];
    
    // Mapeo de orígenes
    $origenes = [
        'corporacion' => 'Corporación',
        'aliado' => 'Aliado'
    ];
    
    // Mapeo de estados
    $estados = [
        'pendiente' => 'Pendiente',
        'entregada' => 'Entregada',
        'cancelada' => 'Cancelada'
    ];
    
    // Datos
    $row = 2;
    $totalValor = 0;
    foreach ($ayudas as $ayuda) {
        $nombreMadre = trim(
            ($ayuda['primer_nombre'] ?? '') . ' ' .
            ($ayuda['segundo_nombre'] ?? '') . ' ' .
            ($ayuda['primer_apellido'] ?? '') . ' ' .
            ($ayuda['segundo_apellido'] ?? '')
        );
        
        $nombreBebe = $ayuda['bebe_nombre'] ?? '';
        
        $tipoAyuda = $tiposAyuda[$ayuda['tipo_ayuda']] ?? $ayuda['tipo_ayuda'];
        $origen = $origenes[$ayuda['origen_ayuda']] ?? $ayuda['origen_ayuda'];
        $estado = $estados[$ayuda['estado']] ?? $ayuda['estado'];
        
        $sheet->setCellValue('A' . $row, $ayuda['id']);
        $sheet->setCellValue('B' . $row, $ayuda['fecha_recepcion']);
        $sheet->setCellValue('C' . $row, $ayuda['madre_id']);
        $sheet->setCellValue('D' . $row, $nombreMadre);
        $sheet->setCellValue('E' . $row, $nombreBebe);
        $sheet->setCellValue('F' . $row, $tipoAyuda);
        $sheet->setCellValue('G' . $row, $origen);
        $sheet->setCellValue('H' . $row, $ayuda['valor'] ?? 0);
        $sheet->setCellValue('I' . $row, $estado);
        $sheet->setCellValue('J' . $row, $ayuda['observaciones']);
        $sheet->setCellValue('K' . $row, $ayuda['created_at']);
        
        $totalValor += $ayuda['valor'] ?? 0;
        $row++;
    }
    
    // Fila de totales
    $sheet->setCellValue('G' . $row, 'TOTAL:');
    $sheet->setCellValue('H' . $row, $totalValor);
    $sheet->getStyle('G' . $row . ':H' . $row)->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'E8DAEF']
        ]
    ]);
    
    // Formato de moneda para columna H
    $sheet->getStyle('H2:H' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    
    // Autoajustar columnas
    foreach (range('A', 'K') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Generar archivo
    $filename = 'ayudas_' . $anio . '_' . date('Y-m-d_His') . '.xlsx';
    
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
