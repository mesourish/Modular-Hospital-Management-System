@extends('layouts.skote')

@section('title', 'User Roles & Permissions Control')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Platform User Roles & Permissions Control</h4>
                <p class="text-muted font-size-13 mb-0">Platform HQ Authority • Super Admin Role Governance & Fine-Grained Module Entitlements</p>
            </div>
            <div class="page-title-right d-flex gap-2">
                <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#newUserModal">
                    <i class="bx bx-user-plus me-1"></i> Provision New Staff Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Super Admin Exclusive Authority Banner -->
<div class="alert alert-warning alert-top-border p-3 mb-4 shadow-sm">
    <div class="d-flex align-items-center">
        <div class="avatar-sm me-3">
            <span class="avatar-title bg-warning text-white rounded-circle font-size-20">
                <i class="bx bx-shield-quarter"></i>
            </span>
        </div>
        <div>
            <h5 class="text-warning-emphasis font-size-15 mb-1">Super Admin Exclusive Privilege: Role & Permissions Governance</h5>
            <p class="text-muted font-size-13 mb-0">
                In strict compliance with multi-tenant healthcare security protocols, <strong>only Super Admin</strong> can modify user roles, grant permissions, or alter access boundaries. Individual hospital staff or doctors cannot elevate their own roles or access unassigned modules (e.g. Doctors are restricted to clinical care and cannot view billing/pharmacy records).
            </p>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('superadmin.users') }}" method="GET" class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <label for="hospital_id" class="text-muted font-size-12 fw-medium text-nowrap mb-0">Filter by Hospital Network:</label>
                <select name="hospital_id" id="hospital_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 280px;">
                    <option value="all" {{ !$selectedHospitalId || $selectedHospitalId === 'all' ? 'selected' : '' }}>All Healthcare Tenants (Global View)</option>
                    @foreach($hospitals as $h)
                        <option value="{{ $h->id }}" {{ $selectedHospitalId == $h->id ? 'selected' : '' }}>
                            {{ $h->name }} ({{ $h->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <span class="badge bg-primary-subtle text-primary px-3 py-2 font-size-12">
                <i class="bx bx-user-check me-1"></i> Total Users: {{ $users->total() }}
            </span>
        </form>
    </div>
</div>

<!-- Users Table with Role & Permissions Badges -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User Name & Email</th>
                        <th>Hospital / Branch</th>
                        <th>Assigned Role</th>
                        <th>Active Permissions</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        @php
                            $roleBadge = match($u->role) {
                                'super_admin' => 'bg-warning text-dark',
                                'hospital_admin' => 'bg-primary',
                                'doctor' => 'bg-info',
                                'pharmacist' => 'bg-dark',
                                'nurse' => 'bg-success',
                                'accountant' => 'bg-secondary',
                                default => 'bg-light text-dark'
                            };
                            $roleIcon = match($u->role) {
                                'super_admin' => 'bx-crown',
                                'hospital_admin' => 'bx-buildings',
                                'doctor' => 'bx-plus-medical',
                                'pharmacist' => 'bx-capsule',
                                'nurse' => 'bx-heart',
                                'accountant' => 'bx-receipt',
                                default => 'bx-user'
                            };
                            $userPerms = !empty($u->permissions) ? $u->permissions : \App\Models\User::getDefaultPermissionsForRole($u->role);
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs me-2">
                                        <span class="avatar-title rounded-circle bg-light text-primary font-size-14 fw-bold">
                                            {{ substr($u->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="fw-bold text-dark font-size-13 d-block">{{ $u->name }}</span>
                                        <small class="text-muted font-size-11">{{ $u->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium font-size-12 text-dark">{{ $u->hospital->name ?? 'Platform HQ (Central Authority)' }}</div>
                                <small class="text-muted font-size-11">
                                    <i class="bx bx-git-branch"></i> {{ $u->branch->name ?? 'All Branches' }}
                                </small>
                            </td>
                            <td>
                                <span class="badge {{ $roleBadge }} px-2 py-1 font-size-11 text-uppercase">
                                    <i class="bx {{ $roleIcon }} me-1"></i> {{ str_replace('_', ' ', $u->role) }}
                                </span>
                            </td>
                            <td style="max-width: 320px;">
                                <div class="d-flex flex-wrap gap-1">
                                    @if($u->isSuperAdmin())
                                        <span class="badge bg-warning-subtle text-warning font-size-10">Full Platform HQ Governance</span>
                                    @else
                                        @foreach(array_slice($userPerms, 0, 3) as $perm)
                                            <span class="badge bg-light text-dark font-size-10 border">
                                                {{ $permissionsCatalog[$perm]['name'] ?? $perm }}
                                            </span>
                                        @endforeach
                                        @if(count($userPerms) > 3)
                                            <span class="badge bg-info-subtle text-info font-size-10">+{{ count($userPerms) - 3 }} more</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($u->is_active)
                                    <span class="badge bg-success font-size-11"><i class="bx bx-check-circle"></i> Active</span>
                                @else
                                    <span class="badge bg-danger font-size-11"><i class="bx bx-x-circle"></i> Inactive</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal-{{ $u->id }}">
                                    <i class="bx bx-cog me-1"></i> Modify Access
                                </button>
                            </td>
                        </tr>

                        <!-- Modal: Modify Role & Permissions -->
                        <div class="modal fade" id="editUserModal-{{ $u->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title text-white">
                                            <i class="bx bx-shield me-1"></i> Edit Role & Permissions: {{ $u->name }}
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('superadmin.users.update_role', $u->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body p-4">
                                            <div class="row g-3 mb-4 pb-3 border-bottom">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Assigned System Role <span class="text-danger">*</span></label>
                                                    <select name="role" class="form-select" required>
                                                        @foreach($roles as $rKey => $rLabel)
                                                            <option value="{{ $rKey }}" {{ $u->role === $rKey ? 'selected' : '' }}>
                                                                {{ $rLabel }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted font-size-11 d-block mt-1">Changing role automatically shifts navbar/sidebar views.</small>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-medium">Branch Assignment</label>
                                                    <select name="branch_id" class="form-select">
                                                        <option value="">-- All Hospital Branches (HQ Wide) --</option>
                                                        @if($u->hospital)
                                                            @foreach($u->hospital->branches as $br)
                                                                <option value="{{ $br->id }}" {{ $u->branch_id == $br->id ? 'selected' : '' }}>
                                                                    {{ $br->name }} ({{ $br->city }})
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active-{{ $u->id }}" {{ $u->is_active ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-medium" for="active-{{ $u->id }}">
                                                            Account Status: Active (Can sign in to portal)
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <h6 class="fw-bold text-dark mb-2">Granular Module Permissions:</h6>
                                            <p class="text-muted font-size-12 mb-3">Super Admin can customize exact feature boundaries for this user:</p>

                                            <div class="row g-3">
                                                @foreach($permissionsCatalog as $pKey => $pMeta)
                                                    @php
                                                        $isChecked = in_array($pKey, $userPerms);
                                                    @endphp
                                                    <div class="col-md-6">
                                                        <div class="border rounded p-2 bg-light-subtle h-100">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $pKey }}" id="perm-{{ $u->id }}-{{ $pKey }}" {{ $isChecked ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-semibold text-dark font-size-13" for="perm-{{ $u->id }}-{{ $pKey }}">
                                                                    {{ $pMeta['name'] }}
                                                                </label>
                                                            </div>
                                                            <div class="text-muted font-size-11 ps-4 mt-1">
                                                                {{ $pMeta['desc'] }} • <span class="badge bg-secondary-subtle text-secondary font-size-10">{{ $pMeta['group'] }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i> Save Role & Permissions
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- Modal: Provision New User Account -->
<div class="modal fade" id="newUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-user-plus me-1"></i> Provision New Staff User Account
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('superadmin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Dr. Ramesh Gupta" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Official Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. ramesh.cardio@apollocare.in" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Initial Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" value="password" required>
                            <small class="text-muted font-size-11">Default temporary password is <code>password</code></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Select Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                @foreach($roles as $rKey => $rLabel)
                                    <option value="{{ $rKey }}">{{ $rLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Hospital Tenant</label>
                            <select name="hospital_id" class="form-select">
                                <option value="">-- Platform HQ / None --</option>
                                @foreach($hospitals as $h)
                                    <option value="{{ $h->id }}">{{ $h->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Contact Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+91 98200 44556">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-check-circle me-1"></i> Provision Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
