@extends('layouts.skote')

@section('title', 'Security Audit Trail')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Central Platform Security & Audit Trail</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Governance</a></li>
                    <li class="breadcrumb-item active">Audit Trail</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="card-title mb-1">Immutable Platform Audit Event Ledger</h5>
                <p class="text-muted font-size-13 mb-0">
                    Compliant with clinical governance (PDF Page 11 & 16): Records actor, tenant boundary, action, timestamp, entity ID, and client IP.
                </p>
            </div>
            <span class="badge bg-soft-success text-success font-size-13 p-2">
                <i class="bx bx-check-shield me-1"></i> Append-Only Retention Active
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Event ID</th>
                        <th>Timestamp</th>
                        <th>Hospital Tenant</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Actor / User</th>
                        <th>IP Address</th>
                        <th>Security Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audits as $item)
                        <tr>
                            <td><span class="badge bg-light text-dark">#{{ $item->id }}</span></td>
                            <td><small class="text-muted">{{ $item->created_at->format('d M Y, H:i:s') }}</small></td>
                            <td>
                                @if($item->hospital)
                                    <span class="fw-semibold text-primary">{{ $item->hospital->name }}</span>
                                    <span class="badge bg-soft-info text-info ms-1">{{ $item->hospital->code }}</span>
                                @else
                                    <span class="badge bg-secondary">System Global</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-soft-primary text-primary font-size-12">{{ $item->module_key }}</span>
                            </td>
                            <td>
                                <span class="badge bg-soft-dark text-dark">{{ str_replace('_', ' ', $item->action) }}</span>
                            </td>
                            <td>
                                @if($item->entity_type)
                                    <code>{{ class_basename($item->entity_type) }} #{{ $item->entity_id }}</code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                {{ $item->user ? $item->user->name : 'System Automated' }}
                            </td>
                            <td><code>{{ $item->ip_address }}</code></td>
                            <td>
                                @if($item->details)
                                    <small class="text-muted">{{ json_encode($item->details) }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No audit events recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $audits->links() }}
        </div>
    </div>
</div>
@endsection
