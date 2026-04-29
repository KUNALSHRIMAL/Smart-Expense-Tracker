<?php
define('API_REQUEST', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireLogin();
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$userId = userId();
$month  = $_GET['month'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $month)) $month = date('Y-m');

$stmt = $pdo->prepare("
    SELECT title, amount, category, expense_date, notes, created_at
    FROM expenses
    WHERE user_id=? AND DATE_FORMAT(expense_date,'%Y-%m')=?
    ORDER BY expense_date DESC, created_at DESC
");
$stmt->execute([$userId, $month]);
$rows = $stmt->fetchAll();

$budget = getBudget($pdo, $userId, $month);
$total  = array_sum(array_column($rows, 'amount'));

$filename = 'expenses-' . $month . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');
// BOM for Excel UTF-8
fwrite($out, "\xEF\xBB\xBF");

fputcsv($out, ['Smart Expense Tracker Export - ' . monthLabel($month)]);
fputcsv($out, ['Monthly Budget:', '₹' . number_format($budget['amount'], 2)]);
fputcsv($out, ['Total Spent:', '₹' . number_format($total, 2)]);
fputcsv($out, ['Remaining:', '₹' . number_format($budget['amount'] - $total, 2)]);
fputcsv($out, []);
fputcsv($out, ['Date', 'Title', 'Category', 'Amount (₹)', 'Notes']);

foreach ($rows as $row) {
    fputcsv($out, [
        $row['expense_date'],
        $row['title'],
        $row['category'],
        number_format((float)$row['amount'], 2),
        $row['notes'] ?? '',
    ]);
}

fputcsv($out, []);
fputcsv($out, ['', '', 'TOTAL', number_format($total, 2), '']);
fclose($out);
