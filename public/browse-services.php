<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

// ============================================
// 🗺️ ميزة الموقع الجغرافي (Geolocation)
// ============================================

// الحصول على موقع المستخدم من الرابط (عند الضغط على "Near Me")
$user_lat = isset($_GET['lat']) ? (float)$_GET['lat'] : null;
$user_lng = isset($_GET['lng']) ? (float)$_GET['lng'] : null;

// حفظ الموقع في الجلسة إذا كان موجوداً
if ($user_lat && $user_lng) {
    $_SESSION['user_lat'] = $user_lat;
    $_SESSION['user_lng'] = $user_lng;
}

// إذا لم يكن هناك موقع في الرابط، حاول جلبها من الجلسة
if (!$user_lat || !$user_lng) {
    $user_lat = $_SESSION['user_lat'] ?? null;
    $user_lng = $_SESSION['user_lng'] ?? null;
}

// ============================================
// 🧠 نظام التوصيات الذكي (Smart Recommendations)
// ============================================

// أوزان التوصية (يمكن تعديلها حسب الحاجة)
$rating_weight = 0.7;      // وزن التقييم
$distance_weight = 0.3;    // وزن المسافة

// جلب جميع الفئات للفلتر
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// جلب الخدمات مع معلومات مقدم الخدمة
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_term = $search;

// ============================================
// 📊 استعلام SQL مع حساب المسافة والتوصية
// ============================================

$sql = "SELECT s.*, u.name as provider_name, u.phone as provider_phone, 
        c.name as category_name, 
        u.latitude as provider_lat, u.longitude as provider_lng,
        COALESCE(ROUND(AVG(r.rating), 1), 0) as avg_rating,
        COUNT(r.id) as review_count";

// إذا كان المستخدم لديه موقع، أضف حساب المسافة والتوصية
if ($user_lat && $user_lng) {
    $sql .= ",
        (6371 * acos(
            cos(radians($user_lat)) * cos(radians(u.latitude)) * 
            cos(radians(u.longitude) - radians($user_lng)) + 
            sin(radians($user_lat)) * sin(radians(u.latitude))
        )) as distance,
        -- 🧠 حساب درجة التوصية الذكية
        (COALESCE(ROUND(AVG(r.rating), 1), 0) * $rating_weight + 
         (1 / (1 + (6371 * acos(
            cos(radians($user_lat)) * cos(radians(u.latitude)) * 
            cos(radians(u.longitude) - radians($user_lng)) + 
            sin(radians($user_lat)) * sin(radians(u.latitude))
         )))) * $distance_weight) as recommendation_score";
} else {
    // إذا لم يكن هناك موقع، استخدم التقييم فقط
    $sql .= ",
        COALESCE(ROUND(AVG(r.rating), 1), 0) as recommendation_score";
}

$sql .= " FROM services s
        JOIN users u ON s.provider_id = u.id
        JOIN categories c ON s.category_id = c.id
        LEFT JOIN reviews r ON r.provider_id = u.id
        WHERE u.status = 'active'";

if ($category_filter > 0) {
    $sql .= " AND s.category_id = $category_filter";
}

if (!empty($search)) {
    $search_param = $pdo->quote("%$search%");
    $sql .= " AND (s.title LIKE $search_param OR s.description LIKE $search_param OR u.name LIKE $search_param)";
}

$sql .= " GROUP BY s.id";

// 🔥 الترتيب حسب درجة التوصية (الأعلى أولاً)
if ($user_lat && $user_lng) {
    $sql .= " ORDER BY recommendation_score DESC, distance ASC";
} else {
    $sql .= " ORDER BY recommendation_score DESC, s.id DESC";
}

