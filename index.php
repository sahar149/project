<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dabberha | Your Trusted Local Service Marketplace</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <!-- Material Symbols -->
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet"
    >

    <style>

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #fff8f6;
        }

        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24;
        }

    </style>

    <script>

        tailwind.config = {

            theme: {

                extend: {

                    colors: {

                        primary: "#95442b",
                        primaryContainer: "#b45b40",

                        secondary: "#805252",
                        secondaryContainer: "#ffc3c2",

                        background: "#fff8f6",
                        surface: "#fff8f6",

                        surfaceContainer: "#fdeae4",
                        surfaceContainerLow: "#fff1ec",
                        surfaceContainerHighest: "#f1dfd8",

                        outline: "#88726c",
                        outlineVariant: "#dbc1ba",

                        onSurface: "#231916",
                        onSurfaceVariant: "#55433d",

                        onPrimary: "#ffffff",
                        onPrimaryContainer: "#fffbff"

                    },

                    fontFamily: {

                        jakarta: [
                            "Plus Jakarta Sans",
                            "sans-serif"
                        ]

                    }

                }

            }

        };

    </script>

</head>


<body class="bg-[#fff8f6] text-[#231916] antialiased">


<!-- =====================================================
     NAVBAR
===================================================== -->

<header
    class="bg-stone-50/90 backdrop-blur-md sticky top-0 z-50 border-b border-stone-200/60 shadow-sm"
>

    <div
        class="flex justify-between items-center px-6 py-4 max-w-7xl mx-auto"
    >

        <!-- Logo -->

        <a
            href="/local-services-platform/index.php"
            class="text-2xl font-bold tracking-tight text-[#95442b]"
        >
            Dabberha
        </a>


        <!-- Desktop Navigation -->

        <nav class="hidden md:flex items-center gap-8">

            <a
                href="/local-services-platform/public/browse-services.php"
                class="text-[#95442b] border-b-2 border-[#95442b] pb-1 font-medium hover:text-[#b45b40] transition-colors"
            >
                Find Services
            </a>

            <a
                href="#how-it-works"
                class="text-stone-600 font-medium hover:text-[#95442b] transition-colors"
            >
                How it Works
            </a>

            <a
                href="#categories"
                class="text-stone-600 font-medium hover:text-[#95442b] transition-colors"
            >
                Categories
            </a>

        </nav>


        <!-- Right Side -->

        <div class="flex items-center gap-3">

            <?php if (isLoggedIn()): ?>

                <!-- User Name -->

                <span
                    class="hidden sm:flex items-center gap-2 text-sm font-semibold text-stone-700"
                >

                    <span class="material-symbols-outlined text-[#95442b]">
                        person
                    </span>

                    <?php echo htmlspecialchars(getUserName()); ?>

                </span>


                <!-- Customer -->

                <?php if (getUserRole() === 'customer'): ?>

                    <a
                        href="/local-services-platform/public/my-bookings.php"
                        class="hidden sm:flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-[#95442b] hover:bg-[#fff1ec] transition"
                    >

                        <span class="material-symbols-outlined text-[20px]">
                            event_note
                        </span>

                        My Bookings

                    </a>

                <?php endif; ?>


                <!-- Provider -->

                <?php if (getUserRole() === 'provider'): ?>

                    <a
                        href="/local-services-platform/provider/dashboard.php"
                        class="hidden sm:flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-[#95442b] hover:bg-[#fff1ec] transition"
                    >

                        <span class="material-symbols-outlined text-[20px]">
                            dashboard
                        </span>

                        Dashboard

                    </a>

                <?php endif; ?>


                <!-- Admin -->

                <?php if (getUserRole() === 'admin'): ?>

                    <a
                        href="/local-services-platform/admin/dashboard.php"
                        class="hidden sm:flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-[#95442b] hover:bg-[#fff1ec] transition"
                    >

                        <span class="material-symbols-outlined text-[20px]">
                            admin_panel_settings
                        </span>

                        Admin Panel

                    </a>

                <?php endif; ?>


                <!-- Logout -->

                <a
                    href="/local-services-platform/public/logout.php"
                    class="bg-[#95442b] text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-[#b45b40] active:scale-95 transition-all"
                >
                    Logout
                </a>


            <?php else: ?>

                <!-- Login -->

                <a
                    href="/local-services-platform/public/login.php"
                    class="bg-[#95442b] text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-[#b45b40] active:scale-95 transition-all"
                >
                    Sign In
                </a>

            <?php endif; ?>

        </div>

    </div>

</header>



<main>


<!-- =====================================================
     HERO
===================================================== -->

