<?php
namespace App\Livewire\DeviceManagement;

use App\Models\Branch;
use App\Models\Business;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DeviceManagement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Device extends Component
{
    use WithPagination;

    public $search = '';
    public $searchBranch = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortAsc = true;
    public $editing = false;

    // Device properties
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

    // For delete confirmation
    public $deviceToDelete;
    public $deviceToDeleteName;
    public $businessId = null;

    public function mount()
    {
        $this->businessId = Auth::user()->emp_b_id;
    }


    protected function rules()
{
    return [
        'device_name' => 'required|string|max:255',
        'short_name' => 'nullable|string|max:100',

        // ✅ No uniqueness check for serial_name anymore
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
        'com_key' => 'required|integer',
        'machine_id' => 'nullable|string',
        'br_id' => 'required|exists:branches,br_id',
    ];
}


    protected $messages = [
        'device_name.required' => 'The Device Name field is required.',
        'serial_name.required' => 'The Serial Name field is required.',
        'serial_name.unique' => 'This serial name is already in use.',
        'ip_address.required' => 'The IP Address field is required.',
        'ip_address.ipv4' => 'The IP Address must be a valid IPv4 address.',
        'ip_address.unique' => 'This IP address is already in use for another device in the same business.',
        'br_id.required' => 'The Branch field is required.',
        'br_id.exists' => 'The selected Branch is invalid.',
        'com_key.required' => 'The Communication Key field is required.',
        'com_key.integer' => 'The Communication Key must be an integer.',
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }
        $this->sortField = $field;
    }

    public function addDevice()
    {
        $this->resetForm();
        $this->editing = false;
        $this->dispatch('openModal', ['modalId' => 'deviceModal']);
    }

    public function view($id)
    {
        $device = DeviceManagement::findOrFail($id);
        $this->fillFromModel($device);
        $this->editing = false;
        $this->dispatch('openModal', ['modalId' => 'viewDeviceModal']);
    }

    public function edit($id)
    {
        $device = DeviceManagement::findOrFail($id);
        $this->fillFromModel($device);
        $this->editing = true;
        $this->dispatch('openModal', ['modalId' => 'deviceModal']);
    }

    private function fillFromModel($device)
    {
        $this->device_id = $device->id;
        $this->device_name = $device->device_name;
        $this->short_name = $device->short_name;
        $this->serial_name = $device->serial_name;
        $this->device_location = $device->device_location;
        $this->ip_address = $device->ip_address;
        $this->company = $device->company;
        $this->com_key = $device->com_key;
        $this->machine_id = $device->machine_id;
        $this->br_id = $device->br_id;
        $this->searchBranch = optional($device->branch)->br_name ?? '';
    }

    public function submit()
    {
        $validatedData = $this->validate();
        $validatedData['b_id'] = $this->businessId;

        if ($this->device_id) {
            DeviceManagement::find($this->device_id)->update($validatedData);
            $message = 'Device updated successfully!';
        } else {
            DeviceManagement::create($validatedData);
            $message = 'Device created successfully!';
        }

        $this->dispatch('show-alert', ['type' => 'success', 'message' => $message]);
        $this->resetForm();
        $this->editing = false;
        $this->dispatch('closeModal', ['modalId' => 'deviceModal']);
    }

    public function confirmDelete($id)
    {
        $device = DeviceManagement::findOrFail($id);
        $this->deviceToDelete = $id;
        $this->deviceToDeleteName = $device->device_name;
        $this->dispatch('showDeleteModal');
    }

    public function delete()
    {
        if ($this->deviceToDelete) {
            DeviceManagement::find($this->deviceToDelete)->delete();
            $this->dispatch('show-alert', ['type' => 'success', 'message' => 'Device deleted successfully.']);
            $this->deviceToDelete = null;
            $this->deviceToDeleteName = '';
        }
    }

    public function selectBranch($id, $name)
    {
        $this->br_id = $id;
        $this->searchBranch = $name;
    }

    public function resetForm()
    {
        $this->reset([
            'device_id', 'device_name', 'short_name', 'serial_name',
            'device_location', 'ip_address', 'company', 'com_key',
            'machine_id', 'br_id', 'searchBranch',
        ]);
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function cancel()
    {
        $this->resetForm();
        $this->editing = false;
        $this->dispatch('closeModal', ['modalId' => 'deviceModal']);
        $this->dispatch('closeModal', ['modalId' => 'viewDeviceModal']);
    }

    public function clearSearch()
    {
        $this->search = '';
    }

    public function dismissFlash()
    {
        session()->forget('message');
        session()->forget('error');
    }

    public function render()
    {
       $devices = DeviceManagement::query()
    ->where('b_id', $this->businessId)
    ->with('business') // eager load the related business
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
    ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
    ->paginate($this->perPage);


            //add b_unique_id
          

        $branches = Branch::where('br_b_id', $this->businessId)
            ->select('br_id', 'br_name')
            ->when(strlen($this->searchBranch) >= 1 && !$this->br_id,
                fn($q) => $q->where('br_name', 'like', "%{$this->searchBranch}%")
            )
            ->limit(100)
            ->get();

        return view('livewire.device-management.device', [
            'devices' => $devices,
            'branches' => $branches
        ]);
    }
}