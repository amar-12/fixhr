<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification: Leave Request Rejected</title>
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
                        <h4 class="card-title">Notification: Leave Request Rejected</h4>
                        <p>Dear {{ $data['data']['receiverName'] }},</p>
                        <p>I hope you are doing well.</p>
                        <p>
                            I am writing to inform you that the leave request submitted by {{ $data['data']['leaveData']->fh_employee->emp_fname }}, scheduled from <strong>{{ Carbon::parse($data['data']['leaveData']->lvr_start_date)->format('d M, Y') }} to {{ Carbon::parse($data['data']['leaveData']->lvr_end_date)->format('d M, Y') }}</strong>, has been reviewed and unfortunately, it has been rejected.
                        </p>
                        <ul>
                            {{-- <li><strong>Leave Request ID:</strong> {{ $data['data']['leaveData']->lvr_unique_id }}</li> --}}
                            <li><strong>Submit Date:</strong> {{ Carbon::parse($data['data']['leaveData']->created_at)->format('d M, Y') }}</li>
                            <li><strong>Leave Type:</strong> {{ optional($data['data']['leaveData']->fh_leave_day_type)->m_name ?? 'N/A' }}</li>
                            <li><strong>Leave Category:</strong> {{ optional($data['data']['leaveData']->fh_leave_cat_type)->m_name ?? 'N/A' }}</li>
                            <li><strong>From Date:</strong> {{ $data['data']['leaveData']->lvr_start_date ?? 'N/A' }}</li>
                            <li><strong>To Date:</strong> {{ $data['data']['leaveData']->lvr_end_date ?? 'N/A' }}</li>
                            <li><strong>Reason for Leave:</strong> {{ $data['data']['leaveData']->lvr_reason ?? 'N/A' }}</li>
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
