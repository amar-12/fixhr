<?php

namespace App\Http\Controllers;

use App\Models\Component;
use App\Models\ComponentField;
use App\Models\FieldType;
use App\Models\DropdownOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComponentController extends Controller
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
        $components = Component::with(['fields.fieldType'])->paginate(15);
        return view('admin.components.index', compact('components'));
    }

    public function create()
    {
        $fieldTypes = FieldType::where('is_active', true)->get();
        $dropdownCategories = DropdownOption::getCategories();
        return view('admin.components.create', compact('fieldTypes', 'dropdownCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string|max:255',
            'fields.*.field_type_id' => 'required|exists:field_types,id'
        ]);

        DB::transaction(function () use ($request) {
            $component = Component::create([
                'name' => $request->name,
                'description' => $request->description,
                'sort_order' => Component::max('sort_order') + 1
            ]);

            foreach ($request->fields as $index => $fieldData) {
                ComponentField::create([
                    'component_id' => $component->id,
                    'name' => $fieldData['name'],
                    'slug' => Str::slug($fieldData['name']),
                    'field_type_id' => $fieldData['field_type_id'],
                    'options_category' => $fieldData['options_category'] ?? null,
                    'dropdown_options' => $fieldData['dropdown_options'] ?? null,
                    'validation_rules' => $fieldData['validation_rules'] ?? null,
                    'attributes' => $fieldData['attributes'] ?? null,
                    // 'condition_field_slug' => $fieldData['condition_field_slug'] ?? null,
                    // 'condition_operator' => $fieldData['condition_operator'] ?? null,
                    // 'condition_value' => $fieldData['condition_value'] ?? null,
                    'sort_order' => $index + 1,
                    'is_required' => $fieldData['is_required'] ?? true,
                    'is_active' => true
                ]);
            }
        });

        return redirect()->route('components.index')->with('success', 'Component created successfully!');
    }


    public function show(Component $component)
    {
        $component->load(['fields.fieldType']);
        return view('admin.components.show', compact('component'));
    }

    public function edit(Component $component)
    {
        $component->load(['fields.fieldType']);
        $fieldTypes = FieldType::where('is_active', true)->get();
        $dropdownCategories = DropdownOption::getCategories();
        return view('admin.components.edit', compact('component', 'fieldTypes', 'dropdownCategories'));
    }

    public function update(Request $request, Component $component)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string|max:255',
            'fields.*.field_type_id' => 'required|exists:field_types,id'
        ]);

        DB::transaction(function () use ($request, $component) {
            $component->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description
            ]);

            // Delete existing fields and recreate
            $component->fields()->delete();

            foreach ($request->fields as $index => $fieldData) {
                ComponentField::create([
                    'component_id' => $component->id,
                    'name' => $fieldData['name'],
                    'slug' => Str::slug($fieldData['name']),
                    'field_type_id' => $fieldData['field_type_id'],
                    'options_category' => $fieldData['options_category'] ?? null,
                    'dropdown_options' => $fieldData['dropdown_options'] ?? null,
                    'validation_rules' => $fieldData['validation_rules'] ?? null,
                    'attributes' => $fieldData['attributes'] ?? null,
                    // 'condition_field_slug' => $fieldData['condition_field_slug'] ?? null,
                    // 'condition_operator' => $fieldData['condition_operator'] ?? null,
                    // 'condition_value' => $fieldData['condition_value'] ?? null,
                    'sort_order' => $index + 1,
                    'is_required' => $fieldData['is_required'] ?? true,
                    'is_active' => true
                ]);
            }
        });

        return redirect()->route('components.index')
            ->with('success', 'Component updated successfully!');
    }

    public function destroy(Component $component)
    {
        if ($component->assetTypes()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete component that is used in asset types.');
        }

        $component->delete();

        return redirect()->route('components.index')
            ->with('success', 'Component deleted successfully!');
    }

    // API Methods for AJAX operations
    public function apiIndex()
    {
        $components = Component::withCount('fields')->get();
        return response()->json($components);
    }

    public function apiStore(Request $request)
    {
        \Log::info('Component API Store called', $request->all());

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string|max:255',
            'fields.*.field_type_id' => 'required|exists:field_types,id'
        ]);

        try {
            $component = null;

            DB::transaction(function () use ($request) {
                $component = Component::create([
                    'name' => $request->name,
                    'slug' => Str::slug($request->name),
                    'description' => $request->description,
                    'sort_order' => Component::max('sort_order') + 1
                ]);

                foreach ($request->fields as $index => $fieldData) {
                    ComponentField::create([
                        'component_id' => $component->id,
                        'name' => $fieldData['name'],
                        'slug' => Str::slug($fieldData['name']),
                        'field_type_id' => $fieldData['field_type_id'],
                        'sort_order' => $index + 1,
                        'is_required' => true,
                        'is_active' => true
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Component created successfully!',
                'data' => $component
            ]);
        } catch (\Exception $e) {
            \Log::error('Component creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error creating component: ' . $e->getMessage()
            ], 422);
        }
    }

    public function apiDestroy(Component $component)
    {
        try {
            if ($component->assetTypes()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete component that is used in asset types.'
                ], 422);
            }

            $component->delete();

            return response()->json([
                'success' => true,
                'message' => 'Component deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting component: ' . $e->getMessage()
            ], 422);
        }
    }
}
