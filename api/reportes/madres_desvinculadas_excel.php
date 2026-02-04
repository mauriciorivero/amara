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
    $dao = new MadreDAO();
    
    // Obtener todas las madres desvinculadas
    $filters = ['estado' => 'desvinculada'];
    $total = $dao->countAll($filters);
    $madres = $dao->getAll($total, 0, $filters);
    
    // Crear spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Madres Desvinculadas');
    
    // Encabezados
    $headers = [
        'A1' => 'ID',
        'B1' => 'Fecha Ingreso',
        'C1' => 'Primer Nombre',
        'D1' => 'Segundo Nombre',
        'E1' => 'Primer Apellido',
        'F1' => 'Segundo Apellido',
        'G1' => 'Tipo Documento',
        'H1' => 'Número Documento',
        'I1' => 'Fecha Nacimiento',
        'J1' => 'Edad',
        'K1' => 'Sexo',
        'L1' => 'Teléfono',
        'M1' => 'Otro Contacto',
        'N1' => 'Número Hijos',
        'O1' => 'Pérdidas',
        'P1' => 'Estado Civil',
        'Q1' => 'Nombre Pareja',
        'R1' => 'Teléfono Pareja',
        'S1' => 'Nivel Estudio',
        'T1' => 'Ocupación',
        'U1' => 'Religión',
        'V1' => 'EPS',
        'W1' => 'SISBEN',
        'X1' => 'Orientadora',
        'Y1' => 'Aliado',
        'Z1' => 'Motivo Desvinculación',
        'AA1' => 'Novedades'
    ];
    
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }
    
    // Estilo de encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'C0392B']
        ],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ];
    $sheet->getStyle('A1:AA1')->applyFromArray($headerStyle);
    
    // Datos
    $row = 2;
    foreach ($madres as $madre) {
        $sheet->setCellValue('A' . $row, $madre->getId());
        $sheet->setCellValue('B' . $row, $madre->getFechaIngreso());
        $sheet->setCellValue('C' . $row, $madre->getPrimerNombre());
        $sheet->setCellValue('D' . $row, $madre->getSegundoNombre());
        $sheet->setCellValue('E' . $row, $madre->getPrimerApellido());
        $sheet->setCellValue('F' . $row, $madre->getSegundoApellido());
        $sheet->setCellValue('G' . $row, $madre->getTipoDocumento());
        $sheet->setCellValue('H' . $row, $madre->getNumeroDocumento());
        $sheet->setCellValue('I' . $row, $madre->getFechaNacimiento());
        $sheet->setCellValue('J' . $row, $madre->getEdad());
        $sheet->setCellValue('K' . $row, $madre->getSexo());
        $sheet->setCellValue('L' . $row, $madre->getNumeroTelefono());
        $sheet->setCellValue('M' . $row, $madre->getOtroContacto());
        $sheet->setCellValue('N' . $row, $madre->getNumeroHijos());
        $sheet->setCellValue('O' . $row, $madre->getPerdidas());
        $sheet->setCellValue('P' . $row, $madre->getEstadoCivil());
        $sheet->setCellValue('Q' . $row, $madre->getNombrePareja());
        $sheet->setCellValue('R' . $row, $madre->getTelefonoPareja());
        $sheet->setCellValue('S' . $row, $madre->getNivelEstudio());
        $sheet->setCellValue('T' . $row, $madre->getOcupacion());
        $sheet->setCellValue('U' . $row, $madre->getReligion());
        $sheet->setCellValue('V' . $row, $madre->getEps() ? $madre->getEps()->getNombre() : '');
        $sheet->setCellValue('W' . $row, $madre->getSisben());
        $sheet->setCellValue('X' . $row, $madre->getOrientadora() ? $madre->getOrientadora()->getNombre() : '');
        $sheet->setCellValue('Y' . $row, $madre->getAliado() ? $madre->getAliado()->getNombre() : '');
        $sheet->setCellValue('Z' . $row, $madre->getDesvinculo());
        $sheet->setCellValue('AA' . $row, $madre->getNovedades());
        $row++;
    }
    
    // Autoajustar columnas
    foreach (range('A', 'Z') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    $sheet->getColumnDimension('AA')->setAutoSize(true);
    
    // Generar archivo
    $filename = 'madres_desvinculadas_' . date('Y-m-d_His') . '.xlsx';
    
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
