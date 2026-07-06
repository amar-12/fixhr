<div class="row d-flex justify-content-center align-items-start ef-wrapper"
     style="padding-top: 20px; padding-bottom: 20px;">

<style>
/* ─── CSS tokens ─── */
:root {
    --ef-bg:          #ffffff;
    --ef-surface:     #f8f9fb;
    --ef-surface2:    #f1f3f7;
    --ef-border:      #e2e6ee;
    --ef-text:        #1a1d2e;
    --ef-muted:       #6b7080;
    --ef-accent:      #4f7cff;
    --ef-accent2:     #7c5cfc;
    --ef-radius:      10px;
    --ef-input-h:     32px;
    --ef-font-sm:     12px;
    --ef-shadow:      0 2px 8px rgba(0,0,0,0.07);
    --ef-shadow-lg:   0 8px 32px rgba(0,0,0,0.10);
    --ef-badge-bg:    rgba(79,124,255,0.10);
    --ef-badge-bdr:   rgba(79,124,255,0.28);
    --ef-badge-text:  #4f7cff;
}
[data-bs-theme="dark"], .dark-mode {
    --ef-bg:        #151636;
    --ef-surface:   #151636;
    --ef-surface2:  #151636;
    --ef-border:    rgba(255,255,255,0.08);
    --ef-text:      #e8eaf0;
    --ef-muted:     #6b7080;
    --ef-shadow:    0 2px 8px rgba(0,0,0,0.35);
    --ef-shadow-lg: 0 8px 32px rgba(0,0,0,0.50);
}

/* ─── Panel ─── */
.ef-panel {
    background: var(--ef-bg);
    border: 1px solid var(--ef-border);
    border-radius: 20px;
    padding: 36px 40px 32px;
    box-shadow: var(--ef-shadow-lg), 0 0 0 1px rgba(255,255,255,0.04);
}

/* ─── Grid shrink (when filter panel open) ─── */
.ef-fields-grid {
    transition: padding-right 0.3s ease;
    padding-right: 0;
}
.ef-fields-grid.ef-grid-shrink {
    padding-right: 252px;
}

/* ─── Panel header ─── */
.ef-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
}
.ef-panel-title {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--ef-muted);
}

/* ─── Filter toggle button (top-right) ─── */
.ef-filter-toggle-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: var(--ef-surface2);
    border: 1px solid var(--ef-border);
    border-radius: 8px;
    padding: 6px 12px;
    color: var(--ef-muted);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: border-color .2s, color .2s;
    white-space: nowrap;
}
.ef-filter-toggle-btn:hover { border-color: var(--ef-accent); color: var(--ef-accent); }
.ef-filter-toggle-btn svg  { width: 13px; height: 13px; flex-shrink: 0; }

/* Floating dropdown panel */
.ef-filter-panel-wrap { position: relative; }
.ef-filter-panel {
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    width: 232px;
    z-index: 1055;
    background: var(--ef-bg);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow-lg);
    padding: 14px 16px;
    animation: efFadeDown .18s ease both;
}
@keyframes efFadeDown {
    from { opacity:0; transform:translateY(-6px); }
    to   { opacity:1; transform:translateY(0); }
}
.ef-filter-panel .ef-fp-heading {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--ef-muted);
    margin-bottom: 10px;
}
.ef-switch-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 5px 0;
    border-bottom: 1px solid var(--ef-border);
}
.ef-switch-row:last-child { border-bottom: none; }
.ef-switch-row label { font-size: 12px; color: var(--ef-text); margin: 0; cursor: pointer; }
.form-check-input:checked { background-color: var(--ef-accent); border-color: var(--ef-accent); }

/* ─── Inputs / selects ─── */
.ef-control {
    width: 100%;
    height: var(--ef-input-h);
    background: var(--ef-surface2);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    padding: 0 34px 0 12px;
    color: var(--ef-text);
    font-size: var(--ef-font-sm);
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    appearance: none;
    -webkit-appearance: none;
}
.ef-control::placeholder { color: var(--ef-muted); }
.ef-control:focus {
    border-color: var(--ef-accent);
    box-shadow: 0 0 0 3px rgba(79,124,255,.13);
}

