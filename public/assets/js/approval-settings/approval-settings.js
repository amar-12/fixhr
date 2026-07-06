// global Variable
var moduleSettingsData = null; // Global variable to store module validation data
var ruleCriteriaData = null; // Global variable to store rule criteria data
var approverData = null; // Global variable to store approver data
var approverDataUpdatedVersion = null; // Global variable to store approver data
var actionUponApproval = null;
var ruleCriteriaRemovedRowIds = [];
var ruleApproverRemovedRowIds = [];
var r = ruleCriteriaCount; // iterator rule criteria
var approvalNextStatusSelectedId = null;
const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");
function showSwal(icon, text) {
    Swal.fire({ icon: icon, text: text });
}

// Function to initialize process approver
const moduleSettingTab = document.getElementById("module-setting");
const ruleCriteriaTab = document.getElementById("rule-criteria");
const approverSettingTab = document.getElementById("approver-setting");
const actionUponRejectTab = document.getElementById("action-upon-reject");

let forInitialLoad = 1;
function switchTab(currentTab, nextTab, $id = null) {
    $(`#settings-tab a[href="${currentTab}"]`).removeClass("active");
    $(currentTab).hide();
    $(`#settings-tab a[href="${nextTab}"]`).addClass("active");
    $(nextTab).removeClass("fade").show();
    // Reset all tabs to hidden (d-none) and remove active class
    moduleSettingTab.classList.remove("active", "d-block");
    moduleSettingTab.classList.add("d-none");
    ruleCriteriaTab.classList.remove("active", "d-block");
    ruleCriteriaTab.classList.add("d-none");
    approverSettingTab.classList.remove("active", "d-block");
    approverSettingTab.classList.add("d-none");
    actionUponRejectTab.classList.remove("active", "d-block");
    actionUponRejectTab.classList.add("d-none");

    // Show the correct tab based on the $id value
    // moduleSettingTab.classList.add("active", "d-block");
    if ($id == 1) {
        ruleCriteriaTab.classList.add("active", "d-block");
    } else if ($id == 2) {
        approverSettingTab.classList.add("active", "d-block");
        if (forInitialLoad) {
            forInitialLoad = 0;
        }
    } else if ($id == 3) {
        actionUponRejectTab.classList.add("active", "d-block");
    } else if ($id == 4) {
        // actionUponRejectTab.classList.add("active", "d-block");
    } else if ($id == 5) {
    }
}

function backButton(currentTab, backTab, $id) {
    // Remove 'active' class from the current tab and hide it
    $(`#settings-tab a[href="${currentTab}"]`).removeClass("active");
    $(currentTab).hide();

    // Add 'active' class to the back tab and show it
    $(`#settings-tab a[href="${backTab}"]`).addClass("active");
    $(backTab).removeClass("fade").show();

    // Reset all tabs to hidden (d-none) and remove active class
    moduleSettingTab.classList.remove("active", "d-block");
    moduleSettingTab.classList.add("d-none");
    ruleCriteriaTab.classList.remove("active", "d-block");
    ruleCriteriaTab.classList.add("d-none");
    approverSettingTab.classList.remove("active", "d-block");
    approverSettingTab.classList.add("d-none");
    actionUponRejectTab.classList.remove("active", "d-block");
    actionUponRejectTab.classList.add("d-none");

    // Show the correct tab based on the $id value
    const tabs = {
        1: moduleSettingTab,
        2: ruleCriteriaTab,
        3: approverSettingTab,
        4: actionUponRejectTab,
    };

    if (tabs[$id]) {
        tabs[$id].classList.add("active", "d-block");
    }
}

function validateModuleForm() {
    var moduleId = $("#module").val().trim();
    var moduleName = $("#module_name").val().trim();
    var exp_rej_day = $("#exp_rej_day").val().trim();
    var noti_before_days = $("#noti_before_days").val().trim();
    var executionOn = $('input[name="execution_on[]"]:checked')
        .map(function () {
            return this.value;
        })
        .get();

    if (!moduleId) {
        showSwal("warning", "Module field is required");
        return false;
    }
    if (!moduleName) {
        showSwal("warning", "Module name is required");
        return false;
    }
    if (executionOn.length < 1) {
        showSwal("warning", "Execute field is required");
        return false;
    }
    return { moduleId, moduleName, executionOn, exp_rej_day, noti_before_days };
}

function ajaxRequest(url, method, data, beforeSend, success, error) {
    $.ajax({ url, method, data, dataType: "json", beforeSend, success, error });
}

function resetForm(formId, buttonId, resetSelect2 = false) {
    $(buttonId).attr("disabled", false);
    $(formId)[0].reset();
    if (resetSelect2) {
        // $("#module").select2();
    }
}

function transformRuleCriteriaData(data) {
    var transformed = {
        dynamic: [],
    };
    data.forEach(function (item) {
        var nameParts = item.name.split("[");
        if (nameParts.length === 3) {
            var index = nameParts[1].slice(0, -1);
            var key = nameParts[2].slice(0, -1);

            if (item.value !== null && item.value !== "") {
                if (!transformed.dynamic[index]) {
                    transformed.dynamic[index] = {};
                }
                transformed.dynamic[index][key] = item.value;
            }
        } else {
            if (item.value !== null && item.value !== "") {
                transformed[nameParts[0]] = item.value;
            }
        }
    });

    // Remove empty dynamic objects
    transformed.dynamic = transformed.dynamic.filter(function (item) {
        return Object.keys(item).length > 0;
    });

    return transformed;
}

