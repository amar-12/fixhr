@extends('admin.layout.master')

@section('title', 'Payroll Cycles')

@section('content')
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

<div class="payroll-cycles-page">
<div class="row mt-5">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <!-- Card Header -->
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-3">
                <div>
                    <h4 class="card-title mb-0 d-flex align-items-center">
                        <i data-lucide="calendar" class="text-primary me-2"></i>
                        Payroll Cycles
                    </h4>
                    @php
                        $cyclesCountHeader = 0;
                        if (is_countable($cycles)) {
                            $cyclesCountHeader = count($cycles);
                        } elseif (is_object($cycles) && method_exists($cycles, 'count')) {
                            $cyclesCountHeader = $cycles->count();
                        }
                    @endphp
                    <span class="text-muted small mt-1 d-block">{{ $cyclesCountHeader }} period(s) in selected year</span>
                </div>

                <div class="d-flex flex-column flex-sm-row flex-wrap align-items-stretch align-items-sm-center justify-content-md-end gap-2 gap-sm-3 ms-md-auto payroll-cycles-header-actions">
                    @if($financialYears->isNotEmpty())
                    <div class="payroll-cycles-fy-wrap d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-1 gap-sm-2">
                        <label for="payrollCyclesFySelect" class="form-label mb-0 small text-nowrap">Financial year</label>
                        <select id="payrollCyclesFySelect" class="form-select form-select-sm payroll-cycles-fy-select" autocomplete="off" aria-label="Select financial year">
                            @foreach ($financialYears as $year)
                            <option value="{{ $year->fy_id }}" @selected($currentFY && (int) $currentFY->fy_id === (int) $year->fy_id)>
                                {{ $year->fy_year }}@if($year->fy_is_current) — Current @endif
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <button type="button" data-action="create-cycle" class="btn btn-outline-primary btn-sm d-flex align-items-center">
                        <i data-lucide="plus" class="me-1" style="width: 14px; height: 14px;"></i>
                        New Cycle
                    </button>
                </div>
            </div>

            <div class="card-body">
                <!-- Statistics Cards -->
                <div class="card-body">
                    @php
                    // Define status groups for counting
                    $inProgressStatuses = ['open', 'attendance_frozen', 'processing', 'pending_approval', 'under_review', 'in_process', 'verification'];
                    $completedStatus = 'finalized_locked';
                    $upcomingStatuses = ['upcoming', 'expired'];

                    // Helper function to count by specific status
                    function countByStatus($cyclesArray, $status) {
                        return count(array_filter($cyclesArray, function($cycle) use ($status) {
                            return isset($cycle['status']) && $cycle['status'] === $status;
                        }));
                    }

                    // Helper function to count by multiple statuses
                    function countInStatuses($cyclesArray, $statuses) {
                        return count(array_filter($cyclesArray, function($cycle) use ($statuses) {
                            return isset($cycle['status']) && in_array($cycle['status'], $statuses);
                        }));
                    }

                    // Helper function to sum hold counts
                    function sumHoldCount($cyclesArray) {
                        return array_sum(array_column($cyclesArray, 'hold_count'));
                    }

                    // Convert cycles to array for safe operations
                    $cyclesArray = [];
                    $cyclesCount = 0;

                    if (is_countable($cycles)) {
                        $cyclesArray = $cycles instanceof \Illuminate\Support\Collection ? $cycles->toArray() : (array)$cycles;
                        $cyclesCount = count($cyclesArray);
                    } elseif (is_object($cycles) && method_exists($cycles, 'toArray')) {
                        $cyclesArray = $cycles->toArray();
                        $cyclesCount = count($cyclesArray);
                    }
                @endphp


                    <!-- Statistics Cards - Single Line Grid -->
                    @if($cyclesCount > 0)
                    <div class="row g-3 mb-4">
                        <!-- Card 1: Total Periods -->
                        <div class="col">
                            <div class="card stat-card border-0 bg-opacity-5 h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-opacity-10 bg-primary rounded-circle p-2 me-3">
                                            <i data-lucide="calendar" class="text-primary"
                                                style="width: 20px; height: 20px;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-0">{{ $cyclesCount }}</h5>
                                            <small class="text-muted">Total Periods</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Completed -->
                        <div class="col">
                            <div class="card stat-card border-0 bg-opacity-5 h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-opacity-10 bg-success rounded-circle p-2 me-3">
                                            <i data-lucide="check-circle" class="text-success"
                                                style="width: 20px; height: 20px;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-0">{{ countByStatus($cyclesArray, 'finalized_locked') }}</h5>
                                            <small class="text-muted">Completed & Locked</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Card 3: In Progress -->
                        <div class="col">
                            <div class="card stat-card border-0 bg-opacity-5 h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-opacity-10 bg-warning rounded-circle p-2 me-3">
                                            <i data-lucide="play-circle" class="text-warning"
                                                style="width: 20px; height: 20px;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-0">{{ countInStatuses($cyclesArray, $inProgressStatuses) }}</h5>
                                            <small class="text-muted">In Progress</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Card 4: Upcoming/Expired -->
                        <div class="col">
                            <div class="card stat-card border-0 bg-opacity-5 h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-opacity-10 bg-info rounded-circle p-2 me-3">
                                            <i data-lucide="clock" class="text-info"
                                                style="width: 20px; height: 20px;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-0">{{ countInStatuses($cyclesArray, ['upcoming', 'expired']) }}</h5>
                                            <small class="text-muted">Upcoming/Expired</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 5: Total Holds -->
                        <div class="col">
                            <div class="card stat-card border-0 bg-opacity-5 h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-opacity-10 bg-danger rounded-circle p-2 me-3">
                                            <i data-lucide="pause" class="text-danger"
                                                style="width: 20px; height: 20px;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-0">{{ sumHoldCount($cyclesArray) }}</h5>
                                            <small class="text-muted">Total Holds</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 4x3 Grid Layout (4 columns for 4 quarters, 3 rows for months) -->
                    @if($cyclesCount === 0)
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i data-lucide="calendar" class="text-muted" style="width: 64px; height: 64px;"></i>
                        </div>
                        <h5 class="text-muted mb-2">No Payroll Cycles Found</h5>
                        <p class="text-muted mb-4">
                            No payroll periods found for <strong>{{ $currentFY->fy_year ?? 'selected' }}</strong>
                            financial year.
                            <br>Create a new payroll cycle to get started.
                        </p>
                        <div class="d-flex justify-content-center gap-3">
                            <button type="button" data-action="create-cycle" class="btn btn-primary">
                                <i data-lucide="plus" class="me-1"></i>
                                Create New Cycle
                            </button>
                        </div>
                    </div>
                    @else
                    @php
                    // Group cycles by quarter (Indian Financial Year)
                    $quarters = [
                    'Q1' => [
                    'name' => 'Q1 (Apr-Jun)',
                    'months' => ['April', 'May', 'June'],
                    'color' => 'primary',
                    'icon' => 'trending-up'
                    ],
                    'Q2' => [
                    'name' => 'Q2 (Jul-Sep)',
                    'months' => ['July', 'August', 'September'],
                    'color' => 'success',
                    'icon' => 'trending-up'
                    ],
                    'Q3' => [
                    'name' => 'Q3 (Oct-Dec)',
                    'months' => ['October', 'November', 'December'],
                    'color' => 'warning',
                    'icon' => 'trending-up'
                    ],
                    'Q4' => [
                    'name' => 'Q4 (Jan-Mar)',
                    'months' => ['January', 'February', 'March'],
                    'color' => 'info',
                    'icon' => 'trending-up'
                    ]
                    ];

                    // Sort cycles in financial year order (April to March)
                    usort($cyclesArray, function($a, $b) {
                    $monthOrder = [
                    'April' => 1, 'May' => 2, 'June' => 3,
                    'July' => 4, 'August' => 5, 'September' => 6,
                    'October' => 7, 'November' => 8, 'December' => 9,
                    'January' => 10, 'February' => 11, 'March' => 12
                    ];
                    return ($monthOrder[$a['month']] ?? 0) - ($monthOrder[$b['month']] ?? 0);
                    });

                    // Group by month names
                    $groupedCycles = [];
                    foreach ($cyclesArray as $cycle) {
                    $groupedCycles[$cycle['month']] = $cycle;
                    }

                    // Prepare data for 4 columns (one for each quarter)
                    $columnsData = [[], [], [], []];

                    // Fill each column with its quarter months
                    $quarterIndex = 0;
                    foreach ($quarters as $quarterKey => $quarterData) {
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

                    <!-- 4 Column Layout -->
                    <div class="row g-3">
                        @foreach($columnsData as $quarterIndex => $quarterCycles)
                        @php
                        $quarterKeys = array_keys($quarters);
                        $quarterKey = $quarterKeys[$quarterIndex] ?? 'Q1';
                        $quarterData = $quarters[$quarterKey] ?? $quarters['Q1'];
                        @endphp

                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                            <!-- Quarter Header -->
                            <div class="card border-0 bg-{{ $quarterData['color'] }}-subtle mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-opacity-25 rounded-circle p-2 me-2">
                                                <i data-lucide="{{ $quarterData['icon'] }}"
                                                    class="text-{{ $quarterData['color'] }}"
                                                    style="width: 16px; height: 16px;"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-body">{{ $quarterData['name'] }}</h6>
                                                <small class="text-muted">{{ count($quarterCycles) }} month(s)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quarter Months -->
                            @if(empty($quarterCycles))
                            <div class="text-center py-4">
                                <div class="mb-2">
                                    <i data-lucide="calendar-off" class="text-muted"
                                        style="width: 32px; height: 32px;"></i>
                                </div>
                                <p class="text-muted small mb-0">No cycles for this quarter</p>
                            </div>
                            @else
                            @foreach($quarterCycles as $cycle)
                            <!-- Update this section in your blade template -->
                                @php
                                    $ui = $cycle['ui'] ?? [];
                                    $status = $cycle['status'] ?? 'upcoming';

                                    // Define which statuses should block card click
                                    // FIXED: Change 'payroll_locked' to 'finalized_locked'
                                    $blockedStatuses = ['upcoming', 'expired', 'finalized_locked', 'PAYROLL_LOCKED'];
                                    $isDisabled = in_array($status, $blockedStatuses);

                                    // Show "View Payslips" button only when status is 'finalized_locked'
                                    // FIXED: Change 'payroll_locked' to 'finalized_locked'
                                    $showViewPayslips = $status === 'finalized_locked' || $status === 'PAYROLL_LOCKED';

                                    // Define action URLs based on status
                                    $actionUrl = '';
                                    $actionText = '';
                                    $actionIcon = '';
                                    $actionClass = '';
                                    $isActionDisabled = false;

                                    switch($status) {
                                        case 'open':
                                        case 'attendance_frozen':
                                            $actionUrl = route('payroll.new.process', ['period' => $cycle['id']]);
                                            $actionText = 'Process Payroll';
                                            $actionIcon = 'play-circle';
                                            $actionClass = 'btn-outline-primary';
                                            break;

                                        case 'processing':
                                            $actionUrl = route('payroll.new.process', ['period' => $cycle['id']]);
                                            $actionText = 'Continue Processing';
                                            $actionIcon = 'refresh-cw';
                                            $actionClass = 'btn-outline-primary';
                                            break;

                                        case 'pending_approval':
                                        case 'under_review':
                                            $actionText = 'Awaiting Approval';
                                            $actionIcon = 'clock';
                                            $actionClass = 'btn-warning';
                                            $isActionDisabled = true;
                                            break;

                                        case 'in_process':
                                        case 'verification':
                                            $actionText = 'In Process';
                                            $actionIcon = 'cog';
                                            $actionClass = 'btn-info';
                                            $isActionDisabled = true;
                                            break;

                                        case 'finalized_locked':
                                        case 'PAYROLL_LOCKED': // ✅ Added case for PAYROLL_LOCKED
                                            // View Payslips button is handled separately
                                            $actionText = 'View Payslips';
                                            $actionIcon = 'eye';
                                            $actionClass = 'btn-success';
                                            break;


                                        case 'expired':
                                            $actionText = 'Expired';
                                            $actionIcon = 'calendar-x';
                                            $actionClass = 'btn-secondary';
                                            $isActionDisabled = true;
                                            break;

                                        default:
                                            $actionText = 'Not Available';
                                            $actionIcon = 'lock';
                                            $actionClass = 'btn-light text-muted';
                                            $isActionDisabled = true;
                                    }
                                @endphp
                            <div class="mb-3">
                                <div class="card cycle-card h-100 border-0 shadow-sm hover-shadow"
                                    data-cycle-id="{{ $cycle['id'] }}" data-status="{{ $status }}" style="border-left: 4px solid var(--bs-{{ $ui['color'] ?? 'primary' }});
                                                           {{ $isDisabled ? 'opacity: 0.8;' : '' }}">

                                    <div class="card-body d-flex flex-column p-3" style="border-radius: 12px; border: 1px solid #e5e7eba1;">

                                        <!-- Header with Month and Status -->
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <div class="d-flex align-items-center mb-1">
                                                    <div class="bg-{{ $ui['color'] ?? 'primary' }}-subtle rounded-circle p-1 me-2">
                                                        <i data-lucide="calendar" class="text-{{ $ui['color'] ?? 'primary' }}"
                                                            style="width: 14px; height: 14px;"></i>
                                                    </div>
                                                    <h6 class="card-title mb-0 text-body">{{ $cycle['month'] ?? 'N/A' }}</h6>
                                                </div>
                                                <div class="text-muted small">
                                                    <i data-lucide="calendar-range" class="me-1"
                                                        style="width: 12px; height: 12px;"></i>
                                                    {{ $cycle['start'] ?? 'N/A' }} – {{ $cycle['end'] ?? 'N/A' }}
                                                </div>
                                            </div>

                                            <span
                                                class="badge bg-{{ $ui['color'] ?? 'primary' }}-subtle text-{{ $ui['color'] ?? 'primary' }} border border-{{ $ui['color'] ?? 'primary' }} border-opacity-25 d-flex align-items-center">
                                                <i data-lucide="{{ $ui['icon'] ?? 'clock' }}" class="me-1"
                                                    style="width: 12px; height: 12px;"></i>
                                                {{ $ui['label'] ?? 'Upcoming' }}
                                            </span>
                                        </div>

                                        <!-- Employee Count -->
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="bg-secondary bg-opacity-10 rounded-circle p-1 me-2">
                                                <i data-lucide="users" class="text-muted"
                                                    style="width: 12px; height: 12px;"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-body small">{{ $cycle['employees'] ?? 0 }}</div>
                                                <div class="text-muted small">Employees</div>
                                            </div>
                                        </div>

                                        <!-- SALARY HOLDS COUNT - NEW SECTION -->
                                        @if(!empty($cycle['has_holds']))
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="bg-danger-subtle rounded-circle p-1 me-2">
                                                <i data-lucide="pause" class="text-danger"
                                                    style="width: 12px; height: 12px;"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-danger small">{{ $cycle['hold_count'] ?? 0 }} Salary
                                                    Hold(s)</div>
                                                <div class="text-muted small">Requires attention</div>
                                            </div>
                                            <button type="button"
                                                class="ms-auto btn btn-sm btn-outline-danger btn-hold-details"
                                                data-cycle-id="{{ $cycle['id'] }}"
                                                data-cycle-month="{{ $cycle['month'] ?? 'N/A' }}" data-bs-toggle="tooltip"
                                                data-bs-title="View held employees">
                                                <i data-lucide="eye" style="width: 12px; height: 12px;"></i>
                                            </button>
                                        </div>
                                        @else
                                        <!-- Show green check when no holds -->
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="bg-success-subtle rounded-circle p-1 me-2">
                                                <i data-lucide="check-circle" class="text-success"
                                                    style="width: 12px; height: 12px;"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-success small">No Salary Holds</div>
                                                <div class="text-muted small">All clear</div>
                                            </div>
                                        </div>
                                        @endif


                                        <!-- Cheque Number -->
                                        @if(!empty($cycle['cheque_number']))
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="bg-secondary bg-opacity-10 rounded-circle p-1 me-2">
                                                <i data-lucide="file-text" class="text-muted"
                                                    style="width: 12px; height: 12px;"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-body cheque-number small">{{
                                                    $cycle['cheque_number'] }}</div>
                                                <div class="text-muted small">Cheque No.</div>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Progress Bar -->
                                        @if(!in_array($status, ['upcoming', 'expired']))
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between small mb-1">
                                                <span class="text-muted">Progress</span>
                                                <span class="fw-semibold small">{{ $ui['progress'] ?? 0 }}%</span>
                                            </div>
                                            <div class="progress" style="height: 4px;">
                                                <div class="progress-bar bg-{{ $ui['color'] ?? 'primary' }}"
                                                    style="width: {{ $ui['progress'] ?? 0 }}%" role="progressbar"></div>
                                            </div>
                                        </div>
                                        @endif

                                         <!-- Actions -->
                                        <div class="mt-auto pt-2">
                                            @if($showViewPayslips)
                                                <!-- View Payslips Button - Show for both finalized_locked and PAYROLL_LOCKED -->
                                                <a href="{{ route('payroll.payslip.list', ['payrollId' => $cycle['id']]) }}"
                                                    class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                                                    <i data-lucide="eye" style="width: 12px; height: 12px;"></i>
                                                    View Payslips
                                                </a>
                                            @else
                                                <!-- Show appropriate action button based on status -->
                                                @if($isActionDisabled)
                                                    <button class="btn {{ $actionClass }} btn-sm w-100 d-flex align-items-center justify-content-center gap-2"
                                                            disabled>
                                                        <i data-lucide="{{ $actionIcon }}" style="width: 12px; height: 12px;"></i>
                                                        {{ $actionText }}
                                                    </button>
                                                @else
                                                    <a href="{{ $actionUrl }}"
                                                        class="btn {{ $actionClass }} btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                                                        <i data-lucide="{{ $actionIcon }}" style="width: 12px; height: 12px;"></i>
                                                        {{ $actionText }}
                                                    </a>
                                                @endif
                                            @endif
                                        </div>


                                        <!-- Actions -->
                                        {{-- <div class="mt-auto pt-2">
                                            @if(!empty($showViewReport))
                                            <!-- View Payslips Button -->
                                            <a href="{{ route('payroll.payslip.list', ['payrollId' => $cycle['id']]) }}"
                                                class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                                                <i data-lucide="eye" style="width: 12px; height: 12px;"></i>
                                                View Payslips
                                            </a>
                                            @else
                                            @switch($status)
                                            @case('open')
                                            @case('attendance_frozen')
                                            @case('processing')
                                            <a href="{{ route('payroll.new.process', ['period' => $cycle['id']]) }}"
                                                class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                                                <i data-lucide="play-circle" style="width: 12px; height: 12px;"></i>
                                                Process Payroll
                                            </a>
                                            @break
                                            @case('under_review')
                                            @case('pending_approval')
                                            <button
                                                class="btn btn-warning btn-sm w-100 d-flex align-items-center justify-content-center gap-2"
                                                disabled>
                                                <i data-lucide="clock" style="width: 12px; height: 12px;"></i>
                                                Awaiting Approval
                                            </button>
                                            @break
                                            @case('completed')
                                            <a href="{{ url('/payroll/reports?period='.$cycle['id']) }}"
                                                class="btn btn-success btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                                                <i data-lucide="file-text" style="width: 12px; height: 12px;"></i>
                                                View Reports
                                            </a>
                                            @break
                                            @case('expired')
                                            <button
                                                class="btn btn-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2"
                                                disabled>
                                                <i data-lucide="calendar-x" style="width: 12px; height: 12px;"></i>
                                                Expired
                                            </button>
                                            @break
                                            @default
                                            <button
                                                class="btn btn-light btn-sm w-100 text-muted d-flex align-items-center justify-content-center gap-2"
                                                disabled>
                                                <i data-lucide="lock" style="width: 12px; height: 12px;"></i>
                                                Not Available
                                            </button>
                                            @endswitch
                                            @endif
                                        </div> --}}

                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

    <!-- Payroll Period Modal -->
    <div class="modal fade" id="addPayrollPeriodModal" tabindex="-1" aria-labelledby="addPayrollPeriodLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPayrollPeriodLabel">Add Payroll Period</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Alert Messages -->
                    <div id="alertContainer" class="mb-3"></div>

                    <form id="addPayrollPeriodForm" action="#" method="POST">
                        @csrf
                        <input type="hidden" name="pp_id" id="pp_id">
                        <div class="row">

                            <!-- Year -->
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="year" class="form-label">Financial Year <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="year" name="year" required>
                                        <option value="">-- Select Financial Year --</option>
                                        @foreach ($financialYears as $year)
                                        <option value="{{ $year->fy_id }}" {{ $currentFY && $currentFY->fy_id ==
                                            $year->fy_id ? 'selected' : '' }}>
                                            {{ $year->fy_year }}
                                            @if($year->fy_is_current) (Current) @endif
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="yearError"></div>
                                </div>
                            </div>

                            <!-- Payroll Type -->
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="payroll_type" class="form-label">Payroll Type <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="payroll_type" name="payroll_type" required>
                                        @if ($paymentCycle)
                                        <option value="{{ $paymentCycle->m_id }}" selected>{{ $paymentCycle->m_name }}
                                        </option>
                                        @else
                                        <option value="">Select Payroll Type</option>
                                        @endif
                                    </select>
                                    <div class="invalid-feedback" id="payroll_typeError"></div>
                                </div>
                            </div>

                            <!-- Quarter -->
                            <input type="hidden" name="payroll_quarter" id="payroll_quarter" value="">

                            <!-- Month -->
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="month" class="form-label">Month <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="month" name="month" required>
                                        <option value="">-- Select Month --</option>
                                    </select>
                                    <div class="invalid-feedback" id="monthError"></div>
                                    <small class="text-muted">Only current quarter months are enabled</small>
                                </div>
                            </div>

                            <!-- Payroll Name -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Payroll Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                    <div class="invalid-feedback" id="nameError"></div>
                                </div>
                            </div>

                            <!-- Cheque Number -->
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="cheque_number" class="form-label">Cheque Number</label>
                                    <input type="text" class="form-control" id="cheque_number" name="cheque_number"
                                        placeholder="Optional cheque number">
                                    <div class="invalid-feedback" id="cheque_numberError"></div>
                                </div>
                            </div>

                            <!-- Attendance Start & End -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="attendance_start_date" class="form-label">Attendance Start Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="attendance_start_date"
                                        name="attendance_start_date" required>
                                    <div class="invalid-feedback" id="attendance_start_dateError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="attendance_end_date" class="form-label">Attendance End Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="attendance_end_date"
                                        name="attendance_end_date" required>
                                    <div class="invalid-feedback" id="attendance_end_dateError"></div>
                                </div>
                            </div>

                            <!-- Payment Dates -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_of_payment" class="form-label">Date of Payment <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date_of_payment" name="date_of_payment"
                                        required>
                                    <div class="invalid-feedback" id="date_of_paymentError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payslip_online_date" class="form-label">Payslip Online Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="payslip_online_date"
                                        name="payslip_online_date" required>
                                    <div class="invalid-feedback" id="payslip_online_dateError"></div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description"
                                        rows="2"></textarea>
                                </div>
                            </div>

                            <!-- Modal Actions -->
                            <div class="col-md-12 text-end mt-3">
                                <button type="submit" class="btn btn-outline-primary" id="submitBtn">
                                    <span id="submitText">
                                        <i data-lucide="save" class="me-1" style="width: 14px; height: 14px;"></i>
                                        Save Payroll Period
                                    </span>
                                    <span id="loadingSpinner" class="d-none">
                                        <span class="spinner-border spinner-border-sm me-1"></span>
                                        Saving...
                                    </span>
                                </button>
                                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">
                                    <i data-lucide="x" class="me-1" style="width: 14px; height: 14px;"></i>
                                    Cancel
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>




