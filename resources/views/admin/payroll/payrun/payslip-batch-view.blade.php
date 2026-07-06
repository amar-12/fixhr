@extends('admin.layout.master')

@section('title', 'Payslip Batch Preview - ' . ($payrollPeriod->pp_name ?? 'Payroll'))

@section('css')
    <style>
        /* Payslip Batch Preview Specific Styles */
        .payslip-preview-container {
            margin-top: 30px;
        }
        
        .payslip-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        /* Payslip Card Styling */
        .payslip-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .payslip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
        }
        
        /* Payslip Header (Looks like actual payslip) */
        .payslip-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
            padding: 20px;
            position: relative;
        }
        
        .payslip-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #fbbf24 0%, #f59e0b 100%);
        }
        
        .company-name {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .payslip-title {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 15px;
        }
        
        /* Payslip Content */
        .payslip-content {
            padding: 20px;
        }
        
        .employee-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .employee-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.25rem;
            color: white;
        }
        
        .employee-details h4 {
            margin: 0 0 5px 0;
            font-size: 1rem;
            color: #1e293b;
        }
        
        .employee-details p {
            margin: 0;
            font-size: 0.8rem;
            color: #64748b;
        }
        
        /* Salary Breakdown */
        .salary-breakdown {
            margin-bottom: 20px;
        }
        
        .breakdown-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #f1f5f9;
        }
        
        .breakdown-row.total {
            border-top: 2px solid #e2e8f0;
            border-bottom: none;
            padding-top: 12px;
            margin-top: 8px;
            font-weight: 700;
        }
        
        .breakdown-label {
            color: #64748b;
            font-size: 0.85rem;
        }
        
        .breakdown-value {
            color: #1e293b;
            font-weight: 500;
            font-family: 'Courier New', monospace;
        }
        
        .breakdown-row.total .breakdown-value {
            color: #059669;
            font-size: 1.1rem;
        }
        
        /* Payslip Footer */
        .payslip-footer {
            padding: 15px 20px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .payslip-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-processed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        /* Batch Summary Card */
        .batch-summary-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            margin-bottom: 25px;
        }
        
        .summary-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        
        .stat-box {
            text-align: center;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            display: block;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: #64748b;
            text-transform: uppercase;
        }
        
        .stat-box.total .stat-value {
            color: #059669;
        }
        
        /* Load More Section */
        .load-more-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
        }
        
        .load-more-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .load-more-btn:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }
        
        .load-more-btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Side View All Button */
        .view-all-sidebar {
            position: fixed;
            right: 20px;
            top: 150px;
            z-index: 1000;
        }
        
        .view-all-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.3);
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .view-all-btn:hover {
            transform: translateX(-5px);
            box-shadow: 0 15px 30px -10px rgba(5, 150, 105, 0.4);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 2px dashed #e2e8f0;
        }
        
        .empty-icon {
            width: 80px;
            height: 80px;
            background: #e2e8f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .empty-title {
            color: #64748b;
            font-size: 1.25rem;
            margin-bottom: 10px;
        }
        
        .empty-message {
            color: #94a3b8;
            max-width: 400px;
            margin: 0 auto 25px;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .payslip-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
            
            .summary-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .payslip-grid {
                grid-template-columns: 1fr;
            }
            
            .summary-stats {
                grid-template-columns: 1fr;
            }
            
            .view-all-sidebar {
                position: static;
                margin-top: 20px;
            }
            
            .view-all-btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* Dark Mode Support */
        .dark-mode .payslip-card,
        .dark-mode .batch-summary-card {
            background: #1f2937;
            border-color: #374151;
        }
        
        .dark-mode .payslip-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        }
        
        .dark-mode .employee-details h4 {
            color: #f9fafb;
        }
        
        .dark-mode .employee-details p {
            color: #d1d5db;
        }
        
        .dark-mode .breakdown-label {
            color: #9ca3af;
        }
        
        .dark-mode .breakdown-value {
            color: #f9fafb;
        }
        
        .dark-mode .payslip-footer {
            background: #374151;
            border-color: #4b5563;
        }
        
        .dark-mode .stat-box {
            background: #374151;
            border-color: #4b5563;
        }
        
        .dark-mode .stat-value {
            color: #f9fafb;
        }
        
        .dark-mode .empty-state {
            background: #374151;
            border-color: #4b5563;
        }
        
        .dark-mode .empty-title {
            color: #d1d5db;
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
                    <li><a href="{{ route('payroll.new.process', ['period' => $payrollPeriod->pp_id ?? '']) }}">Salary Process</a></li>
                    <li class="active"><span><b>Payslip Batch Preview</b></span></li>
                </ol>
            </div>
            <div class="col-md-8 text-end">
                <div class="d-flex gap-2 justify-content-end">
                    <button onclick="downloadAllPayslips()" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="download"></i> Download All
                    </button>
                    <button onclick="printAllPayslips()" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2">
                        <i data-lucide="printer"></i> Print All
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- Breadcrumbs End --}}

    <!-- Side View All Button -->
    <div class="view-all-sidebar">
        <a href="{{ route('payroll.payslip.list', ['payrollId' => $payrollPeriod->pp_id]) }}" class="view-all-btn">
            <i data-lucide="list" style="width: 18px; height: 18px;"></i>
            View All Payslips
        </a>
    </div>

    <div class="row mt-4">
        <div class="col-xl-12">
            <div class="payslip-preview-container">
                <!-- Batch Summary Card -->
                <div class="batch-summary-card">
                    <div class="summary-header">
                        <div>
                            <h3 class="mb-1">{{ $payrollPeriod->pp_name ?? 'Payroll Cycle' }}</h3>
                            <p class="text-muted mb-0">{{ $monthName ?? 'Month' }} {{ date('Y') }}</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success fs-6">Processed</span>
                            <p class="text-muted mt-1 mb-0">Batch ID: PSB-{{ $payrollPeriod->pp_id ?? '001' }}</p>
                        </div>
                    </div>
                    
                    <div class="summary-stats">
                        <div class="stat-box">
                            <span class="stat-value">{{ $processedEmployees->count() ?? 0 }}</span>
                            <span class="stat-label">Processed</span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-value">{{ $totalEmployees - $processedEmployees->count() ?? 0 }}</span>
                            <span class="stat-label">Pending</span>
                        </div>
                        <div class="stat-box total">
                            <span class="stat-value">₹{{ number_format($totalNetSalary ?? 0, 2) }}</span>
                            <span class="stat-label">Total Net Pay</span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-value">{{ $totalEmployees ?? 0 }}</span>
                            <span class="stat-label">Total Employees</span>
                        </div>
                    </div>
                </div>

                <!-- Payslip Preview Grid -->
                @if($processedEmployees->count() > 0)
                    <div class="payslip-grid" id="payslipGrid">
                        @php
                            $initialLoad = 4; // Show 4 payslips initially
                            $loadIncrement = 4; // Load 4 more each time
                            $currentCount = 0;
                        @endphp
                        
                        @foreach($processedEmployees as $index => $employee)
                            @if($index < $initialLoad)
                                @include('admin.payroll.partials.payslip-card', ['employee' => $employee])
                            @endif
                            @php $currentCount++; @endphp
                        @endforeach
                    </div>

                    <!-- Load More Section -->
                    @if($processedEmployees->count() > $initialLoad)
                        <div class="load-more-section">
                            <button id="loadMoreBtn" class="load-more-btn" 
                                    data-current="{{ $initialLoad }}"
                                    data-total="{{ $processedEmployees->count() }}"
                                    data-increment="{{ $loadIncrement }}">
                                <i data-lucide="chevron-down" style="width: 18px; height: 18px;"></i>
                                Load More ({{ $processedEmployees->count() - $initialLoad }} remaining)
                            </button>
                            <p class="text-muted mt-2" id="remainingCount">
                                Showing {{ $initialLoad }} of {{ $processedEmployees->count() }} payslips
                            </p>
                        </div>
                    @endif
                @else
                    <!-- Empty State -->
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i data-lucide="file-text" style="width: 32px; height: 32px;" class="text-muted"></i>
                        </div>
                        <h4 class="empty-title">No payslips available for preview</h4>
                        <p class="empty-message">
                            Process salaries first to generate payslips for this payroll period.
                            Once processed, you'll be able to view, download, and print payslips here.
                        </p>
                        <a href="{{ route('payroll.new.process', ['period' => $payrollPeriod->pp_id ?? '']) }}" 
                           class="btn btn-primary d-flex align-items-center gap-2 mx-auto" style="width: fit-content;">
                            <i data-lucide="play-circle"></i> Process Salaries
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Payslip Card Partial Template (for JavaScript loading) -->
    <template id="payslipCardTemplate">
        <div class="payslip-card">
            <div class="payslip-header">
                <div class="company-name">YOUR COMPANY</div>
                <div class="payslip-title">PAYSLIP</div>
                <div style="display: flex; justify-content: space-between; font-size: 0.8rem;">
                    <div>
                        <div>Pay Period: {{ $monthName ?? 'Month' }}</div>
                        <div>Payment Date: {{ date('d M, Y') }}</div>
                    </div>
                    <div style="text-align: right;">
                        <div>Payslip #: <span id="payslipNumber">001</span></div>
                        <div>Status: <span class="payslip-status status-processed">Processed</span></div>
                    </div>
                </div>
            </div>
            
            <div class="payslip-content">
                <div class="employee-info">
                    <div class="employee-avatar" id="employeeInitial">JD</div>
                    <div class="employee-details">
                        <h4 id="employeeName">John Doe</h4>
                        <p id="employeeDetails">EMP001 • Software Engineer • IT Department</p>
                    </div>
                </div>
                
                <div class="salary-breakdown">
                    <div class="breakdown-row">
                        <span class="breakdown-label">Basic Salary</span>
                        <span class="breakdown-value" id="basicSalary">₹45,000.00</span>
                    </div>
                    <div class="breakdown-row">
                        <span class="breakdown-label">HRA</span>
                        <span class="breakdown-value" id="hra">₹22,500.00</span>
                    </div>
                    <div class="breakdown-row">
                        <span class="breakdown-label">Allowances</span>
                        <span class="breakdown-value" id="allowances">₹5,000.00</span>
                    </div>
                    <div class="breakdown-row">
                        <span class="breakdown-label">Deductions</span>
                        <span class="breakdown-value" id="deductions">₹8,000.00</span>
                    </div>
                    <div class="breakdown-row total">
                        <span class="breakdown-label">Net Salary</span>
                        <span class="breakdown-value" id="netSalary">₹64,500.00</span>
                    </div>
                </div>
            </div>
            
            <div class="payslip-footer">
                <div>
                    <button class="btn btn-sm btn-outline-primary" onclick="viewPayslip(this)" 
                            data-employee-id="EMP001">
                        <i data-lucide="eye" style="width: 14px; height: 14px;"></i> Preview
                    </button>
                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="downloadPayslip(this)"
                            data-employee-id="EMP001">
                        <i data-lucide="download" style="width: 14px; height: 14px;"></i> Download
                    </button>
                </div>
                <span class="payslip-status status-processed">
                    <i data-lucide="check-circle" style="width: 12px; height: 12px;"></i> Processed
                </span>
            </div>
        </div>
    </template>
