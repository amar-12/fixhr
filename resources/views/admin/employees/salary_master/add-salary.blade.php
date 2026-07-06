<?php
use App\Helpers\RolePermissionLogics;
$permission = new RolePermissionLogics();
?>
@extends('admin.layout.master')
@section('title')
    {{ $pageTitle }}
@endsection
@section('css')
    <style>
        .add-section {
            background-color: #f1f3f5;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
        }

        .add-section-header {
            font-weight: bold;
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .small-input {
            width: 100px;
        }

        .salary-preview-card {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
        }

        .salary-preview-header {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .preview-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .preview-item span {
            font-size: 0.9rem;
        }

        .preview-total {
            font-weight: bold;
            font-size: 1rem;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }
    </style>
@endsection
@section('content')
    <x-breadcrumb :breadcrumbs="$breadcrumbs" />
    <div class="mt-4">
        <!-- <form action="{{ route('salary-template.store') }}" method="POST">
                    @csrf
                    <meta name="csrf-token" content="{{ csrf_token() }}"> -->

        <div class="row">
            <!-- Left Form Section -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Generate Salary</h5>

                        <div class="row dynamic-row">


                            <div class="form-group col-md">
                                <label for="employee_0">
                                    Employee <span style="color:red;">*</span>
                                </label>
                                <select id="employee_0" name="rows[0][employee]" class="form-control select2"
                                    data-placeholder="Select Employee" data-url="/ajax/employees">
                                    <option value="" selected disabled>Select Employee</option>
                                </select>
                            </div>



                            <!-- Month and Year Select -->
                            <div class="form-group col-md-4">
                                <label for="month">Month <span style="color:red;">*</span></label>
                                <select id="month" name="month" class="form-control">
                                    <option value="" selected disabled>Select Month</option>
                                    <option value="01">January</option>
                                    <option value="02">February</option>
                                    <option value="03">March</option>
                                    <option value="04">April</option>
                                    <option value="05">May</option>
                                    <option value="06">June</option>
                                    <option value="07">July</option>
                                    <option value="08">August</option>
                                    <option value="09">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="year">Year <span style="color:red;">*</span></label>
                                <select id="year" name="year" class="form-control">
                                    <option value="" selected disabled>Select Year</option>
                                    <option value="2025">2025</option>
                                    <option value="2024">2024</option>
                                    <!-- Add other years here -->
                                </select>
                            </div>
                            <!-- Attendance Count -->
                            <div class="attendance-count mt-3" id="attendance-count">
                                <h5>Attendance Count</h5>
                                <p id="selected-month-year"></p>
                                <p id="total-days"></p>
                                <p id="weekend-days"></p>
                                <p id="workable-days"></p>


                                <p>Present: <span id="present-count"></span></p>
                                <p>Half Day: <span id="halfday-count"></span></p>
                                <p>Leave: <span id="leave-count"></span></p>
                                <p>Absent: <span id="absent-count"></span></p>
                                <p>Week Off: <span id="weekoff-count"></span></p>
                            </div>
                        </div>

                        <form action="{{ route('salary-template.store') }}" method="POST">
                            @csrf
                            <meta name="csrf-token" content="{{ csrf_token() }}">
                            <!-- Template Name and Description -->
                            <!-- <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Template Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="template_name" class="form-control"
                                                                placeholder="Enter Template Name" required />
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Description</label>
                                                            <textarea name="description" class="form-control" rows="1"
                                                                placeholder="Max 500 Characters"></textarea>
                                                        </div>
                                                    </div> -->

                            <div class="mb-3 d-none" id="earningsTableSection">
                                <h6>Earnings</h6>
                                <table class="table table-bordered" id="earningsTable">
                                    <thead>
                                        <tr>
                                            <th>Salary Components</th>
                                            <th>Calculation Type</th>
                                            <th>Monthly Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="mb-3 d-none" id="deductionsTableSection">
                                <h6>Deductions</h6>
                                <table class="table table-bordered" id="deductionsTable">
                                    <thead>
                                        <tr>
                                            <th>Deduction Components</th>
                                            <th>Employee Contribution</th>
                                            <th>Employer Contribution</th>
                                            <th>Deduction Cycle</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <input type="hidden" name="total_earnings" id="hiddenTotalEarnings" value="0">
                            <input type="hidden" name="total_gross_salary" id="hiddenTotalGrossSalary" value="0">
                            <input type="hidden" name="total_deductions" id="hiddenTotalDeductions" value="0">
                            <input type="hidden" name="net_pay" id="hiddenNetPay" value="0">
                            <input type="hidden" name="annual_ctc" id="hiddenAnnualCtc" value="0">
                            <input type="hidden" name="monthly_ctc" id="hiddenMonthlyCtc" value="0">
                            <input type="hidden" id="hiddenComponentType" name="componentType" value="">
                            <input type="hidden" id="hiddenComponentList" name="componentList" value="">
                            <input type="hidden" id="hiddenEarningsData" name="earnings_data">
                            <input type="hidden" id="hiddenDeductionsData" name="deductions_data">


                            <button id="savePayrollTemplate" type="submit" class="btn btn-outline-primary">Save</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Salary Preview Section -->
            <div class="col-md-4">
                <div class="salary-preview-card">
                    <div class="salary-preview-header">Salary Preview</div>
                        <div class="preview-total">
                        <div class="d-flex justify-content-between">
                            <span>Total Earnings</span>
                            <span id="totalEarnings">&#8377; 0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Gross Salary</span>
                            <span id="totalGrossSalary">&#8377; 0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Total Deductions</span>
                            <span id="totalDeductions">&#8377; 0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Net Pay</span>
                            <span id="netPay">&#8377; 0</span>
                        </div>
                    </div>

                </div>
                <div class="add-section mt-2">
                    <div class="add-section-header">Add New Allowance/Deduction</div>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <input type="hidden" name="payroll_template_id" value="{{ $payrollTemplate->id ?? '' }}">
                            <label class="form-label">Annual CTC</label>
                            <input type="number" name="annualCTC" id="annualCTC" class="form-control"
                                placeholder="Enter Annual CTC" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Monthly CTC</label>
                            <input type="number" name="monthlyCTC" id="monthlyCTC" class="form-control"
                                placeholder="Enter Monthly CTC" readonly />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select id="componentType" name="componentType" class="form-select">
                            <option value="EARNINGS">EARNING</option>
                            <option value="DEDUCTIONS">Deduction</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Components</label>
                        <select id="componentList" name="componentList" class="form-select">
                            <option value="">Select Component</option>
                        </select>
                    </div>
                    <button id="addComponentBtn" class="btn btn-outline-primary w-100">Add Component</button>
                </div>
            </div>
        </div>
        <!-- </form> -->
    </div>
    </div>
@endsection

@section('script')

    <script type="text/javascript">
        $(document).ready(function () {
            // Function to fetch attendance data
            function fetchAttendance() {
                var employeeId = $('#employee_0').val();
                var month = $('#month').val();
                var year = $('#year').val();

                if (!employeeId || !month || !year) {
                    return; // Do nothing if any required field is missing
                }

                var totalDays = new Date(year, month, 0).getDate();

                // Calculate weekends (Saturdays and Sundays)
                var weekendCount = 0;
                for (var day = 1; day <= totalDays; day++) {
                    var date = new Date(year, month - 1, day); // month - 1 because JS months are 0-indexed
                    if (date.getDay() === 0 || date.getDay() === 6) { // 0 = Sunday, 6 = Saturday
                        weekendCount++;
                    }
                }




                // Calculate workable days (excluding weekends)
                var workableDays = totalDays - weekendCount;

                // Call API to fetch attendance data for selected employee, month, and year
                $.ajax({
                    url: `/ajax/attendance-count/${employeeId}`,
                    method: 'GET',
                    data: { month: month, year: year },
                    success: function (response) {
                        if (response.attendanceCount) {
                            $('#attendance-count').show();
                            $('#selected-month-year').text(`Month: ${month}, Year: ${year}`);
                            $('#total-days').text(`Total Days: ${totalDays}`);
                            $('#weekend-days').text(`Total Week Offs (Sat/Sun): ${weekendCount}`);
                            $('#workable-days').text(`Workable Days: ${workableDays}`);

                            $('#present-count').text(response.attendanceCount.presentCount);
                            $('#halfday-count').text(response.attendanceCount.halfDayCount);
                            $('#leave-count').text(response.attendanceCount.leaveCount);
                            $('#absent-count').text(response.attendanceCount.absentCount);
                            $('#weekoff-count').text(response.attendanceCount.weekOffCount);
                        }
                    },
                    error: function (error) {
                        console.error('Error fetching attendance count:', error);
                    }
                });
            }

            // Trigger fetch when employee, month, or year is changed
            $('#employee_0, #month, #year').on('change', function () {
                fetchAttendance();
            });

            // When the form is submitted
            $("form").submit(function (event) {
                // Prevent the default form submission
                event.preventDefault();

                // Get the calculated values
                var totalEarnings = parseFloat($("#totalEarnings").text()) || 0;
                var totalGrossSalary = parseFloat($("#totalGrossSalary").text()) || 0;
                var totalDeductions = parseFloat($("#totalDeductions").text()) || 0;
                var netPay = parseFloat($("#netPay").text()) || 0;
                var annualCTC = parseFloat($("#annualCTC").val()) || 0;
                var monthlyCTC = parseFloat($("#monthlyCTC").val()) || 0;
                var componentType = $("#componentType").val() || "";
                var componentList = $("#componentList").val() || "";

                // Get present and week-off days from your elements (e.g. based on attendance data)
                var presentDays = parseInt($("#presentDays").val()) || 0;
                var weekOffDays = parseInt($("#weekOffDays").val()) || 0;

                // Calculate the total workable days (assuming you already have total days in the month and weekends counted)
                var totalDaysInMonth = parseInt($("#totalDaysInMonth").val()) || 30; // You can adjust this dynamically
                var weekendDays = parseInt($("#weekendDays").val()) || 0; // Adjust this based on actual data

                var workableDays = totalDaysInMonth - weekendDays; // Workable days excluding weekends
                var adjustedMonthlyCTC = (monthlyCTC / workableDays) * (presentDays + weekOffDays); // Adjust monthly CTC based on present + week off days


                // var selectedMonthYear = $("#selected-month-year").text();
                // var totalDays = parseInt($("#total-days").text()) || 0;
                // var weekendDays = parseInt($("#weekend-days").text()) || 0;
                // var workableDays = parseInt($("#workable-days").text()) || 0;

                // var presentCount = parseInt($("#present-count").text()) || 0;
                // var halfDayCount = parseInt($("#halfday-count").text()) || 0;
                // var leaveCount = parseInt($("#leave-count").text()) || 0;
                // var absentCount = parseInt($("#absent-count").text()) || 0;
                // var weekOffCount = parseInt($("#weekoff-count").text()) || 0;

                // // Log the values to the console for debugging
                // console.log("Selected Month and Year: " + selectedMonthYear);
                // console.log("Total Days: " + totalDays);
                // console.log("Weekend Days: " + weekendDays);
                // console.log("Workable Days: " + workableDays);

                // console.log("Present Count: " + presentCount);
                // console.log("Half Day Count: " + halfDayCount);
                // console.log("Leave Count: " + leaveCount);
                // console.log("Absent Count: " + absentCount);
                // console.log("Week Off Count: " + weekOffCount);




                // Now set the adjusted monthly CTC to be used in hidden field
                $("#adjustedMonthlyCTC").val(adjustedMonthlyCTC.toFixed(2)); // Store adjusted CTC value

                // Collect earnings data
                var earningsData = [];
                $("#earningsTable .EARNING_ROW").each(function () {
                    var componentId = $(this).data("id");
                    var componentName = $(this).find("td:first").text();
                    var threshold = $(this).find("td:eq(1) input").val();
                    var monthlyAmount = $(this).find("td:eq(2) input").val();

                    earningsData.push({
                        id: componentId,
                        name: componentName,
                        threshold: threshold,
                        monthly_amount: monthlyAmount
                    });
                });

                // Collect deductions data
                var deductionsData = [];
                $("#deductionsTable .DEDUCTION_ROW").each(function () {
                    var componentId = $(this).data("id");
                    var componentName = $(this).find("td:first").text();
                    var employeeContribution = $(this).find("td:eq(1) input").val();
                    var employerContribution = $(this).find("td:eq(2) input").val();
                    var deductionCycle = $(this).find("td:eq(3) input").val();

                    deductionsData.push({
                        id: componentId,
                        name: componentName,
                        employee_contribution: employeeContribution,
                        employer_contribution: employerContribution,
                        deduction_cycle: deductionCycle
                    });
                });

                // Update the hidden input fields
                $("#hiddenTotalEarnings").val(totalEarnings);
                $("#hiddenTotalGrossSalary").val(totalGrossSalary);
                $("#hiddenTotalDeductions").val(totalDeductions);
                $("#hiddenNetPay").val(netPay);
                $("#hiddenAnnualCtc").val(annualCTC);
                $("#hiddenMonthlyCtc").val(monthlyCTC);
                $("#hiddenComponentType").val(componentType);
                $("#hiddenComponentList").val(componentList);

                // Convert arrays to JSON and store in hidden fields
                $("#hiddenEarningsData").val(JSON.stringify(earningsData));
                $("#hiddenDeductionsData").val(JSON.stringify(deductionsData));

                // Submit the form programmatically
                this.submit();
            });
        });
    </script>



        <script>
            $("#saveTemplateBtn").click(function () {
                event.preventDefault();
                var templateName = $("#templateName").val();
                var templateDescription = $("#templateDescription").val();
                // var componentType = $("#componentType").val();
                // var componentList = $("#componentList").val();
                const totalEarningsElement = document.getElementById('totalEarnings');
                const totalGrossSalaryElement = document.getElementById('totalGrossSalary');
                const totalDeductionsElement = document.getElementById('totalDeductions');
                const netPayElement = document.getElementById('netPay');
                // const annualCTCElement = document.getElementById('annualCTC');
                var annualCTC = parseFloat($("#annualCTC").val()) || 0;


                const hiddenTotalEarnings = document.getElementById('hiddenTotalEarnings');
                const hiddenTotalGrossSalary = document.getElementById('hiddenTotalGrossSalary');
                const hiddenTotalDeductions = document.getElementById('hiddenTotalDeductions');
                const hiddenNetPay = document.getElementById('hiddenNetPay');
                const hiddenAnnualCtc = document.getElementById('hiddenAnnualCtc');


                var earningsData = [];
                var deductionsData = [];




                // Earnings Data Collect karo
                $("#earningsTable .EARNING_ROW").each(function () {
                    earningsData.push({
                        component_id: $(this).data("id"),
                        amount: $(this).find("input").val()
                    });
                });

                // Deductions Data Collect karo
                $("#deductionsTable .DEDUCTION_ROW").each(function () {
                    deductionsData.push({
                        component_id: $(this).data("id"),
                        employee_contribution: $(this).find("input").eq(0).val(),
                        employer_contribution: $(this).find("input").eq(1).val()
                    });
                });

                // AJAX Request bhejo
                $.ajax({
                    url: "/payroll/template/save",
                    type: "POST",
                    data: {
                        template_name: templateName,
                        template_description: templateDescription,
                        component_type: componentType,
                        component_list: componentList,
                        earnings: earningsData,
                        deductions: deductionsData,
                        total_earnings: totalEarnings,
                        total_deductions: totalDeductions,
                        net_pay: netPay,
                        annual_ctc: annualCTC,
                        gross_salary: grossSalary,
                        annual_ctc: $("#annualCTC").val(),
                        monthly_ctc: $("#monthlyCTC").val(),
                        componentType: [],
                        componentList: []
                                            _token: $("meta[name='csrf-token']").attr("content")
                    },
                    success: function (response) {
                        if (response.status) {
                            alert("Template Created Successfully!");
                            window.location.reload();
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function (xhr) {
                        alert("Request Failed: " + xhr.responseText);
                    }
                });
            });



        </script>




        <script type="text/javascript">


            var earningRowDataArray = [];
            var deductionRowDataArray = [];
            var monthlyCTC = 0;



            $(document).ready(function () {

                // Initial population of component dropdown
                getComponentByType("#componentType", earningRowDataArray, deductionRowDataArray);

                // When Add Component Button is Clicked
                $("#addComponentBtn").click(function () {
                    var annualCTCInput = $("#annualCTC").val();
                    var componentType = $("#componentType").val();
                    var selectedOption = $("#componentList option:selected");

                    var componentVal = selectedOption.val();
                    var componentName = selectedOption.text();
                    if (componentType == '' || componentVal == '' || annualCTCInput == '') {
                        alert('Fields are required!');
                        return false;
                    }



                    // Add the component to the target table
                    var targetTable = '';
                    if (componentType === "EARNINGS") {
                        $("#earningsTableSection").removeClass('d-none');
                        targetTable = "#earningsTable";
                        var threshold = selectedOption.data('sa_threshold') || '';
                        var sa_calc_type = selectedOption.data('sa_calculation_type') || '';
                        var sa_calc_type_id = selectedOption.data('sa_calc_type_id') || null;
                        var earning_cal_type_id = selectedOption.data('earning_cal_type_id') || null;
                        var sa_earning_type_id = selectedOption.data('sa_earning_type_id') || null;
                        // Check if the component is already added
                        if (earningRowDataArray.includes(componentVal)) {//${earning_cal_type_id==364?:''}
                            alert("Earning Component already added.");
                            return false;
                        }



                        var calculation_on_amount = 0;
                        var componentValue = 0;
                        if (sa_earning_type_id == 360) {//Basic
                            componentValue = monthlyCTC ? monthlyCTC * (threshold / 100) : 0;
                        } else if (sa_earning_type_id == 361) {//House Rent Allowance
                            var basic_salary = $("#monthly_amt_360").val();
                            componentValue = basic_salary ? basic_salary * (threshold / 100) : 0;
                        } else if (sa_earning_type_id == 362) {//Dearness Allowance
                            var basic_salary = $("#monthly_amt_360").val();
                            componentValue = basic_salary ? basic_salary * (threshold / 100) : 0;
                        } else if (sa_earning_type_id == 363) {//Conveyance Allowance
                            if (earning_cal_type_id == 348) {
                                componentValue = threshold;
                            }
                        } else if (sa_earning_type_id == 364) { // Other Allowance (User Input)
                            let inputField = $(`#monthly_amt_${sa_earning_type_id}`);
                            if (inputField.length > 0) {
                                componentValue = parseFloat(inputField.val()) || 0;
                            } else {
                                componentValue = 0; // Fallback in case input is not yet available
                            }
                        } else {
                            var componentValue = threshold;
                        }
                        console.log("Component Value:", componentValue);

                        // var newPreviewItem = `<div class="preview-item" data-id="${componentName}">
                        // <span>${componentName}</span>
                        // <span>&#8377; ${(earning_cal_type_id == 348 || earning_cal_type_id == 364) ? sa_calc_type : calculation_on_amount+' x '+threshold1+sa_calc_type}</span>
                        // <span id="EARNING_${head_type_id}">${componentValue}</span>
                        //  </div>`;


                        var newRow = `<tr class="EARNING_ROW" data-row="${componentVal}" data-id="${componentVal}" data-calc_type_id="${sa_calc_type_id}" data-earning_type_id="${sa_earning_type_id}">
                                    <td>${componentName}</td>
                                    <td>
                                        <div class="input-group">
                                            <input class="form-control small-input" value="${threshold ? threshold : ''}"  readonly  />
                                            <span class="input-group-text">${sa_calc_type}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input class="form-control small-input monthly_amt_earning" id="monthly_amt_${sa_earning_type_id}" value="${componentValue}"  ${sa_earning_type_id == 364 ? '' : 'readonly'}  />
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-danger  btn-sm remove-row" data-id="${componentVal}">
                                            <i class="fa fa-remove"></i>
                                        </button>
                                    </td>
                                </tr>`;

                        earningRowDataArray.push(componentVal);

                        // Start with 0 instead of `monthly_amt_360` to avoid double counting
                        let totalEarningSum = 0;

                        // Iterate over each input with the class 'monthly_amt_earning'
                        $('.monthly_amt_earning').each(function () {
                            const inputValue = parseFloat($(this).val()) || 0;
                            totalEarningSum += inputValue;
                        });

                        // Ensure first component value is included only if not in `monthly_amt_earning`
                        if (!$(`#monthly_amt_${sa_earning_type_id}`).length) {
                            totalEarningSum += componentValue;
                        }

                        console.log("Total Earning Sum: ", totalEarningSum);
                        $("#totalGrossSalary").text(totalEarningSum.toFixed(2));
                        $("#totalEarnings").text(totalEarningSum.toFixed(2));



                        //updateSalaryPreview(sa_earning_type_id, earning_cal_type_id, componentType, componentName, threshold, null, sa_calc_type_id, sa_calc_type, null);
                        console.log(sa_earning_type_id, earning_cal_type_id, componentType, componentName, threshold, null, sa_calc_type_id, sa_calc_type, null);
                    } else {
                        var employee_contribution = selectedOption.data('employee_contribution') ? selectedOption.data('employee_contribution') : '';
                        var employer_contribution = selectedOption.data('employee_contribution') ? selectedOption.data('employer_contribution') : '';
                        var deduction_cycle = selectedOption.data('deduction_cycle'); //$deduction->fh_deduction_cycle->m_name
                        if (deductionRowDataArray.includes(componentVal)) {
                            alert("Deduction Component already added.");
                            return false;
                        }
                        $("#deductionsTableSection").removeClass('d-none');
                        targetTable = "#deductionsTable";

                        var newRow = `<tr class="DEDUCTION_ROW" data-row="${componentVal}" data-id="${componentVal}">
                                    <td>${componentName}</td>
                                    <td>
                                        <input type="number" class="form-control small-input" value="${employee_contribution}" readonly />
                                    </td>
                                    <td>
                                        <input type="number" class="form-control small-input" value="${employer_contribution}" readonly />
                                    </td>
                                    <td>
                                        <input  class="form-control small-input" value="${deduction_cycle}" readonly />
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-danger  btn-sm remove-row" data-id="${componentVal}">
                                            <i class="fa fa-remove"></i>
                                        </button>
                                    </td>
                                </tr>`;
                        // Add to the selected list
                        deductionRowDataArray.push(componentVal);
                        // updateSalaryPreview(null,null, componentType, componentName, employee_contribution, employer_contribution, calc_type_id, null, deduction_cycle);

                    }

                    // Append the new row to the table
                    $(targetTable).append(newRow);

                    // Refresh the dropdown list
                    getComponentByType("#componentType", earningRowDataArray, deductionRowDataArray);
                });

                // Listen for changes in the componentType dropdown
                $('#componentType').on('change', function () {
                    // earningRowDataArray = []; // Reset data array when the type changes
                    getComponentByType("#componentType", earningRowDataArray, deductionRowDataArray);
                });

                // Remove row functionality
                $(document).on('click', '.remove-row', function () {
                    var RowIdToRemove = $(this).data('id'); // Get component ID to remove
                    // Remove row from DOM
                    $(this).closest('tr').remove();

                    // Remove the component ID from earningRowDataArray
                    earningRowDataArray = earningRowDataArray.filter(function (item) {
                        return item != RowIdToRemove;
                    });

                    // Refresh the dropdown list
                    getComponentByType("#componentType", earningRowDataArray, deductionRowDataArray);
                });

            });

            // Function to populate the component dropdown based on the selected type
            function getComponentByType(componentTypeSelector, earningRowIds, deductionRowIds) {
                var componentType = $(componentTypeSelector).val(); // Get the selected value

                if (componentType) {
                    // Make an AJAX GET request to fetch components
                    $.ajax({
                        url: "/payroll/template/get-components/" + componentType + "?earningRowIds=" + earningRowIds.join(',') + "&deductionRowIds=" + deductionRowIds.join(','),
                        type: "GET",
                        dataType: "json",
                        success: function (response) {
                            // Clear the dropdown
                            $('#componentList').empty();
                            $('#componentList').append('<option value="">Select Component</option>');

                            if (response.status == true) {

                                $.each(response.data, function (index, component) {
                                    if (componentType == 'EARNINGS') {
                                        var earning_cal_type = component.fh_allowance_calculation_type ?
                                            component.fh_allowance_calculation_type.m_name : '';
                                        var earning_cal_type_id = component.fh_allowance_calculation_type ?
                                            component.fh_allowance_calculation_type.m_id : '';

                                        $('#componentList').append('<option value="' + component.sa_id +
                                            '" data-sa_threshold="' + component.sa_threshold_value +
                                            '" data-sa_earning_type_id="' + component.sa_earning_type_id +
                                            '" data-component_type="' + componentType +
                                            '" data-sa_calculation_type="' + earning_cal_type +
                                            '"  data-earning_cal_type_id="' + earning_cal_type_id +
                                            '" data-sa_calc_type_id="' + component.sa_id +
                                            '">' + component.sa_title + '</option>');
                                    } else if (componentType == 'DEDUCTIONS') {
                                        $('#componentList').append('<option value="' + component.std_id +
                                            '" data-id="' + component.std_id +
                                            '" data-component_type="' + componentType +
                                            '" data-employee_contribution="' + component.std_employee_contri_rate_amount +
                                            '" data-employer_contribution="' + component.std_employer_contri_rate_amount +
                                            '" data-deduction_cycle="' + component.fh_deduction_cycle.m_name +
                                            '">' + component.fh_deduction_type.m_name + '</option>');
                                    }
                                });

                            } else {
                                $('#componentList').append('<option value="">No components found</option>');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log("Error: " + error); // Debugging
                            $('#componentList').html('<p class="text-danger">Failed to fetch components. Please try again.</p>');
                        }
                    });
                } else {
                    $('#componentList').empty();
                }
            }


            function updateSalaryPreview(head_type_id, earning_cal_type_id, componentType, componentName, threshold1, threshold2, sa_calc_type_id, sa_calc_type, deduction_cycle) {

                var previewContainer = $(".salary-preview-card");
                var typeHeaderSelector = componentType === "EARNINGS" ? ".earnings-header" : ".deductions-header";

                // Check if header exists, if not add it
                if (previewContainer.find(typeHeaderSelector).length === 0) {
                    var typeHeader = `<div class="${componentType === "EARNINGS" ? 'earnings-header' : 'deductions-header'}">
                                    <strong>${componentType === "EARNINGS" ? 'Earnings' : 'Deductions'}</strong>
                                </div>`;
                    previewContainer.find(".preview-total").before(typeHeader);
                }

                // Insert a new preview item under the appropriate header
                if (componentType === "EARNINGS") {

                    var calculation_on_amount = 0;
                    if (head_type_id == 360 || head_type_id == 362) {
                        calculation_on_amount = monthlyCTC;
                        var componentValue = monthlyCTC ? monthlyCTC * (threshold1 / 100) : 0;
                    } else if (head_type_id == 361) {
                        var basic_salary = $("#EARNING_360").text();
                        calculation_on_amount = basic_salary;
                        var componentValue = basic_salary ? basic_salary * (threshold1 / 100) : 0;
                    } else if (head_type_id == 364) {
                        $('.EARNING_ROW[data-earning_type_id="364"]').each(function () {
                            // Get the value of the input field inside this row
                            const inputValue = $(this).find('input.form-control').val();
                            console.log("Input Value:", inputValue);
                            // var componentValue = inputValue;
                        });


                    } else {
                        var componentValue = threshold1;
                    }


                    var newPreviewItem = `<div class="preview-item" data-id="${componentName}">
                                <span>${componentName}</span>
                                <span>&#8377; ${(earning_cal_type_id == 348 || earning_cal_type_id == 364) ? sa_calc_type : calculation_on_amount + ' x ' + threshold1 + sa_calc_type}</span>
                                <span id="EARNING_${head_type_id}">${componentValue}</span>
                                 </div>`;
                } else {
                    var newPreviewItem = `<div class="preview-item" data-id="${componentName}">
                                <span>${componentName}</span>
                                <span>&#8377; ${value}</span>
                                <span>&#8377; ${value}</span>
                            </div>`;
                }

                previewContainer.find(typeHeaderSelector).after(newPreviewItem);

            }

            $(document).ready(function () {
                // Initialize earnings, deductions, and net pay to 0
                $("#totalEarnings").text("₹ 0");
                $("#totalGrossSalary").text("₹ 0");
                $("#totalDeductions").text("₹ 0");
                $("#netPay").text("₹ 0");

                $("#annualCTC").on("blur", function () {
                    var annualCTC = parseFloat($(this).val()) || 0; // Default to 0 if empty or invalid
                    if (annualCTC > 0) {
                        calculateAndUpdateSalary(annualCTC);
                    } else {
                        $("#totalEarnings").text("₹ 0");
                        $("#totalGrossSalary").text("₹ 0");
                        $("#totalDeductions").text("₹ 0");
                        $("#netPay").text("₹ 0");
                    }
                });
            });


            function calculateAndUpdateSalary(annualCTC) {
                monthlyCTC = (annualCTC / 12).toFixed(2); // Monthly CTC calculation
                $("#monthlyCTC").val(monthlyCTC);
                var deductions = calculateTotalDeductions();
                // var earnings = (monthlyCTC - deductions).toFixed(2); // Earnings = Monthly CTC - Deductions
                var earnings = updateTotalEarnings();
                // Update UI with calculated values
                $("#totalEarnings").text(`₹ ${earnings}`);
                $("#totalDeductions").text(`₹ ${deductions}`);
                $("#totalGrossSalary").text(`₹ ${earnings}`);
                $("#netPay").text(`₹ ${(monthlyCTC - deductions).toFixed(2)}`);

                // Update Earnings Table values (example: Distribute across earnings components proportionally)
                distributeEarningsAcrossComponents(earnings);
            }





            // Function to calculate and update total earnings
            function updateTotalEarnings() {
                let totalEarningSum = 0;

                // Iterate over each input with the class 'monthly_amt_earning'
                $('.monthly_amt_earning').each(function () {
                    const inputValue = parseFloat($(this).val()) || 0;
                    totalEarningSum += inputValue;
                });

                console.log("Updated Total Earning Sum: ", totalEarningSum);
                $("#totalGrossSalary").text(totalEarningSum);
                $("#totalEarnings").text(totalEarningSum);
            }

            // Attach event listener only for Other Allowance (ID: 364)
            $(document).on('input', '#monthly_amt_364', function () {
                updateTotalEarnings();
            });



            function calculateTotalDeductions() {
                var total = 0;
                $("#deductionsTable .DEDUCTION_ROW").each(function () {
                    var deductionValue = parseFloat($(this).find("input").val()) || 0;
                    total += deductionValue;
                });
                return total;
            }

            function distributeEarningsAcrossComponents(totalEarnings) {
                var totalComponents = $("#earningsTable .EARNING_ROW").length;
                if (totalComponents > 0) {
                    var perComponentEarning = (totalEarnings / totalComponents).toFixed(2);

                    $("#earningsTable .EARNING_ROW").each(function () {
                        $(this).find("input").val(perComponentEarning); // Update the input value with the distributed amount
                    });
                }
            }


        </script>

        <script>
            $(document).ready(function () {
                function setupSelect2(selector) {
                    let url = $(selector).data('url'); // Get the URL from the data-url attribute
                    let placeholder = $(selector).data('placeholder'); // Get the placeholder from data-placeholder attribute

                    $(selector).select2({
                        placeholder: placeholder,
                        ajax: {
                            url: url,
                            dataType: 'json',
                            delay: 250, // Delay in ms before sending request
                            data: function (params) {
                                return {
                                    search: params.term // Send the search term to the server
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: data.map(function (item) {
                                        return {
                                            id: item.id,
                                            text: item.name // The text to display in the dropdown
                                        };
                                    })
                                };
                            },
                            cache: true // Cache the results for performance
                        },
                        minimumInputLength: 1 // Start searching when at least 1 character is entered
                    });
                }

                // Initialize Select2 on the employee field
                setupSelect2('#employee_0');
            });


        </script>
@endsection
