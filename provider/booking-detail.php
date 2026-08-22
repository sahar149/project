<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('provider');

$provider_id = getUserId();
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($booking_id == 0) {
    header('Location: bookings.php');
    exit;
}

// جلب تفاصيل الحجز
$stmt = $pdo->prepare("
    SELECT b.*, s.title as service_title, s.price as service_price,
           u.name as customer_name, u.phone as customer_phone, u.email as customer_email
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.customer_id = u.id
    WHERE b.id = ? AND b.provider_id = ?
");
$stmt->execute([$booking_id, $provider_id]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: bookings.php');
    exit;
}

// معالجة تحديث الحالة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    $allowed_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ? AND provider_id = ?");
        $stmt->execute([$new_status, $booking_id, $provider_id]);
        header("Location: booking-detail.php?id=$booking_id&success=1");
        exit;
    }
}

$success = isset($_GET['success']) ? true : false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - Provider Dashboard</title>
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: '#CB6D51',
                        primaryHover: '#b55a40',
                        surfaceBg: '#F9F5F1',
                        surfaceWhite: '#ffffff',
                    },
                    borderRadius: {
                        card: '1rem',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .text-terracotta {
            color: #CB6D51;
        }

        .border-terracotta {
            border-color: #CB6D51;
        }
    </style>
</head>
<body class="font-sans bg-surfaceBg text-gray-800 antialiased min-h-screen flex flex-col">
    <?php include __DIR__ . '/header.php'; ?>

    <div class="flex-grow flex max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 gap-8">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="flex-1 min-w-0 overflow-y-auto">
            <div class="max-w-4xl mx-auto">
                <div class="mb-6 flex items-center justify-between">
                    <a class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-900 font-medium transition-colors bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-100 text-sm" href="bookings.php">
                        <i class="fa-solid fa-arrow-left text-sm"></i>
                        Back to Bookings
                    </a>
                </div>

                <?php if ($success): ?>
                    <div class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                        <i class="fa-solid fa-circle-check"></i>
                        Booking status updated successfully!
                    </div>
                <?php endif; ?>

                <section class="overflow-hidden rounded-card border border-gray-100 bg-surfaceWhite shadow-sm">
                    <div class="flex items-center gap-3 border-b border-gray-100 p-6 sm:p-8">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-50 text-primary">
                            <i class="fa-regular fa-calendar-check"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">Booking Details</h1>
                    </div>

                    <div class="space-y-8 p-6 sm:p-8">
                        <div class="grid grid-cols-1 gap-x-12 gap-y-6 md:grid-cols-2">
                            <div class="space-y-1">
                                <span class="text-sm font-medium text-gray-500">Booking ID</span>
                                <p class="text-base font-semibold text-gray-900">#<?php echo (int) $booking['id']; ?></p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-sm font-medium text-gray-500">Service</span>
                                <p class="text-base font-semibold capitalize text-gray-900"><?php echo htmlspecialchars($booking['service_title']); ?></p>
                            </div>
                            <div class="space-y-1">
                                <span class="mb-1 block text-sm font-medium text-gray-500">Status</span>
                                <?php
                                $status_classes = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                ];
                                $status_class = $status_classes[$booking['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold <?php echo $status_class; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            <div class="space-y-1">
                                <span class="text-sm font-medium text-gray-500">Price</span>
                                <p class="text-base font-semibold text-gray-900">$<?php echo number_format($booking['total_price'], 2); ?></p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-sm font-medium text-gray-500">Date</span>
                                <p class="text-base font-semibold text-gray-900"><?php echo date('F d, Y', strtotime($booking['booking_date'])); ?></p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-sm font-medium text-gray-500">Booked on</span>
                                <p class="text-base font-semibold text-gray-900"><?php echo date('F d, Y h:i A', strtotime($booking['created_at'])); ?></p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-sm font-medium text-gray-500">Time</span>
                                <p class="text-base font-semibold text-gray-900"><?php echo date('h:i A', strtotime($booking['booking_time'])); ?></p>
                            </div>
                        </div>

                        <?php if (!empty($booking['notes'])): ?>
                            <hr class="border-gray-100">
                            <div>
                                <h2 class="mb-2 text-sm font-semibold text-gray-900">Customer Notes</h2>
                                <p class="whitespace-pre-line text-sm text-gray-600"><?php echo htmlspecialchars($booking['notes']); ?></p>
                            </div>
                        <?php endif; ?>

                        <hr class="border-gray-100">
                        <div>
                            <h2 class="mb-4 flex items-center gap-2 text-lg font-bold text-gray-900">
                                <i class="fa-regular fa-user text-gray-400"></i>
                                Customer Information
                            </h2>
                            <div class="grid grid-cols-1 gap-4 rounded-xl border border-gray-100 bg-gray-50/50 p-5 sm:grid-cols-3">
                                <div>
                                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500">Name</span>
                                    <p class="mt-1 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($booking['customer_name']); ?></p>
                                </div>
                                <div>
                                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500">Phone</span>
                                    <p class="mt-1 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($booking['customer_phone'] ?? 'Not provided'); ?></p>
                                </div>
                                <div>
                                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500">Email</span>
                                    <p class="mt-1 break-words text-sm font-medium text-gray-900"><?php echo htmlspecialchars($booking['customer_email']); ?></p>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-100">
                        <div>
                            <h2 class="mb-4 flex items-center gap-2 text-lg font-bold text-gray-900">
                                <i class="fa-solid fa-arrows-rotate text-gray-400"></i>
                                Update Status
                            </h2>
                            <form method="POST" class="flex flex-col items-center gap-4 sm:flex-row">
                                <div class="w-full sm:w-2/3">
                                    <select name="status" class="block w-full rounded-lg border-gray-300 bg-white py-2.5 pl-4 pr-10 text-gray-900 shadow-sm transition-shadow focus:border-primary focus:outline-none focus:ring-primary sm:text-sm">
                                        <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-transparent bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primaryHover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-1/3" type="submit">
                                    <i class="fa-solid fa-check text-sm"></i>
                                    Update Status
                                </button>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>