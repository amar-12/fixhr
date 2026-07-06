@foreach ($attendanceDetails as $row)
    <tr data-emp-id="{{ $row['employee']->emp_id }}" data-emp-code="{{ $row['employee']->emp_code }}">
        <td class="para-text-table" style="padding:8px; position:sticky; left:0; background: inherit;">
            {{ $row['employee']->emp_code }}
        </td>
        <td class="para-text-table" style="padding:8px; position:sticky; left:70px; text-align:left; background: inherit;">
            {{ $row['employee']->emp_full_name }}
        </td>
        <td class="para-text-table text-center">{{ $row['attendance_summary']['total_days'] ?? 7 }}</td>
        <td class="para-text-table text-center">{{ $row['attendance_summary']['presentCount'] ?? 0 }}</td>
        <td class="para-text-table text-center">{{ $row['attendance_summary']['absentCount'] ?? 0 }}</td>
        <td class="para-text-table text-center">{{ $row['attendance_summary']['weekOffCount'] ?? 0 }}</td>
        <td class="para-text-table text-center">{{ $row['attendance_summary']['weekOffPresentCount'] ?? 0 }}</td>
        <td class="para-text-table text-center">{{ $row['attendance_summary']['halfDayCount'] ?? 0 }}</td>
        <td class="para-text-table text-center">{{ $row['attendance_summary']['leaveCount'] ?? 0 }}</td>
        <td class="para-text-table text-center">{{ $row['attendance_summary']['holidayCount'] ?? 0 }}</td>
        <td class="para-text-table text-center">{{ $row['attendance_summary']['UPL'] ?? 0 }}</td>
        <td class="para-text-table text-center">
            <input type="number" class="late-input input-text" 
                value="{{ $row['attendance_summary']['lateCount'] ?? 0 }}"
                data-original="{{ $row['attendance_summary']['lateCount'] ?? 0 }}" 
                min="0" max="7" step="0.5"
                style="width:55px;text-align:center; border-radius:4px; border:1px solid #cbd5e1; padding:4px;">
        </td>
        <td class="para-text-table text-center">
            <input type="number" class="early-input input-text"
                value="{{ $row['attendance_summary']['earlyExitCount'] ?? 0 }}"
                data-original="{{ $row['attendance_summary']['earlyExitCount'] ?? 0 }}" 
                min="0" max="7" step="0.5"
                style="width:55px;text-align:center; border-radius:4px; border:1px solid #cbd5e1; padding:4px;">
        </td>
        <td class="para-text-table text-center">{{ $row['attendance_summary']['missedPunchCount'] ?? 0 }}</td>
        <td class="para-text-table text-center">{{ $row['attendance_summary']['overtimeCount'] ?? 0 }}</td>
        <td class="para-text-table text-center" style="font-weight:bold; color: #059669;">
            {{ $row['attendance_summary']['total'] ?? 0 }}
        </td>
    </tr>
@endforeach

@if(count($attendanceDetails) == 0)
    <tr>
        <td colspan="16" style="padding:40px; text-align:center; color:#94a3b8;">
            No employees found for this week
        </td>
    </tr>
@endif