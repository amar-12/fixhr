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
        {{-- ═══ Filter panel ═══ --}}
        <div class="ef-panel">
            {{-- Header --}}
            <div class="ef-panel-header">
                <span class="ef-panel-title">Payroll Period Report Filters</span>

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
                        {{-- $filterFields is passed from the controller --}}
                        @foreach ($filterFields as $key => $label)
                            @if (array_key_exists($key, $filters))
                                <div class="ef-switch-row">
                                    <label for="{{ $key }}Pp3SwCb">{{ $label }}</label>
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
                {{-- Conditional: Employee Status + Employee/Code --}}
                @if ($filters['employee'])
                    <div class="col-md-6">
                        <label class="ef-field-label">Employee Status</label>
                        <div class="ef-input-wrap">
                            <select wire:model.live="employeeStatusId" class="ef-control">
                                <option value="">All</option>
                                @foreach ($employeeStatus as $status)
                                    <option value="{{ $status->m_id }}">{{ $status->m_name }}</option>
                                @endforeach
                            </select>
                            <span class="ef-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </span>
                        </div>
                        @error('employeeStatusId')
                            <span class="text-danger" style="font-size:11px;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="ef-field-label">Employee / Code</label>
                        <div class="ef-input-wrap">
                            <select wire:model="selectedEmployeeId" class="ef-control">
                                <option value="">All</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->emp_id }}">{{ $emp->emp_full_name }}</option>
                                @endforeach
                            </select>
                            <span class="ef-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </span>
                        </div>
                        @error('selectedEmployeeId')
                            <span class="text-danger" style="font-size:11px;">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
                {{-- Conditional: Financial Year --}}
                @if ($filters['financial_year'])
                    <div class="col-md-6">
                        <label class="ef-field-label">Financial Year</label>
                        <div class="ef-input-wrap">
                            <select wire:model="selectedFYId" class="ef-control">
                                <option value="">Select Year</option>
                                @foreach ($financialYears as $year)
                                    <option value="{{ $year->fy_id }}">{{ $year->fy_year }}</option>
                                @endforeach
                            </select>
                            <span class="ef-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </span>
                        </div>
                        @error('selectedFYId')
                            <span class="text-danger" style="font-size:11px;">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
                {{-- Always visible: Payroll Period --}}
                <div class="col-md-6">
                    <label class="ef-field-label">Payroll Period</label>
                    <div class="ef-input-wrap">
                        <select wire:model="selectedPayrollPeriodId" class="ef-control">
                            <option value="">Select Period</option>
                            @foreach ($payrollPeriods as $period)
                                <option value="{{ $period->pp_id }}">{{ $period->pp_name }}</option>
                            @endforeach
                        </select>
                        <span class="ef-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </span>
                    </div>
                    @error('selectedPayrollPeriodId')
                        <span class="text-danger" style="font-size:11px;">{{ $message }}</span>
                    @enderror
                </div>
                {{-- Conditional: Department --}}
                @if ($filters['department'])
                    <div class="col-md-6">
                        <label class="ef-field-label">Department</label>
                        <div class="ef-input-wrap">
                            <select wire:model="selectedDepartmentId" class="ef-control">
                                <option value="">All</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->d_id }}">{{ $dept->d_name }}</option>
                                @endforeach
                            </select>
                            <span class="ef-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </span>
                        </div>
                        @error('selectedDepartmentId')
                            <span class="text-danger" style="font-size:11px;">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
                {{-- Divider --}}
                <div class="col-12">
                    <div class="ef-divider"></div>
                </div>
                {{-- Round Off toggle --}}
                <div class="col-12">
                    <div class="ef-roundoff-row">
                        <div class="form-check mb-0">
                            <input type="checkbox" class="form-check-input" id="roundOffPp3"
                                wire:model.live="roundOffValues" style="cursor:pointer;">
                            <label class="form-check-label" for="roundOffPp3">Round Off All Values</label>
                        </div>
                        @if ($roundOffValues)
                            <p class="ef-roundoff-note">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    style="width:12px;height:12px;margin-right:3px;vertical-align:middle;">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" y1="8" x2="12" y2="12" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" />
                                </svg>
                                All monetary values will be rounded to the nearest whole number
                            </p>
                        @endif
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
