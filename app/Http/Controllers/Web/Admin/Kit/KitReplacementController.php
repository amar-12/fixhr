<?php

namespace App\Http\Controllers\Web\Admin\Kit;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Kit;
use App\Models\KitLog;
use App\Models\KitStock;
use App\Models\KitReplacement;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KitReplacementController extends Controller
{


    public function index(Request $request)
    {
        $businessId = Auth::user()->emp_b_id;

        $kitFilter = $request->kit_id;
        $employeeFilter = $request->employee_id;
        $dateRange = $request->dateRange;

        if ($request->ajax()) {
            $dynamicConditions = [
                ['method' => 'where', 'args' => ['b_id', $businessId]],
                ['method' => 'with',  'args' => [['fh_kit.kit', 'replacedBy:emp_id,emp_full_name']]],
                [
                    'method' => 'select',
                    'args' => [
                        'id',
                        'b_id',
                        'kit_id',
                        'replaced_item',
                        'replacement_qty',
                        'replace_note',
                        'cost',
                        'replaced_by',
                        'replace_date',
                        'created_at'
                    ],
                    'relation' => [],
                ]
            ];

            if (!empty($kitFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['kit_id', $kitFilter]];
            }

            if (!empty($employeeFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['replaced_by', $employeeFilter]];
            }

            if (!empty($dateRange)) {
                $dates = explode(' - ', $dateRange);
                if (count($dates) == 2) {
                    $start = Carbon::parse(trim($dates[0]))->startOfDay();
                    $end   = Carbon::parse(trim($dates[1]))->endOfDay();

                    $dynamicConditions[] = [
                        'method' => 'whereBetween',
                        'args' => ['replace_date', [$start, $end]]
                    ];
                }
            }

            $searchColumns = [
                'replaced_item',
                'replace_note',
                'replacement_qty',
                'cost',
            ];

            $searchRelationships = [
                'kit' => ['kit_name'],
                'replacedBy' => ['emp_full_name']
            ];

            $helper = new DynamicModelDataTableHelper(
                new KitReplacement(),
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
                    e($d->replaced_item),
                    $d->replacement_qty,
                    $d->cost,
                    e($d->replacedBy->emp_full_name ?? 'N/A'),
                    ($d->replace_date ? Carbon::parse($d->replace_date)->format('d M Y') : '-'),
                    e($d->replace_note),
                    $actions
                ];
            }

            return response()->json([
                "draw"            => $request->draw,
                "recordsTotal"    => KitReplacement::where('b_id', $businessId)->count(),
                "recordsFiltered" => $helper->countFilteredServerSideDataTable(),
                "data"            => $rowData
            ]);
        }

        $columns = [
            ['name' => 'S. No.', 'width' => '4%'],
            ['name' => 'Kit Name', 'width' => '12%'],
            ['name' => 'Replaced Item', 'width' => '14%'],
            ['name' => 'Qty', 'width' => '8%'],
            ['name' => 'Cost', 'width' => '8%'],
            ['name' => 'Replaced By', 'width' => '12%'],
            ['name' => 'Date', 'width' => '10%'],
            ['name' => 'Note', 'width' => '15%'],
            ['name' => 'Action', 'width' => '7%'],
        ];

        return view('admin.kits.replaced', [
            'columns'   => $columns,
            'kits'      => Kit::where('b_id', $businessId)->orderBy('id', 'DESC')->get(),
            'employees' => Employee::where('emp_status', 71)->where('emp_b_id', $businessId)->where('emp_role_id', '!=', 1)->select('emp_id', 'emp_full_name', 'emp_code')->get(),
        ]);
    }


    public function store(Request $request)
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

        KitReplacement::create([
            'b_id' => $businessId,
            'kit_id' => $kit_id,
            'replaced_item' => $request->description ?? '',
            'replacement_qty' => $request->quantity,
            'replace_date' => now(),
            'replaced_by' => $emp_id,
            'cost' => 0,
        ]);

        KitLog::create([
            'b_id'           => $businessId,
            'kit_id'         => $kit_id,
            'action_type'    => 'REPLACEMENT',
            'reference_id'   => $existing->id,
            'opening_qty'    => 0,
            'available_qty'  => $existing->available_qty,
            'assigned_qty'   => 0,
            'damaged_qty'    => 0,
            'lost_qty'       => 0,
            'qty'            => $request->quantity,
            'replaced_qty'   => $request->quantity,
            'price_per_unit' => $existing->price_per_unit,
            'total_price'    => $existing->total_price,
            'note'           => $request->description ?? null,
            'action_by'      => $emp_id,
        ]);

        $existing->available_qty -= $request->quantity;
        if ($existing->available_qty < 0) $existing->available_qty = 0;
        $existing->replaced_qty  += $request->quantity;
        $existing->total_price    = $existing->available_qty * $existing->price_per_unit;
        $existing->save();

        return response()->json([
            'success' => true,
            'message' => 'Replacement saved successfully!'
        ]);
    }
}
