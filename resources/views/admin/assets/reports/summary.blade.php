@extends('admin.layout.master')
@section('title', 'Assets Report')

@section('header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endsection

@section('content')
<div>
    <div class="mt-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('/admin/report/attendance-report') }}">Assets Management</a></li>
            <li class="active"><span><b>Asset Summary Report</b></span></li>
        </ol>
    </div>

    <div class="row pt-5">
        <div class="container-fluid bg-white" style="padding-bottom: 500px;">
            <div class="row d-flex justify-content-center align-items-start ef-wrapper"
                 style="padding-top: 20px; padding-bottom: 20px;">
                <div class="col-md-10">

                    @if (session()->has('error'))
                        <div class="alert alert-danger ef-alert alert-dismissible fade show d-flex justify-content-between align-items-center mb-3" role="alert">
                            <div><strong>Error!</strong> {{ session('error') }}</div>
                            <button type="button" class="custom-close-button" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="ef-panel">

                        {{-- Header --}}
                        <div class="ef-panel-header">
                            <span class="ef-panel-title">Asset Summary Report Filters</span>
                        </div>

                        {{-- Fields Grid --}}
                        <div class="row gx-3 gy-3">

                            {{-- Asset Type --}}
                            <div class="col-md-6">
                                <label class="ef-field-label">Asset Type</label>
                                <select name="assetType" class="ef-control select2">
                                        <option value="">All</option>
                                        @foreach ($assetTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                            </div>

                            {{-- Category --}}
                            <div class="col-md-6">
                                <label class="ef-field-label">Category</label>
                                <select name="category" class="ef-control select2">
                                        <option value="">All</option>
                                        @foreach ($assetCategories as $category)
                                            <option value="{{ $category->ac_id }}">{{ $category->ac_name }}</option>
                                        @endforeach
                                    </select>
                            </div>

                            {{-- Brand --}}
                            <div class="col-md-6">
                                <label class="ef-field-label">Brand</label>
                                <select name="brand" class="ef-control select2">
                                        <option value="">All</option>
                                        @foreach ($assetBrands as $brand)
                                            <option value="{{ $brand->br_id }}">{{ $brand->br_name }}</option>
                                        @endforeach
                                    </select>
                            </div>

                            {{-- Date Range --}}
                            <div class="col-md-6">
                                <label class="ef-field-label">Date Range</label>
                                <div class="ef-input-wrap">
                                    <input type="text" name="dateRange" id="fromDate"
                                           class="ef-control" readonly placeholder="Select date range">
                                    <span class="ef-icon">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                            <line x1="16" y1="2" x2="16" y2="6"/>
                                            <line x1="8"  y1="2" x2="8"  y2="6"/>
                                            <line x1="3"  y1="10" x2="21" y2="10"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            {{-- Divider --}}
                            <div class="col-12"><div class="ef-divider"></div></div>

                            {{-- Footer --}}
                            <div class="col-12 d-flex align-items-center justify-content-end">
                               
                                <button type="button" class="ef-btn-export" id="saveUptBtn">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="7 10 12 15 17 10"/>
                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                    </svg>
                                    Export
                                </button>
                            </div>

                        </div>{{-- /.row --}}
                    </div>{{-- /.ef-panel --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {

            // Initialize Select2
            $('.select2').select2({
                placeholder: "All",
                allowClear: true,
                width: '100%'
            });

            // Date Range Picker
            $('#fromDate').daterangepicker({
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month'),
                minDate: moment('2000-01-01'),
                maxDate: moment().add(5, 'years'),
                opens: 'left',
                autoApply: true,
                ranges: {
                    'Today':                 [moment(), moment()],
                    'Yesterday':             [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days':           [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days':          [moment().subtract(29, 'days'), moment()],
                    'This Month':            [moment().startOf('month'), moment().endOf('month')],
                    'Last Month':            [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'This Year':             [moment().startOf('year'), moment().endOf('year')],
                    'Current Fiscal Period': [moment().startOf('quarter'), moment().endOf('quarter')],
                },
                locale: { format: 'MMM D, YYYY' }
            });

            // Reset filters
            $('#resetBtn').on('click', function () {
                $('.select2').val(null).trigger('change');
                $('#fromDate').val('');
            });

            // Export Button
            $('#saveUptBtn').on('click', function (e) {
                e.preventDefault();

                var form = $('<form>', {
                    action: "{{ route('assets.report.export.summary') }}",
                    method: "POST"
                });

                form.append('@csrf');
                form.append('<input type="hidden" name="assetType"  value="' + $('select[name="assetType"]').val()  + '">');
                form.append('<input type="hidden" name="category"   value="' + $('select[name="category"]').val()   + '">');
                form.append('<input type="hidden" name="assetTag"   value="' + $('select[name="assetTag"]').val()   + '">');
                form.append('<input type="hidden" name="brand"      value="' + $('select[name="brand"]').val()      + '">');
                form.append('<input type="hidden" name="dateRange"  value="' + $('input[name="dateRange"]').val()   + '">');

                $('body').append(form);
                form.submit();
                form.remove();
            });
        });
    </script>
@endsection