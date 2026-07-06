<div class="row d-flex justify-content-center align-items-center" style="padding-top: 20px; padding-bottom: 20px;">
    <style>
        .custom-close-button {
            background: transparent;
            border: none;
            font-size: 1.25rem;
            font-weight: bold;
            line-height: 1;
            color: white;
            cursor: pointer;
            padding: 0;
            margin-left: 1rem;
            transition: color 0.2s ease-in-out;
        }
        .custom-close-button:hover {
            color: #ddd;
        }
        .custom-close-button::before {
            content: '×';
        }
    </style>
    <div class="col-md-10">
        @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show position-relative d-flex justify-content-between align-items-center" role="alert" style="padding-right: 3rem;">
            <div><strong>Error!</strong> {{ session('error') }}</div>
            <button type="button" class="custom-close-button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <!-- ====================== FILTER SECTION ====================== -->
        <div class="row">
            <!-- Left: Core Filters (FY, Payroll, Payment Mode) -->
            <div class="col-md-8">
                <div class="row gx-3">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-6 col-form-label text-end pe-2">
                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                    Employee Status
                                </label>
                            </div>
                            <div class="col-6">
                                <select wire:model.live="employeeStatusId" class="form-select shadow-sm report" style="font-size: 12px; height: 30px;">
                                    <option value="">All</option>
                                    @foreach ($employeeStatus as $status)
                                    <option value="{{ $status->m_id }}">{{ $status->m_name }}</option>
                                    @endforeach
                                </select>
                                @error('employeeStatusId')
                                <span class="text-danger" style="font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <x-search-input label="Employee/Code" wireModel="search" :results="$employees" selectMethod="selectEmployee" idField="emp_id" nameField="emp_full_name" errorField="selectedEmployeeId" mainCol="col-md-6" />
                    <!-- Financial Year -->
                    <x-search-input label="Financial Year" wireModel="searchFY" :results="$financialYears" selectMethod="selectFY" idField="fy_id" nameField="fy_year" errorField="selectedFYId" mainCol="col-md-6" />
                    <!-- Payroll Period -->
                    <x-search-input label="Payroll Period" wireModel="searchPayroll" :results="$payrollPeriods" selectMethod="selectPayrollPeriod" idField="pp_id" nameField="pp_name" errorField="selectedPayrollPeriodId" mainCol="col-md-6" />
                    @if ($filter['grade'])
                    <x-search-input label="Grade" wireModel="searchGrade" :results="$grades" selectMethod="selectGrade" idField="g_id" nameField="g_name" :selected="$selectedGradeId" errorField="selectedGradeId" mainCol="col-md-6" />
                    @endif
                    @if ($filter['branch'])
                    <x-search-input label="Branch" wireModel="searchBranch" :results="$branches" selectMethod="selectBranch" idField="br_id" nameField="br_name" errorField="selectedBranchId" mainCol="col-md-6" />
                    @endif
                    @if ($filter['dealership'])
                    <x-search-input label="Dealership" wireModel="searchDealer" :results="$dealers" selectMethod="selectDealer" idField="dlr_id" nameField="dlr_name" errorField="selectedDealerId" mainCol="col-md-6" />
                    @endif
                    @if ($filter['designation'])
                    <x-search-input label="Designation" wireModel="searchDesignation" :results="$designations" selectMethod="selectDesignation" idField="dg_id" nameField="dg_name" errorField="selectedDesignationId" mainCol="col-md-6" />
                    @endif
                    @if ($filter['department'])
                    <x-search-input label="Department" wireModel="searchDepartment" :results="$departments" selectMethod="selectDepartment" idField="d_id" nameField="d_name" :selected="$selectedDepartmentId" errorField="selectedDepartmentId" mainCol="col-md-6" />
                    @endif
                    <!-- Payment Mode -->
                    <div class="col-md-6">
                        <div class="row align-items-center">
                            <div class="col-6 col-form-label text-end pe-2">
                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                    Payment Mode
                                </label>
                            </div>
                            <div class="col-6">
                                <select wire:model.live="paymentMode" class="form-select shadow-sm report" style="font-size: 12px; height: 30px;">
                                    <option value="">All</option>
                                    <option value="bank">Bank</option>
                                    <option value="cash">Cash</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                                @error('paymentMode')
                                <span class="text-danger" style="font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4" x-data="{ showFilter: false }" @click.away="showFilter = false" @keydown.escape.window="showFilter = false">
                <!-- Select-style Toggle -->
                <div class="position-relative">
                    <div class=" d-flex align-items-center justify-content-between  form-control shadow-sm px-0" @click="showFilter = !showFilter" style="    cursor: pointer;
                        font-size: 12px; height: 30px; border: 0; box-shadow: none !important;">
                        <span class="input-group-text bg-white border-start-1" style="height: 30px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M15 19.88c.04.3-.06.62-.29.83a.996.996 0 0 1-1.41 0L9.29 16.7a.99.99 0 0 1-.29-.83v-5.12L4.21 4.62a1 1 0 0 1 .17-1.4c.19-.14.4-.22.62-.22h14c.22 0 .43.08.62.22a1 1 0 0 1 .17 1.4L15 10.75zM7.04 5L11 10.06v5.52l2 2v-7.53L16.96 5z" / class="text-muted">
                            </svg>
                    </div>
                    <!-- Dropdown Content -->
                    <div x-show="showFilter" x-transition class="mt-2 position-absolute w-100 shadow bg-white border rounded" style="z-index: 1050;">
                        <div class="p-3">
                            <!-- Header -->
                            <!-- Master Toggle Switch -->
                            {{-- <div class="d-flex justify-content-end mb-2">
                                <div class="form-check form-switch mb-3">
                                    <input 
                                        type="checkbox" 
                                        class="form-check-input" 
                                        role="switch"
                                        id="toggleAll" 
                                        wire:click="toggleAllFilters"
                                        @click.stop 
                                        {{ $toggleAllActive ? 'checked' : '' }}
                            >
                        </div>
                    </div> --}}
                    <!-- Individual Filter Toggles -->
                    <div class="row gx-3 p-2">
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
                        <!-- Toggle switches -->
                        <div class="row gx-3 p-2">
                            @foreach ($filterFields as $key => $label)
                            @if (array_key_exists($key, $filter))
                            <div class="col-xl-12 col-lg-12 col-md-12">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" role="switch" id="{{ $key }}Checkbox" wire:click="toggleFilters('{{ $key }}', {{ $filter[$key] ? 'false' : 'true' }})" @click.stop {{ $filter[$key] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="{{ $key }}Checkbox">
                                        {{ $label }}
                                    </label>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ====================== ACCORDION WITH CHECKBOXES ====================== -->
    <div class="col-md-8 mx-5 px-5 my-3" x-data="{ preventCollapse: @entangle('preventCollapse') }">
        <div class="accordion accordion-flush" id="accordionFlushExample">
            <div class="accordion-item">
                <h2 class="accordion-header" id="flush-headingOne">
                    <button class="accordion-button collapsed fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne" style="font-size: 12px; padding: 8px 12px;">
                        <i class="bi bi-plus-circle me-1"></i>
                        Additional Details
                    </button>
                </h2>
                <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample" wire:ignore.self>
                    <div class="accordion-body p-2">
                        <div class="row gx-3 p-2">
                            @php
                            $filterFields = [
                            'employeeContacts' => 'Employee Contacts',
                            'employeeEarnings' => 'Employee Earnings',
                            'employeeDeductions' => 'Employee Deductions',
                            'daysInfo' => 'Days Info',
                            'earningComponents' => 'Earning Components',
                            'deductionComponents' => 'Deduction Components',
                            ];
                            @endphp
                            @foreach ($filterFields as $key => $label)
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                                <div class="form-check d-flex align-items-center">
                                    <input type="checkbox" class="form-check-input me-2" id="{{ $key }}Checkbox" wire:model="filters.{{ $key }}" wire:change="toggleFilter('{{ $key }}', {{ $filters[$key] ? 'false' : 'true' }})" @click.stop style="cursor: pointer;">
                                    <label class="form-check-label" for="{{ $key }}Checkbox" style="font-size: 12px; cursor: pointer;">
                                        {{ $label }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ====================== EXPORT BUTTON ====================== -->
    <!-- Action Buttons -->
    <div class="col-8 d-flex justify-content-end px-0">
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-sm px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2" style="font-size: 12px; border-radius: 8px; font-weight: 500;" x-data x-on:click="
                    {{-- Swal.fire({
                        position: 'top-end',
                        icon: 'info',
                        title: 'Generating your report — this may take a little while. Please stay on this page!',
                        toast: true,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'swal2-toast-custom'
                        }
                    }); --}}
                    $wire.generateReport()
                " wire:loading.attr="disabled" wire:loading.class="opacity-50" wire:target="generateReport">
                <!-- Spinner: hidden by default, shown while loading -->
                <span class="spinner-border spinner-border-sm text-light d-none" role="status" aria-hidden="true" wire:loading.class.remove="d-none" wire:target="generateReport"></span>
                <!-- Default label -->
                <span wire:loading.class="d-none" wire:target="generateReport">
                    <i class="bi bi-download"></i> Export
                </span>
                <!-- Loading label -->
                <span class="d-none" wire:loading.class.remove="d-none" wire:target="generateReport">
                    downloading...
                </span>
            </button>
        </div>
    </div>
</div>
</div>
<script>
    document.addEventListener('livewire:initialized', function() {
        // Show alerts
        Livewire.on('show-alert', (data) => {
            Swal.fire({
                position: 'top-end'
                , icon: data[0].type || 'success'
                , title: data[0].message || ''
                , toast: true
                , showConfirmButton: false
                , timer: 3000
                , timerProgressBar: true
                , customClass: {
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
        // Initialize Alpine.js
        Alpine.data('accordion', () => ({
            preventCollapse: @entangle('preventCollapse')
        }));
        // Re-attach Bootstrap collapse behavior after Livewire updates
        Livewire.hook('message.processed', () => {
            const button = document.querySelector('#flush-headingOne button');
            const collapseEl = document.querySelector('#flush-collapseOne');
            if (!button || !collapseEl) return;
            // Prevent collapse when any checkbox is checked
            button.addEventListener('click', function(e) {
                const prevent = @js($this-> preventCollapse);
                if (collapseEl.classList.contains('show') && prevent) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
            // Block Bootstrap collapse event
            collapseEl.addEventListener('hide.bs.collapse', function(e) {
                const prevent = @js($this->preventCollapse);
                if (prevent) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@endpush
