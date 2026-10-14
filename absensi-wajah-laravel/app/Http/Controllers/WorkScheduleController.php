<?php

namespace App\Http\Controllers;

use App\Models\WorkSchedule;
use App\Services\PythonApiService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

class WorkScheduleController extends Controller
{
    private array $weekdays = [
        0 => 'Senin', 1 => 'Selasa', 2 => 'Rabu', 3 => 'Kamis',
        4 => 'Jumat', 5 => 'Sabtu', 6 => 'Minggu',
    ];

    public function index()
    {
        $schedules = WorkSchedule::withCount('employees')
            ->with('days')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10);

        return view('work-schedules.index', [
            'schedules' => $schedules,
        ]);
    }

    public function create()
    {
        return view('work-schedules.create', [
            'days' => $this->weekdays,
            'schedule' => null,
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->payload($request);

        $result = app(PythonApiService::class)->createWorkSchedule($payload);

        if (! $result['success']) {
            return back()->withInput()->with('error', $result['message']);
        }

        AuditLogger::log(
            'work_schedule_created',
            'WorkSchedule',
            $result['data']['id'] ?? null,
            "Menambah jadwal kerja {$payload['name']}."
        );

        return redirect()
            ->route('work-schedules.index')
            ->with('success', 'Jadwal kerja berhasil ditambahkan.');
    }

    public function edit(WorkSchedule $workSchedule)
    {
        $workSchedule->load('days');

        return view('work-schedules.edit', [
            'schedule' => $workSchedule,
            'days' => $this->weekdays,
        ]);
    }

    public function update(Request $request, WorkSchedule $workSchedule)
    {
        $payload = $this->payload($request);

        $result = app(PythonApiService::class)->updateWorkSchedule($workSchedule->id, $payload);

        if (! $result['success']) {
            return back()->withInput()->with('error', $result['message']);
        }

        AuditLogger::log(
            'work_schedule_updated',
            'WorkSchedule',
            $workSchedule->id,
            "Mengubah jadwal kerja {$payload['name']}."
        );

        return redirect()
            ->route('work-schedules.index')
            ->with('success', 'Jadwal kerja berhasil diperbarui.');
    }

    public function toggle(WorkSchedule $workSchedule)
    {
        $workSchedule->update([
            'is_active' => ! $workSchedule->is_active,
        ]);

        $label = $workSchedule->is_active ? 'diaktifkan' : 'dinonaktifkan';

        AuditLogger::log(
            $workSchedule->is_active ? 'work_schedule_activated' : 'work_schedule_deactivated',
            'WorkSchedule',
            $workSchedule->id,
            "Jadwal kerja {$workSchedule->name} {$label}."
        );

        return back()->with('success', "Jadwal kerja {$workSchedule->name} berhasil {$label}.");
    }

    private function payload(Request $request): array
    {
        $days = [];

        for ($w = 0; $w <= 6; $w++) {
            if ($request->boolean("day_{$w}_on")) {
                $days[] = [
                    'weekday' => $w,
                    'start_time' => trim((string) $request->input("day_{$w}_start", '')),
                    'end_time' => trim((string) $request->input("day_{$w}_end", '')),
                ];
            }
        }

        return [
            'name' => trim((string) $request->input('name', '')),
            'late_tolerance_minutes' => (int) $request->input('late_tolerance_minutes', 0),
            'days' => $days,
        ];
    }
}
