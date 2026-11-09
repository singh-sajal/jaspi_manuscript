<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Reviewer Signup')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Noto+Sans:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/sweet-alert/sweetalert2.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/sweet-alert/toastr.min.css') }}">

    <!-- Local Styles -->
    <link href="{{ asset('admin/assets/css/style.css') }}" rel="stylesheet">

    <style>
        :root {
            --color-primary: #2f6db4;
            --color-primary-dark: #245a94;
            --color-primary-light: #eaf1fa;
            --color-ring: rgba(47, 109, 180, 0.35);
            --color-text: #1e293b;
            --color-text-muted: #64748b;
            --color-border: #dbe2ea;
            --color-surface: #ffffff;
            --color-danger: #dc2626;
            --radius-md: 10px;
            --radius-lg: 20px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans', sans-serif;
            margin: 0;
            color: var(--color-text);
            -webkit-font-smoothing: antialiased;
            background:
                radial-gradient(720px 420px at 8% -6%, rgba(47, 109, 180, 0.14), transparent 60%),
                radial-gradient(720px 460px at 96% 8%, rgba(45, 160, 178, 0.12), transparent 60%),
                linear-gradient(180deg, #f4f7fc 0%, #eef3f9 100%);
            background-attachment: fixed;
        }

        h1,
        h2,
        h3,
        h4,
        h5 {
            font-family: 'Figtree', sans-serif;
        }

        .auth-page {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 40px 20px 56px;
        }

        .auth-box {
            width: 100%;
            max-width: 700px;
            background: var(--color-surface);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(219, 226, 234, 0.9);
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
            overflow: hidden;
            animation: cardIn 0.35s ease-out;
        }

        @keyframes cardIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .auth-box {
                animation: none;
            }
        }

        /* ---------- Card top accent ---------- */
        .auth-topbar {
            height: 6px;
            background: linear-gradient(90deg, var(--color-primary) 0%, #3d8bd0 55%, #45b6c2 100%);
        }

        /* ---------- Header ---------- */
        .auth-header {
            text-align: center;
            padding: 28px 32px 8px;
        }

        .auth-header .logo {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--color-primary-light);
            outline: 1px solid var(--color-primary);
            box-shadow: 0 8px 22px rgba(47, 109, 180, 0.22);
            margin-bottom: 14px;
        }

        .auth-header h3 {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            color: var(--color-text);
        }

        .auth-header span.subtitle {
            display: block;
            font-size: 14px;
            color: var(--color-text-muted);
            margin: 4px auto 0;
            max-width: 420px;
            line-height: 1.55;
        }

        /* ---------- Form body ---------- */
        .auth-body {
            padding: 20px 40px 40px;
        }

        @media (max-width: 640px) {
            .auth-body {
                padding: 16px 20px 32px;
            }
        }

        .form-section {
            margin-bottom: 26px;
        }

        .form-section .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Figtree', sans-serif;
            font-size: 13.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--color-primary-dark);
            margin: 0 0 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--color-border);
        }

        .form-section .section-title i {
            font-size: 12.5px;
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: var(--color-primary-light);
            color: var(--color-primary);
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        @media (max-width: 560px) {
            .row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }

        .field {
            margin-bottom: 15px;
            position: relative;
        }

        .field label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 6px;
        }

        .field label .req {
            color: var(--color-danger);
            margin-left: 2px;
        }

        .field .input-wrap {
            position: relative;
        }

        .field .input-wrap>i {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #9aa7b5;
            font-size: 14px;
            pointer-events: none;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border-radius: var(--radius-md);
            border: 1px solid var(--color-border);
            background: var(--color-surface);
            font-family: inherit;
            font-size: 15px;
            color: var(--color-text);
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            min-height: 46px;
        }

        .field textarea {
            padding-top: 12px;
            resize: vertical;
            min-height: 96px;
        }

        .field input::placeholder,
        .field textarea::placeholder {
            color: #a3afbc;
        }

        .field input:hover,
        .field select:hover,
        .field textarea:hover {
            border-color: #b8c6d4;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3.5px var(--color-ring);
            background: #fbfdff;
        }

        .field input[readonly] {
            background: #f4f7fb;
            color: var(--color-text-muted);
            cursor: not-allowed;
        }

        .field .hint {
            display: flex;
            align-items: flex-start;
            gap: 6px;
            font-size: 12.5px;
            color: var(--color-text-muted);
            margin-top: 7px;
            line-height: 1.45;
        }

        .field .hint i {
            margin-top: 2px;
            font-size: 12px;
        }

        .field .pw-toggle {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            border: none;
            background: transparent;
            color: #9aa7b5;
            font-size: 14px;
            cursor: pointer;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.18s ease, background 0.18s ease;
        }

        .field .pw-toggle:hover {
            color: var(--color-primary);
            background: var(--color-primary-light);
        }

        .field .pw-toggle:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3.5px var(--color-ring);
        }

        .field .input-wrap:has(.pw-toggle) input {
            padding-right: 46px;
        }

        /* ---------- SASPI member segmented control ---------- */
        .segmented {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .segmented input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .segmented label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            border: 1.5px solid var(--color-border);
            border-radius: var(--radius-md);
            font-size: 14.5px;
            font-weight: 500;
            color: var(--color-text-muted);
            cursor: pointer;
            transition: all 0.18s ease;
            min-height: 46px;
            margin: 0;
        }

        .segmented label:hover {
            border-color: var(--color-primary);
            color: var(--color-primary-dark);
            background: #f8fbfe;
        }

        .segmented input[type="radio"]:checked+label {
            border-color: var(--color-primary);
            background: var(--color-primary-light);
            color: var(--color-primary-dark);
            font-weight: 600;
            box-shadow: 0 0 0 3px var(--color-ring);
        }

        .segmented input[type="radio"]:focus-visible+label {
            box-shadow: 0 0 0 3.5px var(--color-ring);
        }

        .membership-field {
            animation: fadeIn 0.22s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .membership-field {
                animation: none;
            }
        }

        /* ---------- Areas of interest ---------- */
        .interest-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(215px, 1fr));
            gap: 10px;
        }

        .interest-item input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .field .interest-item label {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 11px 14px;
            border: 1.5px solid var(--color-border);
            border-radius: var(--radius-md);
            font-size: 14.5px;
            font-weight: 500;
            color: var(--color-text-muted);
            cursor: pointer;
            transition: all 0.18s ease;
            min-height: 46px;
            margin: 0;
        }

        .field .interest-item label:hover {
            border-color: var(--color-primary);
            color: var(--color-primary-dark);
            background: #f8fbfe;
        }

        .interest-item input[type="checkbox"]:checked+label {
            border-color: var(--color-primary);
            background: var(--color-primary-light);
            color: var(--color-primary-dark);
            font-weight: 600;
            box-shadow: 0 0 0 3px var(--color-ring);
        }

        .interest-item input[type="checkbox"]:focus-visible+label {
            box-shadow: 0 0 0 3.5px var(--color-ring);
        }

        .interest-item .check-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 6px;
            border: 1.5px solid var(--color-border);
            background: #fff;
            color: transparent;
            font-size: 10px;
            flex-shrink: 0;
            transition: all 0.18s ease;
        }

        .interest-item input[type="checkbox"]:checked+label .check-icon {
            background: var(--color-primary);
            border-color: var(--color-primary);
            color: #fff;
        }

        @media (prefers-reduced-motion: reduce) {

            .field .interest-item label,
            .interest-item .check-icon {
                transition: none;
            }
        }

        /* ---------- Checkbox & button ---------- */
        .terms-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 14px;
            color: var(--color-text);
            margin: 22px 0 18px;
            cursor: pointer;
        }

        .terms-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-top: 1px;
            accent-color: var(--color-primary);
            flex-shrink: 0;
            cursor: pointer;
        }

        .terms-row a {
            color: var(--color-primary);
            font-weight: 600;
            text-decoration: none;
        }

        .terms-row a:hover {
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            padding: 14px 20px;
            background: linear-gradient(90deg, var(--color-primary) 0%, #3d8bd0 100%);
            color: #fff;
            border: none;
            border-radius: var(--radius-md);
            font-family: 'Figtree', sans-serif;
            font-size: 15.5px;
            font-weight: 600;
            letter-spacing: 0.2px;
            cursor: pointer;
            min-height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: box-shadow 0.2s ease, filter 0.2s ease, transform 0.15s ease;
            box-shadow: 0 8px 20px rgba(47, 109, 180, 0.32);
        }

        .btn-submit:hover {
            filter: brightness(1.05);
            box-shadow: 0 10px 26px rgba(47, 109, 180, 0.4);
        }

        .btn-submit:active {
            transform: scale(0.99);
        }

        .btn-submit:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3.5px var(--color-ring);
        }

        .auth-footer {
            margin-top: 18px;
            text-align: center;
            font-size: 14px;
            color: var(--color-text-muted);
        }

        .auth-footer a {
            color: var(--color-primary);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 640px) {
            .auth-page {
                padding: 0 0 40px;
                align-items: flex-start;
            }

            .auth-box {
                border-radius: 0;
                border-left: none;
                border-right: none;
                border-top: none;
                box-shadow: none;
            }

            .auth-header {
                padding: 26px 24px 4px;
            }
        }
    </style>
