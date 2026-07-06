@extends('admin.layout.master')

@section('title', $title . ' - ' . ($employee->emp_full_name ?? ''))

@section('css')
<style>
/* ==================== DEDUCTIONS GRID STYLES ==================== */
/* Deductions preview: single tabular block (matches earnings-container rows) */
.deductions-grid {
    display: block;
    width: 100%;
}

/* ==================== SINGLE DEDUCTION CARD STYLES ==================== */
.deduction-single-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.2s ease;
    margin-bottom: 0;
}

.deduction-single-card:hover {
    border-color: var(--danger);
    box-shadow: var(--shadow);
}

.deduction-single-header {
    background: var(--card-header);
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.deduction-single-header h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.deduction-single-header h3 i {
    color: var(--danger);
    font-size: 1.1rem;
}

.deduction-single-header .badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    background: var(--hover-bg);
    color: var(--text-secondary);
}

.deduction-single-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1px;
    background: var(--border-color);
}

.deduction-single-column {
    background: var(--card-bg);
    padding: 16px;
}

.deduction-single-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px dashed var(--border-color);
}

.deduction-single-item:last-child {
    border-bottom: none;
}

.deduction-single-label {
    font-size: 0.9rem;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 6px;
}

.deduction-single-label i {
    color: var(--danger);
    font-size: 0.85rem;
    width: 18px;
}

.deduction-single-amount {
    font-size: 1rem;
    font-weight: 600;
    color: var(--danger);
}

.deduction-single-footer {
    padding: 16px 20px;
    background: var(--hover-bg);
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
}

.deduction-single-footer span:last-child {
    color: var(--danger);
    font-size: 1.1rem;
}

/* Compact mode adjustments */
.compact-mode .deduction-single-header {
    padding: 12px 16px;
}

.compact-mode .deduction-single-header h3 {
    font-size: 1rem;
}

.compact-mode .deduction-single-column {
    padding: 12px;
}

.compact-mode .deduction-single-item {
    padding: 6px 0;
}

.compact-mode .deduction-single-label {
    font-size: 0.8rem;
}

.compact-mode .deduction-single-amount {
    font-size: 0.9rem;
}

.compact-mode .deduction-single-footer {
    padding: 12px 16px;
}

.compact-mode .deduction-single-footer span:last-child {
    font-size: 1rem;
}

.deduction-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 16px;
    transition: all 0.2s ease;
}

.deduction-card:hover {
    border-color: var(--border-color);
    box-shadow: var(--shadow);
}

.deduction-card-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px dashed var(--border-color);
    display: flex;
    align-items: center;
    gap: 8px;
}

.deduction-card-title i {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.deduction-card-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding: 4px 0;
}

.deduction-card-item:last-child {
    margin-bottom: 0;
}

.deduction-card-label {
    font-size: 0.9rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 6px;
}

.deduction-card-label i {
    color: var(--text-secondary);
    font-size: 0.8rem;
    width: 16px;
}

.deduction-card-amount {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
}

.deduction-card-total {
    margin-top: 12px;
    padding-top: 8px;
    border-top: 2px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 700;
}

.deduction-card-total span:first-child {
    color: var(--text-primary);
    font-size: 0.9rem;
}

.deduction-card-total span:last-child {
    color: var(--text-primary);
    font-size: 1.1rem;
}

.compact-mode .deduction-card {
    padding: 12px;
}

.compact-mode .deduction-card-title {
    font-size: 0.8rem;
    margin-bottom: 8px;
}

.compact-mode .deduction-card-item {
    margin-bottom: 6px;
}

.compact-mode .deduction-card-label {
    font-size: 0.8rem;
}

.compact-mode .deduction-card-amount {
    font-size: 0.85rem;
}

.compact-mode .deduction-card-total {
    margin-top: 8px;
    padding-top: 6px;
}

.compact-mode .deduction-card-total span:last-child {
    font-size: 0.95rem;
}
/* ==================== EARNINGS GRID STYLES ==================== */
.earnings-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.earning-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 16px;
    transition: all 0.2s ease;
}

.earning-card:hover {
    border-color: var(--primary);
    box-shadow: var(--shadow);
}

.earning-card-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px dashed var(--border-color);
}

.earning-card-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding: 4px 0;
}

.earning-card-item:last-child {
    margin-bottom: 0;
}

.earning-card-label {
    font-size: 0.9rem;
    color: var(--text-primary);
}

.earning-card-amount {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
}

.earning-card-amount.highlight {
    color: var(--primary);
}

.earning-card-input {
    width: 120px;
    display: flex;
    align-items: center;
    border: 1px solid var(--input-border);
    border-radius: 6px;
    padding: 4px 8px;
    background: var(--input-bg);
}

.earning-card-input .currency {
    color: var(--text-secondary);
    font-size: 0.8rem;
    margin-right: 4px;
}

.earning-card-input input {
    background: transparent;
    border: none;
    color: var(--input-text);
    font-weight: 600;
    font-size: 0.9rem;
    width: 100%;
    outline: none;
}

/* Compact mode adjustments */
.compact-mode .earnings-grid {
    gap: 12px;
}

.compact-mode .earning-card {
    padding: 12px;
}

.compact-mode .earning-card-title {
    font-size: 0.8rem;
    margin-bottom: 8px;
}

.compact-mode .earning-card-item {
    margin-bottom: 6px;
}

.compact-mode .earning-card-label {
    font-size: 0.8rem;
}

.compact-mode .earning-card-amount {
    font-size: 0.85rem;
}

.compact-mode .earning-card-input {
    width: 100px;
    padding: 2px 6px;
}

/* ==================== EMPLOYER CONTRIBUTIONS STYLES ==================== */
.employer-contributions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.employer-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 16px;
    transition: all 0.2s ease;
}

.employer-card:hover {
    border-color: var(--success);
    box-shadow: var(--shadow);
}

.employer-card-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px dashed var(--border-color);
    display: flex;
    align-items: center;
    gap: 8px;
}

.employer-card-title i {
    color: var(--success);
    font-size: 0.9rem;
}

.employer-card-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding: 4px 0;
}

.employer-card-item:last-child {
    margin-bottom: 0;
}

.employer-card-label {
    font-size: 0.9rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 6px;
}

.employer-card-label i {
    color: var(--success);
    font-size: 0.8rem;
    width: 16px;
}

.employer-card-amount {
    font-size: 1rem;
    font-weight: 600;
    color: var(--success);
}

.employer-card-total {
    margin-top: 12px;
    padding-top: 8px;
    border-top: 2px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 700;
}

.employer-card-total span:first-child {
    color: var(--text-primary);
    font-size: 0.9rem;
}

.employer-card-total span:last-child {
    color: var(--success);
    font-size: 1.1rem;
}

/* Compact mode adjustments */
.compact-mode .employer-contributions-grid {
    gap: 12px;
}

.compact-mode .employer-card {
    padding: 12px;
}

.compact-mode .employer-card-title {
    font-size: 0.8rem;
    margin-bottom: 8px;
}

.compact-mode .employer-card-item {
    margin-bottom: 6px;
}

.compact-mode .employer-card-label {
    font-size: 0.8rem;
}

.compact-mode .employer-card-amount {
    font-size: 0.85rem;
}

.compact-mode .employer-card-total {
    margin-top: 8px;
    padding-top: 6px;
}

.compact-mode .employer-card-total span:last-child {
    font-size: 0.95rem;
}
    /* ==================== DUAL MODE SYSTEM - LIGHT & DARK ==================== */
    :root {
        /* Light Mode Variables - Default */
        --bg-primary: #ffffff;
        --bg-secondary: #f8fafc;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --border-light: #e2e8f0;
        --card-bg: #ffffff;
        --card-header: #f8fafc;
        --input-bg: #ffffff;
        --input-border: #e2e8f0;
        --input-text: #1e293b;
        --input-disabled: #f1f5f9;
        --table-header: #f1f5f9;
        --table-border: #e2e8f0;
        --hover-bg: #f1f5f9;
        --primary: #3b82f6;
        --primary-hover: #2563eb;
        --primary-light: #dbeafe;
        --success: #10b981;
        --success-light: #d1fae5;
        --danger: #ef4444;
        --danger-light: #fee2e2;
        --warning: #f59e0b;
        --warning-light: #fef3c7;
        --info: #3b82f6;
        --purple: #8b5cf6;
        --purple-light: #ede9fe;
        --emerald: #10b981;
        --emerald-light: #d1fae5;
        --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        --progress-bg: #e2e8f0;
        --progress-fill: #8b5cf6;
        --employer-cell: #f0f8ff;
        --arrow: #3b82f6;
        --badge-bg: #dbeafe;
        --badge-text: #3b82f6;
    }

    /* Dark Mode Variables */
    [data-theme="dark"], .dark-mode {
        --bg-primary: #1a1f2e;
        --bg-secondary: #111827;
        --text-primary: #e5e7eb;
        --text-secondary: #9ca3af;
        --text-muted: #9ca3af;
        --border-color: #2d3348;
        --border-light: #2d3348;
        --card-bg: #222837;
        --card-header: #222837;
        --input-bg: #2d3348;
        --input-border: #3f455e;
        --input-text: #e5e7eb;
        --input-disabled: #2d3348;
        --table-header: #2d3348;
        --table-border: #3f455e;
        --hover-bg: #2d3348;
        --primary: #60a5fa;
        --primary-hover: #3b82f6;
        --primary-light: rgba(59, 130, 246, 0.2);
        --success: #34d399;
        --success-light: #064e3b;
        --danger: #f87171;
        --danger-light: #7f1d1d;
        --warning: #fbbf24;
        --warning-light: #78350f;
        --info: #60a5fa;
        --purple: #a78bfa;
        --purple-light: #2e1b4a;
        --emerald: #34d399;
        --emerald-light: #064e3b;
        --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.5);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
        --progress-bg: #2d3348;
        --progress-fill: #a78bfa;
        --employer-cell: #2d3348;
        --arrow: #60a5fa;
        --badge-bg: rgba(59, 130, 246, 0.2);
        --badge-text: #60a5fa;
    }

    /* ==================== GLOBAL STYLES ==================== */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background-color: var(--bg-secondary) !important;
        color: var(--text-primary) !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        transition: background-color 0.3s ease, color 0.3s ease;
        line-height: 1.5;
        min-height: 100vh;
    }

    .salary-calculator {
        min-height: 100vh;
        background-color: var(--bg-secondary);
        color: var(--text-primary);
        padding: 24px;
        transition: all 0.3s ease;
    }

    /* ==================== TYPOGRAPHY ==================== */
    h1, h2, h3, h4, h5, h6 {
        color: var(--text-primary);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1rem;
    }

    .text-muted {
        color: var(--text-muted) !important;
    }

    .text-dark {
        color: var(--text-primary) !important;
    }

    .text-danger {
        color: var(--danger) !important;
    }

    .text-success {
        color: var(--success) !important;
    }

    .text-primary {
        color: var(--primary) !important;
    }

    /* ==================== CARDS ==================== */
    .card {
        background-color: var(--card-bg) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 16px;
        box-shadow: var(--shadow) !important;
        margin-bottom: 1.5rem;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: var(--shadow-lg) !important;
        border-color: var(--primary) !important;
    }

    .card-header {
        background-color: var(--card-header) !important;
        border-bottom: 1px solid var(--border-color) !important;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-header h4, .card-header h5 {
        color: var(--text-primary) !important;
        margin: 0;
        font-weight: 600;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Input Card */
    .input-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 24px;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        box-shadow: var(--shadow);
    }

    .input-badge {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1rem;
        text-align: center;
    }

    /* Section Cards */
    .section-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        height: 100%;
        transition: all 0.3s ease;
    }

    .section-header {
        background: var(--card-header);
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-header h4 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .section-header small {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .section-body {
        padding: 1rem;
        max-height: 400px;
        overflow-y: auto;
    }

    .section-footer {
        padding: 1rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.875rem;
        font-weight: 600;
        background: var(--hover-bg);
    }

    /* Summary Card */
    .summary-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: var(--shadow-lg);
        padding: 1.5rem;
        position: sticky;
        top: 100px;
        transition: all 0.3s ease;
    }

    .summary-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .summary-icon {
        width: 48px;
        height: 48px;
        background: var(--emerald-light);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--emerald);
        font-size: 1.25rem;
        transition: all 0.3s ease;
    }

    .summary-title h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .summary-title p {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin: 0;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px dashed var(--border-color);
    }

    .summary-row.total {
        border-bottom: none;
        padding-top: 1rem;
    }

    .summary-label {
        color: var(--text-secondary);
        font-size: 0.875rem;
    }

    .summary-value {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 1rem;
    }

    .summary-value.large {
        font-size: 2rem;
        color: var(--emerald);
        line-height: 1.2;
    }

    .summary-progress {
        margin: 1.5rem 0;
    }

    .progress-bar-calc {
        height: 6px;
        background: var(--progress-bg);
        border-radius: 9999px;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .progress-fill {
        height: 100%;
        border-radius: 9999px;
        transition: width 0.3s ease;
    }

    /* ==================== FORMS ==================== */
    .form-label {
        color: var(--text-secondary) !important;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.25rem;
        display: block;
    }

    .form-control {
        background-color: var(--input-bg) !important;
        border: 1px solid var(--input-border) !important;
        color: var(--input-text) !important;
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        width: 100%;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }

    .form-control:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        outline: none;
    }

    .form-control:read-only {
        background-color: var(--input-disabled) !important;
        color: var(--text-secondary) !important;
    }

    /* Input Group */
    .input-group-calc {
        display: inline-flex;
        align-items: center;
        background: var(--hover-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 0.5rem;
        flex-wrap: wrap;
    }

    .input-group-calc .btn-group {
        display: flex;
        background: var(--card-bg);
        border-radius: 8px;
        border: 1px solid var(--border-color);
        margin-right: 1rem;
    }

    .input-group-calc .btn-group .btn {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        border: none;
        background: transparent;
        color: var(--text-secondary);
        transition: all 0.2s;
        cursor: pointer;
    }

    .input-group-calc .btn-group .btn.active {
        background: var(--primary);
        color: white;
        border-radius: 6px;
    }

    .input-group-calc .btn-group .btn:hover:not(.active) {
        color: var(--primary);
    }

    .input-wrapper {
        display: flex;
        align-items: center;
        padding-left: 1rem;
        border-left: 1px solid var(--border-color);
    }

    .input-wrapper .currency-symbol {
        color: var(--text-secondary);
        font-weight: 600;
        font-size: 1.25rem;
        margin-right: 0.5rem;
    }

    .input-wrapper input {
        background: transparent;
        border: none;
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--input-text);
        width: 150px;
        outline: none;
    }

    /* Earnings Input Field */
    .earning-item {
        margin-bottom: 1rem;
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 0.75rem;
        transition: all 0.2s ease;
    }

    .earning-item:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow);
    }

    .earning-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .earning-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        border-radius: 9999px;
        background: var(--hover-bg);
        color: var(--text-secondary);
    }

    .earning-input-group {
        display: flex;
        align-items: center;
        border: 1px solid var(--input-border);
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        background: var(--input-bg);
        transition: all 0.2s;
    }

    .earning-input-group:focus-within {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .earning-input-group .currency {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-right: 0.25rem;
        font-weight: 600;
    }

    .earning-input-group input {
        background: transparent;
        border: none;
        color: var(--input-text);
        font-weight: 600;
        font-size: 0.875rem;
        width: 100%;
        outline: none;
    }

    .earning-input-group input:focus {
        outline: none;
    }

    /* ==================== EARNINGS CONTAINER STYLES ==================== */
.earnings-container {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
}

.earnings-header {
    display: grid;
    grid-template-columns: 1fr 1fr;
    padding: 0.75rem 1rem;
    background: var(--card-header);
    border-bottom: 1px solid var(--border-color);
    font-weight: 600;
    font-size: 12px;
    color: var(--text-secondary);
}

.earnings-header span:last-child {
    text-align: right;
}

.earnings-body {
    max-height: 400px;
    overflow-y: auto;
}

/* Deductions preview + employee inputs: tabular rows */
.deductions-preview-earnings-style .earning-info,
.deductions-input-table .earning-info {
    min-width: 0;
}

.deductions-preview-earnings-style .earning-name,
.deductions-input-table .earning-name {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.earning-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: center;
    padding: 0.5rem 1rem;
    border-bottom: 1px solid var(--border-color);
    transition: background-color 0.2s ease;
}

.earning-row:hover {
    background-color: var(--hover-bg);
}

.earning-row:last-child {
    border-bottom: none;
}

.earning-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.earning-name {
    font-weight: 500;
    color: var(--text-primary);
    font-size: 12px;
}

.earning-badge-sm {
    font-size: 12px;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    background: var(--hover-bg);
    color: var(--text-secondary);
    white-space: nowrap;
}

.earning-input-wrapper {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.5rem;
}

.earning-input-group-sm {
    display: flex;
    align-items: center;
    border: 1px solid var(--input-border);
    border-radius: 6px;
    padding: 0.3rem 0.5rem;
    background: var(--input-bg);
    width: 140px;
}

.earning-input-group-sm:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

.earning-input-group-sm .currency-sm {
    color: var(--text-secondary);
    font-size: 12px;
    margin-right: 0.2rem;
    font-weight: 600;
}

.earning-input-group-sm input {
    background: transparent;
    border: none;
    color: var(--input-text);
    font-weight: 600;
    font-size: 12px;
    width: 100%;
    outline: none;
    -moz-appearance: textfield;
}

.earning-input-group-sm input::-webkit-outer-spin-button,
.earning-input-group-sm input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.earning-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background: var(--primary-light);
    border-top: 1px solid var(--border-color);
    font-weight: 600;
    color: var(--primary);
}

