<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gate Pass Request Approval</title>
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
                        <h4 class="card-title">Gate Pass Request Approval</h4>
                        <p>Dear {{ $data['data']['nextApproverName'] }},</p>
                        <p>I hope you are doing well.</p>
                        <p>
                            I am writing to request your approval for the gate pass request submitted. Below are the details:
                        </p>
                        <ul>
                            <li><strong>Submit Date:</strong> {{ Carbon::parse($data['data']['gatepassData']->created_at)->format('d M, Y') }}</li>
                            <li><strong>Gate Pass Date:</strong> {{ Carbon::parse($data['data']['gatepassData']->gtp_date)->format('d M, Y') }}</li>
                            <li><strong>Requested (Out) Time:</strong> {{ $data['data']['gatepassData']->gtp_out_time ?? 'N/A' }}</li>
                            <li><strong>Return Time:</strong> {{ $data['data']['gatepassData']->gtp_in_time ?? 'N/A' }}</li>
                            <li><strong>Destination:</strong> {{ $data['data']['gatepassData']->gtp_destination ?? 'N/A' }}</li>
                            <li><strong>Reason:</strong> {{ $data['data']['gatepassData']->gtp_reason ?? 'N/A' }}</li>
                        </ul>
                        <p>
                            Your approval is required to process this request. Please review the details and approve at your earliest convenience.
                        </p>
                        <p>Kindly click the button: <a href="{{ $data['url'] }}" class="btn btn-outline-primary btn-sm">Proceed Now</a></p>

                        <p class="mt-4">
                            If you need any additional information or have any questions, feel free to reach out.
                        </p>
                        <p>
                            Thank you for your prompt attention to this matter.
                        </p>
                        <p>
                            Best regards,<br>
                            {{ $data['data']['gatepassData']->fh_employee->emp_full_name }}<br>
                            {{ $data['data']['gatepassData']->fh_employee->fh_designation->dg_name }}<br>
                            {{ $data['data']['gatepassData']->fh_employee->emp_phone }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
