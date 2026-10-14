<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PythonApiStatusController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WorkScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get(
        '/login',
        [LoginController::class, 'showLogin']
    )->name('login');

    Route::post(
        '/login',
        [LoginController::class, 'login']
    )
        ->middleware('throttle:login')
        ->name('login.process');
});

Route::middleware(['auth', 'admin'])->group(function () {

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');

    // Karyawan
    Route::resource('employees', EmployeeController::class);

    Route::patch(
        '/employees/{employee}/toggle',
        [EmployeeController::class, 'toggle']
    )->name('employees.toggle');

    Route::post(
        '/employees/{employee}/face-enrollment',
        [EmployeeController::class, 'enrollFace']
    )->name('face-enrollments.start');

    Route::get(
        '/face-enrollments/{id}/status',
        [EmployeeController::class, 'enrollStatus']
    )->where('id', '[0-9]+')->name('face-enrollments.status');

    // Jadwal Kerja
    Route::resource('work-schedules', WorkScheduleController::class)
        ->except(['show', 'destroy'])
        ->parameters(['work-schedules' => 'workSchedule']);

    Route::patch(
        '/work-schedules/{workSchedule}/toggle',
        [WorkScheduleController::class, 'toggle']
    )->name('work-schedules.toggle');

    // Absensi
    Route::get(
        '/attendance/today',
        [AttendanceController::class, 'today']
    )->name('attendance.today');

    Route::get(
        '/attendance',
        [AttendanceController::class, 'index']
    )->name('attendance.index');

    Route::get(
        '/attendance/{id}',
        [AttendanceController::class, 'show']
    )->where('id', '[A-Za-z0-9_\-]+')->name('attendance.show');

    // STEP 3 - Laporan & export
    Route::get(
        '/reports',
        [ReportController::class, 'index']
    )->name('reports.index');

    Route::get(
        '/reports/export/summary',
        [ReportController::class, 'exportSummary']
    )->name('reports.export-summary');

    Route::get(
        '/reports/export/summary-pdf',
        [ReportController::class, 'exportSummaryPdf']
    )->name('reports.export-summary-pdf');

    // STEP 3 - Audit log
    Route::get(
        '/audit-logs',
        [AuditLogController::class, 'index']
    )->name('audit-logs.index');

    // STEP 3 - Status Python API
    Route::get(
        '/python-api/status',
        [PythonApiStatusController::class, 'index']
    )->name('python-api.status');

    Route::get(
        '/python-api/check',
        [PythonApiStatusController::class, 'check']
    )->name('python-api.check');

    Route::post(
        '/logout',
        [LoginController::class, 'logout']
    )->name('logout');
});