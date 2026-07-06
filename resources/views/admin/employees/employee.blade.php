<?php

use ChandraHemant\HtkcUtils\CommonUtils;
use App\Helpers\RolePermissionLogics;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Grade;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

$user = Auth::user();
$branchFilter = CommonUtils::getCustomModelData(new Branch(), [['method' => 'where', 'args' => ['br_b_id', $user->emp_b_id]]]);
$departmentFilter = CommonUtils::getCustomModelData(new Department(), [['method' => 'where', 'args' => ['d_b_id', $user->emp_b_id]]]);
$designationFilter = CommonUtils::getCustomModelData(new Designation(), [['method' => 'where', 'args' => ['dg_b_id', $user->emp_b_id]]]);
$gradeFilter = CommonUtils::getCustomModelData(new Grade(), [['method' => 'where', 'args' => ['g_b_id', $user->emp_b_id]]]);

$permission = new RolePermissionLogics();
$employees = Employee::where('emp_b_id', $user->emp_b_id)
    ->where('emp_role_id', '!=', 1)
    ->get();

$personalDetails = [
    ['label' => 'Date of Birth', 'class' => 'vm-empDob', 'icon' => 'bi-calendar3'],
    ['label' => 'Marital Status', 'class' => 'vm-empMaritalStatus', 'icon' => 'bi-heart'],
    ['label' => 'Personal Mobile', 'class' => 'vm-empPersonalMobile', 'icon' => 'bi-phone'],
    ['label' => 'Personal Email', 'class' => 'vm-empPersonalEmail', 'icon' => 'bi-envelope'],
    ['label' => 'Official Mobile', 'class' => 'vm-empOfficialMobile', 'icon' => 'bi-telephone'],
    ['label' => 'Official Email', 'class' => 'vm-empOfficialEmail', 'icon' => 'bi-envelope-open'],
    ['label' => 'Emergency Contact Number', 'class' => 'vm-empEmergencyContactNumber', 'icon' => 'bi-phone'],
    ['label' => 'Emergency Contact Relation', 'class' => 'vm-empEmergencyRelation', 'icon' => 'bi-person-square'],
    ['label' => 'Gender', 'class' => 'vm-empGender', 'icon' => 'bi-gender-ambiguous'],
    ['label' => 'Nationality', 'class' => 'vm-empNationality', 'icon' => 'bi-flag'],
    ['label' => 'Religion', 'class' => 'vm-empReligion', 'icon' => 'bi-peace'],
    ['label' => 'Cast', 'class' => 'vm-empCast', 'icon' => 'bi-person-badge'],
    ['label' => 'Blood Group', 'class' => 'vm-empBloodGroup', 'icon' => 'bi-droplet'],
    ['label' => 'Identification', 'class' => 'vm-empIdentification', 'icon' => 'bi-fingerprint'],
    ['label' => 'Permanent Address', 'class' => 'vm-empPermanentAddress', 'icon' => 'bi-geo-alt'],
    ['label' => 'Temporary Address', 'class' => 'vm-empTemporaryAddress', 'icon' => 'bi-map'],
    ['label' => 'Permanent Pin Code', 'class' => 'vm-empPerPinCode', 'icon' => 'bi-pin-map'],
    ['label' => 'Temporary Pin Code', 'class' => 'vm-empTempPinCode', 'icon' => 'bi-pin-map'],
];

$workDetails = [
    ['label' => 'Branch', 'class' => 'vm-empBranch', 'icon' => 'bi-diagram-3'],
    ['label' => 'Department', 'class' => 'vm-empDepartment', 'icon' => 'bi-building'],
    ['label' => 'Designation', 'class' => 'vm-empDesignation', 'icon' => 'bi-person-badge'],
    ['label' => 'Grade', 'class' => 'vm-empGrade', 'icon' => 'bi-bar-chart'],
    ['label' => 'Role', 'class' => 'vm-empRole', 'icon' => 'bi-person-gear'],
    ['label' => 'Reporting Manager', 'class' => 'vm-empReportingManager', 'icon' => 'bi-person-lines-fill'],
    ['label' => 'Budget Code (SAP)', 'class' => 'vm-empBudgetCode', 'icon' => 'bi-receipt'],
    ['label' => 'Profit Center', 'class' => 'vm-empProfitCenter', 'icon' => 'bi-cash-stack'],
    ['label' => 'Assigned Region', 'class' => 'vm-empAssignedRegion', 'icon' => 'bi-geo-alt'],
    ['label' => 'Assigned Project', 'class' => 'vm-empAssignedProject', 'icon' => 'bi-kanban'],
];

$policyDetails = [
    ['label' => 'Attendance Policy', 'class' => 'vm-empAttendancePolicy', 'icon' => 'bi-building'],
    ['label' => 'Shift Policy', 'class' => 'vm-empShiftPolicy', 'icon' => 'bi-clock'],
    ['label' => 'Check In Method', 'class' => 'vm-empCheckInMethod', 'icon' => 'bi-box-arrow-in-right'],
    ['label' => 'Work Mode', 'class' => 'vm-empWorkMode', 'icon' => 'bi-laptop'],
    ['label' => 'Geo Fencing', 'class' => 'vm-empGeoFencing', 'icon' => 'bi-pin-map'],
    ['label' => 'Geo Work', 'class' => 'vm-empGeoWork', 'icon' => 'bi-pin-map'],
    ['label' => 'Weekly Policy', 'class' => 'vm-empWeeklyPolicy', 'icon' => 'bi-calendar-week'],
    ['label' => 'Offline Sync', 'class' => 'vm-empOfflineSync', 'icon' => 'bi-wifi-off'],
    ['label' => 'Leave Policy', 'class' => 'vm-empLeavePolicy', 'icon' => 'bi-calendar-x'],
    ['label' => 'Leave Credit on Pro Rata', 'class' => 'vm-empLeaveCreditProRata', 'icon' => 'bi-percent'],
    ['label' => 'Joining Leave Calculation', 'class' => 'vm-empJoiningLeaveCalculation', 'icon' => 'bi-calculator'],
    ['label' => 'Probation Leave on Pro Rata', 'class' => 'vm-empProbationLeaveProRata', 'icon' => 'bi-percent'],
    ['label' => 'Leave Applicable Date', 'class' => 'vm-empLeaveApplicableDate', 'icon' => 'bi-calendar-date'],
];

$miscellaneousDetails = [
    ['label' => 'TADA Policy', 'class' => 'vm-empTADAPolicy', 'icon' => 'bi-receipt'],
    ['label' => 'Allowed Late Comings', 'class' => 'vm-empAllowedLateComings', 'icon' => 'bi-box-arrow-in-right'],
    ['label' => 'Allowed Early Goings', 'class' => 'vm-empAllowedEarlyGoings', 'icon' => 'bi-box-arrow-right'],
    ['label' => 'Allowed Gate Passes', 'class' => 'vm-empAllowedGatePasses', 'icon' => 'bi-door-open'],
    ['label' => 'Allowed MSP', 'class' => 'vm-empAllowedMSP', 'icon' => 'bi-person-workspace'],
    ['label' => 'PF Enabled', 'class' => 'vm-empPFEnabled', 'icon' => 'bi-person-check'],
];

?>
@extends('admin.layout.master')
@section('title')
Employee
@endsection
@section('css')
<style>
    .image-preview-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        align-items: flex-start;
    }

    .image-preview {
        position: relative;
        flex: 0 1 calc(25% - 8px);
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 6px;
        overflow: hidden;
        aspect-ratio: 1 / 1;
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Tooltip on hover */
    .image-preview .tooltip {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.7);
        color: #fff;
        font-size: 12px;
        padding: 4px 6px;
        text-align: center;
        opacity: 0;
        transition: opacity 0.3s;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .image-preview:hover .tooltip {
        opacity: 1;
    }
</style>
<style>
    .emp-id-exists {
        border-color: red;
        color: red;
    }

    .message-exists {
        color: red;
    }

    /* #btnXyz:hover {
                    color: #fff
                } */

    table td {
        padding: 0;
    }

    /* #employee-table-dynamic tbody tr:hover {
                    background-color: rgb(236, 236, 236);
                    transition: background-color 0.2s ease-in-out;
                    cursor: pointer;
                }  */


    .half-colored-icon {
        font-size: 40px;
        /* adjust size as needed */
        background: linear-gradient(to right, green 50%, red 50%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>

<style>
    td {
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Shared modal styling for both modals */
    .modal.uploadModal {
        display: none;
        position: fixed;
        inset: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
    }

    .modal-content.uploadModal {
        background-color: #fff;
        border-radius: 16px;
        width: 90%;
        max-width: 500px;
        padding: 0;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        animation: modalFadeIn 0.3s ease-out;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Modal Tabs */
    .tab-container {
        margin: 0;
        padding: 24px;
    }

    .tab-buttons {
        display: flex;
        margin-bottom: 20px;
        background-color: #f5f7fa;
        border-radius: 10px;
        padding: 4px;
    }

    .tab-button {
        flex: 1;
        padding: 12px 20px;
        cursor: pointer;
        text-align: center;
        font-weight: 500;
        color: #596780;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .tab-button.active {
        background-color: white;
        color: #4361ee;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .cam-tab-content {
        display: none;
        padding: 20px 0 5px;
    }

    .cam-tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .camera-container,
    .preview-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
    }

    #video {
        max-width: 100%;
        border-radius: 14px;
        background-color: #f0f2f5;
        height: 260px;
        object-fit: cover;
        box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    #gallery-preview,
    #camera-preview {
        max-width: 100%;
        max-height: 300px;
        border-radius: 14px;
        object-fit: contain;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .button-row {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 10px;
        width: 100%;
    }

    .upload-icon {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 180px;
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        margin-bottom: 20px;
        cursor: pointer;
        background-color: #f9fafb;
        transition: all 0.2s ease;
    }

    .upload-icon:hover {
        border-color: #4361ee;
        background-color: rgba(67, 97, 238, 0.03);
    }

    .upload-icon svg {
        width: 48px;
        height: 48px;
        color: #9ca3af;
        margin-bottom: 12px;
    }

    .upload-icon p {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
        font-weight: 500;
    }

    #profileInput {
        display: none;
    }

    /* Footer */
    .modal-footer {
        border-top: 1px solid #f0f0f0;
        padding: 16px 24px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background-color: #fcfcfc;
    }

    /* Crop Modal */
    .cropModal {
        display: none;
        justify-content: center;
        align-items: center;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 1100;
    }

    .cropModal>div {
        background: white;
        padding: 20px;
        max-width: 400px;
        width: 100%;
        border-radius: 10px;
    }

    .cropModal img {
        max-width: 100%;
        border-radius: 10px;
    }

    .is-invalid {
        border: 1px solid red !important;
    }
</style>

<style>
    :root {
        --primary: #4A90D9;
        --primary-dark: #2F74C0;
        --primary-light: #EBF4FF;
        --sidebar-bg: #F0F6FF;
        --card-shadow: 0 4px 24px rgba(74, 144, 217, .12);
        --border-color: #E0ECF8;
        --text-muted: #7A90A8;
        --badge-active: #22C55E;
    }

    /* ── Profile Header ─────────────────────────── */
    .profile-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 16px;
        padding: 28px 32px;
        color: #fff;
        box-shadow: var(--card-shadow);
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: -40px;
        right: -40px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .07);
    }

    .profile-header::after {
        content: '';
        position: absolute;
        bottom: -60px;
        right: 120px;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .05);
    }

    .avatar-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .25);
        border: 3px solid rgba(255, 255, 255, .5);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: #fff;
        overflow: hidden;
        flex-shrink: 0;
    }

    .avatar-wrapper div {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
    }

    .employee-name {
        font-size: 1.65rem;
        font-weight: 800;
        margin: 0;
    }

    .employee-meta {
        font-size: .92rem;
        opacity: .88;
        margin-top: 3px;
    }

    .badge-active {
        background: rgba(34, 197, 94, 0.18);
        color: #ffffff;
        border: 1px solid rgba(34, 197, 94, 0.55);
        font-size: 0.78rem;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .badge-active::before {
        content: '';
        width: 8px;
        height: 8px;
        background: #22C55E;
        border-radius: 50%;
    }

    .badge-inactive {
        background: rgba(239, 68, 68, 0.15);
        color: #ffffff;
        border: 1px solid rgba(239, 68, 68, 0.55);
        font-size: 0.78rem;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .badge-inactive::before {
        content: '';
        width: 8px;
        height: 8px;
        background: #EF4444;
        border-radius: 50%;
    }

    .btn-profile-action {
        background: rgba(255, 255, 255, .15);
        border: 1px solid rgba(255, 255, 255, .35);
        color: #fff;
        font-weight: 600;
        font-size: .85rem;
        padding: 7px 16px;
        border-radius: 8px;
        transition: background .2s;
        backdrop-filter: blur(4px);
    }

    .btn-profile-action:hover {
        background: rgba(255, 255, 255, .28);
        color: #fff;
    }

    /* ── Snapshot Card ───────────────────────────── */
    .snapshot-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px 20px;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-color);
    }

    .snapshot-card h6 {
        font-size: .95rem;
        font-weight: 800;
        color: #1E2D42;
        margin-bottom: 18px;
    }

    .snapshot-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid var(--border-color);
        font-size: .88rem;
    }

    .snapshot-row:last-child {
        border-bottom: none;
    }

    .snapshot-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        color: #1E2D42;
    }

    .snapshot-label i {
        color: var(--primary);
        font-size: 1rem;
    }

    .snapshot-value {
        color: var(--text-muted);
        font-weight: 600;
    }

    /* ── Detail Card ─────────────────────────────── */
    .detail-card {
        background: #fff;
        border-radius: 16px;
        padding: 28px 28px 20px;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--border-color);
    }

    .detail-card .employee-title {
        font-size: 1.4rem;
        font-weight: 800;
        margin-bottom: 2px;
    }

    .detail-card .employee-subtitle {
        font-size: .88rem;
        color: var(--text-muted);
    }

    /* ── Nav Tabs ─────────────────────────────────── */
    .profile-tabs {
        border: none;
        gap: 6px;
        margin: 20px 0 24px;
    }

    .profile-tabs .nav-link {
        border: 1px solid var(--border-color) !important;
        border-radius: 10px !important;
        color: #5A738A;
        font-weight: 600;
        font-size: .85rem;
        padding: 8px 18px;
        background: #F8FBFF;
        transition: all .2s;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .profile-tabs .nav-link:hover {
        background: var(--primary-light);
        color: var(--primary);
        border-color: var(--primary) !important;
    }

    .profile-tabs .nav-link.active {
        background: var(--primary) !important;
        color: #fff !important;
        border-color: var(--primary) !important;
    }

    /* ── Personal Details Grid ───────────────────── */
    .section-title {
        font-size: .98rem;
        font-weight: 800;
        color: #1E2D42;
        margin-bottom: 10px;
        padding-bottom: 0 !important;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title i {
        color: var(--primary);
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }

    .detail-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
    }

    .detail-item > div {
        display: flex;
        justify-content: space-between;
        width: 100%;
        flex-wrap: wrap;
    }

    .detail-item:nth-child(odd) {
        border-right: 1px solid var(--border-color);
    }

    .detail-item:nth-last-child(-n+2) {
        border-bottom: none;
    }

    .detail-item i {
        color: var(--primary);
        font-size: 1.05rem;
        margin-top: 2px;
    }

    .detail-item-label {
        font-size: .8rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-top: 2px;
        width: 49%;
    }

    .detail-item-value {
        font-size: .9rem;
        color: #1E2D42;
        font-weight: 700;
        width: 49%;
        text-align: right;
    }

    /* ── Responsive ──────────────────────────────── */
    @media (max-width: 768px) {
        .profile-header {
            padding: 20px;
        }

        .employee-name {
            font-size: 1.3rem;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }

        .detail-item:nth-child(odd) {
            border-right: none;
        }

        .detail-item:nth-last-child(-n+2) {
            border-bottom: 1px solid var(--border-color);
        }

        .detail-item:last-child {
            border-bottom: none;
        }
    }
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet" />
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // Initialize DataTable
        datatable({
            tableId: "employee-table-dynamic",
            url: "{{ route('employee.index') }}",
            dataLength: '[data-length]',
            dataSearch: '[data-search]',
            dataFilter: '[data-filter]',
            dataExport: '[data-export]',
            dataDateFilter: '[data-date-filter]',
            dataShowEntries: '[data-show-entries]',
            dataPagination: '[data-pagination]',
            dataStateSave: true
        });
    });

    const CSRF = '{{ csrf_token() }}';
    const empCheckUrl = "{{ route('addEmp.checkEmpID') }}";
    const phoneEmailCheckURL = "{{ route('addEmp.checkMailPhone') }}";
    const formSubmitURL = "{{ route('addEmp.quickAddEmp') }}";
    const redirectURL = "{{ url('/admin/employee/') }}";
