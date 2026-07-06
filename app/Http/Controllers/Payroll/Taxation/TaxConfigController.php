<?php

namespace App\Http\Controllers\Payroll\Taxation;

use App\Http\Controllers\Controller;
use App\Models\FinancialYear;
use App\Models\IncomeTaxSlab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaxConfigController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $financialYears = FinancialYear::where('fy_b_id',$businessId)->orderBy('fy_year', 'desc')->get();

        // Get current financial year
        $currentFY = FinancialYear::where('fy_b_id',$businessId)->where('fy_is_current', true)->first();

        return view('admin.payroll.taxation.tax-configuration',compact('financialYears', 'currentFY'));
    }


     /**
     * Get tax slabs for a specific financial year and regime
     */
    public function getSlabs(Request $request)
    {
        $request->validate([
            'financial_year_id' => 'required|exists:financial_years,fy_id',
            'regime' => 'required|in:new,old'
        ]);

        $businessId = Auth::user()->emp_b_id;

        $fyExists = FinancialYear::where('fy_id', $request->financial_year_id)
            ->where('fy_b_id', $businessId)
            ->exists();
        if (! $fyExists) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid financial year for this business.',
            ], 403);
        }

        $slabs = IncomeTaxSlab::where('its_fy_id', $request->financial_year_id)
            ->where('its_regime', $request->regime)
            ->where('its_b_id', $businessId)
            ->where('its_is_active', true)
            ->orderBy('its_income_from')
            ->get()
            ->map(function ($slab) {
                return [
                    'id' => $slab->its_id,
                    'financial_year_id' => $slab->its_fy_id,
                    'regime' => $slab->its_regime,
                    'range_start' => $slab->its_income_from,
                    'range_end' => $slab->its_income_to,
                    'tax_rate' => $slab->its_tax_rate,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $slabs
        ]);
    }

    /**
     * Store a new tax slab
     */
    public function store(Request $request)
    {
        $request->validate([
            'financial_year_id' => 'required|exists:financial_years,fy_id',
            'regime' => 'required|in:new,old',
            'range_start' => 'required|numeric|min:0',
            'range_end' => 'nullable|numeric|gt:range_start',
            'tax_rate' => 'required|numeric|min:0|max:100'
        ]);

        $businessId = Auth::user()->emp_b_id;

        $fyExists = FinancialYear::where('fy_id', $request->financial_year_id)
            ->where('fy_b_id', $businessId)
            ->exists();
        if (! $fyExists) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid financial year for this business.',
            ], 403);
        }

        $existingSlab = IncomeTaxSlab::where('its_fy_id', $request->financial_year_id)
            ->where('its_regime', $request->regime)
            ->where('its_b_id', $businessId)
            ->where('its_income_from', $request->range_start)
            ->where('its_is_active', true)
            ->first();

        if ($existingSlab) {
            return response()->json([
                'success' => false,
                'message' => 'A slab with this range already exists for the selected financial year and regime.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $slab = IncomeTaxSlab::create([
                'its_b_id' => $businessId,
                'its_fy_id' => $request->financial_year_id,
                'its_regime' => $request->regime,
                'its_income_from' => $request->range_start,
                'its_income_to' => $request->range_end,
                'its_tax_rate' => $request->tax_rate,
                'its_is_active' => true
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tax slab added successfully.',
                'data' => [
                    'id' => $slab->its_id,
                    'range_start' => $slab->its_income_from,
                    'range_end' => $slab->its_income_to,
                    'tax_rate' => $slab->its_tax_rate
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add tax slab: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing tax slab
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'range_start' => 'required|numeric|min:0',
            'range_end' => 'nullable|numeric|gt:range_start',
            'tax_rate' => 'required|numeric|min:0|max:100'
        ]);

        try {
            $businessId = Auth::user()->emp_b_id;
            $slab = IncomeTaxSlab::where('its_id', $id)->where('its_b_id', $businessId)->firstOrFail();

            $existingSlab = IncomeTaxSlab::where('its_fy_id', $slab->its_fy_id)
                ->where('its_regime', $slab->its_regime)
                ->where('its_b_id', $slab->its_b_id)
                ->where('its_income_from', $request->range_start)
                ->where('its_id', '!=', $id)
                ->where('its_is_active', true)
                ->first();

            if ($existingSlab) {
                return response()->json([
                    'success' => false,
                    'message' => 'Another slab with this range already exists.'
                ], 400);
            }

            DB::beginTransaction();

            $slab->update([
                'its_income_from' => $request->range_start,
                'its_income_to' => $request->range_end,
                'its_tax_rate' => $request->tax_rate
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tax slab updated successfully.',
                'data' => [
                    'id' => $slab->its_id,
                    'range_start' => $slab->its_income_from,
                    'range_end' => $slab->its_income_to,
                    'tax_rate' => $slab->its_tax_rate
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tax slab: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a tax slab (soft delete by setting inactive)
     */
    public function destroy($id)
    {
        try {
            $businessId = Auth::user()->emp_b_id;
            $slab = IncomeTaxSlab::where('its_id', $id)->where('its_b_id', $businessId)->firstOrFail();

            // Soft delete by setting inactive
            $slab->update(['its_is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Tax slab deleted successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tax slab: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get financial years for dropdown
     */
    public function getFinancialYears()
    {
        $businessId = Auth::user()->emp_b_id;
        $years = FinancialYear::where('fy_b_id', $businessId)->orderBy('fy_year', 'desc')->get()->map(function ($year) {
            return [
                'id' => $year->fy_id,
                'year' => $year->fy_year,
                'is_current' => $year->fy_is_current
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $years
        ]);
    }

    /**
     * Seed default tax slabs for a financial year
     */
    public function seedDefaults(Request $request)
    {
        $request->validate([
            'financial_year_id' => 'required|exists:financial_years,fy_id',
            'regime' => 'required|in:new,old'
        ]);

        $businessId = Auth::user()->emp_b_id;

        $fyExists = FinancialYear::where('fy_id', $request->financial_year_id)
            ->where('fy_b_id', $businessId)
            ->exists();
        if (! $fyExists) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid financial year for this business.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Deactivate existing slabs for this FY and regime
            IncomeTaxSlab::where('its_fy_id', $request->financial_year_id)
                ->where('its_regime', $request->regime)
                ->where('its_b_id', $businessId)
                ->update(['its_is_active' => false]);

            $defaultSlabs = [];

            if ($request->regime === 'new') {
                $defaultSlabs = [
                    ['its_income_from' => 0, 'its_income_to' => 400000, 'its_tax_rate' => 0],
                    ['its_income_from' => 400001, 'its_income_to' => 800000, 'its_tax_rate' => 5],
                    ['its_income_from' => 800001, 'its_income_to' => 1200000, 'its_tax_rate' => 10],
                    ['its_income_from' => 1200001, 'its_income_to' => 1600000, 'its_tax_rate' => 15],
                    ['its_income_from' => 1600001, 'its_income_to' => 2000000, 'its_tax_rate' => 20],
                    ['its_income_from' => 2000001, 'its_income_to' => 2400000, 'its_tax_rate' => 25],
                    ['its_income_from' => 2400001, 'its_income_to' => null, 'its_tax_rate' => 30]
                ];
            } else {
                $defaultSlabs = [
                    ['its_income_from' => 0, 'its_income_to' => 250000, 'its_tax_rate' => 0],
                    ['its_income_from' => 250001, 'its_income_to' => 500000, 'its_tax_rate' => 5],
                    ['its_income_from' => 500001, 'its_income_to' => 1000000, 'its_tax_rate' => 20],
                    ['its_income_from' => 1000001, 'its_income_to' => null, 'its_tax_rate' => 30]
                ];
            }

            foreach ($defaultSlabs as $slab) {
                IncomeTaxSlab::create([
                    'its_b_id' => $businessId,
                    'its_fy_id' => $request->financial_year_id,
                    'its_regime' => $request->regime,
                    'its_income_from' => $slab['its_income_from'],
                    'its_income_to' => $slab['its_income_to'],
                    'its_tax_rate' => $slab['its_tax_rate'],
                    'its_is_active' => true
                ]);
            }

            DB::commit();

            // Get newly created slabs
            $slabs = IncomeTaxSlab::where('its_fy_id', $request->financial_year_id)
                ->where('its_regime', $request->regime)
                ->where('its_b_id', $businessId)
                ->where('its_is_active', true)
                ->orderBy('its_income_from')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Default tax slabs seeded successfully.',
                'data' => $slabs
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to seed default slabs: ' . $e->getMessage()
            ], 500);
        }
    }

}
