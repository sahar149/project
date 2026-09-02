<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';
require_once __DIR__ . '/../includes/components/navbar_public.php';
require_once __DIR__ . '/../includes/components/footer_public.php';
require_once __DIR__ . '/../includes/db/bookings_db.php';

requireRole('customer');

$customer_id = getUserId();
$bookings = getCustomerBookings($customer_id);

function getServiceIcon($category_name, $category_icon = '') {
    return getCategoryFAIcon($category_name, $category_icon);
}

renderHead(['title' => __('My Bookings - Dabberha')]);
renderPublicNavbar(['active_page' => 'bookings']);
?>

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
                        $booking['category_name'] ?? '',
                        $booking['category_icon'] ?? ''
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
                            class="w-16 h-16 min-w-[64px] rounded-full bg-surface-container flex items-center justify-center text-primary text-2xl"
                        >

                            <i class="<?php echo htmlspecialchars($service_icon); ?>"></i>

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

<?php renderPublicFooter(['active_page' => 'bookings']); ?>