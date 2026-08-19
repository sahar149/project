<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notifications.php';

requireRole('provider');

$provider_id = getUserId();
$provider_name = getUserName();

$unread_count = getUnreadCount($provider_id);
$notifications = getNotifications($provider_id, 5);

// ============================================================
// Provider Statistics
// ============================================================

// Total Services
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_services 
    FROM services 
    WHERE provider_id = ?
");
$stmt->execute([$provider_id]);
$total_services = $stmt->fetch()['total_services'];

// Total Bookings (excluding cancelled)
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_bookings 
    FROM bookings 
    WHERE provider_id = ? 
    AND status != 'cancelled'
");
$stmt->execute([$provider_id]);
$total_bookings = $stmt->fetch()['total_bookings'];

// Pending Bookings
$stmt = $pdo->prepare("
    SELECT COUNT(*) as pending_bookings 
    FROM bookings 
    WHERE provider_id = ? 
    AND status = 'pending'
");
$stmt->execute([$provider_id]);
$pending_bookings = $stmt->fetch()['pending_bookings'];

// Total Earnings
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(total_price), 0) as total_earnings 
    FROM bookings 
    WHERE provider_id = ? 
    AND status = 'completed'
");
$stmt->execute([$provider_id]);
$total_earnings = $stmt->fetch()['total_earnings'];

