<?php
    use App\Helpers\RolePermissionLogics;
    use Illuminate\Support\Facades\Auth;
    use App\Models\AppMenu;

    $user = Auth::user();
    $appMenu = new AppMenu();
    $permissions = new RolePermissionLogics();
?>
@section('script')
<script src="https://www.unpkg.com/datatable-customizer/assets/jquery.sumoselect.min.js"></script>
<script src="https://www.unpkg.com/datatable-customizer/assets/jquery.sumoselect.js"></script>
@if (session('denied'))
<script>
    Swal.fire({
        position: 'top-end',
        title: "Access Denied!",
        icon: 'warning',
        text: "{{session('denied')}}",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

</script>
@endif
@endsection
<div class="app-header header sticky">
    <div class="container-fluid main-container">
        <div class="d-flex">
            <a class="header-brand" href="{{ url('/dashboard') }}" style="text-align: center">
                <img src={{ asset('assets/logo/logo.png') }} class="header-brand-img desktop-main-logo"
                    alt="FixingDotslogo" style="height: 4rem;  margin: auto;">
                <img src={{ asset('assets/logo/logo_dark.png') }} class="header-brand-img dark-logo"
                    alt="FixingDotslogo" style="height: 4rem;  margin: auto;">
                <img src="{{ asset('assets/logo/logo.png') }}" class="header-brand-img mobile-logo"
                    alt="FixingDotslogo">
                <img src="{{ asset('assets/logo/logo_dark.png') }}" class="header-brand-img darkmobile-logo"
                    alt="FixingDotslogo">
            </a>
            <div class="app-sidebar__toggle" data-bs-toggle="sidebar">
                <a class="open-toggle" href="javascript:void(0);">
                    <i class="feather feather-menu"></i>
                </a>
                <a class="close-toggle" href="javascript:void(0);">
                    <i class="feather feather-x"></i>
                </a>
            </div>
            <div class="d-flex order-lg-1 my-auto ms-auto">
                {{-- <div class="d-flex me-auto">
                    <div class="me-3 mt-0 mt-sm-1 d-block text-center">
                        <h6 class="fs-18 mb-0"><b>Welcome to FixHR Admin Dashboard.</b></h6>
                        <p class="text-muted mt-0 fs-12">Your Lats Login is 26/08/2023 : 01:05 pm</p>
                    </div>
                </div> --}}
                <button class="navbar-toggler nav-link icon navresponsive-toggler vertical-icon ms-auto" type="button"
                    data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent-4"
                    aria-controls="navbarSupportedContent-4" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fe fe-more-vertical header-icons navbar-toggler-icon"></i>
                </button>
                <div class="mb-0 navbar navbar-expand-lg navbar-nav-right responsive-navbar navbar-dark p-0">
                    <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
                        <div class="d-flex ms-auto justify-content-start">
                            <div class="dropdown  d-flex">
                                <a class="nav-link icon theme-layout nav-link-bg layout-setting" id="themeToggle">
                                    <span class="light-layout"><i class="fe fe-sun"></i></span>
                                    <span class="dark-layout"><i class="fe fe-moon"></i></span>

                                </a>
                            </div>
                            <div class="dropdown header-flags">
                                <a class="nav-link icon" data-bs-toggle="dropdown">
                                    <img src={{ asset('assets/images/flags/flag-png/india.png') }} class="h-24"
                                        alt="img">
                                </a>
                            </div>
                            <div class="dropdown header-fullscreen">
                                <a class="nav-link icon full-screen-link">
                                    <i class="feather feather-maximize fullscreen-button fullscreen header-icons"></i>
                                    <i
                                        class="feather feather-minimize fullscreen-button exit-fullscreen header-icons"></i>
                                </a>
                            </div>
                            {{-- <div class="dropdown header-notify">
                                <a class="nav-link icon" data-bs-toggle="sidebar-right" data-bs-target=".sidebar-right">
                                    <i class="feather feather-bell header-icon"></i>
                                    <span class="bg-dot"></span>
                                </a>
                            </div> --}}
                            <div class="d-flex" style="transform: translateX(20px);">
                                <div class="dropdown profile-dropdown">
                                    <a href="javascript:void(0);" class="nav-link  ps-0 leading-none"
                                        data-bs-toggle="dropdown">
                                        {{-- <span>
                                            @php
                                                $imageFilePath = $user->fh_business->b_logo;

                                            @endphp
                                            <img style="background-color:transparent;"
                                                src="{{ $imageFilePath ? $imageFilePath : '' }}"
                                                alt="img" class="avatar avatar-md rounded-circle">
                                        </span> --}}
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow animated">
                                        <a class="dropdown-item d-flex" href="{{ url('/admin/settings/business') }}"> <i
                                                class="feather feather-user me-3 fs-16 my-auto"></i>
                                            <div class="mt-1">Profile</div>
                                        </a>
                                        <a class="dropdown-item d-flex" href="{{ url('/subscription') }}">
                                            <i class="feather feather-award me-3 fs-16 my-auto"></i>
                                            <div class="mt-1">Subscriptions</div>
                                        </a>
                                        {{-- <a class="dropdown-item d-flex" href="e{{url('/admin/settings/business')}}">
                                        <i class="feather feather-settings me-3 fs-16 my-auto"></i>
                                        <div class="mt-1">Settings</div>
                                        </a> --}}
                                        @if($permissions->check_route_permission('admin/role/permission',116) || $permissions->check_route_permission('admin/role/permission/save',115) || $permissions->check_route_permission('admin/role/permission/role-wise/{id}',115))
                                        <a class="dropdown-item d-flex"
                                            data-bs-toggle="modal" href="#empPermission">
                                            <i class="feather feather-user-check me-3 fs-16 my-auto"></i>
                                            <div class="mt-1">Role & Permissions</div>
                                        </a>
                                        @endif
                                        @if($permissions->check_route_permission('admin/app-role/permission',116) || $permissions->check_route_permission('admin/app-role/permission/save',115) || $permissions->check_route_permission('admin/app-role/permission/role-wise/{id}',115))
                                        <a class="dropdown-item d-flex"
                                            data-bs-toggle="modal" href="#empAppPermission">
                                            <i class="feather feather-settings me-3 fs-16 my-auto"></i>
                                            <div class="mt-1">App Permissions</div>
                                        </a>
                                        @endif
                                        <a class="dropdown-item d-flex" href="{{ url('/logout') }}">
                                            <i class="feather feather-power me-3 fs-16 my-auto"></i>
                                            <div class="mt-1">Log Out</div>
                                        </a>
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

<div class="modal fade" id="empPermission" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route("role.permission.store") }}" method="post" id="permissionForm">
            @csrf
            <div class="modal-content modal-content-demo">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2">Update Role - Permission</h4>
                    <button type="button" id="closeBtn" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <p class="form-label">User Roles</p>
                                <select class="form-select-md search_test" name="role_id" id="roleSelect" required>
                                    <option value="">Select Role</option>
                                    @foreach ($permissions->get_role_list() as $role)
                                        <option value="{{$role->role_id}}">{{$role->role_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="col-12 px-5">
                            <label class="custom-control custom-checkbox fw-20">
                                <input type="checkbox" class="custom-control-input allow select-all-checkbox" onchange="toggleSelectAll(this)">
                                <span class="custom-control-label">Select All</span>
                            </label>
                        </div>

                    </div>
                    <div class="row px-2" id="customModalBody">
                        <div class="accordion" id="menuAccordion">
                            @php
                                $menus = $permissions->get_menu_list();
                                $menuGroups = [];
                                foreach ($menus as $menu) {
                                    $menuGroups[$menu->menu_group][] = $menu;
                                }
                            @endphp

                            @foreach ($menuGroups as $groupName => $groupMenus)
                                <div class="accordion-item">
                                    <h4 class="accordion-header" id="heading-{{ Str::slug($groupName) }}">
                                        <button class="accordion-button custom-accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ Str::slug($groupName) }}" aria-expanded="true" aria-controls="collapse-{{ Str::slug($groupName) }}">
                                            {{ $groupName }}
                                        </button>
                                    </h4>
                                    <div id="collapse-{{ Str::slug($groupName) }}" class="accordion-collapse collapse show" aria-labelledby="heading-{{ Str::slug($groupName) }}" data-bs-parent="#menuAccordion">
                                        <div class="accordion-body">
                                            @foreach ($groupMenus as $menu)
                                                <div class="row p-1">
                                                    <div class="col-lg-3">
                                                        <div>{{ $menu->menu_name }}</div>
                                                    </div>
                                                    <div class="col-lg-9">
                                                        <div class="d-flex" id="permit-{{ $menu->menu_id }}">
                                                            <label class="custom-control custom-checkbox mx-5 fw-20">
                                                                <input type="checkbox" class="custom-control-input allow all-checkbox" onchange="toggleAllPermissions(this, {{ $menu->menu_id }})">
                                                                <span class="custom-control-label">All</span>
                                                            </label>
                                                            <label class="custom-control custom-checkbox mx-3 fw-20">
                                                                <input type="checkbox" class="custom-control-input allow" name="permissions[{{ $menu->menu_id }}][create]" value="on" onchange="givePermit(this, {{ $menu->menu_id }})">
                                                                <span class="custom-control-label">Create</span>
                                                            </label>
                                                            <label class="custom-control custom-checkbox mx-3 fw-20">
                                                                <input type="checkbox" class="custom-control-input allow" name="permissions[{{ $menu->menu_id }}][read]" value="on" onchange="givePermit(this, {{ $menu->menu_id }})">
                                                                <span class="custom-control-label">Read</span>
                                                            </label>
                                                            <label class="custom-control custom-checkbox mx-3 fw-20">
                                                                <input type="checkbox" class="custom-control-input allow" name="permissions[{{ $menu->menu_id }}][update]" value="on" onchange="givePermit(this, {{ $menu->menu_id }})">
                                                                <span class="custom-control-label">Update</span>
                                                            </label>
                                                            <label class="custom-control custom-checkbox mx-3 fw-20">
                                                                <input type="checkbox" class="custom-control-input allow" name="permissions[{{ $menu->menu_id }}][delete]" value="on" onchange="givePermit(this, {{ $menu->menu_id }})">
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
                        <button type="button" class="btn btn-outline-danger  btn-sm mx-3" data-bs-dismiss="modal" id="disposeButton">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary btn-sm" id="continueBtn">Continue</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="empAppPermission" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route("app.role.permission.store") }}" method="post" id="appPermissionForm">
            @csrf
            <div class="modal-content modal-content-demo">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2">Update App Role - Permission</h4>
                    <button type="button" id="appCloseBtn" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <p class="form-label">User Roles</p>
                                <select class="form-select-md search_test" name="role_id" id="appRoleSelect" required>
                                    <option value="">Select Role</option>
                                    @foreach ($permissions->get_role_list() as $role)
                                        <option value="{{$role->role_id}}">{{$role->role_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="col-12 px-5">
                            <label class="custom-control custom-checkbox fw-20">
                                <input type="checkbox" class="custom-control-input allow select-all-checkbox" onchange="appToggleSelectAll(this)">
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
                                    <button class="accordion-button custom-accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ Str::slug($groupName) }}" aria-expanded="true" aria-controls="collapse-{{ Str::slug($groupName) }}">
                                        {{ $groupName }}
                                    </button>
                                </h4>
                                <div id="collapse-{{ Str::slug($groupName) }}" class="accordion-collapse collapse show" aria-labelledby="heading-{{ Str::slug($groupName) }}" data-bs-parent="#menuAccordion">
                                    <div class="accordion-body">
                                        <div class="d-flex justify-content-end mb-3">
                                            <label class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input allow group-select-all" data-group="{{ Str::slug($groupName) }}" onchange="appToggleGroupSelectAll(this, '{{ Str::slug($groupName) }}')">
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
                                                        <label class="custom-control custom-checkbox mx-5 fw-20">
                                                            <input type="checkbox" class="custom-control-input allow all-checkbox" onchange="appToggleAllPermissions(this, {{ $menu->menu_id }})">
                                                            <span class="custom-control-label">All</span>
                                                        </label>
                                                        <label class="custom-control custom-checkbox mx-3 fw-20">
                                                            <input type="checkbox" class="custom-control-input allow" name="appPermissions[{{ $menu->menu_id }}][create]" value="on" onchange="appGivePermit(this, {{ $menu->menu_id }})">
                                                            <span class="custom-control-label">Create</span>
                                                        </label>
                                                        <label class="custom-control custom-checkbox mx-3 fw-20">
                                                            <input type="checkbox" class="custom-control-input allow" name="appPermissions[{{ $menu->menu_id }}][read]" value="on" onchange="appGivePermit(this, {{ $menu->menu_id }})">
                                                            <span class="custom-control-label">Read</span>
                                                        </label>
                                                        <label class="custom-control custom-checkbox mx-3 fw-20">
                                                            <input type="checkbox" class="custom-control-input allow" name="appPermissions[{{ $menu->menu_id }}][update]" value="on" onchange="appGivePermit(this, {{ $menu->menu_id }})">
                                                            <span class="custom-control-label">Update</span>
                                                        </label>
                                                        <label class="custom-control custom-checkbox mx-3 fw-20">
                                                            <input type="checkbox" class="custom-control-input allow" name="appPermissions[{{ $menu->menu_id }}][delete]" value="on" onchange="appGivePermit(this, {{ $menu->menu_id }})">
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
                        <button type="button" class="btn btn-outline-danger  btn-sm mx-3" data-bs-dismiss="modal" id="appDisposeButton">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary btn-sm" id="appContinueBtn">Continue</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>



<script>
    document.addEventListener("DOMContentLoaded", function() {
        adjustModalBodyHeight();
        window.addEventListener("resize", adjustModalBodyHeight);
    });

    function adjustModalBodyHeight() {
        const headerHeight = document.querySelector('.modal-header').offsetHeight;
        const footerHeight = document.querySelector('.modal-footer').offsetHeight;
        const windowHeight = window.innerHeight;
        const modalBody = document.getElementById('customModalBody');
        const maxHeight = windowHeight - headerHeight - footerHeight - 340; // Subtract 50px for adjustment

        modalBody.style.maxHeight = `${maxHeight}px`;
        modalBody.style.overflowY = 'auto';
    }

    function toggleAllPermissions(element, menuId) {
        const isChecked = element.checked;
        const permissionGroup = document.getElementById('permit-' + menuId);
        const checkboxes = permissionGroup.querySelectorAll('input[type="checkbox"]:not(.all-checkbox)');

        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
            checkbox.value = isChecked ? 'on' : 'off';
        });
    }

    function givePermit(element, menuId) {
        const permissionGroup = document.getElementById('permit-' + menuId);
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

    function toggleSelectAll(element) {
        const isChecked = element.checked;
        const accordion = document.getElementById('menuAccordion');
        const checkboxes = accordion.querySelectorAll('input[type="checkbox"]');

        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
            checkbox.value = isChecked ? 'on' : 'off';
        });
    }

    document.getElementById('roleSelect').addEventListener('change', function() {
        const roleId = this.value;

        // Reset all checkboxes
        resetCheckboxes();

        // Simulate dynamic change of permissionsData based on roleId
        fetch(`/admin/role/permission/role-wise/${roleId}`)
            .then(response => response.json())
            .then(data => {
                const permissions = data.permissions;
                Object.keys(permissions).forEach(menuId => {
                    const menuPermissions = permissions[menuId]; // Define menuPermissions here

                    const permissionGroup = document.getElementById('permit-' + menuId);
                    if (permissionGroup) {
                        permissionGroup.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                            const nameParts = checkbox.name.split('[');
                            if (nameParts.length >= 3) {
                                const permissionType = nameParts[2].replace(']', '');
                                checkbox.checked = menuPermissions[permissionType] === 'on';
                                checkbox.value = menuPermissions[permissionType] === 'on' ? 'on' : 'off';
                            }
                        });

                        const allCheckbox = permissionGroup.querySelector('.all-checkbox');
                        if (allCheckbox) {
                            allCheckbox.checked = menuPermissions.create === 'on' && menuPermissions.read === 'on' && menuPermissions.update === 'on' && menuPermissions.delete === 'on';
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
    function resetCheckboxes() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"].allow');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
    var themeToggle = document.getElementById('themeToggle');
    var accordionItems = document.querySelectorAll('.accordion-item');
    var container = document.querySelector('.modal-footer').closest('.modal-content');

    // Function to toggle classes
    function toggleClass(element, removeClass, addClass) {
        element.classList.remove(removeClass);
        element.classList.add(addClass);
    }

    // Apply the saved theme on page load
    function applyTheme(theme) {
        if (theme === 'dark-mode') {
            accordionItems.forEach(function (item) {
                toggleClass(item, 'light-mode', 'dark-mode');
            });
            toggleClass(container, 'light-mode', 'dark-mode');
        } else {
            accordionItems.forEach(function (item) {
                toggleClass(item, 'dark-mode', 'light-mode');
            });
            toggleClass(container, 'dark-mode', 'light-mode');
        }
    }

    // Get the saved theme from local storage
    var savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        applyTheme(savedTheme);
    }

    // Toggle the theme on button click
    themeToggle.addEventListener('click', function () {
        var isDarkMode = accordionItems.length > 0 && accordionItems[0].classList.contains('dark-mode');
        var newTheme = isDarkMode ? 'light-mode' : 'dark-mode';

        // Save the new theme to local storage
        localStorage.setItem('theme', newTheme);

        // Apply the new theme
        applyTheme(newTheme);
    });
});


</script>

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
                        permissionGroup.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                            const nameParts = checkbox.name.split('[');
                            if (nameParts.length >= 3) {
                                const permissionType = nameParts[2].replace(']', '');
                                checkbox.checked = menuPermissions[permissionType] === 'on';
                                checkbox.value = menuPermissions[permissionType] === 'on' ? 'on' : 'off';
                            }
                        });

                        const allCheckbox = permissionGroup.querySelector('.all-checkbox');
                        if (allCheckbox) {
                            allCheckbox.checked = menuPermissions.create === 'on' && menuPermissions.read === 'on' && menuPermissions.update === 'on' && menuPermissions.delete === 'on';
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


