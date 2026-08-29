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
    <title>Admin Dashboard Overview</title>
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#F9F5F1', 100: '#F4EAE6', 200: '#E9D5CF',
                            300: '#D5B4AB', 400: '#C18B8B', 500: '#CB6D51',
                            600: '#B55A40', 700: '#914530', 800: '#753A2A', 900: '#3A2F2B'
                        },
                        surface: { DEFAULT: '#FFFFFF', alt: '#F9F5F1', border: '#E9D5CF' }
                    },
                    borderRadius: { card: '0.5rem' }
                }
            }
        };
    </script>
</head>
<body class="bg-brand-50 text-brand-900 font-sans min-h-screen flex flex-col md:flex-row">
    <nav class="md:w-64 bg-surface border-r border-surface-border flex-shrink-0 hidden md:flex md:flex-col" data-purpose="sidebar-nav">
        <div class="p-6 border-b border-surface-border flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-white font-bold">D</div>
            <span class="font-bold text-lg tracking-tight">Dabberha</span>
        </div>
        <div class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-3">
                <li><a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-brand-50 text-brand-600 font-medium" href="dashboard.php"><i class="ph ph-squares-four text-xl"></i>Dashboard</a></li>
                <li><a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-900 hover:bg-brand-50 hover:text-brand-600 transition-colors font-medium" href="users.php"><i class="ph ph-users text-xl"></i>Users</a></li>
                <li><a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-900 hover:bg-brand-50 hover:text-brand-600 transition-colors font-medium" href="services.php"><i class="ph ph-briefcase text-xl"></i>Services</a></li>
                <li><a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-900 hover:bg-brand-50 hover:text-brand-600 transition-colors font-medium" href="bookings.php"><i class="ph ph-calendar-check text-xl"></i>Bookings</a></li>
                <li><a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-900 hover:bg-brand-50 hover:text-brand-600 transition-colors font-medium" href="reviews.php"><i class="ph ph-star text-xl"></i>Reviews</a></li>
                <li><a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-900 hover:bg-brand-50 hover:text-brand-600 transition-colors font-medium" href="categories.php"><i class="ph ph-list-dashes text-xl"></i>Categories</a></li>
                <li><a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-brand-900 hover:bg-brand-50 hover:text-brand-600 transition-colors font-medium" href="#"><i class="ph ph-gear text-xl"></i>Settings</a></li>
            </ul>
        </div>
        <div class="p-4 border-t border-surface-border">
            <div class="flex items-center gap-3 px-3 py-2">
                <div class="w-8 h-8 rounded-full bg-brand-200 flex items-center justify-center text-brand-700 font-medium">A</div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-brand-900 truncate"><?php echo htmlspecialchars(getUserName()); ?></p>
                    <p class="text-xs text-brand-600 truncate">Administrator</p>
                </div>
            </div>
            <a class="mt-2 w-full flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors" href="/local-services-platform/public/logout.php">
                <i class="ph ph-sign-out"></i>Logout
            </a>
        </div>
    </nav>

    <header class="md:hidden bg-surface border-b border-surface-border p-4 flex items-center justify-between">
        <div class="flex items-center gap-2"><div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-white font-bold">D</div><span class="font-bold text-lg">Dabberha</span></div>
        <button class="p-2 text-brand-900 hover:bg-brand-50 rounded-lg" aria-label="Open navigation"><i class="ph ph-list text-2xl"></i></button>
    </header>

    <main class="flex-1 p-6 md:p-8 lg:p-10 max-w-7xl mx-auto w-full">
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold flex items-center gap-3"><i class="ph ph-gauge text-brand-500"></i>Dashboard Overview</h1>
            <p class="text-brand-600 mt-2">Welcome back, <?php echo htmlspecialchars(getUserName()); ?>!</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-surface rounded-card p-6 shadow-sm border border-surface-border flex flex-col justify-between relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-brand-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="relative z-10"><div class="flex items-center gap-2 text-brand-700 font-semibold mb-4"><i class="ph ph-users-three text-xl text-brand-500"></i>Users</div><div class="text-4xl font-bold text-brand-900 mb-2"><?php echo $total_users; ?></div><div class="text-sm text-brand-600"><?php echo $total_providers; ?> Providers | <?php echo $total_customers; ?> Customers</div></div>
            </div>
            <div class="bg-surface rounded-card p-6 shadow-sm border border-surface-border flex flex-col justify-between relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-brand-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="relative z-10"><div class="flex items-center gap-2 text-brand-700 font-semibold mb-4"><i class="ph ph-briefcase text-xl text-brand-500"></i>Services</div><div class="text-4xl font-bold text-brand-900 mb-2"><?php echo $total_services; ?></div><div class="text-sm text-brand-600">Total services listed</div></div>
            </div>
            <div class="bg-surface rounded-card p-6 shadow-sm border border-surface-border flex flex-col justify-between relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-brand-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="relative z-10"><div class="flex items-center gap-2 text-brand-700 font-semibold mb-4"><i class="ph ph-calendar-check text-xl text-brand-500"></i>Bookings</div><div class="text-4xl font-bold text-brand-900 mb-2"><?php echo $total_bookings; ?></div><div class="text-sm text-brand-600"><?php echo $pending_bookings; ?> pending</div></div>
            </div>
            <div class="bg-surface rounded-card p-6 shadow-sm border border-surface-border flex flex-col justify-between relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-brand-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="relative z-10"><div class="flex items-center gap-2 text-brand-700 font-semibold mb-4"><i class="ph ph-star text-xl text-brand-500"></i>Reviews</div><div class="text-4xl font-bold text-brand-900 mb-2"><?php echo $total_reviews; ?></div><div class="text-sm text-brand-600">Total reviews given</div></div>
            </div>
        </div>

        <div class="bg-surface rounded-card shadow-sm border border-surface-border mb-8">
            <div class="p-4 border-b border-surface-border flex items-center gap-2 font-semibold text-brand-900"><i class="ph ph-gear-six text-lg text-brand-500"></i>Management</div>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a class="flex items-center justify-center gap-2 py-2.5 px-4 bg-white border border-brand-200 text-brand-700 hover:bg-brand-50 hover:border-brand-300 rounded-lg font-medium transition-colors" href="users.php"><i class="ph ph-users"></i>Users</a>
                <a class="flex items-center justify-center gap-2 py-2.5 px-4 bg-white border border-brand-200 text-brand-700 hover:bg-brand-50 hover:border-brand-300 rounded-lg font-medium transition-colors" href="categories.php"><i class="ph ph-tag"></i>Categories</a>
                <a class="flex items-center justify-center gap-2 py-2.5 px-4 bg-white border border-brand-200 text-brand-700 hover:bg-brand-50 hover:border-brand-300 rounded-lg font-medium transition-colors" href="services.php"><i class="ph ph-briefcase"></i>Services</a>
                <a class="flex items-center justify-center gap-2 py-2.5 px-4 bg-white border border-brand-200 text-brand-700 hover:bg-brand-50 hover:border-brand-300 rounded-lg font-medium transition-colors" href="bookings.php"><i class="ph ph-calendar"></i>Bookings</a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <section class="bg-surface rounded-card shadow-sm border border-surface-border flex flex-col">
                <div class="p-4 border-b border-surface-border flex items-center justify-between"><h2 class="font-semibold text-brand-900 flex items-center gap-2"><i class="ph ph-user-plus text-lg text-brand-500"></i>Recent Users</h2></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap"><tbody class="divide-y divide-surface-border">
                        <?php if (count($recent_users) > 0): ?>
                            <?php foreach ($recent_users as $user): ?>
                                <tr class="hover:bg-brand-50 transition-colors"><td class="p-4 py-3 font-medium text-brand-900"><?php echo htmlspecialchars($user['name']); ?></td><td class="p-4 py-3 text-right"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?php echo $user['role'] == 'admin' ? 'bg-red-100 text-red-700' : ($user['role'] == 'provider' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'); ?>"><?php echo htmlspecialchars($user['role']); ?></span></td><td class="p-4 py-3 text-brand-600 text-right w-24"><?php echo date('M d', strtotime($user['created_at'])); ?></td></tr>
                            <?php endforeach; ?>
                        <?php else: ?><tr><td class="p-4 text-sm text-brand-600">No users registered yet.</td></tr><?php endif; ?>
                    </tbody></table>
                </div>
            </section>

            <section class="bg-surface rounded-card shadow-sm border border-surface-border flex flex-col">
                <div class="p-4 border-b border-surface-border flex items-center justify-between"><h2 class="font-semibold text-brand-900 flex items-center gap-2"><i class="ph ph-clock-counter-clockwise text-lg text-brand-500"></i>Recent Bookings</h2></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap"><tbody class="divide-y divide-surface-border">
                        <?php if (count($recent_bookings) > 0): ?>
                            <?php foreach ($recent_bookings as $booking): ?>
                                <tr class="hover:bg-brand-50 transition-colors"><td class="p-4 py-3 font-medium text-brand-900"><?php echo htmlspecialchars($booking['customer_name']); ?></td><td class="p-4 py-3 text-brand-600 text-right"><?php echo htmlspecialchars($booking['service_title']); ?></td><td class="p-4 py-3 text-right w-24"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $booking['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : ($booking['status'] == 'confirmed' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'); ?>"><?php echo htmlspecialchars($booking['status']); ?></span></td></tr>
                            <?php endforeach; ?>
                        <?php else: ?><tr><td class="p-4 text-sm text-brand-600">No bookings yet.</td></tr><?php endif; ?>
                    </tbody></table>
                </div>
            </section>
        </div>
    </main>
</body>
</html>