<div style="padding: 20px;">
    <div class="alert alert-warning mb-3">
        <i data-lucide="alert-triangle" class="me-2"></i>
        <strong>Final Confirmation!</strong> This action cannot be undone.
    </div>
    
    <div class="card border-danger mb-3">
        <div class="card-body text-center">
            <i data-lucide="lock" style="width: 48px; height: 48px;" class="text-danger mb-3"></i>
            <h5>Ready to Finalize?</h5>
            <p>Once finalized, this weekly payroll will be permanently locked.</p>
            <button onclick="finalizeWeeklyPayroll()" class="btn btn-danger btn-lg">
                <i data-lucide="lock"></i> Finalize & Lock Payroll
            </button>
        </div>
    </div>
    
    <div class="text-end mt-3">
        <button onclick="showView('STEP4')" class="btn btn-secondary">
            <i data-lucide="arrow-left"></i> Back to Verification
        </button>
    </div>
</div>