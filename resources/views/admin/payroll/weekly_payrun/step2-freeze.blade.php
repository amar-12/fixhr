<div style="padding: 20px;">
    <div class="alert alert-warning mb-3">
        <i data-lucide="lock" class="me-2"></i>
        Verify attendance for week {{ $week->ppw_week_number }}: 
        {{ Carbon\Carbon::parse($week->ppw_start_date)->format('d M') }} - 
        {{ Carbon\Carbon::parse($week->ppw_end_date)->format('d M, Y') }}
    </div>
    
    <div class="attendance-table-container">
        <div class="attendance-table-wrapper">
            <table id="attendanceTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Code</th><th>Name</th><th>Days</th><th>Present</th><th>Absent</th>
                        <th>W-Off</th><th>WOP</th><th>Half</th><th>Leave</th><th>Holiday</th>
                        <th>UPL</th><th>Late</th><th>Early</th><th>Missed</th><th>OT</th><th>Total</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody">
                    <tr><td colspan="16" class="text-center py-5">Loading attendance...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="text-end mt-3">
        <button onclick="showView('STEP1')" class="btn btn-secondary me-2">
            <i data-lucide="arrow-left"></i> Back
        </button>
        <button onclick="freezeWeeklyAttendance()" class="btn btn-danger">
            <i data-lucide="lock"></i> Freeze Weekly Attendance
        </button>
    </div>
</div>