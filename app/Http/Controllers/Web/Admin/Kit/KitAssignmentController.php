<?php

namespace App\Http\Controllers\Web\Admin\Kit;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Kit;
use App\Models\KitStock;
use App\Models\KitAssignment;
use App\Models\KitLog;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KitAssignmentController extends Controller
{

    // public function index(Request $request)
    // {
    //     $businessId = Auth::user()->emp_b_id;

    //     $totalAssigned = KitAssignment::where('b_id', $businessId)->sum('assigned_qty');
    //     $totalReturned = KitAssignment::where('b_id', $businessId)->sum('return_qty');
    //     $totalLost     = KitAssignment::where('b_id', $businessId)->sum('lost_qty');
    //     $kits   = Kit::where('b_id', $businessId)->get();
    //     $employees = Employee::where('emp_status', 71)->where('emp_role_id', '!=', 1)->where('emp_b_id', $businessId)->select('emp_id', 'emp_full_name', 'emp_code')->get();

    //     $kitFilter = $request->kitFilter;
    //     $employeeFilter = $request->employeeFilter;
    //     $dateRange = $request->dateRange;
    //     // $data = KitAssignment::with('fh_kit.kit')->get();

    //     if ($request->ajax()) {
    //         $dynamicConditions = [
    //             ['method' => 'where', 'args' => ['b_id', $businessId]],
    //             ['method' => 'with',  'args' => [['fh_kit.kit', 'assignedTo:emp_id,emp_full_name', 'assignedBy:emp_id,emp_full_name']]],
    //             [
    //                 'method' => 'select',
    //                 'args' => [
    //                     'id',
    //                     'kit_id',
    //                     'assigned_by',
    //                     'assigned_to',
    //                     'assigned_qty',
    //                     'per_unit_price',
    //                     'total_payable_amount',
    //                     'assigned_date',
    //                     'return_date',
    //                     'return_qty',
    //                     'lost_qty',
    //                     'status',
    //                     'return_condition',
    //                     'lost_reason',
    //                     'b_id',
    //                     'created_at'
    //                 ],
    //                 'relation' => [],
    //             ]
    //         ];

    //         if (!empty($kitFilter)) {
    //             $dynamicConditions[] = ['method' => 'where', 'args' => ['kit_id', $kitFilter]];
    //         }

    //         if (!empty($employeeFilter)) {
    //             $dynamicConditions[] = ['method' => 'where', 'args' => ['assigned_to', $employeeFilter]];
    //         }

    //         if (!empty($dateRange)) {
    //             $dates = explode(' - ', $dateRange);
    //             if (count($dates) == 2) {
    //                 $start = Carbon::parse(trim($dates[0]))->startOfDay();
    //                 $end   = Carbon::parse(trim($dates[1]))->endOfDay();

    //                 $dynamicConditions[] = [
    //                     'method' => 'whereBetween',
    //                     'args' => ['assigned_date', [$start, $end]]
    //                 ];
    //             }
    //         }
    //         $searchColumns = [
    //             'assigned_qty',
    //             'remarks',
    //             'status'
    //         ];

    //         $searchRelationships = [
    //             'kit' => ['name'],
    //             'assignedTo' => ['emp_full_name'],
    //             'assignedBy' => ['emp_full_name']
    //         ];

    //         $helper = new DynamicModelDataTableHelper(
    //             eloquentModel: new KitAssignment(),
    //             dynamicConditions: $dynamicConditions,
    //             searchColumns: $searchColumns,
    //             searchRelationships: $searchRelationships
    //         );

    //         $list = $helper->getServerSideDataTable();

    //         $rowData = [];
    //         foreach ($list as $i => $s) {

    //             //     $actions = '
    //             //     <div class="dropdown">
    //             //         <button class="btn btn-light btn-sm" data-bs-toggle="dropdown">
    //             //             <i class="fa fa-ellipsis-v"></i>
    //             //         </button>
    //             //         <ul class="dropdown-menu dropdown-menu-end p-2">

    //             //             <li>
    //             //                 <a href="' . route('kit-assignment.show', $s->id) . '"
    //             //                    class="dropdown-item text-primary">
    //             //                    <i class="bi bi-eye"></i> View Details
    //             //                 </a>
    //             //             </li>

    //             //             <li>
    //             //                 <a href="javascript:void(0)"
    //             //                    class="dropdown-item text-warning editBtn"
    //             //                    data-id="' . $s->id . '">
    //             //                    <i class="bi bi-pencil-square"></i> Edit
    //             //                 </a>
    //             //             </li>

    //             //             <li>
    //             //                 <a href="javascript:void(0)"
    //             //                    class="dropdown-item text-danger deleteBtn"
    //             //                    data-id="' . $s->id . '">
    //             //                    <i class="bi bi-trash"></i> Delete
    //             //                 </a>
    //             //             </li>

    //             //         </ul>
    //             //     </div>
    //             // ';


    //             $actions = '
    //             <div class="dropdown">
    //                 <button class="btn btn-light btn-sm" data-bs-toggle="dropdown">
    //                     <i class="fa fa-ellipsis-v"></i>
    //                 </button>
    //                 <ul class="dropdown-menu dropdown-menu-end p-2">

    //                     <li>
    //                         <a href="javascript:void(0)"
    //                         class="dropdown-item text-success returnQtyBtn"
    //                         data-id="' . $s->id . '"
    //                         data-kit="' . e($s->kit->name ?? '') . '"
    //                         data-total="' . $s->assigned_qty . '">
    //                         <i class="bi bi-arrow-return-left"></i> Return Qty
    //                         </a>
    //                     </li>

    //                     <li>
    //                         <a href="javascript:void(0)"
    //                         class="dropdown-item text-secondary lostQtyBtn"
    //                         data-id="' . $s->id . '"
    //                         data-kit="' . e($s->kit->name ?? '') . '"
    //                         data-total="' . $s->assigned_qty . '">
    //                         <i class="bi bi-question-diamond"></i> Lost Qty
    //                         </a>
    //                     </li>


    //             </ul>

    //             </div>
    //         ';


    //             $rowData[] = [
    //                 $i + 1,
    //                 e($s->kit->fh_kit->kit->name ?? 'N/A'),
    //                 // e($s->kit->name ?? 'N/A'),
    //                 // e($s->kit->name ?? 'N/A'),

    //                 e($s->assignedTo->emp_full_name ?? 'N/A'),
    //                 e($s->assignedBy->emp_full_name ?? 'N/A'),
    //                 $s->assigned_qty,
    //                 $s->per_unit_price,
    //                 $s->total_payable_amount,
    //                 ($s->assigned_date ? Carbon::parse($s->assigned_date)->format('d M Y') : '-'),
    //                 ($s->return_date ? Carbon::parse($s->return_date)->format('d M Y') : '-'),
    //                 $s->return_qty,
    //                 $s->lost_qty,
    //                 ucfirst($s->status),
    //                 e($s->return_condition),
    //                 e($s->lost_reason),
    //                 $actions
    //             ];
    //         }

    //         return response()->json([
    //             "draw"            => $request->draw,
    //             "recordsTotal"    => KitAssignment::where('b_id', $businessId)->count(),
    //             "recordsFiltered" => $helper->countFilteredServerSideDataTable(),
    //             "data"            => $rowData
    //         ]);
    //     }

    //     // ---------------- Columns (for Blade Header) ----------------
    //     $columns = [
    //         ['name' => 'S. No.', 'width' => '4%'],
    //         ['name' => 'Kit Name', 'width' => '12%'],
    //         ['name' => 'Assigned To', 'width' => '12%'],
    //         ['name' => 'Assigned By', 'width' => '12%'],
    //         ['name' => 'Qty', 'width' => '6%'],
    //         ['name' => 'Unit Price', 'width' => '8%'],
    //         ['name' => 'Total Amount', 'width' => '8%'],
    //         ['name' => 'Assigned Date', 'width' => '10%'],
    //         ['name' => 'Return Date', 'width' => '10%'],
    //         ['name' => 'Returned Qty', 'width' => '8%'],
    //         ['name' => 'Lost Qty', 'width' => '6%'],
    //         ['name' => 'Status', 'width' => '6%'],
    //         ['name' => 'Return Remark', 'width' => '10%'],
    //         ['name' => 'Lost Remark', 'width' => '10%'],
    //         ['name' => 'Action', 'width' => '6%'],
    //     ];

    //     return view('admin.kits.assigned', compact(
    //         'columns',
    //         'kits',
    //         'employees',
    //         'totalAssigned',
    //         'totalReturned',
    //         'totalLost'
    //     ));
    // }

    public function index(Request $request)
    {
        $businessId = Auth::user()->emp_b_id;

        // Summary counts
        $totalAssigned = KitAssignment::where('b_id', $businessId)->sum('assigned_qty');
        $totalReturned = KitAssignment::where('b_id', $businessId)->sum('return_qty');
        $totalLost     = KitAssignment::where('b_id', $businessId)->sum('lost_qty');

        // Filters data
        $kits = Kit::where('b_id', $businessId)->get();
        $employees = Employee::where('emp_status', 71)
            ->where('emp_role_id', '!=', 1)
            ->where('emp_b_id', $businessId)
            ->select('emp_id', 'emp_full_name', 'emp_code')
            ->get();

        // Request filters
        $kitFilter      = $request->kitFilter;
        $employeeFilter = $request->employeeFilter;
        $dateRange      = $request->dateRange;

        // AJAX SETUP
        if ($request->ajax()) {

            $dynamicConditions = [
                ['method' => 'where', 'args' => ['b_id', $businessId]],
                ['method' => 'with',  'args' => [['fh_kit.kit', 'assignedTo:emp_id,emp_full_name,emp_code', 'assignedBy:emp_id,emp_full_name,emp_code']]],
                [
                    'method' => 'select',
                    'args' => [
                        'id',
                        'kit_id',
                        'assigned_by',
                        'assigned_to',
                        'assigned_qty',
                        'per_unit_price',
                        'total_payable_amount',
                        'assigned_date',
                        'return_date',
                        'return_qty',
                        'lost_qty',
                        'status',
                        'return_condition',
                        'lost_reason',
                        'b_id',
                        'created_at'
                    ],
                    'relation' => [],
                ],
            ];

            // Filters
            if ($kitFilter) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['kit_id', $kitFilter]];
            }

            if ($employeeFilter) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['assigned_to', $employeeFilter]];
            }

            if ($dateRange) {
                $dates = explode(' - ', $dateRange);

                if (count($dates) == 2) {
                    $start = Carbon::parse(trim($dates[0]))->startOfDay();
                    $end   = Carbon::parse(trim($dates[1]))->endOfDay();

                    $dynamicConditions[] = [
                        'method' => 'whereBetween',
                        'args' => ['assigned_date', [$start, $end]]
                    ];
                }
            }

            // Searching
            $searchColumns = ['assigned_qty', 'remarks', 'status'];
            $searchRelationships = [
                'fh_kit.kit' => ['name'],
                'assignedTo' => ['emp_full_name', 'emp_code'],
                'assignedBy' => ['emp_full_name', 'emp_code']
            ];

            $helper = new DynamicModelDataTableHelper(
                eloquentModel: new KitAssignment(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            );

            $list = $helper->getServerSideDataTable();

            // TABLE ROWS
            $rowData = [];
            foreach ($list as $i => $s) {

                $kitName = $s->fh_kit->kit->name ?? 'N/A';

                $actions = '
                <div class="dropdown">
                    <button class="btn btn-light btn-sm" data-bs-toggle="dropdown">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end p-2">

                        <li>
                            <a href="javascript:void(0)"
                               class="dropdown-item text-success returnQtyBtn"
                               data-id="' . $s->id . '"
                               data-kit="' . e($kitName) . '"
                               data-total="' . $s->assigned_qty . '">
                                <i class="bi bi-arrow-return-left"></i> Return Qty
                            </a>
                        </li>

                        <li>
                            <a href="javascript:void(0)"
                               class="dropdown-item text-secondary lostQtyBtn"
                               data-id="' . $s->id . '"
                               data-kit="' . e($kitName) . '"
                               data-total="' . $s->assigned_qty . '">
                                <i class="bi bi-question-diamond"></i> Lost Qty
                            </a>
                        </li>

                    </ul>
                </div>
            ';

                $rowData[] = [
                    $i + 1,
                    e($kitName),

                    // Assigned To (Name - Code)
                    e(($s->assignedTo->emp_full_name ?? 'N/A') . ' - ' . ($s->assignedTo->emp_code ?? 'N/A')),

                    // Assigned By (Name - Code)
                    e(($s->assignedBy->emp_full_name ?? 'N/A') . ' - ' . ($s->assignedBy->emp_code ?? 'N/A')),

                    $s->assigned_qty,
                    $s->per_unit_price,
                    $s->total_payable_amount,

                    $s->assigned_date
                        ? Carbon::parse($s->assigned_date)->format('d M Y')
                        : '-',

                    $s->return_date
                        ? Carbon::parse($s->return_date)->format('d M Y')
                        : '-',

                    $s->return_qty,
                    $s->lost_qty,
                    ucfirst($s->status),
                    e($s->return_condition),
                    e($s->lost_reason),
                    $actions
                ];
            }

            return response()->json([
                "draw"            => $request->draw,
                "recordsTotal"    => KitAssignment::where('b_id', $businessId)->count(),
                "recordsFiltered" => $helper->countFilteredServerSideDataTable(),
                "data"            => $rowData
            ]);
        }

        // TABLE HEADERS FOR BLADE
        $columns = [
            ['name' => 'S. No.', 'width' => '4%'],
            ['name' => 'Kit Name', 'width' => '12%'],
            ['name' => 'Assigned To', 'width' => '12%'],
            ['name' => 'Assigned By', 'width' => '12%'],
            ['name' => 'Qty', 'width' => '6%'],
            ['name' => 'Unit Price', 'width' => '8%'],
            ['name' => 'Total Amount', 'width' => '8%'],
            ['name' => 'Assigned Date', 'width' => '10%'],
            ['name' => 'Return Date', 'width' => '10%'],
            ['name' => 'Returned Qty', 'width' => '8%'],
            ['name' => 'Lost Qty', 'width' => '6%'],
            ['name' => 'Status', 'width' => '6%'],
            ['name' => 'Return Remark', 'width' => '10%'],
            ['name' => 'Lost Remark', 'width' => '10%'],
            ['name' => 'Action', 'width' => '6%'],
        ];

        return view('admin.kits.assigned', compact(
            'columns',
            'kits',
            'employees',
            'totalAssigned',
            'totalReturned',
            'totalLost'
        ));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $user = Auth::user();
        $businessId = (int) $user->emp_b_id;
        $emp_id = $user->emp_id;

        $kit_id = (int) $request->id;

        $request->validate([
            'id' => 'required|exists:kits,id',
            'quantity' => 'required|numeric|min:1',
            'emp_id' => 'required|exists:employees,emp_id',
            'description' => 'nullable|string',
            'note' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
        ]);

        // Fetch stock
        $existing = KitStock::where('b_id', $businessId)
            ->where('id', $kit_id)
            ->first();

        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => 'Stock record not found for this kit.'
            ], 404);
        }

        if ($request->quantity > $existing->available_qty) {
            return response()->json([
                'success' => false,
                'message' => 'Requested quantity exceeds available stock.'
            ], 422);
        }

        // Create assignment record
        $createAssign = KitAssignment::create([
            'b_id' => $businessId,
            'kit_id' => $kit_id,
            'assigned_by' => $emp_id,
            'assigned_to' => $request->emp_id,
            'assigned_qty' => $request->quantity,
            'per_unit_price' => $existing->price_per_unit,
            'total_payable_amount' => $existing->price_per_unit * $request->quantity,
            'assigned_date' => now(),
            'assigned_data' => $request->description ?? '',
            'remarks' => $request->note ?? '',
            'status' => 'assigned', // 0 = Assigned
        ]);

        // Log the action
        KitLog::create([
            'b_id'           => $businessId,
            'kit_id'         => $kit_id,
            'action_type'    => 'ASSIGNED',
            'reference_id'   => $createAssign->id,
            'opening_qty'    => 0,
            'available_qty'  => $existing->available_qty,
            'assigned_qty'   => $request->quantity,
            'damaged_qty'    => 0,
            'lost_qty'       => 0,
            'qty'            => $request->quantity,
            'replaced_qty'   => 0,
            'price_per_unit' => $existing->price_per_unit,
            'total_price'    => $existing->total_price,
            'note'           => $request->description ?? null,
            'action_by'      => $emp_id,
        ]);

        // Update stock
        $existing->available_qty -= $request->quantity;
        $existing->assigned_qty += $request->quantity;
        $existing->total_price = $existing->available_qty * $existing->price_per_unit;
        $existing->save();

        return response()->json([
            'success' => true,
            'message' => 'Kit assigned successfully!',
            'data' => $createAssign
        ]);
    }


    public function lostKit(Request $request)
    {
        $user = Auth::user();
        $businessId = (int) $user->emp_b_id;

        // Validate request
        $request->validate([
            'id'        => 'required|exists:kit_assignments,id',
            'quantity'  => 'required|numeric|min:1',
            'reason'    => 'nullable|string',
            'remarks'   => 'nullable|string',
            'cost'      => 'nullable|numeric|min:0',
        ]);

        $assignedId = (int) $request->id;

        // Fetch assignment
        $existing = KitAssignment::where('b_id', $businessId)
            ->where('id', $assignedId)
            ->first();

            // dd($existing);

        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment record not found.'
            ], 404);
        }

        // Validate quantity
        if ($request->quantity > $existing->assigned_qty) {
            return response()->json([
                'success' => false,
                'message' => 'Lost quantity cannot exceed available quantity.'
            ], 422);
        }

        // Update record
        $existing->assigned_qty -= $request->quantity;
        $existing->lost_qty      += $request->quantity;
        $existing->lost_reason   = $request->reason;
        $existing->lost_remark   = $request->remarks;
        $existing->lost_cost     = $request->cost ?? 0;
        $existing->save();

        return response()->json([
            'success' => true,
            'message' => 'Lost quantity updated successfully!',
            'data'    => $existing
        ]);
    }
}
