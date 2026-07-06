<!DOCTYPE html>
<html>

<head>
    <title>Salary Slip</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            margin: auto;
            padding: 10px;
            border: 1px solid #000;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .logo {
            width: 80px;
            height: auto;
            margin-right: 15px;
        }

        .company-details {
            flex-grow: 1;
            text-align: center;
        }

        .company-details h2,
        .company-details p {
            margin: 2px 0;
            font-size: 10px;
        }

        .salary-slip {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 8px;
            background-color: #ddd;
            border: 2px solid black;
            width: 50%;
            margin: 10px auto;
            border-radius: 5px;
        }

        .details {
            width: 100%;
            margin-top: 10px;
        }

        .details th,
        .details td {
            padding: 3px;
            text-align: left;
            border: none;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        .footer {
            margin-top: 10px;
            font-size: 10px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
          <table width="100%" style="border-collapse: collapse;">
                <tr>
                    <td style="width: 40%; text-align: left;">
                        <img src="{{ $logoPath }}" alt="Company Logo"
                            style="width: 100px; height: 100px;">
                    </td>
                    <td style="width: 60%; text-align: left; vertical-align: middle;">
                        <h2 style="margin: 0; font-size: 20px;">{{ $employee->b_name }}</h2>
                        <p style="margin: 5px 0; font-size: 12px;">{{ $employee->b_address }},
                            {{ $employee->b_pin_code }}</p>
                        <p style="margin: 5px 0; font-size: 12px;">Email: Info@fixingdots.com</p>
                    </td>
                </tr>
            </table>
        </div>


        <!-- Salary Slip Title -->
        <p class="salary-slip">Salary Slip for {{ date('F', mktime(0, 0, 0, $month, 10)) }} {{ $year }}</p>


{{-- @dd($salaryDetails); --}}
        <table class="details">
            <tr>
                <td><strong>Emp. ID:</strong> {{ $employee->emp_id }}</td>
                <td><strong>Date of Joining:</strong> {{ $employee->emp_date_of_joining }}</td>
            </tr>
            <tr>
                <td><strong>Emp. Name:</strong> {{ $employee->emp_full_name }}</td>
                <td><strong>Month Days:</strong> {{ $salaryDetails[ 'total_days_in_month' ] }}</td>
            </tr>
            <tr>
                <td><strong>Department:</strong> {{ $employee->d_name }}</td>
                <td><strong>Workable Days:</strong>{{ $salaryDetails['total_month_working_days'] }} </td>

            </tr>
            <tr>
                <td><strong>Designation:</strong> {{ $employee->dg_name }}</td>
                 <td><strong>Salaried Days:</strong>{{ $salaryDetails['total_days_worked'] }} </td>
            </tr>
            <tr>
                <td><strong>Branch:</strong> {{ $employee->emp_bank_branch_name }}</td>
                <td><strong>Present Days:</strong>{{ $salaryDetails['present_days'] }} </td>
            </tr>
            <tr>
                <td><strong>Bank Name:</strong> {{ $employee->emp_bank_name }}</td>
                <td><strong>Account No.:</strong> {{ $employee->emp_bank_account_no }}</td>
            </tr>
            <tr>
                <td><strong>IFSC:</strong> {{ $employee->emp_bank_ifsc_code }}</td>
                <td><strong>UAN:</strong> </td>
            </tr>

        </table>

        @php
            // Define a mapping for earnings heads to actual salary values
            $salaryMapping = [
                'Basic' => $employee_salary->es_base_salary ?? 0,
                'House Rent Allowance' => $employee_salary->es_monthly_ctc * 0.15 ?? 0,
                'Dearness Allowance' => $employee_salary->es_monthly_ctc * 0.1 ?? 0,
                'Other Allowance' => $employee_salary->es_rem_allowance ?? 0,
            ];

            // Calculate Remaining Allowance
            $remainingAllowance =
                ($employee_salary->es_monthly_ctc ?? 0) -
                ($salaryMapping['Basic'] +
                    $salaryMapping['House Rent Allowance'] +
                    $salaryMapping['Dearness Allowance'] +
                    $salaryMapping['Other Allowance']);

            // Ensure Remaining Allowance is not negative
            $salaryMapping['Remaining'] = $remainingAllowance > 0 ? $remainingAllowance : 0;

        @endphp

        <table class="table">
            <tr>
                <th colspan="3">Earnings</th>
                <th colspan="3">Deductions</th>
            </tr>
            <tr>
                <th>Head</th>
                <th>Actual</th>
                <th>Amount</th>
                <th>Head</th>
                <th>Employee</th>
                <th>Employer</th>
            </tr>

            @php
                $maxRows = max(count($earnings), count($deductions));
            @endphp

            @for ($i = 0; $i < $maxRows; $i++)
                <tr>
                    <td>{{ $earnings[$i]['head'] ?? '-' }}</td>

                    {{-- Dynamically Fetch Actual Salary Using Mapping --}}
                    <td>
                        @if (isset($earnings[$i]['head']))
                            {{ number_format($salaryMapping[$earnings[$i]['head']] ?? 0, 2) }}
                        @else
                            -
                        @endif
                    </td>

                    <td>{{ $earnings[$i]['amount'] ?? '-' }}</td>

                    <td>{{ $deductions[$i]['head'] ?? '-' }}</td>
                    <td>{{ $deductions[$i]['amount']['employee'] ?? '0.00' }}</td>
                    <td>{{ $deductions[$i]['amount']['employer'] ?? '0.00' }}</td>
                </tr>
            @endfor

            <!-- Totals -->
            <tr>
                <th>Gross Salary</th>
                <th>{{ number_format($employee_salary->es_monthly_gross ?? 0, 2) }}</th>
                <th>{{ $total_earnings }}</th>
                <th>Deductions</th>
                <th colspan="2">{{ $total_deductions }}</th>
            </tr>
            <tr>
                <th>Net Pay:</th>
                <th>-</th> <!-- Empty for consistency -->
                <th>{{ $net_salary }}</th>
                <th></th>
                <th colspan="2"></th>
            </tr>
            <tr>
                <td colspan="6" style="text-align: left; font-weight: bold;">
                    In Words: {{ $net_salary_words }}
                </td>
            </tr>
        </table>



        <!-- Footer Notes -->
        <div class="footer">
            {{-- <p>Prepared by: HR | Checked by: Project Manager | Authorized by: Managing Director</p> --}}
            <p>* This is a computer-generated slip and does not require a signature.</p>
        </div>
    </div>

</body>

</html>
