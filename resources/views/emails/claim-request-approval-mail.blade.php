<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$data['subject']}}</title>
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
                        <h4 class="card-title">{{$data['subject']}}</h4>
                        <p>Dear {{ $data['data']['nextApproverName'] }},</p>
                        <p>I hope you are doing well.</p>
                        <p>
                            I am writing to request your approval for my expense claims. Below are the details:
                        </p>
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

                        <p>
                            Your approval is required to proceed with expense claims. Please review the details and
                            approve at your earliest convenience.
                        </p>
                        <p>Kindly click the buttton: <a href="{{$data['url']}}" class="btn btn-outline-primary btn-sm">Proceed Now</a></p>

                        <p class="mt-4">
                            If you need any additional information or have any questions, feel free to reach out.
                        </p>
                        <p>
                            Thank you for your prompt attention to this matter.
                        </p>
                        <p>
                            Best regards,<br>
                            {{ $data['data']['claimData']->fh_employee->emp_full_name }}<br>
                            {{ $data['data']['claimData']->fh_employee->fh_designation->dg_name }}<br>
                            {{ $data['data']['claimData']->fh_employee->emp_phone }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
