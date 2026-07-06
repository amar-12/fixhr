@extends('errors::layout')

@section('title', __('Server Error'))
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-7 mx-auto d-block">
                <div class="card p-7 mb-0">
                    <div class="text-center">
                        <div class="fs-100  mb-5 text-primary h1">oops!</div>
                        <h1 class="h3  mb-3 font-weight-semibold">Error 500: Internal Server Error</h1>
                        <p class="h5 font-weight-normal mb-7 leading-normal">You may have mistyped the address or the page may have moved.</p>
                        <button class="btn btn-outline-primary" onclick="window.history.go(-1); return false;"><i class="fe fe-arrow-left-circle me-1"></i>Go Back</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
