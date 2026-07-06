let currentStep = 1;
const totalSteps = 5;
let leaveCounter = 1;
const pageData = window.pageData || {};
const leaveCats = pageData.leaveCats || [];
const catCounts = pageData.catCounts || {};
const csrf = pageData.csrf || {};
const submitUrl = pageData.routes['submitUrl'] || {};
let selectedLeaveIds = [];

// Employee Code Type Selection
function selectCodeType(type) {
	document.querySelectorAll('.radio-card').forEach(card => {
		card.classList.remove('selected');
	});
	event.currentTarget.classList.add('selected');
	document.getElementById(type).checked = true;

	const prefixSection = document.getElementById('prefixSection');
	if (type === 'auto') {
		prefixSection.style.display = 'block';
		document.querySelectorAll('.radio-card').forEach(card => {
			card.parentElement.classList.remove('col-lg-6');
			card.parentElement.classList.add('col-lg-4');
		});
	} else {
		prefixSection.style.display = 'none';
		document.querySelectorAll('.radio-card').forEach(card => {
			card.parentElement.classList.remove('col-lg-4');
			card.parentElement.classList.add('col-lg-6');
		});
	}
}

// Toggle Day Selection
function toggleDay(card, dayId) {
	const checkbox = document.getElementById(dayId);
	checkbox.checked = !checkbox.checked;

	if (checkbox.checked) {
		card.classList.add('selected');
	} else {
		card.classList.remove('selected');
	}
}

function getSelectedLeaveIds() {
	return $('select[name="leaveCatIds[]"]')
		.map(function () {
			return Number($(this).val());
		})
		.get()
		.filter(Boolean);
}

function renderLeaveOptions($select) {
	const selectedIds = getSelectedLeaveIds();
	const currentValue = Number($select.val());

	const optionsHtml = leaveCats
		.map(cat => {
			const isSelectedElsewhere =
				selectedIds.includes(cat.m_id) && cat.m_id !== currentValue;

			return `
				<option 
					value="${cat.m_id}"
					${currentValue === cat.m_id ? 'selected' : ''}
					${isSelectedElsewhere ? 'disabled title="Already selected in another row"' : ''}
				>
					${cat.m_name}${isSelectedElsewhere ? ' (selected)' : ''}
				</option>
			`;
		})
		.join('');

	$select.html(`<option value="">Select Leave Type</option>${optionsHtml}`);
}

function refreshAllLeaveDropdowns() {
	$('select[name="leaveCatIds[]"]').each(function () {
		renderLeaveOptions($(this));
	});
}

// Add Leave Type
function addLeaveType() {
	let leaveSelectCount = document.querySelectorAll('select[name="leaveCatIds[]"]').length;
	if (catCounts < leaveSelectCount) {
		Swal.fire({
			icon: 'warning',
			title: 'Limit Reached',
			text: 'You have added all available leave types.',
		});
		return;
	}
	leaveCounter++;

	const container = document.getElementById('leaveTypesContainer');
	const addButton = container.querySelector('.add-leave-btn');

	const newLeave = document.createElement('div');
	newLeave.className = 'leave-input-group';
	newLeave.id = `leave${leaveCounter}`;
	newLeave.style.animation = 'fadeInUp 0.5s ease-out';

	newLeave.innerHTML = `
		<div>
			<select class="form-select" name="leaveCatIds[]">
				<option value="">Select Leave Type</option>
			</select>
		</div>
		<div>
			<input type="number" name="leave_days[]" class="form-control" placeholder="Days per month" min="0" step="0.5">
		</div>
		<div class="d-flex align-items-center h-fill">
			<button class="delete-leave-btn" onclick="removeLeave('leave${leaveCounter}')" type="button">
				<i class="fas fa-trash"></i>
			</button>
		</div>
	`;

	container.insertBefore(newLeave, addButton);

	// 🔥 sync options after add
	refreshAllLeaveDropdowns();
}

// Remove Leave Type
function removeLeave(leaveId) {
	const leave = document.getElementById(leaveId);
	leave.style.animation = 'fadeOut 0.3s ease-out';

	setTimeout(() => {
		leave.remove();
		refreshAllLeaveDropdowns(); // 🔥 sync after delete
	}, 300);
}

$(document).on('change', 'select[name="leaveCatIds[]"]', function () {
	refreshAllLeaveDropdowns();
});

// Update Steps UI
function updateStepsUI() {
	const steps = document.querySelectorAll('.step');
	const progressLine = document.getElementById('progressLine');

	const wizard = document.getElementById('wizard');
	if (currentStep >= 2) {
		wizard.classList.add('is-compact');
	} else {
		wizard.classList.remove('is-compact');
	}

	timeFormat24Hr();
	$(".select2").select2();

	steps.forEach((step, index) => {
		const stepNum = index + 1;
		step.classList.remove('active', 'completed');

		if (stepNum < currentStep) {
			step.classList.add('completed');
			step.querySelector('.step-circle').innerHTML = '<i class="fas fa-check"></i>';
		} else if (stepNum === currentStep) {
			step.classList.add('active');
			const icon = ['fa-forward', 'fa-id-badge', 'fa-clock', 'fa-calendar-week', 'fa-umbrella-beach'][index];
			step.querySelector('.step-circle').innerHTML = `<i class="fas ${icon}"></i>`;
		} else {
			const icon = ['fa-forward', 'fa-id-badge', 'fa-clock', 'fa-calendar-week', 'fa-umbrella-beach'][index];
			step.querySelector('.step-circle').innerHTML = `<i class="fas ${icon}"></i>`;
		}
	});

	// Update progress line
	const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
	progressLine.style.width = progress + '%';

	// Show/hide buttons
	document.getElementById('btnBack').style.display = currentStep > 1 ? 'flex' : 'none';
	document.getElementById('btnNext').style.display = currentStep < totalSteps ? 'flex' : 'none';
	document.getElementById('btnFinish').style.display = currentStep === totalSteps ? 'flex' : 'none';
	document.getElementById('btnSkip').style.display = currentStep < totalSteps ? 'block' : 'none';
	document.getElementById('btnSkip').textContent = currentStep == 1 ? 'Want a Blank Setup?' : 'Skip for now';
}

