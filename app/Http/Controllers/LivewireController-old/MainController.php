<?php
namespace App\Http\Controllers\LivewireController;
use App\Exports\Policy\HolidayPolicyReport;
use App\Exports\Policy\WeeklyOffPolicyReport;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Business;
use App\Models\DeviceManagement;
use App\Models\PolicyHolidayList;
use App\Models\PolicyWeekOff;
use AWS\CRT\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
class MainController extends Controller
{
    public function employeeReport($slug = null)
    {
        if (!in_array($slug, ['employee-detail', 'employee-birthday', 'employee-joining'])) {
            abort(404);
        }
        return view('admin.setting.reports.employee-report', compact('slug'));
    }
    public function loanReport($slug)
    {
        if (!in_array($slug, ['loan-register', 'loan-statements', 'loan-projection'])) {
            return abort(404);
        }
        return view('admin.setting.reports.loan-report', compact('slug'));
    }
    public function attendanceReport($slug = null)
    {
        if (!in_array($slug, ['daily-attendance', 'yearly-summary', 'monthly-attendance-detail', 'monthly-attendance-basic', 'monthly-attendance-in-out', 'selfie-attendance'])) {
            abort(404);
        }
        return view('admin.setting.reports.daily-attendance-detail-report', compact('slug'));
    }
    public function leaveReport($slug)
    {
        if (!in_array($slug, ['summery', 'deatails', 'balance'])) {
            abort(404);
        }
        return view('admin.setting.reports.daily-attendance-leave-report', compact('slug'));
    }
    public function monitoringReport($slug)
    {
        if (!in_array($slug, ['late-coming', 'early-going', 'half-day', 'present', 'missed-punch', 'absent', 'holiday-present', 'weekly-off-present-detailed', 'weekly-off-present-summary', 'comp-off', 'continuous-absent', 'continuous-leave', 'gate-pass', 'attendance-regularization', 'missed-punch-regularization'])) {
            abort(404);
        }
        return view('admin.setting.reports.monitoring-report', compact('slug'));
    }
    public function payrollReport($slug = null)
    {
        if (!in_array($slug, ['payroll-report', 'yearly-processed-salary-report'])) {
            abort(404);
        }
        return view('admin.setting.reports.payroll_sheet', compact('slug'));
    }
    /**
     * Export ESIC report
     */
    public function esicReport()
    {
        // dd(1);
        return view('admin.setting.reports.esic_report');
    }
    public function adhocReport()
    {
        // dd(1);
        return view('admin.setting.reports.adhoc_report');
    }
    public function banksheet()
    {
        return view('admin.setting.reports.bank-sheet-report');
    }
    public function mcTempReport()
    {
        return view('admin.setting.reports.mc-template-report');
    }
    public function pfEpsReportSheet()
    {
        return view('admin.setting.reports.pf_eps_report_export');
    }
    public function policyReport($slug)
    {
        if (!in_array($slug, ['holiday-policy', 'week-off-policy'])) {
            abort(404);
        }
        // ---------------- HOLIDAY REPORT (UNCHANGED) ----------------
        if ($slug === 'holiday-policy') {
            try {
                $businessId = Auth::user()->emp_b_id;
                // Query all holidays for the business ID
                $query = PolicyHolidayList::with([
                    'fh_master_table'
                ])
                    ->where('phl_b_id', $businessId)
                    ->orderBy('phl_start_date', 'asc');
                $records = $query->get();
                // Process records to calculate total days for each holiday
                $records->each(function ($record) {
                    $start = Carbon::parse($record->phl_start_date)->startOfDay();
                    $end = $record->phl_end_date ? Carbon::parse($record->phl_end_date)->endOfDay() : $start;
                    // Calculate total days (inclusive of start and end date)
                    if ($start->equalTo($end)) {
                        $record->total_days = 1; // Single-day holiday
                    } else {
                        $record->total_days = floor($start->diffInDays($end) + 1); // Multi-day holiday, inclusive, rounded down
                    }
                });
                // Check if records are empty
                if ($records->isEmpty()) {
                    throw new \Exception('No holidays found for the business.');
                }
                $businessName = Business::find($businessId)->b_name ?? 'N/A';
                $fileName = 'HolidayReport_' . now()->format('Y-m-d') . '.xlsx';
                return Excel::download(
                    new HolidayPolicyReport($businessName, $records, $slug),
                    $fileName
                );
            } catch (\Exception $e) {
                // Handle errors (e.g., database issues or empty results)
                $this->addError('general', 'Failed to generate report: ' . $e->getMessage());
                return redirect()->back();
            }
        }
        // ---------------- WEEKLY-OFF (controller-only logic; Business ID only) ----------------
        if ($slug === 'week-off-policy') {
            // Get business info here (keeps holiday block 100% unchanged)
            $businessId   = Auth::user()->emp_b_id;
            $businessName = Business::find($businessId)->b_name ?? 'N/A';
            $year = now()->year; // generate for the current year
            // 1) Load the business weekly-off policy
            $policy = PolicyWeekOff::where('pwo_b_id', $businessId)->first(); // <-- adjust model/column names if needed
            if (!$policy) {
                $this->addError('general', 'No weekly-off policy configured for this business.');
                return redirect()->back();
            }
            // Expect tokens like ["1st Saturday","3rd Sunday"] OR ["Saturday","Sunday"] (meaning ALL occurrences)
            $recurrence = $policy->pwo_recurrence_day_ids
                ? json_decode($policy->pwo_recurrence_day_ids, true)
                : [];
            // Prefer model's getWeek(); else normalize here
            $weekDays = method_exists($policy, 'getWeek')
                ? $policy->getWeek($recurrence)   // ['Saturday'=>['1st','3rd'], 'Sunday'=>['2nd']]
                : $this->normalizeWeekDaysAllowAll($recurrence);
            // 2) Build the full year's dates month by month
            $dates = [];
            for ($m = 1; $m <= 12; $m++) {
                $occ = $this->occurrencesOfDaysInMonth($year, $m); // weekday => ['Y-m-d', ...]
                $dates = array_merge($dates, $this->pickNthOrAllWeekdays($weekDays, $occ));
            }
            // Unique + sorted
            $dates = array_values(array_unique($dates));
            sort($dates);
            if (empty($dates)) {
                $this->addError('general', "No weekly-off dates found for $year.");
                return redirect()->back();
            }
            // Prepare export rows: date + day
            $records = collect($dates)->map(function ($d) {
                $c = \Carbon\Carbon::parse($d);
                return (object)[
                    'date'     => $c->toDateString(),
                    'day_name' => $c->format('l'),
                ];
            });
            // dd($records->toArray());
            $fileName = "WeeklyOffReport_{$year}_" . now()->format('Y-m-d') . '.xlsx';
            return Excel::download(new WeeklyOffPolicyReport($businessName, $records, $slug), $fileName);
        }
        // Fallback (shouldn't hit)
        $this->addError('general', 'Invalid report type.');
        return redirect()->back();
    }
    /* ====================== INTERNAL (CONTROLLER-ONLY) METHODS ====================== */
    /**
     * Map weekday => ordered dates for a given month.
     * Example:
     * [
     *   'Saturday' => ['2025-08-02','2025-08-09','2025-08-16','2025-08-23','2025-08-30'],
     *   'Sunday'   => ['2025-08-03','2025-08-10','2025-08-17','2025-08-24','2025-08-31'],
     * ]
     */
    private function occurrencesOfDaysInMonth(int $year, int $month): array
    {
        $first = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
        $last  = $first->copy()->endOfMonth();
        $map = [
            'Monday' => [],
            'Tuesday' => [],
            'Wednesday' => [],
            'Thursday' => [],
            'Friday' => [],
            'Saturday' => [],
            'Sunday' => [],
        ];
        for ($d = $first->copy(); $d->lte($last); $d->addDay()) {
            $map[$d->format('l')][] = $d->toDateString();
        }
        return $map;
    }
    /**
     * Accepts tokens like ["1st Saturday","3rd Sunday"] OR ["Saturday","Sunday"] (meaning ALL).
     * Returns ['Saturday'=>['1st','3rd']] or ['Saturday'=>[]] (empty array = ALL occurrences).
     */
    private function normalizeWeekDaysAllowAll(array $recurrence): array
    {
        $map = [];
        foreach ($recurrence as $token) {
            $token = trim($token);
            // "1st Saturday" style
            if (preg_match('/^(1st|2nd|3rd|4th|5th)\s+(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)$/i', $token, $m)) {
                $nth = $m[1];
                $day = ucfirst(strtolower($m[2]));
                $map[$day] = $map[$day] ?? [];
                $map[$day][] = $nth;
                continue;
            }
            // "Saturday" style => ALL occurrences
            if (preg_match('/^(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)$/i', $token, $m)) {
                $day = ucfirst(strtolower($m[1]));
                $map[$day] = $map[$day] ?? []; // empty array means ALL
            }
        }
        return $map;
    }
    /**
     * If nth list is empty => ALL occurrences for that weekday.
     * Else pick the nth entries (1..5) from the monthly occurrences map.
     */
    private function pickNthOrAllWeekdays(array $weekDays, array $occurrences): array
    {
        $out = [];
        foreach ($occurrences as $weekday => $dates) {
            if (!array_key_exists($weekday, $weekDays)) continue;
            $nths = $weekDays[$weekday];
            // ALL occurrences for that weekday
            if (empty($nths)) {
                $out = array_merge($out, $dates);
                continue;
            }
            // Specific nths
            foreach ($nths as $nthToken) {
                $nth = (int) filter_var($nthToken, FILTER_SANITIZE_NUMBER_INT);
                if ($nth > 0 && isset($dates[$nth - 1])) {
                    $out[] = $dates[$nth - 1];
                }
            }
        }
        return $out;
    }
    public function deviceManagement()
    {
        // dd(1);
        return view('admin.setting.device-management.device');
    }
    public function viewAttendanceLogs($business_code, $device_sn)
    {
        if (!$business_code || !$device_sn) {
            return abort(404);
        }
        if ($business_code) {
            $business_id = Business::where('b_unique_id', $business_code)->first()->b_id;
        } else {
            return abort(404);
        }
        if ($device_sn) {
            $device = DeviceManagement::where('b_id', $business_id)->where('serial_name', $device_sn)->first('id');
            if (!$device) {
                return abort(404);
            }
            $deviceId =  $device->id ?? 0;
            // dd($deviceId);
        } else {
            return abort(404);
        }
        // dd($deviceId);
        // dd($business_id, $device_sn);
        try {
            // Define current month range
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            // Fetch attendance logs directly from DB
            $records = AttendanceRecord::where('atd_b_id', $business_id)
                ->where('atd_device_id', $deviceId)
                ->whereBetween('atd_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->with('fh_employee') // assuming relation exists
                ->orderBy('atd_date', 'desc')
                ->limit(2)
                ->get();
            // dd($records->toArray());
            \Log::info('Fetched attendance logs from DB', [
                'count' => $records->count(),
                'business_id' => $business_id,
                'device_sn' => $device_sn,
            ]);
            return view('admin.setting.device-management.device', [
                'records' => $records,
                'business_id' => $business_id,
                'device_sn' => $device_sn,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to load attendance logs from DB', [
                'error' => $e->getMessage(),
                'business_id' => $business_id,
                'device_sn' => $device_sn,
            ]);
            return view('admin.setting.device-management.device', [
                'records' => [],
                'business_id' => $business_id,
                'device_sn' => $device_sn,
            ]);
        }
    }
    public function download()
    {
        $file = public_path('packages/fixhr-device-connector.exe');
        if (file_exists($file)) {
            return response()->download($file, 'FixHR-Device-Connector.exe');
        }
        return abort(404, 'Installer not found.');
    }
}
