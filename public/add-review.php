<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('customer');

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$customer_id = getUserId();

if ($booking_id == 0) {
    header('Location: browse-services.php');
    exit;
}

// جلب تفاصيل الحجز
$stmt = $pdo->prepare("
    SELECT b.*, s.title as service_title, u.name as provider_name, u.id as provider_id, s.id as service_id
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.provider_id = u.id
    WHERE b.id = ? AND b.customer_id = ? AND b.status = 'completed'
");
$stmt->execute([$booking_id, $customer_id]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: browse-services.php');
    exit;
}

// التحقق إذا كان هناك تقييم مسبق
$stmt = $pdo->prepare("SELECT id FROM reviews WHERE booking_id = ?");
$stmt->execute([$booking_id]);
if ($stmt->fetch()) {
    header('Location: booking-confirmation.php?id=' . $booking_id . '&already_reviewed=1');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating between 1 and 5 stars.';
    } elseif (empty($comment)) {
        $error = 'Please write a comment.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO reviews (booking_id, customer_id, provider_id, service_id, rating, comment)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$booking_id, $customer_id, $booking['provider_id'], $booking['service_id'], $rating, $comment])) {
            $success = 'Thank you for your review!';
        } else {
            $error = 'Failed to save review. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Review - Local Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 10px;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            font-size: 40px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/local-services-platform/index.php">
                <i class="bi bi-tools"></i> Local Services
            </a>
            <div>
                <span class="text-white me-3">Welcome, <?php echo htmlspecialchars(getUserName()); ?></span>
                <a href="/local-services-platform/public/logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-warning">
                        <h4><i class="bi bi-star"></i> Rate Your Experience</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                                <br>
                                <a href="booking-confirmation.php?id=<?php echo $booking_id; ?>" class="alert-link">
                                    Back to booking confirmation
                                </a>
                            </div>
                        <?php else: ?>
                            <p><strong>Service:</strong> <?php echo htmlspecialchars($booking['service_title']); ?></p>
                            <p><strong>Provider:</strong> <?php echo htmlspecialchars($booking['provider_name']); ?></p>
                            <hr>

                            <form method="POST">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">How was your experience? ⭐</label>
                                    <div class="star-rating">
                                        <input type="radio" name="rating" id="star5" value="5" required>
                                        <label for="star5" title="5 stars">★</label>
                                        <input type="radio" name="rating" id="star4" value="4">
                                        <label for="star4" title="4 stars">★</label>
                                        <input type="radio" name="rating" id="star3" value="3">
                                        <label for="star3" title="3 stars">★</label>
                                        <input type="radio" name="rating" id="star2" value="2">
                                        <label for="star2" title="2 stars">★</label>
                                        <input type="radio" name="rating" id="star1" value="1">
                                        <label for="star1" title="1 star">★</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Your Review</label>
                                    <textarea name="comment" class="form-control" rows="4" 
                                              placeholder="Describe your experience..." required></textarea>
                                </div>

                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="bi bi-send"></i> Submit Review
                                </button>
                            </form>
                            <hr>
                            <a href="booking-confirmation.php?id=<?php echo $booking_id; ?>" class="btn btn-secondary w-100">
                                <i class="bi bi-arrow-left"></i> Back to Confirmation
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>