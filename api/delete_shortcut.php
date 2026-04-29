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

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$id    = (int)($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid ID']); exit;
}

$stmt = $pdo->prepare("DELETE FROM shortcuts WHERE id=? AND user_id=?");
$stmt->execute([$id, userId()]);

echo json_encode(['success' => $stmt->rowCount() > 0]);
