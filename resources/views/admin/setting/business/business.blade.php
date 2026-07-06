@extends('admin.layout.master')
{{-- @extends('admin.setting.setting') --}}
@section('title')
    Account Settings
@endsection

@section('content')
    <div class=" p-0 my-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('/admin/settings/account') }}">Settings</a></li>
            <li class="active"><span><b>Account Settings</b></span></li>
        </ol>
    </div>
    <div class="">
        <p class="text-muted">Create and Update Your Account Settings</p>
    </div>

    <div class="row row-sm">
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary"><i class="nav-icon fa fa-building-o "></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="{{ url('admin/settings/business/branches') }}"><h5 class="my-auto text-dark">Branches</h5></a>
                                <p class="my-auto"> {{ $branch }} &nbsp;Branches Created</p>
                            </div>
                            <div class="my-auto">
                                <a href="{{ url('admin/settings/business/branches') }}"><i class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                    class="nav-icon fa fa-sitemap"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="{{ url('admin/settings/business/department') }}"><h5 class="my-auto text-dark">Departments</h5></a>
                                <p class="my-auto">{{ $department }} &nbsp;Departments Created</p>
                            </div>
                            <div class="my-auto">
                                <a href="{{ url('admin/settings/business/department') }}"><i class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                    class="nav-icon fa fa-sitemap"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="{{ url('admin/settings/business/dealership') }}"><h5 class="my-auto text-dark">Dealerships</h5></a>
                                <p class="my-auto">{{ $dealership }} &nbsp;Dealerships Created</p>
                            </div>
                            <div class="my-auto">
                                <a href="{{ url('admin/settings/business/dealership') }}"><i class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary"><i class="nav-icon fe fe-users"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="{{ url('admin/settings/business/designation') }}"><h5 class="my-auto text-dark">Designations</h5></a>
                                <p class="my-auto">{{ $designation }} &nbsp;Designations Created</p>
                            </div>
                            <div class="my-auto">
                                <a href="{{ url('admin/settings/business/designation') }}"><i class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary"><i class="fa fa-star-o"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="{{ url('admin/settings/business/grade') }}"><h5 class="my-auto text-dark">Grade</h5></a>
                                <p class="my-auto">{{ $grade }} &nbsp;Grade Created</p>
                            </div>
                            <div class="my-auto">
                                <a href="{{ url('admin/settings/business/grade') }}"><i class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary"><i class="fa fa-star-o"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="{{ url('admin/settings/business/role') }}"><h5 class="my-auto text-dark">Role</h5></a>
                                <p class="my-auto">{{ $role }} &nbsp;Role Created</p>
                            </div>
                            <div class="my-auto">
                                <a href="{{ url('admin/settings/business/role') }}"><i class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body" data-bs-toggle="modal" data-bs-target="#dashboardModal" style="cursor: pointer;">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary"><i class="fa fa-list"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <h5 class="my-auto text-dark">Set Default Dashboard</h5>
                                <p class="my-auto">{{ $menuCount }} &nbsp;Default Dashboard</p>
                            </div>
                            <div class="my-auto">
                                <i class="fa fa-angle-double-right fs-20 my-auto"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    <div class="col-xl-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="row">
                    <!-- Icon -->
                    <div class="col-2 my-auto">
                        <span class="settings-icon bg-primary-transparent text-primary border-primary">
                            <i class="nav-icon fa fa-folder-open mx-1"></i>
                        </span>
                    </div>

                    <!-- Title, Count, and Link -->
                    <div class="col-10 d-flex justify-content-between">
                        <div class="my-auto">
                            <a href="#" data-bs-toggle="modal" >
                                <h5 class="my-auto text-dark">Projects</h5>
                            </a>
                            <p class="my-auto">
                                {{ $projects }} Projects
                            </p>
                        </div>

                        <div class="my-auto">
                            <a href="{{ route('projects.index') }}">
                                <i class="fa fa-angle-double-right fs-20 my-auto"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <!-- ================= Approval Flow Card ================= -->
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <!-- Icon -->
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                <i class="nav-icon fa fa-check-circle mx-1"></i>
                            </span>
                        </div>

                        <!-- Title, Count, and Link -->
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <!-- Open Modal -->
                                <a href="#" data-bs-toggle="modal" data-bs-target="#approvalModal">
                                    <h5 class="my-auto text-dark">Approval Stage</h5>
                                </a>
                                <p class="my-auto">
                                    {{ $approvalflowCount }} Stage
                                </p>
                            </div>

                            <div class="my-auto">
                                <a href="{{ route('approval.index') }}">
                                    <i class="fa fa-angle-double-right fs-20 my-auto"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

         <!-- ================= Approval Flow Card ================= -->
        <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <!-- Icon -->
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                <i class="nav-icon fa fa-check-circle mx-1"></i>
                            </span>
                        </div>

                        <!-- Title, Count, and Link -->
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <!-- Open Modal -->
                                <a href="{{ route('kit.index') }}" data-bs-toggle="modal" data-bs-target="#approvalModal">
                                    <h5 class="my-auto text-dark">Kit Details</h5>
                                </a>
                                <p class="my-auto">
                                    {{ $kit_details }} Kits
                                </p>
                            </div>

                            <div class="my-auto">
                                <a href="{{ route('kit.index') }}">
                                    <i class="fa fa-angle-double-right fs-20 my-auto"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

   <!-- Modal -->
    <div class="modal fade" id="dashboardModal" tabindex="-1" aria-labelledby="dashboardModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dashboardModalLabel">Set Default Dashboard</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="dashboardForm">
                        @csrf
                        <div class="mb-3">
                            <label for="dashboardSelect" class="form-label">Select Default Dashboard</label>
                            <select class="form-select" id="dashboardSelect" name="menu_id" required>
                                <option value="" disabled {{ empty($b_dashboard_id) ? 'selected' : '' }}>
                                    Choose a dashboard
                                </option>
                                @foreach($menu_list as $menu)
                                    <option value="{{ $menu->menu_id }}"
                                        {{ (isset($b_dashboard_id) && $b_dashboard_id == $menu->menu_id) ? 'selected' : '' }}>
                                        {{ $menu->menu_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-outline-primary" id="saveDashboard">Save changes</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>
    $(document).on('click', '#saveDashboard', function () {
        const menu_id = $('#dashboardSelect').val();

        if (!menu_id) {
            Swal.fire({
                icon: 'warning',
                title: 'Please select a dashboard.',
                text: 'You must choose a dashboard before saving.',
            });
            return;
        }

        $.ajax({
            url: "{{ route('add.default-dashboard') }}",
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                menu_id: menu_id,
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.message || 'Default dashboard set successfully.',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    $('#dashboardModal').modal('hide');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: response.message || 'Could not set default dashboard.',
                    });
                }
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Something went wrong. Please try again.',
                });
            }
        });
    });
</script>
@endsection