$(function () {
    var baseUrl = $("#ajaxUrl").val();
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": csrfToken,
        },
    });

    $("#moduleSaveUptBtn").on("click", function (event) {
        moduleSettingsData = validateModuleForm();
        if (!moduleSettingsData) return false;
        var am_id = $("#am_id").val().trim();
        moduleSettingsData.am_id = am_id;
        moduleSettingsData.moduleDescription = $("#module_description")
            .val()
            .trim();

        switchTab("#module-setting", "#rule-criteria", 1);
    });

    $("#ruleCriteriaNewForm").submit(function (e) {
        e.preventDefault(); // Prevent default form submission
        var formData = $(this).serializeArray();
        var isValid = true;
        var combinations1 = [];
        var errorRows = [];

        formData.forEach(function (field) {
            if (
                field.name.includes("dynamic") &&
                field.name.includes("rc_approval_rule_id")
            ) {
                var currentRow = $('[name="' + field.name + '"]').closest("tr");
                var rowId = currentRow.data("row-id");
                var currentRowIndex = currentRow.index();
                var applyOnId = $(
                    '[name="dynamic[' + rowId + '][rc_approval_rule_id]"]'
                ).val();
                if (!applyOnId) {
                    showSwal("warning", "Apply On field is required");
                    isValid = false;
                    return false;
                }

                var conditionId = $(
                    '[name="dynamic[' + rowId + '][rc_rule_condition_id]"]'
                ).val();

                if (!conditionId) {
                    showSwal("warning", "Condition field is required");
                    isValid = false;
                    return false;
                }

                var ruleValueId = $(
                    '[name="dynamic[' + rowId + '][rule_value]"]'
                ).val();
                if (ruleValueId == "") {
                    showSwal("warning", "Rule Value field is required");
                    isValid = false;
                    return false;
                }

                var combination =
                    applyOnId + "-" + conditionId + "-" + ruleValueId;
                if (combinations1.includes(combination)) {
                    isValid = false;
                    errorRows.push(rowId);
                } else {
                    combinations1.push(combination);
                }
            }
        });

        $("#dynamicTable tbody tr").each(function (index) {
            if (errorRows.includes(index)) {
                showSwal(
                    "error",
                    "Duplicate combinations of Apply On, Condition, and Rule Value have been detected"
                );
                $("#sumbitButton").prop("disabled", false);
                return false;
            } else {
                $("#duplicated_id").html("");
                $(this)
                    .find(".policy-category, .travel-type, .travel-mode")
                    .removeClass("is-invalid");
                $(this)
                    .find(".invalid-feedback")
                    .hide()
                    .text("This field is required.");
            }
        });

        if (isValid) {
            ruleCriteriaData = formData; // Store rule criteria data in global variable
            var transformedRuleCriteriaData =
                transformRuleCriteriaData(ruleCriteriaData);
            ajaxRequest(
                baseUrl + "/admin/settings/tada-settings/save-approval-setting",
                "post",
                {
                    moduleData: moduleSettingsData,
                    ruleCriteriaData: transformedRuleCriteriaData,
                    POST_TYPE: "CHECK_DUPLICATE",
                },
                function () {
                    $("#saveAndUpdateRuleCriteriaButton").attr(
                        "disabled",
                        "disabled"
                    );
                },
                function (data) {
                    $("#saveAndUpdateRuleCriteriaButton").attr(
                        "disabled",
                        false
                    );

                    // resetForm("#moduleSettingForm", "#moduleSaveUptBtn", true);
                    if (data.status_code == 1) {
                        switchTab("#rule-criteria", "#approver-setting", 2);
                    } else {
                        showSwal("warning", data.status_text);
                    }
                },
                function (xhr, status, error) {
                    showSwal("error", error);
                }
            );
        }
    });

    // check the request id in rule criteria
    function getRuleValueById(data, id) {
        var dynamicData = data.dynamic;
        for (var i = 0; i < dynamicData.length; i++) {
            if (dynamicData[i].rc_approval_rule_id == id) {
                return dynamicData[i].rule_value;
            }
        }
        return null; // return null if not found
    }

    $("#saveUptApproverBtn").click(function (e) {
        e.preventDefault();
        let approvalListValid = true;

        // rule criteria request status option me jo selected hoga vo option action upon approval me show nhi hoga start
        var transformedRuleCriteriaData =
            transformRuleCriteriaData(ruleCriteriaData);
        var ruleValue = getRuleValueById(transformedRuleCriteriaData, "131");
        $(".approverMessagesNextStatus option").each(function () {
            if ($(this).val() == ruleValue) {
                $(this).remove();
            }
        });
        $("#saveUptApproverBtn").attr("disabled", true);
        // Approval New version start
        const form = document.getElementById("approvalForm");
        const approvalFlowRadio = document.querySelector(
            'input[name="level"]:checked'
        );
        const approvalFlow = approvalFlowRadio ? approvalFlowRadio.value : null;
        if (!approvalFlow) {
            approvalListValid = false;
            showSwal("error", "Approval Flow is required");
            return;
        }
        const approvalFormData = {
            approvalFlow: approvalFlow,
            approvalData: {},
            deletedApproverIds: deletedApproverIds,
        };

        const approvalList = form.querySelectorAll(".approver-list-class");
        approvalList.forEach((approverRow) => {
            // Retrieve the data-index attribute
            const approvalRowDataIndex = approverRow.getAttribute("data-index");
            const selectRows = approverRow.querySelectorAll(".select-row");

            let keyValue,
                approvalOrder,
                approvalOrderElement,
                departmentSelectElement,
                departmentSelectValue;

            if (approvalFlow === "business") {
                keyValue = "business";
                approvalOrderElement = document.querySelector(
                    'input[name="approvalOption_business"]:checked'
                );
                approvalOrder = approvalOrderElement
                    ? approvalOrderElement.value
                    : null;
            } else {
                // Dynamically select the department value based on data-index
                departmentSelectElement = document.getElementById(
                    `departmentSelectId_${approvalRowDataIndex}`
                );
                keyValue = departmentSelectElement
                    ? departmentSelectElement.value
                    : null;
                departmentSelectValue = keyValue;
                if(!departmentSelectValue){
                    showSwal("error", "Department is required");
                    approvalListValid = false;
                    return false;
                }
                approvalOrderElement = document.querySelector(
                    `input[name="approvalOption_${approvalRowDataIndex}"]:checked`
                );
                approvalOrder = approvalOrderElement
                    ? approvalOrderElement.value
                    : null;
            }

            if (!approvalOrder) {
                showSwal("error", "Approval Order is required");
                approvalListValid = false;
                return false;
            }

            const approvalData = {
                approvalRows: [],
                approvalOrder: approvalOrder,
                departmentSelectValue: departmentSelectValue,
            };
            const seenCombinations = new Set(); // Track unique role-employee combinations

            selectRows.forEach((row) => {
                // Retrieve values from the row
                const primaryKey =
                    row.querySelector(".primaryKeyClass")?.value || null;
                const rowRoleElement = row.querySelector(".roleSelectClass");
                const rowRole = rowRoleElement?.value || null;
                const employeeElement = row.querySelector(
                    ".employeeSelectClass"
                );
                const employee = employeeElement?.value || null;
                const nextStatusElement = row.querySelector(
                    ".nextStatusSelectClass"
                );
                const nextStatus = nextStatusElement?.value || null;
                const approverMessageElement = row.querySelector(
                    ".approverMessageInputClass"
                );
                const approverMessage = approverMessageElement?.value || null;

                // Perform validations
                if (!rowRole) {
                    showSwal("error", "Role field is required");
                    rowRoleElement?.focus(); // Autofocus the invalid input
                    approvalListValid = false;
                    return false; // Exit this iteration
                }
                if (!employee) {
                    showSwal("error", "Employee field is required");
                    employeeElement?.focus(); // Autofocus the invalid input
                    approvalListValid = false;
                    return false; // Exit this iteration
                }
                if (!nextStatus) {
                    showSwal("error", "Next Status field is required");
                    nextStatusElement?.focus(); // Autofocus the invalid input
                    approvalListValid = false;
                    return false; // Exit this iteration
                }
                if (!approverMessage) {
                    showSwal("error", "Approval Message field is required");
                    approverMessageElement?.focus(); // Autofocus the invalid input
                    approvalListValid = false;
                    return false; // Exit this iteration
                }

                // Check for duplicate role-employee combination
                const combinationKey = `${rowRole}-${employee}`;
                if (seenCombinations.has(combinationKey)) {
                    showSwal(
                        "error",
                        "Duplicate Role and Employee combination found"
                    );
                    rowRoleElement?.focus(); // Autofocus the duplicate input
                    approvalListValid = false;
                    return false; // Exit this iteration
                }
                seenCombinations.add(combinationKey);

                // Add valid row data to approvalRows
                const rowData = {
                    primaryKey: primaryKey,
                    role: rowRole,
                    employee: employee,
                    nextstatus: nextStatus,
                    approvermessage: approverMessage,
                };

                approvalData.approvalRows.push(rowData);
            });

            // Directly assign approvalData to the key without nesting it in an array
            approvalFormData.approvalData[keyValue] = approvalData;
        });
        if (!approvalListValid) {
            // showSwal("error", 'Approval Order is required');
            // approvalListValid =false;
            return false;
        } else {
            // Assign to approverDataUpdatedVersion if needed
            approverDataUpdatedVersion = approvalFormData;
            var module_id = $("#approvalModuleId").val();
            let approval_type = $("#module").val().trim();

            fetch(
                `/admin/settings/tada-settings/save-approval-setting?module_id=${module_id}&approval_type=${approval_type}&approverDataUpdatedVersion=${encodeURIComponent(
                    approverDataUpdatedVersion
                )}&POST_TYPE=CHECK_PENDING_APPROVAL`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken, // Include the CSRF token in the header
                    },
                    body: JSON.stringify({
                        // Add any data you need to send in the body (if required)
                        approverDataUpdatedVersion: approverDataUpdatedVersion,
                    }),
                }
            )
                .then((response) => response.json())
                .then((data) => {
                    if (data.status_code == 1) {
                        showSwal("error", data.status_text);
                        $("#saveUptApproverBtn").attr("disabled", false);
                        return false;
                    } else if (data.status_code == 0) {
                        document.getElementById(
                            "saveUptApproverBtn"
                        ).disabled = false;
                        switchTab(
                            "#approver-setting",
                            "#action-upon-reject",
                            3
                        );
                    } else {
                        showSwal("error", "Error Saving Approval Setting");
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    showSwal("error", "Something went wrong");
                });
        }
    });

    $(`#nextStatus`).on("change", function () {
        var selectedOptionName = $(this).find("option:selected").text();
        approverData.forEach(function (item, index) {
            var concatenatedValue =
                item.role_name.trim() + " - " + selectedOptionName.trim();
            $(`#approver_message_${index}`).val(concatenatedValue);
        });
    });

    $(document).on("change", "#rule", function (param) {
        if ($("#rule").val() != "") {
            $.ajax({
                url:
                    baseUrl +
                    "/admin/settings/tada-settings/get-approval-setting",
                method: "get",
                data: {
                    ruleId: $("#rule").val(),
                    REQUEST_TYPE: "RULE_CONDITION",
                },
                dataType: "json",
                beforeSend: function () {},
                success: function (data) {
                    if (data.status_code) {
                        $("#rule_condition").html("");
                        var defaultOption = $("<option>")
                            .val("")
                            .text("Select Condition")
                            .attr("selected", true);
                        $("#rule_condition").append(defaultOption);
                        data.result.forEach(function (element) {
                            var option = $("<option>")
                                .val(element.m_id)
                                .text(element.m_name)
                                .attr("data-type", element.m_type);
                            $("#rule_condition").append(option);
                        });
                    } else {
                        $("#rule_condition").html("");
                    }
                },
                error: function (xhr, status, error) {
                    showSwal("error", error);
                },
            });
        }
    });

    $(document).on("change", "#rule_condition", function (param) {
        if ($("#rule_condition").val() != "") {
            var inputType = $(this).find("option:selected").data("type");
            if (inputType == "input") {
                $("#rule_value_type").val("custom");
                $("#ruleValuDiv").html("");
                var ruleValuDiv =
                    '<label class="form-label">Rule Value:</label>' +
                    '<input name="rule_value" class="form-control custom-select" id="rule_value" data-placeholder="Rule Value">';
                $("#ruleValuDiv").html(ruleValuDiv);
            } else {
                $.ajax({
                    url:
                        baseUrl +
                        "/admin/settings/tada-settings/get-approval-setting",
                    method: "get",
                    data: {
                        rule_condition_id: $("#rule_condition").val(),
                        REQUEST_TYPE: "APPROVAL_STATUS",
                    },
                    dataType: "json",
                    beforeSend: function () {},
                    success: function (data) {
                        $("#rule_value_type").val("master");
                        if (data.status_code) {
                            $("#ruleValuDiv").html("");

                            var ruleValuDiv =
                                '<label class="form-label">Select Value:</label>' +
                                '<select name="rule_value" class="form-control custom-select" id="rule_value" data-placeholder="Select Value">' +
                                '<option value="" selected>Select Value</option>';

                            data.result.forEach(function (element) {
                                var option = $("<option>")
                                    .val(element.m_id)
                                    .text(element.m_name);
                                ruleValuDiv += option.prop("outerHTML");
                            });
                            ruleValuDiv += "</select>";

                            $("#ruleValuDiv").html(ruleValuDiv);
                        } else {
                            $("#ruleValuDiv").html("");
                        }
                    },
                    error: function (xhr, status, error) {
                        showSwal("error", error);
                    },
                });
            }
        }
    });

    $(document).on("change", ".approverRole", function (param) {
        var roleId = $(this).val();
        var row = $(this).data("row");
        var selectedEmpId = $(this).data("emp-id");
        $.ajax({
            url: baseUrl + "/admin/settings/tada-settings/get-approval-setting",
            method: "get",
            data: {
                roleId: roleId,
                REQUEST_TYPE: "GET_EMP_BY_ROLEID",
            },
            dataType: "json",
            beforeSend: function () {},
            success: function (data) {
                if (data.status_code == 1) {
                    $("#approver_name_" + row).empty();

                    data.result.forEach(function (emp) {
                        var option = $("<option>")
                            .val(emp.emp_id)
                            .text(
                                (emp.emp_fname != null ? emp.emp_fname : "") +
                                    (emp.emp_mname != null
                                        ? " " + emp.emp_mname
                                        : "") +
                                    (emp.emp_lname != null
                                        ? " " + emp.emp_lname
                                        : "")
                            );
                        if (emp.emp_id == selectedEmpId) {
                            option.prop("selected", true); // Select the option if emp_id matches selectedEmpId
                        }
                        $("#approver_name_" + row).append(option);
                    });
                } else {
                    $("#approver_name_" + row).html("");
                }
            },
            error: function (xhr, status, error) {
                showSwal("error", error);
            },
        });
    });

    // This is Rule Criteria  Script Section Start
    function getAvailableOptions() {
        var selectedValues = [];
        $(".rule-apply-on").each(function () {
            var value = $(this).val();
            if (value) {
                selectedValues.push(value);
            }
        });
        var options = "";
        $(window.rulesOptions).each(function () {
            var key = $(this).val();
            var value = $(this).text();
            if (!selectedValues.includes(key.toString())) {
                options += `<option value="${key}">${value}</option>`;
            } else {
                options += `<option value="${key}" class="disabled" disabled>${value}</option>`;
            }
        });
        return options;
    }

    if (r === 0) {
        addRuleFields();
    }
    // For add
    $("#addRule").click(function () {
        addRuleFields();
    });

    // Check if r is 0 and automatically trigger the click event
    function addRuleFields() {
        var availableOptions = getAvailableOptions();
        var newRow = `
               <tr data-row-id="${r}">
                   <td>
                       <select name="dynamic[${r}][rc_approval_rule_id]" class="form-control form-select rule-apply-on" >
                           <option value="">Select Apply On: </option>
                       ${availableOptions}
                       </select>
                       <div class="invalid-feedback">This field is required.</div>
                   </td>
                   <td>
                       <select name="dynamic[${r}][rc_rule_condition_id]" data-row-id="${r}" class="form-control form-select rule-condition" >
                           <option value="">Select Condition: </option>
                       </select>
                       <div class="invalid-feedback">This field is required.</div>
                   </td>
                    <td class="dynamic-element-cell">
                       <div class="invalid-feedback">This field is required.</div>
                   </td>
                   <td class="text-end" hidden>
                       <button type="button" class="btn btn-danger remove-tr btn-sm hidden" data-row-pid="null"><i class="feather feather-trash"></i></button>
                      <input type="hidden" name="dynamic[${r}][_delete]" value="0" class="delete-marker" />
                   <input type="hidden" name="dynamic[${r}][_index]" value="${r}" />
                   </td>
               </tr>`;
        $("#dynamicTable tbody").append(newRow);
        r++;
    }

    // For Remove
    $(document).on("click", ".remove-tr", function () {
        var rowIndex = $(this).data("row-id");
        var rowPId = $(this).data("row-pid");

        if ($("#dynamicTable tbody tr").length === 1) {
            showSwal(
                "warning",
                "You can't delete the last row. At least one row is required."
            );
            return; // Exit the function to prevent deletion
        }

        // Store the ID in the array if not already stored
        if (rowPId && !ruleCriteriaRemovedRowIds.includes(rowPId)) {
            ruleCriteriaRemovedRowIds.push(rowPId);
        }
        $('input[name="dynamic[' + rowIndex + '][_delete]"]').val(1);
        var $row = $(this).closest("tr");
        var selectedValue = $row.find("select.rule-apply-on").val();
        $(this).closest("tr").remove();
        // reindexRows();
        // After removing a row, re-enable the option that was selected in the removed row
        if (selectedValue) {
            $(".rule-apply-on").each(function () {
                var $select = $(this);
                $select
                    .find(`option[value="${selectedValue}"]`)
                    .prop("disabled", false);
            });
        }
        var selectedValues = [];
        $(".rule-apply-on").each(function () {
            var value = $(this).val();
            if (value) {
                selectedValues.push(value);
            }
        });
        $(".rule-apply-on").each(function () {
            var $select = $(this);
            var currentVal = $select.val();

            // Re-enable all options
            $select.find("option").prop("disabled", false);

            // Disable already selected values
            selectedValues.forEach(function (value) {
                if (value !== currentVal) {
                    $select
                        .find(`option[value="${value}"]`)
                        .prop("disabled", true);
                }
            });
        });
    });

    // For Condition Change
    $(document).on("change", ".rule-condition", function () {
        var $row = $(this).closest("tr");
        var ruleCondtionId = $(this).val();
        var inputType = $(this).find("option:selected").data("type");
        var rowIndex = $(this).data("row-id");
        if (inputType == "input") {
            $row.find(".dynamic-element-cell").empty(); // Empty instead of remove
            var inputElement = `<input type="number" min="0" name="dynamic[${rowIndex}][rule_value]" class="form-control dynamic-element" id="rule_value" data-placeholder="Rule Value" /> <input type="hidden"
                                                               name="dynamic[${rowIndex}][rule_value_type]"
                                                               value="custom" class="rule_value_type">`;
            $row.find(".dynamic-element-cell").append(inputElement);
        } else if (inputType == "select") {
            $.ajax({
                url: window.approvalSettingUrl,
                type: "GET",
                data: {
                    rule_condition_id: ruleCondtionId,
                    REQUEST_TYPE: "APPROVAL_STATUS",
                },
                dataType: "json",
                beforeSend: function () {
                    // You can add loading indicators or disable elements here
                },
                success: function (data) {
                    if (data.status_code) {
                        $row.find(".dynamic-element-cell").empty(); // Empty instead of remove

                        var options =
                            '<option value="">Select Rule Value:</option>';
                        data.result.forEach(function (item) {
                            options += `<option value="${item.m_id}">${item.m_name}</option>`;
                        });

                        var selectElement = `<select name="dynamic[${rowIndex}][rule_value]" class="form-control dynamic-element" >${options}</select> <input type="hidden"
                                                               name="dynamic[${rowIndex}][rule_value_type]"
                                                               value="master" class="rule_value_type">`;
                        $row.find(".dynamic-element-cell").append(
                            selectElement
                        );
                    } else {
                        $row.find(".dynamic-element-cell").empty(); // Clear content if no data
                    }
                },
                error: function (xhr, status, error) {
                    showSwal("error", error);
                },
            });
        }
    });

    // For apply on change
    $(document).on("change", ".rule-apply-on", function () {
        var $row = $(this).closest("tr");
        var applyOn = $(this).val();

        $.ajax({
            url: window.approvalSettingUrl,
            type: "GET",
            method: "get",
            data: {
                ruleId: applyOn,
                REQUEST_TYPE: "RULE_CONDITION",
            },
            dataType: "json",
            beforeSend: function () {},
            success: function (response) {
                if (response.status_code) {
                    var options = '<option value="">Select Condition:</option>';
                    response.result.forEach(function (item) {
                        options += `<option value="${item.m_id}" data-type="${item.m_type}">${item.m_name}</option>`;
                    });
                    $row.find('select[name*="rc_rule_condition_id"]').html(
                        options
                    );
                } else {
                    $row.find('select[name*="rc_rule_condition_id"]').html("");
                }
            },
            error: function (xhr, status, error) {
                showSwal("error", error);
            },
        });
    });

    $(document).on("click", ".rule-apply-on", function () {
        var $selectedOption = $(this);
        var selectedValue = $selectedOption.val();
        $(".rule-apply-on")
            .not($selectedOption)
            .each(function () {
                var $otherOption = $(this);
                // Enable the selected option in other select boxes
                $otherOption
                    .find(`option[value="${selectedValue}"]`)
                    .prop("disabled", true);
                // If the other option had the same value selected, reset it to the default option
                if ($otherOption.val() === selectedValue) {
                    $otherOption.val("");
                }
            });
    });

    for (var j = 1; j <= rulesDataCount; j++) {
        $(".rule-apply-on").trigger("click");
    }

    function resetSequence(className) {
        $("." + className).each(function (index) {
            // Update the text with the new sequence number
            $(this).text(`Sr No. ${index + 1}`);
        });
    }

    $(document).on("click", "#saveUptActionUponRejection", function () {
        var module_id = $("#approvalModuleId").val();
        var stringArray = $("#approval_notify").val();

        if (!stringArray.length) {
            showSwal("warning", "Notify field is required");
            return false;
        }

        var numberArray = $(stringArray)
            .map(function () {
                return Number(this);
            })
            .get();
        $("#saveUptActionUponRejection").prop("disabled", true);
        if (moduleSettingsData && ruleCriteriaData) {
            // if (moduleSettingsData && ruleCriteriaData && approverData) {
            var transformedRuleCriteriaData =
                transformRuleCriteriaData(ruleCriteriaData);
            ajaxRequest(
                baseUrl + "/admin/settings/tada-settings/save-approval-setting",
                "post",
                {
                    POST_TYPE: "FINAL_SUBMIT",
                    module_id,
                    moduleData: moduleSettingsData,
                    ruleCriteriaData: transformedRuleCriteriaData,
                    ruleCriteriaRemovedRowIds: ruleCriteriaRemovedRowIds,
                    approverData: approverData, // Include approver data
                    approvalNextStatusSelectedId: approvalNextStatusSelectedId,
                    actionUponApproval: actionUponApproval,
                    aur_group_ids: numberArray,
                    approverDataUpdatedVersion: approverDataUpdatedVersion,
                },
                function () {
                    $("#saveUptActionUponRejection").attr(
                        "disabled",
                        "disabled"
                    );
                },
                function (data) {
                    // resetForm(
                    //     "#actioUponRejectionFrm",
                    //     "#saveUptActionUponRejection"
                    // );
                    if (data.status_code == 1) {
                        showSwal("success", data.status_text);
                        window.location.href =
                            baseUrl +
                            "/admin/settings/tada-settings/approval-list";
                    } else {
                        showSwal("warning", data.status_text);
                        $("#saveUptActionUponRejection").prop(
                            "disabled",
                            false
                        );
                    }
                },
                function (xhr, status, error) {
                    showSwal("error", error);
                }
            );
        } else {
            showSwal(
                "error",
                "Validation, rule criteria, or approver data is missing."
            );
        }
    });
});

