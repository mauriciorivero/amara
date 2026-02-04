<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../dao/MadreDAO.php';
require_once __DIR__ . '/../../dao/BebeDAO.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

try {
    $madreDAO = new MadreDAO();
    $bebeDAO = new BebeDAO();
    
    // Obtener todas las madres
    $totalMadres = $madreDAO->countAll([]);
    $madres = $madreDAO->getAll($totalMadres, 0, []);
    
    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Hijos por Mamá');
    
    // Encabezados
    $headers = [
        'A1' => 'ID Madre',
        'B1' => 'Nombre Madre',
        'C1' => 'Documento',
        'D1' => 'Estado Madre',
        'E1' => 'Total Hijos',
        'F1' => 'Hijos Masculinos',
        'G1' => 'Hijos Femeninos',
        'H1' => 'Por Nacer',
        'I1' => 'Nacidos',
        'J1' => 'Detalle Hijos'
    ];
    
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }
    
    // Estilo de encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FF5722']
        ],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ];
    $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
    
    // Totales generales
    $totalHijosGeneral = 0;
    $totalMasculinos = 0;
    $totalFemeninos = 0;
    $totalPorNacer = 0;
    $totalNacidos = 0;
    
    // Datos
    $row = 2;
    foreach ($madres as $madre) {
        $bebes = $bebeDAO->getByMadreId($madre->getId());
        
        if (count($bebes) === 0) {
            continue; // Saltar madres sin hijos registrados
        }
        
        $masculinos = 0;
        $femeninos = 0;
        $porNacer = 0;
        $nacidos = 0;
        $detalleHijos = [];
        
        foreach ($bebes as $bebe) {
            $sexo = $bebe->getSexo();
            if ($sexo === 'Masculino' || $sexo === 'M') {
                $masculinos++;
            } elseif ($sexo === 'Femenino' || $sexo === 'F') {
                $femeninos++;
            }
            
            if ($bebe->getEstado() === 'Por nacer') {
                $porNacer++;
            } elseif ($bebe->getEstado() === 'Nacido') {
                $nacidos++;
            }
            
            // Calcular edad
            $edad = '';
            if ($bebe->getFechaNacimiento() && $bebe->getEstado() === 'Nacido') {
                $fechaNac = new DateTime($bebe->getFechaNacimiento());
                $hoy = new DateTime();
                $diff = $hoy->diff($fechaNac);
                
                if ($diff->y > 0) {
                    $edad = $diff->y . 'a ' . $diff->m . 'm';
                } elseif ($diff->m > 0) {
                    $edad = $diff->m . 'm ' . $diff->d . 'd';
                } else {
                    $edad = $diff->d . 'd';
                }
            } else {
                $edad = 'Por nacer';
            }
            
            $nombreBebe = $bebe->getNombre() ?? 'Sin nombre';
            $sexoBebe = $bebe->getSexo() ?? '?';
            $detalleHijos[] = "{$nombreBebe} ({$sexoBebe}, {$edad})";
        }
        
        $nombreCompleto = trim(
            ($madre->getPrimerNombre() ?? '') . ' ' .
            ($madre->getSegundoNombre() ?? '') . ' ' .
            ($madre->getPrimerApellido() ?? '') . ' ' .
            ($madre->getSegundoApellido() ?? '')
        );
        
        $documento = ($madre->getTipoDocumento() ?? '') . ' ' . ($madre->getNumeroDocumento() ?? '');
        $estadoMadre = $madre->isActiva() ? 'Activa' : 'Desvinculada';
        
        $sheet->setCellValue('A' . $row, $madre->getId());
        $sheet->setCellValue('B' . $row, $nombreCompleto);
        $sheet->setCellValue('C' . $row, trim($documento));
        $sheet->setCellValue('D' . $row, $estadoMadre);
        $sheet->setCellValue('E' . $row, count($bebes));
        $sheet->setCellValue('F' . $row, $masculinos);
        $sheet->setCellValue('G' . $row, $femeninos);
        $sheet->setCellValue('H' . $row, $porNacer);
        $sheet->setCellValue('I' . $row, $nacidos);
        $sheet->setCellValue('J' . $row, implode(' | ', $detalleHijos));
        
        // Actualizar totales
        $totalHijosGeneral += count($bebes);
        $totalMasculinos += $masculinos;
        $totalFemeninos += $femeninos;
        $totalPorNacer += $porNacer;
        $totalNacidos += $nacidos;
        
        $row++;
    }
    
    // Fila de totales
    $sheet->setCellValue('D' . $row, 'TOTALES:');
    $sheet->setCellValue('E' . $row, $totalHijosGeneral);
    $sheet->setCellValue('F' . $row, $totalMasculinos);
    $sheet->setCellValue('G' . $row, $totalFemeninos);
    $sheet->setCellValue('H' . $row, $totalPorNacer);
    $sheet->setCellValue('I' . $row, $totalNacidos);
    $sheet->getStyle('D' . $row . ':I' . $row)->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FFCCBC']
        ]
    ]);
    
    // Autoajustar columnas
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Generar archivo
    $filename = 'hijos_por_mama_' . date('Y-m-d_His') . '.xlsx';
    
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
