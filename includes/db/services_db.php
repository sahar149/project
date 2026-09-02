<?php
/**
 * Dabberha (دبرها) - Services Database Access Layer
 * Location: includes/db/services_db.php
 */

require_once __DIR__ . '/../../config/db.php';

function getAllServices(array $filters = [], ?int $limit = null, int $offset = 0): array {
    global $pdo;

    $sql = "SELECT s.*, 
                   u.name AS provider_name, 
                   u.phone AS provider_phone,
                   u.email AS provider_email,
                   u.status AS provider_status,
                   c.name AS category_name,
                   c.icon AS category_icon,
                   COALESCE(ROUND(AVG(r.rating), 1), 0) AS avg_rating,
                   COUNT(DISTINCT r.id) AS review_count
            FROM services s
            JOIN users u ON s.provider_id = u.id
            LEFT JOIN categories c ON s.category_id = c.id
            LEFT JOIN reviews r ON r.service_id = s.id
            WHERE 1=1";
    
    $params = [];

    if (!empty($filters['only_active_providers'])) {
        $sql .= " AND u.status = 'active'";
    }

    if (!empty($filters['category_id'])) {
        $sql .= " AND s.category_id = ?";
        $params[] = (int)$filters['category_id'];
    }

    if (!empty($filters['provider_id'])) {
        $sql .= " AND s.provider_id = ?";
        $params[] = (int)$filters['provider_id'];
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (s.title LIKE ? OR s.description LIKE ?)";
        $search_term = '%' . $filters['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
    }

    if (!empty($filters['price_type'])) {
        $sql .= " AND s.price_type = ?";
        $params[] = $filters['price_type'];
    }

    $sql .= " GROUP BY s.id ORDER BY s.created_at DESC";

    if ($limit !== null) {
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getServiceById(int $id, bool $only_active = false): ?array {
    global $pdo;

    $sql = "SELECT s.*, 
                   u.name AS provider_name, 
                   u.phone AS provider_phone, 
                   u.email AS provider_email,
                   u.address AS provider_address, 
                   u.status AS provider_status,
                   c.name AS category_name,
                   c.icon AS category_icon,
                   COALESCE(ROUND(AVG(r.rating), 1), 0) AS avg_rating, 
                   COUNT(DISTINCT r.id) AS review_count
            FROM services s
            JOIN users u ON s.provider_id = u.id
            LEFT JOIN categories c ON s.category_id = c.id
            LEFT JOIN reviews r ON r.service_id = s.id
            WHERE s.id = ?";

    $params = [$id];

    if ($only_active) {
        $sql .= " AND u.status = 'active'";
    }

    $sql .= " GROUP BY s.id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}

function getServicesByProvider(int $provider_id): array {
    return getAllServices(['provider_id' => $provider_id]);
}

function createService(array $data): int {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO services (provider_id, category_id, title, description, price, price_type)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        (int)$data['provider_id'],
        !empty($data['category_id']) ? (int)$data['category_id'] : null,
        trim($data['title']),
        trim($data['description'] ?? ''),
        (float)$data['price'],
        $data['price_type'] ?? 'fixed'
    ]);
    return (int)$pdo->lastInsertId();
}

function updateService(int $id, ?int $provider_id, array $data): bool {
    global $pdo;
    $sql = "UPDATE services SET category_id = ?, title = ?, description = ?, price = ?, price_type = ? WHERE id = ?";
    $params = [
        !empty($data['category_id']) ? (int)$data['category_id'] : null,
        trim($data['title']),
        trim($data['description'] ?? ''),
        (float)$data['price'],
        $data['price_type'] ?? 'fixed',
        $id
    ];

    if ($provider_id !== null) {
        $sql .= " AND provider_id = ?";
        $params[] = $provider_id;
    }

    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function deleteService(int $id, ?int $provider_id = null): bool {
    global $pdo;
    $sql = "DELETE FROM services WHERE id = ?";
    $params = [$id];

    if ($provider_id !== null) {
        $sql .= " AND provider_id = ?";
        $params[] = $provider_id;
    }

    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function getServicesCount(?int $provider_id = null): int {
    global $pdo;
    if ($provider_id !== null) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM services WHERE provider_id = ?");
        $stmt->execute([$provider_id]);
        return (int)$stmt->fetchColumn();
    }
    return (int)$pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
}