//  who should approved section Start
// Function to handle the click on a radio button
function handleRadioClick(departmentIndex, option) {
    updateApprovalFields(departmentIndex, option);
}

// Function to toggle between Business and Department approval forms with Accordion
// function toggleApprovalForm() {
//     deletedKeyStore(1);

//     const level = document.querySelector('input[name="level"]:checked').value;
//     const contentContainer = document.getElementById("approval-content");

//     // Save current layout and values before clearing
//     saveCurrentLayout(level === "business" ? "department" : "business");

//     // Clear existing content
//     contentContainer.innerHTML = "";

//     if (level === "business") {
//         // Build the business layout with Accordion (theme style)
//         const accordionDiv = document.createElement("div");
//         accordionDiv.className = "accordion";
//         accordionDiv.id = "accordionBusiness"; // Accordion container ID

//         // Create Accordion item
//         const accordionItem = document.createElement("div");
//         accordionItem.className = "acc-card";

//         // Accordion Header
//         const accordionHeader = document.createElement("div");
//         accordionHeader.className = "acc-header";
//         accordionHeader.id = "businessAccordionHeader";
//         accordionHeader.setAttribute("role", "tab");

//         const accordionButton = document.createElement("h5");
//         accordionButton.className = "mb-0";
//         const accordionLink = document.createElement("a");
//         accordionLink.setAttribute("data-bs-toggle", "collapse");
//         accordionLink.setAttribute("href", "#businessContent");
//         accordionLink.setAttribute("aria-expanded", "true");
//         accordionLink.setAttribute("aria-controls", "businessContent");
//         accordionLink.textContent = "Business Approval";

