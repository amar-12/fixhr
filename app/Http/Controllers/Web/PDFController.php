<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PolicyTadaCategory;
use App\Models\TadaClaim;
use App\Models\TadaMetroCity;
use App\Models\TadaRequestPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use NumberToWords\NumberToWords;
class PDFController extends Controller
{
    public function generatePDF()
    {
        // START admin.tada-reports.travelling-expenses-statement
        $claimId = 3;
        $user = Auth::user();
        if (!$user) {
            abort('403', 'User not authenticated');
            return response()->json(['result' => [], 'status' => false, 'message' => 'User not authenticated.']);
        }

        $claimData = TadaClaim::where('tc_b_id', $user->emp_b_id)
            ->where('tc_id', $claimId)
            ->first();

        if (!$claimData) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Claim data not found.']);
        }

        $workingHour = 8;
        $policyCategory = PolicyTadaCategory::where([
            'ptc_b_id' => optional($claimData->fh_employee)->emp_b_id,
            'ptc_d_id' => optional($claimData->fh_employee)->emp_d_id,
            'ptc_grade_id' => optional($claimData->fh_employee)->emp_grade_id,
        ])->whereJsonContains('ptc_dg_id', optional($claimData->fh_employee)->emp_dg_id)
        ->first();

        if (!$policyCategory) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! policy not found, contact administration.']);
        }

        $cities = TadaMetroCity::where('ctm_b_id', optional($user)->emp_b_id)->pluck('ctm_ct_address');
        $isMetro = $cities->filter(function ($city) use ($claimData) {
            return strpos($city, optional($claimData->fh_tada_request_plan)->trp_destination) === 0;
        });

        if (!empty($isMetro)) {
            $daEligibility = optional($policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 23))->pluck('ptdal_da_per_day_elig')->first() ?? 0; // 23 == 'Metro'
            $lodgingWithBill = optional($policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 23))->pluck('ptdal_lodg_sngl_w_bill_elig')->first() ?? 0; // 23 == 'Metro'
            $lodgingWithOutBill = optional($policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 23))->pluck('ptdal_lodg_sngl_wo_bill_elig')->first() ?? 0; // 23 == 'Metro'
        } else {
            $daEligibility = optional($policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 24))->pluck('ptdal_da_per_day_elig')->first() ?? 0; // 24 == 'Non Metro'
            $lodgingWithBill = optional($policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 24))->pluck('ptdal_lodg_sngl_w_bill_elig')->first() ?? 0; // 24 == 'Non Metro'
            $lodgingWithOutBill = optional($policyCategory->fh_policy_tada_daily_allowance_lodgings->where('ptdal_ct_type_id', 24))->pluck('ptdal_lodg_sngl_wo_bill_elig')->first() ?? 0; // 24 == 'Non Metro'
        }

        $planData = TadaRequestPlan::with(['fh_employee' => ['fh_department:d_id,d_name']])
            ->where('trp_id', 4)
            ->first();

        if (!$planData) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Plan data not found.']);
        }

        $expenseData = $planData->fh_tada_expenses->groupBy('fh_expense_type.m_name');

        $numberToWords = new NumberToWords();

        // Get the converter for English
        $numberTransformer = $numberToWords->getNumberTransformer('en');

        // Convert the number to words
        $words = $numberTransformer->toWords(optional($claimData)->tc_amount);
        $capitalizedWords = ucfirst($words);

        $data = [
            'title' => 'Welcome to Laravel PDF Generation',
            'claimData' => $claimData,
            'capitalizedWords' => $capitalizedWords,
            'daEligibility' => $daEligibility,
            'expenseData' => $expenseData,
            'logoPath' => optional($user->fh_business)->b_logo,
        ];

        $pdf = Pdf::loadView('admin.tada-reports.document', $data);

        return $pdf->stream('document.pdf');
    }
}
