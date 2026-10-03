<?php
/**
 * Dabberha (دبرها) - Notifications Helper Layer
 * Location: includes/notifications.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/translations.php';

function addNotification($user_id, $message) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    return $stmt->execute([(int)$user_id, $message]);
}

function getNotifications($user_id, $limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bindValue(1, (int)$user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

/**
 * Format notification messages into pure Arabic strings
 */
function formatNotificationMessage($message) {
    if (empty($message)) return '';
    
    // Legacy English regex translation
    if (preg_match('/^New booking request from (.+) for (\d{4}-\d{2}-\d{2})$/u', $message, $matches)) {
        return "لديك طلب حجز جديد من العميل (" . $matches[1] . ") لتاريخ " . $matches[2] . ".";
    }
    if (preg_match('/^Your booking for (.+) has been marked as (.+)\.$/u', $message, $matches)) {
        $service_name = $matches[1];
        $st = trim($matches[2]);

        if (strpos($st, 'مؤكد') !== false || strpos($st, 'confirmed') !== false) {
            return "تمت الموافقة على طلبك لخدمة (" . $service_name . ") وتأكيد الحجز بنجاح.";
        } elseif (strpos($st, 'مكتمل') !== false || strpos($st, 'completed') !== false) {
            return "اكتملت خدمتك (" . $service_name . ") بنجاح! نرجو منك تقييم الخدمة لمساعدتنا في تحسين الجودة.";
        } elseif (strpos($st, 'ملغي') !== false || strpos($st, 'cancelled') !== false) {
            return "نعتذر منك، تعذر قبول طلبك لخدمة (" . $service_name . "). يمكنك تصفح واختيار مزود فني آخر.";
        } else {
            return "تم تحديث حالة طلبك لخدمة (" . $service_name . ") إلى: " . __($st);
        }
    }
    if ($message === 'Your booking has been submitted successfully. Waiting for provider confirmation.') {
        return 'تم إرسال طلب حجزك بنجاح، وهو الآن في انتظار موافقة مزود الخدمة.';
    }
    
    return __($message);
}

/**
 * Calculate human-readable Arabic relative time
 */
function timeAgoArabic($datetime) {
    if (empty($datetime)) return '';
    $timestamp = is_numeric($datetime) ? (int)$datetime : strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'الآن';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        if ($mins == 1) return 'منذ دقيقة';
        if ($mins == 2) return 'منذ دقيقتين';
        if ($mins >= 3 && $mins <= 10) return "منذ $mins دقائق";
        return "منذ $mins دقيقة";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        if ($hours == 1) return 'منذ ساعة';
        if ($hours == 2) return 'منذ ساعتين';
        if ($hours >= 3 && $hours <= 10) return "منذ $hours ساعات";
        return "منذ $hours ساعة";
    } elseif ($diff < 172800) {
        return 'أمس في ' . date('H:i', $timestamp);
    } else {
        $days = floor($diff / 86400);
        if ($days <= 10) return "منذ $days أيام";
        return date('Y-m-d', $timestamp);
    }
}

/**
 * Parse notification and extract icon, badge color, and contextual action link
 */
function getNotificationDetails(array $notification): array {
    $raw_msg = $notification['message'] ?? '';
    $formatted_msg = formatNotificationMessage($raw_msg);
    $is_read = !empty($notification['is_read']);
    $time_ago = timeAgoArabic($notification['created_at'] ?? '');

    // Default values
    $type = 'info';
    $icon = 'fa-solid fa-bell';
    $icon_color = 'text-brand-primary';
    $icon_bg = 'bg-brand-primaryLight';
    $action_url = '/local-services-platform/public/my-bookings.php';
    $action_label = 'عرض الحجز';

    // 1. Approval / Confirmed
    if (strpos($formatted_msg, 'الموافقة') !== false || strpos($formatted_msg, 'تأكيد') !== false || strpos($raw_msg, 'confirmed') !== false || strpos($raw_msg, 'مؤكد') !== false) {
        $type = 'success';
        $icon = 'fa-solid fa-circle-check';
        $icon_color = 'text-emerald-600';
        $icon_bg = 'bg-emerald-50';
        $action_url = '/local-services-platform/public/my-bookings.php';
        $action_label = 'عرض الحجز المؤكد';
    }
    // 2. Completed / Review Request
    elseif (strpos($formatted_msg, 'اكتملت') !== false || strpos($formatted_msg, 'تقييم') !== false || strpos($raw_msg, 'completed') !== false || strpos($raw_msg, 'مكتمل') !== false) {
        $type = 'review';
        $icon = 'fa-solid fa-star';
        $icon_color = 'text-amber-600';
        $icon_bg = 'bg-amber-50';
        $action_url = '/local-services-platform/public/my-bookings.php';
        $action_label = 'تقييم الخدمة ⭐';
    }
    // 3. Cancelled / Rejected
    elseif (strpos($formatted_msg, 'نعتذر') !== false || strpos($formatted_msg, 'تعذر') !== false || strpos($formatted_msg, 'ملغي') !== false || strpos($raw_msg, 'cancelled') !== false) {
        $type = 'cancelled';
        $icon = 'fa-solid fa-circle-xmark';
        $icon_color = 'text-rose-600';
        $icon_bg = 'bg-rose-50';
        $action_url = '/local-services-platform/public/browse-services.php';
        $action_label = 'تصفح مزودين آخرين 🔍';
    }
    // 4. Provider new booking request
    elseif (strpos($formatted_msg, 'طلب حجز جديد') !== false || strpos($raw_msg, 'New booking request') !== false) {
        $type = 'provider_new';
        $icon = 'fa-solid fa-calendar-plus';
        $icon_color = 'text-brand-primary';
        $icon_bg = 'bg-brand-primaryLight';
        $action_url = '/local-services-platform/provider/bookings.php';
        $action_label = 'مراجعة الطلب 📋';
    }

    return [
        'id' => (int)($notification['id'] ?? 0),
        'message' => $formatted_msg,
        'type' => $type,
        'icon' => $icon,
        'icon_color' => $icon_color,
        'icon_bg' => $icon_bg,
        'action_url' => $action_url,
        'action_label' => $action_label,
        'time_ago' => $time_ago,
        'is_read' => $is_read,
        'created_at' => $notification['created_at'] ?? ''
    ];
}
?>