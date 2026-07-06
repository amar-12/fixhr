<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta
        content="Fix HR is a comprehensive attendance management system that offers modern businesses detailed time tracking, flexible attendance methods, leave management, gate pass management, and TA/DA integration. With automated reports and real-time data, Fix HR improves accuracy, enhances efficiency, reduces costs, and ensures compliance."
        name="content">
    <meta
        content="Fix HR: Advanced time and expense tracking solution for modern businesses. Streamline attendance, leave management, gate pass requests, and TA/DA claims with detailed tracking, flexible methods, and automated reports for improved accuracy and efficiency."
        name="description">
    <meta content="Fixing Dots" name="author">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="keywords"
        content="Fix HR, time tracking, attendance management, leave management, gate pass, TA/DA integration, automated reports, payroll processing, workforce management, HR software, business efficiency, real-time tracking, compliance, expense tracking" />

    <!-- TITLE -->
    <title>FixHR | @yield('title')</title>


    <!-- APP-HEAD -->
    @include('superadmin.layout.head') 
    <!----hatasactahy -->

    {{-- @vite('resources/css/app.css')
    @vite('resources/js/app.js') --}}

    @yield('css')

    <style>
        /**************** for scoller css in siderbar start */
        .app-sidebar3 {
            overflow-y: auto;
            height: 100%;
        }

        /* Webkit Browsers (Chrome, Safari) */
        .app-sidebar3::-webkit-scrollbar {
            width: 1px;
        }

        .app-sidebar3::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .app-sidebar3::-webkit-scrollbar-thumb {
            background: #888;
        }

        .app-sidebar3::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Firefox */
        .app-sidebar3 {
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
        }

        /********************** for scoller css in siderbar end */


        body *::-webkit-scrollbar-thumb,
        body *:hover::-webkit-scrollbar-thumb {
            color: #f1f4fb;
            background: #1877f2;
        }

        body *::-webkit-scrollbar {
            width: 5px;
            height: 8px;
            -webkit-transition: 0.3s;
            transition: 0.3s;
        }

        /* span[aria-hidden="true"] {
            color: white;
        } */
        tr {
            line-height: 1.5;
        }

        #lottieAnimationContainer {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            /* Ensure it's above other content if needed */
        }

        #lottieAnimationContainer iframe {
            width: 200px;
            /* Adjust width as needed */
            height: 200px;
            /* Adjust height as needed */
            border: none;
            /* Remove iframe border */
        }

        .menu-bg {
            background-color: #1034A6;
            border-bottom-right-radius: 7px;
            border-bottom-left-radius: 7px;
            margin-top: -7px;
            padding-top: 15px;
        }

        .custom-pagination {
            display: flex;
            list-style-type: none;
        }

        .custom-pagination li {
            margin: 0 0px;
            cursor: pointer;
        }

        .custom-pagination li.active {
            font-weight: bold;
        }

        .custom-pagination li.disabled {
            pointer-events: none;
            color: #ccc;
        }

        .custom-heighlight {
            width: !important 100%;
            height: !important 40px;
            border-color: !important #dee6f5;
        }

        .custom-accordion-button {
            height: 40px;
            /* Adjust the height as needed */
            padding: 0.25rem 1.25rem;
            font-size: 0.875rem;

            text-align: left;
            width: 100%;
        }

        .dropdown-menu-export {
            min-width: 0;
            /* Override Bootstrap default min-width */
        }

        #gloabal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gloabal-spinner {
            border: 16px solid #f3f3f3;
            /* Light grey */
            border-top: 16px solid #1034A6;
            /* Blue */
            border-radius: 50%;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .custom-control.custom-checkbox {
            display: flex;
            align-items: center;
        }

        .custom-control.custom-checkbox .custom-control-input {
            margin-right: 0.5rem;
        }

        .accordion-body .d-flex.justify-content-end {
            justify-content: flex-end;
        }
        .accordion-item, .accordion-button, .accordion-body {
            transition: background-color 0.3s, color 0.3s;
        }/* Dark Mode Styles */
        .dark-mode .accordion-item,
        .dark-mode .accordion-button,
        .dark-mode .accordion-body,
        .dark-mode .modal-footer {
            background-color: #25274a;
            color: #fff;
        }

        .dark-mode .accordion-item { background-color: #1c1e3d; }
        .dark-mode .accordion-button { background-color: #2c2c70; }
        .dark-mode .accordion-body { background-color: #24264a; }
        body.dark-mode *::-webkit-scrollbar-thumb, body.dark-mode *:hover::-webkit-scrollbar-thumb {
            background: #1877f2;
        }

        /* Light Mode Styles */
        .light-mode .accordion-item,
        .light-mode .accordion-button,
        .light-mode .accordion-body,
        .light-mode .modal-footer {
            background-color: #fff;
            color: #000;
        }

        .light-mode .accordion-button { background-color: #f8f9fa; }
        .light-mode .accordion-body { background-color: #fff; }

        .SumoSelect > .CaptionCont > span.placeholder {
            color: #bcc0e2;
            font-style:normal;
        }

        .SumoSelect .CaptionCont .placeholder {
            background: transparent;
        }
    </style>
</head>

<body class="app sidebar-mini ltr">
    @include('sweetalert::alert')

    <input type="text" style="display:none" autocomplete="username" />
    <!--Global-Overlay-->
    <div id="gloabal-overlay" style="display:none;">
        <div class="gloabal-spinner"></div>
    </div>
    <!---->

    <!--- GLOBAL-LOADER -->
    <div id="global-loader">
        <div id="lottieAnimationContainer"><iframe
                src="https://lottie.host/embed/fad75f9c-37b5-4957-9393-fd4894af42df/HaHmy3Jrsi.json"></iframe></div>
    </div>
    <!--- END GLOBAL-LOADER -->

    <div class="page">
        <div class="page-main">

            <!-- APP-HEADER -->
            @include('superadmin.layout.header')
            <!-- APP-HEADER CLOSED -->

            <!-- APP-SIDEBAR -->
            @include('superadmin.layout.sidebar')
            <!-- APP-SIDEBAR CLOSED -->

            <div class="app-content main-content mt-0">

                <div class="side-app main-container pt-3">

                    {{-- MAIN CONTENT --}}
                    @yield('content')
                    {{-- END MAIN CONTENT --}}

                </div>
            </div><!-- end app-content-->
        </div>

        <!-- FOOTER -->
        @include('superadmin.layout.footer')
        <!-- END FOOTER -->

    </div>
    <!-- BACK TO TOP -->
    <a href="#top" id="back-to-top"><span class="feather feather-chevrons-up mt-4"></span></a>

    @include('superadmin.layout.script');

    @yield('script')
</body>

</html>
