<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\Admission;
use App\Models\Branch;
use App\Models\DietaryOrder;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DietaryController extends Controller
{
    /**
     * Inpatient Dietary & Nutritional Care Management
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

        $query = DietaryOrder::with(['patient', 'admission.bed.ward', 'prescribedBy', 'branch']);

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $query->where('branch_id', (int) $selectedBranchId);
        }

        $orders = $query->latest('order_date')->take(50)->get();

        // Meal counters
        $totalOrders = $orders->count();
        $deliveredCount = $orders->where('status', 'delivered')->count();
        $cookingCount = $orders->where('status', 'cooking')->count();
        $prescribedCount = $orders->where('status', 'prescribed')->count();

        $patients = Patient::select('id', 'mrn', 'first_name', 'last_name', 'allergies', 'chronic_conditions')->get();
        $admissions = Admission::with(['patient', 'bed.ward'])->where('status', 'admitted')->get();
        $doctors = User::whereIn('role', ['doctor', 'hospital_admin'])->get();

        return view('modules.dietary.index', compact(
            'orders',
            'totalOrders',
            'deliveredCount',
            'cookingCount',
            'prescribedCount',
            'branches',
            'selectedBranchId',
            'activeBranch',
            'hospital',
            'patients',
            'admissions',
            'doctors'
        ));
    }

    /**
     * Prescribe Clinical Meal
     */
    public function storeOrder(Request $request, ?Branch $outlet = null)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'admission_id' => 'nullable|exists:admissions,id',
            'diet_type' => 'required|string|max:100',
            'meal_slot' => 'required|in:breakfast,lunch,evening_snack,dinner',
            'order_date' => 'required|date',
            'room_bed_number' => 'nullable|string|max:100',
            'calorie_target' => 'nullable|string|max:50',
            'allergies_and_restrictions' => 'nullable|string|max:255',
            'kitchen_instructions' => 'nullable|string|max:500',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $hospital = TenantManager::getHospital();
        $branchId = $validated['branch_id'] ?? $outlet?->id ?? TenantManager::getBranchId() ?? Branch::where('hospital_id', $hospital?->id)->first()?->id;

        $order = DietaryOrder::create([
            'hospital_id' => $hospital?->id ?? 1,
            'branch_id' => $branchId,
            'patient_id' => $validated['patient_id'],
            'admission_id' => $validated['admission_id'] ?? null,
            'prescribed_by_id' => Auth::id(),
            'diet_type' => $validated['diet_type'],
            'meal_slot' => $validated['meal_slot'],
            'order_date' => $validated['order_date'],
            'room_bed_number' => $validated['room_bed_number'] ?? 'Ward Room',
            'calorie_target' => $validated['calorie_target'] ?? '1800 kcal',
            'allergies_and_restrictions' => $validated['allergies_and_restrictions'] ?? null,
            'kitchen_instructions' => $validated['kitchen_instructions'] ?? null,
            'status' => 'prescribed',
        ]);

        AuditLogger::log(
            moduleKey: 'dietary',
            action: 'dietary_order_prescribed',
            entityType: 'DietaryOrder',
            entityId: $order->id,
            details: ['diet_type' => $order->diet_type, 'slot' => $order->meal_slot, 'patient_id' => $order->patient_id]
        );

        return back()->with('success', "Dietary order prescribed for {$order->patient->name} ({$order->diet_type} - ".strtoupper($order->meal_slot).').');
    }

    /**
     * Update Kitchen Delivery Status
     */
    public function updateStatus(Request $request, DietaryOrder $dietaryOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:prescribed,cooking,quality_checked,delivered,cancelled',
        ]);

        $dietaryOrder->update(['status' => $validated['status']]);

        return back()->with('success', "Dietary order #{$dietaryOrder->id} updated to ".strtoupper($validated['status']).'.');
    }
}
