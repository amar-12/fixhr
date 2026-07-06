<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification: Gate Pass Request Rejected</title>
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
                        <h4 class="card-title text-danger">Notification: Gate Pass Request Rejected</h4>
                        <p>Dear {{ $data['data']['receiverName'] }},</p>
                        <p>I hope you are doing well.</p>
                        <p>
                            I am writing to inform you that the gate pass request submitted by <strong>{{ $data['data']['gatepassData']->fh_employee->emp_full_name }}</strong> for the date <strong>{{ Carbon::parse($data['data']['gatepassData']->gtp_date)->format('d M, Y') }}</strong> has been reviewed and unfortunately, it has been rejected.
                        </p>
                        <ul>
                            {{-- <li><strong>Request ID:</strong> {{ $data['data']['gatepassData']->gp_unique_id ?? 'N/A' }}</li> --}}
                            <li><strong>Date:</strong> {{ Carbon::parse($data['data']['gatepassData']->gtp_date)->format('d M, Y') }}</li>
                            <li><strong>Requested (Out) Time:</strong> {{ $data['data']['gatepassData']->gtp_out_time ?? 'N/A' }}</li>
                            <li><strong>Return Time:</strong> {{ $data['data']['gatepassData']->gtp_in_time ?? 'N/A' }}</li>
                            <li><strong>Destination:</strong> {{ $data['data']['gatepassData']->gtp_destination ?? 'N/A' }}</li>
                            <li><strong>Reason:</strong> {{ $data['data']['gatepassData']->gtp_reason ?? 'N/A' }}</li>
                        </ul>

                        <p class="mt-4">
                            If you require any further details or have any questions regarding this decision, please feel free to reach out.
                        </p>
                        <p>
                            Thank you for your attention.
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
