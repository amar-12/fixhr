<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Deactivated</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary-blue: #2563eb;
            --dark-blue: #1d4ed8;
            --red-error: #ff4757;
            --warning-yellow: #fbbf24;
            --text-dark: #1a1a1a;
            --text-gray: #6b7280;
            --border-gray: #e5e7eb;
            --bg-gray: #f8f9fc;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg-gray);
            color: var(--text-dark);
            min-height: 100vh;
            overflow-x: hidden;
        }
        /* Header Styles */
        .page-header {
            background: #ffffff;
            padding: 2.5rem 0;
            text-align: center;
        }
        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: var(--red-error);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            animation: fadeInScale 0.5s ease-out;
        }
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        .icon-wrapper i {
            font-size: 2.5rem;
            color: #ffffff;
        }
        .page-header h1 {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }
        .page-header .subtitle {
            color: var(--primary-blue);
            font-size: 1rem;
            font-weight: 500;
        }
        /* Main Content */
        .main-content {
            background: var(--bg-gray);
            padding: 2rem 0 3rem;
        }
        /* Alert Card */
        .alert-card {
            background: #ffffff;
            border: 1px solid var(--border-gray);
            border-radius: 12px;
            padding: 1.75rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            animation: slideIn 0.4s ease-out;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .alert-card .alert-icon {
            font-size: 2rem;
            color: var(--warning-yellow);
        }
        .alert-card h5 {
            font-weight: 700;
            margin-bottom: 0.625rem;
            font-size: 1.15rem;
        }
        .alert-card p {
            color: var(--text-gray);
            line-height: 1.6;
            margin: 0;
            font-size: 0.95rem;
        }
        /* Info Cards */
        .info-card {
            background: #ffffff;
            border: 1px solid var(--border-gray);
            border-radius: 12px;
            padding: 1.75rem;
            height: 100%;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            animation: slideIn 0.5s ease-out;
        }
        .info-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .info-card-header {
            color: var(--primary-blue);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
        }
        .info-card h5 {
            font-weight: 700;
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
        }
        .info-card p {
            color: var(--text-gray);
            line-height: 1.5;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        .contact-item {
            display: flex;
            align-items: center;
            margin-top: 1rem;
        }
        .contact-icon {
            width: 36px;
            height: 36px;
            background: #eff6ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .contact-icon i {
            font-size: 1rem;
            color: var(--primary-blue);
        }
        .contact-info a {
            color: var(--primary-blue);
            font-weight: 500;
            text-decoration: none;
            font-size: 0.95rem;
        }
        .contact-info a:hover {
            color: var(--dark-blue);
            text-decoration: underline;
        }
        /* CTA Button */
        .cta-section {
            text-align: center;
            margin-top: 2rem;
        }
        .btn-cta {
            background: var(--primary-blue);
            color: #ffffff;
            padding: 0.875rem 2.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .btn-cta:hover {
            background: var(--dark-blue);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
        }
        /* Footer */
        .footer-note {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-gray);
        }
        .footer-note p {
            color: var(--text-gray);
            font-size: 0.875rem;
            margin: 0;
        }
        .footer-note i {
            color: var(--primary-blue);
        }
        /* Responsive Styles */
        @media (max-width: 991px) {
            .page-header h1 {
                font-size: 1.875rem;
            }
            .info-card {
                margin-bottom: 1.5rem;
            }
        }
        @media (max-width: 767px) {
            .page-header {
                padding: 2rem 0;
            }
            .page-header h1 {
                font-size: 1.5rem;
            }
            .icon-wrapper {
                width: 70px;
                height: 70px;
            }
            .icon-wrapper i {
                font-size: 2rem;
            }
            .alert-card {
                padding: 1.25rem;
            }
            .info-card {
                padding: 1.5rem;
            }
            .main-content {
                padding: 1.5rem 0 2rem;
            }
        }
        @media (max-width: 575px) {
            .page-header {
                padding: 1.5rem 0;
            }
            .page-header h1 {
                font-size: 1.25rem;
            }
            .icon-wrapper {
                width: 60px;
                height: 60px;
            }
            .icon-wrapper i {
                font-size: 1.75rem;
            }
            .alert-card {
                padding: 1rem;
            }
            .info-card {
                padding: 1.25rem;
            }
            .btn-cta {
                width: 100%;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header class="page-header">
        <div class="container">
            <div class="icon-wrapper">
                <i class="bi bi-building-slash"></i>
            </div>
            <h1>Business Deactivated</h1>
            <p class="subtitle">We're Here to Help</p>
        </div>
    </header>
    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">
                    <!-- Alert Card -->
                    <div class="alert-card">
                        <div class="d-flex flex-column flex-sm-row align-items-start">
                            <i class="bi bi-exclamation-triangle-fill alert-icon me-0 me-sm-3 mb-3 mb-sm-0"></i>
                            <div>
                                <h5>Your Business Account Has Been Deactivated!</h5>
                                <p>Your business account is currently inactive and access to all Fixhr features has been
                                    suspended. Please contact your system administrator or our support team to
                                    reactivate your business account and restore full access.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Info Cards -->
                    <div class="row g-3 g-lg-4">
                        <!-- Sales Card -->
                        <div class="col-12 col-lg-6">
                            <div class="info-card">
                                <div class="info-card-header">Sales</div>
                                <h5>Want to Reactivate?</h5>
                                <p>Pick up the phone to chat with our sales team.</p>
                                <div class="contact-item">
                                    <div class="contact-icon me-3">
                                        <i class="bi bi-telephone-fill"></i>
                                    </div>
                                    <div class="contact-info">
                                        <a href="tel:+917880128802">+91 7880128802</a>
                                    </div>
                                </div>
                                <div class="contact-item">
                                    <div class="contact-icon me-3">
                                        <i class="bi bi-envelope-fill"></i>
                                    </div>
                                    <div class="contact-info">
                                        <a href="mailto:sales@fixingdots.com">sales@fixingdots.com</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Support Card -->
                        <div class="col-12 col-lg-6">
                            <div class="info-card">
                                <div class="info-card-header">Customer Support</div>
                                <h5>Need any help?</h5>
                                <p>Don't worry, we're here for you.</p>
                                <div class="contact-item">
                                    <div class="contact-icon me-3">
                                        <i class="bi bi-envelope-fill"></i>
                                    </div>
                                    <div class="contact-info">
                                        <a href="mailto:support@fixingdots.com">support@fixingdots.com</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Footer Note -->
                    <div class="footer-note">
                        <p>
                            <i class="bi bi-info-circle-fill me-2"></i>
                            This page is displayed because your business account has been deactivated by an
                            administrator.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
