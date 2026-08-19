<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

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
?>

<!DOCTYPE html>
<html class="light" lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($service['title']); ?> - Local Services
    </title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <!-- Plus Jakarta Sans -->
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
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

                        "display-lg": ["Plus Jakarta Sans"],
                        "body-lg": ["Plus Jakarta Sans"],
                        "label-lg": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"],
                        "headline-lg": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"],
                        "label-sm": ["Plus Jakarta Sans"]

                    },

                    fontSize: {

                        "display-lg": [
                            "48px",
                            {
                                "lineHeight": "56px",
                                "letterSpacing": "-0.02em",
                                "fontWeight": "700"
                            }
                        ],

                        "body-lg": [
                            "18px",
                            {
                                "lineHeight": "28px",
                                "fontWeight": "400"
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
                        ],

                        "body-md": [
                            "16px",
                            {
                                "lineHeight": "24px",
                                "fontWeight": "400"
                            }
                        ],

                        "label-sm": [
                            "12px",
                            {
                                "lineHeight": "16px",
                                "fontWeight": "500"
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
        }

        .material-symbols-outlined {

            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24;

            vertical-align: middle;

        }

        .warm-shadow {

            box-shadow:
                0 4px 20px rgba(58, 47, 43, 0.05);

        }

    </style>

</head>


<body class="font-body-md text-on-background min-h-screen">


<!-- ========================================================= -->
<!-- HEADER -->
<!-- ========================================================= -->

<header
    class="bg-stone-50 flex justify-between items-center px-6 py-4 w-full sticky top-0 z-50 shadow-sm shadow-orange-900/5 border-b border-stone-200"
>

    <!-- Logo -->

    <a
        href="/local-services-platform/index.php"
        class="text-2xl font-bold text-[#CB6D51]"
    >
        Dabberha
    </a>


    <!-- Navigation -->

    <nav class="hidden md:flex items-center gap-8">

        <a
            href="browse-services.php"
            class="text-[#CB6D51] font-semibold border-b-2 border-[#CB6D51] pb-1"
        >
            Find Services
        </a>

        <?php if (isLoggedIn() && getUserRole() == 'customer'): ?>

            <a
                href="/local-services-platform/public/my-bookings.php"
                class="text-stone-600 font-medium hover:text-[#CB6D51] transition-colors"
            >
                My Bookings
            </a>

        <?php endif; ?>

        <a
            href="#"
            class="text-stone-600 font-medium hover:text-[#CB6D51] transition-colors"
        >
            How it Works
        </a>

        <?php if (!isLoggedIn()): ?>

            <a
                href="login.php"
                class="text-stone-600 font-medium hover:text-[#CB6D51] transition-colors"
            >
                Become a Provider
            </a>

        <?php endif; ?>

    </nav>


    <!-- User actions -->

    <div class="flex items-center gap-4">

        <?php if (isLoggedIn()): ?>

            <div class="hidden sm:flex items-center gap-2">

                <span class="material-symbols-outlined text-stone-600">
                    account_circle
                </span>

                <span class="font-medium">
                    <?php echo htmlspecialchars(getUserName()); ?>
                </span>

            </div>

            <a
                href="/local-services-platform/public/logout.php"
                class="bg-[#CB6D51] text-white px-5 py-2 rounded-full font-semibold hover:opacity-90 transition-opacity"
            >
                Logout
            </a>

        <?php else: ?>

            <a
                href="login.php"
                class="bg-[#CB6D51] text-white px-5 py-2 rounded-full font-semibold hover:opacity-90 transition-opacity"
            >
                Sign In
            </a>

        <?php endif; ?>

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
                    class="w-32 h-32 md:w-40 md:h-40 rounded-full bg-[#FFF1EC] flex items-center justify-center border-4 border-[#F1DFD8] shrink-0"
                >

                    <span
                        class="material-symbols-outlined text-[#CB6D51] text-6xl"
                    >
                        home_repair_service
                    </span>

                </div>


                <!-- Service info -->

                <div class="flex-1 text-center md:text-left">

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

                            <span
                                class="material-symbols-outlined text-sm"
                                style="font-variation-settings: 'FILL' 1;"
                            >
                                verified
                            </span>

                            <span class="text-label-sm">
                                Verified Service
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

                        <div class="flex items-center gap-2">

                            <span class="material-symbols-outlined">
                                person
                            </span>

                            <span class="font-medium">
                                <?php echo htmlspecialchars($service['provider_name']); ?>
                            </span>

                        </div>


                        <div class="flex items-center gap-1">

                            <span
                                class="material-symbols-outlined text-orange-400"
                                style="font-variation-settings: 'FILL' 1;"
                            >
                                star
                            </span>

                            <?php if ($service['review_count'] > 0): ?>

                                <span class="font-bold">
                                    <?php echo htmlspecialchars($service['avg_rating']); ?>
                                </span>

                                <span class="text-label-lg">
                                    (<?php echo $service['review_count']; ?> reviews)
                                </span>

                            <?php else: ?>

                                <span class="text-label-lg">
                                    No reviews yet
                                </span>

                            <?php endif; ?>

                        </div>


                        <?php if (!empty($service['provider_address'])): ?>

                            <div class="flex items-center gap-1">

                                <span class="material-symbols-outlined text-stone-400">
                                    location_on
                                </span>

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
                    Service Details
                </h2>


                <div
                    class="bg-white p-8 rounded-xl warm-shadow border border-transparent"
                >

                    <div class="flex justify-between items-start mb-6">

                        <div
                            class="p-3 bg-surface-container-low rounded-lg text-primary"
                        >

                            <span class="material-symbols-outlined text-3xl">
                                cleaning_services
                            </span>

                        </div>


                        <div class="text-right">

                            <span
                                class="text-headline-md text-primary font-bold"
                            >
                                $<?php
                                echo number_format(
                                    $service['price'],
                                    2
                                );
                                ?>
                            </span>

                            <span
                                class="block text-sm text-on-surface-variant"
                            >
                                / <?php echo htmlspecialchars($service['price_type']); ?>
                            </span>

                        </div>

                    </div>


                    <h3
                        class="font-headline-md mb-3"
                    >
                        <?php echo htmlspecialchars($service['title']); ?>
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

                        <span
                            class="material-symbols-outlined text-primary text-sm"
                        >
                            check_circle
                        </span>

                        Professional local service

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
                    Provider Information
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

                                <span class="material-symbols-outlined">
                                    person
                                </span>

                            </div>

                            <div>

                                <p
                                    class="text-label-sm text-on-surface-variant mb-1"
                                >
                                    Name
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

                                <span class="material-symbols-outlined">
                                    phone
                                </span>

                            </div>

                            <div>

                                <p
                                    class="text-label-sm text-on-surface-variant mb-1"
                                >
                                    Phone
                                </p>

                                <p class="font-semibold">
                                    <?php
                                    echo htmlspecialchars(
                                        $service['provider_phone']
                                        ?? 'Not provided'
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

                                <span class="material-symbols-outlined">
                                    email
                                </span>

                            </div>

                            <div>

                                <p
                                    class="text-label-sm text-on-surface-variant mb-1"
                                >
                                    Email
                                </p>

                                <p class="font-semibold break-all">
                                    <?php echo htmlspecialchars($service['provider_email']); ?>
                                </p>

                            </div>

                        </div>


                        <!-- Address -->

                        <div class="flex items-start gap-4">

                            <div
                                class="p-3 bg-surface-container-low rounded-lg text-primary"
                            >

                                <span class="material-symbols-outlined">
                                    location_on
                                </span>

                            </div>

                            <div>

                                <p
                                    class="text-label-sm text-on-surface-variant mb-1"
                                >
                                    Address
                                </p>

                                <p class="font-semibold">
                                    <?php
                                    echo htmlspecialchars(
                                        $service['provider_address']
                                        ?? 'Not provided'
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
                    Customer Reviews
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

                                                <span
                                                    class="material-symbols-outlined text-primary"
                                                >
                                                    person
                                                </span>

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

                                <span
                                    class="material-symbols-outlined text-primary text-3xl"
                                >
                                    rate_review
                                </span>

                            </div>

                            <h3
                                class="font-headline-md mb-2"
                            >
                                No reviews yet
                            </h3>

                            <p
                                class="text-on-surface-variant"
                            >
                                Be the first customer to review this service.
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

                    <span class="material-symbols-outlined text-primary">
                        calendar_month
                    </span>

                    Book This Service

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
                                Service Date
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
                                Service Time
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
                                Service Notes
                            </label>

                            <textarea
                                name="notes"
                                rows="4"
                                placeholder="Tell the provider about any special requirements..."
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
                                    Service Price
                                </span>

                                <span
                                    class="font-bold text-on-surface"
                                >
                                    $<?php
                                    echo number_format(
                                        $service['price'],
                                        2
                                    );
                                    ?>
                                </span>

                            </div>


                            <div
                                class="flex justify-between items-center text-headline-md pt-2"
                            >

                                <span>
                                    Total
                                </span>

                                <span class="text-primary">

                                    $<?php
                                    echo number_format(
                                        $service['price'],
                                        2
                                    );
                                    ?>

                                </span>

                            </div>

                        </div>


                        <!-- Book Button -->

                        <button
                            type="submit"
                            class="w-full py-4 bg-[#CB6D51] text-white rounded-xl font-bold text-lg warm-shadow hover:brightness-105 transition-all flex items-center justify-center gap-2 active:scale-95"
                        >

                            Book Now

                            <span class="material-symbols-outlined">
                                arrow_forward
                            </span>

                        </button>

                    </form>


                <?php elseif (!isLoggedIn()): ?>


                    <!-- Not logged in -->

                    <div class="text-center">

                        <div
                            class="w-16 h-16 bg-[#FFF1EC] rounded-full flex items-center justify-center mx-auto mb-5"
                        >

                            <span
                                class="material-symbols-outlined text-[#CB6D51] text-3xl"
                            >
                                lock
                            </span>

                        </div>


                        <h4
                            class="font-headline-md mb-2"
                        >
                            Login to Book
                        </h4>


                        <p
                            class="text-on-surface-variant text-body-md mb-6"
                        >
                            Please login as a customer to book this service.
                        </p>


                        <a
                            href="login.php"
                            class="block w-full py-4 bg-[#CB6D51] text-white rounded-xl font-bold text-lg text-center hover:brightness-105 transition-all active:scale-95"
                        >
                            Login as Customer
                        </a>

                    </div>


                <?php elseif (getUserRole() == 'provider'): ?>


                    <!-- Provider -->

                    <div class="text-center">

                        <div
                            class="w-16 h-16 bg-[#FFF1EC] rounded-full flex items-center justify-center mx-auto mb-5"
                        >

                            <span
                                class="material-symbols-outlined text-[#CB6D51] text-3xl"
                            >
                                store
                            </span>

                        </div>


                        <h4
                            class="font-headline-md mb-2"
                        >
                            Provider Account
                        </h4>


                        <p
                            class="text-on-surface-variant text-body-md"
                        >
                            You are registered as a provider. Please switch to a customer account to book this service.
                        </p>

                    </div>


                <?php elseif (getUserRole() == 'admin'): ?>


                    <!-- Admin -->

                    <div class="text-center">

                        <div
                            class="w-16 h-16 bg-[#FFF1EC] rounded-full flex items-center justify-center mx-auto mb-5"
                        >

                            <span
                                class="material-symbols-outlined text-[#CB6D51] text-3xl"
                            >
                                admin_panel_settings
                            </span>

                        </div>


                        <h4
                            class="font-headline-md mb-2"
                        >
                            Admin Account
                        </h4>


                        <p
                            class="text-on-surface-variant text-body-md"
                        >
                            You are currently logged in as an administrator.
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

                        <span
                            class="material-symbols-outlined text-secondary"
                        >
                            verified_user
                        </span>

                    </div>

                    <p
                        class="text-label-sm text-secondary font-semibold"
                    >
                        Trusted local service
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
    class="bg-stone-100 border-t border-stone-200 py-12 px-6"
>

    <div
        class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-7xl mx-auto text-sm"
    >

        <div class="space-y-4">

            <div
                class="text-lg font-bold text-stone-800"
            >
                Dabberha
            </div>

            <p class="text-stone-500">

                © <?php echo date('Y'); ?>
                Dabberha.
                All rights reserved.

            </p>

        </div>


        <div
            class="flex flex-wrap gap-x-8 gap-y-2 md:justify-end items-center"
        >

            <a
                href="#"
                class="text-stone-500 hover:text-[#CB6D51] transition-colors"
            >
                Privacy Policy
            </a>

            <a
                href="#"
                class="text-stone-500 hover:text-[#CB6D51] transition-colors"
            >
                Terms of Service
            </a>

            <a
                href="#"
                class="text-stone-500 hover:text-[#CB6D51] transition-colors"
            >
                Help Center
            </a>

            <a
                href="#"
                class="text-stone-500 hover:text-[#CB6D51] transition-colors"
            >
                Contact Us
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
                Starting from
            </span>

            <span
                class="font-headline-md text-primary"
            >
                $<?php
                echo number_format(
                    $service['price'],
                    2
                );
                ?>
            </span>

        </div>


        <a
            href="#booking-form"
            onclick="document.querySelector('input[name=booking_date]').focus();"
            class="bg-[#CB6D51] text-white px-8 py-3 rounded-xl font-bold"
        >
            Book Now
        </a>

    </div>

<?php endif; ?>


</body>
</html>