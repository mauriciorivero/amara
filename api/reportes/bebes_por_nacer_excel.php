<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../dao/BebeDAO.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

try {
    $dao = new BebeDAO();
    
    // Obtener todos los bebés
    $total = $dao->countAll('');
    $todosBebes = $dao->getAll($total, 0, '');
    
    // Filtrar solo los que están por nacer
    $bebes = array_filter($todosBebes, function($bebe) {
        return $bebe->getEstado() === 'Por nacer';
    });
    
    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Bebés Por Nacer');
    
    // Encabezados
    $headers = [
        'A1' => 'ID',
        'B1' => 'Nombre (si tiene)',
        'C1' => 'Sexo (si se conoce)',
        'D1' => 'Es Mellizo',
        'E1' => 'ID Madre',
        'F1' => 'Nombre Madre',
        'G1' => 'ID Embarazo',
        'H1' => 'Observaciones',
        'I1' => 'Fecha Registro'
    ];
    
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }
    
    // Estilo de encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'E91E63']
        ],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ];
    $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
    
    // Datos
    $row = 2;
    foreach ($bebes as $bebe) {
        $nombreMadre = '';
        if ($bebe->getMadre()) {
            $madre = $bebe->getMadre();
            $nombreMadre = trim(
                ($madre->getPrimerNombre() ?? '') . ' ' .
                ($madre->getSegundoNombre() ?? '') . ' ' .
                ($madre->getPrimerApellido() ?? '') . ' ' .
                ($madre->getSegundoApellido() ?? '')
            );
        }
        
        $sheet->setCellValue('A' . $row, $bebe->getId());
        $sheet->setCellValue('B' . $row, $bebe->getNombre() ?? 'Por definir');
        $sheet->setCellValue('C' . $row, $bebe->getSexo() ?? 'Por determinar');
        $sheet->setCellValue('D' . $row, $bebe->isEsMellizo() ? 'Sí' : 'No');
        $sheet->setCellValue('E' . $row, $bebe->getMadreId());
        $sheet->setCellValue('F' . $row, $nombreMadre);
        $sheet->setCellValue('G' . $row, $bebe->getEmbarazoId());
        $sheet->setCellValue('H' . $row, $bebe->getObservaciones());
        $sheet->setCellValue('I' . $row, $bebe->getCreatedAt());
        
        $row++;
    }
    
    // Fila de totales
    $totalBebes = count($bebes);
    $sheet->setCellValue('A' . $row, 'TOTAL:');
    $sheet->setCellValue('B' . $row, $totalBebes . ' bebés por nacer');
    $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FCE4EC']
        ]
    ]);
    
    // Autoajustar columnas
    foreach (range('A', 'I') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Generar archivo
    $filename = 'bebes_por_nacer_' . date('Y-m-d_His') . '.xlsx';
    
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