$services = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Services - Local Services</title>
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
                    <li class="nav-item">
                        <a class="nav-link active" href="browse-services.php">
                            <i class="bi bi-search"></i> Browse Services
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (getUserRole() == 'customer'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="my-bookings.php">
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
                            <a class="nav-link" href="/local-services-platform/public/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="bi bi-grid-3x3-gap-fill"></i> Browse Services</h2>
                <p class="text-muted">Find trusted service providers near you</p>
                
                <!-- 🧠 عرض معلومات نظام التوصيات -->
                <?php if ($user_lat && $user_lng): ?>
                    <div class="alert alert-success alert-sm">
                        <i class="bi bi-robot"></i> 
                        <strong>Smart Recommendations:</strong> Services are ranked by 
                        <span class="badge bg-primary">Rating (<?php echo $rating_weight * 100; ?>%)</span> + 
                        <span class="badge bg-success">Proximity (<?php echo $distance_weight * 100; ?>%)</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- 🔍 نموذج البحث مع زر "Near Me" -->
        <!-- ============================================ -->
        <div class="row mt-3 mb-4">
            <div class="col-md-12">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search services..." 
                               value="<?php echo htmlspecialchars($search_term); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select">
                            <option value="0">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="browse-services.php" class="btn btn-secondary w-100">
                            <i class="bi bi-arrow-repeat"></i> Reset
                        </a>
                    </div>
                    <div class="col-md-1">
                        <!-- 🗺️ زر "Near Me" -->
                        <button type="button" onclick="getLocation()" class="btn btn-success w-100" title="Find services near me">
                            <i class="bi bi-geo-alt"></i>
                        </button>
                    </div>
                </form>
                <?php if ($user_lat && $user_lng): ?>
                    <small class="text-success mt-2 d-block">
                        <i class="bi bi-check-circle"></i> Showing services near your location
                        <a href="browse-services.php" class="text-danger ms-2">(Clear)</a>
                    </small>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- 📋 عرض الخدمات -->
        <!-- ============================================ -->
        <div class="row">
            <?php if (count($services) > 0): ?>
                <?php foreach ($services as $service): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <span class="badge bg-info mb-2"><?php echo htmlspecialchars($service['category_name']); ?></span>
                                
                                <!-- 🏆 عرض درجة التوصية -->
                                <?php if (isset($service['recommendation_score']) && $service['recommendation_score'] > 0): ?>
                                    <span class="badge bg-warning text-dark float-end">
                                        <i class="bi bi-star-fill"></i> 
                                        <?php echo number_format($service['recommendation_score'], 2); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <h5 class="card-title"><?php echo htmlspecialchars($service['title']); ?></h5>
                                <p class="card-text text-muted">
                                    <small>
                                        <i class="bi bi-person"></i> <?php echo htmlspecialchars($service['provider_name']); ?>
                                    </small>
                                    <?php if (isset($service['distance']) && $service['distance'] !== null): ?>
                                        <br>
                                        <small>
                                            <i class="bi bi-geo"></i> 
                                            <?php echo number_format($service['distance'], 1); ?> km away
                                        </small>
                                    <?php endif; ?>
                                </p>
                                <p class="card-text">
                                    <?php echo htmlspecialchars(substr($service['description'], 0, 100)) . '...'; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 text-primary">
                                        $<?php echo number_format($service['price'], 2); ?>
                                        <small class="text-muted">/ <?php echo $service['price_type']; ?></small>
                                    </span>
                                    <div>
                                        <?php if ($service['review_count'] > 0): ?>
                                            <span class="text-warning">
                                                <?php 
                                                $full_stars = round($service['avg_rating']);
                                                $empty_stars = 5 - $full_stars;
                                                echo str_repeat('⭐', $full_stars);
                                                echo str_repeat('☆', $empty_stars);
                                                ?>
                                            </span>
                                            <small class="text-muted">(<?php echo $service['review_count']; ?>)</small>
                                        <?php else: ?>
                                            <small class="text-muted">No reviews yet</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="service-detail.php?id=<?php echo $service['id']; ?>" class="btn btn-primary w-100">
                                    <i class="bi bi-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No services found. 
                        <?php if (getUserRole() == 'provider'): ?>
                            <a href="/local-services-platform/provider/dashboard.php" class="alert-link">Add your first service now!</a>
                        <?php else: ?>
                            Please check back later or try different filters.
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="bg-light text-center text-muted py-3 mt-5">
        <div class="container">
            &copy; <?php echo date('Y'); ?> Local Services Platform. All rights reserved.
        </div>
    </footer>

    <!-- ============================================ -->
    <!-- 🗺️ كود JavaScript لجلب الموقع -->
    <!-- ============================================ -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function getLocation() {
        if (navigator.geolocation) {
            // عرض رسالة للمستخدم
            document.querySelector('[onclick="getLocation()"]').innerHTML = '<i class="bi bi-hourglass-split"></i>';
            document.querySelector('[onclick="getLocation()"]').disabled = true;
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    // إعادة التوجيه مع إحداثيات الموقع
                    window.location.href = 'browse-services.php?lat=' + lat + '&lng=' + lng;
                },
                function(error) {
                    alert('Unable to get your location. Please allow location access and try again.');
                    // إعادة الزر إلى حالته الطبيعية
                    document.querySelector('[onclick="getLocation()"]').innerHTML = '<i class="bi bi-geo-alt"></i>';
                    document.querySelector('[onclick="getLocation()"]').disabled = false;
                }
            );
        } else {
            alert('Geolocation is not supported by your browser.');
        }
    }
    </script>
</body>
</html>