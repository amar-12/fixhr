<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script src="{{ asset('assets/plugins/fileupload/js/dropify.js') }}"></script>
<script src="{{ asset('assets/js/filupload.js?v=10') }}"></script>

<!-- JQUERY JS -->
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>

{{-- external link --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- BOOTSTRAP JS -->
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>

<!-- SELECT2 JS -->
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select all inputs with class time_format_24hrs
        timeFormat24Hr();
        $(".select2").select2();
    });

    function timeFormat24Hr(){
        // Select all inputs with class time_format_24hrs
        const timeInputs = document.querySelectorAll('.time_format_24hrs');
        timeInputs.forEach(input => {
            // Allow only numbers and colon, and enforce format
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^0-9]/g, ''); // Remove non-numeric characters
                let formattedValue = '';
    
                if (value.length > 2) {
                    // Insert colon after first two digits
                    formattedValue = value.slice(0, 2);
                    if (value.length > 4) {
                        // Limit to 4 digits (HHMM)
                        formattedValue += ':' + value.slice(2, 4);
                    } else {
                        formattedValue += ':' + value.slice(2);
                    }
                } else {
                    formattedValue = value;
                }
    
                // Validate hours (00-23)
                if (value.length >= 2) {
                    let hours = parseInt(value.slice(0, 2));
                    if (hours > 23) {
                        formattedValue = '23' + (value.length > 2 ? ':' + value.slice(2) : '');
                    }
                }
    
                // Validate minutes (00-59) when full input is provided
                if (value.length >= 4) {
                    let minutes = parseInt(value.slice(2, 4));
                    if (minutes > 59) {
                        formattedValue = formattedValue.slice(0, 3) + '59';
                    }
                }
    
                e.target.value = formattedValue;
            });
    
            // Ensure proper formatting on blur
            input.addEventListener('blur', function(e) {
                let value = e.target.value.replace(/[^0-9:]/g, '');
                if (value.length === 4 && !value.includes(':')) {
                    // Add colon if missing (e.g., 1234 -> 12:34)
                    value = value.slice(0, 2) + ':' + value.slice(2);
                }
    
                // Pad with zeros if necessary
                let parts = value.split(':');
                if (parts.length === 2) {
                    let hours = parts[0].padStart(2, '0');
                    let minutes = parts[1].padStart(2, '0');
    
                    // Validate hours
                    hours = parseInt(hours) > 23 ? '23' : hours;
                    // Validate minutes
                    minutes = parseInt(minutes) > 59 ? '59' : minutes;
    
                    e.target.value = `${hours}:${minutes}`;
                } else if (parts.length === 1 && parts[0].length > 0) {
                    // Handle partial input (e.g., "12" -> "12:00")
                    let hours = parts[0].padStart(2, '0');
                    hours = parseInt(hours) > 23 ? '23' : hours;
                    e.target.value = `${hours}:00`;
                }
            });
    
            // Prevent non-numeric keypresses except for colon
            input.addEventListener('keypress', function(e) {
                const char = String.fromCharCode(e.which || e.keyCode);
                if (!/[0-9:]/.test(char)) {
                    e.preventDefault();
                }
            });
        });
    }
</script>