<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Menu;
use App\Models\MasterTable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;

class MenusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $menu = Menu::where('menu_p_id', 0)->get();
        $sub_menu= Menu::where('menu_p_id', '!=', 0)->get();
        $status = MasterTable::where('m_group', 'STATUS')->get();
        $route_type = MasterTable::where('m_group', 'MENU_ROUTE_TYPE')->get();
        $all_menu = Menu::all();
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['menu_sub_status', '=', 0]
                ],
                [
                    'method' => 'select',
                    'args' => ['menu_id', 'menu_p_id', 'menu_name', 'menu_status', 'menu_sub_status', 'menu_route', 'menu_route_type_id', 'menu_group', 'updated_at'],
                    'relation' => [
                        'fh_menu_route_type:m_id,m_name',
                    ]
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['menu_id', 'menu_p_id', 'menu_name', 'menu_status', 'menu_sub_status', 'menu_group', 'updated_at'] // 'pl_effective_date', 'pl_expire_date',
                ]
            ];

            $searchColumns = ['menu_id', 'menu_p_id', 'menu_name', 'menu_status', 'menu_sub_status', 'menu_route', 'menu_route_type_id', 'menu_group', 'updated_at'];
            $searchRelationships = ['fh_menu_route_type' => ['m_id', 'm_name'],];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new Menu(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 0; // Initialize counter

            foreach ($list as $key => $val) {
                $i++;
                $row = [];

                $sectionName = $val->fh_menu ? $val->fh_menu->menu_name : 'N/A';
                $menuName = $val->menu_name ?? 'N/A';

                $row[] = $i;
                $row[] = $sectionName;
                $row[] = $menuName;
                $row[] = $val->menu_icon ?? 'NA';
                $row[] = isset($val->menu_status) && $val->menu_status == 1 ? 'Active' : 'Inactive'; // Status
                $row[] = $val->menu_route ?? '-';

                $row[] = '<a class="btn btn-sm btn-info edit-menu" data-bs-target="#MenusModal" data-bs-toggle="modal"
                            data-id="' . htmlspecialchars($val->menu_id) . '"
                            data-p_id="' . htmlspecialchars($val->menu_p_id) . '"
                            data-method="' . htmlspecialchars($val->menu_route_type_id) . '"
                            data-name="' . htmlspecialchars($val->menu_name) . '"
                            data-icon="' . htmlspecialchars($val->menu_icon) . '"
                            data-route="' . htmlspecialchars($val->menu_route) . '"
                            data-group="' . htmlspecialchars($val->menu_group) . '"
                            data-status="' . htmlspecialchars($val->menu_status) . '"
                            data-sub_status="' . htmlspecialchars($val->menu_sub_status) . '"
                            data-sequence="' . htmlspecialchars($val->menu_sequence) . '"
                            data-menu_id="' . htmlspecialchars($val->menu_p_id) . '">
                            <i class="feather feather-edit"></i>
                        </a>';

                $rowData[] = $row;
            }


            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new Menu(),
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return json_encode($output);
        }

        $columns = [
            'S. No.',
            'Section Name',
            'Menu Name',
            'Icon',
            'Status',
            'Path',
            'Action',
        ];

        return view('superadmin.menus', compact('columns', 'menu', 'sub_menu', 'status', 'route_type', 'all_menu'));
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
    public function store(Request $request)
    {
        $user = Auth::user();
        $menu = $request->menu_id;
        $sub_menu = $request->menu_p_id;

        $method = $request->Method ?? [];
        $active = $request->active ?? [];
        $icon = $request->icon ?? [];
        $sub_status = $request->sub_status ?? [];
        $route = $request->route ?? [];
        $group = $request->group ?? [];
        $sequence = $request->sequence ?? [];

        foreach ($request->name as $index => $menu_name) {
            $currentMethod = $method[$index] ?? 0;
            $currentActive = $active[$index] ?? 0;
            $currentIcon = $icon[$index] ?? 0;
            $currentSubStatus = $sub_status[$index] ?? 0;
            $currentRoute = $route[$index] ?? '#';
            $currentGroup = $group[$index] ?? '';
            $currentSequence = $sequence[$index] ?? null;

            // Logic for menu and sub_menu
            if ($menu == 'new_menu') {
                $sub_menu = 0;
                $currentMethod = 0;
                $currentIcon = 0;
                $currentSubStatus = 0;
                $currentRoute = '#';
            } elseif ($menu != 'new_menu' && $sub_menu == 'new_sub_menu') {
                $sub_menu = $menu;
            } else {
                $sub_menu = $request->menu_p_id;
            }

            $matchingConditions = [
                'menu_name' => $menu_name,
                'menu_route' => $currentRoute,
            ];

            if ($currentRoute === '#') {
                $matchingConditions['menu_p_id'] = $sub_menu;
            }

            $menu = Menu::updateOrCreate(
                $matchingConditions,
                [
                    'menu_p_id' => $sub_menu,
                    'menu_name' => $menu_name,
                    'menu_icon' => $currentIcon,
                    'menu_status' => $currentActive,
                    'menu_sub_status' => $currentSubStatus,
                    'menu_route' => $currentRoute,
                    'menu_route_type_id' => $currentMethod,
                    'menu_group' => $currentGroup,
                    'menu_sequence' => $currentSequence,
                ]
            );

            if (!$menu) {
                return response()->json(['status' => false, 'message' => 'Menu unable to save.']);
            }
        }

        return response()->json(['status' => true, 'message' => 'Menu saved successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
