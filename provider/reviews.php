<?php
/**
 * Dabberha (دبرها) - Reviews (Provider Panel)
 * Location: provider/reviews.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';

require_once __DIR__ . '/../includes/db/reviews_db.php';

requireRole('provider');

$provider_id = getUserId();
$reviews = getProviderReviews($provider_id);
$total_reviews = getReviewsCount($provider_id);
$avg_rating = getAverageRating($provider_id);

renderHead(['title' => __('My Reviews - Provider')]);
?>

<div class="min-h-screen flex flex-col">
    <?php include __DIR__ . '/header.php'; ?>

    <div class="flex-1 flex flex-col md:flex-row">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="flex-1 p-6 md:p-8 lg:p-10 max-w-7xl mx-auto w-full">
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-brand-900 flex items-center gap-3">
                    <i class="fa-regular fa-star text-brand-primary"></i>
                    <span><?php echo __('Customer Reviews'); ?></span>
                </h1>
                <p class="text-brand-textMuted mt-1"><?php echo __('Feedback and ratings received from your clients'); ?></p>
            </div>

            <!-- Stats Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
                <div class="bg-white rounded-2xl p-6 shadow-soft border border-brand-border flex items-center justify-between">
                    <div>
                        <span class="text-sm font-semibold text-brand-textMuted"><?php echo __('Average Rating'); ?></span>
                        <div class="text-3xl font-black text-brand-900 mt-1 flex items-center gap-2">
                            <span><?php echo number_format($avg_rating, 1); ?></span>
                            <?php echo renderStarRating($avg_rating, 5, 'text-base'); ?>
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-star"></i>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-soft border border-brand-border flex items-center justify-between">
                    <div>
                        <span class="text-sm font-semibold text-brand-textMuted"><?php echo __('Total Reviews'); ?></span>
                        <div class="text-3xl font-black text-brand-900 mt-1"><?php echo $total_reviews; ?></div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                </div>
            </div>

            <!-- Reviews List -->
            <div class="bg-white rounded-2xl shadow-soft border border-brand-border overflow-hidden">
                <div class="divide-y divide-brand-border">
                    <?php if (count($reviews) > 0): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="p-6 space-y-3 hover:bg-brand-surface/30 transition-colors">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-brand-primaryLight text-brand-primary font-bold flex items-center justify-center text-sm">
                                            <i class="fa-regular fa-user"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-sm text-brand-900"><?php echo htmlspecialchars($review['customer_name']); ?></h3>
                                            <span class="text-xs text-brand-textMuted"><?php echo htmlspecialchars($review['service_title']); ?></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <?php echo renderStarRating($review['rating']); ?>
                                        <span class="text-xs text-brand-textLight"><?php echo date('Y-m-d', strtotime($review['created_at'])); ?></span>
                                    </div>
                                </div>
                                <p class="text-sm text-brand-text leading-relaxed bg-brand-surface/50 p-4 rounded-xl border border-brand-border/60">
                                    <?php echo htmlspecialchars($review['comment']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-12 text-center">
                            <?php echo renderEmptyState('fa-regular fa-star', 'No reviews yet'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
