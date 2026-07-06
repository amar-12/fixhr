@extends('admin.layout.master')

@section('title', 'Approval Settings')

@section('content')
    <input type="hidden" id="ajaxUrl" value="{{ url('/') }}">
    <input type="hidden" name="approvalModuleId" class="form-control" id="approvalModuleId">

    <!-- PAGE HEADER -->
    <div class="p-0 mb-4">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('/admin/settings/tada-settings/approval-list') }}">Privilege</a></li>
            <li class="active"><span><b>Approval Process</b></span></li>
        </ol>
    </div>

    <div class="page-header d-xl-flex d-block">
        <div class="page-leftheader">
            <div class="page-title">{{ $masterName?->m_name ?? '' }} Approval Process</div>
        </div>
    </div>

    <!-- ROW -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-lg p-4">
                @if ($processModuleDetails)
                    <div class="row p-2 card-header d-flex align-items-center">
                        <!-- Process Details Section -->
                        <div class="card-title  pt-0 px-3 d-flex">
                            <label class="font-weight-semibold mr-3">Process Details: &nbsp;</label>
                            <label class="font-weight-semibold mr-3"><b>Hierarchy Wise</b></label>
                        </div>

                        <!-- Rejection Receiver's Section -->
                        <div class="col-md text-md-end d-flex align-items-center">
                            <label class="font-weight-semibold text-danger mr-3">Rejection Receiver's: &nbsp;</label>
                            <label class="font-weight-semibold mr-3"><b>{{ $rejectionReceivers }}</b></label>
                        </div>
                    </div>

                    <div class="card-body border-top">

                        <!-- Approval Rules Table -->
                        <div class="table-responsive mt-4">
                            <h5 class="card-title text-center text-success">Approval Rules</h5>
                            <table class="table table-bordered table-striped text-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center">Update Field</th>
                                        <th class="text-center">Update Condition</th>
                                        <th class="text-center">Update Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($processModuleDetails?->fh_rule_criteria as $aproval_rule)
                                        <tr>
                                            <td class="text-center">{{ $aproval_rule->fh_approval_rule->m_name }}</td>
                                            <td class="text-center">
                                                {{ $aproval_rule->fh_rule_condition->m_name ? $aproval_rule->fh_rule_condition->m_name : 'custom' }}
                                            </td>
                                            <td class="text-center font-weight-semibold">
                                                {{ isset($aproval_rule->fh_condition_option->m_name) && $aproval_rule->fh_condition_option->m_name ? $aproval_rule->fh_condition_option->m_name : $aproval_rule->rc_custom_value }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive mt-4">
                            <h4 class="card-title text-center text-success">Approval Details</h4>
                            <table id="approvalDetailsTable" class="table table-bordered table-striped text-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th>Approval Sequence</th>
                                        <th>Approval Type</th>
                                        <th>Approver Name</th>
                                        <th>Approver Email</th>
                                        <th>Approver Role</th>
                                        <th>Approver Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($processApproverOptimizedData as $key => $item)
                                         <tr>
                                            <td colspan="6" class="text-center font-weight-bold"> 
                                                <h4 class="text-primary">{{ ucfirst($key) }} wise flow</h4>
                                             </td>
                                        </tr>  
                                        @foreach ($item as $key1 => $item1)
                                            @if ($key1)
                                                 <tr>
                                                    <td colspan="6" class="text-center font-weight-bold"> 
                                                        <h5 class="text-secondary">{{ $key1 }}</h5>                                        
                                                     </td>
                                                </tr> 
                                            @endif

                                            @foreach ($item1 as $key3 => $processApprover)
                                                @foreach ($processApprover as $approver)
                                                    <tr>
                                                        <td class="text-center">{{ $approver->pa_sequence }}</td>
                                                        <td class="text-center">{{ ucfirst($approver->pa_type) }}</td>
                                                        <td class="text-center font-weight-semibold">
                                                            {{ $approver->fh_employee?->emp_code }} -
                                                            {{ $approver->fh_employee?->emp_full_name }}
                                                        </td>
                                                        <td class="text-center font-weight-semibold">
                                                            {{ $approver->fh_employee->emp_email }}
                                                        </td>
                                                        <td class="text-center">{{ $approver->fh_role->role_name }}</td>
                                                        <td class="text-center">{{ $approver->fh_approver_status->m_name }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif(count($employeeApprovalMapping))
                    <div class="row ">
                        <!-- Card Header -->
                        <div class="card-header pt-0 px-3">
                            <label class="font-weight-semibold card-title">Process Details: &nbsp;</label>
                            <label class="font-weight-semibold card-title"><b>Employee Wise</b></label>
                        </div>

                        @php
                            // Calculate the maximum number of approvers among all employees
                            $maxManagers = $employeeApprovalMapping->max(fn($item) => $item->approvalStatuses->count());
                        @endphp

                        <!-- Card Body -->
                        <div class="card-body">
                            @if($employeeApprovalMapping->isNotEmpty())
                                <div class="row mb-3">
                                    <div class="col-sm-1">
                                        <div class="form-group">
                                            <p class="form-label">Show entries</p>
                                            <select id="employeeLengthMenu" class="form-select-md p-2 search_test" style="width: 100%">
                                                <option value="5">5</option>
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                                <option value="-1">All</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <p class="form-label">Search</p>
                                            <input type="text" id="employeeCustomSearchBox" placeholder="Search" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-sm-2 ms-auto">
                                        <div class="form-group dropdown" style="margin-top: 25px;">
                                            <button class="export-button dropdown-toggle" type="button" id="employeeExportDropdown"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa fa-download me-2"></i> Export As
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-export" aria-labelledby="employeeExportDropdown">
                                                <li><a class="dropdown-item" href="#" data-export="csv">CSV</a></li>
                                                <li><a class="dropdown-item" href="#" data-export="excel">Excel</a></li>
                                                <li><a class="dropdown-item" href="#" data-export="pdf">PDF</a></li>
                                                <li><a class="dropdown-item" href="#" data-export="copy">Copy</a></li>
                                                <li><a class="dropdown-item" href="#" data-export="print">Print</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive mt-5">
                                    <table id="employeeApprovalTable" class="table table-bordered table-striped text-nowrap mb-0">
                                        <thead>
                                            <tr>
                                                <th class="font-weight-bold">Employee Name</th>
                                                @for($i = 1; $i <= $maxManagers; $i++)
                                                    <th class="font-weight-bold">Approving Manager {{ $i }}</th>
                                                @endfor
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($employeeApprovalMapping as $item)
                                                <tr>
                                                    <td>
                                                        {{ $item->fh_employee?->emp_code 
                                                            ? $item->fh_employee->emp_code . ' - ' . $item->fh_employee->emp_full_name 
                                                            : 'No Employee' 
                                                        }}
                                                    </td>

                                                    @for($i = 0; $i < $maxManagers; $i++)
                                                        @php
                                                            $status = $item->approvalStatuses[$i] ?? null;
                                                        @endphp
                                                        <td>
                                                            @if ($status)
                                                                {{ $status->manager?->emp_email ?? '-' }} -
                                                                {{ $status->manager?->emp_code ?? '-' }} -
                                                                {{ $status->manager?->emp_full_name ?? '-' }} -
                                                                {{ $status->approvalStatus?->m_name ?? '-' }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    @endfor
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div id="employee-table-controls" class="d-flex justify-content-between align-items-center mt-2">
                                    <div id="employee-info-wrapper"></div>
                                    <div id="employee-pagination-wrapper"></div>
                                </div>
                            @else
                                <p class="text-muted">No approval mappings found.</p>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="row ">
                        <!-- Card Header -->
                        <div class="card-header pt-0 px-3">
                            <label class="font-weight-semibold card-title">Process Details: &nbsp;</label>
                            <label class="font-weight-semibold card-title"><b>No Approval Rules Defined</b></label>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
    <!-- END ROW -->
@endsection

@section('script')
<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<style>
    .dataTables_empty{
        display:none;
    }
    .thead-light{
        background:#f2f2f2;

    }
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
/* Base style for pagination buttons */
.dataTables_paginate .paginate_button {
    padding: 6px 12px;
    margin: 0 3px;
    border: 1px solid #ccc;
    background-color: #fff;
    color: #333;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s, color 0.3s;
}

/* Hover effect */
.dataTables_paginate .paginate_button:hover {
    background-color: #f4f7fc !important;
    color: #000 !important;
    border: 1px solid #999;
}

/* Current (active) page button */
.dataTables_paginate .paginate_button.current {
    background-color: #1877f2 !important;
    color: #fff !important;
    border-color: #1877f2 !important;
    font-weight: bold;
}

/* Hover on current button (keep consistent color) */
.dataTables_paginate .paginate_button.current:hover {
    background-color: #1877f2 !important;
    color: #fff !important;
}

/* Disabled previous/next buttons */
.dataTables_paginate .paginate_button.disabled {
    background-color: #e9ecef;
    color: #999 !important;
    cursor: not-allowed;
    border-color: #ddd;
    opacity: 0.7;
}

/* Optional: container alignment and spacing */
.dataTables_paginate {
    display: flex;
    justify-content: center; /* or flex-end for right-aligned */
    align-items: center;
    gap: 4px;
    margin-top: 1rem;
}

#employeeLengthMenu{
   border: 1px solid #d3dfea
}

</style>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script>
$(document).ready(function() {
    var empTable = $('#employeeApprovalTable').DataTable({
        dom: 'rtip', // hide default search/buttons
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        pageLength: 10,
    });


    // Move pagination after the table
    var paginate = $('#employeeApprovalTable');
    $('#employeeApprovalTable').after(paginate);
    // Custom search
    $('#employeeCustomSearchBox').on('keyup', function() {
        empTable.search(this.value).draw();
    });
    // Custom export
    $('.dropdown-menu-export .dropdown-item').on('click', function(e) {
        e.preventDefault();
        var type = $(this).data('export');
        switch(type) {
            case 'csv': empTable.button('.buttons-csv').trigger(); break;
            case 'excel': empTable.button('.buttons-excel').trigger(); break;
            case 'pdf': empTable.button('.buttons-pdf').trigger(); break;
            case 'copy': empTable.button('.buttons-copy').trigger(); break;
            case 'print': empTable.button('.buttons-print').trigger(); break;
        }
    });

    // Show entries (page length) control
    $('#employeeLengthMenu').val(empTable.page.len());
    $('#employeeLengthMenu').on('change', function() {
        var val = $(this).val();
        empTable.page.len(val).draw();
    });

    // Move info and pagination below the table, outside the scrollable container
    function moveEmployeeTableControls() {
        var info = $('#employeeApprovalTable_info');
        var paginate = $('#employeeApprovalTable_paginate');
        $('#employee-info-wrapper').append(info);
        $('#employee-pagination-wrapper').append(paginate);
    }
    moveEmployeeTableControls();
    empTable.on('draw', function() {
        moveEmployeeTableControls();
    });
});
</script>
@endsection