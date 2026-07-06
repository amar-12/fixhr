@extends('layouts.master')
@section('content')

<!-- <div class="support-container">
    <a href="chat" class="support-link">
        <button type="submit" class="support-button">Customer Support</button>
    </a>
</div> -->

<div class="support-container">
    <a href="javascript:void(0);" class="support-link" onclick="toggleChatForm()">
        <button type="button" class="support-button">Support</button>
    </a>

</div>

<div class="chat-container" id="chat-container" style="display: none;">
    <div class="chat-box" id="chat-box">

    </div>

    <form id='myForm'>
        <input type="text" name="msg" id="user-input" placeholder="Ask me something..." required />
        <button type="submit" id="sendbtn">Send</button>

    </form>
</div>




<style>
 .support-container {
    padding: 20px;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    background-color: #f4f4f4;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);

}

.support-link {
    text-decoration: none;
}

.support-button {
    background-color: #0d6efd;;
    color: white;
    width: 130px;             /* Set a specific width */
    height: 40px;            /* Set the same height as width to make it a circle */
    border-radius: 30px;      /* Makes the button round */
    border: none;
    font-size: 15px;         /* Increase font size to make it more readable */
    display: flex;           /* Flexbox to center the text */
    justify-content: center; /* Center horizontally */
    align-items: center;     /* Center vertically */
    cursor: pointer;
    transition: background-color 0.3s ease;
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Optional shadow effect */
}

.support-button:hover {
    background-color: #0056b3; /* Darker shade on hover */
}

#chat-container {
    display: none; /* Initially hidden */
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: white;
    border: 1px solid #ccc;
    padding: 15px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    z-index: 9999;
    height: 50vh;
    width: 17.5vw;
}
#myForm {
    position: absolute;
    bottom: 0;
    width: 100%;
    padding: 10px;
    background: #fff;
}
#sendbtn{
    background-color: #007bff;
    color: white;
    border-radius:3px;
}
/* #user-input {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
}

#user-input:hover {
    background-color: #45a049;
} */

.cancel-button {
    background-color: #dc3545;
    color: white;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: none;
    font-size: 18px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: background-color 0.3s ease;
    position: absolute;
    top: 0;
    left: 80px;  /* Position the Cancel button near the Support button */
}

.cancel-button:hover {
    background-color: #c82333;
}

</style>



<!-- Start Features Area -->
<div class="features-area pt-100 pb-75">
    <div class="container">
        <!--start section heading-->
        <div class="col-md-10 offset-md-1">
            <div class="section-heading text-center">
                <h5>About our App</h5>
                <h2>Wonderful features to satisfy you to use our mobile app</h2>
                <p>Fix HR is designed for those who love to user interface. You will love the seamless way we
                    display the user inter face on your devices.As businesses rely on software or app to engage
                    customers, innovation and velocity becomes core to delivering value.</p>
            </div>
        </div>
        <!--end section heading-->
        <div class="row justify-content-center">
            <div class="col-xl-3 col-lg-4 col-sm-6 col-md-6">
                <div class="features-card">
                    <div class="icon">
                        <i class="fa fa-gear"></i>
                    </div>
                    <h3>Quick Setup</h3>
                    <p>The app is really easy to install, and the complete business setup process will take a few
                        minutes.</p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6 col-md-6">
                <div class="features-card">
                    <div class="icon">
                        <i class="ri-pencil-ruler-line"></i>
                    </div>
                    <h3>Lovely Design</h3>
                    <p>With a carefully thought-out design, Fix HR looks great on any device and is easy to
                        customize from the web.</p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6 col-md-6">
                <div class="features-card">
                    <div class="icon three">
                        <i class="fa fa-file-alt"></i>
                    </div>
                    <h3>Optimized Data</h3>
                    <p>Speed is essential when loading data, especially if you have a large number of users.</p>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 col-sm-6 col-md-6">
                <div class="features-card">
                    <div class="icon">
                        <i class="fa fa-lock"></i>
                    </div>
                    <h3>Secure Data</h3>
                    <p>Transfer all information securely with SSL, allowing you to save data from the public eye.
                    </p>
                </div>
            </div>
        </div>


    </div>
</div>
<!-- End Features Area -->

