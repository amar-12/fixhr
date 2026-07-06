<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserDevice;
use Illuminate\Support\Facades\Auth;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use ChandraHemant\HtkcUtils\CommonUtils;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Grade;
use App\Helpers\RolePermissionLogics;

class UserDeviceController extends Controller
{
    // API endpoint for mobile to register device
    public function store(Request $request)
    {
       $user = Auth::user();

        $request->validate([
            'employee_code' => 'required|string',
            'employee_name' => 'required|string',
            'device_id' => 'required|string',
        ]);

        // Check if same device_id is already registered for this employee
        $existingDeviceCount = UserDevice::where('ud_emp_id', $user->emp_id)   
        ->where('ud_emp_b_id', $user->emp_b_id) // Added business ID check
        ->where('ud_device_id', $request->device_id)
        ->count();
    
    if ($existingDeviceCount >= 2) {
        return response()->json([
            'message' => 'This device has already been registered twice for this employee.',
            'status' => false,
            'result' => []
        ], 200);    
    }
        // Check if employee already has any device entries
        $existingEntries = UserDevice::where('ud_emp_id', $user->emp_id)
            ->where('ud_emp_b_id', $user->emp_b_id)
            ->get();

        // If employee has 2 or more entries, reject the request
        if ($existingEntries->count() >= 2) {
            return response()->json([
                'message' => 'Maximum device requests limit reached for this employee.',
                'status' => false
            ], 400);
        }

        // If employee has 1 existing entry, reject the previous one
        if ($existingEntries->count() == 1) {
            $existingEntry = $existingEntries->first();
            $existingEntry->update(['ud_status' => UserDevice::STATUS_REJECTED]);
        }

        // Create new device entry
        $device = UserDevice::create([
            'ud_emp_code' => $request->employee_code,
            'ud_emp_id' => $user->emp_id,
            'ud_emp_b_id' => $user->emp_b_id,
            'ud_emp_name' => $request->employee_name,
            'ud_device_id' => $request->device_id,
            'ud_status' => UserDevice::STATUS_PENDING,
        ]);

        $message = $existingEntries->count() == 1 
            ? 'Previous device request rejected. New device registered, pending verification.'
            : 'Device registered, pending verification';

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
        $businessId = $user->emp_b_id;
        $dateFilter = $request->input('fromDate') ?? now()->toDateString();

        // Filters
        $branchFilter = request()->input('daily_branchFilter');
        $designationFilter = request()->input('daily_designationFilter');
        $departmentFilter = request()->input('daily_departmentFilter');
        $statusFilter = request()->input('daily_activeFilter');
        $toDateFilter = $request->input('fromDate') ?? now()->toDateString();

        // Counts for the selected date
        $query = Employee::where('emp_b_id', $businessId)
            ->whereNot('emp_role_id', 1);

        if ($branchFilter) {
            $query->where('emp_br_id', $branchFilter);
        }
        if ($departmentFilter) {
            $query->where('emp_d_id', $departmentFilter);
        }
        if ($designationFilter) {
            $query->where('emp_dg_id', $designationFilter);
        }
        if ($statusFilter) {
            $query->where('emp_status', $statusFilter);
        }




        if ($request->ajax()) {
            $query = UserDevice::where('ud_emp_b_id', $user->emp_b_id)
            ->select('ud_id', 'ud_emp_code', 'ud_emp_name', 'ud_device_id', 'ud_status')
            ->orderBy('created_at', 'desc');

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

            // Search filter
            if ($search = $request->input('search.value')) {
                $query->where(function($q) use ($search) {
                    $q->where('ud_emp_code', 'like', "%$search%")
                      ->orWhere('ud_emp_name', 'like', "%$search%")
                      ->orWhere('ud_device_id', 'like', "%$search%")
                      ->orWhereRaw("CAST(ud_id AS CHAR) LIKE ?", ["%$search%"]);
                });
            }

            $total = $query->count();

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $devices = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($devices as $device) {
                $data[] = [
                    $device->ud_id , // plain text
                    $device->ud_emp_code, // plain text
                    $device->ud_emp_name, // plain text
                    $device->ud_device_id, // plain text
                    $device->ud_status === UserDevice::STATUS_VERIFIED 
                        ? '<span class="badge badge_rounded bg-success">Verified</span>'
                        : ($device->ud_status === UserDevice::STATUS_REJECTED 
                            ? '<span class="badge badge_rounded bg-danger">Rejected</span>'
                            : '<span class="badge badge_rounded bg-warning text-dark">Pending</span>')
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
            ]);
        }

        return view('admin.requests.device-verification');
    }


    // // Web: Confirm device
    // public function verify($id, Request $request)
    // {
    //     $device = UserDevice::findOrFail($id);
    //     $device->ud_status = UserDevice::STATUS_VERIFIED;
    //     $device->save();

    //     if ($request->ajax()) {
    //         return response()->json(['message' => 'Device verified successfully.']);
    //     }

    //     return redirect()->back()->with('success', 'Device verified successfully.');
    // }

    // // Web: Reject device
    // public function reject($id, Request $request)
    // {
    //     $device = UserDevice::findOrFail($id);
    //     $device->ud_status = UserDevice::STATUS_REJECTED;
    //     $device->save();

