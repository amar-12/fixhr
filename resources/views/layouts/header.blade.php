<div class="navbar-area pakap-new-navbar-area">
    <div class="pakap-responsive-nav">
        <div class="container">
            <div class="pakap-responsive-menu">
                <div class="logo">
                    <a href="#"><img src="{{ asset('frontend/assets/images/black-logo.png') }}" alt="logo" style="height: 3rem"></a>
                </div>
            </div>
        </div>
    </div>
    <div class="pakap-nav">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <a class="navbar-brand" href="#">
                    <img src="{{ asset('frontend/assets/images/black-logo.png') }}" alt="logo" style="height: 3rem">
                </a>
                <div class="collapse navbar-collapse mean-menu">
                    <ul class="navbar-nav">
                        <li class="nav-item"><a href="#home-section" class="nav-link">Home</a></li>
                        <li class="nav-item"><a href="#features-section" class="nav-link">Features</a></li>
                        <li class="nav-item"><a href="#screenshot" class="nav-link">ScreenShots</a></li>
                        {{-- <li class="nav-item"><a href="#pricing" class="nav-link">Pricing</a></li> --}}
                        <li class="nav-item"><a href="#contact-us" class="nav-link">Contact Us</a></li>
                        <li class="nav-item"><a href="{{ url('/login') }}" class="nav-link">Login</a></li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</div>

