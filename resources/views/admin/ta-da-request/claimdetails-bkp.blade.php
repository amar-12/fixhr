@php
    use Carbon\Carbon;
@endphp
@extends('admin.layout.master')
@section('title')
    Claim
@endsection
@section('css')
    <style>
        td span {
            display: inline-block;
            width: 49%;
            vertical-align: top;
        }
    </style>
@endsection

@section('content')
    <input type="hidden" id="ajaxCall" value="{{ url('/') }}">
    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="py-0 bd-highlight">
                <div>
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a href="/admin/ta-da-request/claim">TA &amp; DA Requests</a></li>
                        <li class="active"><span><b>Claim Details</b></span></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="row row-sm">
            <div class="col-lg-12">
                <div class="card">

                    <div class="row">
                        <!-- Employee Details Card -->
                        <div class="col-lg-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Employee Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @php
                                            $employeeDetails = [
                                                'Employee Name' => $claimData->fh_employee->emp_full_name ?? 'N/A',
                                                'Employee Code' => $claimData->fh_employee->emp_code ?? 'N/A',
                                                'Mobile No.' => $claimData->fh_employee->emp_phone ?? 'N/A',
                                                'Branch' => $claimData->fh_tada_request_plan->fh_branch->br_name ?? 'N/A',
                                                'Department' => $claimData->fh_employee->fh_department->d_name ?? 'N/A',
                                                'Designation' => $claimData->fh_employee->fh_designation->dg_name ?? 'N/A',
                                                'Category' => $claimData->fh_tada_request_plan->fh_policy_tada_category->ptc_name ?? 'N/A',
                                            ];
                                        @endphp

                                        @foreach ($employeeDetails as $label => $value)
                                            <div class="col-6">
                                                <p class="mb-1"><b>{{ $label }}</b></p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1">{{ $value }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Claim Details Card -->
                        <div class="col-lg-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Claim Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @php
                                            $claimDetails = [
                                                'Travel ID' => $claimData->fh_tada_request_plan->trp_unique_id ?? 'N/A',
                                                'Claim ID' => $claimData->tc_unique_id ?? 'N/A',
                                                'Travel Type' => $claimData->fh_tada_request_plan->fh_policy_tada_travel_type->fh_travel_type->m_name ?? 'N/A',
                                                'Status' => $claimData->fh_claim_status->m_name ?? 'N/A',
                                                'Purpose' => $claimData->fh_tada_request_plan->fh_travel_purpose->tp_name ?? 'N/A',
                                                'Applied Date' => isset($claimData->created_at) ? $claimData->created_at->format('d-m-Y') : 'N/A',
                                                'Trip Name' => $claimData->fh_tada_request_plan->trp_name ?? 'N/A',
                                            ];
                                        @endphp

                                        @foreach ($claimDetails as $label => $value)
                                            <div class="col-6">
                                                <p class="mb-1"><b>{{ $label }}</b></p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-1">{{ $value }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $expenseTypes = $claimData->fh_tada_request_plan->fh_tada_expenses->groupBy(
                            'fh_expense_type.m_name',
                        );
                    @endphp

                    @if ($expenseTypes->isNotEmpty())
                        @foreach ($expenseTypes as $expenseType => $expenses)
                            <div class="card-header">
                                <h3 class="card-title">{{ $expenseType }} Expenses</h3>
                            </div>
                            <div class="card-body">
                                @foreach ($expenses as $expense)
                                    @if ($expense->te_type_id == 158)
                                        <div class="row">
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Applied Date :</b></span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->created_at->format('d-m-y') }}</span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Hotel Name :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_hotel_name }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Location :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_to_location }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>From/To Date Time
                                                                :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2">
                                                        {{ isset($expense->te_from_date) ? $expense->te_from_date : '' }}
                                                        {{ isset($expense->te_from_time) ? (\Carbon\Carbon::hasFormat($expense->te_from_time, 'H:i:s') ? \Carbon\Carbon::createFromFormat('H:i:s', $expense->te_from_time)->format('H:i:s') : $expense->te_from_time) : '' }}
                                                        <br>to<br>
                                                        {{ isset($expense->te_to_date) ? $expense->te_to_date : '' }}
                                                        {{ isset($expense->te_to_time) && \Carbon\Carbon::hasFormat($expense->te_to_time, 'H:i:s') ? \Carbon\Carbon::createFromFormat('H:i:s', $expense->te_to_time)->format('H:i:s') : $expense->te_to_time }}
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Occupancy :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_occupancy }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Amount :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_amount }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Taxes :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14">{{ $expense->te_taxes }}</span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Remarks :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_remarks }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Paid By :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_paid_by }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Documents :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2">
                                                        <span class="fs-14">
                                                            @php
                                                                $documents = json_decode($expense->te_document, true); // Decode JSON into an array
                                                            @endphp

                                                            @if (is_array($documents) && count($documents) > 0)
                                                                <a href="#" type="button" data-bs-toggle="modal" data-bs-target="#documentsModal{{ $loop->index }}" class="text-primary">
                                                                    <u>View Documents</u>
                                                                </a>
                                                                <!-- Modal -->
                                                                @component('admin.components.document-modal', [
                                                                    'id' => $loop->index,
                                                                    'documents' => $documents, // Pass the decoded documents array
                                                                    'componentString' => 'Expense_',
                                                                ])
                                                                @endcomponent
                                                            @else
                                                                N/A
                                                            @endif
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <hr>
                                    @endif
                                @endforeach

                                @foreach ($expenses as $expense)
                                    @if ($expense->te_type_id == 159)
                                        <div class="row">
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Applied Date :</b></span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->created_at->format('d-m-y') }}</span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Source :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_from_location }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Destination :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_to_location }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Total KM :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_total_km_driven }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Amount :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_amount }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Taxes :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_taxes }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Remarks :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_remarks }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Paid By :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_paid_by }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Documents :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2">
                                                        <span class="fs-14">
                                                            @php
                                                                $documents = json_decode($expense->te_document, true); // Decode JSON into an array
                                                            @endphp

                                                            @if (is_array($documents) && count($documents) > 0)
                                                                <a href="#" type="button" data-bs-toggle="modal" data-bs-target="#documentsModal{{ $loop->index }}" class="text-primary">
                                                                    <u>View Documents</u>
                                                                </a>
                                                                <!-- Modal -->
                                                                @component('admin.components.document-modal', [
                                                                    'id' => $loop->index,
                                                                    'documents' => $documents, // Pass the decoded documents array
                                                                    'componentString' => 'Expense_',
                                                                ])
                                                                @endcomponent
                                                            @else
                                                                N/A
                                                            @endif
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <hr>
                                    @endif
                                @endforeach

                                @foreach ($expenses as $expense)
                                    @if ($expense->te_type_id == 160)
                                        <div class="row">
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Applied Date :</b></span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->created_at->format('d-m-y') }}</span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Amount :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_amount }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Taxes :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_taxes }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Remarks :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_remarks }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Paid By :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_paid_by }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Documents :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2">
                                                        <span class="fs-14">
                                                            @php
                                                                $documents = json_decode($expense->te_document, true); // Decode JSON into an array
                                                            @endphp

                                                            @if (is_array($documents) && count($documents) > 0)
                                                                <a href="#" type="button" data-bs-toggle="modal" data-bs-target="#documentsModal{{ $loop->index }}" class="text-primary">
                                                                    <u>View Documents</u>
                                                                </a>
                                                                <!-- Modal -->
                                                                @component('admin.components.document-modal', [
                                                                    'id' => $loop->index,
                                                                    'documents' => $documents, // Pass the decoded documents array
                                                                    'componentString' => 'Expense_',
                                                                ])
                                                                @endcomponent
                                                            @else
                                                                N/A
                                                            @endif
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <hr>
                                    @endif
                                @endforeach

                                @foreach ($expenses as $expense)
                                    @if ($expense->te_type_id == 161)
                                        <div class="row">
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Applied Date :</b></span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->created_at->format('d-m-y') }}</span>
                                                    </li>
                                                </ul>
                                            </div>
                                            {{-- <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Source :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14">{{ $expense->te_from_location }}</span></li>
                                                </ul>
                                            </div> --}}
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Amount :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_amount }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Taxes :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_taxes }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Remarks :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_remarks }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Paid By :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span
                                                            class="fs-14">{{ $expense->te_paid_by }}</span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2"><span class="fs-14"><b>Documents :</b></span></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <ul>
                                                    <li class="my-2">
                                                        <span class="fs-14">
                                                            @php
                                                                $documents = json_decode($expense->te_document, true); // Decode JSON into an array
                                                            @endphp

                                                            @if (is_array($documents) && count($documents) > 0)
                                                                <a href="#" type="button" data-bs-toggle="modal" data-bs-target="#documentsModal{{ $loop->index }}" class="text-primary">
                                                                    <u>View Documents</u>
                                                                </a>
                                                                <!-- Modal -->
                                                                @component('admin.components.document-modal', [
                                                                    'id' => $loop->index,
                                                                    'documents' => $documents, // Pass the decoded documents array
                                                                    'componentString' => 'Expense_',
                                                                ])
                                                                @endcomponent
                                                            @else
                                                                N/A
                                                            @endif
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <hr>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                    {{-- @foreach ($claimData->fh_tada_request_details as $item)
                        @php
                            $fields = [
                                'Claim Name' => $item->trd_name ?? 'N/A',
                                'Claim Mode' => $item->fh_policy_tada_travel_mode->fh_travel_mode->m_name ?? 'N/A',
                                'Claim Vehicle' => $item->fh_policy_tada_travel_vehicle->fh_vehicle->m_name ?? 'N/A',
                                'Source' => $item->trd_source ?? 'N/A',
                                'Destination' => $item->trd_destination ?? 'N/A',
                                'Start Date' => $item->trd_start_date ?( $item->trd_start_date->format('d-m-Y') ?? 'N/A' ) : 'N/A',
                                'End Date' => $item->trd_end_date?( $item->trd_end_date->format('d-m-Y') ?? 'N/A') : 'N/A',
                                'Documents' => $item->trd_documents ?? 'N/A',
                                'Latitude' => $item->trd_latitude ?? 'N/A',
                                'Longitude' => $item->trd_longitude ?? 'N/A',
                                'Start Time' => $item->trd_start_time ?? 'N/A',
                                'End Time' => $item->trd_end_time ?? 'N/A',
                                'Total Distance' => $item->trd_total_distance ?? 'N/A',
                                'Total Tolerance' => $item->trd_total_tolerance ?? 'N/A',
                                'Call ID' => $item->trd_call_id ?? 'N/A',
                                'Status' => $item->trd_status ?? 'N/A',
                                'Remarks' => $item->trd_remarks ?? 'N/A',
                                'Purpose' => $item->trd_purpose ?? 'N/A',
                                'Ticket Type' => $item->trd_ticket_type ?? 'N/A',
                                'Net Amount' => $item->trd_net_amount ?? 'N/A',
                            ];
                        @endphp
                        <div class="card-body">
                            <div class="row">
                                @foreach ($fields as $label => $value)
                                    <div class="col-6 col-sm-2">
                                        <ul>
                                            <li class="my-2"><span class="fs-14"><b>{{ $label }}</b></span></li>
                                        </ul>
                                    </div>
                                    <div class="col-6 col-sm-2">
                                        <ul>
                                            <li class="my-2"><span class="fs-14">{{ $value }}</span></li>
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach --}}
                    @if (isset($claimData->fh_tada_request_plan->fh_tada_expenses))
                        {{-- @dd($claimData->fh_tada_request_plan->fh_tada_expenses->groupBy('te_type_id')); --}}
                        <div class="card-header">
                            <h3 class="card-title">Claim Amount</h3>
                        </div>
                        <div class="table-responsive px-3">
                            <table class="table  table-vcenter text-nowrap table-bordered border">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Particulars</th>
                                        <th>Amount</th>
                                        <th>Paid By</th>
                                        {{-- <th>Total</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $i = 1;
                                        $totalMinutes = 0;
                                        $totalLodgingAmountByPolicy = 0;
                                        $differenceInDays = 0;
                                    @endphp
                                    {{-- @foreach ($claimData->fh_tada_request_plan->fh_tada_expenses->groupBy('te_type_id') as $key => $tadaExpenseItem) --}}
                                    @foreach ($claimData->fh_tada_request_plan->fh_tada_expenses->groupBy(['te_type_id', 'te_paid_by']) as $key => $item)
                                        @foreach ($item as $key1=> $tadaExpenseItem)
                                            @if ($key == 159)
                                                @php
                                                    $firstItem = $tadaExpenseItem->sortBy('te_from_date')->first();
                                                    $lastItem = $tadaExpenseItem->sortByDesc('te_to_date')->first();

                                                    if ($firstItem && $lastItem) {
                                                        $firstDate = Carbon::parse($firstItem->te_from_date);
                                                        $lastDate = Carbon::parse($lastItem->te_to_date);
                                                        $differenceInDays = $firstDate->diffInDays($lastDate);
                                                    } else {
                                                        $differenceInDays = 0;
                                                    }
                                                @endphp
                                                @foreach ($tadaExpenseItem as $attributes)
                                                    @php

                                                        // Combine the dates and times
                                                        $fromDateTime =
                                                            $attributes['te_from_date'] . ' ' . $attributes['te_from_time'];
                                                        $toDateTime =
                                                            $attributes['te_to_date'] . ' ' . $attributes['te_to_time'];

                                                        // Create DateTime objects
                                                        $from = new DateTime($fromDateTime);
                                                        $to = new DateTime($toDateTime);

                                                        // Calculate the difference
                                                        $interval = $from->diff($to);

                                                        // Convert the difference to hours and minutes
                                                        $hours = $interval->days * 24 + $interval->h;
                                                        $minutes = $interval->i;
                                                        $totalMinutesForItem = $hours * 60 + $minutes;

                                                        // Accumulate the total time
                                                        $totalMinutes += $totalMinutesForItem;

                                                        // Calculate the total hours and minutes
                                                        $totalHours = intdiv($totalMinutes, 60); // Total hours
                                                        $remainingMinutes = $totalMinutes % 60; // Remaining minutes
                                                        // Debugging output
                                                    @endphp
                                                @endforeach
                                            @elseif($key == 158)
                                                @foreach ($tadaExpenseItem as $attributes)
                                                    @php
                                                        // Combine the dates and times
                                                        $fromDateTime =
                                                            $attributes['te_from_date'] . ' ' . $attributes['te_from_time'];
                                                        $toDateTime =
                                                            $attributes['te_to_date'] . ' ' . $attributes['te_to_time'];

                                                        // Create DateTime objects
                                                        $from = new DateTime($fromDateTime);
                                                        $to = new DateTime($toDateTime);

                                                        // Calculate the difference
                                                        $interval = $from->diff($to);

                                                        // Convert the difference to hours and minutes
                                                        $hours = $interval->days * 24 + $interval->h;
                                                        $minutes = $interval->i;
                                                        $totalMinutesForItem = $hours * 60 + $minutes;
                                                        // Debugging output
                                                        if (!empty(json_decode($attributes['te_document']))) {
                                                            $perMinutAmount = $lodgingWithBill / ($workingHour * 60);
                                                        } else {
                                                            $perMinutAmount = $lodgingWithOutBill / ($workingHour * 60);
                                                        }
                                                        $totalLodgingAmountByPolicy +=
                                                            $perMinutAmount * $totalMinutesForItem;
                                                    @endphp
                                                @endforeach
                                            @endif
                                            <tr>
                                                <td>{{ $i++ }}</td>
                                                <td>{{ $tadaExpenseItem[0]->fh_expense_type->m_name }}</td>
                                                @if ($key == 158)
                                                    <td><span>
                                                            {{ $tadaExpenseItem->sum('te_amount') + $tadaExpenseItem->sum('te_taxes') }}</span><span>
                                                            {{ 'As per the policy Total Amount: ' . $totalLodgingAmountByPolicy }}</span>
                                                    </td>
                                                @else
                                                    <td>{{ $tadaExpenseItem->sum('te_amount') + $tadaExpenseItem->sum('te_taxes') }}
                                                    </td>
                                                @endif
                                                </td>
                                                <td>{{ Str::ucfirst($tadaExpenseItem[0]->te_paid_by) }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                    @if(isset($firstDate) && isset($lastDate))
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ 'DA' }}</td>
                                        <td>{{ $daEligibility * $differenceInDays }}</td>
                                        <td>{{ 'Self' }}</td>
                                        <td>{{ $totalDA = $daEligibility * $differenceInDays }}</td>
                                        <td></td>
                                        <td>{{ $firstDate->format('d-M-Y') . ' To ' . $lastDate->format('d-M-Y') . ' = ' . $firstDate->diffInDays($lastDate) . ' Days' }}
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td colspan="2">Claimed Amount With DA</td>
                                        <td colspan="2"><b>
                                                    <b>{{ $claimData->tc_amount ?? 0 }}</b>
                                            </b><span> (Expenses+Tax = {{ $claimData->tc_amount - $claimData->tc_da_amount }}, DA ={{$claimData->tc_da_amount ??  'NA'}})</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">Advance</td>
                                        <td colspan="2">
                                            @if ($claimData->fh_tada_request_plan && $claimData->fh_tada_request_plan->fh_tada_expenses)
                                                <b>-{{ isset($claimData->fh_tada_request_plan) ? $claimData->fh_tada_request_plan->trp_advance_allowance ?? '--' : '--' }}</b>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">Deduction</td>
                                        <td colspan="2">

                                            <b>-{{ $claimData->tc_deduction_amount ?? 0 }}</b>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">Eligible Payble Amount </td>
                                        <td colspan="2">

                                                <b>
                                                    {{ ($claimData->tc_amount ?? 0) - ( ($claimData->fh_tada_request_plan->trp_advance_allowance ??  0) + ($claimData->tc_deduction_amount ?? 0) )  }}</b>

                                        </td>
                                    </tr>

                                <tbody>
                            </table>
                        </div>
                    @endif

                    @if ($claimData->fh_claim_approval_log()->exists())
                        <div class="card-header">
                            <h3 class="card-title">Claim Approval Details</h3>
                        </div>
                        <div class="table-responsive px-3">
                            @if ($claimData->fh_claim_approval_log)
                                <table class="table  table-vcenter text-nowrap  border-bottom ">
                                    <thead>
                                        <tr>
                                            <th class="col-md-4">Name</th>
                                            <th class="col-md-4">Action</th>
                                            <th class="col-md-4">Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($claimData->fh_claim_approval_log as $item2)
                                            <tr>
                                                <td class="col-md-4">
                                                    {{ $item2->fh_employee->emp_fname ?? (' ' . ' ' . $item2->fh_employee->emp_mname ?? (' ' . ' ' . $item2->fh_employee->emp_lname ?? '')) }}
                                                </td>
                                                <td class="col-md-4">{{ $item2->fh_status->m_name }}</td>
                                                <td class="col-md-4">{{ $item2->log_description }}</td>
                                            </tr>
                                        @endforeach
                                    <tbody>
                                </table>
                            @endif
                        </div>
                    @endif

                    @if($actionRoute !== 'claim-request.request.show' && $actionRoute !== 'claim-request-is-paid.request.show')
                        @if (($approvalData &&  (is_null($claimData->tc_deduction_amount) || $claimData->tc_deduction_status)) || ($approvalData && ($claimData->tc_deduction_amount && !is_null($claimData->tc_deduction_status))))
                            <div class="card-header">
                                <h3 class="card-title">Approval Or Reject</h3>
                            </div>
                            <div class="card-body">

                                {{-- @if(!$claimData->tc_deduction_amount) --}}
                                <div class="row">
                                    <div class="col-md-12 col-lg-12 d-flex justify-content-end">
                                        <button class="btn btn-outline-primary mx-3 addDeductionBtn {{$claimData->tc_deduction_amount ? 'd-none': ''}}">Add Deduction</button>
                                    </div>
                                </div>
                                {{-- @endif --}}

                                <form id="approvalForm">

                                    <div class="form-group">

                                        <div id="deductionAmountDiv"></div>

                                        <div class="row">
                                            <div class="col-md-12 col-lg-2">
                                                <label class="form-label mb-0 mt-2">Message</label>
                                            </div>
                                            <div class="col-md-12 col-lg-12">
                                                <textarea rows="2" name="message" class="form-control" id="actionMessage"></textarea>
                                            </div>
                                        </div>

                                        <div class="card-footer mt-3">
                                            <div class="row">
                                                <div class="col-md-12 col-lg-12 d-flex justify-content-end">
                                                    <a href="javascript:void(0);"
                                                        data-approval_status="{{ $approvalData->fh_approver_status->m_id }}"
                                                        data-approval_type="0"
                                                        data-approval_action_type="{{ $approvalData->pa_type }}"
                                                        data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                        data-tc_id="{{ md5($claimData->tc_id) }}"
                                                        data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                        data-is_last_approval="{{ $approvalData->pa_last }}"
                                                        data-emp_d_id="{{ optional($data->fh_employee)->emp_d_id }}"
                                                        class="btn btn-outline-danger  actionBtn mx-3">Reject</a>

                                                    <a href="javascript:void(0);"
                                                        data-approval_status="{{ $approvalData->fh_approver_status->m_id }}"
                                                        data-approval_type="1"
                                                        data-approval_action_type="{{ $approvalData->pa_type }}"
                                                        data-approval_sequence="{{ $approvalData->pa_sequence }}"
                                                        data-tc_id="{{ md5($claimData->tc_id) }}"
                                                        data-module_id="{{ md5($approvalData->pa_am_id) }}"
                                                        data-emp_d_id="{{ optional($data->fh_employee)->emp_d_id }}"
                                                        data-is_last_approval="{{ $approvalData->pa_last }}"class="btn btn-success actionBtn">{{ $approvalData->fh_approver_status->m_name }}</a>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endif
                        @if ($displayDeductionHandler)
                            <div class="card-header">
                                <h3 class="card-title">Accept Or Decline</h3>
                            </div>
                            <div class="card-body">

                                <form id="deductionForm">

                                    <div class="form-group">

                                        <div class="row">
                                            <div class="col-md-12 col-lg-2">
                                                <label class="form-label mb-0 mt-2">Message</label>
                                            </div>
                                            <div class="col-md-12 col-lg-12">
                                                <textarea rows="2" name="empAcceptanceMsg" class="form-control" id="empAcceptanceMsg"></textarea>
                                            </div>
                                        </div>

                                        <div class="card-footer mt-3">
                                            <div class="row">
                                                <div class="col-md-12 col-lg-12 d-flex justify-content-end">
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-outline-danger   mx-3 handleDeductionBtn"
                                                        data-tc_id="{{ md5($claimData->tc_id) }}" data-action="0">Decline</a>

                                                    <a href="javascript:void(0);" class="btn btn-success handleDeductionBtn"
                                                        data-tc_id="{{ md5($claimData->tc_id) }}" data-action="1">Accept</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endif
                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection
@section('script')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>

        $(document).ready(function() {

            // var pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", { cluster: "{{ env('PUSHER_APP_CLUSTER') }}"});
            // var channel = pusher.subscribe('claim-approval-channel');
            // channel.bind('claim-approval-event', function(result) {
            //     if(result.data.next_approver_id == "{{md5(Auth::user()->emp_id)}}" && result.data.tc_id == "{{ md5($claimData->tc_id) }}"){
            //         var baseUrl = $('#ajaxCall').val();
            //             window.location.href = baseUrl + "/admin/ta-da-request/claim/show/{{ md5($claimData->tc_id) }}";
            //     }
            // });

            var deductionAmount = "{{ $claimData->tc_deduction_amount }}";
            var tcId = "{{ md5($claimData->tc_id) }}";
            if(deductionAmount){
                $('.addDeductionBtn').click();
                $('#deductionAmount').val(deductionAmount);
            }
        });
        var tcId = '';
        $(document).on('click', '.actionBtn', function() {
            var dataAttributes = {};
            $.each(this.attributes, function() {
                if (this.name.startsWith('data-')) {
                    var key = this.name.slice(5); // remove 'data-' prefix
                    dataAttributes[key] = this.value;
                }
            });

            tcId = dataAttributes["tc_id"];

            dataAttributes['message'] = $('#actionMessage').val();
            if (dataAttributes['message'] == '') {
                Swal.fire({
                    icon: "warning",
                    text: 'Message is required.',
                    timer: 3000,
                });
                return false;
            }

            if ($('#deductionAmount').length > 0 && $('#deductionAmount').val() == '') {
                Swal.fire({
                    icon: "warning",
                    text: 'Deduction Amount Required',
                    timer: 3000,
                });
                return false;
            }
            dataAttributes['deduction_amount'] = $('#deductionAmount').val();

            $.ajax({
                url: '{{ route('admin.approval-handler') }}',
                method: "post",
                data: {
                    _token: '{{ csrf_token() }}',
                    POST_TYPE: 'CLAIM_REQUEST_APPROVAL',
                    data: dataAttributes
                },
                dataType: "json",
                beforeSend: function() {
                    $("#gloabal-overlay").show();
                    $(".actionBtn").attr("disabled", true);
                },
                success: function(data) {
                    $("#gloabal-overlay").hide();
                    $(".actionBtn").attr("disabled", false);
                    if (data.status == true) {
                        Swal.fire({
                            icon: "success",
                            text: data.message,
                            timer: 3000,
                        });
                        var baseUrl = $('#ajaxCall').val();
                        window.location.href = baseUrl + "/admin/ta-da-request/claim/show/" + tcId;
                    } else {
                        Swal.fire({
                            icon: "warning",
                            text: data.message,
                            timer: 3000,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    $("#gloabal-overlay").hide();
                    Swal.fire({
                        icon: "error",
                        text: error,
                        timer: 3000,
                    });
                },
            });

        });

        $(document).on('click', '.addDeductionBtn', function() {
            if ($('#deductionAmount').length == 0) {
                html = `<div class="row" id="deductionAmountRow">
                <div class="col-md-10 col-lg-10">
                    <label class="form-label mb-0 mt-2">Deduction Amount</label>
                </div>
                <div class="col-md-12 col-lg-12">
                    <div class="input-group">
                    <input type="number" name="deductionAmount" class="form-control " id="deductionAmount" placeholder="Enter Deduction Amount">
                    <button type="button" class="btn btn-outline-danger " id="removeDeductionInput"><i class="feather feather-trash"></i></button>
                    </div>
                </div>
            </div>`;
                $('#deductionAmountDiv').append(html);
            }
        });

        $(document).on('click', '#removeDeductionInput', function() {
            $("#deductionAmountRow").remove();
        })



        $(document).on('click', '.handleDeductionBtn', function() {
            var dataAttributes = {};
            $.each(this.attributes, function() {
                if (this.name.startsWith('data-')) {
                    var key = this.name.slice(5); // remove 'data-' prefix
                    dataAttributes[key] = this.value;
                }
            });

            tcId = dataAttributes["tc_id"];

            if ($('#empAcceptanceMsg').length > 0) {
                dataAttributes['remarks'] = $('#empAcceptanceMsg').val();
                if (dataAttributes['remarks'] == '') {
                    Swal.fire({
                        icon: "warning",
                        text: 'Message is required.',
                        timer: 3000,
                    });
                    return false;
                }
            }

            $.ajax({
                url: '{{ route('admin.deduction-handler') }}',
                method: "post",
                data: {
                    _token: '{{ csrf_token() }}',
                    data: dataAttributes
                },
                dataType: "json",
                beforeSend: function() {
                    $("#gloabal-overlay").show();
                    $(".handleDeductionBtn").attr("disabled", true);
                },
                success: function(data) {
                    $("#gloabal-overlay").hide();
                    $(".handleDeductionBtn").attr("disabled", false);
                    if (data.status == true) {
                        Swal.fire({
                            icon: "success",
                            text: data.message,
                            timer: 3000,
                        });
                        var baseUrl = $('#ajaxCall').val();
                        window.location.href = baseUrl + "/admin/ta-da-request/claim/show/" + tcId;
                    } else {
                        Swal.fire({
                            icon: "warning",
                            text: data.message,
                            timer: 3000,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    $("#gloabal-overlay").hide();
                    Swal.fire({
                        icon: "error",
                        text: error,
                        timer: 3000,
                    });
                },
            });

        });
    </script>
@endsection
