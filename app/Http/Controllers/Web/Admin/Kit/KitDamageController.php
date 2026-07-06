<?php

namespace App\Http\Controllers\Web\Admin\Kit;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Kit;
use App\Models\KitStock;
use App\Models\KitDamage;
use App\Models\KitLog;
use App\Models\KitLost;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KitDamageController extends Controller
{


    public function index(Request $request)
    {
        $businessId = Auth::user()->emp_b_id;

        $totalDamaged = KitDamage::where('b_id', $businessId)->sum('damage_qty');
        $kits = Kit::where('b_id', $businessId)->get();
        $employees = Employee::where('emp_status', 71)->where('emp_role_id', '!=', 1)->where('emp_b_id', $businessId)->select('emp_id', 'emp_full_name', 'emp_code')->get();

        $kitFilter = $request->kitFilter;
        $employeeFilter = $request->employeeFilter;
        $dateRange = $request->dateRange;


        if ($request->ajax()) {

            $dynamicConditions = [
                ['method' => 'where', 'args' => ['b_id', $businessId]],
                ['method' => 'with',  'args' => [['fh_kit.kit', 'damagedBy:emp_id,emp_full_name']]],
                [
                    'method' => 'select',
                    'args' => [
                        'id',
                        'b_id',
                        'kit_id',
                        'damage_by',
                        'damage_qty',
                        'damage_date',
                        'damage_details',
                        'damage_note',
                        'damage_cost',
                        'status',
                        'created_at'
                    ],
                    'relation' => [],
                ]
            ];

            // Filters
            if (!empty($kitFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['kit_id', $kitFilter]];
            }

            if (!empty($employeeFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['damage_by', $employeeFilter]];
            }

            if (!empty($dateRange)) {
                $dates = explode(' - ', $dateRange);
                if (count($dates) == 2) {
                    $start = Carbon::parse(trim($dates[0]))->startOfDay();
                    $end   = Carbon::parse(trim($dates[1]))->endOfDay();

                    $dynamicConditions[] = [
                        'method' => 'whereBetween',
                        'args' => ['damage_date', [$start, $end]]
                    ];
                }
            }

            // Searchable fields
            $searchColumns = [
                'damage_qty',
                'damage_details',
                'damage_note',
                'status'
            ];

            $searchRelationships = [
                'kit' => ['name'],
                'damagedBy' => ['emp_full_name']
            ];

            // Helper for server side DataTable
            $helper = new DynamicModelDataTableHelper(
                eloquentModel: new KitDamage(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            );

            $list = $helper->getServerSideDataTable();

            // Output rows
            $rowData = [];
            foreach ($list as $i => $d) {

                $actions = '
                <div class="dropdown">
                    <button class="btn btn-light btn-sm" data-bs-toggle="dropdown">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end p-2">

                        <li>
                            <a href="' . route('kits.show', $d->kit_id) . '"
                               class="dropdown-item text-primary">
                               <i class="bi bi-eye"></i> View Details
                            </a>
                        </li>
                    </ul>
                </div>
            ';

                $rowData[] = [
                    $i + 1,
                    e($d->fh_kit->kit->name ?? 'N/A'),
                    e($d->damagedBy->emp_full_name ?? 'N/A'),
                    $d->damage_qty,
                    ($d->damage_date ? Carbon::parse($d->damage_date)->format('d M Y') : '-'),
                    e($d->damage_details),
                    e($d->damage_note),
                    $d->damage_cost,
                    ucfirst($d->status),
                    $actions
                ];
            }

            return response()->json([
                "draw"            => $request->draw,
                "recordsTotal"    => KitDamage::where('b_id', $businessId)->count(),
                "recordsFiltered" => $helper->countFilteredServerSideDataTable(),
                "data"            => $rowData
            ]);
        }


        $columns = [
            ['name' => 'S. No.', 'width' => '4%'],
            ['name' => 'Kit Name', 'width' => '12%'],
            ['name' => 'Damaged By', 'width' => '12%'],
            ['name' => 'Qty', 'width' => '8%'],
            ['name' => 'Date', 'width' => '10%'],
            ['name' => 'Details', 'width' => '14%'],
            ['name' => 'Note', 'width' => '12%'],
            ['name' => 'Cost', 'width' => '8%'],
            ['name' => 'Status', 'width' => '8%'],
            ['name' => 'Action', 'width' => '6%'],
        ];

        return view('admin.kits.damage', compact(
            'columns',
            'kits',
            'employees',
            'totalDamaged'
        ));
    }


    public function kitlsotindex(Request $request)
    {
        $businessId = Auth::user()->emp_b_id;
        $kitFilter = $request->kit_id;
        $employeeFilter = $request->employee_id;
        $statusFilter = $request->status;
        $dateRange = $request->dateRange;

        if ($request->ajax()) {

            $dynamicConditions = [
                ['method' => 'where', 'args' => ['b_id', $businessId]],
                ['method' => 'with',  'args' => [['fh_kit.kit','lostBy:emp_id,emp_full_name']]],
                [
                    'method' => 'select',
                    'args' => [
                        'id',
                        'b_id',
                        'kit_id',
                        'lost_by',
                        'lost_qty',
                        'lost_date',
                        'lost_details',
                        'lost_note',
                        'lost_cost',
                        'status',
                        'created_at'
                    ],
                    'relation' => [],
                ]
            ];


            if (!empty($kitFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['kit_id', $kitFilter]];
            }

            if (!empty($employeeFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['lost_by', $employeeFilter]];
            }

            if ($statusFilter !== null && $statusFilter !== '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['status', $statusFilter]];
            }

            if (!empty($dateRange)) {
                $dates = explode(' - ', $dateRange);
                if (count($dates) == 2) {
                    $start = Carbon::parse(trim($dates[0]))->startOfDay();
                    $end   = Carbon::parse(trim($dates[1]))->endOfDay();

                    $dynamicConditions[] = [
                        'method' => 'whereBetween',
                        'args' => ['lost_date', [$start, $end]]
                    ];
                }
            }


            $searchColumns = [
                'lost_qty',
                'lost_details',
                'lost_note',
                'lost_cost',
                'status'
            ];

            $searchRelationships = [
                'kit' => ['name'],
                'lostBy' => ['emp_full_name']
            ];


            $helper = new DynamicModelDataTableHelper(
                new KitLost(),
                $dynamicConditions,
                $searchColumns,
                $searchRelationships
            );

            $list = $helper->getServerSideDataTable();


            $rowData = [];
            foreach ($list as $i => $d) {

                $actions = '
                <div class="dropdown">
                    <button class="btn btn-light btn-sm" data-bs-toggle="dropdown">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end p-2">

                            <li>
                            <a href="' . route('kits.show', $d->kit_id) . '"
                               class="dropdown-item text-primary">
                               <i class="bi bi-eye"></i> View Details
                            </a>
                        </li>

                    </ul>
                </div>
            ';

                $rowData[] = [
                    $i + 1,
                    e($d->fh_kit->kit->name ?? 'N/A'),
                    e($d->lostBy->emp_full_name ?? 'N/A'),
                    $d->lost_qty,
                    ($d->lost_date ? Carbon::parse($d->lost_date)->format('d M Y') : '-'),
                    e($d->lost_details),
                    e($d->lost_note),
                    $d->lost_cost,
                    ucfirst($d->status),
                    $actions
                ];
            }

            return response()->json([
                "draw"            => $request->draw,
                "recordsTotal"    => KitLost::where('b_id', $businessId)->count(),
                "recordsFiltered" => $helper->countFilteredServerSideDataTable(),
                "data"            => $rowData
            ]);
        }

        $columns = [
            ['name' => 'S. No.', 'width' => '4%'],
            ['name' => 'Kit Name', 'width' => '12%'],
            ['name' => 'Lost By', 'width' => '12%'],
            ['name' => 'Qty', 'width' => '8%'],
            ['name' => 'Date', 'width' => '10%'],
            ['name' => 'Details', 'width' => '14%'],
            ['name' => 'Note', 'width' => '12%'],
            ['name' => 'Cost', 'width' => '8%'],
            ['name' => 'Status', 'width' => '8%'],
            ['name' => 'Action', 'width' => '6%'],
        ];

        return view('admin.kits.lost', [
            'columns'   => $columns,


            'kits'      => Kit::where('b_id', $businessId)->orderBy('id', 'DESC')->get(),
            'employees' => Employee::where('emp_status', 71)->where('emp_b_id', $businessId)->where('emp_role_id', '!=', 1)->select('emp_id', 'emp_full_name', 'emp_code')->get(),

        ]);
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
            'description' => 'nullable|string',
            'note' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',

        ]);

        $existing = KitStock::where('b_id', $businessId)
            ->where('id', $kit_id)
            ->first();

        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => 'Stock record not found for this kit.'
            ], 404);
        }
        KitDamage::create([
            'b_id' => $businessId,
            'kit_id' => $kit_id,
            'damage_by' => $emp_id,
            'damage_qty' => $request->quantity,
            'damage_date' => now(),
            'damage_details' => $request->description ?? '',
            'damage_note' => $request->description ?? null,
            'damage_cost' => $request->cost ?? 0,
            'status' => 1,
        ]);

        KitLog::create([
            'b_id'           => $businessId,
            'kit_id'         => $kit_id,
            'action_type'    => 'DAMAGE',
            'reference_id'   => $existing->id,
            'opening_qty'    => 0,
            'available_qty'  => $existing->available_qty,
            'assigned_qty'   => 0,
            'damaged_qty'    => $request->quantity,
            'lost_qty'       => 0,
            'qty'            => $request->quantity,
            'replaced_qty'   => 0,
            'price_per_unit' => $existing->price_per_unit,
            'total_price'    => $existing->total_price,
            'note'           => $request->description ?? null,
            'action_by'      => $emp_id,
        ]);

        // Update stock quantities
        $existing->available_qty -= $request->quantity;
        if ($existing->available_qty < 0) $existing->available_qty = 0;
        $existing->damaged_qty += $request->quantity;
        $existing->total_price = $existing->available_qty * $existing->price_per_unit;
        $existing->save();

        return response()->json([
            'success' => true,
            'message' => 'Damage record saved successfully!'
        ]);
    }

    public function stkistloststorere(Request $request)
    {
        $user = Auth::user();
        $businessId = (int) $user->emp_b_id;
        $emp_id = $user->emp_id;
        $kit_id = (int) $request->id;

        $request->validate([
            'id' => 'required|exists:kits,id',
            'quantity' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            'note' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $existing = KitStock::where('b_id', $businessId)
            ->where('id', $kit_id)
            ->first();

        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => 'Stock record not found for this kit.'
            ], 404);
        }

        // Create lost record
        KitLost::create([
            'b_id' => $businessId,
            'kit_id' => $kit_id,
            'lost_by' => $emp_id,
            'lost_qty' => $request->quantity,
            'lost_date' => now(),
            'lost_details' => $request->description ?? '',
            'lost_note' => $request->description ?? '',
            'lost_cost' => $request->cost ?? 0,
            'status' => 1,
        ]);

        // Log the action
        KitLog::create([
            'b_id'           => $businessId,
            'kit_id'         => $kit_id,
            'action_type'    => 'LOST',
            'reference_id'   => $existing->id,
            'opening_qty'    => 0,
            'available_qty'  => $existing->available_qty,
            'assigned_qty'   => 0,
            'damaged_qty'    => 0,
            'lost_qty'       => $request->quantity,
            'qty'            => $request->quantity,
            'replaced_qty'   => 0,
            'price_per_unit' => $existing->price_per_unit,
            'total_price'    => $existing->total_price,
            'note'           => $request->description ?? null,
            'action_by'      => $emp_id,
        ]);

        // Update stock
        $existing->available_qty -= $request->quantity;
        if ($existing->available_qty < 0) $existing->available_qty = 0;
        $existing->lost_qty += $request->quantity;
        $existing->total_price = $existing->available_qty * $existing->price_per_unit;
        $existing->save();

        return response()->json([
            'success' => true,
            'message' => 'Kit lost record saved successfully!'
        ]);
    }
}
