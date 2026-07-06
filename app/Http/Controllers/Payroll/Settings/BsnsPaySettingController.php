<?php

namespace App\Http\Controllers\Payroll\Settings;

use App\Http\Controllers\Controller;
use App\Models\FinancialYear;
use App\Models\PayrollMasterSetting;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Log;

class BsnsPaySettingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $setting = PayrollMasterSetting::where('pms_b_id', $businessId)->first();
        if ($this->user) {
            if ($request->ajax()) {
                $dynamicConditions = [
                    [
                        'method' => 'where',
                        'args' => ['fy_b_id', $businessId],
                    ],
                    [
                        'method' => 'select',
                        'args' => ['fy_id', 'fy_year', 'fy_start_date', 'fy_end_date', 'fy_is_current'],
                        'relation' => [],
                    ],
                    [
                        'method' => 'orderBy',
                        'args' => ['fy_start_date', 'desc'],
                        'relation' => [],
                    ],
                ];

                $searchColumns = ['fy_year', 'fy_start_date', 'fy_end_date', 'fy_is_current', 'fy_b_id'];

                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new FinancialYear,
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                ))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $key => $val) {
                    $i++;
                    $row = [];
                    $row[] = $i;
                    $row[] = $val->fy_year;
                    $row[] = Carbon::parse($val->fy_start_date)->format('d-M-Y');
                    $row[] = Carbon::parse($val->fy_end_date)->format('d-M-Y');
                    $row[] = $val->fy_is_current ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
                    $row[] = '
                            <div class="btn-list ms-3">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-ellipsis-v"></i> <!-- Three-dot icon -->
                                    </button>
                                    <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                        <li>
                                            <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-financial-year"
                                                type="button"
                                                data-id="'.$val->fy_id.'"
                                                data-year="'.$val->fy_year.'"
                                                data-start_date="'.$val->fy_start_date.'"
                                                data-end_date="'.$val->fy_end_date.'"
                                                data-is_current="'.$val->fy_is_current.'">
                                                <i class="feather feather-edit"></i> Edit
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>';
                    $rowData[] = $row;
                }

                $output = [
                    'draw' => $request->input('draw'),
                    'recordsTotal' => count($list),
                    'recordsFiltered' => (new DynamicModelDataTableHelper(
                        eloquentModel: new FinancialYear,
                        dynamicConditions: $dynamicConditions,
                    ))->countFilteredServerSideDataTable(),
                    'data' => $rowData,
                ];

                return json_encode($output);
            }

            $columns = [
                'S. No.',
                'Financial Year',
                'Start Date',
                'End Date',
                'Current Status',
                'Action',
            ];

            $financialYears = FinancialYear::where('fy_b_id', $businessId)->select('fy_id', 'fy_year', 'fy_start_date', 'fy_end_date', 'fy_is_current')
                ->orderBy('fy_start_date', 'desc')
                ->get();

            return view('admin.payroll.business-payroll-setting', compact('financialYears', 'columns'));
        } else {
            abort(404);
        }
    }

    public function storeOrUpdate(Request $request)
    {
        try {
            \Log::info('Payroll settings store/update request:', $request->all());

            $validated = $request->validate([
                'payroll_cycle' => 'required|string',
                'payroll_mode' => 'required|in:manual,auto',
                'year_type' => 'required|in:0,1',
                'include_with_salary' => 'required|in:0,1',
                'include_tada' => 'required|in:0,1',
            ]);

            $businessId = Auth::user()->emp_b_id;

            if (! $businessId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Business ID not found for this user.',
                ], 400);
            }

            \Log::info('Processing payroll settings for business ID: ' . $businessId);

            $setting = PayrollMasterSetting::where('pms_b_id', $businessId)->first();

            if ($setting) {
                // ✅ Update existing record
                $updateData = [
                    'pms_payroll_cycle' => $validated['payroll_cycle'],
                    'pms_payroll_mode' => $validated['payroll_mode'],
                    'pms_year_type' => $validated['year_type'],
                    'pms_include_with_salary' => $validated['include_with_salary'],
                    'pms_include_tada_with_salary' => $validated['include_tada'],
                    'updated_at' => now(),
                ];
                \Log::info('Updating payroll settings:', $updateData);

                $setting->update($updateData);

                $message = 'Payroll settings updated successfully!';
            } else {
                // ✅ Create new record
                $createData = [
                    'pms_b_id' => $businessId,
                    'pms_payroll_cycle' => $validated['payroll_cycle'],
                    'pms_payroll_mode' => $validated['payroll_mode'],
                    'pms_year_type' => $validated['year_type'],
                    'pms_include_with_salary' => $validated['include_with_salary'],
                    'pms_include_tada_with_salary' => $validated['include_tada'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                \Log::info('Creating new payroll settings:', $createData);

                $setting = PayrollMasterSetting::create($createData);

                $message = 'Payroll settings created successfully!';
            }

            \Log::info('Payroll settings saved successfully:', ['setting_id' => $setting->id ?? 'new']);

            return response()->json([
                'status' => true,
                'message' => $message,
                'data' => $setting,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Payroll settings validation error:', $e->errors());

            return response()->json([
                'status' => false,
                'message' => 'Validation error: ' . implode(', ', array_map(function ($errors) {
                    return implode(', ', $errors);
                }, $e->errors())),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Payroll settings error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to save payroll settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sendOtp(Request $request)
    {
        try {
            $user = Auth::user();
            $action = $request->action; // 'lock' or 'unlock'

            if (! $user) {
                return response()->json(['status' => false, 'message' => 'User not authenticated.']);
            }
            $setting = PayrollMasterSetting::where('pms_b_id', $user->emp_b_id)->first();

            $mobile = $setting->pms_phone ?? null;
            if (! $mobile) {
                return response()->json(['status' => false, 'message' => 'Registered mobile number not found.']);
            }

            // ✅ Generate 6-digit OTP
            $otp = rand(100000, 999999);

            // ✅ Save OTP to employee record
            $user->update([
                'emp_otp' => $otp,
                'emp_otp_created_at' => now(),
            ]);

            Session::put('payroll_otp_action', $action);

            // ✅ Prepare SMS text
            $app = 'https://fixhr.app';
            $message = "Your One-Time Password (OTP) is {$otp}. Please enter this code to proceed. Thanks {$app} NSL LIFE";

            // ✅ Use HTTP endpoint to avoid SSL timeout
            $url = "http://mobicomm.dove-sms.com/submitsms.jsp?user=KesarE&key=8360975400XX&senderid=NSLSMS&mobile={$mobile}&message=".urlencode($message).'&accusage=6';

            // ✅ Send SMS request
            $response = Http::withoutVerifying()
                ->timeout(20)
                ->get($url);

            // ✅ Log API response for debugging
            Log::info('Payroll sendOtp', [
                'user_id' => $user->emp_id,
                'mobile' => $mobile,
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
                'action' => $action,
            ]);

            // ✅ If SMS sent successfully
            if ($response->successful()) {
                // ✅ Update payroll setting lock status right away
                // $setting = PayrollMasterSetting::where('pms_b_id', $user->emp_b_id)->first();
                // if ($setting) {
                //     $setting->update([
                //         'pms_is_locked' => $action === 'lock' ? 1 : 0,
                //     ]);
                // }

                // ✅ Store OTP in session
                // Session::put('payroll_otp', [
                //     'action' => $action,
                //     'expires_at' => now()->addMinutes(5),
                // ]);

                return response()->json([
                    'status' => true,
                    'message' => 'OTP sent successfully to your registered mobile number ending with '.substr($mobile, -4),
                ]);
            }

            // ❌ SMS failed
            return response()->json([
                'status' => false,
                'message' => 'Failed to send OTP. SMS gateway error.',
                'gateway_response' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Payroll sendOtp Exception', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'message' => 'System error occurred while sending OTP. Please try again later.',
            ]);
        }
    }
        public function verifyOtp(Request $request)
        {
            try {
                $user = Auth::user();
                $inputOtp = $request->otp;
                $action = $request->action; // 'lock' या 'unlock'

                if (!$user) {
                    return response()->json([
                        'status' => false,
                        'message' => 'User not authenticated.',
                    ]);
                }

                // ✅ पहले OTP verify करें
                // 1. Check if OTP exists in user record
                if (!$user->emp_otp) {
                    return response()->json([
                        'status' => false,
                        'message' => 'No OTP found. Please request a new OTP.',
                    ]);
                }

                // 2. Check OTP expiry
                if (!$user->emp_otp_created_at || Carbon::now()->diffInMinutes($user->emp_otp_created_at) > 5) {
                    return response()->json([
                        'status' => false,
                        'message' => 'OTP expired. Please request a new OTP.',
                    ]);
                }

                // 3. Verify OTP match
                if ($user->emp_otp != $inputOtp) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid OTP. Please enter correct OTP.',
                    ]);
                }

                // ✅ OTP VERIFIED SUCCESSFULLY
                // अब ही lock/unlock update करें

                $setting = PayrollMasterSetting::where('pms_b_id', $user->emp_b_id)->first();

                if (!$setting) {
                    // अगर setting नहीं है तो create करें
                    $setting = PayrollMasterSetting::create([
                        'pms_b_id' => $user->emp_b_id,
                        'pms_is_locked' => ($action === 'lock') ? 1 : 0,
                    ]);
                } else {
                    // Existing setting update करें
                    $setting->update([
                        'pms_is_locked' => ($action === 'lock') ? 1 : 0,
                        'locked_by' => $user->id,
                        'locked_at' => now(),
                    ]);
                }

                // ✅ OTP clear करें (security के लिए)
                $user->update([
                    'emp_otp' => null,
                    'emp_otp_created_at' => null,
                ]);

                // Session clear
                Session::forget('payroll_otp');

                return response()->json([
                    'status' => true,
                    'message' => ($action === 'lock')
                        ? 'Payroll settings locked successfully.'
                        : 'Payroll settings unlocked successfully.',
                    'is_locked' => ($action === 'lock') ? 1 : 0,
                ]);

            } catch (\Exception $e) {
                \Log::error('OTP Verification Error: ' . $e->getMessage());

                return response()->json([
                    'status' => false,
                    'message' => 'Failed to verify OTP. Please try again.',
                ]);
            }
        }
        // Controller में updatePhone function update करें
        public function updatePhone(Request $request)
        {
            $request->validate([
                'phone_number' => 'required|digits:10',
            ]);

            try {
                $businessId = auth()->user()->emp_b_id;

                // ✅ CRITICAL CHANGE: PayrollMasterSetting में phone update करें
                $setting = PayrollMasterSetting::where('pms_b_id', $businessId)->first();

                if ($setting) {
                    // Update existing payroll setting
                    $setting->pms_phone = $request->phone_number;
                    $setting->save();
                } else {
                    // Create new payroll setting if doesn't exist
                    $setting = PayrollMasterSetting::create([
                        'pms_b_id' => $businessId,
                        'pms_phone' => $request->phone_number,
                        'pms_is_locked' => 0
                    ]);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Phone number updated successfully',
                    'data' => $setting,
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update phone number',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

    // public function sendOtp(Request $request)
    // {
    //     $action = $request->input('action');
    //     $user = auth()->user();

    //     // Generate OTP
    //     $otp = rand(100000, 999999);

    //     // Store OTP in session
    //     session([
    //         'payroll_otp' => $otp,
    //         'payroll_otp_expiry' => now()->addMinutes(5),
    //         'payroll_otp_action' => $action
    //     ]);

    //     // Determine where to send OTP
    //     $phoneToUse = null;
    //     $channel = 'email';
    //     $recipient = $user->email;

    //     // Check if user provided a phone number
    //     if ($request->has('phone_number') && $request->input('use_unsaved_phone')) {
    //         $phoneToUse = $request->input('phone_number');
    //         $channel = 'mobile';
    //         $recipient = $phoneToUse;
    //     }
    //     // Check if user has saved phone number
    //     elseif (!empty($user->phone)) {
    //         $phoneToUse = $user->phone;
    //         $channel = 'mobile';
    //         $recipient = substr($phoneToUse, 0, 3) . '****' . substr($phoneToUse, -3);
    //     }

    //     // Send OTP via selected channel
    //     if ($channel === 'mobile' && $phoneToUse) {
    //         try {
    //             $this->sendSmsOtp($phoneToUse, $otp);

    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'OTP sent to your mobile number',
    //                 'channel' => 'mobile',
    //                 'recipient' => $recipient
    //             ]);
    //         } catch (\Exception $e) {
    //             // Fallback to email
    //             return $this->sendEmailOtp($user->email, $otp, $action);
    //         }
    //     } else {
    //         // Send via email
    //         return $this->sendEmailOtp($user->email, $otp, $action);
    //     }
    // }

    private function sendEmailOtp($email, $otp, $action)
    {
        try {
            Mail::to($email)->send(new OtpMail($otp, $action));

            return response()->json([
                'status' => true,
                'message' => 'OTP sent to your email',
                'channel' => 'email',
                'recipient' => $email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ], 500);
        }
    }
}
