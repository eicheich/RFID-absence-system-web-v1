<?php

namespace App\Exports;

use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private string $date,
        private ?int $employeeId = null
    ) {}

    public function collection()
    {
        $query = Attendance::with('employee')
            ->whereDate('date', $this->date)
            ->orderBy('date');

        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Karyawan',
            'Nama',
            'Departemen',
            'Jabatan',
            'Tanggal',
            'Tap In',
            'Tap Out',
            'Durasi (menit)',
            'Status',
            'Catatan',
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
            $row->date->format('d/m/Y'),
            $row->tap_in?->format('H:i:s') ?? '-',
            $row->tap_out?->format('H:i:s') ?? '-',
            $row->work_duration ?? 0,
            ucfirst($row->status),
            $row->notes ?? '-',
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
        return 'Absensi ' . $this->date;
    }
}
