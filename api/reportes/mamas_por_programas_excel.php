<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../dao/ProgramaDAO.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

try {
    $conn = Database::getInstance()->getConnection();
    $programaDAO = new ProgramaDAO();
    
    // Obtener todos los programas
    $totalProgramas = $programaDAO->countAll([]);
    $programas = $programaDAO->getAll($totalProgramas, 0, []);
    
    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Mamás por Programas');
    
    // Encabezados
    $headers = [
        'A1' => 'Programa',
        'B1' => 'Tipo',
        'C1' => 'Aliado',
        'D1' => 'ID Madre',
        'E1' => 'Nombre Madre',
        'F1' => 'Documento',
        'G1' => 'Estado Inscripción',
        'H1' => 'Fecha Inscripción'
    ];
    
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }
    
    // Estilo de encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '009688']
        ],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ];
    $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
    
    // Datos
    $row = 2;
    $resumenProgramas = [];
    
    foreach ($programas as $programa) {
        // Obtener madres inscritas en este programa
        $sql = "SELECT m.id, m.primer_nombre, m.segundo_nombre, m.primer_apellido, m.segundo_apellido,
                       m.tipo_documento, m.numero_documento, mp.estado, mp.fecha_inscripcion
                FROM madres_programas mp
                INNER JOIN madres m ON mp.madre_id = m.id
                WHERE mp.programa_id = :programaId
                ORDER BY mp.fecha_inscripcion DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':programaId', $programa->getId(), PDO::PARAM_INT);
        $stmt->execute();
        $madresPrograma = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $tipo = $programa->isEsPropio() ? 'Propio' : 'Aliado';
        $aliado = $programa->isEsPropio() ? 'Corporación' : ($programa->getAliado() ? $programa->getAliado()->getNombre() : 'N/A');
        
        $resumenProgramas[$programa->getNombre()] = [
            'tipo' => $tipo,
            'aliado' => $aliado,
            'total' => count($madresPrograma),
            'activas' => 0,
            'completadas' => 0,
            'retiradas' => 0
        ];
        
        if (count($madresPrograma) === 0) {
            // Mostrar programa sin madres
            $sheet->setCellValue('A' . $row, $programa->getNombre());
            $sheet->setCellValue('B' . $row, $tipo);
            $sheet->setCellValue('C' . $row, $aliado);
            $sheet->setCellValue('D' . $row, '-');
            $sheet->setCellValue('E' . $row, 'Sin madres inscritas');
            $sheet->setCellValue('F' . $row, '-');
            $sheet->setCellValue('G' . $row, '-');
            $sheet->setCellValue('H' . $row, '-');
            
            $sheet->getStyle('A' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0F2F1']
                ]
            ]);
            
            $row++;
            continue;
        }
        
        $firstRow = true;
        foreach ($madresPrograma as $madre) {
            $nombreCompleto = trim(
                ($madre['primer_nombre'] ?? '') . ' ' .
                ($madre['segundo_nombre'] ?? '') . ' ' .
                ($madre['primer_apellido'] ?? '') . ' ' .
                ($madre['segundo_apellido'] ?? '')
            );
            
            $documento = ($madre['tipo_documento'] ?? '') . ' ' . ($madre['numero_documento'] ?? '');
            $estadoInscripcion = ucfirst($madre['estado'] ?? 'inscrita');
            
            // Contar por estado
            $estadoLower = strtolower($madre['estado'] ?? 'inscrita');
            if ($estadoLower === 'activa' || $estadoLower === 'inscrita') {
                $resumenProgramas[$programa->getNombre()]['activas']++;
            } elseif ($estadoLower === 'completada' || $estadoLower === 'finalizada') {
                $resumenProgramas[$programa->getNombre()]['completadas']++;
            } else {
                $resumenProgramas[$programa->getNombre()]['retiradas']++;
            }
            
            $sheet->setCellValue('A' . $row, $firstRow ? $programa->getNombre() : '');
            $sheet->setCellValue('B' . $row, $firstRow ? $tipo : '');
            $sheet->setCellValue('C' . $row, $firstRow ? $aliado : '');
            $sheet->setCellValue('D' . $row, $madre['id']);
            $sheet->setCellValue('E' . $row, $nombreCompleto);
            $sheet->setCellValue('F' . $row, trim($documento));
            $sheet->setCellValue('G' . $row, $estadoInscripcion);
            $sheet->setCellValue('H' . $row, $madre['fecha_inscripcion']);
            
            if ($firstRow) {
                $bgColor = $programa->isEsPropio() ? 'E8F5E9' : 'FFF3E0';
                $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $bgColor]
                    ]
                ]);
                $firstRow = false;
            }
            
            $row++;
        }
    }
    
    // Hoja de resumen
    $sheetResumen = $spreadsheet->createSheet();
    $sheetResumen->setTitle('Resumen por Programa');
    
    $sheetResumen->setCellValue('A1', 'Programa');
    $sheetResumen->setCellValue('B1', 'Tipo');
    $sheetResumen->setCellValue('C1', 'Aliado');
    $sheetResumen->setCellValue('D1', 'Total Mamás');
    $sheetResumen->setCellValue('E1', 'Activas');
    $sheetResumen->setCellValue('F1', 'Completadas');
    $sheetResumen->setCellValue('G1', 'Retiradas');
    $sheetResumen->getStyle('A1:G1')->applyFromArray($headerStyle);
    
    $rowResumen = 2;
    $totalGeneral = 0;
    $totalActivas = 0;
    $totalCompletadas = 0;
    $totalRetiradas = 0;
    
    foreach ($resumenProgramas as $programa => $datos) {
        $sheetResumen->setCellValue('A' . $rowResumen, $programa);
        $sheetResumen->setCellValue('B' . $rowResumen, $datos['tipo']);
        $sheetResumen->setCellValue('C' . $rowResumen, $datos['aliado']);
        $sheetResumen->setCellValue('D' . $rowResumen, $datos['total']);
        $sheetResumen->setCellValue('E' . $rowResumen, $datos['activas']);
        $sheetResumen->setCellValue('F' . $rowResumen, $datos['completadas']);
        $sheetResumen->setCellValue('G' . $rowResumen, $datos['retiradas']);
        
        $totalGeneral += $datos['total'];
        $totalActivas += $datos['activas'];
        $totalCompletadas += $datos['completadas'];
        $totalRetiradas += $datos['retiradas'];
        $rowResumen++;
    }
    
    $sheetResumen->setCellValue('A' . $rowResumen, 'TOTAL');
    $sheetResumen->setCellValue('D' . $rowResumen, $totalGeneral);
    $sheetResumen->setCellValue('E' . $rowResumen, $totalActivas);
    $sheetResumen->setCellValue('F' . $rowResumen, $totalCompletadas);
    $sheetResumen->setCellValue('G' . $rowResumen, $totalRetiradas);
    $sheetResumen->getStyle('A' . $rowResumen . ':G' . $rowResumen)->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'B2DFDB']
        ]
    ]);
    
    foreach (range('A', 'G') as $col) {
        $sheetResumen->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Autoajustar columnas de la hoja principal
    $spreadsheet->setActiveSheetIndex(0);
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Generar archivo
    $filename = 'mamas_por_programas_' . date('Y-m-d_His') . '.xlsx';
    
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
