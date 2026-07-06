@extends('admin.layout.master')
<script src="{{ asset('assets/js/cities.js') }}"></script>
@section('title')
    FNF Configuration Settings
@endsection


@section('content')
   <style>
        /* Position alert at top-right */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 280px;
            max-width: 350px;
            background: #fff;
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.4s ease;
            z-index: 9999;
        }

        .toast-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .success .toast-icon {
            background: #e6f9f0;
            color: #28a745;
        }

        .error .toast-icon {
            background: #fdecea;
            color: #dc3545;
        }

        .toast-message {
            font-size: 14px;
            color: #333;
        }

        /* Animation */
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .fade-out {
            animation: fadeOut 0.5s forwards;
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }
    </style>

    @if (session('success'))
        <div class="toast-notification success" id="flashMessage">
            <div class="toast-icon">✔</div>
            <div class="toast-message">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="toast-notification error" id="flashMessage">
            <div class="toast-icon">✖</div>
            <div class="toast-message">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <script>
        setTimeout(function() {
            let flash = document.getElementById('flashMessage');
            if (flash) {
                flash.classList.add('fade-out');
                setTimeout(() => flash.remove(), 500);
            }
        }, 4000);
    </script>


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
    <div class=" p-0 my-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('/admin/settings/business') }}">Settings </a></li>
            <li><a href="{{ url('/admin/settings/business') }}">Payroll Settings </a></li>
            <li class="active"><span><b>FNF Configuration Settings</b></span></li>
        </ol>
    </div>

    <div class="row row-sm">

        {{-- FNF Approval Flow Settings --}}
        <div class="col-xl-6 mt-4">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-2">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                <i class="nav-icon fa fa-book"></i>
                            </span>
                        </div>

                        <div class="col-10 d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn p-0 text-start" data-bs-toggle="modal"
                                    data-bs-target="#fnfModal">
                                    <h5 class="text-dark mb-1">FnF Stage Settings</h5>
                                </button>
                                <p class="mb-0 text-muted">
                                    No. of States:
                                    <strong>{{ $totalapprovaldata ? count($totalapprovaldata) : 0 }}</strong>
                                </p>
                            </div>

                            <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#fnfModal">
                                <i class="fa fa-angle-double-right fs-20"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- FNF Approval Flow Settings --}}
        <div class="col-xl-6 mt-4">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-2">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                <i class="nav-icon fa fa-book"></i>
                            </span>
                        </div>

                        <div class="col-10 d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn p-0 text-start" data-bs-toggle="modal"
                                    data-bs-target="#uplode_images">
                                    <h5 class="text-dark mb-1">FNF Uploads singature</h5>
                                </button>
                                <p class="mb-0 text-muted">
                                    Signature Status:
                                    <strong>
                                        {{ optional($signature)->signature ? 'Uploaded' : 'Not Uploaded' }}
                                    </strong>
                                </p>
                            </div>

                            <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#uplode_images">
                                <i class="fa fa-angle-double-right fs-20"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        {{-- Modal --}}
        <div class="modal fade" id="fnfModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <form action="{{ route('business.fnf.save', $business->b_id ?? 0) }}" method="POST">
                        @csrf

                        {{-- Header --}}
                        <div class="modal-header">
                            <h5 class="modal-title">Select FnF Modules</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        {{-- Body --}}
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Modules</label>


                                @php
                                    // Ensure $selectedModules is always an array
                                    $selectedModules = [];
                                    if (!empty($fnf_data->b_fnf_modules)) {
                                        if (is_string($fnf_data->b_fnf_modules)) {
                                            $selectedModules = json_decode($fnf_data->b_fnf_modules, true);
                                        } elseif (is_array($fnf_data->b_fnf_modules)) {
                                            $selectedModules = $fnf_data->b_fnf_modules;
                                        }
                                    }
                                @endphp

                                <select name="fnf_modules[]" class="form-control select2" multiple>
                                    @foreach ($fnfModules as $module)
                                        <option value="{{ $module->m_id }}"
                                            {{ in_array($module->m_id, $selectedModules) ? 'selected' : '' }}>
                                            {{ $module->m_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('fnf_modules')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Save
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

        {{-- Optional: Select2 Initialization --}}
        <script>
            $(document).ready(function() {
                $('.select2').select2({
                    placeholder: "Select Modules",
                    width: '100%'
                });
            });
        </script>




        <!-- Upload Signature Modal -->
        <div class="modal fade" id="uplode_images" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Upload Signature</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form action="{{ route('upload.signature') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="modal-body">

                            <!-- Description -->
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Enter description">{{ old('description', $singature->notes ?? '') }}</textarea>
                            </div>

                            <!-- Upload Signature -->
                            <div class="mb-3">
                                <label class="form-label">Upload Signature</label> <span> View </span>

                                <input type="file" name="signature" id="signatureInput" class="form-control"
                                    accept="image/*">
                                <small class="text-muted">Max Size: 20 KB</small>
                                <div class="text-danger" id="signatureError"></div>
                            </div>
                              @if (!empty($signature->signature))
                                <img src="{{ asset($signature->signature) }}" alt="Signature" style="height: 80px;">
                            @else
                                <p>No Signature Available</p>
                            @endif

                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Upload</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

        <script>
            document.getElementById('signatureInput').addEventListener('change', function() {

                const file = this.files[0];
                const maxSize = 20 * 1024; // 20 KB
                const errorDiv = document.getElementById('signatureError');

                errorDiv.innerHTML = '';

                if (file) {

                    if (file.size > maxSize) {
                        errorDiv.innerHTML = "File size must be less than 20 KB.";
                        this.value = ""; // reset input
                    }

                }

            });
        </script>

        <script>
            $(document).on('click', '.toggleStatusBtn', function() {
                const id = $(this).data('id');

                $.post(`/admin/employee-exit/toggle-status/${id}`, {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    })
                    .done(function(res) {
                        if (res.status) {
                            Swal.fire('Success!', res.message, 'success');
                        } else {
                            Swal.fire('Error!', res.message, 'error');
                        }

                        $('#mail-template-table').DataTable().ajax.reload(null, false);
                    })
                    .fail(function(xhr) {
                        let msg = 'Something went wrong!';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }

                        Swal.fire('Error!', msg, 'error');
                    });
            });
        </script>

    </div>
@endsection
