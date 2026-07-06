
let map;
let markers = [];

const pageData = window.pageData || {};
const routes = pageData.routes || {};
const csrf = pageData.csrf || '';
// cache last validated GSTIN to avoid redundant API calls
let lastGstinChecked = '';
let gstRequestInProgress = false;

function initMap() {
	// Default location (example: New Delhi)
	const defaultLocation = {
		lat: 28.6139,
		lng: 77.2090
	};

	// Initialize the map
	map = new google.maps.Map(document.getElementById("map"), {
		center: defaultLocation,
		zoom: 12
	});

	// Create the search box and link it to the UI element
	const input = document.getElementById("businessAddressSearchInput");
	const searchBox = new google.maps.places.SearchBox(input);

	// Bias the SearchBox results towards current map's viewport
	map.addListener("bounds_changed", () => {
		searchBox.setBounds(map.getBounds());
	});

	// Event listener for when the user selects a place
	searchBox.addListener("places_changed", () => {
		const places = searchBox.getPlaces();

		if (places.length === 0) {
			return;
		}

		// Clear out the old markers
		markers.forEach(marker => marker.setMap(null));
		markers = [];

		// Get the first place from the places array
		const place = places[0];

		if (!place.geometry || !place.geometry.location) {
			// console.log("Returned place contains no geometry");
			return;
		}

		// Set the marker using google.maps.Marker
		const position = place.geometry.location;
		const marker = new google.maps.Marker({
			map: map,
			position: position, // The position (lat/lng)
			title: place.name // The title to display when hovering
		});

		markers.push(marker);

		// Set the map's view to include the place's location
		if (place.geometry.viewport) {
			map.fitBounds(place.geometry.viewport);
		} else {
			map.setCenter(position);
			map.setZoom(14); // Adjust zoom level as needed
		}

		// Update latitude and longitude fields
		const latitude = position.lat();
		const longitude = position.lng();
		document.getElementById('business_address_longitude').value = longitude;
		document.getElementById('business_address_latitude').value = latitude;
		document.getElementById('business_address_longitude_error').innerHTML = '';
		document.getElementById('business_address_latitude_error').innerHTML = '';
	});
}

// for numeric input field
function numericOnly(event) {
	return (event.charCode >= 48 && event.charCode <= 57);
}

// for pincode
function validatePositiveNumber(input) {
	// Use a regular expression to allow only positive numbers
	const regex = /^[1-9]\d*$/;

	// If the input does not match the regex, clear the value
	if (!regex.test(input.value)) {
		input.value = input.value.replace(/[^0-9]/g, '').replace(/^0+/, '');
	}
}

// Select business type option by fuzzy matching option text with response
function sanitizeTextForMatch(s) {
	if (!s) return '';
	return s.toString().toLowerCase().replace(/[^a-z0-9\s]/g, ' ').replace(/\s+/g, ' ').trim();
}

function selectBusinessTypeByText(respText) {
	const needle = sanitizeTextForMatch(respText);
	if (!needle) return;

	let bestOption = null;
	let bestScore = 0;

	$('#business_type option').each(function () {
		const $opt = $(this);
		const val = $opt.val();
		if (!val) return; // skip placeholder / empty
		const hay = sanitizeTextForMatch($opt.text());

		// exact-ish inclusion is highest priority
		if (hay.indexOf(needle) !== -1 || needle.indexOf(hay) !== -1) {
			bestOption = $opt;
			bestScore = Number.MAX_SAFE_INTEGER;
			return false; // break out of each
		}

		// token overlap scoring
		const tokens = needle.split(' ');
		let score = 0;
		tokens.forEach(t => {
			if (t && hay.indexOf(t) !== -1) score++;
		});

		if (score > bestScore) {
			bestScore = score;
			bestOption = $opt;
		}
	});

	if (bestOption && bestScore > 0) {
		try {
			$('#business_type').val(bestOption.val()).trigger('change');
		} catch (e) {
			// fallback: set selected property
			bestOption.prop('selected', true);
		}
	}
}

function clearMarkers() {
	if (!markers) return;
	markers.forEach(marker => marker.setMap(null));
	markers = [];
}