<section
    class="relative py-20 md:py-24 px-6 overflow-hidden"
>

    <div
        class="max-w-7xl mx-auto flex flex-col items-center text-center"
    >

        <!-- Badge -->

        <div
            class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#fdeae4] text-[#95442b] text-sm font-semibold mb-6"
        >

            <span class="material-symbols-outlined text-[20px]">
                verified
            </span>

            Trusted Local Services

        </div>


        <!-- Main Heading -->

        <h1
            class="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight text-[#231916] mb-6 max-w-4xl leading-tight"
        >

            Expert local help,

            <span class="text-[#95442b]">
                just a click away.
            </span>

        </h1>


        <!-- Description -->

        <p
            class="text-lg md:text-xl text-[#55433d] mb-10 max-w-2xl leading-relaxed"
        >
            Connect with trusted professionals in your neighborhood
            for any home project or service.
        </p>


        <!-- =================================================
             SEARCH
        ================================================== -->

        <form
            action="/local-services-platform/public/browse-services.php"
            method="GET"
            class="w-full max-w-3xl bg-white p-2 rounded-xl shadow-[0_4px_20px_rgba(58,47,43,0.08)] flex flex-col md:flex-row gap-2 border border-[#dbc1ba]/30"
        >

            <!-- Service Search -->

            <div
                class="flex-1 flex items-center px-4 py-3 gap-3"
            >

                <span class="material-symbols-outlined text-[#88726c]">
                    search
                </span>

                <input
                    type="text"
                    name="search"
                    placeholder="What service?"
                    autocomplete="off"
                    class="w-full bg-transparent border-none focus:ring-0 text-[#231916] placeholder:text-[#88726c] outline-none"
                >

            </div>


            <!-- Search Button -->

            <button
                type="submit"
                class="bg-[#95442b] text-white px-10 py-3 rounded-lg font-semibold hover:bg-[#b45b40] hover:shadow-lg active:scale-[0.98] transition-all"
            >

                Search

            </button>

        </form>

    </div>


    <!-- Decorative Background -->

    <div
        class="absolute -top-24 -right-24 w-96 h-96 bg-[#b45b40]/10 rounded-full blur-[100px] -z-10"
    ></div>

    <div
        class="absolute -bottom-24 -left-24 w-96 h-96 bg-[#ffc3c2]/20 rounded-full blur-[100px] -z-10"
    ></div>

</section>



<!-- =====================================================
     LOGGED USER STATUS
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

                        Welcome back,
                        <?php echo htmlspecialchars(getUserName()); ?>!

                    </p>

                    <p class="text-sm text-[#55433d]">

                        You are logged in as

                        <strong>
                            <?php echo htmlspecialchars(getUserRole()); ?>
                        </strong>

                    </p>

                </div>

            </div>


            <!-- Role Actions -->

            <div class="flex flex-wrap gap-3">

                <?php if (getUserRole() === 'customer'): ?>

                    <a
                        href="/local-services-platform/public/browse-services.php"
                        class="bg-[#95442b] text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-[#b45b40] transition"
                    >
                        Browse Services
                    </a>

                    <a
                        href="/local-services-platform/public/my-bookings.php"
                        class="border border-[#95442b] text-[#95442b] px-5 py-2.5 rounded-lg font-semibold hover:bg-white transition"
                    >
                        My Bookings
                    </a>

                <?php elseif (getUserRole() === 'provider'): ?>

                    <a
                        href="/local-services-platform/provider/dashboard.php"
                        class="bg-[#95442b] text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-[#b45b40] transition"
                    >
                        Go to Dashboard
                    </a>

                <?php elseif (getUserRole() === 'admin'): ?>

                    <a
                        href="/local-services-platform/admin/dashboard.php"
                        class="bg-[#95442b] text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-[#b45b40] transition"
                    >
                        Go to Admin Panel
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </div>

</section>

<?php endif; ?>



<!-- =====================================================
     CATEGORIES
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
                Explore Categories
            </h2>

            <p class="text-[#55433d] text-center">
                Find the right professional for your needs.
            </p>

            <div
                class="w-12 h-1 bg-[#f3b8b8] rounded-full mt-4"
            ></div>

        </div>


        <div
            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6"
        >


            <!-- Plumbing -->

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
                    Plumbing
                </span>

            </a>



            <!-- Electrical -->

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
                    Electrical
                </span>

            </a>



            <!-- Cleaning -->

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
                    Cleaning
                </span>

            </a>



            <!-- Gardening -->

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
                    Gardening
                </span>

            </a>



            <!-- Moving -->

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
                    Moving
                </span>

            </a>



            <!-- Painting -->

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
                    Painting
                </span>

            </a>

        </div>

    </div>

