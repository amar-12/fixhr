<?php

namespace App\Http\Controllers\Web\Admin\Kit;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Kit;
use App\Models\KitAssignment;
use App\Models\KitLog;
use App\Models\KitStock;
use App\Models\StockCategory;
use App\Models\StockUnit;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\Category;

class KitController extends Controller
{
    public function index(Request $request)
    {
        $businessId = Auth::user()->emp_b_id;


        $total_kits    = KitStock::where('b_id', $businessId)->sum('available_qty');
        $assigned_kits = KitStock::where('b_id', $businessId)->sum('assigned_qty');
        $damaged_kits  = KitStock::where('b_id', $businessId)->sum('damaged_qty');
        $lost_kits     = KitStock::where('b_id', $businessId)->sum('lost_qty');
        $replaced_kits = KitStock::where('b_id', $businessId)->sum('replaced_qty');
        $in_stock_kits = $total_kits;
        $total_kits = $total_kits + $assigned_kits + $damaged_kits + $lost_kits + $replaced_kits;

        $kits_data     = Kit::with(['fh_category', 'fh_unit', 'stock'])->where('b_id', $businessId)->orderBy('id', 'DESC')->get();
        $categories    = StockCategory::where('sc_b_id', $businessId)->get();
        $units         = StockUnit::where('su_b_id', $businessId)->get();

        $employee = Employee::where('emp_status', 71)->where('emp_b_id', $businessId)->where('emp_role_id', '!=', 1)->select('emp_id', 'emp_full_name', 'emp_code')->get();
        $kitFilter = $request->kitFilter;
        $fromDate  = $request->fromDate;

        if ($request->ajax()) {
            $dynamicConditions = [
                ['method' => 'where', 'args' => ['b_id', $businessId]],
                [
                    'method' => 'with',
                    'args' => [[
                        'kit:id,name,type,size,kt_category_id,kt_unit_id',
                        'kit.fh_category:sc_id,sc_name',
                        'kit.fh_unit:su_id,su_name'
                    ]]
                ],
                [
                    'method' => 'select',
                    'args'   => [
                        'id',
                        'kit_id',
                        'opening_qty',
                        'available_qty',
                        'assigned_qty',
                        'damaged_qty',
                        'lost_qty',
                        'replaced_qty',
                        'note',
                        'b_id',
                        'created_at'
                    ],
                    'relation' => [],
                ]
            ];

            if (!empty($kitFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['kit_id', $kitFilter]];
            }

            if (!empty($fromDate)) {

                $dates = explode(' - ', $fromDate);

                if (count($dates) == 2) {

                    $startDate = Carbon::parse(trim($dates[0]))->startOfDay();
                    $endDate   = Carbon::parse(trim($dates[1]))->endOfDay();

                    $dynamicConditions[] = [
                        'method' => 'whereBetween',
                        'args'   => ['created_at', [$startDate, $endDate]]
                    ];
                }
            }
            $searchColumns = [];
            $searchRelationships = [
                'kit' => ['name'],
            ];

            $helper = new DynamicModelDataTableHelper(
                eloquentModel: new KitStock(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            );
            $list = $helper->getServerSideDataTable();
            $rowData = [];
            foreach ($list as $i => $s) {
                // $actions = '
                // <div class="dropdown">
                //     <button class="btn btn-light btn-sm" data-bs-toggle="dropdown">
                //         <i class="fa fa-ellipsis-v"></i>
                //     </button>
                //     <ul class="dropdown-menu dropdown-menu-end p-2">

                //         <li>
                //             <a href="' . route('kits.show', $s->id) . '"
                //                class="dropdown-item text-primary">
                //                <i class="bi bi-eye"></i> View Details
                //             </a>
                //         </li>


                //         <li>
                //             <a href="javascript:void(0)"
                //                class="dropdown-item text-danger deleteBtn"
                //                data-id="' . $s->id . '">
                //                <i class="bi bi-trash"></i> Delete
                //             </a>
                //         </li>

                //         <li>
                //             <a href="javascript:void(0)"
                //                class="dropdown-item text-primary assignedBtn"
                //                data-id="' . $s->id . '"
                //                data-kit="' . e($s->kit->name ?? '') . '"
                //                data-total="' . $s->available_qty . '">
                //                <i class="bi bi-check-circle"></i> Assigned
                //             </a>
                //         </li>

                //         <li>
                //             <a href="javascript:void(0)"
                //                class="dropdown-item text-warning damagedBtn"
                //                data-id="' . $s->id . '"
                //                data-kit="' . e($s->kit->name ?? '') . '"
                //                data-total="' . $s->available_qty . '">
                //                <i class="bi bi-exclamation-circle"></i> Damaged
                //             </a>
                //         </li>

                //         <li>
                //             <a href="javascript:void(0)"
                //                class="dropdown-item text-secondary lostBtn"
                //                data-id="' . $s->id . '"
                //                data-kit="' . e($s->kit->name ?? '') . '"
                //                data-total="' . $s->available_qty . '">
                //                <i class="bi bi-x-circle"></i> Lost
                //             </a>
                //         </li>

                //         <li>
                //             <a href="javascript:void(0)"
                //                class="dropdown-item text-success replacedBtn"
                //                data-id="' . $s->id . '"
                //                data-kit="' . e($s->kit->name ?? '') . '"
                //                data-total="' . $s->available_qty . '">
                //                <i class="bi bi-arrow-repeat"></i> Replaced
                //             </a>
                //         </li>
                //     </ul>
                // </div>
                //  ';

                 $actions = '
                <div class="dropdown">
                    <button class="btn btn-light btn-sm" data-bs-toggle="dropdown">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end p-2">

                        <li>
                            <a href="' . route('kits.show', $s->id) . '"
                               class="dropdown-item text-primary">
                               <i class="bi bi-eye"></i> View Details
                            </a>
                        </li>

                        <li>
                            <a href="javascript:void(0)"
                               class="dropdown-item text-danger deleteBtn"
                               data-id="' . $s->id . '">
                               <i class="bi bi-trash"></i> Delete
                            </a>
                        </li>


                        <li>
                            <a href="javascript:void(0)"
                               class="dropdown-item text-warning damagedBtn"
                               data-id="' . $s->id . '"
                               data-kit="' . e($s->kit->name ?? '') . '"
                               data-total="' . $s->available_qty . '">
                               <i class="bi bi-exclamation-circle"></i> Damaged
                            </a>
                        </li>

                        <li>
                            <a href="javascript:void(0)"
                               class="dropdown-item text-secondary lostBtn"
                               data-id="' . $s->id . '"
                               data-kit="' . e($s->kit->name ?? '') . '"
                               data-total="' . $s->available_qty . '">
                               <i class="bi bi-x-circle"></i> Lost
                            </a>
                        </li>

                        <li>
                            <a href="javascript:void(0)"
                               class="dropdown-item text-success replacedBtn"
                               data-id="' . $s->id . '"
                               data-kit="' . e($s->kit->name ?? '') . '"
                               data-total="' . $s->available_qty . '">
                               <i class="bi bi-arrow-repeat"></i> Replaced
                            </a>
                        </li>
                    </ul>
                </div>
                 ';

                $rowData[] = [
                    $i + 1,
                    e($s->kit->name ?? 'N/A'),
                    e($s->kit->fh_unit->su_name ?? 'N/A'),
                    e($s->kit->fh_category->sc_name ?? 'N/A'),
                    e($s->kit->size ?? 'N/A'),
                    $s->opening_qty,
                    $s->available_qty,
                    $s->assigned_qty,
                    $s->damaged_qty,
                    $s->lost_qty,
                    $s->replaced_qty,
                    ($s->created_at ? $s->created_at->format('d M Y') : "-"),
                    $actions
                ];
            }

            return response()->json([
                "draw"            => $request->draw,
                "recordsTotal"    => KitStock::where('b_id', $businessId)->count(),
                "recordsFiltered" => $helper->countFilteredServerSideDataTable(),
                "data"            => $rowData
            ]);
        }

        $columns = [
            ['name' => 'S. No.',        'width' => '4%'],
            ['name' => 'Name',      'width' => '12%'],
            ['name' => 'Type',      'width' => '10%'],
            ['name' => 'Category',  'width' => '10%'],
            ['name' => 'Size',      'width' => '7%'],
            ['name' => 'Opening',   'width' => '7%'],
            ['name' => 'Available', 'width' => '7%'],
            ['name' => 'Assigned',  'width' => '7%'],
            ['name' => 'Damaged',   'width' => '7%'],
            ['name' => 'Lost',      'width' => '7%'],
            ['name' => 'Replaced',  'width' => '7%'],
            ['name' => 'Date',  'width' => '12%'],
            ['name' => 'Action',        'width' => '6%'],
        ];

        return view('admin.kits.stock', compact(
            'columns',
            'kits_data',
            'total_kits',
            'assigned_kits',
            'damaged_kits',
            'lost_kits',
            'replaced_kits',
            'employee',
            'in_stock_kits',
            'categories',
            'units',
        ));
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'size'         => 'required|string|max:50',
            'category_id'  => 'required|exists:stock_categories,sc_id',
            'unit_id'      => 'required|exists:stock_units,su_id',
            'per_unit_price' => 'nullable|numeric|min:0',
            'total_qty'      => 'nullable|numeric|min:0',
        ]);

