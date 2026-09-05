<?php
/**
 * Dabberha (دبرها) - Reviews Database Access Layer
 * Location: includes/db/reviews_db.php
 */

require_once __DIR__ . '/../../config/db.php';

function getServiceReviews(int $service_id, int $limit = 5): array {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT r.*, u.name AS customer_name 
        FROM reviews r 
        JOIN users u ON r.customer_id = u.id 
        WHERE r.service_id = ? 
        ORDER BY r.created_at DESC 
        LIMIT ?
    ");
    $stmt->bindValue(1, $service_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProviderReviews(int $provider_id): array {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT r.*, 
               COALESCE(u.name, 'عميل') AS customer_name, 
               COALESCE(s.title, sb.title, 'خدمة منجزة') AS service_title 
        FROM reviews r 
        LEFT JOIN users u ON r.customer_id = u.id 
        LEFT JOIN services s ON r.service_id = s.id 
        LEFT JOIN bookings b ON r.booking_id = b.id
        LEFT JOIN services sb ON b.service_id = sb.id
        WHERE r.provider_id = ? 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$provider_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllReviews(): array {
    global $pdo;
    $stmt = $pdo->query("
        SELECT r.*, 
               COALESCE(u.name, 'عميل') AS customer_name, 
               COALESCE(s.title, sb.title, 'خدمة منجزة') AS service_title, 
               COALESCE(p.name, 'مزود خدمة') AS provider_name
        FROM reviews r
        LEFT JOIN users u ON r.customer_id = u.id
        LEFT JOIN services s ON r.service_id = s.id
        LEFT JOIN bookings b ON r.booking_id = b.id
        LEFT JOIN services sb ON b.service_id = sb.id
        LEFT JOIN users p ON r.provider_id = p.id
        ORDER BY r.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getReviewByBookingId(int $booking_id): ?array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE booking_id = ?");
    $stmt->execute([$booking_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}

function createReview(array $data): int {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO reviews (booking_id, customer_id, provider_id, service_id, rating, comment)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        (int)$data['booking_id'],
        (int)$data['customer_id'],
        (int)$data['provider_id'],
        (int)$data['service_id'],
        (int)$data['rating'],
        trim($data['comment'] ?? '')
    ]);
    return (int)$pdo->lastInsertId();
}

function deleteReview(int $id): bool {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    return $stmt->execute([$id]);
}

function getReviewsCount(?int $provider_id = null): int {
    global $pdo;
    if ($provider_id !== null) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE provider_id = ?");
        $stmt->execute([$provider_id]);
        return (int)$stmt->fetchColumn();
    }
    return (int)$pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
}

function getAverageRating(?int $provider_id = null, ?int $service_id = null): float {
    global $pdo;
    if ($provider_id !== null) {
        $stmt = $pdo->prepare("SELECT COALESCE(ROUND(AVG(rating), 1), 0) FROM reviews WHERE provider_id = ?");
        $stmt->execute([$provider_id]);
        return (float)$stmt->fetchColumn();
    }
    if ($service_id !== null) {
        $stmt = $pdo->prepare("SELECT COALESCE(ROUND(AVG(rating), 1), 0) FROM reviews WHERE service_id = ?");
        $stmt->execute([$service_id]);
        return (float)$stmt->fetchColumn();
    }
    return (float)$pdo->query("SELECT COALESCE(ROUND(AVG(rating), 1), 0) FROM reviews")->fetchColumn();
}
