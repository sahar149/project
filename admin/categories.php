<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$message = '';
$message_type = '';

// إضافة فئة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $icon = trim($_POST['icon']);
    
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, icon) VALUES (?, ?)");
        if ($stmt->execute([$name, $icon])) {
            $message = "Category added successfully!";
            $message_type = 'success';
        }
    }
}

// حذف فئة
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $category_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$category_id])) {
        $message = "Category deleted successfully!";
        $message_type = 'success';
    }
}

// جلب جميع الفئات
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin</title>
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
                <h2><i class="bi bi-tags"></i> Manage Categories</h2>
                <p class="text-muted">Add and manage service categories</p>
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

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="bi bi-plus-circle"></i> Add New Category</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Category Name *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Icon (FontAwesome/Bootstrap Icon)</label>
                                <input type="text" name="icon" class="form-control" placeholder="bi bi-tools">
                            </div>
                            <button type="submit" name="add_category" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle"></i> Add Category
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5>Existing Categories</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($categories) > 0): ?>
                            <div class="row">
                                <?php foreach ($categories as $category): ?>
                                    <div class="col-md-6 mb-2">
                                        <div class="d-flex justify-content-between align-items-center border p-2 rounded">
                                            <span>
                                                <?php if ($category['icon']): ?>
                                                    <i class="<?php echo htmlspecialchars($category['icon']); ?>"></i>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </span>
                                            <a href="categories.php?delete=1&id=<?php echo $category['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Delete this category?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No categories added yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>