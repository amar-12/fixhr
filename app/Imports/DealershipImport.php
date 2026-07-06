<?php

namespace App\Imports;

use App\Models\Dealership;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Exports\ErrorExport;
use Maatwebsite\Excel\Facades\Excel;

class DealershipImport implements ToCollection
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
            'Dealership Name*',
            'Dealership Code*'
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

            $dealership_name = trim($row[1]) ?? null;
            $dealership_code = trim($row[2]) ?? null;

            $rowErrors = [];
            if (!$dealership_name) {
                $rowErrors[] = "Dealership Name '{$dealership_name}' not found.";
            }
            if (!$dealership_code) {
                $rowErrors[] = "Dealership Code '{$dealership_code}' not found.";
            }

            // Unique check
            if (
                !empty($dealership_code) &&
                Dealership::where('dlr_code', $dealership_code)
                      ->where('dlr_b_id', $this->user->emp_b_id)
                      ->exists()
            ) {
                $rowErrors[] = "Dealership Code '{$dealership_code}' already exists.";
            }

            if (!empty($rowErrors)) {
                $unsuccessfulCount++;
                $this->errorMessages[] = "Row " . ($key + 1) . ": " . implode(' ', $rowErrors);
                continue;
            }

            try {
                Dealership::create([
                    'dlr_b_id' => $this->user->emp_b_id,
                    'dlr_name' => $dealership_name,
                    'dlr_code' => $dealership_code,
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
