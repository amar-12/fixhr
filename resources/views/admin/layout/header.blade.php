<?php
use App\Helpers\RolePermissionLogics;
use Illuminate\Support\Facades\Auth;
use App\Models\AppMenu;
use App\Models\Business;
use App\Models\Employee;
$user = Auth::user();
$appMenu = new AppMenu();
$permissions = new RolePermissionLogics();

$business = Business::where('b_id', $user->emp_b_id)->first();
$getBName = collect();

if ($business && $business->switch_emails_array) {
    $getBName = Employee::with('fh_business')
        ->whereIn('emp_email', $business->switch_emails_array)
        ->get()
        ->map(function ($emp) {
            if (!$emp->fh_business) return null;
            return [
                'b_id' => $emp->fh_business->b_id,
                'b_name' => $emp->fh_business->b_name,
                'b_unique_id' => $emp->fh_business->b_unique_id,
                'emp_email' => $emp->emp_email
            ];
        })
        ->filter()
        ->unique('b_id')
        ->values();
}
?>
{{-- @dd($permissions->get_menu_list()); --}}
@section('script')
    <script src="https://www.unpkg.com/datatable-customizer/assets/jquery.sumoselect.min.js"></script>
    <script src="https://www.unpkg.com/datatable-customizer/assets/jquery.sumoselect.js"></script>
    @if (session('denied'))
        <script>
            Swal.fire({
                position: 'top-end',
                title: "Access Denied!",
                icon: 'warning',
                text: "{{ session('denied') }}",
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
<style>
    .password-container {
        position: relative;
    }

    .toggle-password {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        cursor: pointer;
    }

    /*.toggle-password {
      background-color: #007bff !important;
      color: white;
    }*/

    /* Default / Light Theme */
        a.subscription-btn {
          background-color: #f0f0f0 !important; /* Light gray */
          color: #333 !important;               /* Dark text */
          padding: 8px 12px;
          border-radius: 4px;
          text-decoration: none;
          transition: all 0.3s ease;
        }

        a.subscription-btn:hover {
          background-color: #e0e0e0 !important;
        }

        /* Dark Theme */
        body.dark-theme a.subscription-btn {
          background-color: #333 !important;  /* Dark background */
          color: #f0f0f0 !important;          /* Light text */
        }

        body.dark-theme a.subscription-btn:hover {
          background-color: #444 !important;
        }


</style>
<div class="app-header header sticky">
    <div class="container-fluid main-container">
        <div class="d-flex">
            <a class="header-brand" href="{{ url('/dashboard') }}" style="text-align: center">
                <img src={{ asset('assets/logo/logo.png') }} class="header-brand-img desktop-main-logo"
                    alt="FixingDotslogo" style="height: 4rem;  margin: auto;">
                <img src={{ asset('assets/logo/logo_dark.png') }} class="header-brand-img dark-logo" alt="FixingDotslogo"
                    style="height: 4rem;  margin: auto;">
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
                            {{-- <div class="dropdown d-flex">
                                <div class="btn-list">
                                    <button type="button" class="btn btn-outline-primary" id="addAdhocComponentBtn" data-bs-toggle="modal"
                                        data-bs-target="#helpFeedbackModal">
                                        Help/Feedback
                                    </button>
                                </div>
                            </div> --}}
                            @if(isset($getBName) && $getBName->count())
                            <div class="dropdown d-flex">
                                <a class="nav-link icon" id="switchUser" data-bs-toggle="modal" data-bs-target="#switchUserModal">
                                    <i class="nav-icon mdi mdi-account-switch"></i>
                                </a>
                            </div>
                            @endif
                            <div class="dropdown  d-flex">
                                <a class="nav-link icon theme-layout nav-link-bg layout-setting" id="themeToggle">
                                    <span class="light-layout"><i class="fe fe-sun"></i></span>
                                    <span class="dark-layout"><i class="fe fe-moon"></i></span>
                                </a>
                            </div>
                            <div class="dropdown header-flags">
                                <a class="nav-link icon" data-bs-toggle="dropdown">
                                    <img src={{ asset('assets/images/flags/flag-png/support.png') }} class="h-24"
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
                                        <span>
                                            @php
                                                $imageFilePath = $user->fh_business->b_logo;
                                            @endphp
                                            {{-- <img style="background-color:transparent;"
                                                src="{{ $imageFilePath ? $imageFilePath : '' }}"
                                                alt="img" class="avatar avatar-md rounded-circle"> --}}
                                            <img style="height: 45px; width: 80px; background-color: transparent;"
                                                src="{{ $imageFilePath ? $imageFilePath : '' }}" alt="img">
                                        </span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow animated"
                                        style="min-width: 236px;">
                                        <a class="dropdown-item d-flex" href="{{ url('/admin/settings/business') }}"> <i
                                                class="feather feather-user me-3 fs-16 my-auto"></i>
                                            <div class="mt-1">Profile</div>
                                        </a>
                                        <a class="dropdown-item d-flex" data-bs-toggle="modal"
                                            href="#changePasswordModal" id="changePassword">
                                            <i class="feather feather-lock me-3 fs-16 my-auto"></i>
                                            <div class="mt-1">Change Password</div>
                                        </a>
                                        <a class="dropdown-item d-flex" href="{{ url('/two-factor-authentication') }}">
                                            <i class="feather feather-shield me-3 fs-16 my-auto"></i>
                                            <div class="mt-1">Two-Factor Authentication</div>
                                        </a>
                                        <a class="dropdown-item d-flex" href="{{ url('/subscriptions') }}">
                                            <i class="feather feather-award me-3 fs-16 my-auto"></i>
                                            <div class="mt-1">Subscriptions</div>
                                        </a>
                                        <a class="dropdown-item d-flex" href="{{ route('admin.settings.notifications') }}">
                                            <i class="feather feather-bell me-3 fs-16 my-auto"></i>
                                            <div class="mt-1">Notification Settings</div>
                                        </a>
                                        <!-- <div class="dropdown-item d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <i class="feather feather-bell me-3 fs-16 my-auto"></i>
                                                <div class="mt-1">App Notifications</div>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" style="margin-top: 7px" type="checkbox"
                                                    id="notification-toggle"
                                                    {{ isset($user) && ($user->emp_is_notification_enabled ?? 1) == 1 ? 'checked' : '' }}>
                                            </div>
                                        </div> -->
                                        {{-- <a class="dropdown-item d-flex" href="e{{url('/admin/settings/business')}}">
                                        <i class="feather feather-settings me-3 fs-16 my-auto"></i>
                                        <div class="mt-1">Settings</div>
                                        </a> --}}
                                        @if (
                                            (\App\Helpers\RolePermissionLogics::isSuperAdmin() &&
                                                \App\Helpers\RolePermissionLogics::businessHasActiveSubscription()) ||
                                                $permissions->check_route_permission('admin/role/permission', 116) ||
                                                $permissions->check_route_permission('admin/role/permission/save', 115) ||
                                                $permissions->check_route_permission('admin/role/permission/role-wise/{id}', 115))
                                            <a class="dropdown-item d-flex" data-bs-toggle="modal" href="#empPermission"
                                                id="assignPermissionBtn">
                                                <i class="feather feather-user-check me-3 fs-16 my-auto"></i>
                                                <div class="mt-1">Role & Permissions</div>
                                            </a>
                                        @endif
                                        @if (
                                            (\App\Helpers\RolePermissionLogics::isSuperAdmin() &&
                                                \App\Helpers\RolePermissionLogics::businessHasActiveSubscription()) ||
                                                $permissions->check_route_permission('admin/app-role/permission', 116) ||
                                                $permissions->check_route_permission('admin/app-role/permission/save', 115) ||
                                                $permissions->check_route_permission('admin/app-role/permission/role-wise/{id}', 115))
                                            <a class="dropdown-item d-flex" data-bs-toggle="modal"
                                                href="#empAppPermission">
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
<div class="modal fade" id="switchUserModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Switch Business</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
            </div>
            <form id="switchForm">
                <div class="modal-body">
                    <label>Select Name</label>
                    <select class="form-control" id="emailSelect" name="email">
                        <option value="" disabled selected>Select Business</option>

                        @forelse($getBName as $item)
                            <option value="{{ $item['emp_email'] }}">
                            {{ $item['b_name'] }} ({{ $item['b_unique_id'] }})
                        </option>
                        @empty
                            <option value="">No business found</option>
                        @endforelse
                    </select>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" id="switchUserBtn">Switch</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="helpFeedbackModal" tabindex="-1" aria-labelledby="helpFeedbackModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpFeedbackModalLabel">Help / Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="feedbackTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="feedbackTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="feedbackDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="feedbackDescription" name="description" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-outline-primary">Submit Feedback</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    (function() {
        var toggle = document.getElementById('notification-toggle');
        if (!toggle) return;
        toggle.addEventListener('change', function() {
            var prev = this.checked;
            this.disabled = true;
            // console.log("[DEBUG] Sending request... enabled =", this.checked ? 1 : 0);
            fetch("{{ route('settings.notifications.toggle') }}", {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'enabled=' + (this.checked ? 1 : 0) // ðŸ‘ˆ simple key=value
                })
                .then(function(r) {
                    //   console.log("[DEBUG] Response received:", r);
                    if (!r.ok) {
                        console.error("[ERROR] Response not OK:", r.status, r.statusText);
                        throw new Error('Response not OK');
                    }
                    return r.json();
                })
                .then(function(resp) {
                    //   console.log("[DEBUG] Parsed JSON:", resp);
                    if (resp && resp.status === 'success') {
                        toggle.checked = resp.enabled === 1;
                    } else {
                        console.error("[ERROR] Server returned failure:", resp);
                        toggle.checked = prev;
                    }
                })
                .catch(function(err) {
                    console.error("[CATCH] Request failed:", err);
                    toggle.checked = prev;
                })
                .finally(function() {
                    toggle.disabled = false;
                    //   console.log("[DEBUG] Toggle request finished.");
                });
        });
    })();
