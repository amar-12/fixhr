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
                <span class="ef-panel-title">Payroll Report Filters</span>
            </div>
            {{-- Fields grid --}}
            <div class="row gx-3 gy-3 ef-fields-grid">
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
