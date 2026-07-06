@extends('admin.layout.master')
<script src="{{ asset('assets/js/cities.js') }}"></script>
@section('title')
    Business Settings
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

@section('content')
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    {{-- <li><a href="{{ url('/admin/settings/business') }}">Settings </a></li> --}}
                    <li><a href="{{ url('/admin/settings/business') }}">Business Settings </a></li>
                    <li class="active"><span><b>Regulatory Document</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <!-- Add Folder Button -->
                                <button class="btn btn-outline-primary" id="addBusinessPolicyBtn">
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <br> <br>

    <!-- Folders Display -->
    <div class="row row-sm">
        @foreach ($businessfolders as $businessfoder)
            <div class="col-xl-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-2 my-auto">
                                <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                    <i class="nav-icon fa fa-book mx-1"></i>
                                </span>
                            </div>
                            <div class="col-10 d-flex justify-content-between">
                                <div class="my-auto">
                                    <a href="{{ route('documents.index', ['id' => $businessfoder->bpf_id]) }}"
                                        class="text-dark">
                                        <h5 class="my-auto">{{ $businessfoder->bpf_name }}</h5>
                                    </a>

                                    @php
                                        $matchedDocument = $businesdocument->firstWhere(
                                            'bpd_folder_id',
                                            $businessfoder->bpf_id,
                                        );
                                    @endphp

                                    <p class="my-auto">
                                        Latest Version :  {{ $matchedDocument ? $matchedDocument->bpd_version : '0' }}
                                    </p>


                                </div>
                                <div class="my-auto d-flex">
                                    <button class="btn btn-sm edit-folder" data-id="{{ $businessfoder->bpf_id }}"
                                        data-name="{{ $businessfoder->bpf_name }}">
                                        <i class="feather feather-edit"></i>
                                    </button>
                                    <button class="btn btn-sm  delete-folder" data-id="{{ $businessfoder->bpf_id }}">
                                        <i class="feather feather-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>


    <!-- Add/Edit Modal -->
    <div class="modal fade" id="businessPolicyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Folder</h5>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="businessPolicyForm">
                    @csrf
                    <input type="hidden" name="bpf_id" id="bpf_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <input type="text" name="bpf_name" id="bpf_name" class="form-control"
                                placeholder="Folder Name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Open Add Modal
            $('#addBusinessPolicyBtn').click(function() {
                $('#businessPolicyForm')[0].reset();
                $('#bpf_id').val('');
                $('#modalTitle').text('Add Folder');
                $('#saveBtn').text('Save');
                $('#businessPolicyModal').modal('show');
            });

            // Open Edit Modal
            $(document).on('click', '.edit-folder', function() {
                $('#bpf_id').val($(this).data('id'));
                $('#bpf_name').val($(this).data('name'));
                $('#modalTitle').text('Edit Folder');
                $('#saveBtn').text('Update');
                $('#businessPolicyModal').modal('show');
            });

            // Submit Form (Add/Edit)
            $('#businessPolicyForm').submit(function(e) {
                e.preventDefault();
                $('#saveBtn').prop('disabled', true);

                $.ajax({
                    url: "{{ route('folder.store') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#saveBtn').prop('disabled', false);
                        $('#businessPolicyModal').modal('hide');

                        Swal.fire('Success', response.message, 'success').then(() => {
                            location.reload(); // Or use AJAX refresh
                        });
                    },
                    error: function(xhr) {
                        $('#saveBtn').prop('disabled', false);
                        let message = 'An error occurred.';

                        if (xhr.status === 422) {
                            message = Object.values(xhr.responseJSON.errors).map(err =>
                                `<p>${err[0]}</p>`).join('');
                        } else if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: message
                        });
                    }
                });
            });

            // Delete Folder
            $(document).on('click', '.delete-folder', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This folder will be permanently deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/admin/settings/regulatory/folder/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                Swal.fire('Deleted', res.success, 'success').then(
                            () => {
                                    location.reload();
                                });
                            },
                            error: function() {
                                Swal.fire('Error', 'Failed to delete folder.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
