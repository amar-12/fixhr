@php
use Illuminate\Support\Carbon;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Details</title>
    <style>
        body {
            font-size: 10px; /* Adjust font size to fit more content */
            margin: 0;      /* Remove default margins */
            padding: 0;     /* Remove default padding */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            /* margin-bottom: 20px; */
        }
        th, td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }
        .header {
            font-weight: bold;
            text-align: center;
        }
        .sub-header {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .container {
            padding: 20px;
            max-width: 80%;
            margin: 0 auto;
        }
        .right-text {
            text-align: right;
        }
        @media print {
            body {
                zoom: 100%; /* Scale down the content slightly */
            }
        }
    </style>
</head>
<body>
    <div class="container">

        <table>
            <tr>
                <td colspan="8" class="header">Expense Details</td>
            </tr>
            <tr>
                <td colspan="8" class="sub-header">Travelling</td>
            </tr>
            <tr>
                <th rowspan="2">Date</th>
                <th colspan="2" class="sub-header">Place</th>
                <th rowspan="2">Mode</th>
                <th colspan="3" class="sub-header">Paid By</th>
                <th rowspan="2" class="sub-header">Remarks</th>
            </tr>
            <tr>
                <th style="width: 18%">From</th>
                <th>To</th>
                <th style="width: 18%">Company</th>
                <th>Self</th>
                <th>Total</th>
            </tr>
            @php $totalTravelExpense = 0; @endphp
            @foreach ($expenseData as $expenseType => $expenses)
                @foreach ($expenses as $expense)
                    @if ($expense->te_type_id == 159 && !$expense->fh_policy_tada_travel_vehicle->pttv_is_conveyance)
                    @php $totalTravelExpense += $expense->te_amount; @endphp
                        <tr>
                            <td>{{$expense->te_date ? $expense->te_date->format('d-m-y') : ''}}</td>
                            <td>{{$expense->te_from_location}}</td>
                            <td>{{ $expense->te_to_location}}</td>
                            <td>{{$expense->fh_policy_tada_travel_vehicle->fh_vehicle->m_name.' -('.$expense->fh_policy_tada_travel_mode->fh_travel_mode->m_name.')'}}</td>
                            <td>@if($expense->te_paid_by == 'company') {{$expense->te_amount}} @endif</td>
                            <td>@if($expense->te_paid_by == 'self') {{$expense->te_amount}} @endif</td>
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
                <td class="sub-header">Total</td>
                <td>{{$totalTravelExpense}}</td>
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
                        <td>{{ $expense->te_date ? Carbon::parse($expense->te_date)->format('d M, Y') : '--' }}</td>
                        <td colspan="2">{{$expense->te_hotel_name}}</td>
                        <td>---</td>
                        <td>@if($expense->te_paid_by == 'company') {{$expense->te_amount}} @endif</td>
                        <td>@if($expense->te_paid_by == 'self') {{$expense->te_amount}} @endif</td>
                        <td></td>
                        <td>{{ $expense->te_from_date ? Carbon::parse($expense->te_from_date)->format('d M, Y') : '--' }} to {{$expense->te_to_date ? Carbon::parse($expense->te_to_date)->format('d M, Y') : '--' }} Stay ({{$expense->te_from_date && $expense->te_from_date ? Carbon::parse($expense->te_from_date)->diffInDays(Carbon::parse($expense->te_to_date)) : '--';}})</td>
                    </tr>
                   @endif
               @endforeach
            @endforeach
            <tr>
                <td></td>
                <td colspan="2"></td>
                <td class="sub-header">Total</td>
                <td></td>
                <td>{{$totalLodgingExpense}}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="8" class="header">Conveyance</td>
            </tr>
            <tr>
                <th rowspan="2">Date</th>
                <th colspan="2" class="sub-header">Place Visited</th>
                <th rowspan="2">Mode</th>
                <th rowspan="2">Purpose</th>
                <th colspan="2" class="sub-header">Paid By</th>
                <th rowspan="2" class="sub-header">Remarks</th>
            </tr>
            <tr>
                <th>From</th>
                <th>To</th>
                <th>Company</th>
                <th>Self</th>
            </tr>
            @php $totalTravelConveyanceExpense = 0; @endphp
            @foreach ($expenseData as $expenseType => $expenses)
                @foreach ($expenses as $expense)
                    @if ($expense->te_type_id == 159 && $expense->fh_policy_tada_travel_vehicle->pttv_is_conveyance)
                    @php $totalTravelConveyanceExpense += $expense->te_amount; @endphp
                        <tr>
                            <td>{{$expense->te_date ? $expense->te_date->format('d-m-y') : ''}}</td>
                            <td>{{$expense->te_from_location}}</td>
                            <td>{{ $expense->te_to_location}}</td>
                            <td>{{$expense->fh_policy_tada_travel_vehicle->fh_vehicle->m_name.' -('.$expense->fh_policy_tada_travel_mode->fh_travel_mode->m_name.')'}}</td>
                            <td>@if($expense->te_paid_by == 'company') {{$expense->te_amount}} @endif</td>
                            <td>@if($expense->te_paid_by == 'self') {{$expense->te_amount}} @endif</td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endif
                @endforeach
            @endforeach
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="sub-header">Total</td>
                <td></td>
                <td>{{$totalTravelConveyanceExpense}}</td>
                <td>Paid by Self</td>
            </tr>


            <tr>
                <td colspan="8" class="sub-header">Other Expenses</td>
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
                            <td>{{ $expense->te_date ? Carbon::parse($expense->te_date)->format('d M, Y') : '--' }}</td>
                            <td colspan="3">{{ $expense->te_remarks ? $expense->te_remarks : '--' }}</td>
                            <td>---</td>
                            <td>@if($expense->te_paid_by == 'company') {{$expense->te_amount}} @endif</td>
                            <td>@if($expense->te_paid_by == 'self') {{$expense->te_amount}} @endif</td>
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

                <td class="sub-header">Total</td>
                <td></td>
                <td>{{$totalOtherExpense}}</td>
                <td>Paid by Self</td>
            </tr>
        </table>
    </div>
</body>
</html>
