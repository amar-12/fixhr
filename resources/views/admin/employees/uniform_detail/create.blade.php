@extends('admin.layout.master')
@section('title', 'Kit Details')
@section('css')
    <style>
        .form-control-sm {
            font-size: 13px;
            height: auto !important;
        }


        .form-control,
        .form-select {
            font-size: 12px;

        }

        .file-input+i,
        .bi-pencil-square {
            cursor: pointer;
        }

        .table-sm th,
        .table-sm td {
            padding: 6px;
        }
    </style>
@endsection
@section('content')
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li class="active"><span><b>Kit Details</b></span></li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row mt-5" id="uniformFormSection">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Add kit Details</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('uniform_index.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="ud_issued_by">Employee Name</label>
                                @if ($employees->count() === 1)
                                    <input type="hidden" name="ui_issued_by" value="{{ $employees[0]->emp_id }}">
                                    <input type="text" class="form-control"
                                        value="{{ $employees[0]->emp_full_name }} ({{ $employees[0]->emp_code }})" disabled>
                                @else
                                    <select name="ui_issued_by" id="ui_issued_by" class="form-control search_test" required>
                                        <option value="" disabled selected>Select Employee</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->emp_id }}">
                                                {{ $employee->emp_full_name }} ({{ $employee->emp_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                @endif

                            </div>
                        </div>

                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="table table-sm table-bordered" id="uniformTable" style="min-width: 1800px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Material</th>
                                        <th>Available Qty</th>
                                        <th>Assigned Qty</th>
                                        <th>Price/Unit</th>
                                        <th>Payable</th>
                                        <th>Discount Type</th>
                                        <th>Discount Value</th>
                                        <th>Total Price</th>
                                        <th>Issue Date</th>
                                        <th>Note</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    <!-- FIRST ROW (NOT REMOVABLE) -->
                                    <tr>
                                        <td class="serial">1</td>
                                        <td>
                                            <select name="material[]" class="form-select form-control-sm material"
                                                onchange="autoFillFields(this)" required>
                                                <option value="" disabled selected hidden>Select</option>
                                                @foreach ($uniform_type as $data)
                                                    <option value="{{ $data->id }}"
                                                        data-available="{{ $data->available_qty }}"
                                                        data-price="{{ $data->price_per_unit }}"
                                                        data-note="{{ $data->note }}"
                                                        data-payable="{{ $data->is_payable }}"
                                                        data-discount-type="{{ $data->discount_type }}"
                                                        data-discount-value="{{ $data->discount_value }}">
                                                        {{ $data->kit->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input name="available_qty[]" class="form-control form-control-sm" readonly>
                                        </td>
                                        <td><input name="assigned_qty[]" class="form-control form-control-sm assigned"
                                                oninput="calculateRow(this)"></td>
                                        <td><input name="price[]" class="form-control form-control-sm" readonly></td>
                                        <td>
                                            <select name="is_payable[]" class="form-select form-control-sm payable"
                                                onchange="togglePayable(this); calculateRow(this);">
                                                <option value="yes">Yes</option>
                                                <option value="no">No</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="discount_type[]" class="form-select form-control-sm discount_type"
                                                onchange="calculateRow(this)">
                                                <option value="">None</option>
                                                <option value="percentage">Percentage (%)</option>
                                                <option value="amount">Amount (₹)</option>
                                            </select>
                                        </td>
                                        <td><input
                                                name="discount_value[]"class="form-control form-control-sm discount_value"
                                                oninput="calculateRow(this)"></td>
                                        <td><input name="total_price[]"
                                                class="form-control form-control-sm total_price"readonly></td>
                                        <td><input type="date" name="issue_date[]" class="form-control  issue_date"></td>

                                        <td><input name="note[]" class="form-control form-control-sm note"></td>

                                        <td>
                                            <span><button type="button" class="btn btn-sm btn-primary"
                                                    onclick="addNewRow()"> + </button> </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>

                        <div class="row justify-content-end mt-4">
                            <div class="col-md-1">
                                <label>Total</label>
                                <input type="number" name="total" id="total" class="form-control form-control-sm"
                                    readonly>
                            </div>
                            <div class="col-md-1">
                                <label>Payable</label>
                                <input type="number" name="total_payable" id="payable"
                                    class="form-control form-control-sm" readonly>
                            </div>
                            <div class="col-md-1">
                                <label>Waived Off</label>
                                <input type="number" name="total_waived" id="waived"
                                    class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection


@section('script')

    <script>
        // Auto-fill fields when material is selected
        function autoFillFields(select) {
            const row = select.closest('tr');

            const selectedOption = select.options[select.selectedIndex];

            row.querySelector('input[name="available_qty[]"]').value = selectedOption.dataset.available || '';
            row.querySelector('input[name="price[]"]').value = selectedOption.dataset.price || '';
            row.querySelector('input[name="note[]"]').value = selectedOption.dataset.note || '';
            row.querySelector('select[name="is_payable[]"]').value = selectedOption.dataset.payable || 'yes';
            row.querySelector('select[name="discount_type[]"]').value = selectedOption.dataset.discountType || '';
            row.querySelector('input[name="discount_value[]"]').value = selectedOption.dataset.discountValue || '';

            calculateRow(row.querySelector('.assigned'));
        }
        function togglePayable(select) {
        }

        const form = document.querySelector('form[action="{{ route('uniform_index.store') }}"]');
        const submitBtn = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', function(e) {
            submitBtn.disabled = true;
            submitBtn.innerText = "Submitting...";
        });

        window.addEventListener('DOMContentLoaded', () => {
            const firstRowMaterial = document.querySelector('#uniformTable tbody tr:first-child select.material');
            if (firstRowMaterial && firstRowMaterial.options.length > 1) {
                firstRowMaterial.selectedIndex = 1;
                autoFillFields(firstRowMaterial);
            }
        });
    </script>


    <script>
        function calculateTotals() {
            let rows = document.querySelectorAll('#uniformTable tbody tr');
            let total = 0,
                payable = 0,
                waived = 0;

            rows.forEach(row => {
                let amount = parseFloat(row.querySelector('input[name="total_price[]"]').value) || 0;
                let qty = parseFloat(row.querySelector('input[name="assigned_qty[]"]').value) || 0;
                let payStatus = row.querySelector('select[name="is_payable[]"]').value;
                if (payStatus.toLowerCase() === "yes") payable += amount;
                else waived += amount;
            });

            total = payable + waived;

            document.getElementById('payable').value = payable.toFixed(2);
            document.getElementById('waived').value = waived.toFixed(2);
            document.getElementById('total').value = total.toFixed(2);
        }

        function autoFillFields(select) {
            let opt = select.options[select.selectedIndex];
            let row = select.closest('tr');

            row.querySelector('input[name="available_qty[]"]').value = opt.dataset.available || '';
            row.querySelector('input[name="price[]"]').value = opt.dataset.price || '';
            row.querySelector('.note').value = opt.dataset.note || '';
            row.querySelector('.discount_type').value = opt.dataset.discountType || '';
            row.querySelector('.discount_value').value = opt.dataset.discountValue || '';
            row.querySelector('select[name="is_payable[]"]').value = opt.dataset.payable || 'no';
            togglePayable(row.querySelector('select[name="is_payable[]"]'));
            calculateRow(row.querySelector('input[name="assigned_qty[]"]'));
        }


        function togglePayable(payableSelect) {
            let row = payableSelect.closest('tr');
            let discountType = row.querySelector('.discount_type');
            let discountValue = row.querySelector('.discount_value');

            if (payableSelect.value.toLowerCase() === "no") {
                discountType.value = "";
                discountValue.value = "";
                discountType.disabled = true;
                discountValue.disabled = true;
            } else {
                discountType.disabled = false;
                discountValue.disabled = false;
            }

            calculateRow(row.querySelector('input[name="assigned_qty[]"]'));
        }


        function calculateRow(field) {
            let row = field.closest('tr');
            let assigned = parseFloat(row.querySelector('input[name="assigned_qty[]"]').value) || 0;
            let price = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;
            let payable = row.querySelector('select[name="is_payable[]"]').value.toLowerCase();
            let discountType = row.querySelector('.discount_type').value;
            let discountValue = parseFloat(row.querySelector('.discount_value').value) || 0;

            let total = assigned * price;
            let discount = 0;

            if (payable === "yes") {
                if (discountType === "percentage") {
                    if (discountValue > 100) discountValue = 100;
                    discount = (total * discountValue) / 100;
                } else if (discountType === "amount") {
                    if (discountValue > total) discountValue = total;
                    discount = discountValue;
                }
            }

            let finalTotal = Math.max(total - discount, 0);
            row.querySelector('.total_price').value = finalTotal.toFixed(2);

            calculateTotals();
        }


        function addNewRow() {
            let tableBody = document.getElementById('tableBody');
            let rowCount = tableBody.rows.length;
            let firstRow = tableBody.rows[0];

            let newRow = firstRow.cloneNode(true);
            newRow.querySelector('.serial').textContent = rowCount + 1;
            newRow.querySelectorAll('input').forEach(input => input.value = '');
            newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

            let btn = newRow.querySelector('button');
            btn.disabled = false;
            btn.className = 'btn btn-sm btn-danger';
            btn.innerText = '-';
            btn.setAttribute('onclick', 'deleteRow(this)');

            tableBody.appendChild(newRow);
        }


        function deleteRow(button) {
            let tableBody = document.getElementById('tableBody');
            if (button.closest('tr') === tableBody.rows[0]) return;
            button.closest('tr').remove();
            Array.from(tableBody.rows).forEach((row, index) => {
                row.querySelector('.serial').textContent = index + 1;
            });
            calculateTotals();
        }
    </script>
@endsection
