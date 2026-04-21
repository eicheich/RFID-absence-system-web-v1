<?php

namespace App\Http\Controllers\HRD;

use App\Exports\AttendanceExport;
use App\Exports\KpiExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function attendance(Request $request)
    {
        $date     = $request->get('date', Carbon::today()->toDateString());
        $filename = 'absensi-' . $date . '.xlsx';

        return Excel::download(new AttendanceExport($date), $filename);
    }

    public function kpi(Request $request)
    {
        $month    = $request->get('month', Carbon::now()->month);
        $year     = $request->get('year',  Carbon::now()->year);
        $filename = 'kpi-' . $month . '-' . $year . '.xlsx';

        return Excel::download(new KpiExport($month, $year), $filename);
    }
}