function geocodeAddressAndSetMarker(address) {
	if (!address) return;
	if (typeof google === 'undefined' || !google.maps) {
		console.warn('Google Maps not available to geocode address');
		return;
	}

	try {
		const geocoder = new google.maps.Geocoder();
		geocoder.geocode({ address: address }, function (results, status) {
			if (status === 'OK' && results && results[0]) {
				const result = results[0];
				const location = result.geometry.location;

				clearMarkers();
				const marker = new google.maps.Marker({
					map: map,
					position: location,
					title: address
				});
				markers.push(marker);

				if (result.geometry.viewport) {
					map.fitBounds(result.geometry.viewport);
				} else {
					map.setCenter(location);
					map.setZoom(14);
				}

				// set inputs
				const lat = location.lat();
				const lng = location.lng();
				document.getElementById('business_address_latitude').value = lat;
				document.getElementById('business_address_longitude').value = lng;
				document.getElementById('business_address_latitude_error').innerHTML = '';
				document.getElementById('business_address_longitude_error').innerHTML = '';
			} else {
				console.warn('Geocode was not successful for the following reason: ' + status);
			}
		});
	} catch (e) {
		console.error('geocodeAddressAndSetMarker error', e);
	}
}

// Gst Valid Check
$(document).on('blur', '#business_gstin_number', function () {
	const raw = $("#business_gstin_number").val() || '';
	const gst = raw.toString().trim().toUpperCase();
	// normalize input back to field
	$("#business_gstin_number").val(gst);

	// length check - skip API if not 15 characters
	if (gst.length !== 15) {
		$('#business_gstin_number_error').text('GSTIN must be 15 characters').removeClass('text-success').addClass('text-danger');
		$('#submitBtn').attr('disabled', false);
		return;
	}

	// skip call if unchanged or a request already in progress
	if (gst === lastGstinChecked || gstRequestInProgress) {
		return;
	}

	gstRequestInProgress = true;
	$.ajax({
		url: routes.validateGst,
		type: "POST",
		data: {
			_token: csrf,
			gstNumber: gst,
		},
		dataType: 'json',
		cache: true,
		beforeSend: function () {
			$('#submitBtn').attr('disabled', 'disabled');
		},
		success: function (result) {
			// remember last checked value to avoid redundant calls
			lastGstinChecked = gst;

			if (result.isValid && !result.isDuplicate) {
				$('#business_gstin_number_error').text('GST Number is Valid').removeClass('text-danger').addClass('text-success');
				$('#submitBtn').attr('disabled', false);
				// populate zip code and business name if available
				try {
					if (result.businessDetails && result.businessDetails.data && result.businessDetails.data.data) {
						const details = result.businessDetails.data.data;
						let pAddress = details.pradr.addr;
						let adr = (pAddress.loc || '') + " " + (pAddress.dst || '') + " " + (pAddress.stcd || '') + " India";
						adr = adr.replace(/\s+/g, ' ').trim();
						// populate address input and geocode to set marker / lat-lng
						$('#businessAddressSearchInput').val(adr);
						geocodeAddressAndSetMarker(adr);
						if (details.pradr && details.pradr.addr && details.pradr.addr.pncd) {
							$("#business_zip_code").val(details.pradr.addr.pncd);
						}
						if (details.tradeNam) {
							$("#business_name").val(details.tradeNam);
						}

						// fuzzy match and select business type if returned
						if (details.ctb) {
							selectBusinessTypeByText(details.ctb);
						}
					}
				} catch (e) {
					console.error('Error processing GST response', e);
				}

			} else {
				$('#business_gstin_number_error').text(!result.isValid ? 'GST Number is Not Valid' : 'GST Number is Already Registered').removeClass('text-success').addClass('text-danger');
			}
		},
		error: function () {
			// ensure UI isn't stuck
			$('#submitBtn').attr('disabled', false);
		},
		complete: function () {
			gstRequestInProgress = false;
		}
	});
});

