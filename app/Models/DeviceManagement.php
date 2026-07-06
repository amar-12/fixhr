<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeviceManagement extends Model
{
    protected $table = 'attendance_devices';

    protected $fillable = [
        'device_name', 'short_name', 'serial_name', 'device_location',
        'ip_address', 'company', 'com_key', 'machine_id', 'br_id', 'b_id',
        'auto_sync_enabled', 'sync_schedule_type', 'sync_time', 'sync_days',
        'last_sync_at', 'last_sync_result', 'next_sync_at'
    ];

    protected $casts = [
        'auto_sync_enabled' => 'boolean',
        'com_key' => 'string', // Changed from integer to string
        'sync_days' => 'array',
        'last_sync_at' => 'datetime',
        'next_sync_at' => 'datetime',
        'sync_time' => 'string', // Add this cast
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'br_id', 'br_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'b_id');
    }

    public function calculateNextSyncTime()
{
    if (!$this->auto_sync_enabled || !$this->sync_time) {
        return null;
    }

    $now = now();
    
    // Handle sync_time format - it might be stored as "H:i" string
    try {
        if (strlen($this->sync_time) <= 5) { // Format like "14:30"
            $syncTime = Carbon::createFromFormat('H:i', $this->sync_time);
        } else {
            $syncTime = Carbon::parse($this->sync_time);
        }
    } catch (\Exception $e) {
        Log::error('Invalid sync time format', [
            'device_id' => $this->id,
            'sync_time' => $this->sync_time,
            'error' => $e->getMessage()
        ]);
        return null;
    }

    $nextSync = $now->copy()->setTime($syncTime->hour, $syncTime->minute, 0);

    if ($this->sync_schedule_type === 'weekly' && !empty($this->sync_days)) {
        $syncDays = array_map('intval', $this->sync_days);
        $syncDays = array_filter($syncDays, function($day) {
            return $day >= 0 && $day <= 6;
        });

        if (empty($syncDays)) {
            return null;
        }

        // Check if today is a sync day and time hasn't passed
        if (in_array($nextSync->dayOfWeek, $syncDays)) {
            if ($nextSync->greaterThan($now)) {
                return $nextSync;
            }
        }

        // Find next sync day
        for ($i = 1; $i <= 7; $i++) {
            $checkDate = $now->copy()->addDays($i);
            if (in_array($checkDate->dayOfWeek, $syncDays)) {
                return $checkDate->setTime($syncTime->hour, $syncTime->minute, 0);
            }
        }
    } else {
        // Daily schedule - if time passed today, schedule for tomorrow
        if ($nextSync->lte($now)) {
            return $nextSync->addDay();
        }
    }

    return $nextSync;
}

    public function shouldSyncNow(): bool
    {
        if (!$this->auto_sync_enabled || !$this->next_sync_at) {
            return false;
        }

        return now()->greaterThanOrEqualTo($this->next_sync_at);
    }

    public function getSyncStatusAttribute()
    {
        if (!$this->auto_sync_enabled) {
            return ['status' => 'disabled', 'color' => 'secondary', 'text' => 'Disabled'];
        }

        if (!$this->next_sync_at) {
            return ['status' => 'error', 'color' => 'danger', 'text' => 'No schedule'];
        }

        if ($this->last_sync_at) {
            $lastSyncText = $this->last_sync_at->diffForHumans();
            return [
                'status' => 'active',
                'color' => 'success',
                'text' => 'Last: ' . $lastSyncText
            ];
        }

        return ['status' => 'pending', 'color' => 'warning', 'text' => 'Pending first sync'];
    }

    public function getNextSyncTimeAttribute()
    {
        if (!$this->auto_sync_enabled || !$this->next_sync_at) {
            return 'Not scheduled';
        }

        return $this->next_sync_at->format('M j, g:i A');
    }

    // Helper method to update sync time immediately
    public function updateSyncSchedule()
    {
        $this->next_sync_at = $this->calculateNextSyncTime();
        $this->save();
        
        Log::info('Sync schedule updated', [
            'device_id' => $this->id,
            'device_name' => $this->device_name,
            'next_sync_at' => $this->next_sync_at,
            'auto_sync_enabled' => $this->auto_sync_enabled,
            'sync_time' => $this->sync_time,
            'sync_schedule_type' => $this->sync_schedule_type
        ]);
    }



    public function debugSyncSchedule()
{
    Log::info('=== DEBUG SYNC SCHEDULE ===');
    Log::info('Device: ' . $this->device_name);
    Log::info('Auto Sync Enabled: ' . ($this->auto_sync_enabled ? 'Yes' : 'No'));
    Log::info('Sync Time: ' . $this->sync_time);
    Log::info('Schedule Type: ' . $this->sync_schedule_type);
    Log::info('Sync Days: ' . json_encode($this->sync_days));
    Log::info('Next Sync At: ' . ($this->next_sync_at ? $this->next_sync_at->format('Y-m-d H:i:s') : 'NULL'));
    Log::info('Now: ' . now()->format('Y-m-d H:i:s'));
    Log::info('Should Sync Now: ' . ($this->shouldSyncNow() ? 'Yes' : 'No'));
    
    $nextSync = $this->calculateNextSyncTime();
    Log::info('Calculated Next Sync: ' . ($nextSync ? $nextSync->format('Y-m-d H:i:s') : 'NULL'));
}
}