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
    
    // Obtener todos los bebés nacidos (estado = 'Nacido')
    $total = $dao->countAll('');
    $todosBebes = $dao->getAll($total, 0, '');
    
    // Filtrar solo los nacidos
    $bebes = array_filter($todosBebes, function($bebe) {
        return $bebe->getEstado() === 'Nacido';
    });
    
    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Bebés Nacidos');
    
    // Encabezados
    $headers = [
        'A1' => 'ID',
        'B1' => 'Nombre del Bebé',
        'C1' => 'Sexo',
        'D1' => 'Fecha Nacimiento',
        'E1' => 'Edad (días)',
        'F1' => 'Es Mellizo',
        'G1' => 'ID Madre',
        'H1' => 'Nombre Madre',
        'I1' => 'Observaciones',
        'J1' => 'Fecha Registro'
    ];
    
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }
    
    // Estilo de encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '3498DB']
        ],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ];
    $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
    
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
        
        // Calcular edad en días
        $edadDias = '';
        if ($bebe->getFechaNacimiento()) {
            $fechaNac = new DateTime($bebe->getFechaNacimiento());
            $hoy = new DateTime();
            $diff = $hoy->diff($fechaNac);
            $edadDias = $diff->days;
        }
        
        $sheet->setCellValue('A' . $row, $bebe->getId());
        $sheet->setCellValue('B' . $row, $bebe->getNombre() ?? 'Sin nombre');
        $sheet->setCellValue('C' . $row, $bebe->getSexo() ?? 'No especificado');
        $sheet->setCellValue('D' . $row, $bebe->getFechaNacimiento());
        $sheet->setCellValue('E' . $row, $edadDias);
        $sheet->setCellValue('F' . $row, $bebe->isEsMellizo() ? 'Sí' : 'No');
        $sheet->setCellValue('G' . $row, $bebe->getMadreId());
        $sheet->setCellValue('H' . $row, $nombreMadre);
        $sheet->setCellValue('I' . $row, $bebe->getObservaciones());
        $sheet->setCellValue('J' . $row, $bebe->getCreatedAt());
        
        $row++;
    }
    
    // Fila de totales
    $totalBebes = count($bebes);
    $sheet->setCellValue('A' . $row, 'TOTAL:');
    $sheet->setCellValue('B' . $row, $totalBebes . ' bebés nacidos');
    $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'D6EAF8']
        ]
    ]);
    
    // Autoajustar columnas
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Generar archivo
    $filename = 'bebes_nacidos_' . date('Y-m-d_His') . '.xlsx';
    
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
