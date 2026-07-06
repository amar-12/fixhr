<?php

namespace App\Http\Controllers;

use App\Helpers\CentralLogics;
use App\Models\Employee;
use App\Models\MailTemplate;
use App\Models\MasterTable;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MailTemplateController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index(Request $request)
    {
        $businessId = Auth::user()->emp_b_id;
        $moduleFilter   = $request->module_id;
        $mailTypeFilter = $request->mail_type;
        $statusFilter   = $request->status;
        $modules   = MasterTable::where('m_group', 'MODULE')->whereNotIn('m_id', [229, 249, 250, 339, 442, 562])->select('m_name', 'm_id')->get();
        $mailTypes = MasterTable::where('m_group', 'MAIL_TYPE')->select('m_name', 'm_id')->get();
        if ($request->ajax()) {

            $dynamicConditions = [
                ['method' => 'where', 'args' => ['mt_b_id', $businessId], 'relation' => []],
                ['method' => 'with', 'args' => [['fh_master_table:m_id,m_name', 'fh_module:m_id,m_name']], 'relation' => []],
                ['method' => 'select', 'args' => ['mt_id', 'mt_title', 'mt_mail_type', 'mt_module_id', 'mt_is_enabled', 'created_at'], 'relation' => []],
            ];

            if (!empty($moduleFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['mt_module_id', $moduleFilter]];
            }

            if (!empty($mailTypeFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['mt_mail_type', $mailTypeFilter]];
            }

            if ($statusFilter !== null && $statusFilter !== "") {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['mt_is_enabled', $statusFilter]];
            }

            $searchColumns = ['mt_title'];
            $searchRelationships = [
                'fh_master_table' => ['m_name'],
                'fh_module'       => ['m_name']
            ];

            $helper = new DynamicModelDataTableHelper(
                new MailTemplate(),
                $dynamicConditions,
                $searchColumns,
                $searchRelationships
            );

            $list = $helper->getServerSideDataTable();
            $rowData = [];

            foreach ($list as $i => $d) {

                $actions = '
            <div class="dropdown">
                <button class="btn btn-light btn-sm" data-bs-toggle="dropdown">
                    <i class="fa fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end p-2">
                    <li>
                        <a href="javascript:void(0)" class="dropdown-item text-primary editBtn" data-id="' . $d->mt_id . '">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" class="dropdown-item text-danger deleteBtn" data-id="' . $d->mt_id . '">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)" class="dropdown-item ' . ($d->mt_is_enabled ? 'text-secondary' : 'text-success') . ' toggleStatusBtn"
                           data-id="' . $d->mt_id . '" data-status="' . ($d->mt_is_enabled ? 1 : 0) . '">
                            <i class="bi ' . ($d->mt_is_enabled ? 'bi-eye-slash' : 'bi-eye') . '"></i> ' . ($d->mt_is_enabled ? 'Disable' : 'Enable') . '
                        </a>
                    </li>
                </ul>
            </div>
            ';

                $rowData[] = [
                    $i + 1,
                    e($d->mt_title),
                    e(optional($d->fh_module)->m_name ?? 'N/A'),
                    $d->mt_is_enabled ? '<span class="badge bg-success">Enabled</span>' : '<span class="badge bg-secondary">Disabled</span>',
                    $d->created_at?->format('d M Y') ?? '-',
                    $actions
                ];
            }

            return response()->json([
                "draw"            => $request->draw,
                "recordsTotal"    => MailTemplate::where('mt_b_id', $businessId)->count(),
                "recordsFiltered" => $helper->countFilteredServerSideDataTable(),
                "data"            => $rowData
            ]);
        }

        // Columns for frontend DataTable
        $columns = [
            ['name' => 'S. No.', 'width' => '4%'],
            ['name' => 'Title', 'width' => '20%'],
            ['name' => 'Module', 'width' => '12%'],
            ['name' => 'Status', 'width' => '10%'],
            ['name' => 'W.E.F.', 'width' => '12%'],
            ['name' => 'Action', 'width' => '8%'],
        ];

        return view('admin.email_templates.index', [
            'columns'    => $columns,
            'modules'    => $modules,
            'mailTypes'  => $mailTypes,
            'skillsCount' => MailTemplate::where('mt_b_id', $businessId)->count(),
            'moduleName' => $moduleFilter ? optional(MasterTable::find($moduleFilter))->m_name : null
        ]);
    }

    public function store(Request $request)
    {
        $businessId = Auth::user()->emp_b_id;
        $isCustom = $request->mt_mail_type === 'custom';
    
        $moduleRules = $isCustom ? ['nullable'] : ['required', 'exists:master_table,m_id'];
    
        $mailTypeRules = [
            'required',
            'string',
            'in:submission,rejected,approved,custom',
        ];
    
        if (!$isCustom) {
            $mailTypeRules[] = Rule::unique('mail_templates')
                ->where(function ($query) use ($businessId, $request) {
                    return $query->where('mt_b_id', $businessId)
                        ->where('mt_module_id', $request->mt_module_id);
                })
                ->ignore($request->mt_id, 'mt_id');
        }
    
        $request->validate([
            'mt_title'        => 'required|string|max:255',
            'mt_module_id'    => $moduleRules,
            'mt_mail_type'    => $mailTypeRules,
            // 'mt_mail_send_to' => 'required',
            'mt_body'         => 'required|string',
        ], [
            'mt_mail_type.unique' => 'This Mail Type already exists for the selected Module.',
        ]);
    
        $id = $request->mt_id;
    
        $template = MailTemplate::updateOrCreate(
            ['mt_id' => $id],
            [
                'mt_b_id'       => $businessId,
                'mt_title'      => $request->mt_title,
                'mt_module_id'  => $isCustom ? null : $request->mt_module_id,
                'mt_mail_type'  => $request->mt_mail_type,
                'mt_send_to'  => $request->mt_mail_send_to ?? 0,
                'mt_body'       => $request->mt_body,
                'mt_is_enabled' => $request->mt_is_enabled ?? 0,
            ]
        );
    
        return response()->json([
            'status'  => true,
            'message' => $id
                ? 'Template updated successfully!'
                : 'Template created successfully!',
            'data'    => $template
        ]);
    }

    public function destroy($id)
    {
        $template = MailTemplate::findOrFail($id);
        $template->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Template deleted successfully!'
        ]);
    }

    public function toggleStatus($id)
    {
        $template = MailTemplate::findOrFail($id);
        $template->mt_is_enabled = !$template->mt_is_enabled;
        $template->save();

        return response()->json([
            'status'  => true,
            'message' => $template->mt_is_enabled ? 'Template enabled successfully!' : 'Template disabled successfully!',
            'data'    => $template
        ]);
    }

    public function getTemplate($id)
    {
        $template = MailTemplate::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data'   => $template
        ]);
    }
}
