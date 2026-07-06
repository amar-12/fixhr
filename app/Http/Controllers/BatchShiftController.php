<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\PolicyShiftTiming;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BatchShiftSample;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ShiftBatch;
use App\Models\ShiftBatchEmployee;

class BatchShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $shiftPolicies = PolicyShiftTiming::where('pst_b_id', $user->emp_b_id)->get();
        $departments = Department::where('d_b_id', $user->emp_b_id)->get();
        $employees = Employee::where('emp_b_id', $user->emp_b_id)
            ->where('emp_status', 71)
            ->where('emp_role_id', '!=', 1)
            ->get();

        $columns = [
            'S.No',
            'Batch Name',
            'Shift',
            'Start Date',
            'End Date',
            'Employees',
            'Status',
            'Actions',
        ];
        return view('admin.employees.shift-batch', compact('columns', 'shiftPolicies', 'departments', 'employees'));
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

        // Validation Rules
        $validator = Validator::make($request->all(), [
            'sb_name'     => 'required|string|min:3|max:255',
            'sb_code'     => [
                'required',
                'string',
                'min:5',
                'max:255',
                'unique:shift_batches,sb_code',
                'regex:/^[a-zA-Z]+[a-zA-Z0-9_-]*[0-9]+$/'
            ],
            'pst_id'      => 'required|integer|exists:policy_shift_timings,pst_id',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'emp_id'      => 'nullable|array',
            'emp_id.*'    => 'integer|exists:employees,emp_id',
            'import_file' => 'nullable|file|mimes:xlsx,csv|max:10240', // 10MB limit
        ], [
            'sb_code.regex'   => 'Batch code must start with letters and end with numbers (e.g., Batch123, Batch_123, Batch-123).',
            'sb_code.min'     => 'Batch code must be at least 5 characters long.',
            'pst_id.exists'   => 'The selected shift policy is invalid.',
            'emp_id.*.exists' => 'One or more selected employees do not exist.',
        ]);

        // Custom Validation: At least one employee must be assigned (dropdown OR file)
        $validator->after(function ($validator) use ($request) {
            $hasDropdown = $request->has('emp_id') && is_array($request->emp_id) && count($request->emp_id) > 0;
            $hasFile     = $request->hasFile('import_file');

            if (!$hasDropdown && !$hasFile) {
                $validator->errors()->add(
                    'employee_assignment',
                    'At least one employee must be assigned — either by selecting from the list or uploading an Excel/CSV file.'
                );
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Create Shift Batch
            $batch = ShiftBatch::create([
                'sb_b_id'       => $user->emp_b_id ?? null,
                'sb_name'       => $request->sb_name,
                'sb_code'       => $request->sb_code,
                'sb_pst_id'     => $request->pst_id,
                'sb_start_date' => $request->start_date,
                'sb_end_date'   => $request->end_date,
                'sb_created_by' => $user->emp_id ?? null,
            ]);

            $employeeIds = [];

            // 1. Employees selected from dropdown
            if ($request->filled('emp_id')) {
                $employeeIds = array_merge($employeeIds, $request->emp_id);
            }

            // 2. Employees from uploaded Excel/CSV file
            if ($request->hasFile('import_file')) {
                $file = $request->file('import_file');
                $rows = Excel::toArray([], $file)[0];

                if (count($rows) < 2) {
                    throw new \Exception('The uploaded file contains no employee data.');
                }

                // Validate headers strictly
                $expectedHeaders = ['s. no.*', 'emp code*', 'emp name'];
                $actualHeaders   = array_map(fn($h) => strtolower(trim($h)), $rows[0]);

                if ($actualHeaders !== $expectedHeaders) {
                    throw new \Exception('Invalid file format. Expected headers: emp_code, emp_name');
                }

                $importErrors = [];

                foreach ($rows as $index => $row) {
                    if ($index === 0) continue; // Skip header row

                    $rowNumber = $index + 1;
                    $empCode   = trim($row[1] ?? '');
                    $empName   = trim($row[2] ?? '');

                    if (!$empCode) {
                        $importErrors[] = "Row {$rowNumber}: emp_code are required.";
                        continue;
                    }

                    $employee = Employee::where('emp_code', $empCode)->first();

                    if (!$employee) {
                        $importErrors[] = "Row {$rowNumber}: Employee not found with code '{$empCode}'.";
                        continue;
                    }

                    // Prevent duplicates (if same employee in dropdown + file)
                    if (!in_array($employee->emp_id, $employeeIds)) {
                        $employeeIds[] = $employee->emp_id;
                    }
                }

                if (!empty($importErrors)) {
                    throw new \Exception(implode('<br>', $importErrors));
                }
            }

            // Final safety check: Must have at least one valid employee
            if (empty($employeeIds)) {
                throw new \Exception('No valid employees were assigned to this batch.');
            }

            // Prepare data for bulk insert
            $batchEmployees = array_map(function ($empId) use ($user, $batch) {
                return [
                    'sbe_b_id'    => $user->emp_b_id ?? null,
                    'sbe_sb_id'   => $batch->sb_id,
                    'sbe_emp_id'  => $empId,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }, $employeeIds);

            // Bulk insert for performance
            ShiftBatchEmployee::insert($batchEmployees);

            DB::commit();

            return redirect()->back()->with(
                'success',
                "Batch '{$batch->sb_name}' created successfully with " . count($employeeIds) . ' employee(s).'
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
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

    public function batchShiftSample()
    {
        return Excel::download(new BatchShiftSample, 'batch_shift_sample.xlsx');
    }
}