//         accordionButton.appendChild(accordionLink);
//         accordionHeader.appendChild(accordionButton);
//         accordionItem.appendChild(accordionHeader);

//         // Accordion Body (content for Business approval)
//         const accordionBody = document.createElement("div");
//         accordionBody.id = "businessContent";
//         accordionBody.className = "collapse show";
//         accordionBody.setAttribute("aria-labelledby", "businessAccordionHeader");
//         accordionBody.setAttribute("data-bs-parent", "#accordionBusiness");

//         // Add business-specific content to the body
//         const radioDiv = createRadioOptions("business");
//         const approverDiv = document.createElement("div");
//         approverDiv.id = "approverList_business";
//         approverDiv.className = "approver-list-class";
//         approverDiv.setAttribute("data-index", "business");
//         approverDiv.setAttribute("data-oldordervalue", null);

//         accordionBody.appendChild(radioDiv);
//         accordionBody.appendChild(approverDiv);
//         accordionItem.appendChild(accordionBody);

//         // Append Accordion item to the Accordion container
//         accordionDiv.appendChild(accordionItem);
//         contentContainer.appendChild(accordionDiv);

//         // Restore saved layout and values if any
//         restoreLayoutAndValues("business");
//         deletedKeyStore(2);
//     } else if (level === "department") {
//         // Build department layout with Accordion (theme style)
//         const accordionDiv = document.createElement("div");
//         accordionDiv.className = "accordion";
//         accordionDiv.id = "accordionDepartment"; // Accordion container ID

//         Object.entries(departmentObj).forEach(([id, departmentName], index) => {
//             const accordionItem = document.createElement("div");
//             accordionItem.className = "acc-card";

//             // Accordion Header
//             const accordionHeader = document.createElement("div");
//             accordionHeader.className = "acc-header";
//             accordionHeader.id = `departmentAccordionHeader_${index}`;
//             accordionHeader.setAttribute("role", "tab");

//             const accordionButton = document.createElement("h5");
//             accordionButton.className = "mb-0";
//             const accordionLink = document.createElement("a");
//             accordionLink.setAttribute("data-bs-toggle", "collapse");
//             accordionLink.setAttribute("href", `#departmentContent_${index}`);
//             accordionLink.setAttribute("aria-expanded", "true");
//             accordionLink.setAttribute("aria-controls", `departmentContent_${index}`);
//             accordionLink.textContent = departmentName;

//             accordionButton.appendChild(accordionLink);
//             accordionHeader.appendChild(accordionButton);
//             accordionItem.appendChild(accordionHeader);

//             // Accordion Body (content for department approval)
//             const accordionBody = document.createElement("div");
//             accordionBody.id = `departmentContent_${index}`;
//             accordionBody.className = "collapse show";
//             accordionBody.setAttribute("aria-labelledby", `departmentAccordionHeader_${index}`);
//             accordionBody.setAttribute("data-bs-parent", "#accordionDepartment");

//             // Add department-specific content to the body
//             const departmentSelectDiv = createDepartmentSelect(departmentName, index, id);
//             const radioDiv = createRadioOptions(index);
//             const approverDiv = document.createElement("div");
//             approverDiv.id = `approverList_${index}`;
//             approverDiv.className = "approver-list-class";
//             approverDiv.setAttribute("data-index", index);
//             approverDiv.setAttribute("data-oldordervalue", null);

//             accordionBody.appendChild(departmentSelectDiv);
//             accordionBody.appendChild(radioDiv);
//             accordionBody.appendChild(approverDiv);
//             accordionItem.appendChild(accordionBody);

//             // Append Accordion item to the Accordion container
//             accordionDiv.appendChild(accordionItem);
//         });

//         // Append Accordion to content container
//         contentContainer.appendChild(accordionDiv);

//         // Restore saved layout and values if any
//         restoreLayoutAndValues("department");
//         deletedKeyStore(2);
//     }
// }


// function toggleApprovalForm() {
//     deletedKeyStore(1);

//     const level = document.querySelector('input[name="level"]:checked').value;
//     const contentContainer = document.getElementById("approval-content");

//     // Save current layout and values before clearing
//     saveCurrentLayout(level === "business" ? "department" : "business");

//     // Clear existing content
//     contentContainer.innerHTML = "";

//     if (level === "business") {
//         // Build the business layout with Accordion (theme style)
//         const accordionDiv = document.createElement("div");
//         accordionDiv.className = "accordion";
//         accordionDiv.id = "accordionBusiness"; // Accordion container ID

//         // Create Accordion item
//         const accordionItem = document.createElement("div");
//         accordionItem.className = "acc-card";

//         // Accordion Header
//         const accordionHeader = document.createElement("div");
//         accordionHeader.className = "acc-header";
//         accordionHeader.id = "businessAccordionHeader";
//         accordionHeader.setAttribute("role", "tab");

//         const accordionButton = document.createElement("h5");
//         accordionButton.className = "mb-0";
//         const accordionLink = document.createElement("a");
//         accordionLink.setAttribute("data-bs-toggle", "collapse");
//         accordionLink.setAttribute("href", "#businessContent");
//         accordionLink.setAttribute("aria-expanded", "true");
//         accordionLink.setAttribute("aria-controls", "businessContent");
//         accordionLink.textContent = "Business Approval";

//         accordionButton.appendChild(accordionLink);
//         accordionHeader.appendChild(accordionButton);
//         accordionItem.appendChild(accordionHeader);

//         // Accordion Body (content for Business approval)
//         const accordionBody = document.createElement("div");
//         accordionBody.id = "businessContent";
//         accordionBody.className = "acc-body show"; // Changed class here
//         accordionBody.setAttribute("aria-labelledby", "businessAccordionHeader");
//         accordionBody.setAttribute("data-bs-parent", "#accordionBusiness");

//         // Add business-specific content to the body
//         const radioDiv = createRadioOptions("business");
//         const approverDiv = document.createElement("div");
//         approverDiv.id = "approverList_business";
//         approverDiv.className = "approver-list-class";
//         approverDiv.setAttribute("data-index", "business");
//         approverDiv.setAttribute("data-oldordervalue", null);

//         accordionBody.appendChild(radioDiv);
//         accordionBody.appendChild(approverDiv);
//         accordionItem.appendChild(accordionBody);

//         // Append Accordion item to the Accordion container
//         accordionDiv.appendChild(accordionItem);
//         contentContainer.appendChild(accordionDiv);

//         // Restore saved layout and values if any
//         restoreLayoutAndValues("business");
//         deletedKeyStore(2);
//     } else if (level === "department") {
//         // Build department layout with Accordion (theme style)
//         const accordionDiv = document.createElement("div");
//         accordionDiv.className = "accordion";
//         accordionDiv.id = "accordionDepartment"; // Accordion container ID

//         Object.entries(departmentObj).forEach(([id, departmentName], index) => {
//             const accordionItem = document.createElement("div");
//             accordionItem.className = "acc-card";

//             // Accordion Header
//             const accordionHeader = document.createElement("div");
//             accordionHeader.className = "acc-header";
//             accordionHeader.id = `departmentAccordionHeader_${index}`;
//             accordionHeader.setAttribute("role", "tab");

