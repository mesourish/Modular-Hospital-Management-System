<?php

namespace App\Core\Audit;

use App\Core\Tenancy\TenantManager;
use App\Models\AuditEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Log a clinical, administrative, or security audit event.
     */
    public static function log(
        string $moduleKey = 'core',
        string $action = 'action',
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $details = null,
        ?int $hospitalId = null,
        ?string $auditableType = null,
        ?int $auditableId = null,
        ?array $payload = null
    ): AuditEvent {
        $resolvedHospitalId = $hospitalId ?: TenantManager::getHospitalId();
        $user = Auth::user();

        return AuditEvent::create([
            'hospital_id' => $resolvedHospitalId,
            'user_id' => $user?->id,
            'module_key' => $moduleKey,
            'action' => $action,
            'entity_type' => $entityType ?? $auditableType,
            'entity_id' => $entityId ?? $auditableId,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => substr(Request::userAgent() ?? 'System', 0, 500),
            'details' => $details ?? $payload,
            'created_at' => now(),
        ]);
    }
}
