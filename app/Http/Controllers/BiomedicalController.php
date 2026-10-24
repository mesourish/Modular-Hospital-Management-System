<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\BiomedicalEquipment;
use App\Models\BiomedicalTicket;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BiomedicalController extends Controller
{
    /**
     * Biomedical Engineering & Hospital Asset Maintenance
     */
    public function index(Request $request, ?Branch $outlet = null)
    {
        if ($outlet && $outlet->exists) {
            TenantManager::setActiveBranch($outlet);
        }

        $hospital = TenantManager::getHospital();
        $activeBranch = $outlet ?? TenantManager::getBranch() ?? Branch::where('hospital_id', $hospital?->id)->first();
        $branches = Branch::where('hospital_id', $hospital?->id)->get();

        $selectedBranchId = $request->input('branch_id', $activeBranch?->id);

        $equipQuery = BiomedicalEquipment::with(['department', 'branch', 'tickets']);
        $ticketQuery = BiomedicalTicket::with(['equipment', 'reportedBy', 'branch']);

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $equipQuery->where('branch_id', (int) $selectedBranchId);
            $ticketQuery->where('branch_id', (int) $selectedBranchId);
        }

        $equipmentList = $equipQuery->orderBy('name')->get();
        $tickets = $ticketQuery->latest('reported_at')->get();

        // Metrics
        $totalAssets = $equipmentList->count();
        $operationalCount = $equipmentList->where('status', 'operational')->count();
        $calibrationDueCount = $equipmentList->where('status', 'calibration_due')->count();
        $openTicketsCount = $tickets->whereIn('status', ['open', 'in_progress'])->count();

        $departments = Department::where('hospital_id', $hospital?->id)->get();

        return view('modules.biomedical.index', compact(
            'equipmentList',
            'tickets',
            'totalAssets',
            'operationalCount',
            'calibrationDueCount',
            'openTicketsCount',
            'branches',
            'selectedBranchId',
            'activeBranch',
            'hospital',
            'departments'
        ));
    }

    /**
     * Enroll Biomedical Equipment Asset
     */
    public function storeEquipment(Request $request, ?Branch $outlet = null)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'asset_code' => 'required|string|max:50|unique:biomedical_equipment,asset_code',
            'category' => 'required|string|max:100',
            'manufacturer' => 'nullable|string|max:150',
            'model_number' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'location_room' => 'nullable|string|max:100',
            'installation_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'last_calibration_date' => 'nullable|date',
            'next_calibration_due' => 'nullable|date',
            'status' => 'required|in:operational,calibration_due,under_repair,condemned',
            'technical_specifications' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $hospital = TenantManager::getHospital();
        $branchId = $validated['branch_id'] ?? $outlet?->id ?? TenantManager::getBranchId() ?? Branch::where('hospital_id', $hospital?->id)->first()?->id;

        $equipment = BiomedicalEquipment::create([
            'hospital_id' => $hospital?->id ?? 1,
            'branch_id' => $branchId,
            'department_id' => $validated['department_id'] ?? null,
            'name' => $validated['name'],
            'asset_code' => strtoupper($validated['asset_code']),
            'category' => $validated['category'],
            'manufacturer' => $validated['manufacturer'] ?? null,
            'model_number' => $validated['model_number'] ?? null,
            'serial_number' => $validated['serial_number'] ?? null,
            'location_room' => $validated['location_room'] ?? null,
            'installation_date' => $validated['installation_date'] ?? null,
            'warranty_expiry' => $validated['warranty_expiry'] ?? null,
            'last_calibration_date' => $validated['last_calibration_date'] ?? null,
            'next_calibration_due' => $validated['next_calibration_due'] ?? null,
            'status' => $validated['status'],
            'technical_specifications' => $validated['technical_specifications'] ?? null,
        ]);

        AuditLogger::log(
            moduleKey: 'biomedical',
            action: 'biomedical_asset_registered',
            entityType: 'BiomedicalEquipment',
            entityId: $equipment->id,
            details: ['asset_code' => $equipment->asset_code, 'name' => $equipment->name]
        );

        return back()->with('success', "Medical equipment {$equipment->name} ({$equipment->asset_code}) enrolled.");
    }

    /**
     * File Breakdown Fault Ticket
     */
    public function storeTicket(Request $request, ?Branch $outlet = null)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:biomedical_equipment,id',
            'severity' => 'required|in:critical_life_support,major_downtime,routine_check',
            'fault_description' => 'required|string',
            'technician_assigned' => 'nullable|string|max:150',
        ]);

        $hospital = TenantManager::getHospital();
        $equip = BiomedicalEquipment::findOrFail($validated['equipment_id']);

        $ticketNo = 'TKT-BME-'.date('Ymd').'-'.rand(100, 999);

        $ticket = BiomedicalTicket::create([
            'hospital_id' => $hospital?->id ?? 1,
            'branch_id' => $equip->branch_id,
            'equipment_id' => $equip->id,
            'reported_by_id' => Auth::id() ?? 1,
            'ticket_number' => $ticketNo,
            'severity' => $validated['severity'],
            'fault_description' => $validated['fault_description'],
            'reported_at' => now(),
            'technician_assigned' => $validated['technician_assigned'] ?? 'Duty Bioengineer',
            'status' => 'open',
        ]);

        $equip->update(['status' => 'under_repair']);

        AuditLogger::log(
            moduleKey: 'biomedical',
            action: 'biomedical_ticket_logged',
            entityType: 'BiomedicalTicket',
            entityId: $ticket->id,
            details: ['ticket' => $ticketNo, 'asset' => $equip->name, 'severity' => $ticket->severity]
        );

        return back()->with('success', "Service Ticket #{$ticketNo} generated for {$equip->name}.");
    }

    /**
     * Resolve Ticket
     */
    public function resolveTicket(Request $request, BiomedicalTicket $ticket)
    {
        $validated = $request->validate([
            'corrective_action' => 'required|string',
            'repair_cost' => 'nullable|numeric|min:0',
        ]);

        $ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'corrective_action' => $validated['corrective_action'],
            'repair_cost' => $validated['repair_cost'] ?? 0.00,
        ]);

        $ticket->equipment->update(['status' => 'operational']);

        return back()->with('success', "Ticket #{$ticket->ticket_number} marked as resolved. Equipment is now operational.");
    }
}