<!-- Hold Details Modal -->
<div class="modal fade" id="holdDetailsModal" tabindex="-1" aria-labelledby="holdDetailsModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <div class="bg-danger-subtle rounded-circle p-2 me-3">
                        <i data-lucide="pause" class="text-danger" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="holdDetailsModalLabel">Salary Holds Details</h5>
                        <small class="text-muted" id="holdDetailsSubtitle"></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Loading State -->
                <div id="holdDetailsLoading" class="text-center py-5">
                    <div class="spinner-border text-danger" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-3">Loading hold details...</p>
                </div>

                <!-- Content Area -->
                <div id="holdDetailsContent" class="d-none">
                    <!-- Summary Card -->
                    <div class="card border-danger-subtle mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-danger-subtle rounded-circle p-2 me-3">
                                            <i data-lucide="alert-circle" class="text-danger"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0" id="totalHoldsCount">0 Hold(s)</h6>
                                            <small class="text-muted">Total employees on hold</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-warning-subtle rounded-circle p-2 me-3">
                                            <i data-lucide="alert-triangle" class="text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0" id="pendingActions">0 Pending</h6>
                                            <small class="text-muted">Actions required</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Holds Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="holdsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Employee</th>
                                    <th>Employee ID</th>
                                    <th>Department</th>
                                    <th>Hold Reason</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="holdsTableBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div id="noHoldsMessage" class="text-center py-5 d-none">
                        <div class="mb-3">
                            <i data-lucide="check-circle" class="text-success" style="width: 64px; height: 64px;"></i>
                        </div>
                        <h5 class="text-success mb-2">No Salary Holds</h5>
                        <p class="text-muted">All employees are ready for payroll processing.</p>
                    </div>

                    <!-- Error State -->
                    <div id="errorMessage" class="text-center py-5 d-none">
                        <div class="mb-3">
                            <i data-lucide="alert-octagon" class="text-danger" style="width: 64px; height: 64px;"></i>
                        </div>
                        <h5 class="text-danger mb-2">Unable to Load Data</h5>
                        <p class="text-muted" id="errorText">Failed to load hold details. Please try again.</p>
                        <button class="btn btn-outline-danger mt-2" id="retryButton">
                            <i data-lucide="refresh-cw" class="me-1"></i>
                            Retry
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top">
                <div class="d-flex justify-content-between w-100">
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i data-lucide="x" class="me-1"></i>
                            Close
                        </button>
                    </div>
                    <div>
                       <a href="#" id="processPayrollLink"
                            class="btn btn-outline-primary d-flex align-items-center">
                                <i data-lucide="play" class="me-1"></i>
                                Process Payroll
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    @endsection

    @section('css')
    <style>
        /* Hold Details Modal Styles */
