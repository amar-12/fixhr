<div id="held-list-modal" class="modal-overlay hidden">
    <div class="modal-container">
        <div class="modal-content">
            <div class="flex items-center justify-between p-6 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Salaries On Hold</h2>
                <button data-action="close-modal" class="text-slate-400 hover:text-slate-600 transition-colors p-1 hover:bg-slate-100 rounded-full">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6 max-h-[80vh] overflow-y-auto custom-scrollbar">
                <div class="space-y-4">
                    <!-- Sample held employees - in real app this would be dynamic -->
                    <div class="flex items-center justify-between p-4 bg-white rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-xs">
                                <i data-lucide="pause-circle" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-900">Sarah Connor</div>
                                <div class="text-xs text-slate-500 flex items-center gap-1">
                                    <i data-lucide="message-square" class="w-3 h-3"></i>
                                    Pending clearance from HR
                                </div>
                            </div>
                        </div>
                        <button data-action="review-employee" data-employee-id="EMP001" class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 rounded-lg text-xs font-bold transition-all flex items-center gap-2 shadow-sm">
                            Review & Release
                        </button>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-white rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-xs">
                                <i data-lucide="pause-circle" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-900">John Wick</div>
                                <div class="text-xs text-slate-500 flex items-center gap-1">
                                    <i data-lucide="message-square" class="w-3 h-3"></i>
                                    Disciplinary action pending
                                </div>
                            </div>
                        </div>
                        <button data-action="review-employee" data-employee-id="EMP002" class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 rounded-lg text-xs font-bold transition-all flex items-center gap-2 shadow-sm">
                            Review & Release
                        </button>
                    </div>
                    
                    <!-- Empty state -->
                    <div class="text-center py-8 text-slate-400 hidden">
                        <i data-lucide="pause-circle" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                        <p>No employees are currently on hold.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>