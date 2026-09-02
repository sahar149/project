<?php
/**
 * Dabberha (دبرها) - Admin Dashboard
 * Location: admin/dashboard.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';
require_once __DIR__ . '/../includes/components/sidebar.php';
require_once __DIR__ . '/../includes/components/header_dashboard.php';

require_once __DIR__ . '/../includes/db/users_db.php';
require_once __DIR__ . '/../includes/db/services_db.php';
require_once __DIR__ . '/../includes/db/bookings_db.php';
require_once __DIR__ . '/../includes/db/reviews_db.php';

requireRole('admin');

// Statistics
$total_users = getUsersCount();
$total_providers = getUsersCount('provider');
$total_customers = getUsersCount('customer');
$total_services = getServicesCount();
$total_bookings = getBookingsCount();
$total_reviews = getReviewsCount();
$pending_bookings = getBookingsCount(null, 'pending');

// Recent activities
$recent_users = getAllUsers(null, 5);
$recent_bookings = getAllBookings(null, 5);

renderHead(['title' => __('Admin Dashboard Overview') . ' - ' . __('Dabberha')]);
?>

<div class="min-h-screen flex flex-col">
    <?php renderDashboardHeader([
        'title' => __('Admin Panel'),
        'subtitle' => __('Dashboard Overview'),
        'role' => 'admin'
    ]); ?>

    <div class="flex-1 flex flex-col md:flex-row">
        <?php renderSidebar('admin', 'dashboard.php'); ?>

        <main class="flex-1 p-6 md:p-8 lg:p-10 max-w-7xl mx-auto w-full">
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-bold flex items-center gap-3 text-brand-900">
                    <i class="fa-solid fa-chart-pie text-brand-primary"></i>
                    <span><?php echo __('Dashboard Overview'); ?></span>
                </h1>
                <p class="text-brand-textMuted mt-1">
                    <?php echo __('Welcome back, '); ?><strong><?php echo htmlspecialchars(getUserName()); ?></strong>!
                </p>
            </div>

            <!-- KPI Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <div class="bg-white rounded-2xl p-6 shadow-soft border border-brand-border flex flex-col justify-between relative overflow-hidden group hover:border-brand-primary/40 transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold text-brand-textMuted"><?php echo __('Users'); ?></span>
                        <div class="w-10 h-10 rounded-xl bg-brand-primaryLight text-brand-primary flex items-center justify-center">
                            <i class="fa-solid fa-users text-lg"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-black text-brand-900 mb-1"><?php echo $total_users; ?></div>
                    <div class="text-xs text-brand-textMuted font-medium">
                        <?php echo $total_providers; ?> <?php echo __('Providers'); ?> | <?php echo $total_customers; ?> <?php echo __('Customers'); ?>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-soft border border-brand-border flex flex-col justify-between relative overflow-hidden group hover:border-brand-primary/40 transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold text-brand-textMuted"><?php echo __('Services'); ?></span>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-briefcase text-lg"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-black text-brand-900 mb-1"><?php echo $total_services; ?></div>
                    <div class="text-xs text-brand-textMuted font-medium"><?php echo __('Total services listed'); ?></div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-soft border border-brand-border flex flex-col justify-between relative overflow-hidden group hover:border-brand-primary/40 transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold text-brand-textMuted"><?php echo __('Bookings'); ?></span>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i class="fa-regular fa-calendar-check text-lg"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-black text-brand-900 mb-1"><?php echo $total_bookings; ?></div>
                    <div class="text-xs text-amber-600 font-bold"><?php echo $pending_bookings; ?> <?php echo __('pending'); ?></div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-soft border border-brand-border flex flex-col justify-between relative overflow-hidden group hover:border-brand-primary/40 transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold text-brand-textMuted"><?php echo __('Reviews'); ?></span>
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <i class="fa-regular fa-star text-lg"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-black text-brand-900 mb-1"><?php echo $total_reviews; ?></div>
                    <div class="text-xs text-brand-textMuted font-medium"><?php echo __('Total reviews given'); ?></div>
                </div>
            </div>

            <!-- Quick Management Links -->
            <div class="bg-white rounded-2xl shadow-soft border border-brand-border p-6 mb-8">
                <div class="flex items-center gap-2 font-bold text-brand-900 text-base mb-4">
                    <i class="fa-solid fa-layer-group text-brand-primary"></i>
                    <span><?php echo __('Management'); ?></span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <a href="users.php" class="flex items-center justify-center gap-2 py-3 px-4 bg-brand-surface hover:bg-brand-primaryLight border border-brand-border hover:border-brand-primary/40 text-brand-text hover:text-brand-primary rounded-xl font-bold text-sm transition-all shadow-xs">
                        <i class="fa-solid fa-users"></i>
                        <span><?php echo __('Users'); ?></span>
                    </a>
                    <a href="categories.php" class="flex items-center justify-center gap-2 py-3 px-4 bg-brand-surface hover:bg-brand-primaryLight border border-brand-border hover:border-brand-primary/40 text-brand-text hover:text-brand-primary rounded-xl font-bold text-sm transition-all shadow-xs">
                        <i class="fa-solid fa-tags"></i>
                        <span><?php echo __('Categories'); ?></span>
                    </a>
                    <a href="services.php" class="flex items-center justify-center gap-2 py-3 px-4 bg-brand-surface hover:bg-brand-primaryLight border border-brand-border hover:border-brand-primary/40 text-brand-text hover:text-brand-primary rounded-xl font-bold text-sm transition-all shadow-xs">
                        <i class="fa-solid fa-briefcase"></i>
                        <span><?php echo __('Services'); ?></span>
                    </a>
                    <a href="bookings.php" class="flex items-center justify-center gap-2 py-3 px-4 bg-brand-surface hover:bg-brand-primaryLight border border-brand-border hover:border-brand-primary/40 text-brand-text hover:text-brand-primary rounded-xl font-bold text-sm transition-all shadow-xs">
                        <i class="fa-regular fa-calendar-check"></i>
                        <span><?php echo __('Bookings'); ?></span>
                    </a>
                </div>
            </div>

            <!-- Recent Activity Tables -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Users -->
                <section class="bg-white rounded-2xl shadow-soft border border-brand-border overflow-hidden flex flex-col">
                    <div class="p-5 border-b border-brand-border flex items-center justify-between bg-brand-surface/50">
                        <h2 class="font-bold text-brand-900 flex items-center gap-2 text-base">
                            <i class="fa-solid fa-user-plus text-brand-primary"></i>
                            <span><?php echo __('Recent Users'); ?></span>
                        </h2>
                        <a href="users.php" class="text-xs text-brand-primary font-bold hover:underline"><?php echo __('View All'); ?></a>
                    </div>
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-right text-sm">
                            <tbody class="divide-y divide-brand-border">
                                <?php if (count($recent_users) > 0): ?>
                                    <?php foreach ($recent_users as $user): ?>
                                        <tr class="hover:bg-brand-surface/40 transition-colors">
                                            <td class="p-4 font-bold text-brand-900"><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td class="p-4 text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo $user['role'] === 'admin' ? 'bg-red-50 text-red-700 border border-red-200' : ($user['role'] === 'provider' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-gray-100 text-gray-700 border border-gray-200'); ?>">
                                                    <?php echo htmlspecialchars(__($user['role'])); ?>
                                                </span>
                                            </td>
                                            <td class="p-4 text-brand-textMuted text-left text-xs"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td class="p-6 text-center text-brand-textMuted" colspan="3"><?php echo __('No users registered yet.'); ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Recent Bookings -->
                <section class="bg-white rounded-2xl shadow-soft border border-brand-border overflow-hidden flex flex-col">
                    <div class="p-5 border-b border-brand-border flex items-center justify-between bg-brand-surface/50">
                        <h2 class="font-bold text-brand-900 flex items-center gap-2 text-base">
                            <i class="fa-regular fa-clock text-brand-primary"></i>
                            <span><?php echo __('Recent Bookings'); ?></span>
                        </h2>
                        <a href="bookings.php" class="text-xs text-brand-primary font-bold hover:underline"><?php echo __('View All'); ?></a>
                    </div>
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-right text-sm">
                            <tbody class="divide-y divide-brand-border">
                                <?php if (count($recent_bookings) > 0): ?>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                        <tr class="hover:bg-brand-surface/40 transition-colors">
                                            <td class="p-4 font-bold text-brand-900"><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                            <td class="p-4 text-brand-textMuted text-xs"><?php echo htmlspecialchars($booking['service_title']); ?></td>
                                            <td class="p-4 text-left"><?php echo renderStatusBadge($booking['status']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td class="p-6 text-center text-brand-textMuted" colspan="3"><?php echo __('No bookings yet.'); ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</div>
</body>
</html>