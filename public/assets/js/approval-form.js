
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function () {
    // Handle button click
    $('.approve-btn').on('click', function () {

        const button = $(this); // Reference to the clicked button
        const form = $('#approvalEmployeeMappingForm'); // Reference to the form
        // e.preventDefault();

        // Disable all buttons to prevent multiple clicks
        $('.approve-btn').prop('disabled', true);

        // Get the message input value
        const message = $('#log_description').val().trim();

        // Validation: Check if the message is empty
        if (!message) {
            Swal.fire({
                icon: "warning",
                text: "Message is required.",
                timer: 3000,
            });
            $('.approve-btn').prop('disabled', false); // Re-enable buttons
            return;
        }

        //start handle deduction section
        const deductions = {};
            const payableAmounts = {};
            const inputs = document.querySelectorAll('.deduction-input');
            let deductionAmount = 0;
            var isValid = true;
            inputs.forEach(input => {
                const $input = $(input);
                // Get payable amount using jQuery
                const payableAmount = parseFloat($input.data('payableamount')); // Ensure payableAmount is treated as a number
                const key = input.name.match(/\[(.*?)\]/)[1]; // Extracts the exp_type_id
                const value = parseFloat(input.value) || 0; // Gets the value or defaults to 0
                if(value < 0){
                    isValid = false;
                    Swal.fire({
                        icon: "error",
                        text: 'Deduction amount must be positive.',
                        timer: 3000,
                    });
                }
                payableAmounts[key] = payableAmount;
                deductions[key] = value; // Creates key-value pair
                deductionAmount+=value;
            });
            if(!isValid){
                return false;
            }
            if ($('#deductionAmount').length > 0 && $('#deductionAmount').val() == '') {
                Swal.fire({
                    icon: "warning",
                    text: 'Deduction Amount Required',
                    timer: 3000,
                });
                return false;
            }
            // return false;
        //end handle deduction section


        const fd = {
            log_module_id: form.find('[name="log_module_id"]').val(),
            log_request_id: form.find('[name="log_request_id"]').val(),
            log_description: message,
            log_status: button.data('status'), // Pass the approval status (approve/reject)
            deduction_amount: deductionAmount,
            deduction_info: deductions,
            payable_amount: payableAmounts,
        };
        if (fd.log_module_id == 562) {
            fd._method = "PUT";
        }
        // Send the form data with the selected approval status using AJAX
        $.ajax({
            url: form.attr('action'), // replace with your actual endpoint
            type: 'POST',
            data: fd,
            beforeSend: function () {
                $('.approve-btn').prop('disabled', true);
            },
            success: function (response) {
                // Handle the response, for example, show a success message
                if (response.status === 'success') {
                    Swal.fire({
                        icon: "success",
                        text: response.message,
                        timer: 3000,
                    });
                    location.reload();
                } else if (response.status === 'error') {
                    Swal.fire({
                        icon: "error",
                        text: response.message,
                        timer: 3000,
                    });
                    $('.approve-btn').prop('disabled', false);
                }
                // Optionally, clear the form or reload the page
            },
            error: function (xhr, status, error) {
                // Handle any errors here
                const errorMessage = xhr.responseJSON?.message || "An unexpected error occurred.";
                Swal.fire({
                    icon: "error",
                    text: errorMessage,
                    timer: 3000,
                });
                $('.approve-btn').prop('disabled', false); // Re-enable buttons
            }
        });
    });
});
