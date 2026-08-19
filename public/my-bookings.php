<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('customer');

$customer_id = getUserId();

// جلب جميع حجوزات العميل
$stmt = $pdo->prepare("
    SELECT b.*, 
           s.title as service_title, 
           u.name as provider_name,
           (SELECT id FROM reviews WHERE booking_id = b.id) as has_review
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.provider_id = u.id
    WHERE b.customer_id = ?
    ORDER BY b.created_at DESC
");

$stmt->execute([$customer_id]);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Bookings - Dabberha</title>

    <!-- Plus Jakarta Sans + Material Symbols -->
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet"
    >

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

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
                        "on-primary-fixed-variant": "#7a2f18",
                        "inverse-primary": "#ffb59f",
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
                        "stack-lg": "32px",
                        "gutter": "24px",
                        "margin-mobile": "16px",
                        "margin-desktop": "40px",
                        "stack-md": "16px",
                        "stack-sm": "8px",
                        "container-max": "1200px",
                        "base": "8px"
                    },

                    fontFamily: {
                        "headline-md": ["Plus Jakarta Sans"],
                        "headline-lg": ["Plus Jakarta Sans"],
                        "display-lg": ["Plus Jakarta Sans"],
                        "label-lg": ["Plus Jakarta Sans"],
                        "label-sm": ["Plus Jakarta Sans"],
                        "body-lg": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"]
                    },

                    fontSize: {

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
                        ],

                        "display-lg": [
                            "48px",
                            {
                                "lineHeight": "56px",
                                "letterSpacing": "-0.02em",
                                "fontWeight": "700"
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

                        "label-sm": [
                            "12px",
                            {
                                "lineHeight": "16px",
                                "fontWeight": "500"
                            }
                        ],

                        "body-lg": [
                            "18px",
                            {
                                "lineHeight": "28px",
                                "fontWeight": "400"
                            }
                        ],

                        "body-md": [
                            "16px",
                            {
                                "lineHeight": "24px",
                                "fontWeight": "400"
                            }
                        ]
                    }
                }
            }
        };
    </script>

    <style>

        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24;

            vertical-align: middle;
        }

        .ambient-shadow {
            box-shadow:
                0 4px 20px -2px rgba(58, 47, 43, 0.05);
        }

    </style>

</head>


<body class="bg-[#F9F5F1] font-body-md text-[#3A2F2B] antialiased min-h-screen flex flex-col">


<!-- ========================================================= -->
<!-- NAVIGATION -->
<!-- ========================================================= -->

<header
    class="bg-[#F9F5F1] border-b border-[#C18B8B]/20 sticky top-0 z-50 shadow-sm shadow-[#3A2F2B]/5"
>

    <div
        class="flex justify-between items-center w-full px-6 py-4 max-w-7xl mx-auto"
    >

        <!-- Logo -->

        <a
            href="/local-services-platform/index.php"
            class="text-2xl font-bold text-[#CB6D51] font-['Plus_Jakarta_Sans']"
        >
            Dabberha
        </a>


        <!-- Navigation -->

        <nav class="hidden md:flex items-center space-x-8">

            <a
                href="browse-services.php"
                class="text-[#3A2F2B] font-medium hover:text-[#C18B8B] transition-colors duration-200"
            >
                Find Services
            </a>

            <a
                href="my-bookings.php"
                class="text-[#CB6D51] font-semibold"
            >
                My Bookings
            </a>

            <a
                href="#"
                class="text-[#3A2F2B] font-medium hover:text-[#C18B8B] transition-colors duration-200"
            >
                How it Works
            </a>

        </nav>


        <!-- User actions -->

        <div class="flex items-center space-x-4">

            <div class="hidden sm:flex items-center gap-2">

                <span class="material-symbols-outlined">
                    account_circle
                </span>

                <span class="font-medium">
                    <?php echo htmlspecialchars(getUserName()); ?>
                </span>

            </div>

            <a
                href="/local-services-platform/public/logout.php"
                class="bg-[#CB6D51] text-white px-5 py-2.5 rounded-lg font-semibold active:scale-95 transition-all shadow-sm"
            >
                Logout
            </a>

        </div>

    </div>

