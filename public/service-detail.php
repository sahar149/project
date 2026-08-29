<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';

$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($service_id == 0) {
    header('Location: browse-services.php');
    exit;
}

// جلب تفاصيل الخدمة
$stmt = $pdo->prepare("
    SELECT s.*, 
           u.name as provider_name, 
           u.phone as provider_phone, 
           u.email as provider_email,
           u.address as provider_address, 
           c.name as category_name,
           COALESCE(ROUND(AVG(r.rating), 1), 0) as avg_rating, 
           COUNT(r.id) as review_count
    FROM services s
    JOIN users u ON s.provider_id = u.id
    JOIN categories c ON s.category_id = c.id
    LEFT JOIN reviews r ON r.service_id = s.id
    WHERE s.id = ? AND u.status = 'active'
    GROUP BY s.id
");

$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    header('Location: browse-services.php');
    exit;
}

// جلب التقييمات
$stmt = $pdo->prepare("
    SELECT r.*, u.name as customer_name 
    FROM reviews r 
    JOIN users u ON r.customer_id = u.id 
    WHERE r.service_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 5
");

$stmt->execute([$service_id]);
$reviews = $stmt->fetchAll();

// مساعدة لعرض عدد التقييمات
$review_word = ($service['review_count'] == 1)
    ? __('Review')
    : __('Reviews');


// =========================================================
// تحديد أيقونة الخدمة ديناميكياً حسب التصنيف
// =========================================================

$category = trim(
    mb_strtolower(
        (string)($service['category_name'] ?? ''),
        'UTF-8'
    )
);

$category_icons = [

    // Plumbing
    'plumbing' => 'plumbing',
    'سباكة' => 'plumbing',

    // Electrical
    'electrical' => 'electrical_services',
    'كهرباء' => 'electrical_services',

    // Cleaning
    'cleaning' => 'cleaning_services',
    'تنظيف' => 'cleaning_services',

    // Gardening
    'gardening' => 'yard',
    'بستنة' => 'yard',

    // Moving
    'moving' => 'local_shipping',
    'نقل' => 'local_shipping',

    // Painting
    'painting' => 'format_paint',
    'دهان' => 'format_paint',
];

// إذا لم نجد التصنيف نستخدم handyman كأيقونة افتراضية
$service_icon = $category_icons[$category] ?? 'handyman';

?>

<!DOCTYPE html>
<html class="light" lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo htmlspecialchars($service['title']); ?> - دبرها    </title>


    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>


    <!-- Tajawal Font for Arabic -->
    <link
        href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <!-- Material Symbols -->
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet"
    >


    <script>

        tailwind.config = {

            darkMode: "class",

            theme: {

                extend: {

                    colors: {

                        "secondary-fixed": "#ffdad9",
                        "primary-fixed": "#ffdbd1",
                        "surface-variant": "#f1dfd8",
                        "on-primary-container": "#fffbff",
                        "on-surface-variant": "#55433d",
                        "error-container": "#ffdad6",
                        "on-secondary-fixed-variant": "#653b3c",
                        "on-error": "#ffffff",
                        "tertiary-fixed": "#e6e2de",
                        "outline": "#88726c",
                        "secondary-fixed-dim": "#f3b8b8",
                        "inverse-on-surface": "#ffede7",
                        "surface-bright": "#fff8f6",
                        "surface-dim": "#e9d6d0",
                        "on-primary-fixed": "#3a0a00",
                        "on-secondary-container": "#7b4d4e",
                        "on-secondary": "#ffffff",
                        "surface-container": "#fdeae4",
                        "primary-container": "#b45b40",
                        "tertiary-container": "#767471",
                        "tertiary": "#5d5c59",
                        "primary": "#95442b",
                        "on-secondary-fixed": "#321112",
                        "outline-variant": "#dbc1ba",
                        "surface-tint": "#98462d",
                        "surface-container-highest": "#f1dfd8",
                        "on-tertiary-fixed": "#1c1c19",
                        "surface-container-low": "#fff1ec",
                        "on-background": "#231916",
                        "on-tertiary-fixed-variant": "#484744",
                        "on-error-container": "#93000a",
                        "surface": "#fff8f6",
                        "secondary-container": "#ffc3c2",
                        "on-surface": "#231916",
                        "error": "#ba1a1a",
                        "surface-container-lowest": "#ffffff",
                        "primary-fixed-dim": "#ffb59f",
                        "on-tertiary-container": "#fffbff",
                        "tertiary-fixed-dim": "#c9c6c2",
                        "surface-container-high": "#f7e4de",
                        "secondary": "#805252",
                        "background": "#fff8f6",
                        "inverse-surface": "#392e2a",
                        "on-tertiary": "#ffffff",
                        "on-primary": "#ffffff"

                    },

                    borderRadius: {

                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"

                    },

                    spacing: {

                        "margin-mobile": "16px",
                        "gutter": "24px",
                        "container-max": "1200px",
                        "stack-md": "16px",
                        "stack-lg": "32px",
                        "base": "8px",
                        "stack-sm": "8px",
                        "margin-desktop": "40px"

                    },

                    fontFamily: {

                        "display-lg": ["Tajawal"],
                        "body-lg": ["Tajawal"],
                        "label-lg": ["Tajawal"],
                        "headline-md": ["Tajawal"],
                        "headline-lg": ["Tajawal"],
                        "body-md": ["Tajawal"],
                        "label-sm": ["Tajawal"]

                    },

                    fontSize: {

                        "display-lg": [
                            "48px",
                            {
                                lineHeight: "56px",
                                letterSpacing: "-0.02em",
                                fontWeight: "700"
                            }
                        ],

                        "body-lg": [
                            "18px",
                            {
                                lineHeight: "28px",
                                fontWeight: "400"
                            }
                        ],

                        "label-lg": [
                            "14px",
                            {
                                lineHeight: "20px",
                                letterSpacing: "0.01em",
                                fontWeight: "600"
                            }
                        ],

                        "headline-md": [
                            "24px",
                            {
                                lineHeight: "32px",
                                fontWeight: "600"
                            }
                        ],

                        "headline-lg": [
                            "32px",
                            {
                                lineHeight: "40px",
                                letterSpacing: "-0.01em",
                                fontWeight: "600"
                            }
                        ],

                        "body-md": [
                            "16px",
                            {
                                lineHeight: "24px",
                                fontWeight: "400"
                            }
                        ],

                        "label-sm": [
                            "12px",
                            {
                                lineHeight: "16px",
                                fontWeight: "500"
                            }
                        ]

                    }

                }

            }

        };

    </script>


    <style>

        body {

            background-color: #F9F5F1;
            color: #3A2F2B;
            font-family: 'Tajawal', sans-serif;

        }

        .warm-shadow {

            box-shadow:
                0 4px 20px rgba(58, 47, 43, 0.05);

        }

        .material-symbols-outlined {

            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24;

        }

    </style>

</head>


<body class="font-body-md text-on-background min-h-screen">


<!-- ========================================================= -->
<!-- HEADER -->
<!-- ========================================================= -->
<!-- ========================================================= -->
<!-- UNIFIED NAVBAR -->
<!-- ========================================================= -->

<header class="bg-background w-full top-0 z-50">

    <div
        class="flex justify-between items-center w-full px-margin-desktop py-4 max-w-container-max mx-auto"
    >

        <!-- ================================================= -->
        <!-- LOGO -->
        <!-- ================================================= -->

        <div class="flex items-center gap-4">

            <a
                href="/local-services-platform/index.php"
                class="text-2xl font-bold text-primary"
            >
                <?php echo __('Dabberha'); ?>
            </a>

        </div>


        <!-- ================================================= -->
        <!-- NAVIGATION -->
        <!-- ================================================= -->

        <nav class="hidden md:flex gap-8 items-center">

            <!-- Browse Services -->

            <a
                href="/local-services-platform/public/browse-services.php"
                class="text-on-surface-variant font-label-lg text-label-lg hover:text-primary transition-colors duration-200"
            >
                <?php echo __('Browse Services'); ?>
            </a>


            <!-- My Bookings -->

            <?php if (
                isLoggedIn() &&
                getUserRole() === 'customer'
            ): ?>

                <a
                    href="/local-services-platform/public/my-bookings.php"
                    class="text-on-surface-variant font-label-lg text-label-lg hover:text-primary transition-colors duration-200"
                >
                    <?php echo __('My Bookings'); ?>
                </a>

            <?php endif; ?>


            <!-- Provider Dashboard -->

            <?php if (
                isLoggedIn() &&
                getUserRole() === 'provider'
            ): ?>

                <a
                    href="/local-services-platform/provider/dashboard.php"
                    class="text-on-surface-variant font-label-lg text-label-lg hover:text-primary transition-colors duration-200"
                >
                    <?php echo __('Provider Dashboard'); ?>
                </a>

            <?php endif; ?>

        </nav>


        <!-- ================================================= -->
        <!-- USER AREA -->
        <!-- ================================================= -->

        <div class="flex items-center gap-4">

            <?php if (isLoggedIn()): ?>

                <!-- USER NAME -->

                <div
                    class="hidden sm:flex items-center gap-2 text-on-surface-variant"
                >

                    <span class="material-symbols-outlined">
                        account_circle
                    </span>

                    <span class="font-label-lg">
                        <?php
                        echo htmlspecialchars(
                            getUserName()
                        );
                        ?>
                    </span>

                </div>


                <!-- LOGOUT -->

                <a
                    href="/local-services-platform/public/logout.php"
                    class="bg-primary text-on-primary px-6 py-2 rounded-full font-label-lg text-label-lg hover:bg-surface-tint transition-colors"
                >
                    <?php echo __('Logout'); ?>
                </a>

            <?php else: ?>

                <!-- SIGN IN -->

                <a
                    href="/local-services-platform/public/login.php"
                    class="bg-primary text-on-primary px-6 py-2 rounded-full font-label-lg text-label-lg hover:bg-surface-tint transition-colors"
                >
                    <?php echo __('Sign In'); ?>
                </a>

            <?php endif; ?>

        </div>

    </div>

</header>

<!-- ========================================================= -->
<!-- MAIN -->
<!-- ========================================================= -->

<main class="max-w-7xl mx-auto px-6 py-12">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">


        <!-- ================================================= -->
        <!-- LEFT COLUMN -->
        <!-- ================================================= -->

        <div class="lg:col-span-8 space-y-12">


            <!-- ================================================= -->
            <!-- SERVICE HEADER -->
            <!-- ================================================= -->

            <section
                class="bg-white rounded-xl p-8 warm-shadow flex flex-col md:flex-row items-center md:items-start gap-8"
            >

                <!-- Service icon -->

                <div
                    class="w-32 h-32 md:w-40 md:h-40 rounded-full bg-surface-container-low flex items-center justify-center border-4 border-surface-variant shrink-0"
                >

                    <!--
                        الأيقونة ديناميكية حسب Category
                    -->

                    <span
                        class="material-symbols-outlined text-primary text-[64px]"
                        aria-hidden="true"
                    >
                        <?php echo htmlspecialchars($service_icon); ?>
                    </span>

                </div>


                <!-- Service info -->

                <div class="flex-1 text-center md:text-right">

                    <div
                        class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2"
                    >

                        <h1
                            class="font-headline-lg text-on-surface"
                        >
                            <?php echo htmlspecialchars($service['title']); ?>
                        </h1>


                        <div
                            class="flex items-center justify-center md:justify-start gap-2 px-3 py-1 bg-secondary-container text-on-secondary-container rounded-full w-fit mx-auto md:mx-0"
                        >

                            <svg
                                class="w-4 h-4"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >

                                <path
                                    fill-rule="evenodd"
                                    d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"
                                ></path>

                            </svg>


                            <span class="text-label-sm">

                                <?php echo __('Verified Service'); ?>

                            </span>

                        </div>

                    </div>


                    <!-- Category -->

                    <p
                        class="text-headline-md text-secondary font-medium mb-4"
                    >
                        <?php echo htmlspecialchars($service['category_name']); ?>
                    </p>


                    <!-- Provider + Rating -->

                    <div
                        class="flex flex-wrap items-center justify-center md:justify-start gap-6 text-on-surface-variant"
                    >

                        <!-- Provider -->

                        <div class="flex items-center gap-2">

                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7-7h14a7 7 0 00-7-7z"
                                ></path>

                            </svg>

                            <span class="font-medium">

                                <?php echo htmlspecialchars($service['provider_name']); ?>

                            </span>

                        </div>


                        <!-- Rating -->

                        <div class="flex items-center gap-1">

                            <svg
                                class="w-5 h-5 text-orange-400"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >

                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                ></path>

                            </svg>


                            <?php if ($service['review_count'] > 0): ?>

                                <span class="font-bold">

                                    <?php echo htmlspecialchars($service['avg_rating']); ?>

                                </span>

                                <span class="text-label-lg">

                                    (<?php echo $service['review_count']; ?>
                                    <?php echo $review_word; ?>)

                                </span>

                            <?php else: ?>

                                <span class="text-label-lg">

                                    <?php echo __('No reviews yet'); ?>

                                </span>

                            <?php endif; ?>

                        </div>


                        <!-- Address -->

                        <?php if (!empty($service['provider_address'])): ?>

                            <div class="flex items-center gap-1">

                                <svg
                                    class="w-4 h-4 text-stone-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                    ></path>

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                    ></path>

                                </svg>

                                <span class="text-label-lg">

                                    <?php echo htmlspecialchars($service['provider_address']); ?>

                                </span>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </section>


            <!-- ================================================= -->
            <!-- SERVICE DETAILS -->
            <!-- ================================================= -->

            <section>

                <h2
                    class="font-headline-md mb-8 text-on-background"
                >
                    <?php echo __('Service Details'); ?>
                </h2>


                <div
                    class="bg-white p-8 rounded-xl warm-shadow border border-transparent"
                >

                    <div class="flex justify-between items-start mb-6">


                        <!-- Dynamic Service Icon -->

                        <div
                            class="p-3 bg-surface-container-low rounded-lg text-primary"
                        >

                            <span
                                class="material-symbols-outlined text-[32px]"
                                aria-hidden="true"
                            >
                                <?php echo htmlspecialchars($service_icon); ?>
                            </span>

                        </div>


                        <!-- Price -->

                        <div class="text-left">

                        


                              <span dir="rtl" class="text-headline-md text-primary font-bold">
    <?php echo number_format($service['price'], 2); ?>
    <span dir="rtl"> دل</span>
</span>


                        </div>

                    </div>


                    <h3
                        class="font-headline-md mb-3"
                    >

                        <?php
                        echo htmlspecialchars(
                            $service['title']
                        );
                        ?>

                    </h3>


                    <p
                        class="text-on-surface-variant text-body-md mb-6"
                    >

                        <?php

                        echo nl2br(
                            htmlspecialchars(
                                $service['description']
                            )
                        );

                        ?>

                    </p>


                    <div
                        class="flex items-center gap-2 text-label-lg text-on-surface-variant"
                    >

                        <svg
                            class="w-5 h-5 text-primary"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            ></path>

                        </svg>

                        <?php echo __('Professional local service'); ?>

                    </div>

                </div>

            </section>


            <!-- ================================================= -->
            <!-- PROVIDER INFORMATION -->
            <!-- ================================================= -->

            <section>

                <h2
                    class="font-headline-md mb-8 text-on-background"
                >
                    <?php echo __('Provider Information'); ?>
                </h2>


                <div
                    class="bg-white rounded-xl p-8 warm-shadow"
                >

                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-6"
                    >


                        <!-- Name -->

                        <div class="flex items-start gap-4">

                            <div
                                class="p-3 bg-surface-container-low rounded-lg text-primary"
                            >

                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    ></path>

                                </svg>

                            </div>


                            <div>

                                <p
                                    class="text-label-sm text-on-surface-variant mb-1"
                                >
                                    <?php echo __('Name'); ?>
                                </p>

                                <p class="font-semibold">
                                    <?php echo htmlspecialchars($service['provider_name']); ?>
                                </p>

                            </div>

                        </div>


                        <!-- Phone -->

                        <div class="flex items-start gap-4">

                            <div
                                class="p-3 bg-surface-container-low rounded-lg text-primary"
                            >

                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                    ></path>

                                </svg>

                            </div>


                            <div>

                                <p
                                    class="text-label-sm text-on-surface-variant mb-1"
                                >
                                    <?php echo __('Phone'); ?>
                                </p>

                                <p class="font-semibold">

                                    <?php
                                    echo htmlspecialchars(
                                        $service['provider_phone']
                                        ?? __('Not provided')
                                    );
                                    ?>

                                </p>

                            </div>

                        </div>


                        <!-- Email -->

                        <div class="flex items-start gap-4">

                            <div
                                class="p-3 bg-surface-container-low rounded-lg text-primary"
                            >

                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    ></path>

                                </svg>

                            </div>


                            <div>

                                <p
                                    class="text-label-sm text-on-surface-variant mb-1"
                                >
                                    <?php echo __('Email'); ?>
                                </p>

                                <p class="font-semibold break-all">

                                    <?php
                                    echo htmlspecialchars(
                                        $service['provider_email']
                                    );
                                    ?>

                                </p>

                            </div>

                        </div>


                        <!-- Address -->

                        <div class="flex items-start gap-4">

                            <div
                                class="p-3 bg-surface-container-low rounded-lg text-primary"
                            >

                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                    ></path>

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                    ></path>

                                </svg>

                            </div>


                            <div>

                                <p
                                    class="text-label-sm text-on-surface-variant mb-1"
                                >
                                    <?php echo __('Address'); ?>
                                </p>

                                <p class="font-semibold">

                                    <?php
                                    echo htmlspecialchars(
                                        $service['provider_address']
                                        ?? __('Not provided')
                                    );
                                    ?>

                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </section>


            <!-- ================================================= -->
            <!-- REVIEWS -->
            <!-- ================================================= -->

            <section>

                <h2
                    class="font-headline-md mb-8 text-on-background"
                >
                    <?php echo __('Customer Reviews'); ?>
                </h2>


                <div
                    class="bg-white rounded-xl p-8 warm-shadow"
                >

                    <?php if (count($reviews) > 0): ?>

                        <div class="space-y-6">

                            <?php foreach ($reviews as $review): ?>

                                <div
                                    class="border-b border-surface-variant pb-6 last:border-0 last:pb-0"
                                >

                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2"
                                    >

                                        <div
                                            class="flex items-center gap-3"
                                        >

                                            <div
                                                class="w-10 h-10 rounded-full bg-surface-container-low flex items-center justify-center"
                                            >

                                                <svg
                                                    class="w-6 h-6 text-primary"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >

                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7-7h14a7 7 0 00-7 7z"
                                                    ></path>

                                                </svg>

                                            </div>


                                            <strong>

                                                <?php
                                                echo htmlspecialchars(
                                                    $review['customer_name']
                                                );
                                                ?>

                                            </strong>

                                        </div>


                                        <div
                                            class="flex items-center gap-1"
                                        >

                                            <span class="text-orange-400">

                                                <?php
                                                echo str_repeat(
                                                    '★',
                                                    (int)$review['rating']
                                                );
                                                ?>

                                            </span>


                                            <span
                                                class="text-sm text-on-surface-variant"
                                            >

                                                <?php echo (int)$review['rating']; ?>/5

                                            </span>

                                        </div>

                                    </div>


                                    <p
                                        class="text-on-surface-variant text-body-md mb-2"
                                    >

                                        <?php
                                        echo nl2br(
                                            htmlspecialchars(
                                                $review['comment']
                                            )
                                        );
                                        ?>

                                    </p>


                                    <small
                                        class="text-label-sm text-on-surface-variant"
                                    >

                                        <?php
                                        echo date(
                                            'M d, Y',
                                            strtotime(
                                                $review['created_at']
                                            )
                                        );
                                        ?>

                                    </small>

                                </div>

                            <?php endforeach; ?>

                        </div>


                    <?php else: ?>


                        <div
                            class="text-center py-10"
                        >

                            <div
                                class="w-16 h-16 bg-surface-container-low rounded-full flex items-center justify-center mx-auto mb-4"
                            >

                                <svg
                                    class="w-8 h-8 text-primary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                                    ></path>

                                </svg>

                            </div>


                            <h3
                                class="font-headline-md mb-2"
                            >
                                <?php echo __('No reviews yet'); ?>
                            </h3>


                            <p
                                class="text-on-surface-variant"
                            >
                                <?php echo __('Be the first customer to review this service.'); ?>
                            </p>

                        </div>

                    <?php endif; ?>

                </div>

            </section>

        </div>


        <!-- ================================================= -->
        <!-- RIGHT COLUMN - BOOKING -->
        <!-- ================================================= -->

        <aside class="lg:col-span-4">

            <div
                class="sticky top-28 bg-white rounded-xl p-8 warm-shadow border border-outline-variant"
            >

                <h3
                    class="font-headline-md mb-6 flex items-center gap-2"
                >

                    <svg
                        class="w-6 h-6 text-primary"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                        ></path>

                    </svg>

                    <?php echo __('Book This Service'); ?>

                </h3>


                <?php if (isLoggedIn() && getUserRole() == 'customer'): ?>


                    <form
                        method="POST"
                        action="book-service.php"
                        class="space-y-6"
                    >

                        <!-- Hidden fields -->

                        <input
                            type="hidden"
                            name="service_id"
                            value="<?php echo $service['id']; ?>"
                        >

                        <input
                            type="hidden"
                            name="provider_id"
                            value="<?php echo $service['provider_id']; ?>"
                        >


                        <!-- Service Date -->

                        <div>

                            <label
                                class="block text-label-lg mb-2 text-on-surface-variant"
                            >
                                <?php echo __('Service Date'); ?>
                            </label>

                            <input
                                type="date"
                                name="booking_date"
                                min="<?php echo date('Y-m-d'); ?>"
                                required
                                class="w-full p-4 rounded-lg border border-outline-variant bg-surface-bright focus:ring-2 focus:ring-secondary focus:border-transparent outline-none transition-all"
                            >

                        </div>


                        <!-- Service Time -->

                        <div>

                            <label
                                class="block text-label-lg mb-2 text-on-surface-variant"
                            >
                                <?php echo __('Service Time'); ?>
                            </label>

                            <input
                                type="time"
                                name="booking_time"
                                required
                                class="w-full p-4 rounded-lg border border-outline-variant bg-surface-bright focus:ring-2 focus:ring-secondary focus:border-transparent outline-none transition-all"
                            >

                        </div>


                        <!-- Notes -->

                        <div>

                            <label
                                class="block text-label-lg mb-2 text-on-surface-variant"
                            >
                                <?php echo __('Service Notes'); ?>
                            </label>

                            <textarea
                                name="notes"
                                rows="4"
                                placeholder="<?php echo __('Tell the provider about any special requirements...'); ?>"
                                class="w-full p-4 rounded-lg border border-outline-variant bg-surface-bright focus:ring-2 focus:ring-secondary focus:border-transparent outline-none transition-all resize-none"
                            ></textarea>

                        </div>


                        <!-- Price -->

                        <div
                            class="pt-4 border-t border-surface-variant space-y-4"
                        >

                            <div
                                class="flex justify-between items-center text-label-lg"
                            >

                                <span
                                    class="text-on-surface-variant"
                                >
                                    <?php echo __('Service Price'); ?>
                                </span>

                                  <span dir="rtl" class="text-headline-md text-primary font-bold">
    <?php echo number_format($service['price'], 2); ?>
    <span dir="rtl"> دل</span>
</span>

                            </div>


                            <!-- <div
                                class="flex justify-between items-center text-headline-md pt-2"
                            >

                                <span>
                                    <?php echo __('Total'); ?>
                                </span>

                                  <span dir="rtl" class="text-headline-md text-primary font-bold">
    <?php echo number_format($service['price'], 2); ?>
    <span dir="rtl"> دل</span>
</span>

                            </div>

                        </div> -->


                        <!-- Book Button -->

                        <button
                            type="submit"
                            class="w-full py-4 bg-primary text-white rounded-xl font-bold text-lg warm-shadow hover:brightness-105 transition-all flex items-center justify-center gap-2 active:scale-95"
                        >

                            <?php echo __('Book Now'); ?>

                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                                ></path>

                            </svg>

                        </button>

                    </form>


                <?php elseif (!isLoggedIn()): ?>


                    <!-- Not logged in -->

                    <div class="text-center">

                        <div
                            class="w-16 h-16 bg-surface-container-low rounded-full flex items-center justify-center mx-auto mb-5"
                        >

                            <svg
                                class="w-8 h-8 text-primary"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                ></path>

                            </svg>

                        </div>


                        <h4
                            class="font-headline-md mb-2"
                        >
                            <?php echo __('Login to Book'); ?>
                        </h4>


                        <p
                            class="text-on-surface-variant text-body-md mb-6"
                        >
                            <?php echo __('Please login as a customer to book this service.'); ?>
                        </p>


                        <a
                            href="login.php"
                            class="block w-full py-4 bg-primary text-white rounded-xl font-bold text-lg text-center hover:brightness-105 transition-all active:scale-95"
                        >
                            <?php echo __('Login as Customer'); ?>
                        </a>

                    </div>


                <?php elseif (getUserRole() == 'provider'): ?>


                    <!-- Provider -->

                    <div class="text-center">

                        <div
                            class="w-16 h-16 bg-surface-container-low rounded-full flex items-center justify-center mx-auto mb-5"
                        >

                            <svg
                                class="w-8 h-8 text-primary"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 9l1-5h16l1 5M3 9a2 2 0 104 0 2 2 0 00-4 0zm18 0a2 2 0 10-4 0 2 2 0 004 0zM6 9v10a2 2 0 002 2h8a2 2 0 002-2V9M9 13h6"
                                ></path>

                            </svg>

                        </div>


                        <h4
                            class="font-headline-md mb-2"
                        >
                            <?php echo __('Provider Account'); ?>
                        </h4>


                        <p
                            class="text-on-surface-variant text-body-md"
                        >
                            <?php echo __('You are registered as a provider. Please switch to a customer account to book this service.'); ?>
                        </p>

                    </div>


                <?php elseif (getUserRole() == 'admin'): ?>


                    <!-- Admin -->

                    <div class="text-center">

                        <div
                            class="w-16 h-16 bg-surface-container-low rounded-full flex items-center justify-center mx-auto mb-5"
                        >

                            <svg
                                class="w-8 h-8 text-primary"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31.826-2.37-2.37a1.724 1.724 0 001.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                                ></path>

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                ></path>

                            </svg>

                        </div>


                        <h4
                            class="font-headline-md mb-2"
                        >
                            <?php echo __('Admin Account'); ?>
                        </h4>


                        <p
                            class="text-on-surface-variant text-body-md"
                        >
                            <?php echo __('You are currently logged in as an administrator.'); ?>
                        </p>

                    </div>

                <?php endif; ?>


                <!-- Trust message -->

                <div
                    class="mt-6 flex items-center gap-3 justify-center"
                >

                    <div
                        class="w-9 h-9 rounded-full bg-secondary-container flex items-center justify-center"
                    >

                        <svg
                            class="w-5 h-5 text-secondary"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 00-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                            ></path>

                        </svg>

                    </div>


                    <p
                        class="text-label-sm text-secondary font-semibold"
                    >
                        <?php echo __('Trusted local service'); ?>
                    </p>

                </div>

            </div>

        </aside>

    </div>

</main>


<!-- ========================================================= -->
<!-- FOOTER -->
<!-- ========================================================= -->

<footer
    class="bg-stone-100 w-full py-12 px-6 mt-16 border-t border-stone-200"
>

    <div
        class="flex flex-col md:flex-row justify-between items-center gap-6 max-w-7xl mx-auto"
    >

        <!-- الشعار -->

        <div class="flex flex-col gap-2 text-center md:text-right">

            <span
                class="font-bold text-[#95442b] text-xl"
            >
                <?php echo __('Dabberha'); ?>
            </span>

            <p class="text-sm text-stone-600">

                <?php echo __('© 2026 Dabberha Services. Built for the community.'); ?>

            </p>

        </div>


        <!-- روابط التذييل -->

        <div class="flex flex-wrap justify-center gap-6">

            <a
                href="#"
                class="text-sm text-stone-500 hover:text-[#95442b] transition-colors"
            >
                <?php echo __('Privacy Policy'); ?>
            </a>

            <a
                href="#"
                class="text-sm text-stone-500 hover:text-[#95442b] transition-colors"
            >
                <?php echo __('Terms of Service'); ?>
            </a>

            <a
                href="#"
                class="text-sm text-stone-500 hover:text-[#95442b] transition-colors"
            >
                <?php echo __('Help Center'); ?>
            </a>

            <a
                href="#"
                class="text-sm text-stone-500 hover:text-[#95442b] transition-colors"
            >
                <?php echo __('Contact Us'); ?>
            </a>

        </div>

    </div>

</footer>

<!-- ========================================================= -->
<!-- MOBILE BOOKING BAR -->
<!-- ========================================================= -->

<?php if (isLoggedIn() && getUserRole() == 'customer'): ?>

    <div
        class="md:hidden fixed bottom-0 left-0 right-0 bg-white p-4 border-t border-surface-variant flex items-center justify-between z-50"
    >

        <div>

            <span
                class="text-label-sm text-on-surface-variant block uppercase tracking-wider"
            >
                <?php echo __('Starting from'); ?>
            </span>


             <span dir="rtl" class="text-headline-md text-primary font-bold">
    <?php echo number_format($service['price'], 2); ?>
    <span dir="rtl"> دل</span>
</span>


        </div>


        <a
            href="#booking-form"
            onclick="document.querySelector('input[name=booking_date]').focus();"
            class="bg-primary text-white px-8 py-3 rounded-xl font-bold"
        >
            <?php echo __('Book Now'); ?>
        </a>

    </div>

<?php endif; ?>


</body>

</html>