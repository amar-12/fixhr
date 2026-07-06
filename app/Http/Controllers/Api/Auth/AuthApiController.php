<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\OTPRequest;
use App\Http\Requests\Auth\PasswordRequest;
use App\Http\Requests\Auth\PhoneRequest;
use App\Http\Resources\AuthApiResource;
use App\Mail\AuthMailer;
use App\Mail\ForgetMailer;
use App\Models\Employee;
use App\Models\PersonalAccessToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Helpers\Aws\AwsHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthApiController extends Controller
{

    protected $user, $awsHelper;
    public function __construct()
    {
        $this->user = Auth::user();
        if (env('STORE_ON_S3')) {
            $this->awsHelper = new AwsHelper();
        }
    }

    /**
     * Logins a user
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $isValid = $this->isValidCredential($request);

        if (!$isValid['success']) {
            return $this->error($isValid['message'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $isValid['user'];
        $token = isset($request->type) ? $user->emp_web_auth_token : $user->emp_auth_token;
        $employee = Employee::select('emp_phone', 'emp_email','is_approval_manager')->where('emp_b_id', $user->emp_b_id)->where('emp_role_id', 1)->first();

        if ($user && empty($user->emp_device_id) && $user->emp_is_device_restriction == 1) {
            $deviceCheck = Employee::where('emp_device_id', $request->device_id)->first();
            if ($deviceCheck) {
                return response()->json([
                    "data" => null,
                    'success' => false,
                    'message' => 'This device already used!',
                ], Response::HTTP_OK);
            }
            $user->update([
                'emp_device_id' => $request->device_id
            ]);
        }

        if(isset($user->emp_device_id) && ($user->emp_device_id != $request->device_id) && $user->emp_is_device_restriction == 1){
            return response()->json([
                "data" => null,
                'success' => false,
                'message' => 'Device Missed Matched!',
            ], Response::HTTP_OK);
        }

        return $this->success([
            'user' => AuthApiResource::collection([$user])->first(),
            'b_phone' => $employee->emp_phone,
            'b_email' => $employee->emp_email,
            'is_approval_manager'=>$employee->is_approval_manager ? true : false,
            'token' => isset($user->a_auth_token) ? $user->a_auth_token : $token,
        ], 'Login successfully!');
    }

    /**
     * Biometric login API
     */
    public function loginWithBiometric(Request $request): JsonResponse
    {
        // Validate only device_token
        $request->validate([
            'device_token' => 'required|string|max:255',
            'notification_key' => 'nullable|string|max:255'
        ]);

        // Find user by device_token
        $user = Employee::where('emp_device_token', $request->device_token)->first();

        if (!$user) {
            return response()->json([
                "data" => null,
                'success' => false,
                'message' => 'Invalid biometric credential device missed matched!',
            ], Response::HTTP_OK);
        }

        $token = $user->createToken(Employee::USER_TOKEN)->plainTextToken;
        $user->emp_auth_token = $token;
        // $user->emp_device_token = $token;
        $user->save();
        return response()->json([
            'success' => true,
            'message' => 'Login successfully!',
            'data' => [
                'user' => AuthApiResource::collection([$user])->first(),
                'token' => isset($user->a_auth_token) ? $user->a_auth_token : $token,
            ]
        ], Response::HTTP_OK);
    }

    public function loginBg(LoginRequest $request): JsonResponse
    {
        $isValid = $this->bgIsValidCredential($request);

        if (!$isValid['success']) {
            return $this->error($isValid['message'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $isValid['user'];
        $token = $user->emp_bg_auth_token;

        return $this->success([
            'user' => AuthApiResource::collection([$user])->first(),
            'token' => isset($user->a_auth_token) ? $user->a_auth_token : $token,
        ], 'Login successfully!');
    }
    /**
     * Logins a user
     *
     * @param PhoneRequest $request
     * @return JsonResponse
     */
    public function loginWithPhone(PhoneRequest $request): JsonResponse
    {
        $isValid = $this->isValidPhoneCredential($request);

        if (!$isValid['success']) {
            return $this->error($isValid['message'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $isValid['user'];

        return $this->success([
            'user' => AuthApiResource::collection([$user])->first(),
            'token' => '',
        ], 'OTP has been sent successfully');
    }


    /**
     * Logins a user
     *
     * @param OTPRequest $request
     * @return JsonResponse
     */
    public function verifyOTP(OTPRequest $request): JsonResponse
    {
        $isValid = $this->isValidOTP($request);

        if (!$isValid['success']) {
            return $this->error($isValid['message'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $isValid['user'];
        $employee = Employee::select('emp_phone', 'emp_email','is_approval_manager')->where('emp_b_id', $user->emp_b_id)->where('emp_role_id', 1)->first();
        $token = isset($request->type) ? $user->emp_web_auth_token : $user->emp_auth_token;
        if ($user && empty($user->emp_device_id) && $user->emp_is_device_restriction == 1) {
            $deviceCheck = Employee::where('emp_device_id', $request->device_id)->first();
            if ($deviceCheck) {
                return response()->json([
                    "data" => null,
                    'success' => false,
                    'message' => 'This device already used!',
                ], Response::HTTP_OK);
            }
            $user->update([
                'emp_device_id' => $request->device_id
            ]);
        }

        return $this->success([
            'user' => AuthApiResource::collection([$user])->first(),
            'b_phone' => $employee->emp_phone,
            'b_email' => $employee->emp_email,
            'is_approval_manager' => (boolean)$employee->is_approval_manager,
            'token' => isset($user->a_auth_token) ? $user->a_auth_token :  $token,
        ], 'Login successfully!');
    }
    public function bgVerifyOTP(OTPRequest $request): JsonResponse
    {
        $isValid = $this->bgIsValidOTP($request);

        if (!$isValid['success']) {
            return $this->error($isValid['message'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $isValid['user'];
        $token = $user->emp_bg_auth_token;

        return $this->success([
            'user' => AuthApiResource::collection([$user])->first(),
            'token' => isset($user->a_auth_token) ? $user->a_auth_token :  $token,
        ], 'Login successfully!');
    }

    /**
     * Logins a user
     *
     * @param PasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(PasswordRequest $request)
    {
        $data = $request->validated();
        $newHashedPassword = Hash::make($data['password']);

        $user = Employee::where('emp_email', $data['email'])->where('emp_status', 71)->first();
        if ($user) {
            if (Hash::check($data['password'], $user->emp_password)) {
                return [
                    'success' => false,
                    'message' => 'New password cannot be the same as the old password'
                ];
            }
            $user->update([
                'emp_password' => $newHashedPassword
            ]);
            return [
                'success' => true,
                'message' => 'Password has been updated successfully'
            ];
        }
        return [
            'success' => false,
            'message' => 'Error updating password. Please try again later'
        ];
    }

    /**
     * Validates user credential
     *
     * @param LoginRequest $request
     * @return array
     */
    private function isValidCredential(LoginRequest $request): array
    {
        $data = $request->validated();

        // First, check Employee
        $user = Employee::where('emp_email', $data['email'])->first();
        $business = $user ? $user->fh_business : null;

        // If not found, check Admin
        if ($user === null) {
            return [
                'success' => false,
                'message' => 'Email is not matched'
            ];
        } else {
            if ($user->emp_status != 71) {
                return [
                    'success' => false,
                    'message' => 'The employee is currently inactive.'
                ];
            }
            if ($business && $business->b_status != 1) {
                return [
                    'success' => false,
                    'message' => 'The business is currently inactive.'
                ];
            }
        }

        if (Hash::check($data['password'], $user->emp_password)) {
            $token = $user->createToken(Employee::USER_TOKEN);
            if (isset($data['type'])) {
                if ($user->emp_web_auth_token !== null) {
                    $authTokens = PersonalAccessToken::where('id', explode('|', $user->emp_web_auth_token)[0])->first();
                    if (isset($authTokens)) {
                        $authTokens->delete();
                    }
                }

                // Save updated tokens
                $user->update([
                    'emp_web_auth_token' => $token->plainTextToken,
                     'emp_fcm_token' => $data['notification_key'],
                ]);
            } else {
                if ($user->emp_auth_token !== null) {
                    $aToken = PersonalAccessToken::where('id', explode('|', $user->emp_auth_token)[0])->first();
                    if (isset($aToken)) {
                        $aToken->delete();
                    }
                }
                $user->update([
                    'emp_auth_token' => $token->plainTextToken,
                     'emp_fcm_token' => $data['notification_key'],
                ]);
            }
            return [
                'success' => true,
                'user' => $user
            ];
        }

        return [
            'success' => false,
            'message' => 'Password is not matched',
        ];
    }
    private function bgIsValidCredential(LoginRequest $request): array
    {
        $data = $request->validated();

        // First, check Employee
        $user = Employee::where('emp_email', $data['email'])->where('emp_status', 71)->first();

        // If not found, check Admin
        if ($user === null) {
            return [
                'success' => false,
                'message' => 'Invalid Credential'
            ];
        }

        if (Hash::check($data['password'], $user->emp_password)) {
            $token = $user->createToken(Employee::USER_TOKEN);

                if ($user->emp_bg_auth_token !== null) {
                    $aToken = PersonalAccessToken::where('id', explode('|', $user->emp_bg_auth_token)[0])->first();
                    if (isset($aToken)) {
                        $aToken->delete();
                    }
                }
                $user->update([
                    'emp_bg_auth_token' => $token->plainTextToken,
                     'emp_fcm_token' => $data['notification_key'],
                ]);

            return [
                'success' => true,
                'user' => $user
            ];
        }

        return [
            'success' => false,
            'message' => 'Password is not matched',
        ];
    }

    /**
     * Validates user credential
     *
     * @param PhoneRequest $request
     * @return array
     */
    private function isValidPhoneCredential(PhoneRequest $request): array
    {
        $data = $request->validated();

        // First, check Employee
        $user = Employee::where('emp_phone', $data['phone'])->where('emp_status', 71)->first();

        if ($user === null) {
            return [
                'success' => false,
                'message' => 'Invalid Credential'
            ];
        }

        $deviceCheck = Employee::where('emp_device_id', $request->device_id)->first();
        if ($deviceCheck && empty($user->emp_device_id) && $user->emp_is_device_restriction == 1) {
            return [
                "data" => null,
                'success' => false,
                'message' => 'This device already used!',
            ];
        }

        if(isset($user->emp_device_id) && ($user->emp_device_id != $request->device_id) && $user->emp_is_device_restriction == 1){
            return [
                "data" => null,
                'success' => false,
                'message' => 'Device Missed Matched!',
            ];
        }

        $otp = mt_rand(100000, 999999);
        $encodedOTP = urlencode($otp);
        $app = 'https://fixhr.app';
        $message = "Your One-Time Password (OTP) is {$encodedOTP}. Please enter this code to proceed. Thanks {$app} NSL LIFE";

        $url = "https://mobicomm.dove-sms.com/submitsms.jsp?user=KesarE&key=8360975400XX&senderid=NSLSMS&mobile={$data['phone']}&message=" . urlencode($message) . '&accusage=6';

        $response = Http::withoutVerifying()->get($url);

        if ($response->successful()) {
            if (isset($user->a_id)) {
                $user->update([
                    'a_otp' => $otp,
                    'a_otp_created_at' => Carbon::now()
                ]);
            } else {
                $user->update([
                    'emp_otp' => $otp,
                    'emp_otp_created_at' => Carbon::now()
                ]);
            }
            return [
                'success' => true,
                'user' => $user
            ];
        }

        return [
            'success' => false,
            'message' => 'Password is not matched',
        ];
    }

    /**
     * Validates user credential
     *
     * @param OTPRequest $request
     * @return array
     */
    private function isValidOTP(OTPRequest $request): array
    {
        $data = $request->validated();

        // First, check Employee
        $user = Employee::where('emp_phone', $data['phone'])->where('emp_status', 71)->first();

        if ($user === null) {
            return [
                'success' => false,
                'message' => 'Invalid Credential'
            ];
        }

        $otpCreationTime = Carbon::parse($user->otp_created_at);
        $expirationTime = $otpCreationTime->addMinutes(10);

        if (Carbon::now()->lt($expirationTime)) {


            if ($user->emp_otp === $data['otp']) {
                $token = $user->createToken(Employee::USER_TOKEN);

                if (isset($data['type'])) {
                    if ($user->emp_web_auth_token !== null) {
                        $authTokens = PersonalAccessToken::where('id', explode('|', $user->emp_web_auth_token)[0])->first();
                        if (isset($authTokens)) {
                            $authTokens->delete();
                        }
                    }

                    // Save updated tokens
                    $user->update([
                        'emp_web_auth_token' => $token->plainTextToken,
                         'emp_fcm_token' => $data['notification_key'],
                    ]);
                } else {
                    if ($user->emp_auth_token !== null) {
                        $aToken = PersonalAccessToken::where('id', explode('|', $user->emp_auth_token)[0])->first();
                        if (isset($aToken)) {
                            $aToken->delete();
                        }
                    }
                    $user->update([
                        'emp_auth_token' => $token->plainTextToken,
                        'emp_otp' => null,
                        'emp_otp_created_at' => null,
                         'emp_fcm_token' => $data['notification_key'],
                    ]);
                }

                return [
                    'success' => true,
                    'user' => $user
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'OTP is not matched',
        ];
    }
    private function bgIsValidOTP(OTPRequest $request): array
    {
        $data = $request->validated();

        // First, check Employee
        $user = Employee::where('emp_phone', $data['phone'])->where('emp_status', 71)->first();

        if ($user === null) {
            return [
                'success' => false,
                'message' => 'Invalid Credential'
            ];
        }

        $otpCreationTime = Carbon::parse($user->otp_created_at);
        $expirationTime = $otpCreationTime->addMinutes(10);

        if (Carbon::now()->lt($expirationTime)) {


            if ($user->emp_otp === $data['otp']) {
                $token = $user->createToken(Employee::USER_TOKEN);

                    if ($user->emp_bg_auth_token !== null) {
                        $aToken = PersonalAccessToken::where('id', explode('|', $user->emp_bg_auth_token)[0])->first();
                        if (isset($aToken)) {
                            $aToken->delete();
                        }
                    }
                    $user->update([
                        'emp_bg_auth_token' => $token->plainTextToken,
                        'emp_otp' => null,
                        'emp_otp_created_at' => null,
                         'emp_fcm_token' => $data['notification_key'],
                    ]);


                return [
                    'success' => true,
                    'user' => $user
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'OTP is not matched',
        ];
    }

    public function forgotPassword(Request $request)
    {
        $data = $request->all();
        if (!($data['email'])) {
            return [
                'success' => false,
                'message' => 'Email is required'
            ];
        }
        $user = Employee::where('emp_email', $data['email'])->where('emp_status', 71)->first();

        if ($user === null) {
            return [
                'success' => false,
                'message' => 'Invalid Email'
            ];
        }

        $otp = rand(100000, 999999);
        $details = [
            'name' => $user->emp_full_name ?? 'User',
            'otp' => $otp,
        ];
        $sendMail = CentralLogics::send_mail($request->email, new ForgetMailer($details));
        if ($sendMail) {
            $user->update([
                'emp_otp' => $otp,
                'otp_created_at' => Carbon::now()
            ]);
            return [
                'success' => true,
                'message' => 'OTP has been sent successfully'
            ];
        }
        return [
            'success' => false,
            'message' => 'Failed to send OTP'
        ];
    }

    public function verifyForgotPasswordOTP(Request $request)
    {
        if ($request->email) {
            $user = Employee::where('emp_email', $request->email)->where('emp_status', 71)->first();
            $otpCreationTime = Carbon::parse($user->otp_created_at);
            $expirationTime = $otpCreationTime->addMinutes(10);
            if (Carbon::now()->lt($expirationTime)) {
                if ($user->emp_otp == $request->otp) {
                    return [
                        'success' => true,
                        'message' => 'OTP has been verified successfully'
                    ];
                }
                return [
                    'success' => false,
                    'message' => 'Invalid OTP'
                ];
            }
            return [
                'success' => false,
                'message' => 'OTP has expired',
            ];
        }
    }

    /**
     * Logins a user with token
     *
     * @return JsonResponse
     */
    public function loginWithToken(): JsonResponse
    {
        return $this->success(auth()->user(), 'Login successfully!');
    }

    /**
     * Logouts a user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        $emp = Employee::find($user->emp_id);
        $emp->update([
            'emp_fcm_token' => null
        ]);
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logout successfully!');
    }
    public function bgLogout(Request $request): JsonResponse
    {
        $user = Auth::user();
        $emp = Employee::find($user->emp_id);
        $emp->update([
            'emp_fcm_token' => null
        ]);
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logout successfully!');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // max 2MB
        ]);

        $user = Auth::user();
        $employee = Employee::find($user->emp_id);

        if (!$employee || !$employee->fh_business) {
            return response()->json([
                'status' => false,
                'message' => 'Employee or business not found.'
            ], 404);
        }

        $file = $request->file('image');
        $filename = time() . '_' . md5($file->getClientOriginalName()) . '.' . $file->extension();
        $imagePath = $employee->fh_business->b_unique_id . '/' . $filename;

        $shouldStoreOnS3 = env('STORE_ON_S3', false);
        $bucket = 'fixhr-employee-profiles';
        $profile_url = null;

        // Delete old image before uploading new one
        $oldProfilePhoto = $employee->emp_profile_photo;

        if ($oldProfilePhoto) {
            if ($shouldStoreOnS3) {
                $parsedUrl = parse_url($oldProfilePhoto, PHP_URL_PATH);
                $key = ltrim($parsedUrl, '/');

                // Delete from S3 bucket
                $this->awsHelper->deleteFileFromS3($bucket, $key);
            } else {
                $parsedUrl = parse_url($oldProfilePhoto, PHP_URL_PATH);
                $relativePath = ltrim($parsedUrl, '/');
                $fullPath = public_path($relativePath);

                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
        }

        if ($shouldStoreOnS3) {
            $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);

            if (!empty($uploadResult['status'])) {
                $profile_url = $uploadResult['ObjectURL'];
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to upload to S3.'
                ], 500);
            }
        } else {
            $uploaded = \ChandraHemant\HtkcUtils\CommonUtils::uploadFiles($request, 'image', 'employee_profile/'.$employee->fh_business->b_unique_id, ['prefix' => 'emp_profile']);
            $profile_url = isset($uploaded[0]) ? url($uploaded[0]) : null;
        }

        $employee->update([
            'emp_profile_photo' => $profile_url,
        ]);

        return response()->json([
            'result' => [
                [
                    'file_url' => $profile_url,
                ]
            ],
            'status' => true,
            'message' => 'Image uploaded successfully.',
        ]);
    }

    public function changePasswordVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits:10',
            'otp' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        $employee = Employee::where('emp_phone', $request->phone)
            ->where('emp_otp', $request->otp)
            ->first();

        if ($employee) {
            return response()->json([
                'status' => true,
                'message' => 'OTP verified.',
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'OTP not verified.',
        ]);
    }

    public function changePasswordMobile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        $user->emp_password = Hash::make($request->password);
        $user->emp_otp = null;
        $user->save();

        return response()->json([
            'status' => true,
            'results' => true,
            'message' => 'Password changed successfully.',
        ], 200);
    }
}
