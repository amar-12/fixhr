<?php

namespace App\Http\Controllers\Web\Admin\Kit;

use App\Http\Controllers\Controller;
use App\Models\Kit;
use App\Models\KitLog;
use App\Models\KitStock;
use App\Models\UniformItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class KitStockController extends Controller
{
    public function store(Request $request)
    {

        $request->validate([
            'kit_id'         => 'required|numeric',
            'opening_qty'    => 'required|numeric|min:0',
            'price_per_unit' => 'required|numeric|min:0',
            'total_price'    => 'required|numeric|min:0',
        ]);

        try {
            $user = Auth::user();
            $openingQty = $request->opening_qty;
            $existing = KitStock::where('kit_id', $request->kit_id)->where('b_id', $user->emp_b_id)->first();

            if ($existing) {
                $existing->available_qty = $existing->available_qty + $openingQty;
                // $existing->opening_qty   = $existing->opening_qty + $openingQty;
                $existing->total_price   = $existing->total_price + $request->total_price;
                $existing->price_per_unit = $request->price_per_unit; // Updated with latest input
                $existing->note = $request->note ?? $existing->note;


                $existing->is_payable = $request->is_payable ?? $existing->is_payable;
                $existing->discount_type = $request->discount_type ?? $existing->discount_type;
                $existing->discount_value = $request->discount_value ?? $existing->discount_value;

                $existing->save();


                KitLog::create([
                    'b_id'           => $user->emp_b_id,
                    'kit_id'         => $existing->id,
                    'action_type'    => KitLog::UPDATE,
                    'reference_id'   => $existing->id,
                    'opening_qty'    => 0,
                    'available_qty'  => $existing->available_qty,
                    'assigned_qty'   => 0,
                    'damaged_qty'    => 0,
                    'lost_qty'       => 0,
                    'qty'            => $request->opening_qty,
                    'replaced_qty'   => 0,
                    'price_per_unit' => $existing->price_per_unit,
                    'total_price'    => $existing->total_price,
                    'note'           => $request->note,
                    'action_by'      => $user->emp_id,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Kit stock updated successfully.'
                ]);
            }

            // CREATE NEW STOCK RECORD
            $newStock =  KitStock::create([
                'b_id'           => $user->emp_b_id,
                'kit_id'         => $request->kit_id,
                'opening_qty'    => $openingQty,
                'available_qty'  => $openingQty,
                'assigned_qty'   => 0,
                'damaged_qty'    => 0,
                'lost_qty'       => 0,
                'replaced_qty'   => 0,
                'price_per_unit' => $request->price_per_unit,
                'total_price'    => $request->total_price,
                'note'           => $request->note ?? null,

                // Payable Details
                'is_payable'     => $request->is_payable ?? 'no',
                'discount_type'  => $request->discount_type ?? null,
                'discount_value' => $request->discount_value ?? null,
            ]);

            KitLog::create([
                'b_id'           => $user->emp_b_id,
                'kit_id'         => $newStock->id,
                'action_type'    => KitLog::ADD,
                'reference_id'   => $newStock->id,
                'opening_qty'    => $newStock->opening_qty,
                'available_qty'  => $newStock->available_qty,
                'assigned_qty'   => $newStock->assigned_qty,
                'qty'            => $request->opening_qty,
                'damaged_qty'    => $newStock->damaged_qty,
                'lost_qty'       => $newStock->lost_qty,
                'replaced_qty'   => $newStock->replaced_qty,
                'price_per_unit' => $newStock->price_per_unit,
                'total_price'    => $newStock->total_price,
                'note'           => $request->note,
                'action_by'      => $user->emp_id,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Kit stock added successfully.'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong, please try again later.'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'opening_qty'    => 'required|numeric|min:0',
            'available_qty'  => 'required|numeric|min:0',
            'assigned_qty'   => 'required|numeric|min:0',
            'damaged_qty'    => 'required|numeric|min:0',
            'lost_qty'       => 'required|numeric|min:0',
            'replaced_qty'   => 'required|numeric|min:0',
            'price_per_unit' => 'required|numeric|min:0',
            'total_price'    => 'required|numeric|min:0',
            'note'           => 'nullable|string|max:255'
        ]);

        $user = Auth::user();
        $stock = KitStock::findOrFail($id);
        $stock->update($request->all());
        KitLog::create([
            'b_id'           => $user->emp_b_id,
            'kit_id'         => $stock->kit_id,
            'action_type'    => KitLog::UPDATE,
            'reference_id'   => $stock->id,
            'opening_qty'    => $stock->opening_qty,
            'available_qty'  => $stock->available_qty,
            'assigned_qty'   => $stock->assigned_qty,
            'qty'            => $request->opening_qty,
            'damaged_qty'    => $stock->damaged_qty,
            'lost_qty'       => $stock->lost_qty,
            'replaced_qty'   => $stock->replaced_qty,
            'price_per_unit' => $stock->price_per_unit,
            'total_price'    => $stock->total_price,
            'note'           => $request->note,
            'action_by'      => $user->emp_id,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Stock updated successfully.'
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();

        // Check if related kit item exists
        $kitItem = UniformItem::where('uit_b_id', $user->emp_b_id)
            ->where('uit_material_id', $id)
            ->first();

        if ($kitItem) {
            return response()->json([
                'status'  => false,
                'message' => 'This material is assined. You cannot delete it.'
            ], 422); // Validation error
        }

        // Find Stock
        $stock = KitStock::findOrFail($id);

        // Log delete
        KitLog::create([
            'b_id'           => $stock->b_id,
            'kit_id'         => $stock->kit_id,
            'action_type'    => KitLog::DELETE,
            'reference_id'   => $stock->id,
            'opening_qty'    => $stock->opening_qty,
            'available_qty'  => $stock->available_qty,
            'assigned_qty'   => $stock->assigned_qty,
            'damaged_qty'    => $stock->damaged_qty,
            'lost_qty'       => $stock->lost_qty,
            'replaced_qty'   => $stock->replaced_qty,
            'price_per_unit' => $stock->price_per_unit,
            'total_price'    => $stock->total_price,
            'note'           => 'Deleted stock',
            'action_by'      => $user->emp_id,
        ]);

        // Delete Stock
        $stock->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Deleted successfully.'
        ]);
    }
}
