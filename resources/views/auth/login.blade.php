@extends('layouts.base')
@section('title', 'Sign in to AANI Market')
@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&family=DM+Mono:wght@400;500&display=swap');

    :root {
        --bg:        #F5F4F0;
        --surface:   #FFFFFF;
        --border:    #E4E2DC;
        --text:      #1A1916;
        --muted:     #7A7871;
        --accent:    #1D6F42;
        --accent-lt: #EAF4EE;
        --accent-dk: #155232;
        --danger:    #DC2626;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--bg);
        color: var(--text);
        font-size: 14px;
        line-height: 1.6;
    }

    a { color: var(--accent); text-decoration: none; }
    a:hover { text-decoration: underline; }

    /* ── Full-height shell ── */
    .auth-shell {
        min-height: calc(100vh - 60px);
        display: flex;
        align-items: stretch;
        justify-content: center;
        padding: 20px;
    }

    /* ── Split card ── */
    .auth-card {
        display: grid;
        grid-template-columns: 1fr 1fr;
        width: 100%;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: 0 2px 4px rgba(0,0,0,.04), 0 12px 40px rgba(0,0,0,.08);
        overflow: hidden;
        align-self: stretch;
    }

    /* ── Left brand panel ── */
    .auth-panel {
        background: var(--accent-dk);
        padding: 52px 44px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    /* Dot grid */
    .auth-panel::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255,255,255,.12) 1px, transparent 1px);
        background-size: 24px 24px;
        pointer-events: none;
    }

    /* Glow blob */
    .auth-panel::after {
        content: '';
        position: absolute;
        width: 280px; height: 280px;
        background: radial-gradient(circle, rgba(255,255,255,.08) 0%, transparent 70%);
        bottom: -60px; right: -60px;
        pointer-events: none;
    }

    .panel-top { position: relative; z-index: 1; }

    .panel-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 48px;
    }
    .panel-brand-icon {
        width: 36px; height: 36px;
        background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
    }
    .panel-brand-name {
        font-size: 15px;
        font-weight: 600;
        color: #fff;
    }

    .panel-title {
        font-size: 28px;
        font-weight: 600;
        color: #fff;
        line-height: 1.25;
        letter-spacing: -.3px;
        margin-bottom: 14px;
    }
    .panel-title em {
        font-style: normal;
        color: #a7f3c1;
    }

    .panel-desc {
        font-size: 13.5px;
        color: rgba(255,255,255,.6);
        line-height: 1.65;
        max-width: 260px;
    }

    .panel-features {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .panel-feature {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        color: rgba(255,255,255,.75);
    }
    .feature-dot {
        width: 24px; height: 24px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
    }

    /* ── Right form panel ── */
    .auth-form-panel {
        padding: 52px 48px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .form-heading { margin-bottom: 28px; }
    .form-heading h1 {
        font-size: 23px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 4px;
    }
    .form-heading p { font-size: 13.5px; color: var(--muted); }

    /* Fields */
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
        margin-bottom: 14px;
    }
    .form-label {
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--muted);
    }
    .input-wrap { position: relative; }
    .form-control {
        width: 100%;
        height: 50px;
        padding: 0 18px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 15px;
        color: var(--text);
        background: var(--surface);
        outline: none;
        transition: border-color .15s, box-shadow .15s;
        display: block;
    }
    .form-control::placeholder { color: #c4c1bb; }
    .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(29,111,66,.1);
    }
    .form-control.is-invalid { border-color: var(--danger); }
    .form-control.is-invalid:focus { box-shadow: 0 0 0 3px rgba(220,38,38,.1); }

    .toggle-pw {
        position: absolute;
        right: 11px; top: 50%;
        transform: translateY(-50%);
        background: none; border: none;
        cursor: pointer;
        color: var(--muted);
        padding: 4px;
        display: flex; align-items: center;
        transition: color .15s;
    }
    .toggle-pw:hover { color: var(--text); }
    .toggle-pw svg { width: 15px; height: 15px; }
    .has-toggle { padding-right: 38px; }

    .invalid-msg {
        font-size: 12px;
        color: var(--danger);
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .invalid-msg::before {
        content: '!';
        display: inline-flex; align-items: center; justify-content: center;
        width: 14px; height: 14px;
        background: var(--danger); color: #fff;
        border-radius: 50%;
        font-size: 10px; font-weight: 700;
        flex-shrink: 0;
    }

    /* Remember */
    .remember-row {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        margin-top: 2px;
    }
    .check-label {
        display: flex; align-items: center; gap: 8px;
        font-size: 13px; color: var(--text);
        cursor: pointer; user-select: none;
    }
    .check-input {
        width: 15px; height: 15px;
        border-radius: 4px;
        accent-color: var(--accent);
        cursor: pointer; flex-shrink: 0;
    }

    /* Submit */
    .btn-submit {
        width: 100%; height: 48px;
        background: var(--accent); color: #fff;
        border: none; border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px; font-weight: 600;
        cursor: pointer;
        transition: background .15s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        margin-bottom: 20px;
    }
    .btn-submit:hover { background: var(--accent-dk); }

    /* Divider */
    .auth-divider {
        display: flex; align-items: center; gap: 10px;
        margin-bottom: 16px;
    }
    .auth-divider::before,
    .auth-divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
    .auth-divider span { font-size: 11.5px; color: var(--muted); font-weight: 500; white-space: nowrap; }

    /* Footer links */
    .auth-footer { display: flex; flex-direction: column; gap: 7px; }
    .auth-footer-item {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 10px 14px;
        display: flex; align-items: center; justify-content: space-between;
        font-size: 13px;
    }
    .auth-footer-item .label { color: var(--muted); }
    .auth-footer-item a { color: var(--accent); font-weight: 600; font-size: 12.5px; }
    .auth-footer-item a:hover { text-decoration: underline; }

    /* ── Responsive ── */
    @media (max-width: 700px) {
        .auth-card    { grid-template-columns: 1fr; }
        .auth-panel   { display: none; }
        .auth-form-panel { padding: 36px 28px; }
    }
    @media (max-width: 420px) {
        .auth-form-panel { padding: 28px 20px; }
    }
</style>

<div class="auth-shell">
    <div class="auth-card">

        {{-- ── Left: Brand Panel ── --}}
        <div class="auth-panel">
            <div class="panel-top">
                <div class="panel-brand">
                    <div class="panel-brand-icon">🌿</div>
                    <span class="panel-brand-name">AANI Market</span>
                </div>
                <div class="panel-title">
                    Your neighborhood<br>market, <em>online.</em>
                </div>
                <p class="panel-desc">
                    Shop fresh produce, meat, seafood, and native favorites from trusted vendors — all in one place.
                </p>
            </div>

            <div class="panel-features">
                <div class="panel-feature">
                    <div class="feature-dot">✓</div>
                    Verified wet market vendors
                </div>
                <div class="panel-feature">
                    <div class="feature-dot">🚚</div>
                    Pickup & delivery options
                </div>
                <div class="panel-feature">
                    <div class="feature-dot">🛒</div>
                    One cart, multiple stalls
                </div>
                <div class="panel-feature">
                    <div class="feature-dot">🌿</div>
                    Fresh products daily
                </div>
            </div>
        </div>

        {{-- ── Right: Form Panel ── --}}
        <div class="auth-form-panel">

            <div class="form-heading">
                <h1>Welcome back</h1>
                <p>Sign in to manage your cart and orders.</p>
            </div>

            <form action="{{ route('user.signin') }}" method="POST" novalidate>
                @csrf

                {{-- Email --}}
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input
                        class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        type="email" id="email" name="email"
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        autocomplete="email" required
                    >
                    @error('email')
                        <span class="invalid-msg">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrap">
                        <input
                            class="form-control has-toggle {{ $errors->has('password') ? 'is-invalid' : '' }}"
                            type="password" id="password" name="password"
                            placeholder="••••••••"
                            autocomplete="current-password" required
                        >
                        <button type="button" class="toggle-pw" onclick="togglePw()" aria-label="Toggle password">
                            <svg id="eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="invalid-msg">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Remember --}}
                <div class="remember-row">
                    <label class="check-label">
                        <input class="check-input" type="checkbox" id="remember" name="remember">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn-submit">
                    Sign in
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>

                <div class="auth-divider"><span>Don't have an account?</span></div>

                <div class="auth-footer">
                    <div class="auth-footer-item">
                        <span class="label">New customer</span>
                        <a href="{{ route('auth.register') }}">Create account →</a>
                    </div>
                    <div class="auth-footer-item">
                        <span class="label">Want to sell at the market?</span>
                        <a href="{{ route('vendor.register') }}">Apply as vendor →</a>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
    function togglePw() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eye-icon');
        const show  = input.type === 'password';
        input.type  = show ? 'text' : 'password';
        icon.innerHTML = show
            ? `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`
            : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
    }
</script>
@endpush

@endsection