</header>


<!-- ========================================================= -->
<!-- MAIN -->
<!-- ========================================================= -->

<main
    class="max-w-4xl mx-auto px-6 py-16 min-h-screen w-full flex-grow"
>


    <!-- ===================================================== -->
    <!-- PAGE HEADER -->
    <!-- ===================================================== -->

    <div class="mb-12">

        <h1
            class="font-headline-lg text-headline-lg text-[#3A2F2B] mb-2"
        >
            My Bookings
        </h1>

        <p
            class="text-body-md font-body-md text-[#3A2F2B]/70"
        >
            Manage your upcoming and past home services.
        </p>

    </div>


    <!-- ===================================================== -->
    <!-- FILTER TABS -->
    <!-- ===================================================== -->

    <div
        class="flex gap-6 mb-10 border-b border-[#C18B8B]/10"
    >

        <button
            id="allTab"
            onclick="filterBookings('all')"
            class="pb-4 border-b-2 border-[#CB6D51] text-[#CB6D51] font-semibold text-label-lg"
        >
            All Bookings
        </button>

        <button
            id="upcomingTab"
            onclick="filterBookings('upcoming')"
            class="pb-4 text-[#3A2F2B]/50 hover:text-[#3A2F2B] transition-colors font-medium text-label-lg"
        >
            Upcoming
        </button>

        <button
            id="pastTab"
            onclick="filterBookings('past')"
            class="pb-4 text-[#3A2F2B]/50 hover:text-[#3A2F2B] transition-colors font-medium text-label-lg"
        >
            Past
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

                $status = strtolower($booking['status']);

                /*
                 * تحديد نوع الحجز لاستخدام Filter Tabs
                 *
                 * Upcoming:
                 * pending + confirmed
                 *
                 * Past:
                 * completed + cancelled
                 */

                $booking_type = in_array(
                    $status,
                    ['pending', 'confirmed']
                )
                    ? 'upcoming'
                    : 'past';


                /*
                 * ألوان الحالة
                 */

                $status_classes = [

                    'pending' =>
                        'bg-[#C18B8B]/10 text-[#C18B8B]',

                    'confirmed' =>
                        'bg-[#CB6D51]/10 text-[#CB6D51]',

                    'completed' =>
                        'bg-[#3A2F2B]/10 text-[#3A2F2B]',

                    'cancelled' =>
                        'bg-red-100 text-red-700'

                ];


                $status_class =
                    $status_classes[$status]
                    ?? 'bg-gray-100 text-gray-700';


                /*
                 * أيقونة الحالة
                 */

                $status_icons = [

                    'pending' => 'schedule',

                    'confirmed' => 'check_circle',

                    'completed' => 'task_alt',

                    'cancelled' => 'cancel'

                ];


                $status_icon =
                    $status_icons[$status]
                    ?? 'event';


                /*
                 * تنسيق التاريخ
                 */

                $booking_date = date(
                    'F d, Y',
                    strtotime($booking['booking_date'])
                );

                ?>

                <!-- BOOKING CARD -->

                <div
                    class="booking-card bg-white rounded-xl p-6 ambient-shadow flex flex-col md:flex-row md:items-center justify-between gap-6 border border-[#C18B8B]/5"
                    data-type="<?php echo $booking_type; ?>"
                >

                    <!-- Left side -->

                    <div class="flex items-center gap-5 min-w-0">

                        <!-- Service icon -->

                        <div
                            class="w-16 h-16 min-w-[64px] rounded-full overflow-hidden bg-[#F1DFD8] flex items-center justify-center"
                        >

                            <span
                                class="material-symbols-outlined text-[#CB6D51] text-3xl"
                            >
                                home_repair_service
                            </span>

                        </div>


                        <!-- Booking information -->

                        <div class="min-w-0">

                            <!-- Title + Status -->

                            <div
                                class="flex flex-wrap items-center gap-3 mb-1"
                            >

                                <h3
                                    class="font-headline-md text-headline-md text-[#3A2F2B] truncate"
                                >
                                    <?php
                                    echo htmlspecialchars(
                                        $booking['service_title']
                                    );
                                    ?>
                                </h3>


                                <span
                                    class="px-3 py-1 rounded-full <?php echo $status_class; ?> text-label-sm font-semibold inline-flex items-center gap-1"
                                >

                                    <span class="material-symbols-outlined text-[15px]">
                                        <?php echo $status_icon; ?>
                                    </span>

                                    <?php
                                    echo ucfirst(
                                        htmlspecialchars(
                                            $booking['status']
                                        )
                                    );
                                    ?>

                                </span>

                            </div>


                            <!-- Provider -->

                            <p
                                class="text-body-md font-body-md text-[#3A2F2B]/80 mb-1"
                            >

                                Provider:

                                <span class="font-semibold">

                                    <?php
                                    echo htmlspecialchars(
                                        $booking['provider_name']
                                    );
                                    ?>

                                </span>

                            </p>


                            <!-- Date -->

                            <div
                                class="flex flex-wrap items-center text-[#3A2F2B]/60 text-label-lg"
                            >

                                <span
                                    class="material-symbols-outlined text-[18px] mr-1"
                                >
                                    calendar_today
                                </span>

                                <span>
                                    <?php echo $booking_date; ?>
                                </span>


                                <span
                                    class="mx-2 text-[#C18B8B]/30"
                                >
                                    •
                                </span>


                                <span
                                    class="material-symbols-outlined text-[18px] mr-1"
                                >
                                    payments
                                </span>

                                <span>
                                    $<?php
                                    echo number_format(
                                        $booking['total_price'],
                                        2
                                    );
                                    ?>
                                </span>

                            </div>

                        </div>

                    </div>


                    <!-- Right side -->

                    <div
                        class="flex flex-wrap items-center gap-3 md:justify-end"
                    >

                        <!-- View -->

                        <a
                            href="booking-confirmation.php?id=<?php echo $booking['id']; ?>"
                            class="px-6 py-2.5 rounded-lg border border-[#C18B8B]/30 text-[#3A2F2B] font-semibold text-label-lg hover:bg-stone-50 transition-colors active:scale-95 flex items-center gap-2"
                        >

                            <span class="material-symbols-outlined text-[18px]">
                                visibility
                            </span>

                            View

                        </a>


                        <!-- Rate -->

                        <?php if (
                            $booking['status'] == 'completed'
                            && !$booking['has_review']
                        ): ?>

                            <a
                                href="add-review.php?booking_id=<?php echo $booking['id']; ?>"
                                class="px-6 py-2.5 rounded-lg bg-[#CB6D51] text-white font-semibold text-label-lg hover:opacity-90 transition-all active:scale-95 shadow-md flex items-center gap-2"
                            >

                                <span class="material-symbols-outlined text-[18px]">
                                    star
                                </span>

                                Leave a Review

                            </a>


                        <?php elseif (
                            $booking['status'] == 'completed'
                            && $booking['has_review']
                        ): ?>

                            <span
                                class="px-4 py-2.5 rounded-lg bg-green-100 text-green-700 font-semibold text-label-lg flex items-center gap-2"
                            >

                                <span class="material-symbols-outlined text-[18px]">
                                    check_circle
                                </span>

                                Rated

                            </span>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>


        <!-- ================================================= -->
        <!-- FILTER EMPTY MESSAGE -->
        <!-- ================================================= -->

        <div
            id="filterEmpty"
            class="hidden flex-col items-center justify-center py-20 text-center"
        >

            <div
                class="w-20 h-20 bg-[#F1DFD8] rounded-full flex items-center justify-center mb-6"
            >

                <span
                    class="material-symbols-outlined text-[#CB6D51] text-4xl"
                >
                    event_busy
                </span>

            </div>

            <h2
                class="font-headline-md text-headline-md text-[#3A2F2B] mb-2"
            >
                No bookings found
            </h2>

            <p
                class="text-body-md text-[#3A2F2B]/60 max-w-sm mb-8"
            >
                There are no bookings in this category.
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
                class="w-20 h-20 bg-[#F1DFD8] rounded-full flex items-center justify-center mb-6"
            >

                <span
                    class="material-symbols-outlined text-[#CB6D51] text-4xl"
                >
                    event_busy
                </span>

            </div>


            <h2
                class="font-headline-md text-headline-md text-[#3A2F2B] mb-2"
            >
                No bookings yet
            </h2>


            <p
                class="text-body-md text-[#3A2F2B]/60 max-w-sm mb-8"
            >
                Ready to get things done? Explore our local services to find
                the help you need.
            </p>


            <a
                href="browse-services.php"
                class="bg-[#CB6D51] text-white px-8 py-3 rounded-lg font-semibold active:scale-95 transition-all flex items-center gap-2"
            >

                <span class="material-symbols-outlined">
                    search
                </span>

                Browse Services

            </a>

        </div>

    <?php endif; ?>

