<!DOCTYPE html>
<html>

<head>
    <title>FixHR | Sign In</title>
    <link rel="icon" href="{{ asset('assets/logo/f_fav.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="{{asset('assets/plugins/icons/icons.css')}}" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 100%;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #ebecf0;
            overflow: hidden;
        }

        .container {
            border-radius: 10px;
            box-shadow: -5px -5px 10px #fff,
                5px 5px 10px #babebc;
            position: absolute;
            width: 768px;
            min-height: 500px;
            overflow: hidden;
        }

        form {
            background: #ebecf0;
            display: flex;
            flex-direction: column;
            padding: 0 50px;
            height: 100%;
            justify-content: center;
            align-items: center;
        }

        form input {
            background: #eee;
            padding: 16px;
            margin: 8px 0;
            width: 85%;
            border: 0;
            outline: none;
            border-radius: 20px;
            box-shadow: inset 7px 2px 10px #babebc,
                inset -5px -5px 12px #ebecf0;
        }

        button {
            border-radius: 20px;
            border: none;
            outline: none;
            font-size: 12px;
            font-weight: bold;
            padding: 15px 45px;
            margin: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: transform 80ms ease-in;
        }

        /* .form_btn {
            box-shadow: -5px -5px 10px #ebecf0,
                5px 5px 8px #babebc;
        } */

        .form_btn:active {
            box-shadow: inset 1px 1px 2px #babebc,
                inset -1px -1px 2px #ebecf0;
        }

        .overlay_btn {
            background-color: #ebecf0;
            /* color: #ebecf0; */
            box-shadow: -5px -5px 10px #1f1a27,
                5px 5px 8px #1877f2;
        }

        .sign-in-container {
            position: absolute;
            left: 0;
            width: 50%;
            height: 100%;
            transition: all 0.5s;
        }

        .sign-up-container {
            position: absolute;
            left: 0;
            width: 50%;
            height: 100%;
            opacity: 0;
            transition: all 0.5s;
        }

        .overlay-left {
            display: flex;
            flex-direction: column;
            padding: 0 50px;
            justify-content: center;
            align-items: center;
            position: absolute;
            right: 0;
            width: 37%;
            height: 100%;
            opacity: 0;
            background-color: #1877f2;
            color: #ebecf0;
            transition: all 0.5s;
        }

        .overlay-right {
            display: flex;
            flex-direction: column;
            padding: 0 50px;
            justify-content: center;
            align-items: center;
            position: absolute;
            right: 0;
            width: 37%;
            height: 100%;
            background-color: #1877f2;
            color: #ebecf0;
            transition: all 0.5s;
        }

        .social-links {
            margin: 20px 0;
        }

        form h1 {
            font-weight: bold;
            margin: 0;
            color: #000;
        }

        p {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin: 20px 0 30px;
        }

        .spantag {
            font-size: 12px;
            color: #000;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .social-links div {
            width: 40px;
            height: 40px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin: 0 5px;
            border-radius: 50%;
            box-shadow: -5px -5px 10px #ebecf0,
                5px 5px 8px #babebc;
            cursor: pointer;
        }

        .social-links a {
            color: #000;
        }

        .social-links div:active {
            box-shadow: inset 1px 1px 2px #babebc,
                inset -1px -1px 2px #ebecf0;
        }

        .social-links a:hover {
            color: rgb(75, 248, 84);
        }

        .container.right-panel-active .sign-in-container {
            transform: translateX(100%);
            opacity: 0;
        }

        .container.right-panel-active .sign-up-container {
            transform: translateX(100%);
            opacity: 1;
            z-index: 2;
        }

        .container.right-panel-active .overlay-left {
            transform: translateX(-100%);
            opacity: 1;
            z-index: 2;
        }

        .container.right-panel-active .overlay-right {
            transform: translateX(-100%);
            opacity: 0;
        }

        /* wave  */
        .wave-div {
            display: absolute;
            width: 100%;
            bottom: 0;
        }

        .wrapper .bg-wave {
            width: 100%;
            position: absolute;
            bottom: 0;
            opacity: 0.5;
        }

        @media screen and (max-width: 1366px) and (max-height: 768px) {
            .wave-div {
                display: none;
            }
        }

        .input-grou {
            position: relative;
            width: 100%;
            /* margin-bottom: 20px; */
        }

        .icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-52%);
            color: rgb(122, 126, 122);
        }
        .icon1 {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-52%);
            color: rgb(122, 126, 122);
        }

        .input-grou input {
            padding-left: 30px;
            /* Adjust as needed based on your icon size */
        }
    </style>
</head>

<body>
    <div class="wrapper">


        <div class="wave-div">
            <svg width="100%" height="250px" version="1.1" xmlns="http://www.w3.org/2000/svg" class="wave bg-wave">
                <title>Wave</title>
                <defs></defs>
                <path id="feel-the-wave" d="" />
            </svg>
            <svg width="100%" height="250px" version="1.1" xmlns="http://www.w3.org/2000/svg" class="wave bg-wave">
                <title>Wave</title>
                <defs></defs>
                <path id="feel-the-wave-two" d="" />
            </svg>
            <svg width="100%" height="250px" version="1.1" xmlns="http://www.w3.org/2000/svg" class="wave bg-wave">
                <title>Wave</title>
                <defs></defs>
                <path id="feel-the-wave-three" d="" />
            </svg>
        </div>
        @yield('content')
    </div>
    <script>
        const signUpBtn = document.getElementById("signUp");
        const signInBtn = document.getElementById("signIn");
        const container = document.querySelector(".container");

        signUpBtn.addEventListener("click", () => {
            container.classList.add("right-panel-active");

        })
        signInBtn.addEventListener("click", () => {
            container.classList.remove("right-panel-active")
        })
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="{{ asset('assets/wave/js/vendor-all.min.js') }}"></script>
    <script src="{{ asset('assets/wave/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/wave/js/waves.min.js') }}"></script>
    <script src="{{ asset('assets/wave/js/pages/TweenMax.min.js') }}"></script>
    <script src="{{ asset('assets/wave/js/pages/jquery.wavify.js') }}"></script>
    <script>
        $('#feel-the-wave').wavify({
            height: 100,
            bones: 3,
            amplitude: 90,
            color: 'rgba(72, 134, 255, 4)',
            speed: .25
        });
        $('#feel-the-wave-two').wavify({
            height: 70,
            bones: 5,
            amplitude: 60,
            color: 'rgba(72, 134, 255, .3)',
            speed: .35
        });
        $('#feel-the-wave-three').wavify({
            height: 50,
            bones: 4,
            amplitude: 50,
            color: 'rgba(72, 134, 255, .2)',
            speed: .45
        });
    </script>
     @yield('script')
</body>

</html>
