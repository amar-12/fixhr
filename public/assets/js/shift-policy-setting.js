
document.addEventListener("DOMContentLoaded", function () {
	flatpickr(".timepicker", {
		enableTime: true,
		noCalendar: true,
		dateFormat: "H:i:S", // 24-hour format
		time_24hr: true
	});
});

// Initialize DataTable
function initializeDatatable() {
	datatable({
		tableId: "attendance-shift-type-table-dynamic",
		url: window.pageData.routes.indexUrl,
		dataLength: '[data-length]',
		dataSearch: '[data-search]',
		dataFilter: '[data-filter]',
		dataExport: '[data-export]',
		dataDateFilter: '[data-date-filter]',
		dataShowEntries: '[data-show-entries]',
		dataPagination: '[data-pagination]'
	});
}

// Initialize Select2
function initializeSelect2() {
	$('.select2').select2();

	$('#shiftTypeModal').on('shown.bs.modal', function () {
		if (!$(this).data('select2-initialized')) {
			$('.select2').select2({
				dropdownParent: $('#shiftTypeModal')
			});
			$(this).data('select2-initialized', true);
		}
	});
}

const startTimeInput = document.getElementById('pst_start_time');
const minWorkHourInput = document.getElementById('pst_min_work_hour');
const resultDiv = document.getElementById('tot_min_work_hrs');

// Function to calculate the difference in hours and minutes
function calculateWorkingHour() {
	// Get the start time and minimum working hour values
	const startTimeValue = startTimeInput.value;
	const minWorkHourValue = minWorkHourInput.value;

	// Check if both values are provided
	if (startTimeValue && minWorkHourValue) {
		// Split the time values into hours and minutes (ignoring seconds)
		const [startHours, startMinutes] = startTimeValue.split(':').map(Number);
		const [minHours, minMinutes] = minWorkHourValue.split(':').map(Number);

		// Convert both times into minutes
		const startTotalMinutes = startHours * 60 + startMinutes;
		const minWorkTotalMinutes = minHours * 60 + minMinutes;

		// Calculate the difference in minutes
		let diffInMinutes = minWorkTotalMinutes - startTotalMinutes;

		// If the difference is negative, adjust for the next day
		if (diffInMinutes < 0) {
			diffInMinutes += 24 * 60; // Add 24 hours in minutes
		}

		// Convert the difference back to hours and minutes
		const diffHours = Math.floor(diffInMinutes / 60);
		const diffRemainingMinutes = diffInMinutes % 60;

		// Display the result
		resultDiv.textContent = `(Minimum working hours -  ${diffHours} Hrs ${diffRemainingMinutes} Min)`;
	}
}

// Event listeners to trigger calculation on input change
startTimeInput.addEventListener('input', calculateWorkingHour);
minWorkHourInput.addEventListener('input', calculateWorkingHour);

// Shift Type State
function changeState(shiftTypeId) {
	$(".end-by-div").hide();
	$(".open-break-duration-div").hide();
	$(".shift-duration-div").hide();
	$(".auto-shift-switch").hide();
	$(".in-out-config-div").hide();

	if (shiftTypeId == 244) {
		$(".start-time-div").show();
		$(".end-time-div").show();
		$(".halfDay-config-div").show();
		$(".break-config-div").show();
		$(".partial-day-config-div").show();
		$(".grace-time-div").show();
		$(".min-work-hour-div").show();
	} else if (shiftTypeId == 245) {
		$(".start-time-div").show();
		$(".end-time-div").show();
		$(".min-work-hour-div").show();
		$(".halfDay-config-div").hide();
		$(".break-config-div").show();
		$(".end-by-div").show();
		$(".auto-shift-switch").show();
		$(".in-out-config-div").show();
		$(".grace-time-div").show();
		$(".partial-day-config-div").hide();
	} else if (shiftTypeId == 246) {
		$(".open-break-duration-div").show();
		$(".shift-duration-div").show();
		$(".halfDay-config-div").hide();
		$(".start-time-div").hide();
		$(".end-time-div").hide();
		$(".break-config-div").hide();
		$(".grace-time-div").hide();
		$(".min-work-hour-div").hide();
	}
}

// Handle Add Shift Type
function handleAddShiftType() {
	resetForm();
	$('#shiftTypeModalLabel').html('Add Shift Policy');
	$('#saveBtn').html('Save');
	$('#shiftTypeModal').modal('show');

	if (window.pageData.attendancePolicies) {
		let atdPolicy = window.pageData.attendancePolicies;
		if (Object.keys(atdPolicy).length === 1) {
			const firstKey = Object.keys(atdPolicy)[0];
			$('#pst_ap_id').val(firstKey).trigger('change');
		}
	}

	$("#pst_type_id").val(244).trigger('change');
}