.earning-total-label {
    font-size: 0.9rem;
}

.earning-total-value {
    font-size: 1.1rem;
}

/* Compact mode adjustments */
.compact-mode .earning-row {
    padding: 0.35rem 0.75rem;
}

.compact-mode .earning-name {
    font-size: 0.8rem;
}

.compact-mode .earning-input-group-sm {
    width: 110px;
    padding: 0.2rem 0.4rem;
}

.compact-mode .earning-input-group-sm input {
    font-size: 0.75rem;
}

.compact-mode .earning-total-row {
    padding: 0.5rem 0.75rem;
}

.compact-mode .earning-total-value {
    font-size: 0.95rem;
}

    /* Manual Input */
    .manual-earning-input {
        background: transparent;
        border: none;
        color: var(--input-text);
        font-weight: 600;
        font-size: 0.875rem;
        width: 100%;
        outline: none;
        -moz-appearance: textfield;
    }

    .manual-earning-input::-webkit-outer-spin-button,
    .manual-earning-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* ==================== METRICS GRID ==================== */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .metric-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        transition: all 0.2s;
    }

    .metric-card.highlight {
        background: var(--primary-light);
        border-color: var(--primary);
    }

    .metric-card.highlight .metric-value {
        color: var(--primary);
    }

    .metric-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .metric-value {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    /* ==================== MODE SWITCHER ==================== */
    .mode-switcher {
        background: var(--hover-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.25rem;
        display: inline-flex;
        margin-bottom: 1.5rem;
    }

    .mode-switcher .btn {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        border: none;
        background: transparent;
        color: var(--text-secondary);
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .mode-switcher .btn.active {
        background: var(--card-bg);
        color: var(--text-primary);
        box-shadow: var(--shadow);
    }

    .mode-switcher .btn:hover:not(.active) {
        color: var(--primary);
    }

    /* ==================== EARNINGS GRID ==================== */
    .earnings-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1rem;
    }

    /* ==================== DEDUCTION ITEMS ==================== */
    .deduction-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px dashed var(--border-color);
    }

    .deduction-item:last-child {
        border-bottom: none;
    }

    .deduction-label {
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .deduction-value {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    /* ==================== TOTAL GROSS ==================== */
    .total-gross {
        padding: 1rem;
        border-top: 1px solid var(--border-color);
        font-size: 1rem;
        font-weight: 600;
        background: var(--primary-light);
        border-radius: 0 0 12px 12px;
    }

    .total-gross-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .total-gross-row span:last-child {
        color: var(--primary);
    }

    .gross-balance-text {
        display: block;
        margin-top: 0.35rem;
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--text-secondary);
        text-align: right;
    }

    .gross-balance-text.remaining {
        color: var(--warning);
    }

    .gross-balance-text.excess {
        color: var(--danger);
    }

    .gross-balance-text.matched {
        color: var(--success);
    }

    .total-gross.mismatch {
        background: var(--danger-light);
    }

    .total-gross.mismatch .total-gross-row span:last-child {
        color: var(--danger);
    }

    /* ==================== MISMATCH WARNING ==================== */
    .mismatch-warning {
        background: var(--danger-light);
        border: 1px solid var(--danger);
        border-radius: 8px;
        padding: 1rem;
        margin: 1rem 0;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        color: var(--danger);
    }

    /* ==================== BUTTONS ==================== */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 8px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-outline-danger {
        color: var(--danger) !important;
        border-color: var(--danger) !important;
        background: transparent !important;
    }

    .btn-outline-danger:hover {
        background-color: var(--danger) !important;
        color: white !important;
    }

    .btn-outline-info {
        color: var(--primary) !important;
        border-color: var(--primary) !important;
        background: transparent !important;
    }

    .btn-outline-info:hover {
        background-color: var(--primary) !important;
        color: white !important;
    }

    .btn-save {
        width: 100%;
        background: var(--primary);
        color: white;
        font-weight: 600;
        padding: 1rem;
        border-radius: 12px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2);
        cursor: pointer;
    }

    .btn-save:hover:not(:disabled) {
        background: var(--primary-hover);
        transform: translateY(-1px);
        box-shadow: 0 6px 8px -1px rgba(59, 130, 246, 0.3);
    }

    .btn-save:disabled {
        background: var(--text-secondary);
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Back Button */
    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-secondary);
        text-decoration: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .back-btn:hover {
        color: var(--primary);
        background: var(--hover-bg);
    }

    /* ==================== TABLES ==================== */
    .table {
        width: 100%;
        color: var(--text-primary) !important;
        border-collapse: collapse;
    }

    .table thead th {
        background-color: var(--table-header) !important;
        color: var(--text-primary) !important;
        border-bottom: 2px solid var(--border-color) !important;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.75rem;
        text-align: left;
    }

    .table td, .table th {
        border-color: var(--border-color) !important;
        padding: 0.75rem;
        font-size: 0.875rem;
        border-bottom: 1px solid var(--border-color);
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: var(--hover-bg) !important;
    }

    /* ==================== TOOLTIPS ==================== */
    .fa-info-circle {
        color: var(--info);
        cursor: help;
        margin-left: 0.25rem;
        position: relative;
    }

    .fa-info-circle::after {
        content: attr(data-tooltip);
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        background-color: var(--card-bg);
        color: var(--text-primary);
        padding: 0.5rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        white-space: nowrap;
        font-size: 0.75rem;
        box-shadow: var(--shadow);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease;
        z-index: 1000;
        pointer-events: none;
    }

    .fa-info-circle:hover::after {
        opacity: 1;
        visibility: visible;
    }

    /* ==================== GRADE BADGE ==================== */
    .grade-badge {
        background: var(--badge-bg);
        color: var(--badge-text);
        border: 1px solid var(--border-color);
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* ==================== RANGE WARNING ==================== */
    .range-warning {
        position: absolute;
        bottom: 0.5rem;
        left: 0;
        width: 100%;
        display: flex;
        justify-content: center;
    }

    .range-warning span {
        font-size: 0.75rem;
        font-weight: 600;
        background: var(--warning-light);
        color: var(--warning);
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* ==================== SUMMARY INFO ==================== */
    .summary-info {
        background-color: var(--hover-bg);
        padding: 1rem;
        border-radius: 8px;
        margin: 0 1.5rem 1.5rem;
        border: 1px solid var(--border-color);
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .summary-info span {
        font-size: 0.875rem;
        color: var(--text-primary);
    }

    /* ==================== EMPLOYER CELL ==================== */
    .employer-cell {
        background-color: var(--employer-cell) !important;
        color: var(--text-primary) !important;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        padding: 1rem !important;
    }

    .vertical-text {
        writing-mode: vertical-rl;
        text-orientation: mixed;
        transform: rotate(180deg);
        font-size: 0.875rem;
        color: var(--primary);
    }

    /* ==================== SIMPLE ICON ==================== */
    .simple-icon {
        color: var(--primary);
        cursor: pointer;
        font-size: 1.2rem;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }

    .simple-icon:hover {
        opacity: 0.8;
        transform: scale(1.1);
    }

    /* ==================== ARROW ==================== */
    .arrow-up {
        color: var(--arrow);
        font-weight: bold;
        font-size: 1.2rem;
    }

    /* ==================== TOGGLE SWITCH ==================== */
    .form-check-input {
        background-color: var(--input-bg);
        border-color: var(--border-color);
        cursor: pointer;
        width: 2rem;
        height: 1rem;
        border-radius: 9999px;
        appearance: none;
        -webkit-appearance: none;
        position: relative;
        transition: all 0.2s;
    }

    .form-check-input:checked {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .form-check-input::before {
        content: '';
        position: absolute;
        width: 0.75rem;
        height: 0.75rem;
        background-color: white;
        border-radius: 50%;
        top: 50%;
        left: 0.125rem;
        transform: translateY(-50%);
        transition: left 0.2s;
    }

    .form-check-input:checked::before {
        left: 1.125rem;
    }

    /* ==================== DARK MODE TOGGLE ==================== */
    .dark-mode-toggle {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--card-bg);
        border: 2px solid var(--border-color);
        color: var(--text-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: var(--shadow-lg);
        transition: all 0.3s ease;
        font-size: 1.25rem;
    }

    .dark-mode-toggle:hover {
        transform: scale(1.1);
        border-color: var(--primary);
        color: var(--primary);
    }

    /* ==================== TOAST NOTIFICATION ==================== */
    #modeToast {
        position: fixed;
        bottom: 80px;
        right: 20px;
        background: var(--card-bg);
        color: var(--text-primary);
        padding: 12px 24px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-lg);
        z-index: 9999;
        font-size: 0.875rem;
        font-weight: 500;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* ==================== LOADING SPINNER ==================== */
    .calc-spinner {
        width: 1rem;
        height: 1rem;
        border: 2px solid var(--primary);
        border-right-color: transparent;
        border-radius: 50%;
        animation: spinner-rotate 0.75s linear infinite;
    }

    @keyframes spinner-rotate {
        to { transform: rotate(360deg); }
    }

    /* ==================== COMPONENT LIST STYLES ==================== */
    .component-list {
        max-height: 500px;
        overflow-y: auto;
        padding-right: 5px;
    }

    .component-list::-webkit-scrollbar {
        width: 6px;
    }

    .component-list::-webkit-scrollbar-track {
        background: var(--hover-bg);
        border-radius: 10px;
    }

    .component-list::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 10px;
    }

    /* ==================== RESPONSIVE ==================== */
    @media (max-width: 1024px) {
        .metrics-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .earnings-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .salary-calculator {
            padding: 16px;
        }

        .input-group-calc {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
            width: 100%;
        }

        .input-group-calc .btn-group {
            margin-right: 0;
            justify-content: center;
            width: 100%;
        }

        .input-group-calc .btn-group .btn {
            flex: 1;
        }

        .input-wrapper {
            border-left: none;
            border-top: 1px solid var(--border-color);
            padding-left: 0;
            padding-top: 1rem;
            justify-content: center;
        }

        .summary-card {
            position: static;
            margin-top: 1rem;
        }

        .summary-info {
            flex-direction: column;
            gap: 0.5rem;
        }

        .input-card {
            padding: 1rem;
        }
    }

    @media (max-width: 576px) {
        .metrics-grid {
            grid-template-columns: 1fr;
        }

        .btn-group {
            flex-wrap: wrap;
        }
    }

    /* ==================== PRINT STYLES ==================== */
    @media print {
        .no-print {
            display: none !important;
        }

        .summary-card {
            position: static;
            box-shadow: none;
        }
    }

    /* ==================== TRANSITIONS ==================== */
    .calc-card,
    .input-card,
    .metric-card,
    .summary-card,
    .section-card,
    .earning-item,
    .earning-input-group,
    .btn,
    .grade-badge,
    .dark-mode-toggle {
        transition: background-color 0.3s ease,
                    border-color 0.3s ease,
                    color 0.3s ease,
                    box-shadow 0.3s ease !important;
    }

    /* ==================== HARDWARE ACCELERATION ==================== */
    .calc-card,
    .input-card,
    .metric-card,
    .summary-card,
    .section-card {
        transform: translateZ(0);
        backface-visibility: hidden;
        perspective: 1000px;
    }
    /* ==================== DEDUCTION INPUT STYLES ==================== */
.deduction-input-item {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 0.75rem;
    margin-bottom: 0.75rem;
    transition: all 0.2s ease;
}

.deduction-input-item:hover {
    border-color: var(--primary);
    box-shadow: var(--shadow);
}

.deduction-input-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    font-size: 0.85rem;
}

.deduction-input-title {
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.deduction-input-title i {
    color: var(--danger);
    font-size: 0.75rem;
}

.deduction-input-badge {
    font-size: 0.65rem;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    background: var(--hover-bg);
    color: var(--text-secondary);
}

.deduction-input-field {
    display: flex;
    align-items: center;
    border: 1px solid var(--input-border);
    border-radius: 6px;
    padding: 0.4rem 0.5rem;
    background: var(--input-bg);
}

.deduction-input-field:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

.deduction-input-currency {
    color: var(--text-secondary);
    font-size: 0.8rem;
    margin-right: 0.2rem;
    font-weight: 600;
}

.deduction-input-number {
    background: transparent;
    border: none;
    color: var(--input-text);
    font-weight: 600;
    font-size: 0.85rem;
    width: 100%;
    outline: none;
    -moz-appearance: textfield;
}

.deduction-input-number::-webkit-outer-spin-button,
.deduction-input-number::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* ==================== VERTICAL DEDUCTION INPUT STYLES ==================== */
.deduction-input-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    transition: all 0.2s ease;
    width: 100%;
}

.deduction-input-card:last-child {
    margin-bottom: 0;
}

.deduction-input-card:hover {
    border-color: var(--danger);
    box-shadow: var(--shadow);
}

.deduction-input-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px dashed var(--border-color);
}

.deduction-input-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
}

