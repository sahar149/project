<?php
/**
 * Dabberha (دبرها) - Provider Dashboard
 * Location: provider/dashboard.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notifications.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';

require_once __DIR__ . '/../includes/db/services_db.php';
require_once __DIR__ . '/../includes/db/bookings_db.php';
require_once __DIR__ . '/../includes/db/reviews_db.php';

requireRole('provider');

$provider_id = getUserId();
$provider_name = getUserName();

// Handle Notification Actions (AJAX & Standard POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']);

    if ($action === 'mark_notif_read') {
        $notif_id = (int)($_POST['notification_id'] ?? 0);
        if ($notif_id > 0) {
            markAsRead($notif_id, $provider_id);
        }
    } elseif ($action === 'mark_all_read') {
        markAllAsRead($provider_id);
    }

    $unread_count = getUnreadCount($provider_id);

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'unread_count' => $unread_count
        ]);
        exit;
    }

    header('Location: dashboard.php#notifications');
    exit;
}

$unread_count = getUnreadCount($provider_id);
$notifications = getNotifications($provider_id, 15);

// Provider Statistics
$total_services = getServicesCount($provider_id);
$total_bookings = getBookingsCount($provider_id);
$pending_bookings = getBookingsCount($provider_id, 'pending');
$total_earnings = getProviderEarnings($provider_id);
$avg_rating = getAverageRating($provider_id);
$total_reviews = getReviewsCount($provider_id);

// Recent pending bookings & recent reviews
$pending_requests = getProviderBookings($provider_id, 'pending', 5);
$recent_reviews = getProviderReviews($provider_id);

renderHead(['title' => __('Provider Dashboard - Dabberha')]);
?>

<div class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/header.php'; ?>

    <div class="flex-1 flex flex-col md:flex-row">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="flex-1 p-6 md:p-8 lg:p-10 max-w-7xl mx-auto w-full space-y-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-brand-900 flex items-center gap-3">
                    <i class="fa-solid fa-chart-line text-brand-primary"></i>
                    <span><?php echo __('Dashboard Overview'); ?></span>
                </h1>
                <p class="mt-1 text-sm text-brand-textMuted">
                    <?php echo __('Welcome back, '); ?><strong><?php echo htmlspecialchars($provider_name); ?></strong>!
                </p>
            </div>

            <!-- KPI Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- My Services -->
                <div class="bg-white rounded-2xl shadow-soft border border-brand-border p-6 flex flex-col justify-between hover:border-brand-primary/40 transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold text-brand-textMuted"><?php echo __('My Services'); ?></span>
                        <div class="w-10 h-10 rounded-xl bg-brand-primaryLight text-brand-primary flex items-center justify-center">
                            <i class="fa-solid fa-briefcase"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-black text-brand-900 mb-1"><?php echo $total_services; ?></div>
                    <p class="text-xs text-brand-textMuted"><?php echo __('Services you offer'); ?></p>
                </div>

                <!-- Total Bookings -->
                <div class="bg-white rounded-2xl shadow-soft border border-brand-border p-6 flex flex-col justify-between hover:border-brand-primary/40 transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold text-brand-textMuted"><?php echo __('Bookings'); ?></span>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fa-regular fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-black text-brand-900 mb-1"><?php echo $total_bookings; ?></div>
                    <p class="text-xs text-amber-600 font-bold"><?php echo $pending_bookings; ?> <?php echo __('pending confirmation'); ?></p>
                </div>

                <!-- Earnings -->
                <div class="bg-white rounded-2xl shadow-soft border border-brand-border p-6 flex flex-col justify-between hover:border-brand-primary/40 transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold text-brand-textMuted"><?php echo __('Earnings'); ?></span>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-black text-brand-primary mb-1" dir="rtl"><?php echo number_format($total_earnings, 2); ?> <span class="text-lg">د.ل</span></div>
                    <p class="text-xs text-brand-textMuted"><?php echo __('From completed jobs'); ?></p>
                </div>

                <!-- Rating -->
                <div class="bg-white rounded-2xl shadow-soft border border-brand-border p-6 flex flex-col justify-between hover:border-brand-primary/40 transition-all">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold text-brand-textMuted"><?php echo __('Rating'); ?></span>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                            <i class="fa-solid fa-star"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-black text-brand-900 mb-1 flex items-center gap-2">
                        <span><?php echo number_format($avg_rating, 1); ?></span>
                        <?php echo renderStarRating($avg_rating, 5, 'text-sm'); ?>
                    </div>
                    <p class="text-xs text-brand-textMuted"><?php echo $total_reviews; ?> <?php echo __('reviews'); ?></p>
                </div>
            </div>

            <!-- Two-Column Layout for Bookings & Notifications -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left 2 Cols: Pending Requests & Reviews -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Pending Booking Requests -->
                    <div class="bg-white rounded-2xl shadow-soft border border-brand-border overflow-hidden">
                        <div class="p-5 border-b border-brand-border flex items-center justify-between bg-brand-surface/60">
                            <h2 class="font-bold text-base text-brand-900 flex items-center gap-2">
                                <i class="fa-regular fa-clock text-amber-500"></i>
                                <span><?php echo __('Recent Pending Bookings'); ?></span>
                            </h2>
                            <a href="bookings.php?status=pending" class="text-xs text-brand-primary font-bold hover:underline">
                                <?php echo __('View All'); ?>
                            </a>
                        </div>

                        <div class="divide-y divide-brand-border">
                            <?php if (count($pending_requests) > 0): ?>
                                <?php foreach ($pending_requests as $request): ?>
                                    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-brand-surface/30 transition-colors">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2">
                                                <h3 class="font-bold text-sm text-brand-900"><?php echo htmlspecialchars($request['service_title']); ?></h3>
                                                <?php echo renderStatusBadge($request['status']); ?>
                                            </div>
                                            <p class="text-xs text-brand-textMuted">
                                                <i class="fa-regular fa-user text-[10px] mr-1"></i><?php echo htmlspecialchars($request['customer_name']); ?> | 
                                                <i class="fa-regular fa-calendar text-[10px] mr-1"></i><?php echo htmlspecialchars($request['booking_date']); ?> <?php echo htmlspecialchars($request['booking_time'] ?? ''); ?>
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <a href="booking-detail.php?id=<?php echo (int)$request['id']; ?>" class="bg-brand-primary hover:bg-brand-primaryHover text-white px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all shadow-2xs">
                                                <?php echo __('View Details'); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-8 text-center">
                                    <?php echo renderEmptyState('fa-regular fa-calendar-check', 'No pending requests', 'New booking requests will appear here'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Customer Reviews -->
                    <div class="bg-white rounded-2xl shadow-soft border border-brand-border overflow-hidden">
                        <div class="p-5 border-b border-brand-border flex items-center justify-between bg-brand-surface/60">
                            <h2 class="font-bold text-base text-brand-900 flex items-center gap-2">
                                <i class="fa-regular fa-star text-amber-500"></i>
                                <span><?php echo __('Recent Reviews'); ?></span>
                            </h2>
                            <a href="reviews.php" class="text-xs text-brand-primary font-bold hover:underline">
                                <?php echo __('View All'); ?>
                            </a>
                        </div>

                        <div class="divide-y divide-brand-border">
                            <?php if (count($recent_reviews) > 0): ?>
                                <?php foreach (array_slice($recent_reviews, 0, 3) as $rev): ?>
                                    <div class="p-5 space-y-2 hover:bg-brand-surface/30 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-xs text-brand-900"><?php echo htmlspecialchars($rev['customer_name']); ?></span>
                                            <?php echo renderStarRating($rev['rating'], 5, 'text-xs'); ?>
                                        </div>
                                        <p class="text-xs text-brand-text leading-relaxed"><?php echo htmlspecialchars($rev['comment']); ?></p>
                                        <span class="text-[10px] text-brand-textLight block"><?php echo htmlspecialchars($rev['service_title']); ?> • <?php echo date('Y-m-d', strtotime($rev['created_at'])); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-8 text-center">
                                    <?php echo renderEmptyState('fa-regular fa-star', 'No reviews yet'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Col: Notifications Center -->
                <div class="space-y-6" id="notifications">
                    <div class="bg-white rounded-2xl shadow-soft border border-brand-border overflow-hidden flex flex-col">
                        <div class="p-4 sm:p-5 border-b border-brand-border flex items-center justify-between bg-brand-surface/60">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-sm text-brand-900 flex items-center gap-2">
                                    <i class="fa-regular fa-bell text-brand-primary"></i>
                                    <span><?php echo __('Notifications'); ?></span>
                                </h3>
                                <span id="notif-badge-count" class="bg-brand-dangerLight text-brand-danger text-[10px] font-black px-2 py-0.5 rounded-full transition-all <?php echo $unread_count > 0 ? '' : 'hidden'; ?>">
                                    <span id="notif-unread-num"><?php echo $unread_count; ?></span> <?php echo __('new'); ?>
                                </span>
                            </div>
                            
                            <button type="button" id="mark-all-read-btn" class="text-xs font-bold text-brand-primary hover:text-brand-primaryHover flex items-center gap-1.5 px-2.5 py-1 rounded-lg hover:bg-brand-primaryLight/50 transition-all <?php echo $unread_count > 0 ? '' : 'hidden'; ?>">
                                <i class="fa-solid fa-check-double text-[11px]"></i>
                                <span><?php echo __('Mark all as read'); ?></span>
                            </button>
                        </div>

                        <div class="p-4 space-y-3 max-h-[450px] overflow-y-auto" id="notif-list-container">
                            <?php if (count($notifications) > 0): ?>
                                <?php foreach ($notifications as $notif): ?>
                                    <div class="notif-item p-3.5 rounded-xl border text-xs leading-relaxed transition-all duration-200 <?php echo $notif['is_read'] ? 'is-read bg-brand-surface/30 border-brand-border text-brand-textMuted opacity-70 cursor-default' : 'is-unread bg-brand-primaryLight/40 border-brand-primary/40 text-brand-900 font-semibold shadow-xs hover:bg-brand-primaryLight/70 cursor-pointer'; ?>"
                                         data-notif-id="<?php echo $notif['id']; ?>"
                                         data-is-read="<?php echo $notif['is_read'] ? '1' : '0'; ?>"
                                         title="<?php echo $notif['is_read'] ? '' : __('Click to mark as read'); ?>">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-start gap-2 flex-1">
                                                <span class="notif-dot mt-1 w-2 h-2 rounded-full bg-brand-primary flex-shrink-0 <?php echo $notif['is_read'] ? 'hidden' : ''; ?>"></span>
                                                <p class="notif-text mb-1 flex-1 leading-snug"><?php echo htmlspecialchars(formatNotificationMessage($notif['message']), ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
                                            <span class="notif-status-icon text-green-600 text-xs flex-shrink-0 <?php echo $notif['is_read'] ? '' : 'hidden'; ?>">
                                                <i class="fa-solid fa-check"></i>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between text-[10px] text-brand-textLight mt-1 pr-4">
                                            <span><?php echo date('Y-m-d H:i', strtotime($notif['created_at'])); ?></span>
                                            <span class="notif-hint text-brand-primary text-[10px] font-medium <?php echo $notif['is_read'] ? 'hidden' : ''; ?>"><?php echo __('Click to mark as read'); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="py-8 text-center">
                                    <?php echo renderEmptyState('fa-regular fa-bell', 'No notifications'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const markAllBtn = document.getElementById('mark-all-read-btn');
    const notifBadgeCount = document.getElementById('notif-badge-count');
    const notifUnreadNum = document.getElementById('notif-unread-num');
    const headerNotifBadge = document.getElementById('header-notif-badge');
    const notifItems = document.querySelectorAll('.notif-item');

    function updateBadgeCounts(count) {
        if (count > 0) {
            if (notifUnreadNum) notifUnreadNum.textContent = count;
            if (notifBadgeCount) notifBadgeCount.classList.remove('hidden');
            if (markAllBtn) markAllBtn.classList.remove('hidden');
            if (headerNotifBadge) {
                headerNotifBadge.textContent = count > 9 ? '9+' : count;
                headerNotifBadge.classList.remove('hidden');
            }
        } else {
            if (notifBadgeCount) notifBadgeCount.classList.add('hidden');
            if (markAllBtn) markAllBtn.classList.add('hidden');
            if (headerNotifBadge) headerNotifBadge.classList.add('hidden');
        }
    }

    function setItemAsRead(item) {
        if (!item || item.dataset.isRead === '1') return;
        item.dataset.isRead = '1';
        item.className = 'notif-item p-3.5 rounded-xl border text-xs leading-relaxed transition-all duration-200 is-read bg-brand-surface/30 border-brand-border text-brand-textMuted opacity-70 cursor-default';
        item.title = '';
        
        const dot = item.querySelector('.notif-dot');
        if (dot) dot.classList.add('hidden');

        const hint = item.querySelector('.notif-hint');
        if (hint) hint.classList.add('hidden');

        const statusIcon = item.querySelector('.notif-status-icon');
        if (statusIcon) statusIcon.classList.remove('hidden');
    }

    // Individual click to mark as read
    notifItems.forEach(function(item) {
        item.addEventListener('click', function() {
            if (this.dataset.isRead === '1') return;
            const notifId = this.dataset.notifId;
            if (!notifId) return;

            setItemAsRead(this);

            // Calculate optimistic count
            const currentUnread = document.querySelectorAll('.notif-item[data-is-read="0"]').length;
            updateBadgeCounts(currentUnread);

            const formData = new FormData();
            formData.append('action', 'mark_notif_read');
            formData.append('notification_id', notifId);
            formData.append('ajax', '1');

            fetch('dashboard.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data && typeof data.unread_count !== 'undefined') {
                    updateBadgeCounts(data.unread_count);
                }
            })
            .catch(err => console.error('Error marking notification as read:', err));
        });
    });

    // Mark all as read button
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            notifItems.forEach(setItemAsRead);
            updateBadgeCounts(0);

            const formData = new FormData();
            formData.append('action', 'mark_all_read');
            formData.append('ajax', '1');

            fetch('dashboard.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data && typeof data.unread_count !== 'undefined') {
                    updateBadgeCounts(data.unread_count);
                }
            })
            .catch(err => console.error('Error marking all notifications as read:', err));
        });
    }
});
</script>
</body>
</html>