// Send OTP
$("#sendOTPBtn").click(function () {
	const OTP_COOLDOWN = 10; // seconds (5 minutes)
	let contactNoInput = $("#business_phone_number");
	let contactNo = $("#business_phone_number").val();

	// if cooldown active, prevent sending
	const expiry = parseInt(localStorage.getItem('otpExpiry') || '0', 10);
	const now = Date.now();
	if (expiry && expiry > now) {
		// allow user to see remaining time via button text; do nothing else
		return;
	}

	if (contactNo.length === 10) {
		contactNoInput.parent().next('.text-danger').html('');
		$.ajax({
			type: "POST",
			url: routes.verify_phone_no,
			data: {
				_token: csrf,
				contactNo: contactNo,
			},
			beforeSend: function () {
				$("#sendOTPBtn").html(`Sending...`).attr('disabled', true);
			},
			success: function (response) {
				if (response.status) {
					// set expiry in localStorage and start cooldown
					const expireAt = Date.now() + OTP_COOLDOWN * 1000;
					localStorage.setItem('otpExpiry', expireAt.toString());
					startOtpCooldown();

					$("#enter-otp-div").show();
					Swal.fire({
						icon: 'success',
						toast: true,
						position: 'top-end',
						title: response.message,
						timer: 5000,
						showConfirmButton: false,
						timerProgressBar: true,
					});
				} else {
					Swal.fire({
						icon: 'warning',
						toast: true,
						position: 'top-end',
						title: response.message,
						timer: 5000,
						showConfirmButton: false,
						timerProgressBar: true,
					});
					$("#enter-otp-div").hide();
					$("#sendOTPBtn").html(`Send OTP <i class="fa fa-paper-plane small fa-lg"></i>`).attr('disabled', false);
				}
			},
			error: function () {
				$("#sendOTPBtn").html(`Send OTP <i class="fa fa-paper-plane small fa-lg"></i>`).attr('disabled', false);
			}
		});
	} else {
		contactNoInput.parent().next('.text-danger').html('Phone number must be 10 digits');
	}
});

// OTP cooldown timer helpers
let otpIntervalId = null;
function formatSeconds(s) {
	const mm = String(Math.floor(s / 60)).padStart(2, '0');
	const ss = String(s % 60).padStart(2, '0');
	return `${mm}:${ss}`;
}

function startOtpCooldown() {
	const expiry = parseInt(localStorage.getItem('otpExpiry') || '0', 10);
	if (!expiry || expiry <= Date.now()) {
		clearOtpCooldown();
		return;
	}

	// ensure button disabled and show initial time
	$('#sendOTPBtn').attr('disabled', true);

	if (otpIntervalId) clearInterval(otpIntervalId);
	otpIntervalId = setInterval(function () {
		const remainingMs = parseInt(localStorage.getItem('otpExpiry'), 10) - Date.now();
		const remaining = Math.max(0, Math.ceil(remainingMs / 1000));
		if (remaining > 0) {
			$('#sendOTPBtn').html(`Resend (${formatSeconds(remaining)})`);
		} else {
			clearOtpCooldown();
		}
	}, 1000);
}

function clearOtpCooldown() {
	if (otpIntervalId) {
		clearInterval(otpIntervalId);
		otpIntervalId = null;
	}
	localStorage.removeItem('otpExpiry');
	$('#sendOTPBtn').attr('disabled', false).html(`Resend <i class="fa fa-paper-plane small fa-lg"></i>`);
}

// on load, resume cooldown if present
$(function () {
	const expiry = parseInt(localStorage.getItem('otpExpiry') || '0', 10);
	if (expiry && expiry > Date.now()) {
		startOtpCooldown();
	} else {
		// ensure button shows default
		$('#sendOTPBtn').attr('disabled', false).html(`Send OTP <i class="fa fa-paper-plane small fa-lg"></i>`);
	}
});

