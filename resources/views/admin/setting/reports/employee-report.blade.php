@extends('admin.layout.master')
@section('title')
    Employee Report
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
            // $title = 'Employee Report';
            if ($slug == 'employee-detail') {
                $title = 'Employee Detail Report';
            } elseif ($slug == 'employee-birthday') {
                $title = 'Employee Birthday Report';
            } elseif ($slug == 'employee-joining') {
                $title = 'Employee Joining Report';
            }
        @endphp
        <div class=" p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('/admin/report/attendance-report') }}">Report</a></li>
                <li class="active"><span><b>{{ $title ?? '' }}</b></span></li>
            </ol>
        </div>
        <div class="row pt-5">
            <div class="container-fluid bg-white" style="padding-bottom:500px;">
                @if ($slug == 'employee-detail')
                    @livewire('employee-report.employee-detail-report')
                @elseif($slug == 'employee-birthday' || $slug == 'employee-joining')
                    @livewire('employee-report.employee-birth-day-report', ['slug' => $slug])
                @endif
            </div>
        </div>
    </div>
@endsection