//             const accordionButton = document.createElement("h5");
//             accordionButton.className = "mb-0";
//             const accordionLink = document.createElement("a");
//             accordionLink.setAttribute("data-bs-toggle", "collapse");
//             accordionLink.setAttribute("href", `#departmentContent_${index}`);
//             accordionLink.setAttribute("aria-expanded", "true");
//             accordionLink.setAttribute("aria-controls", `departmentContent_${index}`);
//             accordionLink.textContent = departmentName;

//             accordionButton.appendChild(accordionLink);
//             accordionHeader.appendChild(accordionButton);
//             accordionItem.appendChild(accordionHeader);

//             // Accordion Body (content for department approval)
//             const accordionBody = document.createElement("div");
//             accordionBody.id = `departmentContent_${index}`;
//             accordionBody.className = "acc-body show"; // Changed class here
//             accordionBody.setAttribute("aria-labelledby", `departmentAccordionHeader_${index}`);
//             accordionBody.setAttribute("data-bs-parent", "#accordionDepartment");

//             // Add department-specific content to the body
//             const departmentSelectDiv = createDepartmentSelect(departmentName, index, id);
//             const radioDiv = createRadioOptions(index);
//             const approverDiv = document.createElement("div");
//             approverDiv.id = `approverList_${index}`;
//             approverDiv.className = "approver-list-class";
//             approverDiv.setAttribute("data-index", index);
//             approverDiv.setAttribute("data-oldordervalue", null);

//             accordionBody.appendChild(departmentSelectDiv);
//             accordionBody.appendChild(radioDiv);
//             accordionBody.appendChild(approverDiv);
//             accordionItem.appendChild(accordionBody);

//             // Append Accordion item to the Accordion container
//             accordionDiv.appendChild(accordionItem);
//         });

//         // Append Accordion to content container
//         contentContainer.appendChild(accordionDiv);

//         // Restore saved layout and values if any
//         restoreLayoutAndValues("department");
//         deletedKeyStore(2);
//     }
// }

// function toggleApprovalForm() {
//     deletedKeyStore(1);

//     const level = document.querySelector('input[name="level"]:checked').value;
//     const contentContainer = document.getElementById("approval-content");

//     // Save current layout and values before clearing
//     saveCurrentLayout(level === "business" ? "department" : "business");

//     // Clear existing content
//     contentContainer.innerHTML = "";

//     if (level === "business") {
//         buildBusinessAccordion(contentContainer);
//     } else if (level === "department") {
//         buildDepartmentAccordion(contentContainer);
//     }

//     // Restore saved layout and values
//     restoreLayoutAndValues(level);
//     deletedKeyStore(2);
// }

// // Function to build business-level accordion
// function buildBusinessAccordion(container) {
//     const accordionDiv = document.createElement("div");
//     accordionDiv.className = "accordion";
//     accordionDiv.id = "accordionBusiness";

//     const accordionItem = document.createElement("div");
//     accordionItem.className = "accordion-item"; // Correct Bootstrap class

//     const accordionHeader = document.createElement("div");
//     accordionHeader.className = "accordion-header";
//     accordionHeader.id = "businessAccordionHeader";

//     const accordionButton = document.createElement("button");
//     accordionButton.className = "accordion-button";
//     accordionButton.type = "button";
//     accordionButton.setAttribute("data-bs-toggle", "collapse");
//     accordionButton.setAttribute("data-bs-target", "#businessContent");
//     accordionButton.setAttribute("aria-expanded", "true");
//     accordionButton.setAttribute("aria-controls", "businessContent");
//     accordionButton.textContent = "Business Approval";

//     accordionHeader.appendChild(accordionButton);
//     accordionItem.appendChild(accordionHeader);

//     const accordionBody = document.createElement("div");
//     accordionBody.id = "businessContent";
//     accordionBody.className = "accordion-collapse collapse show"; // Default open
//     accordionBody.setAttribute("aria-labelledby", "businessAccordionHeader");

//     // Add business-specific content
//     const radioDiv = createRadioOptions("business");
//     const approverDiv = createApproverListDiv("business");

//     accordionBody.appendChild(radioDiv);
//     accordionBody.appendChild(approverDiv);
//     accordionItem.appendChild(accordionBody);
//     accordionDiv.appendChild(accordionItem);
//     container.appendChild(accordionDiv);
// }

// // Function to build department-level accordion
// function buildDepartmentAccordion(container) {
//     const accordionDiv = document.createElement("div");
//     accordionDiv.className = "accordion";
//     accordionDiv.id = "accordionDepartment";

//     Object.entries(departmentObj).forEach(([id, departmentName], index) => {
//         const accordionItem = document.createElement("div");
//         accordionItem.className = "accordion-item";

//         const accordionHeader = document.createElement("h2");
//         accordionHeader.className = "accordion-header";
//         accordionHeader.id = `departmentAccordionHeader_${index}`;

//         const accordionButton = document.createElement("button");
//         accordionButton.className = `accordion-button ${index !== 0 ? 'collapsed' : ''}`;
//         accordionButton.type = "button";
//         accordionButton.setAttribute("data-bs-toggle", "collapse");
//         accordionButton.setAttribute("data-bs-target", `#departmentContent_${index}`);
//         accordionButton.setAttribute("aria-expanded", index === 0 ? "true" : "false");
//         accordionButton.setAttribute("aria-controls", `departmentContent_${index}`);
//         accordionButton.textContent = departmentName;

//         accordionHeader.appendChild(accordionButton);
//         accordionItem.appendChild(accordionHeader);

//         const accordionBody = document.createElement("div");
//         accordionBody.id = `departmentContent_${index}`;
//         accordionBody.className = `accordion-collapse collapse ${index === 0 ? 'show' : ''}`;
//         accordionBody.setAttribute("aria-labelledby", `departmentAccordionHeader_${index}`);

//         const departmentSelectDiv = createDepartmentSelect(departmentName, index, id);
//         const radioDiv = createRadioOptions(index);
//         const approverDiv = createApproverListDiv(index);

//         accordionBody.appendChild(departmentSelectDiv);
//         accordionBody.appendChild(radioDiv);
//         accordionBody.appendChild(approverDiv);
//         accordionItem.appendChild(accordionBody);
//         accordionDiv.appendChild(accordionItem);
//     });

//     container.appendChild(accordionDiv);
// }

function toggleApprovalForm() {
    deletedKeyStore(1);

    const level = document.querySelector('input[name="level"]:checked').value;
    const contentContainer = document.getElementById("approval-content");

    // Save current layout and values before clearing
    saveCurrentLayout(level === "business" ? "department" : "business");

    // Clear existing content
    contentContainer.innerHTML = "";

    if (level === "business") {
        buildBusinessAccordion(contentContainer);
    } else if (level === "department") {
        buildDepartmentAccordion(contentContainer);
    }

    // Restore saved layout and values
    restoreLayoutAndValues(level);
    deletedKeyStore(2);
}

// Build Business Accordion Structure
function buildBusinessAccordion(container) {
    const accordionDiv = document.createElement("div");
    accordionDiv.className = "accordion";
    accordionDiv.id = "accordion";

    const accCard = document.createElement("div");
    accCard.className = "acc-card";

    const accHeader = document.createElement("div");
    accHeader.className = "acc-header";
    accHeader.id = "headingBusiness";

    const headerLink = document.createElement("a");
    headerLink.setAttribute("href", "#collapseBusiness");
    headerLink.setAttribute("data-bs-toggle", "collapse");
    headerLink.setAttribute("aria-expanded", "true");
    headerLink.setAttribute("aria-controls", "collapseBusiness");
    headerLink.textContent = "Business Approval";

    accHeader.appendChild(headerLink);
    accCard.appendChild(accHeader);

    const collapseDiv = document.createElement("div");
    collapseDiv.className = "collapse show";
    collapseDiv.id = "collapseBusiness";
    collapseDiv.setAttribute("aria-labelledby", "headingBusiness");
    collapseDiv.setAttribute("data-parent", "#accordion");

    const accBody = document.createElement("div");
    accBody.className = "acc-body";

    // Add business-specific content
    accBody.appendChild(createRadioOptions("business"));
    accBody.appendChild(createApproverListDiv("business"));

    collapseDiv.appendChild(accBody);
    accCard.appendChild(collapseDiv);
    accordionDiv.appendChild(accCard);
    container.appendChild(accordionDiv);
}

// Build Department Accordion Structure
function buildDepartmentAccordion(container) {
    const accordionDiv = document.createElement("div");
    accordionDiv.className = "accordion";
    accordionDiv.id = "accordion";

    Object.entries(departmentObj).forEach(([id, departmentName], index) => {
        const accCard = document.createElement("div");
        accCard.className = "acc-card";

        const accHeader = document.createElement("div");
        accHeader.className = "acc-header";
        accHeader.id = `headingDepartment_${index}`;

        const headerLink = document.createElement("a");
        headerLink.setAttribute("href", `#collapseDepartment_${index}`);
        headerLink.setAttribute("data-bs-toggle", "collapse");
        headerLink.className = index === 0 ? "" : "collapsed";
        headerLink.setAttribute("aria-expanded", index === 0 ? "true" : "false");
        headerLink.setAttribute("aria-controls", `collapseDepartment_${index}`);
        headerLink.textContent = departmentName;

        accHeader.appendChild(headerLink);
        accCard.appendChild(accHeader);

        const collapseDiv = document.createElement("div");
        collapseDiv.className = `collapse ${index === 0 ? "show" : ""}`;
        collapseDiv.id = `collapseDepartment_${index}`;
        collapseDiv.setAttribute("aria-labelledby", `headingDepartment_${index}`);
        collapseDiv.setAttribute("data-parent", "#accordion");

        const accBody = document.createElement("div");
        accBody.className = "acc-body";

        accBody.appendChild(createDepartmentSelect(departmentName, index, id));
        accBody.appendChild(createRadioOptions(index));
        accBody.appendChild(createApproverListDiv(index));

        collapseDiv.appendChild(accBody);
        accCard.appendChild(collapseDiv);
        accordionDiv.appendChild(accCard);
    });

    container.appendChild(accordionDiv);
}

