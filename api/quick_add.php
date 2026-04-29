<?php
define('API_REQUEST', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireLogin();
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']); exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$scId  = (int)($input['shortcut_id'] ?? 0);

if ($scId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid shortcut']); exit;
}

$userId = userId();
$stmt = $pdo->prepare("SELECT * FROM shortcuts WHERE id=? AND user_id=?");
$stmt->execute([$scId, $userId]);
$sc = $stmt->fetch();

if (!$sc) {
    echo json_encode(['success' => false, 'error' => 'Shortcut not found']); exit;
}

$stmt = $pdo->prepare("INSERT INTO expenses (user_id,title,amount,category,expense_date,notes) VALUES (?,?,?,?,CURDATE(),?)");
$stmt->execute([$userId, $sc['title'], $sc['amount'], $sc['category'], 'Quick add']);

echo json_encode(['success' => true, 'message' => $sc['title'] . ' added!', 'id' => (int)$pdo->lastInsertId()]);
