@extends('admin.layout.master')
@section('title', 'Email Templates')

@section('header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('css')
    <style>
        .fade-message {
            transition: opacity 0.5s ease;
        }

        .export-button,
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

        .export-button:hover,
        .custom-button:hover {
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
    </style>
    <style>
        .variable-bar {
            margin-bottom: 10px;
        }

        .variable-pill {
            cursor: pointer;
            margin: 3px 3px 3px 0;
            padding: 6px 12px;
            font-size: 0.875rem;
            background-color: #e7f1ff;
            color: #0d6efd;
            border-radius: 50px;
            display: inline-block;
            transition: all 0.2s;
        }

        .variable-pill:hover {
            background-color: #0d6efd;
            color: #fff;
        }

        .quill-editor {
            min-height: 250px;
            background-color: #fff;
        }
    </style>
@endsection

@section('content')

    <div class="mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background:none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li class="active"><span><b>Email Templates</b></span></li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Email Template List</h4>
                    <button class="btn btn-primary openAddModal" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
                        <i class="fa fa-plus"></i> Add
                    </button>

                </div>


                <div class="card-body">
                    <div class="row align-items-end">

                        <!-- Show Entries -->
                        <div class="col-sm-1">
                            <div class="form-group">
                                <label class="form-label">Show entries</label>
                                <select id="customLengthMenu" class="form-select search_test " data-length>
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>

                        <!-- Search -->
                        <div class="col-sm-2">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                    data-search />
                            </div>
                        </div>

                        <div class="col-sm-6"></div>

                        <!-- Filters Button -->
                        <div class="col-sm-1">
                            <button class="custom-button w-100" type="button" onclick="toggleFilters()">
                                <i class="las la-filter"></i> Filters
                            </button>
                        </div>

                        <!-- Export Button -->
                        <div class="col-sm-1 px-1">
                            <div class="dropdown">
                                <button class="export-button dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fa fa-download me-2"></i> Export As
                                </button>
                                <ul class="dropdown-menu dropdown-menu-export">
                                    <li><a class="dropdown-item" href="#" data-export="csv">CSV</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="excel">Excel</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="pdf">PDF</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="copy">Copy</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="print">Print</a></li>
                                </ul>
                            </div>
                        </div>

                    </div>

                    <!-- FILTER SECTION -->
                   <div id="filterContainer" class="row mt-3" style="display:none;">

                        <div class="col-md-2">
                            <label class="form-label">Module</label>
                            <select class="form-select search_test filter_border" style="border-radius: 20px;" data-filter id="moduleFilter">
                                <option value="">All</option>
                                @foreach ($modules as $m)
                                    <option value="{{ $m->m_id }}">{{ $m->m_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Mail Type</label>
                            <select class="form-select search_test filter_border"style="border-radius: 20px;" data-filter id="mailTypeFilter">
                                <option value="">All</option>
                                <option value="submission">Submission</option>
                                <option value="rejected">Rejected</option>
                                <option value="approved">Approved</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select search_test filter_border"style="border-radius: 20px;" data-filter id="statusFilter">
                                <option value="">All</option>
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>

                    </div>

                    <!-- TABLE -->
                    <table class="table table-hover table-vcenter text-wrap border-bottom mt-3" id="mail-template-table">
                        <thead>
                            <tr>
                                @foreach ($columns as $c)
                                    <th style="font-size:12px;width:{{ $c['width'] }}">{{ $c['name'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                    </table>

                    <!-- Pagination -->
                    <div class="row mt-4">
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

    <!-- Add/Edit Email Template Modal -->
    <div class="modal fade" id="addTemplateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title" id="modalTitle">Add</h5>

                    <!-- Predefined Templates Dropdown -->
                    <select id="staticTemplateSelect" class="form-select form-select-sm" style="width: 250px;">
                        <option value="">-- Select Predefined Template --</option>
                    </select>

                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
                </div>
                <!-- Variable Insert Dropdown -->
                <form id="mailTemplateForm" action="{{ route('mail.template.store') }}" method="POST">
                    @csrf
                    <input type="hidden" id="mt_id" name="mt_id" value="">
                    <div class="modal-body">
                    
                        <!-- Module -->
                        <div class="d-flex gap-3">
                            <div class="mb-3 flex-fill">
                                <label for="mt_title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" id="mt_title" name="mt_title" class="form-control" required>
                            </div>
                        </div>

                            <!-- Module -->
                        <div class="d-flex gap-3">
                            <div class="mb-3 flex-fill">
                                <label for="mt_module_id" class="form-label">
                                    Module <span id="moduleRequiredStar" class="text-danger">*</span>
                                </label>
                                <select id="mt_module_id" name="mt_module_id" class="form-select search_test" required>
                                    <option value="">Select</option>
                                    @foreach ($modules as $m)
                                        <option value="{{ $m->m_id }}"> {{ $m->m_id }}-{{ $m->m_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="mb-3 flex-fill">
                                <label for="mt_mail_type" class="form-label">Mail Type <span class="text-danger">*</span></label>
                                <select id="mt_mail_type" class="form-select search_test" required disabled>
                                    <option value="">Select</option>
                                    <option value="submission" class="sub">Submission</option>
                                    <option value="rejected" class="rej">Rejected</option>
                                    <option value="approved" class="app">Approved</option>
                                    <option value="custom" class="cus">Custom</option>
                                </select>
                                <input type="hidden" id="mt_mail_type_display" name="mt_mail_type">
                            </div>

                            <div lass="mb-5 flex-fill">
                                <label for="variableSelect" class="form-label">Insert Variable:</label>
                                <select id="variableSelect" class="form-select form-select-sm search_test " style="width: 250px; overflow-y:auto;">
                                    <option value="">-- Select Variable --</option>
                                    @php
                                        $variables = [
                                            'employee_name' => 'Employee Name',
                                            'employee_code' => 'Employee Code',
                                            'employee_email' => 'Employee Email',
                                            'designation' => 'Designation',
                                            'department' => 'Department',

                                            'resignation_submission_date' => 'Resignation Submission Date',
                                            'resignation_reason' => 'Resignation Reason',
                                            'notice_period' => 'Notice Period',
                                            'last_working_day' => 'Last Working Day',
                                            'resignation_status' => 'Resignation Status',

                                            'manager_name' => 'Manager Name',
                                            'manager_email' => 'Manager Email',
                                            'approval_status' => 'Approval Status',
                                            'approval_date' => 'Approval Date',
                                            'approval_remarks' => 'Approval Remarks',

                                            'leave_type' => 'Leave Type',
                                            'leave_start_date' => 'Leave Start Date',
                                            'leave_end_date' => 'Leave End Date',
                                            'leave_days' => 'Leave Days',
                                            'leave_reason' => 'Leave Reason',

                                            'salary_month' => 'Salary Month',
                                            'salary_year' => 'Salary Year',
                                            'gross_salary' => 'Gross Salary',
                                            'net_salary' => 'Net Salary',
                                            'ctc' => 'CTC',

                                            'company_name' => 'Company Name',
                                            'company_email' => 'Company Email',
                                            'company_phone' => 'Company Phone',
                                            'company_address' => 'Company Address',

                                            'hr_name' => 'HR Name',
                                            'hr_email' => 'HR Email',
                                            'hr_phone' => 'HR Phone',

                                            'current_date' => 'Current Date',
                                            'portal_login_url' => 'Portal Login URL',
                                            'reset_password_link' => 'Reset Password Link',
                                        ];
                                    @endphp

                                    @foreach ($variables as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <style>
                                #variableSelect {
    width: 100% !important;
}

.select2-container {
    width: 100% !important;
}

.select2-dropdown {
    border-radius: 8px !important;
    overflow: hidden;
}

.select2-results__options {
    max-height: 250px !important;
    overflow-y: auto !important;
}

.select2-search__field {
    border-radius: 6px !important;
}

.select2-container--default .select2-selection--single {
    height: 38px !important;
    border: 1px solid #ced4da !important;
    border-radius: 6px !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
}
                            </style>
                        </div>

                        <!-- Email Body -->
                        <div class="mb-3">
                            <label for="mt_body" class="form-label">Email Body <span
                                    class="text-danger">*</span></label>
                            <textarea id="mt_body" name="mt_body" class="form-control summernote" rows="5" required></textarea>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="mt_is_enabled" class="form-label">Status <span
                                    class="text-danger">*</span></label>
                            <select id="mt_is_enabled" name="mt_is_enabled" class="form-select" required>
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>

    <script>
        $('#variableSelect').change(function() {
            const variableKey = $(this).val(); // e.g., employee_name
            if (variableKey) {
                $('#mt_body').summernote('focus');
                // Insert as Blade-style variable
                $('#mt_body').summernote('pasteHTML', `@{{${variableKey}}}`);
                $(this).val(''); // reset dropdown
            }
        });
    </script>

    <script>
        function toggleFilters() {
            const container = document.getElementById('filterContainer');
            container.style.display = container.style.display === 'none' ? 'flex' : 'none';
        }
    </script>
    <script>
        $(document).ready(function() {
            $('.summernote').summernote({
                placeholder: 'Write your email content here...',
                tabsize: 2,
                height: 200
            });

            const userTemplates = {
                submission: {
                    title: "Request Submission",
                    body: `<p>Dear @{{ employee_name }} (Emp Code: @{{ emp_code }}),</p>
                <p>Your request has been successfully submitted.</p>
                <ul>
                    <li>Request Type: @{{ request_type }}</li>
                    <li>From Date: @{{ from_date }}</li>
                    <li>To Date: @{{ to_date }}</li>
                    <li>Reason: @{{ reason }}</li>
                </ul>
                <p>We will notify you once it is reviewed.</p>
                <p>Regard,<br>@{{ company_name }} HR Team</p>`
                },
                approved: {
                    title: "Request Approved",
                    body: `<p>Dear @{{ employee_name }} (Emp Code: @{{ emp_code }}),</p>
                <p>Good news! Your request has been approved.</p>
                <ul>
                    <li>Request Type: @{{ request_type }}</li>
                    <li>From Date: @{{ from_date }}</li>
                    <li>To Date: @{{ to_date }}</li>
                    <li>Approved By: @{{ approver_name }}</li>
                </ul>
                <p>Regard,<br>@{{ company_name }} HR Team</p>`
                },
                rejected: {
                    title: "Request Rejected",
                    body: `<p>Dear @{{ employee_name }} (Emp Code: @{{ emp_code }}),</p>
                <p>We regret to inform you that your request has been rejected.</p>
                <ul>
                    <li>Request Type: @{{ request_type }}</li>
                    <li>From Date: @{{ from_date }}</li>
                    <li>To Date: @{{ to_date }}</li>
                    <li>Rejected By: @{{ approver_name }}</li>
                    <li>Reason: @{{ reason }}</li>
                </ul>
                <p>Regard,<br>@{{ company_name }} HR Team</p>`
                },
                custom: {
                    title: "Custom Mail",
                    body: `<p>Dear @{{ employee_name }},</p>
            
                    <p>@{{ custom_message }}</p>
            
                    <p>
                        Reference No : @{{ reference_no }}<br>
                        Date : @{{ current_date }}
                    </p>
            
                    <p>
                        Regards,<br>
                        @{{ company_name }}
                    </p>`
                }
            };

            const templateSelect = $('#staticTemplateSelect');
            $.each(userTemplates, (key, val) => {
                templateSelect.append(`<option value="${key}">${val.title}</option>`);
            });

            templateSelect.change(function() {
                const key = $(this).val();
                $('#mt_mail_type').val(key)[0].sumo.reload();
                $('#mt_mail_type_display').val(key);
                if (key === 'custom') {
                    $('#mt_module_id')
                        .prop('required', false)
                        .val('');
                    $('#moduleRequiredStar').hide();
                } else {
                    $('#mt_module_id').prop('required', true);
                    $('#moduleRequiredStar').show();
                }
            
                if ($('#mt_module_id')[0].sumo) {
                    $('#mt_module_id')[0].sumo.reload();
                }
            
                if (key && userTemplates[key]) {
                    $('#mt_title').val(userTemplates[key].title);
                    $('#mt_body').summernote('code', userTemplates[key].body);
                } else {
                    $('#mt_title').val('');
                    $('#mt_body').summernote('code', '');
                }
            });
            
            $('#mt_mail_type').on('change', function () {
                if ($(this).val() === 'custom') {
                    $('#mt_module_id')
                        .prop('required', false)
                        .val('');
                    $('label[for="mt_module_id"] .text-danger').hide();
                } else {
                    $('#mt_module_id').prop('required', true);
                    $('label[for="mt_module_id"] .text-danger').show();
                }
            
                // SumoSelect refresh
                if ($('#mt_module_id')[0].sumo) {
                    $('#mt_module_id')[0].sumo.reload();
                }
            });

            // OPEN ADD MODAL → RESET THE FORM COMPLETELY
            $(document).on('click', '.openAddModal', function() {
                $('#modalTitle').text('Add');
                $('#mailTemplateForm')[0].reset();
                $('#mt_id').val('');
                $('#mt_body').summernote('code', '');
                $('#staticTemplateSelect').val('');
                $('#mt_module_id').prop('required', true).val('');
                $('#moduleRequiredStar').show();
                $('#mt_is_enabled').val('1').trigger('change');
            });

            // Edit Button Click
            $(document).on('click', '.editBtn', function() {
                const id = $(this).data('id');

                $.get(`/privilege/mail-template/get/${id}`, function(res) {
                    // if (d.mt_mail_type === 'custom') {
                    //     $('#mt_module_id').prop('required', false);
                    //     $('#moduleRequiredStar').hide();
                    // } else {
                    //     $('#mt_module_id').prop('required', true);
                    //     $('#moduleRequiredStar').show();
                    // }
                    if (res.status === 'success') {
                        const d = res.data;
                        if (d.mt_mail_type === 'custom') {
                            $('#mt_module_id').prop('required', false);
                            $('#moduleRequiredStar').hide();
                        } else {
                            $('#mt_module_id').prop('required', true);
                            $('#moduleRequiredStar').show();
                        }

                        $('#modalTitle').text('Edit');
                        $('#mt_id').val(d.mt_id);
                        $('#mt_title').val(d.mt_title);
                        $('#mt_mail_type').val(d.mt_mail_type || '1');
                        $('#mt_module_id').val(d.mt_module_id).trigger('change');
                        $('#mt_is_enabled').val(d.mt_is_enabled == 1 ? '1' : '0');
                        $('#mt_body').summernote('code', d.mt_body);
                        $('#staticTemplateSelect').val('');
                        $('#addTemplateModal').modal('show');
                    } else {
                        Swal.fire('Error!', 'Template not found.', 'error');
                    }
                });
            });

            // Form Submit (AJAX)
            $('#mailTemplateForm').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                $.ajax({
                    url: "{{ route('mail.template.store') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        Swal.fire('Success!', res.message, 'success').then(() => {
                            $('#mail-template-table').DataTable().ajax.reload(null,
                                false);
                            $('#addTemplateModal').modal('hide');
                            $('#mailTemplateForm')[0].reset();
                            $('#mt_body').summernote('code', '');
                            $('#staticTemplateSelect').val('');
                        });
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors || {};
                        let msg = Object.values(errors).flat().join('\n') ||
                            'Something went wrong!';
                        Swal.fire('Error!', msg, 'error');
                    }
                });
            });

            // Delete template
            $(document).on('click', '.deleteBtn', function() {
                const id = $(this).data('id');
                Swal.fire({
                    icon: 'warning',
                    title: 'Are you sure?',
                    text: 'This will delete the template!',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/privilege/mail-template/delete/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: res => {
                                Swal.fire('Deleted!', res.message, 'success');
                                $('#mail-template-table').DataTable().ajax.reload(null,
                                    false);
                            }
                        });
                    }
                });
            });

            // Toggle enable/disable
            $(document).on('click', '.toggleStatusBtn', function() {
                const id = $(this).data('id');
                $.post(`/privilege/mail-template/toggle-status/${id}`, {
                    _token: $('meta[name="csrf-token"]').attr('content')
                }, res => {
                    Swal.fire('Success!', res.message, 'success');
                    $('#mail-template-table').DataTable().ajax.reload(null, false);
                });
            });

            datatable({
                tableId: "mail-template-table",
                url: "{{ route('mail.template.index') }}",
                dataLength: "[data-length]",
                dataSearch: "[data-search]",
                dataFilter: "[data-filter]",
                dataExport: "[data-export]",
                dataDateFilter: "[data-date-filter]",
                dataShowEntries: "[data-show-entries]",
                dataPagination: "[data-pagination]",
                dataStateSave: true
            });

        });
    </script>
@endsection