.deduction-input-title i {
    color: var(--danger);
    font-size: 1rem;
    width: 20px;
}

.deduction-input-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.6rem;
    border-radius: 4px;
    background: var(--hover-bg);
    color: var(--text-secondary);
    white-space: nowrap;
}

.deduction-input-body {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.deduction-input-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.deduction-input-label {
    font-size: 0.9rem;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 6px;
    flex: 1;
}

.deduction-input-label i {
    color: var(--danger);
    font-size: 0.8rem;
    width: 16px;
}

.deduction-input-value {
    display: flex;
    align-items: center;
    gap: 12px;
    justify-content: flex-end;
    flex: 1;
}

.deduction-amount-display {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--danger);
    min-width: 80px;
    text-align: right;
    white-space: nowrap;
}

.deduction-input-field {
    display: flex;
    align-items: center;
    border: 1px solid var(--input-border);
    border-radius: 6px;
    padding: 0.35rem 0.5rem;
    background: var(--input-bg);
    width: 100px;
    transition: all 0.2s;
}

.deduction-input-field:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

.deduction-input-field .currency {
    color: var(--text-secondary);
    font-size: 0.8rem;
    margin-right: 2px;
    font-weight: 500;
}

.deduction-input-field input {
    background: transparent;
    border: none;
    color: var(--input-text);
    font-weight: 600;
    font-size: 0.85rem;
    width: 100%;
    outline: none;
    -moz-appearance: textfield;
}

.deduction-input-field input::-webkit-outer-spin-button,
.deduction-input-field input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.deduction-toggle-switch {
    display: flex;
    align-items: center;
}

.deduction-toggle-switch .form-check-input {
    width: 2rem;
    height: 1rem;
    margin: 0;
    cursor: pointer;
}

/* Compact mode adjustments */
.compact-mode .deduction-input-card {
    padding: 12px;
    margin-bottom: 8px;
}

.compact-mode .deduction-input-title {
    font-size: 0.9rem;
}

.compact-mode .deduction-input-title i {
    font-size: 0.9rem;
}

.compact-mode .deduction-input-badge {
    font-size: 0.65rem;
    padding: 0.15rem 0.4rem;
}

.compact-mode .deduction-input-label {
    font-size: 0.8rem;
}

.compact-mode .deduction-amount-display {
    font-size: 0.85rem;
    min-width: 70px;
}

.compact-mode .deduction-input-field {
    width: 85px;
    padding: 0.25rem 0.4rem;
}

.compact-mode .deduction-input-field .currency {
    font-size: 0.7rem;
}

.compact-mode .deduction-input-field input {
    font-size: 0.75rem;
}


/* Employee Status Badges */
.emp-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-left: 0.5rem;
}

.emp-status-badge.pf-enabled {
    background: var(--primary-light);
    color: var(--primary);
}

.emp-status-badge.esi-enabled {
    background: var(--success-light);
    color: var(--success);
}

.emp-status-badge.disabled {
    background: var(--danger-light);
    color: var(--danger);
}

/* Info Tooltip */
.info-tooltip {
    position: relative;
    cursor: help;
    margin-left: 0.25rem;
    color: var(--text-secondary);
    font-size: 0.7rem;
}

.info-tooltip:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: var(--card-bg);
    color: var(--text-primary);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    white-space: nowrap;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow);
    z-index: 1000;
}

/* Toggle Switch for Deductions */
.deduction-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.deduction-toggle .form-check-input {
    width: 2rem;
    height: 1rem;
    margin: 0;
}

.deduction-toggle-label {
    font-size: 0.75rem;
    color: var(--text-secondary);
}
</style>
@endsection

@section('content')
<div class="salary-calculator">
    <!-- Dark Mode Toggle -->
    <button class="dark-mode-toggle no-print" id="darkModeToggle" type="button" title="Toggle Dark/Light Mode">
        <i class="fas fa-moon" id="darkModeIcon"></i>
    </button>
    <!-- Compact Mode Toggle -->
<button class="compact-toggle-btn no-print" id="compactModeToggle" type="button" title="Toggle Compact Mode">
    <i class="fas fa-compress-alt" id="compactModeIcon"></i>
</button>

    <!-- Back Button & Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 no-print">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('employee.payroll') }}" class="back-btn">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Employees
            </a>
            <span class="text-muted">/</span>
            <span class="text-muted">
                Structuring for: <strong class="text-dark">{{ $employee->emp_full_name ?? 'New Employee' }}</strong>
            </span>
        </div>
        @if(isset($employee->grade))
        <div class="grade-badge">
            <i class="fas fa-briefcase me-1"></i>
            {{ $employee->grade->grade_name ?? 'Grade ' . $employee->grade_id }}
        </div>
        @endif
    </div>

    <!-- Input Card -->
    <div class="input-card no-print">
        <div class="input-badge">Salary Calculator</div>

        <div class="d-flex justify-content-center">
            <div class="input-group-calc">
                <div class="btn-group">
                    <button type="button"
                            class="btn period-btn {{ $inputPeriod == 'monthly' ? 'active' : '' }}"
                            data-period="monthly">
                        Monthly
                    </button>
                    <button type="button"
                            class="btn period-btn {{ $inputPeriod == 'annual' ? 'active' : '' }}"
                            data-period="annual">
                        Annual
                    </button>
                </div>

                <div class="input-wrapper">
                    <span class="currency-symbol">₹</span>
                    <input type="number"
                           id="inputAmount"
                           class="form-control border-0 p-0"
                           value="{{ $inputVal ?? ($emp_salary->es_annual_ctc ?? 450000) }}"
                           placeholder="0">
                </div>
            </div>
        </div>

        @if($isOutOfRange ?? false)
        <div class="range-warning">
            <span>
                <i class="fas fa-exclamation-triangle me-1"></i>
                Range: {{ number_format($grade->grade_min_ctc ?? 300000) }} - {{ number_format($grade->grade_max_ctc ?? 600000) }}
            </span>
        </div>
        @endif
    </div>

    <!-- Metrics Dashboard -->
    <div class="metrics-grid mb-4 no-print">
        <div class="metric-card">
            <div class="metric-label">Monthly Gross</div>
            <div class="metric-value" id="monthlyGross">₹0</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Annual Gross</div>
            <div class="metric-value" id="annualGross">₹0</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Monthly CTC</div>
            <div class="metric-value" id="monthlyCTC">₹0</div>
        </div>
        <div class="metric-card highlight">
            <div class="metric-label">Annual CTC</div>
            <div class="metric-value" id="annualCTC">₹0</div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="row g-4">
        <!-- Left Column: Breakdown -->
        <div class="col-lg-8">
            <!-- Mode Switcher -->
            <div class="mode-switcher no-print">
                @if(($payrollMasterMode ?? $entryMode ?? 'auto') === 'auto')
                    <button type="button"
                            class="btn entry-mode-btn active"
                            data-mode="auto">
                        <i class="fas fa-sync-alt me-1"></i>
                        Auto-Calculate
                    </button>
                @else
                    <button type="button"
                            class="btn entry-mode-btn active"
                            data-mode="manual">
                        <i class="fas fa-edit me-1"></i>
                        Manual Mode
                    </button>
                @endif
            </div>

        <!-- Earnings Section - Input Fields -->
        <div class="calc-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-semibold mb-0">Earnings Components</h5>
                <small class="text-muted">Enter Monthly Values</small>
            </div>

            <!-- Earnings Grid - 2x2 Layout -->
            <div class="earnings-grid" id="earningsGrid">
                <!-- Grid will be populated dynamically -->
            </div>

            <!-- Total Gross -->
            <div class="total-gross mt-3" id="totalGross">
                <div class="total-gross-row">
                    <span>TOTAL GROSS</span>
                    <span id="totalGrossValue">₹34,928</span>
                </div>
                <small id="grossBalanceValue" class="gross-balance-text">Remaining to match gross: ₹0</small>
            </div>

            <!-- Mismatch Warning -->
            {{-- <div id="mismatchWarning" class="mismatch-warning" style="display: none;">
                <i class="fas fa-exclamation-circle mt-1"></i>
                <div>
                    <strong>Total Mismatch!</strong>
                    <p id="mismatchMessage"></p>
                </div>
            </div> --}}
        </div>

            <div class="row g-4 align-items-stretch">
                <!-- Deductions Section - Employee (Left Side) -->
                <div class="col-md-6">
                    <div class="section-card h-100">
                        <div class="section-header">
                            <h4>Employee deductions</h4>
                        </div>
                        <div class="section-body" id="deductionsInputContainer">
                            <!-- Deduction input fields will be shown here based on employee settings -->
                            <div class="text-muted text-center py-3" id="deductionsLoading">Loading deduction settings...</div>
                        </div>
                        <div class="section-footer" id="totalDeductions">
                            <span>Total deductions</span>
                            <span class="text-muted">- ₹0</span>
                        </div>
                    </div>
                </div>

                <!-- Employer Contributions Section -->
                <div class="col-md-6">
                    <div class="section-card h-100">
                        <div class="section-header">
                            <h4></i> Employer deductions</h4>
                        </div>
                        <div class="section-body" id="employerContributionsContainer">
                            <!-- Employer deductions will be displayed here -->
                            <div class="text-muted text-center py-3" id="employerLoading">No employer deductions</div>
                        </div>
                        <div class="section-footer" id="totalEmployerContributions">
                            <span>Total employer deductions</span>
                            <span class="text-muted">+ ₹0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Summary -->
        <div class="col-lg-4">
            <div class="summary-card">
                <div class="summary-header">
                    <div class="summary-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="summary-title">
                        <h3>Net Pay Preview</h3>
                        <p>Estimated Take Home</p>
                    </div>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Gross Salary</span>
                    <span class="summary-value" id="summaryGross">₹0</span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Employee deductions</span>
                    <span class="summary-value text-muted" id="summaryDeductions">-₹0</span>
                </div>

                <div class="summary-row small border-top pt-2 mt-1" id="taxSlabPanel" style="display: none;">
                    <div class="w-100">
                        <div class="d-flex justify-content-between align-items-baseline mb-1">
                            <span class="text-muted">Tax regime</span>
                            <span class="text-end" id="taxRegimeLabel">—</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline mb-1">
                            <span class="text-muted">Est. taxable income (p.a.)</span>
                            <span class="text-end fw-medium" id="taxTaxableIncome">—</span>
                        </div>
                        <p class="text-muted small mb-0" id="taxMarginalSlab">—</p>
                    </div>
                </div>

                <div class="summary-row total">
                    <span class="summary-label">Net Pay</span>
                    <span class="summary-value large" id="summaryNet">₹0</span>
                </div>

                <div class="summary-progress">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Annual CTC Cost</span>
                        <span class="fw-semibold" id="summaryAnnualCTC">₹0</span>
                    </div>
                    <div class="progress-bar-calc">
                        <div class="progress-fill" id="ctcProgress" style="width: 100%;"></div>
                    </div>
                </div>

                <button type="button"
                        class="btn-save no-print"
                        id="saveButton">
                    <i class="fas fa-check-circle me-2"></i>
                    <span id="saveButtonText">Confirm Structure</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden inputs -->