.ef-input-wrap { position: relative; }
.ef-input-wrap .ef-icon {
    position: absolute;
    right: 11px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--ef-muted);
    pointer-events: none;
    display: flex;
    align-items: center;
}
.ef-input-wrap .ef-icon svg { width: 13px; height: 13px; }

/* ─── Field label (above) ─── */
.ef-field-label {
    display: block;
    font-size: 11.5px;
    font-weight: 600;
    color: var(--ef-muted);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin-bottom: 5px;
}

/* ─── Results dropdown ─── */
.ef-results {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    width: 100%;
    z-index: 1060;
    background: var(--ef-bg);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow-lg);
    max-height: 200px;
    overflow-y: auto;
    padding: 4px 0;
    list-style: none;
    margin: 0;
}
.ef-results li {
    font-size: var(--ef-font-sm);
    padding: 7px 14px;
    cursor: pointer;
    color: var(--ef-text);
    transition: background .15s;
}
.ef-results li:hover       { background: var(--ef-surface2); }
.ef-results li.ef-no-result { color: var(--ef-muted); cursor: default; }

/* ─── Badge ─── */
.ef-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: var(--ef-badge-bg);
    border: 1px solid var(--ef-badge-bdr);
    color: var(--ef-badge-text);
    font-size: 11px;
    padding: 3px 9px;
    border-radius: 20px;
    margin-top: 5px;
}
.ef-badge-remove {
    background: none; border: none; color: inherit;
    cursor: pointer; display: flex; align-items: center;
    padding: 0; opacity: .7; line-height: 1; transition: opacity .2s;
}
.ef-badge-remove:hover { opacity: 1; }

/* ─── Divider ─── */
.ef-divider { height: 1px; background: var(--ef-border); margin: 8px 0; }

/* ─── Additional Details toggle link ─── */
.ef-additional {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    color: var(--ef-muted);
    font-size: 12.5px;
    font-weight: 500;
    user-select: none;
    transition: color .2s;
    padding: 4px 0;
}
.ef-additional:hover { color: var(--ef-text); }
.ef-additional .ef-chevron {
    width: 16px; height: 16px;
    border: 1.5px solid currentColor;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: transform .3s;
}
.ef-additional.open .ef-chevron { transform: rotate(180deg); }

/* ─── Accordion body ─── */
.ef-acc-body {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-top: none;
    border-radius: 0 0 var(--ef-radius) var(--ef-radius);
    padding: 14px 16px;
}
.ef-acc-body .form-check-label { font-size: var(--ef-font-sm); color: var(--ef-text); }

/* ─── Sort row ─── */
.ef-sort-row {
    display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
}
.ef-sort-label { font-size: var(--ef-font-sm); font-weight: 600; color: var(--ef-muted); }
.ef-sort-row .form-check-label { font-size: var(--ef-font-sm); color: var(--ef-text); }

/* ─── Export button ─── */
.ef-btn-export {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, var(--ef-accent), var(--ef-accent2));
    border: none;
    color: #fff;
    font-size: 13.5px;
    font-weight: 500;
    padding: 11px 24px;
    border-radius: var(--ef-radius);
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(79,124,255,.30);
    transition: opacity .2s, transform .15s, box-shadow .2s;
}
.ef-btn-export:hover {
    opacity: .92;
    transform: translateY(-1px);
    box-shadow: 0 8px 28px rgba(79,124,255,.40);
}
.ef-btn-export:active { transform: scale(.98); }
.ef-btn-export svg { width: 15px; height: 15px; }

/* ─── Reset button ─── */
.ef-btn-reset {
    background: none; border: none;
    color: var(--ef-muted); font-size: 13px;
    cursor: pointer; padding: 0;
    transition: color .2s;
}
.ef-btn-reset:hover { color: var(--ef-text); }

