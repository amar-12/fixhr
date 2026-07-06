<?php
use App\Helpers\RolePermissionLogics;

$permissions = new RolePermissionLogics();
?>
<?php
use Illuminate\Support\Facades\Auth;
use App\Models\AppMenu;

$user = Auth::user();
$appMenu = new AppMenu();
?>
@extends('admin.layout.master')
@section('title')
    App Role Permission
@endsection
@section('css')
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "permission-table-dynamic",
                url: "{{ route('app-role.permission.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
            });
        });

        function ItemDeleteModel(e) {
            let id = e.getAttribute('data-id'); // No .value needed
            // var id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this app role permission!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Generate the correct URL dynamically
                    var url = "{{ route('app-role.permission.destroy', ':id') }}".replace(':id', id);
                    $.ajax({
                        url: url,
                        method: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.success,
                                icon: 'success',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                didClose: () => location.reload()
                            });
                        },
                        error: function(xhr) {
                            var errorMessage = 'An error occurred while deleting. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }

                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });

                }

            });
        }
    </script>
@endsection
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
</style>

@section('content')
    <div>

        {{-- Bradcrumbs Start --}}
        <div class="p-0 mt-3">
            <div class="row">
                <div class="col-md-4">
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a href="/admin/role/permission">Privilege</a></li>
                        <li class="active"><span><b>App Role Permission</b></span></li>
                    </ol>
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-2">
                    <div class="page-rightheader ms-md-auto">
                        <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                            <div class="d-lg-flex d-block ms-auto">
                                <div class="btn-list">
                                    @if (
                                        $permissions->check_route_permission('admin/app-role/permission', 116) ||
                                            $permissions->check_route_permission('admin/app-role/permission/save', 115) ||
                                            $permissions->check_route_permission('admin/app-role/permission/role-wise/{id}', 115))
                                        <button type="button" class="btn btn-outline-primary"
                                            data-bs-target="#empAppPermission" data-bs-toggle="modal">Assign
                                            Permission</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Bradcrumbs End --}}



        <!-- ROW -->
        <div class="row mt-5">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">

                    <div class="card-header border-0 p-3">
                        <h4 class="card-title">App Role Permission</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-1">
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


                            <div class="col-sm-2">
                                <div class="form-group">
                                    <p class="form-label">Search</p>
                                    <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                        data-search />
                                </div>
                            </div>

                            <div class="col-sm-7">
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

                        <div class="table-responsive">
                            <table class="table display table-hover table-vcenter text-wrap border-bottom"
                                id="permission-table-dynamic">
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

        {{-- modal start --}}
        <div class="modal fade" id="empAppPermission" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg" role="document">
                <form action="{{ route('app.role.permission.store') }}" method="post" id="appPermissionForm">
                    @csrf
                    <div class="modal-content modal-content-demo">
                        <div class="modal-header border-0">
                            <h4 class="modal-title ms-2">Add App Role - Permission</h4>
                            <button type="button" id="appCloseBtn" class="btn-close" data-bs-dismiss="modal">
                                <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <p class="form-label">User Roles</p>
                                        <select class="form-select-md search_test" name="role_id" id="appRoleSelect"
                                            required>
                                            <option value="">Select Role</option>
                                            @foreach ($permissions->get_role_list() as $role)
                                                <option value="{{ $role->role_id }}">{{ $role->role_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <hr>
                                <div class="col-12 px-5">
                                    <label class="custom-control custom-checkbox fw-20">
                                        <input type="checkbox" class="custom-control-input allow select-all-checkbox"
                                            onchange="appToggleSelectAll(this)">
                                        <span class="custom-control-label">Select All</span>
                                    </label>
                                </div>

                            </div>
                            <div class="row px-2" id="appCustomModalBody">
                                <div class="accordion" id="appMenuAccordion">
                                    @php
                                        $menus = $permissions->get_menu_list($appMenu);
                                        $menuGroups = [];
                                        foreach ($menus as $menu) {
                                            $menuGroups[$menu->menu_group][] = $menu;
                                        }
                                    @endphp

                                    @foreach ($menuGroups as $groupName => $groupMenus)
                                        <div class="accordion-item">
                                            <h4 class="accordion-header" id="appHeading-{{ Str::slug($groupName) }}">
                                                <button class="accordion-button custom-accordion-button" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapse-{{ Str::slug($groupName) }}"
                                                    aria-expanded="true"
                                                    aria-controls="collapse-{{ Str::slug($groupName) }}">
                                                    {{ $groupName }}
                                                </button>
                                            </h4>
                                            <div id="collapse-{{ Str::slug($groupName) }}"
                                                class="accordion-collapse collapse show"
                                                aria-labelledby="heading-{{ Str::slug($groupName) }}"
                                                data-bs-parent="#menuAccordion">
                                                <div class="accordion-body">
                                                    <div class="d-flex justify-content-end mb-3">
                                                        <label class="custom-control custom-checkbox">
                                                            <input type="checkbox"
                                                                class="custom-control-input allow group-select-all"
                                                                data-group="{{ Str::slug($groupName) }}"
                                                                onchange="appToggleGroupSelectAll(this, '{{ Str::slug($groupName) }}')">
                                                            <span class="custom-control-label">Select All</span>
                                                        </label>
                                                    </div>
                                                    @foreach ($groupMenus as $menu)
                                                        <div class="row p-1">
                                                            <div class="col-lg-3">
                                                                <div>{{ $menu->menu_name }}</div>
                                                            </div>
                                                            <div class="col-lg-9">
                                                                <div class="d-flex" id="appPermit-{{ $menu->menu_id }}">
                                                                    <label
                                                                        class="custom-control custom-checkbox mx-5 fw-20">
                                                                        <input type="checkbox"
                                                                            class="custom-control-input allow all-checkbox"
                                                                            onchange="appToggleAllPermissions(this, {{ $menu->menu_id }})">
                                                                        <span class="custom-control-label">All</span>
                                                                    </label>
                                                                    <label
                                                                        class="custom-control custom-checkbox mx-3 fw-20">
                                                                        <input type="checkbox"
                                                                            class="custom-control-input allow"
                                                                            name="appPermissions[{{ $menu->menu_id }}][create]"
                                                                            value="on"
                                                                            onchange="appGivePermit(this, {{ $menu->menu_id }})">
                                                                        <span class="custom-control-label">Create</span>
                                                                    </label>
                                                                    <label
                                                                        class="custom-control custom-checkbox mx-3 fw-20">
                                                                        <input type="checkbox"
                                                                            class="custom-control-input allow"
                                                                            name="appPermissions[{{ $menu->menu_id }}][read]"
                                                                            value="on"
                                                                            onchange="appGivePermit(this, {{ $menu->menu_id }})">
                                                                        <span class="custom-control-label">Read</span>
                                                                    </label>
                                                                    <label
                                                                        class="custom-control custom-checkbox mx-3 fw-20">
                                                                        <input type="checkbox"
                                                                            class="custom-control-input allow"
                                                                            name="appPermissions[{{ $menu->menu_id }}][update]"
                                                                            value="on"
                                                                            onchange="appGivePermit(this, {{ $menu->menu_id }})">
                                                                        <span class="custom-control-label">Update</span>
                                                                    </label>
                                                                    <label
                                                                        class="custom-control custom-checkbox mx-3 fw-20">
                                                                        <input type="checkbox"
                                                                            class="custom-control-input allow"
                                                                            name="appPermissions[{{ $menu->menu_id }}][delete]"
                                                                            value="on"
                                                                            onchange="appGivePermit(this, {{ $menu->menu_id }})">
                                                                        <span class="custom-control-label">Delete</span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-0">
                            <div class="d-flex">
                                <button type="button" class="btn btn-outline-danger  btn-sm mx-3"
                                    data-bs-dismiss="modal" id="appDisposeButton">Cancel</button>
                                <button type="submit" class="btn btn-outline-primary btn-sm"
                                    id="appContinueBtn">Submit</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        {{-- modal end --}}
    </div>
@endsection




@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            appAdjustModalBodyHeight();
            window.addEventListener("resize", appAdjustModalBodyHeight);
        });

        function appAdjustModalBodyHeight() {
            const headerHeight = document.querySelector('.modal-header').offsetHeight;
            const footerHeight = document.querySelector('.modal-footer').offsetHeight;
            const windowHeight = window.innerHeight;
            const modalBody = document.getElementById('appCustomModalBody');
            const maxHeight = windowHeight - headerHeight - footerHeight - 340; // Subtract 50px for adjustment

            modalBody.style.maxHeight = `${maxHeight}px`;
            modalBody.style.overflowY = 'auto';
        }

        function appToggleAllPermissions(element, menuId) {
            const isChecked = element.checked;
            const permissionGroup = document.getElementById('appPermit-' + menuId);
            const checkboxes = permissionGroup.querySelectorAll('input[type="checkbox"]:not(.all-checkbox)');

            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                checkbox.value = isChecked ? 'on' : 'off';
            });
        }

        function appGivePermit(element, menuId) {
            const permissionGroup = document.getElementById('appPermit-' + menuId);
            const allCheckbox = permissionGroup.querySelector('.all-checkbox');
            const checkboxes = permissionGroup.querySelectorAll('input[type="checkbox"]:not(.all-checkbox)');

            let allChecked = true;
            checkboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    allChecked = false;
                }
                checkbox.value = checkbox.checked ? 'on' : 'off';
            });

            allCheckbox.checked = allChecked;
        }

        function appToggleSelectAll(element) {
            const isChecked = element.checked;
            const accordion = document.getElementById('appMenuAccordion');
            const checkboxes = accordion.querySelectorAll('input[type="checkbox"]');

            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                checkbox.value = isChecked ? 'on' : 'off';
            });
        }

        document.getElementById('appRoleSelect').addEventListener('change', function() {
            const roleId = this.value;

            // Reset all checkboxes
            appResetCheckboxes();

            // Simulate dynamic change of permissionsData based on roleId
            fetch(`/admin/app-role/permission/role-wise/${roleId}`)
                .then(response => response.json())
                .then(data => {
                    const appPermissions = data.appPermissions;
                    Object.keys(appPermissions).forEach(menuId => {
                        const menuPermissions = appPermissions[menuId]; // Define menuPermissions here

                        const permissionGroup = document.getElementById('appPermit-' + menuId);
                        if (permissionGroup) {
                            permissionGroup.querySelectorAll('input[type="checkbox"]').forEach(
                                checkbox => {
                                    const nameParts = checkbox.name.split('[');
                                    if (nameParts.length >= 3) {
                                        const permissionType = nameParts[2].replace(']', '');
                                        checkbox.checked = menuPermissions[permissionType] === 'on';
                                        checkbox.value = menuPermissions[permissionType] === 'on' ?
                                            'on' : 'off';
                                    }
                                });

                            const allCheckbox = permissionGroup.querySelector('.all-checkbox');
                            if (allCheckbox) {
                                allCheckbox.checked = menuPermissions.create === 'on' && menuPermissions
                                    .read === 'on' && menuPermissions.update === 'on' && menuPermissions
                                    .delete === 'on';
                            }
                        } else {
                            console.error(`Permission group not found for menu ID ${menuId}`);
                        }
                    });


                })
                .catch(error => {
                    console.error('Error fetching role permissions:', error);
                });
        });

        function appResetCheckboxes() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"].allow');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        }

        function appToggleGroupSelectAll(element, groupName) {
            const isChecked = element.checked;
            const group = document.getElementById('collapse-' + groupName);
            const checkboxes = group.querySelectorAll('input[type="checkbox"]');

            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                checkbox.value = isChecked ? 'on' : 'off';
            });
        }
    </script>
@endsection
