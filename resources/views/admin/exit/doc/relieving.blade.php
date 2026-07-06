<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Relieving Letter</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CDN -->
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
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .company-logo {
            max-height: 80px;
        }

        .letter-title {
            font-weight: 600;
            text-decoration: underline;
            text-align: center;
        }

        .signature-space {
            margin-top: 80px;
            text-align: justify;
        }

        .d-flex.justify-content-between img {
            display: inline-block;
        }

        .text-justify {
            text-align: justify !important;
        }

        .footer-note {
            font-size: 12px;
            color: #6c757d;
            margin-top: 40px;
            bottom: 0;
            position: fixed;
        }
    </style>
</head>

<body>

    <div class="letter-container">
        <!-- Company Header -->
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

        <!-- Reference Details -->
        <div class="row mb-4 mt-4">
            <div class="col-md-6">
                <p><strong>Ref No:</strong> {{ $exit->er_ref_no }}</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p><strong>Date:</strong> {{ now()->format('d/m/Y') }}</p>
            </div>
        </div>

        <!-- Subject -->
        <div class="text-center mb-4" style="margin-top:80px !important;">
            <h4 class="letter-title">RELIEVING LETTER</h4>
        </div>

        <!-- Letter Body -->
        <div>
            <strong>{{ $exit->employee->fh_employee_title->m_name }} {{ $exit->employee->emp_full_name }},</strong>
            <p class="text-justify">
                This is to formally acknowledge that you have been relieved from the services of
                <strong>{{ $exit->employee->fh_business->b_name }}</strong>
                with effect from the close of business hours on
                <strong>{{ \Carbon\Carbon::parse($exit->er_last_working_day)->format('d F Y') }}</strong>.
            </p>

            <p class="text-justify">
                You had submitted your resignation on
                <strong>{{ \Carbon\Carbon::parse($exit->er_resignation_date)->format('d F Y') }}</strong>, which was
                duly accepted by the management.
            </p>

            <p class="text-justify">
                During your tenure from
                <strong>{{ \Carbon\Carbon::parse($exit->employee->emp_date_of_joining)->format('d F Y') }} </strong> to
                <strong>{{ \Carbon\Carbon::parse($exit->er_last_working_day)->format('d F Y') }}</strong>,
                you worked as a <strong>{{ $exit->employee->fh_designation->dg_name ?? '' }}</strong> in the
                <strong>{{ $exit->employee->fh_department->d_name ?? '' }}</strong>.
            </p>

            <p class="text-justify">
                We confirm that you have completed all required handover formalities and clearance
                procedures as per company policies.
            </p>

            <p class="text-justify">
                We appreciate your contributions to the organization and wish you success in your future endeavors.
            </p>
        </div>

        <!-- Signature Section -->
        <!-- Signature -->
        <div class="signature-space">

            <span>For <strong>{{ $exit->employee->fh_business->b_name }}</strong>,</span>
            <div style="height:80px;">
                <img src="{{ $signatures->signature }}" alt="Signature Not Found !" style="max-height:80px;">
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
            <p class="mb-0" style="font-size:13px; text-align:center;">Email: {{ $business_data->emp_email }} | Phone: +91 {{ $business_data->emp_phone }}</p>
            <p class="mb-0" style="font-size:13px; text-align:center;">
                Website : <a href="https://fixhr.app/" target="_blank">https://fixhr.app/</a>
            </p>
        </div>
    </div>

</body>

</html>
