<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Http\Request;

class OrganizationChartController extends Controller
{
    /**
     * Interactive Organizational Hierarchy Chart with Structural Relations for Super Admin
     */
    public function index(Request $request)
    {
        $selectedHospitalId = $request->query('hospital_id', 'all');
        $selectedBranchId = $request->query('branch_id', 'all');

        // Apex: Super Admin Platform HQ
        $superAdminUser = User::where('role', 'super_admin')->first();

        // Hospital Tenants Query
        $hospitalsQuery = Hospital::where('is_active', true)->with([
            'branches' => function ($bq) use ($selectedBranchId) {
                if ($selectedBranchId && $selectedBranchId !== 'all') {
                    $bq->where('id', (int) $selectedBranchId);
                }
                $bq->with([
                    'departments.employees.designation',
                    'departments.employees.user',
                ]);
            },
            'departments.employees.designation',
            'users',
        ]);

        if ($selectedHospitalId && $selectedHospitalId !== 'all') {
            $hospitalsQuery->where('id', (int) $selectedHospitalId);
        }

        $hospitals = $hospitalsQuery->get();
        $allHospitals = Hospital::where('is_active', true)->get();

        // Platform-Wide Aggregated Metrics
        $totalHospitals = Hospital::where('is_active', true)->count();
        $totalBranches = Branch::where('is_active', true)->count();
        $totalDepartments = Department::where('is_active', true)->count();
        $totalEmployees = Employee::where('status', 'active')->count();
        $totalDoctors = User::where('role', 'doctor')->count();
        $totalNurses = User::where('role', 'nurse')->count();

        return view('superadmin.organization_chart', compact(
            'superAdminUser',
            'hospitals',
            'allHospitals',
            'selectedHospitalId',
            'selectedBranchId',
            'totalHospitals',
            'totalBranches',
            'totalDepartments',
            'totalEmployees',
            'totalDoctors',
            'totalNurses'
        ));
    }

    /**
     * Return JSON structured tree data for external org chart renderers / API export
     */
    public function exportData(Request $request)
    {
        $hospitals = Hospital::where('is_active', true)
            ->with(['branches.departments.employees.designation'])
            ->get();

        $tree = [
            'name' => 'Medisystem Platform HQ / Super Admin',
            'title' => 'Main Corporate Apex & Governance',
            'type' => 'super_admin',
            'children' => $hospitals->map(function ($h) {
                return [
                    'name' => $h->name,
                    'code' => $h->code,
                    'title' => 'Hospital Tenant Group',
                    'type' => 'hospital',
                    'children' => $h->branches->map(function ($b) {
                        return [
                            'name' => $b->name,
                            'code' => $b->code,
                            'title' => $b->is_main_branch ? 'Main Flagship Branch' : 'Regional Branch',
                            'manager' => $b->manager_name,
                            'type' => 'branch',
                            'children' => $b->departments->map(function ($d) {
                                return [
                                    'name' => $d->name,
                                    'code' => $d->code,
                                    'title' => 'Department',
                                    'type' => 'department',
                                    'children' => $d->employees->map(function ($e) {
                                        return [
                                            'name' => $e->full_name,
                                            'code' => $e->employee_code,
                                            'title' => $e->designation->title ?? 'Staff Member',
                                            'type' => 'staff',
                                        ];
                                    }),
                                ];
                            }),
                        ];
                    }),
                ];
            }),
        ];

        return response()->json($tree);
    }
}
