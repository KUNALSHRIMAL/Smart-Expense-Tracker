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

$input    = json_decode(file_get_contents('php://input'), true) ?? [];
$title    = trim($input['title']    ?? '');
$amount   = (float)($input['amount']   ?? 0);
$category = trim($input['category'] ?? 'Other');

if ($title === '') {
    echo json_encode(['success' => false, 'error' => 'Title is required']); exit;
}
if ($amount <= 0) {
    echo json_encode(['success' => false, 'error' => 'Amount must be positive']); exit;
}

global $CATEGORIES;
if (!array_key_exists($category, $CATEGORIES)) $category = 'Other';

$userId = userId();

// Limit to 10 shortcuts
$count = $pdo->prepare("SELECT COUNT(*) FROM shortcuts WHERE user_id=?");
$count->execute([$userId]);
if ((int)$count->fetchColumn() >= 10) {
    echo json_encode(['success' => false, 'error' => 'Maximum 10 shortcuts allowed']); exit;
}

$stmt = $pdo->prepare("INSERT INTO shortcuts (user_id,title,amount,category) VALUES (?,?,?,?)");
$stmt->execute([$userId, $title, $amount, $category]);

echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);
