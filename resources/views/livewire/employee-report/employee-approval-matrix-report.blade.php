<div class="row d-flex justify-content-center align-items-start ef-wrapper"
    style="padding-top: 20px; padding-bottom: 20px;">
    <div class="col-md-10">
        {{-- Alert --}}
        @if (session()->has('error'))
            <div class="alert alert-danger ef-alert alert-dismissible fade show d-flex justify-content-between align-items-center mb-3"
                role="alert">
                <div><strong>Error!</strong> {{ session('error') }}</div>
                <button type="button" class="custom-close-button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        {{-- Panel --}}
        <div class="ef-panel">
            {{-- Header --}}
            <div class="ef-panel-header">
                <span class="ef-panel-title">Approval Report Filters</span>
                  <div class="ef-filter-panel-wrap d-flex align-items-center gap-2">
                     @if (collect($filters)->contains(true))
                            <button type="button" class="ef-btn-reset" wire:click="resetFilters">Clear all filters</button>
                        @endif
                    <button class="ef-filter-toggle-btn" type="button" wire:click="toggleFilterPanel"
                        wire:loading.attr="disabled" wire:target="toggleFilterPanel, toggleFilter">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M15 19.88c.04.3-.06.62-.29.83a.996.996 0 0 1-1.41 0L9.29 16.7a.99.99 0 0 1-.29-.83v-5.12L4.21 4.62a1 1 0 0 1 .17-1.4c.19-.14.4-.22.62-.22h14c.22 0 .43.08.62.22a1 1 0 0 1 .17 1.4L15 10.75z" />
                        </svg>
                        Filters
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            style="{{ $showFilterPanel ? 'transform:rotate(180deg);' : '' }}transition:.2s">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    {{-- Dropdown --}}
                    <div class="ef-filter-panel" style="{{ $showFilterPanel ? 'display:block' : 'display:none' }}">
                        <div class="ef-fp-heading">Toggle Filters</div>
                        @foreach ($filterFields as $key => $label)
                            @if (array_key_exists($key, $filters))
                                <div class="ef-switch-row">
                                    <label for="{{ $key }}ApSwCb">{{ $label }}</label>
                                    <div class="form-check form-switch mb-0 ms-2">
                                        <input type="checkbox" class="form-check-input" role="switch"
                                            id="{{ $key }}DaSwCb"
                                            wire:model.live="filters.{{ $key }}">
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>{{-- /header --}}
            {{-- Fields grid --}}
            <div class="row gx-3 gy-3 ef-fields-grid {{ $showFilterPanel ? 'ef-grid-shrink' : '' }}">
                {{-- Employee Status --}}
                 <div class="col-md-6">
                    <label class="ef-field-label">Employee Status</label>
                    <div class="ef-input-wrap">
                        <!-- Bootstrap Dropdown -->
                        <div class="dropdown w-100" x-data="{
                            selected: 'All'
                        }" wire:ignore>
                            <button
                                class="ef-control w-100 text-start d-flex align-items-center justify-content-between"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span x-text="selected" class="ef-selected-value">All</span>
                                <span class="ef-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polyline points="6 9 12 15 18 9" />
                                    </svg>
                                </span>
                            </button>
                            <ul class="dropdown-menu w-100 ef-results">
                                @php
                                    $options = ['' => 'All'] +
                                        $employeeStatus->pluck('m_name', 'm_id')->toArray() + [
                                            'resigned' => 'Resigned',
                                        ];
                                @endphp
                                @foreach ($options as $value => $label)
                                    <li>
                                        <span class=""
                                            @click.prevent="
                               selected = '{{ $label }}';
                               $wire.set('employeeStatusId', '{{ $value }}')
                           "
                                            style="cursor: pointer; display: block;">
                                            {{ $label }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                {{-- Employee / Code --}}
                <x-search-input label="Employee / Code" wireModel="search" :results="$employees"
                    selectMethod="selectEmployee" idField="emp_id" nameField="emp_full_name"
                    errorField="selectedEmployeeId" mainCol="col-md-6" />
                {{-- Employee Type --}}
                @if ($filters['employeeType'])
                    <div class="col-md-6">
                    <label class="ef-field-label">Employee Type</label>
                    <div class="ef-input-wrap">
                        <div class="dropdown w-100" x-data="{
                            selected: 'All'
                        }" wire:ignore>
                            <!-- Button -->
                            <button
                                class="ef-control w-100 text-start d-flex align-items-center justify-content-between"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span x-text="selected" class="ef-selected-value">All</span>
                                <span class="ef-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polyline points="6 9 12 15 18 9" />
                                    </svg>
                                </span>
                            </button>
                            <!-- Dropdown Menu -->
                            <ul class="dropdown-menu w-100 ef-results">
                                @php
                                    $typeOptions = ['' => 'All'] + $employeeTypes->pluck('m_name', 'm_id')->toArray();
                                @endphp
                                @foreach ($typeOptions as $value => $label)
                                    <li>
                                        <span
                                            @click.prevent="
                                selected = '{{ $label }}';
                                $wire.set('selectedEmployeeTypeId', '{{ $value }}');
                                $wire.selectEmployeeType('{{ $value }}');
                            "
                                            style="cursor: pointer; display: block;">
                                            {{ $label }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
                {{-- Role --}}
                @if ($filters['role'])
                    <x-search-input label="Role" wireModel="searchRole" :results="$roles" selectMethod="selectRole"
                        idField="role_id" nameField="role_name" errorField="selectedRoleId" mainCol="col-md-6" />
                @endif
                {{-- Department --}}
                @if ($filters['department'])
                    <x-search-input label="Department" wireModel="searchDepartment" :results="$departments"
                        selectMethod="selectDepartment" idField="d_id" nameField="d_name" :selected="$selectedDepartmentId"
                        errorField="selectedDepartmentId" mainCol="col-md-6" />
                @endif
                {{-- Shift --}}
                @if ($filters['shift'])
                    <x-search-input label="Shift" wireModel="searchShift" :results="$shifts" selectMethod="selectShift"
                        idField="pst_id" nameField="pst_name" errorField="selectedShiftId" mainCol="col-md-6" />
                @endif
                {{-- Designation --}}
                @if ($filters['designation'])
                    <x-search-input label="Designation" wireModel="searchDesignation" :results="$designations"
                        selectMethod="selectDesignation" idField="dg_id" nameField="dg_name"
                        errorField="selectedDesignationId" mainCol="col-md-6" />
                @endif
                {{-- Work Mode --}}
                @if ($filters['workMode'])
                    <x-search-input label="Work Mode" wireModel="searchWorkMode" :results="$workMode"
                        selectMethod="selectLocation" idField="m_id" nameField="m_name"
                        errorField="selectedWorkModeId" mainCol="col-md-6" />
                @endif
                {{-- Check In Method --}}
                @if ($filters['checkingMethod'])
                    <x-search-input label="Check In Method" wireModel="searchCheckingMethod" :results="$checkingMethods"
                        selectMethod="selectCheckingMethod" idField="m_id" nameField="m_name"
                        errorField="selectedCheckingMethodId" mainCol="col-md-6" />
                @endif
                {{-- Dealership --}}
                @if ($filters['dealership'])
                    <x-search-input label="Dealership" wireModel="searchDealer" :results="$dealers"
                        selectMethod="selectDealer" idField="dlr_id" nameField="dlr_name"
                        errorField="selectedDealerId" mainCol="col-md-6" />
                @endif
                {{-- Branch --}}
                @if ($filters['branch'])
                    <x-search-input label="Branch" wireModel="searchBranch" :results="$branches"
                        selectMethod="selectBranch" idField="br_id" nameField="br_name"
                        errorField="selectedBranchId" mainCol="col-md-6" />
                @endif
                {{-- Job Status --}}
                @if ($filters['jobStatus'])
                    <x-search-input label="Job Status" wireModel="searchJobStatus" :results="$jobStatuses"
                        selectMethod="selectJobStatus" idField="m_id" nameField="m_name"
                        errorField="selectedJobStatusId" mainCol="col-md-6" />
                @endif
                {{-- Grade --}}
                @if ($filters['grade'])
                    <x-search-input label="Grade" wireModel="searchGrade" :results="$grades"
                        selectMethod="selectGrade" idField="g_id" nameField="g_name" :selected="$selectedGradeId"
                        errorField="selectedGradeId" mainCol="col-md-6" />
                @endif
                {{-- Reporting Manager --}}
                @if ($filters['reportingManager'])
                    <x-search-input label="Reporting Manager" wireModel="searchReportingManager" :results="$reportingManagers"
                        selectMethod="selectReportingManager" idField="emp_id" nameField="emp_full_name"
                        errorField="selectedReportingManagerId" mainCol="col-md-6" />
                @endif
                {{-- ── Approval Modules multi-select ── --}}
                <div class="col-md-6" x-data="{ open: false }" @click.outside="open = false">
                    <label class="ef-field-label">Approval Modules</label>
                    <div class="position-relative">
                        {{-- Trigger box --}}
                        <div class="ef-multi-select-box" :class="{ open }" @click="open = !open">
                            <span class="text-truncate pe-2" style="font-size:12px;">
                                @if (count($moduleSelection) === 0)
                                    <span style="color:var(--ef-muted)">Select modules…</span>
                                @elseif(count($moduleSelection) === count($availableModules))
                                    <strong>All Modules</strong>
                                @else
                                    <strong>{{ count($moduleSelection) }}</strong> selected
                                @endif
                            </span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                :style="open ? 'transform:rotate(180deg);transition:.2s' : 'transition:.2s'">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </div>
                        {{-- Dropdown --}}
                        <div class="ef-multi-dropdown" x-show="open" x-transition @click.stop>
                            {{-- Select All --}}
                            <div class="ef-multi-item">
                                <input type="checkbox" class="form-check-input" id="ap_select_all_modules"
                                    {{ count($moduleSelection) === count($availableModules) ? 'checked' : '' }}
                                    x-on:change="
                                           const allKeys = @js(collect($availableModules)->pluck('key')->toArray());
                                           $wire.moduleSelection = $el.checked ? allKeys : [];
                                       ">
                                <label for="ap_select_all_modules"
                                    style="font-size:12px; font-weight:600; cursor:pointer;">
                                    Select All ({{ count($availableModules) }})
                                </label>
                            </div>
                            <div class="ef-multi-divider"></div>
                            {{-- Individual modules --}}
                            @forelse ($availableModules as $module)
                                <div class="ef-multi-item">
                                    <input type="checkbox" class="form-check-input" id="ap_mod_{{ $module['key'] }}"
                                        value="{{ $module['key'] }}" wire:model.live="moduleSelection">
                                    <label for="ap_mod_{{ $module['key'] }}" style="font-size:12px; cursor:pointer;">
                                        {{ $module['name'] }}
                                    </label>
                                </div>
                            @empty
                                <div
                                    style="padding:10px 14px; font-size:12px; color:var(--ef-muted); text-align:center;">
                                    No approval modules configured.
                                </div>
                            @endforelse
                        </div>
                        {{-- Count hint --}}
                        <div class="ef-multi-count">
                            <strong>{{ count($moduleSelection) }}</strong> / {{ count($availableModules) }} selected
                        </div>
                    </div>
                </div>
                {{-- Divider --}}
                <div class="col-12">
                    <div class="ef-divider"></div>
                </div>
                {{-- Footer --}}
                <div class="col-12 d-flex align-items-center justify-content-end">
                   
                    <button type="button" class="ef-btn-export" wire:click="generateReport"
                        wire:loading.attr="disabled" wire:loading.class="opacity-50" wire:target="generateReport">
                        <span class="spinner-border spinner-border-sm d-none" wire:loading.class.remove="d-none"
                            wire:target="generateReport" role="status"></span>
                        <span wire:loading.class="d-none" wire:target="generateReport">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="7 10 12 15 17 10" />
                                <line x1="12" y1="15" x2="12" y2="3" />
                            </svg>
                            Export
                        </span>
                        <span class="d-none" wire:loading.class.remove="d-none" wire:target="generateReport">
                            Downloading…
                        </span>
                    </button>
                </div>
            </div>{{-- /.ef-fields-grid --}}
        </div>{{-- /.ef-panel --}}
    </div>
    <script>
        document.addEventListener('livewire:initialized', function() {
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
        });
    </script>
</div>
