<?php

namespace App\Http\Controllers\Payroll;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PayslipConfiguration;
use Illuminate\Support\Facades\Auth;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;

class PayslipConfigController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index(Request $request)
    {
        if ($this->user) {
            if ($request->ajax()) {
                $dynamicConditions = [
                    [
                        'method' => 'select',
                        'args' => [
                            'pc_id',
                            'pc_b_id',
                            'pc_show_employee_code',
                            'pc_show_employee_name',
                            'pc_show_designation',
                            'pc_show_department',
                            'pc_show_branch',
                            'pc_show_doj',
                            'pc_show_ip_uan',
                            'pc_show_bank_details',
                            'pc_show_month',
                            'pc_show_earnings_breakdown',
                            'pc_show_employee_deductions_breakdown',
                            'pc_show_employer_deductions_breakdown',
                            'pc_show_net_pay',
                            'pc_show_total_ctc',
                            'pc_round_off_net_salary',
                            'pc_show_net_salary_in_words',
                            'pc_show_working_days',
                            'pc_show_month_days',
                            'pc_show_days_present',
                            'pc_show_salary_days',
                            'pc_show_lwp_days',
                            'pc_show_leaves_taken',
                            'pc_show_signature',
                            'pc_show_disclaimer',
                            'created_at'
                        ],
                        'relation' => []
                    ],
                    [
                        'method' => 'where',
                        'args' => ['pc_b_id', $this->user->emp_b_id],
                        'relation' => []
                    ],
                    [
                        'method' => 'orderBy',
                        'args' => ['created_at', 'desc'],
                        'relation' => []
                    ]
                ];

                $searchColumns = [
                    'pc_b_id',
                    'pc_show_employee_name',
                    'pc_show_designation',
                    'pc_show_net_pay',
                    'pc_show_total_ctc',
                    'pc_show_branch',
                    'pc_show_department',
                ];

                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new PayslipConfiguration(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                ))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $val) {
                    $i++;
                    $row = [];
                    $row[] = $i;
                    $row[] = $val->fh_business->b_name;

                    $row[] = '
                    <div class="btn-list ms-3">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                <li>
                                    <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-payslip-config"
                                        type="button"
                                        data-id="' . $val->pc_id . '"
                                        data-b_id="' . $val->pc_b_id . '"
                                        data-employee_code="' . $val->pc_show_employee_code . '"
                                        data-employee_name="' . $val->pc_show_employee_name . '"
                                        data-designation="' . $val->pc_show_designation . '"
                                        data-department="' . $val->pc_show_department . '"
                                        data-branch="' . $val->pc_show_branch . '"
                                        data-ip_uan="' . $val->pc_show_ip_uan . '"
                                        data-doj="' . $val->pc_show_doj . '"
                                        data-bank_details="' . $val->pc_show_bank_details . '"
                                        data-month="' . $val->pc_show_month . '"
                                        data-earnings_breakdown="' . $val->pc_show_earnings_breakdown . '"
                                        data-employee_deductions_breakdown="' . $val->pc_show_employee_deductions_breakdown . '"
                                        data-employer_deductions_breakdown="' . $val->pc_show_employer_deductions_breakdown . '"
                                        data-net_pay="' . $val->pc_show_net_pay . '"
                                        data-total_ctc="' . $val->pc_show_total_ctc . '"
                                        data-round_off_net_salary="' . $val->pc_round_off_net_salary . '"
                                        data-net_salary_in_words="' . $val->pc_show_net_salary_in_words . '"
                                        data-working_days="' . $val->pc_show_working_days . '"
                                        data-month_days="' . $val->pc_show_month_days . '"
                                        data-days_present="' . $val->pc_show_days_present . '"
                                        data-salary_days="' . $val->pc_show_salary_days . '"
                                        data-lwp_days="' . $val->pc_show_lwp_days . '"
                                        data-leaves_taken="' . $val->pc_show_leaves_taken . '"
                                        data-signature="' . $val->pc_show_signature . '"
                                        data-disclaimer="' . $val->pc_show_disclaimer . '"
                                    >
                                        <i class="feather feather-edit"></i> Edit
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
                        eloquentModel: new PayslipConfiguration(),
                        dynamicConditions: $dynamicConditions,
                    ))->countFilteredServerSideDataTable(),
                    "data" => $rowData,
                ];

                return response()->json($output);
            }

            $columns = [
                'S. No.',
                'Business Unit',
                'Action',
            ];

            $payslipConfigurations = PayslipConfiguration::select(
                'pc_id',
                'pc_b_id',
                'pc_show_employee_name',
                'pc_show_designation',
                'pc_show_net_pay',
                'pc_show_total_ctc'
            )
                ->orderBy('created_at', 'desc')
                ->get();

            return view('admin.payroll.PayslipConfig', compact('payslipConfigurations', 'columns'));
        } else {
            abort(404);
        }
    }


    public function storeOrUpdate(Request $request)
    {
        $user = Auth::user();
        $b_id = $user->emp_b_id;

        $fields = [
            'pc_show_employee_code',
            'pc_show_employee_name',
            'pc_show_designation',
            'pc_show_department',
            'pc_show_branch',
            'pc_show_ip_uan',
            'pc_show_bank_details',
            'pc_show_month',
            'pc_show_doj',

            'pc_show_earnings_breakdown',
            'pc_show_employee_deductions_breakdown',
            'pc_show_employer_deductions_breakdown',

            'pc_show_net_pay',
            'pc_show_total_ctc',
            'pc_round_off_net_salary',
            'pc_show_net_salary_in_words',

            'pc_show_working_days',
            'pc_show_month_days',
            'pc_show_days_present',
            'pc_show_salary_days',
            'pc_show_lwp_days',
            'pc_show_leaves_taken',

            'pc_show_signature',
            'pc_show_disclaimer',
        ];

        $data = ['pc_b_id' => $b_id]; // Always from logged-in user

        foreach ($fields as $field) {
            $data[$field] = $request->has($field) ? $request->boolean($field) : 0;
        }

        $config = PayslipConfiguration::updateOrCreate(
            ['pc_id' => $request->input('pc_id')],
            $data
        );

        return response()->json([
            'success' => true,
            'message' => $request->input('pc_id') ? 'Payslip configuration updated successfully.' : 'Payslip configuration created successfully.',
            'data' => $config
        ]);
    }


    public function destroy($id)
    {
        $config = PayslipConfiguration::findOrFail($id);
        $config->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
