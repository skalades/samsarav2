<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $description = null,
        ?string $reason = null,
        ?array $payload = null
    ): AuditLog {
        $user = Auth::user();

        return AuditLog::create([
            'user_id' => $user?->id,
            'actor_name' => $user?->name ?? 'Sistem Otomatis',
            'actor_role' => $user?->role ?? 'system',
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'reason' => $reason,
            'payload' => $payload,
            'ip_address' => Request::ip() ?? '127.0.0.1',
        ]);
    }
}
