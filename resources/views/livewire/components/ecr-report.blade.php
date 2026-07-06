<!-- ECR Report -->
<div class="glass-card hover-effect" style="padding: 16px; flex: 1 1 calc(25% - 16px); min-width: 200px;">
    <div style="width: 36px; height: 36px; border-radius: 8px; background: #dbeafe; display: flex; align-items: center; justify-content: center; color: #1d4ed8; margin-bottom: 12px;">
        <i data-lucide="file-text" style="width:18px;height:18px;"></i>
    </div>

    <h3 class="head-text" style="font-size: 0.85rem; margin-bottom: 6px; font-weight: 600;">ECR Report</h3>

    <p class="para-text" style="font-size: 0.7rem; color: #64748b; margin-bottom: 10px; line-height: 1.3;">
        @if($totalEmployees > 0)
            {{ $totalEmployees }} PF Employees
        @else
            No PF Employees
        @endif
    </p>

    <button wire:click="generateReport"
            wire:loading.attr="disabled"
            wire:target="generateReport"
            style="font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 4px;"
            {{ $totalEmployees === 0 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' }}>
        <span wire:loading.remove wire:target="generateReport">
            <i data-lucide="download" style="width: 10px; height: 10px;"></i>
            Download
        </span>
        <span wire:loading wire:target="generateReport">
            <span class="spinner-border spinner-border-sm" style="width: 10px; height: 10px; border-width: 2px;" role="status"></span>
        </span>
    </button>
</div>
