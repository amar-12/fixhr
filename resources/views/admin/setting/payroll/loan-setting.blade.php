@extends('admin.layout.master')
@section('title', 'Loan Configuration')

<style>
    /* Simple Clean Styling */
    .settings-section {
        margin-bottom: 1rem;
        padding: 1rem;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }

    .section-header {
        font-weight: 600;
        font-size: 13px;
        color: #007bff;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-row {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
    }

    .form-row label {
        min-width: 120px;
        font-size: 13px;
        font-weight: 500;
        margin: 0;
    }

    .form-control-sm, .form-select-sm {
        font-size: 13px;
        padding: 0.375rem 0.75rem;
        border-radius: 4px;
    }

    .modal-dialog {
        max-width: 800px;
    }

    .modal-body {
        max-height: none;
        padding: 1.5rem;
    }

    .interest-rule {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.5rem 0;
        font-size: 12px;
        flex-wrap: wrap;
    }

    .interest-rule input[type="number"] {
        width: 60px;
        padding: 2px 6px;
        text-align: center;
        border-radius: 3px;
        border: 1px solid;
        font-size: 11px;
    }

    .interest-rule span {
        font-size: 12px;
    }

    /* Disable section when checkbox unchecked */
    .section-disabled {
        opacity: 0.5;
        pointer-events: none;
    }

    .checkbox-control {
        transform: scale(1.1);
        margin-right: 8px;
    }

    .btn {
        font-size: 13px;
        padding: 0.5rem 1rem;
    }

    @media (max-width: 768px) {
        .modal-dialog {
            max-width: 95vw;
            margin: 10px;
        }

        .form-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }

        .form-row label {
            min-width: auto;
        }
    }
</style>

@section('content')

