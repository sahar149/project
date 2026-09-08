<?php
/**
 * Dabberha (دبرها) - Manage Reviews (Admin Panel)
 * Location: admin/reviews.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';
require_once __DIR__ . '/../includes/components/sidebar.php';
require_once __DIR__ . '/../includes/components/header_dashboard.php';

require_once __DIR__ . '/../includes/db/reviews_db.php';

requireRole('admin');

$message = '';
$message_type = '';

// Delete review
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $review_id = (int)$_GET['id'];
    if (deleteReview($review_id)) {
        $message = __('Review deleted successfully!');
        $message_type = 'success';
    }
}

$reviews = getAllReviews();
$total_reviews = getReviewsCount();
$avg_rating = getAverageRating();

renderHead(['title' => __('Manage Reviews') . ' - ' . __('Admin Panel')]);
?>

<div class="min-h-screen flex flex-col">
    <?php renderDashboardHeader([
        'title' => __('Manage Reviews'),
        'subtitle' => __('Monitor and moderate customer reviews'),
        'role' => 'admin'
    ]); ?>

    <div class="flex-1 flex flex-col md:flex-row">
        <?php renderSidebar('admin', 'reviews.php'); ?>

        <main class="flex-1 p-6 md:p-8 lg:p-10 max-w-7xl mx-auto w-full">
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-brand-900 flex items-center gap-3">
                        <i class="fa-regular fa-star text-brand-primary"></i>
                        <span><?php echo __('Manage Reviews'); ?></span>
                    </h1>
                    <p class="text-brand-textMuted mt-1"><?php echo __('View and manage all customer feedback and ratings'); ?></p>
                </div>
            </div>

            <?php if (!empty($message)): ?>
                <?php echo renderAlert($message, $message_type); ?>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
                <div class="bg-white rounded-2xl p-6 shadow-soft border border-brand-border flex items-center justify-between">
                    <div>
                        <span class="text-sm font-semibold text-brand-textMuted"><?php echo __('Total Reviews'); ?></span>
                        <div class="text-3xl font-black text-brand-900 mt-1"><?php echo $total_reviews; ?></div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-soft border border-brand-border flex items-center justify-between">
                    <div>
                        <span class="text-sm font-semibold text-brand-textMuted"><?php echo __('Average Rating'); ?></span>
                        <div class="text-3xl font-black text-brand-900 mt-1 flex items-center gap-2">
                            <span><?php echo number_format($avg_rating, 1); ?></span>
                            <?php echo renderStarRating($avg_rating, 5, 'text-lg'); ?>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-star"></i>
                    </div>
                </div>
            </div>

            <!-- Reviews Table -->
            <div class="bg-white rounded-2xl shadow-soft border border-brand-border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-brand-border bg-brand-surface/60 text-brand-text font-bold text-xs">
                                <th class="py-4 px-6"><?php echo __('ID'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Customer'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Provider'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Service'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Rating'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Comment'); ?></th>
                                <th class="py-4 px-6"><?php echo __('Date'); ?></th>
                                <th class="py-4 px-6 text-center"><?php echo __('Actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border text-sm">
                            <?php if (count($reviews) > 0): ?>
                                <?php foreach ($reviews as $review): ?>
                                    <tr class="hover:bg-brand-surface/40 transition-colors">
                                        <td class="py-4 px-6 text-xs text-brand-textMuted font-mono">#<?php echo (int) $review['id']; ?></td>
                                        <td class="py-4 px-6 font-bold text-brand-900"><?php echo htmlspecialchars($review['customer_name']); ?></td>
                                        <td class="py-4 px-6 text-brand-textMuted font-medium"><?php echo htmlspecialchars($review['provider_name']); ?></td>
                                        <td class="py-4 px-6 text-brand-text"><?php echo htmlspecialchars($review['service_title']); ?></td>
                                        <td class="py-4 px-6">
                                            <?php echo renderStarRating($review['rating']); ?>
                                        </td>
                                        <td class="py-4 px-6 text-brand-textMuted max-w-xs truncate" title="<?php echo htmlspecialchars($review['comment']); ?>">
                                            <?php echo htmlspecialchars($review['comment']); ?>
                                        </td>
                                        <td class="py-4 px-6 text-xs text-brand-textMuted"><?php echo date('Y-m-d', strtotime($review['created_at'])); ?></td>
                                        <td class="py-4 px-6 text-center">
                                            <a href="reviews.php?delete=1&id=<?php echo (int) $review['id']; ?>" 
                                               class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-colors shadow-2xs" 
                                               title="<?php echo __('Delete Review'); ?>" 
                                               onclick="return confirm('<?php echo htmlspecialchars(__('Delete this review?'), ENT_QUOTES); ?>')">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="py-12 px-6 text-center">
                                        <?php echo renderEmptyState('fa-regular fa-star', 'No reviews found.'); ?>
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