<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('provider');

$provider_id = getUserId();

// جلب خدمات مقدم الخدمة
$stmt = $pdo->prepare("
    SELECT s.*, c.name as category_name 
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.provider_id = ?
    ORDER BY s.id DESC
");
$stmt->execute([$provider_id]);
$services = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Services - Provider</title>
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
                <h2><i class="bi bi-list"></i> My Services</h2>
                <p class="text-muted">Manage your services</p>
                <a href="add-service.php" class="btn btn-primary mb-3">
                    <i class="bi bi-plus-circle"></i> Add New Service
                </a>
            </div>
        </div>

        <?php if (count($services) > 0): ?>
            <div class="row">
                <?php foreach ($services as $service): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <span class="badge bg-info mb-2"><?php echo htmlspecialchars($service['category_name']); ?></span>
                                <h5><?php echo htmlspecialchars($service['title']); ?></h5>
                                <p><?php echo htmlspecialchars(substr($service['description'], 0, 100)) . '...'; ?></p>
                                <div class="d-flex justify-content-between">
                                    <span class="h5 text-primary">$<?php echo number_format($service['price'], 2); ?></span>
                                    <span class="text-muted">/ <?php echo $service['price_type']; ?></span>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="edit-service.php?id=<?php echo $service['id']; ?>" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="delete-service.php?id=<?php echo $service['id']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Are you sure you want to delete this service?')">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> You haven't added any services yet.
                <a href="add-service.php" class="alert-link">Add your first service now!</a>
            </div>
        <?php endif; ?>

        <a href="dashboard.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</body>
</html>