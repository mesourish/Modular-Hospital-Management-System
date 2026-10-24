<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\Admission;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['patient', 'admission', 'items', 'payments'])->latest()->get();
        $patients = Patient::all();
        $admissions = Admission::where('status', 'admitted')->get();
        $totalBilled = Invoice::sum('net_payable');
        $totalPaid = Payment::sum('amount');
        $totalOutstanding = Invoice::sum('balance_due');

        return view('modules.billing.index', compact(
            'invoices',
            'patients',
            'admissions',
            'totalBilled',
            'totalPaid',
            'totalOutstanding'
        ));
    }

    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'admission_id' => 'nullable|exists:admissions,id',
            'items' => 'required|array|min:1',
            'items.*.service_type' => 'required|in:consultation,bed_charge,radiology,blood_bank,pharmacy,procedure,nursing',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $discount = (float) ($validated['discount_amount'] ?? 0);
        $tax = round(($subtotal - $discount) * 0.05, 2);
        $net = ($subtotal - $discount) + $tax;

        $invNo = 'INV-'.date('Y').'-'.str_pad((string) (Invoice::count() + 1), 5, '0', STR_PAD_LEFT);

        $invoice = Invoice::create([
            'patient_id' => $validated['patient_id'],
            'admission_id' => $validated['admission_id'] ?? null,
            'invoice_number' => $invNo,
            'invoice_date' => now()->toDateString(),
            'total_amount' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'net_payable' => $net,
            'paid_amount' => 0.00,
            'balance_due' => $net,
            'payment_status' => 'unpaid',
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_type' => $item['service_type'],
                'item_name' => $item['item_name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        AuditLogger::log(
            moduleKey: 'billing',
            action: 'invoice_generated',
            entityType: 'Invoice',
            entityId: $invoice->id,
            details: ['invoice_number' => $invNo, 'net_payable' => $net]
        );

        return redirect()->back()->with('success', "Invoice #{$invNo} created for Rs {$net}.");
    }

    public function recordPayment(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,card,upi,insurance,bank_transfer',
            'transaction_ref' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);
        $rctNo = 'RCT-'.date('Y').'-'.rand(1000, 9999);
        $user = Auth::user() ?? User::where('role', 'hospital_admin')->first();

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'receipt_number' => $rctNo,
            'payment_date' => now(),
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'transaction_ref' => $validated['transaction_ref'] ?? null,
            'collected_by_id' => $user->id,
        ]);

        $newPaid = $invoice->paid_amount + $validated['amount'];
        $newBalance = max(0, $invoice->net_payable - $newPaid);
        $status = $newBalance <= 0 ? 'paid' : 'partially_paid';

        $invoice->update([
            'paid_amount' => $newPaid,
            'balance_due' => $newBalance,
            'payment_status' => $status,
        ]);

        AuditLogger::log(
            moduleKey: 'billing',
            action: 'payment_collected',
            entityType: 'Payment',
            entityId: $payment->id,
            details: ['receipt_number' => $rctNo, 'amount' => $payment->amount, 'method' => $payment->payment_method]
        );

        return redirect()->back()->with('success', "Payment receipt #{$rctNo} recorded. Current balance: Rs {$newBalance}.");
    }

    /**
     * Printable Indian GST Tax Invoice Template
     */
    public function printGstInvoice(Invoice $invoice)
    {
        $invoice->load(['patient', 'admission.bed.ward', 'items', 'payments.collectedBy', 'branch']);
        $hospital = TenantManager::getHospital();
        $branch = $invoice->branch ?? TenantManager::getBranch() ?? Branch::where('hospital_id', $invoice->hospital_id)->first();

        return view('modules.billing.print_gst_invoice', compact('invoice', 'hospital', 'branch'));
    }
}
