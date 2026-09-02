<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/translations.php';
require_once __DIR__ . '/includes/helpers/ui_helpers.php';
require_once __DIR__ . '/includes/components/head.php';
require_once __DIR__ . '/includes/components/navbar_public.php';
require_once __DIR__ . '/includes/components/footer_public.php';

renderHead(['title' => __('Dabberha | Your Trusted Local Service Marketplace')]);
renderPublicNavbar(['active_page' => 'home']);
?>

<main class="flex-1">


<!-- =====================================================
     القسم الرئيسي (HERO)
===================================================== -->

<section
    class="relative py-20 md:py-24 px-6 overflow-hidden"
>

    <div
        class="max-w-7xl mx-auto flex flex-col items-center text-center"
    >

        <!-- علامة -->

        <div
            class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#fdeae4] text-[#95442b] text-sm font-semibold mb-6"
        >

            <span class="material-symbols-outlined text-[20px]">
                verified
            </span>

            <?php echo __('Trusted Local Services'); ?>

        </div>


        <!-- العنوان الرئيسي -->

        <h1
            class="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight text-[#231916] mb-6 max-w-4xl leading-tight"
        >

            <?php echo __('Expert local help,'); ?>

            <span class="text-[#95442b]">
                <?php echo __('just a click away.'); ?>
            </span>

        </h1>


        <!-- الوصف -->

        <p
            class="text-lg md:text-xl text-[#55433d] mb-10 max-w-2xl leading-relaxed"
        >
            <?php echo __('Connect with trusted professionals in your neighborhood for any home project or service.'); ?>
        </p>

    </div>


    <!-- خلفية زخرفية -->

    <div
        class="absolute -top-24 -right-24 w-96 h-96 bg-[#b45b40]/10 rounded-full blur-[100px] -z-10"
    ></div>

    <div
        class="absolute -bottom-24 -left-24 w-96 h-96 bg-[#ffc3c2]/20 rounded-full blur-[100px] -z-10"
    ></div>

</section>



<!-- =====================================================
     حالة المستخدم المسجل
===================================================== -->

<?php if (isLoggedIn()): ?>

<section class="px-6 pb-8">

    <div class="max-w-7xl mx-auto">

        <div
            class="bg-[#fff1ec] border border-[#dbc1ba]/40 rounded-xl p-5 flex flex-col md:flex-row items-center justify-between gap-4"
        >

            <div class="flex items-center gap-4">

                <div
                    class="w-12 h-12 rounded-full bg-[#fdeae4] flex items-center justify-center"
                >

                    <span class="material-symbols-outlined text-[#95442b]">
                        account_circle
                    </span>

                </div>

                <div>

                    <p class="font-semibold text-[#231916]">

                        <?php echo _e('Welcome back, :name!', ['name' => htmlspecialchars(getUserName())]); ?>

                    </p>

                    <p class="text-sm text-[#55433d]">

                        <?php echo __('You are logged in as'); ?>

                        <strong>
                            <?php 
                            $role = getUserRole();
                            $role_names = [
                                'customer' => 'عميل',
                                'provider' => 'مزود خدمة',
                                'admin' => 'مدير'
                            ];
                            echo htmlspecialchars($role_names[$role] ?? $role);
                            ?>
                        </strong>

                    </p>

                </div>

            </div>


            <!-- إجراءات حسب الدور -->

            <div class="flex flex-wrap gap-3">

                <?php if (getUserRole() === 'customer'): ?>

                    <a
                        href="/local-services-platform/public/browse-services.php"
                        class="bg-[#95442b] text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-[#b45b40] transition"
                    >
                        <?php echo __('Browse Services'); ?>
                    </a>

                    <a
                        href="/local-services-platform/public/my-bookings.php"
                        class="border border-[#95442b] text-[#95442b] px-5 py-2.5 rounded-lg font-semibold hover:bg-white transition"
                    >
                        <?php echo __('My Bookings'); ?>
                    </a>

                <?php elseif (getUserRole() === 'provider'): ?>

                    <a
                        href="/local-services-platform/provider/dashboard.php"
                        class="bg-[#95442b] text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-[#b45b40] transition"
                    >
                        <?php echo __('Go to Dashboard'); ?>
                    </a>

                <?php elseif (getUserRole() === 'admin'): ?>

                    <a
                        href="/local-services-platform/admin/dashboard.php"
                        class="bg-[#95442b] text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-[#b45b40] transition"
                    >
                        <?php echo __('Go to Admin Panel'); ?>
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </div>