<!-- Start App About Area -->
<div class="app-about-area pb-100">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-12">
                <div class="app-about-image">
                    <img src="{{asset('frontend/assets/images/about.png')}}" alt="image">
                </div>
            </div>

            <div class="col-lg-6 col-md-12">
                <div class="app-about-content">
                    <div class="big-text">ABOUT</div>
                    <h5 class="text-primary">ABOUT FIX HR</h5>
                    <h2>Delivering exceptional user experiences.</h2>
                    <p align="justify">The Fix HR attendance management system project was developed to help
                        employers track and
                        monitor their employees. It's the system used to track how much time the workers spend
                        working and how much time they spend off. An attendance management system monitors arrival
                        time, duration of absence from a section, Mis-Punch, gate pass, leave at credit and profit,
                        and the monthly aggregate of hours of duty and absence of employees. This monitoring is done
                        using computerized software and specific devices. This approach ensures that your employees
                        are only paid for the time they work. The attendance system provides a precise view of the
                        company's labor costs.</p>
                    <p>
                        Our customer uses our application to adopt next-generation development practices, deliver
                        new applications, and modernize existing applications.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="app-about-shape-1">
        <img src="{{asset('frontend/assets/images/shape-1_1.png')}}" alt="image">
    </div>
    <div class="app-about-shape-2">
        <img src="{{asset('frontend/assets/images/shape-2_2.png')}}" alt="image">
    </div>
    <div class="app-about-shape-3">
        <img src="{{asset('frontend/assets/images/shape-3.png')}}" alt="image">
    </div>
</div>
<!-- End App About Area -->

<!-- Start About Area -->
<div class="about-area ptb-100" id="some-fatcs">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-12">
                <div class="about-content">
                    <h5 class="text-primary">TAKE A LOOK AT OUR</h2>
                        <h2>Some Facts</h2>
                        <p>Fix HR enables all its users with constant support and wide set of tools to develop and
                            grow their businesses and projects.some of our favorite facts that you might not have
                            known.</p>
                        <div class="features-text">
                            <h6>Our App</h6>
                            <p>The goal of the fix HR project management is to keep track their employees working
                                hours. The advantages of the Fix HR project is basically productivity and cost
                                saving and illegal compliance.</p>
                        </div>
                        <div class="features-text bg-light p-4 rounded shadow-sm">
                            <h6 class="text-primary fw-bold">Our Mission</h6>
                            <ul class="list-unstyled mt-3">
                                <li class="d-flex align-items-start mb-2">
                                    <i class="fa fa-check-circle text-success me-2" aria-hidden="true"></i>
                                    <span>To create intuitive and user-friendly software that simplifies complex
                                        processes, making technology accessible to everyone.</span>
                                </li>
                                <li class="d-flex align-items-start mb-2">
                                    <i class="fa fa-check-circle text-success me-2" aria-hidden="true"></i>
                                    <span>To build eco-friendly software solutions that promote sustainable practices in
                                        the tech industry and help businesses reduce their carbon footprint.</span>
                                </li>
                            </ul>
                        </div>


                </div>
            </div>
            <div class="col-lg-6 col-md-12">
                <div class="about-image">
                    <img src="{{asset('frontend/assets/images/screenshot/app-mocup-4.png')}}" data-aos="fade-up"
                        alt="about">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End About Area -->

<!-- Start Funfacts Area -->
<div class="funfacts-area pb-75">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-3 col-sm-6 col-md-6">
                <div class="funfacts-box">
                    <div class="icon">
                        <i class="ri-download-2-line"></i>
                    </div>
                    <p>APP DOWNLOAD</p>
                    <h3><span class="odometer" data-count="0">00</span><span class="sign"></span></h3>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-md-6">
                <div class="funfacts-box bg1">
                    <div class="icon">
                        <i class="ri-star-fill"></i>
                    </div>
                    <p>5 STAR RATING</p>
                    <h3><span class="odometer" data-count="">00</span><span class="sign"></span></h3>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6 col-md-6">
                <div class="funfacts-box bg3">
                    <div class="icon">
                        <i class="ri-map-pin-user-line"></i>
                    </div>
                    <p>HAPPY USERS</p>
                    <h3><span class="odometer" data-count="0">00</span><span class="sign"></span></h3>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-md-6">
                <div class="funfacts-box bg2">
                    <div class="icon">
                        <i class="fa fa-trophy"></i>
                    </div>
                    <p>BEST AWARDS</p>
                    <h3><span class="odometer" data-count="0">00</span><span class="sign"></span></h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Funfacts Area -->

