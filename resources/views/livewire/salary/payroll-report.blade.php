<div class="row d-flex justify-content-center align-items-start ef-wrapper"
    style="padding-top: 20px; padding-bottom: 20px;">
    <div class="col-md-10">
        {{-- ═══ Filter panel ═══ --}}
        <div class="ef-panel">
            {{-- Header --}}
            <div class="ef-panel-header">
                <span class="ef-panel-title">Payroll Report Filters</span>
                  <div class="ef-filter-panel-wrap d-flex align-items-center gap-2">
                    <button class="ef-filter-toggle-btn" type="button" wire:click="toggleFilterPanel"
                        wire:loading.attr="disabled" wire:target="toggleFilterPanel, toggleFilters">
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
                        @php
                            $filterFields = [
                                'department' => 'Department',
                                'designation' => 'Designation',
                                'checkingMethod' => 'Checking Method',
                                'dealership' => 'Dealership',
                                'branch' => 'Branch',
                                'grade' => 'Grade',
                            ];
                        @endphp
                        @foreach ($filterFields as $key => $label)
                            @if (array_key_exists($key, $filter))
                                <div class="ef-switch-row">
                                    <label for="{{ $key }}PrSwCb">{{ $label }}</label>
                                    <div class="form-check form-switch mb-0 ms-2">
                                        <input type="checkbox" class="form-check-input" role="switch"
                                            id="{{ $key }}DaSwCb" wire:model.live="filter.{{ $key }}">
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
                {{-- Employee / Code --}}
                <x-search-input label="Employee / Code" wireModel="search" :results="$employees"
                    selectMethod="selectEmployee" idField="emp_id" nameField="emp_full_name"
                    errorField="selectedEmployeeId" mainCol="col-md-6" />
                {{-- Financial Year --}}
                <x-search-input label="Financial Year" wireModel="searchFY" :results="$financialYears" selectMethod="selectFY"
                    idField="fy_id" nameField="fy_year" errorField="selectedFYId" mainCol="col-md-6" />
                {{-- Payroll Period --}}
                <x-search-input label="Payroll Period" wireModel="searchPayroll" :results="$payrollPeriods"
                    selectMethod="selectPayrollPeriod" idField="pp_id" nameField="pp_name"
                    errorField="selectedPayrollPeriodId" mainCol="col-md-6" />
                {{-- Payment Mode --}}
                <div class="col-md-6">
                    <label class="ef-field-label">Payment Mode</label>
                    <div class="ef-input-wrap">
                        <select wire:model.live="paymentMode" class="ef-control">
                            <option value="">All</option>
                            <option value="bank">Bank</option>
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                        </select>
                        <span class="ef-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </span>
                    </div>
                    @error('paymentMode')
                        <span class="text-danger" style="font-size:11px;">{{ $message }}</span>
                    @enderror
                </div>
                {{-- Conditional filters --}}
                @if ($filter['grade'])
                    <x-search-input label="Grade" wireModel="searchGrade" :results="$grades" selectMethod="selectGrade"
                        idField="g_id" nameField="g_name" :selected="$selectedGradeId" errorField="selectedGradeId"
                        mainCol="col-md-6" />
                @endif
                @if ($filter['branch'])
                    <x-search-input label="Branch" wireModel="searchBranch" :results="$branches"
                        selectMethod="selectBranch" idField="br_id" nameField="br_name" errorField="selectedBranchId"
                        mainCol="col-md-6" />
                @endif
                @if ($filter['dealership'])
                    <x-search-input label="Dealership" wireModel="searchDealer" :results="$dealers"
                        selectMethod="selectDealer" idField="dlr_id" nameField="dlr_name"
                        errorField="selectedDealerId" mainCol="col-md-6" />
                @endif
                @if ($filter['designation'])
                    <x-search-input label="Designation" wireModel="searchDesignation" :results="$designations"
                        selectMethod="selectDesignation" idField="dg_id" nameField="dg_name"
                        errorField="selectedDesignationId" mainCol="col-md-6" />
                @endif
                @if ($filter['department'])
                    <x-search-input label="Department" wireModel="searchDepartment" :results="$departments"
                        selectMethod="selectDepartment" idField="d_id" nameField="d_name" :selected="$selectedDepartmentId"
                        errorField="selectedDepartmentId" mainCol="col-md-6" />
                @endif
                {{-- Divider --}}
                <div class="col-12">
                    <div class="ef-divider"></div>
                </div>
                {{-- Round Off toggle --}}
                <div class="col-12">
                    <div class="ef-roundoff-row">
                        <div class="form-check mb-0">
                            <input type="checkbox" class="form-check-input" id="roundOff"
                                wire:model.live="roundOffValues" style="cursor:pointer;">
                            <label class="form-check-label" for="roundOff">Round Off All Values</label>
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
                {{-- Additional Details accordion --}}
                <div class="col-12" x-data="{ preventCollapse: @entangle('preventCollapse') }">
                    <div class="accordion accordion-flush" id="efPayrollAcc">
                        <div class="accordion-item" style="background:transparent;border:none;">
                            <h2 class="accordion-header" id="efPayrollAccHead">
                                <button class="ef-acc-trigger accordion-button collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#efPayrollAccBody"
                                    aria-expanded="false" aria-controls="efPayrollAccBody" style="box-shadow:none;">
                                    <span class="ef-chevron">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.5" width="10" height="10">
                                            <polyline points="6 9 12 15 18 9" />
                                        </svg>
                                    </span>
                                    Additional Details
                                </button>
                            </h2>
                            <div id="efPayrollAccBody" class="accordion-collapse collapse"
                                aria-labelledby="efPayrollAccHead" wire:ignore.self>
                                <div class="ef-acc-body">
                                    <div class="row gx-3 gy-1">
                                        @php
                                            $detailFields = [
                                                'employeeContacts' => 'Employee Contacts',
                                                'employeeEarnings' => 'Employee Earnings',
                                                'employeeDeductions' => 'Employee Deductions',
                                                'daysInfo' => 'Days Info',
                                                'earningComponents' => 'Earning Components',
                                                'deductionComponents' => 'Deduction Components',
                                            ];
                                        @endphp
                                        @foreach ($detailFields as $key => $label)
                                            <div class="col-xl-4 col-lg-4 col-md-6">
                                                <div class="form-check d-flex align-items-center">
                                                    <input type="checkbox" class="form-check-input me-2"
                                                        id="{{ $key }}PrDetailCb"
                                                        wire:model="filters.{{ $key }}"
                                                        wire:change="toggleFilter('{{ $key }}', {{ $filters[$key] ? 'false' : 'true' }})"
                                                        @click.stop style="cursor:pointer;">
                                                    <label class="form-check-label"
                                                        for="{{ $key }}PrDetailCb">{{ $label }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
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
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Alpine.data('accordion', () => ({
                preventCollapse: @entangle('preventCollapse')
            }));
            Livewire.hook('message.processed', () => {
                const btn = document.querySelector('#efPayrollAccHead button');
                const collapse = document.querySelector('#efPayrollAccBody');
                if (!btn || !collapse) return;
                btn.addEventListener('click', function(e) {
                    if (collapse.classList.contains('show') && @js($this->preventCollapse)) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                });
                collapse.addEventListener('hide.bs.collapse', function(e) {
                    if (@js($this->preventCollapse)) e.preventDefault();
                });
            });
        });
    </script>
@endpush
