<?php

namespace App\Imports;

use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Exports\ErrorExport;
use Maatwebsite\Excel\Facades\Excel;

class RoleImport implements ToCollection
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
            'Role Name*',
            'Role Description*'
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

            $role_name    = trim($row[1]) ?? null;
            $role_desc    = trim($row[2]) ?? null;

            $rowErrors = [];

            // Required Field Check
            if (!$role_name) {
                $rowErrors[] = "Role Name '{$role_name}' not found.";
            }

            // Unique check
            if (
                !empty($role_name) &&
                Role::where('role_name', $role_name)
                      ->where('role_b_id', $this->user->emp_b_id)
                      ->exists()
            ) {
                $rowErrors[] = "Role Name '{$role_name}' already exists.";
            }

            if (!empty($rowErrors)) {
                $unsuccessfulCount++;
                $this->errorMessages[] = "Row " . ($key + 1) . ": " . implode(' ', $rowErrors);
                continue;
            }

            try {
                Role::create([
                    'role_b_id' => $this->user->emp_b_id,
                    'role_name' => $role_name,
                    'role_description' => $role_desc
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
