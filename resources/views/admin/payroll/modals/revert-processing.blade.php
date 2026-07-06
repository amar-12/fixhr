<div id="revert-processing-modal" class="modal-overlay hidden" style="display: none;">
    <div class="modal-container max-w-3xl">
        <div class="modal-header">
            <h3 class="modal-title">Revert Salary Processing</h3>
            <button class="modal-close-btn" data-action="close-modal">
                <i data-lucide="x"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <div class="modal-alert warning">
                <i data-lucide="refresh-cw"></i>
                <div>
                    <strong>Revert Salary Processing?</strong>
                    <p style="font-size: 0.75rem; margin-top: 0.25rem;">
                        You are going back to the processing stage. Select employees to unprocess (revert to pending).
                    </p>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <span class="modal-input-label">Processed Employees (<span class="processed-count">0</span>)</span>
                <button class="select-all-btn modal-btn modal-btn-secondary" style="font-size: 0.75rem;">
                    Select All
                </button>
            </div>
            
            <div id="revert-employee-list" class="modal-employee-list">
                <!-- Employee items will be inserted here -->
            </div>
            
            <div class="modal-empty-state empty-state" style="display: none;">
                <i data-lucide="users" style="width: 3rem; height: 3rem; margin-bottom: 1rem;"></i>
                <p>No processed employees found</p>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="modal-btn modal-btn-secondary" data-action="close-modal">
                Cancel
            </button>
            <button class="modal-btn modal-btn-primary confirm-revert-btn" disabled>
                <i data-lucide="refresh-cw" style="width: 16px; height: 16px; margin-right: 0.5rem;"></i>
                Revert & Go Back
            </button>
        </div>
    </div>
</div>

<style>
    /* Additional modal styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 1rem;
    }

    .modal-overlay.flex {
        display: flex;
    }

    .modal-overlay.hidden {
        display: none;
    }

    .employee-checkbox {
        cursor: pointer;
    }

    .employee-item:hover {
        background: #f8fafc;
    }

    .employee-salary {
        font-family: monospace;
    }

    /* Animation for modal */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideInDown {
        from {
            transform: translateY(-30px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-overlay.flex {
        animation: fadeIn 0.3s ease-out;
    }

    .modal-container {
        animation: slideInDown 0.3s ease-out;
    }
</style>