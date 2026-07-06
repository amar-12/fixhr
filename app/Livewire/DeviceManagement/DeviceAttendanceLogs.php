<?php
namespace App\Livewire\DeviceManagement;
use App\Models\AttendanceLog;
use App\Models\AttendanceRecord;
use App\Models\Business;
use App\Models\Employee;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
class DeviceAttendanceLogs extends Component
{
    public $attendanceRecords = [];
    public $originalAttendanceRecords = [];
    public $deviceSn;
    public $businessId;
    public $businessName;
    public $selectedDate;
    public $currentMonth;
    public $currentYear;
    public $groupedRecords = [];
    protected $listeners = ['attendanceSynced' => 'loadRecords'];
    public function mount($records, $business_id, $device_sn)
    {
        // dd($records, $business_id, $device_sn);
        $this->attendanceRecords = $records;
        
        $this->businessId = $business_id;
        $this->businessName = Business::where('b_id', $business_id)->first()?->b_name;
        $this->deviceSn = $device_sn;
        $this->currentMonth = Carbon::now()->month;
        $this->currentYear = Carbon::now()->year;
        $this->selectedDate = Carbon::today()->toDateString();
        $this->loadRecords();
    }
   
    
    public function loadRecords()
{
    // ---------- 1) Process device attendance records (already eager-loaded) ----------
    $this->groupedRecords = collect($this->attendanceRecords)
        ->groupBy(function ($record) {
            return Carbon::parse($record->atd_date)->toDateString();
        })
        ->map(function ($dailyRecords) {
            return $dailyRecords
                ->groupBy(function ($record) {
                    $emp = $record->fh_employee;
                    return $emp ? ($emp->emp_code ?? (string) $record->atd_emp_id) : (string) $record->atd_emp_id;
                })
                ->map(function ($userRecords, $empKey) {
                    $first = $userRecords->first();
                    $emp = $first->fh_employee;
                    return [
                        'user_key'    => (string) $empKey,
                        'name'        => $emp ? ($emp->emp_full_name ?? 'N/A') : 'N/A',
                        'check_in'    => $first->atd_check_in_time
                            ? Carbon::parse($first->atd_check_in_time)->format('Y-m-d H:i:s')
                            : null,
                        'check_out'   => $first->atd_check_out_time
                            ? Carbon::parse($first->atd_check_out_time)->format('Y-m-d H:i:s')
                            : null,
                        'processed_at' => $first->updated_at
                            ? Carbon::parse($first->updated_at)->format('Y-m-d H:i:s')
                            : null,
                    ];
                });
        })
        ->toArray();

    // ---------- 2) Prepare date range for DB fetches ----------
    $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
    $endOfMonth = $startOfMonth->copy()->endOfMonth();

    // ---------- 3) Fetch manual attendance logs (overrides) ----------
    $attendanceLogsRaw = AttendanceLog::where('al_b_id', $this->businessId)
        ->whereBetween('al_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
        ->with('fh_employee_real')
        ->get();

    $attendanceLogs = $attendanceLogsRaw->map(function ($record) {
        $segments = json_decode($record->al_segments ?? '[]', true) ?? [];
        $employee = $record->fh_employee_real;
        return [
            'emp_id'            => (string) ($record->al_emp_id ?? ''),
            'emp_code'          => $employee ? ($employee->emp_code ?? $record->al_emp_id) : ($record->al_emp_id ?? ''),
            'name'              => $employee ? ($employee->emp_full_name ?? 'N/A') : ($record->name ?? 'N/A'),
            'check_in_time'     => $record->al_check_in_time
                ? Carbon::parse($record->al_check_in_time)->format('Y-m-d H:i:s')
                : null,
            'check_out_time'    => $record->al_check_out_time
                ? Carbon::parse($record->al_check_out_time)->format('Y-m-d H:i:s')
                : null,
            'date'              => Carbon::parse($record->al_date)->toDateString(),
            'segments'          => $segments,
            'total_worked_hours' => $record->al_total_worked_hours ?? null,
            'source'            => 'log',
        ];
    });

    // Debug: Log the structure of attendanceLogs
    Log::debug('Attendance Logs', ['count' => $attendanceLogs->count(), 'sample' => $attendanceLogs->take(2)->toArray()]);

    // ---------- 4) Transform system records (AttendanceRecord) ----------
    $logKeys = $attendanceLogs->map(fn($r) => $r['date'] . '|' . $r['emp_id'])->unique();

    $systemRecords = collect($this->attendanceRecords)
        ->filter(function ($rec) use ($logKeys) {
            $key = Carbon::parse($rec->atd_date)->toDateString() . '|' . (string) ($rec->atd_emp_id ?? '');
            return ! $logKeys->contains($key);
        })
        ->map(function ($record) {
            $segments = json_decode($record->atd_segments ?? '[]', true) ?? [];
            $employee = $record->fh_employee;
            return [
                'emp_id'            => (string) ($record->atd_emp_id ?? ''),
                'emp_code'          => $employee ? ($employee->emp_code ?? $record->atd_emp_id) : $record->atd_emp_id,
                'name'              => $employee ? ($employee->emp_full_name ?? 'N/A') : 'N/A',
                'check_in_time'     => $record->atd_check_in_time
                    ? Carbon::parse($record->atd_check_in_time)->format('Y-m-d H:i:s')
                    : null,
                'check_out_time'    => $record->atd_check_out_time
                    ? Carbon::parse($record->atd_check_out_time)->format('Y-m-d H:i:s')
                    : null,
                'date'              => Carbon::parse($record->atd_date)->toDateString(),
                'segments'          => $segments,
                'total_worked_hours' => $record->atd_total_worked_hours ?? null,
                'source'            => 'record',
            ];
        });

    // Debug: Log the structure of systemRecords
    Log::debug('System Records', ['count' => $systemRecords->count(), 'sample' => $systemRecords->take(2)->toArray()]);

  // ---------- 5) Merge manual logs with filtered system records ----------
$this->originalAttendanceRecords = $attendanceLogs
    ->concat($systemRecords) // Use concat instead of merge for clarity
    ->groupBy('date')
    ->map(function ($items) {
        return $items->values()->toArray();
    })
    ->toArray();

// Optional debug logs
Log::info('Grouped Device Records Loaded', ['count' => count($this->groupedRecords)]);
Log::info('Original Attendance Records Loaded', ['dates' => array_keys($this->originalAttendanceRecords)]);
}
    public function selectDate($date)
    {
        $this->selectedDate = $date;
    }
    public function previousMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        $this->refreshRecords();
    }
    public function nextMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        $this->refreshRecords();
    }
    public function refreshRecords()
    {
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $deviceId = \App\Models\DeviceManagement::where('serial_name', $this->deviceSn)
            ->where('b_id', $this->businessId)
            ->first()
            ?->id;
          
        //   dd($deviceId);
        if ($deviceId) {
            $this->attendanceRecords = AttendanceRecord::where('atd_b_id', $this->businessId)
                ->where('atd_device_id', $deviceId)
                ->whereBetween('atd_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->with('fh_employee')
                ->orderBy('atd_date', 'desc')
                ->get();
        } else {
            $this->attendanceRecords = collect();
        }
        $this->loadRecords();
    }
    public function render()
    {
        return view('livewire.device-management.device-attendance-logs', [
            'calendarDays' => $this->generateCalendarDays(),
        ]);
    }
    protected function generateCalendarDays()
    {
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $days = [];
        $firstDayOfWeek = $startOfMonth->dayOfWeek;
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $days[] = null;
        }
        for ($day = 1; $day <= $endOfMonth->day; $day++) {
            $date = Carbon::create($this->currentYear, $this->currentMonth, $day)->toDateString();
            $hasLogs = isset($this->groupedRecords[$date]);
            $hasOriginal = isset($this->originalAttendanceRecords[$date]);
            $days[] = [
                'date' => $date,
                'day' => $day,
                'hasLogs' => $hasLogs,
                'hasOriginal' => $hasOriginal,
            ];
        }
        return $days;
    }
}
