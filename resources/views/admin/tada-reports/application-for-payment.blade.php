@php
use Illuminate\Support\Carbon;
use Kwn\NumberToWords\NumberToWords;

@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application for Payment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }
        .header, .footer {
            font-weight: bold;
            text-align: center;
        }
        .signature {
            height: 50px;
        }
        .container {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .appnum {
            display: flex;
            justify-content: flex-end;
            /* align-items: center; */
        }
        @media print {
            body {
                zoom: 100%; /* Scale down the content slightly */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Application for Payment</h1>
        </div>
        <div class="appnum">
            <h4>Application No: &lt;PSD TD&gt; S.N.</h4>
        </div>
        <table class="table">
            <thead>
            </thead>
            <tbody>
                <tr>
                    <th>Department</th>
                    <td>{{$claimData->fh_employee->fh_department->d_name}}</td>
                    <th>Cost Center</th>
                    <td>{{$claimData->fh_employee->emp_sap_budget_code}}</td>
                    <th>Date</th>
                    <td>{{Carbon::parse($claimData->created_at)->format('Y-m-d') }}</td>
                </tr>
                <tr>
                    <th>Vendor Name</th>
                    <td colspan="3">{{$claimData->fh_employee->emp_full_name}}</td>
                    <th>Emp. Code</th>
                    <td>{{$claimData->fh_employee->emp_code}}</td>
                </tr>
                <tr>
                    <th>SAP PO No</th>
                    <td></td>
                    <th>Total Agreement Amount</th>
                    <td>19750 INR</td>
                    <th>Paid Before</th>
                    <td></td>
                </tr>
                <tr>
                    <th>Pay Now</th>
                    <td>19750 INR</td>
                    <th>Payment Date</th>
                    <td>{{Carbon::parse($claimData->created_at)->format('d-m-Y') }}</td>
                    <th>Against advance</th>
                    <td></td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td colspan="5">
                        <table>
                            <tr>
                                <th>Account Code</th>
                                <th>Invoice No</th>
                                <th>Amount</th>
                                <th>Description</th>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>{{$claimData->tc_amount}} INR</td>
                                <td>{{Carbon::parse($claimData->created_at)->format('M-Y') }} Travel expenses payment</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td> &nbsp; </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>&nbsp; </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>Total</td>
                                <td>{{$claimData->tc_amount}} INR</td>
                                <td>{{$capitalizedWords. ' only'}} </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <th>Applied by</th>
                    <td class="signature" colspan="2">{{$claimData->fh_employee->emp_full_name}}</td>
                    <th>Confirmed by</th>
                    <td colspan="2">Bhupendra Nayak Sir</td>
                </tr>
                <tr>
                    <td colspan="6" class="header">Finance & Accounts Department</td>
                </tr>
                <tr>
                    <th>Verified by</th>
                    <td colspan="2" class="signature"></td>
                    <th>Confirmed by</th>
                    <td colspan="2" class="signature"></td>
                </tr>
                <tr>
                    <th>Approved by</th>
                    <td colspan="5" class="signature"></td>
                </tr>
                <tr>
                    <th>Remark</th>
                    <td colspan="5" class="signature"></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
