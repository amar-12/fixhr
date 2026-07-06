var IDEmpIdError = document.getElementById("EmpIdError");
var IDcontactError = document.getElementById("contactError");
var IDemailError = document.getElementById("emailError");
const combinedData = new FormData();

document.addEventListener("DOMContentLoaded", function () {
	const tabOrder = ["#about", "#organization", "#attendanceLeave", "#joining"];

	function switchTab(currentTabId, direction) {
		const currentIndex = tabOrder.indexOf(currentTabId);
		let nextIndex = currentIndex;

		if (direction === "next" && currentIndex < tabOrder.length - 1) {
			nextIndex++;
		} else if (direction === "prev" && currentIndex > 0) {
			nextIndex--;
		} else {
			return;
		}

		const nextTabId = tabOrder[nextIndex];

		// Remove active class from all tabs and links
		document.querySelectorAll(".tab-pane").forEach(pane => pane.classList.remove("active"));
		document.querySelectorAll(".tab-link").forEach(link => link.classList.remove("active"));

		// Activate new tab and tab link
		document.querySelector(nextTabId).classList.add("active");
		document.querySelector(`.tab-link[data-target="${nextTabId}"]`).classList.add("active");
	}

	// Bind next/prev buttons
	// document.querySelectorAll(".previous-tab, .next-tab").forEach(btn => {
	// 	btn.addEventListener("click", function (e) {
	// 		e.preventDefault();
	// 		const currentTab = this.closest(".tab-pane").id;
	// 		const action = this.textContent.toLowerCase().includes("next") ? "next" : "prev";
	// 		var isFormValid = false;

	// 		// FORM VALIDATION
	// 		if (currentTab === "about") {
	// 			var isFormValid = validateFields('employee_id', 'prefix', 'emp_fname', 'contact', 'gender', 'dateOfBirth', 'permanentSearchInput', 'permanentPinCode');
	// 		} else if (currentTab === "organization") {
	// 			var isFormValid = validateFields('branch', 'department', 'designation', 'gradeTADA', 'role', 'reporting_manager');
	// 		} else if (currentTab === "attendanceLeave") {
	// 			var isFormValid = validateFields('attendancePolicy', 'checkInMethodID1:checkInMethod', 'asignShift', 'attendanceMethod', 'geofencingId', 'weekOffId', 'geoworkId', 'leavePolicy', 'joiningLeave', 'probationLeave');
	// 		} else if (currentTab === "joining") {
	// 			var isFormValid = validateFields('status', 'contractType', 'dateOfJoin', 'employeeJobStatus');
	// 		}

	// 		if (isFormValid || action == "prev") {
	// 			switchTab(`#${currentTab}`, action);
	// 		}
	// 	});
	// });
	// Bind next/prev buttons
	document.querySelectorAll(".previous-tab, .next-tab").forEach(btn => {
		btn.addEventListener("click", function (e) {
			e.preventDefault();
			const currentTab = this.closest(".tab-pane").id;
			const action = this.textContent.toLowerCase().includes("next") ? "next" : "prev";
			var isFormValid = false;

			// FORM VALIDATION
			if (currentTab === "about") {
				var isFormValid = validateFields(
					'employee_id',
					'prefix',
					'emp_fname',
					'contact',
					'gender',
					'dateOfBirth',
					'permanentSearchInput',
					'permanentPinCode'
				);

				// 🔥 ADD MOBILE + PINCODE VALIDATION (WITHOUT CHANGING FUNCTION)
				let contact = document.getElementById("contact");
				let permanentPin = document.getElementById("permanentPinCode");

				if (contact && !/^[6-9]\d{9}$/.test(contact.value.trim())) {
					isFormValid = false;
					document.getElementById("contactError").innerHTML =
						"Enter valid 10-digit mobile number.";
				}

				if (permanentPin && !/^\d{6}$/.test(permanentPin.value.trim())) {
					isFormValid = false;
					document.getElementById("permanentPinCodeError").innerHTML =
						"Pincode must be 6 digits.";
				}

			} 
			else if (currentTab === "organization") {
				var isFormValid = validateFields(
					'branch',
					'department',
					'designation',
					'gradeTADA',
					'role',
					'reporting_manager'
				);
			} 
			else if (currentTab === "attendanceLeave") {
				var isFormValid = validateFields(
					'attendancePolicy',
					'checkInMethodID1:checkInMethod',
					'asignShift',
					'attendanceMethod',
					'geofencingId',
					'weekOffId',
					'geoworkId',
					'leavePolicy',
					'joiningLeave',
					'probationLeave'
				);
			} 
			else if (currentTab === "joining") {
				var isFormValid = validateFields(
					'status',
					'contractType',
					'dateOfJoin',
					'employeeJobStatus'
				);
			}

			if (isFormValid || action == "prev") {
				switchTab(`#${currentTab}`, action);
			}
		});
	});
});

