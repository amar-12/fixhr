<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #2563eb;
            --dark-blue: #1d4ed8;
            --warning-yellow: #f59e0b;
            --success-green: #059669;
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

        /* Animated Background with HRM Elements */
        .animated-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }

        /* Floating Code Elements */
        .code-element {
            position: absolute;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            opacity: 0.08;
            color: var(--primary-blue);
        }

        .code-1 {
            font-size: 90px;
            top: 12%;
            left: 8%;
            animation: float1 8s ease-in-out infinite;
        }

        .code-2 {
            font-size: 85px;
            top: 25%;
            right: 10%;
            animation: float2 9s ease-in-out infinite;
        }

        .code-3 {
            font-size: 80px;
            bottom: 20%;
            left: 12%;
            animation: float3 7s ease-in-out infinite;
        }

        .code-4 {
            font-size: 88px;
            bottom: 30%;
            right: 8%;
            animation: float4 8.5s ease-in-out infinite;
        }

        /* HRM Icons */
        .hrm-icon {
            position: absolute;
            opacity: 0.12;
            animation: techFloat 10s ease-in-out infinite;
        }

        .hrm-1 {
            font-size: 95px;
            top: 15%;
            left: 5%;
            color: #10b981;
            animation-delay: 0s;
        }

        .hrm-2 {
            font-size: 90px;
            top: 35%;
            right: 8%;
            color: #8b5cf6;
            animation-delay: 2s;
        }

        .hrm-3 {
            font-size: 85px;
            bottom: 15%;
            left: 8%;
            color: #f59e0b;
            animation-delay: 4s;
        }

        .hrm-4 {
            font-size: 92px;
            bottom: 35%;
            right: 12%;
            color: #ec4899;
            animation-delay: 1s;
        }

        .hrm-5 {
            font-size: 88px;
            top: 55%;
            left: 15%;
            color: #06b6d4;
            animation-delay: 3s;
        }

        .hrm-6 {
            font-size: 90px;
            top: 45%;
            right: 20%;
            color: #ef4444;
            animation-delay: 5s;
        }

        .hrm-7 {
            font-size: 85px;
            top: 68%;
            right: 5%;
            color: #14b8a6;
            animation-delay: 2.5s;
        }

        .hrm-8 {
            font-size: 88px;
            bottom: 45%;
            left: 25%;
            color: #f97316;
            animation-delay: 4.5s;
        }

        /* Binary Code Rain */
        .binary-line {
            position: absolute;
            font-family: 'Courier New', monospace;
            font-size: 20px;
            color: var(--primary-blue);
            opacity: 0.06;
            white-space: nowrap;
            animation: binaryRain 15s linear infinite;
        }

        .binary-1 {
            left: 15%;
            animation-delay: 0s;
        }

        .binary-2 {
            left: 35%;
            animation-delay: 3s;
        }

        .binary-3 {
            left: 55%;
            animation-delay: 6s;
        }

        .binary-4 {
            left: 75%;
            animation-delay: 9s;
        }

        @keyframes binaryRain {
            0% {
                top: -50px;
                opacity: 0;
            }
            10% {
                opacity: 0.06;
            }
            90% {
                opacity: 0.06;
            }
            100% {
                top: 100vh;
                opacity: 0;
            }
        }

        /* Circuit Lines */
        .circuit-line {
            position: absolute;
            background: linear-gradient(90deg, 
                transparent 0%, 
                var(--primary-blue) 20%, 
                var(--primary-blue) 80%, 
                transparent 100%);
            opacity: 0.05;
        }

        .circuit-h-1 {
            width: 300px;
            height: 2px;
            top: 20%;
            left: 10%;
            animation: pulseCircuit 3s ease-in-out infinite;
        }

        .circuit-h-2 {
            width: 250px;
            height: 2px;
            top: 60%;
            right: 10%;
            animation: pulseCircuit 3s ease-in-out infinite;
            animation-delay: 1.5s;
        }

        .circuit-v-1 {
            width: 2px;
            height: 200px;
            top: 30%;
            left: 30%;
            background: linear-gradient(180deg, 
                transparent 0%, 
                var(--primary-blue) 20%, 
                var(--primary-blue) 80%, 
                transparent 100%);
            animation: pulseCircuit 3s ease-in-out infinite;
            animation-delay: 0.5s;
        }

        .circuit-v-2 {
            width: 2px;
            height: 180px;
            bottom: 25%;
            right: 25%;
            background: linear-gradient(180deg, 
                transparent 0%, 
                var(--primary-blue) 20%, 
                var(--primary-blue) 80%, 
                transparent 100%);
            animation: pulseCircuit 3s ease-in-out infinite;
            animation-delay: 2s;
        }

        @keyframes pulseCircuit {
            0%, 100% {
                opacity: 0.05;
            }
            50% {
                opacity: 0.12;
            }
        }

        /* Hexagon Grid */
        .hex-grid {
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(30deg, var(--primary-blue) 12%, transparent 12.5%, transparent 87%, var(--primary-blue) 87.5%, var(--primary-blue)),
                linear-gradient(150deg, var(--primary-blue) 12%, transparent 12.5%, transparent 87%, var(--primary-blue) 87.5%, var(--primary-blue)),
                linear-gradient(30deg, var(--primary-blue) 12%, transparent 12.5%, transparent 87%, var(--primary-blue) 87.5%, var(--primary-blue)),
                linear-gradient(150deg, var(--primary-blue) 12%, transparent 12.5%, transparent 87%, var(--primary-blue) 87.5%, var(--primary-blue));
            background-size: 80px 140px;
            background-position: 0 0, 0 0, 40px 70px, 40px 70px;
            opacity: 0.02;
        }

        /* Particle System */
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: var(--primary-blue);
            border-radius: 50%;
            opacity: 0.15;
        }

        .particle-1 {
            top: 10%;
            left: 20%;
            animation: particleFloat1 12s ease-in-out infinite;
        }

        .particle-2 {
            top: 30%;
            right: 20%;
            animation: particleFloat2 14s ease-in-out infinite;
        }

        .particle-3 {
            bottom: 20%;
            left: 30%;
            animation: particleFloat3 16s ease-in-out infinite;
        }

        .particle-4 {
            bottom: 35%;
            right: 25%;
            animation: particleFloat4 13s ease-in-out infinite;
        }

        .particle-5 {
            top: 50%;
            left: 50%;
            animation: particleFloat1 15s ease-in-out infinite;
        }

        @keyframes particleFloat1 {
            0%, 100% {
                transform: translate(0, 0);
            }
            25% {
                transform: translate(50px, -50px);
            }
            50% {
                transform: translate(100px, 0);
            }
            75% {
                transform: translate(50px, 50px);
            }
        }

        @keyframes particleFloat2 {
            0%, 100% {
                transform: translate(0, 0);
            }
            33% {
                transform: translate(-60px, 60px);
            }
            66% {
                transform: translate(-30px, -30px);
            }
        }

        @keyframes particleFloat3 {
            0%, 100% {
                transform: translate(0, 0);
            }
            50% {
                transform: translate(80px, -80px);
            }
        }

        @keyframes particleFloat4 {
            0%, 100% {
                transform: translate(0, 0);
            }
            25% {
                transform: translate(-40px, -60px);
            }
            50% {
                transform: translate(-80px, 0);
            }
            75% {
                transform: translate(-40px, 60px);
            }
        }

        /* Animation Keyframes */
        @keyframes float1 {
            0%, 100% {
                transform: translateY(0px) translateX(0px) rotate(0deg);
            }
            25% {
                transform: translateY(-25px) translateX(15px) rotate(5deg);
            }
            50% {
                transform: translateY(-40px) translateX(-15px) rotate(-5deg);
            }
            75% {
                transform: translateY(-20px) translateX(10px) rotate(3deg);
            }
        }

        @keyframes float2 {
            0%, 100% {
                transform: translateY(0px) translateX(0px) scale(1);
            }
            33% {
                transform: translateY(-30px) translateX(-20px) scale(1.05);
            }
            66% {
                transform: translateY(-15px) translateX(20px) scale(0.95);
            }
        }

        @keyframes float3 {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-45px) rotate(10deg);
            }
        }

        @keyframes float4 {
            0%, 100% {
                transform: translateY(0px) translateX(0px) rotate(0deg);
            }
            33% {
                transform: translateY(-25px) translateX(20px) rotate(-8deg);
            }
            66% {
                transform: translateY(-35px) translateX(-20px) rotate(8deg);
            }
        }

        @keyframes techFloat {
            0%, 100% {
                transform: translateY(0px) scale(1);
                opacity: 0.12;
            }
            50% {
                transform: translateY(-30px) scale(1.1);
                opacity: 0.18;
            }
        }

        /* Animated Gradient Orbs */
        .gradient-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.06;
            animation: floatOrb 25s ease-in-out infinite;
        }

        .orb-1 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, var(--primary-blue), #8b5cf6);
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, #10b981, var(--primary-blue));
            bottom: -100px;
            right: -100px;
            animation-delay: 8s;
        }

        .orb-3 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, var(--primary-blue), #06b6d4);
            top: 40%;
            right: -80px;
            animation-delay: 16s;
        }

        @keyframes floatOrb {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            33% {
                transform: translate(60px, -60px) scale(1.15);
            }
            66% {
                transform: translate(-40px, 40px) scale(0.85);
            }
        }

        /* Header Styles */
        .page-header {
            background: #ffffff;
            padding: 2.5rem 0;
            text-align: center;
            position: relative;
            z-index: 1;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .icon-wrapper {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            position: relative;
            animation: iconPulse 2s ease-in-out infinite;
        }

        /* Rotating Gears Animation */
        .icon-wrapper::before,
        .icon-wrapper::after {
            content: '';
            position: absolute;
            border: 3px dashed rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: rotate 20s linear infinite;
        }

        .icon-wrapper::before {
            width: 120px;
            height: 120px;
        }

        .icon-wrapper::after {
            width: 140px;
            height: 140px;
            animation-direction: reverse;
            animation-duration: 25s;
        }

        @keyframes iconPulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 0 0 20px rgba(37, 99, 235, 0);
                transform: scale(1.05);
            }
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .icon-wrapper i {
            font-size: 2.5rem;
            color: #ffffff;
            animation: toolRotate 3s ease-in-out infinite;
            position: relative;
            z-index: 1;
        }

        @keyframes toolRotate {
            0%, 100% {
                transform: rotate(0deg);
            }
            25% {
                transform: rotate(-15deg);
            }
            75% {
                transform: rotate(15deg);
            }
        }

        .page-header h1 {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
            animation: fadeInUp 0.6s ease-out;
        }

        .page-header .subtitle {
            color: var(--primary-blue);
            font-size: 1rem;
            font-weight: 500;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Main Content */
        .main-content {
            background: transparent;
            padding: 2rem 0 3rem;
            position: relative;
            z-index: 1;
        }

        /* Info Card */
        .info-card-main {
            background: #ffffff;
            border: 1px solid var(--border-gray);
            border-radius: 12px;
            padding: 1.75rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            animation: slideIn 0.5s ease-out;
            position: relative;
            overflow: hidden;
        }

        .info-card-main::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.05), transparent);
            animation: cardShimmer 3s infinite;
        }

        @keyframes cardShimmer {
            0% {
                left: -100%;
            }
            100% {
                left: 100%;
            }
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

        .info-card-main .info-icon {
            font-size: 2rem;
            color: var(--primary-blue);
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .info-card-main h5 {
            font-weight: 700;
            margin-bottom: 0.625rem;
            font-size: 1.15rem;
        }

        .info-card-main p {
            color: var(--text-gray);
            line-height: 1.6;
            margin: 0;
            font-size: 0.95rem;
        }

        /* Countdown Styles */
        .countdown-container {
            background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
            border-radius: 12px;
            padding: 2rem;
            margin: 2rem 0;
            text-align: center;
            color: white;
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.3);
            position: relative;
            overflow: hidden;
        }

        .countdown-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotateGlow 10s linear infinite;
        }

        @keyframes rotateGlow {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .countdown-title {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .countdown-display {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .countdown-unit {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            padding: 1rem;
            min-width: 100px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }

        .countdown-unit:hover {
            transform: scale(1.05);
        }

        .countdown-value {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            font-variant-numeric: tabular-nums;
            transition: transform 0.15s ease;
        }

        .countdown-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 0.5rem;
            opacity: 0.9;
        }

        .countdown-note {
            font-size: 0.95rem;
            opacity: 0.9;
            margin-top: 1rem;
            position: relative;
            z-index: 1;
        }

        /* Info Cards */
        .info-card {
            background: #ffffff;
            border: 1px solid var(--border-gray);
            border-radius: 12px;
            padding: 1.75rem;
            height: 100%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            animation: slideIn 0.6s ease-out;
        }

        .info-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
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
            transition: transform 0.3s ease;
        }

        .contact-item:hover {
            transform: translateX(5px);
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
            transition: all 0.3s ease;
        }

        .contact-item:hover .contact-icon {
            background: var(--primary-blue);
        }

        .contact-icon i {
            font-size: 1rem;
            color: var(--primary-blue);
            transition: color 0.3s ease;
        }

        .contact-item:hover .contact-icon i {
            color: white;
        }

        .contact-info a {
            color: var(--primary-blue);
            font-weight: 500;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }

        .contact-info a:hover {
            color: var(--dark-blue);
        }

        /* Footer */
        .footer-note {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-gray);
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .footer-note p {
            color: var(--text-gray);
            font-size: 0.875rem;
            margin: 0;
        }

        .footer-note i {
            color: var(--primary-blue);
            animation: shieldPulse 2s ease-in-out infinite;
        }

        @keyframes shieldPulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        /* Responsive Styles */
        @media (max-width: 991px) {
            .page-header h1 {
                font-size: 1.875rem;
            }
            
            .info-card {
                margin-bottom: 1.5rem;
            }
            
            .countdown-display {
                gap: 1rem;
            }
            
            .countdown-unit {
                min-width: 80px;
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
                width: 80px;
                height: 80px;
            }

            .icon-wrapper::before {
                width: 100px;
                height: 100px;
            }

            .icon-wrapper::after {
                width: 120px;
                height: 120px;
            }

            .icon-wrapper i {
                font-size: 2rem;
            }

            .info-card-main,
            .info-card {
                padding: 1.5rem;
            }

            .main-content {
                padding: 1.5rem 0 2rem;
            }
            
            .countdown-container {
                padding: 1.5rem;
                margin: 1.5rem 0;
            }
            
            .countdown-display {
                gap: 0.75rem;
            }
            
            .countdown-unit {
                min-width: 70px;
                padding: 0.75rem;
            }
            
            .countdown-value {
                font-size: 2rem;
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
                width: 70px;
                height: 70px;
            }

            .icon-wrapper::before {
                width: 90px;
                height: 90px;
            }

            .icon-wrapper::after {
                width: 110px;
                height: 110px;
            }

            .icon-wrapper i {
                font-size: 1.75rem;
            }

            .info-card-main,
            .info-card {
                padding: 1.25rem;
            }
            
            .countdown-display {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            
            .countdown-unit {
                min-width: 60px;
                padding: 0.5rem;
            }
            
            .countdown-value {
                font-size: 1.75rem;
            }
            
            .countdown-label {
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background with HRM Elements -->
    <div class="animated-background">
        <!-- Hexagon Grid Pattern -->
        <div class="hex-grid"></div>
        
        <!-- Gradient Orbs -->
        <div class="gradient-orb orb-1"></div>
        <div class="gradient-orb orb-2"></div>
        <div class="gradient-orb orb-3"></div>
        
        <!-- Floating Code Brackets and Symbols -->
        <div class="code-element code-1">&lt;/&gt;</div>
        <div class="code-element code-2">{ }</div>
        <div class="code-element code-3">[ ]</div>
        <div class="code-element code-4">( )</div>
        
        <!-- HRM Icons -->
        <i class="bi bi-cash-stack hrm-icon hrm-1"></i> <!-- Payroll -->
        <i class="bi bi-calendar-check hrm-icon hrm-2"></i> <!-- Attendance -->
        <i class="bi bi-person-badge hrm-icon hrm-3"></i> <!-- Employee ID -->
        <i class="bi bi-calendar-x hrm-icon hrm-4"></i> <!-- Leave Management -->
        <i class="bi bi-clock-history hrm-icon hrm-5"></i> <!-- Time Tracking -->
        <i class="bi bi-wallet2 hrm-icon hrm-6"></i> <!-- TADA/Expenses -->
        <i class="bi bi-people hrm-icon hrm-7"></i> <!-- Team/Employees -->
        <i class="bi bi-graph-up-arrow hrm-icon hrm-8"></i> <!-- Performance -->
        
        <!-- Binary Code Rain -->
        <div class="binary-line binary-1">01001000 01010010 01001101</div>
        <div class="binary-line binary-2">01010011 01111001 01110011</div>
        <div class="binary-line binary-3">01110100 01100101 01101101</div>
        <div class="binary-line binary-4">01010101 01110000 01100100</div>
        
        <!-- Circuit Lines -->
        <div class="circuit-line circuit-h-1"></div>
        <div class="circuit-line circuit-h-2"></div>
        <div class="circuit-line circuit-v-1"></div>
        <div class="circuit-line circuit-v-2"></div>
        
        <!-- Floating Particles -->
        <div class="particle particle-1"></div>
        <div class="particle particle-2"></div>
        <div class="particle particle-3"></div>
        <div class="particle particle-4"></div>
        <div class="particle particle-5"></div>
    </div>

    <!-- Header Section -->
    <header class="page-header">
        <div class="container">
            <div class="icon-wrapper">
                <i class="bi bi-tools"></i>
            </div>
            <h1>Site Under Maintenance</h1>
            <p class="subtitle">We'll be back soon!</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">
                    <!-- Info Card -->
                    <div class="info-card-main">
                        <div class="d-flex flex-column flex-sm-row align-items-start">
                            <i class="bi bi-info-circle-fill info-icon me-0 me-sm-3 mb-3 mb-sm-0"></i>
                            <div>
                                <h5>Scheduled Maintenance in Progress</h5>
                                <p>Our HRM system is currently undergoing scheduled maintenance to improve performance and add new features. We apologize for any inconvenience and appreciate your patience.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Countdown Timer -->
                    <div class="countdown-container">
                        <div class="countdown-title">We will be back in</div>
                        <div class="countdown-display">
                            <div class="countdown-unit">
                                <div id="countdown-hours" class="countdown-value">04</div>
                                <div class="countdown-label">Hours</div>
                            </div>
                            <div class="countdown-unit">
                                <div id="countdown-minutes" class="countdown-value">30</div>
                                <div class="countdown-label">Minutes</div>
                            </div>
                            <div class="countdown-unit">
                                <div id="countdown-seconds" class="countdown-value">00</div>
                                <div class="countdown-label">Seconds</div>
                            </div>
                        </div>
                        <div class="countdown-note">
                            Expected completion: <strong>February 3, 2026 at 6:00 PM</strong>
                        </div>
                    </div>

                    <!-- Info Cards -->
                    <div class="row g-3 g-lg-4">
                        <!-- Sales Card -->
                        <div class="col-12 col-lg-6">
                            <div class="info-card">
                                <div class="info-card-header">Sales</div>
                                <h5>Interested in Our Services?</h5>
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
                            <i class="bi bi-shield-check me-2"></i>
                            All your data is safe and secure. We'll be back online shortly!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Countdown Timer (Example - set your actual end time)
        const endTime = new Date();
        endTime.setHours(endTime.getHours() + 4);
        endTime.setMinutes(endTime.getMinutes() + 30);
        
        function updateCountdown() {
            const now = new Date().getTime();
            const diff = endTime - now;
            
            if (diff <= 0) {
                clearInterval(timer);
                location.reload();
                return;
            }
            
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            document.getElementById('countdown-hours').textContent = 
                hours.toString().padStart(2, '0');
            document.getElementById('countdown-minutes').textContent = 
                minutes.toString().padStart(2, '0');
            document.getElementById('countdown-seconds').textContent = 
                seconds.toString().padStart(2, '0');
        }
        
        // Initialize countdown
        updateCountdown();
        const timer = setInterval(updateCountdown, 1000);
        
        // Add scale animation when countdown values change
        const countdownUnits = document.querySelectorAll('.countdown-value');
        countdownUnits.forEach(unit => {
            let lastValue = unit.textContent;
            
            setInterval(() => {
                if (unit.textContent !== lastValue) {
                    unit.style.transform = 'scale(1.15)';
                    setTimeout(() => {
                        unit.style.transform = 'scale(1)';
                    }, 150);
                    lastValue = unit.textContent;
                }
            }, 50);
        });
    </script>
</body>
</html>