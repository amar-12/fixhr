<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\MasterTable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class EmployeeReportExport implements FromCollection, WithHeadings
{
    protected $data;
    protected $columns;

    public function __construct($filter_data, $selected_columns)
    {
        $this->data = $filter_data;
        $this->columns = $selected_columns;
    }

    public function collection()
    {


        return $this->data->map(function ($employee) {

            $employeeData = [];


            foreach ($this->columns as $column) {
                if ($column === 'emp_role_id') {
                    $employeeData['emp_role_id'] = $employee->fh_role ? $employee->fh_role->role_name : null;
                } elseif ($column === 'emp_marital_status_id') {
                    $employeeData['emp_marital_status_id'] = $employee->fh_marital_status ? $employee->fh_marital_status->m_name : null;
                } elseif ($column === 'emp_d_id') {
                    $employeeData['emp_d_id'] = $employee->fh_department ? $employee->fh_department->d_name : null;
                } elseif ($column === 'emp_grade_id') {
                    $employeeData['emp_grade_id'] = $employee->fh_grade ? $employee->fh_grade->g_name : null;
                } elseif ($column === 'emp_type_id') {
                    $employeeData['emp_type_id'] = $employee->fh_employee_type ? $employee->fh_employee_type->m_name : null;
                } elseif ($column === 'emp_br_id') {
                    $employeeData['emp_br_id'] = $employee->fh_branch ? $employee->fh_branch->br_name : null;
                } elseif ($column === 'emp_dg_id') {
                    $employeeData['emp_dg_id'] = $employee->fh_designation ? $employee->fh_designation->dg_name : null;
                } elseif ($column === 'emp_pt_id') {
                    $employeeData['emp_pt_id'] = $employee->policyTax ? $employee->policyTax->pt_name : null;
                } elseif ($column === 'emp_pwo_id') {
                    $employeeData['emp_pwo_id'] = $employee->fh_week_off_policy ? $employee->fh_week_off_policy->pwo_name : null;
                } elseif ($column === 'emp_work_mode_id') {
                    $employeeData['emp_work_mode_id'] = $employee->emp_work_mode_id === null ? 'Employee Inactive' : 'Office';
                } elseif ($column === 'emp_checkin_method_id') {
                    if (!empty($employee->emp_checkin_method_id)) {
                        $checkMethods = MasterTable::whereIn('m_id', $employee->emp_checkin_method_id)->pluck('m_name')->toArray();
                        $employeeData['emp_checkin_method_id'] = implode(', ', $checkMethods);
                    }
                } elseif ($column === 'emp_job_status') {
                    $employeeData['emp_job_status'] = $employee->fh_status ? $employee->fh_status->m_name : null;
                } elseif ($column === 'emp_status') {
                    $employeeData['emp_status'] = $employee->fh_employee_status ? $employee->fh_employee_status->m_name : null;

                } elseif ($column === 'emp_esic_limit') {
                    $employeeData['emp_esic_limit'] = $employee->fh_esic_limit ? $employee->fh_esic_limit->m_name : null;
                }
                 elseif ($column === 'emp_supervisor_id') {
                   $employeeData['emp_supervisor_id'] = $employee->fh_reporting_manager_id ? $employee->fh_reporting_manager_id->emp_full_name : null;

                }
                 else {
                    $employeeData[$column] = $employee->{$column};
                }



            }

            return $employeeData;
        });
    }




    public function headings(): array
    {
        $headings = $this->columns;

        foreach ($headings as $index => $column) {
            switch ($column) {
                case 'emp_role_id':
                    $headings[$index] = 'Role Name';
                    break;
                case 'emp_marital_status_id':
                    $headings[$index] = 'Marital Status';
                    break;
                case 'emp_d_id':
                    $headings[$index] = 'Department';
                    break;
                case 'emp_grade_id':
                    $headings[$index] = 'Grade';
                    break;
                case 'emp_type_id':
                    $headings[$index] = 'Employee Type';
                    break;
                case 'emp_br_id':
                    $headings[$index] = 'Branch';
                    break;
                case 'emp_dg_id':
                    $headings[$index] = 'Designation';
                    break;
                case 'emp_pt_id':
                    $headings[$index] = 'Policy Tax';
                    break;
                case 'emp_pwo_id':
                    $headings[$index] = "Policy Week Off";
                    break;
                case 'emp_work_mode_id':
                    $headings[$index] = "Checkin Type";
                    break;
                case 'emp_status':
                    $headings[$index] = "Emp Status";
                    break;
                case 'emp_job_status':
                    $headings[$index] = "Job Type";
                    break;
                case 'emp_supervisor_id':
                    $headings[$index] = "Reporting Manager";
                    break;
                case 'emp_checkin_method_id':
                    $headings[$index] = "Checkin Method";
                    break;
                case 'emp_esic_limit':
                    $headings[$index] = "ESIC";
                    break;
                case 'emp_email':
                    $headings[$index] = "Emp Email";
                    break;
                case 'emp_phone':
                    $headings[$index] = "Emp Phone";
                    break;
                case 'emp_dob':
                    $headings[$index] = "Emp DOB";
                    break;

                case 'emp_code':
                    $headings[$index] = "Emp Code";
                    break;

                case 'emp_full_name':
                    $headings[$index] = "Emp Name";
                    break;
                default:
                    break;
            }
        }

        return $headings;
    }
}
