<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\Branch;
use App\Models\DispenseRecord;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\PharmacyStore;
use App\Models\Prescription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PharmacyController extends Controller
{
    /**
     * Pharmacy Management, Interactive POS & Medicine Stock Center
     */
    public function index()
    {
        $hospital = TenantManager::getHospital();
        $medicines = Medicine::with('batches')->latest()->get();
        $batches = MedicineBatch::with('medicine')->latest()->get();
        $dispenses = DispenseRecord::with(['patient', 'batch.medicine', 'dispensedBy'])->latest()->take(25)->get();
        $patients = Patient::all();
        $pharmacists = User::whereIn('role', ['pharmacist', 'hospital_admin'])->get();

        // Stock metrics
        $totalStockValue = $batches->sum(function ($b) {
            return $b->quantity_in_stock * $b->selling_price;
        });

        $lowStockMedicines = $medicines->filter(function ($m) {
            return $m->total_stock <= $m->min_reorder_level;
        });

        $nearExpiryBatches = $batches->filter(function ($b) {
            return $b->expiry_date && Carbon::parse($b->expiry_date)->diffInDays(now(), false) > -90;
        });

        // Live Doctor Prescriptions Queue (Un-dispensed prescriptions written by hospital doctors)
        $pendingPrescriptions = Prescription::where('is_dispensed', false)
            ->with(['patient', 'doctor', 'consultation.appointment'])
            ->latest()
            ->take(20)
            ->get();

        // Pharmacy Outlets (Standalone HQ & Branch Connected)
        $pharmacyStores = PharmacyStore::with('branch')->latest()->get();
        $branches = Branch::where('hospital_id', $hospital?->id)
            ->where(function ($q) {
                $q->whereNull('facility_type')->orWhere('facility_type', 'hospital');
            })
            ->get();

        return view('modules.pharmacy.index', compact(
            'medicines',
            'batches',
            'dispenses',
            'patients',
            'pharmacists',
            'totalStockValue',
            'lowStockMedicines',
            'nearExpiryBatches',
            'pendingPrescriptions',
            'pharmacyStores',
            'branches'
        ));
    }

    /**
     * Interactive Pharmacy POS (Point of Sale) Checkout
     */
    public function posCheckout(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'customer_name' => 'nullable|string|max:150',
            'prescription_id' => 'nullable|exists:prescriptions,id',
            'medicine_batch_id' => 'required|exists:medicine_batches,id',
            'quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:upi,cash,card,insurance',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $result = DB::transaction(function () use ($validated) {
            $batch = MedicineBatch::with('medicine')->lockForUpdate()->findOrFail($validated['medicine_batch_id']);

            if ($batch->quantity_in_stock < $validated['quantity']) {
                throw new \Exception("Insufficient stock for {$batch->medicine->brand_name} (Batch #{$batch->batch_number}). Available: {$batch->quantity_in_stock} units in Rack {$batch->rack_location} {$batch->shelf_number}.");
            }

            $unitPrice = (float) $batch->selling_price;
            $subtotal = $unitPrice * $validated['quantity'];
            $discount = (float) ($validated['discount_amount'] ?? 0);
            $totalPrice = max(0, $subtotal - $discount);

            $user = Auth::user() ?? User::where('role', 'pharmacist')->first();
            $patientId = $validated['patient_id'] ?? null;

            // If walk-in patient without ID, we can link or note
            $customerDisplay = $patientId ? Patient::find($patientId)?->name : ($validated['customer_name'] ?? 'Walk-In Customer');

            // Create Dispense Record
            $dispense = DispenseRecord::create([
                'patient_id' => $patientId,
                'medicine_batch_id' => $batch->id,
                'dispensed_by_id' => $user?->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'dispensed_at' => now(),
                'notes' => ($validated['notes'] ? $validated['notes'].' • ' : '').'POS Paid via '.strtoupper($validated['payment_method'])." [Customer: {$customerDisplay}]",
            ]);

            // Decrement Batch Stock atomically
            $batch->decrement('quantity_in_stock', $validated['quantity']);

            // Mark doctor prescription as dispensed if linked
            if (! empty($validated['prescription_id'])) {
                $prescription = Prescription::find($validated['prescription_id']);
                if ($prescription) {
                    $prescription->update(['is_dispensed' => true]);
                }
            }

            // Generate POS Tax Invoice / Receipt Number
            $receiptNumber = 'RX-POS-'.strtoupper(Str::random(6)).'-'.$dispense->id;

            return compact('batch', 'dispense', 'receiptNumber', 'customerDisplay', 'unitPrice', 'discount', 'totalPrice', 'user');
        });

        $batch = $result['batch'];
        $dispense = $result['dispense'];
        $receiptNumber = $result['receiptNumber'];
        $customerDisplay = $result['customerDisplay'];
        $unitPrice = $result['unitPrice'];
        $discount = $result['discount'];
        $totalPrice = $result['totalPrice'];
        $user = $result['user'];

        // Audit Log
        AuditLogger::log(
            moduleKey: 'pharmacy',
            action: 'pos_sale_completed',
            entityType: 'DispenseRecord',
            entityId: $dispense->id,
            details: [
                'receipt_no' => $receiptNumber,
                'medicine' => $batch->medicine->brand_name,
                'batch' => $batch->batch_number,
                'qty' => $validated['quantity'],
                'total' => $totalPrice,
                'mode' => $validated['payment_method'],
            ]
        );

        $posReceipt = [
            'receipt_number' => $receiptNumber,
            'customer' => $customerDisplay,
            'medicine_name' => $batch->medicine->brand_name,
            'batch_number' => $batch->batch_number,
            'expiry_date' => $batch->expiry_date,
            'quantity' => $validated['quantity'],
            'unit_price' => $unitPrice,
            'discount' => $discount,
            'total_amount' => $totalPrice,
            'payment_method' => strtoupper($validated['payment_method']),
            'dispensed_at' => now()->format('d M Y, h:i A'),
            'pharmacist_name' => $user?->name ?? 'Chief Pharmacist',
        ];

        return redirect()->back()->with('pos_receipt', $posReceipt)
            ->with('success', "POS Sale Completed! Dispensed {$validated['quantity']} units of {$batch->medicine->brand_name} (₹{$totalPrice} via ".strtoupper($validated['payment_method']).').');
    }

    /**
     * Add new Medicine Formulary Item
     */
    public function storeMedicine(Request $request)
    {
        $validated = $request->validate([
            'brand_name' => 'required|string|max:150',
            'generic_name' => 'required|string|max:150',
            'category' => 'required|string|max:100',
            'hsn_code' => 'nullable|string|max:50',
            'dosage_form' => 'required|in:tablet,capsule,syrup,injection,iv_fluid,ointment,inhaler',
            'strength' => 'nullable|string',
            'manufacturer' => 'nullable|string',
            'min_reorder_level' => 'required|integer|min:0',
        ]);

        $code = 'MED-'.strtoupper(substr($validated['brand_name'], 0, 3)).'-'.(Medicine::count() + 101);

        $med = Medicine::create(array_merge($validated, [
            'code' => $code,
            'hsn_code' => $validated['hsn_code'] ?? '30049099',
            'is_active' => true,
        ]));

        AuditLogger::log(
            moduleKey: 'pharmacy',
            action: 'medicine_formulary_item_added',
            entityType: 'Medicine',
            entityId: $med->id,
            details: ['code' => $code, 'brand' => $med->brand_name, 'generic' => $med->generic_name]
        );

        return redirect()->back()->with('success', "Medicine {$med->brand_name} added to hospital formulary.");
    }

    /**
     * Receive New Medicine Batch (Stock Replenishment & Rack/Shelf Allocation)
     */
    public function storeBatch(Request $request)
    {
        $validated = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'batch_number' => 'required|string|max:50',
            'expiry_date' => 'required|date',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity_in_stock' => 'required|integer|min:1',
            'rack_location' => 'nullable|string|max:100',
            'shelf_number' => 'nullable|string|max:50',
            'box_number' => 'nullable|string|max:50',
        ]);

        $batch = MedicineBatch::create($validated);

        AuditLogger::log(
            moduleKey: 'pharmacy',
            action: 'medicine_batch_received',
            entityType: 'MedicineBatch',
            entityId: $batch->id,
            details: ['batch_number' => $batch->batch_number, 'quantity' => $batch->quantity_in_stock]
        );

        return redirect()->back()->with('success', "Stock Received! Batch #{$batch->batch_number} added ({$batch->quantity_in_stock} units).");
    }

    /**
     * Standard prescription dispense
     */
    public function dispense(Request $request)
    {
        return $this->posCheckout($request);
    }

    /**
     * Register a new Pharmacy Store / Outlet (Standalone Super Admin or Branch Connected)
     */
    public function storeStore(Request $request)
    {
        $hospital = TenantManager::getHospital();
        if (! $hospital) {
            return back()->with('error', 'Select a hospital first.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:pharmacy_stores,code',
            'store_type' => 'required|in:retail_counter,ipd_pharmacy,emergency_pharmacy,standalone_retail,central_warehouse',
            'connection_mode' => 'required|in:standalone,branch_connected',
            'branch_id' => 'nullable|exists:branches,id',
            'license_number' => 'nullable|string|max:100',
            'manager_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
        ]);

        $isStandalone = $validated['connection_mode'] === 'standalone';
        $branchId = $isStandalone ? null : ($validated['branch_id'] ?? null);

        $store = PharmacyStore::create([
            'hospital_id' => $hospital->id,
            'branch_id' => $branchId,
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'slug' => Str::slug($validated['name']).'-'.strtolower($validated['code']),
            'store_type' => $validated['store_type'],
            'is_standalone' => $isStandalone,
            'license_number' => $validated['license_number'] ?? 'DL-20B-'.rand(10000, 99999),
            'manager_name' => $validated['manager_name'] ?? 'Chief Pharmacist',
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => true,
        ]);

        // Synchronize corresponding Branch record for outlet-based routing & POS
        $existingBranch = Branch::where('code', $store->code)->first();
        if (! $existingBranch) {
            Branch::create([
                'hospital_id' => $hospital->id,
                'name' => $store->name,
                'code' => $store->code,
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400001',
                'address' => $store->address,
                'phone' => $store->phone,
                'email' => $store->email,
                'manager_name' => $store->manager_name,
                'is_main_branch' => false,
                'facility_type' => 'pharmacy',
                'is_standalone' => $isStandalone,
                'parent_branch_id' => $branchId,
                'license_number' => $store->license_number,
                'is_active' => true,
            ]);
        }

        AuditLogger::log(
            action: 'pharmacy.store_created',
            auditableType: 'PharmacyStore',
            auditableId: $store->id,
            payload: [
                'name' => $store->name,
                'code' => $store->code,
                'store_type' => $store->store_type,
                'is_standalone' => $isStandalone,
                'branch_id' => $branchId,
            ]
        );

        $governance = $isStandalone ? 'Direct Super Admin / Standalone Store' : 'Connected to Hospital Branch';

        return redirect()->back()->with('success', "Pharmacy Outlet '{$store->name}' ({$store->code}) registered successfully as {$governance}.");
    }
}
