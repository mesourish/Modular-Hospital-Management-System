<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\Branch;
use App\Models\DispenseRecord;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PharmacyPosController extends Controller
{
    /**
     * Dedicated Pharmacy Point-Of-Sale Terminal
     */
    public function index(Request $request, ?Branch $outlet = null)
    {
        if ($outlet && $outlet->exists) {
            TenantManager::setActiveBranch($outlet);
        }

        $hospital = TenantManager::getHospital();
        $activeBranch = $outlet ?? TenantManager::getBranch() ?? Branch::where('hospital_id', $hospital?->id)->first();

        $batches = MedicineBatch::with('medicine')
            ->where('quantity_in_stock', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();

        $patients = Patient::select('id', 'mrn', 'first_name', 'last_name', 'phone', 'blood_group', 'allergies')->get();
        $doctors = User::whereIn('role', ['doctor', 'hospital_admin'])->get();

        $recentInvoices = Invoice::with(['patient', 'items'])
            ->whereHas('items', function ($q) {
                $q->where('service_type', 'pharmacy');
            })
            ->latest()
            ->take(8)
            ->get();

        return view('modules.pharmacy.pos', compact(
            'batches',
            'patients',
            'doctors',
            'recentInvoices',
            'hospital',
            'activeBranch'
        ));
    }

    /**
     * Atomic POS Checkout & Stock Deduction
     */
    public function checkout(Request $request, ?Branch $outlet = null)
    {
        if ($outlet && $outlet->exists) {
            TenantManager::setActiveBranch($outlet);
        }

        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'walkin_name' => 'nullable|string|max:150',
            'walkin_phone' => 'nullable|string|max:20',
            'doctor_id' => 'nullable|exists:users,id',
            'doctor_name' => 'nullable|string|max:150',
            'payment_method' => 'required|in:cash,upi,card,insurance,bank_transfer',
            'transaction_ref' => 'nullable|string|max:100',
            'overall_discount' => 'nullable|numeric|min:0',
            'cart_data' => 'required|string', // JSON string containing line items
        ]);

        $cartItems = json_decode($validated['cart_data'], true);

        if (! is_array($cartItems) || count($cartItems) === 0) {
            return redirect()->back()->with('error', 'The POS basket is empty. Please add at least one medication to proceed.');
        }

        $hospital = TenantManager::getHospital();
        $branch = $outlet ?? TenantManager::getBranch() ?? Branch::where('hospital_id', $hospital?->id)->first();
        $currentUser = Auth::user() ?? User::where('role', 'pharmacist')->first();

        try {
            $invoice = DB::transaction(function () use ($cartItems, $validated, $hospital, $branch, $currentUser) {
                // 1. First Pass: Verify exact stock availability for all items
                $totalTaxable = 0.00;
                $totalCgst = 0.00;
                $totalSgst = 0.00;
                $lineItemDetails = [];

                foreach ($cartItems as $item) {
                    $batchId = (int) ($item['batch_id'] ?? 0);
                    $qty = (int) ($item['quantity'] ?? 1);
                    $rate = (float) ($item['unit_price'] ?? 0);
                    $discPercent = (float) ($item['discount_percent'] ?? 0);
                    $gstPercent = (float) ($item['gst_rate'] ?? 12); // Default Indian GST on medicines is 12%

                    $batch = MedicineBatch::with('medicine')->lockForUpdate()->findOrFail($batchId);

                    if ($batch->quantity_in_stock < $qty) {
                        throw new \Exception("Insufficient stock for {$batch->medicine->brand_name} (Batch: {$batch->batch_number}). Only {$batch->quantity_in_stock} units left in Rack {$batch->rack_location} {$batch->shelf_number}.");
                    }

                    $gross = $qty * $rate;
                    $lineDiscount = round($gross * ($discPercent / 100), 2);
                    $taxable = max(0, $gross - $lineDiscount);

                    // Split GST into CGST (50%) and SGST (50%)
                    $cgstRate = $gstPercent / 2;
                    $sgstRate = $gstPercent / 2;
                    $cgstAmt = round($taxable * ($cgstRate / 100), 2);
                    $sgstAmt = round($taxable * ($sgstRate / 100), 2);

                    $totalTaxable += $taxable;
                    $totalCgst += $cgstAmt;
                    $totalSgst += $sgstAmt;

                    $lineItemDetails[] = [
                        'batch' => $batch,
                        'qty' => $qty,
                        'rate' => $rate,
                        'discount' => $lineDiscount,
                        'taxable' => $taxable,
                        'cgst' => $cgstAmt,
                        'sgst' => $sgstAmt,
                    ];
                }

                $overallDiscount = (float) ($validated['overall_discount'] ?? 0);
                $finalTaxable = max(0, $totalTaxable - $overallDiscount);
                $netPayable = round($finalTaxable + $totalCgst + $totalSgst, 2);

                $patient = ! empty($validated['patient_id']) ? Patient::find($validated['patient_id']) : null;
                if (! $patient) {
                    $walkinName = ! empty($validated['walkin_name']) ? trim($validated['walkin_name']) : 'Walk-in OTC Customer';
                    $patient = Patient::firstOrCreate(
                        [
                            'hospital_id' => $hospital?->id ?? 1,
                            'mrn' => 'OTC-WALKIN',
                        ],
                        [
                            'branch_id' => $branch?->id ?? 1,
                            'first_name' => $walkinName,
                            'last_name' => '(OTC)',
                            'gender' => 'other',
                            'phone' => $validated['walkin_phone'] ?? '+91 00000 00000',
                            'email' => 'otc.pharmacy@apollocare.in',
                            'blood_group' => 'Unknown',
                            'date_of_birth' => '1990-01-01',
                            'address' => 'Walk-in Pharmacy Counter',
                            'city' => 'Mumbai',
                            'state' => 'Maharashtra',
                            'pincode' => '400001',
                        ]
                    );
                }
                $customerLabel = ! empty($validated['walkin_name']) ? $validated['walkin_name'] : $patient->full_name;

                $invNumber = 'INV-POS-'.date('Ymd').'-'.str_pad((string) (Invoice::count() + 1), 4, '0', STR_PAD_LEFT);
                $gstin = $branch?->phone ? '27AAAAA0000A1Z5' : '27AAAPL1234F1Z8';

                $prescriberNote = ! empty($validated['doctor_name']) ? " • Prescriber: {$validated['doctor_name']}" : '';

                // 2. Create Master GST Invoice
                $invoice = Invoice::create([
                    'hospital_id' => $hospital?->id ?? 1,
                    'branch_id' => $branch?->id ?? 1,
                    'patient_id' => $patient?->id,
                    'invoice_number' => $invNumber,
                    'invoice_date' => now()->toDateString(),
                    'total_amount' => $totalTaxable,
                    'discount_amount' => $overallDiscount,
                    'tax_amount' => $totalCgst + $totalSgst,
                    'cgst_amount' => $totalCgst,
                    'sgst_amount' => $totalSgst,
                    'gstin_number' => $gstin,
                    'net_payable' => $netPayable,
                    'paid_amount' => $netPayable,
                    'balance_due' => 0.00,
                    'payment_status' => 'paid',
                    'notes' => "POS Counter Sale [Customer: {$customerLabel}] • Payment: ".strtoupper($validated['payment_method']).$prescriberNote,
                ]);

                // 3. Process line items: deduct exact stock and record dispenses
                foreach ($lineItemDetails as $line) {
                    /** @var MedicineBatch $batch */
                    $batch = $line['batch'];
                    $qty = $line['qty'];

                    // Atomically deduct stock from exact batch and shelf
                    $batch->decrement('quantity_in_stock', $qty);

                    $rackLocationString = trim(($batch->rack_location ?? 'Rack A').' '.($batch->shelf_number ?? '').' '.($batch->box_number ?? ''));

                    InvoiceItem::create([
                        'hospital_id' => $hospital?->id ?? 1,
                        'invoice_id' => $invoice->id,
                        'service_type' => 'pharmacy',
                        'item_name' => $batch->medicine->brand_name.' ('.$batch->medicine->generic_name.')',
                        'hsn_code' => $batch->medicine->hsn_code ?? '30049099',
                        'batch_number' => $batch->batch_number,
                        'expiry_date' => $batch->expiry_date,
                        'rack_location' => $rackLocationString,
                        'quantity' => $qty,
                        'unit_price' => $line['rate'],
                        'total_price' => $line['taxable'],
                        'cgst_amount' => $line['cgst'],
                        'sgst_amount' => $line['sgst'],
                    ]);

                    DispenseRecord::create([
                        'hospital_id' => $hospital?->id ?? 1,
                        'patient_id' => $patient?->id,
                        'medicine_batch_id' => $batch->id,
                        'dispensed_by_id' => $currentUser?->id,
                        'quantity' => $qty,
                        'unit_price' => $line['rate'],
                        'total_price' => $line['taxable'] + $line['cgst'] + $line['sgst'],
                        'dispensed_at' => now(),
                        'notes' => "POS Invoice #{$invNumber} [{$rackLocationString}]",
                    ]);
                }

                // 4. Record Payment Receipt
                $receiptNumber = 'RCT-POS-'.date('Y').'-'.rand(10000, 99999);
                Payment::create([
                    'hospital_id' => $hospital?->id ?? 1,
                    'invoice_id' => $invoice->id,
                    'receipt_number' => $receiptNumber,
                    'payment_date' => now(),
                    'amount' => $netPayable,
                    'payment_method' => $validated['payment_method'],
                    'transaction_ref' => $validated['transaction_ref'] ?? ('CASH-'.time()),
                    'collected_by_id' => $currentUser?->id,
                ]);

                AuditLogger::log(
                    moduleKey: 'pharmacy',
                    action: 'pos_checkout_completed',
                    entityType: 'Invoice',
                    entityId: $invoice->id,
                    details: [
                        'invoice' => $invNumber,
                        'net_payable' => $netPayable,
                        'items_count' => count($lineItemDetails),
                        'outlet' => $branch?->name,
                    ]
                );

                return $invoice;
            });

            return redirect()->route('billing.invoices.print', $invoice->id)
                ->with('success', "Sale Completed Successfully! Net Amount: ₹{$invoice->net_payable}. Stock updated in real-time.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'POS Checkout Failed: '.$e->getMessage());
        }
    }
}
