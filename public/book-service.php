<?php
/**
 * Dabberha (دبرها) - Book Service Processing
 * Location: public/book-service.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notifications.php';
require_once __DIR__ . '/../includes/translations.php';
require_once __DIR__ . '/../includes/db/services_db.php';
require_once __DIR__ . '/../includes/db/bookings_db.php';

requireRole('customer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: browse-services.php');
    exit;
}

$service_id = (int)($_POST['service_id'] ?? 0);
$provider_id = (int)($_POST['provider_id'] ?? 0);
$booking_date = $_POST['booking_date'] ?? '';
$booking_time = $_POST['booking_time'] ?? '';
$notes = trim($_POST['notes'] ?? '');
$customer_id = getUserId();

if (empty($booking_date) || empty($booking_time) || $service_id <= 0 || $provider_id <= 0) {
    header('Location: service-detail.php?id=' . $service_id . '&error=invalid_date');
    exit;
}

$service = getServiceById($service_id);

if (!$service) {
    header('Location: browse-services.php');
    exit;
}

$booking_id = createBooking([
    'customer_id' => $customer_id,
    'provider_id' => $provider_id,
    'service_id' => $service_id,
    'booking_date' => $booking_date,
    'booking_time' => $booking_time,
    'total_price' => (float)$service['price'],
    'notes' => $notes
]);

if ($booking_id > 0) {
    // Notification for provider
    addNotification($provider_id, sprintf(__('New booking request from %s for %s'), getUserName(), date('Y-m-d', strtotime($booking_date))));

    // Notification for customer
    addNotification($customer_id, __('Your booking has been submitted successfully. Waiting for provider confirmation.'));

    header("Location: booking-confirmation.php?id=$booking_id");
    exit;
} else {
    header('Location: service-detail.php?id=' . $service_id . '&error=booking_failed');
    exit;
}