// Helper: Approver List
function createApproverListDiv(index) {
    const approverDiv = document.createElement("div");
    approverDiv.className = "approver-list-class";
    approverDiv.id = `approverList_${index}`;
    approverDiv.setAttribute("data-index", index);
    approverDiv.setAttribute("data-oldordervalue", "null");
    return approverDiv;
}

// Helper function to create approver list div
function createApproverListDiv(index) {
    const approverDiv = document.createElement("div");
    approverDiv.id = `approverList_${index}`;
    approverDiv.className = "approver-list-class";
    approverDiv.setAttribute("data-index", index);
    approverDiv.setAttribute("data-oldordervalue", null);
    return approverDiv;
}




// store  business and department layout
function saveCurrentLayout(level) {
    const contentContainer = document.getElementById("approval-content");

    // Save layout HTML
    savedData[level].layout = contentContainer.innerHTML;

    // Save values of all input fields within the container
    const valuesToSave = {};
    contentContainer
        .querySelectorAll("input, select, textarea")
        .forEach((element) => {
            valuesToSave[element.id] = element.value;
        });

    // Save radio button selections
    const radioSelections = {};
    contentContainer
        .querySelectorAll("input[type='radio']:checked")
        .forEach((radio) => {
            radioSelections[radio.name] = radio.value;
        });
    savedData[level].values = valuesToSave;
    savedData[level].radioSelections = radioSelections;
}

// restore business and department layout
function restoreLayoutAndValues(level) {
    const contentContainer = document.getElementById("approval-content");

    // Restore layout HTML if saved
    if (savedData[level].layout) {
        contentContainer.innerHTML = savedData[level].layout;
    }

    // Restore radio button selections
    const radioSelections = savedData[level].radioSelections;
    Object.keys(radioSelections).forEach((name) => {
        const radio = contentContainer.querySelector(
            `input[name="${name}"][value="${radioSelections[name]}"]`
        );
        if (radio) {
            radio.checked = true;
            radio.dispatchEvent(new Event("change")); // Trigger any change event listeners if necessary
        }
    });

    // Restore values for each saved input field
    const valuesToRestore = savedData[level].values;
    Object.keys(valuesToRestore).forEach((id) => {
        const element = document.getElementById(id);
        if (element) {
            element.value = valuesToRestore[id];
            // element.dispatchEvent(new Event('change')); // Trigger any change event listeners if necessary
        }
    });
}

// Function to create radio options for approval flow (Single, And, Anyone)
function createRadioOptions(departmentIndex) {
    // Create the main container
    const containerDiv = document.createElement("div");
    containerDiv.className = "row align-items-center mb-3"; // Bootstrap row for alignment

    // Label for Approval Order
    const approvalOrderLabelDiv = document.createElement("div");
    approvalOrderLabelDiv.className = "col-md-2"; // Adjust width as needed
    const approvalOrderLabel = document.createElement("label");
    approvalOrderLabel.className = "form-label";
    approvalOrderLabel.innerHTML =
        "Approval Order <span class='text-danger'>*</span>";

    approvalOrderLabelDiv.appendChild(approvalOrderLabel);

    // Div to contain the radio buttons in the same row
    const radioDiv = document.createElement("div");
    radioDiv.className = "col-md-10 d-flex"; // Remaining width for radio buttons

    // Function to create a radio button with label
    const createRadioOption = (id, name, value, labelText) => {
        const radioContainer = document.createElement("div");
        radioContainer.className = "form-check form-check-inline"; // Align in a row

        const radioInput = document.createElement("input");
        radioInput.type = "radio";
        radioInput.className = "form-check-input";
        radioInput.name = name;
        radioInput.id = id;
        radioInput.value = value;

        const radioLabel = document.createElement("label");
        radioLabel.className = "form-check-label";
        radioLabel.setAttribute("for", id);
        radioLabel.textContent = labelText;

        radioContainer.appendChild(radioInput);
        radioContainer.appendChild(radioLabel);
        return {
            radioContainer,
            radioInput,
        };
    };

    // Create each radio button with label
    const { radioContainer: singleContainer, radioInput: radioSingle } =
        createRadioOption(
            `single_${departmentIndex}`,
            `approvalOption_${departmentIndex}`,
            "single",
            "Single"
        );
    const { radioContainer: andContainer, radioInput: radioAnd } =
        createRadioOption(
            `and_${departmentIndex}`,
            `approvalOption_${departmentIndex}`,
            "and",
            "And"
        );
    const { radioContainer: anyoneContainer, radioInput: radioAnyone } =
        createRadioOption(
            `anyone_${departmentIndex}`,
            `approvalOption_${departmentIndex}`,
            "anyone",
            "Anyone"
        );

    // Append each radio option to the radio div
    radioDiv.appendChild(singleContainer);
    radioDiv.appendChild(andContainer);
    radioDiv.appendChild(anyoneContainer);

    // Setting onclick attribute using setAttribute
    radioSingle.setAttribute(
        "onchange",
        `updateApprovalFields('${departmentIndex}', 'single')`
    );
    radioAnd.setAttribute(
        "onchange",
        `updateApprovalFields('${departmentIndex}', 'and')`
    );
    radioAnyone.setAttribute(
        "onchange",
        `updateApprovalFields('${departmentIndex}', 'anyone')`
    );

    // Append the label and radio divs to the main container
    containerDiv.appendChild(approvalOrderLabelDiv);
    containerDiv.appendChild(radioDiv);

    return containerDiv;
}

// Function to store delete primary id
function deletedKeyStore(condition, primaryKeyElementsArg = null) {
    let primaryKeyElements = document.getElementsByClassName("primaryKeyClass");

    if (condition === 1) {
        for (let element of primaryKeyElements) {
            let value = element.value;

            // Check if the element has a value and if it's not already in the array
            if (value && !deletedApproverIds.includes(value)) {
                deletedApproverIds.push(value);
            }
        }
    } else if (condition === 2) {
        for (let element of primaryKeyElements) {
            let value = element.value;

            // Check if the element value exists in the array and remove it
            if (value && deletedApproverIds.includes(value)) {
                const index = deletedApproverIds.indexOf(value);
                if (index > -1) {
                    deletedApproverIds.splice(index, 1); // Remove the value from the array
                }
            }
        }
    } else if (condition === 3) {
        for (let element of primaryKeyElementsArg) {
            let value = element.value;

            // Check if the element has a value and if it's not already in the array
            if (value && !deletedApproverIds.includes(value)) {
                deletedApproverIds.push(value);
            }
        }
    } else if (condition === 4) {
        for (let element of primaryKeyElementsArg) {
            let value = element.value;

            // Check if the element value exists in the array and remove it
            if (value && deletedApproverIds.includes(value)) {
                const index = deletedApproverIds.indexOf(value);
                if (index > -1) {
                    deletedApproverIds.splice(index, 1); // Remove the value from the array
                }
            }
        }
    } else if (condition == 5) {
        // Check if the element has a value and if it's not already in the array
        if (
            primaryKeyElementsArg &&
            !deletedApproverIds.includes(primaryKeyElementsArg)
        ) {
            deletedApproverIds.push(primaryKeyElementsArg);
        }
    }
}

