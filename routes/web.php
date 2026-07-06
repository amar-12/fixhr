<?php

use \App\Http\Controllers\Api\UserDeviceController;
use App\Events\PrivateEvent;
use App\Http\Controllers\Api\Approval\AdvanceLogApiController;
use App\Http\Controllers\Api\Attendance\PunchInApiController;
use App\Http\Controllers\Api\SelfieVerifyController;
use App\Http\Controllers\ApiChat\AiChatBoxController;
use App\Http\Controllers\ApprovalFlowController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetReportController;
use App\Http\Controllers\AssetServiceController;
use App\Http\Controllers\AssetTypeController;
use App\Http\Controllers\BatchShiftController;
use App\Http\Controllers\BusinessPolicyDocumentController;
use App\Http\Controllers\CommonApprovalController;
use App\Http\Controllers\CompOffBalanceController;
use App\Http\Controllers\CompOffPolicyController;
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\DemoRequestController;
use App\Http\Controllers\DemoSetupController;
use App\Http\Controllers\DropdownOptionController;
use App\Http\Controllers\DynamicFormController;
use App\Http\Controllers\EmpLeaveCalendar;
use App\Http\Controllers\EmployeeCalendar;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\LivewireController\MainController;
use App\Http\Controllers\MailTemplateController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PaymentModeController;
use App\Http\Controllers\Payroll\AdhocComponentController;
use App\Http\Controllers\Payroll\AdhocPayDeducController;
use App\Http\Controllers\Payroll\AdvanceLoanController;
use App\Http\Controllers\Payroll\FinancialYearController;
use App\Http\Controllers\Payroll\LoanPolicyController;
use App\Http\Controllers\Payroll\PayrollController;
use App\Http\Controllers\Payroll\PayrollHoldSalaryController;
use App\Http\Controllers\Payroll\PayrollPeriodController;
use App\Http\Controllers\Payroll\PayRunController;
use App\Http\Controllers\Payroll\Taxation\LedgerController;
use App\Http\Controllers\Payroll\Taxation\ChallanController;
use App\Http\Controllers\Payroll\Taxation\TaxConfigController;
use App\Http\Controllers\Payroll\Taxation\FormGenerationController;

use App\Http\Controllers\Payroll\PayslipConfigController;
use App\Http\Controllers\Payroll\PayslipController;
use App\Http\Controllers\Payroll\RecurringPayDeducController;
use App\Http\Controllers\Payroll\ReportPayrollController;
use App\Http\Controllers\Payroll\Settings\BsnsPaySettingController;
use App\Http\Controllers\Payroll\WeeklyPayrunController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Http\Controllers\TadaReimburseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Web\Admin\AcademicDetailController;
use App\Http\Controllers\Web\Admin\AdvanceLogController;
use App\Http\Controllers\Web\Admin\ApprovalSettingsController;
use App\Http\Controllers\Web\Admin\AttendanceDashboardController;
use App\Http\Controllers\Web\Admin\AttendanceDetails\AttendanceController;
use App\Http\Controllers\Web\Admin\AttendanceDetails\GatePassController;
use App\Http\Controllers\Web\Admin\AttendanceDetails\LeaveBalanceController;
use App\Http\Controllers\Web\Admin\AttendanceDetails\LeaveController;
use App\Http\Controllers\Web\Admin\AttendanceDetails\LeaveManagementController;
use App\Http\Controllers\Web\Admin\AttendanceDetails\LoanRequestController;
use App\Http\Controllers\Web\Admin\AttendanceDetails\MispunchController;
use App\Http\Controllers\Web\Admin\AttendanceDetails\MonthlyAttendanceController;
use App\Http\Controllers\Web\Admin\AttendanceDetails\OpeningBalanceController;
use App\Http\Controllers\Web\Admin\AttendanceDetails\ReportAttendanceController;
use App\Http\Controllers\Web\Admin\AttendanceDetails\ReportLeaveController;
use App\Http\Controllers\Web\Admin\AttendanceDetails\SummaryAttendanceController;
use App\Http\Controllers\Web\Admin\AttendanceSettings\AttendancePolicyController;
use App\Http\Controllers\Web\Admin\AttendanceSettings\AttendanceShiftTypeController;
use App\Http\Controllers\Web\Admin\AttendanceSettings\ELPolicyController;
use App\Http\Controllers\Web\Admin\AttendanceSettings\LeavePolicyController;
use App\Http\Controllers\Web\Admin\AttendanceSettings\OvertimePolicyController;
use App\Http\Controllers\Web\Admin\AttendanceSettings\PolicyHolidayController;
use App\Http\Controllers\Web\Admin\AttendanceSettings\ShiftPolicyController;
use App\Http\Controllers\Web\Admin\AttendanceSettings\WeeklyPolicyController;
use App\Http\Controllers\Web\Admin\AutomationRulesController;
use App\Http\Controllers\Web\Admin\ClaimGroupRequestController;
use App\Http\Controllers\Web\Admin\ClaimRequestController;
use App\Http\Controllers\Web\Admin\CreateBusinessController;
use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\EmployeeController;
use App\Http\Controllers\Web\Admin\EmployeePayrollController;
use App\Http\Controllers\Web\Admin\EmployeeSelfServiceController;
use App\Http\Controllers\Web\Admin\exit\EmployeeExitController;
use App\Http\Controllers\Web\Admin\FamilyDetailController;
use App\Http\Controllers\Web\Admin\Kit\KitAssignmentController;
use App\Http\Controllers\Web\Admin\Kit\KitController;
use App\Http\Controllers\Web\Admin\Kit\KitDamageController;
use App\Http\Controllers\Web\Admin\Kit\KitLogController;
use App\Http\Controllers\Web\Admin\Kit\KitReplacementController;
use App\Http\Controllers\Web\Admin\Kit\KitStockController;
use App\Http\Controllers\Web\Admin\MenuController;
use App\Http\Controllers\Web\Admin\ProjectController;
use App\Http\Controllers\Web\Admin\Recruitment\RecruitmentCandidateController;
use App\Http\Controllers\Web\Admin\Recruitment\RecruitmentController;
use App\Http\Controllers\Web\Admin\Recruitment\RecruitmentPipelineController;
use App\Http\Controllers\Web\Admin\Recruitment\RecruitmentScheduleInterviewController;
use App\Http\Controllers\Web\Admin\Recruitment\RecruitmentSkillController;
use App\Http\Controllers\Web\Admin\Recruitment\RecruitmentStageController;
use App\Http\Controllers\Web\Admin\ReportController;
use App\Http\Controllers\Web\Admin\RoleSettings\AppRolesHasPermissionsController;
use App\Http\Controllers\Web\Admin\RoleSettings\RoleController;
use App\Http\Controllers\Web\Admin\RoleSettings\RolePermissionController;
use App\Http\Controllers\Web\Admin\SettingController;
use App\Http\Controllers\Web\Admin\TadaSettings\TadaController;
use App\Http\Controllers\Web\Admin\TadaSettings\TaDaReportController;
use App\Http\Controllers\Web\Admin\TadaSettings\TadaSettlementController;
use App\Http\Controllers\Web\Admin\TadaSettings\TravelAllowanceController;
use App\Http\Controllers\Web\Admin\TravelRequestController;
use App\Http\Controllers\Web\Admin\UniformDetailController;
use App\Http\Controllers\Web\Auth\Employee2FAController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\PDFController;
use App\Http\Controllers\Web\TadaRequestController;
use App\Http\Controllers\Web\TestController;
use App\Http\Middleware\TwoFactorMiddleware;
use App\Models\AttendancePolicy;
use App\Models\LoanRequest;
use App\Models\PolicyTadaTravelMode;
use App\Models\ProcessApprover;
use App\Models\TadaReimburse;
use App\Models\TadaRequestPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::post('/employee/device-restriction/save', [EmployeeSelfServiceController::class, 'saveDeviceRestriction'])
    ->name('employee.device.restriction.save');

Route::post('/employee/device-restriction/save-all', [EmployeeSelfServiceController::class, 'saveDeviceRestrictionAll'])
    ->name('employee.device.restriction.saveAll');
Route::get('/get/employee/device-restriction', [EmployeeSelfServiceController::class, 'getDeviceRestriction'])
    ->name('employee.device.restriction.get');

Route::get('/leave-balance-excel-download', [LeaveBalanceController::class, 'downloadSampleExcel'])->name('leave.downloadExcel');

