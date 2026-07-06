<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\PythonController;
use App\Http\Controllers\Api\MenuApiController;
use App\Http\Controllers\Api\CommonApiController;
use App\Http\Controllers\DynamicSearchController;
use App\Http\Controllers\PrivacyPolicyController;
use App\Http\Controllers\Api\SettingApiController;
use App\Http\Controllers\CommonApprovalController;
use \App\Http\Controllers\Api\UserDeviceController;
use \App\Http\Controllers\Api\SelfieVerifyController;
use App\Http\Controllers\Api\FeedbackApiController;
use App\Http\Controllers\Api\Auth\AuthApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\Payroll\EmpPayslipController;
use App\Http\Controllers\Api\Payroll\LoanLogApiController;
use App\Http\Controllers\Api\Payroll\PayrollApiController;
use App\Http\Controllers\Api\Plan\PlanExpenseApiController;
use App\Http\Controllers\Api\Plan\PlanRequestApiController;
use App\Http\Controllers\Api\Employee\EmployeeApiController;
use App\Http\Controllers\Api\Policy\TravelModeApiController;
use App\Http\Controllers\Api\Policy\TravelTypeApiController;
use App\Http\Controllers\Api\Reports\AttReportApiController;
use App\Http\Controllers\Api\Attendance\PunchInApiController;
use App\Http\Controllers\Api\Plan\PlanTadaClaimApiController;
use App\Http\Controllers\Api\Approval\AdvanceLogApiController;
use App\Http\Controllers\Api\Attendance\CheckPolicyController;
use App\Http\Controllers\Api\Attendance\GatePassApiController;
use App\Http\Controllers\Api\Plan\NewPlanExpenseApiController;
use App\Http\Controllers\Api\Approval\ApprovalLogApiController;
use App\Http\Controllers\Api\GatePassConfirmationApiController;
use App\Http\Controllers\Api\Policy\TravelVehicleApiController;
use App\Http\Controllers\Api\Announcement\AnnounceApiController;
use App\Http\Controllers\Api\Approval\RuleCriteriaApiController;
use App\Http\Controllers\Api\Attendance\EmployeeLeaveController;
use App\Http\Controllers\Api\Policy\TravelCategoryApiController;
use App\Http\Controllers\Api\Policy\TravelAllowanceApiController;
use App\Http\Controllers\Api\Approval\ApprovalModuleApiController;
use App\Http\Controllers\Api\Attendance\CompOffApprovalController;
use App\Http\Controllers\Api\Plan\PlanRequestDetailsApiController;
use App\Http\Controllers\Api\Approval\ProcessApproverApiController;
use App\Http\Controllers\Api\Policy\TravelMiscellaneousApiController;
use App\Http\Controllers\Api\Approval\ActionUponRejectionApiController;
use App\Http\Controllers\Api\Attendance\AttendanceApprovalController;
use App\Http\Controllers\Api\Policy\BusinessPolicyDocumentApiController;
use App\Http\Controllers\Api\Policy\TravelDailyAllowanceLodgingApiController;
use App\Http\Controllers\Web\Admin\AttendanceDetails\SummaryAttendanceController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\Plan\LocationTrackerController;
use App\Http\Controllers\Api\AttendanceDashboardApi;
use App\Http\Controllers\Api\Exit\EmployeeExitController;
use App\Http\Controllers\Api\TravelDashboardApi;
use App\Http\Controllers\Api\GptAI\GptAIController;
use App\Http\Controllers\Api\GptAI\TaDaApiController;
use App\Http\Controllers\Api\FixGpt\MonitoringReportController;
use App\Http\Controllers\Api\FixGpt\MonitoringMetaController;
use App\Http\Controllers\Api\FixGpt\EmployeeMetaController;
use App\Http\Controllers\Api\FixGpt\EmployeeSearchController;
use App\Http\Controllers\Api\FixGpt\LeaveMetaController;
use App\Http\Controllers\Api\FixGpt\LeaveReportController;
use App\Http\Controllers\Api\GptAI\TaDaMetaController;
use App\Http\Controllers\Api\Travel\TravelApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('/send-test-mail', [CommonApiController::class, 'sendTestMail']);
Route::prefix('auth')->as('auth.')->group(function () {
    Route::post('login', [AuthApiController::class, 'login']);
    Route::post('loginwithbiometric', [AuthApiController::class, 'loginWithBiometric']);
    Route::post('bg_login', [AuthApiController::class, 'loginBg']);
    Route::post('login_with_phone', [AuthApiController::class, 'loginWithPhone']);
    Route::post('verify_otp', [AuthApiController::class, 'verifyOTP']);
    Route::post('bg_verify_otp', [AuthApiController::class, 'bgVerifyOTP']);
    Route::post('forgot_password', [AuthApiController::class, 'forgotPassword']);
    Route::post('verify_forgot_password_otp', [AuthApiController::class, 'verifyForgotPasswordOTP']);
    Route::post('reset_password', [AuthApiController::class, 'resetPassword']);
    Route::post('login_with_token', [AuthApiController::class, 'loginWithToken'])
        ->middleware('auth:sanctum')
        ->name('login_with_token');
    Route::get('logout', [AuthApiController::class, 'logout'])
        ->middleware('auth:sanctum')
        ->name('logout');
    Route::get('bgLogout', [AuthApiController::class, 'bgLogout'])
        ->middleware('auth:sanctum')
        ->name('bgLogout');
});
Route::get('employee/{emp_code}', [EmployeeApiController::class, 'getEmployeeDataByCode']);
// Route::middleware('auth:web')->get('employee/{emp_code}', [EmployeeApiController::class, 'getEmployeeDataByCode']);
Route::prefix('admin')->as('admin.')->middleware(['auth:sanctum', 'isActive', 'subscription'])->group(function () {
    //employee report
    //monitoring report for fix gpt
    Route::prefix('meta')->group(function () {
        Route::get('/report-types', [MonitoringMetaController::class, 'reportTypes']);
        Route::get('/report-schema', [MonitoringMetaController::class, 'reportSchema']);
    });
    Route::match(['GET', 'POST'], '/monitoring-report', [MonitoringReportController::class, 'index']);
    Route::prefix('employees')->group(function () {
        Route::post('/meta', [EmployeeMetaController::class, 'meta']);
        Route::match(['GET', 'POST'], '/search', [EmployeeSearchController::class, 'search']);
    });
    //leave report for fix gpt
    Route::prefix('leave')->group(function () {
        Route::get('/meta', [LeaveMetaController::class, 'meta']);
        Route::post('/report', [LeaveReportController::class, 'report']);
    });
    Route::get('/monthly-attendance', [GptAIController::class, 'monthlyAttendanceSingleAPI']);
    Route::post('search', [DynamicSearchController::class, 'search']);
    Route::get('menus', [MenuApiController::class, 'getAllMenus']);
    Route::get('menu-permissions', [MenuApiController::class, 'getRoleWiseMenuPermissions']);
    //***************** TaDa Api Start *********************************
    Route::prefix('tada')->as('tada.')->group(function () {
        Route::apiResource('travel_type', TravelTypeApiController::class);
        Route::apiResource('travel_mode', TravelModeApiController::class);
        Route::apiResource('travel_allowance', TravelAllowanceApiController::class);
        Route::apiResource('travel_miscellaneous', TravelMiscellaneousApiController::class);
        Route::apiResource('travel_daily_allowance', TravelDailyAllowanceLodgingApiController::class);
        Route::apiResource('travel_category', TravelCategoryApiController::class);
        Route::apiResource('travel_vehicle', TravelVehicleApiController::class);
        Route::apiResource('travel_request', PlanRequestApiController::class);
        Route::get('get-single-travel-details/{id}', [PlanRequestApiController::class, 'getSingleTravelDetails']);
        Route::get('get-segements-details/{id}', [PlanRequestApiController::class, 'getSegementsDetails']);
        Route::apiResource('new-travel-expense', NewPlanExpenseApiController::class)->except('update');
        Route::post('new-travel-expense/{id}', [NewPlanExpenseApiController::class, 'update']);
        Route::apiResource('action_rejection', ActionUponRejectionApiController::class);
        Route::apiResource('approval_log', ApprovalLogApiController::class)->except(['store']);
        Route::apiResource('approval_module', ApprovalModuleApiController::class);
        Route::apiResource('process_approver', ProcessApproverApiController::class);
        Route::apiResource('rule_criteria', RuleCriteriaApiController::class);
        Route::apiResource('advance_log', AdvanceLogApiController::class);
        Route::apiResource('travel_claim', PlanTadaClaimApiController::class);
        Route::post('travel_claim_custom', [PlanTadaClaimApiController::class, 'updateCustomAmount']);
        Route::apiResource('travel_details', PlanRequestDetailsApiController::class)->except('update');
        Route::apiResource('travel_expense', PlanExpenseApiController::class)->except('update');
        Route::post('travel_expense/{id}', [PlanExpenseApiController::class, 'update']);
        Route::post('travel-update/{id}', [PlanRequestDetailsApiController::class, 'travelUpdate']);
        Route::post('travel-delete/{id}', [PlanRequestDetailsApiController::class, 'travelDelete']);
        Route::post('local-to-outstation-conversion/{id}', [PlanRequestDetailsApiController::class, 'localToOutstationConversion']);
        Route::post('travel_details/{id}', [PlanRequestDetailsApiController::class, 'update']);
        Route::post('plan_travel_details/{id}', [PlanRequestDetailsApiController::class, 'travelDetailsUpdate']);
        Route::get('get_current_plan_ids', [PlanRequestDetailsApiController::class, 'getCurrentPlanIds']);
        Route::post('/start-journey', [PlanRequestDetailsApiController::class, 'startJourney']);
        Route::post('/update-distance', [PlanRequestDetailsApiController::class, 'updateDistance']);
        Route::get('claim_list/{id}', [PlanTadaClaimApiController::class, 'claimList']);
        Route::get('claim-list/{id}', [PlanTadaClaimApiController::class, 'claimDetailList']);
        Route::get('get_sub_expense/{expenseId}', [PlanExpenseApiController::class, 'getSubExpense']);
        Route::get('get-expense/{id}', [PlanExpenseApiController::class, 'getExpenseByPlanId']);
        Route::get('fetch-expense/{id}', [PlanExpenseApiController::class, 'fetchExpenseByPlanId']);
        Route::post('filter-plan', [PlanRequestApiController::class, 'filterPlan']);
        Route::post('filter-mode', [TravelModeApiController::class, 'filterMode']);
        Route::post('filter-vehicle', [TravelVehicleApiController::class, 'filterVehicle']);
        Route::post('filter', [TravelVehicleApiController::class, 'filter']);
        Route::post('process_approval', [ApprovalLogApiController::class, 'approval']);
        Route::post('action_upon_rejection', [ApprovalLogApiController::class, 'actionRejection']);
        Route::get('expense_type/{pttt_id}', [PlanExpenseApiController::class, 'expenseType']);
        Route::get('plan_details_delete/{id}', [PlanRequestDetailsApiController::class, 'DeleteDetails']);
        Route::put('plan_details_update/{id}', [PlanRequestDetailsApiController::class, 'UpdateDetails']);
        Route::post('filter_plan_by_claim', [PlanRequestApiController::class, 'filterPlanByClaim']);
        Route::post('business_filter_plan_by_claim', [PlanRequestApiController::class, 'businessFilterPlanByClaim']);
        Route::get('travel-request-approval-search', [PlanRequestApiController::class, 'travelRequestApprovalSearch']);
        Route::get('status', [PlanRequestApiController::class, 'status']);
        Route::any('eligibility/{id}', [TravelAllowanceApiController::class, 'eligibility']);
        Route::get('allowance_eligibility', [TravelAllowanceApiController::class, 'allowanceEligibility']);
        Route::post('plan_update_status', [PlanRequestApiController::class, 'updateStatus']);
        Route::post('business_filter_claim', [PlanTadaClaimApiController::class, 'businessFilterClaim']);
        Route::post('filter_claim', [PlanTadaClaimApiController::class, 'filterClaim']);
        Route::get('travel_type_name', [TravelTypeApiController::class, 'travelType']);
        Route::post('plan_list', [PlanTadaClaimApiController::class, 'planList']);
        Route::post('details_list', [PlanRequestDetailsApiController::class, 'detailsList']);
        Route::post('store_locations', [PlanRequestDetailsApiController::class, 'storeLocations']);
        Route::get('get_travel_details', [PlanRequestDetailsApiController::class, 'getTravelDetails']);
        Route::get('travel-summary', [TravelApiController::class, 'index']);
        Route::get('acceptance-list/{id}', [PlanTadaClaimApiController::class, 'acceptanceList']);
        Route::get('claim-group-list/{id}', [PlanTadaClaimApiController::class, 'groupClaimList']);
        Route::get('claim-group-travel-list/{id}', [PlanTadaClaimApiController::class, 'claimGroupTravelList']);
        Route::post('individual-tada-queries', [TaDaApiController::class, 'individualTadaQueries']);
        Route::get('travel-details', [TaDaApiController::class, 'travelDetails']);
        Route::get('claim-list', [TaDaApiController::class, 'claimList']);
        Route::get('travel-details-list', [TaDaApiController::class, 'travelDetailsShow']);
        Route::get('expense-category-list', [TaDaApiController::class, 'expenseCategoryList']);
        Route::get('distance-km-based-travel', [TaDaApiController::class, 'distanceKmBasedTravel']);
        //location update
        // Route::post('update_locations/{lc_id}', [PlanRequestDetailsApiController::class, 'updateLocations']);
        Route::middleware(['throttle:global'])->group(function () {
            Route::post('update_locations/{lc_id}', [PlanRequestDetailsApiController::class, 'updateLocations']);
        });
        Route::post('filter_advance_log', [AdvanceLogApiController::class, 'filterAdvanceLog']);
        Route::get('travel_purpose_list', [PlanRequestDetailsApiController::class, 'travelPurpose']);
        Route::get('get_next_approval_detail/{type}/{id}', [PlanRequestDetailsApiController::class, 'getNextApprovalDetails']);
        Route::get('travel_expense_total/{id}', [PlanExpenseApiController::class, 'expense_total']);
        Route::post('/location/update/{id}', [LocationTrackerController::class, 'updateLocation']);
        Route::post('/sync-location', [LocationTrackerController::class, 'syncLocation']);
        Route::get('/locations', [LocationTrackerController::class, 'getLiveLocations']);
        Route::get('/live-locations', [LocationTrackerController::class, 'getLiveLocations']);
    });
    //***************** TaDa Api End *********************************
    //***************** Approval Api Start *********************************
    Route::prefix('approval')->as('approval.')->group(function () {
        Route::post('approval_check', [CommonApprovalController::class, 'checkApproval']);
        Route::post('approval_handler', [CommonApprovalController::class, 'handlerApproval']);
        Route::post('/approve', [CommonApprovalController::class, 'approve']);
        Route::put('deduction_handler', [CommonApprovalController::class, 'handleDeduction']);
    });
    //***************** Approval Api end *********************************
    //***************** Employee Api Start *********************************
    Route::prefix('employee')->as('employee.')->group(function () {
        Route::apiResource('employee', EmployeeApiController::class);
        Route::get('get-initial-data', [EmployeeApiController::class, 'getInitialData']);
        Route::get('get-initial-data-outdoor', [EmployeeApiController::class, 'getInitialDataOutdoor']);
        Route::get('get-outdoor-attendance',[EmployeeApiController::class, 'getOutDoorAttendance']);
        Route::get('get-outdoor-attendance-by-approval',[EmployeeApiController::class, 'getOutdoorAttendanceByApproval']);
        Route::post('apply-outdoor-request', [EmployeeApiController::class, 'applyOutdoorRequest']);
        Route::get('get-employees-list', [EmployeeApiController::class, 'getEmployeeData']);
        Route::post('upload-emp-profile-byadmin', [EmployeeApiController::class, 'uploadEmpImageByAdmin']);
    });
    //***************** Employee Api end *********************************
    // Route::get('/reminder-status', [EmployeeApiController::class, 'reminderStatus']);
    Route::get('/reminder-status', [EmployeeApiController::class, 'approvalPendingReminder']);
    Route::post('/remind-me-later', [EmployeeApiController::class, 'updateRemindMeLater']);
    //***************** Attendance Api Start *********************************
    Route::prefix('attendance')->as('attendance.')->group(function () {
        Route::apiResource('punch_in', PunchInApiController::class);
        Route::post('punch_out', [PunchInApiController::class, 'punchOut']);

        // Outdoor attendance endpoints (use PunchInApiController handlers)
        Route::post('outdoor/checkin', [PunchInApiController::class, 'outdoorStore']);
        Route::post('outdoor/checkout', [PunchInApiController::class, 'punchOutOutDoor']);

        Route::get('get_today_attendance', [PunchInApiController::class, 'getTodayAttendance']);
        Route::get('date_wise_attendance_summary/{date}', [PunchInApiController::class, 'dateWiseAttendanceSummary']);
        Route::get('date_wise_attendance_summary_list/{date}/{status}', [PunchInApiController::class, 'dateWiseAttendanceSummaryList']);
        Route::get('get_date_wise_attendance', [PunchInApiController::class, 'getAttendanceByDate']);
        Route::get('_holiday_', [PunchInApiController::class, 'holiday']);
        Route::apiResource('employee_leave', EmployeeLeaveController::class);
        // Sirf store ke liye middleware
        // Route::post('employee_leave', [EmployeeLeaveController::class, 'store'])
        //     ->middleware(\App\Http\Middleware\RemoveContentLength::class);
        Route::get('get_leave_data', [EmployeeLeaveController::class, 'getLeaveData']);
        Route::get('get-leave-balance', [EmployeeLeaveController::class, 'getLeaveBalance']);
        Route::get('get_leave_list_for_approval', [EmployeeLeaveController::class, 'getLeaveListForApproval']);
        Route::get('get_same_date_leave_list', [EmployeeLeaveController::class, 'getSameDateLeaves']);
        Route::get('daily_approval_list', [AttendanceApprovalController::class, 'index']);
        Route::post('bulk-approve-attendance', [AttendanceApprovalController::class, 'bulkApprove']);
        Route::post('bulk-approve-missed-punch', [AttendanceApprovalController::class, 'bulkApproveMSP']);
        Route::any('check-sandwich-leave', [EmployeeLeaveController::class, 'checkSandwichLeave']);
        Route::get('mis_punch/{isApproval?}', [PunchInApiController::class, 'mispunch']);
        Route::post('mis_punch_store', [PunchInApiController::class, 'mispunchstore']);
        Route::delete('mis_punch_delete/{id}', [PunchInApiController::class, 'misPunchDelete']);
        Route::post('get_in_out_time', [PunchInApiController::class, 'getInOutTime']);
        Route::get('type_id', [PunchInApiController::class, 'typeid']);
        Route::get('mispunch_reason', [PunchInApiController::class, 'mispunchReason']);
        Route::apiResource('gate_pass', GatePassApiController::class);
        Route::get('gate_pass_approval', [GatePassApiController::class, 'getGatePassApprovalList']);
        Route::get('totalAttendance', [PunchInApiController::class, 'totalAttendance']);
        Route::get('attendance_log', [PunchInApiController::class, 'attendanceLog']);
        Route::apiResource('get_data_for_type', CommonApiController::class);
        Route::get('get-policy', [CheckPolicyController::class, 'checkPolicy']);
        Route::post('/upload-visitor-face', [FaceController::class, 'uploadVisitorImageAndAuthenticate'])->name('upload.visitor.face');
        Route::get('/att-report-summary', [AttReportApiController::class, 'attSummary'])->name('att.report.summary');
        // Attendance report (Excel / JSON) slug based
        Route::get('/attendance-report/{slug}', [AttReportApiController::class, 'generateReport'])
            ->name('attendance.report');
        Route::get('overtime-list/{isApproval?}', [AttendanceApprovalController::class, 'otlist']);
    });
    //***************** Attendance Api end *********************************
    Route::prefix('payroll')->group(function () {
        Route::apiResource('loan_log', LoanLogApiController::class);
        Route::get('loan_log_list', [LoanLogApiController::class, 'getLoanLogList']);
        Route::get('loan_advance_approval', [LoanLogApiController::class, 'getLoanLogApprovalList']);
        Route::get('payslip_pdf/{id}', [PayrollApiController::class, 'generatePayslipPdf']);
        Route::apiResource('payslip_list', PayrollApiController::class);
        Route::get('generate_emp_payslip', [EmpPayslipController::class, 'generateEmpPayslip']);
        Route::get('get-payroll-details/{payroll_id}', [PayrollApiController::class, 'getAllPayrollDetails']);
        Route::get('get-payroll-period', [PayrollApiController::class, 'getPayrollPeriods']);
        Route::get('/employee/{empId}/salary-details', [PayrollApiController::class, 'getEmployeeSalaryDetails']);
        Route::get('/employee/{empId}/allowance-summary', [PayrollApiController::class, 'getAllowanceWiseSummary']);
        Route::get('/adhoc-payments', [PayrollApiController::class, 'apiGetAdhocPayments']);
        Route::get('/adhoc-payments/{id}', [PayrollApiController::class, 'apiGetAdhocPaymentDetails']);
        Route::get('/employee-adhoc-payments/{employeeId}', [PayrollApiController::class, 'apiGetEmployeeAdhocPayments']);
    });
    Route::apiResource('events', EventController::class);
    Route::post('/change-password-mobile', [AuthApiController::class, 'changePasswordMobile']);
    Route::post('/change-password-verify', [AuthApiController::class, 'changePasswordVerify']);
    Route::prefix('announcement')->group(function () {
        Route::get('get_role_list', [AnnounceApiController::class, 'getRoleList']);
        Route::post('store_announcement', [AnnounceApiController::class, 'storeAnnouncement']);
        Route::get('get_announcement_list', [AnnounceApiController::class, 'getAnnouncementList']);
        Route::delete('delete_announcement/{id}', [AnnounceApiController::class, 'deleteAnnouncement']);
        Route::get('sent', [AnnounceApiController::class, 'getSentAnnouncements']);
        Route::get('received', [AnnounceApiController::class, 'getReceivedAnnouncements']);
        Route::post('mark-read-all', [AnnounceApiController::class, 'markAllAsRead']);
    });
    Route::post('/employee/upload-image', [AuthApiController::class, 'uploadImage']);
    Route::get('business-policy-documents', [BusinessPolicyDocumentApiController::class, 'index']);
    // Notification API routes
    Route::prefix('notification')->group(function () {
        Route::get('notification_list', [NotificationApiController::class, 'index']);
        Route::post('notification-read/{id}', [NotificationApiController::class, 'markAsRead']);
        Route::post('notification/read-all', [NotificationApiController::class, 'markAllAsRead']);
        Route::delete('notification/delete/{id}', [NotificationApiController::class, 'deleteNotification']);
    });
    Route::prefix('settings')->group(function () {
        Route::post('notifications/toggle', [SettingApiController::class, 'toggleNotification']);
    });
    //gatepass confirmation api
    Route::prefix('gatepass')->group(function () {
        Route::post('confirm-gatepass', [GatePassConfirmationApiController::class, 'confirmGatepass']);
    });
    //device register
    Route::prefix('device')->group(function () {
        Route::post('/device-request', [UserDeviceController::class, 'store']);
        Route::post('/biometric-lock', [UserDeviceController::class, 'isBiometricLock']);
    });
    Route::prefix('selfie')->group(function () {
        Route::post('/selfie-request', [SelfieVerifyController::class, 'store']);
    });
    Route::prefix('feedback')->group(function () {
        Route::post('/submit-feedback', [FeedbackApiController::class, 'submitFeedback']);
        Route::post('/submit-rating', [FeedbackApiController::class, 'saveRating']);
    });


    Route::prefix('employee-exit')->group(function () {
        Route::get('/', [EmployeeExitController::class, 'index']);
        Route::post('/store', [EmployeeExitController::class, 'store']);
        Route::get('/show', [EmployeeExitController::class, 'show']);
        Route::get('delete/{er_id}', [EmployeeExitController::class, 'destroy']);
    });
});

