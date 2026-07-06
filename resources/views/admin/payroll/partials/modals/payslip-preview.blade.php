<div id="payslip-preview-modal" class="modal-overlay hidden">
    <div class="modal-container max-w-3xl">
        <div class="modal-content">
            <div class="flex items-center justify-between p-6 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Payslip Preview</h2>
                <button data-action="close-modal" class="text-slate-400 hover:text-slate-600 transition-colors p-1 hover:bg-slate-100 rounded-full">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6 max-h-[80vh] overflow-y-auto custom-scrollbar">
                <div class="payslip-preview">
                    <div class="flex justify-between border-b-2 border-slate-800 pb-4 mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">PAYSLIP</h1>
                            <p class="text-sm text-slate-500">Confidential Statement - October 2025</p>
                        </div>
                        <div class="text-right">
                            <h2 class="font-bold text-lg">OCTOBER 2025</h2>
                            <p class="text-sm text-slate-500">Payment Date: 31 Oct 2025</p>
                        </div>
                    </div>
                    
                    <!-- Employee Information -->
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="font-bold text-slate-900 mb-2">Employee Information</h3>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Employee ID:</span>
                                    <span class="font-mono font-bold">EMP001</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Name:</span>
                                    <span class="font-bold">Sarah Connor</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Department:</span>
                                    <span>Engineering</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Designation:</span>
                                    <span>Senior Developer</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 mb-2">Payment Details</h3>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Bank:</span>
                                    <span>HDFC Bank</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Account No:</span>
                                    <span class="font-mono">XXXXXX1234</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Days Worked:</span>
                                    <span>31 Days</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600">LOP Days:</span>
                                    <span>0 Days</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Earnings & Deductions -->
                    <div class="grid grid-cols-2 gap-0 border border-slate-300 text-sm mb-6">
                        <div class="border-r border-slate-300 p-4">
                            <h3 class="font-bold border-b pb-2 mb-2 uppercase text-xs tracking-wider">Earnings</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>Basic Salary</span>
                                    <span class="font-mono">4,500.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>House Rent Allowance</span>
                                    <span class="font-mono">2,250.00</span>
                                </div>
                                <div class="flex justify-between text-emerald-600 font-bold">
                                    <span>Ad-Hoc Bonus</span>
                                    <span class="font-mono">450.00</span>
                                </div>
                                <div class="flex justify-between font-bold mt-4 pt-2 border-t border-slate-300">
                                    <span>Total Earnings</span>
                                    <span class="font-mono">7,200.00</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold border-b pb-2 mb-2 uppercase text-xs tracking-wider">Deductions</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>Provident Fund</span>
                                    <span class="font-mono">1,800.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Professional Tax</span>
                                    <span class="font-mono">200.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>TDS</span>
                                    <span class="font-mono">0.00</span>
                                </div>
                                <div class="flex justify-between font-bold mt-4 pt-2 border-t border-slate-300">
                                    <span>Total Deductions</span>
                                    <span class="font-mono">2,000.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Net Payable -->
                    <div class="bg-slate-100 p-4 font-bold text-xl flex justify-between rounded">
                        <span>Net Payable</span>
                        <span class="text-blue-600">$5,200.00</span>
                    </div>
                    
                    <!-- Additional Information -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="font-bold text-blue-900 text-sm mb-2">Additional Information</h4>
                        <div class="grid grid-cols-2 gap-4 text-xs text-blue-700">
                            <div>
                                <span class="font-semibold">PF Accumulation:</span> $18,500.00
                            </div>
                            <div>
                                <span class="font-semibold">Leave Balance:</span> 12 Days
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-8">
                    <button data-action="close-modal" class="px-4 py-2 text-slate-500 hover:bg-slate-100 rounded transition-colors">
                        Close
                    </button>
                    <button class="px-6 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded shadow flex items-center gap-2 transition-colors">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Print
                    </button>
                    <button class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded shadow flex items-center gap-2 transition-colors">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>