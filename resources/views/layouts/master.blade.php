<!DOCTYPE html>
<html lang="zxx">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Link of CSS files -->
    <link rel="stylesheet" href="{{asset('frontend/assets/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/assets/css/aos.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/assets/css/all.min.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/assets/css/odometer.min.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/assets/css/remixicon.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/assets/css/magnific-popup.min.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/assets/css/meanmenu.min.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/assets/css/swiper-bundle.min.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/assets/css/owl.carousel.min.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/assets/css/owl.theme.default.min.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/assets/css/style.css')}}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">


    <title>FixHR - App for Attendance</title>

    <link rel="icon" href="{{ asset('frontend/assets/images/logo/f_fav.png') }}" type="image/x-icon" />
    <style>
    .selected-plan {
        background-color: rgb(0, 102, 253);
        color: #fff;
        transition: 0.9s
    }

    .selected-plan:hover {
        color: #fff
    }

    .totalPriceValue {
        padding-top: 2rem;
    }

    .freePlan {
        padding-top: 2rem;
    }

    ol,
    ul {
        padding-left: 0rem;
    }

    .calculation-part {
        margin-right: 70px;
    }

    .nav-item .nav-link.active {
        color: #fff;
        font-weight: bold;
        text-decoration: none;
    }
    </style>
</head>