window.modalState = {
	uploadModal: {
		streamData: null,
		capturedImage: null,
		cropper: null,
		uploadSource: null,
		finalCroppedFile: null,
	},
	uploadModal2: {
		streamData: null,
		capturedImage: null,
		cropper: null,
		uploadSource: null,
		finalCroppedFile: null,
	}
};

function openModal(modalId) {
	document.getElementById(modalId).style.display = 'flex';
}

// Close modal by ID and reset
function closeModal(modalId) {
	document.getElementById(modalId).style.display = 'none';
	resetModal(modalId);
}

function openCropModal(imageSrc, modalId) {
	const modalSuffix = modalId === 'uploadModal' ? '1' : '2';
	const cropModalId = 'cropModal' + modalSuffix;
	const cropperImageId = 'cropperImage' + modalSuffix;

	document.getElementById(cropModalId).style.display = 'flex';
	const imageElement = document.getElementById(cropperImageId);
	imageElement.src = imageSrc;

	if (modalState[modalId].cropper) {
		modalState[modalId].cropper.destroy();
	}

	modalState[modalId].cropper = new Cropper(imageElement, {
		aspectRatio: 3 / 4, // Portrait ratio (3:4)
		viewMode: 1,
		movable: true,
		zoomable: true,
		scalable: false,
		rotatable: false
	});
}

function closeCropModal(modalId) {
	const modalSuffix = modalId === 'uploadModal' ? '1' : '2';
	const cropModalId = 'cropModal' + modalSuffix;

	if (modalState[modalId].cropper) {
		modalState[modalId].cropper.destroy();
		modalState[modalId].cropper = null;
	}
	document.getElementById(cropModalId).style.display = 'none';
}

function cropImage(modalId) {
	const modalSuffix = modalId === 'uploadModal' ? '1' : '2';

	if (window.modalState[modalId].cropper) {
		const canvas = window.modalState[modalId].cropper.getCroppedCanvas({
			width: 300,
			height: 400,
		});

		canvas.toBlob(function (blob) {
			const fileName = "profile_photo_" + Date.now() + ".jpg";
			const file = new File([blob], fileName, { type: "image/jpeg" });

			// ✅ Store file globally so external JS can access it
			window.modalState[modalId].finalCroppedFile = file;

			const croppedDataUrl = URL.createObjectURL(file);

			// ✅ DOM IDs
			const galleryPreviewId = 'gallery-preview-' + modalSuffix;
			const cameraPreviewId = 'camera-preview-' + modalSuffix;
			const galleryPreviewContainerId = 'gallery-preview-container-' + modalSuffix;
			const cameraPreviewContainerId = 'camera-preview-container-' + modalSuffix;
			const uploadAreaId = 'upload-area-' + modalSuffix;
			const cameraContainerId = 'camera-container-' + modalSuffix;
			const saveBtnId = 'saveBtn-' + modalSuffix;

			// ✅ Show preview
			const preview = window.modalState[modalId].uploadSource === 'gallery'
				? document.getElementById(galleryPreviewId)
				: document.getElementById(cameraPreviewId);

			preview.src = croppedDataUrl;

			// ✅ Toggle UI
			if (window.modalState[modalId].uploadSource === 'gallery') {
				document.getElementById(galleryPreviewContainerId).style.display = 'block';
				document.getElementById(uploadAreaId).style.display = 'none';
			} else {
				document.getElementById(cameraPreviewContainerId).style.display = 'flex';
				document.getElementById(cameraContainerId).style.display = 'none';
			}

			// ✅ Enable Save button
			document.getElementById(saveBtnId).disabled = false;

			// ✅ Close crop modal (if you defined this function)
			closeCropModal(modalId);
		}, "image/jpeg");
	}
}

