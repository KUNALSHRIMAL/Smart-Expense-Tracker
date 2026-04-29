<?php
function getCategoryIcon(string $category): string {
    global $CATEGORIES;
    return $CATEGORIES[$category] ?? '📦';
}

function formatMoney(float $amount): string {
    return '₹' . number_format($amount, 2);
}

function formatMoneyShort(float $amount): string {
    return '₹' . number_format($amount, 0);
}

// Get (or auto-create) budget for given month
function getBudget(PDO $pdo, int $userId, string $month = ''): array {
    if (!$month) $month = date('Y-m');

    $stmt = $pdo->prepare("SELECT * FROM budgets WHERE user_id = ? AND month = ?");
    $stmt->execute([$userId, $month]);
    $budget = $stmt->fetch();

    if (!$budget) {
        // Inherit latest budget amount, or default ₹12,000
        $stmt2 = $pdo->prepare("SELECT amount FROM budgets WHERE user_id = ? ORDER BY month DESC LIMIT 1");
        $stmt2->execute([$userId]);
        $latest = $stmt2->fetch();
        $amount = $latest ? (float)$latest['amount'] : 12000.00;

        $stmt3 = $pdo->prepare("INSERT INTO budgets (user_id, amount, month) VALUES (?, ?, ?)");
        $stmt3->execute([$userId, $amount, $month]);

        return ['id' => (int)$pdo->lastInsertId(), 'user_id' => $userId, 'amount' => $amount, 'month' => $month];
    }

    return $budget;
}

function getMonthSpent(PDO $pdo, int $userId, string $month = ''): float {
    if (!$month) $month = date('Y-m');
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) AS total FROM expenses WHERE user_id=? AND DATE_FORMAT(expense_date,'%Y-%m')=? AND is_credit=0");
    $stmt->execute([$userId, $month]);
    return (float)$stmt->fetch()['total'];
}

function getTodaySpent(PDO $pdo, int $userId): float {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) AS total FROM expenses WHERE user_id=? AND expense_date=CURDATE() AND is_credit=0");
    $stmt->execute([$userId]);
    return (float)$stmt->fetch()['total'];
}

function getRecentExpenses(PDO $pdo, int $userId, int $limit = 10): array {
    $stmt = $pdo->prepare("SELECT * FROM expenses WHERE user_id=? ORDER BY expense_date DESC, created_at DESC LIMIT ?");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

function getShortcuts(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("SELECT * FROM shortcuts WHERE user_id=? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getExpensesByMonth(PDO $pdo, int $userId, string $month): array {
    $stmt = $pdo->prepare("SELECT * FROM expenses WHERE user_id=? AND DATE_FORMAT(expense_date,'%Y-%m')=? ORDER BY expense_date DESC, created_at DESC");
    $stmt->execute([$userId, $month]);
    return $stmt->fetchAll();
}

function groupByDate(array $expenses): array {
    $grouped = [];
    foreach ($expenses as $exp) {
        $date = $exp['expense_date'];
        if (!isset($grouped[$date])) {
            $grouped[$date] = ['expenses' => [], 'total' => 0.0];
        }
        $grouped[$date]['expenses'][] = $exp;
        if (!$exp['is_credit']) $grouped[$date]['total'] += (float)$exp['amount'];
    }
    return $grouped;
}

function humanDate(string $date): string {
    $d    = new DateTime($date);
    $now  = new DateTime();
    $yest = new DateTime('yesterday');
    if ($d->format('Y-m-d') === $now->format('Y-m-d'))  return 'Today';
    if ($d->format('Y-m-d') === $yest->format('Y-m-d')) return 'Yesterday';
    return $d->format('D, d M Y');
}

function progressColor(float $percent): string {
    if ($percent >= 100) return '#ef4444';
    if ($percent >= 80)  return '#f97316';
    if ($percent >= 60)  return '#f59e0b';
    return '#10b981';
}

function getAvailableMonths(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("SELECT DISTINCT DATE_FORMAT(expense_date,'%Y-%m') AS month FROM expenses WHERE user_id=? ORDER BY month DESC LIMIT 24");
    $stmt->execute([$userId]);
    $months = array_column($stmt->fetchAll(), 'month');
    // Always include current month
    $current = date('Y-m');
    if (!in_array($current, $months)) array_unshift($months, $current);
    return $months;
}

function monthLabel(string $ym): string {
    return date('F Y', mktime(0, 0, 0, (int)substr($ym, 5, 2), 1, (int)substr($ym, 0, 4)));
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
