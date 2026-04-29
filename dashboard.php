<?php
$activePage = 'dashboard';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$userId  = userId();
$month   = date('Y-m');
$budget  = getBudget($pdo, $userId, $month);
$spent   = getMonthSpent($pdo, $userId, $month);
$remaining = (float)$budget['amount'] - $spent;
$percent   = $budget['amount'] > 0 ? ($spent / $budget['amount']) * 100 : 0;
$todaySpent = getTodaySpent($pdo, $userId);
$shortcuts  = getShortcuts($pdo, $userId);
$recent     = getRecentExpenses($pdo, $userId, 15);

$hour = (int)date('H');
$greeting = $hour < 12 ? 'Morning' : ($hour < 17 ? 'Afternoon' : 'Evening');

// Flash message from redirects
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$barColor    = progressColor($percent);
$barWidth    = min($percent, 100);
$isOverspent = $remaining < 0;
$isLow       = !$isOverspent && $percent >= 80;
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-[#080808] text-white">

<main class="max-w-lg mx-auto px-4 pt-6 pb-nav">

  <!-- Header -->
  <div class="flex items-start justify-between mb-6 slide-up">
    <div>
      <p class="text-gray-500 text-sm font-medium"><?= date('F Y') ?></p>
      <h1 class="text-2xl font-bold tracking-tight">Good <?= $greeting ?>,</h1>
      <p class="text-xl font-bold tracking-tight text-violet-400"><?= e($_SESSION['username']) ?> 👋</p>
    </div>
    <a href="<?= BASE_URL ?>/logout.php"
       class="w-9 h-9 rounded-xl bg-surface flex items-center justify-center tap"
       title="Logout">
      <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
      </svg>
    </a>
  </div>

  <!-- Budget Card -->
  <div class="bg-surface rounded-3xl p-5 mb-4 slide-up">
    <div class="flex items-start justify-between mb-5">
      <div>
        <p class="text-gray-400 text-xs font-medium uppercase tracking-widest mb-1">Monthly Budget</p>
        <p class="text-3xl font-bold num"><?= formatMoney((float)$budget['amount']) ?></p>
      </div>
      <div class="text-right">
        <p class="text-gray-400 text-xs font-medium uppercase tracking-widest mb-1">Remaining</p>
        <p class="text-2xl font-bold num <?= $isOverspent ? 'text-red-400' : ($isLow ? 'text-amber-400' : 'text-emerald-400') ?>">
          <?= $isOverspent ? '-' : '' ?><?= formatMoney(abs($remaining)) ?>
        </p>
      </div>
    </div>

    <!-- Progress bar -->
    <div class="bg-surface2 rounded-full h-2.5 mb-3 overflow-hidden">
      <div class="h-full rounded-full bar" style="width:<?= $barWidth ?>%;background:<?= $barColor ?>"></div>
    </div>

    <div class="flex justify-between items-center text-sm">
      <span class="text-gray-400">Spent: <span class="text-white font-semibold num"><?= formatMoney($spent) ?></span></span>
      <span class="text-gray-500 num"><?= round($percent, 1) ?>% used</span>
    </div>
  </div>

  <!-- Alerts -->
  <?php if ($isOverspent): ?>
  <div class="bg-red-500/10 border border-red-500/25 rounded-2xl p-4 mb-4 flex items-center gap-3 slide-up">
    <span class="text-2xl">🚨</span>
    <div>
      <p class="font-semibold text-red-400 text-sm">Budget Exceeded!</p>
      <p class="text-xs text-gray-400 mt-0.5">You've overspent by <?= formatMoney(abs($remaining)) ?></p>
    </div>
  </div>
  <?php elseif ($isLow): ?>
  <div class="bg-amber-500/10 border border-amber-500/25 rounded-2xl p-4 mb-4 flex items-center gap-3 slide-up">
    <span class="text-2xl">⚠️</span>
    <div>
      <p class="font-semibold text-amber-400 text-sm">Low Balance</p>
      <p class="text-xs text-gray-400 mt-0.5">Only <?= formatMoney($remaining) ?> remaining this month</p>
    </div>
  </div>
  <?php endif; ?>

  <!-- Stat Cards -->
  <div class="grid grid-cols-2 gap-3 mb-6 slide-up-2">
    <div class="bg-surface rounded-2xl p-4">
      <p class="text-gray-400 text-xs uppercase tracking-wide mb-2">Today</p>
      <p class="text-xl font-bold num <?= $todaySpent > 0 ? 'text-violet-400' : 'text-gray-500' ?>">
        <?= formatMoney($todaySpent) ?>
      </p>
    </div>
    <div class="bg-surface rounded-2xl p-4">
      <p class="text-gray-400 text-xs uppercase tracking-wide mb-2">Transactions</p>
      <p class="text-xl font-bold num"><?= count($recent) ?></p>
    </div>
  </div>

  <!-- Quick Shortcuts -->
  <div class="mb-6 slide-up-2">
    <div class="flex items-center justify-between mb-3">
      <h2 class="font-semibold text-gray-200 text-sm uppercase tracking-wide">Quick Add</h2>
      <a href="<?= BASE_URL ?>/settings.php#shortcuts" class="text-xs text-violet-400 font-medium">Manage</a>
    </div>

    <?php if (empty($shortcuts)): ?>
    <a href="<?= BASE_URL ?>/settings.php#shortcuts"
       class="block bg-surface border border-dashed border-bdr rounded-2xl p-4 text-center text-gray-500 text-sm tap">
      + Add quick shortcuts
    </a>
    <?php else: ?>
    <div class="flex gap-2.5 scroll-x pb-1 -mx-4 px-4">
      <?php foreach ($shortcuts as $sc): ?>
      <button data-id="<?= $sc['id'] ?>"
              onclick="quickAdd(this)"
              class="flex-shrink-0 bg-surface2 border border-bdr rounded-2xl px-4 py-3.5 text-center tap min-w-[90px]">
        <p class="text-base mb-0.5"><?= getCategoryIcon($sc['category']) ?></p>
        <p class="text-sm font-medium text-gray-200 whitespace-nowrap"><?= e($sc['title']) ?></p>
        <p class="text-violet-400 font-bold text-sm num"><?= formatMoneyShort((float)$sc['amount']) ?></p>
      </button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Recent Transactions -->
  <div class="slide-up-3">
    <div class="flex items-center justify-between mb-3">
      <h2 class="font-semibold text-gray-200 text-sm uppercase tracking-wide">Recent</h2>
      <a href="<?= BASE_URL ?>/history.php" class="text-xs text-violet-400 font-medium">View All</a>
    </div>

    <?php if (empty($recent)): ?>
    <div class="text-center py-14 text-gray-600">
      <p class="text-5xl mb-4">💸</p>
      <p class="font-semibold text-gray-400">No expenses yet</p>
      <p class="text-sm mt-1">Tap <span class="text-violet-400">+</span> to record your first expense</p>
    </div>
    <?php else: ?>
    <div class="space-y-2" id="tx-list">
      <?php foreach ($recent as $exp): ?>
      <?php $credit = !empty($exp['is_credit']); ?>
      <div class="bg-surface rounded-2xl p-4 flex items-center gap-3 tap">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl flex-shrink-0
                    <?= $credit ? 'bg-emerald-500/15' : 'bg-surface2' ?>">
          <?= $credit ? '↗️' : getCategoryIcon($exp['category']) ?>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-medium text-sm leading-tight truncate"><?= e($exp['title']) ?></p>
          <p class="text-xs mt-0.5 <?= $credit ? 'text-emerald-500' : 'text-gray-500' ?>">
            <?= $credit ? 'Credit' : e($exp['category']) ?> · <?= humanDate($exp['expense_date']) ?>
          </p>
        </div>
        <p class="font-bold text-sm num flex-shrink-0 <?= $credit ? 'text-emerald-400' : 'text-red-400' ?>">
          <?= $credit ? '+' : '-' ?><?= formatMoneyShort((float)$exp['amount']) ?>
        </p>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

</main>

<!-- Toast -->
<div id="toast"
     class="toast fixed z-50 flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl"
     style="bottom:calc(88px + env(safe-area-inset-bottom,0px));left:1rem;right:1rem;max-width:360px;margin:0 auto;background:#2c2c2e;border:1px solid #3a3a3c">
  <span id="toast-icon" class="text-lg">✓</span>
  <p id="toast-msg" class="font-medium text-sm text-white"></p>
</div>

<?php require_once __DIR__ . '/includes/nav.php'; ?>

<script>
async function quickAdd(btn) {
  const id = btn.dataset.id;
  btn.style.opacity = '.5';
  btn.disabled = true;
  try {
    const res = await fetch('<?= BASE_URL ?>/api/quick_add.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({shortcut_id: parseInt(id)})
    });
    const data = await res.json();
    if (data.success) {
      showToast('✅', data.message || 'Added!');
      setTimeout(() => location.reload(), 1000);
    } else {
      showToast('❌', data.error || 'Failed');
      btn.style.opacity = '1';
      btn.disabled = false;
    }
  } catch {
    showToast('❌', 'Network error');
    btn.style.opacity = '1';
    btn.disabled = false;
  }
}

function showToast(icon, msg) {
  document.getElementById('toast-icon').textContent = icon;
  document.getElementById('toast-msg').textContent = msg;
  const t = document.getElementById('toast');
  t.classList.add('show');
  clearTimeout(window._toastTimer);
  window._toastTimer = setTimeout(() => t.classList.remove('show'), 2800);
}

<?php if ($flash): ?>
document.addEventListener('DOMContentLoaded', () => {
  showToast('<?= $flash['type'] === 'success' ? '✅' : '❌' ?>', '<?= e($flash['msg']) ?>');
});
<?php endif; ?>

if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('<?= BASE_URL ?>/sw.js').catch(() => {});
}
</script>
</body>
</html>
