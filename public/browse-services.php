<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';


// ============================================
// 🗺️ ميزة الموقع الجغرافي (Geolocation)
// ============================================

// الحصول على موقع المستخدم من الرابط
$user_lat = isset($_GET['lat']) ? (float)$_GET['lat'] : null;
$user_lng = isset($_GET['lng']) ? (float)$_GET['lng'] : null;


// حفظ الموقع في الجلسة إذا كان موجوداً
if ($user_lat && $user_lng) {
    $_SESSION['user_lat'] = $user_lat;
    $_SESSION['user_lng'] = $user_lng;
}


// إذا لم يكن هناك موقع في الرابط، حاول جلبه من الجلسة
if (!$user_lat || !$user_lng) {
    $user_lat = $_SESSION['user_lat'] ?? null;
    $user_lng = $_SESSION['user_lng'] ?? null;
}


// ============================================
// 🧠 نظام التوصيات الذكي
// ============================================

$rating_weight = 0.7;
$distance_weight = 0.3;


// جلب جميع الفئات للفلتر
$categories = $pdo->query(
    "SELECT * FROM categories ORDER BY name"
)->fetchAll();


// ============================================
// الفلاتر
// ============================================

$category_filter = isset($_GET['category'])
    ? (int)$_GET['category']
    : 0;


$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';


$search_term = $search;


// ============================================
// 📊 استعلام SQL
// ============================================

$sql = "SELECT s.*, 
        u.name as provider_name, 
        u.phone as provider_phone, 
        c.name as category_name, 
        u.latitude as provider_lat, 
        u.longitude as provider_lng,
        COALESCE(ROUND(AVG(r.rating), 1), 0) as avg_rating,
        COUNT(r.id) as review_count";


// إذا كان المستخدم لديه موقع
if ($user_lat && $user_lng) {

    $sql .= ",
        (6371 * acos(
            cos(radians($user_lat)) * cos(radians(u.latitude)) * 
            cos(radians(u.longitude) - radians($user_lng)) + 
            sin(radians($user_lat)) * sin(radians(u.latitude))
        )) as distance,

        (
            COALESCE(ROUND(AVG(r.rating), 1), 0) * $rating_weight + 
            (
                1 / (
                    1 + (
                        6371 * acos(
                            cos(radians($user_lat)) * cos(radians(u.latitude)) * 
                            cos(radians(u.longitude) - radians($user_lng)) + 
                            sin(radians($user_lat)) * sin(radians(u.latitude))
                        )
                    )
                )
            ) * $distance_weight
        ) as recommendation_score";

} else {

    // إذا لم يكن هناك موقع، استخدم التقييم فقط

    $sql .= ",
        COALESCE(ROUND(AVG(r.rating), 1), 0) as recommendation_score";
}


// ============================================
// FROM / JOIN
// ============================================

$sql .= " FROM services s
        JOIN users u ON s.provider_id = u.id
        JOIN categories c ON s.category_id = c.id
        LEFT JOIN reviews r ON r.provider_id = u.id
        WHERE u.status = 'active'";


// ============================================
// فلترة الفئة
// ============================================

if ($category_filter > 0) {

    $sql .= " AND s.category_id = $category_filter";

}


// ============================================
// البحث
// ============================================

if (!empty($search)) {

    $search_param = $pdo->quote("%$search%");

    $sql .= " AND (
        s.title LIKE $search_param 
        OR s.description LIKE $search_param 
        OR u.name LIKE $search_param
    )";

}


// ============================================
// GROUP BY
// ============================================

$sql .= " GROUP BY s.id";


// ============================================
// ترتيب النتائج
// ============================================

if ($user_lat && $user_lng) {

    $sql .= " ORDER BY recommendation_score DESC, distance ASC";

} else {

    $sql .= " ORDER BY recommendation_score DESC, s.id DESC";

}


