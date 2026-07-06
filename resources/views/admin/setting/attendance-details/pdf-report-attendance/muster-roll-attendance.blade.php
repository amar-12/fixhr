<?php
use App\Models\AttendanceRecord;
use App\Helpers\CentralLogics;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    @php
        $user = Auth::user();
        $imagePath = $user->fh_business->b_logo ? $user->fh_business->b_logo : public_path(asset('assets/logo/logo_round.png'));
        $imageData = file_exists($imagePath) ? base64_encode(file_get_contents($imagePath)) : null;
    @endphp
    <div class="header" style="font-family:Sans-Serif;font-size:11px">
        <table>
            <tr>
                @if ($imageData)
                    <td><img src="{{ 'data:image/png;base64,' . $imageData }}" height="80px" width="80px"
                            style="padding:10px"></td>
                @endif
                <td>
                    <span><b>{{ $business_name }}</b></span><br>
                    <span>Attendance Muster Roll Report</span><br>
                    <span>For the month of {{ date('F-Y', strtotime($year . '-' . $month . '-01')) }}</span><br>
                </td>
            </tr>
        </table>
    </div>
    <div class="table-view" style="padding-top: 10px;font-family:Sans-Serif;font-size:9px">
        <table style="border-collapse: collapse; border: 1px solid black;">
            <thead>
                <tr>
                    <th colspan="3" style="border: 0.5px solid black;"></th>
                    <th colspan="{{ $monthDay }}" style="border: 0.5px solid black;">
                        {{ date('F-Y', strtotime($year . '-' . $month . '-01')) }}</th>
                    <th colspan="11" style="border: 0.5px solid black;padding-top:2px;padding-bottom:2px">Summary</th>
                </tr>
                <tr>
                    <th style="border: 0.5px solid black;">S.No.</th>
                    <th style="border: 0.5px solid black; padding-top:4px;padding-bottom:4px">Name</th>
                    <th style="border: 0.5px solid black;">Employee ID</th>
                    @for ($i = 1; $i <= $monthDay; $i++)
                        <th style="border: 0.5px solid black; padding-left:6px;padding-right:6px;">{{ $i }}
                        </th>
                    @endfor
                    <th style="border: 0.5px solid black; padding-left:5px;padding-right:5px;">P</th>
                    <th style="border: 0.5px solid black; padding-left:5px;padding-right:5px;">A</th>
                    <th style="border: 0.5px solid black; padding-left:5px;padding-right:5px;">HD</th>
                    <th style="border: 0.5px solid black; padding-left:5px;padding-right:5px;">L</th>
                    <th style="border: 0.5px solid black; padding-left:5px;padding-right:5px;">WO</th>
                    <th style="border: 0.5px solid black; padding-left:5px;padding-right:5px;">HO</th>
                    <th style="border: 0.5px solid black; padding-left:5px;padding-right:5px;">MSP</th>
                    <th style="border: 0.5px solid black; padding-left:5px;padding-right:5px;">OT</th>
                    <th style="border: 0.5px solid black; padding-left:5px;padding-right:5px;">LE</th>
                    <th style="border: 0.5px solid black; padding-left:5px;padding-right:5px;">EE</th>
                    <th style="border: 0.5px solid black; padding-left:5px;padding-right:5px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($EmpData as $key => $data)
                    <tr>
                        @php
                          $present = $absent = $half_day = $leave = $holiday = $misPunch = $over_time = $late = $prt = 0;
                        @endphp
                        <td style="border: 0.5px solid black; text-align:center">{{ ++$key }}</td>
                        <td style="border: 0.5px solid black;">
                            {{ $data->emp_fname . ' ' . $data->emp_mname . ' ' . $data->emp_lname }}</td>
                        <td style="border: 0.5px solid black; text-align:center">{{ $data->emp_code }}</td>
                        @php
                            $present = $absent = $half_day = $leave = $holiday = $misPunch = $over_time = $late = $prt = 0;
                        @endphp
                        @for ($i = 1; $i <= $monthDay; $i++)
                        <td style="border: 0.5px solid black; text-align:center;">
                                @php
                                    $recordStatus = AttendanceRecord::where('atd_emp_id', $data->emp_id)
                                        ->where('atd_date', date($year . '-' . $month . '-' . $i))
                                        ->first();
                                    $attendanceData = CentralLogics::getAttendanceRecord($data->emp_id, date($year . '-' . $month . '-' . $i));
                                    $status = $attendanceData['status'] ?? null;
                                    $leave_name = $attendanceData['leave_name'] ?? null;
                                    $holiday_name = $attendanceData['holiday_name'] ?? null;

                                    if ($recordStatus && $recordStatus->atd_attendance_status == 251 || $status == 251) {
                                        echo 'P';
                                        $present++;
                                    } elseif ($recordStatus && $recordStatus->atd_attendance_status == 252 || $status == 202) {
                                        echo 'HD';
                                        $half_day++;
                                    } elseif ($recordStatus && $recordStatus->atd_attendance_status == 203 || $status == 203) {
                                        echo 'A';
                                        $absent++;
                                    } elseif ($recordStatus && $recordStatus->atd_attendance_status == 204) {
                                        echo 'PRT';
                                        $prt++;
                                    } elseif ($status == 205 || $status == 206) {
                                        echo 'HO';
                                        $holiday++;
                                    } elseif ($recordStatus && $recordStatus->atd_attendance_status == 228 || $status == 228) {
                                        echo 'MSP';
                                        $misPunch++;
                                    } elseif ($recordStatus && $recordStatus->atd_attendance_status == 201 || $status == 201) {
                                        echo $leave_name ? $leave_name . '+L' : 'L';
                                        $leave++;
                                    } elseif ($recordStatus && $recordStatus->atd_is_overtime == 1) {
                                        $over_time++;
                                    } elseif ($recordStatus && $recordStatus->atd_is_late == 1) {
                                        $late++;
                                    } else {
                                        echo '-';
                                    }

                                    $total = $present + ($half_day * 0.5);
                                @endphp
                            </td>
                        @endfor

                        <td style="border: 0.5px solid black; text-align:center; ">
                            {{ $present }}</td>
                        <td style="border: 0.5px solid black; text-align:center; ">
                            {{ $absent }}</td>
                        <td style="border: 0.5px solid black; text-align:center; ">
                            {{ $half_day }}</td>
                        <td style="border: 0.5px solid black; text-align:center; ">
                            {{ $leave }}</td>
                        <td style="border: 0.5px solid black; text-align:center; ">
                            {{ $holiday }}</td>
                        <td style="border: 0.5px solid black; text-align:center; ">
                            {{ $holiday }}</td>
                        <td style="border: 0.5px solid black; text-align:center; ">
                            {{ $misPunch }}</td>
                        <td style="border: 0.5px solid black; text-align:center; ">
                            {{ $over_time }}</td>
                        <td style="border: 0.5px solid black; text-align:center; ">
                            {{ $late }}</td>
                        <td style="border: 0.5px solid black; text-align:center; ">
                            {{ $prt }}</td>
                        <td style="border: 0.5px solid black; text-align:center; ">
                            {{ $total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div>
        <span style="padding-top: 10px;font-family:Sans-Serif;font-size:9px">P => Present, A => Absent, HD => Halfday, L
            => Leave, WO => Week-off, HO => Holiday, MSP => Mis-Punch, OT => Overtime, LE => Late Entry, EE => Early
            Exit</span>
    </div>
    <div>
        <span style="padding-top: 10px;font-family:Sans-Serif;font-size:9px"><b>Note: For today`s attendance, count would temporarily reflect in MSP till the punch out and the status shown would be `Present` </b></span>
    </div>
    <div>
        <span style="padding-top: 10px;font-family:Sans-Serif;font-size:9px">Exported By
            <b>{{ $user->emp_full_name }}</b> at: <b>{{ now() }}</b></span>
    </div>
</body>

</html>

