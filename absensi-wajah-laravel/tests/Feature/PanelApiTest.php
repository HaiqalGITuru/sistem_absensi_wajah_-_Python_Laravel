<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Panel Laravel hanya memakai API sistem absensi (Http::fake meniru API Python).
 */
class PanelApiTest extends TestCase
{
    use RefreshDatabase;

    private const API = 'http://absensi.test/api';

    private function admin(): User
    {
        return User::create([
            'name' => 'Andi', 'username' => 'andi', 'password' => bcrypt('RahasiaPanjang123'),
            'role' => 'admin', 'is_active' => true,
        ]);
    }

    private function employee(array $over = []): array
    {
        return array_merge([
            'id' => 7, 'employee_code' => '199001012020011001', 'full_name' => 'Siti Aminah',
            'department' => 'APTIKA', 'is_active' => true, 'face_registered' => false,
            'work_schedule' => ['id' => 1, 'name' => 'Jam Kerja Kantor'],
        ], $over);
    }

    private function schedule(array $over = []): array
    {
        return array_merge([
            'id' => 1, 'name' => 'Jam Kerja Kantor', 'late_tolerance_minutes' => 15, 'is_active' => true,
            'days' => [['weekday' => 0, 'weekday_name' => 'Senin', 'start_time' => '07:30', 'end_time' => '16:00', 'crosses_midnight' => false]],
            'summary' => ['Senin 07:30–16:00'], 'active_employees' => 3,
        ], $over);
    }

    private function attendanceRow(): array
    {
        return [
            'id' => 2, 'work_date' => now()->toDateString(), 'status' => 'terlambat',
            'employee' => ['id' => 1, 'employee_code' => '2310010164', 'full_name' => 'Haiqal Ramadhan', 'department' => 'APTIKA'],
            'check_in_at' => now()->setTime(7, 50)->toIso8601String(), 'check_out_at' => null,
            'late_minutes' => 5, 'left_early' => false, 'scheduled_start' => null, 'scheduled_end' => null,
            'check_in_method' => 'kamera', 'check_out_method' => null,
        ];
    }

    private function page(array $data): array
    {
        return ['data' => $data, 'page' => 1, 'per_page' => 50, 'total' => count($data), 'pages' => 1];
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_dari_api_dengan_token_dan_pelaku(): void
    {
        Http::fake([
            self::API . '/summary/today*' => Http::response([
                'date' => now()->toDateString(), 'active_employees' => 7, 'scheduled' => 7, 'present' => 1,
                'late' => 1, 'checked_out' => 0,
                'not_checked_in' => [['id' => 3, 'employee_code' => '0001', 'full_name' => 'Fuad', 'department' => 'APTIKA']],
            ]),
            self::API . '/work-schedules*' => Http::response(['data' => [$this->schedule()], 'total' => 1]),
            self::API . '/attendance*' => Http::response($this->page([$this->attendanceRow()])),
        ]);

        $this->actingAs($this->admin())->get('/dashboard')
            ->assertOk()->assertSee('Haiqal Ramadhan')->assertSee('Fuad')->assertSee('07:50');

        Http::assertSent(fn (Request $r) => $r->hasHeader('Authorization', 'Bearer aw_token_uji')
            && $r->hasHeader('X-Actor', 'andi'));
    }

    public function test_api_mati_tidak_membuat_halaman_galat(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('mati'));
        $this->actingAs($this->admin())->get('/dashboard')
            ->assertOk()->assertSee('tidak dapat dihubungi');
        $this->get('/employees')->assertOk()->assertSee('tidak dapat dihubungi');
    }

    public function test_token_ditolak_diberi_pesan_jelas(): void
    {
        Http::fake([self::API . '/employees*' => Http::response(['error' => 'token_tidak_valid'], 401)]);
        $this->actingAs($this->admin())->get('/employees')->assertOk()->assertSee('PYTHON_API_TOKEN');
    }

    public function test_tambah_karyawan_lewat_api(): void
    {
        Http::fake([self::API . '/employees' => Http::response($this->employee(), 201)]);

        $this->actingAs($this->admin())->post('/employees', [
            'employee_code' => '199001012020011001', 'full_name' => 'Siti Aminah',
            'department' => 'APTIKA', 'work_schedule_id' => '1',
        ])->assertRedirect('/employees/7');

        Http::assertSent(fn (Request $r) => $r->method() === 'POST'
            && $r->url() === self::API . '/employees'
            && $r['work_schedule_id'] === 1 && $r['confirm_duplicate_name'] === false);
    }

