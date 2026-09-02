<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';
require_once __DIR__ . '/../includes/components/navbar_public.php';
require_once __DIR__ . '/../includes/components/footer_public.php';
require_once __DIR__ . '/../includes/db/bookings_db.php';
require_once __DIR__ . '/../includes/db/reviews_db.php';

requireLogin();

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($booking_id == 0) {
    header('Location: browse-services.php');
    exit;
}

$booking = getBookingById($booking_id, getUserId(), 'customer');

if (!$booking) {
    header('Location: browse-services.php');
    exit;
}

$has_review = getReviewByBookingId($booking_id) ? true : false;

renderHead(['title' => __('Booking Confirmed') . ' - ' . __('Dabberha')]);
renderPublicNavbar(['active_page' => 'bookings']);
?>

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

                    &nbsp;د.ل

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


<?php renderPublicFooter(['active_page' => 'bookings']); ?>