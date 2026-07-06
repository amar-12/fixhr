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
                    <strong></strong> {{ session('error') }}
                </div>
                <button type="button" class="custom-close-button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <!-- Filter visibility dropdown -->
        <div class="row">
            <!-- Always visible fields -->
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
                                <select wire:model.live="employeeStatusFilter" class="form-select shadow-sm report "
                                    style="font-size: 12px; height: 30px;">
                                    <option value="">All</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                @error('employeeStatusFilter')
                                    <span class="text-danger" style="font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <x-search-input label="Employee/Code" wireModel="search" :results="$employees"
                        selectMethod="selectEmployee" idField="emp_id" nameField="emp_full_name" mainCol="col-md-6"
                        errorField="selectedEmployeeId" />
                    <div class="col-md-6">
                        <div class="row align-items-center mb-1">
                            <div class="col-6 col-form-label text-end pe-2">
                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                    From Date
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="date" wire:model="selectedFromDate" class="form-control shadow-sm"
                                    style="font-size: 12px; height: 30px;">
                                @error('selectedFromDate')
                                    <span class="text-danger" style="font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row align-items-center mb-1">
                            <div class="col-6 col-form-label text-end pe-2">
                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                    To Date
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="date" wire:model="selectedToDate" class="form-control shadow-sm"
                                    style="font-size: 12px; height: 30px;">
                                @error('selectedToDate')
                                    <span class="text-danger" style="font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <x-search-input label="Leave Type" wireModel="searchLeaveType" :results="$leaveTypes"
                        selectMethod="selectLeaveType" idField="m_id" nameField="m_name" mainCol="col-md-6"
                        errorField="selectedLeaveTypeId" />
                    <x-search-input label="Leave Segment" wireModel="searchLeaveSegment" :results="$leaveSegments"
                        selectMethod="selectLeaveSegment" idField="m_id" nameField="m_name" mainCol="col-md-6"
                        errorField="selectedLeaveSegmentId" />
                    <x-search-input label="Leave Category" wireModel="searchLeaveCategory" :results="$leaveCategories"
                        selectMethod="selectLeaveCategory" idField="m_id" nameField="m_name" mainCol="col-md-6"
                        errorField="selectedLeaveCategoryId" />
                    <x-search-input label="Approval Status" wireModel="searchApprovalStatus" :results="$approvalStatus"
                        selectMethod="selectApprovalStatus" idField="m_id" nameField="m_name" mainCol="col-md-6"
                        errorField="selectedApprovalStatusId" />
                    <!-- Conditionally visible fields -->
                    @if ($filters['department'])
                        <x-search-input label="Department" wireModel="searchDepartment" :results="$departments"
                            selectMethod="selectDepartment" idField="d_id" nameField="d_name" :selected="$selectedDepartmentId"
                            mainCol="col-md-6" errorField="selectedDepartmentId" />
                    @endif
                    {{-- @if ($filters['shift'])
                        <x-search-input label="Shift" wireModel="searchShift" :results="$shifts"
                            selectMethod="selectShift" idField="pst_id" nameField="pst_name"
                            mainCol="col-md-6" errorField="selectedShiftId" />
                    @endif --}}
                    @if ($filters['designation'])
                        <x-search-input label="Designation" wireModel="searchDesignation" :results="$designations"
                            selectMethod="selectDesignation" idField="dg_id" nameField="dg_name" mainCol="col-md-6"
                            errorField="selectedDesignationId" />
                    @endif
                    {{-- @if ($filters['workMode'])
                        <x-search-input label="Work Mode" wireModel="searchWorkMode" :results="$workMode"
                            selectMethod="selectLocation" idField="m_id" nameField="m_name"
                            mainCol="col-md-6" errorField="selectedWorkModeId" />
                    @endif --}}
                    {{-- @if ($filters['checkingMethod'])
                        <x-search-input label="Check In Method" wireModel="searchCheckingMethod" :results="$checkingMethods"
                            selectMethod="selectCheckingMethod" idField="m_id" nameField="m_name"
                            mainCol="col-md-6" errorField="selectedCheckingMethodId" />
                    @endif --}}
                    @if ($filters['dealership'])
                        <x-search-input label="Dealership" wireModel="searchDealer" :results="$dealers"
                            selectMethod="selectDealer" idField="dlr_id" nameField="dlr_name" mainCol="col-md-6"
                            errorField="selectedDealerId" />
                    @endif
                    {{-- @if ($filters['attendanceStatus'])
                        <x-search-input label="Attendance Status" wireModel="searchAttendanceStatus" :results="$attendanceStatuses"
                            selectMethod="selectAttendanceStatus" idField="m_id" nameField="m_name"
                            mainCol="col-md-6" errorField="selectedAttendanceStatusId" />
                    @endif
                    @if ($filters['branch'])
                        <x-search-input label="Branch" wireModel="searchBranch" :results="$branches"
                            selectMethod="selectBranch" idField="br_id" nameField="br_name"
                            mainCol="col-md-6" errorField="selectedBranchId" />
                    @endif --}}
                    {{-- @if ($filters['jobStatus'])
                        <x-search-input label="Job Status" wireModel="searchJobStatus" :results="$jobStatuses"
                            selectMethod="selectJobStatus" idField="m_id" nameField="m_name"
                            mainCol="col-md-6" errorField="selectedJobStatusId" />
                    @endif --}}
                    {{-- @if ($filters['grade'])
                        <x-search-input label="Grade" wireModel="searchGrade" :results="$grades"
                            selectMethod="selectGrade" idField="g_id" nameField="g_name" :selected="$selectedGradeId"
                            mainCol="col-md-6" errorField="selectedGradeId" />
                    @endif --}}
                </div>
            </div>
            <div class="col-md-3 mb-4" x-data="{ showFilter: false }" @click.away="showFilter = false"
                @keydown.escape.window="showFilter = false">
                <!-- Select-style Toggle -->
                <div class="position-relative">
                    <div class=" d-flex align-items-center justify-content-between  form-control shadow-sm px-0"
                        @click="showFilter = !showFilter"
                        style="    cursor: pointer;
                        font-size: 12px; height: 30px; border: 0; box-shadow: none !important;">
                        
                        <span class="input-group-text bg-white border-start-1" style="height: 30px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="M15 19.88c.04.3-.06.62-.29.83a.996.996 0 0 1-1.41 0L9.29 16.7a.99.99 0 0 1-.29-.83v-5.12L4.21 4.62a1 1 0 0 1 .17-1.4c.19-.14.4-.22.62-.22h14c.22 0 .43.08.62.22a1 1 0 0 1 .17 1.4L15 10.75zM7.04 5L11 10.06v5.52l2 2v-7.53L16.96 5z"
                                    / class="text-muted">
                            </svg>
                    </div>
                    <!-- Dropdown Content -->
                    <div x-show="showFilter" x-transition
                        class="mt-2 position-absolute w-100 shadow bg-white border rounded" style="z-index: 1050;">
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
                                        // 'shift' => 'Shift',
                                        'designation' => 'Designation',
                                        'workMode' => 'Work Mode',
                                        // 'checkingMethod' => 'Checking Method',
                                        'dealership' => 'Dealership',
                                        // 'attendanceStatus' => 'Attendance Status',
                                        // 'branch' => 'Branch',
                                        // 'jobStatus' => 'Job Status',
                                        // 'grade' => 'Grade',
                                    ];
                                @endphp
                                <!-- Toggle switches -->
                                <div class="row gx-3 p-2">
                                    @foreach ($filterFields as $key => $label)
                                        @if (array_key_exists($key, $filters))
                                            <div class="col-xl-12 col-lg-12 col-md-12">
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" class="form-check-input" role="switch"
                                                        id="{{ $key }}Checkbox"
                                                        wire:click="toggleFilter('{{ $key }}', {{ $filters[$key] ? 'false' : 'true' }})"
                                                        @click.stop {{ $filters[$key] ? 'checked' : '' }}>
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
            <div class="row mt-2">
                <div class="col-8 d-flex justify-content-end">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button"
                            class="btn btn-primary btn-sm px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2"
                            style="font-size: 12px; border-radius: 8px; font-weight: 500;" wire:click="generateReport"
                            wire:loading.attr="disabled" wire:loading.class="opacity-50"
                            wire:target="generateReport">
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
