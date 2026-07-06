<?php

namespace App\Http\Controllers;

use App\Models\BusinessPolicyDocument;
use App\Models\BusinessPolicyFolder;
use App\Models\FhBusinessPolicyDocument;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessPolicyDocumentController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }


    public function index(Request $request, $id = null)
    {

        $user_id = $request->route('id');

        if (!$this->user) {
            abort(404);
        }

        if ($request->ajax()) {


            $id = $request->get('id') ?? $id;

            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['bpd_b_id', $this->user->emp_b_id],
                ],

                [
                    'method' => 'where',
                    'args' => ['bpd_status', 1],
                ],


                [
                    'method' => 'where',
                    'args' => ['bpd_folder_id', $id],
                ],


                [
                    'method' => 'select',
                    'args' => [
                        'bpd_id',
                        'bpd_folder_name',
                        'bpd_version',
                        'bpd_file_name',
                        'bpd_with_effect_from',
                        'bpd_file_path',
                        'bpd_status',
                        'created_at'
                    ],
                    'relation' => []
                ],
                [
                    'method' => 'orderBy',
                    'args' => ['bpd_with_effect_from', 'desc'],
                    'relation' => []
                ]
            ];

            $searchColumns = ['bpd_file_name', 'bpd_with_effect_from', 'bpd_status'];

            $datatableHelper = new DynamicModelDataTableHelper(
                eloquentModel: new BusinessPolicyDocument(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            );

            $list = $datatableHelper->getServerSideDataTable();

            $rowData = [];
            foreach ($list as $index => $val) {
                $row = [];
                $row[] = $index + 1;
                $row[] = $val->bpd_file_name
                    ? '<a href="' . asset($val->bpd_file_path) . '" target="_blank">' . e($val->bpd_file_name) . '</a>'
                    : '';

                $row[] = e($val->bpd_version);
                $row[] = $val->bpd_with_effect_from
                    ? Carbon::parse($val->bpd_with_effect_from)->format('d-M-Y')
                    : '';

                $row[] = $val->bpd_status == 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';

                // Actions dropdown
                $row[] = '
                <div class="btn-list ms-3">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu p-2" style="min-width: 180px;">
                            <li>
                                <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-document"
                                    type="button"
                                    data-id="' . $val->bpd_id . '"
                                    data-folder="' . e($val->bpd_folder_name) . '"
                                    data-version="' . e($val->bpd_version) . '"
                                    data-file_name="' . e($val->bpd_file_name) . '"
                                    data-with_effect_from="' . e($val->bpd_with_effect_from) . '"
                                    data-file_path="' . e($val->bpd_file_path) . '"
                                    data-status="' . e($val->bpd_status) . '">
                                    <i class="feather feather-edit"></i> Edit
                                </button>
                            </li>
                            <li>
                                <a class="dropdown-item text-info fw-semibold d-flex align-items-center gap-2" href="' . asset($val->bpd_file_path) . '" target="_blank">
                                    <i class="feather feather-eye"></i> View
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>';

                $rowData[] = $row;
            }

            return response()->json([
                "draw" => $request->input('draw'),
                "recordsTotal" => count($list),
                "recordsFiltered" => $datatableHelper->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ]);
        }

        // Non-AJAX page load
        $columns = [
            'S. No.',
            'Regulatory Document',
            'Document Version',
            'W.E.F.',
            'Status',
            'Action',
        ];

        $documents = BusinessPolicyDocument::select(
            'bpd_id',
            'bpd_folder_name',
            'bpd_file_name',
            'bpd_with_effect_from',
            'bpd_file_path',
            'bpd_status'
        )
            ->where('bpd_b_id', $this->user->emp_b_id)
            ->orderBy('bpd_with_effect_from', 'desc')
            ->get();

        return view('admin.setting.account.business_policy', compact('documents', 'columns', 'user_id'));
    }


    // public function index_two(Request $request)
    // {

    //     if (!$this->user) {
    //         abort(404);
    //     }

    //     if ($request->ajax()) {
    //         $dynamicConditions = [
    //             [
    //                 'method' => 'where',
    //                 'args' => ['bpd_b_id', $this->user->emp_b_id],
    //             ],


    //             [
    //                 'method' => 'select',
    //                 'args' => [
    //                     'bpd_id',
    //                     'bpd_folder_name',
    //                     'bpd_version',
    //                     'bpd_file_name',
    //                     'bpd_with_effect_from',
    //                     'bpd_file_path',
    //                     'bpd_status',
    //                     'created_at'
    //                 ],
    //                 'relation' => []
    //             ],
    //             [
    //                 'method' => 'orderBy',
    //                 'args' => ['bpd_with_effect_from', 'desc'],
    //                 'relation' => []
    //             ]
    //         ];

    //         $searchColumns = ['bpd_file_name', 'bpd_with_effect_from', 'bpd_status'];

    //         $datatableHelper = new DynamicModelDataTableHelper(
    //             eloquentModel: new BusinessPolicyDocument(),
    //             dynamicConditions: $dynamicConditions,
    //             searchColumns: $searchColumns,
    //         );

    //         $list = $datatableHelper->getServerSideDataTable();

    //         $rowData = [];
    //         foreach ($list as $index => $val) {
    //             $row = [];
    //             $row[] = $index + 1;
    //             $row[] = $val->bpd_file_name
    //                 ? '<a href="' . asset($val->bpd_file_path) . '" target="_blank">' . e($val->bpd_file_name) . '</a>'
    //                 : '';

    //             $row[] = e($val->bpd_folder_name);
    //             $row[] = e($val->bpd_version);
    //             $row[] = $val->bpd_with_effect_from
    //                 ? Carbon::parse($val->bpd_with_effect_from)->format('d-M-Y')
    //                 : '';

    //             $row[] = $val->bpd_status == 1
    //                 ? '<span class="badge bg-success">Active</span>'
    //                 : '<span class="badge bg-secondary">Inactive</span>';

    //             // Actions dropdown
    //             $row[] = '
    //             <div class="btn-list ms-3">
    //                 <div class="dropdown">
    //                     <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
    //                         <i class="fa fa-ellipsis-v"></i>
    //                     </button>
    //                     <ul class="dropdown-menu p-2" style="min-width: 180px;">
    //                         <li>
    //                             <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-document"
    //                                 type="button"
    //                                 data-id="' . $val->bpd_id . '"
    //                                 data-folder="' . e($val->bpd_folder_name) . '"
    //                                 data-version="' . e($val->bpd_version) . '"
    //                                 data-file_name="' . e($val->bpd_file_name) . '"
    //                                 data-with_effect_from="' . e($val->bpd_with_effect_from) . '"
    //                                 data-file_path="' . e($val->bpd_file_path) . '"
    //                                 data-status="' . e($val->bpd_status) . '">
    //                                 <i class="feather feather-edit"></i> Edit
    //                             </button>
    //                         </li>
    //                         <li>
    //                             <a class="dropdown-item text-info fw-semibold d-flex align-items-center gap-2" href="' . asset($val->bpd_file_path) . '" target="_blank">
    //                                 <i class="feather feather-eye"></i> View
    //                             </a>
    //                         </li>
    //                     </ul>
    //                 </div>
    //             </div>';

    //             $rowData[] = $row;
    //         }

    //         return response()->json([
    //             "draw" => $request->input('draw'),
    //             "recordsTotal" => count($list),
    //             "recordsFiltered" => $datatableHelper->countFilteredServerSideDataTable(),
    //             "data" => $rowData,
    //         ]);
    //     }

    //     // Non-AJAX page load
    //     $columns = [
    //         'S. No.',
    //         'Regulatory Document',
    //         'Folder Name',
    //         'Document Version',
    //         'W.E.F.',
    //         'Status',
    //         'Action',
    //     ];

    //     $documents = BusinessPolicyDocument::select(
    //         'bpd_id',
    //         'bpd_folder_name',
    //         'bpd_file_name',
    //         'bpd_with_effect_from',
    //         'bpd_file_path',
    //         'bpd_status'
    //     )
    //         ->where('bpd_b_id', $this->user->emp_b_id)
    //         ->orderBy('bpd_with_effect_from', 'desc')
    //         ->get();

    //     return view('admin.setting.account.business_policy', compact('documents', 'columns'));
    // }


    public function store(Request $request)
    {

        // dd($request->all());

        $request->validate([
            'bpd_file_name' => 'required|string|max:255',
            'bpd_with_effect_from' => 'required|date',
            'bpd_status' => 'required|in:0,1',
            'bpd_file_path' => $request->bpd_id ? 'nullable|file|mimes:pdf,doc,docx' : 'required|file|mimes:pdf,doc,docx',
        ]);

        $user = Auth::user();
        $businessId = $user->emp_b_id;



        $document = $request->bpd_id ? BusinessPolicyDocument::findOrFail($request->bpd_id) : new BusinessPolicyDocument();

        if ($request->hasFile('bpd_file_path')) {
            $file = $request->file('bpd_file_path');
            $filename = $file->getClientOriginalExtension();
            $destination = public_path('upload');

            // Ensure directory exists
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            $file->move($destination, $filename);

            $document->bpd_file_path = 'upload/' . $filename; // Relative to public/
        }


        $document->bpd_file_name = $request->bpd_file_name;
        $document->bpd_folder_id = $request->user_id;
        $document->bpd_folder_name = "N/A";
        $document->bpd_b_id = $businessId;
        $document->bpd_version = $request->bpd_version;
        $document->bpd_with_effect_from = Carbon::parse($request->bpd_with_effect_from)->format('Y-m-d');
        $document->bpd_status = $request->bpd_status;

        $document->save();

        return response()->json([
            'success' => $request->bpd_id ? 'Document updated successfully.' : 'Document created successfully.',
        ]);
    }



    public function destroy($id)
    {
        $document = BusinessPolicyDocument::findOrFail($id);

        if ($document->bpd_file_path && file_exists(public_path($document->bpd_file_path))) {
            unlink(public_path($document->bpd_file_path));
        }

        $document->delete();

        return response()->json([
            'success' => 'Document deleted successfully.',
        ]);
    }






    public function folder_index()
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;
        $businessfolders = BusinessPolicyFolder::where('pbf_b_id', $business_id)->get();
        $folderIds = $businessfolders->pluck('bpf_id')->toArray();
        $businesdocument = BusinessPolicyDocument::whereIn('bpd_folder_id', $folderIds)
            // ->orderBy('bpd_version', 'desc')
            ->orderBy('created_at', 'desc')
            ->where('bpd_status', 1)
            ->get();




        return view('admin.setting.account.business_policy_folder', compact('businessfolders', 'businesdocument'));
    }




    public function folder_store(Request $request)
    {
        try {
            $user = Auth::user();
            $business_id = $user->emp_b_id;

            $validatedData = $request->validate([
                'bpf_name' => 'required|string|max:255|unique:business_policy_folder,bpf_name,' . $request->bpf_id . ',bpf_id',
            ]);

            if ($request->filled('bpf_id')) {
                $folder = BusinessPolicyFolder::findOrFail($request->bpf_id);
                $folder->bpf_name = $validatedData['bpf_name'];
                $folder->pbf_b_id = $business_id;
                $folder->save();
                $message = 'Folder updated successfully.';
            } else {
                BusinessPolicyFolder::create([
                    'bpf_name' => $validatedData['bpf_name'],
                    'pbf_b_id' => $business_id
                ]);
                $message = 'Folder created successfully.';
            }

            return response()->json(['success' => true, 'message' => $message]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unexpected error occurred.'], 500);
        }
    }

    public function folder_destroy($id)
    {
        $folder = BusinessPolicyFolder::findOrFail($id);
        $folder->delete();

        return response()->json(['success' => 'Folder deleted successfully.']);
    }
}
