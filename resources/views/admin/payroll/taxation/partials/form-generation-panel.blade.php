<div class="row fg-layout ef-wrapper justify-content-center">
    <div class="col-xl-11 col-12">
        <div class="row g-4 align-items-start">
            @if(empty($standaloneForm))
                <div class="col-lg-3 col-md-4">
                    <nav class="fg-vertical-nav" aria-label="Tax forms">
                        <div class="fg-vertical-nav-title">Tax Forms</div>
                        <ul class="fg-vertical-nav-list" id="formTabs">
                            @foreach($formTabs as $tabKey => $tab)
                                <li>
                                    <button type="button"
                                        class="fg-form-tab {{ $activeForm === $tabKey ? 'active' : '' }}"
                                        data-form="{{ $tabKey }}">
                                        <span>
                                            {{ $tab['label'] }}
                                            <small class="fg-form-tab-desc">{{ $tab['description'] ?? '' }}</small>
                                        </span>
                                        <i class="fa fa-angle-right fg-tab-arrow" aria-hidden="true"></i>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                </div>
            @endif

            <div class="{{ empty($standaloneForm) ? 'col-lg-9 col-md-8' : 'col-12' }} fg-content-panel">
        <div class="ef-panel">
            <div class="ef-panel-header">
                <span class="ef-panel-title" id="filterPanelTitle">{{ ($formTabs[$activeForm]['label'] ?? 'Form') }} Filters</span>
                <div class="fg-filter-actions">
                    <button type="button" class="ef-filter-toggle-btn" id="toggleExtraFiltersBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15 19.88c.04.3-.06.62-.29.83a.996.996 0 0 1-1.41 0L9.29 16.7a.99.99 0 0 1-.29-.83v-5.12L4.21 4.62a1 1 0 0 1 .17-1.4c.19-.14.4-.22.62-.22h14c.22 0 .43.08.62.22a1 1 0 0 1 .17 1.4L15 10.75z" />
                        </svg>
                        Filters
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" id="toggleExtraFiltersIcon">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <button type="button" class="ef-filter-toggle-btn" id="quickClearFiltersBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15 19.88c.04.3-.06.62-.29.83a.996.996 0 0 1-1.41 0L9.29 16.7a.99.99 0 0 1-.29-.83v-5.12L4.21 4.62a1 1 0 0 1 .17-1.4c.19-.14.4-.22.62-.22h14c.22 0 .43.08.62.22a1 1 0 0 1 .17 1.4L15 10.75z" />
                        </svg>
                        Clear Filters
                    </button>
                </div>
            </div>

            <div class="row gx-3 gy-3 ef-fields-grid" id="filterFieldsGrid">
                <div class="col-md-6">
                    <label class="ef-field-label">Employee Status</label>
                    <div class="ef-input-wrap">
                        <select id="employeeStatusFilter" class="ef-control">
                            <option value="">All</option>
                            <option value="71" selected>Active</option>
                            <option value="72">Inactive</option>
                        </select>
                        <span class="ef-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="ef-field-label">Employee / Code</label>
                    <div class="ef-input-wrap" id="employeeSearchWrap">
                        <input type="text" id="employeeSearch" class="ef-control" placeholder="Search..." autocomplete="off" style="padding-right: 34px;">
                        <span class="ef-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                        </span>
                        <ul class="ef-results" id="employeeSearchResults" style="display: none;"></ul>
                    </div>
                    <div class="fg-selected-badge-wrap" id="selectedEmployeeBadge"></div>
                </div>

                <div class="col-md-6">
                    <label class="ef-field-label">Financial Year</label>
                    <div class="ef-input-wrap">
                        <select id="financialYearFilter" class="ef-control">
                            @foreach(($financialYears ?? []) as $year)
                                <option value="{{ $year->fy_id }}" {{ (int) ($selectedFYId ?? 0) === (int) $year->fy_id ? 'selected' : '' }}>
                                    {{ $year->fy_year }}
                                </option>
                            @endforeach
                        </select>
                        <span class="ef-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="col-md-6" id="quarterFieldWrap">
                    <label class="ef-field-label">Quarter</label>
                    <div class="ef-input-wrap">
                        <select id="quarterFilter" class="ef-control">
                            <option value="ALL">Full FY (All quarters)</option>
                            <option value="Q1">Q1 (Apr–Jun)</option>
                            <option value="Q2">Q2 (Jul–Sep)</option>
                            <option value="Q3">Q3 (Oct–Dec)</option>
                            <option value="Q4">Q4 (Jan–Mar)</option>
                        </select>
                        <span class="ef-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="col-md-6 extra-filter-field" id="departmentFieldWrap" style="display: none;">
                    <label class="ef-field-label">Department</label>
                    <div class="ef-input-wrap">
                        <select id="departmentFilter" class="ef-control">
                            <option value="">All Departments</option>
                            @foreach(($departments ?? []) as $department)
                                <option value="{{ $department->d_id }}">{{ $department->d_name }}</option>
                            @endforeach
                        </select>
                        <span class="ef-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="col-12">
                    <div class="ef-divider"></div>
                </div>

                <div class="col-12 d-flex align-items-center justify-content-between">
                    <button type="button" class="ef-btn-reset" id="clearAllFiltersBtn">Clear all filters</button>
                    <button type="button" class="ef-btn-export" id="downloadFormBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                        <span id="downloadFormBtnText">Download Form</span>
                    </button>
                </div>
            </div>
        </div>

        @if(!empty($standaloneForm))
            <p class="text-muted mt-3 mb-0" style="font-size: 0.8rem;" id="activeFormDescription">
                {{ $formTabs[$activeForm]['description'] ?? '' }}
            </p>
        @else
            <p class="text-muted mt-3 mb-0 d-none" style="font-size: 0.8rem;" id="activeFormDescription" aria-hidden="true"></p>
        @endif
            </div>
        </div>
    </div>
</div>
