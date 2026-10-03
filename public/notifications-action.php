<?php
/**
 * Dabberha (دبرها) - Notifications Action & API Endpoint
 * Location: public/notifications-action.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notifications.php';

if (!isLoggedIn()) {
    if (isset($_GET['ajax']) || isset($_POST['ajax'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Unauthenticated']);
        exit;
    }
    header('Location: login.php');
    exit;
}

$user_id = getUserId();
$action = $_REQUEST['action'] ?? '';
$is_ajax = isset($_REQUEST['ajax']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($action === 'open' || $action === 'mark_read') {
    $notif_id = (int)($_REQUEST['id'] ?? 0);
    if ($notif_id > 0) {
        markAsRead($notif_id, $user_id);
    }
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'unread_count' => getUnreadCount($user_id)
        ]);
        exit;
    }
    $redirect = $_REQUEST['redirect'] ?? '';
    if (!empty($redirect)) {
        header("Location: $redirect");
        exit;
    }
} elseif ($action === 'mark_all_read') {
    markAllAsRead($user_id);
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'unread_count' => 0
        ]);
        exit;
    }
} elseif ($action === 'get_notifications') {
    $raw_notifs = getNotifications($user_id, 10);
    $formatted = [];
    foreach ($raw_notifs as $n) {
        $formatted[] = getNotificationDetails($n);
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'unread_count' => getUnreadCount($user_id),
        'notifications' => $formatted
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: $referer");
exit;
