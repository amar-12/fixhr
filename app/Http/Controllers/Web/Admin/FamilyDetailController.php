<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\FamilyDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class FamilyDetailController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    // public function index(Request $request)
    // {
    //     $user = Auth::user();
    //     $business_id = $user->emp_b_id;

    //     // Employee list for filters or dropdowns
    //     $employee_list = Employee::where('emp_b_id', $business_id)
    //         ->where('emp_role_id', '!=', 1)
    //         ->where('emp_status', '!=', 72)
    //         ->select('emp_full_name', 'emp_id', 'emp_code')
    //         ->get();

    //     if ($request->ajax()) {
    //         // Get distinct employee IDs with family records
    //         $distinctEmpIds = FamilyDetail::where('fd_b_id', $business_id)
    //             ->select('fd_emp_id')
    //             ->distinct()
    //             ->pluck('fd_emp_id');

    //         $familyRecords = Employee::with(['fh_branch', 'fh_department', 'familyDetails'])
    //             ->whereIn('emp_id', $distinctEmpIds)
    //             ->get();

    //         $rowData = [];
    //         foreach ($familyRecords as $index => $emp) {
    //             $rowData[] = [
    //                 $index + 1,
    //                 $emp->emp_code ?? '-',
    //                 $emp->emp_full_name ?? '-',
    //                 $emp->fh_branch->br_name ?? '-',
    //                 $emp->fh_department->d_name ?? '-',
    //                 $emp->emp_phone ?? '-',
    //                 '<div class="btn-list ms-3">
    //                 <div class="dropdown">
    //                     <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown">
    //                         <i class="fa fa-ellipsis-v"></i>
    //                     </button>
    //                     <ul class="dropdown-menu p-2" style="min-width: 180px;">
    //                         <li>
    //                             <a href="' . route('family.edit', $emp->emp_id)  . '" class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2">
    //                                 <i class="feather feather-edit"></i> Edit
    //                             </a>
    //                         </li>
    //                         <li>
    //                             <form method="POST" action="' . route('family.destroy', $emp->emp_id) . '">
    //                                 ' . csrf_field() . method_field('DELETE') . '
    //                                 <button type="submit" class="dropdown-item text-danger fw-semibold delete-family">
    //                                     <i class="feather feather-trash-2"></i> Delete
    //                                 </button>
    //                             </form>
    //                         </li>

    //                                <li>
    //                                     <a href="javascript:void(0);" class="view-family-btn" data-id="' . $emp->emp_id . '">
    //                                        <i class="feather feather-eye"></i> View
    //                                     </a>
    //                             </li>

    //                     </ul>
    //                 </div>
    //             </div>',
    //             ];
    //         }

    //         return response()->json([
    //             "draw" => $request->input('draw'),
    //             "recordsTotal" => count($rowData),
    //             "recordsFiltered" => count($rowData),
    //             "data" => $rowData,
    //         ]);
    //     }

    //     // Non-AJAX View Setup
    //     $columns = [
    //         'S. No.',
    //         'Emp Code',
    //         'Emp Name',
    //         'Branch',
    //         'Department',
    //         'Contact',
    //         'Action'
    //     ];

    //     return view('admin.employees.family_detail.index', compact(
    //         'columns',
    //         'employee_list'
    //     ));
    // }


    public function index(Request $request)
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;

        // Employee list for filters or dropdowns
        $employee_list = Employee::where('emp_b_id', $business_id)
            ->where('emp_role_id', '!=', 1)
            ->where('emp_status', '!=', 72)
            ->select('emp_full_name', 'emp_id', 'emp_code')
            ->get();

        if ($request->ajax()) {
            $searchValue = $request->input('search.value'); // DataTables search input

            // Get distinct employee IDs with family records
            $distinctEmpIds = FamilyDetail::where('fd_b_id', $business_id)
                ->select('fd_emp_id')
                ->distinct()
                ->pluck('fd_emp_id');

            // Query builder for filtering
            $familyRecordsQuery = Employee::with(['fh_branch', 'fh_department', 'familyDetails'])
                ->whereIn('emp_id', $distinctEmpIds);

            // Apply search filters
            if (!empty($searchValue)) {
                $familyRecordsQuery->where(function ($query) use ($searchValue) {
                    $query->where('emp_full_name', 'like', "%{$searchValue}%")
                        ->orWhere('emp_code', 'like', "%{$searchValue}%")
                        ->orWhere('emp_phone', 'like', "%{$searchValue}%")
                        ->orWhereHas('fh_branch', function ($q) use ($searchValue) {
                            $q->where('br_name', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('fh_department', function ($q) use ($searchValue) {
                            $q->where('d_name', 'like', "%{$searchValue}%");
                        });
                });
            }

            $familyRecords = $familyRecordsQuery->get();

            $rowData = [];
            foreach ($familyRecords as $index => $emp) {
                $rowData[] = [
                    $index + 1,
                    $emp->emp_code ?? '-',
                    $emp->emp_full_name ?? '-',
                    $emp->fh_branch->br_name ?? '-',
                    $emp->fh_department->d_name ?? '-',
                    $emp->emp_phone ?? '-',
                    '<div class="btn-list ms-3">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu p-2" style="min-width: 180px;">
                            <li>
                                <a href="' . route('family.edit', $emp->emp_id)  . '" class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2">
                                    <i class="feather feather-edit"></i> Edit
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="' . route('family.destroy', $emp->emp_id) . '">
                                    ' . csrf_field() . method_field('DELETE') . '
                                    <button type="submit" class="dropdown-item text-danger fw-semibold delete-family">
                                        <i class="feather feather-trash-2"></i> Delete
                                    </button>
                                </form>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="view-family-btn" data-id="' . $emp->emp_id . '">
                                   <i class="feather feather-eye"></i> View
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>',
                ];
            }

            return response()->json([
                "draw" => $request->input('draw'),
                "recordsTotal" => $familyRecords->count(),
                "recordsFiltered" => $familyRecords->count(),
                "data" => $rowData,
            ]);
        }

        // Non-AJAX View Setup
        $columns = [
            'S. No.',
            'Emp Code',
            'Emp Name',
            'Branch',
            'Department',
            'Contact',
            'Action'
        ];

        return view('admin.employees.family_detail.index', compact(
            'columns',
            'employee_list'
        ));
    }




    public function create(Request $request)
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;

        $query = Employee::where('emp_b_id', $business_id)
            ->where('emp_status', '!=', 72)
            ->where('emp_role_id', '!=', 1)
            ->select('emp_id', 'emp_full_name', 'emp_code');

        // If encrypted emp_id is passed, decrypt and filter
        if ($request->filled('emp_id')) {
            try {
                $emp_id = Crypt::decrypt($request->emp_id);
                $query->where('emp_id', $emp_id);
            } catch (DecryptException $e) {
                abort(404, 'Unauthorized or invalid access.');
            }
        }

        $employees = $query->get();

        return view('admin.employees.family_detail.create', compact('employees'));
    }




    public function store(Request $request)
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;
        $request->validate([
            'fd_emp_id'       => 'required|exists:employees,emp_id',
            'fd_name'         => 'required|array',
            'fd_relation'     => 'required|array',
            'fd_dob'          => 'required|array',
            'fd_dependency'   => 'required|array',
            'fd_occupation'   => 'required|array',
            'fd_contact'      => 'required|array',
            'fd_contact.*'    => 'digits:10',
        ]);

        foreach ($request->fd_name as $index => $name) {
            FamilyDetail::create([
                'fd_emp_id'     => $request->fd_emp_id,
                'fd_b_id'     => $business_id,
                'fd_name'       => $request->fd_name[$index],
                'fd_relation'   => $request->fd_relation[$index],
                'fd_dob'        => $request->fd_dob[$index],
                'fd_dependency' => $request->fd_dependency[$index],
                'fd_occupation' => $request->fd_occupation[$index],
                'fd_contact'    => $request->fd_contact[$index],
            ]);
        }

        return redirect()->route('family.index')->with('success', 'Family details added successfully.');
    }


    public function edit($id)
    {
        $family = FamilyDetail::where('fd_emp_id', $id)->first();
        $familyDetails = FamilyDetail::where('fd_emp_id', $id)->get();

        // dd($family);
        return view('admin.employees.family_detail.update', compact('family', 'familyDetails'));
    }

    public function update(Request $request, $emp_id)
    {

        $user = Auth::user();
        $business_id = $user->emp_b_id;

        $request->validate([
            'fd_name'         => 'required|array',
            'fd_relation'     => 'required|array',
            'fd_dob'          => 'required|array',
            'fd_dependency'   => 'required|array',
            'fd_occupation'   => 'required|array',
            'fd_contact'      => 'required|array',
            'fd_contact.*'    => 'digits:10',
        ]);

        // Remove old family records for the employee
        FamilyDetail::where('fd_emp_id', $emp_id)->delete();

        // Insert updated family records
        foreach ($request->fd_name as $index => $name) {
            FamilyDetail::create([
                'fd_emp_id'     => $request->fd_emp_id,
                'fd_b_id'       => $business_id,
                'fd_name'       => $request->fd_name[$index],
                'fd_relation'   => $request->fd_relation[$index],
                'fd_dob'        => $request->fd_dob[$index],
                'fd_dependency' => $request->fd_dependency[$index],
                'fd_occupation' => $request->fd_occupation[$index],
                'fd_contact'    => $request->fd_contact[$index],
            ]);
        }

        return redirect()->route('family.index')->with('success', 'Family details updated successfully.');
    }


    public function destroy($id)
    {
        FamilyDetail::where('fd_emp_id', $id)->delete(); // ✅ Works directly

        return redirect()->route('family.index')->with('success', 'Family details deleted successfully.');
    }

    public function getFamilyDetails($id)
    {
        $employee = Employee::with('familyDetails') // Make sure the relation exists
            ->where('emp_id', $id)
            ->select('emp_id', 'emp_full_name')
            ->first();

        if (!$employee) {
            return response()->json([
                'error' => 'Employee not found'
            ], 404);
        }

        // Format the DOBs
        $formattedFamily = $employee->familyDetails->map(function ($item) {
            return [
                'fd_name'       => $item->fd_name,
                'fd_relation'   => $item->fd_relation,
                'fd_dob'        => $item->fd_dob ? Carbon::parse($item->fd_dob)->format('j F Y') : null,
                'fd_dependency' => $item->fd_dependency,
                'fd_occupation' => $item->fd_occupation,
                'fd_contact'    => $item->fd_contact,
            ];
        });

        return response()->json([
            'employee' => $employee->emp_full_name,
            'familyRecords' => $formattedFamily
        ]);
    }

    public function show(Request $request)
    {
        $id = $request->emp_id;

        if (is_null($id)) {
            return redirect()->back()->with('error', 'Employee ID is required.');
        }

        $employee = Employee::where('emp_id', $id)->first();

        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        $familyDetails = FamilyDetail::where('fd_emp_id', $id)->get();

        $family = $familyDetails->first();

        if (is_null($family)) {
            return redirect()->back()->with('error', 'No family details found for this employee.');
        }

        return view('admin.employees.family_detail.show', compact('family', 'familyDetails'));
    }
}
