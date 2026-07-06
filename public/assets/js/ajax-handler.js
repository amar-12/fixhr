/**
 * AJAX Handler Script
 * Author: Umesh Kumar Shau
 * Description: Centralized script for handling form submissions and delete actions via AJAX.
 * Date: December 12th, 2024
 */

// CSRF Token Setup
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Clear errors as the user interacts with input fields
$('input, select, textarea').on('input change', function () {
    const fieldId = $(this).attr('id');
    $('#' + fieldId + '_error').text(''); // Clear the error message
});

// Centralized AJAX Form Submission
$(document).on('submit', 'form', function (e) {
    let form = $(this);

    // Exclude the form with the ID 'permissionForm' or 'appPermissionForm' this is the direct submitted form
    if (form.attr('id') === 'permissionForm' || form.attr('id') === 'appPermissionForm') {
        return; // Allow default submission for this form
    }

    // Prevent default submission for all other forms
    e.preventDefault();

    let formId = form.attr('id');

    let actionUrl = form.attr('action');
    let method = form.attr('method');
    let formData = new FormData(this);
    // Check for the selected checkboxes and ensure their values are included
    form.find('input[type="checkbox"]:not(:checked)').each(function () {
        // Manually append unchecked checkboxes to ensure they appear with an empty or default value

        formData.append($(this).attr('name'), ''); // empty value for unchecked checkboxes
    });

    $.ajax({
        url: actionUrl,
        method: method,
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            form.find('button[type="submit"]').attr('disabled', true);
        },
        success: function (response) {
            // form.find('button[type="submit"]').attr('disabled', false);
            $('#' + form.closest('.modal').attr('id')).modal('hide');

            Swal.fire({
                icon: 'success',
                text: response.message,
                timer: 3000
            }).then(() => {
                form[0].reset();
                // $('#' + form.closest('.modal').attr('id')).modal('hide');
                location.reload();
            });
        },
        error: function (response) {
            form.find('button[type="submit"]').attr('disabled', false);
            if (response.responseJSON && response.responseJSON.errors) {
                let errors = response.responseJSON.errors;
                $.each(errors, function (key, value) {
                    key = key.replace(/\./g, '_');
                    form.find('#' + key + '_error').text(value);
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    text: response.responseJSON?.message || 'Something went wrong!',
                });
            }
        }
    });
});

// delete button
$(document).on('click', '.delete-button', function (e) {
    e.preventDefault();

    let button = $(this);
    let url = button.data('url'); // Get the URL from a data attribute on the button

    Swal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url, // Use the correct actionUrl here
                method: 'DELETE',
                beforeSend: function () {
                    button.attr('disabled', true);
                },
                success: function (response) {
                    button.attr('disabled', false);
                    Swal.fire({
                        icon: 'success',
                        text: response.message,
                        timer: 3000,
                    }).then(() => {
                        location.reload(); // Reload the page or dynamically update the UI
                    });
                },
                error: function (xhr) {
                    button.attr('disabled', false);
                    let errorMessage = 'Something went wrong!';

                    // Check if the response contains a message
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }

                    Swal.fire({
                        icon: 'error',
                        text: errorMessage,
                    });
                },
            });
        }
    });
});

// Example usage:
function handleModalSetup(button) {
    // Prevent default action
    event.preventDefault();

    // Get the data-bs-target attribute value and other modal-related info
    let target = button.attr('data-bs-target');
    let title = button.attr('data-title');

    // Use the target to find the modal or form
    let modal = $(target);
    let form = modal.find('form'); // Assumes the form is inside the modal

    // Clear previous error messages
    form.find('.text-danger').text(''); // Clear the text inside elements with the 'text-danger' class

    // Reset all input fields
    form.find('input[type="text"]').val('');   // Reset text inputs
    form.find('input[type="number"]').val('');   // Reset number inputs
    form.find('input[type="radio"]').prop('checked', false);  // Uncheck all radio buttons

    // Reset all select fields
    form.find('select').prop('selectedIndex', 0); // Resets to the first option

    // Reset any other necessary fields like checkboxes or textareas
    form.find('textarea').val('');  // Reset textareas
    form.find('input[type="checkbox"]').prop('checked', false);  // Uncheck all checkboxes


    // Check if it's a create or edit button
    if (button.hasClass('create-button')) {
        // Set modal label and button for Create
        modal.find('.modal-title').text(title || 'Create'); // Update modal title
        modal.find('button[type="submit"]').text('Create'); // Update submit button text
        var $select = $('select.sumo_search');
        if ($select.length) {
            $('select.sumo_search')[0].sumo.unSelectAll();
        }
    } else if (button.hasClass('edit-button')) {
        // Set modal label and button for Edit
        modal.find('.modal-title').text(title || 'Edit'); // Update modal title
        modal.find('button[type="submit"]').text('Update'); // Update submit button text

        // Optionally: Populate the form with data for editing (if provided)
        let data = button.data('edit-data'); // Get the data from data-edit-data attribute

        if (data) {
            $.each(data, function (key, value) {
                // Dynamically set the form values for each field
                var field = form.find(`[name="${key}"], [id="${key}"]`);

                // Handle SumoSelect
                if (field.is('select') && field.closest('.SumoSelect').length > 0) {
                    // Ensure value is an array (for multi-select)
                    if (value) {
                        if (typeof value === 'string') {
                            value = JSON.parse(value); // Convert string to array if it's a JSON string
                        }

                        // Make sure it's an array
                        if (!Array.isArray(value)) {
                            value = value ? [value] : []; // If it's not an array, convert it to one
                        }

                        // Iterate over the values and select them in SumoSelect
                        $.each(value, function (index, value1) {
                            field.SumoSelect().sumo.selectItem(String(value1));
                        });
                        field.trigger('change'); // Trigger the change event for SumoSelect
                    }

                } else if (field.is('select')) {
                    // For regular <select> (not SumoSelect)
                    field.val(value); // Set the value for the regular select box
                    field.trigger('change');
                } else if (field.is('input[type="checkbox"]')) {
                    // For checkboxes
                    field.prop('checked', Boolean(value)); // Check or uncheck based on the value
                } else if (field.is('input[type="file"]')) {
                    // For file inputs (can't set file programmatically)
                    // Display a download link for the file if available
                    // let baseUrl = window.location.protocol + "//" + window.location.host;
                    let baseUrl = window.location.origin;

                    // Assuming `value` is the relative path
                    let fileUrl = value;  // For example: "uploads/RecruitmentCandidate/Resume/Resume_12345.pdf"

                    // Combine base URL and relative path to form the full URL
                    let fullUrl = baseUrl + '/' + fileUrl;
                    if (value) {
                        // For file path/URL, show a link to the file
                        form.find(`#${key}_link`).attr('href', fullUrl).show(); // Show the link for downloading
                    } else {
                        // Hide the link if there's no file
                        form.find(`#${key}_link`).hide();
                    }
                } else if (field.is('img')) {
                    // For <img> tag, set the src attribute
                    if (value) {
                        let baseUrl = window.location.origin;
                        let imageUrl = baseUrl + '/' + value; // Assuming `value` is the relative image path
                        field.attr('src', imageUrl);  // Set the src attribute of the <img> tag
                    }
                } else {
                    // For other input types (text, email, etc.)
                    field.val(value); // Set the value for regular input fields
                }
            });
        }
    }
}


// Event listener for create button
$(document).on('click', '.create-button', function (e) {
    handleModalSetup($(this));
});

// Event listener for edit button
$(document).on('click', '.edit-button', function (e) {
    handleModalSetup($(this));
});
