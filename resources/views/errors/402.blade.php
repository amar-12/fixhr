@extends('errors::layout')

@section('title', __('Payment Required'))
@section('content')
    <div class="container text-center  relative">
        <div class="display-1 mb-5 font-weight-semibold">402</div>
        <h1 class="h3  mb-3 font-weight-semibold">Payment Required</h1>
        <p class="h5 font-weight-normal mb-7 leading-normal text-muted">You may have mistyped the address or the page may have moved.</p>
        <button class="btn btn-outline-primary" onclick="window.history.go(-1); return false;"><i class="fe fe-arrow-left-circle me-1"></i>Go Back</button>
    </div>
@endsection
