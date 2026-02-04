<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../dao/AyudaDAO.php';
require_once __DIR__ . '/../../dao/MadreDAO.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

try {
    $ayudaDAO = new AyudaDAO();
    $madreDAO = new MadreDAO();
    
    // Verificar si se solicita una madre específica
    $madreId = isset($_GET['madre_id']) ? (int)$_GET['madre_id'] : null;
    
    if ($madreId) {
        // Obtener solo la madre específica
        $madre = $madreDAO->getById($madreId);
        if (!$madre) {
            throw new Exception('Madre no encontrada');
        }
        $madres = [$madre];
    } else {
        // Obtener todas las madres
        $totalMadres = $madreDAO->countAll([]);
        $madres = $madreDAO->getAll($totalMadres, 0, []);
    }
    
    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Ayudas por Madre');
    
    // Encabezados
    $headers = [
        'A1' => 'ID Madre',
        'B1' => 'Nombre Completo',
        'C1' => 'Documento',
        'D1' => 'Teléfono',
        'E1' => 'Estado',
        'F1' => 'Total Ayudas',
        'G1' => 'Valor Total ($)',
        'H1' => 'Última Ayuda',
        'I1' => 'Tipos de Ayuda Recibidos'
    ];
    
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }
    
    // Estilo de encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '16A085']
        ],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ];
    $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
    
    // Mapeo de tipos de ayuda
    $tiposAyuda = [
        'kit_recien_nacido' => 'Kit RN',
        'salud_recien_nacido' => 'Salud RN',
        'elementos_recien_nacido' => 'Elementos RN',
        'apoyo_economico' => 'Apoyo Econ.',
        'alimentacion' => 'Alimentación',
        'ropa' => 'Ropa',
        'medicamentos' => 'Medicamentos',
        'transporte' => 'Transporte',
        'otro' => 'Otro'
    ];
    
    // Datos
    $row = 2;
    $granTotalAyudas = 0;
    $granTotalValor = 0;
    
    foreach ($madres as $madre) {
        // Obtener ayudas de esta madre
        $ayudasMadre = $ayudaDAO->getByMadreId($madre->getId());
        
        $totalAyudas = count($ayudasMadre);
        $valorTotal = 0;
        $ultimaFecha = null;
        $tiposRecibidos = [];
        
        foreach ($ayudasMadre as $ayuda) {
            $valorTotal += $ayuda->getValor() ?? 0;
            
            // Determinar última fecha
            if ($ultimaFecha === null || $ayuda->getFechaRecepcion() > $ultimaFecha) {
                $ultimaFecha = $ayuda->getFechaRecepcion();
            }
            
            // Recopilar tipos únicos
            $tipo = $ayuda->getTipoAyuda();
            if (!in_array($tipo, $tiposRecibidos)) {
                $tiposRecibidos[] = $tipo;
            }
        }
        
        // Formatear tipos recibidos
        $tiposFormateados = array_map(function($tipo) use ($tiposAyuda) {
            return $tiposAyuda[$tipo] ?? $tipo;
        }, $tiposRecibidos);
        
        $nombreCompleto = $madre->getNombreCompleto();
        $documento = ($madre->getTipoDocumento() ?? '') . ' ' . ($madre->getNumeroDocumento() ?? '');
        $estado = $madre->isActiva() ? 'Activa' : 'Desvinculada';
        
        $sheet->setCellValue('A' . $row, $madre->getId());
        $sheet->setCellValue('B' . $row, $nombreCompleto);
        $sheet->setCellValue('C' . $row, trim($documento));
        $sheet->setCellValue('D' . $row, $madre->getNumeroTelefono());
        $sheet->setCellValue('E' . $row, $estado);
        $sheet->setCellValue('F' . $row, $totalAyudas);
        $sheet->setCellValue('G' . $row, $valorTotal);
        $sheet->setCellValue('H' . $row, $ultimaFecha ?? 'Sin ayudas');
        $sheet->setCellValue('I' . $row, implode(', ', $tiposFormateados));
        
        // Colorear fila según estado
        if (!$madre->isActiva()) {
            $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FADBD8']
                ]
            ]);
        }
        
        $granTotalAyudas += $totalAyudas;
        $granTotalValor += $valorTotal;
        $row++;
    }
    
    // Fila de totales
    $sheet->setCellValue('E' . $row, 'TOTALES:');
    $sheet->setCellValue('F' . $row, $granTotalAyudas);
    $sheet->setCellValue('G' . $row, $granTotalValor);
    $sheet->getStyle('E' . $row . ':G' . $row)->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'D5F5E3']
        ]
    ]);
    
    // Formato de moneda para columna G
    $sheet->getStyle('G2:G' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    
    // Autoajustar columnas
    foreach (range('A', 'I') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Generar archivo
    $filename = 'ayudas_por_madre_' . date('Y-m-d_His') . '.xlsx';
    
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