<body>

    <!-- Start Navbar Area -->
    @include('layouts.header')
    <!-- End Navbar Area -->

    <!-- Start New App Main Banner Area -->
    @include('layouts.banner')
    <!-- End New App Main Banner Area -->

    @yield('content')


    <!-- Start Footer Style With Black Color Area -->
    @include('layouts.footer')
    <!-- End Footer Style With Black Color Area -->

    <div class="go-top"><i class="ri-arrow-up-s-line"></i></div>

    <!-- Link of JS files -->
    <script src="{{asset('frontend/assets/js/jquery.min.js')}}"></script>
    <script src="{{asset('frontend/assets/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('frontend/assets/js/owl.carousel.min.js')}}"></script>
    <script src="{{asset('frontend/assets/js/swiper-bundle.min.js')}}"></script>
    <script src="{{asset('frontend/assets/js/magnific-popup.min.js')}}"></script>
    <script src="{{asset('frontend/assets/js/meanmenu.min.js')}}"></script>
    <script src="{{asset('frontend/assets/js/appear.min.js')}}"></script>
    <script src="{{asset('frontend/assets/js/odometer.min.js')}}"></script>
    <script src="{{asset('frontend/assets/js/form-validator.min.js')}}"></script>
    <script src="{{asset('frontend/assets/js/contact-form-script.js')}}"></script>
    <script src="{{asset('frontend/assets/js/ajaxchimp.min.js')}}"></script>
    <script src="{{asset('frontend/assets/js/aos.js')}}"></script>
    <script src="{{asset('frontend/assets/js/main.js')}}"></script>
    <script>
    function getCalculator() {
        $('#exampleModal').modal('show');
    }

    function getPayment() {
        $('#alertModal').modal('show');
    }

    function closeCalculator() {
        $('#exampleModal').modal('hide');
    }

    function closePayment() {
        $('#alertModal').modal('hide');
    }
    </script>

    <script>
    $(document).ready(function() {
        // Smooth scroll to section on click, except for the Login link
        $('a.nav-link').on('click', function(event) {
            var hash = this.hash;

            // If the link is not the Login link, apply smooth scrolling
            if (!$(this).attr('href').includes('/login')) {
                event.preventDefault(); // Prevent default behavior for all except login

                // Remove active class from all links
                $('a.nav-link').removeClass('active');

                // Add active class to the clicked link
                $(this).addClass('active');

                // Scroll to the section smoothly
                if (hash !== "") {
                    $('html, body').animate({
                        scrollTop: $(hash).offset().top -
                            50 // Adjust scroll offset if necessary
                    }, 800);
                }
            }
        });

        // Update active link on scroll
        $(window).on('scroll', function() {
            var scrollPos = $(document).scrollTop();
            $('a.nav-link').each(function() {
                var section = $(this.hash);
                if (section.length) {
                    if (section.offset().top - 100 <= scrollPos && section.offset().top +
                        section.height() > scrollPos) {
                        $('a.nav-link').removeClass('active');
                        $(this).addClass('active');
                    }
                }
            });
        });

        // Automatically set the active class for "Home" on page load
        if (window.location.hash === "" || window.location.hash === "#home-section") {
            $('a[href="#home-section"]').addClass('active');
        }
    });

    function toggleEmployeeInput(checkbox) {
        const empCountInput = document.getElementById('empCount');
        const btnMinus = document.getElementById('btnMinus');
        const btnPlus = document.getElementById('btnPlus');

        if (checkbox.checked) {
            // Set input field to 1 and enable the buttons
            empCountInput.value = '1';
            empCountInput.disabled = false;
            btnMinus.disabled = false;
            btnPlus.disabled = false;

            // Call operateEmp to update amounts based on the new value
            operateEmp();
        } else {
            // Disable the buttons and input field
            empCountInput.disabled = true;
            btnMinus.disabled = true;
            btnPlus.disabled = true;

            // Reset empCount to 0 when unchecked
            empCountInput.value = '0';
            operateEmp(); // Update amounts based on the reset value
        }
    }
    </script>
    <script>
    var monthlyBtn = document.getElementById('monthlyBtn');
    var quaterlyBtn = document.getElementById('quaterlyBtn');
    var halfBtn = document.getElementById('halfBtn');
    var annuallyBtn = document.getElementById('annuallyBtn');

    var basePlan = document.getElementById('basePlan');
    var forPlan = document.querySelectorAll(".forPlan");
    var perEmpPrice = document.getElementById("perEmpPrice");

    var empCount = document.getElementById('empCount');
    var additionalAmmount = document.getElementById('additionalAmmount');
    var totalPrice = document.getElementById('totalPrice');
    var i = 0;

    var BasePlan = 500;
    var ForPlan = 50;

    function changePlan(plan) {
        if (plan == 1) {
            monthlyBtn.classList.add('selected-plan');
            quaterlyBtn.classList.remove('selected-plan');
            halfBtn.classList.remove('selected-plan');
            annuallyBtn.classList.remove('selected-plan');
            BasePlan = 500 * plan;
            ForPlan = 50 * plan;
        } else if (plan == 3) {
            quaterlyBtn.classList.add('selected-plan');
            monthlyBtn.classList.remove('selected-plan');
            halfBtn.classList.remove('selected-plan');
            annuallyBtn.classList.remove('selected-plan');
            BasePlan = 500 * plan;
            ForPlan = 50 * plan;
        } else if (plan == 6) {
            halfBtn.classList.add('selected-plan');
            monthlyBtn.classList.remove('selected-plan');
            quaterlyBtn.classList.remove('selected-plan');
            annuallyBtn.classList.remove('selected-plan');
            BasePlan = 500 * plan;
            ForPlan = 50 * plan;
        } else if (plan == 12) {
            annuallyBtn.classList.add('selected-plan');
            monthlyBtn.classList.remove('selected-plan');
            quaterlyBtn.classList.remove('selected-plan');
            halfBtn.classList.remove('selected-plan');
            BasePlan = 500 * plan;
            ForPlan = 50 * plan;
        }
        basePlan.innerHTML = BasePlan;
        perEmpPrice.innerHTML = ForPlan;
        forPlan.forEach(el => el.innerHTML = plan == 1 ? 'Monthly' : (plan == 3 ? 'Quaterly' : (plan == 6 ?
            'Half Yearly' : 'Annually')));
        operateEmp();
    }

    function operateEmp(operation) {
        // Handle plus and minus operations
        if (operation === 'plus') {
            empCount.value = ++i;
        } else if (operation === 'minus') {
            empCount.value = i > 0 ? --i : 0; // Prevent negative value
        } else {
            // Handle direct input
            const inputValue = parseInt(empCount.value, 10);
            if (!isNaN(inputValue) && inputValue >= 0) { // Ensure input is valid and non-negative
                i = inputValue;
            } else {
                i = 0; // Reset to 0 if the input is invalid
                empCount.value = 0; // Ensure the displayed value is 0
            }
        }

        // Update additional amount and total price
        additionalAmmount.innerHTML = i * ForPlan;
        totalPrice.innerHTML = i * ForPlan + BasePlan;
    }
    </script>

</body>