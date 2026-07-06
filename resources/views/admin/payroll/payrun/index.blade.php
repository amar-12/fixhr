
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayrollPro - Payroll Management System</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="{{ asset('assets/css/payroll.css') }}" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    
    <!-- Custom CSS -->
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fade-in 0.5s ease-out',
                        'slide-in-right': 'slide-in-from-right 0.5s ease-out',
                        'slide-in-top': 'slide-in-from-top 0.3s ease-out',
                        'zoom-in': 'zoom-in 0.2s ease-out',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-900 font-sans min-h-screen selection:bg-blue-100 overflow-x-hidden">
    <!-- Background Effects -->
    <div class="bg-effects">
        <div class="bg-effect-1"></div>
        <div class="bg-effect-2"></div>
    </div>

    <!-- Header -->
    <header class="border-b border-slate-200 bg-white/90 backdrop-blur-md sticky top-0 shadow-sm z-20">
        <div class="max-w-[1400px] mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3 cursor-pointer" data-action="go-dashboard">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-sky-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                    <i data-lucide="banknote" class="text-white w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Payroll<span class="text-blue-600">Pro</span></h1>
                </div>
            </div>
            <button data-action="create-cycle" class="flex items-center gap-2 px-4 py-2 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-full text-blue-700 text-xs font-bold transition-all">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                Create Cycle
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-10 max-w-[1400px] mx-auto px-6 py-8">
        <!-- Dashboard View -->
        <div data-view="DASHBOARD" class="animate-fade-in">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="space-y-6">
                    <h3 class="text-slate-500 font-bold text-sm uppercase tracking-widest">Active Periods</h3>
                    
                    <!-- Cycle Cards -->
                    <div class="space-y-4">
                        @foreach([1] as $cycleId)
                        <div class="glass-card hover-effect p-0 overflow-hidden">
                            <div class="p-5 bg-white">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-2xl font-bold text-slate-900">October</h3>
                                        <p class="text-sm text-slate-500">2025</p>
                                    </div>
                                    <div class="status-badge status-open">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        In Progress
                                    </div>
                                </div>
                                
                                <!-- Held Salaries Alert -->
                                <button data-action="open-held-list" class="w-full mb-3 py-2 bg-amber-50 border border-amber-200 rounded-lg text-amber-700 text-xs font-bold flex items-center justify-center gap-2 hover:bg-amber-100 transition-colors">
                                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                                    <span class="held-count">0</span> Salary On Hold (Review)
                                </button>

                                <!-- Action Buttons -->
                                <button data-action="go-step1" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-200 mb-3 transition-colors">
                                    Start Process
                                </button>
                                
                                <button data-action="open-adhoc" class="w-full py-2 bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-slate-800 rounded-lg text-xs font-bold border border-slate-200 flex items-center justify-center gap-2 transition-colors">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    Add Ad-Hoc Adjustment
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="md:col-span-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="glass-card p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="p-3 bg-blue-100 rounded-xl text-blue-600">
                                    <i data-lucide="users" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">Total Employees</h3>
                                    <p class="text-2xl font-bold text-blue-600">3</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="glass-card p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="p-3 bg-emerald-100 rounded-xl text-emerald-600">
                                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">Ready for Processing</h3>
                                    <p class="text-2xl font-bold text-emerald-600">3</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="glass-card p-6 mt-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Recent Activity</h3>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg">
                                <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900">October payroll cycle created</p>
                                    <p class="text-xs text-slate-500">2 days ago</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg">
                                <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600">
                                    <i data-lucide="user-check" class="w-4 h-4"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900">3 employees added to payroll</p>
                                    <p class="text-xs text-slate-500">1 day ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 1: Action Required -->
        <div data-view="STEP1" class="hidden animate-slide-in-right">
            @include('admin/payroll.partials.workflow-stepper', ['currentStep' => 1])
            
            @include('admin/payroll.partials.process-info', [
                'title' => 'Pre-Payroll Checks',
                'description' => 'Review and resolve attendance anomalies. You cannot freeze attendance until all pending requests and missed punches are addressed.'
            ])

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                <!-- Missed Punches Card -->
                <div class="glass-card p-6 border-l-4 border-l-amber-500">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-amber-100 rounded-lg text-amber-600">
                                <i data-lucide="alert-circle" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Missed Punches</h3>
                                <p class="text-xs text-slate-500">Incomplete attendance records</p>
                            </div>
                        </div>
                        <span class="bg-amber-100 text-amber-800 font-bold px-3 py-1 rounded-full text-sm">12 Pending</span>
                    </div>
                    
                    <div class="bg-slate-50 rounded-lg p-3 mb-4 space-y-2 border border-slate-200">
                        <div class="flex justify-between text-sm text-slate-600 border-b border-slate-200 pb-2">
                            <span>John Doe (EMP001)</span>
                            <span class="text-amber-600 font-bold">Oct 12</span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-600 border-b border-slate-200 pb-2">
                            <span>Alice Smith (EMP045)</span>
                            <span class="text-amber-600 font-bold">Oct 14</span>
                        </div>
                    </div>
                    
                    <button class="w-full py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 rounded-lg text-sm font-bold transition-colors">
                        Resolve Batch
                    </button>
                </div>

                <!-- Leave Requests Card -->
                <div class="glass-card p-6 border-l-4 border-l-rose-500">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-rose-100 rounded-lg text-rose-600">
                                <i data-lucide="clock" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Leave Requests</h3>
                                <p class="text-xs text-slate-500">Pending manager approval</p>
                            </div>
                        </div>
                        <span class="bg-rose-100 text-rose-800 font-bold px-3 py-1 rounded-full text-sm">5 Pending</span>
                    </div>
                    
                    <div class="bg-slate-50 rounded-lg p-3 mb-4 space-y-2 border border-slate-200">
                        <div class="flex justify-between text-sm text-slate-600 border-b border-slate-200 pb-2">
                            <span>Robert Fox (Sick)</span>
                            <span class="text-rose-600 font-bold">2 Days</span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-600 border-b border-slate-200 pb-2">
                            <span>Guy Hawkins (Casual)</span>
                            <span class="text-rose-600 font-bold">1 Day</span>
                        </div>
                    </div>
                    
                    <button class="w-full py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 rounded-lg text-sm font-bold transition-colors">
                        View Requests
                    </button>
                </div>
            </div>

            <div class="flex justify-center mt-8">
                <button data-action="go-step2" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 flex items-center gap-2 transition-all active:scale-95">
                    Proceed to Freeze Grid
                    <i data-lucide="arrow-right" class="w-4.5 h-4.5"></i>
                </button>
            </div>
        </div>

        <!-- Step 2: Freeze Grid -->
        <div data-view="STEP2" class="hidden animate-slide-in-right">
            @include('admin/payroll.partials.workflow-stepper', ['currentStep' => 2])
            
            @include('admin.payroll.partials.process-info', [
                'title' => 'Attendance Finalization',
                'description' => 'Verify the consolidated attendance data. Once frozen, this data will be used for salary calculation and cannot be modified easily.'
            ])

            <div class="flex justify-between items-center mb-6 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Attendance Sheet</h2>
                    <p class="text-slate-500 text-xs">October 2025</p>
                </div>
                <div class="flex gap-3">
                    <button data-action="go-step1" class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-50 hover:bg-slate-100 rounded-lg border border-slate-200 flex items-center gap-2">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        Back to Checklist
                    </button>
                    <button class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-50 hover:bg-slate-100 rounded-lg border border-slate-200">
                        Clear All
                    </button>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl overflow-x-auto custom-scrollbar shadow-sm mb-20">
                <table class="w-full text-center border-collapse attendance-grid">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 text-[10px] uppercase tracking-wider border-b border-slate-200">
                            <th class="p-3 sticky-left bg-slate-50 z-10">Code</th>
                            <th class="p-3 sticky-left-16 bg-slate-50 z-10 text-left">Name</th>
                            <th class="p-3">Days</th>
                            <th class="p-3">Present</th>
                            <th class="p-3">Absent</th>
                            <th class="p-3">W-Off</th>
                            <th class="p-3 bg-slate-50">Total</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs text-slate-700 divide-y divide-slate-100">
                        @foreach([
                            ['id' => 1, 'code' => 'FD001', 'name' => 'Kunal Pandit', 'days' => 31, 'present' => 0, 'absent' => 23, 'wo' => 4, 'tot' => 8],
                            ['id' => 2, 'code' => 'FD006', 'name' => 'Gurleen Kaur', 'days' => 31, 'present' => 0, 'absent' => 23, 'wo' => 4, 'tot' => 8],
                            ['id' => 3, 'code' => 'FD017', 'name' => 'Ujjwal Pandey', 'days' => 31, 'present' => 0, 'absent' => 23, 'wo' => 4, 'tot' => 8],
                            ['id' => 4, 'code' => 'FD018', 'name' => 'Umesh Kumar', 'days' => 31, 'present' => 0, 'absent' => 23, 'wo' => 4, 'tot' => 8],
                            ['id' => 5, 'code' => 'FD027', 'name' => 'Chaitanya Rao', 'days' => 31, 'present' => 0, 'absent' => 23, 'wo' => 4, 'tot' => 8],
                        ] as $row)
                        <tr class="hover:bg-blue-50/50 transition-colors">
                            <td class="p-3 sticky-left bg-white font-mono text-slate-500 border-r border-slate-100">{{ $row['code'] }}</td>
                            <td class="p-3 sticky-left-16 bg-white text-left font-medium text-slate-900 border-r border-slate-100">{{ $row['name'] }}</td>
                            <td class="p-1">{{ $row['days'] }}</td>
                            <td class="p-1">
                                <input type="text" value="{{ $row['present'] }}" 
                                       class="w-10 bg-transparent text-center outline-none focus:bg-blue-50 rounded border border-transparent focus:border-blue-200 attendance-input"
                                       data-field="present">
                            </td>
                            <td class="p-1">
                                <input type="text" value="{{ $row['absent'] }}" 
                                       class="w-10 bg-transparent text-center outline-none focus:bg-blue-50 rounded border border-transparent focus:border-blue-200 attendance-input"
                                       data-field="absent">
                            </td>
                            <td class="p-1">
                                <input type="text" value="{{ $row['wo'] }}" 
                                       class="w-10 bg-transparent text-center outline-none focus:bg-blue-50 rounded border border-transparent focus:border-blue-200 attendance-input"
                                       data-field="wo">
                            </td>
                            <td class="p-1 font-bold bg-slate-50" data-field="tot">{{ $row['tot'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="fixed-action-btn">
                <div class="tooltip-container">
                    <button data-action="freeze-attendance" class="px-8 py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-2xl shadow-rose-200 flex items-center gap-2 transition-all active:scale-95 animate-pulse">
                        <i data-lucide="lock" class="w-4.5 h-4.5"></i>
                        Freeze Attendance
                    </button>
                    <div class="tooltip-content">
                        Lock this attendance data for salary calculation
                        <div class="tooltip-arrow"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Salary List -->
        <div data-view="STEP3" class="hidden animate-slide-in-right">
            @include('admin.payroll.partials.workflow-stepper', ['currentStep' => 3])
            
            @include('admin.payroll.partials.process-info', [
                'title' => 'Salary Processing',
                'description' => 'Calculate salaries based on attendance and ad-hoc adjustments. You can hold specific employees or process them individually. Click \'Next\' to auto-process selected items.'
            ])

            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                <div class="flex items-center gap-4">
                    <button data-action="revert-freeze" class="p-2 bg-white hover:bg-slate-100 rounded-full text-slate-400 hover:text-slate-600 transition-colors shadow-sm border border-slate-200" title="Unfreeze Attendance (Go Back)">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    </button>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">Salary Process</h2>
                        <p class="text-slate-500 text-xs">Review adjustments and process salaries.</p>
                    </div>
                </div>
                <div class="flex gap-3 items-center">
                    <div class="tooltip-container">
                        <button data-action="unprocess-all" class="px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-600 rounded-lg text-sm font-bold flex items-center gap-2 transition-colors shadow-sm">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            Reset All
                        </button>
                        <div class="tooltip-content">
                            Revert ALL processed salaries to pending state
                            <div class="tooltip-arrow"></div>
                        </div>
                    </div>
                    
                    <div class="tooltip-container">
                        <button data-action="process-bulk" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg shadow-blue-200 flex items-center gap-2 transition-all">
                            <span class="process-text">Process Selected & Next</span>
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                        <div class="tooltip-content">
                            Auto-calculate salaries for selected employees and move to verification
                            <div class="tooltip-arrow"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="glass-card p-0 overflow-hidden">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-12">
                                <div data-select-all class="cursor-pointer hover:text-blue-600 transition-colors">
                                    <i data-lucide="square" class="w-4.5 h-4.5 text-slate-400"></i>
                                </div>
                            </th>
                            <th>Employee</th>
                            <th>Role</th>
                            <th class="text-right">Base Salary</th>
                            <th class="text-right">Ad-Hoc</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['id' => 'EMP001', 'name' => 'Sarah Connor', 'role' => 'Senior Dev', 'salary' => '4,500', 'adhoc' => '+450 (Bonus)', 'hasAdHoc' => true, 'status' => 'pending'],
                            ['id' => 'EMP002', 'name' => 'John Wick', 'role' => 'Security Lead', 'salary' => '3,200', 'adhoc' => '0', 'hasAdHoc' => false, 'status' => 'pending'],
                            ['id' => 'EMP003', 'name' => 'Ellen Ripley', 'role' => 'Logistics Mgr', 'salary' => '5,100', 'adhoc' => '-200 (Penalty)', 'hasAdHoc' => true, 'status' => 'pending'],
                        ] as $employee)
                        <tr class="group hover:bg-blue-50/50 transition-colors 
                                  {{ $employee['status'] === 'held' ? 'bg-amber-50' : '' }}">
                            <td>
                                @if($employee['status'] === 'pending')
                                <div data-employee-select="{{ $employee['id'] }}" class="cursor-pointer hover:text-blue-600">
                                    <i data-lucide="square" class="w-4.5 h-4.5 text-slate-400"></i>
                                </div>
                                @else
                                <div class="opacity-30">
                                    <i data-lucide="square" class="w-4.5 h-4.5 text-slate-400"></i>
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="employee-avatar">
                                        {{ substr($employee['name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $employee['name'] }}</div>
                                        <div class="text-xs text-slate-500">{{ $employee['id'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-slate-500">{{ $employee['role'] }}</td>
                            <td class="text-right font-mono text-slate-700 font-medium">${{ $employee['salary'] }}</td>
                            <td class="text-right font-mono font-bold">
                                @if($employee['hasAdHoc'])
                                <span class="flex items-center justify-end gap-1 {{ str_contains($employee['adhoc'], '+') ? 'text-emerald-600' : 'text-rose-600' }}">
                                    <i data-lucide="zap" class="w-3 h-3 fill-current"></i>
                                    {{ $employee['adhoc'] }}
                                </span>
                                @else
                                <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($employee['status'] === 'processed')
                                <span class="status-badge status-processed">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i>
                                    PROCESSED
                                </span>
                                @elseif($employee['status'] === 'pending')
                                <span class="status-badge status-pending">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    PENDING
                                </span>
                                @elseif($employee['status'] === 'held')
                                <span class="status-badge status-held">
                                    <i data-lucide="pause-circle" class="w-3 h-3"></i>
                                    ON HOLD
                                </span>
                                @endif
                            </td>
                            <td class="text-right flex items-center justify-end gap-2">
                                @if($employee['status'] === 'processed')
                                <div class="tooltip-container">
                                    <button data-action="unprocess-employee" data-employee-id="{{ $employee['id'] }}" class="p-1.5 hover:bg-rose-100 text-slate-400 hover:text-rose-600 rounded-lg transition-colors">
                                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                    </button>
                                    <div class="tooltip-content">
                                        Revert this employee to pending state
                                        <div class="tooltip-arrow"></div>
                                    </div>
                                </div>
                                @endif
                                
                                @if($employee['status'] === 'pending')
                                <div class="tooltip-container">
                                    <button data-action="hold-salary" data-employee-id="{{ $employee['id'] }}" class="p-1.5 hover:bg-amber-100 text-slate-400 hover:text-amber-600 rounded-lg transition-colors">
                                        <i data-lucide="pause-circle" class="w-4 h-4"></i>
                                    </button>
                                    <div class="tooltip-content">
                                        Prevent this salary from being processed
                                        <div class="tooltip-arrow"></div>
                                    </div>
                                </div>
                                @endif
                                
                                <button data-action="process-individual" data-employee-id="{{ $employee['id'] }}" class="px-3 py-1.5 border rounded-lg text-xs font-bold transition-all flex items-center gap-2 bg-blue-50 border-blue-200 text-blue-600 hover:bg-blue-100">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    View
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Step 4: Individual Employee View -->
        <div data-view="STEP4" class="hidden animate-slide-in-right">
            <button data-action="back-to-list" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 mb-6 text-sm font-bold uppercase">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to List
            </button>
            
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Sarah Connor</h2>
                    <p class="text-slate-500 text-xs">Payment Breakdown • Verification Mode</p>
                </div>
                <div class="flex items-center gap-2 bg-amber-100 text-amber-700 px-3 py-1.5 rounded-lg border border-amber-200 text-xs font-bold">
                    <i data-lucide="lock" class="w-3 h-3"></i>
                    Read Only
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="col-span-2 space-y-6">
                    <!-- Earnings Card -->
                    <div class="glass-card p-6">
                        <div class="flex items-center gap-2 mb-4 border-b border-slate-100 pb-2 text-emerald-600">
                            <i data-lucide="trending-up" class="w-4.5 h-4.5"></i>
                            <h3 class="text-sm font-bold uppercase tracking-wide">Earnings</h3>
                        </div>
                        <div class="space-y-1">
                            <div class="flex justify-between py-2 border-b border-slate-100 last:border-0">
                                <span class="text-slate-500 text-sm">Basic Salary</span>
                                <span class="text-slate-800 font-mono text-sm">4,500.00</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-slate-100 last:border-0">
                                <span class="text-slate-500 text-sm">HRA</span>
                                <span class="text-slate-800 font-mono text-sm">2,250.00</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-slate-100 last:border-0">
                                <span class="text-emerald-600 font-bold text-sm">Ad-Hoc Bonus</span>
                                <span class="text-emerald-600 font-mono text-sm">450.00</span>
                            </div>
                            <div class="flex justify-between py-2 border-t border-slate-200 pt-3 mt-1">
                                <span class="text-slate-900 font-bold">Total Earnings</span>
                                <span class="text-blue-600 font-bold font-mono text-lg">7,200.00</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Deductions Card -->
                    <div class="glass-card p-6">
                        <div class="flex items-center gap-2 mb-4 border-b border-slate-100 pb-2 text-rose-600">
                            <i data-lucide="arrow-right" class="w-4.5 h-4.5 rotate-45"></i>
                            <h3 class="text-sm font-bold uppercase tracking-wide">Deductions</h3>
                        </div>
                        <div class="space-y-1">
                            <div class="flex justify-between py-2 border-b border-slate-100 last:border-0">
                                <span class="text-slate-500 text-sm">Provident Fund</span>
                                <span class="text-slate-800 font-mono text-sm">1,800.00</span>
                            </div>
                            <div class="flex justify-between py-2 border-t border-slate-200 pt-3 mt-1">
                                <span class="text-slate-900 font-bold">Total Deductions</span>
                                <span class="text-blue-600 font-bold font-mono text-lg">1,800.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Net Payable Card -->
                <div class="col-span-1">
                    <div class="glass-card p-6 bg-gradient-to-b from-slate-50 to-white sticky top-24 border border-slate-200">
                        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wide mb-4">Net Payable</h3>
                        <div class="text-4xl font-bold text-slate-900 mb-2">$5,400.00</div>
                        <div class="space-y-3">
                            <button data-action="view-payslip" data-employee-id="EMP001" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg flex items-center justify-center gap-2 shadow-lg shadow-blue-200">
                                <i data-lucide="file-text" class="w-4.5 h-4.5"></i>
                                Preview Payslip
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 5: Reports -->
        <div data-view="STEP5" class="hidden animate-slide-in-right">
            @include('admin.payroll.partials.workflow-stepper', ['currentStep' => 4])
            
            @include('admin.payroll.partials.process-info', [
                'title' => 'Verification & Compliance',
                'description' => 'Final verification. Review the generated reports (Bank Sheet, PF, etc.) before committing. Use \'Save as Draft\' if you need to return later.'
            ])

            <div class="mb-8 text-center">
                <h2 class="text-2xl font-bold text-slate-900">Verification & Compliance</h2>
                <p class="text-slate-500">Review all reports before finalizing the payroll.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Bank Sheet Report -->
                <div class="glass-card p-6 hover:bg-blue-50/50 hover-effect">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 mb-4">
                        <i data-lucide="building" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Bank Sheet</h3>
                    <p class="text-sm text-slate-500 mb-4">HDFC_Oct_Salary.csv</p>
                    <button class="text-xs font-bold text-slate-600 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded hover:bg-slate-100 flex items-center gap-2">
                        <i data-lucide="download" class="w-3 h-3"></i>
                        Download
                    </button>
                </div>
                
                <!-- Payroll Register -->
                <div class="glass-card p-6 hover:bg-blue-50/50 hover-effect">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 mb-4">
                        <i data-lucide="file-spreadsheet" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Payroll Register</h3>
                    <p class="text-sm text-slate-500 mb-4">Consolidated_Register.xlsx</p>
                    <button class="text-xs font-bold text-slate-600 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded hover:bg-slate-100 flex items-center gap-2">
                        <i data-lucide="download" class="w-3 h-3"></i>
                        Download
                    </button>
                </div>
                
                <!-- PF Report -->
                <div class="glass-card p-6 hover:bg-blue-50/50 hover-effect">
                    <div class="w-12 h-12 rounded-xl bg-sky-100 flex items-center justify-center text-sky-600 mb-4">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">PF / EPF Report</h3>
                    <p class="text-sm text-slate-500 mb-4">ECR_Challan_Oct.txt</p>
                    <button class="text-xs font-bold text-slate-600 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded hover:bg-slate-100 flex items-center gap-2">
                        <i data-lucide="download" class="w-3 h-3"></i>
                        Download
                    </button>
                </div>
            </div>

            <!-- Payslip Batch View -->
            <div class="glass-card p-6 mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-5 h-5 text-pink-500"></i>
                        Payslip Batch View
                    </h3>
                    <button data-action="view-all-payslips" class="text-sm text-pink-600 hover:text-pink-700 font-bold">View All</button>
                </div>
                <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                    <div class="flex gap-4 overflow-x-auto pb-2 custom-scrollbar">
                        <!-- Sample payslip preview cards -->
                        @foreach([
                            ['id' => 'EMP001', 'name' => 'Sarah Connor'],
                            ['id' => 'EMP002', 'name' => 'John Wick'],
                            ['id' => 'EMP003', 'name' => 'Ellen Ripley'],
                        ] as $employee)
                        <div data-action="view-payslip" data-employee-id="{{ $employee['id'] }}" class="min-w-[160px] bg-white p-4 rounded-lg shadow-sm border border-slate-200 cursor-pointer transition-all hover:shadow-md hover:border-blue-300 group">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="employee-avatar">{{ substr($employee['name'], 0, 1) }}</div>
                                <div class="text-xs font-bold text-slate-700 truncate w-24">{{ $employee['name'] }}</div>
                            </div>
                            <div class="h-2 w-3/4 bg-slate-100 rounded mb-1 group-hover:bg-blue-50 transition-colors"></div>
                            <div class="h-2 w-1/2 bg-slate-100 rounded group-hover:bg-blue-50 transition-colors"></div>
                            <div class="mt-3 text-[10px] text-blue-600 font-medium flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <i data-lucide="eye" class="w-2.5 h-2.5"></i>
                                Click to View
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Final Confirmation -->
            <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl flex flex-col md:flex-row gap-3 items-center mb-8 max-w-3xl mx-auto justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <i data-lucide="alert-triangle" class="text-amber-600 shrink-0 w-5 h-5"></i>
                    <div>
                        <h4 class="text-amber-800 font-bold text-sm">Final Confirmation</h4>
                        <p class="text-amber-700 text-xs">Ensure all anomalies are resolved. This action sends data to accounts.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button data-action="revert-processing" class="px-4 py-3 text-slate-500 hover:text-slate-700 text-sm font-bold">Back to Processing</button>
                    
                    <div class="tooltip-container">
                        <button data-action="save-draft" class="px-6 py-3 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-xl shadow-sm transition-all whitespace-nowrap">
                            Save as Draft
                        </button>
                        <div class="tooltip-content">
                            Save progress without locking payroll
                            <div class="tooltip-arrow"></div>
                        </div>
                    </div>
                    
                    <div class="tooltip-container">
                        <button data-action="finalize-cycle" class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-200 active:scale-95 transition-all whitespace-nowrap">
                            Finalize & Lock
                        </button>
                        <div class="tooltip-content">
                            Lock payroll and generate final bank files
                            <div class="tooltip-arrow"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 6: Success -->
        <div data-view="STEP6" class="hidden animate-zoom-in text-center py-12">
            @include('admin.payroll.partials.workflow-stepper', ['currentStep' => 5])
            
            <div class="inline-flex p-8 bg-emerald-100 rounded-full border-4 border-emerald-200 mb-6 success-animation">
                <i data-lucide="check-circle" class="w-16 h-16 text-emerald-600"></i>
            </div>
            <h2 class="text-4xl font-bold text-slate-900 mb-4">Payroll Finalized Successfully!</h2>
            <p class="text-slate-500 max-w-md mx-auto mb-8">The payroll for October 2025 has been locked.</p>
            <div class="flex justify-center gap-4">
                <button data-action="go-dashboard" class="px-6 py-3 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-lg border border-slate-200 flex items-center gap-2 shadow-sm">
                    <i data-lucide="arrow-left" class="w-4.5 h-4.5"></i>
                    Back to Dashboard
                </button>
                <button class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-lg shadow-blue-200 flex items-center gap-2">
                    <i data-lucide="zap" class="w-4.5 h-4.5"></i>
                    Email Payslips
                </button>
            </div>
        </div>

        <!-- View All Payslips -->
        <div data-view="VIEW_ALL_PAYSLIPS" class="hidden animate-slide-in-right">
            <div class="flex items-center gap-3 mb-6">
                <button data-action="back-to-list" class="p-2 bg-white hover:bg-slate-100 rounded-full text-slate-500 transition-colors border border-slate-200">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </button>
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">All Payslips</h2>
                    <p class="text-slate-500 text-xs">October 2025 • Verified Payroll Batch</p>
                </div>
            </div>
            
            <div class="glass-card p-0 overflow-hidden">
                <div class="p-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                    <div class="relative w-64">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4"></i>
                        <input type="text" placeholder="Search employee..." class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                    </div>
                    <div class="text-xs text-slate-500 font-medium">Showing 3 Records</div>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="p-4 pl-6">Employee</th>
                            <th class="p-4">Role</th>
                            <th class="p-4 text-center">Days Present</th>
                            <th class="p-4 text-right">Net Salary</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-right pr-6">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['id' => 'EMP001', 'name' => 'Sarah Connor', 'role' => 'Senior Dev', 'netSalary' => '$5,400.00', 'status' => 'verified'],
                            ['id' => 'EMP002', 'name' => 'John Wick', 'role' => 'Security Lead', 'netSalary' => '$4,950.00', 'status' => 'verified'],
                            ['id' => 'EMP003', 'name' => 'Ellen Ripley', 'role' => 'Logistics Mgr', 'netSalary' => '$4,750.00', 'status' => 'verified'],
                        ] as $employee)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="p-4 pl-6">
                                <div class="flex items-center gap-3">
                                    <div class="employee-avatar employee-avatar-lg">{{ substr($employee['name'], 0, 1) }}</div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $employee['name'] }}</div>
                                        <div class="text-xs text-slate-400">{{ $employee['id'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-slate-500">{{ $employee['role'] }}</td>
                            <td class="p-4 text-center">
                                <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded text-xs font-bold border border-slate-200">31 Days</span>
                            </td>
                            <td class="p-4 text-right font-mono text-slate-900 font-medium">{{ $employee['netSalary'] }}</td>
                            <td class="p-4 text-center">
                                <span class="status-badge status-processed">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i>
                                    VERIFIED
                                </span>
                            </td>
                            <td class="p-4 text-right pr-6">
                                <button data-action="view-payslip" data-employee-id="{{ $employee['id'] }}" class="px-3 py-1.5 bg-white hover:bg-blue-50 text-blue-600 border border-blue-200 hover:border-blue-300 rounded-lg text-xs font-bold transition-all flex items-center gap-2 ml-auto shadow-sm">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                    View Payslip
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- View Records (Read Only) -->
        <div data-view="VIEW_RECORDS" class="hidden animate-slide-in-right">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                <div class="flex items-center gap-4">
                    <button data-action="go-dashboard" class="p-2 bg-white hover:bg-slate-100 rounded-full text-slate-400 hover:text-slate-600 transition-colors shadow-sm border border-slate-200">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    </button>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">Finalized Records</h2>
                        <p class="text-slate-500 text-xs">View details and download payslips.</p>
                    </div>
                </div>
            </div>

            <div class="glass-card p-0 overflow-hidden">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Role</th>
                            <th class="text-right">Base Salary</th>
                            <th class="text-right">Ad-Hoc</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['id' => 'EMP001', 'name' => 'Sarah Connor', 'role' => 'Senior Dev', 'salary' => '4,500', 'adhoc' => '+450 (Bonus)', 'hasAdHoc' => true, 'status' => 'processed'],
                            ['id' => 'EMP002', 'name' => 'John Wick', 'role' => 'Security Lead', 'salary' => '3,200', 'adhoc' => '0', 'hasAdHoc' => false, 'status' => 'processed'],
                            ['id' => 'EMP003', 'name' => 'Ellen Ripley', 'role' => 'Logistics Mgr', 'salary' => '5,100', 'adhoc' => '-200 (Penalty)', 'hasAdHoc' => true, 'status' => 'processed'],
                        ] as $employee)
                        <tr class="group hover:bg-blue-50/50 transition-colors">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="employee-avatar">
                                        {{ substr($employee['name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $employee['name'] }}</div>
                                        <div class="text-xs text-slate-500">{{ $employee['id'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-slate-500">{{ $employee['role'] }}</td>
                            <td class="text-right font-mono text-slate-700 font-medium">${{ $employee['salary'] }}</td>
                            <td class="text-right font-mono font-bold">
                                @if($employee['hasAdHoc'])
                                <span class="flex items-center justify-end gap-1 {{ str_contains($employee['adhoc'], '+') ? 'text-emerald-600' : 'text-rose-600' }}">
                                    <i data-lucide="zap" class="w-3 h-3 fill-current"></i>
                                    {{ $employee['adhoc'] }}
                                </span>
                                @else
                                <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="status-badge status-processed">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i>
                                    PROCESSED
                                </span>
                            </td>
                            <td class="text-right">
                                <button data-action="view-payslip" data-employee-id="{{ $employee['id'] }}" class="px-3 py-1.5 border rounded-lg text-xs font-bold transition-all flex items-center gap-2 bg-white hover:bg-slate-50 border-slate-200 text-slate-600">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                    Payslip
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modals -->
    @include('admin.payroll.modals.create-cycle')
    @include('admin.payroll.modals.hold-reason')
    @include('admin.payroll.modals.held-list')
    @include('admin.payroll.modals.release-action')
    @include('admin.payroll.modals.payslip-preview')
    @include('admin.payroll.modals.revert-processing')
    @include('admin.payroll.modals.adhoc-adjustment')
    @include('admin.payroll.modals.deferred-list')
    @include('admin.payroll.modals.deferred-action')

    <!-- JavaScript -->
    <script src="{{ asset('assets/js/payroll.js') }}"></script>
    
    <!-- Initialize Lucide Icons -->
   <script>
    document.addEventListener("DOMContentLoaded", function () {
        if (typeof PayrollApp !== 'undefined') {
            PayrollApp.init();
        } else {
            console.error("PayrollApp is not loaded");
        }
    });
</script>
</body>
</html>