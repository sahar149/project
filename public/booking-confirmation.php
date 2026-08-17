<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($booking_id == 0) {
    header('Location: browse-services.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT b.*, s.title as service_title, u.name as provider_name 
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.provider_id = u.id
    WHERE b.id = ? AND b.customer_id = ?
");
$stmt->execute([$booking_id, getUserId()]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: browse-services.php');
    exit;
}

// التحقق إذا كان هناك تقييم مسبق
$stmt = $pdo->prepare("SELECT id FROM reviews WHERE booking_id = ?");
$stmt->execute([$booking_id]);
$has_review = $stmt->fetch() ? true : false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
                <div class="card shadow-lg">
                    <div class="card-body text-center">
                        <div class="display-1 text-success">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <h2 class="mt-3">Booking Confirmed! ✅</h2>
                        <p class="text-muted">Your service has been booked successfully.</p>
                        <hr>
                        <div class="text-start">
                            <p><strong>Service:</strong> <?php echo htmlspecialchars($booking['service_title']); ?></p>
                            <p><strong>Provider:</strong> <?php echo htmlspecialchars($booking['provider_name']); ?></p>
                            <p><strong>Date:</strong> <?php echo date('F d, Y', strtotime($booking['booking_date'])); ?></p>
                            <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($booking['booking_time'])); ?></p>
                            <p><strong>Total Price:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge <?php 
                                    echo $booking['status'] == 'pending' ? 'bg-warning' : 
                                        ($booking['status'] == 'confirmed' ? 'bg-info' : 
                                        ($booking['status'] == 'completed' ? 'bg-success' : 'bg-danger')); 
                                ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </p>
                        </div>
                        <hr>
                        
                        <!-- ✅ زر التقييم يظهر فقط إذا كانت الحالة completed ولم يتم التقييم مسبقاً -->
                        <?php if ($booking['status'] == 'completed' && !$has_review): ?>
                            <a href="add-review.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-warning btn-lg">
                                <i class="bi bi-star"></i> Rate This Service
                            </a>
                            <br><br>
                        <?php elseif ($booking['status'] == 'completed' && $has_review): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-star-fill text-warning"></i> You have already rated this service.
                            </div>
                        <?php elseif ($booking['status'] == 'pending'): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-clock"></i> Your booking is pending provider confirmation.
                                <br>You can rate the service after it's completed.
                            </div>
                        <?php elseif ($booking['status'] == 'confirmed'): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-check-circle"></i> Your booking has been confirmed!
                                <br>You can rate the service after it's completed.
                            </div>
                        <?php elseif ($booking['status'] == 'cancelled'): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-x-circle"></i> This booking was cancelled.
                            </div>
                        <?php endif; ?>
                        
                        <a href="browse-services.php" class="btn btn-primary">
                            <i class="bi bi-search"></i> Browse More Services
                        </a>
                        <a href="/local-services-platform/index.php" class="btn btn-secondary">
                            <i class="bi bi-house"></i> Home
                        </a>
                        <a href="my-bookings.php" class="btn btn-outline-primary">
                            <i class="bi bi-list"></i> My Bookings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>