        try {

            $user = Auth::user();

            // GET ID FROM BOTH POSSIBLE NAMES
            $updateId = $request->id ?? $request->item_id;

            if ($updateId) {
                // UPDATE
                $kit = Kit::findOrFail($updateId);
                $kitCode = $kit->kit_code;
            } else {
                // CREATE
                $kitCode = DB::transaction(function () {
                    $last = Kit::select('kit_code')
                        ->where('kit_code', 'LIKE', 'KIT-%')
                        ->orderByRaw("CAST(SUBSTRING(kit_code, 5) AS UNSIGNED) DESC")
                        ->lockForUpdate()
                        ->first();

                    $nextNumber = $last ? ((int)substr($last->kit_code, 4) + 1) : 1;

                    return 'KIT-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
                });

                $kit = new Kit();
                $kit->kit_code = $kitCode;
                $kit->created_by = $user->emp_id;
            }

            // COMMON VALUES
            $kit->name = $validated['name'];
            $kit->size = $validated['size'];
            $kit->kt_category_id = $validated['category_id'];
            $kit->kt_unit_id = $validated['unit_id'];
            $kit->description = $request->description ?? null;
            $kit->b_id = $user->emp_b_id;
            $kit->updated_by = $user->emp_id;

            $kit->save();

            return response()->json([
                'success' => true,
                'message' => $updateId ? 'Kit updated successfully!' : 'Kit created successfully!',
                'kit' => $kit
            ]);
        } catch (\Exception $e) {
            Log::error("Kit Create/Update Failed", ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }


    public function edit($id)
    {
        try {
            $kit = Kit::where('id', $id)
                ->where('b_id', auth()->user()->emp_b_id) // Security: only own branch
                ->firstOrFail();

            return response()->json($kit);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kit not found or access denied.'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'item_id' => 'required|exists:stock_items,id',
            'name' => 'required|string|max:255',
            'size' => 'required|string|max:50',
            'category_id' => 'required|exists:stock_categories,sc_id',
            'unit_id' => 'required|exists:stock_units,su_id'
        ]);

        try {
            $user = Auth::user();

            // Get kit with branch check
            $kit = Kit::where('id', $id)
                ->where('b_id', $user->emp_b_id)
                ->firstOrFail();

            // Calculate updated total value
            $totalValue = $request->per_unit_price * $request->total_qty;

            // Update data
            $kit->update([
                'name'           => $request->name,
                'size' => $request->size,
                'category_id' => $request->category_id,
                'unit_id' => $request->unit_id,
                'description'    => $request->description,
                'per_unit_price' => $request->per_unit_price,
                'total_qty'      => $request->total_qty,
                'total_value'    => $totalValue,
                'status'         => $request->status ?? $kit->status,
                'updated_by'     => $user->emp_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kit updated successfully!',
                'kit'     => $kit->fresh()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kit not found or you do not have permission.'
            ], 404);
        } catch (\Exception $e) {

            Log::error('Kit Update Failed', [
                'kit_id'  => $id,
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update kit. Please try again.'
            ], 500);
        }
    }

