@extends('layouts.base')
@section('title', 'Apply as Vendor')
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
        --warm:      #D97706;
        --warm-lt:   #FEF3C7;
        --danger:    #DC2626;
        --radius:    10px;
        --shadow:    0 1px 3px rgba(0,0,0,.06), 0 4px 14px rgba(0,0,0,.05);
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

    /* ── Shell ── */
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
    .auth-panel::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255,255,255,.1) 1px, transparent 1px);
        background-size: 24px 24px;
        pointer-events: none;
    }
    .auth-panel::after {
        content: '';
        position: absolute;
        width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,.07) 0%, transparent 70%);
        bottom: -80px; right: -80px;
        pointer-events: none;
    }

    .panel-top { position: relative; z-index: 1; }

    .panel-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 44px;
    }
    .panel-brand-icon {
        width: 36px; height: 36px;
        background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: 17px;
    }
    .panel-brand-name { font-size: 15px; font-weight: 600; color: #fff; }

    .panel-title {
        font-size: 28px;
        font-weight: 600;
        color: #fff;
        line-height: 1.3;
        letter-spacing: -.3px;
        margin-bottom: 12px;
    }
    .panel-title em { font-style: normal; color: #a7f3c1; }

    .panel-desc {
        font-size: 13px;
        color: rgba(255,255,255,.55);
        line-height: 1.65;
    }

    .panel-features {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        gap: 11px;
    }
    .panel-feature {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        color: rgba(255,255,255,.7);
    }
    .feature-dot {
        width: 24px; height: 24px;
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; flex-shrink: 0;
    }

    /* ── Right form panel ── */
    .auth-form-panel {
        padding: 52px 48px 44px;
        overflow-y: auto;
        max-height: calc(100vh - 100px);
    }
    .auth-form-panel::-webkit-scrollbar { width: 4px; }
    .auth-form-panel::-webkit-scrollbar-track { background: transparent; }
    .auth-form-panel::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    .form-heading { margin-bottom: 24px; }
    .form-heading h1 { font-size: 23px; font-weight: 600; color: var(--text); margin-bottom: 3px; }
    .form-heading p  { font-size: 13px; color: var(--muted); }

    /* ── Form sections ── */
    .form-section { margin-bottom: 22px; }

    .form-section-label {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--muted);
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
    }
    .form-section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    /* ── Field layout ── */
    .field-row {
        display: grid;
        gap: 12px;
        margin-bottom: 12px;
    }
    .field-row.cols-2 { grid-template-columns: 1fr 1fr; }
    .field-row.cols-1 { grid-template-columns: 1fr; }
    .field-row:last-child { margin-bottom: 0; }

    /* ── Field ── */
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .form-label {
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--muted);
        display: flex;
        align-items: center;
        gap: 3px;
    }
    .req { color: var(--danger); font-size: 12px; }

    .input-wrap { position: relative; }

    .form-control {
        width: 100%;
        height: 50px;
        padding: 0 18px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        color: var(--text);
        background: var(--surface);
        outline: none;
        transition: border-color .15s, box-shadow .15s;
        display: block;
        -webkit-appearance: none;
        appearance: none;
    }
    .form-control::placeholder { color: #c4c1bb; font-size: 13.5px; }
    .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(29,111,66,.1);
    }
    .form-control.is-invalid { border-color: var(--danger); }
    .form-control.is-invalid:focus { box-shadow: 0 0 0 3px rgba(220,38,38,.1); }

    textarea.form-control {
        height: auto;
        padding: 10px 14px;
        resize: vertical;
        min-height: 88px;
    }

    /* Password toggle */
    .toggle-pw {
        position: absolute;
        right: 11px; top: 50%;
        transform: translateY(-50%);
        background: none; border: none;
        cursor: pointer; color: var(--muted);
        padding: 4px;
        display: flex; align-items: center;
        transition: color .15s; z-index: 1;
    }
    .toggle-pw:hover { color: var(--text); }
    .toggle-pw svg { width: 15px; height: 15px; }
    .has-toggle { padding-right: 40px; }

    /* Error */
    .invalid-msg {
        font-size: 11.5px;
        color: var(--danger);
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .invalid-msg::before {
        content: '!';
        display: inline-flex; align-items: center; justify-content: center;
        width: 13px; height: 13px;
        background: var(--danger); color: #fff;
        border-radius: 50%;
        font-size: 9px; font-weight: 700; flex-shrink: 0;
    }

    /* ── Services checkboxes ── */
    .services-grid {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 6px;
    }
    .service-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 14px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        cursor: pointer;
        transition: border-color .15s, background .15s;
        user-select: none;
    }
    .service-option:hover { border-color: #ccc; background: var(--bg); }
    .service-option input[type="checkbox"] {
        width: 16px; height: 16px;
        accent-color: var(--accent);
        cursor: pointer; flex-shrink: 0;
        margin: 0;
    }
    .service-option input[type="checkbox"]:checked + .service-label-wrap { color: var(--accent-dk); }
    .service-option:has(input:checked) {
        border-color: var(--accent);
        background: var(--accent-lt);
    }
    .service-label-wrap { display: flex; flex-direction: column; gap: 1px; }
    .service-label-main { font-size: 13.5px; font-weight: 500; color: var(--text); }
    .service-label-sub  { font-size: 11.5px; color: var(--muted); }

    .services-note {
        font-size: 12px;
        color: var(--muted);
        margin-top: 8px;
        display: flex;
        align-items: flex-start;
        gap: 6px;
    }
    .services-note::before {
        content: 'ℹ';
        font-size: 11px;
        color: var(--muted);
        flex-shrink: 0;
        margin-top: 1px;
    }

    /* ── Info box ── */
    .info-box {
        background: var(--warm-lt);
        border: 1px solid #fde68a;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 13px;
        color: #92400e;
        line-height: 1.55;
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .info-box-icon { font-size: 15px; flex-shrink: 0; margin-top: 1px; }

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
        margin-bottom: 14px;
    }
    .auth-divider::before,
    .auth-divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
    .auth-divider span { font-size: 11.5px; color: var(--muted); font-weight: 500; white-space: nowrap; }

    /* Footer */
    .auth-footer { display: flex; flex-direction: column; gap: 7px; }
    .auth-footer-item {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 10px 14px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .auth-footer-item .label { font-size: 13px; color: var(--muted); }
    .auth-footer-item a { color: var(--accent); font-weight: 600; font-size: 12.5px; }
    .auth-footer-item a:hover { text-decoration: underline; }

    /* ── Responsive ── */
    @media (max-width: 700px) {
        .auth-card       { grid-template-columns: 1fr; }
        .auth-panel      { display: none; }
        .auth-form-panel { padding: 32px 24px 36px; max-height: none; }
    }
    @media (max-width: 420px) {
        .field-row.cols-2 { grid-template-columns: 1fr; }
        .auth-form-panel  { padding: 24px 18px 28px; }
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
                <div class="panel-title">Sell at the<br><em>neighborhood</em><br>market.</div>
                <p class="panel-desc">Apply to list your stall on AANI Market and reach customers who shop fresh, local, and online.</p>
            </div>

            <div class="panel-features">
                <div class="panel-feature"><div class="feature-dot">🏪</div> Your own stall on the map</div>
                <div class="panel-feature"><div class="feature-dot">📦</div> Manage your own products</div>
                <div class="panel-feature"><div class="feature-dot">🚚</div> Pickup & delivery options</div>
                <div class="panel-feature"><div class="feature-dot">📊</div> Orders & sales dashboard</div>
            </div>
        </div>

        {{-- ── Right: Form Panel ── --}}
        <div class="auth-form-panel">

            <div class="form-heading">
                <h1>Become an AANI Vendor</h1>
                <p>Submit your application to sell at the market.</p>
            </div>

            <form action="{{ route('vendor.register.submit') }}" method="POST" novalidate>
                @csrf

                {{-- Account Details --}}
                <div class="form-section">
                    <div class="form-section-label">Account Details</div>

                    <div class="field-row cols-2">
                        <div class="form-group">
                            <label class="form-label" for="email">Email Address <span class="req">*</span></label>
                            <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                   type="email" id="email" name="email"
                                   value="{{ old('email') }}" placeholder="you@example.com"
                                   autocomplete="email" required>
                            @error('email')
                                <span class="invalid-msg">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="contact_phone">Contact Phone</label>
                            <input class="form-control {{ $errors->has('contact_phone') ? 'is-invalid' : '' }}"
                                   type="tel" id="contact_phone" name="contact_phone"
                                   value="{{ old('contact_phone') }}" placeholder="09XXXXXXXXX"
                                   autocomplete="tel">
                            @error('contact_phone')
                                <span class="invalid-msg">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="field-row cols-2">
                        <div class="form-group">
                            <label class="form-label" for="password">Password <span class="req">*</span></label>
                            <div class="input-wrap">
                                <input class="form-control has-toggle {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                       type="password" id="password" name="password"
                                       placeholder="Min. 8 chars"
                                       autocomplete="new-password" required>
                                <button type="button" class="toggle-pw" onclick="togglePw('password','eye-1')" aria-label="Toggle">
                                    <svg id="eye-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <span class="invalid-msg">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">Confirm Password <span class="req">*</span></label>
                            <div class="input-wrap">
                                <input class="form-control has-toggle"
                                       type="password" id="password_confirmation" name="password_confirmation"
                                       placeholder="Repeat password"
                                       autocomplete="new-password" required>
                                <button type="button" class="toggle-pw" onclick="togglePw('password_confirmation','eye-2')" aria-label="Toggle">
                                    <svg id="eye-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Business Details --}}
                <div class="form-section">
                    <div class="form-section-label">Business Details</div>

                    <div class="field-row cols-2">
                        <div class="form-group">
                            <label class="form-label" for="business_name">Business / Stall Name <span class="req">*</span></label>
                            <input class="form-control {{ $errors->has('business_name') ? 'is-invalid' : '' }}"
                                   type="text" id="business_name" name="business_name"
                                   value="{{ old('business_name') }}"
                                   placeholder="e.g. Dela Cruz Veggies"
                                   required>
                            @error('business_name')
                                <span class="invalid-msg">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="owner_name">Owner Name <span class="req">*</span></label>
                            <input class="form-control {{ $errors->has('owner_name') ? 'is-invalid' : '' }}"
                                   type="text" id="owner_name" name="owner_name"
                                   value="{{ old('owner_name') }}"
                                   autocomplete="name"
                                   required>
                            @error('owner_name')
                                <span class="invalid-msg">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="field-row cols-1">
                        <div class="form-group">
                            <label class="form-label" for="business_description">What do you sell?</label>
                            <textarea class="form-control {{ $errors->has('business_description') ? 'is-invalid' : '' }}"
                                      id="business_description" name="business_description"
                                      placeholder="Short description of your products and sourcing…">{{ old('business_description') }}</textarea>
                            @error('business_description')
                                <span class="invalid-msg">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Services --}}
                <div class="form-section">
                    <div class="form-section-label">Services You Can Offer</div>

                    <div class="services-grid">
                        <label class="service-option">
                            <input type="checkbox" value="1" name="weekend_pickup_enabled" id="weekend_pickup_enabled"
                                   {{ old('weekend_pickup_enabled', 1) ? 'checked' : '' }}>
                            <div class="service-label-wrap">
                                <span class="service-label-main">🏪 Weekend Pickup</span>
                                <span class="service-label-sub">Customers collect their order at your market stall</span>
                            </div>
                        </label>
                        <label class="service-option">
                            <input type="checkbox" value="1" name="weekday_delivery_enabled" id="weekday_delivery_enabled"
                                   {{ old('weekday_delivery_enabled') ? 'checked' : '' }}>
                            <div class="service-label-wrap">
                                <span class="service-label-main">🚚 Weekday Delivery</span>
                                <span class="service-label-sub">Deliver orders to customers Monday–Friday</span>
                            </div>
                        </label>
                        <label class="service-option">
                            <input type="checkbox" value="1" name="weekend_delivery_enabled" id="weekend_delivery_enabled"
                                   {{ old('weekend_delivery_enabled') ? 'checked' : '' }}>
                            <div class="service-label-wrap">
                                <span class="service-label-main">🚚 Weekend Delivery</span>
                                <span class="service-label-sub">Deliver orders to customers on weekends</span>
                            </div>
                        </label>
                    </div>
                    <p class="services-note">Admins may adjust these settings after reviewing your application.</p>
                </div>

                {{-- Info notice --}}
                <div class="info-box">
                    <span class="info-box-icon">📋</span>
                    Your application will be reviewed by the market administrators. Once approved, you will receive an email and be able to log in as a vendor.
                </div>

                <button type="submit" class="btn-submit">
                    Submit Application
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>

                <div class="auth-divider"><span>Already have an account?</span></div>

                <div class="auth-footer">
                    <div class="auth-footer-item">
                        <span class="label">Existing account</span>
                        <a href="{{ route('auth.login') }}">Sign in →</a>
                    </div>
                    <div class="auth-footer-item">
                        <span class="label">Shopping as a customer?</span>
                        <a href="{{ route('auth.register') }}">Create customer account →</a>
                    </div>
                </div>

            </form>
        </div>

    </div>
</div>

@push('scripts')
<script>
    function togglePw(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        const show  = input.type === 'password';
        input.type  = show ? 'text' : 'password';
        icon.innerHTML = show
            ? `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`
            : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
    }
</script>
@endpush

@endsection