    public function test_galat_validasi_api_tampil_di_field(): void
    {
        Http::fake([self::API . '/employees' => Http::response([
            'error' => 'validasi_gagal', 'message' => 'Periksa kembali data yang dikirim.',
            'fields' => ['employee_code' => "Kode '0001' sudah dipakai karyawan lain."],
            'needs_confirmation' => false,
        ], 422)]);

        $this->actingAs($this->admin())->from('/employees/create')->post('/employees', [
            'employee_code' => '0001', 'full_name' => 'X', 'department' => 'Y', 'work_schedule_id' => 1,
        ])->assertRedirect('/employees/create')
            ->assertSessionHasErrors(['employee_code' => "Kode '0001' sudah dipakai karyawan lain."]);
    }

    public function test_nama_ganda_meminta_konfirmasi(): void
    {
        Http::fake([self::API . '/employees' => Http::response([
            'error' => 'validasi_gagal', 'message' => 'x',
            'fields' => ['full_name' => 'Nama ini sudah dipakai karyawan lain: 0003 (nonaktif).'],
            'needs_confirmation' => true,
        ], 422)]);
        $this->actingAs($this->admin())->from('/employees/create')->post('/employees', [
            'employee_code' => '0009', 'full_name' => 'Febian', 'department' => 'Y', 'work_schedule_id' => 1,
        ])->assertSessionHas('needs_confirmation', true);
    }

    public function test_nonaktifkan_karyawan_dan_tidak_ada_hapus(): void
    {
        Http::fake([
            self::API . '/employees/7/deactivate' => Http::response($this->employee(['is_active' => false])),
            self::API . '/employees/7' => Http::response($this->employee()),
        ]);
        $this->actingAs($this->admin())->from('/employees')->patch('/employees/7/toggle')
            ->assertRedirect('/employees')->assertSessionHas('success');
        Http::assertSent(fn (Request $r) => $r->url() === self::API . '/employees/7/deactivate');

        $this->delete('/employees/7')->assertStatus(405);
    }

    public function test_jadwal_per_hari_dikirim_ke_api(): void
    {
        Http::fake([self::API . '/work-schedules' => Http::response($this->schedule(), 201)]);
        $this->actingAs($this->admin())->post('/work-schedules', [
            'name' => 'Jam Kerja Resmi', 'late_tolerance_minutes' => 15,
            'day_0_on' => '1', 'day_0_start' => '07:30', 'day_0_end' => '16:00',
            'day_4_on' => '1', 'day_4_start' => '07:30', 'day_4_end' => '11:30',
            'day_5_start' => '08:00',
        ])->assertRedirect('/work-schedules');

        Http::assertSent(fn (Request $r) => $r['days'] === [
            ['weekday' => 0, 'start_time' => '07:30', 'end_time' => '16:00'],
            ['weekday' => 4, 'start_time' => '07:30', 'end_time' => '11:30'],
        ]);
    }

    public function test_halaman_ubah_jadwal_tidak_kosong(): void
    {
        Http::fake([self::API . '/work-schedules/1' => Http::response($this->schedule())]);
        $this->actingAs($this->admin())->get('/work-schedules/1/edit')
            ->assertOk()->assertSee('Jam Kerja per Hari')->assertSee('07:30', false);
    }

    public function test_ekspor_rekap_csv_aman_untuk_excel(): void
    {
        Http::fake([self::API . '/reports/summary*' => Http::response([
            'start' => '2026-10-01', 'end' => '2026-10-08',
            'data' => [[
                'employee' => ['id' => 1, 'employee_code' => '2310010164', 'full_name' => '=HYPERLINK("x")',
                               'department' => 'APTIKA', 'is_active' => true],
                'workdays' => 5, 'present' => 4, 'late' => 1, 'late_minutes' => 5, 'left_early' => 0,
                'no_checkout' => 0, 'not_checked_in' => 1,
            ]],
            'totals' => [],
        ])]);
        $csv = $this->actingAs($this->admin())
            ->get('/reports/export/summary?date_from=2026-10-01&date_to=2026-10-08')
            ->assertOk()->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString(';', $csv);
        $this->assertStringContainsString('"=""2310010164"""', $csv);   // NIP tetap teks di Excel
        $this->assertStringContainsString("'=HYPERLINK", $csv);         // CSV injection dinetralkan
    }

    public function test_status_api_menjelaskan_token_read(): void
    {
        Http::fake([
            self::API . '/health' => Http::response(['status' => 'ok']),
            self::API . '/employees*' => Http::response($this->page([])),
            self::API . '/summary/today*' => Http::response(['not_checked_in' => []]),
            self::API . '/audit-logs*' => Http::response(['error' => 'cakupan_tidak_cukup'], 403),
        ]);
        $this->actingAs($this->admin())->get('/python-api/status')
            ->assertOk()->assertSee('butuh token write');
    }

