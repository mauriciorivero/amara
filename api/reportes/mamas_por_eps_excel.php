<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../dao/MadreDAO.php';
require_once __DIR__ . '/../../dao/EpsDAO.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

try {
    $madreDAO = new MadreDAO();
    $epsDAO = new EpsDAO();
    
    // Obtener todas las madres
    $totalMadres = $madreDAO->countAll([]);
    $madres = $madreDAO->getAll($totalMadres, 0, []);
    
    // Agrupar madres por EPS
    $madresPorEps = [];
    $sinEps = [];
    
    foreach ($madres as $madre) {
        $eps = $madre->getEps();
        if ($eps) {
            $epsNombre = $eps->getNombre();
            if (!isset($madresPorEps[$epsNombre])) {
                $madresPorEps[$epsNombre] = [];
            }
            $madresPorEps[$epsNombre][] = $madre;
        } else {
            $sinEps[] = $madre;
        }
    }
    
    // Ordenar por nombre de EPS
    ksort($madresPorEps);
    
    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Mamás por EPS');
    
    // Encabezados
    $headers = [
        'A1' => 'EPS',
        'B1' => 'ID Madre',
        'C1' => 'Nombre Completo',
        'D1' => 'Documento',
        'E1' => 'Teléfono',
        'F1' => 'Edad',
        'G1' => 'Estado',
        'H1' => 'Fecha Ingreso'
    ];
    
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }
    
    // Estilo de encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '00BCD4']
        ],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ];
    $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
    
    // Datos
    $row = 2;
    $resumenEps = [];
    
    foreach ($madresPorEps as $epsNombre => $madresEps) {
        $resumenEps[$epsNombre] = count($madresEps);
        $firstRow = true;
        
        foreach ($madresEps as $madre) {
            $nombreCompleto = trim(
                ($madre->getPrimerNombre() ?? '') . ' ' .
                ($madre->getSegundoNombre() ?? '') . ' ' .
                ($madre->getPrimerApellido() ?? '') . ' ' .
                ($madre->getSegundoApellido() ?? '')
            );
            
            $documento = ($madre->getTipoDocumento() ?? '') . ' ' . ($madre->getNumeroDocumento() ?? '');
            $estado = $madre->isActiva() ? 'Activa' : 'Desvinculada';
            
            $sheet->setCellValue('A' . $row, $firstRow ? $epsNombre : '');
            $sheet->setCellValue('B' . $row, $madre->getId());
            $sheet->setCellValue('C' . $row, $nombreCompleto);
            $sheet->setCellValue('D' . $row, trim($documento));
            $sheet->setCellValue('E' . $row, $madre->getNumeroTelefono());
            $sheet->setCellValue('F' . $row, $madre->getEdad());
            $sheet->setCellValue('G' . $row, $estado);
            $sheet->setCellValue('H' . $row, $madre->getFechaIngreso());
            
            if ($firstRow) {
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E0F7FA']
                    ]
                ]);
                $firstRow = false;
            }
            
            $row++;
        }
    }
    
    // Agregar madres sin EPS
    if (count($sinEps) > 0) {
        $resumenEps['Sin EPS'] = count($sinEps);
        $firstRow = true;
        
        foreach ($sinEps as $madre) {
            $nombreCompleto = trim(
                ($madre->getPrimerNombre() ?? '') . ' ' .
                ($madre->getSegundoNombre() ?? '') . ' ' .
                ($madre->getPrimerApellido() ?? '') . ' ' .
                ($madre->getSegundoApellido() ?? '')
            );
            
            $documento = ($madre->getTipoDocumento() ?? '') . ' ' . ($madre->getNumeroDocumento() ?? '');
            $estado = $madre->isActiva() ? 'Activa' : 'Desvinculada';
            
            $sheet->setCellValue('A' . $row, $firstRow ? 'Sin EPS' : '');
            $sheet->setCellValue('B' . $row, $madre->getId());
            $sheet->setCellValue('C' . $row, $nombreCompleto);
            $sheet->setCellValue('D' . $row, trim($documento));
            $sheet->setCellValue('E' . $row, $madre->getNumeroTelefono());
            $sheet->setCellValue('F' . $row, $madre->getEdad());
            $sheet->setCellValue('G' . $row, $estado);
            $sheet->setCellValue('H' . $row, $madre->getFechaIngreso());
            
            if ($firstRow) {
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'italic' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFECB3']
                    ]
                ]);
                $firstRow = false;
            }
            
            $row++;
        }
    }
    
    // Hoja de resumen
    $sheetResumen = $spreadsheet->createSheet();
    $sheetResumen->setTitle('Resumen por EPS');
    
    $sheetResumen->setCellValue('A1', 'EPS');
    $sheetResumen->setCellValue('B1', 'Cantidad de Mamás');
    $sheetResumen->getStyle('A1:B1')->applyFromArray($headerStyle);
    
    $rowResumen = 2;
    $totalGeneral = 0;
    foreach ($resumenEps as $eps => $cantidad) {
        $sheetResumen->setCellValue('A' . $rowResumen, $eps);
        $sheetResumen->setCellValue('B' . $rowResumen, $cantidad);
        $totalGeneral += $cantidad;
        $rowResumen++;
    }
    
    $sheetResumen->setCellValue('A' . $rowResumen, 'TOTAL');
    $sheetResumen->setCellValue('B' . $rowResumen, $totalGeneral);
    $sheetResumen->getStyle('A' . $rowResumen . ':B' . $rowResumen)->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'B2EBF2']
        ]
    ]);
    
    $sheetResumen->getColumnDimension('A')->setAutoSize(true);
    $sheetResumen->getColumnDimension('B')->setAutoSize(true);
    
    // Autoajustar columnas de la hoja principal
    $spreadsheet->setActiveSheetIndex(0);
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Generar archivo
    $filename = 'mamas_por_eps_' . date('Y-m-d_His') . '.xlsx';
    
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
