@extends('admin.layout.master')
<script src="{{ asset('assets/js/cities.js') }}"></script>
@section('title')
    Employee Details
@endsection

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
    /* Set the map's size */
    #map {
        height: 400px;
        width: 100%;
    }

    /* Adjust the search input style */
    #searchInput {
        width: 100%;
        margin-bottom: 10px;
    }

    #editAddressNameId {
        width: 100%;
        margin-bottom: 10px;

    }

    #mapeditload {
        height: 400px;
        width: 100%;

    }

    .pac-container {
        z-index: 10000 !important;
        /* Set a high z-index for the autocomplete dropdown */
    }
</style>
@section('content')
    <div class="p-0 my-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="active"><span><b>Employee Details</b></span></li>
        </ol>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card custom-card mb-5">
                <div class="card-body p-3">
                    <a href="{{ route('academic.index') }}">
                        <div class="row">
                            <div class="col-2 my-auto">
                                <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                    <i class="nav-icon fa fa-graduation-cap mx-1"></i>

                                </span>
                            </div>
                            <div class="col-10 d-flex justify-content-between">
                                <div class="my-auto">
                                    <h5 class="my-auto text-dark">Academic Details</h5>
                                    <p class="pt-2 my-auto">Total Employees ({{ $academiccount }})</p>
                                </div>
                                <div class="my-auto">
                                    <i class="fa fa-angle-double-right fs-20 my-auto"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card custom-card mb-5">
                <div class="card-body p-3">
                    <a href="{{ route('uniform_index.index') }}">
                        <div class="row">
                            <div class="col-2 my-auto">
                                <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                    <i class="nav-icon fa fa-desktop mx-1"></i>

                                </span>
                            </div>
                            <div class="col-10 d-flex justify-content-between">
                                <div class="my-auto">
                                    <h5 class="my-auto text-dark">Kit Details</h5>
                                    <p class="pt-2 my-auto">Total Employees({{ $uniformcount }})</p>
                                </div>
                                <div class="my-auto">
                                    <i class="fa fa-angle-double-right fs-20 my-auto"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>


        <div class="col-lg-6">
            <div class="card custom-card mb-5">
                <div class="card-body p-3">
                    <a href="{{ route('family.index') }}">
                        <div class="row">
                            <div class="col-2 my-auto">
                                <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                    <i class="nav-icon fa fa-home mx-1"></i>

                                </span>
                            </div>
                            <div class="col-10 d-flex justify-content-between">
                                <div class="my-auto">
                                    <h5 class="my-auto text-dark">Family Details</h5>
                                    <p class="pt-2 my-auto">Total Employees({{ $familyDetails }})</p>
                                </div>
                                <div class="my-auto">
                                    <i class="fa fa-angle-double-right fs-20 my-auto"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>


        {{-- <div class="col-lg-6 mb-4">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                <i class="nav-icon fa fa-desktop mx-1"></i>

                            </span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#familyModal">
                                    <h5 class="my-auto text-dark">Family Details</h5>
                                </a>
                                <p class="my-auto">Total Employees({{ $uniformcount }})</p>
                            </div>
                            <div class="my-auto">
                                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#familyModal">
                                    <i class="fa fa-angle-double-right fs-20 my-auto"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        <!-- Modal -->
        <div class="modal fade" id="familyModal" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content tx-size-sm">
                    <div class="modal-header">
                        <h4 class="modal-title">Employee Family Details</h4>
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('family.show') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <label class="form-label mb-0 mt-2">Employee Family Details <span
                                    class="text-danger">*</span></label>
                            <select name="emp_id" id="emp_id" class="form-control custom-select search_test" required>
                                @foreach ($employee_list as $data)
                                    <option value="{{ $data->emp_id }}">
                                        {{ $data->emp_full_name }} - {{ $data->emp_code }}
                                    </option>
                                @endforeach
                            </select>

                            <span class="text-danger" id="timezone_error"></span>
                            @error('timezone')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="modal-footer py-1">
                            <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>
                            <button type="submit" class="btn btn-outline-primary savebtn me-0" id="timezoneSubmitBtn">Go To
                                Details</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                text: '{{ session('error') }}',
                confirmButtonColor: '#d33'
            });
        @endif
    </script>
@endsection
