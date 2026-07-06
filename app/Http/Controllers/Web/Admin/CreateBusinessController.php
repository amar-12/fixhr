<?php

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Mail\AuthMailer;
use App\Models\Admin;
use App\Models\AppMenu;
use App\Models\AppRolesHasPermission;
use App\Models\Branch;
use App\Models\Business;
use App\Models\City;
use App\Models\Country;
use App\Models\Employee;
use App\Models\MasterTable;
use App\Models\Menu;
use App\Models\RolesHasPermission;
use App\Models\TadaMetroCity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;
use App\Models\State;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use ChandraHemant\HtkcUtils\CommonUtils;
use App\Helpers\Aws\AwsHelper;
use App\Models\KycSettings;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;

use function Symfony\Component\Clock\now;

class CreateBusinessController extends Controller
{
    protected $awsHelper;

    public function __construct(AwsHelper $awsHelper)
    {
        if (env('STORE_ON_S3')) {
            $this->awsHelper = $awsHelper;
        }
    }

    public function index()
    {
        return view('auth.admin.registration');
    }

    public function verify(Request $request)
    {
        if ($request->has('email')) {
            // First, check Admin or Employee
            if (Employee::where('emp_email', $request->email)->where('emp_status', 71)->first()) {
                Alert::warning('', 'This Email is Already Registered Kindly Enter Your New Email')->autoClose(3000);
                return redirect('signup');
            } else {
                $otp = rand(100000, 999999);
                $details = [
                    'name' => 'User',
                    'otp' => $otp,
                ];
                // $sendMail = CentralLogics::send_mail($request->email, new AuthMailer($details));
                // if (isset($sendMail)) {
                $admin = Admin::updateOrCreate(
                    ['a_email' => $request->email],
                    ['a_otp' => $otp, 'a_role_id' => 1]
                );
                $request->session()->put('firstEmail', $request->email);
                Session::put('Progress', $request->email);
                Alert::success('', 'OTP has been Send Successfully to Your Register Email')->autoClose(3000);
                return redirect('signup/otp');
                // }
            }
        } elseif ($request->has('otp')) {

            $pendingAdmin = Admin::where([
                'a_email' => $request->session()->get('firstEmail'),
                'a_otp' => $request->otp,
            ])->first();

            if (isset($pendingAdmin)) {
                Alert::success('', "Your OTP has been Verified Successfully")->autoClose(3000);
                return redirect('signup/create');
            } else {
                return back()->withErrors(['otpError' => 'Error: Invalid OTP']);
            }
        }
    }

    public function otp()
    {
        return view('auth.admin.otp');
    }

    public function verifyPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contactNo' => 'required|digits:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $phone = $request->input('contactNo');

        $alreadyRegistered = Employee::where(['emp_role_id' => 1, 'emp_phone' => $phone])->exists();
        if ($alreadyRegistered) {
            return response()->json(['status' => false, 'message' => 'Entered contact number already exists in our records, please login or enter another number.'], 200);
        }

        $admin = Admin::where('a_email', Session::get('firstEmail'))->first();
        if (!$admin) {
            Log::warning('verifyPhone: no admin record for session email', ['email' => Session::get('firstEmail')]);
            return response()->json(['status' => false, 'message' => 'No pending signup found for this session.'], 404);
        }

        $otp = mt_rand(100000, 999999);
        $app = 'https://fixhr.app';
        $message = "Your One-Time Password (OTP) is {$otp}. Please enter this code to proceed. Thanks {$app} NSL LIFE";
        $url = "https://mobicomm.dove-sms.com/submitsms.jsp?user=KesarE&key=8360975400XX&senderid=NSLSMS&mobile={$phone}&message=" . urlencode($message) . '&accusage=6';

        try {
            $response = Http::withoutVerifying()->timeout(10)->get($url);
        } catch (\Throwable $e) {
            Log::error('verifyPhone: exception while calling SMS provider', ['exception' => $e, 'phone' => $phone]);
            return response()->json(['status' => false, 'message' => 'Failed to send OTP SMS', 'error' => $e->getMessage()], 500);
        }

