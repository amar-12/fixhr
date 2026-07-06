<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mispunch Request - Rejected</title>
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
                        <h4 class="card-title text-danger">Mispunch Request - Rejected</h4>
                        <p>Dear <b>{{ $data['data']['receiverName'] }}</b>,</p>
                        <p>I hope you are doing well.</p>

                        <p>
                            I have reviewed your mispunch correction request for <b>{{Carbon::parse($data['data']['mispunchData']->ae_date)->format('d M, Y')}}</b>. Unfortunately, I am unable to approve this request at this time.
                        </p>

                        <p><strong>Details of the Request:</strong></p>
                        <ul>
                            {{-- <li><strong>Request ID:</strong> {{ $data['data']['mispunchData']->mp_unique_id ?? 'N/A' }}</li> --}}
                            <li><strong>Date:</strong> {{ Carbon::parse($data['data']['mispunchData']->ae_date)->format('d M, Y') }}</li>
                            <li><strong>In Time:</strong> {{ $data['data']['mispunchData']->ae_in_time ?? 'N/A' }}</li>
                            <li><strong>Out Time:</strong> {{ $data['data']['mispunchData']->ae_out_time ?? 'N/A' }}</li>
                            <li><strong>Working Hours:</strong> {{ $data['data']['mispunchData']->ae_total_working ?? 'N/A' }}</li>
                            <li><strong>Reason:</strong> {{ $data['data']['mispunchData']->ae_reason_id ?? 'N/A' }}</li>
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

