<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Arutala') — E-Voting Arutala IAIC Pasuruan</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "outline": "#837561",
                        "inverse-on-surface": "#f7f0e6",
                        "secondary-fixed": "#ffe173",
                        "primary-fixed": "#ffdeac",
                        "on-primary": "#ffffff",
                        "secondary": "#705d00",
                        "on-surface-variant": "#514533",
                        "on-error-container": "#93000a",
                        "surface-dim": "#e0d9d0",
                        "on-secondary": "#ffffff",
                        "surface-container-highest": "#e8e1d8",
                        "on-surface": "#1e1b16",
                        "on-primary-fixed-variant": "#5f4100",
                        "outline-variant": "#d5c4ad",
                        "surface-container-high": "#eee7de",
                        "on-secondary-fixed-variant": "#554500",
                        "tertiary-container": "#d1aa00",
                        "background": "#fff8f1",
                        "error-container": "#ffdad6",
                        "tertiary": "#735c00",
                        "surface-container-lowest": "#ffffff",
                        "tertiary-fixed-dim": "#eec200",
                        "surface-container": "#f4ede3",
                        "secondary-container": "#fdd73b",
                        "on-primary-container": "#593c00",
                        "surface-bright": "#fff8f1",
                        "primary-fixed-dim": "#ffba36",
                        "primary": "#7e5700",
                        "inverse-surface": "#33302a",
                        "on-primary-fixed": "#281900",
                        "on-tertiary-fixed": "#231b00",
                        "on-secondary-container": "#715d00",
                        "on-tertiary-container": "#504000",
                        "inverse-primary": "#ffba36",
                        "on-tertiary": "#ffffff",
                        "secondary-fixed-dim": "#e8c426",
                        "tertiary-fixed": "#ffe083",
                        "surface": "#fff8f1",
                        "primary-container": "#e5a100",
                        "on-error": "#ffffff",
                        "surface-variant": "#e8e1d8",
                        "error": "#ba1a1a",
                        "on-tertiary-fixed-variant": "#574500",
                        "on-background": "#1e1b16",
                        "surface-tint": "#7e5700",
                        "on-secondary-fixed": "#221b00",
                        "surface-container-low": "#faf3e9"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "sm": "0.5rem",
                        "gutter": "1rem",
                        "md": "1rem",
                        "margin-desktop": "5rem",
                        "xs": "0.25rem",
                        "xl": "2.5rem",
                        "margin-mobile": "1.25rem",
                        "lg": "1.5rem"
                    },
                    "fontFamily": {
                        "h1-mobile": ["Baloo 2"],
                        "label-caps": ["Baloo 2"],
                        "display-mobile": ["Baloo 2"],
                        "h1": ["Baloo 2"],
                        "body-lg": ["Baloo 2"],
                        "body-md": ["Baloo 2"],
                        "display": ["Baloo 2"],
                        "h2": ["Baloo 2"]
                    },
                    "fontSize": {
                        "h1-mobile": ["28px", { "lineHeight": "1.2", "fontWeight": "700" }],
                        "label-caps": ["14px", { "lineHeight": "1.0", "letterSpacing": "0.05em", "fontWeight": "600" }],
                        "display-mobile": ["42px", { "lineHeight": "1.1", "fontWeight": "800" }],
                        "h1": ["36px", { "lineHeight": "1.2", "fontWeight": "700" }],
                        "body-lg": ["18px", { "lineHeight": "1.7", "fontWeight": "400" }],
                        "body-md": ["16px", { "lineHeight": "1.7", "fontWeight": "400" }],
                        "display": ["64px", { "lineHeight": "1.1", "letterSpacing": "-0.02em", "fontWeight": "800" }],
                        "h2": ["24px", { "lineHeight": "1.3", "fontWeight": "600" }]
                    }
                }
            }
        }
    </script>

    <style>
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 88px;
        }
        body { font-family: 'Baloo 2', system-ui, sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined.fill {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .glow-shadow {
            box-shadow: 0 10px 40px -10px rgba(229, 161, 0, 0.15);
        }
        .card-hover:hover {
            box-shadow: 0 15px 50px -10px rgba(229, 161, 0, 0.25);
            border-color: #e5a100;
            transform: translateY(-2px);
        }
        /* Desktop nav link */
        .nav-link {
            color: #5C5648;
            font-weight: 500;
            padding-bottom: 4px;
            border-bottom: 2px solid transparent;
            transition: color .2s, border-color .2s;
        }
        .nav-link:hover { color: #E5A100; }
        .nav-link.is-active {
            color: #E5A100;
            font-weight: 700;
            border-bottom-color: #E5A100;
        }
        /* Mobile bottom nav link */
        .m-nav-link {
            color: #5C5648;
            transition: color .2s;
        }
        .m-nav-link:hover { color: #E5A100; }
        .m-nav-link.is-active { color: #E5A100; }
        .m-nav-link.is-active .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
        }
        /* Form input (used by login & forms across app) */
        .arutala-input {
            width: 100%;
            padding: 14px 16px 14px 52px;
            border: 1px solid #E5E0D5;
            border-radius: 12px;
            color: #2D2A24;
            background: #FFFFFF;
            font-size: 15px;
            line-height: 1.4;
            transition: border-color .2s, box-shadow .2s;
        }
        .arutala-input::placeholder { color: #8E8676; }
        .arutala-input:focus {
            outline: none;
            border-color: #E5A100;
            box-shadow: 0 0 0 4px rgba(229,161,0,0.1);
        }
        /* Icon inside input — vertically centered */
        .input-icon {
            position: absolute;
            left: 16px;
            top: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            pointer-events: none;
            color: #8E8676;
            font-size: 20px;
        }
        /* Primary button */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #E5A100;
            color: #FFFFFF;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 12px;
            transition: background .2s;
            box-shadow: 0 8px 24px -8px rgba(229,161,0,0.4);
        }
        .btn-primary:hover { background: #D97706; }
    </style>

    @stack('head')
</head>
<body class="@yield('body-class', 'bg-[#FFFCF5] text-[#2D2A24] antialiased')">

    @yield('body')

    @stack('scripts')
</body>
</html>
