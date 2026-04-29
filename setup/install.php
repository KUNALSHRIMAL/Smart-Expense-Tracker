<?php
/**
 * Database installer — run once at: http://localhost/money/setup/install.php
 * Delete or restrict this file after setup.
 */

$host   = 'localhost';
$user   = 'root';
$pass   = '';
$dbname = 'expense_tracker';

$messages = [];
$success  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Connect without selecting DB first
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbname`");
        $messages[] = ['ok', "Database '$dbname' ready."];

        // users table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            username   VARCHAR(50)  UNIQUE NOT NULL,
            password   VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");
        $messages[] = ['ok', 'Table: users'];

        // budgets table
        $pdo->exec("CREATE TABLE IF NOT EXISTS budgets (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            user_id    INT NOT NULL,
            amount     DECIMAL(10,2) NOT NULL DEFAULT 12000.00,
            month      VARCHAR(7)    NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY uk_user_month (user_id, month)
        ) ENGINE=InnoDB");
        $messages[] = ['ok', 'Table: budgets'];

        // expenses table
        $pdo->exec("CREATE TABLE IF NOT EXISTS expenses (
            id           INT AUTO_INCREMENT PRIMARY KEY,
            user_id      INT NOT NULL,
            title        VARCHAR(255) NOT NULL,
            amount       DECIMAL(10,2) NOT NULL,
            category     ENUM('Grocery','Milk','Fuel','Food','Bills','Medical','Other') NOT NULL DEFAULT 'Other',
            expense_date DATE NOT NULL,
            notes        TEXT,
            created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_date (user_id, expense_date)
        ) ENGINE=InnoDB");
        $messages[] = ['ok', 'Table: expenses'];

        // shortcuts table
        $pdo->exec("CREATE TABLE IF NOT EXISTS shortcuts (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            user_id    INT NOT NULL,
            title      VARCHAR(100)  NOT NULL,
            amount     DECIMAL(10,2) NOT NULL,
            category   ENUM('Grocery','Milk','Fuel','Food','Bills','Medical','Other') NOT NULL DEFAULT 'Other',
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB");
        $messages[] = ['ok', 'Table: shortcuts'];

        // Insert default admin user (username: admin, password: 1234)
        $hash = password_hash('1234', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password) VALUES ('admin', ?)");
        $stmt->execute([$hash]);

        if ($stmt->rowCount() > 0) {
            $messages[] = ['ok', 'Default user created — username: <strong>admin</strong> / password: <strong>1234</strong>'];
        } else {
            $messages[] = ['info', 'User "admin" already exists (skipped).'];
        }

        // Insert default shortcuts for the admin
        $adminStmt = $pdo->prepare("SELECT id FROM users WHERE username='admin'");
        $adminStmt->execute();
        $adminId = (int)$adminStmt->fetchColumn();

        $defaultShortcuts = [
            ['Milk',   50,  'Milk'],
            ['Tea',    20,  'Food'],
            ['Petrol', 200, 'Fuel'],
        ];
        $scStmt = $pdo->prepare("INSERT IGNORE INTO shortcuts (user_id,title,amount,category,sort_order) VALUES (?,?,?,?,?)");
        foreach ($defaultShortcuts as $i => $sc) {
            $scStmt->execute([$adminId, $sc[0], $sc[1], $sc[2], $i]);
        }
        $messages[] = ['ok', 'Default shortcuts seeded (Milk, Tea, Petrol).'];

        // Default budget for current month
        $month = date('Y-m');
        $bStmt = $pdo->prepare("INSERT IGNORE INTO budgets (user_id,amount,month) VALUES (?,12000.00,?)");
        $bStmt->execute([$adminId, $month]);
        $messages[] = ['ok', 'Default budget set: ₹12,000 for ' . date('F Y') . '.'];

        $success = true;
        $messages[] = ['ok', '✅ Installation complete!'];

    } catch (PDOException $e) {
        $messages[] = ['err', 'Error: ' . htmlspecialchars($e->getMessage())];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Install — Smart Expense Tracker</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:#080808;color:#fff;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}
    .card{background:#1c1c1e;border-radius:24px;padding:32px;width:100%;max-width:480px}
    h1{font-size:24px;font-weight:700;margin-bottom:6px}
    p.sub{color:#8e8e93;font-size:14px;margin-bottom:28px}
    .msg{padding:10px 14px;border-radius:12px;font-size:13px;margin-bottom:8px}
    .msg.ok  {background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.25);color:#6ee7b7}
    .msg.err {background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);color:#fca5a5}
    .msg.info{background:rgba(139,92,246,.12);border:1px solid rgba(139,92,246,.25);color:#c4b5fd}
    button{width:100%;background:linear-gradient(135deg,#a78bfa,#7c3aed);color:#fff;border:none;border-radius:16px;padding:16px;font-size:16px;font-weight:600;cursor:pointer;margin-top:16px}
    a.go{display:block;text-align:center;background:#2c2c2e;color:#a78bfa;border-radius:16px;padding:14px;font-weight:600;text-decoration:none;margin-top:12px}
    .warn{background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.25);color:#fcd34d;padding:12px 16px;border-radius:14px;font-size:13px;margin-bottom:20px}
  </style>
</head>
<body>
<div class="card">
  <h1>⚙️ Setup Installer</h1>
  <p class="sub">Smart Expense Tracker · First-time setup</p>

  <?php if (!$success): ?>
  <div class="warn">⚠️ After installing, <strong>delete or protect</strong> this file. It contains DB credentials.</div>
  <?php endif; ?>

  <?php foreach ($messages as [$type, $msg]): ?>
  <div class="msg <?= $type ?>"><?= $msg ?></div>
  <?php endforeach; ?>

  <?php if ($success): ?>
  <a class="go" href="/money/login.php">→ Go to Login</a>
  <?php else: ?>
  <form method="POST">
    <button type="submit">Run Installation</button>
  </form>
  <?php endif; ?>
</div>
</body>
</html>