    public function destroy($id)
    {

        try {
            $user = Auth::user();

            $kit = Kit::where('id', $id)
                ->where('b_id', $user->emp_b_id)
                ->firstOrFail();

            $kit->delete(); // Soft delete (if enabled)

            return response()->json([
                'success' => true,
                'message' => 'Kit deleted successfully!'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Kit not found or access denied.'
            ], 404);
        } catch (\Exception $e) {

            Log::error('Kit Delete Failed', [
                'kit_id'  => $id,
                'user_id' => auth()->id(),
                'error'   => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete kit.'
            ], 500);
        }
    }


    public function show($id)
    {
        $user = Auth::user();
        $b_id = $user->emp_b_id;

        $kit_data = KitStock::with([
            'kit.fh_category',
            'kit.fh_unit'
        ])
            ->where('b_id', $b_id)
            ->where('id', $id)
            ->firstOrFail();

        $kit_id = $kit_data->id;

        // dd($kit_data);

        $kit_history = KitLog::where('b_id', $b_id)
            ->where('kit_id', $kit_id)
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.kits.show', compact('kit_data', 'kit_history'));
    }


    public function returnQty(Request $request)
    {

        $request->validate([
            'id' => 'required|integer|exists:kit_assignments,id',
            'return_qty' => 'required|integer|min:1',
            'return_condition' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $user = Auth::user();
        $businessId = (int) $user->emp_b_id;
        $emp_id = $user->emp_id;

        // Find assignment
        $assign = KitAssignment::where('b_id', $businessId)
            ->where('id', $request->id)
            ->first();

        if (!$assign) {
            return response()->json([
                'success' => false,
                'message' => "Assignment record not found!"
            ]);
        }

        // Validate return qty
        if ($request->return_qty > $assign->assigned_qty) {
            return response()->json([
                'success' => false,
                'message' => "Return qty cannot exceed remaining assigned qty!"
            ]);
        }

        if ($assign->assigned_qty <= 0) {
            return response()->json([
                'success' => false,
                'message' => "No qty left to return."
            ]);
        }

        // Fetch stock
        $stock = KitStock::where('b_id', $businessId)
            ->where('id', $assign->kit_id)
            ->first();

        if (!$stock) {
            return response()->json([
                'success' => false,
                'message' => "Stock record not found!"
            ]);
        }

        // ----------------------------------
        // ✔ UPDATE ASSIGNMENT RECORD
        // ----------------------------------
        $assign->return_qty += $request->return_qty;
        $assign->assigned_qty -= $request->return_qty;
        $assign->return_condition = $request->return_condition;
        $assign->remarks = $request->remarks ?? '';
        $assign->save();

        // ----------------------------------
        // ✔ UPDATE STOCK
        // ----------------------------------
        $stock->available_qty += $request->return_qty;
        $stock->assigned_qty -= $request->return_qty;
        $stock->total_price = $stock->available_qty * $stock->price_per_unit;
        $stock->save();

        // ----------------------------------
        // ✔ LOG ENTRY (same format as assignment log)
        // ----------------------------------
        KitLog::create([
            'b_id'           => $businessId,
            'kit_id'         => $assign->kit_id,
            'action_type'    => 'RETURNED',
            'reference_id'   => $assign->id,
            'opening_qty'    => 0,
            'available_qty'  => $stock->available_qty,
            'assigned_qty'   => $assign->assigned_qty,
            'damaged_qty'    => 0,
            'lost_qty'       => 0,
            'qty'            => $request->return_qty,
            'replaced_qty'   => 0,
            'price_per_unit' => $stock->price_per_unit,
            'total_price'    => $stock->total_price,
            'note'           => $request->remarks ?? '',
            'action_by'      => $emp_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Return quantity processed successfully!",
            'data' => [
                'assignment' => $assign,
                'stock' => $stock
            ]
        ]);
    }


    public function lostQty(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'lost_qty' => 'required|integer|min:1',
            'reason' => 'required|string',
        ]);

        $kit = KitAssignment::find($request->id);

        if (!$kit) {
            return response()->json([
                'success' => false,
                'message' => "Kit not found!"
            ]);
        }

        if ($request->lost_qty > $kit->assigned_qty) {
            return response()->json([
                'success' => false,
                'message' => "Lost qty cannot exceed assigned qty!"
            ]);
        }

        $kit->lost_qty += $request->lost_qty;
        $kit->assigned_qty -= $request->lost_qty;
        $kit->lost_reason = $request->reason;
        $kit->remarks = $request->remarks;
        $kit->save();

        return response()->json([
            'success' => true,
            'message' => "Lost quantity updated successfully!"
        ]);
    }

