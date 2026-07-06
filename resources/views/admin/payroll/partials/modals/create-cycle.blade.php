<div id="create-cycle-modal" class="modal-overlay hidden">
    <div class="modal-container max-w-4xl">
        <div class="modal-content">
            <div class="flex items-center justify-between p-6 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Create New Payroll Cycle</h2>
                <button data-action="close-modal" class="text-slate-400 hover:text-slate-600 transition-colors p-1 hover:bg-slate-100 rounded-full">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6 max-h-[80vh] overflow-y-auto custom-scrollbar">
                <form data-form="create-cycle" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="input-group">
                            <label class="input-label">Year</label>
                            <select class="form-input form-select">
                                <option>-- Select --</option>
                                <option>2025</option>
                                <option>2024</option>
                                <option>2023</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label class="input-label">Payroll Type <span class="input-required">*</span></label>
                            <select class="form-input form-select" required>
                                <option>-- Select --</option>
                                <option>Monthly</option>
                                <option>Bi-Weekly</option>
                                <option>Weekly</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label class="input-label">Quarter</label>
                            <select class="form-input form-select">
                                <option>-- Select --</option>
                                <option>Q1</option>
                                <option>Q2</option>
                                <option>Q3</option>
                                <option>Q4</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label class="input-label">Month</label>
                            <select class="form-input form-select">
                                <option>-- Select --</option>
                                <option>January</option>
                                <option>February</option>
                                <option>March</option>
                                <option>April</option>
                                <option>May</option>
                                <option>June</option>
                                <option>July</option>
                                <option>August</option>
                                <option>September</option>
                                <option>October</option>
                                <option>November</option>
                                <option>December</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="input-group">
                            <label class="input-label">Payroll Name</label>
                            <input type="text" class="form-input" placeholder="e.g., October 2025 Payroll">
                        </div>
                        <div class="input-group">
                            <label class="input-label">Attendance Start Date</label>
                            <input type="date" class="form-input">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="input-group">
                            <label class="input-label">Attendance End Date</label>
                            <input type="date" class="form-input">
                        </div>
                        <div class="input-group">
                            <label class="input-label">Date of Payment <span class="input-required">*</span></label>
                            <input type="date" class="form-input" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="input-group">
                            <label class="input-label">Payslip Online Date <span class="input-required">*</span></label>
                            <input type="date" class="form-input" required>
                        </div>
                        <div class="hidden md:block"></div>
                    </div>
                    
                    <div class="input-group col-span-2">
                        <label class="input-label">Description</label>
                        <textarea class="form-input" rows="3" placeholder="Optional description for this payroll cycle"></textarea>
                    </div>
                    
                    <div class="pt-4 border-t border-slate-200">
                        <h4 class="text-sm text-blue-600 font-bold uppercase mb-4 flex items-center gap-2">
                            <i data-lucide="building" class="w-4 h-4"></i>
                            Bank Configuration
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="input-group">
                                <label class="input-label">Bank Name</label>
                                <select class="form-input form-select">
                                    <option>-- Select Bank --</option>
                                    <option>HDFC Bank</option>
                                    <option>ICICI Bank</option>
                                    <option>State Bank of India</option>
                                    <option>Axis Bank</option>
                                </select>
                            </div>
                            <div class="input-group">
                                <label class="input-label">Account Number</label>
                                <input type="text" class="form-input" placeholder="Enter account number">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="input-group">
                                <label class="input-label">IFSC Code</label>
                                <input type="text" class="form-input" placeholder="Enter IFSC code">
                            </div>
                            <div class="input-group">
                                <label class="input-label">Bank Address</label>
                                <input type="text" class="form-input" placeholder="Bank branch address">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="input-group">
                                <label class="input-label">Cheque Number</label>
                                <input type="text" class="form-input" placeholder="Optional cheque number">
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-6 flex justify-end gap-3 border-t border-slate-200 mt-6">
                        <button type="button" data-action="close-modal" class="px-6 py-2.5 text-sm font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-700 hover:to-sky-700 text-white rounded-lg text-sm font-bold shadow-lg shadow-blue-200 transition-all transform active:scale-95">
                            Save Payroll Period
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>