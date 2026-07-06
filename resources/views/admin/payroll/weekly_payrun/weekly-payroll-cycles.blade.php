@extends('admin.layout.master')

@section('title', 'Payroll Cycles')

@section('css')
<style>
    .week-status-open { background: #dbeafe; color: #1e40af; }
    .week-status-pending { background: #fef3c7; color: #92400e; }
    
    /* Week List Styles */
    .week-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 12px;
        padding-top: 8px;
        border-top: 1px solid #e2e8f0;
        width: 100%;
        max-width: 100%;
    }

    .week-item-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        min-height: 52px;
        gap: 12px;
        transition: all 0.2s ease;
        background: #ffffff;
        overflow: hidden;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }

    .dark-mode .week-item-card {
        background: #1f2937;
        border-color: #374151;
    }

    .week-item-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
    }

    .week-info {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1 1 auto;
        flex-wrap: wrap;
        min-width: 0;
        max-width: 100%;
    }

    .week-badge {
        font-weight: 600;
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #475569;
        min-width: 60px;
        text-align: center;
    }

    .dark-mode .week-badge {
        background: #374151;
        color: #e2e8f0;
    }

    .week-dates {
        font-size: 10px;
        font-family: monospace;
        padding: 3px 10px;
        border-radius: 16px;
        background: #f8fafc;
        color: #475569;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dark-mode .week-dates {
        background: #2d3748;
        color: #94a3b8;
    }

    .week-status-badge {
        font-size: 10px;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        min-width: 90px;
        justify-content: center;
        white-space: nowrap;
        max-width: 100%;
    }

    .week-status-configured { background: #e2e3e5; color: #383d41; }
    .week-status-pending_approval { background: #fff3cd; color: #856404; }
    .week-status-under_review { background: #d1ecf1; color: #0c5460; }
    .week-status-frozen { background: #cce5ff; color: #004085; }
    .week-status-processing { background: #d4edda; color: #155724; }
    .week-status-processed { background: #d1fae5; color: #065f46; }
    .week-status-finalized { background: #c3e6cb; color: #155724; }

    .btn-week-process,
    .btn-week-view,
    .btn-week-disabled {
        padding: 4px 12px;
        font-size: 10px;
        font-weight: 500;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        min-width: 75px;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.2s;
        cursor: pointer;
        border: none;
    }

    .btn-week-process {
        background: #3b82f6;
        color: white;
    }
    .btn-week-process:hover { background: #2563eb; }

    .btn-week-view {
        background: #10b981;
        color: white;
    }
    .btn-week-view:hover { background: #059669; }

    .btn-week-disabled {
        background: #9ca3af;
        color: white;
        cursor: not-allowed;
    }

    .week-actions {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        min-width: 88px;
        max-width: 100%;
    }

    .period-weeks-container {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }

    .cycle-card .card-body {
        overflow: hidden;
    }

    /* Cycle Card Styles */
    .stat-card {
        border-radius: 8px;
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
        background: white;
        height: 100%;
    }
    .dark-mode .stat-card { background: #1f2937; border-color: #374151; }

    .cycle-card {
        transition: all 0.3s ease;
        border-radius: 8px;
        overflow: hidden;
        background: white;
        border: 1px solid #e5e7eb;
        margin-bottom: 1rem;
        cursor: pointer;
    }
    .dark-mode .cycle-card { background: #1f2937; border-color: #374151; }
    .cycle-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-color: #3b82f6; }

    .bg-primary-subtle { background-color: rgba(59, 130, 246, 0.1) !important; }
    .bg-success-subtle { background-color: rgba(16, 185, 129, 0.1) !important; }
    .bg-warning-subtle { background-color: rgba(245, 158, 11, 0.1) !important; }
    .bg-info-subtle { background-color: rgba(6, 182, 212, 0.1) !important; }
    .bg-danger-subtle { background-color: rgba(239, 68, 68, 0.1) !important; }

    @media (max-width: 1200px) {
        .row.g-3>[class*="col-xl-3"] { width: 50%; }
    }
    @media (max-width: 768px) {
        .row.g-3>[class*="col-"] { width: 100%; margin-bottom: 1rem; }
        .week-info { flex: 1 1 100%; justify-content: flex-start; }
        .week-dates { white-space: normal; font-size: 9px; }
        .week-status-badge { white-space: normal; min-width: 0; }
        .week-item-card { flex-wrap: wrap; }
        .week-actions { width: 100%; justify-content: flex-end; margin-top: 6px; }
    }
</style>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-psuccess alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Breadcrumbs --}}
<div class="p-0 mt-3">
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="active"><span><b>Payroll Cycles</b></span></li>
            </ol>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <!-- Card Header -->
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">
                        <i data-lucide="calendar" class="text-primary me-2"></i>
                        Payroll Cycles
                    </h4>
                    @if($currentFY)
                    <div class="d-flex align-items-center mt-2">
                        <div class="badge bg-opacity-10 text-primary px-3 py-1 rounded-pill d-flex align-items-center">
                            <i data-lucide="calendar-days" class="me-1" style="width: 14px; height: 14px;"></i>
                            <strong class="me-2">{{ $currentFY->fy_year }}</strong>
                            @if($currentFY->fy_is_current)
                            <span class="badge bg-success ms-1">Current</span>
                            @endif
                        </div>
                        @php
                            $cyclesCount = is_countable($cycles) ? count($cycles) : (method_exists($cycles, 'count') ? $cycles->count() : 0);
                        @endphp
                        <span class="ms-3 text-muted small">{{ $cyclesCount }} period(s) found</span>
                    </div>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm d-flex align-items-center" type="button" id="fyDropdown" data-bs-toggle="dropdown">
                            <i data-lucide="filter" class="me-1" style="width: 14px; height: 14px;"></i>
                            Financial Year
                            <i data-lucide="chevron-down" class="ms-1" style="width: 14px; height: 14px;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 200px;">
                            <li><h6 class="dropdown-header">Select Financial Year</h6></li>
                            @foreach ($financialYears as $year)
                            <li>
                                <a class="dropdown-item d-flex justify-content-between align-items-center {{ $currentFY && $currentFY->fy_id == $year->fy_id ? 'active' : '' }}"
                                    href="{{ url()->current() }}?fy_id={{ $year->fy_id }}">
                                    <span>{{ $year->fy_year }} @if($year->fy_is_current)<span class="badge bg-success ms-2">Current</span>@endif</span>
                                    @if($currentFY && $currentFY->fy_id == $year->fy_id)
                                    <i data-lucide="check" class="text-primary" style="width: 16px; height: 16px;"></i>
                                    @endif
                                </a>
                            </li>
                            @endforeach
                            @if(request()->has('fy_id'))
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="{{ url()->current() }}"><i data-lucide="x" class="me-1"></i>Clear Filter</a></li>
                            @endif
                        </ul>
                    </div>
                    <button data-action="create-cycle" class="btn btn-outline-primary btn-sm d-flex align-items-center">
                        <i data-lucide="plus" class="me-1" style="width: 14px; height: 14px;"></i>
                        New Cycle
                    </button>
                </div>
            </div>

            <div class="card-body">
                @php
                $inProgressStatuses = ['open', 'attendance_frozen', 'processing', 'pending_approval', 'under_review', 'in_process', 'verification'];
                $completedStatus = 'finalized_locked';
                
                function countByStatus($cyclesArray, $status) {
                    return count(array_filter($cyclesArray, function($cycle) use ($status) {
                        return isset($cycle['status']) && $cycle['status'] === $status;
                    }));
                }
                function countInStatuses($cyclesArray, $statuses) {
                    return count(array_filter($cyclesArray, function($cycle) use ($statuses) {
                        return isset($cycle['status']) && in_array($cycle['status'], $statuses);
                    }));
                }
                function sumHoldCount($cyclesArray) {
                    return array_sum(array_column($cyclesArray, 'hold_count'));
                }
                
                $cyclesArray = [];
                if (is_countable($cycles)) {
                    $cyclesArray = $cycles instanceof \Illuminate\Support\Collection ? $cycles->toArray() : (array)$cycles;
                } elseif (is_object($cycles) && method_exists($cycles, 'toArray')) {
                    $cyclesArray = $cycles->toArray();
                }
                $cyclesCount = count($cyclesArray);
                @endphp

                @if($cyclesCount > 0)
                <div class="row g-3 mb-4">
                    <div class="col"><div class="stat-card border-0 h-100"><div class="card-body p-3"><div class="d-flex align-items-center"><div class="bg-opacity-10 bg-primary rounded-circle p-2 me-3"><i data-lucide="calendar" class="text-primary" style="width: 20px; height: 20px;"></i></div><div><h5 class="mb-0">{{ $cyclesCount }}</h5><small class="text-muted">Total Periods</small></div></div></div></div></div>
                    <div class="col"><div class="stat-card border-0 h-100"><div class="card-body p-3"><div class="d-flex align-items-center"><div class="bg-opacity-10 bg-success rounded-circle p-2 me-3"><i data-lucide="check-circle" class="text-success" style="width: 20px; height: 20px;"></i></div><div><h5 class="mb-0">{{ countByStatus($cyclesArray, 'finalized_locked') }}</h5><small class="text-muted">Completed & Locked</small></div></div></div></div></div>
                    <div class="col"><div class="stat-card border-0 h-100"><div class="card-body p-3"><div class="d-flex align-items-center"><div class="bg-opacity-10 bg-warning rounded-circle p-2 me-3"><i data-lucide="play-circle" class="text-warning" style="width: 20px; height: 20px;"></i></div><div><h5 class="mb-0">{{ countInStatuses($cyclesArray, $inProgressStatuses) }}</h5><small class="text-muted">In Progress</small></div></div></div></div></div>
                    <div class="col"><div class="stat-card border-0 h-100"><div class="card-body p-3"><div class="d-flex align-items-center"><div class="bg-opacity-10 bg-info rounded-circle p-2 me-3"><i data-lucide="clock" class="text-info" style="width: 20px; height: 20px;"></i></div><div><h5 class="mb-0">{{ countInStatuses($cyclesArray, ['upcoming', 'expired']) }}</h5><small class="text-muted">Upcoming/Expired</small></div></div></div></div></div>
                    <div class="col"><div class="stat-card border-0 h-100"><div class="card-body p-3"><div class="d-flex align-items-center"><div class="bg-opacity-10 bg-danger rounded-circle p-2 me-3"><i data-lucide="pause" class="text-danger" style="width: 20px; height: 20px;"></i></div><div><h5 class="mb-0">{{ sumHoldCount($cyclesArray) }}</h5><small class="text-muted">Total Holds</small></div></div></div></div></div>
                </div>

                @php
                $quarters = [
                    'Q1' => ['name' => 'Q1 (Apr-Jun)', 'months' => ['April', 'May', 'June'], 'color' => 'primary', 'icon' => 'trending-up'],
                    'Q2' => ['name' => 'Q2 (Jul-Sep)', 'months' => ['July', 'August', 'September'], 'color' => 'success', 'icon' => 'trending-up'],
                    'Q3' => ['name' => 'Q3 (Oct-Dec)', 'months' => ['October', 'November', 'December'], 'color' => 'warning', 'icon' => 'trending-up'],
                    'Q4' => ['name' => 'Q4 (Jan-Mar)', 'months' => ['January', 'February', 'March'], 'color' => 'info', 'icon' => 'trending-up']
                ];

                // Sort cycles by month order
                usort($cyclesArray, function($a, $b) {
                    $monthOrder = ['April'=>1,'May'=>2,'June'=>3,'July'=>4,'August'=>5,'September'=>6,'October'=>7,'November'=>8,'December'=>9,'January'=>10,'February'=>11,'March'=>12];
                    $monthA = isset($a['month']) ? $a['month'] : '';
                    $monthB = isset($b['month']) ? $b['month'] : '';
                    return ($monthOrder[$monthA] ?? 0) - ($monthOrder[$monthB] ?? 0);
                });

                $groupedCycles = [];
                foreach ($cyclesArray as $cycle) { 
                    if (isset($cycle['month']) && !empty($cycle['month'])) {
                        $groupedCycles[$cycle['month']] = $cycle;
                    }
                }

                // Create columns for ALL quarters (show all quarters, not just current)
                $columnsData = [[], [], [], []];
                $quarterIndex = 0;
                foreach ($quarters as $quarterKey => $quarterData) {
                    // REMOVED: if($quarterKey != $currentQuarterKey) continue; - Show ALL quarters
                    foreach ($quarterData['months'] as $monthName) {
                        if (isset($groupedCycles[$monthName])) {
                            $cycle = $groupedCycles[$monthName];
                            $cycle['quarter'] = $quarterKey;
                            $cycle['quarter_name'] = $quarterData['name'];
                            $cycle['quarter_color'] = $quarterData['color'];
                            $cycle['quarter_icon'] = $quarterData['icon'];
                            $columnsData[$quarterIndex][] = $cycle;
                        }
                    }
                    $quarterIndex++;
                }
                @endphp

                <div class="row g-3">
                    @foreach($columnsData as $quarterIndex => $quarterCycles)
                    @php
                    $quarterKeys = array_keys($quarters);
                    $quarterKey = $quarterKeys[$quarterIndex] ?? 'Q1';
                    $quarterData = $quarters[$quarterKey] ?? $quarters['Q1'];
                    @endphp

                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                        <div class="card border-0 bg-{{ $quarterData['color'] }}-subtle mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-opacity-25 rounded-circle p-2 me-2">
                                            <i data-lucide="{{ $quarterData['icon'] }}" class="text-{{ $quarterData['color'] }}" style="width: 16px; height: 16px;"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-dark">{{ $quarterData['name'] }}</h6>
                                            <small class="text-muted">{{ count($quarterCycles) }} month(s)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(empty($quarterCycles))
                        <div class="text-center py-4">
                            <div class="mb-2"><i data-lucide="calendar-off" class="text-muted" style="width: 32px; height: 32px;"></i></div>
                            <p class="text-muted small mb-0">No cycles for this quarter</p>
                        </div>
                        @else
                        @foreach($quarterCycles as $cycle)
                        @php
                            $ui = $cycle['ui'] ?? [];
                            $status = $cycle['status'] ?? 'upcoming';
                            $blockedStatuses = ['upcoming', 'expired', 'finalized_locked', 'PAYROLL_LOCKED'];
                            $isDisabled = in_array($status, $blockedStatuses);
                            $cycleMonth = isset($cycle['month']) ? $cycle['month'] : 'N/A';
                        @endphp
                        <div class="mb-3">
                            <div class="card cycle-card h-100 border-0 shadow-sm" data-cycle-id="{{ $cycle['id'] }}" data-status="{{ $status }}"
                                style="border-left: 4px solid var(--bs-{{ $ui['color'] ?? 'primary' }}); {{ $isDisabled ? 'opacity: 0.8;' : '' }}">
                                <div class="card-body d-flex flex-column p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="bg-{{ $ui['color'] ?? 'primary' }}-subtle rounded-circle p-1 me-2">
                                                    <i data-lucide="calendar" class="text-{{ $ui['color'] ?? 'primary' }}" style="width: 14px; height: 14px;"></i>
                                                </div>
                                                <h6 class="card-title mb-0 text-dark">{{ $cycleMonth }}</h6>
                                            </div>
                                            <div class="text-muted small">
                                                <i data-lucide="calendar-range" class="me-1" style="width: 12px; height: 12px;"></i>
                                                {{ $cycle['start'] ?? 'N/A' }} – {{ $cycle['end'] ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            @if(($cycle['can_edit_weeks'] ?? false) && ($cycle['is_weekly'] ?? false))
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary edit-cycle-btn"
                                                data-cycle-id="{{ $cycle['id'] }}"
                                                title="Edit week date ranges">
                                                <i data-lucide="pencil" style="width: 12px; height: 12px;"></i>
                                            </button>
                                            @endif
                                            <span class="badge bg-{{ $ui['color'] ?? 'primary' }}-subtle text-{{ $ui['color'] ?? 'primary' }} border border-{{ $ui['color'] ?? 'primary' }} border-opacity-25 d-flex align-items-center">
                                                <i data-lucide="{{ $ui['icon'] ?? 'clock' }}" class="me-1" style="width: 12px; height: 12px;"></i>
                                                {{ $ui['label'] ?? 'Upcoming' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Weeks Display for Weekly Payroll -->
                                    @if(($cycle['is_weekly'] ?? false))
                                    <div class="period-weeks-container mt-2">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted">
                                                <i data-lucide="calendar-range" style="width: 12px; height: 12px;"></i>
                                                Payroll Weeks ({{ $cycle['weeks_count'] ?? 0 }})
                                            </small>
                                            @if(($cycle['weeks_count'] ?? 0) > 0)
                                            <button type="button" class="btn btn-sm btn-outline-secondary weeks-toggle-btn"
                                                onclick="toggleWeeksList({{ $cycle['id'] }})"
                                                style="padding: 2px 8px; font-size: 11px;">
                                                <i data-lucide="chevron-down" style="width: 12px; height: 12px;"></i>
                                                <span>Show</span>
                                            </button>
                                            @endif
                                        </div>

                                        <div id="weeks-list-{{ $cycle['id'] }}" class="week-list" style="display: none;">
                                            @if(!empty($cycle['weeks']) && count($cycle['weeks']) > 0)
                                            @foreach($cycle['weeks'] as $week)
                                            @php
                                                $canProcess = $week['can_process'] ?? false;
                                                $canView = $week['can_view'] ?? false;
                                                $weekStatus = $week['status'] ?? 'pending';
                                                $statusLabel = $week['status_label'] ?? ucfirst($weekStatus);
                                                $statusIcon = $week['status_icon'] ?? 'clock';
                                                $startDate = $week['start_date'] ?? 'N/A';
                                                $endDate = $week['end_date'] ?? 'N/A';
                                                
                                                // Ensure 'open' status shows Process button
                                                if ($weekStatus == 'open') {
                                                    $canProcess = true;
                                                }
                                            @endphp
                                            <div class="week-item-card" data-week-id="{{ $week['id'] }}">
                                                <div class="week-info">
                                                    <span class="week-badge">{{ $week['week_name'] }}</span>
                                                    <span class="week-dates">
                                                        <i data-lucide="calendar" style="width: 10px; height: 10px;"></i>
                                                        {{ $startDate }} - {{ $endDate }}
                                                    </span>
                                                    <span class="week-status-badge week-status-{{ $weekStatus }}">
                                                        <i data-lucide="{{ $statusIcon }}" style="width: 10px; height: 10px;"></i>
                                                        {{ $statusLabel }}
                                                    </span>
                                                    @if(($week['employee_count'] ?? 0) > 0)
                                                    <small class="text-muted">
                                                        <i data-lucide="users" style="width: 10px; height: 10px;"></i>
                                                        {{ $week['employee_count'] }} emp
                                                    </small>
                                                    @endif
                                                </div>
                                                <div class="week-actions">
                                                    @if($canProcess)
                                                    <button type="button" class="btn-week-process" onclick="processWeek({{ $week['payroll_period_id'] ?? $cycle['id'] }}, {{ $week['id'] }}, '{{ addslashes($week['week_name']) }}')">
                                                        <i data-lucide="play" style="width: 10px; height: 10px;"></i>
                                                        Process
                                                    </button>
                                                    @elseif($canView)
                                                    <button type="button" class="btn-week-view" onclick="viewWeekResults({{ $week['payroll_period_id'] ?? $cycle['id'] }}, {{ $week['id'] }}, '{{ $week['week_name'] }}')">
                                                        <i data-lucide="eye" style="width: 10px; height: 10px;"></i>
                                                        View
                                                    </button>
                                                    @else
                                                    <button type="button" class="btn-week-disabled" disabled>
                                                        <i data-lucide="lock" style="width: 10px; height: 10px;"></i>
                                                        {{ $statusLabel }}
                                                    </button>
                                                    @endif
                                                </div>
                                            </div>
                                            @endforeach
                                            @else
                                            <div class="week-empty-state text-center py-3">
                                                <i data-lucide="calendar-x" style="width: 32px; height: 32px; color: #94a3b8; margin-bottom: 8px;"></i>
                                                <p class="text-muted small mb-1">No week details available</p>
                                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="configureWeeks({{ $cycle['id'] }}, '{{ $cycleMonth }}')">
                                                    <i data-lucide="settings" style="width: 12px; height: 12px;"></i>
                                                    Configure Weeks
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif

                                    <div class="d-flex align-items-center mb-2 mt-2">
                                        <div class="bg-light rounded-circle p-1 me-2">
                                            <i data-lucide="users" class="text-muted" style="width: 12px; height: 12px;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small">{{ $cycle['employees'] ?? 0 }}</div>
                                            <div class="text-muted small">Employees</div>
                                        </div>
                                    </div>

                                    @if(!empty($cycle['has_holds']))
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-danger-subtle rounded-circle p-1 me-2">
                                            <i data-lucide="pause" class="text-danger" style="width: 12px; height: 12px;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-danger small">{{ $cycle['hold_count'] ?? 0 }} Salary Hold(s)</div>
                                            <div class="text-muted small">Requires attention</div>
                                        </div>
                                        <button type="button" class="ms-auto btn btn-sm btn-outline-danger btn-hold-details"
                                            data-cycle-id="{{ $cycle['id'] }}" data-cycle-month="{{ $cycleMonth }}">
                                            <i data-lucide="eye" style="width: 12px; height: 12px;"></i>
                                        </button>
                                    </div>
                                    @else
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-success-subtle rounded-circle p-1 me-2">
                                            <i data-lucide="check-circle" class="text-success" style="width: 12px; height: 12px;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-success small">No Salary Holds</div>
                                            <div class="text-muted small">All clear</div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-5">
                    <div class="mb-3"><i data-lucide="calendar" class="text-muted" style="width: 64px; height: 64px;"></i></div>
                    <h5 class="text-muted mb-2">No Payroll Cycles Found</h5>
                    <p class="text-muted mb-4">No payroll periods found for <strong>{{ $currentFY->fy_year ?? 'selected' }}</strong> financial year.<br>Create a new payroll cycle to get started.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <button data-action="create-cycle" class="btn btn-primary">
                            <i data-lucide="plus" class="me-1"></i>Create New Cycle
                        </button>
                        @if(request()->has('fy_id'))<a href="{{ url()->current() }}" class="btn btn-outline-secondary"><i data-lucide="filter-x" class="me-1"></i>Clear Filter</a>@endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Payroll Period Modal -->
<div class="modal fade" id="addPayrollPeriodModal" tabindex="-1" aria-labelledby="addPayrollPeriodLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPayrollPeriodLabel">Add Payroll Period</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="alertContainer" class="mb-3"></div>
                <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                    <i data-lucide="info" class="me-2"></i>
                    Current Quarter (Q1 (Apr-Jun)): April, May, June months are enabled.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <form id="addPayrollPeriodForm" action="{{ route('payroll.weekly.store') }}" method="POST">
                    @csrf
                    <input type="hidden" id="editingPayrollId" value="">
                    <input type="hidden" id="isEditMode" value="0">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Financial Year <span class="text-danger">*</span></label>
                            <select class="form-select" id="financialYear" name="financial_year" required>
                                <option value="">-- Select Financial Year --</option>
                                @foreach($financialYears as $year)
                                <option value="{{ $year->fy_id }}" {{ $currentFY && $currentFY->fy_id == $year->fy_id ? 'selected' : '' }}>
                                    {{ $year->fy_year }} @if($year->fy_is_current) (Current) @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payroll Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="payrollType" name="payroll_type" required>
                                <option value="440">Monthly</option>
                                <option value="441" {{ $defaultPayrollType == '441' ? 'selected' : '' }}>Weekly</option>
                            </select>
                            @if($isWeeklySystem)
                            <small class="text-info d-block mt-1"><i data-lucide="info" style="width: 12px; height: 12px;"></i>Weekly payroll is enabled in system settings</small>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Month <span class="text-danger">*</span></label>
                            <select class="form-select" id="month" name="month" required>
                                <option value="">-- Select Month --</option>
                            </select>
                            <small class="text-muted">Cycle month is used for grouping; week dates can cross month boundaries</small>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-8">
                            <label class="form-label">Payroll Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="payrollName" name="payroll_name" placeholder="e.g. April 2026 - Weekly" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cheque Number</label>
                            <input type="text" class="form-control" id="chequeNumber" name="cheque_number" placeholder="Optional cheque number">
                        </div>
                    </div>

                    <div id="weeklySection" style="display: none;">
                        <div class="card border-primary mb-4 shadow-sm">
                            <div class="card-header bg-opacity-10 py-3">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <h6 class="mb-0"><i data-lucide="calendar-range" class="me-2"></i>Configure Weekly Date Ranges - <span id="selectedMonthDisplay" class="fw-bold"></span></h6>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addWeekBtn"><i data-lucide="plus" class="me-1"></i>Add Week</button>
                                </div>
                            </div>
                            <div class="card-body" id="weeksContainer">
                                <div class="text-center py-5 text-muted" id="weekPlaceholder">
                                    <i data-lucide="calendar" style="width: 64px; height: 64px;"></i>
                                    <p class="mt-3 mb-0">Please select cycle month. Weeks auto-load first; you can then adjust week dates manually (cross-month is allowed).</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="monthlySection">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Attendance Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="attendanceStartDate" name="attendance_start_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Attendance End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="attendanceEndDate" name="attendance_end_date">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="is_weekly_payroll" id="isWeeklyPayroll" value="0">
                    <input type="hidden" name="weekly_weeks_data" id="weeklyWeeksData" value="">

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Date of Payment <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="dateOfPayment" name="date_of_payment" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payslip Online Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="payslipOnlineDate" name="payslip_online_date" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal"><i data-lucide="x" class="me-1"></i>Cancel</button>
                        <button type="submit" class="btn btn-outline-primary" id="submitBtn">
                            <span id="submitText"><i data-lucide="save" class="me-1"></i>Save Payroll Period</span>
                            <span id="loadingSpinner" class="d-none"><span class="spinner-border spinner-border-sm me-1"></span>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Hold Details Modal -->
<div class="modal fade" id="holdDetailsModal" tabindex="-1" aria-labelledby="holdDetailsModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <div class="bg-danger-subtle rounded-circle p-2 me-3"><i data-lucide="pause" class="text-danger" style="width: 20px; height: 20px;"></i></div>
                    <div><h5 class="modal-title mb-0" id="holdDetailsModalLabel">Salary Holds Details</h5><small class="text-muted" id="holdDetailsSubtitle"></small></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="holdDetailsLoading" class="text-center py-5"><div class="spinner-border text-danger" style="width: 3rem; height: 3rem;"></div><p class="text-muted mt-3">Loading hold details...</p></div>
                <div id="holdDetailsContent" class="d-none">
                    <div class="card border-danger-subtle mb-4">
                        <div class="card-body"><div class="row"><div class="col-md-6"><div class="d-flex align-items-center"><div class="bg-danger-subtle rounded-circle p-2 me-3"><i data-lucide="alert-circle" class="text-danger"></i></div><div><h6 class="mb-0" id="totalHoldsCount">0 Hold(s)</h6><small class="text-muted">Total employees on hold</small></div></div></div><div class="col-md-6"><div class="d-flex align-items-center"><div class="bg-warning-subtle rounded-circle p-2 me-3"><i data-lucide="alert-triangle" class="text-warning"></i></div><div><h6 class="mb-0" id="pendingActions">0 Pending</h6><small class="text-muted">Actions required</small></div></div></div></div></div></div>
                    <div class="table-responsive"><table class="table table-hover table-striped" id="holdsTable"><thead class="table-light"><tr><th>#</th><th>Employee</th><th>Employee ID</th><th>Department</th><th>Hold Reason</th><th>Actions</th></tr></thead><tbody id="holdsTableBody"></tbody></table></div>
                    <div id="noHoldsMessage" class="text-center py-5 d-none"><div class="mb-3"><i data-lucide="check-circle" class="text-success" style="width: 64px; height: 64px;"></i></div><h5 class="text-success mb-2">No Salary Holds</h5><p class="text-muted">All employees are ready for payroll processing.</p></div>
                    <div id="errorMessage" class="text-center py-5 d-none"><div class="mb-3"><i data-lucide="alert-octagon" class="text-danger" style="width: 64px; height: 64px;"></i></div><h5 class="text-danger mb-2">Unable to Load Data</h5><p class="text-muted" id="errorText">Failed to load hold details. Please try again.</p><button class="btn btn-outline-danger mt-2" id="retryButton"><i data-lucide="refresh-cw" class="me-1"></i>Retry</button></div>
                </div>
            </div>
            <div class="modal-footer border-top"><div class="d-flex justify-content-end w-100"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i data-lucide="x" class="me-1"></i>Close</button></div></div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // ============================================
    // WEEK LIST TOGGLE FUNCTION
    // ============================================
    window.toggleWeeksList = function(periodId) {
        const weeksList = document.getElementById('weeks-list-' + periodId);
        const toggleBtn = document.querySelector('[onclick*="toggleWeeksList(' + periodId + ')"]');
        if (weeksList) {
            if (weeksList.style.display === 'none' || weeksList.style.display === '') {
                weeksList.style.display = 'flex';
                if (toggleBtn) { const span = toggleBtn.querySelector('span'); if(span) span.textContent = 'Hide'; }
            } else {
                weeksList.style.display = 'none';
                if (toggleBtn) { const span = toggleBtn.querySelector('span'); if(span) span.textContent = 'Show'; }
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    };


    // ============================================
    // PROCESS WEEK FUNCTION - WITH STATUS CHECK
    // ============================================
    window.processWeek = function(periodId, weekId, weekName) {
        Swal.fire({
            title: 'Checking ' + weekName + '...',
            html: '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Checking for pending requests...</p></div>',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: function() {
                fetch('/payroll/weekly-check-pending-requests?payroll_period=' + periodId + '&week_id=' + weekId, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json' }
                })
                .then(function(response) { return response.json(); })
                .then(function(response) {
                    if (response.success) {
                        var data = response.data;
                        if (data.has_pending) {
                            Swal.close();
                            window.showWeeklyPendingRequestsModal(data, periodId, weekId, weekName);
                        } else {
                            Swal.fire({
                                title: 'Process ' + weekName + '?',
                                html: '<div style="background: #dbeafe; padding: 15px; border-radius: 8px; margin-bottom: 15px;"><p style="margin: 0; color: #1e40af;">✅ No pending requests found for ' + weekName + '!</p></div>',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Process Now',
                                confirmButtonColor: '#3b82f6'
                            }).then(function(result) {
                                if (result.isConfirmed) {
                                    window.location.href = '/payroll/weekly-process/' + periodId + '/' + weekId;
                                }
                            });
                        }
                    } else {
                        Swal.fire({ title: 'Error', text: response.message || 'Failed to check pending requests', icon: 'error' });
                    }
                })
                .catch(function(error) {
                    Swal.fire({ title: 'Error', text: 'Failed to check pending requests. Please try again.', icon: 'error' });
                });
            }
        });
    };

    window.showWeeklyPendingRequestsModal = function(data, periodId, weekId, weekName) {
        var existingModal = document.getElementById('weeklyPendingRequestsModal');
        if (existingModal) existingModal.remove();

        var modalHtml = '<div class="modal fade" id="weeklyPendingRequestsModal" tabindex="-1" data-bs-backdrop="static">' +
            '<div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">' +
            '<div class="modal-header bg-warning"><h5 class="modal-title text-white">Pending Requests Found - ' + weekName + '</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>' +
            '<div class="modal-body"><div class="alert alert-warning"><strong>Total Pending Requests:</strong> ' + data.total_pending + ' request(s). Please resolve before processing.</div>';

        if (data.missed_punches && data.missed_punches.exists) {
            modalHtml += '<div class="card border-danger mb-3"><div class="card-header bg-danger bg-opacity-10"><strong>Missed Punches (' + data.missed_punches.count + ')</strong></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Employee</th><th>Date</th><tr></thead><tbody>';
            if (data.missed_punches.details) {
                for (var i = 0; i < data.missed_punches.details.length; i++) {
                    var mp = data.missed_punches.details[i];
                    modalHtml += '<tr><td>' + (mp.employee_name || '') + '<br><small>' + (mp.employee_code || '') + '</small></td><td>' + (mp.date || '') + 'NonNullable</td></tr>';
                }
            }
            modalHtml += '</tbody></table></div></div></div>';
        }

        if (data.leave_requests && data.leave_requests.exists) {
            modalHtml += '<div class="card border-info mb-3"><div class="card-header bg-info bg-opacity-10"><strong>Leave Requests (' + data.leave_requests.count + ')</strong></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Employee</th><th>Dates</th></tr></thead><tbody>';
            if (data.leave_requests.details) {
                for (var i = 0; i < data.leave_requests.details.length; i++) {
                    var lr = data.leave_requests.details[i];
                    modalHtml += '<tr><td>' + (lr.employee_name || '') + '<br><small>' + (lr.employee_code || '') + '</small>NonNullable<td>' + (lr.start_date || '') + ' - ' + (lr.end_date || '') + 'NonNullable</td>';
                }
            }
            modalHtml += '</tbody></table></div></div></div>';
        }

        if (data.overtime_requests && data.overtime_requests.exists) {
            modalHtml += '<div class="card border-success mb-3"><div class="card-header bg-success bg-opacity-10"><strong>Overtime Requests (' + data.overtime_requests.count + ')</strong></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Employee</th><th>Date</th><th>Hours</th></tr></thead><tbody>';
            if (data.overtime_requests.details) {
                for (var i = 0; i < data.overtime_requests.details.length; i++) {
                    var ot = data.overtime_requests.details[i];
                    modalHtml += '<tr><td>' + (ot.employee_name || '') + '<br><small>' + (ot.employee_code || '') + '</small>NonNullable<td>' + (ot.date || '') + 'NonNullable<td>' + (ot.hours || 0) + 'NonNullable</tr>';
                }
            }
            modalHtml += '</tbody></table></div></div></div>';
        }

        modalHtml += '</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>' +
            '<a href="' + (data.missed_punch_url || '#') + '?payroll_id=' + periodId + '&week_id=' + weekId + '" class="btn btn-primary" target="_blank">View All Requests</a></div></div></div></div>';

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        var modalElement = document.getElementById('weeklyPendingRequestsModal');
        var modal = new bootstrap.Modal(modalElement);
        modal.show();
        modalElement.addEventListener('hidden.bs.modal', function() { modalElement.remove(); });
    };

    window.viewWeekResults = function(periodId, weekId, weekName) {
        window.location.href = '/payroll/weekly-process/' + periodId + '/' + weekId;
    };

    window.configureWeeks = function(periodId, monthName) {
        Swal.fire({
            title: 'Configure Weeks',
            text: 'This feature will allow you to configure weeks for ' + monthName,
            icon: 'info',
            confirmButtonText: 'OK'
        });
    };

    // ============================================
    // HOLD DETAILS FUNCTIONS
    // ============================================
    $(document).on('click', '.btn-hold-details', function() {
        var cycleId = $(this).data('cycle-id');
        var cycleMonth = $(this).data('cycle-month');
        $('#holdDetailsSubtitle').text('Payroll Period: ' + cycleMonth);
        var holdDetailsModal = new bootstrap.Modal(document.getElementById('holdDetailsModal'));
        holdDetailsModal.show();
        loadHoldDetails(cycleId);
    });

    function loadHoldDetails(cycleId) {
        $('#holdDetailsLoading').removeClass('d-none');
        $('#holdDetailsContent').addClass('d-none');
        $.ajax({
            url: '/payroll/payroll-new/get-hold-details/' + cycleId,
            type: 'GET',
            success: function(response) {
                if (response.success && response.holds && response.holds.length > 0) {
                    var html = '';
                    $.each(response.holds, function(index, hold) {
                        html += '<tr><td>' + (index + 1) + '</td><td><strong>' + (hold.employee_name || 'N/A') + '</strong><br><small>' + (hold.designation || '') + '</small></td><td>' + (hold.employee_code || 'N/A') + '</td><td>' + (hold.department || 'N/A') + '</td><td>' + (hold.reason || 'No reason provided') + '<br><small class="text-muted">Held on: ' + (hold.held_at || 'N/A') + '</small></td><td><button class="btn btn-sm btn-success" onclick="releaseHold(' + hold.sh_id + ', ' + cycleId + ')">Release</button></td></tr>';
                    });
                    $('#holdsTableBody').html(html);
                    $('#totalHoldsCount').text(response.holds.length + ' Hold(s)');
                    $('#holdDetailsContent').removeClass('d-none');
                } else {
                    $('#noHoldsMessage').removeClass('d-none');
                    $('#totalHoldsCount').text('0 Hold(s)');
                    $('#holdDetailsContent').removeClass('d-none');
                }
                $('#holdDetailsLoading').addClass('d-none');
            },
            error: function() {
                $('#holdDetailsLoading').addClass('d-none');
                $('#errorMessage').removeClass('d-none');
                $('#holdDetailsContent').removeClass('d-none');
            }
        });
    }

    window.releaseHold = function(holdId, cycleId) {
        Swal.fire({
            title: 'Release Salary Hold?',
            text: 'This will allow salary processing for this employee.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Release',
            confirmButtonColor: '#10b981'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/payroll/payroll-new/release-hold',
                    type: 'POST',
                    data: { hold_id: holdId, cycle_id: cycleId, _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Released!', 'Salary hold has been removed.', 'success');
                            loadHoldDetails(cycleId);
                        } else {
                            Swal.fire('Error', response.message || 'Failed to release hold', 'error');
                        }
                    },
                    error: function() { Swal.fire('Error', 'Failed to release hold. Please try again.', 'error'); }
                });
            }
        });
    };

    // ============================================
    // FORM HANDLING FOR NEW CYCLE
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();

        const createCycleBtns = document.querySelectorAll('[data-action="create-cycle"]');
        const payrollModal = new bootstrap.Modal(document.getElementById('addPayrollPeriodModal'));
        const financialYearSelect = document.getElementById('financialYear');
        const payrollTypeSelect = document.getElementById('payrollType');
        const monthSelect = document.getElementById('month');
        const payrollNameInput = document.getElementById('payrollName');
        const attendanceStartDate = document.getElementById('attendanceStartDate');
        const attendanceEndDate = document.getElementById('attendanceEndDate');
        const dateOfPayment = document.getElementById('dateOfPayment');
        const payslipOnlineDate = document.getElementById('payslipOnlineDate');
        const weeklySection = document.getElementById('weeklySection');
        const monthlySection = document.getElementById('monthlySection');
        const weeksContainer = document.getElementById('weeksContainer');
        const addWeekBtn = document.getElementById('addWeekBtn');
        const isWeeklyPayroll = document.getElementById('isWeeklyPayroll');
        const weeklyWeeksData = document.getElementById('weeklyWeeksData');
        const selectedMonthDisplay = document.getElementById('selectedMonthDisplay');
        const editingPayrollIdInput = document.getElementById('editingPayrollId');
        const isEditModeInput = document.getElementById('isEditMode');
        const modalTitle = document.getElementById('addPayrollPeriodLabel');
        const submitText = document.getElementById('submitText');
        const payrollForm = document.getElementById('addPayrollPeriodForm');

        let availableWeeks = [];
        let existingWeekNumbers = [];
        let weeks = [{ id: 1, weekValue: '', start: '', end: '' }];
        let weekCounter = 2;
        let currentYear = new Date().getFullYear();
        let editingCycleId = null;
        let suppressAutoResetWeeks = false;
        const storeWeeklyRoute = "{{ route('payroll.weekly.store') }}";
        const updateCycleRouteTemplate = "{{ route('payroll.weekly.cycle.update', ['payrollId' => '__ID__']) }}";
        const editCycleDataRouteTemplate = "{{ route('payroll.weekly.cycle.edit-data', ['payrollId' => '__ID__']) }}";

        function showSwalValidation(message) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation',
                text: message
            });
        }

        function loadMonthsByFY(fyId) {
            if (!fyId || !monthSelect) return Promise.resolve();
            monthSelect.innerHTML = '<option value="">Loading...</option>';
            monthSelect.disabled = true;
            return fetch('/payroll/get-months/' + fyId).then(function(res) { return res.json(); }).then(function(data) {
                monthSelect.innerHTML = '<option value="">-- Select Month --</option>';
                data.forEach(function(item) {
                    var option = document.createElement('option');
                    option.value = item.value;
                    // Extract month name and year from label (e.g., "August 2025")
                    var labelParts = item.label.split(' ');
                    var monthPart = labelParts[0];
                    var yearPart = labelParts[1] || currentYear;
                    option.setAttribute('data-month-name', monthPart);
                    option.setAttribute('data-year', yearPart);
                    option.textContent = item.label;
                    monthSelect.appendChild(option);
                });
                monthSelect.disabled = false;
                initializePayrollType();
            }).catch(function(error) {
                var monthsList = ['April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March'];
                monthSelect.innerHTML = '<option value="">-- Select Month --</option>';
                monthsList.forEach(function(month) {
                    var option = document.createElement('option');
                    option.value = month;
                    option.setAttribute('data-month-name', month);
                    option.setAttribute('data-year', currentYear);
                    option.textContent = month + ' ' + currentYear;
                    monthSelect.appendChild(option);
                });
                monthSelect.disabled = false;
            });
        }

        function resetModalToCreateMode() {
            editingCycleId = null;
            if (editingPayrollIdInput) editingPayrollIdInput.value = '';
            if (isEditModeInput) isEditModeInput.value = '0';
            if (modalTitle) modalTitle.textContent = 'Add Payroll Period';
            if (submitText) submitText.innerHTML = '<i data-lucide="save" class="me-1"></i>Save Payroll Period';
            if (payrollForm) payrollForm.setAttribute('action', storeWeeklyRoute);
        }

        function setModalToEditMode(cycleId) {
            editingCycleId = cycleId;
            if (editingPayrollIdInput) editingPayrollIdInput.value = String(cycleId);
            if (isEditModeInput) isEditModeInput.value = '1';
            if (modalTitle) modalTitle.textContent = 'Edit Weekly Cycle';
            if (submitText) submitText.innerHTML = '<i data-lucide="save" class="me-1"></i>Update Weekly Cycle';
            if (payrollForm) payrollForm.setAttribute('action', updateCycleRouteTemplate.replace('__ID__', String(cycleId)));
        }

        function canUseWeekNumber(weekNumber) {
            const num = parseInt(weekNumber, 10);
            if (isNaN(num)) return false;
            if (!editingCycleId) {
                return existingWeekNumbers.indexOf(num) === -1;
            }
            return true;
        }

        async function openEditCycleModal(cycleId) {
            try {
                setModalToEditMode(cycleId);
                const url = editCycleDataRouteTemplate.replace('__ID__', String(cycleId));
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const payload = await response.json();
                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'Unable to load cycle details.');
                }
                const data = payload.data || {};
                if (financialYearSelect) financialYearSelect.value = String(data.financial_year || '');
                await loadMonthsByFY(data.financial_year);
                if (monthSelect) monthSelect.value = String(data.month_id || '');
                if (payrollTypeSelect) payrollTypeSelect.value = '441';
                suppressAutoResetWeeks = true;
                handlePayrollTypeChange();
                if (payrollNameInput) payrollNameInput.value = data.payroll_name || '';
                const descriptionInput = document.getElementById('description');
                if (descriptionInput) descriptionInput.value = data.description || '';
                existingWeekNumbers = Array.isArray(data.existing_week_numbers_other_cycles)
                    ? data.existing_week_numbers_other_cycles.map(function(n) { return parseInt(n, 10); }).filter(function(n) { return !isNaN(n); })
                    : [];
                weeks = (data.weeks || []).map(function(w, idx) {
                    return {
                        id: idx + 1,
                        weekValue: String(w.week_number || ''),
                        start: w.start || '',
                        end: w.end || ''
                    };
                });
                if (!weeks.length) weeks = [{ id: 1, weekValue: '', start: '', end: '' }];
                weekCounter = weeks.length + 1;
                renderWeeks();
                suppressAutoResetWeeks = false;
                payrollModal.show();
                if (typeof lucide !== 'undefined') lucide.createIcons();
            } catch (error) {
                suppressAutoResetWeeks = false;
                resetModalToCreateMode();
                Swal.fire('Error', error.message || 'Unable to open edit modal.', 'error');
            }
        }

        function initializePayrollType() {
            var defaultPayrollType = '{{ $defaultPayrollType }}';
            if (defaultPayrollType === '441') {
                payrollTypeSelect.value = '441';
                weeklySection.style.display = 'block';
                monthlySection.style.display = 'none';
                isWeeklyPayroll.value = '1';
                if (attendanceStartDate) attendanceStartDate.required = false;
                if (attendanceEndDate) attendanceEndDate.required = false;
                if (monthSelect.value) handleMonthChange();
            } else {
                payrollTypeSelect.value = '440';
                weeklySection.style.display = 'none';
                monthlySection.style.display = 'block';
                isWeeklyPayroll.value = '0';
                if (attendanceStartDate) attendanceStartDate.required = true;
                if (attendanceEndDate) attendanceEndDate.required = true;
            }
        }

        let selectedYear = new Date().getFullYear();

        function getMonthWeeks(monthName, year) {
            var months = { 'January':0,'February':1,'March':2,'April':3,'May':4,'June':5,'July':6,'August':7,'September':8,'October':9,'November':10,'December':11 };
            var monthIndex = months[monthName];
            if (monthIndex === undefined) return [];

            function formatDate(d) {
                return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
            }

            var weeksList = [];
            var firstDay = new Date(year, monthIndex, 1);
            var lastDay = new Date(year, monthIndex + 1, 0);
            var start = new Date(firstDay);
            var weekNum = 1;

            while (start <= lastDay) {
                var end = new Date(start);
                end.setDate(end.getDate() + 6);
                if (end > lastDay) end = new Date(lastDay);

                weeksList.push({
                    label: 'Week ' + weekNum,
                    value: String(weekNum),
                    start: formatDate(start),
                    end: formatDate(end)
                });

                weekNum++;
                start.setDate(start.getDate() + 7);
            }

            return weeksList;
        }

        function syncWeeksFromInputs() {
            var updated = [];
            document.querySelectorAll('.week-item').forEach(function(item) {
                updated.push({
                    id: parseInt(item.dataset.weekId, 10),
                    weekValue: item.querySelector('.week-number').value,
                    start: item.querySelector('.week-start').value,
                    end: item.querySelector('.week-end').value
                });
            });
            weeks = updated;
        }

        function renderWeeks() {
            if (!weeksContainer) return;
            if (!weeks.length) {
                weeks = [{ id: weekCounter++, weekValue: '', start: '', end: '' }];
            }

            var html = '';
            for (var i = 0; i < weeks.length; i++) {
                var week = weeks[i];
                var selectedInOtherRows = {};
                for (var s = 0; s < weeks.length; s++) {
                    if (s === i) continue;
                    var selectedVal = String(weeks[s].weekValue || '').trim();
                    if (selectedVal) selectedInOtherRows[selectedVal] = true;
                }
                var weekOptions = '<option value="">Select Week</option>';
                var hasMatchingTemplate = false;
                for (var j = 0; j < availableWeeks.length; j++) {
                    var templateWeek = availableWeeks[j];
                    var isSelected = String(week.weekValue || '') === String(templateWeek.value);
                    var weekNumberInt = parseInt(templateWeek.value, 10);
                    var isExistingInDb = !isNaN(weekNumberInt) && existingWeekNumbers.indexOf(weekNumberInt) !== -1;
                    var isDisabled = !isSelected && (!!selectedInOtherRows[String(templateWeek.value)] || isExistingInDb);
                    if (isSelected) hasMatchingTemplate = true;
                    var optionLabel = templateWeek.label + ' (' + templateWeek.start + ' to ' + templateWeek.end + ')';
                    if (isExistingInDb) optionLabel += ' - Already added';
                    weekOptions += '<option value="' + templateWeek.value + '"' + (isSelected ? ' selected' : '') + (isDisabled ? ' disabled' : '') + '>' + optionLabel + '</option>';
                }
                if ((week.weekValue || '') && !hasMatchingTemplate) {
                    weekOptions += '<option value="' + week.weekValue + '" selected>Week ' + week.weekValue + ' (manual)</option>';
                }

                html += '<div class="week-item mb-3" data-week-id="' + week.id + '">' +
                    '<div class="row g-3 align-items-end">' +
                    '<div class="col-md-3"><label class="form-label fw-semibold mb-2">Select Week <span class="text-danger">*</span></label>' +
                    '<select class="form-select week-template">' + weekOptions + '</select></div>' +
                    '<div class="col-md-2"><label class="form-label fw-semibold mb-2">Week No <span class="text-danger">*</span></label>' +
                    '<input type="number" min="1" class="form-control week-number" value="' + (week.weekValue || '') + '" required></div>' +
                    '<div class="col-md-3"><label class="form-label">Start Date <span class="text-danger">*</span></label>' +
                    '<input type="date" class="form-control week-start" value="' + (week.start || '') + '" required></div>' +
                    '<div class="col-md-3"><label class="form-label">End Date <span class="text-danger">*</span></label>' +
                    '<input type="date" class="form-control week-end" value="' + (week.end || '') + '" required></div>' +
                    '<div class="col-md-1">' +
                    (weeks.length > 1
                        ? '<button type="button" class="btn btn-outline-danger remove-week-btn w-100" title="Remove Week"><i data-lucide="trash-2" style="width: 16px; height: 16px;"></i></button>'
                        : '') +
                    '</div></div></div>';
            }

            weeksContainer.innerHTML = html;

            document.querySelectorAll('.remove-week-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var weekId = parseInt(this.closest('.week-item').dataset.weekId, 10);
                    weeks = weeks.filter(function(w) { return w.id !== weekId; });
                    renderWeeks();
                });
            });

            document.querySelectorAll('.week-number, .week-start, .week-end').forEach(function(input) {
                input.addEventListener('change', syncWeeksFromInputs);
            });
            document.querySelectorAll('.week-template').forEach(function(selectEl) {
                selectEl.addEventListener('change', function() {
                    var row = this.closest('.week-item');
                    var selectedValue = this.value;
                    var chosenWeek = null;
                    for (var k = 0; k < availableWeeks.length; k++) {
                        if (String(availableWeeks[k].value) === String(selectedValue)) {
                            chosenWeek = availableWeeks[k];
                            break;
                        }
                    }
                    if (chosenWeek) {
                        row.querySelector('.week-number').value = chosenWeek.value; 
                        row.querySelector('.week-start').value = chosenWeek.start;
                        row.querySelector('.week-end').value = chosenWeek.end;
                        syncWeeksFromInputs();
                    } else {
                        row.querySelector('.week-number').value = '';
                        row.querySelector('.week-start').value = '';
                        row.querySelector('.week-end').value = '';
                        syncWeeksFromInputs();
                    }
                });
            });

            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function handleMonthChange() {
            var selectedOption = monthSelect.options[monthSelect.selectedIndex];
            if (!selectedOption || !selectedOption.value) return;
            
            var monthName = selectedOption.getAttribute('data-month-name');
            if (!monthName) monthName = selectedOption.textContent.split(' ')[0];
            
            // Extract year from the selected option text (e.g., "August 2025" -> 2025)
            var optionText = selectedOption.textContent;
            var yearMatch = optionText.match(/\d{4}/);
            if (yearMatch) {
                selectedYear = parseInt(yearMatch[0]);
                currentYear = selectedYear; // Update the currentYear variable
            }
            
            if (selectedMonthDisplay) selectedMonthDisplay.textContent = monthName + ' ' + selectedYear;
            
            var isWeekly = payrollTypeSelect.value === '441';
            if (isWeekly && payrollNameInput) {
                payrollNameInput.value = 'Weekly Payroll - ' + monthName + ' ' + selectedYear;
            } else if (payrollNameInput && payrollTypeSelect.value === '440') {
                payrollNameInput.value = 'Monthly Payroll - ' + monthName + ' ' + selectedYear;
            }
            
            availableWeeks = getMonthWeeks(monthName, selectedYear);
            existingWeekNumbers = [];

            var financialYearValue = financialYearSelect ? financialYearSelect.value : '';
            var monthValue = monthSelect ? monthSelect.value : '';
            var endpoint = '/payroll/get-weeks-for-month?month=' + encodeURIComponent(monthName) +
                '&year=' + encodeURIComponent(selectedYear) +
                '&financial_year=' + encodeURIComponent(financialYearValue) +
                '&month_id=' + encodeURIComponent(monthValue) +
                '&exclude_payroll_id=' + encodeURIComponent(editingCycleId || '');

            fetch(endpoint)
                .then(function(res) { return res.json(); })
                .then(function(payload) {
                    if (payload && Array.isArray(payload.weeks) && payload.weeks.length) {
                        availableWeeks = payload.weeks.map(function(w) {
                            return {
                                value: String(w.week_number || w.value),
                                label: 'Week ' + (w.week_number || w.value),
                                start: w.start_date || w.start,
                                end: w.end_date || w.end
                            };
                        });
                    }
                    existingWeekNumbers = Array.isArray(payload && payload.existing_week_numbers)
                        ? payload.existing_week_numbers.map(function(n) { return parseInt(n, 10); }).filter(function(n) { return !isNaN(n); })
                        : [];

                    if (payrollTypeSelect.value === '441' && !suppressAutoResetWeeks) {
                        weeks = [{ id: 1, weekValue: '', start: '', end: '' }];
                        weekCounter = 2;
                        renderWeeks();
                    }

                    if (existingWeekNumbers.length > 0 && typeof Swal !== 'undefined') {
                        var allWeekNumbers = availableWeeks
                            .map(function(w) { return parseInt(w.value, 10); })
                            .filter(function(n) { return !isNaN(n); });
                        var remainingWeeks = allWeekNumbers.filter(function(n) { return existingWeekNumbers.indexOf(n) === -1; });
                        Swal.fire({
                            icon: 'info',
                            title: 'Existing weekly cycle found',
                            html: 'Week(s) already added for this month: <b>' + existingWeekNumbers.join(', ') + '</b><br>' +
                                (remainingWeeks.length
                                    ? 'Please add remaining week(s): <b>' + remainingWeeks.join(', ') + '</b>.'
                                    : 'All predefined weeks are already added for this month.'),
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(function() {
                    if (payrollTypeSelect.value === '441' && !suppressAutoResetWeeks) {
                        weeks = [{ id: 1, weekValue: '', start: '', end: '' }];
                        weekCounter = 2;
                        renderWeeks();
                    }
                });
        }

        function handlePayrollTypeChange() {
            var isWeekly = payrollTypeSelect.value === '441';
            if (isWeekly) {
                weeklySection.style.display = 'block';
                monthlySection.style.display = 'none';
                isWeeklyPayroll.value = '1';
                if (attendanceStartDate) attendanceStartDate.required = false;
                if (attendanceEndDate) attendanceEndDate.required = false;
                if (monthSelect.value) handleMonthChange();
            } else {
                weeklySection.style.display = 'none';
                monthlySection.style.display = 'block';
                isWeeklyPayroll.value = '0';
                if (attendanceStartDate) attendanceStartDate.required = true;
                if (attendanceEndDate) attendanceEndDate.required = true;
            }
        }

        if (addWeekBtn) {
            addWeekBtn.addEventListener('click', function() {
                syncWeeksFromInputs();
                weeks.push({ id: weekCounter++, weekValue: '', start: '', end: '' });
                renderWeeks();
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        }

        if (payrollForm) {
            payrollForm.addEventListener('submit', function(e) {
                if (isWeeklyPayroll.value === '1') {
                    syncWeeksFromInputs();
                    var weeksData = [];
                    var seenWeekNumbers = {};
                    for (var i = 0; i < weeks.length; i++) {
                        var weekNumber = parseInt(weeks[i].weekValue, 10);
                        if (!weekNumber || !weeks[i].start || !weeks[i].end) {
                            e.preventDefault();
                            showSwalValidation('Please fill week number, start date, and end date for all rows.');
                            return;
                        }
                        if (seenWeekNumbers[weekNumber]) {
                            e.preventDefault();
                            showSwalValidation('Duplicate week number selected: ' + weekNumber + '. Please keep each week unique.');
                            return;
                        }
                        seenWeekNumbers[weekNumber] = true;
                        if (!canUseWeekNumber(weekNumber)) {
                            e.preventDefault();
                            showSwalValidation('Week ' + weekNumber + ' is already added for this month. Please choose remaining week(s).');
                            return;
                        }

                        var startDate = new Date(weeks[i].start);
                        var endDate = new Date(weeks[i].end);
                        if (endDate < startDate) {
                            e.preventDefault();
                            showSwalValidation('Week end date cannot be before start date.');
                            return;
                        }

                        weeksData.push({ week_value: String(weekNumber), week_number: weekNumber, start: weeks[i].start, end: weeks[i].end });
                    }
                    if (weeksData.length === 0) {
                        e.preventDefault();
                        showSwalValidation('Please add at least one week for weekly payroll.');
                        return;
                    }
                    weeklyWeeksData.value = JSON.stringify(weeksData);
                }

                var editMode = isEditModeInput && isEditModeInput.value === '1';
                if (editMode && editingCycleId) {
                    e.preventDefault();
                    var formData = new FormData(payrollForm);
                    fetch(updateCycleRouteTemplate.replace('__ID__', String(editingCycleId)), {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData
                    })
                    .then(function(res) { return res.json().then(function(data) { return { ok: res.ok, data: data }; }); })
                    .then(function(result) {
                        if (!result.ok || !result.data.success) {
                            throw new Error(result.data.message || 'Failed to update cycle.');
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated',
                            text: result.data.message || 'Weekly cycle updated successfully.'
                        }).then(function() {
                            window.location.reload();
                        });
                    })
                    .catch(function(err) {
                        Swal.fire('Error', err.message || 'Failed to update cycle.', 'error');
                    });
                }
            });
        }

        var today = new Date().toISOString().split('T')[0];
        if (dateOfPayment) dateOfPayment.value = today;
        if (payslipOnlineDate) payslipOnlineDate.value = today;
        if (monthSelect) monthSelect.addEventListener('change', handleMonthChange);
        if (payrollTypeSelect) payrollTypeSelect.addEventListener('change', handlePayrollTypeChange);
        if (financialYearSelect) financialYearSelect.addEventListener('change', function() { loadMonthsByFY(this.value); });
        if (financialYearSelect && financialYearSelect.value) loadMonthsByFY(financialYearSelect.value);
        if (createCycleBtns.length) {
            createCycleBtns.forEach(function(createCycleBtn) {
                createCycleBtn.addEventListener('click', function() {
                    resetModalToCreateMode();
                    suppressAutoResetWeeks = false;
                    if (payrollForm) payrollForm.reset();
                    payrollModal.show();
                    existingWeekNumbers = [];
                    weeks = [];
                    weekCounter = 2;
                    weeksContainer.innerHTML = '<div class="text-center py-5 text-muted"><i data-lucide="calendar" style="width: 48px; height: 48px;"></i><p class="mt-3 mb-0">Please select cycle month. Weeks auto-load first; you can then adjust week dates manually (cross-month is allowed).</p></div>';
                    if (selectedMonthDisplay) selectedMonthDisplay.textContent = '';
                    initializePayrollType();
                    if (dateOfPayment) dateOfPayment.value = today;
                    if (payslipOnlineDate) payslipOnlineDate.value = today;
                    if (financialYearSelect && financialYearSelect.value) loadMonthsByFY(financialYearSelect.value);
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            });
        }

        $(document).on('click', '.edit-cycle-btn', function() {
            var cycleId = parseInt($(this).data('cycle-id'), 10);
            if (!cycleId) return;
            openEditCycleModal(cycleId);
        });
    });
</script>
@endsection