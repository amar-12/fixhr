<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Salary Increment Letter</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body,
        p,
        div,
        span,
        h4,
        h5 {
            font-family: 'Times New Roman', Times, serif;
            font-size: 15px;
            text-align: justify;
        }

        .letter-container {
            background: #ffffff;
            padding: 5px;
            max-width: 900px;
            margin: 10px auto;
        }

        .letter-title {
            font-weight: 600;
            text-decoration: underline;
            text-align: center;
        }

        .signature-space {
            margin-top: 40px;
        }

        .footer-note {
            font-size: 12px;
            margin-top: 40px;
            bottom: 0;
            position: fixed;
        }
    </style>
</head>

<body>

    <div class="letter-container">

        <!-- Header -->
        <table width="100%" style="margin-top: -40px !important; margin-bottom: 0px;">
            <tr>
                <!-- Left Image -->
                <td align="left" valign="middle">
                    <img src="{{ public_path('assets/Save.png') }}" alt="Paper Saved" style="max-height:99px;">
                </td>

                <!-- Right Image -->
                <td align="right" valign="middle">
                    <img src="{{ $exit->employee->fh_business->b_logo }}" alt="Company Logo" style="max-height:80px;">
                    {{-- <img src="{{ public_path('uploads\logo\logo.png') }}" alt="Paper Saved" style="max-height:99px;"> --}}
                </td>
            </tr>
        </table>

        <!-- Ref -->
        <div class="row mb-4 mt-4">
            <div class="col-md-6">
                <p><strong>Ref No:</strong> {{ $exit->er_ref_no }}</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p><strong>Date:</strong> {{ now()->format('d/m/Y') }}</p>
            </div>
        </div>

        <!-- Title -->
        <div class="text-center mb-4" style="margin-top:80px !important;">
            <h4 class="letter-title">SALARY REVISION LETTER</h4>
        </div>

        <!-- Body -->
        <div>

            <strong>
                {{ $exit->employee->fh_employee_title->m_name }}
                {{ $exit->employee->emp_full_name }},
            </strong>

            <p>
                We are pleased to inform you that based on your performance and valuable
                contribution to <strong>{{ $exit->employee->fh_business->b_name }}</strong>,
                your salary has been revised.
            </p>

            <p>
                Your designation as
                <strong>{{ $exit->employee->fh_designation->dg_name }}</strong>
                in the
                <strong>{{ $exit->employee->fh_department->d_name }}</strong>
                department remains unchanged.
            </p>

            <p>
                Your revised Annual CTC will be
                <strong>{{ $latest_salary->sm_annual_ctc ?? 'N/A' }}</strong>,
                effective from
                <strong>{{ \Carbon\Carbon::parse($latest_salary->created_at ?? now())->format('d F Y') }}</strong>.
            </p>

            <p>
                The previous Annual CTC was
                <strong>{{ $previous_salary->sm_annual_ctc ?? 'N/A' }}</strong>,
                effective from
                <strong>{{ \Carbon\Carbon::parse($previous_salary->created_at ?? now())->format('d F Y') }}</strong>.
            </p>


            <p>
                We appreciate your dedication and commitment to the organization
                and look forward to your continued contribution and success.
            </p>

            <p>
                Please sign and return a copy of this letter as acknowledgement.
            </p>

        </div>

        <!-- Signature -->
        <!-- Signature -->
        <div class="signature-space">

            <span>For <strong>{{ $exit->employee->fh_business->b_name }}</strong>,</span>
            <div style="height:80px;">
                <img src="{{ $signatures->signature }}" alt="Company Logo" style="max-height:80px;">
            </div>
            <span>HR Manager</span>

        </div>

        <!-- Footer -->
        <div class="footer-note" style="margin-left:90px !important;">
            <p style="text-align: center; font-size:13px;"> Page : 1/1</p>
            <h4 class="fw-bold" style="text-align: center; font-size:13px;">{{ $exit->employee->fh_business->b_name }}
            </h4>
            <p class="mb-0" style="font-size:13px; text-align:center !important;">Registered Office:
                {{ $exit->employee->fh_business->b_address }}</p>
            <p class="mb-0" style="font-size:13px; text-align:center;">Email: {{ $business_data->emp_email }} |
                Phone: +91 {{ $business_data->emp_phone }}</p>
            <p class="mb-0" style="font-size:13px; text-align:center;">
                Website : <a href="https://fixhr.app/" target="_blank">https://fixhr.app/</a>
            </p>
        </div>

    </div>

</body>

</html>
