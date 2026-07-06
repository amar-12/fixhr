@extends('admin.layout.master')
@section('title')
    Notification Settings
@endsection

@section('css')
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg: #0f0f14;
      --surface: #17171f;
      --card: #1e1e28;
      --border: #2a2a38;
      --sms: #767a7a;
      --sms-dim: rgba(0,212,255,0.1);
      --wa: #25d366;
      --wa-dim: rgba(37,211,102,0.1);
      --text: #f0f0f8;
      --muted: #7c7c9a;
      --radius: 16px;
    }

    /* ── LIGHT THEME ── */
    body.light-mode {
      --bg: #f4f6fb;
      --surface: #ffffff;
      --card: #ffffff;
      --border: #e0e4ef;
      --text: #1a1a2e;
      --muted: #7a7a9a;
      --sms-dim: rgba(0,150,255,0.08);
      --wa-dim: rgba(37,211,102,0.08);
    }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      transition: background 0.3s, color 0.3s;
      background-image:
        radial-gradient(ellipse 80% 50% at 20% 10%, rgba(0,212,255,0.05) 0%, transparent 60%),
        radial-gradient(ellipse 60% 40% at 80% 90%, rgba(37,211,102,0.04) 0%, transparent 60%);
    }

    .wrapper {
      width: 100%;
      max-width: 780px;
      animation: fadeUp 0.6s ease both;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ── THEME TOGGLE BUTTON ── */
    .theme-toggle {
      display: flex;
      align-items: center;
      gap: 8px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 50px;
      padding: 6px 14px 6px 8px;
      cursor: pointer;
      font-family: 'Syne', sans-serif;
      font-size: 12px;
      font-weight: 600;
      color: var(--muted);
      transition: all 0.25s;
      margin-bottom: 28px;
      width: fit-content;
    }
    .theme-toggle:hover { color: var(--text); border-color: var(--muted); }
    .theme-toggle .theme-icon {
      width: 28px; height: 28px;
      border-radius: 50%;
      background: var(--border);
      display: flex; align-items: center; justify-content: center;
      font-size: 14px;
      transition: background 0.3s;
    }

    /* Header */
    .header { margin-bottom: 28px; }
    .header-label {
      font-size: 11px;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 8px;
    }
    .header h1 {
      font-family: 'Syne', sans-serif;
      font-size: 32px;
      font-weight: 800;
      color: var(--text);
      line-height: 1.1;
    }
    .header h1 span { color: var(--sms); }
    .header p {
      margin-top: 10px;
      font-size: 14px;
      color: var(--muted);
      font-weight: 300;
      max-width: 460px;
    }

    /* Tabs */
    .tabs {
      display: flex;
      gap: 8px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 6px;
      margin-bottom: 28px;
      width: fit-content;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .tab-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px 22px;
      border-radius: 10px;
      border: 1px solid transparent;
      background: transparent;
      color: var(--muted);
      font-family: 'Syne', sans-serif;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.25s ease;
    }
    .tab-btn .icon { font-size: 17px; }
    .tab-btn.active[data-tab="sms"] {
      background: var(--sms-dim);
      color: var(--sms);
      border-color: rgba(0,212,255,0.25);
    }
    .tab-btn.active[data-tab="whatsapp"] {
      background: var(--wa-dim);
      color: var(--wa);
      border-color: rgba(37,211,102,0.25);
    }
    .tab-btn:not(.active):hover { color: var(--text); background: rgba(128,128,128,0.07); }

    /* Panel */
    .panel { display: none; animation: fadeUp 0.35s ease both; }
    .panel.active { display: block; }

    /* Section title */
    .section-title {
      font-family: 'Syne', sans-serif;
      font-size: 12px;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 14px;
      padding-left: 4px;
    }

    /* Notification cards */
    .notif-list { display: flex; flex-direction: column; gap: 14px; margin-bottom: 32px; }

    .notif-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 20px 24px;
      display: flex;
      align-items: center;
      gap: 20px;
      transition: border-color 0.2s, box-shadow 0.2s;
      box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .notif-card:hover {
      border-color: rgba(128,128,200,0.25);
      box-shadow: 0 4px 16px rgba(0,0,0,0.07);
    }

    .notif-icon {
      width: 44px; height: 44px;
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 20px;
      flex-shrink: 0;
    }
    .sms  .notif-icon { background: var(--sms-dim); }
    .wa   .notif-icon { background: var(--wa-dim); }

    .notif-info { flex: 1; min-width: 0; }
    .notif-name {
      font-family: 'Syne', sans-serif;
      font-size: 15px;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 4px;
    }
    .notif-desc {
      font-size: 13px;
      color: var(--muted);
      font-weight: 300;
      line-height: 1.5;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    /* Toggle — FIXED: using unique IDs per panel so no conflict */
    .toggle { position: relative; flex-shrink: 0; }
    .toggle input { display: none; }
    .toggle label {
      display: block;
      width: 48px; height: 26px;
      border-radius: 50px;
      background: var(--border);
      cursor: pointer;
      transition: background 0.3s;
      position: relative;
    }
    .toggle label::after {
      content: '';
      position: absolute;
      top: 3px; left: 3px;
      width: 20px; height: 20px;
      border-radius: 50%;
      background: #fff;
      transition: transform 0.3s;
      box-shadow: 0 1px 4px rgba(0,0,0,0.4);
    }
    .sms-panel .toggle input:checked + label { background: var(--sms); }
    .wa-panel  .toggle input:checked + label { background: var(--wa); }
    .toggle input:checked + label::after { transform: translateX(22px); }

    /* Preview box */
    .preview-box {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 20px 24px;
      margin-top: 8px;
    }
    .preview-box h3 {
      font-family: 'Syne', sans-serif;
      font-size: 12px;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 14px;
    }
    .msg-bubble {
      background: var(--card);
      border-radius: 12px 12px 12px 0;
      padding: 14px 18px;
      font-size: 13px;
      color: var(--text);
      line-height: 1.6;
      font-weight: 300;
      border-left: 3px solid;
      margin-bottom: 10px;
      border: 1px solid var(--border);
      border-left: 3px solid;
    }
    .sms-panel .msg-bubble { border-left-color: var(--sms); }
    .wa-panel  .msg-bubble { border-left-color: var(--wa); }

    .msg-bubble .tag {
      display: inline-block;
      background: rgba(128,128,255,0.08);
      border-radius: 4px;
      padding: 1px 6px;
      font-size: 11px;
      font-family: monospace;
    }
    .sms-panel .msg-bubble .tag { color: var(--sms); }
    .wa-panel  .msg-bubble .tag { color: var(--wa); }

    /* Badge */
    .badge {
      font-size: 10px;
      padding: 2px 8px;
      border-radius: 20px;
      font-weight: 600;
      vertical-align: middle;
      margin-left: 4px;
    }
    .badge-premium { background: rgba(255,180,0,0.15); color: #ffb400; border: 1px solid rgba(255,180,0,0.3); }

    @media (max-width: 540px) {
      .notif-desc { display: none; }
      .notif-card { padding: 16px; gap: 14px; }
      .header h1 { font-size: 24px; }
    }
  </style>
@endsection

@section('content')
  <div class="wrapper">
    <!-- Header -->
    <div class="header">
      <h1>Notify &amp; <span>Alert</span></h1>
      <p>Manage how your team gets notified — via SMS or WhatsApp — for every report action.</p>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button class="tab-btn active" data-tab="sms" onclick="switchTab('sms')">
        <span class="icon">💬</span> SMS
        <span class="badge badge-premium">Premium</span>
      </button>
      <button class="tab-btn" data-tab="whatsapp" onclick="switchTab('whatsapp')">
        <span class="icon">🟢</span> WhatsApp
        <span class="badge badge-premium">Premium</span>
      </button>
    </div>

    <!-- SMS Panel -->
    <div class="panel active sms-panel" id="panel-sms">
      <div class="section-title">SMS Notification Rules</div>
      <div class="notif-list sms">

        <!-- Parent -->
        <div class="notif-card">
          <div class="notif-icon">📤</div>
          <div class="notif-info">
            <div class="notif-name">Super Admin Notification</div>
            <div class="notif-desc">Enable for all active employees.</div>
          </div>
          <div class="toggle">
            <input type="checkbox"
              id="sms-toggle-1"
              class="notification-toggle"
              data-type="sms"
              data-field="is_notification"
              {{ $user?->fh_business?->is_notification == 1 ? 'checked' : '' }}>
            <label for="sms-toggle-1"></label>
          </div>
        </div>

        <!-- Child -->
        <div class="notif-card sms-child">
          <div class="notif-icon">✅</div>
          <div class="notif-info">
            <div class="notif-name">Enable Notification</div>
            <div class="notif-desc">Enable as a employee.</div>
          </div>
          <div class="toggle">
            <input type="checkbox"
              id="sms-toggle-2"
              class="notification-toggle"
              data-type="sms"
              data-field="emp_is_notification_enabled"
              {{ $user->emp_is_notification_enabled == 1 ? 'checked' : '' }}>
            <label for="sms-toggle-2"></label>
          </div>
        </div>

      </div>
    </div>

    <!-- WhatsApp Panel -->
    <div class="panel wa-panel" id="panel-whatsapp">
      <div class="section-title">WhatsApp Notification Rules</div>
      <div class="notif-list wa">

        <!-- Parent -->
        <div class="notif-card">
          <div class="notif-icon">📤</div>
          <div class="notif-info">
            <div class="notif-name">Super Admin Notification</div>
            <div class="notif-desc">Enable for all active employees.</div>
          </div>
          <div class="toggle">
            <input type="checkbox"
              id="wa-toggle-1"
              class="notification-toggle"
              data-type="whatsapp"
              data-field="is_whatsapp"
              {{ $user?->fh_business?->is_whatsapp == 1 ? 'checked' : '' }}>
            <label for="wa-toggle-1"></label>
          </div>
        </div>

        <!-- Child -->
        <div class="notif-card whatsapp-child">
          <div class="notif-icon">✅</div>
          <div class="notif-info">
            <div class="notif-name">Enable Notification</div>
            <div class="notif-desc">Enable as a employee.</div>
          </div>
          <div class="toggle">
            <input type="checkbox"
              id="wa-toggle-2"
              class="notification-toggle"
              data-type="whatsapp"
              data-field="emp_is_whatsapp_enabled"
              {{ $user->emp_is_whatsapp_enabled == 1 ? 'checked' : '' }}>
            <label for="wa-toggle-2"></label>
          </div>
        </div>

      </div>
    </div>
@endsection

@section('script')
  <script>

    document.addEventListener("DOMContentLoaded", function(){

      function switchTab(tab){
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));

        document.querySelector(`.tab-btn[data-tab="${tab}"]`).classList.add('active');
        document.getElementById(`panel-${tab}`).classList.add('active');
      }

      window.switchTab = switchTab;


      document.querySelectorAll('.notification-toggle').forEach(toggle => {

        toggle.addEventListener('change', function(){

          let prev = this.checked;
          let enabled = this.checked ? 1 : 0;
          let type = this.dataset.type;

          fetch("{{ route('settings.notifications.toggle') }}",{
            method:'POST',
            headers:{
              'Content-Type':'application/x-www-form-urlencoded',
              'X-CSRF-TOKEN':'{{ csrf_token() }}'
            },
            body:'enabled='+enabled+'&field='+this.dataset.field
          })
          .catch(()=>{
            this.checked = !prev;
          });


          // show hide child
          if(this.id.includes('toggle-1')){

            document.querySelectorAll(`.${type}-child`).forEach(el=>{
              el.style.display = this.checked ? 'flex' : 'none';
            });

          }

        });

      });


      // page load check
      ['sms','whatsapp'].forEach(type => {

        let parent = document.getElementById(`${type}-toggle-1`);

        if(parent && !parent.checked){

          document.querySelectorAll(`.${type}-child`).forEach(el=>{
            el.style.display = 'none';
          });

        }

      });

    });

  </script>
@endsection
