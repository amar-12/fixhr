@extends('admin.layout.master')

@section('title', 'Tax Configuration')

@section('css')
<style>
    /* Tax Configuration Specific Styles */
    .tax-config-container {
        padding: 0;
    }

    .tax-header {
        margin-bottom: 1.5rem;
    }

    .tax-header h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }

    .tax-header p {
        color: #64748b;
        font-size: 0.875rem;
    }

    .tax-controls {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .fy-selector {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: white;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .fy-selector span {
        font-size: 0.875rem;
        color: #475569;
    }

    .fy-selector select {
        border: none;
        background: transparent;
        font-weight: 600;
        color: #4f46e5;
        outline: none;
        cursor: pointer;
        font-size: 0.875rem;
    }

    .regime-toggle {
        display: flex;
        background: white;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        padding: 0.25rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .regime-btn {
        padding: 0.375rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 0.375rem;
        border: none;
        background: transparent;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }

    .regime-btn.active {
        background: var(--accent-blue);
        color: white;
    }

    .slabs-table-container {
        background: white;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .slabs-table-header {
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .slabs-table-header h3 {
        font-weight: 600;
        color: #334155;
        font-size: 1rem;
        margin: 0;
    }

    .slabs-table-header span {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .slabs-table {
        width: 100%;
        border-collapse: collapse;
    }

    .slabs-table th {
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

    .slabs-table td {
        padding: 1rem 1.5rem;
        font-size: 0.875rem;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    .slabs-table tbody tr:hover {
        background: #f8fafc;
        cursor: pointer;
    }

    .slabs-table tbody tr:last-child td {
        border-bottom: none;
    }

    .rate-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        background: #eef2ff;
        color: #4f46e5;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .delete-btn {
        padding: 0.375rem;
        border-radius: 0.375rem;
        color: #ef4444;
        background: transparent;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .delete-btn:hover {
        background: #fee2e2;
    }

    .add-slab-form {
        background: white;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .add-slab-form h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .add-slab-form h3 svg {
        color: #4f46e5;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1rem;
        align-items: end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
    }

    .form-group label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #64748b;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: #1e293b;
        background: white;
        outline: none;
        transition: all 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .add-btn {
        background: var(--accent-blue);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 500;
        font-size: 0.875rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        height: 38px;
    }

    .add-btn:hover {
        background: #4338ca;
    }

    .error-message {
        display: none;
        margin-bottom: 1rem;
        padding: 0.75rem 1rem;
        background: #fef2f2;
        color: #b91c1c;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        align-items: center;
        gap: 0.5rem;
    }

    .error-message.show {
        display: flex;
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
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .modal-header h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
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

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }

    .btn-secondary {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        background: #f1f5f9;
        color: #334155;
        border: none;
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
        background: #4f46e5;
        color: white;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary:hover {
        background: #4338ca;
    }

    .hidden {
        display: none;
    }

    /* Dark mode support */
    .dark-mode .tax-header h2 {
        color: #f1f5f9;
    }

    .dark-mode .tax-header p {
        color: #94a3b8;
    }

    .dark-mode .fy-selector,
    .dark-mode .regime-toggle,
    .dark-mode .slabs-table-container,
    .dark-mode .add-slab-form,
    .dark-mode .modal-content {
        background: #1e293b;
        border-color: #334155;
    }

    .dark-mode .fy-selector span,
    .dark-mode .fy-selector select {
        color: #e2e8f0;
    }

    .dark-mode .slabs-table-header {
        background: #1e293b;
        border-color: #rgb(55 65 81 / var(--tw-bg-opacity, 1));
    }

    .dark-mode .slabs-table-header h3 {
        color: white !important;
    }

    .dark-mode .slabs-table th {
        background: #1e293b;
        color: #94a3b8;
        border-color: #334155;
    }

    .dark-mode .slabs-table td {
        color: #e2e8f0;
        border-color: #334155;
    }

    .dark-mode .slabs-table tbody tr:hover {
        background: #0f172a;
    }

    .dark-mode .form-group label {
        color: #94a3b8;
    }

    .dark-mode .form-group input,
    .dark-mode .form-group select {
        background: #0f172a;
        border-color: #334155;
        color: #e2e8f0;
    }

    .dark-mode .modal-header h3 {
        color: #f1f5f9;
    }

    .dark-mode .btn-secondary {
        background: #334155;
        color: #e2e8f0;
    }

    .dark-mode .btn-secondary:hover {
        background: #475569;
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
                    <li><a href="#">Tax</a></li>
                    <li class="active"><span><b>Tax Configuration</b></span></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div style="transition: all 0.3s ease; padding: 25px; overflow: hidden;">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Income Tax Slab Configuration</h4>
            </div>
            <div class="card-body">
                <!-- Tax Configuration Container -->
                <div class="tax-config-container">
                    <!-- Header Section -->
                    <div class="tax-header d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <h2>Tax Configuration</h2>
                            <p>Manage Income Tax Slabs per Financial Year</p>
                        </div>

                        <div class="tax-controls flex-wrap">

                            <!-- Financial Year Selector -->
                            <div class="fy-selector">
                                <span>FY:</span>
                                <select id="fy-select">
                                    @foreach($financialYears as $year)
                                        <option value="{{ $year->fy_id }}"
                                            {{ $currentFY && $currentFY->fy_id == $year->fy_id ? 'selected' : '' }}>
                                            {{ $year->fy_year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <!-- Regime Toggle -->
                            <div class="regime-toggle">
                                <button id="regime-new" class="regime-btn active" data-regime="new">
                                    New Regime
                                </button>
                                <button id="regime-old" class="regime-btn" data-regime="old">
                                    Old Regime
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tax Slabs Table -->
                    <div class="slabs-table-container mt-4">
                        <div class="slabs-table-header">
                            <h3>Slabs for <span id="selected-fy-display">2024-2025</span></h3>
                            <span>Click on rows to edit</span>
                        </div>

                        <div class="table-responsive">
                            <table class="slabs-table">
                                <thead>
                                    <tr>
                                        <th>Range Start (₹)</th>
                                        <th>Range End (₹)</th>
                                        <th>Tax Rate (%)</th>
                                        <th style="text-align: right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="slabs-table-body">
                                    <!-- New Regime Slabs - FY 2024-2025 -->
                                    <tr class="slab-row" data-id="1" data-regime="new" data-fy="2024-2025">
                                        <td>0</td>
                                        <td>3,00,000</td>
                                        <td>
                                            <span class="rate-badge">0%</span>
                                        </td>
                                        <td style="text-align: right;">
                                            <button class="delete-btn" data-id="1">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="slab-row" data-id="2" data-regime="new" data-fy="2024-2025">
                                        <td>3,00,001</td>
                                        <td>7,00,000</td>
                                        <td>
                                            <span class="rate-badge">5%</span>
                                        </td>
                                        <td style="text-align: right;">
                                            <button class="delete-btn" data-id="2">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="slab-row" data-id="3" data-regime="new" data-fy="2024-2025">
                                        <td>7,00,001</td>
                                        <td>10,00,000</td>
                                        <td>
                                            <span class="rate-badge">10%</span>
                                        </td>
                                        <td style="text-align: right;">
                                            <button class="delete-btn" data-id="3">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="slab-row" data-id="4" data-regime="new" data-fy="2024-2025">
                                        <td>10,00,001</td>
                                        <td>12,00,000</td>
                                        <td>
                                            <span class="rate-badge">15%</span>
                                        </td>
                                        <td style="text-align: right;">
                                            <button class="delete-btn" data-id="4">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="slab-row" data-id="5" data-regime="new" data-fy="2024-2025">
                                        <td>12,00,001</td>
                                        <td>15,00,000</td>
                                        <td>
                                            <span class="rate-badge">20%</span>
                                        </td>
                                        <td style="text-align: right;">
                                            <button class="delete-btn" data-id="5">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="slab-row" data-id="6" data-regime="new" data-fy="2024-2025">
                                        <td>15,00,001</td>
                                        <td>Above</td>
                                        <td>
                                            <span class="rate-badge">30%</span>
                                        </td>
                                        <td style="text-align: right;">
                                            <button class="delete-btn" data-id="6">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Old Regime Slabs - Hidden by default -->
                                    <tr class="slab-row hidden" data-id="7" data-regime="old" data-fy="2024-2025">
                                        <td>0</td>
                                        <td>2,50,000</td>
                                        <td>
                                            <span class="rate-badge">0%</span>
                                        </td>
                                        <td style="text-align: right;">
                                            <button class="delete-btn" data-id="7">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="slab-row hidden" data-id="8" data-regime="old" data-fy="2024-2025">
                                        <td>2,50,001</td>
                                        <td>5,00,000</td>
                                        <td>
                                            <span class="rate-badge">5%</span>
                                        </td>
                                        <td style="text-align: right;">
                                            <button class="delete-btn" data-id="8">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="slab-row hidden" data-id="9" data-regime="old" data-fy="2024-2025">
                                        <td>5,00,001</td>
                                        <td>10,00,000</td>
                                        <td>
                                            <span class="rate-badge">20%</span>
                                        </td>
                                        <td style="text-align: right;">
                                            <button class="delete-btn" data-id="9">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="slab-row hidden" data-id="10" data-regime="old" data-fy="2024-2025">
                                        <td>10,00,001</td>
                                        <td>Above</td>
                                        <td>
                                            <span class="rate-badge">30%</span>
                                        </td>
                                        <td style="text-align: right;">
                                            <button class="delete-btn" data-id="10">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div id="error-message" class="error-message">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <circle cx="12" cy="16" r="1" fill="currentColor"></circle>
                        </svg>
                        <span id="error-text"></span>
                    </div>

                    <!-- Add New Slab Form -->
                    <div class="add-slab-form">
                        <h3>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                            Add New Slab to <span id="selected-fy-add">2024-2025</span>
                        </h3>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Range Start (₹)</label>
                                <input type="number" id="range-start" placeholder="0">
                            </div>

                            <div class="form-group">
                                <label>Range End (₹)</label>
                                <input type="number" id="range-end" placeholder="Leave empty for 'Above'">
                            </div>

                            <div class="form-group">
                                <label>Tax Rate (%)</label>
                                <input type="number" id="tax-rate" step="0.1" placeholder="30">
                            </div>

                            <div class="form-group">
                                <label>Regime</label>
                                <select id="slab-regime">
                                    <option value="new">New Regime</option>
                                    <option value="old">Old Regime</option>
                                </select>
                            </div>

                            <div>
                                <button id="add-slab-btn" class="add-btn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    Add Slab
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Slab Modal -->
<div id="editModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Tax Slab</h3>
            <button id="closeModal" class="modal-close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div>
            <input type="hidden" id="edit-id">

            <div class="form-group mb-3">
                <label>Range Start (₹)</label>
                <input type="number" id="edit-start">
            </div>

            <div class="form-group mb-3">
                <label>Range End (₹)</label>
                <input type="number" id="edit-end" placeholder="Leave empty for 'Above'">
            </div>

            <div class="form-group mb-3">
                <label>Tax Rate (%)</label>
                <input type="number" id="edit-rate" step="0.1">
            </div>
        </div>

        <div class="modal-footer">
            <button id="cancelEdit" class="btn-secondary">Cancel</button>
            <button id="saveEdit" class="btn-primary">Save Changes</button>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const taxationApiBase = @json(url('/taxation'));
    const jsonFetchHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'X-Requested-With': 'XMLHttpRequest'
    };

    // State management
    let currentRegime = 'new';
    let currentFinancialYearId = null;
    let currentFinancialYearName = '2024-2025';

    // DOM Elements
    const regimeNewBtn = document.getElementById('regime-new');
    const regimeOldBtn = document.getElementById('regime-old');
    const fySelect = document.getElementById('fy-select');
    const selectedFYDisplay = document.getElementById('selected-fy-display');
    const selectedFYAdd = document.getElementById('selected-fy-add');
    const slabsTableBody = document.getElementById('slabs-table-body');
    const addSlabBtn = document.getElementById('add-slab-btn');
    const errorMessage = document.getElementById('error-message');
    const errorText = document.getElementById('error-text');

    // Modal Elements
    const editModal = document.getElementById('editModal');
    const closeModal = document.getElementById('closeModal');
    const cancelEdit = document.getElementById('cancelEdit');
    const saveEdit = document.getElementById('saveEdit');
    const editId = document.getElementById('edit-id');
    const editStart = document.getElementById('edit-start');
    const editEnd = document.getElementById('edit-end');
    const editRate = document.getElementById('edit-rate');

    // Check if we're on the tax configuration page
    if (!regimeNewBtn || !regimeOldBtn) {
        return;
    }

    // Initialize with current values
    function initialize() {
        if (fySelect) {
            currentFinancialYearId = fySelect.value;
            currentFinancialYearName = fySelect.options[fySelect.selectedIndex].text;
            selectedFYDisplay.textContent = currentFinancialYearName;
            selectedFYAdd.textContent = currentFinancialYearName;
        }
        loadSlabs();
    }

    // Show loading state
    function showLoading() {
        if (slabsTableBody) {
            slabsTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Loading slabs...</td></tr>';
        }
    }

    // Show error message
    function showError(message) {
        errorText.textContent = message;
        errorMessage.classList.add('show');
        setTimeout(() => {
            errorMessage.classList.remove('show');
        }, 5000);
    }

    // Show success message
    function showSuccess(message) {
        const successDiv = document.createElement('div');
        successDiv.className = 'success-message';
        successDiv.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 6L9 17l-5-5"></path>
            </svg>
            <span>${message}</span>
        `;
        successDiv.style.cssText = `
            display: flex;
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            background: #f0fdf4;
            color: #166534;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            align-items: center;
            gap: 0.5rem;
        `;

        const container = document.querySelector('.tax-config-container');
        const existingSuccess = document.querySelector('.success-message');
        if (existingSuccess) existingSuccess.remove();

        container.insertBefore(successDiv, document.querySelector('.slabs-table-container'));

        setTimeout(() => {
            successDiv.remove();
        }, 3000);
    }

    function showSeedDefaultsPopup(message, icon = 'success') {
        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                icon: icon,
                title: icon === 'success' ? 'Done' : 'Error',
                text: message,
                confirmButtonText: 'OK'
            });
            return;
        }

        if (icon === 'success') {
            showSuccess(message);
            return;
        }

        showError(message);
    }

    async function confirmSeedDefaults() {
        const message = `Are you sure you want to import IncomeTaxSlabs for ${currentFinancialYearName} (${currentRegime.toUpperCase()} Regime)? This will replace all existing slabs.`;

        if (typeof Swal !== 'undefined' && Swal.fire) {
            const result = await Swal.fire({
                icon: 'warning',
                title: 'Import IncomeTaxSlabs?',
                text: message,
                showCancelButton: true,
                confirmButtonText: 'Yes, import IncomeTaxSlabs',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            });
            return result.isConfirmed;
        }

        return confirm(message);
    }

    // Format number as Indian currency
    function formatIndianNumber(num) {
        if (!num && num !== 0) return '';
        return Math.round(num).toLocaleString('en-IN');
    }

    // Load tax slabs from backend
    async function loadSlabs() {
        if (!currentFinancialYearId) return;

        showLoading();

        try {
            const params = new URLSearchParams({
                financial_year_id: currentFinancialYearId,
                regime: currentRegime
            });
            const response = await fetch(`${taxationApiBase}/slabs?${params}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                renderSlabsTable(result.data);
            } else {
                showError('Failed to load tax slabs');
                slabsTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No slabs found. Click "Import IncomeTaxSlabs" to add default slabs.</td></tr>';
            }
        } catch (error) {
            console.error('Error:', error);
            showError('An error occurred while loading slabs');
            slabsTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Error loading slabs. Please try again.</td></tr>';
        }
    }

    // Render slabs table
    function renderSlabsTable(slabs) {
        if (!slabsTableBody) return;

        if (!slabs || slabs.length === 0) {
            slabsTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No slabs found. Click "Import IncomeTaxSlabs" to add default slabs.</td></tr>';
            return;
        }

        slabsTableBody.innerHTML = slabs.map(slab => `
            <tr class="slab-row" data-id="${slab.id}" data-regime="${slab.regime}" data-fy-id="${slab.financial_year_id}">
                <td>${formatIndianNumber(slab.range_start)}</td>
                <td>${slab.range_end ? formatIndianNumber(slab.range_end) : 'Above'}</td>
                <td>
                    <span class="rate-badge">${slab.tax_rate}%</span>
                </td>
                <td style="text-align: right;">
                    <button class="delete-btn" onclick="window.deleteSlab(${slab.id})">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </td>
            </tr>
        `).join('');

        // Attach row click listeners for editing
        attachRowClickListeners();
    }

    // Add new slab
    async function addNewSlab() {
        const start = document.getElementById('range-start').value;
        const end = document.getElementById('range-end').value;
        const rate = document.getElementById('tax-rate').value;
        const regime = document.getElementById('slab-regime').value;

        // Validation
        if (!start || rate === '') {
            showError('Start Range and Rate are required.');
            return;
        }

        const startNum = parseFloat(start);
        const endNum = end ? parseFloat(end) : null;
        const rateNum = parseFloat(rate);

        if (endNum !== null && startNum >= endNum) {
            showError('Start Range must be less than End Range.');
            return;
        }

        if (rateNum < 0 || rateNum > 100) {
            showError('Tax rate must be between 0 and 100.');
            return;
        }

        try {
            const response = await fetch(`${taxationApiBase}/slabs`, {
                method: 'POST',
                headers: jsonFetchHeaders,
                body: JSON.stringify({
                    financial_year_id: currentFinancialYearId,
                    regime: regime,
                    range_start: startNum,
                    range_end: endNum,
                    tax_rate: rateNum
                })
            });

            const result = await response.json();

            if (result.success) {
                showSuccess('Tax slab added successfully');
                document.getElementById('range-start').value = '';
                document.getElementById('range-end').value = '';
                document.getElementById('tax-rate').value = '';
                await loadSlabs();
            } else {
                showError(result.message || 'Failed to add tax slab');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('An error occurred while adding the slab');
        }
    }

    // Delete slab - make it global for onclick
    window.deleteSlab = async function(id) {
        if (!confirm('Are you sure you want to delete this slab?')) {
            return;
        }

        try {
            const response = await fetch(`${taxationApiBase}/slabs/${id}`, {
                method: 'DELETE',
                headers: jsonFetchHeaders
            });

            const result = await response.json();

            if (result.success) {
                showSuccess('Tax slab deleted successfully');
                await loadSlabs();
            } else {
                showError(result.message || 'Failed to delete tax slab');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('An error occurred while deleting the slab');
        }
    };

    // Open edit modal
    function openEditModal(row) {
        const cells = row.querySelectorAll('td');
        const id = row.dataset.id;
        const regime = row.dataset.regime;
        const fyId = row.dataset.fyId;

        editId.value = id;
        editStart.value = cells[0].textContent.replace(/,/g, '');
        editEnd.value = cells[1].textContent === 'Above' ? '' : cells[1].textContent.replace(/,/g, '');
        editRate.value = cells[2].querySelector('.rate-badge').textContent.replace('%', '');

        editModal.classList.add('show');
    }

    // Save edit changes
    async function saveEditChanges() {
        const id = editId.value;
        const start = parseFloat(editStart.value);
        const end = editEnd.value ? parseFloat(editEnd.value) : null;
        const rate = parseFloat(editRate.value);

        if (isNaN(start) || isNaN(rate)) {
            showError('Please fill all required fields');
            return;
        }

        if (end !== null && start >= end) {
            showError('Start Range must be less than End Range.');
            return;
        }

        if (rate < 0 || rate > 100) {
            showError('Tax rate must be between 0 and 100.');
            return;
        }

        try {
            const response = await fetch(`${taxationApiBase}/slabs/${id}`, {
                method: 'PUT',
                headers: jsonFetchHeaders,
                body: JSON.stringify({
                    range_start: start,
                    range_end: end,
                    tax_rate: rate
                })
            });

            const result = await response.json();

            if (result.success) {
                showSuccess('Tax slab updated successfully');
                closeEditModal();
                await loadSlabs();
            } else {
                showError(result.message || 'Failed to update tax slab');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('An error occurred while updating the slab');
        }
    }

    // Close edit modal
    function closeEditModal() {
        editModal.classList.remove('show');
    }

    // Attach row click listeners for editing
    function attachRowClickListeners() {
        document.querySelectorAll('.slab-row').forEach(row => {
            row.addEventListener('click', (e) => {
                if (!e.target.closest('.delete-btn')) {
                    openEditModal(row);
                }
            });
        });
    }

    // Import default IncomeTaxSlabs
    async function seedDefaults() {
        const shouldSeed = await confirmSeedDefaults();
        if (!shouldSeed) {
            return;
        }

        try {
            const response = await fetch(`${taxationApiBase}/seed-defaults`, {
                method: 'POST',
                headers: jsonFetchHeaders,
                body: JSON.stringify({
                    financial_year_id: currentFinancialYearId,
                    regime: currentRegime
                })
            });

            const result = await response.json();

            if (result.success) {
                showSeedDefaultsPopup('IncomeTaxSlabs imported successfully', 'success');
                await loadSlabs();
            } else {
                showSeedDefaultsPopup(result.message || 'Failed to import IncomeTaxSlabs', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showSeedDefaultsPopup('An error occurred while importing IncomeTaxSlabs', 'error');
        }
    }

    // Toggle Regime
    function setActiveRegime(regime) {
        currentRegime = regime;

        // Update button styles
        if (regime === 'new') {
            regimeNewBtn.classList.add('active');
            regimeOldBtn.classList.remove('active');
        } else {
            regimeOldBtn.classList.add('active');
            regimeNewBtn.classList.remove('active');
        }

        // Load slabs for selected regime
        loadSlabs();
    }

    // Update FY display and load slabs
    function updateFYDisplay() {
        if (fySelect) {
            currentFinancialYearId = fySelect.value;
            currentFinancialYearName = fySelect.options[fySelect.selectedIndex].text;
            selectedFYDisplay.textContent = currentFinancialYearName;
            selectedFYAdd.textContent = currentFinancialYearName;
            loadSlabs();
        }
    }

    // Add Import IncomeTaxSlabs button if not exists
    function addSeedDefaultsButton() {
        const taxControls = document.querySelector('.tax-controls');
        if (taxControls && !document.getElementById('seed-defaults-btn')) {
            const seedBtn = document.createElement('button');
            seedBtn.id = 'seed-defaults-btn';
            seedBtn.className = 'seed-defaults-btn';
            seedBtn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 2L9 14M21 2l-4 8-8 4 8-4 4-8zM3 20l4-4"/>
                </svg>
                Import IncomeTaxSlabs
            `;
            seedBtn.style.cssText = `
                padding: 0.5rem 1rem;
                background: #10b981;
                color: white;
                border-radius: 0.5rem;
                font-weight: 500;
                font-size: 0.875rem;
                border: none;
                cursor: pointer;
                transition: all 0.2s;
                display: flex;
                align-items: center;
                gap: 0.375rem;
            `;
            seedBtn.addEventListener('click', seedDefaults);
            taxControls.appendChild(seedBtn);
        }
    }

    // Event Listeners
    if (regimeNewBtn) regimeNewBtn.addEventListener('click', () => setActiveRegime('new'));
    if (regimeOldBtn) regimeOldBtn.addEventListener('click', () => setActiveRegime('old'));
    if (fySelect) fySelect.addEventListener('change', updateFYDisplay);
    if (addSlabBtn) addSlabBtn.addEventListener('click', addNewSlab);

    // Modal controls
    if (closeModal) closeModal.addEventListener('click', closeEditModal);
    if (cancelEdit) cancelEdit.addEventListener('click', closeEditModal);
    if (saveEdit) saveEdit.addEventListener('click', saveEditChanges);

    // Close modal when clicking outside
    if (editModal) {
        editModal.addEventListener('click', (e) => {
            if (e.target === editModal) {
                closeEditModal();
            }
        });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && editModal && editModal.classList.contains('show')) {
            closeEditModal();
        }
    });

    // Add Import IncomeTaxSlabs button
    addSeedDefaultsButton();

    // Initialize
    initialize();
});
</script>

<style>
.success-message {
    display: flex;
    margin-bottom: 1rem;
    padding: 0.75rem 1rem;
    background: #f0fdf4;
    color: #166534;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    align-items: center;
    gap: 0.5rem;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.seed-defaults-btn:hover {
    background: #059669 !important;
}

/* Loading state for table */
.slabs-table tbody tr td[colspan] {
    text-align: center;
    padding: 2rem;
    color: #64748b;
}
</style>
@endsection