</script>
<div class="modal fade" id="empPermission" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('role.permission.store') }}" method="post" id="permissionForm">
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
                                        <option value="{{ $role->role_id }}">{{ $role->role_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <hr>
                        <!-- <div class="col-12 px-5">
                            <label class="custom-control custom-checkbox fw-20">
                                <input type="checkbox" class="custom-control-input allow select-all-checkbox"
                                    onchange="toggleSelectAll(this)">
                                <span class="custom-control-label">Select All</span>
                            </label>
                        </div> -->
                        <div class="col-12 px-5">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <!-- Select All -->
                                <label class="custom-control custom-checkbox fw-20 mb-0">
                                    <input type="checkbox" class="custom-control-input allow select-all-checkbox"
                                        onchange="toggleSelectAll(this)">
                                    <span class="custom-control-label">Select All</span>
                                </label>
                                <!-- Export Button -->
                                <div class="dropdown mt-2 mt-md-0">
                                    <button class="btn btn-primary dropdown-toggle" type="button"
                                        id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-download me-2"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-export" aria-labelledby="exportDropdown">
                                        <li><a class="dropdown-item" href="#" data-export="csv">CSV</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="excel">Excel</a>
                                        </li>
                                        <li><a class="dropdown-item" href="#" data-export="pdf">PDF</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="copy">Copy</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="print">Print</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
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
                                @php
                                    Log::info('Menu Group: ' . $groupName); // Debugging line
                                @endphp
                                <div class="accordion-item">
                                    <h4 class="accordion-header" id="heading-{{ Str::slug($groupName) }}">
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
                                            @foreach ($groupMenus as $menu)
                                                <div class="row p-1">
                                                    <div class="col-lg-3">
                                                        <div>{{ $menu->menu_name }}</div>
                                                    </div>
                                                    <div class="col-lg-9">
                                                        <div class="d-flex" id="permit-{{ $menu->menu_id }}">
                                                            <label class="custom-control custom-checkbox mx-5 fw-20">
                                                                <input type="checkbox"
                                                                    class="custom-control-input allow all-checkbox"
                                                                    onchange="toggleAllPermissions(this, {{ $menu->menu_id }})">
                                                                <span class="custom-control-label">All</span>
                                                            </label>
                                                            <label class="custom-control custom-checkbox mx-3 fw-20">
                                                                <input type="checkbox"
                                                                    class="custom-control-input allow"
                                                                    name="permissions[{{ $menu->menu_id }}][create]"
                                                                    value="on"
                                                                    onchange="givePermit(this, {{ $menu->menu_id }})">
                                                                <span class="custom-control-label">Create</span>
                                                            </label>
                                                            <label class="custom-control custom-checkbox mx-3 fw-20">
                                                                <input type="checkbox"
                                                                    class="custom-control-input allow"
                                                                    name="permissions[{{ $menu->menu_id }}][read]"
                                                                    value="on"
                                                                    onchange="givePermit(this, {{ $menu->menu_id }})">
                                                                <span class="custom-control-label">Read</span>
                                                            </label>
                                                            <label class="custom-control custom-checkbox mx-3 fw-20">
                                                                <input type="checkbox"
                                                                    class="custom-control-input allow"
                                                                    name="permissions[{{ $menu->menu_id }}][update]"
                                                                    value="on"
                                                                    onchange="givePermit(this, {{ $menu->menu_id }})">
                                                                <span class="custom-control-label">Update</span>
                                                            </label>
                                                            <label class="custom-control custom-checkbox mx-3 fw-20">
                                                                <input type="checkbox"
                                                                    class="custom-control-input allow"
                                                                    name="permissions[{{ $menu->menu_id }}][delete]"
                                                                    value="on"
                                                                    onchange="givePermit(this, {{ $menu->menu_id }})">
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
                        <button type="button" class="btn btn-outline-danger  btn-sm mx-3" data-bs-dismiss="modal"
                            id="disposeButton">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary btn-sm"
                            id="continueBtn">Continue</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="empAppPermission" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('app.role.permission.store') }}" method="post" id="appPermissionForm">
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
                        <!-- <div class="col-12 px-5">
                            <label class="custom-control custom-checkbox fw-20">
                                <input type="checkbox" class="custom-control-input allow select-all-checkbox"
                                    onchange="appToggleSelectAll(this)">
                                <span class="custom-control-label">Select All</span>
                            </label>
                        </div> -->
                        <div class="col-12 px-5">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <!-- Select All -->
                                <label class="custom-control custom-checkbox fw-20">
                                    <input type="checkbox" class="custom-control-input allow select-all-checkbox"
                                        onchange="appToggleSelectAll(this)">
                                    <span class="custom-control-label">Select All</span>
                                </label>
                                <!-- Export Button -->
                                <div class="dropdown mt-2 mt-md-0">
                                    <button class="btn btn-primary dropdown-toggle" type="button"
                                        id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-download me-2"></i> Export As
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-export" aria-labelledby="exportDropdown">
                                        <li><a class="dropdown-item" href="#" data-export="csv">CSV</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="excel">Excel</a>
                                        </li>
                                        <li><a class="dropdown-item" href="#" data-export="pdf">PDF</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="copy">Copy</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="print">Print</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row px-2" id="appCustomModalBody">
                        <div class="accordion" id="appMenuAccordion">
                            @php
                                $appMenus = \App\Helpers\RolePermissionLogics::get_plan_app_menus();
                                $menuGroups = [];
                                foreach ($appMenus as $menu) {
                                    $menuGroups[$menu->menu_group][] = $menu;
                                }
                            @endphp
                            @foreach ($menuGroups as $groupName => $groupMenus)
                                <div class="accordion-item">
                                    <h4 class="accordion-header" id="appHeading-{{ Str::slug($groupName) }}">
                                        <button class="accordion-button custom-accordion-button" type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapse-{{ Str::slug($groupName) }}"
                                            aria-expanded="true">
                                            {{ $groupName }}
                                        </button>
                                    </h4>
                                    <div id="collapse-{{ Str::slug($groupName) }}"
                                        class="accordion-collapse collapse show">
                                        <div class="accordion-body">
                                            <div class="d-flex justify-content-end mb-3">
                                                <label class="custom-control custom-checkbox">
                                                    <input type="checkbox"
                                                        class="custom-control-input allow group-select-all"
                                                        onchange="appToggleGroupSelectAll(this, '{{ Str::slug($groupName) }}')">
                                                    <span class="custom-control-label">Select All</span>
                                                </label>
                                            </div>
                                            @foreach ($groupMenus as $menu)
                                                <div class="row p-1">
                                                    <div class="col-lg-3">
                                                        {{ $menu->menu_name }}
                                                    </div>

                                                    <div class="col-lg-9">
                                                        <div class="d-flex" id="appPermit-{{ $menu->menu_id }}">

                                                            <!-- ALL PERMISSIONS -->
                                                            <label class="custom-control custom-checkbox mx-5 fw-20">
                                                                <input type="checkbox"
                                                                    class="custom-control-input allow all-checkbox"
                                                                    onchange="appToggleAllPermissions(this, {{ $menu->menu_id }})">
                                                                <span class="custom-control-label">All</span>
                                                            </label>

                                                            <!-- CREATE -->
                                                            <label class="custom-control custom-checkbox mx-3 fw-20">
                                                                <input type="checkbox"
                                                                    class="custom-control-input allow"
                                                                    name="appPermissions[{{ $menu->menu_id }}][create]"
                                                                    value="on">
                                                                <span class="custom-control-label">Create</span>
                                                            </label>

                                                            <!-- READ -->
                                                            <label class="custom-control custom-checkbox mx-3 fw-20">
                                                                <input type="checkbox"
                                                                    class="custom-control-input allow"
                                                                    name="appPermissions[{{ $menu->menu_id }}][read]"
                                                                    value="on">
                                                                <span class="custom-control-label">Read</span>
                                                            </label>

                                                            <!-- UPDATE -->
                                                            <label class="custom-control custom-checkbox mx-3 fw-20">
                                                                <input type="checkbox"
                                                                    class="custom-control-input allow"
                                                                    name="appPermissions[{{ $menu->menu_id }}][update]"
                                                                    value="on">
                                                                <span class="custom-control-label">Update</span>
                                                            </label>

                                                            <!-- DELETE -->
                                                            <label class="custom-control custom-checkbox mx-3 fw-20">
                                                                <input type="checkbox"
                                                                    class="custom-control-input allow"
                                                                    name="appPermissions[{{ $menu->menu_id }}][delete]"
                                                                    value="on">
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
                        <button type="button" class="btn btn-outline-danger  btn-sm mx-3" data-bs-dismiss="modal"
                            id="appDisposeButton">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary btn-sm"
                            id="appContinueBtn">Continue</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordLabel"
    aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                    <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    @csrf
                    <div class="mb-3">
                        <label for="current-password" class="form-label">Current Password <span
                                class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" id="current-password" name="current_password"
                                class="form-control pe-5" placeholder="Enter your current password" minlength="8"
                                maxlength="20" required>
                            <button type="button"
                                class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y me-2 toggle-password"
                                data-target="current-password" tabindex="-1"><i
                                    class="feather-eye-off fs-6"></i></button>
                        </div>
                        <div class="error-message text-danger mt-1" id="current-password-error"></div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <!-- New Password -->
                        <div class="flex-fill">
                            <label for="new-password" class="form-label">New Password <span
                                    class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="password" id="new-password" name="new_password"
                                    class="form-control pe-5" placeholder="Enter new password" minlength="8"
                                    maxlength="20" required>
                                <button type="button"
                                    class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y me-2 toggle-password"
                                    data-target="new-password" tabindex="-1">
                                    <i class="feather-eye-off fs-6"></i>
                                </button>
                            </div>
                            <div class="error-message text-danger mt-1" id="new-password-error"></div>
                        </div>
                        <!-- Confirm New Password -->
                        <div class="flex-fill">
                            <label for="confirm-password" class="form-label">Confirm New Password <span
                                    class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="password" name="new_password_confirmation" id="confirm-password"
                                    class="form-control pe-5" placeholder="Confirm new password" minlength="8"
                                    maxlength="20" required>
                                <button type="button"
                                    class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y me-2 toggle-password"
                                    data-target="confirm-password" tabindex="-1">
                                    <i class="feather-eye-off fs-6"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="password-requirements mt-2">
                            <div class="requirement" id="length-requirement"><span class="requirement-icon">○</span>
                                At least 8 characters</div>
                            <div class="requirement" id="uppercase-requirement"><span
                                    class="requirement-icon">○</span> At least one uppercase letter</div>
                            <div class="requirement" id="lowercase-requirement"><span
                                    class="requirement-icon">○</span> At least one lowercase letter</div>
                            <div class="requirement" id="number-requirement"><span class="requirement-icon">○</span>
                                At least one number</div>
                            <div class="requirement" id="special-requirement"><span class="requirement-icon">○</span>
                                At least one special character</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="changePasswordForm" class="btn btn-outline-primary">Update</button>
            </div>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        // Adjust Modal Body Height
        function adjustModalBodyHeight() {
            const headerHeight = $('.modal-header').outerHeight() || 0;
            const footerHeight = $('.modal-footer').outerHeight() || 0;
            const windowHeight = $(window).height();
            const maxHeight = windowHeight - headerHeight - footerHeight - 340;
            $('#customModalBody').css({
                'max-height': maxHeight + 'px',
                'overflow-y': 'auto'
            });
        }

        function appAdjustModalBodyHeight() {
            const headerHeight = $('.modal-header').outerHeight() || 0;
            const footerHeight = $('.modal-footer').outerHeight() || 0;
            const windowHeight = $(window).height();
            const maxHeight = windowHeight - headerHeight - footerHeight - 340;
            $('#appCustomModalBody').css({
                'max-height': maxHeight + 'px',
                'overflow-y': 'auto'
            });
        }
        adjustModalBodyHeight();
        appAdjustModalBodyHeight();
        $(window).on('resize', function() {
            adjustModalBodyHeight();
            appAdjustModalBodyHeight();
        });
        // Toggle All Permission (Web)
        window.toggleAllPermissions = function(element, menuId) {
            const isChecked = $(element).is(':checked');
            $(`#permit-${menuId} input[type="checkbox"]:not(.all-checkbox)`).each(function() {
                $(this).prop('checked', isChecked).val(isChecked ? 'on' : 'off');
            });
        };
        // Toggle All Permission (App)
        window.appToggleAllPermissions = function(element, menuId) {
            const isChecked = $(element).is(':checked');
            $(`#appPermit-${menuId} input[type="checkbox"]:not(.all-checkbox)`).each(function() {
                $(this).prop('checked', isChecked).val(isChecked ? 'on' : 'off');
            });
        };
        // Give Individual Permission (Web)
        window.givePermit = function(element, menuId) {
            const $group = $(`#permit-${menuId}`);
            const checkboxes = $group.find('input[type="checkbox"]:not(.all-checkbox)');
            let allChecked = true;
            checkboxes.each(function() {
                if (!$(this).is(':checked')) {
                    allChecked = false;
                }
                $(this).val($(this).is(':checked') ? 'on' : 'off');
            });
            $group.find('.all-checkbox').prop('checked', allChecked);
        };
        // Give Individual Permission (App)
        window.appGivePermit = function(element, menuId) {
            const $group = $(`#appPermit-${menuId}`);
            const checkboxes = $group.find('input[type="checkbox"]:not(.all-checkbox)');
            let allChecked = true;
            checkboxes.each(function() {
                if (!$(this).is(':checked')) {
                    allChecked = false;
                }
                $(this).val($(this).is(':checked') ? 'on' : 'off');
            });
            $group.find('.all-checkbox').prop('checked', allChecked);
        };
        // Select All Global (Web)
        window.toggleSelectAll = function(element) {
            const isChecked = $(element).is(':checked');
            $('#menuAccordion input[type="checkbox"]').each(function() {
                $(this).prop('checked', isChecked).val(isChecked ? 'on' : 'off');
            });
        };
        // Select All Global (App)
        window.appToggleSelectAll = function(element) {
            const isChecked = $(element).is(':checked');
            $('#appMenuAccordion input[type="checkbox"]').each(function() {
                $(this).prop('checked', isChecked).val(isChecked ? 'on' : 'off');
            });
        };
        // Reset All Checkboxes
        function resetCheckboxes() {
            $('input[type="checkbox"].allow').prop('checked', false);
        }

        function appResetCheckboxes() {
            $('input[type="checkbox"].allow').prop('checked', false);
        }
        // Role Change (Web)
        $('#roleSelect').on('change', function() {
            const roleId = $(this).val();
            resetCheckboxes();
            console.log(roleId);
            $.get(`/admin/role/permission/role-wise/${roleId}`, function(data) {
                console.log(data);
                const permissions = data.permissions;
                $.each(permissions, function(menuId, menuPermissions) {
                    const $group = $(`#permit-${menuId}`);
                    if ($group.length) {
                        $group.find('input[type="checkbox"]').each(function() {
                            const name = $(this).attr('name');
                            const type = name ? name.split('[')[2]?.replace(']',
                                '') : null;
                            if (type) {
                                const checked = menuPermissions[type] === 'on';
                                $(this).prop('checked', checked).val(checked ?
                                    'on' : 'off');
                            }
                        });
                        const allChecked = menuPermissions.create === 'on' &&
                            menuPermissions.read === 'on' &&
                            menuPermissions.update === 'on' &&
                            menuPermissions.delete === 'on';
                        $group.find('.all-checkbox').prop('checked', allChecked);
                    }
                });
            });
        });
        // Role Change (App)
        $('#appRoleSelect').on('change', function() {
            const roleId = $(this).val();
            appResetCheckboxes();
            $.get(`/admin/app-role/permission/role-wise/${roleId}`, function(data) {
                const permissions = data.appPermissions;
                $.each(permissions, function(menuId, menuPermissions) {
                    const $group = $(`#appPermit-${menuId}`);
                    if ($group.length) {
                        $group.find('input[type="checkbox"]').each(function() {
                            const name = $(this).attr('name');
                            const type = name ? name.split('[')[2]?.replace(']',
                                '') : null;
                            if (type) {
                                const checked = menuPermissions[type] === 'on';
                                $(this).prop('checked', checked).val(checked ?
                                    'on' : 'off');
                            }
                        });
                        const allChecked = menuPermissions.create === 'on' &&
                            menuPermissions.read === 'on' &&
                            menuPermissions.update === 'on' &&
                            menuPermissions.delete === 'on';
                        $group.find('.all-checkbox').prop('checked', allChecked);
                    }
                });
            });
        });
        // Theme Toggle
        const $themeToggle = $('#themeToggle');
        const $accordionItems = $('.accordion-item');
        const $modalContent = $('.modal-footer').closest('.modal-content');

        function applyTheme(theme) {
            if (theme === 'dark-mode') {
                $accordionItems.removeClass('light-mode').addClass('dark-mode');
                $modalContent.removeClass('light-mode').addClass('dark-mode');
            } else {
                $accordionItems.removeClass('dark-mode').addClass('light-mode');
                $modalContent.removeClass('dark-mode').addClass('light-mode');
            }
        }
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            applyTheme(savedTheme);
        }
        $themeToggle.on('click', function() {
            const isDark = $accordionItems.first().hasClass('dark-mode');
            const newTheme = isDark ? 'dark-mode' : 'light-mode';
            console.log(newTheme);
            localStorage.setItem('theme', newTheme);
            applyTheme(newTheme);
        });
        // Group Select All in Accordion (App)
        window.appToggleGroupSelectAll = function(element, groupName) {
            const isChecked = $(element).is(':checked');
            $(`#collapse-${groupName} input[type="checkbox"]`).each(function() {
                $(this).prop('checked', isChecked).val(isChecked ? 'on' : 'off');
            });
        };
    });

    $('#switchUserBtn').click(function (e) {
        e.preventDefault();

        let email = $('#emailSelect').val();

        if (!email) {
            alert('Please select business');
            return;
        }

        $.ajax({
            url: '{{ route("switch.user") }}',
            type: 'POST',
            data: {
                email: email,
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {

                if (res.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    setTimeout(() => {
                        window.location.href = res.redirect;
                    }, 1500);

                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function (xhr) {

                let message = 'Switch failed!';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire('Error', message, 'error');
            }
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const requirementsSection = document.querySelector('.password-requirements');
        requirementsSection.style.display = 'none';
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i'); // get <i> inside the button
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('feather-eye-off');
                    icon.classList.add('feather-eye');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('feather-eye');
                    icon.classList.add('feather-eye-off');
                }
            });
        });
        const newPasswordInput = document.getElementById('new-password');
        const requirements = {
            length: {
                element: document.getElementById('length-requirement'),
                regex: /.{8,}/,
                shown: false
            },
            uppercase: {
                element: document.getElementById('uppercase-requirement'),
                regex: /[A-Z]/,
                shown: false
            },
            lowercase: {
                element: document.getElementById('lowercase-requirement'),
                regex: /[a-z]/,
                shown: false
            },
            number: {
                element: document.getElementById('number-requirement'),
                regex: /[0-9]/,
                shown: false
            },
            special: {
                element: document.getElementById('special-requirement'),
                regex: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/,
                shown: false
            }
        };
        // Hide all requirements initially
        Object.values(requirements).forEach(req => {
            req.element.style.display = 'none';
        });
        // Show the requirements section when the input field is focused
        // newPasswordInput.addEventListener('focus', function() {
        //     requirementsSection.style.display = 'block';
        // });
        newPasswordInput.addEventListener('input', validatePassword);

        function validatePassword() {
            const password = newPasswordInput.value;
            let isValid = true;
            // If password field is empty, hide the requirements section
            if (password.length === 0) {
                requirementsSection.style.display = 'none';
                return false;
            } else {
                requirementsSection.style.display = 'block';
            }
            // Check each requirement
            Object.keys(requirements).forEach(req => {
                const requirement = requirements[req];
                const passes = requirement.regex.test(password);
                // Show this requirement if it's not already shown or if it fails
                if (!requirement.shown || !passes) {
                    requirement.element.style.display = 'block';
                    requirement.shown = true;
                }
                // Update the UI
                requirement.element.querySelector('.requirement-icon').textContent = passes ? '✓' : '○';
                // Use text-success for passing requirements, text-danger for failing ones
                requirement.element.classList.remove('text-success', 'text-danger', 'text-muted');
                requirement.element.classList.add(passes ? 'text-success' : 'text-danger');
                if (!passes) isValid = false;
            });
            return isValid;
        }
        // Form submission handling
        const form = document.getElementById('changePasswordForm');
        const currentPasswordInput = document.getElementById('current-password');
        const confirmPasswordInput = document.getElementById('confirm-password');
        // Error message elements
        const currentPasswordError = document.getElementById('current-password-error');
        const newPasswordError = document.getElementById('new-password-error');
        const confirmPasswordError = document.getElementById('confirm-password-error');
        $('#changePasswordForm').submit(function(e) {
            e.preventDefault();
            let formData = $(this).serialize();
            $.ajax({
                url: '{{ route('change.business.password') }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.success,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = response.redirect_url;
                        });
                    } else if (response.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error,
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong. Please try again.',
                    });
                }
            });
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const exportButtons = document.querySelectorAll('[data-export]');
        exportButtons.forEach(btn => {
            btn.addEventListener("click", function(e) {
                e.preventDefault();
                const type = btn.dataset.export;
                const isAppModal = document.querySelector("#empAppPermission.show") !== null;
                const roleSelect = document.getElementById(isAppModal ? "appRoleSelect" :
                    "roleSelect");
                if (!roleSelect) return;
                const roleId = roleSelect.value;
                if (!roleId) {
                    alert("Please select a role first.");
                    return;
                }
                const roleName = roleSelect.options[roleSelect.selectedIndex].text;
                const prefix = isAppModal ? "appPermissions" : "permissions";
                const permitPrefix = isAppModal ? "appPermit-" : "permit-";
                // ---- Collect selected permissions ----
                const selectedPermissions = {};
                document.querySelectorAll(`.allow[name^="${prefix}["]`).forEach((checkbox) => {
                    const match = checkbox.name.match(new RegExp(
                        `${prefix}\\[(\\d+)\\]\\[(\\w+)\\]`));
                    if (match) {
                        const menuId = match[1];
                        const action = match[2];
                        if (!selectedPermissions[menuId]) {
                            selectedPermissions[menuId] = {
                                create: false,
                                read: false,
                                update: false,
                                delete: false
                            };
                        }
                        if (checkbox.checked) selectedPermissions[menuId][action] =
                            true;
                    }
                });
                if (Object.keys(selectedPermissions).length === 0) {
                    alert("No permissions selected for this role.");
                    return;
                }
                // ---- Get menu & module names ----
                const menuData = {};
                document.querySelectorAll(`[id^="${permitPrefix}"]`).forEach((div) => {
                    const menuId = div.id.replace(permitPrefix, "");
                    const row = div.closest(".row");
                    const menuNameElem = row.querySelector(".col-lg-3 div");
                    const moduleElem = row.closest(".accordion-item")?.querySelector(
                        ".accordion-button");
                    if (menuNameElem) {
                        menuData[menuId] = {
                            name: menuNameElem.textContent.trim(),
                            module: moduleElem ? moduleElem.textContent.trim() :
                                "N/A"
                        };
                    }
                });
                // ---- Build final structured data ----
                const data = [];
                Object.keys(selectedPermissions).forEach(menuId => {
                    const perms = selectedPermissions[menuId];
                    const info = menuData[menuId] || {
                        name: `Menu #${menuId}`,
                        module: "N/A"
                    };
                    data.push({
                        module: info.module,
                        menu: info.name,
                        create: perms.create ? "✔" : "",
                        read: perms.read ? "✔" : "",
                        update: perms.update ? "✔" : "",
                        delete: perms.delete ? "✔" : ""
                    });
                });
                // Sort module-wise
                data.sort((a, b) => a.module.localeCompare(b.module));
                // Build rows
                const rows = [
                    ["S.No.", "Module", "Menu", "Create", "Read", "Update", "Delete"]
                ];
                data.forEach((item, i) => {
                    rows.push([i + 1, item.module, item.menu, item.create, item.read,
                        item.update, item.delete
                    ]);
                });
                // Export actions
                switch (type) {
                    case "csv":
                        downloadCSV(rows, roleName);
                        break;
                    case "excel":
                        downloadExcel(rows, roleName);
                        break;
                    case "pdf":
                        downloadPDF(rows, roleName);
                        break;
                    case "copy":
                        copyToClipboard(rows, roleName);
                        break;
                    case "print":
                        printTable(roleName, rows);
                        break;
                }
            });
        });
        // =========================
        // 🔹 EXPORT FUNCTIONS
        // =========================
        // inside downloadCSV()
        function downloadCSV(rows, roleName) {
            const heading = `Permissions for Role: ${roleName}\n\n`;
            // Force UTF-8 BOM for Excel
            const csvContent = "\uFEFF" + heading + rows.map(r => r.map(cell => `"${cell}"`).join(",")).join(
                "\n");
            const blob = new Blob([csvContent], {
                type: "text/csv;charset=utf-8;"
            });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = `${roleName}_permissions.csv`;
            link.click();
        }

        function downloadExcel(rows, roleName) {
            let html = `
            <h3 style="text-align:left;">Permissions for Role: ${roleName}</h3>
            <table border="1" style="border-collapse:collapse;font-family:Arial;font-size:13px;">
                <thead style="background-color:#e0e0e0;font-weight:bold;">
                    <tr>
                        <th>S.No.</th>
                        <th>Module</th>
                        <th>Menu</th>
                        <th>Create</th>
                        <th>Read</th>
                        <th>Update</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
        `;
            rows.slice(1).forEach(r => {
                html +=
                    `<tr><td>${r[0]}</td><td>${r[1]}</td><td>${r[2]}</td><td>${r[3]}</td><td>${r[4]}</td><td>${r[5]}</td><td>${r[6]}</td></tr>`;
            });
            html += "</tbody></table>";
            const blob = new Blob([`\uFEFF${html}`], {
                type: "application/vnd.ms-excel"
            });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = `${roleName}_permissions.xls`;
            link.click();
        }
        async function downloadPDF(rows, roleName) {
            const {
                jsPDF
            } = window.jspdf || await new Promise(resolve => {
                const s = document.createElement("script");
                s.src = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js";
                s.onload = () => resolve(window.jspdf);
                document.body.appendChild(s);
            });
            const doc = new jsPDF({
                orientation: "landscape"
            });
            // ✅ Load Unicode-compatible font (Noto Sans)
            const fontUrl =
                "https://raw.githubusercontent.com/google/fonts/main/ofl/notosans/NotoSans-Regular.ttf";
            const fontData = await fetch(fontUrl).then(res => res.arrayBuffer());
            const uint8 = new Uint8Array(fontData);
            let binary = "";
            for (let i = 0; i < uint8.length; i++) binary += String.fromCharCode(uint8[i]);
            const base64Font = btoa(binary);
            doc.addFileToVFS("NotoSans-Regular.ttf", base64Font);
            doc.addFont("NotoSans-Regular.ttf", "NotoSans", "normal");
            doc.setFont("NotoSans");
            const pageWidth = doc.internal.pageSize.width;
            const startX = 10;
            let y = 25;
            doc.setFontSize(14);
            doc.text(`Permissions for Role: ${roleName}`, startX, 15);
            // ✅ Table Header
            const headers = ["S.No.", "Module", "Menu", "Create", "Read", "Update", "Delete"];
            const colWidths = [10, 80, 70, 15, 15, 15, 15];
            doc.setFillColor(220, 220, 220);
            doc.rect(startX, y, pageWidth - 20, 10, "F");
            doc.setFontSize(11);
            let x = startX;
            headers.forEach((h, i) => {
                doc.text(h, x + 2, y + 7);
                x += colWidths[i];
            });
            // ✅ Table Body
            y += 10;
            rows.slice(1).forEach((r) => {
                let x = startX;
                r.forEach((cell, i) => {
                    // 👉 Convert all checkmark symbols to “Yes”
                    const text = (cell === "✔" || cell === "✓" || cell === "âœ”") ? "Yes" :
                        (cell || "");
                    doc.text(text.toString(), x + 2, y + 7);
                    x += colWidths[i];
                });
                doc.rect(startX, y, pageWidth - 20, 10);
                y += 10;
                // ✅ Handle page break
                if (y > 180) {
                    doc.addPage();
                    y = 25;
                }
            });
            doc.save(`${roleName}_permissions.pdf`);
        }

        function copyToClipboard(rows, roleName) {
            const heading = `Permissions for Role: ${roleName}\n\n`;
            const text = heading + rows.map(r => r.join("\t")).join("\n");
            navigator.clipboard.writeText(text)
                .then(() => alert("Copied to clipboard!"))
                .catch(() => alert("Copy failed."));
        }

        function printTable(roleName, rows) {
            let printHTML = `
            <h2 style="margin-bottom:15px;">Permissions for Role: ${roleName}</h2>
            <table border="1" cellpadding="5" cellspacing="0" style="width:100%;border-collapse:collapse;">
                <thead style="background:#e0e0e0;">
                    <tr>
                        <th>S.No.</th>
                        <th>Module</th>
                        <th>Menu</th>
                        <th>Create</th>
                        <th>Read</th>
                        <th>Update</th>
                        <th>Delete</th>
                    </tr>
                </thead><tbody>
        `;
            rows.slice(1).forEach(r => {
                printHTML +=
                    `<tr><td>${r[0]}</td><td>${r[1]}</td><td>${r[2]}</td><td>${r[3]}</td><td>${r[4]}</td><td>${r[5]}</td><td>${r[6]}</td></tr>`;
            });
            printHTML += "</tbody></table>";
            const printWindow = window.open("", "", "width=1000,height=700");
            printWindow.document.write(`
            <html>
                <head>
                    <title>Role Permissions</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                        th { background: #e0e0e0; }
                    </style>
                </head>
                <body>${printHTML}</body>
            </html>
        `);
            
        }
    });