<!-- Start Key Features Area -->
<div id="features-section" class="key-features-area pt-100 pb-75">
    <div class="container">
        <div class="section-title title-with-bg-text">
            <div class="big-title">Features</div>
            <h5 class="text-primary">AN EXHAUSTIVE THRIVING LIST OF</h5>
            <h2>Awesome Features</h2>
            <p>We've mentioned everything you could possibly want to know about Fix HR. Some snapshots of our FixHR
                application are:</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-xl-4 col-lg-6 col-sm-6 col-md-6">
                <div class="key-features-card">
                    <div class="icon">
                        <i class="fa fa-cog"></i>
                    </div>
                    <h3>Attendance Method</h3>
                    <p>Enhanced flexibility to set On-duty or Off-duty attendance for auto or manual
                        In Premises, Outdoor and Remote setup to ensure accurate attendance through QR code, Facial
                        Recognition, Selfie and geo-location.</p>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-sm-6 col-md-6">
                <div class="key-features-card bg-color-two">
                    <div class="icon bg2">
                        <i class="fa fa-cog"></i>
                    </div>
                    <h3>Hybrid Attendance</h3>
                    <p>Multiple options for attendance such as app-based Selfie, Facial Recognition,
                        QR code and geo-location/geo-fencing/geo-tagging for enhanced flexibility. It assists in
                        keeping track of employee schedules.</p>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-sm-6 col-md-6">
                <div class="key-features-card">
                    <div class="icon">
                        <i class="bi bi-display"></i>
                    </div>
                    <h3>On Demand Features</h3>
                    <p>Track employee attendance, view timesheets, track overtime, lateness, shift
                        setting, approve or decline leave, and manage gate pass requests from any mobile or browser
                        on your laptop or PC.</p>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-sm-6 col-md-6">
                <div class="key-features-card bg-color-two">
                    <div class="icon bg2">
                        <i class="bi bi-display"></i>
                    </div>
                    <h3>Multi Level Approval System</h3>
                    <p>Fix HR provides custom workflows for approval from managers, HR, and
                        administrators, enabling enhanced transparency across the organization.</p>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-sm-6 col-md-6">
                <div class="key-features-card">
                    <div class="icon">
                        <i class="fa fa-cog"></i>
                    </div>
                    <h3>Easy to Manage Your All Data</h3>
                    <p>Fix HR helps you manage your device data, improving client relationships, boosting
                        productivity, and organizing your workflows efficiently.</p>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-sm-6 col-md-6">
                <div class="key-features-card bg-color-two">
                    <div class="icon bg2">
                        <i class="bi bi-display"></i>
                    </div>
                    <h3>Responsive Design For All Devices</h3>
                    <p>Fix HR provides user-friendly features with a responsive design, ensuring a positive user
                        experience across web applications and mobile apps on all devices.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="key-features-shape-1">
        <img src="{{asset('frontend/assets/images/shape-1_2.png')}}" alt="image">
    </div>
    <div class="key-features-shape-2">
        <img src="{{asset('frontend/assets/images/shape-2_1.png')}}" alt="image">
    </div>
</div>

<!-- End Key Features Area -->


<!-- Start App Screenshots Area -->
<div id="screenshot" class="app-screenshots-area bg-color pb-100 mt-5">
    <div class="container">
        <div class="section-title title-with-bg-text">
            <div class="big-title">App Screen</div>
            <h5 class="text-primary">SHOWCASE YOUR APP</h5>
            <h2>The Screenshot Gallery</h2>
            <p>This is easy way showcase your app screen . If you want to show your app just pop in the
                screenshots and the magic happens.</p>
        </div>
        <div class="app-screenshots-slides owl-carousel owl-theme">
            <div class="single-screenshot-card">
                <img src="{{asset('frontend/assets/images/screenshot/Screenshot0.png')}}" alt="screenshots">
            </div>
            <div class="single-screenshot-card">
                <img src="{{asset('frontend/assets/images/screenshot/Screenshot1.png')}}" alt="screenshots">
            </div>
            <div class="single-screenshot-card">
                <img src="{{asset('frontend/assets/images/screenshot/Screenshot2.png')}}" alt="screenshots">
            </div>
            <div class="single-screenshot-card">
                <img src="{{asset('frontend/assets/images/screenshot/Screenshot3.png')}}" alt="screenshots">
            </div>
            <div class="single-screenshot-card">
                <img src="{{asset('frontend/assets/images/screenshot/Screenshot4.png')}}" alt="screenshots">
            </div>
            <div class="single-screenshot-card">
                <img src="{{asset('frontend/assets/images/screenshot/Screenshot5.png')}}" alt="screenshots">
            </div>
            <div class="single-screenshot-card">
                <img src="{{asset('frontend/assets/images/screenshot/Screenshot6.png')}}" alt="screenshots">
            </div>
            <div class="single-screenshot-card">
                <img src="{{asset('frontend/assets/images/screenshot/Screenshot7.png')}}" alt="screenshots">
            </div>
            <div class="single-screenshot-card">
                <img src="{{asset('frontend/assets/images/screenshot/Screenshot8.png')}}" alt="screenshots">
            </div>
            <div class="single-screenshot-card">
                <img src="{{asset('frontend/assets/images/screenshot/Screenshot9.png')}}" alt="screenshots">
            </div>
        </div>
    </div>

    <div class="app-screenshots-shape-1">
        <img src="{{asset('frontend/assets/images/shape-1_4.png')}}" alt="image">
    </div>
    <div class="app-screenshots-shape-2">
        <img src="{{asset('frontend/assets/images/shape-2_3.png')}}" alt="image">
    </div>
</div>
<!-- End App Screenshots Area -->

