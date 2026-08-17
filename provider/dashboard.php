<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notifications.php';

requireRole('provider');

$provider_id = getUserId();
$unread_count = getUnreadCount($provider_id);
$notifications = getNotifications($provider_id, 5);

// جلب إحصائيات مقدم الخدمة
$stmt = $pdo->prepare("SELECT COUNT(*) as total_services FROM services WHERE provider_id = ?");
$stmt->execute([$provider_id]);
$total_services = $stmt->fetch()['total_services'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_bookings FROM bookings WHERE provider_id = ? AND status != 'cancelled'");
$stmt->execute([$provider_id]);
$total_bookings = $stmt->fetch()['total_bookings'];

$stmt = $pdo->prepare("SELECT COUNT(*) as pending_bookings FROM bookings WHERE provider_id = ? AND status = 'pending'");
$stmt->execute([$provider_id]);
$pending_bookings = $stmt->fetch()['pending_bookings'];

$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_price), 0) as total_earnings FROM bookings WHERE provider_id = ? AND status = 'completed'");
$stmt->execute([$provider_id]);
$total_earnings = $stmt->fetch()['total_earnings'];

// جلب متوسط التقييمات (لكل خدمات مقدم الخدمة)
$stmt = $pdo->prepare("
    SELECT COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as total_reviews 
    FROM reviews r
    JOIN services s ON r.service_id = s.id
    WHERE s.provider_id = ?
");
$stmt->execute([$provider_id]);
$review_stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-tools"></i> Provider Dashboard
            </a>
            <div>
                <span class="text-white me-3">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars(getUserName()); ?>
                </span>
                <a href="/local-services-platform/public/logout.php" class="btn btn-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="bi bi-speedometer2"></i> Dashboard Overview</h2>
                <p class="text-muted">Welcome back, <?php echo htmlspecialchars(getUserName()); ?>!</p>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-briefcase"></i> My Services</h5>
                        <h2><?php echo $total_services; ?></h2>
                        <p class="card-text">Services you offer</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-calendar-check"></i> Total Bookings</h5>
                        <h2><?php echo $total_bookings; ?></h2>
                        <p class="card-text">All confirmed bookings</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-clock-history"></i> Pending</h5>
                        <h2><?php echo $pending_bookings; ?></h2>
                        <p class="card-text">Awaiting your response</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-currency-dollar"></i> Earnings</h5>
                        <h2>$<?php echo number_format($total_earnings, 2); ?></h2>
                        <p class="card-text">From completed jobs</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="bi bi-star"></i> Average Rating</h5>
                                <h2><?php echo number_format($review_stats['avg_rating'], 1); ?> ⭐</h2>
                                <p class="card-text">Based on <?php echo $review_stats['total_reviews']; ?> reviews</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="reviews.php" class="btn btn-light">
                                    <i class="bi bi-star"></i> View All Reviews
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5>
                            <i class="bi bi-bell"></i> Notifications
                            <?php if ($unread_count > 0): ?>
                                <span class="badge bg-danger"><?php echo $unread_count; ?> new</span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                        <?php if (count($notifications) > 0): ?>
                            <?php foreach ($notifications as $notif): ?>
                                <div class="alert <?php echo $notif['is_read'] ? 'alert-secondary' : 'alert-info'; ?> mb-2">
                                    <?php echo htmlspecialchars($notif['message']); ?>
                                    <small class="text-muted d-block">
                                        <?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No notifications</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5><i class="bi bi-gear"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="add-service.php" class="btn btn-primary w-100">
                                    <i class="bi bi-plus-circle"></i> Add New Service
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="my-services.php" class="btn btn-info w-100">
                                    <i class="bi bi-list"></i> My Services
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="bookings.php" class="btn btn-success w-100">
                                    <i class="bi bi-calendar"></i> View Bookings
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="profile.php" class="btn btn-secondary w-100">
                                    <i class="bi bi-person"></i> Edit Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5><i class="bi bi-clock"></i> Recent Booking Requests</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $pdo->prepare("
                            SELECT b.*, s.title as service_title, u.name as customer_name 
                            FROM bookings b
                            JOIN services s ON b.service_id = s.id
                            JOIN users u ON b.customer_id = u.id
                            WHERE b.provider_id = ? AND b.status = 'pending'
                            ORDER BY b.created_at DESC
                            LIMIT 5
                        ");
                        $stmt->execute([$provider_id]);
                        $pending_requests = $stmt->fetchAll();

                        if (count($pending_requests) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Service</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_requests as $request): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($request['customer_name']); ?></td>
                                                <td><?php echo htmlspecialchars($request['service_title']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($request['booking_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-warning"><?php echo $request['status']; ?></span>
                                                </td>
                                                <td>
                                                    <a href="booking-detail.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No pending booking requests.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5><i class="bi bi-star"></i> Recent Reviews</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $pdo->prepare("
                            SELECT r.*, u.name as customer_name, s.title as service_title
                            FROM reviews r
                            JOIN users u ON r.customer_id = u.id
                            JOIN services s ON r.service_id = s.id
                            WHERE s.provider_id = ?
                            ORDER BY r.created_at DESC
                            LIMIT 5
                        ");
                        $stmt->execute([$provider_id]);
                        $recent_reviews = $stmt->fetchAll();

                        if (count($recent_reviews) > 0): ?>
                            <?php foreach ($recent_reviews as $review): ?>
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo htmlspecialchars($review['customer_name']); ?></strong>
                                        <span class="text-warning">
                                            <?php 
                                            $full_stars = round($review['rating']);
                                            $empty_stars = 5 - $full_stars;
                                            echo str_repeat('⭐', $full_stars);
                                            echo str_repeat('☆', $empty_stars);
                                            ?>
                                        </span>
                                    </div>
                                    <p class="mb-0"><?php echo htmlspecialchars($review['comment']); ?></p>
                                    <small class="text-muted">
                                        <?php echo date('M d, Y', strtotime($review['created_at'])); ?> 
                                        on <?php echo htmlspecialchars($review['service_title']); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No reviews yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>