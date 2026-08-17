<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($service_id == 0) {
    header('Location: browse-services.php');
    exit;
}

// جلب تفاصيل الخدمة
$stmt = $pdo->prepare("
    SELECT s.*, u.name as provider_name, u.phone as provider_phone, u.email as provider_email,
           u.address as provider_address, c.name as category_name,
           COALESCE(ROUND(AVG(r.rating), 1), 0) as avg_rating, 
           COUNT(r.id) as review_count
    FROM services s
    JOIN users u ON s.provider_id = u.id
    JOIN categories c ON s.category_id = c.id
    LEFT JOIN reviews r ON r.service_id = s.id
    WHERE s.id = ? AND u.status = 'active'
    GROUP BY s.id
");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    header('Location: browse-services.php');
    exit;
}

// جلب التقييمات
$stmt = $pdo->prepare("
    SELECT r.*, u.name as customer_name 
    FROM reviews r 
    JOIN users u ON r.customer_id = u.id 
    WHERE r.service_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
$stmt->execute([$service_id]);
$reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($service['title']); ?> - Local Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/local-services-platform/index.php">
                <i class="bi bi-tools"></i> Local Services
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <?php if (getUserRole() == 'customer'): ?>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="/local-services-platform/public/my-bookings.php">
                                    <i class="bi bi-list"></i> My Bookings
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <span class="nav-link text-white">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars(getUserName()); ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/local-services-platform/public/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <span class="badge bg-info mb-2"><?php echo htmlspecialchars($service['category_name']); ?></span>
                        <h2><?php echo htmlspecialchars($service['title']); ?></h2>
                        <p class="text-muted">
                            <i class="bi bi-person"></i> Provided by: <?php echo htmlspecialchars($service['provider_name']); ?>
                        </p>
                        <hr>
                        <h5>Description</h5>
                        <p><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Price</h5>
                                <span class="h3 text-primary">
                                    $<?php echo number_format($service['price'], 2); ?>
                                    <small class="text-muted">/ <?php echo $service['price_type']; ?></small>
                                </span>
                            </div>
                            <div class="col-md-6 text-end">
                                <h5>Rating</h5>
                                <?php if ($service['review_count'] > 0): ?>
                                    <span class="text-warning">
                                        <?php 
                                        $full_stars = round($service['avg_rating']);
                                        $empty_stars = 5 - $full_stars;
                                        echo str_repeat('⭐', $full_stars);
                                        echo str_repeat('☆', $empty_stars);
                                        ?>
                                    </span>
                                    <span class="text-muted">(<?php echo $service['review_count']; ?> reviews)</span>
                                <?php else: ?>
                                    <span class="text-muted">No reviews yet</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-light">
                        <h5><i class="bi bi-person-badge"></i> Provider Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($service['provider_name']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($service['provider_phone'] ?? 'Not provided'); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($service['provider_email']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($service['provider_address'] ?? 'Not provided'); ?></p>
                    </div>
                </div>

                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-light">
                        <h5><i class="bi bi-star"></i> Reviews</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($reviews) > 0): ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo htmlspecialchars($review['customer_name']); ?></strong>
                                        <span class="text-warning">
                                            <?php echo str_repeat('⭐', $review['rating']); ?>
                                        </span>
                                    </div>
                                    <p class="mb-0"><?php echo htmlspecialchars($review['comment']); ?></p>
                                    <small class="text-muted">
                                        <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No reviews yet for this service.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="bi bi-calendar-check"></i> Book This Service</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isLoggedIn() && getUserRole() == 'customer'): ?>
                            <form method="POST" action="book-service.php">
                                <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                <input type="hidden" name="provider_id" value="<?php echo $service['provider_id']; ?>">
                                <div class="mb-3">
                                    <label>Service Date</label>
                                    <input type="date" name="booking_date" class="form-control" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label>Service Time</label>
                                    <input type="time" name="booking_time" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Notes (Optional)</label>
                                    <textarea name="notes" class="form-control" rows="2" 
                                              placeholder="Any special requirements?"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-circle"></i> Book Now - $<?php echo number_format($service['price'], 2); ?>
                                </button>
                            </form>
                        <?php elseif (!isLoggedIn()): ?>
                            <div class="alert alert-warning">
                                Please <a href="login.php">login</a> as a customer to book this service.
                            </div>
                        <?php elseif (getUserRole() == 'provider'): ?>
                            <div class="alert alert-info">
                                You are registered as a provider. Please switch to customer to book.
                            </div>
                        <?php elseif (getUserRole() == 'admin'): ?>
                            <div class="alert alert-info">
                                You are logged in as admin.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-light text-center text-muted py-3 mt-5">
        <div class="container">
            &copy; <?php echo date('Y'); ?> Local Services Platform. All rights reserved.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>