@extends('admin.layout.master')

@section('title', 'Payroll Management System')
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
        box-shadow:
            0 1px 2px 0 rgba(0, 0, 0, 0.05),
            0 1px 3px 0 rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .modern-card.hover-effect:hover {
        box-shadow:
            0 4px 6px -1px rgba(0, 0, 0, 0.1),
            0 2px 4px -1px rgba(0, 0, 0, 0.06),
            0 10px 15px -3px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
        border-color: #d1d5db;
    }

    /* Premium Card Shadow */
    .premium-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
        box-shadow:
            0 1px 3px 0 rgba(0, 0, 0, 0.07),
            0 1px 2px 0 rgba(0, 0, 0, 0.05),
            inset 0 0 0 1px rgba(255, 255, 255, 0.5);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .premium-card.hover-effect:hover {
        box-shadow:
            0 10px 15px -3px rgba(0, 0, 0, 0.07),
            0 4px 6px -2px rgba(0, 0, 0, 0.05),
            inset 0 0 0 1px rgba(255, 255, 255, 0.8);
        transform: translateY(-2px);
    }

    /* Glass Card Effect */
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow:
            0 4px 6px -1px rgba(0, 0, 0, 0.1),
            0 2px 4px -1px rgba(0, 0, 0, 0.06),
            0 0 0 1px rgba(255, 255, 255, 0.5);
    }

    .glass-card.hover-effect:hover {
        box-shadow:
            0 10px 15px -3px rgba(0, 0, 0, 0.1),
            0 4px 6px -2px rgba(0, 0, 0, 0.05),
            0 0 0 1px rgba(255, 255, 255, 0.8);
        transform: translateY(-1px);
    }

    /* Elevated Card */
    .elevated-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #f3f4f6;
        box-shadow:
            0 10px 15px -3px rgba(0, 0, 0, 0.03),
            0 4px 6px -2px rgba(0, 0, 0, 0.02);
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
        box-shadow:
            0 20px 25px -5px rgba(0, 0, 0, 0.05),
            0 10px 10px -5px rgba(0, 0, 0, 0.02);
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
        box-shadow:
            0 0 0 1px #d1d5db,
            0 4px 6px -1px rgba(0, 0, 0, 0.05);
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
        box-shadow:
            0 1px 2px 0 rgba(0, 0, 0, 0.3),
            0 1px 3px 0 rgba(0, 0, 0, 0.2);
    }

    .dark-mode .modern-card.hover-effect:hover {
        box-shadow:
            0 4px 6px -1px rgba(0, 0, 0, 0.3),
            0 2px 4px -1px rgba(0, 0, 0, 0.2),
            0 10px 15px -3px rgba(0, 0, 0, 0.25);
        border-color: #4b5563;
    }

    .dark-mode .premium-card {
        background: #1f2937;
        border-color: #374151;
        box-shadow:
            0 1px 3px 0 rgba(0, 0, 0, 0.3),
            0 1px 2px 0 rgba(0, 0, 0, 0.2),
            inset 0 0 0 1px rgba(255, 255, 255, 0.05);
    }

    .dark-mode .premium-card.hover-effect:hover {
        box-shadow:
            0 10px 15px -3px rgba(0, 0, 0, 0.25),
            0 4px 6px -2px rgba(0, 0, 0, 0.2),
            inset 0 0 0 1px rgba(255, 255, 255, 0.1);
    }

    .dark-mode .glass-card {
        background: rgba(31, 41, 55, 0.8);
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow:
            0 4px 6px -1px rgba(0, 0, 0, 0.2),
            0 2px 4px -1px rgba(0, 0, 0, 0.15),
            0 0 0 1px rgba(255, 255, 255, 0.05);
    }

    .dark-mode .glass-card.hover-effect:hover {
        box-shadow:
            0 10px 15px -3px rgba(0, 0, 0, 0.25),
            0 4px 6px -2px rgba(0, 0, 0, 0.2),
            0 0 0 1px rgba(255, 255, 255, 0.1);
    }

    .dark-mode .elevated-card {
        background: #1f2937;
        border-color: #374151;
        box-shadow:
            0 10px 15px -3px rgba(0, 0, 0, 0.2),
            0 4px 6px -2px rgba(0, 0, 0, 0.15);
    }

    .dark-mode .elevated-card::before {
        background: linear-gradient(90deg, transparent, #4b5563, transparent);
    }

    .dark-mode .elevated-card.hover-effect:hover {
        box-shadow:
            0 20px 25px -5px rgba(0, 0, 0, 0.3),
            0 10px 10px -5px rgba(0, 0, 0, 0.2);
    }

    .dark-mode .minimal-card {
        background: #1f2937;
        border-color: #374151;
        box-shadow: 0 0 0 1px #374151;
    }

    .dark-mode .minimal-card.hover-effect:hover {
        box-shadow:
            0 0 0 1px #4b5563,
            0 4px 6px -1px rgba(0, 0, 0, 0.2);
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
        box-shadow:
            0 1px 3px 0 rgba(0, 0, 0, 0.1),
            0 1px 2px 0 rgba(0, 0, 0, 0.06),
            0 0 20px 0 rgba(59, 130, 246, 0.05);
        transition: all 0.3s ease;
    }

    .card-with-glow.hover-effect:hover {
        box-shadow:
            0 4px 6px -1px rgba(0, 0, 0, 0.1),
            0 2px 4px -1px rgba(0, 0, 0, 0.06),
            0 0 30px 0 rgba(59, 130, 246, 0.1);
    }

    .dark-mode .card-with-glow {
        background: #1f2937;
        border-color: #374151;
        box-shadow:
            0 1px 3px 0 rgba(0, 0, 0, 0.3),
            0 1px 2px 0 rgba(0, 0, 0, 0.2),
            0 0 20px 0 rgba(59, 130, 246, 0.1);
    }

    .dark-mode .card-with-glow.hover-effect:hover {
        box-shadow:
            0 4px 6px -1px rgba(0, 0, 0, 0.3),
            0 2px 4px -1px rgba(0, 0, 0, 0.2),
            0 0 30px 0 rgba(59, 130, 246, 0.15);
    }

    /* Card with Inner Shadow */
    .card-inner-shadow {
        background: white;
        border-radius: 12px;
        border: 1px solid #f3f4f6;
        box-shadow:
            inset 0 2px 4px 0 rgba(0, 0, 0, 0.05),
            0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .dark-mode .card-inner-shadow {
        background: #1f2937;
        border-color: #374151;
        box-shadow:
            inset 0 2px 4px 0 rgba(0, 0, 0, 0.2),
            0 1px 2px 0 rgba(0, 0, 0, 0.1);
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
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
            0 10px 10px -5px rgba(0, 0, 0, 0.04);
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
                <li class="active"><span><b>Salary Process</b></span></li>
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
                <h4 class="card-title">Salary Process</h4>
            </div>

            <div class="card-body">
                <!-- Payroll UI Container -->
                <div class="payroll-system">
                    <!-- Main Content -->
                    <div class="relative z-10" style="padding: 16px 0;">
                        <!-- All your existing views remain here - STEP1, STEP2, STEP3, etc. -->
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
                                                <div
                                                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                                                    <div>
                                                        <h3 class="text-primary">{{ $monthName }} Payroll</h3>
                                                        <p
                                                            style="font-size: 0.875rem; color: #64748b; margin-bottom: 4px;">
                                                            {{ $financialYearName }}
                                                        </p>
                                                        <div style="display: flex; align-items: center; gap: 8px;">
                                                            <span style="font-size: 0.75rem; color: #64748b;">
                                                                {{
                                                                \Carbon\Carbon::parse($payrollPeriod->pp_start_date)->format('d
                                                                M') }}
                                                                -
                                                                {{
                                                                \Carbon\Carbon::parse($payrollPeriod->pp_end_date)->format('d
                                                                M') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="status-badge status-{{ strtolower($payrollPeriod->pp_status_code) }}">
                                                        <i data-lucide="clock" style="width: 12px; height: 12px;"></i>
                                                        {{ $payrollPeriod->status_display }}
                                                    </div>
                                                </div>

                                                <!-- Period Details -->
                                                <div
                                                    style="margin-bottom: 16px; padding: 12px; background: #9e9e9e2b; border-radius: 8px;">
                                                    <div
                                                        style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                                        <div>
                                                            <div style="font-size: 0.75rem; color: #64748b;">Total Days
                                                            </div>
                                                            <div
                                                                style="font-size: 0.875rem; font-weight: 600; color: #475569;">
                                                                {{ $totalDays }}
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div style="font-size: 0.75rem; color: #64748b;">Working
                                                                Days</div>
                                                            <div
                                                                style="font-size: 0.875rem; font-weight: 600; color: #475569;">
                                                                {{ $workingDays }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div style="font-size: 0.75rem; color: #64748b;">
                                                        @if ($payrollPeriod->created_at)
                                                        Created: {{
                                                        \Carbon\Carbon::parse($payrollPeriod->created_at)->format('d M
                                                        Y, h:i A') }}
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Action Buttons -->
                                                <button data-action="start-process"
                                                    data-period-id="{{ $payrollPeriod->pp_id }}"
                                                    data-payroll-id="{{ $payrollPeriod->pp_id }}"
                                                    style="margin-bottom: 16px;padding: 12px 32px; background: transparent; color: #2563eb; font-weight: bold; border-radius: 12px; border: 1px solid #2563eb; display: flex; align-items: center; gap: 8px; transition: all 0.2s; cursor: pointer;"
                                                    onmouseover="this.style.background='#2563eb'; this.style.color='white';"
                                                    onmouseout="this.style.background='transparent'; this.style.color='#2563eb';">
                                                    <i data-lucide="play" style="width: 16px; height: 16px;"></i>
                                                    Start Payroll Process
                                                </button>



                                                <div style="display: flex; gap: 8px;">
                                                    <a href="{{ route('adhoc.index') }}?payroll_id={{ $payrollPeriod->pp_id }}"
                                                        style="flex: 1; padding: 8px; background: #f8fafc; color: #475569; border-radius: 8px; font-size: 0.75rem; font-weight: 700; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; cursor: pointer;">
                                                        <i data-lucide="plus-circle"
                                                            style="width: 14px; height: 14px;"></i>
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
                                                    <i data-lucide="zap"
                                                        style="width: 16px; height: 16px; color: #f59e0b;"></i>
                                                    Quick Actions
                                                </h4>
                                                <div style="display: flex; flex-direction: column; gap: 8px;">

                                                    <a href="{{ route('payroll.cycles') }}"
                                                        style="padding: 8px 12px; background: #f8fafc; color: #475569; border-radius: 8px; font-size: 0.75rem; font-weight: 600; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px; text-decoration: none; cursor: pointer;">
                                                        <i data-lucide="calendar"
                                                            style="width: 14px; height: 14px; color: #059669;"></i>
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
                                                <h3 class="head-text"
                                                    style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                                                    <i data-lucide="users"
                                                        style="width: 20px; height: 20px; color: #2563eb;"></i>
                                                    Employee Statistics
                                                </h3>

                                                <!-- Compact Stats Grid - 3x2 Layout -->
                                                <div
                                                    style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px;">
                                                    <!-- Total Employees -->
                                                    <div class="glass-card hover-effect"
                                                        style="padding: 16px; flex: 1 1 calc(25% - 16px); min-width: 200px;">
                                                        <div
                                                            style="font-size: 0.65rem; color: #3b82f6; margin-bottom: 4px; font-weight: 600; text-transform: uppercase;">
                                                            Total
                                                        </div>
                                                        <div
                                                            style="font-size: 1.25rem; font-weight: 700; color: #1d4ed8; margin-bottom: 2px; line-height: 1.2;">
                                                            {{ $totalEmployees }}
                                                        </div>
                                                        <div style="font-size: 0.6rem; color: #64748b;">Eligible</div>
                                                    </div>

                                                    <!-- Active Employees -->
                                                    <div class="glass-card hover-effect"
                                                        style="padding: 16px; flex: 1 1 calc(25% - 16px); min-width: 200px;">
                                                        <div
                                                            style="font-size: 0.65rem; color: #16a34a; margin-bottom: 4px; font-weight: 600; text-transform: uppercase;">
                                                            Active
                                                        </div>
                                                        <div
                                                            style="font-size: 1.25rem; font-weight: 700; color: #166534; margin-bottom: 2px; line-height: 1.2;">
                                                            {{ $totalActiveEmployees }}
                                                        </div>
                                                        <div style="font-size: 0.6rem; color: #64748b;">Working</div>
                                                    </div>

                                                    <!-- Inactive Employees -->
                                                    <div class="glass-card hover-effect"
                                                        style="padding: 16px; flex: 1 1 calc(25% - 16px); min-width: 200px;">
                                                        <div
                                                            style="font-size: 0.65rem; color: #dc2626; margin-bottom: 4px; font-weight: 600; text-transform: uppercase;">
                                                            Inactive
                                                        </div>
                                                        <div
                                                            style="font-size: 1.25rem; font-weight: 700; color: #991b1b; margin-bottom: 2px; line-height: 1.2;">
                                                            {{ $totalInactiveEmp }}
                                                        </div>
                                                        <div style="font-size: 0.6rem; color: #64748b;">Overlapping
                                                        </div>
                                                    </div>

                                                    <!-- Ready for Processing -->
                                                    <div class="glass-card hover-effect"
                                                        style="padding: 16px; flex: 1 1 calc(25% - 16px); min-width: 200px;">
                                                        <div
                                                            style="font-size: 0.65rem; color: #16a34a; margin-bottom: 4px; font-weight: 600; text-transform: uppercase;">
                                                            Ready
                                                        </div>
                                                        <div
                                                            style="font-size: 1.25rem; font-weight: 700; color: #141f18; margin-bottom: 2px; line-height: 1.2;">
                                                            {{ $readyToProcess }}
                                                        </div>
                                                        <div style="font-size: 0.6rem; color: #64748b;">Pending</div>
                                                    </div>

                                                    <!-- On Hold -->
                                                    <div class="glass-card hover-effect"
                                                        style="padding: 16px; flex: 1 1 calc(25% - 16px); min-width: 200px;">
                                                        <div
                                                            style="font-size: 0.65rem; color: #ea580c; margin-bottom: 4px; font-weight: 600; text-transform: uppercase;">
                                                            On Hold
                                                        </div>
                                                        <div
                                                            style="font-size: 1.25rem; font-weight: 700; color: #9a3412; margin-bottom: 2px; line-height: 1.2;">
                                                            {{ $heldEmployees }}
                                                        </div>
                                                        <div style="font-size: 0.6rem; color: #64748b;">Held</div>
                                                    </div>

                                                    <!-- Processed -->
                                                    <div class="glass-card hover-effect"
                                                        style="padding: 16px; flex: 1 1 calc(25% - 16px); min-width: 200px;">
                                                        <div
                                                            style="font-size: 0.65rem; color: #9333ea; margin-bottom: 4px; font-weight: 600; text-transform: uppercase;">
                                                            Processed
                                                        </div>
                                                        <div
                                                            style="font-size: 1.25rem; font-weight: 700; color: #7c3aed; margin-bottom: 2px; line-height: 1.2;">
                                                            {{ $processedEmployeesCount }}
                                                        </div>
                                                        <div style="font-size: 0.6rem; color: #64748b;">Completed</div>
                                                    </div>
                                                </div>

                                                <!-- Status Breakdown -->
                                                <div>
                                                    <h4 class="head-text">Status Breakdown</h4>
                                                    <div style="display: flex; flex-direction: column; gap: 8px;">
                                                        <!-- Pending -->
                                                        <div
                                                            style="display: flex; justify-content: space-between; background: #9e9e9e2b; align-items: center; padding: 8px 12px; border-radius: 6px;">
                                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                                <div
                                                                    style="width: 8px; height: 8px; background: #94a3b8; border-radius: 50%;">
                                                                </div>
                                                                <span class="para-text">Pending (Ready to
                                                                    Process)</span>
                                                            </div>
                                                            <span style="font-weight: 700; color: #475569;">
                                                                {{ $pendingEmployees }}
                                                            </span>
                                                        </div>

                                                        <!-- On Hold -->
                                                        <div
                                                            style="display: flex; justify-content: space-between; background: #9e9e9e2b; align-items: center; padding: 8px 12px; border-radius: 6px;">
                                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                                <div
                                                                    style="width: 8px; height: 8px; background: #f59e0b; border-radius: 50%;">
                                                                </div>
                                                                <span class="para-text">On Hold</span>
                                                            </div>
                                                            <span style="font-weight: 700; color: #92400e;">
                                                                {{ $heldEmployees }}
                                                            </span>
                                                        </div>

                                                        <!-- Processed -->
                                                        <div
                                                            style="display: flex; justify-content: space-between; background: #9e9e9e2b; align-items: center; padding: 8px 12px; border-radius: 6px;">
                                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                                <div
                                                                    style="width: 8px; height: 8px; background: #10b981; border-radius: 50%;">
                                                                </div>
                                                                <span class="para-text">Processed</span>
                                                            </div>
                                                            <span style="font-weight: 700; color: #059669;">
                                                                {{ $processedEmployeesCount }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>




                                        <!-- Pending Requests Card -->
                                        {{-- @if($pendingRequests['has_pending'])
                                        <div class="glass-card hover-effect" style="border-left: 4px solid #f59e0b;">
                                            <div style="padding: 20px;">
                                                <h3 class="head-text"
                                                    style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                                                    <i data-lucide="alert-triangle"
                                                        style="width: 20px; height: 20px; color: #f59e0b;"></i>
                                                    Pending Approvals (Step 1)
                                                </h3>

                                                <div style="display: flex; flex-direction: column; gap: 12px;">
                                                    @if($pendingRequests['missed_punches'] > 0)
                                                    <div
                                                        style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #fef3c7; border-radius: 8px;">
                                                        <div style="display: flex; align-items: center; gap: 8px;">
                                                            <i data-lucide="clock"
                                                                style="width: 16px; height: 16px; color: #d97706;"></i>
                                                            <span class="para-text">Missed Punches</span>
                                                        </div>
                                                        <span style="font-weight: 700; color: #92400e;">
                                                            {{ $pendingRequests['missed_punches'] }} pending
                                                        </span>
                                                    </div>
                                                    @endif

                                                    @if($pendingRequests['leave_requests'] > 0)
                                                    <div
                                                        style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #fef3c7; border-radius: 8px;">
                                                        <div style="display: flex; align-items: center; gap: 8px;">
                                                            <i data-lucide="calendar"
                                                                style="width: 16px; height: 16px; color: #d97706;"></i>
                                                            <span class="para-text">Leave Requests</span>
                                                        </div>
                                                        <span style="font-weight: 700; color: #92400e;">
                                                            {{ $pendingRequests['leave_requests'] }} pending
                                                        </span>
                                                    </div>
                                                    @endif

                                                    @if($pendingRequests['overtime_requests'] > 0)
                                                    <div
                                                        style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #fef3c7; border-radius: 8px;">
                                                        <div style="display: flex; align-items: center; gap: 8px;">
                                                            <i data-lucide="clock"
                                                                style="width: 16px; height: 16px; color: #d97706;"></i>
                                                            <span class="para-text">Overtime Requests</span>
                                                        </div>
                                                        <span style="font-weight: 700; color: #92400e;">
                                                            {{ $pendingRequests['overtime_requests'] }} pending
                                                        </span>
                                                    </div>
                                                    @endif
                                                </div>

                                                <div style="margin-top: 16px; text-align: center;">
                                                    <button data-action="check-pending-requests"
                                                        data-period-id="{{ $payrollPeriod->pp_id }}"
                                                        style="padding: 8px 16px; background: #f59e0b; color: white; border-radius: 8px; font-size: 0.75rem; font-weight: 600; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                                                        <i data-lucide="refresh-cw"
                                                            style="width: 14px; height: 14px;"></i>
                                                        Check & Approve All
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @endif --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Step 1: Action Required - Updated Cards Layout (3 per row) -->
                        <div data-view="STEP1" style="display: none; animation: slide-in-right 0.5s ease-out;">
                            <!-- Workflow Stepper -->
                            <div class="stepper-container">
                                @for ($i = 1; $i <= 5; $i++) <div class="stepper-step">
                                    @if ($i < 5) <div class="stepper-connector {{ $i < 1 ? 'active' : '' }}">
                            </div>
                            @endif
                            <div class="stepper-circle {{ $i == 1 ? 'active' : '' }}">
                                {{ $i }}
                            </div>
                            <div class="stepper-label {{ $i == 1 ? 'active' : '' }}">
                                @if ($i == 1)
                                Approval
                                @elseif($i == 2)
                                Freeze
                                @elseif($i == 3)
                                Process
                                @elseif($i == 4)
                                Verify
                                @else
                                Finish
                                @endif
                            </div>
                        </div>
                        @endfor
                    </div>

                    {{-- Add hidden input for current step --}}
                    <input type="hidden" id="server-current-step" value="{{ $currentStep ?? 'DASHBOARD' }}">

                    <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <button data-action="back-to-dashboard" data-period-id="{{ $payrollPeriod->pp_id }}"
                                style="padding: 8px; background: white; border: 1px solid #e2e8f0; border-radius: 50%; color: #94a3b8; transition: all 0.2s; cursor: pointer;"
                                title="Back to Dashboard (Go Back)">
                                <i data-lucide="arrow-left" style="width: 20px; height: 20px;"></i>
                            </button>

                            <div>
                                <h2 class="head-text">Salary Process</h2>
                                <p style="font-size: 0.75rem; color: #64748b;">
                                    @if ($pendingRequests['total_pending'] > 0)
                                    {{ $pendingRequests['total_pending'] }} pending requests found. Resolve
                                    them before
                                    proceeding.
                                    @else
                                    No pending requests found. You can proceed to Freeze Grid.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    @if ($pendingRequests['total_pending'] > 0)
                    <!-- Process Info -->
                    <div
                        style="margin-bottom: 24px; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 8px; padding: 16px; display: flex; align-items: flex-start; gap: 12px; animation: slide-in-top 0.3s ease-out;">
                        <div
                            style="padding: 8px; background: rgba(220, 38, 38, 0.1); border-radius: 50%; color: #dc2626;">
                            <i data-lucide="alert-triangle" style="width: 16px; height: 16px;"></i>
                        </div>
                        <div>
                            <h4 style="color: #991b1b; font-weight: bold; font-size: 0.875rem; margin: 0;">
                                Pending Requests Detected!
                            </h4>
                            <p
                                style="color: #dc2626; font-size: 0.75rem; line-height: 1.5; margin-top: 4px; margin-bottom: 0;">
                                You cannot proceed until all pending requests are resolved. Please resolve
                                them first.
                            </p>
                        </div>
                    </div>
                    @else
                    <!-- Process Info -->
                    <div
                        style="margin-bottom: 24px; background: #dbeafe; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; display: flex; align-items: flex-start; gap: 12px; animation: slide-in-top 0.3s ease-out;">
                        <div
                            style="padding: 8px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; color: #2563eb;">
                            <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                        </div>
                        <div>
                            <h4 style="color: #1e3a8a; font-weight: bold; font-size: 0.875rem; margin: 0;">
                                All Clear!
                            </h4>
                            <p
                                style="color: #1d4ed8; font-size: 0.75rem; line-height: 1.5; margin-top: 4px; margin-bottom: 0;">
                                No pending requests found. You can safely proceed to Freeze Grid.
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- Add this modal HTML in your blade file -->
                    <div id="pending-requests-modal" class="modal-overlay hidden" style="display: none;">
                        <div class="modal-container max-w-4xl">
                            <div class="modal-header">
                                <h3 class="modal-title">Pending Requests Details</h3>
                                <button class="modal-close-btn" data-action="close-modal">
                                    <i data-lucide="x"></i>
                                </button>
                            </div>

                            <div class="modal-body">
                                <!-- Tabs for different request types -->
                                <div class="tabs-container" style="margin-bottom: 20px;">
                                    <div class="tabs" style="display: flex; border-bottom: 1px solid #e2e8f0;">
                                        <button class="tab-btn active" data-tab="missed-punches">
                                            <i data-lucide="alert-circle"
                                                style="width: 16px; height: 16px; margin-right: 8px;"></i>
                                            Missed Punches (<span id="missed-punch-count">0</span>)
                                        </button>
                                        <button class="tab-btn" data-tab="leave-requests">
                                            <i data-lucide="calendar"
                                                style="width: 16px; height: 16px; margin-right: 8px;"></i>
                                            Leave Requests (<span id="leave-request-count">0</span>)
                                        </button>
                                        <button class="tab-btn" data-tab="overtime-requests">
                                            <i data-lucide="clock"
                                                style="width: 16px; height: 16px; margin-right: 8px;"></i>
                                            Overtime Requests (<span id="overtime-count">0</span>)
                                        </button>
                                    </div>
                                </div>

                                <!-- Missed Punches Tab Content -->
                                <div id="missed-punches-tab" class="tab-content active">
                                    <div class="modal-employee-list" id="missed-punches-list"
                                        style="max-height: 400px;">
                                        <!-- Missed punches will be loaded here -->
                                    </div>
                                    <div id="missed-punches-empty" class="modal-empty-state" style="display: none;">
                                        <i data-lucide="check-circle"
                                            style="width: 48px; height: 48px; color: #059669;"></i>
                                        <p>No pending missed punches</p>
                                    </div>
                                </div>

                                <!-- Leave Requests Tab Content -->
                                <div id="leave-requests-tab" class="tab-content" style="display: none;">
                                    <div class="modal-employee-list" id="leave-requests-list"
                                        style="max-height: 400px;">
                                        <!-- Leave requests will be loaded here -->
                                    </div>
                                    <div id="leave-requests-empty" class="modal-empty-state" style="display: none;">
                                        <i data-lucide="check-circle"
                                            style="width: 48px; height: 48px; color: #059669;"></i>
                                        <p>No pending leave requests</p>
                                    </div>
                                </div>

                                <!-- Overtime Requests Tab Content -->
                                <div id="overtime-requests-tab" class="tab-content" style="display: none;">
                                    <div class="modal-employee-list" id="overtime-requests-list"
                                        style="max-height: 400px;">
                                        <!-- Overtime requests will be loaded here -->
                                    </div>
                                    <div id="overtime-requests-empty" class="modal-empty-state" style="display: none;">
                                        <i data-lucide="check-circle"
                                            style="width: 48px; height: 48px; color: #059669;"></i>
                                        <p>No pending overtime requests</p>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button class="modal-btn modal-btn-secondary" data-action="close-modal">
                                    Close
                                </button>
                                <button class="modal-btn modal-btn-primary" onclick="resolveAllPending()">
                                    <i data-lucide="check-circle"
                                        style="width: 16px; height: 16px; margin-right: 8px;"></i>
                                    Mark All as Resolved
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Card में View Details Button जोड़ें -->
                    {{-- @if ($pendingRequests['total_pending'] > 0)
                    <button onclick="showPendingRequestsModal({{ $payrollPeriod->pp_id }})"
                        style="width: 100%; margin-top: 8px; padding: 8px; background: #3b82f6; color: white; border: 1px solid #2563eb; border-radius: 8px; font-size: 0.75rem; font-weight: bold; transition: all 0.2s; cursor: pointer; text-decoration: none; display: inline-block; text-align: center;">
                        <i data-lucide="eye" style="width: 14px; height: 14px; margin-right: 6px;"></i>
                        View Details
                    </button>
                    @endif --}}

                    <!-- First Row: 3 Cards -->
                    <div
                        style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; max-width: 80rem; margin: 0 auto 24px;">
                        <!-- Missed Punches Card -->
                        <div class="glass-card hover-effect" style="border-left: 4px solid #f59e0b;">
                            <div style="padding: 16px;">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div
                                            style="padding: 8px; background: #fef3c7; border-radius: 8px; color: #d97706;">
                                            <i data-lucide="alert-circle" style="width: 24px; height: 24px;"></i>
                                        </div>
                                        <div>
                                            <h3 class="head-text">Missed Punches</h3>
                                        </div>
                                    </div>
                                    <span id="missed-punches-count" class="card-count"
                                        style="background: #fef3c7; color: #92400e; font-weight: bold; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem;">
                                        {{ $pendingRequests['missed_punches'] }} Pending
                                    </span>
                                </div>

                                <div id="missed-punches-example" class="example-content"
                                    style="border-radius: 8px; padding: 12px; margin-bottom: 16px; border: 1px solid #e2e8f0;">
                                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                                        @if ($pendingRequests['missed_punches'] > 0)
                                        <span class="para-text">{{ $pendingRequests['missed_punches'] }}
                                            pending missed
                                            punches</span>
                                        <span style="color: #d97706; font-weight: bold;">{{
                                            $pendingRequests['missed_punches'] }}</span>
                                        @else
                                        <span class="para-text">No pending requests</span>
                                        <span style="color: #d97706; font-weight: bold;">--</span>
                                        @endif
                                    </div>
                                </div>

                                @if ($pendingRequests['missed_punches'] > 0)
                                <a href="{{ $pendingRequests['missed_punch_url'] }}?payroll_id={{ $pendingRequests['payroll_id'] }}"
                                    target="_blank"
                                    style="width: 100%; padding: 8px; color: white; border: 1px solid #d97706; border-radius: 8px; font-size: 0.75rem; font-weight: bold; transition: all 0.2s; cursor: pointer; text-decoration: none; display: inline-block; text-align: center;">
                                    Resolve Missed Punches
                                </a>
                                @else
                                <button class="action-btn"
                                    style="width: 100%; padding: 8px; background: white; color: #475569; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.75rem; font-weight: bold; transition: all 0.2s; cursor: pointer;"
                                    disabled>
                                    No Actions Required
                                </button>
                                @endif
                            </div>
                        </div>

                        <!-- Leave Requests Card (2nd card) -->
                        <div class="glass-card hover-effect" style="border-left: 4px solid #f43f5e;">
                            <div style="padding: 16px;">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div
                                            style="padding: 8px; background: #fee2e2; border-radius: 8px; color: #dc2626;">
                                            <i data-lucide="calendar" style="width: 24px; height: 24px;"></i>
                                        </div>
                                        <div>
                                            <h3 class="head-text">Leave Requests</h3>
                                        </div>
                                    </div>
                                    <span class="card-count"
                                        style="background: #fee2e2; color: #b91c1c; font-weight: bold; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem;">
                                        {{ $pendingRequests['leave_requests'] }} Pending
                                    </span>
                                </div>

                                <div class="example-content"
                                    style="border-radius: 8px; padding: 12px; margin-bottom: 16px; border: 1px solid #e2e8f0;">
                                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                                        @if ($pendingRequests['leave_requests'] > 0)
                                        <span class="para-text">{{ $pendingRequests['leave_requests'] }}
                                            pending leave
                                            requests</span>
                                        <span style="color: #dc2626; font-weight: bold;">{{
                                            $pendingRequests['leave_requests'] }}</span>
                                        @else
                                        <span class="para-text">No pending requests</span>
                                        <span style="color: #dc2626; font-weight: bold;">--</span>
                                        @endif
                                    </div>
                                </div>

                                @if ($pendingRequests['leave_requests'] > 0)
                                <a href="{{ route('requests.leave') }}?payroll_id={{ $pendingRequests['payroll_id'] }}"
                                    target="_blank"
                                    style="width: 100%; padding: 8px; color: white; border: 1px solid #b91c1c; border-radius: 8px; font-size: 0.75rem; font-weight: bold; transition: all 0.2s; cursor: pointer; text-decoration: none; display: inline-block; text-align: center;">
                                    View Leave Requests
                                </a>
                                @else
                                <button class="action-btn"
                                    style="width: 100%; padding: 8px; background: white; color: #475569; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.75rem; font-weight: bold; transition: all 0.2s; cursor: pointer;"
                                    disabled>
                                    No Actions Required
                                </button>
                                @endif
                            </div>
                        </div>

                        <!-- Over Time Card (3rd card) -->
                        <div class="glass-card hover-effect" style="border-left: 4px solid #8b5cf6;">
                            <div style="padding: 16px;">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div
                                            style="padding: 8px; background: #ede9fe; border-radius: 8px; color: #7c3aed;">
                                            <i data-lucide="clock" style="width: 24px; height: 24px;"></i>
                                        </div>
                                        <div>
                                            <h3 class="head-text">Over Time Requests</h3>
                                        </div>
                                    </div>
                                    <span class="card-count"
                                        style="background: #ede9fe; color: #7c3aed; font-weight: bold; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem;">
                                        {{ $pendingRequests['overtime_requests'] }} Pending
                                    </span>
                                </div>

                                <div class="example-content"
                                    style="border-radius: 8px; padding: 12px; margin-bottom: 16px; border: 1px solid #e2e8f0;">
                                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                                        @if ($pendingRequests['overtime_requests'] > 0)
                                        <span class="para-text">{{ $pendingRequests['overtime_requests'] }}
                                            pending
                                            overtime requests</span>
                                        <span style="color: #7c3aed; font-weight: bold;">{{
                                            $pendingRequests['overtime_requests'] }}</span>
                                        @else
                                        <span class="para-text">No pending requests</span>
                                        <span style="color: #7c3aed; font-weight: bold;">--</span>
                                        @endif
                                    </div>
                                </div>

                                @if ($pendingRequests['overtime_requests'] > 0)
                                {{-- <a
                                    href="{{ route('overtime.requests.index') }}?payroll_id={{ $pendingRequests['payroll_id'] }}"
                                    target="_blank"
                                    style="width: 100%; padding: 8px; background: #7c3aed; color: white; border: 1px solid #5b21b6; border-radius: 8px; font-size: 0.75rem; font-weight: bold; transition: all 0.2s; cursor: pointer; text-decoration: none; display: inline-block; text-align: center;">
                                    Approve Overtime
                                </a> --}}

                                <a href="{{ route('approve.overtime') }}?payroll_id={{ $pendingRequests['payroll_id'] }}"
                                    target="_blank"
                                    style="width: 100%; padding: 8px; background: #7c3aed; color: white; border: 1px solid #5b21b6; border-radius: 8px; font-size: 0.75rem; font-weight: bold; transition: all 0.2s; cursor: pointer; text-decoration: none; display: inline-block; text-align: center;">
                                    Approve Overtime
                                </a>
                                @else
                                <button class="action-btn"
                                    style="width: 100%; padding: 8px; background: white; color: #475569; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.75rem; font-weight: bold; transition: all 0.2s; cursor: pointer;"
                                    disabled>
                                    No Actions Required
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div style="display: flex; justify-content: flex-end; margin-top: 32px; gap: 16px;">
                        @if ($pendingRequests['total_pending'] > 0)
                        <button
                            style="padding: 12px 32px; background: #dc2626; color: white; font-weight: bold; border-radius: 12px; border: 2px solid #dc2626; display: flex; align-items: center; gap: 8px; transition: all 0.2s; cursor: pointer;"
                            onmouseover="this.style.background='#b91c1c'; this.style.borderColor='#b91c1c';"
                            onmouseout="this.style.background='#dc2626'; this.style.borderColor='#dc2626';">
                            Resolve Pending Requests First
                            <i data-lucide="alert-triangle" style="width: 18px; height: 18px;"></i>
                        </button>
                        @else
                        <button data-action="go-step2" data-period-id="{{ $payrollPeriod->pp_id }}"
                            style="padding: 12px 32px; background: transparent; color: #2563eb; font-weight: bold; border-radius: 12px; border: 2px solid #2563eb; display: flex; align-items: center; gap: 8px; transition: all 0.2s; cursor: pointer;"
                            onmouseover="this.style.background='#2563eb'; this.style.color='white';"
                            onmouseout="this.style.background='transparent'; this.style.color='#2563eb';">
                            Proceed to Freeze Attendance
                            <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                        </button>
                        @endif
                    </div>
                </div>



                <!-- Step 2: Freeze Grid -->
                <div data-view="STEP2" style="display: none; animation: slide-in-right 0.5s ease-out;">
                    <!-- Workflow Stepper -->
                    <div class="stepper-container">
                        @for ($i = 1; $i <= 5; $i++) <div class="stepper-step">
                            @if ($i < 5) <div class="stepper-connector {{ $i < 2 ? 'active' : '' }}">
                    </div>
                    @endif
                    <div class="stepper-circle {{ $i <= 2 ? 'active' : '' }}">
                        {{ $i }}
                    </div>
                    <div class="stepper-label {{ $i <= 2 ? 'active' : '' }}">
                        @if ($i == 1)
                        Approval
                        @elseif($i == 2)
                        Freeze
                        @elseif($i == 3)
                        Process
                        @elseif($i == 4)
                        Verify
                        @else
                        Finish
                        @endif
                    </div>
                </div>
                @endfor
            </div>





            <!-- Process Info -->
            <div
                style="margin-bottom: 24px; background: #dbeafe; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; display: flex; align-items: flex-start; gap: 12px;">
                <div style="padding: 8px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; color: #2563eb;">
                    <i data-lucide="info" style="width: 16px; height: 16px;"></i>
                </div>
                <div>
                    <h4 style="color: #1e3a8a; font-weight: bold; font-size: 0.875rem; margin: 0;">
                        Attendance Finalization</h4>
                    <p style="color: #1d4ed8; font-size: 0.75rem; line-height: 1.5; margin-top: 4px; margin-bottom: 0;">
                        Verify the consolidated attendance data. Once frozen, this data will be used for
                        salary calculation and cannot be modified easily.</p>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">
                <div
                    style="display: flex; justify-content: space-between; align-items: center; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <button data-action="back-to-checklist"
                            style="padding: 8px; background: white; border: 1px solid #e2e8f0; border-radius: 50%; color: #94a3b8; transition: all 0.2s; cursor: pointer;"
                            title="Back to Checklist (Go Back)">
                            <i data-lucide="arrow-left" style="width: 20px; height: 20px;"></i>
                        </button>

                        <div>
                            <h2 class="head-text">Attendance Sheet</h2>
                            <p style="font-size: 0.75rem; color: #64748b;">{{ $monthName }} 2025
                            </p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px; align-items: center;">
                        <button onclick="toggleLateClear()"
                            style="padding: 8px 16px; font-size: 0.75rem; font-weight: bold; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; display: flex; align-items: center; gap: 8px; cursor: pointer;"
                            id="clearLateBtn">
                            Clear Late
                        </button>

                        <button onclick="toggleEarlyClear()"
                            style="padding: 8px 16px; font-size: 0.75rem; font-weight: bold; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; display: flex; align-items: center; gap: 8px; cursor: pointer;"
                            id="clearEarlyBtn">
                            Clear Early
                        </button>

                        <input type="text" id="attendanceSearch" placeholder="Search employee..."
                            style="padding: 8px 12px; font-size: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; min-width: 200px;" />
                    </div>
                </div>
            </div>


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
                            <tr>
                                <td colspan="16" style="padding:20px;text-align:center;color:#94a3b8;">
                                    Click "Proceed" to load attendance
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Freeze Attendance Button -->
            <div style="position: fixed; bottom: 24px; right: 24px; z-index: 30;">
                <button type="button" data-action="freeze-attendance"
                    style="padding: 12px 32px; background: #dc2626; color: white; font-weight: bold; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(220, 38, 38, 0.2); display: flex; align-items: center; gap: 8px; transition: all 0.2s; border: none; cursor: pointer; animation: pulse 2s infinite;">
                    <i data-lucide="lock" style="width: 18px; height: 18px;"></i>
                    Freeze Attendance
                </button>
            </div>
        </div>

        <!-- Step 3: Salary List -->
        <div data-view="STEP3" style="display: none; animation: slide-in-right 0.5s ease-out;">
            <!-- Workflow Stepper -->
            <div class="stepper-container">
                @for ($i = 1; $i <= 5; $i++) <div class="stepper-step">
                    @if ($i < 5) <div class="stepper-connector {{ $i < 3 ? 'active' : '' }}">
            </div>
            @endif
            <div class="stepper-circle {{ $i <= 3 ? 'active' : '' }}">
                {{ $i }}
            </div>
            <div class="stepper-label {{ $i <= 3 ? 'active' : '' }}">
                @if ($i == 1)
                Approval
                @elseif($i == 2)
                Freeze
                @elseif($i == 3)
                Process
                @elseif($i == 4)
                Verify
                @else
                Finish
                @endif
            </div>
        </div>
        @endfor
    </div>

    <!-- Process Info -->
    <div
        style="margin-bottom: 24px; background: #dbeafe; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; display: flex; align-items: flex-start; gap: 12px;">
        <div style="padding: 8px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; color: #2563eb;">
            <i data-lucide="info" style="width: 16px; height: 16px;"></i>
        </div>
        <div>
            <h4 style="color: #1e3a8a; font-weight: bold; font-size: 0.875rem; margin: 0;">
                Salary Processing</h4>
            <p style="color: #1d4ed8; font-size: 0.75rem; line-height: 1.5; margin-top: 4px; margin-bottom: 0;">
                Review employee salaries, make adjustments, and process payroll.</p>
        </div>
    </div>

    <!-- Compact Hold Details Card - One Line -->
    @if(isset($payrollPeriod) && $payrollPeriod->has_holds)
    <div id="hold-summary-card" class="glass-card hover-effect"
        style="margin-bottom: 16px; border-left: 4px solid #ef4444; border-radius: 8px;">
        <div style="padding: 12px 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <!-- Left Side: Hold Count with Icon -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="position: relative;">
                        <div
                            style="padding: 6px; background: #fee2e2; border-radius: 6px; color: #dc2626; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="pause" style="width: 16px; height: 16px;"></i>
                        </div>
                        <div
                            style="position: absolute; top: -6px; right: -6px; width: 18px; height: 18px; background: #dc2626; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; border: 2px solid white;">
                            {{ $payrollPeriod->hold_count ?? 0 }}
                        </div>
                    </div>
                    <div>
                        <div class="para-text">
                            Salary Holds Detected
                        </div>
                        <div style="font-size: 0.75rem; color: #6b7280; margin-top: 2px;">
                            {{ $payrollPeriod->hold_count ?? 0 }} employee(s) on hold
                        </div>
                    </div>
                </div>

                <!-- Right Side: Action Button -->
                <button id="view-hold-details-btn" data-cycle-id="{{ $payrollPeriod->pp_id ?? $payrollPeriod->id }}"
                    data-cycle-month="{{ $monthName ?? 'N/A' }}"
                    style="padding: 6px 12px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border: none; border-radius: 6px; font-size: 0.75rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;"
                    onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 2px 4px rgba(220, 38, 38, 0.2)';"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    <i data-lucide="eye" style="width: 14px; height: 14px;"></i>
                    View Details
                </button>
            </div>
        </div>
    </div>
    @endif


    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; margin-top: 16px; flex-wrap: wrap; gap: 12px;">

        <!-- Left side: Back button and title -->
        <div style="display: flex; align-items: center; gap: 12px;">
            <button data-action="revert-freeze"
                data-payroll-id="{{ $payrollPeriod->payroll_id ?? $payrollPeriod->pp_id }}"
                style="padding: 6px; background: white; border: 1px solid #e2e8f0; border-radius: 50%; color: #64748b; transition: all 0.2s; cursor: pointer; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                title="Unfreeze Attendance (Go Back)">
                <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
            </button>
            <div>
                <h2 class="head-text" style="font-size: 1rem; margin: 0;">Salary Process</h2>
                <p style="font-size: 0.7rem; color: #64748b; margin: 2px 0 0 0;">Review adjustments and process salaries</p>
            </div>
        </div>

        <!-- Center: Search and Filter (Compact) -->
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">

            <!-- Search Input - Compact -->
            <div style="position: relative; min-width: 220px;">
                <i data-lucide="search"
                style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #94a3b8; z-index: 10;"></i>
                <input type="text"
                    id="salarySearch"
                    placeholder="Search by name, code..."
                    style="padding: 8px 12px 8px 32px;
                            font-size: 0.8rem;
                            border: 1px solid #e2e8f0;
                            border-radius: 6px;
                            outline: none;
                            width: 100%;
                            background: white;
                            transition: all 0.2s;"
                    onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 2px rgba(59, 130, 246, 0.1)';"
                    onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';" />

                <!-- Clear search button -->
                <button id="clearSalarySearch"
                        style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
                            background: none; border: none; color: #94a3b8; cursor: pointer; display: none; padding: 2px;"
                        onclick="clearSalarySearch()">
                    <i data-lucide="x" style="width: 14px; height: 14px;"></i>
                </button>
            </div>

            <!-- Processing status -->
            <select id="statusFilter"
                    title="Salary processing status"
                    style="padding: 8px 10px; font-size: 0.8rem; border: 1px solid #e2e8f0; border-radius: 6px; min-width: 140px; background: white;">
                <option value="all">All Processing</option>
                <option value="PENDING">Pending</option>
                <option value="PROCESSED">Processed</option>
                <option value="HELD">Held</option>
            </select>

            <!-- Employee active / inactive -->
            <select id="employeeStatusFilter"
                    title="Employee status"
                    style="padding: 8px 10px; font-size: 0.8rem; border: 1px solid #e2e8f0; border-radius: 6px; min-width: 140px; background: white;">
                <option value="all">All Employees</option>
                <option value="71">Active</option>
                <option value="72">Inactive</option>
            </select>

             <!-- Right side: Process button -->
        <div>
            <button data-action="process-bulk"
                    style="padding: 8px 16px;
                        background: #2563eb;
                        color: white;
                        font-weight: 600;
                        border-radius: 6px;
                        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        cursor: pointer;
                        border: none;
                        font-size: 0.8rem;
                        transition: all 0.2s;"
                    onmouseover="this.style.background='#1d4ed8'; this.style.transform='translateY(-1px)';"
                    onmouseout="this.style.background='#2563eb'; this.style.transform='translateY(0)';">
                <span class="process-text">Process Selected</span>
                <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
            </button>
        </div>
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
                        <th style="text-align: center;">Month Days<br><small
                                style="font-weight: normal; color: #94a3b8;">Total Days</small>
                        </th>
                        <th style="text-align: center;">Salary Days<br><small
                                style="font-weight: normal; color: #94a3b8;">Worked
                                Days</small></th>
                        <th style="text-align: right;">Ad-Hoc</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody id="step3TableBody">
                    <tr>
                        <td colspan="9" style="padding:20px;text-align:center;color:#94a3b8;">
                            Click "Process Selected & Next" to load salary data.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- Hold Details Modal - Enhanced UI with Larger Dimensions -->
<div class="modal fade" id="holdDetailsModal" tabindex="-1" aria-labelledby="holdDetailsModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="width: max-content;">
        <div class="modal-content" style="height: 90vh; display: flex; flex-direction: column;">
            <!-- Modal Header - Modern Design -->
            <div class="modal-header" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
                     border-bottom: 1px solid #fca5a5; padding: 1.25rem 1.5rem;">
                <div class="d-flex align-items-center w-100">
                    <div style="position: relative; margin-right: 16px;">
                        <div class="bg-white rounded-circle p-2 shadow-sm"
                            style="border: 2px solid #dc2626; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="alert-triangle" class="text-danger" style="width: 24px; height: 24px;"></i>
                        </div>
                        <div style="position: absolute; top: -6px; right: -6px; width: 22px; height: 22px;
                             background: #dc2626; color: white; border-radius: 50%; display: flex;
                             align-items: center; justify-content: center; font-size: 11px; font-weight: bold;
                             border: 2px solid white;">
                            <span id="hold-count-badge">0</span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="modal-title mb-1" id="holdDetailsModalLabel"
                            style="font-size: 1.25rem; font-weight: 700; color: #1f2937;">
                            Salary Hold Details
                        </h5>
                        <small class="text-muted" id="holdDetailsSubtitle" style="font-size: 0.875rem; color: #6b7280;">
                            Payroll Period: Loading...
                        </small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        style="opacity: 0.7; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'"
                        onmouseout="this.style.opacity='0.7'"></button>
                </div>
            </div>

            <!-- Modal Body - Full Height with Scroll -->
            <div class="modal-body" style="flex: 1; overflow-y: auto; padding: 0;">
                <!-- Loading State -->
                <div id="holdDetailsLoading" class="text-center py-5"
                    style="height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                    <div class="spinner-border text-danger" role="status" style="width: 3.5rem; height: 3.5rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-4" style="font-size: 1rem; color: #6b7280;">Loading hold details...</p>
                    <p class="text-muted" style="font-size: 0.875rem;">Please wait while we fetch the data</p>
                </div>

                <!-- Content Area -->
                <div id="holdDetailsContent" class="d-none" style="height: 100%;">
                    <!-- Summary Cards Row -->
                    {{-- <div class="container-fluid py-4" style="background: #f8fafc; border-bottom: 1px solid #e5e7eb;">
                        <div class="row g-4">
                            <!-- Total Holds Card -->
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc2626;">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle p-2 me-3"
                                                style="background: rgba(220, 38, 38, 0.1);">
                                                <i data-lucide="users" class="text-danger"
                                                    style="width: 20px; height: 20px;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="text-muted mb-1"
                                                    style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                                    Total Holds</h6>
                                                <h4 class="mb-0 text-dark" id="totalHoldsCount">0</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Hold Types Card -->
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f59e0b;">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle p-2 me-3"
                                                style="background: rgba(245, 158, 11, 0.1);">
                                                <i data-lucide="clock" class="text-warning"
                                                    style="width: 20px; height: 20px;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="text-muted mb-1"
                                                    style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                                    Hold Until Release</h6>
                                                <h4 class="mb-0 text-dark" id="holdUntilReleaseCount">0</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Ready to Process Card -->
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #10b981;">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle p-2 me-3"
                                                style="background: rgba(16, 185, 129, 0.1);">
                                                <i data-lucide="check-circle" class="text-success"
                                                    style="width: 20px; height: 20px;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="text-muted mb-1"
                                                    style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                                    Ready to Process</h6>
                                                <h4 class="mb-0 text-dark" id="readyToProcessCount">0</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    <!-- Table Container -->
                    <div class="container-fluid py-4">
                        <div class="card border-0 shadow-sm">
                            <div class="hold-details-content card-header bg-white border-bottom py-3"
                                style="border-bottom: 2px solid #e5e7eb;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0" style="font-weight: 600; color: #1f2937; font-size: 1rem;">
                                        <i data-lucide="list" class="me-2" style="width: 18px; height: 18px;"></i>
                                        Employees on Hold
                                    </h6>
                                </div>
                            </div>

                            <!-- Employee Holds Table -->
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-hover mb-0" id="holdsTable">
                                    <thead class="table-light" style="position: sticky; top: 0; z-index: 1; background-color: inherit;">
                                        <tr>
                                            <th width="60"
                                                style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; padding: 1rem 0.75rem;">
                                                #</th>
                                            <th
                                                style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; padding: 1rem 0.75rem;">
                                                Emp Name</th>
                                                <th
                                                style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; padding: 1rem 0.75rem;">
                                                Emp Code</th>
                                            <th
                                                style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; padding: 1rem 0.75rem;">
                                                Department</th>
                                            <th
                                                style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; padding: 1rem 0.75rem;">
                                                Hold Reason</th>
                                            <th width="160"
                                                style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; padding: 1rem 0.75rem; text-align: center;">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="holdsTableBody" class="hold-details-content" style="font-size: 0.875rem;">
                                        <!-- Data will be loaded here -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Empty State -->
                            <div id="noHoldsMessage" class="text-center py-5 d-none">
                                <div class="mb-4">
                                    <i data-lucide="check-circle" class="text-success"
                                        style="width: 72px; height: 72px; opacity: 0.8;"></i>
                                </div>
                                <h5 class="text-success mb-3" style="font-weight: 600;">No Salary Holds Found</h5>
                                <p class="text-muted mb-4" style="max-width: 400px; margin: 0 auto;">All employees are
                                    ready for payroll processing.</p>
                                <button class="btn btn-success" data-bs-dismiss="modal">
                                    <i data-lucide="check" class="me-2"></i>
                                    Continue Processing
                                </button>
                            </div>

                            <!-- Error State -->
                            <div id="errorMessage" class="text-center py-5 d-none">
                                <div class="mb-4">
                                    <i data-lucide="alert-octagon" class="text-danger"
                                        style="width: 72px; height: 72px; opacity: 0.8;"></i>
                                </div>
                                <h5 class="text-danger mb-3" style="font-weight: 600;">Unable to Load Data</h5>
                                <p class="text-muted mb-4" id="errorText" style="max-width: 400px; margin: 0 auto;">
                                    Failed to load hold details. Please try again.
                                </p>
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-outline-danger" id="retryButton">
                                        <i data-lucide="refresh-cw" class="me-1"></i>
                                        Retry
                                    </button>
                                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        <i data-lucide="x" class="me-1"></i>
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top py-3" style="background: #f9fafb;">
                <div class="d-flex justify-content-between w-100 align-items-center">
                    <div>
                        <span class="text-muted" style="font-size: 0.875rem;">
                            <i data-lucide="info" class="me-1" style="width: 14px; height: 14px;"></i>
                            Review all holds before proceeding
                        </span>
                    </div>
                    <div class="d-flex gap-3">
                        <button type="button" class="btn btn-outline-secondary d-flex align-items-center"
                            data-bs-dismiss="modal">
                            <i data-lucide="x" class="me-1" style="width: 16px; height: 16px;"></i>
                            Close
                        </button>
                        <a href="#" id="processPayrollLink" class="btn btn-primary d-flex align-items-center px-4"
                            style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: none;">
                            <i data-lucide="play" class="me-2" style="width: 16px; height: 16px;"></i>
                            Continue Payroll Processing
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Step 4: Individual Employee View -->
<div data-view="STEP4" style="display: none; animation: slide-in-right 0.5s ease-out;">
    <button data-action="back-to-list"
        style="display: flex; align-items: center; gap: 8px; color: #64748b; background: none; border: none; cursor: pointer; font-size: 0.875rem; font-weight: bold; margin-bottom: 24px; padding: 8px 0;">
        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
        Back to List
    </button>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;" id="employeeName">Sarah Connor</h2>
            <p style="font-size: 0.75rem; color: #64748b;">Payment Breakdown • Verification
                Mode</p>
        </div>
        <div
            style="display: flex; align-items: center; gap: 8px; background: #fef3c7; color: #b45309; padding: 6px 12px; border-radius: 8px; border: 1px solid #fde68a; font-size: 0.75rem; font-weight: bold;">
            <i data-lucide="lock" style="width: 12px; height: 12px;"></i>
            Read Only
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Earnings Card -->
            <div class="glass-card hover-effect">
                <div
                    style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; color: #059669;">
                    <i data-lucide="trending-up" style="width: 18px; height: 18px;"></i>
                    <h3
                        style="font-size: 0.875rem; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em;">
                        Earnings</h3>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <div
                        style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9;">
                        <span style="color: #64748b; font-size: 0.875rem;">Basic Salary</span>
                        <span style="color: #0f172a; font-family: monospace; font-size: 0.875rem;">4,500.00</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9;">
                        <span style="color: #64748b; font-size: 0.875rem;">HRA</span>
                        <span style="color: #0f172a; font-family: monospace; font-size: 0.875rem;">2,250.00</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 12px 0; border-top: 1px solid #e2e8f0; margin-top: 4px;">
                        <span style="color: #0f172a; font-weight: bold;">Total Earnings</span>
                        <span
                            style="color: #2563eb; font-weight: bold; font-family: monospace; font-size: 1.125rem;">7,200.00</span>
                    </div>
                </div>
            </div>

            <!-- Deductions Card -->
            <div class="glass-card hover-effect">
                <div
                    style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; color: #dc2626;">
                    <i data-lucide="arrow-right" style="width: 18px; height: 18px; transform: rotate(45deg);"></i>
                    <h3
                        style="font-size: 0.875rem; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em;">
                        Deductions</h3>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <div
                        style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9;">
                        <span style="color: #64748b; font-size: 0.875rem;">Provident
                            Fund</span>
                        <span style="color: #0f172a; font-family: monospace; font-size: 0.875rem;">1,800.00</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; padding: 12px 0; border-top: 1px solid #e2e8f0; margin-top: 4px;">
                        <span style="color: #0f172a; font-weight: bold;">Total
                            Deductions</span>
                        <span
                            style="color: #2563eb; font-weight: bold; font-family: monospace; font-size: 1.125rem;">1,800.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Payable Card -->
        <div>
            <div class="glass-card hover-effect" style="position: sticky; top: 96px; padding: 20px;">
                <h3
                    style="font-size: 0.875rem; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 16px;">
                    Net Payable</h3>
                <div style="font-size: 2.25rem; font-weight: bold; color: #0f172a; margin-bottom: 16px;">
                    ₹5,400.00</div>
            </div>
        </div>
    </div>
</div>

<!-- Step 5: Reports -->
<div data-view="STEP5" style="display: none; animation: slide-in-right 0.5s ease-out;">
    <!-- Workflow Stepper -->
    <div class="stepper-container">
        @for ($i = 1; $i <= 5; $i++) <div class="stepper-step">
            @if ($i < 5) <div class="stepper-connector {{ $i < 4 ? 'active' : '' }}">
    </div>
    @endif
    <div class="stepper-circle {{ $i <= 4 ? 'active' : '' }}">
        {{ $i }}
    </div>
    <div class="stepper-label {{ $i <= 4 ? 'active' : '' }}">
        @if ($i == 1)
        Approval
        @elseif($i == 2)
        Freeze
        @elseif($i == 3)
        Process
        @elseif($i == 4)
        Verify
        @else
        Finish
        @endif
    </div>
</div>
@endfor
</div>

<!-- Process Info -->
<div
    style="margin-bottom: 24px; background: #dbeafe; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; display: flex; align-items: flex-start; gap: 12px;">
    <div style="padding: 8px; background: rgba(59, 130, 246, 0.1); border-radius: 50%; color: #2563eb;">
        <i data-lucide="info" style="width: 16px; height: 16px;"></i>
    </div>
    <div>
        <h4 style="color: #1e3a8a; font-weight: bold; font-size: 0.875rem; margin: 0;">
            Verification & Compliance</h4>
        <p style="color: #1d4ed8; font-size: 0.75rem; line-height: 1.5; margin-top: 4px; margin-bottom: 0;">
            Final verification. Review the generated reports (Bank Sheet, PF, etc.) before
            committing. Use 'Save as Draft' if you need to return later.</p>
    </div>
</div>

<div style="margin-bottom: 32px; text-align: center;">
    <h2 class="head-text">Verification &
        Compliance</h2>
    <p style="color: #64748b;">Review all reports before finalizing the payroll.</p>
</div>


{{-- Reports Section with Buttons --}}
<div
    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px;">

    <!-- Bank Sheet Report Component -->
    @livewire('components.bank-sheet-report', [
    'payrollId' => $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0),
    'showButton' => true,
    'buttonStyle' => 'font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid
    #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 4px;',
    ])

    <!-- Payroll Register Livewire Component -->
    @livewire('components.payroll-register-report', [
    'payrollId' => $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0),
    'showButton' => true,
    'buttonStyle' => 'font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid
    #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 4px;',
    ])

    <!-- PF/EPF Livewire Component -->
    @livewire('components.p-f-e-p-f-report', [
    'payrollId' => $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0),
    'showButton' => true,
    'buttonStyle' => 'font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid
    #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 4px;',
    ])

    <!-- ESIC Livewire Component -->
    @livewire('components.e-s-i-c-report', [
    'payrollId' => $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0),
    'showButton' => true,
    'buttonStyle' => 'font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid
    #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 4px;',
    ])

    <!-- Letter Head Livewire Component -->
    @livewire('components.letter-head', [
    'payrollId' => $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0),
    'showButton' => true,
    'buttonStyle' => 'font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid
    #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 4px;',
    ])


    @livewire('components.consolidated-payroll-report', [
    'payrollId' => $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? 0),
    'showButton' => true,
    'buttonStyle' => 'font-size: 0.75rem; font-weight: 500; color: #475569; background: #f8fafc; border:
    1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex;
    align-items: center; justify-content: center; gap: 8px; width: 100%;',
    ])

</div>

@php
    $__payslipPayrollId = $payrollPeriod->payroll_id ?? ($payrollPeriod->pp_id ?? null);
@endphp
<div style="width: 100%; max-width: 100%; box-sizing: border-box; margin-bottom: 32px;">
    <div class="glass-card hover-effect"
        style="width: 100%; max-width: 100%; box-sizing: border-box; padding: 14px 18px;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; width: 100%;">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0;">
                <div
                    style="width: 36px; height: 36px; border-radius: 8px; background: #dbeafe;
                           display: flex; align-items: center; justify-content: center;
                           color: #2563eb; flex-shrink: 0;">
                    <i data-lucide="file-text" style="width: 18px; height: 18px;"></i>
                </div>
                <div style="min-width: 0;">
                    <h3 class="head-text" style="font-size: 0.85rem; margin: 0; font-weight: 600; line-height: 1.2;">
                        Employee payslips
                    </h3>
                    <p class="para-text" style="font-size: 0.65rem; color: #64748b; margin: 2px 0 0 0; line-height: 1.3;">
                        Open the full list of processed payslips for this payroll period.
                    </p>
                </div>
            </div>
            <a id="step5-payslip-list-link"
                href="{{ $__payslipPayrollId ? route('payroll.payslip.list', ['payrollId' => $__payslipPayrollId]) : '#' }}"
                style="flex-shrink: 0; margin-left: auto; font-size: 0.65rem; font-weight: 600; color: #fff;
                       background: #2563eb; border: none; padding: 6px 12px; border-radius: 6px;
                       cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 4px;
                       text-decoration: none; white-space: nowrap; box-shadow: 0 1px 2px rgba(37, 99, 235, 0.25);">
                <i data-lucide="eye" style="width: 12px; height: 12px;"></i>
                View all
            </a>
        </div>
    </div>
</div>


<!-- Final Confirmation -->
<div
    style="background: #fef3c7; border: 1px solid #fde68a; padding: 16px; border-radius: 12px; display: flex; flex-direction: column; gap: 12px; margin-bottom: 32px; max-width: 48rem; margin-left: auto; margin-right: auto;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <i data-lucide="alert-triangle" style="color: #b45309; width: 20px; height: 20px;"></i>
        <div>
            <h4 style="color: #92400e; font-weight: bold; font-size: 0.875rem;">Final
                Confirmation</h4>
            <p style="color: #b45309; font-size: 0.75rem;">Ensure all anomalies are
                resolved. This action sends data to accounts.</p>
        </div>
    </div>
    <div style="display: flex; gap: 12px;">
        <button data-action="back-to-processing"
            style="padding: 12px; color: #64748b; font-size: 0.875rem; font-weight: bold; background: none; border: none; cursor: pointer;">Back
            to Processing</button>
        <button data-action="save-draft"
            style="padding: 12px 24px; background: white; border: 1px solid #e2e8f0; color: #475569; font-weight: bold; border-radius: 12px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            Save as Draft
        </button>
        <button data-action="finalize-cycle"
            style="padding: 12px 24px; background: linear-gradient(to right, #059669, #0d9488); color: white; font-weight: bold; border-radius: 12px; border: none; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.2);">
            Finalize & Lock
        </button>
    </div>
</div>

</div>

<!-- Step 6: Success -->
<div data-view="STEP6" style="display: none; animation: fade-in 0.5s ease-out; text-align: center; padding: 48px 16px;">
    <!-- Workflow Stepper -->
    <div class="stepper-container">
        @for ($i = 1; $i <= 5; $i++) <div class="stepper-step">
            @if ($i < 5) <div class="stepper-connector active">
    </div>
    @endif
    <div class="stepper-circle active">
        {{ $i }}
    </div>
    <div class="stepper-label active">
        @if ($i == 1)
        Approval
        @elseif($i == 2)
        Freeze
        @elseif($i == 3)
        Process
        @elseif($i == 4)
        Verify
        @else
        Finish
        @endif
    </div>
</div>
@endfor
</div>

<div
    style="display: inline-flex; padding: 32px; background: #d1fae5; border-radius: 50%; border: 4px solid #a7f3d0; margin-bottom: 24px; animation: pulse 2s infinite;">
    <i data-lucide="check-circle" style="width: 64px; height: 64px; color: #059669;"></i>
</div>
<h2 class="head-text">
    Payroll Finalized Successfully!</h2>
<p style="color: #64748b; max-width: 28rem; margin: 0 auto 32px;">The payroll for October
    2025 has been locked.</p>
<div style="display: flex; justify-content: center; gap: 16px;">
    <button data-action="go-dashboard"
        style="padding: 12px 24px; background: white; color: #475569; font-weight: bold; border-radius: 12px; border: 1px solid #e2e8f0; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
        <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
        Back to Dashboard
    </button>
</div>
</div>


</div>
</div>
</div>
</div>
</div>
</div>


<!-- REVERT PROCESSING MODAL - ADD THIS AT THE END OF YOUR CONTENT SECTION -->
<div id="revert-processing-modal" class="modal-overlay hidden" style="display: none;">
    <div class="modal-container max-w-4xl">
        <!-- YAHAN max-w-4xl KIYA HAI -->
        <div class="modal-header">
            <h3 class="modal-title">Revert Salary Processing</h3>
            <button class="modal-close-btn" data-action="close-modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <div class="modal-alert warning">
                <i data-lucide="refresh-cw"></i>
                <div>
                    <strong>Revert Salary Processing?</strong>
                    <p style="font-size: 0.75rem; margin-top: 0.25rem;">
                        You are going back to the processing stage. Select employees to unprocess (revert to pending).
                    </p>
                </div>
            </div>

            <!-- Search Section -->
            <div style="margin-bottom: 1rem;">
                <div style="position: relative; margin-bottom: 0.75rem;">
                    <i data-lucide="search"
                        style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #94a3b8; z-index: 10;"></i>
                    <input type="text" id="revert-search-input"
                        placeholder="Search employees by name, designation or department..."
                        style="width: 100%; padding: 10px 12px 10px 36px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem;"
                        autocomplete="off">
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="modal-input-label">Processed employees</span>
                    <button class="select-all-btn modal-btn modal-btn-secondary" style="font-size: 0.75rem;"
                        id="revert-select-all-btn">
                        Select All
                    </button>
                </div>
            </div>

            <div id="revert-employee-list" class="modal-employee-list">
                <!-- Employee items will be inserted here -->
            </div>

            <div id="revert-no-results" class="modal-empty-state empty-state" style="display: none;">
                <i data-lucide="search-x" style="width: 3rem; height: 3rem; margin-bottom: 1rem;"></i>
                <p>No employees found matching your search</p>
            </div>
        </div>

        <div class="modal-footer">
            <button class="modal-btn modal-btn-secondary" data-action="close-modal">
                Cancel
            </button>
            <button class="modal-btn modal-btn-primary confirm-revert-btn" disabled>
                <i data-lucide="refresh-cw" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i>
                Revert & Go Back
            </button>
        </div>
    </div>
</div>


<!-- Hold Salary Modal - Card Stack Style -->
<div id="hold-salary-modal" class="modal-overlay hidden">
    <div class="modal-container" style="
        max-width: 440px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.25);
    ">
        <!-- Simple Header -->
        <div style="
            padding: 20px 24px 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        ">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="
                    width: 36px;
                    height: 36px;
                    background: #fee2e2;
                    border-radius: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                ">
                    <i data-lucide="pause" style="width: 18px; height: 18px; color: #dc2626;"></i>
                </div>
                <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0;">Hold Salary</h3>
            </div>
            <button class="modal-close-btn" data-action="close-modal" style="
                width: 32px;
                height: 32px;
                border-radius: 8px;
                border: none;
                background: #f3f4f6;
                color: #6b7280;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
            ">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <!-- Stacked Cards Container -->
        <div class="modal-body" style="padding: 20px 24px;">

            <!-- Card 1: Employee Card -->
            <div id="hold-employee-info" style="display: none;">
                <div style="
                    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
                    border-radius: 14px;
                    padding: 16px;
                    margin-bottom: 16px;
                    border: 1px solid #e5e7eb;
                ">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div id="hold-employee-avatar" style="
                            width: 48px;
                            height: 48px;
                            background: #1e293b;
                            border-radius: 12px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                            font-weight: 600;
                            font-size: 18px;
                        ">E</div>
                        <div>
                            <h4 id="hold-employee-name" style="
                                font-size: 15px;
                                font-weight: 600;
                                color: #111827;
                                margin: 0 0 4px 0;
                            ">Employee Name</h4>
                            <div style="display: flex; gap: 12px;">
                                <span style="font-size: 11px; color: #6b7280; display: flex; align-items: center; gap: 4px;">
                                    <i data-lucide="layers" style="width: 12px; height: 12px;"></i>
                                    <span id="hold-employee-dept">Department</span>
                                </span>
                                <span style="font-size: 11px; color: #6b7280; display: flex; align-items: center; gap: 4px;">
                                    <i data-lucide="user" style="width: 12px; height: 12px;"></i>
                                    <span id="hold-employee-designation">Designation</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Info Card -->
            <div style="
                background: #fff7ed;
                border-radius: 12px;
                padding: 14px;
                margin-bottom: 16px;
                border: 1px solid #fed7aa;
            ">
                <div style="display: flex; gap: 12px;">
                    <div style="
                        width: 28px;
                        height: 28px;
                        background: #1d4ed8;
                        border-radius: 8px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        flex-shrink: 0;
                    ">
                        <i data-lucide="info" style="width: 14px; height: 14px; color: white;"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 13px; font-weight: 600; color: #9a3412; margin: 0 0 2px 0;">Important</h4>
                        <p style="font-size: 11px; color: #1d4ed8; margin: 0; line-height: 1.5;">
                            Holding salary will pause processing. You can release it later from the salary list.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Card 3: Period Selection Card -->
            <div style="
                background: white;
                border-radius: 12px;
                padding: 16px;
                margin-bottom: 16px;
                border: 1px solid #e5e7eb;
                box-shadow: 0 2px 4px 0 rgba(0,0,0,0.02);
            ">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="font-size: 13px; font-weight: 600; color: #374151;">
                        <i data-lucide="calendar" style="width: 14px; height: 14px; margin-right: 6px;"></i>
                        Payroll Periods
                    </span>
                    <span style="font-size: 10px; background: #f3f4f6; padding: 2px 8px; border-radius: 12px; color: #6b7280;">
                        Select multiple
                    </span>
                </div>

                <div id="payroll-periods-dropdown-container">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 16px;">
                        <div class="spinner-border spinner-border-sm"></div>
                        <span style="font-size: 11px; color: #9ca3af;">Loading available periods...</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Reason Card -->
            <div style="
                background: white;
                border-radius: 12px;
                padding: 16px;
                margin-bottom: 16px;
                border: 1px solid #e5e7eb;
                box-shadow: 0 2px 4px 0 rgba(0,0,0,0.02);
            ">
                <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 12px;">
                    <i data-lucide="edit-3" style="width: 14px; height: 14px; color: #6b7280;"></i>
                    <span style="font-size: 13px; font-weight: 600; color: #374151;">Reason for Hold</span>
                    <span style="font-size: 10px; color: #1d4ed8; margin-left: auto;">*Required</span>
                </div>

                <textarea id="hold-reason"
                    placeholder="e.g., Pending documents, Leave without pay, Disciplinary action..."
                    style="
                        width: 100%;
                        padding: 10px 12px;
                        border: 1px solid #e5e7eb;
                        border-radius: 8px;
                        font-size: 12px;
                        min-height: 70px;
                        resize: vertical;
                        background: #f9fafb;
                    "></textarea>
            </div>

            <!-- Card 5: Options Card -->
            <div style="
                background: white;
                border-radius: 12px;
                padding: 16px;
                border: 1px solid #e5e7eb;
                box-shadow: 0 2px 4px 0 rgba(0,0,0,0.02);
            ">
                <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 12px;">
                    <i data-lucide="settings" style="width: 14px; height: 14px; color: #6b7280;"></i>
                    <span style="font-size: 13px; font-weight: 600; color: #374151;">Additional Options</span>
                </div>

                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" id="hold-until-release" style="
                        width: 16px;
                        height: 16px;
                        border-radius: 4px;
                        border: 2px solid #d1d5db;
                    ">
                    <span style="font-size: 12px; color: #374151;">Hold until manually released</span>
                </label>

                <div id="hold-until-release-info" style="
                    font-size: 11px;
                    color: #6b7280;
                    margin-top: 8px;
                    padding: 8px;
                    background: #f3f4f6;
                    border-radius: 6px;
                    display: none;
                ">
                    <i data-lucide="info" style="width: 12px; height: 12px; display: inline-block;"></i>
                    Only one period can be selected with this option
                </div>
            </div>
        </div>

        <!-- Footer with Stacked Buttons -->
        <div style="
            padding: 16px 24px 24px 24px;
            display: flex;
            gap: 10px;
        ">
            <button data-action="close-modal" style="
                flex: 1;
                padding: 10px;
                font-size: 13px;
                font-weight: 500;
                border-radius: 10px;
                background: #f3f4f6;
                color: #4b5563;
                border: 1px solid #e5e7eb;
                cursor: pointer;
            ">
                Cancel
            </button>

            <button id="confirm-hold-btn" disabled style="
                flex: 1;
                padding: 10px;
                font-size: 13px;
                font-weight: 500;
                border-radius: 10px;
                background: #1d4ed8;
                color: white;
                border: none;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                opacity: 0.5;
            ">
                <i data-lucide="pause" style="width: 16px; height: 16px;"></i>
                Hold Salary
            </button>
        </div>
    </div>
</div>

<div id="release-salary-modal" class="modal-overlay hidden" style="display: none;">
    <div class="modal-container max-w-3xl">
        <div class="modal-header">
            <h3 class="modal-title">Release Salary Hold</h3>
            <button class="modal-close-btn" data-action="close-modal">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-alert info">
                <i data-lucide="info"></i>
                <div>
                    <strong>Release Salary Hold</strong>
                    <p style="font-size: 0.75rem; margin-top: 0.25rem;">
                        This will allow salary processing for the selected employee.
                    </p>
                </div>
            </div>

            <!-- Employee Info -->
            <div id="release-employee-info" style="display: none;">
                <div
                    style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 8px; margin-bottom: 16px;">
                    <div
                        style="width: 40px; height: 40px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; color: #475569;">
                        <span id="release-employee-avatar">E</span>
                    </div>
                    <div>
                        <h4 id="release-employee-name" style="margin: 0; font-weight: 600; color: #1e293b;">Employee
                            Name</h4>
                        <p id="release-employee-details" style="margin: 4px 0 0 0; font-size: 0.75rem; color: #64748b;">
                            Department • Designation</p>
                    </div>
                </div>
            </div>

            <!-- Held Periods -->
            <div class="modal-input-group">
                <label class="modal-input-label">Held Payroll Periods</label>
                <div id="held-periods-list"
                    style="max-height: 200px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px;">
                    <!-- Held periods will be loaded here -->
                </div>
            </div>

            <!-- Release Options -->
            <div class="modal-input-group">
                <label class="modal-input-label">Release Options</label>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="radio" name="release_option" value="specific" checked>
                        <span>Release selected periods only</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="radio" name="release_option" value="all_holds">
                        <span>Release all holds (including "hold until release")</span>
                    </label>
                </div>
            </div>

            <!-- Release Notes -->
            <div class="modal-input-group">
                <label class="modal-input-label">Release Notes (Optional)</label>
                <textarea id="release-notes" class="modal-input modal-textarea"
                    placeholder="Enter notes for releasing the salary hold..." rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn modal-btn-secondary" data-action="close-modal">
                Cancel
            </button>
            <button class="modal-btn modal-btn-primary" id="confirm-release-btn">
                <i data-lucide="play" style="width: 16px; height: 16px; margin-right: 8px;"></i>
                Confirm Release
            </button>
        </div>
    </div>
</div>


<!-- Include All Modals -->
@include('admin.payroll.modals.adhoc-adjustment')
@include('admin.payroll.modals.create-cycle')
@include('admin.payroll.modals.hold-reason')
@include('admin.payroll.modals.deferred-action')
@include('admin.payroll.modals.deferred-list')
@include('admin.payroll.modals.release-action')
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script>
    // ============================================
        // GLOBAL VARIABLES
        // ============================================
        let retrievedAttendanceData = {};
        let lateCleared = false;
        let earlyCleared = false;
        let currentPayrollId = null;
        let currentPeriodId = null;
        let currentMonthName = "{{ $monthName }}";

        // Payroll-specific storage keys
        const MAX_AGE_HOURS = 8;

        // ============================================
        // STORAGE MANAGEMENT FUNCTIONS (PAYROLL-SPECIFIC)
        // ============================================
        function getStepStorageKey(payrollId) {
            return `payroll_step_${payrollId}`;
        }

        function getPayrollDataKey(payrollId) {
            return `payroll_data_${payrollId}`;
        }

        function getStepTimestampKey(payrollId) {
            return `payroll_step_timestamp_${payrollId}`;
        }

        function savePayrollStepWithTimestamp(viewName) {
            if (!currentPayrollId) return;

            const stepKey = getStepStorageKey(currentPayrollId);
            const timestampKey = getStepTimestampKey(currentPayrollId);
            const dataKey = getPayrollDataKey(currentPayrollId);

            localStorage.setItem(stepKey, viewName);
            localStorage.setItem(timestampKey, Date.now());

            // Save payroll-specific data
            const payrollData = {
                payrollId: currentPayrollId,
                periodId: currentPeriodId,
                monthName: currentMonthName,
                viewName: viewName,
                restored: new Date().toISOString()
            };

            localStorage.setItem(dataKey, JSON.stringify(payrollData));

            saveToHistory(viewName);
        }

        function clearPayrollStorage(payrollId) {
            if (!payrollId) return;

            const keys = [
                getStepStorageKey(payrollId),
                getPayrollDataKey(payrollId),
                getStepTimestampKey(payrollId)
            ];

            keys.forEach(key => localStorage.removeItem(key));
        }

        function isStepExpired(payrollId) {
            if (!payrollId) return true;

            const timestampKey = getStepTimestampKey(payrollId);
            const timestamp = localStorage.getItem(timestampKey);
            if (!timestamp) return true;

            const ageHours = (Date.now() - parseInt(timestamp)) / (1000 * 60 * 60);
            return ageHours > MAX_AGE_HOURS;
        }

        function getCurrentPayrollFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('period') || null;
        }

        // ============================================
        // DOCUMENT READY
        // ============================================
        $(document).ready(function() {
            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Get current payroll period from URL
            const urlPeriodId = getCurrentPayrollFromURL();

            if (urlPeriodId) {
                currentPeriodId = urlPeriodId;

                // Try to get payroll ID from the start button data
                const startButton = $('[data-action="start-process"]');
                if (startButton.length) {
                    currentPayrollId = startButton.data('payroll-id') || startButton.data('period-id');
                } else {
                    currentPayrollId = currentPeriodId;
                }

                // Check if we have a server-defined step to show
                const serverStep = $('#server-current-step').val();

                if (serverStep && serverStep !== 'DASHBOARD') {
                    // Show the appropriate view based on server step
                    restorePayrollStep(serverStep);
                } else {
                    // Check if we have stored state for this specific payroll
                    if (currentPayrollId) {
                        if (isStepExpired(currentPayrollId)) {
                            clearPayrollStorage(currentPayrollId);
                            showView('DASHBOARD');
                        } else {
                            const stepKey = getStepStorageKey(currentPayrollId);
                            const savedStep = localStorage.getItem(stepKey);

                            if (savedStep) {
                                restorePayrollStep(savedStep);
                            } else {
                                showView('DASHBOARD', true);
                            }
                        }
                    } else {
                        showView('DASHBOARD', true);
                    }
                }
            } else {
                showView('DASHBOARD', true);
            }

            // Initialize event handlers
            initializeEventHandlers();
            initializeModalEvents();

             setTimeout(() => {
                if ($('[data-view="STEP3"]').is(':visible')) {
                    initializeSalarySearch();
                    updateSearchStats();
                }
            }, 500);

        });

        // ============================================
        // RESTORE PAYROLL STEP FUNCTION
        // ============================================
        function restorePayrollStep(viewName) {
            switch (viewName) {
                case 'DASHBOARD':
                    showView('DASHBOARD', true);
                    break;

                case 'STEP1':
                    showView('STEP1', true);
                    if (currentPeriodId) {
                        loadPendingRequestsData(currentPeriodId);
                    }
                    break;

                case 'STEP2':
                    if (currentPayrollId) {
                        loadAttendanceData(currentPayrollId, true);
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

                case 'STEP4':
                    if (currentPayrollId) {
                        showView('STEP4', true);
                    } else {
                        showView('DASHBOARD', true);
                    }
                    break;

                case 'STEP5':
                case 'STEP6':
                    showView(viewName, true);
                    break;

                default:
                    showView('DASHBOARD', true);
            }
        }

        // ============================================
        // VIEW MANAGEMENT
        // ============================================
        function updateStep5PayslipListLink() {
            const $a = $('#step5-payslip-list-link');
            if (!$a.length) {
                return;
            }
            if (currentPayrollId) {
                $a.attr('href', '/payroll/payroll-new/payslip-list/' + currentPayrollId);
            }
        }

        function showView(viewName, restoreMode = false) {
            $('[data-view]').hide();
            $(`[data-view="${viewName}"]`).show();

            if (!restoreMode) {
                savePayrollStepWithTimestamp(viewName);
            }

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            updateStepper(viewName);

            if (viewName === 'STEP5') {
                updateStep5PayslipListLink();
            }

            if (!restoreMode) {
                $('html, body').animate({
                    scrollTop: 0
                }, 300);
            }
        }

        function updateStepper(viewName) {
            let activeStep = 0;

            switch (viewName) {
                case 'DASHBOARD':
                    activeStep = 0;
                    break;
                case 'STEP1':
                    activeStep = 1;
                    break;
                case 'STEP2':
                    activeStep = 2;
                    break;
                case 'STEP3':
                    activeStep = 3;
                    break;
                case 'STEP4':
                    activeStep = 3;
                    break;
                case 'STEP5':
                    activeStep = 4;
                    break;
                case 'STEP6':
                    activeStep = 5;
                    break;
            }

            $('.stepper-circle').removeClass('active');
            $('.stepper-label').removeClass('active');
            $('.stepper-connector').removeClass('active');

            for (let i = 1; i <= activeStep; i++) {
                $(`.stepper-circle:contains("${i}")`).addClass('active');
                $(`.stepper-step:nth-child(${i}) .stepper-label`).addClass('active');
                if (i < activeStep) {
                    $(`.stepper-step:nth-child(${i}) .stepper-connector`).addClass('active');
                }
            }
        }

        // ============================================
        // MODAL FUNCTIONS
        // ============================================
        function initializeModalEvents() {
            $(document).on('click', '[data-action="close-modal"]', function(e) {
                e.preventDefault();
                closeAllModals();
            });

            $(document).on('click', '.modal-overlay', function(e) {
                if (e.target === this) {
                    $(this).removeClass('flex').addClass('hidden');
                }
            });

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAllModals();
                }
            });
        }


        function closeAllModals() {
            $('.modal-overlay').removeClass('flex').addClass('hidden');
        }

        function openModal(modalId) {
            $(`#${modalId}`).removeClass('hidden').addClass('flex');
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        // ============================================
        // SUCCESS OVERLAY FUNCTIONS
        // ============================================
        function showSuccessOverlay(message, subtext, iconType = 'check') {
            const overlay = $('#success-overlay');
            const iconContainer = $('#success-icon-container');
            const iconElement = iconContainer.find('i');

            $('#success-message').text(message);
            $('#success-subtext').text(subtext);

            iconContainer.removeClass('check lock').addClass(iconType);

            if (iconType === 'lock') {
                iconElement.attr('data-lucide', 'lock');
                iconContainer.css({
                    'background': '#2563eb',
                    'border-color': '#93c5fd'
                });
            } else {
                iconElement.attr('data-lucide', 'check-circle');
                iconContainer.css({
                    'background': '#10b981',
                    'border-color': '#a7f3d0'
                });
            }

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            overlay.fadeIn();

            setTimeout(() => {
                overlay.fadeOut();
            }, 2000);
        }


         // ============================================
        // BATCH PROCESSING VARIABLES
        // ============================================
        let freezeProgress = {
            processedEmployees: [],
            resumeIndex: 0,
            isProcessing: false,
            totalRecords: 0,
            batchSize: 50,
            currentBatch: 0,
            totalBatches: 0
        };

        // ============================================
        // MAIN FREEZE ATTENDANCE FUNCTION
        // ============================================
        async function freezeAttendance() {
            // ✅ Check if button is disabled
            const $freezeBtn = $('[data-action="freeze-attendance"]');

            // Check if already processing
            if (freezeProgress.isProcessing) {
                Swal.fire({
                    title: 'Processing in Progress',
                    text: 'Please wait, attendance freeze is already in progress.',
                    icon: 'info'
                });
                return false;
            }

            // ✅ Validate before proceeding
            if (!validateAttendanceTotal()) {
                Swal.fire({
                    title: 'Validation Failed',
                    html: '<div style="text-align: left; padding: 0 20px;"><p>Cannot freeze attendance due to validation errors.</p></div>',
                    icon: 'error'
                });
                return false;
            }

            // Collect attendance data from the table
            const attendanceData = {};
            let hasData = false;
            let rowCount = 0;
            let skippedRows = 0;

            $('#attendanceTable tbody tr').each(function(index) {
                const $row = $(this);
                const $cells = $row.find('td');

                // Skip empty rows or loading rows
                if ($cells.length < 16 || $row.text().includes('Click "Proceed" to load attendance')) {
                    skippedRows++;
                    return;
                }

                // Skip hidden rows (from search filter)
                if ($row.css('display') === 'none') {
                    skippedRows++;
                    return;
                }

                // Get employee code and name
                const empCode = $cells.eq(0).text().trim();
                const empName = $cells.eq(1).text().trim();

                if (empCode && empCode !== '') {
                    hasData = true;
                    rowCount++;

                    // Get all values from table cells
                    const attendanceValues = {
                        payroll_id: currentPayrollId,
                        emp_code: empCode,
                        emp_name: empName,
                        total_days: $cells.eq(2).text().trim() || 0,
                        presentCount: $cells.eq(3).text().trim() || 0,
                        absentCount: $cells.eq(4).text().trim() || 0,
                        weekOffCount: $cells.eq(5).text().trim() || 0,
                        weekOffPresentCount: $cells.eq(6).text().trim() || 0,
                        halfDayCount: $cells.eq(7).text().trim() || 0,
                        leaveCount: $cells.eq(8).text().trim() || 0,
                        holidayCount: $cells.eq(9).text().trim() || 0,
                        UPL: $cells.eq(10).text().trim() || 0,
                        lateCount: $row.find('.late-input').val() || 0,
                        earlyExitCount: $row.find('.early-input').val() || 0,
                        missedPunchCount: $cells.eq(13).text().trim() || 0,
                        overtimeCount: $cells.eq(14).text().trim() || 0,
                        overtimeHours: $cells.eq(14).text().trim() || 0,
                        total: $cells.eq(15).text().trim() || 0
                    };

                    // Use emp_id from data attribute if available
                    const empId = $row.data('emp-id') || empCode;
                    attendanceData[empId] = attendanceValues;
                } else {
                    skippedRows++;
                }
            });

            freezeProgress.totalRecords = rowCount;

            if (!hasData || rowCount === 0) {
                Swal.fire({
                    title: 'No Data',
                    text: `No attendance data found to freeze. Rows: ${rowCount}, Skipped: ${skippedRows}`,
                    icon: 'warning'
                });
                return false;
            }

            // Check if we have previously processed data
            const hasExistingProgress = freezeProgress.processedEmployees.length > 0;

            // YOUR CONFIRMATION HTML DIALOG
            let confirmationHtml = `
                <div style="text-align: left; padding: 0 20px;">
                    <p style="margin-bottom: 15px; font-size: 15px;">You are about to freeze attendance for the employees in this payroll.</p>
                    ${hasExistingProgress ? `
                        <div style="background: #dbeafe; padding: 12px; border-radius: 8px; border-left: 4px solid #2563eb; margin-bottom: 15px;">
                            <strong style="color: #1e40af;">Resume processing</strong>
                            <p style="color: #2563eb; font-size: 14px; margin: 5px 0 0 0;">
                                A previous freeze did not finish. You can resume to complete it.
                            </p>
                        </div>
                    ` : ''}
                    <div style="background: #fef3c7; padding: 12px; border-radius: 8px; border-left: 4px solid #f59e0b; margin-bottom: 15px;">
                        <strong style="color: #92400e;">Important:</strong>
                        <ul style="margin: 8px 0 0 20px; color: #b45309; font-size: 14px;">
                            <li>All late/early counts will be saved</li>
                            <li>Data cannot be modified after freezing</li>
                            <li>Salary calculations will use this data</li>
                        </ul>
                    </div>
                    <div style="background: #d1fae5; padding: 8px; border-radius: 6px; border: 1px solid #a7f3d0;">
                        <p style="color: #065f46; font-size: 14px; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                            All validation checks passed
                        </p>
                    </div>
                </div>
            `;

            // Show confirmation dialog with your HTML
            const confirmResult = await Swal.fire({
                title: hasExistingProgress ? 'Resume Freezing Attendance?' : 'Freeze Attendance?',
                html: confirmationHtml,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: hasExistingProgress ? 'Resume processing' : 'Freeze attendance',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true
            });

            if (!confirmResult.isConfirmed) {
                return false;
            }

            // Show simple loading spinner (NO chunk details)
            Swal.fire({
                title: 'Freezing Attendance',
                html: `
                    <div style="padding: 20px;">
                        <div class="spinner-border text-primary" style="width: 48px; height: 48px;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3" style="color: #64748b;">Please wait while we freeze attendance records...</p>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            });

            try {
                // Convert attendance data to array for sending
                const attendanceArray = Object.values(attendanceData);

                // Process all attendance data in one request (or chunked if needed)
                const response = await $.ajax({
                    url: '/payroll/payroll-new/attendance/freeze-attendance',
                    type: 'POST',
                    data: {
                        payroll_id: currentPayrollId,
                        all_attendance_data: attendanceArray,
                        batch_offset: freezeProgress.resumeIndex,
                        processed_employee_ids: freezeProgress.processedEmployees,
                        batch_size: 50,
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json'
                });

                // Close loading spinner
                Swal.close();

                if (response.success) {
                    if (response.is_complete) {
                        // All done!
                        showSuccessOverlay(
                            'Attendance Frozen Successfully!',
                            'Proceeding to salary processing.',
                            'lock'
                        );

                        // Reset progress
                        freezeProgress = {
                            processedEmployees: [],
                            resumeIndex: 0,
                            isProcessing: false,
                            totalRecords: 0
                        };

                        // Load step 3 data and proceed
                        setTimeout(() => {
                            loadStep3Data();
                            showView('STEP3');
                        }, 2000);
                    } else {
                        // Partial completion - save progress for resume
                        freezeProgress.processedEmployees = response.processed_employee_ids || [];
                        freezeProgress.resumeIndex = response.next_resume_index || 0;

                        Swal.fire({
                            title: '⚠️ Partial Completion',
                            html: `
                                <div style="text-align: left; padding: 0 20px;">
                                    <div style="background: #fef3c7; padding: 12px; border-radius: 8px; border-left: 4px solid #f59e0b; margin-bottom: 15px;">
                                        <strong style="color: #92400e;">Processing Interrupted!</strong>
                                        <p style="color: #b45309; margin: 8px 0 0 0;">
                                            ${response.message || 'The process was interrupted due to server timeout.'}
                                        </p>
                                    </div>

                                    <div style="background: #dbeafe; padding: 12px; border-radius: 8px; border-left: 4px solid #2563eb; margin-bottom: 15px;">
                                        <strong style="color: #1e40af;">Progress</strong>
                                        <p style="margin: 8px 0 0 0; color: #2563eb;">Some rows were saved. You can resume to finish the rest, or continue with the data already frozen.</p>
                                    </div>

                                    <div style="background: #eff6ff; padding: 12px; border-radius: 8px;">
                                        <strong style="color: #1d4ed8;">What would you like to do?</strong>
                                    </div>
                                </div>
                            `,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Resume processing',
                            cancelButtonText: 'Go to Salary Processing',
                            confirmButtonColor: '#2563eb',
                            cancelButtonColor: '#10b981',
                            showDenyButton: true,
                            denyButtonText: 'Cancel',
                            denyButtonColor: '#6b7280'
                        }).then((resumeResult) => {
                            if (resumeResult.isConfirmed) {
                                // Resume from where we left off
                                freezeAttendance();
                            } else if (resumeResult.dismiss === Swal.DismissReason.cancel) {
                                // Go to salary processing with what we have
                                showSuccessOverlay(
                                    'Partial freeze complete',
                                    'Proceeding with available data.',
                                    'check'
                                );

                                // Reset progress
                                freezeProgress = {
                                    processedEmployees: [],
                                    resumeIndex: 0,
                                    isProcessing: false,
                                    totalRecords: 0
                                };

                                setTimeout(() => {
                                    loadStep3Data();
                                    showView('STEP3');
                                }, 2000);
                            }
                        });
                    }
                } else {
                    Swal.fire({
                        title: 'Freeze Failed',
                        text: response.message || 'Failed to freeze attendance',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }

            } catch (error) {
                Swal.close();
                Swal.fire({
                    title: 'Error',
                    text: error.responseJSON?.message || error.statusText || 'Failed to freeze attendance. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }

            return true;
        }

        async function processFreezeBatch(attendanceData, startOffset, processedIds) {
            const BATCH_SIZE = 50;
            const attendanceArray = Object.values(attendanceData);
            const totalEmployees = attendanceArray.length;

            let currentOffset = startOffset;
            let currentProcessedIds = [...processedIds];
            let allSuccess = true;
            let errorMessage = '';
            let lastResponse = null;

            // Show simple loading modal (NO chunk details)
            Swal.fire({
                title: 'Processing Attendance',
                html: `
                    <div style="padding: 30px;">
                        <div class="spinner-border text-primary" style="width: 50px; height: 50px;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3" style="color: #64748b;">Please wait while we freeze attendance records...</p>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            });

            try {
                // Send all data in one request (or you can still use batches but without showing progress)
                const response = await $.ajax({
                    url: '/payroll/payroll-new/attendance/freeze-attendance',
                    type: 'POST',
                    data: {
                        payroll_id: currentPayrollId,
                        all_attendance_data: attendanceArray,
                        batch_offset: currentOffset,
                        processed_employee_ids: currentProcessedIds,
                        batch_size: BATCH_SIZE,
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json'
                });

                Swal.close();
                return response;

            } catch (error) {
                Swal.close();
                throw new Error(error.responseJSON?.message || error.statusText || 'Network error');
            }
        }


        // ============================================
        // PROCESS ALL BATCHES AUTOMATICALLY - FIXED VERSION
        // ============================================
        async function processAllBatches(attendanceData) {
            const BATCH_SIZE = 50;
            const totalEmployees = attendanceData.length;
            const totalBatches = Math.ceil(totalEmployees / BATCH_SIZE);

            let processedEmployeeIds = [];
            let currentOffset = 0;
            let currentBatch = 1;
            let allSuccessful = true;
            let errorMessage = '';

            Swal.fire({
                title: 'Freezing Attendance',
                html: `
                    <div style="padding: 24px; text-align: center;">
                        <p style="color: #64748b; margin: 0 0 12px 0;">Please wait. Do not close this window.</p>
                        <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;"></div>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            });

            for (let batch = 0; batch < totalBatches; batch++) {
                currentBatch = batch + 1;

                try {
                    const response = await $.ajax({
                        url: '/payroll/payroll-new/attendance/freeze-attendance',
                        type: 'POST',
                        data: {
                            payroll_id: currentPayrollId,
                            all_attendance_data: attendanceData,
                            batch_offset: currentOffset,
                            processed_employee_ids: processedEmployeeIds,
                            batch_size: BATCH_SIZE,
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json'
                    });

                    if (response.success) {
                        processedEmployeeIds = response.processed_employee_ids || [];
                        currentOffset = response.next_resume_index || (currentOffset + BATCH_SIZE);

                        if (response.is_complete) {
                            // All done
                            Swal.close();
                            showSuccessOverlay(
                                'Attendance Frozen Successfully!',
                                'Proceeding to salary processing.',
                                'lock'
                            );

                            setTimeout(() => {
                                loadStep3Data();
                                showView('STEP3');
                            }, 2000);
                            return;
                        }

                        // Small delay between batches
                        await new Promise(resolve => setTimeout(resolve, 500));
                    } else {
                        allSuccessful = false;
                        errorMessage = response.message || `Failed at batch ${currentBatch}`;

                        if (response.pending_leave_employees?.length > 0) {
                            errorMessage += `\nPending leave requests for employees: ${response.pending_leave_employees.join(', ')}`;
                        }
                        if (response.pending_miss_punch_employees?.length > 0) {
                            errorMessage += `\nPending miss punch requests for employees: ${response.pending_miss_punch_employees.join(', ')}`;
                        }
                        break;
                    }
                } catch (error) {
                    allSuccessful = false;
                    errorMessage = error.responseJSON?.message || error.statusText || 'Network error';
                    break;
                }
            }

            if (!allSuccessful) {
                Swal.fire({
                    title: 'Freeze Failed',
                    html: `
                        <div style="text-align: left;">
                            <p>${errorMessage}</p>
                            <div style="background: #fee2e2; padding: 12px; border-radius: 8px;">
                                <strong>Recommendation:</strong>
                                <p>Please resolve pending requests and try again.</p>
                            </div>
                        </div>
                    `,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }

        // ============================================
        // PROCESS SINGLE BATCH - IMPROVED VERSION
        // ============================================
        function processSingleBatch(attendanceData, offset, processedIds) {
            console.log('processSingleBatch called with:', {
                offset: offset,
                processedIdsCount: processedIds.length,
                batchSize: freezeProgress.batchSize
            });

            return new Promise((resolve, reject) => {
                let timeoutId;
                let isResolved = false;

                // Set timeout for this batch (3 minutes)
                const batchTimeout = setTimeout(() => {
                    if (!isResolved) {
                        timeoutId = true;
                        reject(new Error('Batch processing timeout. Please try again.'));
                    }
                }, 180000);

                $.ajax({
                    url: '/payroll/payroll-new/attendance/freeze-attendance',
                    type: 'POST',
                    data: {
                        payroll_id: currentPayrollId,
                        all_attendance_data: attendanceData,
                        batch_offset: offset,
                        processed_employee_ids: processedIds,
                        batch_size: freezeProgress.batchSize,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (!isResolved) {
                            clearTimeout(batchTimeout);
                            isResolved = true;
                            console.log('Batch success response:', response);
                            resolve(response);
                        }
                    },
                    error: function(xhr) {
                        if (!isResolved) {
                            clearTimeout(batchTimeout);
                            isResolved = true;
                            let errorMsg = 'Batch processing failed';
                            if (xhr.responseJSON?.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            console.error('AJAX error:', errorMsg, xhr);
                            reject(new Error(errorMsg));
                        }
                    }
                });
            });
        }

        // ============================================
        // SHOW BATCH PROGRESS MODAL
        // ============================================
        function showBatchProgressModal(currentBatch, totalBatches, processedCount, totalCount) {
            Swal.fire({
                title: 'Processing Attendance',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" style="width: 40px; height: 40px;"></div>
                        <p class="text-muted">Please wait. Do not close this window.</p>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                width: '450px'
            });
        }

        // ============================================
        // UPDATE BATCH PROGRESS
        // ============================================
        function updateBatchProgress(currentBatch, totalBatches, processedCount, totalCount) {
            Swal.update({
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" style="width: 40px; height: 40px;"></div>
                        <p class="text-muted">Please wait. Do not close this window.</p>
                    </div>
                `
            });
        }


          // ============================================
        // SHOW ERROR DIALOG
        // ============================================
        function showErrorDialog(errorMessage, processedCount, totalCount) {
            return new Promise((resolve) => {
                Swal.fire({
                    title: 'Processing Error',
                    html: `
                        <div class="alert alert-warning">${errorMessage}</div>
                        <p class="text-muted">Do you want to continue with the remaining records?</p>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Continue',
                    cancelButtonText: 'No, Stop',
                    confirmButtonColor: '#2563eb'
                }).then((result) => {
                    resolve(result.isConfirmed);
                });
            });
        }

        // ============================================
        // RESET FREEZE PROGRESS
        // ============================================
        function resetFreezeProgress() {
            freezeProgress = {
                processedEmployees: [],
                resumeIndex: 0,
                isProcessing: false,
                totalRecords: 0,
                batchSize: 50,
                currentBatch: 0,
                totalBatches: 0
            };
            localStorage.removeItem('freeze_progress');
        }


        // ============================================
        // HELPER FUNCTIONS
        // ============================================
        function delay(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        // Batch freeze attendance function - Chunked version
        async function freezeAttendanceBatch() {
            const $freezeBtn = $('[data-action="freeze-attendance"]');

            if ($freezeBtn.prop('disabled')) {
                Swal.fire({
                    title: 'Cannot Freeze',
                    text: 'Please fix validation errors before freezing attendance.',
                    icon: 'error'
                });
                return false;
            }

            if (!validateAttendanceTotal()) {
                Swal.fire({
                    title: 'Validation Failed',
                    text: 'Cannot freeze attendance due to validation errors.',
                    icon: 'error'
                });
                return false;
            }

            // Collect all attendance data
            const allAttendanceData = [];
            $('#attendanceTable tbody tr').each(function() {
                const $row = $(this);
                const $cells = $row.find('td');

                if ($cells.length < 16 || $row.text().includes('Click "Proceed" to load attendance')) {
                    return;
                }

                if ($row.css('display') === 'none') {
                    return;
                }

                const empCode = $cells.eq(0).text().trim();
                if (empCode && empCode !== '') {
                    allAttendanceData.push({
                        emp_id: $row.data('emp-id') || empCode,
                        emp_code: empCode,
                        emp_name: $cells.eq(1).text().trim(),
                        total_days: $cells.eq(2).text().trim() || 0,
                        presentCount: $cells.eq(3).text().trim() || 0,
                        absentCount: $cells.eq(4).text().trim() || 0,
                        weekOffCount: $cells.eq(5).text().trim() || 0,
                        weekOffPresentCount: $cells.eq(6).text().trim() || 0,
                        halfDayCount: $cells.eq(7).text().trim() || 0,
                        leaveCount: $cells.eq(8).text().trim() || 0,
                        holidayCount: $cells.eq(9).text().trim() || 0,
                        UPL: $cells.eq(10).text().trim() || 0,
                        lateCount: $row.find('.late-input').val() || 0,
                        earlyExitCount: $row.find('.early-input').val() || 0,
                        missedPunchCount: $cells.eq(13).text().trim() || 0,
                        overtimeCount: $cells.eq(14).text().trim() || 0,
                        overtimeHours: $cells.eq(14).text().trim() || 0,
                        total: $cells.eq(15).text().trim() || 0
                    });
                }
            });

            if (allAttendanceData.length === 0) {
                Swal.fire({
                    title: 'No Data',
                    text: 'No attendance data found to freeze.',
                    icon: 'warning'
                });
                return false;
            }

            // Show confirmation
            const confirmResult = await Swal.fire({
                title: 'Freeze Attendance?',
                text: `You are about to freeze attendance of ${allAttendanceData.length} employee(s).`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: `Freeze ${allAttendanceData.length} Employees`,
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626'
            });

            if (!confirmResult.isConfirmed) {
                return false;
            }

            // Show loading spinner
            Swal.fire({
                title: 'Freezing Attendance',
                html: `
                    <div style="padding: 20px;">
                        <div class="spinner-border text-primary" style="width: 48px; height: 48px;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3" style="color: #64748b;">Please wait...</p>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false
            });

            try {
                // Process all chunks
                const CHUNK_SIZE = 25;
                const totalChunks = Math.ceil(allAttendanceData.length / CHUNK_SIZE);
                let allSuccess = true;

                for (let i = 0; i < totalChunks; i++) {
                    const start = i * CHUNK_SIZE;
                    const end = Math.min(start + CHUNK_SIZE, allAttendanceData.length);
                    const chunkData = allAttendanceData.slice(start, end);

                    const response = await $.ajax({
                        url: '/payroll/payroll-new/attendance/freeze-attendance-chunk',
                        type: 'POST',
                        data: JSON.stringify({
                            payroll_id: currentPayrollId,
                            attendance_chunk: chunkData,
                            chunk_number: i + 1,
                            total_chunks: totalChunks,
                            _token: '{{ csrf_token() }}'
                        }),
                        contentType: 'application/json',
                        dataType: 'json'
                    });

                    if (!response.success) {
                        allSuccess = false;
                        break;
                    }

                    // Small delay between chunks
                    await new Promise(resolve => setTimeout(resolve, 200));
                }

                Swal.close();

                if (allSuccess) {
                    showSuccessOverlay(
                        'Attendance Frozen Successfully!',
                        'Attendance frozen. Proceeding to salary processing.',
                        'lock'
                    );

                    setTimeout(() => {
                        loadStep3Data();
                        showView('STEP3');
                    }, 2000);
                } else {
                    Swal.fire({
                        title: 'Freeze Failed',
                        text: 'Some records could not be processed. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }

            } catch (error) {
                Swal.close();
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to freeze attendance. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }

        async function processAttendanceInChunks(attendanceData) {
            const CHUNK_SIZE = 50; // Smaller chunks to avoid max_input_vars
            const totalEmployees = attendanceData.length;
            const totalChunks = Math.ceil(totalEmployees / CHUNK_SIZE);

            let processedCount = 0;
            let failedCount = 0;
            let errors = [];

            Swal.fire({
                title: 'Freezing Attendance',
                html: `
                    <div style="padding: 24px; text-align: center;">
                        <p style="color: #64748b; margin: 0 0 12px 0;">Please wait. Do not close this window.</p>
                        <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;"></div>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            });

            for (let i = 0; i < totalChunks; i++) {
                const start = i * CHUNK_SIZE;
                const end = Math.min(start + CHUNK_SIZE, totalEmployees);
                const chunkData = attendanceData.slice(start, end);
                const chunkNumber = i + 1;

                try {
                    const response = await $.ajax({
                        url: '/payroll/payroll-new/attendance/freeze-attendance-chunk',
                        type: 'POST',
                        data: JSON.stringify({
                            payroll_id: currentPayrollId,
                            attendance_chunk: chunkData,
                            chunk_number: chunkNumber,
                            total_chunks: totalChunks,
                            _token: '{{ csrf_token() }}'
                        }),
                        contentType: 'application/json',
                        dataType: 'json'
                    });

                    if (response.success) {
                        processedCount += response.processed_count || chunkData.length;
                        await new Promise(resolve => setTimeout(resolve, 300));
                    } else {
                        failedCount++;
                        errors.push(response.message || 'Failed');

                        if (response.pending_leave_employees?.length > 0) {
                            errors.push('Pending leave requests must be resolved.');
                        }
                        if (response.pending_miss_punch_employees?.length > 0) {
                            errors.push('Pending miss punch must be resolved.');
                        }

                        if (response.stop_processing) {
                            break;
                        }
                    }
                } catch (error) {
                    failedCount++;
                    const errorMsg = error.responseJSON?.message || error.statusText || 'Network error';
                    errors.push(errorMsg);
                }
            }

            Swal.close();

            if (failedCount === 0) {
                showSuccessOverlay(
                    'Attendance Frozen Successfully!',
                    'Proceeding to salary processing.',
                    'lock'
                );

                setTimeout(() => {
                    loadStep3Data();
                    showView('STEP3');
                }, 2000);
            } else if (processedCount > 0) {
                Swal.fire({
                    title: 'Processing incomplete',
                    html: `
                        <div style="text-align: left;">
                            <p>Some attendance rows could not be frozen. You can retry after fixing the issues below.</p>
                            <div style="background: #fee2e2; padding: 12px; border-radius: 8px; margin-top: 10px; max-height: 200px; overflow-y: auto;">
                                <ul style="margin: 8px 0 0 20px;">
                                    ${errors.map(e => `<li>${e}</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    title: 'Freeze Failed',
                    html: `
                        <div style="text-align: left;">
                            <p>Attendance could not be frozen. Please try again.</p>
                            <div style="background: #fee2e2; padding: 12px; border-radius: 8px;">
                                <ul style="margin: 8px 0 0 20px;">
                                    ${errors.map(e => `<li>${e}</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                    `,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }


        // Process batches sequentially
        async function processBatches(attendanceData) {
            const BATCH_SIZE = 50;
            const totalBatches = Math.ceil(attendanceData.length / BATCH_SIZE);
            let currentBatch = 1;
            let allSuccessful = true;
            let errorMessage = '';

            for (let i = 0; i < attendanceData.length; i += BATCH_SIZE) {
                const batchData = attendanceData.slice(i, i + BATCH_SIZE);

                try {
                    const response = await $.ajax({
                        url: '/payroll/payroll-new/attendance/freeze-attendance',
                        type: 'POST',
                        data: {
                            payroll_id: currentPayrollId,
                            all_attendance_data: batchData,
                            batch_number: currentBatch,
                            total_batches: totalBatches,
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json'
                    });

                    if (!response.success) {
                        allSuccessful = false;
                        errorMessage = response.message || `Failed at batch ${currentBatch}`;

                        // Show pending employees if any
                        if (response.pending_leave_employees?.length > 0) {
                            errorMessage += `\nPending leave requests for employees: ${response.pending_leave_employees.join(', ')}`;
                        }
                        if (response.pending_miss_punch_employees?.length > 0) {
                            errorMessage += `\nPending miss punch requests for employees: ${response.pending_miss_punch_employees.join(', ')}`;
                        }
                        break;
                    }

                    currentBatch++;

                    // Small delay between batches to prevent server overload
                    await new Promise(resolve => setTimeout(resolve, 200));

                } catch (error) {
                    allSuccessful = false;
                    errorMessage = error.responseJSON?.message || error.statusText || 'Network error';
                    break;
                }
            }

            if (allSuccessful) {
                // Success - close progress dialog and show success overlay
                Swal.close();

                showSuccessOverlay(
                    'Attendance Frozen Successfully!',
                    'Proceeding to salary processing.',
                    'lock'
                );

                // Load step 3 data and proceed
                setTimeout(() => {
                    loadStep3Data();
                    showView('STEP3');
                }, 2000);
            } else {
                // Error - show error dialog
                Swal.fire({
                    title: 'Freeze Failed',
                    html: `
                        <div style="text-align: left; padding: 0 20px;">
                            <p style="margin-bottom: 15px;">${errorMessage}</p>
                            <div style="background: #fee2e2; padding: 12px; border-radius: 8px; border-left: 4px solid #dc2626;">
                                <strong style="color: #b91c1c;">Recommendation:</strong>
                                <p style="color: #dc2626; font-size: 14px; margin-top: 8px;">
                                    Please resolve pending requests and try again.
                                </p>
                            </div>
                        </div>
                    `,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc2626'
                });
            }
        }



        // ============================================
        // MAIN EVENT HANDLERS
        // ============================================
        function initializeEventHandlers() {

            // Add CSRF token to all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Step 1 load होने पर automatically check करें
            $(document).on('click', '[data-action="start-process"]', function(e) {
                e.preventDefault();

                const $button = $(this);
                const payrollPeriodId = $button.data('period-id');
                const payrollId = $button.data('payroll-id');

                currentPayrollId = payrollId;
                currentPeriodId = payrollPeriodId;

                // Clear any previous storage for this payroll to start fresh
                clearPayrollStorage(payrollId);

                const originalText = $button.html();
                $button.prop('disabled', true).html(`
                        <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <div class="spinner-border" style="width: 16px; height: 16px; border-width: 2px;"></div>
                            <span>Loading Process...</span>
                        </div>
                    `);

                // Check pending requests
                $.ajax({
                    url: '/payroll/payroll-new/check-pending-requests',
                    type: 'GET',
                    data: {
                        payroll_period: payrollPeriodId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $button.prop('disabled', false).html(originalText);

                        if (response.success) {
                            if (response.data.total_pending > 0) {
                                // Create buttons HTML based on pending requests
                                let buttonsHTML = '';

                                // Missed Punches button
                                if (response.data.missed_punches.count > 0) {
                                    const missPunchUrl = response.data.missed_punch_url ||
                                        `/attendance/missed-punch?payroll_id=${payrollPeriodId}`;
                                    buttonsHTML += `
                                            <a href="${missPunchUrl}" target="_blank"
                                            style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: #f59e0b; color: white; border-radius: 12px; font-size: 0.875rem; font-weight: bold; transition: all 0.2s; cursor: pointer; text-decoration: none; border: 1px solid #d97706;"
                                            onmouseover="this.style.background='#d97706'; this.style.borderColor='#b45309';"
                                            onmouseout="this.style.background='#f59e0b'; this.style.borderColor='#d97706';">
                                                <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                                                Resolve ${response.data.missed_punches.count} Missed Punches
                                            </a>
                                        `;
                                }

                                // Leave Requests button
                                if (response.data.leave_requests.count > 0) {
                                    buttonsHTML += `
                                            <a href="/leave/requests?payroll_id=${payrollPeriodId}" target="_blank"
                                            style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: #ef4444; color: white; border-radius: 12px; font-size: 0.875rem; font-weight: bold; transition: all 0.2s; cursor: pointer; text-decoration: none; border: 1px solid #dc2626;"
                                            onmouseover="this.style.background='#dc2626'; this.style.borderColor='#b91c1c';"
                                            onmouseout="this.style.background='#ef4444'; this.style.borderColor='#dc2626';">
                                                <i data-lucide="calendar" style="width: 18px; height: 18px;"></i>
                                                Resolve ${response.data.leave_requests.count} Leave Requests
                                            </a>
                                        `;
                                }

                                // Overtime Requests button
                                if (response.data.overtime_requests.count > 0) {
                                    buttonsHTML += `
                                            <a href="/overtime/requests?payroll_id=${payrollPeriodId}" target="_blank"
                                            style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: #8b5cf6; color: white; border-radius: 12px; font-size: 0.875rem; font-weight: bold; transition: all 0.2s; cursor: pointer; text-decoration: none; border: 1px solid #7c3aed;"
                                            onmouseover="this.style.background='#7c3aed'; this.style.borderColor='#6d28d9';"
                                            onmouseout="this.style.background='#8b5cf6'; this.style.borderColor='#7c3aed';">
                                                <i data-lucide="clock" style="width: 18px; height: 18px;"></i>
                                                Resolve ${response.data.overtime_requests.count} Overtime
                                            </a>
                                        `;
                                }

                                // Show warning with buttons
                                Swal.fire({
                                    title: 'Pending Requests Detected',
                                    html: `
                                        <div style="text-align: center; padding: 0 15px;">
                                            <!-- Modern Icon with Badge -->
                                            <div style="position: relative; display: inline-block; margin-bottom: 20px;">
                                                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #fef3c7 0%, #fee2e2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid #fecaca;">
                                                    <i data-lucide="alert-triangle" style="width: 36px; height: 36px; color: #dc2626;"></i>
                                                </div>
                                                <div style="position: absolute; top: -8px; right: -8px; width: 36px; height: 36px; background: #dc2626; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.875rem; border: 3px solid white;">
                                                    ${response.data.total_pending}
                                                </div>
                                            </div>

                                            <!-- Title with gradient -->
                                            <h3 style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 12px; font-size: 1.375rem; font-weight: 700;">
                                                Action Required
                                            </h3>

                                            <!-- Subtitle -->
                                            <p style="color: #64748b; margin-bottom: 24px; font-size: 0.875rem; line-height: 1.5;">
                                                Please resolve the pending requests before proceeding to the next step.
                                            </p>

                                            <!-- Stats Cards - Modern Design -->
                                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px;">
                                                ${response.data.missed_punches.count > 0 ? `
                                                        <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 16px 8px; border-radius: 10px; box-shadow: 0 2px 4px rgba(214, 158, 46, 0.1); border: 1px solid #fde68a;">
                                                            <div style="font-size: 1.5rem; font-weight: 800; color: #92400e; margin-bottom: 4px;">${response.data.missed_punches.count}</div>
                                                            <div style="font-size: 0.7rem; color: #92400e; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Missed</div>
                                                            <div style="font-size: 0.6rem; color: #b45309; margin-top: 2px;">Punches</div>
                                                        </div>
                                                    ` : ''}

                                                ${response.data.leave_requests.count > 0 ? `
                                                        <div style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); padding: 16px 8px; border-radius: 10px; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.1); border: 1px solid #fecaca;">
                                                            <div style="font-size: 1.5rem; font-weight: 800; color: #b91c1c; margin-bottom: 4px;">${response.data.leave_requests.count}</div>
                                                            <div style="font-size: 0.7rem; color: #b91c1c; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Leave</div>
                                                            <div style="font-size: 0.6rem; color: #dc2626; margin-top: 2px;">Requests</div>
                                                        </div>
                                                    ` : ''}

                                                ${response.data.overtime_requests.count > 0 ? `
                                                        <div style="background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); padding: 16px 8px; border-radius: 10px; box-shadow: 0 2px 4px rgba(124, 58, 237, 0.1); border: 1px solid #ddd6fe;">
                                                            <div style="font-size: 1.5rem; font-weight: 800; color: #6d28d9; margin-bottom: 4px;">${response.data.overtime_requests.count}</div>
                                                            <div style="font-size: 0.7rem; color: #6d28d9; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Over Time</div>
                                                            <div style="font-size: 0.6rem; color: #7c3aed; margin-top: 2px;">Requests</div>
                                                        </div>
                                                    ` : ''}
                                            </div>

                                            <!-- Action Required Card -->
                                            <div style="background: #f8fafc; padding: 14px; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 16px;">
                                                <div style="display: flex; align-items: flex-start; gap: 10px;">
                                                    <div style="padding: 6px; background: #fee2e2; border-radius: 6px; color: #dc2626; flex-shrink: 0;">
                                                        <i data-lucide="alert-circle" style="width: 16px; height: 16px;"></i>
                                                    </div>
                                                    <div style="text-align: left;">
                                                        <div style="color: #0f172a; font-size: 0.8125rem; font-weight: 600; margin-bottom: 4px;">Next Step Required</div>
                                                        <div style="color: #64748b; font-size: 0.75rem; line-height: 1.4;">
                                                            Resolve all pending requests to proceed with payroll processing.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Quick Action Hint -->
                                            <div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); padding: 10px; border-radius: 8px; border: 1px solid #bfdbfe;">
                                                <p style="color: #1e40af; font-size: 0.75rem; margin: 0; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                                    <i data-lucide="zap" style="width: 14px; height: 14px;"></i>
                                                    Click "View Details" to review and resolve requests
                                                </p>
                                            </div>
                                        </div>
                                    `,
                                    icon: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'View Details & Resolve',
                                    confirmButtonColor: '#3b82f6',
                                    confirmButtonBorder: 'none',
                                    showCancelButton: false,
                                    width: '440px',
                                    padding: '1.75rem',
                                    background: '#ffffff',
                                    backdrop: 'rgba(0, 0, 0, 0.1)',
                                    customClass: {
                                        popup: 'modern-modal-popup',
                                        title: 'modern-modal-title',
                                        htmlContainer: 'modern-modal-html'
                                    },
                                    didOpen: () => {
                                        if (typeof lucide !== 'undefined') {
                                            lucide.createIcons();
                                        }
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        showView('STEP1');

                                        // ✅ YEH NAYA LINE ADD KARO:
                                        setTimeout(() => {
                                            if (currentPeriodId) {
                                                loadPendingRequestsData(
                                                currentPeriodId);
                                            }
                                        }, 300);
                                    }
                                });
                            } else {
                                // No pending requests, proceed directly to Step 1
                                showView('STEP1');
                            }
                        }
                    },
                    error: function() {
                        $button.prop('disabled', false).html(originalText);
                        // If API fails, still show Step 1
                        showView('STEP1');
                    }
                });
            });

            // Step 1 -> Step 2 (Fixed Version)
            $(document).on('click', '[data-action="go-step2"]', function(e) {
                e.preventDefault();

                const $button = $(this);
                const payrollPeriodId = $button.data('period-id');

                // First check if there are any pending requests
                $.ajax({
                    url: '/payroll/payroll-new/check-pending-requests',
                    type: 'GET',
                    data: {
                        payroll_period: payrollPeriodId,
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: () => {
                        $button.prop('disabled', true).html(`
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div class="spinner-border" style="width: 16px; height: 16px; border-width: 2px;"></div>
                                    <span>Checking Pending Requests...</span>
                                </div>
                            `);
                    },
                    success: function(response) {
                        $button.prop('disabled', false).html('Proceed to Freeze Grid');

                        if (response.success) {
                            if (response.data.total_pending > 0) {
                                // Show error and redirect to Miss Punch Approval
                                Swal.fire({
                                    title: 'Cannot Proceed',
                                    html: `
                                            <div style="text-align: left; padding: 0 20px;">
                                                <p style="margin-bottom: 15px;">There are still <strong>${response.data.total_pending}</strong> pending requests that need to be resolved.</p>
                                                <div style="background: #fee2e2; padding: 12px; border-radius: 8px; border-left: 4px solid #dc2626;">
                                                    <strong style="color: #b91c1c;">Please resolve these first:</strong>
                                                    <ul style="margin: 8px 0 0 20px; color: #dc2626; font-size: 14px;">
                                                        ${response.data.missed_punches > 0 ? `<li><a href="/attendance/missed-punch?payroll_id=${payrollPeriodId}" target="_blank" style="color: #dc2626; text-decoration: underline;">Missed Punches: ${response.data.missed_punches}</a></li>` : ''}
                                                        ${response.data.leave_requests > 0 ? `<li><a href="/leave/requests?payroll_id=${payrollPeriodId}" target="_blank" style="color: #dc2626; text-decoration: underline;">Leave Requests: ${response.data.leave_requests}</a></li>` : ''}
                                                        ${response.data.overtime_requests > 0 ? `<li><a href="/overtime/requests?payroll_id=${payrollPeriodId}" target="_blank" style="color: #dc2626; text-decoration: underline;">Overtime Requests: ${response.data.overtime_requests}</a></li>` : ''}
                                                    </ul>
                                                </div>
                                            </div>
                                        `,
                                    icon: 'error',
                                    confirmButtonText: 'Go to Miss Punch Approval',
                                    confirmButtonColor: '#f59e0b'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.open('/attendance/missed-punch?payroll_id=' +
                                            payrollPeriodId, '_blank');
                                    }
                                });
                            } else {
                                // No pending requests, proceed to Step 2
                                currentPayrollId = payrollPeriodId;
                                currentPeriodId = payrollPeriodId;
                                loadAttendanceData(payrollPeriodId);
                            }
                        }
                    },
                    error: function() {
                        $button.prop('disabled', false).html('Proceed to Freeze Grid');
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to check pending requests. Please try again.',
                            icon: 'error'
                        });
                    }
                });
            });

            // Check pending requests when Step 1 loads
            function onStep1Load() {
                if (currentPeriodId) {
                    $.ajax({
                        url: '/payroll/payroll-new/check-pending-requests',
                        type: 'GET',
                        data: {
                            payroll_period: currentPeriodId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Update the cards with live data
                                updatePendingRequestsCards(response.data);
                            }
                        }
                    });
                }
            }

            // Function to update pending requests cards
            function updatePendingRequestsCards(data) {
                // Update Missed Punches card
                if (data.missed_punches > 0) {
                    $('#missed-punches-count').text(data.missed_punches + ' Pending');
                    $('#missed-punches-example').html(`
                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                                <span class="para-text">${data.missed_punches} pending missed punches</span>
                                <span style="color: #d97706; font-weight: bold;">${data.missed_punches}</span>
                            </div>
                        `);

                    $('#resolve-missed-punches-btn')
                        .html(`Resolve ${data.missed_punches} Missed Punches`)
                        .prop('disabled', false)
                        .off('click')
                        .on('click', function() {
                            window.open('/attendance/missed-punch?payroll_id=' + currentPeriodId, '_blank');
                        });
                }

                // Similar updates for Leave and Overtime cards
                // ...
            }

            // Step 1 show होने पर check करें
            function showView(viewName, restoreMode = false) {
                $('[data-view]').hide();
                $(`[data-view="${viewName}"]`).show();

                if (!restoreMode) {
                    savePayrollStepWithTimestamp(viewName);
                }

                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                updateStepper(viewName);

                if (viewName === 'STEP5') {
                    updateStep5PayslipListLink();
                }

                if (!restoreMode) {
                    $('html, body').animate({
                        scrollTop: 0
                    }, 300);
                }

                // Step 1 load होने पर pending requests check करें
                if (viewName === 'STEP1') {
                    onStep1Load();
                }
            }

            // Back to Dashboard from STEP1
            $(document).on('click', '[data-action="back-to-dashboard"]', function(e) {
                e.preventDefault();
                if (currentPayrollId) {
                    clearPayrollStorage(currentPayrollId);
                }
                showView('DASHBOARD');
            });

            // Step 1 -> Step 2
            $(document).on('click', '[data-action="go-step2"]', function(e) {
                e.preventDefault();
                const payrollId = $(this).data('period-id');
                currentPayrollId = payrollId;
                loadAttendanceData(payrollId);
            });

            // Step 2 -> Step 1 (Back to Checklist)
            $(document).on('click', '[data-action="back-to-checklist"]', function(e) {
                e.preventDefault();
                showView('STEP1');
            });

            // Step 2 -> Step 3 (Freeze Attendance) - UPDATED TO CALL FREEZE FUNCTION
            $(document).on('click', '[data-action="freeze-attendance"]', function(e) {
                e.preventDefault();
                freezeAttendanceBatch();
            });

            // Step 3 -> Step 2 (Revert Freeze) - UPDATED WITH API CALL
            $(document).on('click', '[data-action="revert-freeze"]', function(e) {
                e.preventDefault();

                // Check if payroll ID exists
                if (!currentPayrollId) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Payroll ID not found. Please start the process again.',
                        icon: 'error'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Unfreeze Attendance?',
                    html: `
                        <div style="text-align: left; padding: 0 20px;">
                            <p style="margin-bottom: 15px;">You are about to <strong>unfreeze attendance records</strong> for this payroll period.</p>

                            <div style="background: #fef3c7; padding: 12px; border-radius: 8px; border-left: 4px solid #f59e0b; margin-bottom: 15px;">
                                <strong style="color: #92400e;">⚠️ Important:</strong>
                                <ul style="margin: 8px 0 0 20px; color: #b45309; font-size: 14px;">
                                    <li>Attendance records will be unlocked for editing</li>
                                    <li>Changes may affect calculated salaries</li>
                                    <li>You may need to reprocess salaries after editing</li>
                                </ul>
                            </div>

                            <div style="background: #dbeafe; padding: 12px; border-radius: 8px; border-left: 4px solid #2563eb;">
                                <strong style="color: #1d4ed8;">Note:</strong>
                                <p style="color: #3b82f6; font-size: 14px; margin: 5px 0 0 0;">
                                    After unfreezing, you'll be redirected back to Attendance (Step 2) where you can edit the records.
                                </p>
                            </div>
                        </div>
                        `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Unfreeze Now',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#f59e0b',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return new Promise((resolve, reject) => {
                            $.ajax({
                                url: `/payroll/payroll-new/attendance/unfreeze/${currentPayrollId}`,
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    resolve(response);
                                },
                                error: function(xhr) {
                                    let errorMsg = 'Failed to unfreeze attendance';
                                    if (xhr.responseJSON && xhr.responseJSON
                                        .message) {
                                        errorMsg = xhr.responseJSON.message;
                                    } else if (xhr.status === 404) {
                                        errorMsg =
                                            'Unfreeze route not found. Please check the URL.';
                                    }
                                    reject(new Error(errorMsg));
                                }
                            });
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        const response = result.value;

                        if (response.success) {
                            showSuccessOverlay(
                                'Attendance Unfrozen Successfully!',
                                response.message || 'You can now edit attendance records',
                                'check'
                            );

                            setTimeout(() => {
                                // Reload attendance data and go back to step 2
                                loadAttendanceData(currentPayrollId);
                                showView('STEP2');

                                // Clear step 3 storage since we're going back
                                clearPayrollStorage(currentPayrollId);
                            }, 2000);
                        } else {
                            Swal.fire({
                                title: 'Unfreeze Failed',
                                text: response.message || 'Failed to unfreeze attendance',
                                icon: 'error'
                            });
                        }
                    }
                }).catch((error) => {
                    Swal.fire({
                        title: 'Error',
                        html: `
                            <div style="text-align: left;">
                                <p><strong>${error.message || 'Failed to unfreeze attendance'}</strong></p>
                                <p style="color: #64748b; font-size: 14px; margin-top: 10px;">
                                    <strong>URL Attempted:</strong><br>
                                    <code>/payroll-new/attendance/unfreeze/${currentPayrollId}</code>
                                </p>
                                <p style="color: #dc2626; font-size: 12px; margin-top: 10px;">
                                    Please verify the route exists in your routes file.
                                </p>
                            </div>
                            `,
                        icon: 'error'
                    });
                });
            });

            // Step 3 -> Step 4 (Back to List)
            $(document).on('click', '[data-action="back-to-list"]', function(e) {
                e.preventDefault();
                showView('STEP3');
            });

            // Step 5 -> Step 3 (Back to Processing with Revert)
            $(document).on('click', '[data-action="back-to-processing"]', function(e) {
                e.preventDefault();

                const $button = $(this);
                const originalText = $button.html();
                $button.prop('disabled', true).html(`
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div class="spinner-border" style="width: 16px; height: 16px; border-width: 2px;"></div>
                                <span>Loading...</span>
                            </div>
                        `);

                $.ajax({
                    url: '/payroll/payroll-new/get-processed-employees',
                    type: 'GET',
                    data: {
                        payroll_id: currentPayrollId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $button.prop('disabled', false).html(originalText);

                        if (response.success && response.processed_employees && response
                            .processed_employees.length > 0) {
                            showRevertProcessingModal(response.processed_employees);
                        } else {
                            Swal.fire({
                                title: 'No Processed Salaries',
                                html: `
                                            <div style="text-align: center; padding: 20px;">
                                                <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                                                    <i data-lucide="check-circle" style="width: 40px; height: 40px; color: #64748b;"></i>
                                                </div>
                                                <h3 style="color: #0f172a; margin-bottom: 10px;">No Processed Employees</h3>
                                                <p style="color: #64748b;">
                                                    All employees are already in pending state.<br>
                                                    You can proceed to processing step.
                                                </p>
                                            </div>
                                        `,
                                icon: false,
                                showCancelButton: true,
                                confirmButtonText: 'Go to Processing',
                                cancelButtonText: 'Stay Here',
                                confirmButtonColor: '#2563eb'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    showView('STEP3');
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $button.prop('disabled', false).html(originalText);

                        console.error('Failed to load processed employees:', error);

                        Swal.fire({
                            title: 'Failed to Load Data',
                            text: 'Could not load processed employees. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });


            // Step 6 -> Dashboard (with storage clear)
            $(document).on('click', '[data-action="go-dashboard"]', function(e) {
                e.preventDefault();
                if (currentPayrollId) {
                    clearPayrollStorage(currentPayrollId);
                }
                showView('DASHBOARD');
            });

            // Also clear storage when clicking breadcrumb dashboard link
            $(document).on('click', 'a[href*="/dashboard"]', function(e) {
                if (currentPayrollId) {
                    clearPayrollStorage(currentPayrollId);
                }
            });

            // Clear storage when navigating to a different payroll period
            $(document).on('click', 'a[href*="payroll-new-process"]', function(e) {
                // Extract period ID from href if possible
                const href = $(this).attr('href');
                const match = href.match(/period=(\d+)/);
                if (match && match[1]) {
                    const newPeriodId = match[1];
                    if (currentPayrollId && currentPayrollId != newPeriodId) {
                        clearPayrollStorage(currentPayrollId);
                    }
                }
            });



            // ============================================
            // PROCESS BULK SALARIES - MAIN FUNCTIONALITY
            // ============================================
            // ============================================
            $(document).on('click', '[data-action="process-bulk"]', function(e) {
                e.preventDefault();

                const selectedEmpIds = [];
                getVisibleStep3Checkboxes().filter(':checked:not(:disabled)').each(function() {
                    selectedEmpIds.push($(this).data('emp-id'));
                });

                const selectedCount = selectedEmpIds.length;

                if (selectedCount === 0) {
                    Swal.fire({
                        title: 'No Employees Selected',
                        text: 'Please select at least one employee to process.',
                        icon: 'warning'
                    });
                    return;
                }

                 // Show confirmation dialog
                Swal.fire({
                    title: 'Process Salaries?',
                    html: `
                        <div style="text-align: left;">
                            <div style="background: #eff6ff; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #2563eb;">
                                <p style="margin: 0 0 10px 0; color: #1e40af; font-weight: 500;">
                                    You are about to process salaries for <strong>${selectedCount} employee(s)</strong>
                                </p>
                                <p style="margin: 0; font-size: 13px; color: #4b5563;">
                                    Processing will happen in small chunks to ensure stability.
                                </p>
                            </div>
                            <div style="padding: 10px; background: #fef3c7; border-radius: 6px; border-left: 3px solid #f59e0b; font-size: 13px; color: #92400e;">
                                <strong>Note:</strong> You'll be redirected to Verification after all chunks complete
                            </div>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Process Now',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Call the chunked version instead of batch
                        processSalariesInChunks(selectedEmpIds);
                    }
                });
            });

            // Unprocess All
            $(document).on('click', '[data-action="unprocess-all"]', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Reset All Salaries?',
                    text: 'This will reset all processed salaries back to pending state.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Reset All',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        showSuccessOverlay('All Salaries Reset',
                            'All salaries have been reset to pending state', 'check');

                        console.log('Resetting all salaries');
                    }
                });
            });

            // Save Draft
            $(document).on('click', '[data-action="save-draft"]', function(e) {
                e.preventDefault();

                // Get current month name from your data
                const currentMonthName = "{{ $monthName }}"; // या आपके variable

                Swal.fire({
                    title: 'Save as Draft?',
                    html: `
                            <div style="text-align: left; padding: 0 20px;">
                                <p style="margin-bottom: 15px; font-size: 15px; color: #374151;">
                                    You are about to save the payroll for <strong style="color: #1e40af;">${currentMonthName}</strong> as draft.
                                </p>

                                <div style="background: #dbeafe; padding: 12px; border-radius: 8px; border-left: 4px solid #2563eb; margin-bottom: 15px;">
                                    <strong style="color: #1d4ed8; display: block; margin-bottom: 8px;">What will happen:</strong>
                                    <ul style="margin: 0 0 0 20px; color: #3b82f6; font-size: 14px; line-height: 1.6;">
                                        <li>Payroll progress will be saved as draft</li>
                                        <li>You can continue editing later</li>
                                        <li>Data will not be locked</li>
                                        <li>You will be redirected to Payroll Cycles</li>
                                    </ul>
                                </div>

                                <div style="background: #fef3c7; padding: 12px; border-radius: 8px; border-left: 4px solid #f59e0b;">
                                    <strong style="color: #92400e; display: block; margin-bottom: 5px;">⚠️ Important:</strong>
                                    <p style="color: #b45309; font-size: 14px; margin: 0;">
                                        Once saved, you can resume from Payroll Cycles anytime.
                                    </p>
                                </div>

                                <p style="color: #6b7280; font-size: 14px; margin-top: 12px; font-weight: 500;">
                                    This action can be undone by resuming the draft.
                                </p>
                            </div>
                            `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Save Draft',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                    width: '500px'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Save draft logic यहाँ
                        // ...

                        // Show your custom success overlay
                        showSuccessOverlay('Draft Saved', 'Redirecting to payroll cycles...', 'check');

                        // 2 सेकंड के बाद redirect
                        setTimeout(() => {
                            window.location.href = '/payroll/payroll-cycles';
                        }, 2000);
                    }
                });
            });

            // Finalize Cycle
            $(document).on('click', '[data-action="finalize-cycle"]', function(e) {
                e.preventDefault();

                if (!currentPayrollId || !currentPeriodId) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Payroll information missing. Please refresh the page.',
                        icon: 'error'
                    });
                    return;
                }

                // Direct confirmation without type selection
                Swal.fire({
                    title: 'Finalize Payroll?',
                    html: `
                        <div style="text-align: left; padding: 0 20px;">
                            <p style="margin-bottom: 15px; font-size: 16px; color: #374151;">
                                You are about to finalize the payroll for <strong style="color: #2563eb;">${currentMonthName}</strong>.
                            </p>

                            <div style="background: #dbeafe; padding: 15px; border-radius: 8px; border-left: 4px solid #2563eb; margin-bottom: 15px;">
                                <strong style="color: #1d4ed8; display: block; margin-bottom: 8px;">What will happen:</strong>
                                <ul style="margin: 0 0 0 20px; color: #3b82f6; font-size: 14px;">
                                    <li>Payroll status will be marked as <strong>PAYROLL_LOCKED</strong></li>
                                    <li>All data will be permanently locked</li>
                                    <li>No further changes can be made</li>
                                    <li>You will be redirected to Payroll Cycles</li>
                                </ul>
                            </div>

                            <div style="background: #fee2e2; padding: 15px; border-radius: 8px; border-left: 4px solid #dc2626;">
                                <strong style="color: #b91c1c; display: block; margin-bottom: 8px;">⚠️ IMPORTANT WARNING:</strong>
                                <p style="color: #b91c1c; font-size: 14px; margin: 5px 0 0 0; line-height: 1.4;">
                                    <strong>This action is irreversible!</strong> Once locked, you cannot:
                                </p>
                                <ul style="margin: 8px 0 0 20px; color: #b91c1c; font-size: 14px;">
                                    <li>Edit or modify any payroll data</li>
                                    <li>Process or reprocess salaries</li>
                                    <li>Unfreeze attendance</li>
                                    <li>Make any changes to this payroll period</li>
                                </ul>
                            </div>

                            <p style="color: #dc2626; font-weight: bold; margin-top: 15px; font-size: 16px; text-align: center;">
                                Are you sure you want to proceed?
                            </p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Finalize & Lock Now',
                    cancelButtonText: 'Cancel, I\'m Not Sure',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return new Promise((resolve, reject) => {
                            $.ajax({
                                url: '/payroll/payroll-new/finalize-payroll',
                                type: 'POST',
                                data: {
                                    payroll_id: currentPayrollId,
                                    period_id: currentPeriodId,
                                    finalize_type: 'lock',  // Always send 'lock' for finalize
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    resolve(response);
                                },
                                error: function(xhr) {
                                    let errorMsg = 'Failed to finalize payroll';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMsg = xhr.responseJSON.message;
                                    }
                                    reject(new Error(errorMsg));
                                }
                            });
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            const response = result.value;

                            if (response.success) {
                                // Swal.fire success overlay with progress bar
                                Swal.fire({
                                    title: 'Payroll Locked Successfully!',
                                    html: `
                                        <div style="text-align: left; padding: 10px;">
                                            <div style="text-align: center; margin-bottom: 20px;">
                                                <div style="font-size: 60px; color: #10b981; margin-bottom: 15px;">
                                                    🔒
                                                </div>
                                            </div>

                                            <p style="margin-bottom: 15px; font-size: 16px; color: #374151;">
                                                Payroll for <strong style="color: #2563eb;">${currentMonthName}</strong> has been permanently locked and finalized.
                                            </p>

                                            <div style="background: #fee2e2; padding: 15px; border-radius: 8px; border-left: 4px solid #dc2626; margin: 20px 0;">
                                                <strong style="color: #dc2626; display: block; margin-bottom: 5px;">⚠️ No further changes can be made.</strong>
                                            </div>

                                            <p style="color: #6b7280; margin-bottom: 25px;">
                                                Redirecting to Payroll Cycles...
                                            </p>

                                            <div style="background: #e5e7eb; height: 6px; border-radius: 3px; overflow: hidden; margin-top: 20px;">
                                                <div id="successProgressBar" style="height: 100%; background: linear-gradient(90deg, #10b981 0%, #34d399 100%); width: 0%; transition: width 3s linear;"></div>
                                            </div>

                                            <p style="text-align: center; color: #9ca3af; font-size: 14px; margin-top: 10px;">
                                                Please wait while we redirect you...
                                            </p>
                                        </div>
                                    `,
                                    icon: 'success',
                                    showConfirmButton: false,
                                    showCancelButton: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    width: '500px',
                                    padding: '25px',
                                    didOpen: () => {
                                        // Start progress bar animation
                                        setTimeout(() => {
                                            const progressBar = document.getElementById('successProgressBar');
                                            if (progressBar) {
                                                progressBar.style.width = '100%';
                                            }
                                        }, 100);
                                    }
                                });

                                // Clear local storage
                                if (currentPayrollId) {
                                    clearPayrollStorage(currentPayrollId);
                                }

                                // Close SweetAlert and redirect after 3 seconds
                                setTimeout(() => {
                                    Swal.close();
                                    window.location.href = '/payroll/payroll-cycles';
                                }, 3000);
                            }  else {
                            Swal.fire({
                                title: 'Finalization Failed',
                                html: `<div style="text-align: left; padding: 10px;">
                                        <p style="margin-bottom: 10px;">${response.message || 'Failed to finalize payroll'}</p>
                                        ${response.current_status ? `<p><strong>Current Status:</strong> ${response.current_status}</p>` : ''}
                                    </div>`,
                                icon: 'error'
                            });
                        }
                    }
                }).catch((error) => {
                    Swal.fire({
                        title: 'Error',
                        text: error.message || 'Failed to finalize payroll. Please try again.',
                        icon: 'error'
                    });
                });
            });

            // Checkbox change event to update button text
            $(document).on('change', '.employee-checkbox', function() {
                updateProcessButtonText();
            });

            // Clean up old storage when leaving the page
            $(window).on('beforeunload', function() {
                // Clean up storage older than 1 day for all payrolls except current
                const oneDayAgo = Date.now() - (24 * 60 * 60 * 1000);

                for (let i = 0; i < localStorage.length; i++) {
                    const key = localStorage.key(i);

                    if (key.startsWith('payroll_step_')) {
                        const payrollId = key.replace('payroll_step_', '');

                        if (payrollId !== currentPayrollId?.toString()) {
                            const timestampKey = `payroll_step_timestamp_${payrollId}`;
                            const timestamp = localStorage.getItem(timestampKey);

                            if (timestamp && (Date.now() - parseInt(timestamp)) > oneDayAgo) {
                                clearPayrollStorage(payrollId);
                            }
                        }
                    }
                }
            });
        }

        async function processSalariesInChunksPreserveFunctionality(employeeIds) {
            const CHUNK_SIZE = 15; // Process 15 employees at a time
            const totalEmployees = employeeIds.length;
            const totalChunks = Math.ceil(totalEmployees / CHUNK_SIZE);

            let allProcessedCount = 0;
            let currentPayrollStatus = null;

            Swal.fire({
                title: 'Processing Salaries',
                html: `
                    <div style="padding: 24px; text-align: center;">
                        <p style="color: #64748b; margin: 0 0 12px 0;">Please wait. Do not close this window.</p>
                        <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;"></div>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            });

            for (let i = 0; i < totalChunks; i++) {
                const start = i * CHUNK_SIZE;
                const end = Math.min(start + CHUNK_SIZE, totalEmployees);
                const chunkEmployeeIds = employeeIds.slice(start, end);
                const chunkNumber = i + 1;

                try {
                    const formData = new FormData();
                    formData.append('pp_id', currentPayrollId);
                    formData.append('_token', '{{ csrf_token() }}');

                    chunkEmployeeIds.forEach(id => {
                        formData.append('selected_employees[]', id);
                    });

                    const response = await $.ajax({
                        url: '/payroll/payroll-new/process-salaries-chunk',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json'
                    });

                    if (response.success) {
                        allProcessedCount += response.processed_count || chunkEmployeeIds.length;
                        currentPayrollStatus = response.status;

                        if (response.is_last_chunk) {
                            Swal.close();

                            Swal.fire({
                                title: '',
                                html: `
                                    <div style="text-align: center; padding: 20px 15px;">
                                        <div style="width: 60px; height: 60px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto; animation: successScale 0.4s ease-out;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </div>
                                        <h4 style="color: #065f46; margin: 0 0 5px 0; font-size: 18px;">Complete</h4>
                                        <p style="color: #4b5563; font-size: 13px; margin: 0;">${response.message || 'Salaries processed successfully.'}</p>
                                    </div>
                                    <style>
                                        @keyframes successScale {
                                            0% { transform: scale(0); opacity: 0; }
                                            70% { transform: scale(1.1); opacity: 1; }
                                            100% { transform: scale(1); opacity: 1; }
                                        }
                                    </style>
                                `,
                                showConfirmButton: false,
                                timer: 1200,
                                timerProgressBar: true,
                                width: '380px'
                            });

                            setTimeout(() => {
                                showView('STEP5');
                                loadVerificationData(currentPayrollId);
                            }, 1200);
                            return;
                        }

                        await new Promise(resolve => setTimeout(resolve, 500));
                    } else {
                        Swal.close();
                        Swal.fire({
                            title: 'Processing Notes',
                            html: `
                                <div style="text-align: left; font-size: 13px;">
                                    <div style="padding: 12px; background: #fef3c7; border-radius: 6px; margin-bottom: 10px; border-left: 3px solid #f59e0b; color: #92400e;">
                                        ${response.message || 'Processing completed with notes'}
                                    </div>
                                    ${response.details ? `
                                        <div style="padding: 10px; background: #f1f5f9; border-radius: 6px;">
                                            <strong style="color: #374151;">Details:</strong>
                                            <p style="color: #4b5563; margin: 5px 0 0 0;">${response.details}</p>
                                        </div>
                                    ` : ''}
                                </div>
                            `,
                            icon: 'warning',
                            confirmButtonText: 'Continue',
                            confirmButtonColor: '#2563eb'
                        }).then(() => {
                            if (allProcessedCount > 0) {
                                loadVerificationData(currentPayrollId);
                                showView('STEP5');
                            }
                        });
                        return;
                    }
                } catch (error) {
                    Swal.close();
                    Swal.fire({
                        title: 'Error',
                        html: `
                            <div style="text-align: center; padding: 15px;">
                                <div style="width: 50px; height: 50px; background: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </div>
                                <p style="color: #6b7280; font-size: 13px; margin: 0;">${error.responseJSON?.message || error.statusText || 'Failed to process salaries'}</p>
                            </div>
                        `,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#ef4444'
                    });
                    return;
                }
            }
        }


        async function processSalariesInBatches(employeeIds) {
            const BATCH_SIZE = 20;
            const totalEmployees = employeeIds.length;
            const totalBatches = Math.ceil(totalEmployees / BATCH_SIZE);

            let processedEmployeeIds = [];
            let currentOffset = 0;
            let currentBatch = 1;
            let allSuccessful = true;
            let errorMessage = '';

            Swal.fire({
                title: 'Processing Salaries',
                html: `
                    <div style="padding: 24px; text-align: center;">
                        <p style="color: #64748b; margin: 0 0 12px 0;">Please wait. Do not close this window.</p>
                        <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;"></div>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            });

            for (let batch = 0; batch < totalBatches; batch++) {
                currentBatch = batch + 1;

                const batchEmployeeIds = employeeIds.slice(currentOffset, currentOffset + BATCH_SIZE);

                try {
                    const formData = new FormData();
                    formData.append('pp_id', currentPayrollId);
                    formData.append('batch_offset', currentOffset);
                    formData.append('batch_size', BATCH_SIZE);
                    formData.append('_token', '{{ csrf_token() }}');

                    processedEmployeeIds.forEach(id => {
                        formData.append('processed_employee_ids[]', id);
                    });

                    batchEmployeeIds.forEach(id => {
                        formData.append('selected_employees[]', id);
                    });

                    const response = await $.ajax({
                        url: '/payroll/payroll-new/process-salaries-chunk',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json'
                    });

                    if (response.success) {
                        processedEmployeeIds = response.processed_employee_ids || [];
                        currentOffset = response.next_resume_index || (currentOffset + BATCH_SIZE);

                        if (response.is_complete) {
                            Swal.close();

                            showSuccessOverlay(
                                'Salaries Processed Successfully!',
                                'Proceeding to verification.',
                                'check'
                            );

                            setTimeout(() => {
                                showView('STEP5');
                                loadVerificationData(currentPayrollId);
                            }, 2000);
                            return;
                        }

                        await new Promise(resolve => setTimeout(resolve, 500));
                    } else {
                        allSuccessful = false;
                        errorMessage = response.message || 'Processing failed';

                        break;
                    }
                } catch (error) {
                    allSuccessful = false;
                    errorMessage = error.responseJSON?.message || error.statusText || 'Network error';

                    break;
                }
            }

            if (!allSuccessful) {
                Swal.close();
                Swal.fire({
                    title: 'Processing Failed',
                    html: `
                        <div style="text-align: left;">
                            <p>${errorMessage}</p>
                            <div style="background: #fee2e2; padding: 12px; border-radius: 8px; margin-top: 10px;">
                                <strong>Recommendation:</strong>
                                <p style="margin-top: 5px;">Please check the errors and try again. You can resume from where it left off.</p>
                            </div>
                        </div>
                    `,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }


         async function processSalariesInChunks(employeeIds) {
            const CHUNK_SIZE = 300;
            const totalEmployees = employeeIds.length;
            const totalChunks = Math.ceil(totalEmployees / CHUNK_SIZE);

            let processedCount = 0;
            let failedCount = 0;
            let errors = [];
            let allProcessedIds = [];

            Swal.fire({
                title: 'Processing Salaries',
                html: `
                    <div style="padding: 24px; text-align: center;">
                        <p style="color: #64748b; margin: 0 0 12px 0;">Please wait. Do not close this window.</p>
                        <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;"></div>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false
            });

            for (let i = 0; i < totalChunks; i++) {
                const start = i * CHUNK_SIZE;
                const end = Math.min(start + CHUNK_SIZE, totalEmployees);
                const chunkEmployeeIds = employeeIds.slice(start, end);
                const chunkNumber = i + 1;

                try {
                    const formData = new FormData();
                    formData.append('pp_id', currentPayrollId);
                    formData.append('chunk_number', chunkNumber);
                    formData.append('total_chunks', totalChunks);
                    formData.append('_token', '{{ csrf_token() }}');

                    chunkEmployeeIds.forEach(id => {
                        formData.append('selected_employees[]', id);
                    });

                    if (allProcessedIds.length > 0) {
                        allProcessedIds.forEach(id => {
                            formData.append('processed_employee_ids[]', id);
                        });
                    }

                    const response = await $.ajax({
                        url: '/payroll/payroll-new/process-salaries-chunk',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json'
                    });

                    if (response.success) {
                        const chunkProcessed = response.processed_count || chunkEmployeeIds.length;
                        processedCount += chunkProcessed;

                        if (response.processed_employee_ids && Array.isArray(response.processed_employee_ids)) {
                            allProcessedIds = response.processed_employee_ids.slice();
                        } else {
                            allProcessedIds = [...allProcessedIds, ...chunkEmployeeIds];
                        }

                        if (response.is_last_chunk || chunkNumber === totalChunks) {
                            Swal.close();
                            Swal.fire({
                                title: '',
                                html: `
                                    <div style="text-align: center; padding: 20px 15px;">
                                        <div style="width: 60px; height: 60px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto; animation: successScale 0.4s ease-out;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </div>
                                        <h4 style="color: #065f46; margin: 0 0 5px 0; font-size: 18px;">Complete</h4>
                                        <p style="color: #4b5563; font-size: 13px; margin: 0;">
                                            ${response.message || 'Salaries processed successfully.'}
                                        </p>
                                    </div>
                                    <style>
                                        @keyframes successScale {
                                            0% { transform: scale(0); opacity: 0; }
                                            70% { transform: scale(1.1); opacity: 1; }
                                            100% { transform: scale(1); opacity: 1; }
                                        }
                                    </style>
                                `,
                                showConfirmButton: false,
                                timer: 1500,
                                timerProgressBar: true,
                                width: '380px'
                            });

                            setTimeout(() => {
                                showView('STEP5');
                                loadVerificationData(currentPayrollId);
                            }, 1500);
                            return;
                        }

                        await new Promise(resolve => setTimeout(resolve, 300));
                    } else {
                        failedCount++;
                        errors.push(response.message || 'Failed');
                        if (response.stop_processing) {
                            break;
                        }
                    }
                } catch (error) {
                    failedCount++;
                    const errorMsg = error.responseJSON?.message || error.statusText || 'Network error';
                    errors.push(errorMsg);
                    console.error('Chunk processing error:', error);
                }
            }

            Swal.close();

            if (failedCount > 0 && processedCount > 0) {
                Swal.fire({
                    title: 'Processing incomplete',
                    html: `
                        <div style="text-align: left;">
                            <p>Some records could not be saved. You can retry from the processing step.</p>
                            <div style="background: #fee2e2; padding: 12px; border-radius: 8px; margin-top: 10px; max-height: 200px; overflow-y: auto;">
                                <ul style="margin: 8px 0 0 20px;">
                                    ${errors.map(e => `<li>${e}</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#f59e0b'
                }).then(() => {
                    showView('STEP5');
                    loadVerificationData(currentPayrollId);
                });
            } else if (failedCount > 0 && processedCount === 0) {
                Swal.fire({
                    title: 'Processing Failed',
                    html: `
                        <div style="text-align: left;">
                            <p>Salary processing could not be completed. Please try again.</p>
                            <div style="background: #fee2e2; padding: 12px; border-radius: 8px;">
                                <ul style="margin: 8px 0 0 20px;">
                                    ${errors.map(e => `<li>${e}</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                    `,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }
        // ============================================
        // ATTENDANCE FUNCTIONS
        // ============================================
        function loadAttendanceData(payrollId, restoreMode = false) {
            const $button = $('[data-action="go-step2"]');
            console.log("load:", payrollId);
            if (!payrollId) {
                Swal.fire({
                    title: 'Error',
                    text: 'Payroll period ID is missing',
                    icon: 'error'
                });
                return;
            }

            $.ajax({
                url: '/payroll/payroll-new/attendance/retrieve',
                type: 'GET',
                data: {
                    payroll_id: payrollId
                },
                beforeSend: () => {
                    if (!restoreMode) {
                        $button.prop('disabled', true).text('Loading Attendance...');
                    }
                },
                success: function(response) {
                    if (response.attendanceData) {
                        retrievedAttendanceData = response.attendanceData;
                    }

                    // Check if response contains HTML or data
                    if (response.html) {
                        $('#attendanceTableBody').html(response.html);
                    } else if (response.data && response.data.html) {
                        $('#attendanceTableBody').html(response.data.html);
                    } else {
                        // Direct HTML response
                        $('#attendanceTableBody').html(response);
                    }
                    showView('STEP2', restoreMode);

                    setTimeout(() => {
                        initializeAttendanceValidation();

                        // Also check initial state
                        validateAttendanceTotal();
                    }, 300);



                },


                error: function(xhr) {
                    console.error('Failed to load attendance:', xhr);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to load attendance data. Please try again.',
                        icon: 'error'
                    });
                    if (currentPayrollId) {
                        clearPayrollStorage(currentPayrollId);
                    }
                    showView('DASHBOARD');
                },
                complete: () => {
                    if (!restoreMode) {
                        $button.prop('disabled', false).text('Proceed to Freeze Grid');
                    }
                }
            });
        }

        // ============================================
        // ATTENDANCE VALIDATION FUNCTIONS
        // ============================================

        function initializeAttendanceValidation() {
            // Validate on page load
            validateAttendanceTotal();

            // Validate when any input changes
            $(document).on('input', '#attendanceTable input[type="number"]', function() {
                // Wait a bit to allow value to update
                setTimeout(() => {
                    validateAttendanceTotal();
                }, 100);
            });

            // Also validate when late/early inputs change via buttons
            $(document).on('change', '.late-input, .early-input', function() {
                validateAttendanceTotal();
            });
        }

        // ============================================
        // ATTENDANCE VALIDATION FUNCTIONS - UPDATED
        // ============================================

        function validateAttendanceTotal() {
            let isValid = true;
            let errorRows = [];

            // Get freeze button element
            const $freezeBtn = $('[data-action="freeze-attendance"]');

            $('#attendanceTable tbody tr').each(function(index) {
                const $row = $(this);
                const $cells = $row.find('td');

                // Skip empty rows or loading rows
                if ($cells.length < 16 || $row.text().includes('Click "Proceed" to load attendance')) {
                    return;
                }

                // Skip hidden rows (from search filter)
                if ($row.css('display') === 'none') {
                    return;
                }

                // Get month days and total
                const monthDays = parseFloat($cells.eq(2).text().trim()) || 0;
                const total = parseFloat($cells.eq(15).text().trim()) || 0;

                if (total > monthDays) {
                    isValid = false;
                    errorRows.push(index + 1);

                    // Highlight the row
                    $row.css('background-color', '#fff5f5');
                    $cells.eq(15).css({
                        'color': '#dc2626',
                        'font-weight': 'bold',
                        'border': '2px solid #dc2626',
                        'background-color': '#fee2e2'
                    });
                } else {
                    // Reset styling if valid
                    $row.css('background-color', '');
                    $cells.eq(15).css({
                        'color': '',
                        'font-weight': '',
                        'border': '',
                        'background-color': ''
                    });
                }
            });

            // Update freeze button state
            if (!isValid) {
                // ✅ DISABLE FREEZE BUTTON
                $freezeBtn.prop('disabled', true);
                $freezeBtn.css({
                    'background': '#9ca3af',
                    'cursor': 'not-allowed',
                    'opacity': '0.7',
                    'box-shadow': 'none'
                });

                // Update button text to show it's disabled
                $freezeBtn.html(`
                            <i data-lucide="alert-circle" style="width: 18px; height: 18px; margin-right: 8px;"></i>
                            Fix Errors to Freeze
                        `);

                // Show warning message
                const $errorDiv = $('#attendanceValidationError');
                if ($errorDiv.length === 0) {
                    $('.attendance-table-container').before(`
                                <div id="attendanceValidationError" style="margin-bottom: 16px; padding: 12px; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 8px; color: #b91c1c;">
                                    <div style="display: flex; align-items: flex-start; gap: 8px;">
                                        <i data-lucide="x-circle" style="width: 20px; height: 20px; flex-shrink: 0; color: #dc2626;"></i>
                                        <div>
                                            <strong>Cannot Freeze Attendance</strong>
                                            <p style="margin: 4px 0 0 0; font-size: 0.875rem;">
                                                <span style="font-weight: 600;">Total days exceed month days in row(s): ${errorRows.join(', ')}</span><br>
                                                Please correct the highlighted values before freezing attendance.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            `);
                } else {
                    $errorDiv.find('p').html(`
                                <span style="font-weight: 600;">Total days exceed month days in row(s): ${errorRows.join(', ')}</span><br>
                                Please correct the highlighted values before freezing attendance.
                            `);
                }

                // Initialize Lucide icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

            } else {
                // ✅ ENABLE FREEZE BUTTON
                $freezeBtn.prop('disabled', false);
                $freezeBtn.css({
                    'background': '#dc2626',
                    'cursor': 'pointer',
                    'opacity': '1',
                    'box-shadow': '0 20px 25px -5px rgba(220, 38, 38, 0.2)'
                });

                // Restore original button text
                $freezeBtn.html(`
                            <i data-lucide="lock" style="width: 18px; height: 18px;"></i>
                            Freeze Attendance
                        `);

                // Remove warning message
                $('#attendanceValidationError').remove();

                // Initialize Lucide icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }

            return isValid;
        }

        // Function to validate individual input
        function validateInput(input) {
            if (input.value < 0) {
                input.value = 0;
            }

            // Also trigger total validation
            validateAttendanceTotal();
        }

        // ============================================
        // STEP 3 FUNCTIONS
        // ============================================

        function getStep3FilterValues() {
            const searchInput = document.getElementById('salarySearch');
            const statusFilter = document.getElementById('statusFilter');
            const employeeStatusFilter = document.getElementById('employeeStatusFilter');

            return {
                searchTerm: searchInput ? searchInput.value.toLowerCase().trim() : '',
                processingStatus: statusFilter ? statusFilter.value : 'all',
                employeeStatus: employeeStatusFilter ? employeeStatusFilter.value : 'all',
            };
        }

        // Initialize salary search and filters
        function initializeSalarySearch() {
            const searchInput = document.getElementById('salarySearch');
            const clearBtn = document.getElementById('clearSalarySearch');
            const statusFilter = document.getElementById('statusFilter');
            const employeeStatusFilter = document.getElementById('employeeStatusFilter');

            if (!searchInput) return;

            const cloneAndReplace = (element) => {
                if (!element || !element.parentNode) return null;
                const clone = element.cloneNode(true);
                element.parentNode.replaceChild(clone, element);
                return clone;
            };

            cloneAndReplace(searchInput);
            if (clearBtn) cloneAndReplace(clearBtn);
            if (statusFilter) cloneAndReplace(statusFilter);
            if (employeeStatusFilter) cloneAndReplace(employeeStatusFilter);

            const finalSearchInput = document.getElementById('salarySearch');
            const finalClearBtn = document.getElementById('clearSalarySearch');
            const finalStatusFilter = document.getElementById('statusFilter');
            const finalEmployeeStatusFilter = document.getElementById('employeeStatusFilter');

            if (!finalSearchInput) return;

            const runFilters = () => filterSalaryTable();

            setTimeout(() => updateSearchStats(), 500);

            finalSearchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase().trim();
                if (finalClearBtn) {
                    finalClearBtn.style.display = searchTerm.length > 0 ? 'block' : 'none';
                }
                runFilters();
            });

            if (finalClearBtn) {
                finalClearBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    finalSearchInput.value = '';
                    finalClearBtn.style.display = 'none';
                    runFilters();
                    finalSearchInput.focus();
                });
            }

            [finalStatusFilter, finalEmployeeStatusFilter].forEach((filterEl) => {
                if (filterEl) {
                    filterEl.addEventListener('change', runFilters);
                }
            });
        }

        // Filter salary table (search + processing status + employee status)
        function filterSalaryTable() {
            const filters = getStep3FilterValues();
            const tableBody = document.getElementById('step3TableBody');
            if (!tableBody) return;

            const rows = tableBody.querySelectorAll('tr');
            let visibleCount = 0;

            rows.forEach(row => {
                if (row.cells.length === 1 && row.cells[0].colSpan > 1) {
                    row.style.display = 'table-row';
                    return;
                }

                let employeeName = '', employeeCode = '', department = '', role = '';
                const employeeCell = row.querySelector('.employee-info');
                if (employeeCell) {
                    employeeName = employeeCell.querySelector('.employee-name')?.textContent?.toLowerCase() || '';
                    employeeCode = employeeCell.querySelector('.employee-code')?.textContent?.toLowerCase() || '';
                    department = employeeCell.querySelector('.employee-department')?.textContent?.toLowerCase() || '';
                }
                role = row.cells[2]?.textContent?.toLowerCase() || '';

                const rowEmpStatus = row.dataset.empStatus || '';
                const rowProcStatus = row.dataset.procStatus || '';

                const matchesSearch = filters.searchTerm === '' ||
                    employeeName.includes(filters.searchTerm) ||
                    employeeCode.includes(filters.searchTerm) ||
                    department.includes(filters.searchTerm) ||
                    role.includes(filters.searchTerm);

                const matchesProcessing = filters.processingStatus === 'all' ||
                    rowProcStatus === filters.processingStatus;

                const matchesEmployeeStatus = filters.employeeStatus === 'all' ||
                    String(rowEmpStatus) === String(filters.employeeStatus);

                if (matchesSearch && matchesProcessing && matchesEmployeeStatus) {
                    row.style.display = 'table-row';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            updateSearchStats(visibleCount);
            showNoResultsMessage(visibleCount === 0);
            updateProcessButtonText();
        }

        function getVisibleStep3Checkboxes() {
            const checkboxes = [];
            $('#step3TableBody tr').each(function() {
                const row = this;
                if (row.cells.length === 1 && row.cells[0].colSpan > 1) return;
                if (row.style.display === 'none') return;
                const checkbox = row.querySelector('.employee-checkbox');
                if (checkbox) checkboxes.push(checkbox);
            });
            return $(checkboxes);
        }

        // Update search stats
        function updateSearchStats(visibleCount = null) {
            const statsElement = document.getElementById('searchStats');
            if (!statsElement) return;

            const tableBody = document.getElementById('step3TableBody');
            if (!tableBody) return;

            const rows = tableBody.querySelectorAll('tr');
            let totalRows = 0;

            rows.forEach(row => {
                if (!(row.cells.length === 1 && row.cells[0].colSpan > 1)) {
                    totalRows++;
                }
            });

            let visible = visibleCount;
            if (visible === null) {
                visible = 0;
                rows.forEach(row => {
                    if (row.style.display !== 'none' && !(row.cells.length === 1 && row.cells[0].colSpan > 1)) {
                        visible++;
                    }
                });
            }

            const visibleSpan = document.getElementById('visibleCount');
            const totalSpan = document.getElementById('totalCount');

            if (visibleSpan) visibleSpan.textContent = visible;
            if (totalSpan) totalSpan.textContent = totalRows;
        }

        // Filter by status (legacy helper)
        function filterByStatus(statusId) {
            if (statusId === 'processing_group' || statusId === 'emp_status_group') {
                $('#statusFilter').val('all');
                $('#employeeStatusFilter').val('all');
                filterSalaryTable();
                return;
            }

            if (statusId === 'PENDING' || statusId === 'PROCESSED' || statusId === 'HELD') {
                $('#statusFilter').val(statusId);
            } else if (statusId === 'all') {
                $('#statusFilter').val('all');
                $('#employeeStatusFilter').val('all');
            } else {
                $('#employeeStatusFilter').val(String(statusId));
            }

            filterSalaryTable();
        }

        function filterSalaryTableByAllStatuses(searchTerm = '', statusId) {
            const tableBody = document.getElementById('step3TableBody');
            if (!tableBody) return;

            const rows = tableBody.querySelectorAll('tr');
            let visibleCount = 0;

            rows.forEach(row => {
                // Skip empty/loading rows
                if (row.cells.length === 1 && row.cells[0].colSpan > 1) {
                    row.style.display = 'table-row';
                    return;
                }

                // Get employee info for search
                let employeeName = '', employeeCode = '', department = '', role = '';
                const employeeCell = row.querySelector('.employee-info');
                if (employeeCell) {
                    employeeName = employeeCell.querySelector('.employee-name')?.textContent?.toLowerCase() || '';
                    employeeCode = employeeCell.querySelector('.employee-code')?.textContent?.toLowerCase() || '';
                    department = employeeCell.querySelector('.employee-department')?.textContent?.toLowerCase() || '';
                }
                role = row.cells[2]?.textContent?.toLowerCase() || '';

                // Get statuses from data attributes
                const rowEmpStatus = row.dataset.empStatus;      // Employee status ID (71, 72, etc.)
                const rowProcStatus = row.dataset.procStatus;    // Processing status (PENDING, PROCESSED, HELD)

                const matchesSearch = searchTerm === '' ||
                    employeeName.includes(searchTerm) ||
                    employeeCode.includes(searchTerm) ||
                    department.includes(searchTerm) ||
                    role.includes(searchTerm);

                let matchesStatus = false;

                // Case 1: All Status
                if (statusId === 'all') {
                    matchesStatus = true;
                }
                // Case 2: Processing Status (PENDING, PROCESSED, HELD)
                else if (statusId === 'PENDING' || statusId === 'PROCESSED' || statusId === 'HELD') {
                    matchesStatus = rowProcStatus === statusId;
                }
                // Case 3: Employee Status - Direct ID match (71, 72, etc.)
                else {
                    matchesStatus = rowEmpStatus && String(rowEmpStatus) === String(statusId);
                }

                if (matchesSearch && matchesStatus) {
                    row.style.display = 'table-row';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            updateSearchStats(visibleCount);
            showNoResultsMessage(visibleCount === 0);
        }



        function filterSalaryTableByEmpStatus(searchTerm = '', statusId) {
            const tableBody = document.getElementById('step3TableBody');
            if (!tableBody) return;

            const rows = tableBody.querySelectorAll('tr');
            let visibleCount = 0;

            rows.forEach(row => {
                // Skip empty/loading rows
                if (row.cells.length === 1 && row.cells[0].colSpan > 1) {
                    row.style.display = 'table-row';
                    return;
                }

                // Get employee info for search
                let employeeName = '', employeeCode = '', department = '', role = '';
                const employeeCell = row.querySelector('.employee-info');
                if (employeeCell) {
                    employeeName = employeeCell.querySelector('.employee-name')?.textContent?.toLowerCase() || '';
                    employeeCode = employeeCell.querySelector('.employee-code')?.textContent?.toLowerCase() || '';
                    department = employeeCell.querySelector('.employee-department')?.textContent?.toLowerCase() || '';
                }
                role = row.cells[2]?.textContent?.toLowerCase() || '';

                // Get employee status from data attribute
                const rowStatus = row.dataset.empStatus;

                const matchesSearch = searchTerm === '' ||
                    employeeName.includes(searchTerm) ||
                    employeeCode.includes(searchTerm) ||
                    department.includes(searchTerm) ||
                    role.includes(searchTerm);

                const matchesStatus = statusId === 'all' || rowStatus == statusId;

                if (matchesSearch && matchesStatus) {
                    row.style.display = 'table-row';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            updateSearchStats(visibleCount);
            showNoResultsMessage(visibleCount === 0);
        }

        function filterSalaryTableByStatus(searchTerm = '', statusId) {
            const tableBody = document.getElementById('step3TableBody');
            if (!tableBody) return;

            const rows = tableBody.querySelectorAll('tr');
            let visibleCount = 0;

            rows.forEach(row => {
                // Skip empty/loading rows
                if (row.cells.length === 1 && row.cells[0].colSpan > 1) {
                    row.style.display = 'table-row';
                    return;
                }

                // Get employee info for search
                let employeeName = '', employeeCode = '', department = '', role = '';
                const employeeCell = row.querySelector('.employee-info');
                if (employeeCell) {
                    employeeName = employeeCell.querySelector('.employee-name')?.textContent?.toLowerCase() || '';
                    employeeCode = employeeCell.querySelector('.employee-code')?.textContent?.toLowerCase() || '';
                    department = employeeCell.querySelector('.employee-department')?.textContent?.toLowerCase() || '';
                }
                role = row.cells[2]?.textContent?.toLowerCase() || '';

                // Get employee status from data attribute
                const rowStatus = row.dataset.empStatus;

                const matchesSearch = searchTerm === '' ||
                    employeeName.includes(searchTerm) ||
                    employeeCode.includes(searchTerm) ||
                    department.includes(searchTerm) ||
                    role.includes(searchTerm);

                const matchesStatus = statusId === 'all' || rowStatus == statusId;

                if (matchesSearch && matchesStatus) {
                    row.style.display = 'table-row';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            updateSearchStats(visibleCount);
            showNoResultsMessage(visibleCount === 0);
        }

        // Clear salary search
        function clearSalarySearch() {
            const searchInput = document.getElementById('salarySearch');
            const clearBtn = document.getElementById('clearSalarySearch');

            if (searchInput) {
                searchInput.value = '';
                if (clearBtn) clearBtn.style.display = 'none';
                filterSalaryTable();
                searchInput.focus();
            }
        }

        function clearAllFilters() {
            $('#salarySearch').val('');
            $('#clearSalarySearch').hide();
            $('#statusFilter').val('all');
            $('#employeeStatusFilter').val('all');
            filterSalaryTable();
        }




        // Show "no results" message
        function showNoResultsMessage(show) {
            const tableBody = document.getElementById('step3TableBody');
            let noResultsRow = document.getElementById('noSearchResults');

            if (show) {
                if (!noResultsRow) {
                    noResultsRow = document.createElement('tr');
                    noResultsRow.id = 'noSearchResults';
                    noResultsRow.innerHTML = `
                        <td colspan="9" style="padding: 40px; text-align: center; color: #94a3b8;">
                            <i data-lucide="search-x" style="width: 48px; height: 48px; margin-bottom: 16px;"></i>
                            <p style="font-size: 0.875rem;">No employees found matching your search criteria.</p>
                            <button onclick="clearAllFilters()"
                                    style="margin-top: 12px; padding: 6px 12px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; color: #475569; font-size: 0.75rem; cursor: pointer;">
                                Clear Filters
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(noResultsRow);

                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                } else {
                    noResultsRow.style.display = 'table-row';
                }
            } else if (noResultsRow) {
                noResultsRow.remove();
            }
        }

        function loadStep3Data(restoreMode = false) {
            if (!currentPayrollId) {
                Swal.fire({
                    title: 'Error',
                    text: 'Payroll ID not found',
                    icon: 'error'
                });
                if (currentPayrollId) {
                    clearPayrollStorage(currentPayrollId);
                }
                showView('DASHBOARD');
                return;
            }

            if (!restoreMode) {
                $('#step3TableBody').html(`
                        <tr>
                            <td colspan="7" style="padding:40px;text-align:center;">
                                <div style="display:flex;flex-direction:column;align-items:center;gap:16px;">
                                    <div class="spinner-border text-primary" role="status" style="width:40px;height:40px;"></div>
                                    <p style="color:#64748b;font-size:0.875rem;">Loading salary processing data...</p>
                                </div>
                            </td>
                        </tr>
                    `);
            }

            $.ajax({
                url: '/payroll/payroll-new/process-salary/step3-data',
                type: 'GET',
                data: {
                    payroll_id: currentPayrollId
                },
                success: function(response) {
                    if (response.success && response.data && response.data.employees) {
                        populateStep3Table(response.data.employees, response.data.summary);
                        showView('STEP3', restoreMode);

                        setTimeout(() => {
                            updateProcessButtonText();
                        }, 100);
                    } else {
                        $('#step3TableBody').html(`
                                <tr>
                                    <td colspan="9" style="padding:40px;text-align:center;color:#dc2626;">
                                        <i data-lucide="alert-circle" style="width:24px;height:24px;margin-bottom:12px;"></i>
                                        <p>${response.message || 'Invalid response format from server'}</p>
                                    </td>
                                </tr>
                            `);
                    }
                },
                error: function(xhr) {
                    console.error('Failed to load step 3 data:', xhr);
                    $('#step3TableBody').html(`
                            <tr>
                                <td colspan="9" style="padding:40px;text-align:center;color:#dc2626;">
                                    <i data-lucide="x-circle" style="width:24px;height:24px;margin-bottom:12px;"></i>
                                    <p>Failed to load data. Please try again.</p>
                                </td>
                            </tr>
                        `);
                    if (currentPayrollId) {
                        clearPayrollStorage(currentPayrollId);
                    }
                    showView('DASHBOARD');
                }
            });
        }

        function populateStep3Table(employees, summary = null) {
                const $tableBody = $('#step3TableBody');
                $tableBody.empty();

                if (!employees || employees.length === 0) {
                    $tableBody.html(`
                        <tr>
                            <td colspan="9" style="padding:40px;text-align:center;color:#94a3b8;">
                                No employees found for this payroll period.
                            </td>
                        </tr>
                    `);
                    return;
                }

                employees.forEach((emp) => {
                    const isProcessed = emp.is_processed || false;
                    const isHeld = emp.is_held || false;
                    const hasSalaryConfigured = emp.is_salary_configured !== false;

                    // Processing status
                    let procStatus = 'PENDING';
                    if (isProcessed) procStatus = 'PROCESSED';
                    if (isHeld) procStatus = 'HELD';
                    if (!hasSalaryConfigured) procStatus = 'NO SALARY';

                    // Employee status ID (71, 72, etc.) - YAHAN SE DATA ATTRIBUTE SET HOGA
                    const empStatus = emp.emp_status || '';

                    const row = `
                        <tr data-emp-status="${empStatus}"
                            data-proc-status="${procStatus}"
                            data-emp-id="${emp.emp_id || ''}"
                            data-hold-reason="${emp.hold_details?.hold_reason || ''}">

                            <td style="text-align: center;">
                                <input type="checkbox" class="employee-checkbox"
                                    data-emp-id="${emp.emp_id || ''}"
                                    data-employee-name="${emp.emp_name || ''}"
                                    ${!isProcessed && !isHeld && hasSalaryConfigured ? 'checked' : ''}
                                    ${isProcessed || isHeld || !hasSalaryConfigured ? 'disabled' : ''}>
                            </td>

                            <td>
                                <div class="employee-cell">
                                    <div class="employee-avatar"
                                        style="width: 32px; height: 32px; border-radius: 50%; background: #f1f5f9; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold; color: #475569;">
                                        ${(emp.emp_name || '').charAt(0).toUpperCase()}
                                    </div>
                                    <div class="employee-info">
                                        <div class="employee-name">${emp.emp_name || 'N/A'}</div>
                                        <div class="employee-code">${emp.emp_code || ''}</div>
                                        <div class="employee-department">${emp.department || ''}</div>
                                    </div>
                                </div>
                            </td>

                            <td>${emp.designation || 'N/A'}</td>

                            <td style="text-align: center; font-family: monospace; font-weight: 500;">
                                <div style="font-size: 0.875rem;">${emp.month_days || 0}</div>
                            </td>

                            <td style="text-align: center; font-family: monospace; font-weight: 500; color: #059669;">
                                <div style="font-size: 0.875rem;">${emp.salary_days || 0}</div>
                            </td>

                            <td style="text-align: right; font-family: monospace; font-weight: bold;">
                                ${emp.ad_hoc_amount !== 0 ?
                                    `<span style="color: ${emp.ad_hoc_amount > 0 ? '#059669' : '#dc2626'};">
                                        ${emp.ad_hoc_amount > 0 ? '+' : ''}₹${Math.abs(emp.ad_hoc_amount || 0).toLocaleString('en-IN')}
                                    </span>` :
                                    '<span style="color: #94a3b8;">-</span>'
                                }
                            </td>

                            <td style="text-align: center;">
                                <span class="status-badge ${!hasSalaryConfigured ? 'status-held' : (isHeld ? 'status-held' : (isProcessed ? 'status-processed' : 'status-pending'))}"
                                    title="${!hasSalaryConfigured ? 'Salary master not configured' : (isHeld ? (emp.hold_details?.hold_reason || '') : '')}">
                                    <i data-lucide="${!hasSalaryConfigured ? 'alert-triangle' : (isHeld ? 'alert-triangle' : (isProcessed ? 'check-circle' : 'clock'))}"
                                    style="width: 12px; height: 12px;"></i>
                                    ${procStatus}
                                </span>
                            </td>

                            <td style="text-align: right;">
                                <div class="action-buttons">
                                    ${!isHeld && !isProcessed && hasSalaryConfigured ? `
                                        <button data-action="hold-salary"
                                                data-employee-id="${emp.emp_id || ''}"
                                                data-employee-name="${emp.emp_name || ''}"
                                                class="action-btn hold-btn"
                                                style="background: #fef3c7; color: #92400e; border-color: #fde68a;">
                                            <i data-lucide="pause" style="width: 14px; height: 14px;"></i>
                                            Hold
                                        </button>
                                    ` : ''}
                                    <button data-action="view-employee-details"
                                            data-employee-id="${emp.emp_id || ''}"
                                            data-employee-name="${emp.emp_name || ''}"
                                            class="action-btn view-btn">
                                        <i data-lucide="eye" style="width: 14px; height: 14px;"></i>
                                        View
                                    </button>
                                    ${isHeld ? `
                                        <button data-action="release-salary"
                                                data-employee-id="${emp.emp_id || ''}"
                                                data-employee-name="${emp.emp_name || ''}"
                                                class="action-btn release-btn"
                                                style="background: #d1fae5; color: #065f46; border-color: #a7f3d0;">
                                            <i data-lucide="play" style="width: 14px; height: 14px;"></i>
                                            Release
                                        </button>
                                    ` : ''}
                                </div>
                            </td>
                        </tr>
                    `;

                    $tableBody.append(row);
                });

                initializeSalarySearch();
                filterSalaryTable();

                // Initialize Lucide icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                updateProcessButtonText();

                // Update search stats
                setTimeout(() => {
                    updateSearchStats();
                }, 200);
            }


         function updateTableHeaders() {
            const $tableHead = $('.payroll-table thead tr');
            if ($tableHead.find('th').length === 9) {
                return;
            }

            $tableHead.html(`
                    <th style="width: 48px; text-align: center;"></th>
                    <th>Employee</th>
                    <th>Role</th>
                    <th style="text-align: center;">Month Days<br><small style="font-weight: normal; color: #94a3b8;">Total Days</small></th>
                    <th style="text-align: center;">Salary Days<br><small style="font-weight: normal; color: #94a3b8;">Worked Days</small></th>
                    <th style="text-align:center;">
                        Late Days
                    </th>

                    <th style="text-align:center;">
                        Early Exit
                    </th>
                    <th style="text-align: right;">Ad-Hoc</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: right;">Action</th>
                `);
        }

        function showSummaryData(summary) {
            console.log(summary);
            const monthDays = summary.payroll_period_days || summary.month_days || 0;
            const totalHolidays = summary.total_holidays || 0;

            $('#days-summary').remove();

            $('.salary-table-container').before(`
                    <div id="days-summary" style="
                        margin-bottom: 16px;
                        padding: 12px 16px;
                        border: 1px solid #e2e8f0;
                        border-radius: 8px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        " class="para-text">
                        <div>
                            <strong style="font-size: 0.875rem; color: #c2c8d0;">
                                ${currentMonthName} Month Days: ${monthDays} days
                            </strong>
                            <div style="display: flex; gap: 16px; margin-top: 4px;">
                                <span style="font-size: 0.75rem; color: #64748b;">
                                    <i data-lucide="calendar" style="width: 12px; height: 12px; margin-right: 4px;"></i>
                                    Holidays: ${totalHolidays} days
                                </span>
                                <span style="font-size: 0.75rem; color: #64748b;">
                                    <i data-lucide="users" style="width: 12px; height: 12px; margin-right: 4px;"></i>
                                    Employees: ${summary.total_employees || 0}
                                </span>
                            </div>
                        </div>

                    </div>
                `);

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        // View Employee Details Event
        $(document).on('click', '[data-action="view-employee-details"]', function(e) {
            e.preventDefault();
            const empId = $(this).data('employee-id');
            const empName = $(this).data('employee-name');
            showEmployeeDetails(empId, empName);
        });

        function showEmployeeDetails(empId, empName) {
            // Show loading state
            const $step4View = $('[data-view="STEP4"]');
            $step4View.find('#employeeDetailsContent').html(`
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 20px;">
                    <div class="spinner-border text-primary" style="width:40px;height:40px;"></div>
                    <p style="color:#64748b;margin-top:16px;">Loading employee details...</p>
                </div>
                `);

            showView('STEP4');

            $.ajax({
                url: '/payroll/payroll-new/employee-payroll-details',
                type: 'GET',
                data: {
                    employee_id: empId,
                    payroll_id: currentPayrollId,
                    period_id: currentPeriodId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        populateEmployeeDetailView(response.data);
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message || 'Failed to load employee details',
                            icon: 'error'
                        });
                        showView('STEP3');
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to load employee details. Please try again.',
                        icon: 'error'
                    });
                    showView('STEP3');
                }
            });
        }

       function populateEmployeeDetailView(empData) {
         const $step4View = $('[data-view="STEP4"]');

        // Format currency helper
        const formatCurrency = (amount) => {
            return '₹' + parseFloat(amount).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };

        // Calculate base salary if not provided
        const baseSalary = empData.salary?.base_salary || empData.calculated_summary?.total_earnings || 0;
        const proratedBase = empData.salary?.prorated_base_salary || baseSalary;
        const netPayable = empData.calculated_summary?.net_payable || 0;
        const totalEarnings = empData.calculated_summary?.total_earnings || 0;
        const totalDeductions = empData.calculated_summary?.total_deductions || 0;

        // Get status badge
        let statusBadge = '';
        if (empData.status?.is_held) {
            statusBadge = `
                <div style="display: flex; align-items: center; gap: 6px; background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 6px; border: 1px solid #fde68a; font-size: 0.75rem; font-weight: 600;">
                    <i data-lucide="alert-triangle" style="width: 14px; height: 14px;"></i>
                    Held
                </div>
            `;
        } else if (empData.status?.is_processed) {
            statusBadge = `
                <div style="display: flex; align-items: center; gap: 6px; background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 6px; border: 1px solid #a7f3d0; font-size: 0.75rem; font-weight: 600;">
                    <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i>
                    Processed
                </div>
            `;
        } else {
            statusBadge = `
                <div style="display: flex; align-items: center; gap: 6px; background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.75rem; font-weight: 600;">
                    <i data-lucide="clock" style="width: 14px; height: 14px;"></i>
                    Pending
                </div>
            `;
        }

        // Generate HTML for compact view
        const html = `
            <div style="padding: 24px; min-height: 100vh;">
                <!-- Header -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <button data-action="back-to-list"
                            style="display: flex; align-items: center; gap: 8px; background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 16px; color: #475569; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                        Back to List
                    </button>
                    <div>
                        ${statusBadge}
                    </div>
                </div>

                <!-- Main Card -->
                <div style="max-width: 1200px; margin: 0 auto;">
                    <!-- Employee Header -->
                    <div class="premium-card card-padding-lg" style="margin-bottom: 20px;">
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                            <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; color: white;">
                                ${empData.employee.name?.charAt(0).toUpperCase() || 'E'}
                            </div>
                            <div style="flex: 1;">
                                <h1 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;">
                                    ${empData.employee.name}
                                </h1>
                                <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                                    <span style="background: #f1f5f9; color: #64748b; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">
                                        ${empData.employee.code}
                                    </span>
                                    <span style="color: #64748b; font-size: 0.875rem; display: flex; align-items: center; gap: 4px;">
                                        <i data-lucide="briefcase" style="width: 14px; height: 14px;"></i>
                                        ${empData.employee.designation}
                                    </span>
                                    <span style="color: #64748b; font-size: 0.875rem; display: flex; align-items: center; gap: 4px;">
                                        <i data-lucide="building-2" style="width: 14px; height: 14px;"></i>
                                        ${empData.employee.department}
                                    </span>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 4px;">Net Payable</div>
                                <div style="font-size: 1.75rem; font-weight: 700; color: #059669;">
                                    ${formatCurrency(netPayable)}
                                </div>
                                <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 4px;">
                                    ${empData.employee.currency}
                                </div>
                            </div>
                        </div>

                        <!-- Stats Grid -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 16px;">
                            <div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">Payroll Period</div>
                                <div style="font-size: 0.875rem; font-weight: 600; color: #1e293b;">
                                    ${empData.payroll_period?.month_days || 0} Days
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">Worked Days</div>
                                <div style="font-size: 0.875rem; font-weight: 600; color: #059669;">
                                    ${empData.attendance?.worked_days || 0} (${empData.attendance?.worked_percentage || 0}%)
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">Base Salary</div>
                                <div style="font-size: 0.875rem; font-weight: 600; color: #1e293b;">
                                    ${formatCurrency(baseSalary)}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">Prorated Base</div>
                                <div style="font-size: 0.875rem; font-weight: 600; color: #2563eb;">
                                    ${formatCurrency(proratedBase)}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Information Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 16px; margin-bottom: 16px;">

                        <!-- Attendance Breakdown -->
                        <div class="premium-card card-padding-lg" style="margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px; color: #8b5cf6;">
                                <i data-lucide="calendar" style="width: 18px; height: 18px;"></i>
                                <h3 style="font-size: 0.875rem; font-weight: 700; margin: 0;">Attendance Breakdown</h3>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 16px;">
                                <div style="text-align: center;">
                                    <div style="width: 40px; height: 40px; background: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px;">
                                        <span style="font-weight: 700; color: #059669;">${empData.attendance?.present_days || 0}</span>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b;">Present</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="width: 40px; height: 40px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px;">
                                        <span style="font-weight: 700; color: #dc2626;">${empData.attendance?.absent_days || 0}</span>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b;">Absent</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="width: 40px; height: 40px; background: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px;">
                                        <span style="font-weight: 700; color: #d97706;">${empData.attendance?.half_days || 0}</span>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b;">Half Days</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="width: 40px; height: 40px; background: #dbeafe; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px;">
                                        <span style="font-weight: 700; color: #2563eb;">${empData.attendance?.leave_days || 0}</span>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b;">Leave</div>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                                <div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">Weekoff Days</div>
                                    <div style="font-size: 0.875rem; font-weight: 600;">${empData.attendance?.weekoff_days || 0}</div>
                                </div>
                                <div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">Late Days</div>
                                    <div style="font-size: 0.875rem; font-weight: 600;">${empData.attendance?.late_days || 0}</div>
                                </div>
                                <div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">Early Exits</div>
                                    <div style="font-size: 0.875rem; font-weight: 600;">${empData.attendance?.early_exit_days || 0}</div>
                                </div>
                                <div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 4px;">UPL Days</div>
                                    <div style="font-size: 0.875rem; font-weight: 600;">${empData.attendance?.upl_days || 0}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Salary Breakdown -->
                        <div class="premium-card card-padding-lg" style="margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px; color: #059669;">
                                <i data-lucide="dollar-sign" style="width: 18px; height: 18px;"></i>
                                <h3 style="font-size: 0.875rem; font-weight: 700; margin: 0;">Salary Breakdown</h3>
                            </div>

                            <div style="margin-bottom: 16px;">
                                <!-- Earnings -->
                                <div style="margin-bottom: 16px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <div style="font-size: 0.75rem; font-weight: 600; color: #475569;">EARNINGS</div>
                                        <div style="font-size: 0.75rem; font-weight: 700; color: #059669;">${formatCurrency(totalEarnings)}</div>
                                    </div>
                                    <div style="background: #f0f9ff; border-radius: 8px; padding: 12px; border: 1px solid #e0f2fe;">
                                        ${empData.earnings_breakdown?.map(item => `
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #e0f2fe; font-size: 0.75rem;">
                                                <div style="color: #475569;">${item.type}</div>
                                                <div style="font-weight: 600; color: #059669;">${formatCurrency(item.amount)}</div>
                                            </div>
                                        `).join('') || `
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #e0f2fe; font-size: 0.75rem;">
                                                <div style="color: #475569;">Basic Salary</div>
                                                <div style="font-weight: 600; color: #059669;">${formatCurrency(proratedBase)}</div>
                                            </div>
                                        `}

                                        ${empData.adhoc_adjustments?.total_earnings > 0 ? `
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; font-size: 0.75rem;">
                                                <div style="color: #475569; display: flex; align-items: center; gap: 4px;">
                                                    <i data-lucide="zap" style="width: 12px; height: 12px; color: #059669;"></i>
                                                    Ad-Hoc Earnings
                                                </div>
                                                <div style="font-weight: 600; color: #059669;">+${formatCurrency(empData.adhoc_adjustments.total_earnings)}</div>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>

                                <!-- Deductions -->
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <div style="font-size: 0.75rem; font-weight: 600; color: #475569;">DEDUCTIONS</div>
                                        <div style="font-size: 0.75rem; font-weight: 700; color: #dc2626;">${formatCurrency(totalDeductions)}</div>
                                    </div>
                                    <div style="background: #fef2f2; border-radius: 8px; padding: 12px; border: 1px solid #fee2e2;">
                                        ${empData.deductions_breakdown?.map(item => `
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #fee2e2; font-size: 0.75rem;">
                                                <div style="color: #475569;">${item.type} (${item.category})</div>
                                                <div style="font-weight: 600; color: #dc2626;">${formatCurrency(item.amount)}</div>
                                            </div>
                                        `).join('') || `
                                            <div style="text-align: center; padding: 16px; color: #94a3b8; font-size: 0.75rem;">
                                                No deductions
                                            </div>
                                        `}

                                        ${empData.adhoc_adjustments?.total_deductions > 0 ? `
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; font-size: 0.75rem;">
                                                <div style="color: #475569; display: flex; align-items: center; gap: 4px;">
                                                    <i data-lucide="zap" style="width: 12px; height: 12px; color: #dc2626;"></i>
                                                    Ad-Hoc Deductions
                                                </div>
                                                <div style="font-weight: 600; color: #dc2626;">-${formatCurrency(empData.adhoc_adjustments.total_deductions)}</div>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>

                            <!-- Net Summary -->
                            <div style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border-radius: 8px; padding: 16px; color: white;">
                                <div style="text-align: center;">
                                    <div style="font-size: 0.75rem; color: #cbd5e1; margin-bottom: 4px;">FINAL PAYABLE</div>
                                    <div style="font-size: 1.5rem; font-weight: 700; color: white; margin-bottom: 8px;">
                                        ${formatCurrency(netPayable)}
                                    </div>
                                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; font-size: 0.75rem;">
                                        <div>
                                            <div style="color: #94a3b8;">Gross</div>
                                            <div style="color: white; font-weight: 600;">${formatCurrency(totalEarnings)}</div>
                                        </div>
                                        <div>
                                            <div style="color: #94a3b8;">Deductions</div>
                                            <div style="color: #fca5a5; font-weight: 600;">${formatCurrency(totalDeductions)}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div style="display: flex; gap: 12px; justify-content: center;">

                            ${!empData.status?.is_held && !empData.status?.is_processed ? `
                                <button onclick="holdSalary('${empData.employee.id}', '${empData.employee.name}')"
                                        style="padding: 10px 24px; background: #f59e0b; color: white; border: none; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <i data-lucide="pause" style="width: 16px; height: 16px;"></i>
                                    Hold Salary
                                </button>
                            ` : ''}

                            ${empData.status?.is_held ? `
                                <button onclick="releaseSalary('${empData.employee.id}')"
                                        style="padding: 10px 24px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <i data-lucide="play" style="width: 16px; height: 16px;"></i>
                                    Release Salary
                                </button>
                            ` : ''}

                            <button data-action="back-to-list"
                                    style="padding: 10px 24px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                                Back to List
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $step4View.html(html);

        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        }

        // Helper function to safely parse dates
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            try {
                return new Date(dateString).toLocaleDateString('en-IN', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            } catch (e) {
                return 'N/A';
            }
        }

        // ============================================
        // HOLD SALARY FUNCTIONALITY
        // ============================================

        let holdEmployeeData = null;

        // Hold salary button click event
        $(document).on('click', '[data-action="hold-salary"]', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const empId = $(this).data('employee-id');
            const empName = $(this).data('employee-name');

            holdEmployeeData = {
                employee_id: empId,
                employee_name: empName,
                department: $(this).closest('tr').find('.employee-department').text(),
                designation: $(this).closest('tr').find('td:nth-child(3)').text()
            };

            openHoldSalaryModal();
        });

       // Open hold salary modal (updated)
        function openHoldSalaryModal() {
            if (!holdEmployeeData) return;

            // Set employee details
            const $employeeInfo = $('#hold-employee-info');
            $employeeInfo.show();
            $('#hold-employee-avatar').text(holdEmployeeData.employee_name.charAt(0).toUpperCase());
            $('#hold-employee-name').text(holdEmployeeData.employee_name);
            $('#hold-employee-details').text(`${holdEmployeeData.department} • ${holdEmployeeData.designation}`);

            // Reset form
            $('#hold-reason').val('');
            $('#hold-until-release').prop('checked', false);
            $('#hold-until-release-info').hide();

            // Load payroll periods - NEW
            loadPayrollPeriodsForHold();

            // Open modal
            openModal('hold-salary-modal');
        }

        // Load payroll periods for hold
       // Update the function to use the new structure
        function loadPayrollPeriodsForHold() {
            const $dropdownContainer = $('#payroll-periods-dropdown-container');

            if (!holdEmployeeData) {
                $dropdownContainer.html(`
                    <div style="text-align: center; padding: 12px; background: #f8fafc; border: 1px dashed #d1d5db; border-radius: 6px; color: #dc2626;">
                        <i data-lucide="alert-circle" style="width: 16px; height: 16px;"></i>
                        <p style="font-size: 11px; margin: 4px 0 0 0;">Employee data not found</p>
                    </div>
                `);
                return;
            }

            $.ajax({
                url: '/payroll/payroll-new/get-payroll-periods',
                type: 'GET',
                data: {
                    employee_id: holdEmployeeData.employee_id,
                    current_payroll_id: currentPayrollId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success && response.periods && response.periods.length > 0) {
                        let options = '';
                        response.periods.forEach(period => {
                            const isCurrentPeriod = period.pp_id == currentPayrollId;
                            const isProcessed = period.is_processed || false;
                            const isHeld = period.is_held || false;

                            let disabledAttr = isProcessed || isHeld ? 'disabled' : '';
                            let selectedAttr = isCurrentPeriod ? 'selected' : '';

                            options += `
                                <option value="${period.pp_id}" ${selectedAttr} ${disabledAttr}>
                                    ${period.pp_name} ${isCurrentPeriod ? '(Current)' : ''} ${isHeld ? '(Held)' : ''} ${isProcessed ? '(Processed)' : ''}
                                </option>
                            `;
                        });

                        $dropdownContainer.html(`
                            <select id="payroll-periods-select"
                                    multiple
                                    style="
                                        width: 100%;
                                        padding: 8px;
                                        border: 1px solid #d1d5db;
                                        border-radius: 6px;
                                        font-size: 12px;
                                        min-height: 80px;
                                    ">
                                ${options}
                            </select>
                        `);

                        // Initialize simple multiselect
                        $('#payroll-periods-select').on('change', function() {
                            updateSelectedPeriods();
                            updateHoldButtonState();
                        });

                        updateSelectedPeriods();
                    } else {
                        $dropdownContainer.html(`
                            <div style="text-align: center; padding: 16px; background: #f8fafc; border: 1px dashed #d1d5db; border-radius: 6px; color: #94a3b8;">
                                <i data-lucide="calendar" style="width: 18px; height: 18px;"></i>
                                <p style="font-size: 11px; margin: 4px 0 0 0;">No payroll periods available</p>
                            </div>
                        `);
                    }

                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                },
                error: function() {
                    $dropdownContainer.html(`
                        <div style="text-align: center; padding: 16px; background: #f8fafc; border: 1px dashed #d1d5db; border-radius: 6px; color: #dc2626;">
                            <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                            <p style="font-size: 11px; margin: 4px 0 0 0;">Failed to load periods</p>
                        </div>
                    `);
                }
            });
        }

        // Format option in dropdown
        function formatOption(period) {
            if (!period.id) return period.text;

            const $option = $(period.element);
            const isProcessed = $option.data('processed');
            const isHeld = $option.data('held');
            const isCurrent = $option.data('current');

            let badgeClass = '';
            let badgeText = '';

            if (isProcessed) {
                badgeClass = 'bg-success';
                badgeText = 'Processed';
            } else if (isHeld) {
                badgeClass = 'bg-warning';
                badgeText = 'Already Held';
            } else if (isCurrent) {
                badgeClass = 'bg-primary';
                badgeText = 'Current';
            }

            if (badgeClass) {
                return $(`
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>${period.text.replace(/ \[(Current|Processed|Already Held)\]$/, '')}</span>
                        <span class="badge ${badgeClass}">${badgeText}</span>
                    </div>
                `);
            }

            return period.text;
        }

        // Format selected item
        function formatSelection(period) {
            if (!period.id) return period.text;
            return $(period.element).text().replace(/ \[(Current|Processed|Already Held)\]$/, '');
        }

        // Update selected periods display
        function updateSelectedPeriods() {
            const $select = $('#payroll-periods-select');
            const $container = $('.selected-periods-container');
            const $tagsContainer = $('.selected-periods-tags');

            if (!$select.length) return;

            const selectedOptions = $select.find('option:selected');

            if (selectedOptions.length === 0) {
                $container.hide();
                return;
            }

            let tagsHTML = '';
            selectedOptions.each(function() {
                const periodName = $(this).text().replace(/ \[(Current|Processed|Already Held)\]$/, '');
                const periodId = $(this).val();

                tagsHTML += `
                    <span class="badge bg-light text-dark me-1 mb-1" style="font-size: 11px;">
                        ${periodName}
                        <button type="button" class="btn-close btn-close-sm"
                                onclick="removePeriod('${periodId}')"
                                style="font-size: 8px; padding: 2px;">
                        </button>
                    </span>
                `;
            });

            $tagsContainer.html(tagsHTML);
            $container.show();
        }

        // Remove selected period
        function removePeriod(periodId) {
            const $select = $('#payroll-periods-select');
            $select.find(`option[value="${periodId}"]`).prop('selected', false);

            if ($.fn.select2 && $select.hasClass('select2-hidden-accessible')) {
                $select.trigger('change');
            } else {
                $select.trigger('change');
            }
        }

        // Show warning for "Hold until release"
        function showHoldUntilReleaseWarning() {
            Swal.fire({
                title: 'Multiple Periods Selected',
                html: `
                    <div style="text-align: left; font-size: 14px;">
                        <div style="background: #fef3c7; padding: 12px; border-radius: 8px; border-left: 4px solid #f59e0b; margin-bottom: 12px;">
                            <strong style="color: #92400e;">"Hold until release" is enabled</strong>
                            <p style="color: #b45309; font-size: 13px; margin-top: 6px;">
                                You can only select one payroll period at a time when this option is checked.
                            </p>
                        </div>
                        <p style="color: #64748b;">
                            Please deselect other periods or uncheck "Hold until release" to select multiple periods.
                        </p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#f59e0b'
            });
        }


        // Update hold button state
        function updateHoldButtonState() {
            const $select = $('#payroll-periods-select');
            const selectedCount = $select.val() ? $select.val().length : 0;
            const hasReason = $('#hold-reason').val().trim().length > 0;
            const $confirmBtn = $('#confirm-hold-btn');

            if (selectedCount > 0 && hasReason) {
                $confirmBtn.prop('disabled', false);
                $confirmBtn.html(`
                    <i data-lucide="pause" style="width: 14px; height: 14px; margin-right: 6px;"></i>
                    Hold (${selectedCount} period${selectedCount > 1 ? 's' : ''})
                `);
            } else {
                $confirmBtn.prop('disabled', true);
                $confirmBtn.html(`
                    <i data-lucide="pause" style="width: 14px; height: 14px; margin-right: 6px;"></i>
                    Confirm Hold
                `);
            }

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        // Handle "Hold until release" checkbox change
        $('#hold-until-release').on('change', function() {
            const isChecked = $(this).prop('checked');
            const $infoDiv = $('#hold-until-release-info');

            if (isChecked) {
                $infoDiv.show();

                // If multiple periods are selected, show warning and keep only first one
                const $checkboxes = $('#payroll-periods-list .period-checkbox:checked:not(:disabled)');
                if ($checkboxes.length > 1) {
                    // Uncheck all except the first one
                    $checkboxes.slice(1).prop('checked', false);
                    showHoldUntilReleaseWarning();
                }
            } else {
                $infoDiv.hide();
            }

            updateHoldButtonState();
        });

        // Handle reason input change
        $('#hold-reason').on('input', function() {
            updateHoldButtonState();
        });

        // Confirm hold button click (compact)
        $('#confirm-hold-btn').on('click', function(e) {
            e.preventDefault();

            const selectedPeriods = [];
            $('#payroll-periods-select option:selected').each(function() {
                if (!$(this).prop('disabled')) {
                    selectedPeriods.push($(this).val());
                }
            });

            const reason = $('#hold-reason').val().trim();
            const holdUntilRelease = $('#hold-until-release').prop('checked');

            if (selectedPeriods.length === 0) {
                Swal.fire({
                    title: 'No Period Selected',
                    text: 'Please select at least one payroll period.',
                    icon: 'warning',
                    width: '350px'
                });
                return;
            }

            if (!reason) {
                Swal.fire({
                    title: 'Reason Required',
                    text: 'Please enter a reason for holding salary.',
                    icon: 'warning',
                    width: '350px'
                });
                return;
            }

            // Validate: If holdUntilRelease is true, only one period should be selected
            if (holdUntilRelease && selectedPeriods.length > 1) {
                showHoldUntilReleaseWarning();
                return;
            }

            submitHoldSalary(selectedPeriods, reason, holdUntilRelease);
        });

        // Submit hold salary (updated)
        function submitHoldSalary(payrollPeriods, reason, holdUntilRelease) {
            const $confirmBtn = $('#confirm-hold-btn');
            const originalText = $confirmBtn.html();

            $confirmBtn.prop('disabled', true).html(`
                <div style="display: inline-flex; align-items: center; gap: 6px;">
                    <div class="spinner-border spinner-border-sm"></div>
                    <span style="font-size: 13px;">Processing...</span>
                </div>
            `);

            const formData = new FormData();
            formData.append('employee_id', holdEmployeeData.employee_id);
            formData.append('payroll_period_id[]', payrollPeriods);
            formData.append('reason', reason);
            formData.append('hold_until_release', holdUntilRelease ? 1 : 0);
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: '{{ route("payroll.new.hold-salary") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $confirmBtn.prop('disabled', false).html(originalText);

                    if (response.status === true || response.success === true) {
                        // Close modal
                        closeAllModals();

                        // Show success message
                        showSuccessOverlay(
                            'Salary Held Successfully',
                            `${holdEmployeeData.employee_name}'s salary has been put on hold`,
                            'pause'
                        );

                        // Refresh the salary list
                        setTimeout(() => {
                            loadStep3Data();
                        }, 1500);
                    } else {
                        Swal.fire({
                            title: 'Hold Failed',
                            html: `
                                <div style="text-align: left; font-size: 13px;">
                                    <p><strong>${response.message || 'Failed to hold salary'}</strong></p>
                                    ${response.errors ? `
                                        <div style="background: #fee2e2; padding: 10px; border-radius: 6px; margin-top: 10px;">
                                            <strong style="color: #b91c1c; font-size: 12px;">Errors:</strong>
                                            <ul style="margin: 5px 0 0 20px; color: #dc2626; font-size: 12px;">
                                                ${Object.values(response.errors).map(error => `<li>${error}</li>`).join('')}
                                            </ul>
                                        </div>
                                    ` : ''}
                                </div>
                            `,
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr) {
                    $confirmBtn.prop('disabled', false).html(originalText);

                    let errorMsg = 'Failed to hold salary';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        title: 'Error',
                        text: errorMsg,
                        icon: 'error'
                    });
                }
            });
        }


        // Clear modal data when closed
        $(document).on('click', '[data-action="close-modal"]', function() {
            // Reset form
            $('#hold-reason').val('');
            $('#hold-until-release').prop('checked', false);
            $('#hold-until-release-info').hide();
            holdEmployeeData = null;
        });


        function holdSalary(empId, empName) {
            Swal.fire({
                title: 'Hold Salary?',
                html: `
            <div style="text-align: left;">
                <p style="margin-bottom: 15px;">You are about to put <strong>${empName}</strong>'s salary on hold.</p>
                <p style="color: #64748b; font-size: 0.875rem;">
                    Held salaries will not be processed until released.
                </p>
            </div>
        `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Hold Salary',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#f59e0b'
            }).then((result) => {
                if (result.isConfirmed) {
                    // AJAX call to hold salary
                    $.ajax({
                        url: '/payroll/hold-salary',
                        type: 'POST',
                        data: {
                            employee_id: empId,
                            payroll_id: currentPayrollId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                showSuccessOverlay('Salary Held',
                                    `${empName}'s salary has been put on hold`, 'check');
                                // Refresh the employee details view
                                setTimeout(() => {
                                    showEmployeeDetails(empId, empName);
                                }, 1500);
                            } else {
                                Swal.fire('Error', response.message || 'Failed to hold salary',
                                    'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Failed to hold salary. Please try again.', 'error');
                        }
                    });
                }
            });
        }

        // function releaseSalary(empId) {
        //     Swal.fire({
        //         title: 'Release Salary?',
        //         text: 'This will make the salary available for processing.',
        //         icon: 'question',
        //         showCancelButton: true,
        //         confirmButtonText: 'Release',
        //         cancelButtonText: 'Cancel'
        //     }).then((result) => {
        //         if (result.isConfirmed) {
        //             // AJAX call to release salary
        //             $.ajax({
        //                 url: '/payroll/release-salary',
        //                 type: 'POST',
        //                 data: {
        //                     employee_id: empId,
        //                     payroll_id: currentPayrollId,
        //                     _token: '{{ csrf_token() }}'
        //                 },
        //                 success: function(response) {
        //                     if (response.success) {
        //                         showSuccessOverlay('Salary Released',
        //                             'Salary is now available for processing', 'check');
        //                         // Refresh the view
        //                         setTimeout(() => {
        //                             showView('STEP3');
        //                         }, 1500);
        //                     } else {
        //                         Swal.fire('Error', response.message || 'Failed to release salary',
        //                             'error');
        //                     }
        //                 },
        //                 error: function() {
        //                     Swal.fire('Error', 'Failed to release salary. Please try again.', 'error');
        //                 }
        //             });
        //         }
        //     });
        // }


        // ============================================
        // HOLD DETAILS FUNCTIONALITY (Same as in payroll-cycles)
        // ============================================

        // Hold details button click handler
        $(document).on('click', '#view-hold-details-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const currentCycleId = $(this).data('cycle-id');
            const cycleMonth = $(this).data('cycle-month');

            if (!currentCycleId) return;

            // Update modal subtitle
            if ($('#holdDetailsSubtitle').length) {
                $('#holdDetailsSubtitle').text(`Payroll Period: ${cycleMonth}`);
            }

            // Set process payroll link
            if ($('#processPayrollLink').length) {
                $('#processPayrollLink').attr('href', `/payroll/payroll-new-process?period=${currentCycleId}`);
            }

            // Load hold details
            loadHoldDetails(currentCycleId);

            // Show modal (Bootstrap 5)
            const holdDetailsModal = new bootstrap.Modal(document.getElementById('holdDetailsModal'));
            holdDetailsModal.show();
        });

        // Load hold details function
        async function loadHoldDetails(cycleId) {
            try {
                showHoldLoadingState();

                const response = await fetch(`/payroll/payroll-new/get-hold-details/${cycleId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const result = await response.json();

                if (result.success) {
                    if (result.holds && result.holds.length > 0) {
                        renderHoldDetails(result);
                    } else {
                        showNoHolds();
                    }
                    showHoldContent();
                } else {
                    showHoldError(result.message || 'Failed to load hold details.');
                }
            } catch (error) {
                console.error('Error loading hold details:', error);
                showHoldError('Network error. Please check your connection.');
            }
        }

        // Render hold details function (same as before)
        function renderHoldDetails(data) {
            if (!$('#holdsTableBody').length) return;

            // Update summary
            if ($('#totalHoldsCount').length) {
                $('#totalHoldsCount').text(`${data.count || 0} Hold(s)`);
            }

            // Hide empty state
            if ($('#noHoldsMessage').length) {
                $('#noHoldsMessage').addClass('d-none');
            }

            // Render table rows
            let html = '';
            data.holds.forEach((hold, index) => {
                const holdDate = new Date(hold.sh_held_at).toLocaleDateString('en-IN');
                const isHoldUntilRelease = hold.hold_until_release ? true : false;
                const payrollPeriodId = hold.sh_pp_id || data.payrollPeriod?.pp_id || data.cycle_id;

                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-1 me-2">
                                    <i data-lucide="user" class="text-muted" style="width: 12px; height: 12px;"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">${hold.employee_name || 'N/A'}</div>
                                    <div class="text-muted small">${hold.designation || ''}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">${hold.employee_code || 'N/A'}</span>
                        </td>
                        <td>${hold.department || 'N/A'}</td>
                        <td>
                            <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25">
                                ${hold.reason || 'Salary Hold'}
                            </span>
                            ${hold.remarks ? `<div class="text-muted small mt-1">${hold.remarks}</div>` : ''}
                            <div class="text-muted small mt-1">
                                <i data-lucide="calendar" style="width: 12px; height: 12px;"></i>
                                Held on: ${holdDate}
                                ${isHoldUntilRelease ?
                                    '<span class="badge bg-warning text-dark ms-2">Hold Until Release</span>' : ''}
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-success btn-hold-action"
                                        title="Release Hold"
                                        onclick="releaseHoldFromModal(${hold.sh_id}, ${hold.employee_id}, ${payrollPeriodId})">
                                    <i data-lucide="check" style="width: 12px; height: 12px;"></i>
                                    Release
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            $('#holdsTableBody').html(html);

            // Update Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        // Helper functions
        function showHoldLoadingState() {
            $('#holdDetailsLoading').removeClass('d-none');
            $('#holdDetailsContent').addClass('d-none');
            $('#errorMessage').addClass('d-none');
            $('#noHoldsMessage').addClass('d-none');
        }

        function showHoldContent() {
            $('#holdDetailsLoading').addClass('d-none');
            $('#holdDetailsContent').removeClass('d-none');
        }

        function showNoHolds() {
            $('#holdDetailsContent').addClass('d-none');
            $('#noHoldsMessage').removeClass('d-none');
            $('#totalHoldsCount').text('0 Hold(s)');
        }

        function showHoldError(message) {
            $('#holdDetailsLoading').addClass('d-none');
            $('#holdDetailsContent').addClass('d-none');
            $('#errorMessage').removeClass('d-none');
            $('#errorText').text(message);
        }

        // Release hold function for modal
    // Release hold function for modal - COMPACT VERSION like process-bulk
async function releaseHoldFromModal(holdId, employeeId, cycleId) {

    // Get employee details if available
    let employeeName = '';
    let employeeCode = '';
    const employeeRow = $(`[data-emp-id="${employeeId}"]`).closest('tr');

    if (employeeRow.length) {
        employeeName = employeeRow.find('.employee-name').text() || '';
        employeeCode = employeeRow.find('.employee-code').text() || '';
    }

    // Show confirmation dialog (like process-bulk)
    const result = await Swal.fire({
        title: 'Release Salary Hold?',
        html: `
            <div style="text-align: left;">
                <!-- Employee Badge - Compact -->
                <div style="
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    background: #eff6ff;
                    padding: 12px;
                    border-radius: 8px;
                    margin-bottom: 15px;
                    border-left: 4px solid #2563eb;
                ">
                    <div style="
                        width: 40px;
                        height: 40px;
                        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                        border-radius: 8px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                        font-weight: bold;
                        font-size: 16px;
                        flex-shrink: 0;
                    ">
                        ${employeeName ? employeeName.charAt(0).toUpperCase() : '?'}
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: #1e40af; font-size: 14px; margin-bottom: 2px;">
                            ${employeeName || 'Employee'}
                        </div>
                        <div style="color: #64748b; font-size: 12px; display: flex; gap: 8px;">
                            <span><i data-lucide="hash" style="width: 12px; height: 12px;"></i> ID: ${employeeId}</span>
                            ${employeeCode ? `<span><i data-lucide="credit-card" style="width: 12px; height: 12px;"></i> ${employeeCode}</span>` : ''}
                        </div>
                    </div>
                </div>

                <!-- Warning Section -->
                <div style="
                    background: #fef3c7;
                    padding: 12px;
                    border-radius: 8px;
                    margin-bottom: 12px;
                    border-left: 4px solid #f59e0b;
                ">
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <i data-lucide="alert-triangle" style="width: 16px; height: 16px; color: #d97706; flex-shrink: 0; margin-top: 2px;"></i>
                        <div>
                            <p style="color: #92400e; font-size: 13px; margin: 0; line-height: 1.5;">
                                <strong>Release Salary Hold</strong><br>
                                This will allow salary processing for this employee.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- What happens next - Compact -->
                <div style="
                    background: #eff6ff;
                    padding: 12px;
                    border-radius: 8px;
                    border-left: 4px solid #2563eb;
                ">
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <i data-lucide="info" style="width: 16px; height: 16px; color: #2563eb; flex-shrink: 0; margin-top: 2px;"></i>
                        <div>
                            <p style="color: #1e40af; font-size: 12px; margin: 0 0 5px 0; font-weight: 600;">What happens next?</p>
                            <ul style="margin: 0 0 0 18px; color: #3b82f6; font-size: 11px; line-height: 1.5; padding-left: 0;">
                                <li>Salary hold will be removed immediately</li>
                                <li>Employee becomes eligible for processing</li>
                                <li>Can process in current batch</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        `,
        icon: null,
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Release Now',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        width: '400px',  // Compact width
        padding: '16px',
        customClass: {
            popup: 'compact-modal'
        },
        didOpen: () => {
            // Initialize Lucide icons inside modal
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        },
        preConfirm: () => {
            // Show loading state
            Swal.showLoading();

            // Return promise for AJAX
            return new Promise(async (resolve, reject) => {
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    const response = await fetch('/payroll/payroll-new/release-hold', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            hold_id: holdId,
                            employee_id: employeeId,
                            cycle_id: cycleId
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        resolve(result);
                    } else {
                        reject(new Error(result.message || 'Failed to release hold.'));
                    }
                } catch (error) {
                    reject(error);
                }
            });
        }
    });

    if (result.isConfirmed && result.value) {
        const response = result.value;

        // Show compact success animation (like process-bulk)
        Swal.fire({
            title: '',
            html: `
                <div style="text-align: center; padding: 15px;">
                    <div style="
                        width: 50px;
                        height: 50px;
                        background: #10b981;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: 0 auto 12px auto;
                        animation: successScale 0.4s ease-out;
                    ">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <h4 style="color: #065f46; margin: 0 0 5px 0; font-size: 16px; font-weight: 600;">
                        Released!
                    </h4>
                    <p style="color: #4b5563; font-size: 12px; margin: 0;">
                        ${employeeName ? employeeName : 'Employee'} can now be processed
                    </p>
                </div>
                <style>
                    @keyframes successScale {
                        0% { transform: scale(0); opacity: 0; }
                        70% { transform: scale(1.1); opacity: 1; }
                        100% { transform: scale(1); opacity: 1; }
                    }
                </style>
            `,
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
            width: '320px',
            padding: '16px'
        });

        // Reload hold details
        loadHoldDetails(cycleId);

        // Reload the salary list in STEP3
        if (currentPayrollId) {
            setTimeout(() => {
                loadStep3Data();
            }, 1500);
        }
    }
}

        // Retry button functionality
        $(document).on('click', '#retryButton', function() {
            const cycleId = $('#view-hold-details-btn').data('cycle-id');
            if (cycleId) {
                loadHoldDetails(cycleId);
            }
        });


        // ============================================
        // CONFIRM RELEASE SALARY WITH SWEETALERT
        // ============================================

        // Release salary from STEP3 table
        $(document).on('click', '[data-action="release-salary"]', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const empId = $(this).data('employee-id');
        const empName = $(this).data('employee-name');
        const $button = $(this);

        // Disable button to prevent double click
        $button.prop('disabled', true);

        // Get row data for additional context
        const $row = $button.closest('tr');
        const department = $row.find('.employee-department').text() || 'N/A';
        const empCode = $row.find('.employee-code').text() || 'N/A';

        Swal.fire({
            title: 'Are you sure?',
            html: `
                <div style="text-align: left; padding: 0 15px;">
                    <!-- Icon with employee avatar -->
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;
                        background: #f8fafc; padding: 15px; border-radius: 10px;">
                        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                            border-radius: 50%; display: flex; align-items: center; justify-content: center;
                            color: white; font-weight: bold; font-size: 18px;">
                            ${empName.charAt(0).toUpperCase()}
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: #0f172a; font-size: 16px;">${empName}</div>
                            <div style="color: #64748b; font-size: 13px; margin-top: 2px;">
                                ${empCode} • ${department}
                            </div>
                        </div>
                    </div>

                    <!-- Warning message -->
                    <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; border-radius: 6px; margin-bottom: 15px;">
                        <div style="display: flex; align-items: flex-start; gap: 8px;">
                            <i data-lucide="alert-triangle" style="width: 18px; height: 18px; color: #d97706; flex-shrink: 0;"></i>
                            <div>
                                <strong style="color: #92400e; display: block; margin-bottom: 4px;">Release Salary Hold</strong>
                                <p style="color: #b45309; font-size: 13px; margin: 0;">
                                    This will allow salary processing for this employee in the selected payroll period.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- What happens next -->
                    <div style="background: #dbeafe; border-left: 4px solid #2563eb; padding: 12px; border-radius: 6px;">
                        <strong style="color: #1d4ed8; display: block; margin-bottom: 4px;">What happens next?</strong>
                        <ul style="margin: 5px 0 0 20px; color: #3b82f6; font-size: 13px;">
                            <li>Salary hold will be removed</li>
                            <li>Employee will become eligible for processing</li>
                            <li>You can process salary in the current batch</li>
                        </ul>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, release now',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            width: '500px',
            customClass: {
                confirmButton: 'swal2-confirm-btn',
                cancelButton: 'swal2-cancel-btn'
            },
            didOpen: () => {
                // Initialize Lucide icons inside modal
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            },
            preConfirm: () => {
                // Show loading state
                Swal.showLoading();

                // Return promise for AJAX call
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: '/payroll/payroll-new/release-salary',
                        type: 'POST',
                        data: {
                            employee_id: empId,
                            payroll_id: currentPayrollId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                resolve(response);
                            } else {
                                reject(new Error(response.message || 'Failed to release salary'));
                            }
                        },
                        error: function(xhr) {
                            let errorMsg = 'Failed to release salary';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            reject(new Error(errorMsg));
                        }
                    });
                });
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const response = result.value;

                // Success message
                Swal.fire({
                    title: 'Released!',
                    html: `
                        <div style="text-align: center; padding: 10px;">
                            <div style="width: 60px; height: 60px; background: #10b981; border-radius: 50%;
                                display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                                <i data-lucide="check" style="width: 30px; height: 30px; color: white;"></i>
                            </div>
                            <h3 style="color: #065f46; margin-bottom: 5px;">Salary Released!</h3>
                            <p style="color: #64748b; font-size: 14px; margin: 0;">
                                ${empName}'s salary hold has been removed.
                            </p>
                        </div>
                    `,
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: () => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    }
                });

                // Refresh the salary list
                setTimeout(() => {
                    loadStep3Data();
                }, 1500);
            }
        }).catch((error) => {
            // Re-enable button
            $button.prop('disabled', false);

            // Error message
            Swal.fire({
                title: 'Error',
                text: error.message || 'Failed to release salary. Please try again.',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
        });
    });

        // Add back to list event handler
        $(document).on('click', '[data-action="back-to-list"]', function(e) {
            e.preventDefault();
            showView('STEP3');
        });
        // ============================================
        // LOAD VERIFICATION DATA (STEP5)
        // ============================================
        function loadVerificationData(payrollId) {
            if (!payrollId) {
                console.error('Payroll ID missing for verification data');
                return;
            }

            console.log('Loading verification data for payroll:', payrollId);

            currentPayrollId = payrollId;
            showView('STEP5');
            updateStep5PayslipListLink();

            // Optional: Show success message
            showSuccessOverlay(
                'Salaries Processed!',
                'Proceed to Verification step',
                'check'
            );
        }

        // Function to update STEP5 with processed data
        function updateVerificationStep(response) {
            if (response && response.success !== false) {
                $('#verification-success-message').show();
            }
        }

        // ============================================
        // UTILITY FUNCTIONS
        // ============================================
        function toggleLateClear() {
            const inputs = document.querySelectorAll('.late-input:not([disabled])');
            inputs.forEach(input => {
                if (!lateCleared) {
                    if (!input.dataset.original) {
                        input.dataset.original = input.value;
                    }
                    input.value = 0;
                } else {
                    input.value = input.dataset.original || 0;
                }
            });
            lateCleared = !lateCleared;
            const btn = document.querySelector('button[onclick*="toggleLateClear"]');
            if (btn) {
                btn.textContent = lateCleared ? 'Restore Late' : 'Clear Late';
            }
        }

        function toggleEarlyClear() {
            const inputs = document.querySelectorAll('.early-input:not([disabled])');
            inputs.forEach(input => {
                if (!earlyCleared) {
                    if (!input.dataset.original) {
                        input.dataset.original = input.value;
                    }
                    input.value = 0;
                } else {
                    input.value = input.dataset.original || 0;
                }
            });
            earlyCleared = !earlyCleared;
            const btn = document.querySelector('button[onclick*="toggleEarlyClear"]');
            if (btn) {
                btn.textContent = earlyCleared ? 'Restore Early' : 'Clear Early';
            }
        }

        // Initialize search functionality
        $('#attendanceSearch').on('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#attendanceTableBody tr');

            rows.forEach(row => {
                const match = Array.from(row.cells).some(cell =>
                    cell.textContent.toLowerCase().includes(searchValue)
                );
                row.style.display = match ? '' : 'none';
            });
        });

        // Add a manual reset function (can be called from console if needed)
        window.resetPayrollProcess = function() {
            if (currentPayrollId) {
                clearPayrollStorage(currentPayrollId);
            }
            currentPayrollId = null;
            currentPeriodId = null;
            showView('DASHBOARD');
            Swal.fire({
                title: 'Process Reset',
                text: 'Payroll process has been reset to dashboard',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        };

        // Helper function for history management (kept for compatibility)
        function saveToHistory(viewName) {
            const state = {
                step: viewName,
                payrollId: currentPayrollId,
                timestamp: Date.now()
            };
            window.history.replaceState(state, '', window.location.href);
        }

        // Update process button text (counts only visible rows after filters)
        function updateProcessButtonText() {
            const $visibleCheckboxes = getVisibleStep3Checkboxes().filter(':not(:disabled)');
            const totalEmployees = $visibleCheckboxes.length;
            const selectedCount = $visibleCheckboxes.filter(':checked').length;

            const $button = $('[data-action="process-bulk"]');
            if (!$button.length) return;

            if (totalEmployees === 0) {
                $button.find('.process-text').text('Process Selected & Next');
                return;
            }

            if (selectedCount === totalEmployees) {
                $button.find('.process-text').text(`Process All (${totalEmployees}) & Next`);
            } else {
                $button.find('.process-text').text(`Process ${selectedCount} of ${totalEmployees} & Next`);
            }
        }

        // ============================================
        // PENDING REQUESTS MODAL FUNCTIONS
        // ============================================
        function showPendingRequestsModal(payrollId) {
            // Current cards ko update kar do modal ke data se
            $.ajax({
                url: '/payroll/payroll-new/check-pending-requests',
                type: 'GET',
                data: {
                    payroll_period: payrollId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        // ✅ Modal khulne se pehle hi cards update kar do
                        updateStep1CardsSimple(response.data);

                        // Ab modal dikhao
                        populatePendingRequestsModal(response.data);
                        openModal('pending-requests-modal');
                    }
                },
                error: function() {
                    // Direct modal khol do agar error aaye
                    openModal('pending-requests-modal');
                }
            });
        }

        // Function to populate modal with data
        function populatePendingRequestsModal(data) {
            // Update counts in tabs
            $('#missed-punch-count').text(data.missed_punches.count || 0);
            $('#leave-request-count').text(data.leave_requests.count || 0);
            $('#overtime-count').text(data.overtime_requests.count || 0);

            // Populate Missed Punches tab
            const missedPunchesList = $('#missed-punches-list');
            const missedPunchesEmpty = $('#missed-punches-empty');

            if (data.missed_punches.details && data.missed_punches.details.length > 0) {
                missedPunchesList.empty();
                data.missed_punches.details.forEach(item => {
                    missedPunchesList.append(`
                        <div class="modal-employee-item">
                            <div class="modal-employee-avatar">
                                ${(item.employee_name || '').charAt(0).toUpperCase()}
                            </div>
                            <div class="modal-employee-info">
                                <div class="modal-employee-name">
                                    ${item.employee_name || 'N/A'} (${item.employee_code || 'N/A'})
                                </div>
                                <div class="modal-employee-details">
                                    Missed Punch • ${item.date || 'N/A'} • ${item.in_time || '--'} to ${item.out_time || '--'}
                                </div>
                            </div>
                            <button class="action-btn view-btn" onclick="resolveMissedPunch(${item.employee_id}, '${item.date}')">
                                <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i>
                                Resolve
                            </button>
                        </div>
                    `);
                });
                missedPunchesEmpty.hide();
                missedPunchesList.show();
            } else {
                missedPunchesList.hide();
                missedPunchesEmpty.show();
            }

            // Populate Leave Requests tab
            const leaveRequestsList = $('#leave-requests-list');
            const leaveRequestsEmpty = $('#leave-requests-empty');

            if (data.leave_requests.details && data.leave_requests.details.length > 0) {
                leaveRequestsList.empty();
                data.leave_requests.details.forEach(item => {
                    leaveRequestsList.append(`
                        <div class="modal-employee-item">
                            <div class="modal-employee-avatar">
                                ${(item.employee_name || '').charAt(0).toUpperCase()}
                            </div>
                            <div class="modal-employee-info">
                                <div class="modal-employee-name">
                                    ${item.employee_name || 'N/A'} (${item.employee_code || 'N/A'})
                                </div>
                                <div class="modal-employee-details">
                                    ${item.leave_type || 'Leave'} • ${item.start_date || 'N/A'} to ${item.end_date || 'N/A'} (${item.duration || 'N/A'})
                                </div>
                            </div>
                            <button class="action-btn view-btn" onclick="resolveLeaveRequest(${item.employee_id})">
                                <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i>
                                Approve
                            </button>
                        </div>
                    `);
                });
                leaveRequestsEmpty.hide();
                leaveRequestsList.show();
            } else {
                leaveRequestsList.hide();
                leaveRequestsEmpty.show();
            }

            // Populate Overtime Requests tab
            const overtimeRequestsList = $('#overtime-requests-list');
            const overtimeRequestsEmpty = $('#overtime-requests-empty');

            if (data.overtime_requests.details && data.overtime_requests.details.length > 0) {
                overtimeRequestsList.empty();
                data.overtime_requests.details.forEach(item => {
                    overtimeRequestsList.append(`
                        <div class="modal-employee-item">
                            <div class="modal-employee-avatar">
                                ${(item.employee_name || '').charAt(0).toUpperCase()}
                            </div>
                            <div class="modal-employee-info">
                                <div class="modal-employee-name">
                                    ${item.employee_name || 'N/A'} (${item.employee_code || 'N/A'})
                                </div>
                                <div class="modal-employee-details">
                                    Overtime • ${item.date || 'N/A'} • ${item.hours || '0'} hours
                                </div>
                            </div>
                            <button class="action-btn view-btn" onclick="resolveOvertime(${item.employee_id}, '${item.date}')">
                                <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i>
                                Approve
                            </button>
                        </div>
                    `);
                });
                overtimeRequestsEmpty.hide();
                overtimeRequestsList.show();
            } else {
                overtimeRequestsList.hide();
                overtimeRequestsEmpty.show();
            }

            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        // Tab switching
        $(document).on('click', '.tab-btn', function() {
            const tabId = $(this).data('tab');

            // Remove active class from all tabs
            $('.tab-btn').removeClass('active');
            $('.tab-content').removeClass('active').hide();

            // Add active class to clicked tab
            $(this).addClass('active');
            $(`#${tabId}-tab`).addClass('active').show();
        });

        // Resolve functions (you need to implement these based on your logic)
        function resolveMissedPunch(employeeId, date) {
            // Implement your logic here
            console.log('Resolving missed punch for employee:', employeeId, 'on date:', date);
        }

        function resolveLeaveRequest(employeeId) {
            // Implement your logic here
            console.log('Resolving leave request for employee:', employeeId);
        }

        function resolveOvertime(employeeId, date) {
            // Implement your logic here
            console.log('Resolving overtime for employee:', employeeId, 'on date:', date);
        }

        function resolveAllPending() {
            // Implement bulk resolve logic here
            alert('Bulk resolve functionality to be implemented');
        }

        function closeModal(modalId) {
            $(`#${modalId}`).remove();
        }

        function proceedWithPending(periodId, payrollId) {
            closeModal('pending-requests-modal');

            showSuccessOverlay(
                'Proceeding to Pre-Checks',
                'You can resolve pending requests in the next step',
                'alert-triangle'
            );

            setTimeout(() => {
                proceedToStep1(periodId, payrollId);
            }, 1500);
        }

        function resolveAllAndProceed(periodId, payrollId) {
            closeModal('pending-requests-modal');

            Swal.fire({
                title: 'Resolving Requests',
                html: `
            <div style="text-align: center; padding: 20px;">
                <div class="spinner-border text-primary" style="width: 40px; height: 40px;"></div>
                <p style="color: #64748b; margin-top: 16px;">Resolving all pending requests...</p>
            </div>
            `,
                showConfirmButton: false,
                allowOutsideClick: false
            });

            $.ajax({
                url: '/payroll/resolve-all-pending',
                type: 'POST',
                data: {
                    payroll_period: periodId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.close();

                    if (response.success) {
                        showSuccessOverlay(
                            'Requests Resolved',
                            'All pending requests have been resolved',
                            'check-circle'
                        );

                        setTimeout(() => {
                            proceedToStep1(periodId, payrollId);
                        }, 1500);
                    } else {
                        Swal.fire({
                            title: 'Resolution Failed',
                            text: response.message || 'Failed to resolve requests',
                            icon: 'error'
                        }).then(() => {
                            proceedToStep1(periodId, payrollId);
                        });
                    }
                },
                error: function() {
                    Swal.close();

                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to resolve requests. Please try again.',
                        icon: 'error'
                    }).then(() => {
                        proceedToStep1(periodId, payrollId);
                    });
                }
            });
        }

        // ============================================
        // PROCEED TO STEP 1
        // ============================================
        function proceedToStep1(periodId, payrollId) {
            currentPayrollId = payrollId;
            currentPeriodId = periodId;

            showView('STEP1');
            loadPendingRequestsData(periodId);
        }

        // Function to load pending requests data for STEP1 cards
        function loadPendingRequestsData(periodId) {
            $.ajax({
                url: '/payroll/payroll-new/check-pending-requests',
                type: 'GET',
                data: {
                    payroll_period: periodId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        updateStep1Cards(response.data);
                    }
                },
                error: function() {
                    console.error('Failed to load pending requests data');
                }
            });
        }

        // Function to update STEP1 cards with data
        function updateStep1Cards(data) {
            // Missed Punches
            if (data.missed_punches) {
                const missedCount = data.missed_punches.count || 0;
                $('#missed-punches-count').text(missedCount + ' Pending');

                if (data.missed_punches.details && data.missed_punches.details.length > 0) {
                    const detail = data.missed_punches.details[0];
                    $('#missed-punches-example').html(`
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                        <span class="para-text">${detail.employee_name} (${detail.employee_code})</span>
                        <span style="color: #d97706; font-weight: bold;">${detail.date}</span>
                    </div>
                `);
                } else {
                    $('#missed-punches-example').html(`
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                        <span class="para-text">No pending requests</span>
                        <span style="color: #d97706; font-weight: bold;">--</span>
                    </div>
                `);
                }

                $('#resolve-missed-punches-btn')
                    .text(missedCount > 0 ? 'Resolve Batch' : 'No Actions Required')
                    .prop('disabled', missedCount === 0);
            }
            // Leave Requests
            if (data.leave_requests) {
                const leaveCount = data.leave_requests.count || 0;

                $('.glass-card.hover-effect:nth-child(2) .card-count').text(leaveCount + ' Pending');

                if (data.leave_requests.details && data.leave_requests.details.length > 0) {
                    const detail = data.leave_requests.details[0];
                    $('.glass-card.hover-effect:nth-child(2) .example-content').html(`
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                        <span class="para-text">${detail.employee_name} (${detail.employee_code || 'Leave'})</span>
                        <span style="color: #dc2626; font-weight: bold;">${detail.start_date} - ${detail.end_date}</span>
                    </div>
                `);
                } else {
                    $('.glass-card.hover-effect:nth-child(2) .example-content').html(`
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                        <span class="para-text">No pending requests</span>
                        <span style="color: #dc2626; font-weight: bold;">--</span>
                    </div>
                `);
                }

                $('.glass-card.hover-effect:nth-child(2) button')
                    .text(leaveCount > 0 ? 'View Requests' : 'No Actions Required')
                    .prop('disabled', leaveCount === 0);
            }

            // Over Time Requests
            if (data.overtime_requests) {
                const otCount = data.overtime_requests.count || 0;

                $('.glass-card.hover-effect:nth-child(3) .card-count').text(otCount + ' Pending');

                if (data.overtime_requests.details && data.overtime_requests.details.length > 0) {
                    const detail = data.overtime_requests.details[0];
                    $('.glass-card.hover-effect:nth-child(3) .example-content').html(`
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                        <span class="para-text">${detail.employee_name}</span>
                        <span style="color: #7c3aed; font-weight: bold;">${detail.hours || '--'} Hours</span>
                    </div>
                `);
                } else {
                    $('.glass-card.hover-effect:nth-child(3) .example-content').html(`
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                        <span class="para-text">No pending requests</span>
                        <span style="color: #7c3aed; font-weight: bold;">--</span>
                    </div>
                `);
                }

                $('.glass-card.hover-effect:nth-child(3) button')
                    .text(otCount > 0 ? 'Approve Overtime' : 'No Actions Required')
                    .prop('disabled', otCount === 0);
            }
        }

        // ============================================
        // REVERT PROCESSING MODAL FUNCTIONS
        // ============================================
       function showRevertProcessingModal(processedEmployees) {
            if (!processedEmployees || processedEmployees.length === 0) {
                Swal.fire({
                    title: 'No Processed Employees',
                    text: 'There are no processed employees to revert.',
                    icon: 'info'
                });
                return;
            }

            // Clear any previous modal
            $('#revert-processing-modal').remove();

            // Create new modal HTML
            const modalHtml = `
            <div id="revert-processing-modal" class="modal-overlay flex" style="z-index: 10010; display: flex;">
                <div class="modal-container max-w-4xl">
                    <div class="modal-header">
                        <h3 class="modal-title">Revert Salary Processing</h3>
                        <button class="modal-close-btn" data-action="close-revert-modal">
                            <i data-lucide="x"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="modal-alert warning">
                            <i data-lucide="refresh-cw"></i>
                            <div>
                                <strong>Revert Salary Processing?</strong>
                                <p style="font-size: 0.75rem; margin-top: 0.25rem;">
                                    You are going back to the processing stage. Select employees to unprocess (revert to pending).
                                </p>
                            </div>
                        </div>

                        <!-- Search Section -->
                        <div style="margin-bottom: 1rem;">
                            <div style="position: relative; margin-bottom: 0.75rem;">
                                <i data-lucide="search"
                                    style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #94a3b8; z-index: 10;"></i>
                                <input type="text" id="revert-search-input"
                                    placeholder="Search employees by name, designation or department..."
                                    style="width: 100%; padding: 10px 12px 10px 36px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem;"
                                    autocomplete="off">
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="modal-input-label">Processed employees</span>
                                <button class="select-all-btn modal-btn modal-btn-secondary" style="font-size: 0.75rem;"
                                    id="revert-select-all-btn">
                                    Select All
                                </button>
                            </div>
                        </div>

                        <div id="revert-employee-list" class="modal-employee-list" style="max-height: 300px; overflow-y: auto;">
                            ${processedEmployees.map(employee => `
                                <div class="modal-employee-item" data-employee-id="${employee.id}" style="display: flex; cursor: pointer;">
                                    <div class="modal-employee-checkbox checked">
                                        <i data-lucide="check" style="width: 12px; height: 12px; display: none;"></i>
                                    </div>
                                    <div class="modal-employee-avatar">
                                        ${(employee.name || '').charAt(0).toUpperCase()}
                                    </div>
                                    <div class="modal-employee-info">
                                        <div class="modal-employee-name">${employee.name || 'N/A'}</div>

                                        <div class="modal-employee-code" style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
                                            Emp Code: ${employee.code || 'N/A'}
                                        </div>
                                    </div>
                                    <div style="font-family: monospace; font-weight: bold; color: #059669; margin-left: auto;">
                                        ₹${employee.salary || '0.00'}
                                    </div>
                                </div>
                            `).join('')}
                        </div>

                        <div id="revert-no-results" class="modal-empty-state" style="display: none;">
                            <i data-lucide="search-x" style="width: 3rem; height: 3rem; margin-bottom: 1rem;"></i>
                            <p>No employees found matching your search</p>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="modal-btn modal-btn-secondary" data-action="close-revert-modal">
                            Cancel
                        </button>
                        <button class="modal-btn modal-btn-primary confirm-revert-btn">
                            <i data-lucide="refresh-cw" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i>
                            Revert & Go Back
                        </button>
                    </div>
                </div>
            </div>
            `;

            // Append modal to body
            $('body').append(modalHtml);

            // Initialize state
            window.selectedRevertIds = new Set(processedEmployees.map(emp => emp.id));

            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Setup event listeners
            setupRevertModalEvents();

            // Focus the modal
            $('#revert-processing-modal').focus();
        }

        // Blade view में modal call करने के लिए:
        function setupRevertButton() {
            $(document).on('click', '.btn-revert-processing', function() {
                // PHP से passed processed employees data को JavaScript में pass करें
                const processedEmployees = @json($allProcessedEmployeesForRevert);
                showRevertProcessingModal(processedEmployees);
            });
        }


        function showFallbackModal(processedEmployees) {
            let employeeListHTML = '';

            // Search input HTML
            const searchHTML = `
        <div style="margin-bottom: 16px;">
            <div style="position: relative; margin-bottom: 12px;">
                <i data-lucide="search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #94a3b8;"></i>
                <input type="text" id="fallback-search-input"
                       placeholder="Search employees by name, designation or department..."
                       style="width: 100%; padding: 10px 10px 10px 36px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px;">
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                <strong>Processed employees</strong>
                <button id="fallback-select-all" style="background: none; border: none; color: #3b82f6; cursor: pointer; font-size: 14px; font-weight: 500;">
                    Select All
                </button>
            </div>
        </div>
    `;

            // Employee items
            processedEmployees.forEach(emp => {
                employeeListHTML += `
            <div class="fallback-employee-item" data-emp-id="${emp.id}"
                 style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                <div style="flex: 1; margin-right: 16px;">
                    <strong>${emp.name}</strong><br>
                    <small style="color: #64748b;">${emp.designation || 'N/A'} • ${emp.department || 'N/A'}</small>
                </div>
                <div style="font-weight: bold; color: #059669; min-width: 100px; text-align: right;">
                    ${emp.salary || '₹0.00'}
                </div>
                <div style="margin-left: 16px; min-width: 24px;">
                    <input type="checkbox" class="fallback-emp-checkbox" value="${emp.id}" checked
                           style="width: 16px; height: 16px;">
                </div>
            </div>
        `;
            });

            Swal.fire({
                title: 'Revert Salary Processing',
                html: `
            <div style="max-height: 400px; overflow-y: auto;">
                <div style="background: #fef3c7; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
                    <i data-lucide="alert-triangle" style="width: 16px; height: 16px; color: #b45309; display: inline-block; vertical-align: middle;"></i>
                    <span style="margin-left: 8px; color: #92400e; font-weight: bold;">Modal not loaded properly</span>
                    <p style="color: #b45309; font-size: 14px; margin-top: 8px;">
                        Select employees to unprocess (revert to pending).
                    </p>
                </div>
                ${searchHTML}
                <div id="fallback-employee-list" style="max-height: 250px; overflow-y: auto;">
                    ${employeeListHTML}
                    <div id="fallback-no-results" style="text-align: center; padding: 20px; color: #94a3b8; display: none;">
                        <i data-lucide="search-x" style="width: 48px; height: 48px; margin-bottom: 8px;"></i>
                        <p>No employees found matching your search</p>
                    </div>
                </div>
            </div>
        `,
                showCancelButton: true,
                confirmButtonText: 'Revert Selected & Go Back',
                cancelButtonText: 'Cancel',
                width: '700px',
                customClass: {
                    popup: 'custom-swal-popup'
                },
                didOpen: () => {
                    // Initialize Lucide icons
                    if (window.lucide) {
                        lucide.createIcons();
                    }

                    // Search functionality
                    const searchInput = document.getElementById('fallback-search-input');
                    const employeeItems = document.querySelectorAll('.fallback-employee-item');
                    const noResultsDiv = document.getElementById('fallback-no-results');

                    searchInput.addEventListener('input', function(e) {
                        const searchTerm = e.target.value.toLowerCase().trim();
                        let visibleCount = 0;

                        employeeItems.forEach(item => {
                            const text = item.textContent.toLowerCase();
                            if (text.includes(searchTerm)) {
                                item.style.display = 'flex';
                                visibleCount++;
                            } else {
                                item.style.display = 'none';
                            }
                        });

                        // Show/hide no results message
                        if (visibleCount === 0 && searchTerm.length > 0) {
                            noResultsDiv.style.display = 'block';
                        } else {
                            noResultsDiv.style.display = 'none';
                        }

                    });

                    // Select All functionality
                    const selectAllBtn = document.getElementById('fallback-select-all');
                    let allSelected = true;

                    selectAllBtn.addEventListener('click', function() {
                        const checkboxes = document.querySelectorAll('.fallback-emp-checkbox');
                        allSelected = !allSelected;

                        checkboxes.forEach(checkbox => {
                            if (checkbox.closest('.fallback-employee-item').style.display !==
                                'none') {
                                checkbox.checked = allSelected;
                            }
                        });

                        selectAllBtn.textContent = allSelected ? 'Deselect All' : 'Select All';
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const selectedCheckboxes = document.querySelectorAll('.fallback-emp-checkbox:checked');
                    const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
                    handleRevertConfirmFallback(selectedIds);
                }
            });
        }

        function handleRevertConfirmFallback(employeeIds) {
            if (!employeeIds || employeeIds.length === 0) {
                Swal.fire({
                    title: 'No Employees Selected',
                    text: 'Please select at least one employee to revert.',
                    icon: 'warning'
                });
                return;
            }

            Swal.fire({
                title: 'Confirm Revert',
                html: `
            <div style="text-align: left; padding: 0 20px;">
                <p style="margin-bottom: 15px;">You are about to revert <strong> employee(s)</strong> back to pending state.</p>
                <div style="background: #fef3c7; padding: 12px; border-radius: 8px; border-left: 4px solid #f59e0b;">
                    <strong style="color: #92400e;">Note:</strong>
                    <ul style="margin: 8px 0 0 20px; color: #b45309; font-size: 14px;">
                        <li>Processed salary records will be deleted</li>
                        <li>Payslips will need to be regenerated</li>
                        <li>Attendance will be marked as unprocessed</li>
                    </ul>
                </div>
                <p style="color: #dc2626; font-weight: bold; margin-top: 10px;">
                    This action cannot be undone!
                </p>
            </div>
            `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Revert Selected',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve, reject) => {
                        const formData = new FormData();
                        formData.append('payroll_id', currentPayrollId);
                        formData.append('_token', '{{ csrf_token() }}');

                        employeeIds.forEach(id => {
                            formData.append('selected_employees[]', id);
                        });

                        $.ajax({
                            url: '/payroll/payroll-new/unprocess-selected-salaries',
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                resolve(response);
                            },
                            error: function(xhr) {
                                reject(new Error(xhr.responseText || 'Revert failed'));
                            }
                        });
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const response = result.value;

                    if (response.success) {
                        showSuccessOverlay(
                            'Salary Reverted',
                            `${employeeIds.length} employee(s) returned to pending state`,
                            'check'
                        );

                        setTimeout(() => {
                            loadStep3Data();
                            showView('STEP3');
                        }, 2000);
                    } else {
                        Swal.fire({
                            title: 'Revert Failed',
                            text: response.message || 'Failed to revert salaries.',
                            icon: 'error'
                        });
                    }
                }
            });
        }

        function initializeRevertSearch() {
            const searchInput = document.getElementById('revert-search-input');
            const selectAllBtn = document.getElementById('revert-select-all-btn');

            if (!searchInput) return;

            // Store original list for filtering
            let originalItems = [];

            // Function to filter items
            function filterItems(searchTerm) {
                const items = document.querySelectorAll('.modal-employee-item');
                const noResultsDiv = document.getElementById('revert-no-results');
                let visibleCount = 0;

                items.forEach(item => {
                    const employeeName = item.querySelector('.modal-employee-name')?.textContent?.toLowerCase() ||
                        '';
                    const employeeDetails = item.querySelector('.modal-employee-details')?.textContent
                        ?.toLowerCase() || '';

                    if (employeeName.includes(searchTerm) || employeeDetails.includes(searchTerm)) {
                        item.style.display = 'flex';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Show/hide no results message
                if (noResultsDiv) {
                    if (visibleCount === 0 && searchTerm.length > 0) {
                        noResultsDiv.style.display = 'block';
                    } else {
                        noResultsDiv.style.display = 'none';
                    }
                }

                return visibleCount;
            }

            // Search event listener
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase().trim();
                filterItems(searchTerm);
            });

            // Clear search on modal close
            $(document).on('click', '#revert-processing-modal .modal-close-btn, [data-action="close-modal"]', function() {
                searchInput.value = '';
                filterItems('');
            });

            // Select All button for visible items only
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    const visibleItems = document.querySelectorAll('.modal-employee-item[style="display: flex;"]');
                    const hiddenItems = document.querySelectorAll('.modal-employee-item[style="display: none;"]');

                    if (visibleItems.length === 0) return;

                    // Check if all visible items are selected
                    let allVisibleSelected = true;
                    visibleItems.forEach(item => {
                        const checkbox = item.querySelector('.modal-employee-checkbox');
                        if (!checkbox.classList.contains('checked')) {
                            allVisibleSelected = false;
                        }
                    });

                    // Toggle selection
                    if (allVisibleSelected) {
                        // Deselect all visible
                        visibleItems.forEach(item => {
                            const checkbox = item.querySelector('.modal-employee-checkbox');
                            if (checkbox.classList.contains('checked')) {
                                checkbox.classList.remove('checked');
                                checkbox.classList.add('unchecked');
                                const empId = item.getAttribute('data-employee-id');
                                if (empId) window.selectedRevertIds.delete(empId);
                            }
                        });
                        selectAllBtn.textContent = 'Select All';
                    } else {
                        // Select all visible
                        visibleItems.forEach(item => {
                            const checkbox = item.querySelector('.modal-employee-checkbox');
                            if (!checkbox.classList.contains('checked')) {
                                checkbox.classList.remove('unchecked');
                                checkbox.classList.add('checked');
                                const empId = item.getAttribute('data-employee-id');
                                if (empId) window.selectedRevertIds.add(empId);
                            }
                        });
                        selectAllBtn.textContent = 'Deselect All';
                    }

                    // Don't affect hidden items
                    hiddenItems.forEach(item => {
                        // Keep their current state
                    });

                    updateConfirmButton();
                });
            }

            // Escape key to clear search
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    searchInput.value = '';
                    filterItems('');
                }
            });
        }

       function setupRevertModalEvents() {
        // Close modal event
        $(document).on('click', '[data-action="close-revert-modal"]', function(e) {
            e.preventDefault();
            $('#revert-processing-modal').remove();
        });

        // Click on modal overlay to close
        $(document).on('click', '#revert-processing-modal', function(e) {
            if (e.target === this) {
                $(this).remove();
            }
        });

        // Escape key to close modal
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#revert-processing-modal').length > 0) {
                $('#revert-processing-modal').remove();
            }
        });

        // Employee item click - UPDATED
        $(document).on('click', '#revert-employee-list .modal-employee-item', function(e) {
            e.stopPropagation();
            const $item = $(this);
            const empId = $item.data('employee-id');
            const $checkbox = $item.find('.modal-employee-checkbox');

            if ($checkbox.hasClass('checked')) {
                $checkbox.removeClass('checked').addClass('unchecked');
                // Remove check icon if present
                $checkbox.find('i').hide();
                // Also remove the pseudo-element content by toggling class
                $checkbox.css('background', '');
                $checkbox.css('border-color', '#d1d5db');
                window.selectedRevertIds.delete(empId);
            } else {
                $checkbox.removeClass('unchecked').addClass('checked');
                // Show check icon if it exists
                const $checkIcon = $checkbox.find('i');
                if ($checkIcon.length) {
                    $checkIcon.show();
                }
                // Add background for checked state
                $checkbox.css('background', '#3b82f6');
                $checkbox.css('border-color', '#3b82f6');
                window.selectedRevertIds.add(empId);
            }

            // CRITICAL: Update button state immediately
            updateRevertConfirmButton();

            // Also update select all button text
            updateRevertSelectAllButton();
        });

        // Function to update select all button
        function updateRevertSelectAllButton() {
            const $items = $('#revert-employee-list .modal-employee-item:visible');
            const $selectAllBtn = $('#revert-select-all-btn');

            if ($items.length === 0) {
                $selectAllBtn.hide();
                return;
            }

            let selectedCount = 0;
            $items.each(function() {
                if ($(this).find('.modal-employee-checkbox').hasClass('checked')) {
                    selectedCount++;
                }
            });

            if (selectedCount === $items.length && $items.length > 0) {
                $selectAllBtn.text('Deselect All');
            } else {
                $selectAllBtn.text('Select All');
            }

            $selectAllBtn.show();
        }

        // Select All button - UPDATED
        $(document).on('click', '#revert-select-all-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $items = $('#revert-employee-list .modal-employee-item:visible');
            const $selectAllBtn = $(this);

            if ($items.length === 0) return;

            // Check if all visible items are selected
            let allSelected = true;
            $items.each(function() {
                if (!$(this).find('.modal-employee-checkbox').hasClass('checked')) {
                    allSelected = false;
                }
            });

            if (allSelected) {
                // Deselect all visible items
                $items.each(function() {
                    const empId = $(this).data('employee-id');
                    const $checkbox = $(this).find('.modal-employee-checkbox');
                    $checkbox.removeClass('checked').addClass('unchecked');
                    $checkbox.find('i').hide();
                    $checkbox.css('background', '');
                    $checkbox.css('border-color', '#d1d5db');
                    window.selectedRevertIds.delete(empId);
                });
                $selectAllBtn.text('Select All');
            } else {
                // Select all visible items
                $items.each(function() {
                    const empId = $(this).data('employee-id');
                    const $checkbox = $(this).find('.modal-employee-checkbox');
                    $checkbox.removeClass('unchecked').addClass('checked');
                    const $checkIcon = $checkbox.find('i');
                    if ($checkIcon.length) {
                        $checkIcon.show();
                    }
                    $checkbox.css('background', '#3b82f6');
                    $checkbox.css('border-color', '#3b82f6');
                    window.selectedRevertIds.add(empId);
                });
                $selectAllBtn.text('Deselect All');
            }

            // CRITICAL: Update button after select all
            updateRevertConfirmButton();
        });

        // Function to update confirm button - MOVED OUTSIDE for better scope
        function updateRevertConfirmButton() {
            const $confirmBtn = $('.confirm-revert-btn');
            const selectedCount = window.selectedRevertIds ? window.selectedRevertIds.size : 0;

            if (selectedCount > 0) {
                $confirmBtn.prop('disabled', false);
                $confirmBtn.css({
                    'opacity': '1',
                    'cursor': 'pointer',
                    'background': '#3b82f6'
                });
                // Update button text with count
                $confirmBtn.html(`
                    <i data-lucide="refresh-cw" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i>
                    Revert ${selectedCount} Employee${selectedCount > 1 ? 's' : ''} & Go Back
                `);
            } else {
                $confirmBtn.prop('disabled', true);
                $confirmBtn.css({
                    'opacity': '0.5',
                    'cursor': 'not-allowed',
                    'background': '#9ca3af'
                });
                $confirmBtn.html(`
                    <i data-lucide="refresh-cw" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i>
                    Revert & Go Back
                `);
            }

            // Reinitialize Lucide icons for the button
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        // Confirm revert button
        $(document).on('click', '.confirm-revert-btn', function(e) {
            e.preventDefault();
            // Only proceed if button is not disabled
            if (!$(this).prop('disabled')) {
                handleRevertConfirm();
            }
        });

        // Search functionality - UPDATED
        $(document).on('input', '#revert-search-input', function() {
            const searchTerm = $(this).val().toLowerCase().trim();
            const $items = $('#revert-employee-list .modal-employee-item');
            const $noResults = $('#revert-no-results');

            let visibleCount = 0;

            $items.each(function() {
                const $item = $(this);
                const name = $item.find('.modal-employee-name').text().toLowerCase();
                const details = $item.find('.modal-employee-details').text().toLowerCase();

                if (name.includes(searchTerm) || details.includes(searchTerm)) {
                    $item.show();
                    visibleCount++;
                } else {
                    $item.hide();
                }
            });

            // Show/hide no results message
            if (visibleCount === 0 && searchTerm.length > 0) {
                $noResults.show();
            } else {
                $noResults.hide();
            }

            // Update select all button based on visible items
            updateRevertSelectAllButton();

            // CRITICAL: Update button state after search
            // Check if any visible items are selected
            let hasSelectedVisible = false;
            $items.each(function() {
                if ($(this).is(':visible') && $(this).find('.modal-employee-checkbox').hasClass('checked')) {
                    hasSelectedVisible = true;
                }
            });

            // If no visible items are selected, disable button
            if (visibleCount > 0 && !hasSelectedVisible) {
                const $confirmBtn = $('.confirm-revert-btn');
                $confirmBtn.prop('disabled', true);
                $confirmBtn.css({
                    'opacity': '0.5',
                    'cursor': 'not-allowed'
                });
            } else if (visibleCount > 0 && hasSelectedVisible) {
                updateRevertConfirmButton();
            } else if (visibleCount === 0) {
                const $confirmBtn = $('.confirm-revert-btn');
                $confirmBtn.prop('disabled', true);
                $confirmBtn.css({
                    'opacity': '0.5',
                    'cursor': 'not-allowed'
                });
            } else {
                updateRevertConfirmButton();
            }
        });

        // Initialize button state
        updateRevertConfirmButton();
    }

        function closeRevertModal() {
            $('#revert-processing-modal').addClass('hidden').removeClass('flex');
        }

        function selectAllEmployees() {
            const employeeItems = $('#revert-processing-modal .modal-employee-item');
            window.selectedRevertIds.clear();

            employeeItems.each(function() {
                const employeeId = $(this).data('employee-id');
                window.selectedRevertIds.add(employeeId);

                $(this).find('.modal-employee-checkbox')
                    .removeClass('unchecked')
                    .addClass('checked');
            });

            updateConfirmButton();
            updateSelectAllButton();
        }

       function initializeRevertModal(processedEmployees) {
        const employeeList = $('#revert-employee-list');
        const emptyState = $('#revert-no-results');
        const selectAllBtn = $('.select-all-btn');
        const confirmBtn = $('.confirm-revert-btn');

        employeeList.empty();

        window.selectedRevertIds = new Set();

        if (processedEmployees.length === 0) {
            employeeList.hide();
            emptyState.show();
            selectAllBtn.hide();
            confirmBtn.prop('disabled', true);
            confirmBtn.css({
                'opacity': '0.5',
                'cursor': 'not-allowed'
            });
            confirmBtn.html(`
                <i data-lucide="refresh-cw" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i>
                Revert & Go Back
            `);
        } else {
            employeeList.show();
            emptyState.hide();
            selectAllBtn.show();

            processedEmployees.forEach((employee) => {
                const employeeItem = $(`
                    <div class="modal-employee-item" data-employee-id="${employee.id}" style="display: flex; cursor: pointer;">
                        <div class="modal-employee-checkbox checked">
                            <i data-lucide="check" style="width: 12px; height: 12px;"></i>
                        </div>
                        <div class="modal-employee-avatar">
                            ${(employee.name || '').charAt(0).toUpperCase()}
                        </div>
                        <div class="modal-employee-info">
                            <div class="modal-employee-name">${employee.name || 'N/A'}</div>
                            <div class="modal-employee-details">
                                ${employee.designation || 'N/A'} • ${employee.department || 'N/A'}
                            </div>
                        </div>
                        <div style="font-family: monospace; font-weight: bold; color: #059669;">
                            ${employee.salary || '₹0.00'}
                        </div>
                    </div>
                `);
                employeeList.append(employeeItem);
                window.selectedRevertIds.add(employee.id);
            });

            // Initially, all employees are selected - set button text with count
            confirmBtn.prop('disabled', false);
            confirmBtn.css({
                'opacity': '1',
                'cursor': 'pointer',
                'background': '#3b82f6'
            });
            confirmBtn.html(`
                <i data-lucide="refresh-cw" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i>
                Revert ${processedEmployees.length} Employee${processedEmployees.length > 1 ? 's' : ''} & Go Back
            `);

            updateRevertSelectAllButton();
        }

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        setupRevertModalEvents();
    }

        function updateSelectAllButton() {
            const selectAllBtn = $('.select-all-btn');
            const employeeItems = $('#revert-processing-modal .modal-employee-item');

            if (employeeItems.length === 0) return;

            // Check current state based on visible items
            const visibleItems = document.querySelectorAll('.modal-employee-item[style="display: flex;"]');
            const selectedVisible = document.querySelectorAll(
                '.modal-employee-item[style="display: flex;"] .modal-employee-checkbox.checked');

            if (visibleItems.length > 0 && selectedVisible.length === visibleItems.length) {
                selectAllBtn.text('Deselect All');
            } else {
                selectAllBtn.text('Select All');
            }
        }

        function updateSelectAllButton() {
            const selectAllBtn = $('.select-all-btn');
            const employeeItems = $('#revert-processing-modal .modal-employee-item');

            if (employeeItems.length === 0) {
                selectAllBtn.hide();
                return;
            }

            if (window.selectedRevertIds.size === employeeItems.length) {
                selectAllBtn.text('Deselect All');
            } else {
                selectAllBtn.text('Select All');
            }
        }

        function updateConfirmButton() {
            const confirmBtn = $('.confirm-revert-btn');
            confirmBtn.prop('disabled', window.selectedRevertIds.size === 0);
        }

        function handleRevertConfirm() {
            if (window.selectedRevertIds.size === 0) {
                Swal.fire({
                    title: 'No Employees Selected',
                    text: 'Please select at least one employee to revert.',
                    icon: 'warning'
                });
                return;
            }

            // Remove modal first
            $('#revert-processing-modal').remove();

            Swal.fire({
                title: 'Confirm Revert',
                html: `
            <div style="text-align: left; padding: 0 20px;">
                <p style="margin-bottom: 15px;">You are about to revert <strong>${window.selectedRevertIds.size} employee(s)</strong> back to pending state.</p>
                <div style="background: #fef3c7; padding: 12px; border-radius: 8px; border-left: 4px solid #f59e0b;">
                    <strong style="color: #92400e;">Note:</strong>
                    <ul style="margin: 8px 0 0 20px; color: #b45309; font-size: 14px;">
                        <li>Processed salary records will be deleted</li>
                        <li>Attendance will be marked as unprocessed</li>
                    </ul>
                </div>
                <p style="color: #dc2626; font-weight: bold; margin-top: 10px;">
                    This action cannot be undone!
                </p>
            </div>
        `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Revert Selected',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve, reject) => {
                        const formData = new FormData();
                        formData.append('payroll_id', currentPayrollId);
                        formData.append('_token', '{{ csrf_token() }}');

                        Array.from(window.selectedRevertIds).forEach(id => {
                            formData.append('selected_employees[]', id);
                        });

                        $.ajax({
                            url: '/payroll/payroll-new/unprocess-selected-salaries',
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                resolve(response);
                            },
                            error: function(xhr) {
                                reject(new Error(xhr.responseText || 'Revert failed'));
                            }
                        });
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const response = result.value;

                    if (response.success) {
                        showSuccessOverlay(
                            'Salary Reverted',
                            `${window.selectedRevertIds.size} employee(s) returned to pending state`,
                            'check'
                        );

                        setTimeout(() => {
                            loadStep3Data();
                            showView('STEP3');
                        }, 2000);
                    } else {
                        Swal.fire({
                            title: 'Revert Failed',
                            text: response.message || 'Failed to revert salaries.',
                            icon: 'error'
                        });
                    }
                }
            }).catch((error) => {
                Swal.fire({
                    title: 'Error',
                    text: error.message || 'Failed to revert salaries. Please try again.',
                    icon: 'error'
                });
            });
        }

        // Helper function for generating request cards
        function generateRequestCards(data) {
            let cards = '';

            // Missed Punches
            if (data.missed_punches && data.missed_punches.count > 0) {
                cards += `
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; border-left: 4px solid #f59e0b;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <div style="padding: 6px; background: #fef3c7; border-radius: 6px; color: #d97706;">
                            <i data-lucide="alert-circle" style="width: 16px; height: 16px;"></i>
                        </div>
                        <div style="flex: 1;">
                            <strong style="font-size: 0.875rem;">Missed Punches</strong>
                            <p style="font-size: 0.75rem; color: #64748b; margin: 2px 0;">Incomplete attendance records</p>
                        </div>
                        <span style="background: #fef3c7; color: #92400e; font-weight: bold; padding: 2px 8px; border-radius: 9999px; font-size: 0.75rem;">
                            ${data.missed_punches.count}
                        </span>
                    </div>
                    ${data.missed_punches.details && data.missed_punches.details.length > 0 ? `
                                                <div style="font-size: 0.75rem; color: #64748b; padding: 6px; background: #f8fafc; border-radius: 4px;">
                                                    Example: ${data.missed_punches.details[0].employee_name} (${data.missed_punches.details[0].employee_code}) - ${data.missed_punches.details[0].date}
                                                </div>
                                            ` : ''}
                </div>
            `;
            }

            // Leave Requests
            if (data.leave_requests && data.leave_requests.count > 0) {
                cards += `
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; border-left: 4px solid #f43f5e;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <div style="padding: 6px; background: #fee2e2; border-radius: 6px; color: #dc2626;">
                            <i data-lucide="calendar" style="width: 16px; height: 16px;"></i>
                        </div>
                        <div style="flex: 1;">
                            <strong style="font-size: 0.875rem;">Leave Requests</strong>
                            <p style="font-size: 0.75rem; color: #64748b; margin: 2px 0;">Pending manager approval</p>
                        </div>
                        <span style="background: #fee2e2; color: #b91c1c; font-weight: bold; padding: 2px 8px; border-radius: 9999px; font-size: 0.75rem;">
                            ${data.leave_requests.count}
                        </span>
                    </div>
                </div>
            `;
            }

            // Over Time Requests
            if (data.overtime_requests && data.overtime_requests.count > 0) {
                cards += `
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; border-left: 4px solid #8b5cf6;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <div style="padding: 6px; background: #ede9fe; border-radius: 6px; color: #7c3aed;">
                            <i data-lucide="clock" style="width: 16px; height: 16px;"></i>
                        </div>
                        <div style="flex: 1;">
                            <strong style="font-size: 0.875rem;">Over Time Requests</strong>
                            <p style="font-size: 0.75rem; color: #64748b; margin: 2px 0;">Overtime approvals pending</p>
                        </div>
                        <span style="background: #ede9fe; color: #7c3aed; font-weight: bold; padding: 2px 8px; border-radius: 9999px; font-size: 0.75rem;">
                            ${data.overtime_requests.count}
                        </span>
                    </div>
                </div>
            `;
            }

            // Comp Off Requests
            if (data.comp_off_requests && data.comp_off_requests.count > 0) {
                cards += `
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; border-left: 4px solid #10b981;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <div style="padding: 6px; background: #d1fae5; border-radius: 6px; color: #059669;">
                            <i data-lucide="calendar" style="width: 16px; height: 16px;"></i>
                        </div>
                        <div style="flex: 1;">
                            <strong style="font-size: 0.875rem;">Comp Off Requests</strong>
                            <p style="font-size: 0.75rem; color: #64748b; margin: 2px 0;">Compensatory off approvals</p>
                        </div>
                        <span style="background: #d1fae5; color: #059669; font-weight: bold; padding: 2px 8px; border-radius: 9999px; font-size: 0.75rem;">
                            ${data.comp_off_requests.count}
                        </span>
                    </div>
                </div>
            `;
            }

            return cards;
        }
</script>
@endsection
