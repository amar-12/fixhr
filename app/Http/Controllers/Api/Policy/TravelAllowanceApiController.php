<?php

namespace App\Http\Controllers\Api\Policy;

use App\Models\TadaMetroCity;
use App\Models\TadaRequestDetail;
use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Policy\TravelAllowanceRequest;
use App\Http\Resources\MasterTableResource;
use App\Http\Resources\Policy\TravelAllowanceResource;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\MasterTable;
use App\Models\PolicyTadaCategory;
use App\Models\PolicyTadaDailyAllowanceLodging;
use App\Models\PolicyTadaLodging;
use App\Models\PolicyTadaTravelAllowance;
use App\Models\PolicyTadaTravelVehicle;
use App\Models\PolicyTadaDailyAllowance;
use App\Models\PolicyTadaTravelType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Log;

class TravelAllowanceApiController extends Controller
{


    public function allowanceEligibility(Request $request)
    {
        $user = Auth::user();

        $policyCategory = PolicyTadaCategory::where([
            'ptc_b_id' => $user->emp_b_id,
            'ptc_d_id' => $user->emp_d_id,
            'ptc_grade_id' => $user->emp_grade_id
        ])->whereJsonContains('ptc_dg_id', $user->emp_dg_id)->first();

        if(!$policyCategory) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'The logged-in user does not have an associated policy category.']);
        }

        $response = $this->myGetEligibilityByPolicyTravelTypeDuplicate($policyCategory);
        return ReturnHelper::jsonApiReturn($response);
    }


    public function myGetEligibilityByPolicyTravelTypeDuplicate($policyCategory)
    {
        // Start my
        $user = Auth::user();
        $policyCategoryId = json_decode($policyCategory->ptc_pttt_id);
        $dg = Designation::where('dg_id', $user->emp_dg_id)->first();
        $policyCategoryId = json_decode($policyCategory->ptc_pttt_id);
        $eligibility = [];
        foreach ($policyCategoryId as $index => $ID) {
            $pttt_id = PolicyTadaTravelType::where('pttt_id', $ID)->pluck('pttt_type_id')->first();


            if (!$pttt_id) {
                return ['message' => 'No matching travel type found'];
            }

            $data = MasterTable::where('m_id', $pttt_id)->first();

            if ($data->m_id == 124) { // Local
                $eligibility['local'] = [
                    'da_eligibility' => ['da_cal_type_id' => null, 'da_cal_max_limit' => null, 'da_amount' => null, 'da_amount2'=>null],
                    // 'lodging_eligibility' => [
                    //     'metro' => [
                    //         'lodging_eligibility_with_bill' => ['single_occupancy' => 0, 'double_occupancy' => 0],
                    //         'lodging_eligibility_without_bill' => ['single_occupancy' => 0, 'double_occupancy' => 0]
                    //     ],
                    //     'non_metro' => [
                    //         'lodging_eligibility_with_bill' => ['single_occupancy' => 0, 'double_occupancy' => 0],
                    //         'lodging_eligibility_without_bill' => ['single_occupancy' => 0, 'double_occupancy' => 0]
                    //     ],
                    // ],
                    'vehicle_eligibility' => [],
                    'employee_details' => [[]],
                ];
            } elseif ($data->m_id == 125) { // Outstation
                $eligibility['outstation'] = [
                    'da_eligibility' => ['da_cal_type_id' => null, 'da_cal_max_limit' => null, 'da_amount' => null],
                    'lodging_eligibility' => [
                        'metro' => [
                            'lodging_eligibility_with_bill' => ['single_occupancy' => 0, 'double_occupancy' => 0],
                            'lodging_eligibility_without_bill' => ['single_occupancy' => 0, 'double_occupancy' => 0]
                        ],
                        'non_metro' => [
                            'lodging_eligibility_with_bill' => ['single_occupancy' => 0, 'double_occupancy' => 0],
                            'lodging_eligibility_without_bill' => ['single_occupancy' => 0, 'double_occupancy' => 0]
                        ],
                    ],
                    'vehicle_eligibility' => [],
                    'employee_details' => [[]],
                ];
            } elseif ($data->m_id == 126) { // International
                $eligibility['international'] = [
                    'da_eligibility' => ['da_cal_type_id' => null, 'da_cal_max_limit' => null, 'da_amount' => null],
                    'lodging_eligibility' => [
                        'metro' => [
                            'lodging_eligibility_with_bill' => ['single_occupancy' => 0, 'double_occupancy' => 0],
                            'lodging_eligibility_without_bill' => ['single_occupancy' => 0, 'double_occupancy' => 0]
                        ],
                        'non_metro' => [
                            'lodging_eligibility_with_bill' => ['single_occupancy' => 0, 'double_occupancy' => 0],
                            'lodging_eligibility_without_bill' => ['single_occupancy' => 0, 'double_occupancy' => 0]
                        ],
                    ],
                    'vehicle_eligibility' => [],
                    'employee_details' => [[]],
                ];
            }


            // Ensure $policyCategory and $dg objects are not null
            if ($data->m_id == 124) {
                $eligibility['local']['employee_details'] = [
                    'code' => $user->emp_code ?? '',
                    'name' => $user->emp_full_name ?? '',
                    'designation' => $dg->dg_name ?? '', // Ensure $dg is not null
                    'category' => $policyCategory->ptc_name ?? '', // Ensure $policyCategory is not null
                ];
            } elseif ($data->m_id == 125) {
                $eligibility['outstation']['employee_details'] = [
                    'code' => $user->emp_code ?? '',
                    'name' => $user->emp_full_name ?? '',
                    'designation' => $dg->dg_name ?? '', // Ensure $dg is not null
                    'category' => $policyCategory->ptc_name ?? '', // Ensure $policyCategory is not null
                ];
            } elseif ($data->m_id == 126) {
                $eligibility['international']['employee_details'] = [
                    'code' => $user->emp_code ?? '',
                    'name' => $user->emp_full_name ?? '',
                    'designation' => $dg->dg_name ?? '', // Ensure $dg is not null
                    'category' => $policyCategory->ptc_name ?? '', // Ensure $policyCategory is not null
                ];
            }


            // Fetch vehicles with null check for $policyCategory
            $vehicles = PolicyTadaTravelVehicle::with('fh_policy_tada_travel_mode.fh_travel_type')
                ->where('pttv_b_id', $user->emp_b_id)
                ->where('pttv_ptc_id', $policyCategory->ptc_id ?? 0)
                ->whereHas('fh_policy_tada_travel_mode', function ($query) use ($ID) {
                    $query->where('pttm_pttt_id', $ID);
                })
                ->get();

            // Fetch daily allowance with null check
            $dailyallowance = PolicyTadaDailyAllowance::where('ptda_b_id', $user->emp_b_id)
                ->where('ptda_ptc_id', $policyCategory->ptc_id ?? 0)
                ->where('ptda_pttt_id', $ID)
                ->first();

            // Fetch lodging allowance with null check
            $lodgingAllowance = PolicyTadaLodging::where('ptl_b_id', $user->emp_b_id)
                ->where('ptl_ptc_id', $policyCategory->ptc_id ?? 0)
                ->where('ptl_pttt_id', $ID)
                ->get();

            // Handle vehicles data
            foreach ($vehicles as $vehicle) {
                $vehicle_name = DB::table('master_table')
                    ->where('m_id', $vehicle->pttv_vehicle_id ?? 0)
                    ->where('m_group', 'VEHICLE')
                    ->value('m_name') ?? '';

                $vehicle_class_name = DB::table('master_table')
                    ->where('m_id', $vehicle->pttv_class_id ?? 0)
                    ->where('m_group', 'TRAVEL_CLASS')
                    ->value('m_name') ?? '';

                $claim_type_name = DB::table('master_table')
                    ->where('m_id', $vehicle->pttv_claim_type_id ?? 0)
                    ->where('m_group', 'CLAIM_TYPE')
                    ->value('m_name') ?? '';

                $vehicle_owner = DB::table('master_table')
                    ->where('m_id', $vehicle->pttv_owner_id ?? 0)
                    ->where('m_group', 'VEHICLE_OWNER')
                    ->value('m_name') ?? '';

                // Conditional concatenation
                $full_vehicle_name = $vehicle_owner ? $vehicle_name . ' - ' . $vehicle_owner : $vehicle_name;


                if ($data->m_id == 124) {
                    $eligibility['local']['vehicle_eligibility'][] = [
                        'vehicle_name' => $full_vehicle_name,
                        'vehicle_class_name' => $vehicle_class_name ?? '',
                        'eligibility' => $vehicle->pttv_eligibility ?? 0,
                        'claim_type_id' => $vehicle->pttv_claim_type_id ?? 0,
                        'claim_type_name' => $claim_type_name ?? '',
                        'vehicle_id' => $vehicle->pttv_vehicle_id ?? 0,
                        'vehicle_mode_id' => $vehicle->pttv_pttm_id ?? 0,
                    ];
                } elseif ($data->m_id == 125) {
                    $eligibility['outstation']['vehicle_eligibility'][] = [
                        'vehicle_name' => $full_vehicle_name,
                        'vehicle_class_name' => $vehicle_class_name ?? '',
                        'eligibility' => $vehicle->pttv_eligibility ?? 0,
                        'claim_type_id' => $vehicle->pttv_claim_type_id ?? 0,
                        'claim_type_name' => $claim_type_name ?? '',
                        'vehicle_id' => $vehicle->pttv_vehicle_id ?? 0,
                        'vehicle_mode_id' => $vehicle->pttv_pttm_id ?? 0,
                    ];
                } elseif ($data->m_id == 126) {
                    $eligibility['international']['vehicle_eligibility'][] = [
                        'vehicle_name' => $full_vehicle_name,
                        'vehicle_class_name' => $vehicle_class_name ?? '',
                        'eligibility' => $vehicle->pttv_eligibility ?? 0,
                        'claim_type_id' => $vehicle->pttv_claim_type_id ?? 0,
                        'claim_type_name' => $claim_type_name ?? '',
                        'vehicle_id' => $vehicle->pttv_vehicle_id ?? 0,
                        'vehicle_mode_id' => $vehicle->pttv_pttm_id ?? 0,
                    ];
                }
            }

            // Handle daily allowance eligibility
            if ($dailyallowance) {
                $da_cal_type_collection = MasterTableResource::collection($dailyallowance->fh_policy_tada_daily_allowance_cal_type ? [$dailyallowance->fh_policy_tada_daily_allowance_cal_type] : []);$da_cal_type_collection = MasterTableResource::collection(
                    $dailyallowance->fh_policy_tada_daily_allowance_cal_type
                        ? [$dailyallowance->fh_policy_tada_daily_allowance_cal_type]
                        : []
                );
                $da_cal_type_id = optional($da_cal_type_collection->first())->m_id;

                if($da_cal_type_id == 240) {
                    $ptda_da_cal_limit = explode('|', $dailyallowance->ptda_da_cal_limit);
                    $da_amount = (isset($ptda_da_cal_limit[0]) ? $ptda_da_cal_limit[0] : 'N/A') . '|' . (isset($ptda_da_cal_limit[1]) ? $ptda_da_cal_limit[1] : 'N/A') . '-' . $dailyallowance->ptda_da_amount;
                    $da_amount2 = (isset($ptda_da_cal_limit[2]) ? $ptda_da_cal_limit[2] : 'N/A') . '|' . (isset($ptda_da_cal_limit[3]) ? $ptda_da_cal_limit[3] : 'N/A') . '-' . $dailyallowance->ptda_da_amount2;
                } else {
                    $da_amount = isset($dailyallowance->ptda_da_amount) ? $dailyallowance->ptda_da_amount : null;
                    $da_amount2 = isset($dailyallowance->ptda_da_amount2) ? $dailyallowance->ptda_da_amount2 : null;
                }
                $da_eligibility = [
                    'da_cal_type_id' => MasterTableResource::collection($dailyallowance->fh_policy_tada_daily_allowance_cal_type ? [$dailyallowance->fh_policy_tada_daily_allowance_cal_type] : []),
                    'da_cal_max_limit' => $dailyallowance->ptda_da_cal_limit,
                    'da_amount' => $da_amount,
                    'da_amount2' => $da_amount2,
                ];

                if ($data->m_id == 124) {
                    $eligibility['local']['da_eligibility'] = $da_eligibility;
                } elseif ($data->m_id == 125) {
                    $eligibility['outstation']['da_eligibility'] = $da_eligibility;
                } elseif ($data->m_id == 126) {
                    $eligibility['international']['da_eligibility'] = $da_eligibility;
                }
            }



            // Handle lodging allowance
            if (count($lodgingAllowance)) {
                foreach ($lodgingAllowance as $lodging) {

                    // Determine metro or non-metro

                    if ($lodging->ptl_ct_type_id == 23) {
                        if ($data->m_id == 124) {
                            $eligibility['local']['lodging_eligibility']['metro'] = [
                                'lodging_eligibility_with_bill' => [
                                    'single_occupancy' => $lodging->ptl_sngl_w_bill ?? 0,
                                    'double_occupancy' => $lodging->ptl_dbl_w_bill ?? 0,
                                ],
                                'lodging_eligibility_without_bill' => [
                                    'single_occupancy' => $lodging->ptl_sngl_wo_bill ?? 0,
                                    'double_occupancy' => $lodging->ptl_dbl_wo_bill ?? 0,
                                ]
                            ];
                        } elseif ($data->m_id == 125) {
                            $eligibility['outstation']['lodging_eligibility']['metro'] = [
                                'lodging_eligibility_with_bill' => [
                                    'single_occupancy' => $lodging->ptl_sngl_w_bill ?? 0,
                                    'double_occupancy' => $lodging->ptl_dbl_w_bill ?? 0,
                                ],
                                'lodging_eligibility_without_bill' => [
                                    'single_occupancy' => $lodging->ptl_sngl_wo_bill ?? 0,
                                    'double_occupancy' => $lodging->ptl_dbl_wo_bill ?? 0,
                                ]
                            ];
                        } elseif ($data->m_id == 126) {
                            $eligibility['international']['lodging_eligibility']['metro'] = [
                                'lodging_eligibility_with_bill' => [
                                    'single_occupancy' => $lodging->ptl_sngl_w_bill ?? 0,
                                    'double_occupancy' => $lodging->ptl_dbl_w_bill ?? 0,
                                ],
                                'lodging_eligibility_without_bill' => [
                                    'single_occupancy' => $lodging->ptl_sngl_wo_bill ?? 0,
                                    'double_occupancy' => $lodging->ptl_dbl_wo_bill ?? 0,
                                ]
                            ];
                        }
                    } elseif ($lodging->ptl_ct_type_id == 24) {
                        if ($data->m_id == 124) {
                            $eligibility['local']['lodging_eligibility']['non_metro'] = [
                                'lodging_eligibility_with_bill' => [
                                    'single_occupancy' => $lodging->ptl_sngl_w_bill ?? 0,
                                    'double_occupancy' => $lodging->ptl_dbl_w_bill ?? 0,
                                ],
                                'lodging_eligibility_without_bill' => [
                                    'single_occupancy' => $lodging->ptl_sngl_wo_bill ?? 0,
                                    'double_occupancy' => $lodging->ptl_dbl_wo_bill ?? 0,
                                ]
                            ];
                        } elseif ($data->m_id == 125) {
                            $eligibility['outstation']['lodging_eligibility']['non_metro'] = [
                                'lodging_eligibility_with_bill' => [
                                    'single_occupancy' => $lodging->ptl_sngl_w_bill ?? 0,
                                    'double_occupancy' => $lodging->ptl_dbl_w_bill ?? 0,
                                ],
                                'lodging_eligibility_without_bill' => [
                                    'single_occupancy' => $lodging->ptl_sngl_wo_bill ?? 0,
                                    'double_occupancy' => $lodging->ptl_dbl_wo_bill ?? 0,
                                ]
                            ];
                        } elseif ($data->m_id == 126) {
                            $eligibility['international']['lodging_eligibility']['non_metro'] = [
                                'lodging_eligibility_with_bill' => [
                                    'single_occupancy' => $lodging->ptl_sngl_w_bill ?? 0,
                                    'double_occupancy' => $lodging->ptl_dbl_w_bill ?? 0,
                                ],
                                'lodging_eligibility_without_bill' => [
                                    'single_occupancy' => $lodging->ptl_sngl_wo_bill ?? 0,
                                    'double_occupancy' => $lodging->ptl_dbl_wo_bill ?? 0,
                                ]
                            ];
                        }
                    }
                }
            }
        }

        return [$eligibility];
    }


    public function getEligibilityByPolicyTravelType($policyCategory, $policy_travel_type_id)
    {

        // Start my
        $user = Auth::user();
        $eligibility = [
            'da_eligibility' => ['da_cal_type_id' => null, 'da_cal_max_limit' => null, 'da_amount' => null, 'da_amount2'],
            'lodging_eligibility' => [
                'metro' => ['lodging_eligibility_with_bill' => ['single_occupancy' => 0, 'double_occupancy' => 0], 'lodging_eligibility_without_bill' => ['single_occupancy' => 0, 'double_occupancy' => 0]],
                'non_metro' => ['lodging_eligibility_with_bill' => ['single_occupancy' => 0, 'double_occupancy' => 0], 'lodging_eligibility_without_bill' => ['single_occupancy' => 0, 'double_occupancy' => 0]],
            ],
            'vehicle_eligibility' => [],
            'employee_details' => [],
        ];

        // Ensure $policyCategory and $dg objects are not null
        $eligibility['employee_details'] = [
            'code' => $user->emp_code ?? '',
            'name' => $user->emp_full_name ?? '',
            'designation' => $dg->dg_name ?? '', // Ensure $dg is not null
            'category' => $policyCategory->ptc_name ?? '', // Ensure $policyCategory is not null
        ];

        // Fetch vehicles with null check for $policyCategory
        $vehicles = PolicyTadaTravelVehicle::with('fh_policy_tada_travel_mode.fh_travel_type')
            ->where('pttv_b_id', $user->emp_b_id)
            ->where('pttv_ptc_id', $policyCategory->ptc_id ?? 0)
            ->whereHas('fh_policy_tada_travel_mode', function ($query) use ($policy_travel_type_id) {
                $query->where('pttm_pttt_id', $policy_travel_type_id);
            })
            ->get();

        // Fetch daily allowance with null check
        $dailyallowance = PolicyTadaDailyAllowance::where('ptda_b_id', $user->emp_b_id)
            ->where('ptda_ptc_id', $policyCategory->ptc_id ?? 0)
            ->where('ptda_pttt_id', $policy_travel_type_id)
            ->first();

        // Fetch lodging allowance with null check
        $lodgingAllowance = PolicyTadaLodging::where('ptl_b_id', $user->emp_b_id)
            ->where('ptl_ptc_id', $policyCategory->ptc_id ?? 0)
            ->where('ptl_pttt_id', $policy_travel_type_id)
            ->get();

        // Handle vehicles data
        foreach ($vehicles as $vehicle) {
            $vehicle_name = DB::table('master_table')
                ->where('m_id', $vehicle->pttv_vehicle_id ?? 0)
                ->where('m_group', 'VEHICLE')
                ->value('m_name') ?? '';

            $vehicle_class_name = DB::table('master_table')
                ->where('m_id', $vehicle->pttv_class_id ?? 0)
                ->where('m_group', 'TRAVEL_CLASS')
                ->value('m_name') ?? '';

            $claim_type_name = DB::table('master_table')
                ->where('m_id', $vehicle->pttv_claim_type_id ?? 0)
                ->where('m_group', 'CLAIM_TYPE')
                ->value('m_name') ?? '';

            $vehicle_owner = DB::table('master_table')
                ->where('m_id', $vehicle->pttv_owner_id ?? 0)
                ->where('m_group', 'VEHICLE_OWNER')
                ->value('m_name') ?? '';

            // Conditional concatenation
            $full_vehicle_name = $vehicle_owner ? $vehicle_name . ' - ' . $vehicle_owner : $vehicle_name;

            $eligibility['vehicle_eligibility'][] = [
                'vehicle_name' => $full_vehicle_name,
                'vehicle_class_name' => $vehicle_class_name ?? '',
                'eligibility' => $vehicle->pttv_eligibility ?? 0,
                'claim_type_id' => $vehicle->pttv_claim_type_id ?? 0,
                'claim_type_name' => $claim_type_name ?? '',
                'vehicle_id' => $vehicle->pttv_vehicle_id ?? 0,
                'vehicle_mode_id' => $vehicle->pttv_pttm_id ?? 0,
            ];
        }

        // Handle daily allowance eligibility
        if ($dailyallowance) {
            $eligibility['da_eligibility'] = [
                'da_cal_type_id' => $dailyallowance->ptda_da_cal_type_id,
                'da_cal_max_limit' => $dailyallowance->ptda_da_cal_limit,
                'da_amount' => $dailyallowance->ptda_da_amount,
                'da_amount2' => $dailyallowance->ptda_da_amount2,
                'distance' => $dailyallowance->ptda_distance,
                'lodging' => $dailyallowance->ptda_lodging,
                'half_da' => $dailyallowance->ptda_half_da,
            ];
        }



        // Handle lodging allowance
        if (count($lodgingAllowance)) {
            foreach ($lodgingAllowance as $lodging) {

                // Determine metro or non-metro
                if ($lodging->ptl_ct_type_id == 23) {
                    $eligibility['lodging_eligibility']['metro'] = [
                        'lodging_eligibility_with_bill' => [
                            'single_occupancy' => $lodging->ptl_sngl_w_bill ?? 0,
                            'double_occupancy' => $lodging->ptl_dbl_w_bill ?? 0,
                        ],
                        'lodging_eligibility_without_bill' => [
                            'single_occupancy' => $lodging->ptl_sngl_wo_bill ?? 0,
                            'double_occupancy' => $lodging->ptl_dbl_wo_bill ?? 0,
                        ]
                    ];
                } elseif ($lodging->ptl_ct_type_id == 24) {
                    $eligibility['lodging_eligibility']['non_metro'] = [
                        'lodging_eligibility_with_bill' => [
                            'single_occupancy' => $lodging->ptl_sngl_w_bill ?? 0,
                            'double_occupancy' => $lodging->ptl_dbl_w_bill ?? 0,
                        ],
                        'lodging_eligibility_without_bill' => [
                            'single_occupancy' => $lodging->ptl_sngl_wo_bill ?? 0,
                            'double_occupancy' => $lodging->ptl_dbl_wo_bill ?? 0,
                        ]
                    ];
                }
            }
        }




        return $eligibility;
    }


    public function eligibility(Request $request, $id)
    {
        // get eligibility by vehicle id
        $user = Auth::user();

        $cat = PolicyTadaCategory::where('ptc_b_id', $user->emp_b_id)->where('ptc_d_id', $user->emp_d_id)->where('ptc_grade_id', $user->emp_grade_id)->whereJsonContains('ptc_dg_id', $user->emp_dg_id)->first();

        if ($cat) {
            $vehicle = PolicyTadaTravelAllowance::where('ptta_b_id', $user->emp_b_id)->where('ptta_ptc_id', $cat->ptc_id)->where('ptta_pttv_id', $id)->first();

            if ($vehicle) {
                $eligibility = [
                    'eligibility' => $vehicle->ptta_eligibility ?? 0,
                    'other_eligibility' => $vehicle->ptta_other_eligibility ?? 0,
                    'claim_type_id' => $vehicle->ptta_claim_type_id ?? 0,
                ];
                return response()->json(['result' => [$eligibility], 'status' => true]);
            }
        }
        return response()->json(['result' => [], 'status' => false, 'message' => 'No travel allowance found for this business']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $allowance = PolicyTadaTravelAllowance::where('ptta_b_id', $user->emp_b_id)->get();
        if ($allowance) {
            return ReturnHelper::jsonApiReturn(TravelAllowanceResource::collection($allowance)->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TravelAllowanceRequest $request)
    {
        $user = Auth::user();

        $allowance = $request->validated();

        // Create a new PolicyTadaTravelAllowance instance and set its attributes
        $data = new PolicyTadaTravelAllowance();
        $data->ptta_b_id = $user->emp_b_id; // Assign the correct integer value
        $data->ptta_ptc_id = $allowance['allowance_category_id'];
        $data->ptta_pttm_id = $allowance['allowance_travelmode_id'];
        $data->ptta_pttt_id = $allowance['allowance_traveltype_id'];
        $data->ptta_eligibility = $allowance['eligibility'];
        $data->ptta_other_eligibility = $allowance['other_eligibility'];
        $data->ptta_remarks = $allowance['remarks'];

        // Save the data
        if ($data->save()) {
            return ReturnHelper::jsonApiReturn(TravelAllowanceResource::collection([$data])->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = Auth::user();
        $allowance = PolicyTadaTravelAllowance::find($id);
        if ($allowance) {
            return ReturnHelper::jsonApiReturn(TravelAllowanceResource::collection($allowance)->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();

        $allowance = PolicyTadaTravelAllowance::find($id);
        $allowance->ptta_b_id = $user->emp_b_id ?? $allowance->business_id;
        $allowance->ptta_ptc_id = $request->allowance_category_id ?? $allowance->allowance_category_id;
        $allowance->ptta_pttm_id = $request->allowance_travelmode_id ?? $allowance->allowance_travelmode_id;
        $allowance->ptta_pttt_id = $request->allowance_traveltype_id ?? $allowance->allowance_traveltype_id;
        $allowance->ptta_eligibility = $request->eligibility ?? $allowance->eligibility;
        $allowance->ptta_other_eligibility = $request->other_eligibility ?? $allowance->other_eligibility;
        $allowance->ptta_remarks = $request->remarks ?? $allowance->remarks;

        if ($allowance->save()) {
            return ReturnHelper::jsonApiReturn(TravelAllowanceResource::collection([$allowance])->all());
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $allowance = PolicyTadaTravelAllowance::where('ptta_id', $id)
            ->where('ptta_b_id', $user->emp_b_id)
            ->first();


        if ($allowance) {
            $allowance->delete();
            return ReturnHelper::jsonApiReturn(true);
        }
        return response()->json(['result' => [], 'status' => false]);
    }


    public function calculateDailyAllowance($plan, $da_policy)
    {
        $totalDA = 0;
        $calculationMessage = '';
        // $calculationMessages[] = [ 'index' => '', 'da_cal_type_id' => '', 'day_start' => '', 'day_end' => '', 'hours' => '', 'amount' => '', 'total_distance' => '', 'start_date' => '', 'end_date' => '', 'days' => '', 'stay' => '', 'stay_arranged_by' => '' ];
        if ($plan->fh_policy_tada_travel_type->fh_travel_type->m_id == 124) { //124==Local
            $travelDetails = TadaRequestDetail::where('trd_trp_id', $plan->trp_id)->get();
            if ($da_policy['da_cal_type_id'] == 239) { //239==Hour Wise
                $firstEntry = $travelDetails->first();
                $lastEntry = $travelDetails->last();
                $start_time = null;
                $end_time = null;
                if ($firstEntry) {
                    if ($firstEntry->trd_segments) {
                        $start_time = count(json_decode($firstEntry->trd_segments, true)) ? json_decode($firstEntry->trd_segments, true)[0]['time'] : null;
                    } else {
                        $start_time = $firstEntry->trd_start_time;
                    }
                    if ($start_time) {
                        $start_time = Carbon::parse($start_time);
                    }
                }
                if ($lastEntry) {
                    if ($lastEntry->trd_segments) {

                        $segments = json_decode($firstEntry->trd_segments, true);
                        $end_time = (is_array($segments) && count($segments)) ? $segments[count($segments) - 1]['time'] : null;

                        // $end_time = count(json_decode($firstEntry->trd_segments, true)) ? json_decode($firstEntry->trd_segments, true)[count(json_decode($firstEntry->trd_segments, true)) - 1]['time'] : null;
                    } else {
                        $end_time = $lastEntry->trd_end_time;
                    }
                    if ($end_time) {
                        $end_time =  Carbon::parse($end_time);
                    }
                }
                if ($start_time && $end_time) {
                    $diffInHours = number_format($start_time->diffInHours($end_time), 2);
                } else {
                    $diffInHours = 0;
                }
                // $calculationMessage = "Daily allowance calculated based on travel duration of $diffInHours hours.";
                if ($diffInHours >= (int) $da_policy['da_cal_max_limit']) {
                    $totalDA = $da_policy['da_amount'];
                }
                $calculationMessages[] = ['index' => 1, 'da_cal_type_id' => $da_policy['da_cal_type_id'], 'day_start' => $start_time, 'day_end' => $end_time, 'hours' => $diffInHours, 'amount' => $totalDA, 'total_distance' => '', 'start_date' => '', 'end_date' => '', 'days' => '', 'stay' => '', 'stay_arranged_by' => ''];

                $calculationMessage = json_encode($calculationMessages);
            }elseif ($da_policy['da_cal_type_id'] == 240) { // 240 == Distance Wise
                $totalDistance = number_format($travelDetails->sum('trd_total_distance'), 2);
                $from_km = (int) explode('|', $da_policy['da_cal_max_limit'])[0];
                $to_km = (int) explode('|', $da_policy['da_cal_max_limit'])[1];
                $from_km2 = isset(explode('|', $da_policy['da_cal_max_limit'])[2]) ? (int) explode('|', $da_policy['da_cal_max_limit'])[2] : 0;
                $to_km2 = isset(explode('|', $da_policy['da_cal_max_limit'])[3]) ?  (int) explode('|', $da_policy['da_cal_max_limit'])[3] : 0;
                // $calculationMessage = "Daily allowance calculated based on total travel distance of $totalDistance km.";
                if ($totalDistance >= $from_km && $totalDistance <= $to_km) {
                    $totalDA = $da_policy['da_amount'];
                }elseif(($totalDistance >= $from_km2 && $totalDistance <= $to_km2) || $totalDistance > $to_km2){
                    $totalDA =  $da_policy['da_amount2'];
                }

                $calculationMessages[] = ['index' => 1, 'da_cal_type_id' => $da_policy['da_cal_type_id'], 'day_start' => '', 'day_end' => '', 'hours' => '', 'amount' => $totalDA, 'total_distance' => $totalDistance, 'start_date' => '', 'end_date' => '', 'days' => '', 'stay' => '', 'stay_arranged_by' => ''];
                $calculationMessage = json_encode($calculationMessages);
            }elseif ($da_policy['da_cal_type_id'] == 238){ // 238 == Day Wise calculation
                $minDateTime = $this->getStartEndDateTimeAndDistance($plan)['minDateTime'];
                $maxDateTime = $this->getStartEndDateTimeAndDistance($plan)['maxDateTime'];
                $startDate = Carbon::parse($minDateTime);
                $endDate = Carbon::parse($maxDateTime);
                $daysOfTravel = floor($startDate->diffInDays($endDate) + 1);
                $totalDA = number_format($daysOfTravel, 2) * $da_policy['da_amount'];
                $calculationMessages[] = [
                    'index' => 1,
                    'da_cal_type_id' => $da_policy['da_cal_type_id'],
                    'day_start' => '',
                    'day_end' => '',
                    'hours' => '',
                    'amount' => $totalDA,
                    'total_distance' => '',
                    'start_date' => $startDate->format('d-M-Y'),
                    'end_date' => $endDate->format('d-M-Y'),
                    'days' => $daysOfTravel,
                    'stay' => '',
                    'stay_arranged_by' => ''
                ];
                $calculationMessage = json_encode($calculationMessages);
            }
        } elseif ($plan->fh_policy_tada_travel_type->fh_travel_type->m_id == 125) {
            if ($da_policy['da_cal_type_id'] == 238) { //238==Day Wise calculation
                $minDateTime = $this->getStartEndDateTimeAndDistance($plan)['minDateTime'];
                $maxDateTime = $this->getStartEndDateTimeAndDistance($plan)['maxDateTime'];
                $startDate = Carbon::parse($minDateTime);
                $endDate = Carbon::parse($maxDateTime);
                // Use floor to round down to the nearest whole number
                $daysOfTravel = floor($startDate->diffInDays($endDate) + 1);
                $totalDA = number_format($daysOfTravel, 2) * $da_policy['da_amount'];
                $calculationMessages[] = ['index' => 1, 'da_cal_type_id' => $da_policy['da_cal_type_id'], 'day_start' => '', 'day_end' => '', 'hours' => '', 'amount' => $totalDA, 'total_distance' => '', 'start_date' => $startDate->format('d-M-Y'), 'end_date' => $endDate->format('d-M-Y'), 'days' => $daysOfTravel, 'stay' => '', 'stay_arranged_by' => ''];
                $calculationMessage = json_encode($calculationMessages);
            } elseif ($da_policy['da_cal_type_id'] == 239) { //239==Hour Wise
                $getDailyAllowance =  $this->getDailyAllowance($plan, $da_policy);//$da_policy['da_amount'], $da_policy['da_cal_type_id']);
                $totalDA = $getDailyAllowance['total_payable_da_amount'];
                $calculationMessage = $getDailyAllowance['calculation_message'];
            }
        }

        // Days Wise
        // 0 => array:5 [
        //     "index" => 1
        //     "Start Date  " => "09-Oct-2024"
        //     "End Date" => "12-Oct-2024"
        //     "Days" => 4.0
        //     "Amount" => 2000.0
        //   ]
        return [
            'totalDA' => $totalDA,
            'calculationMessage' => $calculationMessage
        ];
    }

    // $da_policy['da_amount'], $da_policy['da_cal_type_id']);
    // $perDayDaEligibility, $da_cal_type_id)
    public function getDailyAllowance($plan, $da_policy)
    {
        // Initialize variables to hold min and max date and time
        $minDateTime = $this->getStartEndDateTimeAndDistance($plan)['minDateTime'];
        $maxDateTime = $this->getStartEndDateTimeAndDistance($plan)['maxDateTime'];
        $totalDistance = $this->getStartEndDateTimeAndDistance($plan)['totalDistance'];

        // Calculate the total daytime hours and messages
        $startDateTime = new DateTime($minDateTime);
        $endDateTime = new DateTime($maxDateTime);
        $dayStart = new DateTime($startDateTime->format('Y-m-d H:i:s'));
        $dayEnd = new DateTime($endDateTime->format('Y-m-d H:i:s'));

        $totalDaytimeHours = 0;
        $i = 0;
        $calculationMessages = [];
        $daAmount = 0;
        while ($dayStart < $dayEnd) {
            ++$i;

            // Check if the current day is the last day
            if (new DateTime($dayStart->format('Y-m-d')) == new DateTime($dayEnd->format('Y-m-d'))) {
                // On the last day, check if the end time is after 23:59:00
                $effectiveEnd = ($dayEnd->format('H:i:s') > '23:59:00') ?
                    (new DateTime($dayEnd->format('Y-m-d') . ' 23:59:00')) : $dayEnd;
            } else {
                // For other days, set the effective end time to 23:59:00
                // $dayStart = new DateTime($dayStart->format('Y-m-d') . ' 06:00:00');
                $effectiveEnd = new DateTime($dayStart->format('Y-m-d') . ' 23:59:00');
            }

            // Calculate the interval and daytime hours
            $interval = $dayStart->diff($effectiveEnd);
            $daytimeHours = ($interval->h) + ($interval->i / 60); // Convert to decimal hours

            $lodging_info = [
                'index' => $i,
                'day_start' => $dayStart->format('Y-m-d H:i:s'),
                'day_end' => $effectiveEnd->format('Y-m-d H:i:s'),
                'hours' => $daytimeHours,
            ];

            $lodging_info = ['index' => $i, 'da_cal_type_id' => $da_policy['da_cal_type_id'], 'day_start' => $dayStart->format('Y-m-d H:i:s'), 'day_end' => $effectiveEnd->format('Y-m-d H:i:s'), 'hours' => $daytimeHours, 'amount' => '', 'total_distance' => '', 'start_date' => '', 'end_date' => '', 'days' => '', 'stay' => '', 'stay_arranged_by' => ''];

            $stay = null;

            if($da_policy['lodging']) {
                $stay = $plan->fh_tada_expenses()
                    ->select('te_from_date', 'te_from_time', 'te_to_date', 'te_to_time', 'te_paid_by')
                    ->where('te_type_id', 158)
                    ->whereRaw('? BETWEEN CONCAT(te_from_date, " ", te_from_time) AND CONCAT(te_to_date, " ", te_to_time)', [$effectiveEnd->format('Y-m-d H:i:s')])
                    ->first();
            }

            if ($totalDistance > $da_policy['distance'] && $stay) { // distance and lodging
                $lodging_info['stay'] = 1;
                $lodging_info['stay_arranged_by'] = $stay->te_paid_by;
                $lodging_info['amount'] = $stay->te_paid_by == 'company' && ($da_policy['half_da'] == 1) ? ($da_policy['da_amount'] / 2) : $da_policy['da_amount'];
            } else { //This section calculates the Daily Allowance (DA) based on hours within a day: like 8 hours qualify for half DA, fewer than 8 hours do not qualify for DA, and more than 8 hours qualify for full DA.
                $lodging_info['stay'] = 0;
                $lodging_info['stay_arranged_by'] = '';
                $lodging_info['amount'] = ($daytimeHours == $da_policy['da_cal_max_limit']) ? ($da_policy['da_amount'] / 2) : (($daytimeHours > $da_policy['da_cal_max_limit']) ? $da_policy['da_amount'] : 0);
            }

            $daAmount += $lodging_info['amount'];

            $calculationMessages[] = $lodging_info;

            $totalDaytimeHours += $daytimeHours;

            // Move to the next day
            $dayStart = new DateTime($dayStart->format('Y-m-d') . ' 06:00:00');
            $dayStart->modify('+1 day');
        }

        return [
            'start_date_time' => $startDateTime,
            'end_date_time' => $endDateTime,
            'per_day_da_eligibility_amount' => $da_policy['da_amount'],
            'total_travel_hours' => $totalDaytimeHours,
            'calculation_message' => json_encode($calculationMessages),
            'total_payable_da_amount' => $daAmount,
            'total_travel_distance' => number_format($totalDistance, 2)
        ];
    }

    function getTravelStartAndEndDateTime(&$minDateTime, &$maxDateTime, $dateTime)
    {
        if ($minDateTime === null || $dateTime < $minDateTime) {
            $minDateTime = $dateTime;
        }
        if ($maxDateTime === null || $dateTime > $maxDateTime) {
            $maxDateTime = $dateTime;
        }

        return ['minDateTime' => $minDateTime, 'maxDateTime' => $maxDateTime];
    }

    public function getStartEndDateTimeAndDistance($plan)
    {

        $minDateTime = null;
        $maxDateTime = null;
        $totalDistance = 0;

        // Get travel details that match the conditions
        $travelDetails = $plan->fh_tada_request_details->filter(function ($detail) {
            return $detail->trd_type_id == 181 || !is_null($detail->trd_segments); // check travel id = 181 and travel tap location only
        });

        // Fetch the expenses related to the travel record
        $travelExpenses = $plan->fh_tada_expenses;
        // Check if the relationship returns a collection
        if ($travelExpenses) {
            // Filter expenses with te_type_id equal to 159
            $travelExpenses = $travelExpenses->filter(function ($expense) {
                return $expense->te_type_id == 159;
            });
        }


        // Function to compare and set min and max date times

        // Loop through each travel detail
        foreach ($travelDetails as $detail) {
            $totalDistance += $detail->trd_total_distance;
            // Check if trd_start_date and trd_start_time have values
            if (!empty($detail->trd_start_date) && !empty($detail->trd_start_time)) {
                $compareAndSetRes = $this->getTravelStartAndEndDateTime($minDateTime, $maxDateTime, $detail->trd_start_date . ' ' . $detail->trd_start_time);
                $minDateTime = $compareAndSetRes['minDateTime'];
                $maxDateTime = $compareAndSetRes['maxDateTime'];
            }
            // Check if trd_end_date and trd_end_time have values
            if (!empty($detail->trd_end_date) && !empty($detail->trd_end_time)) {
                $compareAndSetRes = $this->getTravelStartAndEndDateTime($minDateTime, $maxDateTime, $detail->trd_end_date . ' ' . $detail->trd_end_time);
                $minDateTime = $compareAndSetRes['minDateTime'];
                $maxDateTime = $compareAndSetRes['maxDateTime'];
            }

            // Check if trd_segments has a value
            if (!empty($detail->trd_segments)) {
                // Decode the segments JSON
                $segments = json_decode($detail->trd_segments, true);
                foreach ($segments as $segment) {
                    // Check if segment has date and time values
                    if (!empty($segment['date']) && !empty($segment['time'])) {
                        $time = date("H:i:s", strtotime($segment['time'])); // Convert to 24-hour format
                        $segmentDateTime = $segment['date'] . ' ' . $time;
                        $compareAndSetRes = $this->getTravelStartAndEndDateTime($minDateTime, $maxDateTime, $segmentDateTime);
                        $minDateTime = $compareAndSetRes['minDateTime'];
                        $maxDateTime = $compareAndSetRes['maxDateTime'];
                    }
                }
            }
        }

        // Process travel expenses for date/time
        foreach ($travelExpenses as $expense) {
            $totalDistance += $expense->te_total_km_driven;
            if (!empty($expense->te_from_date) && !empty($expense->te_from_time)) {
                $compareAndSetRes = $this->getTravelStartAndEndDateTime($minDateTime, $maxDateTime, $expense->te_from_date . ' ' . $expense->te_from_time);
                $minDateTime = $compareAndSetRes['minDateTime'];
                $maxDateTime = $compareAndSetRes['maxDateTime'];
            }
            if (!empty($expense->te_to_date) && !empty($expense->te_to_time)) {
                $compareAndSetRes = $this->getTravelStartAndEndDateTime($minDateTime, $maxDateTime, $expense->te_to_date . ' ' . $expense->te_to_time);
                $minDateTime = $compareAndSetRes['minDateTime'];
                $maxDateTime = $compareAndSetRes['maxDateTime'];
            }
        }

        return ['minDateTime' => $minDateTime, 'maxDateTime' => $maxDateTime, 'totalDistance' => $totalDistance];
    }

    public function calculateNightStayLodging($detail, $lodgingEligibilityPolicy)
    {
        $eligibilityPerDayAll = $this->getPerDayLodgingEligibility($detail, $lodgingEligibilityPolicy);
        $calculationMessage = $eligibilityPerDayAll['calculationMessage'];
        $eligibilityPerDay = $eligibilityPerDayAll['eligibility'];


        $checkInTime = Carbon::createFromFormat('d M, Y g:i A', $detail['fromDate'] . ' ' . $detail['fromTime']);
        $checkOutTime = Carbon::createFromFormat('d M, Y g:i A', $detail['toDate'] . ' ' . $detail['toTime']);

        // Use dynamic checkout time if provided in $detail, otherwise default to 11:30 AM
        $standardCheckoutTime = isset($detail['checkoutTime']) ? $detail['checkoutTime'] : '11:30 AM';
        $checkoutLimit = Carbon::createFromFormat('H:i A', $standardCheckoutTime);

        $totalLodging = 0;
        $totalDays = 0;

        // Check if check-in and check-out are on the same day
        if ($checkInTime->isSameDay($checkOutTime)) {
            // Both check-in and check-out are before the standard checkout time
            if ($checkOutTime->format('H:i') <= $checkoutLimit->format('H:i')) {
                $totalLodging = $eligibilityPerDay;
                $totalDays = 1;
                $calculationMessage .= "Stay from " . $checkInTime->format('d M Y g:i A') . " to " . $checkOutTime->format('d M Y g:i A') . " counted as one full day (standard checkout: $standardCheckoutTime). Lodging: $eligibilityPerDay. ";
            } else {
                // Check-out is after the standard checkout time
                $totalLodging = $eligibilityPerDay;
                $totalDays = 1;
                $calculationMessage .= "Stay from " . $checkInTime->format('d M Y g:i A') . " to " . $checkOutTime->format('d M Y g:i A') . " counted as one full day (extends past standard checkout: $standardCheckoutTime). Lodging: $eligibilityPerDay. ";
            }
        } else {
            // Original logic for multi-day stays
            while ($checkInTime->lessThanOrEqualTo($checkOutTime)) {

                if ($checkInTime->isSameDay($checkOutTime)) {
                    if ($checkOutTime->format('H:i') > $checkoutLimit->format('H:i')) {
                        $totalLodging += $eligibilityPerDay;
                        $totalDays++;
                        $calculationMessage .= "Stay from " . $checkInTime->format('d M Y g:i A') . " to " . $checkOutTime->format('d M Y g:i A') . " counted as full day (standard checkout: $standardCheckoutTime). Lodging: $eligibilityPerDay. ";
                    } else {
                        $calculationMessage .= "Stay from " . $checkInTime->format('d M Y g:i A') . " to " . $checkOutTime->format('d M Y g:i A') . " ends before standard checkout (standard checkout: $standardCheckoutTime). No lodging considered. ";
                    }
                    break;
                } else {
                    if ($checkInTime->format('H:i') < $checkoutLimit->format('H:i')) {
                        $totalLodging += $eligibilityPerDay;
                        $totalDays++;
                        $calculationMessage .= "Stay from " . $checkInTime->format('d M Y g:i A') . " to " . $checkInTime->copy()->setTime($checkoutLimit->hour, $checkoutLimit->minute)->format('d M Y g:i A') . " counted as full day (standard checkout: $standardCheckoutTime). Lodging: $eligibilityPerDay. ";
                    } else {
                        $totalLodging += $eligibilityPerDay;
                        $totalDays++;
                        $calculationMessage .= "Stay from " . $checkInTime->format('d M Y g:i A') . " to " . $checkInTime->copy()->addDay()->setTime($checkoutLimit->hour, $checkoutLimit->minute)->format('d M Y g:i A') . " counted as full day (standard checkout: $standardCheckoutTime). Lodging: $eligibilityPerDay. ";
                    }
                }

                $checkInTime->addDay()->startOfDay();
            }
        }

        return [
            'lodging' => [
                'eligibilityAmountPerDay' => $eligibilityPerDay,
                'totalDays' => $totalDays,
                'totalPayableLodgingAmount' => $totalLodging,
                'calculationMessage' => $calculationMessage
            ]
        ];
    }



    public function getLodgingAmountForDay($detail, $currentDay)
    {
        // Convert current day to a Carbon instance for comparison
        $currentDayCarbon = Carbon::createFromFormat('Y-m-d', $currentDay);
        $checkInDate = Carbon::createFromFormat('d M, Y', $detail['fromDate']);
        $checkOutDate = Carbon::createFromFormat('d M, Y', $detail['toDate']);

        // Check if the current day is within the check-in and check-out range
        if ($currentDayCarbon->between($checkInDate, $checkOutDate)) {
            return $detail['amount'];
        }

        return 0; // Return 0 if the current day is not within the range
    }


    public function getPerDayLodgingEligibility($detail, $lodgingEligibilityPolicy)
    {
        $calculationMessage = ''; // Initialize calculation message
        $user = Auth::user();
        $lodiginPerDayEligibility  = 0;
        $cities = TadaMetroCity::where('ctm_b_id', $user->emp_b_id)->pluck('ctm_ct_address');

        $isMetro = $cities->filter(function ($city) use ($detail) {
            return strpos($city, $detail['destination']) === 0;
        });

        if (count($isMetro)) {
            $lodiginPerDayEligibility = $this->getEligibilityWithOrWithoutDocuments($detail, $lodgingEligibilityPolicy['metro']);
            $calculationMessage .= 'Metro city detected. ';
        } else {
            $lodiginPerDayEligibility = $this->getEligibilityWithOrWithoutDocuments($detail, $lodgingEligibilityPolicy['non_metro']);
            $calculationMessage .= 'Non-metro city detected. ';
        }

        return [
            'eligibility' => $lodiginPerDayEligibility,
            'calculationMessage' => $calculationMessage
        ];
    }

    public function getEligibilityWithOrWithoutDocuments($detail, $eligibilityPolicy)
    {
        if (count($detail['document'])) {
            if ($detail['occupancy'] == 'single') {
                return $eligibilityPolicy['lodging_eligibility_with_bill']['single_occupancy'];
            } else {
                return $eligibilityPolicy['lodging_eligibility_with_bill']['double_occupancy'];
            }
        } else {
            if ($detail['occupancy'] == 'single') {
                return $eligibilityPolicy['lodging_eligibility_without_bill']['single_occupancy'];
            } else {
                return $eligibilityPolicy['lodging_eligibility_without_bill']['double_occupancy'];
            }
        }
    }
}