#holdDetailsModal .modal-header {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(239, 68, 68, 0.02) 100%);
    border-bottom: 1px solid rgba(239, 68, 68, 0.1);
    padding: 1.25rem 1.5rem;
}

#holdDetailsModal .modal-title {
    color: var(--bs-body-color);
    font-weight: 600;
}

#holdDetailsModal .modal-body {
    padding: 1.5rem;
}

#holdDetailsModal .table thead th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--bs-secondary);
    border-bottom: 2px solid var(--bs-gray-300);
}

#holdDetailsModal .table tbody tr {
    transition: all 0.2s ease;
}

#holdDetailsModal .table tbody tr:hover {
    background-color: var(--bs-gray-100);
    transform: translateY(-1px);
}

#holdDetailsModal .table td {
    vertical-align: middle;
    padding: 0.875rem 0.75rem;
    font-size: 0.875rem;
}

#holdDetailsModal .badge-hold {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 500;
}

#holdDetailsModal .modal-footer {
    padding: 1rem 1.5rem;
    background-color: var(--bs-gray-100);
}

/* Card styling for summary */
#holdDetailsModal .card {
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--bs-gray-300);
}

/* Empty and Error states */
#holdDetailsModal .d-none {
    display: none !important;
}

/* Action buttons in table */
.btn-hold-action {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 4px;
}