</section>



<!-- =====================================================
     HOW IT WORKS
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
                How Dabberha Works
            </h2>

            <p class="text-[#55433d]">
                Getting the help you need is simple.
            </p>

        </div>


        <div
            class="grid grid-cols-1 md:grid-cols-3 gap-8"
        >


            <!-- Step 1 -->

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
                    STEP 01
                </div>

                <h3
                    class="text-xl font-semibold mb-3"
                >
                    Find a Service
                </h3>

                <p class="text-[#55433d] leading-relaxed">
                    Search for the service you need and discover
                    professionals available in your area.
                </p>

            </div>



            <!-- Step 2 -->

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
                    STEP 02
                </div>

                <h3
                    class="text-xl font-semibold mb-3"
                >
                    Choose a Professional
                </h3>

                <p class="text-[#55433d] leading-relaxed">
                    Compare service providers and choose the
                    professional that best fits your needs.
                </p>

            </div>



            <!-- Step 3 -->

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
                    STEP 03
                </div>

                <h3
                    class="text-xl font-semibold mb-3"
                >
                    Book Your Service
                </h3>

                <p class="text-[#55433d] leading-relaxed">
                    Book the service you need online quickly
                    and conveniently.
                </p>

            </div>

        </div>

    </div>

</section>



<!-- =====================================================
     SERVICE PROVIDER CTA
===================================================== -->

<section class="py-12 px-6">

    <div
        class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6"
    >


        <!-- Join as Pro -->

        <div
            class="md:col-span-2 bg-[#392e2a] rounded-xl p-10 md:p-12 relative overflow-hidden flex flex-col justify-center"
        >

            <h2
                class="text-3xl md:text-4xl font-bold text-[#ffede7] mb-4"
            >
                Are you a service professional?
            </h2>

            <p
                class="text-lg text-[#f7e4de] mb-8 max-w-md leading-relaxed"
            >
                Grow your business with Dabberha.
                Reach local customers searching for your expertise.
            </p>

            <a
                href="/local-services-platform/public/register.php"
                class="bg-[#ffdbd1] text-[#3a0a00] px-8 py-3 rounded-lg font-semibold w-fit hover:bg-[#ffb59f] transition-colors"
            >
                Join as a Pro
            </a>


            <div
                class="absolute -right-20 -bottom-20 w-80 h-80 bg-[#95442b]/20 rounded-full blur-[60px]"
            ></div>

        </div>



        <!-- Safety -->

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
                Safety First
            </h3>

            <p
                class="text-[#7b4d4e]/80 mb-6"
            >
                We want every customer to feel confident
                when choosing a local service provider.
            </p>

            <a
                href="#"
                class="font-semibold text-[#7b4d4e] underline"
            >
                Learn about safety
            </a>

        </div>

    </div>

</section>



<!-- =====================================================
     FEATURES
===================================================== -->

<section class="py-16 px-6">

    <div class="max-w-7xl mx-auto">

        <div
            class="grid grid-cols-1 md:grid-cols-3 gap-6"
        >


            <!-- Find Services -->

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
                    Find Services
                </h3>

                <p class="text-[#55433d] leading-relaxed">
                    Search for plumbers, electricians, cleaners,
                    tutors and many other local professionals.
                </p>

            </div>



            <!-- Reviews -->

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
                    Read Reviews
                </h3>

                <p class="text-[#55433d] leading-relaxed">
                    See what other customers say about service
                    providers before making your choice.
                </p>

            </div>



            <!-- Booking -->

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
                    Easy Booking
                </h3>

                <p class="text-[#55433d] leading-relaxed">
                    Book the service you need online quickly
                    and conveniently.
                </p>

            </div>

        </div>

    </div>

</section>


</main>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer
    class="bg-stone-100 w-full py-12 px-6 mt-16 border-t border-stone-200"
>

    <div
        class="flex flex-col md:flex-row justify-between items-center gap-6 max-w-7xl mx-auto"
    >

        <!-- Logo -->

        <div class="flex flex-col gap-2 text-center md:text-left">

            <span
                class="font-bold text-[#95442b] text-xl"
            >
                Dabberha
            </span>

            <p class="text-sm text-stone-600">

                © <?php echo date('Y'); ?>

                Dabberha Local Services.
                All rights reserved.

            </p>

        </div>


        <!-- Footer Links -->

        <div class="flex flex-wrap justify-center gap-6">

            <a
                href="#"
                class="text-sm text-stone-500 hover:text-[#95442b] transition-colors"
            >
                Privacy Policy
            </a>

            <a
                href="#"
                class="text-sm text-stone-500 hover:text-[#95442b] transition-colors"
            >
                Terms of Service
            </a>

            <a
                href="#"
                class="text-sm text-stone-500 hover:text-[#95442b] transition-colors"
            >
                Help Center
            </a>

            <a
                href="#"
                class="text-sm text-stone-500 hover:text-[#95442b] transition-colors"
            >
                Contact Us
            </a>

        </div>

    </div>

