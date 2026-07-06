<?php

namespace App\Http\Controllers;

use App\Models\AssetType;
use App\Models\AssetTypeField;
use App\Models\Component;
use App\Models\FieldType;
use App\Models\DropdownOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AssetTypeController extends Controller
{

    protected $user;

    private $database;

    public function __construct()
    {
        $this->user = Auth::user();
        //$this->database = FirebaseService::connect();
    }


    public function index()
    {
        $assetTypes = AssetType::with(['fields.fieldType', 'components.fields.fieldType'])->paginate(15);
        return view('admin.asset-types.index', compact('assetTypes'));
    }

    public function create()
    {
        $fieldTypes = FieldType::where('is_active', true)->get();
        $components = Component::where('is_active', true)->with(['fields.fieldType'])->get();
        $dropdownCategories = DropdownOption::getCategories();
        return view('admin.asset-types.create', compact('fieldTypes', 'components', 'dropdownCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:asset_types,name',
            'description' => 'nullable|string',
            'structure_type' => 'required|in:simple,component_based'
        ]);

        DB::transaction(function () use ($request) {
            $assetType = AssetType::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'structure_type' => $request->structure_type
            ]);


            if ($request->structure_type === 'simple') {
                if ($request->has('fields') && is_array($request->fields)) {
                    foreach ($request->fields as $index => $fieldData) {



                        if (!empty($fieldData['name']) && !empty($fieldData['field_type_id'])) {
                            AssetTypeField::create([
                                'asset_type_id' => $assetType->id,
                                'name' => $fieldData['name'],
                                'slug' => Str::slug($fieldData['name']),
                                'field_type_id' => $fieldData['field_type_id'],
                                // 'options_category' => $fieldData['options_source'] === 'category' ? ($fieldData['options_category'] ?? null) : null,
                                // 'dropdown_options' => $fieldData['options_source'] === 'custom' ? ($fieldData['dropdown_options'] ?? null) : null,
                                'validation_rules' => null,
                                'attributes' => isset($fieldData['attributes']) ? $fieldData['attributes'] : null,
                                'condition_field_slug' => $fieldData['condition_field_slug'] ?? null,
                                'condition_operator' => $fieldData['condition_operator'] ?? null,
                                'condition_value' => $fieldData['condition_value'] ?? null,
                                'sort_order' => $index + 1,
                                'is_required' => isset($fieldData['is_required']) && $fieldData['is_required'] === '1',
                                'is_active' => true
                            ]);
                        }
                    }
                }
            } else {
                // Handle component-based asset type
                if ($request->has('components') && is_array($request->components)) {
                    $componentIds = array_filter($request->components);
                    if (!empty($componentIds)) {
                        $components = Component::whereIn('id', $componentIds)->get();
                        $syncData = [];
                        foreach ($components as $index => $component) {
                            $syncData[$component->id] = [
                                'sort_order' => $index + 1,
                                'is_required' => true
                            ];
                        }
                        $assetType->components()->sync($syncData);
                    }
                }
            }
        });

        \Log::info('Asset Type created successfully');

        return redirect()->route('assets.stock')->with('success', 'Asset type created successfully!');
    }

    public function show(AssetType $assetType)
    {
        $assetType->load(['fields.fieldType', 'components.fields.fieldType']);
        return view('admin.asset-types.show', compact('assetType'));
        \Log::error('Asset Type creation failed', ['error' => $e->getMessage()]);
    }

    public function edit(AssetType $assetType)
    {
        $assetType->load(['fields.fieldType', 'components.fields.fieldType']);
        $fieldTypes = FieldType::where('is_active', true)->get();
        $components = Component::where('is_active', true)->with(['fields.fieldType'])->get();
        $dropdownCategories = DropdownOption::getCategories();
        return view('admin.asset-types.edit', compact('assetType', 'fieldTypes', 'components', 'dropdownCategories'));
    }

    public function update(Request $request, AssetType $assetType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:asset_types,name,' . $assetType->id,
            'description' => 'nullable|string',
            'structure_type' => 'required|in:simple,component_based'
        ]);

        try {
            DB::transaction(function () use ($request, $assetType) {
                $assetType->update([
                    'name' => $request->name,
                    'slug' => Str::slug($request->name),
                    'description' => $request->description,
                    'structure_type' => $request->structure_type
                ]);

                if ($request->structure_type === 'simple') {
                    // Delete existing fields and recreate
                    $assetType->fields()->delete();

                    if ($request->has('fields')) {
                        foreach ($request->fields as $index => $fieldData) {
                            if (!empty($fieldData['name']) && !empty($fieldData['field_type_id'])) {
                                AssetTypeField::create([
                                    'asset_type_id' => $assetType->id,
                                    'name' => $fieldData['name'],
                                    'slug' => Str::slug($fieldData['name']),
                                    'field_type_id' => $fieldData['field_type_id'],
                                    'options_category' => $fieldData['options_category'] ?? null,
                                    'dropdown_options' => $fieldData['dropdown_options'] ?? null,
                                    'validation_rules' => $fieldData['validation_rules'] ?? null,
                                    'attributes' => $fieldData['attributes'] ?? null,
                                    'condition_field_slug' => $fieldData['condition_field_slug'] ?? null,
                                    'condition_operator' => $fieldData['condition_operator'] ?? null,
                                    'condition_value' => $fieldData['condition_value'] ?? null,
                                    'sort_order' => $index + 1,
                                    'is_required' => isset($fieldData['is_required']) ? true : false,
                                    'is_active' => true
                                ]);
                            }
                        }
                    }
                } else {
                    // Update component relationships
                    if ($request->has('components')) {
                        $componentIds = $request->components;
                        $syncData = [];
                        foreach ($componentIds as $index => $componentId) {
                            $syncData[$componentId] = [
                                'sort_order' => $index + 1,
                                'is_required' => true
                            ];
                        }
                        $assetType->components()->sync($syncData);
                    } else {
                        $assetType->components()->detach();
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Asset type updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating asset type: ' . $e->getMessage()
            ], 422);
        }
    }

    public function destroy(AssetType $assetType)
    {
        try {
            if ($assetType->assets()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete asset type that has assets associated with it.'
                ], 422);
            }

            $assetType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Asset type deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting asset type: ' . $e->getMessage()
            ], 422);
        }
    }

    // API endpoints for AJAX operations
    public function apiIndex()
    {
        $assetTypes = AssetType::with(['fields.fieldType', 'components.fields.fieldType'])
            ->withCount(['assets', 'fields', 'components'])
            ->get();

        return response()->json($assetTypes);
    }

    public function apistore(Request $request)
    {

        // dd($request);
        $user = Auth::user();
        $b_id = $user->emp_b_id;

        $request->validate([
            'name'        => 'required|string|max:255',
            'brand_id'    => 'required|exists:assets_brands,br_id',
            'category_id' => 'required|exists:assets_categories,ac_id',
            'is_active'   => 'nullable|boolean',
        ]);

        $assetType = AssetType::create([
            'name'              => $request->name,
            'assets_type_b_id'  => $b_id,
            'brand_id'          => $request->brand_id,
            'description'          => $request->description,
            'category_id'       => $request->category_id,
            'is_active'         => $request->is_active ?? 1,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Asset Type created successfully.',
            'data'    => $assetType,
        ]);
    }
}