<?php
/**
 * Dabberha (دبرها) - Manage Bookings (Admin Panel)
 * Location: admin/bookings.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';
require_once __DIR__ . '/../includes/components/sidebar.php';
require_once __DIR__ . '/../includes/components/header_dashboard.php';

require_once __DIR__ . '/../includes/db/bookings_db.php';

requireRole('admin');

$message = '';
$message_type = '';

// Update booking status
if (isset($_POST['update_status']) && isset($_POST['booking_id']) && isset($_POST['status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $status = $_POST['status'];
    $allowed = ['pending', 'confirmed', 'completed', 'cancelled'];
    
    if (in_array($status, $allowed, true)) {
        if (updateBookingStatus($booking_id, $status)) {
            $message = __('Booking status updated!');
            $message_type = 'success';
        }
    }
}

// Delete booking
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $booking_id = (int)$_GET['id'];
    if (deleteBooking($booking_id)) {
        $message = __('Booking deleted!');
        $message_type = 'success';
    }
}

$bookings = getAllBookings();

renderHead(['title' => __('Manage Bookings') . ' - ' . __('Admin Panel')]);
?>

<div class="min-h-screen flex flex-col">
    <?php renderDashboardHeader([
        'title' => __('Manage Bookings'),
        'subtitle' => __('View and manage all customer bookings'),
        'role' => 'admin'
    ]); ?>

    <div class="flex-1 flex flex-col md:flex-row">
        <?php renderSidebar('admin', 'bookings.php'); ?>

        <main class="flex-1 p-6 md:p-8 lg:p-10 max-w-7xl mx-auto w-full">
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-brand-900 flex items-center gap-3">
                        <i class="fa-regular fa-calendar-check text-brand-primary"></i>
                        <span><?php echo __('Manage Bookings'); ?></span>
                    </h1>
                    <p class="text-brand-textMuted mt-1"><?php echo __('Monitor and update booking requests'); ?></p>
                </div>
            </div>

            <?php if (!empty($message)): ?>
                <?php echo renderAlert($message, $message_type); ?>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-soft border border-brand-border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-brand-border bg-brand-surface/60 text-brand-text font-bold text-xs">
                                <th class="py-4 px-6"><?php echo __('ID'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Customer'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Service'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Provider'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Date'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Price'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Status'); ?></th>
                                <th class="py-4 px-6 text-center"><?php echo __('Actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border text-sm">
                            <?php if (count($bookings) > 0): ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr class="hover:bg-brand-surface/40 transition-colors">
                                        <td class="py-4 px-6 text-xs text-brand-textMuted font-mono">#<?php echo (int) $booking['id']; ?></td>
                                        <td class="py-4 px-6 font-bold text-brand-900"><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                        <td class="py-4 px-6 text-brand-text"><?php echo htmlspecialchars($booking['service_title']); ?></td>
                                        <td class="py-4 px-6 text-brand-textMuted font-medium"><?php echo htmlspecialchars($booking['provider_name']); ?></td>
                                        <td class="py-4 px-6 text-xs text-brand-textMuted">
                                            <?php echo htmlspecialchars($booking['booking_date']); ?> 
                                            <span class="text-brand-primary"><?php echo htmlspecialchars($booking['booking_time'] ?? ''); ?></span>
                                        </td>
                                        <td class="py-4 px-6 font-black text-brand-primary" dir="rtl"><?php echo number_format($booking['total_price'], 2); ?> د.ل</td>
                                        <td class="py-4 px-6">
                                            <form method="POST" class="inline-flex items-center gap-2">
                                                <input type="hidden" name="booking_id" value="<?php echo (int)$booking['id']; ?>">
                                                <select name="status" onchange="this.form.submit()" class="text-xs font-semibold rounded-lg border-brand-border py-1 px-2.5 bg-brand-surface focus:ring-brand-primary focus:border-brand-primary">
                                                    <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>><?php echo __('Pending'); ?></option>
                                                    <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>><?php echo __('Confirmed'); ?></option>
                                                    <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>><?php echo __('Completed'); ?></option>
                                                    <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>><?php echo __('Cancelled'); ?></option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <a href="bookings.php?delete=1&id=<?php echo (int) $booking['id']; ?>" 
                                               class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-colors shadow-2xs" 
                                               title="<?php echo __('Delete'); ?>" 
                                               onclick="return confirm('<?php echo htmlspecialchars(__('Delete this booking?'), ENT_QUOTES); ?>')">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="py-12 px-6 text-center">
                                        <?php echo renderEmptyState('fa-regular fa-calendar-xmark', 'No bookings yet.'); ?>
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