// Function to create row
function createRow(index, isLastRow, departmentIndex, option) {
    const currentRowId = uniqueRowCounter++; // Unique ID for each row

    // Create container div for the row
    const selectRow = document.createElement("div");
    selectRow.className = "select-row mb-3";
    selectRow.id = `row-id-${currentRowId}`;
    selectRow.setAttribute("data-row-id", currentRowId);

    const rowDiv = document.createElement("div");
    rowDiv.className = "row";

    // Serial Number Column (only if option is "and")
    if (option === "and") {
        const serialCol = document.createElement("div");
        serialCol.className = "col-md-1 serial-col";
        serialCol.innerHTML = `
    <label>S.No</label>
    <input type="text" class="form-control serial-number" value="${index}" readonly>`;
        rowDiv.appendChild(serialCol);
    }

    // Primary Key Column
    const primaryKeyCol = document.createElement("div");
    primaryKeyCol.className = "col-md hidden d-none";
    primaryKeyCol.innerHTML = `<label>Primary Key </label>`;

    const primaryKeyInput = document.createElement("input");
    primaryKeyInput.type = "text";
    primaryKeyInput.className = `form-control primaryKeyClass primaryKeyClass_${departmentIndex} hidden d-none`;
    primaryKeyInput.id = `primaryKeyInput_${departmentIndex}_${currentRowId}`;
    primaryKeyInput.readOnly = true;

    primaryKeyCol.appendChild(primaryKeyInput);
    rowDiv.appendChild(primaryKeyCol);

    // Role Select Column
    const roleCol = document.createElement("div");
    roleCol.className = "col-md";
    roleCol.innerHTML =
        "<label>Select Role</label> <span class='text-danger'>*</span>";

    const roleSelect = document.createElement("select");
    roleSelect.className = `form-select custom-heighlight roleSelectClass roleSelectClass_${departmentIndex}`;
    roleSelect.id = `roleSelect_${departmentIndex}_${currentRowId}`;
     var roleoldvalue = roleSelect.getAttribute('data-oldvalue');


    const placeholderOption = document.createElement("option");
    placeholderOption.textContent = "Select Role";
    placeholderOption.value = "";
    placeholderOption.disabled = true;
    placeholderOption.selected = true;
    roleSelect.appendChild(placeholderOption);

    if (departmentIndex === "business") {
        Object.entries(roles).forEach(([roleId, roleName]) => {
            const option = document.createElement("option");
            option.value = roleId;
            option.textContent = roleName;
            roleSelect.appendChild(option);
        });
    } else {
        const selectedDepartmentId = document.querySelector(
            `[name="departmentSelect_${departmentIndex}"]`
        ).value;
        loadRolesForDepartment(
            `roleSelect_${departmentIndex}_${currentRowId}`,
            selectedDepartmentId
        );
    }

    roleCol.appendChild(roleSelect);
    rowDiv.appendChild(roleCol);

    // Employee Select Column
    const employeeCol = document.createElement("div");
    employeeCol.className = "col-md";
    employeeCol.innerHTML =
        "<label>Select Employee</label> <span class='text-danger'>*</span>";

    const employeeSelect = document.createElement("select");
    employeeSelect.className = "form-select employeeSelectClass";
    employeeSelect.id = `employeeSelect_${departmentIndex}_${currentRowId}`;
    employeeSelect.innerHTML =
        '<option value="" selected disabled>Select Employee</option>';
    employeeCol.appendChild(employeeSelect);
    rowDiv.appendChild(employeeCol);

    // Next Status Select Column
    const nextStatusCol = document.createElement("div");
    nextStatusCol.className = "col-md";
    nextStatusCol.innerHTML =
        "<label>Select Next Status</label> <span class='text-danger'>*</span>";

    const nextStatusSelect = document.createElement("select");
    nextStatusSelect.id = `nextStatusSelect_${departmentIndex}_${currentRowId}`;
    nextStatusSelect.className = "form-select nextStatusSelectClass";
    nextStatusSelect.innerHTML =
        '<option value="" selected disabled>Select Next Status</option>';

    Object.entries(approverMessagesData).forEach(
        ([approverId, approverName]) => {
            const option = document.createElement("option");
            option.value = approverId;
            option.textContent = approverName;
            nextStatusSelect.appendChild(option);
        }
    );

    nextStatusSelect.setAttribute(
        "onchange",
        `approvalMessagesSet(${currentRowId})`
    );

    nextStatusCol.appendChild(nextStatusSelect);
    rowDiv.appendChild(nextStatusCol);

    // Approver Message Column
    const approverMessageCol = document.createElement("div");
    approverMessageCol.className = "col-md";
    approverMessageCol.innerHTML =
        "<label>Approver Message</label> <span class='text-danger'>*</span>";

    const approverMessageInput = document.createElement("input");
    approverMessageInput.id = `approverMessageInput_${departmentIndex}_${currentRowId}`;
    approverMessageInput.className = "form-control approverMessageInputClass";
    // Add onchange event listener to call approvalMessagesSet with the row's unique ID

    approverMessageCol.appendChild(approverMessageInput);
    rowDiv.appendChild(approverMessageCol);

    // Action Column
    if (option === "and" || option === "anyone") {
        const actionCol = document.createElement("div");
        actionCol.className = "col-md-1 text-end";
        actionCol.innerHTML = isLastRow
            ? `<button type="button" class="btn btn-info btn-sm mt-5 addSelectBtnClass" onclick="addSelectRow('${departmentIndex}', '${option}')"><i class="fe fe-plus bold"></i></button>`
            : `<button type="button" class="btn btn-danger btn-sm mt-5" onclick="removeSelectRow(this)"><i class="feather feather-trash"></i></button>`;
        rowDiv.appendChild(actionCol);
    }

    selectRow.appendChild(rowDiv);
    roleSelect.setAttribute(
        "onchange",
        `fetchEmployeesByRole(${roleoldvalue ? roleoldvalue : 'this.value'}, document.getElementById('employeeSelect_${departmentIndex}_${currentRowId}'), ${departmentIndex}); approvalMessagesSet(${currentRowId});`
    );


    return selectRow; // Return the complete row element
}

// Fetch employee by role onchange
function fetchEmployeesByRole(roleId, employeeSelectElement, indexForApprovalListOrDepartmentIndex = null, employeeSelectValue = null) {
    const level = document.querySelector('input[name="level"]:checked').value;
    let dId = null;
    if(level == 'department'){
        let val = document.getElementById(`departmentSelectId_${indexForApprovalListOrDepartmentIndex}`);
        dId = val ? val.value : '';
    }
    employeeSelectElement.innerHTML = '<option value="" selected disabled>Select Employee</option>';
    fetch(
        `/admin/settings/tada-settings/get-approval-setting?roleId=${roleId}&dId=${dId}&REQUEST_TYPE=GET_EMP_BY_ROLEID`,
        {
            method: "GET",
        })
        .then((response) => response.json())
        .then((data) => {
            if (data.result && data.result.length > 0) {
                data.result.forEach((employee) => {
                    const option = document.createElement("option");
                    option.value = employee.emp_id;
                    option.textContent = employee.emp_full_name;
                    employeeSelectElement.appendChild(option);

                    // Set the selected employee if it matches
                    if (employee.emp_id === employeeSelectValue) {
                        option.selected = true;
                    }
                });

            }
        })
        .catch((error) => console.error("Error fetching employees:", error));
}

// Function Add Btn Click Row Add
function addSelectRow(departmentIndex, option) {
    const approverListDiv = document.getElementById(
        `approverList_${departmentIndex}`
    );
    if (!approverListDiv) {
        console.error("Approver list div not found.");
        return;
    }

    const rowCount = approverListDiv.childElementCount;

    // Update the previous last row's add button to a remove button
    const lastRow = approverListDiv.lastElementChild;
    if (lastRow) {
        const addButton = lastRow.querySelector(".btn-info");
        if (addButton) {
            addButton.classList.remove("btn-info");
            addButton.classList.add("btn-danger");
            addButton.innerHTML = "<i class='feather feather-trash'></i>";
            addButton.setAttribute("onclick", "removeSelectRow(this)");
        }
    }
    // Append the new row as the last row with an add button
    const newRow = createRow(rowCount + 1, true, departmentIndex, option);
    approverListDiv.appendChild(newRow);


    updateSerialNumbers();
}

// Function Remove Btn Click Row Remove
function removeSelectRow(button) {
    const row = button.closest(".select-row");
    const id = row.querySelector(".primaryKeyClass")?.value; // Selects the first matching element
    deletedKeyStore(5, id);
    if (row) {
        row.remove();
    }
    updateSerialNumbers();
}

// Function to radio options for approval flow (Single, And, Anyone) wise layout
function updateApprovalFields(departmentIndex, option) {
    const approverListDiv = document.getElementById(
        `approverList_${departmentIndex}`
    );
    if (!approverListDiv) {
        console.error("Approver list div not found.", departmentIndex);
        return;
    }

    deletedKeyStore(
        3,
        approverListDiv.getElementsByClassName("primaryKeyClass")
    );

    // Get current old value
    let oldValue = approverListDiv.getAttribute("data-oldordervalue");

    // Get the current content of the approver list
    const approverContent = approverListDiv.innerHTML;

    // Initialize department entry if it doesn't exist
    if (!savedStates[departmentIndex]) {
        savedStates[departmentIndex] = {};
    }

    // Collect values to save from input, select, and textarea elements
    const valuesToSave = {};
    approverListDiv
        .querySelectorAll("input, select, textarea")
        .forEach((element) => {
            valuesToSave[element.id] = element.value;
        });

    // Save or update the state for the previous option (oldValue)
    if (oldValue) {
        savedStates[departmentIndex][oldValue] = {
            approverContent: approverContent,
            values: valuesToSave,
        };
    }

    // Update the old value for the next state change
    approverListDiv.setAttribute("data-oldordervalue", option);

    // Clear the approver list content
    approverListDiv.innerHTML = "";

    // Restore saved state if available for the current option
    const savedState =
        savedStates[departmentIndex] && savedStates[departmentIndex][option];

    if (savedState && savedState.approverContent) {
        // Restore the saved content
        approverListDiv.innerHTML = savedState.approverContent;

        // Restore the saved values
        const valuesToRestore = savedState.values;
        Object.keys(valuesToRestore).forEach((id) => {
            const element = document.getElementById(id);
            if (element) {
                element.value = valuesToRestore[id];
                // Trigger change event if needed
                element.dispatchEvent(new Event("change"));
            }
        });
        deletedKeyStore(
            4,
            approverListDiv.getElementsByClassName("primaryKeyClass")
        );
        updateSerialNumbers();
    } else {
        // If no saved content, append the default row with the "Add" button
        console.log("No saved state found, adding new content.");
        approverListDiv.appendChild(
            createRow(1, true, departmentIndex, option)
        );
    }
}

// Function to fetch role and nextstatus data based on approval message set
function approvalMessagesSet(currentRowId) {
    const form = document.getElementById(`row-id-${currentRowId}`);

    // Get the first (and only) selected option's text in each select box
    const roleSelect = form.querySelector(".roleSelectClass"); // Only one element expected
    const roleSelectedText = roleSelect.value
        ? roleSelect.options[roleSelect.selectedIndex].text
        : "";

    const statusSelect = form.querySelector(".nextStatusSelectClass"); // Only one element expected
    const statusSelectedText = statusSelect.value
        ? statusSelect.options[statusSelect.selectedIndex].text
        : "";

    const approverMessage = form.querySelector(".approverMessageInputClass"); // Only one element expected
    // Concatenate the selected text
    let concatenatedText = roleSelectedText + " - " + statusSelectedText;

    // Set the concatenated text as the value of approverMessage input
    approverMessage.value = concatenatedText;
}

