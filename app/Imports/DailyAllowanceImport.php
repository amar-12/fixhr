<?php
namespace App\Imports;

use App\Models\MasterTable;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaDailyAllowance;
use App\Models\PolicyTadaTravelType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class DailyAllowanceImport implements ToCollection
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
                if (!empty($row[1]) && !empty($row[2]) && !empty($row[3]) && !empty($row[7])) {

                    $policy_category_data = $row[1];
                    $travel_type_data = $row[2];
                    $daily_allowance_type_data = $row[3];
                    $hours = $row[4] ?? NULL;
                    $km_from = $row[5] ?? NULL;
                    $km_to = $row[6] ?? NULL;
                    $da_amount = $row[7];

                    $policy_category = PolicyTadaCategory::where('ptc_b_id', $this->user->emp_b_id)
                        ->where('ptc_name', $policy_category_data)
                        ->where('ptc_status', 1)
                        ->first();

                    $type_name = MasterTable::where('m_group', 'TRAVEL_TYPE')
                        ->where('m_name', $travel_type_data)
                        ->first();

                    $travel_type = PolicyTadaTravelType::with('fh_travel_type')
                        ->where('pttt_b_id', $this->user->emp_b_id)
                        ->where('pttt_type_id', $type_name->m_id)
                        ->where('pttt_status', 1)
                        ->first();

                    $daily_allowance_type = MasterTable::where('m_group', 'DAILY_ALLOWANCE')
                        ->where('m_name', $daily_allowance_type_data)
                        ->first();

                    if (!$policy_category || !$travel_type || !$daily_allowance_type) {
                        continue; // These are not treated as errors, so no exception here
                    }

                    $existingVehicle = PolicyTadaDailyAllowance::where('ptda_ptc_id', $policy_category->ptc_id)
                        ->where('ptda_pttt_id', $travel_type->pttt_id)
                        ->where('ptda_da_cal_type_id', $daily_allowance_type->m_id)
                        ->where('ptda_b_id', $this->user->emp_b_id)
                        ->first();

                    if ($existingVehicle) {
                        continue; // No exception for existing records
                    }

                    $ptda_limit = '';
                    if ($daily_allowance_type->m_id == 239) {
                        $ptda_limit = $hours;
                    } elseif ($daily_allowance_type->m_id == 240) {
                        $ptda_limit = $km_from . '|' . $km_to;
                    }

                    PolicyTadaDailyAllowance::create([
                        'ptda_ptc_id' => $policy_category->ptc_id,
                        'ptda_pttt_id' => $travel_type->pttt_id,
                        'ptda_da_amount' => $da_amount,
                        'ptda_da_cal_limit' => $ptda_limit,
                        'ptda_da_cal_type_id' => $daily_allowance_type->m_id,
                        'ptda_b_id' => $this->user->emp_b_id,
                    ]);

                    $this->successfulImports++; // Increment the count
                }
            } catch (\Exception $e) {
                Log::error('Import failed for row ' . $key . ': ' . $e->getMessage());
                continue;
            }
        }
    }
}
