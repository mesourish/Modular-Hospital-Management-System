@extends('layouts.skote')

@section('title', 'Blood Bank & Transfusion Service')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Blood Bank & Transfusion Medicine</h4>
                <p class="text-muted font-size-13 mb-0">Departmental Extension • Blood Group Serology & Compatibility</p>
            </div>
            <div class="page-title-right d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newDonorModal">
                    <i class="bx bx-user-plus me-1"></i> Register Donor
                </button>
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#newBagModal">
                    <i class="bx bx-plus-circle me-1"></i> Log Screened Blood Bag
                </button>
                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#crossMatchModal">
                    <i class="bx bx-test-tube me-1"></i> Request Cross-Match
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stock By Group Cards -->
<div class="row g-2 mb-4">
    @foreach($stockByGroup as $grp => $count)
        <div class="col-6 col-md-3 col-xl">
            <div class="card mini-stats-wid mb-0 border {{ $count > 0 ? 'border-danger' : '' }}">
                <div class="card-body p-3 text-center">
                    <h3 class="mb-1 text-danger fw-bold">{{ $grp }}</h3>
                    <div class="font-size-12 text-muted fw-semibold">{{ $count }} Units Available</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <!-- Screened Blood Bags Inventory -->
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Screened Blood Bags & Component Ledger</h4>
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Bag Identifier</th>
                                <th>Group / Component</th>
                                <th>Volume</th>
                                <th>Storage Location</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bloodBags as $bag)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark font-size-13">{{ $bag->bag_number }}</div>
                                        <small class="text-muted">Donor: {{ $bag->donor->full_name ?? 'Voluntary' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger font-size-12">{{ $bag->blood_group }}</span>
                                        <span class="badge bg-soft-secondary text-secondary ms-1">{{ strtoupper($bag->component_type) }}</span>
                                    </td>
                                    <td>{{ $bag->volume_ml }} mL</td>
                                    <td><small class="text-muted">{{ $bag->storage_location }}</small></td>
                                    <td>
                                        <small class="{{ \Carbon\Carbon::parse($bag->expiry_date)->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                            {{ \Carbon\Carbon::parse($bag->expiry_date)->format('d M Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($bag->status === 'available')
                                            <span class="badge bg-success">Available</span>
                                        @elseif($bag->status === 'issued')
                                            <span class="badge bg-secondary">Transfused</span>
                                        @else
                                            <span class="badge bg-warning">{{ ucfirst($bag->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($bag->status === 'available')
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#issueModal" onclick="document.getElementById('issue_bag_id').value = '{{ $bag->id }}'; document.getElementById('issue_bag_label').innerText = '{{ $bag->bag_number }} ({{ $bag->blood_group }} {{ $bag->component_type }})';">
                                                <i class="bx bx-check-shield me-1"></i> Issue
                                            </button>
                                        @else
                                            <span class="text-muted font-size-12">Completed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No blood bags in inventory.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cross-Match Testing Orders -->
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Compatibility & Cross-Match Orders</h4>
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Patient</th>
                                <th>Requested</th>
                                <th>Urgency</th>
                                <th>Doctor</th>
                                <th>Compatibility</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($crossMatchOrders as $ord)
                                <tr>
                                    <td><span class="badge bg-soft-primary text-primary">{{ $ord->order_number }}</span></td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $ord->patient->fullName }}</div>
                                        <small class="text-muted">MRN: {{ $ord->patient->mrn }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ $ord->required_blood_group }}</span>
                                        <span>{{ $ord->units_requested }} Unit(s) {{ strtoupper($ord->component_type) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $ord->urgency === 'stat_emergency' ? 'bg-danger' : 'bg-warning' }}">
                                            {{ strtoupper(str_replace('_', ' ', $ord->urgency)) }}
                                        </span>
                                    </td>
                                    <td>{{ $ord->doctor->name ?? 'Ordering Clinician' }}</td>
                                    <td>
                                        @if($ord->status === 'compatible')
                                            <span class="badge bg-soft-success text-success"><i class="bx bx-check"></i> Compatible</span>
                                        @elseif($ord->status === 'issued')
                                            <span class="badge bg-secondary">Issued</span>
                                        @else
                                            <span class="badge bg-info">{{ ucfirst($ord->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">No cross-match orders.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Registered Voluntary Donors -->
    <div class="col-xl-5">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Voluntary Blood Donors</h4>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newDonorModal">+ New</button>
                </div>

                <div class="list-group list-group-flush">
                    @forelse($donors as $d)
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 text-dark font-size-14">{{ $d->full_name }}</h6>
                                <span class="badge bg-danger font-size-12">{{ $d->blood_group }}</span>
                            </div>
                            <small class="text-muted d-block">
                                Code: <strong>{{ $d->donor_code }}</strong> • Phone: {{ $d->phone ?? 'N/A' }}
                            </small>
                            <small class="text-muted">
                                Last Donation: {{ $d->last_donation_date ? \Carbon\Carbon::parse($d->last_donation_date)->format('d M Y') : 'First Time' }} •
                                <span class="text-success"><i class="bx bx-check-circle"></i> {{ ucfirst($d->screening_status) }}</span>
                            </small>
                        </div>
                    @empty
                        <div class="text-muted text-center py-4">No donors registered yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Transfusion Issue Log -->
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Transfusion Verification & Handover Log</h4>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0 font-size-12">
                        <thead class="table-light">
                            <tr>
                                <th>Transfused Bag</th>
                                <th>Patient</th>
                                <th>Received By</th>
                                <th>Handover Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bloodIssues as $iss)
                                <tr>
                                    <td><code>{{ $iss->bloodBag->bag_number }}</code></td>
                                    <td>{{ $iss->patient->fullName }}</td>
                                    <td>{{ $iss->received_by_staff }}</td>
                                    <td>{{ $iss->issued_at->format('d M, H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No blood issued yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Register Donor -->
<div class="modal fade" id="newDonorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('blood_bank.donors.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Register Voluntary Blood Donor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" required placeholder="e.g. Rahul Sharma">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Blood Group <span class="text-danger">*</span></label>
                            <select name="blood_group" class="form-select" required>
                                <option value="A+">A+</option>
                                <option value="B+">B+</option>
                                <option value="O+">O+</option>
                                <option value="AB+">AB+</option>
                                <option value="A-">A-</option>
                                <option value="B-">B-</option>
                                <option value="O-">O-</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+91 98200 00000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Screening Status <span class="text-danger">*</span></label>
                            <select name="screening_status" class="form-select" required>
                                <option value="eligible">Eligible (Hb &gt; 12.5 g/dL)</option>
                                <option value="deferred">Deferred Temporary</option>
                                <option value="rejected">Rejected Permanent</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Donor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Log Screened Bag -->
<div class="modal fade" id="newBagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('blood_bank.bags.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Log Screened Blood Bag (Component Separation)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Blood Group <span class="text-danger">*</span></label>
                            <select name="blood_group" class="form-select" required>
                                <option value="A+">A+</option>
                                <option value="B+">B+</option>
                                <option value="O+">O+</option>
                                <option value="AB+">AB+</option>
                                <option value="A-">A-</option>
                                <option value="B-">B-</option>
                                <option value="O-">O-</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Component Type <span class="text-danger">*</span></label>
                            <select name="component_type" class="form-select" required>
                                <option value="prbc">Packed Red Blood Cells (PRBC)</option>
                                <option value="platelets">Platelet Concentrate (RDP/SDP)</option>
                                <option value="ffp">Fresh Frozen Plasma (FFP)</option>
                                <option value="whole_blood">Whole Blood</option>
                                <option value="cryo">Cryoprecipitate</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Volume (mL) <span class="text-danger">*</span></label>
                            <input type="number" name="volume_ml" class="form-control" value="350" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expiry Validity (Days) <span class="text-danger">*</span></label>
                            <input type="number" name="expiry_days" class="form-control" value="35" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Cold Storage Location <span class="text-danger">*</span></label>
                            <input type="text" name="storage_location" class="form-control" value="Refrigerator Unit #2, Shelf A" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Store Blood Bag</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Cross-Match Request -->
<div class="modal fade" id="crossMatchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('blood_bank.cross_match.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Request Patient Cross-Match Testing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Recipient Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select" required>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}">{{ $p->fullName }} ({{ $p->mrn }}) - Blood Group: {{ $p->blood_group }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Required Blood Group <span class="text-danger">*</span></label>
                            <select name="required_blood_group" class="form-select" required>
                                <option value="A+">A+</option>
                                <option value="B+">B+</option>
                                <option value="O+">O+</option>
                                <option value="AB+">AB+</option>
                                <option value="A-">A-</option>
                                <option value="B-">B-</option>
                                <option value="O-">O-</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Component Type <span class="text-danger">*</span></label>
                            <select name="component_type" class="form-select" required>
                                <option value="prbc">PRBC</option>
                                <option value="platelets">Platelets</option>
                                <option value="ffp">FFP</option>
                                <option value="whole_blood">Whole Blood</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Units Requested <span class="text-danger">*</span></label>
                            <input type="number" name="units_requested" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Urgency <span class="text-danger">*</span></label>
                            <select name="urgency" class="form-select" required>
                                <option value="routine">Routine</option>
                                <option value="urgent">Urgent</option>
                                <option value="stat_emergency">STAT Emergency</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Ordering Clinician <span class="text-danger">*</span></label>
                            <select name="doctor_id" class="form-select" required>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Submit Cross-Match</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Issue Blood -->
<div class="modal fade" id="issueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('blood_bank.issue.store') }}" method="POST">
                @csrf
                <input type="hidden" name="blood_bag_id" id="issue_bag_id">
                <div class="modal-header">
                    <h5 class="modal-title">Dual-Verification Blood Transfusion Issue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning font-size-13">
                        <i class="bx bx-shield-quarter me-1"></i> <strong>Clinical Safety Protocol:</strong> Both unit label, patient cross-match order, and receiving nurse/doctor identity must be verified prior to release.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Blood Bag To Issue:</label>
                        <div class="fw-bold text-danger font-size-15" id="issue_bag_label">Selected Bag</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Matching Cross-Match Order <span class="text-danger">*</span></label>
                        <select name="cross_match_order_id" class="form-select" required>
                            @foreach($crossMatchOrders as $ord)
                                <option value="{{ $ord->id }}">
                                    Order #{{ $ord->order_number }} - {{ $ord->patient->fullName }} ({{ $ord->required_blood_group }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Received By (Staff Nurse / Ward In-Charge) <span class="text-danger">*</span></label>
                        <input type="text" name="received_by_staff" class="form-control" required placeholder="e.g. Sr. Mary Fernandez (CCU In-charge)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transfusion Handover Notes</label>
                        <input type="text" name="transfusion_notes" class="form-control" value="Inspected unit seal intact, temperature maintained at +4°C during transport.">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bx bx-check-double me-1"></i> Verify & Hand Over Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