Route::prefix('gpt')->as('gpt.')->middleware(['auth:sanctum', 'isActive', 'subscription'])->group(function () {

    Route::prefix('tada')->as('tada.')->group(function () {

        // ── Meta ──────────────────────────────────────────────────────────────
        Route::prefix('meta')->as('meta.')->group(function () {
            Route::get('/filter-options', [TaDaMetaController::class, 'filterOptions'])->name('filter-options');
            Route::get('/schema',         [TaDaMetaController::class, 'schema'])->name('schema');
        });

        // ── Core TA/DA Queries ────────────────────────────────────────────────
        Route::get('/individual-queries',  [TaDaApiController::class, 'individualTadaQueries'])->name('individual-queries');
        Route::get('/travel-details',      [TaDaApiController::class, 'travelDetails'])->name('travel-details');
        Route::get('/travel-details-show', [TaDaApiController::class, 'travelDetailsShow'])->name('travel-details-show');
        Route::get('/expense-categories',  [TaDaApiController::class, 'expenseCategoryList'])->name('expense-categories');
        Route::get('/distance-km',         [TaDaApiController::class, 'distanceKmBasedTravel'])->name('distance-km');

        // ── Claim List (GET + POST both accepted) ─────────────────────────────
        Route::match(['get', 'post'], '/claims', [TaDaApiController::class, 'claimList'])->name('claims');

    });

});

