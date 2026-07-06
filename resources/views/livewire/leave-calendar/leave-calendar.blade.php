<div
    class="lc-root"
    x-data="{
        confirmId: null,
        confirmCat: '',
        confirmDates: '',
        showConfirm: false,
        openConfirm(id, cat, dates) {
            this.confirmId = id;
            this.confirmCat = cat;
            this.confirmDates = dates;
            this.showConfirm = true;
        },
        closeConfirm() { this.showConfirm = false; this.confirmId = null; }
    }">

    {{-- ═══════════════════════════════════════════
     STYLES
═══════════════════════════════════════════ --}}
    <style>
        :root {
            --ink: #0f172a;
            --ink-2: #1e293b;
            --ink-3: #334155;
            --muted: #64748b;
            --muted-2: #94a3b8;
            --border: #e2e8f0;
            --border-2: #f1f5f9;
            --surface: #ffffff;
            --bg: #f8fafc;

            --primary: #2563eb;
            --primary-dk: #1d4ed8;
            --primary-lt: #eff6ff;
            --primary-md: #bfdbfe;

            --emerald: #059669;
            --amber: #d97706;
            --rose: #e11d48;
            --violet: #7c3aed;
            --sky: #0284c7;
            --teal: #0f766e;
            --orange: #ea580c;
            --slate: #475569;
            --pink: #db2777;
            --cyan: #0891b2;

            --radius-sm: 8px;
            --radius: 14px;
            --radius-lg: 20px;
            --radius-xl: 28px;

            --shadow-xs: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, .07), 0 1px 3px rgba(0, 0, 0, .04);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, .08), 0 2px 6px rgba(0, 0, 0, .04);
            --shadow-lg: 0 12px 40px rgba(0, 0, 0, .12), 0 4px 12px rgba(0, 0, 0, .06);
            --shadow-xl: 0 20px 60px rgba(0, 0, 0, .16), 0 8px 20px rgba(0, 0, 0, .08);

            --ease-out: cubic-bezier(.16, 1, .3, 1);
        }

        /* ── Reset ── */
        .lc-root *,
        .lc-root *::before,
        .lc-root *::after {
            box-sizing: border-box;
            font-family: 'Figtree', sans-serif;
            margin: 0;
            padding: 0;
        }

        /* ── Root ── */
        .lc-root {
            background: var(--bg);
            min-height: 100vh;
            padding: 28px 28px 40px;
        }

        /* ─────────────────────────────────────────
   TOP BAR
───────────────────────────────────────── */
        .lc-topbar {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 26px;
            flex-wrap: wrap;
        }

        .lc-page-title {
            font-family: 'Syne', sans-serif;
            font-size: 26px;
            font-weight: 800;
            color: var(--ink);
            letter-spacing: -.5px;
            flex: 0 0 auto;
        }

        .lc-page-title span {
            background: linear-gradient(135deg, var(--primary), #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Search */
        .lc-search-wrap {
            position: relative;
            flex: 1;
            max-width: 360px;
            min-width: 220px;
        }

        .lc-search-input {
            width: 100%;
            padding: 11px 44px 11px 18px;
            border: 1.5px solid var(--border);
            border-radius: 40px;
            font-size: 14px;
            color: var(--ink);
            background: var(--surface);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            box-shadow: var(--shadow-xs);
        }

        .lc-search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .1);
        }

        .lc-search-input::placeholder {
            color: var(--muted-2);
        }

        .lc-search-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            pointer-events: none;
        }

        .lc-search-drop {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            z-index: 200;
            overflow: hidden;
            max-height: 260px;
            overflow-y: auto;
        }

        .lc-search-drop::-webkit-scrollbar {
            width: 4px;
        }

        .lc-search-drop::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        .lc-drop-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            cursor: pointer;
            transition: background .15s;
            border-bottom: 1px solid var(--border-2);
        }

        .lc-drop-item:last-child {
            border-bottom: none;
        }

        .lc-drop-item:hover {
            background: var(--primary-lt);
        }

        .lc-drop-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), #60a5fa);
            color: #fff;
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 12px;
            flex-shrink: 0;
            font-family: 'Syne', sans-serif;
        }

        .lc-drop-name {
            font-weight: 600;
            font-size: 13.5px;
            color: var(--ink);
        }

        .lc-drop-code {
            font-size: 11.5px;
            color: var(--muted);
        }

        /* Flash */
        .lc-flash {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 18px;
            border-radius: var(--radius);
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 20px;
            animation: slideDown .3s var(--ease-out);
            border: 1px solid;
        }

        .lc-flash.success {
            background: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0;
        }

        .lc-flash.error {
            background: #fff1f2;
            color: #9f1239;
            border-color: #fecdd3;
        }

        /* ── Attendance row inside each day cell ── */
        .lc-atd-row {
            display: flex;
            align-items: center;
            gap: 3px;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 4px;
            border-radius: 4px;
            margin-bottom: 3px;
            letter-spacing: .03em;
            overflow: hidden;
            white-space: nowrap;
        }

        .lc-atd-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
            background: currentColor;
            opacity: .7;
        }

        .lc-atd-code {
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .04em;
        }

        .lc-atd-time {
            font-size: 10px;
            font-weight: 500;
            opacity: .75;
            margin-left: 1px;
        }

        .lc-atd-flag {
            font-size: 8px;
            font-weight: 800;
            padding: 1px 3px;
            border-radius: 3px;
            line-height: 1;
            margin-left: auto;
        }

        .lc-flag-late {
            background: rgba(217, 119, 6, .18);
            color: #92400e;
        }

        .lc-flag-early {
            background: rgba(220, 38, 38, .15);
            color: #991b1b;
        }

        /* Status colour variants */
        .atd-present {
            background: #f0fdf4;
            color: #15803d;
        }

        .atd-absent {
            background: #fff1f2;
            color: #be123c;
        }

        .atd-holiday {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .atd-weekoff {
            background: #f5f3ff;
            color: #6d28d9;
        }

        .atd-halfday {
            background: #fffbeb;
            color: #b45309;
        }

        .atd-missed {
            background: #fff7ed;
            color: #c2410c;
        }

        .atd-leave {
            background: #ecfeff;
            color: #0e7490;
        }

        .atd-unknown {
            background: #f8fafc;
            color: #64748b;
        }

        /* Slightly taller day cells when attendance is shown */
        .lc-day.has-atd {
            min-height: 105px;

        }

        @keyframes slideDown {
            from {
                transform: translateY(-12px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* ─────────────────────────────────────────
   PROFILE CARD
───────────────────────────────────────── */
        .lc-profile {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1e40af 100%);
            border-radius: var(--radius-lg);
            padding: 26px 30px;
            display: flex;
            align-items: center;
            gap: 22px;
            margin-bottom: 24px;
            box-shadow: 0 8px 32px rgba(37, 99, 235, .25);
            position: relative;
            overflow: hidden;
        }

        .lc-profile::after {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .04);
            pointer-events: none;
        }

        .lc-profile::before {
            content: '';
            position: absolute;
            bottom: -40px;
            left: 30%;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .03);
            pointer-events: none;
        }

        .lc-prof-av {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .15);
            border: 2.5px solid rgba(255, 255, 255, .3);
            display: grid;
            place-items: center;
            font-family: 'Syne', sans-serif;
            font-size: 26px;
            font-weight: 800;
            color: #fff;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .3);
        }

        .lc-prof-info {
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .lc-prof-name {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 19px;
            color: #fff;
            margin-bottom: 6px;
        }

        .lc-prof-meta {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .lc-prof-tag {
            display: flex;
            align-items: center;
            gap: 5px;
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .15);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 12px;
            color: rgba(255, 255, 255, .85);
            font-weight: 500;
        }

        .lc-prof-stats {
            display: flex;
            gap: 2px;
            position: relative;
            z-index: 1;
        }

        .lc-prof-stat {
            text-align: center;
            padding: 10px 20px;
            border-left: 1px solid rgba(255, 255, 255, .12);
        }

        .lc-prof-stat:first-child {
            border-left: none;
        }

        .lc-prof-stat-val {
            font-family: 'Syne', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #fff;
            line-height: 1;
        }

        .lc-prof-stat-lbl {
            font-size: 10.5px;
            color: rgba(255, 255, 255, .6);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-top: 4px;
        }

        /* ─────────────────────────────────────────
   STATS STRIP
───────────────────────────────────────── */
        .lc-stats-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .lc-stat-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 16px 18px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 14px;
            transition: transform .2s, box-shadow .2s;
        }

        .lc-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .lc-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: var(--radius-sm);
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        .lc-stat-icon.blue {
            background: #eff6ff;
            color: var(--primary);
        }

        .lc-stat-icon.green {
            background: #ecfdf5;
            color: var(--emerald);
        }

        .lc-stat-icon.amber {
            background: #fffbeb;
            color: var(--amber);
        }

        .lc-stat-icon.rose {
            background: #fff1f2;
            color: var(--rose);
        }

        .lc-stat-val {
            font-family: 'Syne', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: var(--ink);
            line-height: 1;
        }

        .lc-stat-lbl {
            font-size: 11.5px;
            color: var(--muted);
            margin-top: 3px;
            font-weight: 500;
        }

        /* ─────────────────────────────────────────
   SELECTED LEAVE DETAIL BAR
───────────────────────────────────────── */
        .lc-detail-bar {
            background: var(--surface);
            border: 1.5px solid var(--primary-md);
            border-radius: var(--radius);
            padding: 0;
            margin-bottom: 24px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(37, 99, 235, .1);
            animation: fadeIn .3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .lc-detail-bar-head {
            background: linear-gradient(90deg, var(--primary-lt), #fff);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--primary-md);
        }

        .lc-detail-bar-title {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 13px;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .lc-detail-grid {
            display: flex;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 0;
        }

        .lc-detail-cell {
            padding: 13px 20px;
            border-right: 1px solid var(--border-2);
        }

        .lc-detail-cell:last-child {
            border-right: none;
            grid-column: 1 / -1;
        }

        .lc-detail-cell label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
            display: block;
            margin-bottom: 4px;
        }

        .lc-detail-cell span {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
        }

        /* ─────────────────────────────────────────
   MAIN GRID
───────────────────────────────────────── */
        .lc-main-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 22px;
            align-items: start;
        }

        @media (max-width: 1150px) {
            .lc-main-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ─────────────────────────────────────────
   CALENDAR CARD
───────────────────────────────────────── */
        .lc-cal {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .lc-cal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px 16px;
            border-bottom: 1px solid var(--border-2);
            background: linear-gradient(180deg, var(--surface) 0%, #fafbff 100%);
        }

        .lc-cal-month-label {
            font-family: 'Syne', sans-serif;
            font-size: 21px;
            font-weight: 800;
            color: var(--ink);
            letter-spacing: -.3px;
        }

        .lc-cal-nav-group {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .lc-nav-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1.5px solid var(--border);
            background: var(--surface);
            color: var(--ink-3);
            cursor: pointer;
            display: grid;
            place-items: center;
            transition: all .18s;
            box-shadow: var(--shadow-xs);
        }

        .lc-nav-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, .3);
        }

        .lc-today-btn {
            padding: 7px 18px;
            border-radius: 40px;
            border: 1.5px solid var(--primary);
            background: transparent;
            color: var(--primary);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all .18s;
        }

        .lc-today-btn:hover {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 14px rgba(37, 99, 235, .3);
        }

        /* Day-of-week headers */
        .lc-dow-row {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }

        .lc-dow {
            text-align: center;
            padding: 12px 4px 10px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--muted);
            border-bottom: 1px solid var(--border-2);
        }

        .lc-dow:first-child {
            color: var(--rose);
        }

        .lc-dow:last-child {
            color: var(--primary);
        }

        /* Days grid */
        .lc-days-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }

        .lc-day {
            min-height: 90px;
            padding: 8px 7px 6px;
            border-right: 1px solid var(--border-2);
            border-bottom: 1px solid var(--border-2);
            cursor: pointer;
            position: relative;
            transition: background .15s;
            overflow: hidden;
        }

        .lc-day:nth-child(7n) {
            border-right: none;
        }

        .lc-day:hover {
            background: var(--primary-lt);
        }

        .lc-day.is-today {
            background: #f0f7ff;
        }

        .lc-day.is-today .lc-day-num {
            background: var(--primary);
            color: #fff;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(37, 99, 235, .4);
        }

        .lc-day.is-empty {
            background: #fafbfc;
            opacity: .5;
            cursor: default;
            pointer-events: none;
        }

        .lc-day.has-leave {
            background: #fefeff;
        }

        .lc-day-num {
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 5px;
            border-radius: 50%;
            transition: all .15s;
        }

        .lc-day:hover .lc-day-num:not(.is-today .lc-day-num) {
            background: rgba(37, 99, 235, .08);
        }

        /* Add-leave hint dot */
        .lc-day-add-hint {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--primary-lt);
            color: var(--primary);
            display: grid;
            place-items: center;
            opacity: 0;
            transition: opacity .15s;
            pointer-events: none;
        }

        .lc-day:hover .lc-day-add-hint {
            opacity: 1;
        }

        .lc-day-leaves {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        /* Badge */
        .lc-badge {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 9.5px;
            font-weight: 700;
            padding: 2.5px 5px;
            border-radius: 5px;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            transition: transform .12s, box-shadow .12s;
            border-left: 2.5px solid currentColor;
            letter-spacing: .02em;
        }

        .lc-badge:hover {
            transform: scale(1.04);
            box-shadow: 0 2px 6px rgba(0, 0, 0, .12);
        }

        .lc-badge-del {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
            border-radius: 3px;
            background: rgba(0, 0, 0, .08);
            display: grid;
            place-items: center;
            cursor: pointer;
            opacity: 0;
            transition: opacity .15s, background .15s;
        }

        .lc-badge:hover .lc-badge-del {
            opacity: 1;
        }

        .lc-badge-del:hover {
            background: var(--rose) !important;
            color: #fff;
        }

        /* Badge color variants */
        .badge-cl {
            background: #fefce8;
            color: #854d0e;
        }

        .badge-sl {
            background: #f0fdf4;
            color: #15803d;
        }

        .badge-el {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .badge-ml {
            background: #fdf2f8;
            color: #9d174d;
        }

        .badge-pl {
            background: #f0fdf4;
            color: #166534;
        }

        .badge-mrl {
            background: #faf5ff;
            color: #6b21a8;
        }

        .badge-bl {
            background: #fff7ed;
            color: #c2410c;
        }

        .badge-upl {
            background: #f8fafc;
            color: #475569;
        }

        .badge-co {
            background: #ecfeff;
            color: #155e75;
        }

        .badge-def {
            background: #f1f5f9;
            color: #475569;
        }

        /* More overflow badge */
        .lc-badge-more {
            font-size: 9px;
            font-weight: 700;
            color: var(--muted);
            background: var(--border-2);
            border-radius: 4px;
            padding: 2px 5px;
            display: inline-block;
            margin-top: 1px;
        }

        /* ─── Legend / Balances ─── */
        .lc-legend {
            padding: 18px 24px;
            border-top: 1px solid var(--border-2);
            background: #fafbff;
        }

        .lc-legend-title {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .lc-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }

        .lc-chip {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            border: 1.5px solid;
            cursor: default;
            transition: transform .15s, box-shadow .15s;
            position: relative;
        }

        .lc-chip:not(.lc-chip-dis):hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .lc-chip-dis {
            opacity: .4;
        }

        .lc-chip-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .lc-chip-bal {
            font-size: 10px;
            opacity: .7;
            margin-left: 2px;
            background: rgba(0, 0, 0, .06);
            border-radius: 10px;
            padding: 1px 6px;
        }

        /* ─────────────────────────────────────────
   RIGHT PANEL — Leave History
───────────────────────────────────────── */
        .lc-panel {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .lc-panel-head {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border-2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(180deg, var(--surface) 0%, #fafbff 100%);
        }

        .lc-panel-title {
            font-family: 'Syne', sans-serif;
            font-size: 16px;
            font-weight: 800;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .lc-panel-badge {
            background: var(--primary);
            color: #fff;
            font-size: 10.5px;
            font-weight: 700;
            font-family: 'Figtree', sans-serif;
            padding: 2px 9px;
            border-radius: 20px;
        }

        .lc-add-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 40px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all .18s;
            box-shadow: 0 3px 12px rgba(37, 99, 235, .3);
        }

        .lc-add-btn:hover {
            background: var(--primary-dk);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, .4);
        }

        .lc-panel-body {
            flex: 1;
            overflow-y: auto;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .lc-panel-body::-webkit-scrollbar {
            width: 5px;
        }

        .lc-panel-body::-webkit-scrollbar-track {
            background: transparent;
        }

        .lc-panel-body::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        /* Leave Item */
        .lc-leave-card {
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            padding: 14px 16px;
            cursor: pointer;
            transition: all .2s var(--ease-out);
            position: relative;
            overflow: hidden;
        }

        .lc-leave-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            border-radius: 4px 0 0 4px;
            background: var(--primary);
            transition: width .2s;
        }

        .lc-leave-card:hover {
            border-color: var(--primary-md);
            box-shadow: 0 4px 16px rgba(37, 99, 235, .1);
            transform: translateX(3px);
        }

        .lc-leave-card.lc-active {
            background: var(--primary-lt);
            border-color: var(--primary);
        }

        .lc-leave-card.lc-active::before {
            width: 5px;
        }

        .lc-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 9px;
        }

        .lc-card-cat {
            font-weight: 700;
            font-size: 13.5px;
            color: var(--ink);
            line-height: 1.3;
        }

        .lc-card-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .lc-status-pill {
            font-size: 10px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .st-approved {
            background: #ecfdf5;
            color: #065f46;
        }

        .st-pending {
            background: #fffbeb;
            color: #78350f;
        }

        .st-rejected {
            background: #fff1f2;
            color: #9f1239;
        }

        .lc-del-btn-panel {
            width: 28px;
            height: 28px;
            border: 1.5px solid var(--border);
            background: #fff;
            color: var(--muted);
            border-radius: var(--radius-sm);
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all .15s;
            opacity: 0;
        }

        .lc-leave-card:hover .lc-del-btn-panel {
            opacity: 1;
        }

        .lc-del-btn-panel:hover {
            background: var(--rose);
            border-color: var(--rose);
            color: #fff;
        }

        .lc-card-dates {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 12.5px;
            color: var(--muted);
            margin-bottom: 7px;
            font-weight: 500;
        }

        .lc-card-divider {
            color: var(--muted-2);
        }

        .lc-card-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .lc-card-days {
            font-size: 12px;
            color: var(--muted);
        }

        .lc-card-days strong {
            color: var(--primary);
            font-weight: 700;
            font-size: 14px;
        }

        .lc-card-applied {
            font-size: 10.5px;
            color: var(--muted-2);
        }

        .lc-card-reason {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed var(--border);
            font-size: 11.5px;
            color: var(--muted);
            line-height: 1.5;
            font-style: italic;
        }

        /* Empty */
        .lc-empty {
            text-align: center;
            padding: 48px 20px;
            color: var(--muted);
        }

        .lc-empty-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--border-2);
            margin: 0 auto 16px;
            display: grid;
            place-items: center;
            color: var(--muted-2);
        }

        .lc-empty h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--ink-3);
            margin-bottom: 6px;
        }

        .lc-empty p {
            font-size: 12.5px;
            line-height: 1.6;
        }

        /* ─────────────────────────────────────────
   MODAL — Add Leave
───────────────────────────────────────── */
        .lc-modal-wrap {
            position: fixed;
            inset: 0;
            z-index: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, .5);
            backdrop-filter: blur(6px);
        }

        .lc-modal {
            background: var(--surface);
            border-radius: var(--radius-xl);
            width: 100%;
            max-width: 500px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            animation: modalIn .3s var(--ease-out);
        }

        @keyframes modalIn {
            from {
                transform: translateY(24px) scale(.97);
                opacity: 0;
            }

            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        .lc-modal-head {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1e40af 100%);
            padding: 22px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .lc-modal-head::after {
            content: '';
            position: absolute;
            top: -30px;
            right: -30px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .05);
            pointer-events: none;
        }

        .lc-modal-title {
            font-family: 'Syne', sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: #fff;
        }

        .lc-modal-sub {
            font-size: 12px;
            color: rgba(255, 255, 255, .6);
            margin-top: 2px;
        }

        .lc-modal-close {
            width: 34px;
            height: 34px;
            border: none;
            background: rgba(255, 255, 255, .15);
            color: #fff;
            border-radius: 50%;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: background .15s;
            position: relative;
            z-index: 1;
        }

        .lc-modal-close:hover {
            background: rgba(255, 255, 255, .28);
        }

        .lc-modal-body {
            padding: 26px 28px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .lc-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .lc-fg {        
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .lc-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
        }

        .lc-ctrl {
            padding: 11px 15px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 14px;
            color: var(--ink);
            background: var(--surface);
            outline: none;
            width: 100%;
            transition: border-color .2s, box-shadow .2s;
        }

        .lc-ctrl:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .1);
        }

        .lc-ctrl::placeholder {
            color: var(--muted-2);
        }

        .lc-error-msg {
            font-size: 11.5px;
            color: var(--rose);
            margin-top: 3px;
            font-weight: 500;
        }

        .lc-modal-foot {
            padding: 18px 28px;
            border-top: 1px solid var(--border-2);
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            background: var(--bg);
        }

        .lc-btn-cancel {
            padding: 11px 24px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--surface);
            color: var(--ink-3);
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
        }

        .lc-btn-cancel:hover {
            background: var(--border-2);
        }

        .lc-btn-save {
            padding: 11px 28px;
            border: none;
            border-radius: var(--radius-sm);
            background: var(--primary);
            color: #fff;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all .18s;
            box-shadow: 0 3px 12px rgba(37, 99, 235, .3);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .lc-btn-save:hover {
            background: var(--primary-dk);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, .4);
        }

        .lc-btn-save:disabled {
            opacity: .7;
            transform: none;
            cursor: wait;
        }

        /* ─────────────────────────────────────────
   CONFIRM DIALOG
───────────────────────────────────────── */
        .lc-confirm-wrap {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, .55);
            backdrop-filter: blur(6px);
        }

        .lc-confirm {
            background: var(--surface);
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 380px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            animation: modalIn .25s var(--ease-out);
        }

        .lc-confirm-head {
            padding: 24px 26px 0;
            text-align: center;
        }

        .lc-confirm-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #fff1f2;
            margin: 0 auto 14px;
            display: grid;
            place-items: center;
            color: var(--rose);
        }

        .lc-confirm-title {
            font-family: 'Syne', sans-serif;
            font-size: 18px;
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 8px;
        }

        .lc-confirm-body {
            padding: 12px 26px 24px;
        }

        .lc-confirm-desc {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.7;
            text-align: center;
        }

        .lc-confirm-meta {
            background: var(--border-2);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            margin-top: 10px;
            text-align: center;
        }

        .lc-confirm-meta strong {
            font-size: 13px;
            color: var(--ink-2);
        }

        .lc-confirm-meta span {
            font-size: 11.5px;
            color: var(--muted);
            display: block;
            margin-top: 2px;
        }

        .lc-confirm-foot {
            display: flex;
            gap: 10px;
            padding: 16px 26px;
            border-top: 1px solid var(--border-2);
            background: var(--bg);
        }

        .lc-confirm-cancel {
            flex: 1;
            padding: 11px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--surface);
            color: var(--ink-3);
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
            text-align: center;
        }

        .lc-confirm-cancel:hover {
            background: var(--border-2);
        }

        .lc-confirm-delete {
            flex: 1;
            padding: 11px;
            border: none;
            border-radius: var(--radius-sm);
            background: var(--rose);
            color: #fff;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all .18s;
            box-shadow: 0 3px 12px rgba(225, 29, 72, .3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }

        .lc-confirm-delete:hover {
            background: #be123c;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(225, 29, 72, .4);
        }

        /* Loading overlay */
        .lc-loading-bar {
            height: 3px;
            background: var(--border-2);
            overflow: hidden;
        }

        .lc-loading-bar::after {
            content: '';
            display: block;
            height: 100%;
            background: linear-gradient(90deg, var(--primary), #60a5fa, var(--primary));
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        /* Empty calendar placeholder */
        .lc-cal-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            gap: 12px;
            color: var(--muted);
        }

        .lc-cal-placeholder-icon {
            opacity: .2;
        }

        .lc-cal-placeholder h3 {
            font-size: 15px;
            font-weight: 600;
            color: var(--ink-3);
        }

        .lc-cal-placeholder p {
            font-size: 13px;
            text-align: center;
        }

        .lc-day.is-fully-booked {
            background: #fff5f5;
            cursor: not-allowed;
        }

        .lc-day.is-fully-booked .lc-day-add-hint {
            display: none;
        }
    </style>



    {{-- ═════════════════════════════════════════
     TOP BAR
═════════════════════════════════════════ --}}
    <div class="lc-topbar">


        <div class="lc-search-wrap">
            <input
                type="text"
                class="lc-search-input"
                placeholder="Search employee name or code…"
                wire:model.live.debounce.300ms="search"
                autocomplete="off" />
            <svg class="lc-search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8" />
                <path d="M21 21l-4.35-4.35" />
            </svg>

            @if(count($employees) > 0)
            <div class="lc-search-drop">
                @foreach($employees as $emp)
                @php $name = is_array($emp) ? $emp['emp_full_name'] : $emp->emp_full_name; $code = is_array($emp) ? $emp['emp_code'] : $emp->emp_code; $id = is_array($emp) ? $emp['emp_id'] : $emp->emp_id; @endphp
                <div class="lc-drop-item" wire:click="selectEmployee({{ $id }})">
                    <div class="lc-drop-avatar">{{ strtoupper(substr($name,0,1)) }}</div>
                    <div>
                        <div class="lc-drop-name">{{ $name }}</div>
                        <div class="lc-drop-code">{{ $code }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ═════════════════════════════════════════
     PROFILE CARD
═════════════════════════════════════════ --}}
    @if($employee)
    @php

    $empName = is_array($employee) ? $employee['emp_full_name'] : $employee->emp_full_name;
    $empCode = is_array($employee) ? $employee['emp_code'] : $employee->emp_code;
    $empDes = is_array($employee) ? ($employee['fh_designation']['emp_dg_id'] ?? '—') : ($employee->fh_designation?->dg_name ?? '—');
    $empDept = is_array($employee) ? ($employee['fh_department']['emp_d_id'] ?? '—') : ($employee->fh_department?->d_name ?? '—');
    $empMob = is_array($employee) ? $employee['emp_phone'] : $employee->emp_phone;
    $empEmail = is_array($employee) ? $employee['emp_email'] : $employee->emp_email;
    $totalDays = collect($tableRows)->sum('days');
    $approvedCnt = collect($tableRows)->filter(fn($r) => strtolower($r['status']??'') === 'approved')->count();
    $autoApprovedCnt = collect($tableRows)->filter(fn($r) => strtolower($r['status']??'') === 'auto approved')->count();


    $photoPath = $employee->emp_profile_photo;
    $defaultPhoto = asset('assets/imgs/user.png');
    @endphp

    <div class="lc-profile">
        <div><img class="lc-prof-av"
                src="{{ $photoPath && \Storage::disk('public')->exists($photoPath) 
        ? Storage::url($photoPath) 
        : $defaultPhoto }}"
                alt="{{ $employee->name ?? 'User' }} Profile Photo"></div>
        <div class="lc-prof-info">
            <div class="lc-prof-name">{{$empName. ' - '. $empCode}}</div>
            <div class="lc-prof-meta">
                <div class="lc-prof-tag">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2" />
                        <path d="M16 3H8a2 2 0 0 0-2 2v2h12V5a2 2 0 0 0-2-2z" />
                    </svg>
                    {{ $empDes }}
                </div>
                <div class="lc-prof-tag">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2" />
                        <path d="M9 9h6M9 13h6M9 17h6" />
                    </svg>
                    {{ $empDept }}
                </div>
                <div class="lc-prof-tag">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 
           19.79 19.79 0 0 1-8.63-3.07 
           19.5 19.5 0 0 1-6-6 
           19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3 
           a2 2 0 0 1 2 1.72 
           12.84 12.84 0 0 0 .7 2.81 
           2 2 0 0 1-.45 2.11L8.09 9.91 
           a16 16 0 0 0 6 6l1.27-1.27 
           a2 2 0 0 1 2.11-.45 
           12.84 12.84 0 0 0 2.81.7 
           2 2 0 0 1 1.72 2z" />
                    </svg>
                    {{ $empMob }}
                </div>

                <div class="lc-prof-tag">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M4 4h16v16H4z" />
                        <path d="M4 4l8 8 8-8" />
                    </svg>
                    {{ $empEmail }}
                </div>
            </div>
        </div>
        <div class="lc-prof-stats">
            <div class="lc-prof-stat">
                <div class="lc-prof-stat-val">{{ $totalDays }}</div>
                <div class="lc-prof-stat-lbl">Total Days</div>
            </div>
            <div class="lc-prof-stat">
                <div class="lc-prof-stat-val" style="color:#6ee7b7">{{ $approvedCnt }}</div>
                <div class="lc-prof-stat-lbl">Approved</div>
            </div>
            <div class="lc-prof-stat">
                <div class="lc-prof-stat-val" style="color:#fcd34d">{{ $autoApprovedCnt }}</div>
                <div class="lc-prof-stat-lbl">Auto Approved</div>
            </div>

        </div>
    </div>

    {{-- STATS STRIP --}}
    <div class="lc-stats-strip">
        @foreach($leaveBalanceRaw as $bal)
        <div class="lc-stat-card">
            <div class="lc-stat-icon blue">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" />
                    <path d="M16 2v4M8 2v4M3 10h18" />
                </svg>
            </div>
            <div>
                <div class="lc-stat-val">{{ number_format($bal['balance'],1) }}</div>
                <div class="lc-stat-lbl">{{ $bal['category_name'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- SELECTED LEAVE DETAIL BAR --}}
    @if($selectedLeave)
    <div class="lc-detail-bar">
        <!-- <div class="lc-detail-bar-head">
            <div class="lc-detail-bar-title">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 8v4l3 3" />
                </svg>
                Selected Leave Details
            </div>
            <div class="lc-status-pill {{ match(strtolower($selectedLeave['status']??'')) { 'approved'=>'st-approved','pending'=>'st-pending','rejected'=>'st-rejected',default=>'st-approved' } }}">
                {{ $selectedLeave['status'] ?? '—' }}
            </div>
        </div> -->
        <!-- <div class="lc-detail-grid">
            <div class="lc-detail-cell"><label>Category</label><span>{{ $selectedLeave['category'] ?? '—' }}</span></div>
            <div class="lc-detail-cell"><label>From</label><span>{{ $selectedLeave['from'] ?? '—' }}</span></div>
            <div class="lc-detail-cell"><label>To</label><span>{{ $selectedLeave['to'] ?? '—' }}</span></div>
            <div class="lc-detail-cell"><label>Days</label><span>{{ $selectedLeave['days'] ?? '—' }}</span></div>
            <div class="lc-detail-cell"><label>Applied On</label><span>{{ $selectedLeave['applied_on'] ?? '—' }}</span></div>
            <div class="lc-detail-cell"><label>Remark</label><span>{{ $selectedLeave['reason'] ?: '—' }}</span></div>
        </div> -->
    </div>
    @endif

    @endif {{-- end if employee --}}

    {{-- ═════════════════════════════════════════
     MAIN GRID
═════════════════════════════════════════ --}}
    <div class="lc-main-grid">

        {{-- ════════════ CALENDAR ════════════ --}}
        <div class="lc-cal">
            {{-- Loading bar --}}
            <div wire:loading wire:target="prevMonth,nextMonth,goToToday,saveLeave,deleteLeave">
                <div class="lc-loading-bar"></div>
            </div>

            {{-- Header --}}
            <div class="lc-cal-head">
                <div class="lc-cal-nav-group">
                    <button class="lc-nav-btn" wire:click="prevMonth" title="Previous month">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M15 18l-6-6 6-6" />
                        </svg>
                    </button>
                    <button class="lc-today-btn" wire:click="goToToday">Today</button>
                    <button class="lc-nav-btn" wire:click="nextMonth" title="Next month">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M9 18l6-6-6-6" />
                        </svg>
                    </button>
                </div>

                <div class="lc-cal-month-label">
                    {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('F Y') }}
                </div>

                @if($selectedEmployeeId)
                <button class="lc-add-btn" wire:click="openAddLeave()">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    Add Leave
                </button>
                @else
                <div style="width:110px"></div>
                @endif
            </div>

            {{-- Day-of-week row --}}
            <div class="lc-dow-row">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                <div class="lc-dow">{{ $d }}</div>
                @endforeach
            </div>

            {{-- Days --}}
            @if(count($calendarDays) > 0)
            <div class="lc-days-grid">
                @foreach($calendarDays as $week)
                @foreach($week as $day)
                @if(is_null($day))
                <div class="lc-day is-empty"></div>
                @else
                @php
                $hasLeave = count($day['leaves']) > 0;
                $atd = $day['attendance'] ?? null;
                $atdCode = $atd ? strtolower($atd['status_code'] ?? '') : null;

                // Determine if this day is fully blocked for new leaves
                $dayFullyBooked = false;
                if ($hasLeave) {
                $hasFullDayLeave = collect($day['leaves'])->contains(function($leaf) {
                // abbr won't tell us the day count — we rely on the PHP guard in openAddLeave
                // but we can use a flag if you store it (see step 3 below)
                return $leaf['is_full_day'] ?? false;
                });
                $halfDayCount = collect($day['leaves'])->filter(fn($l) => ($l['is_full_day'] ?? true) === false)->count();
                $dayFullyBooked = $hasFullDayLeave || $halfDayCount >= 2;
                }

                $atdClass = match($atdCode) {
                'p', 'pre' => 'atd-present',
                'abs' => 'atd-absent',
                'h', 'hol' => 'atd-holiday',
                'wo', 'wof' => 'atd-weekoff',
                'hd', 'half'=> 'atd-halfday',
                'mp', 'mis' => 'atd-missed',
                'l', 'leave'=> 'atd-leave',
                default => 'atd-unknown',
                };
                @endphp

                <div
                    class="lc-day {{ $day['is_today'] ? 'is-today' : '' }} {{ $hasLeave ? 'has-leave' : '' }} {{ $atd ? 'has-atd' : '' }} {{ $dayFullyBooked ? 'is-fully-booked' : '' }}"
                    wire:click="openAddLeave('{{ $day['date'] }}')">


                    {{-- Day number --}}
                    <div class="lc-day-num">{{ $day['day'] }}</div>

                    {{-- Attendance status row --}}
                    @if($atd)
                    <div class="lc-atd-row {{ $atdClass }}"
                        title="{{ $atd['status'] }}{{ $atd['checkInTime'] ? ' · In: '.$atd['checkInTime'] : '' }}{{ $atd['checkOutTime'] ? ' · Out: '.$atd['checkOutTime'] : '' }}">

                        <span class="lc-atd-dot"></span>
                        <span class="lc-atd-code">{{ strtoupper($atd['status_code']) }}</span>

                        @if($atd['checkInTime'])
                        <span class="lc-atd-time">{{ $atd['checkInTime'] }}</span>
                        @endif

                        @if($atd['checkInTime'])
                        <span class="lc-atd-time">{{ ' - '. $atd['checkOutTime'] }}</span>
                        @endif

                        {{-- Late / early-exit flags --}}
                        @if($atd['late'])
                        <span class="lc-atd-flag lc-flag-late" title="Late arrival">L</span>
                        @endif
                        @if($atd['earlyExit'])
                        <span class="lc-atd-flag lc-flag-early" title="Early exit">E</span>
                        @endif
                    </div>
                    @endif

                    {{-- Add hint --}}
                    @if($selectedEmployeeId)
                    <div class="lc-day-add-hint">
                        <svg width="8" height="8" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                    </div>
                    @endif

                    {{-- Leave badges --}}
                    <div class="lc-day-leaves">
                        @foreach(array_slice($day['leaves'], 0, 2) as $leaf)
                        @php
                        $t = strtolower($leaf['abbr'] ?? 'def');
                        $cls = in_array($t, ['cl','sl','el','ml','pl','mrl','bl','upl','co']) ? $t : 'def';
                        $cat = $leaf['cat'] ?? '';
                        @endphp
                        <div
                            class="lc-badge badge-{{ $cls }}"
                            wire:click.stop="selectLeaveFromCalendar({{ $leaf['id'] }})"
                            title="{{ $cat }} – {{ $leaf['status'] ?? '' }}">
                            <span>{{ $leaf['abbr'] }}</span>
                            <span
                                class="lc-badge-del"
                                @click.stop="openConfirm({{ $leaf['id'] }}, '{{ addslashes($cat) }}', '{{ $day['date'] }}')"
                                title="Delete leave">
                                <svg width="8" height="8" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path d="M18 6L6 18M6 6l12 12" />
                                </svg>
                            </span>
                        </div>
                        @endforeach
                        @if(count($day['leaves']) > 2)
                        <span class="lc-badge-more">+{{ count($day['leaves']) - 2 }} more</span>
                        @endif
                    </div>
                </div>
                @endif
                @endforeach
                @endforeach
            </div>
            @else
            {{-- No employee selected — still show empty grid --}}
            <div class="lc-days-grid">
                @for($i = 0; $i < 35; $i++)
                    <div class="lc-day is-empty" style="min-height:90px">
            </div>
            @endfor
        </div>
        <div class="lc-cal-placeholder">
            <div class="lc-cal-placeholder-icon">
                <svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" />
                    <path d="M16 2v4M8 2v4M3 10h18M8 14h.01M12 14h.01M16 14h.01" />
                </svg>
            </div>
            <h3>No Employee Selected</h3>
            <p>Search and select an employee above<br>to view their leave calendar.</p>
        </div>
        @endif

        
    </div>{{-- end .lc-cal --}}

    {{-- ════════════ LEAVE HISTORY PANEL ════════════ --}}
    <div class="lc-panel">
        <div class="lc-panel-head">
            <div class="lc-panel-title">
                Leave History
                @if(count($tableRows) > 0)
                <span class="lc-panel-badge">{{ count($tableRows) }}</span>
                @endif
            </div>
            @if($selectedEmployeeId)
            <button class="lc-add-btn" wire:click="openAddLeave()">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                New Leave
            </button>
            @endif
        </div>

        <div class="lc-panel-body">
            @if(count($tableRows) > 0)
            @foreach($tableRows as $i => $row)
            @php
            $isActive = $selectedLeave && ($selectedLeave['lvr_id'] ?? null) == ($row['lvr_id'] ?? null);
            $stCls = match(strtolower($row['status']??'')) {
            'approved' => 'st-approved', 'pending' => 'st-pending', 'rejected' => 'st-rejected', default => 'st-approved'
            };
            $cat = $row['category'] ?? '—';
            $dates = ($row['from'] ?? '') . ' → ' . ($row['to'] ?? '');
            @endphp
            <div class="lc-leave-card {{ $isActive ? 'lc-active' : '' }}" wire:click="selectLeave({{ $i }})">
                <div class="lc-card-top">
                    <div class="lc-card-cat">{{ $cat }}</div>
                    <div class="lc-card-actions">
                        <span class="lc-status-pill {{ $stCls }}">{{ $row['status'] ?? '—' }}</span>
                        <button
                            class="lc-del-btn-panel"
                            @click.stop="openConfirm({{ $row['lvr_id'] }}, '{{ addslashes($cat) }}', '{{ $dates }}')"
                            title="Delete leave">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="lc-card-dates">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <path d="M16 2v4M8 2v4M3 10h18" />
                    </svg>
                    {{ $row['from'] ?? '—' }}
                    <span class="lc-card-divider">→</span>
                    {{ $row['to'] ?? '—' }}
                </div>
                <div class="lc-card-foot">
                    <div class="lc-card-days"><strong>{{ $row['days'] ?? 0 }}</strong> day(s)</div>
                    <div class="lc-card-applied">Applied: {{ $row['applied_on'] ?? '—' }}</div>
                </div>
                @if(!empty($row['reason']))
                <div class="lc-card-reason">"{{ Str::limit($row['reason'], 80) }}"</div>
                @endif
            </div>
            @endforeach
            @elseif($selectedEmployeeId)
            <div class="lc-empty">
                <div class="lc-empty-icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <path d="M8 2v4M16 2v4M3 10h18M8 14h.01M12 14h.01M16 14h.01" />
                    </svg>
                </div>
                <h4>No Leaves This Month</h4>
                <p>Click on any date in the calendar<br>or press <strong>New Leave</strong> to get started.</p>
            </div>
            @else
            <div class="lc-empty">
                <div class="lc-empty-icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <h4>No Employee Selected</h4>
                <p>Search for an employee above to view their leave history.</p>
            </div>
            @endif
        </div>
    </div>