// Next Step
function nextStep(skip = false) {
	if (skip || validateCurrentStep()) {

		document.getElementById(`step${currentStep}`).classList.remove('active');
		currentStep++;
		document.getElementById(`step${currentStep}`).classList.add('active');
		updateStepsUI();

		// Scroll to top
		document.querySelector('.wizard-content').scrollTop = 0;
	}
}

// Previous Step
function previousStep() {
	document.getElementById(`step${currentStep}`).classList.remove('active');
	currentStep--;
	document.getElementById(`step${currentStep}`).classList.add('active');
	updateStepsUI();

	// Scroll to top
	document.querySelector('.wizard-content').scrollTop = 0;
}

// Skip Step
function skipStep() {
	if (currentStep == 1) {
		Swal.fire({
			title: 'Are you sure?',
			text: "You want a blank setup!",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, skip it!'
		}).then((result) => {
			if (result.isConfirmed) {
				window.location = pageData.routes.accountSettings;
			}
		});
	} else if (currentStep < totalSteps) {
		nextStep(true);
	}
}

// Validate Current Step
function validateCurrentStep() {
	let isValid = true;
	let message = '';

	switch (currentStep) {
		case 2:
			const codeType = document.querySelector('input[name="b_emp_code_type"]:checked');
			if (!codeType) {
				message = 'Please select an employee code generation method';
				isValid = false;
			} else if (codeType.value === 'auto') {
				const prefix = document.getElementById('b_emp_code').value.trim();
				if (!prefix) {
					message = 'Please enter an employee code prefix';
					isValid = false;
				}
			}
			break;

		case 3:
			const shiftType = document.getElementById('shiftType').value;
			const startTime = document.getElementById('startTime').value;
			const endTime = document.getElementById('endTime').value;

			if (!shiftType) {
				message = 'Please select a shift type';
				isValid = false;
			} else if (!startTime || !endTime) {
				message = 'Please enter shift start and end times';
				isValid = false;
			}
			break;

		case 4:
			const selectedDays = document.querySelectorAll('.checkbox-card input[type="checkbox"]:checked');
			if (selectedDays.length === 0) {
				message = 'Please select at least one week off day';
				isValid = false;
			}
			break;

		case 5:
			// Leave policy is optional
			break;
	}

	if (!isValid) {
		Swal.fire({
			icon: 'warning',
			toast: true,
			position: 'top-end',
			title: message,
			timer: 5000,
			showConfirmButton: false,
			timerProgressBar: true,
		});
	}

	return isValid;
}

// Initialize
updateStepsUI();

// Add CSS for fadeOut animation
const style = document.createElement('style');
style.textContent = `
			@keyframes fadeOut {
				from {
					opacity: 1;
					transform: translateX(0);
				}
				to {
					opacity: 0;
					transform: translateX(20px);
				}
			}
		`;
document.head.appendChild(style);

$(document).ready(function () {
	$("#sunday").parent().click();

	const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
	const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
});

$("#btnFinish").click(function (e) {
	e.preventDefault();

	const formSelectors = ['#empCodeForm', '#shiftPolicyForm', '#weekOffPolicyForm', '#leavePolicyForm'];
	const fd = new FormData();

	formSelectors.forEach(sel => {
		const formEl = document.querySelector(sel);
		if (!formEl) return;
		const formData = new FormData(formEl);
		formData.forEach((value, key) => {
			fd.append(key, value);
		});
	});

	$.ajax({
		url: submitUrl,
		method: 'POST',
		data: fd,
		processData: false,
		contentType: false,
		headers: csrf ? { 'X-CSRF-TOKEN': csrf } : {},
		beforeSend: () => {
			$('#btnFinish').prop('disabled', true).text('Saving...');
		},
		success: (res) => {
			$('#btnFinish').prop('disabled', false).text('Finish');
			Swal.fire({
				icon: 'success',
				title: 'Saved',
				text: res.message || 'Setup saved successfully'
			}).then(() => {
				if (res.redirect) window.location = res.redirect;
				else window.location = '/dashboard';
			});
		},
		error: (xhr) => {
			$('#btnFinish').prop('disabled', false).text('Finish');
			let msg = 'An error occurred while saving.';
			try {
				msg = xhr.responseJSON?.message || xhr.responseText || msg;
			} catch (e) { }
			Swal.fire({ icon: 'error', title: 'Error', text: msg });
		}
	});
});