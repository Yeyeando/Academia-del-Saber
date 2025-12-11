<?php

namespace App\Exports;

use App\Models\Curso;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

class CursosExport
{
    public function export()
{
    // Crear una nueva hoja de cálculo
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Títulos de las columnas
    $sheet->setCellValue('A1', 'ID')
        ->setCellValue('B1', 'Nombre')
        ->setCellValue('C1', 'Descripción')
        ->setCellValue('D1', 'Precio (€)')
        ->setCellValue('E1', 'Vacantes')
        ->setCellValue('F1', 'Fecha inicio')
        ->setCellValue('G1', 'Fecha fin');

    // Estilos para los encabezados
    $sheet->getStyle('A1:G1')->getFont()->setBold(true);
    $sheet->getStyle('A1:G1')->getFont()->setSize(12);
    $sheet->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID);
    $sheet->getStyle('A1:G1')->getFill()->getStartColor()->setRGB('28A745');
    $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Llenar los datos de los cursos
    $cursos = Curso::all();
    $row = 2;
    foreach ($cursos as $curso) {

        $sheet->setCellValue('A' . $row, $curso->id)
            ->setCellValue('B' . $row, $curso->nombre)
            ->setCellValue('C' . $row, $curso->descripcion ?? 'Sin descripción')
            ->setCellValue('D' . $row, number_format($curso->precio, 2, ',', '.') . ' €')
            ->setCellValue('E' . $row, $curso->vacantes)
            ->setCellValue('F' . $row, $curso->fecha_inicio)
            ->setCellValue('G' . $row, $curso->fecha_fin);
        $row++;
    }

    // Ajustar ancho de las columnas
    foreach (range('A', 'G') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Crear el archivo Excel
    $writer = new Xlsx($spreadsheet);

    // Descargar el archivo
    $fileName = 'cursos_' . date('Y-m-d_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');

    // Enviar el archivo al navegador
    $writer->save('php://output');
    exit();
}

}