// Switch between gallery and camera tabs
function switchTab(tab, modalId) {
	const modalSuffix = modalId === 'uploadModal' ? '1' : '2';

	// Reset state before switching
	resetModal(modalId);

	// Get container
	const tabContainer = document.getElementById(modalId).querySelector('.tab-container');

	// Update tab buttons
	const tabButtons = tabContainer.querySelectorAll('.tab-button');
	tabButtons.forEach(button => {
		button.classList.remove('active');
	});

	// Update tab contents
	const tabContents = tabContainer.querySelectorAll('.cam-tab-content');
	tabContents.forEach(content => {
		content.classList.remove('active');
	});

	// Activate selected tab
	if (tab === 'gallery') {
		tabContainer.querySelector('.tab-button:nth-child(1)').classList.add('active');
		document.getElementById('gallery-tab-' + modalSuffix).classList.add('active');
	} else {
		tabContainer.querySelector('.tab-button:nth-child(2)').classList.add('active');
		document.getElementById('camera-tab-' + modalSuffix).classList.add('active');
	}
}

// Handle file selection from gallery
function handleFileSelect(input, modalId) {
	const file = input.files[0];

	if (file) {
		const reader = new FileReader();
		reader.onload = function (e) {
			modalState[modalId].uploadSource = 'gallery';
			openCropModal(e.target.result, modalId);
		};
		reader.readAsDataURL(file);
	}
}

// Start the camera
function startCamera(modalId) {
	const modalSuffix = modalId === 'uploadModal' ? '1' : '2';
	const videoId = 'video-' + modalSuffix;
	const startCameraBtnId = 'startCameraBtn-' + modalSuffix;
	const captureBtnCameraId = 'captureBtnCamera-' + modalSuffix;

	// Access the user's camera
	navigator.mediaDevices.getUserMedia({
		video: true
	})
		.then(function (videoStream) {
			modalState[modalId].streamData = videoStream;
			const video = document.getElementById(videoId);
			video.srcObject = modalState[modalId].streamData;
			video.play();

			// Show capture button, hide start button
			document.getElementById(startCameraBtnId).style.display = 'none';
			document.getElementById(captureBtnCameraId).style.display = 'inline-block';
		})
		.catch(function (error) {
			console.error('Error accessing camera:', error);
			alert('Could not access the camera. Please make sure you have granted camera permissions.');
		});
}

// Stop the camera
function stopCamera(modalId) {
	if (modalState[modalId] && modalState[modalId].streamData) {
		modalState[modalId].streamData.getTracks().forEach(track => {
			track.stop();
		});
		modalState[modalId].streamData = null;

		const modalSuffix = modalId === 'uploadModal' ? '1' : '2';
		const videoId = 'video-' + modalSuffix;
		const startCameraBtnId = 'startCameraBtn-' + modalSuffix;
		const captureBtnCameraId = 'captureBtnCamera-' + modalSuffix;

		// Reset video element
		const video = document.getElementById(videoId);
		if (video) video.srcObject = null;

		// Reset buttons
		const startCameraBtn = document.getElementById(startCameraBtnId);
		const captureBtnCamera = document.getElementById(captureBtnCameraId);

		if (startCameraBtn) startCameraBtn.style.display = 'inline-block';
		if (captureBtnCamera) captureBtnCamera.style.display = 'none';
	}
}

// Capture photo from camera
function capturePhoto(modalId) {
	const modalSuffix = modalId === 'uploadModal' ? '1' : '2';
	const videoId = 'video-' + modalSuffix;
	const canvasId = 'canvas-' + modalSuffix;

	const video = document.getElementById(videoId);
	const canvas = document.getElementById(canvasId);
	const context = canvas.getContext('2d');

	// Set canvas dimensions to match video
	canvas.width = video.videoWidth;
	canvas.height = video.videoHeight;

	// Draw the current video frame on the canvas
	context.drawImage(video, 0, 0, canvas.width, canvas.height);

	// Convert canvas to data URL
	const imageDataUrl = canvas.toDataURL('image/png');

	// Stop the camera
	stopCamera(modalId);

	modalState[modalId].uploadSource = 'camera';
	openCropModal(imageDataUrl, modalId);
}

