@php
    use Carbon\Carbon;
@endphp

@extends('admin.layout.master')
@section('title', 'Comp Off Policy')

@section('css')
    <style>
        .form-check-input {
            margin-left: -22px !important;
            margin-top: 10px !important;
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
@endsection

@section('content')

    {{-- Breadcrumbs Start --}}
    <div class="p-0 my-5">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/admin/settings/attendance') }}">Attendance Settings</a></li>
                    <li class="active"><span><b>Comp Off Policy</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <x-button type="button" class="btn btn-outline-primary create-button"
                                    data-bs-toggle="modal" data-bs-target="#compOffFormModal" data-title="Create Comp Off">
                                    Create Comp Off Policy
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Breadcrumbs End --}}

    {{-- DataTable Start --}}
    <div class="container-fluid px-0">
        <div class="card">
            <div class="card-header border-0">
                <h4 class="card-title">Comp Off Policy</h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-6 row">
                        <div class="col-6 col-xl-3">
                            <div class="form-group">
                                <p class="form-label">Show entries</p>
                                <select id="customLengthMenu" class="form-select-md p-2 search_test" style="width: 100%"
                                    data-length>
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-6 col-xl-4">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                    data-search />
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 d-flex align-items-center justify-content-end gap-3">
                        <div>
                            <div class="dropdown">
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
                    </div>

                </div>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table display table-hover table-vcenter text-wrap border-bottom"
                        id="compoff-policy-table-dynamic">
                        <thead>
                            <tr>
                                @foreach ($columns as $column)
                                    <th style="font-size: 13px">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                    </table>
                </div>

                {{-- Pagination --}}
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
    {{-- DataTable End --}}

    {{-- Add Comp Off Policy Modal Start --}}
    <div class="modal fade" id="compOffFormModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content modal-content-demo">

                <div class="modal-header">
                    <h6 class="modal-title">Create Comp Off Policy</h6>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" onclick="resetForm()"
                        type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">

                            <form method="post" action="{{ route('compoff-policy.store') }}" class="repeater"
                                id="compOffPolicyForm">
                                @csrf
                                <div class="row g-3 p-2">

                                    {{-- Primary Key --}}
                                    <input type="hidden" id="cop_id" name="cop_id" value="">

                                    {{-- Policy Name --}}
                                    <div class="col-6">
                                        <label for="co_policy_name" class="form-label">
                                            Comp Off Policy Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="co_policy_name" name="co_policy_name"
                                            class="form-control" placeholder="Comp Off Policy Name"
                                            aria-label="Comp Off Policy Name"
                                            onkeyup="return validateFields('co_policy_name')">
                                    </div>

                                    {{-- With Effect From --}}
                                    <div class="col-6">
                                        <label class="form-label" for="cop_effective_date">
                                            With Effect From <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" id="cop_effective_date" name="cop_effective_date"
                                            class="form-control" oninput="return validateFields('cop_effective_date')"
                                            value="{{ Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d') }}">
                                    </div>

                                    {{-- Policy Status & Carry Forward Toggle --}}
                                    <div class="col-3">
                                        <label class="custom-switch form-switch p-0 align-items-start flex-column-reverse">
                                            <input class="custom-switch-input" type="checkbox" role="switch"
                                                id="cop_status">
                                            <span class="custom-switch-indicator"></span>
                                            <span class="custom-switch-description ms-0 mb-2">Policy Status</span>
                                        </label>
                                    </div>

                                    <div class="col-3">
                                        <label class="custom-switch form-switch p-0 align-items-start flex-column-reverse">
                                            <input class="custom-switch-input" type="checkbox" role="switch"
                                                id="carry_forward" onchange="validateCarryForward();">
                                            <span class="custom-switch-indicator"></span>
                                            <span class="custom-switch-description ms-0 mb-2">Carry Forward</span>
                                        </label>
                                    </div>

                                    {{-- Validity Days --}}
                                    <div class="col-6">
                                        <label class="form-label" for="validity">
                                            Validity <span class="text-danger">* (Days)</span>
                                        </label>
                                        <input type="number" id="validity" name="validity" class="form-control"
                                            placeholder="15 Days" aria-label="15 Days" min="1"
                                            onkeyup="validateCarryForward(); return validateFields('validity')">
                                        <span class="text-danger validity-error"></span>
                                    </div>

                                    {{-- Work Duration Condition Heading --}}
                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <div>
                                            <h6 class="mb-0">Worked Duration Threshold Conditions</h6>
                                            <span id="condition-msg" class="text-danger"></span>
                                        </div>
                                        <div>
                                            <button data-repeater-create type="button" class="btn btn-outline-secondary"
                                                id="addConditionBtn">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Work Duration Condition Repeater --}}
                                    <div data-repeater-list="conditions">
                                        <div data-repeater-item class="row mb-4">
                                            <div class="col-3">
                                                <label for="work_hours" class="form-label">
                                                    Work Duration <span class="text-danger">* (Hrs)</span>
                                                </label>
                                                <input type="number" name="work_hours" min="1"
                                                    class="form-control" placeholder="5 hrs" aria-label="5 hrs"
                                                    onkeyup="return validateFields('work_hours')">
                                            </div>
                                            <div class="col-3">
                                                <label for="operator" class="form-label">
                                                    Operator <span class="text-danger">*</span>
                                                </label>
                                                <select name="operator" class="form-select" aria-label="Operator"
                                                    onchange="return validateFields('operator')">
                                                    <option value="" selected disabled>----Select----</option>
                                                    <option value="=">Equal</option>
                                                    <option value="<">Less Than</option>
                                                    <option value=">">Greater Than</option>
                                                    <option value="<=">Less Than Equal</option>
                                                    <option value=">=">Greater Than Equal</option>
                                                </select>
                                            </div>
                                            <div class="col-3">
                                                <label for="cp_quantity" class="form-label">
                                                    Comp Off Quantity <span class="text-danger">*</span>
                                                </label>
                                                <input type="number" name="cp_quantity" min="1"
                                                    class="form-control" placeholder="1" aria-label="1"
                                                    onkeyup="return validateFields('cp_quantity')">
                                            </div>
                                            <div class="col-1 d-flex align-items-end">
                                                <div>
                                                    <button data-repeater-delete
                                                        type="button"class="btn btn-outline-danger">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </form>

                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-bs-dismiss="modal"
                        onclick="resetForm()">Close</button>
                    <button type="button" class="btn btn-primary" id="saveCompOffPolicy">Save Setting</button>
                </div>
            </div>
        </div>
    </div>
    {{-- Add Comp Off Policy Modal End --}}

