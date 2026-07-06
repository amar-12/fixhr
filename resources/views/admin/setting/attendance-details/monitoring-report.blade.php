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
            $title = '';
            if ($slug == 'late-coming') {
                $title = 'Late Coming Report';
            } elseif ($slug == 'early-going') {
                $title = 'Early Going Report';
            } elseif ($slug == 'half-day') {
                $title = 'Half Day Report';
            } elseif ($slug == 'present') {
                $title = 'Present Report';
            } elseif ($slug == 'missed-punch') {
                $title = 'Missed Punch Report';
            } elseif ($slug == 'absent') {
                $title = 'Absent Report';
            } elseif ($slug == 'holiday-present') {
                $title = 'Holiday Present Report';
            } elseif ($slug == 'weekly-off-present-detailed') {
                $title = 'Weekly Off Present Detailed Report';
            } elseif ($slug == 'weekly-off-present-summary') {
                $title = 'Weekly Off Present Summary Report';
            } elseif ($slug == 'comp-off') {
                $title = 'Comp Off Report';
            } elseif ($slug == 'continuous-absent') {
                $title = 'Continuous Absent Report';
            }
            elseif ($slug == 'continuous-leave') {
                $title = 'Continuous Leave Report';
            } else {
                $title = 'Monitoring Report';
            }
        @endphp
        <div class=" p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('/admin/report/attendance-report') }}">Report</a></li>
                <li class="active"><span><b>{{ $title }}</b></span></li>
            </ol>
        </div>
        <!-- END ROW -->
        <!-- ROW -->
        @if ($slug == 'continuous-absent')
            <div class="row pt-5">
                <div class="container-fluid bg-white" style="padding-bottom:500px;">
                    @livewire('attendance-report.continuous-leave-absenteeism-report.absent-report', ['slug' => $slug])
                </div>
            </div>
        @elseif ($slug == 'continuous-leave')
            <div class="row pt-5">
                <div class="container-fluid bg-white" style="padding-bottom:500px;">
                    @livewire('attendance-report.continuous-leave-absenteeism-report.leave-report')
                </div>
            </div>
        @else
            <div class="row pt-5">
                <div class="container-fluid bg-white" style="padding-bottom:500px;">
                    @livewire('attendance-report.monitoring-report', ['slug' => $slug])
                </div>
            </div>
        @endif
    </div>
@endsection
