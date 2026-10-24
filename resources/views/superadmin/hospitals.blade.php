@extends('layouts.skote')

@section('title', 'Hospital Tenants & Extensions')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Hospital Tenancy & Module Extensibility</h4>
            <div class="page-title-right">
                <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#newHospitalModal">
                    <i class="bx bx-plus font-size-16 align-middle me-1"></i> Onboard New Hospital
                </button>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info border-0 shadow-sm mb-4">
    <div class="d-flex align-items-center">
        <i class="bx bx-info-circle font-size-24 me-3 text-info"></i>
        <div>
            <h6 class="mb-1 text-info fw-bold">Extension Basis Architecture (PDF Page 7 & 10)</h6>
            <p class="mb-0 text-muted font-size-13">
                Each hospital operates with its own custom set of activated modular extensions. You can toggle any module below.
                When a module is disabled, the platform <strong>strictly blocks backend execution</strong> and API endpoints for that hospital.
            </p>
        </div>
    </div>
</div>

<div class="row">
    @foreach($hospitals as $h)
        @php
            $enabledModules = $h->modules->where('is_enabled', true)->pluck('module_key')->toArray();
        @endphp
        <div class="col-xl-6">
            <div class="card border">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="card-title text-primary mb-0 font-size-16">{{ $h->name }}</h5>
                        <small class="text-muted">{{ $h->code }} • {{ ucfirst(str_replace('_', ' ', $h->type)) }} • {{ $h->city ?? 'Central' }}</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-soft-info text-info">{{ $h->subscription_plan }}</span>
                        <form action="{{ route('superadmin.switch_hospital') }}" method="POST">
                            @csrf
                            <input type="hidden" name="hospital_id" value="{{ $h->id }}">
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-show me-1"></i> Open Workspace
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <h6 class="font-size-13 text-muted text-uppercase fw-semibold mb-3">Modular Extensions Status:</h6>
                    
                    <div class="table-responsive">
                        <table class="table table-sm align-middle table-nowrap mb-0">
                            <tbody>
                                @foreach($availableModules as $modKey => $mod)
                                    @php
                                        $isActive = in_array($modKey, $enabledModules);
                                    @endphp
                                    <tr>
                                        <td style="width: 40px;">
                                            <span class="avatar-title rounded bg-light text-primary font-size-16" style="width: 32px; height: 32px;">
                                                <i class="bx {{ $mod['icon'] }}"></i>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark font-size-13">{{ $mod['name'] }}</div>
                                            <small class="text-muted">{{ $mod['category'] }}</small>
                                        </td>
                                        <td class="text-center" style="width: 110px;">
                                            @if($isActive)
                                                <span class="badge bg-soft-success text-success font-size-12 px-2 py-1">
                                                    <i class="bx bx-check me-1"></i> Active
                                                </span>
                                            @else
                                                <span class="badge bg-soft-danger text-danger font-size-12 px-2 py-1">
                                                    <i class="bx bx-x me-1"></i> Disabled
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end" style="width: 120px;">
                                            <form action="{{ route('superadmin.toggle_module', $h->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="module_key" value="{{ $modKey }}">
                                                <button type="submit" class="btn btn-sm {{ $isActive ? 'btn-outline-danger' : 'btn-outline-success' }} px-3">
                                                    {{ $isActive ? 'Deactivate' : 'Enable' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Modal: Onboard New Hospital -->
<div class="modal fade" id="newHospitalModal" tabindex="-1" aria-labelledby="newHospitalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('superadmin.hospitals.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="newHospitalModalLabel">Onboard New Hospital Tenant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Hospital Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Apex Health Clinic" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hospital Code (Identifier) <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. AHC-03" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hospital Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="clinic">Outpatient Clinic</option>
                                <option value="general_hospital">General Hospital</option>
                                <option value="multi_specialty">Multi-Specialty Hospital</option>
                                <option value="diagnostic_center">Diagnostic Center</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subscription Tier <span class="text-danger">*</span></label>
                            <select name="subscription_plan" class="form-select" required>
                                <option value="outpatient_starter">Outpatient Starter</option>
                                <option value="clinical_pro">Clinical Pro</option>
                                <option value="enterprise_unlimited">Enterprise Unlimited</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Official Email</label>
                            <input type="email" name="email" class="form-control" placeholder="admin@hospital.org">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+91 22 1234 5678">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">City / Region</label>
                            <input type="text" name="city" class="form-control" placeholder="e.g. Mumbai, New Delhi, Bengaluru">
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2">Select Initial Entitled Extensions:</h6>
                            <div class="row g-2 mt-1">
                                @foreach($availableModules as $key => $manifest)
                                    <div class="col-md-6">
                                        <div class="form-check border rounded p-2 ps-4">
                                            <input class="form-check-input" type="checkbox" name="modules[]" value="{{ $key }}" id="mod_{{ $key }}" {{ in_array($key, ['admissions', 'opd', 'billing']) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-medium" for="mod_{{ $key }}">
                                                {{ $manifest['name'] }}
                                                <small class="text-muted d-block font-size-11">{{ $manifest['category'] }}</small>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Provision Hospital Tenant</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