@endsection

@section('script')
    <script>
        function resetForm() {
            // Reset normal form fields
            $('#compOffPolicyForm')[0].reset();

            // Completely clear the repeater list
            $('.repeater').find('[data-repeater-item]').remove();

            // Also clear the values in the first repeater item
            $('.repeater').repeater('reset');

            $("#compOffPolicyForm").attr("action", "{{ route('compoff-policy.store') }}");
            $(".modal-title").text("Add Comp Off Policy");
            $("#saveCompOffPolicy").text("Save Settings");
            $("#cop_id").val("");
            $("#carry_forward").prop("disabled", false);
        }

        $('#compOffFormModal').on('hidden.bs.modal', function() {
            resetForm();
        });

        $('.repeater').repeater({
            isFirstItemUndeletable: true,
            show: function() {
                $(this).slideDown();
            },
            hide: function(deleteElement) {
                $(this).slideUp(deleteElement);
            },
        });

        function validateCarryForward() {
            let carry_forward = $('#carry_forward').is(':checked') ? 1 : 0;
            let validity = $('#validity').val();
            const validityInt = parseInt(validity, 10);
            if (!carry_forward && !isNaN(validityInt) && validityInt > 30) {
                $('#validity').val(30);
                $(".validity-error").text("Validity cannot exceed 30 days if Carry Forward is disabled.");
            } else {
                $(".validity-error").text("");
            }
        }

        $(document).ready(function() {
            datatable({
                tableId: "compoff-policy-table-dynamic",
                url: "{{ route('compoff-policy.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false
            });

            // Edit policy Form Set
            $(document).on("click", ".edit-policy", function() {
                const editData = $(this).data('edit-data');
                $('#compOffPolicyForm')[0].reset();

                $("#cop_id").val(editData.id);
                $("#co_policy_name").val(editData.co_policy_name);
                editData.carry_forward == 120 ?
                $("#carry_forward").prop("checked", true) :
                $("#carry_forward").prop("checked", false);
                $("#carry_forward").prop("disabled", true);
                editData.cop_status ?
                $("#cop_status").prop("checked", true) :
                $("#cop_status").prop("checked", false);
                $("#validity").val(editData.validity);
                $("#cop_effective_date").val((editData.cop_effective_date.substr(0, 10)));


                editData.conditions.forEach((item, index) => {
                    // Populate condition fields
                    $("#addConditionBtn").click();
                    $("input[name='conditions[" + index + "][work_hours]']").val(item.work_duration);
                    $("select[name='conditions[" + index + "][operator]']").val(item.operator);
                    $("input[name='conditions[" + index + "][cp_quantity]']").val(item.co_quantity);
                });

                $("#compOffPolicyForm").attr("action", "{{ route('compoff-policy.update', '') }}/" +
                    editData.id);
                $(".modal-title").text("Edit Comp Off Policy");
                $("#saveCompOffPolicy").text("Update Settings");
                $('#compOffFormModal').modal('show');
            });

            // Delete policy
            $(document).on("click", ".delete-button", function() {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: $(this).data('url'),
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            type: "DELETE",
                            success: function(response) {
                                Swal.fire({
                                    title: "Deleted!",
                                    text: "The policy has been deleted.",
                                    icon: "success"
                                });
                                $('#compoff-policy-table-dynamic').DataTable().ajax
                                    .reload();
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: "Error!",
                                    text: "An error occurred while deleting the policy.",
                                    icon: "error"
                                });
                            }
                        });
                    }
                });
            });

            // Save or Update Comp Off Policy
            $("#saveCompOffPolicy").click(function() {
                // Validate required fields before submitting
                if (validateFields('co_policy_name', 'validity', 'work_hours', 'operator', 'cp_quantity',
                        'cop_effective_date')) {
                    let carry_forward = $('#carry_forward').is(':checked') ? 1 : 0;
                    let cop_status = $('#cop_status').is(':checked') ? 1 : 0;
                    const copForm = document.getElementById("compOffPolicyForm");
                    const formData = new FormData(copForm);
                    formData.append('carry_forward', carry_forward);
                    formData.append('cop_status', cop_status);
                    let cop_id = $("#cop_id").val();
                    if (cop_id) {
                        formData.append('_method', 'PUT'); // For update request
                    }
                    // formData.forEach((value, key) => {
                    //     console.log(key, value);
                    // });

                    if (formData.get('conditions[0][work_hours]') && formData.get(
                            'conditions[0][operator]') && formData.get('conditions[0][cp_quantity]')) {
                        $("#condition-msg").text("");

                        // Ajax call to save the policy
                        $.ajax({
                            type: "POST",
                            url: $(copForm).attr('action'),
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        html: response.message,
                                        confirmButtonText: 'OK'
                                    });
                                    $('#compOffFormModal').modal('hide');
                                    $('#compoff-policy-table-dynamic').DataTable().ajax
                                        .reload();
                                } else {
                                    console.log(response.error);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        html: response.message,
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(xhr) {
                                console.log(xhr.responseJSON);
                                Swal.fire({
                                    icon: 'error',
                                    title: xhr.responseJSON.message,
                                    html: xhr.responseJSON.error,
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    } else {
                        $("#condition-msg").text("Please add work duration conditions.");
                    }
                }
            });
        });
    </script>
@endsection