<input type="hidden" id="employeeId" value="{{ $employee->emp_id }}">
<input type="hidden" id="businessId" value="{{ $business_id }}">
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ==================== DUAL MODE - LIGHT & DARK ====================


// ==================== COMPONENT DATA FROM DATABASE ====================
// Map the earnings data to include sa_title
const earningsData = @json($earnings ?? []);
const earningsWithTitles = earningsData.map(item => {
    return {
        ...item,
        display_name: item.sa_title || item.name || 'Earning Component'
    };
});

const salaryComponents = {
    earnings: earningsWithTitles,
    deductions: @json($deductions ?? []),
    employer: @json($employerDeductions ?? [])
};

const existingValues = {
    earnings: @json($emp_earnings ?? (object)[]),     // Changed from $existingEarnings
    deductions: @json($emp_deductions ?? (object)[]), // Changed from $existingDeductions
    employer: @json($emplyer_deductions ?? (object)[]) // Changed from $existingEmployer
};

console.log('Salary Components:', salaryComponents);
console.log('Existing Values:', existingValues);

// ==================== STATE MANAGEMENT ====================
const salaryCalculatorData = {
    employee: @json($employee ?? null),
    components: @json($allComponents ?? []),
    existingEarnings: @json($existingEarnings ?? (object)[]),
    existingDeductions: @json($existingDeductions ?? (object)[]),
    existingEmployer: @json($existingEmployer ?? (object)[]),
    empSalary: @json($emp_salary ?? null),
    businessId: '{{ $business_id ?? '' }}',
    empActualId: '{{ $emp_actual_id ?? '' }}'
};

let calcMode = '{{ $calcMode ?? "ctc" }}';
let inputPeriod = '{{ $inputPeriod ?? "annual" }}';
let entryMode = '{{ $entryMode ?? "auto" }}';
let inputVal = parseFloat('{{ $inputVal ?? ($emp_salary->es_annual_ctc ?? 450000) }}');
let manualEarnings = {};
let currentBreakdown = null;
let components = salaryCalculatorData.components || [];

const defaultTdsFyId = @json(isset($defaultTdsFyId) ? (int) $defaultTdsFyId : null);
const previewMonthlyTdsUrl = @json(route('payroll.preview-monthly-tds'));
const csrfToken = @json(csrf_token());
let tdsFetchSeq = 0;
let calcDebounceTimer = null;
const CALC_DEBOUNCE_MS = 280;

/**
 * Read manual PF/PT from deduction input cards (when user overrides amounts).
 * Returns { pf, pt } with null for any key not present in UI so caller can fall back to breakdown.
 */
function getDomStatutoryAmountsForTds() {
    let pf = null;
    let pt = null;
    let hasPf = false;
    let hasPt = false;

    const root = document.getElementById('deductionsInputContainer');
    if (!root) {
        return { pf, pt, hasPf, hasPt };
    }

    root.querySelectorAll('input.deduction-field, input[id^="lwf_deduction_"]').forEach((input) => {
        if (input.disabled) return;
        const name = (input.dataset.name || '').toLowerCase();
        const raw = parseFloat(input.value);
        const val = Number.isFinite(raw) ? raw : 0;
        const tid = String(input.dataset.typeId || '');

        if (tid === '351' || name.includes('provident') || (name.includes('pf') && !name.includes('employer'))) {
            pf = val;
            hasPf = true;
        }
        if (tid === '353' || name.includes('professional') || name.includes('profession')) {
            pt = val;
            hasPt = true;
        }
    });

    return { pf, pt, hasPf, hasPt };
}

function scheduleUpdateCalculator() {
    clearTimeout(calcDebounceTimer);
    calcDebounceTimer = setTimeout(() => {
        calcDebounceTimer = null;
        updateCalculator();
    }, CALC_DEBOUNCE_MS);
}

// ==================== UTILITIES ====================
function formatNumber(num) {
    return (num === undefined || num === null || isNaN(num)) ? '0' : Math.round(num).toLocaleString('en-IN');
}

function getEarningComponentId(earning) {
    return earning.id || earning.sa_id || earning.es_sa_id || '';
}

function resolveExistingEarningValue(earning) {
    const existing = existingValues.earnings || {};
    const displayName = earning.display_name || earning.name || earning.sa_title || 'Earning Component';
    const keys = [
        earning.id,
        earning.sa_id,
        earning.es_sa_id,
        earning.sa_earning_type_id,
        earning.earningTypeId,
        displayName,
        displayName.toLowerCase(),
    ].filter(key => key !== undefined && key !== null && key !== '');

    for (const key of keys) {
        if (existing[key] !== undefined && existing[key] !== null) {
            return parseFloat(existing[key]) || 0;
        }
    }

    return 0;
}

function seedManualEarningsFromSaved() {
    const seeded = {};
    (salaryComponents.earnings || []).forEach((earning) => {
        const displayName = earning.display_name || earning.name || earning.sa_title || 'Earning Component';
        const value = resolveExistingEarningValue(earning);
        if (value > 0) {
            seeded[displayName] = value;
        }
    });

    manualEarnings = seeded;
}

seedManualEarningsFromSaved();

function getInputMonthlyGrossTarget() {
    return inputPeriod === 'annual' ? (inputVal / 12) : inputVal;
}

function updateGrossBalance(totalEarnings) {
    const balanceEl = document.getElementById('grossBalanceValue');
    if (!balanceEl) return;

    const targetGross = getInputMonthlyGrossTarget();
    const diff = targetGross - (parseFloat(totalEarnings) || 0);
    const absDiff = Math.abs(diff);

    balanceEl.classList.remove('remaining', 'excess', 'matched');

    if (absDiff <= 1) {
        balanceEl.textContent = 'Gross matched';
        balanceEl.classList.add('matched');
        return;
    }

    if (diff > 0) {
        balanceEl.textContent = `Remaining to match gross: ₹${formatNumber(diff)}`;
        balanceEl.classList.add('remaining');
        return;
    }

    balanceEl.textContent = `Excess over gross: ₹${formatNumber(absDiff)}`;
    balanceEl.classList.add('excess');
}

function getCalculationTypeText(type) {
    const types = {
        '346': '% of CTC',
        '347': '% of Basic',
        '348': 'Fixed Amount',
        'percentage_basic': '% of Basic',
        'percentage_ctc': '% of CTC',
        'flat': 'Fixed Amount',
        'fixed': 'Fixed Amount'
    };
    return types[type] || 'Fixed Amount';
}

// ==================== DISPLAY EARNINGS IN GRID ====================
function displayEarningsGrid() {
    const container = document.getElementById('earningsGrid');
    if (!container) return;

    let html = '';
    let totalEarnings = 0;

    if (salaryComponents.earnings && salaryComponents.earnings.length > 0) {
        // Separate earnings into two columns
        const earningsList = [...salaryComponents.earnings];

        // Find Basic component to ensure it's in first column
        const basicIndex = earningsList.findIndex(e =>
            e.display_name && e.display_name.toLowerCase().includes('basic')
        );

        // Reorder to put Basic first if found
        if (basicIndex > -1) {
            const basic = earningsList.splice(basicIndex, 1)[0];
            earningsList.unshift(basic);
        }

        // Split earnings into two roughly equal columns
        const midPoint = Math.ceil(earningsList.length / 2);
        const leftColumnEarnings = earningsList.slice(0, midPoint);
        const rightColumnEarnings = earningsList.slice(midPoint);

        // Build left column
        let leftColumnItems = '';
        let leftColumnTotal = 0;

        leftColumnEarnings.forEach(earning => {
            const earningId = getEarningComponentId(earning);
            const value = resolveExistingEarningValue(earning);

            leftColumnTotal += value;
            totalEarnings += value;

            const displayName = earning.display_name || earning.name || earning.sa_title || 'Earning Component';
            const calcType = earning.calcType || earning.type || 'fixed';
            const calcValue = earning.value || 0;
            const typeText = getCalculationTypeText(calcType);

            // Check if this is a special allowance to highlight
            const isHighlight = displayName.toLowerCase().includes('special') ||
                               displayName.toLowerCase().includes('conveyance');

            leftColumnItems += `
                <div class="earning-card-item">
                    <span class="earning-card-label" title="${typeText} ${calcValue}${calcType.includes('percentage') ? '%' : ''}">
                        ${displayName}
                        ${calcType.includes('percentage') ?
                            `<small class="text-muted ms-1">(${calcValue}%)</small>` :
                            ''}
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        <div class="earning-card-input">
                            <span class="currency">₹</span>
                            <input type="number"
                                   class="earning-field"
                                   data-id="${earningId}"
                                   data-name="${displayName}"
                                   data-calctype="${calcType}"
                                   data-value="${calcValue}"
                                   value="${value}"
                                   min="0"
                                   step="100"
                                   placeholder="0"
                                   oninput="handleEarningChange(this)">
                        </div>

                    </div>
                </div>
            `;
        });

        // Build right column
        let rightColumnItems = '';
        let rightColumnTotal = 0;

        rightColumnEarnings.forEach(earning => {
            const earningId = getEarningComponentId(earning);
            const value = resolveExistingEarningValue(earning);

            rightColumnTotal += value;
            totalEarnings += value;

            const displayName = earning.display_name || earning.name || earning.sa_title || 'Earning Component';
            const calcType = earning.calcType || earning.type || 'fixed';
            const calcValue = earning.value || 0;
            const typeText = getCalculationTypeText(calcType);

            // Check if this is a special allowance to highlight
            const isHighlight = displayName.toLowerCase().includes('special') ||
                               displayName.toLowerCase().includes('conveyance');

            rightColumnItems += `
                <div class="earning-card-item">
                    <span class="earning-card-label" title="${typeText} ${calcValue}${calcType.includes('percentage') ? '%' : ''}">
                        ${displayName}
                        ${calcType.includes('percentage') ?
                            `<small class="text-muted ms-1">(${calcValue}%)</small>` :
                            ''}
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        <div class="earning-card-input">
                            <span class="currency">₹</span>
                            <input type="number"
                                   class="earning-field"
                                   data-id="${earningId}"
                                   data-name="${displayName}"
                                   data-calctype="${calcType}"
                                   data-value="${calcValue}"
                                   value="${value}"
                                   min="0"
                                   step="100"
                                   placeholder="0"
                                   oninput="handleEarningChange(this)">
                        </div>

                    </div>
                </div>
            `;
        });

        // Create the grid with dynamic titles
        html = `
            <div class="earning-card">
                <div class="earning-card-title">Earnings (${leftColumnEarnings.length} components)</div>
                ${leftColumnItems}
            </div>
            <div class="earning-card">
                <div class="earning-card-title">Other Allowances (${rightColumnEarnings.length} components)</div>
                ${rightColumnItems}
            </div>
        `;
    } else {
        html = '<div class="text-muted text-center py-4" style="grid-column: 1/-1;">No earnings components found</div>';
    }

    container.innerHTML = html;
    document.getElementById('totalGrossValue').textContent = '₹' + formatNumber(totalEarnings);
    updateGrossBalance(totalEarnings);

    console.log(totalEarnings);
    return totalEarnings;
}

// Update the initialization to use the grid display
function displayEarningsInputs() {
    return displayEarningsGrid();
}


// Handle earning input change
window.handleEarningChange = function(input) {
    const value = parseFloat(input.value) || 0;
    const name = input.dataset.name;
    const id = input.dataset.id;

    // Store in manualEarnings object
    if (name) {
        manualEarnings[name] = value;
    }

    // Update existingValues for persistence
    if (id && existingValues.earnings) {
        existingValues.earnings[id] = value;
    }

    // Recalculate totals - this updates totalGrossValue
    const totalEarnings = calculateTotals();

    // Update contributions and deductions
    calculateAndDisplayContributions();

    // Update employee deductions if function exists
    if (typeof displayEmployeeDeductions === 'function') {
        displayEmployeeDeductions();
    }

    // Update the amount display next to input
    const amountSpan = input.closest('.earning-card-item').querySelector('.earning-card-amount');
    if (amountSpan) {
        amountSpan.textContent = formatNumber(value);
    }

    // Update net pay in summary
    updateNetPay();

    scheduleUpdateCalculator();
};

