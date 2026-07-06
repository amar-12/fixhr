<?php

namespace App\Imports;

use App\Models\Branch;
use App\Models\Country;
use App\Models\State;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Exports\ErrorExport;
use Maatwebsite\Excel\Facades\Excel;

class BranchImport implements ToCollection
{
    public $errorMessages = [];
    protected $user;
    protected $successRows = [];

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function collection(Collection $rows)
    {
        $expectedColumns = [
            'S. No.*',
            'Branch Code*',
            'Branch Name*',
            'Branch Email*',
            'Country*',
            'State*',
            'Branch Address*',
            'Range Limit in meter',
            'Is Active',
            'Wifi Address',
            'Is Wifi Restricted',
        ];

        $successfulCount = 0;
        $unsuccessfulCount = 0;

        foreach ($rows as $key => $row) {
            if ($key === 0) {
                $headers = array_map('trim', $row->toArray());

                $missing = array_diff($expectedColumns, $headers);
                $extra = array_diff($headers, $expectedColumns);

                if ($missing || $extra) {
                    $msg = "Invalid column headers.";
                    if ($missing) {
                        $msg .= " Missing: " . implode(', ', $missing) . ".";
                    }
                    if ($extra) {
                        $msg .= " Unexpected: " . implode(', ', $extra) . ".";
                    }
                    throw new \Exception($msg);
                }
                continue;
            }

            $branch_code    = trim($row[1]) ?? null;
            $branch_name    = trim($row[2]) ?? null;
            $branch_email   = trim($row[3]) ?? null;
            $countryName    = trim($row[4]) ?? null;
            $stateName      = trim($row[5]) ?? null;
            $branch_address = trim($row[6]) ?? null;
            $range_limit    = trim($row[7]) ?? null;
            $is_active      = (strtolower(trim($row[8])) === 'yes') ? 1 : 0;
            $wifi_address   = trim($row[9]) ?? null;
            $wifi_restrict  = (strtolower(trim($row[10])) === 'yes') ? 1 : 0;

            $rowErrors = [];

            // Required Field Check
            $requiredFields = [
                'Branch Code'    => $branch_code,
                'Branch Name'    => $branch_name,
                'Branch Email'   => $branch_email,
                'Country'        => $countryName,
                'State'          => $stateName,
                'Branch Address' => $branch_address,
            ];

            foreach ($requiredFields as $field => $value) {
                if (empty($value)) {
                    $rowErrors[] = "$field is required.";
                }
            }

            // Unique check
            if (
                !empty($branch_code) &&
                Branch::where('br_code', $branch_code)
                      ->where('br_b_id', $this->user->emp_b_id)
                      ->exists()
            ) {
                $rowErrors[] = "Branch Code '$branch_code' already exists.";
            }


            $country = Country::where('c_name', $countryName)->first();
            $country_id = null;
            $state_id = null;

            if (!$country) {
                $rowErrors[] = "Country '{$country}' not found.";
            } else {
                $country_id = $country->c_id;
            }
            $state = State::where('s_name', $stateName)->first();
            if (!$state) {
                $rowErrors[] = "State '{$state}' not found.";
            } else {
                $state_id = $state->s_id;
            }

            if (!empty($rowErrors)) {
                $unsuccessfulCount++;
                $this->errorMessages[] = "Row " . ($key + 1) . ": " . implode(' ', $rowErrors);
                continue;
            }

            try {
                Branch::create([
                    'br_b_id' => $this->user->emp_b_id,
                    'br_code' => $branch_code,
                    'br_name' => $branch_name,
                    'br_email' => $branch_email,
                    'br_c_id' => $country_id,
                    'br_s_id' => $state_id,
                    'br_address' => $branch_address,
                    'br_range_limit' => $range_limit,
                    'br_is_active' => $is_active,
                    'br_wifi_address' => $wifi_address,
                    'br_is_wifi_restricted' => $wifi_restrict,
                ]);

                $successfulCount++;
                $this->successRows[] = $row->toArray();
            } catch (\Exception $e) {
                $unsuccessfulCount++;
                $this->errorMessages[] = "Row " . ($key + 1) . ": Exception - " . $e->getMessage();
            }
        }

        // Return response with the results
        if (!empty($this->errorMessages)) {
            $this->errorMessages[] = "Successfully records imported count: {$successfulCount} & Unsuccessfully records imported count: {$unsuccessfulCount} ";
            return Excel::download(new ErrorExport($this->errorMessages), 'import_errors.xlsx');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Import completed successfully!',
            'successful_count' => $successfulCount,  // Return successful count
            'unsuccessful_count' => $unsuccessfulCount,  // Return unsuccessful count
        ]);
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    public function getSuccessRows()
    {
        return $this->successRows;
    }
}
