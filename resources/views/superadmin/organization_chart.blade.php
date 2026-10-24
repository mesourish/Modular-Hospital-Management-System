@extends('layouts.skote')

@section('title', 'Platform Organization Hierarchy Chart')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">
                <i class="bx bx-sitemap text-primary me-1"></i> Organizational Hierarchy & Reporting Structure
            </h4>
            <div class="page-title-right d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="expandAllNodes()">
                    <i class="bx bx-expand me-1"></i> Expand All
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="collapseAllNodes()">
                    <i class="bx bx-collapse me-1"></i> Collapse All
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                    <i class="bx bx-printer me-1"></i> Print Org Chart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Platform Aggregated Structural Metrics -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-h-100 shadow-sm border-0 mb-0">
            <div class="card-body p-3 text-center">
                <i class="bx bx-crown text-warning font-size-24 mb-1"></i>
                <h4 class="mb-0 fw-bold text-dark">Platform Apex</h4>
                <small class="text-muted font-size-11">Corporate Root</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-h-100 shadow-sm border-0 mb-0">
            <div class="card-body p-3 text-center">
                <i class="bx bx-buildings text-primary font-size-24 mb-1"></i>
                <h4 class="mb-0 fw-bold text-primary">{{ $totalHospitals }}</h4>
                <small class="text-muted font-size-11">Hospital Tenants</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-h-100 shadow-sm border-0 mb-0">
            <div class="card-body p-3 text-center">
                <i class="bx bx-git-branch text-info font-size-24 mb-1"></i>
                <h4 class="mb-0 fw-bold text-info">{{ $totalBranches }}</h4>
                <small class="text-muted font-size-11">Multi-Branches</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-h-100 shadow-sm border-0 mb-0">
            <div class="card-body p-3 text-center">
                <i class="bx bx-pulse text-danger font-size-24 mb-1"></i>
                <h4 class="mb-0 fw-bold text-danger">{{ $totalDepartments }}</h4>
                <small class="text-muted font-size-11">Clinical Depts</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-h-100 shadow-sm border-0 mb-0">
            <div class="card-body p-3 text-center">
                <i class="bx bx-plus-medical text-success font-size-24 mb-1"></i>
                <h4 class="mb-0 fw-bold text-success">{{ $totalDoctors }}</h4>
                <small class="text-muted font-size-11">Specialist Doctors</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-h-100 shadow-sm border-0 mb-0">
            <div class="card-body p-3 text-center">
                <i class="bx bx-group text-secondary font-size-24 mb-1"></i>
                <h4 class="mb-0 fw-bold text-secondary">{{ $totalEmployees }}</h4>
                <small class="text-muted font-size-11">Total Workforce</small>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Hierarchy Filter & Search Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('superadmin.organization_chart') }}" method="GET" class="row align-items-center g-3">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bx bx-buildings"></i></span>
                    <select name="hospital_id" class="form-select" onchange="this.form.submit()">
                        <option value="all">-- All Hospital Groups ({{ $allHospitals->count() }}) --</option>
                        @foreach($allHospitals as $h)
                            <option value="{{ $h->id }}" {{ $selectedHospitalId == $h->id ? 'selected' : '' }}>
                                {{ $h->name }} ({{ $h->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bx bx-search"></i></span>
                    <input type="text" id="orgSearchInput" class="form-control" placeholder="Search doctor, department, branch, or designation..." onkeyup="filterOrgTree()">
                </div>
            </div>

            <div class="col-md-3 text-md-end">
                <span class="text-muted font-size-12">
                    <i class="bx bx-info-circle text-primary me-1"></i> Click any node to collapse/expand
                </span>
            </div>
        </form>
    </div>
</div>

<!-- Main Visual Organizational Tree Chart -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0 fw-bold text-dark">
                <i class="bx bx-network-chart text-primary me-1"></i> Hierarchical Structural Relation Tree
            </h5>
            <small class="text-muted">Direct visual reporting chain: Super Admin HQ &rarr; Hospitals &rarr; Branches &rarr; Departments &rarr; Medical Personnel</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark"><i class="bx bx-crown"></i> Super Admin</span>
            <span class="badge bg-primary text-white"><i class="bx bx-buildings"></i> Hospital</span>
            <span class="badge bg-info text-white"><i class="bx bx-git-branch"></i> Branch</span>
            <span class="badge bg-secondary text-white"><i class="bx bx-folder"></i> Department</span>
            <span class="badge bg-success text-white"><i class="bx bx-user"></i> Staff</span>
        </div>
    </div>
    <div class="card-body p-4 overflow-auto">
        <div class="org-tree-wrapper">
            <div class="org-tree">
                <ul>
                    <li>
                        <!-- 1. ROOT LEVEL: SUPER ADMIN CORPORATE APEX -->
                        <div class="org-node node-superadmin shadow-sm" onclick="toggleNode(this)">
                            <div class="node-badge bg-warning text-dark font-size-10 fw-bold text-uppercase px-2 py-1 rounded-pill mb-2">
                                <i class="bx bx-crown me-1"></i> Corporate Platform Apex
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm">
                                    <span class="avatar-title bg-warning-subtle text-dark rounded-circle font-size-18 fw-bold">
                                        <i class="bx bx-shield-quarter"></i>
                                    </span>
                                </div>
                                <div class="text-start">
                                    <h6 class="mb-0 fw-bold text-dark font-size-13">{{ $superAdminUser->name ?? 'Platform Super Admin HQ' }}</h6>
                                    <small class="text-muted font-size-11">{{ $superAdminUser->email ?? 'superadmin@medisystem.io' }}</small>
                                </div>
                            </div>
                            <div class="node-footer font-size-11 text-muted mt-2 pt-2 border-top">
                                <span>Multi-Tenant Governance • Subscriptions Master</span>
                            </div>
                        </div>

                        <!-- 2. LEVEL 2: HOSPITAL TENANTS -->
                        <ul>
                            @foreach($hospitals as $hosp)
                                <li>
                                    <div class="org-node node-hospital shadow-sm" onclick="toggleNode(this)">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-primary text-white font-size-10">{{ $hosp->code }}</span>
                                            <span class="badge bg-soft-success text-success font-size-10">{{ ucfirst($hosp->subscription_plan) }} Plan</span>
                                        </div>
                                        <div class="text-start">
                                            <h6 class="mb-0 fw-bold text-primary font-size-13">{{ $hosp->name }}</h6>
                                            <small class="text-muted font-size-11"><i class="bx bx-map-pin"></i> {{ $hosp->city }}, {{ $hosp->state }}</small>
                                        </div>
                                        <div class="node-footer font-size-11 text-muted mt-2 pt-2 border-top d-flex justify-content-between">
                                            <span><i class="bx bx-git-branch text-info"></i> {{ $hosp->branches->count() }} Branches</span>
                                            <span><i class="bx bx-group text-success"></i> {{ $hosp->users->count() }} Users</span>
                                        </div>
                                    </div>

                                    <!-- 3. LEVEL 3: HOSPITAL BRANCHES -->
                                    @if($hosp->branches->count() > 0)
                                        <ul>
                                            @foreach($hosp->branches as $branch)
                                                <li>
                                                    <div class="org-node node-branch shadow-sm" onclick="toggleNode(this)">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <span class="badge bg-info text-white font-size-10">{{ $branch->code }}</span>
                                                            @if($branch->is_main_branch)
                                                                <span class="badge bg-warning text-dark font-size-10"><i class="bx bx-star"></i> Main Flagship</span>
                                                            @endif
                                                        </div>
                                                        <div class="text-start">
                                                            <h6 class="mb-0 fw-bold text-dark font-size-13">{{ $branch->name }}</h6>
                                                            <small class="text-muted font-size-11"><i class="bx bx-user-voice"></i> Head: {{ $branch->manager_name ?? 'Branch Head' }}</small>
                                                        </div>
                                                        <div class="node-footer font-size-11 text-muted mt-2 pt-2 border-top d-flex justify-content-between">
                                                            <span><i class="bx bx-folder text-warning"></i> {{ $branch->departments->count() }} Depts</span>
                                                            <span><i class="bx bx-phone text-success"></i> {{ $branch->phone }}</span>
                                                        </div>
                                                    </div>

                                                    <!-- 4. LEVEL 4: DEPARTMENTS UNDER THIS BRANCH -->
                                                    @if($branch->departments->count() > 0)
                                                        <ul>
                                                            @foreach($branch->departments as $dept)
                                                                <li>
                                                                    <div class="org-node node-department shadow-sm" onclick="toggleNode(this)">
                                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                                            <span class="badge bg-secondary text-white font-size-10">{{ $dept->code }}</span>
                                                                            <span class="badge bg-soft-info text-info font-size-10">{{ ucfirst($dept->type) }}</span>
                                                                        </div>
                                                                        <div class="text-start">
                                                                            <h6 class="mb-0 fw-bold text-dark font-size-12">{{ $dept->name }}</h6>
                                                                            <small class="text-muted font-size-11">{{ $dept->employees->count() }} Staff Assigned</small>
                                                                        </div>
                                                                    </div>

                                                                    <!-- 5. LEVEL 5: STAFF & MEDICAL PROFESSIONALS -->
                                                                    @if($dept->employees->count() > 0)
                                                                        <ul>
                                                                            @foreach($dept->employees as $emp)
                                                                                <li>
                                                                                    <div class="org-node node-staff shadow-sm">
                                                                                        <div class="d-flex align-items-center gap-2 mb-1">
                                                                                            <div class="avatar-xs">
                                                                                                <span class="avatar-title bg-success-subtle text-success rounded-circle font-size-12 fw-bold">
                                                                                                    {{ substr($emp->first_name, 0, 1) }}{{ substr($emp->last_name, 0, 1) }}
                                                                                                </span>
                                                                                            </div>
                                                                                            <div class="text-start">
                                                                                                <div class="fw-bold font-size-12 text-dark">{{ $emp->full_name }}</div>
                                                                                                <small class="text-primary font-size-10 d-block">{{ $emp->designation->title ?? 'Staff Professional' }}</small>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="font-size-10 text-muted border-top pt-1 text-start">
                                                                                            <div><code>{{ $emp->employee_code }}</code> • {{ $emp->qualification }}</div>
                                                                                            <div><i class="bx bx-envelope"></i> {{ $emp->email }}</div>
                                                                                        </div>
                                                                                    </div>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    /* CSS Organization Tree Hierarchy Styles */
    .org-tree-wrapper {
        min-width: 900px;
        padding: 20px 0;
        display: flex;
        justify-content: center;
    }
    .org-tree ul {
        padding-top: 20px;
        position: relative;
        transition: all 0.5s;
        display: flex;
        justify-content: center;
        list-style: none;
        margin: 0;
        padding-left: 0;
    }
    .org-tree li {
        text-align: center;
        position: relative;
        padding: 20px 10px 0 10px;
        transition: all 0.5s;
    }
    /* Connecting lines above child nodes */
    .org-tree li::before, .org-tree li::after {
        content: '';
        position: absolute;
        top: 0;
        right: 50%;
        border-top: 2px solid #ced4da;
        width: 50%;
        height: 20px;
    }
    .org-tree li::after {
        right: auto;
        left: 50%;
        border-left: 2px solid #ced4da;
    }
    .org-tree li:only-child::after, .org-tree li:only-child::before {
        display: none;
    }
    .org-tree li:only-child {
        padding-top: 0;
    }
    .org-tree li:first-child::before, .org-tree li:last-child::after {
        border: 0 none;
    }
    .org-tree li:last-child::before {
        border-right: 2px solid #ced4da;
        border-radius: 0 5px 0 0;
    }
    .org-tree li:first-child::after {
        border-radius: 5px 0 0 0;
    }
    /* Vertical line down from parent to children */
    .org-tree ul ul::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        border-left: 2px solid #ced4da;
        width: 0;
        height: 20px;
    }

    /* Node Cards */
    .org-node {
        display: inline-block;
        padding: 12px 14px;
        border-radius: 10px;
        background: #ffffff;
        border: 2px solid #e9ecef;
        min-width: 200px;
        max-width: 240px;
        cursor: pointer;
        transition: all 0.25s ease;
        position: relative;
    }
    .org-node:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
    }
    .node-superadmin {
        border-color: #f1b44c;
        background: linear-gradient(135deg, #ffffff 0%, #fffbf2 100%);
    }
    .node-hospital {
        border-color: #556ee6;
        background: linear-gradient(135deg, #ffffff 0%, #f4f6fd 100%);
    }
    .node-branch {
        border-color: #50a5f1;
        background: linear-gradient(135deg, #ffffff 0%, #f3f8fd 100%);
    }
    .node-department {
        border-color: #74788d;
        background: #ffffff;
    }
    .node-staff {
        border-color: #34c38f;
        background: #ffffff;
        cursor: default;
    }
    .node-collapsed > ul {
        display: none !important;
    }

    @media print {
        .navbar-brand-box, .vertical-menu, .page-title-box .btn, .card-header .badge, footer, .card.mb-4, form {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .org-tree-wrapper {
            zoom: 70%;
        }
    }
</style>

<script>
    function toggleNode(element) {
        var parentLi = element.parentElement;
        var childUl = parentLi.querySelector('ul');
        if (childUl) {
            if (childUl.style.display === 'none') {
                childUl.style.display = 'flex';
                element.classList.remove('opacity-75');
            } else {
                childUl.style.display = 'none';
                element.classList.add('opacity-75');
            }
        }
    }

    function expandAllNodes() {
        document.querySelectorAll('.org-tree ul').forEach(function(ul) {
            ul.style.display = 'flex';
        });
        document.querySelectorAll('.org-node').forEach(function(node) {
            node.classList.remove('opacity-75');
        });
    }

    function collapseAllNodes() {
        document.querySelectorAll('.org-tree ul ul ul').forEach(function(ul) {
            ul.style.display = 'none';
        });
    }

    function filterOrgTree() {
        var query = document.getElementById('orgSearchInput').value.toLowerCase().trim();
        var nodes = document.querySelectorAll('.org-node');

        nodes.forEach(function(node) {
            var text = node.innerText.toLowerCase();
            if (query === '' || text.indexOf(query) !== -1) {
                node.style.borderWidth = '2px';
                if (query !== '' && text.indexOf(query) !== -1) {
                    node.style.borderColor = '#e83e8c';
                    node.style.backgroundColor = '#fff0f5';
                } else {
                    node.style.borderColor = '';
                    node.style.backgroundColor = '';
                }
            } else {
                node.style.borderColor = '#e9ecef';
                node.style.backgroundColor = '#f8f9fa';
            }
        });
    }
</script>
@endsection
