<?php
require_once dirname(__DIR__) . '/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
// Auto-add is_credit column if the table exists but the column doesn't yet
try {
    $r = $pdo->query("SHOW COLUMNS FROM expenses LIKE 'is_credit'");
    if ($r->rowCount() === 0) {
        $pdo->exec("ALTER TABLE expenses ADD COLUMN is_credit TINYINT(1) NOT NULL DEFAULT 0 AFTER notes");
    }
} catch (PDOException $e) { /* table may not exist yet during fresh install */ }

} catch (PDOException $e) {
    if (defined('API_REQUEST')) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit;
    }
    $setupUrl = BASE_URL . '/setup/install.php';
    die("
        <style>body{font-family:sans-serif;background:#0a0a0a;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;flex-direction:column;gap:16px;}</style>
        <h2>Database not connected</h2>
        <p style='color:#8e8e93'>Please run the installer first.</p>
        <a href='$setupUrl' style='background:#8b5cf6;color:#fff;padding:12px 24px;border-radius:12px;text-decoration:none;'>Run Setup</a>
    ");
}
