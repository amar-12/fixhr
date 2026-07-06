<?php

namespace App\Console\Commands;

use App\Jobs\SyncDeviceAttendanceJob;
use App\Models\DeviceManagement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use function Symfony\Component\Clock\now;

class CheckDeviceSync extends Command
{
    protected $signature = 'devices:check-sync';
    protected $description = 'Check and dispatch sync jobs for devices that are due';

    public function handle()
    {
        // Log::info('=== ENHANCED AUTO SYNC CHECK STARTED ===', ['time' => now()]);

        $dueDevices = DeviceManagement::where('auto_sync_enabled', true)
            ->where(function ($query) {
                $query->where('next_sync_at', '<=', now())
                    ->orWhereNull('next_sync_at');
            })
            ->get();


        Log::info("Found {$dueDevices->count()} devices due for sync");

        if ($dueDevices->isEmpty()) {
            Log::info('No devices due for sync at this time');
            $this->info('No devices due for sync');
            return Command::SUCCESS;
        }

        foreach ($dueDevices as $device) {
            try {
                Log::info('Dispatching sync job for device', [
                    'device_id' => $device->id,
                    'device_name' => $device->device_name,
                    'next_sync_at' => $device->next_sync_at,
                    'current_time' => now(),
                    'is_due' => $device->shouldSyncNow()
                ]);

                // Double check if device should sync
                if ($device->shouldSyncNow()) {
                    SyncDeviceAttendanceJob::dispatch($device->id);
                    $this->info("✓ Dispatched sync job for: {$device->device_name}");

                    // Update next sync time
                    $device->updateSyncSchedule();
                } else {
                    Log::warning('Device not actually due for sync', [
                        'device_id' => $device->id,
                        'next_sync_at' => $device->next_sync_at,
                        'now' => now()
                    ]);
                }
            } catch (\Exception $e) {
                $this->error("✗ Failed for device {$device->id}: " . $e->getMessage());
                Log::error("Failed to process device sync", [
                    'device_id' => $device->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        Log::info('=== AUTO SYNC CHECK COMPLETED ===');
        return Command::SUCCESS;
    }
}
