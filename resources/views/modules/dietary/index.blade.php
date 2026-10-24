@extends('layouts.skote')

@section('title', 'Inpatient Dietary & Nutritional Care')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Inpatient Clinical Dietary & Nutrition Care</h4>
                <div class="text-muted font-size-13 mt-1">
                    Outlet: <span class="badge bg-primary-subtle text-primary fw-bold">{{ $activeBranch->name ?? 'Main Hospital Campus' }}</span>
                    @if($activeBranch && $activeBranch->slug)
                        <span class="badge bg-light text-secondary border font-mono">/outlet/{{ $activeBranch->slug }}/dietary</span>
                    @endif
                    • Diabetic, Renal, Low Sodium & Post-Op Nutrition Regimens
                </div>
            </div>
            <div class="page-title-right">
                <button type="button" class="btn btn-sm btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#newDietOrderModal">
                    <i class="bx bx-plus-circle me-1"></i> Prescribe Inpatient Meal
                </button>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bx bx-check-double me-2 font-size-16 align-middle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Dietary Metrics -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Total Meals Prescribed</p>
                        <h4 class="mb-0 font-mono font-size-22 text-dark">{{ $totalOrders }}</h4>
                        <div class="text-muted font-size-11 mt-1">Inpatient Clinical Diets</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-primary-subtle text-primary align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-primary font-size-18">
                            <i class="bx bx-bowl-hot"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Delivered to Bedside</p>
                        <h4 class="mb-0 font-mono font-size-22 text-success">{{ $deliveredCount }}</h4>
                        <div class="text-success font-size-11 mt-1">Confirmed Patient Handover</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-success-subtle text-success align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-success font-size-18">
                            <i class="bx bx-check-double"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Kitchen Cooking Queue</p>
                        <h4 class="mb-0 font-mono font-size-22 text-warning">{{ $cookingCount }}</h4>
                        <div class="text-warning font-size-11 mt-1">In Preparation</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-warning-subtle text-warning align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-warning font-size-18">
                            <i class="bx bx-restaurant"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Doctor Prescribed</p>
                        <h4 class="mb-0 font-mono font-size-22 text-info">{{ $prescribedCount }}</h4>
                        <div class="text-info font-size-11 mt-1">Awaiting Kitchen Intake</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-info-subtle text-info align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-info font-size-18">
                            <i class="bx bx-file"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Meal Orders Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 font-size-15"><i class="bx bx-dish text-primary me-1"></i> Patient Meal Schedules & Diet Specifications</h5>
        <span class="badge bg-light text-secondary border font-mono">Today's Meal Board</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 font-size-13">
                <thead class="table-light">
                    <tr>
                        <th>Order Date</th>
                        <th>Patient & Bed</th>
                        <th>Dietary Regimen</th>
                        <th>Meal Slot</th>
                        <th>Calorie Target</th>
                        <th>Kitchen Instructions</th>
                        <th>Status</th>
                        <th class="text-end">Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $ord)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($ord->order_date)->format('d M Y') }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $ord->patient->full_name ?? 'Inpatient' }}</div>
                                <span class="badge bg-primary-subtle text-primary font-mono">{{ $ord->patient->mrn ?? '' }}</span>
                                <small class="text-muted ms-1">{{ $ord->room_bed_number ?: 'Ward Bed' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info border fw-semibold">
                                    {{ $ord->diet_type }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark font-size-11 text-uppercase">
                                    {{ str_replace('_', ' ', $ord->meal_slot) }}
                                </span>
                            </td>
                            <td class="font-mono text-dark fw-bold">{{ $ord->calorie_target }}</td>
                            <td class="font-size-12 text-muted">
                                {{ $ord->kitchen_instructions ?: 'Standard therapeutic meal guidelines' }}
                            </td>
                            <td>
                                <span class="badge {{ $ord->status === 'delivered' ? 'bg-success' : ($ord->status === 'cooking' ? 'bg-warning' : 'bg-primary') }} text-uppercase font-size-11">
                                    {{ $ord->status }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if($ord->status === 'prescribed')
                                    <form action="{{ route('dietary.orders.status', $ord->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="cooking">
                                        <button type="submit" class="btn btn-xs btn-outline-warning py-0 px-2 font-size-11">Start Cooking</button>
                                    </form>
                                @elseif($ord->status === 'cooking')
                                    <form action="{{ route('dietary.orders.status', $ord->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="delivered">
                                        <button type="submit" class="btn btn-xs btn-outline-success py-0 px-2 font-size-11">Mark Delivered</button>
                                    </form>
                                @else
                                    <span class="text-muted font-size-11">Complete</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No dietary orders prescribed. Click "Prescribe Inpatient Meal" to order.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Prescribe Meal -->
<div class="modal fade" id="newDietOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('dietary.orders.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-bowl-hot me-2"></i> Prescribe Inpatient Clinical Diet</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Patient (Inpatient ADT) <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select font-size-13" required onchange="document.getElementById('dietBed').value = this.options[this.selectedIndex].dataset.bed || ''">
                                <option value="">-- Select Admitted Patient --</option>
                                @foreach($patients as $pat)
                                    <option value="{{ $pat->id }}" data-bed="Ward Bed">
                                        {{ $pat->mrn }} - {{ $pat->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Ward & Bed Number</label>
                            <input type="text" name="room_bed_number" id="dietBed" class="form-control" value="ICU Bed 04 / Male Ward" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Clinical Diet Type <span class="text-danger">*</span></label>
                            <select name="diet_type" class="form-select" required>
                                <option value="Diabetic & Low Sodium">Diabetic & Low Sodium (Sugar-Free, Salt-Restricted)</option>
                                <option value="Renal Diet (Low Protein / Potassium)">Renal Diet (Low Protein, Low Potassium)</option>
                                <option value="Post-Op Clear Liquid Diet">Post-Op Clear Liquid Diet (Post-General Anesthesia)</option>
                                <option value="High Protein Recovery Diet">High Protein Recovery Diet (Burn / Surgery Repair)</option>
                                <option value="Cardiac Healthy Diet">Cardiac Healthy Diet (Low Cholesterol)</option>
                                <option value="Full Soft Diet">Full Soft Mechanical Diet</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label font-size-12 fw-semibold">Meal Slot <span class="text-danger">*</span></label>
                            <select name="meal_slot" class="form-select" required>
                                <option value="breakfast">Breakfast</option>
                                <option value="lunch" selected>Lunch</option>
                                <option value="evening_snack">Evening Snack</option>
                                <option value="dinner">Dinner</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label font-size-12 fw-semibold">Order Date <span class="text-danger">*</span></label>
                            <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Target Caloric Intake</label>
                            <input type="text" name="calorie_target" class="form-control" value="1800 kcal">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Allergies & Restrictions</label>
                            <input type="text" name="allergies_and_restrictions" class="form-control" placeholder="e.g. Gluten intolerant, Lactose-free, No nuts">
                        </div>

                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold">Kitchen Preparation Instructions</label>
                            <textarea name="kitchen_instructions" class="form-control" rows="2" placeholder="e.g. Puree consistency, serve lukewarm, no added white sugar or salt..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Prescribe Meal Order</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
