<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FORM NO. 16</title>
    <style>
        @page {
            margin: 10mm 8mm;
            size: A4 portrait;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9.5pt;
            line-height: 1.15;
            color: #000;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 2px 0;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: middle;
            text-align: left;
        }
        th {
            background-color: #ffffcc;
            font-weight: bold;
            text-align: center;
            font-size: 9pt;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .total { background-color: #ffffcc; font-weight: bold; }
        .underline {
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 100px;
        }
        .long-underline {
            border-bottom: 1px dotted #000;
            display: block;
            width: 100%;
            min-height: 45px;
        }
        .name-address {
            min-height: 65px;
            vertical-align: top;
        }
        .page-break { page-break-before: always; }
        .note {
            font-size: 8.5pt;
            margin-top: 12px;
            line-height: 1.35;
        }
        .verification td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        .verification .signature {
            border: none;
            text-align: right;
            vertical-align: top;
        }
        .part-title {
            font-weight: bold;
            font-size: 10.5pt;
            text-align: center;
            margin: 10px 0 5px;
        }
        .footnote {
            font-size: 8pt;
            margin-top: 6px;
            text-align: left;
        }
    </style>
</head>
<body>

<!-- PART A Header -->
<div class="center" style="font-size: 11pt; font-weight: bold; margin-bottom: 4px;">
    ¹[FORM NO. 16]
</div>
<div class="center" style="font-size: 9pt;">
    [See rule 31(1)(a)]
</div>
<div class="part-title">PART A</div>
<p class="center" style="margin: 8px 0 12px; font-size: 9pt;">
    Certificate under section 203 of the Income-tax Act, 1961 for tax deducted at source on salary paid to an employee under section 192 or pension or interest income of specified senior citizen under section 194P.
</p>

<table>
    <tr>
        <th style="width:25%;">Certificate No.</th>
        <td style="width:25%;">{{ $certificateNo ?? '________________' }}</td>
        <th style="width:25%;">Last updated on</th>
        <td style="width:25%;">{{ $currentDate ?? 'dd/mm/yyyy' }}</td>
    </tr>
    <tr>
        <th colspan="2">Name and address of the Employer/Specified Bank</th>
        <th colspan="2">Name and address of the Employee/ Specified senior citizen</th>
    </tr>
    <tr>
        <td colspan="2" class="name-address">
            <span class="long-underline">{{ $company->name ?? '' }}<br>{{ $company->address ?? '' }}</span>
        </td>
        <td colspan="2" class="name-address">
            <span class="long-underline">{{ $employee->name ?? '' }}<br>{{ $employee->address ?? '' }}</span>
        </td>
    </tr>
    <tr>
        <th style="width:25%;">PAN of the Deductor</th>
        <th style="width:25%;">TAN of the Deductor</th>
        <th style="width:25%;">PAN of the Employee/specified senior citizen</th>
        <th style="width:25%;">Employee Reference No./ Pension Payment order No. provided by the Employer (If available)</th>
    </tr>
    <tr>
        <td>{{ $company->pan ?? '' }}</td>
        <td>{{ $company->tan ?? '' }}</td>
        <td>{{ $employee->pan ?? '' }}</td>
        <td>{{ $employee->ref_no ?? '' }}</td>
    </tr>
    <tr>
        <th>CIT (TDS)</th>
        <th colspan="3">Assessment Year</th>
    </tr>
    <tr>
        <td><span class="underline">{{ $company->cit_address ?? '' }}</span></td>
        <td colspan="3">{{ $assessmentYear ?? '____ - ____' }}</td>
    </tr>
    <tr>
        <th colspan="2">Address</th>
        <th>City</th>
        <th>Pin Code</th>
    </tr>
    <tr>
        <td colspan="2"><span class="underline">{{ $company->address ?? '' }}</span></td>
        <td><span class="underline">{{ $company->city ?? '' }}</span></td>
        <td><span class="underline">{{ $company->pin ?? '' }}</span></td>
    </tr>
</table>

<!-- Summary of amount paid/credited and tax deducted -->
<table>
    <tr><th colspan="5" class="section-title">Summary of amount paid/credited and tax deducted at source thereon in respect of the employee</th></tr>
    <tr>
        <th style="width:10%;">Quarter(s)</th>
        <th style="width:40%;">Receipt Numbers of original quarterly statement of TDS under sub-section (3) of section 200</th>
        <th style="width:20%;">Amount paid/credited</th>
        <th style="width:15%;">Amount of tax deducted (Rs.)</th>
        <th style="width:15%;">Amount of tax deposited/remitted (Rs.)</th>
    </tr>
    @foreach($quarters ?? [] as $q => $data)
    <tr>
        <td class="center">{{ $q }}</td>
        <td class="center">{{ $data['receipt_no'] ?? '' }}</td>
        <td class="right">{{ number_format($data['paid'] ?? 0, 2) }}</td>
        <td class="right">{{ number_format($data['tds_deducted'] ?? 0, 2) }}</td>
        <td class="right">{{ number_format($data['tds_deposited'] ?? 0, 2) }}</td>
    </tr>
    @endforeach
    <tr class="total">
        <td colspan="5" class="right">Total (Rs.) {{ number_format($totals['gross'] ?? 0, 2) }}</td>
    </tr>
</table>

<!-- Book Adjustment -->
<table>
    <tr>
        <th colspan="6" class="section-title">
            I. DETAILS OF TAX DEDUCTED AND DEPOSITED IN THE CENTRAL GOVERNMENT ACCOUNT THROUGH BOOK ADJUSTMENT
        </th>
    </tr>
    <tr>
        <td colspan="6" class="sub-note">
            (The deductor to provide payment wise details of tax deducted and deposited with respect to the deductee)
        </td>
    </tr>
    <tr>
        <th style="width:5%;">Sl. No.</th>
        <th style="width:18%;">Tax deposited in respect of the deductee (Rs.)</th>
        <th colspan="4">Book Identification Number (BIN)</th>
    </tr>
    <tr>
        <th></th>
        <th></th>
        <th style="width:19%;">Receipt numbers of Form No. 24G</th>
        <th style="width:19%;">DDO serial number in Form No. 24G</th>
        <th style="width:19%;">Date of Transfer voucher (dd/mm/yyyy)</th>
        <th style="width:19%;">Status of Matching with Form No.24G</th>
    </tr>
    <tr>
        <td colspan="6" class="center">-</td>
    </tr>
    <tr class="total">
        <td colspan="6" class="right">Total (Rs.) 0.00</td>
    </tr>
</table>

<!-- Challan Section -->
<table>
    <tr>
        <th colspan="6" class="section-title">
            II. DETAILS OF TAX DEDUCTED AND DEPOSITED IN THE CENTRAL GOVERNMENT ACCOUNT THROUGH CHALLAN
        </th>
    </tr>
    <tr>
        <td colspan="6" class="sub-note">
            (The deductor to provide payment wise details of tax deducted and deposited with respect to the deductee)
        </td>
    </tr>
    <tr>
        <th rowspan="2" style="width:5%;">Sl. No.</th>
        <th rowspan="2" style="width:18%;">Tax deposited in respect of the deductee (Rs.)</th>
        <th colspan="4">Challan Identification Number (CIN)</th>
    </tr>
    <tr>
        <th style="width:19%;">BSR Code of the Bank Branch</th>
        <th style="width:19%;">Date on which tax deposited (dd/mm/yyyy)</th>
        <th style="width:19%;">Challan Serial Number</th>
        <th style="width:19%;">Status of matching with OLTAS</th>
    </tr>
    @foreach($payrollHistory ?? [] as $index => $item)
        @if(!empty($item['challan']))
        <tr>
            <td class="center">{{ $index + 1 }}</td>
            <td class="right">{{ number_format($item['tds'] ?? 0, 2) }}</td>
            <td class="center">{{ $item['challan']['bsr'] ?? '-' }}</td>
            <td class="center">{{ $item['challan']['date'] ?? '-' }}</td>
            <td class="center">{{ $item['challan']['serial'] ?? '-' }}</td>
            <td class="center">Matched</td>
        </tr>
        @endif
    @endforeach
    <tr class="total">
        <td colspan="6" class="right">Total (Rs.) {{ number_format($totalTds ?? 0, 2) }}</td>
    </tr>
</table>

<!-- Verification - PART A -->
<table class="verification-table">
    <tr>
        <th colspan="3" class="section-title">Verification</th>
    </tr>
    <tr>
        <td colspan="3">
            I, <span class="underline">{{ $signatoryName ?? '..............................................' }}</span>,
            son/daughter of <span class="underline">{{ $signatoryFather ?? '............................' }}</span>,
            working in the capacity of <span class="underline">{{ $signatoryDesignation ?? '............................' }}</span>
            (designation) do hereby certify that a sum of Rs.
            <span class="underline">{{ number_format($totalTds ?? 0, 2) }}</span>
            [Rs. <span class="underline">{{ $totalTdsWords ?? '......................................................' }}</span> (in words)]
            has been deducted and deposited to the credit of the Central Government.
            I further certify that the information given above is true, complete and correct
            and is based on the books of account, documents, TDS statements, TDS deposited
            and other available records.
        </td>
    </tr>
    <tr>
        <td style="width:15%;">Place</td>
        <td style="width:35%;"><span class="underline">{{ $company->city ?? 'Gurugram' }}</span></td>
        <td rowspan="3" class="signature">
            (Signature of person responsible for deduction of tax)
        </td>
    </tr>
    <tr>
        <td>Date</td>
        <td><span class="underline">{{ $currentDate ?? '________________' }}</span></td>
    </tr>
    <tr>
        <td>Designation:</td>
        <td><span class="underline">{{ $signatoryDesignation ?? '................................' }}</span></td>
    </tr>
    <tr>
        <td>Full Name:</td>
        <td colspan="2"><span class="underline">{{ $signatoryName ?? '................................' }}</span></td>
    </tr>
</table>

<div class="page-break"></div>

<!-- PART B (Annexure-I) - exact text from your message -->
<div class="part-title">[PART B (Annexure-I)]</div>
<p class="center" style="margin-bottom: 8px; font-size:9pt;">
    In relation to employees for tax deduction under section 192
</p>
<p class="center" style="font-weight: bold; margin-bottom: 6px; font-size:9pt;">
    Details of salary paid and any other income and tax deducted
</p>

<table>
    <tr>
        <td style="width:5%;">A</td>
        <td>Whether opting out of taxation u/s 115BAC(1A)?</td>
        <td class="right">[YES/NO]</td>
    </tr>
    <tr>
        <td>1.</td>
        <td>Gross Salary</td>
        <td></td>
    </tr>
    <tr>
        <td>(a)</td>
        <td>Salary as per provisions contained in section 17(1)</td>
        <td class="right">Rs. {{ number_format($totals['gross'] ?? 0, 2) }}</td>
    </tr>
    <tr>
        <td>(b)</td>
        <td>Value of perquisites under section 17(2) (as per Form No. 12BA, wherever applicable)</td>
        <td class="right">Rs. ...</td>
    </tr>
    <tr>
        <td>(c)</td>
        <td>Profits in lieu of salary under section 17(3) (as per Form No. 12BA, wherever applicable)</td>
        <td class="right">Rs. ...</td>
    </tr>
    <tr>
        <td>(d)</td>
        <td>Total</td>
        <td class="right">Rs. {{ number_format($totals['gross'] ?? 0, 2) }}</td>
    </tr>
    <tr>
        <td>(e)</td>
        <td>Reported total amount of salary received from other employer(s)</td>
        <td class="right">Rs. ...</td>
    </tr>
    <tr>
        <td>2.</td>
        <td>Less: Allowances to the extent exempt under section 10</td>
        <td></td>
    </tr>
    <tr>
        <td>(a)</td>
        <td>Travel concession or assistance under section 10(5)</td>
        <td class="right">Rs. ...</td>
    </tr>
    <tr>
        <td>(b)</td>
        <td>Death-cum-retirement gratuity under section 10(10)</td>
        <td class="right">Rs. ...</td>
    </tr>
    <tr>
        <td>(c)</td>
        <td>Commuted value of pension under section 10(10A)</td>
        <td class="right">Rs. ...</td>
    </tr>
    <tr>
        <td>(d)</td>
        <td>Cash equivalent of leave salary encashment under section 10(10AA)</td>
        <td class="right">Rs. ...</td>
    </tr>
    <tr>
        <td>(e)</td>
        <td>House rent allowance under section 10(13A)</td>
        <td class="right">Rs. ...</td>
    </tr>
    <tr>
        <td>(f)</td>
        <td>Other special allowances under section 10(14)</td>
        <td class="right">Rs. ...</td>
    </tr>
    <tr>
        <td>(g)</td>
        <td>Amount of any other exemption under section 10</td>
        <td class="right">Rs. ...</td>
    </tr>
    <tr>
        <td>(h)</td>
        <td>Total amount of any other exemption under section 10</td>
        <td class="right">Rs. ...</td>
    </tr>
    <tr>
        <td>(i)</td>
        <td>Total amount of exemption claimed under section 10 [2(a)+2(b)+2(c)+2(d)+2(e)+2(f)+2(h)]</td>
        <td class="right">Rs. ...</td>
    </tr>
    <tr>
        <td>3.</td>
        <td>Total amount of salary received from current employer [1(d)-2(i)]</td>
        <td class="right">Rs. ...</td>
    </tr>
    <!-- Add remaining PART B rows here if needed -->
</table>

<!-- Verification - PART B -->
<table class="verification-table">
    <tr>
        <th colspan="3" class="section-title">Verification</th>
    </tr>
    <tr>
        <td colspan="3">
            I, <span class="underline">{{ $signatoryName ?? $company->name }}</span>,
            son/daughter of <span class="underline">{{ $signatoryFather ?? '' }}</span>,
            working in the capacity of <span class="underline">{{ $signatoryDesignation ?? 'Director' }}</span>
            (designation) do hereby certify that the information given above is true, complete and correct
            and is based on the books of account, documents, TDS statements, and other available records.
        </td>
    </tr>
    <tr>
        <td style="width:15%;">Place</td>
        <td style="width:35%;"><span class="underline">{{ $company->city ?? 'Gurgaon' }}</span></td>
        <td rowspan="3" class="signature">
            (Signature of person responsible for deduction of tax)
        </td>
    </tr>
    <tr>
        <td>Date</td>
        <td><span class="underline">{{ $currentDate }}</span></td>
    </tr>
    <tr>
        <td>Designation:</td>
        <td><span class="underline">{{ $signatoryDesignation ?? 'Director' }}</span></td>
    </tr>
    <tr>
        <td>Full Name:</td>
        <td colspan="2"><span class="underline">{{ $signatoryName ?? $company->name }}</span></td>
    </tr>
</table>

<div class="page-break"></div>

<!-- PART B (Annexure-I) -->
<div class="part-title">PART B (Annexure-I)</div>
<p class="center" style="margin-bottom: 12px; font-size:9pt;">
    In relation to employees for tax deduction under section 192
</p>
<p class="center" style="font-weight: bold; margin-bottom: 8px; font-size:9pt;">
    Details of salary paid and any other income and tax deducted
</p>

<table>
    <tr>
        <td style="width:5%;">A</td>
        <td>Whether opting out of taxation u/s 115BAC(1A)?</td>
        <td class="right">[{{ $employee->regime == 'New' ? 'YES' : 'NO' }}]</td>
    </tr>

    <tr>
        <td>1.</td>
        <td>Gross Salary</td>
        <td></td>
    </tr>
    <tr>
        <td>(a)</td>
        <td>Salary as per provisions contained in section 17(1)</td>
        <td class="right">Rs. {{ number_format($totals['gross'] ?? 0, 2) }}</td>
    </tr>
    <tr>
        <td>(b)</td>
        <td>Value of perquisites under section 17(2) (as per Form No. 12BA, wherever applicable)</td>
        <td class="right">Rs. 0.00</td>
    </tr>
    <tr>
        <td>(c)</td>
        <td>Profits in lieu of salary under section 17(3) (as per Form No. 12BA, wherever applicable)</td>
        <td class="right">Rs. 0.00</td>
    </tr>
    <tr>
        <td>(d)</td>
        <td>Total</td>
        <td class="right">Rs. {{ number_format($totals['gross'] ?? 0, 2) }}</td>
    </tr>
    <tr>
        <td>(e)</td>
        <td>Reported total amount of salary received from other employer(s)</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>2.</td>
        <td>Less: Allowances to the extent exempt under section 10</td>
        <td></td>
    </tr>
    <tr>
        <td>(a)</td>
        <td>Travel concession or assistance under section 10(5)</td>
        <td class="right">Rs. {{ number_format($taxComputation['lta'] ?? 0, 2) }}</td>
    </tr>
    <tr>
        <td>(b)</td>
        <td>Death-cum-retirement gratuity under section 10(10)</td>
        <td class="right">Rs. 0.00</td>
    </tr>
    <tr>
        <td>(c)</td>
        <td>Commuted value of pension under section 10(10A)</td>
        <td class="right">Rs. 0.00</td>
    </tr>
    <tr>
        <td>(d)</td>
        <td>Cash equivalent of leave salary encashment under section 10(10AA)</td>
        <td class="right">Rs. 0.00</td>
    </tr>
    <tr>
        <td>(e)</td>
        <td>House rent allowance under section 10(13A)</td>
        <td class="right">Rs. {{ number_format($taxComputation['hraExempt'] ?? 0, 2) }}</td>
    </tr>
    <tr>
        <td>(f)</td>
        <td>Other special allowances under section 10(14)</td>
        <td class="right">Rs. 0.00</td>
    </tr>
    <tr>
        <td>(g)</td>
        <td>Amount of any other exemption under section 10</td>
        <td class="right">Rs. 0.00</td>
    </tr>
    <tr>
        <td>(h)</td>
        <td>Total amount of any other exemption under section 10</td>
        <td class="right">Rs. 0.00</td>
    </tr>
    <tr>
        <td>(i)</td>
        <td>Total amount of exemption claimed under section 10 [2(a)+2(b)+2(c)+2(d)+2(e)+2(f)+2(h)]</td>
        <td class="right">Rs. {{ number_format(($taxComputation['hraExempt'] ?? 0) + ($taxComputation['lta'] ?? 0), 2) }}</td>
    </tr>

    <tr>
        <td>3.</td>
        <td>Total amount of salary received from current employer [1(d)-2(i)]</td>
        <td class="right">Rs. {{ number_format(($totals['gross'] ?? 0) - ($taxComputation['hraExempt'] ?? 0) - ($taxComputation['lta'] ?? 0), 2) }}</td>
    </tr>

    <tr>
        <td>4.</td>
        <td>Less: Deductions under section 16</td>
        <td></td>
    </tr>
    <tr>
        <td>(a)</td>
        <td>Standard deduction under section 16(ia)</td>
        <td class="right">Rs. {{ number_format($taxComputation['stdDeduction'] ?? 0, 2) }}</td>
    </tr>
    <tr>
        <td>(b)</td>
        <td>Entertainment allowance under section 16(ii)</td>
        <td class="right">Rs. 0.00</td>
    </tr>
    <tr>
        <td>(c)</td>
        <td>Tax on employment under section 16(iii)</td>
        <td class="right">Rs. {{ number_format($totals['pt'] ?? 0, 2) }}</td>
    </tr>
    <tr>
        <td>5.</td>
        <td>Total amount of deductions under section 16 [4(a)+4(b)+4(c)]</td>
        <td class="right">Rs. {{ number_format(($taxComputation['stdDeduction'] ?? 0) + ($totals['pt'] ?? 0), 2) }}</td>
    </tr>

    <tr>
        <td>6.</td>
        <td>Income chargeable under the head "Salaries" [(3+1(e)-5)]</td>
        <td class="right">Rs. {{ number_format($taxComputation['taxable'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>7.</td>
        <td>Add: Any other income reported by the employee under section 192(2B)</td>
        <td></td>
    </tr>
    <tr>
        <td>(a)</td>
        <td>Income (or admissible loss) from house property reported by employee offered for TDS</td>
        <td class="right">Rs. 0.00</td>
    </tr>
    <tr>
        <td>(b)</td>
        <td>Income under the head "Income from other sources" offered for TDS</td>
        <td class="right">Rs. 0.00</td>
    </tr>
    <tr>
        <td>8.</td>
        <td>Total amount of other income reported by the employee [7(a)+7(b)]</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>9.</td>
        <td>Gross total income (6+8)</td>
        <td class="right">Rs. {{ number_format($taxComputation['taxable'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>10.</td>
        <td>Deductions under Chapter VI-A</td>
        <td></td>
    </tr>
    <tr>
        <th></th>
        <th>Gross Amount</th>
        <th>Qualifying Amount</th>
        <th>Deductible Amount</th>
    </tr>

    <tr>
        <td>(a)</td>
        <td>Deduction in respect of life insurance premia, contribution to provident fund etc. under section 80C</td>
        <td class="right">Rs. {{ number_format(($totals['pf'] ?? 0) * 12, 2) }}</td>
        <td class="right">Rs. {{ number_format(($totals['pf'] ?? 0) * 12, 2) }}</td>
        <td class="right">Rs. {{ number_format(min(($totals['pf'] ?? 0) * 12, 150000), 2) }}</td>
    </tr>

    <tr>
        <td>(b)</td>
        <td>Deduction in respect of contribution to certain pension funds under section 80CCC</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>(c)</td>
        <td>Deduction in respect of contribution by taxpayer to pension scheme under section 80CCD(1)</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>(d)</td>
        <td>Total deduction under section 80C, 80CCC and 80CCD(1) [10(a)+10(b)+10(c)]</td>
        <td class="right">Rs. {{ number_format(($totals['pf'] ?? 0) * 12, 2) }}</td>
        <td class="right">Rs. {{ number_format(($totals['pf'] ?? 0) * 12, 2) }}</td>
        <td class="right">Rs. {{ number_format(min(($totals['pf'] ?? 0) * 12, 150000), 2) }}</td>
    </tr>

    <tr>
        <td>(e)</td>
        <td>Deductions in respect of amount paid/deposited to notified pension scheme under section 80CCD(1B)</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>(f)</td>
        <td>Deduction in respect of contribution by Employer to pension scheme under section 80CCD(2)</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>(g)</td>
        <td>Deduction in respect of health insurance premia under section 80D</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>(h)</td>
        <td>Deduction in respect of interest on loan taken for higher education under section 80E</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>(i)</td>
        <td>Deduction in respect of contribution by the employee to Agnipath Scheme under section 80CCH</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>(j)</td>
        <td>Deduction in respect of contribution by the Central Government to Agnipath Scheme under section 80CCH</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>(k)</td>
        <td>Total deduction in respect of donations to certain funds, charitable institutions, etc. under section 80G</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>(l)</td>
        <td>Deduction in respect of interest on deposits in savings account under section 80TTA</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>(m)</td>
        <td>Amount deductible under any other provision(s) of Chapter VI-A</td>
        <td class="right">Rs. {{ number_format(($taxComputation['totalVIA'] ?? 0) - (($totals['pf'] ?? 0) * 12), 2) }}</td>
        <td class="right">Rs. {{ number_format(($taxComputation['totalVIA'] ?? 0) - (($totals['pf'] ?? 0) * 12), 2) }}</td>
        <td class="right">Rs. {{ number_format(($taxComputation['totalVIA'] ?? 0) - (($totals['pf'] ?? 0) * 12), 2) }}</td>
    </tr>

    <tr>
        <td>11.</td>
        <td>Aggregate of deductible amount under Chapter VI-A [10(d)+10(e)+10(f)+10(g)+10(h)+10(i)+10(j)+10(k)+10(l)+10(m)]</td>
        <td class="right">Rs. {{ number_format($taxComputation['totalVIA'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>12.</td>
        <td>Total taxable income (9-11)</td>
        <td class="right">Rs. {{ number_format(($taxComputation['taxable'] ?? 0) - ($taxComputation['totalVIA'] ?? 0), 2) }}</td>
    </tr>

    <tr>
        <td>13.</td>
        <td>Tax on total income</td>
        <td class="right">Rs. {{ number_format($taxComputation['tax'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>14.</td>
        <td>Rebate under section 87A, if applicable</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>15.</td>
        <td>Surcharge, wherever applicable</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>16.</td>
        <td>Health and education cess @ 4%</td>
        <td class="right">Rs. {{ number_format($taxComputation['cess'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>17.</td>
        <td>Tax payable (13+15+16-14)</td>
        <td class="right">Rs. {{ number_format($taxComputation['totalTax'] ?? 0, 2) }}</td>
    </tr>

    <tr>
        <td>18.</td>
        <td>Less: Relief under section 89 (attach details)</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>19.</td>
        <td>Less: Tax deducted at source as per Form No. 12BAA submitted under provisions of section 192(2B)</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>20.</td>
        <td>Less: Tax collected at source as per Form No. 12BAA submitted under provisions of section 192(2B)</td>
        <td class="right">Rs. 0.00</td>
    </tr>

    <tr>
        <td>21.</td>
        <td>Net tax payable (17-18-19-20)</td>
        <td class="right">Rs. {{ number_format($taxComputation['totalTax'] ?? 0, 2) }}</td>
    </tr>
</table>

<!-- Verification - PART B -->
<table class="verification-table">
    <tr>
        <th colspan="3" class="section-title">Verification</th>
    </tr>
    <tr>
        <td colspan="3">
            I, <span class="underline">{{ $signatoryName ?? $company->name }}</span>,
            son/daughter of <span class="underline">{{ $signatoryFather ?? '' }}</span>,
            working in the capacity of <span class="underline">{{ $signatoryDesignation ?? 'Director' }}</span>
            (designation) do hereby certify that the information given above is true, complete and correct
            and is based on the books of account, documents, TDS statements, and other available records.
        </td>
    </tr>
    <tr>
        <td style="width:15%;">Place</td>
        <td style="width:35%;"><span class="underline">{{ $company->city ?? 'Gurgaon' }}</span></td>
        <td rowspan="3" class="signature">
            (Signature of person responsible for deduction of tax)
        </td>
    </tr>
    <tr>
        <td>Date</td>
        <td><span class="underline">{{ $currentDate }}</span></td>
    </tr>
    <tr>
        <td>Designation:</td>
        <td><span class="underline">{{ $signatoryDesignation ?? 'Director' }}</span></td>
    </tr>
    <tr>
        <td>Full Name:</td>
        <td colspan="2"><span class="underline">{{ $signatoryName ?? $company->name }}</span></td>
    </tr>
</table>
<!-- Annexure II and Notes (keep as per previous version or extend if needed) -->

</body>
</html>
