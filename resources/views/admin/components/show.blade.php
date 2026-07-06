@extends('admin.layout.master')

@section('title', 'Component Details')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $component->name }}</h5>
                <span class="badge {{ $component->is_active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $component->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="card-body">
                @if($component->description)
                <p class="text-muted mb-4">{{ $component->description }}</p>
                @endif

                <h6 class="text-primary mb-3">Fields ({{ $component->fields->count() }})</h6>
                <div class="row">
                    @foreach($component->fields as $field)
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

                <div class="mt-4">
                    <a href="{{ route('components.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to Components
                    </a>
                    @can('update-asset-types')
                    <a href="{{ route('components.edit', $component) }}" class="btn btn-primary ms-2">
                        <i class="bi bi-pencil me-1"></i>
                        Edit Component
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Used in Asset Types ({{ $component->assetTypes->count() }})</h6>
            </div>
            <div class="card-body">
                @if($component->assetTypes->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($component->assetTypes as $assetType)
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    <a href="{{ route('asset-types.show', $assetType) }}" class="text-decoration-none">
                                        {{ $assetType->name }}
                                    </a>
                                </h6>
                                <small class="text-muted">{{ $assetType->description }}</small>
                            </div>
                            <span class="badge bg-{{ $assetType->is_active ? 'success' : 'secondary' }}">
                                {{ $assetType->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted text-center">Not used in any asset types yet</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection