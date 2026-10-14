<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\PythonApiService;
use App\Models\WorkSchedule;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $employees = Employee::with('workSchedule')
            ->withCount('faceTemplates')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_code', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%");
                });
            })
            ->when($status === 'aktif', fn ($q) => $q->where('is_active', true))
            ->when($status === 'nonaktif', fn ($q) => $q->where('is_active', false))
            ->orderBy('full_name')
            ->paginate(10)
            ->withQueryString();

        return view('employees.index', [
            'employees' => $employees,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create()
    {
        return view('employees.create', [
            'schedules' => $this->scheduleOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            $this->rules(),
            $this->messages(),
            $this->attributes()
        );

        $data['is_active'] = $request->boolean('is_active');

        $employee = Employee::create($data);

        AuditLogger::log(
            'employee_created',
            'Employee',
            $employee->id,
            "Menambah karyawan {$employee->employee_code} - {$employee->full_name}."
        );

        return redirect()
            ->route('employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }


    public function enrollFace(Request $request, Employee $employee)
    {
        $data = $request->validate(
            ['replace_reason' => ['nullable', 'string', 'min:5', 'max:255']],
            ['replace_reason.min' => 'Alasan daftar ulang minimal :min karakter.'],
            ['replace_reason' => 'Alasan daftar ulang']
        );

        if (! $employee->is_active) {
            return back()->with('error', 'Karyawan nonaktif. Aktifkan dulu untuk mendaftarkan wajah.');
        }

        $result = app(PythonApiService::class)
            ->startFaceEnrollment($employee->id, $data['replace_reason'] ?? null);

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        AuditLogger::log(
            'face_enrollment_requested',
            'Employee',
            $employee->id,
            "Meminta pendaftaran wajah untuk {$employee->employee_code} - {$employee->full_name}."
        );

        $kioskOnline = $result['data']['kiosk_online'] ?? null;
        $msg = 'Permintaan pendaftaran wajah terkirim. Minta karyawan berdiri di depan kamera kios.';
        if ($kioskOnline === false) {
            $msg .= ' Catatan: kamera kios sedang tidak aktif — nyalakan kios agar pendaftaran berjalan.';
        }

        return back()
            ->with('success', $msg)
            ->with('enroll_request_id', $result['data']['id'] ?? null);
    }

    public function enrollStatus(string $id)
    {
        $result = app(PythonApiService::class)->faceEnrollmentStatus((int) $id);

        if (! $result['success']) {
            return response()->json([
                'ok' => false,
                'message' => $result['message'],
            ]);
        }

        $d = $result['data'] ?? [];

        return response()->json([
            'ok' => true,
            'status' => $d['status'] ?? null,
            'finished' => $d['finished'] ?? false,
            'message' => $d['message'] ?? null,
            'samples_taken' => $d['samples_taken'] ?? 0,
            'samples_total' => $d['samples_total'] ?? 0,
            'kiosk_online' => $d['kiosk_online'] ?? null,
        ]);
    }

    public function show(Employee $employee)
    {
        $employee->load('workSchedule');
        $employee->loadCount('faceTemplates');

        $totalSessions = $employee->attendanceSessions()->count();

        $recentSessions = $employee->attendanceSessions()
            ->orderByDesc('work_date')
            ->orderByDesc('check_in_at')
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'employee_code' => $employee->employee_code,
                'employee_name' => $employee->full_name,
                'department' => $employee->department,
                'work_date' => $s->work_date,
                'check_in' => $s->check_in_at,
                'check_out' => $s->check_out_at,
                'status' => strtolower((string) $s->status),
                'source' => 'local',
                'raw' => [
                    'check_in_method' => $s->check_in_method ?? null,
                    'check_out_method' => $s->check_out_method ?? null,
                    'left_early' => $s->left_early ?? false,
                    'late_minutes' => $s->late_minutes ?? null,
                ],
            ]);

        return view('employees.show', [
            'employee' => $employee,
            'totalSessions' => $totalSessions,
            'recentSessions' => $recentSessions,
        ]);
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', [
            'employee' => $employee,
            'schedules' => $this->scheduleOptions($employee->work_schedule_id),
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate(
            $this->rules($employee),
            $this->messages(),
            $this->attributes()
        );

        $data['is_active'] = $request->boolean('is_active');

        $employee->update($data);

        AuditLogger::log(
            'employee_updated',
            'Employee',
            $employee->id,
            "Mengubah data karyawan {$employee->employee_code} - {$employee->full_name}."
        );

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function toggle(Employee $employee)
    {
        $employee->update([
            'is_active' => ! $employee->is_active,
        ]);

        $label = $employee->is_active ? 'diaktifkan' : 'dinonaktifkan';

        AuditLogger::log(
            $employee->is_active ? 'employee_activated' : 'employee_deactivated',
            'Employee',
            $employee->id,
            "Karyawan {$employee->employee_code} - {$employee->full_name} {$label}."
        );

        return back()->with(
            'success',
            "Karyawan {$employee->full_name} berhasil {$label}."
        );
    }

    public function destroy(Employee $employee)
    {
        if ($employee->attendanceSessions()->exists()) {
            return back()->withErrors([
                'delete' => 'Karyawan tidak dapat dihapus karena sudah memiliki riwayat absensi. Gunakan fitur nonaktifkan.',
            ]);
        }

        $code = $employee->employee_code;
        $name = $employee->full_name;
        $id = $employee->id;

        $employee->delete();

        AuditLogger::log(
            'employee_deleted',
            'Employee',
            $id,
            "Menghapus karyawan {$code} - {$name}."
        );

        return redirect()
            ->route('employees.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }

    private function rules(?Employee $employee = null): array
    {
        return [
            'employee_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('python.employees', 'employee_code')
                    ->ignore($employee?->id),
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'work_schedule_id' => [
                'nullable',
                'integer',
                'exists:python.work_schedules,id',
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute maksimal :max karakter.',
            'unique' => ':attribute sudah digunakan.',
            'exists' => ':attribute tidak valid.',
            'integer' => ':attribute tidak valid.',
        ];
    }

    private function attributes(): array
    {
        return [
            'employee_code' => 'Kode karyawan',
            'full_name' => 'Nama lengkap',
            'department' => 'Departemen',
            'work_schedule_id' => 'Jadwal kerja',
        ];
    }

    private function scheduleOptions(?int $currentId = null)
    {
        return WorkSchedule::query()
            ->where(function ($q) use ($currentId) {
                $q->where('is_active', true);

                if ($currentId) {
                    $q->orWhere('id', $currentId);
                }
            })
            ->orderBy('name')
            ->get();
    }
}