<!doctype html>

<html lang="en">

<head>
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

    <title>FixHR - @yield('title')</title>
    @include('auth/admin/authlayout.head_simple')
    @yield('css')


    <script src="{{ config('app.cdn') }}"></script>
</head>

<body>
    <div class="auth-wrapper">
        @include('sweetalert::alert')
        @yield('content')
    </div>

    @yield('js')

    <!-- JQUERY JS -->
    <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>

    <!-- BOOTSTRAP JS-->
    <script src="{{ asset('assets/plugins/bootstrap/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>

    <!-- SELECT2 JS -->
    <script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>

    <!--STICKY JS -->
    <script src="{{ asset('assets/js/sticky.js') }}"></script>

    <!-- COLOR THEME JS-->
    <script src="{{ asset('assets/js/themeColors.js') }}"></script>

    <!-- CUSTOM JS -->
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    <!-- SWITCHER -->
    <script src="{{ asset('assets/switcher/js/switcher.js') }}"></script>

    <!-- INTERNAL FORM ADVANCED ELEMENT JS -->
    <script src="{{ asset('assets/js/form-elements.js') }}"></script>
    <script src="{{ asset('assets/js/select2.js') }}"></script>

    <!-- INTERNAL FILE-UPLOADS JS -->
    <script src="{{ asset('assets/plugins/fancyuploder/jquery.ui.widget.js') }}"></script>
    <script src="{{ asset('assets/plugins/fancyuploder/jquery.fileupload.js') }}"></script>
    <script src="{{ asset('assets/plugins/fancyuploder/jquery.iframe-transport.js') }}"></script>
    <script src="{{ asset('assets/plugins/fancyuploder/jquery.fancy-fileupload.js') }}"></script>
    <script src="{{ asset('assets/plugins/fancyuploder/fancy-uploader.js') }}"></script>
    <!-- INTERNAL FILE-UPLOADS JS -->
    <script src="{{ asset('assets/plugins/fileupload/js/dropify.js') }}"></script>
    <script src="{{ asset('assets/js/filupload.js') }}"></script>


</body>

</html>
