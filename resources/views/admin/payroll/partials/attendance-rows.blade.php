@foreach ($attendanceDetails as $row)
    <tr>
        <td class="para-text-table" style="padding:8px; position:sticky; left:0;">
            {{ $row['employee']->emp_code }}
        </td>

        <td class="para-text-table" style="padding:8px; position:sticky; left:70px; text-align:left;">
            {{ $row['employee']->emp_full_name }}
        </td>

        <td class="para-text-table">{{ $row['attendance_summary']['total_days'] }}</td>
        <td class="para-text-table">{{ $row['attendance_summary']['presentCount'] }}</td>
        <td class="para-text-table">{{ $row['attendance_summary']['absentCount'] }}</td>
        <td class="para-text-table">{{ $row['attendance_summary']['weekOffCount'] }}</td>
        <td class="para-text-table">{{ $row['attendance_summary']['weekOffPresentCount'] }}</td>
        <td class="para-text-table">{{ $row['attendance_summary']['halfDayCount'] }}</td>
        <td class="para-text-table">{{ $row['attendance_summary']['leaveCount'] }}</td>
        <td class="para-text-table">{{ $row['attendance_summary']['holidayCount'] }}</td>
        <td class="para-text-table">{{ $row['attendance_summary']['UPL'] }}</td>

        <td class="para-text-table">
            <input type="number" class="late-input input-text" value="{{ $row['attendance_summary']['lateCount'] }}"
                data-original="{{ $row['attendance_summary']['lateCount'] }}" min="0" step="0.5"
                style="width:50px;text-align:center;">
        </td>

        <td class="para-text-table">
            <input type="number" class="early-input input-text"
                value="{{ $row['attendance_summary']['earlyExitCount'] }}"
                data-original="{{ $row['attendance_summary']['earlyExitCount'] }}" min="0" step="0.5"
                style="width:50px;text-align:center;">
        </td>

        <td class="para-text-table">{{ $row['attendance_summary']['missedPunchCount'] }}</td>
        <td class="para-text-table">{{ $row['attendance_summary']['overtimeCount'] }}</td>

        <td class="para-text-table" style="font-weight:bold;">
            {{ $row['attendance_summary']['total'] }}
        </td>
    </tr>
@endforeach
