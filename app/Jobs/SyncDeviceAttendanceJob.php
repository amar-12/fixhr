<?php

namespace App\Jobs;

use App\Models\DeviceManagement;
use App\Models\Employee;
use App\Http\Controllers\Api\Attendance\PunchInApiController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Exception;
use Carbon\Carbon;

class SyncDeviceAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 900;
    public $backoff = [60, 120, 300];

    protected $deviceId;
    protected $startDate;
    protected $endDate;
    protected $isRangeSync = false;

    public function __construct(int $deviceId, ?string $startDate = null, ?string $endDate = null)
    {
        $this->deviceId = $deviceId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->isRangeSync = !empty($startDate) && !empty($endDate);
    }

    public function handle()
    {
        Auth::shouldUse('web');

        $device = DeviceManagement::find($this->deviceId);

        if (!$device) {
            Log::warning("Device not found", ['device_id' => $this->deviceId]);
            return;
        }

        $syncType = $this->isRangeSync ? 'manual_range' : 'auto';
        
        Log::info("Sync STARTED", [
            'device_id' => $device->id,
            'device_name' => $device->device_name,
            'sync_type' => $syncType,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);

        $syncCount  = 0;
        $errorCount = 0;

        try {
            $query = DB::connection('biometricfixhr_db')
                ->table('testing_att_punch')
                ->where('device_sn', $device->serial_name);

            // Apply date range filter if provided
            if ($this->isRangeSync) {
                $start = Carbon::parse($this->startDate)->startOfDay();
                $end = Carbon::parse($this->endDate)->endOfDay();
                
                $query->whereBetween('punch_date_time', [$start, $end]);
                
                Log::info("Applying date range filter", [
                    'start' => $start,
                    'end' => $end,
                    'range_days' => $start->diffInDays($end) + 1
                ]);
            }

            $bioRecords = $query->orderBy('punch_date_time')->get();

            if ($bioRecords->isEmpty()) {
                $message = $this->isRangeSync 
                    ? "No records found for date range {$this->startDate} to {$this->endDate}"
                    : "No new records found";
                    
                $this->updateDeviceSyncResult($device, $message);
                return;
            }

            // foreach ($bioRecords as $record) {
            //     try {
            //         $rawEmpCode = trim($record->emp_code);
            //         $pos = strrpos($rawEmpCode, 'B');

            //         if ($pos === false) {
            //             $errorCount++;
            //             continue;
            //         }

            //         $employeeCode = substr($rawEmpCode, 0, $pos);
            //         $businessId   = substr($rawEmpCode, $pos + 1);

            //         if (!ctype_digit($businessId)) {
            //             $errorCount++;
            //             continue;
            //         }

            //         $employee = Employee::where('emp_b_id', $businessId)
            //             ->where('emp_code', $employeeCode)
            //             ->first();

            //         if (!$employee) {
            //             $errorCount++;
            //             continue;
            //         }

            //         $fakeRequest = new Request([
            //             'is_system_sync'        => true,
            //             'atd_checkin_method_id' => 315,
            //             'device_sn'             => $device->serial_name,
            //             'atd_device_id'         => $device->id,
            //             'timestamp'             => $record->punch_date_time,
            //             'emp_code'              => $employeeCode,
            //             'business_id'           => $businessId,
            //         ]);

            //         $fakeRequest->setMethod('POST');

            //         $controller = app(PunchInApiController::class);
            //         $response   = $controller->storeDevice($fakeRequest);

            //         if (
            //             method_exists($response, 'getStatusCode') &&
            //             $response->getStatusCode() != 200
            //         ) {
            //             throw new Exception($response->getContent());
            //         }

            //         $syncCount++;
            //     } catch (Exception $e) {
            //         $errorCount++;
            //         Log::error("Failed punch", [
            //             'device_id' => $device->id,
            //             'raw_code'  => $record->emp_code,
            //             'timestamp' => $record->punch_date_time,
            //             'error'     => $e->getMessage(),
            //         ]);
            //     }
            // }

              foreach ($bioRecords as $record) {
                try {
                    $rawEmpCode = trim($record->emp_code);
                    $pos = strrpos($rawEmpCode, 'B');

                    if ($pos === false) {
                        $errorCount++;
                        continue;
                    }

                    $employeeCode = substr($rawEmpCode, 0, $pos);
                    $businessId   = substr($rawEmpCode, $pos + 1);

                    if (!ctype_digit($businessId)) {
                        $errorCount++;
                        continue;
                    }

                    $employee = Employee::where('emp_b_id', $businessId)
                        ->where('emp_code', $employeeCode)
                        ->first();

                    if (!$employee) {
                        $errorCount++;
                        continue;
                    }

                    // ⭐ SYSTEM SYNC FLAG (THIS FIXES AUTH)
                    $fakeRequest = new Request([
                        'is_system_sync'        => true,
                        'atd_checkin_method_id' => 315,
                        'device_sn'             => $device->serial_name,
                        'atd_device_id'         => $device->id,
                        'timestamp'             => $record->punch_date_time,
                        'emp_code'              => $employeeCode,
                        'business_id'           => $businessId,
                    ]);

                    $fakeRequest->setMethod('POST');

                    $controller = app(PunchInApiController::class);
                    $response   = $controller->storeDevice($fakeRequest);

                    if (
                        method_exists($response, 'getStatusCode') &&
                        $response->getStatusCode() != 200
                    ) {
                        throw new Exception($response->getContent());
                    }

                    $syncCount++;
                } catch (Exception $e) {

                    $errorCount++;

                    Log::error("Failed punch", [
                        'device_id' => $device->id,
                        'raw_code'  => $record->emp_code,
                        'timestamp' => $record->punch_date_time,
                        'error'     => $e->getMessage(),
                    ]);
                }
            }

            $resultMessage = $this->isRangeSync
                ? "Range Sync ({$this->startDate} to {$this->endDate}): Synced: {$syncCount}, Errors: {$errorCount}"
                : "Synced: {$syncCount}, Errors: {$errorCount}";

            $this->updateDeviceSyncResult($device, $resultMessage);
            
        } catch (Exception $e) {
            $errorMessage = $this->isRangeSync
                ? "Range sync failed: " . substr($e->getMessage(), 0, 200)
                : "Sync failed: " . substr($e->getMessage(), 0, 200);
                
            Log::error("Sync FAILED", [
                'device_id' => $device->id,
                'sync_type' => $syncType,
                'error'     => $e->getMessage()
            ]);

            $this->updateDeviceSyncResult($device, $errorMessage);
            throw $e;
        }
    }

    private function updateDeviceSyncResult(DeviceManagement $device, string $message)
    {
        $updateData = [
            'last_sync_at'     => now(),
            'last_sync_result' => $message,
        ];

        // Only update next_sync_at for auto sync, not for manual range sync
        if (!$this->isRangeSync && $device->auto_sync_enabled) {
            $updateData['next_sync_at'] = $device->calculateNextSyncTime();
        }

        $device->update($updateData);
    }

    public function failed(Exception $exception)
    {
        $device = DeviceManagement::find($this->deviceId);

        if ($device) {
            $errorMessage = $this->isRangeSync
                ? 'Range sync failed: ' . substr($exception->getMessage(), 0, 200)
                : 'Sync failed: ' . substr($exception->getMessage(), 0, 200);
                
            $device->update([
                'last_sync_result' => $errorMessage,
                'last_sync_at'     => now(),
            ]);
        }
    }
}