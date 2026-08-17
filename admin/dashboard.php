<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

// إحصائيات عامة
$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$total_users = $stmt->fetch()['total_users'];

$stmt = $pdo->query("SELECT COUNT(*) as total_providers FROM users WHERE role = 'provider'");
$total_providers = $stmt->fetch()['total_providers'];

$stmt = $pdo->query("SELECT COUNT(*) as total_customers FROM users WHERE role = 'customer'");
$total_customers = $stmt->fetch()['total_customers'];

$stmt = $pdo->query("SELECT COUNT(*) as total_services FROM services");
$total_services = $stmt->fetch()['total_services'];

$stmt = $pdo->query("SELECT COUNT(*) as total_bookings FROM bookings");
$total_bookings = $stmt->fetch()['total_bookings'];

$stmt = $pdo->query("SELECT COUNT(*) as total_reviews FROM reviews");
$total_reviews = $stmt->fetch()['total_reviews'];

$stmt = $pdo->query("SELECT COUNT(*) as pending_bookings FROM bookings WHERE status = 'pending'");
$pending_bookings = $stmt->fetch()['pending_bookings'];

// آخر 5 مستخدمين مسجلين
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $stmt->fetchAll();

// آخر 5 حجوزات
$stmt = $pdo->query("
    SELECT b.*, u.name as customer_name, s.title as service_title 
    FROM bookings b
    JOIN users u ON b.customer_id = u.id
    JOIN services s ON b.service_id = s.id
    ORDER BY b.created_at DESC 
    LIMIT 5
");
$recent_bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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

        <!-- Statistics Cards -->
        <div class="row mt-3">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-people"></i> Users</h5>
                        <h2><?php echo $total_users; ?></h2>
                        <p class="card-text"><?php echo $total_providers; ?> Providers | <?php echo $total_customers; ?> Customers</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-briefcase"></i> Services</h5>
                        <h2><?php echo $total_services; ?></h2>
                        <p class="card-text">Total services listed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-calendar-check"></i> Bookings</h5>
                        <h2><?php echo $total_bookings; ?></h2>
                        <p class="card-text"><?php echo $pending_bookings; ?> pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-star"></i> Reviews</h5>
                        <h2><?php echo $total_reviews; ?></h2>
                        <p class="card-text">Total reviews given</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5><i class="bi bi-gear"></i> Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="users.php" class="btn btn-primary w-100">
                                    <i class="bi bi-people"></i> Users
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="categories.php" class="btn btn-info w-100">
                                    <i class="bi bi-tags"></i> Categories
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="services.php" class="btn btn-success w-100">
                                    <i class="bi bi-briefcase"></i> Services
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="bookings.php" class="btn btn-warning w-100">
                                    <i class="bi bi-calendar"></i> Bookings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5><i class="bi bi-person-plus"></i> Recent Users</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($recent_users) > 0): ?>
                            <ul class="list-group">
                                <?php foreach ($recent_users as $user): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($user['name']); ?>
                                        <span>
                                            <span class="badge <?php echo $user['role'] == 'admin' ? 'bg-danger' : ($user['role'] == 'provider' ? 'bg-info' : 'bg-secondary'); ?>">
                                                <?php echo $user['role']; ?>
                                            </span>
                                            <small class="text-muted">
                                                <?php echo date('M d', strtotime($user['created_at'])); ?>
                                            </small>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No users registered yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5><i class="bi bi-clock"></i> Recent Bookings</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($recent_bookings) > 0): ?>
                            <ul class="list-group">
                                <?php foreach ($recent_bookings as $booking): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($booking['customer_name']); ?>
                                        <span>
                                            <?php echo htmlspecialchars($booking['service_title']); ?>
                                            <span class="badge <?php echo $booking['status'] == 'pending' ? 'bg-warning' : ($booking['status'] == 'confirmed' ? 'bg-info' : 'bg-success'); ?>">
                                                <?php echo $booking['status']; ?>
                                            </span>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No bookings yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>