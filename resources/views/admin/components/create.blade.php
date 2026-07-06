@extends('admin.layout.master')

@section('title', 'Create Component')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Create New Component</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('components.store') }}" id="componentForm">
                    @csrf
                    
                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Component Name</label>
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

                    <!-- Component Fields -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-primary mb-0">Component Fields</h6>
                            <button type="button" id="add-field" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-plus me-1"></i>
                                Add Field
                            </button>
                        </div>
                        <div id="fields-container">
                            <!-- Fields will be added here -->
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('components.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Component</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Field Template (Hidden) -->
<template id="field-template">
    <div class="field-row mb-4 p-3 border rounded">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="text-secondary mb-0">Field <span class="field-number"></span></h6>
            <button type="button" class="btn btn-outline-danger btn-sm remove-field">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Field Name</label>
                    <input type="text" name="fields[INDEX][name]" class="form-control field-name" 
                           placeholder="e.g., Brand, Model, Serial Number" required>
                </div>
            </div>
            <div class="col-md-4">
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
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Required</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="fields[INDEX][is_required]" class="form-check-input" value="1" checked>
                        <label class="form-check-label">This field is required</label>
                    </div>
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
    </div>
</template>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let fieldIndex = 0;

    // Add field
    $('#add-field').click(function() {
        addField();
    });

    // Add initial field
    addField();

    function addField() {
        const template = $('#field-template').html();
        const fieldHtml = template.replace(/INDEX/g, fieldIndex);
        $('#fields-container').append(fieldHtml);
        
        updateFieldNumbers();
        fieldIndex++;
    }

    // Remove field
    $(document).on('click', '.remove-field', function() {
        $(this).closest('.field-row').remove();
        updateFieldNumbers();
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

    // Update field numbers
    function updateFieldNumbers() {
        $('.field-row').each(function(index) {
            $(this).find('.field-number').text(index + 1);
        });
    }
});
</script>
@endpush