// Update net pay based on earnings and deductions
function updateNetPay() {
    const totalEarnings = parseFloat(document.getElementById('totalGrossValue').textContent.replace('₹', '').replace(/,/g, '')) || 0;
    const deductionsText = document.getElementById('totalDeductionsPreview')?.innerText || '- ₹0';
    const deductions = parseFloat(deductionsText.replace(/[^0-9-]/g, '')) || 0;
    const netPay = totalEarnings - Math.abs(deductions);

    document.getElementById('summaryNet').textContent = '₹' + formatNumber(netPay);
}

// Calculate total earnings from inputs - UPDATES total gross display
function calculateTotals() {
    let totalEarnings = 0;
    document.querySelectorAll('.earning-field').forEach(input => {
        totalEarnings += parseFloat(input.value) || 0;
    });

    // Update the total gross display
    document.getElementById('totalGrossValue').textContent = '₹' + formatNumber(totalEarnings);
    updateGrossBalance(totalEarnings);

    // Also update summary gross
    document.getElementById('summaryGross').textContent = '₹' + formatNumber(totalEarnings);

    return totalEarnings;
}



// ==================== EMPLOYEE DEDUCTION SETTINGS ====================
const employeeSettings = {
    is_pf_enabled: {{ $employee->emp_is_pf_enabled ?? 0 }},
    emp_esic_limit: {{ $employee->emp_esic_limit ?? 0 }},
    emp_pf_number: '{{ $employee->emp_pf_number ?? '' }}',
    emp_esi_number: '{{ $employee->emp_esi_number ?? '' }}'
};

/** Aligned with weekly-salary-master: 120 = PF/ESIC enabled for this employee */
function isEmployeePfEnabled() {
    return parseInt(employeeSettings.is_pf_enabled, 10) === 120;
}

function isEmployeeEsicEnabled() {
    return parseInt(employeeSettings.emp_esic_limit, 10) === 120;
}

function isPfComponent(name, typeId = null) {
    const text = (name || '').toLowerCase();
    return parseInt(typeId, 10) === 351 || text.includes('provident') || text.includes('pf') || text.includes('epf');
}

function isEsicComponent(name, typeId = null) {
    const text = (name || '').toLowerCase();
    return parseInt(typeId, 10) === 352 || text.includes('esi') || text.includes('esic');
}

function shouldShowEmployeeDeduction(name, typeId = null) {
    if (isPfComponent(name, typeId)) {
        return isEmployeePfEnabled();
    }

    if (isEsicComponent(name, typeId)) {
        return isEmployeeEsicEnabled();
    }

    return true;
}

// ==================== DISPLAY DEDUCTION INPUTS (tabular, same pattern as preview) ====================
function displayDeductionInputs() {
    const container = document.getElementById('deductionsInputContainer');
    if (!container) return;

    let totalDeductions = 0;
    const existingDeductionValues = existingValues.deductions || {};
    const deductions = salaryComponents.deductions || [];
    const rows = [];

    deductions.forEach((deduction) => {
        const typeId = deduction.std_deduction_type_id || deduction.type_id;
        const deductionName = deduction.deduction_type_name || deduction.name;
        const employeeRate = parseFloat(deduction.std_employee_contri_rate_amount) || 0;
        const employerRate = parseFloat(deduction.std_employer_contri_rate_amount) || 0;
        const threshold = parseFloat(deduction.std_threshold) || 0;

        let isEnabled = true;
        let badgeText = '';

        if (isPfComponent(deductionName, typeId)) {
            badgeText = employeeRate + '% of basic';
            isEnabled = isEmployeePfEnabled();
        } else if (isEsicComponent(deductionName, typeId)) {
            badgeText = employeeRate + '% of gross';
            isEnabled = isEmployeeEsicEnabled();
        } else if (deductionName.toLowerCase().includes('professional') || deductionName.toLowerCase().includes('tax')) {
            badgeText = 'Fixed';
            isEnabled = true;
        } else if (
            deductionName.toLowerCase().includes('labour') ||
            deductionName.toLowerCase().includes('welfare') ||
            deductionName.toLowerCase().includes('lwf')
        ) {
            badgeText = 'Fixed';
            isEnabled = true;
        }

        if (!isEnabled) return;

        const valueKey = deductionName.toLowerCase().replace(/\s+/g, '_');
        const deductionValue =
            existingDeductionValues[valueKey] ||
            existingDeductionValues[deductionName] ||
            existingDeductionValues[typeId] ||
            0;

        totalDeductions += parseFloat(deductionValue) || 0;

        const isLwf =
            deductionName.toLowerCase().includes('labour') ||
            deductionName.toLowerCase().includes('lwf');

        if (isLwf) {
            const lwfDisabled = !(deductionValue > 0);
            rows.push(`
                <div class="earning-row">
                    <div class="earning-info min-w-0">
                        <span class="earning-name" title="${deductionName}">${deductionName}</span>
                        <span class="earning-badge-sm ms-1">${badgeText}</span>
                    </div>
                    <div class="earning-input-wrapper" style="flex-wrap: wrap; justify-content: flex-end; gap: 0.5rem;">
                        <div class="form-check m-0">
                            <input type="checkbox"
                                   class="form-check-input"
                                   id="lwf_toggle_${typeId}"
                                   ${deductionValue > 0 ? 'checked' : ''}
                                   onchange="toggleLWF(this, '${typeId}')">
                        </div>
                        <div class="earning-input-group-sm"
                             id="lwf_input_container_${typeId}"
                             style="${lwfDisabled ? 'opacity:0.5;pointer-events:none;' : ''}">
                            <span class="currency-sm">₹</span>
                            <input type="number"
                                   class="deduction-field"
                                   id="lwf_deduction_${typeId}"
                                   data-type="lwf"
                                   data-name="${deductionName}"
                                   data-type-id="${typeId}"
                                   value="${deductionValue}"
                                   min="0"
                                   max="500"
                                   step="10"
                                   placeholder="0"
                                   ${lwfDisabled ? 'disabled' : ''}
                                   oninput="handleDeductionInput(this)">
                        </div>
                    </div>
                </div>`);
        } else {
            rows.push(`
                <div class="earning-row">
                    <div class="earning-info min-w-0">
                        <span class="earning-name" title="${deductionName}">${deductionName}</span>
                        <span class="earning-badge-sm ms-1">${badgeText}</span>
                    </div>
                    <div class="earning-input-wrapper">
                        <div class="earning-input-group-sm">
                            <span class="currency-sm">₹</span>
                            <input type="number"
                                   class="deduction-field"
                                   data-type="${deductionName.toLowerCase()}"
                                   data-name="${deductionName}"
                                   data-type-id="${typeId}"
                                   data-employee-rate="${employeeRate}"
                                   data-employer-rate="${employerRate}"
                                   data-threshold="${threshold}"
                                   value="${deductionValue}"
                                   min="0"
                                   step="10"
                                   placeholder="0"
                                   oninput="handleDeductionInput(this)">
                        </div>
                    </div>
                </div>`);
        }
    });

    let html = '';
    if (rows.length === 0) {
        html = `
            <div class="earnings-container deductions-input-table">
                <div class="earnings-header">
                    <span>Component</span>
                    <span>Amount</span>
                </div>
                <div class="earnings-body">
                    <div class="text-muted text-center py-4">No deductions apply for this employee</div>
                </div>
            </div>`;
    } else {
        html = `
            <div class="earnings-container deductions-input-table">
                <div class="earnings-header">
                    <span>Component</span>
                    <span>Amount</span>
                </div>
                <div class="earnings-body">
                    ${rows.join('')}
                </div>
            </div>`;
    }

    container.innerHTML = html;
    const td = document.getElementById('totalDeductions');
    if (td) {
        td.innerHTML = `<span>Total deductions</span><span class="text-muted">- ₹${formatNumber(totalDeductions)}</span>`;
    }

    return totalDeductions;
}


// Toggle LWF input
window.toggleLWF = function(checkbox, typeId) {
    const container = document.getElementById(`lwf_input_container_${typeId}`);
    const input = document.getElementById(`lwf_deduction_${typeId}`);

    if (checkbox.checked) {
        container.style.opacity = '1';
        container.style.pointerEvents = 'auto';
        input.disabled = false;
        if (input.value == '0' || input.value == '') {
            input.value = '25';
        }
    } else {
        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';
        input.disabled = true;
        input.value = '0';
    }

    handleDeductionInput(input);
};
// Handle deduction input change
window.handleDeductionInput = function(input) {
    const value = parseFloat(input.value) || 0;
    const name = input.dataset.name;
    const typeId = input.dataset.typeId;

    // Update manualEarnings with deduction values
    if (!manualEarnings.deductions) {
        manualEarnings.deductions = {};
    }
    if (name) {
        manualEarnings.deductions[name] = value;
    }

    // Recalculate total deductions
    calculateDeductionsFromInputs();

    scheduleUpdateCalculator();
};

// Calculate deductions from inputs
// Calculate deductions from inputs
function calculateDeductionsFromInputs() {
    let totalDeductions = 0;
    const deductionValues = {};

    document.querySelectorAll('.deduction-field').forEach(input => {
        if (!input.disabled) {
            const value = parseFloat(input.value) || 0;
            const name = input.dataset.name;
            if (name && value > 0) {
                deductionValues[name] = value;
                totalDeductions += value;
            }
        }
    });

    const tdf = document.getElementById('totalDeductions');
    if (tdf) {
        tdf.innerHTML = `<span>Total deductions</span><span class="text-muted">- ₹${formatNumber(totalDeductions)}</span>`;
    }

    // Update deductions preview
    displayDeductionsPreview(deductionValues, totalDeductions);

    // Calculate and display contributions
    calculateAndDisplayContributions();
    // Update net pay
    updateNetPay();

    return { deductionValues, totalDeductions };
}

// New function to calculate and display contributions
function calculateAndDisplayContributions() {
    let basicEarning = getBasicEarning();
    let totalEarnings = getGrossEarning();

    let totalContributions = 0;
    const contributions = {};

    if (salaryComponents.employer && salaryComponents.employer.length > 0) {
        salaryComponents.employer.forEach(contribution => {
            const calcType = contribution.calcType || contribution.type || 'fixed';
            const rate = parseFloat(contribution.value) || 0;
            let value = 0;

            if (calcType === 'percentage_basic' || calcType === 'percentage' || calcType === '347') {
                value = (basicEarning * rate) / 100;
            } else if (calcType === 'percentage_ctc' || calcType === '346') {
                value = (totalEarnings * rate) / 100;
            } else {
                value = rate;
            }

            if (value > 0) {
                contributions[contribution.name || 'Contribution'] = value;
                totalContributions += value;
            }
        });
    }

    const employerFiltered = {};
    let employerFilteredTotal = 0;
    Object.entries(contributions).forEach(([key, value]) => {
        if (value <= 0) return;
        if (!shouldShowEmployeeDeduction(key)) return;
        employerFiltered[key] = value;
        employerFilteredTotal += value;
    });

    displayContributions(employerFiltered, employerFilteredTotal);
    if (typeof displayEmployerContributions === 'function') {
        displayEmployerContributions(employerFiltered, employerFilteredTotal);
    }

    // Update summary with CTC
    const totalDeductions = parseFloat(document.getElementById('totalDeductions').innerText.replace(/[^0-9-]/g, '')) || 0;
    const derivedMonthlyCTC = totalEarnings + employerFilteredTotal;
    const sourceMonthlyInput = inputPeriod === 'annual' ? (inputVal / 12) : inputVal;
    const monthlyCTC = calcMode === 'ctc' ? sourceMonthlyInput : derivedMonthlyCTC;
    const annualCTC = monthlyCTC * 12;
    const netPay = totalEarnings - totalDeductions;

    document.getElementById('monthlyCTC').textContent = '₹' + formatNumber(monthlyCTC);
    document.getElementById('annualCTC').textContent = '₹' + formatNumber(annualCTC);
    document.getElementById('summaryDeductions').textContent = '-₹' + formatNumber(totalDeductions);
    document.getElementById('summaryNet').textContent = '₹' + formatNumber(netPay);
    document.getElementById('summaryAnnualCTC').textContent = '₹' + formatNumber(annualCTC);
}
// ==================== DISPLAY DEDUCTIONS PREVIEW (tabular) ====================
function displayDeductionsPreview(deductions, total) {
    const container = document.getElementById('deductionsGrid');
    const totalEl = document.getElementById('totalDeductionsPreview');
    if (!container) return;

    if (Object.keys(deductions).length === 0) {
        container.innerHTML = `
            <div class="earnings-container deductions-preview-earnings-style">
                <div class="earnings-header">
                    <span>Component</span>
                    <span>Amount</span>
                </div>
                <div class="earnings-body">
                    <div class="text-muted text-center py-4">No deductions in preview</div>
                </div>
            </div>`;
        if (totalEl) {
            totalEl.innerHTML = `<span>Total deductions</span><span class="text-muted">- ₹0</span>`;
        }
        return;
    }

    const deductionItems = Object.entries(deductions).map(([key, value]) => ({ key, value }));

    deductionItems.sort((a, b) => {
        const pri = (x) =>
            (x.includes('PF') || x.includes('ESI') || x.includes('ESIC')) ? 0 : 1;
        const pa = pri(a.key);
        const pb = pri(b.key);
        if (pa !== pb) return pa - pb;
        return a.key.localeCompare(b.key);
    });

    let rowsHtml = '';
    deductionItems.forEach(({ key, value }) => {
        rowsHtml += `
            <div class="earning-row">
                <div class="earning-info min-w-0">
                    <span class="earning-name">${key}</span>
                </div>
                <div class="earning-input-wrapper">
                    <span class="earning-card-amount">₹${formatNumber(value)}</span>
                </div>
            </div>`;
    });

    container.innerHTML = `
        <div class="earnings-container deductions-preview-earnings-style">
            <div class="earnings-header">
                <span>Component</span>
                <span>Amount</span>
            </div>
            <div class="earnings-body">
                ${rowsHtml}
            </div>
        </div>`;

    if (totalEl) {
        totalEl.innerHTML = `<span>Total deductions</span><span class="text-muted">- ₹${formatNumber(total)}</span>`;
    }
}

