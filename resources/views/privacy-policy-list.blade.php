@extends('layouts.master')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Privacy Policy</h3>
                </div>
                <div class="card-body">
                    @forelse($policies as $policy)
                        <div class="mb-4 p-3 border rounded bg-light">
                            <h5 class="mb-2">{{ $policy->pp_title }}</h5>
                            <p class="mb-0">{{ $policy->pp_description }}</p>
                        </div>
                    @empty
                        <div class="text-center">No privacy policies found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
