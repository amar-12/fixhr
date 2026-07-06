<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Grade;
use App\Models\MasterTable;
use App\Models\PolicyTadaCategory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Log;

class PolicyCategoryImport implements ToCollection
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
            if ($key === 0) {
                continue; // Skip header row
            }

            try {
                if (
                    !empty($row[1]) && !empty($row[2]) && !empty($row[3]) &&
                    !empty($row[4]) && !empty($row[5]) && !empty($row[6])
                ) {

                    $category_name = $row[1];
                    $grade_data = $row[2];
                    $department_data = $row[3];
                    $designation_data = explode(',', $row[4]);
                    $travel_type_data = explode(',', $row[5]);
                    $status = $row[6];

                    // Find Grade
                    if ($grade_data == 'Super Admin') {
                        $grade = Grade::where('g_name', trim($grade_data))
                            ->first();
                    } else {
                        $grade = Grade::where('g_b_id', $this->user->emp_b_id)
                            ->where('g_name', trim($grade_data))
                            ->first();
                    }

                    if (!$grade) {
                        Log::info("Grade not found for {$grade_data}");
                        continue;
                    }

                    // Find Department
                    if ($department_data == 'Super Admin') {
                        $department = Department::where('d_name', trim($department_data))->first();
                    } else {
                        $department = Department::where('d_b_id', $this->user->emp_b_id)
                            ->where('d_name', trim($department_data))
                            ->first();
                    }

                    if (!$department) {
                        Log::info("Department not found for {$department_data}");
                        continue;
                    }

                    // Find Designations
                    $designation_ids = Designation::where(function ($query) use ($designation_data) {
                        // Check if 'Super Admin' is part of the designations
                        if (in_array('Super Admin', array_map('trim', $designation_data))) {
                            $query->whereNull('dg_b_id'); // Include rows where dg_b_id is NULL
                        }
                        $query->where('dg_b_id', $this->user->emp_b_id); // Match user's emp_b_id
                    })
                        ->whereIn('dg_name', array_map('trim', $designation_data))
                        ->pluck('dg_id')
                        ->toArray();

                    if (empty($designation_ids)) {
                        Log::info("Designations not found for " . implode(', ', $designation_data));
                        continue;
                    }


                    // Find Travel Types
                    $type_ids = MasterTable::where('m_group', 'TRAVEL_TYPE')
                        ->whereIn('m_name', array_map('trim', $travel_type_data))
                        ->pluck('m_id')
                        ->toArray();
                    if (empty($type_ids)) {
                        Log::info("Travel types not found for " . implode(', ', $travel_type_data));
                        continue;
                    }

                    // Set Status
                    $status_data = ($status == 'Active') ? 1 : 0;

                    // Insert if all components are found
                    PolicyTadaCategory::create([
                        'ptc_name' => $category_name,
                        'ptc_d_id' => $department->d_id,
                        'ptc_dg_id' => json_encode($designation_ids),
                        'ptc_grade_id' => $grade->g_id,
                        'ptc_pttt_id' => json_encode($type_ids),
                        'ptc_status' => $status_data,
                        'ptc_b_id' => $this->user->emp_b_id,
                    ]);

                    $this->successfulImports++;
                    Log::info("Row {$key} successfully imported.");
                }
            } catch (\Exception $e) {
                Log::error("Import failed for row {$key}: {$e->getMessage()}");
                continue;
            }
        }
    }
}