<!-- Start App Ever Area -->
<div class="app-ever-area ptb-100">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-12">
                <div class="app-ever-image">
                    <img src="{{asset('frontend/assets/images/app-ever.png')}}" alt="image">
                </div>
            </div>

            <div class="col-lg-6 col-md-12">
                <div class="app-ever-content">
                    <h5 class="text-primary">DESCRIBE YOUR APP</h5>
                    <h2>Let’s See How It Work</h2>
                    <p>We've gone over everything you could possibly want to know about Fix HR, from how exactly the
                        app works.Three Simple Steps to journey.</p>
                    <ul class="list">
                        <li>
                            <div class="how-work-single">
                                <div class="icon bg2">
                                    <i class="ri-download-cloud-2-line"></i>
                                    <div class="number">01</div>
                                </div>
                                <h3>Download</h3>
                                <p>Most provabily best you can trust on it, just log in with your mail account from
                                    play
                                    store and using whatever you want for your business.</p>
                            </div>
                        </li>
                        <li>
                            <div class="how-work-single two">
                                <div class="icon">
                                    <i class="ri-menu-line"></i>

                                    <div class="number">02</div>
                                </div>
                                <h3>Configure It</h3>
                                <p align="justify">Open your web app's Settings tab that appear on your web screen,
                                    each
                                    of
                                    these customizations
                                    are unique to your Activity.</p>
                            </div>
                        </li>
                        <li>
                            <div class="how-work-single three">
                                <div class="icon bg2">
                                    <i class="ri-trophy-line"></i>
                                    <div class="number">03</div>
                                </div>
                                <h3>Yay! Done</h3>
                                <p align="justify">Explore and share Fix HR app. Check out our FAQ for more
                                    information
                                    on the
                                    system,
                                    subscriptions, 24-Hour Passes, features and more.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="app-ever-shape-1">
        <img src="{{asset('frontend/assets/images/shape-1_6.png')}}" alt="image">
    </div>
</div>
<!-- End App Ever Area -->




