@extends('admin.layout.master')
@section('title', 'Kit Details')

@section('Css')

    <style>
        .table td,
        .table th {
            padding: 0.6rem !important;
        }

        .table thead th {
            white-space: nowrap;
        }
    </style>


@endsection
@section('content')
    <div class="row">

        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Kit Details</h5>

                    <span
                        class="badge 
                    @if ($kit_data->kit->status === 'active') bg-success
                    @elseif($kit_data->kit->status === 'inactive') bg-secondary
                    @else bg-info @endif">
                        {{ ucfirst($kit_data->kit->status) }}
                    </span>
                </div>

                <div class="card-body">

                    {{-- KIT BASIC DETAILS --}}
                    <table class="table table-bordered">
                        <tbody>

                            <tr>
                                <th>Kit Code</th>
                                <td>{{ $kit_data->kit->kit_code }}</td>

                                <th>Kit Name</th>
                                <td>{{ $kit_data->kit->name }}</td>
                            </tr>

                            <tr>
                                <th>Category</th>
                                <td>{{ $kit_data->kit->fh_category->sc_name ?? '-' }}</td>

                                <th>Unit</th>
                                <td>{{ $kit_data->kit->fh_unit->su_name ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Size</th>
                                <td>{{ $kit_data->kit->size ?? '-' }}</td>

                                <th>Is Payable</th>
                                <td>{{ $kit_data->is_payable == 'yes' ? 'Yes' : ($kit_data->is_payable == 'no' ? 'No' : '-') }}</td>


                            </tr>

                            <tr>
                                <th>Discount Type</th>
                                <td>{{ $kit_data->discount_type ?? '-' }}</td>

                                <th>Discount Value</th>
                                <td>{{ $kit_data->discount_value ?? '-' }}</td>

                            </tr>

                            <tr>
                                <th>Per Unit Price</th>
                                <td>₹ {{ number_format($kit_data->price_per_unit, 2) }}</td>

                                <th>Total Value</th>
                                <td>₹ {{ number_format($kit_data->total_price, 2) }}</td>
                            </tr>

                            <tr>
                                <th>Description</th>
                                <td colspan="3">{{ $kit_data->note ?? '-' }}</td>
                            </tr>

                        </tbody>
                    </table>

                    {{-- STOCK SUMMARY --}}
                    <h5 class="mt-4 mb-2">Stock Summary</h5>

                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Opening Qty</th>
                                <td>{{ $kit_data->opening_qty }}</td>

                                <th>Available</th>
                                <td>{{ $kit_data->available_qty }}</td>
                            </tr>

                            <tr>
                                <th>Assigned</th>
                                <td>{{ $kit_data->assigned_qty }}</td>

                                <th>Damaged</th>
                                <td>{{ $kit_data->damaged_qty }}</td>
                            </tr>

                            <tr>
                                <th>Lost</th>
                                <td>{{ $kit_data->lost_qty }}</td>

                                <th>Replaced</th>
                                <td>{{ $kit_data->replaced_qty }}</td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>


        {{-- RIGHT SIDE: KIT HISTORY --}}
        <div class="col-md-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Kit History (All Fields)</h6>
                </div>
                <div class="card-body p-0">
                    @if ($kit_history->count() > 0)
                        <table class="table table-bordered table-hover table-striped align-middle mb-0">
                            <thead class="table-light sticky-top" style="top: 0; z-index: 1;">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Action Type</th>
                                    <th>Items</th>

                                    <th>Opening Qty</th>
                                    <th>Available Qty</th>
                                    <th>Assigned Qty</th>
                                    <th>Damaged Qty</th>
                                    <th>Lost Qty</th>
                                    <th>Replaced Qty</th>

                                    <th>Price/Unit</th>
                                    <th>Total Price</th>

                                    <th>Note</th>
                                    <th>Action By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($kit_history as $index => $log)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>

                                        <td>
                                            <span
                                                class="badge 
                                            @if ($log->action_type == 'added') bg-primary
                                            @elseif($log->action_type == 'assigned') bg-success
                                            @elseif($log->action_type == 'returned') bg-info
                                            @elseif($log->action_type == 'damaged') bg-warning
                                            @elseif($log->action_type == 'lost') bg-danger
                                            @elseif($log->action_type == 'replaced') bg-secondary
                                            @else bg-dark @endif">
                                                {{ ucfirst($log->action_type) }}
                                            </span>
                                        </td>

                                        <td>{{ $log->qty ?? '-' }}</td>

                                        <td>{{ $log->opening_qty }}</td>
                                        <td>{{ $log->available_qty }}</td>
                                        <td>{{ $log->assigned_qty }}</td>
                                        <td>{{ $log->damaged_qty }}</td>
                                        <td>{{ $log->lost_qty }}</td>
                                        <td>{{ $log->replaced_qty }}</td>

                                        <td>₹ {{ number_format($log->price_per_unit, 2) }}</td>
                                        <td>₹ {{ number_format($log->total_price, 2) }}</td>

                                        <td>{{ $log->note ?? '-' }}</td>
                                        <td>{{ $log->user?->emp_full_name ?? 'System' }}</td>

                                        <td>
                                            <small class="text-muted">
                                                {{ $log->created_at->format('d M, Y H:i') }}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center p-3 mb-0">No history found</p>
                    @endif

                </div>

            </div>
        </div>
    </div>
@endsection
