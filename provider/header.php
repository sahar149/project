<?php
/**
 * Dabberha (دبرها) - Provider Header Wrapper
 * Location: provider/header.php
 */

require_once __DIR__ . '/../includes/components/header_dashboard.php';
require_once __DIR__ . '/../includes/notifications.php';

$provider_id = getUserId();
$header_unread_count = isset($unread_count) ? (int)$unread_count : ($provider_id ? getUnreadCount($provider_id) : 0);

renderDashboardHeader([
    'title' => __('Provider Dashboard'),
    'subtitle' => __('Manage your services and bookings'),
    'role' => 'provider',
    'unread_count' => $header_unread_count,
    'user_name' => getUserName()
]);