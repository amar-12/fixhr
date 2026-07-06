@extends('auth/admin/authlayout.master')
@section('title', 'Login')
@section('css')
    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.css') }}" rel="stylesheet" />

    <!-- STYLE CSS -->
    <link href="{{ asset('assets/css/style.css?V=1.2') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/plugins.css') }}" rel="stylesheet" />

    <!-- ANIMATE CSS -->
    <link href="{{ asset('assets/css/animated.css') }}" rel="stylesheet" />

    <!---ICONS CSS -->
    <link href="{{ asset('assets/plugins/icons/icons.css') }}" rel="stylesheet" />

    <!-- INTERNAL SWITCHER CSS -->
    <link href="{{ asset('assets/switcher/css/switcher.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/switcher/demo.css') }}" rel="stylesheet" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <style>
        .transition {
            transition: 1s;
        }
    </style>
@endsection
@section('contentes')

    <body id="my-view">
        <div class="card col-md-12 col-lg-12 transition">
            {{-- <img src="{{ asset('assets/logo/logo.png') }}" alt="" class="img-fluid mb-4">

            <h1 class="h3  mb-3">Welcome to <span style="color: black"><b>Fix<span
                            style="color: #1877F2">HR</span></b></span></h1>
            <p class="h5 font-weight-normal mb-4 leading-normal">Make Your Human Resource Online</p>

            <div class="row align-items-center px-5">
                <div id="pwdBtn" class="transition col-6 text-center bg-primary border border-primary" style="cursor: pointer"
                    onclick="triggerRoute(1)">
                    <div class="p-2">
                        <span><b>With Password</b></span>
                    </div>
                </div>
                <div id="otpBtn" class=" transition col-6 text-center border border-primary" style="cursor: pointer" onclick="triggerRoute(2)">
                    <div class="p-2">
                        <span><b>With OTP</b></span>
                    </div>
                </div>
            </div>
            <h4 class="mb-3 f-w-400 mt-5" > <b class="transition" id="loginTitle">Sign In with Password</b></h4>
            <div class="row align-items-center transition px-3">
                <div class="col-md-12 transition" id="lognByPassword">
                    <form method="POST" action="{{ route('login.otp') }}">
                        @csrf
                        <div class="input-group my-2">
                            <input type="email" class="form-control" placeholder="Enter Your Email ID" name="email"
                                required>
                        </div>
                        <div class="input-group mb-4">
                            <div class="input-group" id="Password-toggle">
                                <a href="#" class="input-group-text" onclick="passwordToggel()">
                                    <i class="fe fe-eye-off" id="passwordEye" aria-hidden="true"></i>
                                </a>
                                <input type="password" id="passwordInput" class="form-control"
                                    placeholder="Enter Your Password" name="password" required>
                            </div>
                        </div>

                        <div class="text-start">
                            @if (Session::has('Fail'))
                                <span class="text-danger fs-14"><i
                                        class="fa fa-warning mx-1"></i>{{ Session::get('Fail') }}</span>
                            @endif
                        </div>
                        <button class="btn btn-block btn-primary mt-3 mb-4 rounded" style="background-color:#1877F2"
                            type="submit">Login</button>
                    </form>
                    <div class="d-flex justify-content-between my-2">
                        <p class="mb-0 text-muted"> <a
                            href="{{ url('/login/forget-password-user-name') }}" class="f-w-400"
                            style="color:#1877F2">Forget</a></p>
                            <p class="mb-0 text-muted"><a href="{{ url('/signup') }}" class="f-w-400"
                                style="color:#1877F2">SignUp</a></p>
                    </div>
                </div>
                <div class="col-md-12 d-none transition" id="loginByOTP">
                    <form method="POST" action="{{ route('login.otp') }}">
                        @csrf
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Enter Your Email ID" name="email"
                                required>
                        </div>
                        <div class="text-start">
                            @if (Session::has('Fail'))
                                <span class="text-danger fs-14"><i
                                        class="fa fa-warning mx-1"></i>{{ Session::get('Fail') }}</span>
                            @endif
                        </div>
                        <button class="btn btn-block btn-primary mt-3 mb-4 rounded" style="background-color:#1877F2"
                            type="submit">Send OTP</button>
                    </form>
                </div>
            </div> --}}

            <div class="p-5">
                <h1 class="fw-bolder">Login Fix<span style="color: #1877F2">HR</span></h1>
                <p class="text-muted fs-14">Choose the way you want to login with password or OTP.</p>
                <div class="row">
                    <div class="col-6 text-center" style="cursor: pointer" onclick="triggerRoute(1)">
                        <div class="bg-primary p-2">
                            <span>With Password</span>
                        </div>
                    </div>
                    <div class="col-6 text-center" style="cursor: pointer" onclick="triggerRoute(2)">
                        <div class="border border-primary p-2">
                            <span>With OTP</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- <div class="col-md-12 col-sm-6 col-lg-12  owner-card p-0" id="" style="cursor: pointer" onclick="triggerRoute(1)">
                    <div class="offer offer-radius offer-primary">
                        <div class="offer-content">
                            <h3 class="lead font-weight-semibold"><b>Login With Password</b></h3>
                            <p>
                                To Record Attendance Details of My Employees
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-sm-6 col-lg-12  owner-card p-0" id="" style="cursor: pointer"
                    onclick="triggerRoute(2)">
                    <div class="offer offer-radius offer-primary">
                        <div class="offer-content">
                            <h3 class="lead font-weight-semibold"><b>Login With OTP</b></h3>
                            <p>
                                To Record Attendance Details of My Employees
                            </p>
                        </div>
                    </div>
                </div> --}}

            <p class="mb-0 text-muted">Don’t have any Account? <a href="{{ url('/signup') }}" class="f-w-400"
                    style="color:#1877F2">SignUp</a></p>

        </div>
    </body>
    <script>
        // function triggerRoute(way) {
        //     var pssword = document.getElementById('lognByPassword');
        //     var otp = document.getElementById('loginByOTP');
        //     var title = document.getElementById('loginTitle');
        //     var PwdBtn = document.getElementById('pwdBtn');
        //     var OtpBtn = document.getElementById('otpBtn');

        //     if (way === 1) {
        //         pssword.classList.remove('d-none');
        //         otp.classList.add('d-none');

        //         title.innerHTML = 'Sign In with Password';

        //         PwdBtn.classList.add('bg-primary');
        //         OtpBtn.classList.remove('bg-primary');
        //     } else if (way === 2) {
        //         otp.classList.remove('d-none');
        //         pssword.classList.add('d-none');

        //         title.innerHTML = 'Sign In with OTP';

        //         PwdBtn.classList.remove('bg-primary');
        //         OtpBtn.classList.add('bg-primary');
        //     }


        // }

        function triggerRoute(way) {
            if (way === 1) {
                window.location.href = '/login/with-password';
            } else if (way === 2) {
                window.location.href = '/login/with-otp';
            } else {
                alert('Invalid route');
            }
        }
    </script>

@endsection
