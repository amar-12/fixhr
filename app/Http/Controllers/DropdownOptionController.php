<?php

namespace App\Http\Controllers;

use App\Models\DropdownOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DropdownOptionController extends Controller
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
        $categories = DropdownOption::getCategories();
        $optionsByCategory = [];
        
        foreach ($categories as $category) {
            $optionsByCategory[$category] = DropdownOption::where('category', $category)
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get();
        }

        return view('admin.dropdown-options.index', compact('optionsByCategory'));
    }

    public function create()
    {
        $existingCategories = DropdownOption::getCategories();
        return view('admin.dropdown-options.create', compact('existingCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'options' => 'required|array|min:1',
            'options.*.value' => 'required|string|max:255',
            'options.*.label' => 'required|string|max:255'
        ]);

        foreach ($request->options as $index => $optionData) {
            DropdownOption::create([
                'category' => $request->category,
                'value' => $optionData['value'],
                'label' => $optionData['label'],
                'sort_order' => $index + 1
            ]);
        }

        return redirect()->route('dropdown-options.index')
                        ->with('success', 'Dropdown options created successfully!');
    }

    public function edit(string $category)
    {
        $options = DropdownOption::where('category', $category)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        if ($options->isEmpty()) {
            return redirect()->route('dropdown-options.index')
                           ->with('error', 'Category not found.');
        }
        return view('admin.dropdown-options.edit', compact('category', 'options'));
    }

    public function update(Request $request, string $category)
    {
        $request->validate([
            'options' => 'required|array|min:1',
            'options.*.value' => 'required|string|max:255',
            'options.*.label' => 'required|string|max:255'
        ]);

        // Delete existing options for this category
        DropdownOption::where('category', $category)->delete();

        // Create new options
        foreach ($request->options as $index => $optionData) {
            DropdownOption::create([
                'category' => $category,
                'value' => $optionData['value'],
                'label' => $optionData['label'],
                'sort_order' => $index + 1
            ]);
        }

        return redirect()->route('dropdown-options.index')
                        ->with('success', 'Dropdown options updated successfully!');
    }

    public function destroy(string $category)
    {
        DropdownOption::where('category', $category)->delete();

        return redirect()->route('dropdown-options.index')
                        ->with('success', 'Dropdown options deleted successfully!');
    }

    // API Methods for AJAX operations
    public function apiIndex()
    {
        $categories = DropdownOption::getCategories();
        $optionsByCategory = [];
        
        foreach ($categories as $category) {
            $optionsByCategory[$category] = DropdownOption::where('category', $category)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['value', 'label']);
        }
        
        return response()->json($optionsByCategory);
    }

    public function apiStore(Request $request)
    {
        \Log::info('Dropdown Option API Store called', $request->all());
        
        $request->validate([
            'category' => 'required|string|max:255',
            'options' => 'required|array|min:1',
            'options.*.value' => 'required|string|max:255',
            'options.*.label' => 'required|string|max:255'
        ]);

        try {
            foreach ($request->options as $index => $optionData) {
                DropdownOption::create([
                    'category' => $request->category,
                    'value' => $optionData['value'],
                    'label' => $optionData['label'],
                    'sort_order' => $index + 1
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully!',
                'data' => ['category' => $request->category, 'count' => count($request->options)]
            ]);

        } catch (\Exception $e) {
            \Log::error('Dropdown option creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error creating category: ' . $e->getMessage()
            ], 422);
        }
    }

    public function apiDestroy(string $category)
    {
        try {
            DropdownOption::where('category', $category)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting category: ' . $e->getMessage()
            ], 422);
        }
    }
}