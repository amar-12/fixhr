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

            .form-check-input:checked {
                background-color: #0d6efd;
                border-color: #0d6efd;
            }

            .sync-status-badge {
                font-size: 11px;
                padding: 4px 8px;
            }

            .swal2-toast-custom {
                font-size: 14px;
            }

            .auto-sync-indicator {
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% {
                    opacity: 1;
                }

                50% {
                    opacity: 0.7;
                }

                100% {
                    opacity: 1;
                }
            }

            .sync-progress {
                height: 4px;
                background-color: #e9ecef;
                border-radius: 2px;
                overflow: hidden;
                margin-top: 5px;
            }

            .sync-progress-bar {
                height: 100%;
                background-color: #0d6efd;
                transition: width 0.3s ease;
            }

            .device-status-indicator {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                display: inline-block;
                margin-right: 6px;
            }

            .status-active {
                background-color: #198754;
            }

            .status-pending {
                background-color: #ffc107;
            }

            .status-disabled {
                background-color: #6c757d;
            }

            .status-error {
                background-color: #dc3545;
            }

            .modal.fade.show {
                display: block !important;
                opacity: 1 !important;
            }

            .modal-backdrop.fade.show {
                opacity: 0.5 !important;
            }

            .bi-calendar-range {
                color: #6c757d;
            }

            .bi-calendar-minus {
                color: #0d6efd;
            }

            .bi-calendar-plus {
                color: #0d6efd;
            }

            .bi-lightning {
                color: #ffc107;
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
                        <!-- Sync Progress -->
                        <div class="sync-progress mt-2 d-none" id="sync-progress">
                            <div class="sync-progress-bar" id="sync-progress-bar" style="width: 0%"></div>
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
                                <button class="btn btn-outline-info" wire:click="openExportModal" data-bs-toggle="modal"
                                    data-bs-target="#exportEmployeeModal">
                                    <i class="bi bi-download"></i>Employee Excel
                                </button>
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
                                        <th style="font-size: 13px">Sync Status</th>
                                        <th style="font-size: 13px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($devices as $device)
                                        <tr style="cursor:pointer;"
                                            onclick="window.location='{{ route('attendance.logs', [$device->business->b_unique_id, $device->serial_name]) }}'">
                                            <td>{{ $loop->iteration + ($devices->currentPage() - 1) * $devices->perPage() }}
                                            </td>
                                            <td><span class="badge bg-light text-dark">#{{ $device->machine_id }}</span>
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
                                            <td>
                                                @php
                                                    $syncStatus = $device->sync_status;
                                                    $nextSyncTime = $device->next_sync_time;
                                                @endphp
                                                <span class="badge sync-status-badge bg-{{ $syncStatus['color'] }}">
                                                    <span
                                                        class="device-status-indicator status-{{ $syncStatus['status'] }}"></span>
                                                    {{ $syncStatus['text'] }}
                                                </span>
                                                @if ($device->auto_sync_enabled)
                                                    <br>
                                                    <small class="text-muted"
                                                        title="Next sync: {{ $device->next_sync_at }}">
                                                        <i class="bi bi-clock me-1"></i>{{ $nextSyncTime }}
                                                    </small>
                                                    @if ($device->last_sync_result)
                                                        <br>
                                                        {{-- <small class="text-muted" title="{{ $device->last_sync_result }}">
                                            <i class="bi bi-info-circle me-1"></i>
                                            {{ Str::limit($device->last_sync_result, 30) }}
                                            </small> --}}
                                                    @endif
                                                @endif
                                            </td>
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
                                                    <!-- Sync Attendance -->
                                                    <li>
                                                        <a wire:click="showSyncPopup({{ $device->id }})"
                                                            class="dropdown-item fw-semibold d-flex align-items-center gap-2"
                                                            onclick="event.stopPropagation()">
                                                            <i class="bi bi-arrow-repeat"></i> Sync Attendance Now
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a wire:click="quickSync('{{ $device->serial_name }}')"
                                                            class="dropdown-item fw-semibold d-flex align-items-center gap-2 text-muted"
                                                            onclick="event.stopPropagation()"
                                                            title="Sync latest data without date range">
                                                            <i class="bi bi-lightning"></i> Quick Sync Latest
                                                        </a>
                                                    </li>
                                                    <!-- Auto Sync Toggle -->
                                                    <li>
                                                        <a wire:click="toggleAutoSync({{ $device->id }})"
                                                            class="dropdown-item fw-semibold d-flex align-items-center gap-2"
                                                            onclick="event.stopPropagation()">
                                                            <i
                                                                class="bi bi-{{ $device->auto_sync_enabled ? 'toggle-on' : 'toggle-off' }}"></i>
                                                            {{ $device->auto_sync_enabled ? 'Disable' : 'Enable' }}
                                                            Auto Sync
                                                        </a>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-4">
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
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
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
                        <!-- Auto Sync Settings Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-arrow-repeat me-2"></i>Auto Sync Settings
                                </h6>
                            </div>
                        </div>
                        <!-- Auto Sync Enabled -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox"
                                        wire:model.live="auto_sync_enabled" id="auto_sync_enabled">
                                    <label class="form-check-label" for="auto_sync_enabled">
                                        <i class="bi bi-arrow-repeat me-1"></i>Enable Auto Sync
                                    </label>
                                </div>
                                <small class="text-muted">Automatically sync attendance data based on schedule</small>
                            </div>
                        </div>
                        @if ($auto_sync_enabled)
                            <!-- Sync Schedule Type -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="sync_schedule_type" class="form-label">
                                        <i class="bi bi-calendar-event me-1"></i>Schedule Type
                                    </label>
                                    <select wire:model.live="sync_schedule_type" id="sync_schedule_type"
                                        class="form-select @error('sync_schedule_type') is-invalid @enderror">
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                    </select>
                                    @error('sync_schedule_type')
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <!-- Sync Time -->
                                <div class="col-md-6 mb-3">
                                    <label for="sync_time" class="form-label">
                                        <i class="bi bi-clock me-1"></i>Sync Time
                                    </label>
                                    <input type="time" wire:model.blur="sync_time" id="sync_time"
                                        class="form-control @error('sync_time') is-invalid @enderror">
                                    @error('sync_time')
                                        <div class="invalid-feedback">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                    <small class="text-muted">Time when auto-sync will run</small>
                                </div>
                            </div>
                            <!-- Sync Days (Weekly only) -->
                            @if ($sync_schedule_type === 'weekly')
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-calendar-week me-1"></i>Sync Days
                                        </label>
                                        <div class="border rounded p-3 bg-light">
                                            <div class="row">
                                                @foreach ($daysOfWeek as $key => $day)
                                                    <div class="col-md-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                wire:model="sync_days" value="{{ $key }}"
                                                                id="day_{{ $key }}">
                                                            <label class="form-check-label"
                                                                for="day_{{ $key }}">
                                                                {{ $day }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('sync_days')
                                                <div class="text-danger small mt-1">
                                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <!-- Next Sync Calculation -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info py-2">
                                        <small>
                                            <i class="bi bi-info-circle me-1"></i>
                                            @if ($device_id && $auto_sync_enabled)
                                                @php
                                                    $device = \App\Models\DeviceManagement::find($device_id);
                                                    $nextSync = $device ? $device->calculateNextSyncTime() : null;
                                                @endphp
                                                Next sync:
                                                {{ $nextSync ? $nextSync->format('Y-m-d H:i:s') : 'Calculating...' }}
                                            @else
                                                Next sync time will be calculated after saving
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <!-- Sync Status Info -->
                            @if ($last_sync_at)
                                <div class="row">
                                    <div class="col-12">
                                        <div
                                            class="alert alert-{{ str_contains(strtolower($last_sync_result), 'error') ? 'warning' : 'success' }} py-2">
                                            <small>
                                                <i
                                                    class="bi bi-{{ str_contains(strtolower($last_sync_result), 'error') ? 'exclamation-triangle' : 'check-circle' }} me-1"></i>
                                                Last sync: {{ \Carbon\Carbon::parse($last_sync_at)->diffForHumans() }}
                                                @if ($last_sync_result)
                                                    - {{ $last_sync_result }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal"
                            wire:click="cancel">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-outline-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="bi bi-check-circle me-1"></i>
                                {{ $device_id ? 'Update Device' : 'Create Device' }}
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
                            <p class="form-control-plaintext fw-semibold">{{ $device_name ?: 'N/A' }}</p>
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
                            <p class="form-control-plaintext"><code>{{ $ip_address ?: 'N/A' }}</code></p>
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
                        <!-- Auto Sync Status -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-arrow-repeat me-1"></i>Auto Sync</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ $auto_sync_enabled ? 'success' : 'secondary' }}">
                                    {{ $auto_sync_enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </p>
                        </div>
                        @if ($auto_sync_enabled)
                            <!-- Sync Schedule -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="bi bi-calendar-event me-1"></i>Sync
                                    Schedule</label>
                                <p class="form-control-plaintext">
                                    {{ ucfirst($sync_schedule_type) }} at
                                    {{ \Carbon\Carbon::parse($sync_time)->format('g:i A') }}
                                    @if ($sync_schedule_type === 'weekly' && count($sync_days) > 0)
                                        <br><small class="text-muted">
                                            On:
                                            @php
                                                $selectedDays = array_map(function ($day) use ($daysOfWeek) {
                                                    return $daysOfWeek[$day] ?? $day;
                                                }, $sync_days);
                                            @endphp
                                            {{ implode(', ', $selectedDays) }}
                                        </small>
                                    @endif
                                </p>
                            </div>
                            <!-- Next Sync -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="bi bi-clock me-1"></i>Next Sync</label>
                                <p class="form-control-plaintext">
                                    @if ($device_id)
                                        @php
                                            $device = \App\Models\DeviceManagement::find($device_id);
                                            $nextSync = $device ? $device->next_sync_at : null;
                                        @endphp
                                        {{ $nextSync ? $nextSync->format('Y-m-d H:i:s') : 'Not scheduled' }}
                                    @else
                                        Not scheduled
                                    @endif
                                </p>
                            </div>
                            <!-- Last Sync -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><i class="bi bi-clock-history me-1"></i>Last Sync</label>
                                <p class="form-control-plaintext">
                                    {{ $last_sync_at ? \Carbon\Carbon::parse($last_sync_at)->diffForHumans() : 'Never' }}
                                    @if ($last_sync_result)
                                        <br><small class="text-muted">{{ $last_sync_result }}</small>
                                    @endif
                                </p>
                            </div>
                        @endif
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
    <!-- Export Employee Modal -->
    <div class="modal fade" id="exportEmployeeModal" tabindex="-1" aria-labelledby="exportEmployeeModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportEmployeeModalLabel">
                        <i class="bi bi-download me-2"></i>Export Employees
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="closeExportModal"></button>
                </div>
                <div class="modal-body">
                    <!-- Search and Select All -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live.debounce.300ms="employeeSearch"
                                    class="form-control" placeholder="Search employees...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model.live="selectAll"
                                    id="selectAll">
                                <label class="form-check-label fw-medium" for="selectAll">
                                    Select All ({{ count($exportEmployees) }} employees)
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- Selected Count -->
                    <div class="alert alert-info py-2 mb-3">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            Selected: {{ count($selectedEmployees) }} employees
                        </small>
                    </div>
                    <!-- Employees List -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-hover">
                            <thead class="sticky-top bg-light">
                                <tr>
                                    <th width="50px" class="text-center">Select</th>
                                    <th>Employee Code</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Joining Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($exportEmployees as $employee)
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" wire:model.live="selectedEmployees"
                                                value="{{ $employee['emp_id'] }}" class="form-check-input">
                                        </td>
                                        <td>
                                            <small class="fw-medium">{{ $employee['emp_code'] }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $employee['emp_fname'] }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $employee['emp_lname'] ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $employee['emp_date_of_joining'] ? \Carbon\Carbon::parse($employee['emp_date_of_joining'])->format('M d, Y') : 'N/A' }}
                                            </small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">
                                            <i class="bi bi-person-x display-4 d-block mb-2"></i>
                                            No employees found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                        wire:click="closeExportModal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-outline-primary" wire:click="downloadBusinessEmployee"
                        wire:loading.attr="disabled" {{ empty($selectedEmployees) ? 'disabled' : '' }}>
                        <span wire:loading.remove>
                            <i class="bi bi-download me-1"></i>Export Selected
                        </span>
                        <span wire:loading>
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                            Exporting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Date Range Sync Popup -->
    @if ($showRangeSyncPopup)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-calendar-range me-2"></i>Select Date Range to Sync
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeSyncPopup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2 mb-3">
                            <small>
                                <i class="bi bi-info-circle me-1"></i>
                                Select date range for manual sync. Auto-sync will continue to work normally.
                            </small>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="rangeStartDate" class="form-label">
                                    <i class="bi bi-calendar-minus me-1"></i>From Date
                                </label>
                                <input type="date" wire:model="rangeStartDate" id="rangeStartDate"
                                    class="form-control @error('rangeStartDate') is-invalid @enderror"
                                    max="{{ now()->format('Y-m-d') }}">
                                @error('rangeStartDate')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="rangeEndDate" class="form-label">
                                    <i class="bi bi-calendar-plus me-1"></i>To Date
                                </label>
                                <input type="date" wire:model="rangeEndDate" id="rangeEndDate"
                                    class="form-control @error('rangeEndDate') is-invalid @enderror"
                                    max="{{ now()->format('Y-m-d') }}">
                                @error('rangeEndDate')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        @php
                            $daysDifference = 0;
                            if ($rangeStartDate && $rangeEndDate) {
                                try {
                                    $start = \Carbon\Carbon::parse($rangeStartDate);
                                    $end = \Carbon\Carbon::parse($rangeEndDate);
                                    $daysDifference = $start->diffInDays($end) + 1;
                                } catch (\Exception $e) {
                                    $daysDifference = 0;
                                }
                            }
                        @endphp
                        @if ($daysDifference > 0)
                            <div class="alert alert-warning py-2">
                                <small>
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    This will sync <strong>{{ $daysDifference }} day(s)</strong> of data
                                    ({{ $rangeStartDate }} to {{ $rangeEndDate }}).
                                </small>
                            </div>
                        @endif
                        <!-- Quick date buttons -->
                        <div class="mt-3">
                            <small class="text-muted mb-2 d-block">Quick select:</small>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    wire:click="$set('rangeStartDate', '{{ now()->format('Y-m-d') }}')">
                                    Today
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    wire:click="$set('rangeStartDate', '{{ now()->subDays(1)->format('Y-m-d') }}'); $set('rangeEndDate', '{{ now()->subDays(1)->format('Y-m-d') }}')">
                                    Yesterday
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    wire:click="$set('rangeStartDate', '{{ now()->subDays(6)->format('Y-m-d') }}'); $set('rangeEndDate', '{{ now()->format('Y-m-d') }}')">
                                    Last 7 Days
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    wire:click="$set('rangeStartDate', '{{ now()->startOfMonth()->format('Y-m-d') }}'); $set('rangeEndDate', '{{ now()->format('Y-m-d') }}')">
                                    This Month
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeSyncPopup">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-outline-primary" wire:click="executeRangeSync"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="bi bi-play-circle me-1"></i>Start Sync
                            </span>
                            <span wire:loading>
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                Starting...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <script>
        // Main application script for Device Management
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize when Livewire is ready
            document.addEventListener('livewire:initialized', function() {
                initializeSyncSystem();
                initializeModalSystem();
                initializeRangeSync();
                initializeEventListeners();
                initializeRealTimeUpdates();
                startAutoSyncMonitoring();
            });
            /**
             * Initialize the sync system
             */
            function initializeSyncSystem() {
                console.log('🔧 Sync system initialized');
                // Poll for sync status updates every 60 seconds
                const syncPollInterval = setInterval(() => {
                    if (!document.hidden) {
                        Livewire.dispatch('check-auto-sync');
                    }
                }, 60000);
                // Handle page visibility changes
                document.addEventListener('visibilitychange', function() {
                    if (document.hidden) {
                        clearInterval(syncPollInterval);
                    } else {
                        startAutoSyncMonitoring();
                    }
                });
            }
            /**
             * Initialize modal system
             */
            function initializeModalSystem() {
                console.log('🔧 Modal system initialized');
                // Function to close modal and remove backdrop
                function closeModal(modalId) {
                    const modalElement = document.getElementById(modalId);
                    if (!modalElement) return;
                    const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                    modal.hide();
                    // Clean up backdrop
                    cleanupModalBackdrop();
                }
                // Function to open modal
                function openModal(modalId) {
                    const modalElement = document.getElementById(modalId);
                    if (!modalElement) return;
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
                // Clean up modal backdrop
                function cleanupModalBackdrop() {
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
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
                // Ensure backdrop is removed when modal is hidden
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.addEventListener('hidden.bs.modal', () => {
                        cleanupModalBackdrop();
                    });
                });
                // Handle custom close buttons
                document.querySelectorAll('.custom-close-button').forEach(button => {
                    button.addEventListener('click', function() {
                        const modal = this.closest('.modal');
                        if (modal) {
                            const modalInstance = bootstrap.Modal.getInstance(modal);
                            if (modalInstance) {
                                modalInstance.hide();
                            }
                        }
                    });
                });
            }
            /**
             * Initialize date range sync functionality
             */
            function initializeRangeSync() {
                console.log('🔧 Range sync initialized');
                // Handle range sync popup
                Livewire.on('show-range-sync-popup', () => {
                    const popup = document.querySelector('[x-show="showRangeSyncPopup"]');
                    if (popup) {
                        // Focus on first input when popup opens
                        setTimeout(() => {
                            const firstInput = popup.querySelector('input[type="date"]');
                            if (firstInput) firstInput.focus();
                        }, 100);
                    }
                });
                // Set up date range input constraints
                setupDateInputConstraints();
                // Set up quick date buttons
                setupQuickDateButtons();
            }
            /**
             * Set up date input constraints
             */
            function setupDateInputConstraints() {
                // Set max date to today for both inputs
                const today = new Date().toISOString().split('T')[0];
                document.querySelectorAll('input[type="date"]').forEach(input => {
                    input.max = today;
                    // Add input event for validation
                    input.addEventListener('change', function() {
                        validateDateRange();
                    });
                });
            }
            /**
             * Validate date range
             */
            function validateDateRange() {
                const startDateInput = document.getElementById('rangeStartDate');
                const endDateInput = document.getElementById('rangeEndDate');
                if (!startDateInput || !endDateInput) return;
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                if (startDate && endDate && startDate > endDate) {
                    // Swap dates if start is after end
                    const temp = startDateInput.value;
                    startDateInput.value = endDateInput.value;
                    endDateInput.value = temp;
                    // Update Livewire model
                    Livewire.dispatch('sync-date-range-updated', {
                        startDate: startDateInput.value,
                        endDate: endDateInput.value
                    });
                }
            }
            /**
             * Set up quick date buttons
             */
            function setupQuickDateButtons() {
                // Quick date calculations
                const quickDateFunctions = {
                    'today': () => new Date().toISOString().split('T')[0],
                    'yesterday': () => {
                        const date = new Date();
                        date.setDate(date.getDate() - 1);
                        return date.toISOString().split('T')[0];
                    },
                    'last7days': () => {
                        const date = new Date();
                        date.setDate(date.getDate() - 6);
                        return date.toISOString().split('T')[0];
                    },
                    'thisMonth': () => {
                        const date = new Date();
                        return new Date(date.getFullYear(), date.getMonth(), 1)
                            .toISOString().split('T')[0];
                    }
                };
                // Attach quick date functionality
                document.querySelectorAll('[data-quick-date]').forEach(button => {
                    button.addEventListener('click', function() {
                        const dateType = this.dataset.quickDate;
                        if (quickDateFunctions[dateType]) {
                            const startDate = quickDateFunctions[dateType]();
                            const endDate = quickDateFunctions['today']();
                            // Update inputs
                            const startInput = document.getElementById('rangeStartDate');
                            const endInput = document.getElementById('rangeEndDate');
                            if (startInput) startInput.value = startDate;
                            if (endInput) endInput.value = dateType === 'yesterday' ? startDate :
                                endDate;
                            // Trigger change events
                            if (startInput) startInput.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
                            if (endInput) endInput.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
                        }
                    });
                });
            }
            /**
             * Initialize event listeners
             */
            function initializeEventListeners() {
                console.log('🔧 Event listeners initialized');
                // Handle escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        // Close range sync popup
                        if (Livewire.get('showRangeSyncPopup')) {
                            Livewire.dispatch('closeSyncPopup');
                        }
                        // Close any open modals
                        const openModals = document.querySelectorAll('.modal.show');
                        openModals.forEach(modal => {
                            const modalInstance = bootstrap.Modal.getInstance(modal);
                            if (modalInstance) {
                                modalInstance.hide();
                            }
                        });
                    }
                });
                // Close popup when clicking outside
                document.addEventListener('click', function(e) {
                    // Range sync popup
                    if (Livewire.get('showRangeSyncPopup') && e.target.classList.contains('modal')) {
                        Livewire.dispatch('closeSyncPopup');
                    }
                    // Branch dropdown
                    const branchDropdown = document.querySelector('[x-data="{ open: false }"]');
                    if (branchDropdown && !branchDropdown.contains(e.target)) {
                        branchDropdown.setAttribute('x-data', '{ open: false }');
                    }
                });
                // Prevent table row click when clicking on actions
                document.querySelectorAll('#device-table-dynamic tbody tr').forEach(row => {
                    row.addEventListener('click', function(e) {
                        // Don't navigate if clicking on actions cell or dropdown
                        if (e.target.closest('td:last-child') ||
                            e.target.closest('.dropdown') ||
                            e.target.closest('button') ||
                            e.target.closest('a')) {
                            e.stopPropagation();
                        }
                    });
                });
                // Initialize tooltips
                initializeTooltips();
                // Initialize data table functionality
                initializeDataTable();
            }
            /**
             * Initialize tooltips
             */
            function initializeTooltips() {
                const tooltipTriggerList = [].slice.call(
                    document.querySelectorAll('[data-bs-toggle="tooltip"]')
                );
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl, {
                        trigger: 'hover focus'
                    });
                });
            }
            /**
             * Initialize data table functionality
             */
            function initializeDataTable() {
                // Show entries functionality
                const showEntriesSelect = document.querySelector('[data-length]');
                if (showEntriesSelect) {
                    showEntriesSelect.addEventListener('change', function() {
                        updateShowEntriesText(this.value);
                    });
                    // Initial update
                    updateShowEntriesText(showEntriesSelect.value);
                }
                // Search functionality indicator
                const searchInput = document.querySelector('[data-search]');
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        toggleSearchClearButton(this.value);
                    });
                    // Initial state
                    toggleSearchClearButton(searchInput.value);
                }
            }
            /**
             * Update "Showing X entries" text
             */
            function updateShowEntriesText(perPage) {
                const showEntriesElement = document.getElementById('custom-show-entries');
                if (showEntriesElement) {
                    showEntriesElement.textContent = `Showing ${perPage} entries`;
                }
            }
            /**
             * Toggle search clear button visibility
             */
            function toggleSearchClearButton(searchValue) {
                const clearButton = document.querySelector('[wire\\:click="clearSearch"]');
                if (clearButton) {
                    if (searchValue && searchValue.length > 0) {
                        clearButton.style.display = 'inline-block';
                    } else {
                        clearButton.style.display = 'none';
                    }
                }
            }
            /**
             * Initialize real-time updates
             */
            function initializeRealTimeUpdates() {
                console.log('🔧 Real-time updates initialized');
                // Auto-sync completion handler
                Livewire.on('auto-sync-completed', (event) => {
                    handleAutoSyncCompletion(event);
                });
                // Show alerts
                Livewire.on('show-alert', (data) => {
                    showToastNotification(data[0].type || 'success', data[0].message || '');
                });
                // Export completion
                Livewire.on('export-completed', (data) => {
                    showToastNotification('success', data[0].message || 'Export completed successfully!');
                });
            }
            /**
             * Handle auto-sync completion
             */
            function handleAutoSyncCompletion(event) {
                console.log('🔄 Auto-sync completed:', event);
                // Show progress bar animation
                const progressBar = document.getElementById('sync-progress-bar');
                const progressContainer = document.getElementById('sync-progress');
                if (progressBar && progressContainer) {
                    progressContainer.classList.remove('d-none');
                    // Animate progress bar
                    let width = 0;
                    const interval = setInterval(() => {
                        if (width >= 100) {
                            clearInterval(interval);
                            // Hide progress after 3 seconds
                            setTimeout(() => {
                                progressBar.style.width = '0%';
                                progressContainer.classList.add('d-none');
                            }, 3000);
                        } else {
                            width += 10;
                            progressBar.style.width = width + '%';
                        }
                    }, 50);
                }
                // Show notification
                if (event.synced > 0 || event.errors > 0) {
                    const message =
                        `Auto-sync completed: ${event.synced} devices synced${event.errors > 0 ? `, ${event.errors} errors` : ''}`;
                    showToastNotification('info', message);
                }
                // Update sync status badge
                updateSyncStatusBadge();
            }
            /**
             * Start auto-sync monitoring
             */
            function startAutoSyncMonitoring() {
                console.log('🔍 Starting auto-sync monitoring');
                // Update sync status badge
                updateSyncStatusBadge();
                // Add animation class
                const statusBadge = document.getElementById('sync-status-badge');
                if (statusBadge) {
                    statusBadge.classList.add('auto-sync-indicator');
                }
            }
            /**
             * Update sync status badge
             */
            function updateSyncStatusBadge() {
                const statusElement = document.getElementById('sync-status-text');
                const badgeElement = document.getElementById('sync-status-badge');
                if (statusElement && badgeElement) {
                    // You can add logic here to check actual sync status
                    // For now, just show "Active"
                    statusElement.textContent = 'Active';
                    // Remove and re-add animation class to restart animation
                    badgeElement.classList.remove('auto-sync-indicator');
                    setTimeout(() => {
                        badgeElement.classList.add('auto-sync-indicator');
                    }, 10);
                }
            }
            /**
             * Show toast notification
             */
            function showToastNotification(type, message) {
                // Use SweetAlert2 for toast notifications
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    },
                    customClass: {
                        popup: 'swal2-toast-custom'
                    }
                });
                Toast.fire({
                    icon: type,
                    title: message,
                    background: getToastBackgroundColor(type),
                    color: '#fff'
                });
            }
            /**
             * Get toast background color based on type
             */
            function getToastBackgroundColor(type) {
                const colors = {
                    'success': '#198754',
                    'error': '#dc3545',
                    'warning': '#ffc107',
                    'info': '#0dcaf0',
                    'question': '#6c757d'
                };
                return colors[type] || colors.info;
            }
            /**
             * Format date for display
             */
            function formatDate(dateString) {
                if (!dateString) return 'N/A';
                try {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } catch (e) {
                    return dateString;
                }
            }
            /**
             * Calculate days between dates
             */
            function calculateDaysBetween(startDate, endDate) {
                try {
                    const start = new Date(startDate);
                    const end = new Date(endDate);
                    const diffTime = Math.abs(end - start);
                    return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                } catch (e) {
                    return 0;
                }
            }
            /**
             * Debounce function for performance
             */
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
            /**
             * Throttle function for performance
             */
            function throttle(func, limit) {
                let inThrottle;
                return function() {
                    const args = arguments;
                    const context = this;
                    if (!inThrottle) {
                        func.apply(context, args);
                        inThrottle = true;
                        setTimeout(() => inThrottle = false, limit);
                    }
                };
            }
            // Global utility functions
            window.DeviceManagement = {
                formatDate: formatDate,
                calculateDaysBetween: calculateDaysBetween,
                showToast: showToastNotification,
                debounce: debounce,
                throttle: throttle
            };
            console.log('✅ Device Management system fully initialized');
        });
        // Additional standalone functions for modal control
        function openDeviceModal(deviceId = null) {
            if (deviceId) {
                Livewire.dispatch('edit', {
                    id: deviceId
                });
            } else {
                Livewire.dispatch('addDevice');
            }
        }

        function syncDeviceNow(deviceSn, businessId) {
            Livewire.dispatch('syncAttendance', {
                device_sn: deviceSn,
                businessId: businessId
            });
        }

        function syncDeviceRange(deviceId) {
            Livewire.dispatch('showSyncPopup', {
                id: deviceId
            });
        }

        function toggleAutoSync(deviceId) {
            Livewire.dispatch('toggleAutoSync', {
                id: deviceId
            });
        }

        function confirmDeviceDelete(deviceId, deviceName) {
            Livewire.dispatch('confirmDelete', {
                id: deviceId
            });
        }
        // Export modal functions
        function openExportModal() {
            Livewire.dispatch('openExportModal');
        }

        function closeExportModal() {
            Livewire.dispatch('closeExportModal');
        }
        // Quick sync function (without date range)
        function quickSyncDevice(deviceSn) {
            Livewire.dispatch('quickSync', {
                device_sn: deviceSn
            });
        }
        // Auto sync functions
        function triggerAutoSyncAll() {
            Livewire.dispatch('autoSyncAllDevices');
        }

        function checkSyncStatus() {
            Livewire.dispatch('check-auto-sync');
        }
        // View device details
        function viewDeviceDetails(deviceId) {
            Livewire.dispatch('view', {
                id: deviceId
            });
        }
        // Handle table row click (navigate to attendance logs)
        function navigateToAttendanceLogs(businessUniqueId, deviceSerial) {
            if (businessUniqueId && deviceSerial) {
                window.location.href = `/attendance/logs/${businessUniqueId}/${deviceSerial}`;
            }
        }
        // Add CSS for animations
        const style = document.createElement('style');
        style.textContent = `
    /* Animation for sync status */
    .auto-sync-indicator {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    /* Progress bar animations */
    .sync-progress-bar {
        transition: width 0.3s ease;
    }
    /* Toast customizations */
    .swal2-toast-custom {
        font-size: 14px !important;
        border-radius: 8px !important;
    }
    /* Modal animations */
    .modal.fade.show {
        display: block !important;
        animation: fadeIn 0.3s;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    /* Table hover effects */
    #device-table-dynamic tbody tr {
        transition: background-color 0.2s;
    }
    #device-table-dynamic tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    /* Button hover effects */
    .custom-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    /* Loading spinner animation */
    .spinner-border {
        animation: spinner-border 0.75s linear infinite;
    }
    @keyframes spinner-border {
        to { transform: rotate(360deg); }
    }
    /* Date range popup styles */
    .date-range-popup {
        animation: slideDown 0.3s ease-out;
    }
    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    /* Status indicator dots */
    .device-status-indicator {
        animation: statusPulse 1.5s infinite;
    }
    @keyframes statusPulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    /* Fade out for alerts */
    .alert-auto-dismiss {
        animation: fadeOut 0.5s ease-out 4.5s forwards;
    }
    @keyframes fadeOut {
        to { opacity: 0; }
    }
`;
        // document.head.appendChild(style);
        // console.log('🎨 CSS animations loaded');
        // // Error handling for the entire application
        // window.addEventListener('error', function(event) {
        //     console.error('Application error:', event.error);
        //     // Show user-friendly error message
        //     if (window.Swal) {
        //         Swal.fire({
        //             icon: 'error',
        //             title: 'Oops...',
        //             text: 'Something went wrong! Please refresh the page and try again.',
        //             footer: '<a href="/support">Need help?</a>'
        //         });
        //     }
        // });
        // Handle offline/online status
        window.addEventListener('online', function() {
            showToastNotification('success', 'Back online! Sync will resume.');
            Livewire.dispatch('check-auto-sync');
        });
        window.addEventListener('offline', function() {
            showToastNotification('warning', 'You are offline. Some features may be limited.');
        });
        // console.log('🛡️ Error handling and offline detection initialized');
    </script>
</div>
