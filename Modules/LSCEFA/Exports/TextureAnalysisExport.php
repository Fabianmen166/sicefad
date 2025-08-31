<?php

namespace Modules\LSCEFA\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TextureAnalysisExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'ID Análisis',
            'Consecutivo',
            'Fecha Análisis',
            'Analista',
            'Metodología',
            'Equipo Utilizado',
            'Código Termómetro',
            'Código Hidrómetro',
            'Código Muestra',
            'Clase Textural',
            'Arena (%)',
            'Arcilla (%)',
            'Limo (%)',
            'Estado Revisión',
            'Observaciones',
            'Fecha Creación',
            'Tipo Control' // Nueva columna para identificar si es muestra o control
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // ID Análisis
            'B' => 15,  // Consecutivo
            'C' => 15,  // Fecha Análisis
            'D' => 20,  // Analista
            'E' => 20,  // Metodología
            'F' => 20,  // Equipo Utilizado
            'G' => 18,  // Código Termómetro
            'H' => 18,  // Código Hidrómetro
            'I' => 15,  // Código Muestra
            'J' => 20,  // Clase Textural
            'K' => 12,  // Arena (%)
            'L' => 12,  // Arcilla (%)
            'M' => 12,  // Limo (%)
            'N' => 18,  // Estado Revisión
            'O' => 30,  // Observaciones
            'P' => 20,  // Fecha Creación
            'Q' => 20,  // Tipo Control
        ];
    }
}
