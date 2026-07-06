<div>
    @if($showButton)
        <button
            type="button"
            wire:click="generateCombinedReport"
            style="{{ $buttonStyle }}"
            wire:loading.attr="disabled"
            wire:target="generateCombinedReport"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            <span wire:loading.remove wire:target="generateCombinedReport">Complete Report</span>
            <span wire:loading wire:target="generateCombinedReport">Generating...</span>
        </button>
    @endif

    <div wire:loading wire:target="generateCombinedReport" class="mt-2 text-sm text-blue-600">
        Preparing combined report with 5 sheets...
    </div>
</div>
