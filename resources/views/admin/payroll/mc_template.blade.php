<?php
use App\Helpers\RolePermissionLogics;
use Illuminate\Support\Facades\Auth;

$user = Auth::user();
$permission = new RolePermissionLogics();
?>

@extends('admin.layout.master')

@section('title')
    Payroll Sheet Report
@endsection

@section('css')
<style>
    h5 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #007bff;
    }
</style>
@endsection

@section('content')
    <div>
        <div class="p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('/admin/report/attendance-report') }}">Report</a></li>
                <li class="active"><span><b>MC Template Sheet</b></span></li>
            </ol>
        </div>

        <!-- ROW -->
        <div class="row pt-5">
            <nav class="navbar navbar-expand-lg navbar-light bg-white">
                <div class="container-fluid">
                    <span class="navbar-brand fw-bold me-5">
                        <span class="h3">MC Template</span>
                        <span class="h4 text-muted ms-5">
                            <i class="feather feather-calendar"></i> {{ now()->format('F d, Y') }}
                        </span>
                    </span>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <form method="POST" action="{{ route('mc.template.sheet.export') }}" id="payrollFilterForm" class="d-flex">
                            @csrf
                            <div class="row mb-2">
                                <div class="col-md-5">
                                    <label for="financialYearFilter" class="form-label fw-bold">Select Financial Year</label>
                                    <select name="financial_year" id="financialYearFilter" class="form-control" required>
                                        <option value="">Select Financial Year</option>
                                        @foreach ($finacial as $fy)
                                            <option value="{{ $fy->fy_id }}">{{ $fy->fy_year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label for="payrollPeriodFilter" class="form-label fw-bold">Select Payroll Period</label>
                                    <select name="payroll_period" id="payrollPeriodFilter" class="form-control" required>
                                        <option value="">Select Payroll Period</option>
                                        
                                    </select>
                                </div>

                               <!-- <div class="col-md-4">
                                    <label for="payrollPeriodFilter" class="form-label fw-bold">Select Payroll Period</label>
                                    <select name="payroll_period" id="payrollPeriodFilter" class="form-control" required>
                                        <option value="">Select Payroll Period</option>
                                        @foreach ($payrollPeriods as $period)
                                            <option value="{{ $period->pp_id }}" style="white-space: normal; word-wrap: break-word;">
                                                {{ $period->pp_name }} ({{ date('F Y', strtotime($period->pp_start_date)) }} -
                                                {{ date('F Y', strtotime($period->pp_end_date)) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div> -->

                                <div class="col-md-2 mt-5">
                                    <button class="btn btn-outline-success" type="submit">Export</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </nav>
        </div>
    </div>

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ session('error') }}',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    <script>
        $(document).ready(function () {
            $('#financialYearFilter').on('change', function () {
                var fyId = $(this).val();
                var $payrollDropdown = $('#payrollPeriodFilter');

                $payrollDropdown.empty().append('<option value="">Loading...</option>');

                if (fyId) {
                    $.ajax({
                        url: @json(route('payrollPeriods.byFY')),
                        type: 'GET',
                        data: { fy_id: fyId },
                        success: function (data) {
                            $payrollDropdown.empty().append('<option value="">Select Payroll Period</option>');

                            if (data.length > 0) {
                                $.each(data, function (index, period) {
                                    let start = new Date(period.pp_start_date);
                                    let end = new Date(period.pp_end_date);

                                    let optionText = `${period.pp_name} (${start.toLocaleString('default', { month: 'long', year: 'numeric' })} - ${end.toLocaleString('default', { month: 'long', year: 'numeric' })})`;

                                    $payrollDropdown.append(
                                        $('<option>', {
                                            value: period.pp_id,
                                            text: optionText
                                        })
                                    );
                                });
                            } else {
                                $payrollDropdown.append('<option value="">No Payroll Periods found</option>');
                            }
                        },
                        error: function (xhr, status, error) {
                            $payrollDropdown.empty().append('<option value="">Error loading periods</option>');
                        }
                    });
                } else {
                    $payrollDropdown.empty().append('<option value="">Select Payroll Period</option>');
                }
            });
        });
    </script>
@endsection

@section('scripts')
<!-- CSRF Token Meta -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Select all checkboxes
        document.getElementById('selectAllRows')?.addEventListener('click', function(event) {
            event.stopPropagation();
            document.querySelectorAll('.form-check-input').forEach(checkbox => {
                checkbox.checked = true;
            });
        });

        // Unselect all checkboxes
        document.getElementById('unselectAllRows')?.addEventListener('click', function(event) {
            event.stopPropagation();
            document.querySelectorAll('.form-check-input').forEach(checkbox => {
                checkbox.checked = false;
            });
        });

        const exportForm = document.getElementById('payrollSheetRecord');
        if (exportForm) {
            exportForm.addEventListener('submit', function (event) {
                event.preventDefault();

                const exportFormData = new FormData(this);

                // Collect dropdown values
                document.querySelectorAll('#branch, #department, #designation, #grade, #status').forEach(input => {
                    if (input.value) {
                        exportFormData.append(input.name, input.value);
                    }
                });

                // Collect checked checkboxes
                document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
                    exportFormData.append(checkbox.name, checkbox.value);
                });

                // Hidden form for submission
                const hiddenForm = document.createElement('form');
                hiddenForm.method = 'POST';
                hiddenForm.action = '{{ route("payroll.sheet.report.export") }}';
                hiddenForm.style.display = 'none';

                // Append CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                hiddenForm.appendChild(csrfInput);

                // Append form data
                for (const [key, value] of exportFormData.entries()) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    hiddenForm.appendChild(input);
                }

                document.body.appendChild(hiddenForm);
                hiddenForm.submit();
            });
        }
    });
</script>
@endsection