    public function test_perintah_admin_create_menolak_password_pendek(): void
    {
        $this->artisan('admin:create', ['username' => 'andi'])
            ->expectsQuestion('Password (minimal 12 karakter)', 'pendek')
            ->assertFailed();
        $this->artisan('admin:create', ['username' => 'andi'])
            ->expectsQuestion('Password (minimal 12 karakter)', 'RahasiaPanjang123')
            ->expectsQuestion('Ulangi password', 'RahasiaPanjang123')
            ->assertSuccessful();
        $this->assertTrue(User::where('username', 'andi')->exists());
    }

    // ------------------------------------------------------------ pendaftaran wajah

    private function enrollment(array $over = []): array
    {
        return array_merge([
            'id' => 12, 'status' => 'pending', 'is_replacement' => false,
            'employee' => ['id' => 7, 'employee_code' => '199001012020011001', 'full_name' => 'Siti Aminah', 'department' => 'APTIKA'],
            'samples_taken' => 0, 'samples_total' => 5, 'message' => 'Menunggu layanan kamera kios...',
            'created_at' => now()->toIso8601String(), 'expires_at' => now()->addMinutes(2)->toIso8601String(),
            'started_at' => null, 'finished_at' => null, 'finished' => false, 'kiosk_online' => true,
        ], $over);
    }

    public function test_tambah_karyawan_langsung_daftarkan_wajah(): void
    {
        Http::fake([
            self::API . '/employees/7/face-enrollment' => Http::response($this->enrollment(), 202),
            self::API . '/employees' => Http::response($this->employee(), 201),
        ]);
        $this->actingAs($this->admin())->post('/employees', [
            'employee_code' => '199001012020011001', 'full_name' => 'Siti Aminah',
            'department' => 'APTIKA', 'work_schedule_id' => '1', 'enroll_face' => '1',
        ])->assertRedirect('/face-enrollments/12');

        Http::assertSent(fn (Request $r) => $r->url() === self::API . '/employees/7/face-enrollment'
            && $r->method() === 'POST' && $r->hasHeader('X-Actor', 'andi'));
        Http::assertSent(fn (Request $r) => $r->url() === self::API . '/employees'
            && ! isset($r['enroll_face']));
    }

    public function test_tanpa_centang_tidak_mendaftarkan_wajah(): void
    {
        Http::fake([self::API . '/employees' => Http::response($this->employee(), 201)]);
        $this->actingAs($this->admin())->post('/employees', [
            'employee_code' => 'X1', 'full_name' => 'X', 'department' => 'Y', 'work_schedule_id' => 1,
        ])->assertRedirect('/employees/7');
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'face-enrollment'));
    }

    public function test_daftar_ulang_mengirim_alasan_dan_galat_tampil(): void
    {
        Http::fake([self::API . '/employees/7/face-enrollment' => Http::response([
            'error' => 'validasi_gagal', 'message' => 'x',
            'fields' => ['replace_reason' => 'Alasan daftar ulang minimal 5 karakter.'], 'needs_confirmation' => false,
        ], 422)]);
        $this->actingAs($this->admin())->from('/employees/7')
            ->post('/employees/7/face-enrollment', ['replace_reason' => 'abc'])
            ->assertRedirect('/employees/7')
            ->assertSessionHasErrors(['replace_reason' => 'Alasan daftar ulang minimal 5 karakter.']);
        Http::assertSent(fn (Request $r) => $r['replace_reason'] === 'abc');
    }

    public function test_halaman_kemajuan_dan_status_json(): void
    {
        Http::fake([self::API . '/face-enrollments/12' => Http::response(
            $this->enrollment(['status' => 'running', 'samples_taken' => 3]))]);
        $this->actingAs($this->admin())->get('/face-enrollments/12')
            ->assertOk()->assertSee('Siti Aminah')->assertSee('Sampel 3/5');
        $this->getJson('/face-enrollments/12/status')
            ->assertOk()->assertJson(['status' => 'running', 'samples_taken' => 3]);
    }

    public function test_batalkan_pendaftaran(): void
    {
        Http::fake([self::API . '/face-enrollments/12/cancel' => Http::response($this->enrollment(['status' => 'cancelled']))]);
        $this->actingAs($this->admin())->from('/face-enrollments/12')->post('/face-enrollments/12/cancel')
            ->assertRedirect('/face-enrollments/12')->assertSessionHas('success');
    }

    public function test_detail_karyawan_menampilkan_tombol_daftar_wajah(): void
    {
        Http::fake([
            self::API . '/employees/7' => Http::response($this->employee(['face_registered' => true])),
            self::API . '/attendance*' => Http::response($this->page([])),
        ]);
        $this->actingAs($this->admin())->get('/employees/7')
            ->assertOk()->assertSee('Daftar ulang wajah di kamera kios')->assertSee('Alasan daftar ulang');
    }
}
