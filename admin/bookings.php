<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$message = '';
$message_type = '';

// تحديث حالة الحجز
if (isset($_POST['update_status']) && isset($_POST['booking_id']) && isset($_POST['status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $status = $_POST['status'];
    $allowed = ['pending', 'confirmed', 'completed', 'cancelled'];
    
    if (in_array($status, $allowed)) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $booking_id])) {
            $message = "Booking status updated!";
            $message_type = 'success';
        }
    }
}

// حذف حجز
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $booking_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    if ($stmt->execute([$booking_id])) {
        $message = "Booking deleted!";
        $message_type = 'success';
    }
}

// جلب جميع الحجوزات
$bookings = $pdo->query("
    SELECT b.*, u.name as customer_name, s.title as service_title, p.name as provider_name
    FROM bookings b
    JOIN users u ON b.customer_id = u.id
    JOIN services s ON b.service_id = s.id
    JOIN users p ON b.provider_id = p.id
    ORDER BY b.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin Panel</title>
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
                            bg: '#F9F5F1', primary: '#CB6D51', primaryHover: '#B55A40',
                            text: '#3A2F2B', textMuted: '#6B5E59', border: '#E8E1DA', surface: '#FFFFFF',
                            success: '#4ADE80', successBg: '#DCFCE7', warning: '#FBBF24', warningBg: '#FEF3C7',
                            danger: '#F87171', dangerBg: '#FEE2E2'
                        }
                    }
                }
            }
        };
    </script>
    <style>
        body { background-color: #F9F5F1; color: #3A2F2B; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #E8E1DA; border-radius: 4px; }
    </style>
</head>
<body class="font-sans antialiased min-h-screen flex flex-col md:flex-row">
    <aside class="w-64 bg-brand-surface border-r border-brand-border flex-shrink-0 hidden md:flex flex-col z-20">
        <div>
            <div class="h-20 flex items-center px-6 border-b border-brand-border"><div class="flex items-center gap-3"><div class="w-8 h-8 rounded-full bg-brand-primary flex items-center justify-center text-white font-bold text-lg">D</div><span class="font-bold text-lg tracking-tight">Dabberha</span></div></div>
            <nav class="p-4 space-y-1">
                <a class="flex items-center gap-3 px-4 py-3 text-brand-text hover:bg-brand-bg hover:text-brand-primary rounded-xl transition-colors font-medium" href="dashboard.php"><i class="fa-solid fa-border-all w-5 text-center text-gray-400"></i>Dashboard</a>
                <a class="flex items-center gap-3 px-4 py-3 text-brand-text hover:bg-brand-bg hover:text-brand-primary rounded-xl transition-colors font-medium" href="users.php"><i class="fa-solid fa-user-group w-5 text-center text-gray-400"></i>Users</a>
                <a class="flex items-center gap-3 px-4 py-3 text-brand-text hover:bg-brand-bg hover:text-brand-primary rounded-xl transition-colors font-medium" href="services.php"><i class="fa-solid fa-briefcase w-5 text-center text-gray-400"></i>Services</a>
                <a class="flex items-center gap-3 px-4 py-3 bg-brand-bg text-brand-primary rounded-xl font-semibold" href="bookings.php"><i class="fa-regular fa-calendar-check w-5 text-center"></i>Bookings</a>
                <a class="flex items-center gap-3 px-4 py-3 text-brand-text hover:bg-brand-bg hover:text-brand-primary rounded-xl transition-colors font-medium" href="reviews.php"><i class="fa-regular fa-star w-5 text-center text-gray-400"></i>Reviews</a>
                <a class="flex items-center gap-3 px-4 py-3 text-brand-text hover:bg-brand-bg hover:text-brand-primary rounded-xl transition-colors font-medium" href="categories.php"><i class="fa-solid fa-list-ul w-5 text-center text-gray-400"></i>Categories</a>
                <a class="flex items-center gap-3 px-4 py-3 text-brand-text hover:bg-brand-bg hover:text-brand-primary rounded-xl transition-colors font-medium" href="#"><i class="fa-solid fa-gear w-5 text-center text-gray-400"></i>Settings</a>
            </nav>
        </div>
        <div class="p-4 border-t border-brand-border mt-auto"><div class="flex items-center gap-3 px-2 py-3 mb-2"><div class="w-10 h-10 rounded-full bg-brand-bg border border-brand-border flex items-center justify-center font-bold">A</div><div><p class="font-bold text-sm"><?php echo htmlspecialchars(getUserName()); ?></p><p class="text-xs text-brand-primary">Administrator</p></div></div><a class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-red-600 hover:bg-red-50 rounded-xl font-medium transition-colors" href="/local-services-platform/public/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i>Logout</a></div>
    </aside>

    <header class="md:hidden bg-white border-b border-brand-border p-4 flex items-center justify-between"><div class="flex items-center gap-2"><div class="w-8 h-8 rounded-full bg-brand-primary flex items-center justify-center text-white font-bold">D</div><span class="font-bold text-lg">Dabberha</span></div><button class="p-2 text-brand-text hover:bg-brand-bg rounded-lg" aria-label="Open navigation"><i class="fa-solid fa-bars text-xl"></i></button></header>

    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="h-16 bg-brand-surface border-b border-brand-border flex items-center justify-between px-6 z-10 flex-shrink-0"><div class="hidden md:flex items-center text-sm text-brand-textMuted"><span>Admin Panel</span><i class="fa-solid fa-chevron-right text-xs mx-2"></i><span class="text-brand-text font-medium">Manage Bookings</span></div><div class="flex items-center gap-4 ml-auto"><button class="text-brand-textMuted hover:text-brand-primary transition-colors relative" aria-label="Notifications"><i class="fa-regular fa-bell text-lg"></i><span class="absolute top-0 right-0 w-2 h-2 bg-brand-primary rounded-full"></span></button><span class="font-medium text-brand-text text-sm"><?php echo htmlspecialchars(getUserName()); ?></span></div></header>
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"><div><div class="flex items-center gap-3 mb-1"><i class="fa-regular fa-calendar text-2xl text-brand-primary"></i><h1 class="text-2xl font-bold text-brand-text">Manage Bookings</h1></div><p class="text-brand-textMuted">View and manage all bookings</p></div><a class="inline-flex items-center gap-2 px-4 py-2 bg-brand-primary text-white text-sm font-medium rounded-lg hover:bg-brand-primaryHover transition-colors shadow-sm self-start" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i>Back to Dashboard</a></div>

                <?php if ($message): ?><div class="rounded-lg border px-4 py-3 text-sm font-medium <?php echo $message_type === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800'; ?>" role="alert"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>

                <div class="bg-brand-surface rounded-lg shadow-sm border border-brand-border overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left text-sm whitespace-nowrap"><thead class="bg-brand-bg border-b border-brand-border"><tr><th class="px-6 py-4 font-semibold text-brand-text">ID</th><th class="px-6 py-4 font-semibold text-brand-text">Customer</th><th class="px-6 py-4 font-semibold text-brand-text">Provider</th><th class="px-6 py-4 font-semibold text-brand-text">Service</th><th class="px-6 py-4 font-semibold text-brand-text">Date</th><th class="px-6 py-4 font-semibold text-brand-text">Price</th><th class="px-6 py-4 font-semibold text-brand-text">Status</th><th class="px-6 py-4 font-semibold text-brand-text text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-brand-border">
                    <?php if (count($bookings) > 0): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <?php
                            $status_classes = ['pending' => 'bg-brand-warningBg text-yellow-800 border-yellow-200', 'confirmed' => 'bg-blue-100 text-blue-800 border-blue-200', 'completed' => 'bg-brand-successBg text-green-800 border-green-200', 'cancelled' => 'bg-brand-bg text-brand-textMuted border-brand-border'];
                            $status_class = $status_classes[$booking['status']] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                            ?>
                            <tr class="hover:bg-brand-bg/50 transition-colors"><td class="px-6 py-4 text-brand-textMuted"><?php echo (int) $booking['id']; ?></td><td class="px-6 py-4 font-medium text-brand-text"><?php echo htmlspecialchars($booking['customer_name']); ?></td><td class="px-6 py-4 text-brand-textMuted"><?php echo htmlspecialchars($booking['provider_name']); ?></td><td class="px-6 py-4 text-brand-textMuted"><?php echo htmlspecialchars($booking['service_title']); ?></td><td class="px-6 py-4 text-brand-textMuted"><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td><td class="px-6 py-4 font-medium text-brand-text">$<?php echo number_format($booking['total_price'], 2); ?></td><td class="px-6 py-4"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border <?php echo $status_class; ?>"><?php echo htmlspecialchars($booking['status']); ?></span></td><td class="px-6 py-4 text-right"><form method="POST" class="inline-flex items-center justify-end gap-2"><input type="hidden" name="booking_id" value="<?php echo (int) $booking['id']; ?>"><select name="status" class="text-sm rounded-lg border-brand-border text-brand-text py-1.5 pl-3 pr-8 focus:ring-brand-primary focus:border-brand-primary bg-brand-bg"><option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option><option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option><option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option><option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option></select><button type="submit" name="update_status" class="w-8 h-8 rounded-lg bg-brand-bg hover:bg-brand-border text-brand-primary flex items-center justify-center transition-colors" title="Update status"><i class="fa-solid fa-check"></i></button></form><a href="bookings.php?delete=1&id=<?php echo (int) $booking['id']; ?>" class="ml-2 inline-flex w-8 h-8 rounded-lg bg-brand-dangerBg hover:bg-red-200 text-red-600 items-center justify-center transition-colors" title="Delete booking" onclick="return confirm('Delete this booking?')"><i class="fa-regular fa-trash-can"></i></a></td></tr>
                        <?php endforeach; ?>
                    <?php else: ?><tr><td colspan="8" class="px-6 py-12 text-center text-brand-textMuted">No bookings found.</td></tr><?php endif; ?>
                </tbody></table></div></div>
            </div>
        </div>
    </main>
</body>
</html>