document.querySelectorAll('.accordion-collapse').forEach(collapseEl => {
	const toggleId = collapseEl.dataset.toggleId;
	if (!toggleId) return;

	const toggleEl = document.getElementById(toggleId);
	if (!toggleEl) return;

	// Accordion → Toggle
	collapseEl.addEventListener('shown.bs.collapse', () => {
		toggleEl.checked = true;
	});

	collapseEl.addEventListener('hidden.bs.collapse', () => {
		toggleEl.checked = false;
	});
	console.log(toggleEl);

	// Toggle → Accordion (optional but recommended)
	toggleEl.addEventListener('change', () => {
		const instance = bootstrap.Collapse.getOrCreateInstance(collapseEl);
		toggleEl.checked ? instance.show() : instance.hide();
	});
});

// Calculate Break Duration
document.getElementById('pst_break_begin_time1').addEventListener('change', calculateBreakDuration);
document.getElementById('pst_break_end_time1').addEventListener('change', calculateBreakDuration);

function calculateBreakDuration() {
	const begin = document.getElementById('pst_break_begin_time1').value;
	const end = document.getElementById('pst_break_end_time1').value;

	if (begin && end) {
		const [bh, bm] = begin.split(':').map(Number);
		const [eh, em] = end.split(':').map(Number);

		let duration = (eh * 60 + em) - (bh * 60 + bm);
		if (duration < 0) duration += 1440; // Handle next-day scenario

		document.getElementById('pst_break1_duration').value = duration;
	}
}

// Reset form fields
function resetForm() {
	// Reset the form
	$('#shiftTypePolicyForm')[0].reset();

	// Manually reset any fields that require special handling (e.g., Select2, checkboxes)
	$('#pst_id').val('');
	$('#pst_ap_id').val('').trigger('change');
	$('#pst_type_id').val('').trigger('change');
	$('#pst_name').val('');
	$('#pst_start_time').val('');
	$('#pst_end_time').val('');
	$('#pst_break_duration_minutes').val('');
	$('#pst_is_break_paid').prop('checked', false);
	$("#tot_min_work_hrs").text('');

	// Trigger any necessary change events for custom elements like Select2
	$('#pst_ap_id').trigger('change');
	$('#pst_type_id').trigger('change');

	$('#shiftTypeModal').find('input, select').each(function () {
		const fieldId = $(this).attr('id'); // Get the field ID
		$('#' + fieldId + '_error').text(''); // Clear the corresponding error message
	});

	changeState();
}

// Handle success response
function handleSuccess(response) {
	$('#shiftTypeModal').modal('hide'); // Hide the modal on success

	Swal.fire({
		icon: 'success',
		title: 'Success!',
		text: response.success,
		timer: 3000,
		timerProgressBar: true,
		didClose: () => {
			resetForm(); // Reset the form
			location.reload(); // Reload the page
		}
	});
}

// Handle error response
function handleError(xhr) {
	if (xhr.status === 422) { // Validation error
		let errors = xhr.responseJSON.errors;
		showValidationErrors(errors); // Show validation errors
		Swal.fire({
			title: 'Rectify the following errors!',
			text: Object.values(errors).join(', '),
			icon: 'error',
			confirmButtonText: 'OK'
		});
	} else {
		Swal.fire({
			title: 'Something went wrong!',
			text: 'Please try again later.',
			icon: 'error',
			confirmButtonText: 'OK'
		});
	}
}

// Show validation errors
function showValidationErrors(errors) {
	$('.text-danger').text('');
	$.each(errors, function (key, value) {
		$('#' + key + '_error').text(value[0]);
		$('#' + key + '_error').show();
	});
}

