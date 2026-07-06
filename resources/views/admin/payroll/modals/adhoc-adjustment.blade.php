<div id="adhoc-adjustment-modal" class="modal-overlay hidden">
    <div class="modal-container">
        <div class="modal-content">
            <div class="flex items-center justify-between p-6 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Global Ad-Hoc Adjustment</h2>
                <button data-action="close-modal" class="text-slate-400 hover:text-slate-600 transition-colors p-1 hover:bg-slate-100 rounded-full">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6 max-h-[80vh] overflow-y-auto custom-scrollbar">
                <form data-form="adhoc-adjustment" class="space-y-6">
                    <div class="p-4 bg-blue-50 border border-blue-100 rounded-lg flex gap-3">
                        <i data-lucide="zap" class="text-blue-600 shrink-0 w-5 h-5 mt-0.5"></i>
                        <div>
                            <h4 class="text-blue-800 font-bold text-sm">Quick Adjustment</h4>
                            <p class="text-blue-600 text-xs">Apply one-time bonuses, recurring deductions, or arrears.</p>
                        </div>
                    </div>
                    
                    <!-- Target Selection -->
                    <div class="grid grid-cols-2 gap-2 bg-slate-100 p-1 rounded-lg">
                        <button type="button" class="target-type-btn text-sm py-2 rounded-md font-bold transition-all bg-white text-blue-600 shadow-md" data-target-type="employee">
                            Individual Employee
                        </button>
                        <button type="button" class="target-type-btn text-sm py-2 rounded-md font-bold transition-all text-slate-500 hover:text-slate-700" data-target-type="department">
                            Entire Department
                        </button>
                    </div>
                    
                    <!-- Employee/Department Selection -->
                    <div class="input-group employee-selection">
                        <label class="input-label">Select Employee</label>
                        <select class="form-input form-select">
                            <option>-- Select Employee --</option>
                            <option>Sarah Connor (EMP001)</option>
                            <option>John Wick (EMP002)</option>
                            <option>Ellen Ripley (EMP003)</option>
                        </select>
                    </div>
                    
                    <div class="input-group department-selection hidden">
                        <label class="input-label">Select Department</label>
                        <select class="form-input form-select">
                            <option>-- Select Department --</option>
                            <option>Engineering</option>
                            <option>Security</option>
                            <option>Logistics</option>
                            <option>HR</option>
                            <option>Finance</option>
                        </select>
                    </div>
                    
                    <!-- Adjustment Details -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="input-group">
                            <label class="input-label">Adjustment Type</label>
                            <select class="form-input form-select">
                                <option>Earning</option>
                                <option>Deduction</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label class="input-label">Category</label>
                            <select class="form-input form-select">
                                <option>REGULAR EARNING</option>
                                <option>STATUTORY</option>
                                <option>NON-STATUTORY</option>
                                <option>VARIABLE EARNING</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="input-group">
                            <label class="input-label">Amount</label>
                            <input type="number" class="form-input" placeholder="0.00" step="0.01">
                        </div>
                        <div class="input-group">
                            <label class="input-label">Frequency</label>
                            <div class="relative">
                                <select class="form-input form-select frequency-select">
                                    <option value="onetime">One Time</option>
                                    <option value="recurring">Recurring</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recurring Options -->
                    <div class="recurring-options hidden bg-slate-50 p-4 rounded-lg border border-slate-200 animate-slide-in-top">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="input-group">
                                <label class="input-label">Start Month</label>
                                <input type="month" class="form-input">
                            </div>
                            <div class="input-group">
                                <label class="input-label">End Month</label>
                                <input type="month" class="form-input">
                            </div>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label class="input-label">Remarks / Reason</label>
                        <textarea class="form-input" rows="3" placeholder="Enter reason for this adjustment..."></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                        <button type="button" data-action="close-modal" class="px-4 py-2 text-slate-500 hover:text-slate-700 text-sm">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow-lg">
                            Apply Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>