// Retake photo
function retakePhoto(modalId) {
	const modalSuffix = modalId === 'uploadModal' ? '1' : '2';
	const cameraPreviewContainerId = 'camera-preview-container-' + modalSuffix;
	const cameraContainerId = 'camera-container-' + modalSuffix;
	const saveBtnId = 'saveBtn-' + modalSuffix;

	// Hide preview, show camera
	document.getElementById(cameraPreviewContainerId).style.display = 'none';
	document.getElementById(cameraContainerId).style.display = 'flex';

	// Start camera again
	startCamera(modalId);

	// Disable save button
	document.getElementById(saveBtnId).disabled = true;
	modalState[modalId].capturedImage = null;
}

function saveImage(modalId) {
    const modalSuffix = modalId === 'uploadModal' ? '1' : '2';

    const profileInputId = modalId === 'uploadModal' ? 'profileInput' : 'profileInput2';
    const imgInput = document.getElementById(profileInputId);
    const cameraPreviewId = 'camera-preview-' + modalSuffix;
    const imagePreview = document.getElementById(cameraPreviewId);
    const croppedFile = window.modalState[modalId]?.finalCroppedFile;

    if (croppedFile) {
        combinedData.append('emp_profile_photo', croppedFile);
        console.log('create1');
    } else if (imgInput?.files?.length > 0) {
        combinedData.append('emp_profile_photo', imgInput.files[0]);
        console.log('create2');
    } else if (imagePreview?.src && imagePreview.src.startsWith('data:image')) {
        fetch(imagePreview.src)
            .then(res => res.blob())
            .then(blob => {
                const file = new File([blob], 'captured_image.jpg', { type: blob.type });
                combinedData.append('emp_profile_photo', file);
                console.log('create3');
                closeModal(modalId);
            })
            .catch(error => {
                console.error('Error converting base64 to file:', error);
            });
    }

    logFormData(combinedData);
    closeModal(modalId);
}


// Reset the modal state
function resetModal(modalId) {
	const modalSuffix = modalId === 'uploadModal' ? '1' : '2';

	// Stop camera if it's running
	stopCamera(modalId);

	// Reset previews
	document.getElementById('gallery-preview-container-' + modalSuffix).style.display = 'none';
	document.getElementById('camera-preview-container-' + modalSuffix).style.display = 'none';
	document.getElementById('camera-container-' + modalSuffix).style.display = 'flex';

	// Reset upload area
	const uploadArea = document.getElementById('upload-area-' + modalSuffix);
	if (uploadArea) {
		uploadArea.style.display = 'flex';
	}

	// Disable save button
	document.getElementById('saveBtn-' + modalSuffix).disabled = true;

	// Reset captured image
	modalState[modalId].capturedImage = null;
	modalState[modalId].uploadSource = null;
}

function getReportingManagers(event) {
	document.querySelector('#thirdNextBtn').disabled = true;

	let departmentId = event.target.value;

	safeAssign(
		departmentText,
		IDdepartment.options[IDdepartment.selectedIndex].text
	);
	// Call to your server to get the updated list of reporting managers based on the department
	$.ajax({
		url: '/admin/employee/get-employee-data', // Replace with your route to get reporting managers
		method: 'GET',
		data: {
			REQUEST_TYPE: 'get-reporting-managers',
			department_id: departmentId
		},
		success: function (response) {
			// Clear the existing options in the reporting manager dropdown
			$('#reporting_manager').empty();

			// Add the default option
			$('#reporting_manager').append('<option value="">Select Reporting Manager</option>');
			document.getElementById('thirdNextBtn').disabled = false;

			// Populate the reporting manager dropdown with the data from the server
			response.managers.forEach(function (manager) {
				$('#reporting_manager').append(
					`<option value="${manager.emp_id}">(${manager.emp_code}) ${manager.emp_full_name}</option>`
				);
			});

			// Re-initialize the Select2 plugin after adding new options
			$('#reporting_manager').trigger('change');
		},
		error: function (error) {
			console.log('Error fetching reporting managers:', error);
		}
	});
}

function checkEmployeeId(e) {
	$.ajax({
		url: empCheckUrl,
		type: "POST",
		data: {
			_token: CSRF,
			emp_id: e.value,
		},
		dataType: "json",
		cache: true,
		success: function (res) {

			if (res) {
				EmpNotAlreadyExist = res.status;
				if (res.status) {
					IDEmpIdError.style.color = "green";
				} else {
					IDEmpIdError.style.color = "red";
				}
				IDEmpIdError.innerHTML = res.message;
			}
		},
	});
}

