<?php
/**
 * Dabberha (دبرها) - Bookings (Provider Panel)
 * Location: provider/bookings.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';

require_once __DIR__ . '/../includes/db/bookings_db.php';

requireRole('provider');

$provider_id = getUserId();
$status_filter = $_GET['status'] ?? null;

$bookings = getProviderBookings($provider_id, $status_filter);

renderHead(['title' => __('My Bookings - Provider Dashboard')]);
?>

<div class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/header.php'; ?>

    <div class="flex-1 flex flex-col md:flex-row">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="flex-1 p-6 md:p-8 lg:p-10 max-w-7xl mx-auto w-full">
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-brand-900 flex items-center gap-3">
                        <i class="fa-regular fa-calendar-check text-brand-primary"></i>
                        <span><?php echo $status_filter === 'completed' ? __('Earnings & Completed Jobs') : __('Bookings Management'); ?></span>
                    </h1>
                    <p class="text-brand-textMuted mt-1">
                        <?php echo $status_filter === 'completed' ? __('Track completed orders and revenue') : __('Manage incoming bookings and schedule'); ?>
                    </p>
                </div>

                <!-- Status Filter Tabs -->
                <div class="flex flex-wrap items-center gap-2 bg-brand-surface p-1.5 rounded-2xl border border-brand-border text-xs font-bold">
                    <a href="bookings.php" class="px-3.5 py-2 rounded-xl transition-all <?php echo empty($status_filter) ? 'bg-white text-brand-primary shadow-2xs' : 'text-brand-textMuted hover:text-brand-primary'; ?>">
                        <?php echo __('All'); ?>
                    </a>
                    <a href="bookings.php?status=pending" class="px-3.5 py-2 rounded-xl transition-all <?php echo $status_filter === 'pending' ? 'bg-white text-brand-primary shadow-2xs' : 'text-brand-textMuted hover:text-brand-primary'; ?>">
                        <?php echo __('Pending'); ?>
                    </a>
                    <a href="bookings.php?status=confirmed" class="px-3.5 py-2 rounded-xl transition-all <?php echo $status_filter === 'confirmed' ? 'bg-white text-brand-primary shadow-2xs' : 'text-brand-textMuted hover:text-brand-primary'; ?>">
                        <?php echo __('Confirmed'); ?>
                    </a>
                    <a href="bookings.php?status=completed" class="px-3.5 py-2 rounded-xl transition-all <?php echo $status_filter === 'completed' ? 'bg-white text-brand-primary shadow-2xs' : 'text-brand-textMuted hover:text-brand-primary'; ?>">
                        <?php echo __('Completed'); ?>
                    </a>
                </div>
            </div>

            <!-- Bookings List Table -->
            <div class="bg-white rounded-2xl shadow-soft border border-brand-border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-brand-border bg-brand-surface/60 text-brand-text font-bold text-xs">
                                <th class="py-4 px-6">ID</th>
                                <th class="py-4 px-6"><?php echo __('Service'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Customer'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Date & Time'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Price'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Status'); ?></th>
                                <th class="py-4 px-6 text-center"><?php echo __('Details'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border text-sm">
                            <?php if (count($bookings) > 0): ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr class="hover:bg-brand-surface/40 transition-colors">
                                        <td class="py-4 px-6 text-xs text-brand-textMuted font-mono">#<?php echo (int)$booking['id']; ?></td>
                                        <td class="py-4 px-6 font-bold text-brand-900"><?php echo htmlspecialchars($booking['service_title']); ?></td>
                                        <td class="py-4 px-6 text-brand-textMuted">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-brand-text"><?php echo htmlspecialchars($booking['customer_name']); ?></span>
                                                <span class="text-xs text-brand-textLight"><?php echo htmlspecialchars($booking['customer_phone'] ?? '-'); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 text-xs text-brand-textMuted">
                                            <span class="font-semibold text-brand-text"><?php echo htmlspecialchars($booking['booking_date']); ?></span>
                                            <span class="text-brand-primary block"><?php echo htmlspecialchars($booking['booking_time'] ?? ''); ?></span>
                                        </td>
                                        <td class="py-4 px-6 font-black text-brand-primary" dir="rtl"><?php echo number_format($booking['total_price'], 2); ?> د.ل</td>
                                        <td class="py-4 px-6"><?php echo renderStatusBadge($booking['status']); ?></td>
                                        <td class="py-4 px-6 text-center">
                                            <a href="booking-detail.php?id=<?php echo (int)$booking['id']; ?>" class="inline-flex items-center gap-1 bg-brand-surface hover:bg-brand-surfaceAlt text-brand-text border border-brand-border px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-2xs">
                                                <span><?php echo __('View'); ?></span>
                                                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-12 px-6 text-center">
                                        <?php echo renderEmptyState('fa-regular fa-calendar-xmark', 'No bookings found'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>