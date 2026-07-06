<div id="hold-reason-modal" class="modal-overlay hidden">
    <div class="modal-container">
        <div class="modal-content">
            <div class="flex items-center justify-between p-6 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Hold Salary Confirmation</h2>
                <button data-action="close-modal" class="text-slate-400 hover:text-slate-600 transition-colors p-1 hover:bg-slate-100 rounded-full">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6 max-h-[80vh] overflow-y-auto custom-scrollbar">
                <form data-form="hold-reason" class="space-y-4">
                    <div class="flex gap-3 items-start p-4 bg-amber-50 border border-amber-200 rounded-lg">
                        <i data-lucide="alert-triangle" class="text-amber-600 shrink-0 w-5 h-5 mt-0.5"></i>
                        <div>
                            <h4 class="text-amber-800 font-bold text-sm">Hold Salary</h4>
                            <p class="text-amber-700 text-xs">This will prevent the salary from being processed in the current batch.</p>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label class="input-label">Reason for Holding</label>
                        <textarea 
                            class="form-input" 
                            placeholder="e.g. Pending clearance, Disciplinary action, Documentation pending..."
                            rows="3"
                            required
                        ></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" data-action="close-modal" class="px-4 py-2 text-slate-500 hover:text-slate-700 text-sm">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-sm">
                            Confirm Hold
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>