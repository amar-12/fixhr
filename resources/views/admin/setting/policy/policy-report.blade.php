@extends('admin.layout.master')
@section('title')
    {{ $title ?? '' }} Report
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
        @php
            $title = '';
            if ($slug == 'holiday-policy') {
                $title = 'Holiday Policy Report';
            } elseif ($slug == 'weekly-off-policy') {
                $title = 'Weekly Off Policy Report';
            }
        @endphp
        <div class=" p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('/admin/report/attendance-report') }}">Report</a></li>
                <li class="active"><span><b>{{ $title }}</b></span></li>
            </ol>
        </div>
        <div class="row pt-5">
            <div class="container-fluid bg-white" style="padding-bottom:500px;">
                @livewire('policy-report.business-policy-reports', ['slug' => $slug])
            </div>
        </div>
    </div>
@endsection

