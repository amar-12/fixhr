<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $statusConfig = [
            'expired' => [
                'title'       => 'Subscription Expired',
                'subtitle'    => 'Your plan has ended',
                'badge'       => 'EXPIRED',
                'badge_class' => 'badge-expired',
                'icon'        => 'bi-clock-history',
                'icon_class'  => 'icon-expired',
                'alert_title' => 'Your Subscription Has Expired',
                'alert_body'  => 'Your business subscription has reached its end date. Renew now to restore full access to all FixHr features and your team\'s data.',
                'alert_class' => 'alert-expired',
                'cta_label'   => 'Renew Subscription',
                'show_date'   => true,
            ],
            'suspended' => [
                'title'       => 'Account Suspended',
                'subtitle'    => 'Temporary access restriction',
                'badge'       => 'SUSPENDED',
                'badge_class' => 'badge-suspended',
                'icon'        => 'bi-pause-circle-fill',
                'icon_class'  => 'icon-suspended',
                'alert_title' => 'Your Account Has Been Suspended',
                'alert_body'  => 'Access to your FixHr account has been temporarily suspended. This may be due to a payment issue or a policy violation. Please contact support to resolve this.',
                'alert_class' => 'alert-suspended',
                'cta_label'   => 'Contact Support',
                'show_date'   => false,
            ],
            'deactivated' => [
                'title'       => 'Subscription Deactivated',
                'subtitle'    => 'This account is no longer active',
                'badge'       => 'DEACTIVATED',
                'badge_class' => 'badge-deactivated',
                'icon'        => 'bi-building-x',
                'icon_class'  => 'icon-deactivated',
                'alert_title' => 'Your Subscription Account Has Been Deactivated',
                'alert_body'  => 'This Subscription account has been permanently deactivated. If you believe this is an error or wish to reactivate your business, please reach out to our sales team.',
                'alert_class' => 'alert-deactivated',
                'cta_label'   => 'Reactivate Business',
                'show_date'   => false,
            ],
            'cancelled' => [
                'title'       => 'Subscription Cancelled',
                'subtitle'    => 'Your plan was cancelled',
                'badge'       => 'CANCELLED',
                'badge_class' => 'badge-cancelled',
                'icon'        => 'bi-x-circle-fill',
                'icon_class'  => 'icon-cancelled',
                'alert_title' => 'Your Subscription Was Cancelled',
                'alert_body'  => 'Your FixHr subscription has been cancelled. To continue using the platform, please subscribe to a new plan or speak with our sales team.',
                'alert_class' => 'alert-cancelled',
                'cta_label'   => 'Subscribe Again',
                'show_date'   => false,
            ],
            'no_subscription' => [
                'title'       => 'No Subscription Found',
                'subtitle'    => 'Get started with FixHr',
                'badge'       => 'INACTIVE',
                'badge_class' => 'badge-inactive',
                'icon'        => 'bi-shield-lock-fill',
                'icon_class'  => 'icon-inactive',
                'alert_title' => 'No Active Subscription Found',
                'alert_body'  => 'Your business account does not have an active subscription. Please contact your system administrator or our sales team to activate your subscription.',
                'alert_class' => 'alert-inactive',
                'cta_label'   => 'Get a Subscription',
                'show_date'   => false,
            ],
        ];

        $currentStatus = $status ?? 'no_subscription';
        $cfg = $statusConfig[$currentStatus] ?? $statusConfig['no_subscription'];
        $endDate = $subscription?->end_date?->format('d F Y');
    @endphp

    <title>{{ $cfg['title'] }} - HRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary:       #2563eb;
            --primary-dark:  #1d4ed8;
            --primary-soft:  #eff6ff;

            /* Status palette */
            --expired-hue:    30;   /* amber */
            --suspended-hue:  210;  /* blue-violet */
            --deactivated-hue:0;    /* red */
            --cancelled-hue:  270;  /* purple */
            --inactive-hue:   220;  /* slate-blue */

            --text-dark:  #0f172a;
            --text-mid:   #475569;
            --text-light: #94a3b8;
            --border:     #e2e8f0;
            --surface:    #ffffff;
            --bg:         #f1f5f9;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text-dark);
            min-height: 100vh;
        }

        /* ─── STATUS COLOUR TOKENS ─── */
        .icon-expired      { --s-h: var(--expired-hue);      }
        .icon-suspended    { --s-h: var(--suspended-hue);    }
        .icon-deactivated  { --s-h: var(--deactivated-hue);  }
        .icon-cancelled    { --s-h: var(--cancelled-hue);    }
        .icon-inactive     { --s-h: var(--inactive-hue);     }

        /* ─── HEADER ─── */
        .page-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 2.75rem 0 2.5rem;
            text-align: center;
        }

        .icon-ring {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
            background: hsl(var(--s-h, 220), 90%, 56%);
            box-shadow: 0 0 0 12px hsl(var(--s-h, 220), 90%, 94%),
                        0 0 0 24px hsl(var(--s-h, 220), 90%, 97%);
            animation: popIn .5s cubic-bezier(.34,1.56,.64,1) both;
        }

        @keyframes popIn {
            from { opacity: 0; transform: scale(.6); }
            to   { opacity: 1; transform: scale(1);  }
        }

        .icon-ring i {
            font-size: 2.4rem;
            color: #fff;
        }

        .status-badge {
            display: inline-block;
            font-family: 'DM Mono', monospace;
            font-size: .65rem;
            font-weight: 500;
            letter-spacing: 2px;
               padding: 0.25rem 1.4rem;
            border-radius: 999px;
            margin-bottom: .875rem;
            background: hsl(var(--s-h, 220), 90%, 94%);
            color: hsl(var(--s-h, 220), 70%, 38%);
        }

        /* inherit hue from parent icon class */
        .badge-expired     { --s-h: var(--expired-hue);     }
        .badge-suspended   { --s-h: var(--suspended-hue);   }
        .badge-deactivated { --s-h: var(--deactivated-hue); }
        .badge-cancelled   { --s-h: var(--cancelled-hue);   }
        .badge-inactive    { --s-h: var(--inactive-hue);    }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -.5px;
            margin-bottom: .3rem;
        }

        .page-header .subtitle {
            color: var(--text-mid);
            font-size: .95rem;
            font-weight: 500;
        }

        /* ─── MAIN ─── */
        .main-content {
            padding: 2.25rem 0 3.5rem;
        }

        /* ─── ALERT CARD ─── */
        .alert-card {
            border-radius: 14px;
            padding: 1.6rem 1.75rem;
            margin-bottom: 2rem;
            border-left: 4px solid hsl(var(--s-h, 220), 85%, 55%);
            background: hsl(var(--s-h, 220), 90%, 98%);
            animation: slideUp .4s ease both;
        }

        .alert-expired     { --s-h: var(--expired-hue);     }
        .alert-suspended   { --s-h: var(--suspended-hue);   }
        .alert-deactivated { --s-h: var(--deactivated-hue); }
        .alert-cancelled   { --s-h: var(--cancelled-hue);   }
        .alert-inactive    { --s-h: var(--inactive-hue);    }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0);    }
        }

        .alert-card .alert-icon {
            font-size: 1.75rem;
            color: hsl(var(--s-h, 220), 70%, 50%);
            flex-shrink: 0;
        }

        .alert-card h5 {
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: .4rem;
        }

        .alert-card p {
            color: var(--text-mid);
            font-size: .9rem;
            line-height: 1.65;
            margin: 0;
        }

        .expiry-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            margin-top: .75rem;
            padding: .3rem .8rem;
            border-radius: 8px;
            background: hsl(var(--s-h, 220), 80%, 92%);
            color: hsl(var(--s-h, 220), 60%, 35%);
            font-size: .8rem;
            font-weight: 600;
            font-family: 'DM Mono', monospace;
        }

        /* ─── INFO CARDS ─── */
        .info-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1.75rem;
            height: 100%;
            transition: transform .25s ease, box-shadow .25s ease;
            animation: slideUp .5s ease both;
        }

        .info-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,.08);
        }

        .card-eyebrow {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: .6rem;
        }

        .info-card h5 {
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: .5rem;
        }

        .info-card p {
            color: var(--text-mid);
            font-size: .875rem;
            line-height: 1.6;
            margin-bottom: .5rem;
        }

        .contact-row {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-top: .9rem;
        }

        .c-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--primary-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .c-icon i {
            color: var(--primary);
            font-size: 1rem;
        }

        .contact-row a {
            color: var(--primary);
            font-weight: 600;
            font-size: .9rem;
            text-decoration: none;
        }

        .contact-row a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* ─── FOOTER ─── */
        .footer-note {
            text-align: center;
            margin-top: 2.25rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }

        .footer-note p {
            color: var(--text-light);
            font-size: .8rem;
        }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 767px) {
            .page-header h1 { font-size: 1.5rem; }
            .icon-ring { width: 72px; height: 72px; }
            .icon-ring i { font-size: 2rem; }
            .info-card { margin-bottom: 1rem; }
        }

        @media (max-width: 575px) {
            .page-header h1 { font-size: 1.25rem; }
            .icon-ring { width: 64px; height: 64px; }
        }
    </style>
