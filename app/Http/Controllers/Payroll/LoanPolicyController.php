<?php

namespace App\Http\Controllers\Payroll;

use App\Models\Business;
use App\Models\LoanSetting;
use App\Models\MasterTable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\SalaryAllowance;
use App\Models\AdvanceLoanSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\AdvanceLoanInterestRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;

class LoanPolicyController extends Controller
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

        $business = Business::with('fh_currency')->where('b_id', $businessId)->first();

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args'   => ['als_b_id', $businessId]
                ],
                [
                    'method' => 'select',
                    'args'   => [
                        'als_id',
                        'als_b_id',
                        'als_loan_advance_name',
                        'als_limit_type',
                        'als_fixed_limit',
                        'als_percentage_limit',
                        'als_permanent_only',
                        'als_min_employment',
                        'als_enable_age_criteria',
                        'als_max_age',
                        'als_apply_interest',
                        'als_interest_exceed_installments',
                        'als_interest_exceed_rate',
                        'als_interest_if_loan_multiplier',
                        'als_interest_if_loan_months',
                        'als_interest_if_loan_rate',
                        'als_interest_if_loan_greater_multiplier',
                        'als_interest_if_loan_greater_months',
                        'als_interest_if_loan_greater_rate',
                        'als_max_concurrent',
                        'als_min_repayment',
                        'als_max_repayment',
                        'als_status',
                        'updated_at',
                        'created_at'
                    ],
                    'relation' => []
                ],
                [
                    'method' => 'orderBy',
                    'args'   => ['created_at', 'desc'],
                    'relation' => []
                ]
            ];

            $searchColumns = [
                'als_id',
                'als_loan_advance_name',
                'als_limit_type',
                'als_fixed_limit',
                'als_percentage_limit',
                'als_apply_interest',
                'als_interest_exceed_rate',
                'als_max_concurrent',
                'als_status',
            ];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new AdvanceLoanSetting(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns
            ))->getServerSideDataTable();

            $rowData = [];
            $i = $request->input('start', 0) + 1;

            foreach ($list as $val) {
                $row = [];
                $row[] = $i++;
                $row[] = $val->als_loan_advance_name ?? '-';
                $row[] = '
                <span class="fs-11 fw-bold">W.E.F. </span>
                <span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';
                $row[] = '
                <div class="btn-list ms-3">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu p-2" style="min-width: 200px;">
                            <li>
                                <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-loan"
                                    type="button"
                                    data-als-id="' . $val->als_id . '"
                                    data-name="' . e($val->als_loan_advance_name) . '">
                                    <i class="feather feather-edit"></i> Edit
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-loan"
                                    type="button"
                                    data-als-id="' . $val->als_id . '"
                                    data-name="' . e($val->als_loan_advance_name) . '">
                                    <i class="feather feather-trash"></i> Delete
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>';

                $rowData[] = $row;
            }

            return response()->json([
                'draw'            => intval($request->input('draw')),
                'recordsTotal'    => sizeof($list),
                'recordsFiltered' => (new DynamicModelDataTableHelper(
                    eloquentModel: new AdvanceLoanSetting(),
                    dynamicConditions: $dynamicConditions
                ))->countFilteredServerSideDataTable(),
                'data'            => $rowData,
            ]);
        }

        $columns = [
            'S. No.',
            'Loan/Advance Name',
            'W.E.F.',
            'Action',
        ];

        return view('admin.setting.payroll.loan-setting', compact('business', 'columns'));
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $businessId = $user->emp_b_id;

            // Validation rules
            $validator = Validator::make($request->all(), [
                'loan_advance_name' => 'required|string|max:255',
                'limit_type' => 'required|in:fixed,percentage',
                'fixed_limit' => 'required_if:limit_type,fixed|nullable|numeric|min:1',
                'percentage_limit' => 'required_if:limit_type,percentage|nullable|numeric|min:1|max:100',
                'min_employment' => 'nullable|integer|min:0',
                'max_age' => 'nullable|integer|min:18|max:100',
                'max_concurrent' => 'nullable|integer|min:1',
                'min_repayment' => 'nullable|integer|min:1',
                'max_repayment' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $alsId = $request->als_id;

            // Prepare data for insert/update
            $data = [
                'als_b_id' => $businessId,
                'als_loan_advance_name' => $request->loan_advance_name,
                'als_limit_type' => strtoupper($request->limit_type), // Ensure uppercase for enum
                'als_fixed_limit' => $request->limit_type === 'fixed' ? $request->fixed_limit : null,
                'als_percentage_limit' => $request->limit_type === 'percentage' ? $request->percentage_limit : 50,
                'als_permanent_only' => $request->permanent_only == '1' ? 1 : 0,
                'als_min_employment' => $request->min_employment ?? 6,
                'als_enable_age_criteria' => $request->enable_age_criteria == '1' ? 1 : 0,
                'als_max_age' => $request->max_age ?? 60,
                'als_apply_interest' => $request->apply_interest == '1' ? 1 : 0,
                'als_max_concurrent' => $request->max_concurrent ?? 1,
                'als_min_repayment' => $request->min_repayment ?? 1,
                'als_max_repayment' => $request->max_repayment ?? 24,
                'als_status' => $request->status == '1' ? 1 : 0,
                'updated_at' => now()
            ];

            // Check if updating or creating
            if ($alsId) {
                // Update existing record
                $advanceLoan = AdvanceLoanSetting::where('als_id', $alsId)
                    ->where('als_b_id', $businessId)
                    ->first();

                if (!$advanceLoan) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Configuration not found'
                    ], 404);
                }

                $advanceLoan->update($data);
                $message = 'Loan configuration updated successfully';
            } else {
                // Create new record
                $data['created_at'] = now();
                $advanceLoan = AdvanceLoanSetting::create($data);
                $alsId = $advanceLoan->als_id;
                $message = 'Loan configuration created successfully';
            }

            // ✅ Handle interest rules in separate table
            if ($request->apply_interest == '1' && $request->has('rule_type')) {
                // Delete existing rules for this configuration
                AdvanceLoanInterestRule::where('alir_als_id', $alsId)
                    ->where('alir_b_id', $businessId)
                    ->delete();

                // Insert new rules
                $ruleTypes = $request->rule_type ?? [];
                $ruleParam1s = $request->rule_param1 ?? [];
                $ruleParam2s = $request->rule_param2 ?? [];
                $ruleRates = $request->rule_rate ?? [];

                foreach ($ruleTypes as $index => $ruleType) {
                    if (!empty($ruleType)) {
                        AdvanceLoanInterestRule::create([
                            'alir_b_id' => $businessId,
                            'alir_als_id' => $alsId,
                            'alir_rule_type' => $ruleType,
                            'alir_param1' => $ruleParam1s[$index] ?? null,
                            'alir_param2' => $ruleParam2s[$index] ?? null,
                            'alir_rule_rate' => $ruleRates[$index] ?? 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            } else {
                // If interest is disabled, remove all rules
                AdvanceLoanInterestRule::where('alir_als_id', $alsId)
                    ->where('alir_b_id', $businessId)
                    ->delete();
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        return $this->edit($id);
    }

    public function edit($id)
    {
        try {
            $user = Auth::user();
            $businessId = $user->emp_b_id;

            $advanceLoan = AdvanceLoanSetting::where('als_id', $id)
                ->where('als_b_id', $businessId)
                ->first();

            if (!$advanceLoan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration not found'
                ], 404);
            }

            // ✅ Fetch interest rules from the separate table
            $interestRules = AdvanceLoanInterestRule::where('alir_als_id', $id)
                ->where('alir_b_id', $businessId)
                ->get()
                ->map(function ($rule) {
                    return [
                        'type' => $rule->alir_rule_type,
                        'param1' => $rule->alir_param1,
                        'param2' => $rule->alir_param2,
                        'rate' => $rule->alir_rule_rate
                    ];
                })
                ->toArray();

            // Format the data for frontend
            $data = [
                'als_id' => $advanceLoan->als_id,
                'als_loan_advance_name' => $advanceLoan->als_loan_advance_name,
                'als_limit_type' => $advanceLoan->als_limit_type,
                'als_fixed_limit' => $advanceLoan->als_fixed_limit,
                'als_percentage_limit' => $advanceLoan->als_percentage_limit,
                'als_permanent_only' => $advanceLoan->als_permanent_only,
                'als_min_employment' => $advanceLoan->als_min_employment,
                'als_enable_age_criteria' => $advanceLoan->als_enable_age_criteria,
                'als_max_age' => $advanceLoan->als_max_age,
                'als_apply_interest' => $advanceLoan->als_apply_interest,
                'als_max_concurrent' => $advanceLoan->als_max_concurrent,
                'als_min_repayment' => $advanceLoan->als_min_repayment,
                'als_max_repayment' => $advanceLoan->als_max_repayment,
                'als_status' => $advanceLoan->als_status,

                // ✅ IMPORTANT: Include interest rules from separate table
                'interest_rules' => $interestRules,

                // Alternative field names for backward compatibility
                'loan_name' => $advanceLoan->als_loan_advance_name,
                'limit_type' => $advanceLoan->als_limit_type,
                'fixed_amount' => $advanceLoan->als_fixed_limit,
                'percentage' => $advanceLoan->als_percentage_limit,
                'permanent_only' => $advanceLoan->als_permanent_only,
                'min_employment' => $advanceLoan->als_min_employment,
                'enable_age' => $advanceLoan->als_enable_age_criteria,
                'max_age' => $advanceLoan->als_max_age,
                'enable_interest' => $advanceLoan->als_apply_interest,
                'max_concurrent' => $advanceLoan->als_max_concurrent,
                'min_repayment' => $advanceLoan->als_min_repayment,
                'max_repayment' => $advanceLoan->als_max_repayment,
                'status' => $advanceLoan->als_status
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }



    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $businessId = $user->emp_b_id;
            // Validation
            $validator = Validator::make($request->all(), [
                'loan_advance_name' => 'required|string|max:255',
                'limit_type'        => 'required|in:fixed,percentage',
                'fixed_limit'       => 'required_if:limit_type,fixed|nullable|numeric|min:1',
                'percentage_limit'  => 'required_if:limit_type,percentage|nullable|numeric|min:1|max:100',
                'min_employment'    => 'nullable|integer|min:0',
                'max_age'           => 'nullable|integer|min:18|max:100',
                'max_concurrent'    => 'nullable|integer|min:1',
                'min_repayment'     => 'nullable|integer|min:1',
                'max_repayment'     => 'nullable|integer|min:1',
                'rule_type.*'       => 'nullable|string|max:100',
                'rule_param1.*'     => 'nullable|string|max:255',
                'rule_param2.*'     => 'nullable|string|max:255',
                'rule_rate.*'       => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors()
                ], 422);
            }

            // Prepare parent table data
            $data = [
                'als_loan_advance_name'   => $request->loan_advance_name,
                'als_limit_type'          => $request->limit_type,
                'als_fixed_limit'         => $request->limit_type === 'fixed' ? $request->fixed_limit : null,
                'als_percentage_limit'    => $request->limit_type === 'percentage' ? $request->percentage_limit : null,
                'als_permanent_only'      => $request->permanent_only == '1' ? 1 : 0,
                'als_min_employment'      => $request->min_employment ?? 6,
                'als_enable_age_criteria' => $request->enable_age_criteria == '1' ? 1 : 0,
                'als_max_age'             => $request->max_age ?? 60,
                'als_apply_interest'      => $request->apply_interest == '1' ? 1 : 0,
                'als_max_concurrent'      => $request->max_concurrent ?? 1,
                'als_min_repayment'       => $request->min_repayment ?? 1,
                'als_max_repayment'       =>$request->max_repayment,
                'als_status'              => $request->status == '1' ? 1 : 0,
                'updated_at'              => now(),
            ];

            // ✅ Important: assign updateOrCreate to a variable
            $advanceLoan = AdvanceLoanSetting::updateOrCreate(
                ['als_id' => $id, 'als_b_id' => $businessId],
                $data
            );

            // ✅ Update child table
            if ($request->has('rule_type')) {
                // delete old rules
                AdvanceLoanInterestRule::where('alir_als_id', $advanceLoan->als_id)
                    ->where('alir_b_id', $businessId)
                    ->delete();

                foreach ($request->rule_type as $key => $ruleType) {
                    if ($ruleType) {
                        AdvanceLoanInterestRule::create([
                            'alir_b_id'       => $businessId,
                            'alir_als_id'     => $advanceLoan->als_id, // ✅ always use fresh $advanceLoan
                            'alir_rule_type'  => $ruleType,
                            'alir_param1'     => $request->rule_param1[$key] ?? null,
                            'alir_param2'     => $request->rule_param2[$key] ?? null,
                            'alir_rule_rate'  => $request->rule_rate[$key] ?? 0,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Loan configuration updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }




    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $businessId = $user->emp_b_id;

            $advanceLoan = AdvanceLoanSetting::where('als_id', $id)
                ->where('als_b_id', $businessId)
                ->first();

            if (!$advanceLoan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration not found'
                ], 404);
            }

            $advanceLoan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Configuration deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }
}
