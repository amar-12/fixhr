<?php

namespace App\Http\Controllers\Web\Auth;
use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class Employee2FAController extends Controller
{

    public function index()
    {
        $employee = Auth::user();
        $has2FA = !empty($employee->emp_google2fa_secret) && !empty($employee->emp_google2fa_enabled_at);
        $setupTime = $employee->emp_google2fa_enabled_at ?? null;
        $pageTitle = 'Two-Factor Authentication';
        $breadcrumbs = \App\Helpers\CentralLogics::getBreadcrumbs();

        // Add 2FA status (Enabled/Disabled)
        $twoFAStatus = $has2FA ? 'Enabled' : 'Disabled';

        return view('auth.2fa-index', compact('has2FA', 'employee', 'setupTime', 'pageTitle', 'breadcrumbs', 'twoFAStatus'));
    }

    // Show 2FA setup form
    public function showSetupForm(Request $request)
    {
        try {
            $employee = Auth::user();
            
            // Debug logging
            \Log::info('2FA Setup - User: ' . ($employee ? $employee->emp_id : 'null'));
            \Log::info('2FA Setup - Email: ' . ($employee ? $employee->emp_email : 'null'));
            
            if (!$employee) {
                throw new \Exception('User not authenticated');
            }
            
            $google2fa = new Google2FA();

            if (!$employee->emp_google2fa_secret) {
                $secret = $google2fa->generateSecretKey();
                Session::put('2fa_secret', $secret);
            } else {
                $secret = $employee->emp_google2fa_secret;
            }

            $company = config('app.name', 'FixHR');
            $email = $employee->emp_email ?? 'user@example.com'; // Fallback email
            
            \Log::info('2FA Setup - Generating QR for: ' . $email);
            
            $qrImage = $this->generateQRCode($company, $email, $secret);

            // Return JSON for AJAX requests
            if ($request->ajax()) {
                // Test with simple response first
                return response()->json([
                    'success' => true,
                    'secret' => $secret,
                    'qrImage' => $qrImage
                ]);
            }

            return view('auth.2fa-index', compact('secret', 'qrImage', 'employee'));
        } catch (\Exception $e) {
            \Log::error('2FA Setup Error: ' . $e->getMessage());
            \Log::error('2FA Setup Error Stack: ' . $e->getTraceAsString());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating 2FA setup data: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Error generating 2FA setup data. Please try again.']);
        }
    }

    // Save 2FA secret to employee
    public function enable2FA(Request $request)
    {
        try {
            $request->validate([
                'secret' => 'required',
                'otp' => 'required',
            ]);
            $employee = Auth::user();
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($request->input('secret'), $request->input('otp'), 2);
            if ($valid) {
                $employee->emp_google2fa_secret = $request->input('secret');
                $employee->emp_google2fa_enabled_at = now();
                $employee->save();
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => '2FA enabled successfully!'
                    ]);
                }
                return redirect()->route('2fa.index')->with('success', '2FA enabled successfully!');
            } else {
                $errorMessage = 'Invalid OTP code. Please try again.';
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 422);
                }
                return redirect()->back()->withErrors(['otp' => $errorMessage]);
            }
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error enabling 2FA: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->withErrors(['error' => 'Error enabling 2FA. Please try again.']);
        }
    }

    // Change 2FA authenticator
    public function change2FA(Request $request)
    {
        try {
            $request->validate([
                'secret' => 'required',
                'otp' => 'required',
            ]);
            
            $employee = Auth::user();
            $google2fa = new Google2FA();
            
            // Verify the new secret with the provided OTP
            $valid = $google2fa->verifyKey($request->input('secret'), $request->input('otp'), 2);
            
            if ($valid) {
                // Store the new secret and update the enabled timestamp
                $employee->emp_google2fa_secret = $request->input('secret');
                $employee->emp_google2fa_enabled_at = now();
                $employee->save();
                
                // Clear the change secret from session
                Session::forget('2fa_change_secret');
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Authenticator changed successfully!'
                    ]);
                }
                return redirect()->route('2fa.index')->with('success', 'Authenticator changed successfully!');
            } else {
                $errorMessage = 'Invalid OTP code. Please try again.';
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 422);
                }
                return redirect()->back()->withErrors(['otp' => $errorMessage]);
            }
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error changing authenticator: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->withErrors(['error' => 'Error changing authenticator. Please try again.']);
        }
    }

    // Disable 2FA - handles both self-disable and admin-disable
    public function disable2FA(Request $request, $employee_id = null)
    {
        try {
            $currentUser = Auth::user();
            
            // \Log::info('2FA Disable Request', [
            //     'user_id' => $currentUser->emp_id,
            //     'user_role' => $currentUser->emp_role_id,
            //     'employee_id' => $employee_id,
            //     'is_ajax' => $request->ajax()
            // ]);
            
            // If no employee_id provided, user is disabling their own 2FA
            if ($employee_id === null) {
                $employee = $currentUser;
                $isSelfDisable = true;
            } else {
                // Admin is disabling 2FA for another employee
                $employee = \App\Models\Employee::findOrFail($employee_id);
                $isSelfDisable = false;
                
                // Check if user has admin permissions (role_id = 1 is typically admin/superadmin)
                if ($currentUser->emp_role_id != 1) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
                }
                
                // Prevent admin from disabling their own 2FA through this route (they should use self-disable)
                if ($employee_id == $currentUser->emp_id) {
                    return response()->json(['success' => false, 'message' => 'Please use the self-disable option for your own 2FA.'], 400);
                }
                
                // Log the admin action
                \Log::info('2FA disabled for employee ID: ' . $employee_id . ' by admin: ' . $currentUser->emp_id);
            }
            
            // Clear 2FA settings
            $employee->emp_google2fa_secret = null;
            $employee->emp_google2fa_enabled_at = null;
            $employee->save();
            
            // \Log::info('2FA Disabled Successfully', [
            //     'target_employee_id' => $employee->emp_id,
            //     'target_employee_name' => $employee->emp_fname . ' ' . $employee->emp_lname,
            //     'disabled_by_user_id' => $currentUser->emp_id,
            //     'is_self_disable' => $isSelfDisable
            // ]);
            
            // Prepare success message
            if ($isSelfDisable) {
                $message = '2FA disabled successfully.';
            } else {
                $message = '2FA disabled successfully for ' . $employee->emp_fname . ' ' . $employee->emp_lname;
            }
            
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            
            if ($isSelfDisable) {
                return redirect()->route('2fa.index')->with('success', $message);
            } else {
                return redirect()->back()->with('success', $message);
            }
            
        } catch (\Exception $e) {
            \Log::error('Error disabling 2FA: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error disabling 2FA. Please try again.'], 500);
            }
            
            return redirect()->back()->with('error', 'Error disabling 2FA. Please try again.');
        }
    }

    // Show 2FA verification form
    public function showVerifyForm()
    {
        return view('auth.2fa-verify');
    }

    // Verify OTP during login
    public function verify2FA(Request $request)
    {
        $request->validate([
            'otp' => 'required',
        ]);
        $employee = Auth::user();
        $google2fa = new Google2FA();
        $secret = $employee->emp_google2fa_secret;
        $valid = $google2fa->verifyKey($secret, $request->input('otp'));
        if ($valid) {
            Session::put('2fa_passed', true);
            return redirect()->intended('/dashboard');
        } else {
            return redirect()->back()->withErrors(['otp' => 'Invalid OTP code. Please try again.']);
        }
    }

    // Helper: Generate QR code SVG
    private function generateQRCode($company, $email, $secret)
    {
        try {
            $google2fa = new Google2FA();
            $qrContent = $google2fa->getQRCodeUrl($company, $email, $secret);
            $renderer = new ImageRenderer(
                new RendererStyle(200),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            return $writer->writeString($qrContent);
        } catch (\Exception $e) {
            \Log::error('QR Code Generation Error: ' . $e->getMessage());
            throw new \Exception('Failed to generate QR code: ' . $e->getMessage());
        }
    }

    public function showChangeForm(Request $request)
    {
        try {
            $employee = Auth::user();
            
            // Debug logging
            \Log::info('2FA Change - User: ' . ($employee ? $employee->emp_id : 'null'));
            \Log::info('2FA Change - Email: ' . ($employee ? $employee->emp_email : 'null'));
            
            if (!$employee) {
                throw new \Exception('User not authenticated');
            }
            
            if (!$employee->emp_google2fa_secret) {
                throw new \Exception('2FA not enabled for this user');
            }
            
            $google2fa = new Google2FA();
            
            // Generate a new secret for the change process
            $newSecret = $google2fa->generateSecretKey();
            Session::put('2fa_change_secret', $newSecret);

            $company = config('app.name', 'FixHR');
            $email = $employee->emp_email ?? 'user@example.com';
            
            \Log::info('2FA Change - Generating QR for: ' . $email);
            
            $qrImage = $this->generateQRCode($company, $email, $newSecret);

            // Return JSON for AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'secret' => $newSecret,
                    'qrImage' => $qrImage
                ]);
            }

            return view('auth.2fa-index', compact('newSecret', 'qrImage', 'employee'));
        } catch (\Exception $e) {
            \Log::error('2FA Change Error: ' . $e->getMessage());
            \Log::error('2FA Change Error Stack: ' . $e->getTraceAsString());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating 2FA change data: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Error generating 2FA change data. Please try again.']);
        }
    }

   
} 