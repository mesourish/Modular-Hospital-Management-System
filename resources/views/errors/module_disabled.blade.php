@extends('layouts.skote')

@section('title', 'Module Inactive')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-lg">
            <div class="card-body p-5 text-center">
                <div class="avatar-lg mx-auto mb-4">
                    <div class="avatar-title bg-soft-warning text-warning rounded-circle font-size-36">
                        <i class="bx bx-lock-alt"></i>
                    </div>
                </div>

                <h3 class="fw-bold text-dark mb-2">Extension Not Activated</h3>
                <p class="text-muted font-size-15 mb-4">
                    The extension <span class="badge bg-soft-danger text-danger font-size-14">{{ $moduleName }}</span> (<code>{{ $moduleKey }}</code>) is currently <strong>disabled or not licensed</strong> for hospital:
                </p>

                <div class="p-3 bg-light rounded text-start mb-4 border">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Target Hospital:</span>
                        <span class="fw-bold text-dark">{{ $hospital->name ?? 'Unknown Hospital' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Hospital Code:</span>
                        <span class="badge bg-primary">{{ $hospital->code ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Subscription Plan:</span>
                        <span class="text-info text-capitalize">{{ str_replace('_', ' ', $hospital->subscription_plan ?? 'Standard') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Security Enforcement:</span>
                        <span class="badge bg-soft-success text-success"><i class="bx bx-check"></i> Strict Backend Gate Active</span>
                    </div>
                </div>

                <div class="alert alert-info text-start font-size-13 mb-4">
                    <i class="bx bx-info-circle me-1"></i>
                    <strong>Architectural Note:</strong> As per the system blueprint, hiding navigation menus alone is insufficient. Backend middleware (<code>EnsureModuleEnabled</code>) strictly blocks unauthorized API and web execution to prevent cross-tenant and unentitled access.
                </div>

                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('hospital.dashboard') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Return to Hospital Dashboard
                    </a>
                    
                    @if($hospital)
                        <form action="{{ route('superadmin.toggle_module', $hospital->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="module_key" value="{{ $moduleKey }}">
                            <button type="submit" class="btn btn-success">
                                <i class="bx bx-check-shield me-1"></i> Entitle & Activate as Super Admin
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
