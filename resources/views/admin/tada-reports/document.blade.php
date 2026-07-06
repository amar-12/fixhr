<!DOCTYPE html>
<html>

<head>
    <title>Document</title>
    <style>
        * {
            box-sizing: border-box;
            padding: 0;
            margin: 5px 0;

        }

        body {
            font-family: DejaVu Sans, sans-serif;
        }

        .page-break {
            page-break-after: always;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0 10px 0 10px;
            font-size: 14px;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            /* border: 1px solid black; */

        }

        th,
        td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }

        .header,
        .footer {
            font-weight: bold;
            text-align: center;
        }

        .sub-header {
            text-align: center;
        }

        .signature {
            height: 50px;
        }

        .container {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }

        .appnum {
            display: flex;
            justify-content: flex-end;
        }

        .no-wrap {
            white-space: nowrap;
        }


        @media print {
            body {
                zoom: 100%;
            }

            .no-page-break {
                page-break-inside: avoid;
            }
        }

        @media print {
            body {
                zoom: 100%;
            }
        }
    </style>
</head>

<body>
    @php
        use Carbon\Carbon;
    @endphp

    <div class="container">
        <div class="user-pic" style="padding:0px; marging:0px;">
            <img src="{{ $logoPath }}" alt="Logo" style="width: 100px; height: auto;">
        </div>
        <div class="header">
            <h1>Application for Payment</h1>
        </div>
        <div class="appnum">
            <h4>Application No: &lt;{{$claimData->fh_tada_request_plan->trp_unique_id}}&gt; S.N.</h4>
        </div>
        <table class="table">
            <thead></thead>
            <tbody>
                <tr>
                    <th>Department</th>
                    <td>{{ isset($claimData->fh_employee->fh_department) ? $claimData->fh_employee->fh_department->d_name : '' }}
                    </td>
                    <th>Cost Center</th>
                    <td>{{ isset($claimData->fh_employee) ? $claimData->fh_employee->emp_sap_budget_code : '' }}</td>
                    <th>Date</th>
                    <td>{{ \Carbon\Carbon::parse($claimData->updated_at)->format('Y-m-d') }}</td>
                </tr>
                <tr>
                    <th>Employee Name</th>
                    <td colspan="3">
                        {{ isset($claimData->fh_employee) ? $claimData->fh_employee->emp_full_name : '' }}</td>
                    <th>Emp. Code</th>
                    <td>{{ isset($claimData->fh_employee) ? $claimData->fh_employee->emp_code : '' }}</td>
                </tr>
                <tr>
                    <th>SAP PO No</th>
                    <td></td>
                    <th>Total Agr. Amt.</th>
                    {{-- <th>Total Agreement Amount</th>  --}}
                    <th>{{ ($claimData->tc_amount ?? 0) - (($claimData->fh_tada_request_plan->trp_advance_allowance ?? 0) + ($claimData->tc_deduction_amount ?? 0)) }}
                        INR</th>
                    <th>Paid Before</th>
                    <td></td>
                </tr>
                <tr>
                    <th>Pay Now</th>
                    <th>{{ ($claimData->tc_amount ?? 0) - (($claimData->fh_tada_request_plan->trp_advance_allowance ?? 0) + ($claimData->tc_deduction_amount ?? 0)) }}
                        INR</th>
                    <th>Payment Date</th>
                    <td>{{ \Carbon\Carbon::parse($claimData->updated_at)->format('d-m-Y') }}</td>
                    <th>Against advance</th>
                    <td></td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td colspan="5">
                        <table>
                            <tr>
                                <th>Account Code</th>
                                <th>Invoice No</th>
                                <th>Amount</th>
                                <th>Description</th>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>{{ ($claimData->tc_amount ?? 0) - (($claimData->fh_tada_request_plan->trp_advance_allowance ?? 0) + ($claimData->tc_deduction_amount ?? 0)) }}
                                    INR</td>
                                <td>{{ \Carbon\Carbon::parse($claimData->created_at)->format('M-Y') }} Travel expenses payment
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td></td>
                                <th>Total</th>
                                <th>{{ ($claimData->tc_amount ?? 0) - (($claimData->fh_tada_request_plan->trp_advance_allowance ?? 0) + ($claimData->tc_deduction_amount ?? 0)) }}
                                    INR</th>
                                <td>{{ $capitalizedWords }} only</td>
                            </tr>

                        </table>
                    </td>
                </tr>
                <tr>
                    <th>Applied by</th>
                    <td colspan="5">{{ $claimData->fh_employee->emp_full_name }}</td>
                </tr>
                <tr>
                    <td colspan="6" class="header">Travel Approval Details</td>
                </tr>
                @if (optional($claimData)->fh_tada_request_plan->fh_plan_approval_log)
                    @foreach ($claimData->fh_tada_request_plan->fh_plan_approval_log as $log)
                        <tr>
                            <th>{{ isset($log->fh_status) ? $log->fh_status->m_name : '' }} By</th>
                            <td>{{ isset($log->fh_employee) ? $log->fh_employee->emp_full_name : '' }}</td>
                            <td>{{ \Carbon\Carbon::parse($log->created_at)->format('M-Y H:i') }}</td>
                            <td colspan="3">{{ $log->log_description }}</td>
                        </tr>
                    @endforeach
                @endif
                <tr>
                    <td colspan="6" class="header">Finance & Accounts Department</td>
                </tr>
                @foreach ($claimData->fh_claim_approval_log as $clog)
                    <tr>
                        <th>{{ isset($clog->fh_status) ? $clog->fh_status->m_name : '' }} By</th>
                        <td>{{ isset($clog->fh_employee) ? $clog->fh_employee->emp_full_name : '' }}</td>
                        <td>{{ \Carbon\Carbon::parse($clog->created_at)->format('M-Y H:i') }}</td>
                        <td colspan="3">{{ $clog->log_description }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($claimData->fh_tada_request_plan && $claimData->fh_tada_request_plan->fh_tada_expenses)
        <div class="page-break"></div>
        <div class="container no-page-break">
            <div class="user-pic" style="padding:0px; marging:0px;">
                <img src="{{ $logoPath }}" alt="Logo" style="width: 100px; height: auto;">
            </div>
            <div class="header">
                <h1>TRAVELLING EXPENSES STATEMENT</h1>
            </div>
            <div class="details">
                <p><strong>Name: </strong>{{ optional($claimData->fh_employee)->emp_full_name ?? '' }}</p>
                <p><strong>Employee ID: </strong>{{ optional($claimData->fh_employee)->emp_code ?? '' }}</p>
                <p><strong>Designation: </strong>{{ optional(optional($claimData->fh_employee)->fh_designation)->dg_name ?? '' }}</p>
                <p><strong>Place Visited: </strong>{{ optional($claimData->fh_tada_request_plan)->trp_destination ?? '' }}</p>
            </div>

            <table class="table">
                <tbody>
                    <tr>
                        <th></th>
                        <th>Date of Departure</th>
                        <th>Time</th>
                        <th>Date of Arrival at Destination</th>
                        <th>Time</th>
                    </tr>
                    @foreach ($claimData->fh_tada_request_plan->fh_tada_expenses as $item)
                        @if (
                            $item->te_type_id == 159 &&
                                $item->fh_policy_tada_travel_vehicle()->exists() &&
                                !$item->fh_policy_tada_travel_vehicle->pttv_is_conveyance)
                            <tr>
                                <td>{{ $item->te_from_location . 'To' . $item->te_from_location }} </td>
                                <td> {{ $item->te_from_date ? \Carbon\Carbon::parse($item->te_from_date)->format('d-m-Y') : '' }}
                                </td>
                                <td>{{ (new DateTime($item->te_from_time))->format('h:i A') }} </td>
                                <td>{{ $item->te_to_date ? \Carbon\Carbon::parse($item->te_to_date)->format('d-m-Y') : '--' }}
                                <td>{{ (new DateTime($item->te_to_time))->format('h:i A') }} </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>

            <table class="table" style="margin-top: 10px">
                <thead>
                    <tr>
                        <th rowspan="2">S.No.</th>
                        <th rowspan="2">Particulars</th>
                        <th colspan="2" class="sub-header">Paid by</th>
                        <th rowspan="2">Total</th>
                        <th rowspan="2">Excess expn on actual</th>
                        <th rowspan="2">Remarks</th>
                    </tr>
                    <tr>
                        <th>Company</th>
                        <th>Self</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                        $totalMinutes = 0;
                        $totalDA = 0;
                        $differenceInDays = 0;
                    @endphp
                    @foreach ($claimData->fh_tada_request_plan->fh_tada_expenses->groupBy(['te_type_id', 'te_paid_by']) as $key => $item)
                        @foreach ($item as $key1 => $tadaExpenseItem)
                            @if ($key == 159)
                                @php
                                    $firstItem = $tadaExpenseItem->sortBy('te_from_date')->first();
                                    $lastItem = $tadaExpenseItem->sortByDesc('te_to_date')->first();
                                    if ($firstItem && $lastItem) {
                                        $firstDate = Carbon::parse($firstItem->te_from_date);
                                        $lastDate = Carbon::parse($lastItem->te_to_date);
                                        $differenceInDays = $firstDate->diffInDays($lastDate);
                                    } else {
                                        $differenceInDays = null;
                                    }
                                @endphp
                                @foreach ($tadaExpenseItem as $attributes)
                                    @php
                                        $fromDateTime = $attributes['te_from_date'] . ' ' . $attributes['te_from_time'];
                                        $toDateTime = $attributes['te_to_date'] . ' ' . $attributes['te_to_time'];
                                        $from = new DateTime($fromDateTime);
                                        $to = new DateTime($toDateTime);
                                        $interval = $from->diff($to);
                                        $hours = $interval->days * 24 + $interval->h;
                                        $minutes = $interval->i;
                                        $totalMinutesForItem = $hours * 60 + $minutes;
                                        $totalMinutes += $totalMinutesForItem;
                                        $totalHours = intdiv($totalMinutes, 60);
                                        $remainingMinutes = $totalMinutes % 60;
                                    @endphp
                                @endforeach
                            @endif
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $tadaExpenseItem[0]->fh_expense_type->m_name }}</td>
                                <td>{{ $key1 == 'company' ? $tadaExpenseItem->sum('te_amount') + $tadaExpenseItem->sum('te_taxes') : '' }}
                                </td>
                                <td>{{ Str::ucfirst($tadaExpenseItem[0]->te_paid_by) }}</td>
                                <td>{{ $key1 == 'self' ? $tadaExpenseItem->sum('te_amount') + $tadaExpenseItem->sum('te_taxes') : '' }}
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforeach
                    @endforeach
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ 'DA' }}</td>
                        <td>{{ $claimData->tc_da_amount ?? 0 }}</td>
                        <td>{{ 'Self' }}</td>
                        <td>{{ $totalDA = $claimData->tc_da_amount ?? 0 }}</td>
                        <td></td>
                        <td>
                            {{
                                (isset($firstDate) ? \Carbon\Carbon::parse($firstDate)->format('d-M-Y') : '') .
                                ' To ' .
                                (isset($lastDate) ? \Carbon\Carbon::parse($lastDate)->format('d-M-Y') : '') .
                                ' = ' . $differenceInDays . ' Days'
                            }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>TOTAL</strong></td>
                        <td><strong>
                                @if ($claimData->fh_tada_request_plan && $claimData->fh_tada_request_plan->fh_tada_expenses)
                                    <b>{{ $claimData->fh_tada_request_plan->fh_tada_expenses->where('te_paid_by', 'company')->sum('te_amount') + $claimData->fh_tada_request_plan->fh_tada_expenses->sum('te_taxes') + $totalDA ?? '' }}</b>
                                @endif
                            </strong></td>
                        <td></td>
                        <td><strong>
                                @if ($claimData->fh_tada_request_plan && $claimData->fh_tada_request_plan->fh_tada_expenses)
                                    <b>{{ $claimData->fh_tada_request_plan->fh_tada_expenses->where('te_paid_by', 'self')->sum('te_amount') + $claimData->fh_tada_request_plan->fh_tada_expenses->sum('te_taxes') + $totalDA ?? '' }}</b>
                                @endif
                            </strong></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <h5>*Details of the particulars to be filled on the reserve side.</h5>
            <div class="details">
                <p><strong>Advance taken:</strong> {{ $claimData->fh_tada_request_plan->trp_advance_allowance ?? '' }}
                </p>
                <p><strong>Less Expenses incurred:</strong> 0</p>
                <p><strong>Net Amount Refundable/Payable:</strong>
                    {{ ($claimData->tc_amount ?? 0) - (($claimData->fh_tada_request_plan->trp_advance_allowance ?? 0) + ($claimData->tc_deduction_amount ?? 0)) }}
                </p>
            </div>

            <div class="signature">
                <p>Signature of Employee</p>
            </div>
            <hr style="border-top: dotted 1px;" />
            <p><u>To be filled by A/c Deptt.</u></p>
            <div class="form-row">
                <label>Tour Bill Passed for Rs.<input type="text">(Rupees <input type="text">)</label>
            </div>
            <div class="form-row">
                <label>Amount Received Rs.<input type="text">(Rupees<input type="text">)</label>
            </div>
            <div class="spacer"></div>
            <table style="width: 100%; margin-top: 50px; border-top: 1px solid black; padding-top: 10px;">
                <tr>
                    <td style="text-align: center;">HOD Sign.</td>
                    <td style="text-align: center;">Checked by HR</td>
                    <td style="text-align: center;">A/c Deptt.</td>
                </tr>
            </table>
        </div>
    @endif

    @if (count($expenseData))
        <div class="page-break"></div>

        <div class="container">
            <div class="user-pic" style="padding:0px; marging:0px;">
                <img src="{{ $logoPath }}" alt="Logo" style="width: 100px; height: auto;">
            </div>
            <table class="table">
                <tbody>
                    <tr>
                        <th colspan="8" class="header">Expense Details</th>
                    </tr>
                    <tr>
                        <th colspan="8" class="sub-header">Travelling</th>
                    </tr>
                    <tr>
                        <th rowspan="2">Date</th>
                        <th colspan="2" class="sub-header">Place</th>
                        <th rowspan="2">Mode</th>
                        <th colspan="3" class="sub-header">Paid By</th>
                        <th rowspan="2" class="sub-header">Remarks</th>
                    </tr>
                    <tr>
                        <th>From</th>
                        <th>To</th>
                        <th>Company</th>
                        <th>Self</th>
                        <th>Total</th>
                    </tr>
                    @php $totalTravelExpense = 0; @endphp
                    @foreach ($expenseData as $expenseType => $expenses)
                        @foreach ($expenses as $expense)
                            @if (
                                $expense->te_type_id == 159 &&
                                    $expense->fh_policy_tada_travel_vehicle()->exists() &&
                                    !$expense->fh_policy_tada_travel_vehicle->pttv_is_conveyance)
                                @php $totalTravelExpense += $expense->te_amount; @endphp
                                <tr>
                                    <td>{{ $expense->te_date ? \Carbon\Carbon::parse($expense->te_date)->format('d-m-Y') : '' }}
                                    </td>
                                    <td>{{ $expense->te_from_location }}</td>
                                    <td>{{ $expense->te_to_location }}</td>
                                    <td>{{ $expense->fh_policy_tada_travel_vehicle->fh_vehicle->m_name . '-(' . $expense->fh_policy_tada_travel_mode->fh_travel_mode->m_name . ')' }}
                                    </td>
                                    <td>{{ $expense->te_paid_by == 'company' ? $expense->te_amount : 0 }}</td>
                                    <td>{{ $expense->te_paid_by == 'self' ? $expense->te_amount : 0 }}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <th class="sub-header">Total</th>
                        <th>{{ $totalTravelExpense }}Rs</th>
                        <td></td>
                        <td>Paid by Self</td>
                    </tr>
                    <tr>
                        <th colspan="8" class="header">Lodging</th>
                    </tr>
                    <tr>
                        <th rowspan="2">Date</th>
                        <th rowspan="2" colspan="2" class="sub-header">Hotel Name</th>
                        <th rowspan="2">Bill No.</th>
                        <th colspan="3" class="sub-header">Paid By</th>
                        <th rowspan="2" class="sub-header">Remarks</th>
                    </tr>
                    <tr>
                        <th>Company</th>
                        <th>Self</th>
                        <th>Total</th>
                    </tr>
                    @php $totalLodgingExpense = 0; @endphp
                    @foreach ($expenseData as $expenseType => $expenses)
                        @foreach ($expenses as $expense)
                            @if ($expense->te_type_id == 158)
                                @php $totalLodgingExpense += $expense->te_amount; @endphp
                                <tr>
                                    <td>{{ $expense->te_date ? \Carbon\Carbon::parse($expense->te_date)->format('d-m-Y') : '' }}
                                    </td>
                                    <td colspan="2">{{ $expense->te_hotel_name }}</td>
                                    <td>---</td>
                                    <td>{{ $expense->te_paid_by == 'company' ? $expense->te_amount : 0 }}</td>
                                    <td>{{ $expense->te_paid_by == 'self' ? $expense->te_amount : 0 }}</td>
                                    <td></td>
                                    <td>{{ $expense->te_from_date ? \Carbon\Carbon::parse($expense->te_from_date)->format('d-m-Y') : '--' }}
                                        to
                                        {{ $expense->te_to_date ? \Carbon\Carbon::parse($expense->te_to_date)->format('d-m-Y') : '--' }}
                                        Stay
                                        ({{ $expense->te_from_date && $expense->te_from_date ? \Carbon\Carbon::parse($expense->te_from_date)->diffInDays(\Carbon\Carbon::parse($expense->te_to_date)) : '--' }})
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <th class="sub-header">Total</th>
                        <th>{{ $totalLodgingExpense }}Rs</th>
                        <td></td>
                        <td>Paid by Self</td>
                    </tr>
                    @if(isset($expenseData['Meal']))
                    <tr>
                        <th colspan="8" class="header">Meal</th>
                    </tr>
                    <tr>
                        <th rowspan="2">Date</th>
                        <th rowspan="2" colspan="2" class="sub-header">Particulars</th>
                        <th rowspan="2">Bill No.</th>
                        <th colspan="3" class="sub-header">Paid By</th>
                        <th rowspan="2" class="sub-header">Remarks</th>
                    </tr>
                    <tr>
                        <th>Company</th>
                        <th>Self</th>
                        <th>Total</th>
                    </tr>
                    @php $totalMealExpense = 0; @endphp
                    @foreach ($expenseData as $expenseType => $expenses)
                        @foreach ($expenses as $expense)
                            @if ($expense->te_type_id == 160)
                                @php $totalMealExpense += $expense->te_amount; @endphp
                                <tr>
                                    <td>{{ $expense->te_date ? \Carbon\Carbon::parse($expense->te_date)->format('d-m-Y') : '' }}
                                    </td>
                                    <td colspan="2">{{ $expense->te_hotel_name }}</td>
                                    <td>---</td>
                                    <td>{{ $expense->te_paid_by == 'company' ? $expense->te_amount : 0 }}</td>
                                    <td>{{ $expense->te_paid_by == 'self' ? $expense->te_amount : 0 }}</td>
                                    <td></td>
                                    <td>{{ $expense->te_remarks ? $expense->te_remarks : '--' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <th class="sub-header">Total</th>
                        <th>{{ $totalLodgingExpense }}Rs</th>
                        <td></td>
                        <td>Paid by Self</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="8" class="header">Conveyance</td>
                    </tr>
                    <tr>
                        <th rowspan="2">Date</th>
                        <th colspan="2" class="sub-header">Place Visited</th>
                        <th rowspan="2">Mode</th>
                        <th colspan="3" class="sub-header">Paid By</th>
                        <th rowspan="2" class="sub-header">Remarks</th>
                    </tr>
                    <tr>
                        <th>From</th>
                        <th>To</th>
                        <th>Company</th>
                        <th>Self</th>
                        <th>Total</th>
                    </tr>
                    @php $totalTravelConveyanceExpense = 0; @endphp
                    @foreach ($expenseData as $expenseType => $expenses)
                        @foreach ($expenses as $expense)
                            @if (
                                $expense->te_type_id == 159 &&
                                    $expense->fh_policy_tada_travel_vehicle()->exists() &&
                                    $expense->fh_policy_tada_travel_vehicle->pttv_is_conveyance)
                                @php $totalTravelConveyanceExpense += $expense->te_amount; @endphp
                                <tr>
                                    <td class="no-wrap">
                                        <td>{{ $expense->te_date ? \Carbon\Carbon::parse($expense->te_date)->format('d-m-Y') : '' }}</td>
                                    </td>
                                    <td>{{ $expense->te_from_location }}</td>
                                    <td>{{ $expense->te_to_location }}</td>
                                    <td>{{ $expense->fh_policy_tada_travel_vehicle->fh_vehicle->m_name . ' -(' . $expense->fh_policy_tada_travel_mode->fh_travel_mode->m_name . ')' }}
                                    </td>
                                    <td>{{ $expense->te_paid_by == 'company' ? $expense->te_amount : 0 }}</td>
                                    <td>{{ $expense->te_paid_by == 'self' ? $expense->te_amount : 0 }}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <th class="sub-header">Total</th>
                        <th>{{ $totalTravelConveyanceExpense }}Rs</th>
                        <td></td>
                        <td>Paid by Self</td>
                    </tr>
                    <tr>
                        <th colspan="8" class="sub-header">Other Expenses</th>
                    </tr>
                    <tr>
                        <th rowspan="2">Date</th>
                        <th rowspan="2" colspan="3" class="sub-header">Particulars</th>
                        <th rowspan="2">Bill No.</th>
                        <th colspan="2" class="sub-header">Paid By</th>
                        <th rowspan="2" class="sub-header">Remarks</th>
                    </tr>
                    <tr>
                        <th>Company</th>
                        <th>Self</th>
                    </tr>
                    @php $totalOtherExpense = 0; @endphp
                    @foreach ($expenseData as $expenseType => $expenses)
                        @foreach ($expenses as $expense)
                            @if ($expense->te_type_id == 161)
                                @php $totalOtherExpense += $expense->te_amount; @endphp
                                <tr>
                                    <td class="no-wrap">
                                        {{ $expense->te_date ? \Carbon\Carbon::parse($expense->te_date)->format('d-m-Y') : '--' }}
                                    </td>
                                    <td colspan="3">{{ $expense->te_name }}</td>
                                    <td>---</td>
                                    <td>
                                        @if ($expense->te_paid_by == 'company')
                                            {{ $expense->te_amount }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($expense->te_paid_by == 'self')
                                            {{ $expense->te_amount }}
                                        @endif
                                    </td>
                                    <td>{{ $expense->te_remarks ? $expense->te_remarks : '--' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>

                        <th class="sub-header">Total</th>
                        <td></td>
                        <th>{{ $totalOtherExpense }}Rs</th>
                        <td>Paid by Self</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
</body>

</html>
