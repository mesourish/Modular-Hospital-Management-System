<?php

namespace App\Http\Controllers;

use App\Core\ModuleRegistry\ModuleRegistry;
use App\Core\Tenancy\TenantManager;
use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\BloodBag;
use App\Models\DutyRoster;
use App\Models\Employee;
use App\Models\Hospital;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\RadiologyOrder;

class DashboardController extends Controller
{
    public function index()
    {
        $hospital = TenantManager::getHospital();
        if (! $hospital) {
            $first = Hospital::first();
            if ($first) {
                TenantManager::setActiveHospital($first);
                $hospital = $first;
            }
        }

        $allHospitals = Hospital::all();
        $availableModules = ModuleRegistry::getAvailableModules();
        $activeModules = $hospital ? ModuleRegistry::getActiveModulesForHospital($hospital->id) : [];

        // Scoped metrics
        $patientsCount = Patient::count();
        $admissionsCount = Admission::where('status', 'admitted')->count();
        $totalBeds = Bed::count();
        $occupiedBeds = Bed::where('status', 'occupied')->count();
        $bedOccupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100) : 0;

        $appointmentsToday = Appointment::whereDate('appointment_date', now()->toDateString())->count();
        $employeesCount = Employee::where('status', 'active')->count();

        // Blood Bank metrics (if enabled)
        $bloodBags = [];
        if (isset($activeModules['blood_bank'])) {
            $bloodBags = BloodBag::where('status', 'available')->get();
        }

        // Radiology metrics (if enabled)
        $radiologyOrders = [];
        if (isset($activeModules['radiology'])) {
            $radiologyOrders = RadiologyOrder::with(['patient', 'modality', 'orderingDoctor'])
                ->latest()
                ->take(5)
                ->get();
        }

        // ER / Workforce On Duty
        $onDutyStaff = [];
        if (isset($activeModules['er_workforce'])) {
            $onDutyStaff = DutyRoster::with(['employee', 'shift', 'department'])
                ->whereDate('duty_date', now()->toDateString())
                ->get();
        }

        // Pharmacy low stock
        $lowStockMeds = [];
        if (isset($activeModules['pharmacy'])) {
            $lowStockMeds = Medicine::with('batches')->take(5)->get();
        }

        $recentAdmissions = Admission::with(['patient', 'ward', 'bed', 'attendingDoctor'])
            ->where('status', 'admitted')
            ->latest()
            ->take(5)
            ->get();

        return view('hospital.dashboard', compact(
            'hospital',
            'allHospitals',
            'availableModules',
            'activeModules',
            'patientsCount',
            'admissionsCount',
            'totalBeds',
            'occupiedBeds',
            'bedOccupancyRate',
            'appointmentsToday',
            'employeesCount',
            'bloodBags',
            'radiologyOrders',
            'onDutyStaff',
            'lowStockMeds',
            'recentAdmissions'
        ));
    }
}
