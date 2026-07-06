<div class="row d-flex justify-content-center align-items-start ef-wrapper"
    style="padding-top: 20px; padding-bottom: 20px;">
    <div class="col-md-10">
        @if (session()->has('error'))
            <div class="alert alert-danger ef-alert alert-dismissible fade show d-flex justify-content-between align-items-center mb-3"
                role="alert">
                <div><strong>Error!</strong> {{ session('error') }}</div>
                <button type="button" class="custom-close-button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="ef-panel">
            {{-- Header --}}
            <div class="ef-panel-header">
                <span class="ef-panel-title">Gate Pass Report Filters</span>
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
                    <div class="ef-filter-panel" style="{{ $showFilterPanel ? 'display:block' : 'display:none' }}">
                        <div class="ef-fp-heading">Toggle Filters</div>
                        @foreach ([
        'department' => 'Department',
        'shift' => 'Shift',
        'designation' => 'Designation',
        'dealership' => 'Dealership',
        'branch' => 'Branch',
        'grade' => 'Grade',
    ] as $key => $label)
                            @if (array_key_exists($key, $filters))
                                <div class="ef-switch-row">
                                    <label for="{{ $key }}LvSwCb">{{ $label }}</label>
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
            </div>
            {{-- Fields Grid --}}
            <div class="row gx-3 gy-3 ef-fields-grid {{ $showFilterPanel ? 'ef-grid-shrink' : '' }}">
                {{-- Employee Status --}}
                <div class="col-md-6">
                    <label class="ef-field-label">Employee Status</label>
                    <div class="ef-input-wrap">
                        <select wire:model.live="employeeStatusFilter" class="ef-control">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <span class="ef-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <polyline points="6 9 12 15 18 9" />
                            </svg></span>
                    </div>
                    @error('employeeStatusFilter')
                        <span class="text-danger" style="font-size:11px;">{{ $message }}</span>
                    @enderror
                </div>
                {{-- Employee / Code --}}
                <x-search-input label="Employee / Code" wireModel="search" :results="$employees"
                    selectMethod="selectEmployee" idField="emp_id" nameField="emp_full_name"
                    errorField="selectedEmployeeId" mainCol="col-md-6" />
                {{-- From Date --}}
                <div class="col-md-6">
                    <label class="ef-field-label">From Date</label>
                    <div class="ef-input-wrap">
                        <input type="date" wire:model="selectedFromDate" class="ef-control">
                        <span class="ef-icon">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                        </span>
                    </div>
                    @error('selectedFromDate')
                        <span class="text-danger" style="font-size:11px;">{{ $message }}</span>
                    @enderror
                </div>
                {{-- To Date --}}
                <div class="col-md-6">
                    <label class="ef-field-label">To Date</label>
                    <div class="ef-input-wrap">
                        <input type="date" wire:model="selectedToDate" class="ef-control">
                        <span class="ef-icon">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                        </span>
                    </div>
                    @error('selectedToDate')
                        <span class="text-danger" style="font-size:11px;">{{ $message }}</span>
                    @enderror
                </div>
                {{-- Department --}}
                @if ($filters['department'] ?? false)
                    <x-search-input label="Department" wireModel="searchDepartment" :results="$departments"
                        selectMethod="selectDepartment" idField="d_id" nameField="d_name" :selected="$selectedDepartmentId ? $searchDepartment : null"
                        errorField="selectedDepartmentId" mainCol="col-md-6" />
                @endif
                {{-- Shift --}}
                @if ($filters['shift'] ?? false)
                    <x-search-input label="Shift" wireModel="searchShift" :results="$shifts"
                        selectMethod="selectShift" idField="pst_id" nameField="pst_name"
                        errorField="selectedShiftId" mainCol="col-md-6" />
                @endif
                {{-- Designation --}}
                @if ($filters['designation'] ?? false)
                    <x-search-input label="Designation" wireModel="searchDesignation" :results="$designations"
                        selectMethod="selectDesignation" idField="dg_id" nameField="dg_name"
                        errorField="selectedDesignationId" mainCol="col-md-6" />
                @endif
                {{-- Dealership --}}
                @if ($filters['dealership'] ?? false)
                    <x-search-input label="Dealership" wireModel="searchDealer" :results="$dealers"
                        selectMethod="selectDealer" idField="dlr_id" nameField="dlr_name"
                        errorField="selectedDealerId" mainCol="col-md-6" />
                @endif
                {{-- Branch --}}
                @if ($filters['branch'] ?? false)
                    <x-search-input label="Branch" wireModel="searchBranch" :results="$branches"
                        selectMethod="selectBranch" idField="br_id" nameField="br_name"
                        errorField="selectedBranchId" mainCol="col-md-6" />
                @endif
                {{-- Grade --}}
                @if ($filters['grade'] ?? false)
                    <x-search-input label="Grade" wireModel="searchGrade" :results="$grades"
                        selectMethod="selectGrade" idField="g_id" nameField="g_name" :selected="$selectedGradeId ? $searchGrade : null"
                        errorField="selectedGradeId" mainCol="col-md-6" />
                @endif
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
                        <span class="d-none" wire:loading.class.remove="d-none"
                            wire:target="generateReport">Downloading…</span>
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
