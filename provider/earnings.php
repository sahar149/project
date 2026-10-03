<?php
/**
 * Dabberha (دبرها) - Provider Earnings & Financial Reports
 * Location: provider/earnings.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';

require_once __DIR__ . '/../includes/db/bookings_db.php';

requireRole('provider');

$provider_id = getUserId();

// جلب المعاملات والحجوزات المكتملة
$completed_bookings = getProviderBookings($provider_id, 'completed');

// الإحصائيات المالية
$total_earnings = getProviderEarnings($provider_id);
$completed_count = count($completed_bookings);
$avg_order_value = $completed_count > 0 ? ($total_earnings / $completed_count) : 0.0;

// حساب أرباح الشهر الحالي
$current_month = date('Y-m');
$this_month_earnings = 0.0;
foreach ($completed_bookings as $b) {
    if (isset($b['booking_date']) && strpos($b['booking_date'], $current_month) === 0) {
        $this_month_earnings += (float)($b['total_price'] ?? 0);
    }
}

renderHead(['title' => __('Earnings & Financials') . ' - ' . __('Dabberha')]);
?>

<div class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/header.php'; ?>

    <div class="flex-1 flex flex-col md:flex-row">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="flex-1 p-6 md:p-8 lg:p-10 max-w-7xl mx-auto w-full space-y-8">
            <!-- رأس الصفحة -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-brand-900 flex items-center gap-3">
                        <i class="fa-solid fa-wallet text-brand-primary"></i>
                        <span><?php echo __('Earnings & Financials'); ?></span>
                    </h1>
                    <p class="text-brand-textMuted mt-1">
                        <?php echo __('Track your revenue, completed orders, and financial summary'); ?>
                    </p>
                </div>
            </div>

            <!-- بطاقات مؤشرات الأداء المالي (KPI Cards) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- إجمالي الإيرادات -->
                <div class="bg-white rounded-2xl shadow-soft border border-brand-border p-6 flex flex-col justify-between hover:border-brand-primary/40 transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold text-brand-textMuted"><?php echo __('Total Revenue'); ?></span>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="fa-solid fa-coins text-lg"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-black text-brand-primary mb-1" dir="rtl">
                        <?php echo number_format($total_earnings, 2); ?> <span class="text-lg">د.ل</span>
                    </div>
                    <p class="text-xs text-brand-textMuted"><?php echo __('From completed jobs'); ?></p>
                </div>

                <!-- أرباح الشهر الحالي -->
                <div class="bg-white rounded-2xl shadow-soft border border-brand-border p-6 flex flex-col justify-between hover:border-brand-primary/40 transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold text-brand-textMuted"><?php echo __('This Month\'s Earnings'); ?></span>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-chart-line text-lg"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-black text-brand-900 mb-1" dir="rtl">
                        <?php echo number_format($this_month_earnings, 2); ?> <span class="text-lg">د.ل</span>
                    </div>
                    <p class="text-xs text-brand-textMuted"><?php echo date('F Y'); ?></p>
                </div>

                <!-- الطلبات المكتملة -->
                <div class="bg-white rounded-2xl shadow-soft border border-brand-border p-6 flex flex-col justify-between hover:border-brand-primary/40 transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold text-brand-textMuted"><?php echo __('Completed Paid Orders'); ?></span>
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <i class="fa-solid fa-circle-check text-lg"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-black text-brand-900 mb-1">
                        <?php echo $completed_count; ?>
                    </div>
                    <p class="text-xs text-brand-textMuted"><?php echo __('Successfully delivered'); ?></p>
                </div>

                <!-- متوسط قيمة الطلب -->
                <div class="bg-white rounded-2xl shadow-soft border border-brand-border p-6 flex flex-col justify-between hover:border-brand-primary/40 transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold text-brand-textMuted"><?php echo __('Average Order Value'); ?></span>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i class="fa-solid fa-receipt text-lg"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-black text-brand-900 mb-1" dir="rtl">
                        <?php echo number_format($avg_order_value, 2); ?> <span class="text-lg">د.ل</span>
                    </div>
                    <p class="text-xs text-brand-textMuted"><?php echo __('Per completed service'); ?></p>
                </div>
            </div>

            <!-- جدول سجل المعاملات المالية المكتملة -->
            <div class="bg-white rounded-2xl shadow-soft border border-brand-border overflow-hidden">
                <div class="p-5 border-b border-brand-border bg-brand-surface/60 flex items-center justify-between">
                    <h2 class="font-bold text-base text-brand-900 flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-brand-primary"></i>
                        <span><?php echo __('Completed Transactions'); ?></span>
                    </h2>
                    <span class="text-xs font-bold text-brand-textMuted bg-white px-3 py-1 rounded-xl border border-brand-border">
                        <?php echo $completed_count; ?> <?php echo __('orders'); ?>
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-brand-border bg-brand-surface/40 text-brand-text font-bold text-xs">
                                <th class="py-4 px-6"><?php echo __('ID'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Service'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Customer'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Date & Time'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Price Type'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Paid Amount'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Status'); ?></th>
                                <th class="py-4 px-6 text-center"><?php echo __('Action'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border text-sm">
                            <?php if ($completed_count > 0): ?>
                                <?php foreach ($completed_bookings as $booking): ?>
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
                                        <td class="py-4 px-6 text-xs text-brand-text font-medium">
                                            <?php echo htmlspecialchars(__($booking['price_type'] ?? 'fixed')); ?>
                                        </td>
                                        <td class="py-4 px-6 font-black text-emerald-600" dir="rtl">
                                            +<?php echo number_format($booking['total_price'], 2); ?> د.ل
                                        </td>
                                        <td class="py-4 px-6"><?php echo renderStatusBadge($booking['status']); ?></td>
                                        <td class="py-4 px-6 text-center">
                                            <a href="booking-detail.php?id=<?php echo (int)$booking['id']; ?>" class="inline-flex items-center gap-1 bg-brand-surface hover:bg-brand-surfaceAlt text-brand-text border border-brand-border px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-2xs">
                                                <span><?php echo __('View Details'); ?></span>
                                                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="py-12 px-6 text-center">
                                        <?php echo renderEmptyState('fa-solid fa-wallet', __('No Earnings Recorded Yet'), __('When you complete customer booking requests, your earnings and revenue reports will appear here.')); ?>
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