        if ($response->successful()) {
            try {
                $admin->update([
                    'a_phone' => $phone,
                    'a_otp' => $otp,
                    'a_otp_created_at' => Carbon::now()
                ]);
                Log::info('Sms response', ['res' => $response->json()]);
            } catch (\Throwable $e) {
                Log::error('verifyPhone: failed to update admin OTP', ['exception' => $e, 'admin_id' => $admin->a_id ?? null]);
                return response()->json(['status' => false, 'message' => 'OTP sent but failed to persist record'], 500);
            }

            return response()->json([
                'status' => true,
                'message' => 'OTP sent to entered phone number.'
            ], 200);
        }

        Log::error('verifyPhone: SMS provider returned non-success', ['status' => $response->status(), 'body' => $response->body(), 'phone' => $phone]);
        return response()->json(['status' => false, 'message' => 'Failed to send OTP SMS', 'provider_status' => $response->status()], 502);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|string|min:6|max:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $otp = $request->otp;
        $phone = $request->phone;

        // First, check Employee
        $admin = Admin::where('a_phone', $phone)->where('a_email', Session::get('firstEmail'))->first();

        if ($admin === null) {
            return response()->json(['status' => false, 'message' => 'Phone number not matched!'], 200);
        }

        $otpCreationTime = Carbon::parse($admin->otp_created_at);
        $expirationTime = $otpCreationTime->addMinutes(10);

        if (Carbon::now()->lt($expirationTime)) {
            if ($admin->a_otp === $otp) {
                $admin->update([
                    'a_otp' => null,
                    'a_otp_created_at' => null,
                ]);
                return response()->json(['status' => true, 'message' => 'OTP matched, contact verified'], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Entered OTP does not match!'], 200);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'OTP expired!'], 200);
        }
    }

    public function create()
    {
        $businessCat = MasterTable::where('m_group', 'BUSINESS_CATEGORY')->orderBy('m_name', 'asc')->pluck('m_name', 'm_id')->toArray();
        $businessType = MasterTable::where('m_group', 'BUSINESS_TYPE')->orderBy('m_name', 'asc')->pluck('m_name', 'm_id')->toArray();
        if (Session()->get('firstEmail')) {
            return view('auth.admin.business', compact('businessCat', 'businessType'));
        } else {
            return redirect('/login');
        }
    }

    public function getState(Request $request)
    {
        $states = State::where('s_c_id', $request->country)->orderBy('s_name')->select('s_id', 's_name')->get();
        return response()->json(['states' => $states]);
    }

    public function getCity(Request $request)
    {
        $City = City::where('ct_s_id', $request->state)->orderBy('ct_name')->select('ct_id', 'ct_name')->get();
        return response()->json(['city' => $City]);
    }

    public function sandboxAuth()
    {
        $apiDetails = KycSettings::whereNull('business_id')->where('provider_name', 'Sandbox')->first();

        if (!$apiDetails) {
            Log::error('KycSettings record for Sandbox provider not found');
            return null;
        }

        try {
            if (!method_exists($apiDetails, 'isTokenValid') || !$apiDetails->isTokenValid()) {
                $response = Http::withHeaders([
                    'x-api-key' => $apiDetails->client_id,
                    'x-api-secret' => $apiDetails->client_secret,
                    'x-api-version' => $apiDetails->client_version,
                ])->post(rtrim($apiDetails->api_base_url, '/') . '/authenticate');

                if ($response->ok()) {
                    $body = $response->json();
                    if (!empty($body['access_token'])) {
                        $token_expriy = Carbon::now()->addDay();
                        $accessToken = $body['access_token'];
                        $apiDetails->update(['access_token' => $accessToken, 'token_expiration' => $token_expriy]);
                        // Refresh the model attributes so callers have the token
                        $apiDetails->access_token = $accessToken;
                        $apiDetails->token_expiration = $token_expriy;
                    } else {
                        Log::error('Sandbox authenticate response missing access_token', ['response' => $body]);
                        return null;
                    }
                } else {
                    Log::error('Sandbox authenticate HTTP error', ['status' => $response->status(), 'body' => $response->body()]);
                    return null;
                }
            }
        } catch (\Throwable $e) {
            Log::error('Exception during sandboxAuth: ' . $e->getMessage(), ['exception' => $e]);
            return null;
        }

        return $apiDetails;
    }

    public function fetchGstDetails($gstin)
    {
        $apiDetails = $this->sandboxAuth();

        if (!$apiDetails) {
            Log::error('fetchGstDetails aborted: sandboxAuth returned null');
            return ['status' => false, 'error' => 'KYC API details unavailable'];
        }

        $accessToken = $apiDetails->access_token ?? null;
        if (empty($accessToken)) {
            Log::error('fetchGstDetails aborted: access_token missing in KycSettings', ['kyc_id' => $apiDetails->id ?? null]);
            return ['status' => false, 'error' => 'KYC access token unavailable'];
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-api-key' => $apiDetails->client_id,
                'x-api-version' => $apiDetails->client_version,
                'authorization' => $accessToken,
            ])->post(rtrim($apiDetails->api_base_url, '/') . '/gst/compliance/public/gstin/search', ['gstin' => $gstin]);

            if (!$response->ok()) {
                Log::error('fetchGstDetails HTTP error', ['status' => $response->status(), 'body' => $response->body()]);
                return ['error' => 'Failed to fetch GST details', 'status' => $response->status()];
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('Exception during fetchGstDetails: ' . $e->getMessage(), ['exception' => $e]);
            return ['error' => 'Exception while fetching GST details', 'message' => $e->getMessage()];
        }
    }

