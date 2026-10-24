<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\SalaryStructure;
use App\Models\User;
use Illuminate\Http\Request;

class HrPayrollController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['department', 'designation', 'salaryStructure', 'user'])->latest()->get();
        $designations = Designation::with('department')->get();
        $departments = Department::where('is_active', true)->get();
        $payrolls = Payroll::with('employee')->latest('id')->get();
        $usersWithoutEmployee = User::whereDoesntHave('employee')->get();

        return view('modules.hr.index', compact(
            'employees',
            'designations',
            'departments',
            'payrolls',
            'usersWithoutEmployee'
        ));
    }

    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'gender' => 'required|in:male,female,other',
            'date_of_joining' => 'required|date',
            'qualification' => 'nullable|string',
            'medical_license_no' => 'nullable|string',
            'license_expiry_date' => 'nullable|date',
            'employment_type' => 'required|in:full_time,part_time,visiting_consultant,contract',
            'base_salary' => 'required|numeric|min:0',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $code = 'EMP-'.strtoupper(substr(Department::find($validated['department_id'])->code ?? 'GEN', 0, 3)).'-'.(Employee::count() + 101);

        $employee = Employee::create(array_merge($validated, ['employee_code' => $code]));

        // Create standard salary structure breakdown based on base_salary
        $base = (float) $validated['base_salary'];
        $basic = round($base * 0.55, 2);
        $hra = round($base * 0.25, 2);
        $med = round($base * 0.10, 2);
        $conv = round($base * 0.10, 2);
        $gross = $basic + $hra + $med + $conv;
        $pf = round($basic * 0.12, 2);
        $tax = round($gross * 0.08, 2);
        $net = $gross - ($pf + $tax);

        SalaryStructure::create([
            'employee_id' => $employee->id,
            'basic_salary' => $basic,
            'hra' => $hra,
            'medical_allowance' => $med,
            'conveyance_allowance' => $conv,
            'special_allowance' => 0.00,
            'pf_deduction' => $pf,
            'tax_deduction' => $tax,
            'gross_salary' => $gross,
            'net_salary' => $net,
            'effective_from' => $validated['date_of_joining'],
            'is_active' => true,
        ]);

        AuditLogger::log(
            moduleKey: 'hr_payroll',
            action: 'employee_record_created',
            entityType: 'Employee',
            entityId: $employee->id,
            details: ['code' => $code, 'name' => $employee->fullName, 'base_salary' => $base]
        );

        return redirect()->back()->with('success', "Employee {$employee->fullName} registered with code {$code} and automated salary structure.");
    }

    public function generatePayroll(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|string',
            'year' => 'required|integer',
        ]);

        $activeEmployees = Employee::with('salaryStructure')->where('status', 'active')->get();
        $generatedCount = 0;

        foreach ($activeEmployees as $emp) {
            $struct = $emp->salaryStructure;
            if (! $struct) {
                continue;
            }

            $exists = Payroll::where('employee_id', $emp->id)
                ->where('payroll_month', $validated['month'])
                ->where('payroll_year', $validated['year'])
                ->exists();

            if ($exists) {
                continue;
            }

            $payslipNo = 'PAY-'.$validated['year'].'-'.strtoupper(substr($validated['month'], 0, 3)).'-'.str_pad((string) $emp->id, 4, '0', STR_PAD_LEFT);
            $totalAllowances = $struct->hra + $struct->medical_allowance + $struct->conveyance_allowance + $struct->special_allowance;
            $totalDeductions = $struct->pf_deduction + $struct->tax_deduction;

            Payroll::create([
                'employee_id' => $emp->id,
                'payslip_number' => $payslipNo,
                'payroll_month' => $validated['month'],
                'payroll_year' => $validated['year'],
                'basic_salary' => $struct->basic_salary,
                'total_allowances' => $totalAllowances,
                'total_deductions' => $totalDeductions,
                'net_salary' => $struct->net_salary,
                'payment_status' => 'approved',
                'payment_date' => now()->toDateString(),
                'payment_method' => 'bank_transfer',
                'remarks' => "Payroll batch generated for {$validated['month']} {$validated['year']}.",
            ]);

            $generatedCount++;
        }

        AuditLogger::log(
            moduleKey: 'hr_payroll',
            action: 'payroll_batch_generated',
            entityType: 'Payroll',
            entityId: null,
            details: ['month' => $validated['month'], 'year' => $validated['year'], 'count' => $generatedCount]
        );

        return redirect()->back()->with('success', "Generated {$generatedCount} payslips for {$validated['month']} {$validated['year']}.");
    }
}
