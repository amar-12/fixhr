@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travelling Expenses Statement</title>
    <style>
        .container {
            font-family: Arial, sans-serif;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }

        .header,
        .footer {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            /* margin-bottom: 20px; */
        }

        th,
        td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }

        .signature {
            display: flex;
            justify-content: flex-end;
            /* align-items: center; */
        }

        .signature div {
            width: 48%;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .form-row label {
            flex: 1;
            margin-right: 10px;
        }

        .form-row input {
            flex: 2;
            margin-right: 10px;
            border: none;
            border-bottom: 1px solid black;
            outline: none;
            padding: 0 5px;
        }

        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            border-top: 1px solid black;
            padding-top: 10px;
        }

        .signature-row div {
            flex: 1;
            text-align: space-between;
        }

        .signature-row div:not(:last-child) {
            margin-right: 10px;
            /* Add some spacing between columns if needed */
        }

        .spacer {
            margin-bottom: 50px;
        }

        @media print {
            body {
                zoom: 100%;
                /* Scale down the content slightly */
            }
        }

        .sub-header {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>TRAVELLING EXPENSES STATEMENT</h1>
        </div>

        <div class="details">
            <p><strong>Name:
                </strong>{{ $claimData->fh_employee->emp_fname ?? (' ' . ' ' . $claimData->fh_employee->emp_mname ?? (' ' . $claimData->fh_employee->emp_lname ?? ' ')) }}
            </p>
            <p><strong>Employee ID: </strong>{{ $claimData->fh_employee->emp_code ?? '' }}</p>
            <p><strong>Designation: </strong>{{ $claimData->fh_employee->fh_designation->dg_name ?? '' }}</p>
            <p><strong>Place Visited: </strong>{{ $claimData->fh_tada_request_plan->trp_destination ?? '' }}</p>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th></th>
                    <th>Date of Departure</th>
                    <th>Time</th>
                    <th>Date of Arrival at Destination</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @if ($claimData->fh_tada_request_plan && $claimData->fh_tada_request_plan->fh_tada_expenses)
                    @foreach ($claimData->fh_tada_request_plan->fh_tada_expenses as $item)
                        @if ($item->te_type_id == 159 && !$item->fh_policy_tada_travel_vehicle->pttv_is_conveyance)
                            <tr>
                                <td>{{ $item->te_from_location . 'To' . $item->te_from_location }} </td>
                                <td> {{ $item->te_from_date ? Carbon::parse($item->te_from_date)->format('d-m-Y') : '' }}
                                </td>
                                <td>{{ (new DateTime($item->te_from_time))->format('h:i A') }} </td>
                                <td>{{ $item->te_to_date ? Carbon::parse($item->te_to_date)->format('d-m-Y') : '--' }}
                                </td>
                                <td>{{ (new DateTime($item->te_to_time))->format('h:i A') }} </td>

                            </tr>
                        @endif
                    @endforeach
                @endif
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
                @endphp
                @foreach ($claimData->fh_tada_request_plan->fh_tada_expenses->groupBy('te_type_id') as $key => $tadaExpenseItem)
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
                                // Combine the dates and times
                                $fromDateTime = $attributes['te_from_date'] . ' ' . $attributes['te_from_time'];
                                $toDateTime = $attributes['te_to_date'] . ' ' . $attributes['te_to_time'];

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
                    @endif
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ $tadaExpenseItem[0]->fh_expense_type->m_name }}</td>
                        <td>{{ $tadaExpenseItem->sum('te_amount') + $tadaExpenseItem->sum('te_taxes') }}
                        </td>
                        <td>{{ Str::ucfirst($tadaExpenseItem[0]->te_paid_by) }}</td>
                        <td>{{ $tadaExpenseItem->sum('te_amount') + $tadaExpenseItem->sum('te_taxes') }}
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>{{ 'DA' }}</td>
                    <td>{{ $daEligibility * $differenceInDays }}</td>
                    <td>{{ 'Self' }}</td>
                    <td>{{ $totalDA = $daEligibility * $differenceInDays }}</td>
                    <td></td>
                    <td>{{ $firstDate->format('d-M-Y') . ' To ' . $lastDate->format('d-M-Y') . ' = ' . $differenceInDays . ' Days' }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><strong>TOTAL</strong></td>
                    <td><strong>
                            @if ($claimData->fh_tada_request_plan && $claimData->fh_tada_request_plan->fh_tada_expenses)
                                <b>{{ $claimData->fh_tada_request_plan->fh_tada_expenses->sum('te_amount') + $claimData->fh_tada_request_plan->fh_tada_expenses->sum('te_taxes') + $totalDA ?? '' }}</b>
                            @endif
                        </strong></td>
                    <td></td>
                    <td><strong>
                            @if ($claimData->fh_tada_request_plan && $claimData->fh_tada_request_plan->fh_tada_expenses)
                                <b>{{ $claimData->fh_tada_request_plan->fh_tada_expenses->sum('te_amount') + $claimData->fh_tada_request_plan->fh_tada_expenses->sum('te_taxes') + $totalDA ?? '' }}</b>
                            @endif
                        </strong></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <h5>*Details of the particulars to be filled on the reserve side.</h5>
        <div class="details">
            <p><strong>Advance taken:</strong> -{{ $claimData->fh_tada_request_plan->trp_advance_allowance ?? '' }} </p>
            <p><strong>Less Expenses incurred:</strong> 0</p>
            <p><strong>Net Amount Refundable/Payable:</strong>
                {{ ($claimData->fh_tada_request_plan->fh_tada_expenses->sum('te_amount') + $claimData->fh_tada_request_plan->fh_tada_expenses->sum('te_taxes') + $totalDA ?? '0') - $claimData->fh_tada_request_plan->trp_advance_allowance }}
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
</body>

</html>
