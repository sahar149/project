<?php
/**
 * Dabberha (دبرها) - Categories Database Access Layer
 * Location: includes/db/categories_db.php
 */

require_once __DIR__ . '/../../config/db.php';

function getAllCategories(): array {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategoryById(int $id): ?array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}

function createCategory(string $name, string $icon = ''): bool {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO categories (name, icon) VALUES (?, ?)");
    return $stmt->execute([trim($name), trim($icon)]);
}

function deleteCategory(int $id): bool {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    return $stmt->execute([$id]);
}

function getCategoriesCount(): int {
    global $pdo;
    return (int) $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
}