Route::middleware(['maintenance'])->group(function () {
    Route::prefix('demo-setup')->name('demo.setup.')->group(function () {
        Route::get('/', [DemoSetupController::class, 'start'])
            ->name('start');
        Route::post('/save', [DemoSetupController::class, 'store'])
            ->name('store');
    });
    Route::get('/check-session', function () {
        return Auth::check()
            ? response()->json(['status' => 'alive'])
            : response()->json(['status' => 'expired'], 401);
    });
    Route::post('/check-pending-approval-requests', [ApprovalSettingsController::class, 'checkPendingApprovalRequests'])->name('check.pending.approval.requests');
    Route::post('/reset-approval-flow', [ApprovalSettingsController::class, 'resetApprovalFlow'])->name('reset.approval.flow');
    Route::post('/get-leave-balance', [EmployeeSelfServiceController::class, 'getLeaveBalance'])->name('get.leave.balance');
    Route::post('/update-leave-balance', [EmployeeSelfServiceController::class, 'updateLeaveBalance'])->name('update.leave.balance');
    Route::get('/leave-opening-sample-export', [EmployeeSelfServiceController::class, 'leaveOpeningExport'])->name('leave.opening.sample.export');
    Route::post('/leave-opening-import', [EmployeeSelfServiceController::class, 'leaveOpeningImport'])->name('leave.opening.import');
    Route::get('/admin/ta-da-request/report', [ReportController::class, 'index']);
    // Route::get('/employee-self-service', [EmployeeSelfServiceController::class, 'index'])->name('employee.self.service');
    Route::get('/get-employee-approval', [EmployeeSelfServiceController::class, 'getEmployeeApproval'])->name('get.employee.approval');
    Route::post('/change-employee-manager', [EmployeeSelfServiceController::class, 'changeEmployeeManager'])->name('change.employee.manager');
    Route::get('/missed-punch/export', [EmployeeSelfServiceController::class, 'missedPunchExport'])->name('missed.punch.export');
    Route::get('/employee/field/sample', [EmployeeSelfServiceController::class, 'downloadSampleField'])
        ->name('employee.field.sample');
    Route::post('/employee/field/import', [EmployeeSelfServiceController::class, 'employeeFieldImport'])
        ->name('employee.field.import');
    Route::get('/', function () {
        return view('welcome');
    });
    Route::get('/privacy-policy', function () {
        return view('privacy-policy');
    });
    Route::get('/onboarding', function () {
        return view('admin.offboarding.index');
    });
    Route::get('/employee-calendar/upload', [EmployeeCalendar::class, 'empCalendarSampleExport'])->name('employee.calendar.downloadExcel');
    Route::post('/employee-calendar/import', [EmployeeCalendar::class, 'empCalendarImport'])->name('employee.calendar.import');
    Route::post('/daily-attendance/bulk-approval', [CommonApprovalController::class, 'commonApproveBulk'])->name('admin.common-bulk-approval');
    Route::post('/toggle-deduction', [EmployeePayrollController::class, 'getDeductionFlags'])->name('toggle.deduction');
    Route::post('/admin/settings/business/new-user-role', [SettingController::class, 'newUserRole'])->name('admin.newuser.role');
    Route::post('/change-business-password', [CreateBusinessController::class, 'changeBusinessPassword'])->name('change.business.password')->middleware('auth');
    // Route::get('/export-smhistory/{id}', [EmployeePayrollController::class, 'exportSalaryMasterHistoryExcel'])->name('export.smhistory');
    Route::get('/admin/ta-da-request/tada-details/show/{id}', [TadaController::class, 'tadaDetailsShow'])->name('tadadetails'); // view all details travel id pass
    Route::get('payslip/{employee_id}/{month}/{year}', [PayrollController::class, 'generatePayslip']);
    Route::post('/generate-payslip', [PayrollController::class, 'generatePayslip'])->name('generate.payslip');
    Route::get('/payslip/pdf/{employee_id}/{month}/{year}', [PayrollController::class, 'generatePayslipPdf'])
        ->name('payslip.pdf');
    Route::any('/get-earning-name', [PayrollController::class, 'getEarningName'])->name('get.earning.name');
    Route::get('/admin/report/bank-sheet-export', [ReportPayrollController::class, 'bankSheetExport'])->name('bank.sheet.export');
    Route::get('/admin/report/employee-payroll-report-export-sheet', [ReportPayrollController::class, 'payrollReportSheet'])->name('employee.payroll.report.export.sheet');
    Route::post('admin/report/pf-eps-sheet-report-export', [ReportPayrollController::class, 'pfEpsSheetReportExport'])->name('pf.eps.sheet.report.export');
    Route::get('/admin/report/mc-template-report-export-sheet', [ReportPayrollController::class, 'mcTempReport'])->name('mc.template.report.export.sheet');
    Route::post('admin/report/mc-template-sheet-export', [ReportPayrollController::class, 'mcTempReportExport'])->name('mc.template.sheet.export');
    Route::post('admin/report/payroll-sheet-report-export', [ReportPayrollController::class, 'payrollSheetReportExport'])->name('payroll.sheet.report.export');
    Route::post('/generate-payslip', [PayrollController::class, 'generatePayslip'])->name('generate.payslip');
    Route::get('/get-employee-deduction-flags/{id}', [App\Http\Controllers\Web\Admin\EmployeePayrollController::class, 'getDeductionFlags']);
    Route::get('payroll/attendance/retrieve', [PayrollPeriodController::class, 'retrieveAttendance'])->name('payroll.attendance.retrieve');
    Route::post('/freeze-payroll-attendance', [PayrollPeriodController::class, 'freezeAttendance'])->name('freeze.payroll.attendance');
    Route::post('unfreeze/payroll/attendance/{id}', [PayrollPeriodController::class, 'unfreezeAttendance'])->name('unfreeze.payroll.attendance');
    Route::get('/get-process-salary/{id}', [PayrollPeriodController::class, 'getProcessSalary'])->name('payroll.process-salary');
    Route::post('/unprocess-selected-salaries', [PayrollPeriodController::class, 'unprocessSalaries'])->name('unprocess-salaries');
    Route::get('payslip/{employee_id}/{month}/{year}', [PayrollPeriodController::class, 'generatePayslip']);
    Route::post('/process-selected-salaries', [PayrollPeriodController::class, 'processSalaries'])->name('process-salaries');
    Route::post('/unprocess-selected-salaries', [PayrollPeriodController::class, 'unprocessSalaries'])->name('unprocess-salaries');
    Route::post('admin/report/payroll-sheet-report-export', [ReportPayrollController::class, 'payrollSheetReportExport'])->name('payroll.sheet.report.export');
    Route::delete('/payroll-period/delete/{id}', [PayrollPeriodController::class, 'deletePayrollPeriod']);
    Route::get('/payslip/download/{id}', [PayrollPeriodController::class, 'payslipDownload'])->name('payroll.payslip.download');
    Route::get('employee-other-allowance', [EmployeePayrollController::class, 'empOtherAllowance'])->name('employee.other.allowance');
    Route::post('/import-employees-salary', [EmployeePayrollController::class, 'employeesSalaryImport'])->name('employees.salary.import');
    Route::get('employee-salary/export', [EmployeePayrollController::class, 'employeesSalaryexport'])->name('employee.salary.export');
    Route::get('/view-payslip/{id}', [PayrollPeriodController::class, 'viewPayslip'])->name('salary.viewPayslip');
    Route::get('/view-payslip2/{id}', [PayrollPeriodController::class, 'viewPayslip2'])->name('salary.viewPayslip2');
    Route::get('/payslip/download/{id}', [PayrollPeriodController::class, 'downloadPayslip'])->name('salary.downloadPayslip');
    Route::get('/payslip/download2/{id}', [PayrollPeriodController::class, 'downloadPayslip2'])->name('salary.downloadPayslip2');
    Route::get('/payslip/download/{id}', [PayrollPeriodController::class, 'downloadPayslip'])->name('salary.downloadPayslip');
    Route::get('/payslip/download2/{id}', [PayrollPeriodController::class, 'downloadPayslip2'])->name('salary.downloadPayslip2');
    Route::get('/loan-approval-list', [AdvanceLoanController::class, 'loanApprovalList'])->name('loan.approval.list');
    Route::get('/loan-details/{id}', [AdvanceLoanController::class, 'loanDetails'])->name('loan.details');
    Route::post('/loan-approve/{id}', [AdvanceLoanController::class, 'approveLoan'])->name('loan.approve');
    Route::post('loan/save-loan-details/{loan}', [AdvanceLoanController::class, 'saveLoanDetails'])->name('save.loan.details');
    Route::post('/loan-reject/{id}', [AdvanceLoanController::class, 'rejectLoan'])->name('loan.reject');
    Route::get('/get-adhoc-components', [AdhocPayDeducController::class, 'getAdhocComponents'])->name('get.adhoc.components');
    Route::post('/adhoc/confirmation', [AdhocPayDeducController::class, 'showConfirmation'])->name('adhoc.confirmation');
    Route::post('/attendance-salary-check', [AdhocPayDeducController::class, 'checkSalaryProcessed'])->name('employee.salary.check');
    Route::post('/adhoc-components/edit/{id}', [AdhocComponentController::class, 'updateAdhoc'])->name('adhoc-components.updateAdhoc');
    Route::delete('/adhoc-components/delete/{id}', [AdhocComponentController::class, 'destroy'])->name('adhoc-components.destroy');
    Route::post('/comp-off/bulk-approval', [CommonApprovalController::class, 'compoffApproveBulk'])->name('admin.comp-off-bulk-approval');
    Route::post('/store-location-by-web', [LoginController::class, 'storeLocationByWeb'])->name('store.location.web');
    Route::middleware('userCheck')->middleware('loginCheck')->group(function () {
        Route::prefix('login')->group(function () {
            Route::get('/', [LoginController::class, 'index'])->name('login');
            Route::any('/submit', [LoginController::class, 'submit'])->name('login.submit');
            Route::post('/check-user', [LoginController::class, 'checkUser'])->name('login.checkUserUser');
            Route::any('/otp', [LoginController::class, 'login_otp'])->name('login.otp');
            Route::post('/check-otp', [LoginController::class, 'checkOTP'])->name('login.checkUserOTP');
            Route::get('/forget-password', [LoginController::class, 'newForgetPasswordPage'])->name('newForget.password');
            Route::post('/reset-password', [LoginController::class, 'newForgetPassword'])->name('login.resetpassword');
            Route::post('/check-password', [LoginController::class, 'checkUserPassword'])->name('login.checkUserPassword');
            Route::post('/verify-password-2fa', [LoginController::class, 'verifyPassword2FA'])->name('login.verifyPassword2FA');
            Route::post('/verify-2fa-public', [LoginController::class, 'verify2FA'])->name('login.verify2FA.public');
        });
        Route::prefix('signup')->group(function () {
            Route::get('/', [CreateBusinessController::class, 'index'])->name('signup');
            Route::get('/otp', [CreateBusinessController::class, 'otp'])->name('signup.otp');
            Route::get('/create', [CreateBusinessController::class, 'create'])->name('createBusiness');
            Route::post('/verify', [CreateBusinessController::class, 'verify'])->name('businessVerify');
            Route::post('/verify-phone', [CreateBusinessController::class, 'verifyPhone'])->name('verify.phone');
            Route::post('/verify-otp', [CreateBusinessController::class, 'verifyOtp'])->name('verify.otp');
            Route::post('/get-state', [CreateBusinessController::class, 'getState'])->name('get.state');
            Route::post('/get-city', [CreateBusinessController::class, 'getCity'])->name('get.city');
            Route::post('/validate-gst-num', [CreateBusinessController::class, 'validateGst'])->name('validate.gst');
            Route::post('/save-business', [CreateBusinessController::class, 'saveBusiness'])->name('save.business');
        });
    });
    Route::get('/admin/report/travelling-expense-statement', [ReportController::class, 'expenseStatement'])->name('export.expenseStatement');
    Route::get('/admin/report/application-payment', [ReportController::class, 'paymentApplication'])->name('export.applicationPayment');
    Route::get('/admin/report/expense-details', [ReportController::class, 'expenseDetails']);
    Route::get('/reverb', function () {
        return view('admin.dashboard.dashboard');
    });
    Route::post('/payroll/template/save', [PayrollController::class, 'storePayrollTemplate'])->name('salary-template.store');
    Route::post('admin/employee/quick-add-emp', [EmployeeController::class, 'quickAddEmp'])->name('addEmp.quickAddEmp');
    Route::middleware('userCheck')->group(function () {
        Route::middleware(['subscription'])->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('/attendance-dashboard', [AttendanceDashboardController::class, 'index'])->name('attendance.dashboard');
            Route::post('/attendance-dashboard', [AttendanceDashboardController::class, 'index']);
            Route::get('/invoice', [AttendanceDashboardController::class, 'get'])->name('invoice');
            Route::prefix('/admin')->group(function () {
                Route::prefix('/employee')->group(function () {
                    Route::any('/', [EmployeeController::class, 'index'])->name('employee.index');
                    Route::resource('employee-calendar', EmployeeCalendar::class);
                    Route::resource('batch-assign-shift', BatchShiftController::class);
                    Route::any('/get-field-options', [EmployeeController::class, 'getFieldOptions'])->name('get.field.options');
                    Route::post('/bulk-update', [EmployeeController::class, 'bulkUpdate'])->name('admin.employee.bulk.update');
                    Route::get('/form', [EmployeeController::class, 'form'])->name('employee.form');
                    Route::get('/form/{id}', [EmployeeController::class, 'form'])->name('update.employee');
                    Route::post('/get-qualification', [EmployeeController::class, 'getQualifications'])->name('get.qualification');
                    Route::post('/check-emp-exist', [EmployeeController::class, 'checkEmployeeId'])->name('addEmp.checkEmpID');
                    Route::post('/add-emp-data', [EmployeeController::class, 'saveData'])->name('addEmp.saveData');
                    Route::post('/add-state-city', [EmployeeController::class, 'getStateCity'])->name('addEmp.getStateCity');
                    Route::post('/check-ifsc-code', [EmployeeController::class, 'getIFSCDetails'])->name('addEmp.checkIFSC');
                    Route::post('/check-email-phone', [EmployeeController::class, 'checkPhoneEmail'])->name('addEmp.checkMailPhone');
                    Route::post('/upload-employee-profile', [EmployeeController::class, 'uploadEmployeeAvatar'])->name('addEmp.uploadProfilePic');
                    Route::any('/get-employee-data', [EmployeeController::class, 'getEmployeeData'])->name('addEmp.getEmployeeData');
                    Route::post('/bulk-image-upload', [EmployeeController::class, 'bulkImageUpload'])->name('employee.bulk.image.upload');
                    Route::get('/payroll', [EmployeePayrollController::class, 'emplyeeSalary'])->name('employee.payroll');
                    Route::get('/payroll-add-edit/{id?}', [EmployeePayrollController::class, 'addEditEmployeeSalary'])->name('employee.addEdit.payroll');
                    Route::get('/payroll-manual-monthly-edit/{id?}', [EmployeePayrollController::class, 'addEditMMonthlyManualEmployeeSalary'])->name('employee.manual.salary.monthly');
                    Route::get('/payroll-daily-add-edit/{id?}', [EmployeePayrollController::class, 'addEditDailyEmployeeSalary'])->name('employee.salary.master.daily');
                    Route::get('/payroll-weekly-daily-wise-add-edit/{id?}', [EmployeePayrollController::class, 'addEditWeeklyDailyWiseEmployeeSalary'])->name('employee.salary.master.weekly.daily-wise');

                     Route::post('/save-payroll-weekly-daily-wise', [EmployeePayrollController::class, 'saveWeeklyDailywiseEmplyeeSalary'])->name('save.weekly.daily.wise');
                    Route::post('/payroll', [EmployeePayrollController::class, 'saveEmplyeeSalary'])->name('save.employee.payroll');
                    Route::get('/payroll/slip/{id}', [EmployeePayrollController::class, 'pdfDownload'])->name('payslip.download');
                    // Route::get('/payroll/{id}', [EmployeePayrollController::class, 'deleteSalary'])->name('employee.delete.payroll');
                    Route::get('/get-payroll-calculation', [EmployeePayrollController::class, 'getPayrollCalculation'])->name('get.payroll.calculation');
                    Route::get('/get-payroll-existdata-check', [EmployeePayrollController::class, 'existDataCheck'])->name('get.payroll.exist.data');
                    Route::get('/payroll/payslip', [EmployeePayrollController::class, 'getPayrollSlip'])->name('get.payroll.slip');
                    Route::post('/payroll/payslip', [EmployeePayrollController::class, 'paySlipStore'])->name('store.payroll.slip');
                    Route::delete('/payroll/payslip/{id}', [EmployeePayrollController::class, 'paySlipDestroy'])->name('destroy.payroll.slip');
                    Route::get('/get-earnings', [EmployeePayrollController::class, 'getEarnings']);
                    Route::get('/export-smhistory/{id}', [EmployeePayrollController::class, 'exportSalaryMasterHistoryExcel'])->name('export.smhistory');
                    Route::get('/view-employee-payslip/{id}', [PayrollPeriodController::class, 'viewEmpPayslip'])->name('view.employee.payslip');
                    // academic
                    Route::prefix('/academic')->name('academic.')->group(function () {
                        Route::get('/', [AcademicDetailController::class, 'index'])->name('index');
                        Route::get('/create', [AcademicDetailController::class, 'create'])->name('create');
                        Route::post('/store', [AcademicDetailController::class, 'store'])->name('store');
                        Route::delete('/delete/{id}', [AcademicDetailController::class, 'destroy'])->name('destroy');
                        Route::get('/edit/{id}', [AcademicDetailController::class, 'edit'])->name('edit');
                        Route::put('/update/{id}', [AcademicDetailController::class, 'update'])->name('update');
                        Route::get('/get-courses', [AcademicDetailController::class, 'getCourses'])->name('get.courses');
                        Route::get('/get-specialization', [AcademicDetailController::class, 'getspecialization'])->name('get.specialization');
                        Route::get('/get-bord', [AcademicDetailController::class, 'getbords'])->name('get.bords');
                        Route::get('/export', [AcademicDetailController::class, 'export'])->name('export');
                        Route::post('/import', [AcademicDetailController::class, 'import'])->name('import');
                        Route::get('/emp-details', [AcademicDetailController::class, 'emp_details_index'])->name('emp_details.index');
                    });
                    // uniforms
                    Route::prefix('/uniforms')->group(function () {
                        Route::get('/uniform', [UniformDetailController::class, 'uniform_index'])->name('uniform_index.index');
                        Route::get('/uniform/create', [UniformDetailController::class, 'uniform_create'])->name('uniform_index.create');
                        Route::post('/uniform/store', [UniformDetailController::class, 'uniform_store'])->name('uniform_index.store');
                        Route::delete('/uniform/delete/{id}', [UniformDetailController::class, 'uniform_destroy'])->name('uniform_index.destroy');
                        Route::get('/uniform/edit/{id}', [UniformDetailController::class, 'uniform_edit'])->name('uniform_index.edit');
                        Route::put('/uniform/update/{id}', [UniformDetailController::class, 'uniform_update'])->name('uniform_index.update');
                        Route::get('/uniform/get-colors', [UniformDetailController::class, 'uniform_colors'])->name('uniform_index.color');
                        Route::get('/get-uniform/{id}', [UniformDetailController::class, 'getuniform'])->name('get.uniform');
                    });
                    // Family Details
                    Route::prefix('family-details')->group(function () {
                        Route::get('/', [FamilyDetailController::class, 'index'])->name('family.index');
                        Route::get('/create', [FamilyDetailController::class, 'create'])->name('family.create');
                        Route::post('/', [FamilyDetailController::class, 'store'])->name('family.store');
                        Route::get('/edit/{id}', [FamilyDetailController::class, 'edit'])->name('family.edit');
                        Route::put('/update/{id}', [FamilyDetailController::class, 'update'])->name('family.update');
                        Route::delete('/delete/{id}', [FamilyDetailController::class, 'destroy'])->name('family.destroy');
                        Route::get('/{id}', [FamilyDetailController::class, 'getFamilyDetails'])->name('get.getfamily');
                        Route::post('/show', [FamilyDetailController::class, 'show'])->name('family.show');
                    });
                    //Employee self service
                    Route::get('/employee-self-service', [EmployeeSelfServiceController::class, 'index'])->name('employee.self.service');
                });
                Route::resource('reimburse', TadaReimburseController::class);
                Route::resource('settlement', TadaSettlementController::class);
                Route::prefix('/ta-da-request')->group(function () {
                    Route::get('/travel', [TravelRequestController::class, 'index'])->name('travel.request.index');
                    Route::get('/travel-request', [TravelRequestController::class, 'index2'])->name('travel.request.index2');
                    // advance route
                    Route::post('/advance_log', [AdvanceLogApiController::class, 'store'])->name('advancelog.store');
                    Route::post('/travel/{id}', [TravelRequestController::class, 'update'])->name('travel.request.update');
                    Route::get('/travel/show/{id}', [TravelRequestController::class, 'show'])->name('travel.request.show');
                    Route::get('/travel-request/show/{id}', [TravelRequestController::class, 'show'])->name('travel-request.request.show');
                    Route::get('/travel/edit/{id}', [TravelRequestController::class, 'edit'])->name('travel.request.edit');
                    Route::post('/bulk-handler', [CommonApprovalController::class, 'handlerBulk'])->name('admin.bulk-handler');
                    Route::post('/approval-handler', [CommonApprovalController::class, 'handlerApproval'])->name('admin.approval-handler');
                    Route::post('/deduction-handler', [CommonApprovalController::class, 'handleDeduction'])->name('admin.deduction-handler');
                    Route::get('/claim', [ClaimRequestController::class, 'index'])->name('claim.request.index1');
                    Route::get('/claim-request', [ClaimRequestController::class, 'index2'])->name('claim.request.index2');
                    Route::get('/claim-request-is-paid', [ClaimRequestController::class, 'index3'])->name('claim.request.index3');
                    Route::post('/claim-request-is-paid', [ClaimRequestController::class, 'store'])->name('claim.request.is.paid.store');
                    Route::get('/claim/show/{id}', [ClaimRequestController::class, 'show'])->name('claim.request.show');
                    Route::get('/claim-request/show/{id}', [ClaimRequestController::class, 'show'])->name('claim-request.request.show');
                    Route::get('/claim-request-is-paid/show/{id}', [ClaimRequestController::class, 'show'])->name('claim-request-is-paid.request.show');
                    Route::post('/claim/{id}', [ClaimRequestController::class, 'destroy'])->name('claim-request.destroy');
                    Route::get('/claim-group', [ClaimGroupRequestController::class, 'index'])->name('claim-group.request.index1');
                    Route::get('/claim-group-request', [ClaimGroupRequestController::class, 'index2'])->name('claim-group.request.index2');
                    Route::get('/claim-group-request-is-paid', [ClaimGroupRequestController::class, 'index3'])->name('claim-group.request.index3');
                    Route::post('/claim-group/details/{id}', [ClaimGroupRequestController::class, 'getClaimDetails'])->name('travel.claim-group.details');
                    Route::get('/claim-group/show/{id}', [ClaimGroupRequestController::class, 'show'])->name('claim-group.request.show');
                    Route::get('/approved-claim', [ClaimRequestController::class, 'approvedindex'])->name('approved_claim.index');
                    Route::get('travel-claim/group-report/{ids}', [\App\Http\Controllers\Web\Admin\ClaimGroupRequestController::class, 'groupReport'])
                        ->withoutMiddleware(['userCheck'])
                        ->middleware(['auth'])
                        ->name('travel.claim.group.report');
                    Route::get('/travel-claim/report/{id}', [ClaimRequestController::class, 'travelClaim'])->name('travel.claim.report');
                    Route::get('/advance', [AdvanceLogController::class, 'index'])->name('advance.request.index');
                    Route::post('/advance/store', [AdvanceLogController::class, 'store'])->name('advance.request.store');
                    Route::get('advance/edit/{id}', [AdvanceLogController::class, 'edit'])->name('advance.request.edit');
                    Route::put('advance/handle-approval/{id}', [AdvanceLogApiController::class, 'update'])->name('handle.advance.approval');
                });
                Route::prefix('/role')->group(function () {
                    Route::any('/', [RoleController::class, 'index']);
                    Route::prefix('/permission')->group(function () {
                        Route::get('/', [RolePermissionController::class, 'index'])->name('role.permission.index');
                        Route::post('/save', [RolePermissionController::class, 'store'])->name('role.permission.store');
                        Route::delete('/{permission}', [RolePermissionController::class, 'destroy'])->name('role.permission.delete');
                        Route::get('/role-wise/{id}', [RolePermissionController::class, 'getRolePermissions'])->name('role.permission.roleWise');
                    });
                });
                Route::prefix('/app-role')->group(function () {
                    Route::any('/', [RoleController::class, 'index']);
                    Route::prefix('/permission')->group(function () {
                        Route::get('/', [AppRolesHasPermissionsController::class, 'index'])->name('app-role.permission.index');
                        Route::delete('/{permission}', [AppRolesHasPermissionsController::class, 'destroy'])->name('app-role.permission.destroy');
                        Route::post('/save', [AppRolesHasPermissionsController::class, 'store'])->name('app.role.permission.store');
                        Route::get('/role-wise/{id}', [AppRolesHasPermissionsController::class, 'getRolePermissions'])->name('app.role.permission.roleWise');
                    });
                });
                Route::prefix('/attendance')->group(function () {
                    Route::get('/daily-attendance', [AttendanceController::class, 'index'])->name('attendance.daily-attendance');
                    Route::get('/daily-attendance/{id}', [AttendanceController::class, 'show'])->name('attendance.daily-attendance.show');
                    Route::get('/monthly-attendance', [MonthlyAttendanceController::class, 'index'])->name('attendance.month-attendance');
                    Route::post('/monthly-attendance-update', [MonthlyAttendanceController::class, 'update'])->name('attendance.month-attendance-update');
                    Route::get('/summary-attendance', [SummaryAttendanceController::class, 'index'])->name('attendance.summary-attendance');
                    Route::get('/byattendance/{id?}/{year_month?}', [SummaryAttendanceController::class, 'byAttendance'])->name('update.attendence');
                    Route::post('/byattendance-update', [SummaryAttendanceController::class, 'byAttendanceUpdate'])->name('edit.attendence');
                    // Route::post('/history/delete', [SummaryAttendanceController::class, 'deleteAttendanceHistory'])->name('attendance.history.delete');
                    Route::post('/daily-attendance-import', [AttendanceController::class, 'import'])->name('daily-attendance.import');
                    Route::post('/bulk-attendance', [AttendanceController::class, 'bulkAttendance'])->name('bulk.attendance');
                    Route::get('/face-attendance', [AttendanceController::class, 'faceAttendance'])->name('attendance.face.ttendance');
                    Route::post('/upload-visitor-face', [FaceController::class, 'uploadVisitorImageAndAuthenticate'])->name('upload.visitor.face');
                });
                // Route::prefix('/leave')->group(function () {
                //     Route::resource('/employee-leave-calendar', EmpLeaveCalendar::class);
                //     Route::post('/check-sandwich', [EmpLeaveCalendar::class, 'checkSandwich'])->name('checkSandwichLeave');
                // });
                Route::prefix('/report')->group(function () {
                    Route::get('/monthly-summary-attendance-export-report', [ReportAttendanceController::class, 'monthlySummaryAttendanceExport'])->name('monthly.summary.attendance.report.export');
                    Route::post('/monthly-summary-attendance-report', [ReportAttendanceController::class, 'monthlySummaryReport'])->name('monthly.summary.report.details');
                    //  Leave Reports
                    Route::get('/leave-balance-report-export', [ReportLeaveController::class, 'leaveBalance'])->name('leave.balance.report.export');
                    Route::post('/leave-balance-report', [ReportLeaveController::class, 'employeeLeaveBalanceReport'])->name('leave.balance.report.details');
                    // Attendence Reports
                    Route::get('/attendance-report', [ReportAttendanceController::class, 'index'])->name('attendance.report');
                    Route::get('/detailed-attendance-export-report', [ReportAttendanceController::class, 'detailedattendanceExport'])->name('detailed.attendance.report.export');
                    Route::post('/detailed-muster-roll-report', [ReportAttendanceController::class, 'detailedmusterRollReport'])->name('musterroll.report.details');
                    Route::post('/employee-report-export', [ReportAttendanceController::class, 'employeeReportExport'])->name('employee.report.export');
                    Route::get('/attendance-export-report', [ReportAttendanceController::class, 'attendanceExport'])->name('attendance.report.export');
                    Route::post('/muster-roll-report', [ReportAttendanceController::class, 'musterRollReport'])->name('musterroll.report');
                    Route::get('/selfie-attendance-export-report', [ReportAttendanceController::class, 'selfieattendanceExport'])->name('selfie.attendance.report.export');
                    Route::post('/selfie-attendance-report', [ReportAttendanceController::class, 'selfiePunchReport'])->name('selfie.report.details');
                    Route::get('/travel', [ReportController::class, 'index'])->name('travel.report.index');
                    Route::post('/travel-report-export', [ReportController::class, 'export'])->name('travel.report.export');
                    Route::post('/bank-sheet-report-export', [ReportPayrollController::class, 'bankSheetReportExport'])->name('bank.sheet.report.export');
                });
                Route::prefix('/requests')->group(function () {
                    Route::get('/leave', [LeaveController::class, 'index'])->name('requests.leave');
                    Route::get('/leave/{id}', [LeaveController::class, 'show'])->name('leave.requests.show');
                    Route::post('/leave/bulk-approval', [CommonApprovalController::class, 'approveBulk'])->name('admin.bulk-approval');
                    Route::post('/leave-request-import', [LeaveController::class, 'leaveRequestImport'])->name('leave-request.import');
                    Route::get('/leave-request-excel-download', [LeaveController::class, 'downloadLeaveRequestSampleExcel'])->name('leave-request.downloadLeaveRequestSampleExcel');
                    Route::post('/leave-delete', [LeaveController::class, 'destroy']);
                    Route::get('/comp-off-balance', [CompOffBalanceController::class, 'index'])->name('compoff.balance.index');
                    // Route::get('/leave-balance', [LeaveBalanceController::class, 'index'])->name('requests.gate-pass');
                    Route::get('/leave-management', [LeaveManagementController::class, 'index'])->name('leave.leave-management.index');
                    Route::post('/leave-apply', [LeaveManagementController::class, 'store'])->name('leave.leave-apply');
                    Route::get('/leave-details/{id}', [LeaveManagementController::class, 'show'])->name('leave.details.show');
                    Route::get('/leave-balance', [LeaveBalanceController::class, 'index'])->name('leave-balance.index');
                    Route::post('/get-employee-leave-options', [LeaveManagementController::class, 'getEmployeeLeaveOptions'])->name('leave.get-employee-leave-options');
                    Route::get('/gate-pass', [GatePassController::class, 'index'])->name('requests.gate-pass');
                    Route::get('/gate-pass/{id}', [GatePassController::class, 'show'])->name('requests.gate-pass.show');
                    Route::get('/loan-requests', [LoanRequestController::class, 'index'])->name('requests.loan-requests');
                    Route::get('/opening-balance', [OpeningBalanceController::class, 'index'])->name('opening-balance.index');
                    Route::post('/add-opening-balance', [OpeningBalanceController::class, 'add'])->name('opening-balance.add');
                    Route::post('/opening-balance-import', [OpeningBalanceController::class, 'import'])->name('opening.balance.import');
                    Route::get('/opening-balance-excel-download', [OpeningBalanceController::class, 'downloadSampleExcel'])->name('opening.balance.downloadExcel');
                    Route::post('/opening-balance/delete/{empId}', [OpeningBalanceController::class, 'deleteEmpOpenBalance'])->name('opening.balance.delete');
                    // Mis-Punch Requests (Resource Routes)
                    Route::resource('/mis-punch', MispunchController::class);
                });
                Route::any('/get-state-city-country', [SettingController::class, 'getCountryStateCityAjax'])->name('getCityStateCountry');
                Route::prefix('/settings')->group(function () {
                    Route::prefix('/account')->group(function () {
                        Route::get('/', [SettingController::class, 'account'])->name('account.settings');
                        Route::post('/update-account', [SettingController::class, 'updateAccount'])->name('account.update');
                    });
                    Route::prefix('/business')->group(function () {
                        Route::get('/', [SettingController::class, 'business']);
                        Route::get('/branches', [SettingController::class, 'branches'])->name('admin.branch');
                        Route::get('/department', [SettingController::class, 'department'])->name('admin.department');
                        Route::get('/dealership', [SettingController::class, 'dealership'])->name('admin.dealership');
                        // Route::get('/dealership/upload', [SettingController::class, 'dealershipSampleExport'])->name('business.dealership.downloadExcel');
                        Route::any('/designation', [SettingController::class, 'designation'])->name('admin.designation');
                        Route::any('/grade', [SettingController::class, 'grade'])->name('admin.grade');
                        Route::any('/role', [SettingController::class, 'role'])->name('admin.role');
                        Route::get('/branches/upload', [SettingController::class, 'branchesSampleExport'])->name('business.branch.downloadExcel');
                        Route::get('/grades/upload', [SettingController::class, 'gradeSampleExport'])->name('business.grade.downloadExcel');
                        Route::get('/roles/upload', [SettingController::class, 'roleSampleExport'])->name('business.role.downloadExcel');
                        Route::get('/designation/upload', [SettingController::class, 'designationSampleExport'])->name('business.designation.downloadExcel');
                        Route::get('/dealership/upload', [SettingController::class, 'dealershipSampleExport'])->name('business.dealership.downloadExcel');
                        Route::get('/department/upload', [SettingController::class, 'departmentSampleExport'])->name('business.department.downloadExcel');
                        // Route::get('/branches/upload', [SettingController::class, 'branchesSampleExport'])->name('business.branch.downloadExcel');
                        // Route::get('/grades/upload', [SettingController::class, 'gradeSampleExport'])->name('business.grade.downloadExcel');
                        // Route::get('/roles/upload', [SettingController::class, 'roleSampleExport'])->name('business.role.downloadExcel');
                        // Route::get('/designation/upload', [SettingController::class, 'designationSampleExport'])->name('business.designation.downloadExcel');
                        // Route::get('/department/upload', [SettingController::class, 'departmentSampleExport'])->name('business.department.downloadExcel');
                        Route::prefix('/add')->group(function () {
                            Route::post('/branch', [SettingController::class, 'addBranch'])->name('add.branch');
                            Route::post('/branches/import', [SettingController::class, 'importBranches'])->name('business.branch.import');
                            Route::post('/department', [SettingController::class, 'addDepartment'])->name('add.department');
                            Route::post('/dealership', [SettingController::class, 'addDealership'])->name('add.dealership');
                            // Route::post('/dealership/import', [SettingController::class, 'importDealership'])->name('business.dealership.import');
                            // Route::post('/branches/import', [SettingController::class, 'importBranches'])->name('business.branch.import');
                            Route::post('/designation', [SettingController::class, 'addDesignation'])->name('add.designation');
                            Route::post('/grade', [SettingController::class, 'addGrade'])->name('add.grade');
                            Route::post('/grade/import', [SettingController::class, 'importGrade'])->name('business.grade.import');
                            Route::post('/role', [SettingController::class, 'addRole'])->name('add.role');
                            Route::post('/default-dashboard', [SettingController::class, 'addDashboard'])->name('add.default-dashboard');
                            Route::post('/department/import', [SettingController::class, 'importDepartment'])->name('business.department.import');
                            Route::post('/grade/import', [SettingController::class, 'importGrade'])->name('business.grade.import');
                            Route::post('/role/import', [SettingController::class, 'importRole'])->name('business.role.import');
                            Route::post('/designation/import', [SettingController::class, 'importDesignation'])->name('business.designation.import');
                            Route::post('/dealership/import', [SettingController::class, 'importDealership'])->name('business.dealership.import');
                            // Route::post('/role/import', [SettingController::class, 'importRole'])->name('business.role.import');
                            // Route::post('/default-dashboard', [SettingController::class, 'addDashboard'])->name('add.default-dashboard');
                            // Route::post('/designation/import', [SettingController::class, 'importDesignation'])->name('business.designation.import');
                            // Route::post('/department/import', [SettingController::class, 'importDepartment'])->name('business.department.import');
                        });
                        Route::prefix('/update')->group(function () {
                            Route::post('/branch', [SettingController::class, 'updateBranch'])->name('update.branch');
                            Route::post('/department', [SettingController::class, 'updateDepartment'])->name('update.department');
                            Route::post('/dealership', [SettingController::class, 'updateDealership'])->name('update.dealership');
                            Route::post('/designation', [SettingController::class, 'updateDesignation'])->name('update.designation');
                            Route::post('/grade', [SettingController::class, 'updateGrade'])->name('update.grade');
                            Route::post('/role', [SettingController::class, 'updateRole'])->name('update.role');
                        });
                        Route::prefix('/delete')->group(function () {
                            Route::post('/branch', [SettingController::class, 'deleteBranch'])->name('delete.branch');
                            Route::delete('/department/{id}', [SettingController::class, 'deleteDepartment'])->name('delete.department');
                            Route::delete('/dealership/{id}', [SettingController::class, 'deleteDealership'])->name('delete.dealership');
                            Route::post('/designation', [SettingController::class, 'deleteDesignation'])->name('delete.designation');
                            Route::post('/grade', [SettingController::class, 'deleteGrade'])->name('delete.grade');
                            Route::post('/role', [SettingController::class, 'deleteRole'])->name('delete.role');
                        });
                    });
                    Route::prefix('/tada-settings')->group(function () {
                        Route::get('/', [TadaController::class, 'index']);
                        Route::post('/create-update-travel-type', [TadaController::class, 'createOrUpdateTravelType'])->name('creatOrUpdate.travel.type');
                        Route::any('/get-travel-purpose', [TadaController::class, 'travelPurpose'])->name('admin.travelpurpose');
                        Route::post('/add-travel-purpose', [TadaController::class, 'addTravelPurpose'])->name('add.travelpurpose');
                        Route::post('/update-travel-purpose', [TadaController::class, 'updateTravelPurpose'])->name('update.travelpurpose');
                        Route::delete('/delete-travel-purpose/{id}', [TadaController::class, 'deleteTravelPurpose'])->name('delete.travelpurpose');
                        Route::get('/travel-types', [TadaController::class, 'travelList'])->name('travel.type.list');
                        Route::post('/delete-travel-type', [TadaController::class, 'deleteTravelType'])->name('delete.travel.type');
                        Route::get('/policy-category', [TadaController::class, 'travelPolicyCategory'])->name('admin.travel.policy.category');
                        Route::post('/create-update-policy-category', [TadaController::class, 'createUpdateTravelPolicyCategory'])->name('admin.create.update.policy.category');
                        Route::get('/travel-modes', [TadaController::class, 'travelMode'])->name('admin.travel.modes');
                        Route::post('/save-travel-modes', [TadaController::class, 'saveTravelModes'])->name('admin.save.travel.modes');
                        // Route::get('/travel-allowance', [TadaController::class, 'travelAllowance'])->name('travel-allowance.index');
                        Route::resource('/travel-allowance', TravelAllowanceController::class);
                        Route::post('/create-update-travel-allowance', [TadaController::class, 'createOrUpdateTravelAllowance'])->name('creatOrUpdate.travel.allowance');
                        Route::get('/get_travel_modes/{id}', [TadaController::class, 'getTravelModes'])->name('getTravelModes');
                        Route::get('/get-travel-lists', [TadaController::class, 'getTravelLists'])->name('get.travelLists');
                        Route::get('/travel-vehicle', [TadaController::class, 'travelVehicle'])->name('admin.travel.vehicle');
                        Route::post('/get-travel-vehicle', [TadaController::class, 'getTravelVehicleSetting'])->name('admin.get.travel.vehicle');
                        Route::post('/delete-travel-vehicle', [TadaController::class, 'deleteTravelVehicleSetting'])->name('admin.delete.travel.vehicle');
                        Route::post('/save-travel-vehicle', [TadaController::class, 'saveTravelVehicleSetting'])->name('admin.save.travel.vehicle');
                        // Route::get('/lodging', [TadaController::class, 'Lodging'])->name('admin.travel.lodging');
                        // Route::post('/create-update-lodging', [TadaController::class, 'createUpdateLodging'])->name('admin.create.update.lodging');
                        // Route::post('/delete-daily-allowance-lodging', [TadaController::class, 'deleteDALodging'])->name('admin.delete.da.lodging');
                        Route::prefix('/lodging')->group(function () {
                            Route::get('/', [TadaController::class, 'lodging'])->name('admin.travel.lodging');
                            Route::post('/create-update', [TadaController::class, 'createUpdateLodging'])->name('create.update.lodging');
                            Route::post('/delete', [TadaController::class, 'deleteLodging'])->name('delete.lodging');
                        });
                        Route::prefix('/daily-allowance')->group(function () {
                            Route::get('/', [TadaController::class, 'dailyAllowance'])->name('admin.travel.daily-allowance');
                            Route::post('/create-update', [TadaController::class, 'createUpdateDailyAllowance'])->name('create.update.daily-allowance');
                            Route::post('/delete', [TadaController::class, 'deleteDailyAllowance'])->name('delete.daily-allowance');
                        });
                        Route::prefix('/cities')->group(function () {
                            Route::get('/', [TadaController::class, 'citiesTravel'])->name('travel.cities.list');
                            Route::post('/add', [TadaController::class, 'addCity'])->name('add.city');
                            Route::post('/delete', [TadaController::class, 'deleteCity'])->name('delete.city');
                        });
                        Route::prefix('/expense-setting')->group(function () {
                            Route::get('/', [TadaController::class, 'expenseDetails'])->name('get.expense-setting');
                            Route::post('/create-update', [TadaController::class, 'storeUpdateExpenseDetails'])->name('store.update.expense-setting');
                            Route::post('/delete/{id}', [TadaController::class, 'deleteExpenseDetails'])->name('delete.expense-setting');
                        });
                        // For Approval Settings
                        Route::get('/approval-setting/{id?}', [ApprovalSettingsController::class, 'travelApprovalSetting'])->name('admin.travel.approval.setting');
                        Route::post('/save-approval-setting', [ApprovalSettingsController::class, 'saveAllMappings'])->name('admin.save.approval.setting');
                        Route::get('/get-approval-setting', [ApprovalSettingsController::class, 'getApprovalSetting'])->name('admin.get.approval.setting');
                        Route::get('/approval-list', [ApprovalSettingsController::class, 'index'])->name('travel.approval.list');
                        Route::get('/approval-mapping/sample-download', [ApprovalSettingsController::class, 'approvalMappingSampleDownload'])->name('approval.mapping.sample');
                        Route::post('/approval-list-toggle-box', [ApprovalSettingsController::class, 'approvalUpdateToggleBox'])->name('approval-update-toggle-box');
                        Route::get('/approval-process-details/{id}', [ApprovalSettingsController::class, 'approvalProcessDetails'])->name('approval.process.details');
                        Route::post('/approval-mapping/import', [ApprovalSettingsController::class, 'approvalMappingImport'])->name('approval.mapping.import');
                    });
                    // Attendance Let's Begin
                    Route::prefix('/attendance')->group(function () {
                        Route::get('/', [SettingController::class, 'attendance'])->name('attendance');
                        Route::resource('/attendance-policies', AttendancePolicyController::class);
                        Route::resource('/shift-policy', ShiftPolicyController::class);
                        Route::resource('/attendance-shift-type', AttendanceShiftTypeController::class);
                        //Route::post('/fast-recalculate', [AttendanceShiftController::class, 'fastRecalculate'])->name('attendance.recalculate');
                        Route::get('/holiday-policy', [PolicyHolidayController::class, 'index'])->name('get.policy-holiday');
                        Route::post('/store-holiday-policy', [PolicyHolidayController::class, 'store'])->name('store.policy-holiday');
                        Route::post('/update-holiday-policy', [PolicyHolidayController::class, 'update'])->name('update.policy-holiday');
                        Route::delete('/delete-holiday-policy', [PolicyHolidayController::class, 'destroy'])->name('delete.policy-holiday');
                        Route::resource('/leave-policy', LeavePolicyController::class);
                        //Route::get('/leave-policy/edit/{id}', [LeavePolicyController::class, 'edit'])->name('leave-policy.edit');
                        Route::resource('/weekly-policy', WeeklyPolicyController::class);
                        Route::resource('/automation-rules', AutomationRulesController::class);
                        Route::post('/late-early-automation-rule', [AutomationRulesController::class, 'lateEarlyRuleSave'])->name('late.early.automation.rule.save');
                        Route::resource('/compoff-policy', CompOffPolicyController::class);
                        //Route::resource('/overtime-policy', OvertimePolicyController::class);
                    });
                    // Attendance Let's Begin
                    Route::prefix('/payroll')->group(function () {
                        Route::get('/', action: [EmployeePayrollController::class, 'payroll'])->name('payroll');
                        Route::resource('/payroll-policies', EmployeePayrollController::class);
                        Route::resource('/financial-year', FinancialYearController::class);
                        Route::resource('/loan-configuration', LoanPolicyController::class);
                        Route::resource('/adhoc-components', AdhocComponentController::class);
                        Route::resource('/payslip-configuration', PayslipConfigController::class);
                        Route::resource('/payroll-business-setting', BsnsPaySettingController::class);
                        Route::post('/save-payroll-business-setting', [BsnsPaySettingController::class, 'storeOrUpdate'])->name('payroll.setting.storeOrUpdate');
                        Route::post('send-otp', [BsnsPaySettingController::class, 'sendOtp'])->name('payroll.setting.sendOtp');
                        Route::post('verify-otp', [BsnsPaySettingController::class, 'verifyOtp'])->name('payroll.setting.verifyOtp');
                    });
                    // Business Policy
                    Route::prefix('/regulatory')->group(function () {
                        Route::get('/folder', [PolicyController::class, 'folder_index'])->name('regulatory.folder.index');
                        Route::post('/documents-folder-store', [PolicyController::class, 'folder_store'])->name('folder.store');
                        Route::delete('/folder/{id}', [PolicyController::class, 'folder_destroy'])->name('folder.destroy');
                        Route::get('/document/{id}', [PolicyController::class, 'index'])->name('documents.index');
                        Route::post('/folder/store', [PolicyController::class, 'store'])->name('documents.store');
                        Route::delete('/document/{id}', [PolicyController::class, 'destroy'])->name('documents.destroy');
                        Route::post('/bulk-download', [PolicyController::class, 'bulkDownload'])->name('documents.bulk_download');
                        Route::get('/document/download/{id}', [PolicyController::class, 'downloadPDF'])->name('document.download');
                    });

                });
                Route::prefix('approve')->group(function () {
                    Route::post('/gate-pass', [GatePassController::class, 'approve'])->name('approve.gate-pass');
                    Route::post('/mis-punch', [MispunchController::class, 'approve'])->name('approve.mis-punch');
                    Route::post('/leave', [LeaveController::class, 'approve'])->name('approve.leave');
                    Route::post('/travel', [TravelRequestController::class, 'approve'])->name('approve.travel');
                    Route::post('/advance', [AdvanceLogController::class, 'approve'])->name('approve.advance');
                    Route::post('/claim', [ClaimRequestController::class, 'approve'])->name('approve.claim');
                    Route::post('/attendance', [AttendanceController::class, 'approve'])->name('approve.attendance');
                    Route::post('/loan-advance', [LoanRequestController::class, 'approve'])->name('approve.loan-request');
                    Route::get('/overtime', [OvertimePolicyController::class, 'overtime_index'])->name('approve.overtime');
                });
                // Asset routes
                Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
                Route::get('assets/stock', [AssetController::class, 'stock'])->name('assets.stock');
                Route::get('assets/assigned', [AssetController::class, 'assigned'])->name('assets.assigned');
                Route::get('assets/scrap', [AssetController::class, 'scrap'])->name('assets.scrap-list');
                Route::get('assets/replaced', [AssetController::class, 'replaced'])->name('assets.replaced');
                Route::get('assets/service', [AssetController::class, 'service'])->name('assets.service');
            });
            Route::prefix('/privilege')->group(function () {
                Route::resource('email-templates', MailTemplateController::class);
            });
            Route::prefix('/recruitment')->group(function () {
                Route::resource('/pipeline', RecruitmentPipelineController::class);
                Route::get('/fetch-candidate-data', [RecruitmentPipelineController::class, 'getCandidateData'])->name('fetch.candidate.data');
                Route::resource('/skills', RecruitmentSkillController::class);
                Route::resource('/recruitments', RecruitmentController::class);
                Route::resource('/stages', RecruitmentStageController::class);
                Route::resource('/candidates', RecruitmentCandidateController::class);
                Route::resource('/schedule-interview', RecruitmentScheduleInterviewController::class);
            });
            Route::prefix('/payroll')->group(function () {
                Route::get('/dashboard', [PayrollController::class, 'index'])->name('salary.dashboard');;
                Route::get('/components', [PayrollController::class, 'payrollComponentList'])->name('payroll.component.list');
                Route::post('/component/create', [PayrollController::class, 'createPayrollComponent'])->name('payroll.component.create');
                Route::delete('/component/delete/{id}', [PayrollController::class, 'deletePayrollComponent'])->name('payroll.component.delete');
                Route::get('/payroll-period', [PayrollPeriodController::class, 'payrollPeriodList'])->name('payroll.period.list');
              //weeklyroutes
        Route::get('/payroll-cycles-weekly', [WeeklyPayrunController::class, 'weeklyCyclesIndex'])->name('payroll.cycles.weekly');
        Route::get('/store-payroll-cycles-weekly', [WeeklyPayrunController::class, 'storeWeeklyPayrollCycles'])->name('store.payroll.cycles.weekly');
        Route::post('/weekly-payroll-store', [WeeklyPayrunController::class, 'storeWeeklyPayrollCycles'])->name('payroll.weekly.store');

        Route::get('/weekly-cycle/{payrollId}/edit-data', [WeeklyPayrunController::class, 'getWeeklyCycleEditData'])
            ->name('payroll.weekly.cycle.edit-data');
        Route::post('/weekly-cycle/{payrollId}/update', [WeeklyPayrunController::class, 'updateWeeklyCycleWeeks'])
            ->name('payroll.weekly.cycle.update');

        Route::get('/get-weeks-for-month', [WeeklyPayrunController::class, 'getWeeksForMonth'])->name('payroll.weeks.for-month');
        Route::get('/weekly-process/{periodId}/{weekId}', [WeeklyPayrunController::class, 'processWeeklyPayroll'])
            ->name('payroll.weekly.process');
        Route::get('/weekly-attendance', [WeeklyPayrunController::class, 'payrunRetrieveAttendance'])->name('payroll.weekly.attendance');
        Route::post('/weekly-freeze-attendance-chunk', [WeeklyPayrunController::class, 'freezeWeeklyAttendanceChunk'])
             ->name('payroll.weekly.freeze.chunk');
        Route::post('/weekly-check-processed-status', [WeeklyPayrunController::class, 'checkWeeklyProcessedStatus'])->name('payroll.weekly.check-processed-status');
        Route::post('/weekly-process-salaries', [WeeklyPayrunController::class, 'processWeeklySalaries'])
            ->name('payroll.weekly.process-salaries');
        Route::get('/weekly-salary-data/{periodId}/{weekId}', [WeeklyPayrunController::class, 'getWeeklySalaryData'])
            ->name('payroll.weekly.salary-data');
        Route::get('/weekly-employee-salary-preview', [WeeklyPayrunController::class, 'getWeeklyEmployeeSalaryPreview'])
            ->name('payroll.weekly.employee-salary-preview');
        Route::post('/weekly-save-draft', [WeeklyPayrunController::class, 'saveWeeklyDraft'])
            ->name('payroll.weekly.save-draft');
        Route::post('/weekly-finalize', [WeeklyPayrunController::class, 'finalizeWeeklyPayroll'])
            ->name('payroll.weekly.finalize');
        Route::post('/weekly-release-hold', [WeeklyPayrunController::class, 'releaseWeeklyHold'])
            ->name('payroll.weekly.release-hold');
        Route::get('/weekly-get-hold-details/{periodId}/{weekId}', [WeeklyPayrunController::class, 'getWeeklyHoldDetails'])
            ->name('payroll.weekly.hold-details');
        // Route::get('/check-pending-requests', [WeeklyPayrunController::class, 'checkWeeklyPendingRequests'])->name('payroll.weekly.check-pending');
        Route::get('/payroll-cycles', [PayRunController::class, 'cyclesIndex'])->name('payroll.cycles');
        Route::get('/weekly-check-pending-requests', [WeeklyPayrunController::class, 'checkWeeklyPendingRequests'])->name('payroll.weekly.check-pending');
        Route::post('/payroll-weekly/retrieve-attendance', [WeeklyPayrunController::class, 'payrunRetrieveAttendance'])->name('payroll.weekly.retrieve-attendance');
        Route::post('/payroll-weekly/attendance/unfreeze/{payrollId}', [WeeklyPayrunController::class, 'unfreezeWeeklyAttendance'])
            ->name('payroll.weekly.attendance.unfreeze');
         // Weekly Payroll Routes
        Route::post('/weekly/back-to-processing', [WeeklyPayrunController::class, 'backToProcessing'])->name('payroll.weekly.back-to-processing');
        Route::post('/weekly/update-status', [WeeklyPayrunController::class, 'updateWeekStatus'])->name('payroll.weekly.update-status');
        Route::post('/weekly-revert-salaries', [WeeklyPayrunController::class, 'revertWeeklySalaries'])
            ->name('payroll.weekly.revert-salaries');
        Route::post('/weekly-update-processed-flag', [WeeklyPayrunController::class, 'updateProcessedFlag'])
          ->name('payroll.weekly.update-processed-flag');
        Route::get('/weekly-payslips-data/{payrollId}/{weekId}', [WeeklyPayrunController::class, 'getWeeklyPayslipsData'])
          ->name('payroll.weekly.payslips-data');
        Route::get('/weekly-payslip-list/{payrollId}/{weekId}', [WeeklyPayrunController::class, 'viewWeeklyPayslips'])
          ->name('payroll.weekly.payslip.list');
        Route::get('/weekly-employee-payslip/{employeeId}', [WeeklyPayrunController::class, 'getWeeklyEmployeePayslip'])
          ->name('payroll.weekly.employee.payslip');
        Route::get('/weekly-download-payslip/{id}', [WeeklyPayrunController::class, 'downloadWeeklyPayslip'])
          ->name('payroll.weekly.downloadPayslip');
        Route::post('/weekly-change-status/{weekId}', [WeeklyPayrunController::class, 'changeWeeklyStatus'])
          ->name('payroll.weekly.change-status');
        // Weekly Payroll Routes
        Route::post('/weekly-revert-all-salaries', [WeeklyPayrunController::class, 'revertAllWeeklySalaries'])->name('payroll.weekly.revert.all');
        Route::post('/weekly-unprocess-salaries', [WeeklyPayrunController::class, 'unprocessSelectedSalaries'])->name('payroll.weekly.unprocess');

               //Monthly
                Route::get('/payroll-cycles', [PayRunController::class, 'cyclesIndex'])->name('payroll.cycles');
                Route::post('/payroll-new/period/create', [PayRunController::class, 'updateOrCreatePayrollPeriod'])->name('payroll.new.period.create');
                Route::post('/period/create-ajax', [PayRunController::class, 'createPayrollPeriodAjax'])->name('payroll.period.create.ajax');
                Route::get('/payroll-new-process', [PayRunController::class, 'payrollNewProcess'])->name('payroll.new.process');
                Route::get('/payroll-new/attendance/retrieve', [PayRunController::class, 'payrunRetrieveAttendance'])->name('payroll.new.attendance.retrieve');
                Route::post('/payroll-new/attendance/freeze-attendance', [PayRunController::class, 'payrunfreezeAttendance'])->name('payroll.new.freeze.attendance');
                Route::post('/payroll-new/attendance/unfreeze/{id}', [PayRunController::class, 'payrunUnfreezeAttendance'])->name('payroll.attendance.unfreeze');
                Route::get('/payroll-new/attendance/getProcessSalary/{id}', [PayRunController::class, 'getProcessSalaryStep3'])->name('payroll.attendance.getprocess.salary.list');
                Route::get('/payroll-new/process-salary/step3-data', [PayRunController::class, 'getProcessSalaryStep3'])->name('payroll.process-salary.step3');
                Route::post('/payroll-new/process-salaries', [PayRunController::class, 'payrunprocessSalaries'])->name('payroll.process.salaries');
                Route::get('/payroll-new/get-processed-employees', [PayRunController::class, 'getProcessedEmployees'])->name('payroll.get.processed.employees');
                Route::post('/payroll-new/unprocess-selected-salaries', [PayRunController::class, 'payrunUnprocessSalaries'])->name('payroll.unprocess.selected.salaries');
                Route::get('/payroll-new/pending-requests', [PayRunController::class, 'getPendingRequests'])->name('payroll.pending-requests');
                Route::get('/payroll-new/employee-payroll-details', [PayRunController::class, 'empPayrollDetails'])->name('employee.payroll.details');
                Route::get('/payroll-new/payslip-list/{payrollId}', [PayRunController::class, 'viewAllPayslips'])->name('payroll.payslip.list');
                Route::get('/payroll-new/employee-payslip/{employeeId}', [PayRunController::class, 'getEmployeePayslip'])->name('payroll.employee.payslip');
                Route::get('/payroll-new/payslip-batch/{payrollId}', [PayRunController::class, 'payslipBatchView'])
                    ->name('payroll.payslip.batch');
                Route::get('/payroll-new/employee-salary-preview', [PayRunController::class, 'getEmployeeSalaryPreview']);
                Route::post('/payroll-new/process-single-salary', [PayRunController::class, 'processSingleSalary']);
                Route::get('/payroll-new/check-pending-requests', [PayRunController::class, 'checkPendingRequests']);
                Route::get('/payroll-new/download-payslip/{id}', [PayRunController::class, 'payrunDownloadPayslip'])->name('payrun.downloadPayslip');
                    Route::post('/payroll-new/attendance/freeze-attendance-chunk', [PayRunController::class, 'payrunfreezeAttendanceChunk'])->name('payroll.new.freeze.attendance.chunk');

                // Employee payroll details route
                Route::get('/payroll/payroll-new/get-employee-payroll-details', [PayRunController::class, 'getEmployeePayrollDetails'])
                    ->name('payroll.employee-details');
                // Hold salary route
                Route::post('/payroll-new/hold-salary', [PayRunController::class, 'storeWithHoldSalary'])
                    ->name('payroll.new.hold-salary');
                // Release salary route
                Route::post('/payroll-new/release-hold', [PayRunController::class, 'releaseSalary'])
                    ->name('payroll.new.release-hold');
                Route::post('/payroll-new/finalize-payroll', [PayRunController::class, 'finalizePayroll'])->name('payroll.finalize');
                Route::get('/payroll/get-held-periods', [PayRunController::class, 'getHeldPeriods'])
                    ->name('payroll.get-held-periods');
                Route::get('/payroll-new/get-hold-details/{periodId}', [PayRunController::class, 'getHoldDetails'])
                    ->name('payroll.hold-details');
                Route::get('/payroll-new/get-payroll-periods', [PayRunController::class, 'getPayrollPeriods'])
                    ->name('payroll.new.get-payroll-periods');
                Route::post('/payroll-new/finalize-payroll', [PayRunController::class, 'finalizePayroll'])->name('payroll.finalize');
                Route::post('/payroll-period/create', [PayrollPeriodController::class, 'updateOrCreatepayrollPeriod'])->name('payroll.period.create');
                Route::get('/payroll-period/{id}/getPayslipDate', [PayrollPeriodController::class, 'getPayslipDate'])->name('payroll.period.getPayslipDate');
                Route::post('/payroll-period/updatePayslipDate', [PayrollPeriodController::class, 'updatePayslipDate'])->name('payroll.period.updatePayslipDate');
                // Route::get('/attendance/retrieve', [PayrollPeriodController::class, 'retrieveAttendance'])->name('payroll.attendance.retrieve');
                Route::get('/freeze-attendance', [PayrollPeriodController::class, 'attendancelist'])->name('payroll.attendance.list');
                Route::get('/loan/loan-approval', [AdvanceLoanController::class, 'loanApprovalList'])->name('loan.approval.list');
                Route::get('/get-months/{fy_id}', [PayrollPeriodController::class, 'getMonthsForFinancialYear']);
                Route::get('/adhoc-payments-deductions', [AdhocPayDeducController::class, 'index'])->name('adhoc.index');
                Route::get('/adhoc-payments-deductions/edit-adhoc', [AdhocPayDeducController::class, 'edit'])->name('adhoc.edit');
                Route::post('/adhoc-payments-deductions/store-adhoc', [AdhocPayDeducController::class, 'storeAdhoc'])->name('adhoc.store');
                Route::get('/adhoc/form-view', [AdhocPayDeducController::class, 'formView'])->name('adhoc.form.view');
                Route::get('/recurring-payments-deductions', [RecurringPayDeducController::class, 'index'])->name('recurring.index');
                Route::get('/recurring/form-view', [RecurringPayDeducController::class, 'recurringformView'])->name('recurring.form.view');
                Route::post('/recurring/store-recurring-transaction', [RecurringPayDeducController::class, 'storeRecurringTransaction'])->name('store.recurring.transaction');
                Route::get('/recurring/edit-recurring-transaction', [RecurringPayDeducController::class, 'editRecurringTransaction'])->name('recurring.edit');
                Route::post('/setting/update-phone', [BsnsPaySettingController::class, 'updatePhone'])
                    ->name('payroll.setting.updatePhone');
                Route::get('/employees/search-by-name', [AdhocPayDeducController::class, 'searchByName'])->name('employees.searchByName');
                Route::get('/employees/search-by-code', [AdhocPayDeducController::class, 'searchByCode'])->name('employees.searchByCode');
                Route::get('/employees/search-by-all', [AdhocPayDeducController::class, 'searchByall'])->name('employees.searchByall');
                Route::prefix('holdSalary')->controller(PayrollHoldSalaryController::class)->group(function () {
                    Route::get('/', 'payrollHoldSalaryList')->name('payroll.holdSalary.list');
                    Route::post('/', 'storeWithHoldSalary')->name('withheld.salary.store');
                    Route::post('/edit', 'editWithHoldSalary')->name('withheld.salary.edit');
                    Route::post('/update', 'updateWithHoldSalary')->name('withheld.salary.update');
                    Route::post('/release/{id}', 'releaseWithHoldSalary')->name('withheld.salary.release');
                    Route::post('/revert/{id}', 'revertWithHoldSalary')->name('withheld.salary.revert');
                    Route::delete('/delete/{id}', 'deleteWithHoldSalary')->name('withheld.salary.delete');
                    Route::post('release-held-salaries', 'releaseSelectedHeldSalaries')->name('release-held-salaries');
                });
                Route::prefix('deductions')->controller(PayrollController::class)->group(function () {
                    Route::get('/', 'payrollDeductionList');
                    Route::post('/create', 'createOrUpdatePayrollDeduction')->name('payroll.deduction.create.update');
                    Route::delete('/delete/{id}', 'destroy')->name('payroll.deduction.destroy');
                    Route::post('/update', 'updatePayrollDeduction');
                    Route::post('/delete', 'deletePayrollDeduction');
                });
                Route::get('/templates', [PayrollController::class, 'payrollTemplateList']);
                Route::get('/template/add-edit', [PayrollController::class, 'addEditTemplate'])->name('payroll.new_template');
                // Route::post('/template/storePayrollTemplate', [PayrollController::class, 'storePayrollTemplate'])->name('salary-template.store');
                Route::get('/salary-master', [PayrollController::class, 'salaryMaster'])->name('salary.master');
                Route::get('/salary/generate', [PayrollController::class, 'generateSalary'])->name('salary.generate');
                Route::post('/template/create', [PayrollController::class, 'createPayrollTemplate']);
                Route::post('/template/update', [PayrollController::class, 'updatePayrollTemplate']);
                Route::delete('/template/delete', [PayrollController::class, 'deletePayrollTemplate']);
                Route::get('/template/get-components/{type}', [PayrollController::class, 'getComponentForTemplate']);
                Route::get('/payslips', [PayrollController::class, 'payrollPayslip'])->name('payroll.payslips');
                Route::get('/get-payroll-periods', [PayrollController::class, 'getPayrollPeriods'])->name('payroll.getPayrollPeriods');
                Route::post('/payslip/create', [PayrollController::class, 'createPayrollPayslip']);
                Route::post('/payslip/update', [PayrollController::class, 'updatePayrollPayslip']);
                Route::delete('/payslip/delete', [PayrollController::class, 'deletePayrollPayslip']);
                Route::get('/loans', [PayrollController::class, 'loan'])->name('loans.index');
                Route::post('/loan-advance/create', [PayrollController::class, 'createOrUpdateLoan']);
                Route::post('/loan-advance/update', [PayrollController::class, 'createOrUpdateLoan']);
                Route::post('/loan-advance/getEmployeeLoanBalance', [PayrollController::class, 'getEmployeeLoanBalance'])->name('loan.getEmployeeLoanBalance');
                Route::post('/loan-advance/save-loan-advance', [PayrollController::class, 'saveLoanAdvance'])->name('loan.saveLoanAdvance');
                Route::delete('/loans/delete/{id}', [PayrollController::class, 'deleteLoan'])->name('loan-delete');
                Route::get('/pay-runs', [PayrollController::class, 'payrollPayRun']);
            });


                Route::prefix('/taxation')->group(function () {
                Route::get('/tax-config-index', [TaxConfigController::class, 'index'])->name('tax.config.index');
                Route::get('/slabs', [TaxConfigController::class, 'getSlabs'])->name('tax.slabs.get');
                Route::get('/financial-years', [TaxConfigController::class, 'getFinancialYears'])->name('tax.financial-years');
                Route::post('/slabs', [TaxConfigController::class, 'store'])->name('tax.slabs.store');
                Route::put('/slabs/{id}', [TaxConfigController::class, 'update'])->name('tax.slabs.update');
                Route::delete('/slabs/{id}', [TaxConfigController::class, 'destroy'])->name('tax.slabs.destroy');
                Route::post('/seed-defaults', [TaxConfigController::class, 'seedDefaults'])->name('tax.seed-defaults');

                 Route::get('/form-generation', [FormGenerationController::class, 'index'])->name('tax.form-generation.index');

                Route::get('/monthly-ledger-index', [LedgerController::class, 'index'])->name('monthly.ledger.index');
                Route::get('/monthly-ledger-data', [LedgerController::class, 'getEmployeeLedgerData'])->name('monthly.ledger.data');
                Route::get('/challans', [ChallanController::class, 'index'])->name('challans.index');
                        Route::get('/challan-quarter-salary-availability', [ChallanController::class, 'quarterSalaryAvailability'])->name('challans.quarter-salary-availability');

                Route::post('/challans', [ChallanController::class, 'upsert'])->name('challans.upsert');
                Route::get('/form-16a-availability', [LedgerController::class, 'form16AAvailability'])->name('form16a.availability');
                Route::post('/generate-form-16a', [LedgerController::class, 'generateForm16A'])->name('generate.form16a');
                Route::post('/generate-form-16', [LedgerController::class, 'generateForm16'])->name('generate.form16');
                Route::post('/generate-form-15g', [LedgerController::class, 'generateForm15G'])->name('generate.form15g');
            });

        });

        Route::prefix('admin/employee-exit')->group(function () {
            Route::get('index', [EmployeeExitController::class, 'index'])->name('admin.employee-exit.index');
            Route::get('/view/{id}', [EmployeeExitController::class, 'view'])->name('exit.view');

            Route::post('/exit/{id}/manager/approve', [EmployeeExitController::class, 'managerApprove'])->name('exit.manager.approve');
            Route::post('/exit/{id}/manager/reject', [EmployeeExitController::class, 'managerReject'])->name('exit.manager.reject');

            Route::post('/exit/{id}/hr/approve', [EmployeeExitController::class, 'hrApprove'])->name('exit.hr.approve');
            Route::post('/exit/{id}/hr/reject', [EmployeeExitController::class, 'hrReject'])->name('exit.hr.reject');

            Route::post('/exit/{id}/finance/clearance', [EmployeeExitController::class, 'financeClearance'])->name('exit.finance.clearance');

            Route::post('/exit/{id}/documentation', [EmployeeExitController::class, 'storeDocuments'])->name('exit.documentation.store');

            Route::post('/exit/{id}/relieve', [EmployeeExitController::class, 'relieveEmployee'])->name('exit.relieve');
        });
    });
    //Route::post('/employee/search', [PayrollController::class, 'searchEmployee'])->name('employee.search');
    Route::post('/employee/search', [AdhocPayDeducController::class, 'searchByAll'])->name('employee.search');
    Route::get('/payroll-periods/by-fy', [ReportPayrollController::class, 'getPayrollPeriodsByFY'])->name('payrollPeriods.byFY');
    Route::prefix('/super-admin')->group(function () {
        Route::resource('organizations', OrganizationController::class)->middleware('auth');
        Route::get('/sub-menu-get/{id}', [OrganizationController::class, 'getSubModule'])->name('organizations.submenu');
        Route::post('/store-menu', [OrganizationController::class, 'saveSubModule'])->name('organizations.store');
        Route::get('/organizations/{id}/employees', [OrganizationController::class, 'organizationEmployeeDetails'])->name('organizations.employees');
        Route::post('/organizations/update-password', [OrganizationController::class, 'updateEmployeePassword'])->name('update.employee.password');
        Route::get('/organizations/{id}/copy-settings', [OrganizationController::class, 'copySettings'])->name('organizations.copy-settings');
        Route::post('/organizations/copy-settings-store', [OrganizationController::class, 'copySettingsProcess'])->name('organizations.copy-settings-store');
    });
    Route::post('/employee-import', [EmployeeController::class, 'employeeImport'])->name('employee.import');
    Route::get('/employee-excel-download', [EmployeeController::class, 'downloadSampleExcel'])->name('employee.downloadExcel');
    Route::get('/employee-export-data', [EmployeeController::class, 'employeeExportData'])->name('employee.export.data');
    Route::post('/vehicle-import', [TadaController::class, 'import'])->name('vehicle.import');
    Route::get('/vehicle-excel-download', [TadaController::class, 'downloadEmptyTemplate'])->name('vehicle.downloadExcel');
    Route::post('/daily-allowance-import', [TadaController::class, 'dailyAllowanceImport'])->name('daily-allowance.import');
    Route::get('/daily-allowance-excel-download', [TadaController::class, 'downloadSampleExcelAllowance'])->name('daily-allowance.downloadExcel');
    Route::post('/lodging-import', [TadaController::class, 'lodgingImport'])->name('lodging.import');
    Route::get('/lodging-excel-download', [TadaController::class, 'downloadEmptyTemplatelodging'])->name('lodging.downloadExcel');
    Route::post('/policy-category-import', [TadaController::class, 'policyCategoryImport'])->name('policy-category.import');
    Route::get('/policy-category-excel-download', [TadaController::class, 'downloadSampleExcelPolicyCategory'])->name('policy-category.downloadExcel');
    Route::get('/advance_log', [AdvanceLogController::class, 'advanceLog'])->name('admin.setting.tada-settings.advancelog');
    Route::post('advance_log', [AdvanceLogApiController::class, 'store']);
    Route::post('update_advance_log/{id}', [AdvanceLogApiController::class, 'update'])->name('advancelog.update');
    Route::put('advance_log_update_/{id}', [AdvanceLogApiController::class, 'advanceUpdate'])->name('advancelog.advancelogupdate');
    // Route::resource('/weekly-policy', WeeklyPolicyController::class);
    Route::resource('/menu', MenuController::class);
    Route::get('/logout', function () {
        Auth::logout();
        Session::forget('2fa_passed'); // <-- Add this line
        return redirect('login');
    })->name('logout');
    Route::get('/generate-pdf', [PDFController::class, 'generatePDF']);
    Route::any('/test', [TestController::class, 'test'])->name('test');
    Route::get('/test/show/{id}', [TestController::class, 'show'])->name('test.show');
    Route::get('/application-form/{id}', [RecruitmentCandidateController::class, 'applicationFormShow'])->name('re-ap-form.show');
    Route::post('/application-form-submit', [RecruitmentCandidateController::class, 'applicationFormSubmit'])->name('application.form.submit');
    Route::post('/get-states', [TestController::class, 'getStates'])->name('get.states');
    Route::get('/test-lb', [TestController::class, 'monthlyLeaveBalance']);
    Route::get('/test-sandwich', [TestController::class, 'sandwichLeave']);
    Route::get('/test-reverb-page', [TestController::class, 'testReverbPage']);
    Route::get('/test-reverb', [TestController::class, 'testReverb']);
    Route::get('/test-tabs', [TestController::class, 'testTabs']);
    Route::get('/testmap', [TestController::class, 'testmap']);
    Route::get('add-new-desig', [TestController::class, 'create_desig']);
    Route::post('save-new-desig', [TestController::class, 'save_new_desig']);
    Route::any('/attendance/store/{emp_id}/{month}/{customCheckInTime?}/{customCheckOutTime?}/{customBranchName?}/{customLatitude?}/{customLongitude?}', [TestController::class, 'storeEmployeeMonthlyAttendance']);
    Route::get('/download-tada-payment-sheet', function () {
        $request = request()->merge([
            'report_type' => 196,
        ] + request()->all());
        return app(ReportController::class)->export($request);
    });
    Route::get('/download-adv-payment-sheet', function () {
        $request = request()->merge([
            'report_type' => 197,
        ] + request()->all());
        return app(ReportController::class)->export($request);
    });
    Route::get('/download-expense-booking-sheet', function () {
        $request = request()->merge([
            'report_type' => 198,
        ] + request()->all());
        return app(ReportController::class)->export($request);
    });
    Route::get('/download-error-file', [EmployeeController::class, 'downloadErrorFile'])->name('employee.downloadErrorFile');
    // use Illuminate\Support\Facades\Hash;
    // use App\Models\Business;
    // Route::get('/employee-created', function () {
    //     return Auth::user();//Business::where('b_id', 14)->pluck('b_name')->first();
    //     $length = 8;
    //     $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    //     $password = substr(str_shuffle($characters), 0, $length);
    //     $hashedPassword = Hash::make($password);
    //     return [
    //         'plain' => $password,
    //         'hashed' => $hashedPassword,
    //         'str' => Str::random(8)
    //     ];
    // });
    Route::get('/clear-cache', function () {
        Artisan::call('optimize:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:cache');
        Artisan::call('route:clear');
        return 'Cache cleared successfully';
    });
    // Route::prefix('/subscriptions')->group(function () {
    //     Route::resource('/', SubscriptionController::class);
    // });
    Route::resource('subscriptions', SubscriptionController::class);

    Route::get('/salary-master', function () {
        return view('salary-master');
    });

    Route::post('/approval-expiry-save', [FormController::class, 'saveExpiryDay'])
    ->name('approval.expiry.save');

    Route::get('/attendance-import-view', [TestController::class, 'importView'])->name('attendance.import.view');
    Route::post('/register-emp', [TestController::class, 'registerEmp'])->name('register.emp');
    Route::get('/form', [FormController::class, 'index']);
    Route::get('/form/{id}', [FormController::class, 'show'])->name('form.show');
    Route::post('/form', [FormController::class, 'store'])->name('form.store');
    Route::get('/ajax/employees', [FormController::class, 'getEmployees']);
    Route::get('/ajax/modules', [FormController::class, 'getModules']);
    Route::get('/ajax/managers', [FormController::class, 'getManagers']);
    Route::get('/ajax/status', [FormController::class, 'getStatus']);
    Route::get('/ajax/employee-approval/{empId}/{moduleId}', [FormController::class, 'getEmployeeApprovalFlow'])->name('ajax.employee.approval');
    Route::post('/ajax/approval/flow', [FormController::class, 'approvalFlow'])->name('approval.flow');
    Route::get('/ajax/attendance-count/{employeeId}', [FormController::class, 'getAttendanceCount'])->name('ajax.attendance-count');
    Route::post('/attendance/import', [AttendanceController::class, 'import'])->name('attendance.import');
    // For Approval
    Route::post('/update-candidate-stage', [RecruitmentPipelineController::class, 'updateCandidateStage']);
    Route::post('/update-bulk-candidate-stage', [RecruitmentPipelineController::class, 'updateBulkCandidateStage']);
    Route::post('/interview-schedule', [RecruitmentPipelineController::class, 'interviewSchedule'])->name('interview.schedule.store');
    Route::post('/send-mail', [RecruitmentPipelineController::class, 'sendMailStore'])->name('send.mail.store');
    Route::prefix('approve')->group(function () {
        Route::post('/gate-pass', [GatePassController::class, 'approve'])->name('approve.gate-pass');
        Route::post('/mis-punch', [MispunchController::class, 'approve'])->name('approve.mis-punch');
        Route::post('/leave', [LeaveController::class, 'approve'])->name('approve.leave');
        Route::post('/travel', [TravelRequestController::class, 'approve'])->name('approve.travel');
        Route::post('/advance', [AdvanceLogController::class, 'approve'])->name('approve.advance');
    });
    Route::post('/leave-balance', [LeaveBalanceController::class, 'leaveBalance']);
    Route::post('/leave-balance-import', [LeaveBalanceController::class, 'import'])->name('leave.import');
    // Route::get('/leave-balance-excel-download', [LeaveBalanceController::class, 'downloadSampleExcel'])->name('leave.downloadExcel');
    Route::get('/download-leave-error-file', [LeaveBalanceController::class, 'downloadErrorFile'])->name('leave.downloadErrorFile');
    Route::prefix('face-detection')->group(function () {
        Route::get('employee/{FaceId}', [PunchInApiController::class, 'getEmployeeByFaceId']);
    });
    Route::post('/ai-chat', [AiChatBoxController::class, 'getBotResponse']);
    Route::get('/check-liveness', [AiChatBoxController::class, 'checkLiveness'])->name('check.livenes');
    Route::get('/video-feed', [AiChatBoxController::class, 'videoFeed'])->name('video_feed');
    // Dynamic Form Creation
    Route::get('/forms', [DynamicFormController::class, 'index'])->name('forms.index');
    Route::get('/forms/create', [DynamicFormController::class, 'create'])->name('forms.create');
    Route::post('/forms/store', [DynamicFormController::class, 'store'])->name('forms.store');
    Route::get('/forms/{id}', [DynamicFormController::class, 'show'])->name('forms.show');
    Route::delete('/forms/{id}', [DynamicFormController::class, 'destroy'])->name('forms.destroy');
    Route::post('/forms/submit', [DynamicFormController::class, 'submit'])->name('forms.submit');
    Route::get('/daily-attendance-excel-download', [AttendanceController::class, 'downloadDailyAttendanceSampleExcel'])->name('daily.attendance.downloadExcel');
    Route::post('/settings/timezone/update', [SettingController::class, 'updateTimezone'])->name('settings.updateTimezone');
    Route::get('/payslip/send-mail/{id}', [PayrollController::class, 'send_email'])->name('salary.send_email');
    Route::post('/salary/bulk-email', [PayrollController::class, 'sendBulkEmail'])->name('salary.bulk_email');
    Route::post('/check-salary-ifsc-code', [EmployeeController::class, 'getsalaryIFSCDetails'])->name('addEmp.salaryifsc');
    Route::get('/export-employees', [EmployeeController::class, 'export'])->name('employees.export');
    Route::get('/business-policy-document/{id}', [PolicyController::class, 'index'])->name('documents.index');
    Route::get('/business-policy-folder', [PolicyController::class, 'folder_index'])->name('folder.index');
    Route::post('/store', [PolicyController::class, 'store'])->name('documents.store');
    Route::delete('/{id}', [PolicyController::class, 'destroy'])->name('documents.destroy');
    Route::post('/documents-folder-store', [PolicyController::class, 'folder_store'])->name('documents_folder_store.store');
    Route::delete('/business-policy-folder/{id}', [PolicyController::class, 'folder_destroy']);
    Route::post('/business-policy/bulk-download', [PolicyController::class, 'bulkDownload'])->name('business-policy.bulk_download');
    Route::get('/document/download/{id}', [PolicyController::class, 'downloadPDF'])->name('document.download');
    Route::get('/export-sample-adhoc-component', [AdhocPayDeducController::class, 'exportSampleAdhocComponent'])->name('adhoc.export.sample');
    Route::post('/import-employees-adhoc-component', [AdhocPayDeducController::class, 'adhocComponentsImport'])->name('adhoc.import.sample');
    Route::get('/monthly-attendance/upload', [MonthlyAttendanceController::class, 'monthlyAttendanceSampleExport'])->name('monthly.attendance.downloadExcel');
    Route::get('/batch-shift-sample', [BatchShiftController::class, 'batchShiftSample'])->name('employee.batch.shift.download');
    Route::post('/monthly-attendance/import', [MonthlyAttendanceController::class, 'monthlyAttendanceImport'])->name('monthly.attendance.import');
    Route::get('/employee-detail-sample-template/{type}', [EmployeeController::class, 'downloadEmployeeDetailsSample'])->name('download.employee.details.sample');
    Route::post('/import-employee-details', [EmployeeController::class, 'importEmployeeDetails'])->name('import.employee.details');
    Route::get('admin/requests/loan-requests/{id}', [LoanRequestController::class, 'show'])->name('requests.loan-requests.show');
    Route::get('admin/exportEmiSchedule/loan-requests/{id}', [LoanRequestController::class, 'exportEmiSchedule'])->name('requests.loan-requests.exportEmiSchedule');
    // Route to view business policy documents
    // Route::get('/business-policy-document/{id}', [BusinessPolicyDocumentController::class, 'index'])->name('documents.index');
    // Route::get('/business-policy-folder', [BusinessPolicyDocumentController::class, 'folder_index'])->name('folder.index');
    // Route::post('/store', [BusinessPolicyDocumentController::class, 'store'])->name('documents.store');
    // Route::delete('/{id}', [BusinessPolicyDocumentController::class, 'destroy'])->name('documents.destroy');
    // Route::post('/documents-folder-store', [BusinessPolicyDocumentController::class, 'folder_store'])->name('documents_folder_store.store');
    // Route::delete('/business-policy-folder/{id}', [BusinessPolicyDocumentController::class, 'folder_destroy']);
    Route::get('/branches/upload', [SettingController::class, 'branchesSampleExport'])->name('business.branch.downloadExcel');
    Route::post('/branches/import', [SettingController::class, 'importBranches'])->name('business.branch.import');
    Route::get('/grades/upload', [SettingController::class, 'gradeSampleExport'])->name('business.grade.downloadExcel');
    Route::post('/grade/import', [SettingController::class, 'importGrade'])->name('business.grade.import');
    Route::get('/roles/upload', [SettingController::class, 'roleSampleExport'])->name('business.role.downloadExcel');
    Route::post('/role/import', [SettingController::class, 'importRole'])->name('business.role.import');
    Route::get('/designation/upload', [SettingController::class, 'designationSampleExport'])->name('business.designation.downloadExcel');
    Route::post('/designation/import', [SettingController::class, 'importDesignation'])->name('business.designation.import');
    Route::get('/dealership/upload', [SettingController::class, 'dealershipSampleExport'])->name('business.dealership.downloadExcel');
    Route::post('/dealership/import', [SettingController::class, 'importDealership'])->name('business.dealership.import');
    Route::get('/department/upload', [SettingController::class, 'departmentSampleExport'])->name('business.department.downloadExcel');
    Route::post('/department/import', [SettingController::class, 'importDepartment'])->name('business.department.import');
    Route::get('/monthly-attendance/upload', [MonthlyAttendanceController::class, 'monthlyAttendanceSampleExport'])->name('monthly.attendance.downloadExcel');
    Route::post('/monthly-attendance/import', [MonthlyAttendanceController::class, 'monthlyAttendanceImport'])->name('monthly.attendance.import');
    Route::get('/monthly-attendance/upload', [MonthlyAttendanceController::class, 'monthlyAttendanceSampleExport'])->name('monthly.attendance.downloadExcel');
    Route::post('/monthly-attendance/import', [MonthlyAttendanceController::class, 'monthlyAttendanceImport'])->name('monthly.attendance.import');
    Route::get('/monthly-attendance/export', [MonthlyAttendanceController::class, 'monthlyAttendanceStatusExport'])->name('monthly.attendance.status.download');
    Route::post('/monthly-attendance-status-update', [MonthlyAttendanceController::class, 'monthlyAttendanceStatusUpdate'])->name('monthly.attendance.status.update');
    Route::get('/employee-detail-sample-template/{type}', [EmployeeController::class, 'downloadEmployeeDetailsSample'])->name('download.employee.details.sample');
    Route::post('/import-employee-details', [EmployeeController::class, 'importEmployeeDetails'])->name('import.employee.details');
    // Route::post('admin/report/bank-sheet-report-export', [ReportPayrollController::class, 'bankSheetReportExport'])->name('bank.sheet.report.export');
    Route::get('/salary/master-sample-export/{businessId?}', [EmployeePayrollController::class, 'salaryMasterSampleExport'])
        ->name('salary.master.sample.export');
    Route::post('/ta-da-report-export', [TaDaReportController::class, 'taDaReport'])->name('ta.da.report');
    Route::post('/import-employee-details', [EmployeeController::class, 'importEmployeeDetails'])->name('import.employee.details');
    Route::get('/get-academic/{id}', [AcademicDetailController::class, 'getAcademic'])->name('get.academic');
    Route::get('/get-uniform/{id}', [UniformDetailController::class, 'getuniform'])->name('get.uniform');
    Route::get('/payslip-config/index', [PayslipConfigController::class, 'index'])->name('payslip-config.index');
    Route::post('/payslip-config/store-or-update', [PayslipConfigController::class, 'storeOrUpdate'])->name('payslip-config.storeOrUpdate');
    Route::delete('/payslip-config/delete', [PayslipConfigController::class, 'storeOrUpdate'])->name('payslip-config.destroy');
    Route::get('/adhoc-payments-deductions/edit-adhoc', [AdhocPayDeducController::class, 'edit'])->name('adhoc.edit');
    Route::post('/adhoc/check-salary', [AdhocPayDeducController::class, 'checkSalaryProcessed'])->name('adhoc.checkSalary');
    //device verification
    Route::prefix('admin/requests/device-verification')->middleware(['auth', 'isActive'])->group(function () {
        Route::get('/', [UserDeviceController::class, 'index'])->name('admin.requests.device_verification.index');
        // Route::post('/verify/{id}', [UserDeviceController::class, 'verify'])->name('admin.requests.device_verification.verify');
        // Route::post('/reject/{id}', [UserDeviceController::class, 'reject'])->name('admin.requests.device_verification.reject');
        Route::post('/bulk-verify', [UserDeviceController::class, 'bulkVerify'])->name('admin.requests.device_verification.bulk_verify');
        Route::post('/bulk-reject', [UserDeviceController::class, 'bulkReject'])->name('admin.requests.device_verification.bulk_reject');
        Route::get('/datatable', [UserDeviceController::class, 'datatable'])->name('admin.requests.device_verification.datatable');
    });
    // selfie verification
    Route::prefix('admin/requests/selfie-verification')->middleware(['auth', 'isActive'])->group(function () {
        Route::get('/', [SelfieVerifyController::class, 'index'])->name('admin.requests.selfie_verification.index');
        Route::post('/bulk-action', [SelfieVerifyController::class, 'bulkAction'])
            ->name('admin.requests.selfie_verification.bulk_action');
        Route::post('/inline-update', [SelfieVerifyController::class, 'updateInline'])
            ->name('admin.requests.selfie_verification.inline_update');
        Route::get('/datatable', [SelfieVerifyController::class, 'datatable'])->name('admin.requests.selfie_verification.datatable');
    });
    //privacy policy
    Route::resource('privacy-policy', \App\Http\Controllers\PrivacyPolicyController::class);
    Route::get('web/privacy-policy', [\App\Http\Controllers\PrivacyPolicyController::class, 'webIndex'])->name('privacy-policies.list');
    Route::post('/leave-request-revert', [LeaveController::class, 'leaveRequestRevert'])->name('leave-request.revert');
    Route::post('/claim-group-bulk-upload', [ClaimGroupRequestController::class, 'approveMultipleClaims'])->name('claim-group.bulk.approved');
    Route::post('/fast-recalculate', [AttendanceShiftTypeController::class, 'fastRecalculate'])->name('attendance.recalculate');
    Route::post('/byattendance-update-history', [SummaryAttendanceController::class, 'getAttendanceHistoryByDate'])->name('attendance.history');
    Route::post('/attendance/history/delete', [SummaryAttendanceController::class, 'deleteAttendanceHistory'])->name('attendance.history.delete');
    Route::prefix('admin/projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
        Route::post('/store', [ProjectController::class, 'store'])->name('project.store');
        Route::get('/show/{id}', [ProjectController::class, 'show'])->name('projects.show');
        Route::delete('/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    });
    Route::get('/reimburse-data-show/{id?}', [TadaReimburseController::class, 'reimburse_index'])->name('reimburse.show');
    Route::get('/tada-report/export/{id}', [TadaReimburseController::class, 'exportReport'])->name('reimburse.exportReport');
    Route::get('export-report', [TadaReimburseController::class, 'exportReportformate'])->name('formate.exportReport');
    Route::post('/tada-reimburse/import', [TadaReimburseController::class, 'import'])->name('reimburse.import');
    Route::post('/payment-mode/save', [PaymentModeController::class, 'save'])->name('payment-mode.save');
    Route::middleware(['auth:web'])->group(function () {
        Route::post('/admin/settings/notifications/toggle', [SettingController::class, 'toggleNotification'])->name('settings.notifications.toggle');
        Route::get('/admin/settings/notifications', [SettingController::class, 'adminSettingNotification'])->name('admin.settings.notifications');
    });
    // 2FA routes (web)
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/2fa/setup', [Employee2FAController::class, 'showSetupForm'])->name('2fa.setup');
        Route::post('/2fa/enable', [Employee2FAController::class, 'enable2FA'])->name('2fa.enable');
        Route::post('/2fa/change', [Employee2FAController::class, 'change2FA'])->name('2fa.change.submit');
        // Route::get('/2fa/verify', [Employee2FAController::class, 'showVerifyForm'])->name('2fa.verify.form');
        Route::get('/two-factor-authentication', [Employee2FAController::class, 'index'])->name('2fa.index');
        Route::post('/2fa/disable/{employee_id?}', [Employee2FAController::class, 'disable2FA'])->name('2fa.disable');
        Route::get('/2fa/change', [Employee2FAController::class, 'showChangeForm'])->name('2fa.change');
    });
    //     Route::post('/2fa/verify', [Employee2FAController::class, 'verify2FA'])->name('2fa.verify');
    // Route::get('/employee-report-old', [ReportAttendanceController::class, 'employeeReportOld'])->name('employee.reports');
    Route::get('/employee-report/{slug}', [ReportAttendanceController::class, 'employeeReport'])->name('employee.report');
    Route::get('/employee-report/{slug}', [ReportAttendanceController::class, 'employeeReport'])->name('employee.report');
    Route::get('/demo/requests', [DemoRequestController::class, 'index'])->name('demo_requests.index');
    Route::post('/demo/store', [DemoRequestController::class, 'store'])->name('schedule_demo.store');
    //reports & devicemanagement route
    Route::middleware(['auth:web'])->group(function () {
        //shivam routes start
        Route::get('/policy-report/{slug}', [MainController::class, 'policyReport'])->name('policy.report');
        Route::get('/loan-report/{slug}', [MainController::class, 'loanReport'])->name('loan.report');
        //attendance report
        Route::get('/attendance/{slug}', [MainController::class, 'attendanceReport'])->name('attendance.report');
        Route::get('/leave-report/{slug}', [MainController::class, 'leaveReport'])->name('leave.report');
        Route::get('/monitoring-report/{slug}', [MainController::class, 'monitoringReport'])->name('monitoring.report');
        //new payroll report
        Route::get('/admin/report/employee-payroll/{slug}', [MainController::class, 'payrollReport'])->name('employee.payroll.report.export.sheet');
        Route::get('/admin/report/esic-report', [MainController::class, 'esicReport'])->name('esic.report.export.sheet');
        Route::get('/admin/report/pf-eps-summary-report-export-sheet', [MainController::class, 'pfEpsReportSheet'])->name('pf.eps.summary.report.export.sheet');
        Route::get('/admin/report/tax-form/{slug}', [FormGenerationController::class, 'report'])->name('tax.form.report');

        Route::get('/admin/report/adhoc-report', [MainController::class, 'adhocReport'])->name('adhoc.report.export.sheet');
        Route::get('/admin/report/bank-sheet', [MainController::class, 'bankSheet'])->name('bank.sheet');
        Route::get('/admin/report/mc-template-report', [MainController::class, 'mcTempReport'])->name('mc.template.report');
        Route::get('/employee-report/{slug}', [MainController::class, 'employeeReport'])->name('employee.report');
        Route::get('/employee-report/{slug}', [MainController::class, 'employeeReport'])->name('employee.report');

        //khillesh changes
        // Assets Management  Reports
        Route::get('/assets/report/summary', [AssetReportController::class, 'summary'])->name('assets.report.summary');
        Route::get('/assets/report/stock', [AssetReportController::class, 'stock'])->name('assets.report.stock');
        Route::get('/assets/report/assigned', [AssetReportController::class, 'assigned'])->name('assets.report.assigned');
        Route::get('/assets/report/service', [AssetReportController::class, 'service'])->name('assets.report.service');
        Route::get('/assets/report/scrap', [AssetReportController::class, 'scrap'])->name('assets.report.scrap');
        Route::get('/assets/report/replace', [AssetReportController::class, 'replace'])->name('assets.report.replace');
        Route::post('admin/report/assets/export/summary', [AssetReportController::class, 'exportSummary'])->name('assets.report.export.summary');
        Route::post('admin/report/assets/export/stock', [AssetReportController::class, 'exportStock'])->name('assets.report.export.stock');
        Route::post('admin/report/assets/export/assigned', [AssetReportController::class, 'exportAssigned'])->name('assets.report.export.assigned');
        Route::post('admin/report/assets/export/scrap', [AssetReportController::class, 'exportScrap'])->name('assets.report.export.scrap');
        Route::post('admin/report/assets/export/replace', [AssetReportController::class, 'exportReplace'])->name('assets.report.export.replace');
        Route::post('admin/report/assets/export/service', [AssetReportController::class, 'exportService'])->name('assets.report.export.service');
    });
    #bank Details
    #bank Details
    Route::prefix('/bank-details')->group(function () {
        Route::get('/business-banks', [SettingController::class, 'bankIndex'])->name('business.bank.index');
        Route::post('/save-business-banks', [SettingController::class, 'saveBank'])->name('business.bank.save');
        Route::delete('/delete-business-bank/{id}', [SettingController::class, 'deleteBank'])->name('business.bank.delete');
        Route::get('/fetch-ifsc', [SettingController::class, 'fetchIFSCDetails'])->name('bank.fetch.ifsc');
        Route::post('/admin/email-templates/{id}/toggle-status', [App\Http\Controllers\MailTemplateController::class, 'toggleStatus'])->name('email-templates.toggle-status');
    });

    //**************************************************************************************************************
    Route::post('/employee-upload-image-admin', [EmployeeController::class, 'uploadEmpImageByAdminCheck'])->name('employee.admin.check.img');
    Route::get('/demo/requests', [DemoRequestController::class, 'index'])->name('demo_requests.index');
    Route::post('/demo/store', [DemoRequestController::class, 'store'])->name('schedule_demo.store');
    Route::get('assets/create', [AssetController::class, 'create'])->name('assets.create');
    Route::post('assets/store', [AssetController::class, 'store'])->name('assets.store');
    Route::get('assets/{asset}', [AssetController::class, 'show'])->name('assets.show');
    Route::get('assets/{asset}/edit', [AssetController::class, 'edit'])->name('assets.edit');
    Route::put('assets/{asset}', [AssetController::class, 'update'])->name('assets.update');
    Route::delete('assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');
    Route::post('assets/assign', [AssetController::class, 'assign'])->name('assets.assign');
    Route::patch('assets/{asset}/unassign', [AssetController::class, 'unassign'])->name('assets.unassign');
    Route::patch('assets/{asset}/scrap', [AssetController::class, 'scrapAsset'])->name('assets.scrap-asset');
    Route::patch('assets/{asset}/restore', [AssetController::class, 'restore'])->name('assets.restore');
    Route::patch('assets/{asset}/replace', [AssetController::class, 'replace'])->name('assets.replace');
    Route::patch('assets/{asset}/send-to-service', [AssetController::class, 'sendToService'])->name('assets.send-to-service');
    Route::patch('assets/{asset}/return-from-service', [AssetController::class, 'returnFromService'])->name('assets.return-from-service');
    Route::get('asset-types/{assetType}/fields', [AssetController::class, 'getAssetTypeFields'])->name('asset-types.fields');
    Route::get('asset-types/{assetType}/structure', [AssetController::class, 'getAssetTypeStructure'])->name('asset-types.structure');
    Route::get('/asset/services', [AssetServiceController::class, 'index'])->name('assets.services.index');
    Route::post('/assets/update-status', [AssetController::class, 'updateStatus'])->name('assets.update-status');
    Route::post('/assets/duplicate', [AssetController::class, 'duplicate'])->name('assets.duplicate');
    Route::post('/parse-invoice', [AssetServiceController::class, 'parseInvoice'])->name('parse.invoice');
    Route::post('/emails/store', [AssetServiceController::class, 'email_store'])->name('emails.store');
    Route::get('/emails/get', [AssetServiceController::class, 'getEmails'])->name('emails.get');
    Route::post('/employee/update-projects/{id}', [EmployeeController::class, 'updateProjects'])->name('employee.updateProjects');
    Route::get('/employee/{id}/projects', [EmployeeController::class, 'getProjects'])->name('employee.getProjects');
    Route::post('/categories/save', [AssetController::class, 'saveCategory'])->name('categories.save');
    Route::post('/brands/store', [AssetController::class, 'brand_store'])->name('brands.store');
    Route::post('/asset-type/save', [AssetController::class, 'assets_store'])->name('asset-type.save');
    // Asset Type routes
    Route::resource('asset-types', AssetTypeController::class);
    // API routes for AJAX configuration management
    Route::get('api/asset-types', [AssetTypeController::class, 'apiIndex'])->name('api.asset-types.index');
    // Route::post('api/asset-types', [AssetTypeController::class, 'apiStore'])->name('api.asset-types.store');
    Route::post('/asset-types/store', [AssetTypeController::class, 'apiStore'])->name('asset-types.store');
    Route::put('api/asset-types/{assetType}', [AssetTypeController::class, 'apiUpdate'])->name('api.asset-types.update');
    Route::delete('api/asset-types/{assetType}', [AssetTypeController::class, 'apiDestroy'])->name('api.asset-types.destroy');
    // Component routes
    Route::resource('components', ComponentController::class);
    Route::get('api/components', [ComponentController::class, 'apiIndex'])->name('api.components.index');
    Route::post('api/components', [ComponentController::class, 'apiStore'])->name('api.components.store');
    Route::delete('api/components/{component}', [ComponentController::class, 'apiDestroy'])->name('api.components.destroy');
    // Dropdown Option routes
    Route::resource('dropdown-options', DropdownOptionController::class)->parameters(['dropdown-options' => 'category']);
    Route::get('api/dropdown-options', [DropdownOptionController::class, 'apiIndex'])->name('api.dropdown-options.index');
    Route::post('api/dropdown-options', [DropdownOptionController::class, 'apiStore'])->name('api.dropdown-options.store');
    Route::delete('api/dropdown-options/{category}', [DropdownOptionController::class, 'apiDestroy'])->name('api.dropdown-options.destroy');
    // Employee routes
    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::get('employees-import', [EmployeeController::class, 'import'])->name('employees.import');
    Route::post('employees-import', [EmployeeController::class, 'importStore'])->name('employees.import.store');
    Route::get('employees-assignment-list', [EmployeeController::class, 'getEmployeesForAssignment'])->name('employees.assignment-list');
    Route::get('employees-export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::get('employees-export-template', [EmployeeController::class, 'exportTemplate'])->name('employees.export-template');
    // route/web.php
    Route::get('/assets/{id}/edit', [AssetController::class, 'edit'])->name('assets.edit');
    Route::post('/assets/duplicate', [AssetController::class, 'duplicate'])->name('assets.duplicate');
    // API utility routes
    Route::get('api/field-types', function () {
        return response()->json(\App\Models\FieldType::where('is_active', true)->get());
    })->name('api.field-types');
    Route::get('api/dropdown-categories', function () {
        return response()->json(\App\Models\DropdownOption::getCategories());
    })->name('api.dropdown-categories');
    Route::post('/leave-request-revert', [LeaveController::class, 'leaveRequestRevert'])->name('leave-request.revert');
    // Route::get('/download/device-connector', function () {
    //     $file = public_path('packages/fixhr-device-connector.exe');
    //     if (file_exists($file)) {
    //         return response()->download($file, 'FixHR-Device-Connector.exe');
    //     } else {
    //         abort(404, 'Installer not found.');
    //     }
    // })->name('device.connector.download');
    Route::get('/download/device-connector', [MainController::class, 'download'])
        ->name('device.connector.download');
    Route::post('/admin/email-templates/{id}/toggle-status', [App\Http\Controllers\MailTemplateController::class, 'toggleStatus'])->name('email-templates.toggle-status');
    Route::resource('/overtime-policy', OvertimePolicyController::class);
    Route::resource('/el-policy', ELPolicyController::class);
    Route::get('admin/approve/overtime-approve/{id}', [OvertimePolicyController::class, 'edit'])->name('approve.overtime.show');
    Route::put('admin/approve/overtime-approval', [OvertimePolicyController::class, 'approve'])->name('overtime.approval');
    Route::prefix('/admin/leave')->group(function () {
        Route::resource('/employee-leave-calendar', EmpLeaveCalendar::class);
        Route::post('/check-sandwich', [EmpLeaveCalendar::class, 'checkSandwich'])->name('checkSandwichLeave');
    });
    Route::prefix('admin/approval-flows')->group(function () {
        Route::get('/', [ApprovalFlowController::class, 'index'])->name('approval.index');
        Route::post('/store', [ApprovalFlowController::class, 'store'])->name('approval.store');
        Route::put('/update/{id}', [ApprovalFlowController::class, 'update'])->name('approval.update');
        Route::delete('/{id}', [ApprovalFlowController::class, 'destroy'])->name('approval.destroy');
        Route::post('/save-approvers/{moduleId}', [FormController::class, 'save_approvers'])->name('save.approvers');
        Route::post('/save-approvers-ajax/{id}', [FormController::class, 'saveApproversAjax'])->name('save.approvers.ajex');
    });
    Route::prefix('stock')->group(function () {
        Route::get('/', [KitController::class, 'index'])->name('kit.index');
        Route::post('/store', [KitController::class, 'store'])->name('stock.items.save');
        Route::get('/edit/{id}', [KitController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [KitController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [KitController::class, 'destroy'])->name('stock.items.delete');
        Route::get('/kits/{id}', [KitController::class, 'show'])->name('kits.show');
        Route::post('/kit/return-qty', [KitController::class, 'returnQty'])->name('kit.return.qty');
        Route::post('/kit/lost-qty', [KitController::class, 'lostQty'])->name('kit.lost.qty');
        Route::post('categories/save', [KitController::class, 'categories_save'])->name('stock.categories.save');
        Route::get('categories', [KitController::class, 'categories_index'])->name('categories.index');
        Route::post('units/save', [KitController::class, 'units_save'])->name('stock.units.save');
        Route::get('units', [KitController::class, 'units_index'])->name('units.index');
    });
    Route::prefix('kit-stock')->group(function () {
        Route::get('/', [KitStockController::class, 'index'])->name('kit-stock.index');
        Route::get('/create', [KitStockController::class, 'create'])->name('kit-stock.create');
        Route::post('/store', [KitStockController::class, 'store'])->name('kit-stock.store');
        Route::put('/update/{id}', [KitStockController::class, 'update'])->name('kit-stock.update');
        Route::delete('/delete/{id}', [KitStockController::class, 'destroy'])->name('kit-stock.delete');
    });
    Route::prefix('assignments')->group(function () {
        Route::get('/', [KitAssignmentController::class, 'index'])->name('assignments.index');
        Route::post('/store', [KitAssignmentController::class, 'store'])->name('kit.assign.store');
        Route::post('/return/{id}', [KitAssignmentController::class, 'returnKit'])->name('return');
        Route::post('/lost', [KitAssignmentController::class, 'lostKit'])->name('assignments.lost');
    });
    Route::prefix('damage')->group(function () {
        Route::get('/', [KitDamageController::class, 'index'])->name('damage.index');
        Route::post('/store', [KitDamageController::class, 'store'])->name('damaged.store');
    });
    Route::prefix('kit-lost')->group(function () {
        Route::get('/', [KitDamageController::class, 'kitlsotindex'])->name('kitlost.index');
        Route::post('/store', [KitDamageController::class, 'stkistloststorere'])->name('kitlost.store');
    });
    Route::prefix('replacement')->group(function () {
        Route::get('/', [KitReplacementController::class, 'index'])->name('replacement.index');
        Route::post('/store', [KitReplacementController::class, 'store'])->name('replacement.store');
    });
    Route::prefix('kit-logs')->group(function () {
        Route::get('/', [KitLogController::class, 'index'])->name('kit-logs.index');
        Route::get('/create', [KitLogController::class, 'create'])->name('kit-logs.create');
        Route::post('/store', [KitLogController::class, 'store'])->name('kit-logs.store');
        Route::delete('/delete/{id}', [KitLogController::class, 'destroy'])->name('kit-logs.delete');
    });
    Route::post('/uniform/store', [UniformDetailController::class, 'uniform_store'])->name('uniform.stock.details');
    Route::prefix('privilege/mail-template')->group(function () {
        Route::get('/', [MailTemplateController::class, 'index'])->name('mail.template.index');
        Route::post('/store', [MailTemplateController::class, 'store'])->name('mail.template.store');
        Route::get('/get/{id}', [MailTemplateController::class, 'getTemplate'])->name('mail.template.get');
        Route::delete('/delete/{id}', [MailTemplateController::class, 'destroy'])->name('mail.template.delete');
        Route::post('/toggle-status/{id}', [MailTemplateController::class, 'toggleStatus'])->name('mail.template.toggle');
    });
    Route::get('approval/unassigned-employees', [ApprovalSettingsController::class, 'getUnAssignedEmployees'])->name('approval.unassigned.employees');

    //device management
    Route::get('/device-management', [MainController::class, 'deviceManagement'])->name('device.management');
    Route::get('/device-attendance-log/{id}', [MainController::class, 'deviceAttendanceLog'])->name('device.attendance.log');
    Route::get('/attendance-logs/{business_code}/{device_sn}', [MainController::class, 'viewAttendanceLogs'])->name('attendance.logs');
    Route::post('/change-business-password', [CreateBusinessController::class, 'changeBusinessPassword'])->name('change.business.password')->middleware('auth');
});

// Route::post('/update-account', [SettingController::class, 'updateAccount'])->name('account.update');
Route::post('save-switch-email', [SettingController::class, 'saveSwitchEmail'])->name('save.switch.email');
Route::post('switch-user', [SettingController::class, 'switchUser'])->name('switch.user');

Route::post('send-email-otp', [SettingController::class, 'sendOtpEmail'])->name('send.email.otp');
Route::post('verify-email-otp', [SettingController::class, 'verifyOtpEmail'])->name('verify.email.otp');

Route::put('/contacts/{id}', [EmployeeExitController::class, 'update'])->name('contacts.update');
Route::post('/update-notes', [EmployeeExitController::class, 'saveNotes'])->name('exit.updateNotes');
Route::get('/exit/{id}/generate-pdf', [EmployeeExitController::class, 'generatePDF'])->name('exit.generate.pdf');
Route::get('/documents/generate/{id}/{type}', [EmployeeExitController::class, 'generatePDFtow'])->name('documents.generate');

Route::prefix('admin/employee-exit')->group(function () {
    Route::post('/exit/{id}/documentation', [EmployeeExitController::class, 'storeDocuments'])->name('exit.documentation.store');
    Route::get('/view/{id}', [EmployeeExitController::class, 'view'])->name('exit.view');
    Route::post('/store', [EmployeeExitController::class, 'employeeexitapprovalstore'])->name('employeeexit.approval.store');
});

Route::prefix('admin/employee-exit')->group(function () {
    Route::get('index', [EmployeeExitController::class, 'index'])->name('admin.employee-exit.index');
    Route::get('/view/{id}', [EmployeeExitController::class, 'view'])->name('exit.view');
    Route::post('/exit/{id}/manager/approve', [EmployeeExitController::class, 'managerApprove'])->name('exit.manager.approve');
    Route::post('/exit/{id}/manager/reject', [EmployeeExitController::class, 'managerReject'])->name('exit.manager.reject');
    Route::post('/exit/{id}/hr/approve', [EmployeeExitController::class, 'hrApprove'])->name('exit.hr.approve');
    Route::post('/exit/{id}/hr/reject', [EmployeeExitController::class, 'hrReject'])->name('exit.hr.reject');
    Route::post('/exit/{id}/finance/clearance', [EmployeeExitController::class, 'financeClearance'])->name('exit.finance.clearance');
    Route::post('/exit/{id}/documentation', [EmployeeExitController::class, 'storeDocuments'])->name('exit.documentation.store');
    Route::post('/exit/{id}/relieve', [EmployeeExitController::class, 'relieveEmployee'])->name('exit.relieve');
    Route::get('settings', [EmployeeExitController::class, 'fnf_seting_index'])->name('admin.setting.index');
    Route::post('/fnf-approver', [EmployeeExitController::class, 'approve'])->name('approve.fnf');
    Route::post('/upload-signature', [EmployeeExitController::class, 'uploadSignature'])->name('upload.signature');
    Route::post('/toggle-status/{id}', [EmployeeExitController::class, 'toggleStatus'])->name('employee-exit.template.toggle');
    Route::post('/business/{business}/fnf-modules', [EmployeeExitController::class, 'saveFnfModules'])->name('business.fnf.save');
    Route::get('/documents/download-all/{id}', [EmployeeExitController::class, 'downloadAll'])->name('documents.downloadAll');
    Route::post('/exit-clearance/{id}', [EmployeeExitController::class, 'saveClearance'])->name('exit.clearance.save');
    Route::post('/exit/revert', [EmployeeExitController::class, 'revert'])->name('exit.revert');
    Route::get('/configuration', [EmployeeExitController::class, 'configuration'])->name('fnf.configuration');
});

Route::get('/tada-detailed-report/export/{id}', [TadaReimburseController::class, 'exportreimburedReport'])->name('exportreimburedReport.exportReport');
Route::get('/employee/download-profile/{id}',[EmployeeController::class, 'downloadProfile'])->name('employee.download.profile');
Route::post('/settings/email-config/update', [SettingController::class, 'updateEmailConfiguration'])->name('settings.email.config.update');
Route::prefix('privilege/mail-template')->group(function () {
    Route::get('/', [MailTemplateController::class, 'index'])->name('mail.template.index');
    Route::post('/store', [MailTemplateController::class, 'store'])->name('mail.template.store');
    Route::get('/get/{id}', [MailTemplateController::class, 'getTemplate'])->name('mail.template.get');
    Route::delete('/delete/{id}', [MailTemplateController::class, 'destroy'])->name('mail.template.delete');
    Route::post('/toggle-status/{id}', [MailTemplateController::class, 'toggleStatus'])->name('mail.template.toggle');
});
