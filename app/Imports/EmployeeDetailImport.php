<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\EmployeeIdentityDetail;
use App\Models\MasterTable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Auth;
use App\Exports\ErrorExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;

class EmployeeDetailImport implements ToCollection
{
    protected $type;
    public $errorMessages = [];
    public $successRows = [];
    protected $user;

    public function __construct($type, $user)
    {
        $this->type = $type;
        $this->user = $user;
    }

    public function collection(Collection $rows)
    {
        $headers = $rows->first()->toArray();
        $rows->shift();

        $expectedHeaders = $this->getExpectedHeaders();

        // Validate headers (case-insensitive, whitespace-normalized, allow extra trailing columns)
        $normalizeHeader = function ($h) {
            if ($h === null) return '';
            // Replace non-breaking spaces and collapse internal whitespace
            $h = str_replace("\xC2\xA0", ' ', (string) $h);
            $h = preg_replace('/\s+/u', ' ', $h);
            return strtolower(trim($h));
        };

        $normalizedUploadedHeaders = array_map($normalizeHeader, $headers);
        $normalizedExpectedHeaders = array_map($normalizeHeader, $expectedHeaders);

        // Compare only the first N headers (ignore extra columns in the sheet)
        $normalizedUploadedPrefix = array_slice($normalizedUploadedHeaders, 0, count($normalizedExpectedHeaders));
        if ($normalizedUploadedPrefix !== $normalizedExpectedHeaders) {
            throw new \Exception("Invalid column headers. Expected: " . implode(', ', $expectedHeaders));
        }

        $success = 0;
        $failure = 0;

        foreach ($rows as $key => $row) {
            $data = array_map('trim', $row->toArray());
            $empCode = $data[1] ?? null;

            if (!$empCode) {
                $this->errorMessages[] = "Row " . ($key + 2) . ": Employee Code is missing.";
                $failure++;
                continue;
            }

            $employee = Employee::where('emp_code', $empCode)->where('emp_b_id', $this->user->emp_b_id)->first();
            if (!$employee) {
                $this->errorMessages[] = "Row " . ($key + 2) . ": Employee with code '{$empCode}' not found.";
                $failure++;
                continue;
            }

            $validationRules = $this->getValidationRules();
            // Ensure data count matches expected headers for validation
            $dataForValidation = array_slice($data, 0, count($expectedHeaders));
            if (count($dataForValidation) < count($expectedHeaders)) {
                $dataForValidation = array_pad($dataForValidation, count($expectedHeaders), null);
                
            }
            $validator = Validator::make(array_combine($expectedHeaders, $dataForValidation), $validationRules);

            if ($validator->fails()) {
                $this->errorMessages[] = "Row " . ($key + 2) . ": " . implode(', ', $validator->errors()->all());
                $failure++;
                continue;
            }

            try {
                $this->processRow($employee, $data);
                $success++;
                $this->successRows[] = $data;
            } catch (\Exception $e) {
                $this->errorMessages[] = "Row " . ($key + 2) . ": Exception - " . $e->getMessage();
                $failure++;
            }
        }

        if ($failure > 0) {
            // $this->errorMessages[] = "Total Success: {$success}, Failed: {$failure}";
            $this->errorMessages[] = "Successfully records imported count: {$success} & Unsuccessfully records imported count: {$failure} ";
            return Excel::download(new ErrorExport($this->errorMessages), 'import_errors.xlsx');
        }
    }

    protected function getExpectedHeaders()
    {
        return match ($this->type) {
            'epf_esic' => [
                'Emp Code',
                'PF Enable',
                'PF Trust Code',
                'Pension Fund Member',
                'PF Number',
                'UAN',
                'VPF(%)',
                'PF DOJ(DD/MM/YYYY)',
                'PF DOL(DD/MM/YYYY)',
                'Reason of Leaving PF',
                'ESIC Enable',
                'ESIC Number',
                'ESIC Dispensary',
                'ESIC DOJ(DD/MM/YYYY)',
                'ESIC DOL(DD/MM/YYYY)',
                'Reason of Leaving ESIC',
                'Insured By',
                'Insurance Number',
                'Valid From',
                'Valid Thru'
            ],
            'identity' => [
                'Emp Code',
                'Aadhar Number',
                'Driving License Number',
                'Election Card Number',
                'Passport Number',
                'PAN Number',
                'Bank AC Number'
            ],
            'bank_details' => [
                'Emp Name',
                'Emp Code',
                'Payment Method',
                'Account Code',
                'IFSC Code',
                'Bank Name',
                'Branch Name',
                'MICR',
                'Branch Code',
                'AC Number',
                'Account Type',
            ],
        };
    }