    public function validateGst(Request $request)
    {
        $gstin = $request->gstNumber;
        if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gstin)) {
            return response()->json(['isValid' => false]);
        }

        $gstin = strtoupper($gstin);
        $codePoints = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $factor = 2;
        $sum = 0;
        $mod = 36;

        for ($i = strlen($gstin) - 2; $i >= 0; $i--) {
            $codePoint = strpos($codePoints, $gstin[$i]);
            $digit = $factor * $codePoint;

            $factor = ($factor == 2) ? 1 : 2;

            $add = (int)($digit / $mod) + ($digit % $mod);
            $sum += $add;
        }

        $checksum = (36 - ($sum % 36)) % 36;
        $expectedDigit = $codePoints[$checksum];

        $flag = $gstin[14] === $expectedDigit;
        $duplicateCheck = Business::where('b_gst_no', $gstin)->exists();

        if ($flag && !$duplicateCheck) {
            $businessDetails = $this->fetchGstDetails($gstin);
        } else {
            $businessDetails = [];
        }
        return response()->json(['isValid' => $flag, 'isDuplicate' => $duplicateCheck, 'businessDetails' => $businessDetails]);
    }

    public function saveBusiness(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'business_category' => 'required|string|exists:master_table,m_id',
                'business_type'     => 'required|string|exists:master_table,m_id',
                'owner_name'             => 'required|string|max:255',
                'business_name'     => 'required|string|max:255',
                'business_phone_number'            => 'required|digits:10',
                'business_zip_code'              => 'required|digits:6',
                // 'business_gstin_number'              => 'required|digits:15',
                'business_password' => 'required|string|min:8',
                // 'business_confirm_password'         => 'required|string|min:8',
                'business_address_location'         => 'required|string|max:255',
                'business_address_longitude'          => 'required|string|max:100',
                'business_address_latitude'          => 'required|string|max:100',
                'image' => 'nullable',
                'image.*' => 'nullable|file|mimes:jpg,png,jpeg|max:2048', // Validate each file in the array
            ],
            [
                'business_category.required' => 'Business category is required.',
                'business_category.exists'   => 'Selected business category does not exist.',
                'business_type.required'     => 'Business type is required.',
                'business_type.exists'       => 'Selected business type does not exist.',
                'owner_name.required'       => 'Administrator is required.',
                'business_name.required'     => 'Business Name is required.',
                'business_phone_number.required'            => 'Phone number is required.',
                'business_zip_code.required'              => 'Zip Code is required.',
                'business_phone_number.digits'            => 'Phone number should be 10 digits.',
                'business_gstin_number.required'              => 'GSTIN Number is required.',
                'business_gstin_number.size'              => 'GSTIN Number should be 15 digits.',
                'business_password.required'         => 'Password is required.',
                'business_confirm_password.required'         => 'Confirm Password is required.',
                'business_address_location.required' => 'Business address field is required.',
                'business_address_longitude.required'          => 'Longitude field is required.',
                'business_address_latitude.required'          => 'Latitude field is required.',
                'image.*.mimes' => 'Only .jpg and .png file formats are allowed.',
                'image.*.max' => 'Each image may not be larger than 2MB.',

            ],
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $business_unique_id = '';
        $data = Business::get()->last();
        if ($data) {
            $business_unique_id = $data->b_unique_id;
        }

        $business_id = CentralLogics::alpha_numeric_generator(4, 'FH', '', $business_unique_id);

        // Get the uploaded image file
        $imageUrl = NULL;
        if ($request->image != '' && $request->image != NULL && $request->image != []) {
            if (env('STORE_ON_S3')) {
                $bucket = 'fixhr-uploads';
                $originalName = $request->image->getClientOriginalName();
                $imageUniqueName = str_replace(' ', '-', $originalName) . '-' . time(); // Replace spaces with hyphens
                $imagePath = 'BusinessLogo/' . $business_id . '/' . $imageUniqueName;
                $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $request->image);
                if ($uploadResult['status']) {
                    $imageUrl = $uploadResult['ObjectURL'];
                }
            } else {
                $imageUrl = $request->file('image') ? CommonUtils::uploadFiles($request, 'image', 'BusinessLogo/' . $business_id, ['prefix' => 'Logo']) : null;
                $imageUrl = isset($imageUrl[0]) ? url($imageUrl[0]) : NULL;
            }
        }

        $business = Business::create([
            'b_unique_id'    => $business_id,
            'b_category_id'  => $request->business_category,
            'b_type_id'      => $request->business_type,
            'b_name'         => $request->business_name,
            'b_gst_no'       => $request->business_gstin_number,
            // 'b_pan_no'       => $request->pan,
            'b_pin_code'     => $request->business_zip_code,
            'b_address'      => $request->business_address_location,
            'b_logo' => $imageUrl,
            'b_is_verified'  => 1,
            'b_status'       => 1,
            'b_longitude' => $request->business_address_longitude,
            'b_latitude' => $request->business_address_latitude,
            'b_emp_code_type' => 191,
        ]);

        $demoPlan = Plan::findOrFail(5);
        $billing_cycle = $demoPlan->billing_cycle;
        if ($billing_cycle == 'yearly') {
            $endDate = Carbon::now()->addYear()->format('Y-m-d');
        } else if ($billing_cycle == 'monthly') {
            $endDate = Carbon::now()->addMonth()->format('Y-m-d');
        }

        $subscription = Subscription::create([
            'business_id' => $business->b_id,
            'plan_id' => 5,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => $endDate,
            'trial_ends_at' => $endDate,
            'status' => 'demo',
            'price_slab_id' => 1,
            'price_per_user' => 80,
            'billing_cycle' => "monthly",
        ]);

        // Import metro cities from master table
        $this->importMetroCitiesForBusiness($business->b_id);

        $branch = Branch::create([
            'br_b_id' => $business->b_id,
            'br_name' => 'Head Quarter',
            'br_email' => $request->session()->get('firstEmail'),
            'br_is_active' => 1,
            'br_address' => $request->business_address_location,
            'br_longitude' => $request->business_address_longitude,
            'br_latitude' => $request->business_address_latitude,
        ]);

        $fullNameParts = explode(' ', $request->owner_name, 3);

        if (count($fullNameParts) === 3) {
            $emp_fname = $fullNameParts[0];
            $emp_mname = $fullNameParts[1];
            $emp_lname = $fullNameParts[2];
        } else if (count($fullNameParts) === 2) {
            $emp_fname = $fullNameParts[0];
            $emp_lname = $fullNameParts[1];
        } else {
            $emp_fname = $fullNameParts[0];
        }
        $employee = Employee::create([
            'emp_email' => $request->session()->get('firstEmail'),
            'emp_b_id' => $business->b_id,
            'emp_fname' => $emp_fname,
            'emp_mname' => isset($emp_mname) ? $emp_mname : '',
            'emp_lname' => isset($emp_lname) ? $emp_lname : '',
            'emp_full_name' => $request->owner_name,
            'emp_phone' => $request->business_phone_number,
            'emp_password' => Hash::make($request->business_password),
            'emp_br_id' => $branch->br_id,
            'emp_grade_id' => 1,
            'emp_d_id' => 1,
            'emp_dg_id' => 1,
            'emp_role_id' => 1,
            'emp_status' => 71
        ]);

        $admin = Admin::where('a_email', Session()->get('firstEmail'))->first();

        if ($admin->delete() && $employee) {
            $business->update([
                'b_emp_id' => $employee->emp_id,
            ]);
            Auth::login($employee);
        }

        if ($business) {
            // Fetch all menus where 'menu_sub_status' is 0 and 'menu_route' is not '#'
            $menus = Menu::where('menu_sub_status', 0)->where('menu_route', '!=', '#')->get();

            $permissions = [];

            foreach ($menus as $menu) {
                $permissions[$menu->menu_id] = [
                    'create' => 'on',
                    'read' => 'on',
                    'update' => 'on',
                    'delete' => 'on'
                ];
            }

            // Convert the permissions array to JSON format
            $rhpPermissions = json_encode($permissions);

            // Create the RolesHasPermission entry
            RolesHasPermission::create([
                'rhp_role_id'     => 1,
                'rhp_b_id'        => $business->b_id,
                'rhp_permissions' => $rhpPermissions,
            ]);

            // Fetch all app menus where 'menu_sub_status' is 0 and 'menu_route' is not '#'
            $appMenus = AppMenu::where('menu_sub_status', 0)->where('menu_route', '!=', '#')->get();

            $permissions = [];

            foreach ($appMenus as $appMenu) {
                $permissions[$appMenu->menu_id] = [
                    'create' => 'on',
                    'read' => 'on',
                    'update' => 'on',
                    'delete' => 'on'
                ];
            }

            // Convert the permissions array to JSON format
            $appRhpPermissions = json_encode($permissions);

            // Create the RolesHasPermission entry
            AppRolesHasPermission::create([
                'rhp_role_id'     => 1,
                'rhp_b_id'        => $business->b_id,
                'rhp_permissions' => $appRhpPermissions,
            ]);

            // Alert::success('', "Your Business Account Has Been Created Successfully")->autoClose(3000);
            // return redirect()->route('account.settings'); // redirect('/dashboard');
            return response()->json(['success' => 'Your Business Account Has Been Created Successfully!']);
        } else {
            return response()->json(['success' => 'Business Not Created \n Please Check Your Details!']);

            // Alert::info('', "Business Not Created \n Please Check Your Details!")->autoClose(3000);
            // return back();
        }
    }

    public function changeBusinessPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
        ]);

        if ($request->current_password == $request->new_password) {
            return response()->json(['error' => 'Current password and new password cannot same']);
        }
        if ($request->new_password != $request->new_password_confirmation) {
            return response()->json(['error' => 'New password and confirm password should be same']);
        }
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->emp_password)) {
            return response()->json(['error' => 'Current password is incorrect']);
        }

        $user->emp_password = Hash::make($request->new_password);
        $user->save();
        Auth::logout();
        return response()->json([
            'success' => 'Password changed successfully. Please login again.',
            'redirect_url' => route('login')
        ]);
    }

    /**
     * Import metro cities from master table for a new business
     *
     * @param int $businessId
     * @return void
     */
    private function importMetroCitiesForBusiness($businessId)
    {
        // Get all metro cities from master table
        $metroCities = MasterTable::where('m_group', 'METRO_CITIES')->get();

        foreach ($metroCities as $metroCity) {
            // Check if city already exists for this business
            $existingCity = TadaMetroCity::where('ctm_b_id', $businessId)
                ->where('ctm_ct_address', $metroCity->m_name)
                ->first();

            // Only create if it doesn't exist
            if (!$existingCity) {
                TadaMetroCity::create([
                    'ctm_b_id' => $businessId,
                    'ctm_ct_address' => $metroCity->m_name,
                    'ctm_longitude' => 0.0, // Default longitude, can be updated later
                    'ctm_latitude' => 0.0,  // Default latitude, can be updated later
                ]);
            }
        }
    }
}