</div>{{-- end .lc-main-grid --}}


{{-- ═════════════════════════════════════════
     ADD LEAVE MODAL
═════════════════════════════════════════ --}}
@if($showModal)
<div class="lc-modal-wrap" wire:click.self="closeModal">
    <div class="lc-modal">
        <div class="lc-modal-head">
            <div>
                <div class="lc-modal-title">Apply Leave</div>
                <div class="lc-modal-sub">
                    @if($lvr_start_date) {{ \Carbon\Carbon::parse($lvr_start_date)->format('d M, Y') }} @else Select dates below @endif
                </div>
            </div>
            <button class="lc-modal-close" wire:click="closeModal">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M18 6L6 18M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="lc-modal-body">
            {{-- Leave Category --}}
            <div class="lc-fg">
                <label class="lc-label">Leave Category *</label>
                <select class="lc-ctrl" wire:model="lvr_cat_type_id">
                    <option value="">— Select Category —</option>
                    @foreach($leaveCategories as $cat)
                    <option value="{{ $cat['id'] }}" {{ $cat['disabled'] ? 'disabled' : '' }}>
                        {{ $cat['name'] }}{{ $cat['balance'] !== null ? '  ('.$cat['balance'].' days left)' : '' }}
                    </option>
                    @endforeach
                </select>
                @error('lvr_cat_type_id') <span class="lc-error-msg">{{ $message }}</span> @enderror
            </div>

            {{-- Leave Day Type --}}
            <div class="lc-fg">
                <label class="lc-label">Leave Day Type *</label>
                <select class="lc-ctrl" wire:model.live="lvr_leave_day_type_id">
                    <option value="">— Select Type —</option>
                    @foreach($leaveTypes as $lt)
                    <option value="{{ $lt['m_id'] ?? $lt->m_id }}">{{ $lt['m_name'] ?? $lt->m_name }}</option>
                    @endforeach
                </select>
                @error('lvr_leave_day_type_id') <span class="lc-error-msg">{{ $message }}</span> @enderror
            </div>

            @if($lvr_leave_day_type_id == 202)
            {{-- Day Segment --}}
            <div class="lc-fg">
                <label class="lc-label">Day Segment</label>
                <select class="lc-ctrl" wire:model="lvr_day_segment_id">
                    @foreach($daySegments as $ds)
                    <option value="{{ $ds['m_id'] ?? $ds->m_id }}">{{ $ds['m_name'] ?? $ds->m_name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Dates --}}
            <div class="lc-form-row">
                <div class="lc-fg">
                    <label class="lc-label">Start Date *</label>
                    <input type="date" class="lc-ctrl" wire:model="lvr_start_date" />
                    @error('date') <span class="lc-error-msg">{{ $message }}</span> @enderror
                </div>
                <div class="lc-fg">
                    <label class="lc-label">End Date *</label>
                    <input type="date" class="lc-ctrl" wire:model="lvr_end_date" />
                </div>
            </div>

            {{-- Reason --}}
            <div class="lc-fg">
                <label class="lc-label">Reason / Remark</label>
                <textarea class="lc-ctrl" wire:model="lvr_reason" rows="3" placeholder="Enter reason for leave…" style="resize:vertical;min-height:80px"></textarea>
            </div>
        </div>

        <div class="lc-modal-foot">
            <button class="lc-btn-cancel" wire:click="closeModal">Cancel</button>
            <button class="lc-btn-save" wire:click="saveLeave" wire:loading.attr="disabled" wire:target="saveLeave">
                <span wire:loading.remove wire:target="saveLeave">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M20 6L9 17l-5-5" />
                    </svg>
                    Save Leave
                </span>
                <span wire:loading wire:target="saveLeave">Saving…</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- ═════════════════════════════════════════
     CONFIRM DELETE DIALOG  (Alpine-driven)
