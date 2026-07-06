@extends('admin.layout.master')

@section('title', 'Asset Type Details')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $assetType->name }}</h5>
                <div>
                    <span class="badge {{ $assetType->isComponentBased() ? 'bg-info' : 'bg-secondary' }} me-2">
                        {{ $assetType->isComponentBased() ? 'Component-Based' : 'Simple' }}
                    </span>
                    <span class="badge {{ $assetType->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $assetType->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                @if($assetType->description)
                <p class="text-muted mb-4">{{ $assetType->description }}</p>
                @endif

                @if($assetType->isComponentBased())
                    <h6 class="text-primary mb-3">Components ({{ $assetType->components->count() }})</h6>
                    @foreach($assetType->components as $component)
                    <div class="component-section mb-4 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-secondary mb-0">
                                {{ $component->name }}
                                @if($component->pivot->is_required)
                                    <span class="badge bg-warning text-dark">Required</span>
                                @endif
                            </h6>
                            <small class="text-muted">{{ $component->fields->count() }} fields</small>
                        </div>
                        
                        @if($component->description)
                        <p class="text-muted small mb-3">{{ $component->description }}</p>
                        @endif

                        <div class="row">
                            @foreach($component->fields as $field)
                            <div class="col-md-6 mb-3">
                                <div class="field-info p-2 bg-light rounded">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $field->name }}</strong>
                                            @if($field->is_required)
                                                <span class="text-danger">*</span>
                                            @endif
                                            <br>
                                            <small class="text-muted">{{ $field->fieldType->label }}</small>
                                            
                                            @if($field->hasCondition())
                                            <br>
                                            <small class="text-info">
                                                <i class="bi bi-arrow-right me-1"></i>
                                                Shows when {{ $field->condition_field_slug }} {{ $field->condition_operator }} "{{ $field->condition_value }}"
                                            </small>
                                            @endif
                                        </div>
                                        @if($field->fieldType->requires_options)
                                            <span class="badge bg-info">{{ count($field->getOptions()) }} options</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                 @else
                    <h6 class="text-primary mb-3">Fields ({{ $assetType->fields->count() }})</h6>
                    <div class="row">
                        @foreach($assetType->fields as $field)
                        <div class="col-md-6 mb-3">
                            <div class="field-info p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $field->name }}</strong>
                                        @if($field->is_required)
                                            <span class="text-danger">*</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ $field->fieldType->label }}</small>
                                        
                                        @if($field->hasCondition())
                                        <br>
                                        <small class="text-info">
                                            <i class="bi bi-arrow-right me-1"></i>
                                            Shows when {{ $field->condition_field_slug }} {{ $field->condition_operator }} "{{ $field->condition_value }}"
                                        </small>
                                        @endif
                                    </div>
                                    @if($field->fieldType->requires_options)
                                        <span class="badge bg-info">{{ count($field->getOptions()) }} options</span>
                                    @endif
                                </div>
                                
                                @if($field->fieldType->requires_options && count($field->getOptions()) > 0)
                                <div class="mt-2">
                                    <small class="text-muted">Options:</small>
                                    <div class="mt-1">
                                        @foreach(array_slice($field->getOptions(), 0, 5, true) as $value => $label)
                                            <span class="badge bg-light text-dark me-1">{{ $label }}</span>
                                        @endforeach
                                        @if(count($field->getOptions()) > 5)
                                            <span class="text-muted">... and {{ count($field->getOptions()) - 5 }} more</span>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-4">
                    <a href="{{ route('asset-types.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to Asset Types
                    </a>
                    @can('update-asset-types')
                    <a href="{{ route('asset-types.edit', $assetType) }}" class="btn btn-primary ms-2">
                        <i class="bi bi-pencil me-1"></i>
                        Edit Asset Type
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Assets Using This Type ({{ $assetType->assets->count() }})</h6>
            </div>
            <div class="card-body">
                @if($assetType->assets->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($assetType->assets->take(10) as $asset)
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    <a href="{{ route('assets.show', $asset) }}" class="text-decoration-none">
                                        {{ $asset->asset_tag }}
                                    </a>
                                </h6>
                                <p class="mb-1 small text-muted">{{ $asset->model_number }}</p>
                                @if($asset->employee)
                                <small class="text-muted">Assigned to {{ $asset->employee->name }}</small>
                                @endif
                            </div>
                            <span class="badge bg-{{ $asset->status === 'stock' ? 'primary' : ($asset->status === 'assigned' ? 'success' : 'warning') }}">
                                {{ ucfirst($asset->status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                    @if($assetType->assets->count() > 10)
                    <div class="list-group-item px-0 text-center">
                        <small class="text-muted">... and {{ $assetType->assets->count() - 10 }} more assets</small>
                    </div>
                    @endif
                </div>
                @else
                <p class="text-muted text-center">No assets using this type yet</p>
                @endif
            </div>
        </div>

        <!-- Form Preview -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Form Preview</h6>
            </div>
            <div class="card-body">
                <div id="form-preview">
                    <!-- Form preview will be rendered here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Render form preview
    const structure = {!! json_encode($assetType->getFormStructure()) !!};
    renderFormPreview(structure);

    function renderFormPreview(structure) {
        const container = $('#form-preview');
        let html = '';

        @if($assetType->isComponentBased())
            structure.forEach(function(component) {
                html += `<div class="mb-3">`;
                html += `<h6 class="text-primary">${component.name}</h6>`;
                
                component.fields.forEach(function(field) {
                    html += renderField(field);
                });
                
                html += `</div>`;
            });
        @else
            structure.forEach(function(field) {
                html += renderField(field);
            });
        @endif

        container.html(html);
        
        // Apply conditional logic
        applyConditionalLogic();
    }

    function renderField(field) {
        let html = `<div class="mb-2 field-preview" data-field-slug="${field.slug}">`;
        html += `<label class="form-label small">${field.name}`;
        if (field.is_required) html += ' <span class="text-danger">*</span>';
        html += `</label>`;

        if (field.html_type === 'select') {
            html += `<select class="form-select form-select-sm" disabled>`;
            html += `<option>Select ${field.name}</option>`;
            Object.entries(field.options).forEach(([value, label]) => {
                html += `<option value="${value}">${label}</option>`;
            });
            html += `</select>`;
        } else if (field.html_type === 'textarea') {
            html += `<textarea class="form-control form-control-sm" rows="2" disabled placeholder="${field.name}"></textarea>`;
        } else {
            html += `<input type="${field.html_type === 'input' ? 'text' : field.html_type}" class="form-control form-control-sm" disabled placeholder="${field.name}">`;
        }

        if (field.condition_field_slug) {
            html += `<small class="text-info d-block mt-1">
                <i class="bi bi-arrow-right me-1"></i>
                Shows when ${field.condition_field_slug} ${field.condition_operator} "${field.condition_value}"
            </small>`;
        }

        html += `</div>`;
        return html;
    }

    function applyConditionalLogic() {
        // This would implement the conditional logic for the preview
        // For now, just show all fields
    }
});
</script>
@endpush