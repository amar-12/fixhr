@extends('admin.layout.master')

@section('title', 'Assets Report')

@section('header')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

<!-- @section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

    <style>
        :root {
            --ef-bg:        #ffffff;
            --ef-surface:   #f8f9fb;
            --ef-surface2:  #f1f3f7;
            --ef-border:    #e2e6ee;
            --ef-text:      #1a1d2e;
            --ef-muted:     #6b7080;
            --ef-accent:    #4f7cff;
            --ef-accent2:   #7c5cfc;
            --ef-radius:    10px;
            --ef-input-h:   32px;
            --ef-font-sm:   12px;
            --ef-shadow-lg: 0 8px 32px rgba(0,0,0,0.10);
        }
        [data-bs-theme="dark"], .dark-mode {
            --ef-bg:        #0d0f14;
            --ef-surface:   #14171f;
            --ef-surface2:  #1c2030;
            --ef-border:    rgba(255,255,255,0.08);
            --ef-text:      #e8eaf0;
            --ef-muted:     #6b7080;
            --ef-shadow-lg: 0 8px 32px rgba(0,0,0,0.50);
        }
        .ef-panel { background:var(--ef-bg); border:1px solid var(--ef-border); border-radius:20px; padding:36px 40px 32px; box-shadow:var(--ef-shadow-lg),0 0 0 1px rgba(255,255,255,0.04); }
        .ef-panel-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; }
        .ef-panel-title { font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:var(--ef-muted); }
        .ef-field-label { display:block; font-size:11.5px; font-weight:600; color:var(--ef-muted); letter-spacing:0.04em; text-transform:uppercase; margin-bottom:5px; }
        .ef-control { width:100%; height:var(--ef-input-h); background:var(--ef-surface2); border:1px solid var(--ef-border); border-radius:var(--ef-radius); padding:0 34px 0 12px; color:var(--ef-text); font-size:var(--ef-font-sm); outline:none; transition:border-color .2s,box-shadow .2s; appearance:none; -webkit-appearance:none; }
        .ef-control::placeholder { color:var(--ef-muted); }
        .ef-control:focus { border-color:var(--ef-accent); box-shadow:0 0 0 3px rgba(79,124,255,.13); }
        .ef-input-wrap { position:relative; }
        .ef-input-wrap .ef-icon { position:absolute; right:11px; top:50%; transform:translateY(-50%); color:var(--ef-muted); pointer-events:none; display:flex; align-items:center; }
        .ef-input-wrap .ef-icon svg { width:13px; height:13px; }
        .ef-divider { height:1px; background:var(--ef-border); margin:8px 0; }
        .ef-btn-export { display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg,var(--ef-accent),var(--ef-accent2)); border:none; color:#fff; font-size:13.5px; font-weight:500; padding:11px 24px; border-radius:var(--ef-radius); cursor:pointer; box-shadow:0 4px 20px rgba(79,124,255,.30); transition:opacity .2s,transform .15s,box-shadow .2s; }
        .ef-btn-export:hover { opacity:.92; transform:translateY(-1px); box-shadow:0 8px 28px rgba(79,124,255,.40); }
        .ef-btn-export:active { transform:scale(.98); }
        .ef-btn-export svg { width:15px; height:15px; }
        .ef-btn-reset { background:none; border:none; color:var(--ef-muted); font-size:13px; cursor:pointer; padding:0; transition:color .2s; }
        .ef-btn-reset:hover { color:var(--ef-text); }
        .ef-alert { border-radius:var(--ef-radius); font-size:var(--ef-font-sm); }
        .custom-close-button { background:transparent; border:none; font-size:1.25rem; font-weight:bold; color:inherit; cursor:pointer; padding:0; margin-left:1rem; transition:opacity .2s; }
        .custom-close-button:hover { opacity:.6; }
        .custom-close-button::before { content:'×'; }

        /* Select2 overrides to match ef-control style */
        .select2-container .select2-selection--single { height:var(--ef-input-h) !important; background:var(--ef-surface2) !important; border:1px solid var(--ef-border) !important; border-radius:var(--ef-radius) !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height:var(--ef-input-h) !important; color:var(--ef-text); font-size:var(--ef-font-sm); padding-left:12px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height:var(--ef-input-h) !important; right:8px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow b { border-color:var(--ef-muted) transparent transparent; }
        .select2-container--default.select2-container--open .select2-selection--single { border-color:var(--ef-accent) !important; box-shadow:0 0 0 3px rgba(79,124,255,.13) !important; }
        .select2-dropdown { border:1px solid var(--ef-border) !important; border-radius:var(--ef-radius) !important; box-shadow:var(--ef-shadow-lg) !important; font-size:var(--ef-font-sm); }
        .select2-container--default .select2-results__option--highlighted { background-color:var(--ef-surface2) !important; color:var(--ef-text) !important; }
        .select2-search--dropdown .select2-search__field { border:1px solid var(--ef-border) !important; border-radius:6px !important; font-size:var(--ef-font-sm); padding:4px 8px; }

        /* Daterangepicker override */
        .daterangepicker { border-radius:var(--ef-radius) !important; border:1px solid var(--ef-border) !important; box-shadow:var(--ef-shadow-lg) !important; font-size:var(--ef-font-sm) !important; }
    </style>
@endsection -->

@section('content')
<div>
    <div class="mt-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('/admin/report/attendance-report') }}">Assets Management</a></li>
            <li class="active"><span><b>Asset Stock Report</b></span></li>
        </ol>
    </div>
    <div class="row d-flex justify-content-center align-items-start ef-wrapper" style="padding-top: 20px; padding-bottom: 20px;">
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
                    <span class="ef-panel-title">Asset Stock Report Filters</span>
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

                    {{-- Asset Tag --}}
                    <div class="col-md-6">
                        <label class="ef-field-label">Asset Tag</label>
                        <select name="assetTag" class="ef-control select2">
                            <option value="">All</option>
                            @foreach ($assetTags as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->asset_tag }}</option>
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

                    {{-- Model Number --}}
                    <div class="col-md-6">
                        <label class="ef-field-label">Model Number</label>
                        <select name="modelNumber" class="ef-control select2">
                            <option value="">All</option>
                            @foreach ($assetTags as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->model_number }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Vendor Name --}}
                    <div class="col-md-6">
                        <label class="ef-field-label">Vendor Name</label>
                        <select name="vendorName" class="ef-control select2">
                            <option value="">All</option>
                            @foreach ($assetTags as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->vendor_name }}</option>
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

                    {{-- Serial Number --}}
                    <div class="col-md-6">
                        <label class="ef-field-label">Serial Number</label>
                        <select name="serialNumber" class="ef-control select2">
                            <option value="">All</option>
                            @foreach ($assetTags as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->serial_number }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Range --}}
                    <div class="col-md-6">
                        <label class="ef-field-label">Date Range</label>
                        <div class="ef-input-wrap">
                            <input type="text" name="dateRange" id="fromDate" class="ef-control" readonly placeholder="Select date range">
                            <span class="ef-icon">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="col-12">
                        <div class="ef-divider"></div>
                    </div>

                    {{-- Footer --}}
                    <div class="col-12 d-flex align-items-center justify-content-end">

                        <button type="button" class="ef-btn-export" id="saveUptBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="7 10 12 15 17 10" />
                                <line x1="12" y1="15" x2="12" y2="3" />
                            </svg>
                            Export
                        </button>
                    </div>

                </div>{{-- /.row --}}
            </div>{{-- /.ef-panel --}}
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {

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
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
            },
            locale: {
                format: 'MMM D, YYYY'
            }
        });

        // Reset filters
        $('#resetBtn').on('click', function() {
            $('.select2').val(null).trigger('change');
            $('#fromDate').val('');
        });

        // Export Button
        $('#saveUptBtn').on('click', function(e) {
            e.preventDefault();

            const form = $('<form>', {
                action: "{{ route('assets.report.export.stock') }}",
                method: "POST"
            });

            form.append('@csrf');
            form.append('<input type="hidden" name="assetType"    value="' + $('select[name="assetType"]').val() + '">');
            form.append('<input type="hidden" name="assetTag"     value="' + $('select[name="assetTag"]').val() + '">');
            form.append('<input type="hidden" name="category"     value="' + $('select[name="category"]').val() + '">');
            form.append('<input type="hidden" name="modelNumber"  value="' + $('select[name="modelNumber"]').val() + '">');
            form.append('<input type="hidden" name="vendorName"   value="' + $('select[name="vendorName"]').val() + '">');
            form.append('<input type="hidden" name="brand"        value="' + $('select[name="brand"]').val() + '">');
            form.append('<input type="hidden" name="serialNumber" value="' + $('select[name="serialNumber"]').val() + '">');
            form.append('<input type="hidden" name="dateRange"    value="' + $('#fromDate').val() + '">');

            $('body').append(form);
            form.submit();
            form.remove();
        });
    });
</script>
@endsection