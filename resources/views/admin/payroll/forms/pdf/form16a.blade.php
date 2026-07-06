<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FORM NO. 16A</title>
    <style>
        @page {
            margin: 12mm 10mm;
            size: A4 portrait;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9pt;
            line-height: 1.15;
            color: #000;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px 6px;
            vertical-align: middle;
            text-align: left;
        }
        th {
            background-color: #e6e6e6;
            font-weight: bold;
            text-align: center;
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }
        .title-th {
            background-color: #e6e6e6;
            font-size: 9.5pt;
            font-weight: bold;
            text-align: left;
            padding: 4px 8px;
        }
        .sub-th {
            background-color: #e6e6e6;
            font-size: 9pt;
            font-style: italic;
            text-align: center;
            padding: 3px 6px;
        }
        .center { text-align: center; }
        .right  { text-align: right; }
        .total  { background-color: #e6e6e6; font-weight: bold; }
        .underline {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 140px;
        }
        .sign-block td {
            border: none;
            padding: 3px 0;
        }
        .page-break { page-break-before: always; }
        .note { font-size: 9pt; margin-top: 12px; line-height: 1.3; }
        .muted { color: #444; }
        .page-marker {
            text-align: center;
            margin-top: 6px;
            font-size: 9pt;
        }
    </style>
</head>
<body>

<!-- Main Form Table -->
<table class="form16a-main" style="margin-top:2px;">
    <colgroup>
        <col style="width:25%;">
        <col style="width:25%;">
        <col style="width:25%;">
        <col style="width:25%;">
    </colgroup>
    <tr>
        <th colspan="4" class="title-th" style="font-size:12pt; text-align:center;">
            FORM NO. 16A
        </th>
    </tr>
    <tr>
        <td colspan="4" class="center" style="font-size:9.5pt; padding:2px 6px;">[See rule 31(1)(b)]</td>
    </tr>
    <tr>
        <th colspan="4" class="title-th" style="text-align:center;">
            Certificate under section 203 of the Income-tax Act, 1961 for tax deducted at source
        </th>
    </tr>
    <tr>
        <th style="width:25%;">Certificate No.</th>
        <td style="width:25%;">{{ $certificateNo ?? '________________' }}</td>
        <th style="width:25%;">Last updated on</th>
        <td style="width:25%;">{{ $currentDate ?? 'dd/mm/yyyy' }}</td>
    </tr>
    <tr>
        <th colspan="2">Name and address of the Deductor</th>
        <th colspan="2">Name and address of the Deductee</th>
    </tr>
    <tr>
        <td colspan="2">{{ $company->name ?? '' }}<br>{{ $company->address ?? '' }}</td>
        <td colspan="2">{{ $employee->name ?? '' }}<br>{{ $employee->address ?? '' }}</td>
    </tr>
    <tr>
        <th>Permanent Account Number or Aadhaar Number of the Deductor</th>
        <th>TAN of the Deductor</th>
        <th colspan="2">Permanent Account Number or Aadhaar Number of the Deductee</th>
    </tr>
    <tr>
        <td>{{ $company->pan ?? '________________________' }}</td>
        <td>{{ $company->tan ?? '________________' }}</td>
        <td colspan="2">{{ $employee->pan ?? '________________________' }}</td>
    </tr>
    <tr>
        <th>CIT (TDS)</th>
        <th>Assessment Year</th>
        <th colspan="2">Period</th>
    </tr>
    <tr>
        <td>{{ $company->cit_address ?? '' }}</td>
        <td>{{ $assessmentYear ?? '____ - ____' }}</td>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:700;">Address ………………………………………………………</td>
        <td style="font-weight:700;">From</td>
        <td style="font-weight:700;">To</td>
    </tr>
    <tr>
        <td colspan="2">………………………………………………………</td>
        <td>{{ $fromDate ?? '________' }}</td>
        <td>{{ $toDate ?? '________' }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:700;">City…………………</td>
        <td colspan="2" style="font-weight:700;">Pin code………………</td>
    </tr>
    <tr>
        <td colspan="2">{{ $company->city ?? '' }}</td>
        <td colspan="2">{{ $company->pin ?? '' }}</td>
    </tr>
</table>

<!-- Summary of payment -->
<table>
    <tr><th colspan="5" class="title-th">Summary of payment</th></tr>
    <tr>
        <th style="width:6%;">Sl. No.</th>
        <th style="width:14%;">Month</th>
        <th style="width:18%;">Amount paid/credited</th>
        <th style="width:14%;">Nature of payment</th>
        <th style="width:34%;">Deductee Reference No. provided by the Deductor (if any)</th>
        <th style="width:18%;">Date of payment/credit (dd/mm/yyyy)</th>
    </tr>
    @foreach($payrollHistory ?? [] as $index => $item)
    <tr>
        <td class="center">{{ $index + 1 }}</td>
        <td class="center">{{ $item['month'] ?? '' }}</td>
        <td class="right">{{ number_format($item['gross'] ?? 0, 2) }}</td>
        <td>{{ $natureOfPayment ?? 'Salary' }}</td>
        <td></td>
        <td class="center">{{ $item['payment_date'] ?? '' }}</td>
    </tr>
    @endforeach
    <tr class="total">
        <td colspan="6" class="right">Total (Rs.) {{ number_format($totalGross ?? 0, 2) }}</td>
    </tr>
</table>

<!-- Summary of tax deducted -->
<table>
    <tr><th colspan="4" class="title-th">Summary of tax deducted at source in respect of Deductee</th></tr>
    <tr>
        <th style="width:10%;">Quarter</th>
        <th style="width:40%;">Receipt Numbers of original quarterly statements of TDS under sub-section (3) of section 200</th>
        <th style="width:25%;">Amount of tax deducted in respect of Deductee</th>
        <th style="width:25%;">Amount of tax deposited/remitted in respect of Deductee</th>
    </tr>
    @foreach($quarters ?? [] as $q => $data)
    <tr>
        <td class="center">{{ $q }}</td>
        <td class="center">{{ $data['receipt_no'] ?? '' }}</td>
        <td class="right">{{ number_format($data['tds_deducted'] ?? 0, 2) }}</td>
        <td class="right">{{ number_format($data['tds_deposited'] ?? 0, 2) }}</td>
    </tr>
    @endforeach
</table>

<!-- Book Adjustment -->
<table>
    <tr>
        <th colspan="6" class="title-th">
            I. DETAILS OF TAX DEDUCTED AND DEPOSITED IN THE CENTRAL GOVERNMENT ACCOUNT THROUGH BOOK ADJUSTMENT
        </th>
    </tr>
    <tr>
        <td colspan="6" class="sub-th">
            (The deductor to provide payment wise details of tax deducted and deposited with respect to the deductee)
        </td>
    </tr>
    <tr>
        <th style="width:6%;">Sl. No.</th>
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

<div class="page-marker">-- 1 of 2 --</div>
<div class="page-break"></div>

<!-- Challan Section -->
<table>
    <tr>
        <th colspan="6" class="title-th">
            II. DETAILS OF TAX DEDUCTED AND DEPOSITED IN THE CENTRAL GOVERNMENT ACCOUNT THROUGH CHALLAN
        </th>
    </tr>
    <tr>
        <td colspan="6" class="sub-th">
            (The deductor to provide payment wise details of tax deducted and deposited with respect to the deductee)
        </td>
    </tr>
    <tr>
        <th rowspan="2" style="width:6%;">Sl. No.</th>
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

<!-- Verification Table -->
<table>
    <tr>
        <th colspan="4" class="title-th">Verification</th>
    </tr>
    <tr>
        <td colspan="4" style="border-bottom:none; padding:6px;">
            I, <span class="underline">{{ $signatoryName ?? '..............................................' }}</span>,
            son/daughter of <span class="underline">{{ $signatoryFather ?? '............................' }}</span>,
            working in the capacity of <span class="underline">{{ $signatoryDesignation ?? '............................' }}</span>
            (designation) do hereby certify that a sum of Rs.
            <span class="underline">{{ number_format($totalTds ?? 0, 2) }}</span>
            [Rs. <span class="underline">{{ $totalTdsWords ?? '......................................................' }}</span> (in words)]
            has been deducted and deposited to the credit of the Central Government.
            I further certify that the information given above is true, complete and correct and is based on the books of account, documents, TDS statements, TDS deposited and other available records.
        </td>
    </tr>
    <tr>
        <td style="width:15%; border-right:none;">Place</td>
        <td style="width:35%;"><span class="underline">{{ $company->city ?? 'Gurugram' }}</span></td>
        <td style="width:50%; text-align:right; border-left:none;" rowspan="3">
            (Signature of person responsible for deduction of tax)
        </td>
    </tr>
    <tr>
        <td style="border-right:none;">Date</td>
        <td><span class="underline">{{ $currentDate ?? '________________' }}</span></td>
    </tr>
    <tr>
        <td style="border-right:none;">Designation:</td>
        <td><span class="underline">{{ $signatoryDesignation ?? '................................' }}</span></td>
    </tr>
    <tr>
        <td colspan="2" style="border:none;"></td>
        <td style="text-align:right; border:none;">
            Full Name: <span class="underline">{{ $signatoryName ?? '................................' }}</span>
        </td>
    </tr>
</table>

<!-- Notes (kept outside table as per your request) -->
<div class="note">
    <strong>Notes:</strong><br>
    1. Government deductors to fill information in item I if tax is paid without production of an income-tax challan and in item II if tax is paid accompanied by an income-tax challan.<br>
    2. Non-Government deductors to fill information in item II.<br>
    3. The deductor shall furnish the address of the Commissioner of Income-tax (TDS) having jurisdiction as regards TDS statements of the assessee.<br>
    4. In items I and II, in column for tax deposited in respect of deductee, furnish total amount of TDS, surcharge (if applicable) and education cess (if applicable).
</div>

<div class="page-marker">-- 2 of 2 --</div>

</body>
</html>
