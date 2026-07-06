<div id="release-action-modal" class="modal-overlay hidden">
    <div class="modal-container">
        <div class="modal-content">
            <div class="flex items-center justify-between p-6 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight release-modal-title">Release Options</h2>
                <button data-action="close-modal" class="text-slate-400 hover:text-slate-600 transition-colors p-1 hover:bg-slate-100 rounded-full">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6 max-h-[80vh] overflow-y-auto custom-scrollbar">
                <!-- Payslip Preview View -->
                <div class="payslip-preview-view hidden">
                    <div class="bg-white text-slate-900 p-8 rounded-lg shadow-xl border border-slate-200">
                        <div class="flex justify-between border-b-2 border-slate-800 pb-4 mb-6">
                            <div>
                                <h1 class="text-2xl font-bold text-slate-900">PAYSLIP PREVIEW</h1>
                                <p class="text-sm text-slate-500">Confidential Statement</p>
                            </div>
                            <div class="text-right">
                                <h2 class="font-bold">OCT 2025</h2>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-0 border border-slate-300 text-sm mb-6">
                            <div class="border-r border-slate-300 p-4">
                                <h3 class="font-bold border-b pb-2 mb-2 uppercase text-xs tracking-wider">Earnings</h3>
                                <div class="flex justify-between mb-1"><span>Basic Salary</span><span>4,500.00</span></div>
                                <div class="flex justify-between mb-1"><span>HRA</span><span>2,250.00</span></div>
                                <div class="flex justify-between mb-1 text-emerald-600 font-bold"><span>Ad-Hoc Adjustment</span><span>450.00</span></div>
                                <div class="flex justify-between font-bold mt-4 pt-2 border-t"><span>Total Earnings</span><span>7,200.00</span></div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold border-b pb-2 mb-2 uppercase text-xs tracking-wider">Deductions</h3>
                                <div class="flex justify-between mb-1"><span>Provident Fund</span><span>1,800.00</span></div>
                                <div class="flex justify-between font-bold mt-4 pt-2 border-t"><span>Total Deductions</span><span>1,800.00</span></div>
                            </div>
                        </div>
                        <div class="bg-slate-100 p-4 font-bold text-xl flex justify-between rounded">
                            <span>Net Payable</span>
                            <span>$5,400.00</span>
                        </div>
                    </div>
                    <div class="flex justify-start mt-6">
                        <button data-action="back-to-options" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-bold">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            Back to Options
                        </button>
                    </div>
                </div>
                
                <!-- Release Options View -->
                <div class="release-options-view">
                    <div class="space-y-6">
                        <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <div class="w-12 h-12 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-700 text-lg shadow-sm employee-initial">S</div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 employee-name">Sarah Connor</h3>
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <span>Held Reason:</span>
                                    <span class="text-amber-700 font-bold bg-amber-100 px-2 py-0.5 rounded hold-reason">Pending clearance from HR</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-3">
                            <button data-action="view-payslip-preview" class="w-full p-4 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between group transition-all shadow-sm">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                                        <i data-lucide="file-text" class="w-5 h-5"></i>
                                    </div>
                                    <div class="text-left">
                                        <h4 class="text-slate-900 font-bold text-sm">View Payslip Preview</h4>
                                        <p class="text-slate-500 text-xs">Verify calculations before releasing</p>
                                    </div>
                                </div>
                                <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-colors"></i>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-200">
                            <button data-action="release-defer" class="p-3 bg-white border border-slate-300 hover:border-slate-400 rounded-xl text-slate-600 hover:text-slate-800 text-xs font-bold flex flex-col items-center gap-2 transition-all shadow-sm">
                                <i data-lucide="calendar-days" class="w-5 h-5"></i>
                                Defer to Next Cycle
                                <span class="text-[10px] text-slate-400 font-normal">Pay in Nov 2025</span>
                            </button>
                            <button data-action="release-now" class="p-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex flex-col items-center gap-2 shadow-lg shadow-emerald-200 transition-all">
                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                                Release Now
                                <span class="text-[10px] text-emerald-100 font-normal">Pay in Current Cycle</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>