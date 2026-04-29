<?php
define('API_REQUEST', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireLogin();
require_once dirname(__DIR__) . '/includes/db.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']); exit;
}

$input   = json_decode(file_get_contents('php://input'), true) ?? [];
$current = $input['current']      ?? '';
$newPw   = $input['new_password'] ?? '';

if ($current === '' || $newPw === '') {
    echo json_encode(['success' => false, 'error' => 'All fields required']); exit;
}
if (strlen($newPw) < 4) {
    echo json_encode(['success' => false, 'error' => 'New password must be at least 4 characters']); exit;
}

$userId = userId();
$stmt   = $pdo->prepare("SELECT password FROM users WHERE id=?");
$stmt->execute([$userId]);
$user   = $stmt->fetch();

if (!$user || !password_verify($current, $user['password'])) {
    echo json_encode(['success' => false, 'error' => 'Current password is incorrect']); exit;
}

$hash = password_hash($newPw, PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $userId]);

echo json_encode(['success' => true]);
