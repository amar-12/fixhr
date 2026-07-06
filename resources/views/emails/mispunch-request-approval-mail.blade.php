<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mispunch Request Approval</title>
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
                        <h4 class="card-title">Mispunch Request Approval</h4>
                        <p>Dear {{ $data['data']['nextApproverName'] }},</p>
                        <p>I hope you are doing well.</p>
                        <p>
                            I am writing to request your approval for the mispunch correction request submitted. Below are the details:
                        </p>
                        <ul>
                            {{-- <li><strong>Mispunch Request ID:</strong> {{ $data['data']['mispunchData']->mp_unique_id }}</li> --}}
                            <li><strong>Submit Date:</strong> {{ Carbon::parse($data['data']['mispunchData']->created_at)->format('d M, Y') }}</li>
                            <li><strong>Date of Mispunch:</strong> {{ Carbon::parse($data['data']['mispunchData']->ae_date)->format('d M, Y') }}</li>
                            <li><strong>In Time:</strong> {{ $data['data']['mispunchData']->ae_in_time ?? 'N/A' }}</li>
                            <li><strong>Out Time:</strong> {{ $data['data']['mispunchData']->ae_out_time ?? 'N/A' }}</li>
                            <li><strong>Total Working Hours:</strong> {{ $data['data']['mispunchData']->ae_total_working ?? 'N/A' }}</li>
                            <li><strong>Reason for Correction:</strong> {{ $data['data']['mispunchData']->ae_reason_id ?? 'N/A' }}</li>
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
                            {{ $data['data']['mispunchData']->fh_employee->emp_full_name }}<br>
                            {{ $data['data']['mispunchData']->fh_employee->fh_designation->dg_name }}<br>
                            {{ $data['data']['mispunchData']->fh_employee->emp_phone }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
