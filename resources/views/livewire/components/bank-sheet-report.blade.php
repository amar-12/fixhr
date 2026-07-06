<div class="glass-card hover-effect"
    style="padding: 16px; flex: 1 1 calc(25% - 16px); min-width: 200px;">

    <div
        style="width: 36px; height: 36px; border-radius: 8px; background: #dbeafe;
               display: flex; align-items: center; justify-content: center;
               color: #2563eb; margin-bottom: 12px;">
        <i data-lucide="building" style="width: 18px; height: 18px;"></i>
    </div>

    <h3 class="head-text"
        style="font-size: 0.85rem; margin-bottom: 6px; font-weight: 600;">
        Bank Sheet
    </h3>

    <p class="para-text"
        style="font-size: 0.7rem; color: #64748b; margin-bottom: 10px; line-height: 1.3;">
        Bank_Sheet.xlsx
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

 

    <button x-on:click="$wire.generateReport()"
        style="font-size: 0.65rem; font-weight: 500; color: #475569;
               background: #f8fafc; border: 1px solid #e2e8f0;
               padding: 4px 8px; border-radius: 5px;
               cursor: pointer; display: flex; align-items: center; gap: 4px;">
        <i data-lucide="download" style="width: 10px; height: 10px;"></i>
        Download
    </button>
</div>