/**
 * TDS preview API returns regime + marginal slab (Form 16–style projection).
 */
function updateTaxSlabPanel(data) {
    const panel = document.getElementById('taxSlabPanel');
    const regimeEl = document.getElementById('taxRegimeLabel');
    const taxableEl = document.getElementById('taxTaxableIncome');
    const slabEl = document.getElementById('taxMarginalSlab');
    if (!panel || !regimeEl || !taxableEl || !slabEl) return;

    if (!data) {
        panel.style.display = 'none';
        return;
    }

    panel.style.display = 'block';
    regimeEl.textContent = data.regime_label || '—';

    const ati = Math.round(parseFloat(data.annual_taxable_income) || 0);
    taxableEl.textContent = '₹' + formatNumber(ati);

    if (data.slabs_configured !== true) {
        slabEl.textContent =
            'Bracket detail needs active income tax slabs for this FY. Estimated taxable income is still shown for reference.';
        return;
    }

    const ms = data.marginal_slab;
    if (ati <= 0) {
        slabEl.textContent = 'No taxable income in this projection (TDS may be nil).';
    } else if (ms && ms.label) {
        slabEl.textContent = 'Marginal bracket: ' + ms.label;
    } else {
        slabEl.textContent = 'Slab row could not be resolved for this income.';
    }
}

