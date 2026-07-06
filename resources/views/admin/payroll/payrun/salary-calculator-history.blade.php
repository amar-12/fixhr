@extends('admin.layout.master')

@section('title', $title)

@section('css')
<style>
    .salary-history-page .history-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }

    .salary-history-page .employee-meta {
        color: #64748b;
        font-size: 12px;
    }

    .salary-history-page .history-timeline {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding-left: 34px;
    }

    .salary-history-page .history-timeline::before {
        content: "";
        position: absolute;
        left: 10px;
        top: 4px;
        bottom: 4px;
        width: 2px;
        background: #bfdbfe;
    }

    .salary-history-page .history-timeline-item {
        position: relative;
        display: block;
    }

    .salary-history-page .history-timeline-dot {
        position: absolute;
        display: block;
        left: -30px;
        top: 18px;
        width: 16px;
        height: 16px;
        border-radius: 999px;
        background: #2563eb;
        border: 3px solid #eff6ff;
        box-shadow: 0 0 0 2px #bfdbfe;
        z-index: 1;
    }

    .salary-history-page .history-timeline-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .salary-history-page .history-timeline-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    }

    .salary-history-page .history-timeline-card.left {
        grid-column: auto;
    }

    .salary-history-page .history-timeline-card.right {
        grid-column: auto;
    }

    .salary-history-page .history-tab-header {
        padding: 12px 14px;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
    }

    .salary-history-page .history-tab-body {
        padding: 12px;
    }

    .salary-history-page .history-metric-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .salary-history-page .history-metric {
        border: 1px solid #edf2f7;
        border-radius: 10px;
        padding: 10px;
        background: #ffffff;
    }

    .salary-history-page .history-metric-label {
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .salary-history-page .history-metric-value {
        color: #0f172a;
        font-size: 14px;
        font-weight: 800;
    }

    .salary-history-page .history-tab-title {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
    }

    .salary-history-page .history-tab-subtitle,
    .salary-history-page .remark-text,
    .salary-history-page .mini-label,
    .salary-history-page .mini-value {
        font-size: 12px;
    }

    .salary-history-page .mini-change-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
    }

    .salary-history-page .mini-change-card {
        border: 1px solid #edf2f7;
        border-radius: 10px;
        background: #ffffff;
        padding: 10px;
    }

    .salary-history-page .mini-label {
        color: #64748b;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .salary-history-page .mini-values {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        color: #334155;
        margin-bottom: 4px;
    }

    .salary-history-page .mini-diff {
        font-size: 12px;
        text-align: right;
    }

    .salary-history-page .detail-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .salary-history-page .detail-table th {
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        background: #f8fafc;
    }

    .salary-history-page .detail-table th,
    .salary-history-page .detail-table td {
        padding: 9px 10px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .salary-history-page .diff-up {
        color: #16a34a;
        font-weight: 700;
    }

    .salary-history-page .diff-down {
        color: #dc2626;
        font-weight: 700;
    }

    .salary-history-page .diff-neutral {
        color: #64748b;
        font-weight: 700;
    }

    .salary-history-page .empty-state {
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        padding: 28px;
        text-align: center;
        color: #64748b;
        font-size: 13px;
    }

    .salary-history-page .history-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 3px 8px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .salary-history-page .history-timeline {
            padding-left: 28px;
        }

        .salary-history-page .history-timeline-dot {
            left: -28px;
        }
    }
</style>
@endsection

@section('content')
@php
    $money = fn ($value) => '₹' . number_format((float) ($value ?? 0), 2);
@endphp

