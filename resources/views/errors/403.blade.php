@extends('errors::layout')

@section('title', __('Forbidden'))
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-5">
                <img src="assets/images/png/error.png" alt="img" class="mt-7">
            </div>
            <div class="col-md-7 mt-6">
                <div class="display-1 text-primary  mb-2 font-weight-semibold"> 403</div>
                <h1 class="h3  mb-3 font-weight-semibold">Sorry, Forbidden Error, Requested Page not found!</h1>
                <p class="h5 font-weight-normal mb-7 leading-normal">You may have mistyped the address or the page may have moved.</p>
                <button class="btn btn-outline-primary" onclick="window.history.go(-1); return false;"><i class="fe fe-arrow-left-circle me-1"></i>Go Back</button>
            </div>
        </div>
    </div>
@endsection
