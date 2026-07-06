@extends('admin.layout.master')

@section('title', 'Reports & Ledger - FixHR')

@section('css')
    <style>
        /* Reports & Ledger Specific Styles - Minimalist Version */
        .reports-container {
            padding: 0;
        }

        .reports-header {
            margin-bottom: 1.5rem;
        }

        .reports-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .reports-header p {
            color: #64748b;
            font-size: 0.875rem;
        }

        .ledger-actions-row {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
            flex-wrap: nowrap;
        }

        .ledger-actions-row form {
            margin: 0;
        }

        .ledger-actions-row .form-control {
            height: 38px;
        }

        .search-wrapper {
            position: relative;
            width: 260px;
            max-width: 260px;
        }

        .search-wrapper svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: #94a3b8;
        }

        .search-wrapper input {
            width: 100%;
            padding: 0.5rem 0.75rem 0.5rem 2.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            background: white;
            transition: all 0.2s;
        }

        .search-wrapper input:focus {
            outline: none;
            border-color: #94a3b8;
            box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.1);
        }

        /* Employee Table Styles */
        .employee-table-container {
            background: white;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .employee-table-header {
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .employee-table-header h3 {
            font-weight: 600;
            color: #334155;
            font-size: 1rem;
            margin: 0;
        }

        .employee-table {
            width: 100%;
            border-collapse: collapse;
        }

        .employee-table th {
            text-align: left;
            padding: 0.75rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .employee-table td {
            padding: 1rem 1.5rem;
            font-size: 0.875rem;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
        }

        .employee-table tbody tr {
            cursor: pointer;
            transition: all 0.2s;
        }

        .employee-table tbody tr:hover {
            background: #f8fafc;
        }

        .employee-table tbody tr:last-child td {
            border-bottom: none;
        }

        .employee-id {
            color: #64748b;
            font-size: 0.875rem;
        }

        .employee-name {
            font-weight: 500;
            color: #1e293b;
        }

        .employee-pan {
            font-family: monospace;
            color: #475569;
        }

        .action-icon {
            color: #94a3b8;
            transition: all 0.2s;
        }

        tr:hover .action-icon {
            color: #475569;
        }

        /* Employee Menu Styles */
        .employee-menu-container {
            margin-top: 1.5rem;
        }

        .back-btn {
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            color: #64748b;
            background: none;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }

        .back-btn:hover {
            color: #334155;
            background: #f1f5f9;
        }

        .back-btn svg {
            width: 16px;
            height: 16px;
            transform: rotate(180deg);
        }

        .employee-summary-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .dark-mode .employee-summary-card {
            background: #1e293b;
            border-color: #334155;
        }

        .employee-summary-card h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #1e293b;
        }

        .dark-mode .employee-summary-card h2 {
            color: #f1f5f9;
        }

        .employee-summary-card p {
            color: #64748b;
            font-size: 0.875rem;
        }

        .dark-mode .employee-summary-card p {
            color: #94a3b8;
        }

        .employee-badge {
            background: #f1f5f9;
            color: #334155;
            padding: 0.375rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 500;
            border: 1px solid #e2e8f0;
        }

        .dark-mode .employee-badge {
            background: #334155;
            color: #e2e8f0;
            border-color: #475569;
        }

        /* Documents Grid */
        .documents-grid {
            background: white;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .documents-header {
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .documents-header h3 {
            font-weight: 600;
            color: #334155;
            font-size: 1rem;
            margin: 0;
        }

        .document-item {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.2s;
        }

        .document-item:last-child {
            border-bottom: none;
        }

        .document-item:hover {
            background: #f8fafc;
        }

        .document-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .document-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #475569;
        }

        .document-details h4 {
            font-weight: 500;
            color: #1e293b;
            font-size: 0.9375rem;
            margin-bottom: 0.25rem;
        }

        .document-details p {
            color: #64748b;
            font-size: 0.75rem;
        }

        .document-action {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .view-ledger-btn {
            padding: 0.375rem 1rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }

        .view-ledger-btn:hover {
            border-color: #94a3b8;
            color: #334155;
            background: #f8fafc;
        }

        .generate-btn {
            padding: 0.375rem 1rem;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #334155;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .generate-btn:hover {
            background: #e2e8f0;
            border-color: #cbd5e1;
        }

        .generate-btn svg {
            width: 14px;
            height: 14px;
        }

        /* Payroll Ledger Styles */
        .ledger-container {
            margin-top: 1rem;
        }

        .ledger-summary {
            background: white;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }

        .summary-item {
            text-align: center;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 0.5rem;
            border: 1px solid #f1f5f9;
        }

        .summary-item .label {
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .summary-item .value {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
        }

        .dark-mode .summary-item {
            background: #0f172a;
            border-color: #334155;
        }

        .dark-mode .summary-item .value {
            color: #e2e8f0;
        }


        .text-right {
            text-align: right;
        }

        .bg-light {
            background: #f8fafc;
        }
        .ledger-table-container {
            background: white;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            overflow: auto;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
            table-layout: fixed; /* YEH LINE IMPORTANT HAI */
        }

        .ledger-table th,
        .ledger-table td {
            padding: 0.75rem 0.5rem; /* padding kam kiya */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ledger-table th {
            text-align: left;
            padding: 0.75rem 0.5rem; /* th ka bhi padding same rakha */
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ledger-table td {
            padding: 0.75rem 0.5rem;
            font-size: 0.8125rem;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
        }

        .ledger-table tbody tr:hover {
            background: #f8fafc;
        }

        .ledger-table tfoot {
            background: #f8fafc;
            font-weight: 500;
        }

        .ledger-table tfoot td {
            padding: 0.75rem 0.5rem;
            font-size: 0.8125rem;
            color: #1e293b;
            border-top: 2px solid #e2e8f0;
        }

        .text-right {
            text-align: right !important; /* !important lagao */
        }

        /* Dark mode support */
        .dark-mode .ledger-table-container,
        .dark-mode .ledger-summary,
        .dark-mode .documents-grid,
        .dark-mode .employee-table-container {
            background: #1e293b;
            border-color: #334155;
        }

        .dark-mode .ledger-table tfoot{
        background: #0f172a;

        }

        .dark-mode .ledger-table th,
        .dark-mode .ledger-table tfoot,
        .dark-mode .employee-table th,
        .dark-mode .documents-header,
        .dark-mode .employee-table-header {
            background: #1e293b;
            border-color: #334155;
            color: #94a3b8;
        }

        .dark-mode .ledger-table td,
        .dark-mode .employee-table td {
            color: #e2e8f0;
            border-color: #334155;
        }

        .dark-mode .ledger-table tbody tr:hover,
        .dark-mode .employee-table tbody tr:hover,
        .dark-mode .document-item:hover {
            background: #0f172a;
        }

        .dark-mode .document-details h4 {
            color: #e2e8f0;
        }

        .dark-mode .search-wrapper input {
            background: #0f172a;
            border-color: #334155;
            color: #e2e8f0;
        }

        .dark-mode .totals {
            background: #0f172a;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            z-index: 1050;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            width: 100%;
            max-width: 28rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .modal-header h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-close {
            padding: 0.375rem;
            border-radius: 0.375rem;
            background: transparent;
            border: none;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .modal-body {
            margin-bottom: 1.5rem;
            color: #475569;
            line-height: 1.6;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .btn-secondary {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .btn-primary {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            background: #334155;
            color: white;
            border: 1px solid #1e293b;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: #1e293b;
        }

        /* Document Preview Modal */
        .preview-modal {
            max-width: 90%;
            width: 900px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            padding: 0;
            overflow: hidden;
        }

        .preview-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .preview-body {
            overflow: auto;
            padding: 2rem;
            background: #f1f5f9;
            flex: 1;
        }

        .preview-paper {
            background: white;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            border: 1px solid #e2e8f0;
        }

        .hidden {
            display: none !important;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            background: #f1f5f9;
            color: #334155;
            border-radius: 0.25rem;
            border: 1px solid #e2e8f0;
        }

        .dark-mode .badge {
            background: #334155;
            color: #e2e8f0;
            border-color: #475569;
        }
    </style>
@endsection

@section('content')
    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="py-0 bd-highlight">
                <div>
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a href="#">Payroll</a></li>
                        <li class="active"><span><b>Monthly Ledger</b></span></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div style="transition: all 0.3s ease; padding: 25px; overflow: hidden;">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Monthly Ledger</h4>
                </div>
                <div class="card-body">
                    <!-- Reports Container -->
                    <div class="reports-container">

                        <!-- Employee List View (Default) -->
                        <div id="employeeListView">
                            <!-- Header with Search -->
                            <div class="reports-header d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div>
                                    <p>Select an employee to view ledger and generate forms</p>
                                </div>
                                <div class="ledger-actions-row">
                                    <form method="GET" action="{{ route('monthly.ledger.index') }}">
                                        <select name="financial_year_id" class="form-control" onchange="this.form.submit()" style="min-width: 180px;">
                                            @foreach(($financialYears ?? []) as $year)
                                                <option value="{{ $year->fy_id }}" {{ (int) ($selectedFYId ?? 0) === (int) $year->fy_id ? 'selected' : '' }}>
                                                    {{ $year->fy_year }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                    <div class="search-wrapper">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <input type="text" id="employeeSearch" placeholder="Search">
                                    </div>
                                </div>
                            </div>

                            <!-- Employee Table -->
                            <div class="employee-table-container mt-4">
                                <div class="employee-table-header">
                                    <h3>Active Employees ({{ $totalEmployees ?? 0 }})</h3>
                                </div>
                                <div class="table-responsive">
                                    <table class="employee-table">
                                        <thead>
                                            <tr>
                                                <th>Emp Code</th>
                                                <th>Employee Name</th>
                                                <th>Designation</th>
                                                <th>Department</th>
                                                <th>Branch</th>
                                                <th>PAN Number</th>
                                                <th style="text-align: right;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="employeeTableBody">
                                            @forelse(($employees ?? []) as $emp)
                                            <tr class="employee-row" data-employee='@json($emp)'>
                                                <td class="employee-id">{{ $emp['code'] }}</td>
                                                <td class="employee-name">{{ $emp['name'] }}</td>
                                                <td>{{ $emp['designation'] }}</td>
                                                <td>{{ $emp['department'] }}</td>
                                                <td>{{ $emp['branch'] }}</td>
                                                <td class="employee-pan">{{ $emp['pan'] }}</td>
                                                <td style="text-align: right;">
                                                    <svg class="action-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" style="text-align:center; color:#64748b;">No active employees found for this business.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Employee Menu View (Hidden by default) -->
                        <div id="employeeMenuView" class="employee-menu-container hidden">
                            <button class="back-btn" onclick="switchView('list')">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                Back to Employee Directory
                            </button>

                            <!-- Employee Summary Card -->
                            <div class="employee-summary-card">
                                <div>
                                    <h2 id="selectedEmployeeName"></h2>
                                    <p id="selectedEmployeeDetails"></p>
                                </div>
                                <div class="employee-badge" id="selectedEmployeeId"></div>
                            </div>

                            <!-- Documents Grid -->
                            <div class="documents-grid">
                                <div class="documents-header">
                                    <h3>Available Documents & Reports</h3>
                                </div>

                                <!-- Payroll Ledger -->
                                <div class="document-item">
                                    <div class="document-info">
                                        <div class="document-icon">
                                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="document-details">
                                            <h4>Payroll Ledger</h4>
                                            <p>Detailed monthly salary breakdown and tax computations</p>
                                        </div>
                                    </div>
                                    <div class="document-action">
                                        <button class="view-ledger-btn" onclick="showPayrollLedger()">View Ledger</button>
                                    </div>
                                </div>

                                <!-- Form 16 -->
                                <div class="document-item">
                                    <div class="document-info">
                                        <div class="document-icon">
                                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="document-details">
                                            <h4>Form 16 (Part A & B)</h4>
                                            <p>Annual salary tax certificate for the employee</p>
                                        </div>
                                    </div>
                                    <div class="document-action">
                                        <button class="generate-btn" onclick="generateForm('Form 16')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                            Generate PDF
                                        </button>
                                    </div>
                                </div>

                                <!-- Form 16A -->
                                <div class="document-item">
                                    <div class="document-info">
                                        <div class="document-icon">
                                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="document-details">
                                            <h4>Form 16A</h4>
                                            <p>Tax deduction certificate for non-salary income</p>
                                        </div>
                                    </div>
                                    <div class="document-action">
                                        <button class="generate-btn" onclick="generateForm('Form 16A')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                            Generate PDF
                                        </button>
                                    </div>
                                </div>

                                <!-- Form 15G -->
                                <div class="document-item">
                                    <div class="document-info">
                                        <div class="document-icon">
                                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="document-details">
                                            <h4>Form 15G</h4>
                                            <p>Declaration for non-deduction of TDS on income</p>
                                        </div>
                                    </div>
                                    <div class="document-action">
                                        <button class="generate-btn" onclick="generateForm('Form 15G')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                            Generate PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payroll Ledger View (Hidden by default) -->
                        <div id="payrollLedgerView" class="ledger-container hidden">
                            <button class="back-btn" onclick="switchView('menu')">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                Back to Employee Actions
                            </button>

                            <!-- Ledger Summary -->
                            <div class="ledger-summary">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="font-weight-bold" id="ledgerEmployeeName">Arjun Sharma</h4>
                                    <span class="badge" id="ledgerEmployeeRegime">Old Regime</span>
                                </div>
                                <div class="summary-grid">
                                    <div class="summary-item">
                                        <div class="label">Annual Gross</div>
                                        <div class="value" id="annualGross">₹ 13,70,000</div>
                                    </div>
                                    <div class="summary-item">
                                        <div class="label">Total TDS</div>
                                        <div class="value" id="totalTDS">₹ 1,44,000</div>
                                    </div>
                                    <div class="summary-item">
                                        <div class="label">Total PF</div>
                                        <div class="value" id="totalPF">₹ 21,600</div>
                                    </div>
                                    <div class="summary-item">
                                        <div class="label">Net Payable</div>
                                        <div class="value" id="netPayable">₹ 12,02,000</div>
                                    </div>
                                    <div class="summary-item">
                                        <div class="label">Taxable Income</div>
                                        <div class="value" id="taxableIncome">₹ 8,50,000</div>
                                    </div>
                                </div>
                                <div id="taxValidationBanner" style="margin-top:12px; font-size:0.875rem; color:#475569;"></div>
                            </div>

                            <!-- Ledger Table -->
                            <div class="ledger-table-container">
                                <div class="table-responsive">
                                    <table class="ledger-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 100px;">Month</th>
                                                <th class="text-right" style="width: 100px;">Basic</th>
                                                <th class="text-right" style="width: 100px;">HRA</th>
                                                <th class="text-right" style="width: 100px;">Special</th>
                                                <th class="text-right" style="width: 120px;">Bonus/LTA</th>
                                                <th class="text-right" style="width: 100px;">Gross</th>
                                                <th class="text-right" style="width: 80px;">PF</th>
                                                <th class="text-right" style="width: 80px;">PT</th>
                                                <th class="text-right" style="width: 100px;">TDS</th>
                                                <th class="text-right" style="width: 120px;">Net Pay</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ledgerTableBody"></tbody>
                                        <tfoot>
                                            <tr class="totals" style="font-weight: 700;font-weight: 600;">
                                                <td>Total</td>
                                                <td class="text-right" id="totalBasic">0</td>
                                                <td class="text-right" id="totalHra">0</td>
                                                <td class="text-right" id="totalSpecial">0</td>
                                                <td class="text-right" id="totalBonusLta">0</td>
                                                <td class="text-right" id="totalGross">0</td>
                                                <td class="text-right" id="totalPf">0</td>
                                                <td class="text-right" id="totalPt">0</td>
                                                <td class="text-right" id="totalTdsTable">0</td>
                                                <td class="text-right" id="totalNet">0</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Warning Modal -->
    <div id="warningModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Verification Required
                </h3>
                <button class="modal-close" onclick="closeWarningModal()">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <p id="warningMessage">
                    Form cannot be generated. Employee has pending tax declarations.
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeWarningModal()">Cancel</button>
                <button class="btn-primary" onclick="goToVerification()">Go to Verification Queue</button>
            </div>
        </div>
    </div>

    <!-- Document Preview Modal -->
    <div id="previewModal" class="modal-overlay">
        <div class="modal-content preview-modal">
            <div class="preview-header">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <button class="modal-close" onclick="closePreviewModal()">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                    <span style="font-weight: 500;" id="previewFileName"></span>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button class="btn-primary" onclick="downloadPDF()">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 0.375rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download PDF
                    </button>
                </div>
            </div>
            <div class="preview-body">
                <div class="preview-paper" id="documentContent">
                    <div style="text-align: center; padding: 4rem 2rem;">
                        <svg width="64" height="64" fill="none" stroke="#cbd5e1" viewBox="0 0 24 24" style="margin: 0 auto 1rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h4 style="color: #64748b; margin-bottom: 0.5rem;">Document Preview</h4>
                        <p style="color: #94a3b8;">Click Generate PDF to create and download the document</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    // Global variables - these need to be accessible everywhere
    let currentView = 'list';
    let currentEmployee = null;
    const SELECTED_FY_ID = '{{ $selectedFYId ?? "" }}';
    const FORM16A_AVAILABILITY_URL = '{{ route("form16a.availability") }}';
    const FINANCIAL_YEARS = @json(collect($financialYears ?? [])->map(function ($year) {
        return ['id' => (int) $year->fy_id, 'label' => (string) $year->fy_year];
    })->values());
    const FORM16A_QUARTERS = [
        { id: 'ALL', label: 'Full FY (All quarters)' },
        { id: 'Q1', label: 'Q1 (Apr–Jun)' },
        { id: 'Q2', label: 'Q2 (Jul–Sep)' },
        { id: 'Q3', label: 'Q3 (Oct–Dec)' },
        { id: 'Q4', label: 'Q4 (Jan–Mar)' },
    ];

    // DOM Elements - declare globally but initialize in DOMContentLoaded
    let employeeListView, employeeMenuView, payrollLedgerView, warningModal, previewModal, employeeSearch;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DOM Elements
        employeeListView = document.getElementById('employeeListView');
        employeeMenuView = document.getElementById('employeeMenuView');
        payrollLedgerView = document.getElementById('payrollLedgerView');
        warningModal = document.getElementById('warningModal');
        previewModal = document.getElementById('previewModal');
        employeeSearch = document.getElementById('employeeSearch');

        // Search functionality
        if (employeeSearch) {
            employeeSearch.addEventListener('keyup', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('.employee-row');

                rows.forEach(row => {
                    const name = row.querySelector('.employee-name').textContent.toLowerCase();
                    const pan = row.querySelector('.employee-pan').textContent.toLowerCase();

                    if (name.includes(searchTerm) || pan.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Employee row click
        document.querySelectorAll('.employee-row').forEach(row => {
            row.addEventListener('click', function() {
                const empData = JSON.parse(this.dataset.employee);
                currentEmployee = empData;
                showEmployeeMenu(empData);
            });
        });
    });

   // Switch between views
    function switchView(view) {
        const employeeListView = document.getElementById('employeeListView');
        const employeeMenuView = document.getElementById('employeeMenuView');
        const payrollLedgerView = document.getElementById('payrollLedgerView');

        employeeListView.classList.add('hidden');
        employeeMenuView.classList.add('hidden');
        payrollLedgerView.classList.add('hidden');

        if (view === 'list') {
            employeeListView.classList.remove('hidden');
        } else if (view === 'menu') {
            employeeMenuView.classList.remove('hidden');
        } else if (view === 'ledger') {
            payrollLedgerView.classList.remove('hidden');
        }

        currentView = view;
    }


    // Show employee menu
    function showEmployeeMenu(employee) {
        document.getElementById('selectedEmployeeName').textContent = employee.name;
        document.getElementById('selectedEmployeeDetails').textContent =
            `${employee.designation} | ${employee.department} | ${employee.branch} | PAN: ${employee.pan}`;
        document.getElementById('selectedEmployeeId').textContent = `Code: ${employee.code}`;

        // Update ledger with employee data
        document.getElementById('ledgerEmployeeName').textContent = employee.name;
        document.getElementById('ledgerEmployeeRegime').textContent = `${employee.regime} Regime`;

        switchView('menu');
    }


    function formatMoney(value) {
        const amount = Number(value || 0);
        return amount.toLocaleString('en-IN', { maximumFractionDigits: 2, minimumFractionDigits: 0 });
    }

    function renderLedgerTable(rows, totals) {
        const body = document.getElementById('ledgerTableBody');
        if (!body) return;

        if (!rows || rows.length === 0) {
            body.innerHTML = '<tr><td colspan="10" class="text-center" style="padding:1rem;">No processed payroll found for this employee in selected financial year.</td></tr>';
        } else {
            body.innerHTML = rows.map((row) => `
                <tr>
                    <td style="font-weight: 500;">${row.month ?? '-'}</td>
                    <td class="text-right">${formatMoney(row.basic)}</td>
                    <td class="text-right">${formatMoney(row.hra)}</td>
                    <td class="text-right">${formatMoney(row.special)}</td>
                    <td class="text-right">${formatMoney(row.bonus_lta)}</td>
                    <td class="text-right" style="font-weight: 500;">${formatMoney(row.gross)}</td>
                    <td class="text-right">${formatMoney(row.pf)}</td>
                    <td class="text-right">${formatMoney(row.pt)}</td>
                    <td class="text-right">${formatMoney(row.tds)}</td>
                    <td class="text-right" style="font-weight: 600;">${formatMoney(row.net)}</td>
                </tr>
            `).join('');
        }

        document.getElementById('totalBasic').textContent = formatMoney(totals.basic);
        document.getElementById('totalHra').textContent = formatMoney(totals.hra);
        document.getElementById('totalSpecial').textContent = formatMoney(totals.special);
        document.getElementById('totalBonusLta').textContent = formatMoney(totals.bonus_lta);
        document.getElementById('totalGross').textContent = formatMoney(totals.gross);
        document.getElementById('totalPf').textContent = formatMoney(totals.pf);
        document.getElementById('totalPt').textContent = formatMoney(totals.pt);
        document.getElementById('totalTdsTable').textContent = formatMoney(totals.tds);
        document.getElementById('totalNet').textContent = formatMoney(totals.net);
    }

      // Show payroll ledger
    async function showPayrollLedger() {
        if (!currentEmployee) {
            return;
        }

        try {
            const params = new URLSearchParams({
                employee_id: currentEmployee.id,
                financial_year_id: '{{ $selectedFYId ?? "" }}',
            });

            const response = await fetch(`{{ route('monthly.ledger.data') }}?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Failed to load ledger data');
            }

            const ledgerData = result.data || {};
            const summary = ledgerData.summary || {};
            const totals = ledgerData.totals || {};
            const taxValidation = ledgerData.tax_validation || {};

            document.getElementById('annualGross').textContent = `₹ ${formatMoney(summary.annual_gross)}`;
            document.getElementById('totalTDS').textContent = `₹ ${formatMoney(summary.total_tds)}`;
            document.getElementById('totalPF').textContent = `₹ ${formatMoney(summary.total_pf)}`;
            document.getElementById('netPayable').textContent = `₹ ${formatMoney(summary.net_payable)}`;
            document.getElementById('taxableIncome').textContent = `₹ ${formatMoney(summary.taxable_income)}`;
            const taxBanner = document.getElementById('taxValidationBanner');
            if (taxBanner) {
                const expected = formatMoney(taxValidation.expected_tax_by_slab || 0);
                const actual = formatMoney(taxValidation.actual_tds_deducted || 0);
                const variance = formatMoney(Math.abs(taxValidation.variance || 0));
                const aligned = !!taxValidation.is_aligned;
                taxBanner.innerHTML = aligned
                    ? `Tax check: Actual TDS (₹ ${actual}) matches slab tax (₹ ${expected}) for this FY.`
                    : `Tax check: Slab tax ₹ ${expected}, actual TDS ₹ ${actual}, variance ₹ ${variance}.`;
            }

            renderLedgerTable(ledgerData.rows || [], totals);
            switchView('ledger');
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Unable to load ledger',
                text: error.message || 'Something went wrong while fetching processed payroll data.'
            });
        }
    }

    // Generate form - UPDATED VERSION
    function generateForm(formType) {
        // Check if employee is selected
        if (!currentEmployee) {
            Swal.fire({
                icon: 'warning',
                title: 'No Employee Selected',
                text: 'Please select an employee first.',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        // Check for pending declarations (implement your logic)
        const hasPendingDeclarations = false; // Set to true to test warning modal

        if (hasPendingDeclarations) {
            document.getElementById('warningMessage').innerHTML =
                `Form cannot be generated. <strong>${currentEmployee.name}</strong> has pending tax declarations. ` +
                `Please verify them in the Verification Queue first.`;
            document.getElementById('warningModal').classList.add('show');
        } else {
            // Form 16A: ask for FY + Quarter (quarter-wise PDF)
            if (formType === 'Form 16A') {
                const fyOptionsHtml = (FINANCIAL_YEARS || [])
                    .map((fy) => `<option value="${fy.id}" ${String(fy.id) === String(SELECTED_FY_ID) ? 'selected' : ''}>${fy.label}</option>`)
                    .join('');
                const quarterOptionsHtml = FORM16A_QUARTERS
                    .map((q) => `<option value="${q.id}">${q.label}</option>`)
                    .join('');

                Swal.fire({
                    title: 'Generate Form 16A',
                    html: `
                        <div style="text-align:left;">
                            <label style="display:block; font-weight:600; margin:0 0 6px;">Financial Year</label>
                            <select id="swal_fy" class="swal2-input" style="width:100%; margin:0 0 12px;">${fyOptionsHtml}</select>
                            <label style="display:block; font-weight:600; margin:0 0 6px;">Quarter</label>
                            <select id="swal_quarter" class="swal2-input" style="width:100%; margin:0;">${quarterOptionsHtml}</select>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Generate PDF',
                    preConfirm: async () => {
                        const fyId = document.getElementById('swal_fy')?.value;
                        const quarter = document.getElementById('swal_quarter')?.value;
                        if (!fyId || !quarter) {
                            Swal.showValidationMessage('Please select both Financial Year and Quarter.');
                            return false;
                        }

                        try {
                            const params = new URLSearchParams({
                                employee_id: currentEmployee.id,
                                financial_year_id: fyId,
                                quarter: quarter,
                            });
                            const res = await fetch(`${FORM16A_AVAILABILITY_URL}?${params.toString()}`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            const data = await res.json().catch(() => ({}));
                            const exists = !!data.exists;
                            if (!res.ok || !exists) {
                                Swal.showValidationMessage(data.message || 'Salary of this quarter is not generated yet.');
                                return false;
                            }
                        } catch (e) {
                            Swal.showValidationMessage('Unable to verify quarter salary data. Please try again.');
                            return false;
                        }

                        return { fyId, quarter };
                    }
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    const { fyId, quarter } = result.value || {};
                    submitPdfForm('{{ route("generate.form16a") }}', {
                        employee_id: currentEmployee.id,
                        financial_year_id: fyId,
                        form_type: formType,
                        ...(quarter && quarter !== 'ALL' ? { quarter } : {}),
                    }, formType);
                });

                return;
            }

            // Create a form dynamically and submit to generate PDF
            let actionUrl = '';
            if (formType === 'Form 16') {
                actionUrl = '{{ route("generate.form16") }}';
            } else if (formType === 'Form 15G') {
                actionUrl = '{{ route("generate.form15g") }}';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Form Type',
                    text: 'Please select a valid form type.',
                    timer: 2000,
                    showConfirmButton: false
                });
                return;
            }
            submitPdfForm(actionUrl, {
                employee_id: currentEmployee.id,
                financial_year_id: SELECTED_FY_ID,
                form_type: formType,
            }, formType);
        }
    }

    function submitPdfForm(actionUrl, payload, formTypeLabel) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        form.target = '_blank';
        form.action = actionUrl;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        Object.entries(payload || {}).forEach(([key, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value ?? '';
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        Swal.fire({
            icon: 'success',
            title: 'PDF Generating',
            text: `${formTypeLabel} is being generated for ${currentEmployee.name}.`,
            timer: 1500,
            showConfirmButton: false
        });
    }


      // Close warning modal
    function closeWarningModal() {
        document.getElementById('warningModal').classList.remove('show');
    }


      // Close preview modal
        function closePreviewModal() {
            document.getElementById('previewModal').classList.remove('show');
        }


    // Go to verification queue
    function goToVerification() {
        closeWarningModal();
        window.location.href = '/fixhr/verification-queue';
    }

   // Download PDF (remove if not needed)
    function downloadPDF() {
        Swal.fire({
            icon: 'info',
            title: 'Info',
            text: 'Please use the Generate PDF button instead.',
            timer: 2000,
            showConfirmButton: false
        });
    }

   // Handle escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeWarningModal();
            closePreviewModal();
        }
    });

      // Click outside to close modals
    document.getElementById('warningModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeWarningModal();
        }
    });

    document.getElementById('previewModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closePreviewModal();
        }
    });
</script>
@endsection