<div class="salary-history-page">
    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background:none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="#">Payroll</a></li>
                <li><a href="{{ route('payroll.salary-calculator.employees') }}">Salary Calculator</a></li>
                <li class="active"><span><b>{{ $title }}</b></span></li>
            </ol>
        </div>
    </div>

    <div class="card history-card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="mb-1">{{ $employee->emp_full_name }}</h4>
                <div class="employee-meta">
                    {{ $employee->emp_code ?? 'No Code' }} |
                    {{ optional($employee->fh_department)->d_name ?? 'No Department' }} |
                    {{ optional($employee->fh_designation)->dg_name ?? 'No Designation' }}
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary">{{ $historyCount }} history records</span>
                <a href="{{ route('payroll.salary-calculator.employees') }}" class="btn btn-light btn-sm">
                    Back to Index
                </a>
                <a href="{{ route('employee.salaries.addEdit', \Illuminate\Support\Facades\Crypt::encrypt($employee->emp_id)) }}" class="btn btn-primary btn-sm">
                    Open Calculator
                </a>
            </div>
        </div>
    </div>

    @if($timeline->isEmpty())
        <div class="empty-state">
            No salary history found for this employee.
        </div>
    @else
        <div class="history-timeline">
            @foreach($timeline as $item)
                @php
                    $history = $item['history'];
                    $changes = collect($item['changes']);
                    $effectiveDate = optional($history->wef)->format('d M Y') ?? optional($history->created_at)->format('d M Y');
                    $createdAt = optional($history->created_at)->format('d M Y h:i A');
                    $fy = optional($history->financial_years)->fy_year ?? 'FY not mapped';
                    $modalId = 'salaryHistoryDetails' . $history->sm_id;
                @endphp

                <div class="history-timeline-item">
                    <div class="history-timeline-card" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                        <div class="history-tab-header">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="history-tab-title">
                                        {{ $item['is_initial'] ? 'Initial salary structure' : 'Salary revision' }}
                                    </div>
                                    <div class="history-tab-subtitle text-muted">
                                        Effective: {{ $effectiveDate ?? '-' }}
                                    </div>
                                </div>
                                <span class="history-pill">{{ $fy }}</span>
                            </div>
                            <div class="history-tab-subtitle text-muted mt-1">
                                Updated: {{ $createdAt ?? '-' }}
                            </div>
                        </div>

                        <div class="history-tab-body">
                            <div class="history-metric-row">
                                <div class="history-metric">
                                    <div class="history-metric-label">Annual CTC</div>
                                    <div class="history-metric-value">{{ $money($history->sm_annual_ctc) }}</div>
                                </div>
                                <div class="history-metric">
                                    <div class="history-metric-label">Monthly Gross</div>
                                    <div class="history-metric-value">{{ $money($history->sm_gross_pay) }}</div>
                                </div>
                            </div>
                            <div class="history-tab-subtitle text-muted mt-2">
                                {{ $changes->count() }} change{{ $changes->count() === 1 ? '' : 's' }}. Click to view details.
                            </div>
                        </div>
                    </div>
                    <span class="history-timeline-dot"></span>
                </div>

                <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title" id="{{ $modalId }}Label">
                                        {{ $item['is_initial'] ? 'Initial salary structure' : 'Salary revision' }}
                                    </h5>
                                    <div class="history-tab-subtitle text-muted">
                                        Effective: {{ $effectiveDate ?? '-' }} | {{ $fy }} | Updated: {{ $createdAt ?? '-' }}
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="history-metric-row mb-3">
                                    <div class="history-metric">
                                        <div class="history-metric-label">Annual CTC</div>
                                        <div class="history-metric-value">{{ $money($history->sm_annual_ctc) }}</div>
                                    </div>
                                    <div class="history-metric">
                                        <div class="history-metric-label">Monthly Gross</div>
                                        <div class="history-metric-value">{{ $money($history->sm_gross_pay) }}</div>
                                    </div>
                                </div>

                                @if($history->sm_remark)
                                    <div class="remark-text text-muted mb-3">
                                        <strong>Remark:</strong> {{ $history->sm_remark }}
                                    </div>
                                @endif

                                @if($changes->isEmpty())
                                    <div class="text-muted remark-text">No amount difference captured for this update.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="detail-table">
                                            <thead>
                                                <tr>
                                                    <th>Component</th>
                                                    <th>Previous</th>
                                                    <th>Updated</th>
                                                    <th>Difference</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($changes as $change)
                                                    @php
                                                        $diff = (float) $change['diff'];
                                                        $diffClass = $diff > 0 ? 'diff-up' : ($diff < 0 ? 'diff-down' : 'diff-neutral');
                                                        $diffPrefix = $diff > 0 ? '+' : '';
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $change['label'] }}</td>
                                                        <td>{{ $change['old'] === null ? '-' : $money($change['old']) }}</td>
                                                        <td>{{ $money($change['new']) }}</td>
                                                        <td class="{{ $diffClass }}">{{ $diffPrefix }}{{ $money($diff) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
                                <a href="{{ route('export.smhistory', ['id' => $history->sm_id]) }}" class="btn btn-primary btn-sm">
                                    Export
                                </a>
                            </div>
                                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
