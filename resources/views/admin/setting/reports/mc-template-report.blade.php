@extends('admin.layout.master')
@section('title')
    MC Template Report
@endsection
@section('css')
    <style>
        h5 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #007bff;
        }
    </style>
@endsection
@section('content')
    <div>
        <div class=" p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('/admin/report/attendance-report') }}">Report</a></li>
                <li class="active"><span><b>MC Template Report</b></span></li>
            </ol>
        </div>
        <div class="row pt-5">
            <div class="container-fluid bg-white" style="padding-bottom:500px;">
                @livewire('salary.mc-template-report')
            </div>
        </div>
    </div>
@endsection
