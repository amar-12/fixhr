<!-- Deferred List Modal -->
<div id="deferred-list-modal" class="modal-overlay hidden">
    <div class="modal-container">
        <div class="modal-content">
            <div class="flex items-center justify-between p-6 border-b border-slate-200 bg-slate-50/50">
                <h2 class="text-xl font-bold text-slate-900">Deferred Salaries</h2>
                <button data-action="close-modal" class="text-slate-400 hover:text-slate-600 transition-colors p-1 hover:bg-slate-100 rounded-full">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg flex gap-3">
                    <i data-lucide="calendar-days" class="text-slate-600 shrink-0 w-5 h-5"></i>
                    <div>
                        <h4 class="text-slate-800 font-bold text-sm">Deferred Salary Management</h4>
                        <p class="text-slate-600 text-xs">Manage salaries that were deferred from previous payroll cycles.</p>
                    </div>
                </div>
                
                <div class="space-y-3" id="deferred-employee-list">
                    <!-- Deferred employee items will be populated here -->
                    <div class="deferred-employee-item hidden" data-employee-id="">
                        <div class="flex items-center justify-between p-4 bg-white hover:bg-slate-50 rounded-lg border border-slate-200 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                                    <i data-lucide="calendar-days" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 employee-name">Employee Name</div>
                                    <div class="text-xs text-slate-500">
                                        Deferred: <span class="font-mono text-slate-700 employee-salary">$0.00</span>
                                    </div>
                                    <div class="employee-reason hidden text-xs text-amber-600 mt-1">
                                        <i data-lucide="message-square" class="w-3 h-3 inline mr-1"></i>
                                        <span class="reason-text"></span>
                                    </div>
                                </div>
                            </div>
                            <button class="manage-deferred-btn px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-xs font-bold transition-all shadow-sm">
                                Manage
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Empty state -->
                <div class="empty-state text-center py-8 text-slate-400">
                    <i data-lucide="calendar-x" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                    <p>No deferred salaries found.</p>
                    <p class="text-xs mt-1">Salaries deferred from previous cycles will appear here.</p>
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-slate-200">
                    <div class="text-xs text-slate-500">
                        Showing <span class="deferred-count">0</span> deferred salaries
                    </div>
                    <button data-action="close-modal" class="px-4 py-2 text-slate-500 hover:text-slate-700 text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize deferred list modal
    const deferredModal = document.getElementById('deferred-list-modal');
    if (!deferredModal) return;

    // Open deferred list modal
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-action="open-deferred-list"]')) {
            openDeferredListModal();
        }
    });

    function openDeferredListModal() {
        if (window.payrollApp) {
            window.payrollApp.openModal('deferred-list');
            renderDeferredListModal();
        }
    }

    function renderDeferredListModal() {
        if (!window.payrollApp) return;

        const deferredEmployees = window.payrollApp.getDeferredEmployees();
        const employeeList = document.getElementById('deferred-employee-list');
        const deferredCount = deferredModal.querySelector('.deferred-count');
        const emptyState = deferredModal.querySelector('.empty-state');

        // Update count
        deferredCount.textContent = deferredEmployees.length;

        // Clear existing items except empty state
        const existingItems = employeeList.querySelectorAll('.deferred-employee-item');
        existingItems.forEach(item => item.remove());

        if (deferredEmployees.length === 0) {
            employeeList.classList.add('hidden');
            emptyState.classList.remove('hidden');
        } else {
            employeeList.classList.remove('hidden');
            emptyState.classList.add('hidden');

            // Add deferred employee items
            deferredEmployees.forEach(employee => {
                const item = document.createElement('div');
                item.className = 'deferred-employee-item';
                item.dataset.employeeId = employee.id;
                
                const reasonHtml = employee.holdReason ? `
                    <div class="employee-reason text-xs text-amber-600 mt-1">
                        <i data-lucide="message-square" class="w-3 h-3 inline mr-1"></i>
                        <span class="reason-text">${employee.holdReason}</span>
                    </div>
                ` : '<div class="employee-reason hidden"></div>';
                
                item.innerHTML = `
                    <div class="flex items-center justify-between p-4 bg-white hover:bg-slate-50 rounded-lg border border-slate-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                                <i data-lucide="calendar-days" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-900 employee-name">${employee.name}</div>
                                <div class="text-xs text-slate-500">
                                    Deferred: <span class="font-mono text-slate-700 employee-salary">$${employee.salary}</span>
                                </div>
                                ${reasonHtml}
                            </div>
                        </div>
                        <button class="manage-deferred-btn px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-xs font-bold transition-all shadow-sm">
                            Manage
                        </button>
                    </div>
                `;
                employeeList.appendChild(item);
            });
        }

        // Initialize Lucide icons
        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    // Handle manage deferred action
    deferredModal.addEventListener('click', function(e) {
        if (e.target.classList.contains('manage-deferred-btn')) {
            const employeeItem = e.target.closest('.deferred-employee-item');
            const employeeId = employeeItem.dataset.employeeId;
            handleManageDeferred(employeeId);
        }
    });

    function handleManageDeferred(employeeId) {
        if (window.payrollApp) {
            const employee = window.payrollApp.employees.find(e => e.id === employeeId);
            if (employee) {
                window.payrollApp.selectedEmployee = employee;
                window.payrollApp.closeModal('deferred-list');
                window.payrollApp.openModal('deferred-action');
            }
        }
    }
});
</script>