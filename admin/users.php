<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$message = '';
$message_type = '';

// معالجة تغيير حالة المستخدم
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user) {
        $new_status = $user['status'] == 'active' ? 'inactive' : 'active';
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $user_id])) {
            $message = "User status updated successfully!";
            $message_type = 'success';
        }
    }
}

// معالجة حذف مستخدم
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    if ($stmt->execute([$user_id])) {
        $message = "User deleted successfully!";
        $message_type = 'success';
    }
}

// جلب جميع المستخدمين
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
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
                        },
                        role: {
                            customer: '#E2E8F0', customerText: '#475569',
                            admin: '#FEE2E2', adminText: '#EF4444',
                            provider: '#E0F2FE', providerText: '#0EA5E9'
                        },
                        status: { active: '#DCFCE7', activeText: '#22C55E' }
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
                <li><a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-brand-primaryLight text-brand-primary font-medium" href="users.php"><i class="fa-solid fa-users w-5 text-center"></i>Users</a></li>
                <li><a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-text hover:bg-brand-primaryLight hover:text-brand-primary transition-colors font-medium" href="services.php"><i class="fa-solid fa-briefcase w-5 text-center text-gray-400"></i>Services</a></li>
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
        <div class="flex-1 overflow-auto p-6 md:p-8 lg:p-10">
            <div class="max-w-7xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-2xl md:text-3xl font-bold text-brand-text flex items-center gap-3 mb-2"><i class="fa-solid fa-users text-brand-primary"></i>Manage Users</h1>
                    <p class="text-gray-500">View and manage all registered users</p>
                    <a class="mt-4 inline-flex items-center gap-2 bg-brand-primary hover:bg-brand-primaryHover text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i>Back to Dashboard</a>
                </div>

                <?php if ($message): ?>
                    <div class="mb-6 rounded-lg border px-4 py-3 text-sm font-medium <?php echo $message_type === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800'; ?>" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-xl shadow-sm border border-brand-border overflow-hidden">
                    <div class="overflow-x-auto table-container">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead><tr class="border-b border-brand-border bg-gray-50/50"><th class="py-4 px-6 font-semibold text-sm">ID</th><th class="py-4 px-6 font-semibold text-sm">Name</th><th class="py-4 px-6 font-semibold text-sm">Email</th><th class="py-4 px-6 font-semibold text-sm">Role</th><th class="py-4 px-6 font-semibold text-sm">Phone</th><th class="py-4 px-6 font-semibold text-sm">Status</th><th class="py-4 px-6 font-semibold text-sm">Joined</th><th class="py-4 px-6 font-semibold text-sm text-center">Actions</th></tr></thead>
                            <tbody class="divide-y divide-brand-border">
                                <?php foreach ($users as $user): ?>
                                    <?php
                                    $role_classes = $user['role'] === 'admin' ? 'bg-role-admin text-role-adminText' : ($user['role'] === 'provider' ? 'bg-role-provider text-role-providerText' : 'bg-role-customer text-role-customerText');
                                    $status_classes = $user['status'] === 'active' ? 'bg-status-active text-status-activeText' : 'bg-red-100 text-red-600';
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-4 px-6 text-sm text-gray-500"><?php echo (int) $user['id']; ?></td>
                                        <td class="py-4 px-6 text-sm font-medium text-brand-text"><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td class="py-4 px-6 text-sm text-gray-600"><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td class="py-4 px-6"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $role_classes; ?>"><?php echo htmlspecialchars($user['role']); ?></span></td>
                                        <td class="py-4 px-6 text-sm text-gray-600"><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                                        <td class="py-4 px-6"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $status_classes; ?>"><?php echo htmlspecialchars($user['status']); ?></span></td>
                                        <td class="py-4 px-6 text-sm text-gray-600"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td class="py-4 px-6 text-center">
                                            <?php if ($user['role'] != 'admin'): ?>
                                                <div class="flex justify-center gap-2">
                                                    <a href="users.php?toggle_status=1&id=<?php echo (int) $user['id']; ?>" class="w-8 h-8 rounded bg-yellow-100 text-yellow-600 hover:bg-yellow-200 flex items-center justify-center transition-colors" title="Change status" onclick="return confirm('Change status?')"><i class="fa-solid <?php echo $user['status'] === 'active' ? 'fa-pause' : 'fa-play'; ?> text-xs"></i></a>
                                                    <a href="users.php?delete=1&id=<?php echo (int) $user['id']; ?>" class="w-8 h-8 rounded bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center transition-colors" title="Delete user" onclick="return confirm('Delete this user?')"><i class="fa-solid fa-trash-can text-xs"></i></a>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-400 font-medium uppercase tracking-wider">Protected</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>