{{-- Breadcrumbs Start --}}
<div class="p-0 mt-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="">Payroll</a></li>
                <li class="active"><span><b>Loan/Advance Configuration</b></span></li>
            </ol>
        </div>
        <div class="col-md-6">
            <div class="page-rightheader ms-md-auto">
                <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end justify-content-end">
                    <div class="btn-list">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#loanModal">
                          Add Configuration
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Breadcrumbs End --}}

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">
                    <i class="fas fa-university me-2"></i>Loan/Advance Configuration
                </h4>
            </div>
            <div class="card-body">
                @csrf
                <div class="table-responsive">
                    <table class="table table-striped" id="loanTable">
                        <thead>
                            <tr>
                                @foreach ($columns as $column)
                                <th>{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ADVANCE/LOAN SETTINGS MODAL -->
<div class="modal fade" id="loanModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="loanForm">
                @csrf
                 <input type="hidden" name="als_id" id="alsId">

                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="fa fa-money me-2"></i>
                        <span id="modalTitle">Add Loan/Advance Configuration</span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="fa fa-times"></i></button>
                </div>

                <div class="modal-body">
                    <!-- Loan Name -->
                    <div class="mb-3">
                        <label class="form-label">Loan/Advance Name <span class="text-danger">*</span></label>
                        <input type="text" name="loan_advance_name" id="loanName" class="form-control form-control-sm" required>
                    </div>

                    <!-- Advance Limit -->
                    <div class="settings-section">
                        <div class="section-header">Advance Limit</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-row">
                                    <input type="radio" name="limit_type" value="fixed" id="fixedRadio" class="checkbox-control">
                                    <label for="fixedRadio">Fixed Amount (₹):</label>
                                    <input type="number" name="fixed_limit" id="fixedAmount" class="form-control form-control-sm" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-row">
                                    <input type="radio" name="limit_type" value="percentage" id="percentageRadio" class="checkbox-control" checked>
                                    <label for="percentageRadio">Gross Salary (%):</label>
                                    <input type="number" name="percentage_limit" id="percentage" class="form-control form-control-sm" value="50" min="1" max="100">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Restrictions -->
                    <div class="row px-3">
                        <div class="col-lg-6 settings-section">
                            <div class="section-header">Employee Restrictions</div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-row">
                                        <input type="checkbox" id="permanentOnly" name="permanent_only" class="checkbox-control" checked>
                                        <label for="permanentOnly">Only Permanent Employees</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-row">
                                        <label>Min Employment (months):</label>
                                        <input type="number" name="min_employment" class="form-control form-control-sm" value="6" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Age Criteria -->
                        <div class="col-lg-6 py-4">
                            <div class="section-header">Age Criteria</div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-row">
                                        <input type="checkbox" id="enableAge" name="enable_age_criteria" class="checkbox-control">
                                        <label for="enableAge">Enable Age Limit</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-row" id="ageSection">
                                        <label class="form-row">Maximum Age:</label>
                                        <input type="number" name="max_age" class="form-control form-control-sm" value="60" min="18" max="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

              <!-- Interest Settings -->
                <div class="settings-section">
                    <div class="section-header">Interest Settings</div>

                    <!-- Master Switch -->
                    <div class="form-row mb-2">
                        <input type="checkbox" id="enableInterest" name="apply_interest" class="checkbox-control">
                        <label class="form-label" for="enableInterest">Apply Interest on Loan/Advance</label>
                    </div>

                    <!-- Dynamic Rules Section -->
                    <div id="interestSection" class="section-disabled">
                        <div id="interestRulesContainer"></div>

                        <!-- Add Rule Button -->
                        <button type="button" id="addInterestRule" class="btn btn-sm btn-outline-primary mt-2">
                            + Add Interest Rule
                        </button>
                    </div>
                </div>

                <!-- Template for Rule -->
                <template id="interestRuleTemplate">
                    <div class="interest-rule d-flex align-items-center gap-2 mb-2 border rounded p-2 bg-light flex-nowrap">

                        <!-- Rule Type -->
                        <select name="rule_type[]" class="form-select form-select-sm w-auto rule-type">
                            <option value="installments_exceed">If exceeds installments</option>
                            <option value="loan_multiple">If loan is × salary</option>
                            <option value="tenure_exceed">If exceeds months</option>
                            <option value="amount_exceed">If loan amount > value</option>
                            <option value="amount_range">If loan amount between range</option>
                            <option value="custom">Custom Condition</option>
                        </select>

                        <!-- Param 1 -->
                        <input type="number" name="rule_param1[]" placeholder="Value / Min Amount"
                            class="form-control form-control-sm w-auto param1">

                        <!-- Param 2 -->
                        <input type="number" name="rule_param2[]" placeholder="Months/Multiplier / Max Amount"
                            class="form-control form-control-sm w-auto param2">

                        <!-- Rate -->
                        <div class="d-flex align-items-center gap-1">
                            <span>→ Rate:</span>
                            <input type="number" name="rule_rate[]" step="0.1" value="0"
                                class="form-control form-control-sm" style="width:80px;">
                            <span>%</span>
                        </div>

                        <!-- Remove Button (same row + outline) -->
                        <button type="button" class="btn btn-sm btn-outline-danger ms-auto removeRule">×</button>
                    </div>
                </template>

                <!-- Other Settings -->
                <div class="settings-section">
                    <div class="section-header">Additional Settings</div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-row">
                                <label>Max Concurrent:</label>
                                <input type="number" name="max_concurrent" class="form-control form-control-sm" value="1" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-row">
                                <label>Min Repayment (months):</label>
                                <input type="number" name="min_repayment" class="form-control form-control-sm" value="1" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-row">
                                <label>Max Repayment (months):</label>
                                <input type="number" name="max_repayment" class="form-control form-control-sm" value="24" min="1">
                            </div>
                        </div>
                    </div>
                    <div class="form-row mt-2">
                        <input type="checkbox" id="isActive" name="status" class="checkbox-control" checked>
                        <label for="isActive">Active Configuration</label>
                    </div>
                </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-outline-primary" id="saveBtn">
                        <i class="fas fa-save me-1"></i>Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    let table = $('#loanTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('admin/settings/payroll/loan-configuration') }}",
            type: 'GET'
        },
        columns: [
            { data: 0, name: 'rownum' },
            { data: 1, name: 'loan_name' },
            { data: 2, name: 'wef', orderable: false, searchable: false },
            { data: 3, name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        pageLength: 25,
        language: {
            processing: "Loading...",
            emptyTable: "No configurations found"
        }
    });

    // Reset form when modal opens/closes
    $('#loanModal').on('show.bs.modal', function() {
        if (!$('#alsId').val()) {
            resetForm();
        }
    }).on('hidden.bs.modal', function() {
        resetForm();
    });

    function resetForm() {
        $('#loanForm')[0].reset();
        $('#alsId').val('');
        $('#modalTitle').text('Add Loan/Advance Configuration');
        $('#percentageRadio').prop('checked', true).trigger('change');
        $('#permanentOnly').prop('checked', true);
        $('#isActive').prop('checked', true);
        $('#enableInterest').prop('checked', false).trigger('change');
        $('#enableAge').prop('checked', false).trigger('change');

        // Clear interest rules container
        $('#interestRulesContainer').empty();
    }

    // Limit type change handler
    $('input[name="limit_type"]').on('change', function() {
        $('#fixedAmount, #percentage').prop('disabled', true);
        if ($(this).val() === 'fixed') {
            $('#fixedAmount').prop('disabled', false).focus();
        } else {
            $('#percentage').prop('disabled', false).focus();
        }
    });

    // Enable/Disable sections based on checkboxes
    $('#enableAge').on('change', function() {
        $('#ageSection').toggleClass('section-disabled', !$(this).is(':checked'));
    });

    $('#enableInterest').on('change', function() {
        $('#interestSection').toggleClass('section-disabled', !$(this).is(':checked'));
    });

    // Function to add interest rule dynamically
  function addInterestRule(rule = null) {
    const template = document.getElementById("interestRuleTemplate").content;
    let clone = document.importNode(template, true);

    if (rule) {
        console.log('Setting rule data:', rule); // Debug

        // Set rule type
        const ruleTypeSelect = clone.querySelector('.rule-type');
        if (ruleTypeSelect && rule.type) {
            ruleTypeSelect.value = rule.type;
        }

        // Set parameters
        const param1Input = clone.querySelector('.param1');
        if (param1Input && rule.param1 !== null && rule.param1 !== undefined) {
            param1Input.value = rule.param1;
        }

        const param2Input = clone.querySelector('.param2');
        if (param2Input && rule.param2 !== null && rule.param2 !== undefined) {
            param2Input.value = rule.param2;
        }

        // Set rate
        const rateInput = clone.querySelector('input[name="rule_rate[]"]');
        if (rateInput && rule.rate !== null && rule.rate !== undefined) {
            rateInput.value = rule.rate;
        }

        // Update placeholders based on rule type
        setTimeout(() => {
            updateRulePlaceholders(ruleTypeSelect);
        }, 100);
    }

    document.getElementById("interestRulesContainer").appendChild(clone);
}

  // Edit configuration function - WITH INTEREST RULES SUPPORT
    window.editLoan = function(alsId) {
        const editUrl = '{{ url("admin/settings/payroll/loan-configuration") }}' + '/' + alsId + '/edit';
        console.log('Edit URL:', editUrl);

        $.ajax({
            url: editUrl,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    console.log('Edit data received:', data); // Debug log

                    $('#alsId').val(data.als_id);
                    $('#loanName').val(data.als_loan_advance_name);
                    $('#modalTitle').text('Edit Loan/Advance Configuration');

                    // Set limit type with exact database field names and values
                    console.log('Limit type from DB:', data.als_limit_type); // Debug

                    if (data.als_limit_type === 'FIXED' || data.als_limit_type === 'fixed') {
                        $('#fixedRadio').prop('checked', true).trigger('change');
                        $('#fixedAmount').val(data.als_fixed_limit || '');
                        console.log('Setting fixed amount:', data.als_fixed_limit); // Debug
                    } else {
                        $('#percentageRadio').prop('checked', true).trigger('change');
                        $('#percentage').val(data.als_percentage_limit || 50);
                        console.log('Setting percentage:', data.als_percentage_limit); // Debug
                    }

                    // Set other fields
                    $('#permanentOnly').prop('checked', data.als_permanent_only == 1);
                    $('input[name="min_employment"]').val(data.als_min_employment || 6);

                    $('#enableAge').prop('checked', data.als_enable_age_criteria == 1).trigger('change');
                    $('input[name="max_age"]').val(data.als_max_age || 60);

                    $('#enableInterest').prop('checked', data.als_apply_interest == 1).trigger('change');

                    // ✅ HANDLE INTEREST RULES FROM SEPARATE TABLE
                    $('#interestRulesContainer').empty();
                    console.log('Interest rules received:', data.interest_rules); // Debug

                    if (data.interest_rules && Array.isArray(data.interest_rules) && data.interest_rules.length > 0) {
                        data.interest_rules.forEach(function(rule) {
                            console.log('Adding rule:', rule); // Debug
                            addInterestRule(rule);
                        });
                    }

                    // Additional settings
                    $('input[name="max_concurrent"]').val(data.als_max_concurrent || 1);
                    $('input[name="min_repayment"]').val(data.als_min_repayment || 1);
                    $('input[name="max_repayment"]').val(data.als_max_repayment || 24);
                    $('#isActive').prop('checked', data.als_status == 1);

                    $('#loanModal').modal('show');
                } else {
                    Swal.fire('Error', response.message || 'Failed to load configuration', 'error');
                }
            },
            error: function(xhr) {
                let message = 'Failed to load configuration data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire('Error', message, 'error');
                console.log('Edit Error:', xhr);
                console.log('Edit URL used:', editUrl);
            }
        });
    };

    // ✅ Function to update placeholders based on rule type
    function updateRulePlaceholders(selectElement) {
        let row = selectElement.closest(".interest-rule");
        let param1 = row.querySelector(".param1");
        let param2 = row.querySelector(".param2");

        switch(selectElement.value) {
            case "amount_range":
                param1.placeholder = "Min Amount";
                param2.placeholder = "Max Amount";
                break;
            case "loan_multiple":
                param1.placeholder = "Multiplier Value";
                param2.placeholder = "—";
                break;
            case "tenure_exceed":
                param1.placeholder = "Months";
                param2.placeholder = "—";
                break;
            case "installments_exceed":
                param1.placeholder = "Installments";
                param2.placeholder = "—";
                break;
            case "amount_exceed":
                param1.placeholder = "Loan Amount >";
                param2.placeholder = "—";
                break;
            default:
                param1.placeholder = "Value";
                param2.placeholder = "Months/Multiplier";
        }
    }


    // Edit configuration click handler
    $(document).on('click', '.edit-loan', function() {
        const alsId = $(this).data('als-id');

        if (alsId) {
            editLoan(alsId);
        } else {
            Swal.fire('Error', 'Configuration ID not found', 'error');
        }
    });

    // Form submission - SIMPLIFIED
    $('#loanForm').on('submit', function(e) {
        e.preventDefault();

        const loanName = $('#loanName').val().trim();
        if (!loanName) {
            Swal.fire('Error', 'Please enter loan/advance name', 'error');
            return;
        }

        if ($('#fixedRadio').is(':checked') && !$('#fixedAmount').val()) {
            Swal.fire('Error', 'Please enter fixed amount', 'error');
            return;
        }

        const submitBtn = $('#saveBtn');
        const isEdit = $('#alsId').val() !== '';
        const alsId = $('#alsId').val();

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');

        const formData = new FormData(this);

         formData.set('_token', $('meta[name="csrf-token"]').attr('content'));

        // Convert checkboxes to 1/0
        const checkboxes = ['permanent_only', 'enable_age_criteria', 'apply_interest', 'status'];
        checkboxes.forEach(name => {
            const checkbox = $(`input[name="${name}"]`);
            if (checkbox.length) {
                formData.set(name, checkbox.is(':checked') ? '1' : '0');
            }
        });

        // Set URL and method
        let url = '{{ url("admin/settings/payroll/loan-configuration") }}';
        let method = isEdit ? 'POST' : 'POST';

        // If editing, use update route
        if (isEdit) {
            url = '{{ url("admin/settings/payroll/loan-configuration") }}' + '/' + alsId;
            formData.append('_method', 'PUT');
        }

        console.log('Form submission URL:', url);
        console.log('Is Edit:', isEdit);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Configuration saved successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#loanModal').modal('hide');
                    table.ajax.reload();
                } else {
                    Swal.fire('Error', response.message || 'Something went wrong', 'error');
                }
            },
            error: function(xhr) {
                let message = 'Something went wrong';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        message = Object.values(errors).flat().join('\n');
                    }
                }
                Swal.fire('Error', message, 'error');
                console.log('Form submission error:', xhr);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Save');
            }
        });
    });

    // Delete configuration - SIMPLIFIED
    $(document).on('click', '.delete-loan', function() {
        const alsId = $(this).data('als-id');
        const name = $(this).data('name');

        if (!alsId) {
            Swal.fire('Error', 'Configuration ID not found', 'error');
            return;
        }

        Swal.fire({
            title: 'Delete Configuration?',
            text: `Are you sure you want to delete "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const deleteUrl = '{{ url("admin/settings/payroll/loan-configuration") }}' + '/' + alsId;
                console.log('Delete URL:', deleteUrl);

                $.ajax({
                    url: deleteUrl,
                    type: 'POST', // Use POST with _method override
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val(),
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message || 'Configuration deleted successfully',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            table.ajax.reload();
                        } else {
                            Swal.fire('Error', response.message || 'Failed to delete configuration', 'error');
                        }
                    },
                    error: function(xhr) {
                        let message = 'Failed to delete configuration';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', message, 'error');
                        console.log('Delete error:', xhr);
                    }
                });
            }
        });
    });

    // Initialize form state on page load
    $('#percentageRadio').trigger('change');
    $('#enableAge').trigger('change');
    $('#enableInterest').trigger('change');
});

// Interest rules management
document.addEventListener("DOMContentLoaded", function () {
    const container = document.getElementById("interestRulesContainer");
    const addBtn = document.getElementById("addInterestRule");
    const template = document.getElementById("interestRuleTemplate").content;

    if (addBtn) {
        addBtn.addEventListener("click", function () {
            let clone = document.importNode(template, true);
            container.appendChild(clone);
        });
    }

    if (container) {
        container.addEventListener("click", function (e) {
            if (e.target.classList.contains("removeRule")) {
                e.target.closest(".interest-rule").remove();
            }
        });

        // Dynamic placeholder updates
        container.addEventListener("change", function (e) {
            if (e.target.classList.contains("rule-type")) {
                let row = e.target.closest(".interest-rule");
                let param1 = row.querySelector(".param1");
                let param2 = row.querySelector(".param2");

                switch(e.target.value) {
                    case "amount_range":
                        param1.placeholder = "Min Amount";
                        param2.placeholder = "Max Amount";
                        break;
                    case "loan_multiple":
                        param1.placeholder = "Multiplier Value";
                        param2.placeholder = "—";
                        break;
                    case "tenure_exceed":
                        param1.placeholder = "Months";
                        param2.placeholder = "—";
                        break;
                    case "installments_exceed":
                        param1.placeholder = "Installments";
                        param2.placeholder = "—";
                        break;
                    case "amount_exceed":
                        param1.placeholder = "Loan Amount >";
                        param2.placeholder = "—";
                        break;
                    default:
                        param1.placeholder = "Value";
                        param2.placeholder = "Months/Multiplier";
                }
            }
        });
    }
});
</script>

@endsection
