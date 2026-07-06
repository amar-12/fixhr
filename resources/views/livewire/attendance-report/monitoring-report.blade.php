<div class="row d-flex justify-content-center align-items-start ef-wrapper"
    style="padding-top: 20px; padding-bottom: 20px;">
    <div class="col-md-10">
        {{-- ═══ Progress overlay ═══ --}}
        @if ($generatingReport || $hasActiveJob)
            <div class="ef-overlay">
                <div class="ef-progress-card">
                    {{-- Close button --}}
                    <button type="button" class="ef-card-close" wire:click="cancelJobAndClosePopup"
                        title="Cancel (ESC)">×</button>
                    {{-- Header --}}
                    <div class="p-4 border-bottom"
                        style="border-color: var(--ef-border) !important; padding-right: 50px !important;">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="ef-spinner"></div>
                            <div>
                                <div style="font-size:15px; font-weight:700; color:var(--ef-text);">Creating Your Report
                                </div>
                                <div style="font-size:12px; color:var(--ef-muted); margin-top:2px;">
                                    Please wait while we prepare your document
                                </div>
                            </div>
                        </div>
                        {{-- Progress bar --}}
                        <div class="d-flex justify-content-between mb-1">
                            <span style="font-size:11.5px; color:var(--ef-muted);">Progress</span>
                            <span
                                style="font-size:11.5px; font-weight:600; color:var(--ef-text);">{{ $progressPercentage }}%</span>
                        </div>
                        <div class="ef-progress-bar-track">
                            <div class="ef-progress-bar-fill" style="width:{{ $progressPercentage }}%;"></div>
                        </div>
                    </div>
                    {{-- Steps --}}
                    <div class="p-4">
                        @php
                            $steps = [
                                [
                                    'threshold' => 10,
                                    'prev' => 0,
                                    'title' => 'Preparing your request',
                                    'desc' => 'Setting up the report parameters',
                                ],
                                [
                                    'threshold' => 40,
                                    'prev' => 10,
                                    'title' => 'Gathering data',
                                    'desc' => 'Collecting information from database',
                                ],
                                [
                                    'threshold' => 75,
                                    'prev' => 40,
                                    'title' => 'Processing information',
                                    'desc' => 'Organizing and formatting data',
                                ],
                                [
                                    'threshold' => 95,
                                    'prev' => 75,
                                    'title' => 'Finalizing document',
                                    'desc' => 'Preparing file for download',
                                ],
                            ];
                        @endphp
                        @foreach ($steps as $i => $step)
                            <div class="ef-step">
                                <div
                                    class="ef-step-icon
                                    {{ $progressPercentage >= $step['threshold'] ? 'completed' : ($progressPercentage >= $step['prev'] ? 'active' : '') }}">
                                    @if ($progressPercentage >= $step['threshold'])
                                        ✓
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </div>
                                <div>
                                    <div class="ef-step-title">{{ $step['title'] }}</div>
                                    <div class="ef-step-desc">{{ $step['desc'] }}</div>
                                </div>
                            </div>
                        @endforeach
                        {{-- Cancel --}}
                        <button type="button" class="ef-cancel-btn" wire:click="cancelJobAndClosePopup">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                            Cancel Report Generation
                        </button>
                        <div class="ef-cancel-note">Press ESC key to cancel</div>
                    </div>
                </div>
            </div>
        @endif
        {{-- Resume banner --}}
        <div id="resumeBanner" class="ef-resume-banner" onclick="showReportPopup()">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size:18px;">📊</span>
                    <div>
                        <div style="font-weight:600; font-size:13px;">Report in Progress</div>
                        <small style="opacity:.85; font-size:11px;">Click to view progress</small>
                    </div>
                </div>
                <button class="ef-resume-banner-close" onclick="hideBannerAndCancelJob(event)">×</button>
            </div>
        </div>
        {{-- Alert --}}
        @if (session()->has('error'))
            <div class="alert alert-danger ef-alert alert-dismissible fade show d-flex justify-content-between align-items-center mb-3"
                role="alert">
                <div><strong>Error!</strong> {{ session('error') }}</div>
                <button type="button" class="custom-close-button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        {{-- ═══ Filter panel (hidden while report generating) ═══ --}}
        @if (!$generatingReport)
            <div class="ef-panel">
                {{-- Header --}}
                <div class="ef-panel-header">
                    <span class="ef-panel-title">{{$reportName}} Report Filters</span>
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
                            @php
                                $filterFields = [
                                    'department' => 'Department',
                                    'shift' => 'Shift',
                                    'designation' => 'Designation',
                                    'workMode' => 'Work Mode',
                                    'checkingMethod' => 'Checking Method',
                                    'dealership' => 'Dealership',
                                    'branch' => 'Branch',
                                    'jobStatus' => 'Job Status',
                                    'grade' => 'Grade',
                                ];
                            @endphp
                            @foreach ($filterFields as $key => $label)
                                @if (array_key_exists($key, $filters))
                                    <div class="ef-switch-row">
                                        <label for="{{ $key }}MrSwCb">{{ $label }}</label>
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
                    {{-- Conditional filters --}}
                    @if ($filters['department'])
                        <x-search-input label="Department" wireModel="searchDepartment" :results="$departments"
                            selectMethod="selectDepartment" idField="d_id" nameField="d_name" :selected="$selectedDepartmentId"
                            errorField="selectedDepartmentId" mainCol="col-md-6" />
                    @endif
                    @if ($filters['shift'])
                        <x-search-input label="Shift" wireModel="searchShift" :results="$shifts"
                            selectMethod="selectShift" idField="pst_id" nameField="pst_name"
                            errorField="selectedShiftId" mainCol="col-md-6" />
                    @endif
                    @if ($filters['designation'])
                        <x-search-input label="Designation" wireModel="searchDesignation" :results="$designations"
                            selectMethod="selectDesignation" idField="dg_id" nameField="dg_name"
                            errorField="selectedDesignationId" mainCol="col-md-6" />
                    @endif
                    @if ($filters['workMode'])
                        <x-search-input label="Work Mode" wireModel="searchWorkMode" :results="$workMode"
                            selectMethod="selectLocation" idField="m_id" nameField="m_name"
                            errorField="selectedWorkModeId" mainCol="col-md-6" />
                    @endif
                    @if ($filters['checkingMethod'])
                        <x-search-input label="Check In Method" wireModel="searchCheckingMethod" :results="$checkingMethods"
                            selectMethod="selectCheckingMethod" idField="m_id" nameField="m_name"
                            errorField="selectedCheckingMethodId" mainCol="col-md-6" />
                    @endif
                    @if ($filters['dealership'])
                        <x-search-input label="Dealership" wireModel="searchDealer" :results="$dealers"
                            selectMethod="selectDealer" idField="dlr_id" nameField="dlr_name"
                            errorField="selectedDealerId" mainCol="col-md-6" />
                    @endif
                    @if ($filters['branch'])
                        <x-search-input label="Branch" wireModel="searchBranch" :results="$branches"
                            selectMethod="selectBranch" idField="br_id" nameField="br_name"
                            errorField="selectedBranchId" mainCol="col-md-6" />
                    @endif
                    @if ($filters['jobStatus'])
                        <x-search-input label="Job Status" wireModel="searchJobStatus" :results="$jobStatuses"
                            selectMethod="selectJobStatus" idField="m_id" nameField="m_name"
                            errorField="selectedJobStatusId" mainCol="col-md-6" />
                    @endif
                    @if ($filters['grade'])
                        <x-search-input label="Grade" wireModel="searchGrade" :results="$grades"
                            selectMethod="selectGrade" idField="g_id" nameField="g_name" :selected="$selectedGradeId"
                            errorField="selectedGradeId" mainCol="col-md-6" />
                    @endif
                    {{-- Absent with punches checkbox --}}
                    @if ($slug == 'absent')
                        <div class="col-12">
                            <div class="d-flex align-items-center gap-2" style="padding: 4px 0;">
                                <input type="checkbox" wire:model="showAbsentWithPunches" class="form-check-input"
                                    id="absentWithPunches"
                                    style="width:15px; height:15px; cursor:pointer; accent-color:var(--ef-accent);">
                                <label for="absentWithPunches"
                                    style="font-size:12px; color:var(--ef-text); cursor:pointer; margin:0;">
                                    Abnormal Absentees (with check-in/check-out times)
                                </label>
                            </div>
                        </div>
                    @endif
                    {{-- Divider --}}
                    <div class="col-12">
                        <div class="ef-divider"></div>
                    </div>
                    {{-- Footer --}}
                    <div class="col-12 d-flex align-items-center justify-content-end">
                    
                        <button type="button" class="ef-btn-export" wire:click="generateReport"
                            @if ($generatingReport || $hasActiveJob) disabled @endif>
                            @if ($generatingReport || $hasActiveJob)
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                Creating…
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="7 10 12 15 17 10" />
                                    <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>
                                Export
                            @endif
                        </button>
                    </div>
                </div>{{-- /.ef-fields-grid --}}
            </div>{{-- /.ef-panel --}}
        @endif
    </div>
    {{-- Poll for progress --}}
    @if ($generatingReport || $hasActiveJob)
        <div wire:poll.2000ms="checkReportProgress" class="d-none"></div>
    @endif
    <script>
        function showReportPopup() {
            @this.call('showPopup');
            document.getElementById('resumeBanner').classList.remove('visible');
        }
        function hideBannerAndCancelJob(event) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            document.getElementById('resumeBanner').classList.remove('visible');
            @this.call('cancelJobAndClosePopup');
        }
        function checkForStoredJob() {
            const storedJobId = localStorage.getItem('active_report_job_id');
            const storedTs = localStorage.getItem('active_report_job_timestamp');
            const storedUrl = localStorage.getItem('active_report_job_url');
            if (storedJobId && storedTs && storedUrl) {
                const currentUrl = window.location.href.split('?')[0].split('#')[0];
                const savedUrl = storedUrl.split('?')[0].split('#')[0];
                const twoHours = 2 * 60 * 60 * 1000;
                if ((Date.now() - parseInt(storedTs)) < twoHours && currentUrl === savedUrl) {
                    @this.call('restoreFromLocalStorage', storedJobId);
                    setTimeout(() => document.getElementById('resumeBanner').classList.remove('visible'), 100);
                } else {
                    clearLocalStorageJob();
                }
            }
        }
        function clearLocalStorageJob() {
            localStorage.removeItem('active_report_job_id');
            localStorage.removeItem('active_report_job_timestamp');
            localStorage.removeItem('active_report_job_url');
            document.getElementById('resumeBanner').classList.remove('visible');
        }
        document.addEventListener('livewire:initialized', () => {
            window.addEventListener('beforeunload', () => {
                if (@this.currentJobId) {
                    localStorage.setItem('active_report_job_id', @this.currentJobId);
                    localStorage.setItem('active_report_job_timestamp', Date.now());
                    localStorage.setItem('active_report_job_url', window.location.href);
                }
            });
            Livewire.on('store-job-in-localstorage', (e) => {
                localStorage.setItem('active_report_job_id', e[0].jobId);
                localStorage.setItem('active_report_job_timestamp', Date.now());
                localStorage.setItem('active_report_job_url', window.location.href);
            });
            Livewire.on('clear-localstorage-job', () => clearLocalStorageJob());
            Livewire.on('check-localstorage-job', () => checkForStoredJob());
            Livewire.on('show-resume-banner', () => document.getElementById('resumeBanner').classList.add(
                'visible'));
            Livewire.on('hide-resume-banner', () => document.getElementById('resumeBanner').classList.remove(
                'visible'));
            Livewire.on('download-report', (e) => {
                const data = e[0];
                if (!data?.data) return;
                try {
                    const binary = atob(data.data);
                    const bytes = new Uint8Array(binary.length);
                    for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
                    const blob = new Blob([bytes], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });
                    const url = window.URL.createObjectURL(blob);
                    const a = Object.assign(document.createElement('a'), {
                        href: url,
                        download: data.filename,
                        style: 'display:none'
                    });
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    setTimeout(() => window.URL.revokeObjectURL(url), 100);
                } catch (err) {
                    console.error('Download error:', err);
                }
            });
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
            document.addEventListener('keydown', (e) => {
                if ((e.key === 'Escape' || e.key === 'Esc') && (@this.generatingReport || @this
                        .hasActiveJob)) {
                    @this.call('cancelJobAndClosePopup');
                }
            });
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) setTimeout(checkForStoredJob, 300);
            });
            window.addEventListener('popstate', () => setTimeout(checkForStoredJob, 500));
            setTimeout(checkForStoredJob, 500);
        });
    </script>
</div>