    public function units_save(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $request->validate([
            'su_name' => [
                'required',
                'string',
                'max:255',
                // Unique per business
                \Illuminate\Validation\Rule::unique('stock_units', 'su_name')
                    ->where(function ($query) use ($businessId, $request) {
                        $query->where('su_b_id', $businessId)
                            ->where('su_id', '<>', $request->su_id ?? 0);
                    }),
            ],
            'su_short_name' => 'nullable|string|max:50',
            'su_description' => 'nullable|string'
        ]);

        $unit = StockUnit::updateOrCreate(
            ['su_id' => $request->su_id],
            [
                'su_name' => $request->su_name,
                'su_b_id' => $businessId,
                'su_short_name' => $request->su_short_name,
                'su_description' => $request->su_description,
                'su_status' => 1
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => $request->su_id ? 'Unit updated successfully!' : 'Unit added successfully!'
        ]);
    }


    public function categories_save(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $request->validate([
            'sc_name' => [
                'required',
                'string',
                'max:255',
                // Unique per business
                \Illuminate\Validation\Rule::unique('stock_categories', 'sc_name')
                    ->where(function ($query) use ($businessId, $request) {
                        $query->where('sc_b_id', $businessId)
                            ->where('sc_id', '<>', $request->id ?? 0);
                    }),
            ],
            'sc_description' => 'nullable|string'
        ]);

        $category = StockCategory::updateOrCreate(
            ['sc_id' => $request->id],
            [
                'sc_name' => $request->sc_name,
                'sc_b_id' => $businessId,
                'sc_description' => $request->sc_description,
                'sc_status' => 1
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => $request->id ? 'Category updated successfully!' : 'Category added successfully!'
        ]);
    }
}
