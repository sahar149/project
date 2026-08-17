<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Local Services Platform</title>
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
                            <a class="nav-link text-white" href="/local-services-platform/public/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/local-services-platform/public/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto text-center">
                <h1>Welcome to Local Services Platform</h1>
                <p class="lead">Find trusted local service providers near you</p>
                
                <?php if (isLoggedIn()): ?>
                    <div class="alert alert-success mt-4">
                        You are logged in as <strong><?php echo htmlspecialchars(getUserRole()); ?></strong>
                    </div>
                    <?php if (getUserRole() == 'customer'): ?>
                        <a href="/local-services-platform/public/browse-services.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-search"></i> Browse Services
                        </a>
                        <a href="/local-services-platform/public/my-bookings.php" class="btn btn-info btn-lg">
                            <i class="bi bi-list"></i> My Bookings
                        </a>
                    <?php elseif (getUserRole() == 'provider'): ?>
                        <a href="/local-services-platform/provider/dashboard.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-speedometer2"></i> Go to Dashboard
                        </a>
                    <?php elseif (getUserRole() == 'admin'): ?>
                        <a href="/local-services-platform/admin/dashboard.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-speedometer2"></i> Go to Admin Panel
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="mt-4">
                        <a href="/local-services-platform/public/register.php" class="btn btn-success btn-lg">Get Started</a>
                        <a href="/local-services-platform/public/login.php" class="btn btn-primary btn-lg">Login</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">🔧 Find Services</h5>
                        <p class="card-text">Search for plumbers, electricians, tutors and more</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">⭐ Read Reviews</h5>
                        <p class="card-text">See what others say about service providers</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">📅 Instant Booking</h5>
                        <p class="card-text">Book services online instantly</p>
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