<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('provider');

$provider_id = getUserId();
$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($service_id == 0) {
    header('Location: my-services.php');
    exit;
}

// جلب الخدمة
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND provider_id = ?");
$stmt->execute([$service_id, $provider_id]);
$service = $stmt->fetch();

if (!$service) {
    header('Location: my-services.php');
    exit;
}

// جلب الفئات
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)$_POST['category_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $price_type = $_POST['price_type'];

    if (empty($title) || empty($description) || $price <= 0) {
        $error = 'Please fill all fields correctly';
    } else {
        $stmt = $pdo->prepare("
            UPDATE services 
            SET category_id = ?, title = ?, description = ?, price = ?, price_type = ?
            WHERE id = ? AND provider_id = ?
        ");
        if ($stmt->execute([$category_id, $title, $description, $price, $price_type, $service_id, $provider_id])) {
            $success = 'Service updated successfully!';
            // تحديث البيانات المعروضة
            $service['category_id'] = $category_id;
            $service['title'] = $title;
            $service['description'] = $description;
            $service['price'] = $price;
            $service['price_type'] = $price_type;
        } else {
            $error = 'Failed to update service. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service - Provider</title>
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
                <div class="card shadow">
                    <div class="card-header bg-warning">
                        <h4><i class="bi bi-pencil"></i> Edit Service</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label>Category *</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo $service['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Service Title *</label>
                                <input type="text" name="title" class="form-control" 
                                       value="<?php echo htmlspecialchars($service['title']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Description *</label>
                                <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($service['description']); ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label>Price *</label>
                                        <input type="number" name="price" class="form-control" 
                                               step="0.01" min="0.01" 
                                               value="<?php echo $service['price']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label>Price Type *</label>
                                        <select name="price_type" class="form-select" required>
                                            <option value="fixed" <?php echo $service['price_type'] == 'fixed' ? 'selected' : ''; ?>>Fixed Price</option>
                                            <option value="hourly" <?php echo $service['price_type'] == 'hourly' ? 'selected' : ''; ?>>Hourly Rate</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-check-circle"></i> Update Service
                            </button>
                        </form>
                        <hr>
                        <a href="my-services.php" class="btn btn-secondary w-100">
                            <i class="bi bi-arrow-left"></i> Back to My Services
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>