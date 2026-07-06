<?php

namespace App\Http\Controllers\Web\Admin;

use App\Exports\AdvancePaymentSheet;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PolicyTadaTravelType;
use App\Models\TadaRequestPlan;
use App\Models\MasterTable;
use App\Models\TadaClaim;
use App\Exports\ExpenseReportExport;
use App\Exports\ClaimReportExport;
use App\Exports\ExpenseBookingSheet;
use App\Exports\ExpenseSheet;
use App\Exports\TADAPaymentSheet;
use App\Models\TadaReimburse;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\TravelPurpose;
use App\Models\Business;
use App\Models\Country;
use App\Models\Designation;
use App\Models\Branch;
// use App\Exports\ExpenseReportExport;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $travelType = PolicyTadaTravelType::with('fh_travel_type:m_id,m_name')->where('pttt_b_id', $user->emp_b_id)->where('pttt_status', '1')->select('pttt_id', 'pttt_type_id')->get();
        $statusFilter = MasterTable::where('m_group', 'APPROVAL_STATUS')->get();
        $reportFilter = MasterTable::where('m_group', 'TADA_REPORT_TYPE')->get();
        // $reportFilter = MasterTable::where('m_group', 'TADA_REPORT_TYPE')->where('m_id','<>',195)->get(); // Travel Detail Report Not Making
                

        // Get currencies from countries table
        $business_details = Business::where('b_id', $user->emp_b_id)->get();
        $currencies = $business_details->pluck('b_currency')->unique();
        $currencies = Country::whereIn('c_id', $currencies)->get();
        $purposeTypes = TravelPurpose::where('tp_b_id', $user->emp_b_id)->get();

        // Get data for new filters
        $designations = \App\Models\Designation::where('dg_b_id', $user->emp_b_id)->get();
        $employeeStatuses = MasterTable::where('m_group', 'STATUS')->get();
        // Fix: Use correct column name for business id in branches table
        $branches = Branch::where('br_b_id', $user->emp_b_id)->select('br_id','br_name')->get();
        //  dd($branches);
        $getSlugQuery = $request->query('report');        

        return view('admin.tada-reports.report-index', compact('travelType', 'statusFilter', 'reportFilter', 'currencies', 'purposeTypes', 'designations', 'employeeStatuses', 'branches', 'getSlugQuery'));
    }

    public function export(Request $request, $id = null)
    {
        $request->validate([
            'report_type' => 'required|integer', // Adjust validation rules as needed
        ]);

        $user = Auth::user();
        // dd($request->all());

        // 193 Claim Report
        // if(isset($request->report_type) && ($request->report_type == 193))
        // {
        //     $data = TadaClaim::where('tc_b_id', $user->emp_b_id);

        //     if (isset($request->travel_type)) {
        //         $data->whereHas('fh_tada_request_plan', function ($query) use ($request) {
        //             $query->where('trp_pttt_id', $request->travel_type);
        //         });
        //     }

        //     if(isset($request->status)) {
        //         $data->where('tc_status', $request->status);
        //     }

        //     if(isset($request->from_date) && isset($request->to_date)) {
        //         $data->whereDate('created_at', '>=', $request->from_date)
        //             ->whereDate('created_at', '<=', $request->to_date);
        //     }

        //     $data = $data->with('fh_tada_request_plan', 'fh_employee');

        //     if ($data->count() == 0) {
        //         return redirect()->back()->with('alert', 'No data found');
        //     }

        //     // dd('claim', $data);
        //     $claim_download = 'Claim_Export'.'_'.now()->format('Y-m-d').'.xlsx';
        //     return Excel::download(new ClaimReportExport($data->get(), $user), $claim_download);
        // }

        // // 194 Expense Report
        // if(isset($request->report_type) && ($request->report_type == 194))
        // {
        //     $data = TadaRequestPlan::where('trp_b_id', $user->emp_b_id);

        //     if(isset($request->travel_type)) {
        //         $data->where('trp_pttt_id', $request->travel_type);
        //     }

        //     if(isset($request->status)) {
        //         $data->where('trp_request_status', $request->status);
        //     }

        //     // if(isset($request->from_date) && isset($request->to_date)) {
        //     //     $data->whereDate('fh_tada_expenses.created_at', '>=', $request->from_date)
        //     //         ->whereDate('fh_tada_expenses.created_at', '<=', $request->to_date);
        //     // }
        //     $data = $data->with('fh_employee', 'fh_tada_claim', 'fh_tada_expenses');

        //     if (isset($request->from_date) || isset($request->to_date)) {
        //         $data = $data->filter(function ($item) use ($request) {
        //             return $item->fh_tada_expenses->contains(function ($expense) use ($request) {
        //                 // Check if both dates are provided
        //                 if (isset($request->from_date) && isset($request->to_date)) {
        //                     return $expense->created_at >= $request->from_date && $expense->created_at <= $request->to_date;
        //                 }
        //                 // Check if only from_date is provided
        //                 if (isset($request->from_date)) {
        //                     return $expense->created_at >= $request->from_date;
        //                 }
        //                 // Check if only to_date is provided
        //                 if (isset($request->to_date)) {
        //                     return $expense->created_at <= $request->to_date;
        //                 }
        //                 return true;
        //             });
        //         });
        //     }

        //     if ($data->count() == 0) {
        //         return redirect()->back()->with('alert', 'No data found');
        //     }

        //     // dd($request->all(), $data);
        //     $expense_download = 'Expense_Export'.'_'.now()->format('Y-m-d').'.xlsx';
        //     return Excel::download(new ExpenseReportExport($data->get(), $user), $expense_download);
        // }

        // TADA Payment Sheet
        if(isset($request->report_type) && ($request->report_type == 196))
        {
            $reimburseData = TadaReimburse::where('tr_b_id', $user->emp_b_id);

            if (isset($request->fromDate) || isset($request->toDate)) {
                if (isset($request->fromDate) && isset($request->toDate)) {
                    $reimburseData->whereDate('created_at', '>=', $request->fromDate)
                        ->whereDate('created_at', '<=', $request->toDate);
                } elseif (isset($request->fromDate)) {
                    $reimburseData->whereDate('created_at', '>=', $request->fromDate);
                } elseif (isset($request->toDate)) {
                    $reimburseData->whereDate('created_at', '<=', $request->toDate);
                }
            }

            $reimburseData = $reimburseData->pluck('tr_claims_id')->flatten();

            $claimData = TadaClaim::where('tc_b_id', $user->emp_b_id)->whereIn('tc_id', $reimburseData)
                ->where('tc_payed_amount', '>', 0)->pluck('tc_trp_id')->flatten();

            $planData = TadaRequestPlan::where('trp_b_id', $user->emp_b_id)->whereIn('trp_id', $claimData)
                ->with('fh_employee', 'fh_tada_claim');
            // dd($reimburseData, $claimData, $data);

            if(isset($request->status)) {
                $planData->where('trp_tada_payment_processed', $request->status);
                // $claimId = $planData->pluck();
            }

            if ($planData->count() == 0) {
                return redirect()->back()->with('alert', 'No data found');
            }

            // dd($request->all(), $planData->get());
            $payment_download = 'TADA_Payment_Sheet'.'_'.now()->format('Y-m-d').'.xlsx';
            return Excel::download(new TADAPaymentSheet($planData->get(), $user), $payment_download);
        }

        // ADV Sheet
        if(isset($request->report_type) && ($request->report_type == 197))
        {
            $data = TadaRequestPlan::where('trp_b_id', $user->emp_b_id)->where('trp_advance_allowance', '>', 0);
            // dd( $request->travel_type, $data->get());
            if(isset($request->plan_unique_id)) {
                $data->where('trp_id', $request->plan_unique_id);
            }

            if(isset($request->travel_type)) {
                $data->whereHas('fh_policy_tada_travel_type.fh_travel_type', function ($query) use ($request) {
                    $query->where('m_id', $request->travel_type);
                });
            }

            if(isset($request->status)) {
                $data->where('trp_adv_payment_processed', $request->status);
            }

            if (isset($request->employee_id)) {
                $data->where('trp_emp_id', $request->employee_id);
            }

            $data = $data->with('fh_employee')->get();

            if ($data->count() == 0) {
                // Option 1: Return a response with an alert (if using Ajax)
                // return response()->json(['message' => 'No data found..']);

                // Option 2: If you want to redirect and show an alert using session flash message
                return redirect()->back()->with('alert', 'No data found');
            }

            // dd($request->all(), $data);
            $advance_download = 'Advance_Payment_Sheet'.'_'.now()->format('Y-m-d').'.xlsx';
            return Excel::download(new AdvancePaymentSheet($data, $user), $advance_download);
        }

        // Expense Sheet
        if(isset($request->report_type) && ($request->report_type == 198))
        {
            $reimburseData = TadaReimburse::where('tr_b_id', $user->emp_b_id);

            if (isset($request->fromDate) || isset($request->toDate)) {
                if (isset($request->fromDate) && isset($request->toDate)) {
                    $reimburseData->whereDate('created_at', '>=', $request->fromDate)
                        ->whereDate('created_at', '<=', $request->toDate);
                } elseif (isset($request->fromDate)) {
                    $reimburseData->whereDate('created_at', '>=', $request->fromDate);
                } elseif (isset($request->toDate)) {
                    $reimburseData->whereDate('created_at', '<=', $request->toDate);
                }
            }
            $reimburseData = $reimburseData->pluck('tr_claims_id')->flatten();

            $claimData = TadaClaim::where('tc_b_id', $user->emp_b_id)->whereIn('tc_id', $reimburseData)->pluck('tc_trp_id')->flatten();

            $planData = TadaRequestPlan::where('trp_b_id', $user->emp_b_id)->whereIn('trp_id', $claimData)
                ->with('fh_employee', 'fh_tada_claim');

            if(isset($request->status)) {
                $planData->where('trp_exp_payment_processed', $request->status);
            }

            if ($planData->count() == 0) {
                return redirect()->back()->with('alert', 'No data found');
            }

            $expense_download = 'Expense_Booking_Sheet_Export'.'_'.now()->format('Y-m-d').'.xlsx';
            return Excel::download(new ExpenseBookingSheet($planData->get(), $user), $expense_download);
        }
    }

    public function expenseStatement(Request $request) {
        $user = Auth::user();
        $travelData = TadaRequestPlan::with(
            'fh_employee:emp_id,emp_full_name,emp_fname,emp_mname,emp_lname,emp_dg_id,emp_d_id,emp_phone',
            'fh_employee.fh_department:d_id,d_name',
            'fh_employee.fh_designation:dg_id,dg_name',
        )->first();
        // dd($travelData);
        return view('admin.tada-reports.travelling-expenses-statement', compact('user', 'travelData'));
    }

    public function paymentApplication(Request $request) {
        $user = Auth::user();
        return view('admin.tada-reports.application-for-payment', compact('user'));
    }

    public function expenseDetails(Request $request) {
        return view('admin.tada-reports.expense-details');
    }
}
