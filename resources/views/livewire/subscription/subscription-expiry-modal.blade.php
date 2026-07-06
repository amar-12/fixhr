<div>
    @if($showModal)
        @php
            $modalStyles = $this->getModalStyles();
            $notificationTitle = $this->getNotificationTitle();
            $notificationMessage = $this->getNotificationMessage();
        @endphp

        <!-- Backdrop -->
        <div class="swal-backdrop" wire:click="closeModal"></div>

        <!-- Modal -->
        <div class="swal-modal-wrapper" 
             wire:key="subscription-modal-{{ $subscription?->sub_id ?? 'none' }}"
             x-data="{
                countdown: 30,
                init() {
                    if (this.countdown > 0 && '{{ $notificationType }}' !== 'danger') {
                        let timer = setInterval(() => {
                            this.countdown--;
                            if (this.countdown <= 0) {
                                clearInterval(timer);
                                $wire.closeModal();
                            }
                        }, 1000);
                    }
                }
             }">
            
            <div class="swal-modal {{ 'swal-' . $notificationType }}" :class="{ 'closing': $wire.isClosing }">
                
                <!-- Icon -->
                <div class="swal-icon {{ 'icon-' . $notificationType }}">
                    <i class="bi {{ $modalStyles['icon'] }}"></i>
                </div>

                <!-- Title -->
                <h2 class="swal-title">{!! strip_tags($notificationTitle) !!}</h2>
                <p class="swal-subtitle">{{ $planName }} • {{ $businessName }}</p>

                <!-- Content -->
                <div class="swal-content">
                    
                    <!-- Stats -->
                    <div class="swal-stats">
                        <div class="stat-card {{ 'card-' . $notificationType }}">
                            <div class="stat-icon {{ 'icon-bg-' . $notificationType }}">
                                <i class="bi bi-calendar-x"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-num">{{ $daysLeft }}</div>
                                <div class="stat-label">{{ $daysLeft === 1 ? 'Day' : 'Days' }} Left</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon icon-bg-secondary">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-num">{{ \Carbon\Carbon::parse($endDate)->format('d M') }}</div>
                                <div class="stat-label">Expires On</div>
                            </div>
                        </div>
                    </div>

                    <!-- Message -->
                    <div class="swal-message {{ 'msg-' . $notificationType }}">
                        <i class="bi bi-info-circle"></i>
                        <p>{!! $notificationMessage !!}</p>
                    </div>

                    <!-- Progress -->
                    <div class="swal-progress">
                        <div class="progress-header">
                            <span>Subscription Status</span>
                            <span class="badge {{ 'badge-' . $notificationType }}">{{ $daysLeft }}/5 days</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill {{ 'fill-' . $notificationType }}" 
                                 style="width: {{ $progressPercentage }}%;">
                                <span class="progress-shimmer"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Timer -->
                    @if($notificationType !== 'danger')
                        <div class="swal-timer">
                            <i class="bi bi-clock-history"></i>
                            Auto-closing in <strong x-text="countdown"></strong>s
                        </div>
                    @endif
                </div>

                <!-- Buttons -->
                <div class="swal-buttons">
                    <button class="swal-btn swal-btn-secondary" wire:click="remindLater">
                        <i class="bi bi-clock"></i>
                        <span>Remind Later</span>
                    </button>
                    @if($notificationType === 'danger')
                        <button class="swal-btn swal-btn-outline" wire:click="contactSupport">
                            <i class="bi bi-headset"></i>
                            <span>Contact Support</span>
                        </button>
                    @else
                        <button class="swal-btn swal-btn-primary {{ 'btn-' . $notificationType }}" wire:click="closeModal">
                            <i class="bi bi-check-circle"></i>
                            <span>Understood</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <style>
            .swal-backdrop {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.45);
                backdrop-filter: blur(6px);
                z-index: 9998;
                animation: fadeIn 0.2s ease;
            }

            .swal-modal-wrapper {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                padding: 20px;
                pointer-events: none;
            }

            .swal-modal {
                background: #fff;
                border-radius: 20px;
                max-width: 500px;
                width: 100%;
                padding: 36px;
                box-shadow: 0 20px 60px rgba(34, 153, 221, 0.15);
                animation: slideUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
                pointer-events: auto;
                text-align: center;
            }

            .swal-modal.closing {
                animation: slideDown 0.2s ease forwards;
            }

            /* Icon */
            .swal-icon {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                margin: 0 auto 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 36px;
                color: #fff;
                position: relative;
            }

            .swal-icon::before {
                content: '';
                position: absolute;
                width: 100%;
                height: 100%;
                border-radius: 50%;
                background: inherit;
                opacity: 0.2;
                animation: pulse 2s ease infinite;
            }

            .icon-info {
                background: linear-gradient(135deg, #2299dd 0%, #1976d2 100%);
                box-shadow: 0 8px 24px rgba(34, 153, 221, 0.35);
            }

            .icon-warning {
                background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                box-shadow: 0 8px 24px rgba(245, 158, 11, 0.35);
            }

            .icon-danger {
                background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
                box-shadow: 0 8px 24px rgba(220, 38, 38, 0.35);
            }

            /* Title */
            .swal-title {
                font-size: 24px;
                font-weight: 700;
                color: #1e293b;
                margin: 0 0 8px;
                line-height: 1.3;
            }

            .swal-subtitle {
                font-size: 13px;
                color: #94a3b8;
                margin: 0 0 28px;
                font-weight: 500;
            }

            /* Content */
            .swal-content {
                margin-bottom: 28px;
            }

            /* Stats Cards */
            .swal-stats {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
                margin-bottom: 20px;
            }

            .stat-card {
                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 20px;
                display: flex;
                align-items: center;
                gap: 14px;
                transition: all 0.3s ease;
            }

            .stat-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            }

            .card-info {
                border-left: 3px solid #2299dd;
            }

            .card-warning {
                border-left: 3px solid #f59e0b;
            }

            .card-danger {
                border-left: 3px solid #dc2626;
            }

            .stat-icon {
                width: 42px;
                height: 42px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-size: 18px;
                flex-shrink: 0;
            }

            .icon-bg-info {
                background: linear-gradient(135deg, #2299dd 0%, #1976d2 100%);
            }

            .icon-bg-warning {
                background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            }

            .icon-bg-danger {
                background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            }

            .icon-bg-secondary {
                background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            }

            .stat-info {
                text-align: left;
                flex: 1;
            }

            .stat-num {
                font-size: 22px;
                font-weight: 700;
                color: #1e293b;
                line-height: 1;
                margin-bottom: 4px;
            }

            .stat-label {
                font-size: 11px;
                color: #94a3b8;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-weight: 600;
            }

            /* Message */
            .swal-message {
                background: #f8fafc;
                border-radius: 12px;
                padding: 16px;
                margin-bottom: 20px;
                display: flex;
                gap: 12px;
                align-items: flex-start;
                text-align: left;
            }

            .swal-message i {
                font-size: 18px;
                flex-shrink: 0;
                margin-top: 2px;
            }

            .msg-info {
                border-left: 3px solid #2299dd;
            }

            .msg-info i {
                color: #2299dd;
            }

            .msg-warning {
                border-left: 3px solid #f59e0b;
                background: #fffbeb;
            }

            .msg-warning i {
                color: #f59e0b;
            }

            .msg-danger {
                border-left: 3px solid #dc2626;
                background: #fef2f2;
            }

            .msg-danger i {
                color: #dc2626;
            }

            .swal-message p {
                margin: 0;
                font-size: 13px;
                line-height: 1.6;
                color: #475569;
            }

            /* Progress */
            .swal-progress {
                margin-bottom: 16px;
            }

            .progress-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
                font-size: 12px;
                font-weight: 600;
                color: #64748b;
            }

            .badge {
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 700;
                color: #fff;
            }

            .badge-info {
                background: linear-gradient(135deg, #2299dd 0%, #1976d2 100%);
            }

            .badge-warning {
                background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            }

            .badge-danger {
                background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            }

            .progress-track {
                height: 10px;
                background: #e2e8f0;
                border-radius: 100px;
                overflow: hidden;
                position: relative;
            }

            .progress-fill {
                height: 100%;
                border-radius: 100px;
                transition: width 0.5s ease;
                position: relative;
                overflow: hidden;
            }

            .progress-shimmer {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(
                    90deg,
                    transparent,
                    rgba(255, 255, 255, 0.4),
                    transparent
                );
                animation: shimmer 2s infinite;
            }

            .fill-info {
                background: linear-gradient(90deg, #2299dd 0%, #1976d2 100%);
            }

            .fill-warning {
                background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
            }

            .fill-danger {
                background: linear-gradient(90deg, #dc2626 0%, #b91c1c 100%);
            }

            /* Timer */
            .swal-timer {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 18px;
                background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
                border-radius: 100px;
                font-size: 12px;
                color: #64748b;
                border: 1px solid #cbd5e1;
            }

            .swal-timer i {
                font-size: 14px;
                color: #2299dd;
            }

            /* Buttons */
            .swal-buttons {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }

            .swal-btn {
                flex: 1;
                min-width: 140px;
                padding: 14px 24px;
                border-radius: 12px;
                font-size: 14px;
                font-weight: 600;
                border: none;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                position: relative;
                overflow: hidden;
            }

            .swal-btn::before {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                transform: translate(-50%, -50%);
                transition: width 0.6s, height 0.6s;
            }

            .swal-btn:hover::before {
                width: 300px;
                height: 300px;
            }

            .swal-btn i {
                font-size: 16px;
                position: relative;
                z-index: 1;
            }

            .swal-btn span {
                position: relative;
                z-index: 1;
            }

            .swal-btn-primary {
                color: #fff;
            }

            .btn-info {
                background: linear-gradient(135deg, #2299dd 0%, #1976d2 100%);
                box-shadow: 0 4px 16px rgba(34, 153, 221, 0.4);
            }

            .btn-warning {
                background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                box-shadow: 0 4px 16px rgba(245, 158, 11, 0.4);
            }

            .btn-danger {
                background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
                box-shadow: 0 4px 16px rgba(220, 38, 38, 0.4);
            }

            .swal-btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(34, 153, 221, 0.5);
            }

            .swal-btn-secondary {
                background: #fff;
                color: #64748b;
                border: 2px solid #e2e8f0;
            }

            .swal-btn-secondary:hover {
                background: #f8fafc;
                border-color: #cbd5e1;
                transform: translateY(-2px);
            }

            .swal-btn-outline {
                background: transparent;
                color: #475569;
                border: 2px solid #cbd5e1;
            }

            .swal-btn-outline:hover {
                background: #f1f5f9;
                border-color: #94a3b8;
            }

            /* Animations */
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(40px) scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            @keyframes slideDown {
                from {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
                to {
                    opacity: 0;
                    transform: translateY(40px) scale(0.9);
                }
            }

            @keyframes pulse {
                0%, 100% {
                    transform: scale(1);
                    opacity: 0.2;
                }
                50% {
                    transform: scale(1.1);
                    opacity: 0.3;
                }
            }

            @keyframes shimmer {
                0% {
                    transform: translateX(-100%);
                }
                100% {
                    transform: translateX(100%);
                }
            }

            /* Mobile */
            @media (max-width: 640px) {
                .swal-modal {
                    padding: 28px;
                }

                .swal-icon {
                    width: 70px;
                    height: 70px;
                    font-size: 30px;
                }

                .swal-title {
                    font-size: 20px;
                }

                .swal-stats {
                    gap: 10px;
                }

                .stat-card {
                    padding: 16px;
                    gap: 12px;
                }

                .stat-icon {
                    width: 38px;
                    height: 38px;
                    font-size: 16px;
                }

                .stat-num {
                    font-size: 20px;
                }

                .swal-btn {
                    min-width: 100px;
                    padding: 12px 18px;
                    font-size: 13px;
                }
            }
        </style>
    @endif
</div>