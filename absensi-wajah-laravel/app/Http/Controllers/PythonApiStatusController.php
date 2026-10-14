<?php

namespace App\Http\Controllers;

use App\Services\PythonApiService;

class PythonApiStatusController extends Controller
{
    public function __construct(
        private PythonApiService $api
    ) {
    }

    public function index()
    {
        $checks = [
            $this->probe('Health check', fn () => $this->api->health()),
            $this->probe('Data karyawan', fn () => $this->api->employees(['per_page' => 1])),
            $this->probe('Absensi hari ini', fn () => $this->api->attendanceToday()),
        ];

        $url = (string) config('services.python_api.url');

        return view('python-api.status', [
            'checks' => $checks,
            'allOk' => collect($checks)->every(fn ($c) => $c['ok']),
            'host' => parse_url($url, PHP_URL_HOST) . (
                parse_url($url, PHP_URL_PORT) ? ':' . parse_url($url, PHP_URL_PORT) : ''
            ),
            'timeout' => config('services.python_api.timeout'),
            'tokenSet' => ! empty(config('services.python_api.token')),
            'checkedAt' => now(),
        ]);
    }

    /**
     * Endpoint JSON ringan untuk kartu status di dashboard.
     */
    public function check()
    {
        return response()->json(
            $this->probe('Health check', fn () => $this->api->health())
        );
    }

    private function probe(string $name, \Closure $callback): array
    {
        $start = microtime(true);

        try {
            $result = $callback();
            $ok = (bool) ($result['success'] ?? false);
            $message = (string) ($result['message'] ?? '');
        } catch (\Throwable $e) {
            $ok = false;
            $message = 'Tidak dapat terhubung ke Python API.';
        }

        return [
            'name' => $name,
            'ok' => $ok,
            'message' => $message,
            'ms' => (int) round((microtime(true) - $start) * 1000),
        ];
    }
}