<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request - Rejected</title>
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
                        <h4 class="card-title">Leave Request - Rejected</h4>
                        <p>Dear <b>{{ $data['data']['receiverName'] }}</b>,</p>
                        <p>I hope you are doing well.</p>

                        <p>
                            I have reviewed your leave request scheduled from <strong>{{ Carbon::parse($data['data']['leaveData']->lvr_start_date)->format('d M, Y') }} to {{ Carbon::parse($data['data']['leaveData']->lvr_end_date)->format('d M, Y') }}</strong>. Unfortunately, I am unable to approve this request at this time.
                        </p>
                        <p>Details of the Request:</p>
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
                            If you have any questions or need further clarification regarding the decision, please feel free to reach out.
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
