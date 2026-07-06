@extends('admin.layout.master')
@section('title', 'View Employee Exit')
@section('css')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .progress-container {
            padding: 30px;
            background: #f8f9fa;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 40px;
        }

        .progress-line {
            position: absolute;
            top: 20px;
            left: 0;
            width: 100%;
            height: 4px;
            background: #e0e0e0;
            z-index: 1;
        }

        .progress-line-fill {
            height: 100%;
            background: #28a745;
            transition: width 0.4s ease;
        }

        .step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 4px solid #e0e0e0;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #999;
            transition: all 0.3s ease;
        }

        .step.active .step-circle {
            border-color: #28a745;
            background: #28a745;
            color: white;
            transform: scale(1.1);
        }

        .step.completed .step-circle {
            border-color: #28a745;
            background: #28a745;
            color: white;
        }

        .step.rejected .step-circle {
            border-color: #dc3545;
            background: #dc3545;
            color: white;
        }

        .step-label {
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }

        .step.active .step-label {
            color: #667eea;
            font-weight: 600;
        }

        .content-area {
            padding: 30px;
        }

        .stage-content {
            display: none;
        }

        .stage-content.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stage-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }

        .stage-icon {
            font-size: 36px;
        }

        .stage-title h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-submitted {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-approved {
            background: #e8f5e9;
            color: #388e3c;
        }

        .status-rejected {
            background: #ffebee;
            color: #d32f2f;
        }

        .status-clearance {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-documentation {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .status-relieved {
            background: #e0f2f1;
            color: #00796b;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .required {
            color: #dc3545;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .button-group button {
            padding: 6px 15px;
            font-size: 13px;
            height: 35px;
            min-width: 90px;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover:not(:disabled) {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover:not(:disabled) {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

        .clearance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .clearance-table th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .clearance-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .clearance-table tr:last-child td {
            border-bottom: none;
        }

        .clearance-table tr:hover {
            background: #f8f9fa;
        }

        .value-cell {
            font-weight: 600;
            color: #667eea;
        }

        .file-upload {
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }

        .file-upload:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }

        .file-upload input[type="file"] {
            display: none;
        }

        .file-list {
            margin-top: 15px;
        }

        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 8px;
        }

        .file-item span {
            font-size: 14px;
            color: #333;
        }

        .file-item button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .remarks-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .remark-item {
            padding: 15px;
            background: white;
            border-left: 4px solid #667eea;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .remark-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .remark-author {
            font-weight: 600;
            color: #667eea;
        }

        .remark-date {
            font-size: 12px;
            color: #999;
        }

        .remark-text {
            color: #333;
            line-height: 1.6;
        }

        .success-animation {
            text-align: center;
            padding: 40px;
        }

        .success-animation .checkmark {
            font-size: 72px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .summary-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-label {
            font-weight: 600;
            color: #666;
        }

        .summary-value {
            color: #333;
        }
    </style>
@endsection

@section('content')
    {{-- Breadcrumbs --}}

    <!-- Alerts Container (fixed at top-right) -->
    <div class="alert-container position-fixed top-0 end-0 p-3" style="z-index: 1050;">
        <!-- Display Error Message -->
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Display Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- Smooth Auto Dismiss Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach((alert) => {
                // Automatically dismiss after 4 seconds
                setTimeout(() => {
                    // Bootstrap fade out
                    alert.classList.remove('show');
                    alert.classList.add('hide');
                    // Remove from DOM after fade animation (150ms)
                    setTimeout(() => alert.remove(), 150);
                }, 4000);
            });
        });
    </script>

    <!-- Optional CSS for smoother fade -->
    <style>
        .alert.hide {
            opacity: 0;
            transition: opacity 0.15s linear;
        }
    </style>
    <div class="mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/admin/employee-exit/index') }}">Employee Exit Requests</a></li>
                    <li class="active"><span><b>View Exit Details</b></span></li>
                </ol>
            </div>
        </div>
    </div>


    <div class="row g-4 mt-2">
        <!-- Left  Side Container  -->
        <div class="col-lg-8 order-lg-1">
            <!-- Employee Overview Card -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4 card-hover">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-between gap-4">
                        <div class="d-flex align-items-center gap-4">
                            <div>
                                <h3 class="fw-bold mb-4">{{ $exit->employee->emp_full_name ?? 'N/A' }}</h3>
                                <div class="fs-6 d-flex flex-wrap gap-3">
                                    <span>{{ $exit->employee->emp_code ?? 'N/A' }}</span>
                                    <span>•</span>
                                    <span>{{ $exit->employee->fh_department->d_name ?? 'N/A' }}</span>
                                    <span>•</span>
                                    <span>{{ $exit->employee->fh_designation->dg_name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            @php
                                $status = $exit->er_overall_status;
                                $badgeClass = $statusClasses[$status] ?? 'badge bg-secondary';
                                $label = $statusLabels[$status] ?? $status;
                            @endphp

                            <span class="{{ $badgeClass }} px-2 py-1 small fw-semibold">
                                <i class="bi bi-check-circle me-1"></i> {{ $label }}
                            </span>

                        </div>
                    </div>

                    <hr class="my-4 opacity-50">

                    <div class="row g-4 text-center text-md-start stats-section">

                        <div class="col-6 col-sm-2">
                            <div class="mb-1"><i class="bi bi-calendar2-check me-1"></i> Date Of Joining</div>

                            <div class="fw-bold">
                                {{ strtolower(\Carbon\Carbon::parse($exit->employee->emp_date_of_joining)->format('d-M-Y')) }}
                            </div>
                        </div>

                        <div class="col-6 col-sm-2">
                            <div class="mb-1"><i class="bi bi-calendar2-check me-1"></i> Submission Date</div>
                             <div class="fw-bold">
                                {{ strtolower(\Carbon\Carbon::parse($exit->er_resignation_date)->format('d-M-Y')) }}
                            </div>
                        </div>

                        <div class="col-6 col-sm-2">
                            <div class="mb-1"><i class="bi bi-clock-history me-1"></i> Last Working Day</div>
                             <div class="fw-bold">
                                {{ strtolower(\Carbon\Carbon::parse($exit->er_last_working_day)->format('d-M-Y')) }}
                            </div>
                        </div>

                        <div class="col-6 col-sm-2">
                            <div class="mb-1"><i class="bi bi-hourglass-split me-1"></i> Notice Period</div>
                            <div class="fw-bold ">
                                {{ $exit->employee->emp_notice_period_req_days ?? 0 }} days
                            </div>
                        </div>

                        <div class="col-6 col-sm-2">
                            <div class="mb-1"><i class="bi bi-hourglass-split me-1"></i> Notice Period Serve Days</div>
                            <div class="fw-bold ">
                                {{ $exit->employee->emp_notice_period_serve_days ?? 0 }} days
                            </div>
                        </div>

                        <div class="col-6 col-sm-2">
                            <div class="mb-1"><i class="bi bi-exclamation-circle me-1"></i> Reason</div>
                            <div class="fw-bold ">
                                {{ $exit->exitType->m_name ?? 'N/A' }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <style>
                .small-btn {
                    padding: 2px 8px;
                    font-size: 12px;
                }
            </style>

            <!-- Workflow Stepper (modern horizontal stepper) -->
            <div class="card border-0 shadow-lg rounded-4 mb-4">
                <div class="progress-container">
                    <div class="progress-steps">
                        <div class="progress-line">
                            <div class="progress-line-fill" id="progressFill" style="width: 0%"></div>
                        </div>
                        <div class="step active" data-step="1">
                            <div class="step-circle">1</div>
                            <div class="step-label">Submitted</div>
                        </div>

                        <!-- Dynamic Steps -->
                        @foreach ($fnfStages as $index => $stage)
                            @if ($stage->m_id)
                                {{-- 🔹 skip if m_id is null --}}
                                <div class="step" data-step="{{ $index + 2 }}">
                                    <div class="step-circle">{{ $index + 2 }}</div>
                                    <div class="step-label">{{ $stage->m_name }}</div>
                                </div>
                            @endif
                        @endforeach

                        <!-- Final Step -->
                        <div class="step" data-step="{{ $fnfStages->count() + 2 }}">
                            <div class="step-circle">{{ $fnfStages->count() + 2 }}</div>
                            <div class="step-label">Relieved</div>
                        </div>
                    </div>
                </div>

                <!-- ================= CONTENT ================= -->
                <div class="content-area">

                    <!-- ================= STEP 1 ================= -->
                    <div class="stage-content completed" id="stage1">
                        <div class="stage-header">
                            <div class="stage-title">
                                <h4>Step 1: Submit Resignation</h4>
                                <span class="status-badge status-submitted">Submitted</span>
                            </div>
                        </div>
                    </div>

                    <!-- ================= DYNAMIC STAGES ================= -->
                    @foreach ($fnfStages as $index => $stage)
                        @php
                            if (!$stage->m_id) {
                                continue;
                            }
                            $stepNumber = $index + 2;
                        @endphp

                        <div class="stage-content" id="stage{{ $stepNumber }}">

                            <div class="stage-header">
                                <div class="stage-title">
                                    <h4>Step {{ $stepNumber }}: {{ $stage->m_name }}</h4>

                                    {{--                                   
                                   <span class="status-badge"
                                        style="
                                            @if ($exit->er_module_id > $stage->m_id)
                                                background: linear-gradient(135deg, #a8e6cf, #dcedc1); color: #000;
                                            @elseif ($exit->er_module_id == $stage->m_id)
                                                background: linear-gradient(135deg, #fff9a6, #ffe39c); color: #000;
                                            @else
                                                background: linear-gradient(135deg, #e0e0e0, #f5f5f5); color: #000;
                                            @endif
                                            padding: 5px 10px; border-radius: 20px; font-size: 10px; font-weight: 500;
                                        "
                                    >
                                        @if ($exit->er_module_id > $stage->m_id)
                                            Approved
                                        @elseif ($exit->er_module_id == $stage->m_id)
                                            In Progress
                                        @else
                                            Pending
                                        @endif
                                    </span> --}}
                                </div>
                            </div>

                            <!-- ================= MANAGER ================= -->
                            {{-- @if ($exit->er_overall_status == 'RESIGNATION_SUBMITTED') --}}
                            @if ($stage->m_id == $managerReviewId)
                                <div class="card-body p-0">
                                    <h4 style="margin-bottom: 15px;">Assets And Kits Details</h4>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-0">
                                            <thead style="background-color: #dee3e3;">
                                                <tr>
                                                    <th style="width: 20%;" scope="col">Category</th>
                                                    <th style="width: 40%;" scope="col">Description</th>
                                                    <th style="width: 20%;" scope="col" class="text-end">Amount
                                                        (₹)
                                                    </th>
                                                    <th style="width: 20%;" scope="col">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Loan / Advance -->
                                                {{-- <tr>
                                                        <td><i class="bi bi-currency-rupee me-2"></i>Loan / Advance</td>
                                                        <td>Employee Advance / Loan Recovery</td>
                                                        <td class="text-end   deduction-amount">
                                                            {{ number_format($totalLoanRecovery ?? 0, 2) }}
                                                        </td>
                                                        <td><span>Recoverable</span></td>
                                                    </tr> --}}

                                                <!-- Assets / Kits (if recovery needed) -->
                                                @forelse ($assets as $asset)
                                                    @if ($asset && strtolower($asset->status) !== 'returned')
                                                        <tr>
                                                            <td><i class="bi bi-laptop me-2"></i>Assets</td>
                                                            <td>{{ $asset->assetType->name ?? 'Asset' }} -
                                                                {{ $asset->asset_tag }}
                                                                (Recovery if not returned)
                                                            </td>
                                                            <td class="text-end   deduction-amount">
                                                                {{ number_format($asset->purchase_value ?? 0, 2) }}
                                                            </td>

                                                            <td><span class="badge bg-warning text-dark">Pending
                                                                    Return</span></td>
                                                        </tr>
                                                    @endif
                                                @empty
                                                    {{-- <td colspan="4" class="text-center">No Assets Assigned</td> --}}
                                                @endforelse


                                                <!-- Kits -->
                                                @forelse ($uniformItem as $item)
                                                    @if ($item->fh_stock && $item->fh_stock->kit)
                                                        <tr>
                                                            <td><i class="bi bi-receipt me-2"></i>Kits</td>
                                                            <td>{{ $item->fh_stock->kit->name }}</td>
                                                            <td class="text-end   deduction-amount">
                                                                {{ number_format($item->uit_total_price ?? 0, 2) }}
                                                            </td>
                                                            <td><span class="badge bg-warning text-dark">Pending
                                                                    Return</span></td>
                                                        </tr>
                                                    @endif
                                                @empty
                                                    <tr>
                                                        {{-- <td colspan="4" class="text-center">No Kits Assigned</td> --}}
                                                    </tr>
                                                @endforelse



                                                <!-- Adhoc Deduction -->
                                                {{-- <tr>
                                                        <td><i class="bi bi-receipt me-2"></i>Adhoc</td>
                                                        <td>Adhoc Deduction / Penalty (if any)</td>
                                                        <td class="text-end   deduction-amount">
                                                            {{ number_format($adhoc ?? 0, 2) }}
                                                        </td>
                                                        <td><span>Clear</span></td>
                                                    </tr> --}}

                                                <!-- Add Notice Pay, Damage Recovery, etc. here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>


                                @if ($approvalData)
                                    <div class="card-body">

                                        <label class="mt-4"> Message <span style="color: red;">*</span></label>
                                        <textarea rows="2" class="form-control mb-3 auto-focus" id="actionMessage-{{ $stage->m_id }}"></textarea>

                                        <div class="text-end">

                                            <button type="button"
                                                data-approval_status="{{ $approvalData?->fh_approver_status?->m_id }}"
                                                data-approval_type="0"
                                                data-approval_action_type="{{ $approvalData->pa_type }}"
                                                data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                data-atd_id="{{ md5($exit->er_id) }}"
                                                data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                data-master_module_id="{{ $managerReviewId }}"
                                                data-is_last_approval="{{ $approvalData->pa_last }}"
                                                data-emp_d_id="{{ optional($exit->fh_employee)->emp_d_id }}"
                                                class="btn btn-outline-danger small-btn actionBtn mx-3">
                                                Reject
                                            </button>

                                            <!-- Approval Button -->
                                            <button type="button"
                                                data-approval_status="{{ $approvalData?->fh_approver_status?->m_id }}"
                                                data-approval_type="1"
                                                data-approval_action_type="{{ $approvalData->pa_type }}"
                                                data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                data-atd_id="{{ md5($exit->er_id) }}"
                                                data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                data-master_module_id="{{ $managerReviewId }}"
                                                data-is_last_approval="{{ $approvalData->pa_last }}"
                                                data-emp_d_id="{{ optional($exit->fh_employee)->emp_d_id }}"
                                                class="btn btn-outline-primary small-btn actionBtn">
                                                {{ $approvalData?->fh_approver_status?->m_name }}
                                            </button>



                                        </div>
                                    </div>
                                @elseif ($canApprove && $exit->er_module_id == $managerReviewId)
                                    <x-approval-form :moduleName="$exit?->fh_module?->m_name" :masterApproveBtn="$masterApproveBtn" :primaryId="$exit->er_id" :moduleId="$stage->m_id"
                                        actionUrl="{{ route('approve.fnf') }}" />
                                @endif
                            @endif
                            {{-- @endif --}}




                            <!-- ================= HR ================= -->
                            {{-- @if ($exit->er_overall_status == 'CLEARANCE_IN_PROGRESS') --}}
                            @if ($stage->m_id == $hrReviewId)
                                <div class="card-body p-0">
                                    <h4 style="margin-bottom: 15px;">Earnings / Credits</h4>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-0">
                                            <thead style="background-color: #dee3e3;">
                                                <tr>
                                                    <th style="width: 20%;" scope="col">Category</th>
                                                    <th style="width: 40%;" scope="col">Description</th>
                                                    <th style="width: 20%;" scope="col" class="text-end">Amount
                                                        (₹)
                                                    </th>
                                                    <th style="width: 20%;" scope="col">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <tr>
                                                    <td><i class="bi bi-journal-check me-2"></i>Total Salary Days</td>
                                                    <td>Check Attendance For Salary Days</td>
                                                    <td class="text-end  ">{{ $totalSalariedDays }} Days</td>
                                                    <td><span>Payable</span></td>

                                                </tr>

                                                <!-- Attendance / Salary -->
                                                <tr>
                                                    <td><i class="bi bi-wallet2 me-2"></i>Salary</td>
                                                    <td>Final Month Salary (Pro-rated)</td>
                                                    <td class="text-end   earning-amount">
                                                        {{ number_format($salary ?? 0, 2) }}
                                                    </td>
                                                    <td><span>Payable</span></td>
                                                </tr>

                                                <!-- Leave Encashment (example - add your variable if any) -->
                                              @forelse($activeLeaveTypes as $leave)
                                                    <tr>
                                                        <td>
                                                            <i class="bi bi-wallet2 me-2"></i>
                                                            Leave Encashable ({{ $leave['leave_type'] }})
                                                        </td>

                                                        <td>
                                                            Encashable Leave Days ({{ $leave['balance'] }} days)
                                                        </td>

                                                        <td class="text-end earning-amount">
                                                            {{ number_format($leave['encash_amount'] ?? 0, 2) }}
                                                        </td>

                                                        <td>
                                                            <span>Payable</span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">
                                                            No Leave Encashable Found
                                                        </td>
                                                    </tr>
                                                @endforelse

                                                <!-- Incentives -->
                                                <tr>
                                                    <td><i class="bi bi-gift me-2"></i>Incentives</td>
                                                    <td>Pending Incentives / Performance Bonus</td>
                                                    <td class="text-end   earning-amount">
                                                        {{ number_format($incentives ?? 0, 2) }}
                                                    </td>
                                                    <td><span>Payable</span></td>
                                                </tr>

                                                <!-- Payable TA/DA -->
                                                @forelse($payble_claims as $claim)
                                                    <tr>
                                                        <td><i class="bi bi-car-front me-2"></i>TA/DA</td>
                                                        <td>Travel Allowance Outstanding</td>
                                                        <td class="text-end   earning-amount">
                                                            {{ number_format($claim->tc_claimed_amount ?? 0, 2) }}
                                                        </td>
                                                        <td><span>Payable</span></td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        {{-- <td colspan="4" class="text-center text-muted py-3">No Payable Reimbursements</td> --}}
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div><br> <br>


                                <div class="summary-card" style="margin-top: 30px;">
                                    <h3 style="margin-bottom: 15px;">Settlement Summary</h3>

                                    <div class="summary-row">
                                        <span class="summary-label">Total Payable:</span><span id="totalPayable"
                                            class="summary-value fw-bold ">₹0.00</span>
                                    </div>

                                    <div class="summary-row">
                                        <span class="summary-label">Net Settlement:</span>
                                        <span id="netSettlement" class="summary-value fw-bold"
                                            style="font-size:12px;">₹0.00</span>
                                    </div>
                                </div>



                                @if ($approvalData)
                                    <div class="card-body">
                                        <label class="mt-4"> Message <span style="color: red;">*</span></label>

                                        <textarea rows="2" class="form-control mb-3 auto-focus" id="actionMessage-{{ $stage->m_id }}"></textarea>

                                        <div class="text-end">
                                            <button type="button"
                                                data-approval_status="{{ $approvalData?->fh_approver_status?->m_id }}"
                                                data-approval_type="0"
                                                data-approval_action_type="{{ $approvalData->pa_type }}"
                                                data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                data-atd_id="{{ md5($exit->er_id) }}"
                                                data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                data-master_module_id="{{ $managerReviewId }}"
                                                data-is_last_approval="{{ $approvalData->pa_last }}"
                                                data-emp_d_id="{{ optional($exit->fh_employee)->emp_d_id }}"
                                                class="btn btn-outline-danger small-btn  actionBtn mx-3">
                                                Reject
                                            </button>

                                            <!-- Approval Button -->
                                            <button type="button"
                                                data-approval_status="{{ $approvalData?->fh_approver_status?->m_id }}"
                                                data-approval_type="1"
                                                data-approval_action_type="{{ $approvalData->pa_type }}"
                                                data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                data-atd_id="{{ md5($exit->er_id) }}"
                                                data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                data-master_module_id="{{ $managerReviewId }}"
                                                data-is_last_approval="{{ $approvalData->pa_last }}"
                                                data-emp_d_id="{{ optional($exit->fh_employee)->emp_d_id }}"
                                                class="btn btn-outline-primary small-btn actionBtn">
                                                {{ $approvalData?->fh_approver_status?->m_name }}
                                            </button>
                                        </div>
                                    </div>
                                @elseif ($canApprove && $exit->er_module_id == $hrReviewId)
                                    <x-approval-form :moduleName="$exit?->fh_module?->m_name" :masterApproveBtn="$masterApproveBtn" :primaryId="$exit->er_id" :moduleId="$stage->m_id"
                                        actionUrl="{{ route('approve.fnf') }}" />
                                @endif
                            @endif
                            {{-- @endif --}}



                            <!-- ================= FINANCE ================= -->
                            {{-- @if ($exit->er_overall_status == 'MANAGER_APPROVED') --}}

                            {{-- @dd($stage->m_id , $financeReviewId); --}}
                            @if ($stage->m_id == $financeReviewId)
                                <div class="card-body p-0">
                                    <h4 style="margin-bottom: 15px;">Deductions / Recoveries</h4>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-0">
                                            <thead style="background-color: #dee3e3;">
                                                <tr>
                                                    <th style="width: 20%;" scope="col">Category</th>
                                                    <th style="width: 40%;" scope="col">Description</th>
                                                    <th style="width: 20%;" scope="col" class="text-end">Amount
                                                        (₹)
                                                    </th>
                                                    <th style="width: 20%;" scope="col">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Loan / Advance -->
                                                <tr>
                                                    <td><i class="bi bi-currency-rupee me-2"></i>Loan / Advance</td>
                                                    <td>Employee Advance / Loan Recovery</td>
                                                    <td class="text-end   deduction-amount">
                                                        {{ number_format($totalLoanRecovery ?? 0, 2) }}
                                                    </td>
                                                    <td><span>Recoverable</span></td>
                                                </tr>

                                                <!-- Assets / Kits (if recovery needed) -->
                                                {{-- @forelse ($assets as $asset)
                                                    @if ($asset && strtolower($asset->status) !== 'returned')
                                                        <tr>
                                                            <td><i class="bi bi-laptop me-2"></i>Assets</td>
                                                            <td>{{ $asset->assetType->name ?? 'Asset' }} -
                                                                {{ $asset->asset_tag }}
                                                                (Recovery if not returned)
                                                            </td>
                                                            <td class="text-end   deduction-amount">
                                                                {{ number_format($asset->purchase_value ?? 0, 2) }}
                                                            </td>

                                                            <td><span class="badge bg-warning text-dark">Pending
                                                                    Return</span></td>
                                                        </tr>
                                                    @endif
                                                @empty --}}
                                                {{-- <td colspan="4" class="text-center">No Assets Assigned</td> --}}
                                                {{-- @endforelse --}}


                                                <!-- Kits -->
                                                {{-- @forelse ($uniformItem as $item)
                                                    @if ($item->fh_stock && $item->fh_stock->kit)
                                                        <tr>
                                                            <td><i class="bi bi-receipt me-2"></i>Kits</td>
                                                            <td>{{ $item->fh_stock->kit->name }}</td>
                                                            <td class="text-end   deduction-amount">
                                                                {{ number_format($item->uit_total_price ?? 0, 2) }}
                                                            </td>
                                                            <td><span class="badge bg-warning text-dark">Pending
                                                                    Return</span></td>
                                                        </tr>
                                                    @endif
                                                @empty
                                                    <tr> --}}
                                                {{-- <td colspan="4" class="text-center">No Kits Assigned</td> --}}
                                                {{-- </tr>
                                                @endforelse --}}



                                                <!-- Adhoc Deduction -->
                                                <tr>
                                                    <td><i class="bi bi-receipt me-2"></i>Adhoc</td>
                                                    <td>Adhoc Deduction / Penalty (if any)</td>
                                                    <td class="text-end   deduction-amount">
                                                        {{ number_format($adhoc ?? 0, 2) }}
                                                    </td>
                                                    <td><span>Clear</span></td>
                                                </tr>

                                                <!-- Add Notice Pay, Damage Recovery, etc. here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="summary-card" style="margin-top: 30px;">
                                    <h3 style="margin-bottom: 15px;">Settlement Summary</h3>

                                    <div class="summary-row">
                                        <span class="summary-label">Total Recoverable:</span><span id="totalRecoverable"
                                            class="summary-value fw-bold ">₹0.00</span>
                                    </div>
                                </div>



                                @if ($approvalData)
                                    <div class="card-body">
                                        <label class="mt-4"> Message <span style="color: red;">*</span></label>


                                        <textarea rows="2" class="form-control mb-3 auto-focus" id="actionMessage-{{ $stage->m_id }}"></textarea>

                                        <div class="text-end">
                                            <button type="button"
                                                data-approval_status="{{ $approvalData?->fh_approver_status?->m_id }}"
                                                data-approval_type="0"
                                                data-approval_action_type="{{ $approvalData->pa_type }}"
                                                data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                data-atd_id="{{ md5($exit->er_id) }}"
                                                data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                data-master_module_id="{{ $managerReviewId }}"
                                                data-is_last_approval="{{ $approvalData->pa_last }}"
                                                data-emp_d_id="{{ optional($exit->fh_employee)->emp_d_id }}"
                                                class="btn btn-outline-danger  small-btn actionBtn mx-3">
                                                Reject
                                            </button>

                                            <!-- Approval Button -->
                                            <button type="button"
                                                data-approval_status="{{ $approvalData?->fh_approver_status?->m_id }}"
                                                data-approval_type="1"
                                                data-approval_action_type="{{ $approvalData->pa_type }}"
                                                data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                data-atd_id="{{ md5($exit->er_id) }}"
                                                data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                data-master_module_id="{{ $managerReviewId }}"
                                                data-is_last_approval="{{ $approvalData->pa_last }}"
                                                data-emp_d_id="{{ optional($exit->fh_employee)->emp_d_id }}"
                                                class="btn btn-outline-primary  small-btn actionBtn">
                                                {{ $approvalData?->fh_approver_status?->m_name }}
                                            </button>
                                        </div>
                                    </div>
                                @elseif ($canApprove && $exit->er_module_id == $financeReviewId)
                                    <x-approval-form :moduleName="$exit?->fh_module?->m_name" :masterApproveBtn="$masterApproveBtn" :primaryId="$exit->er_id"
                                        :moduleId="$stage->m_id" actionUrl="{{ route('approve.fnf') }}" />
                                @endif
                            @endif
                            {{-- @endif --}}



                            <!-- ================= DOCUMENT ================= -->
                            {{-- @if ($exit->er_overall_status == 'HR_APPROVED') --}}
                            @if ($stage->m_id == $adminReviewId)
                                <!-- KEEP YOUR ORIGINAL DOCUMENT UI HERE -->
                                {{-- paste your document section here --}}


                                <style>
                                    .document-item {
                                        margin: 12px 0;
                                    }

                                    .download-btn {
                                        width: 100%;
                                        padding: 14px 16px;
                                        background: #f8f9fa;
                                        border: 1px solid #ddd;
                                        border-radius: 6px;
                                        text-align: left;
                                        cursor: pointer;
                                        font-family: system-ui, sans-serif;
                                        transition: all 0.2s;
                                    }

                                    .download-btn:hover {
                                        background: #e9ecef;
                                        border-color: #adb5bd;
                                    }

                                    .download-btn span:first-child {
                                        font-weight: 600;
                                        display: block;
                                        margin-bottom: 4px;
                                    }

                                    .download-btn .file-type {
                                        color: #6c757d;
                                        font-size: 0.9em;
                                    }

                                    .download-btn .download-icon {
                                        float: right;
                                        color: #0d6efd;
                                        font-weight: bold;
                                    }
                                </style>
                                <style>
                                    /* Container styling */
                                    .documents-container {
                                        display: grid;
                                        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                                        gap: 20px;
                                        margin-top: 20px;
                                    }

                                    /* Card styling */
                                    .document-item {
                                        background: #f8f9fa;
                                        border-radius: 12px;
                                        padding: 20px;
                                        transition: transform 0.2s, box-shadow 0.2s;
                                        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
                                        display: flex;
                                        flex-direction: column;
                                    }

                                    .document-item:hover {
                                        transform: translateY(-5px);
                                        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
                                    }

                                    /* Link styling */
                                    .download-btn {
                                        text-decoration: none;
                                        color: #212529;
                                        display: flex;
                                        flex-direction: column;
                                        gap: 8px;
                                    }

                                    /* Title + numbering badge */
                                    .download-btn span:first-child {
                                        font-weight: 600;
                                        font-size: 1rem;
                                        display: flex;
                                        align-items: center;
                                        gap: 10px;
                                    }

                                    /* Badge for numbering */
                                    .download-btn span:first-child::before {
                                        color: white;
                                        font-size: 0.8rem;
                                        font-weight: bold;
                                        width: 24px;
                                        height: 24px;
                                        display: inline-flex;
                                        align-items: center;
                                        justify-content: center;
                                    }

                                    /* File type text */
                                    .file-type {
                                        font-size: 0.85rem;
                                        color: #6c757d;
                                    }

                                    /* Optional: add an icon for PDF */
                                    .download-btn span.file-type::before {
                                        content: '📄';
                                        margin-right: 5px;
                                    }
                                </style>

                                <div class="documents-container">
                                    <div class="document-item">
                                        <a href="{{ route('documents.generate', [Crypt::encrypt($exit->er_id), 'relieving']) }}"
                                            class="download-btn">
                                            <span>Relieving Letter</span>
                                            <span class="file-type">Document · PDF</span>
                                        </a>
                                    </div>

                                    <div class="document-item">
                                        <a href="{{ route('documents.generate', [Crypt::encrypt($exit->er_id), 'experience']) }}"
                                            class="download-btn">
                                            <span>Experience Certificate</span>
                                            <span class="file-type">Document · PDF</span>
                                        </a>
                                    </div>

                                    <div class="document-item">
                                        <a href="{{ route('documents.generate', [Crypt::encrypt($exit->er_id), 'noc']) }}"
                                            class="download-btn">
                                            <span>No Objection Certificate</span>
                                            <span class="file-type">Document · PDF</span>
                                        </a>
                                    </div>

                                    <div class="document-item">
                                        <a href="{{ route('documents.generate', [Crypt::encrypt($exit->er_id), 'nodues']) }}"
                                            class="download-btn">
                                            <span>No Dues Certificate</span>
                                            <span class="file-type">Document · PDF</span>
                                        </a>
                                    </div>

                                    <div class="document-item">
                                        <a href="{{ route('documents.generate', [Crypt::encrypt($exit->er_id), 'service']) }}"
                                            class="download-btn">
                                            <span>Service Certificate</span>
                                            <span class="file-type">Document · PDF</span>
                                        </a>
                                    </div>

                                    <div class="document-item">
                                        <a href="{{ route('documents.generate', [Crypt::encrypt($exit->er_id), 'full_final']) }}"
                                            class="download-btn">
                                            <span>Full & Final Settlement</span>
                                            <span class="file-type">Document · PDF</span>
                                        </a>
                                    </div>

                                    <div class="document-item">
                                        <a href="{{ route('documents.generate', [Crypt::encrypt($exit->er_id), 'salary_revision']) }}"
                                            class="download-btn">
                                            <span>SALARY REVISION LETTER</span>
                                            <span class="file-type">Document · PDF</span>
                                        </a>
                                    </div>

                                </div>


                                @if ($approvalData)
                                    <div class="card-body">
                                        <label class="mt-4"> Message <span style="color: red;">*</span></label>


                                        <textarea rows="2" class="form-control mb-3 auto-focus" id="actionMessage-{{ $stage->m_id }}"></textarea>

                                        <div class="text-end">
                                            <button type="button"
                                                data-approval_status="{{ $approvalData?->fh_approver_status?->m_id }}"
                                                data-approval_type="0"
                                                data-approval_action_type="{{ $approvalData->pa_type }}"
                                                data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                data-atd_id="{{ md5($exit->er_id) }}"
                                                data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                data-master_module_id="{{ $managerReviewId }}"
                                                data-is_last_approval="{{ $approvalData->pa_last }}"
                                                data-emp_d_id="{{ optional($exit->fh_employee)->emp_d_id }}"
                                                class="btn btn-outline-danger  small-btn actionBtn mx-3">
                                                Reject
                                            </button>

                                            <!-- Approval Button -->
                                            <button type="button"
                                                data-approval_status="{{ $approvalData?->fh_approver_status?->m_id }}"
                                                data-approval_type="1"
                                                data-approval_action_type="{{ $approvalData->pa_type }}"
                                                data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                data-atd_id="{{ md5($exit->er_id) }}"
                                                data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                data-master_module_id="{{ $managerReviewId }}"
                                                data-is_last_approval="{{ $approvalData->pa_last }}"
                                                data-emp_d_id="{{ optional($exit->fh_employee)->emp_d_id }}"
                                                class="btn btn-outline-primary  small-btn actionBtn">
                                                {{ $approvalData?->fh_approver_status?->m_name }}
                                            </button>


                                        </div>
                                    </div>
                                @elseif ($canApprove && $exit->er_module_id == $adminReviewId)
                                    <x-approval-form :moduleName="$exit?->fh_module?->m_name" :masterApproveBtn="$masterApproveBtn" :primaryId="$exit->er_id"
                                        :moduleId="$stage->m_id" actionUrl="{{ route('approve.fnf') }}" />
                                @endif
                            @endif
                            {{-- @endif --}}
                        </div>
                    @endforeach


                    <!-- ================= FINAL STEP ================= -->
                    {{-- @if ($exit->er_overall_status == 'RELIEVED') --}}
                    <div class="stage-content" id="stage{{ $fnfStages->count() + 2 }}">
                        <div class="text-center">
                            {{-- <h2 class="text-success">✅ Employee Successfully Relieved!</h2> --}}


                            <div class="success-animation">
                                <div class="checkmark">✅</div>
                                <h2 style="color: #28a745; margin-bottom: 15px;">Employee Successfully Relieved!</h2>
                                <p style="color: #666; margin-bottom: 30px;">The exit process has been completed
                                    successfully.
                                </p>
                            </div>

                            <div class="summary-card">
                                <h3 style="margin-bottom: 15px;">Final Summary</h3>
                                <div class="summary-row">
                                    <span class="summary-label">Employee Name:</span>
                                    <span class="summary-value" id="final_empName"></span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Employee ID:</span>
                                    <span class="summary-value" id="final_empId"></span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Last Working Day:</span>
                                    <span class="summary-value" id="final_lastWorkingDay"></span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Final Status:</span>
                                    <span class="summary-value"><span class="status-badge status-relieved">Relieved &
                                            Closed</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- @endif --}}
                </div>
            </div>
        </div>

        <!-- Right Side Container -->
        <div class="col-lg-4 order-lg-2">

            {{-- <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header">
                    <h5 class="mb-0">Key Contacts</h5>
                </div>
                <div class="card-body p-4" id="contactsList">

                    @php
                        $defaultContacts = [
                            ['id' => 1, 'role' => 'Manager', 'email' => optional($managerStage->employee)->emp_email],
                            ['id' => 2, 'role' => 'HR', 'email' => optional($hrStage->employee)->emp_email],
                            ['id' => 3, 'role' => 'Admin', 'email' => optional($adminStage->employee)->emp_email],
                            ['id' => 4, 'role' => 'Finance', 'email' => optional($financeStage->employee)->emp_email],
                        ];
                    @endphp

                    @foreach ($defaultContacts as $contact)
                        <div class="d-flex align-items-center mb-4 contact-row" data-id="{{ $contact['id'] }}">
                            <div class="bg-primary-subtle rounded-circle p-3 me-3 d-flex align-items-center justify-content-center"
                                style="width: 60px; height: 60px;">
                                <i class="las la-envelope la-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-dark role" data-field="role">{{ $contact['role'] }}</div>
                                <div class="email text-primary" data-field="email">{{ $contact['email'] }}</div>
                            </div>
                            {{-- <div class="ms-3">
                                 <button
                                    class="btn btn-outline-primary btn-sm editToggle"style="padding: 0.25rem 0.6rem; font-size: 0.8rem;">Edit</button>
                            </div> 
                        </div>
                    @endforeach
                </div>
            </div> --}}

            {{-- <script>
                $(document).ready(function() {

                    $(document).on('click', '.editToggle', function() {

                        let $btn = $(this);
                        let $row = $btn.closest('.contact-row');

                        if ($btn.text().trim() === 'Edit') {

                            // Switch to edit mode
                            $row.find('.role').each(function() {
                                let value = $(this).text().trim();
                                $(this).html(
                                    `<input type="text" 
                            class="form-control form-control-sm edit-input" readonly
                            data-field="role"
                            value="${value}" />`
                                );
                            });

                            $row.find('.email').each(function() {
                                let value = $(this).text().trim();
                                $(this).html(
                                    `<input type="email" 
                            class="form-control form-control-sm edit-input" 
                            data-field="email"
                            value="${value}" />`
                                );
                            });

                            $btn.text('Save');

                        } else {

                            let contactId = $row.data('id');
                            let role = $row.find('input[data-field="role"]').val().trim();
                            let email = $row.find('input[data-field="email"]').val().trim();

                            if (!role || !email) {
                                alert('Role and Email are required.');
                                return;
                            }

                            if (!validateEmail(email)) {
                                alert('Please enter valid email.');
                                return;
                            }

                            $btn.prop('disabled', true).text('Saving...');

                            $.ajax({
                                url: `/contacts/${contactId}`,
                                method: 'PUT',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    role: role,
                                    email: email
                                },
                                success: function(res) {

                                    $row.find('.role').text(role);
                                    $row.find('.email').text(email);

                                    $btn.prop('disabled', false).text('Edit');


                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: 'Contact updated successfully',
                                        showConfirmButton: false,
                                        timer: 2000,
                                        timerProgressBar: true
                                    });

                                },
                                error: function() {
                                    $btn.prop('disabled', false).text('Save');
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: 'Something went wrong!',
                                        confirmButtonText: 'Try Again'
                                    });
                                }
                            });
                        }

                    });

                    function validateEmail(email) {
                        let regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        return regex.test(email);
                    }

                });
            </script> --}}

            <!-- Notes Card -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden position-relative">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Important Notes</h5>
                    <button id="editNotesBtn"
                        class="btn btn-outline-primary btn-sm"style="padding: 0.25rem 0.6rem; font-size: 0.8rem;">Edit</button>

                </div>
                <div class="card-body p-4">
                    <!-- Display mode -->
                    <p id="notesText" class="mb-0 text-dark lh-lg">
                        {{ $signatures->notes ?? 'Not Found' }}
                    </p>
                    <!-- Edit mode (hidden by default) -->
                    <textarea id="notesTextarea" class="form-control d-none" rows="5">{{ $signatures->notes ?? 'Ensure all company assets are returned, full & final settlement is initiated, and knowledge transfer is documented before final relieving.' }}</textarea>
                </div>
            </div>

            <script>
                $(document).ready(function() {
                    $(document).on('click', '#editNotesBtn', function() {
                        let $btn = $(this);
                        let $card = $btn.closest('.card'); // get card container
                        let $text = $card.find('p');
                        let $textarea = $card.find('textarea');

                        if ($btn.text() === 'Edit') {
                            // Switch to edit mode
                            $text.addClass('d-none');
                            $textarea.removeClass('d-none');
                            $btn.text('Save');
                        } else {
                            // Save notes
                            let notes = $textarea.val();

                            $.ajax({
                                url: "{{ route('exit.updateNotes') }}",
                                method: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    notes: notes
                                },
                                success: function(res) {
                                    if (res.success) {
                                        $text.text(notes).removeClass('d-none');
                                        $textarea.addClass('d-none');
                                        $btn.text('Edit');
                                        alert('Notes updated successfully!');
                                    } else {
                                        alert('Failed to save notes.');
                                    }
                                },
                                error: function() {
                                    alert('Error saving notes.');
                                }
                            });
                        }
                    });
                });
            </script>

            @if ($exit->er_overall_status === 'RELIEVED')
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mt-4">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Documents</h5>

                        <a href="{{ route('documents.downloadAll', Crypt::encrypt($exit->er_id)) }}"
                            class="btn btn-outline-primary  small-btn">
                            Download All (ZIP)
                        </a>
                    </div>

                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">

                            {{-- Relieving Letter --}}
                            <li class="list-group-item list-group-item-action px-4 py-3">
                                <a href="{{ route('documents.generate', [Crypt::encrypt($exit->er_id), 'relieving']) }}"
                                    target="_blank" class="text-decoration-none d-flex align-items-center text-primary">
                                    <i class="las la-file-pdf me-3 text-danger la-2x"></i>
                                    Relieving Letter
                                </a>
                            </li>

                            {{-- Experience Certificate --}}
                            <li class="list-group-item list-group-item-action px-4 py-3">
                                <a href="{{ route('documents.generate', [Crypt::encrypt($exit->er_id), 'experience']) }}"
                                    target="_blank" class="text-decoration-none d-flex align-items-center text-primary">
                                    <i class="las la-file-pdf me-3 text-danger la-2x"></i>
                                    Experience Certificate
                                </a>
                            </li>

                            {{-- NOC --}}
                            <li class="list-group-item list-group-item-action px-4 py-3">
                                <a href="{{ route('documents.generate', [Crypt::encrypt($exit->er_id), 'noc']) }}"
                                    target="_blank" class="text-decoration-none d-flex align-items-center text-primary">
                                    <i class="las la-file-pdf me-3 text-danger la-2x"></i>
                                    No Objection Certificate
                                </a>
                            </li>

                            {{-- No Dues --}}
                            <li class="list-group-item list-group-item-action px-4 py-3">
                                <a href="{{ route('documents.generate', [Crypt::encrypt($exit->er_id), 'nodues']) }}"
                                    target="_blank" class="text-decoration-none d-flex align-items-center text-primary">
                                    <i class="las la-file-pdf me-3 text-danger la-2x"></i>
                                    No Dues Certificate
                                </a>
                            </li>

                            {{-- Service Certificate --}}
                            <li class="list-group-item list-group-item-action px-4 py-3">
                                <a href="{{ route('documents.generate', [Crypt::encrypt($exit->er_id), 'service']) }}"
                                    target="_blank" class="text-decoration-none d-flex align-items-center text-primary">
                                    <i class="las la-file-pdf me-3 text-danger la-2x"></i>
                                    Service Certificate
                                </a>
                            </li>

                            {{-- Full & Final Settlement --}}
                            <li class="list-group-item list-group-item-action px-4 py-3">
                                <a href="{{ route('documents.generate', [Crypt::encrypt($exit->er_id), 'full_final']) }}"
                                    target="_blank" class="text-decoration-none d-flex align-items-center text-primary">
                                    <i class="las la-file-pdf me-3 text-danger la-2x"></i>
                                    Full & Final Settlement
                                </a>
                            </li>

                            <li class="list-group-item list-group-item-action px-4 py-3">
                                <a href="{{ route('documents.generate', [Crypt::encrypt($exit->er_id), 'salary_revision']) }}"
                                    target="_blank" class="text-decoration-none d-flex align-items-center text-primary">
                                    <i class="las la-file-pdf me-3 text-danger la-2x"></i>
                                   SALARY REVISION LETTER
                                </a>
                            </li>



                        </ul>
                    </div>
                </div>
            @endif


            {{-- @if ($exit->er_overall_status === 'RELIEVED')
                Stage 1: Manager
                <div class="card p-3 mt-4">
                    <h5>Manager</h5>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" disabled
                            {{ isset($emp_checks_data->handover_documents) && $emp_checks_data->handover_documents == 1 ? 'checked' : '' }}
                            id="handover_documents">
                        <label for="handover_documents">Handover Documents</label>
                    </div>
                </div>
                Stage 4: Finance
                <div class="card p-3 mt-4">
                    <h4>Finance</h4>

                    @php
                        $financeItems = [
                            'signatory' => 'Authorized Signatory Removal',
                            'lease' => 'Lease Termination (Self/Company)',
                            'loan' => 'Company Loan',
                            'salary_adv' => 'Salary Advance',
                            'travel' => 'Travel Advance',
                            'deduction' => 'Other Deduction',
                            'bank_loan' => 'Bank Loans',
                            'pf' => 'PF Loan',
                            'notice' => 'Notice Period Shortfall Recovery',
                            'buyback' => 'Any Buy Back',
                            'accessories' => 'Accessories',
                            'finance_others' => 'Others',
                        ];
                    @endphp

                    @foreach ($financeItems as $key => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" disabled
                                {{ isset($emp_checks_data->$key) && $emp_checks_data->$key == 1 ? 'checked' : '' }}
                                id="{{ $key }}">
                            <label for="{{ $key }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>

                Stage 2: HR
                <div class="card p-3 mt-4">
                    <h5>HR</h5>


                    @php
                        $hrItems = [
                            'id_card' => 'Employee ID Card',
                            'insurance_card' => 'Insurance Card',
                            'helmet' => 'Helmet',
                            'exit_interview' => 'Exit Interview',
                            'vehicle' => 'Vehicle',
                            'petrol_card' => 'Petrol Card',
                            'hr_others' => 'Others',
                        ];
                    @endphp

                    @foreach ($hrItems as $key => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" disabled
                                {{ isset($emp_checks_data->$key) && $emp_checks_data->$key == 1 ? 'checked' : '' }}
                                id="{{ $key }}">
                            <label for="{{ $key }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>

                Stage 3: IT
                <div class="card p-3 mt-4">
                    <h5>Admin/IT</h5>


                    @php
                        $itItems = [
                            'laptop' => 'Laptop / Desktop',
                            'mouse' => 'Additional KB / Mouse',
                            'email' => 'Official Mail ID & Password',
                            'mobile' => 'Mobile & Charger',
                            'storage' => 'Storage Devices (Pendrive / USB HDD)',
                            'access' => 'Any Electronic Access',
                            'whatsapp' => 'WhatsApp Group Exit & Ownership Transfer',
                            'github' => 'GitHub Ownership & Credentials',
                            'sheet' => 'Spreadsheet Ownership Transfer',
                            'credentials' => 'Change Credentials',
                            'it_others' => 'Others',
                        ];
                    @endphp

                    @foreach ($itItems as $key => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" disabled
                                {{ isset($emp_checks_data->$key) && $emp_checks_data->$key == 1 ? 'checked' : '' }}
                                id="{{ $key }}">
                            <label for="{{ $key }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>


            @endif --}}


            {{-- @if ($exit->er_overall_status === 'RESIGNATION_SUBMITTED')
                <form method="POST" action="{{ route('exit.clearance.save', $exit->er_id) }}">
                    @csrf
                    <div class="card p-3 mt-4">
                        <h5>Manager</h5>


                        <input type="hidden" name="stage" value="{{ $exit->er_id }}">
                        <div class="form-check">
                            <input type="hidden" name="handover_documents" value="0">
                            <input class="form-check-input" type="checkbox" name="handover_documents" value="1"
                                {{ isset($emp_checks_data->handover_documents) && $emp_checks_data->handover_documents == 1 ? 'checked' : '' }}
                                id="handover_documents">
                            <label class="form-check-label" for="handover_documents">Handover Documents</label>
                        </div>

                        <br>
                        <button type="submit" style="width: 22%;"   class="btn btn-outline-primary  small-btn">Save</button>
                    </div>
                </form>
            @endif

            @if ($exit->er_overall_status === 'MANAGER_APPROVED')
                <form method="POST" action="{{ route('exit.clearance.save', $exit->er_id) }}">
                    @csrf
                    <div class="card p-3 mt-4">
                        <h5>Finance</h5>

                        <input type="hidden" name="stage" value="finance">

                        <div class="form-check">
                            <input type="hidden" name="signatory" value="0">
                            <input class="form-check-input" type="checkbox" name="signatory" value="1"
                                {{ isset($emp_checks_data->signatory) && $emp_checks_data->signatory == 1 ? 'checked' : '' }}
                                id="signatory">
                            <label class="form-check-label" for="signatory">Authorized Signatory Removal</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="lease" value="0">
                            <input class="form-check-input" type="checkbox" name="lease" value="1"
                                {{ isset($emp_checks_data->lease) && $emp_checks_data->lease == 1 ? 'checked' : '' }}
                                id="lease">
                            <label class="form-check-label" for="lease">Lease Termination (Self/Company)</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="loan" value="0">
                            <input class="form-check-input" type="checkbox" name="loan" value="1"
                                {{ isset($emp_checks_data->loan) && $emp_checks_data->loan == 1 ? 'checked' : '' }}
                                id="loan">
                            <label class="form-check-label" for="loan">Company Loan</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="salary_adv" value="0">
                            <input class="form-check-input" type="checkbox" name="salary_adv" value="1"
                                {{ isset($emp_checks_data->salary_adv) && $emp_checks_data->salary_adv == 1 ? 'checked' : '' }}
                                id="salary_adv">
                            <label class="form-check-label" for="salary_adv">Salary Advance</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="travel" value="0">
                            <input class="form-check-input" type="checkbox" name="travel" value="1"
                                {{ isset($emp_checks_data->travel) && $emp_checks_data->travel == 1 ? 'checked' : '' }}
                                id="travel">
                            <label class="form-check-label" for="travel">Travel Advance</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="deduction" value="0">
                            <input class="form-check-input" type="checkbox" name="deduction" value="1"
                                {{ isset($emp_checks_data->deduction) && $emp_checks_data->deduction == 1 ? 'checked' : '' }}
                                id="deduction">
                            <label class="form-check-label" for="deduction">Other Deduction</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="bank_loan" value="0">
                            <input class="form-check-input" type="checkbox" name="bank_loan" value="1"
                                {{ isset($emp_checks_data->bank_loan) && $emp_checks_data->bank_loan == 1 ? 'checked' : '' }}
                                id="bank_loan">
                            <label class="form-check-label" for="bank_loan">Bank Loans</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="pf" value="0">
                            <input class="form-check-input" type="checkbox" name="pf" value="1"
                                {{ isset($emp_checks_data->pf) && $emp_checks_data->pf == 1 ? 'checked' : '' }}
                                id="pf">
                            <label class="form-check-label" for="pf">PF Loan</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="notice" value="0">
                            <input class="form-check-input" type="checkbox" name="notice" value="1"
                                {{ isset($emp_checks_data->notice) && $emp_checks_data->notice == 1 ? 'checked' : '' }}
                                id="notice">
                            <label class="form-check-label" for="notice">Notice Period Shortfall Recovery</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="buyback" value="0">
                            <input class="form-check-input" type="checkbox" name="buyback" value="1"
                                {{ isset($emp_checks_data->buyback) && $emp_checks_data->buyback == 1 ? 'checked' : '' }}
                                id="buyback">
                            <label class="form-check-label" for="buyback">Any Buy Back</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="accessories" value="0">
                            <input class="form-check-input" type="checkbox" name="accessories" value="1"
                                {{ isset($emp_checks_data->accessories) && $emp_checks_data->accessories == 1 ? 'checked' : '' }}
                                id="accessories">
                            <label class="form-check-label" for="accessories">Accessories</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="finance_others" value="0">
                            <input class="form-check-input" type="checkbox" name="finance_others" value="1"
                                {{ isset($emp_checks_data->finance_others) && $emp_checks_data->finance_others == 1 ? 'checked' : '' }}
                                id="finance_others">
                            <label class="form-check-label" for="finance_others">Others</label>
                        </div>

                        <br>
                        <button type="submit" style="width: 22%;"      class="btn btn-outline-primary  small-btn">Save</button>
                    </div>
                </form>
            @endif

            @if ($exit->er_overall_status === 'HR_APPROVED')
                <form method="POST" action="{{ route('exit.clearance.save', $exit->er_id) }}">
                    @csrf
                    <div class="card p-3 mt-4">
                        <h5>Admin / IT</h5>

                        <input type="hidden" name="stage" value="it">

                        <div class="form-check">
                            <input type="hidden" name="laptop" value="0">
                            <input class="form-check-input" type="checkbox" name="laptop" value="1"
                                {{ isset($emp_checks_data->laptop) && $emp_checks_data->laptop == 1 ? 'checked' : '' }}
                                id="laptop">
                            <label class="form-check-label" for="laptop">Laptop / Desktop</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="mouse" value="0">
                            <input class="form-check-input" type="checkbox" name="mouse" value="1"
                                {{ isset($emp_checks_data->mouse) && $emp_checks_data->mouse == 1 ? 'checked' : '' }}
                                id="mouse">
                            <label class="form-check-label" for="mouse">Additional KB / Mouse</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="email" value="0">
                            <input class="form-check-input" type="checkbox" name="email" value="1"
                                {{ isset($emp_checks_data->email) && $emp_checks_data->email == 1 ? 'checked' : '' }}
                                id="email">
                            <label class="form-check-label" for="email">Official Mail ID & Password</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="mobile" value="0">
                            <input class="form-check-input" type="checkbox" name="mobile" value="1"
                                {{ isset($emp_checks_data->mobile) && $emp_checks_data->mobile == 1 ? 'checked' : '' }}
                                id="mobile">
                            <label class="form-check-label" for="mobile">Mobile & Charger</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="storage" value="0">
                            <input class="form-check-input" type="checkbox" name="storage" value="1"
                                {{ isset($emp_checks_data->storage) && $emp_checks_data->storage == 1 ? 'checked' : '' }}
                                id="storage">
                            <label class="form-check-label" for="storage">Storage Devices (Pendrive / USB HDD)</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="access" value="0">
                            <input class="form-check-input" type="checkbox" name="access" value="1"
                                {{ isset($emp_checks_data->access) && $emp_checks_data->access == 1 ? 'checked' : '' }}
                                id="access">
                            <label class="form-check-label" for="access">Any Electronic Access</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="whatsapp" value="0">
                            <input class="form-check-input" type="checkbox" name="whatsapp" value="1"
                                {{ isset($emp_checks_data->whatsapp) && $emp_checks_data->whatsapp == 1 ? 'checked' : '' }}
                                id="whatsapp">
                            <label class="form-check-label" for="whatsapp">WhatsApp Group Exit & Ownership
                                Transfer</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="github" value="0">
                            <input class="form-check-input" type="checkbox" name="github" value="1"
                                {{ isset($emp_checks_data->github) && $emp_checks_data->github == 1 ? 'checked' : '' }}
                                id="github">
                            <label class="form-check-label" for="github">GitHub Ownership & Credentials</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="sheet" value="0">
                            <input class="form-check-input" type="checkbox" name="sheet" value="1"
                                {{ isset($emp_checks_data->sheet) && $emp_checks_data->sheet == 1 ? 'checked' : '' }}
                                id="sheet">
                            <label class="form-check-label" for="sheet">Spreadsheet Ownership Transfer</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="credentials" value="0">
                            <input class="form-check-input" type="checkbox" name="credentials" value="1"
                                {{ isset($emp_checks_data->credentials) && $emp_checks_data->credentials == 1 ? 'checked' : '' }}
                                id="credentials">
                            <label class="form-check-label" for="credentials">Change Credentials</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="it_others" value="0">
                            <input class="form-check-input" type="checkbox" name="it_others" value="1"
                                {{ isset($emp_checks_data->it_others) && $emp_checks_data->it_others == 1 ? 'checked' : '' }}
                                id="it_others">
                            <label class="form-check-label" for="it_others">Others</label>
                        </div>

                        <br>
                        <button type="submit" style="width: 22%;"      class="btn btn-outline-primary  small-btn">Save </button>
                    </div>
                </form>
            @endif

            @if ($exit->er_overall_status === 'CLEARANCE_IN_PROGRESS')
                <form method="POST" action="{{ route('exit.clearance.save', $exit->er_id) }}">
                    @csrf
                    <div class="card p-3 mt-4">
                        <h5>HR</h5>

                        <input type="hidden" name="stage" value="hr">

                        <div class="form-check">
                            <input type="hidden" name="id_card" value="0">
                            <input class="form-check-input" type="checkbox" name="id_card" value="1"
                                {{ isset($emp_checks_data->id_card) && $emp_checks_data->id_card == 1 ? 'checked' : '' }}
                                id="id_card">
                            <label class="form-check-label" for="id_card">Employee ID Card</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="insurance_card" value="0">
                            <input class="form-check-input" type="checkbox" name="insurance_card" value="1"
                                {{ isset($emp_checks_data->insurance_card) && $emp_checks_data->insurance_card == 1 ? 'checked' : '' }}
                                id="insurance_card">
                            <label class="form-check-label" for="insurance_card">Insurance Card</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="helmet" value="0">
                            <input class="form-check-input" type="checkbox" name="helmet" value="1"
                                {{ isset($emp_checks_data->helmet) && $emp_checks_data->helmet == 1 ? 'checked' : '' }}
                                id="helmet">
                            <label class="form-check-label" for="helmet">Helmet</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="exit_interview" value="0">
                            <input class="form-check-input" type="checkbox" name="exit_interview" value="1"
                                {{ isset($emp_checks_data->exit_interview) && $emp_checks_data->exit_interview == 1 ? 'checked' : '' }}
                                id="exit_interview">
                            <label class="form-check-label" for="exit_interview">Exit Interview</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="vehicle" value="0">
                            <input class="form-check-input" type="checkbox" name="vehicle" value="1"
                                {{ isset($emp_checks_data->vehicle) && $emp_checks_data->vehicle == 1 ? 'checked' : '' }}
                                id="vehicle">
                            <label class="form-check-label" for="vehicle">Vehicle</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="petrol_card" value="0">
                            <input class="form-check-input" type="checkbox" name="petrol_card" value="1"
                                {{ isset($emp_checks_data->petrol_card) && $emp_checks_data->petrol_card == 1 ? 'checked' : '' }}
                                id="petrol_card">
                            <label class="form-check-label" for="petrol_card">Petrol Card</label>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="hr_others" value="0">
                            <input class="form-check-input" type="checkbox" name="hr_others" value="1"
                                {{ isset($emp_checks_data->hr_others) && $emp_checks_data->hr_others == 1 ? 'checked' : '' }}
                                id="hr_others">
                            <label class="form-check-label" for="hr_others">Others</label>
                        </div>

                        <br>
                        <button type="submit" style="width: 22%;"      class="btn btn-outline-primary  small-btn">Save </button>
                    </div>
                </form>
            @endif --}}
        </div>

    @endsection
    @section('script')

        <script>
            // 🔹 Exit Data from backend
            const exitData = {!! json_encode([
                'name' => $exit->employee->emp_full_name ?? '',
                'id' => $exit->employee->emp_code ?? '',
                'department' => $exit->employee->fh_department->d_name ?? '',
                'designation' => $exit->employee->fh_designation->dg_name ?? '',
                'resignDate' => optional($exit->employee->emp_separation_submit_date)->format('d M Y'),
                'lastWorkingDay' => optional($exit->er_last_working_day)->format('d M Y'),
                'reason' => $exit->exitType->m_name ?? '',
                'managerRemark' => $exit->er_manager_remark ?? '',
                'managerDate' => optional($exit->er_manager_action_at)->format('d M Y H:i'),
                'hrRemark' => $exit->er_hr_remark ?? '',
                'hrDate' => optional($exit->er_hr_action_at)->format('d M Y H:i'),
                'financeRemark' => $exit->er_finance_remark ?? '',
                'financeDate' => optional($exit->er_finance_action_at)->format('d M Y H:i'),
                'status' => $exit->er_overall_status,
                'moduledata' => $exit->er_module_id,
            ]) !!};

            let currentStage = 1;

            let processData = {
                employee: exitData,
                manager: {
                    remark: exitData.managerRemark,
                    date: exitData.managerDate
                },
                hr: {
                    remark: exitData.hrRemark,
                    date: exitData.hrDate
                },
                finance: {
                    remark: exitData.financeRemark,
                    date: exitData.financeDate
                },
                documents: {}
            };

            // 🔹 Dynamic Stage Mapping (skip null m_id)
            const stageMapping = {
                @foreach ($fnfStages as $stage)
                    @if ($stage->m_id)
                        {{ $stage->m_id }}: {{ $loop->index + 2 }},
                    @endif
                @endforeach
                'final': {{ $fnfStages->whereNotNull('m_id')->count() + 2 }} // Final Relieved stage
            };

            // 🔹 Get stage from status & module dynamically
            function getStageFromStatus(status, moduleId) {
                // 🔹 Final stage
                if (status === 'RELIEVED' || status === 'DOCUMENTS_AND_RELIEVING') {
                    return stageMapping['final'];
                }

                // 🔹 Rejections
                if (status === 'MANAGER_REJECTED') return stageMapping[Object.keys(stageMapping)[0]] || 2;
                if (status === 'HR_REJECTED') return stageMapping[Object.keys(stageMapping)[1]] || 3;

                // 🔹 Dynamic module mapping
                if (moduleId && stageMapping[moduleId]) return stageMapping[moduleId];

                // 🔹 fallback
                return 2;
            }

            document.addEventListener('DOMContentLoaded', function() {
                initFileUploads();

                const startStage = getStageFromStatus(exitData.status, exitData.moduledata);
                moveToStage(startStage);
            });

            // 🔹 FILE UPLOADS
            function initFileUploads() {
                ['relievingLetter', 'experienceLetter', 'nocForm', 'settlementDocs'].forEach(id => {
                    const input = document.getElementById(id);
                    if (input) input.addEventListener('change', () => displayFileList(input));
                });
            }

            function displayFileList(input) {
                const list = document.getElementById(input.id + 'List');
                if (!list) return;
                list.innerHTML = '';
                [...input.files].forEach((file, index) => {
                    const item = document.createElement('div');
                    item.className = 'file-item';
                    item.innerHTML =
                        `📎 ${file.name} <button type="button" onclick="removeFile('${input.id}',${index})">Remove</button>`;
                    list.appendChild(item);
                });
            }

            function removeFile(inputId, index) {
                const input = document.getElementById(inputId);
                const dt = new DataTransfer();
                [...input.files].forEach((file, i) => i !== index && dt.items.add(file));
                input.files = dt.files;
                displayFileList(input);
            }

            // 🔹 STAGE NAVIGATION
            function moveToStage(stage) {
                document.querySelectorAll('.stage-content').forEach(el => el.classList.remove('active'));
                const stageEl = document.getElementById('stage' + stage);
                if (!stageEl) return;

                stageEl.classList.add('active');
                updateProgress(stage);

                // Stop further stages if rejected
                if ((exitData.status === 'MANAGER_REJECTED' && stage === 2) ||
                    (exitData.status === 'HR_REJECTED' && stage === 3)) {
                    stageEl.querySelectorAll('button, textarea, input').forEach(el => el.disabled = true);
                    showAlert('Process stopped due to rejection.', 'warning');
                    return;
                }

                // Populate dynamic stages
                populateStageData(stage);

                currentStage = stage;
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }

            function updateProgress(stage) {
                const steps = document.querySelectorAll('.step');
                const fill = document.getElementById('progressFill');
                const totalSteps = steps.length;

                steps.forEach((step, index) => {
                    const num = index + 1;
                    step.classList.remove('active', 'completed');
                    if (num < stage) step.classList.add('completed');
                    if (num === stage) step.classList.add('active');
                });

                fill.style.width = ((stage - 1) / (totalSteps - 1)) * 100 + '%';
            }

            function populateStageData(stage) {
                // Stage-specific population
                if (stageMapping['final'] === stage) {
                    // Final Stage
                    document.getElementById('final_empName').textContent = processData.employee.name;
                    document.getElementById('final_empId').textContent = processData.employee.id;
                    document.getElementById('final_lastWorkingDay').textContent = processData.employee.lastWorkingDay;
                    document.getElementById('timeline_manager').textContent = processData.manager.date || '';
                    document.getElementById('timeline_managerRemark').textContent = processData.manager.remark || '';
                    document.getElementById('timeline_hr').textContent = processData.hr.date || '';
                    document.getElementById('timeline_hrRemark').textContent = processData.hr.remark || '';
                    document.getElementById('timeline_finance').textContent = processData.finance.date || '';
                    document.getElementById('timeline_financeRemark').textContent = processData.finance.remark || '';
                } else if (stage === stageMapping[exitData.moduledata]) {
                    // Current stage remark population
                    if (stage === stageMapping[Object.keys(stageMapping)[0]]) { // Manager
                        document.getElementById('displayManagerRemark').textContent = processData.manager.remark || '—';
                        document.getElementById('managerRemarkDate').textContent = processData.manager.date || '';
                    }
                }
            }

            // 🔹 ALERT
            function showAlert(msg, type = 'success') {
                const box = document.getElementById('alertBox');
                if (!box) return;
                box.className = `alert alert-${type} show`;
                box.textContent = msg;
                setTimeout(() => box.classList.remove('show'), 4000);
            }

            // 🔹 APPROVE / REJECT HANDLING
            function submitReview(stageType, action) {
                const remarkInputId = stageType === 'manager' ? 'managerRemark' : 'hrRemark';
                const remark = document.getElementById(remarkInputId)?.value.trim();
                if (!remark) return Swal.fire({
                    icon: 'warning',
                    title: 'Oops!',
                    text: 'Please enter remarks.'
                });

                const exitId = @json($exit->er_id);
                fetch(`/admin/employee-exit/exit/${exitId}/${stageType}/${action}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            remark
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: `${stageType.charAt(0).toUpperCase() + stageType.slice(1)} ${action} successfully.`,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            const badge = document.querySelector(
                                `#stage${getStageFromStatus(exitData.status, exitData.moduledata)} .status-badge`);
                            if (badge) {
                                badge.textContent = action === 'approve' ? 'Approved' : 'Rejected';
                                badge.className =
                                    `status-badge ${action === 'approve' ? 'status-approved' : 'status-rejected'}`;
                            }
                            document.querySelector(
                                    `#stage${getStageFromStatus(exitData.status, exitData.moduledata)} .button-group`)
                                ?.setAttribute('style', 'display:none');
                            if (action === 'approve') moveToStage(getStageFromStatus(exitData.status, exitData.moduledata) +
                                1);
                        } else Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Something went wrong!'
                        });
                    });
            }

            function approveManager() {
                submitReview('manager', 'approve');
            }

            function rejectManager() {
                submitReview('manager', 'reject');
            }

            function approveHR() {
                submitReview('hr', 'approve');
            }

            function rejectHR() {
                submitReview('hr', 'reject');
            }
        </script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {

                function parseAmount(text) {
                    return parseFloat(
                        text.replace(/[^0-9.-]+/g, "")
                    ) || 0;
                }

                function formatCurrency(amount) {
                    return "₹" + amount.toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                function calculateSettlement() {

                    let totalPayable = 0;
                    let totalRecoverable = 0;

                    // ===== Earnings =====
                    document.querySelectorAll(".earning-amount").forEach(function(el) {
                        totalPayable += parseAmount(el.innerText);
                    });

                    // ===== Deductions =====
                    document.querySelectorAll(".deduction-amount").forEach(function(el) {
                        totalRecoverable += parseAmount(el.innerText);
                    });

                    let netSettlement = totalPayable - totalRecoverable;

                    // ===== Update UI =====
                    document.getElementById("totalPayable").innerText =
                        formatCurrency(totalPayable);

                    document.getElementById("totalRecoverable").innerText =
                        formatCurrency(totalRecoverable);

                    let netEl = document.getElementById("netSettlement");

                    if (netSettlement > 0) {
                        netEl.innerText = formatCurrency(netSettlement) + " (Payable)";
                    } else if (netSettlement < 0) {
                        netEl.innerText = formatCurrency(Math.abs(netSettlement));

                    } else {
                        netEl.innerText = "₹0.00";
                        netEl.style.color = "#6c757d";
                    }
                }
                calculateSettlement();

            });
        </script>

        <script>
            $(document).on('click', '.actionBtn', function() {
                $(".actionBtn").attr("disabled", true);
                var planId = '';
                var dataAttributes = {};
                $.each(this.attributes, function() {
                    if (this.name.startsWith('data-')) {
                        var key = this.name.slice(5); // remove 'data-' prefix
                        dataAttributes[key] = this.value;
                    }
                });

                masterModuleId = dataAttributes["master_module_id"];
                planId = dataAttributes["atd_id"];
                dataAttributes['message'] = $('#actionMessage-{{ $exit->er_module_id }}').val();


                // dataAttributes['message'] = $('#actionMessage').val();

                if (dataAttributes['message'] == '') {
                    Swal.fire({
                        icon: "warning",
                        text: 'Message is required.',
                        timer: 3000,
                    });
                    $(".actionBtn").attr("disabled", false);
                    return false;
                }

                $.ajax({
                    url: '{{ route('admin.approval-handler') }}',
                    method: "post",
                    data: {
                        _token: '{{ csrf_token() }}',
                        POST_TYPE: 'FNF_REQUEST_APPROVAL',
                        data: dataAttributes
                    },
                    dataType: "json",
                    beforeSend: function() {
                        $("#gloabal-overlay").show();
                        $(".actionBtn").attr("disabled", true);
                    },
                    success: function(data) {
                        $("#gloabal-overlay").hide();
                        if (data.status == true) {
                            Swal.fire({
                                icon: "success",
                                text: data.message,
                                timer: 3000,
                            });
                            var baseUrl = $('#ajaxCall').val();
                            window.location.reload();

                        } else {
                            Swal.fire({
                                icon: "warning",
                                text: data.message,
                                timer: 3000,
                            });
                            $('.actionBtn').prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        $("#gloabal-overlay").hide();
                        $('.actionBtn').prop('disabled', false);
                        Swal.fire({
                            icon: "error",
                            text: error,
                            timer: 3000,
                        });
                    },
                });
            });
        </script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const el = document.querySelector(".auto-focus");
                if (el) {
                    el.focus();
                }
            });
        </script>

        <script src="{{ asset('assets/js/approval-form.js') }}"></script>
    @endsection
