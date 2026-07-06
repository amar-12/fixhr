<?php

namespace App\Http\Controllers\Web\Auth;

use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Mail\AuthMailer;
use App\Models\Employee;
use App\Models\Menu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use RealRashid\SweetAlert\Facades\Alert;
use PragmaRX\Google2FA\Google2FA;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function checkUser(Request $request)
    {
        try {
            // Rate limiting for user check
            if (Session::get('user_check_attempts', 0) >= 5) {
                $lastAttempt = Session::get('user_check_last_attempt', 0);
                // if (time() - $lastAttempt < 300) { // 5 minutes lockout
                //     return response()->json(['data' => '', 'status' => false, 'message' => 'Too many attempts. Please try again in 5 minutes.']);
                   $secondsLeft = 300 - (time() - $lastAttempt);
                if ($secondsLeft > 0) {
                    return response()->json([
                        'data' => '',
                        'status' => false,
                        'message' => 'Too many attempts. Please try again in ' . ceil($secondsLeft/60) . ' minutes.',
                        'seconds_left' => $secondsLeft
                    ]);
                } else {
                    Session::forget(['user_check_attempts', 'user_check_last_attempt']);
                }
            }

            $user = Employee::select('emp_id', 'emp_email', 'emp_full_name', 'emp_status', 'emp_google2fa_secret', 'emp_google2fa_enabled_at')
                ->where('emp_email', $request->user)
                ->first();
                
            if (isset($user)) {
                if($user->emp_status == 72){
                    return response()->json(['data' => $user, 'status' => false, 'message' => 'Error: Your Account is Deactivated.']);
                }else{
                    // Check if 2FA is enabled for this user
                    $has2FA = !empty($user->emp_google2fa_secret) && !empty($user->emp_google2fa_enabled_at);
                    
                    \Log::info('User check successful', [
                        'email' => $user->emp_email,
                        'has2FA' => $has2FA,
                        'status' => $user->emp_status,
                        'ip' => $request->ip()
                    ]);
                    
                    // Reset rate limiting on successful check
                    Session::forget(['user_check_attempts', 'user_check_last_attempt']);
                    
                    return response()->json([
                        'data' => $user, 
                        'status' => true, 
                        'message' => '',
                        'has2FA' => $has2FA,
                        'authMethod' => $has2FA ? '2fa' : 'email'
                    ]);
                }
            } else {
                // Increment rate limiting counter
                $attempts = Session::get('user_check_attempts', 0) + 1;
                Session::put('user_check_attempts', $attempts);
                Session::put('user_check_last_attempt', time());
                
                \Log::warning('User not found', [
                    'email' => $request->user,
                    'ip' => $request->ip(),
                    'attempts' => $attempts
                ]);
                return response()->json(['data' => $user ?? '', 'status' => false, 'message' => 'Invalid User.']);
            }
        } catch (\Exception $e) {
            \Log::error('Error in checkUser: ' . $e->getMessage());
            return response()->json(['data' => '', 'status' => false, 'message' => 'System error occurred. Please try again.']);
        }
    }

    public function login_otp(Request $request)
    {
        $user = Employee::with('fh_business', 'fh_role')->where('emp_email', $request->email)->first();
        
        // Check if this is an AJAX request
        if ($request->ajax()) {
            if (!$user) {
                return response()->json(['status' => false, 'message' => 'Email not found. Please register your business first.']);
            }
            
            if ($user->emp_status != 71) {
                return response()->json(['status' => false, 'message' => 'Your account is deactivated.']);
            }
            
            // Generate and send OTP
            $otp = rand(100000, 999999);
            $details = [
                'name' => $user->emp_full_name ?? 'User',
                'otp' => $otp,
            ];
            
            try {
                \Log::info('Attempting to send OTP', [
                    'email' => $request->email,
                    'user_id' => $user->emp_id,
                    'otp' => $otp
                ]);
                
                $sendMail = CentralLogics::send_mail($request->email, new AuthMailer($details));
                
                \Log::info('OTP Send Result', [
                    'email' => $request->email,
                    'success' => $sendMail
                ]);
                
                if ($sendMail) {
                    $user->update(['emp_otp' => $otp, 'emp_otp_created_at' => Carbon::now()]);
                    return response()->json([
                        'status' => true, 
                        'message' => 'OTP has been sent successfully to your registered email.'
                    ]);
                } else {
                    return response()->json([
                        'status' => false, 
                        'message' => 'Failed to send OTP. Please try again.'
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('OTP Send Error: ' . $e->getMessage());
                return response()->json([
                    'status' => false, 
                    'message' => 'Error sending OTP. Please try again.'
                ]);
            }
        }
        
        // Handle non-AJAX requests (form submission)
        if ($request->password && $user) {
            if (isset($user) && Hash::check($request->password, $user->emp_password)) {
                // Check if 2FA is required
                if (!empty($user->emp_google2fa_secret) && !empty($user->emp_google2fa_enabled_at)) {
                    // 2FA is enabled, check if 2FA code was provided
                    if (!$request->password2FACode) {
                        Alert::error('', '2FA code is required for this account')->autoClose(3000);
                        return redirect('/login');
                    }
                    
                    // Verify 2FA code
                    try {
                        $google2fa = new \PragmaRX\Google2FA\Google2FA();
                        $valid = $google2fa->verifyKey($user->emp_google2fa_secret, $request->password2FACode, 2);
                        
                        if (!$valid) {
                            Alert::error('', 'Invalid 2FA code')->autoClose(3000);
                            return redirect('/login');
                        }
                    } catch (\Exception $e) {
                        \Log::error('2FA Verification Error in login_otp: ' . $e->getMessage());
                        Alert::error('', '2FA verification failed')->autoClose(3000);
                        return redirect('/login');
                    }
                }
                
                // All validations passed, proceed with login
                if (isset($user->fh_role) && isset($user->fh_business)) {
                    Auth::login($user);
                }
                Alert::success('', 'Login Successfully')->autoClose(3000);

                $dashboardId = $user->fh_business->b_dashboard_id ?? null;

                if ($dashboardId) {
                    $menu = Menu::where('menu_id', $dashboardId)->first();
                    if ($menu) {
                        return redirect($menu->menu_route);
                    }
                }

                return redirect('/attendance-dashboard');
            } else {
                Alert::error('', 'Either username or password is invalid')->autoClose(3000);
                return redirect('/login');
            }
        } else if ($user) {
            $otp = rand(100000, 999999);
            $details = [
                'name' => $user->emp_full_name ?? 'User',
                'otp' => $otp,
            ];
            $sendMail = CentralLogics::send_mail($request->email, new AuthMailer($details));
            if ($sendMail) {
                $user->update(['emp_otp' => $otp, 'emp_otp_created_at' => Carbon::now()]);
            }
            Alert::success('', 'OTP has been Sent Successfully to Your Registered Email Id')->autoClose(3000);
            return view('auth.admin.otp');
        } else {
            Alert::warning('', "Email Id not Found Kindly Register Your Business First")->autoClose(3000);
            return redirect('/login');
        }
    }

    public function checkOTP(Request $request)
    {
        try {
            // Rate limiting for OTP verification
            // if (Session::get('otp_attempts', 0) >= 3) {
            //     $lastAttempt = Session::get('otp_last_attempt', 0);
            //     if (time() - $lastAttempt < 600) { // 10 minutes lockout
            //         return response()->json(['data' => '', 'status' => false, 'message' => 'Too many OTP attempts. Please try again in 10 minutes.']);
                 // Rate limiting for OTP verification with real-time timer feedback
            $maxAttempts = 3;
            $lockoutSeconds = 600; // 10 minutes

            $attempts = Session::get('otp_attempts', 0);
            $lastAttempt = Session::get('otp_last_attempt', 0);

            if ($attempts >= $maxAttempts) {
                $secondsSinceLast = time() - $lastAttempt;
                if ($secondsSinceLast < $lockoutSeconds) {
                    $secondsLeft = $lockoutSeconds - $secondsSinceLast;
                    $minutes = floor($secondsLeft / 60);
                    $seconds = $secondsLeft % 60;
                    $timerString = sprintf('%02d:%02d', $minutes, $seconds);
                    return response()->json([
                        'data' => '',
                        'status' => false,
                        'message' => "Too many OTP attempts. Please try again in $minutes minute(s) and $seconds second(s).",
                        'lockout_seconds' => $secondsLeft,
                        'lockout_timer' => $timerString
                    ]);
                } else {
                    Session::forget(['otp_attempts', 'otp_last_attempt']);
                }
            }

            $user = Employee::with('fh_business')->where('emp_status', 71)->where('emp_email', $request->user)->where('emp_otp', $request->otp)->first();
            if (isset($user) && ($user->fh_business || $user->emp_role_id != null)) {
                $now = Carbon::now()->setTimezone('Asia/Kolkata');
                $otpCreatedAt = Carbon::parse($user->emp_otp_created_at)->setTimezone('Asia/Kolkata');
                $minutesDifference = $otpCreatedAt->diffInMinutes($now);
                if ($minutesDifference > 10) {
                    $user->emp_otp = null;
                    $user->save();
                    \Log::warning('OTP expired', [
                        'email' => $request->user, 
                        'minutes' => $minutesDifference,
                        'ip' => $request->ip()
                    ]);
                    return response()->json(['data' => $user ?? '', 'status' => false, 'message' => 'Error: OTP has expired']);
                }else{
                    Session::put('email', $request->user);
                    // Reset rate limiting on successful verification
                    Session::forget(['otp_attempts', 'otp_last_attempt']);
                    
                    \Log::info('OTP verified successfully', [
                        'email' => $request->user,
                        'ip' => $request->ip()
                    ]);
                    return response()->json(['data' => $user, 'status' => true, 'message' => '']);
                }
            } else {
                // Increment rate limiting counter
                $attempts = Session::get('otp_attempts', 0) + 1;
                Session::put('otp_attempts', $attempts);
                Session::put('otp_last_attempt', time());
                
                \Log::warning('Invalid OTP', [
                    'email' => $request->user, 
                    'otp' => $request->otp,
                    'ip' => $request->ip(),
                    'attempts' => $attempts
                ]);
                return response()->json(['data' => $user ?? '', 'status' => false, 'message' => 'Error: Invalid OTP']);
            }
        } catch (\Exception $e) {
            \Log::error('Error in checkOTP: ' . $e->getMessage());
            return response()->json(['data' => '', 'status' => false, 'message' => 'System error occurred. Please try again.']);
        }
    }

    public function submit(Request $request)
    {
        $email = Session::get('email');
        $otp = $request->otp;
        $twoFACode = $request->twoFACode;

        // If email is not in session but we have OTP, try to get email from the request
        if (!$email && $otp) {
            // For OTP login, we need to find the user by OTP
            $user = Employee::with('fh_business', 'fh_role', 'fh_attendance_policy')
                ->where('emp_otp', $otp)
                ->where('emp_status', 71)
                ->first();
                
            if ($user) {
                $email = $user->emp_email;
                Session::put('email', $email);
            }
        }

        if (isset($email)) {
            $user = Employee::with('fh_business', 'fh_role', 'fh_attendance_policy')
                ->where('emp_email', $email)
                ->first();

            if (isset($user) && $user != null) {
                // Check if this is an OTP login or 2FA login
                if (isset($otp)) {
                    // OTP login
                    if ($user->emp_otp == $otp) {
                        // Clear OTP after successful verification
                        $user->update(['emp_otp' => null, 'emp_otp_created_at' => null]);
                        Auth::login($user);
                        Alert::success('', "Your OTP has been Verified Successfully")->autoClose(3000);
                    } else {
                        Alert::error('', "Invalid OTP")->autoClose(3000);
                        return redirect('/login');
                    }
                } elseif (isset($twoFACode)) {
                    // 2FA login
                    try {
                        // Check if user has 2FA enabled
                        if (empty($user->emp_google2fa_secret) || empty($user->emp_google2fa_enabled_at)) {
                            Alert::error('', "2FA is not enabled for this account")->autoClose(3000);
                            return redirect('/login');
                        }
                        
                        // Use Google2FA library for proper verification
                        $google2fa = new \PragmaRX\Google2FA\Google2FA();
                        $valid = $google2fa->verifyKey($user->emp_google2fa_secret, $twoFACode, 2);
                        
                        if ($valid) {
                            Auth::login($user);
                            Alert::success('', "Your 2FA has been Verified Successfully")->autoClose(3000);
                        } else {
                            Alert::error('', "Invalid 2FA Code")->autoClose(3000);
                            return redirect('/login');
                        }
                    } catch (\Exception $e) {
                        \Log::error('2FA Login Error: ' . $e->getMessage());
                        Alert::error('', "2FA verification failed")->autoClose(3000);
                        return redirect('/login');
                    }
                } else {
                    Alert::error('', "Authentication required")->autoClose(3000);
                    return redirect('/login');
                }

                $dashboardId = $user->fh_business->b_dashboard_id ?? null;

                if ($dashboardId) {
                    $menu = Menu::where('menu_id', $dashboardId)->first();
                    if ($menu) {
                        return redirect($menu->menu_route);
                    }
                }

                return redirect('/attendance-dashboard');
            }
        }

        return redirect('/login');
    }

    public function newForgetPasswordPage()
    {
        return view('auth.admin.forget-password');
    }

    public function newForgetPassword(Request $request)
    {
        $user = Employee::where('emp_email', $request->email)->first();
        if ($user) {
            $encryptPassword = Hash::make($request->password);
            Employee::where('emp_email', $request->email)->update([
                'emp_password' => $encryptPassword,
            ]);
            return response()->json(['data' => $user, 'status' => true]);
        } else {
            return response()->json(['data' => $user ?? '', 'status' => false]);
        }
    }

    public function checkUserPassword(Request $request)
    {
        $email = $request->email;
        $password = $request->password;
        $user = Employee::where('emp_email', $email)->first();

        if ($user) {
            $validate = Hash::check($password, $user->emp_password);
            if($user->emp_status != 71){
                return response()->json(['data' => $user, 'status' => false, 'message' => 'Error: Your Account is Deactivated.']);
            }else if ($validate) {
                // Check if user has 2FA enabled
                $has2FA = !empty($user->emp_google2fa_secret) && !empty($user->emp_google2fa_enabled_at);
                
                return response()->json([
                    'data' => $user, 
                    'status' => true, 
                    'message' => 'Successfully Login',
                    'has2FA' => $has2FA
                ]);
            } else {
                return response()->json(['data' => $user, 'status' => false, 'message' => 'Error: Invalid username or password.']);
            }
        } else {
            return response()->json(['data' => $user, 'status' => false, 'message' => 'Error: Invalid User.']);
        }
    }

    public function verifyPassword2FA(Request $request)
    {
        $email = $request->email;
        $password = $request->password;
        $twoFACode = $request->twoFACode;
        
        // Rate limiting for 2FA verification
        // if (Session::get('2fa_attempts', 0) >= 3) {
        //     $lastAttempt = Session::get('2fa_last_attempt', 0);
        //     if (time() - $lastAttempt < 900) { // 15 minutes lockout
        //         return response()->json(['status' => false, 'message' => 'Too many 2FA attempts. Please try again in 15 minutes.']);
           // Rate limiting for 2FA verification with real-time timer for more than 3 attempts
        $maxAttempts = 3;
        $lockoutSeconds = 900; // 15 minutes

        $attempts = session()->get('2fa_attempts', 0);
        $lastAttempt = session()->get('2fa_last_attempt', 0);

        if ($attempts >= $maxAttempts) {
            $secondsSinceLast = time() - $lastAttempt;
            if ($secondsSinceLast < $lockoutSeconds) {
                $secondsLeft = $lockoutSeconds - $secondsSinceLast;
                // Format as mm:ss for user feedback
                $minutes = floor($secondsLeft / 60);
                $seconds = $secondsLeft % 60;
                $timerString = sprintf('%02d:%02d', $minutes, $seconds);
                return response()->json([
                    'status' => false,
                    'message' => "Too many 2FA attempts. Please try again in ",
                    'lockout_seconds' => $secondsLeft,
                    'lockout_timer' => $timerString
                ]);
            } else {
                // Session::forget(['2fa_attempts', '2fa_last_attempt']);
                  // Reset attempts after lockout period
                session()->forget(['2fa_attempts', '2fa_last_attempt']);
            }
        }
        
        \Log::info('Password + 2FA Verification Request', [
            'email' => $email,
            'twoFACode' => $twoFACode,
            'ip' => $request->ip()
        ]);
        
        $user = Employee::where('emp_email', $email)->first();
        
        if (!$user) {
            \Log::warning('Password + 2FA Verification: User not found', ['email' => $email, 'ip' => $request->ip()]);
            return response()->json(['status' => false, 'message' => 'Error: Invalid user.']);
        }
        
        if ($user->emp_status != 71) {
            \Log::warning('Password + 2FA Verification: Account deactivated', ['email' => $email, 'status' => $user->emp_status, 'ip' => $request->ip()]);
            return response()->json(['status' => false, 'message' => 'Error: Your Account is Deactivated.']);
        }
        
        // Verify password first
        if (!Hash::check($password, $user->emp_password)) {
            return response()->json(['status' => false, 'message' => 'Error: Invalid password.']);
        }
        
        // Check if user has 2FA enabled
        if (empty($user->emp_google2fa_secret) || empty($user->emp_google2fa_enabled_at)) {
            \Log::warning('Password + 2FA Verification: 2FA not enabled', [
                'email' => $email,
                'has_secret' => !empty($user->emp_google2fa_secret),
                'has_enabled_at' => !empty($user->emp_google2fa_enabled_at),
                'ip' => $request->ip()
            ]);
            
            return response()->json(['status' => false, 'message' => 'Error: 2FA is not enabled for this account.']);
        }
        
        // Use Google2FA library for proper verification
        try {
            $google2fa = new \PragmaRX\Google2FA\Google2FA();
            $valid = $google2fa->verifyKey($user->emp_google2fa_secret, $twoFACode, 2);
            
            \Log::info('Password + 2FA Verification Result', [
                'email' => $email,
                'valid' => $valid,
                'secret_length' => strlen($user->emp_google2fa_secret),
                'ip' => $request->ip()
            ]);
            
            if ($valid) {
                // Reset rate limiting on successful verification
                Session::forget(['2fa_attempts', '2fa_last_attempt']);
                return response()->json(['status' => true, 'message' => '2FA verification successful']);
            } else {
                // Increment rate limiting counter
                $attempts = Session::get('2fa_attempts', 0) + 1;
                Session::put('2fa_attempts', $attempts);
                Session::put('2fa_last_attempt', time());
                
                return response()->json(['status' => false, 'message' => 'Error: Invalid 2FA code.']);
            }
        } catch (\Exception $e) {
            \Log::error('Password + 2FA Verification Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Error: 2FA verification failed.']);
        }
    }

    public function verify2FA(Request $request)
    {
        $email = $request->email;
        $twoFACode = $request->twoFACode;
        
        // Rate limiting for 2FA verification
        // if (Session::get('2fa_attempts', 0) >= 3) {
        //     $lastAttempt = Session::get('2fa_last_attempt', 0);
        //     if (time() - $lastAttempt < 900) { // 15 minutes lockout
        //         return response()->json(['status' => false, 'message' => 'Too many 2FA attempts. Please try again in 15 minutes.']);
                // Rate limiting for 2FA verification with real-time timer feedback
        $maxAttempts = 3;
        $lockoutSeconds = 900; // 15 minutes

        $attempts = Session::get('2fa_attempts', 0);
        $lastAttempt = Session::get('2fa_last_attempt', 0);

        if ($attempts >= $maxAttempts) {
            $secondsSinceLast = time() - $lastAttempt;
            if ($secondsSinceLast < $lockoutSeconds) {
                $secondsLeft = $lockoutSeconds - $secondsSinceLast;
                // Format as mm:ss for user feedback
                $minutes = floor($secondsLeft / 60);
                $seconds = $secondsLeft % 60;
                $timerString = sprintf('%02d:%02d', $minutes, $seconds);
                return response()->json([
                    'status' => false,
                    'message' => "Too many 2FA attempts . Please try again in ",
                    'lockout_seconds' => $secondsLeft,
                    'lockout_timer' => $timerString
                ]);
            } else {
                Session::forget(['2fa_attempts', '2fa_last_attempt']);
            }
        }
        
        \Log::info('2FA Verification Request', [
            'email' => $email,
            'twoFACode' => $twoFACode,
            'ip' => $request->ip()
        ]);
        
        $user = Employee::where('emp_email', $email)->first();
        
        if (!$user) {
            \Log::warning('2FA Verification: User not found', ['email' => $email, 'ip' => $request->ip()]);
            return response()->json(['status' => false, 'message' => 'Error: Invalid user.']);
        }
        
        if ($user->emp_status != 71) {
            \Log::warning('2FA Verification: Account deactivated', ['email' => $email, 'status' => $user->emp_status, 'ip' => $request->ip()]);
            return response()->json(['status' => false, 'message' => 'Error: Your Account is Deactivated.']);
        }
        
        // Check if user has 2FA enabled
        if (empty($user->emp_google2fa_secret) || empty($user->emp_google2fa_enabled_at)) {
            \Log::warning('2FA Verification: 2FA not enabled', [
                'email' => $email,
                'has_secret' => !empty($user->emp_google2fa_secret),
                'has_enabled_at' => !empty($user->emp_google2fa_enabled_at),
                'ip' => $request->ip()
            ]);
            
            return response()->json(['status' => false, 'message' => 'Error: 2FA is not enabled for this account.']);
        }
        
        // Use Google2FA library for proper verification
        try {
            $google2fa = new \PragmaRX\Google2FA\Google2FA();
            $valid = $google2fa->verifyKey($user->emp_google2fa_secret, $twoFACode, 2);
            
            \Log::info('2FA Verification Result', [
                'email' => $email,
                'valid' => $valid,
                'secret_length' => strlen($user->emp_google2fa_secret),
                'ip' => $request->ip()
            ]);
            
            if ($valid) {
                // Store email in session for the final login step
                Session::put('email', $email);
                // Reset rate limiting on successful verification
                Session::forget(['2fa_attempts', '2fa_last_attempt']);
                return response()->json(['status' => true, 'message' => '2FA verification successful']);
            } else {
                // Increment rate limiting counter
                $attempts = Session::get('2fa_attempts', 0) + 1;
                Session::put('2fa_attempts', $attempts);
                Session::put('2fa_last_attempt', time());
                
                return response()->json(['status' => false, 'message' => 'Error: Invalid 2FA code.']);
            }
        } catch (\Exception $e) {
            \Log::error('2FA Verification Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Error: 2FA verification failed.']);
        }
    }
}
