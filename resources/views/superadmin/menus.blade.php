@extends('superadmin.layout.master')
@section('content')
    {{-- Bradcrumbs Start --}}
    <div class="p-0 mt-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
            <li class="active"><span><b>Menus</b></span></li>
        </ol>
    </div>
    {{-- Bradcrumbs End --}}

    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="page-title">Menus</div>
            <p class="text-muted m-0">Active Menus</p>
        </div>
        <div class="page-rightheader ms-md-auto">
            <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                <div class="d-lg-flex d-block ms-auto">
                    <div class="btn-list">
                        <button type="button" class="btn btn-outline-primary" data-bs-target="#MenusModal" data-bs-toggle="modal"
                            id="addMenusBtn">Add Menu</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Menu List</div>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-1 col-sm-4">
                            <div class="form-group">
                                <p class="form-label">Show entries</p>
                                <select id="customLengthMenu" class="form-select-md p-2 search_test" data-length
                                    style="width: 100px">
                                    <option value="5" style="width: 100px">5</option>
                                    <option value="10" style="width: 100px">10</option>
                                    <option value="25" style="width: 100px">25</option>
                                    <option value="50" style="width: 100px">50</option>
                                    <option value="100" style="width: 100px">100</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <div class="form-group mb-3">
                                    <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                        data-search />
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7 col-sm-4"></div>

                        <div class="col-md-2 col-sm-4 pt-5" align="right">
                            <div class="btn-group">
                                <button class="btn btn-outline-danger dropdown-toggle" type="button" id="defaultDropdown"
                                    data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                                    Export As
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

                        <div class="table-responsive">
                            <table class="table display table-vcenter text-wrap border-bottom" id="menu-table-dynamic">
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
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="MenusModal" tabindex="-1" role="dialog" aria-labelledby="MenusModal" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="MenusModalTitle">Add Menu</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="menusForm"> @csrf
                    <div class="modal-body">
                        <input type="hidden" name="Id" id="Id">

                        <div class="row mt-2">
                            <div class="col-xl-5 mb-4">
                                <label class="form-label" for="menu_id"
                                    title="Main navigation menu selection ">Menu<span class="text-danger">*</span></label>
                                <select name="menu_id" id="menu_id" class="form-control  select2"
                                    data-placeholder="Select Menu" required>
                                    <option class="text-muted" value="" label="Select Menu"></option>
                                    <option value="new_menu">New Menu</option>
                                    @foreach ($menu as $item)
                                        <option value="{{ $item->menu_id }}">{{ $item->menu_name }}</option>
                                    @endforeach
                                </select>
                                <div id="menu_id_error" class="text-danger" style="display: none;"></div>
                            </div>

                            <div class="col-xl-6 mb-4">
                                <label class="form-label" for="menu_p_id" title="Submenu selection under main menu">Sub
                                    Menu<span class="text-danger">*</span></label>
                                <select name="menu_p_id" id="menu_p_id" class="form-control  select2"
                                    data-placeholder="Select Sub Menu" required>
                                    <option class="text-muted" value="" label="Select Sub Menu"></option>
                                    <option value="new_sub_menu">New Sub Menu</option>
                                    @foreach ($sub_menu as $item)
                                        <option value="{{ $item->menu_id }}">{{ $item->menu_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-xl-1 mt-5">
                                <button type="button" class="btn btn-outline-primary mt-1" id="addMenutBtn"><i
                                        class="fa fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="row">
                            <div id="menusRows">
                                <div class="row menus-row mb-5">
                                    <input type="hidden" hidden value="" name="Id">
                                    <div class="col-md-1">
                                        <label class="form-label" for="method"
                                            title="Define the method: Create, Update, Get, Delete">Method</label>
                                        <select name="Method[]" id="Method_1" class="form-control  select2 methodType"
                                            placeholder="Select Method">
                                            <option class="text-muted" value="" label="Select Method"></option>
                                            @foreach ($route_type as $item)
                                                <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="name" title="Menu Name">Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="name[]" id="name_1" class="form-control"
                                            required placeholder="Name ">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label" for="icon" title="Menu Icon">Icon </label>
                                        <input type="text" name="icon[]" id="icon_1" class="form-control"
                                            placeholder="Icon">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label" for="active" title="Menu View or not">Status <span
                                                class="text-danger">*</span></label>
                                        <select name="active[]" class="form-control select2"
                                            data-placeholder="Select Status" required>
                                            <option class="text-muted" value="" label="Select Status"></option>
                                            @foreach ($status as $item)
                                                <option value="{{ $item->m_description }}">{{ $item->m_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label" for="sub_status"
                                            title="Menu Hierarchy with Nested Submenus">SubStatus</label>
                                        <input type="number" name="sub_status[]" class="form-control" min="0"
                                            placeholder="SubStatus">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="route"
                                            title="Menu path which location the menu show">Route </label>
                                        <input type="text" name="route[]" class="form-control" placeholder="Route">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="group"
                                            title="Which group the menu belong">Group <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="group[]" class="form-control" required
                                            placeholder="Group">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="sequence"
                                            title="the position of menu like 1st,2nd,3rd like this">Sequence <span
                                                class="text-danger">*</span></label>
                                        <input type="number" name="sequence[]" class="form-control" required
                                            placeholder="Sequence" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            datatable({
                tableId: "menu-table-dynamic",
                url: "{{ route('menus.index') }}",
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


        var number = 2;

        $(document).on('change', '.select2[name="active[]"]', function() {
            var selectedValue = $(this).val();
            var carryForwardLimitInput = $(this).closest('.row').find('input[name="sub_status[]"]');

            if (selectedValue == 222) {
                carryForwardLimitInput.prop('readonly', true).val(0) // Optionally reset to 0 when readonly
                    .css({
                        'background-color': '#eee',
                        'pointer-events': 'none'
                    });
            } else if (selectedValue == 221) {
                carryForwardLimitInput.prop('readonly', false)
                    .css({
                        'background-color': '',
                        'pointer-events': ''
                    });
            }
        });

        // Add new row
        document.getElementById('addMenutBtn').addEventListener('click', function() {

            let allFilled = true;
            const requiredFields = document.querySelectorAll('#menusRows .form-control[required]');

            requiredFields.forEach(function(field) {
                if (field.value.trim() === "") {
                    allFilled = false;
                    return;
                }
            });

            if (!allFilled) {
                alert("Please fill in all required fields before adding a new row.");
                return;
            }

            let routeTypeOptions = `
                @foreach ($route_type as $item)
                    <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                @endforeach
            `;

            let statusOptions = `
                @foreach ($status as $item)
                    <option value="{{ $item->m_description }}">{{ $item->m_name }}</option>
                @endforeach
            `;

            let html = `
                <div class="row menus-row mb-5">
                    <div class="col-md-1">
                        <select name="Method[]" id="Method_${number}" class="form-control select2 methodType" data-placeholder="Select Method" onchange="checkForDuplicatesAndAlert()" >
                            <option class="text-muted" value="" label="Select Category"></option>
                            ${routeTypeOptions}
                        </select>
                    </div>
                    <div class="col-md-2">
                         <input type="text" name="icon[]" id="name_${number}" class="form-control" data-placeholder="Name" required placeholder="Name">
                    </div>
                    <div class="col-md-1">
                        <input type="text" name="icon[]" id="icon_${number}" class="form-control" data-placeholder="Icon" placeholder="Icon">
                    </div>
                    <div class="col-md-1">
                        <select name="active[]" class="form-control select2" data-placeholder="Status" required>
                            <option class="text-muted" value="" label="Status"></option>
                            ${statusOptions}
                        </select>
                    </div>
                    <div class="col-md-1">
                        <input type="number" name="sub_status[]" class="form-control" data-placeholder="SubStatus" min="0" placeholder="Substatus">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="route[]" id="route_${number}" class="form-control" data-placeholder="Route" placeholder="Route">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="group[]" id="group_${number}" class="form-control" data-placeholder="Group" required placeholder="Group">
                    </div>
                    <div class="col-md-1">
                        <input type="number" name="sequence[]" id="sequence_${number}" class="form-control" data-placeholder="Sequence" required placeholder="Sequence" min="0">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger  removeRow"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            `;

            $('#menusRows').append(html);
            number++;
            $('.select2').select2();
            // Initialize Select2 on modal shown
            $('#MenusModal').on('shown.bs.modal', function() {
                if (!$(this).data('select2-initialized')) {
                    $('.select2').select2({
                        dropdownParent: $('#MenusModal')
                    });
                    $(this).data('select2-initialized', true);
                }
            });
        });


        function checkForDuplicatesAndAlert() {
            var count = $('.menus-row').length;
            var duplicateIndices = [];
            var isDuplicate = false;
            var combinationSet = new Set();

            // Iterate through each row with the .menus-row class
            $('.menus-row').each(function(index) {
                var elem = $(this).find('.route');
                if (elem.length > 0) { // Check if the element exists
                    var cat = elem.val();
                    var combination = `${cat}`;

                    if (combinationSet.has(combination)) {
                        isDuplicate = true;
                        duplicateIndices.push(index); // Use index since class-based elements are not unique
                    }
                    combinationSet.add(combination);
                } else {
                    console.log(`Element with class route not found in row ${index + 1}`);
                }
            });

            if (isDuplicate) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Duplicate selection of route on menu.',
                    timer: 3000,
                });

                duplicateIndices.forEach(function(index) {
                    // Reset the duplicate .methodType field
                    $('.menus-row').eq(index).find('.route').val('').trigger('change');
                });
                return false;
            }
            $('.select2').select2();
            $('#MenusModal').on('shown.bs.modal', function() {
                if (!$(this).data('select2-initialized')) {
                    $('.select2').select2({
                        dropdownParent: $('#MenusModal')
                    });
                    $(this).data('select2-initialized', true);
                }
            });
            return true;
        }

        // Remove row
        document.getElementById('menusRows').addEventListener('click', function(e) {
            if (e.target.classList.contains('removeRow')) {
                if (document.querySelectorAll('.menus-row').length > 1) {
                    e.target.closest('.menus-row').remove();
                } else {
                    alert('You need at least one row.');
                }
            }
        });

        // Listen for the modal close event
        $('#MenusModal').on('hidden.bs.modal', function() {
            // Remove all dynamically added rows
            $('#menusRows').html(`
                <div class="row menus-row mb-5">
                    <div class="col-md-1">
                    <label class="form-label" for="method">Method </label>
                        <select name="Method[]" id="Method_1" class="form-control  select2 methodType" data-placeholder="Select Method">
                        <option class="text-muted" value="" label="Select Method"></option>
                        @foreach ($route_type as $item)
                            <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                        @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name[]" id="name_1" class="form-control" data-placeholder="Name" required>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label" for="icon">Icon </label>
                        <input type="text" name="icon[]" id="icon_1" class="form-control" data-placeholder="Icon">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label" for="active">Status <span class="text-danger">*</span></label>
                        <select name="active[]" class="form-control select2" data-placeholder="Select Status" required>
                            <option class="text-muted" value="" label="Select Status"></option>
                            @foreach ($status as $item)
                                <option value="{{ $item->m_description }}">{{ $item->m_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label" for="sub_status">SubStatus </label>
                        <input type="number" name="sub_status[]" class="form-control" data-placeholder="Sub Status" min="0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="route">Route </label>
                        <input type="text" name="route[]" class="form-control" data-placeholder="Route" >
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="group">Group <span class="text-danger">*</span></label>
                        <input type="text" name="group[]" class="form-control" data-placeholder="Group"  required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="sequence">Sequence <span class="text-danger">*</span></label>
                        <input type="number" name="sequence[]" class="form-control" data-placeholder="sequence"  required min="0">
                    </div>
                </div>
            `);
            number = 2;
            $('.select2').select2();
            $('#MenusModal').on('shown.bs.modal', function() {
                if (!$(this).data('select2-initialized')) {
                    $('.select2').select2({
                        dropdownParent: $('#MenusModal')
                    });
                    $(this).data('select2-initialized', true);
                }
            });
        });


        $(document).on('click', '.edit-menu', function() {
            var id = $(this).data('id');
            var menu_p_id = $(this).data('p_id');
            var method = $(this).data('method');
            var name = $(this).data('name');
            var icon = $(this).data('icon');
            var route = $(this).data('route');
            var group = $(this).data('group');
            var status = $(this).data('status');
            var sub_status = $(this).data('sub_status');
            var sequence = $(this).data('sequence');

            $('#menu_id').val(menu_p_id).trigger('change');
            $('#menu_p_id').val(id).trigger('change');
            $('#menusRows').html('');

            var menu_id = @json($all_menu);

            menu_id.forEach(function(item, index) {

                if (item.menu_p_id == menu_p_id) {
                    let html = '';

                    if ($('#menusRows').children().length === 0) {
                        html += `
                        <div class="row">
                            <div class="col-md-1">
                                <label class="form-label" for="Method_0">Method </label>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="name_0">Name <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label" for="icon_0">Icon </label>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label" for="active_0">Status <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label" for="sub_status_0">SubStatus </label>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="route_0">Route </label>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="group_0">Group <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="sequence_0">Sequence <span class="text-danger">*</span></label>
                            </div>

                        </div>
                    `;
                    }

                    html += `
                <div class="row menus-row mb-3" data-index="${index}">
                    <input type="hidden" name="Id[]" value="${item.menu_id}">
                    <div class="col-md-1">
                        <select name="Method[]" id="Method_${index}" class="form-control select2 methodType" onchange="checkForDuplicatesAndAlert()">
                            <option value="" disabled>Select Method</option>`;
                    @foreach ($route_type as $route)
                        html +=
                            `<option value="{{ $route->m_id }}" ${item.menu_route_type_id == "{{ $route->m_id }}" ? 'selected' : ''}>{{ $route->m_name }}</option>`;
                    @endforeach
                    html += `
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="name[]" id="name_${index}" class="form-control" value="${item.menu_name}" required placeholder="Name">
                    </div>
                    <div class="col-md-1">
                        <input type="text" name="icon[]" id="icon_${index}" class="form-control" value="${item.menu_icon}" placeholder="Icon">
                    </div>
                    <div class="col-md-1">
                        <select name="active[]" id="active_${index}" class="form-control select2" required>
                            <option value="" disabled>Select Status</option>`;
                    @foreach ($status as $stat)
                        html +=
                            `<option value="{{ $stat->m_description }}" ${item.menu_status == "{{ $stat->m_description }}" ? 'selected' : ''}>{{ $stat->m_name }}</option>`;
                    @endforeach
                    html += `
                        </select>
                    </div>
                    <div class="col-md-1">
                        <input type="number" name="sub_status[]" id="sub_status_${index}" class="form-control" value="${item.menu_sub_status}" placeholder="SubStatus">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="route[]" id="route_${index}" class="form-control" value="${item.menu_route}" placeholder="Route">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="group[]" id="group_${index}" class="form-control" value="${item.menu_group}" required placeholder="Group">
                    </div>
                    <div class="col-md-1">
                        <input type="number" name="sequence[]" id="sequence_${index}" class="form-control" value="${item.menu_sequence}" required placeholder="Sequence">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger  removeRow"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            `;

                    $('#menusRows').append(html);
                }
            });

            $('.select2').select2();
            $('#MenusModal').on('shown.bs.modal', function() {
                if (!$(this).data('select2-initialized')) {
                    $('.select2').select2({
                        dropdownParent: $('#MenusModal')
                    });
                    $(this).data('select2-initialized', true);
                }
            });

            $('#MenusModal').modal('show');
            $('#MenusModalTitle').text('Edit Menu');
            checkForDuplicatesAndAlert();
        });



        $(document).ready(function() {
            // CSRF Token Setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Create or Update Leave Type
            $('#menusForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ url('superadmin/menus') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $('#saveBtn').attr('disabled', true);
                    },
                    success: function(response) {
                        if (response.status == true) {
                            $('#MenusModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                text: response.message,
                                timer: 3000,
                                didClose: () => {
                                    location
                                .reload(); // Reload the page after deletion
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                text: response.message,
                                timer: 3000,
                            });
                        }
                        $('#saveBtn').attr('disabled', false);
                    },
                    error: function(xhr, status, error) { // Updated parameters
                        console.error('AJAX Error: ', error); // This will log the error
                        Swal.fire({
                            icon: 'error',
                            text: 'Something went wrong!',
                            timer: 3000,
                        });
                        $('#saveBtn').attr('disabled', false);
                    }
                });
            });


            // Delete Leave Type
            $(document).on('click', '.delete-leave-type', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure ?',
                    text: 'You will not be able to recover this leave policy!',
                    // timer: 3000,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var url = "{{ route('leave-policy.destroy', ':id') }}";
                        url = url.replace(':id', id);
                        $.ajax({
                            url: url,
                            method: "DELETE",
                            success: function(response) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: response.success,
                                    icon: 'success',
                                    timer: 3000, // 3 seconds
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                    didClose: () => {
                                        location
                                    .reload(); // Reload the page after deletion
                                    }
                                });
                            }
                        });
                    }
                });
            });

            $(document).on('click', '#addMenusBtn', function() {
                // $('#addMenutBtn').show();
                $('#MenusModalTitle').html('Add Menu');
                $('#saveBtn').html('Save');
                $('#Id').val('');
                $('#menu_id').val('').trigger('change');
                $('#menu_p_id').val('');
                $('#MenusModal').modal('show');
            });
        });

        $('.select2').select2();
        $('#MenusModal').on('shown.bs.modal', function() {
            $('.select2').each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy'); // Destroy any existing instances
                }
                $(this).select2({
                    dropdownParent: $('#MenusModal') // Reinitialize select2 with modal as parent
                });
            });
        });
    </script>
@endsection
