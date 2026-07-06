<div class='row d-flex justify-content-center align-items-center' style="padding-top: 20px; padding-bottom: 20px;">
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
                <div>
                    <strong>Error!</strong> {{ session('error') }}
                </div>
                <button type="button" class="custom-close-button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <!-- Filter visibility dropdown -->
        <div class="row">
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
                                @error('employeeStatusFilter')
                                    <span class="text-danger" style="font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <x-search-input label="Employee/Code" wireModel="searchEmployee" :results="$employees"
                        selectMethod="selectEmployee" idField="emp_id" nameField="emp_full_name"
                        errorField="selectedEmployeeId" mainCol="col-md-6" />
                    <div class="col-md-6">
                        <div class="row align-items-center mb-1">
                            <div class="col-6 col-form-label text-end pe-2">
                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                    Financial Year
                                </label>
                            </div>
                            <div class="col-6">
                                <select class="form-select shadow-sm report" wire:model="selectedYear"
                                    wire:change="selectYear($event.target.value)"
                                    style="font-size: 12px; height: 30px;">
                                    <option value="">Select Year</option>
                                    @foreach ($financialYears as $year)
                                        <option value="{{ $year->fy_id }}">
                                            {{ $year->fy_year }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('selectedYear')
                                    <span class="text-danger" style="font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row align-items-center mb-1">
                            <div class="col-6 col-form-label text-end pe-2">
                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                    Month
                                </label>
                            </div>
                            <div class="col-6">
                                <div class="">
                                    <select class="form-select shadow-sm report" wire:model="selectedMonth"
                                        style="font-size: 12px; height: 30px;">
                                        <option value="">Select Month</option>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}">
                                                {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                @error('selectedMonth')
                                    <span class="text-danger" style="font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row align-items-center mb-1">
                            <div class="col-6 col-form-label text-end pe-2">
                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                    Approval Status
                                </label>
                            </div>
                            <div class="col-6">
                                <select wire:model.live="approvalStatusId"
                                    wire:change="selectApprovalStatus($event.target.value)"
                                    class="form-select shadow-sm report" style="font-size: 12px; height: 30px;">
                                    <option value="">All</option>
                                    {{-- @dd($approvalStatus) --}}
                                    @foreach ($approvalStatus as $status)
                                        <option value="{{ $status->m_id }}">{{ $status->m_name }}</option>
                                    @endforeach
                                </select>
                                @error('approvalStatusId')
                                    <span class="text-danger" style="font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-8 d-flex justify-content-end">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button"
                            class="btn btn-primary btn-sm px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2"
                            style="font-size: 12px; border-radius: 8px; font-weight: 500;" wire:click="generateReport"
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
    </div>
</div>
