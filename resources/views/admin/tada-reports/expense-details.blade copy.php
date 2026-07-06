<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Details</title>
    <style>
        body {
            font-size: 10px; /* Adjust font size to fit more content */
            margin: 0;      /* Remove default margins */
            padding: 0;     /* Remove default padding */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            /* margin-bottom: 20px; */
        }
        th, td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }
        .header {
            font-weight: bold;
            text-align: center;
        }
        .sub-header {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .container {
            padding: 20px;
            max-width: 80%;
            margin: 0 auto;
        }
        .right-text {
            text-align: right;
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

        <table>
            <tr>
                <td colspan="8" class="header">Expense Details</td>
            </tr>
            <tr>
                <td colspan="8" class="sub-header">Travelling</td>
            </tr>
            <tr>
                <th rowspan="2">Date</th>
                <th colspan="2" class="sub-header">Place</th>
                <th rowspan="2">Mode</th>
                <th colspan="3" class="sub-header">Paid By</th>
                <th rowspan="2" class="sub-header">Remarks</th>
            </tr>
            <tr>
                <th style="width: 18%">From</th>
                <th>To</th>
                <th style="width: 18%">Company</th>
                <th>Self</th>
                <th>Total</th>
            </tr>
            <tr>
                <td>5-Apr-2024</td>
                <td>Nagpur (M.H.)</td>
                <td>Vijayawada (A.P.)</td>
                <td>Train</td>
                <td></td>
                <td>1978</td>
                <td>1978</td>
                <td></td>
            </tr>
            <tr>
                <td>10-Apr-2024</td>
                <td>Vijayawada (A.P.)</td>
                <td>Sathupalli (T.S.)</td>
                <td>Car</td>
                <td></td>
                <td>60</td>
                <td>60</td>
                <td>Paid by Siddhart</td>
            </tr>
            <tr>
                <td>18-Apr-2024</td>
                <td>Sathupalli (T.S.)</td>
                <td>Bhuvapalli (T.S.)</td>
                <td>Bus</td>
                <td></td>
                <td>460</td>
                <td>460</td>
                <td></td>
            </tr>
            <tr>
                <td>20-Apr-2024</td>
                <td>Ramagundam (T.S.)</td>
                <td>Nagpur (M.H.)</td>
                <td>Train</td>
                <td></td>
                <td>1605</td>
                <td>1605</td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="sub-header">Total</td>
                <td>4103</td>
                <td></td>
                <td>Paid by Self</td>
            </tr>

            <tr>
                <th colspan="8" class="header">Lodging</th>
            </tr>
            <tr>
                <th rowspan="2">Date</th>
                <th rowspan="2" colspan="2" class="sub-header">Hotel Name</th>
                <th rowspan="2">Bill No.</th>
                <th colspan="3" class="sub-header">Paid By</th>
                <th rowspan="2" class="sub-header">Remarks</th>
            </tr>
            <tr>
                <th>Company</th>
                <th>Self</th>
                <th>Total</th>
            </tr>
            <tr>
                <td>5-Apr-2024</td>
                <td colspan="2">Cherry's Deluxe Lodge</td>
                <td>3923</td>
                <td></td>
                <td>16000</td>
                <td>16000</td>
                <td>6-Apr to 14-Apr-2024 Stay (8 Days)</td>
            </tr>
            <tr>
                <td>14-Apr-2024</td>
                <td colspan="2">Uma Maheshwara Lodge</td>
                <td>392</td>
                <td></td>
                <td>6000</td>
                <td>6000</td>
                <td>16-Apr to 18-Apr-2024 Stay (4 Days)</td>
            </tr>
            <tr>
                <td>20-Apr-2024</td>
                <td colspan="2">Hotel Srimaya Luxury</td>
                <td>20241000056</td>
                <td></td>
                <td>1631</td>
                <td>1631</td>
                <td>10-Apr to 20-Apr-2024 Stay (1 Days)</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td class="sub-header">Total</td>
                <td></td>
                <td>23631</td>
                <td></td>
                <td>Paid by Self</td>
            </tr>

            <tr>
                <td colspan="8" class="header">Conveyance</td>
            </tr>
            <tr>
                <th rowspan="2">Date</th>
                <th colspan="2" class="sub-header">Place Visited</th>
                <th rowspan="2">Mode</th>
                <th rowspan="2">Purpose</th>
                <th colspan="2" class="sub-header">Paid By</th>
                <th rowspan="2" class="sub-header">Remarks</th>
            </tr>
            <tr>
                <th>From</th>
                <th>To</th>
                <th>Company</th>
                <th>Self</th>
            </tr>
            <tr>
                <td>5-Apr-2024</td>
                <td>Balaji Nagar, Nagpur</td>
                <td>Railway Station, Nagpur</td>
                <td>Auto</td>
                <td></td>
                <td></td>
                <td>200</td>
                <td></td>
            </tr>
            <tr>
                <td>7-Apr-2024</td>
                <td>Sathupalli to Sushee-Camp</td>
                <td>Rajerla back to Sathupalli</td>
                <td>Auto</td>
                <td></td>
                <td></td>
                <td>300</td>
                <td></td>
            </tr>
            <tr>
                <td>8-Apr-2024</td>
                <td>Sathupalli to Sushee-Camp</td>
                <td>Rajerla back to Sathupalli</td>
                <td>Auto</td>
                <td></td>
                <td></td>
                <td>300</td>
                <td></td>
            </tr>
            <tr>
                <td>9-Apr-2024</td>
                <td>Sathupalli to Grand Furniture</td>
                <td>Kakarapalli back to Sathupalli</td>
                <td>Auto</td>
                <td></td>
                <td></td>
                <td>200</td>
                <td></td>
            </tr>
            <tr>
                <td>10-Apr-2024</td>
                <td>Sathupalli to Sushee-Camp</td>
                <td>Rajerla back to Sathupalli</td>
                <td>Auto</td>
                <td></td>
                <td></td>
                <td>300</td>
                <td></td>
            </tr>
            <tr>
                <td>11-Apr-2024</td>
                <td>Sathupalli to Vijay Ind</td>
                <td>Tallamadla back to Sathupalli</td>
                <td>Auto</td>
                <td></td>
                <td></td>
                <td>200</td>
                <td></td>
            </tr>
            <tr>
                <td>12-Apr-2024</td>
                <td>Sathupalli to Sushee-Camp</td>
                <td>Rajerla back to Sathupalli</td>
                <td>Auto</td>
                <td></td>
                <td></td>
                <td>300</td>
                <td></td>
            </tr>
            <tr>
                <td>13-Apr-2024</td>
                <td>Sathupalli to Sushee-Camp</td>
                <td>Rajerla back to Sathupalli</td>
                <td>Auto</td>
                <td></td>
                <td></td>
                <td>150</td>
                <td></td>
            </tr>
            <tr>
                <td>14-Apr-2024</td>
                <td>Sathupalli to Sushee-Camp</td>
                <td>Rajerla back to Sathupalli</td>
                <td>Auto</td>
                <td></td>
                <td></td>
                <td>300</td>
                <td></td>
            </tr>
            <tr>
                <td>15-Apr-2024</td>
                <td>Sathupalli to Sushee-Camp</td>
                <td>Rajerla back to Sathupalli</td>
                <td>Auto</td>
                <td></td>
                <td></td>
                <td>300</td>
                <td></td>
            </tr>
            <tr>
                <td>16-Apr-2024</td>
                <td>Sathupalli to Vijay Ind</td>
                <td>Tallamadla back to Sathupalli</td>
                <td>Auto</td>
                <td></td>
                <td></td>
                <td>200</td>
                <td></td>
            </tr>
            <tr>
                <td>17-Apr-2024</td>
                <td>Sathupalli to Sushee-Camp</td>
                <td>Rajerla back to Sathupalli</td>
                <td>Auto</td>
                <td></td>
                <td></td>
                <td>300</td>
                <td></td>
            </tr>
            <tr>
                <td>18-Apr-2024</td>
                <td>Sathupalli to Sushee-Camp</td>
                <td>Rajerla back to Sathupalli</td>
                <td>Auto</td>
                <td></td>
                <td></td>
                <td>300</td>
                <td></td>
            </tr>
            <tr>
                <td>19-Apr-2024</td>
                <td>Hotel, Ramagundam</td>
                <td>Railway Station, Ramagundam</td>
                <td>Auto</td>
                <td></td>
                <td></td>
                <td>200</td>
                <td></td>
            </tr>
            <tr>
                <td>20-Apr-2024</td>
                <td>Railway Station, Nagpur</td>
                <td>Balaji Nagar, Nagpur</td>
                <td>Auto</td>
                <td></td>
                <td></td>
                <td>200</td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="sub-header">Total</td>
                <td></td>
                <td>2850</td>
                <td>Paid by Self</td>
            </tr>

            <tr>
                <td colspan="8" class="sub-header">Other Expenses</td>
            </tr>
            <tr>
                <th rowspan="2">Date</th>
                <th rowspan="2" colspan="3" class="sub-header">Particulars</th>
                <th rowspan="2">Bill No.</th>
                <th colspan="2" class="sub-header">Paid By</th>
                <th rowspan="2" class="sub-header">Remarks</th>
            </tr>
            <tr>
                {{-- <th></th>
                <th></th> --}}
                <th>Self</th>
                <th>Company</th>
            </tr>
            <tr>
                <td>13-Apr-2024</td>
                <td colspan="3">Sushee Site Support Team Dinner Bill (Mr. Chester, Srinivas, Nitesh and Me)</td>
                <td>35</td>
                <td>1386</td>
                <td>1386</td>
                <td></td>
            </tr>
            <tr>
                <td>19-Apr-2024</td>
                <td colspan="3">Vijay Ind, Sathupalli Furniture purchase Bill sending through DTDC Charges</td>
                <td>H47125976</td>
                <td>200</td>
                <td>200</td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

                <td class="sub-header">Total</td>
                <td>1586</td>
                <td></td>
                <td>Paid by Self</td>
            </tr>
        </table>
    </div>
</body>
</html>