    //     if ($request->ajax()) {
    //         return response()->json(['message' => 'Device rejected successfully.']);
    //     }

    //     return redirect()->back()->with('success', 'Device rejected successfully.');
    // }

    public function bulkVerify(Request $request)
    {
        if ($request->input('all')) {
            UserDevice::where('ud_status', UserDevice::STATUS_PENDING)->update(['ud_status' => UserDevice::STATUS_VERIFIED]);
            return response()->json(['message' => 'All pending devices verified successfully.']);
        }
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['message' => 'No device IDs provided.'], 400);
        }
        UserDevice::whereIn('ud_id', $ids)->update(['ud_status' => UserDevice::STATUS_VERIFIED]);
        return response()->json(['message' => 'Selected devices verified successfully.']);
    }

    public function bulkReject(Request $request)
    {
        if ($request->input('all')) {
            UserDevice::where('ud_status', UserDevice::STATUS_PENDING)->update(['ud_status' => UserDevice::STATUS_REJECTED]);
            return response()->json(['message' => 'All pending devices rejected successfully.']);
        }
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['message' => 'No device IDs provided.'], 400);
        }
        UserDevice::whereIn('ud_id', $ids)->update(['ud_status' => UserDevice::STATUS_REJECTED]);
        return response()->json(['message' => 'Selected devices rejected successfully.']);
    }

    /**
     * Server-side DataTable for UserDevice (for admin device verification page)
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
            $query = UserDevice::where('ud_emp_b_id', $businessId);

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

            // Handle device status filter separately from employee status filter
            // $deviceStatusFilter = $request->input('device_status_filter');
            // if ($deviceStatusFilter) {
            //     if ($deviceStatusFilter == '71') { // Active/Verified
            //         $query->where('ud_status', UserDevice::STATUS_VERIFIED);
            //     } elseif ($deviceStatusFilter == '72') { // Inactive/Rejected
            //         $query->where('ud_status', UserDevice::STATUS_REJECTED);
            //     } elseif ($deviceStatusFilter == '0') { // Pending
            //         $query->where('ud_status', UserDevice::STATUS_PENDING);
            //     }
            // }

            if ($dateFilter) {
                $query->whereDate('created_at', $dateFilter);
            }

            // Apply search
            $search = $request->input('search.value');
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('ud_emp_code', 'like', "%$search%")
                      ->orWhere('ud_emp_name', 'like', "%$search%")
                      ->orWhere('ud_device_id', 'like', "%$search%");
                });
            }

            // Get total count before pagination
            $totalRecords = $query->count();
            $filteredRecords = $totalRecords; // Same as total since we're not doing separate filtering

            // Apply ordering and pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            // $orderColumn = $request->input('order.0.column', 4); // Default to status column
            // $orderDir = $request->input('order.0.dir', 'desc');

            // // Map column index to actual column name
            // $columns = ['ud_id', 'ud_emp_code', 'ud_emp_name', 'ud_device_id', 'ud_status', 'created_at', 'created_at'];
            // $orderBy = $columns[$orderColumn] ?? 'created_at';

            // $devices = $query->orderBy($orderBy, $orderDir)
            //                 ->skip($start)

       // Always ensure newest first by default
            $query->orderBy('created_at', 'desc');

            // Optional secondary ordering based on client request
            $requestedOrderColumn = $request->input('order.0.column');
            $requestedOrderDir = strtolower($request->input('order.0.dir', 'desc'));
            $requestedOrderDir = in_array($requestedOrderDir, ['asc', 'desc']) ? $requestedOrderDir : 'desc';

            // Column map aligned with DataTable columns (0 = S.No, non-orderable)
            $columns = [
                null,                 // 0: S.No (do not order on this)
                'ud_emp_code',        // 1
                'ud_emp_name',        // 2
                'ud_device_id',       // 3
                'ud_status',          // 4
                'created_at',         // 5
                'created_at',         // 6 (time derived from created_at)
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
                $row[] = $device->ud_emp_code;
                $row[] = $device->ud_emp_name;
                $row[] = $device->ud_device_id;
                $row[] = $device->ud_status === UserDevice::STATUS_VERIFIED 
                    ? '<span class="badge badge_rounded bg-success">Verified</span>'
                    : ($device->ud_status === UserDevice::STATUS_REJECTED 
                        ? '<span class="badge badge_rounded bg-danger">Rejected</span>'
                        : '<span class="badge badge_rounded bg-warning">Pending</span>');
                $row[] = $device->created_at->format('d-m-Y');
                $row[] = $device->created_at->format('H:i');
                $row[] = '<input type="checkbox" class="row-checkbox" value="'.$device->ud_id.'">';
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

    public function isBiometricLock(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'is_lock' => 'required|boolean',
            'device_token' => 'nullable|string|max:255'
        ]);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        // Update employee record
        $employee = Employee::where('emp_id', $user->emp_id)->first();

        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee not found'
            ], 404);
        }

        $employee->update([
            'emp_is_device_lock' => $request->is_lock,
            'emp_device_token' => $request->is_lock ? $request->device_token : null,
        ]);

        return response()->json([
            'status' => true,
            'result' => true,
            'message' => 'Device lock status updated successfully',
        ]);
    }
}
