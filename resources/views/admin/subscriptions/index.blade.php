@extends('admin.layout.master')
@section('title')
    Subscriptions
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background: linear-gradient(145deg, #f0f4fa 0%, #e6ecf5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
        }

        /* main card */
        .subscription-card {
            max-width: 1360px;
            width: 100%;
            background: rgba(255,255,255,0.75);
            backdrop-filter: blur(4px);
            border-radius: 40px;
            padding: 2.2rem 2.5rem;
            box-shadow: 0 30px 60px -15px rgba(0,20,40,0.25), 
                        0 8px 20px rgba(0,0,0,0.05),
                        inset 0 1px 2px rgba(255,255,255,0.6);
            border: 1px solid rgba(255,255,255,0.5);
        }

        /* header */
        .edit-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.8rem;
        }
        .edit-header h1 {
            font-size: 2rem;
            font-weight: 600;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #1A2E45, #2B4A6F);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .edit-header i {
            font-size: 2.1rem;
            color: #2e5a8b;
            opacity: 0.8;
        }

        /* section tags */
        .section-tag {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 1.8rem 0 1rem 0;
        }
        .section-tag h2 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1f3a57;
            letter-spacing: -0.01em;
        }
        .section-tag i {
            color: #3d6b9c;
            font-size: 1.2rem;
            background: rgba(61, 107, 156, 0.12);
            padding: 5px;
            border-radius: 12px;
        }
        .section-desc {
            font-size: 0.9rem;
            color: #556c83;
            margin-top: -0.5rem;
            margin-bottom: 1.3rem;
            font-weight: 400;
            border-left: 3px solid #98b9d9;
            padding-left: 14px;
        }

        /* layout grid: two columns */
        .config-preview-grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            gap: 2rem;
        }

        @media (max-width: 1100px) {
            .config-preview-grid {
                grid-template-columns: 1fr;
            }
        }

        /* left column cards */
        .info-block {
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(2px);
            border-radius: 28px;
            padding: 1.6rem 1.8rem;
            box-shadow: 0 12px 28px -10px rgba(25, 50, 80, 0.15), inset 0 1px 0 #ffffff;
            border: 1px solid rgba(255,255,255,0.7);
            margin-bottom: 1.8rem;
        }

        .business-badge {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #e2eaf3;
            padding: 0.8rem 1.2rem;
            border-radius: 60px;
            color: #1d3d5c;
            font-weight: 500;
            font-size: 0.95rem;
            border: 1px solid #c7d9e9;
            box-shadow: inset 0 1px 3px rgba(255,255,255,0.7);
            margin-bottom: 1.6rem;
        }
        .business-badge i {
            color: #4073a3;
            margin-right: 6px;
        }
        .badge-light {
            background: white;
            border-radius: 40px;
            padding: 0.3rem 1rem;
            font-weight: 600;
            color: #1c4b77;
            border: 1px solid #acc4dd;
            font-size: 0.85rem;
        }

        /* plan row */
        .plan-row {
            background: #f9fcff;
            border-radius: 20px;
            padding: 1rem 1.4rem;
            margin-bottom: 1.3rem;
            border: 1px solid #d5e2f0;
            box-shadow: 0 3px 10px rgba(0,0,0,0.02);
        }
        .plan-header {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            justify-content: space-between;
        }
        .plan-name {
            font-weight: 700;
            font-size: 1.25rem;
            color: #153e5a;
        }
        .pill {
            background: #d3e4f7;
            padding: 0.25rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e4f7a;
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 1.1rem;
        }
        .detail-item {
            background: white;
            border-radius: 18px;
            padding: 0.7rem 1rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            border: 1px solid #e0ecf9;
        }
        .detail-item label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            color: #54718b;
            display: block;
            margin-bottom: 4px;
        }
        .detail-item .value {
            font-weight: 700;
            font-size: 1.1rem;
            color: #122f44;
        }
        .detail-item .value small {
            font-weight: 400;
            font-size: 0.75rem;
            color: #758fa8;
            margin-left: 4px;
        }
        .optional-note {
            font-size: 0.75rem;
            color: #748ea6;
            margin-top: 6px;
            font-style: italic;
        }

        .price-slab-block {
            background: #eaf1fa;
            border-radius: 24px;
            padding: 1.1rem 1.4rem;
            margin: 1.2rem 0 1.6rem 0;
            border-left: 5px solid #3c7bb3;
            font-weight: 500;
            color: #0b3b5a;
            font-size: 1.1rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
        }
        .price-slab-block i {
            color: #1e5b96;
        }

        .status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            background: white;
            border-radius: 50px;
            padding: 0.8rem 1.6rem;
            border: 1px solid #d2e0ed;
        }
        .status-active {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .status-active .badge {
            background: #2b7f4e;
            color: white;
            font-weight: 600;
            padding: 0.3rem 1.2rem;
            border-radius: 40px;
            font-size: 0.9rem;
            box-shadow: 0 2px 8px rgba(43,127,78,0.3);
        }
        .contact-icons {
            display: flex;
            gap: 25px;
            color: #416181;
        }
        .contact-icons span i {
            margin-right: 6px;
            color: #527ca0;
        }

        /* right panel (preview) */
        .preview-panel {
            background: linear-gradient(145deg, #ffffff, #f8fcff);
            border-radius: 32px;
            padding: 1.8rem 1.5rem;
            box-shadow: 0 20px 35px -10px rgba(30,60,90,0.2), inset 0 1px 4px white;
            border: 1px solid rgba(255,255,255,0.7);
            height: fit-content;
        }
        .preview-header {
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 2px dashed #aac3dd;
            padding-bottom: 0.8rem;
            margin-bottom: 1.2rem;
        }
        .preview-header i {
            font-size: 1.4rem;
            color: #2e6296;
        }
        .preview-header h3 {
            font-weight: 600;
            color: #0f3a57;
        }

        .business-mini-card {
            background: #e7f0fa;
            border-radius: 20px;
            padding: 1.2rem 1.2rem;
            margin-bottom: 1.5rem;
            border: 1px solid #bdd2e9;
        }
        .business-mini-card .line1 {
            font-weight: 700;
            font-size: 1.2rem;
            color: #10344e;
        }
        .business-mini-card .line2 {
            display: flex;
            gap: 12px;
            margin: 6px 0 10px 0;
            color: #2f5579;
        }
        .cycle-badge {
            background: white;
            border-radius: 50px;
            padding: 0.2rem 1rem;
            font-weight: 500;
            font-size: 0.8rem;
            width: fit-content;
            border: 1px solid #9cb9d9;
        }

        .pricing-breakdown {
            background: #f2f9ff;
            border-radius: 24px;
            padding: 1.3rem;
            margin: 1.2rem 0;
        }
        .big-total {
            font-size: 2.2rem;
            font-weight: 700;
            color: #134979;
            line-height: 1.2;
        }
        .per-cycle {
            font-size: 1rem;
            font-weight: 400;
            color: #577392;
        }
        .users-detail {
            display: flex;
            justify-content: space-between;
            margin: 15px 0 8px 0;
            font-weight: 500;
        }
        .light-text {
            color: #52718d;
        }

        .next-billing-block {
            background: white;
            border-radius: 20px;
            padding: 1rem 1.3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid #c9dcef;
        }
        .next-date {
            font-size: 1.5rem;
            font-weight: 650;
            color: #1e4c77;
        }
        .next-label i {
            color: #628bb2;
            margin-right: 4px;
        }

        /* notes section */
        .notes-area {
            margin: 2rem 0 1.8rem 0;
        }
        .notes-label {
            font-weight: 600;
            color: #1f3d5a;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .notes-box {
            background: rgba(255,255,255,0.5);
            backdrop-filter: blur(2px);
            border: 2px dashed #b7cee8;
            border-radius: 24px;
            padding: 1rem 1.5rem;
            color: #1f3b52;
            font-style: italic;
            letter-spacing: 0.2px;
            font-size: 0.95rem;
            box-shadow: inset 0 2px 4px #eef5fc;
        }

        /* action buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }
        .btn {
            padding: 0.8rem 2.4rem;
            border-radius: 60px;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            background: white;
            color: #1d4468;
            border: 1px solid #b7cfe5;
        }
        .btn-primary {
            background: #1f4f7c;
            color: white;
            border: 1px solid #17416b;
            box-shadow: 0 10px 18px -8px #1f4f7c80;
        }
        .btn-primary:hover {
            background: #163f62;
            transform: translateY(-2px);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px -10px #2c577b;
        }

        hr {
            border: none;
            border-top: 2px solid rgba(168, 194, 221, 0.4);
            margin: 1.2rem 0;
        }

        /* extra polish */
        i.fa-solid, i.fa-regular, i.fa-light {
            filter: drop-shadow(0 2px 2px rgba(0,30,60,0.1));
        }
        .price-slab-block strong {
            background: white;
            padding: 0.2rem 0.8rem;
            border-radius: 40px;
            font-size: 0.9rem;
            margin-left: 6px;
            color: #0a2d46;
        }
    </style>
    <style>
        body {
            background: linear-gradient(145deg, #edf2f9 0%, #dfe7f2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
        }

        /* main card – same design language as subscription card */
        .stats-card {
            max-width: 1300px;
            width: 100%;
            background: rgba(255,255,255,0.75);
            backdrop-filter: blur(4px);
            border-radius: 40px;
            padding: 2rem 2.2rem;
            box-shadow: 0 30px 60px -15px rgba(0,20,40,0.25), 
                        0 8px 20px rgba(0,0,0,0.05),
                        inset 0 1px 2px rgba(255,255,255,0.6);
            border: 1px solid rgba(255,255,255,0.5);
        }

        /* header with active + contacts (exactly as described) */
        .top-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            background: #e3ecf6;   /* matches previous .business-badge tone */
            padding: 0.9rem 1.8rem;
            border-radius: 60px;
            margin-bottom: 2rem;
            border: 1px solid #cbdae9;
            box-shadow: inset 0 1px 4px rgba(255,255,255,0.8);
        }

        .active-badge {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .active-badge .badge {
            background: #2b7f4e;
            color: white;
            font-weight: 600;
            padding: 0.4rem 1.5rem;
            border-radius: 40px;
            font-size: 1rem;
            box-shadow: 0 2px 10px rgba(43,127,78,0.3);
            letter-spacing: 0.3px;
        }
        .active-badge i {
            color: #2b7f4e;
            font-size: 1.4rem;
        }

        .contact-info {
            display: flex;
            gap: 2rem;
            color: #1d4468;
            font-weight: 500;
            background: rgba(255,255,255,0.5);
            padding: 0.3rem 1.6rem;
            border-radius: 40px;
            backdrop-filter: blur(2px);
            border: 1px solid white;
        }
        .contact-info span i {
            margin-right: 8px;
            color: #3f6a97;
            width: 1.2rem;
        }

        /* tab headers (Monthly / Quarterly ...) – as per design */
        .frequency-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 1.8rem 0 1.2rem 0;
        }
        .tab-btn {
            background: rgba(255,255,255,0.5);
            backdrop-filter: blur(2px);
            border: 1px solid #bdd2ea;
            padding: 0.6rem 1.8rem;
            border-radius: 60px;
            font-weight: 600;
            color: #1e4164;
            cursor: default;  /* non-interactive demo */
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            transition: 0.1s;
            font-size: 0.95rem;
        }
        .tab-btn.active {
            background: #1f4f7c;
            border-color: #163f63;
            color: white;
            box-shadow: 0 10px 16px -8px #1f4f7c;
        }
        .tab-btn i {
            margin-right: 8px;
            font-size: 0.9rem;
        }

        /* subtitle line "For Monthly" etc – but we will use inside each table caption style */
        .table-caption {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 1rem 0 0.8rem 0;
        }
        .table-caption h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1f3a57;
            background: #dde9f7;
            padding: 0.2rem 1.2rem;
            border-radius: 60px;
            border: 1px solid #acc4de;
        }
        .table-caption i {
            color: #3d6b9c;
        }

        /* tables – clean, modern, matches subscription preview blocks */
        .table-wrapper {
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(2px);
            border-radius: 28px;
            padding: 1.5rem 1.2rem 1.8rem 1.2rem;
            box-shadow: 0 12px 28px -10px rgba(25, 50, 80, 0.15), inset 0 1px 0 #ffffff;
            border: 1px solid rgba(255,255,255,0.7);
            margin-bottom: 2rem;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        th {
            text-align: left;
            padding: 0.8rem 0.8rem 0.4rem 0.8rem;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #54718b;
            border-bottom: 2px dashed #b7cee8;
        }
        td {
            background: white;
            padding: 0.9rem 0.8rem;
            border-radius: 18px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.02);
            border: 1px solid #deecfb;
            font-weight: 500;
            color: #122f44;
        }
        td:first-child {
            border-top-left-radius: 24px;
            border-bottom-left-radius: 24px;
        }
        td:last-child {
            border-top-right-radius: 24px;
            border-bottom-right-radius: 24px;
        }

        .sno-cell {
            font-weight: 600;
            color: #1f4f7c;
            background: #e9f2fc;
            border: 1px solid #c3d8f0;
            width: 50px;
            text-align: center;
        }
        .month-cell {
            font-weight: 600;
            color: #0e3f60;
        }
        .number-highlight {
            font-weight: 700;
            color: #1e4c77;
        }
        .inactive-number {
            color: #8f4b4b;
            font-weight: 500;
        }

        /* summary card (exactly as requested: "ISKE NEECHE CARD ME AAYE") */
        .summary-card {
            background: linear-gradient(135deg, #eaf2fc, #d9e6f5);
            border-radius: 28px;
            padding: 1.5rem 2rem;
            margin-top: 2rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            border: 1px solid #b5cde4;
            box-shadow: 0 12px 28px -10px #1f4f7c40, inset 0 1px 4px white;
        }

        .summary-left {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .summary-icon {
            background: #1f4f7c;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            box-shadow: 0 12px 12px -8px #1f4f7c;
        }
        .summary-stats {
            display: flex;
            gap: 2.5rem;
            flex-wrap: wrap;
        }
        .stat-item {
            background: white;
            border-radius: 24px;
            padding: 0.6rem 1.6rem;
            box-shadow: inset 0 1px 5px #f0f7ff, 0 6px 12px -8px #32587b;
            border: 1px solid white;
        }
        .stat-label {
            font-size: 0.8rem;
            color: #4a6e8c;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .stat-value {
            font-size: 1.7rem;
            font-weight: 700;
            color: #103857;
            line-height: 1.2;
        }
        .summary-right {
            color: #1d4468;
            font-weight: 500;
            background: rgba(255,255,255,0.6);
            padding: 0.7rem 1.8rem;
            border-radius: 60px;
            backdrop-filter: blur(2px);
            border: 1px solid white;
        }
        .summary-right i {
            color: #527ca0;
            margin-right: 8px;
        }

        /* small extras */
        .footnote {
            margin-top: 1rem;
            text-align: right;
            font-size: 0.8rem;
            color: #788fa8;
        }
        hr {
            border: none;
            border-top: 2px solid rgba(168, 194, 221, 0.4);
            margin: 0.8rem 0;
        }

        /* responsiveness */
        @media (max-width: 750px) {
            .summary-left { flex-direction: column; align-items: flex-start; }
            .summary-stats { gap: 1rem; }
        }
    </style>
    <style>
        @media (max-width: 768px) {
            .subscription-card,
            .stats-card {
                padding: 1.4rem;
                border-radius: 26px;
            }
        }
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            min-width: 650px;
        }
        @media (max-width: 768px) {
            .business-badge {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
        @media (max-width: 768px) {
            .status-row {
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }
        }
        @media (max-width: 768px) {
            .big-total {
                font-size: 1.8rem;
            }

            .users-detail {
                flex-direction: column;
                gap: 6px;
                text-align: center;
            }

            .next-billing-block {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
        @media (max-width: 600px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 768px) {
            .summary-card {
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }

            .summary-stats {
                justify-content: center;
            }
        }
        @media (max-width: 600px) {
            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

    </style>
@endsection

@section('content')

    <div class="subscription-card">
        <!-- Subscription config + preview grid -->
        <div class="section-tag">
            <i class="fas fa-sliders-h"></i>
            <h2>Subscription Configuration</h2>
        </div>
        <div class="section-desc">Set up the subscription plan and pricing</div>

        <div class="config-preview-grid">
            <!-- LEFT: CONFIG FIELDS -->
            <div class="left-config">
                <!-- Business details card -->
                <div class="info-block">
                    <div class="business-badge">
                        <span><i class="fas fa-building"></i> {{ $user->fh_business->b_name }} • <strong>{{ $user->fh_business->b_unique_id }}</strong></span>
                        <span class="badge-light"><i class="far fa-id-card"></i> GST: {{ $user->fh_business->b_gst_no }}</span>
                    </div>

                    <!-- Plan * -->
                    <div class="plan-row">
                        <div class="plan-header">
                            <span class="plan-name"><i class="fas fa-crown" style="color:#d4a11e;"></i> {{ $plan->name }} — {{ ucfirst($subscriptionData->billing_cycle) }}</span>
                            <span class="pill"><i class="far fa-calendar-alt"></i> recurring</span>
                        </div>
                        <div class="details-grid">
                            <div class="detail-item">
                                <label>Price / user</label>
                                <span class="value">{{ $subscriptionData->price_per_user }}</span>
                            </div>

                            <div class="detail-item">
                                <label>Billing cycle</label>
                                <span class="value">{{ $subscriptionData->billing_cycle }} <i class="fas fa-redo-alt fa-xs" style="color:#527ca0;"></i></span>
                            </div>
                            <div class="detail-item">
                                <label>End date</label>
                                <span class="value"> {{ optional($subscriptionData->end_date)->format('d M Y') ?? '-' }} <small>optional</small></span>
                            </div>
                        </div>
                        <div class="optional-note"><i class="far fa-clock"></i> Leave blank for ongoing</div>
                    </div>

                    <!-- Price slab* -->
                    <div class="price-slab-block">
                        <i class="fas fa-layer-group"></i> <strong>Price Slab*</strong> 
                        <span style="background: white; padding: 0.3rem 1.2rem; border-radius: 40px; font-weight:600;">{{ $priceSlabs->min_employees }} - {{ $priceSlabs->max_employees }} users</span>
                        <span style="font-size:1rem;">(₹{{ $subscriptionData->price_per_user }}/user)</span>
                    </div>

                    <!-- Status + totals + contacts -->
                    <div class="status-row">
                        <div class="status-active">
                            <span class="badge"><i class="fas fa-check-circle"></i> {{ ucfirst($subscriptionData->status) }}</span>
                            <!-- <span><i class="fas fa-users"></i> Total {{ $totalEmployees }}</span> -->
                        </div>
                        <div class="contact-icons">
                            <span><i class="fas fa-envelope"></i> support@fixingdots.com</span>
                            <span><i class="fas fa-phone-alt"></i> 7880128672</span>
                        </div>
                    </div>
                </div>

                <!-- MONTHLY table (exact data from prompt) -->
                <div class="table-caption">
                    <i class="fas fa-calendar-alt fa-fw" style="font-size: 1.2rem;"></i>
                    <h3>For {{ ucfirst($subscriptionData->billing_cycle) }}</h3>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Period</th>
                                <th>No of active Employees</th>
                                <th>No. Of Inactive Employees</th>
                                <th>total Employees</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- data exactly as given -->
                            @foreach($subscriptionAllData as $subscription)
                                <tr>
                                    <td class="sno-cell">{{ $loop->iteration }}</td>

                                    <td class="month-cell">
                                        {{ optional($subscription->start_date)->format('d M Y') }} -
                                        {{ optional($subscription->end_date)->format('d M Y') }}
                                    </td>

                                    <td class="number-highlight">
                                        {{ $subscription->active_count }}
                                    </td>

                                    <td class="inactive-number">
                                        {{ $subscription->inactive_count }}
                                    </td>

                                    <td>
                                        {{ $subscription->active_count + $subscription->inactive_count }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        {{--<tbody id="subscriptionTable">
                            @include('admin.subscriptions.table')
                        </tbody>--}}
                    </table>
                </div>
            </div>

            <!-- RIGHT: PREVIEW & CALCULATION -->
            <div class="preview-panel">
                <div class="preview-header">
                    <i class="fas fa-calculator"></i>
                    <h3>Preview & Calculation</h3>
                    <span style="font-size:0.75rem; background:#d1e3fa; padding:3px 12px; border-radius:40px;">live</span>
                </div>
                <p style="color:#54718b; margin-bottom:1rem; font-size:0.9rem;">Real-time preview of subscription details</p>

                <!-- Business preview -->
                <div class="business-mini-card">
                    <div class="line1"><i class="fas fa-store"></i> {{ $user->fh_business->b_name }}</div>
                    <div class="line2">
                        <span>ID: {{ $user->fh_business->b_unique_id }}</span>
                        <span class="cycle-badge"><i class="fas fa-play-circle"></i> {{ ucfirst($subscriptionData->status) }}</span>
                    </div>
                    <div style="display: flex; gap: 15px; margin-top: 10px;">
                        <span><i class="far fa-calendar-check"></i> Cycle: {{ ucfirst($subscriptionData->billing_cycle) }}</span>
                        <span><i class="fas fa-wallet"></i> Total per month</span>
                    </div>
                </div>

                <!-- Pricing details block (exactly as in description) -->
                <div style="background: white; border-radius: 24px; padding: 1.2rem;">
                    <div style="font-weight: 600; color: #133e60; margin-bottom: 16px;">
                        <i class="fas fa-coins" style="margin-right: 6px;"></i>Pricing Details
                    </div>
                    <div style="background: #e7f2fd; border-radius: 18px; padding: 1rem; margin-bottom: 1.5rem;">
                        <div class="big-total">
                            ₹{{ number_format($amountPerCycle, 2) }}
                        </div>
                        <div class="per-cycle">Per month</div>
                        <div class="users-detail">
                            <span class="light-text">
                                <i class="fas fa-user-friends"></i>
                                {{ $activeUsersForBilling }} × ₹ {{ $subscriptionData->price_per_user }}
                            </span>

                            <span><strong>Users:</strong> {{ $activeUsersForBilling }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Price/User: ₹ {{ $subscriptionData->price_per_user }} </span>
                            <span><strong>Total: ₹{{ number_format($amountPerCycle, 2) }}</strong></span>
                        </div>
                    </div>

                    <!-- Amount per cycle & next billing exactly as description -->
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin: 12px 0;">
                        <span style="font-weight: 600;">Amount Per Cycle</span>
                        <span style="font-size: 1.5rem; font-weight: 700; color: #1a4c7a;">
                            ₹{{ number_format($amountPerCycle, 2) }}
                        </span>
                    </div>
                    <div style="color:#3c6382;"><i class="far fa-hourglass"></i> Per month</div>

                    <hr>

                    <div class="next-billing-block">
                        <div>
                            <div class="next-label"><i class="fas fa-calendar-alt"></i> Next Billing</div>
                            <div class="next-date">
                                {{ $nextBillingDate ? $nextBillingDate->format('d M Y') : '-' }}
                            </div>
                        </div>
                        <div style="text-align: right; background: #daeafc; padding: 0.5rem 1.2rem; border-radius: 30px;">
                            <i class="fas fa-arrow-right"></i> Next Billing Date
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align: right; font-size: 0.7rem; color: #95aec9; margin-top: 0.5rem;">
            <i class="far fa-edit"></i> last edited today
        </div>
    </div>

@endsection

@section('script')
    <script>
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();

            let url = $(this).attr('href');

            $.get(url, function(data) {
                $('#subscriptionTable').html(data);
            });
        });
    </script>
@endsection
