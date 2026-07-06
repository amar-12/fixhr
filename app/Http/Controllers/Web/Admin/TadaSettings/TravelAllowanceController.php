<?php

namespace App\Http\Controllers\Web\Admin\TadaSettings;

use App\Http\Controllers\Controller;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaTravelAllowance;
use App\Models\PolicyTadaTravelMode;
use App\Models\PolicyTadaTravelType;
use App\Models\PolicyTadaTravelVehicle;
use App\Models\RolesHasPermission;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDO;

class TravelAllowanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $policyCategoryFilter = request()->input('policyCategoryFilter');
        $travelModeFilter = request()->input('travelModeFilter');
        $travelTypeFilter = request()->input('travelTypeFilter');
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['ptta_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['ptta_id', 'ptta_b_id', 'ptta_ptc_id', 'ptta_pttt_id', 'ptta_pttm_id', 'ptta_pttv_id', 'ptta_claim_type_id', 'ptta_eligibility', 'ptta_claim_type_id', 'ptta_remarks', 'updated_at'],
                    'relation' => [
                        'fh_policy_tada_category:ptc_id,ptc_name',
                    ]
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['ptta_id', 'ptta_ptc_id', 'ptta_pttt_id', 'ptta_pttm_id', 'ptta_pttv_id', 'ptta_eligibility', 'ptta_remarks', 'updated_at', 'ptta_id', 'desc'],
                ],
            ];

            if ($policyCategoryFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['ptta_ptc_id', $policyCategoryFilter]
                ];
            }

            if ($travelTypeFilter != '') {
                $dynamicConditions[] =
                    [
                        'method' => 'where',
                        'args' => ['ptta_pttt_id', $travelTypeFilter]
                    ];
            }

            if ($travelModeFilter != '') {
                $dynamicConditions[] =
                    [
                        'method' => 'whereHas',
                        'args' => ['pttm_by_mode_id', $travelModeFilter],
                        'relation' => 'fh_policy_tada_travel_mode'
                    ];
            }

            // Define search value, columns, and relationships
            $searchColumns = [
                // 'ptta_id', 'ptta_ptc_id', 'ptta_pttt_id', 'ptta_pttm_id', 'ptta_pttv_id', 'ptta_eligibility', 'ptta_remarks', 'updated_at'
                'ptta_id', 'ptta_ptc_id', 'ptta_remarks'
            ];
            $searchRelationships = [
                'fh_policy_tada_category' => ['ptc_name'],
                'fh_policy_tada_travel_type.fh_travel_type' => ['m_name'],
                'fh_policy_tada_travel_mode.fh_travel_mode' => ['m_name'],
                'fh_policy_tada_travel_vehicle.fh_vehicle' => ['m_name'],
                'fh_policy_tada_travel_vehicle.fh_travel_class' => ['m_name'],
                'fh_policy_tada_travel_vehicle.fh_vehicle_owner' => ['m_name'],
                'fh_claim_type' => ['m_name'],
            ];
            $list = (new DynamicModelDataTableHelper(eloquentModel: new PolicyTadaTravelAllowance(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->getServerSideDataTable();

            $rowData = array();
            $i = 0;
            foreach ($list as $key => $val) {
                $i++;
                $row = array();

                $row[] = $i;
                $row[] = (isset($val->fh_policy_tada_category->ptc_name) ? $val->fh_policy_tada_category->ptc_name : '');
                $row[] = (isset($val->fh_policy_tada_travel_type->fh_travel_type->m_name) ? $val->fh_policy_tada_travel_type->fh_travel_type->m_name : '');
                $row[] = isset($val->fh_policy_tada_travel_mode->fh_travel_mode->m_name) ? $val->fh_policy_tada_travel_mode->fh_travel_mode->m_name : '';
                $row[] = (isset($val->fh_policy_tada_travel_vehicle->fh_vehicle->m_name) ? $val->fh_policy_tada_travel_vehicle->fh_vehicle->m_name : '') .
                    (isset($val->fh_policy_tada_travel_vehicle->fh_travel_class->m_name) ? ' - ' . $val->fh_policy_tada_travel_vehicle->fh_travel_class->m_name : '') .
                    (isset($val->fh_policy_tada_travel_vehicle->fh_vehicle_owner->m_name) ? ' - ' . $val->fh_policy_tada_travel_vehicle->fh_vehicle_owner->m_name : '') .
                    (isset($val->fh_claim_type->m_name) ? ' - ' . $val->fh_claim_type->m_name : '');
                $row[] = $val->ptta_eligibility ?? '---';
                $row[] = $val->ptta_remarks;
                $row[] = '<span class="fs-11 fw-bold">W.E.F. </span><span class="with-effect-from-badge fs-10">' . date('d-M-Y h:i A', strtotime($val->updated_at)) . '</span>';
                $row[] = '<button class="btn action-btns btn-sm btn-primary edittravelAllowance"
                        data-id="' . $val->ptta_id . '"
                        data-ptta_ptc_id="' . $val->ptta_ptc_id . '"
                        data-ptta_pttt_id="' . $val->ptta_pttt_id . '"
                        data-ptta_pttm_id="' . $val->ptta_pttm_id . '"
                        data-ptta_pttv_id="' . $val->ptta_pttv_id . '"
                        data-ptta_eligibility="' . $val->ptta_eligibility . '"
                        data-ptta_claim_type_id="' . $val->ptta_claim_type_id . '"
                        data-ptta_remarks="' . $val->ptta_remarks . '">
                        <i class="feather feather-edit"></i>
                    </button>
                    <button class="btn action-btns btn-sm btn-danger deleteTravelAllowance" data-id="' . $val->ptta_id . '">
                        <i class="feather feather-trash"></i>
                    </button>';
                $rowData[] = $row;
            }

            $output = array(
                "draw" => $request->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(eloquentModel: new PolicyTadaTravelAllowance(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            );

            return json_encode($output);
        }

        $columns = [
            'S. No.',
            'Policy Category',
            'Travel Type',
            'Travel Mode',
            'Travel Vehicle List',
            'Eligibility Amount/Km',
            'Description',
            'W.E.F.',
            'Action',
        ];
        // return view('admin.setting.tada-settings.travel-allowance2', compact('columns'));


        $user = Auth::user();
        // $travelAllowanceData = PolicyTadaTravelAllowance::where('ptta_b_id', $user->emp_b_id)->get();
        $policyCategory = PolicyTadaCategory::where('ptc_b_id', $user->emp_b_id)->where('ptc_status', 1)->get();
        $travelType = PolicyTadaTravelType::with('fh_travel_type')->where('pttt_b_id', $user->emp_b_id)->where('pttt_status', 1)->get();
        $travelMode = PolicyTadaTravelMode::with('fh_travel_mode')->where('pttm_b_id', $user->emp_b_id)->where('pttm_status', 1)->get();
        $vehicleList = PolicyTadaTravelVehicle::where('pttv_b_id', $user->emp_b_id)->with('fh_vehicle:m_id,m_name', 'fh_travel_class:m_id,m_name', 'fh_vehicle_owner:m_id,m_name', 'fh_claim_type:m_id,m_name')->get();
        return view('admin.setting.tada-settings.travel-allowance2', compact('columns', 'policyCategory', 'travelType', 'travelMode', 'vehicleList'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $vehicleList = PolicyTadaTravelVehicle::where('pttv_b_id', $user->emp_b_id)
            ->where('pttv_id', $request->ptta_pttv_id)
            ->with('fh_claim_type:m_id,m_name')
            ->first();
        if (!$vehicleList) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }
        $requestData = $request->except(['_token']);
        // $requestData['ptta_claim_type_id'] = $vehicleList->fh_claim_type->m_id ?? null;
        $requestData['ptta_b_id'] = $user->emp_b_id;


        if (isset($request->ptta_id) && ($request->ptta_id)) {
            $id = $request->ptta_id;

            $duplicateDataCheck = PolicyTadaTravelAllowance::where([
                ['ptta_b_id', $user->emp_b_id],
                ['ptta_ptc_id', $request->ptta_ptc_id],
                ['ptta_pttt_id', $request->ptta_pttt_id],
                ['ptta_pttm_id', $request->ptta_pttm_id],
                ['ptta_pttv_id', $request->ptta_pttv_id],
                ['ptta_id', '!=', $request->ptta_id],
            ])->first();
            if ($duplicateDataCheck) {
                return response()->json(['error' => 'Travel allowance data duplicate found'], 200);
            }

            $travelAllowanceData = PolicyTadaTravelAllowance::where([
                'ptta_id' => $id,
                'ptta_b_id' => $user->emp_b_id
            ])->first();

            if (!$travelAllowanceData) {
                return response()->json(['error' => 'Travel allowance data not found'], 404);
            }
            $travelAllowanceData->update($requestData);
        } else {
            if($request->ptta_claim_type_id == 154){
                if(!empty($request->ptta_pttv_id)){
                    foreach($request->ptta_pttv_id as $ppi){
                        $duplicateDataCheck = PolicyTadaTravelAllowance::where([
                            ['ptta_b_id', $user->emp_b_id],
                            ['ptta_ptc_id', $request->ptta_ptc_id],
                            ['ptta_pttt_id', $request->ptta_pttt_id],
                            ['ptta_pttm_id', $request->ptta_pttm_id],
                            ['ptta_pttv_id', $ppi],
                        ])->first();
                        if ($duplicateDataCheck) {
                            return response()->json(['error' => 'Travel allowance data duplicate found'], 200);
                        }
                    }
                    foreach($request->ptta_pttv_id as $ppi){
                        $travelAllowanceData = PolicyTadaTravelAllowance::create([
                            'ptta_b_id' => $user->emp_b_id,
                            'ptta_ptc_id' => $request->ptta_ptc_id,
                            'ptta_pttt_id' => $request->ptta_pttt_id,
                            'ptta_pttm_id' => $request->ptta_pttm_id,
                            'ptta_pttv_id' => $ppi,
                            'ptta_eligibility' => $request->ptta_eligibility,
                            'ptta_claim_type_id' => $request->ptta_claim_type_id,
                            'ptta_remarks' => $request->ptta_remarks,
                        ]);
                    }
                }
            }else{
                $duplicateDataCheck = PolicyTadaTravelAllowance::where([
                    ['ptta_b_id', $user->emp_b_id],
                    ['ptta_ptc_id', $request->ptta_ptc_id],
                    ['ptta_pttt_id', $request->ptta_pttt_id],
                    ['ptta_pttm_id', $request->ptta_pttm_id],
                    ['ptta_pttv_id', $request->ptta_pttv_id],
                ])->first();
                if ($duplicateDataCheck) {
                    return response()->json(['error' => 'Travel allowance data duplicate found'], 200);
                }
                $travelAllowanceData = PolicyTadaTravelAllowance::create($requestData);
            }
            // Load related data
        }
        // $travelAllowanceData->load('fh_policy_tada_category:ptc_id,ptc_name', 'fh_policy_tada_travel_type.fh_travel_type', 'fh_policy_tada_travel_mode.fh_travel_mode', 'fh_policy_tada_travel_vehicle.fh_vehicle', 'fh_policy_tada_travel_vehicle.fh_travel_class', 'fh_policy_tada_travel_vehicle.fh_vehicle_owner', 'fh_claim_type');
        return response()->json(['success' => 'Travel Allowance has been created & Updated Successfully'], 200);
    }

    public function show($id)
    {
        // $post = PolicyTadaTravelAllowance::find($id);
        // return response()->json($post);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $vehicleList = PolicyTadaTravelVehicle::where('pttv_b_id', $user->emp_b_id)
            ->where('pttv_id', $request->ptta_pttv_id)
            ->with('fh_claim_type:m_id,m_name')
            ->first();

        if (!$vehicleList) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        $requestData = $request->except(['_token']);
        $requestData['ptta_claim_type_id'] = $vehicleList->fh_claim_type->m_id ?? null;
        $requestData['ptta_b_id'] = $user->emp_b_id;

        $travelAllowanceData = PolicyTadaTravelAllowance::where([
            'ptta_id' => $id,
            'ptta_b_id' => $user->emp_b_id
        ])->first();

        if (!$travelAllowanceData) {
            return response()->json(['error' => 'Travel allowance data not found'], 404);
        }
        $travelAllowanceData->update($requestData);
        $travelAllowanceData->load('fh_policy_tada_category:ptc_id,ptc_name', 'fh_policy_tada_travel_type.fh_travel_type', 'fh_policy_tada_travel_mode.fh_travel_mode', 'fh_policy_tada_travel_vehicle.fh_vehicle', 'fh_policy_tada_travel_vehicle.fh_travel_class', 'fh_policy_tada_travel_vehicle.fh_vehicle_owner', 'fh_claim_type');
        return response()->json($travelAllowanceData);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $travelAllowanceData = PolicyTadaTravelAllowance::where([
            'ptta_id' => $id,
            'ptta_b_id' => $user->emp_b_id
        ])->first();
        $travelAllowanceData->delete();
        return response()->json('Travel Allowance deleted successfully');
    }
}
