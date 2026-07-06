@extends('admin.layout.master')

@section('title', 'Create Asset Type')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Create New Asset Type</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('asset-types.store') }}" id="assetTypeForm">
                    @csrf
                    
                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Asset Type Name</label>
                                <input type="text" name="name" id="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <input type="text" name="description" id="description" 
                                       class="form-control @error('description') is-invalid @enderror" 
                                       value="{{ old('description') }}" placeholder="Optional description">
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Structure Type Selection -->
                    <div class="mb-4">
                        <label class="form-label">Asset Type Structure</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check p-3 border rounded">
                                    <input class="form-check-input" type="radio" name="structure_type" id="simple" value="simple" checked>
                                    <label class="form-check-label" for="simple">
                                        <strong>Simple Asset</strong><br>
                                        <small class="text-muted">Single asset with custom specification fields</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check p-3 border rounded">
                                    <input class="form-check-input" type="radio" name="structure_type" id="component_based" value="component_based">
                                    <label class="form-check-label" for="component_based">
                                        <strong>Component-Based Asset</strong><br>
                                        <small class="text-muted">Asset made up of multiple components (like Desktop Set)</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Simple Asset Fields -->
                    <div id="simple-fields" class="structure-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-primary mb-0">Specification Fields</h6>
                            <button type="button" id="add-simple-field" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-plus me-1"></i>
                                Add Field
                            </button>
                        </div>
                        <div id="simple-fields-container">
                            <!-- Simple fields will be added here -->
                        </div>
                    </div>

                    <!-- Component-Based Asset Fields -->
                    <div id="component-fields" class="structure-section" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-primary mb-0">Components Selection</h6>
                        </div>
                        <div class="row">
                            @foreach($components as $component)
                            <div class="col-md-4 mb-3">
                                <div class="form-check p-3 border rounded">
                                    <input class="form-check-input" type="checkbox" name="components[]" 
                                           id="component_{{ $component->id }}" value="{{ $component->id }}">
                                    <label class="form-check-label" for="component_{{ $component->id }}">
                                        <strong>{{ $component->name }}</strong><br>
                                        <small class="text-muted">{{ $component->description }}</small><br>
                                        <small class="text-info">{{ $component->fields->count() }} fields</small>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @if($components->count() == 0)
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No components available. <a href="{{ route('components.create') }}">Create components first</a>.
                        </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('asset-types.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Asset Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Field Template (Hidden) -->
<template id="simple-field-template">
    <div class="field-row mb-4 p-3 border rounded">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="text-secondary mb-0">Field <span class="field-number"></span></h6>
            <button type="button" class="btn btn-outline-danger btn-sm remove-field">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Field Name</label>
                    <input type="text" name="fields[INDEX][name]" class="form-control field-name" 
                           placeholder="e.g., Brand, Model, Serial Number" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Field Type</label>
                    <select name="fields[INDEX][field_type_id]" class="form-select field-type-select" required>
                        <option value="">Select Type</option>
                        @foreach($fieldTypes as $type)
                        <option value="{{ $type->id }}" data-requires-options="{{ $type->requires_options ? 'true' : 'false' }}">
                            {{ $type->label }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Required</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="fields[INDEX][is_required]" class="form-check-input" value="1" checked>
                        <label class="form-check-label">This field is required</label>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Placeholder</label>
                    <input type="text" name="fields[INDEX][attributes][placeholder]" class="form-control" 
                           placeholder="Enter placeholder text">
                </div>
            </div>
        </div>

        <!-- Dropdown Options Section -->
        <div class="dropdown-options-section" style="display: none;">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Options Source</label>
                        <select name="fields[INDEX][options_source]" class="form-select options-source-select">
                            <option value="custom">Custom Options</option>
                            <option value="category">Use Existing Category</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 category-select-section" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="fields[INDEX][options_category]" class="form-select">
                            <option value="">Select Category</option>
                            @foreach($dropdownCategories as $category)
                            <option value="{{ $category }}">{{ ucfirst(str_replace('_', ' ', $category)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="custom-options-section">
                <label class="form-label">Custom Options</label>
                <div class="options-container">
                    <div class="option-row mb-2">
                        <div class="row">
                            <div class="col-md-5">
                                <input type="text" name="fields[INDEX][dropdown_options][0][value]" 
                                       class="form-control" placeholder="Value (e.g., dell)">
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="fields[INDEX][dropdown_options][0][label]" 
                                       class="form-control" placeholder="Label (e.g., Dell)">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-option">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-success btn-sm add-option">
                    <i class="bi bi-plus me-1"></i>
                    Add Option
                </button>
            </div>
        </div>

        <!-- Conditional Logic Section -->
        <div class="conditional-logic-section mt-3">
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input enable-condition" id="enable_condition_INDEX">
                <label class="form-check-label" for="enable_condition_INDEX">
                    <strong>Enable Conditional Logic</strong>
                    <small class="text-muted d-block">Show this field only when another field matches a condition</small>
                </label>
            </div>
            
            <div class="condition-settings" style="display: none;">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Depends on Field</label>
                            <select name="fields[INDEX][condition_field_slug]" class="form-select condition-field-select">
                                <option value="">Select Field</option>
                                <!-- Will be populated dynamically -->
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Operator</label>
                            <select name="fields[INDEX][condition_operator]" class="form-select">
                                <option value="==">=== (equals)</option>
                                <option value="!=">=== (not equals)</option>
                                <option value="contains">contains</option>
                                <option value="not_contains">does not contain</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Value</label>
                            <input type="text" name="fields[INDEX][condition_value]" class="form-control" 
                                   placeholder="Value to match">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let fieldIndex = 0;

    // Toggle between simple and component-based structure
    $('input[name="structure_type"]').change(function() {
        if ($(this).val() === 'simple') {
            $('#simple-fields').show();
            $('#component-fields').hide();
        } else {
            $('#simple-fields').hide();
            $('#component-fields').show();
        }
    });

    // Add simple field
    $('#add-simple-field').click(function() {
        addSimpleField();
    });

    // Add initial field
    addSimpleField();

    function addSimpleField() {
        const template = $('#simple-field-template').html();
        const fieldHtml = template.replace(/INDEX/g, fieldIndex);
        $('#simple-fields-container').append(fieldHtml);
        
        updateFieldNumbers();
        updateConditionalFieldOptions();
        fieldIndex++;
    }

    // Remove field
    $(document).on('click', '.remove-field', function() {
        $(this).closest('.field-row').remove();
        updateFieldNumbers();
        updateConditionalFieldOptions();
    });

    // Handle field type changes
    $(document).on('change', '.field-type-select', function() {
        const requiresOptions = $(this).find('option:selected').data('requires-options');
        const fieldRow = $(this).closest('.field-row');
        const dropdownSection = fieldRow.find('.dropdown-options-section');
        
        if (requiresOptions) {
            dropdownSection.show();
        } else {
            dropdownSection.hide();
        }
    });

    // Handle options source changes
    $(document).on('change', '.options-source-select', function() {
        const fieldRow = $(this).closest('.field-row');
        const categorySection = fieldRow.find('.category-select-section');
        const customSection = fieldRow.find('.custom-options-section');
        
        if ($(this).val() === 'category') {
            categorySection.show();
            customSection.hide();
        } else {
            categorySection.hide();
            customSection.show();
        }
    });

    // Add option
    $(document).on('click', '.add-option', function() {
        const container = $(this).siblings('.options-container');
        const fieldRow = $(this).closest('.field-row');
        const fieldIndexMatch = fieldRow.find('input[name*="fields["]').attr('name').match(/fields\[(\d+)\]/);
        const currentFieldIndex = fieldIndexMatch ? fieldIndexMatch[1] : 0;
        const optionIndex = container.find('.option-row').length;
        
        const optionHtml = `
            <div class="option-row mb-2">
                <div class="row">
                    <div class="col-md-5">
                        <input type="text" name="fields[${currentFieldIndex}][dropdown_options][${optionIndex}][value]" 
                               class="form-control" placeholder="Value (e.g., dell)">
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="fields[${currentFieldIndex}][dropdown_options][${optionIndex}][label]" 
                               class="form-control" placeholder="Label (e.g., Dell)">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-option">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.append(optionHtml);
    });

    // Remove option
    $(document).on('click', '.remove-option', function() {
        $(this).closest('.option-row').remove();
    });

    // Enable/disable conditional logic
    $(document).on('change', '.enable-condition', function() {
        const fieldRow = $(this).closest('.field-row');
        const conditionSettings = fieldRow.find('.condition-settings');
        
        if ($(this).is(':checked')) {
            conditionSettings.show();
        } else {
            conditionSettings.hide();
        }
    });

    // Update field numbers
    function updateFieldNumbers() {
        $('.field-row').each(function(index) {
            $(this).find('.field-number').text(index + 1);
        });
    }

    // Update conditional field options
    function updateConditionalFieldOptions() {
        const allFields = [];
        $('.field-row').each(function() {
            const fieldName = $(this).find('.field-name').val();
            if (fieldName) {
                const slug = fieldName.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
                allFields.push({
                    slug: slug,
                    name: fieldName
                });
            }
        });

        $('.condition-field-select').each(function() {
            const currentSelect = $(this);
            const currentValue = currentSelect.val();
            currentSelect.empty().append('<option value="">Select Field</option>');
            
            allFields.forEach(function(field) {
                currentSelect.append(`<option value="${field.slug}">${field.name}</option>`);
            });
            
            if (currentValue) {
                currentSelect.val(currentValue);
            }
        });
    }

    // Update conditional options when field names change
    $(document).on('input', '.field-name', function() {
        updateConditionalFieldOptions();
    });
});
</script>
@endpush