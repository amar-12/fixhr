<?php

namespace App\Imports;

use App\Models\MasterTable;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaLodging;
use App\Models\PolicyTadaTravelType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Log;

class LodgingImport implements ToCollection
{
    protected $user;
    public $successfulImports = 0;

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
                // Ensure that all necessary columns have data
                if (
                    !empty($row[1]) && !empty($row[2]) && !empty($row[3]) &&
                    !empty($row[4]) && !empty($row[5]) && !empty($row[6]) && !empty($row[7])
                ) {
                    // Extract data from each row
                    $policy_category_data = $row[1];
                    $travel_type_data = $row[2];
                    $city_type_data = $row[3];
                    $will_bill_s = $row[4];
                    $will_bill_d = $row[5];
                    $willout_bill_s = $row[6];
                    $willout_bill_d = $row[7];

                    // Fetch related data from the database
                    $policy_category = PolicyTadaCategory::where('ptc_b_id', $this->user->emp_b_id)
                        ->where('ptc_name', $policy_category_data)
                        ->where('ptc_status', 1)
                        ->first();

                    $type_name = MasterTable::where('m_group', 'TRAVEL_TYPE')
                        ->where('m_name', $travel_type_data)
                        ->first();

                    $travel_type = PolicyTadaTravelType::with('fh_travel_type')
                        ->where('pttt_b_id', $this->user->emp_b_id)
                        ->where('pttt_type_id', optional($type_name)->m_id)
                        ->where('pttt_status', 1)
                        ->first();

                    $cityType = MasterTable::where('m_group', 'CITY_TYPE')
                        ->where('m_name', $city_type_data)
                        ->first();

                    // Skip row if any of the required data is missing
                    if (!$policy_category || !$travel_type || !$cityType) {
                        continue;
                    }

                    // Check for existing lodging records to prevent duplicates
                    $existingLodging = PolicyTadaLodging::where('ptl_ptc_id', $policy_category->ptc_id)
                        ->where('ptl_pttt_id', $travel_type->pttt_id)
                        ->where('ptl_ct_type_id', $cityType->m_id)
                        ->where('ptl_b_id', $this->user->emp_b_id)
                        ->first();

                    if ($existingLodging) {
                        continue;
                    }

                    // Create new PolicyTadaLodging entry
                    PolicyTadaLodging::create([
                        'ptl_ptc_id' => $policy_category->ptc_id,
                        'ptl_pttt_id' => $travel_type->pttt_id,
                        'ptl_ct_type_id' => $cityType->m_id,
                        'ptl_sngl_w_bill' => $will_bill_s,
                        'ptl_sngl_wo_bill' => $willout_bill_s,
                        'ptl_dbl_w_bill' => $will_bill_d,
                        'ptl_dbl_wo_bill' => $willout_bill_d,
                        'ptl_b_id' => $this->user->emp_b_id,
                    ]);

                    $this->successfulImports++;
                }
            } catch (\Exception $e) {
                Log::error('Import failed for row ' . $key . ': ' . $e->getMessage());
                continue;
            }
        }
    }
}
