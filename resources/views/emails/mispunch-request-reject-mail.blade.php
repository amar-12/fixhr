<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification: Mispunch Request Rejected</title>
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
                        <h4 class="card-title text-danger">Notification: Mispunch Request Rejected</h4>
                        <p>Dear {{ $data['data']['receiverName'] }},</p>
                        <p>I hope you are doing well.</p>
                        <p>
                            I am writing to inform you that the mispunch correction request submitted by <strong>{{ $data['data']['mispunchData']->fh_employee->emp_full_name }}</strong> for the date <strong>{{ Carbon::parse($data['data']['mispunchData']->ae_date)->format('d M, Y') }}</strong> has been reviewed and unfortunately, it has been rejected.
                        </p>
                        <ul>
                            {{-- <li><strong>Request ID:</strong> {{ $data['data']['mispunchData']->mp_unique_id ?? 'N/A' }}</li> --}}
                            <li><strong>Date:</strong> {{ Carbon::parse($data['data']['mispunchData']->ae_date)->format('d M, Y') }}</li>
                            <li><strong>In Time:</strong> {{ $data['data']['mispunchData']->ae_in_time ?? 'N/A' }}</li>
                            <li><strong>Out Time:</strong> {{ $data['data']['mispunchData']->ae_out_time ?? 'N/A' }}</li>
                            <li><strong>Working Hours:</strong> {{ $data['data']['mispunchData']->ae_total_working ?? 'N/A' }}</li>
                            <li><strong>Reason:</strong> {{ $data['data']['mispunchData']->ae_reason_id ?? 'N/A' }}</li>
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
