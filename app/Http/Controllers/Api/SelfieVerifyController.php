<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SelfieVerify;
use Illuminate\Support\Facades\Auth;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use ChandraHemant\HtkcUtils\CommonUtils;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Grade;
use App\Helpers\RolePermissionLogics;
use Illuminate\Validation\Rule;

class SelfieVerifyController extends Controller
{
    // API endpoint for mobile to register device
    public function store(Request $request)
    {
       $user = Auth::user();

        $request->validate([
            'emp_code' => 'required|string',
            'emp_name' => 'required|string',
            'device_id' => 'required|string',
            'device_name' => 'required|string',
            'device_modal' => 'required|string',
        ]);

        $existingDevice = SelfieVerify::where('sv_device_id', $request->device_id)
            // ->where('sv_emp_b_id', $user->emp_b_id)
            ->first();
    
        if ($existingDevice) {
            $name = trim(preg_replace('/\s+/', ' ', $existingDevice->employee->emp_full_name));
            return response()->json([
                'message' => 'This device has already been registered for ' . $name,
                'status' => false,
                'result' => []
            ], 200);    
        }

        // Check if employee already has any device entries
        $existingEntry = SelfieVerify::where('sv_emp_id', $user->emp_id)
            ->where('sv_emp_b_id', $user->emp_b_id)
            ->first();
        if ($existingEntry) {
            $deviceDetail = $existingEntry->sv_device_name.' '.$existingEntry->sv_device_modal;
            return response()->json([
                'message' => 'You have already registered in '.$deviceDetail,
                'status' => false,
                'result' => []
            ], 400);
        }

        // Create new device entry
        $device = SelfieVerify::create([
            'sv_emp_code' => $request->emp_code,
            'sv_emp_id' => $user->emp_id,
            'sv_emp_b_id' => $user->emp_b_id,
            'sv_emp_name' => $request->emp_name,
            'sv_device_id' => $request->device_id,
            'sv_device_name' => $request->device_name,
            'sv_device_modal' => $request->device_modal,
            'sv_status' => SelfieVerify::STATUS_VERIFIED,
        ]);

        $message = 'Device registered, pending verification';
        return response()->json([
            'message' => $message, 
            'result' => [$device], 
            'status' => true
        ], 200);
    }

