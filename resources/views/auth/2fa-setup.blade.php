@extends('auth.layout')

@section('content')
<div class="container">
    <h2>Two-Factor Authentication Setup</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card mb-3">
        <div class="card-body">
            <p>Scan this QR code with your Google Authenticator app:</p>
            <div>{!! $qrImage !!}</div>
            <p>Or enter this secret manually: <strong>{{ $secret }}</strong></p>
            <form method="POST" action="{{ route('2fa.enable') }}">
                @csrf
                <input type="hidden" name="secret" value="{{ $secret }}">
                <div class="form-group mt-3">
                    <label for="otp">Enter the 6-digit code from your app:</label>
                    <input type="text" name="otp" id="otp" class="form-control" required maxlength="6">
                </div>
                <button type="submit" class="btn btn-primary mt-2">Enable 2FA</button>
            </form>
        </div>
    </div>
</div>
@endsection 