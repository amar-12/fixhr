<script>
    const FORM_TABS = @json($formTabs);
    const ALL_EMPLOYEES = @json($employees ?? []);
    const FINANCIAL_YEARS_META = @json($financialYearsMeta ?? []);
    const SERVER_TODAY = @json($todayDate ?? now()->toDateString());
    const FORM16A_AVAILABILITY_URL = '{{ route("form16a.availability") }}';
    const FORM_RELOAD_BASE_URL = @json($formReloadUrl ?? route('tax.form-generation.index'));

    const QUARTER_OPTIONS = [
        { value: 'ALL', label: 'Full FY (All quarters)' },
        { value: 'Q1', label: 'Q1 (Apr–Jun)', index: 1 },
        { value: 'Q2', label: 'Q2 (Jul–Sep)', index: 2 },
        { value: 'Q3', label: 'Q3 (Oct–Dec)', index: 3 },
        { value: 'Q4', label: 'Q4 (Jan–Mar)', index: 4 },
    ];

    const FORM_ROUTE_MAP = {
        form16a: '{{ route("generate.form16a") }}',
        form16: '{{ route("generate.form16") }}',
        form15g: '{{ route("generate.form15g") }}',
    };

    const FORM_LABEL_MAP = {
        form16a: 'Form 16A',
        form16: 'Form 16',
        form15g: 'Form 15G',
    };

    let activeFormKey = '{{ $activeForm }}';
    let currentEmployee = null;
    let showExtraFilters = false;
    const STANDALONE_FORM = @json(!empty($standaloneForm));

    document.addEventListener('DOMContentLoaded', function() {
        if (!STANDALONE_FORM) {
            document.querySelectorAll('.fg-form-tab').forEach((tab) => {
                tab.addEventListener('click', () => switchFormTab(tab.dataset.form));
            });
        }

        document.getElementById('employeeSearch')?.addEventListener('input', renderEmployeeSearchResults);
        document.getElementById('employeeSearch')?.addEventListener('focus', renderEmployeeSearchResults);
        document.getElementById('employeeStatusFilter')?.addEventListener('change', () => {
            renderEmployeeSearchResults();
            clearSelectedEmployee();
        });
        document.getElementById('departmentFilter')?.addEventListener('change', () => {
            renderEmployeeSearchResults();
            clearSelectedEmployee();
        });
        document.getElementById('financialYearFilter')?.addEventListener('change', reloadWithFilters);
        document.getElementById('downloadFormBtn')?.addEventListener('click', downloadActiveForm);
        document.getElementById('clearAllFiltersBtn')?.addEventListener('click', resetAllFilters);
        document.getElementById('quickClearFiltersBtn')?.addEventListener('click', resetAllFilters);
        document.getElementById('toggleExtraFiltersBtn')?.addEventListener('click', toggleExtraFilters);

        document.addEventListener('click', (event) => {
            const wrap = document.getElementById('employeeSearchWrap');
            if (wrap && !wrap.contains(event.target)) {
                hideEmployeeSearchResults();
            }
        });

        updateFormTabUI();
        updateQuarterOptions();
    });

    function parseDateOnly(dateStr) {
        if (!dateStr) return null;
        const parts = String(dateStr).substring(0, 10).split('-').map(Number);
        if (parts.length !== 3 || parts.some((n) => Number.isNaN(n))) return null;
        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    function todayDateOnly() {
        return parseDateOnly(SERVER_TODAY);
    }

    function addMonthsToDate(date, months) {
        const d = new Date(date.getTime());
        d.setMonth(d.getMonth() + months);
        return d;
    }

    function getSelectedFYMeta() {
        const fyId = document.getElementById('financialYearFilter')?.value;
        return FINANCIAL_YEARS_META.find((fy) => String(fy.id) === String(fyId)) || null;
    }

    function getQuarterStartDate(fyStart, quarterIndex) {
        return addMonthsToDate(fyStart, (quarterIndex - 1) * 3);
    }

    function isQuarterSelectable(fyMeta, quarterValue) {
        const today = todayDateOnly();
        if (!today) return true;

        const fyStart = parseDateOnly(fyMeta?.start);
        const fyEnd = parseDateOnly(fyMeta?.end);

        if (!fyStart) {
            return true;
        }

        if (quarterValue === 'ALL') {
            if (!fyEnd) {
                return false;
            }
            return today.getTime() > fyEnd.getTime();
        }

        const quarterDef = QUARTER_OPTIONS.find((q) => q.value === quarterValue);
        if (!quarterDef?.index) {
            return false;
        }

        const quarterStart = getQuarterStartDate(fyStart, quarterDef.index);
        return today.getTime() >= quarterStart.getTime();
    }

    function updateQuarterOptions() {
        const select = document.getElementById('quarterFilter');
        if (!select || activeFormKey !== 'form16a') {
            return;
        }

        const fyMeta = getSelectedFYMeta();
        const previousValue = select.value;
        let firstEnabledValue = null;

        select.innerHTML = QUARTER_OPTIONS.map((option) => {
            const enabled = isQuarterSelectable(fyMeta, option.value);
            if (enabled && firstEnabledValue === null) {
                firstEnabledValue = option.value;
            }

            const suffix = enabled ? '' : ' — not started';
            return `<option value="${option.value}" ${enabled ? '' : 'disabled'}>${option.label}${suffix}</option>`;
        }).join('');

        if (previousValue && isQuarterSelectable(fyMeta, previousValue)) {
            select.value = previousValue;
        } else if (firstEnabledValue) {
            select.value = firstEnabledValue;
        }
    }

    function switchFormTab(formKey) {
        if (!FORM_TABS[formKey]) return;
        activeFormKey = formKey;

        document.querySelectorAll('.fg-form-tab').forEach((tab) => {
            tab.classList.toggle('active', tab.dataset.form === formKey);
        });

        const url = new URL(window.location.href);
        url.searchParams.set('form', formKey);
        window.history.replaceState({}, '', url.toString());

        updateFormTabUI();
    }

    function updateFormTabUI() {
        const tab = FORM_TABS[activeFormKey] || {};
        document.getElementById('filterPanelTitle').textContent = `${tab.label || 'Form'} Filters`;
        document.getElementById('activeFormDescription').textContent = tab.description || '';
        document.getElementById('downloadFormBtnText').textContent = `Download ${tab.label || 'Form'}`;

        const isForm16A = activeFormKey === 'form16a';
        document.getElementById('quarterFieldWrap').style.display = isForm16A ? '' : 'none';
        document.getElementById('departmentFieldWrap').style.display = (!isForm16A || showExtraFilters) ? '' : 'none';

        if (isForm16A) {
            updateQuarterOptions();
        }
    }

    function toggleExtraFilters() {
        showExtraFilters = !showExtraFilters;
        const icon = document.getElementById('toggleExtraFiltersIcon');
        if (icon) {
            icon.style.transform = showExtraFilters ? 'rotate(180deg)' : '';
        }
        document.getElementById('filterFieldsGrid')?.classList.toggle('ef-grid-shrink', showExtraFilters);
        updateFormTabUI();
    }

    function getFilteredEmployees() {
        const status = document.getElementById('employeeStatusFilter')?.value || '';
        const departmentId = document.getElementById('departmentFilter')?.value || '';
        const search = (document.getElementById('employeeSearch')?.value || '').toLowerCase().trim();

        return ALL_EMPLOYEES.filter((emp) => {
            if (status && String(emp.emp_status) !== String(status)) return false;
            if (departmentId && String(emp.department_id) !== String(departmentId)) return false;
            if (search) {
                const haystack = `${emp.name} ${emp.code} ${emp.department} ${emp.pan}`.toLowerCase();
                if (!haystack.includes(search)) return false;
            }
            return true;
        });
    }

    function renderEmployeeSearchResults() {
        const list = document.getElementById('employeeSearchResults');
        if (!list) return;

        const results = getFilteredEmployees().slice(0, 20);

        if (!results.length) {
            list.innerHTML = '<li class="ef-no-result">No results found</li>';
            list.style.display = 'block';
            return;
        }

        list.innerHTML = results.map((emp) => `
            <li data-employee-id="${emp.id}">
                ${emp.name} (${emp.code})
            </li>
        `).join('');

        list.querySelectorAll('li[data-employee-id]').forEach((item) => {
            item.addEventListener('click', () => {
                const emp = ALL_EMPLOYEES.find((e) => String(e.id) === String(item.dataset.employeeId));
                if (emp) selectEmployee(emp);
            });
        });

        list.style.display = 'block';
    }

    function hideEmployeeSearchResults() {
        const list = document.getElementById('employeeSearchResults');
        if (list) list.style.display = 'none';
    }

    function selectEmployee(employee) {
        currentEmployee = employee;
        const searchInput = document.getElementById('employeeSearch');
        if (searchInput) {
            searchInput.value = `${employee.name} (${employee.code})`;
        }

        const badgeWrap = document.getElementById('selectedEmployeeBadge');
        if (badgeWrap) {
            badgeWrap.innerHTML = `
                <span class="ef-badge">
                    ${employee.name}
                    <button type="button" class="ef-badge-remove" id="removeSelectedEmployee" title="Remove">&times;</button>
                </span>
            `;
            document.getElementById('removeSelectedEmployee')?.addEventListener('click', clearSelectedEmployee);
        }

        hideEmployeeSearchResults();
    }

    function clearSelectedEmployee() {
        currentEmployee = null;
        const searchInput = document.getElementById('employeeSearch');
        if (searchInput) searchInput.value = '';
        const badgeWrap = document.getElementById('selectedEmployeeBadge');
        if (badgeWrap) badgeWrap.innerHTML = '';
    }

    function reloadWithFilters() {
        const url = new URL(FORM_RELOAD_BASE_URL, window.location.origin);
        url.searchParams.set('form', activeFormKey);
        url.searchParams.set('financial_year_id', document.getElementById('financialYearFilter')?.value || '');
        window.location.href = url.toString();
    }

    function resetAllFilters() {
        document.getElementById('employeeStatusFilter').value = '71';
        document.getElementById('departmentFilter').value = '';
        clearSelectedEmployee();
        hideEmployeeSearchResults();
        updateQuarterOptions();
    }

    function downloadActiveForm() {
        if (!currentEmployee) {
            Swal.fire({
                icon: 'warning',
                title: 'Employee Required',
                text: 'Please search and select an employee first.',
            });
            document.getElementById('employeeSearch')?.focus();
            return;
        }

        const formLabel = FORM_LABEL_MAP[activeFormKey] || 'Form';
        const fyId = document.getElementById('financialYearFilter')?.value;

        if (!fyId) {
            Swal.fire({ icon: 'warning', title: 'Financial Year Required', text: 'Please select a financial year.' });
            return;
        }

        if (activeFormKey === 'form16a') {
            downloadForm16A(formLabel, fyId);
            return;
        }

        const actionUrl = FORM_ROUTE_MAP[activeFormKey];
        if (!actionUrl) {
            Swal.fire({ icon: 'error', title: 'Invalid form', text: 'Please select a valid form tab.' });
            return;
        }

        submitPdfForm(actionUrl, {
            employee_id: currentEmployee.id,
            financial_year_id: fyId,
            form_type: formLabel,
        }, formLabel);
    }

    async function downloadForm16A(formLabel, fyId) {
        const quarter = document.getElementById('quarterFilter')?.value || 'ALL';
        const fyMeta = getSelectedFYMeta();

        if (!isQuarterSelectable(fyMeta, quarter)) {
            Swal.fire({
                icon: 'warning',
                title: 'Quarter not available',
                text: 'Selected quarter has not started yet for this financial year.',
            });
            return;
        }

        try {
            const params = new URLSearchParams({
                employee_id: currentEmployee.id,
                financial_year_id: fyId,
                quarter: quarter,
            });
            const res = await fetch(`${FORM16A_AVAILABILITY_URL}?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.exists) {
                Swal.fire({
                    icon: 'error',
                    title: 'Cannot Generate',
                    text: data.message || 'Salary or challan data is not available for selected period.',
                });
                return;
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Verification Failed',
                text: 'Unable to verify salary data. Please try again.',
            });
            return;
        }

        submitPdfForm(FORM_ROUTE_MAP.form16a, {
            employee_id: currentEmployee.id,
            financial_year_id: fyId,
            form_type: formLabel,
            ...(quarter && quarter !== 'ALL' ? { quarter } : {}),
        }, formLabel);
    }

    function submitPdfForm(actionUrl, payload, formTypeLabel) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        form.target = '_blank';
        form.action = actionUrl;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        Object.entries(payload || {}).forEach(([key, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value ?? '';
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        Swal.fire({
            icon: 'success',
            title: 'Downloading',
            text: `${formTypeLabel} is being generated for ${currentEmployee.name}.`,
            timer: 1500,
            showConfirmButton: false,
        });
    }
</script>
