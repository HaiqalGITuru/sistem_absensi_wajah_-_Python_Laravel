<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use App\Services\PythonApiService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AttendanceController extends Controller
{
    public function __construct(
        private PythonApiService $api
    ) {
    }

    public function today()
    {
        $rows = null;
        $notice = null;
        $source = 'api';

        try {
            $result = $this->api->attendanceToday();

            if ($result['success']) {
                $rows = $this->normalizeRows(
                    $this->extractRows($result['data']),
                    'api'
                );
            } else {
                $notice = $result['message'];
            }
        } catch (\Throwable $e) {
            $notice = 'Python API tidak dapat dihubungi.';
        }

        if ($rows === null) {
            $source = 'local';

            $rows = AttendanceSession::with(['employee', 'workSchedule'])
                ->whereDate('work_date', Carbon::today())
                ->orderByDesc('updated_at')
                ->get()
                ->map(fn ($s) => $this->fromModel($s))
                ->values();
        }

        $summary = [
            'total' => $rows->count(),
            'checked_in' => $rows->filter(fn ($r) => $r['check_in'])->count(),
            'checked_out' => $rows->filter(fn ($r) => $r['check_out'])->count(),
            'working' => $rows->filter(
                fn ($r) => $r['check_in'] && ! $r['check_out']
            )->count(),
        ];

        return view('attendance.today', [
            'rows' => $rows,
            'summary' => $summary,
            'source' => $source,
            'notice' => $notice,
        ]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', 'string', 'max:30'],
            'q' => ['nullable', 'string', 'max:100'],
        ], [
            'date_from.date' => 'Tanggal awal tidak valid.',
            'date_to.date' => 'Tanggal akhir tidak valid.',
            'date_to.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ]);

        $from = $request->query('date_from')
            ?: Carbon::today()->subDays(29)->toDateString();
        $to = $request->query('date_to')
            ?: Carbon::today()->toDateString();
        $status = strtolower(trim((string) $request->query('status', '')));
        $search = trim((string) $request->query('q', ''));

        $perPage = 15;
        $notice = null;
        $source = 'api';
        $paginator = null;

        try {
            $params = [
                'start' => $from,
                'end' => $to,
            ];

            if ($search !== '') {
                $params['q'] = $search;
            }

            if ($status !== '') {
                $params['status'] = $status;
            }

            $result = $this->api->attendance($params);

            if ($result['success']) {
                $rows = $this->normalizeRows(
                    $this->extractRows($result['data']),
                    'api'
                );

                $rows = $rows->filter(function ($r) use ($from, $to, $status, $search) {
                    if ($r['work_date']) {
                        $d = $r['work_date']->toDateString();

                        if ($d < $from || $d > $to) {
                            return false;
                        }
                    }

                    if ($status !== '' && $r['status'] !== $status) {
                        return false;
                    }

                    if ($search !== '') {
                        $haystack = strtolower(
                            ($r['employee_name'] ?? '') . ' ' .
                            ($r['employee_code'] ?? '') . ' ' .
                            ($r['department'] ?? '')
                        );

                        if (! str_contains($haystack, strtolower($search))) {
                            return false;
                        }
                    }

                    return true;
                })->all();

                usort($rows, function ($a, $b) {
                    return [
                        $b['work_date']?->timestamp ?? 0,
                        $b['check_in']?->timestamp ?? 0,
                    ] <=> [
                        $a['work_date']?->timestamp ?? 0,
                        $a['check_in']?->timestamp ?? 0,
                    ];
                });

                $rows = collect($rows);
                $page = LengthAwarePaginator::resolveCurrentPage();

                $paginator = new LengthAwarePaginator(
                    $rows->forPage($page, $perPage)->values(),
                    $rows->count(),
                    $perPage,
                    $page,
                    [
                        'path' => $request->url(),
                        'query' => $request->query(),
                    ]
                );
            } else {
                $notice = $result['message'];
            }
        } catch (\Throwable $e) {
            $notice = 'Python API tidak dapat dihubungi.';
        }

        if ($paginator === null) {
            $source = 'local';

            $paginator = AttendanceSession::with(['employee', 'workSchedule'])
                ->whereDate('work_date', '>=', $from)
                ->whereDate('work_date', '<=', $to)
                ->when($status !== '', fn ($q) => $q->where('status', $status))
                ->when($search !== '', function ($q) use ($search) {
                    $q->whereHas('employee', function ($e) use ($search) {
                        $e->where('full_name', 'like', "%{$search}%")
                            ->orWhere('employee_code', 'like', "%{$search}%")
                            ->orWhere('department', 'like', "%{$search}%");
                    });
                })
                ->orderByDesc('work_date')
                ->orderByDesc('check_in_at')
                ->paginate($perPage)
                ->withQueryString()
                ->through(fn ($s) => $this->fromModel($s));
        }

        return view('attendance.index', [
            'rows' => $paginator,
            'from' => $from,
            'to' => $to,
            'status' => $status,
            'search' => $search,
            'source' => $source,
            'notice' => $notice,
        ]);
    }

    public function show(Request $request, string $id)
    {
        $record = null;
        $notice = null;

        if ($request->query('source') !== 'local') {
            try {
                $result = $this->api->attendanceDetail($id);

                if ($result['success']) {
                    $payload = $result['data'];
                    $row = is_array($payload)
                        ? ($payload['data'] ?? $payload)
                        : null;

                    if (is_array($row) && ! array_is_list($row)) {
                        $record = $this->normalize($row, 'api');
                    }
                } else {
                    $notice = $result['message'];
                }
            } catch (\Throwable $e) {
                $notice = 'Python API tidak dapat dihubungi.';
            }
        }

        if (! $record && ctype_digit($id)) {
            $session = AttendanceSession::with(['employee', 'workSchedule'])
                ->find($id);

            if ($session) {
                $record = $this->fromModel($session);
            }
        }

        abort_if(! $record, 404);

        return view('attendance.show', [
            'record' => $record,
            'notice' => $notice,
        ]);
    }

    private function extractRows(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        if (array_is_list($payload)) {
            return array_values(array_filter($payload, 'is_array'));
        }

        foreach (['data', 'items', 'results', 'attendance', 'records', 'rows'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return $this->extractRows($payload[$key]);
            }
        }

        return [];
    }

    private function normalizeRows(array $rows, string $source): Collection
    {
        return collect($rows)
            ->map(fn ($row) => $this->normalize($row, $source))
            ->values();
    }

    private function normalize(array $row, string $source): array
    {
        $emp = is_array($row['employee'] ?? null) ? $row['employee'] : [];

        $checkIn = $this->toDate($row['check_in_at'] ?? $row['check_in'] ?? null);
        $checkOut = $this->toDate($row['check_out_at'] ?? $row['check_out'] ?? null);
        $workDate = $this->toDate($row['work_date'] ?? $row['date'] ?? null)
            ?? $checkIn?->copy()->startOfDay();

        $schedule = $row['schedule_name'] ?? null;

        if (! $schedule && is_array($row['work_schedule'] ?? null)) {
            $schedule = $row['work_schedule']['name'] ?? null;
        }

        return [
            'id' => $row['id'] ?? $row['session_id'] ?? null,
            'employee_code' => $row['employee_code'] ?? $emp['employee_code'] ?? null,
            'employee_name' => $row['employee_name']
                ?? $row['full_name']
                ?? $emp['full_name']
                ?? $emp['name']
                ?? $row['name']
                ?? null,
            'department' => $row['department'] ?? $emp['department'] ?? null,
            'schedule' => is_scalar($schedule) ? $schedule : null,
            'work_date' => $workDate,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'duration' => $this->duration($checkIn, $checkOut),
            'status' => strtolower(trim((string) ($row['status'] ?? ''))),
            'source' => $source,
            'raw' => $row,
        ];
    }

    private function fromModel(AttendanceSession $session): array
    {
        $checkIn = $session->check_in_at;
        $checkOut = $session->check_out_at;

        return [
            'id' => $session->id,
            'employee_code' => $session->employee?->employee_code,
            'employee_name' => $session->employee?->full_name,
            'department' => $session->employee?->department,
            'schedule' => $session->workSchedule
                ? sprintf(
                    '%s (%s - %s)',
                    $session->workSchedule->name,
                    substr((string) $session->workSchedule->start_time, 0, 5),
                    substr((string) $session->workSchedule->end_time, 0, 5)
                )
                : null,
            'work_date' => $session->work_date,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'duration' => $this->duration($checkIn, $checkOut),
            'status' => strtolower((string) $session->status),
            'source' => 'local',
            'raw' => [],
        ];
    }

    private function toDate(mixed $value): ?Carbon
    {
        if (blank($value) || ! is_scalar($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value)
                ->setTimezone(config('app.timezone'));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function duration(?Carbon $in, ?Carbon $out): ?string
    {
        if (! $in || ! $out || $out->lt($in)) {
            return null;
        }

        $minutes = (int) $in->diffInMinutes($out);

        return intdiv($minutes, 60) . 'j ' . ($minutes % 60) . 'm';
    }
}