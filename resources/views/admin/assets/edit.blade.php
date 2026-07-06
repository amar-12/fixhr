@extends('admin.layout.master')

@section('title', 'Edit Asset')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Asset: {{ $asset->asset_tag }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('assets.update', $asset) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_tag" class="form-label">Asset Tag</label>
                                <input type="text" name="asset_tag" id="asset_tag" 
                                       class="form-control @error('asset_tag') is-invalid @enderror" 
                                       value="{{ old('asset_tag', $asset->asset_tag) }}" required>
                                @error('asset_tag')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_type_id" class="form-label">Asset Type</label>
                                <select name="asset_type_id" id="asset_type_id" class="form-select @error('asset_type_id') is-invalid @enderror" required>
                                    <option value="">Select Asset Type</option>
                                    @foreach($assetTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('asset_type_id', $asset->asset_type_id) == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('asset_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Basic Asset Fields (only for simple assets) -->
                    <div id="basic-asset-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="serial_number" class="form-label">Serial Number</label>
                                    <input type="text" name="serial_number" id="serial_number" 
                                           class="form-control @error('serial_number') is-invalid @enderror" 
                                           value="{{ old('serial_number', $asset->serial_number) }}">
                                    @error('serial_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="model_number" class="form-label">Model Number</label>
                                    <input type="text" name="model_number" id="model_number" 
                                           class="form-control @error('model_number') is-invalid @enderror" 
                                           value="{{ old('model_number', $asset->model_number) }}">
                                    @error('model_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Component-Based Asset Fields -->
                    <div id="component-asset-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="component_serial_number" class="form-label">Asset Set Serial Number</label>
                                    <input type="text" name="component_serial_number" id="component_serial_number" 
                                           class="form-control @error('component_serial_number') is-invalid @enderror" 
                                           placeholder="Overall serial number for this asset set"
                                           value="{{ old('component_serial_number', $asset->serial_number) }}">
                                    @error('component_serial_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="component_model_number" class="form-label">Asset Set Model</label>
                                    <input type="text" name="component_model_number" id="component_model_number" 
                                           class="form-control @error('component_model_number') is-invalid @enderror" 
                                           placeholder="Model identifier for this asset set"
                                           value="{{ old('component_model_number', $asset->model_number) }}">
                                    @error('component_model_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Fields Container -->
                    <div id="dynamic-fields-container">
                        <!-- Dynamic fields will be loaded here -->
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $asset->notes) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('assets.show', $asset) }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Asset types data from server
    const assetTypesData = {!! json_encode($assetTypes->keyBy('id')->map(function($type) {
        return [
            'name' => $type->name,
            'required_fields' => $type->required_fields ?? [],
            'components' => $type->components ?? [],
            'is_component_based' => !empty($type->components)
        ];
    })) !!};
    
    // Current asset specifications
    const currentSpecs = {!! json_encode($asset->specifications ?? []) !!};
    
    $('#asset_type_id').change(function() {
        const selectedTypeId = $(this).val();
        const container = $('#dynamic-fields-container');
        const basicFields = $('#basic-asset-fields');
        const componentFields = $('#component-asset-fields');
        
        // Clear previous content
        container.empty();
        basicFields.hide();
        componentFields.hide();
        
        if (!selectedTypeId || !assetTypesData[selectedTypeId]) {
            return;
        }
        
        const assetTypeData = assetTypesData[selectedTypeId];
        const isComponentBased = assetTypeData.is_component_based;
        
        if (isComponentBased) {
            // Show component fields for component-based assets
            componentFields.show();
            
            const components = assetTypeData.components;
            
            if (components && components.length > 0) {
                components.forEach(function(component, componentIndex) {
                    let componentHtml = '<div class="component-section mb-4 p-3 border rounded">';
                    componentHtml += '<h6 class="text-primary mb-3">' + component.name + '</h6>';
                    componentHtml += '<div class="row">';
                    
                    if (component.fields && component.fields.length > 0) {
                        component.fields.forEach(function(field) {
                            const fieldName = field.name.toLowerCase().replace(/\s+/g, '_');
                            const componentName = component.name.toLowerCase().replace(/\s+/g, '_');
                            const specKey = componentName + '_' + fieldName;
                            const fullFieldName = 'components[' + componentIndex + '][' + fieldName + ']';
                            const currentValue = currentSpecs[specKey] || '';
                            
                            componentHtml += '<div class="col-md-4">';
                            componentHtml += '<div class="mb-3">';
                            componentHtml += '<label class="form-label">' + field.name + '</label>';
                            
                            if (field.type === 'dropdown' && field.options && field.options.length > 0) {
                                componentHtml += '<select name="' + fullFieldName + '" class="form-select" required>';
                                componentHtml += '<option value="">Select ' + field.name + '</option>';
                                field.options.forEach(function(option) {
                                    const selected = currentValue === option ? 'selected' : '';
                                    componentHtml += '<option value="' + option + '" ' + selected + '>' + option + '</option>';
                                });
                                componentHtml += '</select>';
                            } else {
                                componentHtml += '<input type="text" name="' + fullFieldName + '" class="form-control" value="' + currentValue + '" required>';
                            }
                            
                            componentHtml += '</div>';
                            componentHtml += '</div>';
                        });
                    }
                    
                    componentHtml += '</div>';
                    componentHtml += '</div>';
                    
                    container.append(componentHtml);
                });
            }
        } else {
            // Show basic fields for simple assets
            basicFields.show();
            
            // Add dynamic specification fields
            const fields = assetTypeData.required_fields;
            
            if (fields && fields.length > 0) {
                let fieldsHtml = '<div class="row"><div class="col-md-12"><h6 class="text-primary mb-3">Specifications</h6></div></div><div class="row">';
                
                fields.forEach(function(field, index) {
                    const fieldName = (typeof field === 'object' ? field.name : field).toLowerCase().replace(/\s+/g, '_');
                    const fieldLabel = typeof field === 'object' ? field.name : field;
                    const fieldType = typeof field === 'object' ? field.type : 'text';
                    const fieldOptions = typeof field === 'object' ? field.options : [];
                    const currentValue = currentSpecs[fieldName] || '';
                    
                    if (index > 0 && index % 2 === 0) {
                        fieldsHtml += '</div><div class="row">';
                    }
                    
                    fieldsHtml += '<div class="col-md-6">';
                    fieldsHtml += '<div class="mb-3">';
                    fieldsHtml += '<label for="specifications_' + fieldName + '" class="form-label">' + fieldLabel + '</label>';
                    
                    if (fieldType === 'dropdown' && fieldOptions && fieldOptions.length > 0) {
                        fieldsHtml += '<select name="specifications[' + fieldName + ']" id="specifications_' + fieldName + '" class="form-select" required>';
                        fieldsHtml += '<option value="">Select ' + fieldLabel + '</option>';
                        fieldOptions.forEach(function(option) {
                            const selected = currentValue === option ? 'selected' : '';
                            fieldsHtml += '<option value="' + option + '" ' + selected + '>' + option + '</option>';
                        });
                        fieldsHtml += '</select>';
                    } else {
                        fieldsHtml += '<input type="text" name="specifications[' + fieldName + ']" id="specifications_' + fieldName + '" class="form-control" value="' + currentValue + '" required>';
                    }
                    
                    fieldsHtml += '</div>';
                    fieldsHtml += '</div>';
                });
                
                fieldsHtml += '</div>';
                container.html(fieldsHtml);
            }
        }
    });
});
    // Trigger change event on page load to show current asset fields
@endpush