    protected function getValidationRules()
    {
        return match ($this->type) {
            'epf_esic' => [
                'Emp Code' => 'required|string',
                'PF Number' => 'nullable|string',
                'UAN' => 'nullable|string',
                'VPF(%)' => 'nullable|numeric',
                'PF DOJ' => 'nullable|date',
                'ESIC Number' => 'nullable|string',
                'ESIC DOJ' => 'nullable|date',
                'Insurance Number' => 'nullable|string'
            ],
            'identity' => [
                'Emp Code' => 'required|string',
                'Aadhar Number' => 'nullable|string',
                'Driving License Number' => 'nullable|string',
                'Election Card Number' => 'nullable|string',
                'Passport Number' => 'nullable|string',
                'PAN Number' => 'nullable|string',
                'Bank AC Number' => 'nullable|string',
            ],
            'bank_details' => [
                'Emp Code' => 'required|string',
                'IFSC Code' => 'nullable|string',
            ]
        };
    }

    protected function processRow(Employee $employee, array $data)
    {
        if ($this->type === 'epf_esic') {
            $pfjd = $data[7] ? $this->formatExcelDate($data[7]) : null;
            $pfld = $data[8] ? $this->formatExcelDate($data[8]) : null;
            $esicjd = $data[13] ? $this->formatExcelDate($data[13]) : null;
            $esicld = $data[14] ? $this->formatExcelDate($data[14]) : null;
            $gisd = $data[18] ? $this->formatExcelDate($data[18]) : null;
            $gitd = $data[19] ? $this->formatExcelDate($data[19]) : null;
            $emp_esic_reason = MasterTable::where('m_name', $data[15])->value('m_id');
            $employee->update([
                'emp_is_pf_enabled' => $data[1] == 'Yes' ? 120 : 121,
                'emp_pf_trust_code' => $data[2] ?? null,
                'emp_pf_found_member' => $data[3] ?? null,
                'emp_pf_no' => $data[4] ?? null,
                'emp_pf_universal_ac_no' => $data[5] ?? null,
                'emp_vpf_percentage' => is_numeric($data[6]) ? round((float) $data[6], 2) : null,
                'emp_pf_joining_date' => $pfjd,
                'emp_pf_leaving_date' => $pfld,
                'emp_pr_leaving_reason' => $data[9] ?? null,
                'emp_esic_limit' => $data[10] == 'Yes' ? 120 : 121,
                'emp_esic_no' => $data[11] ?? null,
                'emp_esic_dispensary' => $data[12] ?? null,
                'emp_esic_joining_date' => $esicjd,
                'emp_esic_leaving_date' => $esicld,
                'emp_esic_leaving_reason' => $emp_esic_reason ?? null,
                'emp_group_insured_by' => $data[16] ?? null,
                'emp_group_insurance_no' => $data[17] ?? null,
                'emp_group_insurance_start_date' => $gisd,
                'emp_group_insurance_till_date' => $gitd,
            ]);
        }

        if ($this->type === 'identity') {
            $docs['aadhar_number'] = $data[1] ?? null;
            $docs['driving_license_number'] = $data[2] ?? null;
            $docs['voter_id_number'] = $data[3] ?? null;
            $docs['passport_number'] = $data[4] ?? null;
            $docs['pan_number'] = $data[5] ?? null;
            $docs['account_number'] = $data[6] ?? null;
            $employee->update([
                'emp_documents_ref_file' => json_encode($docs)
            ]);

            $empDetails = EmployeeIdentityDetail::where('emp_id', $employee->emp_id)
                ->where('emp_b_id', $this->user->emp_b_id)
                ->first();

            $dataToSave = [
                'emp_id'                 => $employee->emp_id,
                'emp_b_id'               => $this->user->emp_b_id,
                'aadhar_number'          => $data[1] ?? null,
                'driving_license_number' => $data[2] ?? null,
                'election_card_number'   => $data[3] ?? null,
                'passport_number'        => $data[4] ?? null,
                'pan_number'             => $data[5] ?? null,
                'bank_ac_number'         => $data[6] ?? null,
            ];

            if ($empDetails) {
                $empDetails->update($dataToSave);
            } else {
                EmployeeIdentityDetail::create($dataToSave);
            }
        }

        if ($this->type === 'bank_details') {
            $employee->update([
                'emp_name' => $data[0] ?? null,
                'emp_code' => $data[1] ?? null,
                'payment_method' => $data[2] ?? null,
                'emp_account_code' => $data[3] ?? null,
                'emp_bank_ifsc_code' => $data[4] ?? null,
                'emp_bank_name' => $data[5] ?? null,
                'emp_bank_branch_name' => $data[6] ?? null,
                'emp_bank_micr_code' => $data[7] ?? null,
                'emp_bank_branch_code' => $data[8] ?? null,
                'emp_bank_account_no' => $data[9] ?? null,
                'account_type' => $data[10] ?? null,
            ]);
        }
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    public function getSuccessRows()
    {
        return $this->successRows;
    }

    private function formatExcelDate($value)
    {
        if (empty($value)) return null;

        // Remove single/double quotes and white spaces
        $value = trim($value, "'\" ");

        try {
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            \Log::warning('Date parse failed', ['value' => $value, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
