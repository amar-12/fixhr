<?php

namespace App\Livewire\Components;

use App\Models\Business;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class EmployeeECRFileGenerator extends Component
{
    use WithFileUploads;

    /**
     * ECR Format Configuration - 30 Fields
     */
    const ECR_FORMAT = [
        'emp_code' => ['position' => 1, 'field' => 'Employee Code', 'required' => true, 'max_length' => 50],
        'emp_name' => ['position' => 2, 'field' => 'Employee Name', 'required' => true, 'max_length' => 100],
        'dob' => ['position' => 3, 'field' => 'Date of Birth', 'required' => true, 'format' => 'd/m/Y'],
        'doj' => ['position' => 4, 'field' => 'Date of Joining', 'required' => true, 'format' => 'd/m/Y'],
        'gender' => ['position' => 5, 'field' => 'Gender', 'required' => true, 'options' => ['M', 'F', 'O']],
        'father_name' => ['position' => 6, 'field' => 'Father\'s Name', 'required' => true, 'max_length' => 100],
        'marital_status' => ['position' => 7, 'field' => 'Marital Status', 'required' => true, 'options' => ['M', 'U', 'S']],
        'phone' => ['position' => 8, 'field' => 'Phone Number', 'required' => false, 'max_length' => 15],
        'alt_phone' => ['position' => 9, 'field' => 'Alternate Phone', 'required' => false, 'max_length' => 15],
        'nationality' => ['position' => 10, 'field' => 'Nationality', 'required' => false, 'max_length' => 50, 'default' => 'Indian'],
        'field11' => ['position' => 11, 'field' => 'Field 11', 'required' => false],
        'field12' => ['position' => 12, 'field' => 'Field 12', 'required' => false],
        'emp_type' => ['position' => 13, 'field' => 'Employee Type', 'required' => false, 'max_length' => 1, 'options' => ['U', 'P', 'T']],
        'status' => ['position' => 14, 'field' => 'Status', 'required' => false, 'max_length' => 1, 'options' => ['A', 'I', 'N']],
        'field15' => ['position' => 15, 'field' => 'Field 15', 'required' => false],
        'field16' => ['position' => 16, 'field' => 'Field 16', 'required' => false],
        'field17' => ['position' => 17, 'field' => 'Field 17', 'required' => false],
        'field18' => ['position' => 18, 'field' => 'Field 18', 'required' => false],
        'field19' => ['position' => 19, 'field' => 'Field 19', 'required' => false],
        'pf_nominee' => ['position' => 20, 'field' => 'PF Nominee', 'required' => false, 'max_length' => 1, 'default' => 'N'],
        'esi_nominee' => ['position' => 21, 'field' => 'ESI Nominee', 'required' => false, 'max_length' => 1, 'default' => 'N'],
        'gratuity_nominee' => ['position' => 22, 'field' => 'Gratuity Nominee', 'required' => false, 'max_length' => 1, 'default' => 'N'],
        'field23' => ['position' => 23, 'field' => 'Field 23', 'required' => false],
        'bank_account' => ['position' => 24, 'field' => 'Bank Account', 'required' => false, 'max_length' => 30],
        'ifsc' => ['position' => 25, 'field' => 'IFSC Code', 'required' => false, 'max_length' => 20],
        'account_holder' => ['position' => 26, 'field' => 'Account Holder', 'required' => false, 'max_length' => 100],
        'pan' => ['position' => 27, 'field' => 'PAN Number', 'required' => false, 'max_length' => 15],
        'field28' => ['position' => 28, 'field' => 'Field 28', 'required' => false],
        'field29' => ['position' => 29, 'field' => 'Field 29', 'required' => false],
        'field30' => ['position' => 30, 'field' => 'Field 30', 'required' => false],
    ];

    const SEPARATOR = '#~#';
    const FILE_EXTENSION = '.txt';

    // Component properties
    public $businessId;
    public $business;
    public $isGenerating = false;
    public $ecrContent = '';
    public $generatedFileName = '';
    public $validationErrors = [];
    public $stats = [
        'total' => 0,
        'success' => 0,
        'failed' => 0
    ];

    protected $listeners = [
        'generateECR' => 'generateECR',
        'downloadFile' => 'downloadECR'
    ];

    /**
     * Mount the component
     */
    public function mount()
    {
        $this->businessId = auth()->user()->emp_b_id;
        $this->business = Business::find($this->businessId);
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.components.employee-e-c-r-file-generator');
    }

    /**
     * Generate ECR file for all active employees (status 71)
     */
    public function generateECR()
    {
        $this->isGenerating = true;
        $this->validationErrors = [];
        $this->ecrContent = '';

        $this->stats = [
            'total' => 0,
            'success' => 0,
            'failed' => 0
        ];

        try {
            // Get all active employees (status 71) for this business
            $employees = Employee::with([
                'fh_department',
                'fh_designation',
                'fh_employee_status',
                'fh_gender',
                'fh_marital_status',
                'fh_employee_type'
            ])
            ->where('emp_b_id', $this->businessId)
            ->where('emp_status', 71) // Active employees
            ->orderBy('emp_code')
            ->get();

            $this->stats['total'] = $employees->count();

            if ($employees->isEmpty()) {
                $this->dispatch('show-error', message: 'No active employees found for this business.');
                $this->isGenerating = false;
                return;
            }

            $lines = [];

            // Process each employee
            foreach ($employees as $index => $employee) {
                try {
                    $line = $this->generateEmployeeLine($employee);
                    $lines[] = $line;
                    $this->stats['success']++;
                } catch (\Exception $e) {
                    $this->stats['failed']++;
                    $this->validationErrors[] = "Employee {$employee->emp_code}: " . $e->getMessage();
                    Log::error("Error processing employee {$employee->emp_id}: " . $e->getMessage());
                }
            }

            // Combine all lines
            $this->ecrContent = implode("\n", $lines);

            // Generate file name
            $businessCode = $this->business->business_code ?? 'BUS';
            $dateTime = date('Ymd_His');
            $this->generatedFileName = "{$businessCode}_ACTIVE_EMPLOYEES_{$dateTime}" . self::FILE_EXTENSION;

            // Download the file
            $this->downloadECR();

            // Show success message
            $this->dispatch('show-success', message: "ECR file generated successfully with {$this->stats['success']} active employees.");

        } catch (\Exception $e) {
            Log::error('Error generating ECR: ' . $e->getMessage());
            $this->validationErrors[] = 'Error generating ECR: ' . $e->getMessage();
            $this->dispatch('show-error', message: 'Error generating ECR file.');
        }

        $this->isGenerating = false;
    }

    /**
     * Generate single employee line in ECR format
     */
    private function generateEmployeeLine($employee): string
    {
        $fields = [];
        $employeeData = $this->prepareEmployeeData($employee);

        foreach (self::ECR_FORMAT as $key => $config) {
            $value = $this->getFieldValue($employeeData, $key, $config);
            $fields[] = $value ?? '';
        }

        return implode(self::SEPARATOR, $fields);
    }

    /**
     * Prepare employee data from model
     */
    private function prepareEmployeeData($employee): array
    {
        // Format dates as DD/MM/YYYY
        $dob = $employee->emp_dob ?? null;
        $doj = $employee->emp_date_of_joining ?? null;

        $formattedDob = $dob ? Carbon::parse($dob)->format('d/m/Y') : '';
        $formattedDoj = $doj ? Carbon::parse($doj)->format('d/m/Y') : '';

        // Build full name
        $fullName = '';
        if (!empty($employee->emp_full_name)) {
            $fullName = $employee->emp_full_name;
        } else {
            $fullName = trim(($employee->emp_fname ?? '') . ' ' . ($employee->emp_mname ?? '') . ' ' . ($employee->emp_lname ?? ''));
        }

        // Get father's name
        $fatherName = $employee->emp_relative_name ?? '';

        return [
            'emp_code' => $employee->emp_code ?? '',
            'emp_name' => strtoupper($fullName),
            'dob' => $formattedDob,
            'doj' => $formattedDoj,
            'gender' => $this->mapGender($employee->fh_gender->m_code ?? $employee->emp_gender_id),
            'father_name' => strtoupper($fatherName),
            'marital_status' => $this->mapMaritalStatus($employee->fh_marital_status->m_code ?? $employee->emp_marital_status_id),
            'phone' => $employee->emp_phone ?? '',
            'alt_phone' => $employee->emp_emergency_contact ?? $employee->emp_relative_phone_no ?? '',
            'nationality' => $employee->emp_nationality ?? 'Indian',
            'field11' => '',
            'field12' => '',
            'emp_type' => $this->mapEmployeeType($employee->fh_employee_type->m_code ?? $employee->emp_type_id),
            'status' => 'A', // Active status for ECR
            'field15' => '',
            'field16' => '',
            'field17' => '',
            'field18' => '',
            'field19' => '',
            'pf_nominee' => 'N',
            'esi_nominee' => 'N',
            'gratuity_nominee' => 'N',
            'field23' => '',
            'bank_account' => $employee->emp_bank_account_no ?? $employee->emp_salary_bank_account_no ?? '',
            'ifsc' => $employee->emp_bank_ifsc_code ?? $employee->emp_salary_bank_ifsc_code ?? '',
            'account_holder' => $fullName,
            'pan' => strtoupper($employee->emp_pan_number ?? ''),
            'field28' => '',
            'field29' => '',
            'field30' => '',
        ];
    }

    /**
     * Get field value with fallbacks
     */
    private function getFieldValue(array $data, string $field, array $config): string
    {
        if (isset($data[$field]) && $data[$field] !== '') {
            return (string) $data[$field];
        }

        if (isset($config['default'])) {
            return (string) $config['default'];
        }

        return '';
    }

    /**
     * Map gender from master table values
     */
    private function mapGender($gender): string
    {
        if (is_numeric($gender)) {
            return match((int)$gender) {
                1 => 'M',
                2 => 'F',
                3 => 'O',
                default => 'M'
            };
        }

        $gender = strtoupper(substr($gender ?? '', 0, 1));
        return match($gender) {
            'M', '1' => 'M',
            'F', '2' => 'F',
            'O', '3' => 'O',
            default => 'M'
        };
    }

    /**
     * Map marital status from master table values
     */
    private function mapMaritalStatus($status): string
    {
        if (is_numeric($status)) {
            return match((int)$status) {
                1 => 'M',
                2 => 'U',
                3 => 'S',
                default => 'U'
            };
        }

        $status = strtoupper(substr($status ?? '', 0, 1));
        return match($status) {
            'M', '1' => 'M',
            'U', 'S', '2' => 'U',
            default => 'U'
        };
    }

    /**
     * Map employee type from master table values
     */
    private function mapEmployeeType($type): string
    {
        if (is_numeric($type)) {
            return match((int)$type) {
                1 => 'P',
                2 => 'T',
                3 => 'U',
                default => 'U'
            };
        }

        $type = strtoupper(substr($type ?? '', 0, 1));
        return match($type) {
            'P', '1' => 'P',
            'T', '2' => 'T',
            default => 'U'
        };
    }

    /**
     * Download ECR file
     */
    public function downloadECR()
    {
        if (empty($this->ecrContent)) {
            $this->dispatch('show-error', message: 'No content to download.');
            return;
        }

        $this->dispatch('download-file',
            content: $this->ecrContent,
            filename: $this->generatedFileName
        );
    }

    /**
     * Get count of active employees (status 71)
     */
    public function getActiveEmployeeCount()
    {
        return Employee::where('emp_b_id', $this->businessId)
                       ->where('emp_status', 71)
                       ->count();
    }
}
