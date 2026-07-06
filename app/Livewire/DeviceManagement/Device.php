<?php

namespace App\Livewire\DeviceManagement;

use App\Exports\BusinessEmployee\BusinessEmployeeExport;
use App\Jobs\SyncDeviceAttendanceJob;
use App\Models\Branch;
use App\Models\DeviceManagement;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class Device extends Component
{
    use WithPagination;

    // Properties
    public $search = '';
    public $searchBranch = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortAsc = true;
    public $editing = false;
    public $businessId;

    // Device Form Properties
    public $device_id;
    public $device_name = '';
    public $short_name = '';
    public $serial_name = '';
    public $device_location = '';
    public $ip_address = '';
    public $company = '';
    public $com_key = '';
    public $machine_id = '';
    public $br_id = null;

    // Auto-sync Properties
    public $auto_sync_enabled = false;
    public $sync_schedule_type = 'daily';
    public $sync_time = '';
    public $sync_days = [];
    public $last_sync_at = '';
    public $last_sync_result = '';

    // Range Sync Properties
    public $showRangeSyncPopup = false;
    public $rangeStartDate = '';
    public $rangeEndDate = '';
    public $selectedDeviceId = null;
    
    // Sync Statistics
    public $syncCount = 0;
    public $errorCount = 0;
    public $totalSynced = 0;
    public $totalErrors = 0;

    // Delete Confirmation
    public $deviceToDelete;
    public $deviceToDeleteName;

    // Export Properties
    public $showExportModal = false;
    public $selectedEmployees = [];
    public $selectAll = false;
    public $employeeSearch = '';
    public $exportEmployees = [];

    // Constants
    protected $daysOfWeek = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday'
    ];

    protected $listeners = ['check-auto-sync' => 'checkAutoSync'];

    /**
     * Initialize component
     */
    public function mount()
    {
        $this->businessId = Auth::user()->emp_b_id;
        $this->sync_time = now()->format('H:i');
        $this->setDefaultDateRange();
        $this->loadExportEmployees();
    }

    /**
     * Set default date range for manual sync
     */
    private function setDefaultDateRange(): void
    {
        $this->rangeStartDate = now()->subDays(7)->format('Y-m-d');
        $this->rangeEndDate = now()->format('Y-m-d');
    }

    /**
     * Validation rules
     */
    protected function rules()
    {
        $rules = [
            'device_name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'serial_name' => 'required|string|max:255',
            'device_location' => 'nullable|string|max:255',
            'ip_address' => [
                'required',
                'ipv4',
                'max:50',
                Rule::unique('attendance_devices')->where(function ($query) {
                    return $query->where('b_id', $this->businessId)
                        ->whereNotNull('ip_address')
                        ->where('ip_address', '!=', '');
                })->ignore($this->device_id, 'id')
            ],
            'company' => 'nullable|string|max:255',
            'com_key' => 'required|string|max:100',
            'machine_id' => 'nullable|string|max:20',
            'br_id' => 'required|exists:branches,br_id',
            'auto_sync_enabled' => 'boolean',
            'sync_schedule_type' => 'required_if:auto_sync_enabled,true|in:daily,weekly',
            'sync_time' => 'required_if:auto_sync_enabled,true|date_format:H:i',
        ];

        if ($this->auto_sync_enabled && $this->sync_schedule_type === 'weekly') {
            $rules['sync_days'] = 'required|array|min:1';
            $rules['sync_days.*'] = 'integer|between:0,6';
        }

        return $rules;
    }

    /**
     * Custom validation messages
     */
    protected function messages()
    {
        return [
            'device_name.required' => 'The Device Name field is required.',
            'serial_name.required' => 'The Serial Name field is required.',
            'ip_address.required' => 'The IP Address field is required.',
            'ip_address.ipv4' => 'The IP Address must be a valid IPv4 address.',
            'ip_address.unique' => 'This IP address is already in use for another device in the same business.',
            'br_id.required' => 'The Branch field is required.',
            'br_id.exists' => 'The selected Branch is invalid.',
            'com_key.required' => 'The Communication Key field is required.',
            'sync_time.required_if' => 'Sync time is required when auto-sync is enabled.',
            'sync_days.required' => 'Please select at least one day for weekly sync.',
        ];
    }

    /**
     * Open date range sync popup
     */
    public function showSyncPopup($deviceId): void
    {
        $this->selectedDeviceId = $deviceId;
        $this->setDefaultDateRange();
        $this->showRangeSyncPopup = true;
    }

    /**
     * Close date range sync popup
     */
    public function closeSyncPopup(): void
    {
        $this->showRangeSyncPopup = false;
        $this->selectedDeviceId = null;
    }

    /**
     * Execute manual sync with date range
     */
    public function executeRangeSync(): void
    {
        $this->validateRangeSync();
        
        try {
            $device = $this->getSelectedDevice();
            $daysDifference = $this->calculateDaysDifference();
            
            SyncDeviceAttendanceJob::dispatch(
                $device->id, 
                $this->rangeStartDate, 
                $this->rangeEndDate
            );

            $this->showSuccessAlert(
                "Manual sync started for {$daysDifference} day(s) ({$this->rangeStartDate} to {$this->rangeEndDate})"
            );
            
            $this->closeSyncPopup();
        } catch (\Exception $e) {
            $this->handleError('Manual sync error', $e);
        }
    }

    /**
     * Quick sync without date range (latest data only)
     */
    public function quickSync($deviceSn): void
    {
        try {
            $device = DeviceManagement::where('serial_name', $deviceSn)
                ->where('b_id', $this->businessId)
                ->firstOrFail();

            SyncDeviceAttendanceJob::dispatch($device->id);

            $this->showSuccessAlert('Quick sync started! Syncing latest data...');
        } catch (\Exception $e) {
            $this->handleError('Quick sync error', $e);
        }
    }

    /**
     * Validate date range sync
     */
    private function validateRangeSync(): void
    {
        $this->validate([
            'rangeStartDate' => 'required|date|before_or_equal:rangeEndDate',
            'rangeEndDate' => 'required|date|after_or_equal:rangeStartDate',
        ], [
            'rangeStartDate.required' => 'Start date is required',
            'rangeEndDate.required' => 'End date is required',
            'rangeStartDate.before_or_equal' => 'Start date must be before or equal to end date',
            'rangeEndDate.after_or_equal' => 'End date must be after or equal to start date',
        ]);
    }

    /**
     * Get selected device for sync
     */
    private function getSelectedDevice()
    {
        $device = DeviceManagement::find($this->selectedDeviceId);
        
        if (!$device) {
            throw new \Exception('Device not found');
        }
        
        return $device;
    }

    /**
     * Calculate days difference between start and end dates
     */
    private function calculateDaysDifference(): int
    {
        $startDate = Carbon::parse($this->rangeStartDate);
        $endDate = Carbon::parse($this->rangeEndDate);
        
        return $startDate->diffInDays($endDate) + 1;
    }

    /**
     * Sort table by field
     */
    public function sortBy($field): void
    {
        $this->sortAsc = $this->sortField === $field ? !$this->sortAsc : true;
        $this->sortField = $field;
    }

    /**
     * Add new device
     */
    public function addDevice(): void
    {
        $this->resetForm();
        $this->dispatch('openModal', ['modalId' => 'deviceModal']);
    }

    /**
     * View device details
     */
    public function view($id): void
    {
        $this->fillFromModel(DeviceManagement::findOrFail($id));
        $this->editing = false;
        $this->dispatch('openModal', ['modalId' => 'viewDeviceModal']);
    }

    /**
     * Edit device
     */
    public function edit($id): void
    {
        $this->fillFromModel(DeviceManagement::findOrFail($id));
        $this->editing = true;
        $this->dispatch('openModal', ['modalId' => 'deviceModal']);
    }

    /**
     * Fill form from device model
     */
    private function fillFromModel($device): void
    {
        $this->device_id = $device->id;
        $this->device_name = $device->device_name;
        $this->short_name = $device->short_name;
        $this->serial_name = $device->serial_name;
        $this->device_location = $device->device_location;
        $this->ip_address = $device->ip_address;
        $this->company = $device->company;
        $this->com_key = (string) $device->com_key;
        $this->machine_id = $device->machine_id;
        $this->br_id = $device->br_id;
        $this->searchBranch = optional($device->branch)->br_name ?? '';

        // Auto-sync fields
        $this->auto_sync_enabled = (bool) $device->auto_sync_enabled;
        $this->sync_schedule_type = $device->sync_schedule_type ?? 'daily';
        $this->sync_time = $device->sync_time ? Carbon::parse($device->sync_time)->format('H:i') : now()->format('H:i');
        $this->sync_days = $device->sync_days ?? [];
        $this->last_sync_at = $device->last_sync_at;
    }

    /**
     * Submit device form
     */
    public function submit(): void
    {
        $validatedData = $this->validateFormData();
        
        try {
            $device = $this->device_id 
                ? DeviceManagement::findOrFail($this->device_id)
                : new DeviceManagement();

            $device->fill($validatedData);
            $device->b_id = $this->businessId;
            $device->save();

            // Always calculate next_sync_at after save
            $device->updateSyncSchedule();

            $message = $this->device_id ? 'Device updated successfully!' : 'Device created successfully!';
            
            $this->showSuccessAlert($message);
            $this->resetForm();
            $this->dispatch('closeModal', ['modalId' => 'deviceModal']);
        } catch (\Exception $e) {
            $this->handleError('Device submit error', $e);
        }
    }

    /**
     * Validate and prepare form data
     */
    private function validateFormData(): array
    {
        $validatedData = $this->validate();
        
        // Handle sync_days formatting
        if ($validatedData['sync_schedule_type'] !== 'weekly' || !$validatedData['auto_sync_enabled']) {
            $validatedData['sync_days'] = null;
        } else {
            $validatedData['sync_days'] = array_map('intval', $validatedData['sync_days'] ?? []);
        }

        // Ensure com_key is properly stored
        $validatedData['com_key'] = (string) $validatedData['com_key'];

        return $validatedData;
    }

    /**
     * Handle property updates
     */
    public function updated($property): void
    {
        // Update sync settings in real-time when editing existing device
        if ($this->device_id && $this->editing) {
            if (in_array($property, ['auto_sync_enabled', 'sync_time', 'sync_schedule_type', 'sync_days'])) {
                $this->updateDeviceSyncSettings();
            }
        }

        // Reset sync_days when switching from weekly to daily
        if ($property === 'sync_schedule_type' && $this->sync_schedule_type === 'daily') {
            $this->sync_days = [];
        }

        // Reset validation when changing sync settings
        if (in_array($property, ['auto_sync_enabled', 'sync_schedule_type'])) {
            $this->resetValidation();
        }

        // Load export employees when search changes
        if ($property === 'employeeSearch') {
            $this->loadExportEmployees();
        }
    }

    /**
     * Update device sync settings in real-time
     */
    private function updateDeviceSyncSettings(): void
    {
        try {
            $device = DeviceManagement::find($this->device_id);
            
            if ($device) {
                $updateData = [
                    'auto_sync_enabled' => $this->auto_sync_enabled,
                    'sync_schedule_type' => $this->sync_schedule_type,
                    'sync_time' => $this->sync_time,
                ];

                // Handle sync_days based on schedule type
                if ($this->sync_schedule_type === 'weekly' && $this->auto_sync_enabled) {
                    $updateData['sync_days'] = array_map('intval', $this->sync_days);
                } else {
                    $updateData['sync_days'] = null;
                }

                $device->update($updateData);
                $device->updateSyncSchedule();
                $this->fillFromModel($device->fresh());
            }
        } catch (\Exception $e) {
            $this->handleError('Failed to update device sync settings', $e);
        }
    }

    /**
     * Toggle auto-sync for device
     */
    public function toggleAutoSync($deviceId): void
    {
        try {
            $device = DeviceManagement::findOrFail($deviceId);
            
            $device->auto_sync_enabled = !$device->auto_sync_enabled;
            
            if ($device->auto_sync_enabled) {
                $device->updateSyncSchedule();
            } else {
                $device->next_sync_at = null;
                $device->save();
            }

            $status = $device->auto_sync_enabled ? 'enabled' : 'disabled';
            $message = "Auto sync {$status} for device: {$device->device_name}";
            
            $this->showSuccessAlert($message);
        } catch (\Exception $e) {
            $this->handleError('Toggle auto sync error', $e);
        }
    }

    /**
     * Confirm device deletion
     */
    public function confirmDelete($id): void
    {
        $device = DeviceManagement::findOrFail($id);
        $this->deviceToDelete = $id;
        $this->deviceToDeleteName = $device->device_name;
        $this->dispatch('showDeleteModal');
    }

    /**
     * Delete device
     */
    public function delete(): void
    {
        if ($this->deviceToDelete) {
            try {
                DeviceManagement::find($this->deviceToDelete)->delete();
                $this->showSuccessAlert('Device deleted successfully.');
                $this->resetDeleteConfirmation();
            } catch (\Exception $e) {
                $this->handleError('Device delete error', $e);
            }
        }
    }

    /**
     * Reset delete confirmation
     */
    private function resetDeleteConfirmation(): void
    {
        $this->deviceToDelete = null;
        $this->deviceToDeleteName = '';
    }

    /**
     * Select branch
     */
    public function selectBranch($id, $name): void
    {
        $this->br_id = $id;
        $this->searchBranch = $name;
    }

    /**
     * Reset form
     */
    public function resetForm(): void
    {
        $this->reset([
            'device_id',
            'device_name',
            'short_name',
            'serial_name',
            'device_location',
            'ip_address',
            'company',
            'com_key',
            'machine_id',
            'br_id',
            'searchBranch',
            'auto_sync_enabled',
            'sync_schedule_type',
            'sync_time',
            'sync_days',
            'last_sync_at',
            'last_sync_result',
            'syncCount',
            'errorCount',
            'totalSynced',
            'totalErrors',
        ]);
        
        $this->sync_time = now()->format('H:i');
        $this->resetErrorBag();
        $this->resetValidation();
    }

    /**
     * Cancel operation
     */
    public function cancel(): void
    {
        $this->resetForm();
        $this->editing = false;
        $this->dispatch('closeModal', ['modalId' => 'deviceModal']);
        $this->dispatch('closeModal', ['modalId' => 'viewDeviceModal']);
    }

    /**
     * Clear search
     */
    public function clearSearch(): void
    {
        $this->search = '';
    }

    /**
     * Load employees for export
     */
    public function loadExportEmployees(): void
    {
        $this->exportEmployees = Employee::query()
            ->where('emp_b_id', $this->businessId)
            ->where('emp_role_id', '<>', 1)
            ->where('emp_status', 71)
            ->when($this->employeeSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('emp_fname', 'like', '%' . $this->employeeSearch . '%')
                        ->orWhere('emp_lname', 'like', '%' . $this->employeeSearch . '%')
                        ->orWhere('emp_code', 'like', '%' . $this->employeeSearch . '%');
                });
            })
            ->select('emp_id', 'emp_code', 'emp_fname', 'emp_lname', 'emp_date_of_joining', 'emp_status')
            ->orderBy('emp_fname')
            ->get()
            ->toArray();
    }

    /**
     * Open export modal
     */
    public function openExportModal(): void
    {
        $this->showExportModal = true;
        $this->loadExportEmployees();
    }

    /**
     * Close export modal
     */
    public function closeExportModal(): void
    {
        $this->showExportModal = false;
        $this->selectedEmployees = [];
        $this->selectAll = false;
        $this->employeeSearch = '';
    }

    /**
     * Handle select all for export
     */
    public function updatedSelectAll($value): void
    {
        $this->selectedEmployees = $value 
            ? collect($this->exportEmployees)->pluck('emp_id')->toArray()
            : [];
    }

    /**
     * Download employee export
     */
    public function downloadBusinessEmployee()
    {
        if (empty($this->selectedEmployees)) {
            $this->dispatch('show-alert', [
                'type' => 'error', 
                'message' => 'Please select at least one employee to export.'
            ]);
            return;
        }

        $fileName = 'BusinessEmployee_' . now()->format('Y-m-d') . '.xlsx';
        
        return Excel::download(
            new BusinessEmployeeExport($this->businessId, $this->selectedEmployees), 
            $fileName
        );
    }

    /**
     * Check and dispatch auto-sync jobs
     */
    public function checkAutoSync(): void
    {
        $dueDevices = DeviceManagement::query()
            ->where('b_id', $this->businessId)
            ->where('auto_sync_enabled', true)
            ->where(function ($query) {
                $query->where('next_sync_at', '<=', now())
                    ->orWhereNull('next_sync_at');
            })
            ->get();

        $this->totalSynced = 0;
        $this->totalErrors = 0;

        foreach ($dueDevices as $device) {
            try {
                SyncDeviceAttendanceJob::dispatch($device->id);
                $this->totalSynced++;
                $device->updateSyncSchedule();
            } catch (\Exception $e) {
                Log::error("Auto-sync failed for device: {$device->serial_name}", [
                    'error' => $e->getMessage()
                ]);
                
                $this->totalErrors++;
                $device->update([
                    'last_sync_at' => now(),
                    'last_sync_result' => 'Error: ' . $e->getMessage(),
                ]);
            }
        }

        if ($this->totalSynced > 0 || $this->totalErrors > 0) {
            $this->dispatch('auto-sync-completed', [
                'synced' => $this->totalSynced,
                'errors' => $this->totalErrors
            ]);
        }
    }

    /**
     * Auto-sync all devices (manual trigger)
     */
    public function autoSyncAllDevices(): void
    {
        $this->checkAutoSync();

        if ($this->totalSynced > 0) {
            $this->showSuccessAlert("Auto-sync initiated for {$this->totalSynced} devices.");
        } else {
            $this->dispatch('show-alert', [
                'type' => 'info', 
                'message' => 'No devices are due for sync at this time.'
            ]);
        }
    }

    /**
     * Handle errors
     */
    private function handleError(string $context, \Exception $e): void
    {
        Log::error($context, ['error' => $e->getMessage()]);
        
        $this->dispatch('show-alert', [
            'type' => 'error', 
            'message' => 'Failed: ' . $e->getMessage()
        ]);
    }

    /**
     * Show success alert
     */
    private function showSuccessAlert(string $message): void
    {
        $this->dispatch('show-alert', [
            'type' => 'success', 
            'message' => $message
        ]);
    }

    /**
     * Render component
     */
    public function render()
    {
        $devices = $this->getDevicesQuery()->paginate($this->perPage);
        $branches = $this->getBranchesQuery()->get();

        return view('livewire.device-management.device', [
            'devices' => $devices,
            'branches' => $branches,
            'daysOfWeek' => $this->daysOfWeek,
        ]);
    }

    /**
     * Get devices query
     */
    private function getDevicesQuery()
    {
        return DeviceManagement::query()
            ->where('b_id', $this->businessId)
            ->with('business')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('device_name', 'like', '%' . $this->search . '%')
                        ->orWhere('short_name', 'like', '%' . $this->search . '%')
                        ->orWhere('serial_name', 'like', '%' . $this->search . '%')
                        ->orWhere('device_location', 'like', '%' . $this->search . '%')
                        ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                        ->orWhere('company', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');
    }

    /**
     * Get branches query
     */
    private function getBranchesQuery()
    {
        return Branch::query()
            ->where('br_b_id', $this->businessId)
            ->select('br_id', 'br_name')
            ->when(
                strlen($this->searchBranch) >= 1 && !$this->br_id,
                fn($q) => $q->where('br_name', 'like', "%{$this->searchBranch}%")
            )
            ->limit(100);
    }
}