<?php

namespace App\Support;

class AttendanceStatus
{
    // Status mengikuti sistem Python: hadir, terlambat, dibatalkan.
    private const LABELS = [
        'hadir' => 'Hadir',
        'terlambat' => 'Terlambat',
        'dibatalkan' => 'Dibatalkan',
    ];

    private const BADGES = [
        'hadir' => 'badge-green',
        'terlambat' => 'badge-yellow',
        'dibatalkan' => 'badge-red',
    ];

    public static function options(): array
    {
        return self::LABELS;
    }

    public static function label(?string $status): string
    {
        $status = strtolower(trim((string) $status));

        if ($status === '') {
            return '-';
        }

        return self::LABELS[$status] ?? ucfirst($status);
    }

    public static function badge(?string $status): string
    {
        $status = strtolower(trim((string) $status));

        return self::BADGES[$status] ?? 'badge-gray';
    }
}
