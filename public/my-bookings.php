<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('customer');

$customer_id = getUserId();

// جلب جميع حجوزات العميل
$stmt = $pdo->prepare("
    SELECT b.*, s.title as service_title, u.name as provider_name,
           (SELECT id FROM reviews WHERE booking_id = b.id) as has_review
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.provider_id = u.id
    WHERE b.customer_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$customer_id]);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Local Services</title>
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

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="bi bi-calendar-check"></i> My Bookings</h2>
                <p class="text-muted">All your bookings and their status</p>
                <a href="/local-services-platform/index.php" class="btn btn-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>

        <?php if (count($bookings) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Service</th>
                            <th>Provider</th>
                            <th>Date</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $index => $booking): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($booking['service_title']); ?></td>
                                <td><?php echo htmlspecialchars($booking['provider_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                                <td>
                                    <?php
                                    $status_class = [
                                        'pending' => 'bg-warning',
                                        'confirmed' => 'bg-info',
                                        'completed' => 'bg-success',
                                        'cancelled' => 'bg-danger'
                                    ];
                                    ?>
                                    <span class="badge <?php echo $status_class[$booking['status']] ?? 'bg-secondary'; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="booking-confirmation.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    
                                    <!-- ✅ زر التقييم يظهر فقط إذا كانت الحالة completed ولم يتم التقييم مسبقاً -->
                                    <?php if ($booking['status'] == 'completed' && !$booking['has_review']): ?>
                                        <a href="add-review.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-star"></i> Rate
                                        </a>
                                    <?php elseif ($booking['status'] == 'completed' && $booking['has_review']): ?>
                                        <span class="badge bg-success">Rated ✅</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> You haven't made any bookings yet.
                <a href="browse-services.php" class="alert-link">Browse services now!</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>