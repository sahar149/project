<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$message = '';
$message_type = '';

// حذف تقييم
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $review_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    if ($stmt->execute([$review_id])) {
        $message = "Review deleted successfully!";
        $message_type = 'success';
    }
}

// جلب جميع التقييمات
$reviews = $pdo->query("
    SELECT r.*, u.name as customer_name, p.name as provider_name, s.title as service_title
    FROM reviews r
    JOIN users u ON r.customer_id = u.id
    JOIN users p ON r.provider_id = p.id
    JOIN services s ON r.service_id = s.id
    ORDER BY r.created_at DESC
")->fetchAll();

// إحصائيات التقييمات
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        AVG(rating) as avg,
        MIN(rating) as min,
        MAX(rating) as max
    FROM reviews
")->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-shield-lock"></i> Admin Panel
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
                <h2><i class="bi bi-star"></i> Manage Reviews</h2>
                <p class="text-muted">View and manage all reviews</p>
                <a href="dashboard.php" class="btn btn-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $stats['total']; ?></h3>
                        <p>Total Reviews</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3><?php echo number_format($stats['avg'], 1); ?> ⭐</h3>
                        <p>Average Rating</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $stats['max']; ?> ⭐</h3>
                        <p>Highest Rating</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $stats['min']; ?> ⭐</h3>
                        <p>Lowest Rating</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Provider</th>
                        <th>Service</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td><?php echo $review['id']; ?></td>
                            <td><?php echo htmlspecialchars($review['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($review['provider_name']); ?></td>
                            <td><?php echo htmlspecialchars($review['service_title']); ?></td>
                            <td>
                                <span class="text-warning">
                                    <?php echo str_repeat('⭐', $review['rating']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars(substr($review['comment'], 0, 50)) . '...'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                            <td>
                                <a href="reviews.php?delete=1&id=<?php echo $review['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Delete this review?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>