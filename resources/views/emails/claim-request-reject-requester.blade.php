<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel expense claim request - Rejected</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    @php
        use Carbon\Carbon;
    @endphp
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Travel expense claim request - Rejected</h4>
                        <p>Dear {{ $data['data']['receiverName'] }},</p>
                        <p>I hope you are doing well.</p>
                        <p>
                            I am writing to inform you that your expense claim with the following details has been reviewed and unfortunately, it has been rejected:
                        </p>
                        <p><b>Claim Details:</b></p>
                        <ul>
                            <li><strong>Claim ID:</strong> {{ $data['data']['claimData']->tc_unique_id }}</li>
                            <li><strong>Claim Ref ID:</strong> {{ $data['data']['claimData']->fh_tada_request_plan->trp_unique_id }}</li>
                            <li><strong>Claim Date:</strong> {{ Carbon::parse($data['data']['claimData']->created_at)->format('d-M-Y') }}
                            </li>
                        </ul>

                        @if(isset($data['data']['claimData']) && $data['data']['claimData']->fh_tada_request_plan && $data['data']['claimData']->fh_tada_request_plan->fh_tada_expenses()->exists())
                        <p>
                            Below is brief summary of expenses,
                        </p>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Particulars</th>
                                    <th>Amount</th>

                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1; @endphp
                                @foreach ($data['data']['claimData']->fh_tada_request_plan->fh_tada_expenses->groupBy('te_type_id') as $key => $tadaExpenseItem)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ $tadaExpenseItem[0]->fh_expense_type->m_name }}</td>
                                        <td>{{ $tadaExpenseItem->sum('te_amount') + $tadaExpenseItem->sum('te_taxes') }}
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2"><strong>Total Expenses</strong></td>
                                    <td colspan="2">
                                        {{ $data['data']['claimData']->fh_tada_request_plan->fh_tada_expenses->sum('te_amount') + $data['data']['claimData']->fh_tada_request_plan->fh_tada_expenses->sum('te_taxes') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        @endif


                        <p class="mt-4">
                            If you require further details or need clarification regarding the rejection, please feel free to reach out.
                        </p>
                        <p>
                            Thank you for your understanding.
                        </p>
                        <p>
                            Best regards,<br>
                            {{ $data['data']['approver']->emp_full_name }}<br>
                            {{ $data['data']['approver']->fh_designation->dg_name }}<br>
                            {{ $data['data']['approver']->emp_phone }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
