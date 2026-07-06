<?php

namespace App\Http\Controllers\Api\Payroll;

// use PDF;
use NumberFormatter;
use App\Models\Business;
use App\Models\Employee;
use App\Models\AdvanceLog;
use App\Models\LoanRequest;
use Illuminate\Http\Request;
use App\Models\RuleCriterion;
use App\Helpers\CentralLogics;
use App\Helpers\PayrollLogics;
use App\Models\AutomationRule;
use App\Models\AdvanceLoanSetting;
use Illuminate\Support\Carbon;
use App\Helpers\ApprovalHelper;
use App\Models\ProcessApprover;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PayrollLoanInstallment;
use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Resources\Payroll\LoanResource;
use ChandraHemant\HtkcUtils\PaginatedResource;
use App\Http\Requests\Payroll\LoanDetailsRequest;
use ChandraHemant\HtkcUtils\FirebaseNotification;
use App\Http\Resources\Payroll\LoanLogApiResource;
use App\Http\Resources\Approval\Travel\AdvanceLogApiResource;
use App\Http\Resources\Approval\Travel\TravelPlanApiResource;
use App\Helpers\NotificationHelper;


class LoanLogApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;


        $loanRequestData = LoanRequest::where('lnr_emp_id', $user->emp_id)
        // ->whereMonth('created_at', $currentMonth)
        // ->whereYear('created_at', $currentYear)
        ->get();
        // dd($loanRequestData);



        $loanRequestCountDetail = [
            'loan_request_applied' => $loanRequestData->count(),
        ];


        if ($loanRequestData->isEmpty()) {
            return response()->json(['result' => [[
                'loan_request_list' => [],
                'loan_request_count_details' => $loanRequestCountDetail
            ]], 'message' => 'No loan request application found.', 'status' => false]);
        } else {
            return ReturnHelper::jsonApiReturn([[
                'loan_request_list' => LoanLogApiResource::collection($loanRequestData),
                'loan_request_count_details' => $loanRequestCountDetail
            ]]);

        }
    }



    // public function store(Request $request)
    // {

    //     $user = Auth::user();
    //     $existingRequest = LoanRequest::where('lnr_emp_id', $user->emp_id)
    //         ->where('lnr_b_id', $user->emp_b_id)
    //         ->first();

    //     if ($existingRequest) {
    //         return response()->json([
    //             'result' => [],
    //             'status' => false,
    //             'message' => 'A loan request already exists for this employee.'
    //         ]);
    //     }

    //     $ruleCriteria = RuleCriterion::with('fh_approval_module')
    //         ->where('rc_b_id', $user->emp_b_id)
    //         ->where('rc_condition_option_id', 140)
    //         ->whereHas('fh_approval_module', function ($query) {
    //             $query->where('am_module_id', 442)
    //                 ->where('am_status', 1);
    //         })->first();

    //     $year = date('Y');
    //     $month = date('m');

    //     // Count how many loan requests have been created in the current month for this branch
    //     $currentMonthCount = LoanRequest::where('lnr_b_id', $user->emp_b_id)
    //         ->whereYear('created_at', $year)
    //         ->whereMonth('created_at', $month)
    //         ->count();

    //     $sno = str_pad($currentMonthCount + 1, 3, '0', STR_PAD_LEFT); // e.g., 001, 002
    //     $unique_id = "LA-{$year}-{$month}-{$sno}";


    //     // $lnr_unique_id = '';
    //     // $lastUnique = LoanRequest::where('lnr_b_id', $user->emp_b_id)->get()->last();
    //     // if ($lastUnique) {
    //     //     $lnr_unique_id = $lastUnique->lnr_unique_id ?? '';
    //     // }

    //     // $unique_id = CentralLogics::alpha_numeric_generator(4, 'LA', '', $lnr_unique_id ?? '');


    //     $processApprovers = [];
    //     $emp_d_id = $user->emp_d_id;
    //     $amId = null;

    //     // Ensure $ruleCriteria exists before accessing the relationship
    //     if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
    //         // Fetch filtered process approvers using emp_d_id
    //         $processApprovers = $ruleCriteria->fh_approval_module
    //             ->filteredProcessApprovers($emp_d_id)
    //             ->get(); // Fetch the filtered data
    //     }

    //     if (empty($processApprovers)) {
    //         $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 442);
    //         if (!$approvalMapping) {
    //             return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for loan module, contact administration.']);
    //         }
    //     } else {
    //         $amId = $ruleCriteria->rc_am_id;
    //     }


    //     $data = new LoanRequest();
    //     $data->lnr_emp_id  = $user->emp_id;
    //     $data->lnr_b_id  = $user->emp_b_id;
    //     $data->lnr_advance_type = $request->input('lnr_advance_type');
    //     $data->lnr_request_subject = $request->input('request_subject');
    //     $data->lnr_requested_amount = $request->input('requested_amount');
    //     $data->lnr_description = $request->input('description');
    //     $data->lnr_installment_amount = $request->input('installment_amount');
    //     $data->lnr_installments = $request->input('installments');
    //     $data->lnr_start_date = date('Y-m-d', strtotime($request->input('loan_start_date')));
    //     $data->lnr_unique_id = $unique_id;
    //     $data->lnr_request_status = 140;
    //     $data->lnr_am_id = $amId;
    //     $data->lnr_stage_completed = 0;

    //     if ($data->save()) {
    //         $title = 'New Loan Request';
    //         $body = 'A new loan request has been submitted by ' . $user->emp_full_name;
    //         $additionalData = [
    //             'user_id' => $user->emp_id,
    //             'notification_type' => 'alert',
    //             'route' => '/LoanApprovalList',
    //         ];
    //         $serviceAccountPath = public_path('fixhr-app-firebase.json');
    //         foreach ($processApprovers as $pa) {
    //             $emp = Employee::find($pa->pa_emp_id);
    //             $approver = ApprovalHelper::getApprovalOrRejectionData($data->lnr_id, $data->lnr_request_status, $amId, $pa->pa_emp_id, 442);
    //             // if ($approver) {
    //             //     FirebaseNotification::sendPushNotification(
    //             //         $title,
    //             //         $body,
    //             //         $emp->emp_fcm_token,
    //             //         $serviceAccountPath,
    //             //         config('credentials')['FIREBASE_MESSAGING_CONFIG'],
    //             //         $additionalData
    //             //     );
    //             // }
    //         }

    //         return ReturnHelper::jsonApiReturn(LoanResource::collection([$data]));
    //     }
    //     return [
    //         'result' => [],
    //         'message' => 'Failed to save Loan Request.',
    //         'status' => false
    //     ];
    // }

    public function store(Request $request)
    {
        $user = Auth::user();

        $settings = AdvanceLoanSetting::where('als_b_id', $user->emp_b_id)->first();
        if (!$settings) {
            return response()->json([
                'result'  => [],
                'status'  => false,
                'message' => 'Loan advance settings in this business not found. Please contact administrator to create them first.'
            ]);
        }

        // ✅ Check if an existing request already exists
        $existingRequest = LoanRequest::where('lnr_emp_id', $user->emp_id)
            ->where('lnr_b_id', $user->emp_b_id)
            ->first();

        // if ($existingRequest) {
        //     return response()->json([
        //         'result' => [],
        //         'status' => false,
        //         'message' => 'A loan request already exists for this employee.'
        //     ]);
        // }

        // ✅ Pehle ek temporary LoanRequest object banao validation ke liye
        $tempLoan = new LoanRequest();
        $tempLoan->lnr_emp_id          = $user->emp_id;
        $tempLoan->lnr_b_id            = $user->emp_b_id;
        $tempLoan->lnr_requested_amount = $request->input('requested_amount');
        $tempLoan->lnr_installments    = $request->input('installments');

        // Relation inject karna zaroori hai (salary nikalne ke liye)
        $tempLoan->setRelation('fh_employee', $user->load('fh_employee_salary'));

        // ✅ Loan validation check
        $validation = PayrollLogics::validateLoanAgainstSettings($tempLoan);
        if (!$validation['status']) {
            return response()->json([
                'result'  => [],
                'status'  => false,
                'message' => $validation['message']
            ]);
        }
        $appliedRate = $validation['interest_rate'] ?? 0;
        // ✅ Unique ID generate
        $year = date('Y');
        $month = date('m');
        $currentMonthCount = LoanRequest::where('lnr_b_id', $user->emp_b_id)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        $sno = str_pad($currentMonthCount + 1, 3, '0', STR_PAD_LEFT);
        $unique_id = "LA-{$year}-{$month}-{$sno}";

        // // ✅ Rule Criteria check
        // $ruleCriteria = RuleCriterion::with('fh_approval_module')
        //     ->where('rc_b_id', $user->emp_b_id)
        //     ->where('rc_condition_option_id', 140)
        //     ->whereHas('fh_approval_module', function ($query) {
        //         $query->where('am_module_id', 442)
        //             ->where('am_status', 1);
        //     })->first();

        // $processApprovers = [];
        // $amId = null;
        // $emp_d_id = $user->emp_d_id;

        // if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
        //     $processApprovers = $ruleCriteria->fh_approval_module
        //         ->filteredProcessApprovers($emp_d_id)
        //         ->get();
        // }

        // if (empty($processApprovers)) {
        //     $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 442);
        //     if (!$approvalMapping) {
        //         return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for loan module, contact administration.']);
        //     }
        // } else {
        //     $amId = $ruleCriteria->rc_am_id;
        // }


     // NEW: Try to get employee-wise approval mapping first
    $approvalMapping = \App\Helpers\ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 442);
    $approvalEmpIds = [];
    $amId = null;

    if ($approvalMapping) {
        // Employee-wise mapping exists, get approver emp_ids
        $approvalEmpIds = \App\Helpers\ApprovalHelper::getApprovalArray($approvalMapping);
        // $amId = null; // Store mapping's am_id if necessary (adjust as per actual column)
    } else {
        // Fallback to hierarchy: get RuleCriteria and processApprovers
        $ruleCriteria = RuleCriterion::with('fh_approval_module')
            ->where('rc_b_id', $user->emp_b_id)
            ->where('rc_condition_option_id', 140)
            ->whereHas('fh_approval_module', function ($query) {
                $query->where('am_module_id', 442)
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
        }else {
                return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for loan module, contact administration.']);
            }
     
        }




        // ✅ Ab actual save karo
        $data = new LoanRequest();
        $data->lnr_emp_id  = $user->emp_id;
        $data->lnr_b_id  = $user->emp_b_id;
        $data->lnr_advance_type = $request->input('lnr_advance_type');
        $data->lnr_request_subject = $request->input('request_subject');
        $data->lnr_requested_amount = $request->input('requested_amount');
        $data->lnr_description = $request->input('description');
        $data->lnr_installment_amount = $request->input('installment_amount');
        $data->lnr_installments = $request->input('installments');
        $data->lnr_rate = $appliedRate;
        $data->lnr_start_date = date('Y-m-d', strtotime($request->input('loan_start_date')));
        $data->lnr_unique_id = $unique_id;
        $data->lnr_request_status = 140;
        $data->lnr_am_id = $amId;
        $data->lnr_stage_completed = 0;

        if ($data->save()) {
                 // Notify only the *first* approver (employee-wise or hierarchy wise), like GatePass

                    $title = 'Loan Request';
                    $body = 'A loan request has been submitted by ' . $user->emp_full_name;
                    $additionalData = [
                        'user_id' => $user->emp_id,
                        'notification_type' => 'alert',
                        'route' => '/LoanAdvanceApproval',
                    ];
                    $serviceAccountPath = public_path('fixhr-app-firebase.json');

                    // Compose a list of possible approver emp_ids, in the correct order:
                    //  - for employee-wise, it's approvalEmpIds (from mapping)
                    //  - for hierarchy, it's processApprovers->pluck('pa_emp_id')
                    $notifyEmpIds = [];

                    if (!empty($approvalEmpIds)) {
                        $firstEmpId = reset($approvalEmpIds);
                        $notifyEmpIds[] = $firstEmpId;
                    
                    
                    } elseif (!empty($processApprovers) && $processApprovers->count()) {
                        $firstApprover = $processApprovers->first();
                        if ($firstApprover && $firstApprover->pa_emp_id) {
                            $notifyEmpIds[] = $firstApprover->pa_emp_id;
                        }
                    }

                    foreach ($notifyEmpIds as $approverEmpId) {
                        $emp = Employee::find($approverEmpId);
                        $approver = ApprovalHelper::getApprovalOrRejectionData($data->lnr_id, $data->lnr_request_status, $amId, $approverEmpId, 442);

                        if($emp && $emp->emp_is_notification_enabled){
                        if ($emp && $emp->emp_fcm_token) {
                            FirebaseNotification::sendPushNotification(
                                $title,
                                $body,
                                $emp->emp_fcm_token,
                                $serviceAccountPath,
                                config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                $additionalData
                            );
                        }
                        NotificationHelper::saveNotification(
                            $user->emp_id,
                            $approverEmpId,
                            $title,
                            $body,
                            $additionalData
                        );
                      }
                       
                    }
            return ReturnHelper::jsonApiReturn(LoanResource::collection([$data]));
        }

        return [
            'result' => [],
            'message' => 'Failed to save Loan Request.',
            'status' => false
        ];
    }


    public function show($id)
    {
        $user = Auth::user();

        $data = LoanRequest::with(
            'fh_employee:emp_id,emp_fname,emp_mname,emp_lname,emp_dg_id,emp_d_id,emp_phone,emp_code,emp_profile_photo,emp_full_name,emp_email,emp_status,emp_br_id',
            'fh_employee.fh_department:d_id,d_name',
            'fh_employee.fh_designation:dg_id,dg_name',
            'fh_employee.fh_branch:br_id,br_name',
            'fh_approval_status:m_id,m_name,m_other',
            'fh_module:m_id,m_name,m_other',
            'fh_approval_log2',
        // )->where(DB::raw('md5(lnr_id)'), $id)->first();
            )->where('lnr_id', $id)->first();


        //     $approvalData = ApprovalHelper::getApprovalOrRejectionData($data->lnr_id, $data->lnr_request_status, $data->lnr_am_id);
        // )->where('lnr_id', $id)->first();

        // Get approval data
        $approvalData = ApprovalHelper::getApprovalOrRejectionData($data->lnr_id, $data->lnr_request_status, $data->lnr_am_id, NULL, 442);

        // Check if the user employee mapping can approve start
        $data->approvalData = $approvalData;
        $approval = ApprovalHelper::getApprovalData($data, $user, 'lnr_');
        $masterApproveBtn = $approval['masterApproveBtn'];
        $canApprove = $approval['canApprove'];
        // Check if the user employee mapping can approve end
        return view('admin.setting.attendance-details.gate-pass-requests-show', compact('data', 'approvalData', 'canApprove', 'masterApproveBtn'));
    }

    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = Auth::user();

            // Loan request ko find karo
            $loan = LoanRequest::where('lnr_id', $id)
                ->where('lnr_emp_id', $user->emp_id) // ensure apna loan hi delete kar sake
                ->first();

            if (!$loan) {
                return response()->json([
                    'message' => 'Loan request not found',
                    'status' => false,
                ], 404);
            }

            // ✅ Sirf tab delete jab stage complete nahi hua
            if ($loan->lnr_stage_completed != 0) {
                return response()->json([
                    'message' => 'Approved loan request cannot be deleted',
                    'status' => false,
                ], 403);
            }

            // pehle installments delete karo
            $loan->fh_payroll_loan_installments()->delete();

            // ab loan delete karo
            $loan->delete();

            return response()->json([
                'message' => 'Loan request and linked installments deleted successfully',
                'status' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong: ' . $e->getMessage(),
                'status' => false,
            ], 500);
        }
    }

    public function getLoanLogList(Request $request)
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

         $query = LoanRequest::with('fh_business', 'fh_employee')->where('lnr_emp_id', $user->emp_id);


        if (!is_null($request->input('status'))) {
            $query->where('lnr_status', $request->input('status'));
        }

        if (!is_null($request->input('from_date'))) {
            $query->whereDate('lnr_start_date', '>=', Carbon::createFromFormat('d M, Y', $request->input('from_date'))->startOfDay());
        }


          if (!is_null($request->input('to_date'))) {
            $query->whereDate('lnr_start_date', '<=', Carbon::createFromFormat('d M, Y', $request->input('to_date'))->startOfDay());
        }

        if ($request->input('last_15_days')) {
            $query->whereDate('created_at', '>=', Carbon::now()->subDays(15));
        }

        $data = $query->orderBy('lnr_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        // Check if the result is empty
        if ($data->isEmpty()) {
            return response()->json([
                'result' => [],
                'message' => 'No loan request found',
                'status' => false,
            ]);
        }

        // Return the paginated resource with transformation
        return ReturnHelper::jsonApiReturn(new PaginatedResource($data, LoanLogApiResource::class));
    }



    public function getLoanLogApprovalList(Request $request)
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

       $query = LoanRequest::with('fh_business', 'fh_employee')->where('lnr_b_id', $user->emp_b_id);


         if (!is_null($request->input('status'))) {
            $query->where('lnr_request_status', $request->input('status'));
        }


        if (!is_null($request->input('from_date'))) {
            $query->whereDate('lnr_start_date', '>=', Carbon::createFromFormat('d M, Y', $request->input('from_date'))->startOfDay());
        }


        if (!is_null($request->input('to_date'))) {
            $query->whereDate('lnr_start_date', '<=', Carbon::createFromFormat('d M, Y', $request->input('to_date'))->startOfDay());
        }

        if ($request->input('last_15_days')) {
            $query->whereDate('created_at', '>=', Carbon::now()->subDays(15));
        }

        $data = $query->orderBy('lnr_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        // Check if the result is empty
        if ($data->isEmpty()) {
            return response()->json([
                'result' => [],
                'message' => 'No loan request found',
                'status' => false,
            ]);
        }

        // Return the paginated resource with transformation
        return ReturnHelper::jsonApiReturn(new PaginatedResource($data, LoanLogApiResource::class));
    }
     public function convertNumberToWords($number)
    {
        $number = (int)$number;
        $words = [
            0 => 'Zero',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Forty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety'
        ];
        if ($number < 21) {
            return $words[$number];
        } elseif ($number < 100) {
            return $words[10 * floor($number / 10)] . (($number % 10) ? ' ' . $words[$number % 10] : '');
        } elseif ($number < 1000) {
            return $words[floor($number / 100)] . ' Hundred' . (($number % 100) ? ' and ' . $this->convertNumberToWords($number % 100) : '');
        } elseif ($number < 1000000) {
            return $this->convertNumberToWords(floor($number / 1000)) . ' Thousand' . (($number % 1000) ? ' ' . $this->convertNumberToWords($number % 1000) : '');
        } else {
            return 'Amount too large';
        }
    }


    public function generateloanRequestPdf($loanId)
    {
        // $loanData = LoanRequest::with('fh_business', 'fh_employee')->where(DB::raw('md5(lnr_id)'), $loanId)->first();
        $loanData = LoanRequest::with([
            'fh_business',
            'fh_employee.fh_department',
            'fh_employee.fh_designation',
        ])->where(DB::raw('md5(lnr_id)'), $loanId)->first();
        // dd($loanData);
        if (!$loanData) {
            return response()->json(['status' => false, 'message' => 'Claim not found.'], 404);
        }
        $loanList = LoanRequest::with('fh_business','fh_employee')->where('lnr_b_id', $loanData->lnr_b_id)->get();

        $amount = $loanData->lnr_requested_amount;
        $amountInWords = ucfirst($this->convertNumberToWords($amount)) . ' only';
        $data = [
            'employee' => $loanData->fh_employee,
            'fh_designation' => $loanData->fh_employee->fh_designation,
            'fh_business' => $loanData->fh_business,
            'loan_data' => $loanData,
            'loan_data_in_words' => $amountInWords,
            'current_salary' => '25,000',
            'reason' => 'Medical Expenses',
            'sad_balance' => '0',
            'approved_amount' => '10,000',
            'deduction_per_month' => '2,000',
            'deduction_start_month' => 'June 2025',
        ];

       // dd($data);
        $pdf = Pdf::loadView('admin.payroll.loan_request_pdf', $data)->setPaper('A4', 'portrait');

        return $pdf->stream('loan-request.pdf');


    }

    public function exportEmiScheduleApi($loanId)
    {
        try {
           $loan = LoanRequest::with('fh_employee')
            ->whereRaw('md5(lnr_id) = ?', [$loanId])
            ->first();

            $employee = Employee::with([
                'fh_department',
                'fh_designation',
                'fh_branch',
                'fh_business.fh_admin'
                ])->findOrFail($loan->lnr_emp_id);
            $logoPath = $employee->fh_business->b_logo ?? null;

            if (!$loan) {
                return response()->json([
                    'message' => 'Loan not found',
                    'status' => false,
                ], 404);
            }

            $installments = PayrollLoanInstallment::where('pli_loan_id', $loan->lnr_id)
                ->orderBy('pli_id', 'asc')
                ->get();

            $data = [
                'employee'     => $employee,
                'loan'         => $loan,
                'installments' => $installments,
                'logoPath'     => $logoPath,
            ];

            $pdf = PDF::loadView('admin.payroll.exports.emi-schedule', $data)
                ->setPaper('A4', 'portrait');

            // ✅ Browser me show hoga, bina save kiye
            return $pdf->stream('emi_schedule_' . $loanId . '.pdf');
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
