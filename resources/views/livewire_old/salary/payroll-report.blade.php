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
            <div class="alert alert-danger alert-dismissible fade show position-relative d-flex justify-content-between align-items-center"
                role="alert" style="padding-right: 3rem;">
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
                                <select wire:model.live="employeeStatusId" class="form-select shadow-sm report"
                                    style="font-size: 12px; height: 30px;">
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
                    <x-search-input label="Employee/Code" wireModel="search" :results="$employees"
                        selectMethod="selectEmployee" idField="emp_id" nameField="emp_full_name"
                        errorField="selectedEmployeeId" mainCol="col-md-6" />
                    <!-- Financial Year -->
                    <x-search-input label="Financial Year" wireModel="searchFY" :results="$financialYears"
                        selectMethod="selectFY" idField="fy_id" nameField="fy_year" errorField="selectedFYId"
                        mainCol="col-md-6" />
                    <!-- Payroll Period -->
                    <x-search-input label="Payroll Period" wireModel="searchPayroll" :results="$payrollPeriods"
                        selectMethod="selectPayrollPeriod" idField="pp_id" nameField="pp_name"
                        errorField="selectedPayrollPeriodId" mainCol="col-md-6" />
                    <!-- Payment Mode -->
                    <div class="col-md-6">
                        <div class="row align-items-center">
                            <div class="col-6 col-form-label text-end pe-2">
                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                    Payment Mode
                                </label>
                            </div>
                            <div class="col-6">
                                <select wire:model.live="paymentMode" class="form-select shadow-sm report"
                                    style="font-size: 12px; height: 30px;">
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
            <!-- ====================== ACCORDION WITH CHECKBOXES ====================== -->
            <div class="col-md-8 mx-5 px-5 my-3" x-data="{ preventCollapse: @entangle('preventCollapse') }">
                <div class="accordion accordion-flush" id="accordionFlushExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingOne">
                            <button class="accordion-button collapsed fw-medium" type="button"
                                data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false"
                                aria-controls="flush-collapseOne" style="font-size: 12px; padding: 8px 12px;">
                                <i class="bi bi-plus-circle me-1"></i>
                                Additional Details
                            </button>
                        </h2>
                        <div id="flush-collapseOne" class="accordion-collapse collapse"
                            aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample" wire:ignore.self>
                            <div class="accordion-body p-2">
                                <div class="row gx-3 p-2">
                                    @php
                                        $filterFields = [
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
                                                <input type="checkbox" class="form-check-input me-2"
                                                    id="{{ $key }}Checkbox"
                                                    wire:model="filters.{{ $key }}"
                                                    wire:change="toggleFilter('{{ $key }}', {{ $filters[$key] ? 'false' : 'true' }})"
                                                    @click.stop style="cursor: pointer;">
                                                <label class="form-check-label" for="{{ $key }}Checkbox"
                                                    style="font-size: 12px; cursor: pointer;">
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
                    <button type="button"
                        class="btn btn-primary btn-sm px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2"
                        style="font-size: 12px; border-radius: 8px; font-weight: 500;" x-data
                        x-on:click="
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
                "
                        wire:loading.attr="disabled" wire:loading.class="opacity-50" wire:target="generateReport">
                        <!-- Spinner: hidden by default, shown while loading -->
                        <span class="spinner-border spinner-border-sm text-light d-none" role="status"
                            aria-hidden="true" wire:loading.class.remove="d-none"
                            wire:target="generateReport"></span>
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
                    const prevent = @js($this->preventCollapse);
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
