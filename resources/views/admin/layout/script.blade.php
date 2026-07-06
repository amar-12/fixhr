<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

{{-- <script src="https://www.unpkg.com/datatable-customizer/index.js"></script> --}}
{{-- <script src="https://www.unpkg.com/datatable-customizer@1.0.3/index.js"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/datatable-customizer@1.0.3/index.js"></script>

<script src="{{ asset('assets/plugins/formwizard/jquery.smartWizard.js?v1.3') }}"></script>
<script src="{{ asset('assets/plugins/formwizard/fromwizard.js?v3.80') }}"></script>
<script src="{{ asset('assets/plugins/fileupload/js/dropify.js') }}"></script>
<script src="{{ asset('assets/js/filupload.js?v=10') }}"></script>

<script src="{{ asset('assets/repeatable-field-group/jquery.repeater.min.js') }}"></script>
<script src="{{ asset('assets/js/validation.js') }}"></script>

{{-- external link --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- JQUERY JS -->


<!-- BOOTSTRAP JS -->
<script src="{{ asset('assets/plugins/bootstrap/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>


<!-- INTERNAL MULTIPLE SELECT JS -->
<script src="{{ asset('assets/plugins/multipleselect/multiple-select.js') }}" wire:ignore></script>
<script src="{{ asset('assets/plugins/multipleselect/multi-select.js') }}" wire:ignore></script>

<!-- MOMENT JS -->
<script src="{{ asset('assets/plugins/moment/moment.js') }}"></script>


<!--SIDEMENU JS -->
<script src="{{ asset('assets/plugins/sidemenu/sidemenu.js') }}"></script>

<!-- SELECT2 JS -->
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.js') }}"></script>

<!-- INTERNAL DATA TABLES -->
<script src="{{ asset('assets/js/datatables.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/buttons.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/jszip.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/responsive.bootstrap5.min.js') }}"></script>

<!-- STICKY JS -->
<script src="{{ asset('assets/js/sticky.js') }}"></script>

<!-- CUSTOM JS -->
<script src="{{ asset('assets/js/custom.js?v=0.6') }}"></script>


{{-- <script src="https://www.unpkg.com/datatable-customizer/assets/jquery.sumoselect.min.js"></script>
<script src="https://www.unpkg.com/datatable-customizer/assets/jquery.sumoselect.js"></script>

<script>
    $('.search_test').SumoSelect({
        search: true,
        searchText: 'Search'
        // triggerChangeCombined: true,

    });

    $('.sumo_search').SumoSelect({
        search: true,
        searchText: 'Search'
        // triggerChangeCombined: true,
    });
</script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.sumoselect/3.0.2/jquery.sumoselect.min.js"></script>
<script>
    $(document).ready(function() {
        $('.search_test').SumoSelect({
            search: true,
            searchText: 'Search...',
        });

        $('.sumo_search').SumoSelect({
            search: true,
            searchText: 'Search...',
        });

        // $('#customLengthMenu').val('');
        // $('#customLengthMenu').val('10').trigger('change');

        setTimeout(function() {
            $('#customLengthMenu')[0].sumo.selectItem(1);
            $('#customLengthMenu').trigger('change');
        }, 100);
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Select all inputs with class time_format_24hrs
        timeFormat24Hr();
    });

    function timeFormat24Hr() {
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

<script>
    let sessionLifetime = {{ config('session.lifetime') }} * 60 * 1000;
    let inactivityTimer;

    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);

        inactivityTimer = setTimeout(() => {
            console.log("User inactive. Session timeout.");

            Swal.fire({
                title: 'Session Expired',
                text: 'You were inactive for too long. Please login again.',
                icon: 'warning',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
            }).then(() => {
                window.location.href = "/login";
            });

        }, sessionLifetime);
    }

    // Activity events
    ['mousemove', 'keydown', 'scroll', 'click', 'touchstart'].forEach(event => {
        document.addEventListener(event, resetInactivityTimer, true);
    });

    // Start timer initially
    resetInactivityTimer();

    setInterval(() => {
        fetch('/check-session', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (res.status === 401) {
                    Swal.fire({
                        title: 'Session Expired',
                        text: 'Your session has expired. Redirecting to login.',
                        icon: 'warning',
                        showConfirmButton: false,
                        timer: 2000,
                    }).then(() => {
                        window.location.href = "/login";
                    });
                }
            });
    }, 60000); // every 1 min
</script>
