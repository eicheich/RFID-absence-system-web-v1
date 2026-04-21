<?php

namespace App\Exports;

use App\Models\KpiScore;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KpiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private int $month,
        private int $year
    ) {}

    public function collection()
    {
        return KpiScore::with('employee')
            ->where('year', $this->year)
            ->where('month', $this->month)
            ->orderByDesc('total_score')
            ->get();
    }

 public function headings(): array
{
    return [
        'No', 'Kode Karyawan', 'Nama', 'Departemen', 'Jabatan',
        'Bulan', 'Tahun',
        'Rata-rata Penyelesaian Task (%)',
        'Hari Eligible (≥70%)',
        'Total Skor KPI',
        'Status KPI', 'Tap Out', 'Dihitung Pada',
    ];
}

public function map($row): array
{
    static $no = 0;
    $no++;

    return [
        $no,
        $row->employee->employee_code ?? '-',
        $row->employee->name ?? '-',
        $row->employee->department ?? '-',
        $row->employee->position ?? '-',
        \Carbon\Carbon::create()->month($row->month)->translatedFormat('F'),
        $row->year,
        number_format($row->attendance_score, 2),   // task completion rate
        number_format($row->punctuality_score, 2),  // % hari eligible
        number_format($row->total_score, 2),
        ucfirst($row->status),
        $row->tap_out_allowed ? 'Diizinkan' : 'Diblokir',
        $row->calculated_at?->format('d/m/Y H:i') ?? '-',
    ];
}

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->employee->employee_code ?? '-',
            $row->employee->name ?? '-',
            $row->employee->department ?? '-',
            $row->employee->position ?? '-',
            \Carbon\Carbon::create()->month($row->month)->translatedFormat('F'),
            $row->year,
            number_format($row->attendance_score, 2),
            number_format($row->punctuality_score, 2),
            number_format($row->total_score, 2),
            ucfirst($row->status),
            $row->tap_out_allowed ? 'Diizinkan' : 'Diblokir',
            $row->calculated_at?->format('d/m/Y H:i') ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'color' => ['rgb' => '1e293b']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function title(): string
    {
        return 'KPI ' . \Carbon\Carbon::create()->month($this->month)->translatedFormat('F') . ' ' . $this->year;
    }
}