@endsection

@section('script')
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        // Sample employee data for demo (replace with actual data from backend)
        const employeeData = [
            @foreach($processedEmployees as $employee)
            {
                id: "{{ $employee['id'] ?? $loop->iteration }}",
                name: "{{ $employee['name'] ?? 'Employee ' . $loop->iteration }}",
                code: "{{ $employee['code'] ?? 'EMP00' . $loop->iteration }}",
                designation: "{{ $employee['designation'] ?? 'Designation' }}",
                department: "{{ $employee['department'] ?? 'Department' }}",
                basic_salary: {{ $employee['basic_salary'] ?? 45000 }},
                hra: {{ $employee['hra'] ?? 22500 }},
                allowances: {{ $employee['allowances'] ?? 5000 }},
                deductions: {{ $employee['deductions'] ?? 8000 }},
                net_salary: {{ $employee['net_salary'] ?? 64500 }},
                status: "{{ $employee['status'] ?? 'processed' }}"
            },
            @endforeach
        ];
        
        // Load More Functionality
        document.getElementById('loadMoreBtn')?.addEventListener('click', function() {
            const btn = this;
            const current = parseInt(btn.dataset.current);
            const total = parseInt(btn.dataset.total);
            const increment = parseInt(btn.dataset.increment);
            
            // Disable button and show loading
            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = `
                <div class="spinner-border spinner-border-sm" role="status" style="width: 16px; height: 16px;"></div>
                Loading...
            `;
            
            // Simulate loading delay (replace with actual AJAX call)
            setTimeout(() => {
                // Load next batch of employees
                const nextBatch = Math.min(current + increment, total);
                
                // Generate payslip cards for the next batch
                for (let i = current; i < nextBatch && i < employeeData.length; i++) {
                    const emp = employeeData[i];
                    addPayslipCard(emp, i + 1);
                }
                
                // Update button state
                btn.dataset.current = nextBatch;
                
                if (nextBatch >= total) {
                    btn.style.display = 'none';
                    document.getElementById('remainingCount').innerHTML = 
                        `<i data-lucide="check-circle" style="width: 16px; height: 16px;"></i> All ${total} payslips loaded`;
                } else {
                    btn.innerHTML = `
                        <i data-lucide="chevron-down" style="width: 18px; height: 18px;"></i>
                        Load More (${total - nextBatch} remaining)
                    `;
                    btn.disabled = false;
                    
                    document.getElementById('remainingCount').textContent = 
                        `Showing ${nextBatch} of ${total} payslips`;
                }
                
                // Re-initialize Lucide icons for new cards
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
                
            }, 800); // Simulated delay
        });
        
        // Function to add a payslip card to the grid
        function addPayslipCard(employee, index) {
            const template = document.getElementById('payslipCardTemplate');
            const clone = template.content.cloneNode(true);
            const card = clone.querySelector('.payslip-card');
            
            // Update card with employee data
            card.querySelector('#employeeInitial').textContent = employee.name.charAt(0);
            card.querySelector('#employeeName').textContent = employee.name;
            card.querySelector('#employeeDetails').textContent = 
                `${employee.code} • ${employee.designation} • ${employee.department}`;
            card.querySelector('#payslipNumber').textContent = index.toString().padStart(3, '0');
            card.querySelector('#basicSalary').textContent = `₹${employee.basic_salary.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
            card.querySelector('#hra').textContent = `₹${employee.hra.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
            card.querySelector('#allowances').textContent = `₹${employee.allowances.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
            card.querySelector('#deductions').textContent = `₹${employee.deductions.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
            card.querySelector('#netSalary').textContent = `₹${employee.net_salary.toLocaleString('en-IN', {minimumFractionDigits: 2})}`;
            
            // Update buttons with employee ID
            const viewBtn = card.querySelector('[onclick="viewPayslip(this)"]');
            const downloadBtn = card.querySelector('[onclick="downloadPayslip(this)"]');
            viewBtn.dataset.employeeId = employee.id;
            downloadBtn.dataset.employeeId = employee.id;
            
            // Update status
            if (employee.status === 'pending') {
                const statusElem = card.querySelector('.payslip-status');
                statusElem.className = 'payslip-status status-pending';
                statusElem.innerHTML = '<i data-lucide="clock" style="width: 12px; height: 12px;"></i> Pending';
            }
            
            // Add to grid
            document.getElementById('payslipGrid').appendChild(card);
        }
        
        // View individual payslip
        function viewPayslip(button) {
            const employeeId = button.dataset.employeeId;
            const employee = employeeData.find(emp => emp.id === employeeId);
            
            if (employee) {
                Swal.fire({
                    title: `Payslip Preview - ${employee.name}`,
                    html: `
                    <div style="text-align: left; max-width: 500px; margin: 0 auto;">
                        <div style="background: #1e3a8a; color: white; padding: 20px; border-radius: 10px 10px 0 0;">
                            <h3 style="margin: 0;">YOUR COMPANY</h3>
                            <p style="margin: 5px 0 0 0; opacity: 0.9;">PAYSLIP - ${employee.code}</p>
                        </div>
                        <div style="padding: 20px; background: white; border: 1px solid #e2e8f0; border-top: none;">
                            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                                <div style="width: 60px; height: 60px; border-radius: 50%; background: #6366f1; 
                                            display: flex; align-items: center; justify-content: center; 
                                            font-size: 1.5rem; font-weight: bold; color: white;">
                                    ${employee.name.charAt(0)}
                                </div>
                                <div>
                                    <h4 style="margin: 0 0 5px 0;">${employee.name}</h4>
                                    <p style="margin: 0; color: #64748b; font-size: 0.9rem;">
                                        ${employee.designation}<br>${employee.department}
                                    </p>
                                </div>
                            </div>
                            
                            <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                                <div style="background: #f8fafc; padding: 10px 15px; border-bottom: 1px solid #e2e8f0;">
                                    <strong>Salary Breakdown</strong>
                                </div>
                                <div style="padding: 15px;">
                                    ${generateSalaryTable(employee)}
                                </div>
                            </div>
                        </div>
                    </div>
                    `,
                    width: 600,
                    showConfirmButton: false,
                    showCloseButton: true
                });
            }
        }
        
        // Generate salary table HTML
        function generateSalaryTable(employee) {
            return `
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px dashed #f1f5f9;">Basic Salary</td>
                        <td style="text-align: right; padding: 8px 0; border-bottom: 1px dashed #f1f5f9; font-family: monospace;">
                            ₹${employee.basic_salary.toLocaleString('en-IN', {minimumFractionDigits: 2})}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px dashed #f1f5f9;">House Rent Allowance</td>
                        <td style="text-align: right; padding: 8px 0; border-bottom: 1px dashed #f1f5f9; font-family: monospace;">
                            ₹${employee.hra.toLocaleString('en-IN', {minimumFractionDigits: 2})}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px dashed #f1f5f9;">Other Allowances</td>
                        <td style="text-align: right; padding: 8px 0; border-bottom: 1px dashed #f1f5f9; font-family: monospace;">
                            ₹${employee.allowances.toLocaleString('en-IN', {minimumFractionDigits: 2})}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 2px solid #e2e8f0;">Deductions</td>
                        <td style="text-align: right; padding: 8px 0; border-bottom: 2px solid #e2e8f0; font-family: monospace;">
                            - ₹${employee.deductions.toLocaleString('en-IN', {minimumFractionDigits: 2})}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 0; font-weight: bold; font-size: 1.1em;">Net Salary</td>
                        <td style="text-align: right; padding: 12px 0; font-weight: bold; font-size: 1.1em; color: #059669; font-family: monospace;">
                            ₹${employee.net_salary.toLocaleString('en-IN', {minimumFractionDigits: 2})}
                        </td>
                    </tr>
                </table>
            `;
        }
        
        // Download individual payslip
        function downloadPayslip(button) {
            const employeeId = button.dataset.employeeId;
            const employee = employeeData.find(emp => emp.id === employeeId);
            
            if (employee) {
                Swal.fire({
                    title: 'Download Payslip',
                    html: `
                    <div style="text-align: left; padding: 0 20px;">
                        <p>Download payslip for <strong>${employee.name}</strong> (${employee.code})?</p>
                        <div style="background: #f0f9ff; padding: 12px; border-radius: 8px; margin-top: 15px;">
                            <strong>Format Options:</strong>
                            <div style="margin-top: 10px;">
                                <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                                    <input type="radio" name="format" value="pdf" checked> PDF Document
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px;">
                                    <input type="radio" name="format" value="excel"> Excel Sheet
                                </label>
                            </div>
                        </div>
                    </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Download',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#059669',
                    preConfirm: () => {
                        const format = document.querySelector('input[name="format"]:checked').value;
                        return { format: format };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Generating File',
                            html: `
                            <div style="text-align: center; padding: 20px;">
                                <div class="spinner-border text-primary" style="width: 40px; height: 40px;"></div>
                                <p style="color: #64748b; margin-top: 16px;">Preparing ${result.value.format.toUpperCase()} file...</p>
                            </div>
                            `,
                            showConfirmButton: false,
                            allowOutsideClick: false
                        });
                        
                        // Simulate download (replace with actual AJAX call)
                        setTimeout(() => {
                            Swal.fire({
                                title: 'Download Started',
                                text: `Payslip for ${employee.name} is downloading as ${result.value.format.toUpperCase()}.`,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }, 1500);
                    }
                });
            }
        }
        
        // Download all payslips as ZIP
        function downloadAllPayslips() {
            const totalPayslips = employeeData.length;
            
            if (totalPayslips === 0) {
                Swal.fire({
                    title: 'No Payslips',
                    text: 'There are no payslips available to download.',
                    icon: 'warning'
                });
                return;
            }
            
            Swal.fire({
                title: 'Download All Payslips',
                html: `
                <div style="text-align: left; padding: 0 20px;">
                    <p>You are about to download <strong>${totalPayslips} payslips</strong> as a ZIP file.</p>
                    <div style="background: #f0f9ff; padding: 12px; border-radius: 8px; margin-top: 15px;">
                        <strong>The ZIP file will include:</strong>
                        <ul style="margin: 8px 0 0 20px; color: #0369a1; font-size: 14px;">
                            <li>Individual PDF payslips</li>
                            <li>Consolidated report</li>
                            <li>Payment summary sheet</li>
                        </ul>
                    </div>
                    <p style="color: #64748b; font-size: 0.9rem; margin-top: 15px;">
                        <i data-lucide="info" style="width: 16px; height: 16px; margin-right: 5px;"></i>
                        Large files may take a few moments to prepare.
                    </p>
                </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Download ZIP',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#059669',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        // AJAX call to generate and download ZIP
                        $.ajax({
                            url: '/payroll/payslip/download-all',
                            type: 'POST',
                            data: {
                                payroll_id: {{ $payrollPeriod->pp_id ?? 0 }},
                                _token: '{{ csrf_token() }}'
                            },
                            xhrFields: {
                                responseType: 'blob'
                            },
                            success: function(response) {
                                // Create download link
                                const blob = new Blob([response], { type: 'application/zip' });
                                const url = window.URL.createObjectURL(blob);
                                const a = document.createElement('a');
                                a.href = url;
                                a.download = 'payslips-{{ $monthName ?? "payroll" }}-{{ date("Y") }}.zip';
                                document.body.appendChild(a);
                                a.click();
                                window.URL.revokeObjectURL(url);
                                document.body.removeChild(a);
                                resolve();
                            },
                            error: function() {
                                Swal.showValidationMessage('Download failed. Please try again.');
                            }
                        });
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Download Started',
                        text: 'Payslip ZIP download has started.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }
        
        // Print all payslips
        function printAllPayslips() {
            const totalPayslips = employeeData.length;
            
            if (totalPayslips === 0) {
                Swal.fire({
                    title: 'No Payslips',
                    text: 'There are no payslips available to print.',
                    icon: 'warning'
                });
                return;
            }
            
            Swal.fire({
                title: 'Print All Payslips',
                html: `
                <div style="text-align: left; padding: 0 20px;">
                    <p>Print <strong>${totalPayslips} payslips</strong>?</p>
                    <div style="background: #fef3c7; padding: 12px; border-radius: 8px; margin-top: 15px;">
                        <strong>Print Options:</strong>
                        <div style="margin-top: 10px;">
                            <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                                <input type="radio" name="printOption" value="individual" checked> Individual payslips
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="radio" name="printOption" value="consolidated"> Consolidated report
                            </label>
                        </div>
                    </div>
                </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Print',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#3b82f6',
                preConfirm: () => {
                    return document.querySelector('input[name="printOption"]:checked').value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show printing dialog
                    Swal.fire({
                        title: 'Preparing Print',
                        html: `
                        <div style="text-align: center; padding: 20px;">
                            <div class="spinner-border text-primary" style="width: 40px; height: 40px;"></div>
                            <p style="color: #64748b; margin-top: 16px;">Preparing ${result.value} print layout...</p>
                        </div>
                        `,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                    
                    // Simulate print preparation
                    setTimeout(() => {
                        Swal.close();
                        
                        // Open print dialog
                        const printContent = generatePrintContent(result.value);
                        const printWindow = window.open('', '_blank');
                        printWindow.document.write(printContent);
                        printWindow.document.close();
                        printWindow.focus();
                        printWindow.print();
                        printWindow.close();
                        
                        Swal.fire({
                            title: 'Print Job Sent',
                            text: `Print job for ${result.value} report has been sent to printer.`,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }, 1500);
                }
            });
        }
        
        // Generate print content
        function generatePrintContent(option) {
            const date = new Date().toLocaleDateString();
            const time = new Date().toLocaleTimeString();
            
            if (option === 'individual') {
                return `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Payslips - {{ $monthName }} {{ date('Y') }}</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            .payslip { border: 2px solid #000; padding: 20px; margin-bottom: 30px; break-inside: avoid; }
                            .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
                            .company { font-size: 24px; font-weight: bold; }
                            .title { font-size: 18px; color: #666; }
                            .employee-info { display: flex; justify-content: space-between; margin-bottom: 20px; }
                            .salary-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                            .salary-table th, .salary-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                            .salary-table th { background: #f5f5f5; }
                            .total-row { font-weight: bold; background: #f0f0f0; }
                            @media print {
                                .no-print { display: none; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="no-print" style="text-align: center; margin-bottom: 20px;">
                            <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Print</button>
                            <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">Close</button>
                        </div>
                        ${employeeData.map((emp, index) => `
                            <div class="payslip">
                                <div class="header">
                                    <div class="company">YOUR COMPANY</div>
                                    <div class="title">PAYSLIP - {{ $monthName }} {{ date('Y') }}</div>
                                    <div>Generated: ${date} ${time}</div>
                                </div>
                                <div class="employee-info">
                                    <div>
                                        <strong>${emp.name}</strong><br>
                                        ${emp.designation}<br>
                                        ${emp.department}<br>
                                        Employee Code: ${emp.code}
                                    </div>
                                    <div>
                                        Payslip #: ${(index + 1).toString().padStart(3, '0')}<br>
                                        Payment Date: ${date}<br>
                                        Status: Processed
                                    </div>
                                </div>
                                <table class="salary-table">
                                    <tr>
                                        <th>Component</th>
                                        <th>Amount (₹)</th>
                                    </tr>
                                    <tr>
                                        <td>Basic Salary</td>
                                        <td>${emp.basic_salary.toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                                    </tr>
                                    <tr>
                                        <td>House Rent Allowance</td>
                                        <td>${emp.hra.toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                                    </tr>
                                    <tr>
                                        <td>Other Allowances</td>
                                        <td>${emp.allowances.toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                                    </tr>
                                    <tr>
                                        <td>Deductions</td>
                                        <td>${emp.deductions.toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td><strong>Net Salary Payable</strong></td>
                                        <td><strong>${emp.net_salary.toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong></td>
                                    </tr>
                                </table>
                                <div style="margin-top: 30px; border-top: 1px solid #000; padding-top: 10px;">
                                    <div style="display: flex; justify-content: space-between;">
                                        <div>
                                            <strong>Authorized Signatory</strong><br>
                                            <div style="height: 50px; border-bottom: 1px solid #000; width: 200px;"></div>
                                        </div>
                                        <div>
                                            <strong>Employee Acknowledgement</strong><br>
                                            <div style="height: 50px; border-bottom: 1px solid #000; width: 200px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </body>
                    </html>
                `;
            } else {
                // Consolidated report
                return `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Payslip Consolidated Report - {{ $monthName }} {{ date('Y') }}</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 30px; }
                            .company { font-size: 28px; font-weight: bold; }
                            .report-title { font-size: 20px; color: #666; margin: 10px 0; }
                            .summary-table { width: 100%; border-collapse: collapse; margin: 30px 0; }
                            .summary-table th, .summary-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
                            .summary-table th { background: #f5f5f5; font-weight: bold; }
                            .total-row { font-weight: bold; background: #f0f0f0; }
                            .footer { margin-top: 50px; border-top: 1px solid #000; padding-top: 20px; font-size: 12px; color: #666; }
                            @media print {
                                .no-print { display: none; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="no-print" style="text-align: center; margin-bottom: 20px;">
                            <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Print</button>
                            <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">Close</button>
                        </div>
                        <div class="header">
                            <div class="company">YOUR COMPANY</div>
                            <div class="report-title">PAYSLIP CONSOLIDATED REPORT</div>
                            <div>Pay Period: {{ $monthName }} {{ date('Y') }}</div>
                            <div>Generated: ${date} ${time}</div>
                            <div>Total Employees: ${employeeData.length}</div>
                        </div>
                        
                        <table class="summary-table">
                            <tr>
                                <th>S.No</th>
                                <th>Employee Name</th>
                                <th>Employee Code</th>
                                <th>Designation</th>
                                <th>Department</th>
                                <th>Basic Salary</th>
                                <th>Allowances</th>
                                <th>Deductions</th>
                                <th>Net Salary</th>
                                <th>Status</th>
                            </tr>
                            ${employeeData.map((emp, index) => `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${emp.name}</td>
                                    <td>${emp.code}</td>
                                    <td>${emp.designation}</td>
                                    <td>${emp.department}</td>
                                    <td>${emp.basic_salary.toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                                    <td>${(emp.hra + emp.allowances).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                                    <td>${emp.deductions.toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                                    <td>${emp.net_salary.toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                                    <td>${emp.status.charAt(0).toUpperCase() + emp.status.slice(1)}</td>
                                </tr>
                            `).join('')}
                            <tr class="total-row">
                                <td colspan="5"><strong>TOTAL</strong></td>
                                <td><strong>${employeeData.reduce((sum, emp) => sum + emp.basic_salary, 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong></td>
                                <td><strong>${employeeData.reduce((sum, emp) => sum + emp.hra + emp.allowances, 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong></td>
                                <td><strong>${employeeData.reduce((sum, emp) => sum + emp.deductions, 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong></td>
                                <td><strong>${employeeData.reduce((sum, emp) => sum + emp.net_salary, 0).toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong></td>
                                <td></td>
                            </tr>
                        </table>
                        
                        <div class="footer">
                            <div style="display: flex; justify-content: space-between;">
                                <div>
                                    <strong>Prepared By:</strong><br>
                                    Payroll Department<br>
                                    Date: ${date}
                                </div>
                                <div>
                                    <strong>Approved By:</strong><br>
                                    Finance Department<br>
                                    Date: ${date}
                                </div>
                            </div>
                            <div style="text-align: center; margin-top: 20px;">
                                This is a system generated report. No signature required.
                            </div>
                        </div>
                    </body>
                    </html>
                `;
            }
        }
        
        // Initialize on page load
        $(document).ready(function() {
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            
            // Add smooth scrolling to top
            $('html, body').animate({ scrollTop: 0 }, 300);
        });
    </script>
@endsection