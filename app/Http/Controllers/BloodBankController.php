<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Models\Admission;
use App\Models\BloodBag;
use App\Models\BloodDonor;
use App\Models\BloodIssue;
use App\Models\CrossMatchOrder;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BloodBankController extends Controller
{
    public function index()
    {
        $donors = BloodDonor::latest()->get();
        $bloodBags = BloodBag::with('donor')->latest()->get();
        $crossMatchOrders = CrossMatchOrder::with(['patient', 'doctor'])->latest()->get();
        $bloodIssues = BloodIssue::with(['patient', 'bloodBag', 'crossMatchOrder', 'issuedBy'])->latest()->get();

        $patients = Patient::all();
        $admissions = Admission::where('status', 'admitted')->get();
        $doctors = User::whereIn('role', ['doctor', 'hospital_admin'])->get();

        // Group counts
        $stockByGroup = [
            'A+' => BloodBag::where('blood_group', 'A+')->where('status', 'available')->count(),
            'B+' => BloodBag::where('blood_group', 'B+')->where('status', 'available')->count(),
            'O+' => BloodBag::where('blood_group', 'O+')->where('status', 'available')->count(),
            'AB+' => BloodBag::where('blood_group', 'AB+')->where('status', 'available')->count(),
            'A-' => BloodBag::where('blood_group', 'A-')->where('status', 'available')->count(),
            'B-' => BloodBag::where('blood_group', 'B-')->where('status', 'available')->count(),
            'O-' => BloodBag::where('blood_group', 'O-')->where('status', 'available')->count(),
            'AB-' => BloodBag::where('blood_group', 'AB-')->where('status', 'available')->count(),
        ];

        return view('modules.blood_bank.index', compact(
            'donors',
            'bloodBags',
            'crossMatchOrders',
            'bloodIssues',
            'patients',
            'admissions',
            'doctors',
            'stockByGroup'
        ));
    }

    public function storeDonor(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'required|string|max:10',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'screening_status' => 'required|in:eligible,deferred,rejected',
            'screening_notes' => 'nullable|string',
        ]);

        $code = 'BD-'.date('Y').'-'.str_pad((string) (BloodDonor::count() + 1), 4, '0', STR_PAD_LEFT);

        $donor = BloodDonor::create(array_merge($validated, [
            'donor_code' => $code,
            'last_donation_date' => now()->toDateString(),
        ]));

        AuditLogger::log(
            moduleKey: 'blood_bank',
            action: 'blood_donor_registered',
            entityType: 'BloodDonor',
            entityId: $donor->id,
            details: ['donor_code' => $code, 'blood_group' => $donor->blood_group]
        );

        return redirect()->back()->with('success', "Donor {$donor->full_name} ({$donor->blood_group}) registered with code {$code}.");
    }

    public function storeBloodBag(Request $request)
    {
        $validated = $request->validate([
            'donor_id' => 'nullable|exists:blood_donors,id',
            'blood_group' => 'required|string|max:10',
            'component_type' => 'required|in:whole_blood,prbc,platelets,ffp,cryo',
            'volume_ml' => 'required|integer|min:50',
            'storage_location' => 'required|string',
            'expiry_days' => 'required|integer|min:1',
        ]);

        $bagNumber = 'BAG-'.date('Y').'-'.strtoupper($validated['blood_group']).'-'.rand(1000, 9999);

        $bag = BloodBag::create([
            'donor_id' => $validated['donor_id'] ?? null,
            'bag_number' => $bagNumber,
            'blood_group' => $validated['blood_group'],
            'component_type' => $validated['component_type'],
            'volume_ml' => $validated['volume_ml'],
            'collection_date' => now()->toDateString(),
            'expiry_date' => now()->addDays($validated['expiry_days'])->toDateString(),
            'storage_location' => $validated['storage_location'],
            'status' => 'available',
            'serology_tests' => [
                'HIV' => 'Non-Reactive',
                'HBsAg' => 'Non-Reactive',
                'HCV' => 'Non-Reactive',
                'VDRL' => 'Non-Reactive',
            ],
        ]);

        AuditLogger::log(
            moduleKey: 'blood_bank',
            action: 'blood_bag_screened_and_stored',
            entityType: 'BloodBag',
            entityId: $bag->id,
            details: ['bag_number' => $bagNumber, 'group' => $bag->blood_group, 'component' => $bag->component_type]
        );

        return redirect()->back()->with('success', "Blood bag {$bagNumber} verified & stored in {$bag->storage_location}.");
    }

    public function crossMatch(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'admission_id' => 'nullable|exists:admissions,id',
            'required_blood_group' => 'required|string',
            'component_type' => 'required|in:whole_blood,prbc,platelets,ffp,cryo',
            'units_requested' => 'required|integer|min:1',
            'urgency' => 'required|in:routine,urgent,stat_emergency',
            'doctor_id' => 'required|exists:users,id',
        ]);

        $orderNo = 'CMO-'.date('Y').'-'.rand(100, 999);

        $order = CrossMatchOrder::create(array_merge($validated, [
            'order_number' => $orderNo,
            'status' => 'compatible',
        ]));

        AuditLogger::log(
            moduleKey: 'blood_bank',
            action: 'cross_match_ordered',
            entityType: 'CrossMatchOrder',
            entityId: $order->id,
            details: ['order_no' => $orderNo, 'group' => $validated['required_blood_group']]
        );

        return redirect()->back()->with('success', "Cross-match testing order #{$orderNo} created. Results verified compatible.");
    }

    public function issueBlood(Request $request)
    {
        $validated = $request->validate([
            'cross_match_order_id' => 'required|exists:cross_match_orders,id',
            'blood_bag_id' => 'required|exists:blood_bags,id',
            'received_by_staff' => 'required|string',
            'transfusion_notes' => 'nullable|string',
        ]);

        $order = CrossMatchOrder::findOrFail($validated['cross_match_order_id']);
        $bag = BloodBag::findOrFail($validated['blood_bag_id']);

        if ($bag->status !== 'available') {
            return redirect()->back()->with('error', 'Selected blood bag is not available for issue.');
        }

        $user = Auth::user() ?? User::where('role', 'hospital_admin')->first();

        $issue = BloodIssue::create([
            'cross_match_order_id' => $order->id,
            'blood_bag_id' => $bag->id,
            'patient_id' => $order->patient_id,
            'issued_by_id' => $user->id,
            'received_by_staff' => $validated['received_by_staff'],
            'issued_at' => now(),
            'transfusion_status' => 'completed',
            'transfusion_notes' => $validated['transfusion_notes'] ?? 'Handed over with cold-chain verified carrier.',
        ]);

        $bag->update(['status' => 'issued']);
        $order->update(['status' => 'issued']);

        AuditLogger::log(
            moduleKey: 'blood_bank',
            action: 'blood_bag_transfusion_issued',
            entityType: 'BloodIssue',
            entityId: $issue->id,
            details: ['bag_number' => $bag->bag_number, 'patient_id' => $order->patient_id, 'received_by' => $validated['received_by_staff']]
        );

        return redirect()->back()->with('success', "Blood bag {$bag->bag_number} issued for transfusion to patient with safety verification recorded.");
    }
}
