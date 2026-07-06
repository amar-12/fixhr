@extends('auth/admin/authlayout.master')
@section('title', 'Login')
@section('content')
    <div class="card">
        <div class="row align-items-center">
            <div class="col-md-12">
                <div class="card-body">
                    <img src="{{ asset('assets/logo/logo.png') }}" alt="" class="img-fluid mb-4">

                    {{-- <h1 class="h3  mb-3">Welcome to <span style="color: black"><b>Fix<span style="color: #1877F2">HR</span></b></span></h1> --}}
                    <p class="h5 font-weight-normal mb-4 leading-normal">Reset Your Password</p>
                    {{-- <h4 class="mb-3 f-w-400"> <b>Sign In</b></h4> --}}
                    <form method="POST" action="{{ route('forget.password') }}">
                        @csrf
                        <div class="input-group my-2">
                            <input type="text" class="form-control" id="password1" onchange="checkPassword(this)"
                                placeholder="Enter Password" name="password" required>
                        </div>
                        <div class="input-group my-2">
                            <input type="password" class="form-control" id="cpassword" placeholder="Confirm Password"
                                name="confirm_password" onchange="checkPassword(this)" required>
                        </div>
                        <input type="text" value="{{ $userId }}" name="UserId" hidden>
                        <input type="text" value="{{ $Business_id }}" name="business_id" hidden>
                        <div class="" style="text-align:left">
                            <span class="text-danger fs-14" id="confirmError"><i class="fa fa-warning mx-1"></i></span>
                        </div>
                        <button class="btn btn-block btn-primary mt-3 mb-4 rounded" id="submitBtn"
                            style="background-color:#1877F2" type="button">Reset Password</button>
                    </form>
                    {{-- <p class="mb-0 text-muted">Don’t have any Account? <a href="{{ url('/signup') }}" class="f-w-400" style="color:#1877F2">SignUp</a></p> --}}
                    {{-- <p class="mb-0 text-muted">I forgot my password ? <a href="{{ url('/login/forget-password-user-name') }}" class="f-w-400" style="color:#1877F2">Forget</a></p> --}}
                </div>
            </div>
        </div>
    </div>

    <div class="text-center">
        <div class="saprator my-2"><span>OR</span></div>
        <button class="btn text-white bg-facebook mb-2 mr-2  wid-40 px-0 hei-40 rounded-circle"><i
                class="fab fa-facebook-f"></i></button>
        <button class="btn text-white bg-googleplus mb-2 mr-2 wid-40 px-0 hei-40 rounded-circle"><i
                class="fab fa-google-plus-g"></i></button>
        <button class="btn text-white bg-twitter mb-2  wid-40 px-0 hei-40 rounded-circle"><i
                class="fab fa-twitter"></i></button>
    </div>

    <script>
        document.getElementById('password1').value = '';
        document.getElementById('cpassword').value = '';

        function checkPassword(e) {
            var password = document.getElementById('password1');
            var password1 = document.getElementById('cpassword');
            var cpassword = e.value;

            if (e.value.length < 8) {
                document.getElementById('confirmError').innerHTML = 'Your password should be greater than 8 character**';
            } else if (password.value != cpassword) {
                document.getElementById('confirmError').innerHTML = 'Password Not Matchng**';
            } else {
                document.getElementById('confirmError').innerHTML = '';
                document.getElementById('submitBtn').type = 'submit';
            }
        }
    </script>

@endsection