<!-- Start App Video Area -->
<div class="pb-100" id="custom-plan-area">
    <div class="container">
        <div class="section-title title-with-bg-text">
            <h5 class="text-primary">FIX HR COST CALCULATOR</h5>
            <h2>Need a Custom Plan?</h2>
            <p>We’ve created this handy plan cost calculator just for you. Find
                out how much your custom plan will cost in under a minute! </p>
            {{-- <a onclick="getCalculator()" class="btn btn-sm btn-primary">USE COST CALCULATOR</a> --}}
        </div>
        <div class="app-video-box custom-plan-wrap">
            <img src="{{asset('frontend/assets/images/video.jpg')}}" alt="video">
            {{-- <a onclick="getCalculator()" class="plan-btn two video-btn popup-video"><i class="ri-play-line"></i></a> --}}
            <a href="#contact-us" class="plan-btn two video-btn">
                            <i class="ri-play-line"></i>
                        </a>

            <div class="shape">
                <img class="shape-1" src="{{asset('frontend/assets/images/shape-1_5.png')}}" alt="shape1">
                <img class="shape-2" src="{{asset('frontend/assets/images/shape-2_4.png')}}" alt="shape2">
            </div>
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content shadow-lg rounded-3">
                        <div class="modal-header bg-light rounded-top">
                            <h5 class="modal-title" id="exampleModalLabel">Price Calculator</h5>
                            <div class="col-9 text-end">
                                <button type="button" class="btn-close me-3" aria-label="Close"
                                    onclick="closeCalculator()"></button>
                            </div>
                        </div>
                        <div class="modal-body p-4 bg-white">
                            <div class="row justify-content-center">
                                <div class="col-lg-10 offset-lg-1 col-md-12 col-sm-12">
                                    <div class="content">
                                        <div class="text-center mx-auto mb-4">
                                            <span class="fs-5 fw-bold">Customize Your FixHR Subscription</span><br>
                                            <span class="fs-6 text-muted">Your registered Email Id is:
                                                <a href="#"><span class="text-primary mx-1">Login Now</span></a>
                                            </span>
                                        </div>

                                        <!-- Plan Selector Buttons -->
                                        <div class="row justify-content-center">
                                            <div class="col-md-12">
                                                <div class="d-flex justify-content-center py-3">
                                                    <ul
                                                        class="d-flex justify-content-center list-unstyled border border-primary rounded p-1 shadow-sm">
                                                        <li id="monthlyBtn"
                                                            class="btn btn-sm selected-plan mx-1 btn-outline-primary"
                                                            onclick="changePlan(1)">
                                                            <b>Monthly</b>
                                                        </li>
                                                        <li id="quaterlyBtn"
                                                            class="btn btn-sm mx-1 btn-outline-primary"
                                                            onclick="changePlan(3)">
                                                            <b>Quaterly</b>
                                                        </li>
                                                        <li id="halfBtn"
                                                            class="btn btn-sm mx-1 btn-outline-primary"
                                                            onclick="changePlan(6)">
                                                            <b>Half Yearly</b>
                                                        </li>
                                                        <li id="annuallyBtn"
                                                            class="btn btn-sm mx-1 btn-outline-primary"
                                                            onclick="changePlan(12)">
                                                            <b>Annually</b>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Base Plan Pricing -->
                                        <div class="pricings py-3 border-bottom shadow-sm bg-light rounded-3 px-3 mb-4">
                                            <div class="d-flex justify-content-between">
                                                <p class="mb-0">Base Plan (for up to 10 Employees):</p>
                                                <h5 class="mb-0"><span id="basePlan">500</span><br><span
                                                        class="text-muted fs-6 fw-light">For
                                                        <span class="forPlan">Monthly</span></span></h5>
                                            </div>
                                        </div>

                                        <!-- Additional Employee Pricing -->
                                        <div class="additional-employee py-3 shadow-sm bg-light rounded-3 px-3 mb-4">
                                            <div class="d-flex justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <label class="form-check-label me-2">
                                                        <input type="checkbox" class="form-check-input" checked
                                                            onchange="toggleEmployeeInput(this)"> Add
                                                        Additional Employee:
                                                    </label>
                                                    <div class="d-flex align-items-center mx-3">
                                                        <button class="btn btn-sm btn-primary rounded-pill px-2"
                                                            id="btnMinus" onclick="operateEmp('minus')">
                                                            <i class="fa fa-minus"></i>
                                                        </button>
                                                        <input id="empCount" oninput="operateEmp()" value='1'
                                                            class="fs-6 text-center border-0 mx-2"
                                                            style="width:3rem; background-color: #f0f0f0; border-radius: 0.25rem;"
                                                            disabled>
                                                        <button class="btn btn-sm btn-primary rounded-pill px-2"
                                                            id="btnPlus" onclick="operateEmp('plus')">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <h5 class="my-auto"><span id="additionalAmmount">0</span><br><span
                                                            class="text-muted fs-6 fw-light">Per Employee
                                                            <span id="perEmpPrice">50</span> / <span
                                                                class="forPlan">Monthly</span></span></h5>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Total Price -->
                                        <div class="pricings py-3 border-top shadow-sm bg-light rounded-3 px-3">
                                            <div class="d-flex justify-content-between">
                                                <h5 class="mb-0">Total Price:</h5>
                                                <h5 class="mb-0"><i class="fa fa-inr mx-2"></i><span
                                                        id="totalPrice">500</span><br><span
                                                        class="text-muted fs-6 fw-light">(Inclusive of All Taxes)</span>
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer bg-light rounded-bottom">
                            <button type="button" class="btn btn-outline-danger"
                                onclick="closeCalculator()">Close</button>
                            <button type="button" class="btn btn-outline-primary" onclick="getPayment()">Proceed to
                                Payment</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- JS Script for Functionality -->
            <div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel"
                aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="alertModalLabel">Alert</h5>
                            <button type="button" class="btn-close" aria-label="Close"
                                onclick="closePayment()"></button>
                        </div>
                        <div class="modal-body text-center">
                            <div class="alert-icon mb-4">
                                <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                            </div>
                            <h4 class="mb-3">You Need to Login First</h4>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-outline-danger" onclick="closePayment()">Close</button>
                            <a href="{{ url('/login') }}" class="btn btn-outline-primary">Login Now</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- End App Video Area -->

<!-- Start App Pricing Area -->
{{-- <div class="app-pricing-area pb-100" id="pricing">
    <div class="container">
        <div class="section-title title-with-bg-text">
            <div class="big-title">PLANS</div>
            <h5 class="text-primary">SUBSCRIPTION PLAN</h5>
            <h2>Choose The Right Plan</h2>
            <p>Build trust with prospective clients, delight existing customers, and increase the efficiency
                and collaboration within your team. We have experience with plethora of technologies.</p>
            <p>Fix HR has plans, from free to paid, that scale with your needs. Subscribe to a plan that
                fits the size of your business. Fix HR monthly pricing is based on how many employees and
                functions you need to start your work. If you ready to use Fix HR for a long time you can
                customize is for quaterly, half yearly and annual basis to save your time and money.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="single-app-pricing-box active">
                    <div class="title">
                        <h3>Start</h3>
                        <p>Powerful &amp; awesome elements</p>
                    </div>
                    <span class="popular">START</span>
                    <div class="price">
                        ₹.500 <span>/Month</span>
                    </div>
                    <div class="pricing-btn">
                        <a href="#" class="default-btn">Get Started</a>
                    </div>
                    <ul class="features-list">
                        <li><i class="ri-check-line"></i> Up to 10 Employee</li>
                        <li><i class="ri-check-line"></i> Easy to Customize</li>
                        <li><i class="ri-check-line"></i> Unlimited Server Space</li>
                        <li><i class="ri-check-line"></i> Support Unlimited User</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="single-app-pricing-box">
                    <div class="title">
                        <h3>Standard</h3>
                        <p>Powerful &amp; awesome elements</p>
                    </div>
                    <div class="price">
                        ₹.6000 <span>/Year</span>
                    </div>
                    <div class="pricing-btn">
                        <a href="#" class="default-btn">Get Started</a>
                    </div>
                    <ul class="features-list">
                        <li><i class="ri-check-line"></i> Up to 10 Employee</li>
                        <li><i class="ri-check-line"></i> Easy to Customize</li>
                        <li><i class="ri-check-line"></i> Unlimited Server Space</li>
                        <li><i class="ri-check-line"></i> Support Unlimited User</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div> --}}
