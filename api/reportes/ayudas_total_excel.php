<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../dao/AyudaDAO.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

try {
    $dao = new AyudaDAO();
    
    // Obtener todas las ayudas (sin límite)
    $total = $dao->countAll([]);
    $ayudas = $dao->getAll($total, 0, []);
    
    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Ayudas Entregadas');
    
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
        $nombreMadre = '';
        if ($ayuda->getMadre()) {
            $madre = $ayuda->getMadre();
            $nombreMadre = trim(
                ($madre->getPrimerNombre() ?? '') . ' ' .
                ($madre->getSegundoNombre() ?? '') . ' ' .
                ($madre->getPrimerApellido() ?? '') . ' ' .
                ($madre->getSegundoApellido() ?? '')
            );
        }
        
        $nombreBebe = '';
        if ($ayuda->getBebe()) {
            $nombreBebe = $ayuda->getBebe()->getNombre() ?? 'Sin nombre';
        }
        
        $tipoAyuda = $tiposAyuda[$ayuda->getTipoAyuda()] ?? $ayuda->getTipoAyuda();
        $origen = $origenes[$ayuda->getOrigenAyuda()] ?? $ayuda->getOrigenAyuda();
        $estado = $estados[$ayuda->getEstado()] ?? $ayuda->getEstado();
        
        $sheet->setCellValue('A' . $row, $ayuda->getId());
        $sheet->setCellValue('B' . $row, $ayuda->getFechaRecepcion());
        $sheet->setCellValue('C' . $row, $ayuda->getMadreId());
        $sheet->setCellValue('D' . $row, $nombreMadre);
        $sheet->setCellValue('E' . $row, $nombreBebe);
        $sheet->setCellValue('F' . $row, $tipoAyuda);
        $sheet->setCellValue('G' . $row, $origen);
        $sheet->setCellValue('H' . $row, $ayuda->getValor() ?? 0);
        $sheet->setCellValue('I' . $row, $estado);
        $sheet->setCellValue('J' . $row, $ayuda->getObservaciones());
        $sheet->setCellValue('K' . $row, $ayuda->getCreatedAt());
        
        $totalValor += $ayuda->getValor() ?? 0;
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
    $filename = 'ayudas_total_' . date('Y-m-d_His') . '.xlsx';
    
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
