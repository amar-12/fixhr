<?php

namespace App\Http\Controllers\SuperAdmin;

use DB;
use Log;
use App\Models\Menu;
use App\Models\Module;
use App\Models\Business;
use App\Models\MenuModule;
use App\Models\Employee;
use App\Models\MasterTable;
use Illuminate\Http\Request;
use App\Models\ModuleFeature;
use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Models\BusinessModuleAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;

class SuperAdminController extends Controller
{
    public function login()
    {
        return view('superadmin.login');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('superadmin.login')->with('success', 'Logged out successfully.');
    }



    public function createSuperAdmin(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        try {

            $superadmin = new Employee;
            $superadmin->emp_email = $request->email;
            $superadmin->emp_password = bcrypt($request->password);
            $superadmin->save();

            return redirect()->route('superadmin.login')
                ->with('success', 'Super Admin created successfully.');
        } catch (QueryException $e) {
            return redirect()->back()
                ->with('error', 'Something went wrong. Please try again.')
                ->withInput();
        }
    }


    public function loginSuperAdmin(Request $request){
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        $email = $request->email;
        $password = $request->password;
        $superadmin = Employee::where('emp_email', $email)->first();


        if($superadmin) {
            if (password_verify($password, $superadmin->emp_password)) {
                // dd("khem");
                Auth::login($superadmin);

                // $a=Auth::user();
                // dd($a);
                return redirect()->route('superadmin.dashboard')
                    ->with('success', 'Login successfully.');
            } else {
                return redirect()->back()
                    ->with('error', 'Invalid credentials check your email and password.')
                    ->withInput();
            }
        }

        else {
                return redirect()->back()
                    ->with('error', 'Invalid credentials check your email and password.')
                    ->withInput();
            }

        // if(Auth::attempt(['emp_email' => $request->email, 'emp_password' => bcrypt($request->password), 'emp_role_id' => 22])) {
        //     return redirect()->route('superadmin.dashboard')
        //     ->with('success', 'Login successfully.');
        // } else {
        //     return redirect()->back()
        //         ->with('error', 'Invalid credentials.')
        //         ->withInput();
        // }
    }
    public function index()
    {
        return view('superadmin.masterview');
    }

 
    public function dashboard()
    {
        return view('superadmin.dashboard');
    }

    public function getData(Request $request)
    {
        // Define dynamic conditions for the query
        $dynamicConditions = [
            [
                'method' => 'select',
                'args' => ['m_id', 'm_group', 'm_name', 'm_alias_name', 'm_type', 'm_other', 'm_description', 'created_at', 'updated_at'],
                'relation' => [] // Add empty relation array to prevent undefined key error
            ]
        ];

        // Add sorting if requested
        if ($request->has('order.0.column') && $request->has('order.0.dir')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir');
            
            // Define sortable columns
            $sortableColumns = ['m_id', 'm_group', 'm_name', 'm_alias_name', 'm_type', 'm_other', 'm_description'];
            
            if (isset($sortableColumns[$orderColumnIndex])) {
                $dynamicConditions[] = [
                    'method' => 'orderBy',
                    'args' => [$sortableColumns[$orderColumnIndex], $orderDirection]
                ];
            }
        }

        // Define searchable columns
        $searchColumns = ['m_group', 'm_name', 'm_alias_name', 'm_type', 'm_other', 'm_description'];

        // Define searchable relationships (if any)
        $searchRelationships = [];

        // Create helper instance
        $helper = new DynamicModelDataTableHelper(
            eloquentModel: new MasterTable(),
            dynamicConditions: $dynamicConditions,
            searchColumns: $searchColumns,
            searchRelationships: $searchRelationships
        );

        // Get the data
        $data = $helper->getServerSideDataTable();
        
        // Get total count for pagination
        $totalRecords = MasterTable::count();
        $filteredRecords = $helper->countFilteredServerSideDataTable();

        // Format data for DataTables
        $formattedData = [];
        foreach ($data as $item) {
            $formattedData[] = [
                'm_id' => $item->m_id,
                'm_group' => $item->m_group ?? '----',
                'm_name' => $item->m_name ?? '----',
                'm_alias_name' => $item->m_alias_name ?? '----',
                'm_type' => $item->m_type ?? '----',
                'm_other' => $item->m_other ?? '----',
                'm_description' => $item->m_description ?? '----',
                'actions' => $this->getActionButtons($item->m_id)
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData
        ]);
    }

