<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';

requireLogin();

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($booking_id == 0) {
    header('Location: browse-services.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Booking
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT 
        b.*, 
        s.title as service_title, 
        u.name as provider_name 
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.provider_id = u.id
    WHERE b.id = ? 
      AND b.customer_id = ?
");

$stmt->execute([
    $booking_id,
    getUserId()
]);

$booking = $stmt->fetch();


if (!$booking) {
    header('Location: browse-services.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Check Existing Review
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id 
    FROM reviews 
    WHERE booking_id = ?
");

$stmt->execute([$booking_id]);

$has_review = $stmt->fetch() ? true : false;

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
        content="width=device-width, initial-scale=1.0"
        name="viewport"
    >

    <title>
        <?php echo __('Booking Confirmed'); ?> - دبرها    </title>


    <!-- ===================================================== -->
    <!-- Tailwind CSS -->
    <!-- ===================================================== -->

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>


    <!-- ===================================================== -->
    <!-- Tajawal Arabic Font -->
    <!-- ===================================================== -->

    <link
        href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- ===================================================== -->
    <!-- Material Symbols -->
    <!-- ===================================================== -->

    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap"
        rel="stylesheet"
    >


    <!-- ===================================================== -->
    <!-- Tailwind Configuration -->
    <!-- ===================================================== -->

    <script>

        tailwind.config = {

            darkMode: "class",

            theme: {

                extend: {

                    colors: {

                        "surface-tint": "#98462d",
                        "on-error": "#ffffff",
                        "inverse-primary": "#ffb59f",
                        "on-tertiary-fixed-variant": "#484744",
                        "surface-container-high": "#f7e4de",
                        "tertiary-fixed": "#e6e2de",
                        "secondary-fixed": "#ffdad9",
                        "surface-container-low": "#fff1ec",
                        "surface": "#fff8f6",
                        "on-error-container": "#93000a",
                        "on-secondary-fixed": "#321112",
                        "on-surface-variant": "#55433d",
                        "surface-container-lowest": "#ffffff",
                        "outline-variant": "#dbc1ba",
                        "on-tertiary-container": "#fffbff",
                        "primary-container": "#b45b40",
                        "surface-variant": "#f1dfd8",
                        "inverse-on-surface": "#ffede7",
                        "on-surface": "#231916",
                        "primary": "#95442b",
                        "on-tertiary-fixed": "#1c1c19",
                        "primary-fixed-dim": "#ffb59f",
                        "secondary-container": "#ffc3c2",
                        "on-primary": "#ffffff",
                        "on-secondary-container": "#7b4d4e",
                        "on-primary-container": "#fffbff",
                        "surface-bright": "#fff8f6",
                        "on-secondary-fixed-variant": "#653b3c",
                        "on-secondary": "#ffffff",
                        "error-container": "#ffdad6",
                        "background": "#fff8f6",
                        "error": "#ba1a1a",
                        "on-primary-fixed-variant": "#7a2f18",
                        "secondary-fixed-dim": "#f3b8b8",
                        "on-background": "#231916",
                        "on-tertiary": "#ffffff",
                        "tertiary-fixed-dim": "#c9c6c2",
                        "surface-container": "#fdeae4",
                        "primary-fixed": "#ffdbd1",
                        "surface-dim": "#e9d6d0",
                        "tertiary": "#5d5c59",
                        "surface-container-highest": "#f1dfd8",
                        "on-primary-fixed": "#3a0a00",
                        "inverse-surface": "#392e2a",
                        "secondary": "#805252",
                        "tertiary-container": "#767471",
                        "outline": "#88726c"
                    },


                    borderRadius: {

                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"

                    },


                    spacing: {

                        "gutter": "24px",
                        "margin-mobile": "16px",
                        "margin-desktop": "40px",
                        "stack-sm": "8px",
                        "stack-md": "16px",
                        "stack-lg": "32px"

                    },


                    fontFamily: {

                        "body-md": ["Tajawal"],
                        "label-sm": ["Tajawal"],
                        "label-lg": ["Tajawal"],
                        "headline-md": ["Tajawal"],
                        "headline-lg": ["Tajawal"],
                        "display-lg": ["Tajawal"]

                    },


                    fontSize: {

                        "body-md": [
                            "16px",
                            {
                                "lineHeight": "24px",
                                "fontWeight": "400"
                            }
                        ],
                         "display-lg": [
                            "24px",
                            {
                                lineHeight: "56px",
                                letterSpacing: "-0.02em",
                                fontWeight: "700"
                            }
                        ],

                        "label-sm": [
                            "12px",
                            {
                                "lineHeight": "16px",
                                "fontWeight": "500"
                            }
                        ],

                        "label-lg": [
                            "14px",
                            {
                                "lineHeight": "20px",
                                "letterSpacing": "0.01em",
                                "fontWeight": "600"
                            }
                        ],

                        "headline-md": [
                            "24px",
                            {
                                "lineHeight": "32px",
                                "fontWeight": "600"
                            }
                        ],

                        "headline-lg": [
                            "32px",
                            {
                                "lineHeight": "40px",
                                "letterSpacing": "-0.01em",
                                "fontWeight": "600"
                            }
                        ]

                    }

                }

            }

        };

    </script>


    <!-- ===================================================== -->
    <!-- Custom CSS -->
    <!-- ===================================================== -->

    <style>

        .shadow-ambient {

            box-shadow:
                0 4px 20px rgba(85, 67, 61, 0.08);

        }


        .shadow-ambient-hover:hover {

            box-shadow:
                0 8px 24px rgba(85, 67, 61, 0.12);

        }


        body {

            font-family: 'Tajawal', sans-serif;

        }


        

        .material-symbols-outlined {

            font-family: 'Material Symbols Outlined' !important;

            font-weight: normal;

            font-style: normal;

            font-size: 24px;

            line-height: 1;

            letter-spacing: normal;

            text-transform: none;

            display: inline-block;

            white-space: nowrap;

            word-wrap: normal;

            direction: ltr;

            -webkit-font-feature-settings: 'liga';

            -webkit-font-smoothing: antialiased;

            font-feature-settings: 'liga';

        }

    </style>

</head>


<body
    class="bg-background text-on-background font-body-md min-h-screen flex flex-col"
>


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

<main
    class="flex-grow flex items-center justify-center py-16 md:py-24 px-4 md:px-margin-desktop"
>


    <!-- Confirmation Card -->

    <div
        class="bg-surface-container-lowest w-full max-w-2xl rounded-xl shadow-ambient p-6 md:p-10 flex flex-col items-center text-center"
    >


        <!-- ================================================= -->
        <!-- SUCCESS ICON -->
        <!-- ================================================= -->

        <div
            class="w-20 h-20 bg-[#e6f4ea] rounded-full flex items-center justify-center mb-8"
        >

            <span
                class="material-symbols-outlined text-[40px] text-[#1e8e3e]"
                style="font-variation-settings: 'FILL' 1;"
            >
                check_circle
            </span>

        </div>


        <!-- ================================================= -->
        <!-- TITLE -->
        <!-- ================================================= -->

        <h1
            class="font-headline-lg text-on-surface mb-2 flex items-center gap-2 justify-center"
        >

            <?php echo __('Booking Confirmed!'); ?>


            <span
                class="material-symbols-outlined text-[#1e8e3e]"
                style="font-variation-settings: 'FILL' 1;"
            >
                check_box
            </span>

        </h1>


        <p
            class="font-body-md text-on-surface-variant mb-12"
        >

            <?php echo __('Your service has been booked successfully.'); ?>

        </p>


        <hr
            class="w-full border-outline-variant mb-8"
        />


        <!-- ================================================= -->
        <!-- BOOKING DETAILS -->
        <!-- ================================================= -->

        <div
            class="w-full text-right space-y-2 mb-8"
        >


            <!-- Service -->

            <div
                class="flex flex-col sm:flex-row sm:items-center py-3 border-b border-surface-variant"
            >

                <span
                    class="font-label-lg text-on-surface w-32 shrink-0 mb-1 sm:mb-0 text-right"
                >

                    <?php echo __('Service:'); ?>

                </span>


                <span
                    class="font-body-md text-on-surface-variant"
                >

                    <?php

                    echo htmlspecialchars(
                        $booking['service_title']
                    );

                    ?>

                </span>

            </div>


            <!-- Provider -->

            <div
                class="flex flex-col sm:flex-row sm:items-center py-3 border-b border-surface-variant"
            >

                <span
                    class="font-label-lg text-on-surface w-32 shrink-0 mb-1 sm:mb-0 text-right"
                >

                    <?php echo __('Provider:'); ?>

                </span>


                <span
                    class="font-body-md text-on-surface-variant"
                >

                    <?php

                    echo htmlspecialchars(
                        $booking['provider_name']
                    );

                    ?>

                </span>

            </div>


            <!-- Date -->

            <div
                class="flex flex-col sm:flex-row sm:items-center py-3 border-b border-surface-variant"
            >

                <span
                    class="font-label-lg text-on-surface w-32 shrink-0 mb-1 sm:mb-0 text-right"
                >

                    <?php echo __('Date:'); ?>

                </span>


                <span
                    class="font-body-md text-on-surface-variant"
                    dir="ltr"
                >

                    <?php

                    echo date(
                        'F d, Y',
                        strtotime(
                            $booking['booking_date']
                        )
                    );

                    ?>

                </span>

            </div>


            <!-- Time -->

            <div
                class="flex flex-col sm:flex-row sm:items-center py-3 border-b border-surface-variant"
            >

                <span
                    class="font-label-lg text-on-surface w-32 shrink-0 mb-1 sm:mb-0 text-right"
                >

                    <?php echo __('Time:'); ?>

                </span>


                <span
                    class="font-body-md text-on-surface-variant"
                    dir="ltr"
                >

                    <?php

                    echo date(
                        'h:i A',
                        strtotime(
                            $booking['booking_time']
                        )
                    );

                    ?>

                </span>

            </div>


            <!-- Total Price -->

            <div
                class="flex flex-col sm:flex-row sm:items-center py-3 border-b border-surface-variant"
            >

                <span
                    class="font-label-lg text-on-surface w-32 shrink-0 mb-1 sm:mb-0 text-right"
                >

                    <?php echo __('Total Price:'); ?>

                </span>


                <span
                    class="font-body-md text-on-surface-variant font-semibold"
                    dir="rtl"
                >

                    <?php

                    echo number_format(
                        $booking['total_price'],
                        2
                    );

                    ?>

                    &nbsp;دل

                </span>

            </div>


            <!-- Status -->

            <div
                class="flex flex-col sm:flex-row sm:items-center py-3"
            >

                <span
                    class="font-label-lg text-on-surface w-32 shrink-0 mb-2 sm:mb-0 text-right"
                >

                    <?php echo __('Status:'); ?>

                </span>


                <?php

                /*
                |--------------------------------------------------------------------------
                | Status Styles + Material Icons
                |--------------------------------------------------------------------------
                */

                $status_styles = [

                    'pending' => [
                        'bg-[#fff8e1]',
                        'text-[#f57f17]',
                        'schedule'
                    ],

                    'confirmed' => [
                        'bg-[#e3f2fd]',
                        'text-[#1976d2]',
                        'check_circle'
                    ],

                    'completed' => [
                        'bg-[#e6f4ea]',
                        'text-[#1e8e3e]',
                        'check_circle'
                    ],

                    'cancelled' => [
                        'bg-[#ffebee]',
                        'text-[#c62828]',
                        'cancel'
                    ]

                ];


                $current_status =
                    $status_styles[$booking['status']]
                    ?? [
                        'bg-stone-100',
                        'text-stone-600',
                        'info'
                    ];

                ?>


                <span
                    class="inline-flex items-center gap-1.5 w-fit px-3 py-1 rounded-full font-label-sm <?php echo $current_status[0]; ?> <?php echo $current_status[1]; ?>"
                >

                    <span
                        class="material-symbols-outlined text-[16px]"
                        style="font-variation-settings: 'FILL' 1;"
                    >

                        <?php echo $current_status[2]; ?>

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

        </div>


        <hr
            class="w-full border-outline-variant mb-8"
        />


        <!-- ================================================= -->
        <!-- STATUS / REVIEW MESSAGE -->
        <!-- ================================================= -->

        <?php if ($booking['status'] == 'completed' && !$has_review): ?>


            <!-- Completed - Can Review -->

            <div
                class="w-full bg-[#fff8e1] border border-[#ffe082] rounded-lg p-4 flex items-start gap-4 mb-8 text-right"
            >

                <span
                    class="material-symbols-outlined text-[#f57f17] shrink-0 mt-0.5"
                    style="font-variation-settings: 'FILL' 1;"
                >
                    star
                </span>


                <div>

                    <p
                        class="font-label-lg text-[#f57f17] mb-1"
                    >

                        <?php echo __('Service Completed'); ?>

                    </p>


                    <p
                        class="font-body-md text-[#795548]"
                    >

                        <?php echo __('Your service has been completed. You can now rate your experience.'); ?>

                    </p>

                </div>

            </div>


            <!-- Rate Button -->

            <a
                href="add-review.php?booking_id=<?php echo $booking_id; ?>"
                class="w-full sm:w-auto bg-[#CB6D51] hover:bg-primary-container text-white font-label-lg px-6 py-3 rounded-full flex items-center justify-center gap-2 transition-colors duration-200 shadow-ambient shadow-ambient-hover mb-8"
            >

                <span
                    class="material-symbols-outlined text-[20px]"
                    style="font-variation-settings: 'FILL' 1;"
                >
                    star
                </span>


                <?php echo __('Rate This Service'); ?>

            </a>


        <?php elseif ($booking['status'] == 'completed' && $has_review): ?>


            <!-- Already Rated -->

            <div
                class="w-full bg-[#e6f4ea] border border-[#b7dfc1] rounded-lg p-4 flex items-start gap-4 mb-8 text-right"
            >

                <span
                    class="material-symbols-outlined text-[#1e8e3e] shrink-0 mt-0.5"
                    style="font-variation-settings: 'FILL' 1;"
                >
                    star
                </span>


                <p
                    class="font-body-md text-[#1e8e3e]"
                >

                    <?php echo __('You have already rated this service.'); ?>

                </p>

            </div>


        <?php elseif ($booking['status'] == 'pending'): ?>


            <!-- Pending -->

            <div
                class="w-full bg-[#e0f7fa] border border-[#b2ebf2] rounded-lg p-4 flex items-start gap-4 mb-8 text-right"
            >

                <span
                    class="material-symbols-outlined text-[#006064] shrink-0 mt-0.5"
                >
                    schedule
                </span>


                <p
                    class="font-body-md text-[#006064]"
                >

                    <?php echo __('Your booking is pending provider confirmation.'); ?>

                    <br>

                    <?php echo __('You can rate the service after it\'s completed.'); ?>

                </p>

            </div>


        <?php elseif ($booking['status'] == 'confirmed'): ?>


            <!-- Confirmed -->

            <div
                class="w-full bg-[#e3f2fd] border border-[#bbdefb] rounded-lg p-4 flex items-start gap-4 mb-8 text-right"
            >

                <span
                    class="material-symbols-outlined text-[#1976d2] shrink-0 mt-0.5"
                    style="font-variation-settings: 'FILL' 1;"
                >
                    check_circle
                </span>


                <p
                    class="font-body-md text-[#1976d2]"
                >

                    <?php echo __('Your booking has been confirmed!'); ?>

                    <br>

                    <?php echo __('You can rate the service after it\'s completed.'); ?>

                </p>

            </div>


        <?php elseif ($booking['status'] == 'cancelled'): ?>


            <!-- Cancelled -->

            <div
                class="w-full bg-[#ffebee] border border-[#ffcdd2] rounded-lg p-4 flex items-start gap-4 mb-8 text-right"
            >

                <span
                    class="material-symbols-outlined text-[#c62828] shrink-0 mt-0.5"
                >
                    cancel
                </span>


                <p
                    class="font-body-md text-[#c62828]"
                >

                    <?php echo __('This booking was cancelled.'); ?>

                </p>

            </div>


        <?php endif; ?>


        <!-- ================================================= -->
        <!-- ACTION BUTTONS -->
        <!-- ================================================= -->

        <div
            class="flex flex-wrap items-center justify-center gap-4 w-full"
        >


            <!-- Browse More -->

            <a
                href="browse-services.php"
                class="bg-primary hover:bg-primary-container text-on-primary font-label-lg px-6 py-3 rounded-full flex items-center gap-2 transition-colors duration-200 shadow-ambient shadow-ambient-hover"
            >

                <span
                    class="material-symbols-outlined text-[20px]"
                >
                    search
                </span>


                <?php echo __('Browse More Services'); ?>

            </a>


            <!-- Home -->

            <a
                href="/local-services-platform/index.php"
                class="bg-surface hover:bg-surface-variant text-on-surface border border-outline font-label-lg px-6 py-3 rounded-full flex items-center gap-2 transition-colors duration-200"
            >

                <span
                    class="material-symbols-outlined text-[20px]"
                    style="font-variation-settings: 'FILL' 1;"
                >
                    home
                </span>


                <?php echo __('Home'); ?>

            </a>


            <!-- My Bookings -->

            <a
                href="my-bookings.php"
                class="bg-surface hover:bg-primary-fixed text-primary border border-primary font-label-lg px-6 py-3 rounded-full flex items-center gap-2 transition-colors duration-200"
            >

                <span
                    class="material-symbols-outlined text-[20px]"
                >
                    format_list_bulleted
                </span>


                <?php echo __('My Bookings'); ?>

            </a>

        </div>

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


        <!-- روابط التذييل -->

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


</body>

</html>