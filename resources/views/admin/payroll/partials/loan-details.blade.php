<div class="card shadow-sm border-0 mb-4">
    <div
        class="card-header d-flex justify-content-between align-items-center bg-light text-dark dark:bg-dark dark:text-white">
        <div>
            <strong>Loan Application</strong>
            <span class="badge bg-warning text-dark ms-2">Pending Approval</span>
        </div>
        <span>Ref #{{ $loan->lnr_id }}</span>
    </div>

    <div class="card-body bg-white text-dark dark:bg-gray-900 dark:text-white">
        <!-- Employee and Business Info -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-3">
                        <img src="{{ $loan->fh_employee->profile_image ?? 'https://ui-avatars.com/api/?name=' . urlencode($loan->fh_employee->emp_full_name ?? '') }}"
                            class="rounded-circle" width="40" height="40" alt="Employee">
                    </div>
                    <div>
                        <strong>Employee:</strong> {{ $loan->fh_employee->emp_full_name ?? '-' }}<br>
                        <small class="text-muted">Emp Code: {{ $loan->fh_employee->emp_code ?? '-' }}</small><br>
                        <small class="text-muted">Current Salary:
                            ₹{{ number_format($loan->fh_employee_salary->es_monthly_gross ?? 0, 2) }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex flex-column align-items-end">
                    <span class="badge bg-info mb-2">{{ $loan->fh_business->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Loan Details Card -->
        <div class="card border-primary mb-4">
            <div class="card-header bg-primary text-white">
                <strong>Loan Details</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <small class="text-muted">Type</small>
                            <h5>{{ $loan->fh_master_type->m_name ?? '-' }}</h5>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <small class="text-muted">Loan Amount</small>
                            <h5>₹{{ number_format($loan->lnr_requested_amount, 2) }}</h5>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <small class="text-muted">Monthly Installment</small>
                            <h5>₹<span id="installmentAmount">{{ number_format($loan->lnr_installment_amount, 2)
                                    }}</span>
                            </h5>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <small class="text-muted">Tenure</small>
                            <h5>{{ $loan->lnr_installments }} months</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="lnr_b_id" id="lnr_b_id" value="{{ $loan->lnr_b_id }}">
        @php
        $monthlySalary = $loan->fh_employee_salary->es_monthly_gross ?? 0;
        @endphp
        @if($monthlySalary < $loan->lnr_requested_amount)
            <div class="d-flex align-items-center text-warning small mb-2">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>
                    <strong>Note:</strong> Monthly salary is less than loan amount. Please enter interest rate.
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="rate" class="form-label small">Interest Rate (%)</label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="0.01" name="rate" id="rate" class="form-control" required
                            value="{{ old('rate', $loan->rate) }}">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            @endif


            <!-- Repayment Schedule -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Repayment Schedule</h5>
                <small class="text-muted">Installment Start Date:
                    {{ \Carbon\Carbon::parse($loan->lnr_start_date)->format('d M Y') }}</small>
            </div>


            <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                <table class="table table-bordered table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Payroll Month</th>
                            <th>Due Date</th>
                            <th>Installment Amount</th>
                            <th>Status</th>
                            <th>Remaining Balance</th>
                        </tr>
                    </thead>
                    <tbody id="loanSchedule">
                        @php
                        $balance = $loan->lnr_requested_amount;
                        $startDate = \Carbon\Carbon::parse($loan->lnr_start_date);
                        @endphp

                        @for($i = 0; $i < $loan->lnr_installments; $i++)
                            @php
                            $dueDate = $startDate->copy()->addMonths($i)->startOfMonth();
                            $balance -= $loan->lnr_installment_amount;
                            @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $dueDate->format('M Y') }}</td>
                                <td>{{ $dueDate->format('d-M-Y') }}</td>
                                <td>
                                    ₹
                                    <input type="number" step="0.01"
                                        class="form-control form-control-sm installmentAmountValue"
                                        value="{{ number_format($loan->lnr_installment_amount, 2, '.', '') }}"
                                        style="width: 100px; display: inline-block;">
                                </td>
                                <td>
                                    <span class="badge bg-secondary">Pending</span>
                                </td>
                                <td>₹<span class="remainingBalance">{{ number_format(max($balance, 0), 2) }}</span></td>
                            </tr>
                            @endfor
                    </tbody>
                </table>
            </div>


    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Confirm Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                Are you sure you want to approve this loan application?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-primary" id="confirmAction">Confirm</button>
            </div>
        </div>
    </div>
</div>


<script>

    $(document).on('input', '.installmentAmountValue', function () {
        let totalLoan = {{ $loan->lnr_requested_amount }};
        let runningBalance = totalLoan;

        $('.installmentAmountValue').each(function () {
            const $row = $(this).closest('tr');
            const amount = parseFloat($(this).val()) || 0;

            runningBalance -= amount;
            $row.find('.remainingBalance').text(runningBalance.toFixed(2));

            // Update subsequent rows
            $row.nextAll().each(function () {
                const nextAmount = parseFloat($(this).find('.installmentAmountValue').val()) || 0;
                runningBalance -= nextAmount;
                $(this).find('.remainingBalance').text(runningBalance.toFixed(2));
            });

            return false; // Exit loop after current row
        });
    });


    // Recalculate the remaining balance for each row
    function recalculateBalances() {
        const rows = document.querySelectorAll('#loanSchedule tr');
        let balance = parseFloat('{{ $loan->lnr_requested_amount }}');
        let totalPaid = 0;

        rows.forEach((row, index) => {
            const input = row.querySelector('.installmentAmountValue');
            const installment = parseFloat(input.value) || 0;

            totalPaid += installment;
            balance = Math.max(parseFloat('{{ $loan->lnr_requested_amount }}') - totalPaid, 0);

            // Update remaining balance cell
            const balanceCell = row.querySelector('.remainingBalance');
            if (balanceCell) {
                balanceCell.innerText = balance.toFixed(2);
            }
        });
    }

    // Attach change listener to all installment inputs
    document.querySelectorAll('.installmentAmountValue').forEach(input => {
        input.addEventListener('input', recalculateBalances);
    });

    // Function to calculate the new installment amount based on the interest rate
    function calculateInstallmentAmount() {
        const loanAmount = parseFloat('{{ $loan->lnr_requested_amount }}');
        const rate = parseFloat(document.getElementById('rate').value);
        const installments = {{ $loan->lnr_installments }};

        if (rate > 0) {
            // Calculate interest on loan amount
            const interestRate = rate / 100;
            const totalInterest = loanAmount * interestRate;
            const totalRepayment = loanAmount + totalInterest;
            const newInstallmentAmount = totalRepayment / installments;

            // Update the installment amount on the page
            document.getElementById('installmentAmount').innerText = newInstallmentAmount.toFixed(2);

            // Update the loan schedule dynamically
            const rows = document.querySelectorAll('#loanSchedule tr');
            let balance = loanAmount;
            let totalPaid = 0;

            rows.forEach((row, index) => {
                // For all but last installment, use calculated amount
                let installment = index < installments - 1
                    ? newInstallmentAmount
                    : totalRepayment - totalPaid;

                totalPaid += installment;
                balance = Math.max(loanAmount - totalPaid, 0);

                row.querySelector('.installmentAmountValue').value = installment.toFixed(2);
                row.querySelector('.remainingBalance').innerText = balance.toFixed(2);
            });
        } else {
            // Reset to original values if rate is 0
            const originalInstallment = loanAmount / installments;
            document.getElementById('installmentAmount').innerText = originalInstallment.toFixed(2);

            const rows = document.querySelectorAll('#loanSchedule tr');
            let balance = loanAmount;

            rows.forEach((row) => {
                balance = Math.max(balance - originalInstallment, 0);
                row.querySelector('.installmentAmountValue').value = originalInstallment.toFixed(2);
                row.querySelector('.remainingBalance').innerText = balance.toFixed(2);
            });
        }
    }

    // Add event listener for rate change
    document.getElementById('rate').addEventListener('input', calculateInstallmentAmount);

    // Approval/Rejection Logic
    let currentAction = '';

    // Approve button click handler
    document.getElementById('approveBtn').addEventListener('click', function () {
        currentAction = 'approve';
        document.getElementById('modalTitle').textContent = 'Confirm Loan Approval';
        document.getElementById('modalBody').textContent = 'Are you sure you want to approve this loan application?';
        document.getElementById('confirmAction').className = 'btn btn-success';
        document.getElementById('confirmAction').innerHTML = '<i class="fas fa-check-circle me-2"></i> Approve';

        const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        modal.show();
    });

    // Reject button click handler
    document.getElementById('rejectBtn').addEventListener('click', function () {
        currentAction = 'reject';
        document.getElementById('modalTitle').textContent = 'Confirm Loan Rejection';
        document.getElementById('modalBody').innerHTML = `
            <div class="mb-3">
                <p>Are you sure you want to reject this loan application?</p>
                <label for="rejectionReasonModal" class="form-label">Reason for rejection:</label>
                <select class="form-select" id="rejectionReasonModal" required>
                    <option value="">Select reason...</option>
                    <option value="salary_insufficient">Salary insufficient for loan amount</option>
                    <option value="policy_violation">Violates company loan policy</option>
                    <option value="other">Other reasons</option>
                </select>
                <div class="mt-2">
                    <label for="rejectionComments" class="form-label">Comments:</label>
                    <textarea class="form-control" id="rejectionComments" rows="2" required></textarea>
                </div>
            </div>
        `;
        document.getElementById('confirmAction').className = 'btn btn-outline-danger ';
        document.getElementById('confirmAction').innerHTML = '<i class="fas fa-times-circle me-2"></i> Reject';

        const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        modal.show();
    });

    // Confirm action (approve or reject)
    document.getElementById('confirmAction').addEventListener('click', function () {
        if (currentAction === 'approve') {
            // Submit approval form
            alert('Loan approved successfully!');
            // Here you would typically submit the form via AJAX or regular form submission
        } else if (currentAction === 'reject') {
            const reason = document.getElementById('rejectionReasonModal').value;
            const comments = document.getElementById('rejectionComments').value;

            if (!reason) {
                alert('Please select a rejection reason');
                return;
            }

            // Submit rejection with reason and comments
            alert(`Loan rejected. Reason: ${reason}\nComments: ${comments}`);
            // Here you would typically submit the form via AJAX or regular form submission
        }

        // Close the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmationModal'));
        modal.hide();
    });
</script>

<style>
    .avatar img {
        object-fit: cover;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .badge {
        font-weight: 500;
    }

    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
</style>
