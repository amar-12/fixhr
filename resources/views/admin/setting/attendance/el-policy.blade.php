@extends('admin.layout.master')
@section('title')
    Earned Leave
@endsection

@section('css')
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
        }
        
        .policy-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, #34495e 100%);
            color: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .policy-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--border-color);
            padding: 20px 25px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .card-header i {
            margin-right: 10px;
            color: var(--secondary-color);
        }
        
        .card-body {
            padding: 25px;
        }
        
        .form-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid var(--secondary-color);
            position: relative;
        }
        
        .form-section h5 {
            margin-bottom: 20px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
        }
        
        .form-section h5 i {
            margin-right: 10px;
            color: var(--secondary-color);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 8px;
        }
        
        .form-text {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .statutory-badge {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(231, 76, 60, 0.2);
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-left: 8px;
        }
        
        .recommended-badge {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(39, 174, 96, 0.2);
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-left: 8px;
        }
        
        .action-buttons {
            background: white;
            padding: 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }
        
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 12px 24px;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
        }
        
        .nav-tabs .nav-link:hover {
            color: var(--secondary-color);
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .nav-tabs .nav-link.active {
            color: var(--secondary-color);
            border-bottom: 3px solid var(--secondary-color);
            background-color: transparent;
        }
        
        .tab-content {
            padding: 25px 0;
        }
        
        .policy-preview {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 25px;
            border: 1px dashed #dee2e6;
        }
        
        .policy-preview h6 {
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        
        .preview-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .preview-item:last-child {
            border-bottom: none;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: var(--success-color);
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(30px);
        }
        
        .employee-group-badge {
            display: inline-flex;
            align-items: center;
            background-color: #e9ecef;
            padding: 5px 12px;
            border-radius: 20px;
            margin: 0 5px 5px 0;
            font-size: 0.85rem;
        }
        
        .employee-group-badge .remove {
            margin-left: 8px;
            cursor: pointer;
            color: #6c757d;
        }
        
        .employee-group-badge .remove:hover {
            color: var(--danger-color);
        }
        
        .compliance-check {
            display: flex;
            align-items: center;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        
        .compliance-check i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .compliance-check.valid i {
            color: var(--success-color);
        }
        
        .compliance-check.warning i {
            color: var(--warning-color);
        }
        
        .compliance-check.error i {
            color: var(--danger-color);
        }
        
        @media (max-width: 992px) {
            .policy-container {
                padding: 10px;
            }
            
            .header-card {
                padding: 15px;
            }
            
            .card-body {
                padding: 15px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="policy-container">
        <div class="header-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1 class="h3 mb-2"><i class="bi bi-gear me-2"></i>EL Policy Configuration</h1>
                    <p class="mb-0 opacity-75">Configure Earned Leave policies for your organization</p>
                </div>
                <div class="text-end">
                    <div class="badge bg-light text-dark mb-2">Policy ID:</div>
                    <div class="d-flex">
                        <div class="form-check form-switch me-3">
                            <input class="form-check-input" type="checkbox" id="policyStatus">
                            <label class="form-check-label text-white" for="policyStatus">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Main Configuration Area -->
            <div class="col-12">
                <!-- Configuration Tabs -->
                <div class="policy-card">
                    <div class="card-header">
                        <ul class="nav nav-tabs" id="policyTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button">
                                    <i class="bi bi-info-circle"></i> Basic
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="accrual-tab" data-bs-toggle="tab" data-bs-target="#accrual" type="button">
                                    <i class="bi bi-calculator"></i> Accrual
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="balance-tab" data-bs-toggle="tab" data-bs-target="#el_balance" type="button">
                                    <i class="bi bi-graph-up"></i> Balance
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="carry-tab" data-bs-toggle="tab" data-bs-target="#carry" type="button">
                                    <i class="bi bi-arrow-right-circle"></i> Carry Forward
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="encashment-tab" data-bs-toggle="tab" data-bs-target="#encashment" type="button">
                                    <i class="bi bi-cash-stack"></i> Encashment
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="advanced-tab" data-bs-toggle="tab" data-bs-target="#advanced" type="button">
                                    <i class="bi bi-sliders"></i> Advanced
                                </button>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="card-body">
                        <form id="policyForm" method="POST" action="">
                            @csrf
                            <div class="tab-content" id="policyTabsContent">
                                <!-- Basic Settings Tab -->
                                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                    <div class="form-section">
                                        <h5><i class="bi bi-info-square"></i> Basic Information</h5>
                                        <span class="section-info" data-bs-toggle="tooltip" title="Basic policy identification details">
                                            <i class="bi bi-info-circle"></i>
                                        </span>
                                        
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Policy Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="policy_name" value="" placeholder="Enter policy name" required>
                                                <div class="form-text">Unique name for this policy</div>
                                                    <div class="text-danger"></div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Policy Code <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="policy_code" value="" placeholder="e.g., EL-STD-001" required>
                                                <div class="form-text">Unique identifier for the policy</div>
                                                <div class="text-danger"></div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Financial Year Cycle</label>
                                                <select class="form-select" name="financial_year_cycle">
                                                    <option value="apr-mar">April to March</option>
                                                    <option value="jan-dec">January to December</option>
                                                    <option value="custom">Custom Financial Year</option>
                                                </select>
                                                <div class="form-text">For carry forward and year-end calculations</div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Effective From <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="effective_from" value="" required>
                                                <div class="form-text">Date when policy becomes active</div>
                                                <div class="text-danger"></div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Effective To</label>
                                                <input type="date" class="form-control" name="effective_to" value="">
                                                <div class="form-text">Leave blank for indefinite policy</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-section">
                                        <h5><i class="bi bi-building"></i> Applicability</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Employee Groups</label>
                                                <select class="form-select" multiple name="employee_groups[]" id="employeeGroups">
                                                    <option value="all">All Employees</option>
                                                    <option value="permanent">Permanent Employees</option>
                                                    <option value="contract">Contract Employees</option>
                                                    <option value="probation">Probation Employees</option>
                                                    <option value="it">IT Department</option>
                                                    <option value="sales">Sales Department</option>
                                                    <option value="manufacturing">Manufacturing</option>
                                                </select>
                                                <div class="form-text">Select groups this policy applies to</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Locations</label>
                                                <select class="form-select" multiple name="locations[]">
                                                    <option value="all">All Locations</option>
                                                    @foreach(['mumbai', 'delhi', 'bangalore', 'chennai', 'hyderabad'] as $location)
                                                        <option value="{{ $location }}">{{ ucfirst($location) }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="form-text">Select locations for this policy</div>
                                            </div>
                                        </div>
                                        
                                        <div class="selected-groups mt-2" id="selectedGroups">
                                            <!-- Dynamic badges will appear here -->
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Accrual Rules Tab -->
                                <div class="tab-pane fade" id="accrual" role="tabpanel">
                                    <div class="form-section">
                                        <h5><i class="bi bi-calendar-plus"></i> Accrual Method</h5>
                                        <span class="section-info" data-bs-toggle="tooltip" title="How employees earn leave over time">
                                            <i class="bi bi-info-circle"></i>
                                        </span>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="accrual_method" id="monthlyFixed" value="monthly">
                                                    <label class="form-check-label" for="monthlyFixed">Monthly Fixed Rate</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="accrual_method" id="daysWorked" value="days_worked">
                                                    <label class="form-check-label" for="daysWorked">Based on Days Worked <span class="statutory-badge">Statutory</span></label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="accrual_method" id="customFormula" value="custom">
                                                    <label class="form-check-label" for="customFormula">Custom Formula</label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Monthly Fixed Rate Options -->
                                        <div id="monthlyOptions">
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Monthly Accrual Rate <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" name="monthly_rate" value="" step="0.25" min="0" max="5">
                                                        <span class="input-group-text">days/month</span>
                                                    </div>
                                                    <div class="form-text">Standard: 1.25 days/month (15 days/year) <span class="recommended-badge">Recommended</span></div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Annual Entitlement</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="annualEntitlement" value="" readonly>
                                                        <span class="input-group-text">days/year</span>
                                                    </div>
                                                    <div class="form-text">Calculated from monthly rate</div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Accrual Day</label>
                                                    <select class="form-select" name="accrual_day">
                                                        <option value="1">1st of Month</option>
                                                        <option value="last">Last Day of Month</option>
                                                        <option value="joining">Joining Date</option>
                                                        <option value="custom">Custom Day</option>
                                                    </select>
                                                    <div class="form-text">When accrual is calculated</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Days Worked Options -->
                                        <div id="daysWorkedOptions" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Days Worked for 1 EL <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" name="days_for_one_el" value="" min="1" max="30">
                                                        <span class="input-group-text">days</span>
                                                    </div>
                                                    <div class="form-text">As per Factories Act: 1 day per 20 days worked <span class="statutory-badge">Statutory</span></div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Maximum Annual Accrual</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" name="max_annual_accrual" value="" min="0" max="45">
                                                        <span class="input-group-text">days/year</span>
                                                    </div>
                                                    <div class="form-text">Maximum EL that can be earned in a year</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Rounding Method</label>
                                                <select class="form-select" name="rounding_method">
                                                    <option value="floor">Round Down (Floor)</option>
                                                    <option value="ceil">Round Up (Ceil)</option>
                                                    <option value="half">Round to Nearest 0.5</option>
                                                    <option value="exact">No Rounding (Exact)</option>
                                                </select>
                                                <div class="form-text">How to handle fractional days</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Pro-rata Basis</label>
                                                <select class="form-select" name="pro_rata_basis">
                                                    <option value="calendar">Calendar Days</option>
                                                    <option value="working">Working Days</option>
                                                    <option value="custom">Custom Formula</option>
                                                </select>
                                                <div class="form-text">For joining/exit mid-period</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-section">
                                        <h5><i class="bi bi-person-badge"></i> Probation & Special Cases</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">EL Accrual During Probation</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="probation_eligibility_percentage" value="" min="0" max="100">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                                <div class="form-text">Percentage of regular accrual rate</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Accrual Start</label>
                                                <select class="form-select" name="accrual_start">
                                                    <option value="joining">From Joining Date</option>
                                                    <option value="confirmation">After Confirmation</option>
                                                    <option value="next_month">Next Month after Joining</option>
                                                    <option value="financial_year">Next Financial Year</option>
                                                </select>
                                                <div class="form-text">When accrual begins for new employees</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="policy-preview">
                                        <h6><i class="bi bi-eye me-2"></i>Accrual Preview</h6>
                                        <div class="preview-item">
                                            <span>Employee works full month:</span>
                                            <span class="fw-bold" id="previewFullMonth">1.25 days EL earned</span>
                                        </div>
                                        <div class="preview-item">
                                            <span>Employee joins on 15th:</span>
                                            <span class="fw-bold" id="previewProRata">0.63 days EL (pro-rata)</span>
                                        </div>
                                        <div class="preview-item">
                                            <span>Annual entitlement:</span>
                                            <span class="fw-bold" id="previewAnnual">15 days (1.25 × 12)</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Balance & Restrictions Tab -->
                                <div class="tab-pane fade" id="el_balance" role="tabpanel">
                                    <div class="form-section">
                                        <h5><i class="bi bi-shield-check"></i> Balance Limits</h5>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Maximum Balance <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="max_balance" value="" min="0" max="100">
                                                    <span class="input-group-text">days</span>
                                                </div>
                                                <div class="form-text">Maximum EL balance employee can have <span class="recommended-badge">Recommended</span></div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Minimum Balance to Apply</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="min_balance_to_apply" value="" step="0.5" min="0">
                                                    <span class="input-group-text">days</span>
                                                </div>
                                                <div class="form-text">Minimum balance required to apply for leave</div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Minimum Application Days</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="min_application_days" value="" step="0.5" min="0">
                                                    <span class="input-group-text">days</span>
                                                </div>
                                                <div class="form-text">Minimum leave that can be applied at once</div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Maximum Continuous Leave</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="max_continuous_days" value="" min="1" max="30">
                                                    <span class="input-group-text">days</span>
                                                </div>
                                                <div class="form-text">Maximum leave in single application</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Advance Leave Allowed</label>
                                                <div class="d-flex align-items-center">
                                                    <label class="toggle-switch me-3">
                                                        <input type="checkbox" name="allow_advance_leave" id="advanceLeave">
                                                        <span class="toggle-slider"></span>
                                                    </label>
                                                    <div>
                                                        <div>Allow employees to take leave in advance</div>
                                                        <small class="text-muted">Negative balance allowed</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-section">
                                        <h5><i class="bi bi-calendar-x"></i> Application Restrictions</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Backdated Applications</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="max_backdated_days" value="" min="0" max="30">
                                                    <span class="input-group-text">days</span>
                                                </div>
                                                <div class="form-text">Maximum days back for leave application</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Future Applications</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="max_future_days" value="" min="0" max="365">
                                                    <span class="input-group-text">days</span>
                                                </div>
                                                <div class="form-text">Maximum days ahead for leave application</div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="prevent_sandwich_leave" id="preventSandwich">
                                                    <label class="form-check-label" for="preventSandwich">
                                                        Prevent Sandwich Leave
                                                    </label>
                                                </div>
                                                <div class="form-text">Prevent leave between working days</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="block_month_end" id="blockMonthEnd">
                                                    <label class="form-check-label" for="blockMonthEnd">
                                                        Restrict Month-End Leaves
                                                    </label>
                                                </div>
                                                <div class="form-text">Restrict leave on last working day</div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Blackout Periods</label>
                                            <textarea class="form-control" name="blackout_periods" rows="3" placeholder="Add blackout periods (e.g., '25 Dec - 1 Jan: Year-end closing')">jj</textarea>
                                            <div class="form-text">Periods when leave is not allowed (one per line)</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Carry Forward Tab -->
                                <div class="tab-pane fade" id="carry" role="tabpanel">
                                    <div class="form-section">
                                        <h5><i class="bi bi-arrow-right-circle"></i> Carry Forward Rules</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="enable_carry_forward" id="enableCarryForward">
                                                    <label class="form-check-label" for="enableCarryForward">
                                                        Enable Carry Forward
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div id="carryForwardOptions">
                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Carry Forward Method</label>
                                                    <select class="form-select" name="carry_forward_method" id="carryMethod">
                                                        <option value="fixed">Fixed Cap</option>
                                                        <option value="percentage">Percentage of Balance</option>
                                                        <option value="hybrid">Hybrid (Cap + Percentage)</option>
                                                        <option value="unlimited">Unlimited</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Carry Forward Limit</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" name="carry_forward_cap" id="carryCap" value="" min="0" max="100">
                                                        <span class="input-group-text">days</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Percentage</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" name="carry_forward_percentage" id="carryPercentage" value="" min="0" max="100" disabled>
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Carry Forward Date</label>
                                                    <select class="form-select" name="carry_forward_date">
                                                        <option value="mar31">31st March (FY End)</option>
                                                        <option value="dec31">31st December</option>
                                                        <option value="joining">Joining Anniversary</option>
                                                        <option value="custom">Custom Date</option>
                                                    </select>
                                                    <div class="form-text">When carry forward calculation runs</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Validity Period</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" name="carry_forward_validity_months" value="" min="1" max="24">
                                                        <span class="input-group-text">months</span>
                                                    </div>
                                                    <div class="form-text">Carried leaves expire after this period</div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Excess Balance Handling</label>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="excess_handling" id="excessLapse" value="lapse">
                                                        <label class="form-check-label" for="excessLapse">
                                                            Automatically Lapse Excess Balance
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="excess_handling" id="excessEncash" value="encash">
                                                        <label class="form-check-label" for="excessEncash">
                                                            Automatically Encash Excess Balance
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="excess_handling" id="excessCarryExtra" value="carry_extra">
                                                        <label class="form-check-label" for="excessCarryExtra">
                                                            Allow Extra Carry with Approval
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="policy-preview">
                                            <h6><i class="bi bi-eye me-2"></i>Carry Forward Example</h6>
                                            <div class="preview-item">
                                                <span>Year-end balance:</span>
                                                <span class="fw-bold" id="carryExampleBalance">42 days</span>
                                            </div>
                                            <div class="preview-item">
                                                <span>Carry forward (50% or 30 days):</span>
                                                <span class="fw-bold" id="carryExampleResult">21 days (50% of 42)</span>
                                            </div>
                                            <div class="preview-item">
                                                <span>Excess balance:</span>
                                                <span class="fw-bold text-danger" id="carryExampleExcess">Will be lapsed</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Encashment Tab -->
                                <div class="tab-pane fade" id="encashment" role="tabpanel">
                                    <div class="form-section">
                                        <h5><i class="bi bi-cash-stack"></i> Encashment Rules</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="enable_encashment" id="enableEncashment">
                                                    <label class="form-check-label" for="enableEncashment">
                                                        Enable Leave Encashment
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div id="encashmentOptions">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Encashment Basis</label>
                                                    <select class="form-select" name="encashment_basis" id="encashmentBasis">
                                                        <option value="basic">Basic Salary</option>
                                                        <option value="basic_da">Basic + Dearness Allowance</option>
                                                        <option value="gross">Gross Salary</option>
                                                        <option value="ctc">Cost to Company</option>
                                                    </select>
                                                    <div class="form-text">Salary component used for calculation</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Calculation Formula</label>
                                                    <select class="form-select" name="encashment_formula" id="encashmentFormula">
                                                        <option value="standard">(Basic + DA) / 26</option>
                                                        <option value="calendar">Monthly Salary / 30</option>
                                                        <option value="working">Monthly Salary / Working Days</option>
                                                        <option value="custom">Custom Formula</option>
                                                    </select>
                                                    <div class="form-text">Standard formula follows Indian payroll practices</div>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Minimum Balance for Encashment</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" name="min_balance_for_encashment" value="" min="0" max="30">
                                                        <span class="input-group-text">days</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Maximum Encashment per Year</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" name="max_encashment_per_year" value="" min="0" max="45">
                                                        <span class="input-group-text">days</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Tax Deduction</label>
                                                    <select class="form-select" name="tax_deduction">
                                                        <option value="yes">Apply TDS (Income Tax)</option>
                                                        <option value="no">No Tax Deduction</option>
                                                        <option value="threshold">Above Threshold Only</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Encashment Frequency</label>
                                                    <select class="form-select" name="encashment_frequency">
                                                        <option value="anytime">Anytime (with approval)</option>
                                                        <option value="yearly">Once per Year</option>
                                                        <option value="halfyearly">Twice per Year</option>
                                                        <option value="exit">Only at Exit</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Require Approval</label>
                                                    <div class="d-flex align-items-center">
                                                        <label class="toggle-switch me-3">
                                                            <input type="checkbox" name="encashment_require_approval" id="encashmentApproval">
                                                            <span class="toggle-slider"></span>
                                                        </label>
                                                        <div>
                                                            <div>Manager/HR approval required</div>
                                                            <small class="text-muted">For all encashment requests</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="policy-preview">
                                                <h6><i class="bi bi-eye me-2"></i>Encashment Example</h6>
                                                <div class="preview-item">
                                                    <span>Basic Salary:</span>
                                                    <span class="fw-bold">₹50,000</span>
                                                </div>
                                                <div class="preview-item">
                                                    <span>DA:</span>
                                                    <span class="fw-bold">₹10,000</span>
                                                </div>
                                                <div class="preview-item">
                                                    <span>Per day rate ((50,000+10,000)/26):</span>
                                                    <span class="fw-bold">₹2,307.69</span>
                                                </div>
                                                <div class="preview-item">
                                                    <span>Encash 10 days:</span>
                                                    <span class="fw-bold text-success">₹23,076.90</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Advanced Settings Tab -->
                                <div class="tab-pane fade" id="advanced" role="tabpanel">
                                    <div class="form-section">
                                        <h5><i class="bi bi-code-slash"></i> Integration & Automation</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="payroll_integration" id="payrollIntegration">
                                                    <label class="form-check-label" for="payrollIntegration">
                                                        Payroll Integration
                                                    </label>
                                                </div>
                                                <div class="form-text">Sync leave encashment with payroll system</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="attendance_integration" id="attendanceIntegration">
                                                    <label class="form-check-label" for="attendanceIntegration">
                                                        Attendance Integration
                                                    </label>
                                                </div>
                                                <div class="form-text">Auto-mark attendance during approved leave</div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Auto-Accrual Schedule</label>
                                                <select class="form-select" name="auto_accrual_schedule">
                                                    <option value="monthly">Monthly (1st day)</option>
                                                    <option value="weekly">Weekly</option>
                                                    <option value="daily">Daily</option>
                                                    <option value="custom">Custom Cron Schedule</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Notification Triggers</label>
                                                <select class="form-select" name="notification_triggers[]" multiple>
                                                    <option value="before_leave">Before Leave Starts</option>
                                                    <option value="after_approval">After Leave Approval</option>
                                                    <option value="low_balance">When Balance is Low</option>
                                                    <option value="monthly_statement">Monthly Balance Statement</option>
                                                    <option value="year_end">Before Year-End Processing</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-section">
                                        <h5><i class="bi bi-shield-check"></i> Compliance Settings</h5>
                                        <div class="compliance-check valid">
                                            <i class="bi bi-check-circle"></i>
                                            <div>
                                                <div class="fw-bold">Factories Act, 1948</div>
                                                <small>Minimum 1 day EL per 20 days worked (max 30 days/year)</small>
                                            </div>
                                        </div>
                                        
                                        <div class="compliance-check warning">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            <div>
                                                <div class="fw-bold">Shops & Establishments Act</div>
                                                <small>Varies by state - configure location-specific rules</small>
                                            </div>
                                        </div>
                                        
                                        <div class="compliance-check error">
                                            <i class="bi bi-x-circle"></i>
                                            <div>
                                                <div class="fw-bold">Income Tax Rules</div>
                                                <small>Leave encashment taxable as income - TDS required</small>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <label class="form-label">Statutory Compliance Mode</label>
                                            <select class="form-select" name="statutory_compliance_mode">
                                                <option value="auto">Auto-detect based on location</option>
                                                <option value="factories">Factories Act Compliant</option>
                                                <option value="shops">Shops & Establishments Act</option>
                                                <option value="custom">Custom Compliance Rules</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-section">
                                        <h5><i class="bi bi-database"></i> Data & Audit</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Audit Log Retention</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="audit_log_retention_years" value="" min="1" max="10">
                                                    <span class="input-group-text">years</span>
                                                </div>
                                                <div class="form-text">How long to keep policy change logs</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Balance History</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="balance_history_years" value="" min="1" max="10">
                                                    <span class="input-group-text">years</span>
                                                </div>
                                                <div class="form-text">How long to keep employee balance history</div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="enable_audit_trail" id="enableAuditTrail">
                                                    <label class="form-check-label" for="enableAuditTrail">
                                                        Enable Detailed Audit Trail
                                                    </label>
                                                </div>
                                                <div class="form-text">Log all policy changes and balance adjustments</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <div>
                            <a href="" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                        </div>
                        <div>
                            <button type="submit" form="policyForm" class="btn btn-primary me-2" id="savePolicy">
                                <i class="bi bi-check-circle me-1"></i> Save Policy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Handle accrual method toggle
        const monthlyOptions = document.getElementById('monthlyOptions');
        const daysWorkedOptions = document.getElementById('daysWorkedOptions');
        const monthlyRateInput = document.querySelector('input[name="monthly_rate"]');
        const annualEntitlement = document.getElementById('annualEntitlement');
        const previewFullMonth = document.getElementById('previewFullMonth');
        const previewProRata = document.getElementById('previewProRata');
        const previewAnnual = document.getElementById('previewAnnual');
        
        function updateAccrualPreview() {
            const monthlyRate = parseFloat(monthlyRateInput.value) || 1.25;
            const annual = monthlyRate * 12;
            const proRata = (monthlyRate / 2).toFixed(2);
            
            annualEntitlement.value = annual.toFixed(2);
            previewFullMonth.textContent = `${monthlyRate} days EL earned`;
            previewProRata.textContent = `${proRata} days EL (pro-rata)`;
            previewAnnual.textContent = `${annual} days (${monthlyRate} × 12)`;
        }
        
        document.getElementById('monthlyFixed').addEventListener('change', function() {
            if (this.checked) {
                monthlyOptions.style.display = 'block';
                daysWorkedOptions.style.display = 'none';
                updateAccrualPreview();
            }
        });
        
        document.getElementById('daysWorked').addEventListener('change', function() {
            if (this.checked) {
                monthlyOptions.style.display = 'none';
                daysWorkedOptions.style.display = 'block';
            }
        });
        
        document.getElementById('customFormula').addEventListener('change', function() {
            if (this.checked) {
                monthlyOptions.style.display = 'none';
                daysWorkedOptions.style.display = 'none';
            }
        });
        
        monthlyRateInput.addEventListener('input', updateAccrualPreview);
        
        // Handle carry forward method
        const carryMethod = document.getElementById('carryMethod');
        const carryPercentage = document.getElementById('carryPercentage');
        const carryCap = document.getElementById('carryCap');
        const carryExampleBalance = document.getElementById('carryExampleBalance');
        const carryExampleResult = document.getElementById('carryExampleResult');
        const carryExampleExcess = document.getElementById('carryExampleExcess');
        
        function updateCarryExample() {
            const method = carryMethod.value;
            const cap = parseFloat(carryCap.value) || 30;
            const percentage = parseFloat(carryPercentage.value) || 50;
            const balance = 42; // Example balance
            
            let carryDays = 0;
            let excessDays = 0;
            
            if (method === 'fixed') {
                carryDays = Math.min(balance, cap);
                excessDays = balance - carryDays;
            } else if (method === 'percentage') {
                carryDays = Math.min(balance, balance * (percentage / 100));
                excessDays = balance - carryDays;
            } else if (method === 'hybrid') {
                const byPercentage = balance * (percentage / 100);
                carryDays = Math.min(byPercentage, cap);
                excessDays = balance - carryDays;
            } else if (method === 'unlimited') {
                carryDays = balance;
                excessDays = 0;
            }
            
            carryExampleResult.textContent = `${carryDays} days (${method === 'fixed' ? 'max ' + cap : method === 'percentage' ? percentage + '%' : percentage + '% or max ' + cap})`;
            carryExampleExcess.textContent = excessDays > 0 ? `${excessDays} days will be lapsed` : 'No excess';
        }
        
        carryMethod.addEventListener('change', function() {
            if (this.value === 'percentage' || this.value === 'hybrid') {
                carryPercentage.disabled = false;
            } else {
                carryPercentage.disabled = true;
            }
            updateCarryExample();
        });
        
        carryCap.addEventListener('input', updateCarryExample);
        carryPercentage.addEventListener('input', updateCarryExample);
        
        // Handle enable/disable sections
        const enableCarryForward = document.getElementById('enableCarryForward');
        const carryForwardOptions = document.getElementById('carryForwardOptions');
        const enableEncashment = document.getElementById('enableEncashment');
        const encashmentOptions = document.getElementById('encashmentOptions');
        
        function toggleSection(checkbox, section) {
            section.style.display = checkbox.checked ? 'block' : 'none';
        }
        
        enableCarryForward.addEventListener('change', function() {
            toggleSection(this, carryForwardOptions);
        });
        
        enableEncashment.addEventListener('change', function() {
            toggleSection(this, encashmentOptions);
        });
        
        // Handle employee groups selection
        const employeeGroups = document.getElementById('employeeGroups');
        const selectedGroups = document.getElementById('selectedGroups');
        
        function updateSelectedGroups() {
            const selected = Array.from(employeeGroups.selectedOptions).map(opt => opt.text);
            selectedGroups.innerHTML = '';
            
            selected.forEach(group => {
                const badge = document.createElement('div');
                badge.className = 'employee-group-badge';
                badge.innerHTML = `${group} <span class="remove">&times;</span>`;
                selectedGroups.appendChild(badge);
                
                // Add remove functionality
                badge.querySelector('.remove').addEventListener('click', function() {
                    badge.remove();
                    // Also unselect from dropdown
                    const option = Array.from(employeeGroups.options).find(opt => opt.text === group);
                    if (option) option.selected = false;
                });
            });
        }
        
        employeeGroups.addEventListener('change', updateSelectedGroups);
        
        // Handle policy save buttons
        document.getElementById('saveDraft').addEventListener('click', function() {
            // Add draft flag to form
            const draftInput = document.createElement('input');
            draftInput.type = 'hidden';
            draftInput.name = 'is_draft';
            draftInput.value = '1';
            document.getElementById('policyForm').appendChild(draftInput);
            
            // Submit form
            document.getElementById('policyForm').submit();
        });
        
        document.getElementById('saveAndAssign').addEventListener('click', function() {
            // Add assign flag to form
            const assignInput = document.createElement('input');
            assignInput.type = 'hidden';
            assignInput.name = 'assign_after_save';
            assignInput.value = '1';
            document.getElementById('policyForm').appendChild(assignInput);
            
            // Submit form
            document.getElementById('policyForm').submit();
        });
        
        // Form validation
        const policyForm = document.getElementById('policyForm');
        policyForm.addEventListener('submit', function(e) {
            const policyName = document.querySelector('input[name="policy_name"]').value;
            const policyCode = document.querySelector('input[name="policy_code"]').value;
            
            if (!policyName.trim() || !policyCode.trim()) {
                e.preventDefault();
                alert('Please fill in all required fields (Policy Name and Policy Code)');
                return false;
            }
            
            return true;
        });
        
        // Initialize with default values
        toggleSection(enableCarryForward, carryForwardOptions);
        toggleSection(enableEncashment, encashmentOptions);
        updateSelectedGroups();
        updateAccrualPreview();
        updateCarryExample();
        
        // Set initial accrual method display
        const initialAccrualMethod = document.querySelector('input[name="accrual_method"]:checked').value;
        if (initialAccrualMethod === 'monthly') {
            monthlyOptions.style.display = 'block';
            daysWorkedOptions.style.display = 'none';
        } else if (initialAccrualMethod === 'days_worked') {
            monthlyOptions.style.display = 'none';
            daysWorkedOptions.style.display = 'block';
        } else {
            monthlyOptions.style.display = 'none';
            daysWorkedOptions.style.display = 'none';
        }
        
        // Set initial carry forward method
        const initialCarryMethod = document.getElementById('carryMethod').value;
        if (initialCarryMethod === 'percentage' || initialCarryMethod === 'hybrid') {
            carryPercentage.disabled = false;
        } else {
            carryPercentage.disabled = true;
        }
    });
</script>
@endsection
