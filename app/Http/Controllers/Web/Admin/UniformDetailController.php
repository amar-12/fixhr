<?php

namespace App\Http\Controllers\Web\Admin;

use App\Exports\UniformDetailsExport;
use App\Http\Controllers\Controller;
use App\Imports\UniformDetailsImport;
use App\Models\Employee;
use App\Models\KitAssignment;
use App\Models\KitLog;
use App\Models\KitStock;
use App\Models\MasterTable;
use App\Models\UniformDetail;
use App\Models\UniformIssue;
use App\Models\UniformItem;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;

class UniformDetailController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index(Request $request)
    {

        $user =  Auth::user();
        $business_id = $user->emp_b_id;
        $employee_list = Employee::where('emp_b_id', $business_id)

            ->where('emp_role_id', '!=', 1)
            ->where('emp_status', '!=', 72)
            ->select('emp_full_name', 'emp_id', 'emp_code')
            ->get();

        $employees = Employee::where('emp_b_id', $business_id)->where('emp_status', '!=', 72)
            ->select('emp_full_name', 'emp_id', 'emp_code')
            ->get();
        // dd($employee_list);


        $ud_status_Filter = $request->ud_status_Filter;
        $emp_Filter = $request->emp_Filter;


        if ($this->user) {
            if ($request->ajax()) {
                $dynamicConditions = [
                    [
                        'method' => 'select',
                        'args' => [
                            'ud_id',
                            'ud_emp_id',
                            'ud_b_id',
                            'ud_uniform_type',
                            'ud_shirt_size',
                            'ud_pant_size',
                            'ud_shoe_size',
                            'ud_headgear_type',
                            'ud_headgear_size',
                            'ud_issue_date',
                            'ud_replacement_due_date',
                            'ud_status',
                            'ud_issued_by',
                            'ud_photo_path',
                            'ud_remarks',
                            'ud_uniform_color',
                            'ud_shirt_color',
                            'ud_pant_color',
                            'ud_shoe_color',
                            'ud_headgear_color'
                        ],
                        'relation' => []
                    ],
                    [
                        'method' => 'orderBy',
                        'args' => ['ud_issue_date', 'desc'],
                        'relation' => []
                    ]
                ];


                // Filter conditions
                if ($ud_status_Filter != '') {
                    $dynamicConditions[] = ['method' => 'where', 'args' => ['ud_status', $ud_status_Filter]];
                }

                if ($emp_Filter != '') {
                    $dynamicConditions[] = ['method' => 'where', 'args' => ['ud_emp_id', $emp_Filter]];
                }




                $searchColumns = [
                    'ud_emp_id',
                    'ud_b_id',
                    'ud_uniform_type',
                    'ud_shirt_size',
                    'ud_pant_size',
                    'ud_shoe_size',
                    'ud_headgear_type',
                    'ud_headgear_size',
                    'ud_issue_date',
                    'ud_replacement_due_date',
                    'ud_issued_by',
                    'ud_status',
                    'ud_remarks'
                ];


                $searchRelationships = [
                    'employee' => ['emp_full_name'],
                    'employee' => ['emp_code'],
                ];


                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new UniformDetail(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                    searchRelationships: $searchRelationships
                ))->getServerSideDataTable();

                // dd($list);

                $rowData = [];
                $i = 0;
                foreach ($list as $key => $val) {
                    $i++;
                    $row = [];
                    $row[] = $i;
                    $row[] = $val->employee->emp_code;
                    $row[] = $val->employee->emp_full_name;
                    $row[] = $val->ud_uniform_type;
                    $row[] = $val->ud_shirt_size;
                    $row[] = $val->ud_pant_size;
                    $row[] = $val->ud_shoe_size;
                    $row[] = $val->ud_headgear_type;
                    $row[] = $val->ud_headgear_size;
                    $row[] = \Carbon\Carbon::parse($val->ud_issue_date)->format('d-M-Y');
                    $row[] = \Carbon\Carbon::parse($val->ud_replacement_due_date)->format('d-M-Y');
                    $row[] =  $val->employee->emp_full_name;
                    // $row[] = $val->ud_status;

                    $row[] = $val->ud_status == "New"
                        ? '<span class="badge bg-success">New</span>'
                        : '<span class="badge bg-secondary">Replace</span>';


                    // // dd($val->ud_photo_path);
                    // $row[] = $val->ud_remarks
                    //     ? '<img src="' . asset($val->ud_photo_path) . '" alt="Image" width="60" height="60" style="object-fit: cover;" />'
                    //     : '-';

                    // Action dropdown
                    $row[] = '
                    <div class="btn-list ms-3">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                <li>
                                    <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-uniform"
                                        type="button"
                                        data-id="' . $val->ud_id . '"
                                        data-emp_id="' . $val->ud_emp_id . '"
                                        data-emp_name="' . $val->employee->emp_full_name . ' - ' . $val->employee->emp_code . '"
                                        data-b_id="' . $val->ud_b_id . '"

                                        data-uniform_type="' . $val->ud_uniform_type . '"
                                        data-shirt_size="' . $val->ud_shirt_size . '"
                                        data-pant_size="' . $val->ud_pant_size . '"
                                        data-shoe_size="' . $val->ud_shoe_size . '"
                                        data-headgear_type="' . $val->ud_headgear_type . '"
                                        data-headgear_size="' . $val->ud_headgear_size . '"
                                        data-issue_date="' . \Carbon\Carbon::parse($val->ud_issue_date)->format('Y-m-d') . '"
                                        data-replacement_due_date="' . \Carbon\Carbon::parse($val->ud_replacement_due_date)->format('Y-m-d') . '"
                                        data-issued_by="' . $val->ud_issued_by . '"
                                        data-remarks="' . $val->ud_remarks . '"
                                        data-photo="' . $val->ud_photo_path . '"
                                        data-ud_status="' . $val->ud_status . '"

                                        data-ud_uniform="' . $val->ud_uniform_color . '"
                                        data-ud_shirt="' . $val->ud_shirt_color . '"
                                        data-ud_pant="' . $val->ud_pant_color . '"
                                        data-ud_shoe="' . $val->ud_shoe_color . '"
                                        data-ud_headgear="' . $val->ud_headgear_color . '"
                                    >
                                        <i class="feather feather-edit"></i> Edit
                                    </button>
                                </li>

                                <li>
                                        <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-uniform"
                                            type="button"
                                            data-id="' . $val->ud_id . '">
                                            <i class="feather feather-trash-2"></i> Delete
                                        </button>
                                </li>

                              <li>
                                <a class="dropdown-item text-info fw-semibold d-flex align-items-center gap-2" href="' . asset($val->ud_photo_path) . '" target="_blank">
                                    <i class="feather feather-eye"></i> View
                                </a>
                            </li>


                            </ul>
                        </div>
                    </div>
                ';
                    $rowData[] = $row;
                }

                $output = [
                    "draw" => $request->input('draw'),
                    "recordsTotal" => sizeof($list),
                    "recordsFiltered" => (new DynamicModelDataTableHelper(
                        eloquentModel: new UniformDetail(),
                        dynamicConditions: $dynamicConditions,
                    ))->countFilteredServerSideDataTable(),
                    "data" => $rowData,
                ];

                return response()->json($output);
            }

            $columns = [
                'S. No.',
                'Emp. Code',
                'Emp. Name',
                'Uniform Type',
                'Shirt Size',
                'Pant Size',
                'Shoe Size',
                'Headgear Type',
                'Headgear Size',
                'Issue Date',
                'Replacement Due',
                'Issued By',
                'Status',
                'Action',

            ];

            $uniformDetails = UniformDetail::select('ud_id', 'ud_emp_id', 'ud_b_id', 'ud_uniform_type', 'ud_shirt_size', 'ud_pant_size', 'ud_shoe_size', 'ud_headgear_type', 'ud_headgear_size', 'ud_issue_date', 'ud_replacement_due_date', 'ud_issued_by', 'ud_remarks')
                ->orderBy('ud_issue_date', 'desc')
                ->get();
            // dd($uniformDetails);

            return view('admin.employees.uniform_details.index', compact('uniformDetails', 'columns', 'employee_list', 'employees'));
        } else {
            abort(404);
        }
    }

    public function storeOrUpdate(Request $request, $ud_id = null)
    {
        $validated = $request->validate(
            [
                'ud_emp_id' => 'required|integer|unique:uniform_details,ud_emp_id',
                'ud_uniform_type' => 'required|string',
                'ud_shirt_size' => 'required|string',
                'ud_pant_size' => 'required|string',
                'ud_shoe_size' => 'required|string',
                'ud_headgear_type' => 'required|string',
                'ud_headgear_size' => 'nullable|string',
                'ud_issue_date' => 'required|date',
                'ud_replacement_due_date' => 'nullable|date',
                'ud_issued_by' => 'required|string',
                'ud_remarks' => 'nullable|string',
                'ud_status' => 'required|string',
                'ud_photo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'ud_uniform_color' => 'nullable|string',
                'ud_shirt_color' => 'nullable|string',
                'ud_pant_color' => 'nullable|string',
                'ud_shoe_color' => 'nullable|string',
                'ud_headgear_color' => 'nullable|string',
            ],
            [
                'ud_emp_id.unique' => 'This Employee has already been taken.',
            ]
        );

        $validated['ud_b_id'] = Auth::user()->emp_b_id;

        // If updating, get existing record
        $uniform = $ud_id ? UniformDetail::findOrFail($ud_id) : new UniformDetail();

        // Handle image upload
        if ($request->hasFile('ud_photo_path')) {
            $file = $request->file('ud_photo_path');
            $filename = $file->getClientOriginalName();
            $file->move(public_path('upload'), $filename);
            $validated['ud_photo_path'] = 'upload/' . $filename;
        } elseif ($ud_id) {
            // Preserve existing photo during update
            $validated['ud_photo_path'] = $uniform->ud_photo_path;
        }

        // Set prefix on create
        if (!$ud_id) {
            $lastId = UniformDetail::max('ud_id') ?? 0;
            $validated['ud_prefix'] = 'UD' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        }

        // Save record
        $uniform->fill($validated)->save();

        return response()->json([
            'success' => $ud_id ? 'Uniform updated successfully.' : 'Uniform created successfully.',
        ]);
    }

    public function destroy($id)
    {
        // dd($id);
        try {
            $uniform = UniformDetail::findOrFail($id);
            $uniform->delete();

            return response()->json(['success' => 'Uniform deleted successfully.']);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json(['error' => 'Delete failed.'], 500);
        }
    }

    public function export()
    {
        return Excel::download(new UniformDetailsExport, 'uniform_details.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,csv',
        ]);

        $file = $request->file('import_file');
        $uniformImport = new UniformDetailsImport(Auth::user());

        try {
            Excel::import($uniformImport, $file);
            $errorMessages = $uniformImport->getErrorMessages();
            if (!empty($errorMessages)) {
                session()->put('import_errors', $errorMessages);
                session()->flash('import_errors_blade', $errorMessages);
                return back()->with('error', 'Some rows failed to import. Please check the error file.');
            }

            return back()->with('success', 'Uniform details imported successfully.');
        } catch (\Exception $e) {
            // dd("Hello2");

            \Log::error('Uniform Import Failed: ' . $e->getMessage());
            return back()->with('error', 'Import failed. Please check your file and try again.');
        }
    }

    public function uniform_index(Request $request)
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;

        // Ensure authenticated user exists
        if (!$user) {
            abort(404);
        }

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['ui_b_id', $business_id],
                ],
                [
                    'method' => 'select',
                    'args' => [
                        'ui_id',
                        'ui_issued_by',
                        'ui_total',
                        'ui_payable',
                        'ui_waived',
                        'created_at',
                    ],
                    'relation' => []
                ],
            ];

            $searchColumns = [
                'ui_id',
                'ui_issued_by',
                'ui_total',
                'ui_payable',
                'ui_waived',
            ];

            $searchRelationships = [
                'employee' => ['emp_full_name', 'emp_code'],
            ];

            $datatableHelper = new DynamicModelDataTableHelper(
                new UniformIssue(),
                $dynamicConditions,
                $searchColumns,
                $searchRelationships
            );

            $list = $datatableHelper->getServerSideDataTable();

            // dd($list);

            $rowData = [];
            foreach ($list as $index => $val) {
                $latestUitIssueDate = $val->items->max('uit_issues_date');
                $rowData[] = [
                    $index + 1,
                    $val->employee->emp_code ?? '-',
                    '<a href="javascript:void(0);" class="view-academic-btn" data-id="' . $val->ui_id . '">' .
                        ($val->employee->emp_full_name ?? '-') .
                        '</a>',
                    $latestUitIssueDate ? Carbon::parse($latestUitIssueDate)->format('j M Y') : '-',
                    $val->ui_total ?? '-',
                    $val->ui_waived ?? '-',
                    $val->ui_payable ?? '-',
                    '
                    <div class="btn-list ms-3">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                <li>
                                    <a href="' . route('uniform_index.edit', $val->ui_id)  . '" class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2">
                                        <i class="feather feather-edit"></i> Edit
                                    </a>
                                </li>

                                <li>
                                    <a href="javascript:void(0)"
                                    class="dropdown-item text-danger uniformDeleteBtn"
                                    data-id="' . $val->ui_id . '">
                                    <i class="bi bi-trash"></i> Delete
                                    </a>
                                </li>


                            </ul>
                        </div>
                    </div>'
                ];
            }

            return response()->json([
                "draw" => $request->input('draw'),
                "recordsTotal" => count($list),
                "recordsFiltered" => $datatableHelper->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ]);
        }

        // Table Headers - Aligned with actual data
        $columns = [
            'S. No.',
            'Emp. Code',
            'Emp. Name',
            'Issue date',
            'Total Amount',
            'Waived Amount',
            'Payable Amount',
            'Action',
        ];

        // Fetch latest academic/uniform issue details
        $academicDetails = UniformIssue::select(
            'ui_id',
            'ui_issued_by',
            'ui_total',
            'ui_payable',
            'ui_waived'
        )->orderBy('updated_at', 'desc')->first();

        // Return view (clean up passed variables to only defined ones)
        return view('admin.employees.uniform_detail.index', compact(
            'academicDetails',
            'columns'
        ));
    }

    // public function uniform_create(Request $request)
    // {

    //     $uniform_type = MasterTable::where('m_group', 'uniform_type')->get();
    //     $uniform_dce = MasterTable::where('m_group', 'uniform_dce')->get();
    //     // dd($material);

    //     $user = Auth::user();
    //     $business_id = $user->emp_b_id;
    //     $query = Employee::where('emp_b_id', $business_id)
    //         ->where('emp_status', '!=', 72)
    //         ->where('emp_role_id', '!=', 1)
    //         ->select('emp_id', 'emp_full_name', 'emp_code');

    //         if ($request->filled('emp_id')) {
    //         try {
    //             $emp_id = Crypt::decrypt($request->emp_id);
    //             $query->where('emp_id', $emp_id);
    //         } catch (DecryptException $e) {
    //             abort(404, 'Unauthorized or invalid access.');
    //         }
    //     }

    //     $employees = $query->get();

    //     return view('admin.employees.uniform_detail.create', compact('employees', 'uniform_type', 'uniform_dce'));
    // }


    public function uniform_create(Request $request)
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;
        $uniform_type = KitStock::with('kit')->where('b_id', $business_id)->where('available_qty', '>', 0)->get();

        // dd($uniform_type);

        $query = Employee::where('emp_b_id', $business_id)->where('emp_status', '!=', 72)->where('emp_role_id', '!=', 1)->select('emp_id', 'emp_full_name', 'emp_code');

        if ($request->filled('emp_id')) {
            try {
                $emp_id = Crypt::decrypt($request->emp_id);
                $query->where('emp_id', $emp_id);
            } catch (DecryptException $e) {
                abort(404, 'Unauthorized or invalid access.');
            }
        }
        $employees = $query->get();

        return view('admin.employees.uniform_detail.create', compact('employees', 'uniform_type'));
    }


    public function uniform_store(Request $request)
    {

        $user = Auth::user();
        $business_id = $user->emp_b_id;
        $emp_id = $user->emp_id;

        $request->validate([
            'material.*' => 'required|integer|exists:kit_stock,id',
            'available_qty.*' => 'required|integer|min:0',
            'assigned_qty.*' => 'required|integer|min:1',
            'price.*' => 'required|numeric|min:0',
            'is_payable.*' => 'required|string|in:yes,no',
            'total' => 'required|numeric|min:0',
            'total_payable' => 'required|numeric|min:0',
        ]);

        // Create Uniform Issue
        $uniformIssue = UniformIssue::create([
            'ui_issued_by' => $request->ui_issued_by,
            'ui_b_id' => $business_id,
            'ui_total' => $request->total,
            'ui_payable' => $request->total_payable,
            'ui_waived' => $request->total_waived ?? 0,
        ]);

        foreach ($request->material as $index => $material_id) {

            $assigned_qty = $request->assigned_qty[$index] ?? 0;
            $available_qty = $request->available_qty[$index] ?? 0;

            if ($assigned_qty > $available_qty) {
                return redirect()->back()->withErrors([
                    'assigned_qty' => "Assigned quantity for item ID '{$material_id}' exceeds available stock."
                ]);
            }

            // Save Uniform Item
            $itemData = [
                'uit_emp_id' => $request->ui_issued_by,
                'uit_b_id' => $business_id,
                'uit_issue_id' => $uniformIssue->ui_id,
                'uit_material_id' => $material_id,
                'uit_quantity' => $assigned_qty,
                'uit_price' => $request->price[$index] ?? 0,
                'uit_payable' => $request->is_payable[$index] ?? 'no',
                'uit_discount_type' => $request->discount_type[$index] ?? null,
                'uit_discount_value' => $request->discount_value[$index] ?? 0,
                'uit_total_price' => $request->total_price[$index] ?? 0,
                'uit_issues_date' => $request->issue_date[$index] ?? now(),
                'uit_note' => $request->note[$index] ?? null,
            ];

            if (isset($request->image[$index])) {
                $file = $request->image[$index];
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path('assets/uniforms');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $filename);
                $itemData['uit_image_path'] = 'assets/uniforms/' . $filename;
            }

            $uniformItem = UniformItem::create($itemData);

            // Fetch stock
            $stock = KitStock::where('b_id', $business_id)
                ->where('id', $material_id)
                ->first();


            if (!$stock) {
                return redirect()->back()->withErrors([
                    'material' => "Stock record not found for item ID '{$material_id}'."
                ]);
            }

            // Create Kit Assignment
            $assignment = KitAssignment::create([
                'b_id' => $business_id,
                'kit_id' => $material_id,
                'assigned_to' => $request->ui_issued_by,
                'assigned_by' => $emp_id,
                'assigned_qty' => $assigned_qty,
                'per_unit_price' => $request->price[$index] ?? 0,
                'total_payable_amount' => $request->total_price[$index] ?? 0,
                'assigned_date' => now(),
                'assigned_data' => $request->description ?? $request->note[$index] ?? '',
                'remarks' => $request->note[$index] ?? '',
                'status' => 'assigned',
            ]);

            // Log the action
            KitLog::create([
                'b_id' => $business_id,
                'kit_id' => $material_id,
                'action_type' => 'ASSIGNED',
                'reference_id' => $assignment->id,
                'opening_qty' => $stock->available_qty + $stock->assigned_qty,
                'available_qty' => $stock->available_qty,
                'assigned_qty' => $assigned_qty,
                'damaged_qty' => 0,
                'lost_qty' => 0,
                'qty' => $assigned_qty,
                'replaced_qty' => 0,
                'price_per_unit' => $request->price[$index] ?? 0,
                'total_price' => $request->total_price[$index] ?? 0,
                'note' => $request->note[$index] ?? null,
                'action_by' => $emp_id,
            ]);

            // Update Stock
            $stock->available_qty -= $assigned_qty;
            $stock->assigned_qty += $assigned_qty;
            $stock->total_price = $stock->available_qty * $stock->price_per_unit;
            $stock->save();
        }

        return redirect()
            ->route('uniform_index.index')
            ->with('success', 'Uniform details added successfully!');
    }

    public function uniform_destroy($id)
    {
        $user = Auth::user();

        // Load uniform issue with items
        $uniform = UniformIssue::with('items')->find($id);

        if (!$uniform) {
            return response()->json([
                'status'  => false,
                'message' => 'Uniform issue not found.'
            ], 404);
        }

        $uniform->items()->delete();

        // Delete master record
        $uniform->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Uniform issue and related items deleted successfully.'
        ]);
    }


    public function uniform_edit($id)
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;
        $uniform_details = UniformIssue::with('employee')->where('ui_b_id', $business_id)->where('ui_id', $id)->firstOrFail();
        $issue_items = UniformItem::with('fh_stock.kit')->where('uit_b_id', $business_id)->where('uit_issue_id', $uniform_details->ui_id)->get();

        // dd($issue_items,$uniform_details);
        $employees = Employee::where('emp_b_id', $business_id)->where('emp_status', '!=', 72)->where('emp_role_id', '!=', 1)->select('emp_id', 'emp_full_name', 'emp_code')->get();
        $uniform_type = KitStock::with('kit')->where('b_id', $business_id)->where('available_qty', '>=', 0)->get();

        return view(
            'admin.employees.uniform_detail.update',
            compact('uniform_details', 'issue_items', 'uniform_type', 'employees')
        );
    }



    public function uniform_update(Request $request, $id)
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;
        $emp_id = $user->emp_id;

        // Validate Request
        $request->validate([
            'material.*'       => 'required|integer|exists:kit_stock,id',
            'available_qty.*'  => 'required|integer|min:0',
            'assigned_qty.*'   => 'required|integer|min:1',
            'price.*'          => 'required|numeric|min:0',
            'is_payable.*'     => 'required|string|in:yes,no',
            'total'            => 'required|numeric|min:0',
            'total_payable'    => 'required|numeric|min:0',
        ]);

        // Fetch Issue
        $issue = UniformIssue::where('ui_b_id', $business_id)
            ->where('ui_id', $id)
            ->firstOrFail();

        // Update ISSUE totals
        $issue->update([
            'ui_total'   => $request->total,
            'ui_payable' => $request->total_payable,
            'ui_waived'  => $request->total_waived ?? 0,
        ]);

        // Fetch old issue items
        $oldItems = UniformItem::where('uit_issue_id', $id)->get();

        foreach ($oldItems as $old) {

            $stock = KitStock::where('b_id', $business_id)
                ->where('id', $old->uit_material_id)
                ->first();

            if ($stock) {
                $stock->available_qty += $old->uit_quantity;
                $stock->assigned_qty  -= $old->uit_quantity;
                $stock->save();
            }
        }


        KitAssignment::where('kit_id', $old->uit_material_id)->delete();
        UniformItem::where('uit_issue_id', $id)->delete();

        foreach ($request->material as $i => $material_id) {

            $assigned_qty = $request->assigned_qty[$i] ?? 0;
            $available_qty = $request->available_qty[$i] ?? 0;

            if ($assigned_qty > $available_qty) {
                return redirect()->back()->withErrors([
                    "Row " . ($i + 1) . " assigned quantity exceeds available stock."
                ]);
            }

            $itemData = [
                'uit_emp_id'        => $request->ui_issued_by,
                'uit_b_id'          => $business_id,
                'uit_issue_id'      => $issue->ui_id,
                'uit_material_id'   => $material_id,
                'uit_quantity'      => $assigned_qty,
                'uit_price'         => $request->price[$i] ?? 0,
                'uit_payable'       => $request->is_payable[$i] ?? 'no',
                'uit_discount_type' => $request->discount_type[$i] ?? null,
                'uit_discount_value' => $request->discount_value[$i] ?? 0,
                'uit_total_price'   => $request->total_price[$i] ?? 0,
                'uit_issues_date'   => $request->issue_date[$i] ?? now(),
                'uit_note'          => $request->note[$i] ?? null,
            ];

            if (isset($request->image[$i])) {

                $file = $request->image[$i];
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = public_path('assets/uniforms');

                if (!file_exists($path)) mkdir($path, 0755, true);

                $file->move($path, $filename);
                $itemData['uit_image_path'] = "assets/uniforms/" . $filename;
            }

            $item = UniformItem::create($itemData);
            $stock = KitStock::where('b_id', $business_id)
                ->where('id', $material_id)
                ->firstOrFail();

            $stock->available_qty -= $assigned_qty;
            $stock->assigned_qty  += $assigned_qty;
            $stock->total_price    = $stock->available_qty * $stock->price_per_unit;
            $stock->save();


            $assignment = KitAssignment::create([
                'b_id'                => $business_id,
                'kit_id'              => $material_id,
                'assigned_to'         => $request->ui_issued_by,
                'assigned_by'         => $emp_id,
                'assigned_qty'        => $assigned_qty,
                'per_unit_price'      => $request->price[$i] ?? 0,
                'total_payable_amount' => $request->total_price[$i] ?? 0,
                'assigned_date'       => now(),
                'assigned_data'       => $request->note[$i] ?? '',
                'remarks'             => $request->note[$i] ?? '',
                'status'              => 'assigned',
                'reference_id'        => $issue->ui_id,
            ]);


            KitLog::create([
                'b_id'            => $business_id,
                'kit_id'          => $material_id,
                'action_type'     => 'UPDATED-ASSIGN',
                'reference_id'    => $assignment->id,
                'opening_qty'     => $stock->available_qty + $stock->assigned_qty,
                'available_qty'   => $stock->available_qty,
                'assigned_qty'    => $assigned_qty,
                'qty'             => $assigned_qty,
                'price_per_unit'  => $request->price[$i] ?? 0,
                'total_price'     => $request->total_price[$i] ?? 0,
                'note'            => $request->note[$i] ?? null,
                'action_by'       => $emp_id,
                'damaged_qty'    => 0,
                'replaced_qty'   => 0,
                'lost_qty'       => 0,
            ]);
        }

        return redirect()
            ->route('uniform_index.index')
            ->with('success', 'Uniform details updated successfully!');
    }



    public function uniform_colors(Request $request)
    {
        $id = $request->materialId;

        $material = MasterTable::select('m_alias_name')->where('m_id', $id)->first();

        if (!$material) {
            return response()->json([], 404);
        }

        $colors = MasterTable::where('m_type', $material->m_alias_name)
            ->pluck('m_name', 'm_id');

        return response()->json($colors);
    }


    public function getuniform($id)
    {
        $uniformItems = UniformItem::with(['material', 'outfit'])
            ->where('uit_issue_id', $id)
            ->get();

        $uniformIssue = UniformIssue::with('employee')->find($id);

        if ($uniformIssue && $uniformIssue->employee) {
            $employeeName = $uniformIssue->employee->emp_full_name . ' ' . '(' . $uniformIssue->employee->emp_code . ')';
        } else {
            dd('Employee or UniformIssue not found');
        }

        $uniform_details = $uniformItems->map(function ($item) {
            return [
                'uit_material_id' => $item->material->m_name ?? 'N/A',
                'uit_description_id' => $item->outfit->m_name ?? 'N/A',
                'uit_color' => $item->uit_color,
                'uit_size' => $item->size->m_name ?? 'N/A',
                'uit_price' => $item->uit_price,
                'uit_quantity' => $item->uit_quantity,
                'uit_payable' => $item->uit_payable,
                // 'uit_issues_date' => $item->uit_issues_date,
                'uit_issues_date'    => Carbon::parse($item->uit_issues_date)->format('j F Y'),
                'uit_image_path' => $item->uit_image_path,
            ];
        });

        return response()->json([
            'employee' => $employeeName,
            'uniform_details' => $uniform_details,
        ]);
    }
}