// Helper function to create a main deduction card
function createDeductionMainCard(title, icon, items, type) {
    let itemsHtml = '';

    // Split items into two columns
    const midPoint = Math.ceil(items.length / 2);
    const leftColumn = items.slice(0, midPoint);
    const rightColumn = items.slice(midPoint);

    // Build left column
    let leftColumnHtml = '';
    leftColumn.forEach(({ key, value }) => {
        leftColumnHtml += `
            <div class="deduction-item-row">
                <span class="deduction-item-label">${key}</span>
                <span class="deduction-item-value">₹ ${formatNumber(value)}</span>
            </div>
        `;
    });

    // Build right column
    let rightColumnHtml = '';
    rightColumn.forEach(({ key, value }) => {
        rightColumnHtml += `
            <div class="deduction-item-row">
                <span class="deduction-item-label">${key}</span>
                <span class="deduction-item-value">₹ ${formatNumber(value)}</span>
            </div>
        `;
    });

    return `
        <div class="deduction-main-card">
            <div class="deduction-main-header">
                <h3><i class="fas ${icon}"></i>${title}</h3>
                <span class="badge">${items.length} item${items.length > 1 ? 's' : ''}</span>
            </div>
            <div class="deduction-items-grid">
                <div class="deduction-item-card">
                    <div class="deduction-item-content">
                        ${leftColumnHtml || '<div class="deduction-item-row"><span class="deduction-item-label">No items</span></div>'}
                    </div>
                </div>
                <div class="deduction-item-card">
                    <div class="deduction-item-content">
                        ${rightColumnHtml || '<div class="deduction-item-row"><span class="deduction-item-label">No items</span></div>'}
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Helper function to create a main employer card
function createEmployerMainCard(title, icon, items, type) {
    let itemsHtml = '';

    // Split items into two columns
    const midPoint = Math.ceil(items.length / 2);
    const leftColumn = items.slice(0, midPoint);
    const rightColumn = items.slice(midPoint);

    // Build left column
    let leftColumnHtml = '';
    leftColumn.forEach(({ key, value }) => {
        leftColumnHtml += `
            <div class="employer-item-row">
                <span class="employer-item-label">${key}</span>
                <span class="employer-item-value">₹ ${formatNumber(value)}</span>
            </div>
        `;
    });

    // Build right column
    let rightColumnHtml = '';
    rightColumn.forEach(({ key, value }) => {
        rightColumnHtml += `
            <div class="employer-item-row">
                <span class="employer-item-label">${key}</span>
                <span class="employer-item-value">₹ ${formatNumber(value)}</span>
            </div>
        `;
    });

    return `
        <div class="employer-main-card">
            <div class="employer-main-header">
                <h3><i class="fas ${icon}"></i>${title}</h3>
                <span class="badge">${items.length} item${items.length > 1 ? 's' : ''}</span>
            </div>
            <div class="employer-items-grid">
                <div class="employer-item-card">
                    <div class="employer-item-content">
                        ${leftColumnHtml || '<div class="employer-item-row"><span class="employer-item-label">No items</span></div>'}
                    </div>
                </div>
                <div class="employer-item-card">
                    <div class="employer-item-content">
                        ${rightColumnHtml || '<div class="employer-item-row"><span class="employer-item-label">No items</span></div>'}
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Helper function to create a deduction card
function createDeductionCard(title, icon, items) {
    let itemsHtml = '';
    let cardTotal = 0;

    items.forEach(({ key, value }) => {
        itemsHtml += `
            <div class="deduction-card-item">
                <span class="deduction-card-label">
                    <i class="fas ${icon}"></i>
                    ${key}
                </span>
                <span class="deduction-card-amount">₹ ${formatNumber(value)}</span>
            </div>
        `;
        cardTotal += value;
    });

    return `
        <div class="deduction-card">
            <div class="deduction-card-title">
                <i class="fas ${icon}"></i>
                ${title}
            </div>
            ${itemsHtml}
            <div class="deduction-card-total">
                <span>Total</span>
                <span>₹ ${formatNumber(cardTotal)}</span>
            </div>
        </div>
    `;
}

// Helper function to get basic earning
function getBasicEarning() {
    let basic = 0;
    document.querySelectorAll('.earning-field').forEach(input => {
        const name = input.dataset.name;
        if (name && name.toLowerCase().includes('basic')) {
            basic = parseFloat(input.value) || 0;
        }
    });
    return basic;
}

// Helper function to get gross earning
function getGrossEarning() {
    let total = 0;
    document.querySelectorAll('.earning-field').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    return total;
}





// Display contributions
function displayContributions(contributions, total) {
    const container = document.getElementById('contributionsContainer');
    if (!container) return;

    let html = '';

    if (Object.keys(contributions).length > 0) {
        Object.entries(contributions).forEach(([key, value]) => {
            if (value > 0) {
                html += `
                    <div class="deduction-item">
                        <span class="deduction-label">${key}</span>
                        <span class="deduction-value">₹ ${formatNumber(value)}</span>
                    </div>
                `;
            }
        });
    } else {
        html = '<div class="text-muted text-center py-3">No contributions calculated</div>';
    }

    container.innerHTML = html;
    document.getElementById('totalContributions').innerHTML = `<span>Total Benefits</span><span>₹${formatNumber(total)}</span>`;
}

// ==================== WORKBENCH (salary master monthly rules) ====================
function buildWorkbenchComponentsFromSalary() {
    const list = [];
    (salaryComponents.earnings || []).forEach(raw => {
        const active = raw.sa_is_active === undefined || raw.sa_is_active === null
            || raw.sa_is_active === true || raw.sa_is_active === 1 || raw.sa_is_active === '1';
        const title = raw.sa_title || raw.display_name || raw.name || 'Earning';
        list.push({
            category: 'Earnings',
            status: active ? 'Active' : 'Inactive',
            name: title,
            value: parseFloat(raw.sa_threshold_value) || 0,
            calcType: raw.sa_calculation_type ?? raw.calcType ?? raw.type,
            earningTypeId: raw.sa_earning_type_id,
            payrollHeadingId: raw.sa_payroll_heading_id,
            id: raw.sa_id,
        });
    });
    (salaryComponents.deductions || []).forEach(raw => {
        const nm = raw.deduction_type_name || raw.name || 'Deduction';
        list.push({
            category: 'Deductions',
            status: 'Active',
            name: nm,
            value: parseFloat(raw.std_threshold) || 0,
            calcType: raw.std_calculation_type,
        });
    });
    (salaryComponents.employer || []).forEach(raw => {
        const nm = raw.std_deduction_type_name || raw.deduction_type_name || raw.name || 'Employer';
        list.push({
            category: 'Benefits',
            status: 'Active',
            name: nm,
            value: parseFloat(raw.std_employer_contri_rate_amount ?? raw.value) || 0,
            calcType: raw.std_calculation_type ?? raw.calcType,
        });
    });
    return list;
}

function getWorkbenchComponents() {
    if (Array.isArray(components) && components.length > 0) {
        return components;
    }
    return buildWorkbenchComponentsFromSalary();
}

function isBalancingEarningRow(comp) {
    const hid = parseInt(comp.payrollHeadingId, 10);
    if (hid === 419) {
        return true;
    }
    const et = parseInt(comp.earningTypeId, 10);
    if (et === 364) {
        return true;
    }
    const t = (comp.name || '').toLowerCase();
    return t.includes('other') && t.includes('allow');
}

function calcTypeNumeric(comp) {
    const v = comp.calcType ?? comp.type;
    const n = parseInt(v, 10);
    return Number.isNaN(n) ? String(v || '').toLowerCase() : n;
}

function extractBasicFromEarnings(earningsObj, earningsRows) {
    const rows = (earningsRows || []).filter(e => e && e.status === 'Active');
    for (let i = 0; i < rows.length; i++) {
        const r = rows[i];
        const et = parseInt(r.earningTypeId, 10) || 0;
        if (et === 360 || (r.name && r.name.toLowerCase().includes('basic'))) {
            const v = earningsObj[r.name];
            if (v !== undefined && v !== null) {
                return parseFloat(v) || 0;
            }
        }
    }
    return parseFloat(earningsObj.Basic) || parseFloat(earningsObj.basic) || 0;
}

/**
 * Mirrors monthly salary master auto logic (add-edit-auto.blade.php):
 * 360: basic = base * threshold%; 346: base * threshold%; 348: flat; else: basic * threshold%.
 * Balancing row (other allowance / heading 419 / type 364): base minus sum of other lines.
 */
function distributeEarningsMasterAuto(baseMonthly, earningsRows, manualMap) {
    const manualOnly = manualMap ? { ...manualMap } : {};
    if (manualOnly.deductions) {
        delete manualOnly.deductions;
    }
    if (entryMode === 'manual' && Object.keys(manualOnly).length > 0) {
        const earningsObj = { ...manualOnly };
        const totalEarnings = Object.values(earningsObj).reduce((a, b) => a + (parseFloat(b) || 0), 0);
        const basicAmount = extractBasicFromEarnings(earningsObj, earningsRows);
        return { earningsObj, totalEarnings, basicAmount };
    }

    const active = (earningsRows || []).filter(e => e && e.status === 'Active');
    const balancing = active.filter(isBalancingEarningRow);
    const ordered = active.filter(c => !isBalancingEarningRow(c));

    const earningsObj = {};
    let basicAmount = 0;

    ordered.forEach(comp => {
        const et = parseInt(comp.earningTypeId, 10) || 0;
        const thresh = parseFloat(comp.value) || 0;
        if (et === 360) {
            basicAmount = Math.round(baseMonthly * (thresh / 100));
            earningsObj[comp.name] = basicAmount;
        }
    });

    if (basicAmount === 0) {
        const basicRow = ordered.find(c => (c.name || '').toLowerCase().includes('basic'));
        if (basicRow) {
            const pct = parseFloat(basicRow.value) || 40;
            basicAmount = Math.round(baseMonthly * (pct / 100));
            earningsObj[basicRow.name] = basicAmount;
        }
    }

    ordered.forEach(comp => {
        const et = parseInt(comp.earningTypeId, 10) || 0;
        if (et === 360) {
            return;
        }
        const ct = calcTypeNumeric(comp);
        const thresh = parseFloat(comp.value) || 0;
        const nm = (comp.name || '').toLowerCase();
        if (nm.includes('basic') && earningsObj[comp.name] !== undefined) {
            return;
        }

        let val = 0;
        if (ct === 346 || ct === '346' || ct === 'percentage_ctc') {
            val = Math.round(baseMonthly * (thresh / 100));
        } else if (ct === 348 || ct === '348' || ct === 'flat' || ct === 'fixed') {
            val = Math.round(thresh);
        } else if (ct === 347 || ct === '347' || ct === 'percentage_basic' || ct === 'percentage') {
            val = Math.round(basicAmount * (thresh / 100));
        } else {
            val = Math.round(basicAmount * (thresh / 100));
        }
        earningsObj[comp.name] = val;
    });

    if (balancing.length > 0) {
        const bal = balancing[0];
        let sumOthers = 0;
        Object.keys(earningsObj).forEach(k => {
            if (k !== bal.name) {
                sumOthers += parseFloat(earningsObj[k]) || 0;
            }
        });
        earningsObj[bal.name] = Math.round(Math.max(0, baseMonthly - sumOthers));
    }

    const totalEarnings = Object.values(earningsObj).reduce((a, b) => a + (parseFloat(b) || 0), 0);
    const basicResolved = extractBasicFromEarnings(earningsObj, earningsRows) || basicAmount;

    return { earningsObj, totalEarnings, basicAmount: basicResolved };
}

function applyStatutoryFromGross(gross, basic, deductions, benefits) {
    const pfWage = Math.min(basic, 15000);

    let employerPF = 0;
    let employerLWF = 0;
    let gratuity = 0;
    let employerESI = 0;

    const pfBenefit = benefits.find(c => c && c.name && isPfComponent(c.name));
    if (pfBenefit && basic > 0) {
        employerPF = isEmployeePfEnabled() ? Math.round(pfWage * 0.12) : 0;
    }

    const lwfBenefit = benefits.find(c => c && c.name && c.name.includes('LWF'));
    if (lwfBenefit) {
        employerLWF = parseFloat(lwfBenefit.value) || 50;
    }

    const gratuityBenefit = benefits.find(c => c && c.name && c.name.includes('Gratuity'));
    if (gratuityBenefit && basic > 0) {
        gratuity = Math.round(basic * 0.0481);
    }

    const esiBenefit = benefits.find(c => c && c.name && isEsicComponent(c.name));
    if (esiBenefit && gross <= 21000) {
        employerESI = isEmployeeEsicEnabled() ? Math.round(gross * 0.0325) : 0;
    }

    const derivedMonthlyCTC = gross + employerPF + employerESI + employerLWF + gratuity;
    const derivedAnnualCTC = derivedMonthlyCTC * 12;

    let employeePF = 0;
    let pt = 0;
    let employeeESI = 0;
    let lwfEmp = 0;

    const pfDeduction = deductions.find(c => c && c.name && isPfComponent(c.name));
    if (pfDeduction && basic > 0) {
        employeePF = isEmployeePfEnabled() ? Math.round(pfWage * 0.12) : 0;
    }

    const ptDeduction = deductions.find(c => c && c.name && (c.name.includes('Professional Tax') || c.name.includes('PT')));
    if (ptDeduction) {
        pt = 200;
    }

    const esiDeduction = deductions.find(c => c && c.name && isEsicComponent(c.name) && !c.name.includes('Employer'));
    if (esiDeduction && gross <= 21000) {
        employeeESI = isEmployeeEsicEnabled() ? Math.round(gross * 0.0075) : 0;
    }

    const lwfDeduction = deductions.find(c => c && c.name && (c.name.includes('Labour') || c.name.includes('LWF')));
    if (lwfDeduction) {
        lwfEmp = parseFloat(lwfDeduction.value) || 25;
    }

    const totalDeductions = employeePF + pt + employeeESI + lwfEmp;
    const net = gross - totalDeductions;

    const employerBenefits = {};
    if (employerPF > 0) {
        employerBenefits['PF (Employer)'] = employerPF;
    }
    if (gratuity > 0) {
        employerBenefits['Gratuity'] = gratuity;
    }
    if (employerLWF > 0) {
        employerBenefits['LWF (Employer)'] = employerLWF;
    }
    if (employerESI > 0) {
        employerBenefits['ESI (Employer)'] = employerESI;
    }

    const empDeductions = {};
    if (employeePF > 0) {
        empDeductions['PF (Employee)'] = employeePF;
    }
    if (pt > 0) {
        empDeductions['Professional Tax'] = pt;
    }
    if (employeeESI > 0) {
        empDeductions['ESI (Employee)'] = employeeESI;
    }
    if (lwfEmp > 0) {
        empDeductions['Labour Welfare Fund'] = lwfEmp;
    }

    const totalEmployer = Object.values(employerBenefits).reduce((a, b) => a + b, 0);

    return {
        employerBenefits,
        empDeductions,
        totalDeductions,
        net,
        derivedMonthlyCTC,
        derivedAnnualCTC,
        totalEmployer,
    };
}

function calculateBreakdown() {
    try {
        const comps = getWorkbenchComponents();
        const earnings = comps.filter(c => c && c.category === 'Earnings' && c.status === 'Active') || [];
        const deductions = comps.filter(c => c && c.category === 'Deductions' && c.status === 'Active') || [];
        const benefits = comps.filter(c => c && c.category === 'Benefits' && c.status === 'Active') || [];

        const userMonthlyInput = inputPeriod === 'annual' ? (inputVal / 12) : inputVal;
        const userAnnualInput = inputPeriod === 'annual' ? inputVal : (inputVal * 12);

        let annualCTC = 0;
        let monthlyCTC = 0;
        let targetGross = 0;

        if (calcMode === 'ctc') {
            monthlyCTC = userMonthlyInput;
            annualCTC = userAnnualInput;
            targetGross = userMonthlyInput;
        } else {
            annualCTC = userAnnualInput;
            targetGross = userMonthlyInput;
        }

        let earningsObj = {};
        let totalEarnings = 0;
        let basicAmount = 0;

        const manualOnly = { ...manualEarnings };
        if (manualOnly.deductions) {
            delete manualOnly.deductions;
        }

        if (entryMode === 'manual' && Object.keys(manualOnly).length > 0) {
            const dist = distributeEarningsMasterAuto(0, earnings, manualEarnings);
            earningsObj = dist.earningsObj;
            totalEarnings = dist.totalEarnings;
            basicAmount = dist.basicAmount;
            targetGross = calcMode === 'gross' ? userMonthlyInput : userMonthlyInput;
        } else if (calcMode === 'ctc' && entryMode === 'auto') {
            let alloc = userMonthlyInput;
            let lastDist;
            for (let iter = 0; iter < 25; iter++) {
                lastDist = distributeEarningsMasterAuto(alloc, earnings, manualEarnings);
                const pkg = applyStatutoryFromGross(
                    lastDist.totalEarnings,
                    lastDist.basicAmount,
                    deductions,
                    benefits
                );
                const nextAlloc = userMonthlyInput - pkg.totalEmployer;
                if (Math.abs(nextAlloc - alloc) < 0.5) {
                    alloc = nextAlloc;
                    break;
                }
                alloc = nextAlloc;
            }
            lastDist = distributeEarningsMasterAuto(alloc, earnings, manualEarnings);
            earningsObj = lastDist.earningsObj;
            totalEarnings = lastDist.totalEarnings;
            basicAmount = lastDist.basicAmount;
        } else if (calcMode === 'gross' && entryMode === 'auto') {
            const dist = distributeEarningsMasterAuto(userMonthlyInput, earnings, manualEarnings);
            earningsObj = dist.earningsObj;
            totalEarnings = dist.totalEarnings;
            basicAmount = dist.basicAmount;
        } else {
            const dist = distributeEarningsMasterAuto(userMonthlyInput, earnings, manualEarnings);
            earningsObj = dist.earningsObj;
            totalEarnings = dist.totalEarnings;
            basicAmount = dist.basicAmount;
        }

        const gross = totalEarnings;
        const pkg = applyStatutoryFromGross(gross, basicAmount, deductions, benefits);

        const displayMonthlyCTC = calcMode === 'ctc' ? userMonthlyInput : pkg.derivedMonthlyCTC;
        const displayAnnualCTC = calcMode === 'ctc' ? userAnnualInput : pkg.derivedAnnualCTC;
        const displayGross = userMonthlyInput;

        return {
            earnings: earningsObj,
            deductions: pkg.empDeductions,
            employer: pkg.employerBenefits,
            gross: gross,
            displayGross: displayGross,
            net: pkg.net,
            monthlyCTC: pkg.derivedMonthlyCTC,
            annualCTC: pkg.derivedAnnualCTC,
            displayMonthlyCTC: displayMonthlyCTC,
            displayAnnualCTC: displayAnnualCTC,
            totalDeductions: pkg.totalDeductions,
            totalEmployer: pkg.totalEmployer,
            targetGross: targetGross,
            inputMonthlyCTC: userMonthlyInput,
            inputAnnualCTC: userAnnualInput,
        };
    } catch (error) {
        console.error('Calculation error:', error);
        return null;
    }
}


function updateMetrics(breakdown) {
    if (!breakdown) return;

    // Normalize input to monthly value
    const monthlyInput = inputPeriod === 'annual'
        ? inputVal / 12
        : inputVal;

    const annualInput = monthlyInput * 12;

    document.getElementById('monthlyGross').textContent = '₹' + formatNumber(Math.round(monthlyInput));
    document.getElementById('annualGross').textContent = '₹' + formatNumber(Math.round(annualInput));

    // ========================
    // If Input Mode = CTC
    // ========================
    if (calcMode === 'ctc') {
        // Show input values in CTC fields
        document.getElementById('monthlyCTC').textContent = '₹' + formatNumber(Math.round(monthlyInput));
        document.getElementById('annualCTC').textContent = '₹' + formatNumber(Math.round(annualInput));
    }
    // ========================
    // If Input Mode = Gross
    // ========================
    else {
        // Show calculated values in CTC fields
        document.getElementById('monthlyCTC').textContent = '₹' + formatNumber(Math.round(breakdown.monthlyCTC));
        document.getElementById('annualCTC').textContent = '₹' + formatNumber(Math.round(breakdown.annualCTC));
    }
}

function updateEarnings(breakdown) {
    const container = document.getElementById('earningsGrid');
    if (!container || !breakdown) return;

    if (entryMode === 'manual') {
        return;
    }

    const earningsMap = breakdown.earnings || {};
    let businessRows = (getWorkbenchComponents() || []).filter(
        c => c && c.category === 'Earnings' && c.status === 'Active'
    );

    if (businessRows.length === 0) {
        const keys = Object.keys(earningsMap);
        if (keys.length === 0) {
            container.innerHTML = '<div class="text-muted text-center py-4" style="grid-column: 1/-1;">No earnings configured for this business</div>';
            document.getElementById('totalGrossValue').textContent = '₹' + formatNumber(breakdown.gross || 0);
            updateGrossBalance(breakdown.gross || 0);
            return;
        }
        businessRows = keys.map(name => ({
            name,
            value: 0,
            calcType: 'fixed',
        }));
    } else {
        const bi = businessRows.findIndex(e => (e.name || '').toLowerCase().includes('basic'));
        if (bi > -1) {
            const b = businessRows.splice(bi, 1)[0];
            businessRows.unshift(b);
        }
    }

    const midPoint = Math.ceil(businessRows.length / 2);
    const leftColumn = businessRows.slice(0, midPoint);
    const rightColumn = businessRows.slice(midPoint);

    function autoEarningRowHtml(comp) {
        const key = comp.name || 'Earning';
        const raw = earningsMap[key];
        const num = raw !== undefined && raw !== null ? (parseFloat(raw) || 0) : 0;
        const ct = String(comp.calcType || comp.type || 'fixed');
        const calcValue = parseFloat(comp.value) || 0;
        const typeText = getCalculationTypeText(ct);
        const showPct = ct.includes('percentage') || ['346', '347', '360'].includes(ct);
        const pctHint = showPct ? `<small class="text-muted ms-1">(${calcValue}%)</small>` : '';
        return `
            <div class="earning-card-item">
                <span class="earning-card-label" title="${typeText}">
                    ${key}
                    ${pctHint}
                </span>
                <div class="d-flex align-items-center gap-2">
                    <div class="earning-card-input readonly">
                        <span class="currency">₹</span>
                        <input type="text"
                               class="earning-field"
                               value="${formatNumber(num)}"
                               readonly
                               style="background: transparent; border: none;">
                    </div>
                </div>
            </div>
        `;
    }

    let leftColumnItems = '';
    leftColumn.forEach(comp => {
        leftColumnItems += autoEarningRowHtml(comp);
    });

    let rightColumnItems = '';
    rightColumn.forEach(comp => {
        rightColumnItems += autoEarningRowHtml(comp);
    });

    const html = `
        <div class="earning-card">
            <div class="earning-card-title">Earnings (${leftColumn.length} components)</div>
            ${leftColumnItems}
        </div>
        <div class="earning-card">
            <div class="earning-card-title">Other Allowances (${rightColumn.length} components)</div>
            ${rightColumnItems}
        </div>
    `;

    container.innerHTML = html;
    document.getElementById('totalGrossValue').textContent = '₹' + formatNumber(breakdown.gross || 0);
    updateGrossBalance(breakdown.gross || 0);
}

window.handleManualInput = function(input) {
    manualEarnings[input.dataset.key] = parseFloat(input.value) || 0;
    scheduleUpdateCalculator();
};

function updateDeductions(breakdown) {
    const container = document.getElementById('deductionsContainer');
    if (!container || !breakdown) return;
    let html = '';
    Object.entries(breakdown.deductions).forEach(([key, val]) => {
        if (val > 0) html += `<div class="deduction-item"><span class="deduction-label">${key}</span><span class="deduction-value">₹ ${formatNumber(val)}</span></div>`;
    });
    container.innerHTML = html;
    const tdfD = document.getElementById('totalDeductions');
    if (tdfD) {
        tdfD.innerHTML = `<span>Total deductions</span><span class="text-muted">- ₹${formatNumber(breakdown.totalDeductions)}</span>`;
    }
}

function updateContributions(breakdown) {
    const container = document.getElementById('contributionsContainer');
    if (!container || !breakdown) return;
    let html = '';
    Object.entries(breakdown.employer).forEach(([key, val]) => {
        if (val > 0) html += `<div class="deduction-item"><span class="deduction-label">${key}</span><span class="deduction-value">₹ ${formatNumber(val)}</span></div>`;
    });
    container.innerHTML = html;
    document.getElementById('totalContributions') && (document.getElementById('totalContributions').innerHTML = `<span>Total Benefits</span><span>₹${formatNumber(breakdown.totalEmployer)}</span>`);
}

function updateSummary(breakdown) {
    if (!breakdown) return;
    document.getElementById('summaryGross') && (document.getElementById('summaryGross').textContent = '₹' + formatNumber(breakdown.gross));
    document.getElementById('summaryDeductions') && (document.getElementById('summaryDeductions').textContent = '-₹' + formatNumber(breakdown.totalDeductions));
    document.getElementById('summaryNet') && (document.getElementById('summaryNet').textContent = '₹' + formatNumber(breakdown.net));
    document.getElementById('summaryAnnualCTC') && (document.getElementById('summaryAnnualCTC').textContent = '₹' + formatNumber(Math.round(breakdown.displayAnnualCTC ?? breakdown.annualCTC)));
}

function checkMismatch(breakdown) {
    if (!breakdown) return;

    const elements = {
        mismatchWarning: document.getElementById('mismatchWarning'),
        totalGross: document.getElementById('totalGross'),
        saveButton: document.getElementById('saveButton'),
        saveButtonText: document.getElementById('saveButtonText'),
        mismatchMessage: document.getElementById('mismatchMessage')
    };

    if (!elements.mismatchWarning || !elements.totalGross || !elements.saveButton || !elements.saveButtonText) return;

    const displayedTotal = Object.values(breakdown.earnings).reduce((a, b) => a + (parseFloat(b) || 0), 0);
    const grossValue = breakdown.gross;

    // Check if earnings sum equals gross (should always be true)
    const earningsMatchGross = Math.abs(displayedTotal - grossValue) <= 1;

    // In manual mode, check if gross matches target
    if (entryMode === 'manual') {
        const target = breakdown.targetGross;
        const grossMatchesTarget = Math.abs(grossValue - target) <= 10;

        if (!earningsMatchGross || !grossMatchesTarget) {
            elements.mismatchWarning.style.display = 'flex';
            elements.totalGross.className = 'total-gross mismatch';

            let message = '';
            if (!earningsMatchGross) {
                message = `Earnings sum (₹${formatNumber(displayedTotal)}) doesn't match gross (₹${formatNumber(grossValue)})`;
            } else if (!grossMatchesTarget) {
                message = `Gross (₹${formatNumber(grossValue)}) doesn't match target (₹${formatNumber(target)})`;
            }

            if (elements.mismatchMessage) elements.mismatchMessage.innerHTML = message;
            elements.saveButton.disabled = true;
            elements.saveButtonText.textContent = 'Fix Errors to Save';
        } else {
            elements.mismatchWarning.style.display = 'none';
            elements.totalGross.className = 'total-gross normal';
            elements.saveButton.disabled = false;
            elements.saveButtonText.textContent = 'Confirm Structure';
        }
    }
    // In auto mode, just check if earnings sum equals gross
    else if (!earningsMatchGross) {
        elements.mismatchWarning.style.display = 'flex';
        elements.totalGross.className = 'total-gross mismatch';
        if (elements.mismatchMessage) {
            elements.mismatchMessage.innerHTML = `Earnings sum (₹${formatNumber(displayedTotal)}) doesn't match gross (₹${formatNumber(grossValue)})`;
        }
        elements.saveButton.disabled = true;
        elements.saveButtonText.textContent = 'Fix Errors to Save';
    } else {
        elements.mismatchWarning.style.display = 'none';
        elements.totalGross.className = 'total-gross normal';
        elements.saveButton.disabled = false;
        elements.saveButtonText.textContent = 'Confirm Structure';
    }
}

function syncManualDeductionsPreviewWithTds(breakdown) {
    if (!breakdown || !document.getElementById('deductionsGrid')) return;
    const tds = parseFloat(breakdown.deductions && breakdown.deductions.TDS) || 0;
    const deductionValues = {};
    let totalDeductions = 0;

    document.querySelectorAll('.deduction-field').forEach((input) => {
        if (!input.disabled) {
            const value = parseFloat(input.value) || 0;
            const name = input.dataset.name;
            if (name && value > 0) {
                deductionValues[name] = value;
                totalDeductions += value;
            }
        }
    });

    document.querySelectorAll('input[id^="lwf_deduction_"]').forEach((input) => {
        if (input.disabled) return;
        const value = parseFloat(input.value) || 0;
        const name = input.dataset.name;
        if (name && value > 0) {
            deductionValues[name] = value;
            totalDeductions += value;
        }
    });

    if (tds > 0) {
        deductionValues.TDS = tds;
        totalDeductions += tds;
    }

    displayDeductionsPreview(deductionValues, totalDeductions);
    const el = document.getElementById('totalDeductionsPreview');
    if (el) {
        el.innerHTML = `<span>Total deductions</span><span class="text-muted">- ₹${formatNumber(totalDeductions)}</span>`;
    }
}

function finishCalculatorUpdate(breakdown) {
    if (!breakdown) return;
    currentBreakdown = breakdown;
    updateMetrics(breakdown);
    updateEarnings(breakdown);
    updateDeductions(breakdown);
    updateContributions(breakdown);
    updateSummary(breakdown);
    syncManualDeductionsPreviewWithTds(breakdown);
    checkMismatch(breakdown);
}

function updateCalculator() {
    try {
        const breakdown = calculateBreakdown();
        if (!breakdown) return;

        if (!defaultTdsFyId || !previewMonthlyTdsUrl) {
            updateTaxSlabPanel(null);
            finishCalculatorUpdate(breakdown);
            return;
        }

        const seq = ++tdsFetchSeq;
        const domSt = getDomStatutoryAmountsForTds();
        const pf = domSt.hasPf
            ? domSt.pf
            : (breakdown.deductions['PF (Employee)'] || 0);
        const pt = domSt.hasPt
            ? domSt.pt
            : (breakdown.deductions['Professional Tax'] || 0);

        fetch(previewMonthlyTdsUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                monthly_gross: breakdown.gross,
                monthly_pf: pf,
                monthly_pt: pt,
                financial_year_id: defaultTdsFyId,
            }),
        })
            .then((r) => (r.ok ? r.json() : Promise.reject(new Error('tds preview'))))
            .then((data) => {
                if (seq !== tdsFetchSeq) return;
                updateTaxSlabPanel(data);
                const tds = Math.round(parseFloat(data.tds) || 0);
                const d = { ...breakdown.deductions };
                if (tds > 0) {
                    d.TDS = tds;
                } else {
                    delete d.TDS;
                }
                const totalDed = Object.values(d).reduce((a, b) => a + (parseFloat(b) || 0), 0);
                const net = breakdown.gross - totalDed;
                finishCalculatorUpdate({
                    ...breakdown,
                    deductions: d,
                    totalDeductions: totalDed,
                    net,
                });
            })
            .catch(() => {
                if (seq !== tdsFetchSeq) return;
                updateTaxSlabPanel(null);
                finishCalculatorUpdate(breakdown);
            });
    } catch (error) {
        console.error('Error updating calculator:', error);
    }
}

