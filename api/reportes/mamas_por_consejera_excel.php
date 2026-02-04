<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../dao/MadreDAO.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

try {
    $madreDAO = new MadreDAO();
    
    // Obtener todas las madres
    $totalMadres = $madreDAO->countAll([]);
    $madres = $madreDAO->getAll($totalMadres, 0, []);
    
    // Agrupar madres por Orientadora/Consejera
    $madresPorConsejera = [];
    $sinConsejera = [];
    
    foreach ($madres as $madre) {
        $orientadora = $madre->getOrientadora();
        if ($orientadora) {
            $consejeraNombre = $orientadora->getNombre();
            if (!isset($madresPorConsejera[$consejeraNombre])) {
                $madresPorConsejera[$consejeraNombre] = [];
            }
            $madresPorConsejera[$consejeraNombre][] = $madre;
        } else {
            $sinConsejera[] = $madre;
        }
    }
    
    // Ordenar por nombre de consejera
    ksort($madresPorConsejera);
    
    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Mamás por Consejera');
    
    // Encabezados
    $headers = [
        'A1' => 'Consejera/Orientadora',
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
            'startColor' => ['rgb' => '673AB7']
        ],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ];
    $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
    
    // Datos
    $row = 2;
    $resumenConsejera = [];
    
    foreach ($madresPorConsejera as $consejeraNombre => $madresConsejera) {
        $resumenConsejera[$consejeraNombre] = [
            'total' => count($madresConsejera),
            'activas' => 0,
            'desvinculadas' => 0
        ];
        
        $firstRow = true;
        
        foreach ($madresConsejera as $madre) {
            $nombreCompleto = trim(
                ($madre->getPrimerNombre() ?? '') . ' ' .
                ($madre->getSegundoNombre() ?? '') . ' ' .
                ($madre->getPrimerApellido() ?? '') . ' ' .
                ($madre->getSegundoApellido() ?? '')
            );
            
            $documento = ($madre->getTipoDocumento() ?? '') . ' ' . ($madre->getNumeroDocumento() ?? '');
            $estado = $madre->isActiva() ? 'Activa' : 'Desvinculada';
            
            if ($madre->isActiva()) {
                $resumenConsejera[$consejeraNombre]['activas']++;
            } else {
                $resumenConsejera[$consejeraNombre]['desvinculadas']++;
            }
            
            $sheet->setCellValue('A' . $row, $firstRow ? $consejeraNombre : '');
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
                        'startColor' => ['rgb' => 'EDE7F6']
                    ]
                ]);
                $firstRow = false;
            }
            
            $row++;
        }
    }
    
    // Agregar madres sin consejera
    if (count($sinConsejera) > 0) {
        $resumenConsejera['Sin Consejera Asignada'] = [
            'total' => count($sinConsejera),
            'activas' => 0,
            'desvinculadas' => 0
        ];
        
        $firstRow = true;
        
        foreach ($sinConsejera as $madre) {
            $nombreCompleto = trim(
                ($madre->getPrimerNombre() ?? '') . ' ' .
                ($madre->getSegundoNombre() ?? '') . ' ' .
                ($madre->getPrimerApellido() ?? '') . ' ' .
                ($madre->getSegundoApellido() ?? '')
            );
            
            $documento = ($madre->getTipoDocumento() ?? '') . ' ' . ($madre->getNumeroDocumento() ?? '');
            $estado = $madre->isActiva() ? 'Activa' : 'Desvinculada';
            
            if ($madre->isActiva()) {
                $resumenConsejera['Sin Consejera Asignada']['activas']++;
            } else {
                $resumenConsejera['Sin Consejera Asignada']['desvinculadas']++;
            }
            
            $sheet->setCellValue('A' . $row, $firstRow ? 'Sin Consejera Asignada' : '');
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
    $sheetResumen->setTitle('Resumen por Consejera');
    
    $sheetResumen->setCellValue('A1', 'Consejera/Orientadora');
    $sheetResumen->setCellValue('B1', 'Total Mamás');
    $sheetResumen->setCellValue('C1', 'Activas');
    $sheetResumen->setCellValue('D1', 'Desvinculadas');
    $sheetResumen->getStyle('A1:D1')->applyFromArray($headerStyle);
    
    $rowResumen = 2;
    $totalGeneral = 0;
    $totalActivas = 0;
    $totalDesvinculadas = 0;
    
    foreach ($resumenConsejera as $consejera => $datos) {
        $sheetResumen->setCellValue('A' . $rowResumen, $consejera);
        $sheetResumen->setCellValue('B' . $rowResumen, $datos['total']);
        $sheetResumen->setCellValue('C' . $rowResumen, $datos['activas']);
        $sheetResumen->setCellValue('D' . $rowResumen, $datos['desvinculadas']);
        $totalGeneral += $datos['total'];
        $totalActivas += $datos['activas'];
        $totalDesvinculadas += $datos['desvinculadas'];
        $rowResumen++;
    }
    
    $sheetResumen->setCellValue('A' . $rowResumen, 'TOTAL');
    $sheetResumen->setCellValue('B' . $rowResumen, $totalGeneral);
    $sheetResumen->setCellValue('C' . $rowResumen, $totalActivas);
    $sheetResumen->setCellValue('D' . $rowResumen, $totalDesvinculadas);
    $sheetResumen->getStyle('A' . $rowResumen . ':D' . $rowResumen)->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'D1C4E9']
        ]
    ]);
    
    foreach (range('A', 'D') as $col) {
        $sheetResumen->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Autoajustar columnas de la hoja principal
    $spreadsheet->setActiveSheetIndex(0);
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Generar archivo
    $filename = 'mamas_por_consejera_' . date('Y-m-d_His') . '.xlsx';
    
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