</script>
{{--
      <script>
        document.getElementById('exportEmployees').addEventListener('click', function () {

            fetch("{{ route('employees.export') }}", {
headers: {
'X-Requested-With': 'XMLHttpRequest'
}
})
.then(response => {
// ❌ validation error
if (!response.ok) {
return response.json().then(err => {
Swal.fire({
icon: 'warning',
title: 'Export Blocked',
html: `
<p>${err.message}</p>
<ul style="text-align:left">
    ${err.missing_fields.map(f => `<li><b>${f}</b></li>`).join('')}
</ul>
`
});
});
}

// ✅ success → download file
return response.blob().then(blob => {
let link = document.createElement('a');
link.href = window.URL.createObjectURL(blob);
link.download = 'employees.xlsx';
link.click();
});
});

});
</script> --}}

<!-- Include CSRF token in your <head> -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {

        // Setup CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#importForm').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);

            // Show loading modal
            Swal.fire({
                title: 'Importing...',
                text: 'Please wait while employees are being imported.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();

                    // Send AJAX request
                    $.ajax({
                        url: '/employee/import', // Laravel route
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message
                            });
                        },
                        error: function(xhr) {
                            let message = 'Import failed. Please check the file and try again.';
                            if (xhr.status === 403 && xhr.responseJSON?.type === 'LIMIT_REACHED') {
                                message = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Action not allowed',
                                html: `You’ve reached your employee limit.<br>
                                    Please upgrade your plan to add more employees.`
                            });
                        }
                    });
                }
            });
        });
    });
</script>


<script src="{{ asset('assets/js/employee/quick-add-emp.js') }}"></script>
@endsection


@if(session('export_error'))
<script>
    Swal.fire({
        icon: 'warning',
        title: 'Export Failed',
        html: `
            <p>{{ session('export_error') }}</p>
            <ul style="text-align:left">
                @foreach(session('missing_fields') as $field)
                    <li><b>{{ $field }}</b></li>
                @endforeach
            </ul>
        `,
        confirmButtonText: 'Ok, I will configure'
    });
</script>
@endif

