<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../dao/MadreDAO.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

try {
    $conn = Database::getInstance()->getConnection();
    $madreDAO = new MadreDAO();
    
    // Obtener todas las madres
    $totalMadres = $madreDAO->countAll([]);
    $madres = $madreDAO->getAll($totalMadres, 0, []);
    
    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Programas por Mamá');
    
    // Encabezados
    $headers = [
        'A1' => 'ID Madre',
        'B1' => 'Nombre Madre',
        'C1' => 'Documento',
        'D1' => 'Estado Madre',
        'E1' => 'Total Programas',
        'F1' => 'Programas Propios',
        'G1' => 'Programas Aliados',
        'H1' => 'Detalle Programas'
    ];
    
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }
    
    // Estilo de encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FF9800']
        ],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ];
    $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
    
    // Datos
    $row = 2;
    $totalProgramasGeneral = 0;
    $totalPropiosGeneral = 0;
    $totalAliadosGeneral = 0;
    $madresConProgramas = 0;
    $madresSinProgramas = 0;
    
    foreach ($madres as $madre) {
        // Obtener programas de esta madre
        $sql = "SELECT p.id, p.nombre, p.es_propio, mp.estado, mp.fecha_inscripcion,
                       a.nombre as aliado_nombre
                FROM madres_programas mp
                INNER JOIN programas p ON mp.programa_id = p.id
                LEFT JOIN aliados a ON p.aliado_id = a.id
                WHERE mp.madre_id = :madreId
                ORDER BY mp.fecha_inscripcion DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':madreId', $madre->getId(), PDO::PARAM_INT);
        $stmt->execute();
        $programasMadre = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $nombreCompleto = trim(
            ($madre->getPrimerNombre() ?? '') . ' ' .
            ($madre->getSegundoNombre() ?? '') . ' ' .
            ($madre->getPrimerApellido() ?? '') . ' ' .
            ($madre->getSegundoApellido() ?? '')
        );
        
        $documento = ($madre->getTipoDocumento() ?? '') . ' ' . ($madre->getNumeroDocumento() ?? '');
        $estadoMadre = $madre->isActiva() ? 'Activa' : 'Desvinculada';
        
        $totalProgramas = count($programasMadre);
        $programasPropios = 0;
        $programasAliados = 0;
        $detalleProgramas = [];
        
        foreach ($programasMadre as $prog) {
            if ($prog['es_propio']) {
                $programasPropios++;
                $detalleProgramas[] = $prog['nombre'] . ' (Propio)';
            } else {
                $programasAliados++;
                $aliadoNombre = $prog['aliado_nombre'] ?? 'Aliado';
                $detalleProgramas[] = $prog['nombre'] . ' (' . $aliadoNombre . ')';
            }
        }
        
        if ($totalProgramas > 0) {
            $madresConProgramas++;
        } else {
            $madresSinProgramas++;
        }
        
        $totalProgramasGeneral += $totalProgramas;
        $totalPropiosGeneral += $programasPropios;
        $totalAliadosGeneral += $programasAliados;
        
        $sheet->setCellValue('A' . $row, $madre->getId());
        $sheet->setCellValue('B' . $row, $nombreCompleto);
        $sheet->setCellValue('C' . $row, trim($documento));
        $sheet->setCellValue('D' . $row, $estadoMadre);
        $sheet->setCellValue('E' . $row, $totalProgramas);
        $sheet->setCellValue('F' . $row, $programasPropios);
        $sheet->setCellValue('G' . $row, $programasAliados);
        $sheet->setCellValue('H' . $row, implode(' | ', $detalleProgramas) ?: 'Sin programas');
        
        // Colorear según cantidad de programas
        if ($totalProgramas === 0) {
            $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFEBEE']
                ]
            ]);
        } elseif ($totalProgramas >= 3) {
            $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F5E9']
                ]
            ]);
        }
        
        $row++;
    }
    
    // Fila de totales
    $sheet->setCellValue('D' . $row, 'TOTALES:');
    $sheet->setCellValue('E' . $row, $totalProgramasGeneral);
    $sheet->setCellValue('F' . $row, $totalPropiosGeneral);
    $sheet->setCellValue('G' . $row, $totalAliadosGeneral);
    $sheet->getStyle('D' . $row . ':G' . $row)->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FFE0B2']
        ]
    ]);
    
    // Hoja de resumen
    $sheetResumen = $spreadsheet->createSheet();
    $sheetResumen->setTitle('Resumen General');
    
    $sheetResumen->setCellValue('A1', 'Indicador');
    $sheetResumen->setCellValue('B1', 'Valor');
    $sheetResumen->getStyle('A1:B1')->applyFromArray($headerStyle);
    
    $sheetResumen->setCellValue('A2', 'Total de Madres');
    $sheetResumen->setCellValue('B2', count($madres));
    $sheetResumen->setCellValue('A3', 'Madres con Programas');
    $sheetResumen->setCellValue('B3', $madresConProgramas);
    $sheetResumen->setCellValue('A4', 'Madres sin Programas');
    $sheetResumen->setCellValue('B4', $madresSinProgramas);
    $sheetResumen->setCellValue('A5', 'Total Inscripciones');
    $sheetResumen->setCellValue('B5', $totalProgramasGeneral);
    $sheetResumen->setCellValue('A6', 'Inscripciones Programas Propios');
    $sheetResumen->setCellValue('B6', $totalPropiosGeneral);
    $sheetResumen->setCellValue('A7', 'Inscripciones Programas Aliados');
    $sheetResumen->setCellValue('B7', $totalAliadosGeneral);
    $sheetResumen->setCellValue('A8', 'Promedio Programas por Madre');
    $sheetResumen->setCellValue('B8', count($madres) > 0 ? round($totalProgramasGeneral / count($madres), 2) : 0);
    
    $sheetResumen->getColumnDimension('A')->setAutoSize(true);
    $sheetResumen->getColumnDimension('B')->setAutoSize(true);
    
    // Autoajustar columnas de la hoja principal
    $spreadsheet->setActiveSheetIndex(0);
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Generar archivo
    $filename = 'programas_por_mamas_' . date('Y-m-d_His') . '.xlsx';
    
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