// ==================== SAVE ====================
function saveSalaryStructure() {
    if (!currentBreakdown) return;
    const employeeId = document.getElementById('employeeId')?.value;
    if (!employeeId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Employee ID not found',
            background: document.body.classList.contains('dark-mode') ? '#1a1f2e' : '#ffffff',
            color: document.body.classList.contains('dark-mode') ? '#e5e7eb' : '#1e293b'
        });
        return;
    }

    Swal.fire({
        title: 'Saving...',
        text: 'Please wait...',
        allowOutsideClick: false,
        background: document.body.classList.contains('dark-mode') ? '#1a1f2e' : '#ffffff',
        color: document.body.classList.contains('dark-mode') ? '#e5e7eb' : '#1e293b',
        didOpen: () => Swal.showLoading()
    });

    fetch('{{ route("payroll.save-salary-structure") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            employee_id: employeeId,
            business_id: salaryCalculatorData.businessId,
            annual_ctc: Math.round(currentBreakdown.displayAnnualCTC ?? currentBreakdown.annualCTC),
            monthly_ctc: Math.round(currentBreakdown.displayMonthlyCTC ?? currentBreakdown.monthlyCTC),
            monthly_gross: Math.round(currentBreakdown.displayGross ?? currentBreakdown.gross),
            monthly_net: Math.round(currentBreakdown.net),
            earnings: currentBreakdown.earnings,
            deductions: currentBreakdown.deductions,
            employer_contributions: currentBreakdown.employer,
            calc_mode: calcMode,
            input_period: inputPeriod,
            entry_mode: entryMode,
            _token: '{{ csrf_token() }}'
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Salary structure saved successfully',
                timer: 1500,
                showConfirmButton: false,
                background: document.body.classList.contains('dark-mode') ? '#1a1f2e' : '#ffffff',
                color: document.body.classList.contains('dark-mode') ? '#e5e7eb' : '#1e293b'
            }).then(() => window.location.href = '{{ route("employee.payroll") }}');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to save',
                background: document.body.classList.contains('dark-mode') ? '#1a1f2e' : '#ffffff',
                color: document.body.classList.contains('dark-mode') ? '#e5e7eb' : '#1e293b'
            });
        }
    })
    .catch(() => Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'An error occurred while saving',
        background: document.body.classList.contains('dark-mode') ? '#1a1f2e' : '#ffffff',
        color: document.body.classList.contains('dark-mode') ? '#e5e7eb' : '#1e293b'
    }));
}

// Display employer deductions in card grid
function displayEmployerContributions(contributions, total) {
    const container = document.getElementById('employerContributionsContainer');
    if (!container) return;

    const rows = Object.entries(contributions)
        .filter(([, value]) => (parseFloat(value) || 0) > 0)
        .map(([key, value]) => `
            <div class="earning-row">
                <div class="earning-info min-w-0">
                    <span class="earning-name" title="${key}">${key}</span>
                    <span class="earning-badge-sm ms-1">Employer</span>
                </div>
                <div class="earning-input-wrapper">
                    <div class="earning-input-group-sm readonly">
                        <span class="currency-sm">₹</span>
                        <input type="text"
                               value="${formatNumber(value)}"
                               readonly
                               style="background: transparent; border: none;">
                    </div>
                </div>
            </div>
        `);

    let html = '';
    if (rows.length > 0) {
        html = `
            <div class="earnings-container deductions-input-table">
                <div class="earnings-header">
                    <span>Component</span>
                    <span>Amount</span>
                </div>
                <div class="earnings-body">
                    ${rows.join('')}
                </div>
            </div>`;
    } else {
        html = `
            <div class="earnings-container deductions-input-table">
                <div class="earnings-header">
                    <span>Component</span>
                    <span>Amount</span>
                </div>
                <div class="earnings-body">
                    <div class="text-muted text-center py-4">No employer deductions calculated</div>
                </div>
            </div>`;
    }

    container.innerHTML = html;
    document.getElementById('totalEmployerContributions').innerHTML = `<span>Total employer deductions</span><span class="text-muted">+ ₹${formatNumber(total)}</span>`;
}

// Helper function to create an employer card
function createEmployerCard(title, icon, items) {
    let itemsHtml = '';
    let cardTotal = 0;

    items.forEach(({ key, value }) => {
        itemsHtml += `
            <div class="employer-card-item">
                <span class="employer-card-label">
                    <i class="fas ${icon}"></i>
                    ${key}
                </span>
                <span class="employer-card-amount">₹ ${formatNumber(value)}</span>
            </div>
        `;
        cardTotal += value;
    });

    return `
        <div class="employer-card">
            <div class="employer-card-title">
                <i class="fas ${icon}"></i>
                ${title}
            </div>
            ${itemsHtml}
            <div class="employer-card-total">
                <span>Total</span>
                <span>₹ ${formatNumber(cardTotal)}</span>
            </div>
        </div>
    `;
}

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
    // Display earnings in dynamic grid layout
    displayEarningsGrid();

    // Display deduction input fields
    displayDeductionInputs();

    // Calculate initial deductions and update displays
    calculateDeductionsFromInputs();

    // Calculate initial totals
    calculateTotals();

    // Update net pay
    updateNetPay();

    // Input amount handler
    const inputAmountEl = document.getElementById('inputAmount');
    inputAmountEl?.addEventListener('input', function(e) {
        inputVal = parseFloat(e.target.value) || 0;

        // Clear manual earnings when input amount changes
        manualEarnings = {};

        scheduleUpdateCalculator();
    });
    inputAmountEl?.addEventListener('change', function() {
        clearTimeout(calcDebounceTimer);
        updateCalculator();
    });

    // Mode buttons
    document.querySelectorAll('.calc-mode-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.calc-mode-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            calcMode = this.dataset.mode;
            updateCalculator();
        });
    });

    // Period buttons
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            inputPeriod = this.dataset.period;
            updateCalculator();
        });
    });

    // Entry mode buttons
    document.querySelectorAll('.entry-mode-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.entry-mode-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            entryMode = this.dataset.mode;

            // Toggle between display modes
            if (entryMode === 'manual') {
                displayEarningsGrid();
                if (typeof calculateDeductionsFromInputs === 'function') {
                    calculateDeductionsFromInputs();
                }
            } else {
                updateCalculator();
            }
        });
    });

    // Save button
    document.getElementById('saveButton')?.addEventListener('click', saveSalaryStructure);

    // Theme toggle
    const themeToggle = document.getElementById('darkModeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function(e) {
            e.preventDefault();
            window.toggleTheme();
        });
    }

    // Compact mode toggle
    const compactToggle = document.getElementById('compactModeToggle');
    if (compactToggle) {
        compactToggle.addEventListener('click', function() {
            document.body.classList.toggle('compact-mode');
            const icon = document.getElementById('compactModeIcon');
            if (icon) {
                icon.className = document.body.classList.contains('compact-mode') ?
                    'fas fa-expand-alt' : 'fas fa-compress-alt';
            }
        });
    }

    // Initial calculation
    updateCalculator();

});
</script>
@endsection