// Function to update serial numbers based on current rows
function updateSerialNumbers() {
    const approverList = document.querySelectorAll(".approver-list-class");
    approverList.forEach((approverListDiv, index) => {
        const rows = approverListDiv.querySelectorAll(
            ".serial-col .serial-number"
        );
        rows.forEach((serialInput, index) => {
            serialInput.value = index + 1; // Update serial numbers in sequence
        });
    });
}

// Function to load roles for department
async function loadRolesForDepartment(
    departmentIndex,
    departmentId,
    onchangeBy = null
) {
    if (!departmentId) {
        console.error("Department ID is required.");
        return;
    }

    try {
        // Fetch roles for the department
        const response = await fetch(
            `/admin/settings/tada-settings/get-approval-setting?departmentId=${departmentId}&REQUEST_TYPE=GET_ROLE_BY_DEPARTMENT`,
            { method: "GET" }
        );

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (!data || !data.result) {
            console.error("No roles found in the response.");
            return;
        }

        // Handle onchange by type
        if (onchangeBy === "departmentSelectBoxOnChange") {
            const roleSelectElements =
                document.getElementsByClassName(departmentIndex);

            // Convert HTMLCollection to Array and process each element
            Array.from(roleSelectElements).forEach((roleSelect) => {
                roleSelect.classList.add("highlight-select");
                roleSelect.innerHTML =
                    '<option value="" selected disabled>Select Role</option>';

                data.result.forEach((role) => {
                    const option = document.createElement("option");
                    option.value = role.role_id;
                    option.textContent = role.role_name;
                    roleSelect.appendChild(option);
                });
            });
        } else {
            const roleSelect = document.getElementById(departmentIndex);

            if (!roleSelect) {
                console.error(
                    `Role select element with ID ${departmentIndex} not found.`
                );
                return;
            }

            roleSelect.innerHTML =
                '<option value="" selected disabled>Select Role</option>';

            data.result.forEach((role) => {
                const option = document.createElement("option");
                option.value = role.role_id;
                option.textContent = role.role_name;
                roleSelect.appendChild(option);
            });

            // Handle preselected value
            const selectOldValue = roleSelect.getAttribute("data-oldvalue");
            if (selectOldValue) {
                const matchingOption = roleSelect.querySelector(
                    `option[value="${selectOldValue}"]`
                );
                if (matchingOption) {
                    matchingOption.selected = true;
                } else {
                    console.log(
                        `Option with value "${selectOldValue}" not found. Selecting default.`
                    );
                    roleSelect.value = "";
                }
            }
        }
    } catch (error) {
        console.error("Error fetching roles:", error);
    }
}

// Function to create the department select dropdown for unique selection
function createDepartmentSelect(department, departmentIndex, id) {
    const selectDiv = document.createElement("div");
    selectDiv.className = "mb-3";
    selectDiv.id = `departmentSelect_${departmentIndex}`; // Ensure each department has a unique ID

    const label = document.createElement("label");
    label.textContent = `Select Department `;
    // label.textContent = `Select Department for ${department}`;
    label.className = "form-label";

    const select = document.createElement("select");
    select.className = "form-select  custom-heighlight departmentSelectClass";
    select.name = `departmentSelect_${departmentIndex}`;
    select.id = `departmentSelectId_${departmentIndex}`;
    // Assign the onchange function properly

    select.setAttribute(
        "onchange",
        `validateUniqueSelections(); loadRolesForDepartment('roleSelectClass_${departmentIndex}', this.value, 'departmentSelectBoxOnChange')`
    );

    // select.onchange = loadRolesForDepartment(departmentIndex, id);

    const placeholderOption = document.createElement("option");
    placeholderOption.textContent = "Select Department";
    placeholderOption.value = "";
    placeholderOption.disabled = true;
    placeholderOption.selected = true;
    select.appendChild(placeholderOption);

    // onchange = "loadRolesForDepartment(${index}, ${departmentId})"

    // Populate the select dropdown with department options
    Object.entries(departmentObj).forEach(([departmentId, departmentName]) => {
        const option = document.createElement("option");
        option.value = departmentId; // Set the department ID as the value
        option.textContent = departmentName; // Set the department name as the display text
        select.appendChild(option);
    });

    // Append the label and select to the div
    selectDiv.appendChild(label);
    selectDiv.appendChild(select);

    // setTimeout(() => {
    //     $(`#departmentSelectId_${departmentIndex}`).SumoSelect({
    //         search: true,
    //         searchText: 'Enter here.',
    //         placeholder: 'Select Department',
    //     });
    // }, 0);

    return selectDiv;
}

// Function department unique selection
function validateUniqueSelections() {
    const dropdowns = document.querySelectorAll(
        "#approval-content .departmentSelectClass"
    );
    selectedDepartments.clear(); // Clear previously selected departments

    let hasDuplicate = false;
    dropdowns.forEach((dropdown) => {
        const selectedValue = dropdown.value;

        if (selectedValue) {
            if (selectedDepartments.has(selectedValue)) {
                hasDuplicate = true;
                alert(
                    "Duplicate selection detected. Please select unique departments for each dropdown."
                );
                dropdown.selectedIndex = 0; // Reset to first option if duplicate is found
            } else {
                selectedDepartments.add(selectedValue); // Add selected value to the set
            }
        }
    });

    if (hasDuplicate) {
        return false; // Prevent form submission or other actions if duplicates exist
    }
}

// Function to initialized process approvers
function initializedProcessApprover() {
    // const processApproverOptimizedData = @json($processApproverOptimizedData);
    if (!processApproverOptimizedData) {
        console.log("No data available.");
        return;
    }

    // Outer loop: Iterates over each approval flow in processApproverOptimizedData
    Object.keys(processApproverOptimizedData).forEach((flowKey) => {
        const paFlow = flowKey;
        const approvalFlowRadio = document.getElementById(paFlow);

        // Clicks on the approval flow radio button if it exists
        if (approvalFlowRadio) approvalFlowRadio.click();

        const flowData = processApproverOptimizedData[flowKey];
        let indexForApprovalList = 0;
        let flowIndex = 0;

        // Loop 1: Iterates over each department within the current flow
        Object.keys(flowData).forEach((departmentKey) => {
            const departmentSelect = document.getElementById(
                `departmentSelectId_${indexForApprovalList}`
            );
            if (departmentSelect && flowKey === "department") {
                // Sets department selection value and triggers change event
                departmentSelect.value = departmentKey;
                departmentSelect.dispatchEvent(new Event("change"));
            }

            const departmentData = flowData[departmentKey];

            // Loop 2: Iterates over each type within the current department
            Object.keys(departmentData).forEach((typeKey) => {
                if (paFlow == "department") {
                    const paOrderRadioBtn = document.getElementById(
                        `${typeKey}_${indexForApprovalList}`
                    );
                    const approvalList = document.getElementById(
                        `approverList_${indexForApprovalList}`
                    );

                    // Clicks on the order radio button for the current type if it exists
                    if (paOrderRadioBtn) paOrderRadioBtn.click();
                }
                if (paFlow == "business") {
                    const approvalOrderRadio = document.getElementById(
                        `${typeKey}_${paFlow}`
                    );
                    const approvalList = document.getElementById(
                        `approverList_${paFlow}`
                    );
                    approvalList.setAttribute("data-oldordervalue", typeKey);

                    if (approvalOrderRadio) approvalOrderRadio.click();
                }
                const typeData = departmentData[typeKey];

                // Loop 3: Iterates over each record in the current type to populate data
                typeData.forEach((record, index) => {
                    const rowId = `row-id-${index + 1}`;
                    const findRow = document.getElementById(
                        `row-id-${flowIndex++}`
                    );

                    if (findRow) {
                        const primaryKeyElement =
                            findRow.querySelector(".primaryKeyClass");
                        // Sets the primary key value for the row and triggers change event
                        if (primaryKeyElement) {
                            primaryKeyElement.value = record["pa_id"];
                            primaryKeyElement.dispatchEvent(
                                new Event("change")
                            );
                        }

                        // Uncomment to handle role selection based on record data
                        const roleElement =
                            findRow.querySelector(".roleSelectClass");
                        if (roleElement) {
                            roleElement.value = record["pa_role_id"];
                            roleElement.setAttribute(
                                "data-oldvalue",
                                record["pa_role_id"]
                            );
                            // roleElement.dispatchEvent(new Event("change"));
                        }

                        const employeeElement = findRow.querySelector(
                            ".employeeSelectClass"
                        );
                        if (employeeElement) {
                            employeeElement.value = record["pa_emp_id"];
                            employeeElement.setAttribute(
                                "data-oldValue",
                                record["pa_emp_id"]
                            );
                            fetchEmployeesByRole(record["pa_role_id"],employeeElement, indexForApprovalList, record["pa_emp_id"]);
                            // employeeElement.dispatchEvent(new Event("change"));
                        }

                        const nextStatusElement = findRow.querySelector(
                            ".nextStatusSelectClass"
                        );
                        if (nextStatusElement) {
                            nextStatusElement.setAttribute("date-oldvalue", 12);
                            nextStatusElement.value = record["pa_status_id"];
                            // nextStatusElement.dispatchEvent(new Event('change'));
                        }

                        const approverMessageElement = findRow.querySelector(
                            ".approverMessageInputClass"
                        );
                        if (approverMessageElement) {
                            approverMessageElement.value = record["pa_message"];
                            approverMessageElement.dispatchEvent(
                                new Event("input")
                            );
                        }

                        // If this is not the last record, simulate clicking the add button to add another row
                        if (index !== typeData.length - 1) {
                            const addselectBtnElm =
                                findRow.querySelector(".addSelectBtnClass");
                            if (addselectBtnElm) {
                                addselectBtnElm.click();
                            }
                        }
                    }
                });
            });
            indexForApprovalList++;
        });
    });
}

initializedProcessApprover();
//  who should approved section End
