@extends('admin.layout.master')

{{-- @extends('admin.setting.setting') --}}
@section('title')
Device Management
@endsection
@section('content')
<div class=" p-0 my-3">
    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
        <li><a href="{{ url('/admin/settings/attendance') }}">Settings</a></li>
        <li class="active"><span><b>Device Management</b></span></li>
    </ol>
</div>

@php
$records = isset($records) ? $records : [];
$business_id = isset($business_id) ? $business_id : null;
$device_sn = isset($device_sn) ? $device_sn : null;
@endphp

<div class="row row-sm">
    
    @if ($records || $business_id || $device_sn)
    {{-- @dd('device-management.device-attendance-logs'); --}}
    @livewire('device-management.device-attendance-logs', [
    'records' => $records ?? [],
    'business_id' => $business_id ?? null,
    'device_sn' => $device_sn ?? null,
    ])
    @else
    {{-- @dd('device-management.device'); --}}
    @livewire('device-management.device')
    @endif
</div>
@endsection