@section('content')
<div>
    <div class=" p-0 pb-4">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="active"><span><b>Employee</b></span></li>
        </ol>
    </div>

    <div class="modal fade" id="employeeModal_old" tabindex="-1" role="dialog" aria-labelledby="employeeModal_oldLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content tx-size-sm">
                <div class="card user-pro-list overflow-hidden" id="avtarDiv"
                    style="margin-bottom: 0rem;border-radius: 0px;">

                    <button type="button" class="btn-close position-absolute"
                        style="right: 15px; top: 15px; z-index: 1;" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>

                    <div class="card-body py-5">
                        <div class="row user-pic text-left">
                            <div class="col-1 pt-3">
                                <span class="avatar avatar-xxl brround" id="empAvtar"
                                    style="background-image: url(" {{ asset('assets/imgs/user.png') }}");"></span>
                            </div>
                            <div class="col-11 text-left">
                                <h1 class="px-4 pt-6 mt-6 mb-0" id="empName"></h1>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <tbody class="text-center">
                                    <tr>
                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Email </span>
                                        </td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empEmail"></td>

                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Role </span>
                                        </td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empRole"></td>
                                    </tr>

                                    <tr>
                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">DOB </span></td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empBirth"></td>

                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Phone </span>
                                        </td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empPhone"></td>
                                    </tr>

                                    <tr>
                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Marital
                                                Status</span></td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empMarital"></td>

                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Department
                                            </span></td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empDepartment"></td>
                                    </tr>

                                    <tr>
                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">DOJ </span></td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empJoining"></td>

                                        <td class="py-2 px-0"><span
                                                class="font-weight-semibold w-50">Designation</span></td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empDesignation"></td>
                                    </tr>

                                    <tr>
                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Status </span>
                                        </td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empStatus"></td>

                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Grade </span>
                                        </td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empGrade"></td>
                                    </tr>

                                    <tr>
                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Attendance
                                                Mode</span></td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empMode"></td>

                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Type </span>
                                        </td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empType"></td>
                                    </tr>

                                    <tr>
                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Assign
                                                Shift</span></td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empShift"></td>

                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Code </span>
                                        </td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empCode"></td>
                                    </tr>

                                    <tr>
                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Branch </span>
                                        </td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empBranch"></td>

                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Gender </span>
                                        </td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empGender"></td>
                                    </tr>

                                    <tr>
                                        <td class="py-2 px-0"><span class="font-weight-semibold w-50">Policy
                                                Category</span></td>
                                        <td class="py-2 px-0">:</td>
                                        <td class="py-2 px-0" id="empPolicy"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- EMPLOYEE DETAIL MODAL --}}
    <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header d-block" style="padding-right: 10px">

                    <!-- Profile Header -->
                    <div class="profile-header">
                        <div class="d-flex align-items-center gap-4 flex-wrap">

                            <!-- Avatar -->
                            <div class="avatar-wrapper">
                                <div id="vm-empAvtar" style="background-image: url('{{ asset('assets/imgs/user.png') }}')"></div>
                            </div>

                            <!-- Name & Meta -->
                            <div class="flex-grow-1">
                                <h1 class="employee-name vm-empName"></h1>
                                <div class="employee-meta d-flex align-items-center gap-3 flex-wrap mt-1">
                                    <span class="vm-empDesignation"></span>
                                    <span class="opacity-50">|</span>
                                    <span class="vm-empDepartment"></span>
                                </div>
                                <div class="d-flex align-items-center gap-3 mt-2 flex-wrap"
                                    style="font-size:.85rem; opacity:.9;">
                                    <span>Employee Code: <strong class="vm-empCode"></strong></span>
                                    <span class="badge-active" id="vm-empStatus"></span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 flex-wrap" style="position:relative;z-index:1;">
                                <a href="#" class="btn btn-profile-action" id="vm-editProfileBtn">
                                    <i class="bi bi-pencil-fill me-1"></i> Edit Profile
                                </a>
                             
                                <a href="#" class="btn btn-profile-action" id="vm-downloadProfileBtn">
                                        <i class="bi bi-download me-1"></i> Download Profile
                                </a>
                                <button class="btn btn-profile-action px-2" title="More options">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                            </div>

                        </div>
                    </div>

                </div>

                <div class="modal-body">

                    <!-- Body Row -->
                    <div class="row g-4">

                        <!-- Snapshot -->
                        <div class="col-lg-3">
                            <div class="snapshot-card">
                                <h6>Employee Snapshot</h6>

                                <div class="snapshot-row">
                                    <span class="snapshot-label"><i class="bi bi-gender-ambiguous"></i>
                                        Gender</span>
                                    <span class="snapshot-value vm-empGender"></span>
                                </div>
                                <div class="snapshot-row">
                                    <span class="snapshot-label"><i class="bi bi-calendar3"></i> DOB</span>
                                    <span class="snapshot-value vm-empDOB"></span>
                                </div>
                                <div class="snapshot-row">
                                    <span class="snapshot-label"><i class="bi bi-calendar-check"></i> DOJ</span>
                                    <span class="snapshot-value vm-empDOJ"></span>
                                </div>
                                <div class="snapshot-row">
                                    <span class="snapshot-label"><i class="bi bi-briefcase-fill"></i> Type</span>
                                    <span class="snapshot-value vm-empType"></span>
                                </div>
                                <div class="snapshot-row">
                                    <span class="snapshot-label"><i class="bi bi-award-fill"></i> Grade</span>
                                    <span class="snapshot-value vm-empGrade"></span>
                                </div>
                                <div class="snapshot-row">
                                    <span class="snapshot-label"><i class="bi bi-building"></i> Branch</span>
                                    <span class="snapshot-value vm-empBranch"></span>
                                </div>
                                <div class="snapshot-row">
                                    <span class="snapshot-label"><i class="bi bi-clock-fill"></i>
                                        Assign mode</span>
                                    <span class="snapshot-value vm-empAssignMode"></span>
                                </div>

                            </div>
                        </div>

                        <!-- Detail Tabs -->
                        <div class="col-lg-9 ps-0">
                            <div class="detail-card">
                                <div class="employee-title vm-empName"></div>
                                <div class="employee-subtitle"><span class="vm-empDesignation"></span> | <span class="vm-empDepartment"></span></div>

                                <!-- Nav Tabs -->
                                <ul class="nav profile-tabs" id="profileTab" role="tablist">
                                    <li class="nav-item">
                                        <button class="nav-link active" data-bs-toggle="tab"
                                            data-bs-target="#personal">
                                            <i class="bi bi-person-fill"></i> Personal
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#work">
                                            <i class="bi bi-briefcase-fill"></i> Work
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#shift">
                                            <i class="bi bi-clock-history"></i> Shift & Attendance
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#policy">
                                            <i class="bi bi-shield-fill-check"></i> Policy
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content">

                                    <!-- Personal -->
                                    <div class="tab-pane fade show active" id="personal">
                                        <div class="section-title"><i class="bi bi-info-circle-fill"></i> Personal Details</div>

                                        <div class="detail-grid">
                                            @foreach ($personalDetails as $item)
                                                <div class="detail-item">
                                                    <i class="bi {{ $item['icon'] }}"></i>
                                                    <div>
                                                        <div class="detail-item-label">{{ $item['label'] }}</div>
                                                        <div class="detail-item-value {{ $item['class'] }}"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                    </div>

                                    <!-- Work -->
                                    <div class="tab-pane fade" id="work">
                                        <div class="section-title"><i class="bi bi-briefcase-fill"></i> Work Details</div>

                                        <div class="detail-grid">
                                            @foreach ($workDetails as $item)
                                                <div class="detail-item">
                                                    <i class="bi {{ $item['icon'] }}"></i>
                                                    <div>
                                                        <div class="detail-item-label">{{ $item['label'] }}</div>
                                                        <div class="detail-item-value {{ $item['class'] }}"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                    </div>

                                    <!-- Attendance -->
                                    <div class="tab-pane fade" id="shift">
                                        <div class="section-title"><i class="bi bi-shield-fill-check"></i> Shift & Attendance Details</div>

                                        <div class="detail-grid">
                                            @foreach ($policyDetails as $item)
                                                <div class="detail-item">
                                                    <i class="bi {{ $item['icon'] }}"></i>
                                                    <div>
                                                        <div class="detail-item-label">{{ $item['label'] }}</div>
                                                        <div class="detail-item-value {{ $item['class'] }}"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                    </div>

                                    <!-- Miscellaneous -->
                                    <div class="tab-pane fade" id="policy">
                                        <div class="section-title"><i class="bi bi-clock-history"></i> Assigned Policies</div>

                                        <div class="detail-grid">
                                            @foreach ($miscellaneousDetails as $item)
                                                <div class="detail-item">
                                                    <i class="bi {{ $item['icon'] }}"></i>
                                                    <div>
                                                        <div class="detail-item-label">{{ $item['label'] }}</div>
                                                        <div class="detail-item-value {{ $item['class'] }}"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                    </div>

                                </div>

                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>
    {{-- EMPLOYEE DETAIL MODAL --}}

    <!-- START ROW -->
    <div class="row">
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="text-start"> <span class="font-weight-semibold">Total</span>
                                <h3 class="mb-0 mt-1 text-success"> {{ $totalEmployeeCount }} </h3>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="icon1 bg-success-transparent my-auto pt-3 float-end"> <i
                                    class="las la-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <div class="text-start"> <span class="font-weight-semibold">Active</span>
                                <h3 class="mb-0 mt-1 text-success"> {{ $allEmployeeCount }} </h3>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="icon1 bg-success-transparent my-auto pt-3 float-end">
                                <i class="las la-users half-colored-icon"></i>
                            </div>
                        </div>

                        <div class="col-4">
                            <div class="text-start" style="white-space: nowrap;"> <span
                                    class="font-weight-semibold">Inactive</span>
                                <h3 class="mb-0 mt-1  text-danger"> {{ $allInactiveEmployeeCount }} </h3>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>



        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-7">
                            <div class="mt-0 text-start"> <span class="font-weight-semibold">Male</span>
                                <h3 class="mb-0 mt-1 text-primary"> {{ $maleEmployeesCount }}</h3>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="icon1 bg-primary-transparent my-auto pt-3 float-end"> <i
                                    class="las la-male"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <div class="mt-0 text-start"> <span class="font-weight-semibold">Female</span>
                                <h3 class="mb-0 mt-1 text-primary"> {{ $femaleEmployeesCount }}</h3>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="icon1 bg-primary-transparent my-auto float-end pt-3"> <i
                                    class="las la-female"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-7">
                            <div class="mt-0 text-start"> <span class="font-weight-semibold">New</span>
                                <h3 class="mb-0 mt-1 text-success"> {{ $newEmployeesCount }}
                                </h3>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="icon1 bg-success-transparent my-auto pt-3 float-end"> <i
                                    class="las la-user-friends"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-7">
                            <div class="mt-0 text-start"> <span class="font-weight-semibold">Exit</span>
                                <h3 class="mb-0 mt-1 text-danger"> {{ $exitEmployeesCount }}
                                </h3>
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="icon1 bg-danger-transparent my-auto pt-3 float-end"> <i
                                    class="las la-user-friends"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- END ROW -->
    @if (session('import_errors_blade'))
    <div class="alert d-flex align-items-center mt-3">
        <p>There were errors in the import. You can download the error file from the link below:</p>
        <a href="{{ route('employee.downloadErrorFile') }}" onclick="location.reload()"
            class="ms-2 mb-4 btn btn-danger">Download Error File</a>
    </div>
    @endif
    <!-- ROW -->
    <div class="row">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">

                <div class="card-header border-0">
                    <h4 class="card-title">Employee</h4>

                    <div class="page-rightheader ms-auto">
                        <div class="align-items-end flex-wrap my-auto right-content breadcrumb-right">
                            <div class="d-flex gap-3">
                                <!--<div class="btn-list">
                                        <a class="btn btn-outline-info my-auto" data-bs-target="#checkImgEmployee" data-bs-toggle="modal"
                                            href="javascript:void(0)">
                                            Check Image
                                        </a>
                                    </div>-->
                                <div class="btn-list">
                                    @if ($permission->check_route_permission('admin/employee/form', 115))
                                    <a class="btn btn-outline-info my-auto"
                                        data-bs-target="#quickAddEmployee"
                                        data-bs-toggle="modal"
                                        href="javascript:void(0)">
                                        Quick Add Employee
                                    </a>
                                    @endif
                                </div>

                                <div class="btn-list">
                                    @if ($permission->check_route_permission('admin/employee/form', 115))
                                    <a class="btn btn-outline-info my-auto"
                                        href="{{ route('employee.form') }}">
                                        Add New Employee
                                    </a>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-sm-1">
                            <div class="form-group">
                                <p class="form-label">Show entries</p>
                                <select id="customLengthMenu" class="form-select-md p-2 search_test"
                                    style="width: 100%" data-length>
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-sm-2">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                    data-search />
                            </div>
                        </div>

                        <div class="col-sm-4">
                        </div>
                        <div class="col-sm-2" style="margin-top: 10px;">
                            <div id="approval-buttons" class="d-flex justify-content-end gap-3 m-5">
                                <label class="custom-control custom-checkbox-md mx-3 d-flex align-items-center">
                                    Select All &nbsp;&nbsp;
                                    <input type="checkbox" id="selectAll" class="custom-control-input-success"
                                        name="example-checkbox1" value="option1" onclick="selectAllCheckboxes(this)">
                                    <span class="custom-control-label-md success"></span>
                                </label>
                            </div>
                        </div>



                        <div class="col-sm-1">
                            <button class="custom-button w-100" type="button" onclick="toggleFilters()"
                                style="margin-top: 28px;">
                                <!-- Custom SVG: 2 horizontal lines with knobs -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.5">
                                    <!-- Top slider -->
                                    <line x1="3" y1="8" x2="21" y2="8"
                                        stroke-linecap="round" />
                                    <circle cx="10" cy="8" r="1.5" fill="currentColor" />

                                    <!-- Bottom slider -->
                                    <line x1="3" y1="16" x2="21" y2="16"
                                        stroke-linecap="round" />
                                    <circle cx="16" cy="16" r="1.5" fill="currentColor" />
                                </svg>
                                Filters
                            </button>
                        </div>

                        <div class="col-sm-1"
                            style=" padding-left: 1px;  padding-right: 1px; height: 10px; margin-top: 28px;    ">
                            <div class="form-group dropdown">
                                <button class="export-button dropdown-toggle" type="button" id="defaultDropdown"
                                    data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                                    <i class="fa fa-download me-2"></i> Export As
                                </button>
                                <ul class="dropdown-menu dropdown-menu-export" aria-labelledby="defaultDropdown">
                                    <li><a class="dropdown-item" href="#" data-export="csv">CSV</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="excel">Excel</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="pdf">PDF</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="copy">Copy</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="print">Print</a></li>
                                </ul>
                            </div>
                        </div>




                        <div class="col-sm-1" style="margin-top: 30px;">
                            <div class="form-group filter_dots">
                                <button class="btn btn-info" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>

                                <ul class="dropdown-menu p-2" aria-labelledby="actionDropdown"
                                    style="min-width: 220px;">
                                    @if ($permission->check_route_permission('admin/employee/form', 115))
                                    <li>
                                        <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                            data-bs-toggle="modal" data-bs-target="#addEmployeeFile">
                                            <i class="las la-file-upload"></i> Upload File
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)"
                                            class="dropdown-item text-warning fw-semibold d-flex align-items-center gap-2"
                                            id="exportEmployees">
                                            <i class="las la-file-download"></i> Export Format
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-success fw-semibold d-flex align-items-center gap-2"
                                            id="bulkButton">
                                            <i class="las la-tasks"></i> Bulk Update
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#"
                                            class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                            data-bs-toggle="modal" data-bs-target="#emp_img_bulk_upload"><i
                                                class="nav-icon ion ion-images mx-1"></i> <span
                                                class="my-auto">Bulk Image Upload</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#"
                                            class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                            data-bs-toggle="modal" data-bs-target="#emp_details_bulk_upload"><i
                                                class="nav-icon ion ion-images mx-1"></i> <span
                                                class="my-auto">Bulk Employee Details</span>
                                        </a>
                                    </li>
                                    @endif
                                </ul>

                            </div>
                        </div>


                        <div class="row" id="filterContainer" style="display: none; margin-bottom: 21px;">
                                <div class="col-md">
                                    <label for="dealershipFilter" class="form-label">Dealership</label>
                                    <select id="dealershipFilter" data-filter
                                        class="form-select search_test filter_border"style="border-radius: 20px;">
                                        <option value="">All</option>
                                        @foreach ($dealerships as $key => $val)
                                            <option value="{{ $key }}">{{ $val }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label for="branchFilter" class="form-label">Branch</label>
                                    <select id="emp_branchFilter" data-filter
                                        class="form-select search_test filter_border"style="border-radius: 20px;">
                                        <option value="">All</option>
                                        @foreach ($branchFilter as $branchF)
                                            <option value="{{ $branchF->br_id }}">{{ $branchF->br_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md">
                                    <label for="departmentFilter" class="form-label">Department</label>
                                    <select id="emp_departmentFilter" data-filter
                                        class="form-select search_test filter_border" style="border-radius: 20px;">
                                        <option value="">All</option>
                                        @foreach ($departmentFilter as $departmentF)
                                            <option value="{{ $departmentF->d_id }}">{{ $departmentF->d_name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                                <div class="col-md">
                                    <label for="designationFilter" class="form-label">Designation</label>
                                    <select id="emp_designationFilter" data-filter
                                        class="form-select search_test filter_border" style="border-radius: 20px;">
                                        <option value="">All</option>
                                        @foreach ($designationFilter as $designationF)
                                            <option value="{{ $designationF->dg_id }}">
                                                {{ $designationF->dg_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md">
                                    <label for="activeFilter" class="form-label">Employee Status</label>
                                    <select id="emp_activeFilter" data-filter
                                        class="form-select search_test filter_border" style="border-radius: 20px;">
                                        <option value="">All</option>
                                        @foreach ($employee_status as $data)
                                            <option value="{{ $data->m_id }}"
                                                {{ $data->m_name == 'Active' ? 'selected' : '' }}>
                                                {{ $data->m_name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                                {{-- <div class="col-sm-6 ">
                                            <label for="toDate" class="form-label">Date</label>
                                            <span class="bg-light border-0 rounded-start-4">
                                                <span
                                                    class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 34px; height: 34px;">
                                                    <i class="las la-calendar-alt fs-5"></i>
                                                </span>
                                            </span>
                                            <input type="text" id="fromDate" name="fromDate" class="form-control"
                                                data-date-filter="from-date">
                                        </div> --}}

                                <div class="col-md">
                                    <div class="form-group">
                                        <p class="form-label">Grade</p>
                                        <select id="emp_gradeFilter" data-filter
                                            class="form-select search_test filter_border"style="border-radius: 20px;">
                                            <option value="">All</option>
                                            @foreach ($gradeFilter as $gradeF)
                                                <option value="{{ $gradeF->g_id }}">{{ $gradeF->g_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                        <script>
                            function toggleFilters() {
                                const container = document.getElementById('filterContainer');
                                container.style.display = container.style.display === 'none' ? 'flex' : 'none';
                            }
                        </script>
                        <script>
                            window.addEventListener('DOMContentLoaded', function() {
                                document.getElementById('mt_monthFilter').value = '';
                            });
                        </script>




                        <style>
                            .export-button {
                                display: flex;
                                align-items: center;
                                gap: 6px;
                                background-color: white;
                                border: 1px solid #ddd;
                                border-radius: 999px;
                                padding: 8px 14px;
                                font-size: 14px;
                                cursor: pointer;
                                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                                transition: background-color 0.2s ease, box-shadow 0.2s ease;
                            }

                            .export-button:hover {
                                background-color: #f1f1f1;
                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                            }

                            .dropdown-menu-export {
                                font-size: 14px;
                                min-width: 140px;
                            }

                            .dropdown-menu-export .dropdown-item:hover {
                                background-color: #f8f9fa;
                            }

                            .custom-button {
                                display: flex;
                                align-items: center;
                                gap: 6px;
                                background-color: white;
                                border: 1px solid #ddd;
                                border-radius: 999px;
                                padding: 8px 14px;
                                font-size: 14px;
                                cursor: pointer;
                                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                                transition: background-color 0.2s ease, box-shadow 0.2s ease;
                            }

                            .custom-button:hover {
                                background-color: #f1f1f1;
                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                            }

                            .custom-button svg {
                                width: 16px;
                                height: 16px;
                            }
                        </style>

                    </div>

                    <div class="">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom"
                            id="employee-table-dynamic">
                            <thead>
                                <tr>
                                    @foreach ($columns as $column)
                                    <th style="font-size: 13px">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="row mt-5">
                        <div class="col-sm-6">
                            <div id="custom-show-entries" data-show-entries></div>
                        </div>
                        <div class="col-sm-6 d-flex justify-content-end">
                            <ul data-pagination class="custom-pagination"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="emp_img_bulk_upload" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title">Employee bulk image upload</h4><button aria-label="Close"
                        class="btn-close" data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <form method="POST" enctype="multipart/form-data"
                    action="{{ route('employee.bulk.image.upload') }}">
                    @csrf
                    <p class=" fs-13 px-4 mt-3 pb-3 border-bottom " style="color: rgb(110, 104, 88)">Please upload the
                        bulk image for employee in png, jpg or jpeg format.</p>
                    <input type="hidden" name="POST_TYPE" value="EMP_BULK_UPLOAD">
                    <div class="modal-body" id="imageModal"
                        style="width: 498px;height: 400px;overflow-y: auto;padding: 10px;border: 1px solid #ccc;margin: 20px auto;border-radius: 10px;box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <h3 class="card-title">File Upload</h3>
                        <!-- <input type="file" name="images[]" class="dropify" multiple accept=".jpg,.jpeg,.png"
                                        style="display: block; margin: 0 auto;" required id="imageInput" /> -->
                        <input type="file" id="imageInput" multiple accept=".jpg,.jpeg,.png" class="dropify"
                            required />
                        <div class="image-preview-container" id="previewContainer"></div>
                    </div>
                    <div class="modal-footer py-1">
                        <a class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</a>
                        <button type="submit" class="btn btn-outline-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.getElementById('imageInput').addEventListener('change', function(event) {
                const previewContainer = document.getElementById('previewContainer');
                previewContainer.innerHTML = ''; // Clear previews

                const files = event.target.files;
                Array.from(files).forEach(file => {
                    if (!file.type.startsWith('image/')) return;

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imgDiv = document.createElement('div');
                        imgDiv.classList.add('image-preview');
                        imgDiv.innerHTML = `
                        <img src="${e.target.result}" alt="${file.name}">
                        <div class="tooltip" title="${file.name}">${file.name}</div>
                      `;
                        previewContainer.appendChild(imgDiv);
                    };
                    reader.readAsDataURL(file);
                });
            });
        </script>
    </div>

    <!-- Upload Employee Details Modal -->
    <div class="modal fade" id="emp_details_bulk_upload" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2" id="modal-title">Upload Employee Details</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="{{ route('import.employee.details') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group">
                                <!-- Field Selection -->
                                <div class="mb-3">
                                    <label for="updateField2" class="form-label">Select Field to Upload</label>
                                    <select class="form-select" id="updateField2" name="field_type" required>
                                        <option value="">Select Field</option>
                                        <option value="epf_esic">EPFO/ESIC Details</option>
                                        <option value="identity">Identity</option>
                                        <option value="bank_details">Bank Details</option>
                                    </select>
                                </div>

                                <!-- File Upload -->
                                <div class="form-group">
                                    <label class="form-label">Upload File :</label>
                                    <input type="file" name="import_file" id="import_file" class="form-control"
                                        required accept=".xlsx, .csv">
                                    <br>
                                    <div style="display: flex; align-items: center;">
                                        <p class="fw-bold" style="margin: 0;">Note -</p>
                                        <p style="margin: 0; margin-left: 5px;">Only upload xlsx, csv files</p>
                                    </div>
                                </div>

                                <!-- Sample Download -->
                                <div class="form-group mt-2">
                                    <!-- <button type="button" class="btn btn-info" id="downloadSampleBtn">
                                                    Download Sample Format
                                                </button> -->
                                    <a href="javascript:void(0)" class="btn btn-info" id="downloadSampleBtn">
                                        Download Sample Format
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-end">
                        <button type="reset" class="btn btn-danger cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary savebtn" id="saveUptBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden Input for Route Template -->
    <input type="hidden" id="sampleRouteTemplate" value="{{ route('download.employee.details.sample', ':type') }}">

    {{-- for model file upload strat --}}
    <div class="modal fade" id="addEmployeeFile" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2" id="modal-title">Upload Employee File</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('employee.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <input type="text" id="editId" name="editTravelVehicle" hidden>
                            <input type="text" id="travelMode" hidden>
                            <input type="text" id="travelVehicle" hidden>
                            <input type="text" id="travelClass" hidden>
                            <input type="text" id="travelOwner" hidden>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Upload File :</label>
                                    <input type="file" name="import_file" id="import_file" class="form-control"
                                        required accept=".xlsx, .csv">
                                    <br>
                                    <div style="display: flex; align-items: center;">
                                        <p class="fw-bold" style="margin: 0;">Note -</p>
                                        <p style="margin: 0; margin-left: 5px;">Only upload xlsx, csv files</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="reset" class="btn btn-danger cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary savebtn" id="saveUptBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bulkUpdateModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateModalLabel">Update Selected Employees</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form id="updateBulkForm">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="updateField" class="form-label">Select Field to Update</label>
                                <select class="form-select" id="updateField">
                                    <option value="">Select Field</option>
                                    @foreach ($bulkUpdateFields as $key => $upf)
                                    <option value="{{ $key }}" data-type="{{ $upf['input_type'] }}">
                                        {{ $upf['label'] }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3" id="updateInputContainer">
                                <label for="newValue" class="form-label">New Value</label>
                                <input type="text" class="form-control" id="newValue" readonly>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-outline-primary"
                                id="updateBulkButton">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- for model file upload end --}}

    <!-- Employee Image Modal -->
    <div class="modal fade" id="checkImgEmployee" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-circle me-2"></i>Employee Image Viewer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="emp_code" class="form-label">Employee Code</label>
                        <select class="form-select" name="emp_code" id="emp_code" required>
                            <option value="">-- Choose an employee --</option>
                            {{-- @foreach($employees as $emp)
                                    <option value="{{ $emp->emp_code }}">{{ $emp->emp_code }}</option>
                            @endforeach --}}
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Upload Image</label>
                        <input class="form-control" type="file" name="image" id="image" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload me-1"></i> Upload Image
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal for Quick Add Employee --}}
    <div class="modal fade" id="quickAddEmployee" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Add New Employee</h6><button aria-label="Close" class="btn-close"
                        data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    {{-- Tabs Panel --}}
                    <div class="panel panel-primary">
                        {{-- Tab Headings --}}
                        <div class=" tab-menu-heading p-0 bg-light">
                            <div class="tabs-menu1 ">
                                <!-- Tabs -->
                                <ul class="nav panel-tabs">
                                    <li class=""><a href="javascript:void(0)" data-target="#about"
                                            class="active tab-link fs-17">About</a></li>
                                    <li><a href="javascript:void(0)" data-target="#organization"
                                            class="tab-link fs-17">Organization</a></li>
                                    <li><a href="javascript:void(0)" data-target="#attendanceLeave"
                                            class="tab-link fs-17">Attendance & Leave</a></li>
                                    <li><a href="javascript:void(0)" data-target="#joining"
                                            class="tab-link fs-17">Joining</a></li>
                                </ul>
                            </div>
                        </div>

                        {{-- MODAL BODY START --}}
                        <div class="row mt-5">

                            {{-- SideBar START --}}
                            <div class="col-xl-3 col-md-12 col-lg-12" id="sidebard-employee-card">

                                {{-- Avatar Div --}}
                                <div class="card user-pro-list overflow-hidden" id="avtarDiv">
                                    <div class="card-body">
                                        <div class="user-pic text-center">
                                            <!-- First Modal (Working) -->
                                            <span class="avatar avatar-xxl brround emp-avatar" data-target="1"
                                                onclick="openModal('uploadModal')"
                                                style="background-image: url('{{ asset('assets/imgs/user.png') }}');">
                                            </span>
                                            <input type="file" id="profileInput" style="display:none;"
                                                accept="image/*" onchange="handleFileSelect(this, 'uploadModal')" />

                                            {{-- Upload Modal --}}
                                            <div id="uploadModal" class="modal uploadModal">
                                                <div class="modal-content uploadModal">
                                                    <div id="cropModal1" class="crop-modal"
                                                        style="display: none; justify-content: center; align-items: center; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.7); z-index: 1100;">
                                                        <div
                                                            style="background: white; padding: 20px; max-width: 400px; width: 100%; border-radius: 10px;">
                                                            <img id="cropperImage1"
                                                                style="max-width: 100%; border-radius: 10px;" />
                                                            <div style="margin-top: 10px; text-align: right;">
                                                                <button onclick="cropImage('uploadModal')"
                                                                    class="btn btn-outline-primary">Crop & Use</button>
                                                                <button onclick="closeCropModal('uploadModal')"
                                                                    class="btn btn-outline-danger">Cancel</button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="tab-container">
                                                        <div class="tab-buttons">
                                                            <div class="tab-button active"
                                                                onclick="switchTab('gallery', 'uploadModal')">Drive
                                                            </div>
                                                            <div class="tab-button"
                                                                onclick="switchTab('camera', 'uploadModal')">Camera
                                                            </div>
                                                        </div>

                                                        <div id="gallery-tab-1"
                                                            class="tab-content cam-tab-content active"
                                                            data-modal="uploadModal">
                                                            <div class="camera-container">
                                                                <label for="profileInput" class="upload-icon"
                                                                    id="upload-area-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        fill="none" viewBox="0 0 24 24"
                                                                        stroke="currentColor">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                    </svg>
                                                                    <p>Click to browse image</p>
                                                                </label>

                                                                <div id="gallery-preview-container-1"
                                                                    style="display: none;">
                                                                    <img id="gallery-preview-1" src=""
                                                                        style="max-width: 100%; max-height: 300px;" />
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div id="camera-tab-1" class="tab-content cam-tab-content"
                                                            data-modal="uploadModal">
                                                            <div id="camera-container-1" class="camera-container">
                                                                <video id="video-1" width="100%" height="auto"
                                                                    autoplay playsinline></video>
                                                                <div class="button-row">
                                                                    <button id="startCameraBtn-1"
                                                                        class="btn btn-success"
                                                                        onclick="startCamera('uploadModal')">Start
                                                                        Camera</button>
                                                                    <button id="captureBtnCamera-1"
                                                                        class="btn btn-success"
                                                                        onclick="capturePhoto('uploadModal')"
                                                                        style="display: none;">Capture
                                                                        Image</button>
                                                                </div>
                                                            </div>

                                                            <canvas id="canvas-1" style="display:none;"></canvas>

                                                            <div id="camera-preview-container-1"
                                                                class="preview-container" style="display: none;">
                                                                <img id="camera-preview-1" src=""
                                                                    style="max-width: 100%; max-height: 300px;" />
                                                                <div class="button-row">
                                                                    <button class="btn btn-danger"
                                                                        onclick="retakePhoto('uploadModal')">Retake</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-danger"
                                                            onclick="closeModal('uploadModal')">Cancel</button>
                                                        <button type="button" class="btn btn-outline-primary"
                                                            id="saveBtn-1" name="action" value="ajaxcapsave"
                                                            onclick="saveImage('uploadModal')" disabled>Save</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="pro-user mt-3">
                                                <h5 class="pro-user-username text-dark mb-1 fs-16" id="employeeName">
                                                    Employee Name</h5>
                                                <h6 class="pro-user-desc text-muted fs-12" id="emailText">-</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            {{-- SideBar END --}}

                            <div class="col-xl-9 col-md-12 col-lg-12">

                                {{-- Tab Panel Body START --}}
                                <div class="panel-body tabs-menu-body pt-0">
                                    <div class="tab-content">

                                        {{-- About Tab START --}}
                                        <div class="tab-pane active " id="about">

                                            {{-- About Form START --}}
                                            <form method="post" id="about-form">
                                                <div class="form-group">
                                                    <h4 class="card-title mb-1 text-primary pt-4">About Employee</h4>
                                                    <div class="row">

                                                        {{-- Employee Code --}}
                                                        <div class="row">
                                                            <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                                                <input type="hidden" id="primary_id">
                                                                <label for="employee_id"
                                                                    class="form-label mb-0 mt-2">Employee Code <span
                                                                        class="text-danger">*</span></label>
                                                                <input id="employee_id" name="emp_code"
                                                                    type="text" class=" form-control"
                                                                    {{ $newEmpCode ? 'readonly' : '' }}
                                                                    value="{{ $newEmpCode ?? '' }}"
                                                                    placeholder="Employee Code Like: IT001"
                                                                    onchange="checkEmployeeId(this)"
                                                                    onkeyup="return validateFields('employee_id')">
                                                                <span class="text-danger" id="EmpIdError"></span>
                                                            </div>
                                                        </div>

                                                        {{-- Employee Name --}}
                                                        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                                            <div class="row">
                                                                <!-- Prefix -->
                                                                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                                                    <label for="prefix" class="form-label mb-0 mt-2">
                                                                        Prefix <span class="text-danger">*</span>
                                                                    </label>

                                                                    <select class="form-control form-select select2"
                                                                        id="prefix"
                                                                        data-placeholder="Select Prefix"
                                                                        required>
                                                                        <option value="">Select Prefix</option>
                                                                        @foreach ($prefix as $prefixitem)
                                                                        <option value="{{ $prefixitem->m_id }}"
                                                                            {{ isset($employee) && $employee->emp_prefix == $prefixitem->m_id ? 'selected' : '' }}>
                                                                            {{ $prefixitem->m_name }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="col-md-8">
                                                                    <label class="form-label mb-0 mt-2"
                                                                        for="emp_fname">First Name <span
                                                                            class="text-danger">*</span></label>
                                                                    <input type="text" id="emp_fname"
                                                                        class="form-control"
                                                                        onkeyup="return validateFields('emp_fname')"
                                                                        oninput="validAlpha(this)" maxlength="50"
                                                                        placeholder="First Name" name="emp_fname"
                                                                        required>
                                                                    <span class="text-danger"
                                                                        id="firstNameErrorShow"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                                            <label for="emp_mname" class="form-label mb-0 mt-2">Middle
                                                                Name</label>
                                                            <input type="text" id="emp_mname" class="form-control"
                                                                oninput="validAlpha(this)" placeholder="Middle Name"
                                                                name="emp_mname">
                                                            <span class="text-danger" id="middleNameError"></span>
                                                        </div>
                                                        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                                            <label for="emp_lname" class="form-label mb-0 mt-2">Last
                                                                Name <span class="text-danger"></span></label>
                                                            <input type="text" id="emp_lname"
                                                                class=" form-control" oninput="validAlpha(this)"
                                                                placeholder="Last Name" name="emp_lname" required>
                                                            <span class="text-danger" id="lastNameError"></span>
                                                        </div>

                                                        <!-- Gender -->
                                                        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                                            <label for="gender" class="form-label mb-0 mt-2">
                                                                Gender <span class="text-danger">*</span>
                                                            </label>

                                                            <select class="form-control form-select select2"
                                                                id="gender"
                                                                data-placeholder="Select Gender"
                                                                required>
                                                                <option value="">Select Gender</option>
                                                                @foreach ($staticGender as $gender)
                                                                <option value="{{ $gender->m_id }}"
                                                                    {{ isset($employee) && $employee->emp_gender_id == $gender->m_id ? 'selected' : '' }}>
                                                                    {{ $gender->m_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>

                                                            <span class="text-danger" id="genderError"></span>
                                                        </div>


                                                        {{-- Marital Status --}}
                                                        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                                            <label for="mariteStatus"
                                                                class="form-label mb-0 mt-2">Marital Status </label>
                                                            <select class="form-control form-select select2"
                                                                id="mariteStatus" name="emp_marital_status_id"
                                                                data-placeholder="Select Marital Status"
                                                                aria-label="Type" required>
                                                                <option class="text-muted" value=""
                                                                    label="Select Marital Status">
                                                                </option>
                                                                @foreach ($maritalStatus as $martial)
                                                                <option value="{{ $martial->m_id }}">
                                                                    {{ $martial->m_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger" id="mariteStatusError"></span>
                                                        </div>

                                                        {{-- DOB --}}
                                                        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                                            <label for="dateOfBirth" class="form-label mb-0 mt-2">Date
                                                                Of Birth <span class="text-danger">*</span></label>
                                                            <input type="date" max="2099-12-31" id="dateOfBirth"
                                                                name="emp_dob" class="form-control" required
                                                                onkeyup="return validateFields('dateOfBirth')">
                                                            <span class="text-danger" id="dateOfBirthError"></span>
                                                        </div>

                                                        <h4 class="card-title mb-1 mt-4 text-primary">Contact Info</h4>

                                                        {{-- Mobile --}}
                                                        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                                            <label for="contact"
                                                                class="form-label mb-0 mt-2">Personal Mobile <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="tel" id="contact" class="form-control"
                                                                placeholder="Enter 10-digit phone number"
                                                                name="emp_phone"
                                                                oninput="this.value = this.value.replace(/\D/g, '').substring(0, 10); checkPhoneEmail(this, 0);"
                                                                maxlength="10"
                                                                onkeyup="return validateFields('contact')">
                                                            <span class="text-danger" id="contactError"></span>
                                                        </div>

                                                        {{-- Email --}}
                                                        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                                            <label for="email"
                                                                class="form-label mb-0 mt-2">Personal Email</label>
                                                            <input name="emp_email" type="text" id="email"
                                                                class="form-control" placeholder="Email"
                                                                oninput="checkPhoneEmail(this,1)"
                                                                oninput="this.value = this.value.replace(/[^a-zA-Z0-9._@-]/g, '');"
                                                                required>
                                                            <span class="text-danger" id="emailError"></span>
                                                        </div>

                                                        <h4 class="card-title mb-1 mt-4 text-primary">Address</h4>

                                                        {{-- Address & Pin code --}}
                                                        <div class="col-12">
                                                            <div class="row">
                                                                <div class="col-6 col-sm-6 col-md-8 col-lg-8 col-xl-8">
                                                                    <label class="form-label mb-0 mt-2"
                                                                        for="permanentSearchInput">Permanent Address
                                                                        <span class="text-danger">*</span></label>
                                                                    <input class="form-control" type="text"
                                                                        id="permanentSearchInput"
                                                                        name="emp_permanent_address"
                                                                        placeholder="Search Your location"
                                                                        onkeyup="return validateFields('permanentSearchInput')">
                                                                    <span class="text-danger"
                                                                        id="permanentSearchInputError"></span>
                                                                </div>

                                                                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                                                    <label for="permanentPinCode"
                                                                        class="form-label mb-0 mt-2"
                                                                        for="permanentPinCode">Zip Code <span
                                                                            class="text-danger">*</span></label>
                                                                    <input class="form-control" type="text"
                                                                        id="permanentPinCode"
                                                                        onkeyup="return validateFields('permanentPinCode')"
                                                                        name="emp_permanent_pin_code"
                                                                        oninput="validatePositiveNumber(this)"
                                                                        maxlength="6"
                                                                        onkeypress="numericOnly(event)"
                                                                        placeholder="Zip Code">
                                                                    <span class="text-danger"
                                                                        id="permanentPinCodeError"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                                {{-- BUTTONS --}}
                                                <div class="d-flex justify-content-end pt-2">
                                                    <button class="btn btn-outline-primary next-tab">Next</button>
                                                </div>
                                            </form>
                                            {{-- About Form END --}}

                                        </div>
                                        {{-- About Tab END --}}

                                        {{-- Organization Tab START --}}
                                        <div class="tab-pane " id="organization">

                                            {{-- Organization Form START --}}
                                            <form method="post" id="organization-form">
                                                <div class="form-group">
                                                    <h4 class="card-title mb-1 text-primary pt-4">Organization
                                                        Information</h4>

                                                    <div class="row">
                                                        {{-- Branch --}}
                                                        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                                            <label for="branch"
                                                                class="form-label mb-0 mt-2">Branch <span
                                                                    class="text-danger">*</span></label>
                                                            <select id="branch" name="emp_br_id"
                                                                class="form-control form-select select2"
                                                                data-placeholder="Select Branch">
                                                                <option class="text-muted" value=""
                                                                    label="Select Branch"
                                                                    onchange="return validateFields('branch')">
                                                                </option>
                                                                @foreach ($BranchList as $branchlist)
                                                                <option value="{{ $branchlist->br_id }}">
                                                                    {{ $branchlist->br_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger" id="branchError"></span>
                                                        </div>

                                                        {{-- Department --}}
                                                        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                                            <label for="department"
                                                                class="form-label mb-0 mt-2">Department <span
                                                                    class="text-danger">*</span></label>
                                                            <select id="department" name="emp_d_id"
                                                                class="form-select form-control select2"
                                                                data-placeholder="Select Department"
                                                                onchange="return validateFields('department')">
                                                                <option class="text-muted" value=""
                                                                    label="Select Deparment">
                                                                </option>
                                                                @foreach ($DepartmentList as $department)
                                                                <option value="{{ $department->d_id }}">
                                                                    {{ $department->d_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger" id="departmentError"></span>
                                                        </div>

                                                        {{-- Designation --}}
                                                        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                                            <label for="designation" class="form-label mb-0 mt-2"
                                                                for="designation">Designation <span
                                                                    class="text-danger">*</span></label>
                                                            <select id="designation" name="emp_dg_id"
                                                                class="form-control form-select select2"
                                                                data-placeholder="Select Designation"
                                                                onchange="return validateFields('designation')">
                                                                <option class="text-muted" value=""
                                                                    label="Select Designation">
                                                                </option>
                                                                @foreach ($DesignationList as $designation)
                                                                <option value="{{ $designation->dg_id }}">
                                                                    {{ $designation->dg_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger" id="designationError"></span>
                                                        </div>

                                                        {{-- Grade --}}
                                                        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-4">
                                                            <label class="form-label mb-0 mt-2"
                                                                for="gradeTADA">Grade <span
                                                                    class="text-danger">*</span></label>
                                                            <select id="gradeTADA" name="emp_grade_id"
                                                                class="form-control form-select select2"
                                                                data-placeholder="Select Grade"
                                                                onchange="return validateFields('gradeTADA')">
                                                                <option class="text-muted" value=""
                                                                    label="Select Grade">
                                                                </option>
                                                                @foreach ($Grade as $grade)
                                                                <option value="{{ $grade->g_id }}">
                                                                    {{ $grade->g_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger" id="gradeTADAError"></span>
                                                        </div>

                                                        {{-- Role --}}
                                                        <div class="col-md-4">
                                                            <label for="role" class="form-label mb-0 mt-2"
                                                                for="role">Role <span
                                                                    class="text-danger">*</span></label>
                                                            <select id="role" name="emp_role_id"
                                                                class="form-control form-select select2"
                                                                data-placeholder="Select Role"
                                                                onchange="return validateFields('role')">
                                                                <option class="text-muted" value=""
                                                                    label="Select Role">
                                                                </option>
                                                                @foreach ($Role as $roleitem)
                                                                <option value="{{ $roleitem->role_id }}">
                                                                    {{ $roleitem->role_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger" id="roleError"></span>
                                                        </div>

                                                        {{-- Reporting Manager --}}
                                                        <div class="col-md-4">
                                                            <label for="reporting_manager"
                                                                class="form-label mb-0 mt-2">Reporting Manager <span
                                                                    class="text-danger">*</span></label>
                                                            <select id="reporting_manager"
                                                                name="emp_reporting_manager_id"
                                                                class="form-control form-select select2"
                                                                data-placeholder="Select Reporting Manager"
                                                                onchange="return validateFields('reporting_manager')">
                                                                <option class="text-muted" value=""
                                                                    label="Select Reporting Manager"></option>
                                                                @foreach ($supervisor as $roleitem)
                                                                <option value="{{ $roleitem->emp_id }}">
                                                                    {{ isset($roleitem->emp_code) ? '(' . $roleitem->emp_code . ')' : '' }}
                                                                    {{ $roleitem->emp_full_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger"
                                                                id="reportManagerError"></span>
                                                        </div>
                                                    </div>

                                                </div>

                                                {{-- BUTTONS --}}
                                                <div class="d-flex justify-content-between pt-2">
                                                    <button
                                                        class="btn btn-outline-secondary previous-tab">Previous</button>
                                                    <button class="btn btn-outline-primary next-tab">Next</button>
                                                </div>

                                            </form>
                                            {{-- Organization Form END --}}

                                        </div>
                                        {{-- Organization Tab END --}}

                                        {{-- Attendance Details Tab START --}}
                                        <div class="tab-pane " id="attendanceLeave">

                                            {{-- Attendance Details Form START --}}
                                            <form method="post" id="attendance-form">

                                                <div class="form-group">
                                                    <h4 class="card-title mb-1 text-primary pt-4">Attendance
                                                        Information</h4>

                                                    <div class="row">
                                                        {{-- Policy --}}
                                                        <div class="col-md-4">
                                                            <label for="attendancePolicy"
                                                                class="form-label mb-0 mt-2">Assign Policy<span
                                                                    class="text-danger">*</span></label>
                                                            <select id="attendancePolicy" name="emp_ap_id"
                                                                class="form-control form-select select2"
                                                                data-placeholder="Select Attendance Policy"
                                                                onchange="return validateFields('attendancePolicy')">
                                                                <option class="text-muted" value=""
                                                                    label="Select Attendance Policy"></option>
                                                                @foreach ($attendancePolicy as $apolicy)
                                                                <option value="{{ $apolicy->ap_id }}"
                                                                    data-checkMethod="">
                                                                    {{ $apolicy->ap_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger"
                                                                id="attendancePolicyError"></span>
                                                        </div>

                                                        {{-- Check in Method --}}
                                                        <div class="col-md-6">
                                                            <div class="row">
                                                                <label class="form-label mb-0 mt-2">Assign Check In
                                                                    Method <span class="text-danger">*</span></label>
                                                                <div class="col">
                                                                    @foreach ($checkInMethod as $index => $method)
                                                                    <label
                                                                        class="custom-control custom-checkbox d-inline-block me-3"
                                                                        for="checkInMethodID{{ $index + 1 }}">
                                                                        <input type="checkbox"
                                                                            class="custom-control-input"
                                                                            master="{{ $method->m_id }}"
                                                                            name="checkInMethod[]"
                                                                            value="{{ $method->m_id }}"
                                                                            id="checkInMethodID{{ $index + 1 }}"
                                                                            onchange="return validateFields('checkInMethodID{{ $index + 1 }}')">
                                                                        <span
                                                                            class="custom-control-label"></span>{{ $method->m_name }}
                                                                    </label>
                                                                    @endforeach
                                                                </div>
                                                                <span class="text-danger"
                                                                    id="checkInMethodError"></span>
                                                            </div>
                                                        </div>

                                                        {{-- Assign Shift --}}
                                                        <div class="col-md-4">
                                                            <label for="asignShift"
                                                                class="form-label mb-0 mt-2">Assign Shift <span
                                                                    class="text-danger">*</span></label>
                                                            <select id="asignShift" name="emp_shift_type_id"
                                                                class="form-control form-select select2"
                                                                data-placeholder="Select Shift Type"
                                                                onchange="return validateFields('asignShift')">
                                                                <option class="text-muted" value=""
                                                                    label="Select Shift Type">
                                                                </option>
                                                                @foreach ($ShiftType as $shift)
                                                                <option value="{{ $shift->pst_id }}">
                                                                    {{ $shift->pst_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger" id="asignShiftError"></span>
                                                        </div>

                                                        {{-- Assign Mode --}}
                                                        <div class="col-md-4">
                                                            <label for="attendanceMethod"
                                                                class="form-label mb-0 mt-2">Assign Mode <span
                                                                    class="text-danger">*</span></label>
                                                            <select name="emp_work_mode_id"
                                                                class="form-control form-select select2 custom-select"
                                                                id="attendanceMethod" data-placeholder="Select Mode"
                                                                onchange="checkInMethodCheckbox(this); return validateFields('attendanceMethod')">
                                                                <option class="text-muted" value=""
                                                                    label="Select Attendance Method"></option>
                                                                @foreach ($attendanceMethod as $key => $value)
                                                                <option value="{{ $key }}">
                                                                    {{ $value }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger"
                                                                id="attendanceMethodError"></span>
                                                        </div>

                                                        {{-- Geofencing --}}
                                                        <div class="col-md-4">
                                                            <label for="geofencingId"
                                                                class="form-label mb-0 mt-2">Geofencing <span
                                                                    class="text-danger">*</span></label>
                                                            <select id="geofencingId"
                                                                name="emp_is_geofencing_active"
                                                                class="form-control form-select select2"
                                                                data-placeholder="Select Geofencing"
                                                                onchange="return validateFields('geofencingId')">
                                                                <option class="text-muted" value=""
                                                                    label="Select Geofencing">
                                                                </option>
                                                                @foreach (['1' => 'Active', '0' => 'Inactive'] as $key => $item)
                                                                <option value="{{ $key }}">
                                                                    {{ $item }}
                                                                </option>
                                                                @endforeach

                                                            </select>
                                                            <span class="text-danger" id="geofencingIdError"></span>
                                                        </div>

                                                        {{-- Weekoff --}}
                                                        <div class="col-md-4">
                                                            <label for="weekOffId"
                                                                class="form-label mb-0 mt-2">Weekoff<span
                                                                    class="text-danger">*</span></label>
                                                            <select id="weekOffId" name="emp_pwo_id"
                                                                class="form-control form-select select2"
                                                                data-placeholder="Select Week off"
                                                                onchange="return validateFields('weekOffId')">
                                                                <option class="text-muted" value=""
                                                                    label="Select Week off">
                                                                </option>
                                                                @foreach ($weekOffs as $key => $wof)
                                                                <option value="{{ $key }}">
                                                                    {{ $wof }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger" id="weekOffIdError"></span>
                                                        </div>

                                                        {{-- Geo Work --}}
                                                        <div class="col-md-4">
                                                            <label for="geoworkId"
                                                                class="form-label mb-0 mt-2">GeoWork <span
                                                                    class="text-danger">*</span></label>
                                                            <select id="geoworkId" name="emp_is_geowork_active"
                                                                class="form-control form-select select2"
                                                                data-placeholder="Select GeoWork"
                                                                onchange="return validateFields('geoworkId')">
                                                                <option class="text-muted" value=""
                                                                    label="Select GeoWork"></option>
                                                                @foreach (['1' => 'Active', '0' => 'Inactive'] as $key => $item)
                                                                <option value="{{ $key }}">
                                                                    {{ $item }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger" id="geoworkIdError"></span>
                                                        </div>

                                                    </div>

                                                </div>

                                                <div class="form-group">
                                                    <h4 class="card-title mb-1 text-primary">Leave Information</h4>

                                                    <div class="row">

                                                        {{-- Leave Policy --}}
                                                        <div class="col-md-4">
                                                            <label for="leavePolicy"
                                                                class="form-label mb-0 mt-2">Leave Assign Policy <span
                                                                    class="text-danger">*</span></label>
                                                            <select
                                                                class="form-control form-select select2 custom-select"
                                                                name="emp_pl_id" id="leavePolicy"
                                                                data-placeholder="Select Leave Policy"
                                                                onchange="return validateFields('leavePolicy')">
                                                                <option class="text-muted" value=""
                                                                    label="Select Leave Policy">
                                                                </option>
                                                                @foreach ($leavePolicy as $policy)
                                                                <option value="{{ $policy->pl_id }}">
                                                                    {{ $policy->pl_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger" id="leavePolicyError"></span>
                                                        </div>

                                                        {{-- Leave Credit --}}
                                                        <div class="col-md-4">
                                                            <label for="joiningLeave"
                                                                class="form-label mb-0 mt-2">Leave credit on
                                                                pro-rata<span class="text-danger">*</span></label>
                                                            <select
                                                                class="form-control form-select select2 custom-select"
                                                                id="joiningLeave"
                                                                data-placeholder="Select Joining Leave"
                                                                name="emp_allow_joining_leave"
                                                                onchange="toggleJoiningLeaveMethod(this.value); return validateFields('joiningLeave')">
                                                                <option class="text-muted" value=""
                                                                    label="Select Joining Leave"></option>
                                                                <option value="1">Allowed</option>
                                                                <option value="0">Not Allowed</option>
                                                            </select>
                                                            <span class="text-danger" id="joiningLeaveError"></span>
                                                        </div>

                                                        {{-- Leave Calculation --}}
                                                        <div class="col-md-4" id="calculationMethodDiv"
                                                            style="display: none">
                                                            <label for="calculationMethod"
                                                                class="form-label mb-0 mt-2"> Joining Leave
                                                                Calculation Method <span
                                                                    class="text-danger">*</span></label>
                                                            <select
                                                                class="form-control form-select select2 custom-select"
                                                                id="calculationMethod"
                                                                data-placeholder="Select Joining Method"
                                                                name="emp_joining_leave_calc_type"
                                                                onchange="toggleDateInput(this.value); return validateFields('calculationMethod')">
                                                                <option class="text-muted" value=""
                                                                    label="Select Calculation Method"></option>
                                                                @foreach ($leaveCalcBy as $method)
                                                                <option value="{{ $method->m_id }}">
                                                                    {{ $method->m_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger"
                                                                id="calculationMethodError"></span>
                                                        </div>

                                                        {{-- Leave Apply Date --}}
                                                        <div class="col-md-4" id="dateInputDiv"
                                                            style="display: none;">
                                                            <label for="applicableDate"
                                                                class="form-label mb-0 mt-2">Leave Applicable Date
                                                                (1-29) <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control"
                                                                id="applicableDate"
                                                                name="emp_joining_leave_before_date" min="1"
                                                                max="29" placeholder="Enter date (1-29)"
                                                                oninput="validateDateInput(this);" />
                                                            <span class="text-danger"
                                                                id="applicableDateError"></span>
                                                        </div>

                                                        {{-- Probation Leave --}}
                                                        <div class="col-md-4">
                                                            <label for="probationLeave"
                                                                class="form-label mb-0 mt-2"> Probation leave on
                                                                pro-rata <span class="text-danger">*</span></label>
                                                            <select
                                                                class="form-control form-select select2 custom-select"
                                                                onchange="return validateFields('probationLeave')"
                                                                id="probationLeave" name="emp_allow_probation_leave"
                                                                data-placeholder="Select Probation Leave">
                                                                <option class="text-muted" value=""
                                                                    label="Select Probation Leave"></option>
                                                                <option value="1">Allowed</option>
                                                                <option value="0">Not Allowed</option>
                                                            </select>
                                                            <span class="text-danger"
                                                                id="probationLeaveError"></span>
                                                        </div>

                                                        {{-- Input Toggler Script --}}
                                                        <script>
                                                            document.addEventListener("DOMContentLoaded", function() {
                                                                const joiningLeaveValue = document.getElementById("joiningLeave").value;
                                                                toggleJoiningLeaveMethod(joiningLeaveValue);
                                                            });

                                                            function toggleJoiningLeaveMethod(value) {
                                                                const calculationMethodDiv = document.getElementById("calculationMethodDiv");
                                                                document.getElementById("calculationMethodError").textContent = '';

                                                                const dateInputDiv = document.getElementById("dateInputDiv");
                                                                const applicableDate = document.getElementById("applicableDate");
                                                                if (calculationMethodDiv) {
                                                                    calculationMethodDiv.style.display = (value === "1") ? "block" : "none";
                                                                }

                                                                const calculationMethod = document.getElementById("calculationMethod").value;
                                                                let applicableDateVal = applicableDate ? applicableDate : '';

                                                                if (dateInputDiv) {
                                                                    if (value === "1" && calculationMethod === "366") {
                                                                        dateInputDiv.style.display = "block";
                                                                        $('#applicableDate').attr("required", true);
                                                                    } else {
                                                                        $('#applicableDate').val(applicableDateVal);
                                                                        dateInputDiv.style.display = "none";
                                                                        $('#applicableDate').removeAttr("required");
                                                                    }
                                                                }
                                                            }

                                                            function toggleDateInput(selectedValue) {
                                                                const dateInputDiv = document.getElementById("dateInputDiv");
                                                                dateInputDiv.style.display = (selectedValue === "366") ? "block" : "none";
                                                            }

                                                            function validateDateInput(input) {
                                                                let value = parseInt(input.value, 10);
                                                                if (value < 1) input.value = 1;
                                                                else if (value > 29) input.value = 29;
                                                                else input.value = value.toString().slice(0, 2);
                                                            }
                                                        </script>
                                                    </div>
                                                </div>

                                                {{-- BUTTONS --}}
                                                <div class="d-flex justify-content-between pt-2">
                                                    <button
                                                        class="btn btn-outline-secondary previous-tab">Previous</button>
                                                    <button class="btn btn-outline-primary next-tab">Next</button>
                                                </div>

                                            </form>
                                            {{-- Attendance Details Form END --}}

                                        </div>
                                        {{-- Attendance Details Tab END --}}

                                        {{-- Joining Tab START --}}
                                        <div class="tab-pane " id="joining">

                                            {{-- Joining Form START --}}
                                            <form method="post" id="joinig-form">

                                                <div class="form-group">
                                                    <h4 class="card-title mb-1  text-primary">Joining Details</h4>

                                                    <div class="row">

                                                        {{-- Emp Status --}}
                                                        <div class="col-md-4">
                                                            <label class="form-label mb-0 mt-2"
                                                                for="status">Status <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-control form-select select2"
                                                                id="status" aria-label="Type" name="emp_status"
                                                                data-placeholder="Select Status" required
                                                                onchange="return validateFields('status')">
                                                                <option class="text-muted" value=""
                                                                    label="Select Employee Staus">
                                                                </option>
                                                                @foreach ($emp_status as $item)
                                                                <option value="{{ $item->m_id }}">
                                                                    {{ $item->m_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger" id="statusError"></span>
                                                        </div>

                                                        {{-- Emp Type --}}
                                                        <div class="col-md-4">
                                                            <label class="form-label mb-0 mt-2"
                                                                id="contractType">Employee Type <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-control form-select select2"
                                                                id="contractType" aria-label="Type"
                                                                name="emp_type_id"
                                                                data-placeholder="Select Employee Type" required
                                                                onchange="return validateFields('contractType')">
                                                                <option class="text-muted" value=""
                                                                    label="Select Contract Type">
                                                                </option>
                                                                @foreach ($employeeType as $empType)
                                                                <option value="{{ $empType->m_id }}">
                                                                    {{ $empType->m_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger" id="contractTypeError"></span>
                                                        </div>

                                                        {{-- DoJ --}}
                                                        <div class="col-md-4">
                                                            <label class="form-label mb-0 mt-2"
                                                                for="dateOfJoinError">Date Of Joining <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="date" max="2099-12-31"
                                                                class="form-control" name="emp_date_of_joining"
                                                                placeholder="DD-MM-YYY" id="dateOfJoin" required
                                                                onkeyup="return validateFields('dateOfJoinError')">
                                                            <span class="text-danger" id="dateOfJoinError"></span>
                                                        </div>

                                                        {{-- Job Status --}}
                                                        <div class="col-md-4">
                                                            <label for="employeeJobStatus"
                                                                class="form-label mb-0 mt-2">Job Status <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-control form-select select2"
                                                                id="employeeJobStatus" name="emp_job_status"
                                                                data-placeholder="Select Job Staus" required
                                                                onchange="return validateFields('employeeJobStatus')">
                                                                <option value="" selected
                                                                    label="Select Job Status"></option>
                                                                @foreach ($employeeJobStatus as $item)
                                                                <option value="{{ $item->m_id }}">
                                                                    {{ $item->m_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <span class="text-danger"
                                                                id="employeeJobStatusError"></span>
                                                        </div>

                                                    </div>

                                                </div>

                                                {{-- BUTTONS --}}
                                                <div class="d-flex justify-content-between pt-2">
                                                    <button
                                                        class="btn btn-outline-secondary previous-tab">Previous</button>
                                                    <button class="btn btn-outline-primary"
                                                        id="qAddEmpBTn">Save</button>
                                                </div>

                                            </form>
                                            {{-- Joining Form END --}}

                                        </div>
                                        {{-- Joining Tab END --}}

                                    </div>
                                </div>
                                {{-- Tab Panel Body END --}}

                            </div>

                        </div>
                        {{-- MODAL BODY END --}}

                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Modal for Quick Add Employee --}}

    <!-- Project Assign Modal -->

    <!-- Project Assign Modal -->
    <div class="modal" id="projectAssignModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="projectAssignForm">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">Assign Projects</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <label>Select Projects</label>
                        <select class="form-control form-select select2" id="emp_project_assigned"
                            name="emp_project_id[]" data-placeholder="Select one or more projects" multiple
                            required>
                            @foreach ($emp_projects as $project)
                            <option value="{{ $project->ps_id }}">{{ $project->ps_name }}</option>
                            @endforeach
                        </select>
                        <span id="emp_project_assigned_error"></span>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


{{-- for employee validation  --}}
<script>
    const canAddEmployee = @json($canAddEmployee ?? false);
    const limitMessage  = @json($limitMessage ?? 'Employee limit exceeded. Please upgrade your plan.');

    document.addEventListener('DOMContentLoaded', function () {

        // -------- QUICK ADD EMPLOYEE (MODAL) --------
        document.querySelectorAll('[data-bs-target="#quickAddEmployee"]').forEach(btn => {

            const originalToggle = btn.getAttribute('data-bs-toggle');

            // 🔴 BLOCK MODAL IMMEDIATELY ON LOAD
            if (!canAddEmployee) {
                btn.removeAttribute('data-bs-toggle');
            }

            btn.addEventListener('click', function (e) {

                if (!canAddEmployee) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Action not allowed',
                        text: limitMessage,
                        confirmButtonText: 'OK'
                    });

                    return false;
                }

                // 🟢 Restore only if allowed
                btn.setAttribute('data-bs-toggle', originalToggle);
            }, true);
        });


        // -------- ADD NEW EMPLOYEE (PAGE REDIRECT) --------
        document.querySelectorAll('a[href="{{ route('employee.form') }}"]').forEach(btn => {
            btn.addEventListener('click', function (e) {
                if (!canAddEmployee) {
                    e.preventDefault();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Action not allowed',
                        text: limitMessage,
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const downloadBtn = document.getElementById('downloadSampleBtn');
        const routeTemplateInput = document.getElementById('sampleRouteTemplate');
        const fieldSelect = document.getElementById('updateField2');

        if (downloadBtn && routeTemplateInput && fieldSelect) {
            downloadBtn.addEventListener('click', function() {
                const selectedField = fieldSelect.value;

                if (!selectedField) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Field Not Selected',
                        text: 'Please select a field to download the sample file.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                const routeTemplate = routeTemplateInput.value;
                const url = routeTemplate.replace(':type', selectedField);

                window.location.href = url;
            });
        }
    });
</script>

{{-- For Name Prefix to change gender value  --}}
<script>
    $(document).ready(function() {

        // Init Select2
        $('#gender').select2();
        $('#prefix').select2();

        // Prefix change event
        $('#prefix').on('change', function() {
            namePrefix(this);
        });

        // Page load par already selected prefix ke liye
        setTimeout(() => {
            const prefixVal = $('#prefix').val();
            if (prefixVal) {
                namePrefix($('#prefix'));
            }
        }, 100);
    });

    function namePrefix(prefixSelect) {
        const genderSelect = $('#gender');
        const prefixId = $(prefixSelect).val();

        const maleGenderId = "33";
        const femaleGenderId = "34";
        const neutralGenderId = "35";

        const prefixToGender = {
            "95": maleGenderId, // Mr
            "96": femaleGenderId, // Mrs
            "98": femaleGenderId, // Miss
            "413": femaleGenderId // Ms
        };

        const genderValue = prefixToGender[prefixId] || neutralGenderId;

        genderSelect.val(genderValue).trigger('change');
    }
</script>

<script>
    function dateFormat(dateString) {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    }

    function checkObject(obj) {
        return obj == null || Object.keys(obj).length === 0;
    }

    $(document).on('click', '.openBtn', function() {
        const encId = $(this).data('encid');

        $.ajax({
            type: "POST",
            url: "{{ route('addEmp.getEmployeeData') }}",
            _token: "{{ csrf_token() }}",
            data: {
                emp_id: $(this).data('id')
            },
            success: function(response) {
                console.log(response);
                response = response.data;
                editUrl = "{{ route('update.employee', ':id') }}".replace(':id', encId);
                downloadUrl = "{{ route('employee.download.profile', ':id') }}".replace(':id', response.emp_id);
                $('#employeeModal').modal('show');
                $('#vm-editProfileBtn').attr('href', editUrl);
                $('#vm-downloadProfileBtn').attr('href', downloadUrl);
                if (response.emp_profile_photo && response.emp_profile_photo.length > 0) {
                    $('#vm-empAvtar').css('background-image', `url('${response.emp_profile_photo}')`);
                } else {
                    $('#vm-empAvtar').css('background-image', `url('{{ asset("assets/imgs/user.png") }}')`);
                }
                $('.vm-empName').text(response.emp_full_name);
                $('.vm-empCode').text(response.emp_code);
                $('.vm-empDesignation').text(checkObject(response.fh_designation) ? "-" : response.fh_designation.dg_name);
                $('.vm-empDepartment').text(checkObject(response.fh_department) ? "-" : response.fh_department.d_name);

                $('#vm-empStatus').text(checkObject(response.fh_employee_status) ? "-" : response.fh_employee_status.m_name);
                checkObject(response.fh_employee_status) ? "-" : response.fh_employee_status.m_id == 71 ? $('#vm-empStatus').removeClass().addClass('badge-active') : $('#vm-empStatus').removeClass().addClass('badge-inactive');

                $('.vm-empGender').text(checkObject(response.fh_gender) ? "-" : response.fh_gender.m_name);
                $('.vm-empDob').text(dateFormat(response.emp_dob ?? "-"));
                $('.vm-empDOJ').text(dateFormat(response.emp_date_of_joining ?? "-"));
                $('.vm-empType').text(checkObject(response.fh_employee_type) ? "-" : response.fh_employee_type.m_name);
                $('.vm-empGrade').text(checkObject(response.fh_grade) ? "-" : response.fh_grade.g_name);
                $('.vm-empBranch').text(checkObject(response.fh_branch) ? "-" : response.fh_branch.br_name);
                $('.vm-empAssignMode').text(checkObject(response.fh_work_mode) ? "-" : response.fh_work_mode.m_name);

                $('.vm-empMaritalStatus').text(checkObject(response.fh_marital_status) ? "-" : response.fh_marital_status.m_name);
                $('.vm-empPersonalMobile').text(response.emp_phone ?? "-");
                $('.vm-empPersonalEmail').text(response.emp_email ?? "-");
                $('.vm-empOfficialMobile').text(response.emp_official_contact ?? "-");
                $('.vm-empOfficialEmail').text(response.emp_official_email ?? "-");
                $('.vm-empEmergencyContactNumber').text(response.emp_emergency_contact ?? "-");
                $('.vm-empEmergencyRelation').text(response.emp_emergency_relation ?? "-");
                $('.vm-empNationality').text(response.emp_nationality ?? "-");
                $('.vm-empReligion').text(response.emp_religion ?? "-");
                $('.vm-empCast').text(checkObject(response.fh_cast_category) ? "-" : response.fh_cast_category.m_name);
                $('.vm-empBloodGroup').text(checkObject(response.fh_blood_group) ? "-" : response.fh_blood_group.m_name);
                $('.vm-empIdentification').text(response.emp_body_mark ?? "-");
                $('.vm-empPerPinCode').text(response.emp_permanent_pin_code ?? "-");
                $('.vm-empTempPinCode').text(response.emp_temporary_pin_code ?? "-");
                $('.vm-empTemporaryAddress').text(response.emp_is_temporary_add_same ? response.emp_permanent_address : response.emp_temporary_address ?? "-");
                $('.vm-empPermanentAddress').text(response.emp_permanent_address ?? "-");

                $('.vm-empRole').text(checkObject(response.fh_role) ? "-" : response.fh_role.role_name);
                $('.vm-empReportingManager').text(checkObject(response.fh_reporting_manager_id) ? "-" : response.fh_reporting_manager_id.emp_full_name);
                $('.vm-empBudgetCode').text(response.emp_budget_code ?? "-");
                $('.vm-empProfitCenter').text(response.emp_profit_center ?? "-");
                $('.vm-empAssignedRegion').text(checkObject(response.fh_region) ? "-" : response.fh_region.m_name);
                $('.vm-empAssignedProject').text(checkObject(response.project_names) ? "-" : response.project_names.join(', ') ?? "-");

                $('.vm-empAttendancePolicy').text(checkObject(response.fh_attendance_policy) ? "-" : response.fh_attendance_policy.ap_name);
                $('.vm-empShiftPolicy').text(checkObject(response.fh_shift_type) ? "-" : response.fh_shift_type.pst_name);
                $('.vm-empCheckInMethod').text(checkObject(response.checkin_method_names) ? "-" : response.checkin_method_names.join(', ') ?? "-");
                $('.vm-empWorkMode').text(checkObject(response.fh_work_mode) ? "-" : response.fh_work_mode.m_name);
                $('.vm-empGeoFencing').text(checkObject(response.fh_geofencing) ? "-" : response.fh_geofencing.m_name);
                $('.vm-empGeoWork').text(response.emp_is_geowork_active ? 'Active' : 'Inactive');
                $('.vm-empWeeklyPolicy').text(checkObject(response.fh_week_off_policy2) ? "-" : response.fh_week_off_policy2.pwo_name);
                $('.vm-empOfflineSync').text(response.emp_is_offline_sync ? 'Enabled' : 'Disabled');
                $('.vm-empLeavePolicy').text(checkObject(response.fh_policy_leave) ? "-" : response.fh_policy_leave.pl_name);
                $('.vm-empLeaveCreditProRata').text(response.emp_allow_joining_leave ? 'Allowed' : 'Not Allowed');
                $('.vm-empJoiningLeaveCalculation').text(checkObject(response.fh_leave_calculation_type) ? "-" : response.fh_leave_calculation_type.m_name);
                response.fh_leave_calculation_type.m_id == 365 ? $('.vm-empLeaveApplicableDate').parent().parent().hide() : $('.vm-empLeaveApplicableDate').parent().parent().show();
                $('.vm-empLeaveApplicableDate').text(response.emp_joining_leave_before_date ?? '-');
                $('.vm-empProbationLeaveProRata').text(response.emp_allow_probation_leave ? 'Allowed' : 'Not Allowed');

                $('.vm-empTADAPolicy').text(checkObject(response.tadaPolicy) ? "-" : response.tadaPolicy);
                $('.vm-empAllowedLateComings').text(response.allowedLateComings ?? "-");
                $('.vm-empAllowedEarlyGoings').text(response.allowedEarlyGoings ?? "-");
                $('.vm-empAllowedGatePasses').text(response.allowedGatePasses ?? "-");
                $('.vm-empAllowedMSP').text(response.allowedMSP ?? "-");
                $('.vm-empPFEnabled').text(checkObject(response.fh_pf_master) ? "-" : response.fh_pf_master.m_name);
            }
        });
    });

    $(document).ready(function() {
        /*$('#emp_img_bulk_upload form').on('submit', function(e) {
            e.preventDefault(); // Default form submission ko rokta hai
            console.log("AJAX form submission triggered");

            var form = this;
            var formData = new FormData(form);

            $.ajax({
                url: $(form).attr('action'),
                type: $(form).attr('method'),
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log("Server response:", response);
                    let successMessage = response.success || 'Upload completed.';
                    let errorMessages = response.errors || [];

                    if (response.success) {
                        $('#emp_img_bulk_upload').modal('hide');
                        form.reset();
                        $('#previewContainer').html('');

                        const errorHtml = errorMessages.length > 0 ?
                            `<hr><strong>Filename should be businesscode_empcode</strong><ul style="text-align:left;">${errorMessages.map(e => `<li>${e}</li>`).join('')}</ul>` :
                            '';

                        if (response.successCount > 0) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                html: `<p>${successMessage}</p>${errorHtml}`
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Employee images not uploaded',
                                html: errorHtml
                            }).then(() => {
                                location.reload();
                            });
                        }
                    }
                },
                error: function(xhr) {
                    console.error("AJAX Error:", xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Failed!',
                        text: 'Something went wrong.'
                    });
                }
            });
        });*/

        const CHUNK_SIZE = 20;
        $('#emp_img_bulk_upload form').on('submit', function(e) {
            e.preventDefault();

            const form = this;
            const files = $('#imageInput')[0].files;

            if (!files.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No files selected'
                });
                return;
            }

            const totalChunks = Math.ceil(files.length / CHUNK_SIZE);
            let uploadedCount = 0;
            let allErrors = [];

            function uploadChunk(chunkIndex) {
                const chunk = Array.from(files).slice(chunkIndex * CHUNK_SIZE, (chunkIndex + 1) *
                    CHUNK_SIZE);
                const formData = new FormData();
                formData.append('_token', $('input[name="_token"]').val());
                formData.append('POST_TYPE', 'EMP_BULK_UPLOAD');

                chunk.forEach(file => formData.append('images[]', file));

                $.ajax({
                    url: $(form).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        uploadedCount += response.successCount || 0;
                        if (response.errors && response.errors.length) {
                            allErrors = allErrors.concat(response.errors);
                        }

                        if (chunkIndex + 1 < totalChunks) {
                            uploadChunk(chunkIndex + 1); // Upload next chunk
                        } else {
                            // All chunks uploaded
                            $('#emp_img_bulk_upload').modal('hide');
                            form.reset();
                            $('#previewContainer').html('');

                            const errorHtml = allErrors.length > 0 ?
                                `<hr><strong>Filename should be businesscode_empcode</strong><ul style="text-align:left;">${allErrors.map(e => `<li>${e}</li>`).join('')}</ul>` :
                                '';

                            if (uploadedCount > 0) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Upload Complete',
                                    html: `<p>${uploadedCount} images uploaded.</p>${errorHtml}`
                                }).then(() => location.reload());
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'No images uploaded',
                                    html: errorHtml
                                }).then(() => location.reload());
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error("AJAX Error:", xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Upload Failed!',
                            text: 'Something went wrong.'
                        });
                    }
                });
            }

            // Start uploading first chunk
            uploadChunk(0);
        });
    });


    document.addEventListener('DOMContentLoaded', function() {
        // Success message
        @if(session('success'))
        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: '{{ session('
            success ') }}',
            toast: true,
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            customClass: {
                toast: 'swal2-toast-green-glow'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        @endif

        // Error message
        @if(session('error'))
        Swal.fire({
            position: 'top-end',
            icon: 'error',
            title: '{{ session('
            error ') }}',
            toast: true,
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        @endif

        // Error messages (if multiple validation errors or custom messages are passed)
        @if($errors -> any())
        let errorMessages = '';
        @foreach($errors -> all() as $error)
        errorMessages += '{{ $error }}' + '<br>';
        @endforeach
        Swal.fire({
            position: 'top-end',
            icon: 'error',
            title: 'Validation Errors',
            html: errorMessages, // Display the list of errors
            toast: true,
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        @endif
    });

    // Change input field based on selected field
    $(document).on('change', '#updateField', function() {
        let selectedOption = $(this).find('option:selected');
        let selectedFieldVal = selectedOption.val();
        let selectedFieldType = selectedOption.data('type');
        let inputContainer = $("#updateInputContainer");


        if (selectedFieldType == 'select') {
            $.ajax({
                url: "{{ route('get.field.options') }}", // Replace with your actual route
                method: 'GET',
                data: {
                    field: selectedFieldVal
                },
                success: function(response) {
                    let optionsHtml = '';
                    $.each(response, function(key, value) {
                        console.log('option', key, value);
                        optionsHtml += `<option value="${key}">${value}</option>`;
                    });


                    inputContainer.html(`
                        <label for="newValue" class="form-label">New Value</label>
                        <select class="form-select" id="newValue" ${selectedFieldVal == 'emp_checkin_method_id' ? 'multiple' : ''}>
                            ${optionsHtml}
                        </select>
                    `);
                },
                error: function() {
                    inputContainer.html(
                        `<p class="text-danger">Failed to load options. Please try again.</p>`);
                }
            });
        } else {
            inputContainer.html(`
                <label for="newValue" class="form-label">New Value</label>
                <input type="text" class="form-control" id="newValue" placeholder="Enter Value">
            `);
        }

    });
</script>

<script>
    var empIDs = [];

    // ✅ Function to update selected checkboxes
    function selectCheckboxUpdate(element) {
        var empId = $(element).val(); // Get actual emp_id
        var isChecked = $(element).is(':checked');

        if (isChecked) {
            if (!empIDs.includes(empId)) {
                empIDs.push(empId);
            }
        } else {
            empIDs = empIDs.filter(id => id != empId);
        }

        // Check/uncheck the "Select All" checkbox
        var allChecked = $('.testClass').length === $('.testClass:checked').length;
        $('#selectAll').prop('checked', allChecked);
    }

    // ✅ Function to select/deselect all checkboxes
    function selectAllCheckboxes(source) {
        var isChecked = $(source).is(':checked');
        empIDs = isChecked ? $('.testClass').map(function() {
            return $(this).val();
        }).get() : [];

        $('.testClass').prop('checked', isChecked);

    }

    $(document).ready(function() {
        // ✅ CSRF token setup for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // ✅ Attach event listeners
        $('.testClass').on('change', function() {
            selectCheckboxUpdate(this);
        });

        $('#selectAll').on('change', function() {
            selectAllCheckboxes(this);
        });

        // ✅ Handle Bulk button click
        $('#bulkButton').on('click', function() {
            if (empIDs.length === 0) {
                Swal.fire({
                    toast: true,
                    position: 'top-end', // ✅ Position at the top-right corner
                    icon: 'error',
                    title: 'Please select at least one employee before proceeding!',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                return; // ✅ Prevent modal from opening
            }

            // ✅ If employees are selected, open the modal
            $('#bulkUpdateModal').modal('show');
        });

        // ✅ Form submission with validation & AJAX request
        $('#updateBulkForm').on('submit', function(e) {
            e.preventDefault();

            if (empIDs.length === 0) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Please select at least one employee!',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                return;
            }

            let selectedField = $('#updateField').val(); // Get the selected field ID
            let newValue = (selectedField == 'emp_checkin_method_id' && Array.isArray($('#newValue')
                .val())) ? $('#newValue').val() : $('#newValue').val().trim();
            // console.log($('#newValue').val().length);
            // return false;
            if (selectedField == 'emp_checkin_method_id' && $('#newValue').val().length == 0) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Please select an option!',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                return;
            } else {
                if (!selectedField || newValue === "") {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'Please select a field and enter a value!',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    return;
                }
            }


            $.ajax({
                url: "{{ route('admin.employee.bulk.update') }}",
                method: "POST",
                data: {
                    empIDs: empIDs, // Array of employee IDs
                    field: selectedField,
                    value: newValue
                },
                dataType: "json",
                beforeSend: function() {
                    $('#updateBulkButton').attr('disabled', true).text('Updating...');
                },
                success: function(response) {
                    $('#updateBulkButton').attr('disabled', false).text('Update');
                    $('#bulkUpdateModal').modal('hide');

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.success,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    $('#updateBulkButton').attr('disabled', false).text('Update');
                    let errorMessage = xhr.responseJSON?.error || 'Something went wrong!';
                    $('#bulkUpdateModal').modal('hide');
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: errorMessage,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            });
        });

        $('#quickAddEmployee').on('shown.bs.modal', function() {
            $('.select2').select2({
                dropdownParent: $('#quickAddEmployee')
            });
        });

        // Wait for DOM to be fully loaded
        $(document).ready(function() {
            // Single 2FA disable functionality
            $(document).on('click', '.disable2faBtn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const employeeId = $(this).data('employee-id');
                const employeeName = $(this).data('employee-name');

                // Check if button is disabled
                if ($(this).prop('disabled')) {
                    return;
                }

                Swal.fire({
                    title: 'Disable 2FA',
                    text: `Are you sure you want to disable 2FA for ${employeeName}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, disable it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Disable the button to prevent double clicks
                        $(this).prop('disabled', true);

                        $.ajax({
                            url: `/2fa/disable/${employeeId}`,
                            method: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr(
                                    'content')
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'success',
                                        title: response.message,
                                        showConfirmButton: false,
                                        timer: 3000,
                                        timerProgressBar: true
                                    }).then(() => {
                                        // Disable the button permanently
                                        $('.disable2faBtn[data-employee-id="' +
                                                employeeId + '"]')
                                            .prop('disabled', true);
                                    });
                                } else {
                                    Swal.fire({
                                        toast: true,
                                        position: 'top-end',
                                        icon: 'error',
                                        title: response.message ||
                                            'Error disabling 2FA',
                                        showConfirmButton: false,
                                        timer: 3000,
                                        timerProgressBar: true
                                    });
                                    // Re-enable the button on error
                                    $('.disable2faBtn[data-employee-id="' +
                                        employeeId + '"]').prop(
                                        'disabled', false);
                                }
                            },
                            error: function(xhr) {
                                let errorMessage =
                                    'Error disabling 2FA. Please try again.';
                                if (xhr.responseJSON && xhr.responseJSON
                                    .message) {
                                    errorMessage = xhr.responseJSON.message;
                                }

                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: errorMessage,
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true
                                });

                                // Re-enable the button on error
                                $('.disable2faBtn[data-employee-id="' +
                                    employeeId + '"]').prop('disabled',
                                    false);
                            }
                        });
                    }
                });
            });
        });
    });
</script>
<script>
    let currentEmployeeId = null;

    function setEmployeeId(empId) {
        currentEmployeeId = empId;

        // Reset select2
        $('#emp_project_assigned').val(null).trigger('change');

        // Fetch existing projects for this employee
        $.ajax({
            url: '/employee/' + empId + '/projects',
            type: 'GET',
            success: function(response) {
                if (response.projects && response.projects.length > 0) {
                    // Preselect existing project ids
                    $('#emp_project_assigned').val(response.projects).trigger('change');
                }
            }
        });
    }

    $(document).ready(function() {
        $('#emp_project_assigned').select2({
            placeholder: "Select one or more projects",
            width: '100%',
            dropdownParent: $('#projectAssignModal')
        });

        // Handle form submit
        $('#projectAssignForm').on('submit', function(e) {
            e.preventDefault();

            let selectedProjects = $('#emp_project_assigned').val();

            $.ajax({
                url: '/employee/update-projects/' + currentEmployeeId,
                type: 'POST',
                data: {
                    emp_project_id: selectedProjects,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#projectAssignModal').modal('hide');
                        alert(response.message);
                        // optionally refresh page/table here
                    }
                },
                error: function(xhr) {
                    $('#emp_project_assigned_error').text('');
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        if (errors.emp_project_id) {
                            $('#emp_project_assigned_error').text(errors.emp_project_id[0]);
                        }
                    } else {
                        alert('Something went wrong!');
                    }
                }
            });
        });
    });
</script>