</main>


<!-- ========================================================= -->
<!-- FOOTER -->
<!-- ========================================================= -->

<footer
    class="bg-[#F9F5F1] border-t border-stone-200"
>

    <div
        class="flex flex-col md:flex-row justify-between items-center w-full px-8 py-12 max-w-7xl mx-auto space-y-4 md:space-y-0"
    >

        <div
            class="flex flex-col items-center md:items-start gap-2"
        >

            <div
                class="text-lg font-bold text-[#CB6D51] font-['Plus_Jakarta_Sans']"
            >
                Dabberha
            </div>

            <p
                class="font-['Plus_Jakarta_Sans'] text-sm text-[#3A2F2B]"
            >
                © <?php echo date('Y'); ?> Dabberha.
                All rights reserved.
            </p>

        </div>


        <div
            class="flex flex-wrap justify-center gap-x-8 gap-y-4"
        >

            <a
                href="#"
                class="font-['Plus_Jakarta_Sans'] text-sm text-[#3A2F2B] hover:text-[#CB6D51] hover:underline transition-all"
            >
                Privacy Policy
            </a>

            <a
                href="#"
                class="font-['Plus_Jakarta_Sans'] text-sm text-[#3A2F2B] hover:text-[#CB6D51] hover:underline transition-all"
            >
                Terms of Service
            </a>

            <a
                href="#"
                class="font-['Plus_Jakarta_Sans'] text-sm text-[#3A2F2B] hover:text-[#CB6D51] hover:underline transition-all"
            >
                Help Center
            </a>

            <a
                href="#"
                class="font-['Plus_Jakarta_Sans'] text-sm text-[#3A2F2B] hover:text-[#CB6D51] hover:underline transition-all"
            >
                Contact Us
            </a>

        </div>

    </div>

