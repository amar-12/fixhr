@extends('admin.layout.master')
@section('title', 'Leave Calendar')

@section('css')
    <style>
        .leave-details {
            font-size: 14px;
        }

        hr {
            border-top: 6px solid var(--primary);
        }

        table th:first-of-type,
        table th:nth-child(2) {
            width: auto !important;
        }

        .fc .fc-bg-event .fc-event-title {
            font-size: 1.25em !important;
            font-weight: bold !important;
        }

        .fc-daygrid-block-event .fc-event-title {
            font-size: 12px;
            font-weight: 500;
        }

        .dataTables_length .select2 {
            width: 60% !important;
        }

        .fc-h-event {
            cursor: pointer;
        }
    </style>
@endsection

@section('content')

    {{-- Breadcrumbs Start --}}
    <div class="p-0 my-1">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a class="text-white">Leave</a></li>
            <li class="active"><span><b>Leave Calendar</b></span></li>
        </ol>
    </div>
    {{-- Breadcrumbs End --}}

 @livewire('leave-calendar.leave-calendar',[$slug])

@endsection

