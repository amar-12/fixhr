<?php

namespace App\Imports;

use App\Models\MasterTable;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaTravelMode;
use App\Models\PolicyTadaTravelType;
use App\Models\PolicyTadaTravelVehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ErrorExport;

class VehicleImport implements ToCollection
{
    protected $user;
    public $successfulImports = 0; // Add this property
    protected $errorRows = [];
    public $errorCount = 0;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function collection(Collection $collection)
    {
        foreach ($collection as $key => $row) {
            // Skip the header row
            if ($key === 0) {
                continue;
            }

            try {
                if (!empty($row[1]) && !empty($row[2]) && !empty($row[3]) && !empty($row[4]) && !empty($row[5])) {

                    $travel_type = $row[1];
                    $travel_mode = $row[2];
                    $vehicle_type = $row[3];
                    $claim_type = $row[4];
                    $policy_cat = $row[5];
                    $vehicle_class = $row[6] ?? null;
                    $eligibility_limit = $row[7] ?? 0;
                    $conveyance_is = $row[8] ?? 0;

                    // Fetch related data
                    $master_travel_type = MasterTable::where('m_group', 'TRAVEL_TYPE')->where('m_name', $travel_type)->first();
                    $type = PolicyTadaTravelType::where('pttt_type_id', $master_travel_type->m_id)->where('pttt_b_id', $this->user->emp_b_id)->first();
                    $master_mode = MasterTable::where('m_group', 'TRAVEL_MODE')->where('m_name', $travel_mode)->first();
                    $mode = PolicyTadaTravelMode::where('pttm_pttt_id', $type->pttt_id)->where('pttm_by_mode_id', $master_mode->m_id)->where('pttm_b_id', $this->user->emp_b_id)->first();
                    $vehicle = MasterTable::where('m_group', 'VEHICLE')->where('m_name', $vehicle_type)->first();
                    $policy = PolicyTadaCategory::where('ptc_name', $policy_cat)->where('ptc_b_id', $this->user->emp_b_id)->where('ptc_status', 1)->first();
                    $class = !empty($vehicle_class) ? MasterTable::where('m_group', 'TRAVEL_CLASS')->where('m_name', $vehicle_class)->first() : null;
                    $claim = MasterTable::where('m_group', 'CLAIM_TYPE')->where('m_name', $claim_type)->first();
                    $owner = MasterTable::whereIn('m_group', ['TRAVEL_CLASS', 'VEHICLE_OWNER'])->whereJsonContains('m_description', (int) $vehicle->m_id)->first();

                    // Skip if necessary models are missing
                    if (!$type || !$mode || !$vehicle || !$policy || !$claim) {
                        Log::warning("Skipping row $key due to missing lookup. Data:", $row->toArray());
                        $this->addErrorRow($key, $row, 'Missing required lookup data');
                        continue;
                    }

                    // Check for existing record
                    $existingVehicle = PolicyTadaTravelVehicle::where('pttv_pttm_id', $mode->pttm_id)
                        ->where('pttv_vehicle_id', $vehicle->m_id)
                        ->where('pttv_claim_type_id', $claim->m_id)
                        ->where('pttv_ptc_id', $policy->ptc_id)
                        ->where('pttv_b_id', $this->user->emp_b_id)
                        ->when($class, function ($query) use ($class) {
                            return $query->where('pttv_class_id', $class->m_id);
                        })
                        ->first();

                    if ($existingVehicle) {
                        Log::info("Skipping duplicate vehicle at row $key.");
                        $this->addErrorRow($key, $row, 'Duplicate entry. Vehicle already exists.');
                        continue;
                    }

                    // Create a new PolicyTadaTravelVehicle record
                    PolicyTadaTravelVehicle::create([
                        'pttv_pttm_id' => $mode->pttm_id,
                        'pttv_vehicle_id' => $vehicle->m_id,
                        'pttv_owner_id' => $owner->m_id,
                        'pttv_class_id' => $class->m_id ?? null,
                        'pttv_claim_type_id' => $claim->m_id,
                        'pttv_ptc_id' => $policy->ptc_id,
                        'pttv_eligibility' => $eligibility_limit,
                        'pttv_is_conveyance' => $conveyance_is,
                        'pttv_b_id' => $this->user->emp_b_id,
                    ]);

                    $this->successfulImports++; // Increment the count
                }
            } catch (\Exception $e) {
                Log::error('Import failed for row ' . $key . ': ' . $e->getMessage());
                continue;
            }
        }

        // Export failed rows if any
        if (!empty($this->errorRows)) {
            // Format error rows as messages
            $formattedErrors = [];

            foreach ($this->errorRows as $row) {
                $rowNumber = $row['Row'] ?? 'N/A';
                $errorMsg = $row['Error'] ?? 'Unknown error';
                $formattedErrors[] = "Row {$rowNumber}: {$errorMsg}";
            }

            // Generate file and save
            $fileName = 'tada_policy_import_errors/error_log_' . now()->format('Ymd_His') . '.xlsx';
            \Excel::store(new ErrorExport($formattedErrors), $fileName, 'public');
            $this->errorFileName = $fileName;
            Log::info("Error log saved to: $fileName");
        }
    }

    protected function addErrorRow($key, $row, $message)
    {
        $this->errorRows[] = [
            'Row' => $key + 1,
            'Travel Type' => $row[1] ?? '',
            'Travel Mode' => $row[2] ?? '',
            'Vehicle Type' => $row[3] ?? '',
            'Claim Type' => $row[4] ?? '',
            'Policy Category' => $row[5] ?? '',
            'Vehicle Class' => $row[6] ?? '',
            'Eligibility Limit' => $row[7] ?? '',
            'Conveyance' => $row[8] ?? '',
            'Error' => $message,
        ];
        $this->errorCount++;
    }
}
