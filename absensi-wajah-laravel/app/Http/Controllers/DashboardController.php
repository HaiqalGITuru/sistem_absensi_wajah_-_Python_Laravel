<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use App\Models\Employee;
use App\Models\WorkSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $totalEmployees = Employee::where('is_active', true)->count();

        $checkInsToday = AttendanceSession::whereDate('work_date', $today)
            ->whereNotNull('check_in_at')
            ->count();

        $checkOutsToday = AttendanceSession::whereDate('work_date', $today)
            ->whereNotNull('check_out_at')
            ->count();

        $latestAttendance = AttendanceSession::with(['employee', 'workSchedule'])
            ->whereDate('work_date', $today)
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $lateToday = AttendanceSession::whereDate('work_date', $today)
            ->where('status', 'terlambat')
            ->count();

        $notCheckedInQuery = Employee::where('is_active', true)
            ->whereDoesntHave('attendanceSessions', function ($q) use ($today) {
                $q->whereDate('work_date', $today)
                    ->whereNotNull('check_in_at');
            });

        $notCheckedInCount = (clone $notCheckedInQuery)->count();

        $notCheckedIn = $notCheckedInQuery
            ->with('workSchedule')
            ->orderBy('full_name')
            ->limit(8)
            ->get();

        $activeSchedules = WorkSchedule::where('is_active', true)->count();

        // Grafik check-in 7 hari terakhir
        $start = $today->copy()->subDays(6);

        $counts = DB::connection('python')->table('attendance_sessions')
            ->whereBetween('work_date', [
                $start->toDateString(),
                $today->toDateString(),
            ])
            ->whereNotNull('check_in_at')
            ->selectRaw('work_date, COUNT(DISTINCT employee_id) as total')
            ->groupBy('work_date')
            ->pluck('total', 'work_date');

        $max = max(1, (int) $counts->max());

        $weekly = collect(range(0, 6))->map(function ($i) use ($start, $counts, $max) {
            $date = $start->copy()->addDays($i);
            $value = (int) ($counts[$date->toDateString()] ?? 0);

            return [
                'label' => $date->format('d/m'),
                'value' => $value,
                'percent' => (int) round($value / $max * 100),
            ];
        });

        return view('dashboard', [
            'totalEmployees' => $totalEmployees,
            'checkInsToday' => $checkInsToday,
            'checkOutsToday' => $checkOutsToday,
            'latestAttendance' => $latestAttendance,
            'lateToday' => $lateToday,
            'notCheckedInCount' => $notCheckedInCount,
            'notCheckedIn' => $notCheckedIn,
            'activeSchedules' => $activeSchedules,
            'weekly' => $weekly,
        ]);
    }
}