$services = $pdo->query($sql)->fetchAll();

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
        <?php echo __('Browse Services - Dabberha'); ?>
    </title>


    <!-- ========================================================= -->
    <!-- GOOGLE FONT -->
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
    <!-- TAILWIND -->
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
                        "base": "8px",
                        "stack-md": "16px",

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

                        "body-md": [
                            "16px",
                            {
                                "lineHeight": "24px",
                                "fontWeight": "400"
                            }
                        ],

                        "headline-md": [
                            "24px",
                            {
                                "lineHeight": "32px",
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

                        "headline-lg": [
                            "32px",
                            {
                                "lineHeight": "40px",
                                "letterSpacing": "-0.01em",
                                "fontWeight": "600"
                            }
                        ],

                        "body-lg": [
                            "18px",
                            {
                                "lineHeight": "28px",
                                "fontWeight": "400"
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


        /*
        |--------------------------------------------------------------------------
        | Category Dropdown
        |--------------------------------------------------------------------------
        */

        .category-menu {

            box-shadow:
                0 10px 30px rgba(85, 67, 61, 0.12);

        }


        .category-arrow {

            transition:
                transform 0.2s ease;

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
            grid_view
        </span>


        <div>

            <h1
                class="font-display-lg text-display-lg text-on-background"
            >
                <?php echo __('Browse Services'); ?>
            </h1>


            <p
                class="font-body-lg text-body-lg text-on-surface-variant mt-2"
            >
                <?php echo __('Find trusted service providers near you'); ?>
            </p>

        </div>

    </div>


    <!-- ===================================================== -->
    <!-- SMART RECOMMENDATIONS -->
    <!-- ===================================================== -->

    <?php if ($user_lat && $user_lng): ?>

        <div
            class="bg-surface-container-low rounded-xl p-4 mb-6 flex items-start gap-3"
        >

            <span
                class="material-symbols-outlined text-primary"
            >
                auto_awesome
            </span>


            <div>

                <p
                    class="font-label-lg text-primary"
                >
                    <?php echo __('Smart Recommendations'); ?>
                </p>


                <p
                    class="text-sm text-on-surface-variant mt-1"
                >

                    <?php echo __('Services are ranked by'); ?>


                    <span
                        class="font-semibold text-primary"
                    >
                        <?php echo __('Rating'); ?>
                        (<?php echo $rating_weight * 100; ?>%)
                    </span>


                    +


                    <span
                        class="font-semibold text-primary"
                    >
                        <?php echo __('Proximity'); ?>
                        (<?php echo $distance_weight * 100; ?>%)
                    </span>

                </p>

            </div>

        </div>

    <?php endif; ?>


    <!-- ===================================================== -->
    <!-- SEARCH / FILTER SECTION -->
    <!-- ===================================================== -->

    <div
        class="bg-surface-container-lowest ambient-shadow rounded-xl p-6 mb-12 flex flex-col md:flex-row gap-4 items-center"
    >

        <form
            method="GET"
            action=""
            class="w-full flex flex-col md:flex-row gap-4 items-center"
        >


            <!-- ================================================= -->
            <!-- SEARCH -->
            <!-- ================================================= -->

            <div
                class="flex-grow w-full md:w-auto relative"
            >

                <span
                    class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant"
                >
                    search
                </span>


                <input
                    type="text"
                    name="search"
                    value="<?php echo htmlspecialchars($search_term); ?>"
                    placeholder="<?php echo __('Search services...'); ?>"
                    class="w-full pr-12 pl-4 py-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-secondary focus:ring-1 focus:ring-secondary transition-colors outline-none font-body-md text-on-background"
                >

            </div>


            <!-- ================================================= -->
            <!-- CUSTOM CATEGORY DROPDOWN -->
            <!-- ================================================= -->

            <div
                class="w-full md:w-64 relative"
                id="categoryDropdown"
            >


                <!-- Hidden input -->

                <input
                    type="hidden"
                    name="category"
                    id="categoryInput"
                    value="<?php echo $category_filter; ?>"
                >


                <!-- Dropdown Box -->

                <div
                    class="w-full h-[50px] rounded-lg border border-outline-variant bg-surface-container-lowest flex items-center"
                >


                    <!-- ================================================= -->
                    <!-- SELECTED CATEGORY TEXT -->
                    <!-- ================================================= -->

                    <div
                        id="selectedCategory"
                        class="flex-1 px-4 text-on-background font-body-md select-none"
                    >

                        <?php

                        $selected_category_name =
                            __('All Categories');


                        if ($category_filter > 0) {

                            foreach ($categories as $cat) {

                                if (
                                    $category_filter ==
                                    $cat['id']
                                ) {

                                    $selected_category_name =
                                        $cat['name'];

                                    break;

                                }

                            }

                        }


                        echo htmlspecialchars(
                            $selected_category_name
                        );

                        ?>

                    </div>


                    <!-- ================================================= -->
                    <!-- ARROW BUTTON -->
                    <!-- ONLY THIS AREA IS CLICKABLE -->
                    <!-- ================================================= -->

                    <button
                        type="button"
                        id="categoryToggle"
                        class="w-[50px] h-full shrink-0 flex items-center justify-center text-on-surface-variant hover:text-primary transition-colors"
                        aria-label="<?php echo __('Open categories'); ?>"
                        aria-expanded="false"
                    >

                        <span
                            id="categoryArrow"
                            class="material-symbols-outlined category-arrow"
                        >
                            expand_more
                        </span>

                    </button>

                </div>


                <!-- ================================================= -->
                <!-- DROPDOWN MENU -->
                <!-- ================================================= -->

                <div
                    id="categoryMenu"
                    class="category-menu hidden absolute top-[58px] right-0 left-0 z-50 bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden"
                >


                    <!-- ALL CATEGORIES -->

                    <button
                        type="button"
                        class="category-option w-full text-right px-4 py-3 text-on-background hover:bg-surface-container-low transition-colors"
                        data-value="0"
                        data-label="<?php echo htmlspecialchars(__('All Categories')); ?>"
                    >

                        <?php echo __('All Categories'); ?>

                    </button>


                    <!-- CATEGORIES -->

                    <?php foreach ($categories as $cat): ?>

                        <button
                            type="button"
                            class="category-option w-full text-right px-4 py-3 text-on-background hover:bg-surface-container-low transition-colors"
                            data-value="<?php echo $cat['id']; ?>"
                            data-label="<?php echo htmlspecialchars($cat['name']); ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $cat['name']
                            );
                            ?>

                        </button>

                    <?php endforeach; ?>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- BUTTONS -->
            <!-- ================================================= -->

            <div
                class="flex gap-4 w-full md:w-auto"
            >


                <!-- SEARCH -->

                <button
                    type="submit"
                    class="bg-primary text-on-primary px-8 py-3 rounded-lg font-label-lg text-label-lg flex items-center gap-2 hover:bg-surface-tint transition-colors flex-grow md:flex-grow-0 justify-center"
                >

                    <span
                        class="material-symbols-outlined text-sm"
                    >
                        search
                    </span>


                    <?php echo __('Search'); ?>

                </button>


                <!-- RESET -->

                <a
                    href="browse-services.php"
                    class="border border-outline text-on-surface-variant px-6 py-3 rounded-lg font-label-lg text-label-lg flex items-center gap-2 hover:bg-surface-container transition-colors justify-center"
                >

                    <span
                        class="material-symbols-outlined text-sm"
                    >
                        refresh
                    </span>


                    <?php echo __('Reset'); ?>

                </a>


                <!-- NEAR ME -->

                <button
                    type="button"
                    onclick="getLocation()"
                    id="locationButton"
                    class="bg-surface-container-high text-primary px-4 py-3 rounded-lg flex items-center justify-center hover:bg-surface-container transition-colors"
                    title="<?php echo __('Find services near me'); ?>"
                >

                    <span
                        class="material-symbols-outlined"
                    >
                        location_on
                    </span>

                </button>

            </div>

        </form>

    </div>


    <!-- ===================================================== -->
    <!-- LOCATION STATUS -->
    <!-- ===================================================== -->

    <?php if ($user_lat && $user_lng): ?>

        <div
            class="flex items-center gap-2 text-sm text-primary -mt-8 mb-8"
        >

            <span
                class="material-symbols-outlined text-sm"
            >
                check_circle
            </span>


            <span>
                <?php echo __('Showing services near your location'); ?>
            </span>


            <a
                href="browse-services.php"
                class="underline mr-2 hover:text-secondary"
            >
                <?php echo __('Clear'); ?>
            </a>

        </div>

    <?php endif; ?>


    <!-- ===================================================== -->
    <!-- SERVICES GRID -->
    <!-- ===================================================== -->

    <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter"
    >

        <?php if (count($services) > 0): ?>


            <?php foreach ($services as $service): ?>

                <?php

                $description =
                    $service['description'] ?? '';


                if (strlen($description) > 100) {

                    $description =
                        substr(
                            $description,
                            0,
                            100
                        ) . '...';

                }


                $full_stars = 0;


                if ($service['review_count'] > 0) {

                    $full_stars =
                        round(
                            $service['avg_rating']
                        );

                }


                $empty_stars =
                    5 - $full_stars;

                ?>


                <!-- ================================================= -->
                <!-- SERVICE CARD -->
                <!-- ================================================= -->

                <div
                    class="bg-surface-container-lowest rounded-xl p-6 ambient-shadow flex flex-col h-full border border-surface-variant transition-transform hover:-translate-y-1 duration-300"
                >


                    <!-- CATEGORY + RECOMMENDATION -->

                    <div
                        class="flex justify-between items-start mb-4"
                    >

                        <span
                            class="bg-surface-container text-primary font-label-sm text-label-sm px-3 py-1 rounded-full"
                        >
                            <?php
                            echo htmlspecialchars(
                                $service['category_name']
                            );
                            ?>
                        </span>


                        <?php if (
                            isset(
                                $service['recommendation_score']
                            ) &&
                            $service['recommendation_score'] > 0
                        ): ?>

                            <div
                                class="flex items-center gap-1 bg-[#ffb59f]/20 text-[#95442b] px-2 py-1 rounded-md font-label-sm text-label-sm"
                            >

                                <span
                                    class="material-symbols-outlined text-[14px]"
                                    style="font-variation-settings: 'FILL' 1;"
                                >
                                    star
                                </span>


                                <?php

                                echo number_format(
                                    $service['recommendation_score'],
                                    2
                                );

                                ?>

                            </div>

                        <?php endif; ?>

                    </div>


                    <!-- SERVICE TITLE -->

                    <h3
                        class="font-headline-md text-headline-md text-on-background mb-1"
                    >
                        <?php
                        echo htmlspecialchars(
                            $service['title']
                        );
                        ?>
                    </h3>


                    <!-- PROVIDER -->

                    <div
                        class="flex items-center gap-2 text-on-surface-variant mb-2"
                    >

                        <span
                            class="material-symbols-outlined text-sm"
                        >
                            person
                        </span>


                        <span>
                            <?php
                            echo htmlspecialchars(
                                $service['provider_name']
                            );
                            ?>
                        </span>

                    </div>


                    <!-- DISTANCE -->

                    <?php if (
                        isset($service['distance']) &&
                        $service['distance'] !== null
                    ): ?>

                        <div
                            class="flex items-center gap-2 text-on-surface-variant text-sm mb-4"
                        >

                            <span
                                class="material-symbols-outlined text-sm"
                            >
                                location_on
                            </span>


                            <span>

                                <?php

                                echo number_format(
                                    $service['distance'],
                                    1
                                );

                                ?>

                                <?php echo __('km away'); ?>

                            </span>

                        </div>

                    <?php else: ?>

                        <div
                            class="mb-4"
                        ></div>

                    <?php endif; ?>


                    <!-- DESCRIPTION -->

                    <p
                        class="font-body-md text-body-md text-on-surface-variant mb-6 line-clamp-2"
                    >
                        <?php
                        echo htmlspecialchars(
                            $description
                        );
                        ?>
                    </p>


                    <!-- BOTTOM -->

                    <div
                        class="mt-auto"
                    >


                        <!-- PRICE -->

                        <div
                            class="flex items-baseline gap-1 mb-4"
                        >

                            <span
                                dir="rtl"
                                class="text-headline-md text-primary font-bold"
                            >

                                <?php
                                echo number_format(
                                    $service['price'],
                                    2
                                );
                                ?>

                                <span dir="rtl">
                                    دل
                                </span>

                            </span>


                            <span
                                class="font-body-md text-body-md text-on-surface-variant"
                            >
                                /
                                <?php
                                echo htmlspecialchars(
                                    $service['price_type']
                                );
                                ?>
                            </span>

                        </div>


                        <!-- RATING -->

                        <div
                            class="flex items-center justify-between mb-6"
                        >

                            <?php if (
                                $service['review_count'] > 0
                            ): ?>

                                <div
                                    class="flex text-[#ffb59f]"
                                >

                                    <?php for (
                                        $i = 0;
                                        $i < $full_stars;
                                        $i++
                                    ): ?>

                                        <span
                                            class="material-symbols-outlined"
                                            style="font-variation-settings: 'FILL' 1;"
                                        >
                                            star
                                        </span>

                                    <?php endfor; ?>


                                    <?php for (
                                        $i = 0;
                                        $i < $empty_stars;
                                        $i++
                                    ): ?>

                                        <span
                                            class="material-symbols-outlined"
                                        >
                                            star_border
                                        </span>

                                    <?php endfor; ?>

                                </div>


                                <span
                                    class="font-label-sm text-label-sm text-on-surface-variant"
                                >
                                    (<?php echo $service['review_count']; ?>)
                                </span>

                            <?php else: ?>

                                <span
                                    class="font-label-sm text-label-sm text-on-surface-variant"
                                >
                                    <?php echo __('No reviews yet'); ?>
                                </span>

                            <?php endif; ?>

                        </div>


                        <!-- VIEW DETAILS -->

                        <a
                            href="service-detail.php?id=<?php echo $service['id']; ?>"
                            class="w-full bg-primary text-on-primary py-3 rounded-lg font-label-lg text-label-lg flex items-center justify-center gap-2 hover:bg-surface-tint transition-colors"
                        >

                            <span
                                class="material-symbols-outlined text-sm"
                            >
                                visibility
                            </span>


                            <?php echo __('View Details'); ?>

                        </a>

                    </div>

                </div>

            <?php endforeach; ?>


        <?php else: ?>


            <!-- ================================================= -->
            <!-- NO SERVICES -->
            <!-- ================================================= -->

            <div
                class="col-span-1 md:col-span-2 lg:col-span-3"
            >

                <div
                    class="bg-surface-container-lowest rounded-xl p-8 ambient-shadow text-center border border-surface-variant"
                >

                    <span
                        class="material-symbols-outlined text-primary text-5xl"
                    >
                        search_off
                    </span>


                    <h3
                        class="font-headline-md text-headline-md text-on-background mt-4"
                    >
                        <?php echo __('No services found'); ?>
                    </h3>


                    <p
                        class="text-on-surface-variant mt-2"
                    >

                        <?php if (
                            getUserRole() == 'provider'
                        ): ?>

                            <?php
                            echo __(
                                'You can add your first service now.'
                            );
                            ?>

                        <?php else: ?>

                            <?php
                            echo __(
                                'Please check back later or try different filters.'
                            );
                            ?>

                        <?php endif; ?>

                    </p>


                    <?php if (
                        getUserRole() == 'provider'
                    ): ?>

                        <a
                            href="/local-services-platform/provider/dashboard.php"
                            class="inline-flex items-center gap-2 mt-6 bg-primary text-on-primary px-6 py-3 rounded-lg font-label-lg hover:bg-surface-tint transition-colors"
                        >

                            <span
                                class="material-symbols-outlined text-sm"
                            >
                                add
                            </span>


                            <?php echo __('Add Your First Service'); ?>

                        </a>

                    <?php endif; ?>

                </div>

            </div>

        <?php endif; ?>

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
                <?php
                echo __(
                    '© 2026 Dabberha Services. Built for the community.'
                );
                ?>
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
<!-- CATEGORY DROPDOWN JAVASCRIPT -->
<!-- ========================================================= -->

<script>


// ============================================================
// العناصر
// ============================================================

const categoryDropdown =
    document.getElementById('categoryDropdown');


const categoryToggle =
    document.getElementById('categoryToggle');


const categoryMenu =
    document.getElementById('categoryMenu');


const categoryInput =
    document.getElementById('categoryInput');


const selectedCategory =
    document.getElementById('selectedCategory');


const categoryArrow =
    document.getElementById('categoryArrow');


const categoryOptions =
    document.querySelectorAll('.category-option');


// ============================================================
// فتح القائمة عند الضغط على السهم فقط
// ============================================================

categoryToggle.addEventListener(
    'click',
    function (event) {

        event.stopPropagation();


        const isOpen =
            !categoryMenu.classList.contains('hidden');


        if (isOpen) {

            closeCategoryDropdown();

        } else {

            openCategoryDropdown();

        }

    }
);


// ============================================================
// فتح القائمة
// ============================================================

function openCategoryDropdown() {

    categoryMenu.classList.remove(
        'hidden'
    );


    categoryToggle.setAttribute(
        'aria-expanded',
        'true'
    );


    categoryArrow.style.transform =
        'rotate(180deg)';

}


// ============================================================
// إغلاق القائمة
// ============================================================

function closeCategoryDropdown() {

    categoryMenu.classList.add(
        'hidden'
    );


    categoryToggle.setAttribute(
        'aria-expanded',
        'false'
    );


    categoryArrow.style.transform =
        'rotate(0deg)';

}


// ============================================================
// اختيار Category
// ============================================================

categoryOptions.forEach(
    function (option) {

        option.addEventListener(
            'click',
            function () {

                const value =
                    this.dataset.value;


                const label =
                    this.dataset.label;


                // حفظ القيمة
                categoryInput.value =
                    value;


                // تغيير النص الظاهر
                selectedCategory.textContent =
                    label;


                // إغلاق القائمة
                closeCategoryDropdown();

            }
        );

    }
);


// ============================================================
// إغلاق القائمة عند الضغط خارجها
// ============================================================

document.addEventListener(
    'click',
    function (event) {

        if (
            !categoryDropdown.contains(
                event.target
            )
        ) {

            closeCategoryDropdown();

        }

    }
);

</script>


<!-- ========================================================= -->
<!-- GEOLOCATION JAVASCRIPT -->
<!-- ========================================================= -->

<script>

function getLocation() {

    const button =
        document.getElementById(
            'locationButton'
        );


    if (navigator.geolocation) {


        // تغيير شكل الزر أثناء تحديد الموقع

        button.innerHTML = `
            <span class="material-symbols-outlined animate-spin">
                progress_activity
            </span>
        `;


        button.disabled = true;


        navigator.geolocation.getCurrentPosition(

            function (position) {

                const lat =
                    position.coords.latitude;


                const lng =
                    position.coords.longitude;


                // إعادة التوجيه مع الإحداثيات

                window.location.href =
                    'browse-services.php?lat=' +
                    encodeURIComponent(lat) +
                    '&lng=' +
                    encodeURIComponent(lng);

            },


            function (error) {

                alert(
                    '<?php echo __('Unable to get your location. Please allow location access and try again.'); ?>'
                );


                // إعادة الزر إلى حالته الطبيعية

                button.innerHTML = `
                    <span class="material-symbols-outlined">
                        location_on
                    </span>
                `;


                button.disabled = false;

            }

        );

    } else {

        alert(
            '<?php echo __('Geolocation is not supported by your browser.'); ?>'
        );

    }

}

</script>


</body>

</html>