function validAlpha(inputElement) {
	const regex = /^[A-Za-z]*$/;
	const value = inputElement.value;
	if (!regex.test(value)) {
		inputElement.value = value.replace(/[^A-Za-z]/g, ''); // Remove any non-alphabetical characters
	}
}

function checkInMethodCheckbox(e) {
	if (e.value == 62) {
		$('#checkInMethodID1').prop('disabled', false);
		$('#checkInMethodID2').prop('disabled', false);
		$('#checkInMethodID3').prop('disabled', false);
		$('#checkInMethodID4').prop('disabled', false);
	} else if (e.value == 63) {
		$('#checkInMethodID1').prop('disabled', false);
		$('#checkInMethodID2').prop('disabled', true);
		$('#checkInMethodID3').prop('disabled', false);
		$('#checkInMethodID4').prop('disabled', true);

		$('#checkInMethodID2').prop('checked', false);
		$('#checkInMethodID4').prop('checked', false);
	} else if (e.value == 64) {
		$('#checkInMethodID1').prop('disabled', false);
		$('#checkInMethodID2').prop('disabled', true);
		$('#checkInMethodID3').prop('disabled', false);
		$('#checkInMethodID4').prop('disabled', true);

		$('#checkInMethodID2').prop('checked', false);
		$('#checkInMethodID4').prop('checked', false);
	}
}

// Client Side Validation Function
function validateFields(...ids) {
	let isValid = true;

	ids.forEach(spec => {
		let [id, groupName] = spec.split(":"); // e.g., "genderMale:gender"
		const input = document.getElementById(id);
		if (!input) return;

		const type = input.type || input.tagName.toLowerCase();
		const name = input.name;

		// Reset previous error highlight
		input.classList.remove("is-invalid");
		if (input.classList.contains("select2")) {
			input.nextElementSibling.classList.remove("is-invalid");
		}

		// TEXT, EMAIL, NUMBER, PASSWORD, TEXTAREA
		if (["text", "email", "number", "password", "textarea", "tel", "date"].includes(type)) {
			if (!input.value.trim()) {
				isValid = false;
				input.classList.add("is-invalid");
			}
		}

		// SELECT
		else if (type === "select-one" || type === "select") {
			if (!input.value) {
				isValid = false;
				if (input.classList.contains("select2")) {
					input.nextElementSibling.classList.add("is-invalid");
				} else {
					input.classList.add("is-invalid");
				}
			}
		}

		// CHECKBOX / RADIO
		else if (type === "checkbox" || type === "radio") {
			// Only validate if it's the first in the group
			if (name && document.querySelectorAll(`[name="${name}"]`).length > 1) {
				const group = document.querySelectorAll(`[name="${name}"]`);
				const checked = Array.from(group).some(el => el.checked);
				group.forEach(el => el.classList.remove("is-invalid"));
				if (!checked) {
					isValid = false;
					group.forEach(el => el.classList.add("is-invalid"));
				}
			} else {
				if (!input.checked) {
					isValid = false;
					input.classList.add("is-invalid");
				}
			}
		}

		// Single CHECKBOX / RADIO (no group)
		else if (type === "checkbox" || type === "radio") {
			if (!input.checked) {
				isValid = false;
				input.classList.add("is-invalid");
			}
		}

		// FILE
		else if (type === "file") {
			if (!input.files || input.files.length === 0) {
				isValid = false;
				input.classList.add("is-invalid");
			}
		}
	});

	return isValid;
}

function numericOnly(event) {
	return (event.charCode >= 48 && event.charCode <= 57);
}

function validatePositiveNumber(input) {
	// Use a regular expression to allow only positive numbers
	const regex = /^[1-9]\d*$/;

	// If the input does not match the regex, clear the value
	if (!regex.test(input.value)) {
		input.value = input.value.replace(/[^0-9]/g, '').replace(/^0+/, '');
	}
}

