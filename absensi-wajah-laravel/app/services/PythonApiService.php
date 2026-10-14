<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class PythonApiService
{
    protected function client(): PendingRequest
    {
        $request = Http::baseUrl(
            rtrim(
                config('services.python_api.url'),
                '/'
            )
        )
            ->timeout(
                config('services.python_api.timeout', 5)
            )
            ->acceptJson();

        $token = config(
            'services.python_api.token'
        );

        if (!empty($token)) {
            $request->withToken($token);
        }

        return $request;
    }

    public function health(): array
    {
        $response = $this->client()->get(
            '/api/health'
        );

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => 'Python API tidak tersedia.',
                'data' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Python API aktif.',
            'data' => $response->json(),
        ];
    }

    public function employees(array $params = []): array
    {
        $response = $this->client()->get(
            '/api/employees',
            $params
        );

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => 'Data karyawan dari Python API gagal diambil.',
                'data' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Data karyawan berhasil diambil.',
            'data' => $response->json(),
        ];
    }

    public function attendanceToday(): array
    {
        $response = $this->client()->get(
            '/api/attendance'
        );

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => 'Data absensi hari ini gagal diambil.',
                'data' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Data absensi hari ini berhasil diambil.',
            'data' => $response->json(),
        ];
    }

    public function attendance(array $params = []): array
    {
        $response = $this->client()->get(
            '/api/attendance',
            $params
        );

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => 'Riwayat absensi gagal diambil.',
                'data' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Riwayat absensi berhasil diambil.',
            'data' => $response->json(),
        ];
    }

    public function attendanceDetail(int|string $id): array
    {
        $response = $this->client()->get(
            "/api/attendance/{$id}"
        );

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => 'Detail absensi gagal diambil.',
                'data' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Detail absensi berhasil diambil.',
            'data' => $response->json(),
        ];
    }
    public function startFaceEnrollment(int $employeeId, ?string $replaceReason = null): array
    {
        $body = [];

        if (!empty($replaceReason)) {
            $body['replace_reason'] = $replaceReason;
        }

        $response = $this->client()->post(
            "/api/employees/{$employeeId}/face-enrollment",
            $body
        );

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => $response->json('message')
                    ?? 'Permintaan pendaftaran wajah gagal dikirim ke sistem Python.',
                'data' => $response->json(),
            ];
        }

        return [
            'success' => true,
            'message' => 'Permintaan pendaftaran wajah terkirim.',
            'data' => $response->json(),
        ];
    }
    public function faceEnrollmentStatus(int $requestId): array
    {
        $response = $this->client()->get("/api/face-enrollments/{$requestId}");

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => $response->json('message')
                    ?? 'Status pendaftaran wajah tidak dapat diambil.',
                'data' => $response->json(),
            ];
        }

        return [
            'success' => true,
            'message' => 'ok',
            'data' => $response->json(),
        ];
    }
    public function createWorkSchedule(array $payload): array
    {
        return $this->scheduleResult(
            $this->client()->post('/api/work-schedules', $payload),
            'Jadwal kerja gagal disimpan.'
        );
    }

    public function updateWorkSchedule(int $id, array $payload): array
    {
        return $this->scheduleResult(
            $this->client()->patch("/api/work-schedules/{$id}", $payload),
            'Jadwal kerja gagal diperbarui.'
        );
    }

    private function scheduleResult($response, string $failMsg): array
    {
        if ($response->successful()) {
            return ['success' => true, 'message' => 'ok', 'data' => $response->json()];
        }

        $msg = $response->json('message') ?: $failMsg;
        $fields = $response->json('fields');

        if (is_array($fields) && $fields) {
            $parts = [];
            foreach ($fields as $v) {
                $parts[] = is_array($v) ? implode(' ', $v) : (string) $v;
            }
            $msg .= ' (' . implode('; ', $parts) . ')';
        }

        return ['success' => false, 'message' => $msg, 'data' => $response->json()];
    }
}