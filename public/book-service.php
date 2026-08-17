<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

// يجب أن يكون المستخدم مسجل دخوله وكاستمر
requireRole('customer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: browse-services.php');
    exit;
}

$service_id = (int)$_POST['service_id'];
$provider_id = (int)$_POST['provider_id'];
$booking_date = $_POST['booking_date'];
$booking_time = $_POST['booking_time'];
$notes = trim($_POST['notes'] ?? '');
$customer_id = getUserId();

// التحقق من صحة التاريخ
if (empty($booking_date) || empty($booking_time)) {
    header('Location: service-detail.php?id=' . $service_id . '&error=invalid_date');
    exit;
}

// جلب سعر الخدمة
$stmt = $pdo->prepare("SELECT price FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    header('Location: browse-services.php');
    exit;
}

// إدخال الحجز
$stmt = $pdo->prepare("
    INSERT INTO bookings (customer_id, provider_id, service_id, booking_date, booking_time, total_price, notes)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$result = $stmt->execute([
    $customer_id,
    $provider_id,
    $service_id,
    $booking_date,
    $booking_time,
    $service['price'],
    $notes
]);

if ($result) {
    $booking_id = $pdo->lastInsertId();

    require_once __DIR__ . '/../includes/notifications.php';

    // إشعار لمقدم الخدمة
    addNotification($provider_id, "New booking request from " . getUserName() . " for " . date('Y-m-d', strtotime($booking_date)));

    // إشعار للعميل
    addNotification($customer_id, "Your booking has been submitted successfully. Waiting for provider confirmation.");

    header("Location: booking-confirmation.php?id=$booking_id");
    exit;
} else {
    header('Location: service-detail.php?id=' . $service_id . '&error=booking_failed');
    exit;
}
?>