</head>
<body>

    {{-- ── HEADER ── --}}
    <header class="page-header">
        <div class="container">
            <div class="icon-ring {{ $cfg['icon_class'] }}">
                <i class="bi {{ $cfg['icon'] }}"></i>
            </div>

            <div class="status-badge {{ $cfg['badge_class'] }}">{{ $cfg['badge'] }}</div>

            <h1>{{ $cfg['title'] }}</h1>
            <p class="subtitle">{{ $cfg['subtitle'] }}</p>
        </div>
    </header>

    {{-- ── MAIN ── --}}
    <main class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">

                    {{-- Alert Card --}}
                    <div class="alert-card {{ $cfg['alert_class'] }}">
                        <div class="d-flex flex-column flex-sm-row align-items-start gap-3">
                            <i class="bi bi-exclamation-triangle-fill alert-icon"></i>
                            <div>
                                <h5>{{ $cfg['alert_title'] }}</h5>
                                <p>{{ $cfg['alert_body'] }}</p>

                                @if ($cfg['show_date'] && $endDate)
                                    <div class="expiry-pill {{ $cfg['alert_class'] }}">
                                        <i class="bi bi-calendar-x"></i>
                                        Expired on {{ $endDate }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Contact Cards --}}
                    <div class="row g-3 g-lg-4">

                        {{-- Sales Card --}}
                        <div class="col-12 col-lg-6">
                            <div class="info-card">
                                <div class="card-eyebrow">Sales</div>
                                <h5>{{ $cfg['cta_label'] }}?</h5>
                                <p>Pick up the phone or drop us an email — our sales team is ready.</p>

                                <div class="contact-row">
                                    <div class="c-icon"><i class="bi bi-telephone-fill"></i></div>
                                    <a href="tel:+917880128802">+91 7880128802</a>
                                </div>

                                <div class="contact-row">
                                    <div class="c-icon"><i class="bi bi-envelope-fill"></i></div>
                                    <a href="mailto:sales@fixingdots.com">sales@fixingdots.com</a>
                                </div>
                            </div>
                        </div>

                        {{-- Support Card --}}
                        <div class="col-12 col-lg-6">
                            <div class="info-card">
                                <div class="card-eyebrow">Customer Support</div>
                                <h5>Need help resolving this?</h5>
                                <p>Our support team can assist with account access, billing queries, and reactivation requests.</p>

                                <div class="contact-row">
                                    <div class="c-icon"><i class="bi bi-envelope-fill"></i></div>
                                    <a href="mailto:support@fixingdots.com">support@fixingdots.com</a>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Footer --}}
                    <div class="footer-note">
                        <p>
                            <i class="bi bi-info-circle me-1"></i>
                            @switch($currentStatus)
                                @case('suspended')
                                    Your account access has been temporarily suspended. Contact support for assistance.
                                    @break
                                @case('deactivated')
                                    This business account has been deactivated. Contact our team to discuss reactivation.
                                    @break
                                @case('cancelled')
                                    Your subscription was cancelled. Subscribe to a new plan to restore access.
                                    @break
                                @case('no_subscription')
                                    No active subscription is linked to this business account.
                                    @break
                                @default
                                    This page is shown because your subscription has expired or is inactive.
                            @endswitch
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>