</footer>


<!-- ========================================================= -->
<!-- FILTER TABS JAVASCRIPT -->
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


    // إعادة ضبط الـ Tabs

    const tabs = [
        allTab,
        upcomingTab,
        pastTab
    ];

    tabs.forEach(function(tab) {

        tab.classList.remove(
            'border-b-2',
            'border-[#CB6D51]',
            'text-[#CB6D51]',
            'font-semibold'
        );

        tab.classList.add(
            'text-[#3A2F2B]/50',
            'font-medium'
        );

    });


    // تفعيل الـ Tab المختار

    let activeTab;

    if (type === 'all') {

        activeTab = allTab;

    } else if (type === 'upcoming') {

        activeTab = upcomingTab;

    } else {

        activeTab = pastTab;

    }


    activeTab.classList.remove(
        'text-[#3A2F2B]/50',
        'font-medium'
    );

    activeTab.classList.add(
        'border-b-2',
        'border-[#CB6D51]',
        'text-[#CB6D51]',
        'font-semibold'
    );


    // إظهار الحجوزات المناسبة

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

        } else {

            card.classList.add('hidden');

        }

    });


    // إظهار رسالة إذا لم توجد حجوزات في الفئة

    if (visibleCount === 0) {

        filterEmpty.classList.remove('hidden');
        filterEmpty.classList.add('flex');

    } else {

        filterEmpty.classList.add('hidden');
        filterEmpty.classList.remove('flex');

    }

}

</script>


</body>
</html>