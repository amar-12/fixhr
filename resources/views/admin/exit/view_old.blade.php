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
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
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
                    <li><a href="{{ url('/dashboard') }}">Employee Exit Requests</a></li>
                    <li class="active"><span><b>View Exit Details</b></span></li>


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
                            <div class="fw-bold ">
                                {{ optional($exit->employee->emp_date_of_joining)->format('d M Y') ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-6 col-sm-2">
                            <div class="mb-1"><i class="bi bi-calendar2-check me-1"></i> Submission Date</div>
                            <div class="fw-bold ">
                                {{ optional($exit->employee->emp_separation_submit_date)->format('d M Y') ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-6 col-sm-2">
                            <div class="mb-1"><i class="bi bi-clock-history me-1"></i> Last Working Day</div>
                            <div class="fw-bold ">
                                {{ optional($exit->er_last_working_day)->format('d M Y') ?? 'N/A' }}
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
                        <div class="step" data-step="2">
                            <div class="step-circle">2</div>
                            <div class="step-label">Manager Review</div>
                        </div>
                        <div class="step" data-step="3">
                            <div class="step-circle">3</div>
                            {{-- <div class="step-label">HR Review</div> --}}
                            <div class="step-label">Clearance</div>

                        </div>
                        <div class="step" data-step="4">
                            <div class="step-circle">4</div>
                            {{-- <div class="step-label">Clearance</div> --}}
                            <div class="step-label">HR Review</div>

                        </div>
                        <div class="step" data-step="5">
                            <div class="step-circle">5</div>
                            <div class="step-label">Documentation</div>
                        </div>
                        <div class="step" data-step="6">
                            <div class="step-circle">6</div>
                            <div class="step-label">Relieved</div>
                        </div>
                    </div>
                </div>

                <div class="content-area">
                    <div id="alertBox" class="alert"></div>

                    <!-- Stage 1: Resignation Submitted -->
                    <div class="stage-content completed" id="stage1"> <!-- added 'completed' -->
                        <div class="stage-header">
                            <div class="stage-title">
                                <h4>Step 1: Submit Resignation</h4>
                                <span class="status-badge status-submitted">Submitted</span> <!-- default submitted -->
                            </div>
                        </div>

                        <form id="resignationForm" style="display:none;"> <!-- hide the form -->
                            <!-- form fields -->
                            <div class="button-group">
                                <button type="submit" class="btn btn-outline-primary">Submit Resignation →</button>
                            </div>
                        </form>
                    </div>

                    <!-- Stage 2: Manager Review -->
                    <div class="stage-content" id="stage2">
                        <div class="stage-header">
                            <div class="stage-title">
                                <h4>Step 2: Manager Review</h4>
                                <span class="status-badge status-submitted">Pending Approval</span>
                            </div>
                        </div>

                        {{-- @if ($exit->er_overall_status === 'RESIGNATION_SUBMITTED' && optional($managerStage)->ee_emp_id == $employee_id) --}}
                        {{-- <form id="managerReviewForm">
                                <div class="form-group">
                                    <label>Manager's Remarks <span class="required">*</span></label>
                                    <textarea id="managerRemark" required placeholder="Please provide your comments..."></textarea>
                                </div>

                                <div class="button-group">
                                    <button type="button" class="btn btn-outline-primary" onclick="approveManager()">✓
                                        Approve</button>
                                    <button type="button" class="btn btn-outline-danger" onclick="rejectManager()">✗
                                        Reject</button>
                                </div>
                            </form> --}}
                        {{-- @endif --}}


                        @if ($approvalData)
                            <div class="card-body">
                                <form id="approvalForm">
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="form-label mb-0 mt-2">Message</label>
                                            <div class="col-md-12 col-lg-12">
                                                <textarea rows="2" name="message" class="form-control" id="actionMessage-603"></textarea>
                                            </div>
                                        </div>

                                        <div class="card-footer mt-3">
                                            <div class="row">
                                                <div class="col-md-12 col-lg-12 d-flex justify-content-end">
                                                    <!-- Reject Button -->

                                                    <button type="button"
                                                        data-approval_status="{{ $approvalData?->fh_approver_status?->m_id }}"
                                                        data-approval_type="0"
                                                        data-approval_action_type="{{ $approvalData->pa_type }}"
                                                        data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                        data-atd_id="{{ md5($exit->atd_id) }}"
                                                        data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                        data-master_module_id="{{ $managerReviewId }}"
                                                        data-is_last_approval="{{ $approvalData->pa_last }}"
                                                        data-emp_d_id="{{ optional($exit->fh_employee)->emp_d_id }}"
                                                        class="btn btn-outline-danger  actionBtn mx-3">
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
                                                        class="btn btn-success actionBtn">
                                                        {{ $approvalData?->fh_approver_status?->m_name }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @elseif ($canApprove)
                            @if ($exit->er_module_id == $managerReviewId)
                                <x-approval-form :moduleName="$exit?->fh_module?->m_name" :masterApproveBtn="$masterApproveBtn" :primaryId="$exit->er_id" :moduleId="603"
                                    actionUrl="{{ route('approve.fnf') }}" />
                            @endif
                        @endif

                        {{-- <div class="col-xl-12 col-md-12 col-lg-6">
                            <ul id="claimLogList" class="timeline ">
                                @foreach ($approvlLogs->sortBy('updated_at') as $item2)
                                    @php

                                        $className = get_class($item2);
                                        if ($className == 'App\Models\ApprovalLog') {
                                            $jsonData2 = $item2->fh_status->m_other;
                                            $item2DecodedData = json_decode($jsonData2, true); // true for associative array
                                            $item2Color = $item2DecodedData['color'];
                                            $item2Icon = $item2DecodedData['web_icon'];
                                            $item2StatusName = $item2->fh_status->m_name;
                                            $remark = $item2->log_description;

                                            $emp_name = $item2->fh_employee->emp_full_name;
                                            $emp_code = $item2->fh_employee->emp_code;
                                            $emp_designation = $item2->fh_employee->fh_designation->dg_name;
                                        } else {
                                            $item2StatusName =
                                                $item2->dlog_requester_action == 1 ? 'Accepted' : 'Declined';
                                            $item2Color = $item2->dlog_requester_action == 1 ? '#4CAF50' : '#F44336';
                                            $item2Icon =
                                                $item2->dlog_requester_action == 1 ? 'fa fa-check' : 'fa fa-times';
                                            $remark = $item2->dlog_remarks;

                                            $emp_name = $claimData->fh_employee->emp_full_name;
                                            $emp_code = $claimData->fh_employee->emp_code;
                                            $emp_designation = $claimData->fh_employee->fh_designation->dg_name;
                                        }
                                    @endphp
                                    <li class="{{ $loop->index % 2 == 0 ? 'primary' : 'success' }}">
                                        <a href="javascript:void(0);" class="font-weight-semibold fs-15 mb-2 ms-3">
                                            <span class="badge " style="background-color:{{ $item2Color }}">
                                                <i class="{{ $item2Icon }}">&nbsp;</i>{{ $item2StatusName }}
                                            </span></a>
                                        <a href="javascript:void(0);" class="text-muted float-end fs-12">On
                                            {{ \Carbon\Carbon::parse($item2->created_at)->format('l') }}</a><br>
                                        <span class="text-muted float-end ms-3 fs-14"> <i class="fa fa-calendar"></i>
                                            {{ \Carbon\Carbon::parse($item2->created_at)->format('d-M-Y') }}
                                            <i class=" ms-3 fa fa-clock-o"></i>
                                            {{ \Carbon\Carbon::parse($item2->created_at)->format('h:i A') }}</span>
                                        <p class="mb-0 pb-0 text-muted fs-18 pt-1 ms-3">
                                            {{ $emp_name }} &nbsp; <span class="fs-14">
                                                {{ '(' . $emp_code . ')' }}</span>
                                        </p>
                                        <span class="mb-0 pb-0 text-muted fs-14 ms-3">{{ $emp_designation }}</span><br>
                                        <span class="text-muted ms-3 fs-14">Remark : {{ $remark }}</span>
                                        <div>
                                            @if (isset($item2->fh_deductionLog->dlog_additional_info) && $item2->fh_deductionLog->dlog_additional_info)
                                                <span class="text-muted ms-3 fs-14">Deduction : </span>
                                                @foreach (json_decode($item2->fh_deductionLog->dlog_additional_info) as $key => $keyItem)
                                                    <span
                                                        class="text-muted ms-3 fs-14">{{ \App\Models\MasterTable::find($key)->m_name }}
                                                        : {{ $keyItem }}</span>
                                                @endforeach
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div> --}}
                    </div>

                    <!-- Stage 3: HR Review -->
                    <div class="stage-content" id="stage4"> 
                        <div class="stage-header">
                            <div class="stage-title">
                                <h4>Step 4: HR Review</h4>
                                <span class="status-badge status-approved">Manager Approved</span>
                            </div>
                        </div>

                        {{-- <div class="remarks-section">
                            <h3 style="margin-bottom: 15px;">Previous Remarks</h3>
                            <div class="remark-item">
                                <div class="remark-header">
                                    <span class="remark-author">Manager</span>
                                    <span class="remark-date" id="managerRemarkDate"></span>
                                </div>
                                <div class="remark-text" id="displayManagerRemark"></div>
                            </div>
                        </div> --}}

                        {{-- HR Approved/ Rejected  --}}

                        {{-- @if ($exit->er_overall_status === 'MANAGER_APPROVED' && optional($hrStage)->ee_emp_id == $employee_id)
                            <form id="hrReviewForm">
                                <div class="form-group">
                                    <label>HR Remarks <span class="required">*</span></label>
                                    <textarea id="hrRemark" required placeholder="Please provide your comments..."></textarea>
                                </div>

                                <div class="button-group">
                                    <button type="button" class="btn btn-outline-primary" onclick="approveHR()">✓
                                        Approve</button>
                                    <button type="button" class="btn btn-outline-danger" onclick="rejectHR()">✗
                                        Reject</button>
                                </div>
                            </form>
                        @endif --}}

                        @if ($approvalData)
                            <div class="card-body">
                                <form id="approvalForm">
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="form-label mb-0 mt-2">Message</label>
                                            <div class="col-md-12 col-lg-12">
                                                <textarea rows="2" name="message" class="form-control" id="actionMessage-604"></textarea>
                                            </div>
                                        </div>

                                        <div class="card-footer mt-3">
                                            <div class="row">
                                                <div class="col-md-12 col-lg-12 d-flex justify-content-end">
                                                    <!-- Reject Button -->

                                                    <button type="button"
                                                        data-approval_status="{{ $approvalData?->fh_approver_status?->m_id }}"
                                                        data-approval_type="0"
                                                        data-approval_action_type="{{ $approvalData->pa_type }}"
                                                        data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                        data-atd_id="{{ md5($exit->atd_id) }}"
                                                        data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                        data-master_module_id="{{ $hrReviewId }}"
                                                        data-is_last_approval="{{ $approvalData->pa_last }}"
                                                        data-emp_d_id="{{ optional($exit->fh_employee)->emp_d_id }}"
                                                        class="btn btn-outline-danger  actionBtn mx-3">
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
                                                        data-master_module_id="{{ $hrReviewId }}"
                                                        data-is_last_approval="{{ $approvalData->pa_last }}"
                                                        data-emp_d_id="{{ optional($exit->fh_employee)->emp_d_id }}"
                                                        class="btn btn-success actionBtn">
                                                        {{ $approvalData?->fh_approver_status?->m_name }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @elseif ($canApprove)
                            @if ($exit->er_module_id == $hrReviewId)
                                <x-approval-form :moduleName="$exit?->fh_module?->m_name" :masterApproveBtn="$masterApproveBtn" :primaryId="$exit->er_id" :moduleId="604"
                                    actionUrl="{{ route('approve.fnf') }}" />
                            @endif
                        @endif

                        {{-- <div class="col-xl-12 col-md-12 col-lg-6">
                            <ul id="claimLogList" class="timeline ">
                                @foreach ($approvlLogs3->sortBy('updated_at') as $item2)
                                    @php

                                        $className = get_class($item2);
                                        if ($className == 'App\Models\ApprovalLog') {
                                            $jsonData2 = $item2->fh_status->m_other;
                                            $item2DecodedData = json_decode($jsonData2, true); // true for associative array
                                            $item2Color = $item2DecodedData['color'];
                                            $item2Icon = $item2DecodedData['web_icon'];
                                            $item2StatusName = $item2->fh_status->m_name;
                                            $remark = $item2->log_description;

                                            $emp_name = $item2->fh_employee->emp_full_name;
                                            $emp_code = $item2->fh_employee->emp_code;
                                            $emp_designation = $item2->fh_employee->fh_designation->dg_name;
                                        } else {
                                            $item2StatusName =
                                                $item2->dlog_requester_action == 1 ? 'Accepted' : 'Declined';
                                            $item2Color = $item2->dlog_requester_action == 1 ? '#4CAF50' : '#F44336';
                                            $item2Icon =
                                                $item2->dlog_requester_action == 1 ? 'fa fa-check' : 'fa fa-times';
                                            $remark = $item2->dlog_remarks;

                                            $emp_name = $claimData->fh_employee->emp_full_name;
                                            $emp_code = $claimData->fh_employee->emp_code;
                                            $emp_designation = $claimData->fh_employee->fh_designation->dg_name;
                                        }
                                    @endphp
                                    <li class="{{ $loop->index % 2 == 0 ? 'primary' : 'success' }}">
                                        <a href="javascript:void(0);" class="font-weight-semibold fs-15 mb-2 ms-3">
                                            <span class="badge " style="background-color:{{ $item2Color }}">
                                                <i class="{{ $item2Icon }}">&nbsp;</i>{{ $item2StatusName }}
                                            </span></a>
                                        <a href="javascript:void(0);" class="text-muted float-end fs-12">On
                                            {{ \Carbon\Carbon::parse($item2->created_at)->format('l') }}</a><br>
                                        <span class="text-muted float-end ms-3 fs-14"> <i class="fa fa-calendar"></i>
                                            {{ \Carbon\Carbon::parse($item2->created_at)->format('d-M-Y') }}
                                            <i class=" ms-3 fa fa-clock-o"></i>
                                            {{ \Carbon\Carbon::parse($item2->created_at)->format('h:i A') }}</span>
                                        <p class="mb-0 pb-0 text-muted fs-18 pt-1 ms-3">
                                            {{ $emp_name }} &nbsp; <span class="fs-14">
                                                {{ '(' . $emp_code . ')' }}</span>
                                        </p>
                                        <span class="mb-0 pb-0 text-muted fs-14 ms-3">{{ $emp_designation }}</span><br>
                                        <span class="text-muted ms-3 fs-14">Remark : {{ $remark }}</span>
                                        <div>
                                            @if (isset($item2->fh_deductionLog->dlog_additional_info) && $item2->fh_deductionLog->dlog_additional_info)
                                                <span class="text-muted ms-3 fs-14">Deduction : </span>
                                                @foreach (json_decode($item2->fh_deductionLog->dlog_additional_info) as $key => $keyItem)
                                                    <span
                                                        class="text-muted ms-3 fs-14">{{ \App\Models\MasterTable::find($key)->m_name }}
                                                        : {{ $keyItem }}</span>
                                                @endforeach
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div> --}}

                    </div>

                    <!-- Stage 4: Finance / Clearance -->
                    <div class="stage-content" id="stage3">
                        <div class="stage-header">
                            <div class="stage-title">
                                <h4>Step 3: Finance & Clearance</h4>
                                <span class="status-badge status-clearance">In Clearance</span>
                            </div>
                        </div>


                        {{-- Finance  Approved/ Rejected  --}}

                        {{-- @if ($exit->er_overall_status === 'HR_APPROVED' && optional($adminStage)->ee_emp_id == $employee_id)
                            <form id="clearanceForm">
                                <div class="form-group">
                                    <label>Finance Remarks <span class="required">*</span></label>
                                    <textarea id="financeRemark" required placeholder="Please provide clearance notes..."></textarea>
                                </div>

                                <div class="button-group">

                                    <button type="button"
                                        onclick="window.location='{{ route('exit.generate.pdf', $exit->er_id) }}'"
                                        class="btn btn-outline-primary">
                                        Generate Documents
                                    </button>

                                    <button type="submit" class="btn btn-outline-primary">✓ Mark as Cleared</button>
                                </div>
                            </form>
                        @endif --}}

                        @if ($approvalData)
                            <div class="card-body">
                                <form id="approvalForm">
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="form-label mb-0 mt-2">Message</label>
                                            <div class="col-md-12 col-lg-12">
                                                <textarea rows="2" name="message" class="form-control" id="actionMessage-605"></textarea>
                                            </div>
                                        </div>

                                        <div class="card-footer mt-3">
                                            <div class="row">
                                                <div class="col-md-12 col-lg-12 d-flex justify-content-end">
                                                    <!-- Reject Button -->

                                                    <button type="button"
                                                        data-approval_status="{{ $approvalData?->fh_approver_status?->m_id }}"
                                                        data-approval_type="0"
                                                        data-approval_action_type="{{ $approvalData->pa_type }}"
                                                        data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                        data-atd_id="{{ md5($exit->atd_id) }}"
                                                        data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                        data-master_module_id="{{ $financeReviewId }}"
                                                        data-is_last_approval="{{ $approvalData->pa_last }}"
                                                        data-emp_d_id="{{ optional($exit->fh_employee)->emp_d_id }}"
                                                        class="btn btn-outline-danger  actionBtn mx-3">
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
                                                        data-master_module_id="{{ $financeReviewId }}"
                                                        data-is_last_approval="{{ $approvalData->pa_last }}"
                                                        data-emp_d_id="{{ optional($exit->fh_employee)->emp_d_id }}"
                                                        class="btn btn-success actionBtn">
                                                        {{ $approvalData?->fh_approver_status?->m_name }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @elseif ($canApprove)
                            @if ($exit->er_module_id == $financeReviewId)
                                <x-approval-form :moduleName="$exit?->fh_module?->m_name" :masterApproveBtn="$masterApproveBtn" :primaryId="$exit->er_id" :moduleId="605"
                                    actionUrl="{{ route('approve.fnf') }}" />
                            @endif
                        @endif

                        {{-- <div class="col-xl-12 col-md-12 col-lg-6">
                            <ul id="claimLogList" class="timeline ">
                                @foreach ($approvlLogs3->sortBy('updated_at') as $item2)
                                    @php

                                        $className = get_class($item2);
                                        if ($className == 'App\Models\ApprovalLog') {
                                            $jsonData2 = $item2->fh_status->m_other;
                                            $item2DecodedData = json_decode($jsonData2, true); // true for associative array
                                            $item2Color = $item2DecodedData['color'];
                                            $item2Icon = $item2DecodedData['web_icon'];
                                            $item2StatusName = $item2->fh_status->m_name;
                                            $remark = $item2->log_description;

                                            $emp_name = $item2->fh_employee->emp_full_name;
                                            $emp_code = $item2->fh_employee->emp_code;
                                            $emp_designation = $item2->fh_employee->fh_designation->dg_name;
                                        } else {
                                            $item2StatusName =
                                                $item2->dlog_requester_action == 1 ? 'Accepted' : 'Declined';
                                            $item2Color = $item2->dlog_requester_action == 1 ? '#4CAF50' : '#F44336';
                                            $item2Icon =
                                                $item2->dlog_requester_action == 1 ? 'fa fa-check' : 'fa fa-times';
                                            $remark = $item2->dlog_remarks;

                                            $emp_name = $claimData->fh_employee->emp_full_name;
                                            $emp_code = $claimData->fh_employee->emp_code;
                                            $emp_designation = $claimData->fh_employee->fh_designation->dg_name;
                                        }
                                    @endphp
                                    <li class="{{ $loop->index % 2 == 0 ? 'primary' : 'success' }}">
                                        <a href="javascript:void(0);" class="font-weight-semibold fs-15 mb-2 ms-3">
                                            <span class="badge " style="background-color:{{ $item2Color }}">
                                                <i class="{{ $item2Icon }}">&nbsp;</i>{{ $item2StatusName }}
                                            </span></a>
                                        <a href="javascript:void(0);" class="text-muted float-end fs-12">On
                                            {{ \Carbon\Carbon::parse($item2->created_at)->format('l') }}</a><br>
                                        <span class="text-muted float-end ms-3 fs-14"> <i class="fa fa-calendar"></i>
                                            {{ \Carbon\Carbon::parse($item2->created_at)->format('d-M-Y') }}
                                            <i class=" ms-3 fa fa-clock-o"></i>
                                            {{ \Carbon\Carbon::parse($item2->created_at)->format('h:i A') }}</span>
                                        <p class="mb-0 pb-0 text-muted fs-18 pt-1 ms-3">
                                            {{ $emp_name }} &nbsp; <span class="fs-14">
                                                {{ '(' . $emp_code . ')' }}</span>
                                        </p>
                                        <span class="mb-0 pb-0 text-muted fs-14 ms-3">{{ $emp_designation }}</span><br>
                                        <span class="text-muted ms-3 fs-14">Remark : {{ $remark }}</span>
                                        <div>
                                            @if (isset($item2->fh_deductionLog->dlog_additional_info) && $item2->fh_deductionLog->dlog_additional_info)
                                                <span class="text-muted ms-3 fs-14">Deduction : </span>
                                                @foreach (json_decode($item2->fh_deductionLog->dlog_additional_info) as $key => $keyItem)
                                                    <span
                                                        class="text-muted ms-3 fs-14">{{ \App\Models\MasterTable::find($key)->m_name }}
                                                        : {{ $keyItem }}</span>
                                                @endforeach
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div> --}}

                    </div>

                    <!-- Stage 5: Documentation -->
                    <div class="stage-content" id="stage5">
                        <div class="stage-header">
                            <div class="stage-title">
                                <h4>Step 5: Documentation</h4>
                                <span class="status-badge status-documentation">Documentation</span>
                            </div>
                        </div>
                      

                        {{-- Admin  Approved/ Rejected  --}}

                        {{-- @if ($exit->er_overall_status === 'CLEARANCE_IN_PROGRESS' && optional($financeStage)->ee_emp_id == $employee_id)
                            <form id="documentationForm">
                                <div class="form-group">
                                    <label>Documentation Notes</label>
                                    <textarea id="docNotes" placeholder="Any additional notes regarding documentation..."></textarea>
                                </div>

                                <div class="button-group">
                                    <button type="submit" class="btn btn-outline-primary">Complete Documentation
                                        →</button>
                                </div>
                            </form>
                        @endif --}}


                        @if ($approvalData)
                            <div class="card-body">
                                <form id="approvalForm">
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="form-label mb-0 mt-2">Message</label>
                                            <div class="col-md-12 col-lg-12">
                                                <textarea rows="2" name="message" class="form-control" id="actionMessage-606"></textarea>
                                            </div>
                                        </div>

                                        <div class="card-footer mt-3">
                                            <div class="row">
                                                <div class="col-md-12 col-lg-12 d-flex justify-content-end">
                                                    <!-- Reject Button -->

                                                    <button type="button"
                                                        data-approval_status="{{ $approvalData?->fh_approver_status?->m_id }}"
                                                        data-approval_type="0"
                                                        data-approval_action_type="{{ $approvalData->pa_type }}"
                                                        data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                        data-atd_id="{{ md5($exit->atd_id) }}"
                                                        data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                        data-master_module_id="{{ $adminReviewId }}"
                                                        data-is_last_approval="{{ $approvalData->pa_last }}"
                                                        data-emp_d_id="{{ optional($exit->fh_employee)->emp_d_id }}"
                                                        class="btn btn-outline-danger  actionBtn mx-3">
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
                                                        data-master_module_id="{{ $adminReviewId }}"
                                                        data-is_last_approval="{{ $approvalData->pa_last }}"
                                                        data-emp_d_id="{{ optional($exit->fh_employee)->emp_d_id }}"
                                                        class="btn btn-success actionBtn">
                                                        {{ $approvalData?->fh_approver_status?->m_name }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @elseif ($canApprove)
                            @if ($exit->er_module_id == $adminReviewId)
                                <x-approval-form :moduleName="$exit?->fh_module?->m_name" :masterApproveBtn="$masterApproveBtn" :primaryId="$exit->er_id" :moduleId="$exit->er_module_id"
                                    actionUrl="{{ route('approve.fnf') }}" />
                            @endif
                        @endif

                        {{-- <div class="col-xl-12 col-md-12 col-lg-6">
                            <ul id="claimLogList" class="timeline ">
                                @foreach ($approvlLogs3->sortBy('updated_at') as $item2)
                                    @php

                                        $className = get_class($item2);
                                        if ($className == 'App\Models\ApprovalLog') {
                                            $jsonData2 = $item2->fh_status->m_other;
                                            $item2DecodedData = json_decode($jsonData2, true); // true for associative array
                                            $item2Color = $item2DecodedData['color'];
                                            $item2Icon = $item2DecodedData['web_icon'];
                                            $item2StatusName = $item2->fh_status->m_name;
                                            $remark = $item2->log_description;

                                            $emp_name = $item2->fh_employee->emp_full_name;
                                            $emp_code = $item2->fh_employee->emp_code;
                                            $emp_designation = $item2->fh_employee->fh_designation->dg_name;
                                        } else {
                                            $item2StatusName =
                                                $item2->dlog_requester_action == 1 ? 'Accepted' : 'Declined';
                                            $item2Color = $item2->dlog_requester_action == 1 ? '#4CAF50' : '#F44336';
                                            $item2Icon =
                                                $item2->dlog_requester_action == 1 ? 'fa fa-check' : 'fa fa-times';
                                            $remark = $item2->dlog_remarks;

                                            $emp_name = $claimData->fh_employee->emp_full_name;
                                            $emp_code = $claimData->fh_employee->emp_code;
                                            $emp_designation = $claimData->fh_employee->fh_designation->dg_name;
                                        }
                                    @endphp
                                    <li class="{{ $loop->index % 2 == 0 ? 'primary' : 'success' }}">
                                        <a href="javascript:void(0);" class="font-weight-semibold fs-15 mb-2 ms-3">
                                            <span class="badge " style="background-color:{{ $item2Color }}">
                                                <i class="{{ $item2Icon }}">&nbsp;</i>{{ $item2StatusName }}
                                            </span></a>
                                        <a href="javascript:void(0);" class="text-muted float-end fs-12">On
                                            {{ \Carbon\Carbon::parse($item2->created_at)->format('l') }}</a><br>
                                        <span class="text-muted float-end ms-3 fs-14"> <i class="fa fa-calendar"></i>
                                            {{ \Carbon\Carbon::parse($item2->created_at)->format('d-M-Y') }}
                                            <i class=" ms-3 fa fa-clock-o"></i>
                                            {{ \Carbon\Carbon::parse($item2->created_at)->format('h:i A') }}</span>
                                        <p class="mb-0 pb-0 text-muted fs-18 pt-1 ms-3">
                                            {{ $emp_name }} &nbsp; <span class="fs-14">
                                                {{ '(' . $emp_code . ')' }}</span>
                                        </p>
                                        <span class="mb-0 pb-0 text-muted fs-14 ms-3">{{ $emp_designation }}</span><br>
                                        <span class="text-muted ms-3 fs-14">Remark : {{ $remark }}</span>
                                        <div>
                                            @if (isset($item2->fh_deductionLog->dlog_additional_info) && $item2->fh_deductionLog->dlog_additional_info)
                                                <span class="text-muted ms-3 fs-14">Deduction : </span>
                                                @foreach (json_decode($item2->fh_deductionLog->dlog_additional_info) as $key => $keyItem)
                                                    <span
                                                        class="text-muted ms-3 fs-14">{{ \App\Models\MasterTable::find($key)->m_name }}
                                                        : {{ $keyItem }}</span>
                                                @endforeach
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div> --}}

                    </div>

                    <!-- Stage 6: Relieved & Closed -->
                    <div class="stage-content" id="stage6">
                     

                        {{-- <div class="remarks-section">
                            <h3 style="margin-bottom: 15px;">Process Timeline</h3>
                            <div class="remark-item">
                                <div class="remark-header">
                                    <span class="remark-author">Manager Review</span>
                                    <span class="remark-date" id="timeline_manager"></span>
                                </div>
                                <div class="remark-text" id="timeline_managerRemark"></div>
                            </div>
                            <div class="remark-item">
                                <div class="remark-header">
                                    <span class="remark-author">HR Review</span>
                                    <span class="remark-date" id="timeline_hr"></span>
                                </div>
                                <div class="remark-text" id="timeline_hrRemark"></div>
                            </div>
                            <div class="remark-item">
                                <div class="remark-header">
                                    <span class="remark-author">Finance Clearance</span>
                                    <span class="remark-date" id="timeline_finance"></span>
                                </div>
                                <div class="remark-text" id="timeline_financeRemark"></div>
                            </div>
                        </div> --}}
                    </div>
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
                    <div class="card-header ">
                        <h5 class="mb-0">Documents</h5>
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
                            {{-- <li class="list-group-item list-group-item-action px-4 py-3">
                                <a href="{{ route('documents.generate', [Crypt::encrypt($exit->er_id), 'full_final']) }}"
                                    target="_blank" class="text-decoration-none d-flex align-items-center text-primary">
                                    <i class="las la-file-pdf me-3 text-danger la-2x"></i>
                                    Full & Final Settlement
                                </a>
                            </li> --}}

                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection
@section('script')
    <script>
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

        // 🔹 Map status to stage
        function getStageFromStatus(status) {
            switch (status) {
                case 'RESIGNATION_SUBMITTED':
                    return 2; // Manager Stage
                case 'MANAGER_APPROVED':
                    return 3; // HR Stage
                case 'MANAGER_REJECTED':
                    return 2; // Stop at Manager
                case 'HR_APPROVED':
                    return 4; // Finance / Clearance
                case 'HR_REJECTED':
                    return 3; // Stop at HR
                case 'CLEARANCE_IN_PROGRESS':
                    return 5;
                case 'DOCUMENTS_AND_RELIEVING':
                    return 6;
                case 'RELIEVED':
                    return 6;
                default:
                    return 2;
            }
        }

        // 🔹 INIT
        document.addEventListener('DOMContentLoaded', function() {
            initFileUploads();
            const startStage = getStageFromStatus(exitData.status);
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

            // Stop further stages if Manager or HR rejected
            if ((exitData.status === 'MANAGER_REJECTED' && stage === 2) ||
                (exitData.status === 'HR_REJECTED' && stage === 3)) {
                // Disable further action buttons
                stageEl.querySelectorAll('button, textarea, input').forEach(el => el.disabled = true);
                showAlert('Process stopped due to rejection.', 'warning');
                return;
            }

            if (stage === 3) populateStage3();
            if (stage === 6) populateStage6();

            currentStage = stage;
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function updateProgress(stage) {
            const steps = document.querySelectorAll('.step');
            const fill = document.getElementById('progressFill');

            steps.forEach((step, index) => {
                const num = index + 1;
                step.classList.remove('active', 'completed');
                if (num < stage) step.classList.add('completed');
                if (num === stage) step.classList.add('active');
            });

            fill.style.width = ((stage - 1) / 5) * 100 + '%';
        }

        // 🔹 POPULATE DATA
        function populateStage3() {
            document.getElementById('displayManagerRemark').textContent = processData.manager.remark || '—';
            document.getElementById('managerRemarkDate').textContent = processData.manager.date || '';
        }

        function populateStage6() {
            document.getElementById('final_empName').textContent = processData.employee.name;
            document.getElementById('final_empId').textContent = processData.employee.id;
            document.getElementById('final_lastWorkingDay').textContent = processData.employee.lastWorkingDay;

            document.getElementById('timeline_manager').textContent = processData.manager.date || '';
            document.getElementById('timeline_managerRemark').textContent = processData.manager.remark || '';
            document.getElementById('timeline_hr').textContent = processData.hr.date || '';
            document.getElementById('timeline_hrRemark').textContent = processData.hr.remark || '';
            document.getElementById('timeline_finance').textContent = processData.finance.date || '';
            document.getElementById('timeline_financeRemark').textContent = processData.finance.remark || '';
        }

        // 🔹 ALERT
        function showAlert(msg, type = 'success') {
            const box = document.getElementById('alertBox');
            box.className = `alert alert-${type} show`;
            box.textContent = msg;
            setTimeout(() => box.classList.remove('show'), 4000);
        }

        // Manager Approve
        function approveManager() {
            submitManagerReview('approve');
        }

        // Manager Reject
        function rejectManager() {
            submitManagerReview('reject');
        }

        // 🔹 APPROVE / REJECT HANDLING (Manager)
        function submitManagerReview(action) {
            const remark = document.getElementById('managerRemark').value.trim();
            if (!remark) return Swal.fire({
                icon: 'warning',
                title: 'Oops!',
                text: 'Please enter remarks.'
            });

            const exitId = @json($exit->er_id);

            fetch(`/admin/employee-exit/exit/${exitId}/manager/${action}`, {
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
                            text: `Manager ${action} successfully.`,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        const badge = document.querySelector('#stage2 .status-badge');
                        badge.textContent = action === 'approve' ? 'Approved' : 'Rejected';
                        badge.className =
                            `status-badge ${action === 'approve' ? 'status-approved' : 'status-rejected'}`;

                        document.querySelector('#stage2 .button-group').style.display = 'none';

                        // Move to next stage only if approved
                        if (action === 'approve') moveToStage(3);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Something went wrong!'
                        });
                    }
                });
        }

        // HR approved 
        function approveHR() {
            submitHRReview('approve');
        }

        // HR Rejected 
        function rejectHR() {
            submitHRReview('reject');
        }

        // 🔹 APPROVE / REJECT HANDLING (HR)
        function submitHRReview(action) {
            const remark = document.getElementById('hrRemark').value.trim();
            if (!remark) return Swal.fire({
                icon: 'warning',
                title: 'Oops!',
                text: 'Please enter remarks.'
            });

            const exitId = @json($exit->er_id);

            fetch(`/admin/employee-exit/exit/${exitId}/hr/${action}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
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
                            text: `HR ${action} successfully.`,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        const badge = document.querySelector('#stage3 .status-badge');
                        badge.textContent = action === 'approve' ? 'Approved' : 'Rejected';
                        badge.className =
                            `status-badge ${action === 'approve' ? 'status-approved' : 'status-rejected'}`;

                        document.querySelector('#stage3 .button-group').style.display = 'none';

                        // Move to next stage only if approved
                        if (action === 'approve') moveToStage(4);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Something went wrong!'
                        });
                    }
                });
        }


        function submitFinanceClearance() {
            const remark = document.getElementById('financeRemark').value.trim();
            if (!remark) {
                return Swal.fire({
                    icon: 'warning',
                    title: 'Oops!',
                    text: 'Please enter finance remarks before submitting.'
                });
            }

            const exitId = @json($exit->er_id);
            fetch(`/admin/employee-exit/exit/${exitId}/finance/clearance`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        remark
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success == true || data.success == 1 || data.success === 'true') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Update badge
                        const badge = document.querySelector('#stage4 .status-badge');
                        badge.textContent = 'Cleared';
                        badge.className = 'status-badge status-approved';

                        // Disable button and textarea
                        document.querySelector('#clearanceForm .button-group').style.display = 'none';
                        document.getElementById('financeRemark').disabled = true;

                        // Move to next stage
                        moveToStage(5); // Documentation / Relieve stage
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Something went wrong!'
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed!',
                        text: 'Could not submit. Please try again.'
                    });
                });
        }

        document.getElementById('clearanceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitFinanceClearance();
        });

        document.getElementById('documentationForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const remark = document.getElementById('docNotes').value.trim();
            if (!remark) {
                return Swal.fire({
                    icon: 'warning',
                    title: 'Oops!',
                    text: 'Please enter documentation remarks before submitting.'
                });
            }

            const exitId = @json($exit->er_id);
            fetch(`/admin/employee-exit/exit/${exitId}/documentation`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        docNotes: remark

                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success == true || data.success == 1 || data.success === 'true') {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Update badge
                        const badge = document.querySelector('#stage5 .status-badge');
                        badge.textContent = 'Completed';
                        badge.className = 'status-badge status-approved';

                        // Disable form
                        document.querySelectorAll('#documentationForm textarea, #documentationForm button')
                            .forEach(el => el.disabled = true);

                        moveToStage(6);

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Something went wrong!'
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed!',
                        text: 'Could not submit. Please try again.'
                    });
                });
        });

        function financeClearance() {
            const id = exitData.id;
            const remark = document.getElementById('financeRemark').value;

            fetch(`/admin/employee-exit/exit/${id}/finance/clearance`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        remark
                    })
                })
                .then(handleResponse)
                .then(() => moveToStage(5)) // move to Documentation / Relieve stage
                .catch(handleError);
        }

        function relieveEmployee() {
            const id = exitData.id;

            fetch(`/admin/employee-exit/exit/${id}/relieve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                })
                .then(handleResponse)
                .then(() => showAlert('Employee Relieved Successfully!', 'success'))
                .catch(handleError);
        }

        function handleResponse(res) {
            if (!res.ok) return res.text().then(text => {
                throw new Error(text)
            });
            return res.json().then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                } else {
                    throw new Error(data.message || 'Something went wrong');
                }
            });
        }

        function handleError(err) {
            console.error(err);
            showAlert('Error: ' + err.message, 'danger');
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
    <script src="{{ asset('assets/js/approval-form.js') }}"></script>

@endsection
