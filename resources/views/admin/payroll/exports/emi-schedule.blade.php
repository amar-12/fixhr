<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loan Repayment Schedule</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        h2, h3 {
            margin: 15px 0 10px;
            padding: 6px 10px;
            background: #29323b;
            color: #fff;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
             border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: center;
            white-space: nowrap;
        }
        th {
            background: #f7f7f7;


        }
        tr:nth-child(even) td {
            background: #fafafa;
        }
        .status-completed {
            color: #27ae60;
            font-weight: bold;
        }
        .status-pending {
            color: #e67e22;
            font-weight: bold;
        }
        .info-table th {
            width: 25%;
        }
        .footer-note {
            font-size: 11px;
            margin-top: 30px;
            text-align: center;
            color: #888;
        }
        /* Header table styling */
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
    </style>
</head>
<body>

    <!-- Company Header -->
    <table class="header-table">
        <tr>
            <td style="width: 30%;">
                <img src="{{ $logoPath }}" alt="Company Logo" style="width: 150px;">
            </td>
            <td style="text-align: right; line-height: 1.4; width: 70%;">
                <strong>{{ $employee->fh_business->b_name }}</strong><br>
                {{ optional($employee->fh_business->fh_admin)->emp_phone ?? '-' }}<br>
                {{ $employee->fh_business->b_address ?? '-' }}
            </td>
        </tr>
    </table>

    <h3>Loan/Advance Details</h3>
    <table class="info-table">
        <tr>
            <th>Employee</th>
            <td>{{ $loan->fh_employee->emp_full_name ?? '---' }}</td>
             <th>Employee Code</th>
            <td>{{ $loan->fh_employee->emp_code ?? '---' }}</td>

        </tr>
         <tr>

            <th>Email</th>
            <td>{{ $loan->fh_employee->emp_email ?? '---' }}</td>
            <th>Department</th>
            <td>{{ $loan->fh_employee->fh_department->dept_name ?? '---' }}</td>
        </tr>
        <tr>
            <th>Loan Start Date</th>
            <td>{{ $loan->lnr_start_date ? $loan->lnr_start_date->format('d-M-Y') : '---' }}</td>
            <th>Loan Unique ID</th>
            <td>{{ $loan->lnr_unique_id ?? '---' }}</td>
        </tr>
         <tr>
            <th>Loan Amount</th>
            <td>₹ {{ number_format($loan->lnr_requested_amount ?? 0, 2) }}</td>
            <th>Interest Rate</th>
            <td>{{ $loan->lnr_rate ?? 0 }}%</td>
        </tr>
        <tr>
            <th>Tenure</th>
            <td>{{ $loan->lnr_installments ?? 0 }} Months</td>
            <th>Approval Status</th>
            <td>
                @if(($loan->lnr_stage_completed ?? 0) == 1)
                    <span class="status-completed">Completed</span>
                @else
                    <span class="status-pending">Active</span>
                @endif
            </td>
        </tr>
    </table>

    <h3>Repayment Schedule</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Due Date</th>
                <th>Opening Balance (₹)</th>
                <th>Principal (₹)</th>
                <th>Interest (₹)</th>
                <th>EMI (₹)</th>
                <th>Balance (₹)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php $openingBalance = $loan->lnr_requested_amount; @endphp

            @forelse($installments as $key => $inst)
               <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($inst->pli_due_date)->format('d-M-Y') }}</td>

                {{-- ✅ Opening Balance --}}
                <td>{{ number_format($openingBalance, 2) }}</td>

                {{-- ✅ Principal = Opening Balance - Remaining Balance --}}
                <td>{{ number_format($openingBalance - $inst->pli_rem_bal, 2) }}</td>

                {{-- ✅ Interest = EMI - Principal --}}
                <td>{{ number_format($inst->pli_amount - ($openingBalance - $inst->pli_rem_bal), 2) }}</td>

                {{-- ✅ EMI Amount --}}
                <td>{{ number_format($inst->pli_amount, 2) }}</td>

                {{-- ✅ Closing / Remaining Balance --}}
                <td>{{ number_format($inst->pli_rem_bal, 2) }}</td>

               {{-- <td>
                    @if(strtolower($inst->pli_status) == 'paid')
                    <span style="color:green;">Paid</span>
                    @else
                    <span style="color:orange;">Pending</span>
                    @endif
                </td> --}}

                <td>
                    @if(strtolower($inst->pli_status) == 'paid')
                    <span style="color:green;" class="status-completed">Paid</span>
                    @elseif(\Carbon\Carbon::parse($inst->pli_due_date)->isPast())
                    <span style="color:rgb(173, 34, 34);" class="status-overdue">Overdue</span>
                    @else
                    <span style="color:rgb(216, 135, 59);" class="status-pending">Pending</span>
                    @endif
                </td>
            </tr>

            @php
                $openingBalance = $inst->pli_rem_bal;
            @endphp
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; color:#888;">No Installments Found</td>
                </tr>
            @endforelse

        </tbody>
    </table>

    <div class="footer-note">
        <p>Generated on {{ $loan->updated_at->format('d M, Y h:i A') }}</p>
        <p>This is a system-generated report and does not require a signature.</p>
    </div>

</body>
</html>
