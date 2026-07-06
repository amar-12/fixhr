
 <!-- APP-SIDEBAR -->
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
 <div class="sticky">
    <aside class="app-sidebar " >
        <div class="app-sidebar__logo">



                <img src="{{ asset('assets/logo/logo.png') }}" class="header-brand-img desktop-main-logo" alt="FixingDotslogo">
                <a href="{{ url('/dashboard') }}" style="display: flex; justify-content: center; align-items: center;">
                    <img src="{{ asset('assets/logo/logo_dark.png') }}" class="header-brand-img dark-logo" style="transform: translateY(-5px); max-width: 70%;" alt="FixingDotslogo">
                </a>
                <img src="{{ asset('assets/logo/logo_round.png') }}" class="header-brand-img mobile-logo" alt="FixingDotslogo">
                <img src="{{ asset('assets/logo/logo_round.png') }}" class="header-brand-img darkmobile-logo" alt="FixingDotslogo">
            </a>
        </div>
        <div class="app-sidebar3">
            <div class="main-menu">
                <div class="app-sidebar__user">
                    <div class="dropdown user-pro-body text-center">

                        <div class="user-info">
                            <h5 class=" mb-2">Khemsingh Nishad</h5>
                            <span class="text-muted app-sidebar__user-name text-sm">Super Admin</span>
                        </div>
                    </div>
                </div>
                <div class="slide-left disabled" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"><path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"/></svg></div>
                <ul class="side-menu">

                    <li class="slide">
                        <a class="side-menu__item" data-bs-toggle="slide"  href="{{route('superadmin.dashboard')}}">
                        <i class="fa-solid fa-house"></i>
                        <span class="side-menu__label">Dashboards</span></a>

                    </li>
                    <li class="slide">
                        <a class="side-menu__item" data-bs-toggle="slide"  href="{{route('superadmin.masters')}}">
                            <i class="fa-solid fa-database"></i>
                            <span class="side-menu__label">Masters</span>
                        </a>

                    </li>
                    <li class="slide">
                        <a class="side-menu__item"  href="{{url('superadmin/menus')}}">
                            <i class="fa-solid fa-bars"></i>
                            <span class="side-menu__label">Menus</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a class="side-menu__item" data-bs-toggle="slide"  href="{{route('module.dashboard')}}">
                            <i class="fa-solid fa-layer-group"></i>
                            <span class="side-menu__label">Modules</span>
                        </a>

                    </li>
                    {{-- <li class="slide">
                        <a class="side-menu__item" data-bs-toggle="slide"  href="javascript:void(0);">
                            <i class="fa-solid fa-signal"></i>
                            <span class="side-menu__label">Reports</span><i class="angle fa fa-angle-right"></i>
                        </a>
                    </li> --}}
                    <li class="slide">
                        <a class="side-menu__item" data-bs-toggle="slide"  href="javascript:void(0);">
                            <i class="fa-solid fa-gear"></i>
                        <span class="side-menu__label">Settings</span></a>
                    </li>
                    <li class="slide">
                        <a class="side-menu__item" href="{{ route('superadmin.privacy-policy-manage') }}">
                            <i class="fa fa-shield-alt"></i>
                            <span class="side-menu__label">Privacy Policy</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a class="side-menu__item" href="{{ route('superadmin.feedbacks.index') }}">
                            <i class="fa fa-comments"></i>
                            <span class="side-menu__label">Feedbacks</span>
                        </a>
                    </li>




            </div>
        </div>
    </aside>
</div>
<!-- APP-SIDEBAR CLOSED -->

<style>
    .side-menu__label {
    margin-left: 10px; /* Adjust this value to create the desired space */
}
</style>


