<?php
$host = '127.0.0.1';
$port = '3307';
$db   = 'local_services_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Auto-migrate users table with geolocation columns if missing
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('latitude', $cols, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN latitude DECIMAL(10,8) NULL DEFAULT NULL");
        }
        if (!in_array('longitude', $cols, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN longitude DECIMAL(11,8) NULL DEFAULT NULL");
        }
        if (!in_array('city', $cols, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN city VARCHAR(100) NULL DEFAULT NULL");
        }
    } catch (Exception $mig_err) {
        // Table might not exist yet during initial setup
    }
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>