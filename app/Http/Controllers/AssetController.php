<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetHistory;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exports\AssetsExport;
use App\Models\AssetCategory as ModelsAssetCategory;
use App\Models\AssetsBrand;
use App\Models\AssetService;
use App\Models\AssetsServiceEmail;
use App\Models\Branch;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AssetController extends Controller
{

    protected $user;

    private $database;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index(Request $request)
    {
        return redirect()->route('assets.stock');
    }

    public function stock(Request $request)
    {

        $user       = Auth::user();
        $businessId = $user->emp_b_id;
        $branch = Branch::where('br_b_id', $businessId)->get();

        $assetTagFilter    = request()->input('assetTagFilter');
        $modelNumberFilter  = request()->input('modelNumberFilter');
        $serialNumberFilter = request()->input('serialNumberFilter');
        $assetTypeFilter    = request()->input('assetTypeFilter');
        $fromToDateFilter   = request()->input('fromDate');



        $data = Asset::all();

        $total_stock   = $data->whereNotIn('status', ['Service', 'Requested', 'replaced', 'scrap'])->where('assets_b_id', $businessId)->count();
        $newstock      = $data->where('status', 'stock')->where('assets_b_id', $businessId)->count();
        $assignstock   = $data->whereIn('status', ['assigned', 'Return'])->where('assets_b_id', $businessId)->count();
        $scrapstock    = $data->where('status', 'scrap')->where('assets_b_id', $businessId)->count();
        $servicestock  = $data->whereIn('status', ['Service', 'Requested'])->where('assets_b_id', $businessId)->count();
        $replacestock  = $data->where('status', 'replaced')->where('assets_b_id', $businessId)->count();

        // dd($assetTagFilter, $modelNumberFilter, $serialNumberFilter, $assetTypeFilter, $fromToDateFilter);

        if ($request->ajax()) {
            $dynamicConditions = [
                ['method' => 'where', 'args' => ['assets_b_id', $businessId]],
                ['method' => 'where', 'args' => ['status', '=', 'stock']],

                [
                    'method'   => 'select',
                    'args'     => [
                        'id',
                        'asset_tag',
                        'asset_type_id',
                        'assets_b_id',
                        'serial_number',
                        'model_number',
                        'status',
                        'employee_id',
                        'replaced_by',
                        'assigned_at',
                        'scrapped_at',
                        'purchase_value',
                        'warranty_months',
                        'scrap_value',
                        'service_date',
                        'service_return_date',
                        'specifications',
                        'purchase_date',
                        'invoice_no',
                        'invoice_date',
                        'po_no',
                        'vendor_name',
                        'created_at',
                        'updated_at',
                    ],
                    'relation' => [],
                ],
                ['method' => 'with', 'args' => [['assetType', 'employee']]],
            ];

            if (!empty($assetTagFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['asset_tag', $assetTagFilter]];
            }

            if (!empty($modelNumberFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['model_number', $modelNumberFilter]];
            }

            if (!empty($serialNumberFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['serial_number', $serialNumberFilter]];
            }

            if (!empty($assetTypeFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['asset_type_id', $assetTypeFilter]];
            }


            if (!empty($fromToDateFilter)) {
                $dates = explode(' - ', $fromToDateFilter);

                if (count($dates) == 2) {
                    $startDate = \Carbon\Carbon::createFromFormat('M d, Y', trim($dates[0]))->startOfDay();
                    $endDate   = \Carbon\Carbon::createFromFormat('M d, Y', trim($dates[1]))->endOfDay();

                    $dynamicConditions[] = [
                        'method' => 'whereBetween',
                        'args' => ['created_at', [$startDate, $endDate]]
                    ];
                }
            }


            $searchColumns = ['asset_tag', 'serial_number', 'model_number'];

            $helper = new DynamicModelDataTableHelper(
                eloquentModel: new Asset(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            );

            $list = $helper->getServerSideDataTable();

            // 🔹 Prepare row data
            $rowData = [];
            foreach ($list as $i => $val) {
                $row   = [];
                $row[] = $i + 1;
                $row[] = '<strong class="text-primary">' . e($val->asset_tag) . '</strong>';
                $row[] = e($val->assetType->name ?? '-');
                $row[] = e($val->serial_number ?? 'N/A');
                $row[] = e($val->model_number ?? 'N/A');
                $row[] = e($val->employee->name ?? 'Unassigned');
                $row[] = $val->purchase_date ? \Carbon\Carbon::parse($val->purchase_date)->format('j M Y') : 'N/A';

                if ($val->purchase_date && $val->warranty_months) {
                    $expiryDate = \Carbon\Carbon::parse($val->purchase_date)->addMonths($val->warranty_months);
                    if ($expiryDate->isFuture()) {
                        $status = '<span class="badge bg-success">Under Warranty</span>';
                        $underWarranty = true;
                    } else {
                        $status = '<span class="badge bg-danger">Expired</span>';
                        $underWarranty = false;
                    }
                } else {
                    $status = '<span class="badge bg-secondary">N/A</span>';
                    $underWarranty = false;
                }
                $row[] = $status;

                $actions = '
                        <div class="btn-list ms-3">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 180px;">

                                    <!-- View Asset -->
                                    <li>
                                        <a href="' . route('assets.show', $val->id) . '" 
                                        class="dropdown-item d-flex align-items-center gap-2 text-primary">
                                            <i class="bi bi-eye"></i> View Asset
                                        </a>
                                    </li>

                                    <!-- Assign to Employee -->
                                    <li>
                                        <button type="button" class="dropdown-item d-flex align-items-center gap-2 text-success assign-single-btn"
                                            data-asset-id="' . $val->id . '" 
                                            data-asset-name="' . $val->assetType->name . '" 
                                            data-asset-tag="' . e($val->asset_tag) . '">
                                            <i class="bi bi-person-plus"></i> Assign
                                        </button>
                                    </li>

                                    <!-- Duplicate -->
                                    <li>
                                        <button type="button" 
                                            class="dropdown-item d-flex align-items-center gap-2 text-warning duplicate-asset-btn"
                                            data-asset-id="' . $val->id . '"
                                            data-asset-name="' . $val->assetType->name . '" 
                                            data-asset-tag="' . e($val->asset_tag) . '"
                                            data-serial-number="' . e($val->serial_number ?? '') . '"
                                            data-model-number="' . e($val->model_number ?? '') . '"
                                            data-purchase-value="' . e($val->purchase_value ?? '') . '"
                                            data-purchase-date="' . e($val->purchase_date ?? '') . '"
                                            data-warranty-months="' . e($val->warranty_months ?? '') . '"
                                            data-invoice-no="' . e($val->invoice_no ?? '') . '"
                                            data-invoice-date="' . e($val->invoice_date ?? '') . '"
                                            data-vendor-name="' . e($val->vendor_name ?? '') . '">
                                            <i class="bi bi-files"></i> Duplicate
                                        </button>
                                    </li>
                                      ';

                if (!$underWarranty) {
                    $actions .= '
                                    <!-- Move to Scrap -->
                                    <li>
                                        <button type="button" class="dropdown-item d-flex align-items-center gap-2 text-warning"
                                            data-bs-toggle="modal" data-bs-target="#scrapModal"
                                            data-asset-id="' . $val->id . '" 
                                            data-asset-name="' . $val->assetType->name . '" 
                                            data-asset-tag="' . e($val->asset_tag) . '">
                                            <i class="bi bi-trash"></i> Scrap
                                        </button>
                                    </li>
                                   ';
                }

                $actions .= '
                                    <!-- Send to Service -->
                                    <li>
                                        <button type="button" class="dropdown-item d-flex align-items-center gap-2 text-info"
                                            data-bs-toggle="modal" data-bs-target="#serviceModal"
                                            data-asset-id="' . $val->id . '" 
                                            data-asset-name="' . $val->assetType->name . '" 
                                            data-asset-tag="' . e($val->asset_tag) . '">
                                            <i class="bi bi-tools"></i> Service
                                        </button>
                                    </li>

                                </ul>
                            </div>
                        </div>
                    ';


                $actions .= '</div>';
                $row[]     = $actions;

                $rowData[] = $row;
            }

            // 🔹 JSON Response
            return response()->json([
                "draw"            => $request->input('draw'),
                "recordsTotal"    => Asset::count(),
                "recordsFiltered" => $helper->countFilteredServerSideDataTable(),
                "data"            => $rowData,
            ]);
        }

        // 🔹 For non-AJAX request
        $columns = [
            ['name' => 'S. No.',          'width' => '5%'],
            ['name' => 'Asset Tag',       'width' => '15%'],
            ['name' => 'Asset Type',      'width' => '15%'],
            ['name' => 'Serial Number',   'width' => '15%'],
            ['name' => 'Model Number',    'width' => '15%'],
            ['name' => 'Assigned To',        'width' => '15%'],
            ['name' => 'Purchase Date',        'width' => '15%'],
            ['name' => 'Warranty Status',        'width' => '15%'],
            ['name' => 'Action',          'width' => '10%'],
        ];

        $assetTypes = AssetType::where('is_active', true)->where('assets_type_b_id', $businessId)->get();

        $assetTag = Asset::where('status', 'stock')->select('id', 'asset_tag', 'model_number', 'serial_number')->where('assets_b_id', $businessId)->get();
        $noofassets = AssetType::where('is_active', true)->select('id', 'name')->where('assets_type_b_id', $businessId)->get();

        return view('admin.assets.stock', compact('columns', 'assetTypes', 'branch', 'assetTag', 'noofassets', 'total_stock', 'newstock', 'assignstock', 'scrapstock', 'servicestock', 'replacestock'));
    }


    public function assigned(Request $request)
    {
        $user       = Auth::user();
        $businessId = $user->emp_b_id;

        $assetTagFilter    = request()->input('assetTagFilter');
        $modelNumberFilter  = request()->input('modelNumberFilter');
        $serialNumberFilter = request()->input('serialNumberFilter');
        $assetTypeFilter    = request()->input('assetTypeFilter');
        $employeeFilter    = request()->input('employeeFilter');
        $fromToDateFilter   = request()->input('fromDate');

        if ($request->ajax()) {
            $dynamicConditions = [
                ['method' => 'where', 'args' => ['assets_b_id', $businessId]],
                ['method' => 'whereIn', 'args' => ['status', ['assigned', 'Return']]],
                [
                    'method'   => 'select',
                    'args'     => [
                        'id',
                        'asset_tag',
                        'asset_type_id',
                        'assets_b_id',
                        'serial_number',
                        'model_number',
                        'status',
                        'employee_id',
                        'replaced_by',
                        'assigned_at',
                        'scrapped_at',
                        'purchase_value',
                        'purchase_date',
                        'warranty_months',
                        'scrap_value',
                        'service_date',
                        'service_return_date',
                        'specifications',
                        'created_at',
                        'updated_at',
                    ],
                    'relation' => [],
                ],
                // ✅ Nested eager loading relations
                ['method' => 'with', 'args' => [['assetType', 'employee.fh_branch', 'employee.fh_department', 'employee.fh_designation']]],
            ];

            if (!empty($assetTagFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['asset_tag', $assetTagFilter]];
            }

            if (!empty($modelNumberFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['model_number', $modelNumberFilter]];
            }

            if (!empty($serialNumberFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['serial_number', $serialNumberFilter]];
            }

            if (!empty($assetTypeFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['asset_type_id', $assetTypeFilter]];
            }

            if (!empty($employeeFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['employee_id', $employeeFilter]];
            }

            if (!empty($fromToDateFilter)) {
                $dates = explode(' - ', $fromToDateFilter);

                if (count($dates) == 2) {
                    $startDate = \Carbon\Carbon::createFromFormat('M d, Y', trim($dates[0]))->startOfDay();
                    $endDate   = \Carbon\Carbon::createFromFormat('M d, Y', trim($dates[1]))->endOfDay();

                    $dynamicConditions[] = [
                        'method' => 'whereBetween',
                        'args' => ['created_at', [$startDate, $endDate]]
                    ];
                }
            }

            $searchColumns = [
                'asset_tag',
                'serial_number',
                'model_number',

            ];

            $searchRelationships = [
                'employee' => ['emp_full_name', 'emp_code'],
                'employee.fh_branch' => ['br_name'],
                'employee.fh_department' => ['d_name'],
                'employee.fh_designation' => ['dg_name'],
            ];

            $helper = new DynamicModelDataTableHelper(
                eloquentModel: new Asset(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            );
            $list = $helper->getServerSideDataTable();
            // 🔹 Prepare row data
            $rowData = [];
            foreach ($list as $i => $val) {
                $row   = [];
                $row[] = $i + 1; // serial no
                $row[] = e($val->employee->emp_code  ?? 'N/A');

                if ($val->employee) {
                    $empText = ($val->employee->emp_full_name ?? 'Unassigned');
                    $row[] = e($empText)
                        . ' <br><small class="text-muted">'
                        . e($val->employee->fh_designation->dg_name ?? '-')
                        . '</small>';
                } else {
                    $row[] = 'Unassigned';
                }

                $row[] = e($val->employee->fh_branch->br_name ?? 'N/A');
                $row[] = '<strong class="text-primary">' . e($val->asset_tag) . '</strong>';
                $row[] = e($val->assetType->name ?? '-');
                $row[] = e($val->serial_number ?? 'N/A');
                $row[] = e($val->model_number ?? 'N/A');
                $row[] = $val->assigned_at ? \Carbon\Carbon::parse($val->assigned_at)->format('j M Y') : 'N/A';
                $actions = '
                    <div class="btn-list ms-3">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 200px;">
                                
                                <!-- View Asset -->
                                <li>
                                    <a href="' . route('assets.show', $val->id) . '" 
                                    class="dropdown-item d-flex align-items-center gap-2 text-primary">
                                        <i class="bi bi-eye"></i> View Asset
                                    </a>
                                </li>

                                <!-- Unassign -->
                                <li>
                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2 text-secondary unassign-single-btn"
                                        data-asset-id="' . $val->id . '" 
                                         data-asset-name="' . $val->assetType->name . '" 
                                        data-asset-tag="' . e($val->asset_tag) . '">
                                        <i class="bi bi-person-dash"></i> Unassign
                                    </button>
                                </li>

                                <!-- Replace with New Asset -->
                                <li>
                                    <button type="button" class="dropdown-item d-flex align-items-center gap-2 text-success"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#replaceModal" 
                                        data-asset-id="' . $val->id . '"
                                         data-asset-name="' . $val->assetType->name . '" 
                                        data-asset-tag="' . e($val->asset_tag) . '">
                                        <i class="bi bi-arrow-repeat"></i> Replace
                                    </button>
                                </li>

                                  <!-- Send to Service -->
                                    <li>
                                        <button type="button" class="dropdown-item d-flex align-items-center gap-2 text-info" 
                                            data-bs-toggle="modal" data-bs-target="#serviceModal"
                                            data-asset-id="' . $val->id . '" 
                                            data-asset-name="' . $val->assetType->name . '" 
                                            data-asset-empid="' . $val->employee_id . '" 
                                            data-asset-tag="' . e($val->asset_tag) . '">
                                            <i class="bi bi-tools"></i> Service
                                        </button>
                                    </li>
                            </ul>
                        </div>
                    </div>
                ';

                $actions .= '</div>';
                $row[]     = $actions;

                $rowData[] = $row;
            }

            // 🔹 JSON Response
            return response()->json([
                "draw"            => $request->input('draw'),
                "recordsTotal"    => Asset::count(),
                "recordsFiltered" => $helper->countFilteredServerSideDataTable(),
                "data"            => $rowData,
            ]);
        }

        // 🔹 For non-AJAX request
        $columns = [
            ['name' => 'S. No.',         'width' => '5%'],
            ['name' => 'Emp Code',       'width' => '10%'],
            ['name' => 'Assigned To',    'width' => '10%'],
            ['name' => 'Branch',         'width' => '10%'],
            ['name' => 'Asset Tag',      'width' => '20%'],
            ['name' => 'Asset Type',     'width' => '10%'],
            ['name' => 'Serial Number',  'width' => '10%'],
            ['name' => 'Model Number',   'width' => '10%'],
            ['name' => 'Assigned Date',  'width' => '10%'],
            ['name' => 'Action',         'width' => '5%'],
        ];


        $assetTypes = AssetType::where('is_active', true)->where('assets_type_b_id', $businessId)->get();
        $assetTag = Asset::where('status', 'assigned')->select('id', 'asset_tag', 'model_number', 'serial_number')->where('assets_b_id', $businessId)->get();
        $noofassets = AssetType::where('is_active', true)->select('id', 'name')->where('assets_type_b_id', $businessId)->get();
        $emp_list = Employee::where('emp_b_id', $businessId)->select('emp_id', 'emp_full_name')->get();

        return view('admin.assets.assigned', compact('columns', 'assetTypes', 'assetTag', 'noofassets', 'emp_list'));
    }


    public function scrap(Request $request)
    {
        $user       = Auth::user();
        $businessId = $user->emp_b_id;

        $assetTagFilter    = request()->input('assetTagFilter');
        $modelNumberFilter  = request()->input('modelNumberFilter');
        $serialNumberFilter = request()->input('serialNumberFilter');
        $assetTypeFilter    = request()->input('assetTypeFilter');
        $fromToDateFilter   = request()->input('fromDate');

        if ($request->ajax()) {
            $dynamicConditions = [
                ['method' => 'where', 'args' => ['assets_b_id', $businessId]],
                ['method' => 'where', 'args' => ['status', '=', 'scrap']],
                [
                    'method'   => 'select',
                    'args'     => [
                        'id',
                        'asset_tag',
                        'asset_type_id',
                        'assets_b_id',
                        'serial_number',
                        'model_number',
                        'status',
                        'employee_id',
                        'replaced_by',
                        'assigned_at',
                        'scrapped_at',
                        'purchase_value',
                        'purchase_date',
                        'warranty_months',
                        'scrap_value',
                        'scraped_by',
                        'service_date',
                        'service_return_date',
                        'specifications',
                        'created_at',
                        'updated_at',
                    ],
                    'relation' => [],
                ],
                ['method' => 'with', 'args' => [['assetType', 'employee']]],
            ];


            if (!empty($assetTagFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['asset_tag', $assetTagFilter]];
            }

            if (!empty($modelNumberFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['model_number', $modelNumberFilter]];
            }

            if (!empty($serialNumberFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['serial_number', $serialNumberFilter]];
            }

            if (!empty($assetTypeFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['asset_type_id', $assetTypeFilter]];
            }


            if (!empty($fromToDateFilter)) {
                $dates = explode(' - ', $fromToDateFilter);

                if (count($dates) == 2) {
                    $startDate = \Carbon\Carbon::createFromFormat('M d, Y', trim($dates[0]))->startOfDay();
                    $endDate   = \Carbon\Carbon::createFromFormat('M d, Y', trim($dates[1]))->endOfDay();

                    $dynamicConditions[] = [
                        'method' => 'whereBetween',
                        'args' => ['created_at', [$startDate, $endDate]]
                    ];
                }
            }
            $searchColumns = ['asset_tag', 'serial_number', 'model_number'];
            $helper = new DynamicModelDataTableHelper(
                eloquentModel: new Asset(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            );

            $list = $helper->getServerSideDataTable();

            // 🔹 Prepare row data
            $rowData = [];
            foreach ($list as $i => $val) {
                $row   = [];
                $row[] = $i + 1; // serial no
                $row[] = '<strong class="text-primary">' . e($val->asset_tag) . '</strong>';
                $row[] = e($val->assetType->name ?? '-');
                $row[] = e($val->serial_number ?? 'N/A');
                $row[] = e($val->model_number ?? 'N/A');
                $row[] = e($val->fh_scraped->emp_full_name ?? 'Unassigned');
                $row[] = e($val->scrap_value ?? 'N/A');
                $row[] = $val->scrapped_at     ? \Carbon\Carbon::parse($val->scrapped_at)->format('j M Y')     : 'N/A';
                $actions = '
                    <div class="btn-list ms-3">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 200px;">

                                <!-- View Asset -->
                                <li>
                                    <a href="' . route('assets.show', $val->id) . '" 
                                    class="dropdown-item d-flex align-items-center gap-2 text-primary">
                                        <i class="bi bi-eye"></i> View Asset
                                    </a>
                                </li>

                                <!-- Restore to Stock -->
                                <li>
                                    <form method="POST" action="' . route('assets.restore', $val->id) . '" class="d-inline">
                                        ' . csrf_field() . method_field('PATCH') . '
                                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-success"
                                            onclick="return confirm(\'Are you sure you want to restore this asset to stock?\')">
                                            <i class="bi bi-arrow-clockwise"></i> Restore
                                        </button>
                                    </form>
                                </li>

                   
                            </ul>
                        </div>
                    </div>
                ';

                $actions .= '</div>';
                $row[]     = $actions;

                $rowData[] = $row;
            }

            // 🔹 JSON Response
            return response()->json([
                "draw"            => $request->input('draw'),
                "recordsTotal"    => Asset::where('assets_b_id', $businessId)->where('status', 'scrap')->count(),
                "recordsFiltered" => $helper->countFilteredServerSideDataTable(),
                "data"            => $rowData,
            ]);
        }

        // 🔹 For non-AJAX request
        $columns = [
            ['name' => 'S. No.',          'width' => '5%'],
            ['name' => 'Asset Tag',       'width' => '15%'],
            ['name' => 'Asset Type',      'width' => '15%'],
            ['name' => 'Serial Number',   'width' => '15%'],
            ['name' => 'Model Number',    'width' => '15%'],
            ['name' => 'Scrap By',        'width' => '15%'],
            ['name' => 'Scrap Value',        'width' => '10%'],
            ['name' => 'Scrap Date',        'width' => '10%'],
            ['name' => 'Action',          'width' => '10%'],
        ];

        $assetTypes = AssetType::where('is_active', true)->where('assets_type_b_id', $businessId)->get();
        $stockAssets = Asset::where('assets_b_id', $businessId)->where('status', 'stock')->with('assetType')->get();
        $assetTag = Asset::where('status', 'scrap')->select('id', 'asset_tag', 'model_number', 'serial_number')->where('assets_b_id', $businessId)->get();
        $noofassets = AssetType::where('is_active', true)->select('id', 'name')->where('assets_type_b_id', $businessId)->get();

        return view('admin.assets.scrap', compact('columns', 'assetTypes', 'stockAssets', 'assetTag', 'noofassets'));
    }

    public function replaced(Request $request)
    {
        $user       = Auth::user();
        $businessId = $user->emp_b_id;
        $assetTagFilter    = request()->input('assetTagFilter');
        $modelNumberFilter  = request()->input('modelNumberFilter');
        $serialNumberFilter = request()->input('serialNumberFilter');
        $assetTypeFilter    = request()->input('assetTypeFilter');
        $fromToDateFilter   = request()->input('fromDate');


        if ($request->ajax()) {
            $dynamicConditions = [
                ['method' => 'where', 'args' => ['assets_b_id', $businessId]],
                ['method' => 'where', 'args' => ['status', '=', 'replaced']],
                [
                    'method'   => 'select',
                    'args'     => [
                        'id',
                        'asset_tag',
                        'asset_type_id',
                        'assets_b_id',
                        'serial_number',
                        'model_number',
                        'status',
                        'employee_id',
                        'replaced_by',
                        'assigned_at',
                        'scrapped_at',
                        'purchase_value',
                        'purchase_date',
                        'warranty_months',
                        'scrap_value',
                        'service_date',
                        'service_return_date',
                        'specifications',
                        'created_at',
                        'updated_at',
                    ],
                    'relation' => [],
                ],
                ['method' => 'with', 'args' => [['assetType', 'employee']]],
            ];


            if (!empty($assetTagFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['asset_tag', $assetTagFilter]];
            }

            if (!empty($modelNumberFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['model_number', $modelNumberFilter]];
            }

            if (!empty($serialNumberFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['serial_number', $serialNumberFilter]];
            }

            if (!empty($assetTypeFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['asset_type_id', $assetTypeFilter]];
            }


            if (!empty($fromToDateFilter)) {
                $dates = explode(' - ', $fromToDateFilter);

                if (count($dates) == 2) {
                    $startDate = \Carbon\Carbon::createFromFormat('M d, Y', trim($dates[0]))->startOfDay();
                    $endDate   = \Carbon\Carbon::createFromFormat('M d, Y', trim($dates[1]))->endOfDay();

                    $dynamicConditions[] = [
                        'method' => 'whereBetween',
                        'args' => ['created_at', [$startDate, $endDate]]
                    ];
                }
            }



            $searchColumns = ['asset_tag', 'serial_number', 'model_number'];

            $helper = new DynamicModelDataTableHelper(
                eloquentModel: new Asset(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            );

            $list = $helper->getServerSideDataTable();
            // 🔹 Prepare row data
            $rowData = [];
            foreach ($list as $i => $val) {
                $row   = [];
                $row[] = $i + 1; // serial no
                $row[] = '<strong class="text-primary">' . e($val->asset_tag) . '</strong>';
                $row[] = e($val->assetType->name ?? '-');
                $row[] = e($val->serial_number ?? 'N/A');
                $row[] = e($val->model_number ?? 'N/A');
                // $row[] = e($val->employee->emp_full_name ?? 'Unassigned');
                // $row[] = e($val->employee->division ?? 'N/A');

                // // 🔹 Specifications
                // $specHtml = '<span class="text-muted">-</span>';
                // if ($val->specifications) {
                //     $specArray = is_string($val->specifications)
                //         ? json_decode($val->specifications, true)
                //         : $val->specifications;

                //     if (is_array($specArray)) {
                //         $specHtml = '';
                //         foreach (array_slice($specArray, 0, 3, true) as $key => $value) {
                //             $specHtml .= '<small class="d-block text-muted">
                //             <strong>' . ucfirst(str_replace('_', ' ', $key)) . ':</strong> ' . e($value) . '
                //         </small>';
                //         }
                //         if (count($specArray) > 3) {
                //             $specHtml .= '<small class="text-muted">... and ' . (count($specArray) - 3) . ' more</small>';
                //         }
                //     }
                // }
                // $row[] = $specHtml;

                $actions = '
                        <div class="btn-list ms-3">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 180px;">
                                    
                                    <!-- View Asset -->
                                    <li>
                                        <a href="' . route('assets.show', $val->id) . '" 
                                        class="dropdown-item d-flex align-items-center gap-2 text-primary">
                                            <i class="bi bi-eye"></i> View Asset
                                        </a>
                                    </li>

                                </ul>
                            </div>
                        </div>
                    ';



                $actions .= '</div>';
                $row[]     = $actions;

                $rowData[] = $row;
            }

            // 🔹 JSON Response
            return response()->json([
                "draw"            => $request->input('draw'),
                "recordsTotal"    => Asset::count(),
                "recordsFiltered" => $helper->countFilteredServerSideDataTable(),
                "data"            => $rowData,
            ]);
        }

        // 🔹 For non-AJAX request
        $columns = [
            ['name' => 'S. No.',          'width' => '5%'],
            ['name' => 'Asset Tag',       'width' => '15%'],
            ['name' => 'Asset Type',      'width' => '15%'],
            ['name' => 'Serial Number',   'width' => '15%'],
            ['name' => 'Model Number',    'width' => '15%'],
            ['name' => 'Action',          'width' => '10%'],
        ];

        $assetTypes = AssetType::where('is_active', true)->get();

        $assetTag = Asset::where('status', 'replaced')->select('id', 'asset_tag', 'model_number', 'serial_number')->get();
        $noofassets = AssetType::where('is_active', true)->select('id', 'name')->get();

        return view('admin.assets.replaced', compact('columns', 'assetTypes', 'assetTag', 'noofassets'));
    }

    public function service(Request $request)
    {
        $user       = Auth::user();
        $businessId = $user->emp_b_id;

        $assetTagFilter    = request()->input('assetTagFilter');
        $modelNumberFilter  = request()->input('modelNumberFilter');
        $serialNumberFilter = request()->input('serialNumberFilter');
        $assetTypeFilter    = request()->input('assetTypeFilter');

        $fromToDateFilter   = request()->input('fromDate');
        $services = AssetService::with('fh_asset.assetType:id,name')->get();
        if ($request->ajax()) {
            $dynamicConditions = [
                ['method' => 'where', 'args' => ['assets_b_id', $businessId]],
                ['method' => 'whereIn', 'args' => ['service_status', ['service', 'In Progress', 'Requested', 'Completed']]],
                [
                    'method'   => 'select',
                    'args'     => [
                        'id',
                        'asset_id',
                        'asset_tag',
                        'asset_type_id',
                        'service_type',
                        'service_location',
                        'issue_description',
                        'vendor_name',
                        'service_status',
                        'service_cost',
                        'service_start_date',
                        'service_end_date',
                        'courier_name',
                        'docket_no',
                        'service_file',
                        'created_at',
                        'updated_at',
                    ],
                    'relation' => [],
                ],
                ['method' => 'with', 'args' => [['fh_asset', 'fh_asset.assetType']]],
            ];


            if (!empty($assetTagFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['asset_tag', $assetTagFilter]];
            }

            if (!empty($modelNumberFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['model_number', $modelNumberFilter]];
            }

            if (!empty($serialNumberFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['serial_number', $serialNumberFilter]];
            }


            if (!empty($assetTypeFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['asset_type_id', $assetTypeFilter]];
            }

            if (!empty($fromToDateFilter)) {
                $dates = explode(' - ', $fromToDateFilter);

                if (count($dates) == 2) {
                    $startDate = \Carbon\Carbon::createFromFormat('M d, Y', trim($dates[0]))->startOfDay();
                    $endDate   = \Carbon\Carbon::createFromFormat('M d, Y', trim($dates[1]))->endOfDay();

                    $dynamicConditions[] = [
                        'method' => 'whereBetween',
                        'args' => ['created_at', [$startDate, $endDate]]
                    ];
                }
            }
            $searchColumns = ['asset_tag', 'serial_number', 'model_number'];
            // Helper class use
            $helper = new DynamicModelDataTableHelper(
                eloquentModel: new AssetService(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            );
            $list = $helper->getServerSideDataTable();

            // 🔹 Prepare row data
            $rowData = [];
            foreach ($list as $i => $val) {
                $row   = [];
                $row[] = $i + 1; // serial no
                $row[] = '<strong class="text-primary">' . e($val->asset_tag) . '</strong>';
                $row[] = e($val->fh_asset->assetType->name ?? '-');
                // $row[] = e($val->vendor_name ?? 'N/A');
                $row[] = e($val->service_type ?? 'Local');
                $row[] = e($val->fh_branch->br_name ?? 'N/A');
                $row[] = $val->service_start_date ? \Carbon\Carbon::parse($val->service_start_date)->format('j M Y') : 'N/A';
                $row[] = $val->service_end_date ? \Carbon\Carbon::parse($val->service_end_date)->format('j M Y') : 'N/A';
                $row[] = e($val->service_cost ? '₹ ' . number_format($val->service_cost, 2) : 'N/A');

                $row[] = e($val->courier_name ?? 'N/A');
                $row[] = e($val->docket_no ?? 'N/A');
                $row[] = $val->service_file
                    ? '<a href="' . asset($val->service_file) . '" target="_blank" class="text-primary">View File</a>'
                    : 'N/A';

                $row[] = '<span class="badge bg-info">' . e($val->service_status ?? '-') . '</span>';
                // Actions
                $actions = '
                <div class="btn-list ms-3">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 200px;">

                            <!-- View Asset -->
                            <li>
                                <a href="' . route('assets.show', $val->asset_id) . '" 
                                class="dropdown-item d-flex align-items-center gap-2 text-primary">
                                    <i class="bi bi-eye"></i> View Asset
                                </a>
                            </li>

                            <!-- Update Service Status -->
                     

                            <li>
                                <form method="POST" action="' . route('assets.return-from-service', $val->asset_id) . '" class="d-inline">
                                        ' . csrf_field() . method_field('PATCH') . '
                                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-success"
                                        onclick="return confirm(\'Are you sure this asset is ready to return from service?\')">
                                        <i class="bi bi-check-circle"></i> Return
                                        </button>
                                </form>
                            </li>

                        </ul>
                    </div>
                </div>
            ';

                $row[]     = $actions;
                $rowData[] = $row;
            }

            // JSON response
            return response()->json([
                "draw"            => $request->input('draw'),
                "recordsTotal"    => AssetService::count(),
                "recordsFiltered" => $helper->countFilteredServerSideDataTable(),
                "data"            => $rowData,
            ]);
        }

        // For non-ajax request
        $columns = [
            ['name' => 'S. No.',          'width' => '5%'],
            ['name' => 'Asset Tag',       'width' => '10%'],
            ['name' => 'Asset Type',      'width' => '10%'],
            // ['name' => 'Vendor',          'width' => '10%'],
            ['name' => 'Service Type',    'width' => '10%'],
            ['name' => 'Service Location',    'width' => '10%'],
            ['name' => 'Start Date',      'width' => '10%'],
            ['name' => 'End Date',        'width' => '10%'],
            ['name' => 'Service Cost',    'width' => '10%'],
            ['name' => 'Courier Name',    'width' => '10%'],
            ['name' => 'Docket No',    'width' => '10%'],
            ['name' => 'File',    'width' => '10%'],
            ['name' => 'Status',          'width' => '10%'],

            ['name' => 'Action',          'width' => '15%'],
        ];

        $assetTypes = AssetType::where('is_active', true)->get();
        $assetTag = Asset::where('status', 'Requested')->select('id', 'asset_tag', 'model_number', 'serial_number')->get();
        $noofassets = AssetType::where('is_active', true)->select('id', 'name')->get();

        return view('admin.assets.service', compact('columns', 'assetTypes', 'assetTag', 'noofassets'));
    }

    public function create()
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $assetTypes = AssetType::where('is_active', true)->where('assets_type_b_id', $businessId)->get();
        $assetbrand = AssetsBrand::where('br_b_id', $businessId)->get();
        $assetcategory = AssetCategory::where('ac_b_id', $businessId)->get();
        return view('admin.assets.create', compact('assetTypes', 'assetbrand', 'assetcategory'));
    }

    public function getAssetTypeStructure(AssetType $assetType)
    {
        return response()->json([
            'structure' => $assetType->getFormStructure(),
            'is_component_based' => $assetType->isComponentBased(),
            'asset_type' => [
                'id' => $assetType->id,
                'name' => $assetType->name,
                'description' => $assetType->description,
                'structure_type' => $assetType->structure_type ?? 'simple'
            ]
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $validated = $request->validate([
            'asset_type_id'   => 'required|exists:asset_types,id',
            'notes'           => 'nullable|string',
            'purchase_value'  => 'required|numeric|min:0',
            'purchase_date'   => 'required|date',
            'warranty_months' => 'required|integer|min:0|max:120',
            'vendor_name'     => 'nullable|string|max:255',
            'po_no'           => 'nullable|string|max:255',
            'invoice_no'      => 'nullable|string|max:255',
            'invoice_date'    => 'nullable|date',
            'serial_number'   => 'nullable|string|max:255',
            'amc_expiry_date'   => 'nullable|string|max:255',
            'model_number'    => 'nullable|string|max:255',
            'invoice_file'    => 'nullable|file|mimes:pdf,jpg,png|max:2048', // optional file
        ]);

            $filePath = null;
            if ($request->hasFile('invoice_file')) {
                $fileName = time() . '_' . $request->file('invoice_file')->getClientOriginalName();
                $request->file('invoice_file')->move(public_path('invoices'), $fileName);
                $filePath = 'invoices/' . $fileName;
            }

        $asset = Asset::create([
            'asset_type_id'   => $validated['asset_type_id'],
            'issue_by'     => $user->emp_id,
            'assets_b_id'     => $businessId,
            'status'          => 'stock',
            'invoice_file'    => $filePath,
            'vendor_name'     => $validated['vendor_name'] ?? null,
            'po_no'           => $validated['po_no'] ?? null,
            'invoice_no'      => $validated['invoice_no'] ?? null,
            'invoice_date'    => $validated['invoice_date'] ?? null,
            'purchase_date'   => $validated['purchase_date'],
            'purchase_value'  => $validated['purchase_value'],
            'warranty_months' => $validated['warranty_months'],
            'serial_number'   => $validated['serial_number'] ?? null,
            'amc_expiry_date'   => $validated['amc_expiry_date'] ?? null,
            'model_number'    => $validated['model_number'] ?? null,
            'notes'           => $validated['notes'] ?? null,
        ]);


        $assetType = AssetType::findOrFail($validated['asset_type_id']);
        $asset->histories()->create([
            'user_id'     => $user->emp_id,
            'assets_b_id' => $businessId,
            'action'      => 'created',
            'to_status'   => 'stock',
            'notes'       => 'Asset created and added to stock',
        ]);

        return redirect()
            ->route('assets.stock')
            ->with('success', 'Asset created successfully!');
    }

    public function show(Asset $asset)
    {
        $asset->load([
            'assetType',
            'employee',
            'fh_service.fh_branch',
            'histories.employee',
            'histories.user',
        ]);

        // dd($asset);

        return view('admin.assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $assetTypes = AssetType::where('is_active', true)->with(['fields.fieldType', 'components.fields.fieldType'])->get();
        return view('admin.assets.edit', compact('asset', 'assetTypes'));
    }

    public function update(Request $request, Asset $asset)
    {
        $assetType = AssetType::findOrFail($request->asset_type_id);

        $rules = [
            'asset_tag' => 'required|string|max:255|unique:assets,asset_tag,' . $asset->id,
            'asset_type_id' => 'required|exists:asset_types,id',
            'serial_number' => 'required|string|max:255',
            'model_number' => 'required|string|max:255',
            'notes' => 'nullable|string'
        ];

        // Add dynamic validation based on asset type
        $requiredFields = $assetType->getRequiredFieldsForType();
        foreach ($requiredFields as $field) {
            $fieldName = strtolower(str_replace(' ', '_', $field));
            $rules["specifications.{$fieldName}"] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $request, $asset) {
            $oldData = $asset->toArray();

            $asset->update([
                'asset_tag' => $validated['asset_tag'],
                'asset_type_id' => $validated['asset_type_id'],
                'serial_number' => $validated['serial_number'],
                'model_number' => $validated['model_number'],
                'specifications' => $request->specifications ?? [],
                'notes' => $validated['notes'] ?? null,
            ]);

            $user = Auth::user();
            $businessId = $user->emp_b_id;
            // Log the update
            AssetHistory::create([
                'asset_id' => $asset->id,
                'user_id' => Auth::id(),
                'assets_b_id'     => $businessId,
                'action' => 'updated',
                'from_status' => $asset->status,
                'to_status' => $asset->status,
                'notes' => 'Asset details updated',
                'metadata' => [
                    'old_data' => $oldData,
                    'changes' => $asset->getChanges()
                ]
            ]);
        });

        return redirect()->route('assets.stock')->with('success', 'Asset updated successfully!');
    }

    public function destroy(Asset $asset)
    {
        if ($asset->status === 'assigned') {
            return redirect()->back()->with('error', 'Cannot delete assigned asset. Please unassign first.');
        }

        DB::transaction(function () use ($asset) {

            $user = Auth::user();
            $businessId = $user->emp_b_id;
            // Log the deletion
            AssetHistory::create([
                'asset_id' => $asset->id,
                'user_id' => Auth::id(),
                'assets_b_id'     => $businessId,
                'action' => 'deleted',
                'from_status' => $asset->status,
                'to_status' => 'deleted',
                'notes' => 'Asset permanently deleted'
            ]);

            $asset->delete();
        });

        return redirect()->route('assets.stock')->with('success', 'Asset deleted successfully!');
    }

    public function assign(Request $request)
    {
        // dd($request);
        $request->validate([
            'asset_ids'   => 'required|array',
            'asset_ids.*' => 'exists:assets,id',
            'employee_id' => 'required|exists:employees,emp_id', // fixed here
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $existingIds = $employee->emp_assets_id;

        if (!is_array($existingIds)) {
            $existingIds = [];
        }
        $newAssetIds = $request->input('asset_ids');
        $mergedIds = array_unique(array_merge($existingIds, $newAssetIds));
        $employee->emp_assets_id = ($mergedIds);
        $employee->save();


        $assets = Asset::whereIn('id', $request->asset_ids)
            ->where('status', 'stock')
            ->get();

        if ($assets->count() !== count($request->asset_ids)) {
            return back()->with('error', 'Some assets are not available for assignment.');
        }

        DB::transaction(function () use ($assets, $employee, $request) {
            foreach ($assets as $asset) {
                $newAssetTag = $asset->asset_tag;

                if (str_starts_with($asset->asset_tag, 'KES/LIPL/RAI/')) {
                    $division = Asset::getDivisionCode($employee->division);
                    $location = Asset::getLocationCode($employee->branch);
                    $device   = strtoupper(substr($asset->assetType->name, 0, 3));
                    $year     = date('Y');

                    $baseTag   = "KES/{$division}/{$location}/{$device}/{$year}/";
                    $sequence  = Asset::getNextSequence($baseTag);
                    $newAssetTag = $baseTag . str_pad($sequence, 3, '0', STR_PAD_LEFT);
                }

                $user = Auth::user();
                $emp_id = $user->emp_id;
                $asset->update([
                    'status'      => 'assigned',
                    'employee_id' => $employee->emp_id,
                    'assigned_at' => now(),
                    'asset_tag'   => $newAssetTag,
                    'assigned_by'     => $user->emp_id,
                ]);

                $user = Auth::user();
                $emp_id = $user->emp_id;
                $businessId = $user->emp_b_id;

                AssetHistory::create([
                    'asset_id'    => $asset->id,
                    'employee_id' => $employee->emp_id,
                    'assets_b_id'  => $businessId,
                    'user_id'     => $emp_id,
                    'action'      => 'assigned',
                    'from_status' => 'stock',
                    'to_status'   => 'assigned',
                    'notes'       => $request->remark ?: "Asset assigned to {$employee->emp_full_name}",
                ]);
            }
        });


        return redirect()
            ->route('assets.assigned')
            ->with('success', 'Assets assigned successfully!');
    }
    public function unassign(Request $request, Asset $asset)
    {

        // dd($request->all());
        if (in_array($asset->status, ['assigned', 'Return'])) {
        } else {
            return redirect()->back()->with('error', 'Asset is not currently assigned.');
        }

        DB::transaction(function () use ($asset, $request) {
            $employeeName = $asset->employee->name ?? 'Unknown';

            $asset->update([
                'status' => 'stock',
                'employee_id' => null,
                'assigned_at' => null,
                'assigned_by' => null
            ]);

            $user       = Auth::user();
            $user_id = $user->emp_id;
            $businessId = $user->emp_b_id;

            AssetHistory::create([
                'asset_id' => $asset->id,
                'user_id' => $user_id,
                'assets_b_id'     => $businessId,
                'action' => 'unassigned',
                'from_status' => 'assigned',
                'to_status' => 'stock',
                'notes'       => $request->remark ?: "Asset unassigned to {$employeeName}",
            ]);
        });

        return redirect()->back()->with('success', 'Asset unassigned successfully!');
    }

    public function scrapAsset(Request $request, Asset $asset)
    {

        // dd($request->all());
        if ($asset->status === 'scrap') {
            return redirect()->back()->with('error', 'Asset is already scrapped.');
        }

        $request->validate([
            'scrap_value' => 'required|numeric|min:0'
        ]);

        DB::transaction(function () use ($asset, $request) {
            $fromStatus = $asset->status;

            $asset->update([
                'status' => 'scrap',
                'employee_id' => null,
                'scrapped_at' => now(),
                'scraped_by' => Auth::id(),
                'scrap_value'   => $request->scrap_value ?? 0,
                'scrap_reason'  => $request->scrap_reason ?: 'Asset moved to scrap with value ₹' . number_format($request->scrap_value, 2),

            ]);

            $user = Auth::user();
            $businessId = $user->emp_b_id;

            AssetHistory::create([
                'asset_id' => $asset->id,
                'user_id' => Auth::id(),
                'assets_b_id'     => $businessId,
                'action' => 'scrapped',
                'from_status' => $fromStatus,
                'to_status' => 'scrap',
                'notes'       => $request->scrap_reason ?: 'Asset moved to scrap with value ₹' . number_format($request->scrap_value, 2),
                'metadata' => ['scrap_value' => $request->scrap_value]
            ]);
        });

        return redirect()->route('assets.scrap-list')
            ->with('success', 'Asset scrapped successfully!');
    }

    public function sendToService(Request $request, Asset $asset)
    {
        if ($asset->status === 'service') {
            return redirect()->back()->with('error', 'Asset is already in service.');
        }

        $validated = $request->validate([
            'service_type'        => 'required|string',
            'service_location'    => 'required|string',
            'vendor_name'         => 'nullable|string|max:255',
            'service_cost'        => 'nullable|numeric|min:0',
            'service_start_date'  => 'nullable|date',
            'service_end_date'    => 'nullable|date',
            'issue_description'   => 'nullable|string|max:1000',
        ]);

        // Transaction Start
        $assetService = DB::transaction(function () use ($asset, $validated, $request) {
            $fromStatus = $asset->status;
            $asset->update([
                'status'              => 'Requested',
                'employee_id'         =>  $request->serviceAssestempid ?? null,
                'service_date'        => now(),
                'service_return_date' => null,
                'service_notes'       => $validated['issue_description'] ?? null,
            ]);

            $user = Auth::user();
            $emp_b_id = $user->emp_b_id;

            AssetHistory::create([
                'asset_id'    => $asset->id,
                'assets_b_id' => $emp_b_id,
                'employee_id' =>  $request->serviceAssestempid ?? $user->emp_id,
                'user_id'     => Auth::id(),
                'action'      => 'sent_to_service',
                'from_status' => $fromStatus,
                'to_status'   => 'Requested',
                'notes'       => 'Asset sent to service for repair' . (!empty($validated['issue_description']) ? ': ' . $validated['issue_description'] : ''),
                'metadata'    => $validated,
            ]);

            // File upload
            $filePath = null;
            if ($request->hasFile('service_file')) {
                $fileName = time() . '_' . $request->file('service_file')->getClientOriginalName();
                $request->file('service_file')->move(public_path('invoices'), $fileName);
                $filePath = 'invoices/' . $fileName;
            }



            // AssetService create
            return AssetService::create([
                'assets_b_id'        => $emp_b_id,
                'asset_tag'          => $asset->asset_tag,
                'asset_id'           => $asset->id,
                'service_type'       => $validated['service_type'],
                'service_location'   => $validated['service_location'],
                'issue_description'  => $validated['issue_description'] ?? null,
                'vendor_name'        => $validated['vendor_name'] ?? null,
                'service_status'     => 'Requested',
                'service_cost'       => $validated['service_cost'] ?? null,
                'service_start_date' => $validated['service_start_date'] ?? null,
                'service_end_date'   => $validated['service_end_date'] ?? null,
                'courier_name'       => $request->courier_name ?? null,
                'docket_no'          => $request->docket_no ?? null,
                'asset_type_id'      => $asset->asset_type_id,
                'employee_id'        => $request->serviceAssestempid ?? $user->emp_id,
                'service_file'       => $filePath,
            ]);
        });

        $user = Auth::user();
        $emp_b_id = $user->emp_b_id;
        $assetService = AssetService::with('fh_branch')->find($assetService->id);


        $emails = AssetsServiceEmail::where('b_id', $emp_b_id)->get();

        $recipients = [];

        foreach ($emails as $email) {
            if (!empty($email->email1)) {
                $recipients[] = $email->email1;
            }
            if (!empty($email->email2)) {
                $recipients[] = $email->email2;
            }
        }
        $recipients = array_unique($recipients);



        foreach ($recipients as $email) {
            Mail::send('admin.assets.emails_service', ['service' => $assetService,  'service_type' => 'request'], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Asset Service Request');
            });
        }

        return redirect()->route('assets.service')
            ->with('success', 'Asset sent to service successfully and mail sent to all concerned!');
    }


    public function returnFromService(Asset $asset)
    {
        if ($asset->status !== 'Requested') {
            return redirect()->back()->with('error', 'Asset is not currently in service.');
        }

        $user = Auth::user();
        $b_id = $user->emp_b_id;

        // 1. Fetch service record (without employee role condition)
        $assets_service = AssetService::with('asset', 'employee')
            ->where('asset_id', $asset->id)
            ->where('assets_b_id', $b_id)
            ->where('service_status', 'Requested')
            ->first();

        if (!$assets_service) {
            return redirect()->back()->with('error', 'No active service record found for this asset.');
        }

        // 2. Decide asset status based on employee role
        $employee = $assets_service->employee;
        if (!$employee) {
            $assetStatus = 'Stock'; // no employee assigned
        } elseif ($employee->emp_role_id == 1) {
            $assetStatus = 'Stock'; // role 1 means not assignable
        } else {
            $assetStatus = 'assigned'; // valid employee assigned
        }

        DB::transaction(function () use ($asset, $assets_service, $assetStatus) {
            $assets_service->update([
                'service_status'   => $assetStatus,
                'service_end_date' => now()
            ]);

            $asset->update([
                'status'              => $assetStatus,
                'service_return_date' => now()
            ]);

            AssetHistory::create([
                'asset_id'     => $asset->id,
                'user_id'      => Auth::id(),
                'assets_b_id'  => Auth::user()->emp_b_id,
                'action'       => 'returned_from_service',
                'from_status'  => 'service',
                'to_status'    => $assetStatus,
                'notes'        => 'Asset returned from service and moved to ' . strtolower($assetStatus)
            ]);
        });

        // Prepare email
        $email = $employee?->emp_email ?? 'khilesh.fixingdot@gmail.com';

        try {
            Mail::send('admin.assets.emails_service', [
                'service'      => $assets_service,
                'service_type' => 'Return'
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Assets Service Return');
            });
        } catch (\Exception $e) {
            Log::error('Mail sending failed to ' . $email . ' Error: ' . $e->getMessage());

            // Fallback mail
            try {
                Mail::send('admin.assets.emails_service', [
                    'service'      => $assets_service,
                    'service_type' => 'Return'
                ], function ($message) {
                    $message->to('khilesh.fixingdot@gmail.com')
                        ->subject('Assets Service Return');
                });
            } catch (\Exception $ex) {
                Log::error('Fallback mail also failed. Error: ' . $ex->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Asset returned from service successfully!');
    }



    public function restore(Asset $asset)
    {
        if ($asset->status !== 'scrap') {
            return redirect()->back()->with('error', 'Asset is not currently scrapped.');
        }

        DB::transaction(function () use ($asset) {
            $asset->update([
                'status' => 'stock',
                'scrapped_at' => null,
                'scraped_by' => null,
                'scrap_value' => null,
                'scrap_reason' => null
            ]);


            $user = Auth::user();
            $businessId = $user->emp_b_id;

            AssetHistory::create([
                'asset_id' => $asset->id,
                'assets_b_id'     => $businessId,
                'user_id' => Auth::id(),
                'action' => 'restored',
                'from_status' => 'scrap',
                'to_status' => 'stock',
                'notes' => 'Asset restored from scrap to stock'
            ]);
        });

        return redirect()->back()->with('success', 'Asset restored to stock successfully!');
    }

    public function replace(Request $request, Asset $scrapAsset)
    {
        $request->validate([
            'replacement_asset_id' => 'required|exists:assets,id'
        ]);

        $replacementAsset = Asset::findOrFail($request->replacement_asset_id);
        $old_assets = Asset::findOrFail($request->asset_id);

        if ($replacementAsset->status !== 'stock') {
            return redirect()->back()->with('error', 'Replacement asset must be in stock.');
        }

        DB::transaction(function () use ($old_assets, $replacementAsset, $request) {
            // Update the scrap asset
            $old_assets->update([
                'status' => 'replaced',
                'replaced_by' => $request->replacement_asset_id,
                'replace_date' => now()

            ]);

            // Update the replacement asset
            $replacementAsset->update([
                'status' => 'assigned',
                'employee_id' => $old_assets->employee_id,
                'assigned_at' => now()
            ]);
            $user = Auth::user();
            $businessId = $user->emp_b_id;

            AssetHistory::create([
                'asset_id' => $request->asset_id,
                'user_id' => Auth::id(),
                'assets_b_id'     => $businessId,
                'action' => 'replaced',
                'from_status' => 'assigned',
                'to_status' => 'replaced',
                'notes' => "Asset replaced with {$replacementAsset->asset_tag}",
                'metadata' => ['replacement_asset_id' => $replacementAsset->id]
            ]);

            AssetHistory::create([
                'asset_id' => $replacementAsset->id,
                'employee_id' => $replacementAsset->employee_id,
                'assets_b_id'     => $businessId,
                'user_id' => Auth::id(),
                'action' => 'replaced',
                'from_status' => 'assigned',
                'to_status' => 'assigned',
                'notes' => "Asset assigned as replacement for {$old_assets->asset_tag}"
            ]);
        });

        return redirect()->route('assets.replaced')
            ->with('success', 'Asset replaced successfully!');
    }

    private function linkDesktopComponents($desktopSet, $specifications)
    {
        $componentFields = ['first_monitor', 'second_monitor', 'cpu', 'keyboard', 'mouse', 'ups'];

        foreach ($componentFields as $field) {
            if (!empty($specifications[$field])) {
                $componentAsset = Asset::where('asset_tag', $specifications[$field])->first();
                if ($componentAsset) {
                    // Update component to reference the desktop set
                    $componentSpecs = $componentAsset->specifications ?? [];
                    $componentSpecs['parent_desktop'] = $desktopSet->asset_tag;
                    $componentAsset->update(['specifications' => $componentSpecs]);

                    $user = Auth::user();
                    $businessId = $user->emp_b_id;
                    // Log the linking
                    AssetHistory::create([
                        'asset_id' => $componentAsset->id,
                        'user_id' => Auth::id(),
                        'assets_b_id'     => $businessId,
                        'action' => 'updated',
                        'from_status' => $componentAsset->status,
                        'to_status' => $componentAsset->status,
                        'notes' => "Linked to Desktop Set: {$desktopSet->asset_tag}",
                        'metadata' => ['desktop_set_id' => $desktopSet->id]
                    ]);
                }
            }
        }
    }
    public function getAssetTypeFields(AssetType $assetType)
    {
        return response()->json([
            'fields' => $assetType->getRequiredFieldsForType()
        ]);
    }



    public function duplicate(Request $request)
    {
        $id = $request->assets_id;
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $asset = Asset::findOrFail($id);

        Asset::create([
            'asset_type_id'   => $asset->asset_type_id,
            'assets_b_id'     => $businessId,
            'issue_by'     => $user->emp_id,
            'serial_number'   => $request->serial_number,
            'model_number'    => $request->model_number,
            'specifications'  => $asset->specifications,
            'notes'           => $request->note,
            'purchase_value'  => $request->purchase_value,
            'purchase_date'   => $request->purchase_date,
            'warranty_months' => $request->warranty_months,
            'status'          => 'stock',
            'employee_id'     => $asset->employee_id,
            'invoice_no'      => $request->invoice_no,
            'invoice_date'    => $request->invoice_date,
            'invoice_file'    => $asset->invoice_file,
            'po_no'           => $request->po_no,
            'vendor_name'     => $request->vendor_name,
        ]);

        $user = Auth::user();
        $emp_id = $user->emp_id;
        $businessId = $user->emp_b_id;

        AssetHistory::create([
            'asset_id'    => $asset->id,
            'employee_id' => $asset->employee_id,
            'user_id'     => $emp_id,
            'assets_b_id'     => $businessId,
            'action'      => 'Duplicate',
            'from_status' => 'stock',
            'to_status'   => 'stock',
            'notes'       => "Asset Create Duplicate ",
        ]);

        return redirect()
            ->route('assets.stock')
            ->with('success', 'Asset duplicated successfully!');
    }

    public function updateStatus(Request $request)
    {

        $request->validate([
            'asset_id' => 'required|integer|exists:assets,id',
            'status' => 'required|string',
            'remark' => 'nullable|string',
        ]);

        $asset = AssetService::where('asset_tag', $request->asset_tag)->first();
        $assetdata = Asset::where('asset_tag', $request->asset_tag)->first();


        $asset->update([
            'service_status' => $request->status,
        ]);

        $assetdata->update([
            'status' => $request->status,
        ]);

        $user = Auth::user();
        $emp_id = $user->emp_id;
        $businessId = $user->emp_b_id;
        AssetHistory::create([
            'asset_id'    => $asset->asset_id,
            'assets_b_id'     => $businessId,
            'employee_id' => null,
            'user_id'     => $emp_id,
            'action'      => $request->status,
            'from_status' => $request->status,
            'to_status'   => $request->status,
            'notes'       => $request->remarkn ?? 'N/A',
        ]);


        return redirect()->back()->with('success', 'Asset status updated successfully.');
    }

    public function saveCategory(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $rules = [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'id' => 'nullable|integer'
        ];

        // If updating, adjust unique rule to ignore current record
        if ($request->id) {
            $rules['name'] .= '|unique:assets_categories,ac_name,' . $request->id . ',ac_id';
        } else {
            $rules['name'] .= '|unique:assets_categories,ac_name';
        }

        $validator = Validator::make($request->all(), $rules, [
            'name.required' => 'Category name is required.',
            'name.unique' => 'This category already exists.',
            'name.max' => 'Category name cannot exceed 100 characters.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update or create
        if ($request->id) {
            $category = AssetCategory::find($request->id);
            if (!$category) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category not found'
                ], 404);
            }
        } else {
            $category = new AssetCategory();
            $category->ac_code = strtoupper(Str::random(5)); // auto-generate code
        }

        $category->ac_name = $request->name;
        $category->ac_b_id = $businessId;
        $category->ac_description = $request->description;
        $category->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Category saved successfully',
            'category' => $category
        ]);
    }

    public function brand_store(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $request->validate([
            'br_name' => 'required|unique:assets_brands,br_name',
            'br_description' => 'nullable|string'
        ]);

        $brand = AssetsBrand::create([
            'br_name' => $request->br_name,
            'br_b_id' => $businessId,
            'br_slug' => Str::slug($request->br_name),
            'br_description' => $request->br_description
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Brand created successfully!',
            'data' => $brand
        ]);
    }
}
