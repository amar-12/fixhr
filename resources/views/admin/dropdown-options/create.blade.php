@extends('admin.layout.master')

@section('title', 'Create Dropdown Category')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Create New Dropdown Category</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('dropdown-options.store') }}" id="dropdownForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category Name</label>
                        <input type="text" name="category" id="category" 
                               class="form-control @error('category') is-invalid @enderror" 
                               value="{{ old('category') }}" 
                               placeholder="e.g., computer_brand, monitor_size, etc." required>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Use lowercase with underscores (e.g., computer_brand)</div>
                    </div>

                    @if(count($existingCategories) > 0)
                    <div class="mb-3">
                        <label class="form-label">Existing Categories</label>
                        <div class="existing-categories">
                            @foreach($existingCategories as $existing)
                                <span class="badge bg-light text-dark me-1 mb-1">{{ $existing }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label">Options</label>
                            <button type="button" id="add-option" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-plus me-1"></i>
                                Add Option
                            </button>
                        </div>
                        <div id="options-container">
                            <!-- Options will be added here -->
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('dropdown-options.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Category</button>
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
    let optionIndex = 0;

    // Add option
    $('#add-option').click(function() {
        addOption();
    });

    // Add initial options
    addOption();
    addOption();

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
        } else {
            alert('At least one option is required.');
        }
    });
});
</script>
@endpush