/* ─── Alert ─── */
.ef-alert { border-radius: var(--ef-radius); font-size: var(--ef-font-sm); }
.custom-close-button {
    background: transparent; border: none;
    font-size: 1.25rem; font-weight: bold;
    color: inherit; cursor: pointer;
    padding: 0; margin-left: 1rem;
    transition: opacity .2s;
}
.custom-close-button:hover  { opacity: .6; }
.custom-close-button::before { content: '×'; }
</style>

    <div class="col-md-10">

        @if (session()->has('error'))
            <div class="alert alert-danger ef-alert alert-dismissible fade show d-flex justify-content-between align-items-center mb-3" role="alert">
                <div><strong>Error!</strong> {{ session('error') }}</div>
                <button type="button" class="custom-close-button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="ef-panel">

            {{-- Header --}}
            <div class="ef-panel-header">
                <span class="ef-panel-title">Loan Registration Report Filters</span>
            </div>

            {{-- Fields Grid --}}
            <div class="row gx-3 gy-3">

                {{-- Employee Status --}}
                <div class="col-md-6">
                    <label class="ef-field-label">Employee Status</label>
                    <div class="ef-input-wrap">
                        <select wire:model.live="employeeStatusId" class="ef-control">
                            <option value="">All</option>
                            @foreach ($employeeStatus as $status)
                                <option value="{{ $status->m_id }}">{{ $status->m_name }}</option>
                            @endforeach
                        </select>
                        <span class="ef-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></span>
                    </div>
                    @error('employeeStatusFilter') <span class="text-danger" style="font-size:11px;">{{ $message }}</span> @enderror
                </div>

                {{-- Employee / Code --}}
                <x-search-input label="Employee / Code" wireModel="searchEmployee" :results="$employees"
                    selectMethod="selectEmployee" idField="emp_id" nameField="emp_full_name"
                    errorField="selectedEmployeeId" mainCol="col-md-6" />

                {{-- Financial Year --}}
                <div class="col-md-6">
                    <label class="ef-field-label">Financial Year</label>
                    <div class="ef-input-wrap">
                        <select wire:model="selectedYear"
                                wire:change="selectYear($event.target.value)"
                                class="ef-control">
                            <option value="">Select Year</option>
                            @foreach ($financialYears as $year)
                                <option value="{{ $year->fy_id }}">{{ $year->fy_year }}</option>
                            @endforeach
                        </select>
                        <span class="ef-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></span>
                    </div>
                    @error('selectedYear') <span class="text-danger" style="font-size:11px;">{{ $message }}</span> @enderror
                </div>

                {{-- Month --}}
                <div class="col-md-6">
                    <label class="ef-field-label">Month</label>
                    <div class="ef-input-wrap">
                        <select wire:model="selectedMonth" class="ef-control">
                            <option value="">Select Month</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                            @endfor
                        </select>
                        <span class="ef-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></span>
                    </div>
                    @error('selectedMonth') <span class="text-danger" style="font-size:11px;">{{ $message }}</span> @enderror
                </div>

                {{-- Approval Status --}}
                <div class="col-md-6">
                    <label class="ef-field-label">Approval Status</label>
                    <div class="ef-input-wrap">
                        <select wire:model.live="approvalStatusId"
                                wire:change="selectApprovalStatus($event.target.value)"
                                class="ef-control">
                            <option value="">All</option>
                            @foreach ($approvalStatus as $status)
                                <option value="{{ $status->m_id }}">{{ $status->m_name }}</option>
                            @endforeach
                        </select>
                        <span class="ef-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></span>
                    </div>
                    @error('approvalStatusId') <span class="text-danger" style="font-size:11px;">{{ $message }}</span> @enderror
                </div>

                {{-- Divider --}}
                <div class="col-12"><div class="ef-divider"></div></div>

               
                {{-- Footer --}}
                <div class="col-12 d-flex align-items-center justify-content-end ">
                    
                    <button type="button" class="ef-btn-export"
                            wire:click="generateReport"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50"
                            wire:target="generateReport">
                        <span class="spinner-border spinner-border-sm d-none"
                              wire:loading.class.remove="d-none" wire:target="generateReport" role="status"></span>
                        <span wire:loading.class="d-none" wire:target="generateReport">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            Export
                        </span>
                        <span class="d-none" wire:loading.class.remove="d-none" wire:target="generateReport">Downloading…</span>
                    </button>
                </div>

            </div>{{-- /.row --}}
        </div>{{-- /.ef-panel --}}
    </div>

    <script>
        document.addEventListener('livewire:initialized', function () {
            Livewire.on('show-alert', (data) => {
                Swal.fire({ position:'top-end', icon:data[0].type||'success', title:data[0].message||'',
                    toast:true, showConfirmButton:false, timer:3000, timerProgressBar:true,
                    customClass:{popup:'swal2-toast-custom'} });
            });
        });
    </script>
</div>