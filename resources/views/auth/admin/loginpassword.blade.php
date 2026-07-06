@extends('auth/admin/authlayout.master')
@section('title', 'Login')
@section('css')
    <link href="{{ asset('assets/plugins/icons/icons.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="card">
        <div class="row align-items-center">
            <div class="col-md-12">
                <div class="card-body">
                    <img src="{{ asset('assets/logo/logo.png') }}" alt="" class="img-fluid mb-4">

                    <h1 class="h3  mb-3">Welcome to <span style="color: black"><b>Fix<span
                                    style="color: #1877F2">HR</span></b></span></h1>
                    <p class="h5 font-weight-normal mb-4 leading-normal">Make Your Human Resource Online</p>
                    <h4 class="mb-3 f-w-400"> <b>Sign In</b></h4>
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
                        {{-- <div class="input-group my-2">
                        <input type="password" class="form-control" placeholder="Enter Your Password" name="password" required>
                    </div> --}}
                        <div class="text-start">
                            @if (Session::has('Fail'))
                                <span class="text-danger fs-14"><i
                                        class="fa fa-warning mx-1"></i>{{ Session::get('Fail') }}</span>
                            @endif
                        </div>
                        <button class="btn btn-block btn-primary mt-3 mb-4 rounded" style="background-color:#1877F2"
                            type="submit">Login</button>
                    </form>
                    {{-- <p class="mb-0 text-muted">Don’t have any Account? <a href="{{ url('/signup') }}" class="f-w-400" style="color:#1877F2">SignUp</a></p> --}}
                    <p class="mb-0 text-muted">I forgot my password ? <a
                            href="{{ url('/login/forget-password-user-name') }}" class="f-w-400"
                            style="color:#1877F2">Forget</a></p>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="text-center">
        <div class="saprator my-2"><span>OR</span></div>
        <button class="btn text-white bg-facebook mb-2 mr-2  wid-40 px-0 hei-40 rounded-circle"><i
                class="fab fa-facebook-f"></i></button>
        <button class="btn text-white bg-googleplus mb-2 mr-2 wid-40 px-0 hei-40 rounded-circle"><i
                class="fab fa-google-plus-g"></i></button>
        <button class="btn text-white bg-twitter mb-2  wid-40 px-0 hei-40 rounded-circle"><i
                class="fab fa-twitter"></i></button>
    </div> --}}

    <script>
        function passwordToggel() {
            var eyeType = document.getElementById('passwordEye');
            var InputBox = document.getElementById('passwordInput');

            if (eyeType.classList.value == 'fe fe-eye-off') {
                eyeType.classList.value = 'fe fe-eye';
                InputBox.type = 'text';

            } else {
                eyeType.classList.value = 'fe fe-eye-off';
                InputBox.type = 'password';
            }
        }
    </script>

@endsection