Route::prefix('face-detection')->group(function () { // Not in use
    Route::get('liveness/create', [FaceController::class, 'createLivenessSession']);
    Route::get('upload-visitor-image', [FaceController::class, 'uploadVisitorImageAndAuthenticate']);
    Route::get('employee/{FaceId}', [PunchInApiController::class, 'getEmployeeByFaceId']);
});
Route::prefix('offline-attendance')->group(function () {
    Route::post('syncOfflineAttendance', [PunchInApiController::class, 'syncOfflineAttendance']);
});
Route::get('admin/tada/travel_claim_pdf/{id}', [PlanTadaClaimApiController::class, 'generateTravelClaimPdf']);
Route::get('admin/payroll/emp_payslip_pdf/{id}', [EmpPayslipController::class, 'empPayslipPdf']);
Route::get('admin/loan/loan_request_pdf/{id}', [LoanLogApiController::class, 'generateloanRequestPdf']);
Route::get('admin/loan/emi-schedule/{loanId}', [LoanLogApiController::class, 'exportEmiScheduleApi']);
Route::get('admin/privacy-policy', [PrivacyPolicyController::class, 'index']);
Route::post('/leave-request/revert', [EmployeeLeaveController::class, 'leaveRequestRevert'])->name('api.leave-request.revert');
Route::get('/business/{id}/attendance-dashboard', [AttendanceDashboardApi::class, 'index']);
Route::get('/business/{id}/travel-dashboard', [TravelDashboardApi::class, 'index']);