    // Web: List all devices (for admin)
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($request->ajax()) {

            $query = SelfieVerify::where('sv_emp_b_id', $user->emp_b_id)
                ->select('sv_id', 'sv_emp_code', 'sv_emp_name', 'sv_device_id', 'sv_device_name', 'sv_device_modal', 'sv_status', 'created_at');

            // Filters
            if ($branchFilter = $request->input('daily_branchFilter')) {
                $query->whereHas('employee', fn($q) => $q->where('emp_br_id', $branchFilter));
            }

            if ($departmentFilter = $request->input('daily_departmentFilter')) {
                $query->whereHas('employee', fn($q) => $q->where('emp_d_id', $departmentFilter));
            }

            if ($designationFilter = $request->input('daily_designationFilter')) {
                $query->whereHas('employee', fn($q) => $q->where('emp_dg_id', $designationFilter));
            }

            if ($statusFilter = $request->input('daily_activeFilter')) {
                $query->whereHas('employee', fn($q) => $q->where('emp_status', $statusFilter));
            }

            if ($dateFilter = $request->input('fromDate')) {
                $query->whereDate('created_at', $dateFilter);
            }

            // Search
            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('sv_emp_code', 'like', "%$search%")
                      ->orWhere('sv_emp_name', 'like', "%$search%")
                      ->orWhere('sv_device_id', 'like', "%$search%")
                      ->orWhere('sv_device_name', 'like', "%$search%")
                      ->orWhere('sv_device_modal', 'like', "%$search%");
                });
            }

            // Counts
            $totalRecords = SelfieVerify::where('sv_emp_b_id', $user->emp_b_id)->count();
            $filteredRecords = $query->count();

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);

            $devices = $query->orderBy('created_at', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

            // Data format
            $data = [];
            $i = $start + 1;

            foreach ($devices as $device) {

                // Status Badge
                $status = $device->sv_status === SelfieVerify::STATUS_VERIFIED 
                    ? '<span class="badge badge_rounded bg-success">Verified</span>'
                    : ($device->sv_status === SelfieVerify::STATUS_REJECTED 
                        ? '<span class="badge badge_rounded bg-danger">Rejected</span>'
                        : '<span class="badge badge_rounded bg-warning text-dark">Pending</span>');

                // Checkbox (only for pending - optional but best practice)
                $checkbox = $device->sv_status == SelfieVerify::STATUS_PENDING
                    ? '<input type="checkbox" class="row-checkbox" value="'.$device->sv_id.'">'
                    : '';

                $data[] = [
                    $i++, // S.No
                    $device->sv_emp_code,
                    $device->sv_emp_name,
                    // 👇 Editable fields
                    '<span class="editable" data-id="'.$device->sv_id.'" data-field="sv_device_id">'.$device->sv_device_id.'</span>',
                    
                    '<span class="editable" data-id="'.$device->sv_id.'" data-field="sv_device_name">'.$device->sv_device_name.'</span>',
                    
                    '<span class="editable" data-id="'.$device->sv_id.'" data-field="sv_device_model">'.$device->sv_device_model.'</span>',
                    $status,
                    $device->created_at->format('d-m-Y'), // Date
                    $device->created_at->format('H:i'),   // Time
                    $checkbox
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data,
            ]);
        }

        return view('admin.requests.selfie-verification');
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);
        $all = $request->input('all');

        if (!in_array($action, ['verify', 'reject'])) {
            return response()->json(['message' => 'Invalid action'], 400);
        }

        $status = $action === 'verify'
            ? SelfieVerify::STATUS_VERIFIED
            : SelfieVerify::STATUS_REJECTED;

        if ($all) {
            SelfieVerify::where('sv_status', SelfieVerify::STATUS_PENDING)
                ->update(['sv_status' => $status]);

            return response()->json([
                'message' => "All pending devices {$action}ed successfully."
            ]);
        }

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['message' => 'No device IDs provided.'], 400);
        }

        SelfieVerify::whereIn('sv_id', $ids)
            ->update(['sv_status' => $status]);

        return response()->json([
            'message' => "Selected devices {$action}ed successfully."
        ]);
    }

    public function updateInline(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'id' => 'required',
            'sv_device_id' => 'required',
            'sv_device_name' => 'required',
            'sv_device_modal' => 'required',
        ]);

        //Check duplicate device id (exclude current record)
        $existingDevice = SelfieVerify::where('sv_device_id', $request->sv_device_id)
            ->where('sv_id', '!=', $request->id)
            ->where('sv_emp_b_id', $user->emp_b_id) // multi-tenant safe
            ->with('employee:emp_id,emp_full_name,emp_code') // relation load
            ->first();

        if ($existingDevice) {
            return response()->json([
                'status' => false,
                'message' => 'Device ID already assigned by '.$existingDevice->employee->emp_full_name,
            ], 422);
        }

        $device = SelfieVerify::find($request->id);

        if (!$device) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $device->update([
            'sv_device_id' => $request->sv_device_id,
            'sv_device_name' => $request->sv_device_name,
            'sv_device_modal' => $request->sv_device_modal,
        ]);

        return response()->json(['message' => 'Updated successfully']);
    }

    /**
     * Server-side DataTable for SelfieVerify (for admin device verification page)
     */
    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            $businessId = $user->emp_b_id;

            // Get filter values
            $branchFilter = $request->input('daily_branchFilter');
            $designationFilter = $request->input('daily_designationFilter');
            $departmentFilter = $request->input('daily_departmentFilter');
            $statusFilter = $request->input('daily_activeFilter');
            $dateFilter = $request->input('fromDate');

            // Build the query manually to avoid DynamicModelDataTableHelper issues
            $query = SelfieVerify::where('sv_emp_b_id', $businessId);

            // Apply filters by joining with Employee table
            if ($branchFilter) {
                $query->whereHas('employee', function ($q) use ($branchFilter) {
                    $q->where('emp_br_id', $branchFilter);
                });
            }
            if ($departmentFilter) {
                $query->whereHas('employee', function ($q) use ($departmentFilter) {
                    $q->where('emp_d_id', $departmentFilter);
                });
            }
            if ($designationFilter) {
                $query->whereHas('employee', function ($q) use ($designationFilter) {
                    $q->where('emp_dg_id', $designationFilter);
                });
            }
            if ($statusFilter) {
                $query->whereHas('employee', function ($q) use ($statusFilter) {
                    $q->where('emp_status', $statusFilter);
                });
            }

            if ($dateFilter) {
                $query->whereDate('created_at', $dateFilter);
            }

            // Apply search
            $search = $request->input('search.value');
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('sv_emp_code', 'like', "%$search%")
                      ->orWhere('sv_emp_name', 'like', "%$search%")
                      ->orWhere('sv_device_id', 'like', "%$search%")
                      ->orWhere('sv_device_name', 'like', "%$search%")
                      ->orWhere('sv_device_modal', 'like', "%$search%");
                });
            }

            // Get total count before pagination
            $totalRecords = $query->count();
            $filteredRecords = $totalRecords; // Same as total since we're not doing separate filtering

            // Apply ordering and pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);

       // Always ensure newest first by default
            $query->orderBy('created_at', 'desc');

            // Optional secondary ordering based on client request
            $requestedOrderColumn = $request->input('order.0.column');
            $requestedOrderDir = strtolower($request->input('order.0.dir', 'desc'));
            $requestedOrderDir = in_array($requestedOrderDir, ['asc', 'desc']) ? $requestedOrderDir : 'desc';

            // Column map aligned with DataTable columns (0 = S.No, non-orderable)
            $columns = [
                null,
                'sv_emp_code',
                'sv_emp_name',
                'sv_device_id',
                'sv_device_name',
                'sv_device_modal',
                'sv_status',
                'created_at',
                'created_at',
            ];

            if ($requestedOrderColumn !== null && array_key_exists((int)$requestedOrderColumn, $columns)) {
                $orderColumnName = $columns[(int)$requestedOrderColumn];
                if (!empty($orderColumnName)) {
                    $query->orderBy($orderColumnName, $requestedOrderDir);
                }
            }
               $devices = $query->skip($start)
                            ->take($length)
                            ->get();

            

            $rowData = [];
            $i = $start + 1;
            foreach ($devices as $device) {
                $row = [];
                $row[] = $i++;
                $row[] = $device->sv_emp_code;
                $row[] = $device->sv_emp_name;

                // Editable columns
                $row[] = '<span class="editable device_id">'.$device->sv_device_id.'</span>';
                $row[] = '<span class="editable device_name">'.$device->sv_device_name.'</span>';
                $row[] = '<span class="editable device_model">'.$device->sv_device_modal.'</span>';

                // status
                $row[] = $device->sv_status === SelfieVerify::STATUS_VERIFIED 
                    ? '<span class="badge badge_rounded bg-success">Verified</span>'
                    : ($device->sv_status === SelfieVerify::STATUS_REJECTED 
                        ? '<span class="badge badge_rounded bg-danger">Rejected</span>'
                        : '<span class="badge badge_rounded bg-warning">Pending</span>');

                // date & time
                $row[] = $device->created_at->format('d-m-Y');
                $row[] = $device->created_at->format('H:i');

                // LAST COLUMN (actions + checkbox)
                $row[] = '
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa fa-edit text-primary edit-row" data-id="'.$device->sv_id.'" style="cursor:pointer;"></i>
                        <i class="fa fa-save text-success save-row d-none" data-id="'.$device->sv_id.'" style="cursor:pointer;"></i>
                        <input type="checkbox" class="row-checkbox ms-2" value="'.$device->sv_id.'">
                    </div>
                ';
                $rowData[] = $row;
            }

            $output = [
                "draw" => intval($request->input('draw')),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $filteredRecords,
                "data" => $rowData,
            ];

            return response()->json($output);
        }
        abort(404);
    }
}
