<!doctype html>
<html lang="en-US">

<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title>FixHR Login Credentials</title>
    <meta name="description" content="Reset Password Email Template.">
    <style type="text/css">
        body {
            margin: 0;
            background-color: #f2f3f8;
        }
        table {
            width: 100%;
            background-color: #f2f3f8;
        }
        table.inner {
            width: 650px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
        }
        .content {
            font-family: 'Open Sans', Helvetica, Arial, sans-serif;
            font-size: 25px;
        }
        .header {
            text-align: center;
            padding: 30px 0;
        }
        .header img {
            width: 200px;
        }
        .main {
            padding: 20px 30px;
        }
        .footer {
            text-align: center;
            color: #999;
            font-size: 70%;
            padding: 20px 0;
        }
        a {
            color: #8d6cd1;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline !important;
        }
    </style>
</head>

<body>
    <table cellspacing="0" border="0" cellpadding="0">
        <tr>
            <td align="center">
                <table class="inner">
                    <tr>
                        <td class="header">
                            <img src="http://dev.fixhr.app/assets/logo/logo.png" alt="FixHR Logo">
                        </td>
                    </tr>
                    <tr>
                        <td class="main content">
                            <p>Dear {{ $details['name'] }},</p>
                            <p>Please verify your OTP by providing the one-time password (OTP) mentioned below on the FixHR Software.</p>
                            <p><b>OTP: <span style="color:#8d6cd1; font-size:27px; font-weight:bold;">{{ $details['otp'] }}</span></b></p>
                            <p>Do not share this OTP with anyone. It is valid for only 10 minutes genrated at {{ now() }}.</p>
                            <p>Note: If you haven’t made this request, please ignore this email. Your login details are secure and can only be accessed if a unique OTP, sent to your registered email ID, is provided.</p>
                            <p>Warm Regards,<br>Fixing Dots</p>
                            <p style="font-size: 70%;">Please do not reply to this message. To reach us, <a href="http://dev.fixhr.app">click here</a>.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="footer">
                            <p>Powered By Fixing Dots</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
