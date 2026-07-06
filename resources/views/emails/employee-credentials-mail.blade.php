<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to the Team</title>
    <style>
        /* General Reset */
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }

        table {
            border-collapse: collapse !important;
        }

        body {
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background-color: #f4f7f9;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        /* Responsive Styles */
        @media screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                padding: 10px !important;
            }

            .content {
                padding: 25px !important;
            }

            .header-text {
                font-size: 22px !important;
            }

            .stat-box {
                width: 100% !important;
                margin-bottom: 10px !important;
            }
        }

        /* Design Tokens */
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f7f9;
            padding-bottom: 40px;
        }

        .main-card {
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .header {
            background-color: #2c3e50;
            padding: 40px 20px;
            text-align: center;
            color: #ffffff;
        }

        .content {
            padding: 45px;
            color: #444444;
            line-height: 1.7;
            font-size: 15px;
        }

        .footer {
            padding: 30px;
            text-align: center;
            color: #95a5a6;
            font-size: 13px;
        }

        .button {
            display: inline-block;
            padding: 14px 35px;
            background-color: #27ae60;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .secondary-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #ffffff;
            color: #2c3e50 !important;
            text-decoration: none;
            border: 2px solid #2c3e50;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            margin: 5px;
        }

        .credential-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }

        .highlight {
            color: #2c3e50;
            font-weight: 700;
        }

        .accent {
            color: #27ae60;
        }

        .feature-list {
            margin: 20px 0;
            padding-left: 0;
            list-style: none;
        }

        .feature-item {
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <table role="presentation" class="container" align="center" border="0" cellpadding="0" cellspacing="0"
            width="600" style="margin: auto;">
            <tr>
                <td style="padding: 30px 0;">
                    <!-- Logo Area -->
                    <div style="text-align: center; padding-bottom: 25px;">
                        <!-- Replace the URL below with your actual hosted logo image URL -->
                        <img src="https://fixhr.app/assets/logo/logo.png" alt="FixHR Logo" width="150"
                            style="display: block; margin: 0 auto;">
                    </div>

                    <!-- Main Content Card -->
                    <table role="presentation" class="main-card" width="100%" border="0" cellpadding="0"
                        cellspacing="0">
                        <tr>
                            <td class="header">
                                <h1 class="header-text" style="margin: 0; font-size: 26px; font-weight: 600;">Welcome to
                                    the Team!</h1>
                                <p style="margin: 10px 0 0 0; opacity: 0.9; font-size: 16px;">We're thrilled to have you
                                    onboard.</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="content">
                                <p style="font-size: 18px;">Welcome to <span
                                        class="highlight">{{ $data['business_name'] }}</span>, <span
                                        class="accent">{{ $data['name'] }}!</span></p>

                                <p>To ensure a smooth and efficient work experience, we use the <strong>FixHR
                                        App</strong> for all your HR needs.</p>

                                <!-- Credentials Section -->
                                <div class="credential-box">
                                    <h3 style="margin-top: 0; font-size: 16px; color: #2c3e50;">Your Login Credentials
                                    </h3>
                                    <p style="margin: 5px 0;"><strong>Email / Username:</strong> <span
                                            style="color: #27ae60;">{{ $data['email'] }}</span></p>
                                    <p style="margin: 5px 0;"><strong>Password :</strong> <span
                                            style="color: #27ae60;">{{ $data['password'] }}</span></p>

                                </div>

                                <h3 style="color: #2c3e50; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                                    Getting Started with FixHR</h3>
                                <p>Download the app and complete your profile to finalize your joining formalities:</p>

                                <div style="text-align: center; margin-bottom: 25px;">
                                    <a href="https://play.google.com/store/apps/details?id=com.fd.fixHR"
                                        class="secondary-button" target="_blank" rel="noopener noreferrer">
                                        Google Play Store
                                    </a>

                                    <a href="https://apps.apple.com/in/app/fixhr/id6744237631" class="secondary-button"
                                        target="_blank" rel="noopener noreferrer">
                                        Apple App Store
                                    </a>
                                </div>

                                <p><strong>The FixHR App is your primary platform for:</strong></p>
                                <ul style="padding-left: 20px; color: #555;">
                                    <li>Attendance tracking & travel management</li>
                                    <li>Leave applications and approvals</li>
                                    <li>Viewing salary slips & company policies</li>
                                    <li>HR requests and feedback</li>
                                </ul>

                                <div
                                    style="background-color: #fff9eb; border-radius: 8px; padding: 15px; margin: 25px 0; text-align: center; border: 1px solid #ffeeba;">
                                    <p style="margin: 0; color: #856404; font-size: 14px;">
                                        Need help? Reach out to the <strong>HR Team</strong> anytime!
                                    </p>
                                    <a href="https://fixhr.app/login" target="_blank" rel="noopener noreferrer"
                                        style="color: #2c3e50; font-weight: bold; text-decoration: underline; font-size: 13px;">
                                        Log in to FixHR Web
                                    </a>

                                </div>

                                <p style="margin-bottom: 0;">Once again, welcome aboard. We wish you a successful
                                    journey!</p>

                                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                                    <p style="margin: 0; font-weight: bold;">Warm regards,</p>
                                    <p style="margin: 5px 0; color: #27ae60; font-weight: bold;">HR Team</p>
                                    <p style="margin: 0; font-size: 13px; color: #64748b;">{{ $data['business_name'] }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <!-- Footer -->
                    <table role="presentation" class="footer" width="100%" border="0" cellpadding="0"
                        cellspacing="0">
                        <tr>
                            <td class="footer">
                                <p>This is an auto-generated email. Please do not reply.</p>
                                <p>Powered by <strong>Fixing Dots Technologies</strong></p>
                                <p>&copy; {{ date('Y') }} Fixing Dots. All rights reserved.</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