</head>

<body>
    <div class="auth-page">
        <div class="auth-box">
            <div class="auth-topbar"></div>

            <header class="auth-header">
                <img class="logo" src="{{ asset('web/logo.jpeg') }}" alt="JASPI Logo">
                <h3>New Reviewer Signup</h3>
                <span class="subtitle">Join the review team of the Journal of Antimicrobial Stewardship Practices and Infectious Diseases (JASPI)</span>
            </header>

            <main class="auth-body">
                <form action="{{ route('admin.registration') }}" method="POST" enctype="multipart/form-data"
                    novalidate>
                    @csrf
                    <input type="hidden" name="role_name" value="{{ $role->name }}">

                    <!-- Personal Details -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="fas fa-user"></i> Personal Details</h4>
                        <div class="field">
                            <label for="name">Reviewer Name <span class="req">*</span></label>
                            <div class="input-wrap">
                                <i class="fas fa-user"></i>
                                <input type="text" id="name" name="name" placeholder="e.g. Dr. Anjali Sharma"
                                    required autocomplete="name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="field">
                                    <label for="phone">Phone</label>
                                    <div class="input-wrap">
                                        <i class="fas fa-phone"></i>
                                        <input type="number" id="phone" name="phone"
                                            value="{{ $phone ?? '' }}" placeholder="Phone" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="field">
                                    <label for="email">Email</label>
                                    <div class="input-wrap">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" id="email" name="email"
                                            value="{{ $email ?? '' }}" placeholder="Email" readonly
                                            autocomplete="email">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="field">
                                    <label for="dob">Date of Birth <span class="req">*</span></label>
                                    <div class="input-wrap">
                                        <i class="fas fa-calendar"></i>
                                        <input type="date" id="dob" name="dob" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="field">
                                    <label for="gender">Gender <span class="req">*</span></label>
                                    <div class="input-wrap">
                                        <i class="fas fa-venus-mars"></i>
                                        <select id="gender" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SASPI Membership -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="fas fa-id-card"></i> SASPI Membership</h4>
                        <div class="field">
                            <label>Annual/Associate/Life/Foundation member of SASPI? <span class="req">*</span></label>
                            <div class="segmented" role="radiogroup" aria-label="SASPI membership">
                                <input type="radio" name="saspi_member" id="saspi_yes" value="Yes" required>
                                <label for="saspi_yes"><i class="fas fa-check-circle"></i> Yes</label>
                                <input type="radio" name="saspi_member" id="saspi_no" value="No" required>
                                <label for="saspi_no"><i class="fas fa-times-circle"></i> No</label>
                            </div>
                        </div>
                        <div class="field membership-field" id="membershipIdGroup" style="display: none;">
                            <label for="membership_id">Membership ID</label>
                            <div class="input-wrap">
                                <i class="fas fa-id-badge"></i>
                                <input type="text" id="membership_id" name="membership_id"
                                    placeholder="Enter your SASPI membership ID">
                            </div>
                            <span class="hint"><i class="fas fa-info-circle"></i> Once documented, no need to fill name, age, email ID, contact no, designation and affiliation unless updating SASPI records.</span>
                        </div>
                    </div>

                    <!-- Academic Profile -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="fas fa-graduation-cap"></i> Academic Profile</h4>
                        <div class="row">
                            <div class="col">
                                <div class="field">
                                    <label for="designation">Designation <span class="req">*</span></label>
                                    <div class="input-wrap">
                                        <i class="fas fa-briefcase"></i>
                                        <input type="text" id="designation" name="designation"
                                            placeholder="e.g. Associate Professor" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="field">
                                    <label for="affiliation">Affiliation (including dept name) <span class="req">*</span></label>
                                    <div class="input-wrap">
                                        <i class="fas fa-building"></i>
                                        <input type="text" id="affiliation" name="affiliation"
                                            placeholder="e.g. AIIMS, Dept. of Medicine" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="field">
                                    <label for="research_experience_years">Academic and/or Research Experience (in years) <span class="req">*</span></label>
                                    <div class="input-wrap">
                                        <i class="fas fa-hourglass-half"></i>
                                        <input type="number" id="research_experience_years"
                                            name="research_experience_years" placeholder="e.g. 10"
                                            min="0" step="0.1" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="field">
                                    <label for="avg_articles_published">Average number of articles in indexed journals (PubMed/Scopus/DOAJ) <span class="req">*</span></label>
                                    <div class="input-wrap">
                                        <i class="fas fa-file-alt"></i>
                                        <input type="number" id="avg_articles_published"
                                            name="avg_articles_published" placeholder="e.g. 15"
                                            min="0" step="0.1" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <label>Areas of Interest <span class="req">*</span></label>
                            <div class="interest-grid">
                                @forelse ($integrations ?? [] as $integration)
                                    <div class="interest-item">
                                        <input type="checkbox" name="integrations[]"
                                            id="integration_{{ $integration->id }}"
                                            value="{{ $integration->id }}">
                                        <label for="integration_{{ $integration->id }}">
                                            <span class="check-icon"><i class="fas fa-check"></i></span>
                                            {{ $integration->name }}
                                        </label>
                                    </div>
                                @empty
                                    <span class="hint"><i class="fas fa-info-circle"></i> No areas of
                                        interest have been configured yet.</span>
                                @endforelse
                            </div>
                            <span class="hint"><i class="fas fa-info-circle"></i> These decide which
                                manuscripts you will be invited to review.</span>
                        </div>
                        <div class="field">
                            <label for="h_index">h-Index</label>
                            <div class="input-wrap">
                                <i class="fas fa-chart-line"></i>
                                <input type="text" id="h_index" name="h_index" placeholder="e.g. 12">
                            </div>
                        </div>
                        <div class="field">
                            <label for="remarks">Remarks</label>
                            <div class="input-wrap">
                                <i class="fas fa-comment-dots"></i>
                                <textarea id="remarks" name="remarks" rows="4"
                                    placeholder="Number of manuscripts per quarter issue, preferred recognition/benefits from JASPI"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="fas fa-map-marker-alt"></i> Address</h4>
                        <div class="row">
                            <div class="col">
                                <div class="field">
                                    <label for="country">Country <span class="req">*</span></label>
                                    <div class="input-wrap">
                                        <i class="fas fa-globe"></i>
                                        <input type="text" id="country" name="country" placeholder="e.g. India"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="field">
                                    <label for="state">State <span class="req">*</span></label>
                                    <div class="input-wrap">
                                        <i class="fas fa-flag"></i>
                                        <input type="text" id="state" name="state" placeholder="e.g. Delhi"
                                            required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="field">
                                    <label for="city">City <span class="req">*</span></label>
                                    <div class="input-wrap">
                                        <i class="fas fa-city"></i>
                                        <input type="text" id="city" name="city" placeholder="e.g. New Delhi"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="field">
                                    <label for="pincode">Pin Code <span class="req">*</span></label>
                                    <div class="input-wrap">
                                        <i class="fas fa-map-pin"></i>
                                        <input type="number" id="pincode" name="pincode"
                                            placeholder="e.g. 110001" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <label for="address">Address <span class="req">*</span></label>
                            <div class="input-wrap">
                                <i class="fas fa-map-marker-alt"></i>
                                <input type="text" id="address" name="address"
                                    placeholder="Street, area, landmark" required>
                            </div>
                        </div>
                    </div>

                    <!-- Account -->
                    <div class="form-section">
                        <h4 class="section-title"><i class="fas fa-lock"></i> Account</h4>
                        <div class="field">
                            <label for="password">Password <span class="req">*</span></label>
                            <div class="input-wrap">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password"
                                    placeholder="At least 6 characters" required autocomplete="new-password">
                                <button type="button" class="pw-toggle" id="pwToggle" aria-label="Show password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <label class="terms-row">
                        <input type="checkbox" name="terms" required>
                        <span>I accept the <a href="#">terms &amp; conditions</a></span>
                    </label>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-user-plus"></i> Register
                    </button>

                    <div class="auth-footer">
                        Already registered? <a href="{{ route('admin.login') }}">Login</a> &middot; Or return to <a
                            href="{{ route('home') }}">Home</a>
                    </div>
                </form>
            </main>
        </div>
    </div>
    <script src="{{ asset('admin/assets/vendor/jquery/jquery-3.6.0.min.js') }}"></script>
    <script>
        $(function() {
            var $group = $('#membershipIdGroup');
            $('input[name="saspi_member"]').on('change', function() {
                if ($(this).val() === 'Yes') {
                    $group.show();
                    $group.find('input').prop('required', true);
                } else {
                    $group.hide();
                    $group.find('input').prop('required', false).val('');
                }
            });

            var $pw = $('#password');
            $('#pwToggle').on('click', function() {
                var isPassword = $pw.attr('type') === 'password';
                $pw.attr('type', isPassword ? 'text' : 'password');
                $(this).find('i').attr('class', isPassword ? 'fas fa-eye-slash' : 'fas fa-eye');
                $(this).attr('aria-label', isPassword ? 'Hide password' : 'Show password');
                $pw.trigger('focus');
            });
        });
    </script>
    <script src="{{ asset('admin/assets/vendor/sweet-alert/toastr.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/ajax-engine-1.0.js') }}"></script>
</body>

</html>

