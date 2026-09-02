<?php
/**
 * Dabberha (دبرها) - Users Database Access Layer
 * Location: includes/db/users_db.php
 */

require_once __DIR__ . '/../../config/db.php';

function getUserById(int $id): ?array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}

function getUserByEmail(string $email, bool $active_only = false): ?array {
    global $pdo;
    $sql = "SELECT * FROM users WHERE email = ?";
    if ($active_only) {
        $sql .= " AND status = 'active'";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([trim($email)]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}

function getAllUsers(?string $role = null, ?int $limit = null, int $offset = 0): array {
    global $pdo;
    $sql = "SELECT * FROM users WHERE 1=1";
    $params = [];

    if (!empty($role)) {
        $sql .= " AND role = ?";
        $params[] = $role;
    }

    $sql .= " ORDER BY created_at DESC";

    if ($limit !== null) {
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createUser(array $data): int {
    global $pdo;
    $raw_password = (string)($data['password'] ?? '');
    $password_info = password_get_info($raw_password);
    $hashed_password = ($password_info['algo'] === 0) 
        ? password_hash($raw_password, PASSWORD_DEFAULT) 
        : $raw_password;

    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role, phone, address, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        trim($data['name']),
        trim($data['email']),
        $hashed_password,
        $data['role'] ?? 'customer',
        trim($data['phone'] ?? ''),
        trim($data['address'] ?? ''),
        $data['status'] ?? 'active'
    ]);
    return (int)$pdo->lastInsertId();
}

function updateUserStatus(int $id, string $status): bool {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $id]);
}

function updateUserProfile(int $id, array $data): bool {
    global $pdo;
    $fields = [];
    $params = [];

    if (isset($data['name'])) {
        $fields[] = "name = ?";
        $params[] = trim($data['name']);
    }
    if (isset($data['email'])) {
        $fields[] = "email = ?";
        $params[] = trim($data['email']);
    }
    if (isset($data['phone'])) {
        $fields[] = "phone = ?";
        $params[] = trim($data['phone']);
    }
    if (isset($data['address'])) {
        $fields[] = "address = ?";
        $params[] = trim($data['address']);
    }
    if (!empty($data['password'])) {
        $fields[] = "password = ?";
        $params[] = $data['password'];
    }

    if (empty($fields)) {
        return true;
    }

    $params[] = $id;
    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function deleteUser(int $id): bool {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$id]);
}

function getUsersCount(?string $role = null, ?string $status = null): int {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM users WHERE 1=1";
    $params = [];

    if (!empty($role)) {
        $sql .= " AND role = ?";
        $params[] = $role;
    }
    if (!empty($status)) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}