<!-- End App Pricing Area -->

<!-- Start New App Download Area -->
<div class="new-app-download-area ptb-100">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-12">
                <div class="new-app-download-content">
                    <div class="big-text">Download</div>
                    <h5 class="text-primary">CHOOSE YOUR DEVICE PLATFORM</h5>
                    <h2>Get The App on</h2>
                    <p>Get the latest resources for downloading, installing, and updating Fix HR. Select your device
                        platform and Use Our app and Enjoy Your Life.</p>
                    <div class="btn-box">
                        <a href="https://play.google.com/store/apps/details?id=com.fixingdots.htkc.fixhr&hl=en_US" target="_blank" class="playstore-btn">
                            <img src="{{asset('frontend/assets/images/play-store.png')}}" alt="image">
                            Get It On
                            <span>Google Play</span>
                        </a>
                        <a href="#" class="applestore-btn">
                            <img src="{{asset('frontend/assets/images/apple-store.png')}}" alt="image">
                            Download on the
                            <span>Apple Store</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12">
                <div class="new-app-download-image text-end" data-aos="fade-up">
                    <img src="{{asset('frontend/assets/images/download.png')}}" alt="app-img">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End New App Download Area -->

<!-- Start New Software Area -->
<div class="new-software-area ptb-100">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-12">
                <div class="new-software-list">
                    <img src="{{asset('frontend/assets/images/border.png')}}" alt="bg-shape">
                    <ul>
                        <li data-aos="fade-down"><img src="{{asset('frontend/assets/images/laravel.png')}}"
                                class="laravel" style="height:50px;width:50px;" alt="laravel"></li>
                        <li data-aos="fade-right"><img src="{{asset('frontend/assets/images/php.png')}}" class="php"
                                alt="php"></li>
                        <li data-aos="fade-up"><img src="{{asset('frontend/assets/images/dart.png')}}" class="dart"
                                style="height:50px;width:50px;" alt="dart"></li>
                        <li data-aos="fade-down"><img src="{{asset('frontend/assets/images/flutter.png')}}"
                                class="flutter" style="height:50px;width:50px;" alt="flutter"></li>
                        <li data-aos="fade-up"><img src="{{asset('frontend/assets/images/react.png')}}" class="react"
                                style="height:50px;width:50px;" alt="react"></li>
                        <li><img src="{{asset('frontend/assets/images/logo/logo_round.png')}}" class="frame"
                                alt="frame"></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 col-md-12">
                <div class="new-software-content">
                    <div class="big-text">Features</div>
                    <h5 class="text-primary">EXPLORE AMAZING FEATURES</h5>
                    <h1>That will boost your productivity</h2>
                        <p>With our wide range of features, you can create a custom web and app setup no matter what
                            your niche: startups, small business and all the rest!</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End New Software Area -->

