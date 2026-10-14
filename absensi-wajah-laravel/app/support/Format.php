<?php

namespace App\Support;

class Format
{
    public static function minutes(?int $minutes): string
    {
        $minutes = (int) $minutes;

        return intdiv($minutes, 60) . 'j ' . ($minutes % 60) . 'm';
    }

    public static function auditBadge(string $action): string
    {
        return match (true) {
            str_contains($action, 'failed'),
            str_contains($action, 'deleted'),
            str_contains($action, 'deactivated') => 'badge-red',

            str_contains($action, 'created'),
            str_contains($action, 'success'),
            str_contains($action, 'activated') => 'badge-green',

            str_contains($action, 'updated') => 'badge-yellow',
            str_contains($action, 'exported') => 'badge-blue',

            default => 'badge-gray',
        };
    }
}