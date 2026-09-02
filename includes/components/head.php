<?php
/**
 * Dabberha (دبرها) - Unified Head Component
 * Location: includes/components/head.php
 *
 * @param array $params [
 *     'title' => string,
 *     'extra_head' => string (optional HTML)
 * ]
 */

function renderHead(array $params = []): void {
    $title = $params['title'] ?? 'دبرها | Dabberha';
    $extra_head = $params['extra_head'] ?? '';
    ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>

    <!-- Google Fonts: Tajawal (Arabic) & Plus Jakarta Sans (English) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Tajawal:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Google Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Tailwind CSS with Forms & Container Queries -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Tajawal', '"Plus Jakarta Sans"', 'sans-serif'],
                        arabic: ['Tajawal', 'sans-serif'],
                        latin: ['"Plus Jakarta Sans"', 'sans-serif'],
                        "display-lg": ["Tajawal", '"Plus Jakarta Sans"', "sans-serif"],
                        "headline-lg": ["Tajawal", '"Plus Jakarta Sans"', "sans-serif"],
                        "headline-md": ["Tajawal", '"Plus Jakarta Sans"', "sans-serif"],
                        "body-lg": ["Tajawal", '"Plus Jakarta Sans"', "sans-serif"],
                        "body-md": ["Tajawal", '"Plus Jakarta Sans"', "sans-serif"],
                        "label-lg": ["Tajawal", '"Plus Jakarta Sans"', "sans-serif"],
                        "label-sm": ["Tajawal", '"Plus Jakarta Sans"', "sans-serif"]
                    },
                    colors: {
                        "primary": "#95442b",
                        "on-primary": "#ffffff",
                        "primary-container": "#b45b40",
                        "on-primary-container": "#fffbff",
                        "primary-fixed": "#ffdbd1",
                        "primary-fixed-dim": "#ffb59f",
                        "on-primary-fixed": "#3a0a00",
                        "on-primary-fixed-variant": "#7a2f18",

                        "secondary": "#805252",
                        "on-secondary": "#ffffff",
                        "secondary-container": "#ffc3c2",
                        "on-secondary-container": "#7b4d4e",
                        "secondary-fixed": "#ffdad9",
                        "secondary-fixed-dim": "#f3b8b8",
                        "on-secondary-fixed": "#321112",
                        "on-secondary-fixed-variant": "#653b3c",

                        "tertiary": "#5d5c59",
                        "on-tertiary": "#ffffff",
                        "tertiary-container": "#767471",
                        "on-tertiary-container": "#fffbff",
                        "tertiary-fixed": "#e6e2de",
                        "tertiary-fixed-dim": "#c9c6c2",
                        "on-tertiary-fixed": "#1c1c19",
                        "on-tertiary-fixed-variant": "#484744",

                        "error": "#ba1a1a",
                        "on-error": "#ffffff",
                        "error-container": "#ffdad6",
                        "on-error-container": "#93000a",

                        "background": "#fff8f6",
                        "on-background": "#231916",

                        "surface": "#fff8f6",
                        "on-surface": "#231916",
                        "surface-variant": "#f1dfd8",
                        "on-surface-variant": "#55433d",
                        "surface-tint": "#98462d",
                        "surface-dim": "#e9d6d0",
                        "surface-bright": "#fff8f6",

                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#fff1ec",
                        "surface-container": "#fdeae4",
                        "surface-container-high": "#f7e4de",
                        "surface-container-highest": "#f1dfd8",

                        "outline": "#88726c",
                        "outline-variant": "#dbc1ba",

                        "inverse-surface": "#392e2a",
                        "inverse-on-surface": "#ffede7",
                        "inverse-primary": "#ffb59f",

                        brand: {
                            50: '#F9F5F1',
                            100: '#F4EAE6',
                            200: '#E9D5CF',
                            300: '#D5B4AB',
                            400: '#C18B8B',
                            500: '#CB6D51',
                            600: '#B55A40',
                            700: '#914530',
                            800: '#753A2A',
                            900: '#3A2F2B',
                            primary: '#95442b',
                            primaryHover: '#7a2f18',
                            primaryLight: '#FFF0ED',
                            primaryDark: '#5c1e0e',
                            secondary: '#805252',
                            bg: '#fff8f6',
                            surface: '#FFFFFF',
                            surfaceAlt: '#F4EAE6',
                            border: '#dbc1ba',
                            borderLight: '#f1dfd8',
                            text: '#231916',
                            textMuted: '#55433d',
                            textLight: '#88726c',
                            danger: '#ba1a1a',
                            dangerLight: '#ffdad6',
                            warning: '#D97706',
                            warningLight: '#FEF3C7',
                            success: '#16A34A',
                            successLight: '#DCFCE7'
                        }
                    },
                    spacing: {
                        "margin-mobile": "16px",
                        "margin-desktop": "40px",
                        "gutter": "24px",
                        "stack-sm": "8px",
                        "stack-md": "16px",
                        "stack-lg": "32px",
                        "base": "8px",
                        "container-max": "1200px"
                    },
                    fontSize: {
                        "display-lg": ["36px", { lineHeight: "44px", letterSpacing: "-0.02em", fontWeight: "700" }],
                        "headline-lg": ["28px", { lineHeight: "36px", letterSpacing: "-0.01em", fontWeight: "600" }],
                        "headline-md": ["22px", { lineHeight: "30px", fontWeight: "600" }],
                        "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }],
                        "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }],
                        "label-lg": ["14px", { lineHeight: "20px", letterSpacing: "0.01em", fontWeight: "600" }],
                        "label-sm": ["12px", { lineHeight: "16px", fontWeight: "500" }]
                    },
                    boxShadow: {
                        ambient: "0 4px 20px -2px rgba(85, 67, 61, 0.08)",
                        floating: "0 12px 32px -4px rgba(85, 67, 61, 0.12)",
                        soft: "0 4px 20px -2px rgba(58, 47, 43, 0.08)",
                        card: "0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03)"
                    },
                    borderRadius: {
                        DEFAULT: "0.25rem",
                        lg: "0.5rem",
                        xl: "0.75rem",
                        "2xl": "1rem",
                        "3xl": "1.5rem",
                        full: "9999px"
                    }
                }
            }
        };
    </script>

    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #fff8f6;
            color: #231916;
        }
        .ambient-shadow {
            box-shadow: 0 4px 20px rgba(85, 67, 61, 0.08);
        }
        .ambient-shadow:hover {
            box-shadow: 0 8px 24px rgba(85, 67, 61, 0.12);
        }
        .warm-shadow {
            box-shadow: 0 10px 30px -5px rgba(85, 67, 61, 0.08);
        }
        .booking-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .booking-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(85, 67, 61, 0.12);
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        [dir="rtl"] select, html[dir="rtl"] select, select {
            background-position: left 0.75rem center !important;
            padding-left: 2.5rem !important;
            padding-right: 1rem !important;
            text-align: right;
        }
    </style>

    <!-- Centralized Theme Tokens & Base CSS -->
    <link rel="stylesheet" href="/local-services-platform/assets/css/theme.css">

    <!-- Shared Client-side Scripts -->
    <script defer src="/local-services-platform/assets/js/main.js"></script>

    <?php echo $extra_head; ?>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col font-arabic antialiased">
<?php
}
