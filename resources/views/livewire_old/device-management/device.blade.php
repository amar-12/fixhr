<div class="container-fluid py-4">
    <div>
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
            .text-wrap {
                font-size: 12px !important;
            }
        </style>
        <!-- ROW -->
        <div class="row">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title">Device List</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <p class="form-label">Show entries</p>
                                    <select wire:model.live="perPage"
                                        class="form-select-md p-2 search_test from-control" style="width: 100%"
                                        data-length>
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-md-2">
                                <p class="form-label">Search</p>
                                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search"
                                    class="form-control" data-search />
                            </div>
                            <div class="col-sm-8 pt-5 mt-1 d-flex justify-content-end p-5">
                                <a href="{{ route('device.connector.download') }}" class="btn btn-outline-info">
                                    <i class="bi bi-download"></i> Download Connector
                                </a>
                                <button class="btn btn-outline-success ms-5" type="button" wire:click="addDevice">
                                    <i class="las la-plus-circle"></i> Add New Device
                                </button>
                            </div>
                        </div>
                        <div>
                            <table class="table display table-vcenter table-hover text-wrap border-bottom"
                                id="device-table-dynamic">
                                <thead>
                                    <tr>
                                        <th style="font-size: 13px" wire:click="sortBy('id')" style="cursor: pointer;">
                                            S.No
                                        </th>
                                        <th style="font-size: 13px" wire:click="sortBy('id')" style="cursor: pointer;">
                                            Machine Id
                                            @if ($sortField == 'id')
                                                <i class="bi bi-arrow-{{ $sortAsc ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </th>
                                        <th style="font-size: 13px" wire:click="sortBy('device_name')"
                                            style="cursor: pointer;">
                                            Device Name
                                            @if ($sortField == 'device_name')
                                                <i class="bi bi-arrow-{{ $sortAsc ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </th>
                                        <th style="font-size: 13px" wire:click="sortBy('short_name')"
                                            style="cursor: pointer;">
                                            Short Name
                                            @if ($sortField == 'short_name')
                                                <i class="bi bi-arrow-{{ $sortAsc ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </th>
                                        <th style="font-size: 13px" wire:click="sortBy('device_location')"
                                            style="cursor: pointer;">
                                            Location
                                            @if ($sortField == 'device_location')
                                                <i class="bi bi-arrow-{{ $sortAsc ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </th>
                                        <th style="font-size: 13px" wire:click="sortBy('ip_address')"
                                            style="cursor: pointer;">
                                            IP Address
                                            @if ($sortField == 'ip_address')
                                                <i class="bi bi-arrow-{{ $sortAsc ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </th>
                                        <th style="font-size: 13px" wire:click="sortBy('company')"
                                            style="cursor: pointer;">
                                            Company
                                            @if ($sortField == 'company')
                                                <i class="bi bi-arrow-{{ $sortAsc ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </th>
                                        <th style="font-size: 13px" wire:click="sortBy('company')"
                                            style="cursor: pointer;">
                                            Business Code
                                            @if ($sortField == 'company')
                                                <i class="bi bi-arrow-{{ $sortAsc ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </th>
                                        <th style="font-size: 13px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($devices as $device)
                                        <tr style="cursor:pointer;"
                                            onclick="window.location='{{ route('attendance.logs', [$device->business->b_unique_id, $device->serial_name]) }}'">
                                            <td>{{ $loop->iteration + ($devices->currentPage() - 1) * $devices->perPage() }}
                                            </td>
                                            <td><span
                                                    class="badge bg-light text-dark">#{{ $device->machine_id }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-medium">{{ $device->device_name }}</div>
                                                <small class="text-muted">{{ $device->serial_name }}</small>
                                            </td>
                                            <td>{{ $device->short_name }}</td>
                                            <td><i
                                                    class="bi bi-geo-alt text-muted me-1"></i>{{ $device->device_location }}
                                            </td>
                                            <td><code
                                                    class="bg-light px-2 py-1 rounded">{{ $device->ip_address ?: 'N/A' }}</code>
                                            </td>
                                            <td>{{ $device->company }}</td>
                                            <td>{{ $device->business->b_unique_id ?? 'N/A' }}</td>
                                            <td onclick="event.stopPropagation();">
                                                <button class="btn btn-outline-light text-muted" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu p-2" style="min-width: 240px;">
                                                    <!-- View Device -->
                                                    <li>
                                                        <a wire:click="view({{ $device->id }})"
                                                            class="dropdown-item fw-semibold d-flex align-items-center gap-2"
                                                            onclick="event.stopPropagation()">
                                                            <i class="bi bi-eye"></i> View Device
                                                        </a>
                                                    </li>
                                                    <!-- Edit Device -->
                                                    <li>
                                                        <a wire:click="edit({{ $device->id }})"
                                                            class="dropdown-item fw-semibold d-flex align-items-center gap-2"
                                                            onclick="event.stopPropagation()">
                                                            <i class="bi bi-pencil-square"></i> Edit Device
                                                        </a>
                                                    </li>
                                                    <!-- Delete Device -->
                                                    <li>
                                                        <a wire:click="confirmDelete({{ $device->id }})"
                                                            class="dropdown-item fw-semibold d-flex align-items-center gap-2"
                                                            onclick="event.stopPropagation()">
                                                            <i class="bi bi-trash"></i> Delete Device
                                                        </a>
                                                    </li>
                                                </ul>
                                            </td>
                                            {{-- Business unique code --}}
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="bi bi-inbox display-4 text-muted d-block mb-2"></i>
                                                <span class="text-muted">No devices found.</span>
                                                @if ($search)
                                                    <div class="mt-2">
                                                        <button wire:click="clearSearch"
                                                            class="btn btn-sm btn-outline-primary">
                                                            Clear search
                                                        </button>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="row mt-5">
                            <div class="col-sm-6">
                                <div id="custom-show-entries" data-show-entries></div>
                            </div>
                            <div class="col-sm-6 d-flex justify-content-end">
                                {{ $devices->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Device Modal (Edit/Add) -->
    <div class="modal fade" id="deviceModal" tabindex="-1" aria-labelledby="deviceModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deviceModalLabel">
                        <i class="bi bi-{{ $device_id ? 'pencil-square' : 'plus-circle' }} me-2"></i>
                        {{ $device_id ? 'Edit Device' : 'Add New Device' }}
                    </h5>
                    <button type="button" class="btn-close custom-close-button" data-bs-dismiss="modal"
                        aria-label="Close" wire:click="cancel"></button>
                </div>
                <form wire:submit.prevent="submit">
                    <div class="modal-body">
                        <div class="row">
                            <!-- Device Name -->
                            <div class="col-md-6 mb-3">
                                <label for="device_name" class="form-label">
                                    <i class="bi bi-device-hdd me-1"></i>Device Name <span
                                        class="text-danger">*</span>
                                </label>
                                <input type="text" wire:model.blur="device_name" id="device_name"
                                    class="form-control @error('device_name') is-invalid @enderror"
                                    placeholder="Enter device name">
                                @error('device_name')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <!-- Short Name -->
                            <div class="col-md-6 mb-3">
                                <label for="short_name" class="form-label">
                                    <i class="bi bi-tag me-1"></i>Short Name
                                </label>
                                <input type="text" wire:model.blur="short_name" id="short_name"
                                    class="form-control @error('short_name') is-invalid @enderror"
                                    placeholder="Enter short name">
                                @error('short_name')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <!-- Serial Name -->
                            <div class="col-md-6 mb-3">
                                <label for="serial_name" class="form-label">
                                    <i class="bi bi-upc me-1"></i>Serial Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" wire:model.blur="serial_name" id="serial_name"
                                    class="form-control @error('serial_name') is-invalid @enderror"
                                    placeholder="Enter serial name">
                                @error('serial_name')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <!-- Device Location -->
                            <div class="col-md-6 mb-3">
                                <label for="device_location" class="form-label">
                                    <i class="bi bi-geo-alt me-1"></i>Device Location
                                </label>
                                <input type="text" wire:model.blur="device_location" id="device_location"
                                    class="form-control @error('device_location') is-invalid @enderror"
                                    placeholder="Enter device location">
                                @error('device_location')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <!-- IP Address -->
                            <div class="col-md-6 mb-3">
                                <label for="ip_address" class="form-label">
                                    <i class="bi bi-router me-1"></i>IP Address <span class="text-danger">*</span>
                                </label>
                                <input type="text" wire:model.blur="ip_address" id="ip_address"
                                    class="form-control @error('ip_address') is-invalid @enderror"
                                    placeholder="e.g., 192.168.1.100">
                                @error('ip_address')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <!-- Company -->
                            <div class="col-md-6 mb-3">
                                <label for="company" class="form-label">
                                    <i class="bi bi-building me-1"></i>Company
                                </label>
                                <input type="text" wire:model.blur="company" id="company"
                                    class="form-control @error('company') is-invalid @enderror"
                                    placeholder="Enter company name">
                                @error('company')
                                    <div class="invalid-feedback">
                                        <i class ماشین bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <!-- Communication Key -->
                            <div class="col-md-6 mb-3">
                                <label for="com_key" class="form-label">
                                    <i class="bi bi-key me-1"></i>Communication Key <span class="text-danger">*</span>
                                </label>
                                <input type="text" wire:model.blur="com_key" id="com_key"
                                    class="form-control @error('com_key') is-invalid @enderror"
                                    placeholder="Enter communication key">
                                @error('com_key')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <!-- Machine ID -->
                            <div class="col-md-6 mb-3">
                                <label for="machine_id" class="form-label">
                                    <i class="bi bi-hash me-1"></i>Machine ID
                                </label>
                                <input type="text" wire:model.blur="machine_id" id="machine_id"
                                    class="form-control @error('machine_id') is-invalid @enderror"
                                    placeholder="Enter machine ID">
                                @error('machine_id')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-6" x-data="{ open: false }" @click.away="open = false">
                                <div class="position-relative">
                                    <label for="branch" class="form-label">
                                        <i class="bi bi-building me-1"></i>Branch <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" wire:model.live="searchBranch" @focus="open = true"
                                        class="form-control @error('br_id') is-invalid @enderror"
                                        placeholder="Search branches...">
                                    @error('br_id')
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                    <ul x-show="open" x-transition
                                        class="list-group shadow position-absolute w-100 bg-white border rounded mt-1"
                                        style="z-index: 1050; max-height: 200px; overflow-y: auto; top: 100%;">
                                        @forelse($branches as $item)
                                            <li class="list-group-item list-group-item-action border-0 py-2 px-3"
                                                wire:click="selectBranch({{ $item['br_id'] }}, '{{ $item['br_name'] }}')"
                                                @click="open = false" style="font-size: 12px; cursor: pointer;">
                                                {{ $item['br_name'] }}
                                            </li>
                                        @empty
                                            <li class="list-group-item text-muted py-2 px-3" style="font-size: 12px;">
                                                <i class="bi bi-file-earmark-x"></i> No Branches Found
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal"
                            wire:click="cancel">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-outline-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="bi bi-check-circle me-1"></i>
                                {{ $device_id ? 'Update Device' : 'Submit' }}
                            </span>
                            <span wire:loading>
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- View Device Modal -->
    <div class="modal fade" id="viewDeviceModal" tabindex="-1" aria-labelledby="viewDeviceModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDeviceModalLabel">
                        <i class="bi bi-eye me-2"></i>View Device
                    </h5>
                    <button type="button" class="btn-close custom-close-button" data-bs-dismiss="modal"
                        aria-label="Close" wire:click="cancel"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Device Name -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-device-hdd me-1"></i>Device Name</label>
                            <p class="form-control-plaintext">{{ $device_name ?: 'N/A' }}</p>
                        </div>
                        <!-- Short Name -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-tag me-1"></i>Short Name</label>
                            <p class="form-control-plaintext">{{ $short_name ?: 'N/A' }}</p>
                        </div>
                        <!-- Serial Name -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-upc me-1"></i>Serial Name</label>
                            <p class="form-control-plaintext">{{ $serial_name ?: 'N/A' }}</p>
                        </div>
                        <!-- Device Location -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-geo-alt me-1"></i>Device Location</label>
                            <p class="form-control-plaintext">{{ $device_location ?: 'N/A' }}</p>
                        </div>
                        <!-- IP Address -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-router me-1"></i>IP Address</label>
                            <p class="form-control-plaintext">{{ $ip_address ?: 'N/A' }}</p>
                        </div>
                        <!-- Company -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-building me-1"></i>Company</label>
                            <p class="form-control-plaintext">{{ $company ?: 'N/A' }}</p>
                        </div>
                        <!-- Communication Key -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-key me-1"></i>Communication Key</label>
                            <p class="form-control-plaintext">{{ $com_key ?: 'N/A' }}</p>
                        </div>
                        <!-- Machine ID -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-hash me-1"></i>Machine ID</label>
                            <p class="form-control-plaintext">{{ $machine_id ?: 'N/A' }}</p>
                        </div>
                        <!-- Branch -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-building me-1"></i>Branch</label>
                            <p class="form-control-plaintext">{{ $searchBranch ?: 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                        wire:click="cancel">
                        <i class="bi bi-x-circle me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the device <strong>{{ $deviceToDeleteName }}</strong>? This
                        action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-primary" wire:click="delete"
                        data-bs-dismiss="modal">
                        <i class="bi bi-trash me-1"></i>Delete Device
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Control Scripts -->
    <script>
        document.addEventListener('livewire:initialized', function() {
            // Function to close modal and remove backdrop
            function closeModal(modalId) {
                const modalElement = document.getElementById(modalId);
                const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                modal.hide();
                // Remove backdrop and modal-open class
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
            }
            // Function to open modal
            function openModal(modalId) {
                const modalElement = document.getElementById(modalId);
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
            // Handle closeModal event
            Livewire.on('closeModal', (data) => {
                const modalId = data[0].modalId;
                closeModal(modalId);
            });
            // Handle openModal event
            Livewire.on('openModal', (data) => {
                const modalId = data[0].modalId;
                openModal(modalId);
            });
            // Show delete confirmation modal
            Livewire.on('showDeleteModal', () => {
                openModal('deleteModal');
            });
            // Show alerts
            Livewire.on('show-alert', (data) => {
                Swal.fire({
                    position: 'top-end',
                    icon: data[0].type || 'success',
                    title: data[0].message || '',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'swal2-toast-custom'
                    }
                });
            });
            // Ensure backdrop is removed when modal is hidden
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('hidden.bs.modal', () => {
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                });
            });
        });
    </script>
</div>
