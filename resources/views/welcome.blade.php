{{--
    InvoiceKit — Award-winning Landing Page
    Multilingual via resources/lang/landing/{LANG}.md
--}}
@php
    $availableLangs = [
        'en',
        'bg',
        'cs',
        'da',
        'de',
        'el',
        'es',
        'et',
        'fi',
        'fr',
        'ga',
        'hr',
        'hu',
        'it',
        'lt',
        'lv',
        'mt',
        'nl',
        'pl',
        'pt',
        'ro',
        'sk',
        'sl',
        'sv',
    ];
    $requestedLang = strtolower(request()->get('lang', session('ik_lang', 'en')));
    $lang = in_array($requestedLang, $availableLangs) ? $requestedLang : 'en';
    session(['ik_lang' => $lang, 'locale' => $lang]);

    $langMeta = [
        'bg' => ['flag' => '🇧🇬', 'name' => 'Български'],
        'cs' => ['flag' => '🇨🇿', 'name' => 'Čeština'],
        'da' => ['flag' => '🇩🇰', 'name' => 'Dansk'],
        'de' => ['flag' => '🇩🇪', 'name' => 'Deutsch'],
        'el' => ['flag' => '🇬🇷', 'name' => 'Ελληνικά'],
        'en' => ['flag' => '🇬🇧', 'name' => 'English'],
        'es' => ['flag' => '🇪🇸', 'name' => 'Español'],
        'et' => ['flag' => '🇪🇪', 'name' => 'Eesti'],
        'fi' => ['flag' => '🇫🇮', 'name' => 'Suomi'],
        'fr' => ['flag' => '🇫🇷', 'name' => 'Français'],
        'ga' => ['flag' => '🇮🇪', 'name' => 'Gaeilge'],
        'hr' => ['flag' => '🇭🇷', 'name' => 'Hrvatski'],
        'hu' => ['flag' => '🇭🇺', 'name' => 'Magyar'],
        'it' => ['flag' => '🇮🇹', 'name' => 'Italiano'],
        'lt' => ['flag' => '🇱🇹', 'name' => 'Lietuvių'],
        'lv' => ['flag' => '🇱🇻', 'name' => 'Latviešu'],
        'mt' => ['flag' => '🇲🇹', 'name' => 'Malti'],
        'nl' => ['flag' => '🇳🇱', 'name' => 'Nederlands'],
        'pl' => ['flag' => '🇵🇱', 'name' => 'Polski'],
        'pt' => ['flag' => '🇵🇹', 'name' => 'Português'],
        'ro' => ['flag' => '🇷🇴', 'name' => 'Română'],
        'sk' => ['flag' => '🇸🇰', 'name' => 'Slovenčina'],
        'sl' => ['flag' => '🇸🇮', 'name' => 'Slovenščina'],
        'sv' => ['flag' => '🇸🇪', 'name' => 'Svenska'],
    ];

    $parseLandingFile = function (string $path): array {
        if (!file_exists($path)) {
            return [];
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $result = [];
        $key = null;
        $buffer = [];
        foreach ($lines as $line) {
            if (preg_match('/^\[([a-z0-9_]+)\]$/', $line, $m)) {
                if ($key !== null) {
                    $result[$key] = trim(implode("\n", $buffer));
                }
                $key = $m[1];
                $buffer = [];
            } elseif ($key !== null) {
                $buffer[] = $line;
            }
        }
        if ($key !== null) {
            $result[$key] = trim(implode("\n", $buffer));
        }
        return $result;
    };

    $en = $parseLandingFile(resource_path('lang/landing/EN.md'));
    $translations = $parseLandingFile(resource_path('lang/landing/' . strtoupper($lang) . '.md'));
    $t = array_merge($en, $translations);
    $g = fn(string $k, string $d = '') => htmlspecialchars($t[$k] ?? $d, ENT_QUOTES, 'UTF-8');
    $gnl = fn(string $k, string $d = '') => nl2br(htmlspecialchars($t[$k] ?? $d, ENT_QUOTES, 'UTF-8'));

    $vatRates = [
        ['🇦🇹', 'AT', '20%'],
        ['🇧🇪', 'BE', '21%'],
        ['🇧🇬', 'BG', '20%'],
        ['🇭🇷', 'HR', '25%'],
        ['🇨🇾', 'CY', '19%'],
        ['🇨🇿', 'CZ', '21%'],
        ['🇩🇰', 'DK', '25%'],
        ['🇪🇪', 'EE', '22%'],
        ['🇫🇮', 'FI', '25.5%'],
        ['🇫🇷', 'FR', '20%'],
        ['🇩🇪', 'DE', '19%'],
        ['🇬🇷', 'GR', '24%'],
        ['🇭🇺', 'HU', '27%'],
        ['🇮🇪', 'IE', '23%'],
        ['🇮🇹', 'IT', '22%'],
        ['🇱🇻', 'LV', '21%'],
        ['🇱🇹', 'LT', '21%'],
        ['🇱🇺', 'LU', '17%'],
        ['🇲🇹', 'MT', '18%'],
        ['🇳🇱', 'NL', '21%'],
        ['🇵🇱', 'PL', '23%'],
        ['🇵🇹', 'PT', '23%'],
        ['🇷🇴', 'RO', '19%'],
        ['🇸🇰', 'SK', '23%'],
        ['🇸🇮', 'SI', '22%'],
        ['🇪🇸', 'ES', '21%'],
        ['🇸🇪', 'SE', '25%'],
    ];

    $euCountries = [
        ['🇦🇹', 'Austria'],
        ['🇧🇪', 'Belgium'],
        ['🇧🇬', 'Bulgaria'],
        ['🇭🇷', 'Croatia'],
        ['🇨🇾', 'Cyprus'],
        ['🇨🇿', 'Czechia'],
        ['🇩🇰', 'Denmark'],
        ['🇪🇪', 'Estonia'],
        ['🇫🇮', 'Finland'],
        ['🇫🇷', 'France'],
        ['🇩🇪', 'Germany'],
        ['🇬🇷', 'Greece'],
        ['🇭🇺', 'Hungary'],
        ['🇮🇪', 'Ireland'],
        ['🇮🇹', 'Italy'],
        ['🇱🇻', 'Latvia'],
        ['🇱🇹', 'Lithuania'],
        ['🇱🇺', 'Luxembourg'],
        ['🇲🇹', 'Malta'],
        ['🇳🇱', 'Netherlands'],
        ['🇵🇱', 'Poland'],
        ['🇵🇹', 'Portugal'],
        ['🇷🇴', 'Romania'],
        ['🇸🇰', 'Slovakia'],
        ['🇸🇮', 'Slovenia'],
        ['🇪🇸', 'Spain'],
        ['🇸🇪', 'Sweden'],
    ];

    $featShowcase = [
        [
            'icon' => '⏱',
            'col' => 'icon-indigo',
            'img' => 'app-timer.png',
            'side' => 'left',
            'tk' => 'f1_title',
            'dk' => 'f1_desc',
        ],
        [
            'icon' => '📄',
            'col' => 'icon-sky',
            'img' => 'app-invoice-view.png',
            'side' => 'right',
            'tk' => 'f2_title',
            'dk' => 'f2_desc',
        ],
        [
            'icon' => '🔗',
            'col' => 'icon-rose',
            'img' => 'app-portal.png',
            'side' => 'left',
            'tk' => 'f6_title',
            'dk' => 'f6_desc',
        ],
    ];

    $featGrid = [
        ['icon' => '🇪🇺', 'col' => 'icon-teal', 'tk' => 'f3_title', 'dk' => 'f3_desc'],
        ['icon' => '👥', 'col' => 'icon-violet', 'tk' => 'f4_title', 'dk' => 'f4_desc'],
        ['icon' => '🔁', 'col' => 'icon-amber', 'tk' => 'f5_title', 'dk' => 'f5_desc'],
        ['icon' => '📲', 'col' => 'icon-emerald', 'tk' => 'f7_title', 'dk' => 'f7_desc'],
        ['icon' => '🧾', 'col' => 'icon-orange', 'tk' => 'f8_title', 'dk' => 'f8_desc'],
        ['icon' => '💱', 'col' => 'icon-cyan', 'tk' => 'f9_title', 'dk' => 'f9_desc'],
        ['icon' => '🤖', 'col' => 'icon-violet', 'tk' => 'f10_title', 'dk' => 'f10_desc'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InvoiceKit — {!! strip_tags($gnl('hero_headline', 'EU Invoicing for Freelancers')) !!}</title>
    <meta name="description" content="{!! $g('hero_subheadline') !!}">
    <link rel="canonical" href="{{ url('/') }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="InvoiceKit — {!! strip_tags($gnl('hero_headline', 'EU Invoicing for Freelancers')) !!}">
    <meta property="og:description" content="{!! $g('hero_subheadline') !!}">
    <meta property="og:image" content="{{ url('/images/og-thumb.png') }}">
    <meta property="og:locale" content="{{ config('invoicekit.og_locales.' . $lang, 'en_GB') }}">
    @foreach ($availableLangs as $altLang)
        @if ($altLang !== $lang)
            <meta property="og:locale:alternate" content="{{ config('invoicekit.og_locales.' . $altLang, 'en_GB') }}">
        @endif
    @endforeach

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="InvoiceKit — {!! strip_tags($gnl('hero_headline', 'EU Invoicing for Freelancers')) !!}">
    <meta name="twitter:description" content="{!! $g('hero_subheadline') !!}">
    <meta name="twitter:image" content="{{ url('/images/og-thumb.png') }}">

    {{-- RSS Feed --}}
    <link rel="alternate" type="application/rss+xml" title="InvoiceKit Blog" href="{{ route('blog.feed') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet">
    @vite('resources/css/landing.css')

    @if (config('services.google.analytics_id'))
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', '{{ config('services.google.analytics_id') }}');
        </script>
    @endif
</head>

<body>

    <!-- ====== NAVBAR ====== -->
    <nav class="nav" id="topnav">
        <a href="/" class="nav-logo">
            <img src="/img/logo.png" alt="InvoiceKit" class="nav-logo-img">
            InvoiceKit
        </a>
        <div class="nav-links">
            <a href="#features">{!! $g('nav_features') !!}</a>
            <a href="#pricing">{!! $g('nav_pricing') !!}</a>
            <a href="{{ route('blog.index') }}">Blog</a>
        </div>
        <div class="nav-right">
            <div class="lang-wrap">
                <div class="lang-toggle" id="langToggle" role="button" aria-haspopup="listbox" aria-expanded="false">
                    <span>{{ $langMeta[$lang]['flag'] ?? '🌐' }}</span>
                    <span>{{ strtoupper($lang) }}</span>
                    <span class="chevron">▾</span>
                </div>
                <div class="lang-dd" id="langDd" role="listbox">
                    @foreach ($langMeta as $code => $meta)
                        <a class="lang-opt{{ $code === $lang ? ' active' : '' }}" href="?lang={{ $code }}"
                            role="option" aria-selected="{{ $code === $lang ? 'true' : 'false' }}">
                            <span>{{ $meta['flag'] }}</span><span>{{ $meta['name'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <a href="/login" class="btn btn-ghost">{!! $g('nav_login') !!}</a>
            <a href="/register" class="btn btn-primary">{!! $g('nav_cta') !!} →</a>
        </div>
    </nav>

    <!-- ====== HERO ====== -->
    <section class="hero" id="home">
        <div class="hero-bg">
            <div class="hg1"></div>
            <div class="hg2"></div>
            <div class="hg3"></div>
            <div class="hero-grid"></div>
        </div>
        <div class="hero-container">
            <div class="hero-left">
                <div class="hero-badge">{!! $g('hero_badge') !!}</div>
                <h1 class="hero-title grad">{!! $gnl('hero_headline') !!}</h1>
                <p class="hero-sub">{!! $g('hero_subheadline') !!}</p>
                <div class="hero-cta">
                    <a href="/register" class="btn btn-primary btn-lg">{!! $g('hero_cta_primary') !!}</a>
                    <a href="#features" class="btn-out-lg">{!! $g('hero_cta_secondary') !!}</a>
                </div>
                <div class="hero-trust">
                    <span class="t-item">✓ {!! $g('hero_trust_1') !!}</span>
                    <span class="t-dot"></span>
                    <span class="t-item">✓ {!! $g('hero_trust_2') !!}</span>
                    <span class="t-dot"></span>
                    <span class="t-item">✓ {!! $g('hero_trust_3') !!}</span>
                    <span class="t-dot"></span>
                    <span class="t-item">✓ {!! $g('hero_trust_4') !!}</span>
                </div>
            </div>
            <div class="hero-right">
                <div class="inv-card">
                    <div class="inv-hdr">
                        <div class="inv-lg">⚡ InvoiceKit</div>
                        <div class="inv-num">INV-2026-0042</div>
                    </div>
                    <div class="inv-meta">
                        <div>
                            <div class="inv-lbl">From</div>
                            <div class="inv-val">Your Business</div>
                            <div class="inv-val" style="font-size:.72rem;color:var(--subtle)">BG123456789</div>
                        </div>
                        <div>
                            <div class="inv-lbl">Bill to</div>
                            <div class="inv-val">Acme GmbH</div>
                            <div class="inv-vat-chip">🇩🇪 DE987654321 · VAT registered</div>
                        </div>
                    </div>
                    <div class="inv-items">
                        <div class="inv-row">
                            <div class="inv-rd">Web Development</div>
                            <div class="inv-rq">32h × €85</div>
                            <div class="inv-rt">€2,720</div>
                        </div>
                        <div class="inv-row">
                            <div class="inv-rd">UI/UX Design</div>
                            <div class="inv-rq">8h × €75</div>
                            <div class="inv-rt">€600</div>
                        </div>
                    </div>
                    <div class="inv-tots">
                        <div class="inv-tr"><span>Subtotal</span><span>€3,320</span></div>
                        <div class="inv-tr"><span>VAT</span><span>Reverse charge</span></div>
                        <div class="inv-tr grand"><span>Total Due</span><span>€3,320</span></div>
                    </div>
                    <div class="inv-notice">⚖ VAT reverse charge — Art. 44 Directive 2006/112/EC. Buyer accounts for
                        VAT.</div>
                    <div class="inv-status">✓ SENT</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ====== EU MARQUEE ====== -->
    <div class="eu-strip">
        <div class="eu-marquee">
            @php $strip = array_merge($euCountries, $euCountries); @endphp
            @foreach ($strip as [$flag, $name])
                <span class="eu-item"><span>{{ $flag }}</span><span>{{ $name }}</span></span>
            @endforeach
        </div>
    </div>

    <!-- ====== FEATURES ====== -->
    <section class="section" id="features">
        <div class="tcenter">
            <div class="s-tag">✦ {!! $g('features_tag') !!}</div>
            <h2 class="s-title grad">{!! $g('features_title') !!}</h2>
            <p class="s-sub center">{!! $g('features_subtitle') !!}</p>
        </div>

        {{-- Showcase rows with real app screenshots --}}
        <div class="feat-showcase">
            @foreach ($featShowcase as $fs)
                <div class="fshow {{ $fs['side'] === 'right' ? 'fshow--right' : '' }} fade-up">
                    <div class="fshow-text">
                        <div class="fc-icon {{ $fs['col'] }}">{{ $fs['icon'] }}</div>
                        <h3 class="fshow-title">{!! $g($fs['tk']) !!}</h3>
                        <p class="fshow-desc">{!! $g($fs['dk']) !!}</p>
                    </div>
                    <div class="fshow-img">
                        <figure class="app-frame">
                            <img src="{{ asset('images/app/' . $fs['img']) }}" alt="{!! strip_tags($g($fs['tk'])) !!}"
                                loading="lazy">
                        </figure>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Secondary features grid --}}
        <div class="feat-mini">
            @foreach ($featGrid as $i => $f)
                <div class="fc fade-up fd{{ $i + 1 }}">
                    <div class="fc-icon {{ $f['col'] }}">{{ $f['icon'] }}</div>
                    <div class="fc-title">{!! $g($f['tk']) !!}</div>
                    <div class="fc-desc">{!! $g($f['dk']) !!}</div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- ====== AI IMPORT ====== -->
    <div class="pay-section sfull ai-import-section" style="background:linear-gradient(135deg,#f5f3ff 0%,#ede9fe 50%,#ddd6fe 100%)">
        <div class="pay-inner" style="padding:5rem 2rem">
            <div class="fade-up">
                <div class="pay-badge" style="background:#ede9fe;color:#6d28d9">🤖 {!! $g('ai_import_tag') !!}</div>
                <h2 class="pay-t" style="color:#1e1b4b">{!! $g('ai_import_title') !!}</h2>
                <p class="pay-d" style="color:#4c1d95;opacity:.85">{!! $g('ai_import_desc') !!}</p>
                <div class="vat-rules">
                    <div class="vr">
                        <div class="vr-i icon-violet">📄</div>
                        <div>
                            <div class="vr-l">{!! $g('ai_import_r1_label') !!}</div>
                            <div class="vr-s">{!! $g('ai_import_r1_sub') !!}</div>
                        </div>
                    </div>
                    <div class="vr">
                        <div class="vr-i icon-indigo">✨</div>
                        <div>
                            <div class="vr-l">{!! $g('ai_import_r2_label') !!}</div>
                            <div class="vr-s">{!! $g('ai_import_r2_sub') !!}</div>
                        </div>
                    </div>
                    <div class="vr">
                        <div class="vr-i icon-sky">✅</div>
                        <div>
                            <div class="vr-l">{!! $g('ai_import_r3_label') !!}</div>
                            <div class="vr-s">{!! $g('ai_import_r3_sub') !!}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="fade-up fd2">
                <div class="aic-card">
                    <div class="aic-titlebar">
                        <span class="aic-dot aic-dot-red"></span>
                        <span class="aic-dot aic-dot-yellow"></span>
                        <span class="aic-dot aic-dot-green"></span>
                        <span class="aic-titlebar-label">Document Import</span>
                    </div>
                    <div class="aic-body">
                        <div class="aic-file-row">
                            <span class="aic-file-icon">📄</span>
                            <div class="aic-file-meta">
                                <div class="aic-file-name">invoice_acme_2026.pdf</div>
                                <div class="aic-file-size">284 KB · PDF</div>
                            </div>
                            <span class="aic-file-check">✓</span>
                        </div>
                        <div class="aic-processing-pill">✨ Gemini AI is reading your document…</div>
                        <div class="aic-divider-label">Extracted fields</div>
                        <div class="aic-fields">
                            <div class="aic-field-row">
                                <span class="aic-field-key">Vendor</span>
                                <span class="aic-field-val">Acme GmbH</span>
                            </div>
                            <div class="aic-field-row">
                                <span class="aic-field-key">Invoice #</span>
                                <span class="aic-field-val">INV-2026-109</span>
                            </div>
                            <div class="aic-field-row">
                                <span class="aic-field-key">Date</span>
                                <span class="aic-field-val">3 Apr 2026</span>
                            </div>
                            <div class="aic-field-row">
                                <span class="aic-field-key">Amount</span>
                                <span class="aic-field-val aic-amount">€ 2,450.00</span>
                            </div>
                            <div class="aic-field-row">
                                <span class="aic-field-key">VAT</span>
                                <span class="aic-field-val">19% · DE</span>
                            </div>
                        </div>
                    </div>
                    <div class="aic-footer">
                        <button class="aic-btn-primary">Save as Expense →</button>
                        <button class="aic-btn-ghost">Edit fields</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ====== HOW IT WORKS ====== -->
    <section class="section" id="how" style="padding-top:1rem">
        <div class="tcenter">
            <div class="s-tag">✦ {!! $g('how_tag') !!}</div>
            <h2 class="s-title">{!! $g('how_title') !!}</h2>
            <p class="s-sub center">{!! $g('how_subtitle') !!}</p>
        </div>
        <div class="steps">
            <div class="step fade-up fd1">
                <div class="step-n">1</div>
                <div class="step-t">{!! $g('s1_title') !!}</div>
                <div class="step-d">{!! $g('s1_desc') !!}</div>
            </div>
            <div class="step fade-up fd2">
                <div class="step-n">2</div>
                <div class="step-t">{!! $g('s2_title') !!}</div>
                <div class="step-d">{!! $g('s2_desc') !!}</div>
            </div>
            <div class="step fade-up fd3">
                <div class="step-n">3</div>
                <div class="step-t">{!! $g('s3_title') !!}</div>
                <div class="step-d">{!! $g('s3_desc') !!}</div>
            </div>
        </div>
    </section>

    <!-- ====== ONLINE PAYMENTS ====== -->
    <div class="pay-section sfull">
        <div class="pay-inner" style="padding:5rem 2rem">
            <div class="fade-up">
                <div class="pay-badge">💳 {!! $g('pay_tag') !!}</div>
                <h2 class="pay-t">{!! $g('pay_title') !!}</h2>
                <p class="pay-d">{!! $g('pay_desc') !!}</p>
                <div class="vat-rules">
                    <div class="vr">
                        <div class="vr-i icon-indigo">🔗</div>
                        <div>
                            <div class="vr-l">{!! $g('pay_r1_label') !!}</div>
                            <div class="vr-s">{!! $g('pay_r1_sub') !!}</div>
                        </div>
                    </div>
                    <div class="vr">
                        <div class="vr-i icon-teal">🔒</div>
                        <div>
                            <div class="vr-l">{!! $g('pay_r2_label') !!}</div>
                            <div class="vr-s">{!! $g('pay_r2_sub') !!}</div>
                        </div>
                    </div>
                    <div class="vr">
                        <div class="vr-i icon-emerald">💸</div>
                        <div>
                            <div class="vr-l">{!! $g('pay_r3_label') !!}</div>
                            <div class="vr-s">{!! $g('pay_r3_sub') !!}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="fade-up fd2">
                <div class="portal-card">
                    <div class="portal-card-hdr">
                        <div class="portal-logo">⚡ InvoiceKit</div>
                        <div class="portal-inv">INV-2026-0042</div>
                    </div>
                    <div class="portal-from">From: Your Business · Bill to: Acme GmbH</div>
                    <div class="portal-amount">€3,320.00</div>
                    <div class="portal-items">
                        <div class="portal-row">
                            <span>Web Development — 32h × €85</span>
                            <span>€2,720</span>
                        </div>
                        <div class="portal-row">
                            <span>UI/UX Design — 8h × €75</span>
                            <span>€600</span>
                        </div>
                    </div>
                    <a class="portal-pay-btn">💳 Pay Now — €3,320.00</a>
                    <div class="portal-divider">— or pay by bank transfer —</div>
                    <div class="portal-iban">
                        <div class="portal-iban-label">Bank Transfer (free)</div>
                        <div class="portal-iban-val">IBAN: DE89 3704 0044 0032 1014 00</div>
                    </div>
                    <div class="portal-fee">2% platform fee on card payments · Bank transfers always free</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ====== EU VAT ENGINE ====== -->
    <div class="vat-section sfull">
        <div class="vat-inner" style="padding:5rem 2rem">
            <div class="fade-up">
                <div class="vat-badge">🇪🇺 {!! $g('vat_tag') !!}</div>
                <h2 class="vat-t">{!! $g('vat_title') !!}</h2>
                <p class="vat-d">{!! $g('vat_desc') !!}</p>
                <div class="vat-rules">
                    <div class="vr">
                        <div class="vr-i icon-teal">🏠</div>
                        <div>
                            <div class="vr-l">{!! $g('vat_r1_label') !!}</div>
                            <div class="vr-s">{!! $g('vat_r1_sub') !!}</div>
                        </div>
                    </div>
                    <div class="vr">
                        <div class="vr-i icon-indigo">🔄</div>
                        <div>
                            <div class="vr-l">{!! $g('vat_r2_label') !!}</div>
                            <div class="vr-s">{!! $g('vat_r2_sub') !!}</div>
                        </div>
                    </div>
                    <div class="vr">
                        <div class="vr-i icon-sky">🛒</div>
                        <div>
                            <div class="vr-l">{!! $g('vat_r3_label') !!}</div>
                            <div class="vr-s">{!! $g('vat_r3_sub') !!}</div>
                        </div>
                    </div>
                    <div class="vr">
                        <div class="vr-i icon-amber">🌍</div>
                        <div>
                            <div class="vr-l">{!! $g('vat_r4_label') !!}</div>
                            <div class="vr-s">{!! $g('vat_r4_sub') !!}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="fade-up fd2">
                <div class="vat-rt">{!! $g('vat_rates_title') !!}</div>
                <div class="vat-grid">
                    @foreach ($vatRates as [$flag, $code, $rate])
                        <div class="vat-item">
                            <span class="vat-fl">{{ $flag }}</span>
                            <span class="vat-co">{{ $code }}</span>
                            <span class="vat-pc">{{ $rate }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- ====== PRICING ====== -->
    <section class="section" id="pricing">
        <div class="tcenter">
            <div class="s-tag">✦ {!! $g('pricing_tag') !!}</div>
            <h2 class="s-title">{!! $g('pricing_title') !!}</h2>
            <p class="s-sub center">{!! $g('pricing_subtitle') !!}</p>
        </div>
        <div class="pricing-grid">
            <div class="pc fade-up fd1">
                <div class="p-name">{!! $g('p_free_name') !!}</div>
                <div class="p-price">{!! $g('p_free_price') !!}</div>
                <div class="p-period">{!! $g('p_free_period') !!}</div>
                <div class="p-desc">{!! $g('p_free_desc') !!}</div>
                <ul class="p-feats">
                    <li>{!! $g('p_free_l1') !!}</li>
                    <li>{!! $g('p_free_l2') !!}</li>
                    <li>{!! $g('p_free_l3') !!}</li>
                    <li>{!! $g('p_free_l4') !!}</li>
                    <li>{!! $g('p_free_l5') !!}</li>
                </ul>
                <a href="/register" class="p-cta p-out">{!! $g('p_free_cta') !!}</a>
            </div>
            <div class="pc feat fade-up fd2">
                <div class="pop-badge">{!! $g('p_popular') !!}</div>
                <div class="p-name">{!! $g('p_starter_name') !!}</div>
                <div class="p-price">{!! $g('p_starter_price') !!}</div>
                <div class="p-period">{!! $g('p_starter_period') !!}</div>
                <div class="p-desc">{!! $g('p_starter_desc') !!}</div>
                <ul class="p-feats">
                    <li>{!! $g('p_starter_l1') !!}</li>
                    <li>{!! $g('p_starter_l2') !!}</li>
                    <li>{!! $g('p_starter_l3') !!}</li>
                    <li>{!! $g('p_starter_l4') !!}</li>
                    <li>{!! $g('p_starter_l5') !!}</li>
                    <li>{!! $g('p_starter_l6') !!}</li>
                </ul>
                <a href="/register" class="p-cta p-pri">{!! $g('p_starter_cta') !!}</a>
            </div>
            <div class="pc fade-up fd3">
                <div class="p-name">{!! $g('p_pro_name') !!}</div>
                <div class="p-price">{!! $g('p_pro_price') !!}</div>
                <div class="p-period">{!! $g('p_pro_period') !!}</div>
                <div class="p-desc">{!! $g('p_pro_desc') !!}</div>
                <ul class="p-feats">
                    <li>{!! $g('p_pro_l1') !!}</li>
                    <li>{!! $g('p_pro_l2') !!}</li>
                    <li>{!! $g('p_pro_l3') !!}</li>
                    <li>{!! $g('p_pro_l4') !!}</li>
                    <li>{!! $g('p_pro_l5') !!}</li>
                    <li>{!! $g('p_pro_l6') !!}</li>
                    <li>{!! $g('p_pro_l7') !!}</li>
                </ul>
                <a href="/register" class="p-cta p-out">{!! $g('p_pro_cta') !!}</a>
            </div>
        </div>
    </section>

    <!-- ====== FAQ ====== -->
    <section class="section" id="faq">
        <div class="tcenter">
            <div class="s-tag">✦ {!! $g('faq_tag') !!}</div>
            <h2 class="s-title">{!! $g('faq_title') !!}</h2>
        </div>
        <div class="faq-list">
            @foreach (range(1, 6) as $n)
                <details class="fq fade-up">
                    <summary class="fq-q">{!! $g("faq{$n}_q") !!}</summary>
                    <div class="fq-a">{!! $g("faq{$n}_a") !!}</div>
                </details>
            @endforeach
        </div>
    </section>

    <!-- ====== FINAL CTA ====== -->
    <div class="cta-wrap sfull">
        <div class="section" style="padding-top:5rem;padding-bottom:5rem">
            <div class="cta-t grad">{!! $gnl('cta_title') !!}</div>
            <p class="cta-s">{!! $g('cta_subtitle') !!}</p>
            <a href="/register" class="btn btn-primary btn-lg">{!! $g('cta_button') !!}</a>
        </div>
    </div>

    <!-- ====== FOOTER ====== -->
    <footer class="footer">
        <div>
            <div class="footer-brand">⚡ InvoiceKit</div>
            <div class="footer-tl">{!! $g('footer_tagline') !!}</div>
        </div>
        <div class="footer-links">
            <a href="/privacy">{!! $g('footer_privacy') !!}</a>
            <a href="/terms">{!! $g('footer_terms') !!}</a>
        </div>
        <div class="footer-copy">{!! $g('footer_copy') !!}</div>
    </footer>

    <script>
        (function() {
            // Sticky nav
            var nav = document.getElementById('topnav');
            window.addEventListener('scroll', function() {
                nav.classList.toggle('scrolled', window.scrollY > 20);
            }, {
                passive: true
            });

            // Language dropdown
            var toggle = document.getElementById('langToggle');
            var dd = document.getElementById('langDd');
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                var open = dd.classList.toggle('open');
                toggle.classList.toggle('open', open);
                toggle.setAttribute('aria-expanded', String(open));
            });
            document.addEventListener('click', function() {
                dd.classList.remove('open');
                toggle.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
            });

            // Scroll fade-in animations
            if ('IntersectionObserver' in window) {
                var io = new IntersectionObserver(function(entries) {
                    entries.forEach(function(e) {
                        if (e.isIntersecting) {
                            e.target.classList.add('visible');
                            io.unobserve(e.target);
                        }
                    });
                }, {
                    threshold: 0.12
                });
                document.querySelectorAll('.fade-up').forEach(function(el) {
                    io.observe(el);
                });
            } else {
                document.querySelectorAll('.fade-up').forEach(function(el) {
                    el.classList.add('visible');
                });
            }
        })();
    </script>
</body>

</html>
