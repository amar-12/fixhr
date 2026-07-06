<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .payslip-container {
            max-width: 800px;
            margin: auto;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            color: #007bff;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
            color: #555;
        }
        .details, .breakdown {
            width: 100%;
            border-collapse: collapse;
        }
        .details td, .breakdown td, .breakdown th {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .details tr:nth-child(even), .breakdown tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        <div class="header">
            <h2>{{ optional($employee->fh_business)->b_name }}</h2>
            <p><strong>Payslip for the Month of {{ $month }}, {{ $year }}</strong></p>
        </div>
        <div class="section">
            <div class="section-title">Employee Details</div>
            <table class="details">
                <tr>
                    <td><strong>Name:</strong> {{ $employee->emp_full_name }}</td>
                    <td><strong>Employee ID:</strong> {{ $employee->emp_code }}</td>
                </tr>
                <tr>
                    <td><strong>Department:</strong> {{ optional($employee->fh_department)->d_name }}</td>
                    <td><strong>Designation:</strong> {{ optional($employee->fh_designation)->dg_name }}</td>
                </tr>
            </table>
        </div>
        <div class="section">
            <div class="section-title">Salary Breakdown</div>
            <table class="breakdown">
                <thead>
                    <tr>
                        <th>Earnings</th>
                        <th>Amount</th>
                        <th>Deductions</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Gross Salary</td>
                        <td>{{ $monthlyGross }}</td>
                        <td>Deduction</td>
                        <td>{{ $deductions }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-right">Net Salary</th>
                        <th colspan="2">{{ $monthlyNetSalary }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</body>
</html>
