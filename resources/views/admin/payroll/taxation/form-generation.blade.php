@extends('admin.layout.master')

@section('title', 'Form Generation - FixHR')

@section('css')
<style>
    .fg-form-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .fg-form-tab {
        padding: 8px 14px;
        border: 1px solid var(--ef-border, #e2e6ee);
        border-radius: 10px;
        background: var(--ef-bg, #fff);
        color: var(--ef-muted, #6b7080);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .fg-form-tab:hover {
        border-color: var(--ef-accent, #1877f2);
        color: var(--ef-accent, #1877f2);
    }

    .fg-form-tab.active {
        background: linear-gradient(135deg, rgba(24, 119, 242, 0.1), rgba(79, 124, 255, 0.12));
        border-color: rgba(79, 124, 255, 0.35);
        color: var(--ef-accent, #1877f2);
    }

    .fg-filter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .fg-selected-badge-wrap:empty { display: none; }
</style>
@endsection

@section('content')
<div class="page-header d-md-flex d-block">
    <div class="page-leftheader">
        <div class="py-0 bd-highlight">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="#">Payroll</a></li>
                <li><a href="#">Taxation</a></li>
                <li class="active"><span><b>Form Generation</b></span></li>
            </ol>
        </div>
    </div>
</div>

@include('admin.payroll.taxation.partials.form-generation-panel', [
    'standaloneForm' => false,
    'formTabs' => $formTabs,
    'activeForm' => $activeForm,
    'financialYears' => $financialYears,
    'selectedFYId' => $selectedFYId,
    'departments' => $departments,
])
@endsection

@section('script')
@include('admin.payroll.taxation.partials.form-generation-scripts', [
    'standaloneForm' => false,
    'formTabs' => $formTabs,
    'activeForm' => $activeForm,
    'employees' => $employees,
    'formReloadUrl' => route('tax.form-generation.index'),
])
@endsection
