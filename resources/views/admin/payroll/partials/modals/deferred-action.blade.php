<div id="deferred-action-modal" class="modal-overlay hidden">
    <div class="modal-container">
        <div class="modal-content">
            <div class="flex items-center justify-between p-6 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Manage Deferred Salary</h2>
                <button data-action="close-modal" class="text-slate-400 hover:text-slate-600 transition-colors p-1 hover:bg-slate-100 rounded-full">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6 max-h-[80vh] overflow-y-auto custom-scrollbar">
                <div class="space-y-6">
                    <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="w-12 h-12 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-600 text-lg shadow-inner employee-initial">R</div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 employee-name">Robert Johnson</h3>
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <i data-lucide="calendar-days" class="w-3 h-3"></i>
                                Deferred Amount: <span class="text-slate-900 font-mono font-bold employee-salary">$3,800</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <button data-action="merge-deferred" class="w-full p-4 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl flex items-center justify-between group transition-all text-left">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                                    <i data-lucide="merge" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="text-slate-900 font-bold text-sm">Add to Current Payroll Cycle</h4>
                                    <p class="text-slate-500 text-xs">Merge with October salary (Total: $3,800 + Current)</p>
                                </div>
                            </div>
                            <i data-lucide="arrow-right" class="w-4 h-4 text-blue-500 group-hover:translate-x-1 transition-transform"></i>
                        </button>

                        <button data-action="separate-deferred" class="w-full p-4 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl flex items-center justify-between group transition-all text-left">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600">
                                    <i data-lucide="external-link" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="text-slate-900 font-bold text-sm">Release Individually</h4>
                                    <p class="text-slate-500 text-xs">Process as a separate off-cycle transaction.</p>
                                </div>
                            </div>
                            <i data-lucide="arrow-right" class="w-4 h-4 text-emerald-600 group-hover:translate-x-1 transition-transform"></i>
                        </button>
                    </div>
                    
                    <!-- Additional Information -->
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                        <h4 class="text-amber-800 font-bold text-sm mb-2 flex items-center gap-2">
                            <i data-lucide="info" class="w-4 h-4"></i>
                            Important Note
                        </h4>
                        <p class="text-amber-700 text-xs">
                            Deferred salaries are processed outside the regular payroll cycle and may have different tax implications.
                            Please ensure proper documentation for audit purposes.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>