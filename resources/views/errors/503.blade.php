@extends('errors::layout')

@section('title', __('Service Unavailable'))
@section('content')
    <div class="container text-center relative">
        <div class="display-1 text-blue mb-5 font-weight-semibold"> 5<i class="fa fa-frown-o"></i>3</div>
        <h1 class="h3  mb-3 font-weight-semibold text-blue-80">We're currently performing scheduled maintenance. Service unavailable due to maintenance. </h1>
        <p class="h5 font-weight-normal mb-7 leading-normal text-blue-50">We'll be back soon!.</p>
        <button class="btn btn-outline-primary" onclick="window.history.go(-1); return false;"><i class="fe fe-arrow-left-circle me-1"></i>Go Back</button>
    </div>
@endsection

