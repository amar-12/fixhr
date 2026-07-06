@extends('admin.layout.master')

@section('title', 'Asset Types')

@section('header-actions')
    @can('create-asset-types')
    <a href="{{ route('asset-types.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>
        Add Asset Type
    </a>
    @endcan
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Asset Types Management</h6>
    </div>
    <div class="card-body">
        @if($assetTypes->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Fields/Components</th>
                        <th>Assets</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assetTypes as $assetType)
                    <tr>
                        <td>
                            <strong class="text-primary">{{ $assetType->name }}</strong>
                            @if($assetType->description)
                                <br><small class="text-muted">{{ $assetType->description }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $assetType->isComponentBased() ? 'bg-info' : 'bg-secondary' }}">
                                {{ $assetType->isComponentBased() ? 'Component-Based' : 'Simple' }}
                            </span>
                        </td>
                        <td>
                            @if($assetType->isComponentBased())
                                <span class="badge bg-primary">{{ $assetType->components->count() }} Components</span>
                                @foreach($assetType->components->take(3) as $component)
                                    <small class="d-block text-muted">{{ $component->name }}</small>
                                @endforeach
                                @if($assetType->components->count() > 3)
                                    <small class="text-muted">... and {{ $assetType->components->count() - 3 }} more</small>
                                @endif
                            @else
                                <span class="badge bg-secondary">{{ $assetType->fields->count() }} Fields</span>
                                @foreach($assetType->fields->take(3) as $field)
                                    <small class="d-block text-muted">{{ $field->name }}</small>
                                @endforeach
                                @if($assetType->fields->count() > 3)
                                    <small class="text-muted">... and {{ $assetType->fields->count() - 3 }} more</small>
                                @endif
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $assetType->assets->count() }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $assetType->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $assetType->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('asset-types.show', $assetType) }}" class="btn btn-outline-info" title="View Asset Type Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('update-asset-types')
                                <a href="{{ route('asset-types.edit', $assetType) }}" class="btn btn-outline-primary" title="Edit Asset Type">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                @can('delete-asset-types')
                                @if($assetType->assets->count() == 0)
                                <form method="POST" action="{{ route('asset-types.destroy', $assetType) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" 
                                            onclick="return confirm('Are you sure you want to delete this asset type?')"
                                            title="Delete Asset Type">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{ $assetTypes->links() }}
        @else
        <div class="text-center py-5">
            <i class="bi bi-tags display-1 text-muted"></i>
            <h5 class="mt-3">No asset types found</h5>
            <p class="text-muted">Create asset types to categorize your assets.</p>
            @can('create-asset-types')
            <a href="{{ route('asset-types.create') }}" class="btn btn-primary">Add Asset Type</a>
            @endcan
        </div>
        @endif
    </div>
</div>
@endsection