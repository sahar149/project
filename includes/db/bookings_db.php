<?php
/**
 * Dabberha (دبرها) - Bookings Database Access Layer
 * Location: includes/db/bookings_db.php
 */

require_once __DIR__ . '/../../config/db.php';

function getBookingById(int $id, ?int $user_id = null, ?string $role = null): ?array {
    global $pdo;

    $sql = "SELECT b.*, 
                   s.title AS service_title, 
                   s.price_type,
                   c.name AS category_name,
                   c.icon AS category_icon,
                   u_cust.name AS customer_name,
                   u_cust.phone AS customer_phone,
                   u_cust.email AS customer_email,
                   u_cust.address AS customer_address,
                   u_prov.name AS provider_name,
                   u_prov.phone AS provider_phone,
                   u_prov.email AS provider_email,
                   u_prov.address AS provider_address,
                   (SELECT id FROM reviews WHERE booking_id = b.id LIMIT 1) AS review_id,
                   (SELECT rating FROM reviews WHERE booking_id = b.id LIMIT 1) AS review_rating
            FROM bookings b
            JOIN services s ON b.service_id = s.id
            LEFT JOIN categories c ON s.category_id = c.id
            JOIN users u_cust ON b.customer_id = u_cust.id
            JOIN users u_prov ON b.provider_id = u_prov.id
            WHERE b.id = ?";

    $params = [$id];

    if ($user_id !== null && $role === 'customer') {
        $sql .= " AND b.customer_id = ?";
        $params[] = $user_id;
    } elseif ($user_id !== null && $role === 'provider') {
        $sql .= " AND b.provider_id = ?";
        $params[] = $user_id;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}

function getCustomerBookings(int $customer_id, ?string $status = null): array {
    global $pdo;
    $sql = "SELECT b.*,
                   s.title AS service_title,
                   s.price_type,
                   c.name AS category_name,
                   c.icon AS category_icon,
                   u.name AS provider_name,
                   u.phone AS provider_phone,
                   u.email AS provider_email,
                   (SELECT id FROM reviews WHERE booking_id = b.id LIMIT 1) AS has_review
            FROM bookings b
            JOIN services s ON b.service_id = s.id
            LEFT JOIN categories c ON s.category_id = c.id
            JOIN users u ON b.provider_id = u.id
            WHERE b.customer_id = ?";
    
    $params = [$customer_id];

    if (!empty($status)) {
        $sql .= " AND b.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY b.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProviderBookings(int $provider_id, ?string $status = null, ?int $limit = null): array {
    global $pdo;
    $sql = "SELECT b.*,
                   s.title AS service_title,
                   s.price_type,
                   u.name AS customer_name,
                   u.phone AS customer_phone,
                   u.email AS customer_email,
                   u.address AS customer_address
            FROM bookings b
            JOIN services s ON b.service_id = s.id
            JOIN users u ON b.customer_id = u.id
            WHERE b.provider_id = ?";

    $params = [$provider_id];

    if (!empty($status)) {
        $sql .= " AND b.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY b.created_at DESC";

    if ($limit !== null) {
        $sql .= " LIMIT " . (int)$limit;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllBookings(?string $status = null, ?int $limit = null, int $offset = 0): array {
    global $pdo;
    $sql = "SELECT b.*,
                   s.title AS service_title,
                   u_cust.name AS customer_name,
                   u_prov.name AS provider_name
            FROM bookings b
            JOIN services s ON b.service_id = s.id
            JOIN users u_cust ON b.customer_id = u_cust.id
            JOIN users u_prov ON b.provider_id = u_prov.id
            WHERE 1=1";

    $params = [];

    if (!empty($status)) {
        $sql .= " AND b.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY b.created_at DESC";

    if ($limit !== null) {
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createBooking(array $data): int {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO bookings (customer_id, provider_id, service_id, booking_date, booking_time, total_price, notes, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([
        (int)$data['customer_id'],
        (int)$data['provider_id'],
        (int)$data['service_id'],
        $data['booking_date'],
        $data['booking_time'],
        (float)$data['total_price'],
        trim($data['notes'] ?? '')
    ]);
    return (int)$pdo->lastInsertId();
}

function updateBookingStatus(int $id, string $status, ?int $provider_id = null): bool {
    global $pdo;
    $sql = "UPDATE bookings SET status = ? WHERE id = ?";
    $params = [$status, $id];

    if ($provider_id !== null) {
        $sql .= " AND provider_id = ?";
        $params[] = $provider_id;
    }

    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function deleteBooking(int $id): bool {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    return $stmt->execute([$id]);
}

function getBookingsCount(?int $provider_id = null, ?string $status = null): int {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM bookings WHERE 1=1";
    $params = [];

    if ($provider_id !== null) {
        $sql .= " AND provider_id = ?";
        $params[] = $provider_id;
    }

    if (!empty($status)) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

function getProviderEarnings(int $provider_id): float {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE provider_id = ? AND status = 'completed'");
    $stmt->execute([$provider_id]);
    return (float)$stmt->fetchColumn();
}
