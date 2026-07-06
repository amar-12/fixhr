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
        {{-- ═══ Panel ═══ --}}
        <div class="ef-panel">
            {{-- Header --}}
            <div class="ef-panel-header">
                <span class="ef-panel-title">PF Report Filters</span>
            </div>
            {{-- ═══ Fields grid ═══ --}}
            <div class="row gx-3 gy-3">
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
                {{-- Divider --}}
                <div class="col-12">
                    <div class="ef-divider"></div>
                </div>
                {{-- Round Off Toggle --}}
                <div class="col-12">
                    <div class="ef-toggle-row">
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox" class="form-check-input" role="switch" id="roundOffSwitch"
                                wire:model.live="roundOff">
                            <label class="form-check-label fw-semibold" for="roundOffSwitch"
                                style="font-size: var(--ef-font-sm); color: var(--ef-text); cursor:pointer;">
                                Round Off All Values
                            </label>
                        </div>
                        @if ($roundOff)
                            <p class="ef-toggle-info mb-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"
                                    fill="currentColor" viewBox="0 0 16 16"
                                    style="margin-right:4px;vertical-align:middle;">
                                    <path
                                        d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z" />
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
                <div class="col-12 mt-1" x-data="{ preventCollapse: @entangle('preventCollapse') }">
                    <div class="accordion accordion-flush" id="efAccordion">
                        <div class="accordion-item" style="background:transparent;border:none;">
                            <h2 class="accordion-header" id="efAccHead">
                                <button class="accordion-button collapsed ef-additional" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#efAccBody" aria-expanded="false"
                                    aria-controls="efAccBody"
                                    style="background:transparent;border:none;box-shadow:none;padding:4px 0;font-size:12.5px;font-weight:500;color:var(--ef-muted);">
                                    <span class="ef-chevron">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                            width="10" height="10">
                                            <polyline points="6 9 12 15 18 9" />
                                        </svg>
                                    </span>
                                    Additional Details
                                </button>
                            </h2>
                            <div id="efAccBody" class="accordion-collapse collapse" aria-labelledby="efAccHead"
                                data-bs-parent="#efAccordion" wire:ignore.self>
                                <div class="ef-acc-body">
                                    <div class="row gx-3 gy-1">
                                        @php
                                            $filterFields = [
                                                'employeeName' => 'Employee',
                                                'companyName' => 'Company',
                                                'department' => 'Dept',
                                                'designation' => 'Designation',
                                                'gender' => 'Gender',
                                                'aadhaarNo' => 'Aadhaar',
                                                'fathersName' => "Father's Name",
                                                'dob' => 'DOB',
                                                'doj' => 'DOJ',
                                                'dol' => 'DOL',
                                                'lastWorkingDate' => 'Last Working Date',
                                                'reasonOfLeaving' => 'Exit Reason',
                                                'pf' => 'PF',
                                                'eps' => 'EPS',
                                                'ua' => 'UAN',
                                                'monthPeriod' => 'Month/Period',
                                                'daysWorked' => 'Days Worked',
                                                'arrearDays' => 'Arrears',
                                                'lop' => 'LOP',
                                                'grossSalary' => 'Gross',
                                                'basicDa' => 'Basic + DA',
                                                'pfContribution' => 'PF Contribution',
                                                'epsContribution' => 'EPS Contribution',
                                                'edli' => 'EDLI',
                                            ];
                                        @endphp
                                        @foreach ($filterFields as $key => $label)
                                            <div class="col-xl-4 col-lg-4 col-md-6">
                                                <div class="form-check d-flex align-items-center">
                                                    <input type="checkbox" class="form-check-input me-2"
                                                        id="{{ $key }}Checkbox"
                                                        wire:model="filters.{{ $key }}"
                                                        wire:change="toggleFilter('{{ $key }}', {{ $filters[$key] ? 'false' : 'true' }})"
                                                        @click.stop style="cursor:pointer;">
                                                    <label class="form-check-label"
                                                        for="{{ $key }}Checkbox">{{ $label }}</label>
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
                {{-- Footer: export --}}
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
            </div>{{-- /row gx-3 gy-3 --}}
        </div>{{-- /ef-panel --}}
    </div>{{-- /col-md-10 --}}
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
            Livewire.on('component.initialized', () => {
                const btn = document.querySelector('#efAccHead button');
                const collapse = document.querySelector('#efAccBody');
                if (!btn || !collapse) return;
                btn.addEventListener('click', function(e) {
                    if (collapse.classList.contains('show') && @js($this->preventCollapse)) {
                        e.preventDefault();
                        e.stopPropagation();
                    } else {
                        new bootstrap.Collapse(collapse, {
                            toggle: true
                        });
                    }
                });
                collapse.addEventListener('hide.bs.collapse', function(e) {
                    if (@js($this->preventCollapse)) e.preventDefault();
                });
            });
        });
    </script>
@endpush
