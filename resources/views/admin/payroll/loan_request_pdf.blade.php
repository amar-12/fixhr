<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advance Slip - Kesar Earth Solutions</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .sub-heading {
            text-align: center;
            text-decoration: underline;
            font-weight: bold;
            margin-bottom: 20px;
        }

        p {
            margin: 8px 0;
        }

        .section {
            margin-bottom: 15px;
        }

        .signature-row {
            margin-top: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .bordered td {
            border: 1px solid #000;
            padding: 8px;
        }

        .signatures td {
            padding-top: 50px;
            text-align: center;
            width: 33%;
        }

        .signatures.director td {
            width: 50%;
        }

        .line {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 150px;
            margin: 0 5px;
            height: 16px;
            vertical-align: bottom;
        }

        .line-large {
            min-width: 300px;
        }

        .line-medium {
            min-width: 200px;
        }

        .line-small {
            min-width: 80px;
        }

        .label {
            min-width: 150px;
            display: inline-block;
        }

        .indent {
            padding-left: 30px;
        }

        .subject-line {
            margin-bottom: 10px !important;
            font-weight: bold;
        }

        .date {
            text-align: right;
            margin-bottom: 20px;
        }


        .signatures td {
            padding-top: 50px;
            padding-bottom: 15px;
            text-align: center;
            width: 33%;
        }

        .signatures.director td {
            width: 50%;
        }

        .dotted-line {
            display: inline-block;
            border-bottom: 1px dotted #000;
            min-width: 150px;
            margin: 0 5px;
            height: 16px;
            vertical-align: bottom;
        }

        .dotted-line-large {
            min-width: 60%;
        }

        .dotted-line-medium {
            min-width: 200px;
        }
        .dotted-line-md-medium {
            min-width: 180px;
        }
        .dotted-line-lg-medium {
            min-width: 45%;
        }

        .dotted-line-sm-small {
            min-width: 40px;
        }
        .dotted-line-small {
            min-width: 80px;
        }

        .label {
            min-width: 150px;
            display: inline-block;
        }

        .indent-boday {
            padding-left: 0px;
        }

        .subject-line {
            margin: 20px 0;
            font-weight: bold;
        }

        .date {
            text-align: right;
            margin-bottom: 25px;
        }

        .signature-label {
            border-top: 1px solid #000;
            display: inline-block;
            padding-top: 3px;
            margin-top: -3px;
            min-width: 120px;
        }

        .header-line {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 15px;
            width: 100%;
        }
        .to-section {
            text-align: left;
        }
        .date-section {
            text-align: right;
        }
        .dotted-line {
            border-bottom: 1px dotted #000;
            min-width: 100px;
            display: inline-block;
        }
        .dotted-line-small {
            min-width: 80px;
        }


        .dotted-name, 
        .dotted-date, 
        .dotted-doj, 
        .dotted-code, 
        .dotted-dg-name, 
        .dotted-loan-date, 
        .dotted-installment, 
        .dotted-in-word, 
        .dotted-lnr-desc, 
        .dotted-advance-loan, 
        .dotted-line {
            display: inline-block;
            border-bottom: 1px dotted #000;
            padding-bottom: 0px;
            position: relative;
        }


        .dotted-date {
            min-width: 50px;
        }
        .dotted-loan-date {
            min-width: 120px;
        }

        .dotted-name {
            min-width: 150px;
        }
        .dotted-installment {
            min-width: 100px;
        }
        .dotted-in-word {
            min-width: 220px;
        }
        .dotted-lnr-desc {
            min-width: 280px;
        }
        .dotted-dg-name {
            min-width: 200px;
        }

        .dotted-code {
            min-width: 80px;
        }
        .dotted-doj {
            min-width: 80px;
        }
        .dotted-advance-loan {
            min-width: 80px;
        }

        .dotted-line {
            min-width: 100px;
        }

        .indent-body {
            padding-left: 30px;
        }

    </style>
</head>
<body>

    <h2>{{ $fh_business->b_name }}</h2>
    <div class="sub-heading">ADVANCE SLIP</div>

    <table style="width: 100%;">
        <tr>
            <td style="text-align: left; vertical-align: top;">
                <strong>To,</strong>
            </td>
            <td style="text-align: right; vertical-align: top;">
                Date: <span class="dotted-date"><strong>{{ \Carbon\Carbon::now()->format('d / m / Y') }}</strong></span>
            </td>
        </tr>
    </table>

    <div class="section">
        <p class="indent"><strong>HR Department,</strong></p>
        <p class="indent"><strong>{{ $fh_business->b_name }}</strong></p>
        <p class="indent"><strong>{{ $fh_business->b_address }}</strong></p>
    </div>

    <div class="subject-line">
        <strong>Subject : Regarding Salary Advance.</strong>
    </div>

    <div class="content">
        <p><strong>Respected Sir/Madam,</strong></p>
        <p class="indent-boday">
            This is to inform you that My Self (Mr./Mrs.) <span class="dotted-name"><strong>{{ $employee->emp_full_name }}</strong></span> 

            <span>Emp. Code: <strong class="dotted-code">{{ $employee->emp_code }}</strong><br></span>
            DOJ: <span class="dotted-doj">
            <strong>{{ $employee->emp_date_of_joining ? \Carbon\Carbon::parse($employee->emp_date_of_joining)->format('d / m / Y') : '' }}</strong>
            </span>
            and Working as <span class="dotted-dg-name"> <strong> {{ $fh_designation->dg_name }}</strong></span> want to take Advance of Rs. 

            <span class="dotted-advance-loan"><strong>{{ $loan_data->lnr_requested_amount }}</strong></span>
            (In words: <span class="dotted-in-word"><strong>{{ $loan_data_in_words }} </strong></span>) and I will deduct my advance on my salary<br>
            during installation of Rs. <span class="dotted-installment"><strong>{{ $loan_data->lnr_installment_amount }}</strong>
            </span> /- from <span class="dotted-loan-date"> <strong>{{ date('d / m / Y', strtotime($loan_data->lnr_start_date)) }}</strong></span>.
        </p>

        <p style="padding-top:10px;">I recommend that this advance application will be approved.<br>
        Current Salary: <span class="dotted-line dotted-line-medium"></span> /-</p>

        <p style="padding-top: 10px;">Reason for taking advance : <span class="dotted-lnr-desc"><strong>{{ $loan_data->lnr_description }}</strong></span></p>

        <p>Any SAD Balance : <span class="dotted-line dotted-line-medium"></span> (Fill by Account Department)</p>
    </div>

    <p style="font-size:16px;font-weight:800;text-align: center; padding-top: 20px;
            padding-bottom: 0px;">Kindly consider my application & oblige.</p>

    <table class="signatures">
        <tr>
            <td><span class="signature-label">Employee Sign.</span></td>
            <td><span class="signature-label">HOD Sign.</span></td>
            <td><span class="signature-label">Account Head Sign.</span></td>
        </tr>
    </table>

    <table class="bordered">
        <tr>
            <td width="50%"><strong>Approved Amount:</strong></td>
            <td width="50%"><span></span></td>
        </tr>
        <tr>
            <td><strong>Deduction Amount (per month):</strong></td>
            <td><span></span></td>
        </tr>
        <tr>
            <td><strong>Deduction Start Month:</strong></td>
            <td><span></span></td>
        </tr>
    </table>

    <table class="signatures director">
        <tr>
            <td><span class="signature-label">HR Sign.</span></td>
            <td><span class="signature-label">Director</span></td>
        </tr>
    </table>

</body>
</html>

<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advance Slip - Kesar Earth Solutions</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .sub-heading {
            text-align: center;
            text-decoration: underline;
            font-weight: bold;
            margin-bottom: 15px;
        }

        p {
            margin: 8px 0;
        }

        .section {
            margin-bottom: 10px;
        }

        .signature-row {
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .bordered td {
            border: 1px solid #000;
            padding: 6px;
        }

        .signatures td {
            padding-top: 40px;
            text-align: center;
        }

        .line {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 200px;
            margin: 0 5px;
        }

        .label {
            min-width: 150px;
            display: inline-block;
        }
    </style>
</head>
<body>

    <h2>KESAR EARTH SOLUTIONS</h2>
    <div class="sub-heading">ADVANCE SLIP</div>
    <div style="text-align: right;">
        Date: {{ \Carbon\Carbon::now()->format('d / m / Y') }}
    </div>

    <p><strong>To,</strong><br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong> Department,</strong><br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong> Earth Solutions,</strong><br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong> Earth Solutions,</strong><br><br>
    <strong>Subject: Regarding Salary Advance</p></strong><br>


    <p>Respected Sir/Madam,</p>

    <p>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This is to inform you that My Self (Mr./Mrs.) </span> Emp. Code: <strong>{{ $employee->emp_code }}</strong><br>
        DOJ:  <span class="line" style="width: 80px;">
        {{ $employee->emp_date_of_joining ? \Carbon\Carbon::parse($employee->emp_date_of_joining)->format('d / m / Y') : '' }}
         </span>
          and Working as {{ $employee->dg_name }} <span class="line"></span> want to take Advance of Rs. <span class="line"></span>
        (In words: <span class="line" style="width: 300px;"></span>) and I will deduct my advance on my salary<br>
        during installation of Rs. <span class="line"></span> /- from <span class="line"></span>.
    </p>

    <p>I recommend that this advance application will be approved.<br>
    Current Salary: <span class="line"></span> /-</p>

    <p>Reason for taking advance: <span class="line" style="width: 400px;"></span></p>

    <p>Any SAD Balance: <span class="line" style="width: 250px;"></span> (Fill by Account Department)</p>

    <p>Kindly consider my application & oblige.</p>

    <table class="signatures">
        <tr>
            <td>Employee Sign.</td>
            <td>HOD Sign.</td>
            <td>Account Head Sign.</td>
        </tr>
    </table>

    <table class="bordered">
        <tr>
            <td><strong>Approved Amount:</strong></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Deduction Amount (per month):</strong></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Deduction Start Month:</strong></td>
            <td></td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>HR Sign.</td>
            <td>Director</td>
        </tr>
    </table>

</body>
</html>
 -->