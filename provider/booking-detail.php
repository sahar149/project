<?php
/**
 * Dabberha (دبرها) - Booking Details (Provider Panel)
 * Location: provider/booking-detail.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notifications.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';

require_once __DIR__ . '/../includes/db/bookings_db.php';

requireRole('provider');

$provider_id = getUserId();
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($booking_id <= 0) {
    header('Location: bookings.php');
    exit;
}

$booking = getBookingById($booking_id, $provider_id, 'provider');

if (!$booking) {
    header('Location: bookings.php');
    exit;
}

$message = '';
$message_type = '';

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    $allowed = ['pending', 'confirmed', 'completed', 'cancelled'];

    if (in_array($new_status, $allowed, true)) {
        if (updateBookingStatus($booking_id, $new_status, $provider_id)) {
            $message = 'Booking status updated successfully!';
            $message_type = 'success';
            $booking['status'] = $new_status;

            // Send notification to customer
            addNotification((int)$booking['customer_id'], sprintf(__('Your booking for %s has been marked as %s.'), $booking['service_title'], __($new_status)));
        }
    }
}

renderHead(['title' => __('Booking Details - Provider Dashboard')]);
?>

<div class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/header.php'; ?>

    <div class="flex-1 flex flex-col md:flex-row">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="flex-1 p-6 md:p-8 lg:p-10 max-w-4xl mx-auto w-full">
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-brand-900 flex items-center gap-3">
                        <i class="fa-regular fa-calendar-check text-brand-primary"></i>
                        <span><?php echo __('Booking Details'); ?> #<?php echo (int)$booking['id']; ?></span>
                    </h1>
                    <p class="text-brand-textMuted mt-1"><?php echo __('Review customer request and update service status'); ?></p>
                </div>
            </div>

            <?php if (!empty($message)): ?>
                <?php echo renderAlert($message, $message_type); ?>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Main Info Card -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Service Info -->
                    <div class="bg-white rounded-2xl p-6 shadow-soft border border-brand-border space-y-4">
                        <div class="flex justify-between items-start border-b border-brand-border pb-4">
                            <div>
                                <span class="text-xs text-brand-textMuted font-bold block mb-1"><?php echo __('Service Information'); ?></span>
                                <h2 class="text-xl font-bold text-brand-900"><?php echo htmlspecialchars($booking['service_title']); ?></h2>
                            </div>
                            <?php echo renderStatusBadge($booking['status']); ?>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-xs text-brand-textMuted block mb-1"><?php echo __('Booking Date'); ?></span>
                                <span class="font-bold text-brand-text"><?php echo htmlspecialchars($booking['booking_date']); ?></span>
                            </div>
                            <div>
                                <span class="text-xs text-brand-textMuted block mb-1"><?php echo __('Booking Time'); ?></span>
                                <span class="font-bold text-brand-primary"><?php echo htmlspecialchars($booking['booking_time'] ?? '-'); ?></span>
                            </div>
                            <div>
                                <span class="text-xs text-brand-textMuted block mb-1"><?php echo __('Total Price'); ?></span>
                                <span class="text-2xl font-black text-brand-primary" dir="rtl"><?php echo number_format($booking['total_price'], 2); ?> د.ل</span>
                            </div>
                            <div>
                                <span class="text-xs text-brand-textMuted block mb-1"><?php echo __('Created At'); ?></span>
                                <span class="font-medium text-brand-text text-xs"><?php echo date('Y-m-d H:i', strtotime($booking['created_at'])); ?></span>
                            </div>
                        </div>

                        <?php if (!empty($booking['notes'])): ?>
                            <div class="border-t border-brand-border pt-4">
                                <span class="text-xs text-brand-textMuted font-bold block mb-1"><?php echo __('Customer Notes'); ?></span>
                                <p class="text-sm bg-brand-surface p-3.5 rounded-xl border border-brand-border text-brand-text leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars($booking['notes'])); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Customer Contact Info -->
                    <div class="bg-white rounded-2xl p-6 shadow-soft border border-brand-border space-y-4">
                        <h3 class="text-base font-bold text-brand-900 flex items-center gap-2 border-b border-brand-border pb-3">
                            <i class="fa-regular fa-user text-brand-primary"></i>
                            <span><?php echo __('Customer Details'); ?></span>
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-xs text-brand-textMuted block mb-1"><?php echo __('Customer Name'); ?></span>
                                <span class="font-bold text-brand-text"><?php echo htmlspecialchars($booking['customer_name']); ?></span>
                            </div>
                            <div>
                                <span class="text-xs text-brand-textMuted block mb-1"><?php echo __('Phone Number'); ?></span>
                                <a href="tel:<?php echo htmlspecialchars($booking['customer_phone']); ?>" class="font-bold text-brand-primary hover:underline">
                                    <?php echo htmlspecialchars($booking['customer_phone'] ?? '-'); ?>
                                </a>
                            </div>
                            <div>
                                <span class="text-xs text-brand-textMuted block mb-1"><?php echo __('Email Address'); ?></span>
                                <span class="font-medium text-brand-text"><?php echo htmlspecialchars($booking['customer_email']); ?></span>
                            </div>
                            <div>
                                <span class="text-xs text-brand-textMuted block mb-1"><?php echo __('Service Location'); ?></span>
                                <span class="font-medium text-brand-text"><?php echo htmlspecialchars($booking['customer_address'] ?? __('Not provided')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Update Action Card -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl p-6 shadow-soft border border-brand-border space-y-4">
                        <h3 class="text-base font-bold text-brand-900 border-b border-brand-border pb-3">
                            <?php echo __('Update Status'); ?>
                        </h3>

                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-brand-textMuted mb-2" for="status-select">
                                    <?php echo __('Change Booking Status'); ?>
                                </label>
                                <select name="status" id="status-select" class="w-full pr-4 pl-10 py-2.5 rounded-xl border border-brand-border bg-brand-surface text-sm font-bold text-right focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary">
                                    <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>><?php echo __('Pending'); ?></option>
                                    <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>><?php echo __('Confirmed'); ?></option>
                                    <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>><?php echo __('Completed'); ?></option>
                                    <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>><?php echo __('Cancelled'); ?></option>
                                </select>
                            </div>

                            <button type="submit" class="w-full bg-brand-primary hover:bg-brand-primaryHover text-white font-bold py-3 rounded-xl transition-all shadow-xs flex items-center justify-center gap-2">
                                <i class="fa-solid fa-arrows-rotate"></i>
                                <span><?php echo __('Update Status'); ?></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>