<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\Ambulance;
use App\Models\AmbulanceTrip;
use App\Models\Branch;
use App\Models\Patient;
use Illuminate\Http\Request;

class AmbulanceController extends Controller
{
    /**
     * Ambulance Fleet & Emergency Dispatch Control
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

        $ambulancesQuery = Ambulance::with('branch');
        $tripsQuery = AmbulanceTrip::with(['ambulance', 'patient', 'branch']);

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $ambulancesQuery->where('branch_id', (int) $selectedBranchId);
            $tripsQuery->where('branch_id', (int) $selectedBranchId);
        }

        $ambulances = $ambulancesQuery->latest()->get();
        $trips = $tripsQuery->latest('dispatched_at')->take(40)->get();

        // Scheduled calendar trips (scheduled or future trips)
        $scheduledTrips = (clone $tripsQuery)
            ->where(function ($q) {
                $q->where('status', 'scheduled')
                    ->orWhereNotNull('scheduled_for');
            })
            ->orderBy('scheduled_for', 'asc')
            ->get();

        // Unique registered drivers for quick scheduling assignment
        $knownDrivers = Ambulance::where('hospital_id', $hospital?->id)
            ->select('driver_name', 'driver_phone')
            ->whereNotNull('driver_name')
            ->distinct()
            ->get();

        // Fleet stats
        $totalVehicles = $ambulances->count();
        $availableCount = $ambulances->where('status', 'available')->count();
        $onCallCount = $ambulances->where('status', 'on_call_dispatch')->count();
        $activeTrips = $trips->whereIn('status', ['dispatched', 'patient_picked_up'])->count();
        $scheduledCount = $scheduledTrips->where('status', 'scheduled')->count();

        $patients = Patient::select('id', 'mrn', 'first_name', 'last_name', 'phone')->get();

        return view('modules.ambulance.index', compact(
            'ambulances',
            'trips',
            'scheduledTrips',
            'knownDrivers',
            'totalVehicles',
            'availableCount',
            'onCallCount',
            'activeTrips',
            'scheduledCount',
            'branches',
            'selectedBranchId',
            'activeBranch',
            'hospital',
            'patients'
        ));
    }

    /**
     * Register Ambulance Vehicle
     */
    public function storeAmbulance(Request $request, ?Branch $outlet = null)
    {
        $validated = $request->validate([
            'vehicle_number' => 'required|string|max:50|unique:ambulances,vehicle_number',
            'vehicle_model' => 'required|string|max:100',
            'type' => 'required|in:advanced_life_support,basic_life_support,neonatal_mobile_icu',
            'driver_name' => 'required|string|max:150',
            'driver_phone' => 'required|string|max:50',
            'paramedic_name' => 'nullable|string|max:150',
            'current_location' => 'nullable|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $hospital = TenantManager::getHospital();
        $branchId = $validated['branch_id'] ?? $outlet?->id ?? TenantManager::getBranchId() ?? Branch::where('hospital_id', $hospital?->id)->first()?->id;

        $ambulance = Ambulance::create([
            'hospital_id' => $hospital?->id ?? 1,
            'branch_id' => $branchId,
            'vehicle_number' => strtoupper($validated['vehicle_number']),
            'vehicle_model' => $validated['vehicle_model'],
            'type' => $validated['type'],
            'driver_name' => $validated['driver_name'],
            'driver_phone' => $validated['driver_phone'],
            'paramedic_name' => $validated['paramedic_name'] ?? 'Senior Paramedic On-Duty',
            'current_location' => $validated['current_location'] ?? 'Base Emergency Bay',
            'status' => 'available',
            'is_active' => true,
        ]);

        AuditLogger::log(
            moduleKey: 'ambulance',
            action: 'ambulance_enrolled',
            entityType: 'Ambulance',
            entityId: $ambulance->id,
            details: ['vehicle' => $ambulance->vehicle_number, 'type' => $ambulance->type]
        );

        return back()->with('success', "Ambulance {$ambulance->vehicle_number} registered in fleet.");
    }

    /**
     * Dispatch Ambulance on Emergency Call
     */
    public function storeTrip(Request $request, ?Branch $outlet = null)
    {
        $validated = $request->validate([
            'ambulance_id' => 'required|exists:ambulances,id',
            'patient_id' => 'nullable|exists:patients,id',
            'patient_name' => 'required|string|max:200',
            'patient_phone' => 'required|string|max:50',
            'pickup_address' => 'required|string|max:255',
            'destination_hospital' => 'required|string|max:255',
            'emergency_category' => 'required|string|max:100',
            'fare_amount' => 'nullable|numeric|min:0',
            'paramedic_triage_notes' => 'nullable|string|max:500',
        ]);

        $hospital = TenantManager::getHospital();
        $ambulance = Ambulance::findOrFail($validated['ambulance_id']);

        $tripNo = 'TRIP-'.date('Ymd').'-'.rand(100, 999);

        $trip = AmbulanceTrip::create([
            'hospital_id' => $hospital?->id ?? 1,
            'branch_id' => $ambulance->branch_id,
            'ambulance_id' => $ambulance->id,
            'patient_id' => $validated['patient_id'] ?? null,
            'trip_number' => $tripNo,
            'patient_name' => $validated['patient_name'],
            'patient_phone' => $validated['patient_phone'],
            'pickup_address' => $validated['pickup_address'],
            'destination_hospital' => $validated['destination_hospital'],
            'emergency_category' => $validated['emergency_category'],
            'dispatched_at' => now(),
            'status' => 'dispatched',
            'distance_km' => 10.5,
            'fare_amount' => $validated['fare_amount'] ?? 1500.00,
            'paramedic_triage_notes' => $validated['paramedic_triage_notes'] ?? null,
        ]);

        // Update vehicle status
        $ambulance->update([
            'status' => 'on_call_dispatch',
            'current_location' => 'En route to: '.$validated['pickup_address'],
        ]);

        AuditLogger::log(
            moduleKey: 'ambulance',
            action: 'emergency_trip_dispatched',
            entityType: 'AmbulanceTrip',
            entityId: $trip->id,
            details: ['trip_number' => $tripNo, 'vehicle' => $ambulance->vehicle_number, 'category' => $trip->emergency_category]
        );

        return back()->with('success', "Emergency Dispatch #{$tripNo} initiated for vehicle {$ambulance->vehicle_number}.");
    }

    /**
     * Schedule Ambulance Trip & Assign Driver Based on Customer Requirements
     */
    public function scheduleTrip(Request $request, ?Branch $outlet = null)
    {
        $validated = $request->validate([
            'ambulance_id' => 'required|exists:ambulances,id',
            'driver_name' => 'required|string|max:150',
            'driver_phone' => 'required|string|max:50',
            'patient_name' => 'required|string|max:200',
            'patient_phone' => 'required|string|max:50',
            'pickup_address' => 'required|string|max:255',
            'destination_hospital' => 'required|string|max:255',
            'scheduled_for' => 'required|date',
            'emergency_category' => 'required|string|max:100',
            'customer_notes' => 'nullable|string|max:1000',
            'fare_amount' => 'nullable|numeric|min:0',
            'patient_id' => 'nullable|exists:patients,id',
            'dispatch_immediately' => 'nullable|boolean',
        ]);

        $hospital = TenantManager::getHospital();
        $ambulance = Ambulance::findOrFail($validated['ambulance_id']);

        $tripNo = 'SCHED-'.date('Ymd').'-'.rand(1000, 9999);
        $dispatchImmediately = $request->boolean('dispatch_immediately');

        $trip = AmbulanceTrip::create([
            'hospital_id' => $hospital?->id ?? $ambulance->hospital_id,
            'branch_id' => $ambulance->branch_id,
            'ambulance_id' => $ambulance->id,
            'patient_id' => $validated['patient_id'] ?? null,
            'trip_number' => $tripNo,
            'patient_name' => $validated['patient_name'],
            'patient_phone' => $validated['patient_phone'],
            'pickup_address' => $validated['pickup_address'],
            'destination_hospital' => $validated['destination_hospital'],
            'emergency_category' => $validated['emergency_category'],
            'scheduled_for' => $validated['scheduled_for'],
            'assigned_driver_name' => $validated['driver_name'],
            'assigned_driver_phone' => $validated['driver_phone'],
            'customer_notes' => $validated['customer_notes'] ?? null,
            'dispatched_at' => $dispatchImmediately ? now() : $validated['scheduled_for'],
            'status' => $dispatchImmediately ? 'dispatched' : 'scheduled',
            'distance_km' => 12.0,
            'fare_amount' => $validated['fare_amount'] ?? 1500.00,
            'paramedic_triage_notes' => 'Customer Requirement: '.($validated['customer_notes'] ?? 'Standard Transport')." | Assigned Driver: {$validated['driver_name']}",
        ]);

        if ($dispatchImmediately) {
            $ambulance->update([
                'status' => 'on_call_dispatch',
                'driver_name' => $validated['driver_name'],
                'driver_phone' => $validated['driver_phone'],
                'current_location' => 'Dispatched to: '.$validated['pickup_address'],
            ]);
        }

        AuditLogger::log(
            moduleKey: 'ambulance',
            action: 'ambulance_trip_scheduled',
            entityType: 'AmbulanceTrip',
            entityId: $trip->id,
            details: [
                'trip_number' => $tripNo,
                'vehicle' => $ambulance->vehicle_number,
                'driver' => $validated['driver_name'],
                'scheduled_for' => $validated['scheduled_for'],
                'requirement' => $validated['customer_notes'] ?? null,
            ]
        );

        return back()->with('success', "Ambulance Trip #{$tripNo} successfully scheduled with Driver {$validated['driver_name']} for {$validated['patient_name']}.");
    }

    /**
     * Update Trip Status
     */
    public function updateTripStatus(Request $request, AmbulanceTrip $trip)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,dispatched,patient_picked_up,completed,cancelled',
            'paramedic_triage_notes' => 'nullable|string|max:500',
        ]);

        $data = ['status' => $validated['status']];
        if ($validated['status'] === 'dispatched') {
            $data['dispatched_at'] = now();
            $trip->ambulance->update([
                'status' => 'on_call_dispatch',
                'current_location' => 'Dispatched to: '.$trip->pickup_address,
            ]);
        } elseif ($validated['status'] === 'patient_picked_up') {
            $data['reached_patient_at'] = now();
            $trip->ambulance->update([
                'current_location' => 'Patient on board, en route to: '.$trip->destination_hospital,
            ]);
        } elseif (in_array($validated['status'], ['completed', 'cancelled'])) {
            if ($validated['status'] === 'completed') {
                $data['arrived_hospital_at'] = now();
            }
            $trip->ambulance->update([
                'status' => 'available',
                'current_location' => 'Hospital Base ER Bay',
            ]);
        }

        if (! empty($validated['paramedic_triage_notes'])) {
            $data['paramedic_triage_notes'] = $validated['paramedic_triage_notes'];
        }

        $trip->update($data);

        return back()->with('success', "Trip #{$trip->trip_number} updated to ".str_replace('_', ' ', strtoupper($validated['status'])).'.');
    }
}
