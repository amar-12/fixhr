@extends('admin.layout.master')

@section('title', 'Edit Dropdown Category')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Dropdown Category: {{ ucfirst(str_replace('_', ' ', $category)) }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('dropdown-options.update', $category) }}" id="dropdownForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category Name</label>
                        <input type="text" name="category" id="category" 
                               class="form-control" 
                               value="{{ $category }}" readonly>
                        <div class="form-text">Category name cannot be changed after creation</div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label">Options</label>
                            <button type="button" id="add-option" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-plus me-1"></i>
                                Add Option
                            </button>
                        </div>
                        <div id="options-container">
                            @foreach($options as $index => $option)
                            <div class="option-row mb-2">
                                <div class="row">
                                    <div class="col-md-5">
                                        <input type="text" name="options[{{ $index }}][value]" class="form-control" 
                                               value="{{ $option->value }}" placeholder="Value (e.g., dell)" required>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" name="options[{{ $index }}][label]" class="form-control" 
                                               value="{{ $option->label }}" placeholder="Label (e.g., Dell)" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-option">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('dropdown-options.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Option Template (Hidden) -->
<template id="option-template">
    <div class="option-row mb-2">
        <div class="row">
            <div class="col-md-5">
                <input type="text" name="options[INDEX][value]" class="form-control" 
                       placeholder="Value (e.g., dell)" required>
            </div>
            <div class="col-md-5">
                <input type="text" name="options[INDEX][label]" class="form-control" 
                       placeholder="Label (e.g., Dell)" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-sm remove-option">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let optionIndex = {{ $options->count() }};

    // Add option
    $('#add-option').click(function() {
        addOption();
    });

    function addOption() {
        const template = $('#option-template').html();
        const optionHtml = template.replace(/INDEX/g, optionIndex);
        $('#options-container').append(optionHtml);
        optionIndex++;
    }

    // Remove option
    $(document).on('click', '.remove-option', function() {
        if ($('.option-row').length > 1) {
            $(this).closest('.option-row').remove();
            updateOptionIndexes();
        } else {
            alert('At least one option is required.');
        }
    });

    // Update option indexes after removal
    function updateOptionIndexes() {
        $('.option-row').each(function(index) {
            $(this).find('input[name*="[value]"]').attr('name', `options[${index}][value]`);
            $(this).find('input[name*="[label]"]').attr('name', `options[${index}][label]`);
        });
        optionIndex = $('.option-row').length;
    }
});
</script>
@endpush