// Handle Create or Update Shift Type Form Submission
function handleShiftTypeSubmit(e) {
	e.preventDefault();
	var id = $('#pst_id').val(); // Get the ID of the shift-type if applicable
	var requiredFields = [
		'pst_ap_id',
		'pst_type_id',
		'pst_name',
		'pst_code',
		'pst_start_time',
		'pst_end_time',
		'pst_min_work_hour',
	];
	if (id == 246) {
		requiredFields.pop('pst_start_time',
			'pst_end_time',
			'pst_min_work_hour',);
		requiredFields.push('pst_shift_duration', 'pst_break_duration_minutes');
	}

	var isValid = validateFields(...requiredFields);
	if (!isValid) {
		Swal.fire({
			title: 'Please fill all required fields!',
			icon: 'error',
			confirmButtonText: 'OK'
		});
		return;
	}

	// Normalize checkbox toggles to 1/0 before sending
	var formArray = $('#shiftTypePolicyForm').serializeArray();

	// Convert to a map so we can override/add normalized checkbox values
	var formMap = {};
	formArray.forEach(function (item) {
		formMap[item.name] = item.value;
	});

	// Loop through all checkboxes and set single-value checkboxes to '1' or '0'
	$('#shiftTypePolicyForm').find('input[type="checkbox"]').each(function () {
		var name = this.name || $(this).attr('name');
		if (!name) return;
		// Skip checkbox arrays (names ending with []) which represent multi-values
		if (name.slice(-2) === '[]') return;

		formMap[name] = $(this).is(':checked') ? '1' : '0';
	});

	// Convert map back to array for serialization
	formArray = Object.keys(formMap).map(function (k) {
		return { name: k, value: formMap[k] };
	});

	var payload = $.param(formArray);

	$.ajax({
		url: window.pageData.routes.storeUrl,
		method: "POST",
		data: payload,
		beforeSend: function () {
			$('#saveBtn').attr('disabled', true); // Disable the button to prevent multiple submissions
		},
		success: function (response) {
			handleSuccess(response);
		},
		error: function (xhr) {
			handleError(xhr);
		},
		complete: function () {
			$('#saveBtn').attr('disabled', false); // Re-enable the button
		}
	});
}

// Generic toggle handler for checkbox -> target input(s)
function applyToggleMappings() {
	var mappings = {
		// single input targets
		'pst_allow_grace_time': ['pst_grace_time'],
		'pst_end_next_day': ['pst_end_by'],
		'pst_allow_punch_begin_before': ['pst_mins_punch_begin_before'],
		'pst_allow_punch_end_after': ['pst_mins_punch_end_after'],
		'pst_hd_office_report_after': ['pst_hd_office_report_after_time'],
		'pst_hd_office_report_before': ['pst_hd_office_report_before_time'],
	};

	function toggleForCheckbox(checkboxId, targets) {
		var $chk = $('#' + checkboxId);
		if (!$chk.length) return;

		function applyState() {
			var checked = $chk.is(':checked');
			targets.forEach(function (t) {
				var $t = $('#' + t);
				if (!$t.length) return;
				$t.prop('disabled', !checked);
				// prefer readonly for text inputs
				if ($t.is('input[type="text"]') || $t.is('input[type="number"]')) {
					$t.prop('readonly', !checked);
				}
				$t.toggleClass('opacity-50', !checked);
				// trigger change for Select2
				if ($t.hasClass('select2')) $t.trigger('change.select2');
			});
		}

		// initial
		applyState();
		// on change
		$(document).off('change', '#' + checkboxId, applyState).on('change', '#' + checkboxId, applyState);
	}

	Object.keys(mappings).forEach(function (k) {
		toggleForCheckbox(k, mappings[k]);
	});
}

// Handle Edit Shift Type
function handleEditShiftType() {
	const shiftData = $(this).data('shift');
	let weekOffStr = shiftData.week_off ?? '{}';
	let weekOffArray = weekOffStr.replace(/[{}]/g, '').split(',');

	$('#shiftTypeModalLabel').html('Update Shift Policy');
	$('#saveBtn').html('Update');

	// Fill form fields with data
	$('#pst_id').val(shiftData.id);
	$('#pst_ap_id').val(shiftData.ap_id).trigger('change');
	$('#pst_type_id').val(shiftData.type_id).trigger('change');
	$('#pst_name').val(shiftData.name);
	$('#pst_code').val(shiftData.code);
	$('#pst_start_time').val(shiftData.start_time);
	$('#pst_end_time').val(shiftData.end_time);
	$('#pst_allow_grace_time').prop('checked', shiftData.allow_grace_time == 1);
	$('#pst_grace_time').val(shiftData.grace_time);
	$('#pst_shift_duration').val(shiftData.shift_duration);
	$('#pst_break_duration_minutes').val(shiftData.break_duration);
	$('#pst_end_next_day').prop('checked', shiftData.end_next_day == 1);
	$('#pst_end_by').val(shiftData.end_by);
	$('#pst_hd_office_report_after').prop('checked', shiftData.hd_office_report_after == 1);
	$('#pst_hd_office_report_after_time').val(shiftData.hd_office_report_after_time);
	$('#pst_hd_office_report_before').prop('checked', shiftData.hd_office_report_before == 1);
	$('#pst_hd_office_report_before_time').val(shiftData.hd_office_report_before_time);
	$('#pst_allow_break1').prop('checked', shiftData.allow_break1 == 1);
	$('#pst_break_begin_time1').val(shiftData.break_begin_time1);
	$('#pst_break_end_time1').val(shiftData.break_end_time1);
	$('#pst_break1_duration').val(shiftData.break1_duration);
	$('#pst_allow_break2').prop('checked', shiftData.allow_break2 == 1);
	$('#pst_break_begin_time2').val(shiftData.break_begin_time2);
	$('#pst_break_end_time2').val(shiftData.break_end_time2);
	$('#pst_allow_punch_begin_before').prop('checked', shiftData.allow_punch_begin_before == 1);
	$('#pst_mins_punch_begin_before').val(shiftData.mins_punch_begin_before);
	$('#pst_allow_punch_end_after').prop('checked', shiftData.allow_punch_end_after == 1);
	$('#pst_mins_punch_end_after').val(shiftData.mins_punch_end_after)
	$('#pst_allow_partial_day').prop('checked', shiftData.allow_partial_day == 1);
	$('#pst_partial_day_type_id').val(shiftData.partial_day_type_id).trigger('change');
	$('#pst_partial_day_begin_time').val(shiftData.partial_day_begin_time);
	$('#pst_partial_day_end_time').val(shiftData.partial_day_end_time);
	$('#pst_min_work_hour').val(shiftData.min_work_hour);
	$('#pst_auto_assign_shift').prop('checked', shiftData.auto_assign_shift == 1);

	weekOffArray.forEach(function (val) {
		$(`input[name="week_off[]"][value="${val}"]`).prop('checked', true);
	});

	$(`input[name="pst_is_break_paid"][value="${shiftData.is_paid}"]`).prop('checked', true);

	$('#shiftTypeModal').modal('show');

	// Calculate and display the working hours initially if both fields have values
	calculateWorkingHour();
}

