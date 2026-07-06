@extends('errors::layout')

@section('title', __('Bad Request'))
@section('content')
    <div class="container text-center">
        <div class="display-1 text-primary mb-5 font-weight-bold">400</div>
        <h1 class="h3  mb-3 font-weight-semibold">Bad Request Error!</h1>
        <p class="h5 font-weight-normal mb-7 leading-normal">You may have mistyped the address or the page may have moved.</p>
        <button class="btn btn-outline-primary" onclick="window.history.go(-1); return false;"><i class="fe fe-arrow-left-circle me-1"></i>Go Back</button>
    </div>
@endsection
