@extends('admin.layout.master')

@section('title', 'Weekly Payroll Process - Week ' . $weekNumber)

@section('css')
<style>
    /* Add these styles to your CSS section */
    .compact-select2-dropdown {
        font-size: 13px !important;
    }

    .compact-select2-dropdown .select2-results__option {
        padding: 6px 12px !important;
        font-size: 13px !important;
    }

    .select2-container--default .select2-selection--multiple {
        min-height: 38px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 6px !important;
        font-size: 13px !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        font-size: 11px !important;
        padding: 2px 6px !important;
        margin-top: 3px !important;
        margin-bottom: 3px !important;
        background: #f3f4f6 !important;
        border: 1px solid #d1d5db !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        margin-right: 4px !important;
        font-size: 10px !important;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1) !important;
    }

    .hold-details-content {
        color: #dbddef !important;
    }

    /* Compact Modal Styles for Hold Salary */
    #hold-salary-modal .modal-container {
        max-width: 500px;
    }

    .compact-employee-info {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        margin-bottom: 16px;
    }

    .compact-employee-avatar {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        flex-shrink: 0;
    }

    .compact-employee-details {
        flex: 1;
        min-width: 0;
    }

    .compact-employee-name {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .compact-employee-meta {
        font-size: 12px;
        color: #64748b;
        margin: 2px 0 0 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Compact Dropdown */
    .compact-dropdown-container {
        margin-bottom: 16px;
    }

    .compact-dropdown-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .compact-dropdown-label .label-text {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }

    .compact-dropdown-label .hint-text {
        font-size: 11px;
        color: #6b7280;
        font-weight: 400;
    }

    /* Select2 Compact Customization */
    .payroll-period-select.compact+.select2-container {
        font-size: 13px;
    }

    .payroll-period-select.compact+.select2-container .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 4px 8px;
    }

    .payroll-period-select.compact+.select2-container .select2-selection__choice {
        font-size: 11px;
        padding: 2px 6px;
        margin-top: 3px;
        margin-bottom: 3px;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
    }

    .payroll-period-select.compact+.select2-container .select2-selection__choice__remove {
        margin-right: 4px;
        font-size: 10px;
    }

    /* Selected Periods Tags - Compact */
    .selected-periods-container.compact {
        margin-top: 8px;
    }

    .selected-periods-tags.compact {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }

    .tag-badge.compact {
        font-size: 11px;
        padding: 2px 6px;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .tag-close.compact {
        width: 12px;
        height: 12px;
        background: #9ca3af;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 8px;
        cursor: pointer;
        padding: 0;
        border: none;
    }

    /* Compact Form Elements */
    .compact-form-group {
        margin-bottom: 16px;
    }

    .compact-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .compact-textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
        min-height: 80px;
        resize: vertical;
    }

    .compact-textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }

    /* Compact Checkbox */
    .compact-checkbox-container {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }

    .compact-checkbox {
        width: 16px;
        height: 16px;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        cursor: pointer;
        flex-shrink: 0;
    }

    .compact-checkbox:checked {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }

    .compact-checkbox-label {
        font-size: 13px;
        color: #374151;
        cursor: pointer;
    }

    .compact-checkbox-hint {
        font-size: 11px;
        color: #6b7280;
        margin-left: 24px;
        margin-top: -4px;
    }

    /* Compact Modal Footer */
    .compact-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
        margin-top: 16px;
    }

    .compact-btn {
        padding: 6px 16px;
        font-size: 13px;
        font-weight: 500;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
    }

    .compact-btn-secondary {
        background: white;
        color: #374151;
        border-color: #d1d5db;
    }

    .compact-btn-secondary:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    .compact-btn-primary {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    .compact-btn-primary:hover {
        background: #2563eb;
        border-color: #2563eb;
    }

    .compact-btn-primary:disabled {
        background: #9ca3af;
        border-color: #9ca3af;
        cursor: not-allowed;
    }

    /* Modern Card Design */
    .modern-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05), 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .modern-card.hover-effect:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06), 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
        border-color: #d1d5db;
    }

    /* Premium Card Shadow */
    .premium-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.07), 0 1px 2px 0 rgba(0, 0, 0, 0.05), inset 0 0 0 1px rgba(255, 255, 255, 0.5);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .premium-card.hover-effect:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.07), 0 4px 6px -2px rgba(0, 0, 0, 0.05), inset 0 0 0 1px rgba(255, 255, 255, 0.8);
        transform: translateY(-2px);
    }

    /* Glass Card Effect */
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06), 0 0 0 1px rgba(255, 255, 255, 0.5);
    }

    .glass-card.hover-effect:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05), 0 0 0 1px rgba(255, 255, 255, 0.8);
        transform: translateY(-1px);
    }

    /* Elevated Card */
    .elevated-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #f3f4f6;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.03), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
        position: relative;
    }

    .elevated-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
    }

    .elevated-card.hover-effect:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        transform: translateY(-2px);
    }

    /* Minimal Card */
    .minimal-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 0 0 1px #f1f5f9;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .minimal-card.hover-effect:hover {
        box-shadow: 0 0 0 1px #d1d5db, 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transform: translateY(-1px);
    }

    /* Gradient Border Card */
    .gradient-border-card {
        background: white;
        border-radius: 12px;
        position: relative;
        padding: 1px;
    }

    .gradient-border-card::before {
        content: '';
        position: absolute;
        top: -1px;
        left: -1px;
        right: -1px;
        bottom: -1px;
        background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
        border-radius: 13px;
        z-index: -1;
    }

    .gradient-border-card .content {
        background: white;
        border-radius: 11px;
        padding: 24px;
        position: relative;
        z-index: 1;
    }

    /* Dark Mode Support */
    .dark-mode .modern-card {
        background: #1f2937;
        border-color: #374151;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.3), 0 1px 3px 0 rgba(0, 0, 0, 0.2);
    }

    .dark-mode .modern-card.hover-effect:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2), 0 10px 15px -3px rgba(0, 0, 0, 0.25);
        border-color: #4b5563;
    }

    .dark-mode .premium-card {
        background: #1f2937;
        border-color: #374151;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3), 0 1px 2px 0 rgba(0, 0, 0, 0.2), inset 0 0 0 1px rgba(255, 255, 255, 0.05);
    }

    .dark-mode .premium-card.hover-effect:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.25), 0 4px 6px -2px rgba(0, 0, 0, 0.2), inset 0 0 0 1px rgba(255, 255, 255, 0.1);
    }

    .dark-mode .glass-card {
        background: rgba(31, 41, 55, 0.8);
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -1px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(255, 255, 255, 0.05);
    }

    .dark-mode .glass-card.hover-effect:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.25), 0 4px 6px -2px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(255, 255, 255, 0.1);
    }

    .dark-mode .elevated-card {
        background: #1f2937;
        border-color: #374151;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2), 0 4px 6px -2px rgba(0, 0, 0, 0.15);
    }

    .dark-mode .elevated-card::before {
        background: linear-gradient(90deg, transparent, #4b5563, transparent);
    }

    .dark-mode .elevated-card.hover-effect:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
    }

    .dark-mode .minimal-card {
        background: #1f2937;
        border-color: #374151;
        box-shadow: 0 0 0 1px #374151;
    }

    .dark-mode .minimal-card.hover-effect:hover {
        box-shadow: 0 0 0 1px #4b5563, 0 4px 6px -1px rgba(0, 0, 0, 0.2);
    }

    .dark-mode .gradient-border-card::before {
        background: linear-gradient(135deg, #374151, #4b5563);
    }

    .dark-mode .gradient-border-card .content {
        background: #1f2937;
    }

    /* Advanced Card Variations */
    .card-with-glow {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06), 0 0 20px 0 rgba(59, 130, 246, 0.05);
        transition: all 0.3s ease;
    }

    .card-with-glow.hover-effect:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06), 0 0 30px 0 rgba(59, 130, 246, 0.1);
    }

    .dark-mode .card-with-glow {
        background: #1f2937;
        border-color: #374151;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3), 0 1px 2px 0 rgba(0, 0, 0, 0.2), 0 0 20px 0 rgba(59, 130, 246, 0.1);
    }

    .dark-mode .card-with-glow.hover-effect:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2), 0 0 30px 0 rgba(59, 130, 246, 0.15);
    }

    /* Card with Inner Shadow */
    .card-inner-shadow {
        background: white;
        border-radius: 12px;
        border: 1px solid #f3f4f6;
        box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .dark-mode .card-inner-shadow {
        background: #1f2937;
        border-color: #374151;
        box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.2), 0 1px 2px 0 rgba(0, 0, 0, 0.1);
    }

    /* Card Utility Classes */
    .card-padding-sm {
        padding: 16px;
    }

    .card-padding-md {
        padding: 20px;
    }

    .card-padding-lg {
        padding: 24px;
    }

    .card-padding-xl {
        padding: 32px;
    }

    /* Card Header Styles */
    .card-header {
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 16px;
        margin-bottom: 16px;
    }

    .dark-mode .card-header {
        border-bottom-color: #374151;
    }

    /* Card Footer Styles */
    .card-footer {
        border-top: 1px solid #f1f5f9;
        padding-top: 16px;
        margin-top: 16px;
    }

    .dark-mode .card-footer {
        border-top-color: #374151;
    }

    /* Card Title */
    .card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    .dark-mode .card-title {
        color: #f9fafb;
    }

    /* Card Subtitle */
    .card-subtitle {
        font-size: 0.875rem;
        color: #6b7280;
        margin-top: 4px;
    }

    .dark-mode .card-subtitle {
        color: #9ca3af;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {

        .modern-card,
        .premium-card,
        .glass-card,
        .elevated-card,
        .minimal-card,
        .gradient-border-card {
            margin: 0 8px;
        }

        .card-padding-lg,
        .card-padding-xl {
            padding: 20px;
        }
    }

    /* Animation for Card Entrance */
    @keyframes cardSlideIn {
        from {
            opacity: 0;
            transform: translateY(10px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .modern-card,
    .premium-card,
    .glass-card,
    .elevated-card,
    .minimal-card,
    .gradient-border-card {
        animation: cardSlideIn 0.3s ease-out;
    }

    /* All your existing CSS styles remain here */
    .dark-mode .input-text {
        width: 50px;
        text-align: center;
        background: #25274a;
        border: 1px solid #3c3f76;
        color: #ffffff;
        border-radius: 5px !important;
    }

    .input-text {
        width: 50px;
        text-align: center;
        border: 1px solid #3c3f76;
        border-radius: 5px !important;
    }

    .head-text {
        font-size: 1.125rem;
        font-weight: 700;
    }

    .dark-mode .head-text {
        color: white !important;
        font-size: 1.125rem;
        font-weight: 700;
    }

    .dark-mode .para-text {
        font-size: 0.875rem;
        font-weight: 500;
        color: white;
    }

    .dark-mode .para-text-table {
        font-size: 10px;
        font-weight: 500;
        color: white;
    }

    /* Add to your existing CSS */
    .spinner-border {
        display: inline-block;
        width: 2rem;
        height: 2rem;
        vertical-align: text-bottom;
        border: 0.25em solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: spinner-border .75s linear infinite;
    }

    @keyframes spinner-border {
        to {
            transform: rotate(360deg);
        }
    }

    /* Checkbox styling */
    .employee-checkbox {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        border: 2px solid #cbd5e1;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
    }

    .employee-checkbox:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    /* Employee avatar hover effect */
    .employee-avatar:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .thumbnail-card {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        padding-top: 10px;
        padding-left: 10px;
    }

    .export-button {
        display: flex;
        align-items: center;
        gap: 6px;
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 14px;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }

    .export-button:hover {
        background-color: #f1f1f1;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .dropdown-menu-export {
        font-size: 14px;
        min-width: 140px;
    }

    .dropdown-menu-export .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .custom-button {
        display: flex;
        align-items: center;
        gap: 6px;
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 14px;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }

    .custom-button:hover {
        background-color: #f1f1f1;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .custom-button svg {
        width: 16px;
        height: 16px;
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 1rem;
        animation: fade-in 0.3s ease-out;
    }

    .modal-container {
        width: 100%;
        max-width: 28rem;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        animation: slide-in-top 0.3s ease-out;
    }

    .modal-container.max-w-3xl {
        max-width: 48rem;
    }

    .modal-container.max-w-4xl {
        max-width: 80%;
        max-height: 80%;
    }

    .modal-content {
        max-height: calc(100vh - 4rem);
        overflow-y: auto;
    }

    .hidden {
        display: none !important;
    }

    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.05);
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 3px;
    }

    /* Animations */
    @keyframes fade-in {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slide-in-top {
        from {
            transform: translateY(-10px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes slide-in-right {
        from {
            transform: translateX(20px);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    /* Form Styles */
    .input-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .input-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .input-required {
        color: #ef4444;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: #1e293b;
        transition: all 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }

    /* Payroll-specific styles */
    .payroll-system * {
        box-sizing: border-box;
    }

    .payroll-system button:hover {
        opacity: 0.9;
    }

    .payroll-system input:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1) !important;
        outline: none;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border: 1px solid;
    }

    .status-open {
        background-color: #dbeafe;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .status-pending {
        background-color: #f1f5f9;
        color: #475569;
        border-color: #e2e8f0;
    }

    .status-processed {
        background-color: #d1fae5;
        color: #065f46;
        border-color: #a7f3d0;
    }

    .status-held {
        background-color: #fef3c7;
        color: #92400e;
        border-color: #fde68a;
    }

    /* Glass Card */
    .glass-card {
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .glass-card.hover-effect:hover {
        border-color: #93c5fd;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
        transition: all 0.3s;
    }

    /* UI Fixes */
    .payroll-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .payroll-table th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 600;
        padding: 16px;
        border-bottom: 1px solid #e2e8f0;
        text-transform: uppercase;
        font-size: 0.75rem;
        text-align: left;
    }

    .payroll-table td {
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .employee-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .employee-info {
        display: flex;
        flex-direction: column;
        min-width: 180px;
    }

    .dark-mode .employee-name {
        font-weight: 700;
        color: white;
        font-size: 0.875rem;
    }

    .employee-name {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.875rem;
    }

    .employee-code {
        font-size: 0.75rem;
        color: #64748b;
    }

    .employee-department {
        font-size: 0.625rem;
        color: #94a3b8;
        margin-top: 2px;
    }

    /* Action buttons container */
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .action-btn {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .view-btn {
        background: #dbeafe;
        color: #2563eb;
        border-color: #bfdbfe;
    }

    .hold-btn {
        background: #fef3c7;
        color: #b45309;
        border-color: #fde68a;
    }

    .release-btn {
        background: #d1fae5;
        color: #059669;
        border-color: #a7f3d0;
    }

    /* Stepper styles */
    .stepper-container {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 32px;
        margin-bottom: 32px;
    }

    .stepper-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        position: relative;
        width: 80px;
    }

    .stepper-connector {
        position: absolute;
        top: 16px;
        left: 40px;
        width: calc(100% - 32px);
        height: 2px;
        background: #e2e8f0;
    }

    .stepper-connector.active {
        background: #2563eb;
    }

    .stepper-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.875rem;
        border: 2px solid #e2e8f0;
        background: white;
        color: #94a3b8;
        z-index: 1;
    }

    .stepper-circle.active {
        background: #2563eb;
        border-color: #2563eb;
        color: white;
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
    }

    .stepper-label {
        font-size: 0.625rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
    }

    .stepper-label.active {
        color: #2563eb;
    }

    /* Success Overlay Styles - NEWLY ADDED */
    .success-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        animation: fade-in 0.3s ease-out;
    }

    .success-icon-container {
        padding: 32px;
        background: #10b981;
        border-radius: 50%;
        border: 8px solid #a7f3d0;
        margin-bottom: 24px;
        animation: success-pop 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    .success-icon-container.lock {
        background: #2563eb;
        border-color: #93c5fd;
    }

    .success-icon-container.check {
        background: #10b981;
        border-color: #a7f3d0;
    }

    @keyframes success-pop {
        0% {
            transform: scale(0.5);
            opacity: 0;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .success-message {
        font-size: 2.25rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 16px;
        text-align: center;
    }

    .success-subtext {
        color: #64748b;
        font-size: 1.125rem;
        text-align: center;
        max-width: 28rem;
        margin: 0 auto 32px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .payroll-system .grid-cols-2 {
            grid-template-columns: 1fr !important;
        }

        .payroll-system .flex-col-md-row {
            flex-direction: column !important;
        }

        .modal-container {
            margin: 1rem;
            max-height: calc(100vh - 2rem);
        }

        .action-buttons {
            flex-direction: column;
        }

        .stepper-container {
            gap: 16px;
        }

        .stepper-step {
            width: 60px;
        }

        .success-message {
            font-size: 1.75rem;
        }

        .success-subtext {
            font-size: 1rem;
        }
    }

    /* ============================================
                                                           MODAL STYLES - REACT LIKE
                                                           ============================================ */

    /* Modal Overlay with backdrop blur */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 1rem;
        animation: fadeIn 0.3s ease-out;
    }

    .modal-overlay.hidden {
        display: none !important;
    }

    /* Modal Container */
    .modal-container {
        width: 100%;
        max-width: 28rem;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        animation: slideInTop 0.3s ease-out;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        border: 1px solid #e2e8f0;
    }

    .modal-container.max-w-3xl {
        max-width: 48rem;
    }

    .modal-container.max-w-4xl {
        max-width: 56rem;
    }

    /* Modal Header */
    .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .modal-close-btn {
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        padding: 0.375rem;
        border-radius: 0.375rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-close-btn:hover {
        background: #f1f5f9;
        color: #475569;
    }

    /* Modal Body */
    .modal-body {
        padding: 1.5rem;
        overflow-y: auto;
        flex: 1;
        max-height: calc(90vh - 120px);
    }

    /* Modal Footer */
    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    /* Modal Form Elements */
    .modal-input-group {
        margin-bottom: 1.25rem;
    }

    .modal-input-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .modal-input-label.required::after {
        content: " *";
        color: #ef4444;
    }

    .modal-input {
        width: 100%;
        padding: 0.625rem 0.875rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: #374151;
        background: white;
        transition: all 0.2s;
    }

    .modal-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .modal-textarea {
        min-height: 100px;
        resize: vertical;
    }

    /* Modal Buttons */
    .modal-btn {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
    }

    .modal-btn-primary {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    .modal-btn-primary:hover {
        background: #2563eb;
        border-color: #2563eb;
    }

    .modal-btn-secondary {
        background: white;
        color: #374151;
        border-color: #d1d5db;
    }

    .modal-btn-secondary:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    /* Employee List in Modal */
    .modal-employee-list {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background: white;
    }

    .modal-employee-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f3f4f6;
        cursor: pointer;
        transition: background 0.2s;
    }

    .modal-employee-item:hover {
        background: #f9fafb;
    }

    .modal-employee-item:last-child {
        border-bottom: none;
    }

    .modal-employee-checkbox {
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid #d1d5db;
        border-radius: 0.25rem;
        margin-right: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s;
    }

    .modal-employee-checkbox.checked {
        background: #3b82f6;
        border-color: #3b82f6;
    }

    .modal-employee-checkbox.checked::after {
        content: "✓";
        color: white;
        font-size: 0.75rem;
        font-weight: bold;
    }

    .modal-employee-avatar {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
        margin-right: 0.75rem;
    }

    .modal-employee-info {
        flex: 1;
    }

    .modal-employee-name {
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.125rem;
    }

    .modal-employee-details {
        font-size: 0.75rem;
        color: #6b7280;
    }

    /* Modal Grid */
    .modal-grid {
        display: grid;
        gap: 1rem;
    }

    .modal-grid-2 {
        grid-template-columns: repeat(2, 1fr);
    }

    /* Alert Cards in Modal */
    .modal-alert {
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        font-size: 0.875rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .modal-alert.warning {
        background: #fef3c7;
        border: 1px solid #fde68a;
        color: #92400e;
    }

    .modal-alert.info {
        background: #dbeafe;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
    }

    /* Empty State */
    .modal-empty-state {
        text-align: center;
        padding: 2rem;
        color: #9ca3af;
    }

    /* Scrollbar Styling */
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    /* Dark Mode Support */
    .dark-mode .modal-container {
        background: #1f2937;
        border-color: #374151;
    }

    .dark-mode .modal-header {
        background: #111827;
        border-color: #374151;
    }

    .dark-mode .modal-title {
        color: #f9fafb;
    }

    .dark-mode .modal-body {
        background: #1f2937;
    }

    .dark-mode .modal-footer {
        background: #111827;
        border-color: #374151;
    }

    .dark-mode .modal-input {
        background: #374151;
        border-color: #4b5563;
        color: #f9fafb;
    }

    /* Animation Keyframes */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideInTop {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .modal-container {
            margin: 0.5rem;
            max-height: 85vh;
        }

        .modal-grid-2 {
            grid-template-columns: 1fr;
        }

        .modal-body {
            padding: 1rem;
        }
    }

    /* Success Overlay (keep existing but update) */
    .success-overlay {
        position: fixed;
        inset: 0;
        z-index: 10000;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes successPop {
        0% {
            transform: scale(0.5);
            opacity: 0;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    /* ============================================
                                       SCROLLABLE TABLES WITH FIXED HEADERS
                                       ============================================ */

    /* Attendance Table Container */
    .attendance-table-container {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 80px;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        position: relative;
        height: 500px;
        /* Fixed height for scrolling */
    }

    .attendance-table-wrapper {
        overflow-x: auto;
        overflow-y: auto;
        height: 100%;
        position: relative;
    }

    /* Attendance Table with fixed header */
    #attendanceTable {
        width: 100%;
        text-align: center;
        border-collapse: collapse;
        min-width: 1200px;
        position: relative;
    }

    #attendanceTable thead {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f8fafc;
    }

    #attendanceTable th {
        padding: 12px;
        position: sticky;
        color: #64748b;
        font-size: 10px;
        text-transform: uppercase;
        font-weight: 600;
        border-bottom: 2px solid #e2e8f0;
        background: #f8fafc;
    }

    #attendanceTable th:nth-child(1) {
        left: 0;
        z-index: 20;
        background: #f8fafc;
    }

    #attendanceTable th:nth-child(2) {
        left: 70px;
        z-index: 20;
        background: #f8fafc;
        text-align: left;
    }

    #attendanceTable tbody {
        overflow-y: auto;
    }

    #attendanceTable td {
        padding: 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 12px;
        color: #475569;
    }

    #attendanceTable td:nth-child(1),
    #attendanceTable td:nth-child(2) {
        position: sticky;
        left: 0;
        z-index: 5;
        background: white;
    }

    #attendanceTable td:nth-child(2) {
        left: 70px;
        text-align: left;
        background: white;
    }

    /* Salary List Table Container */
    .salary-table-container {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        margin-top: 16px;
        height: 600px;
        /* Fixed height for scrolling */
    }

    .salary-table-wrapper {
        overflow-x: auto;
        overflow-y: auto;
        height: 100%;
        position: relative;
    }

    /* Salary Table with fixed header */
    .payroll-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
        min-width: 800px;
        position: relative;
    }

    .payroll-table thead {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f8fafc;
    }

    .payroll-table th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 600;
        padding: 16px;
        border-bottom: 2px solid #e2e8f0;
        text-transform: uppercase;
        font-size: 0.75rem;
        text-align: left;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .payroll-table th:first-child {
        left: 0;
        z-index: 20;
        background: #f8fafc;
    }

    .payroll-table tbody {
        overflow-y: auto;
    }

    .payroll-table td {
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .payroll-table td:first-child {
        position: sticky;
        left: 0;
        z-index: 5;
        background: white;
    }

    /* Make the first column in salary list also fixed */
    .payroll-table th:nth-child(2),
    .payroll-table td:nth-child(2) {
        position: sticky;
        left: 48px;
        /* Width of first column (checkbox) */
        z-index: 5;
        background: white;
    }

    .payroll-table th:nth-child(2) {
        left: 48px;
        z-index: 20;
        background: #f8fafc;
    }

    /* Employee cell in salary table */
    .employee-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 180px;
    }

    /* Dark mode adjustments */
    .dark-mode .attendance-table-container,
    .dark-mode .salary-table-container {
        border-color: #374151;
    }

    .dark-mode #attendanceTable th,
    .dark-mode .payroll-table th {
        background: #1f2937;
        color: #9ca3af;
        border-bottom-color: #4b5563;
    }

    .dark-mode #attendanceTable td,
    .dark-mode .payroll-table td {
        background: #1f2937;
        color: #d1d5db;
        border-bottom-color: #374151;
    }

    .dark-mode #attendanceTable td:nth-child(1),
    .dark-mode #attendanceTable td:nth-child(2),
    .dark-mode .payroll-table td:first-child,
    .dark-mode .payroll-table td:nth-child(2) {
        background: #1f2937;
    }

    /* Scrollbar styling */
    .attendance-table-wrapper::-webkit-scrollbar,
    .salary-table-wrapper::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .attendance-table-wrapper::-webkit-scrollbar-track,
    .salary-table-wrapper::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .attendance-table-wrapper::-webkit-scrollbar-thumb,
    .salary-table-wrapper::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .attendance-table-wrapper::-webkit-scrollbar-thumb:hover,
    .salary-table-wrapper::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .dark-mode .attendance-table-wrapper::-webkit-scrollbar-track,
    .dark-mode .salary-table-wrapper::-webkit-scrollbar-track {
        background: #374151;
    }

    .dark-mode .attendance-table-wrapper::-webkit-scrollbar-thumb,
    .dark-mode .salary-table-wrapper::-webkit-scrollbar-thumb {
        background: #4b5563;
    }

    .dark-mode .attendance-table-wrapper::-webkit-scrollbar-thumb:hover,
    .dark-mode .salary-table-wrapper::-webkit-scrollbar-thumb:hover {
        background: #6b7280;
    }

    /* Table row hover effects */
    #attendanceTable tbody tr:hover td,
    .payroll-table tbody tr:hover td {
        background-color: #f8fafc;
    }

    .dark-mode #attendanceTable tbody tr:hover td,
    .dark-mode .payroll-table tbody tr:hover td {
        background-color: #2d3748;
    }
</style>
@endsection

@section('content')
{{-- Breadcrumbs Start --}}
<div class="p-0 mt-3">
    <div class="row">
        <div class="col-md-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('/payroll/payroll-cycles') }}">Payroll Cycles</a></li>
                <li class="active"><span><b>Weekly Salary Process</b></span></li>
            </ol>
        </div>
        <div class="col-md-6"></div>
        <div class="col-md-2"></div>
    </div>
</div>
{{-- Breadcrumbs End --}}

<!-- Success Overlay Container - NEWLY ADDED -->
<div id="success-overlay" class="success-overlay" style="display: none;">
    <div id="success-icon-container" class="success-icon-container check">
        <i data-lucide="check-circle" style="width: 64px; height: 64px; color: white;"></i>
    </div>
    <h2 id="success-message" class="success-message">Success!</h2>
    <p id="success-subtext" class="success-subtext">Operation completed successfully.</p>
</div>

<div class="row mt-5">
    <div class="col-xl-12 col-md-12 col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <h4 class="card-title">Weekly Salary Process</h4>
            </div>

            <div class="card-body">
                <!-- Payroll UI Container -->
                <div class="payroll-system">
                    <!-- Main Content -->
                    <div class="relative z-10" style="padding: 16px 0;">
                        <!-- Dashboard View -->
                        <div data-view="DASHBOARD" style="animation: fade-in 0.5s ease-out;">
                            <div style="display: grid; grid-template-columns: 1fr; gap: 32px;">
                                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 32px;">
                                    <!-- Left Column: Payroll Period Card -->
                                    <div style="display: flex; flex-direction: column; gap: 24px;">
                                        <!-- Payroll Period Card -->
                                        <div class="glass-card hover-effect">
                                            <div style="padding: 20px;">
                                                <!-- Period Header -->
                                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                                                    <div>
                                                        <h3 class="text-primary">Week {{ $weekNumber }} Payroll</h3>
                                                        <p style="font-size: 0.875rem; color: #64748b; margin-bottom: 4px;">
                                                            {{ $monthName }} {{ $year }}
                                                        </p>
                                                        <div style="display: flex; align-items: center; gap: 8px;">
                                                            <span style="font-size: 0.75rem; color: #64748b;">
                                                                {{ \Carbon\Carbon::parse($weekStart)->format('d M') }} - {{ \Carbon\Carbon::parse($weekEnd)->format('d M, Y') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="status-badge status-{{ strtolower($status) }}">
                                                        <i data-lucide="clock" style="width: 12px; height: 12px;"></i>
                                                        {{ ucfirst($status) }}
                                                    </div>
                                                </div>

                                                <!-- Period Details -->
                                                <div style="margin-bottom: 16px; padding: 12px; background: #9e9e9e2b; border-radius: 8px;">
                                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                                        <div>
                                                            <div style="font-size: 0.75rem; color: #64748b;">Week Days</div>
                                                            <div style="font-size: 0.875rem; font-weight: 600; color: #475569;">7 Days</div>
                                                        </div>
                                                        <div>
                                                            <div style="font-size: 0.75rem; color: #64748b;">Working Days</div>
                                                            <div style="font-size: 0.875rem; font-weight: 600; color: #475569;">5 Days</div>
                                                        </div>
                                                    </div>
                                                    <div style="font-size: 0.75rem; color: #64748b;">
                                                        Week {{ $weekNumber }} • {{ $monthName }} {{ $year }}
                                                    </div>
                                                </div>

                                                <!-- Action Buttons -->
                                                <button data-action="start-process"
                                                    data-period-id="{{ $payrollPeriod->pp_id }}"
                                                    data-week-id="{{ $week->ppw_id }}"
                                                    style="margin-bottom: 16px;padding: 12px 32px; background: transparent; color: #2563eb; font-weight: bold; border-radius: 12px; border: 1px solid #2563eb; display: flex; align-items: center; gap: 8px; transition: all 0.2s; cursor: pointer; width: 100%; justify-content: center;">
                                                    <i data-lucide="play" style="width: 16px; height: 16px;"></i>
                                                    Start Weekly Payroll Process
                                                </button>

                                                <div style="display: flex; gap: 8px;">
                                                    <a href="{{ route('adhoc.index') }}?payroll_id={{ $payrollPeriod->pp_id }}"
                                                        style="flex: 1; padding: 8px; background: #f8fafc; color: #475569; border-radius: 8px; font-size: 0.75rem; font-weight: 700; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;">
                                                        <i data-lucide="plus-circle" style="width: 14px; height: 14px;"></i>
                                                        Ad-Hoc
                                                    </a>
                                                    <button data-action="open-cycle-details"
                                                        data-period-id="{{ $payrollPeriod->pp_id }}"
                                                        style="flex: 1; padding: 8px; background: #f8fafc; color: #475569; border-radius: 8px; font-size: 0.75rem; font-weight: 700; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer;">
                                                        <i data-lucide="info" style="width: 14px; height: 14px;"></i>
                                                        Details
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Quick Actions -->
                                        <div class="glass-card hover-effect">
                                            <div style="padding: 16px;">
                                                <h4 class="head-text">
                                                    <i data-lucide="zap" style="width: 16px; height: 16px; color: #f59e0b;"></i>
                                                    Quick Actions
                                                </h4>
                                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                                    <a href="{{ ($isWeeklyPayrollMode ?? false) ? route('payroll.weekly.payslip.list', ['payrollId' => $payrollPeriod->pp_id, 'weekId' => $week->ppw_id]) : route('payroll.payslip.list', ['payrollId' => $payrollPeriod->pp_id]) }}"
                                                        class="para-text"
                                                        style="background:#9e9e9e2b; padding: 8px 12px; border-radius: 8px; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                                                        <i data-lucide="file-text" style="width: 14px; height: 14px;"></i>
                                                        @if($isWeeklyPayrollMode ?? false)
                                                            View Payslips ({{ $processedEmployeesCount }})
                                                        @else
                                                            View Payslips
                                                        @endif
                                                    </a>

                                                    <a href="{{ route('payroll.cycles.weekly') }}"
                                                        style="padding: 8px 12px; background: #f8fafc; color: #475569; border-radius: 8px; font-size: 0.75rem; font-weight: 600; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                                                        <i data-lucide="calendar" style="width: 14px; height: 14px; color: #059669;"></i>
                                                        All Cycles
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Column: Statistics -->
                                    <div style="display: flex; flex-direction: column; gap: 24px;">
                                        <!-- Employee Statistics -->
                                        <div class="glass-card hover-effect">
                                            <div style="padding: 20px;">
                                                <h3 class="head-text" style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                                                    <i data-lucide="users" style="width: 20px; height: 20px; color: #2563eb;"></i>
                                                    Employee Statistics
                                                </h3>

                                                <!-- Compact Stats Grid - 3x2 Layout -->
                                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px;">
                                                    <div class="glass-card hover-effect" style="padding: 16px;">
                                                        <div style="font-size: 0.65rem; color: #3b82f6;">Total</div>
                                                        <div style="font-size: 1.25rem; font-weight: 700;">{{ $totalEmployees }}</div>
                                                        <div style="font-size: 0.6rem; color: #64748b;">Eligible</div>
                                                    </div>

                                                    <div class="glass-card hover-effect" style="padding: 16px;">
                                                        <div style="font-size: 0.65rem; color: #16a34a;">Active</div>
                                                        <div style="font-size: 1.25rem; font-weight: 700;">{{ $totalActiveEmployees }}</div>
                                                        <div style="font-size: 0.6rem; color: #64748b;">Working</div>
                                                    </div>

                                                    <div class="glass-card hover-effect" style="padding: 16px;">
                                                        <div style="font-size: 0.65rem; color: #dc2626;">Inactive</div>
                                                        <div style="font-size: 1.25rem; font-weight: 700;">{{ $totalInactiveEmp }}</div>
                                                        <div style="font-size: 0.6rem; color: #64748b;">Overlapping</div>
                                                    </div>

                                                    <div class="glass-card hover-effect" style="padding: 16px;">
                                                        <div style="font-size: 0.65rem; color: #16a34a;">Ready</div>
                                                        <div style="font-size: 1.25rem; font-weight: 700;">{{ $readyToProcess }}</div>
                                                        <div style="font-size: 0.6rem; color: #64748b;">Pending</div>
                                                    </div>

                                                    <div class="glass-card hover-effect" style="padding: 16px;">
                                                        <div style="font-size: 0.65rem; color: #ea580c;">On Hold</div>
                                                        <div style="font-size: 1.25rem; font-weight: 700;">{{ $heldEmployees }}</div>
                                                        <div style="font-size: 0.6rem; color: #64748b;">Held</div>
                                                    </div>

                                                    <div class="glass-card hover-effect" style="padding: 16px;">
                                                        <div style="font-size: 0.65rem; color: #9333ea;">Processed</div>
                                                        <div style="font-size: 1.25rem; font-weight: 700;">{{ $processedEmployeesCount }}</div>
                                                        <div style="font-size: 0.6rem; color: #64748b;">Completed</div>
                                                    </div>
                                                </div>

                                                <!-- Status Breakdown -->
                                                <div>
                                                    <h4 class="head-text">Status Breakdown</h4>
                                                    <div style="display: flex; flex-direction: column; gap: 8px;">
                                                        <div style="display: flex; justify-content: space-between; background: #9e9e9e2b; align-items: center; padding: 8px 12px; border-radius: 6px;">
                                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                                <div style="width: 8px; height: 8px; background: #94a3b8; border-radius: 50%;"></div>
                                                                <span class="para-text">Pending (Ready to Process)</span>
                                                            </div>
                                                            <span style="font-weight: 700; color: #475569;">{{ $pendingEmployees }}</span>
                                                        </div>

                                                        <div style="display: flex; justify-content: space-between; background: #9e9e9e2b; align-items: center; padding: 8px 12px; border-radius: 6px;">
                                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                                <div style="width: 8px; height: 8px; background: #f59e0b; border-radius: 50%;"></div>
                                                                <span class="para-text">On Hold</span>
                                                            </div>
                                                            <span style="font-weight: 700; color: #92400e;">{{ $heldEmployees }}</span>
                                                        </div>

                                                        <div style="display: flex; justify-content: space-between; background: #9e9e9e2b; align-items: center; padding: 8px 12px; border-radius: 6px;">
                                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                                <div style="width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></div>
                                                                <span class="para-text">Processed</span>
                                                            </div>
                                                            <span style="font-weight: 700; color: #059669;">{{ $processedEmployeesCount }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 1: Action Required - Updated Cards Layout (3 per row) -->
                        <div data-view="STEP1" style="display: none; animation: slide-in-right 0.5s ease-out;">
                            <!-- Workflow Stepper -->
                            <div class="stepper-container">
                                @for ($i = 1; $i <= 5; $i++)
                                    <div class="stepper-step">
                                        @if ($i < 5)
                                            <div class="stepper-connector {{ $i < 1 ? 'active' : '' }}"></div>
                                        @endif
                                        <div class="stepper-circle {{ $i == 1 ? 'active' : '' }}">{{ $i }}</div>
                                        <div class="stepper-label {{ $i == 1 ? 'active' : '' }}">
                                            @if($i == 1) Approval
                                            @elseif($i == 2) Freeze
                                            @elseif($i == 3) Process
                                            @elseif($i == 4) Verify
                                            @else Finish
                                            @endif
                                        </div>
                                    </div>
                                @endfor
                            </div>

                            <input type="hidden" id="server-current-step" value="{{ $currentStep }}">

                            <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <button data-action="back-to-dashboard" data-period-id="{{ $payrollPeriod->pp_id }}" data-week-id="{{ $week->ppw_id }}"
                                        style="padding: 8px; background: white; border: 1px solid #e2e8f0; border-radius: 50%; color: #94a3b8; transition: all 0.2s; cursor: pointer;">
                                        <i data-lucide="arrow-left" style="width: 20px; height: 20px;"></i>
                                    </button>

                                    <div>
                                        <h2 class="head-text">Weekly Salary Process</h2>
                                        <p style="font-size: 0.75rem; color: #64748b;" id="step1WeekInfo">
                                            Week {{ $weekNumber }}: {{ \Carbon\Carbon::parse($weekStart)->format('d M') }} - {{ \Carbon\Carbon::parse($weekEnd)->format('d M, Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            @if($pendingRequests['total_pending'] > 0)
                            <div style="margin-bottom: 24px; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 8px; padding: 16px; display: flex; align-items: flex-start; gap: 12px;">
                                <div style="padding: 8px; background: rgba(220, 38, 38, 0.1); border-radius: 50%; color: #dc2626;">
                                    <i data-lucide="alert-triangle" style="width: 16px; height: 16px;"></i>
                                </div>
                                <div>
                                    <h4 style="color: #991b1b; font-weight: bold; font-size: 0.875rem; margin: 0;">Pending Requests Detected!</h4>
                                    <p style="color: #dc2626; font-size: 0.75rem; line-height: 1.5; margin-top: 4px; margin-bottom: 0;">
                                        You cannot proceed until all pending requests are resolved.
                                    </p>
                                </div>
                            </div>
                            @else
                            <div style="margin-bottom: 24px; background: #dbeafe; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; display: flex; align-items: flex-start; gap: 12px;">
                                <div style="padding: 8px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; color: #2563eb;">
                                    <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                                </div>
                                <div>
                                    <h4 style="color: #1e3a8a; font-weight: bold; font-size: 0.875rem; margin: 0;">All Clear!</h4>
                                    <p style="color: #1d4ed8; font-size: 0.75rem; line-height: 1.5; margin-top: 4px; margin-bottom: 0;">
                                        No pending requests found. You can safely proceed to Freeze Grid.
                                    </p>
                                </div>
                            </div>
                            @endif

                            <!-- First Row: 3 Cards -->
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; max-width: 80rem; margin: 0 auto 24px;">
                                <!-- Missed Punches Card -->
                                <div class="glass-card hover-effect" style="border-left: 4px solid #f59e0b;">
                                    <div style="padding: 16px;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <div style="padding: 8px; background: #fef3c7; border-radius: 8px; color: #d97706;">
                                                    <i data-lucide="alert-circle" style="width: 24px; height: 24px;"></i>
                                                </div>
                                                <div><h3 class="head-text">Missed Punches</h3></div>
                                            </div>
                                            <span id="missed-punches-count" class="card-count" style="background: #fef3c7; color: #92400e; font-weight: bold; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem;">
                                                {{ $pendingRequests['missed_punches'] }} Pending
                                            </span>
                                        </div>
                                        <div id="missed-punches-example" class="example-content" style="border-radius: 8px; padding: 12px; margin-bottom: 16px; border: 1px solid #e2e8f0;">
                                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                                                @if($pendingRequests['missed_punches'] > 0)
                                                    <span class="para-text">{{ $pendingRequests['missed_punches'] }} pending missed punches</span>
                                                    <span style="color: #d97706; font-weight: bold;">{{ $pendingRequests['missed_punches'] }}</span>
                                                @else
                                                    <span class="para-text">No pending requests</span>
                                                    <span style="color: #d97706; font-weight: bold;">--</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($pendingRequests['missed_punches'] > 0)
                                            <a href="{{ $pendingRequests['missed_punch_url'] }}?payroll_id={{ $pendingRequests['payroll_id'] }}&week_id={{ $week->ppw_id }}" target="_blank"
                                                style="width: 100%; padding: 8px; background: #f59e0b; color: white; border: 1px solid #d97706; border-radius: 8px; font-size: 0.75rem; font-weight: bold; text-decoration: none; display: inline-block; text-align: center;">
                                                Resolve Missed Punches
                                            </a>
                                        @else
                                            <button class="action-btn" style="width: 100%; padding: 8px; background: white; color: #475569; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.75rem; font-weight: bold;" disabled>No Actions Required</button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Leave Requests Card -->
                                <div class="glass-card hover-effect" style="border-left: 4px solid #f43f5e;">
                                    <div style="padding: 16px;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <div style="padding: 8px; background: #fee2e2; border-radius: 8px; color: #dc2626;">
                                                    <i data-lucide="calendar" style="width: 24px; height: 24px;"></i>
                                                </div>
                                                <div><h3 class="head-text">Leave Requests</h3></div>
                                            </div>
                                            <span id="leave-requests-count" class="card-count" style="background: #fee2e2; color: #b91c1c; font-weight: bold; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem;">
                                                {{ $pendingRequests['leave_requests'] }} Pending
                                            </span>
                                        </div>
                                        <div id="leave-requests-example" class="example-content" style="border-radius: 8px; padding: 12px; margin-bottom: 16px; border: 1px solid #e2e8f0;">
                                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                                                @if($pendingRequests['leave_requests'] > 0)
                                                    <span class="para-text">{{ $pendingRequests['leave_requests'] }} pending leave requests</span>
                                                    <span style="color: #dc2626; font-weight: bold;">{{ $pendingRequests['leave_requests'] }}</span>
                                                @else
                                                    <span class="para-text">No pending requests</span>
                                                    <span style="color: #dc2626; font-weight: bold;">--</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($pendingRequests['leave_requests'] > 0)
                                            <a href="{{ route('requests.leave') }}?payroll_id={{ $pendingRequests['payroll_id'] }}&week_id={{ $week->ppw_id }}" target="_blank"
                                                style="width: 100%; padding: 8px; background: #ef4444; color: white; border: 1px solid #b91c1c; border-radius: 8px; font-size: 0.75rem; font-weight: bold; text-decoration: none; display: inline-block; text-align: center;">
                                                View Leave Requests
                                            </a>
                                        @else
                                            <button class="action-btn" style="width: 100%; padding: 8px; background: white; color: #475569; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.75rem; font-weight: bold;" disabled>No Actions Required</button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Over Time Requests Card -->
                                <div class="glass-card hover-effect" style="border-left: 4px solid #8b5cf6;">
                                    <div style="padding: 16px;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <div style="padding: 8px; background: #ede9fe; border-radius: 8px; color: #7c3aed;">
                                                    <i data-lucide="clock" style="width: 24px; height: 24px;"></i>
                                                </div>
                                                <div><h3 class="head-text">Over Time Requests</h3></div>
                                            </div>
                                            <span id="overtime-requests-count" class="card-count" style="background: #ede9fe; color: #7c3aed; font-weight: bold; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem;">
                                                {{ $pendingRequests['overtime_requests'] }} Pending
                                            </span>
                                        </div>
                                        <div id="overtime-requests-example" class="example-content" style="border-radius: 8px; padding: 12px; margin-bottom: 16px; border: 1px solid #e2e8f0;">
                                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                                                @if($pendingRequests['overtime_requests'] > 0)
                                                    <span class="para-text">{{ $pendingRequests['overtime_requests'] }} pending overtime requests</span>
                                                    <span style="color: #7c3aed; font-weight: bold;">{{ $pendingRequests['overtime_requests'] }}</span>
                                                @else
                                                    <span class="para-text">No pending requests</span>
                                                    <span style="color: #7c3aed; font-weight: bold;">--</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($pendingRequests['overtime_requests'] > 0)
                                            <a href="{{ route('approve.overtime') }}?payroll_id={{ $pendingRequests['payroll_id'] }}&week_id={{ $week->ppw_id }}" target="_blank"
                                                style="width: 100%; padding: 8px; background: #7c3aed; color: white; border: 1px solid #5b21b6; border-radius: 8px; font-size: 0.75rem; font-weight: bold; text-decoration: none; display: inline-block; text-align: center;">
                                                Approve Overtime
                                            </a>
                                        @else
                                            <button class="action-btn" style="width: 100%; padding: 8px; background: white; color: #475569; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.75rem; font-weight: bold;" disabled>No Actions Required</button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div style="display: flex; justify-content: flex-end; margin-top: 32px; gap: 16px;">
                                @if($pendingRequests['total_pending'] > 0)
                                    <button style="padding: 12px 32px; background: #dc2626; color: white; font-weight: bold; border-radius: 12px; border: 2px solid #dc2626; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        Resolve Pending Requests First
                                        <i data-lucide="alert-triangle" style="width: 18px; height: 18px;"></i>
                                    </button>
                                @else
                                    <button data-action="go-step2" data-period-id="{{ $payrollPeriod->pp_id }}" data-week-id="{{ $week->ppw_id }}"
                                        style="padding: 12px 32px; background: transparent; color: #2563eb; font-weight: bold; border-radius: 12px; border: 2px solid #2563eb; display: flex; align-items: center; gap: 8px; transition: all 0.2s; cursor: pointer;">
                                        Proceed to Freeze Weekly Attendance
                                        <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Step 2: Freeze Grid - Weekly Attendance -->
                        <div data-view="STEP2" style="display: none; animation: slide-in-right 0.5s ease-out;">
                            <!-- Workflow Stepper -->
                            <div class="stepper-container">
                                @for ($i = 1; $i <= 5; $i++)
                                    <div class="stepper-step">
                                        @if ($i < 5)
                                            <div class="stepper-connector {{ $i < 2 ? 'active' : '' }}"></div>
                                        @endif
                                        <div class="stepper-circle {{ $i <= 2 ? 'active' : '' }}">{{ $i }}</div>
                                        <div class="stepper-label {{ $i <= 2 ? 'active' : '' }}">
                                            @if($i == 1) Approval
                                            @elseif($i == 2) Freeze
                                            @elseif($i == 3) Process
                                            @elseif($i == 4) Verify
                                            @else Finish
                                            @endif
                                        </div>
                                    </div>
                                @endfor
                            </div>

                            <!-- Process Info with Week Details -->
                            <div style="margin-bottom: 24px; background: #dbeafe; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; display: flex; align-items: flex-start; gap: 12px;">
                                <div style="padding: 8px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; color: #2563eb;">
                                    <i data-lucide="info" style="width: 16px; height: 16px;"></i>
                                </div>
                                <div>
                                    <h4 style="color: #1e3a8a; font-weight: bold; font-size: 0.875rem; margin: 0;">Week {{ $weekNumber }} Attendance Finalization</h4>
                                    <p style="color: #1d4ed8; font-size: 0.75rem; line-height: 1.5; margin-top: 4px; margin-bottom: 0;">
                                        {{ \Carbon\Carbon::parse($weekStart)->format('d M Y') }} - {{ \Carbon\Carbon::parse($weekEnd)->format('d M Y') }}
                                    </p>
                                </div>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
                                    <div style="display: flex; align-items: center; gap: 16px;">
                                        <button data-action="back-to-checklist"
                                            style="padding: 8px; background: white; border: 1px solid #e2e8f0; border-radius: 50%; color: #94a3b8; cursor: pointer;">
                                            <i data-lucide="arrow-left" style="width: 20px; height: 20px;"></i>
                                        </button>
                                        <div>
                                            <h2 class="head-text">Weekly Attendance Sheet</h2>
                                            <p style="font-size: 0.75rem; color: #64748b;">Week {{ $weekNumber }} - {{ \Carbon\Carbon::parse($weekStart)->format('d M') }} to {{ \Carbon\Carbon::parse($weekEnd)->format('d M, Y') }}</p>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 12px; align-items: center;">
                                        <button onclick="toggleLateClear()" class="btn btn-sm btn-outline-secondary">
                                            <i data-lucide="clock"></i> Clear Late
                                        </button>
                                        <button onclick="toggleEarlyClear()" class="btn btn-sm btn-outline-secondary">
                                            <i data-lucide="log-out"></i> Clear Early
                                        </button>
                                        <input type="text" id="attendanceSearch" placeholder="Search employee..." class="form-control form-control-sm" style="width: 200px;" />
                                    </div>
                                </div>
                            </div>

                            <!-- Weekly Attendance Table -->
                            <div class="attendance-table-container">
                                <div class="attendance-table-wrapper">
                                    <table id="attendanceTable">
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Name</th>
                                                <th>Days</th>
                                                <th>Present</th>
                                                <th>Absent</th>
                                                <th>W-Off</th>
                                                <th>WOP</th>
                                                <th>Half</th>
                                                <th>Leave</th>
                                                <th>Holiday</th>
                                                <th>UPL</th>
                                                <th style="width: 6%">Late</th>
                                                <th style="width: 6%">Early</th>
                                                <th>Missed</th>
                                                <th>OT</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="attendanceTableBody">
                                            <tr><td colspan="16" style="padding:20px;text-align:center;color:#94a3b8;">Click "Proceed" to load weekly attendance</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div style="position: fixed; bottom: 24px; right: 24px; z-index: 30; display: flex; gap: 16px;">
                                <button data-action="back-to-step1" data-period-id="{{ $payrollPeriod->pp_id }}" data-week-id="{{ $week->ppw_id }}"
                                    style="padding: 12px 24px; background: white; color: #64748b; font-weight: bold; border-radius: 12px; border: 2px solid #e2e8f0; display: flex; align-items: center; gap: 8px; transition: all 0.2s; cursor: pointer;">
                                    <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                                    Back
                                </button>
                                <button type="button" data-action="freeze-attendance"
                                    style="padding: 12px 32px; background: #dc2626; color: white; font-weight: bold; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(220, 38, 38, 0.2); display: flex; align-items: center; gap: 8px; transition: all 0.2s; border: none; cursor: pointer; animation: pulse 2s infinite;">
                                    <i data-lucide="lock" style="width: 18px; height: 18px;"></i>
                                    Freeze Weekly Attendance
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Salary List - Weekly -->
                        <div data-view="STEP3" style="display: none; animation: slide-in-right 0.5s ease-out;">
                            <!-- Workflow Stepper -->
                            <div class="stepper-container">
                                @for ($i = 1; $i <= 5; $i++)
                                    <div class="stepper-step">
                                        @if ($i < 5)
                                            <div class="stepper-connector {{ $i < 3 ? 'active' : '' }}"></div>
                                        @endif
                                        <div class="stepper-circle {{ $i <= 3 ? 'active' : '' }}">{{ $i }}</div>
                                        <div class="stepper-label {{ $i <= 3 ? 'active' : '' }}">
                                            @if($i == 1) Approval
                                            @elseif($i == 2) Freeze
                                            @elseif($i == 3) Process
                                            @elseif($i == 4) Verify
                                            @else Finish
                                            @endif
                                        </div>
                                    </div>
                                @endfor
                            </div>

                            <!-- Process Info -->
                            <div style="margin-bottom: 24px; background: #dbeafe; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; display: flex; align-items: flex-start; gap: 12px;">
                                <div style="padding: 8px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; color: #2563eb;">
                                    <i data-lucide="info" style="width: 16px; height: 16px;"></i>
                                </div>
                                <div>
                                    <h4 style="color: #1e3a8a; font-weight: bold; font-size: 0.875rem; margin: 0;">Weekly Salary Processing</h4>
                                    <p style="color: #1d4ed8; font-size: 0.75rem; line-height: 1.5; margin-top: 4px; margin-bottom: 0;">
                                        Review weekly salaries, make adjustments, and process payroll.
                                    </p>
                                </div>
                            </div>

                            <!-- Compact Hold Details Card -->
                            @if($hasHolds ?? false)
                            <div id="hold-summary-card" class="glass-card hover-effect" style="margin-bottom: 16px; border-left: 4px solid #ef4444; border-radius: 8px;">
                                <div style="padding: 12px 16px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="position: relative;">
                                                <div style="padding: 6px; background: #fee2e2; border-radius: 6px; color: #dc2626;">
                                                    <i data-lucide="pause" style="width: 16px; height: 16px;"></i>
                                                </div>
                                                <div style="position: absolute; top: -6px; right: -6px; width: 18px; height: 18px; background: #dc2626; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; border: 2px solid white;">
                                                    {{ $heldEmployees }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="para-text">Salary Holds Detected</div>
                                                <div style="font-size: 0.75rem; color: #6b7280;">{{ $heldEmployees }} employee(s) on hold</div>
                                            </div>
                                        </div>
                                        <button id="view-hold-details-btn" data-cycle-id="{{ $payrollPeriod->pp_id }}" data-week-id="{{ $week->ppw_id }}"
                                            style="padding: 6px 12px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border: none; border-radius: 6px; font-size: 0.75rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                            <i data-lucide="eye" style="width: 14px; height: 14px;"></i> View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; margin-top: 16px; flex-wrap: wrap; gap: 12px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <button data-action="revert-freeze" data-payroll-id="{{ $payrollPeriod->pp_id }}" data-week-id="{{ $week->ppw_id }}"
                                        style="padding: 6px; background: white; border: 1px solid #e2e8f0; border-radius: 50%; color: #64748b; cursor: pointer; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                        <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
                                    </button>
                                    <div>
                                        <h2 class="head-text" style="font-size: 1rem; margin: 0;">Weekly Salary Process</h2>
                                        <p style="font-size: 0.7rem; color: #64748b; margin: 2px 0 0 0;">Week {{ $weekNumber }}</p>
                                    </div>
                                </div>

                                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                    <button data-action="back-to-step2" data-period-id="{{ $payrollPeriod->pp_id }}" data-week-id="{{ $week->ppw_id }}"
                                        style="padding: 8px 16px; background: white; color: #64748b; font-weight: bold; border-radius: 8px; border: 2px solid #e2e8f0; display: flex; align-items: center; gap: 8px; transition: all 0.2s; cursor: pointer; font-size: 0.8rem;">
                                        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                                        Back to Attendance
                                    </button>
                                    <div style="position: relative; min-width: 220px;">
                                        <i data-lucide="search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #94a3b8;"></i>
                                        <input type="text" id="salarySearch" placeholder="Search by name, code..." class="form-control" style="padding-left: 32px; font-size: 0.8rem;" />
                                        <button id="clearSalarySearch" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; display: none;" onclick="clearSalarySearch()">
                                            <i data-lucide="x" style="width: 14px; height: 14px;"></i>
                                        </button>
                                    </div>
                                    <select id="employeeStatusFilter" class="form-select" style="width: auto; font-size: 0.8rem;">
                                        <option value="all">All Status</option>
                                    </select>
                                    <button data-action="process-bulk" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.8rem;">
                                        <span class="process-text">Process Selected</span>
                                        <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="glass-card salary-table-container">
                                <div class="salary-table-wrapper">
                                    <table class="payroll-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 48px; text-align: center;"></th>
                                                <th>Employee</th>
                                                <th>Role</th>
                                                <th style="text-align: center;">Week Days<br><small>Total Days</small></th>
                                                <th style="text-align: center;">Salary Days<br><small>Worked Days</small></th>
                                                <th style="text-align: right;">Ad-Hoc</th>
                                                <th style="text-align: center;">Status</th>
                                                <th style="text-align: right;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="step3TableBody">
                                            <tr><td colspan="9" style="padding:20px;text-align:center;color:#94a3b8;">Click "Process Selected & Next" to load weekly salary data.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Individual Employee View -->
                        <div data-view="STEP4" style="display: none; animation: slide-in-right 0.5s ease-out;">
                            <div id="employeeDetailsContent"></div>
                        </div>

                        <!-- STEP5: Reports -->
                        <div data-view="STEP5" style="display: none; animation: slide-in-right 0.5s ease-out;">
                            <!-- Workflow Stepper -->
                            <div class="stepper-container">
                                @for ($i = 1; $i <= 5; $i++)
                                    <div class="stepper-step">
                                        @if ($i < 5)
                                            <div class="stepper-connector {{ $i < 4 ? 'active' : '' }}"></div>
                                        @endif
                                        <div class="stepper-circle {{ $i <= 4 ? 'active' : '' }}">{{ $i }}</div>
                                        <div class="stepper-label {{ $i <= 4 ? 'active' : '' }}">
                                            @if($i == 1) Approval
                                            @elseif($i == 2) Freeze
                                            @elseif($i == 3) Process
                                            @elseif($i == 4) Verify
                                            @else Finish
                                            @endif
                                        </div>
                                    </div>
                                @endfor
                            </div>

                            <!-- Process Info -->
                            <div style="margin-bottom: 24px; background: #dbeafe; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; display: flex; align-items: flex-start; gap: 12px;">
                                <div style="padding: 8px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; color: #2563eb;">
                                    <i data-lucide="info" style="width: 16px; height: 16px;"></i>
                                </div>
                                <div>
                                    <h4 style="color: #1e3a8a; font-weight: bold; font-size: 0.875rem; margin: 0;">Verification & Compliance</h4>
                                    <p style="color: #1d4ed8; font-size: 0.75rem; line-height: 1.5; margin-top: 4px; margin-bottom: 0;">Final verification for weekly payroll.</p>
                                </div>
                            </div>

                            <div style="margin-bottom: 32px; text-align: center;">
                                <h2 class="head-text">Weekly Payroll Verification</h2>
                                <p style="color: #64748b;">Week {{ $weekNumber }}: {{ \Carbon\Carbon::parse($weekStart)->format('d M') }} - {{ \Carbon\Carbon::parse($weekEnd)->format('d M, Y') }}</p>
                            </div>

                            <!-- Reports: week-scoped when payroll master cycle is Weekly (441), else same as monthly payrun -->
                            @include('admin.payroll.partials.payroll-reports-by-master-mode', [
                                'payrollPeriod' => $payrollPeriod,
                                'week' => $week,
                                'isWeeklyPayrollMode' => $isWeeklyPayrollMode ?? false,
                                'layout' => 'verification',
                            ])

                            <div style="margin-bottom: 20px; text-align: center;">
                                <a href="{{ ($isWeeklyPayrollMode ?? false) ? route('payroll.weekly.payslip.list', ['payrollId' => $payrollPeriod->pp_id, 'weekId' => $week->ppw_id]) : route('payroll.payslip.list', ['payrollId' => $payrollPeriod->pp_id]) }}"
                                    class="btn btn-primary"
                                    style="padding: 10px 24px; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; margin: 0 auto;">
                                    <i data-lucide="file-text" style="width: 16px; height: 16px;"></i>
                                    View All Payslips
                                </a>
                            </div>


                            <!-- Final Confirmation -->
                            <div style="background: #fef3c7; border: 1px solid #fde68a; padding: 16px; border-radius: 12px; display: flex; flex-direction: column; gap: 12px; max-width: 48rem; margin: 0 auto;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <i data-lucide="alert-triangle" style="color: #b45309; width: 20px; height: 20px;"></i>
                                    <div>
                                        <h4 style="color: #92400e; font-weight: bold; font-size: 0.875rem;">Final Confirmation</h4>
                                        <p style="color: #b45309; font-size: 0.75rem;">This action sends data to accounts.</p>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 12px;">
                                    <button data-action="back-to-processing" class="btn btn-link">Back to Processing</button>
                                    <button data-action="save-draft" class="btn btn-outline-secondary">Save as Draft</button>
                                    <button data-action="finalize-cycle" class="btn btn-success">Finalize & Lock</button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 6: Success -->
                        <div data-view="STEP6" style="display: none; animation: fade-in 0.5s ease-out; text-align: center; padding: 48px 16px;">
                            <div class="stepper-container">
                                @for ($i = 1; $i <= 5; $i++)
                                    <div class="stepper-step">
                                        @if ($i < 5)
                                            <div class="stepper-connector active"></div>
                                        @endif
                                        <div class="stepper-circle active">{{ $i }}</div>
                                        <div class="stepper-label active">
                                            @if($i == 1) Approval
                                            @elseif($i == 2) Freeze
                                            @elseif($i == 3) Process
                                            @elseif($i == 4) Verify
                                            @else Finish
                                            @endif
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            <div style="display: inline-flex; padding: 32px; background: #d1fae5; border-radius: 50%; border: 4px solid #a7f3d0; margin-bottom: 24px;">
                                <i data-lucide="check-circle" style="width: 64px; height: 64px; color: #059669;"></i>
                            </div>
                            <h2 class="head-text" style="color: #065f46;">Weekly Payroll Finalized Successfully!</h2>
                            <p style="color: #64748b; max-width: 28rem; margin: 0 auto 16px;">
                                Week {{ $weekNumber }} payroll has been verified and locked.
                            </p>
                            <p style="color: #10b981; font-size: 14px; margin-bottom: 32px;">
                                <i data-lucide="check-circle" style="width: 14px; height: 14px; display: inline-block;"></i>
                                All salaries have been processed and records are secure.
                            </p>    
                            <div style="display: flex; justify-content: center; gap: 16px;">
                                <button data-action="go-dashboard" class="btn btn-outline-secondary">
                                    <i data-lucide="arrow-left"></i> Back to Dashboard
                                </button>
                                <a href="{{ ($isWeeklyPayrollMode ?? false) ? route('payroll.weekly.payslip.list', ['payrollId' => $payrollPeriod->pp_id, 'weekId' => $week->ppw_id]) : route('payroll.payslip.list', ['payrollId' => $payrollPeriod->pp_id]) }}" class="btn btn-primary">
                                    <i data-lucide="file-text"></i> View Payslips
                                </a>
                            </div>
                        </div>

                        <!-- View All Payslips - Direct Redirect -->
                        <div data-view="VIEW_ALL_PAYSLIPS" class="hidden">
                            <!-- This will redirect immediately -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- All Modals (same as monthly version) -->
@include('admin.payroll.modals.adhoc-adjustment')
@include('admin.payroll.modals.create-cycle')
@include('admin.payroll.modals.hold-reason')
@include('admin.payroll.modals.deferred-action')
@include('admin.payroll.modals.deferred-list')
@include('admin.payroll.modals.release-action')
@include('admin.payroll.modals.payslip-preview')

<!-- Hold Details Modal -->
<div class="modal fade" id="holdDetailsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="height: 90vh;">
            <div class="modal-header">
                <h5 class="modal-title">Salary Hold Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="holdDetailsLoading" class="text-center py-5">
                    <div class="spinner-border text-danger"></div>
                    <p class="mt-3">Loading hold details...</p>
                </div>
                <div id="holdDetailsContent" class="d-none">
                    <div class="table-responsive">
                        <table class="table table-hover" id="holdsTable">
                            <thead>
                                <tr><th>#</th><th>Employee</th><th>Code</th><th>Department</th><th>Hold Reason</th><th>Actions</th></tr>
                            </thead>
                            <tbody id="holdsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Revert Processing Modal for Weekly Payroll -->
<div id="revert-processing-modal-weekly" class="modal-overlay hidden" style="display: none;">
    <div class="modal-container max-w-4xl">
        <div class="modal-header">
            <h3 class="modal-title">Revert Weekly Salary Processing</h3>
            <button class="modal-close-btn" data-action="close-revert-modal-weekly">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <div class="modal-alert warning">
                <i data-lucide="refresh-cw"></i>
                <div>
                    <strong>⚠️ Warning: Reverting Weekly Salary Processing</strong>
                    <p style="font-size: 0.75rem; margin-top: 0.25rem;">
                        You are about to revert ALL processed employees for Week {{ $weekNumber }}.
                        This action cannot be undone.
                    </p>
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span class="modal-input-label">Employees to be Reverted (<span id="revert-processed-count-weekly">0</span>)</span>
                </div>
            </div>

            <div id="revert-employee-list-weekly" class="modal-employee-list" style="max-height: 300px; overflow-y: auto;">
                <!-- Employee items will be inserted here -->
            </div>
        </div>

        <div class="modal-footer">
            <button class="modal-btn modal-btn-secondary" data-action="close-revert-modal-weekly">
                Cancel
            </button>
            <button class="modal-btn modal-btn-primary confirm-revert-btn-weekly" disabled>
                <i data-lucide="refresh-cw" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i>
                Revert All & Go Back
            </button>
        </div>
    </div>
</div>

<!-- Hold Salary Modal -->
<div id="hold-salary-modal" class="modal-overlay hidden">
    <div class="modal-container" style="max-width: 440px;">
        <div class="modal-header">
            <h3 class="modal-title">Hold Weekly Salary</h3>
            <button class="modal-close-btn" data-action="close-modal"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body">
            <div id="hold-employee-info" class="d-none">
                <div class="compact-employee-info">
                    <div id="hold-employee-avatar" class="compact-employee-avatar">E</div>
                    <div class="compact-employee-details">
                        <h4 id="hold-employee-name" class="compact-employee-name">Employee Name</h4>
                        <div id="hold-employee-details" class="compact-employee-meta">Department • Designation</div>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Payroll Periods <span class="text-danger">*</span></label>
                <div id="payroll-periods-dropdown-container"></div>
                <small class="text-muted">Select weeks to hold</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Reason for Hold <span class="text-danger">*</span></label>
                <textarea id="hold-reason" class="form-control" rows="3" placeholder="Enter reason..."></textarea>
            </div>
            <div class="form-check">
                <input type="checkbox" id="hold-until-release" class="form-check-input">
                <label class="form-check-label">Hold until manually released</label>
                <div id="hold-until-release-info" class="text-muted small mt-1" style="display: none;">
                    Only one period can be selected with this option
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button data-action="close-modal" class="btn btn-secondary">Cancel</button>
            <button id="confirm-hold-btn" class="btn btn-warning" disabled>Hold Salary</button>
        </div>
    </div>
</div>

<!-- Release Salary Modal -->
<div id="release-salary-modal" class="modal-overlay hidden">
    <div class="modal-container max-w-3xl">
        <div class="modal-header">
            <h3 class="modal-title">Release Salary Hold</h3>
            <button class="modal-close-btn" data-action="close-modal"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body">
            <div class="modal-alert info">
                <i data-lucide="info"></i>
                <div>This will allow salary processing for the selected employee.</div>
            </div>
            <div id="release-employee-info" class="d-none">
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 8px; margin-bottom: 16px;">
                    <div style="width: 40px; height: 40px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center;"><span id="release-employee-avatar">E</span></div>
                    <div><h4 id="release-employee-name" style="margin: 0;">Employee Name</h4><p id="release-employee-details" class="text-muted small">Department • Designation</p></div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Release Notes (Optional)</label>
                <textarea id="release-notes" class="form-control" rows="3" placeholder="Enter notes..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" data-action="close-modal">Cancel</button>
            <button class="btn btn-success" id="confirm-release-btn">Confirm Release</button>
        </div>
    </div>
</div>

@endsection



@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>
    // ============================================
    // GLOBAL VARIABLES
    // ============================================

    let retrievedAttendanceData = {};
    let lateCleared = false;
    let earlyCleared = false;
    let currentPayrollId = '{{ $payrollPeriod->pp_id ?? '' }}';
    let currentWeekId = '{{ $week->ppw_id ?? '' }}';
    let currentWeekNumber = {{ $weekNumber ?? 0 }};
    let currentMonthName = '{{ $monthName }}';
    let currentYear = {{ $year }};
    let currentStep = '{{ $currentStep }}';
    let currentWeekStatus = '{{ strtolower($status ?? "open") }}';
    let isLoadingAttendance = false;
    let isLoadingSalary = false;
    let selectedRevertIdsWeekly = new Set();

    function normalizeWeeklyStatusForStep(status) {
        return (status || '')
            .toString()
            .toLowerCase()
            .trim()
            .replace(/[-\s]+/g, '_')
            .replace(/_+/g, '_');
    }

    function mapWeeklyStatusToStep(status) {
        const normalized = normalizeWeeklyStatusForStep(status);
        switch (normalized) {
            case 'open':
            case 'pending':
            case 'configured':
            case 'pendingapproval':
            case 'pending_approval':
                return 'STEP1';
            case 'underreview':
            case 'under_review':
                return 'STEP2';
            case 'processing':
            case 'in_process':
            case 'in-process':
            case 'frozen':
            case 'attendance_frozen':
                return 'STEP3';
            case 'verification':
            case 'processed':
                return 'STEP5';
            case 'finalized':
            case 'completed':
                return 'STEP6';
            default:
                return null;
        }
    }

    function setCurrentWeeklyStatus(status) {
        currentWeekStatus = normalizeWeeklyStatusForStep(status);
        const mappedStep = mapWeeklyStatusToStep(currentWeekStatus);
        if (mappedStep) {
            currentStep = mappedStep;
            $('#server-current-step').val(mappedStep);
        }
    }

    function openCurrentWeeklyStage(forceReload = false) {
        const targetStep = mapWeeklyStatusToStep(currentWeekStatus) || $('#server-current-step').val() || currentStep || 'STEP1';

        if (targetStep === 'STEP2') {
            loadWeeklyAttendanceData(currentPayrollId, false);
            return;
        }

        if (targetStep === 'STEP3') {
            loadStep3Data(false);
            return;
        }

        if (targetStep === 'STEP5') {
            showView('STEP5', false, forceReload);
            return;
        }

        if (targetStep === 'STEP6') {
            showView('STEP6', false, forceReload);
            return;
        }

        showView('STEP1', false, forceReload);
    }

    // ============================================
    // STORAGE MANAGEMENT
    // ============================================
    const MAX_AGE_HOURS = 8;

    function getStepStorageKey() {
        return `weekly_payroll_step_${currentPayrollId}_${currentWeekId}`;
    }

    function savePayrollStepWithTimestamp(viewName) {
        localStorage.setItem(getStepStorageKey(), viewName);
        localStorage.setItem(`weekly_payroll_timestamp_${currentPayrollId}_${currentWeekId}`, Date.now());
    }

    function clearPayrollStorage() {
        localStorage.removeItem(getStepStorageKey());
        localStorage.removeItem(`weekly_payroll_timestamp_${currentPayrollId}_${currentWeekId}`);
    }

    function isStepExpired() {
        const timestamp = localStorage.getItem(`weekly_payroll_timestamp_${currentPayrollId}_${currentWeekId}`);
        if (!timestamp) return true;
        return (Date.now() - parseInt(timestamp)) / (1000 * 60 * 60) > MAX_AGE_HOURS;
    }

    // ============================================
    // VIEW MANAGEMENT
    // ============================================
    function showView(viewName, restoreMode = false, forceReload = false) {
        $('[data-view]').hide();
        $(`[data-view="${viewName}"]`).show();

        if (!restoreMode && !forceReload) {
            savePayrollStepWithTimestamp(viewName);
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
        updateStepper(viewName);

        if (!restoreMode && !forceReload) {
            $('html, body').animate({ scrollTop: 0 }, 300);
        }

        if (!restoreMode) {
            if (viewName === 'STEP1' && currentPayrollId) {
                loadPendingRequestsData(currentPayrollId);
            }
            else if (viewName === 'STEP2' && currentPayrollId) {
                const hasData = $('#attendanceTableBody tr').length > 0 &&
                            !$('#attendanceTableBody').text().includes('Click "Proceed"') &&
                            !$('#attendanceTableBody').text().includes('Loading');
                if (forceReload || !hasData) {
                    loadWeeklyAttendanceData(currentPayrollId, false);
                }
            }
            else if (viewName === 'STEP3' && currentPayrollId) {
                const hasData = $('#step3TableBody tr').length > 0 &&
                            !$('#step3TableBody').text().includes('Click');
                if (forceReload || !hasData) {
                    loadStep3Data(false);
                }
            }
            else if (viewName === 'STEP5') {
                initializeAutoSaveDisbursementDates();
                loadStep5Data();
                ensureWeeklyVerificationStageReady();
            }
        } else {
            if (viewName === 'STEP2' && currentPayrollId) {
                const hasData = $('#attendanceTableBody tr').length > 0 &&
                            !$('#attendanceTableBody').text().includes('Click "Proceed"') &&
                            !$('#attendanceTableBody').text().includes('Loading');
                if (!hasData) {
                    loadWeeklyAttendanceData(currentPayrollId, true);
                }
            } else if (viewName === 'STEP3' && currentPayrollId) {
                const hasData = $('#step3TableBody tr').length > 0 &&
                            !$('#step3TableBody').text().includes('Click') &&
                            !$('#step3TableBody').text().includes('Loading');
                if (!hasData) {
                    loadStep3Data(true);
                }
            } else if (viewName === 'STEP5') {
                initializeAutoSaveDisbursementDates();
                loadStep5Data();
                ensureWeeklyVerificationStageReady();
            }
        }
    }

    function updateStepper(viewName) {
        let activeStep = 0;
        switch (viewName) {
            case 'DASHBOARD': activeStep = 0; break;
            case 'STEP1': activeStep = 1; break;
            case 'STEP2': activeStep = 2; break;
            case 'STEP3': activeStep = 3; break;
            case 'STEP4': activeStep = 3; break;
            case 'STEP5': activeStep = 4; break;
            case 'STEP6': activeStep = 5; break;
        }
        $('.stepper-circle, .stepper-label').removeClass('active');
        $('.stepper-connector').removeClass('active');
        for (let i = 1; i <= activeStep; i++) {
            $(`.stepper-circle:contains("${i}")`).addClass('active');
            $(`.stepper-step:nth-child(${i}) .stepper-label`).addClass('active');
            if (i < activeStep) $(`.stepper-step:nth-child(${i}) .stepper-connector`).addClass('active');
        }
    }

    // ============================================
    // WEEKLY ATTENDANCE FUNCTIONS
    // ============================================
    function loadWeeklyAttendanceData(payrollId, restoreMode = false) { 
        if (!payrollId || !currentWeekId) {
            Swal.fire({ title: 'Error', text: 'Missing payroll or week ID', icon: 'error' });
            return;
        }

        if (window.isLoadingAttendance) {
            console.log('Already loading attendance, skipping...');
            return;
        }
        window.isLoadingAttendance = true;

        $.ajax({
            url: '/payroll/weekly-attendance',
            type: 'GET',
            data: { payroll_id: payrollId, week_id: currentWeekId },
            beforeSend: () => { 
                if (!restoreMode) {
                    $('#attendanceTableBody').html('<tr><td colspan="16" style="padding:40px;text-align:center;"><div class="spinner-border"></div><p>Loading weekly attendance...</p></td></tr>');
                }
            },
            success: function(response) {
                window.isLoadingAttendance = false;
                if (response.success && response.html) {
                    if (response.week_status) {
                        setCurrentWeeklyStatus(response.week_status);
                    }
                    $('#attendanceTableBody').html(response.html);
                    setTimeout(() => { 
                        initializeAttendanceValidation(); 
                        validateAttendanceTotal(); 
                    }, 300);
                    showView('STEP2', true);
                } else {
                    Swal.fire({ title: 'Error', text: response.message || 'Failed to load', icon: 'error' });
                    showView('DASHBOARD', false);
                }
            },
            error: function(xhr) {
                window.isLoadingAttendance = false;
                Swal.fire({ title: 'Error', text: 'Failed to load attendance data', icon: 'error' });
                showView('DASHBOARD', false);
            }
        });
    }

    function initializeAttendanceValidation() {
        validateAttendanceTotal();
        $(document).on('input', '#attendanceTable input[type="number"]', function() {
            setTimeout(() => { validateAttendanceTotal(); }, 100);
        });
    }

    function validateAttendanceTotal() {
        let isValid = true;
        let errorRows = [];
        const $freezeBtn = $('[data-action="freeze-attendance"]');

        $('#attendanceTable tbody tr').each(function(index) {
            const $row = $(this);
            const $cells = $row.find('td');
            if ($cells.length < 16 || $row.text().includes('Click "Proceed"')) return;
            if ($row.css('display') === 'none') return;

            const weekDays = 7;
            const total = parseFloat($cells.eq(15).text().trim()) || 0;

            if (total > weekDays) {
                isValid = false;
                errorRows.push(index + 1);
                $row.css('background-color', '#fff5f5');
                $cells.eq(15).css({ 'color': '#dc2626', 'font-weight': 'bold', 'border': '2px solid #dc2626' });
            } else {
                $row.css('background-color', '');
                $cells.eq(15).css({ 'color': '', 'font-weight': '', 'border': '' });
            }
        });

        if (!isValid) {
            $freezeBtn.prop('disabled', true).css({ 'background': '#9ca3af', 'cursor': 'not-allowed' }).html('<i data-lucide="alert-circle"></i> Fix Errors to Freeze');
            if ($('#attendanceValidationError').length === 0) {
                $('.attendance-table-container').before('<div id="attendanceValidationError" class="alert alert-danger">Total days exceed 7 in row(s): ' + errorRows.join(', ') + '</div>');
            }
        } else {
            $freezeBtn.prop('disabled', false).css({ 'background': '#dc2626', 'cursor': 'pointer' }).html('<i data-lucide="lock"></i> Freeze Weekly Attendance');
            $('#attendanceValidationError').remove();
        }
        return isValid;
    }

    async function freezeWeeklyAttendance() {
        if (!validateAttendanceTotal()) {
            Swal.fire({ title: 'Validation Failed', text: 'Please fix errors before freezing', icon: 'error' });
            return;
        }

        const attendanceData = [];
        $('#attendanceTable tbody tr').each(function() {
            const $row = $(this);
            const $cells = $row.find('td');
            if ($cells.length < 16 || $row.text().includes('Click "Proceed"')) return;
            
            const empCode = $cells.eq(0).text().trim();
            if (empCode) {
                attendanceData.push({
                    emp_id: $row.data('emp-id') || empCode,
                    emp_code: empCode,
                    emp_name: $cells.eq(1).text().trim(),
                    total_days: 7,
                    presentCount: parseFloat($cells.eq(3).text().trim()) || 0,
                    absentCount: parseFloat($cells.eq(4).text().trim()) || 0,
                    weekOffCount: parseFloat($cells.eq(5).text().trim()) || 0,
                    weekOffPresentCount: parseFloat($cells.eq(6).text().trim()) || 0,
                    halfDayCount: parseFloat($cells.eq(7).text().trim()) || 0,
                    leaveCount: parseFloat($cells.eq(8).text().trim()) || 0,
                    holidayCount: parseFloat($cells.eq(9).text().trim()) || 0,
                    UPL: parseFloat($cells.eq(10).text().trim()) || 0,
                    lateCount: parseFloat($row.find('.late-input').val()) || 0,
                    earlyExitCount: parseFloat($row.find('.early-input').val()) || 0,
                    missedPunchCount: parseFloat($cells.eq(13).text().trim()) || 0,
                    overtimeCount: parseFloat($cells.eq(14).text().trim()) || 0,
                    total: parseFloat($cells.eq(15).text().trim()) || 0,
                });
            }
        });

        if (attendanceData.length === 0) {
            Swal.fire({ title: 'No Data', text: 'No attendance data found', icon: 'warning' });
            return;
        }

        const confirmResult = await Swal.fire({
            title: 'Freeze Weekly Attendance?',
            text: `Freeze attendance for Week ${currentWeekNumber} (${attendanceData.length} employees)`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: `Freeze Week ${currentWeekNumber}`,
            confirmButtonColor: '#dc2626'
        });

        if (!confirmResult.isConfirmed) return;

        Swal.fire({
            title: 'Freezing Attendance',
            html: '<div class="spinner-border"></div><p>Processing...</p>',
            showConfirmButton: false,
            allowOutsideClick: false
        });

        const CHUNK_SIZE = 50;
        const totalChunks = Math.ceil(attendanceData.length / CHUNK_SIZE);
        let successCount = 0;

        for (let i = 0; i < totalChunks; i++) {
            const chunk = attendanceData.slice(i * CHUNK_SIZE, (i + 1) * CHUNK_SIZE);
            const chunkNumber = i + 1;
            
            try {
                const response = await $.ajax({
                    url: '/payroll/weekly-freeze-attendance-chunk',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        payroll_id: parseInt(currentPayrollId),
                        week_id: parseInt(currentWeekId),
                        attendance_chunk: chunk,
                        chunk_number: chunkNumber,
                        total_chunks: totalChunks,
                        _token: '{{ csrf_token() }}'
                    })
                });
                
                if (response.success) {
                    successCount += response.processed_count;
                    if (response.is_last_chunk) {
                        setCurrentWeeklyStatus(response.week_status || 'processing');
                        Swal.close();
                        showSuccessOverlay('Attendance Reviewed!', `${successCount} employees moved to in-process successfully.`, 'check');
                        
                        setTimeout(() => {
                            loadStep3Data();
                            showView('STEP3');
                        }, 2000);
                    }
                } else {
                    throw new Error(response.message);
                }
            } catch (error) {
                Swal.close();
                Swal.fire({ title: 'Freeze Failed', text: error.message, icon: 'error' });
                return;
            }
        }
    }

    // ============================================
    // STEP 3 FUNCTIONS - Salary Processing
    // ============================================
    function loadStep3Data(restoreMode = false) {
        if (!currentPayrollId) {
            Swal.fire({ title: 'Error', text: 'Payroll ID not found', icon: 'error' });
            showView('DASHBOARD', false);
            return;
        }

        if (window.isLoadingSalary) return;
        window.isLoadingSalary = true;

        if (!restoreMode) {
            $('#step3TableBody').html('<tr><td colspan="9" style="padding:40px;text-align:center;"><div class="spinner-border"></div><p>Loading weekly salary data...</p></td></tr>');
        }

        $.ajax({
            url: '/payroll/weekly-salary-data/' + currentPayrollId + '/' + currentWeekId,
            type: 'GET',
            success: function(response) {
                window.isLoadingSalary = false;
                if (response.success && response.data && response.data.employees) {
                    const employeeStatusFilters = response.data.summary?.employee_status_filters || [];
                    populateStep3Table(response.data.employees, employeeStatusFilters);
                    
                    const weekStatus = response.data.summary?.week_status;
                    if (weekStatus) {
                        setCurrentWeeklyStatus(weekStatus);
                    }
                    const shouldShowStep5 = response.data.summary?.should_show_step5;
                    const processedCount = response.data.summary?.processed_count || 0;
                    const totalEmployees = response.data.employees.length;
                    
                    const statusStep = mapWeeklyStatusToStep(weekStatus);

                    if (statusStep === 'STEP5' || shouldShowStep5 === true || 
                        (processedCount > 0 && processedCount === totalEmployees)) {
                        showView('STEP5', true);
                    } else {
                        showView('STEP3', true);
                    }
                    
                    setTimeout(() => { 
                        updateProcessButtonText(); 
                        initializeSalarySearch(); 
                    }, 100);
                } else {
                    $('#step3TableBody').html(`<td><td colspan="9" style="padding:40px;text-align:center;color:#dc2626;">${response.message || 'No data found'}<\/td><\/tr>`);
                }
            },
            error: function() {
                window.isLoadingSalary = false;
                $('#step3TableBody').html('<td><td colspan="9" style="padding:40px;text-align:center;color:#dc2626;">Failed to load data<\/td><\/tr>');
            }
        });
    }
    
    function populateStep3Table(employees, statusOptions = []) {
        const $tableBody = $('#step3TableBody');
        $tableBody.empty();

        if (!employees || employees.length === 0) {
            $tableBody.html('<tr><td colspan="9" style="padding:40px;text-align:center;">No employees found<\/td><\/tr>');
            return;
        }

        let processedCount = 0;
        let totalCount = employees.length;

        employees.forEach((emp) => {
            const isProcessed = emp.is_processed || false;
            const isHeld = emp.is_held || false;
            const procStatus = isProcessed ? 'PROCESSED' : (isHeld ? 'HELD' : 'PENDING');
            const isChecked = !isProcessed && !isHeld;
            
            if (isProcessed) processedCount++;

            const row = `
                <tr data-emp-id="${emp.emp_id || ''}" data-proc-status="${procStatus}" data-employee-status="${emp.emp_status || ''}">
                    <td style="text-align: center;">
                        <input type="checkbox" class="employee-checkbox" data-emp-id="${emp.emp_id || ''}" ${isChecked ? 'checked' : ''} ${isProcessed || isHeld ? 'disabled' : ''}>
                    </td>
                    <td>
                        <div class="employee-cell">
                            <div class="employee-avatar">${(emp.emp_name || '').charAt(0).toUpperCase()}</div>
                            <div class="employee-info">
                                <div class="employee-name">${emp.emp_name || 'N/A'}</div>
                                <div class="employee-code">${emp.emp_code || ''}</div>
                                <div class="employee-department">${emp.department || ''}</div>
                                <div class="employee-status" style="font-size:0.7rem;color:#64748b;">${emp.emp_status_name || ('Status ' + (emp.emp_status || 'N/A'))}</div>
                            </div>
                        </div>
                    </td>
                    <td>${emp.designation || 'N/A'}</td>
                    <td style="text-align: center;">${emp.week_days || 7}</td>
                    <td style="text-align: center; color: #059669;">${emp.salary_days || 0}</td>
                    <td style="text-align: right;">-</td>
                    <td style="text-align: center;">
                        <span class="status-badge ${isHeld ? 'status-held' : (isProcessed ? 'status-processed' : 'status-pending')}">
                            <i data-lucide="${isHeld ? 'alert-triangle' : (isProcessed ? 'check-circle' : 'clock')}" style="width: 12px; height: 12px;"></i>
                            ${procStatus}
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <div class="action-buttons">
                            ${!isHeld && !isProcessed ? `<button data-action="hold-salary" data-employee-id="${emp.emp_id || ''}" data-employee-name="${emp.emp_name || ''}" class="action-btn hold-btn"><i data-lucide="pause"></i> Hold</button>` : ''}
                            <button data-action="view-employee-details" data-employee-id="${emp.emp_id || ''}" class="action-btn view-btn"><i data-lucide="eye"></i> View</button>
                            ${isHeld ? `<button data-action="release-salary" data-employee-id="${emp.emp_id || ''}" class="action-btn release-btn"><i data-lucide="play"></i> Release</button>` : ''}
                        </div>
                    </td>
                </tr>
            `;
            $tableBody.append(row);
        });

        populateEmployeeStatusFilter(employees, statusOptions);

        if (typeof lucide !== 'undefined') lucide.createIcons();
        updateProcessButtonText();
        
        if (processedCount === totalCount && totalCount > 0) {
            setTimeout(() => {
                showView('STEP5');
            }, 500);
        }
    }

    function populateEmployeeStatusFilter(employees, statusOptions = []) {
        const $employeeStatusFilter = $('#employeeStatusFilter');
        if (!$employeeStatusFilter.length) return;

        const selectedValue = $employeeStatusFilter.val() || 'all';
        const statusesMap = new Map();

        (statusOptions || []).forEach((status) => {
            const statusId = String(status?.id ?? '').trim();
            const statusName = String(status?.name ?? '').trim();
            if (!statusId || !statusName) return;
            statusesMap.set(statusId, statusName);
        });

        (employees || []).forEach((emp) => {
            const statusId = String(emp.emp_status ?? '').trim();
            if (!statusId) return;
            const statusName = (emp.emp_status_name || `Status ${statusId}`).trim();
            if (!statusesMap.has(statusId)) {
                statusesMap.set(statusId, statusName);
            }
        });

        const sortedStatuses = Array.from(statusesMap.entries())
            .sort((a, b) => a[1].localeCompare(b[1]));

        let optionsHtml = '<option value="all">All Status</option>';
        sortedStatuses.forEach(([statusId, statusName]) => {
            optionsHtml += `<option value="${statusId}">${statusName}</option>`;
        });

        $employeeStatusFilter.html(optionsHtml);
        if (statusesMap.has(selectedValue)) {
            $employeeStatusFilter.val(selectedValue);
        } else {
            $employeeStatusFilter.val('all');
        }
    }

    function updateProcessButtonText() {
        const total = $('.employee-checkbox:not(:disabled)').length;
        const selected = $('.employee-checkbox:checked:not(:disabled)').length;
        const $button = $('[data-action="process-bulk"]');
        if ($button.length) {
            if (selected === total && total > 0) $button.find('.process-text').text(`Process All (${total}) & Next`);
            else $button.find('.process-text').text(`Process ${selected} of ${total} & Next`);
        }
    }

    function initializeSalarySearch() {
        const searchInput = document.getElementById('salarySearch');
        if (!searchInput) return;

        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            const employeeStatusFilter = document.getElementById('employeeStatusFilter');
            const employeeStatusValue = employeeStatusFilter ? employeeStatusFilter.value : 'all';
            filterSalaryTable(searchTerm, employeeStatusValue);
        });

        const employeeStatusFilter = document.getElementById('employeeStatusFilter');
        if (employeeStatusFilter) {
            employeeStatusFilter.addEventListener('change', function() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                filterSalaryTable(searchTerm, this.value);
            });
        }
    }

    function filterSalaryTable(searchTerm = '', employeeStatusFilter = 'all') {
        const rows = document.querySelectorAll('#step3TableBody tr');
        rows.forEach(row => {
            if (row.cells.length === 1 && row.cells[0].colSpan > 1) {
                row.style.display = 'table-row';
                return;
            }
            
            let employeeName = '', employeeCode = '', department = '';
            const employeeCell = row.querySelector('.employee-info');
            if (employeeCell) {
                employeeName = employeeCell.querySelector('.employee-name')?.textContent?.toLowerCase() || '';
                employeeCode = employeeCell.querySelector('.employee-code')?.textContent?.toLowerCase() || '';
                department = employeeCell.querySelector('.employee-department')?.textContent?.toLowerCase() || '';
            }
            
            const statusElement = row.querySelector('.status-badge');
            status = statusElement ? statusElement.textContent.trim() : '';
            
            const matchesSearch = searchTerm === '' || employeeName.includes(searchTerm) || employeeCode.includes(searchTerm) || department.includes(searchTerm);
            const rowEmpStatus = (row.getAttribute('data-employee-status') || '').trim();
            const matchesEmployeeStatus = employeeStatusFilter === 'all' || rowEmpStatus === employeeStatusFilter;
            
            if (matchesSearch && matchesEmployeeStatus) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function formatWeeklyCurrency(amount) {
        const parsed = Number(amount || 0);
        return `₹${parsed.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}`;
    }

    function escapePreviewText(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function openWeeklyEmployeeSalaryPreview(employeeId) {
        if (!employeeId || !currentPayrollId || !currentWeekId) {
            Swal.fire('Error', 'Missing payroll context for salary preview.', 'error');
            return;
        }

        const $step4View = $('[data-view="STEP4"]');
        $step4View.find('#employeeDetailsContent').html(`
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 20px;">
                <div class="spinner-border text-primary" style="width:40px;height:40px;"></div>
                <p style="color:#64748b;margin-top:16px;">Loading employee weekly preview...</p>
            </div>
        `);
        showView('STEP4');

        $.ajax({
            url: '/payroll/weekly-employee-salary-preview',
            type: 'GET',
            data: {
                employee_id: employeeId,
                payroll_id: currentPayrollId,
                week_id: currentWeekId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (!response.success || !response.data) {
                    Swal.fire('Error', response.message || 'Failed to load salary preview.', 'error');
                    showView('STEP3');
                    return;
                }

                populateWeeklyEmployeeDetailView(response.data);
            },
            error: function(xhr) {
                const errorMessage = xhr?.responseJSON?.message || 'Failed to load salary preview.';
                Swal.fire('Error', errorMessage, 'error');
                showView('STEP3');
            }
        });
    }

    function populateWeeklyEmployeeDetailView(preview) {
        const $step4View = $('[data-view="STEP4"]');
        const emp = preview.employee || {};
        const week = preview.week || {};
        const attendance = preview.attendance || {};
        const earnings = preview.earnings || {};
        const deductions = preview.deductions || {};
        const summary = preview.summary || {};

        const earningsRows = [
            { type: 'Prorated Basic', amount: Number(earnings.prorated_basic || 0) },
            { type: 'HRA', amount: Number(earnings.hra || 0) },
            { type: 'Conveyance', amount: Number(earnings.conveyance || 0) },
            { type: 'Medical', amount: Number(earnings.medical || 0) },
            { type: 'Special', amount: Number(earnings.special || 0) },
        ];

        const deductionsRows = [
            { type: 'PF', amount: Number(deductions.pf || 0) },
            { type: 'ESI', amount: Number(deductions.esi || 0) },
            { type: 'PT', amount: Number(deductions.pt || 0) },
        ];

        let statusBadge = '';
        if (summary.is_held) {
            statusBadge = `
                <div style="display:flex;align-items:center;gap:6px;background:#fef3c7;color:#92400e;padding:4px 10px;border-radius:6px;border:1px solid #fde68a;font-size:0.75rem;font-weight:600;">
                    <i data-lucide="alert-triangle" style="width:14px;height:14px;"></i>
                    Held
                </div>
            `;
        } else if (summary.is_processed) {
            statusBadge = `
                <div style="display:flex;align-items:center;gap:6px;background:#d1fae5;color:#065f46;padding:4px 10px;border-radius:6px;border:1px solid #a7f3d0;font-size:0.75rem;font-weight:600;">
                    <i data-lucide="check-circle" style="width:14px;height:14px;"></i>
                    Processed
                </div>
            `;
        } else {
            statusBadge = `
                <div style="display:flex;align-items:center;gap:6px;background:#f1f5f9;color:#475569;padding:4px 10px;border-radius:6px;border:1px solid #e2e8f0;font-size:0.75rem;font-weight:600;">
                    <i data-lucide="clock" style="width:14px;height:14px;"></i>
                    Pending
                </div>
            `;
        }

        const html = `
            <div style="padding:24px;min-height:100vh;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
                    <button data-action="back-to-list" style="display:flex;align-items:center;gap:8px;background:white;border:1px solid #e2e8f0;border-radius:8px;padding:8px 16px;color:#475569;font-weight:600;cursor:pointer;">
                        <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
                        Back to List
                    </button>
                    <div>${statusBadge}</div>
                </div>

                <div style="max-width:1200px;margin:0 auto;">
                    <div class="premium-card card-padding-lg" style="margin-bottom:20px;">
                        <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">
                            <div style="width:64px;height:64px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;color:white;">
                                ${escapePreviewText(emp.name || 'E').charAt(0).toUpperCase()}
                            </div>
                            <div style="flex:1;">
                                <h1 style="font-size:1.5rem;font-weight:700;color:#1e293b;margin:0 0 4px 0;">${escapePreviewText(emp.name || 'N/A')}</h1>
                                <div style="display:flex;gap:16px;flex-wrap:wrap;">
                                    <span style="background:#f1f5f9;color:#64748b;padding:4px 10px;border-radius:6px;font-size:0.75rem;font-weight:600;">${escapePreviewText(emp.code || 'N/A')}</span>
                                    <span style="color:#64748b;font-size:0.875rem;display:flex;align-items:center;gap:4px;">
                                        <i data-lucide="briefcase" style="width:14px;height:14px;"></i>
                                        ${escapePreviewText(emp.designation || 'N/A')}
                                    </span>
                                    <span style="color:#64748b;font-size:0.875rem;display:flex;align-items:center;gap:4px;">
                                        <i data-lucide="building-2" style="width:14px;height:14px;"></i>
                                        ${escapePreviewText(emp.department || 'N/A')}
                                    </span>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:0.875rem;color:#64748b;margin-bottom:4px;">Net Payable</div>
                                <div style="font-size:1.75rem;font-weight:700;color:#059669;">${formatWeeklyCurrency(summary.net_payable)}</div>
                                <div style="font-size:0.75rem;color:#94a3b8;margin-top:4px;">Weekly Preview</div>
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
                            <div><div style="font-size:0.75rem;color:#64748b;margin-bottom:4px;">Week</div><div style="font-size:0.875rem;font-weight:600;color:#1e293b;">${escapePreviewText(week.name || '-')}</div></div>
                            <div><div style="font-size:0.75rem;color:#64748b;margin-bottom:4px;">Period</div><div style="font-size:0.875rem;font-weight:600;color:#1e293b;">${escapePreviewText(week.start_date || '-')} to ${escapePreviewText(week.end_date || '-')}</div></div>
                            <div><div style="font-size:0.75rem;color:#64748b;margin-bottom:4px;">Worked Days</div><div style="font-size:0.875rem;font-weight:600;color:#059669;">${Number(attendance.worked_days || 0)} (${Number(attendance.worked_percentage || 0)}%)</div></div>
                            <div><div style="font-size:0.75rem;color:#64748b;margin-bottom:4px;">Weekly Basic</div><div style="font-size:0.875rem;font-weight:600;color:#2563eb;">${formatWeeklyCurrency(earnings.weekly_basic)}</div></div>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(350px,1fr));gap:16px;margin-bottom:16px;">
                        <div class="premium-card card-padding-lg" style="margin-bottom:20px;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;color:#8b5cf6;">
                                <i data-lucide="calendar" style="width:18px;height:18px;"></i>
                                <h3 style="font-size:0.875rem;font-weight:700;margin:0;">Attendance Breakdown</h3>
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;">
                                <div style="text-align:center;"><div style="width:40px;height:40px;background:#d1fae5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;"><span style="font-weight:700;color:#059669;">${Number(attendance.present_days || 0)}</span></div><div style="font-size:0.75rem;color:#64748b;">Present</div></div>
                                <div style="text-align:center;"><div style="width:40px;height:40px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;"><span style="font-weight:700;color:#dc2626;">${Number(attendance.absent_days || 0)}</span></div><div style="font-size:0.75rem;color:#64748b;">Absent</div></div>
                                <div style="text-align:center;"><div style="width:40px;height:40px;background:#fef3c7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;"><span style="font-weight:700;color:#d97706;">${Number(attendance.half_days || 0)}</span></div><div style="font-size:0.75rem;color:#64748b;">Half Days</div></div>
                                <div style="text-align:center;"><div style="width:40px;height:40px;background:#dbeafe;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;"><span style="font-weight:700;color:#2563eb;">${Number(attendance.leave_days || 0)}</span></div><div style="font-size:0.75rem;color:#64748b;">Leave</div></div>
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                                <div><div style="font-size:0.75rem;color:#64748b;margin-bottom:4px;">Week Days</div><div style="font-size:0.875rem;font-weight:600;">${Number(week.total_days || 0)}</div></div>
                                <div><div style="font-size:0.75rem;color:#64748b;margin-bottom:4px;">Worked %</div><div style="font-size:0.875rem;font-weight:600;">${Number(attendance.worked_percentage || 0)}%</div></div>
                            </div>
                        </div>

                        <div class="premium-card card-padding-lg" style="margin-bottom:20px;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;color:#059669;">
                                <i data-lucide="dollar-sign" style="width:18px;height:18px;"></i>
                                <h3 style="font-size:0.875rem;font-weight:700;margin:0;">Salary Breakdown</h3>
                            </div>
                            <div style="margin-bottom:16px;">
                                <div style="margin-bottom:16px;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                        <div style="font-size:0.75rem;font-weight:600;color:#475569;">EARNINGS</div>
                                        <div style="font-size:0.75rem;font-weight:700;color:#059669;">${formatWeeklyCurrency(earnings.total)}</div>
                                    </div>
                                    <div style="background:#f0f9ff;border-radius:8px;padding:12px;border:1px solid #e0f2fe;">
                                        ${earningsRows.map(item => `
                                            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #e0f2fe;font-size:0.75rem;">
                                                <div style="color:#475569;">${item.type}</div>
                                                <div style="font-weight:600;color:#059669;">${formatWeeklyCurrency(item.amount)}</div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                                <div>
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                        <div style="font-size:0.75rem;font-weight:600;color:#475569;">DEDUCTIONS</div>
                                        <div style="font-size:0.75rem;font-weight:700;color:#dc2626;">${formatWeeklyCurrency(deductions.total)}</div>
                                    </div>
                                    <div style="background:#fef2f2;border-radius:8px;padding:12px;border:1px solid #fee2e2;">
                                        ${deductionsRows.map(item => `
                                            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #fee2e2;font-size:0.75rem;">
                                                <div style="color:#475569;">${item.type}</div>
                                                <div style="font-weight:600;color:#dc2626;">${formatWeeklyCurrency(item.amount)}</div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            </div>
                            <div style="background:linear-gradient(135deg,#1e293b 0%,#334155 100%);border-radius:8px;padding:16px;color:white;">
                                <div style="text-align:center;">
                                    <div style="font-size:0.75rem;color:#cbd5e1;margin-bottom:4px;">FINAL PAYABLE</div>
                                    <div style="font-size:1.5rem;font-weight:700;color:white;margin-bottom:8px;">${formatWeeklyCurrency(summary.net_payable)}</div>
                                    <div style="font-size:0.72rem;color:#cbd5e1;">Source: ${escapePreviewText(summary.preview_source || 'live_calculation')}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $step4View.find('#employeeDetailsContent').html(html);
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    async function processWeeklySalaries(employeeIds) {
        const CHUNK_SIZE = 15;
        let processedCount = 0;
        let errors = [];

        Swal.fire({
            title: 'Processing Weekly Salaries',
            html: '<div class="spinner-border"></div><p>Processing...</p>',
            showConfirmButton: false,
            allowOutsideClick: false
        });

        for (let i = 0; i < employeeIds.length; i += CHUNK_SIZE) {
            const chunk = employeeIds.slice(i, i + CHUNK_SIZE);
            try {
                const response = await $.ajax({
                    url: '/payroll/weekly-process-salaries',
                    type: 'POST',
                    data: {
                        payroll_id: currentPayrollId,
                        week_id: currentWeekId,
                        selected_employees: chunk,
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json'
                });

                if (response.success) {
                    processedCount += response.processed_count || chunk.length;
                    
                    if (response.data && response.data.week_status) {
                        setCurrentWeeklyStatus(response.data.week_status);
                    }
                } else {
                    errors.push(response.message || 'Processing failed for a batch');
                }
            } catch (error) {
                console.error('Chunk failed:', error);
                errors.push(error.responseJSON?.message || error.statusText || 'Unknown error');
            }
        }

        Swal.close();

        if (processedCount > 0) {
            showSuccessOverlay('Weekly Salaries Processed!', `${processedCount} employees processed. Ready for verification.`, 'check');
            
            const newStep = 'STEP5';
            savePayrollStepWithTimestamp(newStep);
            
            setTimeout(() => {
                loadStep3Data();
                showView('STEP5');
            }, 2000);
        } else {
            Swal.fire({
                title: 'Processing Failed',
                html: errors.length > 0 ? errors.join('<br>') : 'No employees were processed successfully.',
                icon: 'error'
            });
        }
    }

    // ============================================
    // BACK TO PROCESSING - SIMPLE FIXED VERSION (YAHI CHANGE HAI)
    // ============================================
    
    // Direct function to go to Processing Page (STEP3)
    function goToProcessingPage() {
        Swal.fire({
            title: 'Loading Processing Page...',
            html: '<div class="spinner-border"></div><p>Please wait...</p>',
            showConfirmButton: false,
            allowOutsideClick: false
        });
        
        clearPayrollStorage();
        
        setTimeout(() => {
            Swal.close();
            loadStep3Data(true);
            setCurrentWeeklyStatus('processing');
            showView('STEP3');
            savePayrollStepWithTimestamp('STEP3');
        }, 500);
    }

    // Simple confirmation modal for Back to Processing button
    function showBackToProcessingModal() {
        Swal.fire({
            title: 'Back to Salary Processing?',
            html: `
                <div style="text-align: left;">
                    <div class="alert alert-info" style="background: #dbeafe; border-left: 4px solid #2563eb; margin-bottom: 15px;">
                        <i data-lucide="info" style="color: #2563eb;"></i>
                        <strong>Going back to processing will allow you to:</strong>
                        <ul class="mt-2 mb-0 small">
                            <li>✓ Review and edit salary calculations</li>
                            <li>✓ Process pending employees</li>
                            <li>✓ Make adjustments before finalizing</li>
                        </ul>
                    </div>
                    <p class="text-muted">
                        This will unprocess processed salaries for this week and take you back one step.
                    </p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Unprocess & Go Back',
            cancelButtonText: 'Stay Here',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280',
            didOpen: () => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updateWeekStatusAndGoBack();
            }
        });
    }

    // ✅ Global: used by SweetAlert confirm (must NOT be inside document.ready)
    function updateWeekStatusAndGoBack() {
        Swal.fire({
            title: 'Updating Status...',
            html: '<div class="spinner-border"></div><p>Unprocessing salaries and moving back to Processing...</p>',
            showConfirmButton: false,
            allowOutsideClick: false
        });

        $.ajax({
            url: '/payroll/weekly-unprocess-salaries',
            type: 'POST',
            data: {
                payroll_id: currentPayrollId,
                week_id: currentWeekId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.close();
                    setCurrentWeeklyStatus(response.data?.new_status || response.week_status || response.data?.week_status || 'processing');

                    loadStep3Data(true);

                    showSuccessOverlay(
                        'Status Updated!',
                        response.message || 'Processed salaries deleted and moved back to processing successfully.',
                        'check'
                    );

                    const apiStep = response.next_step || response.data?.next_step ||
                        mapWeeklyStatusToStep(response.data?.new_status || response.week_status || response.data?.week_status);
                    const targetStep = apiStep || 'STEP3';

                    setTimeout(() => {
                        showView(targetStep);
                        savePayrollStepWithTimestamp(targetStep);

                        setTimeout(() => {
                            initializeSalarySearch();
                            updateProcessButtonText();
                        }, 300);
                    }, 800);
                } else {
                    Swal.close();
                    Swal.fire({
                        title: 'Status Update Failed',
                        text: response.message || 'Failed to unprocess salaries',
                        icon: 'error'
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                Swal.fire({
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to unprocess salaries. Please try again.',
                    icon: 'error'
                });
            }
        });
    }

    // ============================================
    // HELPER FUNCTIONS
    // ============================================
    function showSuccessOverlay(message, subtext, iconType = 'check') {
        const overlay = $('#success-overlay');
        const iconContainer = $('#success-icon-container');
        $('#success-message').text(message);
        $('#success-subtext').text(subtext);
        iconContainer.removeClass('check lock').addClass(iconType);
        overlay.fadeIn();
        setTimeout(() => overlay.fadeOut(), 2000);
    }

    function toggleLateClear() {
        const inputs = document.querySelectorAll('.late-input:not([disabled])');
        inputs.forEach(input => {
            if (!lateCleared) { if (!input.dataset.original) input.dataset.original = input.value; input.value = 0; }
            else { input.value = input.dataset.original || 0; }
        });
        lateCleared = !lateCleared;
    }

    function toggleEarlyClear() {
        const inputs = document.querySelectorAll('.early-input:not([disabled])');
        inputs.forEach(input => {
            if (!earlyCleared) { if (!input.dataset.original) input.dataset.original = input.value; input.value = 0; }
            else { input.value = input.dataset.original || 0; }
        });
        earlyCleared = !earlyCleared;
    }

    function loadPendingRequestsData(periodId) {
        $.ajax({
            url: '/payroll/weekly-check-pending-requests',
            type: 'GET',
            data: { payroll_period: periodId, week_id: currentWeekId },
            success: function(response) { 
                if (response.success) {
                    if (response.data?.current_status) {
                        setCurrentWeeklyStatus(response.data.current_status);
                    } else {
                        setCurrentWeeklyStatus('pending_approval');
                    }
                    updateStep1Cards(response.data);
                }
            }
        });
    }

    function updateStep1Cards(data) {
        const missedPunches = (typeof data.missed_punches === 'object' && data.missed_punches !== null)
            ? (data.missed_punches.count ?? 0)
            : (data.missed_punches ?? 0);
        const leaveRequests = (typeof data.leave_requests === 'object' && data.leave_requests !== null)
            ? (data.leave_requests.count ?? 0)
            : (data.leave_requests ?? 0);
        const overtimeRequests = (typeof data.overtime_requests === 'object' && data.overtime_requests !== null)
            ? (data.overtime_requests.count ?? 0)
            : (data.overtime_requests ?? 0);

        $('#missed-punches-count').text(`${missedPunches} Pending`);
        $('#missed-punches-example').html(`
            <div style="display: flex; justify-content: space-between;">
                <span>${missedPunches} pending missed punches</span>
                <span style="color: #d97706; font-weight: bold;">${missedPunches}</span>
            </div>
        `);

        $('#leave-requests-count').text(`${leaveRequests} Pending`);
        $('#leave-requests-example').html(`
            <div style="display: flex; justify-content: space-between;">
                <span>${leaveRequests} pending leave requests</span>
                <span style="color: #dc2626; font-weight: bold;">${leaveRequests}</span>
            </div>
        `);

        $('#overtime-requests-count').text(`${overtimeRequests} Pending`);
        $('#overtime-requests-example').html(`
            <div style="display: flex; justify-content: space-between;">
                <span>${overtimeRequests} pending overtime requests</span>
                <span style="color: #7c3aed; font-weight: bold;">${overtimeRequests}</span>
            </div>
        `);
    }

    function saveDisbursementDates(isManual) {
        const salaryDate = document.getElementById('salaryDisbursementDate')?.value;
        if (!salaryDate && isManual) { 
            Swal.fire({ title: 'Required Field', text: 'Please select a salary disbursement date.', icon: 'warning' }); 
            return false; 
        }
        $.ajax({
            url: '/payroll/payroll-new/save-disbursement-dates',
            type: 'POST',
            data: { payroll_id: currentPayrollId, salary_disbursement_date: salaryDate, _token: '{{ csrf_token() }}' }
        });
    }

    function initializeAutoSaveDisbursementDates() {
        const salaryDateInput = document.getElementById('salaryDisbursementDate');
        if (salaryDateInput) {
            salaryDateInput.addEventListener('change', () => saveDisbursementDates(false));
        }
    }

    function loadStep5Data() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    async function ensureWeeklyVerificationStageReady() {
        const status = (currentWeekStatus || '').toString().toLowerCase().trim();
        if (status !== 'verification') return;
        if (!currentPayrollId || !currentWeekId) return;

        try {
            const resp = await $.ajax({
                url: `/payroll/weekly-salary-data/${currentPayrollId}/${currentWeekId}`,
                type: 'GET',
                dataType: 'json'
            });

            const processedCount = Number(resp?.data?.summary?.processed_count ?? 0);
            if (processedCount > 0) return;

            const result = await Swal.fire({
                title: 'No Processed Salaries',
                html: `
                    <div style="text-align: center; padding: 10px;">
                        <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                            <i data-lucide="alert-circle" style="width: 40px; height: 40px; color: #64748b;"></i>
                        </div>
                        <h3 style="color: #0f172a; margin-bottom: 10px;">Nothing to verify yet</h3>
                        <p style="color: #64748b; margin: 0;">
                            This week is in Verification stage, but no employees are processed.<br>
                            Go back to Processing and process salaries first.
                        </p>
                    </div>
                `,
                icon: false,
                showCancelButton: true,
                confirmButtonText: 'Go to Processing',
                cancelButtonText: 'Stay on Verification',
                confirmButtonColor: '#2563eb',
                didOpen: () => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            });

            if (!result.isConfirmed) return;

            const updateResp = await $.ajax({
                url: '/payroll/weekly/update-status',
                type: 'POST',
                dataType: 'json',
                data: {
                    payroll_id: currentPayrollId,
                    week_id: currentWeekId,
                    status: 'processing',
                    _token: '{{ csrf_token() }}'
                }
            });

            if (updateResp?.success) {
                setCurrentWeeklyStatus(updateResp.data?.new_status || updateResp.week_status || 'processing');
                loadStep3Data(true);
                showView('STEP3');
                savePayrollStepWithTimestamp('STEP3');
            } else {
                Swal.fire({
                    title: 'Failed',
                    text: updateResp?.message || 'Could not move back to Processing.',
                    icon: 'error'
                });
            }
        } catch (e) {
            Swal.fire({
                title: 'Error',
                text: e?.responseJSON?.message || 'Could not check verification state.',
                icon: 'error'
            });
        }
    }

    function restoreWeeklyStep(viewName) {
        switch (viewName) {
            case 'STEP1':
                showView('STEP1', true);
                if (currentPayrollId) {
                    loadPendingRequestsData(currentPayrollId);
                }
                break;
            case 'STEP2':
                if (currentPayrollId) {
                    loadWeeklyAttendanceData(currentPayrollId, true);
                } else {
                    showView('DASHBOARD', true);
                }
                break;
            case 'STEP3':
                if (currentPayrollId) {
                    loadStep3Data(true);
                } else {
                    showView('DASHBOARD', true);
                }
                break;
            case 'STEP5':
                showView('STEP5', true);
                initializeAutoSaveDisbursementDates();
                loadStep5Data();
                break;
            case 'STEP6':
                showView('STEP6', true);
                break;
            case 'DASHBOARD':
            default:
                showView('DASHBOARD', true);
                break;
        }
    }

    // ============================================
    // EVENT HANDLERS
    // ============================================
    $(document).ready(function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();

        const serverStep = $('#server-current-step').val() || currentStep;
        const savedStep = localStorage.getItem(getStepStorageKey());

        if (serverStep && serverStep !== 'DASHBOARD') {
            clearPayrollStorage();
            restoreWeeklyStep(serverStep);
        } else if (savedStep && !isStepExpired() && savedStep !== 'DASHBOARD') {
            restoreWeeklyStep(savedStep);
        } else {
            restoreWeeklyStep('DASHBOARD');
        }

        // Start Process
        $(document).on('click', '[data-action="start-process"]', function(e) {
            e.preventDefault();
            clearPayrollStorage();
            openCurrentWeeklyStage(true);
        });

        $(document).on('click', '[data-action="open-cycle-details"]', function(e) {
            e.preventDefault();
            openCurrentWeeklyStage(true);
        });

        // Go to Step 2
        $(document).on('click', '[data-action="go-step2"]', function(e) {
            e.preventDefault();
            loadWeeklyAttendanceData(currentPayrollId);
        });

        // Freeze Attendance
        $(document).on('click', '[data-action="freeze-attendance"]', function(e) {
            e.preventDefault();
            freezeWeeklyAttendance();
        });

        // Revert Freeze (Back to Attendance)
        $(document).on('click', '[data-action="revert-freeze"]', function(e) {
            e.preventDefault();
            Swal.fire({ 
                title: 'Unfreeze Weekly Attendance?', 
                text: 'This will delete attendance summaries and allow re-editing.',
                icon: 'warning', 
                showCancelButton: true, 
                confirmButtonText: 'Unfreeze' 
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({ 
                        url: `/payroll/payroll-weekly/attendance/unfreeze/${currentPayrollId}`, 
                        type: 'POST', 
                        data: { week_id: currentWeekId, week_number: currentWeekNumber, _token: '{{ csrf_token() }}' }, 
                        success: function(response) {
                            if (response.success) { 
                                showSuccessOverlay('Attendance Unfrozen', 'You can now edit attendance', 'check'); 
                                setTimeout(() => { 
                                    loadWeeklyAttendanceData(currentPayrollId); 
                                    showView('STEP2'); 
                                }, 1500); 
                            } else { 
                                Swal.fire('Error', response.message || 'Failed to unfreeze', 'error'); 
                            }
                        }, 
                        error: () => Swal.fire('Error', 'Failed to unfreeze attendance', 'error')
                    });
                }
            });
        });

        // Process Bulk Salaries
        $(document).on('click', '[data-action="process-bulk"]', function(e) {
            e.preventDefault();
            const selectedEmpIds = $('.employee-checkbox:checked').map(function() { return $(this).data('emp-id'); }).get();
            if (selectedEmpIds.length === 0) { 
                Swal.fire({ title: 'No Employees Selected', text: 'Please select employees to process', icon: 'warning' }); 
                return; 
            }
            
            const totalCheckboxes = $('.employee-checkbox:not(:disabled)').length;
            const isProcessingAll = (selectedEmpIds.length === totalCheckboxes);
            
            let confirmMessage = `Process ${selectedEmpIds.length} employee(s) for Week ${currentWeekNumber}?`;
            if (isProcessingAll) {
                confirmMessage = `Process ALL ${selectedEmpIds.length} employees for Week ${currentWeekNumber}? After processing, you'll go to Verification.`;
            }
            
            Swal.fire({ 
                title: `Process Week ${currentWeekNumber} Salaries?`, 
                text: confirmMessage,
                icon: 'question', 
                showCancelButton: true, 
                confirmButtonText: isProcessingAll ? 'Process All & Verify' : 'Process Now',
                confirmButtonColor: '#2563eb'
            }).then((result) => {
                if (result.isConfirmed) processWeeklySalaries(selectedEmpIds);
            });
        });

        $(document).on('click', '[data-action="view-employee-details"]', function(e) {
            e.preventDefault();
            const empId = $(this).data('employee-id');
            openWeeklyEmployeeSalaryPreview(empId);
        });

         // Back to Processing (STEP5 button) - UPDATED FOR WEEKLY
        $(document).on('click', '[data-action="back-to-processing"]', function(e) {
            e.preventDefault();

            showBackToProcessingModal();
        });



        // Back to Attendance (STEP3 button)
        $(document).on('click', '[data-action="back-to-step2"]', function(e) {
            e.preventDefault();
            Swal.fire({ 
                title: 'Go Back to Attendance?', 
                text: 'This will take you to the Attendance page where you can edit attendance before processing salaries.',
                icon: 'question', 
                showCancelButton: true, 
                confirmButtonText: 'Yes, Go to Attendance',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    showView('STEP2');
                }
            });
        });

        // Save Draft
        $(document).on('click', '[data-action="save-draft"]', function(e) {
            e.preventDefault();
            Swal.fire({ title: 'Save as Draft?', icon: 'question', showCancelButton: true, confirmButtonText: 'Save Draft' }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({ 
                        url: '/payroll/weekly-save-draft', 
                        type: 'POST', 
                        data: { payroll_id: currentPayrollId, week_id: currentWeekId, _token: '{{ csrf_token() }}' }, 
                        success: function(response) {
                            if (response.success) { 
                                showSuccessOverlay('Draft Saved', 'Redirecting...', 'check'); 
                                setTimeout(() => { window.location.href = '{{ route("payroll.cycles.weekly") }}'; }, 2000); 
                            } else { 
                                Swal.fire('Error', response.message || 'Failed to save draft', 'error'); 
                            }
                        }
                    });
                }
            });
        });

        // Finalize Cycle
        $(document).on('click', '[data-action="finalize-cycle"]', function(e) {
            e.preventDefault();
            
            const totalEmployees = $('#step3TableBody tr').length;
            const processedEmployees = $('#step3TableBody tr .status-badge:contains("PROCESSED")').length;
            
            if (processedEmployees < totalEmployees) {
                Swal.fire({ title: 'Incomplete Processing', text: `Only ${processedEmployees} out of ${totalEmployees} employees processed.`, icon: 'warning' });
                return;
            }
            
            Swal.fire({ 
                title: `Finalize Week ${currentWeekNumber} Payroll?`, 
                html: 'This action will lock the payroll!', 
                icon: 'warning', 
                showCancelButton: true, 
                confirmButtonText: 'Finalize & Lock'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Finalizing...', html: '<div class="spinner-border"></div>', showConfirmButton: false });
                    $.ajax({ 
                        url: '/payroll/weekly-finalize', 
                        type: 'POST', 
                        data: { payroll_id: currentPayrollId, week_id: currentWeekId, _token: '{{ csrf_token() }}' }, 
                        success: function(response) {
                            Swal.close();
                            if (response.success) { 
                                savePayrollStepWithTimestamp('STEP6');
                                showSuccessOverlay('Payroll Finalized!', 'Weekly payroll locked successfully', 'lock');
                                setTimeout(() => { 
                                    showView('STEP6'); 
                                    clearPayrollStorage(); 
                                }, 2000); 
                            } else { 
                                Swal.fire('Error', response.message || 'Failed to finalize', 'error'); 
                            }
                        }
                    });
                }
            });
        });

        // Back to Dashboard
        $(document).on('click', '[data-action="back-to-dashboard"], [data-action="go-dashboard"]', function(e) {
            e.preventDefault();
            clearPayrollStorage();
            showView('DASHBOARD');
        });

        // Back to Checklist
        $(document).on('click', '[data-action="back-to-checklist"]', function(e) { 
            e.preventDefault(); 
            showView('STEP1'); 
        });

        // Back to List
        $(document).on('click', '[data-action="back-to-list"]', function(e) { 
            e.preventDefault(); 
            showView('STEP3'); 
        });

        // Employee Checkbox Change
        $(document).on('change', '.employee-checkbox', function() {
            updateProcessButtonText();
        });

        // Attendance Search
        $('#attendanceSearch').on('keyup', function() {
            const searchValue = this.value.toLowerCase();
            $('#attendanceTableBody tr').each(function() { 
                $(this).toggle(Array.from(this.cells).some(cell => cell.textContent.toLowerCase().includes(searchValue))); 
            });
        });
    });
</script>
@endsection