$("#otp").on('input', function () {
	let otp = $("#otp").val();
	let phone = $("#business_phone_number").val();
	if (otp.length == 6) {
		$.ajax({
			type: "POST",
			url: routes.verify_otp,
			data: {
				_token: csrf,
				otp: otp,
				phone: phone,
			},
			beforeSend: function () {
				// make contact readonly and show spinner at end of otp input
				$("#business_phone_number").prop('readonly', true);
				// ensure input has smooth border transition
				$("#otp").css('transition', 'border-color 0.3s ease, opacity 0.6s ease');
				// remove any previous messages
				$('#otp_error').remove();
				$('#otp-spinner').remove();
				$('<span id="otp-spinner" class="otp-spinner" style="margin-left:8px;color:#6c757d"><i class="fa fa-spinner fa-spin"></i></span>').insertAfter($('#otp'));
			},
			success: function (response) {
				// remove spinner
				$('#otp-spinner').remove();
				if (response.status) {
					$('#otp').css({
						border: '2px solid #0dcd94',
						boxShadow: '0 0 0 1px #0dcd9478',
						outline: 'none'
					});
					$('#otp').prev().css({
						color: '#0dcd94',
					});
					setTimeout(function () {
						$('#otp').fadeOut(600, function () {
							$('#enter-otp-div').hide();
							$('#send-otp-div').removeClass('d-flex').addClass('d-none');
						});
					}, 200);
					// keep phone readonly once verified
				} else {
					// failed - red border and show error message below
					$('#otp').css({
						border: '2px solid #dc3545',
						boxShadow: '0 0 0 1px rgba(220,53,69,0.15)',
						outline: 'none'
					});
					$('#otp').prev().css({
						color: 'rgba(220,53,69,0.78)',
					});
					var msg = response.message || 'Invalid OTP';
					$('<div id="otp_error" class="text-danger small mt-1">' + msg + '</div>').insertAfter($('#otp'));
					// re-enable phone for retry
					$("#business_phone_number").prop('readonly', false);
				}
			},
			error: function () {
				// network / server error
				$('#otp-spinner').remove();
				$('#otp').css({
					border: '2px solid #dc3545',
					boxShadow: '0 0 0 1px rgba(220,53,69,0.15)',
					outline: 'none'
				});
				$('#otp').prev().css({
					color: 'rgba(220,53,69,0.78)',
				});
				$('<div id="otp_error" class="text-danger small mt-1">Could not verify OTP. Try again later.</div>').insertAfter($('#otp'));
				$("#business_phone_number").prop('readonly', false);
			}
		});
	}
});

