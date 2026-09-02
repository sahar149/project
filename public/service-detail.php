<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';
require_once __DIR__ . '/../includes/components/navbar_public.php';
require_once __DIR__ . '/../includes/components/footer_public.php';

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
           c.icon as category_icon,
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

$review_word = ($service['review_count'] == 1) ? __('Review') : __('Reviews');
$service_icon = getCategoryFAIcon($service['category_name'], $service['category_icon'] ?? '');

renderHead(['title' => $service['title'] . ' - Dabberha']);
renderPublicNavbar(['active_page' => 'services']);
?>

<main class="max-w-7xl mx-auto px-6 py-12 flex-1">

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
                        الأيقونة ديناميكية حسب Category التي أضافها الأدمن
                    -->

                    <i
                        class="<?php echo htmlspecialchars($service_icon); ?> text-primary text-5xl md:text-6xl"
                        aria-hidden="true"
                    ></i>

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
                            class="p-3 bg-surface-container-low rounded-lg text-primary flex items-center justify-center"
                        >

                            <i
                                class="<?php echo htmlspecialchars($service_icon); ?> text-2xl"
                                aria-hidden="true"
                            ></i>

                        </div>


                        <!-- Price -->

                        <div class="text-left">

                        


                              <span dir="rtl" class="text-headline-md text-primary font-bold">
    <?php echo number_format($service['price'], 2); ?>
    <span dir="rtl"> د.ل</span>
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
                        <div class="pt-4 border-t border-surface-variant space-y-3">
                            <div class="flex justify-between items-center text-label-lg">
                                <span class="text-on-surface-variant font-medium">
                                    <?php echo __('Service Price'); ?>
                                </span>
                                <span dir="rtl" class="text-headline-md text-primary font-bold">
                                    <?php echo number_format($service['price'], 2); ?>
                                    <span>د.ل</span>
                                </span>
                            </div>
                        </div>

                        <!-- Book Button -->
                        <button
                            type="submit"
                            class="w-full py-3.5 bg-primary hover:bg-[#7a2f18] text-white rounded-xl font-bold text-base shadow-ambient hover:shadow-floating transition-all flex items-center justify-center gap-2 active:scale-95 mt-4"
                        >
                            <span><?php echo __('Book Now'); ?></span>
                            <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
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


<?php renderPublicFooter(['active_page' => 'services']); ?>