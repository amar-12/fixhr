<div class="row d-flex justify-content-center align-items-start ef-wrapper"
     style="padding-top: 20px; padding-bottom: 20px;">

<style>
:root {
    --ef-bg:        #ffffff;
    --ef-surface:   #f8f9fb;
    --ef-surface2:  #f1f3f7;
    --ef-border:    #e2e6ee;
    --ef-text:      #1a1d2e;
    --ef-muted:     #6b7080;
    --ef-accent:    #4f7cff;
    --ef-accent2:   #7c5cfc;
    --ef-radius:    10px;
    --ef-input-h:   32px;
    --ef-font-sm:   12px;
    --ef-shadow-lg: 0 8px 32px rgba(0,0,0,0.10);
}
[data-bs-theme="dark"], .dark-mode {
    --ef-bg:        #0d0f14;
    --ef-surface:   #14171f;
    --ef-surface2:  #1c2030;
    --ef-border:    rgba(255,255,255,0.08);
    --ef-text:      #e8eaf0;
    --ef-muted:     #6b7080;
    --ef-shadow-lg: 0 8px 32px rgba(0,0,0,0.50);
}
.ef-panel { background:var(--ef-bg); border:1px solid var(--ef-border); border-radius:20px; padding:36px 40px 32px; box-shadow:var(--ef-shadow-lg),0 0 0 1px rgba(255,255,255,0.04); }
.ef-panel-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; }
.ef-panel-title { font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:var(--ef-muted); }
.ef-control { width:100%; height:var(--ef-input-h); background:var(--ef-surface2); border:1px solid var(--ef-border); border-radius:var(--ef-radius); padding:0 34px 0 12px; color:var(--ef-text); font-size:var(--ef-font-sm); outline:none; transition:border-color .2s,box-shadow .2s; appearance:none; -webkit-appearance:none; }
.ef-control::placeholder { color:var(--ef-muted); }
.ef-control:focus { border-color:var(--ef-accent); box-shadow:0 0 0 3px rgba(79,124,255,.13); }
.ef-input-wrap { position:relative; }
.ef-input-wrap .ef-icon { position:absolute; right:11px; top:50%; transform:translateY(-50%); color:var(--ef-muted); pointer-events:none; display:flex; align-items:center; }
.ef-input-wrap .ef-icon svg { width:13px; height:13px; }
.ef-field-label { display:block; font-size:11.5px; font-weight:600; color:var(--ef-muted); letter-spacing:0.04em; text-transform:uppercase; margin-bottom:5px; }
.ef-divider { height:1px; background:var(--ef-border); margin:8px 0; }
.ef-btn-export { display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg,var(--ef-accent),var(--ef-accent2)); border:none; color:#fff; font-size:13.5px; font-weight:500; padding:11px 24px; border-radius:var(--ef-radius); cursor:pointer; box-shadow:0 4px 20px rgba(79,124,255,.30); transition:opacity .2s,transform .15s,box-shadow .2s; }
.ef-btn-export:hover { opacity:.92; transform:translateY(-1px); box-shadow:0 8px 28px rgba(79,124,255,.40); }
.ef-btn-export:active { transform:scale(.98); }
.ef-btn-export svg { width:15px; height:15px; }
.ef-btn-reset { background:none; border:none; color:var(--ef-muted); font-size:13px; cursor:pointer; padding:0; transition:color .2s; }
.ef-btn-reset:hover { color:var(--ef-text); }
.ef-alert { border-radius:var(--ef-radius); font-size:var(--ef-font-sm); }
.custom-close-button { background:transparent; border:none; font-size:1.25rem; font-weight:bold; color:inherit; cursor:pointer; padding:0; margin-left:1rem; transition:opacity .2s; }
.custom-close-button:hover { opacity:.6; }
.custom-close-button::before { content:'×'; }
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
                <span class="ef-panel-title">Report Filters</span>
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

                {{-- From Date --}}
                <div class="col-md-6">
                    <label class="ef-field-label">From Date</label>
                    <div class="ef-input-wrap">
                        <input type="date" wire:model="selectedFromDate" class="ef-control">
                        <span class="ef-icon">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8"  y1="2" x2="8"  y2="6"/>
                                <line x1="3"  y1="10" x2="21" y2="10"/>
                            </svg>
                        </span>
                    </div>
                    @error('selectedFromDate') <span class="text-danger" style="font-size:11px;">{{ $message }}</span> @enderror
                </div>

                {{-- To Date --}}
                <div class="col-md-6">
                    <label class="ef-field-label">To Date</label>
                    <div class="ef-input-wrap">
                        <input type="date" wire:model="selectedToDate" class="ef-control">
                        <span class="ef-icon">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8"  y1="2" x2="8"  y2="6"/>
                                <line x1="3"  y1="10" x2="21" y2="10"/>
                            </svg>
                        </span>
                    </div>
                    @error('selectedToDate') <span class="text-danger" style="font-size:11px;">{{ $message }}</span> @enderror
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
                <div class="col-12 d-flex align-items-center justify-content-end">
                  
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