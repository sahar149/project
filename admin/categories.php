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
    <title>Manage Categories - Admin Panel</title>
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        brand: {
                            surface: '#F9F5F1', white: '#FFFFFF', primary: '#CB6D51',
                            primaryHover: '#B55A41', secondary: '#C18B8B', text: '#3A2F2B',
                            textMuted: '#7A6E69', border: '#E8DED9', danger: '#E05252',
                            dangerLight: '#FDECEC', successLight: '#E8F5E9', successText: '#2E7D32'
                        }
                    },
                    boxShadow: { soft: '0 4px 20px -2px rgba(0, 0, 0, 0.05)' }
                }
            }
        };
    </script>
    <style>
        body { background-color: #F9F5F1; color: #3A2F2B; }
    </style>
</head>
<body class="flex h-screen overflow-hidden antialiased font-sans">
    <aside class="w-64 bg-white border-r border-brand-border flex flex-col justify-between h-full flex-shrink-0 z-20 hidden md:flex">
        <div>
            <div class="h-20 flex items-center px-6 border-b border-brand-border">
                <div class="flex items-center gap-3"><div class="w-8 h-8 rounded-full bg-brand-primary flex items-center justify-center text-white font-bold text-lg">D</div><span class="font-bold text-lg tracking-tight">Dabberha</span></div>
            </div>
            <nav class="p-4 space-y-1 overflow-y-auto">
                <a class="flex items-center gap-3 px-4 py-3 text-brand-text hover:bg-brand-surface hover:text-brand-primary rounded-xl transition-colors font-medium" href="dashboard.php"><i class="fa-solid fa-border-all w-5 text-center text-gray-400"></i>Dashboard</a>
                <a class="flex items-center gap-3 px-4 py-3 text-brand-text hover:bg-brand-surface hover:text-brand-primary rounded-xl transition-colors font-medium" href="users.php"><i class="fa-solid fa-user-group w-5 text-center text-gray-400"></i>Users</a>
                <a class="flex items-center gap-3 px-4 py-3 text-brand-text hover:bg-brand-surface hover:text-brand-primary rounded-xl transition-colors font-medium" href="services.php"><i class="fa-solid fa-briefcase w-5 text-center text-gray-400"></i>Services</a>
                <a class="flex items-center gap-3 px-4 py-3 text-brand-text hover:bg-brand-surface hover:text-brand-primary rounded-xl transition-colors font-medium" href="bookings.php"><i class="fa-regular fa-calendar-check w-5 text-center text-gray-400"></i>Bookings</a>
                <a class="flex items-center gap-3 px-4 py-3 text-brand-text hover:bg-brand-surface hover:text-brand-primary rounded-xl transition-colors font-medium" href="reviews.php"><i class="fa-regular fa-star w-5 text-center text-gray-400"></i>Reviews</a>
                <a class="flex items-center gap-3 px-4 py-3 bg-brand-surface/70 text-brand-primary rounded-xl font-semibold" href="categories.php"><i class="fa-solid fa-tags w-5 text-center"></i>Categories</a>
                <a class="flex items-center gap-3 px-4 py-3 text-brand-text hover:bg-brand-surface hover:text-brand-primary rounded-xl transition-colors font-medium" href="#"><i class="fa-solid fa-gear w-5 text-center text-gray-400"></i>Settings</a>
            </nav>
        </div>
        <div class="p-4 border-t border-brand-border">
            <div class="flex items-center gap-3 px-2 py-3 mb-2"><div class="w-10 h-10 rounded-full bg-brand-surface border border-brand-border flex items-center justify-center font-bold">A</div><div><p class="font-bold text-sm"><?php echo htmlspecialchars(getUserName()); ?></p><p class="text-xs text-brand-primary">Administrator</p></div></div>
            <a class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-red-600 hover:bg-red-50 rounded-xl font-medium transition-colors" href="/local-services-platform/public/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i>Logout</a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        <header class="h-20 bg-brand-surface flex items-center justify-end px-8 lg:px-12 flex-shrink-0 border-b border-brand-border"><span class="font-medium text-brand-text text-sm"><?php echo htmlspecialchars(getUserName()); ?></span></header>
        <div class="flex-1 overflow-y-auto p-8 lg:px-12 xl:px-16">
            <div class="max-w-7xl mx-auto">
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-2"><i class="fa-solid fa-tags text-2xl text-brand-primary"></i><h1 class="text-3xl font-bold tracking-tight">Manage Categories</h1></div>
                    <p class="text-brand-textMuted">Add and manage service categories.</p>
                    <a class="mt-6 inline-flex items-center gap-2 bg-brand-primary text-white px-5 py-2.5 rounded-xl font-medium hover:bg-brand-primaryHover transition-colors shadow-sm" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i>Back to Dashboard</a>
                </div>

                <?php if ($message): ?>
                    <div class="mb-6 rounded-lg border px-4 py-3 text-sm font-medium <?php echo $message_type === 'success' ? 'border-green-200 bg-brand-successLight text-brand-successText' : 'border-red-200 bg-red-50 text-red-800'; ?>" role="alert"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <div class="flex flex-col xl:flex-row gap-8 items-start">
                    <section class="w-full xl:w-1/3 bg-white rounded-2xl shadow-soft border border-brand-border overflow-hidden">
                        <div class="bg-brand-surface/50 border-b border-brand-border px-6 py-4 flex items-center gap-2"><i class="fa-solid fa-circle-plus text-brand-primary"></i><h2 class="font-semibold text-lg">Add New Category</h2></div>
                        <form method="POST" class="p-6 flex flex-col gap-5">
                            <div><label class="block text-sm font-medium mb-2" for="categoryName">Category Name <span class="text-red-500">*</span></label><input class="w-full rounded-xl border-brand-border focus:border-brand-primary focus:ring focus:ring-brand-primary/20 bg-brand-surface/30 px-4 py-2.5" id="categoryName" name="name" required type="text"></div>
                            <div><label class="block text-sm font-medium mb-2" for="categoryIcon">Icon (FontAwesome/Bootstrap Icon)</label><input class="w-full rounded-xl border-brand-border focus:border-brand-primary focus:ring focus:ring-brand-primary/20 bg-brand-surface/30 px-4 py-2.5 text-brand-textMuted" id="categoryIcon" name="icon" placeholder="bi bi-tools" type="text"></div>
                            <button class="w-full bg-brand-primary hover:bg-brand-primaryHover text-white font-medium py-3 rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2 mt-2" name="add_category" type="submit"><i class="fa-solid fa-plus"></i>Add Category</button>
                        </form>
                    </section>

                    <section class="w-full xl:w-2/3 bg-white rounded-2xl shadow-soft border border-brand-border overflow-hidden">
                        <div class="bg-brand-surface/50 border-b border-brand-border px-6 py-4 flex items-center justify-between"><h2 class="font-semibold text-lg">Existing Categories</h2><span class="text-xs font-medium bg-brand-surface px-3 py-1 rounded-full text-brand-textMuted border border-brand-border"><?php echo count($categories); ?> Total</span></div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php if (count($categories) > 0): ?>
                                <?php foreach ($categories as $category): ?>
                                    <div class="flex items-center justify-between p-4 rounded-xl border border-brand-border hover:border-brand-primary/30 hover:shadow-sm transition-all bg-brand-surface/20 group">
                                        <div class="flex items-center gap-3"><div class="w-10 h-10 rounded-lg bg-brand-surface flex items-center justify-center text-brand-primary border border-brand-border group-hover:border-brand-primary/30 transition-colors"><?php if ($category['icon']): ?><i class="<?php echo htmlspecialchars($category['icon']); ?>"></i><?php else: ?><i class="fa-solid fa-tag"></i><?php endif; ?></div><span class="font-medium"><?php echo htmlspecialchars($category['name']); ?></span></div>
                                        <a aria-label="Delete category" class="w-8 h-8 rounded-lg text-brand-textMuted hover:bg-brand-dangerLight hover:text-brand-danger flex items-center justify-center transition-colors" href="categories.php?delete=1&id=<?php echo (int) $category['id']; ?>" title="Delete Category" onclick="return confirm('Delete this category?')"><i class="fa-solid fa-trash-can"></i></a>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?><p class="text-brand-textMuted md:col-span-2">No categories added yet.</p><?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>
</body>
</html>