// Handle Delete Shift Type
function handleDeleteShiftType() {
	var id = $(this).data('id');
	Swal.fire({
		title: 'Are you sure?',
		text: 'You will not be able to recover this shift policy!',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Yes, delete it!',
		cancelButtonText: 'No, keep it'
	}).then((result) => {
		if (result.isConfirmed) {
			var url = window.pageData.routes.deleteUrl.replace(':id', id);
			$.ajax({
				url: url,
				method: "DELETE",
				success: function (response) {
					Swal.fire({
						title: 'Deleted!',
						text: response.success,
						icon: 'success',
						timer: 3000,
						timerProgressBar: true,
						showConfirmButton: false,
						didClose: () => {
							location.reload(); // Reload the page after deletion
						}
					});
				},
				error: function (xhr) {
					var errorMessage =
						'An error occurred while deleting the shift policy. Please try again.';

					// Check if the response contains an error message from the server
					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMessage = xhr.responseJSON.error;
					}

					Swal.fire({
						title: 'Error!',
						text: 'The requested shift policy is assigned to one or more employees or may have been assigned in the past and cannot be deleted.',
						icon: 'error',
						confirmButtonText: 'OK'
					});
				}
			});
		}
	});
}

function validateOfficeTimes() {
	const startTime = document.getElementById('pst_start_time').value;
	const endTime = document.getElementById('pst_end_time').value;
	const afterTime = document.getElementById('pst_hd_office_report_after_time').value;
	const beforeTime = document.getElementById('pst_hd_office_report_before_time').value;

	// Rule 1: after_time >= start_time
	if (startTime && afterTime && afterTime < startTime) {
		Swal.fire({
			toast: true,
			position: 'top-end',
			icon: 'error',
			title: 'After time cannot be less than start time',
			showConfirmButton: false,
			timer: 3000
		});

		document.getElementById('pst_hd_office_report_after_time').value = '';
		return false;
	}

	// Rule 2: before_time <= end_time
	if (endTime && beforeTime && beforeTime > endTime) {
		Swal.fire({
			toast: true,
			position: 'top-end',
			icon: 'error',
			title: 'Before time cannot be greater than end time',
			showConfirmButton: false,
			timer: 3000
		});

		document.getElementById('pst_hd_office_report_before_time').value = '';
		return false;
	}

	return true;
}

$(function () {
	// CSRF Token Setup (for all AJAX requests)
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	// Initialize DataTable
	initializeDatatable();

	// Initialize Select2
	initializeSelect2();

	// Handle Create or Update Shift Type
	$('#saveBtn').on('click', handleShiftTypeSubmit);

	// Handle Delete Shift Type
	$(document).on('click', '.delete-shift-type', handleDeleteShiftType);

	// Handle Add Shift Type Button
	$(document).on('click', '#addShiftTypeBtn', handleAddShiftType);

	// Handle Edit Shift Type Button
	$(document).on('click', '.edit-shift-type', handleEditShiftType);

	changeState();

	applyToggleMappings();
	// run again after our resetForm() helper completes
	$(document).on('reset', '#shiftTypePolicyForm', function () {
		setTimeout(applyToggleMappings, 10);
	});

	// When modal shows (edit or add), re-apply so fields reflect values set programmatically
	$('#shiftTypeModal').on('shown.bs.modal', function () {
		setTimeout(applyToggleMappings, 10);
	});
});
