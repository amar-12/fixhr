<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppNotification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationApiController extends Controller
{
    /**
     * Fetch notifications for the authenticated user (paginated).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Pagination parameters
        $page = $request->input('page', 1);
        $limit = $request->input('limit', $request->input('per_page', 15));
        
        // Date filter parameters
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        
        // Base query for notifications
        $notificationsQuery = AppNotification::where('user_id', $user->emp_id);
        
        // Apply date filter to notifications query only (not to counts)
        if ($fromDate || $toDate) {
            $fromDate = $fromDate ? Carbon::parse($fromDate)->startOfDay() : null;
            $toDate = $toDate ? Carbon::parse($toDate)->endOfDay() : Carbon::now()->endOfDay();
            
            if ($fromDate && $toDate) {
                $notificationsQuery->whereBetween('created_at', [$fromDate, $toDate]);
            } elseif ($fromDate) {
                $notificationsQuery->where('created_at', '>=', $fromDate);
            } elseif ($toDate) {
                $notificationsQuery->where('created_at', '<=', $toDate);
            }
        }
        
        // Get paginated notifications
        $notifications = $notificationsQuery
            ->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
        
        return response()->json([
            'result' => 
                [
                    'data' => $notifications->items(),
                    'pagination' => [
                     'total' => $notifications->total(),
                    'count' => $notifications->count(),
                    'per_page' => $notifications->perPage(),
                    'current_page' => $notifications->currentPage(),
                    'last_pages' => $notifications->lastPage(),
                    ]
                
            ],
            'status' => true,
            // 'message' => 'Notifications fetched successfully.'
        ]);
    }

    /**
     * Mark a notification as read for the authenticated user.
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = AppNotification::where('id', $id)
            ->where('user_id', $user->emp_id)
            ->first();
        if (!$notification) {
            return response()->json([
                'result' => [],
                'status' => false,
                'message' => 'Notification not found.'
            ], 404);
        }
        $notification->is_read = true;
        $notification->save();
        return response()->json([
            'result' => [$notification],
            'status' => true,
            'message' => 'Notification marked as read.'
        ]);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();

        $ids = $request->input('ids');
        $type = $request->input('type');

        $hasIds = is_array($ids) && count($ids) > 0;

        $query = AppNotification::where('user_id', $user->emp_id);

        if ($hasIds) {
            $query->whereIn('id', $ids);
        }

        if ($type === 'is_read') {
            $query->where('is_read', false);
            $affected = $query->update(['is_read' => true]);

            $message = $hasIds
                ? 'Selected notifications marked as read.'
                : 'All notifications marked as read.';

        } else {
            $affected = $query->delete();
            $message = $hasIds
                ? 'Selected notifications deleted.'
                : 'All notifications deleted.';
        }

        return response()->json([
            'status'   => true,
            'affected' => $affected,
            'message'  => $message
        ]);
    }
    
    public function deleteNotification($id)
    {
        $user = Auth::user();

        $notification = AppNotification::where('id', $id)
            ->where('user_id', $user->emp_id)
            ->first();

        if (!$notification) {
            return response()->json([
                'result' => [],
                'status' => false,
                'message' => 'Notification not found.'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'result' => true,
            'status' => true,
            'message' => 'Notification deleted successfully.'
        ]);
    }
}