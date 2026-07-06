<?php

namespace App\Observers;

use App\Models\Employee;
use Illuminate\Support\Facades\Mail;

class EmployeeObserver
{

    public function updated(Employee $employee)
    {

        $employeeData = Employee::where('emp_id', $employee->emp_id)->select('emp_email','emp_full_name')->first();
        $supervisor = Employee::where('emp_id', $employee->emp_supervisor_id)
        ->orWhere('emp_role_id', 1)
        ->select('emp_email', 'emp_full_name')
        ->first();


        if (!$employeeData || !$supervisor) {
            return;
        }

        $employeeEmail = $employeeData->emp_email;
        $managerEmail = $supervisor->emp_email;
        $employeeFullName = $employeeData->emp_full_name;

        $message = "Employee {$employeeFullName} ({$employee->emp_code}) details have been updated.";

        //this is temporary because mail is not working currently remove this line when resolved to execute the below code
        // Mail::raw($message, function ($mail) use ($employeeFullName, $employeeEmail) {
        //     $mail->to($employeeEmail)
        //         ->subject("Your Details Have Been Updated: {$employeeFullName}");
        // });
        // Mail::raw($message, function ($mail) use ($employeeFullName, $managerEmail) {
        //     $mail->to($managerEmail)
        //         ->subject("Employee Details Updated: {$employeeFullName}");
        // });
    }
}
