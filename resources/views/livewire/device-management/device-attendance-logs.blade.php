<div class="container-fluid p-3 p-lg-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3 px-4">
            <div>
                <h5 class="mb-1 fw-semibold">Device Log</h5>
                <small class="text-muted">{{ $businessName ?? 'N/A' }} • {{ $deviceSn ?? 'N/A' }}</small>
            </div>

        </div>

        <div class="card-body p-3 p-lg-4">
            <div class="row g-3 g-lg-4">
                <!-- Compact Professional Calendar -->
                <div class="col-lg-4 col-xl-3">
                    <div class="calendar-card">
                        <!-- Month Selector -->
                        <div class="month-selector">
                            <button wire:click="previousMonth" class="btn-nav">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <h6 class="month-title">
                                {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('M Y') }}</h6>
                            <button wire:click="nextMonth" class="btn-nav">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>

                        <!-- Mini Calendar -->
                        <div class="mini-calendar">
                            <div class="weekday-row">
                                @foreach (['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $day)
                                    <div class="weekday">{{ $day }}</div>
                                @endforeach
                            </div>

                            <div class="days-grid">
    @php
        $calendarDays = $this->generateCalendarDays();
    @endphp

    @foreach ($calendarDays as $day)
        @if ($day)
            @php
                // Determine if this date has device or manual logs
                $hasDevice = isset($this->originalAttendanceRecords[$day['date']])
                    && collect($this->originalAttendanceRecords[$day['date']])
                        ->contains(fn($r) => $r['source'] === 'record');

                $hasManual = isset($this->originalAttendanceRecords[$day['date']])
                    && collect($this->originalAttendanceRecords[$day['date']])
                        ->contains(fn($r) => $r['source'] === 'log');
            @endphp

            <button
                wire:click="selectDate('{{ $day['date'] }}')"
                class="day-cell
                    {{ $day['date'] === $selectedDate ? 'active' : '' }}
                    {{ $hasDevice && $hasManual ? 'has-both' : '' }}
                    {{ $hasDevice && !$hasManual ? 'has-device' : '' }}
                    {{ !$hasDevice && $hasManual ? 'has-manual' : '' }}"
            >
                {{ $day['day'] }}

                @if ($hasDevice || $hasManual)
                    <span class="dot"></span>
                @endif
            </button>
        @else
            <div class="day-cell empty"></div>
        @endif
    @endforeach
</div>

                        </div>

                        <!-- Legend -->
                        <div class="legend mt-3">
                            <div class="legend-item">
                                <span class="dot device"></span>
                                <small>Device</small>
                            </div>
                            <div class="legend-item">
                                <span class="dot manual"></span>
                                <small>Manual</small>
                            </div>
                            <div class="legend-item">
                                <span class="dot both"></span>
                                <small>Both</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Details -->
                <div class="col-lg-8 col-xl-9">
                    <div class="details-card">
                        <div class="details-header">
                            <div>
                                <h6 class="mb-1 fw-semibold">
                                    {{ \Carbon\Carbon::parse($selectedDate)->format('l, M j, Y') }}</h6>
                                <small class="text-muted">Attendance Records</small>
                            </div>
                        </div>

                        @if (empty($groupedRecords[$selectedDate]) && empty($originalAttendanceRecords[$selectedDate]))
                            <div class="no-data">
                                <i class="bi bi-calendar-x"></i>
                                <p>No records found for this date</p>
                            </div>
                        @else
                            <div class="row g-3">
                                <!-- Device Logs Panel -->
                                <div class="col-md-6">
                                    <div class="data-panel device-panel">
                                        <div class="panel-header">
                                            <i class="bi bi-hdd-network"></i>
                                            <span>Device Logs</span>
                                            <span
                                                class="count">{{ count($groupedRecords[$selectedDate] ?? []) }}</span>
                                        </div>
                                        <div class="panel-body">
                                            @if (empty($groupedRecords[$selectedDate]))
                                                <div class="empty-state">No device logs</div>
                                            @else
                                                @foreach ($groupedRecords[$selectedDate] as $user_id => $record)
                                                    {{-- @dd($groupedRecords); --}}
                                                    <div class="record-item">
                                                        <div class="record-main">
                                                            <span class="emp-code">{{ $user_id }}</span>
                                                            <span class="emp-name">{{ $record['name'] }}</span>
                                                        </div>
                                                        <div class="record-times">
                                                            <span class="time in">
                                                                <i class="bi bi-box-arrow-in-right"></i>
                                                                {{ \Carbon\Carbon::parse($record['check_in'])->format('h:i A') }}
                                                            </span>
                                                            <span class="separator">→</span>
                                                            <span class="time out">
                                                                <i class="bi bi-box-arrow-right"></i>
                                                                {{ $record['check_out'] ? \Carbon\Carbon::parse($record['check_out'])->format('h:i A') : '--:--' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Original Records Panel -->
                                <div class="col-md-6">
                                    <div class="data-panel manual-panel">
                                        <div class="panel-header">
                                            <i class="bi bi-journal-text"></i>
                                            <span>Fix HR Logs</span>
                                            <span
                                                class="count">{{ count($originalAttendanceRecords[$selectedDate] ?? []) }}</span>
                                        </div>
                                        <div class="panel-body">
                                            @if (empty($originalAttendanceRecords[$selectedDate]))
                                                <div class="empty-state">No system records</div>
                                            @else
                                                @foreach ($originalAttendanceRecords[$selectedDate] as $record)
                                                    @php
                                                        // Simply mark as manual if the source is from the attendance log table
                                                        $isManual =
                                                            isset($record['source']) && $record['source'] === 'log';
                                                    @endphp
                                                    <div class="record-item {{ $isManual ? 'manual-entry' : '' }}">
                                                        <div class="record-main">
                                                            <span class="emp-code">{{ $record['emp_code'] }}</span>
                                                            <span class="emp-name">{{ $record['name'] }}</span>
                                                            @if ($isManual)
                                                                <span class="manual-badge">Manual</span>
                                                            @endif
                                                        </div>
                                                        <div class="record-times">
                                                            <span class="time in">
                                                                <i class="bi bi-box-arrow-in-right"></i>
                                                                {{ $record['check_in_time'] ? \Carbon\Carbon::parse($record['check_in_time'])->format('h:i A') : '--:--' }}
                                                            </span>
                                                            <span class="separator">→</span>
                                                            <span class="time out">
                                                                <i class="bi bi-box-arrow-right"></i>
                                                                {{ $record['check_out_time'] ? \Carbon\Carbon::parse($record['check_out_time'])->format('h:i A') : '--:--' }}
                                                            </span>
                                                        </div>
                                                        {{-- @if ($record['total_worked_hours'])
                                                            <div class="worked-hours">
                                                                <i class="bi bi-clock"></i> {{ $record['total_worked_hours'] }}
                                                            </div>
                                                        @endif --}}
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>



    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
        }

        /* Calendar Card */
        .calendar-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid var(--gray-200);
        }

        /* Month Selector */
        .month-selector {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .month-title {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-900);
        }

        .btn-nav {
            width: 32px;
            height: 32px;
            border: none;
            background: var(--gray-100);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--gray-600);
        }

        .btn-nav:hover {
            background: var(--gray-200);
            color: var(--gray-900);
        }

        /* Mini Calendar */
        .mini-calendar {
            font-size: 11px;
        }

        .weekday-row {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
            margin-bottom: 4px;
        }

        .weekday {
            text-align: center;
            font-weight: 600;
            color: var(--gray-400);
            padding: 4px;
            font-size: 10px;
        }

        .days-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }

        .day-cell {
            aspect-ratio: 1;
            border: none;
            background: transparent;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 11px;
            color: var(--gray-700);
            position: relative;
            transition: all 0.2s;
            font-weight: 500;
        }

        .day-cell:not(.empty):hover {
            background: var(--gray-100);
        }

        .day-cell.empty {
            cursor: default;
        }

        .day-cell.active {
            background: var(--primary);
            color: white;
            font-weight: 600;
        }

        .day-cell .dot {
            position: absolute;
            bottom: 4px;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--success);
        }

        .day-cell.has-device .dot {
            background: var(--success);
        }

        .day-cell.has-manual .dot {
            background: var(--danger);
        }

        .day-cell.has-both .dot {
            background: var(--warning);
        }

        /* Legend */
        .legend {
            display: flex;
            gap: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .legend-item small {
            font-size: 10px;
            color: var(--gray-600);
        }

        .legend-item .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .legend-item .dot.device {
            background: var(--success);
        }

        .legend-item .dot.manual {
            background: var(--danger);
        }

        .legend-item .dot.both {
            background: var(--warning);
        }

        /* Details Card */
        .details-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid var(--gray-200);
            min-height: 500px;
        }

        .details-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .details-header h6 {
            font-size: 14px;
            margin: 0;
        }

        .details-header small {
            font-size: 11px;
        }

        /* No Data State */
        .no-data {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem 1rem;
            color: var(--gray-400);
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .no-data p {
            margin: 0;
            font-size: 13px;
        }

        /* Data Panels */
        .data-panel {
            background: var(--gray-50);
            border-radius: 10px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .panel-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            background: white;
            border-bottom: 1px solid var(--gray-200);
            font-size: 12px;
            font-weight: 600;
            color: var(--gray-700);
        }

        .panel-header i {
            font-size: 14px;
        }

        .panel-header .count {
            margin-left: auto;
            background: var(--gray-100);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
        }

        .device-panel .panel-header {
            color: var(--success);
        }

        .manual-panel .panel-header {
            color: var(--primary);
        }

        .panel-body {
            padding: 12px;
            overflow-y: auto;
            max-height: 450px;
            flex: 1;
        }

        /* Record Items */
        .record-item {
            background: white;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            border: 1px solid var(--gray-200);
            transition: all 0.2s;
        }

        .record-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-color: var(--gray-300);
        }

        .record-item.manual-entry {
            background: #fef2f2;
            border-color: #fecaca;
        }

        .record-main {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .emp-code {
            background: var(--gray-100);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            color: var(--gray-600);
        }

        .emp-name {
            font-size: 12px;
            font-weight: 500;
            color: var(--gray-900);
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .manual-badge {
            background: var(--danger);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .record-times {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
        }

        .time {
            display: flex;
            align-items: center;
            gap: 4px;
            color: var(--gray-600);
        }

        .time i {
            font-size: 10px;
        }

        .time.in {
            color: var(--success);
        }

        .time.out {
            color: var(--danger);
        }

        .separator {
            color: var(--gray-300);
        }

        .worked-hours {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid var(--gray-200);
            font-size: 10px;
            color: var(--gray-600);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--gray-400);
            font-size: 11px;
        }

        /* Scrollbar */
        .panel-body::-webkit-scrollbar {
            width: 4px;
        }

        .panel-body::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 2px;
        }

        .panel-body::-webkit-scrollbar-track {
            background: transparent;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .calendar-card {
                margin-bottom: 1rem;
            }

            .details-card {
                min-height: auto;
            }
        }

        @media (max-width: 767px) {
            .data-panel {
                margin-bottom: 1rem;
            }

            .panel-body {
                max-height: 300px;
            }

            .record-times {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }

            .separator {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .day-cell {
                font-size: 10px;
            }

            .weekday {
                font-size: 9px;
            }

            .details-card {
                padding: 1rem;
            }

            .details-header h6 {
                font-size: 13px;
            }
        }
    </style>

    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script> --}}

    <script>
        document.addEventListener('livewire:initialized', function() {
            // Auto-scroll to selected date on mobile
            if (window.innerWidth < 768) {
                const activeDay = document.querySelector('.day-cell.active');
                if (activeDay) {
                    activeDay.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }
        });
    </script>
</div>
