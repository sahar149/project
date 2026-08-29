<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';

requireRole('customer');

$customer_id = getUserId();

/*
|--------------------------------------------------------------------------
| جلب جميع حجوزات العميل
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT 
        b.*,
        s.title AS service_title,
        c.name AS category_name,
        u.name AS provider_name,
        (
            SELECT id
            FROM reviews
            WHERE booking_id = b.id
            LIMIT 1
        ) AS has_review

    FROM bookings b

    JOIN services s
        ON b.service_id = s.id

    LEFT JOIN categories c
        ON s.category_id = c.id

    JOIN users u
        ON b.provider_id = u.id

    WHERE b.customer_id = ?

    ORDER BY b.created_at DESC
");

$stmt->execute([$customer_id]);

$bookings = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| دالة تحديد أيقونة الخدمة
|--------------------------------------------------------------------------
*/

function getServiceIcon($category_name)
{
    if (!$category_name) {
        return 'home_repair_service';
    }

    $category = trim(mb_strtolower($category_name, 'UTF-8'));

    /*
    |--------------------------------------------------------------------------
    | دهان
    |--------------------------------------------------------------------------
    */

    if (
        strpos($category, 'دهان') !== false ||
        strpos($category, 'paint') !== false ||
        strpos($category, 'painting') !== false
    ) {
        return 'format_paint';
    }


    /*
    |--------------------------------------------------------------------------
    | نقل
    |--------------------------------------------------------------------------
    */

    if (
        strpos($category, 'نقل') !== false ||
        strpos($category, 'transport') !== false ||
        strpos($category, 'moving') !== false
    ) {
        return 'local_shipping';
    }


    /*
    |--------------------------------------------------------------------------
    | بستنة
    |--------------------------------------------------------------------------
    */

    if (
        strpos($category, 'بستنة') !== false ||
        strpos($category, 'حدائق') !== false ||
        strpos($category, 'زراعة') !== false ||
        strpos($category, 'garden') !== false ||
        strpos($category, 'gardening') !== false
    ) {
        return 'yard';
    }


    /*
    |--------------------------------------------------------------------------
    | تنظيف
    |--------------------------------------------------------------------------
    */

    if (
        strpos($category, 'تنظيف') !== false ||
        strpos($category, 'نظافة') !== false ||
        strpos($category, 'clean') !== false ||
        strpos($category, 'cleaning') !== false
    ) {
        return 'cleaning_services';
    }


    /*
    |--------------------------------------------------------------------------
    | كهرباء
    |--------------------------------------------------------------------------
    */

    if (
        strpos($category, 'كهرباء') !== false ||
        strpos($category, 'كهربائي') !== false ||
        strpos($category, 'electric') !== false ||
        strpos($category, 'electrical') !== false
    ) {
        return 'electrical_services';
    }


    /*
    |--------------------------------------------------------------------------
    | سباكة
    |--------------------------------------------------------------------------
    */

    if (
        strpos($category, 'سباكة') !== false ||
        strpos($category, 'سباك') !== false ||
        strpos($category, 'plumb') !== false
    ) {
        return 'plumbing';
    }


    /*
    |--------------------------------------------------------------------------
    | Default
    |--------------------------------------------------------------------------
    */

    return 'home_repair_service';
}

?>

<!DOCTYPE html>

<html
    class="light"
    lang="ar"
    dir="rtl"