    /**
     * Generate action buttons for each row
     */
    private function getActionButtons($id)
    {
        return '<div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-primary edit-master" data-id="' . $id . '">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-master" data-id="' . $id . '">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>';
    }

    public function createMaster(Request $request)
    {

        $existing = MasterTable::where('m_group', $request->group)
            ->where('m_name', $request->name)
            ->exists();

        if ($existing) {
            // return redirect()->route('superadmin.masters')->with('error', 'Duplicate entry for this Group and Name is not allowed.');
            return response()->json([
                'success' => false,
                'message' => 'Duplicate entry for this Master Group and Master Name is not allowed.',

            ]);
        }

        try {
            // Insert data into database
            MasterTable::create([
                'm_group' => $request->group,
                'm_name' => $request->name,
                'm_type' => $request->type,
                'm_alias_name' => $request->alias_name,
                'm_description' => $request->description,
                'm_other' => $request->other,
            ]);

            // return redirect()->route('superadmin.masters')->with('success', 'Master created successfully.');

            return response()->json([
                'success' => true,
                'message' => 'Master created successfully.',
            ]);
        } catch (QueryException $e) {
            // return redirect()->route('superadmin.masters')->with('error', 'Something went wrong. Please try again.');
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ]);
        }
    }








    public function updateMaster(Request $request, $id)
    {
        $existing = MasterTable::where('m_group', $request->group)
            ->where('m_name', $request->name)
            ->where('m_id', '!=', $id) // $id is the current record's ID
            ->exists();

        if ($existing) {
            // return redirect()->route('superadmin.masters')->with('error', 'Duplicate entry for this Group and Name is not allowed.');
            return response()->json([
                'status' => false,
                'message' => 'Duplicate entry for this Master Group and Master Name is not allowed.',

            ]);
        }

        DB::table('master_table')->where('m_id', $id)->update([
            'm_name' => $request->name,
            'm_group' => $request->group,
            'm_type' => $request->type,
            'm_description' => $request->description,
            'm_other' => $request->other,
            'm_alias_name' => $request->alias
        ]);


        return response()->json([
            'status' => true,
        ]);
    }



    public function deleteMaster($id)
    {

        $master = MasterTable::find($id);

        if (!$master) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found.'
            ], 404);
        }


        $master->delete();

        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully.',
        ]);
    }


    public function editMaster($id)
    {
        $response = ['status' => false, 'result' => [], 'message' => ''];

        try {
            $master = MasterTable::find($id);
            if (!$master) {
                $response['message'] = 'Data not found';
            } else {
                $response['status'] = true;
                $response['message'] = 'Data found';
                $response['result'] = $master;
            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }



    public function moduleDashboard()
    {
        return view('superadmin.modules');
    }


    public function createModule(Request $request)
    {

        $existing = Module::where('mdl_name', $request->mdl_name)
            ->where('mdl_code', $request->mdl_code)
            ->exists();

        if ($existing) {
            // return redirect()->route('module.dashboard')->with('error', 'Duplicate entry for this Name and Code is not allowed.');
            return response()->json([
                'status' => false,
                'message' => 'Duplicate entry for this Module Name and Module Code is not allowed.',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'mdl_name' => 'required|string|max:255',
            'mdl_code' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => '',
                'errors' => $validator->errors()
            ]);
        }

        try {
            // Insert data into database
            $module = new Module;
            $module->mdl_name = $request->mdl_name;
            $module->mdl_code = $request->mdl_code;
            $module->mdl_description = $request->mdl_description;
            $module->mdl_price = $request->mdl_price;
            $module->save();

            return response()->json([
                'status' => true,
            ]);
        } catch (QueryException $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getModules(Request $request)
    {
        // Define dynamic conditions for the query
        $dynamicConditions = [
            [
                'method' => 'select',
                'args' => ['mdl_id', 'mdl_name', 'mdl_code', 'mdl_description', 'mdl_price', 'created_at', 'updated_at'],
                'relation' => []
            ]
        ];

        // Add sorting if requested
        if ($request->has('order.0.column') && $request->has('order.0.dir')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir');

            // Define sortable columns
            $sortableColumns = ['mdl_id', 'mdl_name', 'mdl_code', 'mdl_description','mdl_price'];

            if (isset($sortableColumns[$orderColumnIndex])) {
                $dynamicConditions[] = [
                    'method' => 'orderBy',
                    'args' => [$sortableColumns[$orderColumnIndex], $orderDirection]
                ];
            }
        }

        // Define searchable columns
        $searchColumns = ['mdl_name', 'mdl_code', 'mdl_description','mdl
        _price'];
        $searchRelationships = [];

        // Create helper instance
        $helper = new \ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper(
            eloquentModel: new \App\Models\Module(),
            dynamicConditions: $dynamicConditions,
            searchColumns: $searchColumns,
            searchRelationships: $searchRelationships
        );

        // Get the data
        $data = $helper->getServerSideDataTable();
        $totalRecords = \App\Models\Module::count();
        $filteredRecords = $helper->countFilteredServerSideDataTable();

        // Format data for DataTables
        $formattedData = [];
        foreach ($data as $item) {
            $formattedData[] = [
                'mdl_id' => $item->mdl_id,
                'mdl_name' => $item->mdl_name ?? '----',
                'mdl_code' => $item->mdl_code ?? '----',
                'mdl_description' => $item->mdl_description ?? '----',
                'mdl_price' => $item->mdl_price ?? '----',
                'actions' => $this->getModuleActionButtons($item->mdl_id)
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData
        ]);
    }

    /**
     * Generate action buttons for each module row
     */
    private function getModuleActionButtons($id)
    {
        return '<div class="btn-group">'
            . '<button type="button" class="btn btn-sm btn-outline-primary edit-btn" data-update-id="' . $id . '"><i class="fas fa-edit"></i></button>'
            . '<button type="button" class="btn btn-sm btn-outline-danger delete-btn" id="delete-data" data-id="' . $id . '"><i class="fas fa-trash-alt"></i></button>'
            . '<button type="button" class="btn btn-info btn-sm view-btn" data-view-id="' . $id . '"><i class="fas fa-eye"></i></button>'
            . '</div>';
    }

    public function getUpdate($id)
    {
        $module = Module::findOrFail($id);

        $module_list = Module::get();
        $features = ModuleFeature::where('mdf_mdl_id', $id)->get();
        $module_selected_features = $features->isNotEmpty() ? $features : null;

        $feature_code = ModuleFeature::where('mdf_mdl_id', $id)->get();

        $module_selected_code = $feature_code->isNotEmpty() ? $feature_code : null;


        return view('superadmin.updatemodule', compact('module_list', 'module', 'module_selected_features', 'module_selected_code'));
    }

    public function storefeature(Request $request)
    {
        //  dd($request->all());


        try {
            $feature = ModuleFeature::create([
                'mdf_mdl_id' => $request->mdf_mdl_id,
                'mdf_name' => $request->mdf_name,
                'mdf_code' => $request->mdf_code,
                'mdf_description' => $request->mdf_description,
                'mdf_price' => is_numeric($request->mdf_price) ? (double)$request->mdf_price : 0,

            ]);

            return response()->json([
                'success' => true,
                'message' => 'Feature added successfully',
                'data' => $feature
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add feature',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getModulesFeatures(Request $request)
    {
        // Define dynamic conditions for the query
        $dynamicConditions = [
            [
                'method' => 'select',
                'args' => ['mdf_id', 'mdf_mdl_id', 'mdf_name', 'mdf_code', 'mdf_description', 'mdf_price', 'created_at', 'updated_at'],
                'relation' => []
            ]
        ];

        // Add sorting if requested
        if ($request->has('order.0.column') && $request->has('order.0.dir')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir');

            // Define sortable columns
            $sortableColumns = ['mdf_id', 'mdf_mdl_id', 'mdf_name', 'mdf_code', 'mdf_description', 'mdf_price'];

            if (isset($sortableColumns[$orderColumnIndex])) {
                $dynamicConditions[] = [
                    'method' => 'orderBy',
                    'args' => [$sortableColumns[$orderColumnIndex], $orderDirection]
                ];
            }
        }

        // Define searchable columns
        $searchColumns = ['mdf_mdl_id', 'mdf_name', 'mdf_code', 'mdf_description', 'mdf_price'];
        $searchRelationships = [];

        // Create helper instance
        $helper = new \ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper(
            eloquentModel: new \App\Models\ModuleFeature(),
            dynamicConditions: $dynamicConditions,
            searchColumns: $searchColumns,
            searchRelationships: $searchRelationships
        );

        // Get the data
        $data = $helper->getServerSideDataTable();
        $totalRecords = \App\Models\ModuleFeature::count();
        $filteredRecords = $helper->countFilteredServerSideDataTable();

        // Format data for DataTables
        $formattedData = [];
        foreach ($data as $item) {
            $formattedData[] = [
                'mdf_id' => $item->mdf_id,
                'mdf_mdl_id' => $item->mdf_mdl_id ?? '----',
                'mdf_name' => $item->mdf_name ?? '----',
                'mdf_code' => $item->mdf_code ?? '----',
                'mdf_description' => $item->mdf_description ?? '----',
                'mdf_price' => $item->mdf_price ?? '----',
                'actions' => $this->getModuleFeatureActionButtons($item->mdf_id)
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData
        ]);
    }

    /**
     * Generate action buttons for each module feature row
     */
    private function getModuleFeatureActionButtons($id)
    {
        return '<div class="btn-group">'
            . '<button type="button" class="btn btn-sm btn-outline-primary edit-feature-btn" data-update-id="' . $id . '"><i class="fas fa-edit"></i></button>'
            . '<button type="button" class="btn btn-sm btn-outline-danger delete-feature-btn" data-id="' . $id . '"><i class="fas fa-trash-alt"></i></button>'
            . '</div>';
    }

    public function editfeature($id)
    {
        $response = ['status' => false, 'result' => [], 'message' => ''];

        try {
            $master = ModuleFeature::find($id);
            if (!$master) {
                $response['message'] = 'Data not found';
            } else {
                $response['status'] = true;
                $response['message'] = 'Data found';
                $response['result'] = $master;
            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function updatefeature(Request $request, $id)
    {

        $existing = ModuleFeature::where('mdf_name', $request->name)
            ->where('mdf_code', $request->code)
            ->where('mdf_id', '!=', $id)
            ->exists();

        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'Duplicate entry for this Module Name and Module Code is not allowed.',
            ]);
        }
        // dd($request->all());
        DB::table('module_features')->where('mdf_id', $id)->update([

            'mdf_mdl_id' => $request->module,
            'mdf_name' => $request->name,
            'mdf_code' => $request->code,
            'mdf_description' => $request->description,
            'mdf_price' => $request->price
        ]);




        return response()->json([
            'status' => true,
        ]);
    }


    public function deleteFeature($id)
    {
        $master = ModuleFeature::find($id);

        if (!$master) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found.'
            ], 404);
        }


        $master->delete();

        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully.'
        ]);
    }


    public function deleteModule($id)
    {
        $master = Module::find($id);

        if (!$master) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found.'
            ], 404);
        }


        $master->delete();

        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully.'
        ]);
    }


    public function editModule($id)
    {
        $response = ['status' => false, 'result' => [], 'message' => ''];

        try {
            $master = Module::find($id);
            if (!$master) {
                $response['message'] = 'Data not found';
            } else {
                $response['status'] = true;
                $response['message'] = 'Data found';
                $response['result'] = $master;
            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }


    public function updateModule(Request $request, $id)
    {
        // $existing = Module::where('mdl_name', $request->name)
        //     ->where('mdl_code', $request->code)
        //     // ->where('mdl_id', '!=', $id)
        //     ->exists();

        // if ($existing) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Duplicate entry for this Module Name and Module Code is not allowed.',
        //     ]);
        // }

        DB::table('modules')->where('mdl_id', $id)->update([
            'mdl_name' => $request->name,
            'mdl_code' => $request->code,
            'mdl_description' => $request->description,
            'mdl_price' => $request->price,

        ]);


        return response()->json([
            'status' => true,
            // 'success' => 'User updated successfully',
        ]);
    }



    public function menuDashboard()
    {
        return view('superadmin.menu');
    }

    public function managePrivacyPolicy()
    {
        return view('superadmin.privacy-policy-manage');
    }
}