</script>
<script>
    $('#permissionForm').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        formData.append('check_only', true); // Flag for permission check
        // ✅ Step 1: Mandatory permission check
        fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Permission Denied',
                        text: data.error
                    });
                    $('#assignPermissionBtn').prop('disabled', false);
                } else {
                    $('#empPermission').modal('hide'); // Close modal
                    // ✅ Step 2: SweetAlert2 input confirmation
                    Swal.fire({
                        icon: 'info',
                        title: 'Please type "Update" to confirm',
                        text: 'This will update the role permissions.',
                        input: 'text',
                        inputPlaceholder: 'Type "Update"',
                        showCancelButton: true,
                        confirmButtonText: 'Confirm',
                        cancelButtonText: 'Cancel',
                        inputValidator: (value) => {
                            if (value !== 'Update') {
                                return 'You must type "Update" exactly to proceed.';
                            }
                            return null;
                        }
                    }).then((result) => {
                        if (result.isConfirmed && result.value === 'Update') {
                            // ✅ Step 3: Submit actual request
                            formData.delete('check_only');
                            fetch(form.action, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                            'content')
                                    },
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    $('#empPermission').modal('hide');
                                    $('#assignPermissionBtn').prop('disabled', true);
                                    if (data.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success',
                                            text: data.success,
                                            timer: 2000,
                                            showConfirmButton: false
                                        }).then(() => {
                                            location.reload();
                                        });
                                    } else if (data.error) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: data.error
                                        }).then(() => {
                                            location.reload();
                                        });
                                    }
                                })
                                .catch(() => {
                                    $('#assignPermissionBtn').prop('disabled', true);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: 'Something went wrong!'
                                    }).then(() => {
                                        location.reload();
                                    });
                                });
                        } else {
                            $('#assignPermissionBtn').prop('disabled', false);
                        }
                    });
                }
            });
    });
</script>
