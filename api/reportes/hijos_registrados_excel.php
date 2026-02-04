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
    
    // Obtener todos los bebés/hijos registrados
    $total = $dao->countAll('');
    $bebes = $dao->getAll($total, 0, '');
    
    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Hijos Registrados');
    
    // Encabezados
    $headers = [
        'A1' => 'ID',
        'B1' => 'Nombre',
        'C1' => 'Sexo',
        'D1' => 'Fecha Nacimiento',
        'E1' => 'Edad',
        'F1' => 'Estado',
        'G1' => 'Es Mellizo',
        'H1' => 'ID Madre',
        'I1' => 'Nombre Madre',
        'J1' => 'Observaciones'
    ];
    
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }
    
    // Estilo de encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '9C27B0']
        ],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ];
    $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
    
    // Contadores por sexo
    $contadorMasculino = 0;
    $contadorFemenino = 0;
    $contadorNoEspecificado = 0;
    
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
        
        // Calcular edad
        $edad = 'N/A';
        if ($bebe->getFechaNacimiento() && $bebe->getEstado() === 'Nacido') {
            $fechaNac = new DateTime($bebe->getFechaNacimiento());
            $hoy = new DateTime();
            $diff = $hoy->diff($fechaNac);
            
            if ($diff->y > 0) {
                $edad = $diff->y . ' año(s), ' . $diff->m . ' mes(es)';
            } elseif ($diff->m > 0) {
                $edad = $diff->m . ' mes(es), ' . $diff->d . ' día(s)';
            } else {
                $edad = $diff->d . ' día(s)';
            }
        } elseif ($bebe->getEstado() === 'Por nacer') {
            $edad = 'Por nacer';
        }
        
        // Contar por sexo
        $sexo = $bebe->getSexo();
        if ($sexo === 'Masculino' || $sexo === 'M') {
            $contadorMasculino++;
        } elseif ($sexo === 'Femenino' || $sexo === 'F') {
            $contadorFemenino++;
        } else {
            $contadorNoEspecificado++;
        }
        
        $sheet->setCellValue('A' . $row, $bebe->getId());
        $sheet->setCellValue('B' . $row, $bebe->getNombre() ?? 'Sin nombre');
        $sheet->setCellValue('C' . $row, $sexo ?? 'No especificado');
        $sheet->setCellValue('D' . $row, $bebe->getFechaNacimiento() ?? 'N/A');
        $sheet->setCellValue('E' . $row, $edad);
        $sheet->setCellValue('F' . $row, $bebe->getEstado());
        $sheet->setCellValue('G' . $row, $bebe->isEsMellizo() ? 'Sí' : 'No');
        $sheet->setCellValue('H' . $row, $bebe->getMadreId());
        $sheet->setCellValue('I' . $row, $nombreMadre);
        $sheet->setCellValue('J' . $row, $bebe->getObservaciones());
        
        // Colorear fila según sexo
        if ($sexo === 'Masculino' || $sexo === 'M') {
            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E3F2FD']
                ]
            ]);
        } elseif ($sexo === 'Femenino' || $sexo === 'F') {
            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FCE4EC']
                ]
            ]);
        }
        
        $row++;
    }
    
    // Resumen
    $row++;
    $sheet->setCellValue('A' . $row, 'RESUMEN POR SEXO:');
    $sheet->getStyle('A' . $row)->applyFromArray(['font' => ['bold' => true]]);
    $row++;
    $sheet->setCellValue('A' . $row, 'Masculino:');
    $sheet->setCellValue('B' . $row, $contadorMasculino);
    $row++;
    $sheet->setCellValue('A' . $row, 'Femenino:');
    $sheet->setCellValue('B' . $row, $contadorFemenino);
    $row++;
    $sheet->setCellValue('A' . $row, 'No especificado:');
    $sheet->setCellValue('B' . $row, $contadorNoEspecificado);
    $row++;
    $sheet->setCellValue('A' . $row, 'TOTAL:');
    $sheet->setCellValue('B' . $row, count($bebes));
    $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray(['font' => ['bold' => true]]);
    
    // Autoajustar columnas
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Generar archivo
    $filename = 'hijos_registrados_' . date('Y-m-d_His') . '.xlsx';
    
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
