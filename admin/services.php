<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$message = '';
$message_type = '';

// حذف خدمة
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $service_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    if ($stmt->execute([$service_id])) {
        $message = "Service deleted successfully!";
        $message_type = 'success';
    }
}

// جلب جميع الخدمات مع معلومات مقدم الخدمة
$services = $pdo->query("
    SELECT s.*, u.name as provider_name, c.name as category_name 
    FROM services s
    JOIN users u ON s.provider_id = u.id
    JOIN categories c ON s.category_id = c.id
    ORDER BY s.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - Admin Panel</title>
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
                            bg: '#F9F5F1', text: '#3A2F2B', primary: '#CB6D51',
                            primaryHover: '#B35E44', primaryLight: '#FFF0ED', border: '#E8DFD8'
                        }
                    }
                }
            }
        };
    </script>
    <style>
        body { background-color: #F9F5F1; color: #3A2F2B; }
        .table-container::-webkit-scrollbar { height: 8px; }
        .table-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .table-container::-webkit-scrollbar-thumb { background: #d4d4d4; border-radius: 4px; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col md:flex-row">
    <nav class="md:w-64 bg-white border-r border-brand-border flex-shrink-0 hidden md:flex md:flex-col">
        <div class="p-6 border-b border-brand-border flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-brand-primary flex items-center justify-center text-white font-bold">D</div>
            <span class="text-lg font-bold tracking-tight">Dabberha</span>
        </div>
        <div class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-3">
                <li><a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-text hover:bg-brand-primaryLight hover:text-brand-primary transition-colors font-medium" href="dashboard.php"><i class="fa-solid fa-border-all w-5 text-center text-gray-400"></i>Dashboard</a></li>
                <li><a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-text hover:bg-brand-primaryLight hover:text-brand-primary transition-colors font-medium" href="users.php"><i class="fa-solid fa-users w-5 text-center text-gray-400"></i>Users</a></li>
                <li><a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-brand-primaryLight text-brand-primary font-medium" href="services.php"><i class="fa-solid fa-briefcase w-5 text-center"></i>Services</a></li>
                <li><a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-text hover:bg-brand-primaryLight hover:text-brand-primary transition-colors font-medium" href="bookings.php"><i class="fa-regular fa-calendar-check w-5 text-center text-gray-400"></i>Bookings</a></li>
                <li><a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-text hover:bg-brand-primaryLight hover:text-brand-primary transition-colors font-medium" href="reviews.php"><i class="fa-regular fa-star w-5 text-center text-gray-400"></i>Reviews</a></li>
                <li><a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-text hover:bg-brand-primaryLight hover:text-brand-primary transition-colors font-medium" href="categories.php"><i class="fa-solid fa-list-ul w-5 text-center text-gray-400"></i>Categories</a></li>
                <li><a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-text hover:bg-brand-primaryLight hover:text-brand-primary transition-colors font-medium" href="#"><i class="fa-solid fa-gear w-5 text-center text-gray-400"></i>Settings</a></li>
            </ul>
        </div>
        <div class="border-t border-brand-border p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 rounded-full bg-brand-border flex items-center justify-center text-brand-text font-bold mr-3">A</div>
                <div><p class="text-sm font-semibold leading-tight"><?php echo htmlspecialchars(getUserName()); ?></p><p class="text-xs text-brand-primary mt-0.5">Administrator</p></div>
            </div>
            <a class="w-full flex items-center justify-center py-2.5 text-red-600 font-medium hover:bg-red-50 rounded-lg transition-colors" href="/local-services-platform/public/logout.php"><i class="fa-solid fa-arrow-right-from-bracket mr-2"></i>Logout</a>
        </div>
    </nav>

    <header class="md:hidden bg-white border-b border-brand-border p-4 flex items-center justify-between">
        <div class="flex items-center gap-2"><div class="w-8 h-8 rounded-full bg-brand-primary flex items-center justify-center text-white font-bold">D</div><span class="font-bold text-lg">Dabberha</span></div>
        <button class="p-2 text-brand-text hover:bg-brand-bg rounded-lg" aria-label="Open navigation"><i class="fa-solid fa-bars text-xl"></i></button>
    </header>

    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="h-20 bg-brand-bg px-8 flex items-center justify-end shrink-0">
            <span class="text-sm font-medium"><?php echo htmlspecialchars(getUserName()); ?></span>
        </header>
        <div class="flex-1 overflow-auto p-8 lg:px-12 xl:px-16">
            <div class="max-w-7xl mx-auto">
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fa-solid fa-briefcase text-4xl text-brand-primary"></i>
                        <h1 class="text-3xl font-bold text-brand-text tracking-tight">Manage Services</h1>
                    </div>
                    <p class="text-gray-500 text-lg">View and manage all services</p>
                    <a class="mt-4 inline-flex items-center gap-2 bg-brand-primary hover:bg-brand-primaryHover text-white px-5 py-2.5 rounded-xl font-medium transition-colors shadow-sm" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i>Back to Dashboard</a>
                </div>

                <?php if ($message): ?>
                    <div class="mb-6 rounded-lg border px-4 py-3 text-sm font-medium <?php echo $message_type === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800'; ?>" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-2xl shadow-sm border border-brand-border overflow-hidden">
                    <div class="overflow-x-auto table-container">
                        <table class="w-full text-left border-collapse min-w-max">
                            <thead><tr class="border-b border-brand-border bg-brand-bg/50"><th class="py-4 px-6 font-semibold text-brand-text text-sm whitespace-nowrap">ID</th><th class="py-4 px-6 font-semibold text-brand-text text-sm whitespace-nowrap">Title</th><th class="py-4 px-6 font-semibold text-brand-text text-sm whitespace-nowrap">Category</th><th class="py-4 px-6 font-semibold text-brand-text text-sm whitespace-nowrap">Provider</th><th class="py-4 px-6 font-semibold text-brand-text text-sm whitespace-nowrap">Price</th><th class="py-4 px-6 font-semibold text-brand-text text-sm whitespace-nowrap">Type</th><th class="py-4 px-6 font-semibold text-brand-text text-sm whitespace-nowrap text-right pr-8">Actions</th></tr></thead>
                            <tbody class="divide-y divide-brand-border">
                                <?php if (count($services) > 0): ?>
                                    <?php foreach ($services as $service): ?>
                                        <tr class="hover:bg-brand-bg/30 transition-colors">
                                            <td class="py-4 px-6 text-sm text-gray-500"><?php echo (int) $service['id']; ?></td>
                                            <td class="py-4 px-6 text-sm font-medium text-brand-text"><?php echo htmlspecialchars($service['title']); ?></td>
                                            <td class="py-4 px-6 text-sm text-gray-600"><?php echo htmlspecialchars($service['category_name']); ?></td>
                                            <td class="py-4 px-6 text-sm text-gray-600"><?php echo htmlspecialchars($service['provider_name']); ?></td>
                                            <td class="py-4 px-6 text-sm font-medium text-brand-text">$<?php echo number_format($service['price'], 2); ?></td>
                                            <td class="py-4 px-6 text-sm text-gray-600"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200"><?php echo htmlspecialchars($service['price_type']); ?></span></td>
                                            <td class="py-4 px-6 text-right pr-8"><a href="services.php?delete=1&id=<?php echo (int) $service['id']; ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Delete Service" onclick="return confirm('Delete this service?')"><i class="fa-solid fa-trash text-sm"></i></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="py-12 px-6 text-center text-sm text-gray-500">No services found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>