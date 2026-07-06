<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Request Approval</title>
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
                        <h4 class="card-title">Tour Request Approval - {{$data['data']['planData']->fh_policy_tada_travel_type->fh_travel_type->m_name}}</h4>
                        <p>Dear {{ $data['data']['nextApproverName'] }},</p>
                        <p>I hope you are doing well.</p>
                        <p>
                            I am writing to request your approval for my upcoming Tour plan. Below are the details:
                        </p>
                        <ul>
                            <li><strong>Tour Request ID:</strong> {{ $data['data']['planData']->trp_unique_id }}</li>
                            <li><strong>Submit Date:</strong> {{Carbon::parse($data['data']['planData']->created_at)->format('d M, Y')}}</li>
                            <li><strong>Travel Dates:</strong> {{Carbon::parse($data['data']['planData']->trp_start_date)->format('d M, Y')}} <span>{{Carbon::parse($data['data']['planData']->trd_start_time)->format('h:i A')}}</span> to {{Carbon::parse($data['data']['planData']->trp_end_date)->format('d M, Y') }} <span>{{Carbon::parse($data['data']['planData']->trd_end_time)->format('h:i A')}}</span></li>
                            <li><strong>Destination:</strong> {{$data['data']['planData']->trp_destination ?? ''}}</li>
                            <li><strong>Purpose of Travel:</strong> {{$data['data']['planData']->fh_travel_purpose->tp_name}}</li>
                        </ul>
                        <p>
                            Your approval is required to proceed with the tour arrangements and to process. Please review the details and approve at your earliest convenience.
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
                            {{ $data['data']['planData']->fh_employee->emp_full_name }}<br>
                            {{ $data['data']['planData']->fh_employee->fh_designation->dg_name }}<br>
                            {{ $data['data']['planData']->fh_employee->emp_phone }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
