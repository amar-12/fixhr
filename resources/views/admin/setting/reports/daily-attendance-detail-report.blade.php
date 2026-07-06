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
            if ($slug == 'daily-attendance') {
                $title = 'Daily Attendance Report';
            }elseif($slug == 'monthly-attendance-detail') {
                $title = 'Monthly Attendance Detail Report';
            }
            
            elseif ($slug == 'yearly-summary') {
                $title = 'Yearly Summary Report';
            } elseif ($slug == 'monthly-attendance-basic') {
                $title = 'Monthly Attendance Report';
            } elseif ($slug == 'monthly-attendance-in-out') {
                $title = 'Monthly Attendance In-Out Report';
            } elseif ($slug == 'selfie-attendance') {
                $title = 'Selfie Attendance Report';
            } else {
                $title = 'Daily Attendance Detail Report';
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
                @if ($slug == 'yearly-summary')
                    @livewire('attendance-report.yearly-summary-report')
                @elseif($slug == 'monthly-attendance-basic' || $slug == 'monthly-attendance-in-out' || $slug == 'monthly-attendance-detail')
                    @livewire('attendance-report.monthly-attendance-report', ['slug' => $slug])
                @elseif($slug == 'selfie-attendance')
                    @livewire('attendance-report.selfie-attendance-report')
                @else
                    @livewire('attendance-report.daily-attendance-detail-report')
                @endif
            </div>
        </div>
    </div>
@endsection
