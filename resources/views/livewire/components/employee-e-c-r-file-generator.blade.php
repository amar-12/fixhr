<div>
    <!-- Single Card Button as per Bank Sheet design -->
    <div class="glass-card hover-effect" style="padding: 16px; flex: 1 1 calc(25% - 16px); min-width: 200px;">
        <div style="width: 36px; height: 36px; border-radius: 8px; background: #dbeafe; display: flex; align-items: center; justify-content: center; color: #2563eb; margin-bottom: 12px;">
            <i data-lucide="activity" style="width:18px;height:18px;"></i>
        </div>
        <h3 class="head-text" style="font-size: 0.85rem; margin-bottom: 6px; font-weight: 600;">Employee ECR</h3>
        <p class="para-text" style="font-size: 0.7rem; color: #64748b; margin-bottom: 10px; line-height: 1.3;">
            Active Employees ({{ $this->getActiveEmployeeCount() }})
        </p>
        <button
            x-on:click="$wire.generateECR()"
            wire:loading.attr="disabled"
            style="font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 4px;"
        >
            <span wire:loading.remove wire:target="generateECR">
                <i data-lucide="download" style="width: 10px; height: 10px;"></i>
                Download
            </span>
            <span wire:loading wire:target="generateECR">
                <i data-lucide="loader" style="width: 10px; height: 10px; animation: spin 1s linear infinite;"></i>
                Generating...
            </span>
        </button>
    </div>

    <!-- Progress/Status Section (appears below the card when generating) -->
    @if($isGenerating)
        <div style="margin-top: 16px; padding: 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span style="font-size: 0.75rem; color: #475569;">Processing employees...</span>
                <span style="font-size: 0.75rem; font-weight: 600; color: #3b82f6;">
                    {{ $stats['processed'] }}/{{ $stats['total'] }}
                </span>
            </div>

            <!-- Progress Bar -->
            <div style="width: 100%; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; margin-bottom: 8px;">
                <div style="height: 100%; background: #3b82f6; width: {{ $stats['total'] > 0 ? ($stats['processed'] / $stats['total'] * 100) : 0 }}%; transition: width 0.3s ease;"></div>
            </div>

            <div style="display: flex; gap: 16px; font-size: 0.7rem;">
                <span style="color: #10b981;">✓ Success: {{ $stats['success'] }}</span>
                @if($stats['failed'] > 0)
                    <span style="color: #ef4444;">✗ Failed: {{ $stats['failed'] }}</span>
                @endif
            </div>
        </div>
    @endif

    <!-- Error Messages (silent - only shown in UI, no alerts) -->
    @if(!empty($validationErrors) && !$isGenerating)
        <div style="margin-top: 12px; padding: 8px; background: #fef2f2; border: 1px solid #fee2e2; border-radius: 6px;">
            <div style="font-size: 0.7rem; color: #991b1b; margin-bottom: 4px;">Errors:</div>
            <ul style="margin: 0; padding-left: 16px;">
                @foreach($validationErrors as $error)
                    <li style="font-size: 0.65rem; color: #b91c1c;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Success Message (silent - only shown in UI, no alerts) -->
    @if(session()->has('success') && !$isGenerating)
        <div style="margin-top: 12px; padding: 8px; background: #f0fdf4; border: 1px solid #dcfce7; border-radius: 6px; font-size: 0.7rem; color: #166534;">
            {{ session('success') }}
        </div>
    @endif

    <!-- JavaScript for File Download Only (no alerts) -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            // File download handler only
            Livewire.on('download-file', ({ content, filename }) => {
                const blob = new Blob([content], { type: 'text/plain' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            });

            // Silently log to console only
            Livewire.on('show-error', ({ message }) => {
                console.error('ECR Generation Error:', message);
            });

            Livewire.on('show-success', ({ message }) => {
                console.log('ECR Generation Success:', message);
            });
        });

        // Add spin animation for loader
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
</div>
