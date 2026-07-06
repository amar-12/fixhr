@extends('admin.layout.master')
@section('title')
    {{ $pageTitle }}
@endsection

@section('css')
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
    <x-breadcrumb :breadcrumbs="$breadcrumbs" />
    <div class="mt-5 row g-4">
        <!-- Module (Business) Card Section -->
        <div class="col-xl-6">
            <div class="card custom-card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-wrapper me-3">
                        <span
                            class="settings-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 50px; height: 50px;">
                            <i class="fa fa-building-o fs-4"></i>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between w-100 pt-4">
                        <div>
                            <a href="{{ route('organizations.edit', md5($business->b_id)) }}" class="text-decoration-none">
                                <h5 class="text-dark mb-1">Module</h5>
                            </a>
                            <p class="text-muted small">Manage your business modules efficiently</p>
                        </div>
                        <div class="align-self-center">
                            <a href="{{ route('organizations.edit', md5($business->b_id)) }}" class="text-decoration-none">
                                <i class="fa fa-angle-double-right fs-3"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Card Section -->
        <div class="col-xl-6">
            <div class="card custom-card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-wrapper me-3">
                        <span
                            class="settings-icon bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 50px; height: 50px;">
                            <i class="fa fa-users fs-4"></i>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between w-100 pt-4">
                        <div>
                            <a href="{{ route('organizations.employees', md5($business->b_id)) }}"
                                class="text-decoration-none">
                                <h5 class="text-dark mb-1">Employee</h5>
                            </a>
                            <p class="text-muted small">View and manage employee records</p>
                        </div>
                        <div class="align-self-center">
                            <a href="{{ route('organizations.employees', md5($business->b_id)) }}" class="text-success">
                                <i class="fa fa-angle-double-right fs-3"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

          <!-- Copy Settings Card Section -->
          <div class="col-xl-6">
            <div class="card custom-card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-wrapper me-3">
                        <span
                            class="settings-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 50px; height: 50px;">
                            <i class="fa fa-cogs fs-4"></i>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between w-100 pt-4">
                        <div>
                            <a href="{{ route('organizations.copy-settings', md5($business->b_id)) }}"
                                class="text-decoration-none">
                                <h5 class="text-dark mb-1">Copy Settings</h5>
                            </a>
                            <p class="text-muted small">Manage your account preferences</p>
                        </div>
                        <div class="align-self-center">
                            <a href="{{ route('organizations.copy-settings', md5($business->b_id)) }}" class="text-primary">
                                <i class="fa fa-angle-double-right fs-3"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
@endsection

<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
