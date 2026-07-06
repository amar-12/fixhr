<?php

namespace App\Http\Controllers\Payroll;

use App\Models\MasterTable;
use Illuminate\Http\Request;
use App\Models\FinancialYear;
use App\Models\AdhocComponent;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Validation\Rule;

class AdhocComponentController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }


    public function index(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        if ($this->user) {
            $payrollHeadings = MasterTable::where('m_group', '=', 'PAYROLL_HEADINGS')->get();
            $headingMap = $payrollHeadings->pluck('m_name', 'm_id');
            // Handle AJAX Request for Adhoc Components
            if ($request->ajax()) {

                $dynamicConditions = [
                    [
                        'method' => 'select',
                        'args' => ['ac_id', 'ac_adhoc_heading_id', 'ac_adhoc_component_name', 'created_at'],
                        'relation' => []
                    ],
                    [
                        'method' => 'where',
                        'args' => ['ac_adhoc_business_id', $businessId],
                        'relation' => []
                    ],
                    [
                        'method' => 'orderBy',
                        'args' => ['created_at', 'desc'],
                        'relation' => []
                    ]
                ];

                $searchColumns = ['ac_adhoc_heading_id', 'ac_adhoc_component_name'];


                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new AdhocComponent(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                ))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $key => $val) {
                    $i++;
                    $row = [];
                    $row[] = $i;
                    $row[] = $headingMap[$val->ac_adhoc_heading_id] ?? 'N/A';
                    $row[] = $val->ac_adhoc_component_name;

                    // Optional Action Column
                    $row[] = '
                    <div class="btn-list ms-3">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                <li>
                                    <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-adhoc-component"
                                        type="button"
                                        data-id="' . $val->ac_id . '"
                                        data-heading_id="' . $val->ac_adhoc_heading_id . '"
                                        data-name="' . $val->ac_adhoc_component_name . '">
                                        <i class="feather feather-edit"></i> Edit
                                    </button>
                                </li>

                                 <li>
                                    <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-adhoc-component"
                                        type="button"
                                        data-id="' . $val->ac_id . '">
                                        <i class="feather feather-trash-2"></i> Delete
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>';
                    $rowData[] = $row;
                }


                $output = [
                    "draw" => $request->input('draw'),
                    "recordsTotal" => sizeof($list),
                    "recordsFiltered" => (new DynamicModelDataTableHelper(
                        eloquentModel: new AdhocComponent(),
                        dynamicConditions: $dynamicConditions,
                    ))->countFilteredServerSideDataTable(),
                    "data" => $rowData,
                ];

                return response()->json($output);
            }

            // Regular Page Load
            $columns = [
                'S. No.',
                'Adhoc Name',
                'Adhoc Component Name',
                'Action',
            ];

            $financialYears = FinancialYear::select('fy_id', 'fy_year', 'fy_start_date', 'fy_end_date', 'fy_is_current')
                ->orderBy('fy_start_date', 'desc')
                ->get();


            return view('admin.payroll.adhoc-components', compact(
                'financialYears',
                'columns',
                'payrollHeadings',
                'businessId'
            ));
        } else {
            abort(404);
        }
    }


    public function store(Request $request)
    {
        $request->validate([
            'payroll_heading_id' => 'required|exists:master_table,m_id',
            'adhoc_component_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('adhoc_components', 'ac_adhoc_component_name')
                    ->where(function ($query) use ($request) {
                        return $query->where('ac_adhoc_heading_id', $request->payroll_heading_id);
                    }),
            ],
            'businessId' => 'required|integer',
        ], [
            'adhoc_component_name.unique' => 'This component already exists under the selected Adhoc Name.',
        ]);

        AdhocComponent::create([
            'ac_adhoc_heading_id' => $request->payroll_heading_id,
            'ac_adhoc_component_name' => $request->adhoc_component_name,
            'ac_adhoc_business_id' => $request->businessId,
        ]);

        return response()->json(['success' => 'Adhoc Component created successfully.']);
    }



    public function updateAdhoc(Request $request, $id)
    {
        $request->validate([
            'payroll_heading_id' => 'required|exists:master_table,m_id',
            'adhoc_component_name' => 'required|string|max:255',
        ]);

        $component = AdhocComponent::findOrFail($id);
        $component->update([
            'ac_adhoc_heading_id' => $request->payroll_heading_id,
            'ac_adhoc_component_name' => $request->adhoc_component_name,
        ]);

        return response()->json(['success' => 'Adhoc Component updated successfully.']);
    }

    public function destroy($id)
    {
        $component = AdhocComponent::findOrFail($id);
        $component->delete();

        return response()->json(['success' => 'Adhoc Component deleted successfully.']);
    }
}