// Business form submit
$(document).ready(function () {
	$('#image').dropify({
		tpl: {
			message: `
				<div class="dropify-message upload-area">
	
					<div class="upload-icon-circle">
						<i class="fa fa-cloud-upload-alt fa-3x"></i>
					</div>
	
					<div class="mb-2">
						<span class="fw-semibold text-primary">Upload a file</span>
						<span class="text-secondary ms-1">or drag and drop</span>
					</div>
	
					<p class="small text-secondary mb-0">PNG, JPG, GIF up to 5MB</p>
	
				</div>
			`,
			preview: `
				<div class="dropify-preview">
					<span class="dropify-render"></span>
					<div class="dropify-infos">
						<div class="dropify-infos-inner">
							<p class="dropify-filename">
								<span class="dropify-filename-inner"></span>
							</p>
						</div>
					</div>
				</div>
			`,
			clearButton: `
				<button type="button" class="dropify-clear btn btn-sm btn-outline-custom mt-2">
					Remove
				</button>
			`
		}
	});

	$('#image').on('change', function () {
		$('#image_error').html('');
	});

	$('select').on('input change', function () {
		$(this).closest('.form-group').find('.text-danger-select2').html('');
	});
	$('input').on('input change', function () {
		$(this).next('.text-danger').html('');
	});

	$('#business_password').on('input change', function () {
		$('#business_password_error').html('');
	});

	$('#business_confirm_password').on('input change', function () {
		$('#business_confirm_password_error').html('');
	});

	$('#business-form').on('submit', function (e) {
		e.preventDefault(); // Prevent default form submission
		let isValid = true;
		$('input, select', this).each(function () {
			let $input = $(this);
			let value = $input.val();
			let autofocusSet = false;

			// Skip validation for optional image field
			if ($input.attr('id') === 'image') {
				return true; // continue to next iteration
			}

			if ($input.is('select') && !$input.val()) {
				// Show error message if select box is required and no option is selected
				$input.closest('.form-group').find('.text-danger-select2').html(
					'This field is required', $input.attr('type'));
				isValid = false;
				if (!autofocusSet) {
					$input.focus();
					autofocusSet = true;
				}
			} else if (!$input.val()) {
				// Show error message if input field is required and empty
				$input.next('.text-danger').html('This field is required');
				isValid = false;
				if ($input.attr('id') === 'business_password') {
					$('#business_password_error').html('This field is required');
					if (!autofocusSet) {
						$input.focus();
						autofocusSet = true;
					}
				} else if ($input.attr('id') === 'business_confirm_password') {
					$('#business_confirm_password_error').html('This field is required');
					if (!autofocusSet) {
						$input.focus();
						autofocusSet = true;
					}
				} else {
					$input.next('.text-danger').html('This field is required');
					if (!autofocusSet) {
						$input.focus();
						autofocusSet = true;
					}
				}
			} else if ($input.attr('id') === 'business_phone_number' && value.length !==
				10) {
				// Show error message if phone number is not 10 digits
				$input.next('.text-danger').html('Phone number must be 10 digits');
				isValid = false;
				if (!autofocusSet) {
					$input.focus();
					autofocusSet = true;
				}
			} else if ($input.attr('id') === 'business_zip_code' && value.length !==
				6) {
				// Show error message if pincode is not 6 digits
				$input.next('.text-danger').html('Pincode must be 6 digits');
				isValid = false;
				if (!autofocusSet) {
					$input.focus();
					autofocusSet = true;
				}
			} else if ($input.attr('id') === 'business_password' && value.length < 8) {
				// Show error message if password is less than 8 characters
				$('#business_password_error').html('Password must be at least 8 characters');
				isValid = false;
				if (!autofocusSet) {
					$input.focus();
					autofocusSet = true;
				}
			} else if ($input.attr('id') === 'business_confirm_password' && value !== $(
				'#business_password')
				.val()) {
				// Show error message if confirm password does not match password
				$('#business_confirm_password_error').html('Confirm password does not match password');
				isValid = false;
				if (!autofocusSet) {
					$input.focus();
					autofocusSet = true;
				}
			} else if ($input.attr('id') === 'business_password') {
				$('#business_password_error').html('');
			} else if ($input.attr('id') === 'business_confirm_password') {
				$('#business_confirm_password_error').html('');
			} else {
				// Remove error message if input field is valid
				$input.next('.text-danger').html('');
			}
		});
		console.log(isValid);

		if (isValid) {
			let formData = new FormData(this);
			$.ajax({
				url: routes.saveBusiness,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				beforeSend: function () {
					$('#submitBtn').attr('disabled', true);
				},
				success: function (response) {
					Swal.fire({
						icon: 'success',
						text: 'Your Business Account Has Been Created Successfully!',
						timer: 3000,
						didClose: () => {
							window.location.href = routes.demo_setup; // Reload the page after deletion
						}
					});
					// alert('Form submitted successfully');
				},
				error: function (xhr) {
					$('span.text-danger').html(''); // Clear previous errors

					if (xhr.status === 422) { // Validation error
						let errors = xhr.responseJSON.errors;
						$.each(errors, function (key, value) {
							$('#' + key + '_error').html(value[
								0]); // Show validation error
						});

					}
					$('#submitBtn').attr('disabled', false);
				}
			});
		} else {
			$('#submitBtn').attr('disabled', false);
		}
	});
});

$(function () {
	$('.select2').select2();
});

document.querySelectorAll('.password-toggle-icon').forEach(item => {
	item.addEventListener('click', function () {
		const input = document.querySelector(this.getAttribute('data-toggle'));
		const icon = this.querySelector('i');

		if (input.type === "password") {
			input.type = "text";
			icon.classList.remove('fa-eye-slash');
			icon.classList.add('fa-eye');
		} else {
			input.type = "password";
			icon.classList.remove('fa-eye');
			icon.classList.add('fa-eye-slash');
		}
	});
});
