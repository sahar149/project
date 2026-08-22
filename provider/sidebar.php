<?php
$current_page = basename($_SERVER['PHP_SELF']);
$nav_link = 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors';
$active_link = 'bg-white text-terracotta border-l-4 border-terracotta group flex items-center px-3 py-2 text-sm font-medium rounded-r-md shadow-sm';
?>
<aside class="w-64 flex-shrink-0 hidden md:block">
    <nav class="space-y-1">
        <a class="<?php echo $current_page === 'dashboard.php' ? $active_link : $nav_link; ?>" href="dashboard.php">
            <i class="fa-solid fa-chart-line mr-3 text-lg<?php echo $current_page === 'dashboard.php' ? '' : ' text-gray-400 group-hover:text-gray-500'; ?>"></i>
            Dashboard
        </a>
        <a class="<?php echo $current_page === 'my-services.php' ? $active_link : $nav_link; ?>" href="my-services.php">
            <i class="fa-solid fa-toolbox mr-3 text-lg<?php echo $current_page === 'my-services.php' ? '' : ' text-gray-400 group-hover:text-gray-500'; ?>"></i>
            My Services
        </a>
        <a class="<?php echo $current_page === 'bookings.php' && (!isset($_GET['status']) || $_GET['status'] !== 'completed') ? $active_link : $nav_link; ?>" href="bookings.php">
            <i class="fa-regular fa-calendar-check mr-3 text-lg<?php echo $current_page === 'bookings.php' ? '' : ' text-gray-400 group-hover:text-gray-500'; ?>"></i>
            Bookings
        </a>
        <a class="<?php echo $current_page === 'reviews.php' ? $active_link : $nav_link; ?>" href="reviews.php">
            <i class="fa-regular fa-star mr-3 text-lg<?php echo $current_page === 'reviews.php' ? '' : ' text-gray-400 group-hover:text-gray-500'; ?>"></i>
            Reviews
        </a>
        <a class="<?php echo $current_page === 'bookings.php' && isset($_GET['status']) && $_GET['status'] === 'completed' ? $active_link : $nav_link; ?>" href="bookings.php?status=completed">
            <i class="fa-solid fa-wallet mr-3 text-lg<?php echo isset($_GET['status']) && $_GET['status'] === 'completed' ? '' : ' text-gray-400 group-hover:text-gray-500'; ?>"></i>
            Earnings
        </a>
        <a class="<?php echo $current_page === 'profile.php' ? $active_link : $nav_link; ?>" href="profile.php">
            <i class="fa-regular fa-user mr-3 text-lg<?php echo $current_page === 'profile.php' ? '' : ' text-gray-400 group-hover:text-gray-500'; ?>"></i>
            Profile
        </a>
        <div class="pt-4 mt-4 border-t border-gray-200">
            <a class="text-red-500 hover:bg-red-50 hover:text-red-600 group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors" href="/local-services-platform/public/logout.php">
                <i class="fa-solid fa-arrow-right-from-bracket mr-3 text-lg"></i>
                Logout
            </a>
        </div>
    </nav>
</aside>