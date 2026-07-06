<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">
    <title>Salary Slip</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            overflow-x: auto;
        }

        .container {
            width: 95%;
            max-width: 900px;
            margin: auto;
            padding: 10px;
            border: 1px solid #000;
            overflow-x: auto;
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
            table-layout: fixed;
            word-wrap: break-word;
            border-collapse: collapse;
        }


        .table th,
        .table td {
            border: 1px solid #000 !important;
            padding: 4px;
            text-align: left;
            word-wrap: break-word;
            white-space: normal;
            /* Allows text wrapping */
            overflow: hidden;
            font-size: 10px;
            /* Reduce text size */
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
                        <img src="{{ $logoPath }}" alt="Company Logo" style="width: 100px; height: 100px;">
                    </td>
                    <td style="width: 60%; text-align: left; vertical-align: middle;">
                        <h2 style="margin: 0; font-size: 20px;">{{ $employee->b_name }}</h2>
                        <p style="margin: 5px 0; font-size: 12px;">{{ $employee->b_address }},
                            {{ $employee->b_pin_code }}
                        </p>
                        <p style="margin: 5px 0; font-size: 12px;">Email: Info@fixingdots.com</p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="salary-slip">Salary Slip for {{$payroll_period->pp_name }}</div>

        <table class="details">
            <tr>
                <td><strong>Emp. Code:</strong> {{ $employee->emp_code }}</td>
                <td><strong>Date of Joining:</strong> {{ $employee->emp_date_of_joining }}</td>
            </tr>
            <tr>
                <td><strong>Emp. Name:</strong> {{ $employee->emp_full_name }}</td>
                <td><strong>Month Days:</strong> {{ $processedSalary->ps_total_days_in_month }}</td>
            </tr>
            <tr>
                <td><strong>Department:</strong> {{ $employee->d_name }}</td>
                <td><strong>Salaried Days:</strong> {{ $processedSalary->ps_total_days_worked }}</td>
            </tr>
            <tr>
                <td><strong>Designation:</strong> {{ $employee->dg_name }}</td>
                <td><strong>Present Days:</strong> {{ $processedSalary->ps_present_days }}</td>
            </tr>
            <tr>
                <td><strong>Branch:</strong> {{ $employee->br_name }}</td>
                <td><strong>LWP:</strong> {{ $processedSalary->ps_upl_count }}</td>
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

        <br><br>

        <table class="table">
            @php
                $earnings = App\Models\ProcessedSalaryEarning::where('ps_id', $processedSalary->ps_id)->get();

                $employeeDeductions = App\Models\ProcessedSalaryDeduction::where('ps_id', $processedSalary->ps_id)
                    ->where('ps_d_category', 'employee')
                    ->get();
                $total_earnings = $processedSalary->ps_earnings;
                $total_deductions = $processedSalary->ps_employee_deductions;
                $net_salary = $processedSalary->ps_monthly_net_salary;
                $empEarnings = App\Models\SalaryEmployeeEarnings::where('es_e_emp_id', $processedSalary->ps_emp_id)->get();
            @endphp

            <tr>
                <th colspan="3">Earnings</th>
                <th colspan="2">Deductions</th>
            </tr>

            <tr>
                <th>Head</th>
                <th>Actual</th>
                <th>Amount</th>
                <th>Head</th>
                <th>Employee</th>
            </tr>

            @foreach ($earnings as $earning)
            @php     
                $actualEarning = $empEarnings->firstWhere('es_e_type_id', $earning->ps_earning_type_id) 
                    ?? $empEarnings->firstWhere('es_sa_id', $earning->ps_earning_type_id); 
            @endphp

                <tr>
                    <td>{{ $earning->ps_earning_type }}</td>
                    <td> {{ isset($actualEarning) ? '₹' . number_format($actualEarning->es_e_amount, 2) : '-' }}   </td> 
                            <td>₹ {{ number_format($earning->ps_e_amount, 2) }}</td>
                    @php                                     
                        $employeeDeduction = $employeeDeductions->shift();
                    @endphp
                    <td>{{ $employeeDeduction->ps_deduction_type ?? '-' }}</td>
                    <td>
                                {{ isset($employeeDeduction) && $employeeDeduction->ps_d_amount > 0 ? '₹ ' . number_format($employeeDeduction->ps_d_amount, 2) : '-' }}
                            </td>                    
                        </tr>
            @endforeach

            <tr style="font-weight: bold;">
                <th>Gross Salary</th>
                <th>₹ {{ number_format($processedSalary->ps_monthly_salary ?? 0, 2) }}</th>
                <th>{{ $total_earnings }}</th>
                <th>Deductions</th>
                <th>₹ {{ number_format($total_deductions ?? 0, 2) }}</th>
            </tr>

            <tr style="font-weight: bold;">
                <th>Take Home Salary:</th>
                <th>-</th> 
                <th>₹ {{ number_format($net_salary, 2) }}</th>
                <th></th>
                <th></th>
            </tr>

            <tr>
                <td colspan="5" style="text-align: left; font-weight: bold; padding: 5px;">
                    In Words: {{ $net_salary_words }}
                </td>
            </tr>
        </table>

        <div class="footer">
            <p>* This is a computer-generated slip and does not require a signature.</p>
        </div>
    </div>

</body>

</html>