</section>

<?php endif; ?>



<!-- =====================================================
     التصنيفات
===================================================== -->

<section
    id="categories"
    class="py-16 px-6 bg-[#fff1ec]/40"
>

    <div class="max-w-7xl mx-auto">

        <div class="flex flex-col items-center mb-12">

            <h2
                class="text-3xl font-semibold text-[#231916] mb-2"
            >
                <?php echo __('Explore Categories'); ?>
            </h2>

            <p class="text-[#55433d] text-center">
                <?php echo __('Find the right professional for your needs.'); ?>
            </p>

            <div
                class="w-12 h-1 bg-[#f3b8b8] rounded-full mt-4"
            ></div>

        </div>


        <div
            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6"
        >


            <!-- سباكة -->

            <a
                href="/local-services-platform/public/browse-services.php?category=plumbing"
                class="flex flex-col items-center p-6 bg-white rounded-xl border border-[#dbc1ba]/20 hover:border-[#ffdbd1] transition-all group cursor-pointer shadow-sm hover:shadow-md"
            >

                <div
                    class="w-16 h-16 rounded-full bg-[#fdeae4] flex items-center justify-center mb-4 group-hover:bg-[#ffdbd1] transition-colors"
                >

                    <span
                        class="material-symbols-outlined text-[32px] text-[#C18B8B]"
                    >
                        plumbing
                    </span>

                </div>

                <span class="font-semibold text-[#231916]">
                    <?php echo __('Plumbing'); ?>
                </span>

            </a>



            <!-- كهرباء -->

            <a
                href="/local-services-platform/public/browse-services.php?category=electrical"
                class="flex flex-col items-center p-6 bg-white rounded-xl border border-[#dbc1ba]/20 hover:border-[#ffdbd1] transition-all group cursor-pointer shadow-sm hover:shadow-md"
            >

                <div
                    class="w-16 h-16 rounded-full bg-[#fdeae4] flex items-center justify-center mb-4 group-hover:bg-[#ffdbd1] transition-colors"
                >

                    <span
                        class="material-symbols-outlined text-[32px] text-[#C18B8B]"
                    >
                        electrical_services
                    </span>

                </div>

                <span class="font-semibold text-[#231916]">
                    <?php echo __('Electrical'); ?>
                </span>

            </a>



            <!-- تنظيف -->

            <a
                href="/local-services-platform/public/browse-services.php?category=cleaning"
                class="flex flex-col items-center p-6 bg-white rounded-xl border border-[#dbc1ba]/20 hover:border-[#ffdbd1] transition-all group cursor-pointer shadow-sm hover:shadow-md"
            >

                <div
                    class="w-16 h-16 rounded-full bg-[#fdeae4] flex items-center justify-center mb-4 group-hover:bg-[#ffdbd1] transition-colors"
                >

                    <span
                        class="material-symbols-outlined text-[32px] text-[#C18B8B]"
                    >
                        cleaning_services
                    </span>

                </div>

                <span class="font-semibold text-[#231916]">
                    <?php echo __('Cleaning'); ?>
                </span>

            </a>



            <!-- بستنة -->

            <a
                href="/local-services-platform/public/browse-services.php?category=gardening"
                class="flex flex-col items-center p-6 bg-white rounded-xl border border-[#dbc1ba]/20 hover:border-[#ffdbd1] transition-all group cursor-pointer shadow-sm hover:shadow-md"
            >

                <div
                    class="w-16 h-16 rounded-full bg-[#fdeae4] flex items-center justify-center mb-4 group-hover:bg-[#ffdbd1] transition-colors"
                >

                    <span
                        class="material-symbols-outlined text-[32px] text-[#C18B8B]"
                    >
                        yard
                    </span>

                </div>

                <span class="font-semibold text-[#231916]">
                    <?php echo __('Gardening'); ?>
                </span>

            </a>



            <!-- نقل -->

            <a
                href="/local-services-platform/public/browse-services.php?category=moving"
                class="flex flex-col items-center p-6 bg-white rounded-xl border border-[#dbc1ba]/20 hover:border-[#ffdbd1] transition-all group cursor-pointer shadow-sm hover:shadow-md"
            >

                <div
                    class="w-16 h-16 rounded-full bg-[#fdeae4] flex items-center justify-center mb-4 group-hover:bg-[#ffdbd1] transition-colors"
                >

                    <span
                        class="material-symbols-outlined text-[32px] text-[#C18B8B]"
                    >
                        local_shipping
                    </span>

                </div>

                <span class="font-semibold text-[#231916]">
                    <?php echo __('Moving'); ?>
                </span>

            </a>



            <!-- دهان -->

            <a
                href="/local-services-platform/public/browse-services.php?category=painting"
                class="flex flex-col items-center p-6 bg-white rounded-xl border border-[#dbc1ba]/20 hover:border-[#ffdbd1] transition-all group cursor-pointer shadow-sm hover:shadow-md"
            >

                <div
                    class="w-16 h-16 rounded-full bg-[#fdeae4] flex items-center justify-center mb-4 group-hover:bg-[#ffdbd1] transition-colors"
                >

                    <span
                        class="material-symbols-outlined text-[32px] text-[#C18B8B]"
                    >
                        format_paint
                    </span>

                </div>

                <span class="font-semibold text-[#231916]">
                    <?php echo __('Painting'); ?>
                </span>

            </a>

        </div>

    </div>

