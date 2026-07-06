<?php

namespace App\Http\Controllers\Api\Exit;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\EmployeeExitRequest;
use App\Models\MailTemplate;
use App\Models\MasterTable;
use App\Models\RuleCriterion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmployeeExitController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $master = MasterTable::where('m_group', 'Leaving')->select('m_id', 'm_name')->get();
        return response()->json([
            'result' => $master,
            'message' => 'Employee data fetched successfully',
            'status' => true,
        ]);
    }

    public function store(Request $request)
    {

        $user = Auth::user();
        $emp_b_id = $user->emp_b_id;
        $employeeId = $user->emp_id;

        $employee = Employee::where('emp_b_id', $emp_b_id)
            ->where('emp_id', $user->emp_id)
            ->where('emp_status', 71)
            ->first();


        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found'
            ]);
        }


        $separationDate = Carbon::now();
        $lastWorkingDate = Carbon::parse($request->emp_last_working_date);
        $servedDays = $separationDate->diffInDays($lastWorkingDate) + 1;

        // Shortfall calculation
        $requiredDays = (int) $request->emp_notice_period_req_days;
        $shortfallDays = $requiredDays - $servedDays;
        $shortfallDays = $shortfallDays > 0 ? $shortfallDays : 0;

        $employee->update([
            'emp_separation_submit_date' => $separationDate,
            'emp_last_working_date'      => $request->emp_last_working_date,
            'emp_notice_period_req_days' => $requiredDays,
            'emp_remark'                 => $request->emp_remark,

            'emp_notice_period_serve_days'      => $servedDays,
            'emp_notice_period_shortfall_days'  => $shortfallDays,
        ]);


        if ($request->filled('emp_last_working_date') || $request->filled('emp_separation_submit_date')) {
            $business = Business::where('b_id', $emp_b_id)->first();
            $business_name = $business->b_name ?? '';
            $words = explode(' ', $business_name);
            $initials = '';
            foreach ($words as $word) {
                $initials .= strtoupper(substr($word, 0, 1));
            }

            $month = Carbon::now()->format('m');
            $year = Carbon::now()->format('Y');

            $count = EmployeeExitRequest::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count() + 1;

            $serial = str_pad($count, 4, '0', STR_PAD_LEFT);
            $fnfReference = "{$initials}/{$month}/{$year}/{$serial}";

            $businessId = $user->emp_b_id;

            $names = [
                'Admin Review',
                'Finance Review',
                'HR Review',
                'Manager Review'
            ];

            $masterData = MasterTable::where('m_group', 'MODULE')
                ->where('m_type', 'FNF')
                ->whereIn('m_name', $names)
                ->pluck('m_id', 'm_name')
                ->toArray();

            $managerReviewId = $masterData['Manager Review'] ?? null;

            $approvalMapping = \App\Helpers\ApprovalHelper::getApprovalMapping(
                $employee->emp_b_id,
                $employee->emp_id,
                $managerReviewId
            );

            $approvalEmpIds = [];
            $amId = null;

            if ($approvalMapping) {
                $approvalEmpIds = \App\Helpers\ApprovalHelper::getApprovalArray($approvalMapping);
            } else {
                $ruleCriteria = RuleCriterion::with('fh_approval_module')
                    ->where('rc_b_id', $user->emp_b_id)
                    ->where('rc_condition_option_id', 140)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 5888)
                            ->where('am_status', 1);
                    })->first();

                $processApprovers = [];
                $emp_d_id = $user->emp_d_id;

                if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                    $processApprovers = $ruleCriteria->fh_approval_module
                        ->filteredProcessApprovers($emp_d_id)
                        ->get();
                }

                if (!empty($processApprovers)) {
                    $amId = $ruleCriteria->rc_am_id;

                    foreach ($processApprovers as $pa) {
                        if ($pa->pa_emp_id) {
                            $approvalEmpIds[] = $pa->pa_emp_id;
                        }
                    }
                } else {
                    return response()->json([
                        'result' => [],
                        'status' => false,
                        'message' => 'Sorry! not found any approval settings for FNF module, contact administration.'
                    ]);
                }
            }

            $data = [
                'er_exit_type_id'       => $request->emp_leaving_reason,
                'er_reason'             => $request->emp_remark,
                'er_resignation_date'   => now(),
                'er_notice_period_days' => $request->emp_notice_period_req_days,
                'er_last_working_day'   => $request->emp_last_working_date,
                'er_manager_status'     => 'PENDING',
                'er_hr_status'          => 'PENDING',
                'er_overall_status'     => 'RESIGNATION_SUBMITTED',
                'er_remark'             => $request->emp_remark,
                'er_updated_by'         => $user->emp_id,
                'er_ref_no'             => $fnfReference,
                'er_am_id'              => $amId,
                'er_module_id'          => $managerReviewId,
                'er_status'             => 140,
                'er_stage_completed'    => 0,
                'er_module_stage'       => 1,
                'er_next_approver'      => 1,
            ];

            try {
                $existingExit = EmployeeExitRequest::where('er_emp_id', $employeeId)
                    ->where('er_b_id', $businessId)
                    ->whereNotIn('er_overall_status', ['RELIEVED', 'REJECTED'])
                    ->first();

                if ($existingExit) {
                    $existingExit->update($data);

                    Log::info('Employee exit request updated', [
                        'employee_id' =>  $user->emp_id,
                        'business_id' => $businessId,
                        'updated_by'  => $user->emp_id,
                        'timestamp'   => now()
                    ]);


                    return response()->json([
                        'message' => 'Employee data updated successfully',
                        'status' => true,
                        'result' => true,

                    ]);
                } else {
                    EmployeeExitRequest::create(array_merge($data, [
                        'er_emp_id'     =>  $user->emp_id,
                        'er_b_id'       => $businessId,
                        'er_created_by' => $user->emp_id
                    ]));

                    Log::info('Employee exit request created', [
                        'employee_id' =>  $user->emp_id,
                        'business_id' => $businessId,
                        'created_by'  => $user->emp_id,
                        'timestamp'   => now()
                    ]);


                    // Get Employee Details
                    $employee = Employee::with('fh_department', 'fh_designation')
                        ->where('emp_b_id', $emp_b_id)
                        ->where('emp_id', $employee->emp_id)
                        ->firstOrFail();

                    // Get Manager Details
                    $manager = Employee::with('fh_department', 'fh_designation')
                        ->where('emp_b_id', $emp_b_id)
                        ->where('emp_id', $employee->emp_supervisor_id)
                        ->first();


                    // dd($employee, $manager);

                    if (!$manager) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Manager not found'
                        ]);
                    }

                    // Get Employee Details
                    $employee = Employee::with('fh_department', 'fh_designation')
                        ->where('emp_b_id', $emp_b_id)
                        ->where('emp_id', $employee->emp_id)
                        ->firstOrFail();

                    // Get Manager Details
                    $manager = Employee::with('fh_department', 'fh_designation')
                        ->where('emp_b_id', $emp_b_id)
                        ->where('emp_id', $employee->emp_supervisor_id)
                        ->first();

                    if (!$manager) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Manager not found'
                        ]);
                    }

                    // Get all required mail templates at once
                    $templates = MailTemplate::where('mt_b_id', $emp_b_id)
                        ->where('mt_is_enabled', 1)
                        ->whereIn('mt_id', [32, 33, 39])
                        ->get()
                        ->keyBy('mt_id');

                    $sendEmail = function ($email, $subject, $body) {
                        try {
                            Mail::html($body, function ($message) use ($email, $subject) {
                                $message->to($email)->subject($subject);
                            });
                        } catch (\Exception $e) {
                            Log::error("Email failed for {$email}: " . $e->getMessage());
                        }
                    };

                    $replace = function ($body, $placeholders) {
                        return str_replace(
                            array_keys($placeholders),
                            array_values($placeholders),
                            $body
                        );
                    };

                    $formatDate = function ($date) {
                        return !empty($date)
                            ? \Carbon\Carbon::parse($date)->format('d-M-Y')
                            : '';
                    };

                    if ($templates->has(32) && !empty($employee->emp_email)) {

                        $placeholders = [
                            '[Employee Name]'   => $employee->emp_full_name ?? '',
                            '[Emp Code]'        => $employee->emp_code ?? '',
                            '[Submission Date]' => $formatDate($employee->emp_separation_submit_date),
                            '[Designation]'     => $employee->fh_designation->dg_name ?? '',
                            '[Department]'      => $employee->fh_department->d_name ?? '',
                            '[Effective LWD]'   => !empty($employee->emp_last_working_date)
                                ? $formatDate($employee->emp_last_working_date)
                                : 'Pending Approval',
                        ];

                        // Replace placeholders in email body
                        $body = $replace($templates[32]->mt_body, $placeholders);

                        // Replace placeholders in email subject/title
                        $subject = $replace(
                            $templates[32]->mt_title ?? 'Resignation Submitted',
                            $placeholders
                        );

                        $sendEmail(
                            $employee->emp_email,
                            $subject,
                            $body
                        );
                    }

                    if ($templates->has(33) && !empty($manager->emp_email)) {

                        $placeholders = [
                            '[Manager Name]'    => $manager->emp_full_name ?? '',
                            '[Employee Name]'   => $employee->emp_full_name ?? '',
                            '[Emp Code]'        => $employee->emp_code ?? '',
                            '[Submission Date]' => $formatDate($employee->emp_separation_submit_date),
                            '[Designation]'     => $employee->fh_designation->dg_name ?? '',
                            '[Department]'      => $employee->fh_department->d_name ?? '',
                            '[Effective LWD]'   => !empty($employee->emp_last_working_date)
                                ? $formatDate($employee->emp_last_working_date)
                                : 'Not Specified',
                        ];

                        // Replace placeholders in email body
                        $body = $replace($templates[33]->mt_body, $placeholders);

                        // Replace placeholders in email subject/title
                        $subject = $replace(
                            $templates[33]->mt_title ?? 'Approval Request',
                            $placeholders
                        );

                        $sendEmail(
                            $manager->emp_email,
                            $subject,
                            $body
                        );
                    }


                    return response()->json([
                        'message' => 'Employee exit request created Successfully',
                        'status' => true,
                        'result' => true,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error processing employee exit request', [
                    'employee_id' => $employeeId,
                    'business_id' => $businessId,
                    'error'       => $e->getMessage(),
                    'timestamp'   => now()
                ]);
            }
        }
    }


    public function show()
    {
        $user = Auth::user();

        // Fetch exits with relationships, selecting only needed columns
        $exits = EmployeeExitRequest::with([
            'employee:emp_id,emp_code,emp_full_name,emp_d_id,emp_dg_id,emp_notice_period_req_days,emp_separation_submit_date,emp_last_working_date',
            'employee.fh_designation:dg_id,dg_name',
            'employee.fh_department:d_id,d_name',
            'exitType:m_id,m_name',
        ])
            ->where('er_b_id', $user->emp_b_id)
            ->where('er_emp_id', $user->emp_id)
            ->get();

        // Map data or return empty array if no records
        $data = $exits->map(function ($exit) {
            return [
                'er_id'                 => $exit->er_id,
                'emp_full_name'         => $exit->employee->emp_full_name ?? null,
                'emp_code'              => $exit->employee->emp_code ?? null,
                'submitted_date'        => $exit?->er_resignation_date ? date('d-m-Y', strtotime($exit->er_resignation_date)) : null,
                'department'            => $exit->employee->fh_department->d_name ?? null,
                'designation'           => $exit->employee->fh_designation->dg_name ?? null,
                'last_working_day'      => $exit?->er_last_working_day ? date('d-m-Y', strtotime($exit->er_last_working_day)) : null,
                'notice_period'         => $exit->employee->emp_notice_period_req_days ?? null,
                'reason_for_leaving'    => $exit->exitType->m_name ?? null,
                'additional_reason'     => $exit->er_reason ?? null,
                'status'                => $exit->er_overall_status ?? null,
            ];
        })->toArray(); // convert collection to array immediately

        return response()->json([
            'result'  => $data ?: [],  // empty array if no data
            'message' => 'Employee exit data fetched successfully',
            'status'  => !empty($data), // false if data is empty
        ]);
    }

    public function destroy($er_id)
    {
        // dd($er_id);
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        // ✅ Filter by business, employee, AND exit request ID
        $exit = EmployeeExitRequest::where('er_b_id', $businessId)
            ->where('er_id', $er_id)
            ->first();

        // dd($exit);

        if (!$exit) {
            return response()->json([
                'message' => 'Record not found',
                'status' => false,
            ], 404);
        }

        $exit->delete();

        return response()->json([
            'message' => 'Employee exit data deleted successfully',
            'status' => true,
        ]);
    }
}
