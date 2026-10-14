<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use App\Models\Employee;
use App\Support\AuditLogger;
use App\Support\Format;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to, $department] = $this->filters($request);

        $rows = $this->summaryQuery($from, $to, $department)->get();

        $totals = [
            'total_sessions' => (int) $rows->sum('total_sessions'),
            'present_count' => (int) $rows->sum('present_count'),
            'late_count' => (int) $rows->sum('late_count'),
            'absent_count' => (int) $rows->sum('absent_count'),
            'work_minutes' => (int) $rows->sum('work_minutes'),
        ];

        $departments = Employee::query()
            ->select('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('reports.index', [
            'rows' => $rows,
            'totals' => $totals,
            'departments' => $departments,
            'from' => $from,
            'to' => $to,
            'department' => $department,
        ]);
    }

    public function exportSummary(Request $request)
    {
        [$from, $to, $department] = $this->filters($request);

        $rows = $this->summaryQuery($from, $to, $department)->get();

        AuditLogger::log(
            'report_exported',
            'Report',
            null,
            "Export rekap absensi {$from} s/d {$to}."
        );

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Kode', 'Nama', 'Departemen', 'Total Sesi',
                'Hadir', 'Terlambat', 'Tidak Hadir', 'Total Jam Kerja',
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $this->cell($r->employee_code),
                    $this->cell($r->full_name),
                    $this->cell($r->department),
                    $r->total_sessions,
                    $r->present_count,
                    $r->late_count,
                    $r->absent_count,
                    Format::minutes((int) $r->work_minutes),
                ]);
            }

            fclose($out);
        }, "rekap-absensi_{$from}_{$to}.csv", [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportSummaryPdf(Request $request)
    {
        [$from, $to, $department] = $this->filters($request);

        $rows = $this->summaryQuery($from, $to, $department)->get();

        $totals = [
            'total_sessions' => (int) $rows->sum('total_sessions'),
            'present_count' => (int) $rows->sum('present_count'),
            'late_count' => (int) $rows->sum('late_count'),
            'absent_count' => (int) $rows->sum('absent_count'),
            'work_minutes' => (int) $rows->sum('work_minutes'),
        ];

        AuditLogger::log(
            'report_exported',
            'Report',
            null,
            "Export PDF rekap absensi {$from} s/d {$to}."
        );

        return Pdf::loadView('reports.pdf-summary', [
            'rows' => $rows,
            'totals' => $totals,
            'from' => $from,
            'to' => $to,
            'department' => $department,
        ])->setPaper('a4', 'landscape')
          ->download("rekap-absensi_{$from}_{$to}.pdf");
    }

    private function filters(Request $request): array
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'department' => ['nullable', 'string', 'max:255'],
        ], [
            'date_from.date' => 'Tanggal awal tidak valid.',
            'date_to.date' => 'Tanggal akhir tidak valid.',
            'date_to.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ]);

        $from = $request->query('date_from')
            ?: Carbon::today()->startOfMonth()->toDateString();
        $to = $request->query('date_to')
            ?: Carbon::today()->toDateString();
        $department = trim((string) $request->query('department', ''));

        return [$from, $to, $department];
    }

    private function summaryQuery(string $from, string $to, string $department)
    {
        return AttendanceSession::query()
            ->join('employees', 'employees.id', '=', 'attendance_sessions.employee_id')
            ->where('attendance_sessions.work_date', '>=', $from)
            ->where('attendance_sessions.work_date', '<=', $to)
            ->when($department !== '', fn ($q) => $q->where('employees.department', $department))
            ->groupBy(
                'employees.id',
                'employees.employee_code',
                'employees.full_name',
                'employees.department'
            )
            ->selectRaw("
                employees.id,
                employees.employee_code,
                employees.full_name,
                employees.department,
                COUNT(*) as total_sessions,
                SUM(attendance_sessions.status = 'present') as present_count,
                SUM(attendance_sessions.status = 'late') as late_count,
                SUM(attendance_sessions.status = 'absent') as absent_count,
                COALESCE(SUM(
                    CASE WHEN attendance_sessions.check_out_at >= attendance_sessions.check_in_at
                    THEN TIMESTAMPDIFF(MINUTE, attendance_sessions.check_in_at, attendance_sessions.check_out_at)
                    ELSE 0 END
                ), 0) as work_minutes
            ")
            ->orderBy('employees.full_name');
    }

    /**
     * Cegah CSV/formula injection saat dibuka di Excel.
     */
    private function cell(mixed $value): string
    {
        $text = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $text) ? "'" . $text : $text;
    }
}