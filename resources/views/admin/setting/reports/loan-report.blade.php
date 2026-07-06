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
            if ($slug == 'loan-register') {
                $title = 'Loan Register Report';
            } elseif ($slug == 'loan-statements') {
                $title = 'Loan Statements Report';
            } elseif ($slug == 'loan-projection') {
                $title = 'Loan Projection Report';
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
        <div class="row pt-5">
            <div class="container-fluid bg-white" style="padding-bottom:500px;">
                @if ($slug == 'loan-register')
                    @livewire('loan.loan-registration', ['slug' => $slug])
                @elseif($slug == 'loan-projection')
                    @livewire('loan.loan-projection', ['slug' => $slug])
                @else
                @livewire('loan.loan-statements')
                @endif
            </div>
        </div>
    </div>
@endsection