>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo __('My Bookings'); ?> - دبرها    </title>


    <!-- ========================================================= -->
    <!-- GOOGLE FONTS -->
    <!-- ========================================================= -->

    <link
        href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- ========================================================= -->
    <!-- MATERIAL SYMBOLS -->
    <!-- ========================================================= -->

    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet"
    >


    <!-- ========================================================= -->
    <!-- TAILWIND CSS -->
    <!-- ========================================================= -->

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>


    <script>

        tailwind.config = {

            darkMode: "class",

            theme: {

                extend: {

                    colors: {

                        "primary": "#95442b",
                        "surface-tint": "#98462d",
                        "tertiary": "#5d5c59",

                        "on-secondary-container": "#7b4d4e",

                        "primary-fixed-dim": "#ffb59f",
                        "primary-fixed": "#ffdbd1",

                        "on-surface-variant": "#55433d",

                        "secondary-fixed-dim": "#f3b8b8",
                        "on-secondary-fixed": "#321112",

                        "on-background": "#231916",

                        "inverse-surface": "#392e2a",

                        "secondary": "#805252",

                        "on-secondary-fixed-variant": "#653b3c",

                        "surface-container-high": "#f7e4de",

                        "secondary-container": "#ffc3c2",

                        "surface": "#fff8f6",

                        "on-error-container": "#93000a",

                        "background": "#fff8f6",

                        "error-container": "#ffdad6",

                        "surface-container-lowest": "#ffffff",

                        "on-tertiary": "#ffffff",
                        "on-primary": "#ffffff",

                        "surface-container-low": "#fff1ec",

                        "on-primary-container": "#fffbff",

                        "tertiary-fixed": "#e6e2de",

                        "surface-bright": "#fff8f6",

                        "primary-container": "#b45b40",

                        "surface-container": "#fdeae4",

                        "on-tertiary-fixed": "#1c1c19",

                        "error": "#ba1a1a",

                        "on-tertiary-fixed-variant": "#484744",

                        "inverse-primary": "#ffb59f",

                        "on-secondary": "#ffffff",

                        "secondary-fixed": "#ffdad9",

                        "outline-variant": "#dbc1ba",

                        "on-surface": "#231916",

                        "on-tertiary-container": "#fffbff",

                        "on-primary-fixed-variant": "#7a2f18",

                        "tertiary-container": "#767471",

                        "outline": "#88726c",

                        "surface-dim": "#e9d6d0",

                        "on-error": "#ffffff",

                        "surface-container-highest": "#f1dfd8",

                        "surface-variant": "#f1dfd8",

                        "tertiary-fixed-dim": "#c9c6c2",

                        "inverse-on-surface": "#ffede7",

                        "on-primary-fixed": "#3a0a00"

                    },


                    borderRadius: {

                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"

                    },


                    spacing: {

                        "margin-mobile": "16px",
                        "margin-desktop": "40px",

                        "gutter": "24px",

                        "stack-sm": "8px",
                        "stack-lg": "32px",
                        "stack-md": "16px",

                        "base": "8px",

                        "container-max": "1200px"

                    },


                    fontFamily: {

                        "display-lg": ["Tajawal", "sans-serif"],
                        "label-lg": ["Tajawal", "sans-serif"],
                        "body-md": ["Tajawal", "sans-serif"],
                        "headline-md": ["Tajawal", "sans-serif"],
                        "label-sm": ["Tajawal", "sans-serif"],
                        "headline-lg": ["Tajawal", "sans-serif"],
                        "body-lg": ["Tajawal", "sans-serif"]

                    },


                    fontSize: {

                        "display-lg": [
                            "24px",
                            {
                                lineHeight: "56px",
                                letterSpacing: "-0.02em",
                                fontWeight: "700"
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

                        "body-md": [
                            "16px",
                            {
                                lineHeight: "24px",
                                fontWeight: "400"
                            }
                        ],

                        "headline-md": [
                            "24px",
                            {
                                lineHeight: "32px",
                                fontWeight: "600"
                            }
                        ],

                        "label-sm": [
                            "12px",
                            {
                                lineHeight: "16px",
                                fontWeight: "500"
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

                        "body-lg": [
                            "18px",
                            {
                                lineHeight: "28px",
                                fontWeight: "400"
                            }
                        ]

                    }

                }

            }

        };

    </script>


    <!-- ========================================================= -->
    <!-- CUSTOM CSS -->
    <!-- ========================================================= -->

    <style>

        body {

            font-family: 'Tajawal', sans-serif;

        }


        .ambient-shadow {

            box-shadow:
                0 4px 20px rgba(85, 67, 61, 0.08);

        }


        .ambient-shadow:hover {

            box-shadow:
                0 8px 24px rgba(85, 67, 61, 0.12);

        }


        .booking-card {

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;

        }


        .booking-card:hover {

            transform: translateY(-2px);

            box-shadow:
                0 8px 24px rgba(85, 67, 61, 0.12);

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


<body
    class="bg-background text-on-background font-body-md min-h-screen flex flex-col"
>


<!-- ========================================================= -->
<!-- NAVBAR -->
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

<main
    class="flex-grow px-margin-desktop py-stack-lg max-w-container-max mx-auto w-full"
>


    <!-- ===================================================== -->
    <!-- PAGE HEADER -->
    <!-- ===================================================== -->

    <div
        class="mb-stack-lg flex items-center gap-3"
    >

        <span
            class="material-symbols-outlined text-display-lg text-primary"
            style="font-variation-settings: 'FILL' 1;"
        >
            event_note
        </span>


        <div>

            <h1
                class="font-display-lg text-display-lg text-on-background"
            >
                <?php echo __('My Bookings'); ?>
            </h1>


            <p
                class="font-body-lg text-body-lg text-on-surface-variant mt-2"
            >
                <?php echo __('Manage your upcoming and past home services.'); ?>
            </p>

        </div>

    </div>


    <!-- ===================================================== -->
    <!-- FILTER TABS -->
    <!-- ===================================================== -->

    <div
        class="flex gap-8 mb-10 border-b border-outline-variant"
    >

        <!-- ALL -->

        <button
            id="allTab"
            onclick="filterBookings('all')"
            class="pb-4 border-b-2 border-primary text-primary font-semibold font-label-lg"
        >
            <?php echo __('All Bookings'); ?>
        </button>


        <!-- UPCOMING -->

        <button
            id="upcomingTab"
            onclick="filterBookings('upcoming')"
            class="pb-4 text-on-surface-variant hover:text-primary transition-colors font-label-lg"
        >
            <?php echo __('Upcoming'); ?>
        </button>


        <!-- PAST -->

        <button
            id="pastTab"
            onclick="filterBookings('past')"
            class="pb-4 text-on-surface-variant hover:text-primary transition-colors font-label-lg"
        >
            <?php echo __('Past'); ?>
        </button>

    </div>


    <!-- ===================================================== -->
    <!-- BOOKINGS -->
    <!-- ===================================================== -->

    <?php if (count($bookings) > 0): ?>


        <div
            id="bookingsList"
            class="space-y-6"
        >


            <?php foreach ($bookings as $booking): ?>


                <?php

                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

                $status = strtolower(
                    $booking['status']
                );


                /*
                |--------------------------------------------------------------------------
                | Booking Type
                |--------------------------------------------------------------------------
                */

                $booking_type =
                    in_array(
                        $status,
                        ['pending', 'confirmed']
                    )
                    ? 'upcoming'
                    : 'past';


                /*
                |--------------------------------------------------------------------------
                | Status Classes
                |--------------------------------------------------------------------------
                */

                $status_classes = [

                    'pending' =>
                        'bg-secondary-fixed text-on-secondary-fixed-variant',

                    'confirmed' =>
                        'bg-primary-fixed text-on-primary-fixed-variant',

                    'completed' =>
                        'bg-surface-container-highest text-on-surface-variant',

                    'cancelled' =>
                        'bg-error-container text-on-error-container'

                ];


                $status_class =
                    $status_classes[$status]
                    ?? 'bg-surface-container text-on-surface-variant';


                /*
                |--------------------------------------------------------------------------
                | Status Icons
                |--------------------------------------------------------------------------
                */

                $status_icons = [

                    'pending' =>
                        'schedule',

                    'confirmed' =>
                        'check_circle',

                    'completed' =>
                        'task_alt',

                    'cancelled' =>
                        'cancel'

                ];


                $status_icon =
                    $status_icons[$status]
                    ?? 'event';


                /*
                |--------------------------------------------------------------------------
                | Service Icon
                |--------------------------------------------------------------------------
                */

                $service_icon =
                    getServiceIcon(
                        $booking['category_name']
                    );


                /*
                |--------------------------------------------------------------------------
                | Date
                |--------------------------------------------------------------------------
                */

                $booking_date =
                    date(
                        'd/m/Y',
                        strtotime(
                            $booking['booking_date']
                        )
                    );

                ?>


                <!-- ================================================= -->
                <!-- BOOKING CARD -->
                <!-- ================================================= -->

                <div
                    class="booking-card bg-surface-container-lowest rounded-xl p-6 md:p-7 ambient-shadow flex flex-col lg:flex-row lg:items-center justify-between gap-7 border border-surface-variant"
                    data-type="<?php echo $booking_type; ?>"
                >


                    <!-- ================================================= -->
                    <!-- SERVICE INFORMATION -->
                    <!-- ================================================= -->

                    <div
                        class="flex items-start gap-5 min-w-0 flex-1"
                    >


                        <!-- SERVICE ICON -->

                        <div
                            class="w-16 h-16 min-w-[64px] rounded-full bg-surface-container flex items-center justify-center"
                        >

                            <span
                                class="material-symbols-outlined text-primary text-3xl"
                                style="font-variation-settings: 'FILL' 0, 'wght' 500;"
                            >
                                <?php echo $service_icon; ?>
                            </span>

                        </div>


                        <!-- INFORMATION -->

                        <div
                            class="min-w-0 flex-1"
                        >


                            <!-- TITLE + STATUS -->

                            <div
                                class="flex flex-wrap items-center gap-3 mb-2"
                            >

                                <h3
                                    class="font-headline-md text-headline-md text-on-background break-words"
                                >
                                    <?php
                                    echo htmlspecialchars(
                                        $booking['service_title']
                                    );
                                    ?>
                                </h3>


                                <span
                                    class="px-3 py-1.5 rounded-full <?php echo $status_class; ?> text-label-sm inline-flex items-center gap-1.5 whitespace-nowrap"
                                >

                                    <span
                                        class="material-symbols-outlined text-[15px]"
                                        style="font-variation-settings: 'FILL' 1;"
                                    >
                                        <?php echo $status_icon; ?>
                                    </span>


                                    <?php

                                    echo __(
                                        ucfirst(
                                            $booking['status']
                                        )
                                    );

                                    ?>

                                </span>

                            </div>


                            <!-- PROVIDER -->

                            <p
                                class="text-body-md text-on-surface-variant mb-3"
                            >

                                <?php echo __('Provider:'); ?>

                                <span
                                    class="font-semibold text-on-background"
                                >
                                    <?php
                                    echo htmlspecialchars(
                                        $booking['provider_name']
                                    );
                                    ?>
                                </span>

                            </p>


                            <!-- DATE + PRICE -->

                            <div
                                class="flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-on-surface-variant"
                            >


                                <!-- DATE -->

                                <div
                                    class="flex items-center gap-1.5"
                                >

                                    <span
                                        class="material-symbols-outlined text-[18px]"
                                    >
                                        calendar_today
                                    </span>


                                    <span>
                                        <?php echo $booking_date; ?>
                                    </span>

                                </div>


                                <span
                                    class="text-outline-variant"
                                >
                                    •
                                </span>


                                <!-- PRICE -->

                                <div
                                    class="flex items-center gap-1.5"
                                >

                                    <span
                                        class="material-symbols-outlined text-[18px]"
                                    >
                                        payments
                                    </span>


                                    <span
                                        class="font-semibold"
                                    >
                                        <?php
                                        echo number_format(
                                            $booking['total_price'],
                                            2
                                        );
                                        ?>

                                        د.ل
                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- ACTIONS -->
                    <!-- ================================================= -->

                    <div
                        class="flex flex-wrap items-center gap-3 lg:shrink-0"
                    >


                        <!-- VIEW -->

                        <a
                            href="booking-confirmation.php?id=<?php echo $booking['id']; ?>"
                            class="px-5 py-2.5 rounded-lg border border-outline text-on-surface-variant font-label-lg text-label-lg hover:bg-surface-container transition-colors flex items-center justify-center gap-2"
                        >

                            <span
                                class="material-symbols-outlined text-[18px]"
                            >
                                visibility
                            </span>


                            <?php echo __('View'); ?>

                        </a>


                        <!-- REVIEW -->

                        <?php if (
                            $booking['status'] == 'completed'
                            && !$booking['has_review']
                        ): ?>


                            <a
                                href="add-review.php?booking_id=<?php echo $booking['id']; ?>"
                                class="px-5 py-2.5 rounded-lg bg-primary text-on-primary font-label-lg text-label-lg hover:bg-surface-tint transition-colors flex items-center justify-center gap-2"
                            >

                                <span
                                    class="material-symbols-outlined text-[18px]"
                                    style="font-variation-settings: 'FILL' 1;"
                                >
                                    star
                                </span>


                                <?php echo __('Leave a Review'); ?>

                            </a>


                        <?php elseif (
                            $booking['status'] == 'completed'
                            && $booking['has_review']
                        ): ?>


                            <span
                                class="px-4 py-2.5 rounded-lg bg-surface-container text-primary font-label-lg text-label-lg flex items-center gap-2"
                            >

                                <span
                                    class="material-symbols-outlined text-[18px]"
                                    style="font-variation-settings: 'FILL' 1;"
                                >
                                    check_circle
                                </span>


                                <?php echo __('Rated'); ?>

                            </span>


                        <?php endif; ?>


                    </div>

                </div>


            <?php endforeach; ?>


        </div>


        <!-- ================================================= -->
        <!-- FILTER EMPTY -->
        <!-- ================================================= -->

        <div
            id="filterEmpty"
            class="hidden flex-col items-center justify-center py-20 text-center"
        >

            <div
                class="w-20 h-20 bg-surface-container rounded-full flex items-center justify-center mb-6"
            >

                <span
                    class="material-symbols-outlined text-primary text-4xl"
                >
                    event_busy
                </span>

            </div>


            <h2
                class="font-headline-md text-headline-md text-on-background mb-2"
            >
                <?php echo __('No bookings found'); ?>
            </h2>


            <p
                class="font-body-md text-body-md text-on-surface-variant max-w-sm"
            >
                <?php echo __('There are no bookings in this category.'); ?>
            </p>

        </div>


    <?php else: ?>


        <!-- ================================================= -->
        <!-- NO BOOKINGS -->
        <!-- ================================================= -->

        <div
            class="flex flex-col items-center justify-center py-20 text-center"
        >

            <div
                class="w-20 h-20 bg-surface-container rounded-full flex items-center justify-center mb-6"
            >

                <span
                    class="material-symbols-outlined text-primary text-4xl"
                >
                    event_busy
                </span>

            </div>


            <h2
                class="font-headline-md text-headline-md text-on-background mb-2"
            >
                <?php echo __('No bookings yet'); ?>
            </h2>


            <p
                class="font-body-md text-body-md text-on-surface-variant max-w-sm mb-8"
            >
                <?php echo __('Ready to get things done? Explore our local services to find the help you need.'); ?>
            </p>


            <a
                href="browse-services.php"
                class="bg-primary text-on-primary px-8 py-3 rounded-lg font-label-lg text-label-lg flex items-center gap-2 hover:bg-surface-tint transition-colors"
            >

                <span
                    class="material-symbols-outlined"
                >
                    search
                </span>


                <?php echo __('Browse Services'); ?>

            </a>

        </div>


    <?php endif; ?>


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

        <!-- LOGO -->

        <div
            class="flex flex-col gap-2 text-center md:text-right"
        >

            <span
                class="font-bold text-[#95442b] text-xl"
            >
                <?php echo __('Dabberha'); ?>
            </span>


            <p
                class="text-sm text-stone-600"
            >
                <?php echo __('© 2026 Dabberha Services. Built for the community.'); ?>
            </p>

        </div>


        <!-- FOOTER LINKS -->

        <div
            class="flex flex-wrap justify-center gap-6"
        >

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
<!-- FILTER JAVASCRIPT -->
<!-- ========================================================= -->

<script>

function filterBookings(type) {

    const cards =
        document.querySelectorAll('.booking-card');

    const allTab =
        document.getElementById('allTab');

    const upcomingTab =
        document.getElementById('upcomingTab');

    const pastTab =
        document.getElementById('pastTab');

    const filterEmpty =
        document.getElementById('filterEmpty');


    /*
    |--------------------------------------------------------------------------
    | Reset Tabs
    |--------------------------------------------------------------------------
    */

    const tabs = [
        allTab,
        upcomingTab,
        pastTab
    ];


    tabs.forEach(function(tab) {

        tab.classList.remove(
            'border-b-2',
            'border-primary',
            'text-primary',
            'font-semibold'
        );


        tab.classList.add(
            'text-on-surface-variant'
        );

    });


    /*
    |--------------------------------------------------------------------------
    | Active Tab
    |--------------------------------------------------------------------------
    */

    let activeTab;


    if (type === 'all') {

        activeTab = allTab;

    }

    else if (type === 'upcoming') {

        activeTab = upcomingTab;

    }

    else {

        activeTab = pastTab;

    }


    activeTab.classList.remove(
        'text-on-surface-variant'
    );


    activeTab.classList.add(
        'border-b-2',
        'border-primary',
        'text-primary',
        'font-semibold'
    );


    /*
    |--------------------------------------------------------------------------
    | Filter Cards
    |--------------------------------------------------------------------------
    */

    let visibleCount = 0;


    cards.forEach(function(card) {

        const cardType =
            card.getAttribute('data-type');


        if (
            type === 'all' ||
            cardType === type
        ) {

            card.classList.remove('hidden');

            visibleCount++;

        }

        else {

            card.classList.add('hidden');

        }

    });


    /*
    |--------------------------------------------------------------------------
    | Empty State
    |--------------------------------------------------------------------------
    */

    if (visibleCount === 0) {

        filterEmpty.classList.remove('hidden');

        filterEmpty.classList.add('flex');

    }

    else {

        filterEmpty.classList.add('hidden');

        filterEmpty.classList.remove('flex');

    }

}

</script>


</body>

</html>