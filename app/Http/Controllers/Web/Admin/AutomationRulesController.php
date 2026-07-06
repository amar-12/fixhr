<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutomationRuleRequest;
use App\Models\AutomationRule;
use App\Models\EarlyGoingAutomation;
use App\Models\LateComingAutomation;
use App\Models\MasterTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PenaltyLog;
use Illuminate\Support\Facades\DB;

class AutomationRulesController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index()
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $rules = AutomationRule::where('ar_b_id', $businessId)->get()->keyBy('ar_rule_type')->toArray();
        $penaltyRule = PenaltyLog::where('pl_b_id', $user->emp_b_id)->where('pl_ar_id', 414)->first();
        
        // Late Coming Automation Rules - collect only non-empty values where both columns exist
        $lateRules = LateComingAutomation::where('lca_b_id', $businessId)->get();
        $lateToggle = $lateRules->first() ? $lateRules->first()->lca_is_penalty_enabled : 0;
        $latePenaltyRule = $lateRules->map(function ($rule) {
            // Check if both columns have values
            if (!empty($rule->lca_penalty_amount) && !empty($rule->lca_late_till)) {
                return [
                    'lca_penalty_amount' => $rule->lca_penalty_amount,
                    'lca_late_till' => $rule->lca_late_till,
                ];
            }
            return null;
        })->filter(); // Remove null values
        
        $lateSalaryRule = $lateRules->map(function ($rule) {
            // Check if both columns have values
            if (!empty($rule->lca_no_late) && !empty($rule->lca_days_to_deduct)) {
                return [
                    'lca_no_late' => $rule->lca_no_late,
                    'lca_days_to_deduct' => $rule->lca_days_to_deduct,
                ];
            }
            return null;
        })->filter(); // Remove null values
        
        // Early Going Automation Rules - collect only non-empty values where both columns exist
        $earlyRules = EarlyGoingAutomation::where('ega_b_id', $businessId)->get();
        $earlyToggle = $earlyRules->first() ? $earlyRules->first()->ega_is_penalty_enabled : 0;
        $earlyPenaltyRule = $earlyRules->map(function ($rule) {
            // Check if both columns have values
            if (!empty($rule->ega_penalty_amount) && !empty($rule->ega_exit_before)) {
                return [
                    'ega_penalty_amount' => $rule->ega_penalty_amount,
                    'ega_exit_before' => $rule->ega_exit_before,
                ];
            }
            return null;
        })->filter(); // Remove null values
        
        $earlySalaryRule = $earlyRules->map(function ($rule) {
            // Check if both columns have values
            if (!empty($rule->ega_no_early) && !empty($rule->ega_days_to_deduct)) {
                return [
                    'ega_no_early' => $rule->ega_no_early,
                    'ega_days_to_deduct' => $rule->ega_days_to_deduct,
                ];
            }
            return null;
        })->filter(); // Remove null values
        
        return view('admin.setting.attendance.automation-rules', compact('businessId', 'rules', 'penaltyRule', 'latePenaltyRule', 'lateSalaryRule', 'earlyPenaltyRule', 'earlySalaryRule', 'lateToggle', 'earlyToggle'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $response = ['status' => false, 'message' => ''];
        try {
            $rules = $request->input('rules', []); // Default to empty array if null
            if (!is_array($rules)) {
                return response()->json(['error' => 'Invalid data format'], 400);
            }

            foreach ($rules as $ruleData) {

                AutomationRule::updateOrCreate(
                    [
                        'ar_rule_type' => $ruleData['rule_type_id'],
                        'ar_b_id' => $this->user->emp_b_id,
                    ],
                    [
                        'ar_is_enabled' => $ruleData['is_enabled'] ?? 0,
                        'ar_occurrences' => $ruleData['max_occurrences'] ?? null,
                        'ar_apply_before_day' => $ruleData['apply_before_day'] ?? null,
                        'ar_both_time_count' => $ruleData['both_time_count'] ?? null,
                        'ar_apply_gatepass_checkout' => $ruleData['apply_gatepass_checkout'] ?? 0,
                    ]
                );
            }

            $response['status'] = true;
            $response['message'] = 'Automation rules saved successfully!';
        } catch (\Exception $e) {
            $response['status'] = false;
            $response['message'] = $e->getMessage();
        }
        return response()->json($response);
    }

    public function lateEarlyRuleSave(Request $request)
    {
        $response = ['status' => false, 'message' => ''];
        try {
            $rule_type = $request->input('rule_type');

            $late_coming_toggle = $request->input('late_coming_toggle') ? 0 : 1;
            $noLateArray = $request->input('no_late', []);
            $lcDaysToDeductArray = $request->input('lc_days_to_deduct', []);
            $lateTillArray = $request->input('late_till', []);
            $lcPenaltyAmountArray = $request->input('lc_penalty_amount', []);
            $lateArrCount = max(count($noLateArray), count($lcDaysToDeductArray), count($lateTillArray), count($lcPenaltyAmountArray));

            $early_going_toggle = $request->input('early_going_toggle') ? 0 : 1;
            $exitBeforeArray = $request->input('exit_before', []);
            $egPenaltyArray = $request->input('eg_penalty_amount', []);
            $noEarlyArray = $request->input('no_early', []);
            $egaDaysToDeductArray = $request->input('ega_days_to_deduct', []);
            $earlyArrCount = max(count($exitBeforeArray), count($egPenaltyArray), count($noEarlyArray), count($egaDaysToDeductArray));

            if (count($noLateArray) !== count($lcDaysToDeductArray) || count($lateTillArray) !== count($lcPenaltyAmountArray) || count($noEarlyArray) !== count($egaDaysToDeductArray) || count($exitBeforeArray) !== count($egPenaltyArray)) {
                throw new \Exception('Some deduction rules are missing corresponding values.');
            }

            DB::beginTransaction();
            if ($rule_type == 415) {
                // Delete existing rules
                EarlyGoingAutomation::where('ega_b_id', $this->user->emp_b_id)->delete();

                // Insert new rules
                for ($i = 0; $i < $earlyArrCount; $i++) {
                    // if ((empty($exitBeforeArray[$i]) && empty($egPenaltyArray[$i])) || (empty($noEarlyArray[$i]) && empty($egaDaysToDeductArray[$i]))) {
                    //     continue; // Skip empty rules
                    // }
                    $rule = EarlyGoingAutomation::create([
                        'ega_b_id' => $this->user->emp_b_id,
                        'ega_is_penalty_enabled' => $early_going_toggle,
                        'ega_exit_before' => $exitBeforeArray[$i] ?? null,
                        'ega_penalty_amount' => $egPenaltyArray[$i] ?? null,
                        'ega_no_early' => $noEarlyArray[$i] ?? null,
                        'ega_days_to_deduct' => $egaDaysToDeductArray[$i] ?? null,
                    ]);
                    if (!$rule) {
                        throw new \Exception('Failed to save early going automation rule at index ' . $i);
                    }
                }
            } else if ($rule_type == 414) {
                // Delete existing rules
                LateComingAutomation::where('lca_b_id', $this->user->emp_b_id)->delete();

                // Insert new rules
                for ($i = 0; $i < $lateArrCount; $i++) {
                    $rule = LateComingAutomation::create([
                        'lca_b_id' => $this->user->emp_b_id,
                        'lca_is_penalty_enabled' => $late_coming_toggle,
                        'lca_late_till' => $lateTillArray[$i] ?? null,
                        'lca_penalty_amount' => $lcPenaltyAmountArray[$i] ?? null,
                        'lca_no_late' => $noLateArray[$i] ?? null,
                        'lca_days_to_deduct' => $lcDaysToDeductArray[$i] ?? null,
                    ]);
                    if (!$rule) {
                        throw new \Exception('Failed to save late coming automation rule at index ' . $i);
                    }
                }
            }
            DB::commit();
            $response['status'] = true;
            $response['message'] = $rule_type == 415 ? 'Early going automation rules saved successfully!' : 'Late coming automation rules saved successfully!';
        } catch (\Exception $e) {
            DB::rollBack();
            $response['status'] = false;
            $response['message'] = $e->getMessage();
        }
        return response()->json($response);
    }
}
