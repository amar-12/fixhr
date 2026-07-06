<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">
    <title>FixHR Payslip Mail</title>
    <meta name="description" content="Payslip Email Template">
    <style>
        body {
            margin: 0;
            background-color: #f2f3f8;
        }

        table {
            width: 100%;
            background-color: #f2f3f8;
        }

        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            font-family: 'Open Sans', Helvetica, Arial, sans-serif;
        }

        .header {
            text-align: center;
            padding: 30px;
            background-color: #ffffff;
        }

        .header img {
            max-width: 200px;
        }

        .content {
            padding: 30px;
            font-size: 16px;
            line-height: 1.6;
            color: #333;
        }

        .footer {
            text-align: center;
            color: #999;
            font-size: 12px;
            padding: 20px;
            background-color: #ffffff;
        }

        a {
            color: #8d6cd1;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .payslip-link {
            display: inline-block;
            margin-top: 10px;
            font-size: 14px;
            color: #8d6cd1;
        }
    </style>
</head>

<body>
    <table cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center">
                <table class="email-container">
                  <tr>
                    <td class="header">
                        <img src="{{ $logoPath ?? 'http://dev.fixhr.app/assets/logo/logo.png' }}" alt="FixHR Logo" style="max-height: 100px;">
                    </td>
                </tr>
                    <tr>
                        <td class="content">
                            <p>Dear {{ $employee->emp_full_name }},</p>

                            {{-- <p>We hope you are doing well.</p> --}}

                            <p>
                                Please find your payslip for the period:
                                <strong>
                                    {{ \Carbon\Carbon::parse($period->pp_start_date)->format('d M Y') }}
                                    –
                                    {{ \Carbon\Carbon::parse($period->pp_end_date)->format('d M Y') }}
                                </strong>
                             attached to this email.
                            </p>

                            @if($processedSalary->ps_payslip_url)
                                <p>
                                    <a class="payslip-link" href="{{ $processedSalary->ps_payslip_url }}" target="_blank">
                                        View/Download Payslip
                                    </a>
                                </p>
                            @endif

                            <p>If you have any questions or discrepancies, please contact the HR department.</p>

                            <p>Thank you,<br><strong>FixHR Team</strong></p>

                            <p style="font-size: 12px; color: #888;">This is an auto-generated email. Please do not reply.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="footer">
                            <p>Powered by <a href="https://fixingdots.com">Fixing Dots</a></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
