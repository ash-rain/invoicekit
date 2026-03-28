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

    $features = [
        ['icon' => '⏱', 'col' => 'icon-indigo', 'tk' => 'f1_title', 'dk' => 'f1_desc'],
        ['icon' => '📄', 'col' => 'icon-sky', 'tk' => 'f2_title', 'dk' => 'f2_desc'],
        ['icon' => '🇪🇺', 'col' => 'icon-teal', 'tk' => 'f3_title', 'dk' => 'f3_desc'],
        ['icon' => '👥', 'col' => 'icon-violet', 'tk' => 'f4_title', 'dk' => 'f4_desc'],
        ['icon' => '�', 'col' => 'icon-amber', 'tk' => 'f5_title', 'dk' => 'f5_desc'],
        ['icon' => '🔗', 'col' => 'icon-rose', 'tk' => 'f6_title', 'dk' => 'f6_desc'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InvoiceKit — {!! strip_tags($gnl('hero_headline', 'EU Invoicing for Freelancers')) !!}</title>
    <meta name="description" content="{!! $g('hero_subheadline') !!}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet">
    <style>
        /* ===== RESET ===== */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        html {
            scroll-behavior: smooth
        }

        :root {
            --bg: #09090f;
            --bg1: #0d0d18;
            --card: rgba(255, 255, 255, .03);
            --card-h: rgba(255, 255, 255, .06);
            --border: rgba(255, 255, 255, .08);
            --border-h: rgba(255, 255, 255, .16);
            --text: #f1f5f9;
            --muted: rgba(241, 245, 249, .62);
            --subtle: rgba(241, 245, 249, .35);
            --indigo: #6366f1;
            --sky: #0ea5e9;
            --teal: #14b8a6;
            --amber: #f59e0b;
            --rose: #f43f5e;
            --violet: #8b5cf6;
            --r: 12px;
            --rl: 20px;
            --tr: .22s ease;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', system-ui, sans-serif;
            line-height: 1.7;
            overflow-x: hidden
        }

        a {
            text-decoration: none;
            color: inherit
        }

        ::-webkit-scrollbar {
            width: 5px
        }

        ::-webkit-scrollbar-track {
            background: var(--bg)
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .12);
            border-radius: 3px
        }

        /* ===== NAVBAR ===== */
        .nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 200;
            height: 72px;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background var(--tr), border-color var(--tr);
            border-bottom: 1px solid transparent
        }

        .nav.scrolled {
            background: rgba(9, 9, 15, .9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom-color: var(--border)
        }

        .nav-logo {
            font-size: 1.2rem;
            font-weight: 900;
            letter-spacing: -.03em;
            background: linear-gradient(135deg, #fff 20%, var(--indigo));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: .35rem
        }

        .nav-logo-icon {
            -webkit-text-fill-color: initial;
            font-size: 1.1rem
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem
        }

        .nav-links a {
            font-size: .875rem;
            font-weight: 500;
            color: var(--muted);
            transition: color var(--tr)
        }

        .nav-links a:hover {
            color: var(--text)
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: .75rem
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-weight: 600;
            font-size: .875rem;
            padding: .55rem 1.2rem;
            border-radius: var(--r);
            cursor: pointer;
            transition: opacity var(--tr), transform var(--tr);
            border: none;
            text-decoration: none
        }

        .btn-ghost {
            background: none;
            color: var(--muted);
            transition: color var(--tr), background var(--tr)
        }

        .btn-ghost:hover {
            color: var(--text);
            background: var(--card-h)
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--indigo), var(--sky));
            color: #fff
        }

        .btn-primary:hover {
            opacity: .9;
            transform: translateY(-1px)
        }

        .btn-lg {
            padding: .85rem 2.1rem;
            font-size: 1rem;
            border-radius: 14px
        }

        .btn-out-lg {
            padding: .85rem 2.1rem;
            font-size: 1rem;
            border-radius: 14px;
            background: none;
            border: 1px solid var(--border-h);
            color: var(--text);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: .35rem
        }

        .btn-out-lg:hover {
            border-color: var(--indigo);
            background: rgba(99, 102, 241, .07)
        }

        /* ===== LANG SWITCHER ===== */
        .lang-wrap {
            position: relative
        }

        .lang-toggle {
            display: flex;
            align-items: center;
            gap: .4rem;
            padding: .45rem .8rem;
            border-radius: 8px;
            background: var(--card);
            border: 1px solid var(--border);
            font-size: .82rem;
            color: var(--text);
            cursor: pointer;
            transition: border-color var(--tr);
            user-select: none
        }

        .lang-toggle:hover {
            border-color: var(--border-h)
        }

        .lang-toggle .chevron {
            font-size: .65rem;
            opacity: .45;
            transition: transform .18s
        }

        .lang-toggle.open .chevron {
            transform: rotate(180deg)
        }

        .lang-dd {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: #131320;
            border: 1px solid var(--border);
            border-radius: var(--r);
            min-width: 176px;
            max-height: 340px;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .55);
            z-index: 300
        }

        .lang-dd.open {
            display: block
        }

        .lang-opt {
            display: flex;
            align-items: center;
            gap: .55rem;
            padding: .48rem 1rem;
            font-size: .83rem;
            color: var(--muted);
            cursor: pointer;
            transition: background var(--tr), color var(--tr);
            text-decoration: none
        }

        .lang-opt:hover {
            background: var(--card-h);
            color: var(--text)
        }

        .lang-opt.active {
            color: var(--indigo)
        }

        /* ===== HERO ===== */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 140px 2rem 80px;
            position: relative;
            overflow: hidden
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            pointer-events: none
        }

        .hg1 {
            position: absolute;
            width: 800px;
            height: 800px;
            top: -200px;
            left: -200px;
            background: radial-gradient(circle, rgba(99, 102, 241, .14) 0%, transparent 65%)
        }

        .hg2 {
            position: absolute;
            width: 700px;
            height: 700px;
            bottom: -100px;
            right: -150px;
            background: radial-gradient(circle, rgba(14, 165, 233, .10) 0%, transparent 65%)
        }

        .hg3 {
            position: absolute;
            width: 500px;
            height: 500px;
            top: 30%;
            right: 20%;
            background: radial-gradient(circle, rgba(20, 184, 166, .08) 0%, transparent 65%)
        }

        .hero-grid {
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(255, 255, 255, .025) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, .025) 1px, transparent 1px);
            background-size: 64px 64px;
            mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 30%, transparent 100%)
        }

        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3.5rem;
            align-items: center;
            position: relative;
            z-index: 1
        }

        .hero-left {
            animation: hIn .8s ease both
        }

        .hero-right {
            animation: hInR .8s .2s ease both
        }

        @keyframes hIn {
            from {
                opacity: 0;
                transform: translateY(32px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        @keyframes hInR {
            from {
                opacity: 0;
                transform: translateX(32px)
            }

            to {
                opacity: 1;
                transform: translateX(0)
            }
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            background: rgba(99, 102, 241, .12);
            border: 1px solid rgba(99, 102, 241, .28);
            color: #a5b4fc;
            padding: .32rem .9rem .32rem .7rem;
            border-radius: 100px;
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .02em;
            margin-bottom: 1.5rem
        }

        .hero-title {
            font-size: clamp(2.4rem, 4.5vw, 3.8rem);
            font-weight: 900;
            letter-spacing: -.035em;
            line-height: 1.1;
            margin-bottom: 1.4rem
        }

        .grad {
            background: linear-gradient(140deg, #fff 0%, #c7d2fe 30%, #7dd3fc 65%, #5eead4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text
        }

        .hero-sub {
            font-size: 1.05rem;
            line-height: 1.8;
            color: var(--muted);
            max-width: 500px;
            margin-bottom: 2.25rem
        }

        .hero-cta {
            display: flex;
            gap: .9rem;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 2rem
        }

        .hero-trust {
            display: flex;
            align-items: center;
            gap: .55rem;
            flex-wrap: wrap
        }

        .t-item {
            font-size: .78rem;
            color: var(--subtle)
        }

        .t-dot {
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: var(--subtle)
        }

        /* ===== INVOICE MOCKUP ===== */
        .inv-card {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 20px;
            padding: 1.75rem;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 0 0 1px rgba(99, 102, 241, .14), 0 40px 80px rgba(0, 0, 0, .45), inset 0 1px 0 rgba(255, 255, 255, .07);
            position: relative;
            overflow: hidden;
            animation: float 5.5s ease-in-out infinite
        }

        .inv-card::before {
            content: '';
            position: absolute;
            top: -60%;
            left: -40%;
            width: 200%;
            height: 200%;
            background: radial-gradient(ellipse at 55% 0%, rgba(99, 102, 241, .09) 0%, transparent 60%);
            pointer-events: none
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(.1deg)
            }

            40% {
                transform: translateY(-8px) rotate(-.2deg)
            }

            70% {
                transform: translateY(-4px) rotate(.15deg)
            }
        }

        .inv-hdr {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border)
        }

        .inv-lg {
            font-size: .95rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, var(--indigo));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text
        }

        .inv-num {
            font-size: .68rem;
            font-family: 'Courier New', monospace;
            background: rgba(99, 102, 241, .12);
            border: 1px solid rgba(99, 102, 241, .22);
            color: #a5b4fc;
            padding: .18rem .5rem;
            border-radius: 4px
        }

        .inv-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .85rem;
            margin-bottom: 1.1rem
        }

        .inv-lbl {
            font-size: .62rem;
            font-weight: 700;
            color: var(--subtle);
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: .18rem
        }

        .inv-val {
            font-size: .82rem;
            font-weight: 500;
            color: var(--text)
        }

        .inv-vat-chip {
            font-size: .7rem;
            color: var(--teal)
        }

        .inv-items {
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: .9rem
        }

        .inv-row {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: .7rem;
            padding: .55rem .75rem;
            font-size: .75rem;
            border-bottom: 1px solid var(--border);
            align-items: center
        }

        .inv-row:last-child {
            border-bottom: none
        }

        .inv-rd {
            color: var(--muted)
        }

        .inv-rq {
            color: var(--subtle);
            text-align: right
        }

        .inv-rt {
            font-weight: 600;
            text-align: right
        }

        .inv-tots {
            padding: .2rem 0
        }

        .inv-tr {
            display: flex;
            justify-content: space-between;
            font-size: .75rem;
            padding: .2rem 0;
            color: var(--muted)
        }

        .inv-tr.grand {
            color: var(--text);
            font-weight: 700;
            font-size: .88rem;
            padding-top: .5rem;
            border-top: 1px solid var(--border);
            margin-top: .25rem
        }

        .inv-notice {
            font-size: .63rem;
            color: var(--teal);
            background: rgba(20, 184, 166, .08);
            border: 1px solid rgba(20, 184, 166, .15);
            border-radius: 6px;
            padding: .38rem .6rem;
            margin-top: .7rem;
            line-height: 1.5
        }

        .inv-status {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .67rem;
            font-weight: 700;
            color: #4ade80;
            background: rgba(74, 222, 128, .1);
            border: 1px solid rgba(74, 222, 128, .2);
            padding: .18rem .5rem;
            border-radius: 100px;
            margin-top: .7rem
        }

        /* ===== EU MARQUEE ===== */
        .eu-strip {
            padding: 1.25rem 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            overflow: hidden;
            background: rgba(255, 255, 255, .01)
        }

        .eu-marquee {
            display: flex;
            gap: 2.5rem;
            white-space: nowrap;
            animation: marquee 28s linear infinite
        }

        .eu-marquee:hover {
            animation-play-state: paused
        }

        @keyframes marquee {
            from {
                transform: translateX(0)
            }

            to {
                transform: translateX(-50%)
            }
        }

        .eu-item {
            display: inline-flex;
            align-items: center;
            gap: .38rem;
            font-size: .78rem;
            color: var(--subtle);
            flex-shrink: 0
        }

        .eu-item span:first-child {
            font-size: 1rem
        }

        /* ===== LAYOUT ===== */
        .section {
            padding: 6rem 2rem;
            max-width: 1200px;
            margin: 0 auto
        }

        .sfull {
            padding: 6rem 2rem
        }

        .s-tag {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .72rem;
            font-weight: 700;
            color: var(--indigo);
            letter-spacing: .1em;
            text-transform: uppercase;
            margin-bottom: .65rem
        }

        .s-title {
            font-size: clamp(1.75rem, 3.2vw, 2.65rem);
            font-weight: 800;
            letter-spacing: -.025em;
            line-height: 1.2;
            margin-bottom: .9rem
        }

        .s-sub {
            font-size: 1rem;
            color: var(--muted);
            line-height: 1.75
        }

        .tcenter {
            text-align: center
        }

        .s-sub.center {
            max-width: 580px;
            margin: 0 auto 3.25rem
        }

        /* ===== FEATURES ===== */
        .feat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.15rem;
            margin-top: 3.25rem
        }

        .fc {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--rl);
            padding: 1.65rem;
            transition: border-color var(--tr), background var(--tr), transform var(--tr)
        }

        .fc:hover {
            border-color: var(--border-h);
            background: var(--card-h);
            transform: translateY(-3px)
        }

        .fc-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin-bottom: .9rem
        }

        .icon-indigo {
            background: rgba(99, 102, 241, .15)
        }

        .icon-sky {
            background: rgba(14, 165, 233, .15)
        }

        .icon-teal {
            background: rgba(20, 184, 166, .15)
        }

        .icon-amber {
            background: rgba(245, 158, 11, .15)
        }

        .icon-rose {
            background: rgba(244, 63, 94, .15)
        }

        .icon-violet {
            background: rgba(139, 92, 246, .15)
        }

        .fc-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: .45rem
        }

        .fc-desc {
            font-size: .855rem;
            color: var(--muted);
            line-height: 1.65
        }

        /* ===== STEPS ===== */
        .steps {
            position: relative;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-top: 3.25rem
        }

        .steps::before {
            content: '';
            position: absolute;
            top: 30px;
            left: calc(16.6% + .5rem);
            right: calc(16.6% + .5rem);
            height: 1px;
            background: linear-gradient(90deg, var(--indigo), var(--sky), var(--teal));
            opacity: .28
        }

        .step {
            text-align: center;
            padding: 0 1rem
        }

        .step-n {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--indigo), var(--sky));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 900;
            color: #fff;
            margin: 0 auto 1.15rem;
            position: relative;
            z-index: 1;
            box-shadow: 0 0 28px rgba(99, 102, 241, .35)
        }

        .step-t {
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: .45rem
        }

        .step-d {
            font-size: .855rem;
            color: var(--muted);
            line-height: 1.65
        }

        /* ===== VAT ===== */
        .vat-section {
            background: var(--bg1);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border)
        }

        .vat-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: start
        }

        .vat-badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: #a5b4fc;
            background: rgba(99, 102, 241, .1);
            padding: .28rem .7rem;
            border-radius: 100px;
            margin-bottom: .9rem
        }

        .vat-t {
            font-size: clamp(1.6rem, 2.8vw, 2.2rem);
            font-weight: 800;
            letter-spacing: -.025em;
            margin-bottom: .75rem;
            line-height: 1.25
        }

        .vat-d {
            color: var(--muted);
            margin-bottom: 1.75rem;
            line-height: 1.75;
            font-size: .975rem
        }

        .vat-rules {
            display: flex;
            flex-direction: column;
            gap: .7rem
        }

        .vr {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            padding: .7rem .9rem;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px
        }

        .vr-i {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .88rem;
            flex-shrink: 0;
            margin-top: .05rem
        }

        .vr-l {
            font-size: .85rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: .1rem
        }

        .vr-s {
            font-size: .78rem;
            color: var(--muted)
        }

        .vat-rt {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--subtle);
            margin-bottom: .85rem
        }

        .vat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: .55rem
        }

        .vat-item {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: .55rem .5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .12rem;
            text-align: center
        }

        .vat-fl {
            font-size: 1.15rem
        }

        .vat-co {
            font-size: .6rem;
            color: var(--subtle);
            font-weight: 700;
            letter-spacing: .04em
        }

        .vat-pc {
            font-size: .8rem;
            font-weight: 700;
            color: var(--teal)
        }

        /* ===== PRICING ===== */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            max-width: 960px;
            margin: 3.25rem auto 0
        }

        .pc {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--rl);
            padding: 2rem;
            position: relative;
            transition: transform var(--tr)
        }

        .pc:hover {
            transform: translateY(-5px)
        }

        .pc.feat {
            background: linear-gradient(180deg, rgba(99, 102, 241, .12) 0%, rgba(14, 165, 233, .06) 100%);
            border-color: rgba(99, 102, 241, .42);
            box-shadow: 0 0 50px rgba(99, 102, 241, .16)
        }

        .pop-badge {
            position: absolute;
            top: -13px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, var(--indigo), var(--sky));
            color: #fff;
            font-size: .68rem;
            font-weight: 700;
            padding: .22rem .8rem;
            border-radius: 100px;
            letter-spacing: .06em;
            text-transform: uppercase;
            white-space: nowrap
        }

        .p-name {
            font-size: .72rem;
            font-weight: 700;
            color: var(--subtle);
            text-transform: uppercase;
            letter-spacing: .1em;
            margin-bottom: .45rem
        }

        .p-price {
            font-size: 2.7rem;
            font-weight: 900;
            letter-spacing: -.04em;
            line-height: 1;
            margin-bottom: .2rem
        }

        .p-period {
            font-size: .77rem;
            color: var(--subtle);
            margin-bottom: .7rem
        }

        .p-desc {
            font-size: .855rem;
            color: var(--muted);
            margin-bottom: 1.35rem;
            padding-bottom: 1.35rem;
            border-bottom: 1px solid var(--border)
        }

        .p-feats {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: .55rem;
            margin-bottom: 1.85rem
        }

        .p-feats li {
            display: flex;
            align-items: center;
            gap: .55rem;
            font-size: .855rem;
            color: var(--muted)
        }

        .p-feats li::before {
            content: '✓';
            color: var(--teal);
            font-weight: 700;
            flex-shrink: 0
        }

        .p-cta {
            display: block;
            width: 100%;
            padding: .7rem 1.5rem;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: opacity var(--tr), transform var(--tr), border-color var(--tr);
            text-decoration: none
        }

        .p-pri {
            background: linear-gradient(135deg, var(--indigo), var(--sky));
            color: #fff
        }

        .p-pri:hover {
            opacity: .9;
            transform: translateY(-1px)
        }

        .p-out {
            background: none;
            border: 1px solid var(--border-h);
            color: var(--text)
        }

        .p-out:hover {
            border-color: var(--indigo);
            background: rgba(99, 102, 241, .06)
        }

        /* ===== FAQ ===== */
        .faq-list {
            max-width: 720px;
            margin: 3.25rem auto 0;
            display: flex;
            flex-direction: column;
            gap: .7rem
        }

        details.fq {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r);
            overflow: hidden;
            transition: border-color var(--tr)
        }

        details.fq[open] {
            border-color: rgba(99, 102, 241, .3)
        }

        summary.fq-q {
            padding: 1.2rem 1.5rem;
            cursor: pointer;
            font-size: .955rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            list-style: none;
            user-select: none;
            gap: 1rem
        }

        summary.fq-q::-webkit-details-marker {
            display: none
        }

        summary.fq-q::after {
            content: '+';
            color: var(--indigo);
            font-size: 1.2rem;
            font-weight: 300;
            flex-shrink: 0
        }

        details[open] summary.fq-q::after {
            content: '−'
        }

        .fq-a {
            padding: 0 1.5rem 1.2rem;
            font-size: .865rem;
            color: var(--muted);
            line-height: 1.72
        }

        /* ===== CTA ===== */
        .cta-wrap {
            text-align: center;
            border-top: 1px solid rgba(99, 102, 241, .18);
            background: linear-gradient(180deg, rgba(99, 102, 241, .07) 0%, transparent 100%)
        }

        .cta-t {
            font-size: clamp(1.9rem, 4vw, 3.25rem);
            font-weight: 900;
            letter-spacing: -.03em;
            margin-bottom: .9rem;
            line-height: 1.15
        }

        .cta-s {
            font-size: 1rem;
            color: var(--muted);
            max-width: 460px;
            margin: 0 auto 2.25rem;
            line-height: 1.7
        }

        /* ===== FOOTER ===== */
        .footer {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.75rem 2rem;
            border-top: 1px solid var(--border);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem
        }

        .footer-brand {
            font-size: 1rem;
            font-weight: 900;
            letter-spacing: -.02em;
            background: linear-gradient(135deg, #fff 20%, var(--indigo));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text
        }

        .footer-tl {
            font-size: .77rem;
            color: var(--subtle);
            margin-top: .18rem
        }

        .footer-links {
            display: flex;
            gap: 1.35rem;
            flex-wrap: wrap
        }

        .footer-links a {
            font-size: .77rem;
            color: var(--subtle);
            transition: color var(--tr)
        }

        .footer-links a:hover {
            color: var(--text)
        }

        .footer-copy {
            font-size: .72rem;
            color: var(--subtle)
        }

        /* ===== ANIMATIONS ===== */
        .fade-up {
            opacity: 0;
            transform: translateY(22px);
            transition: opacity .55s ease, transform .55s ease
        }

        .fade-up.visible {
            opacity: 1;
            transform: translateY(0)
        }

        .fd1 {
            transition-delay: .08s
        }

        .fd2 {
            transition-delay: .16s
        }

        .fd3 {
            transition-delay: .24s
        }

        .fd4 {
            transition-delay: .32s
        }

        .fd5 {
            transition-delay: .40s
        }

        .fd6 {
            transition-delay: .48s
        }

        /* ===== RESPONSIVE ===== */
        @media(max-width:960px) {
            .hero-container {
                grid-template-columns: 1fr;
                gap: 3rem
            }

            .inv-card {
                max-width: 440px;
                margin: 0 auto
            }

            .feat-grid {
                grid-template-columns: repeat(2, 1fr)
            }

            .steps {
                grid-template-columns: 1fr;
                gap: 1.5rem
            }

            .steps::before {
                display: none
            }

            .vat-inner {
                grid-template-columns: 1fr
            }

            .pricing-grid {
                grid-template-columns: 1fr;
                max-width: 400px
            }

            .nav-links {
                display: none
            }
        }

        @media(max-width:640px) {
            .hero {
                padding: 120px 1.25rem 60px
            }

            .feat-grid {
                grid-template-columns: 1fr
            }

            .section {
                padding: 4rem 1.25rem
            }

            .sfull {
                padding: 4rem 1.25rem
            }

            .footer {
                flex-direction: column;
                text-align: center
            }

            .footer-links {
                justify-content: center
            }
        }
    </style>

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
        <a href="/" class="nav-logo"><span class="nav-logo-icon">⚡</span>InvoiceKit</a>
        <div class="nav-links">
            <a href="#features">{!! $g('nav_features') !!}</a>
            <a href="#pricing">{!! $g('nav_pricing') !!}</a>
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
        <div class="feat-grid">
            @foreach ($features as $i => $f)
                <div class="fc fade-up fd{{ $i + 1 }}">
                    <div class="fc-icon {{ $f['col'] }}">{{ $f['icon'] }}</div>
                    <div class="fc-title">{!! $g($f['tk']) !!}</div>
                    <div class="fc-desc">{!! $g($f['dk']) !!}</div>
                </div>
            @endforeach
        </div>
    </section>

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
            @foreach (range(1, 5) as $n)
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