</section>



<!-- =====================================================
     كيف يعمل دابرهة
===================================================== -->

<section
    id="how-it-works"
    class="py-20 px-6"
>

    <div class="max-w-7xl mx-auto">

        <div class="text-center mb-14">

            <h2
                class="text-3xl font-semibold text-[#231916] mb-3"
            >
                <?php echo __('How Dabberha Works'); ?>
            </h2>

            <p class="text-[#55433d]">
                <?php echo __('Getting the help you need is simple.'); ?>
            </p>

        </div>


        <div
            class="grid grid-cols-1 md:grid-cols-3 gap-8"
        >


            <!-- الخطوة 1 -->

            <div
                class="text-center p-8 rounded-xl bg-white border border-[#dbc1ba]/20 shadow-sm"
            >

                <div
                    class="w-16 h-16 mx-auto rounded-full bg-[#fdeae4] flex items-center justify-center mb-5"
                >

                    <span
                        class="material-symbols-outlined text-[32px] text-[#95442b]"
                    >
                        search
                    </span>

                </div>

                <div
                    class="text-sm font-bold text-[#95442b] mb-2"
                >
                    <?php echo __('STEP 01'); ?>
                </div>

                <h3
                    class="text-xl font-semibold mb-3"
                >
                    <?php echo __('Find a Service'); ?>
                </h3>

                <p class="text-[#55433d] leading-relaxed">
                    <?php echo __('Search for the service you need and discover professionals available in your area.'); ?>
                </p>

            </div>



            <!-- الخطوة 2 -->

            <div
                class="text-center p-8 rounded-xl bg-white border border-[#dbc1ba]/20 shadow-sm"
            >

                <div
                    class="w-16 h-16 mx-auto rounded-full bg-[#fdeae4] flex items-center justify-center mb-5"
                >

                    <span
                        class="material-symbols-outlined text-[32px] text-[#95442b]"
                    >
                        person_search
                    </span>

                </div>

                <div
                    class="text-sm font-bold text-[#95442b] mb-2"
                >
                    <?php echo __('STEP 02'); ?>
                </div>

                <h3
                    class="text-xl font-semibold mb-3"
                >
                    <?php echo __('Choose a Professional'); ?>
                </h3>

                <p class="text-[#55433d] leading-relaxed">
                    <?php echo __('Compare service providers and choose the professional that best fits your needs.'); ?>
                </p>

            </div>



            <!-- الخطوة 3 -->

            <div
                class="text-center p-8 rounded-xl bg-white border border-[#dbc1ba]/20 shadow-sm"
            >

                <div
                    class="w-16 h-16 mx-auto rounded-full bg-[#fdeae4] flex items-center justify-center mb-5"
                >

                    <span
                        class="material-symbols-outlined text-[32px] text-[#95442b]"
                    >
                        event_available
                    </span>

                </div>

                <div
                    class="text-sm font-bold text-[#95442b] mb-2"
                >
                    <?php echo __('STEP 03'); ?>
                </div>

                <h3
                    class="text-xl font-semibold mb-3"
                >
                    <?php echo __('Book Your Service'); ?>
                </h3>

                <p class="text-[#55433d] leading-relaxed">
                    <?php echo __('Book the service you need online quickly and conveniently.'); ?>
                </p>

            </div>

        </div>

    </div>

</section>



<!-- =====================================================
     دعوة مقدمي الخدمات
===================================================== -->

