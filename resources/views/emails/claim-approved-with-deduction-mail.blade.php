<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$data['subject']}}</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{$data['subject']}}</h4>
                        <p>Dear <strong>{{ $data['data']['receiverName'] }}</strong>,</p>
                        <p>
                            We are writing to inform you that your recent expense claim 
                            (<strong>Claim REF ID: #{{$data['data']['claimData']->fh_tada_request_plan->trp_unique_id}}</strong>) has been reviewed and approved. 
                            However, please note that the approval includes the following deductions:
                        </p>
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Description</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Claimed Amount</td>
                                    <td>{{$data['data']['claimData']->tc_amount}} including DA</td>
                                </tr>
                                <tr>
                                    <td>Approved Amount</td>
                                    <td>{{$data['data']['claimData']->tc_amount - $data['data']['claimData']->tc_deduction_amount}} including DA</td>
                                </tr>
                                <tr>
                                    <td>Deductions</td>
                                    <td>{{$data['data']['claimData']->tc_deduction_amount}}</td>
                                </tr>
                            </tbody>
                        </table>

                        <p>
                            The deductions were applied due to 
                            <strong>{{$data['data']['deduction_remark']}}</strong>.
                        </p>
                        

                        <p class="mt-4">
                            if you have any questions or require further clarification regarding the deductions, please do not hesitate to reach out 
                        </p>
                        <p>
                            Thank you for your understanding and cooperation.
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
