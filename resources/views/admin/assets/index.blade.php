@extends('admin.layout.master')

@section('title', 'Assets')

@section('header-actions')
    @can('create-assets')
    <a href="{{ route('assets.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>
        Add Asset
    </a>
    @endcan
    <button type="button" class="btn btn-outline-success ms-2" data-bs-toggle="modal" data-bs-target="#exportAssetsModal">
        <i class="bi bi-download me-1"></i>
        Export Assets
    </button>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <!-- Search and Filter Section -->
        <div class="row mb-3">
            <div class="col-md-12">
                <form method="GET" action="{{ route('assets.index') }}" class="row g-3">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Asset tag, serial, model..." 
                               value="{{ request('search') }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Asset Type</label>
                        <select name="asset_type_id" class="form-select">
                            <option value="">All Types</option>
                            @foreach($assetTypes as $type)
                            <option value="{{ $type->id }}" {{ request('asset_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    @if($tab === 'assigned')
                    <div class="col-md-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select">
                            <option value="">All Employees</option>
                            @foreach(\App\Models\Employee::where('is_active', true)->get() as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }} ({{ $employee->employee_id }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    <div class="col-md-2">
                        <label class="form-label">Division</label>
                        <select name="division" class="form-select">
                            <option value="">All Divisions</option>
                            <option value="Lipl" {{ request('division') == 'Lipl' ? 'selected' : '' }}>Lipl</option>
                            <option value="Ajax" {{ request('division') == 'Ajax' ? 'selected' : '' }}>Ajax</option>
                            <option value="KTPL" {{ request('division') == 'KTPL' ? 'selected' : '' }}>KTPL</option>
                            <option value="Sndk" {{ request('division') == 'Sndk' ? 'selected' : '' }}>Sndk</option>
                            <option value="FD" {{ request('division') == 'FD' ? 'selected' : '' }}>FD</option>
                            <option value="Wbco" {{ request('division') == 'Wbco' ? 'selected' : '' }}>Wbco</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            @if(request()->hasAny(['search', 'asset_type_id', 'employee_id', 'division']))
                            <a href="{{ route('assets.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x"></i> Clear
                            </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        @if(request()->hasAny(['search', 'asset_type_id', 'employee_id', 'division']))
        <div class="alert alert-info mb-3">
            <small>
                <i class="bi bi-funnel"></i> 
                Showing filtered results 
                @if(request('search'))
                    for "<strong>{{ request('search') }}</strong>"
                @endif
                ({{ $assets->total() }} {{ Str::plural('result', $assets->total()) }})
            </small>
        </div>
        @endif
    </div>

    <div class="card-body">
        @if($tab === 'stock')
            @include('assets.tabs.stock', ['assets' => $assets])
        @elseif($tab === 'assigned')
            @include('assets.tabs.assigned', ['assets' => $assets])
        @elseif($tab === 'scrap')
            @include('assets.tabs.scrap', ['assets' => $assets])
        @elseif($tab === 'replaced')
            @include('assets.tabs.replaced', ['assets' => $assets])
        @endif
    </div>
</div>

<!-- Assignment Modal -->
@can('assign-assets')
@if($tab === 'stock')
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Assets</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignForm" method="POST" action="{{ route('assets.assign') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Choose an employee...</option>
                        </select>
                    </div>
                    <div id="selectedAssets"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Assets</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Single Asset Assignment Modal -->
<div class="modal fade" id="assignSingleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignSingleForm" method="POST" action="{{ route('assets.assign') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Asset:</strong> <span id="singleAssetTag"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Choose an employee...</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-person-plus me-1"></i>
                        Assign Asset
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@endcan
@endsection

<!-- Export Assets Modal -->
<div class="modal fade" id="exportAssetsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Assets</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('assets.export') }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Asset Type</label>
                                <select name="asset_type_id" class="form-select">
                                    <option value="">All Asset Types</option>
                                    @foreach(\App\Models\AssetType::where('is_active', true)->get() as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="stock">Stock</option>
                                    <option value="assigned">Assigned</option>
                                    <option value="scrap">Scrap</option>
                                    <option value="replaced">Replaced</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Employee</label>
                                <select name="employee_id" class="form-select">
                                    <option value="">All Employees</option>
                                    @foreach(\App\Models\Employee::where('is_active', true)->get() as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_id }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Date Range</label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="date" name="date_from" class="form-control" placeholder="From">
                                    </div>
                                    <div class="col-6">
                                        <input type="date" name="date_to" class="form-control" placeholder="To">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-download me-1"></i>
                        Export Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Load employees for assignment
    @can('assign-assets')
    $.get('{{ route('employees.assignment-list') }}', function(employees) {
        const select = $('select[name="employee_id"]');
        employees.forEach(function(employee) {
            select.append(`<option value="${employee.id}">${employee.name} (${employee.employee_id}) - ${employee.division}, ${employee.branch}</option>`);
        });
    });

    // Handle assign button clicks
    $(document).on('click', '.assign-btn', function() {
        const assetIds = [];
        const assetTags = [];
        
        $('.asset-checkbox:checked').each(function() {
            assetIds.push($(this).val());
            assetTags.push($(this).data('tag'));
        });

        if (assetIds.length === 0) {
            alert('Please select at least one asset to assign.');
            return;
        }

        // Clear previous asset IDs
        $('input[name="asset_ids[]"]').remove();
        
        // Add selected asset IDs to form
        assetIds.forEach(function(id) {
            $('#assignForm').append(`<input type="hidden" name="asset_ids[]" value="${id}">`);
        });

        // Show selected assets
        $('#selectedAssets').html(`
            <div class="alert alert-info">
                <strong>Selected Assets:</strong><br>
                ${assetTags.join(', ')}
            </div>
        `);

        $('#assignModal').modal('show');
    });

    // Handle select all checkbox
    $('#selectAll').change(function() {
        $('.asset-checkbox').prop('checked', this.checked);
        updateAssignButton();
    });

    // Handle individual checkboxes
    $(document).on('change', '.asset-checkbox', function() {
        updateAssignButton();
    });

    function updateAssignButton() {
        const checkedCount = $('.asset-checkbox:checked').length;
        if (checkedCount > 0) {
            $('.assign-btn').removeClass('d-none').text(`Assign ${checkedCount} Asset${checkedCount > 1 ? 's' : ''}`);
        } else {
            $('.assign-btn').addClass('d-none');
        }
    }

    // Handle single asset assignment
    $(document).on('click', '.assign-single-btn', function() {
        const assetId = $(this).data('asset-id');
        const assetTag = $(this).data('asset-tag');
        
        // Clear previous asset ID
        $('#assignSingleForm input[name="asset_ids[]"]').remove();
        
        // Add asset ID to form
        $('#assignSingleForm').append(`<input type="hidden" name="asset_ids[]" value="${assetId}">`);
        
        // Show asset tag in modal
        $('#singleAssetTag').text(assetTag);
        
        // Show modal
        $('#assignSingleModal').modal('show');
    });

    // Load employees for single assignment modal
    $.get('{{ route('employees.assignment-list') }}', function(employees) {
        const singleSelect = $('#assignSingleModal select[name="employee_id"]');
        employees.forEach(function(employee) {
            singleSelect.append(`<option value="${employee.id}">${employee.name} (${employee.employee_id}) - ${employee.division}, ${employee.branch}</option>`);
        });
    });
    @endcan
});
</script>
@endpush