<section class="py-12 px-6">

    <div
        class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6"
    >


        <!-- انضم كمحترف -->

        <div
            class="md:col-span-2 bg-[#392e2a] rounded-xl p-10 md:p-12 relative overflow-hidden flex flex-col justify-center"
        >

            <h2
                class="text-3xl md:text-4xl font-bold text-[#ffede7] mb-4"
            >
                <?php echo __('Are you a service professional?'); ?>
            </h2>

            <p
                class="text-lg text-[#f7e4de] mb-8 max-w-md leading-relaxed"
            >
                <?php echo __('Grow your business with Dabberha. Reach local customers searching for your expertise.'); ?>
            </p>

            <a
                href="/local-services-platform/public/register.php"
                class="bg-[#ffdbd1] text-[#3a0a00] px-8 py-3 rounded-lg font-semibold w-fit hover:bg-[#ffb59f] transition-colors"
            >
                <?php echo __('Join as a Pro'); ?>
            </a>


            <div
                class="absolute -right-20 -bottom-20 w-80 h-80 bg-[#95442b]/20 rounded-full blur-[60px]"
            ></div>

        </div>



        <!-- الأمان أولاً -->

        <div
            class="bg-[#ffc3c2] rounded-xl p-10 md:p-12 flex flex-col items-center text-center justify-center border border-[#dbc1ba]/30"
        >

            <span
                class="material-symbols-outlined text-[#7b4d4e] text-5xl mb-4"
            >
                verified_user
            </span>

            <h3
                class="text-2xl font-semibold text-[#7b4d4e] mb-2"
            >
                <?php echo __('Safety First'); ?>
            </h3>

            <p
                class="text-[#7b4d4e]/80 mb-6"
            >
                <?php echo __('We want every customer to feel confident when choosing a local service provider.'); ?>
            </p>

            <a
                href="#"
                class="font-semibold text-[#7b4d4e] underline"
            >
                <?php echo __('Learn about safety'); ?>
            </a>

        </div>

    </div>

</section>



<!-- =====================================================
     المميزات
===================================================== -->

<section class="py-16 px-6">

    <div class="max-w-7xl mx-auto">

        <div
            class="grid grid-cols-1 md:grid-cols-3 gap-6"
        >


            <!-- البحث عن خدمات -->

            <div
                class="bg-white rounded-xl p-8 border border-[#dbc1ba]/20 shadow-sm hover:shadow-md transition"
            >

                <div
                    class="w-14 h-14 rounded-full bg-[#fdeae4] flex items-center justify-center mb-5"
                >

                    <span
                        class="material-symbols-outlined text-[28px] text-[#95442b]"
                    >
                        handyman
                    </span>

                </div>

                <h3
                    class="text-xl font-semibold mb-3"
                >
                    <?php echo __('Find Services'); ?>
                </h3>

                <p class="text-[#55433d] leading-relaxed">
                    <?php echo __('Search for plumbers, electricians, cleaners, tutors and many other local professionals.'); ?>
                </p>

            </div>



            <!-- قراءة التقييمات -->

            <div
                class="bg-white rounded-xl p-8 border border-[#dbc1ba]/20 shadow-sm hover:shadow-md transition"
            >

                <div
                    class="w-14 h-14 rounded-full bg-[#fdeae4] flex items-center justify-center mb-5"
                >

                    <span
                        class="material-symbols-outlined text-[28px] text-[#95442b]"
                    >
                        star
                    </span>

                </div>

                <h3
                    class="text-xl font-semibold mb-3"
                >
                    <?php echo __('Read Reviews'); ?>
                </h3>

                <p class="text-[#55433d] leading-relaxed">
                    <?php echo __('See what other customers say about service providers before making your choice.'); ?>
                </p>

            </div>



            <!-- حجز سهل -->

            <div
                class="bg-white rounded-xl p-8 border border-[#dbc1ba]/20 shadow-sm hover:shadow-md transition"
            >

                <div
                    class="w-14 h-14 rounded-full bg-[#fdeae4] flex items-center justify-center mb-5"
                >

                    <span
                        class="material-symbols-outlined text-[28px] text-[#95442b]"
                    >
                        calendar_month
                    </span>

                </div>

                <h3
                    class="text-xl font-semibold mb-3"
                >
                    <?php echo __('Easy Booking'); ?>
                </h3>

                <p class="text-[#55433d] leading-relaxed">
                    <?php echo __('Book the service you need online quickly and conveniently.'); ?>
                </p>

            </div>

        </div>

    </div>

</section>


</main>



<!-- =====================================================
</main>

<?php renderPublicFooter(['active_page' => 'home']); ?>