// Average Rating + Reviews Count
$stmt = $pdo->prepare("
    SELECT 
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(r.id) as total_reviews
    FROM reviews r
    JOIN services s ON r.service_id = s.id
    WHERE s.provider_id = ?
");
$stmt->execute([$provider_id]);
$review_stats = $stmt->fetch();

$avg_rating = (float) $review_stats['avg_rating'];
$total_reviews = (int) $review_stats['total_reviews'];

// ============================================================
// Recent Booking Requests
// ============================================================

$stmt = $pdo->prepare("
    SELECT 
        b.*,
        s.title as service_title,
        u.name as customer_name
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.customer_id = u.id
    WHERE b.provider_id = ?
    AND b.status = 'pending'
    ORDER BY b.created_at DESC
    LIMIT 5
");
$stmt->execute([$provider_id]);
$pending_requests = $stmt->fetchAll();

// ============================================================
// Recent Reviews
// ============================================================

$stmt = $pdo->prepare("
    SELECT 
        r.*,
        u.name as customer_name,
        s.title as service_title
    FROM reviews r
    JOIN users u ON r.customer_id = u.id
    JOIN services s ON r.service_id = s.id
    WHERE s.provider_id = ?
    ORDER BY r.created_at DESC
    LIMIT 5
");
$stmt->execute([$provider_id]);
$recent_reviews = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Provider Dashboard - LocalEase</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F9F5F1;
            color: #333333;
        }

        .text-terracotta {
            color: #CB6D51;
        }

        .bg-terracotta {
            background-color: #CB6D51;
        }

        .hover-bg-terracotta:hover {
            background-color: #b55e43;
        }

        .text-dusty-rose {
            color: #C18B8B;
        }

        .bg-dusty-rose {
            background-color: #C18B8B;
        }

        .bg-creamy-beige {
            background-color: #F9F5F1;
        }

        .border-terracotta {
            border-color: #CB6D51;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>

<body class="bg-[#F9F5F1] min-h-screen flex flex-col">

<!-- ============================================================
     TOP NAVBAR
============================================================ -->

<header class="bg-white border-b border-gray-200 sticky top-0 z-50">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between h-16">

            <!-- Logo -->
            <div class="flex items-center">

                <a
                    class="flex items-center gap-2 text-2xl font-bold text-terracotta"
                    href="/local-services-platform/index.php"
                >
                    <i class="fa-solid fa-house-chimney"></i>
                    LocalEase
                </a>

            </div>

            <!-- Right Side -->
            <div class="flex items-center gap-4">

                <!-- Notification -->
                <a
                    href="#notifications"
                    class="text-gray-500 hover:text-terracotta relative transition-colors"
                >
                    <i class="fa-regular fa-bell text-xl"></i>

                    <?php if ($unread_count > 0): ?>
                        <span
                            class="absolute -top-1 -right-1 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"
                        ></span>
                    <?php endif; ?>
                </a>

                <!-- User -->
                <div class="flex items-center gap-2">

                    <div
                        class="h-8 w-8 rounded-full bg-orange-50 text-terracotta flex items-center justify-center"
                    >
                        <i class="fa-regular fa-user"></i>
                    </div>

                    <span class="font-medium text-sm text-gray-700 hidden sm:block">
                        <?php echo htmlspecialchars($provider_name); ?>
                    </span>

                    <i class="fa-solid fa-chevron-down text-xs text-gray-400"></i>

                </div>

                <!-- Logout -->
                <a
                    href="/local-services-platform/public/logout.php"
                    class="hidden sm:inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-red-600 transition-colors"
                >
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    Logout
                </a>

            </div>

        </div>

    </div>

</header>


<!-- ============================================================
     PAGE LAYOUT
============================================================ -->

<div class="flex-grow flex max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 gap-8">


    <!-- ========================================================
         SIDEBAR
    ========================================================= -->

    <aside class="w-64 flex-shrink-0 hidden md:block">

        <nav class="space-y-1">

            <!-- Dashboard -->
            <a
                class="bg-white text-terracotta border-l-4 border-terracotta group flex items-center px-3 py-2 text-sm font-medium rounded-r-md shadow-sm"
                href="dashboard.php"
            >
                <i class="fa-solid fa-chart-line mr-3 text-lg"></i>
                Dashboard
            </a>

            <!-- My Services -->
            <a
                class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                href="my-services.php"
            >
                <i class="fa-solid fa-toolbox mr-3 text-lg text-gray-400 group-hover:text-gray-500"></i>
                My Services
            </a>

            <!-- Bookings -->
            <a
                class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                href="bookings.php"
            >
                <i class="fa-regular fa-calendar-check mr-3 text-lg text-gray-400 group-hover:text-gray-500"></i>
                Bookings
            </a>

            <!-- Reviews -->
            <a
                class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                href="reviews.php"
            >
                <i class="fa-regular fa-star mr-3 text-lg text-gray-400 group-hover:text-gray-500"></i>
                Reviews
            </a>

            <!-- Earnings -->
            <a
                class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                href="bookings.php?status=completed"
            >
                <i class="fa-solid fa-wallet mr-3 text-lg text-gray-400 group-hover:text-gray-500"></i>
                Earnings
            </a>

            <!-- Profile -->
            <a
                class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                href="profile.php"
            >
                <i class="fa-regular fa-user mr-3 text-lg text-gray-400 group-hover:text-gray-500"></i>
                Profile
            </a>

            <!-- Logout -->
            <div class="pt-4 mt-4 border-t border-gray-200">

                <a
                    class="text-red-500 hover:bg-red-50 hover:text-red-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                    href="/local-services-platform/public/logout.php"
                >
                    <i class="fa-solid fa-arrow-right-from-bracket mr-3 text-lg"></i>
                    Logout
                </a>

            </div>

        </nav>

    </aside>


    <!-- ========================================================
         MAIN CONTENT
    ========================================================= -->

    <main class="flex-1 space-y-6 min-w-0">


        <!-- ====================================================
             PAGE HEADER
        ==================================================== -->

        <div>

            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">

                <i class="fa-solid fa-gauge text-terracotta"></i>

                Dashboard Overview

            </h1>

            <p class="mt-1 text-sm text-gray-500">

                Welcome back,
                <?php echo htmlspecialchars($provider_name); ?>!

            </p>

        </div>


        <!-- ====================================================
             METRICS CARDS
        ==================================================== -->

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">


            <!-- My Services -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col relative overflow-hidden group"
            >

                <div
                    class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 rounded-full bg-terracotta opacity-10 group-hover:scale-110 transition-transform"
                ></div>

                <div class="flex items-center gap-3 mb-4">

                    <div class="p-2 bg-orange-50 rounded-lg text-terracotta">

                        <i class="fa-solid fa-briefcase"></i>

                    </div>

                    <h3 class="text-sm font-medium text-gray-600">
                        My Services
                    </h3>

                </div>

                <p class="text-3xl font-bold text-gray-900 mb-1">
                    <?php echo $total_services; ?>
                </p>

                <p class="text-xs text-gray-500">
                    Services you offer
                </p>

            </div>


            <!-- Total Bookings -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col relative overflow-hidden group"
            >

                <div
                    class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 rounded-full bg-green-500 opacity-10 group-hover:scale-110 transition-transform"
                ></div>

                <div class="flex items-center gap-3 mb-4">

                    <div class="p-2 bg-green-50 rounded-lg text-green-600">

                        <i class="fa-regular fa-calendar-check"></i>

                    </div>

                    <h3 class="text-sm font-medium text-gray-600">
                        Total Bookings
                    </h3>

                </div>

                <p class="text-3xl font-bold text-gray-900 mb-1">
                    <?php echo $total_bookings; ?>
                </p>

                <p class="text-xs text-gray-500">
                    All confirmed bookings
                </p>

            </div>


            <!-- Pending -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col relative overflow-hidden group"
            >

                <div
                    class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 rounded-full bg-yellow-500 opacity-10 group-hover:scale-110 transition-transform"
                ></div>

                <div class="flex items-center gap-3 mb-4">

                    <div class="p-2 bg-yellow-50 rounded-lg text-yellow-600">

                        <i class="fa-regular fa-clock"></i>

                    </div>

                    <h3 class="text-sm font-medium text-gray-600">
                        Pending
                    </h3>

                </div>

                <p class="text-3xl font-bold text-gray-900 mb-1">
                    <?php echo $pending_bookings; ?>
                </p>

                <p class="text-xs text-gray-500">
                    Awaiting your response
                </p>

            </div>


            <!-- Earnings -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col relative overflow-hidden group"
            >

                <div
                    class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 rounded-full bg-dusty-rose opacity-10 group-hover:scale-110 transition-transform"
                ></div>

                <div class="flex items-center gap-3 mb-4">

                    <div class="p-2 bg-pink-50 rounded-lg text-dusty-rose">

                        <i class="fa-solid fa-dollar-sign"></i>

                    </div>

                    <h3 class="text-sm font-medium text-gray-600">
                        Earnings
                    </h3>

                </div>

                <p class="text-3xl font-bold text-gray-900 mb-1">
                    $<?php echo number_format($total_earnings, 2); ?>
                </p>

                <p class="text-xs text-gray-500">
                    From completed jobs
                </p>

            </div>

        </div>


        <!-- ====================================================
             AVERAGE RATING
        ==================================================== -->

        <div
            class="bg-yellow-50 rounded-xl border border-yellow-200 p-6 flex flex-col sm:flex-row justify-between items-center gap-4"
        >

            <div>

                <div
                    class="flex items-center gap-2 text-yellow-700 font-semibold mb-1"
                >

                    <i class="fa-regular fa-star"></i>

                    Average Rating

                </div>


                <div class="flex items-end gap-2">

                    <span class="text-4xl font-bold text-yellow-600">

                        <?php echo number_format($avg_rating, 1); ?>

                    </span>

                    <div class="flex text-yellow-400 text-xl mb-1">

                        <?php
                        $rounded_rating = round($avg_rating);

                        for ($i = 1; $i <= 5; $i++) {

                            if ($i <= $rounded_rating) {
                                echo '<i class="fa-solid fa-star"></i>';
                            } else {
                                echo '<i class="fa-regular fa-star text-yellow-300"></i>';
                            }

                        }
                        ?>

                    </div>

                </div>


                <p class="text-sm text-yellow-700 mt-1">

                    Based on
                    <?php echo $total_reviews; ?>
                    <?php echo $total_reviews == 1 ? 'review' : 'reviews'; ?>

                </p>

            </div>


            <a
                href="reviews.php"
                class="bg-white text-yellow-700 border border-yellow-300 hover:bg-yellow-100 font-medium py-2 px-4 rounded-lg shadow-sm transition-colors"
            >

                <i class="fa-regular fa-star mr-1"></i>

                View All Reviews

            </a>

        </div>


        <!-- ====================================================
             TWO COLUMN CONTENT
        ==================================================== -->

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">


            <!-- =================================================
                 LEFT COLUMN
            ================================================= -->

            <div class="lg:col-span-2 space-y-6">


                <!-- =================================================
                     QUICK ACTIONS
                ================================================= -->

                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                >

                    <div
                        class="border-b border-gray-100 bg-gray-50 px-5 py-3"
                    >

                        <h3
                            class="font-semibold text-gray-800 flex items-center gap-2"
                        >

                            <i class="fa-solid fa-bolt text-gray-400"></i>

                            Quick Actions

                        </h3>

                    </div>


                    <div class="p-5 grid grid-cols-2 md:grid-cols-4 gap-3">


                        <!-- Add Service -->
                        <a
                            href="add-service.php"
                            class="bg-terracotta hover-bg-terracotta text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors flex justify-center items-center gap-2"
                        >

                            <i class="fa-solid fa-plus"></i>

                            Add Service

                        </a>


                        <!-- My Services -->
                        <a
                            href="my-services.php"
                            class="bg-white border border-terracotta text-terracotta hover:bg-orange-50 py-2 px-4 rounded-lg text-sm font-medium transition-colors flex justify-center items-center gap-2"
                        >

                            <i class="fa-solid fa-list"></i>

                            My Services

                        </a>


                        <!-- Bookings -->
                        <a
                            href="bookings.php"
                            class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 py-2 px-4 rounded-lg text-sm font-medium transition-colors flex justify-center items-center gap-2"
                        >

                            <i class="fa-regular fa-calendar"></i>

                            Bookings

                        </a>


                        <!-- Profile -->
                        <a
                            href="profile.php"
                            class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 py-2 px-4 rounded-lg text-sm font-medium transition-colors flex justify-center items-center gap-2"
                        >

                            <i class="fa-regular fa-user"></i>

                            Edit Profile

                        </a>

                    </div>

                </div>


                <!-- =================================================
                     RECENT BOOKING REQUESTS
                ================================================= -->

                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                >

                    <div
                        class="border-b border-gray-100 bg-gray-50 px-5 py-3 flex justify-between items-center"
                    >

                        <h3
                            class="font-semibold text-gray-800 flex items-center gap-2"
                        >

                            <i class="fa-regular fa-clock text-gray-400"></i>

                            Recent Booking Requests

                        </h3>

                        <?php if (count($pending_requests) > 0): ?>

                            <a
                                href="bookings.php"
                                class="text-xs font-medium text-terracotta hover:text-orange-700"
                            >
                                View All
                            </a>

                        <?php endif; ?>

                    </div>


                    <?php if (count($pending_requests) > 0): ?>

                        <div class="overflow-x-auto">

                            <table class="min-w-full divide-y divide-gray-200">

                                <thead class="bg-white">

                                    <tr>

                                        <th
                                            class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Customer
                                        </th>

                                        <th
                                            class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Service
                                        </th>

                                        <th
                                            class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Date
                                        </th>

                                        <th
                                            class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Status
                                        </th>

                                        <th
                                            class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Action
                                        </th>

                                    </tr>

                                </thead>


                                <tbody
                                    class="bg-white divide-y divide-gray-100 text-sm"
                                >

                                    <?php foreach ($pending_requests as $request): ?>

                                        <tr class="hover:bg-gray-50 transition-colors">

                                            <!-- Customer -->
                                            <td
                                                class="px-5 py-4 whitespace-nowrap text-gray-900 font-medium"
                                            >
                                                <?php
                                                echo htmlspecialchars(
                                                    $request['customer_name']
                                                );
                                                ?>
                                            </td>


                                            <!-- Service -->
                                            <td
                                                class="px-5 py-4 whitespace-nowrap text-gray-600"
                                            >
                                                <?php
                                                echo htmlspecialchars(
                                                    $request['service_title']
                                                );
                                                ?>
                                            </td>


                                            <!-- Date -->
                                            <td
                                                class="px-5 py-4 whitespace-nowrap text-gray-600"
                                            >
                                                <?php
                                                echo date(
                                                    'M d, Y',
                                                    strtotime($request['booking_date'])
                                                );
                                                ?>
                                            </td>


                                            <!-- Status -->
                                            <td
                                                class="px-5 py-4 whitespace-nowrap"
                                            >

                                                <span
                                                    class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"
                                                >
                                                    Pending
                                                </span>

                                            </td>


                                            <!-- Action -->
                                            <td
                                                class="px-5 py-4 whitespace-nowrap text-right text-sm font-medium"
                                            >

                                                <a
                                                    href="booking-detail.php?id=<?php echo (int)$request['id']; ?>"
                                                    class="text-terracotta hover:text-orange-700 bg-orange-50 hover:bg-orange-100 px-3 py-1 rounded-md transition-colors text-xs font-semibold inline-flex items-center"
                                                >

                                                    <i class="fa-regular fa-eye mr-1"></i>

                                                    View

                                                </a>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="p-8 text-center">

                            <div
                                class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3"
                            >

                                <i class="fa-regular fa-calendar text-gray-400"></i>

                            </div>

                            <p class="text-sm text-gray-500">
                                No pending booking requests.
                            </p>

                        </div>

                    <?php endif; ?>

                </div>


                <!-- =================================================
                     RECENT REVIEWS
                ================================================= -->

                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                >

                    <div
                        class="border-b border-gray-100 bg-gray-50 px-5 py-3 flex justify-between items-center"
                    >

                        <h3
                            class="font-semibold text-gray-800 flex items-center gap-2"
                        >

                            <i class="fa-regular fa-star text-gray-400"></i>

                            Recent Reviews

                        </h3>

                        <?php if (count($recent_reviews) > 0): ?>

                            <a
                                href="reviews.php"
                                class="text-xs font-medium text-terracotta hover:text-orange-700"
                            >
                                View All
                            </a>

                        <?php endif; ?>

                    </div>


                    <div class="p-5">

                        <?php if (count($recent_reviews) > 0): ?>

                            <?php foreach ($recent_reviews as $index => $review): ?>

                                <div
                                    class="<?php echo $index < count($recent_reviews) - 1 ? 'border-b border-gray-100 pb-4 mb-4' : ''; ?>"
                                >

                                    <div
                                        class="flex justify-between items-start mb-2"
                                    >

                                        <h4
                                            class="font-medium text-gray-900"
                                        >
                                            <?php
                                            echo htmlspecialchars(
                                                $review['customer_name']
                                            );
                                            ?>
                                        </h4>


                                        <div class="flex text-yellow-400 text-sm">

                                            <?php

                                            $rating = (int) round($review['rating']);

                                            for ($i = 1; $i <= 5; $i++) {

                                                if ($i <= $rating) {
                                                    echo '<i class="fa-solid fa-star"></i>';
                                                } else {
                                                    echo '<i class="fa-regular fa-star text-gray-300"></i>';
                                                }

                                            }

                                            ?>

                                        </div>

                                    </div>


                                    <?php if (!empty($review['comment'])): ?>

                                        <p class="text-gray-600 text-sm mb-1">

                                            <?php
                                            echo htmlspecialchars(
                                                $review['comment']
                                            );
                                            ?>

                                        </p>

                                    <?php endif; ?>


                                    <p class="text-xs text-gray-400">

                                        <?php
                                        echo date(
                                            'M d, Y',
                                            strtotime($review['created_at'])
                                        );
                                        ?>

                                        on

                                        <?php
                                        echo htmlspecialchars(
                                            $review['service_title']
                                        );
                                        ?>

                                    </p>

                                </div>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="py-6 text-center">

                                <div
                                    class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3"
                                >

                                    <i class="fa-regular fa-star text-gray-400"></i>

                                </div>

                                <p class="text-sm text-gray-500">
                                    No reviews yet.
                                </p>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 RIGHT COLUMN - NOTIFICATIONS
            ================================================= -->

            <div class="space-y-6" id="notifications">

                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-[500px]"
                >

                    <!-- Header -->
                    <div
                        class="border-b border-gray-100 bg-gray-50 px-5 py-3 flex justify-between items-center"
                    >

                        <h3
                            class="font-semibold text-gray-800 flex items-center gap-2"
                        >

                            <i class="fa-regular fa-bell text-gray-400"></i>

                            Notifications

                            <?php if ($unread_count > 0): ?>

                                <span
                                    class="bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded-full font-bold"
                                >
                                    <?php echo $unread_count; ?> new
                                </span>

                            <?php endif; ?>

                        </h3>

                    </div>


                    <!-- Notifications Content -->
                    <div
                        class="p-3 flex-1 overflow-y-auto space-y-3 custom-scrollbar"
                    >

                        <?php if (count($notifications) > 0): ?>

                            <?php foreach ($notifications as $notif): ?>

                                <div
                                    class="
                                    <?php
                                    echo $notif['is_read']
                                        ? 'bg-gray-50 border-gray-200'
                                        : 'bg-blue-50 border-blue-100';
                                    ?>
                                    border rounded-lg p-4 relative pl-10
                                    "
                                >

                                    <div
                                        class="
                                        absolute left-4 top-4
                                        <?php
                                        echo $notif['is_read']
                                            ? 'text-gray-400'
                                            : 'text-blue-400';
                                        ?>
                                        "
                                    >

                                        <?php if ($notif['is_read']): ?>

                                            <i class="fa-regular fa-bell"></i>

                                        <?php else: ?>

                                            <i class="fa-solid fa-circle-info"></i>

                                        <?php endif; ?>

                                    </div>


                                    <p
                                        class="text-sm text-gray-800 font-medium mb-1"
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $notif['message']
                                        );
                                        ?>

                                    </p>


                                    <p class="text-xs text-gray-500">

                                        <?php
                                        echo date(
                                            'M d, Y H:i',
                                            strtotime($notif['created_at'])
                                        );
                                        ?>

                                    </p>

                                </div>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="h-full flex items-center justify-center">

                                <div class="text-center">

                                    <div
                                        class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3"
                                    >

                                        <i
                                            class="fa-regular fa-bell text-gray-400"
                                        ></i>

                                    </div>

                                    <p class="text-sm text-gray-500">
                                        No notifications
                                    </p>

                                </div>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </main>

</div>


<!-- ============================================================
     FOOTER
============================================================ -->

<footer class="bg-white border-t border-gray-200 mt-auto">

    <div
        class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8"
    >

        <div
            class="md:flex md:items-center md:justify-between"
        >

            <!-- Links -->
            <div
                class="flex justify-center md:justify-start space-x-6 md:order-2"
            >

                <a
                    href="#"
                    class="text-gray-400 hover:text-gray-500 text-sm transition-colors"
                >
                    Terms
                </a>

                <a
                    href="#"
                    class="text-gray-400 hover:text-gray-500 text-sm transition-colors"
                >
                    Privacy
                </a>

                <a
                    href="#"
                    class="text-gray-400 hover:text-gray-500 text-sm transition-colors"
                >
                    Help
                </a>

            </div>


            <!-- Copyright -->
            <div
                class="mt-8 md:mt-0 md:order-1"
            >

                <p
                    class="text-center text-sm text-gray-500"
                >
                    © 2024 LocalEase Inc. All rights reserved.
                </p>

            </div>

        </div>

    </div>

</footer>

</body>
</html>