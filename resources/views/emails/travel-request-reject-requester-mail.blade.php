<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Request - {{$data['data']['planData']->fh_policy_tada_travel_type->fh_travel_type->m_name}} Rejected</title>
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
                        <h4 class="card-title">Tour Request - {{$data['data']['planData']->fh_policy_tada_travel_type->fh_travel_type->m_name}} Rejected</h4>
                        <p>Dear <b>{{ $data['data']['receiverName'] }}</b>,</p>
                        <p>I hope you are doing well.</p>

                        <p>
                            I have reviewed your tour request for travel to <b>{{$data['data']['planData']->trp_destination ?? ''}}</b>, scheduled from <strong>{{Carbon::parse($data['data']['planData']->trp_start_date)->format('d M, Y')}} <span>{{Carbon::parse($data['data']['planData']->trd_start_time)->format('h:i A')}}</span> to {{Carbon::parse($data['data']['planData']->trp_end_date)->format('d M, Y') }} <span>{{Carbon::parse($data['data']['planData']->trd_end_time)->format('h:i A')}}</span></strong>. Unfortunately, I am unable to approve this request at this time.
                        </p>
                        <p>Details of the Request:</p>
                        <ul>
                            <li><strong>Tour Request ID:</strong> {{ $data['data']['planData']->trp_unique_id }}</li>
                            <li><strong>Submit Date:</strong> {{Carbon::parse($data['data']['planData']->created_at)->format('d M, Y')}}</li>
                            <li><strong>Destination:</strong> {{$data['data']['planData']->trp_destination ?? ''}}</li>
                            <li><strong>Purpose of Travel:</strong> {{$data['data']['planData']->fh_travel_purpose->tp_name}}</li>
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
