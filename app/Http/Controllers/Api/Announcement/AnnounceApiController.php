<?php

namespace App\Http\Controllers\Api\Announcement;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use ChandraHemant\HtkcUtils\ReturnHelper;
use ChandraHemant\HtkcUtils\PaginatedResource;
use App\Http\Resources\Announcement\AnnouncementApiResource;
use App\Helpers\NotificationHelper;
use ChandraHemant\HtkcUtils\FirebaseNotification;
use App\Models\Employee;

class AnnounceApiController extends Controller
{
    // public function getRoleList()
    // {
    //     try {
    //         $user = Auth::user();
    //         $businessId = $user->emp_b_id;
    //         // dd($businessId);

    //         $roles = \App\Models\Role::where('role_b_id', $businessId)
    //             ->orderBy('role_name', 'asc')
    //             ->get(['role_id', 'role_name']);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Roles fetched successfully',
    //             'result' => $roles,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function getRoleList()
    {
        try {
            $user = Auth::user();
            $businessId = $user->emp_b_id;

            $roles = \App\Models\Role::where('role_b_id', $businessId)
                ->orderBy('role_name', 'asc')
                ->get(['role_id', 'role_name']);

            // "All" option prepend kar diya
            $roles->prepend([
                'role_id' => 0,
                'role_name' => 'All',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Roles fetched successfully',
                'result' => $roles->values(), // index reset karne ke liye
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeAnnouncement(Request $request)
    {
        $user = Auth::user();
        // dd($request->all());

        // ✅ Validation
        $request->validate([
            'title'     => 'required|string|max:255',
            'message'   => 'required|string',
            'send_to'   => 'nullable|string',
            'role_id'   => 'nullable|integer',
            'category'  => 'nullable|string|max:100',
            'image'     => 'nullable|file|mimes:jpg,jpeg,png,pdf,xlsx,xls,xlsb,doc,docx,txt|max:2048',
        ]);

        // Image Upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image      = $request->file('image');
            $imageName  = time() . '-' . $image->getClientOriginalName();
            $folderPath = public_path('announcements');

            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }

            $image->move($folderPath, $imageName);

            $imagePath = asset("announcements/{$imageName}");
        }

        // Save Announcement
        // dd($request->all());
        $announcement = Announcement::create([
            'ann_b_id'     => $user->emp_b_id,
            'ann_title'    => $request->title,
            'ann_message'  => $request->message,
            'ann_role_id'  => $request->send_to,
            'ann_category' => $request->category,
            'ann_image'    => $imagePath,
            'ann_user_id'  => $user->emp_id,
        ]);


        $responseData = [
            'ann_id'      => $announcement->ann_id,
            'ann_b_id'    => $announcement->ann_b_id,
            'ann_title'   => $announcement->ann_title,
            'ann_message' => $announcement->ann_message,
            'ann_role_id' => $announcement->ann_role_id,
            'ann_category' => $announcement->ann_category,
            'ann_image'   => $announcement->ann_image,
            'ann_user_id'  => $announcement->ann_user_id,
            'created_at'  => $announcement->created_at->toDateTimeString(),
            'updated_at'  => $announcement->updated_at->toDateTimeString(),
        ];


        // Send push notifications and persist in-app notifications
        $title = $request->title;
        $body = $request->message;
        $additionalData = [
            'notification_type' => 'announcement',
            'announcement_id'   => $announcement->ann_id,
            'route'             => '/AnnouncementScreen',
            'image'             => $imagePath,
        ];
        $serviceAccountPath = public_path('fixhr-app-firebase.json');

        $recipientQuery = \App\Models\Employee::where('emp_b_id', $user->emp_b_id)
            ->where('emp_status', 71);

        if (!empty($request->send_to) && is_numeric($request->send_to)) {
            $recipientQuery->where('emp_role_id', (int) $request->send_to);
        }

        // 🔑 Agar "All" selected hai (role_id = 0), to role filter mat lagao
        if (!empty($request->send_to) && is_numeric($request->send_to) && (int)$request->send_to !== 0) {
            $recipientQuery->where('emp_role_id', (int) $request->send_to);
        }


        $recipients = $recipientQuery->get(['emp_id', 'emp_fcm_token']);

        foreach ($recipients as $emp) {
            if (!app()->environment('local') && !empty($emp->emp_fcm_token)) {
                FirebaseNotification::sendPushNotification(
                    $title,
                    $body,
                    $emp->emp_fcm_token,
                    $serviceAccountPath,
                    config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                    $additionalData
                );
            }

        }


        return response()->json([
            'status'  => true,
            'message' => 'Announcement created successfully',
            'data'    => $responseData
        ], 201);
    }

    public function getSentAnnouncements(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $userId     = $user->emp_id;

        $page  = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $query = Announcement::query()
            ->where('ann_b_id', $businessId)
            ->where('ann_user_id', $userId); // ✅ Sirf apne banaye

        // Category filter
        if ($request->has('category')) {
            $query->where('ann_category', $request->category);
        }

        $data = $query->orderBy('ann_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        return ReturnHelper::jsonApiReturn(new PaginatedResource($data, AnnouncementApiResource::class));
    }


    public function getReceivedAnnouncements(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $userRoleId = $user->emp_role_id;
        $userId     = $user->emp_id;

        $page  = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $query = Announcement::query()->where('ann_b_id', $businessId);

        if ($userRoleId != 1) {
            $query->where(function ($q) use ($userRoleId, $userId) {
                $q->where('ann_role_id', $userRoleId)  // same role
                    ->orWhereNull('ann_role_id')       // all
                    ->orWhere('ann_role_id', 0);
                // NOTE: yaha creator condition hatayi gayi hai
            });
        }

        // Category filter
        if ($request->has('category')) {
            $query->where('ann_category', $request->category);
        }

        $data = $query->orderBy('ann_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        return ReturnHelper::jsonApiReturn(new PaginatedResource($data, AnnouncementApiResource::class));
    }


    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $ids = $request->input('ids');

        $query = Announcement::where('ann_is_read', 0);

        if (is_array($ids) && count($ids) > 0) {
            $query->whereIn('ann_id', $ids);
        }

        $updated = $query->update(['ann_is_read' => 1]);

        return response()->json([
            'status' => true,
            'updated' => $updated,
            'message' => (is_array($ids) && count($ids) > 0)
                ? 'Selected announcement marked as read.'
                : 'All announcement marked as read.'
        ]);
    }

    public function getAnnouncementList(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $userRoleId = $user->emp_role_id;
        $userId     = $user->emp_id;

        $page  = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $query = Announcement::query()->where('ann_b_id', $businessId);

        // ✅ Role filter + Creator override
        if ($userRoleId != 1) {
            $query->where(function ($q) use ($userRoleId, $userId) {
                $q->where('ann_role_id', $userRoleId)  // same role
                    ->orWhereNull('ann_role_id')         // all
                    ->orWhere('ann_role_id', 0)
                    ->orWhere('ann_user_id', $userId);   // creator override
            });
        }

        // ✅ Category filter
        if ($request->has('category')) {
            $query->where('ann_category', $request->category);
        }

        // ✅ From Date filter
        if (!is_null($request->input('from_date'))) {
            $query->whereDate(
                'created_at',
                '>=',
                Carbon::createFromFormat('d M, Y', $request->input('from_date'))->startOfDay()
            );
        }

        // ✅ To Date filter
        if (!is_null($request->input('to_date'))) {
            $query->whereDate(
                'created_at',
                '<=',
                Carbon::createFromFormat('d M, Y', $request->input('to_date'))->endOfDay()
            );
        }

        // ✅ Last 15 days filter
        if ($request->input('last_15_days')) {
            $query->whereDate('created_at', '>=', Carbon::now()->subDays(15));
        }

        $data = $query->orderBy('ann_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        return ReturnHelper::jsonApiReturn(new PaginatedResource($data, AnnouncementApiResource::class));
    }




    public function deleteAnnouncement($id)
    {
        try {
            $announcement = Announcement::find($id);

            if (!$announcement) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Announcement not found',
                ], 404);
            }

            // Agar image delete karni ho
            if ($announcement->ann_image && file_exists(public_path('uploads/announcements/' . $announcement->ann_image))) {
                unlink(public_path('uploads/announcements/' . $announcement->ann_image));
            }

            $announcement->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Announcement deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error deleting announcement',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


}
