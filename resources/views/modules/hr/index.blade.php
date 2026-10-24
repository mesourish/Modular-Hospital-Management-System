@extends('layouts.skote')

@section('title', 'Employee Records & Salaries')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Hospital Human Resources & Payroll</h4>
                <p class="text-muted font-size-13 mb-0">Employee Master, Medical Licenses, Designations, Salary Structures & Monthly Payslips</p>
            </div>
            <div class="page-title-right d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newEmpModal">
                    <i class="bx bx-user-plus me-1"></i> Add Employee Record
                </button>
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#payrollModal">
                    <i class="bx bx-calculator me-1"></i> Process Payroll Batch
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Employee Records List -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Employee Master Directory</h4>
                <div class="table-responsive">
                    <table class="table align-middle table-hover table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code / Name</th>
                                <th>Department</th>
                                <th>Designation / Job</th>
                                <th>Medical License</th>
                                <th>Base Salary</th>
                                <th>Net Salary</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $emp)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark font-size-13">{{ $emp->fullName }}</div>
                                        <small class="text-muted"><code>{{ $emp->employee_code }}</code> • {{ ucfirst(str_replace('_', ' ', $emp->employment_type)) }}</small>
                                    </td>
                                    <td><span class="badge bg-soft-info text-info">{{ $emp->department->name ?? 'Clinical' }}</span></td>
                                    <td>
                                        <div class="font-size-13 text-dark">{{ $emp->designation->title ?? 'Staff' }}</div>
                                        <small class="text-muted">{{ $emp->qualification }}</small>
                                    </td>
                                    <td>
                                        @if($emp->medical_license_no)
                                            <span class="badge bg-soft-success text-success">{{ $emp->medical_license_no }}</span>
                                            <small class="d-block text-muted">Exp: {{ $emp->license_expiry_date ? \Carbon\Carbon::parse($emp->license_expiry_date)->format('M Y') : 'N/A' }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>Rs {{ number_format($emp->base_salary, 2) }}</td>
                                    <td>
                                        <strong class="text-success">
                                            Rs {{ number_format($emp->salaryStructure->net_salary ?? $emp->base_salary, 2) }}
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ ucfirst($emp->status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No employees registered.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Monthly Payroll Runs -->
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Processed Monthly Payrolls & Payslips</h4>
                <div class="table-responsive">
                    <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                        <thead class="table-light">
                            <tr>
                                <th>Payslip #</th>
                                <th>Staff Member</th>
                                <th>Period</th>
                                <th>Basic Pay</th>
                                <th>Allowances</th>
                                <th>Deductions (PF/Tax)</th>
                                <th>Net Disbursed</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payrolls as $pay)
                                <tr>
                                    <td><span class="badge bg-soft-primary text-primary">{{ $pay->payslip_number }}</span></td>
                                    <td>{{ $pay->employee->fullName }}</td>
                                    <td><strong>{{ $pay->payroll_month }} {{ $pay->payroll_year }}</strong></td>
                                    <td>Rs {{ number_format($pay->basic_salary, 2) }}</td>
                                    <td><span class="text-success">+ Rs {{ number_format($pay->total_allowances, 2) }}</span></td>
                                    <td><span class="text-danger">- Rs {{ number_format($pay->total_deductions, 2) }}</span></td>
                                    <td><strong class="text-primary font-size-14">Rs {{ number_format($pay->net_salary, 2) }}</strong></td>
                                    <td><span class="badge bg-success">{{ ucfirst($pay->payment_status) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-3 text-muted">No payroll runs processed yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Salary Structure & Designations Breakdown -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Active Job Designations</h4>
                <div class="list-group list-group-flush">
                    @forelse($designations as $des)
                        <div class="list-group-item px-0 py-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 font-size-13 text-dark">{{ $des->title }}</h6>
                                <span class="badge bg-soft-secondary text-secondary">{{ $des->department->name ?? 'Dept' }}</span>
                            </div>
                            <small class="text-muted d-block">
                                Salary Band: Rs {{ number_format($des->min_salary, 0) }} - Rs {{ number_format($des->max_salary, 0) }}
                            </small>
                        </div>
                    @empty
                        <div class="text-muted text-center py-3">No designations created.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card bg-soft-info border-info border">
            <div class="card-body">
                <h5 class="text-info font-size-14 fw-bold mb-2"><i class="bx bx-check-shield me-1"></i> Salary Structure Automation</h5>
                <p class="font-size-12 text-muted mb-0">
                    When employees are created, statutory allowances (Basic 55%, HRA 25%, Medical 10%, Conveyance 10%) and deductions (Provident Fund 12%, TDS 8%) are automatically computed into an auditable salary structure ledger.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Employee Record -->
<div class="modal fade" id="newEmpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('hr.employees.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create Hospital Employee & Job Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required placeholder="e.g. Ramesh">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required placeholder="e.g. Kulkarni">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select" required>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Job Designation <span class="text-danger">*</span></label>
                            <select name="designation_id" class="form-select" required>
                                @foreach($designations as $des)
                                    <option value="{{ $des->id }}">{{ $des->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Joining <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_joining" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Employment Type <span class="text-danger">*</span></label>
                            <select name="employment_type" class="form-select" required>
                                <option value="full_time">Full Time</option>
                                <option value="part_time">Part Time</option>
                                <option value="visiting_consultant">Visiting Consultant</option>
                                <option value="contract">Contract</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="staff@hospital.org">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+91 98200 00000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Qualifications</label>
                            <input type="text" name="qualification" class="form-control" placeholder="e.g. MBBS, MD, B.Sc Nursing">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Medical Council License #</label>
                            <input type="text" name="medical_license_no" class="form-control" placeholder="e.g. MCI-MH-99210">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Base Monthly Salary (Rs) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="base_salary" class="form-control" value="85000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Link System User Account</label>
                            <select name="user_id" class="form-select">
                                <option value="">None / Standalone Record</option>
                                @foreach($usersWithoutEmployee as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Employee & Auto-Calculate Salary</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Process Payroll Batch -->
<div class="modal fade" id="payrollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('hr.payroll.generate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Run Monthly Payroll Batch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted font-size-13">
                        This batch engine calculates gross pay, allowances, PF, and statutory deductions for all active hospital employees and produces verified payslips.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Month <span class="text-danger">*</span></label>
                            <select name="month" class="form-select" required>
                                <option value="October" selected>October</option>
                                <option value="November">November</option>
                                <option value="December">December</option>
                                <option value="January">January</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Year <span class="text-danger">*</span></label>
                            <input type="number" name="year" class="form-control" value="2026" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bx bx-check me-1"></i> Generate & Approve Payslips</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
