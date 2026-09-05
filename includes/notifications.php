<?php
function addNotification($user_id, $message) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    return $stmt->execute([$user_id, $message]);
}

function getNotifications($user_id, $limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, (int) $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function markAsRead($notification_id, $user_id = null) {
    global $pdo;
    if ($user_id !== null) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        return $stmt->execute([(int)$notification_id, (int)$user_id]);
    }
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    return $stmt->execute([(int)$notification_id]);
}

function markAllAsRead($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    return $stmt->execute([(int)$user_id]);
}

function getUnreadCount($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([(int)$user_id]);
    return (int)$stmt->fetchColumn();
}

function formatNotificationMessage($message) {
    if (empty($message)) return '';
    
    // Convert legacy English patterns to Arabic
    if (preg_match('/^New booking request from (.+) for (\d{4}-\d{2}-\d{2})$/u', $message, $matches)) {
        return sprintf(__('New booking request from %s for %s'), $matches[1], $matches[2]);
    }
    if (preg_match('/^Your booking for (.+) has been marked as (.+)\.$/u', $message, $matches)) {
        return sprintf(__('Your booking for %s has been marked as %s.'), $matches[1], __($matches[2]));
    }
    if ($message === 'Your booking has been submitted successfully. Waiting for provider confirmation.') {
        return __('Your booking has been submitted successfully. Waiting for provider confirmation.');
    }
    
    return __($message);
}
?>