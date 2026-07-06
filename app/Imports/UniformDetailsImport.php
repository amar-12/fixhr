<?php

namespace App\Imports;

use App\Models\UniformDetail;
use App\Models\Employee; // Assuming this is your model
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Exports\ErrorExport;
use Maatwebsite\Excel\Facades\Excel;

class UniformDetailsImport implements ToCollection
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
            'S.No.',
            'Employee Name',
            'Employee Code',
            'Uniform Type',
            'Shirt Size',
            'Pant Size',
            'Shoe Size',
            'Headgear Type',
            'Headgear Size',
            'Issue Date',
            'Replacement Due Date',
            'Issued By',
            'Remarks',
            'Status',
            'Photo Path',
            'Uniform Color',
            'Shirt Color',
            'Pant Color',
            'Shoe Color',
            'Headgear Color',
        ];

        $successfulCount = 0;
        $unsuccessfulCount = 0;


        foreach ($rows as $key => $row) {
            if ($key == 0) {
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

                    $this->errorMessages[] = $msg;
                    return;
                }
                continue;
            }

            $employeeName     = trim($row[1]) ?? null;
            $employeeCode     = trim($row[2]) ?? null;
            $uniformType      = trim($row[3]) ?? null;
            $shirtSize        = trim($row[4]) ?? null;
            $pantSize         = trim($row[5]) ?? null;
            $shoeSize         = trim($row[6]) ?? null;
            $headgearType     = trim($row[7]) ?? null;
            $headgearSize     = trim($row[8]) ?? null;
            $issueDate        = trim($row[9]) ?? null;
            $replacementDate  = trim($row[10]) ?? null;
            $issuedBy         = trim($row[11]) ?? null;
            $remarks          = trim($row[12]) ?? null;
            $status           = trim($row[13]) ?? null;
            $photoPath        = trim($row[14]) ?? null;
            $uniformColor     = trim($row[15]) ?? null;
            $shirtColor       = trim($row[16]) ?? null;
            $pantColor        = trim($row[17]) ?? null;
            $shoeColor        = trim($row[18]) ?? null;
            $headgearColor    = trim($row[19]) ?? null;

            $rowErrors = [];

            $requiredFields = [
                'Employee Name'   => $employeeName,
                'Employee Code'   => $employeeCode,
                'Uniform Type'    => $uniformType,
                'Shirt Size'      => $shirtSize,
                'Pant Size'       => $pantSize,
                'Shoe Size'       => $shoeSize,
                'Headgear Type'   => $headgearType,
                'Headgear Size'   => $headgearSize,
                'Issue Date'      => $issueDate,
                'Issued By'       => $issuedBy,
                'Status'          => $status,
            ];

            foreach ($requiredFields as $field => $value) {
                if (empty($value)) {
                    $rowErrors[] = "$field is required.";
                }
            }


            if (!in_array(strtolower($status), ['New', 'Replace'])) {
                $rowErrors[] = "Status must be 'New' or 'Replace'.";
            }

            if ($issuedBy) {
                $issuedByUser = \App\Models\Employee::where('emp_full_name', $issuedBy)->first();

                if (!$issuedByUser) {
                    $rowErrors[] = "Issued By name '{$issuedBy}' not found.";
                } else {
                    $issuedById = $issuedByUser->emp_id;
                }
            } else {
                $rowErrors[] = "Issued By is required.";
            }

            if ($employeeCode) {
                $employee = \App\Models\Employee::where('emp_code', $employeeCode)->first();

                if (!$employee) {
                    $rowErrors[] = "Employee Code '$employeeCode' not found.";
                } else {
                    $employeeCodeByID = $employee->emp_id;
                }
            } else {
                $rowErrors[] = "Employee is required.";
            }


            // Skip processing if there are row errors
            if (!empty($rowErrors)) {
                $unsuccessfulCount++;
                $this->errorMessages[] = "Row " . ($key + 1) . ": " . implode(' ', $rowErrors);
                continue;
            }



            try {
                $data = [
                    'ud_b_id'                 => $this->user->emp_b_id,
                    'ud_uniform_type'         => $uniformType,
                    'ud_shirt_size'           => $shirtSize,
                    'ud_pant_size'            => $pantSize,
                    'ud_shoe_size'            => $shoeSize,
                    'ud_headgear_type'        => $headgearType,
                    'ud_headgear_size'        => $headgearSize,
                    'ud_issue_date'           => $issueDate,
                    'ud_replacement_due_date' => $replacementDate,
                    'ud_issued_by'            => $issuedById ?? 'System',
                    'ud_status'               => strtolower($status),
                    'ud_remarks'              => $remarks,
                    'ud_photo_path'           => $photoPath,
                    'ud_uniform_color'        => $uniformColor,
                    'ud_shirt_color'          => $shirtColor,
                    'ud_pant_color'           => $pantColor,
                    'ud_shoe_color'           => $shoeColor,
                    'ud_headgear_color'       => $headgearColor,
                ];

                // Insert or Update
                \App\Models\UniformDetail::updateOrCreate(
                    ['ud_emp_id' => $employeeCodeByID],
                    $data
                );

                $successfulCount++;
                $this->successRows[] = $row->toArray();
            } catch (\Exception $e) {
                $unsuccessfulCount++;
                $this->errorMessages[] = "Row " . ($key + 1) . ": Exception - " . $e->getMessage();
            }
        }

        if (!empty($this->errorMessages)) {
            $this->errorMessages[] = "Successfully imported: {$successfulCount} | Failed: {$unsuccessfulCount}";
            return Excel::download(new ErrorExport($this->errorMessages), 'uniform_import_errors.xlsx');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Uniform import completed successfully!',
            'successful_count' => $successfulCount,
            'unsuccessful_count' => $unsuccessfulCount,
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
