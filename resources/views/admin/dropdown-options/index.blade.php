@extends('admin.layout.master')

@section('title', 'Dropdown Options')

@section('header-actions')
    @can('create-asset-types')
    <a href="{{ route('dropdown-options.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>
        Add Category
    </a>
    @endcan
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Dropdown Options Management</h6>
        <small class="text-muted">Manage reusable dropdown option categories</small>
    </div>
    <div class="card-body">
        @if(count($optionsByCategory) > 0)
        <div class="row">
            @foreach($optionsByCategory as $category => $options)
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">{{ ucfirst(str_replace('_', ' ', $category)) }}</h6>
                        <div class="btn-group btn-group-sm">
                            @can('update-asset-types')
                            <a href="{{ route('dropdown-options.edit', $category) }}" class="btn btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            @can('delete-asset-types')
                            <form method="POST" action="{{ route('dropdown-options.destroy', $category) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" 
                                        onclick="return confirm('Are you sure you want to delete this category?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="badge bg-primary">{{ $options->count() }} options</span>
                        </div>
                        <div class="options-list">
                            @foreach($options->take(10) as $option)
                                <span class="badge bg-light text-dark me-1 mb-1">{{ $option->label }}</span>
                            @endforeach
                            @if($options->count() > 10)
                                <small class="text-muted d-block mt-2">... and {{ $options->count() - 10 }} more options</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-list-ul display-1 text-muted"></i>
            <h5 class="mt-3">No dropdown categories found</h5>
            <p class="text-muted">Create dropdown option categories for reuse in asset types and components.</p>
            @can('create-asset-types')
            <a href="{{ route('dropdown-options.create') }}" class="btn btn-primary">Add Category</a>
            @endcan
        </div>
        @endif
    </div>
</div>
@endsection