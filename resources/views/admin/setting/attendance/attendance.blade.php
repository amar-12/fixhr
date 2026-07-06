@extends('admin.layout.master')
{{-- @extends('admin.setting.setting') --}}
@section('title')
    Attendence Settings
@endsection

@section('content')
    <div class=" p-0 my-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('/admin/settings/attendance') }}">Settings</a></li>
            <li class="active"><span><b>Attendance Settings</b></span></li>
        </ol>
    </div>
    <div class="">
        <h5 class="text-muted">Create and Update Your Attendance Settings</h5>
    </div>

    <div class="row row-sm">
        <x-card-item icon="fa fa-building-o" title="Attendance Policy" count="{{ $attendancePolicy }}" description="Attendance Policy Created"
            url="{{ url('admin/settings/attendance/attendance-policies') }}" />

        <x-card-item icon="fa fa-star-o" title="Shift Policy" count="{{ $attendanceShiftType }}" description="Shift Policy Created"
            url="{{ url('admin/settings/attendance/attendance-shift-type') }}" />

        <x-card-item icon="fa fa-home" title="Holiday Policy" count="{{ $holidayPolicy }}" description="Holiday Policy Created"
            url="{{ url('admin/settings/attendance/holiday-policy') }}" />

        <x-card-item icon="fa fa-calendar" title="Leave Policy" count="{{ $attendanceLeavePolicy }}" description="Leave Policy Created"
            url="{{ url('admin/settings/attendance/leave-policy') }}" />

        <x-card-item icon="fa fa-calendar" title="Weekly Policy" count="{{ $weeklyPolicy }}" description="Weekly Policy Created"
            url="{{ route('weekly-policy.index') }}" />

        <x-card-item icon="fa fa-gear" title="Automation Rules" count="" description="Track Late Entry, Early Out, Overtime, and Breaks"
            url="{{ route('automation-rules.index') }}" />

        <x-card-item icon="fa fa-gear" title="Comp Off Rules" count="" description="Define Comp Off Rules and Settings"
            url="{{ route('compoff-policy.index') }}" />

        <x-card-item icon="fa fa-clock-o" title="Overtime Rules" count="" description="Define Overtime Rules and Settings" url="{{ route('overtime-policy.index') }}" />

        <x-card-item icon="fa fa-calendar" title="Earned Leave" count="" description="Define Earned Leave Settings" url="{{ route('el-policy.index') }}" />
    </div>
@endsection
