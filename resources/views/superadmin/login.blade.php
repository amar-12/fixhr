@extends('auth.layout')
@section('content')
    {{-- @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif --}}
    {{-- @if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif --}}


    <div class="container">

        <div class="sign-in-container">
            <form method="post" action="{{ route('superadmin.login') }}" id="pwdForm">
                @csrf
                <h1>Sign In</h1>
                <span class="spantag">Use email and password for login.</span>

                <div class="input-grou">
                    <div class="icon">
                        <i class="fa fa-envelope"></i>
                    </div>
                    <input type="email" id="email2" name="email" placeholder="Email">
                    @error('email')
                        <span class="spantag" style="color: red">{{ $message }}</span>
                    @enderror
                </div>
                <div class="input-grou">
                    <div class="icon">
                        <i class="fa fa-key"></i>
                    </div>
                    <div class="icon1">
                        <i class="fa fa-eye-slash" style="cursor: pointer" onclick="passwordToggle(this)"></i>
                    </div>
                    <input type="password" id="password" name="password" placeholder="Password">
                    @error('password')
                        <span class="spantag" style="color: red">{{ $message }}</span>
                    @enderror
                </div>
                <span class="spantag" style="color: red" id="pwdError"></span>
                <button class="form_btn" type="submit" style="background-color: #1877f2; color:#ebecf0"
                    id="loginWithPassBtn">Log In</button>
                <span class="spantag"><a href="">Forget your password ?</a></span>
                <span class="spantag text-secondary">I don't have any account ?
                </span>
            </form>
        </div>

    </div>
@endsection

@section('script')
    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
                confirmButtonColor: '#3085d6'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
                confirmButtonColor: '#d33'
            });
        @endif
    </script>
@endsection
