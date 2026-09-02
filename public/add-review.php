<?php
/**
 * Dabberha (دبرها) - Add Review (Customer Panel)
 * Location: public/add-review.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/helpers/ui_helpers.php';
require_once __DIR__ . '/../includes/components/head.php';
require_once __DIR__ . '/../includes/components/navbar_public.php';
require_once __DIR__ . '/../includes/components/footer_public.php';
require_once __DIR__ . '/../includes/db/bookings_db.php';
require_once __DIR__ . '/../includes/db/reviews_db.php';

requireRole('customer');

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$customer_id = getUserId();

if ($booking_id <= 0) {
    header('Location: my-bookings.php');
    exit;
}

$booking = getBookingById($booking_id, $customer_id, 'customer');

if (!$booking || $booking['status'] !== 'completed') {
    header('Location: my-bookings.php');
    exit;
}

// Check if review already submitted
if (getReviewByBookingId($booking_id)) {
    header('Location: booking-confirmation.php?id=' . $booking_id . '&already_reviewed=1');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating between 1 and 5 stars.';
    } elseif (empty($comment)) {
        $error = 'Please write a comment.';
    } else {
        $review_id = createReview([
            'booking_id' => $booking_id,
            'customer_id' => $customer_id,
            'provider_id' => (int)$booking['provider_id'],
            'service_id' => (int)$booking['service_id'],
            'rating' => $rating,
            'comment' => $comment
        ]);

        if ($review_id > 0) {
            $success = 'Thank you for your review!';
        } else {
            $error = 'Failed to save review. Please try again.';
        }
    }
}

renderHead(['title' => __('Add Review') . ' - ' . __('Dabberha')]);
renderPublicNavbar(['active_page' => 'bookings']);
?>

<main class="flex-grow flex items-center justify-center py-16 px-4 md:px-margin-desktop">
    <div class="bg-surface-container-lowest w-full max-w-xl rounded-2xl shadow-ambient border border-surface-variant p-6 sm:p-10 space-y-6">
        <div class="text-center space-y-2">
            <div class="w-16 h-16 bg-primary-fixed rounded-full flex items-center justify-center text-primary text-3xl mx-auto">
                <i class="fa-solid fa-star"></i>
            </div>
            <h1 class="text-2xl font-bold text-on-background"><?php echo __('Rate & Review Service'); ?></h1>
            <p class="text-xs text-on-surface-variant"><?php echo __('Share your experience to help others in the community'); ?></p>
        </div>

        <!-- Service Info Box -->
        <div class="bg-surface-container-low p-4 rounded-xl border border-surface-variant space-y-1 text-xs">
            <p class="font-bold text-on-background text-sm"><?php echo htmlspecialchars($booking['service_title']); ?></p>
            <p class="text-on-surface-variant"><?php echo __('Service Provider'); ?>: <strong class="text-on-background"><?php echo htmlspecialchars($booking['provider_name']); ?></strong></p>
            <p class="text-on-surface-variant"><?php echo __('Completed on'); ?>: <?php echo htmlspecialchars($booking['booking_date']); ?></p>
        </div>

        <?php if (!empty($error)): ?>
            <?php echo renderAlert($error, 'danger'); ?>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <?php echo renderAlert($success, 'success'); ?>
            <div class="text-center pt-2">
                <a href="my-bookings.php" class="inline-flex items-center gap-2 bg-primary hover:bg-[#7a2f18] text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-ambient transition-all">
                    <span><?php echo __('Back to My Bookings'); ?></span>
                </a>
            </div>
        <?php else: ?>
            <form method="POST" class="space-y-6">
                <!-- Interactive Star Rating Input -->
                <div class="text-center space-y-2">
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider"><?php echo __('Your Rating'); ?></label>
                    <div class="flex flex-row-reverse justify-center gap-2 text-3xl" id="starContainer">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" class="hidden peer">
                            <label for="star<?php echo $i; ?>" class="cursor-pointer text-outline-variant hover:text-amber-400 peer-checked:text-amber-500 peer-checked:~label:text-amber-500 hover:~label:text-amber-400 transition-colors">
                                ★
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-on-background mb-2" for="comment">
                        <?php echo __('Your Feedback / Comment'); ?> <span class="text-error">*</span>
                    </label>
                    <textarea name="comment" id="comment" rows="4" class="w-full px-4 py-3 rounded-xl border border-outline-variant bg-surface-container-lowest text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:border-primary leading-relaxed" placeholder="<?php echo __('Write your review here...'); ?>" required></textarea>
                </div>

                <button type="submit" class="w-full bg-primary hover:bg-[#7a2f18] text-white font-bold py-3.5 rounded-xl text-sm transition-all shadow-ambient flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span><?php echo __('Submit Review'); ?></span>
                </button>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php renderPublicFooter(['active_page' => 'bookings']); ?>