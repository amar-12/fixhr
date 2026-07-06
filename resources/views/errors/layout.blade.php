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

        <!-- FAVICON  QA Changes-->
        <link rel="icon" href="{{ asset('assets/logo/f_fav.ico') }}" type="image/x-icon" />

        <!-- STYLE CSS -->
        <link href="{{asset('assets/css/style.css')}}" rel="stylesheet" />

		<!---ICONS CSS -->
		<link href="{{ asset('assets/plugins/icons/icons.css')}}" rel="stylesheet" />

    </head>

	<body class="login-img">
        <div class="page responsive-log error-bg">
            <div class="page-content m-0">
                @yield('content')
            </div>
        </div>
    </body>
</html>
