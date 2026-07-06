@extends('auth/admin/authlayout.master')
@section('title', 'Login')
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
                    <form id="loginForm" method="POST" action="{{ route('login.otp') }}">
                        @csrf
                        <div class="input-group">
                            <input type="text" value="true" class="form-control" placeholder="Enter Your Email ID" name="validator" hidden>
                            <input type="email" class="form-control" placeholder="Enter Your Email ID" name="email" id="userMail" required>
                        </div>
                        <div class="text-end d-none" id="errorDiv" style="text-align: left">
                            <span class="text-danger fs-14 mx-2" id="emailError"></span>
                        </div>
                        <button class="btn btn-block btn-primary mt-3 mb-4 rounded" style="background-color:#1877F2"
                            id="proceedBtn" onclick="checkUser(this)" type="button">Proceed</button>
                    </form>
                    <p class="mb-0 text-muted">Don't have any Account? <a href="{{ url('/signup') }}" class="f-w-400"
                            style="color:#1877F2">SignUp</a></p>
                </div>
            </div>
        </div>
    </div>
    <script>
        function checkUser(e) {
            var errordiv = document.getElementById('errorDiv');
            var error = document.getElementById('emailError');
            var btn = document.getElementById('proceedBtn');
            var mail = document.getElementById('userMail').value;

            if (mail) {

                $.ajax({
                    url: "{{ url('/login/check-user') }}", // Assuming this is the correct URL
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        _token: '{{ csrf_token() }}',
                        user: mail
                    }),
                    success: function(response) {
                        if (response.status) {
                            btn.type = 'submit';
                            error.innerHTML = '';
                            errordiv.classList.add('d-none');
                            document.getElementById('loginForm').submit();
                        }else{
                            btn.type = 'button';
                            error.innerHTML = '**Enter valid email';
                            errordiv.classList.remove('d-none');
                        }

                    }
                });

            }else{
                error.innerHTML = '**This field should not empty';
                errordiv.classList.remove('d-none');
            }

        }
    </script>
    {{-- <div class="text-center">
    <div class="saprator my-2"><span>OR</span></div>
    <button class="btn text-white bg-facebook mb-2 mr-2  wid-40 px-0 hei-40 rounded-circle"><i class="fab fa-facebook-f"></i></button>
    <button class="btn text-white bg-googleplus mb-2 mr-2 wid-40 px-0 hei-40 rounded-circle"><i class="fab fa-google-plus-g"></i></button>
    <button class="btn text-white bg-twitter mb-2  wid-40 px-0 hei-40 rounded-circle"><i class="fab fa-twitter"></i></button>
</div> --}}

@endsection
