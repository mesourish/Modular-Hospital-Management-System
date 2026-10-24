<?php

namespace Database\Seeders;

use App\Core\Audit\AuditLogger;
use App\Core\Constants\HospitalConstants;
use App\Core\ModuleRegistry\ModuleRegistry;
use App\Core\Tenancy\TenantManager;
use App\Models\Admission;
use App\Models\Ambulance;
use App\Models\AmbulanceTrip;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\BiomedicalEquipment;
use App\Models\BiomedicalTicket;
use App\Models\BirthRecord;
use App\Models\BloodBag;
use App\Models\BloodDonor;
use App\Models\Branch;
use App\Models\CodeBlueLog;
use App\Models\Consultation;
use App\Models\DeathRecord;
use App\Models\Department;
use App\Models\Designation;
use App\Models\DietaryOrder;
use App\Models\DispenseRecord;
use App\Models\DoctorChart;
use App\Models\DoctorTestimonial;
use App\Models\DutyRoster;
use App\Models\Employee;
use App\Models\HealthCheckupPackage;
use App\Models\Hospital;
use App\Models\HospitalConstant;
use App\Models\HospitalModule;
use App\Models\IcuMonitor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\OperationTheater;
use App\Models\OtMedicationLog;
use App\Models\OtSurgery;
use App\Models\PackageBooking;
use App\Models\Patient;
use App\Models\PatientAssignment;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\PharmacyStore;
use App\Models\Prescription;
use App\Models\RadiologyModality;
use App\Models\RadiologyOrder;
use App\Models\RadiologyReport;
use App\Models\SalaryStructure;
use App\Models\Shift;
use App\Models\StaffAttendance;
use App\Models\SubDepartment;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Ward;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ModularHospitalSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin (Platform Root & Governance)
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@medisystem.io'],
            [
                'name' => 'Shri Ratan Tata Memorial Platform Authority / Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'phone' => '+91 22 6665 8282',
                'is_active' => true,
            ]
        );

        // 2. Hospital A: Apollo Care Healthcare Network India (Multi-Branch Hierarchy)
        $apollo = Hospital::updateOrCreate(
            ['code' => 'ACH-01'],
            [
                'name' => 'Apollo Care Healthcare Network India',
                'slug' => 'apollo-care-india',
                'type' => 'multi_specialty',
                'license_number' => 'MH-MED-2026-99214',
                'phone' => '+91 22 2450 1100',
                'email' => 'info@apollocare.in',
                'address' => '74 Marine Health Boulevard, Nariman Point',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'country' => 'India',
                'currency' => 'INR',
                'subscription_plan' => 'enterprise_multi_branch',
                'is_active' => true,
            ]
        );

        // Enable all 8 modules for Apollo Care
        $allModules = array_keys(ModuleRegistry::getAvailableModules());
        foreach ($allModules as $modKey) {
            HospitalModule::updateOrCreate(
                ['hospital_id' => $apollo->id, 'module_key' => $modKey],
                ['is_enabled' => true, 'activated_at' => now()->subMonths(6)]
            );
        }

        // Setup Hospital B: Fortis Community Clinic & Diagnostics (New Delhi)
        $fortis = Hospital::updateOrCreate(
            ['code' => 'FCC-02'],
            [
                'name' => 'Fortis Community Clinic & Diagnostics',
                'slug' => 'fortis-clinic-delhi',
                'type' => 'clinic',
                'license_number' => 'DL-CLN-2026-44120',
                'phone' => '+91 11 4123 5599',
                'email' => 'contact@fortisclinic.in',
                'address' => '12 Connaught Outer Circle',
                'city' => 'New Delhi',
                'state' => 'Delhi',
                'country' => 'India',
                'currency' => 'INR',
                'subscription_plan' => 'outpatient_starter',
                'is_active' => true,
            ]
        );

        // Fortis has OPD, Admissions, Pharmacy, HR Payroll, Billing (Blood Bank & Radiology DISABLED)
        $fortisEnabled = ['opd', 'admissions', 'pharmacy', 'hr_payroll', 'billing'];
        foreach ($allModules as $modKey) {
            HospitalModule::updateOrCreate(
                ['hospital_id' => $fortis->id, 'module_key' => $modKey],
                [
                    'is_enabled' => in_array($modKey, $fortisEnabled),
                    'activated_at' => in_array($modKey, $fortisEnabled) ? now()->subMonths(2) : null,
                ]
            );
        }

        // 3. Seed Apollo Care Multi-Branch Hierarchy
        TenantManager::setOverrideHospitalId($apollo->id);

        // Branch 1: Main Flagship Branch (South Mumbai)
        $mainBranch = Branch::create([
            'hospital_id' => $apollo->id,
            'name' => 'Apollo Care Flagship Hospital - South Mumbai',
            'code' => 'APO-MUM-01',
            'slug' => 'south-mumbai-main-campus',
            'phone' => '+91 22 2450 1101',
            'email' => 'mumbai.hq@apollocare.in',
            'address' => '74 Marine Health Boulevard, Nariman Point',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400021',
            'manager_name' => 'Dr. Rajesh Sharma (Medical Director)',
            'is_main_branch' => true,
            'is_active' => true,
        ]);

        // Branch 2: Bandra Specialty & Cardiac Care
        $bandraBranch = Branch::create([
            'hospital_id' => $apollo->id,
            'name' => 'Apollo Cardiac & Specialty Clinic - Bandra West',
            'code' => 'APO-BAN-02',
            'slug' => 'bandra-specialty-clinic',
            'phone' => '+91 22 2640 4422',
            'email' => 'bandra@apollocare.in',
            'address' => '102 Turner Road, Near Waterfield Junction, Bandra West',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400050',
            'manager_name' => 'Dr. Ananya Sen (Branch Lead)',
            'is_main_branch' => false,
            'is_active' => true,
        ]);

        // Branch 3: Thane Suburban Outpatient Center
        $thaneBranch = Branch::create([
            'hospital_id' => $apollo->id,
            'name' => 'Apollo Outpatient & Diagnostic Center - Thane',
            'code' => 'APO-THA-03',
            'slug' => 'thane-outpatient-center',
            'phone' => '+91 22 2544 8833',
            'email' => 'thane@apollocare.in',
            'address' => 'Gokhale Road, Naupada, Thane West',
            'city' => 'Thane',
            'state' => 'Maharashtra',
            'pincode' => '400601',
            'manager_name' => 'Dr. Kevin D’Souza (Superintendent)',
            'is_main_branch' => false,
            'is_active' => true,
        ]);

        // Branch 4: Pune Diagnostic & Imaging Center
        $puneBranch = Branch::create([
            'hospital_id' => $apollo->id,
            'name' => 'Apollo Advanced Imaging & Diagnostics - Pune',
            'code' => 'APO-PUN-04',
            'slug' => 'pune-imaging-center',
            'phone' => '+91 20 2553 9911',
            'email' => 'pune@apollocare.in',
            'address' => 'Fergusson College Road, Shivajinagar',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411005',
            'manager_name' => 'Dr. Alok Verma (Lead Radiologist)',
            'is_main_branch' => false,
            'is_active' => true,
        ]);

        // Departments across branches
        $depCardio = Department::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Cardiology & Coronary Intensive Care',
            'code' => 'CARD-MUM',
            'type' => 'clinical',
            'description' => 'Advanced interventional cardiology, echocardiography and cath labs.',
        ]);

        $depEr = Department::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Emergency Medicine & Trauma Center',
            'code' => 'ER-MUM',
            'type' => 'clinical',
            'description' => '24/7 Level-1 Resuscitation and trauma units.',
        ]);

        $depBlood = Department::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Blood Transfusion & Apheresis Service',
            'code' => 'BLD-BNK',
            'type' => 'diagnostic',
            'description' => 'Serology, component separation and immunohematology.',
        ]);

        $depRad = Department::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Department of Diagnostic Radiology & PACS',
            'code' => 'RAD-IMG',
            'type' => 'diagnostic',
            'description' => '128-Slice CT, 3.0T MRI, Color Doppler, Digital X-Ray.',
        ]);

        $depPharm = Department::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Apollo Central Pharmacy & Formulary',
            'code' => 'PHARM-MUM',
            'type' => 'pharmacy',
            'description' => 'Round-the-clock IPD and OPD medicine store.',
        ]);

        $depHr = Department::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Human Resources & Clinical Credentialing',
            'code' => 'HR-FIN',
            'type' => 'administrative',
            'description' => 'Physician licensure, staff payroll, and duty shifts.',
        ]);

        $depBandraOpd = Department::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'name' => 'Bandra Outpatient Consultations & Diabetes Care',
            'code' => 'OPD-BAN',
            'type' => 'clinical',
            'description' => 'Comprehensive outpatient consultations & specialty clinics.',
        ]);

        // Sub-Departments across Specialized Divisions
        SubDepartment::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'department_id' => $depCardio->id,
            'name' => 'Interventional Cath Lab & Angioplasty Suite',
            'code' => 'CATH-LAB',
            'description' => 'Percutaneous coronary interventions, stent placement, and pacemaker implants.',
            'is_active' => true,
        ]);

        SubDepartment::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'department_id' => $depCardio->id,
            'name' => 'Coronary Care Unit (CCU Bed Wing)',
            'code' => 'CCU-UNIT',
            'description' => 'Dedicated CCU beds for post-CABG and acute coronary syndrome care.',
            'is_active' => true,
        ]);

        SubDepartment::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'department_id' => $depEr->id,
            'name' => 'Red Triage Acute Resuscitation Bay',
            'code' => 'TRIAGE-RED',
            'description' => 'Immediate Level-1 trauma and cardiac resuscitation suite.',
            'is_active' => true,
        ]);

        SubDepartment::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'department_id' => $depRad->id,
            'name' => '128-Slice Cardiac CT & 3.0T MRI Suite',
            'code' => 'RAD-MRI-CT',
            'description' => 'Diagnostic cross-sectional neuro and cardiovascular imaging suites.',
            'is_active' => true,
        ]);

        // Seed Master Realistic Healthcare Constants (Triage, Ambulance, Shifts, Payment Modes, Ward Types)
        foreach (HospitalConstants::SYSTEM_CONSTANTS as $catKey => $items) {
            foreach ($items as $item) {
                HospitalConstant::firstOrCreate(
                    [
                        'hospital_id' => $apollo->id,
                        'category' => $catKey,
                        'key' => $item['key'],
                    ],
                    [
                        'branch_id' => null,
                        'name' => $item['name'],
                        'code' => $item['code'],
                        'badge_color' => $item['badge_color'] ?? 'primary',
                        'sort_order' => $item['sort_order'] ?? 0,
                        'description' => $item['description'] ?? null,
                        'is_active' => true,
                    ]
                );
            }
        }

        // Users / Staff (Indian Healthcare Professionals)
        $adminUser = User::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Dr. Rajesh Sharma (Medical Director)',
            'email' => 'admin@apollocare.in',
            'password' => Hash::make('password'),
            'role' => 'hospital_admin',
            'phone' => '+91 98200 11223',
            'is_active' => true,
        ]);

        $bandraAdmin = User::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'name' => 'Dr. Vikramaditya Joshi (Bandra Branch Head)',
            'email' => 'bandra.head@apollocare.in',
            'password' => Hash::make('password'),
            'role' => 'branch_admin',
            'phone' => '+91 98200 66778',
            'is_active' => true,
        ]);

        $drCardio = User::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Dr. Ananya Sen, MD, DM (Cardiologist)',
            'email' => 'dr.ananya@apollocare.in',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'phone' => '+91 98200 44556',
            'is_active' => true,
        ]);

        $drEr = User::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Dr. Kevin D’Souza, MEM (ER Specialist)',
            'email' => 'dr.kevin@apollocare.in',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'phone' => '+91 98200 77889',
            'is_active' => true,
        ]);

        $drNeuro = User::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'name' => 'Dr. Sunita Rao, MD (Neurologist)',
            'email' => 'dr.sunita@apollocare.in',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'phone' => '+91 98200 88123',
            'is_active' => true,
        ]);

        $nurseMary = User::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Sr. Mary Fernandez (Head ICU Nurse)',
            'email' => 'mary.fernandez@apollocare.in',
            'password' => Hash::make('password'),
            'role' => 'nurse',
            'phone' => '+91 98200 33445',
            'is_active' => true,
        ]);

        $pharmacist = User::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Pooja Nair (Chief Pharmacist)',
            'email' => 'pooja.pharmacy@apollocare.in',
            'password' => Hash::make('password'),
            'role' => 'pharmacist',
            'phone' => '+91 98200 99001',
            'is_active' => true,
        ]);

        $radiologist = User::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Dr. Alok Verma, DMRD (Senior Radiologist)',
            'email' => 'alok.radiology@apollocare.in',
            'password' => Hash::make('password'),
            'role' => 'radiologist',
            'phone' => '+91 98200 66778',
            'is_active' => true,
        ]);

        $accountant = User::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Amit Saxena, CA (Head of Accounts)',
            'email' => 'amit.finance@apollocare.in',
            'password' => Hash::make('password'),
            'role' => 'accountant',
            'phone' => '+91 98200 55432',
            'is_active' => true,
        ]);

        // Job Designations
        $desigCardio = Designation::create([
            'hospital_id' => $apollo->id,
            'department_id' => $depCardio->id,
            'title' => 'Senior Consultant Cardiologist',
            'min_salary' => 180000.00,
            'max_salary' => 400000.00,
            'is_active' => true,
        ]);

        $desigEr = Designation::create([
            'hospital_id' => $apollo->id,
            'department_id' => $depEr->id,
            'title' => 'Emergency Trauma In-Charge',
            'min_salary' => 140000.00,
            'max_salary' => 250000.00,
            'is_active' => true,
        ]);

        $desigNurse = Designation::create([
            'hospital_id' => $apollo->id,
            'department_id' => $depEr->id,
            'title' => 'Critical Care Charge Nurse',
            'min_salary' => 50000.00,
            'max_salary' => 95000.00,
            'is_active' => true,
        ]);

        $desigNeuro = Designation::create([
            'hospital_id' => $apollo->id,
            'department_id' => $depBandraOpd->id,
            'title' => 'Consultant Neurologist',
            'min_salary' => 160000.00,
            'max_salary' => 320000.00,
            'is_active' => true,
        ]);

        // Employees with Indian council registrations
        $empCardio = Employee::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'user_id' => $drCardio->id,
            'employee_code' => 'APO-EMP-101',
            'first_name' => 'Ananya',
            'last_name' => 'Sen',
            'department_id' => $depCardio->id,
            'designation_id' => $desigCardio->id,
            'email' => $drCardio->email,
            'phone' => $drCardio->phone,
            'gender' => 'female',
            'date_of_joining' => '2021-04-01',
            'qualification' => 'MBBS (AIIMS New Delhi), MD (Medicine), DM (Cardiology)',
            'medical_license_no' => 'MCI-MH-449102',
            'license_expiry_date' => '2030-12-31',
            'employment_type' => 'full_time',
            'status' => 'active',
            'base_salary' => 250000.00,
            'bank_name' => 'HDFC Bank',
            'bank_account_no' => '5010049281920',
            'pan_number' => 'ABCPS1234F',
        ]);

        $empEr = Employee::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'user_id' => $drEr->id,
            'employee_code' => 'APO-EMP-102',
            'first_name' => 'Kevin',
            'last_name' => 'D’Souza',
            'department_id' => $depEr->id,
            'designation_id' => $desigEr->id,
            'email' => $drEr->email,
            'phone' => $drEr->phone,
            'gender' => 'male',
            'date_of_joining' => '2022-02-15',
            'qualification' => 'MBBS (KEM Mumbai), Masters in Emergency Medicine',
            'medical_license_no' => 'MCI-MH-552918',
            'license_expiry_date' => '2029-06-30',
            'employment_type' => 'full_time',
            'status' => 'active',
            'base_salary' => 180000.00,
            'bank_name' => 'State Bank of India',
            'bank_account_no' => '30992817263',
            'pan_number' => 'XYZPD5678G',
        ]);

        $empNeuro = Employee::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'user_id' => $drNeuro->id,
            'employee_code' => 'APO-EMP-103',
            'first_name' => 'Sunita',
            'last_name' => 'Rao',
            'department_id' => $depBandraOpd->id,
            'designation_id' => $desigNeuro->id,
            'email' => $drNeuro->email,
            'phone' => $drNeuro->phone,
            'gender' => 'female',
            'date_of_joining' => '2023-01-10',
            'qualification' => 'MBBS (Grant Medical), MD (Neurology)',
            'medical_license_no' => 'MMC-MH-771920',
            'license_expiry_date' => '2028-11-30',
            'employment_type' => 'full_time',
            'status' => 'active',
            'base_salary' => 210000.00,
            'bank_name' => 'ICICI Bank',
            'bank_account_no' => '002101928374',
            'pan_number' => 'JKLPR9012K',
        ]);

        $empNurse = Employee::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'user_id' => $nurseMary->id,
            'employee_code' => 'APO-EMP-204',
            'first_name' => 'Mary',
            'last_name' => 'Fernandez',
            'department_id' => $depEr->id,
            'designation_id' => $desigNurse->id,
            'email' => $nurseMary->email,
            'phone' => $nurseMary->phone,
            'gender' => 'female',
            'date_of_joining' => '2020-07-01',
            'qualification' => 'B.Sc Nursing, Fellowship in Critical Care Nursing',
            'medical_license_no' => 'MNC-NUR-88219',
            'license_expiry_date' => '2027-05-31',
            'employment_type' => 'full_time',
            'status' => 'active',
            'base_salary' => 80000.00,
            'bank_name' => 'Bank of Baroda',
            'bank_account_no' => '1928010002931',
            'pan_number' => 'DFGPN4321M',
        ]);

        // Salary Structures
        SalaryStructure::create([
            'hospital_id' => $apollo->id,
            'employee_id' => $empCardio->id,
            'basic_salary' => 137500.00,
            'hra' => 62500.00,
            'medical_allowance' => 25000.00,
            'conveyance_allowance' => 25000.00,
            'special_allowance' => 0.00,
            'pf_deduction' => 16500.00,
            'tax_deduction' => 33000.00,
            'gross_salary' => 250000.00,
            'net_salary' => 200500.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        // Payrolls
        Payroll::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'employee_id' => $empCardio->id,
            'payslip_number' => 'PAY-2026-10-MUM-01',
            'payroll_month' => 'October',
            'payroll_year' => 2026,
            'basic_salary' => 137500.00,
            'total_allowances' => 112500.00,
            'total_deductions' => 49500.00,
            'net_salary' => 200500.00,
            'payment_status' => 'paid',
            'payment_date' => '2026-10-22',
            'payment_method' => 'neft_rtgs',
            'remarks' => 'Monthly clinical remuneration disbursed via HDFC Corporate NEFT.',
        ]);

        // Shifts & ER Rosters
        $shiftMorning = Shift::create([
            'hospital_id' => $apollo->id,
            'name' => 'Morning OPD & Clinical Rounds (08:00 - 16:00)',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'is_active' => true,
        ]);

        $shiftEr = Shift::create([
            'hospital_id' => $apollo->id,
            'name' => 'ER Emergency Trauma Duty (16:00 - 00:00)',
            'start_time' => '16:00:00',
            'end_time' => '00:00:00',
            'is_active' => true,
        ]);

        DutyRoster::create([
            'hospital_id' => $apollo->id,
            'employee_id' => $empEr->id,
            'shift_id' => $shiftEr->id,
            'department_id' => $depEr->id,
            'duty_date' => now()->toDateString(),
            'role_in_shift' => 'er_incharge',
            'status' => 'on_duty',
            'duty_notes' => 'Code Blue leader and primary trauma triage.',
        ]);

        DutyRoster::create([
            'hospital_id' => $apollo->id,
            'employee_id' => $empNurse->id,
            'shift_id' => $shiftMorning->id,
            'department_id' => $depEr->id,
            'duty_date' => now()->toDateString(),
            'role_in_shift' => 'staff_nurse',
            'status' => 'on_duty',
            'duty_notes' => 'Trauma resuscitation bay cannula & cardiac monitor in-charge.',
        ]);

        // Wards & Beds
        $wardCcu = Ward::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Coronary Critical Care Unit (CCU)',
            'ward_type' => 'icu',
            'floor' => 'Floor 3 - Tower A',
            'capacity' => 12,
            'is_active' => true,
        ]);

        $wardPvt = Ward::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Executive Deluxe Cardiac Ward',
            'ward_type' => 'private',
            'floor' => 'Floor 5 - Tower B',
            'capacity' => 20,
            'is_active' => true,
        ]);

        $bed101 = Bed::create([
            'hospital_id' => $apollo->id,
            'ward_id' => $wardCcu->id,
            'bed_number' => 'CCU-BED-01',
            'bed_type' => 'icu_ventilator',
            'status' => 'occupied',
            'daily_charge' => 14500.00,
        ]);

        $bed102 = Bed::create([
            'hospital_id' => $apollo->id,
            'ward_id' => $wardCcu->id,
            'bed_number' => 'CCU-BED-02',
            'bed_type' => 'icu_ventilator',
            'status' => 'available',
            'daily_charge' => 14500.00,
        ]);

        // Patients with Indian demographics & ABHA ID
        $patient1 = Patient::create([
            'hospital_id' => $apollo->id,
            'mrn' => 'MRN-2026-00101',
            'abha_id' => '91-4820-1928-3019',
            'first_name' => 'Vikramaditya',
            'last_name' => 'Rao',
            'date_of_birth' => '1968-04-12',
            'gender' => 'male',
            'blood_group' => 'B+',
            'phone' => '+91 98112 34567',
            'email' => 'vikram.rao@example.com',
            'address' => 'Flat 12B, Sea Green Towers, Worli',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400018',
            'emergency_contact_name' => 'Radhika Rao (Wife)',
            'emergency_contact_phone' => '+91 98112 34568',
            'allergies' => 'Penicillin, NSAIDs (Aspirin)',
            'chronic_conditions' => 'Hypertension, Type 2 Diabetes Mellitus, Past PTCA (2021)',
        ]);

        $patient2 = Patient::create([
            'hospital_id' => $apollo->id,
            'mrn' => 'MRN-2026-00102',
            'abha_id' => '91-5512-8821-4402',
            'first_name' => 'Meera',
            'last_name' => 'Deshmukh',
            'date_of_birth' => '1985-09-23',
            'gender' => 'female',
            'blood_group' => 'O+',
            'phone' => '+91 97690 98765',
            'email' => 'meera.d@example.com',
            'address' => 'B-402, Sunshine Heights, Baner Road',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411045',
            'emergency_contact_name' => 'Sunil Deshmukh (Husband)',
            'emergency_contact_phone' => '+91 97690 98760',
            'allergies' => 'Sulfa Drugs',
            'chronic_conditions' => 'Bronchial Asthma, Migraine',
        ]);

        $patient3 = Patient::create([
            'hospital_id' => $apollo->id,
            'mrn' => 'MRN-2026-00103',
            'abha_id' => '91-7719-2230-9912',
            'first_name' => 'Aarav',
            'last_name' => 'Gupta',
            'date_of_birth' => '1998-11-05',
            'gender' => 'male',
            'blood_group' => 'A+',
            'phone' => '+91 98210 11224',
            'email' => 'aarav.g@example.com',
            'address' => '14 Linking Road, Bandra West',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400050',
            'emergency_contact_name' => 'Neha Gupta (Mother)',
            'emergency_contact_phone' => '+91 98210 11225',
            'allergies' => 'None known',
            'chronic_conditions' => 'None',
        ]);

        $patient4 = Patient::create([
            'hospital_id' => $apollo->id,
            'mrn' => 'MRN-2026-00104',
            'abha_id' => '91-6629-1144-8833',
            'first_name' => 'Kavita',
            'last_name' => 'Kulkarni',
            'date_of_birth' => '1975-06-18',
            'gender' => 'female',
            'blood_group' => 'B-',
            'phone' => '+91 98330 44551',
            'email' => 'kavita.k@example.com',
            'address' => 'Plot 88, Panchpakhadi, Thane West',
            'city' => 'Thane',
            'state' => 'Maharashtra',
            'pincode' => '400602',
            'emergency_contact_name' => 'Anand Kulkarni (Brother)',
            'emergency_contact_phone' => '+91 98330 44552',
            'allergies' => 'Ciprofloxacin',
            'chronic_conditions' => 'Hypothyroidism, Grade 2 Hypertension',
        ]);

        // Admission
        $admission1 = Admission::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'patient_id' => $patient1->id,
            'admission_number' => 'ADM-2026-MUM-901',
            'admission_date' => now()->subDays(2),
            'ward_id' => $wardCcu->id,
            'bed_id' => $bed101->id,
            'attending_doctor_id' => $drCardio->id,
            'admission_type' => 'emergency',
            'admission_reason' => 'Acute Coronary Syndrome, retrosternal chest tightness with dynamic T-wave inversions on ECG.',
            'initial_vitals' => 'BP: 165/102 mmHg | Pulse: 104 bpm | SpO2: 95% on room air',
            'status' => 'admitted',
        ]);

        PatientAssignment::create([
            'hospital_id' => $apollo->id,
            'patient_id' => $patient1->id,
            'admission_id' => $admission1->id,
            'employee_id' => $empCardio->id,
            'assignment_type' => 'attending_doctor',
            'assigned_at' => now()->subDays(2),
            'is_active' => true,
            'clinical_handover_instructions' => 'Continuous telemetry, check Troponin I & CK-MB 6-hourly. Maintain IV Heparin infusion.',
        ]);

        // Centralized Calendar Doctor Schedule & Appointments
        // Today appointments
        $appt1 = Appointment::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'patient_id' => $patient2->id,
            'doctor_id' => $drCardio->id,
            'department_id' => $depCardio->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00:00',
            'token_number' => 1,
            'status' => 'completed',
            'symptoms' => 'Exertional dyspnea, palpitations while walking, mild dizziness.',
        ]);

        $cons1 = Consultation::create([
            'hospital_id' => $apollo->id,
            'appointment_id' => $appt1->id,
            'patient_id' => $patient2->id,
            'doctor_id' => $drCardio->id,
            'encounter_date' => now()->subHours(3),
            'chief_complaints' => 'Patient has shortness of breath on climbing stairs, no chest pain.',
            'examination_findings' => 'Bilateral chest clear, normal vesicular breath sounds. S1 S2 heard normally.',
            'diagnosis' => 'Essential Hypertension Stage 1 with Sinus Tachycardia',
            'bp' => '144/92',
            'pulse' => '86',
            'temperature' => '98.4 F',
            'spo2' => '98%',
            'weight_kg' => 64.0,
            'clinical_notes' => 'Prescribed Telmisartan. Advised dietary sodium restriction and daily 30-min brisk walk. Review in 2 weeks.',
            'follow_up_date' => now()->addDays(14)->toDateString(),
            'status' => 'finalized',
        ]);

        // Prescriptions for Patient 2
        Prescription::create([
            'hospital_id' => $apollo->id,
            'consultation_id' => $cons1->id,
            'patient_id' => $patient2->id,
            'doctor_id' => $drCardio->id,
            'medicine_name' => 'Telma 40 (Telmisartan 40mg)',
            'dosage' => '40 mg',
            'frequency' => '1-0-0 (Once daily morning after food)',
            'duration' => '30 Days',
            'instructions' => 'Take with water after breakfast. Check morning BP weekly.',
            'is_dispensed' => true,
        ]);

        Prescription::create([
            'hospital_id' => $apollo->id,
            'consultation_id' => $cons1->id,
            'patient_id' => $patient2->id,
            'doctor_id' => $drCardio->id,
            'medicine_name' => 'Pan 40 (Pantoprazole 40mg)',
            'dosage' => '40 mg',
            'frequency' => '1-0-0 (Empty stomach 30 mins before breakfast)',
            'duration' => '15 Days',
            'instructions' => 'Early morning with warm water.',
            'is_dispensed' => true,
        ]);

        // Appointment 2 (Today 11:30 AM with Dr. Ananya)
        $appt2 = Appointment::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'patient_id' => $patient3->id,
            'doctor_id' => $drCardio->id,
            'department_id' => $depCardio->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '11:30:00',
            'token_number' => 2,
            'status' => 'completed',
            'symptoms' => 'High fever (102°F), body aches, severe sore throat.',
        ]);

        $cons2 = Consultation::create([
            'hospital_id' => $apollo->id,
            'appointment_id' => $appt2->id,
            'patient_id' => $patient3->id,
            'doctor_id' => $drCardio->id,
            'encounter_date' => now()->subHours(1),
            'chief_complaints' => 'Acute onset of high grade fever with chills and throat congestion.',
            'examination_findings' => 'Pharyngeal erythema with tonsillar exudate. No cervical lymphadenopathy.',
            'diagnosis' => 'Acute Streptococcal Pharyngotonsillitis with High Grade Pyrexia',
            'bp' => '118/76',
            'pulse' => '96',
            'temperature' => '102.2 F',
            'spo2' => '99%',
            'weight_kg' => 70.0,
            'clinical_notes' => 'Start antibiotic course immediately. Maintain adequate hydration and rest.',
            'status' => 'finalized',
        ]);

        Prescription::create([
            'hospital_id' => $apollo->id,
            'consultation_id' => $cons2->id,
            'patient_id' => $patient3->id,
            'doctor_id' => $drCardio->id,
            'medicine_name' => 'Dolo 650 (Paracetamol 650mg)',
            'dosage' => '650 mg',
            'frequency' => '1-1-1 (Thrice daily after food or SOS fever)',
            'duration' => '5 Days',
            'instructions' => 'Maintain 6 hours interval between doses.',
            'is_dispensed' => true,
        ]);

        Prescription::create([
            'hospital_id' => $apollo->id,
            'consultation_id' => $cons2->id,
            'patient_id' => $patient3->id,
            'doctor_id' => $drCardio->id,
            'medicine_name' => 'Augmentin 625 (Amoxicillin + Clavulanic Acid)',
            'dosage' => '625 mg',
            'frequency' => '1-0-1 (Twice daily after food)',
            'duration' => '5 Days',
            'instructions' => 'Complete full 5-day antibiotic course without skipping.',
            'is_dispensed' => true,
        ]);

        // Appointment 3 (Today 02:00 PM with Dr. Sunita Rao at Bandra Branch)
        $appt3 = Appointment::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'patient_id' => $patient4->id,
            'doctor_id' => $drNeuro->id,
            'department_id' => $depBandraOpd->id,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '14:00:00',
            'token_number' => 3,
            'status' => 'scheduled',
            'symptoms' => 'Unilateral throbbing headache with photophobia and nausea.',
        ]);

        // Indian Pharmaceuticals Formulary
        $dolo = Medicine::create([
            'hospital_id' => $apollo->id,
            'code' => 'MED-DOL-650',
            'brand_name' => 'Dolo 650',
            'generic_name' => 'Paracetamol Tablets IP',
            'category' => 'Antipyretic / Analgesic',
            'dosage_form' => 'tablet',
            'strength' => '650mg',
            'manufacturer' => 'Micro Labs Limited',
            'min_reorder_level' => 200,
            'is_prescription_required' => false,
            'is_active' => true,
        ]);

        $aug = Medicine::create([
            'hospital_id' => $apollo->id,
            'code' => 'MED-AUG-625',
            'brand_name' => 'Augmentin 625 Duo',
            'generic_name' => 'Amoxicillin 500mg + Potassium Clavulanate 125mg IP',
            'category' => 'Broad Spectrum Antibiotic',
            'dosage_form' => 'tablet',
            'strength' => '625mg',
            'manufacturer' => 'GlaxoSmithKline Pharmaceuticals',
            'min_reorder_level' => 100,
            'is_prescription_required' => true,
            'is_active' => true,
        ]);

        $pan = Medicine::create([
            'hospital_id' => $apollo->id,
            'code' => 'MED-PAN-40',
            'brand_name' => 'Pan 40',
            'generic_name' => 'Pantoprazole Gastro-Resistant Tablets IP',
            'category' => 'Proton Pump Inhibitor (Antacid)',
            'dosage_form' => 'tablet',
            'strength' => '40mg',
            'manufacturer' => 'Alkem Laboratories Ltd',
            'min_reorder_level' => 150,
            'is_prescription_required' => false,
            'is_active' => true,
        ]);

        $telma = Medicine::create([
            'hospital_id' => $apollo->id,
            'code' => 'MED-TEL-40',
            'brand_name' => 'Telma 40',
            'generic_name' => 'Telmisartan Tablets IP',
            'category' => 'Antihypertensive',
            'dosage_form' => 'tablet',
            'strength' => '40mg',
            'manufacturer' => 'Glenmark Pharmaceuticals',
            'min_reorder_level' => 80,
            'is_prescription_required' => true,
            'is_active' => true,
        ]);

        $glycomet = Medicine::create([
            'hospital_id' => $apollo->id,
            'code' => 'MED-GLY-GP2',
            'brand_name' => 'Glycomet GP2',
            'generic_name' => 'Metformin 500mg + Glimepiride 2mg IP',
            'category' => 'Oral Hypoglycemic / Antidiabetic',
            'dosage_form' => 'tablet',
            'strength' => '500mg/2mg',
            'manufacturer' => 'USV Private Limited',
            'min_reorder_level' => 100,
            'is_prescription_required' => true,
            'is_active' => true,
        ]);

        // Medicine Batches
        $batchDolo = MedicineBatch::create([
            'hospital_id' => $apollo->id,
            'medicine_id' => $dolo->id,
            'batch_number' => 'DL-2026-M81',
            'expiry_date' => '2028-09-30',
            'purchase_price' => 22.00,
            'selling_price' => 34.50,
            'quantity_in_stock' => 1200,
            'rack_location' => 'Rack A1 (General Oral Solids)',
            'shelf_number' => 'Shelf 2',
            'box_number' => 'Box 04',
        ]);

        $batchAug = MedicineBatch::create([
            'hospital_id' => $apollo->id,
            'medicine_id' => $aug->id,
            'batch_number' => 'AUG-992-04',
            'expiry_date' => '2027-12-31',
            'purchase_price' => 140.00,
            'selling_price' => 205.00,
            'quantity_in_stock' => 450,
            'rack_location' => 'Rack B2 (Antibiotics Vault)',
            'shelf_number' => 'Shelf 1',
            'box_number' => 'Box 12',
        ]);

        $batchTelma = MedicineBatch::create([
            'hospital_id' => $apollo->id,
            'medicine_id' => $telma->id,
            'batch_number' => 'TLM-2026-X11',
            'expiry_date' => '2028-06-30',
            'purchase_price' => 115.00,
            'selling_price' => 175.00,
            'quantity_in_stock' => 600,
            'rack_location' => 'Rack C3 (Cardiovascular Store)',
            'shelf_number' => 'Shelf 3',
            'box_number' => 'Box 08',
        ]);

        // Dispensing
        DispenseRecord::create([
            'hospital_id' => $apollo->id,
            'patient_id' => $patient2->id,
            'medicine_batch_id' => $batchTelma->id,
            'dispensed_by_id' => $pharmacist->id,
            'quantity' => 30,
            'unit_price' => 5.83,
            'total_price' => 175.00,
            'dispensed_at' => now()->subHours(2),
            'notes' => 'Dispensed 1 strip of Telma 40 as per Dr. Ananya Sen prescription.',
        ]);

        // Blood Bank Data
        $donor1 = BloodDonor::create([
            'hospital_id' => $apollo->id,
            'donor_code' => 'BD-MUM-2026-012',
            'full_name' => 'Rohan Kapoor',
            'date_of_birth' => '1992-07-14',
            'gender' => 'male',
            'blood_group' => 'B+',
            'rh_factor' => 'positive',
            'phone' => '+91 99201 23456',
            'email' => 'rohan.k@example.com',
            'last_donation_date' => now()->subDays(10),
            'screening_status' => 'eligible',
            'screening_notes' => 'Hb 14.8 g/dL, Vitals normal. Cleared voluntary donor.',
        ]);

        $bag1 = BloodBag::create([
            'hospital_id' => $apollo->id,
            'donor_id' => $donor1->id,
            'bag_number' => 'BLD-BAG-2026-B081',
            'blood_group' => 'B+',
            'rh_factor' => 'positive',
            'component_type' => 'prbc',
            'volume_ml' => 350,
            'collection_date' => now()->subDays(10),
            'expiry_date' => now()->addDays(32),
            'storage_location' => 'Main Blood Bank Refrigerator #2 - Shelf A',
            'status' => 'available',
            'serology_tests' => [
                'HIV_1_2' => 'Non-Reactive',
                'HBsAg' => 'Non-Reactive',
                'HCV' => 'Non-Reactive',
                'VDRL_Syphilis' => 'Non-Reactive',
                'Malaria_Ag' => 'Negative',
            ],
        ]);

        // Radiology Machines & Imaging Modalities
        $radCt = RadiologyModality::create([
            'hospital_id' => $apollo->id,
            'name' => 'Siemens Somatom 128-Slice Cardiac CT Scanner',
            'modality_code' => 'CT',
            'room_number' => 'Room 102 - Ground Floor Imaging Suite',
            'is_active' => true,
        ]);

        $radMri = RadiologyModality::create([
            'hospital_id' => $apollo->id,
            'name' => 'GE Signa 3.0 Tesla High-Field MRI',
            'modality_code' => 'MRI',
            'room_number' => 'Shielded Suite 104',
            'is_active' => true,
        ]);

        $radXray = RadiologyModality::create([
            'hospital_id' => $apollo->id,
            'name' => 'Shimadzu High-Frequency Digital Radiography (X-Ray)',
            'modality_code' => 'XRAY',
            'room_number' => 'Suite 101 - Digital Radiography Suite',
            'is_active' => true,
        ]);

        $radUsg = RadiologyModality::create([
            'hospital_id' => $apollo->id,
            'name' => 'GE Voluson 4D Color Doppler Ultrasound',
            'modality_code' => 'USG',
            'room_number' => 'Suite 103 - Ultrasound Suite',
            'is_active' => true,
        ]);

        // Walk-in Diagnostic Patients
        $walkinPatient1 = Patient::create([
            'hospital_id' => $apollo->id,
            'mrn' => 'MRN-20261024-W001',
            'first_name' => 'Rahul',
            'last_name' => 'Sharma',
            'gender' => 'male',
            'phone' => '9820011223',
            'date_of_birth' => '1992-06-15',
            'city' => 'Mumbai',
        ]);

        $walkinPatient2 = Patient::create([
            'hospital_id' => $apollo->id,
            'mrn' => 'MRN-20261024-W002',
            'first_name' => 'Sunita',
            'last_name' => 'Deshmukh',
            'gender' => 'female',
            'phone' => '9820033445',
            'date_of_birth' => '1985-09-20',
            'city' => 'Mumbai',
        ]);

        // Radiology Orders: Inpatient & Walk-in Customers
        $radOrder1 = RadiologyOrder::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'patient_id' => $patient1->id,
            'modality_id' => $radMri->id,
            'order_number' => 'RAD-2026-00001',
            'procedure_name' => 'High-Resolution Brain MRI with Contrast',
            'clinical_indication' => 'Suspected ischemia / posterior circulation evaluation',
            'ordering_doctor_id' => $drCardio->id,
            'priority' => 'urgent',
            'status' => 'reported',
            'is_walkin' => false,
            'scheduled_at' => now(),
            'slot_time' => '10:00 AM',
            'performed_at' => now()->subHours(2),
        ]);

        RadiologyReport::create([
            'hospital_id' => $apollo->id,
            'radiology_order_id' => $radOrder1->id,
            'radiologist_id' => $radiologist->id,
            'findings' => 'Brain parenchyma shows normal signal intensity. Ventricles and sulci are within normal limits. No evidence of acute intracranial hemorrhage or focal mass effect.',
            'impression' => 'Normal study. No acute infarction or demyelinating pathology observed.',
            'recommendations' => 'Follow up with neurologist if episodic cephalea persists.',
            'dicom_study_uid' => '1.2.840.113619.2.55.'.time(),
            'status' => 'verified',
            'verified_at' => now()->subHour(),
        ]);

        // Walk-in Order 1
        RadiologyOrder::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'patient_id' => $walkinPatient1->id,
            'modality_id' => $radUsg->id,
            'order_number' => 'RAD-WALK-20261024-0001',
            'token_number' => 'RAD-W01',
            'procedure_name' => 'Color Doppler 4D Abdominal & Pelvic USG',
            'clinical_indication' => 'Acute right lower quadrant abdominal pain',
            'ordering_doctor_id' => $drCardio->id,
            'referring_doctor_name' => 'Dr. P. K. Mehta (City Care Clinic)',
            'patient_contact' => '9820011223',
            'priority' => 'urgent',
            'status' => 'scheduled',
            'is_walkin' => true,
            'scheduled_at' => now(),
            'slot_time' => '09:00 AM',
        ]);

        // Walk-in Order 2
        RadiologyOrder::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'patient_id' => $walkinPatient2->id,
            'modality_id' => $radXray->id,
            'order_number' => 'RAD-WALK-20261024-0002',
            'token_number' => 'RAD-W02',
            'procedure_name' => 'Digital Chest X-Ray PA View',
            'clinical_indication' => 'Pre-employment health clearance checkup',
            'ordering_doctor_id' => $drCardio->id,
            'referring_doctor_name' => 'Dr. Rajesh Gupta (Metro Polyclinic)',
            'patient_contact' => '9820033445',
            'priority' => 'routine',
            'status' => 'scheduled',
            'is_walkin' => true,
            'scheduled_at' => now(),
            'slot_time' => '11:00 AM',
        ]);

        // Hierarchical Multi-Branch Financial Records & Invoices
        // Invoice 1: South Mumbai Main Flagship Branch (IPD Inpatient Bill)
        $invMain = Invoice::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'patient_id' => $patient1->id,
            'admission_id' => $admission1->id,
            'invoice_number' => 'INV-MUM-2026-0081',
            'invoice_date' => now()->toDateString(),
            'total_amount' => 48500.00,
            'discount_amount' => 2000.00,
            'tax_amount' => 2325.00, // 5% GST on consumables/room
            'cgst_amount' => 1162.50,
            'sgst_amount' => 1162.50,
            'gstin_number' => '27AAAAA0000A1Z5',
            'net_payable' => 48825.00,
            'paid_amount' => 30000.00,
            'balance_due' => 18825.00,
            'payment_status' => 'partially_paid',
            'notes' => 'IPD CCU Coronary Admission Bill. Advance collected via UPI & Card.',
        ]);

        InvoiceItem::create([
            'hospital_id' => $apollo->id,
            'invoice_id' => $invMain->id,
            'service_type' => 'bed_charge',
            'item_name' => 'CCU Ventilator Bed Charges (2 Days @ ₹14,500)',
            'quantity' => 2,
            'unit_price' => 14500.00,
            'total_price' => 29000.00,
        ]);

        InvoiceItem::create([
            'hospital_id' => $apollo->id,
            'invoice_id' => $invMain->id,
            'service_type' => 'radiology',
            'item_name' => 'CT Coronary Angiogram with IV Non-Ionic Contrast',
            'quantity' => 1,
            'unit_price' => 14000.00,
            'total_price' => 14000.00,
        ]);

        InvoiceItem::create([
            'hospital_id' => $apollo->id,
            'invoice_id' => $invMain->id,
            'service_type' => 'pharmacy',
            'item_name' => 'Emergency Medications, Infusions & Consumables',
            'quantity' => 1,
            'unit_price' => 5500.00,
            'total_price' => 5500.00,
        ]);

        Payment::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'invoice_id' => $invMain->id,
            'receipt_number' => 'RCT-MUM-2026-00411',
            'payment_date' => now()->subDays(2),
            'amount' => 30000.00,
            'payment_method' => 'upi',
            'transaction_ref' => 'UPI/GPay/62910492810@okaxis',
            'collected_by_id' => $adminUser->id,
        ]);

        // Invoice 2: Bandra Specialty Branch (OPD & Pharmacy Bill)
        $invBandra = Invoice::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'patient_id' => $patient2->id,
            'admission_id' => null,
            'invoice_number' => 'INV-BAN-2026-0019',
            'invoice_date' => now()->toDateString(),
            'total_amount' => 1800.00,
            'discount_amount' => 0.00,
            'tax_amount' => 90.00,
            'cgst_amount' => 45.00,
            'sgst_amount' => 45.00,
            'gstin_number' => '27AAAAA0000A1Z5',
            'net_payable' => 1890.00,
            'paid_amount' => 1890.00,
            'balance_due' => 0.00,
            'payment_status' => 'paid',
            'notes' => 'Bandra Branch OPD Cardiology Consultation & Pharmacy Settlement.',
        ]);

        InvoiceItem::create([
            'hospital_id' => $apollo->id,
            'invoice_id' => $invBandra->id,
            'service_type' => 'consultation',
            'item_name' => 'Senior Consultant Cardiologist Consultation Fee',
            'quantity' => 1,
            'unit_price' => 1500.00,
            'total_price' => 1500.00,
        ]);

        InvoiceItem::create([
            'hospital_id' => $apollo->id,
            'invoice_id' => $invBandra->id,
            'service_type' => 'pharmacy',
            'item_name' => 'Telma 40 & Pan 40 (1 Month Pack)',
            'quantity' => 1,
            'unit_price' => 300.00,
            'total_price' => 300.00,
        ]);

        Payment::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'invoice_id' => $invBandra->id,
            'receipt_number' => 'RCT-BAN-2026-00105',
            'payment_date' => now()->subHours(2),
            'amount' => 1890.00,
            'payment_method' => 'card',
            'transaction_ref' => 'POS/HDFC/AUTH-992104',
            'collected_by_id' => $adminUser->id,
        ]);

        // Invoice 3: Thane Suburban Branch (Fever & Pharmacy Bill)
        $invThane = Invoice::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $thaneBranch->id,
            'patient_id' => $patient3->id,
            'admission_id' => null,
            'invoice_number' => 'INV-THA-2026-0012',
            'invoice_date' => now()->toDateString(),
            'total_amount' => 1250.00,
            'discount_amount' => 0.00,
            'tax_amount' => 62.50,
            'cgst_amount' => 31.25,
            'sgst_amount' => 31.25,
            'gstin_number' => '27AAAAA0000A1Z5',
            'net_payable' => 1312.50,
            'paid_amount' => 1312.50,
            'balance_due' => 0.00,
            'payment_status' => 'paid',
            'notes' => 'Thane Outpatient Consult & Antibiotic Dispensing.',
        ]);

        InvoiceItem::create([
            'hospital_id' => $apollo->id,
            'invoice_id' => $invThane->id,
            'service_type' => 'consultation',
            'item_name' => 'Outpatient Physician Clinical Triage',
            'quantity' => 1,
            'unit_price' => 800.00,
            'total_price' => 800.00,
        ]);

        InvoiceItem::create([
            'hospital_id' => $apollo->id,
            'invoice_id' => $invThane->id,
            'service_type' => 'pharmacy',
            'item_name' => 'Dolo 650 & Augmentin 625 Duo Pack',
            'quantity' => 1,
            'unit_price' => 450.00,
            'total_price' => 450.00,
        ]);

        Payment::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $thaneBranch->id,
            'invoice_id' => $invThane->id,
            'receipt_number' => 'RCT-THA-2026-00092',
            'payment_date' => now()->subHour(),
            'amount' => 1312.50,
            'payment_method' => 'upi',
            'transaction_ref' => 'UPI/PhonePe/TXN-8821948190',
            'collected_by_id' => $adminUser->id,
        ]);

        // 12. Seed Hospital-Wise & Branch-Wise Doctor Master Charts
        $chartSharma = DoctorChart::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'user_id' => $adminUser->id,
            'department_id' => $depCardio->id,
            'doctor_name' => 'Dr. Rajesh Sharma, MS, MCh',
            'degrees' => 'MBBS, MS (General Surgery), MCh (Surgical Oncology, AIIMS Delhi)',
            'specialty' => 'Medical Director & Senior Surgical Oncologist',
            'opd_days' => 'Mon, Tue, Thu, Fri',
            'start_time' => '10:00:00',
            'end_time' => '14:00:00',
            'time_slot_label' => 'Morning Executive Slot (10:00 AM - 02:00 PM)',
            'room_number' => 'Suite 101 - Executive OPD, 1st Floor',
            'consultation_fee' => 1200.00,
            'follow_up_fee' => 600.00,
            'emergency_fee' => 2000.00,
            'max_patients_per_session' => 20,
            'avg_consultation_time_mins' => 20,
            'status' => 'available',
            'instructions' => 'Strictly by prior appointment. Pre-operative workup required.',
            'is_active' => true,
        ]);

        $chartSen = DoctorChart::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'user_id' => $drCardio->id,
            'department_id' => $depCardio->id,
            'doctor_name' => 'Dr. Ananya Sen, MD, DM',
            'degrees' => 'MBBS (Gold Medalist), MD (Medicine), DM (Cardiology, AIIMS Delhi)',
            'specialty' => 'Interventional Cardiology & Coronary Angioplasty Specialist',
            'opd_days' => 'Mon, Wed, Fri',
            'start_time' => '09:00:00',
            'end_time' => '13:00:00',
            'time_slot_label' => 'Morning OPD (09:00 AM - 01:00 PM)',
            'room_number' => 'OPD Cabin 104 - Cardiac Sciences Block',
            'consultation_fee' => 1000.00,
            'follow_up_fee' => 500.00,
            'emergency_fee' => 1800.00,
            'max_patients_per_session' => 25,
            'avg_consultation_time_mins' => 15,
            'status' => 'available',
            'instructions' => 'Bring previous ECG, 2D-Echo and blood sugar fasting reports.',
            'is_active' => true,
        ]);

        $chartRao = DoctorChart::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'user_id' => $drNeuro->id,
            'department_id' => $depBandraOpd->id,
            'doctor_name' => 'Dr. Sunita Rao, MD, DNB',
            'degrees' => 'MBBS, MD (Medicine), DNB (Neurology, NIMHANS Bangalore)',
            'specialty' => 'Consultant Neurologist & Comprehensive Stroke Care Lead',
            'opd_days' => 'Tue, Thu, Sat',
            'start_time' => '11:00:00',
            'end_time' => '15:30:00',
            'time_slot_label' => 'Day Slot (11:00 AM - 03:30 PM)',
            'room_number' => 'Room 202 - Neuro Suite, 2nd Floor (Bandra Clinic)',
            'consultation_fee' => 900.00,
            'follow_up_fee' => 450.00,
            'emergency_fee' => 1500.00,
            'max_patients_per_session' => 20,
            'avg_consultation_time_mins' => 20,
            'status' => 'available',
            'instructions' => 'Senior citizens priority tokens available from 10:30 AM.',
            'is_active' => true,
        ]);

        $chartDsouza = DoctorChart::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'user_id' => $drEr->id,
            'department_id' => $depEr->id,
            'doctor_name' => 'Dr. Kevin D’Souza, MEM',
            'degrees' => 'MBBS, MEM (Emergency Medicine, GWU USA), FACEE',
            'specialty' => 'Lead Emergency Physician & Acute Resuscitation Specialist',
            'opd_days' => 'Daily (Mon - Sun Rota)',
            'start_time' => '08:00:00',
            'end_time' => '14:00:00',
            'time_slot_label' => 'Morning ER Duty Slot',
            'room_number' => 'Trauma Resuscitation Bay 1 (Main ER Ground Floor)',
            'consultation_fee' => 800.00,
            'follow_up_fee' => 400.00,
            'emergency_fee' => 1200.00,
            'max_patients_per_session' => 35,
            'avg_consultation_time_mins' => 10,
            'status' => 'available',
            'instructions' => 'Walk-in emergency triage. No prior appointment required.',
            'is_active' => true,
        ]);

        $chartVerma = DoctorChart::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $puneBranch->id,
            'user_id' => $radiologist->id,
            'department_id' => $depRad->id,
            'doctor_name' => 'Dr. Alok Verma, DMRD',
            'degrees' => 'MBBS, DMRD (Radio-Diagnosis, KEM Mumbai), DNB',
            'specialty' => 'Chief Radiologist & Advanced MRI/CT Imaging Lead',
            'opd_days' => 'Mon to Sat',
            'start_time' => '09:30:00',
            'end_time' => '17:30:00',
            'time_slot_label' => 'Full Day Diagnostic Review Slot',
            'room_number' => 'PACS Console Cabin 3 (Pune Imaging Annex)',
            'consultation_fee' => 750.00,
            'follow_up_fee' => 350.00,
            'emergency_fee' => 1200.00,
            'max_patients_per_session' => 30,
            'avg_consultation_time_mins' => 15,
            'status' => 'available',
            'instructions' => 'CT/MRI review appointments available online.',
            'is_active' => true,
        ]);

        $chartNair = DoctorChart::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $thaneBranch->id,
            'department_id' => null,
            'doctor_name' => 'Dr. Priya Nair, MS (Ortho)',
            'degrees' => 'MBBS, MS (Orthopedics, Grant Medical College), Joint Replacement Fellow',
            'specialty' => 'Consultant Orthopedic & Arthroscopy Surgeon',
            'opd_days' => 'Mon, Wed, Sat',
            'start_time' => '16:00:00',
            'end_time' => '20:30:00',
            'time_slot_label' => 'Evening Specialist Clinic (04:00 PM - 08:30 PM)',
            'room_number' => 'Cabin 108 - Ortho Care Suite, Thane Center',
            'consultation_fee' => 850.00,
            'follow_up_fee' => 400.00,
            'emergency_fee' => 1400.00,
            'max_patients_per_session' => 25,
            'avg_consultation_time_mins' => 15,
            'status' => 'available',
            'instructions' => 'X-Ray facility on site. Knee & joint replacement consultations.',
            'is_active' => true,
        ]);

        // Seed Patient Testimonials for Doctors
        $testimonials = [
            [
                'hospital_id' => $apollo->id,
                'branch_id' => $mainBranch->id,
                'doctor_chart_id' => $chartSharma->id,
                'patient_name' => 'Anand Singhania',
                'patient_location' => 'Malabar Hill, Mumbai',
                'rating' => 5,
                'headline' => 'World-Class Surgical Precision & Compassionate Care',
                'comment' => 'Dr. Rajesh Sharma performed complex surgical oncology on my mother. His clarity, warmth, and calm reassurance gave our entire family strength. The surgical team and post-op CCU at Apollo Mumbai Central are truly unmatched in India.',
                'treatment_received' => 'Oncological Resection & Reconstruction',
                'is_verified' => true,
                'is_published' => true,
            ],
            [
                'hospital_id' => $apollo->id,
                'branch_id' => $mainBranch->id,
                'doctor_chart_id' => $chartSharma->id,
                'patient_name' => 'Meera Kulkarni',
                'patient_location' => 'Dadar, Mumbai',
                'rating' => 5,
                'headline' => 'Dignified & Honest Medical Guidance',
                'comment' => 'One of the most dignified and experienced doctors in Mumbai. Dr. Sharma took time to review every biopsy and CT scan, explaining options with absolute transparency. Today my father is 2 years cancer-free.',
                'treatment_received' => 'Surgical Consultation & Follow-up',
                'is_verified' => true,
                'is_published' => true,
            ],
            [
                'hospital_id' => $apollo->id,
                'branch_id' => $mainBranch->id,
                'doctor_chart_id' => $chartSen->id,
                'patient_name' => 'Vikramaditya Deshmukh',
                'patient_location' => 'Worli Sea Face, Mumbai',
                'rating' => 5,
                'headline' => 'Saved My Life During Golden Hour Cardiac Emergency',
                'comment' => 'I was rushed to Apollo ER with acute chest pain. Dr. Ananya Sen diagnosed anterior wall STEMI and performed primary coronary angioplasty within 35 minutes door-to-balloon time! Her swift intervention gave me a second chance at life.',
                'treatment_received' => 'Primary Coronary Angioplasty (PTCA)',
                'is_verified' => true,
                'is_published' => true,
            ],
            [
                'hospital_id' => $apollo->id,
                'branch_id' => $mainBranch->id,
                'doctor_chart_id' => $chartSen->id,
                'patient_name' => 'Shalini Merchant',
                'patient_location' => 'Bandra West, Mumbai',
                'rating' => 5,
                'headline' => 'Outstanding Cardiologist with Empathy',
                'comment' => 'Very thorough examination. Dr. Sen patiently listened to my symptoms, reviewed my 2D Echocardiogram, and fine-tuned my hypertension therapy. Booking online token saved me hours of waiting!',
                'treatment_received' => 'Hypertension & Lipid Consultation',
                'is_verified' => true,
                'is_published' => true,
            ],
            [
                'hospital_id' => $apollo->id,
                'branch_id' => $bandraBranch->id,
                'doctor_chart_id' => $chartRao->id,
                'patient_name' => 'Rohan Nair',
                'patient_location' => 'Khar West, Mumbai',
                'rating' => 5,
                'headline' => 'Miraculous Stroke Recovery',
                'comment' => 'My grandfather suffered an acute ischemic stroke. Brought to the Bandra branch where Dr. Sunita Rao initiated immediate protocol. Today he walks and speaks normally! Forever grateful to Dr. Rao.',
                'treatment_received' => 'Acute Stroke Thrombolysis & Neuro Rehab',
                'is_verified' => true,
                'is_published' => true,
            ],
            [
                'hospital_id' => $apollo->id,
                'branch_id' => $bandraBranch->id,
                'doctor_chart_id' => $chartRao->id,
                'patient_name' => 'Priyanka Chhabra',
                'patient_location' => 'Santacruz, Mumbai',
                'rating' => 5,
                'headline' => 'Best Neurologist in Mumbai for Chronic Migraine',
                'comment' => 'I suffered from debilitating migraines for 4 years. Dr. Sunita Rao’s prophylactic prescription and lifestyle adjustments made me migraine-free within three weeks.',
                'treatment_received' => 'Neurological Evaluation & Migraine Care',
                'is_verified' => true,
                'is_published' => true,
            ],
            [
                'hospital_id' => $apollo->id,
                'branch_id' => $thaneBranch->id,
                'doctor_chart_id' => $chartNair->id,
                'patient_name' => 'Sunanda Joshi',
                'patient_location' => 'Thane West, Maharashtra',
                'rating' => 5,
                'headline' => 'Pain-Free Walking After Knee Replacement',
                'comment' => 'Underwent bilateral total knee arthroplasty under Dr. Priya Nair. Pre-op counseling was comforting and post-op physio care was phenomenal. I am walking pain-free for the first time in 7 years.',
                'treatment_received' => 'Total Knee Arthroplasty (TKR)',
                'is_verified' => true,
                'is_published' => true,
            ],
            [
                'hospital_id' => $apollo->id,
                'branch_id' => $mainBranch->id,
                'doctor_chart_id' => $chartDsouza->id,
                'patient_name' => 'Devendra Patil',
                'patient_location' => 'Parel, Mumbai',
                'rating' => 5,
                'headline' => 'Swift Emergency Trauma Care at Midnight',
                'comment' => 'Arrived at 2:30 AM after a road accident. Dr. Kevin D’Souza and the Apollo trauma resuscitation team mobilized CT scan, blood cross-match, and stabilization within minutes. Exceptional emergency team!',
                'treatment_received' => 'Emergency Trauma Resuscitation',
                'is_verified' => true,
                'is_published' => true,
            ],
        ];

        foreach ($testimonials as $t) {
            DoctorTestimonial::create($t);
        }

        // 13. Seed Customizable System & Customer Frontend Settings
        $settings = [
            // Global Platform Defaults
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'general',
                'key' => 'site_name',
                'value' => 'Apollo Hospitals Healthcare Network',
                'type' => 'string',
                'label' => 'Platform Brand Name',
                'description' => 'Global branding name shown across platforms',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'general',
                'key' => 'brand_tagline',
                'value' => 'Touching Lives • World-Class Clinical Excellence Across India',
                'type' => 'string',
                'label' => 'Brand Tagline',
                'description' => 'Default tagline for health systems',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'frontend',
                'key' => 'frontend_hero_title',
                'value' => "India's Leading Multi-Super Specialty Healthcare Network",
                'type' => 'string',
                'label' => 'Customer Portal Hero Headline',
                'description' => 'Main headline on public patient portal',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'frontend',
                'key' => 'frontend_hero_subtitle',
                'value' => 'Book instant confirmed OPD consultation tokens, view doctor duty charts, and track live prescriptions across our multi-branch hospital network.',
                'type' => 'string',
                'label' => 'Customer Portal Subtitle',
                'description' => 'Subheading on customer portal',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'frontend',
                'key' => 'announcement_enabled',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Enable Announcement Banner',
                'description' => 'Toggle top customer alert bar',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'frontend',
                'key' => 'announcement_text',
                'value' => '⚡ Free Preventive Cardiac & Diabetes Screening Camp this week at all branches! Walk-in ECG & Blood Sugar consultations available.',
                'type' => 'string',
                'label' => 'Announcement Banner Text',
                'description' => 'Public notice message',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'frontend',
                'key' => 'allow_online_booking',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Allow Online OPD Booking',
                'description' => 'Allows public customers to self-book OPD tokens',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'frontend',
                'key' => 'show_doctor_fees',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Show Doctor Consultation Fees',
                'description' => 'Displays consultation tariffs on public doctor charts',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'contact',
                'key' => 'emergency_hotline',
                'value' => '1066 / +91 22 2656 7890',
                'type' => 'string',
                'label' => '24x7 Emergency Contact',
                'description' => 'Emergency helpline shown on header and banner',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'contact',
                'key' => 'ambulance_phone',
                'value' => '108 / +91 22 2656 7899',
                'type' => 'string',
                'label' => 'Ambulance Helpline',
                'description' => 'Ambulance dispatch phone number',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'contact',
                'key' => 'whatsapp_number',
                'value' => '+91 98200 12345',
                'type' => 'string',
                'label' => 'WhatsApp Assistance',
                'description' => 'WhatsApp customer desk',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'contact',
                'key' => 'support_email',
                'value' => 'care@apollocare.in',
                'type' => 'string',
                'label' => 'Support Email',
                'description' => 'Public patient assistance email',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'contact',
                'key' => 'hospital_address',
                'value' => 'Apollo Healthcare Complex, Dr. E. Moses Road, Mahalaxmi, Mumbai, Maharashtra 400011',
                'type' => 'string',
                'label' => 'Corporate Address',
                'description' => 'Central hospital physical address',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'contact',
                'key' => 'opd_hours',
                'value' => 'Mon - Sat: 08:00 AM - 08:00 PM (Emergency 24x7)',
                'type' => 'string',
                'label' => 'OPD Timings',
                'description' => 'General outpatient operational hours',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'clinical',
                'key' => 'currency_symbol',
                'value' => '₹',
                'type' => 'string',
                'label' => 'Currency Symbol',
                'description' => 'Official currency symbol for bills and fees',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'clinical',
                'key' => 'gstin_number',
                'value' => '27AAACA1234F1Z5',
                'type' => 'string',
                'label' => 'GSTIN Number',
                'description' => 'Indian GST Identification Number',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'clinical',
                'key' => 'token_prefix',
                'value' => 'TK-',
                'type' => 'string',
                'label' => 'OPD Token Prefix',
                'description' => 'Prefix code for generated tokens',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'gateway',
                'key' => 'sms_gateway_provider',
                'value' => 'Fast2SMS India Simulator',
                'type' => 'string',
                'label' => 'SMS Gateway Provider',
                'description' => 'SMS / WhatsApp Gateway integration',
            ],
            [
                'hospital_id' => null,
                'branch_id' => null,
                'group' => 'gateway',
                'key' => 'sms_sender_id',
                'value' => 'APOLLO',
                'type' => 'string',
                'label' => 'SMS Sender ID',
                'description' => 'DLT approved 6-character sender ID',
            ],

            // Branch Level Customizations
            [
                'hospital_id' => $apollo->id,
                'branch_id' => $bandraBranch->id, // Bandra
                'group' => 'contact',
                'key' => 'branch_notice',
                'value' => 'Bandra Branch: Evening OPD consultations for Cardiology & Diabetes extended up to 9:00 PM.',
                'type' => 'string',
                'label' => 'Branch Custom Notice',
                'description' => 'Notice shown specifically on Bandra branch portal',
            ],
            [
                'hospital_id' => $apollo->id,
                'branch_id' => $thaneBranch->id, // Thane
                'group' => 'contact',
                'key' => 'branch_notice',
                'value' => 'Thane Branch: Walk-in Ultrasound, 2D-Echo, and fasting blood tests open from 7:00 AM daily.',
                'type' => 'string',
                'label' => 'Branch Custom Notice',
                'description' => 'Notice shown specifically on Thane branch portal',
            ],
            [
                'hospital_id' => $apollo->id,
                'branch_id' => $puneBranch->id, // Pune
                'group' => 'contact',
                'key' => 'branch_notice',
                'value' => 'Pune Branch: 3.0T MRI, 128-Slice High Speed CT scan & PET-CT available round the clock.',
                'type' => 'string',
                'label' => 'Branch Custom Notice',
                'description' => 'Notice shown specifically on Pune branch portal',
            ],
        ];

        foreach ($settings as $settingData) {
            SystemSetting::updateOrCreate(
                [
                    'hospital_id' => $settingData['hospital_id'],
                    'branch_id' => $settingData['branch_id'],
                    'key' => $settingData['key'],
                ],
                $settingData
            );
        }

        // 13. Operation Theater (OT) Suites, Configurations & Equipment
        $otMumbai1 = OperationTheater::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Cardiac & Neuro Modular OT 1',
            'code' => 'OT-MUM-01',
            'type' => 'major_modular',
            'floor' => '4th Floor - Surgical Pavilion',
            'status' => 'occupied_in_surgery',
            'anesthesia_workstation' => 'Datex-Ohmeda Avance CS2 Pro',
            'c_arm_model' => 'Philips Veradius Unity Flat-Detector C-Arm',
            'heart_lung_machine' => 'Maquet HL-20 Perfusion Console',
            'laparoscopic_tower' => 'Karl Storz Image1 S 4K NIR/ICG Tower',
            'hepa_filter_status' => 'certified_active',
            'air_pressure_type' => 'positive_laminar_flow',
            'last_fumigation_at' => now()->subDays(2),
            'next_sterilization_due' => now()->addDays(5),
            'infection_control_cleared' => true,
            'is_active' => true,
        ]);

        $otMumbai2 = OperationTheater::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Hybrid Cath Lab & Endovascular Suite',
            'code' => 'OT-MUM-02',
            'type' => 'hybrid_cath_lab',
            'floor' => '4th Floor - Vascular Wing',
            'status' => 'available',
            'anesthesia_workstation' => 'GE Healthcare Carestation 650',
            'c_arm_model' => 'Philips Azurion 7 Biplane Cath Lab System',
            'heart_lung_machine' => 'Stockert S5 Perfusion Console',
            'laparoscopic_tower' => 'Olympus VISERA 4K UHD System',
            'hepa_filter_status' => 'certified_active',
            'air_pressure_type' => 'positive_laminar_flow',
            'last_fumigation_at' => now()->subDays(1),
            'next_sterilization_due' => now()->addDays(6),
            'infection_control_cleared' => true,
            'is_active' => true,
        ]);

        $otMumbai3 = OperationTheater::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Robotic & Laparoscopic Suite 3',
            'code' => 'OT-MUM-03',
            'type' => 'major_modular',
            'floor' => '4th Floor - Surgical Pavilion',
            'status' => 'available',
            'anesthesia_workstation' => 'Dräger Perseus A500',
            'c_arm_model' => 'Siemens Cios Alpha 3D C-Arm',
            'heart_lung_machine' => null,
            'laparoscopic_tower' => 'Da Vinci Xi 4-Arm Surgical Console + Stryker 1688 AIM 4K',
            'hepa_filter_status' => 'certified_active',
            'air_pressure_type' => 'positive_laminar_flow',
            'last_fumigation_at' => now()->subDays(3),
            'next_sterilization_due' => now()->addDays(4),
            'infection_control_cleared' => true,
            'is_active' => true,
        ]);

        $otBandra1 = OperationTheater::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'name' => 'Orthopedic & Joint Arthroplasty OT 1',
            'code' => 'OT-BND-01',
            'type' => 'major_modular',
            'floor' => '3rd Floor - Ortho Block',
            'status' => 'available',
            'anesthesia_workstation' => 'GE Healthcare Carestation 650',
            'c_arm_model' => 'Ziehm Solo FD High-Res C-Arm',
            'heart_lung_machine' => null,
            'laparoscopic_tower' => 'Smith & Nephew 4K Arthroscopy Stack',
            'hepa_filter_status' => 'certified_active',
            'air_pressure_type' => 'positive_laminar_flow',
            'last_fumigation_at' => now()->subDays(1),
            'next_sterilization_due' => now()->addDays(6),
            'infection_control_cleared' => true,
            'is_active' => true,
        ]);

        $otBandra2 = OperationTheater::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'name' => 'Day-Care & Endoscopy Suite 2',
            'code' => 'OT-BND-02',
            'type' => 'day_care',
            'floor' => '3rd Floor - Day Care Unit',
            'status' => 'available',
            'anesthesia_workstation' => 'Mindray WATO EX-65',
            'c_arm_model' => null,
            'heart_lung_machine' => null,
            'laparoscopic_tower' => 'Olympus EVIS X1 Endoscopy Video System',
            'hepa_filter_status' => 'certified_active',
            'air_pressure_type' => 'positive_laminar_flow',
            'last_fumigation_at' => now()->subDays(4),
            'next_sterilization_due' => now()->addDays(3),
            'infection_control_cleared' => true,
            'is_active' => true,
        ]);

        $otThane1 = OperationTheater::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $thaneBranch->id,
            'name' => 'Emergency Trauma & Resuscitation OT',
            'code' => 'OT-THN-01',
            'type' => 'minor_emergency',
            'floor' => 'Ground Floor - Emergency Wing',
            'status' => 'available',
            'anesthesia_workstation' => 'Dräger Fabius GS Premium',
            'c_arm_model' => 'Siemens Cios Select C-Arm',
            'heart_lung_machine' => null,
            'laparoscopic_tower' => 'Karl Storz Tele Pack X LED',
            'hepa_filter_status' => 'certified_active',
            'air_pressure_type' => 'positive_laminar_flow',
            'last_fumigation_at' => now()->subDays(2),
            'next_sterilization_due' => now()->addDays(5),
            'infection_control_cleared' => true,
            'is_active' => true,
        ]);

        $otPune1 = OperationTheater::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $puneBranch->id,
            'name' => 'Ophthalmic Micro-Surgery Suite',
            'code' => 'OT-PUN-01',
            'type' => 'ophthalmic',
            'floor' => '2nd Floor - Eye Care Centre',
            'status' => 'available',
            'anesthesia_workstation' => 'BleaseSirius Anesthesia System',
            'c_arm_model' => null,
            'heart_lung_machine' => null,
            'laparoscopic_tower' => null,
            'hepa_filter_status' => 'certified_active',
            'air_pressure_type' => 'positive_laminar_flow',
            'last_fumigation_at' => now()->subDays(3),
            'next_sterilization_due' => now()->addDays(4),
            'infection_control_cleared' => true,
            'is_active' => true,
        ]);

        // 14. Patient Surgeries
        $surg1 = OtSurgery::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'operation_theater_id' => $otMumbai1->id,
            'patient_id' => $patient1->id, // Rajesh Sharma
            'lead_surgeon_id' => $drCardio->id,
            'anesthesiologist_id' => $drEr->id,
            'scrub_nurse_id' => $nurseMary->id,
            'surgery_name' => 'Off-Pump Coronary Artery Bypass Graft (CABG x 3 LIMA-LAD, SVG-OM, SVG-PDA)',
            'surgery_code' => 'SURG-2026-1042',
            'surgery_category' => 'super_major',
            'pre_op_diagnosis' => 'Severe Triple Vessel Coronary Artery Disease (TVCAD) with 90% proximal LAD lesion and preserved EF 52%',
            'post_op_findings' => null,
            'surgical_notes' => 'Median sternotomy performed. LIMA harvested with pedicle. Hemodynamics maintained stable with inotropic support.',
            'scheduled_start_time' => now()->subHours(2),
            'scheduled_end_time' => now()->addHours(2),
            'actual_start_time' => now()->subHours(2),
            'status' => 'in_progress',
            'anesthesia_type' => 'general',
            'blood_units_transfused' => 2,
        ]);

        $surg2 = OtSurgery::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'operation_theater_id' => $otBandra1->id,
            'patient_id' => $patient2->id, // Sunita Patel
            'lead_surgeon_id' => $drCardio->id,
            'anesthesiologist_id' => $drNeuro->id,
            'scrub_nurse_id' => $nurseMary->id,
            'surgery_name' => 'Computer-Navigated Total Knee Arthroplasty (Right TKR)',
            'surgery_code' => 'SURG-2026-1088',
            'surgery_category' => 'major',
            'pre_op_diagnosis' => 'Bilateral Severe Tricompartmental Osteoarthritis (Ahlbäck Grade IV) with Varus Deformity Right Knee',
            'post_op_findings' => 'Distal femoral and proximal tibial bone cuts verified with infrared navigation. Excellent knee range of motion 0-120 degrees achieved.',
            'surgical_notes' => 'Pneumatic tourniquet applied at 250 mmHg for 48 minutes. Cruciate-retaining Stryker Triathlon implant cemented with Simplex antibiotic bone cement.',
            'scheduled_start_time' => now()->subHours(5),
            'scheduled_end_time' => now()->subHours(2),
            'actual_start_time' => now()->subHours(5),
            'actual_end_time' => now()->subHours(2),
            'status' => 'post_op_recovery',
            'anesthesia_type' => 'spinal_epidural',
            'blood_units_transfused' => 0,
        ]);

        $surg3 = OtSurgery::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'operation_theater_id' => $otMumbai3->id,
            'patient_id' => $patient3->id, // Priya Kulkarni
            'lead_surgeon_id' => $drCardio->id,
            'anesthesiologist_id' => $drEr->id,
            'scrub_nurse_id' => $nurseMary->id,
            'surgery_name' => 'Four-Port Laparoscopic Cholecystectomy',
            'surgery_code' => 'SURG-2026-1120',
            'surgery_category' => 'major',
            'pre_op_diagnosis' => 'Symptomatic Cholelithiasis with Recurrent Biliary Colic and Thickened Gallbladder Wall',
            'post_op_findings' => 'Distended gallbladder with multiple facet stones. Calot triangle dissected cleanly. Cystic artery and duct clipped with titanium ligating clips.',
            'surgical_notes' => 'Pneumoperitoneum established with Veress needle at 12 mmHg. Specimen extracted intact via umbilical port with endobag. Minimal blood loss (<20ml).',
            'scheduled_start_time' => now()->subDays(1)->setTime(10, 0),
            'scheduled_end_time' => now()->subDays(1)->setTime(11, 45),
            'actual_start_time' => now()->subDays(1)->setTime(10, 0),
            'actual_end_time' => now()->subDays(1)->setTime(11, 30),
            'status' => 'completed',
            'anesthesia_type' => 'general',
            'blood_units_transfused' => 0,
        ]);

        $surg4 = OtSurgery::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $puneBranch->id,
            'operation_theater_id' => $otPune1->id,
            'patient_id' => $patient4->id, // Mohammed Al-Mansoor
            'lead_surgeon_id' => $drNeuro->id,
            'anesthesiologist_id' => $drEr->id,
            'scrub_nurse_id' => $nurseMary->id,
            'surgery_name' => 'Phacoemulsification with Foldable Hydrophobic Acrylic Monofocal IOL Implantation',
            'surgery_code' => 'SURG-2026-1215',
            'surgery_category' => 'minor',
            'pre_op_diagnosis' => 'Senile Nuclear Sclerotic Cataract Grade 3 (Right Eye)',
            'post_op_findings' => null,
            'surgical_notes' => 'Clear corneal temporal incision 2.2mm. Continuous curvilinear capsulorhexis 5.5mm. Nucleus emulsified with torsional phaco.',
            'scheduled_start_time' => now()->addDay()->setTime(11, 0),
            'scheduled_end_time' => now()->addDay()->setTime(12, 0),
            'status' => 'scheduled',
            'anesthesia_type' => 'local_sedation',
            'blood_units_transfused' => 0,
        ]);

        // 15. Itemized OT Surgical Medications & Consumables Ledger
        // Medications for Surgery 1 (CABG)
        $cabgMeds = [
            [
                'medicine_name' => 'Propofol 1% 20ml Emulsion IV',
                'quantity_used' => 2.0,
                'unit' => 'Vial',
                'administration_stage' => 'induction',
                'batch_number' => 'BAT-PROP-994',
                'cost_per_unit' => 280.00,
                'total_cost' => 560.00,
                'administered_by' => 'Dr. Kevin D’Souza',
                'notes' => 'Rapid smooth induction of general anesthesia with BIS monitoring',
            ],
            [
                'medicine_name' => 'Fentanyl Citrate 50mcg/ml 2ml Injection',
                'quantity_used' => 4.0,
                'unit' => 'Ampoule',
                'administration_stage' => 'induction',
                'batch_number' => 'BAT-FNT-881',
                'cost_per_unit' => 145.00,
                'total_cost' => 580.00,
                'administered_by' => 'Dr. Kevin D’Souza',
                'notes' => 'High-dose opioid analgesia prior to endotracheal intubation',
            ],
            [
                'medicine_name' => 'Vecuronium Bromide 4mg Lyophilized IV',
                'quantity_used' => 3.0,
                'unit' => 'Vial',
                'administration_stage' => 'maintenance',
                'batch_number' => 'BAT-VEC-772',
                'cost_per_unit' => 190.00,
                'total_cost' => 570.00,
                'administered_by' => 'Dr. Kevin D’Souza',
                'notes' => 'Neuromuscular blockade maintained for sternotomy and IMA harvesting',
            ],
            [
                'medicine_name' => 'Sevoflurane Inhalation Vapor 250ml',
                'quantity_used' => 1.0,
                'unit' => 'Bottle',
                'administration_stage' => 'maintenance',
                'batch_number' => 'BAT-SEV-401',
                'cost_per_unit' => 4850.00,
                'total_cost' => 4850.00,
                'administered_by' => 'Dr. Kevin D’Souza',
                'notes' => 'Volatile inhalational maintenance at 1.2 to 1.8 MAC',
            ],
            [
                'medicine_name' => 'Heparin Sodium 25,000 IU/5ml Injection',
                'quantity_used' => 2.0,
                'unit' => 'Vial',
                'administration_stage' => 'intra_op',
                'batch_number' => 'BAT-HEP-210',
                'cost_per_unit' => 650.00,
                'total_cost' => 1300.00,
                'administered_by' => 'Dr. Kevin D’Souza',
                'notes' => 'Full systemic heparinization; Target ACT > 450 seconds confirmed',
            ],
            [
                'medicine_name' => 'Protamine Sulfate 50mg/5ml Injection',
                'quantity_used' => 2.0,
                'unit' => 'Ampoule',
                'administration_stage' => 'reversal',
                'batch_number' => 'BAT-PRO-319',
                'cost_per_unit' => 420.00,
                'total_cost' => 840.00,
                'administered_by' => 'Dr. Kevin D’Souza',
                'notes' => 'Slow infusion for heparin reversal; Post-reversal ACT returned to 125 seconds',
            ],
            [
                'medicine_name' => 'Surgicel Fibrillar Oxidized Cellulose Hemostat',
                'quantity_used' => 2.0,
                'unit' => 'Pack',
                'administration_stage' => 'intra_op',
                'batch_number' => 'BAT-SURG-104',
                'cost_per_unit' => 3200.00,
                'total_cost' => 6400.00,
                'administered_by' => 'Sr. Mary Fernandez',
                'notes' => 'Topical hemostasis over sternal marrow and pericardial margins',
            ],
            [
                'medicine_name' => 'Prolene 7-0 Double-Armed BV-1 Suture',
                'quantity_used' => 3.0,
                'unit' => 'Suture Box',
                'administration_stage' => 'intra_op',
                'batch_number' => 'BAT-SUT-602',
                'cost_per_unit' => 1150.00,
                'total_cost' => 3450.00,
                'administered_by' => 'Sr. Mary Fernandez',
                'notes' => 'Microvascular running sutures for LIMA to LAD anastomosis',
            ],
            [
                'medicine_name' => 'Normal Saline 0.9% 500ml IV Infusion',
                'quantity_used' => 6.0,
                'unit' => 'Bag (500ml)',
                'administration_stage' => 'intra_op',
                'batch_number' => 'BAT-NS-551',
                'cost_per_unit' => 48.00,
                'total_cost' => 288.00,
                'administered_by' => 'Sr. Mary Fernandez',
                'notes' => 'Crystalloid maintenance and surgical site irrigation',
            ],
        ];

        foreach ($cabgMeds as $medItem) {
            OtMedicationLog::create(array_merge($medItem, [
                'hospital_id' => $apollo->id,
                'branch_id' => $mainBranch->id,
                'ot_surgery_id' => $surg1->id,
                'patient_id' => $patient1->id,
            ]));
        }

        // Medications for Surgery 2 (TKR)
        $tkrMeds = [
            [
                'medicine_name' => 'Bupivacaine Heavy 0.5% 4ml Ampoule',
                'quantity_used' => 1.0,
                'unit' => 'Ampoule',
                'administration_stage' => 'induction',
                'batch_number' => 'BAT-BUP-401',
                'cost_per_unit' => 85.00,
                'total_cost' => 85.00,
                'administered_by' => 'Dr. Sunita Rao',
                'notes' => 'Subarachnoid block at L3-L4 space with 25G Quincke needle; Sensory level up to T10',
            ],
            [
                'medicine_name' => 'Tranexamic Acid 500mg/5ml Injection',
                'quantity_used' => 3.0,
                'unit' => 'Ampoule',
                'administration_stage' => 'intra_op',
                'batch_number' => 'BAT-TXA-882',
                'cost_per_unit' => 120.00,
                'total_cost' => 360.00,
                'administered_by' => 'Dr. Sunita Rao',
                'notes' => 'Given slow IV prior to tourniquet deflation to minimize blood loss',
            ],
            [
                'medicine_name' => 'Cefuroxime 1.5g IV Vial',
                'quantity_used' => 2.0,
                'unit' => 'Vial',
                'administration_stage' => 'pre_medication',
                'batch_number' => 'BAT-CEF-912',
                'cost_per_unit' => 260.00,
                'total_cost' => 520.00,
                'administered_by' => 'Dr. Sunita Rao',
                'notes' => 'Prophylactic antibiotic 30 minutes before skin incision',
            ],
            [
                'medicine_name' => 'Vicryl 1-0 Heavy Absorbable Suture',
                'quantity_used' => 2.0,
                'unit' => 'Pack',
                'administration_stage' => 'intra_op',
                'batch_number' => 'BAT-SUT-409',
                'cost_per_unit' => 580.00,
                'total_cost' => 1160.00,
                'administered_by' => 'Sr. Mary Fernandez',
                'notes' => 'Watertight knee arthrotomy and extensor capsule closure',
            ],
        ];

        foreach ($tkrMeds as $medItem) {
            OtMedicationLog::create(array_merge($medItem, [
                'hospital_id' => $apollo->id,
                'branch_id' => $bandraBranch->id,
                'ot_surgery_id' => $surg2->id,
                'patient_id' => $patient2->id,
            ]));
        }

        // Medications for Surgery 3 (Laparoscopic Cholecystectomy)
        $lapMeds = [
            [
                'medicine_name' => 'Propofol 1% 20ml Emulsion IV',
                'quantity_used' => 1.5,
                'unit' => 'Vial',
                'administration_stage' => 'induction',
                'batch_number' => 'BAT-PROP-994',
                'cost_per_unit' => 280.00,
                'total_cost' => 420.00,
                'administered_by' => 'Dr. Kevin D’Souza',
                'notes' => 'IV induction with lidocaine pre-treatment',
            ],
            [
                'medicine_name' => 'Atracurium Besylate 25mg/2.5ml Injection',
                'quantity_used' => 2.0,
                'unit' => 'Ampoule',
                'administration_stage' => 'intra_op',
                'batch_number' => 'BAT-ATR-711',
                'cost_per_unit' => 160.00,
                'total_cost' => 320.00,
                'administered_by' => 'Dr. Kevin D’Souza',
                'notes' => 'Abdominal wall relaxation during pneumoperitoneum',
            ],
            [
                'medicine_name' => 'Neostigmine 0.5mg + Glycopyrrolate 0.2mg Combipack',
                'quantity_used' => 2.0,
                'unit' => 'Ampoule',
                'administration_stage' => 'reversal',
                'batch_number' => 'BAT-REV-303',
                'cost_per_unit' => 95.00,
                'total_cost' => 190.00,
                'administered_by' => 'Dr. Kevin D’Souza',
                'notes' => 'Complete reversal of neuromuscular block; smooth extubation',
            ],
            [
                'medicine_name' => 'Paracetamol 1000mg/100ml IV Infusion',
                'quantity_used' => 1.0,
                'unit' => 'Bottle',
                'administration_stage' => 'post_op_recovery',
                'batch_number' => 'BAT-PCM-818',
                'cost_per_unit' => 110.00,
                'total_cost' => 110.00,
                'administered_by' => 'Sr. Mary Fernandez',
                'notes' => 'Post-operative analgesia administered in PACU',
            ],
        ];

        foreach ($lapMeds as $medItem) {
            OtMedicationLog::create(array_merge($medItem, [
                'hospital_id' => $apollo->id,
                'branch_id' => $mainBranch->id,
                'ot_surgery_id' => $surg3->id,
                'patient_id' => $patient3->id,
            ]));
        }

        AuditLogger::log(
            moduleKey: 'ot',
            action: 'ot_suites_and_surgeries_initialized',
            entityType: 'OperationTheater',
            entityId: $otMumbai1->id,
            details: ['hospital' => $apollo->name, 'surgeries_count' => 4, 'medications_logged' => count($cabgMeds) + count($tkrMeds) + count($lapMeds)],
            hospitalId: $apollo->id
        );

        // Audit Trail Logs
        AuditLogger::log(
            moduleKey: 'admissions',
            action: 'emergency_inpatient_admitted',
            entityType: 'Admission',
            entityId: $admission1->id,
            details: ['mrn' => $patient1->mrn, 'bed' => 'CCU-BED-01', 'branch' => 'APO-MUM-01'],
            hospitalId: $apollo->id
        );

        // 15. Seed Hospital-Wise Health Checkup Packages (Configured by Super Admin & Hospital Admin)
        $pkg1 = HealthCheckupPackage::create([
            'hospital_id' => $apollo->id,
            'branch_id' => null, // Hospital-wide available at all branches
            'name' => 'Apollo Executive Whole Body Health Checkup',
            'code' => 'APO-PKG-EXEC-01',
            'slug' => 'apollo-executive-whole-body-checkup',
            'category' => 'Whole Body',
            'original_price' => 9500.00,
            'discounted_price' => 4999.00,
            'tests_count' => 83,
            'turnaround_time' => 'Same Day (6 Hours)',
            'fasting_required' => true,
            'fasting_hours' => 12,
            'gender_target' => 'All',
            'age_group' => '18+ Years',
            'tests_included' => [
                'Complete Hemogram (28 Blood Parameters)',
                'Lipid Profile (Cholesterol, HDL, LDL, VLDL, Triglycerides)',
                'Liver Function Test (Bilirubin, SGOT, SGPT, Alkaline Phosphatase)',
                'Kidney Function Test (Urea, Serum Creatinine, Uric Acid, BUN)',
                'Glycated Hemoglobin (HbA1c) & Fasting Blood Sugar',
                'Thyroid Profile (Total T3, Total T4, Ultrasensitive TSH)',
                'Vitamin D3 (25-OH) & Vitamin B12 Quantitative',
                'High-Sensitivity C-Reactive Protein (hs-CRP)',
                'Urine Routine & Microscopic Examination',
                '12-Lead Electrocardiogram (ECG)',
                'Digital Chest X-Ray PA View',
                'Ultrasound Whole Abdomen & Pelvis',
                'Senior Consultant Physician Physical Examination',
                'Post-Checkup Clinical Diet & Lifestyle Counseling',
            ],
            'description' => 'Our flagship comprehensive preventive screening package designed to evaluate cardiac health, metabolic wellness, vital organ functions, and lifestyle disease risk.',
            'is_popular' => true,
            'is_active' => true,
        ]);

        $pkg2 = HealthCheckupPackage::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id, // Branch specific: Apollo Mumbai Central
            'name' => 'Apollo Master Cardiac & Coronary Heart Screen',
            'code' => 'APO-PKG-CARD-02',
            'slug' => 'apollo-master-cardiac-screening',
            'category' => 'Cardiac Care',
            'original_price' => 6800.00,
            'discounted_price' => 3499.00,
            'tests_count' => 42,
            'turnaround_time' => 'Same Day (4 Hours)',
            'fasting_required' => true,
            'fasting_hours' => 10,
            'gender_target' => 'All',
            'age_group' => '25+ Years',
            'tests_included' => [
                '12-Lead Resting Electrocardiogram (ECG)',
                '2D Echocardiography with Color Doppler (2D Echo)',
                'Treadmill Stress Test (TMT / Stress ECG)',
                'Comprehensive Lipid Profile with Apolipoprotein A1 & B',
                'Cardiac Inflammatory Biomarkers (hs-CRP, Homocysteine)',
                'Serum Electrolytes (Sodium, Potassium, Chloride)',
                'Fasting Blood Sugar & Serum Creatinine',
                'One-on-One Consultation with Senior Interventional Cardiologist',
                '10-Year Framingham Coronary Risk Assessment',
            ],
            'description' => 'Specialized non-invasive cardiac evaluation including 2D Echo and Stress TMT, reviewed directly by our Senior Interventional Cardiologists.',
            'is_popular' => true,
            'is_active' => true,
        ]);

        $pkg3 = HealthCheckupPackage::create([
            'hospital_id' => $apollo->id,
            'branch_id' => null,
            'name' => 'Apollo Advanced Diabetic Comprehensive Care',
            'code' => 'APO-PKG-DIAB-03',
            'slug' => 'apollo-advanced-diabetic-care',
            'category' => 'Diabetic Care',
            'original_price' => 4200.00,
            'discounted_price' => 1999.00,
            'tests_count' => 35,
            'turnaround_time' => 'Same Day (4 Hours)',
            'fasting_required' => true,
            'fasting_hours' => 10,
            'gender_target' => 'All',
            'age_group' => 'All Ages',
            'tests_included' => [
                'Fasting Blood Sugar (FBS)',
                'Post-Prandial Blood Sugar (PPBS - 2 hrs after meal)',
                'HbA1c Glycated Hemoglobin with Estimated Average Glucose (eAG)',
                'Lipid Profile Comprehensive',
                'Kidney Health Evaluation (Serum Creatinine, Blood Urea, eGFR)',
                'Urine Microalbumin / Creatinine Ratio (UACR)',
                'Diabetic Foot Neuropathy Biothesiometry Screening',
                'Diabetic Retinopathy Fundus Examination',
                'Consultation with Consultant Diabetologist & Nutritionist',
            ],
            'description' => 'In-depth diagnostic protocol to detect early diabetic nephropathy, neuropathy, and glycemic fluctuations to prevent end-organ damage.',
            'is_popular' => false,
            'is_active' => true,
        ]);

        $pkg4 = HealthCheckupPackage::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id, // Apollo Bandra Clinic
            'name' => 'Apollo Well-Woman Comprehensive Screening',
            'code' => 'APO-PKG-WOMEN-04',
            'slug' => 'apollo-well-woman-screening',
            'category' => "Women's Wellness",
            'original_price' => 7500.00,
            'discounted_price' => 3799.00,
            'tests_count' => 54,
            'turnaround_time' => 'Same Day (6 Hours)',
            'fasting_required' => true,
            'fasting_hours' => 10,
            'gender_target' => 'Female',
            'age_group' => '20+ Years',
            'tests_included' => [
                'Complete Blood Count with ESR',
                'Thyroid Function Profile (T3, T4, TSH Ultrasensitive)',
                'Vitamin D3 (25-OH) & Total Serum Calcium',
                'Serum Ferritin & Iron Deficiency Screening',
                'Digital Bilateral Mammography / Breast Ultrasound (USG)',
                'Liquid-Based Cytology (LBC) Pap Smear',
                'Pelvic Ultrasound (TVS / TAS)',
                'CA-125 Ovarian Marker Blood Screen',
                'Consultation with Senior Consultant Gynecologist',
            ],
            'description' => 'Targeted wellness package tailored for women across all stages of life, covering hormonal balance, bone density, and breast/cervical health.',
            'is_popular' => true,
            'is_active' => true,
        ]);

        $pkg5 = HealthCheckupPackage::create([
            'hospital_id' => $apollo->id,
            'branch_id' => null,
            'name' => 'Apollo Senior Citizen Golden Age Wellness',
            'code' => 'APO-PKG-SENIOR-05',
            'slug' => 'apollo-senior-citizen-wellness',
            'category' => 'Senior Citizen',
            'original_price' => 5800.00,
            'discounted_price' => 2999.00,
            'tests_count' => 62,
            'turnaround_time' => 'Same Day (6 Hours)',
            'fasting_required' => true,
            'fasting_hours' => 10,
            'gender_target' => 'All',
            'age_group' => '60+ Years',
            'tests_included' => [
                'Complete Hemogram & Peripheral Blood Smear',
                'Extended Liver & Renal Function Panels',
                'Cardiac Risk Profile (Lipids + 12-Lead ECG)',
                'Bone Mineral Health (Calcium, Phosphorous, Alkaline Phosphatase)',
                'Serum Uric Acid (Gout Screening)',
                'PSA (Prostate Specific Antigen) for Male / Thyroid for Female',
                'Stool Occult Blood (Colorectal Screen)',
                'Audiometry Hearing Assessment & Vision Check',
                'Comprehensive Geriatric Medicine Review',
            ],
            'description' => 'Carefully curated for elders aged 60 and above, focusing on arthritis, cardiac health, kidney wellness, and prostate/hormonal parameters.',
            'is_popular' => false,
            'is_active' => true,
        ]);

        $pkg6 = HealthCheckupPackage::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $puneBranch->id, // Apollo Pune Center
            'name' => 'Apollo Active Fit & Preventive Health Screen',
            'code' => 'APO-PKG-FIT-06',
            'slug' => 'apollo-active-fit-health-screen',
            'category' => 'Preventive Care',
            'original_price' => 3000.00,
            'discounted_price' => 1499.00,
            'tests_count' => 28,
            'turnaround_time' => 'Same Day (3 Hours)',
            'fasting_required' => true,
            'fasting_hours' => 8,
            'gender_target' => 'All',
            'age_group' => 'All Ages',
            'tests_included' => [
                'Complete Hemogram (CBC)',
                'Fasting Blood Glucose',
                'Serum Total Cholesterol & Triglycerides',
                'Serum Creatinine & Blood Urea Nitrogen',
                'SGPT (Alanine Transaminase Liver Screen)',
                'Urine Routine & Microscopic',
                'Body Mass Index (BMI) & Body Composition Metrics',
                'Physician Physical Consultation & Fitness Clearance',
            ],
            'description' => 'Essential preventive health screen for working professionals and fitness enthusiasts to monitor basic metabolic vitality.',
            'is_popular' => false,
            'is_active' => true,
        ]);

        // Seed Sample Health Checkup Package Booking
        PackageBooking::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'package_id' => $pkg1->id,
            'booking_number' => 'PKG-BK-2026-1001',
            'patient_name' => 'Gautam Singhal',
            'patient_phone' => '+91 98200 44556',
            'patient_email' => 'gautam.singhal@example.com',
            'patient_age' => 45,
            'patient_gender' => 'male',
            'preferred_date' => now()->addDays(2)->toDateString(),
            'notes' => 'Annual corporate executive health checkup. Preferred morning slot 08:00 AM.',
            'status' => 'confirmed',
        ]);

        AuditLogger::log(
            moduleKey: 'packages',
            action: 'health_checkup_packages_configured',
            entityType: 'HealthCheckupPackage',
            entityId: $pkg1->id,
            details: ['hospital' => $apollo->name, 'packages_seeded' => 6],
            hospitalId: $apollo->id
        );

        // 17. Seed Detailed Clinical Histories for Patients (Allergies, Chronic Illnesses, Past Surgeries)
        $patients = Patient::where('hospital_id', $apollo->id)->get();
        if ($patients->count() > 0) {
            $p0 = $patients[0]; // e.g. Rahul Verma
            $p0->update([
                'allergies' => 'Penicillin, NSAIDs (Aspirin, Ibuprofen)',
                'chronic_conditions' => 'Type-2 Diabetes Mellitus, Essential Hypertension (Stage 2)',
                'past_surgeries' => 'Laparoscopic Appendectomy (2018), Inguinal Hernioplasty (2022)',
                'family_medical_history' => 'Maternal Coronary Artery Disease; Paternal Type-2 Diabetes',
                'emergency_contact_name' => 'Kavita Verma (Spouse)',
                'emergency_contact_phone' => '+91 98201 11223',
            ]);

            if (isset($patients[1])) {
                $p1 = $patients[1]; // Meera Patel
                $p1->update([
                    'allergies' => 'Sulfa Antibiotics, Shellfish',
                    'chronic_conditions' => 'Mild Bronchial Asthma, Allergic Rhinitis',
                    'past_surgeries' => 'Right Knee Arthroscopy (2021)',
                    'family_medical_history' => 'Mother has Rheumatoid Arthritis',
                    'emergency_contact_name' => 'Kiran Patel (Brother)',
                    'emergency_contact_phone' => '+91 98202 33445',
                ]);
            }

            if (isset($patients[2])) {
                $p2 = $patients[2]; // Amit Deshmukh
                $p2->update([
                    'allergies' => 'No known drug allergies (NKDA)',
                    'chronic_conditions' => 'Coronary Artery Disease (CAD), Triple Vessel Disease',
                    'past_surgeries' => 'Coronary Angioplasty with DES Stent Placement (2022)',
                    'family_medical_history' => 'Early Myocardial Infarction in father at age 48',
                    'emergency_contact_name' => 'Smita Deshmukh (Spouse)',
                    'emergency_contact_phone' => '+91 98203 55667',
                ]);
            }
        }

        // 18. Seed Statutory Vital Records (Births & Deaths)
        $drAnanya = User::where('email', 'dr.ananya@apollocare.in')->first() ?? $superAdmin;
        $drKevin = User::where('email', 'dr.kevin@apollocare.in')->first() ?? $superAdmin;

        $motherPatient = $patients->firstWhere('gender', 'female') ?? $patients->first();

        // Sample Birth Record 1 (Normal Delivery)
        $birth1 = BirthRecord::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'mother_id' => $motherPatient?->id,
            'doctor_id' => $drAnanya->id,
            'pediatrician_id' => $drKevin->id,
            'certificate_number' => 'BC-MH-2026-0001',
            'baby_name' => 'Baby Boy of '.($motherPatient ? $motherPatient->last_name : 'Verma'),
            'gender' => 'male',
            'birth_date' => now()->subDays(3)->toDateString(),
            'birth_time' => '06:45:00',
            'mother_name' => $motherPatient ? $motherPatient->full_name : 'Sunita Verma',
            'father_name' => 'Rahul Verma',
            'birth_weight_kg' => 3.25,
            'delivery_type' => 'normal_vaginal',
            'gestational_age_weeks' => 39,
            'apgar_score_1min' => 9,
            'apgar_score_5min' => 10,
            'blood_group' => 'O+',
            'status' => 'certificate_issued',
            'notes' => 'Healthy male infant cried immediately after birth. Skin-to-skin completed.',
        ]);

        // Sample Birth Record 2 (LSCS Caesarean)
        $birth2 = BirthRecord::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'mother_id' => null,
            'doctor_id' => $drAnanya->id,
            'pediatrician_id' => $drKevin->id,
            'certificate_number' => 'BC-MH-2026-0002',
            'baby_name' => 'Aaradhya Deshmukh',
            'gender' => 'female',
            'birth_date' => now()->subDays(7)->toDateString(),
            'birth_time' => '14:20:00',
            'mother_name' => 'Pooja Deshmukh',
            'father_name' => 'Kiran Deshmukh',
            'birth_weight_kg' => 2.95,
            'delivery_type' => 'lscs_caesarean',
            'gestational_age_weeks' => 38,
            'apgar_score_1min' => 8,
            'apgar_score_5min' => 9,
            'blood_group' => 'B+',
            'status' => 'certificate_issued',
            'notes' => 'Elective LSCS for breech presentation. Transferred to maternity suite.',
        ]);

        // Sample Death Record (MCCD Form 4)
        $deceasedPatient = $patients->last() ?? $patients->first();
        if ($deceasedPatient) {
            DeathRecord::create([
                'hospital_id' => $apollo->id,
                'branch_id' => $mainBranch->id,
                'patient_id' => $deceasedPatient->id,
                'certifying_doctor_id' => $drKevin->id,
                'certificate_number' => 'DC-MH-2026-0001',
                'date_of_death' => now()->subDays(10)->toDateString(),
                'time_of_death' => '23:15:00',
                'place_of_death' => 'Intensive Care Unit (ICU)',
                'primary_cause' => 'Acute Cardiorespiratory Arrest secondary to Refractory Cardiogenic Shock',
                'secondary_cause' => 'Severe Acute Anterior Wall STEMI with Triple Vessel CAD',
                'icd10_code' => 'I46.9',
                'next_of_kin_name' => 'Ramesh '.$deceasedPatient->last_name,
                'next_of_kin_phone' => '+91 98209 88776',
                'next_of_kin_relation' => 'Son',
                'body_released_to' => 'Ramesh '.$deceasedPatient->last_name,
                'cremation_pass_issued' => true,
                'autopsy_required' => false,
                'status' => 'certified',
                'notes' => 'Medical certificate of cause of death issued under Form 4. Natural death.',
            ]);
        }

        // 19. Seed Ambulance Fleet & Emergency Dispatches
        $amb1 = Ambulance::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'vehicle_number' => 'MH-01-CR-1088',
            'vehicle_model' => 'Force Traveller Cardiac Mobile ICU (ALS)',
            'type' => 'advanced_life_support',
            'driver_name' => 'Sanjay Jadhav',
            'driver_phone' => '+91 98190 12345',
            'paramedic_name' => 'Ganesh Patil, EMT-P',
            'status' => 'available',
            'current_location' => 'Main ER Bay Standby',
            'equipment_checklist' => ['Ventilator', 'Defibrillator', 'Infusion Pump', 'Suction', 'Oxygen Cylinder'],
            'is_active' => true,
        ]);

        $amb2 = Ambulance::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'vehicle_number' => 'MH-02-ER-2099',
            'vehicle_model' => 'Tata Winger Critical Transit (BLS)',
            'type' => 'basic_life_support',
            'driver_name' => 'Mahesh Pawar',
            'driver_phone' => '+91 98200 67890',
            'paramedic_name' => 'Sunil Shinde, EMT-B',
            'status' => 'on_call_dispatch',
            'current_location' => 'En route to Bandra Linking Road',
            'equipment_checklist' => ['AED Defibrillator', 'Stretcher', 'Oxygen Bag', 'First Aid Kit'],
            'is_active' => true,
        ]);

        $amb3 = Ambulance::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $thaneBranch->id,
            'vehicle_number' => 'MH-04-IC-3311',
            'vehicle_model' => 'Mahindra Bolero Neonatal Transport Unit',
            'type' => 'neonatal_mobile_icu',
            'driver_name' => 'Vijay Kamble',
            'driver_phone' => '+91 98330 99887',
            'paramedic_name' => 'Dr. Ritu Saxena (Fellow Neonatologist)',
            'status' => 'available',
            'current_location' => 'Thane NICU Bay',
            'equipment_checklist' => ['Transport Incubator', 'Neonatal Syringe Driver', 'Nitric Oxide Ready'],
            'is_active' => true,
        ]);

        // Sample Ambulance Trip
        AmbulanceTrip::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'ambulance_id' => $amb1->id,
            'patient_id' => $patients->first()?->id,
            'trip_number' => 'TRIP-'.date('Ymd').'-001',
            'patient_name' => 'Harish Chandra',
            'patient_phone' => '+91 98211 44332',
            'pickup_address' => 'Flat 402, Sea Green Apts, Worli Sea Face, Mumbai',
            'destination_hospital' => 'Apollo Care Flagship ER Pavilion',
            'emergency_category' => 'Acute Coronary Syndrome / STEMI',
            'dispatched_at' => now()->subHours(2),
            'reached_patient_at' => now()->subHours(2)->addMinutes(12),
            'arrived_hospital_at' => now()->subHours(1)->addMinutes(45),
            'status' => 'completed',
            'distance_km' => 8.4,
            'fare_amount' => 1800.00,
            'paramedic_triage_notes' => '12-lead ECG transmitted to Cath Lab. Aspirin 300mg + Clopidogrel 300mg administered en route.',
        ]);

        // 20. Seed Inpatient Dietary Orders
        $admittedAdm = Admission::where('hospital_id', $apollo->id)->where('status', 'admitted')->first();
        if ($admittedAdm) {
            DietaryOrder::create([
                'hospital_id' => $apollo->id,
                'branch_id' => $mainBranch->id,
                'patient_id' => $admittedAdm->patient_id,
                'admission_id' => $admittedAdm->id,
                'prescribed_by_id' => $drAnanya->id,
                'diet_type' => 'Diabetic & Low Sodium',
                'meal_slot' => 'lunch',
                'order_date' => now()->toDateString(),
                'room_bed_number' => 'ICU Bed 04',
                'calorie_target' => '1800 kcal',
                'allergies_and_restrictions' => 'Sugar-free, salt-restricted (<2g Na+), lactose-friendly',
                'kitchen_instructions' => 'Puree consistency, fresh steamed seasonal vegetables, brown rice khichdi.',
                'status' => 'delivered',
            ]);

            DietaryOrder::create([
                'hospital_id' => $apollo->id,
                'branch_id' => $mainBranch->id,
                'patient_id' => $admittedAdm->patient_id,
                'admission_id' => $admittedAdm->id,
                'prescribed_by_id' => $drAnanya->id,
                'diet_type' => 'High Protein Recovery Diet',
                'meal_slot' => 'dinner',
                'order_date' => now()->toDateString(),
                'room_bed_number' => 'ICU Bed 04',
                'calorie_target' => '2000 kcal',
                'allergies_and_restrictions' => 'No raw foods',
                'kitchen_instructions' => 'Paneer bhurji, yellow lentil soup, warm turmeric almond milk at 09:00 PM.',
                'status' => 'cooking',
            ]);
        }

        // 21. Seed Biomedical Equipment & Asset Maintenance
        $equip1 = BiomedicalEquipment::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Dräger Fabius Plus Anesthesia Workstation',
            'asset_code' => 'BME-MUM-ANES-01',
            'category' => 'Life Support & Critical Care',
            'manufacturer' => 'Dräger Medical Germany',
            'model_number' => 'Fabius Plus XL',
            'serial_number' => 'DRG-2024-99812',
            'location_room' => 'Operation Theater Suite 01',
            'installation_date' => '2024-03-15',
            'warranty_expiry' => '2029-03-14',
            'last_calibration_date' => now()->subMonths(3)->toDateString(),
            'next_calibration_due' => now()->addMonths(3)->toDateString(),
            'status' => 'operational',
            'technical_specifications' => 'Electronically driven, piston ventilator, heated patient breathing system, 3 gas rotameter tubes.',
        ]);

        $equip2 = BiomedicalEquipment::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'GE Carescape B650 Multiparameter Patient Monitor',
            'asset_code' => 'BME-MUM-MON-02',
            'category' => 'Life Support & Critical Care',
            'manufacturer' => 'GE Healthcare USA',
            'model_number' => 'B650 Intensive',
            'serial_number' => 'GE-MON-88120',
            'location_room' => 'Coronary ICU Bed 02',
            'installation_date' => '2024-06-01',
            'warranty_expiry' => '2027-05-31',
            'last_calibration_date' => now()->subMonths(1)->toDateString(),
            'next_calibration_due' => now()->addMonths(5)->toDateString(),
            'status' => 'operational',
            'technical_specifications' => 'ECG 12-lead, SpO2, invasive arterial blood pressure (IBP), EtCO2, BIS cerebral monitoring.',
        ]);

        $equip3 = BiomedicalEquipment::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'name' => 'Philips HeartStart XL+ Biphasic Defibrillator',
            'asset_code' => 'BME-BAN-DEF-03',
            'category' => 'Life Support & Critical Care',
            'manufacturer' => 'Philips Healthcare',
            'model_number' => 'HeartStart XL+ AED',
            'serial_number' => 'PHL-DEF-44109',
            'location_room' => 'Bandra ER Crash Cart',
            'installation_date' => '2023-11-10',
            'warranty_expiry' => '2028-11-09',
            'last_calibration_date' => now()->subMonths(7)->toDateString(),
            'next_calibration_due' => now()->subDays(5)->toDateString(),
            'status' => 'calibration_due',
            'technical_specifications' => 'SMART Biphasic waveform, non-invasive transcutaneous pacing, automated daily self-tests.',
        ]);

        // Sample Biomedical Ticket
        BiomedicalTicket::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'equipment_id' => $equip3->id,
            'reported_by_id' => $superAdmin->id,
            'ticket_number' => 'TKT-BME-'.date('Ymd').'-001',
            'severity' => 'major_downtime',
            'fault_description' => 'Periodic calibration due notice triggered. Energy discharge output calibration check required with defibrillator analyzer.',
            'reported_at' => now()->subDays(2),
            'technician_assigned' => 'Er. Rajesh Kulkarni (Senior Bioengineer)',
            'status' => 'open',
        ]);

        // 13. Branch-Wise Staff Attendance Logs (Today & Yesterday)
        $allEmployees = Employee::all();
        $statuses = ['present', 'present', 'present', 'late', 'on_duty'];
        $shiftsList = ['morning', 'evening', 'general'];

        foreach ($allEmployees as $idx => $emp) {
            StaffAttendance::create([
                'hospital_id' => $apollo->id,
                'branch_id' => $emp->branch_id,
                'employee_id' => $emp->id,
                'date' => now()->toDateString(),
                'clock_in_time' => Carbon::createFromTime(8, 45)->addMinutes($idx * 7)->format('H:i:s'),
                'clock_out_time' => null,
                'status' => $statuses[$idx % count($statuses)],
                'work_shift' => $shiftsList[$idx % count($shiftsList)],
                'total_hours' => 8.50,
                'remarks' => 'Biometric scan verified at branch entry terminal.',
                'is_verified' => true,
            ]);

            // Yesterday record
            StaffAttendance::create([
                'hospital_id' => $apollo->id,
                'branch_id' => $emp->branch_id,
                'employee_id' => $emp->id,
                'date' => now()->subDay()->toDateString(),
                'clock_in_time' => Carbon::createFromTime(8, 50)->addMinutes($idx * 5)->format('H:i:s'),
                'clock_out_time' => Carbon::createFromTime(17, 30)->addMinutes($idx * 4)->format('H:i:s'),
                'status' => 'present',
                'work_shift' => 'general',
                'total_hours' => 8.65,
                'remarks' => 'Duty completed and logged.',
                'is_verified' => true,
            ]);
        }

        // 14. Pharmacy Stores (Branch-Connected vs Standalone Direct to Super Admin)
        PharmacyStore::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $mainBranch->id,
            'name' => 'Apollo South Mumbai In-Hospital Pharmacy',
            'code' => 'PHARM-MUM-MAIN',
            'slug' => 'apollo-south-mumbai-pharmacy',
            'store_type' => 'in_hospital',
            'is_standalone' => false,
            'license_number' => '20B/21B-MH-102938',
            'manager_name' => 'Pooja Nair (Chief Pharmacist)',
            'phone' => '+91 22 2847 9005',
            'email' => 'pharmacy.main@apollocare.in',
            'address' => 'Ground Floor, South Mumbai Campus',
            'is_active' => true,
        ]);

        PharmacyStore::create([
            'hospital_id' => $apollo->id,
            'branch_id' => $bandraBranch->id,
            'name' => 'Apollo Bandra Specialty Outpatient Chemist',
            'code' => 'PHARM-BAN-01',
            'slug' => 'apollo-bandra-pharmacy',
            'store_type' => 'in_hospital',
            'is_standalone' => false,
            'license_number' => '20B/21B-MH-102939',
            'manager_name' => 'Ramesh Joshi (Senior Pharmacist)',
            'phone' => '+91 22 2640 1102',
            'email' => 'pharmacy.bandra@apollocare.in',
            'address' => 'OPD Block, Bandra West',
            'is_active' => true,
        ]);

        PharmacyStore::create([
            'hospital_id' => $apollo->id,
            'branch_id' => null, // STANDALONE (Directly connected to Super Admin / Central Network)
            'name' => 'Apollo 24x7 Express Retail Chemist - Nariman Point',
            'code' => 'PHARM-STA-NP',
            'slug' => 'apollo-nariman-point-standalone-pharmacy',
            'store_type' => 'standalone_retail',
            'is_standalone' => true,
            'license_number' => '20B/21B-MH-994012',
            'manager_name' => 'Kiran Deshmukh (Retail Lead)',
            'phone' => '+91 22 6690 4001',
            'email' => 'nariman.chemist@apollocare.in',
            'address' => 'Shop 4, Nariman Point Commercial Hub, Mumbai',
            'is_active' => true,
        ]);

        PharmacyStore::create([
            'hospital_id' => $apollo->id,
            'branch_id' => null, // STANDALONE (Directly connected to Super Admin / Central Network)
            'name' => 'Apollo Standalone Central Warehouse & Chemist - BKC',
            'code' => 'PHARM-STA-BKC',
            'slug' => 'apollo-bkc-standalone-pharmacy',
            'store_type' => 'standalone_retail',
            'is_standalone' => true,
            'license_number' => '20B/21B-MH-994015',
            'manager_name' => 'Sunil Gupta (Distribution Head)',
            'phone' => '+91 22 6120 5500',
            'email' => 'bkc.pharmacy@apollocare.in',
            'address' => 'Tower C, Bandra Kurla Complex, Mumbai',
            'is_active' => true,
        ]);

        // 15. Standalone Blood Bank & Diagnostic facilities
        Branch::create([
            'hospital_id' => $apollo->id,
            'name' => 'Apollo Central Apheresis & Standalone Blood Bank',
            'code' => 'BB-MUM-STA-01',
            'slug' => 'apollo-central-standalone-blood-bank',
            'facility_type' => 'blood_bank',
            'is_standalone' => true,
            'license_number' => 'BB-LIC-MH-2026-9901',
            'phone' => '+91 22 2847 9999',
            'email' => 'bloodbank.standalone@apollocare.in',
            'address' => 'Central Transfusion Complex, Dadar West',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400028',
            'manager_name' => 'Dr. Meera Sen (Transfusion Director)',
            'is_main_branch' => false,
            'is_active' => true,
        ]);

        // 16. ICU Critical Care Monitors & Code Blue Emergency Logs
        $admission1 = Admission::first();
        if ($admission1) {
            IcuMonitor::create([
                'hospital_id' => $apollo->id,
                'branch_id' => $admission1->branch_id,
                'admission_id' => $admission1->id,
                'patient_id' => $admission1->patient_id,
                'bed_id' => $admission1->bed_id,
                'attending_doctor_id' => $admission1->attending_doctor_id,
                'vent_mode' => 'SIMV + PSV',
                'fio2_percentage' => 45,
                'peep' => 8.0,
                'tidal_volume' => 480,
                'respiratory_rate' => 16,
                'spo2' => 97,
                'heart_rate' => 84,
                'bp_systolic' => 118,
                'bp_diastolic' => 76,
                'temperature_c' => 37.2,
                'gcs_score' => 14,
                'arterial_line' => true,
                'central_line' => true,
                'inotrope_infusion' => 'Noradrenaline 0.04 mcg/kg/min',
                'clinical_condition' => 'ventilator_dependent',
                'nurse_shift_notes' => 'Post CABG day 1. Ventilator weaning protocol initiated. Hemodynamics stable on low-dose inotropes.',
                'last_assessment_at' => now(),
            ]);

            CodeBlueLog::create([
                'hospital_id' => $apollo->id,
                'branch_id' => $admission1->branch_id,
                'patient_id' => $admission1->patient_id,
                'bed_id' => $admission1->bed_id,
                'initiated_by_id' => 5, // Dr Kevin D'Souza
                'event_time' => now()->subHours(18),
                'cpr_duration_mins' => 4,
                'shocks_delivered' => 1,
                'cardiac_rhythm' => 'Ventricular Fibrillation',
                'outcome' => 'resuscitated',
                'resuscitation_notes' => 'ROSC achieved post 1 shock (200J biphasic) and 1mg IV Adrenaline. Sinus rhythm restored with pulse 92 bpm.',
            ]);
        }

        TenantManager::setOverrideHospitalId(null);
    }
}
