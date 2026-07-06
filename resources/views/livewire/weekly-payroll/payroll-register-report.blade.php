  <div class="glass-card hover-effect" style="padding: 16px; flex: 1 1 calc(25% - 16px); min-width: 200px;">
            <div
                style="width: 36px; height: 36px; border-radius: 8px; background: #d1fae5; display: flex; align-items: center; justify-content: center; color: #059669; margin-bottom: 12px;">
                <i data-lucide="file-spreadsheet" style="width: 18px; height: 18px;"></i>
            </div>
            <h3 class="head-text" style="font-size: 0.85rem; margin-bottom: 6px; font-weight: 600;">Payroll Register
            </h3>
            <p class="para-text" style="font-size: 0.7rem; color: #64748b; margin-bottom: 10px; line-height: 1.3;">
                Consolidated_Register.xlsx
            </p>

             <!-- Round Off Checkbox -->
            <label for="roundOff"
                style="font-size: 0.65rem; color: #475569; display: flex; align-items: center; margin-bottom: 6px; cursor: pointer;">
                <input type="checkbox"
                    wire:model.live="roundOffValues"
                    id="roundOff"
                    style="margin-right: 6px;">
                Round Off All Values
            </label>
            <button type="button"
                wire:click="generateReport"
                wire:loading.attr="disabled"
                wire:target="generateReport"
                style="font-size: 0.65rem; font-weight: 500; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 5px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 28px;">
                <span wire:loading.remove.inline-flex wire:target="generateReport" style="align-items: center; gap: 4px;">
                    <i data-lucide="download" style="width: 10px; height: 10px;"></i>
                    Download
                </span>
                <span wire:loading.inline-flex wire:target="generateReport" style="align-items: center; gap: 6px; cursor: wait; color: #334155;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="flex-shrink: 0;">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.2"/>
                        <path fill="currentColor" d="M12 2a10 10 0 0 1 10 10h-3a7 7 0 0 0-7-7V2z">
                            <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.8s" repeatCount="indefinite"/>
                        </path>
                    </svg>
                    Downloading…
                </span>
            </button>
        </div>
