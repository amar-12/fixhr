@extends('admin.layout.master')
@section('title', 'Components')
@section('header-actions')
    @can('create-asset-types')
        <a href="{{ route('components.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>
            Add Component
        </a>
    @endcan
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Components Management</h6>
            <small class="text-muted">Reusable components for building complex asset types</small>
        </div>
        <div class="card-body">
            @if ($components->count() > 0)
                <div class="table-responsive">
                    <table class="table table-fixed">
                        <thead>
                            <tr>
                                <th class="text-center">Name</th>
                                <th class="text-center">Description</th>
                                <th class="text-center">Fields</th>
                                <th class="text-center">Used in Asset Types</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($components as $component)
                                <tr>
                                    <td class="text-center">
                                        <strong class="text-primary">{{ $component->name }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <small class="text-muted">{{ $component->description ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $component->fields->count() }} Fields</span>
                                        @foreach ($component->fields->take(3) as $field)
                                            <small class="d-block text-muted">{{ $field->name }}</small>
                                        @endforeach
                                        @if ($component->fields->count() > 3)
                                            <small class="text-muted">... and {{ $component->fields->count() - 3 }}
                                                more</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $component->assetTypes->count() }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $component->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $component->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('components.show', $component) }}"
                                                class="btn btn-outline-info" title="View Component Details">
                                                <i class="bi bi-eye fs-6"></i> <!-- Reduced font size of the eye icon -->
                                            </a>
                                            <a href="{{ route('components.edit', $component) }}"
                                                class="btn btn-outline-primary" title="Edit Component">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if ($component->assetTypes->count() == 0)
                                                <form method="POST" action="{{ route('components.destroy', $component) }}"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger"
                                                        onclick="return confirm('Are you sure you want to delete this component?')"
                                                        title="Delete Component">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $components->links() }}
            @else
                <div class="text-center py-5">
                    <i class="bi bi-gear display-1 text-muted"></i>
                    <h5 class="mt-3">No components found</h5>
                    <p class="text-muted">Create reusable components for building complex asset types.</p>
                    @can('create-asset-types')
                        <a href="{{ route('components.create') }}" class="btn btn-primary">Add Component</a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
@endsection