/* Responsive adjustments — hold details modal */
@media (max-width: 768px) {
    #holdDetailsModal .modal-dialog {
        margin: 0.5rem;
    }

    #holdDetailsModal .modal-body {
        padding: 1rem;
    }

    #holdDetailsModal .table-responsive {
        border: 1px solid var(--bs-gray-300);
        border-radius: 6px;
    }

    #holdsTable {
        min-width: 700px;
    }
}

        /* Statistics Cards - Single Line Grid */
        .row.g-3 .col {
            min-width: 200px;
            flex: 1;
        }

        .stat-card {
            border-radius: 8px;
            transition: all 0.3s ease;
            border: 1px solid var(--bs-gray-300);
            background-color: var(--bs-body-bg);
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        .bg-opacity-10.bg-primary {
            background-color: rgba(59, 130, 246, 0.1) !important;
        }

        .bg-opacity-10.bg-success {
            background-color: rgba(16, 185, 129, 0.1) !important;
        }

        .bg-opacity-10.bg-warning {
            background-color: rgba(245, 158, 11, 0.1) !important;
        }

        .bg-opacity-10.bg-info {
            background-color: rgba(6, 182, 212, 0.1) !important;
        }

        .bg-opacity-10.bg-danger {
            background-color: rgba(239, 68, 68, 0.1) !important;
        }

        /* Responsive adjustments */
        @media (max-width: 1400px) {
            .row.g-3 .col {
                min-width: 180px;
            }
        }

        @media (max-width: 1200px) {
            .row.g-3 {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
            }

            .row.g-3 .col {
                min-width: auto;
            }
        }

        @media (max-width: 768px) {
            .row.g-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .row.g-3 {
                grid-template-columns: 1fr;
            }
        }

        /* Salary Hold Specific Styles */
        .hold-count-badge {
            position: relative;
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .hold-count-badge:hover {
            background: #fecaca;
            border-color: #f87171;
            transform: translateY(-1px);
        }

        .hold-employee-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            transition: background 0.2s;
        }

        .hold-employee-item:hover {
            background: #f8fafc;
        }

        .hold-employee-item:last-child {
            border-bottom: none;
        }

        .hold-employee-avatar {
            width: 32px;
            height: 32px;
            background: #e5e7eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #4b5563;
            margin-right: 12px;
        }

        .hold-employee-info {
            flex: 1;
        }

        .hold-employee-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 0.875rem;
        }

        .hold-employee-details {
            font-size: 0.75rem;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 2px;
        }

        .hold-reason {
            background: #fef3c7;
            border-left: 3px solid #f59e0b;
            padding: 8px;
            border-radius: 4px;
            margin-top: 4px;
        }

        .hold-reason-text {
            font-size: 0.75rem;
            color: #92400e;
            margin: 0;
        }

        .hold-empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .hold-empty-state i {
            color: #10b981;
            margin-bottom: 16px;
        }

        .btn-hold-details {
            width: 28px;
            height: 28px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }

        .btn-hold-details:hover {
            background: #fee2e2;
            border-color: #ef4444;
            color: #dc2626;
        }

        /* 4 Column Layout - Quarter Wise */
        .row.g-3>[class*="col-"] {
            display: flex;
            flex-direction: column;
        }

        /* Quarter Header */
        .quarter-header {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--bs-primary);
        }

        .quarter-header.success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
            border-left-color: var(--bs-success);
        }

        .quarter-header.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
            border-left-color: var(--bs-warning);
        }

        .quarter-header.info {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.1) 0%, rgba(6, 182, 212, 0.05) 100%);
            border-left-color: var(--bs-info);
        }

        /* Card Design Improvements - Compact */
        .cycle-card {
            transition: all 0.3s ease;
            border-radius: 8px;
            overflow: hidden;
            background-color: var(--bs-body-bg);
            border: 1px solid #e5e7eb;
            flex: 0 0 auto;
            margin-bottom: 1rem;
        }

        .cycle-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-color: #3b82f6;
        }

        .hover-shadow {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .hover-shadow:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Stat Cards */
        .stat-card {
            border-radius: 8px;
            transition: all 0.3s ease;
            border: 1px solid var(--bs-gray-300);
            background-color: var(--bs-body-bg);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        /* Progress Bar - Thinner */
        .progress {
            background-color: var(--bs-gray-200);
            border-radius: 4px;
            overflow: hidden;
            height: 4px;
        }

        .progress-bar {
            border-radius: 4px;
        }

        /* Badge Styling - Smaller */
        .badge {
            font-weight: 500;
            font-size: 0.65em;
            padding: 0.2em 0.4em;
        }

        /* Color Variants */
        .bg-primary-subtle {
            background-color: rgba(59, 130, 246, 0.1) !important;
        }

        .bg-success-subtle {
            background-color: rgba(16, 185, 129, 0.1) !important;
        }

        .bg-warning-subtle {
            background-color: rgba(245, 158, 11, 0.1) !important;
        }

        .bg-info-subtle {
            background-color: rgba(6, 182, 212, 0.1) !important;
        }

        .bg-danger-subtle {
            background-color: rgba(239, 68, 68, 0.1) !important;
        }

        .bg-secondary-subtle {
            background-color: rgba(100, 116, 139, 0.1) !important;
        }

        /* Cheque number style */
        .cheque-number {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            background-color: var(--bs-gray-100);
            padding: 1px 4px;
            border-radius: 3px;
            border: 1px solid var(--bs-gray-300);
            font-size: 0.7em;
        }

        /* Responsive Adjustments */
        @media (max-width: 1200px) {
            .row.g-3>[class*="col-xl-3"] {
                width: 50%;
            }
        }

        @media (max-width: 768px) {
            .row.g-3>[class*="col-"] {
                width: 100%;
                margin-bottom: 1rem;
            }

            .cycle-card {
                margin-bottom: 0.75rem;
            }
        }

        .payroll-cycles-header-actions {
            min-width: 0;
        }

        .payroll-cycles-fy-wrap {
            min-width: 0;
            flex: 1 1 auto;
        }

        @media (min-width: 768px) {
            .payroll-cycles-fy-wrap {
                flex: 0 1 auto;
                max-width: 20rem;
            }
        }

        .payroll-cycles-fy-select {
            min-width: 0;
            width: 100%;
        }

        @media (min-width: 576px) {
            .payroll-cycles-fy-select {
                min-width: 11rem;
                width: auto;
                max-width: 18rem;
            }
        }

        body.dark-mode .payroll-cycles-page .stat-card,
        body.dark-mode .payroll-cycles-page .cycle-card {
            background-color: #24264a;
            border-color: rgba(255, 255, 255, 0.12);
            color: #e9ecef;
        }

        body.dark-mode .payroll-cycles-page .cycle-card:hover {
            border-color: rgba(13, 110, 253, 0.55);
        }

        body.dark-mode .payroll-cycles-page .progress {
            background-color: rgba(255, 255, 255, 0.08);
        }

        body.dark-mode .payroll-cycles-page .cheque-number {
            background-color: rgba(0, 0, 0, 0.25);
            border-color: rgba(255, 255, 255, 0.15);
            color: #f8f9fa;
        }

        body.dark-mode #holdDetailsModal .modal-title {
            color: #fff;
        }

        body.dark-mode #holdDetailsModal .table thead th {
            color: rgba(255, 255, 255, 0.7);
            border-bottom-color: rgba(255, 255, 255, 0.15);
        }

        body.dark-mode #holdDetailsModal .table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.06);
        }

        body.dark-mode #holdDetailsModal .modal-footer {
            background-color: #25274a;
        }

        body.dark-mode #holdDetailsModal .card {
            background-color: #24264a;
            border-color: rgba(255, 255, 255, 0.12);
        }
    </style>
    @endsection

    @section('script')
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            /* -------------------------------
            *  Lucide Icons Initialization
            * ----------------------------- */
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            const payrollCyclesFySelect = document.getElementById('payrollCyclesFySelect');
            if (payrollCyclesFySelect) {
                payrollCyclesFySelect.addEventListener('change', function () {
                    const url = new URL(window.location.href);
                    url.searchParams.set('fy_id', this.value);
                    window.location.href = url.toString();
                });
            }

            /* -------------------------------
            *  DOM Elements
            * ----------------------------- */
            // Payroll Period Modal Elements
            const createCycleBtn = document.querySelector('[data-action="create-cycle"]');
            const payrollModalEl = document.getElementById('addPayrollPeriodModal');
            const payrollForm = document.getElementById('addPayrollPeriodForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const alertContainer = document.getElementById('alertContainer');

            const fySelect = document.getElementById('year');
            const monthSelect = document.getElementById('month');
            const payrollTypeSel = document.getElementById('payroll_type');
            const startDateEl = document.getElementById('attendance_start_date');
            const endDateEl = document.getElementById('attendance_end_date');
            const payrollNameEl = document.getElementById('name');
            const paymentDateEl = document.getElementById('date_of_payment');
            const payslipDateEl = document.getElementById('payslip_online_date');
            const chequeNumberEl = document.getElementById('cheque_number');
            const quarterInput = document.getElementById('payroll_quarter');

            // Hold Details Modal Elements
            const holdDetailsModalEl = document.getElementById('holdDetailsModal');
            const holdDetailsContent = document.getElementById('holdDetailsContent');
            const holdDetailsLoading = document.getElementById('holdDetailsLoading');
            const processPayrollLink = document.getElementById('processPayrollLink');
            const holdDetailsSubtitle = document.getElementById('holdDetailsSubtitle');
            const totalHoldsCount = document.getElementById('totalHoldsCount');
            const holdsTableBody = document.getElementById('holdsTableBody');
            const noHoldsMessage = document.getElementById('noHoldsMessage');
            const errorMessage = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            const retryButton = document.getElementById('retryButton');

            // Global Variables
            let payrollModalInstance = null;
            let holdDetailsModalInstance = null;
            let allMonthsData = [];
            let currentCycleId = null;

            /* -------------------------------
            *  Initialize Bootstrap Components
            * ----------------------------- */
            function initializeBootstrapComponents() {
                // Initialize tooltips
                const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

                // Initialize modals
                if (payrollModalEl) {
                    payrollModalInstance = new bootstrap.Modal(payrollModalEl);
                }

                if (holdDetailsModalEl) {
                    holdDetailsModalInstance = new bootstrap.Modal(holdDetailsModalEl);
                }
            }

            /* -------------------------------
            *  Payroll Period Modal Functions
            * ----------------------------- */
            // Open Payroll Period Modal
            if (createCycleBtn && payrollModalEl) {
                createCycleBtn.addEventListener('click', function () {
                    if (!payrollModalInstance) {
                        payrollModalInstance = new bootstrap.Modal(payrollModalEl);
                    }

                    // Reset form
                    payrollForm.reset();
                    clearAlerts();
                    clearValidationErrors();
                    resetLoadingState();

                    // Set today's date as default for payment and payslip dates
                    const today = new Date().toISOString().split('T')[0];
                    if (paymentDateEl) paymentDateEl.value = today;
                    if (payslipDateEl) payslipDateEl.value = today;

                    // Load months based on selected FY
                    const currentFYId = document.getElementById('fy_id')?.value || (fySelect ? fySelect.value : '');
                    if (currentFYId) {
                        loadMonthsByFY(currentFYId);
                    }

                    payrollModalInstance.show();
                });
            }

            // AJAX Form Submission
            if (payrollForm) {
                payrollForm.addEventListener('submit', async function (e) {
                    e.preventDefault();

                    // Clear previous alerts and errors
                    clearAlerts();
                    clearValidationErrors();

                    // Basic validation
                    if (!validatePayrollForm()) {
                        return;
                    }

                    // Show loading state
                    setLoadingState(true);

                    try {
                        // Get CSRF token
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                                        document.querySelector('input[name="_token"]')?.value;

                        if (!csrfToken) {
                            showAlert('error', 'Security token missing. Please refresh the page.');
                            setLoadingState(false);
                            return;
                        }

                        // Create FormData
                        const formData = new FormData(this);

                        // Make AJAX request
                        const response = await fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            // Success
                            showAlert('success', result.message);

                            // Close modal and reload page after 2 seconds
                            setTimeout(() => {
                                if (payrollModalInstance) {
                                    payrollModalInstance.hide();
                                }
                                window.location.reload();
                            }, 2000);
                        } else {
                            // Error
                            if (result.errors) {
                                // Show validation errors
                                showValidationErrors(result.errors);
                            } else {
                                showAlert('error', result.message || 'An error occurred while saving.');
                            }
                            setLoadingState(false);
                        }
                    } catch (error) {
                        console.error('Form submission error:', error);
                        showAlert('error', 'Network error. Please check your connection.');
                        setLoadingState(false);
                    }
                });
            }
            /* -------------------------------
            *  Cycle Card Click Navigation
            * ----------------------------- */
            document.addEventListener('click', function(e) {
                const cycleCard = e.target.closest('.cycle-card');
                if (!cycleCard) return;

                // If clicked on button/link inside card → ignore
                if (e.target.closest('button') || e.target.closest('a')) {
                    return;
                }

                const status = cycleCard.dataset.status;
                const cycleId = cycleCard.dataset.cycleId;
                const monthName = cycleCard.querySelector('.card-title')?.textContent || 'N/A';

                // Block navigation for finalized/locked statuses and show message
                const blockedStatuses = ['finalized_locked', 'PAYROLL_LOCKED', 'upcoming', 'expired'];

                if (blockedStatuses.includes(status)) {
                    // If finalized_locked or PAYROLL_LOCKED, show special message
                    if (status === 'finalized_locked' || status === 'PAYROLL_LOCKED') {
                        e.preventDefault();
                        e.stopPropagation();

                        // Create a custom toast with View Payslips button
                        const toastHtml = `
                            <div class="alert alert-info alert-dismissible fade show position-fixed top-0 end-0 m-3"
                                role="alert" style="z-index: 9999; min-width: 350px;">
                                <div class="d-flex align-items-start">
                                    <div class="me-2">
                                        <i data-lucide="lock" class="text-info"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="alert-heading mb-1">${monthName} - Finalized & Locked</h6>
                                        <p class="mb-2 small">This payroll has been processed and locked. No further changes allowed.</p>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-info" data-bs-dismiss="alert">
                                                <i data-lucide="x" class="me-1" style="width: 12px; height: 12px;"></i>
                                                Close
                                            </button>
                                            <a href="/payroll/payroll-new/payslip-list/${cycleId}" class="btn btn-sm btn-primary">
                                                <i data-lucide="eye" class="me-1" style="width: 12px; height: 12px;"></i>
                                                View Payslips
                                            </a>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        `;

                        // Remove existing toasts
                        document.querySelectorAll('.position-fixed.alert').forEach(el => el.remove());

                        // Add new toast
                        document.body.insertAdjacentHTML('beforeend', toastHtml);

                        // Initialize icons
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }

                        // Auto remove after 8 seconds
                        setTimeout(() => {
                            document.querySelectorAll('.position-fixed.alert').forEach(el => el.remove());
                        }, 8000);

                        return;
                    }

                    // For other blocked statuses
                    if (status === 'upcoming') {
                        showToast('info', 'This payroll cycle is upcoming and not available for processing yet.');
                    } else if (status === 'expired') {
                        showToast('warning', 'This payroll cycle has expired.');
                    }
                    return;
                }

                // Rest of the code remains the same...
                const allowedStatuses = [
                    'processing', 'PENDING_APPROVAL', 'UNDER_REVIEW','SALARY_PROCESSING',
                    'IN-PROCESS', 'VERIFICATION', 'open', 'ATTENDANCE_FROZEN','UPCOMING'
                ];

                const normalizedStatus = status.toUpperCase();
                const normalizedAllowedStatuses = allowedStatuses.map(s => s.toUpperCase());

                if (normalizedAllowedStatuses.includes(normalizedStatus)) {
                    window.location.href = `/payroll/payroll-new-process?period=${cycleId}`;
                } else {
                    showToast('warning', 'This payroll cycle is not available for processing.');
                }
            });

            /* -------------------------------
            *  FY → Load Months with Quarter Filter
            * ----------------------------- */
            function loadMonthsByFY(fyId) {
                if (!fyId || !monthSelect) return;

                monthSelect.innerHTML = `<option value="">Loading...</option>`;
                monthSelect.disabled = true;

                fetch(`/payroll/get-months/${fyId}`)
                    .then(res => res.json())
                    .then(data => {
                        allMonthsData = data;
                        monthSelect.innerHTML = `<option value="">-- Select Month --</option>`;

                        const currentQuarter = getCurrentQuarter();
                        const currentMonth = new Date().getMonth() + 1; // Current month number

                        // Get current quarter months
                        const currentQuarterMonths = getQuarterMonthNumbers(currentQuarter);

                        // Get previous quarter months (if needed)
                        const previousQuarter = currentQuarter === 1 ? 4 : currentQuarter - 1;
                        const previousQuarterMonths = getQuarterMonthNumbers(previousQuarter);

                        // Find which months to enable
                        const enabledMonths = [];

                        // Add all months from current quarter
                        enabledMonths.push(...currentQuarterMonths);

                        // If we're in the first month of current quarter, enable previous quarter's last month
                        const firstMonthOfCurrentQuarter = currentQuarterMonths[0];
                        if (currentMonth === firstMonthOfCurrentQuarter) {
                            // Add last month from previous quarter
                            const lastMonthOfPreviousQuarter = previousQuarterMonths[previousQuarterMonths.length - 1];
                            enabledMonths.push(lastMonthOfPreviousQuarter);
                        }

                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.value;
                            option.textContent = item.label;

                            const monthName = item.label.split(' ')[0].toLowerCase();
                            const monthNumber = getMonthNumber(monthName);

                            // Check if month should be enabled
                            const shouldEnable = enabledMonths.includes(monthNumber);

                            if (!shouldEnable) {
                                option.disabled = true;
                                option.classList.add('text-muted');
                            }

                            monthSelect.appendChild(option);
                        });

                        monthSelect.disabled = false;

                        // Prepare message
                        const enabledMonthNames = enabledMonths.map(monthNum => {
                            const date = new Date();
                            date.setMonth(monthNum - 1);
                            return date.toLocaleString('en-US', { month: 'long' });
                        });

                        // Get quarter names
                        const currentQuarterName = getQuarterName(currentQuarter);
                        const previousQuarterName = getQuarterName(previousQuarter);

                        let message = `Current Quarter (${currentQuarterName}): ${enabledMonthNames.join(', ')} months are enabled.`;

                        if (currentMonth === firstMonthOfCurrentQuarter) {
                            message += ` Also enabled last month from previous quarter (${previousQuarterName}).`;
                        }

                        showAlert('info', message);

                    })
                    .catch(() => {
                        monthSelect.innerHTML = `<option value="">Failed to load months</option>`;
                        monthSelect.disabled = false;
                        showAlert('error', 'Failed to load months. Please try again.');
                    });
            }

            /* -------------------------------
            *  Month Change Handler
            * ----------------------------- */
            if (monthSelect) {
                monthSelect.addEventListener('change', function () {
                    const optionText = this.options[this.selectedIndex]?.text;
                    if (!optionText) return;

                    const [monthName, year] = optionText.split(' ');
                    if (!monthName || !year) return;

                    try {
                        const monthIndex = new Date(`${monthName} 1, ${year}`).getMonth();
                        const monthNumber = monthIndex + 1;

                        // Calculate quarter ID based on month number
                        let quarterId;
                        if (monthNumber >= 4 && monthNumber <= 6) {
                            quarterId = 444; // Q1
                        } else if (monthNumber >= 7 && monthNumber <= 9) {
                            quarterId = 445; // Q2
                        } else if (monthNumber >= 10 && monthNumber <= 12) {
                            quarterId = 446; // Q3
                        } else {
                            quarterId = 447; // Q4 (Jan-Mar)
                        }

                        if (quarterInput) {
                            quarterInput.value = quarterId;
                        }

                        // Set date ranges
                        const start = new Date(year, monthIndex, 1);
                        const end = new Date(year, monthIndex + 1, 0);

                        if (startDateEl) startDateEl.value = formatDate(start);
                        if (endDateEl) endDateEl.value = formatDate(end);

                        // Set payment dates (7th of next month)
                        const paymentDate = new Date(year, monthIndex + 1, 7);
                        const formattedPayment = formatDate(paymentDate);

                        if (paymentDateEl) paymentDateEl.value = formattedPayment;
                        if (payslipDateEl) payslipDateEl.value = formattedPayment;

                        // Auto-generate payroll name
                        if (payrollNameEl) {
                            payrollNameEl.value = `Payroll - ${monthName} ${year}`;
                        }
                    } catch (error) {
                        console.error('Error processing month:', error);
                    }
                });
            }

            /* -------------------------------
            *  FY Change Handler
            * ----------------------------- */
            if (fySelect) {
                fySelect.addEventListener('change', function () {
                    loadMonthsByFY(this.value);

                    // Reset dependent fields
                    if (monthSelect) monthSelect.value = '';
                    if (startDateEl) startDateEl.value = '';
                    if (endDateEl) endDateEl.value = '';
                    if (payrollNameEl) payrollNameEl.value = '';
                    if (quarterInput) quarterInput.value = '';
                    if (chequeNumberEl) chequeNumberEl.value = '';
                });
            }

            /* -------------------------------
            *  Hold Details Modal Functions
            * ----------------------------- */
            // Hold details button click handler
            document.addEventListener('click', function (e) {
                const holdBtn = e.target.closest('.btn-hold-details');
                if (!holdBtn) return;

                e.preventDefault();
                e.stopPropagation();

                currentCycleId = holdBtn.dataset.cycleId;
                const cycleMonth = holdBtn.dataset.cycleMonth;

                if (!currentCycleId || !holdDetailsModalInstance) return;

                // Update modal subtitle
                if (holdDetailsSubtitle) {
                    holdDetailsSubtitle.textContent = `Payroll Period: ${cycleMonth}`;
                }

                // Show loading state
                showHoldLoadingState();

                // Set process payroll link
                if (processPayrollLink) {
                    processPayrollLink.href = `/payroll/payroll-new-process?period=${currentCycleId}`;
                }

                // Load hold details
                loadHoldDetails(currentCycleId);

                // Show modal
                holdDetailsModalInstance.show();
            });

            // Retry button functionality
            if (retryButton) {
                retryButton.addEventListener('click', function () {
                    if (currentCycleId) {
                        loadHoldDetails(currentCycleId);
                    }
                });
            }

            async function loadHoldDetails(cycleId) {
                try {
                    showHoldLoadingState();

                    const response = await fetch(`/payroll/payroll-new/get-hold-details/${cycleId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const result = await response.json();

                    if (result.success) {
                        if (result.holds && result.holds.length > 0) {
                            renderHoldDetails(result);
                        } else {
                            showNoHolds();
                        }
                        showHoldContent();
                    } else {
                        showHoldError(result.message || 'Failed to load hold details.');
                    }
                } catch (error) {
                    console.error('Error loading hold details:', error);
                    showHoldError('Network error. Please check your connection.');
                }
            }

           function renderHoldDetails(data) {
            console.log("Complete data structure:", data); // Debug के लिए
            console.log("Payroll period data:", data.payrollPeriod); // यहाँ देखें

            if (!holdsTableBody) return;

            // Update summary
            if (totalHoldsCount) {
                totalHoldsCount.textContent = `${data.count || 0} Hold(s)`;
            }

            // Hide empty state
            if (noHoldsMessage) {
                noHoldsMessage.classList.add('d-none');
            }

            // Render table rows
            let html = '';
            data.holds.forEach((hold, index) => {
                const holdDate = new Date(hold.sh_held_at).toLocaleDateString('en-IN');
                const isHoldUntilRelease = hold.hold_until_release ? true : false;

                // ✅ यहाँ सही field name का इस्तेमाल करें
                // data.cycle_id या hold.sh_pp_id या data.payrollPeriod.pp_id
                const payrollPeriodId = hold.sh_pp_id || data.payrollPeriod?.pp_id || data.cycle_id;

                console.log(`Row ${index} - payrollPeriodId:`, payrollPeriodId, "hold data:", hold);

                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-1 me-2">
                                    <i data-lucide="user" class="text-muted" style="width: 12px; height: 12px;"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">${hold.employee_name || 'N/A'}</div>
                                    <div class="text-muted small">${hold.designation || ''}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">${hold.employee_code || 'N/A'}</span>
                        </td>
                        <td>${hold.department || 'N/A'}</td>
                        <td>
                            <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25">
                                ${hold.reason || 'Salary Hold'}
                            </span>
                            ${hold.remarks ? `<div class="text-muted small mt-1">${hold.remarks}</div>` : ''}
                            <div class="text-muted small mt-1">
                                <i data-lucide="calendar" style="width: 12px; height: 12px;"></i>
                                Held on: ${holdDate}
                                ${isHoldUntilRelease ?
                                    '<span class="badge bg-warning text-dark ms-2">Hold Until Release</span>' : ''}
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-success btn-hold-action"
                                        title="Release Hold"
                                        onclick="releaseHold(${hold.sh_id}, ${hold.employee_id}, ${payrollPeriodId})">
                                    <i data-lucide="check" style="width: 12px; height: 12px;"></i>
                                    Release
                                </button>

                            </div>
                        </td>
                    </tr>
                `;
            });

            holdsTableBody.innerHTML = html;

            // Update Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
         }

            function showHoldLoadingState() {
                if (holdDetailsLoading) holdDetailsLoading.classList.remove('d-none');
                if (holdDetailsContent) holdDetailsContent.classList.add('d-none');
                if (errorMessage) errorMessage.classList.add('d-none');
                if (noHoldsMessage) noHoldsMessage.classList.add('d-none');
            }

            function showHoldContent() {
                if (holdDetailsLoading) holdDetailsLoading.classList.add('d-none');
                if (holdDetailsContent) holdDetailsContent.classList.remove('d-none');
            }

            function showNoHolds() {
                if (holdDetailsContent) holdDetailsContent.classList.add('d-none');
                if (noHoldsMessage) noHoldsMessage.classList.remove('d-none');
                if (totalHoldsCount) totalHoldsCount.textContent = '0 Hold(s)';
            }

            function showHoldError(message) {
                if (holdDetailsLoading) holdDetailsLoading.classList.add('d-none');
                if (holdDetailsContent) holdDetailsContent.classList.add('d-none');
                if (errorMessage) {
                    errorMessage.classList.remove('d-none');
                    if (errorText) errorText.textContent = message;
                }
            }

            /* -------------------------------
            *  Global Helper Functions
            * ----------------------------- */
            // Payroll Form Validation
            function validatePayrollForm() {
                let isValid = true;

                // Check required fields
                const requiredFields = [
                    { element: monthSelect, errorId: 'monthError', message: 'Month is required' },
                    { element: startDateEl, errorId: 'attendance_start_dateError', message: 'Start date is required' },
                    { element: endDateEl, errorId: 'attendance_end_dateError', message: 'End date is required' },
                    { element: paymentDateEl, errorId: 'date_of_paymentError', message: 'Payment date is required' },
                    { element: payslipDateEl, errorId: 'payslip_online_dateError', message: 'Payslip date is required' }
                ];

                requiredFields.forEach(field => {
                    if (field.element && !field.element.value) {
                        showFieldError(field.element, field.errorId, field.message);
                        isValid = false;
                    }
                });

                // Date validation
                if (startDateEl && startDateEl.value && endDateEl && endDateEl.value) {
                    const startDate = new Date(startDateEl.value);
                    const endDate = new Date(endDateEl.value);

                    if (startDate > endDate) {
                        showFieldError(endDateEl, 'attendance_end_dateError', 'End date must be after start date');
                        isValid = false;
                    }
                }

                if (paymentDateEl && paymentDateEl.value && endDateEl && endDateEl.value) {
                    const paymentDate = new Date(paymentDateEl.value);
                    const endDate = new Date(endDateEl.value);

                    if (paymentDate <= endDate) {
                        showFieldError(paymentDateEl, 'date_of_paymentError', 'Payment date must be after end date');
                        isValid = false;
                    }
                }

                return isValid;
            }

            function showFieldError(element, errorId, message) {
                if (element) {
                    element.classList.add('is-invalid');
                    const errorElement = document.getElementById(errorId);
                    if (errorElement) {
                        errorElement.textContent = message;
                    }
                }
            }

            function clearValidationErrors() {
                // Remove invalid class from all form controls
                document.querySelectorAll('.form-control, .form-select').forEach(el => {
                    el.classList.remove('is-invalid');
                });

                // Clear all error messages
                document.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                });
            }

            function showValidationErrors(errors) {
                Object.keys(errors).forEach(field => {
                    const element = document.getElementById(field);
                    const errorElement = document.getElementById(field + 'Error');

                    if (element) {
                        element.classList.add('is-invalid');
                    }

                    if (errorElement) {
                        errorElement.textContent = errors[field][0];
                    }
                });
            }

            function showAlert(type, message) {
                if (!alertContainer) return;

                const alertClass = type === 'success' ? 'alert-success' :
                                type === 'error' ? 'alert-danger' : 'alert-info';
                alertContainer.innerHTML = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            }

            function clearAlerts() {
                if (alertContainer) {
                    alertContainer.innerHTML = '';
                }
            }

            function setLoadingState(isLoading) {
                if (submitBtn) {
                    submitBtn.disabled = isLoading;
                    if (submitText) {
                        if (isLoading) {
                            submitText.classList.add('d-none');
                        } else {
                            submitText.classList.remove('d-none');
                        }
                    }
                    if (loadingSpinner) {
                        if (isLoading) {
                            loadingSpinner.classList.remove('d-none');
                        } else {
                            loadingSpinner.classList.add('d-none');
                        }
                    }
                }
            }

            function resetLoadingState() {
                setLoadingState(false);
            }

            function formatDate(date) {
                const yyyy = date.getFullYear();
                const mm = String(date.getMonth() + 1).padStart(2, '0');
                const dd = String(date.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            }

            /* -------------------------------
            *  Quarter and Month Helper Functions
            * ----------------------------- */
            function getCurrentQuarter() {
                const today = new Date();
                const month = today.getMonth() + 1; // January is 0

                // Indian Financial Year Quarters:
                // Q1: April (4) - June (6)
                // Q2: July (7) - September (9)
                // Q3: October (10) - December (12)
                // Q4: January (1) - March (3)

                if (month >= 4 && month <= 6) return 1; // Q1: Apr-Jun
                if (month >= 7 && month <= 9) return 2; // Q2: Jul-Sep
                if (month >= 10 && month <= 12) return 3; // Q3: Oct-Dec
                return 4; // Q4: Jan-Mar
            }

            function getMonthNumber(monthName) {
                const monthMap = {
                    'january': 1, 'february': 2, 'march': 3,
                    'april': 4, 'may': 5, 'june': 6,
                    'july': 7, 'august': 8, 'september': 9,
                    'october': 10, 'november': 11, 'december': 12
                };
                return monthMap[monthName.toLowerCase()] || 1;
            }

            function getQuarterMonthNumbers(quarter) {
                switch (quarter) {
                    case 1: // Q1: April-June
                        return [4, 5, 6];
                    case 2: // Q2: July-September
                        return [7, 8, 9];
                    case 3: // Q3: October-December
                        return [10, 11, 12];
                    case 4: // Q4: January-March
                        return [1, 2, 3];
                    default:
                        return [];
                }
            }

            function getQuarterName(quarter) {
                switch (quarter) {
                    case 1: return 'Q1 (Apr-Jun)';
                    case 2: return 'Q2 (Jul-Sep)';
                    case 3: return 'Q3 (Oct-Dec)';
                    case 4: return 'Q4 (Jan-Mar)';
                    default: return `Q${quarter}`;
                }
            }

            /* -------------------------------
            *  Global Action Functions
            * ----------------------------- */
            // Placeholder functions for hold actions
            window.viewEmployeeDetails = function (employeeId) {
                // Implement view employee details functionality
                console.log('View employee details:', employeeId);
                // window.open(`/employees/${employeeId}`, '_blank');
            }

            window.releaseHold = async function (holdId, employeeId, cycleId) {
                if (!confirm('Are you sure you want to release this salary hold?')) {
                    return;
                }

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    const response = await fetch('/payroll/payroll-new/release-hold', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            hold_id: holdId,
                            employee_id: employeeId,
                            cycle_id: cycleId
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        showToast('success', 'Salary hold released successfully.');
                        // Reload hold details
                        loadHoldDetails(cycleId);
                    } else {
                        showToast('error', result.message || 'Failed to release hold.');
                    }
                } catch (error) {
                    console.error('Error releasing hold:', error);
                    showToast('error', 'Network error. Please try again.');
                }
            }

            // Toast notification function
            function showToast(type, message) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const icon = type === 'success' ? 'check-circle' : 'alert-circle';

                const toastHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3"
                        role="alert" style="z-index: 9999; min-width: 300px;">
                        <div class="d-flex align-items-center">
                            <i data-lucide="${icon}" class="me-2"></i>
                            <div>${message}</div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                `;

                // Remove existing toasts
                document.querySelectorAll('.position-fixed.alert').forEach(el => el.remove());

                // Add new toast
                document.body.insertAdjacentHTML('beforeend', toastHtml);

                // Initialize icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                // Auto remove after 5 seconds
                setTimeout(() => {
                    document.querySelectorAll('.position-fixed.alert').forEach(el => el.remove());
                }, 5000);
            }

            /* -------------------------------
            *  Initialization
            * ----------------------------- */
            // Initialize all components when DOM is loaded
            initializeBootstrapComponents();

            // Auto-load months if FY is already selected
            if (fySelect && fySelect.value) {
                loadMonthsByFY(fySelect.value);
            }
        });
    </script>
    @endsection