<!-- Start New Feedback Area -->
<div class="new-feedback-area pb-100" id="trusted-user">
    <div class="container">
        <div class="section-title title-with-bg-text">
            <div class="big-title">Reviews</div>
            <h5 class="text-primary">TRUSTED USER</h5>
            <h2>A Word From Our Customers</h2>
            <p>Our passion drives us to work hard and deliver outstanding results so we can be the best app
                development company. Hear what our clients have to say about Fix HR.
            </p>
        </div>
        {{-- <div class="new-feedback-slides owl-carousel owl-theme"> --}}
              <div class="new-feedback-slides owl-carousel owl-theme">
            <div class="single-feedback-card">
                <div class="client-info">
                    <div class="d-flex align-items-center">
                        <img src="{{asset('frontend/assets/images/user2.jpg')}}" alt="user">
                        <div class="title">
                            <h3>Kesar Trucks Pvt Ltd</h3>
                            <span>Gondwara, Raipur</span>
                        </div>
                    </div>
                </div>
                <p>"Installation was pretty easy.We have been Fix HR customers for years, and we have had nothing
                    but amazing experiences with the Fix HR and well-designed mobile app. Fix HR provided that for
                    us with easy-to-use software and personalized support. I like this app. Thank you"</p>
                <div class="rating d-flex align-items-center justify-content-between">
                    <h5>Mr. L. Singh (SM)</h5>
                    <div>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                    </div>
                </div>
            </div>
            <div class="single-feedback-card">
                <div class="client-info">
                    <div class="d-flex align-items-center">
                        <img src="{{asset('frontend/assets/images/user2.jpg')}}" alt="user">
                        <div class="title">
                            <h3>Kesar Earth Solutions</h3>
                            <span>Raipur</span>
                        </div>
                    </div>
                </div>
                <p>"Installation was pretty easy.We have been Fix HR customers for years, and we have had nothing
                    but amazing experiences with the Fix HR and well-designed mobile app. Fix HR provided that for
                    us with easy-to-use software and personalized support. I like this app. Thank you"</p>
                <div class="rating d-flex align-items-center justify-content-between">
                    <h5>Ms. Nidhi Sangeria(HR)</h5>
                    <div>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                    </div>
                </div>
            </div>
            {{-- <div class="single-feedback-card">
                <div class="client-info">
                    <div class="d-flex align-items-center">
                        <img src="{{asset('frontend/assets/images/user3.jpg')}}" alt="user">
                        <div class="title">
                            <h3>Prashant Kumar</h3>
                            <span>Flutter Developer</span>
                        </div>
                    </div>
                </div>
                <p>"Installation was pretty easy.We have been Fix HR customers for years, and we have had nothing
                    but amazing experiences with the Fix HR and well-designed mobile app. Fix HR provided that for
                    us with easy-to-use software and personalized support. I like this app. Thank you"</p>
                <div class="rating d-flex align-items-center justify-content-between">
                    <h5>Responsive Design</h5>
                    <div>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-line"></i>
                    </div>
                </div>
            </div>
            <div class="single-feedback-card">
                <div class="client-info">
                    <div class="d-flex align-items-center">
                        <img src="{{asset('frontend/assets/images/user4.jpg')}}" alt="user">
                        <div class="title">
                            <h3>Mitesh Kumar</h3>
                            <span>Dart Developer</span>
                        </div>
                    </div>
                </div>
                <p>"Installation was pretty easy.We have been Fix HR customers for years, and we have had nothing
                    but amazing experiences with the Fix HR and well-designed mobile app. Fix HR provided that for
                    us with easy-to-use software and personalized support. I like this app. Thank you"</p>
                <div class="rating d-flex align-items-center justify-content-between">
                    <h5>Design Quality</h5>
                    <div>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-fill"></i>
                        <i class="ri-star-half-line"></i>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>

    <div class="new-feedback-shape">
        <img src="{{asset('frontend/assets/images/shape.png')}}" alt="image">
    </div>
</div>
<!-- End New Feedback Area -->

<!-- Start Page Title Area -->
<div class="page-title-area" id="faq">
    <div class="container">
        <div class="page-title-content section-title">
            <h5 class="text-light">TAKE A LOOK</h5>
            <h2>Frequently Asked Questions</h2>
            <p class="text-light">Our Mobile App can be downloaded and installed on your compatible mobile device
                easily. If
                you have any questions - please look through the most frequently asked questions or contact
                us for more details.</p>
        </div>
    </div>
    <div class="divider"></div>
    <div class="lines">
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
    </div>
</div>
<!-- End Page Title Area -->

<!-- Start FAQ Area -->
<div class="faq-area ptb-100">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-12">
                <div class="faq-sidebar">
                    <img src="{{asset('frontend/assets/images/progress.png')}}" alt="app-img">
                </div>
            </div>
            <div class="col-lg-8 col-md-12">
                <div class="faq-accordion accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">Is the
                            Mobile App Secure?</button>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p><strong>Fix HR</strong> mobile app uses strong encryption, user authentication,
                                    and access
                                    controls to ensure secure attendance management, complying with privacy
                                    regulations and industry standards.</p>

                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">What
                            features does the Mobile
                            App have?</button>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>An attendance management system is software that tracks the working hours of
                                    employees.
                                    It does precise time tracking for attendance, breaks, the time off taken, clock
                                    in and clock out, by your employee.
                                    It prevents any type of error in a record. It makes your attendance management
                                    precise and efficient. In a Fix HR
                                    attendance management system, your employees can mark their time and attendance
                                    in the mobile app.
                                    The software automates your attendance management, so the data should be
                                    available to the HR department in real-time to do the precise payroll and your
                                    employees should be compensated for their time.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">How
                            do I get the Mobile App for my phone?</button>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>First you need to setup Fix HR web application and then download Fix HR mobile
                                    application from the Play Store or IOS Store to explore Fix HR attendance
                                    features. Both the Mobile Apps and the Mobile Web App give you the ability to
                                    you to access your account information,
                                    view notice releases, request report, and contact us via email or phone. Once
                                    you've installed a Mobile App on your phone,
                                    you'll also have ability to setup web application to view a map of our offices
                                    and branch locations.</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">How
                            does Fix HR differ from
                            usual apps?</button>
                        <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>
                                    Fix HR web and mobile application provides multiple attendance features such as
                                    facial recognization,
                                    QR code, and selfie based attendance mode in a single plateform. It also
                                    provides realtime and error free time tracking attendance Solutions
                                    with mispunch, leave and gatepass features which make us different from the
                                    other tech orgnization.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- End FAQ Area -->

<!-- Start Contact Area -->
<div class="contact-area ptb-100" id="contact-us">
    <div class="container">
        <div class="section-title">
            <h5 class="text-primary">CONTACT US</h5>
            <h2>Get in Touch</h2>
            <p>If you have any questions, just fill in the contact form, and we will answer you shortly.</p>
        </div>
        <div class="contact-form">
            <form action="#" method="get">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <div class="form-group">
                            <input type="text" name="name" class="form-control" id="name" required=""
                                data-error="Please enter your name" placeholder="Enter your name">
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <div class="form-group">
                            <input type="email" name="email" class="form-control" id="email" required=""
                                data-error="Please enter your email" placeholder="Enter your email">
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <div class="form-group">
                            <input type="text" name="phone_number" class="form-control" id="phone_number" required=""
                                data-error="Please enter your phone number" placeholder="Enter your phone number">
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <div class="form-group">
                            <input type="text" name="msg_subject" class="form-control" id="msg_subject"
                                placeholder="Enter your subject" required="" data-error="Please enter your subject">
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="form-group">
                            <textarea name="message" id="message" class="form-control" cols="30" rows="6" required=""
                                data-error="Please enter your message" placeholder="Enter message..."></textarea>
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <button type="submit" class="default-btn"><i class="bx bx-paper-plane"></i> Send
                            Message</button>
                        <div id="msgSubmit" class="h3 text-center hidden"></div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="maps">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3819.4917839913174!2d81.62110337492324!3d21.28961407389733!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a28e782e9987bc5%3A0xd8b880633cdeccdd!2sFixingDots!5e0!3m2!1sen!2sin!4v1695742953035!5m2!1sen!2sin"
            width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>


    </div>
</div>
<!-- End Contact Area -->

<!-- Start New Free Trial Area -->
<div class="new-free-trial-area">
    <div class="container">
        <div class="new-free-trial-inner-box">
            <div class="row align-items-center">
                <div class="col-lg-9 col-md-9">
                    <div class="new-free-trial-content">
                        <h5 class="text-primary text-center">BE THE FIRST TO KNOW</h5>
                        <h2 class="mb-0 text-center">About New Features</h2>
                        <p class="text-light text-center">If you want to receive monthly updates from us just pop
                            your email in the box.</p>
                        <form class="free-trial-form">
                            <input type="text" class="input-newsletter" placeholder="Enter Your Email Address"
                                name="email">
                            <button type="submit" class="default-btn">SUBSCRIBE</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-3 col-md-3">
                    <div class="new-free-trial-image">
                        <img src="{{asset('frontend/assets/images/free-trial.png')}}" alt="image">
                    </div>
                </div>
            </div>

            <div class="new-free-trial-shape">
                <img src="{{asset('frontend/assets/images/shape_1.png')}}" alt="image">
            </div>
        </div>
    </div>
</div>
<!-- End New Free Trial Area -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
    // Handle form submit event
    $('#myForm').submit(function(event) {
        event.preventDefault();  // Prevent the form from reloading the page

        // Get user input from the text field
        var userInput = $('#user-input').val();
        console.log('userInput: ',userInput);


        // Check if the input is empty
        if (userInput.trim() === "") {
            alert("Please enter a message.");
            return;
        }

        // AJAX call to send the data to the server
        $.ajax({
            url: '/ai-chat',  // The URL to send the request to
            type: 'POST',  // HTTP method
            data: {
                msg: userInput,  // Send the user input as msg
                _token: '{{ csrf_token() }}'  // CSRF token (if you're using Laravel)
            },
            success: function(response) {
                // Handle successful response from server
                console.log('Response from server:', response);
                // Append the user message and server response to the chat box
                var userMessage = '<div class="user-message">'+'YOU: ' + userInput + '</div>';
                var serverMessage = '<div class="server-message">'+'DOTFAI: ' + response.message + '</div>';  // Assuming response contains a 'message' field

                // Add both messages to the chat box
                $('#chat-box').append(userMessage + serverMessage);

                // Optionally, you can update the UI with the response
                // $('#response-container').html(response);
            },
            error: function(xhr, status, error) {
                // Handle error response
                console.log('Error:', error);
            }
        });

        // Optionally, clear the input field after sending the request
        $('#user-input').val('');
    });
});

</script>
<script>
    function toggleChatForm() {
        var chatContainer = document.getElementById('chat-container');
        if (chatContainer.style.display === "none" || chatContainer.style.display === "") {
            chatContainer.style.display = "block";  // Show the chat form
        } else {
            chatContainer.style.display = "none";   // Hide the chat form
        }
    }
</script>
@endsection
