<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMode;
use Illuminate\Support\Facades\Auth;

class PaymentModeController extends Controller
{
    // Create new payment mode
    public function save(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $validated = $request->validate([
            'payment_mode' => 'required|string',
        ]);

        // Check if record exists
        $paymentMode = PaymentMode::where('pm_b_id', $businessId)->first();

        if ($paymentMode) {
            // Update
            $paymentMode->update([
                'pm_mode' => $validated['payment_mode']
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Payment mode updated successfully!',
                'id'      => $paymentMode->pm_id
            ]);
        } else {
            // Create
            $paymentMode = PaymentMode::create([
                'pm_b_id' => $businessId,
                'pm_mode' => $validated['payment_mode'],
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Payment mode created successfully!',
                'id'      => $paymentMode->pm_id
            ]);
        }
    }
}
