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

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$amount = (float)($input['amount'] ?? 0);

if ($amount < 100) {
    echo json_encode(['success' => false, 'error' => 'Budget must be at least ₹100']); exit;
}

$userId = userId();
$month  = date('Y-m');

$stmt = $pdo->prepare("
    INSERT INTO budgets (user_id, amount, month) VALUES (?,?,?)
    ON DUPLICATE KEY UPDATE amount=VALUES(amount), updated_at=CURRENT_TIMESTAMP
");
$stmt->execute([$userId, $amount, $month]);

echo json_encode(['success' => true, 'amount' => $amount]);