</footer>



<!-- =====================================================
     MOBILE NAVIGATION
===================================================== -->

<nav
    class="md:hidden fixed bottom-0 left-0 w-full flex justify-around items-center px-4 pb-5 pt-3 bg-white/95 backdrop-blur-lg border-t border-stone-100 shadow-[0_-4px_20px_rgba(58,47,43,0.05)] z-50 rounded-t-2xl"
>


    <!-- Home -->

    <a
        href="/local-services-platform/index.php"
        class="flex flex-col items-center justify-center text-[#95442b] bg-[#fff1ec] rounded-xl px-4 py-1"
    >

        <span class="material-symbols-outlined">
            home
        </span>

        <span class="text-[11px] font-semibold">
            Home
        </span>

    </a>



    <!-- Explore -->

    <a
        href="/local-services-platform/public/browse-services.php"
        class="flex flex-col items-center justify-center text-stone-500 hover:bg-stone-50 rounded-xl px-4 py-1 transition"
    >

        <span class="material-symbols-outlined">
            search
        </span>

        <span class="text-[11px] font-semibold">
            Explore
        </span>

    </a>



    <!-- Bookings / Join -->

    <?php if (isLoggedIn() && getUserRole() === 'customer'): ?>

        <a
            href="/local-services-platform/public/my-bookings.php"
            class="flex flex-col items-center justify-center text-stone-500 hover:bg-stone-50 rounded-xl px-4 py-1 transition"
        >

            <span class="material-symbols-outlined">
                event_note
            </span>

            <span class="text-[11px] font-semibold">
                Bookings
            </span>

        </a>

    <?php else: ?>

        <a
            href="/local-services-platform/public/register.php"
            class="flex flex-col items-center justify-center text-stone-500 hover:bg-stone-50 rounded-xl px-4 py-1 transition"
        >

            <span class="material-symbols-outlined">
                person_add
            </span>

            <span class="text-[11px] font-semibold">
                Join
            </span>

        </a>

    <?php endif; ?>



    <!-- Profile -->

    <?php if (isLoggedIn()): ?>

        <?php if (getUserRole() === 'provider'): ?>

            <a
                href="/local-services-platform/provider/dashboard.php"
                class="flex flex-col items-center justify-center text-stone-500 hover:bg-stone-50 rounded-xl px-4 py-1 transition"
            >

                <span class="material-symbols-outlined">
                    person
                </span>

                <span class="text-[11px] font-semibold">
                    Profile
                </span>

            </a>

        <?php elseif (getUserRole() === 'admin'): ?>

            <a
                href="/local-services-platform/admin/dashboard.php"
                class="flex flex-col items-center justify-center text-stone-500 hover:bg-stone-50 rounded-xl px-4 py-1 transition"
            >

                <span class="material-symbols-outlined">
                    admin_panel_settings
                </span>

                <span class="text-[11px] font-semibold">
                    Admin
                </span>

            </a>

        <?php else: ?>

            <a
                href="/local-services-platform/public/my-bookings.php"
                class="flex flex-col items-center justify-center text-stone-500 hover:bg-stone-50 rounded-xl px-4 py-1 transition"
            >

                <span class="material-symbols-outlined">
                    person
                </span>

                <span class="text-[11px] font-semibold">
                    Profile
                </span>

            </a>

        <?php endif; ?>

    <?php else: ?>

        <a
            href="/local-services-platform/public/login.php"
            class="flex flex-col items-center justify-center text-stone-500 hover:bg-stone-50 rounded-xl px-4 py-1 transition"
        >

            <span class="material-symbols-outlined">
                person
            </span>

            <span class="text-[11px] font-semibold">
                Login
            </span>

        </a>

    <?php endif; ?>

</nav>



<!-- =====================================================
     SUPPORT BUTTON
===================================================== -->

<div
    class="fixed bottom-24 right-6 md:bottom-8 md:right-8 z-40"
>

    <button
        type="button"
        title="Support"
        class="bg-[#95442b] text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg hover:shadow-xl hover:bg-[#b45b40] active:scale-95 transition-all"
    >

        <span class="material-symbols-outlined text-2xl">
            support_agent
        </span>

    </button>

</div>


</body>

</html>