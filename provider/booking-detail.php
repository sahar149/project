<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('provider');

$provider_id = getUserId();
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($booking_id == 0) {
    header('Location: bookings.php');
    exit;
}

// جلب تفاصيل الحجز
$stmt = $pdo->prepare("
    SELECT b.*, s.title as service_title, s.price as service_price,
           u.name as customer_name, u.phone as customer_phone, u.email as customer_email
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.customer_id = u.id
    WHERE b.id = ? AND b.provider_id = ?
");
$stmt->execute([$booking_id, $provider_id]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: bookings.php');
    exit;
}

// معالجة تحديث الحالة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    $allowed_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ? AND provider_id = ?");
        $stmt->execute([$new_status, $booking_id, $provider_id]);
        header("Location: booking-detail.php?id=$booking_id&success=1");
        exit;
    }
}

$success = isset($_GET['success']) ? true : false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - Provider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-tools"></i> Provider Dashboard
            </a>
            <div>
                <span class="text-white me-3"><?php echo htmlspecialchars(getUserName()); ?></span>
                <a href="/local-services-platform/public/logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle"></i> Booking status updated successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="bi bi-calendar-check"></i> Booking Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Booking ID:</strong> #<?php echo $booking['id']; ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="badge <?php 
                                        echo $booking['status'] == 'pending' ? 'bg-warning' : 
                                            ($booking['status'] == 'confirmed' ? 'bg-info' : 
                                            ($booking['status'] == 'completed' ? 'bg-success' : 'bg-danger')); 
                                    ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </p>
                                <p><strong>Date:</strong> <?php echo date('F d, Y', strtotime($booking['booking_date'])); ?></p>
                                <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($booking['booking_time'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Service:</strong> <?php echo htmlspecialchars($booking['service_title']); ?></p>
                                <p><strong>Price:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                                <p><strong>Booked on:</strong> <?php echo date('F d, Y h:i A', strtotime($booking['created_at'])); ?></p>
                            </div>
                        </div>

                        <?php if (!empty($booking['notes'])): ?>
                            <hr>
                            <p><strong>Customer Notes:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars($booking['notes'])); ?></p>
                        <?php endif; ?>

                        <hr>
                        <h5><i class="bi bi-person"></i> Customer Information</h5>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['customer_name']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['customer_phone'] ?? 'Not provided'); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['customer_email']); ?></p>
                    </div>
                </div>

                <!-- Update Status -->
                <div class="card shadow mt-3">
                    <div class="card-header bg-light">
                        <h5><i class="bi bi-arrow-repeat"></i> Update Status</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-8">
                                    <select name="status" class="form-select">
                                        <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirm</option>
                                        <option value="completed" <?php echo $booking['status'] == 'completed' ? 'selected' : ''; ?>>Complete</option>
                                        <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancel</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-save"></i> Update Status
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="bookings.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Bookings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>