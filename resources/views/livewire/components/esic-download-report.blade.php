<div>
    <!-- ESIC Report Card - Direct Download -->
    <div class="glass-card hover-effect" style="padding: 16px; flex: 1 1 calc(25% - 16px); min-width: 200px;">
        <div style="width: 36px; height: 36px; border-radius: 8px; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626; margin-bottom: 12px;">
            <i data-lucide="activity" style="width:18px;height:18px;"></i>
        </div>
        <h3 class="head-text" style="font-size: 0.85rem; margin-bottom: 6px; font-weight: 600;">ESIC Report</h3>
        <p class="para-text" style="font-size: 0.7rem; color: #64748b; margin-bottom: 10px; line-height: 1.3;">
            ESIC_Report.xlsx
        </p>

        <!-- Round Off Toggle -->
        <div class="mb-3 flex items-center">
            <input type="checkbox"
                   id="roundOffToggle"
                   wire:model.live="roundOffValues"
                   class="h-3 w-3 text-red-600 focus:ring-red-500 border-gray-300 rounded">
            <label for="roundOffToggle" class="ml-1 block text-xs font-medium text-gray-700">
                Round off values
            </label>
        </div>

        <!-- Current Period Info -->
        @if($currentPayrollPeriod)
            <p class="text-xs text-gray-600 mb-2">
                Current: {{ $currentPayrollPeriod->pp_name }}
            </p>
        @endif

        <!-- Download Button -->
        <button wire:click="downloadReport"
                wire:loading.attr="disabled"
                style="font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 4px;">
            <i data-lucide="download" style="width: 10px; height: 10px;"></i>
            <span wire:loading.remove>Download Report</span>
            <span wire:loading>
                <svg class="animate-spin h-3 w-3 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Generating...
            </span>
        </button>

        <!-- Info Text -->
        <p class="text-xs text-gray-500 mt-2">
            Downloads complete ESIC report for current payroll period
        </p>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            lucide.createIcons();

            // Listen for download completion
            Livewire.on('download-started', () => {
                // You can add any UI feedback here
            });
        });
    </script>
    @endpush
</div>
