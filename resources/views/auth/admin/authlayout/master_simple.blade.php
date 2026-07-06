<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta content="FixingDots - It is one of the Major Dashboard Template which includes - HR, Employee and Job Dashboard. This template has multipurpose HTML template and also deals with Task, Project, Client and Support System Dashboard." name="description">
    <meta content="Fixing Dots" name="author">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="keywords" content="admin dashboard, admin panel template, html admin template, dashboard html template, bootstrap 5 dashboard, template admin bootstrap 5 , simple admin panel template, simple dashboard html template,  bootstrap admin panel, task dashboard, job dashboard, bootstrap admin panel, dashboards html, panel in html, bootstrap 5 dashboard, bootstrap 5 dashboard, bootstrap5 dashboard"/>

    <!-- TITLE -->
    <title>FixHR | @yield('title')</title>

    <!-- APP-HEAD -->

    @include('auth.admin.authlayout.head_simple')
    <!----hatasactahy -->

    @yield('css')
</head>

<body class="app sidebar-mini ltr">
    @include('sweetalert::alert')

    <div class="page">
        <div class="page-main">
                <div class="side-app main-container pt-3">
                    {{-- MAIN CONTENT --}}
                    @yield('content')
                    {{-- END MAIN CONTENT --}}

                </div>
        </div>

    </div>
    @include('auth.admin.authlayout.script-simple')
    @yield('script')
</body>

</html>
