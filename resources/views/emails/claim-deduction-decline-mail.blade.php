<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request for Reconsideration of Expense Claim Deduction - [Claim ID:
        #{{ isset($data['data']['claimData']->tc_unique_id) ? $data['data']['claimData']->tc_unique_id : 'N/A' }}]
    </title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Request for Reconsideration of Expense Claim Deduction - [Claim ID:
                            #{{ isset($data['data']['claimData']->tc_unique_id) ? $data['data']['claimData']->tc_unique_id : 'N/A' }}]
                        </h4>
                        <p>Dear <strong>{{ $data['data']['receiverName'] ?? 'Unknown' }}</strong>,</p>
                        <p>
                            I hope you are doing well. I am writing regarding the recent approval of my expense claim
                            <strong>(Claim ID: #{{ $data['data']['claimData']->tc_unique_id }})</strong>, which included
                            certain deductions.
                        </p>
                        <p>
                            After carefully reviewing the details, I would like to kindly request a reconsideration of
                            the deductions applied:
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
                                    <td>{{ $data['data']['claimData']->tc_amount }} including DA</td>
                                </tr>
                                <tr>
                                    <td>Approved Amount</td>
                                    <td>{{ $data['data']['claimData']->tc_amount - $data['data']['claimData']->tc_deduction_amount }}
                                        including DA</td>
                                </tr>
                                <tr>
                                    <td>Deductions</td>
                                    <td>{{ $data['data']['claimData']->tc_deduction_amount }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <p>Please proceed with the processing of the approved amount.</p>
                        <p>
                            Thank you for your prompt review and approval of my claim. Should you need any further
                            information or documentation from my end, please let me know.
                        </p>
                        <p>
                            Best regards,<br>
                            {{ $data['data']['claimData']->fh_employee->emp_full_name }}
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
