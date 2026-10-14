<?php

namespace App\Support;

use App\Models\AuditLog;

class AuditLogger
{
    public static function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $description = null
    ): void {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => $description,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}