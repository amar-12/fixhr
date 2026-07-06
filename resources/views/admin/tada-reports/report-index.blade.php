@extends('admin.layout.master')
@section('title')
    TA & DA Report
@endsection
@section('css')
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    <style>
        h5 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #007bff;
        }
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

        /* Full-screen loader overlay shown during report download */
        #reportLoader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(255, 255, 255, 0.75);
            display: none; /* toggled via JS */
            align-items: center;
            justify-content: center;
            z-index: 2000;
            backdrop-filter: blur(1px);
        }
        .loader-spinner {
            width: 56px;
            height: 56px;
            border: 5px solid #cfd0d1;
            border-top-color: #0d6efd;
            border-radius: 50%;
            animation: spin 0.9s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .loader-text { margin-top: 12px; color: #0d6efd; font-weight: 600; font-size: 13px; text-align: center; }
    </style>
@endsection
@section('content')
    <div>
        <!-- Download loader overlay -->
        <div id="reportLoader">
            <div>
                <div class="loader-spinner"></div>
                <div class="loader-text">Preparing report... Please wait</div>
            </div>
        </div>
        <div class=" p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('/admin/report/attendance-report') }}">Travel Management</a></li>
                <li class="active"><span><b id="breadcrumb-report-name">TADA Report</b></span></li>
            </ol>
        </div>
        
        <div class="row pt-5">
            <div class="container-fluid bg-white" style="padding-bottom:500px;">
                <div class='row d-flex justify-content-center align-items-center' style="padding-top: 20px; padding-bottom: 20px;">
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
                                        <div class="row align-items-center mb-1">
                                            <div class="col-6 col-form-label text-end pe-2">
                                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                                    From Date
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <input type="date" class="form-control shadow-sm" name="from_date" style="font-size: 12px; height: 30px;">
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
                                                <input type="date" class="form-control shadow-sm" name="to_date" style="font-size: 12px; height: 30px;">
                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-md-6">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-6 col-form-label text-end pe-2">
                                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                                    Travel Type
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <select class="form-select shadow-sm select2" name="travel_type" data-placeholder="Select" style="font-size: 12px; height: 30px;">
                                                    <option value="">Select</option>
                                                    @foreach ($travelType as $item)
                                                        <option value="{{ $item->pttt_id }}">
                                                            {{ $item->fh_travel_type->m_name ?? '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-6 col-form-label text-end pe-2">
                                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                                    Employee Status
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <select name="employee_status" class="form-select shadow-sm select2" style="font-size: 12px; height: 30px;">
                                                    <option value="">Select</option>
                                                    @foreach ($employeeStatuses as $status)
                                                        <option value="{{ $status->m_id }}">{{ $status->m_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6" id="reportTypeGroup" style="display:none;">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-6 col-form-label text-end pe-2">
                                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                                    Report Type <span class="text-danger">*</span>
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <select name="report_type" class="form-select shadow-sm select2" style="font-size: 12px; height: 30px;">
                                                    <option value="">Select</option>
                                                    @foreach ($reportFilter as $reportF)
                                                        <option value="{{ $reportF->m_id }}">{{ $reportF->m_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="report_type" id="report_type_hidden" value="">

                                    <div class="col-md-6">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-6 col-form-label text-end pe-2">
                                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                                    Employee
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <input type="text" id="employee_search" name="employee" class="form-control shadow-sm" placeholder="Type name or code to search" style="font-size: 12px; height: 30px;" autocomplete="off">
                                                <div id="employee_results" class="position-absolute bg-white border rounded shadow-sm" style="width: 100%; max-height: 200px; overflow-y: auto; z-index: 1000; display: none;"></div>
                                            </div>
                                        </div>
                                    </div>

   
                                    <div class="col-md-6">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-6 col-form-label text-end pe-2">
                                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                                    Branch
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <select name="branch" class="form-select shadow-sm select2" style="font-size: 12px; height: 30px;">
                                                    <option value="">Select</option>
                                                    @foreach ($branches as $branch)
                                                        <option value="{{ $branch->br_id }}">{{ $branch->br_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

        
                                    <div class="col-md-6">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-6 col-form-label text-end pe-2">
                                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                                    Designation
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <select name="designation" class="form-select shadow-sm select2" style="font-size: 12px; height: 30px;">
                                                    <option value="">Select</option>
                                                    @foreach ($designations as $designation)
                                                        <option value="{{ $designation->dg_id }}">{{ $designation->dg_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    @if($getSlugQuery != 'travel-attendance-report')
                                    <div class="col-md-6">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-6 col-form-label text-end pe-2">
                                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                                    Travel ID
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <input type="text" class="form-control shadow-sm" name="travel_id" placeholder="TRPAA0219" style="font-size: 12px; height: 30px;">
                                            </div>
                                        </div>
                                    </div>

                                        
                                    <div class="col-md-6">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-6 col-form-label text-end pe-2">
                                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                                    Claim ID
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <input type="text" class="form-control shadow-sm" name="claim_id" placeholder="TCAA0009" style="font-size: 12px; height: 30px;">
                                            </div>
                                        </div>
                                    </div>
                                    
                                 
                                    <div class="col-md-6">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-6 col-form-label text-end pe-2">
                                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                                    Purpose Type
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <select name="purpose_type" class="form-select shadow-sm select2" style="font-size: 12px; height: 30px;">
                                                    <option value="">Select</option>
                                                    @foreach ($purposeTypes as $purposeType)
                                                        <option value="{{ $purposeType->tp_id }}">{{ $purposeType->tp_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @if($getSlugQuery == 'travel-attendance-report')
                                    <div class="col-md-6">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-6 col-form-label text-end pe-2">
                                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                                    Less or equal to 
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <select name="status" class="form-select shadow-sm select2" style="font-size: 12px; height: 30px;">
                                                    <option value="">-----</option>
                                                    <option value="3">3</option>
                                                    <option value="6">6</option>
                                                    <option value="9">9</option>
                                                    <option value="12">12</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="col-md-6">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-6 col-form-label text-end pe-2">
                                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                                    Status
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <select name="status" class="form-select shadow-sm select2" style="font-size: 12px; height: 30px;">
                                                    <option value="">All Status</option>
                                                    @foreach ($statusFilter as $statusF)
                                                        <option value="{{ $statusF->m_id }}">{{ $statusF->m_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- <div class="col-md-6">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-6 col-form-label text-end pe-2">
                                                <label class="form-label fw-semibold text-dark mb-0" style="font-size: 12px;">
                                                    Report Output
                                                </label>
                                            </div>
                                            <div class="col-6">
                                                <select name="report_output" class="form-select shadow-sm select2" style="font-size: 12px; height: 30px;">
                                                    <option value="excel">Excel</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div> --}}
                              
             
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-4" x-data="{ showFilter: false }" @click.away="showFilter = false"
                                @keydown.escape.window="showFilter = false">
                                <div class="position-relative">
                                    <!-- Filter Toggle Button -->
                                    @if($getSlugQuery != 'travel-attendance-report')
                                    <button type="button" @click="showFilter = !showFilter" 
                                        class="btn btn-outline-secondary btn-sm w-100 mb-2" 
                                        style="font-size: 12px; height: 30px;">
                                        <i class="bi bi-funnel"></i> Filter Options
                                    </button>
                                    @endif
                                    <div x-show="showFilter" x-transition
                                        class="mt-2 position-absolute w-100 shadow bg-white border rounded" style="z-index: 1050;">
                                        <div class="p-3">
                                            <div class="row gx-3 p-2">
                                                {{-- <div class="col-xl-12 col-lg-12 col-md-12">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" role="switch" id="travelTypeCheckbox">
                                                        <label class="form-check-label" for="travelTypeCheckbox">Travel Type</label>
                                                    </div>
                                                </div>
                                                <div class="col-xl-12 col-lg-12 col-md-12">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" role="switch" id="statusCheckbox">
                                                        <label class="form-check-label" for="statusCheckbox">Status</label>
                                                    </div>
                                                </div>
                                                <div class="col-xl-12 col-lg-12 col-md-12">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" role="switch" id="purposeTypeCheckbox">
                                                        <label class="form-check-label" for="purposeTypeCheckbox">Purpose Type</label>
                                                    </div>
                                                </div>
                                                <div class="col-xl-12 col-lg-12 col-md-12">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" role="switch" id="claimIdCheckbox">
                                                        <label class="form-check-label" for="claimIdCheckbox">Claim ID</label>
                                                    </div>
                                                </div>
                                                <div class="col-xl-12 col-lg-12 col-md-12">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" role="switch" id="travelIdCheckbox">
                                                        <label class="form-check-label" for="travelIdCheckbox">Travel ID</label>
                                                    </div>
                                                </div>
                                                <div class="col-xl-12 col-lg-12 col-md-12">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" role="switch" id="designationCheckbox">
                                                        <label class="form-check-label" for="designationCheckbox">Designation</label>
                                                    </div>
                                                </div>
                                                <div class="col-xl-12 col-lg-12 col-md-12">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" role="switch" id="employeeStatusCheckbox">
                                                        <label class="form-check-label" for="employeeStatusCheckbox">Employee Status</label>
                                                    </div>
                                                </div>
                                                <div class="col-xl-12 col-lg-12 col-md-12">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" role="switch" id="branchCheckbox">
                                                        <label class="form-check-label" for="branchCheckbox">Branch</label>
                                                    </div>
                                                </div> --}}
                                                <div class="col-xl-12 col-lg-12 col-md-12">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input" role="switch" id="tapLocationCheckbox">
                                                        <label class="form-check-label" for="tapLocationCheckbox">Tap Location</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-8 d-flex justify-content-end">
                                    <div class="d-flex flex-wrap gap-2">
                                        <form id="exportForm" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm px-4 py-2 shadow-sm"
                                                style="font-size: 12px; border-radius: 8px; font-weight: 500;" id="saveUptBtn">
                                                <i class="bi bi-download"></i> Export
                                            </button>
                                        </form>
                                       
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
        <script>
            document.getElementById('saveUptBtn').addEventListener('click', function(e) {
                e.preventDefault();
                
                const reportType = ($('#report_type_hidden').val() || $('select[name="report_type"]').val());
                if (!reportType || reportType === '' || reportType === null) {
                    Swal.fire({ icon: 'warning', title: 'Report Type Required', text: 'Please select a report type to continue.', confirmButtonColor: '#3085d6', confirmButtonText: 'OK' });
                    return false;
                }
                
                const selectedOptionText = $('select[name="report_type"] option:selected').text().trim() || 'Report';
                
                const form = document.getElementById('exportForm');
                const formData = new FormData(form);
                
                const employeeValue = $('#employee_search').val();
                const selectedEmployeeId = $('#employee_search').attr('data-selected-id');
                
                const travelType = $('select[name="travel_type"]').val();
                if (travelType) formData.append('travel_type', travelType);
                
                const statusValRaw = $('select[name="status"]').val();
                const statusVal = (statusValRaw && statusValRaw !== 'null' && statusValRaw !== 'undefined') ? statusValRaw : '';
                if (statusVal !== '') formData.append('status', statusVal);
                
                formData.append('report_type', reportType);
                
                const fromDate = $('input[name="from_date"]').val();
                if (fromDate) formData.append('from_date', fromDate);
                const toDate = $('input[name="to_date"]').val();
                if (toDate) formData.append('to_date', toDate);
                
                if (selectedEmployeeId && selectedEmployeeId !== '' && selectedEmployeeId !== null && selectedEmployeeId !== undefined) {
                    formData.append('employee', selectedEmployeeId);
                }
                
                const purposeType = $('select[name="purpose_type"]').val();
                if (purposeType) formData.append('purpose_type', purposeType);
                const claimId = $('input[name="claim_id"]').val();
                if (claimId) formData.append('claim_id', claimId);
                const travelId = $('input[name="travel_id"]').val();
                if (travelId) formData.append('travel_id', travelId);
                const designation = $('select[name="designation"]').val();
                if (designation) formData.append('designation', designation);
                const employeeStatus = $('select[name="employee_status"]').val();
                if (employeeStatus) formData.append('employee_status', employeeStatus);
                const branch = $('select[name="branch"]').val();
                if (branch) formData.append('branch', branch);
                const reportOutput = $('select[name="report_output"]').val();
                if (reportOutput) formData.append('report_output', reportOutput);
                
                // Add tap location switch value
                const tapLocation = $('#tapLocationCheckbox').is(':checked') ? '1' : '0';
                formData.append('tap_location', tapLocation);
                
                // Show loader while downloading
                const loader = document.getElementById('reportLoader');
                if (loader) loader.style.display = 'flex';

              // Create AbortController for timeout handling
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 300000); // 5 minutes timeout

                fetch("{{ route('ta.da.report') }}", {
                        method: 'POST',
                        body: formData,
                        signal: controller.signal,
                        headers: {
                            'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(async response => {
                        const contentType = response.headers.get('content-type') || '';
                        if (contentType.includes('application/json')) {
                            const data = await response.json();
                            if (data.status === 'no_data') {
                                Swal.fire({ icon: 'info', title: 'No Data', text: data.message || 'No records found for the selected criteria.', confirmButtonColor: '#3085d6', confirmButtonText: 'OK' });
                                return null;
                            }
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'An error occurred. Please try again.', confirmButtonColor: '#d33', confirmButtonText: 'OK' });
                            return null;
                        }
                        if (!response.ok) throw new Error('Error downloading the file.');
                        return response.blob();
                    })
                    .then(blob => {
                     clearTimeout(timeoutId); // Clear timeout on success
                        if (!blob) return;
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        var reportName = selectedOptionText.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_');
                        a.download = reportName + '_' + new Date().toISOString().split('T')[0] + '.xlsx';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                    })
                    .catch(error => {
                          clearTimeout(timeoutId); // Clear timeout on error
                        console.error('Download error:', error);
                        let errorMessage = 'An error occurred while downloading the file. Please try again.';
                        if (error.name === 'AbortError') {
                            errorMessage = 'The request timed out. The report might be too large. Please try with more specific filters.';
                        }
                        
                        Swal.fire({ 
                            icon: 'error', 
                            title: 'Download Error', 
                            text: errorMessage, 
                            confirmButtonColor: '#d33', 
                            confirmButtonText: 'OK' 
                        });
                    })
                    .finally(() => { if (loader) loader.style.display = 'none'; });
            });
        </script>
        <script>
            $(document).ready(function () {
                let searchTimeout;
                let selectedEmployeeId = '';
                let selectedEmployeeText = '';
                
                $('#employee_search').on('input', function() {
                    const query = $(this).val();
                    const resultsDiv = $('#employee_results');
                    clearTimeout(searchTimeout);
                    if (query.length < 1) { resultsDiv.hide(); return; }
                    searchTimeout = setTimeout(function() {
                        $.ajax({
                            url: '{{ url("/employee/search") }}',
                            type: 'POST',
                            data: { q: query, _token: '{{ csrf_token() }}' },
                            success: function(data) {
                                if (data.length > 0) {
                                    let html = '';
                                    data.forEach(function(item) {
                                        html += '<div class="p-2 border-bottom employee-option" data-id="' + item.emp_id + '" data-text="' + item.emp_full_name + ' (' + item.emp_code + ')" style="cursor: pointer; font-size: 12px;">';
                                        html += '<strong>' + item.emp_full_name + '</strong> (' + item.emp_code + ')';
                                        html += '</div>';
                                    });
                                    resultsDiv.html(html).show();
                                } else {
                                    resultsDiv.html('<div class="p-2 text-muted">No employees found</div>').show();
                                }
                            },
                            error: function() { resultsDiv.html('<div class="p-2 text-danger">Error searching employees</div>').show(); }
                        });
                    }, 300);
                });
                
                $(document).on('click', '.employee-option', function() {
                    const id = $(this).data('id');
                    const text = $(this).data('text');
                    selectedEmployeeId = id;
                    selectedEmployeeText = text;
                    $('#employee_search').val(text).attr('data-selected-id', id);
                    $('#employee_results').hide();
                });
                
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('#employee_search, #employee_results').length) {
                        $('#employee_results').hide();
                    }
                });
            });
        </script>
        <script>
            $(document).ready(function() {
                $('.form-control, .form-select').closest('.col-md-6').show();
                $('#travelTypeCheckbox').change(function() { $('select[name="travel_type"]').closest('.col-md-6').toggle(this.checked); });
                $('#statusCheckbox').change(function() { $('select[name="status"]').closest('.col-md-6').toggle(this.checked); });
                $('#purposeTypeCheckbox').change(function() { $('select[name="purpose_type"]').closest('.col-md-6').toggle(this.checked); });
                $('#claimIdCheckbox').change(function() { $('input[name="claim_id"]').closest('.col-md-6').toggle(this.checked); });
                $('#travelIdCheckbox').change(function() { $('input[name="travel_id"]').closest('.col-md-6').toggle(this.checked); });
                $('#designationCheckbox').change(function() { $('select[name="designation"]').closest('.col-md-6').toggle(this.checked); });
                $('#employeeStatusCheckbox').change(function() { $('select[name="employee_status"]').closest('.col-md-6').toggle(this.checked); });
                $('#branchCheckbox').change(function() { $('select[name="branch"]').closest('.col-md-6').toggle(this.checked); });
                $('#tapLocationCheckbox').change(function() { 
                    // Tap location is a switch that controls column visibility in export, no UI field to toggle
                    // This checkbox controls whether tap location column is included in the report
                });
                $('#travelTypeCheckbox, #statusCheckbox, #purposeTypeCheckbox, #claimIdCheckbox, #travelIdCheckbox, #designationCheckbox, #employeeStatusCheckbox, #branchCheckbox, #tapLocationCheckbox').prop('checked', true);

                // Always keep Report Type UI hidden and set from slug when present
                @php
                    $slugMap = config('tada_reports.slug_map', []);
                    $reports = config('tada_reports.reports', []);
                    $slugToIdName = [];
                    foreach ($slugMap as $slug => $id) {
                        $slugToIdName[$slug] = [
                            'id' => $id,
                            'name' => $reports[$id]['name'] ?? 'Report',
                        ];
                    }
                @endphp
                const slugToIdMap = @json($slugToIdName);
                const reportSlug = '{{ request('report') }}'?.toString().trim();
                const reportData = slugToIdMap[reportSlug] || null;
                if (reportData) {
                    $('#report_type_hidden').val(reportData.id);
                    $('select[name="report_type"]').val(reportData.id);
                    // Update breadcrumb with report name
                    $('#breadcrumb-report-name').text(reportData.name);
                }
                $('#reportTypeGroup').hide();
            });
        </script>
        <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @endpush
@endsection

