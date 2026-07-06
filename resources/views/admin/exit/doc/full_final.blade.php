<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Full & Final Settlement Statement</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body,
        p,
        div,
        span,
        h4,
        h5 {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
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

        .letter-title {
            font-weight: 600;
            text-decoration: underline;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .section-title {
            font-weight: bold;
            text-align: center;
            background: #f0f0f0;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .footer-note {
            font-size: 12px;
            color: #6c757d;
            margin-top: 40px;
            bottom: 0;
            position: fixed;
        }

        .page-break {
            page-break-after: always;
        }

        .line {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 220px;
        }

        .long-line {
            display: block;
            border-bottom: 1px solid #000;
            margin-top: 6px;
        }
    </style>
</head>

<body>

    <div class="letter-container">

        <!-- Header -->
        <table width="100%" style="margin-top:-40px; border:none; border-collapse:collapse;">
            <tr style="border:none;">
                <td align="left" style="border:none;">
                    <img src="{{ public_path('assets/Save.png') }}" style="max-height:99px;">
                </td>

                <td align="right" style="border:none;">
                    <img src="{{ $exit->employee->fh_business->b_logo }}" style="max-height:80px;">
                    {{-- <img src="{{ public_path('uploads\logo\logo.png') }}" alt="Paper Saved" style="max-height:99px;"> --}}
                </td>
            </tr>
        </table>

        <!-- Ref -->
        <div class="row mt-4 mb-3">
            <div class="col-md-6">
                <p><strong>Ref No:</strong> {{ $exit->er_ref_no }}</p>
            </div>

            <div class="col-md-6 text-md-end">
                <p><strong>Date:</strong> {{ now()->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="text-center mb-3" style="margin-top:80px !important;">
            <h5 class="letter-title">FULL & FINAL SETTLEMENT STATEMENT</h5>
        </div>

        <!-- Employee Details -->

        <style>
            table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
                /* IMPORTANT */
            }

            td,
            th {
                border: 1px solid #000;
                padding: 6px;
                word-wrap: break-word;
            }

            .bold {
                font-weight: bold;
            }

            .right {
                text-align: right;
            }

            /* Equal 4 columns */
            .four-col td,
            .four-col th {
                width: 25%;
            }

            /* Section title center */
            .section-title td {
                text-align: center;
                font-weight: bold;
            }
        </style>

        <table class="four-col">
            <tr>
                <td class="bold">Employee Code</td>
                <td>{{ $exit->employee->emp_code ?? 'N/A' }}</td>

                <td class="bold">Employee Name</td>
                <td>{{ $exit->employee->emp_full_name ?? 'N/A' }}</td>
            </tr>

            <tr>
                <td class="bold">DOB</td>
                <td>
                    {{ \Carbon\Carbon::parse($exit->employee->emp_dob)->format('d F Y') }}
                </td>

                <td class="bold">Department</td>
                <td>{{ $exit->employee->fh_department->d_name ?? 'N/A' }}</td>
            </tr>

            <tr>
                <td class="bold">Designation</td>
                <td>{{ $exit->employee->fh_designation->dg_name ?? 'N/A' }}</td>

                <td class="bold">DOJ</td>
                <td>
                    {{ \Carbon\Carbon::parse($exit->employee->emp_date_of_joining)->format('d F Y') }}
                </td>
            </tr>

            <tr>
                <td class="bold">Separation Submitted</td>
                <td>
                    {{ \Carbon\Carbon::parse($exit->er_resignation_date)->format('d F Y') }}
                </td>

                <td class="bold">Last Working Day</td>
                <td>
                    {{ \Carbon\Carbon::parse($exit->er_last_working_day)->format('d F Y') }}
                </td>
            </tr>

            <tr>
                <td class="bold">Settlement Date</td>
                <td>
                    {{ \Carbon\Carbon::parse($exit->er_last_working_day)->format('d F Y') }}
                </td>

                <td class="bold">Reason of Leaving</td>
                <td>{{ $exit->exitType->m_name ?? 'N/A' }}</td>
            </tr>

            <tr>
                <td class="bold">Notice Period Required</td>
                <td>{{ $exit->employee->emp_notice_period_req_days ?? 0 }}</td>

                <td class="bold">Notice Period Served</td>
                <td>{{ $exit->employee->emp_notice_period_serve_days ?? 0 }}</td>
            </tr>

            <tr>
                <td class="bold">Notice Period Shortfall</td>
                <td>
                    {{ ($exit->employee->emp_notice_period_req_days ?? 0) - ($exit->employee->emp_notice_period_serve_days ?? 0) }}
                </td>

                <td class="bold">Days Worked</td>
                <td class="right">{{ $totalSalariedDays ?? 0 }}</td>
            </tr>

            <tr>
                <td class="bold">Workable Days</td>
                <td class="right">{{ $totalSalariedDays ?? 0 }}</td>

                <td class="bold">Bank Name</td>
                <td>{{ $exit->employee->emp_bank_name ?? 'N/A' }}</td>
            </tr>

            <tr>
                <td class="bold">A/c Number</td>
                <td>{{ $exit->employee->emp_bank_account_no ?? 'N/A' }}</td>

                <td class="bold">IFSC Code</td>
                <td>{{ $exit->employee->emp_bank_ifsc_code ?? 'N/A' }}</td>
            </tr>
        </table>

        <br>




        @php
            $totalPayable =
                ($salary ?? 0) +
                ($leaveEncashment ?? 0) +
                ($incentives ?? 0) +
                ($payble_claims->sum('tc_claimed_amount') ?? 0);

            $totalDeductions = ($totalLoanRecovery ?? 0) + ($adhoc ?? 0);

            $netPay = $totalPayable - $totalDeductions;
        @endphp


        <table class="four-col">
            <tr class="section-title">
                <td colspan="2">Earnings</td>
                <td colspan="2">Deductions</td>
            </tr>

            <tr>
                <th>Particulars</th>
                <th>Amount (Rs.)</th>
                <th>Particulars</th>
                <th>Amount (Rs.)</th>
            </tr>

            <tr>
                <td>Salary</td>
                <td class="right">{{ number_format($salary ?? 0, 2) }}</td>

                <td>Loan / Advance</td>
                <td class="right">{{ number_format($totalLoanRecovery ?? 0, 2) }}</td>
            </tr>

            <tr>
                <td>Leave Encashment</td>
                <td class="right">{{ number_format($leaveEncashment ?? 0, 2) }}</td>

                <td>Adhoc</td>
                <td class="right">{{ number_format($adhoc ?? 0, 2) }}</td>
            </tr>

            <tr>
                <td>Incentives</td>
                <td class="right">{{ number_format($incentives ?? 0, 2) }}</td>
                <td></td>
                <td></td>
            </tr>

            <tr>
                <td>TA / DA</td>
                <td class="right">{{ number_format($payble_claims->sum('tc_claimed_amount') ?? 0, 2) }}</td>
                <td></td>
                <td></td>
            </tr>

            <tr class="bold">
                <td>Total Payable</td>
                <td class="right">{{ number_format($totalPayable, 2) }}</td>

                <td>Total Deductions</td>
                <td class="right">{{ number_format($totalDeductions, 2) }}</td>
            </tr>

            <tr class="bold">
                <td colspan="4">
                    Net Pay : {{ number_format($netPay, 2) }}
                </td>
            </tr>
        </table>

        <!-- Footer -->

        <!-- Footer -->
        <div class="footer-note" style="margin-left:90px !important;">
            <p style="text-align: center; font-size:13px;"> Page : 1/2</p>
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
    <div class="page-break"></div>


    <div class="letter-container">


        <!-- Header -->
        <table width="100%" style="margin-top:-40px; border:none; border-collapse:collapse;">
            <tr style="border:none;">
                <td align="left" style="border:none;">
                    <img src="{{ public_path('assets/Save.png') }}" style="max-height:99px;">
                </td>

                <td align="right" style="border:none;">
                    <img src="{{ $exit->employee->fh_business->b_logo }}" style="max-height:80px;">
                    {{-- <img src="{{ public_path('uploads\logo\logo.png') }}" alt="Paper Saved" style="max-height:99px;"> --}}
                </td>
            </tr>
        </table>


        <h5 class="letter-title center mb-4 mt-4">ACKNOWLEDGEMENT</h5>

        <p>
            I <span class="line">{{ $exit->employee->emp_full_name ?? 'N/A' }}</span>
            S/O Shri <span class="line">{{ $family_details->fd_name ?? 'N/A' }}</span>
            have received a sum of Rs
            <span class="line">{{ number_format($netPay, 2) }}</span>
            towards full & final settlement from
            <strong>{{ $exit->employee->fh_business->b_name }}</strong>.
        </p>

        <span>Permanent Address:</span>

        <div class="long-line">
            {{ $exit->employee->emp_permanent_address ?? '' }}
        </div>

        <div class="long-line">
            {{ $exit->employee->emp_permanent_pin_code ?? '' }}
        </div>

        <div class="text-center mt-5">

            <p>(Signature)</p>

            <p>
                <strong>{{ $exit->employee->emp_full_name ?? '' }}</strong>
            </p>

        </div>


        <!-- Footer -->

        <!-- Footer -->
        <div class="footer-note" style="margin-left:90px !important;">
            <p style="text-align: center; font-size:13px;"> Page : 2/2</p>
            <h4 class="fw-bold" style="text-align: center; font-size:13px;">
                {{ $exit->employee->fh_business->b_name }}
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