═════════════════════════════════════════ --}}
<div class="lc-confirm-wrap" x-show="showConfirm" x-cloak style="display:none">
    <div class="lc-confirm" @click.outside="closeConfirm()">
        <div class="lc-confirm-head">
            <div class="lc-confirm-icon">
                <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6" />
                </svg>
            </div>
            <div class="lc-confirm-title">Delete Leave?</div>
        </div>
        <div class="lc-confirm-body">
            <div class="lc-confirm-desc">
                This action cannot be undone. The leave record will be permanently removed.
            </div>
            <div class="lc-confirm-meta">
                <strong x-text="confirmCat"></strong>
                <span x-text="confirmDates"></span>
            </div>
        </div>
        <div class="lc-confirm-foot">
            <button class="lc-confirm-cancel" @click="closeConfirm()">Keep Leave</button>
            <button
                class="lc-confirm-delete"
                @click="$wire.deleteLeave(confirmId); closeConfirm();"
                wire:loading.attr="disabled">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" />
                </svg>
                Yes, Delete
            </button>
        </div>
    </div>
</div>


<script>
    document.addEventListener('livewire:initialized', function() {
        Livewire.on('show-alert', (payload) => {
            // Livewire 3 wraps dispatch data as first array element
            const data = Array.isArray(payload) ? payload[0] : payload;

            Swal.fire({
                position: 'top-end',
                icon: data.type || 'success',
                title: data.message || 'Something went wrong!',
                toast: true,
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                customClass: {
                    popup: 'swal2-toast-custom'
                },
                // ✅ This fixes the freeze on hover issue
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        });
    });
</script>
</div>{{-- end .lc-root --}}