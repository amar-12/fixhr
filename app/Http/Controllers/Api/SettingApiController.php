<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingApiController extends Controller
{
    public function toggleNotification(Request $request): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'enabled' => ['required', 'integer', 'in:0,1'],
        ]);

        try {
            $enabled = (int) $request->enabled;
            $type    = $request->type;

            if ($type === 'REMINDER') {
                $user->emp_is_reminder_enabled = $enabled;
                $label = 'Reminder';
            } else {
                $user->emp_is_notification_enabled = $enabled;
                $label = 'Notification';
            }
            
            $saved = $user->save();
            if (!$saved) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Failed to update setting',
                ], 500);
            }

            return response()->json([
                'status'  => true,
                'result'  => true,
                'message' => $enabled
                    ? "{$label} enabled successfully"
                    : "{$label} disabled successfully",
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}


