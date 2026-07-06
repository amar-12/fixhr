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
    <div class="mt-5">
        <!-- Business Information Section -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4>Business Details</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Business Name:</strong> {{ $business->b_name }}
                    </div>
                    <div class="col-md-4">
                        <strong>Contact Email:</strong> -
                    </div>
                    <div class="col-md-4">
                        <strong>Phone:</strong> -
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <strong>Description:</strong> -
                    </div>
                </div>
            </div>
        </div>

        <!-- Module Permissions Section -->
        <form id="moduleAccessForm" method="POST" action="{{ route('organizations.update', $business->b_id) }}">
            @csrf
            @method('PUT')

            <input type="hidden" name="business_id" value="{{ $business->b_id }}">

            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h4>Module Access Permissions</h4>
                </div>

                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Module Name</th>
                                <th>Description</th>
                                <th class="text-center">Access</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($modules as $module)
                                <tr>
                                    <td>{{ $module->mdl_name }}</td>
                                    <td>{{ $module->mdl_description }}</td>
                                    <td class="text-center">
                                        <input type="checkbox" name="module[{{ $module->mdl_id }}]" value="1"
                                            class="open-modal-checkbox" data-module-id="{{ $module->mdl_id }}"
                                            data-business-id="{{ $business->b_id }}"
                                            {{ $accessData->where('bma_mdl_id', $module->mdl_id)->first()?->bma_access == 1 ? 'checked' : '' }}
                                            data-bs-target="#subModuleModal">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer text-center">
                    <button type="submit" class="btn btn-outline-primary">Save Permissions</button>
                    <a href="/admin/business-list" class="btn btn-outline-danger">Back to Business List</a>
                </div>
            </div>
        </form>

    </div>


    {{-- -------------------- Sub Module  Modal Start -------------- --}}
    <div class="modal fade" id="subModuleModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-xl"> <!-- Added 'modal-xl' for extra-large size -->
            <div class="modal-content tx-size-lg" >
                <div class="modal-header border-0">
                    <h4 class="modal-title" id="modalTitle">Sub Module Access Permissions</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('organizations.store') }}">
                    <input type="hidden" name="mainModuleID" id="module_id">
                    @csrf
                    <div style="max-height: 650px; overflow-y: auto;">
                        <table class="table table-bordered">
                            <thead class="pt-3">
                                <tr>
                                    <th scope="col">S.NO</th>
                                    <th scope="col">Module Name</th>
                                    <th scope="col">Access</th>
                                </tr>
                            </thead>
                            <tbody id="sub_menu_data">
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer d-flex justify-content-end mt-5">
                        <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary saveUptBtn" id="saveUptBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- -------------------- Sub Module  Modal End -------------- --}}
@endsection

<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    //**************** sub module open js with data
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".open-modal-checkbox").forEach(function(checkbox) {
            checkbox.addEventListener("change", function() {
                if (this.checked) {
                    let moduleId = this.getAttribute("data-module-id"); // Get module ID
                    let businessId = this.getAttribute("data-business-id"); // Get business ID
                    document.getElementById("module_id").value = moduleId; // Set hidden input

                    let fetchUrl =
                        `/super-admin/sub-menu-get/${moduleId}?business_id=${businessId}`;

                    fetch(fetchUrl)
                        .then(response => response.json())
                        .then(data => {
                            let subMenuData = document.getElementById("sub_menu_data");
                            subMenuData.innerHTML = "";

                            if (data.menus.length === 0) {
                                subMenuData.innerHTML =
                                    `<tr><td colspan="4" class="text-center">No sub-modules found</td></tr>`;
                            } else {
                                let moduleRow = `
                                <tr>
                                    <td></td> <!-- Serial No -->
                                    <td>${data.menus[0].module_name || ""}</td>
                                    <td></td>
                                </tr>`;
                                subMenuData.innerHTML += moduleRow;

                                data.menus.forEach((menu, index) => {
                                    let isChecked = data.checked_menus.includes(menu
                                        .menu_id) ? "checked" : "";
                                    let subModuleRow = `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${menu.menu_name}</td>
                                        <td class="text-center">
                                            <input type="checkbox" class="submodule-checkbox"
                                                name="submodule[${menu.menu_id}]"
                                                value="1" ${isChecked}
                                                data-menu-id="${menu.menu_id}">
                                        </td>
                                    </tr>`;
                                    subMenuData.innerHTML += subModuleRow;
                                });

                                // Track unchecked checkboxes
                                document.querySelectorAll(".submodule-checkbox").forEach((
                                    checkbox) => {
                                    checkbox.addEventListener("change", function() {
                                        if (!this.checked) {
                                            this.value =
                                            "0"; // Mark as unchecked
                                        }
                                    });
                                });
                            }

                            let modal = new bootstrap.Modal(document.getElementById(
                                'subModuleModal'));
                            modal.show();
                        })
                        .catch(error => {
                            console.error('Error fetching sub-menu data:', error);
                        });
                }
            });
        });
    });

    //**************** store sub module js
    document.querySelector(".saveUptBtn").addEventListener("click", function(event) {
        event.preventDefault(); // Prevents default form submission

        let formData = new FormData(document.querySelector("form"));

        fetch("{{ route('organizations.store') }}", { // Ensure correct URL
                method: "POST",
                body: formData,
            }).then(response => response.json())
            .then(data => console.log(data))
            .catch(error => console.error("Error:", error));
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if (session('success'))
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    toast: 'swal2-toast-green-glow'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif

        @if (session('error'))
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: '{{ session('error') }}',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif
    });
</script>