function checkPhoneEmail(e, For) {
	var primary_emp_id = $(e).data("primary-emp-id");
	$("#nextBtn").addClass("disabled").off("click");
	$.ajax({
		url: phoneEmailCheckURL,
		type: "POST",
		data: {
			_token: CSRF,
			primary_emp_id: primary_emp_id,
			id: e.value,
			for: For,
		},
		dataType: "json",
		cache: true,
		success: function (res) {
			if (For == 0) {
				PhoneNotAlreadyExist = res.status;
				if (res.status) {
					IDcontactError.style.color = "green";
					$("#nextBtn").removeClass("disabled")
						.on("click", function () {
							saveData("1", "1");
						});
				} else {
					IDcontactError.style.color = "red";
				}
				IDcontactError.innerHTML = res.message;
			} else if (For == 1) {
				EmailNotAlreadyExist = res.status;
				if (res.status) {
					IDemailError.style.color = "green";
					$("#nextBtn").removeClass("disabled")
						.on("click", function () {
							saveData("1", "1");
						});
				} else {
					IDemailError.style.color = "red";
				}
				IDemailError.innerHTML = res.message;
			}
		},
	});
}

const today = new Date();
const maxAllowedDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());

document.getElementById('dateOfBirth').addEventListener('input', function () {
	const userInputDate = new Date(this.value);
	if (userInputDate > maxAllowedDate) {
		this.setCustomValidity('You must be 18 years or older');
		this.reportValidity();
		document.getElementById('dateOfBirthError').innerHTML = 'You must be at 18 years or older';
		$('#nextBtn').addClass('disabled').off('click');

	} else {
		this.setCustomValidity('');
		this.reportValidity();
		document.getElementById('dateOfBirthError').innerHTML = '';
		$('#nextBtn').removeClass('disabled').on('click', function () {
			saveData('1', '1');
		});
	}
});

function logFormData(formData) {
	for (let [key, value] of formData.entries()) {
		console.log(`${key}:`, value);
	}
}

function collectAllFormData(...formIds) {

	formIds.forEach(id => {
		const form = document.getElementById(id);
		if (!form) return;

		const formData = new FormData(form);
		for (const [key, value] of formData.entries()) {
			combinedData.append(key, value);
		}
	});

	return combinedData;
}

// Employee Ajax Save Function
$("#qAddEmpBTn").click(function (event) {
	event.preventDefault();

	let isValid = validateFields('status', 'contractType', 'dateOfJoin', 'employeeJobStatus');

	if (isValid) {
		const formdata = collectAllFormData("about-form", "organization-form", "attendance-form", "joinig-form");
		combinedData.append("emp_attendance_preference", 367);

		let saveButton = $("#qAddEmpBTn");
		saveButton.attr("disabled", true);
		saveButton.text("Saving...");

		$.ajax({
			url: formSubmitURL,
			type: "POST",
			data: formdata,
			processData: false, // Important: Do not process data
			contentType: false, // Important: Set content type to false
			cache: false, // Prevent caching
			dataType: "json",
			success: function (res) {

				if (!(res.status)) {
					Swal.fire({
						icon: "error",
						// title: 'Error',
						text: "Error in saving data: " + res.message,
						timer: 3000,
					});
					saveButton.attr("disabled", false);
					saveButton.text("Save");
				} else {
					Swal.fire({
						icon: "success",
						text: "Data save successfully.",
						timer: 3000,
					});
					window.location.href = redirectURL;
				}
				// changeTab(state, 0);
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.log("AJAX request failed:", textStatus, errorThrown);
				if (jqXHR.responseJSON && jqXHR.responseJSON.errors) {
					// Create a list of error messages
					let errorMessages = '';
					for (let field in jqXHR.responseJSON.errors) {
						errorMessages += jqXHR.responseJSON.errors[field].join(', ') + '\n'; // Join messages for each field
					}

					// Show SweetAlert with validation errors
					Swal.fire({
						icon: 'error', // Icon type (error, warning, info, success)
						title: 'Validation Errors',
						text: errorMessages, // Display the error messages
						// footer: `<a href="">Why do I have this issue?</a>` // Optional footer link
					});
				} else {
					// Handle other types of errors
					Swal.fire({
						icon: 'error',
						title: 'Oops...',
						text: 'something went wrong!', // Custom message for generic errors
						// footer: `<a href="">Why do I have this issue?</a>` // Optional footer link
					});
				}
				saveButton.attr("disabled", false);
				saveButton.text("Save");
			},
		});
	}
});

$(document).ready(function () {
	// Get the current selected value from the dropdown
	var selectedAttendanceMethod = $('#attendanceMethod').val();
	checkInMethodCheckbox({
		value: selectedAttendanceMethod
	});
})