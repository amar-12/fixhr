@extends('admin.layout.master')

@section('title', 'Asset Details')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Asset Details</h5>
                    <span
                        class="badge badge-status 
                    @if ($asset->status === 'stock') bg-primary
                    @elseif($asset->status === 'assigned') bg-success
                    @elseif($asset->status === 'scrap') bg-warning
                    @else bg-info @endif">
                        {{ ucfirst($asset->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        @if ($asset->asset_tag)
                                            <th>Asset Tag</th>
                                            <td>{{ $asset->asset_tag }}</td>
                                        @endif
                                        @if ($asset->assetType?->name)
                                            <th>Type</th>
                                            <td>{{ $asset->assetType->name }}</td>
                                        @endif
                                    </tr>

                                    <tr>
                                        @if ($asset->serial_number)
                                            <th>Serial Number</th>
                                            <td>{{ $asset->serial_number }}</td>
                                        @endif
                                        @if ($asset->model_number)
                                            <th>Model Number</th>
                                            <td>{{ $asset->model_number }}</td>
                                        @endif
                                    </tr>

                                    <tr>
                                        @if ($asset->purchase_value)
                                            <th>Purchase Value</th>
                                            <td>₹ {{ number_format($asset->purchase_value, 2) }}</td>
                                        @endif
                                        @if ($asset->purchase_date)
                                            <th>Purchase Date</th>
                                            <td>{{ \Carbon\Carbon::parse($asset->purchase_date)->format('M d, Y') }}</td>
                                        @endif
                                    </tr>

                                    <tr>
                                        @if ($asset->po_no)
                                            <th>Purchase No</th>
                                            <td>{{ $asset->po_no }}</td>
                                        @endif
                                        @if ($asset->vendor_name)
                                            <th>Purchase From (Vendor)</th>
                                            <td>{{ $asset->vendor_name }}</td>
                                        @endif
                                    </tr>

                                    {{-- <tr>
                                        @if ($asset->warranty_months)
                                            <th>Warranty</th>
                                            <td>{{ $asset->warranty_months }} Months</td>
                                        @endif
                                        @if ($asset->invoice_no)
                                            <th>Invoice No</th>
                                            <td>{{ $asset->invoice_no }}</td>
                                        @endif
                                    </tr> --}}

                                    <tr>
                                        @if ($asset->warranty_months)
                                            <th>Warranty</th>
                                            <td>
                                                {{ $asset->warranty_months }} Months
                                                @php
                                                    $status = 'N/A';
                                                    $leftText = '';

                                                    if ($asset->purchase_date && $asset->warranty_months) {
                                                        $expiryDate = \Carbon\Carbon::parse(
                                                            $asset->purchase_date,
                                                        )->addMonths($asset->warranty_months);

                                                        if ($expiryDate->isFuture()) {
                                                          $days = intval(now()->diffInDays($expiryDate));
                                                            $status = 'Under Warranty';
                                                            $leftText = $days . ' days left';
                                                        } else {
                                                            $days = $expiryDate->diffInDays(now());
                                                            $status = 'Expired';
                                                            $leftText = 'Expired ' . $days . ' days ago';
                                                        }
                                                    }
                                                @endphp

                                                <span
                                                    class="ms-2 badge {{ $status === 'Under Warranty' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $status }}
                                                </span>

                                                @if ($leftText)
                                                    <small class="text-muted ms-2">({{ $leftText }})</small>
                                                @endif
                                            </td>
                                        @endif

                                        @if ($asset->invoice_no)
                                            <th>Invoice No</th>
                                            <td>{{ $asset->invoice_no }}</td>
                                        @endif
                                    </tr>





                                    <tr>
                                        @if ($asset->fh_scraped)
                                            <th>Scraped By</th>
                                            <td>{{ $asset->fh_scraped->emp_full_name }}</td>
                                        @endif
                                        @if ($asset->scrapped_at)
                                            <th>Scraped Date</th>
                                            <td>{{ \Carbon\Carbon::parse($asset->scrapped_at)->format('M d, Y') }}</td>
                                        @endif
                                    </tr>
                                    {{-- Service Details --}}
                                    @if ($asset->fh_service?->service_type && $asset->fh_service?->fh_branch?->br_name)
                                        <tr>
                                            <th>Service Type</th>
                                            <td>{{ $asset->fh_service?->service_type }}</td>

                                            <th>Service Location</th>
                                            <td>{{ $asset->fh_service?->fh_branch?->br_name }}</td>
                                        </tr>
                                    @endif




                                    <tr>
                                        @if ($asset->fh_service?->service_status)
                                            <th>Status</th>
                                            <td>{{ $asset->fh_service->service_status }}</td>
                                        @endif
                                        @if ($asset->fh_service?->service_start_date)
                                            <th>Start Date</th>
                                            <td>{{ $asset->fh_service->service_start_date }}</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        @if ($asset->fh_service?->service_end_date)
                                            <th>End Date</th>
                                            <td>{{ $asset->fh_service->service_end_date }}</td>
                                        @endif
                                        @if ($asset->fh_service?->service_cost)
                                            <th>Service Cost</th>
                                            <td>{{ $asset->fh_service->service_cost }}</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        @if ($asset->fh_service?->courier_name)
                                            <th>Courier Name</th>
                                            <td>{{ $asset->fh_service->courier_name }}</td>
                                        @endif
                                        @if ($asset->fh_service?->docket_no)
                                            <th>Docket No</th>
                                            <td>{{ $asset->fh_service->docket_no }}</td>
                                        @endif
                                    </tr>

                                    <tr>
                                        @if ($asset->invoice_date)
                                            <th>Invoice Date</th>
                                            <td>{{ \Carbon\Carbon::parse($asset->invoice_date)->format('M d, Y') }}</td>
                                        @endif
                                        @if ($asset->invoice_file)
                                            <th>Invoice File</th>
                                            <td>
                                                <a href="{{ asset($asset->invoice_file) }}" target="_blank"
                                                    class="text-primary">
                                                    View File
                                                </a>
                                            </td>
                                        @endif
                                    </tr>

                                    @if ($asset->employee)
                                        <tr>
                                            <th>Assigned To</th>
                                            <td>
                                                <strong>{{ $asset->employee->emp_full_name }}</strong><br>
                                                <small class="text-muted">{{ $asset->employee->employee_id }}</small><br>
                                                <small class="text-muted">{{ $asset->employee->division }},
                                                    {{ $asset->employee->branch }}</small>
                                            </td>
                                            @if ($asset->assigned_at)
                                                <th>Assigned Date</th>
                                                <td>{{ $asset->assigned_at->format('M d, Y') }}</td>
                                            @endif
                                        </tr>
                                    @endif

                                    @if ($asset->notes)
                                        <tr>
                                            <th>Notes</th>
                                            <td colspan="3">{{ $asset->notes }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>


                        </div>

                    </div>

                    <div class="mt-4">
                        <div class="btn-group">
                            @if ($asset->status === 'assigned')
                                <form method="POST" action="{{ route('assets.unassign', $asset) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-secondary"
                                        onclick="return confirm('Are you sure you want to unassign this asset?')">
                                        <i class="bi bi-person-dash me-1"></i>
                                        Unassign
                                    </button>
                                </form>
                            @endif

                            {{-- @if ($asset->status !== 'scrap')
                                <form method="POST" action="{{ route('assets.scrap-asset', $asset) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-warning"
                                        onclick="return confirm('Are you sure you want to scrap this asset?')">
                                        <i class="bi bi-trash me-1"></i>
                                        Scrap
                                    </button>
                                </form>
                            @endif --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Asset History</h6>
                </div>
                <div class="card-body">
                    @if ($asset->histories->count() > 0)
                        <div class="timeline">
                            @foreach ($asset->histories->sortByDesc('created_at') as $history)
                                <div class="timeline-item mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span
                                                class="badge 
                                    @if ($history->action === 'created') bg-primary
                                    @elseif($history->action === 'assigned') bg-success
                                    @elseif($history->action === 'unassigned') bg-secondary
                                    @elseif($history->action === 'scrapped') bg-warning
                                    @else bg-info @endif mb-1">
                                                {{ ucfirst($history->action) }}
                                            </span>
                                            <p class="mb-1 small">{{ $history->notes }}</p>
                                            @if ($history->employee)
                                                <small class="text-muted">Employee:
                                                    {{ $history->employee->emp_full_name }}</small><br>
                                            @endif
                                            <small class="text-muted">By:
                                                {{ $history->user->emp_full_name ?? 'System' }}</small>
                                        </div>
                                        <small class="text-muted">{{ $history->created_at->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center">No history available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
