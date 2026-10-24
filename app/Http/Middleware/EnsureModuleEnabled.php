<?php

namespace App\Http\Middleware;

use App\Core\Audit\AuditLogger;
use App\Core\ModuleRegistry\ModuleRegistry;
use App\Core\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        $hospitalId = TenantManager::getHospitalId();

        if (! $hospitalId) {
            abort(403, 'No hospital context resolved. Please select an active hospital.');
        }

        if (! ModuleRegistry::isModuleEnabled($moduleKey, $hospitalId)) {
            // Log security event
            AuditLogger::log(
                moduleKey: $moduleKey,
                action: 'unauthorized_module_access_blocked',
                entityType: 'HospitalModule',
                entityId: null,
                details: [
                    'attempted_module' => $moduleKey,
                    'hospital_id' => $hospitalId,
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ]
            );

            $available = ModuleRegistry::getAvailableModules();
            $moduleName = $available[$moduleKey]['name'] ?? $moduleKey;

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Module Not Enabled',
                    'message' => "The extension '{$moduleName}' is not enabled for your hospital. Contact Super Admin to subscribe.",
                ], 403);
            }

            return response()->view('errors.module_disabled', [
                'moduleKey' => $moduleKey,
                'moduleName' => $moduleName,
                'hospital' => TenantManager::getHospital(),
            ], 403);
        }

        return $next($request);
    }
}
