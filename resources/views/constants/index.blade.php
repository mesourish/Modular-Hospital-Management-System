@extends('layouts.skote')

@section('title', 'Master Constants & Clinical Departments')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">
                <i class="bx bx-slider-alt text-primary me-1"></i> Master Constants & Clinical Taxonomy
            </h4>
            <div class="page-title-right d-flex gap-2">
                @if($user->isSuperAdmin() || $user->role === 'hospital_admin')
                <form action="{{ route('constants.seed_defaults') }}" method="POST" onsubmit="return confirm('Populate realistic clinical departments, sub-departments, designations and constants? Existing items will not be overwritten.');">
                    @csrf
                    <input type="hidden" name="hospital_id" value="{{ $selectedHospitalId }}">
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="bx bx-magic-wand me-1"></i> Pre-populate Realistic Standards
                    </button>
                </form>
                @endif
                <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-cog me-1"></i> System Settings
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Scope Selector -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('constants.index') }}" method="GET" class="row align-items-center g-3">
            <div class="col-md-4">
                <label class="form-label font-size-12 fw-bold text-muted text-uppercase mb-1">Hospital Scope:</label>
                @if($user->isSuperAdmin())
                    <select name="hospital_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($hospitals as $h)
                            <option value="{{ $h->id }}" {{ $selectedHospitalId == $h->id ? 'selected' : '' }}>
                                {{ $h->name }} ({{ $h->code }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $hospitals->first()->name ?? 'Current Hospital' }}" readonly>
                    <input type="hidden" name="hospital_id" value="{{ $selectedHospitalId }}">
                @endif
            </div>

            <div class="col-md-4">
                <label class="form-label font-size-12 fw-bold text-muted text-uppercase mb-1">Branch Scope:</label>
                @if($user->isBranchAdmin() && $user->branch_id)
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $branches->where('id', $selectedBranchId)->first()->name ?? 'My Branch' }}" readonly>
                    <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                @else
                    <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">🏢 All Branches (Hospital-Wide)</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->city }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="col-md-4 text-md-end pt-3">
                <span class="badge bg-primary-subtle text-primary font-size-12 px-3 py-2">
                    <i class="bx bx-check-shield me-1"></i> Master Constants Synchronized
                </span>
            </div>
        </form>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-departments" role="tab">
            <i class="bx bx-building-house me-1"></i> Departments ({{ $departments->count() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-subdepartments" role="tab">
            <i class="bx bx-git-branch me-1"></i> Sub-Departments ({{ $subDepartments->count() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-designations" role="tab">
            <i class="bx bx-id-card me-1"></i> Designations ({{ $designations->count() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-constants" role="tab">
            <i class="bx bx-list-check me-1"></i> Clinical & System Constants ({{ $constants->flatten()->count() }})
        </a>
    </li>
</ul>

<div class="tab-content">
    <!-- Tab 1: Departments -->
    <div class="tab-pane active" id="tab-departments" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0 fw-bold">Clinical & Administrative Departments</h5>
                    <small class="text-muted">Master organizational divisions across outpatient, inpatient, diagnostic, and emergency disciplines.</small>
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                    <i class="bx bx-plus me-1"></i> Add Department
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Department Name</th>
                                <th>Category / Type</th>
                                <th>Sub-Units</th>
                                <th>Branch Assignment</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departments as $dept)
                            <tr>
                                <td><span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $dept->code }}</span></td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $dept->name }}</span>
                                    @if($dept->description)
                                        <p class="font-size-11 text-muted mb-0">{{ Str::limit($dept->description, 80) }}</p>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $typeColors = [
                                            'clinical' => 'primary',
                                            'diagnostic' => 'info',
                                            'pharmacy' => 'success',
                                            'support' => 'warning',
                                            'administrative' => 'secondary'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $typeColors[$dept->type] ?? 'primary' }}-subtle text-{{ $typeColors[$dept->type] ?? 'primary' }} text-capitalize">
                                        {{ $dept->type }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $dept->subDepartments->count() }} Sub-Units
                                    </span>
                                </td>
                                <td>
                                    @if($dept->branch)
                                        <span class="badge bg-info-subtle text-info">{{ $dept->branch->name }}</span>
                                    @else
                                        <span class="badge bg-light text-muted">All Branches</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No departments registered yet. Click "Pre-populate Realistic Standards" to seed clinical departments.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 2: Sub-Departments -->
    <div class="tab-pane" id="tab-subdepartments" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0 fw-bold">Sub-Departments & Specialized Clinical Units</h5>
                    <small class="text-muted">High-dependency units, cath labs, imaging suites, triage bays, and recovery sections.</small>
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSubDepartmentModal">
                    <i class="bx bx-plus me-1"></i> Add Sub-Department
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Unit Code</th>
                                <th>Sub-Department / Specialized Unit</th>
                                <th>Parent Department</th>
                                <th>Branch</th>
                                <th>Description</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subDepartments as $sub)
                            <tr>
                                <td><span class="badge bg-info-subtle text-info font-monospace">{{ $sub->code }}</span></td>
                                <td class="fw-bold text-dark">{{ $sub->name }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $sub->department->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ $sub->branch->name ?? 'All Branches' }}</td>
                                <td><small class="text-muted">{{ $sub->description ?? '—' }}</small></td>
                                <td><span class="badge bg-success-subtle text-success">Active</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No sub-departments defined yet. Click "Pre-populate Realistic Standards" or add a new unit.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 3: Designations -->
    <div class="tab-pane" id="tab-designations" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0 fw-bold">Healthcare Professional Designations</h5>
                    <small class="text-muted">Standard clinical credentialing hierarchy, payroll bands, and job roles.</small>
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDesignationModal">
                    <i class="bx bx-plus me-1"></i> Add Designation
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Designation Title</th>
                                <th>Primary Department</th>
                                <th>Salary Compensation Band</th>
                                <th>Job Description</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($designations as $desig)
                            <tr>
                                <td class="fw-bold text-dark">{{ $desig->title }}</td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        {{ $desig->department->name ?? 'General / Cross-Department' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="font-monospace text-dark font-size-13">
                                        ₹{{ number_format($desig->min_salary) }} - ₹{{ number_format($desig->max_salary) }}
                                    </span>
                                </td>
                                <td><small class="text-muted">{{ Str::limit($desig->description, 70) ?? '—' }}</small></td>
                                <td><span class="badge bg-success-subtle text-success">Active</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No designations found. Pre-populate realistic healthcare standards to load consultant, nursing, and pilot roles.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 4: Realistic System Constants -->
    <div class="tab-pane" id="tab-constants" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-0">Clinical Constants & System Taxonomies</h5>
                <small class="text-muted">Triage priority levels, ambulance capabilities, shift schedules, payment modes, and ward types.</small>
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addConstantModal">
                <i class="bx bx-plus me-1"></i> Add Custom Constant
            </button>
        </div>

        <div class="row g-4">
            @php
                $categoryTitles = [
                    'triage_level' => ['title' => 'Emergency Triage Severity Levels (ESI)', 'icon' => 'bx-pulse', 'color' => 'danger'],
                    'ambulance_type' => ['title' => 'Ambulance Fleet Capability Levels', 'icon' => 'bx-car', 'color' => 'warning'],
                    'shift_timing' => ['title' => 'Hospital Operational Duty Shifts', 'icon' => 'bx-time-five', 'color' => 'info'],
                    'payment_mode' => ['title' => 'Billing & Payment Channels', 'icon' => 'bx-credit-card', 'color' => 'success'],
                    'ward_type' => ['title' => 'Inpatient Ward & Bed Accommodations', 'icon' => 'bx-bed', 'color' => 'primary'],
                ];
            @endphp

            @forelse($constants as $catKey => $catItems)
                @php
                    $catMeta = $categoryTitles[$catKey] ?? ['title' => ucwords(str_replace('_', ' ', $catKey)), 'icon' => 'bx-list-check', 'color' => 'secondary'];
                @endphp
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0 fw-bold text-dark">
                                <i class="bx {{ $catMeta['icon'] }} text-{{ $catMeta['color'] }} me-1"></i> {{ $catMeta['title'] }}
                            </h6>
                            <span class="badge bg-light text-muted">{{ $catItems->count() }} Rules</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush font-size-13">
                                @foreach($catItems as $item)
                                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge bg-{{ $item->badge_color ?? 'primary' }}-subtle text-{{ $item->badge_color ?? 'primary' }} fw-bold">
                                                {{ $item->code }}
                                            </span>
                                            <span class="fw-bold text-dark">{{ $item->name }}</span>
                                            @if(!$item->is_active)
                                                <span class="badge bg-secondary">Disabled</span>
                                            @endif
                                        </div>
                                        <small class="text-muted d-block">{{ $item->description }}</small>
                                        <small class="text-muted font-monospace font-size-11">Key: <code>{{ $item->key }}</code></small>
                                    </div>
                                    <form action="{{ route('constants.toggle', $item->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $item->is_active ? 'btn-outline-success' : 'btn-outline-secondary' }}" title="Toggle Active Status">
                                            <i class="bx {{ $item->is_active ? 'bx-check-circle' : 'bx-x-circle' }}"></i>
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="p-4 bg-light rounded text-muted">
                        <i class="bx bx-slider font-size-36 mb-2 text-primary"></i>
                        <h5>No Master Constants Initialized Yet</h5>
                        <p class="mb-3">Click the button below to initialize all realistic emergency triage, ambulance capabilities, duty shifts, payment modes, and ward types.</p>
                        <form action="{{ route('constants.seed_defaults') }}" method="POST">
                            @csrf
                            <input type="hidden" name="hospital_id" value="{{ $selectedHospitalId }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-magic-wand me-1"></i> Pre-populate Realistic Standards Now
                            </button>
                        </form>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal: Add Department -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold"><i class="bx bx-plus me-1"></i> Register New Department</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('constants.departments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="hospital_id" value="{{ $selectedHospitalId }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Department Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Neurology & Neurosurgery" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Department Code</label>
                            <input type="text" name="code" class="form-control text-uppercase font-monospace" placeholder="e.g. NEURO" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Department Type</label>
                            <select name="type" class="form-select" required>
                                <option value="clinical">Clinical</option>
                                <option value="diagnostic">Diagnostic / Imaging</option>
                                <option value="pharmacy">Pharmacy & Dispensary</option>
                                <option value="support">Support & Logistics</option>
                                <option value="administrative">Administrative & Accounts</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Branch (Optional)</label>
                        <select name="branch_id" class="form-select">
                            <option value="">🏢 Available across all branches</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Description & Scope</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief clinical scope and services provided..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Create Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Sub-Department -->
<div class="modal fade" id="addSubDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold"><i class="bx bx-plus me-1"></i> Add Specialized Sub-Unit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('constants.sub_departments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="hospital_id" value="{{ $selectedHospitalId }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Parent Department</label>
                        <select name="department_id" class="form-select" required>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Sub-Unit Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Interventional Cath Lab Suite" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Sub-Unit Code</label>
                            <input type="text" name="code" class="form-control text-uppercase font-monospace" placeholder="e.g. CATH_LAB" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Branch</label>
                            <select name="branch_id" class="form-select">
                                <option value="">All Branches</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Clinical Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Clinical equipment and procedures handled..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Save Sub-Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Designation -->
<div class="modal fade" id="addDesignationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold"><i class="bx bx-plus me-1"></i> Add Professional Designation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('constants.designations.store') }}" method="POST">
                @csrf
                <input type="hidden" name="hospital_id" value="{{ $selectedHospitalId }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Designation Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Senior Consultant Cardiologist" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Associated Department (Optional)</label>
                        <select name="department_id" class="form-select">
                            <option value="">Cross-Department / General Staff</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Min Salary Band (₹)</label>
                            <input type="number" name="min_salary" class="form-control" placeholder="100000" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Max Salary Band (₹)</label>
                            <input type="number" name="max_salary" class="form-control" placeholder="250000" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Job Description / Credentialing Criteria</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Qualifications, licensure, and duties..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Save Designation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Constant -->
<div class="modal fade" id="addConstantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold"><i class="bx bx-plus me-1"></i> Add Custom System Constant</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('constants.system_constants.store') }}" method="POST">
                @csrf
                <input type="hidden" name="hospital_id" value="{{ $selectedHospitalId }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Constant Category</label>
                        <select name="category" class="form-select" required>
                            <option value="triage_level">Triage Severity Level</option>
                            <option value="ambulance_type">Ambulance Fleet Type</option>
                            <option value="shift_timing">Hospital Duty Shift</option>
                            <option value="payment_mode">Payment Mode</option>
                            <option value="ward_type">Ward / Bed Category</option>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">System Key</label>
                            <input type="text" name="key" class="form-control font-monospace" placeholder="e.g. level_1_resuscitation" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Short Code</label>
                            <input type="text" name="code" class="form-control text-uppercase font-monospace" placeholder="e.g. L1_RED" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Display Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Level 1 - Resuscitation (Immediate)" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Badge Color</label>
                            <select name="badge_color" class="form-select">
                                <option value="primary">Primary (Blue)</option>
                                <option value="danger">Danger (Red)</option>
                                <option value="warning">Warning (Orange)</option>
                                <option value="success">Success (Green)</option>
                                <option value="info">Info (Teal)</option>
                                <option value="secondary">Secondary (Gray)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Clinical Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Clinical context, response time target..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Save Master Constant</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
