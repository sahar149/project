<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('provider');

$provider_id = getUserId();

// جلب جميع التقييمات الخاصة بخدمات مقدم الخدمة
$stmt = $pdo->prepare("
    SELECT r.*, u.name as customer_name, s.title as service_title
    FROM reviews r
    JOIN users u ON r.customer_id = u.id
    JOIN services s ON r.service_id = s.id
    WHERE s.provider_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$provider_id]);
$reviews = $stmt->fetchAll();

// حساب متوسط التقييم
$stmt = $pdo->prepare("
    SELECT COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as total 
    FROM reviews r
    JOIN services s ON r.service_id = s.id
    WHERE s.provider_id = ?
");
$stmt->execute([$provider_id]);
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reviews - Provider</title>
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
            <div class="col-md-12">
                <h2><i class="bi bi-star"></i> My Reviews</h2>
                <p class="text-muted">What customers say about your services</p>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3><?php echo number_format($stats['avg_rating'], 1); ?> ⭐</h3>
                                <p>Average Rating</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3><?php echo $stats['total']; ?></h3>
                                <p>Total Reviews</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <a href="dashboard.php" class="btn btn-secondary w-100 h-100 d-flex align-items-center justify-content-center">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>

                <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?php echo htmlspecialchars($review['customer_name']); ?></strong>
                                        <span class="text-muted">on <?php echo htmlspecialchars($review['service_title']); ?></span>
                                    </div>
                                    <span class="text-warning">
                                        <?php echo str_repeat('⭐', $review['rating']); ?>
                                    </span>
                                </div>
                                <p class="mt-2"><?php echo htmlspecialchars($review['comment']); ?></p>
                                <small class="text-muted">
                                    <?php echo date('F d, Y h:i A', strtotime($review['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No reviews yet. Keep providing great service!
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>