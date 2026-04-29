<?php
$activePage = 'history';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$userId = userId();

// Month selector
$selectedMonth = $_GET['month'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) $selectedMonth = date('Y-m');

$availableMonths = getAvailableMonths($pdo, $userId);
$expenses  = getExpensesByMonth($pdo, $userId, $selectedMonth);
$grouped   = groupByDate($expenses);
$budget    = getBudget($pdo, $userId, $selectedMonth);
$monthTotal = getMonthSpent($pdo, $userId, $selectedMonth);
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-[#080808] text-white">

<div class="max-w-lg mx-auto px-4 pt-5 pb-nav">

  <!-- Header -->
  <div class="flex items-center justify-between mb-5 slide-up">
    <h1 class="text-2xl font-bold tracking-tight">History</h1>
    <a href="<?= BASE_URL ?>/api/export_csv.php?month=<?= urlencode($selectedMonth) ?>"
       class="flex items-center gap-1.5 bg-surface border border-bdr rounded-xl px-3 py-2 text-xs font-medium text-gray-300 tap">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      CSV
    </a>
  </div>

  <!-- Month Selector -->
  <div class="bg-surface rounded-2xl p-1 mb-4 slide-up">
    <select onchange="window.location='?month='+this.value"
            class="w-full bg-transparent text-white text-sm font-medium px-3 py-2.5 cursor-pointer border-0 focus:ring-0">
      <?php foreach ($availableMonths as $m): ?>
      <option value="<?= $m ?>" <?= $m === $selectedMonth ? 'selected' : '' ?>>
        <?= monthLabel($m) ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>

  <!-- Month Summary -->
  <div class="grid grid-cols-3 gap-2 mb-5 slide-up">
    <div class="bg-surface rounded-2xl p-3 text-center">
      <p class="text-gray-400 text-[10px] uppercase tracking-wide mb-1">Budget</p>
      <p class="font-bold text-sm num"><?= formatMoneyShort((float)$budget['amount']) ?></p>
    </div>
    <div class="bg-surface rounded-2xl p-3 text-center">
      <p class="text-gray-400 text-[10px] uppercase tracking-wide mb-1">Spent</p>
      <p class="font-bold text-sm text-red-400 num"><?= formatMoneyShort($monthTotal) ?></p>
    </div>
    <div class="bg-surface rounded-2xl p-3 text-center">
      <p class="text-gray-400 text-[10px] uppercase tracking-wide mb-1">Remaining</p>
      <?php $rem = (float)$budget['amount'] - $monthTotal; ?>
      <p class="font-bold text-sm num <?= $rem < 0 ? 'text-red-400' : 'text-emerald-400' ?>">
        <?= $rem < 0 ? '-' : '' ?><?= formatMoneyShort(abs($rem)) ?>
      </p>
    </div>
  </div>

  <!-- Search + Filter -->
  <div class="space-y-3 mb-5 slide-up">
    <div class="relative">
      <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
      </svg>
      <input type="text" id="search-input" placeholder="Search expenses…"
             class="w-full bg-surface border border-bdr rounded-2xl pl-10 pr-4 py-3 text-sm text-white placeholder-gray-600 focus:border-violet-500 transition-colors">
    </div>

    <!-- Category filter chips -->
    <div class="flex gap-2 scroll-x pb-1 -mx-4 px-4">
      <button data-cat="all" onclick="filterCat(this)"
              class="cat-chip flex-shrink-0 px-4 py-2 rounded-full text-xs font-semibold transition-colors bg-violet-600 text-white">
        All
      </button>
      <?php foreach ($CATEGORIES as $cat => $icon): ?>
      <button data-cat="<?= e($cat) ?>" onclick="filterCat(this)"
              class="cat-chip flex-shrink-0 flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold transition-colors bg-surface2 text-gray-400 border border-bdr">
        <span><?= $icon ?></span><?= $cat ?>
      </button>
      <?php endforeach; ?>
      <button data-cat="__credit" onclick="filterCat(this)"
              class="cat-chip flex-shrink-0 flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold transition-colors bg-surface2 text-gray-400 border border-bdr">
        ↗ Credit
      </button>
    </div>
  </div>

  <!-- Transaction List -->
  <div id="tx-container">
    <?php if (empty($grouped)): ?>
    <div class="text-center py-16 text-gray-600">
      <p class="text-5xl mb-4">📭</p>
      <p class="font-semibold text-gray-400">No transactions</p>
      <p class="text-sm mt-1">for <?= monthLabel($selectedMonth) ?></p>
    </div>
    <?php else: ?>
    <?php foreach ($grouped as $date => $dayData): ?>
    <div class="date-group mb-5">
      <!-- Date header -->
      <div class="flex items-center justify-between mb-2 px-1">
        <p class="text-sm font-semibold text-gray-300"><?= humanDate($date) ?></p>
        <?php if ($dayData['total'] > 0): ?>
        <p class="text-xs font-semibold text-gray-500 num">-<?= formatMoneyShort($dayData['total']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Expenses for this date -->
      <div class="space-y-2">
        <?php foreach ($dayData['expenses'] as $exp): ?>
        <?php $credit = !empty($exp['is_credit']); ?>
        <div class="expense-item bg-surface rounded-2xl p-4 flex items-center gap-3"
             data-id="<?= $exp['id'] ?>"
             data-search="<?= e(strtolower($exp['title'] . ' ' . ($credit ? 'credit' : $exp['category']) . ' ' . ($exp['notes'] ?? ''))) ?>"
             data-cat="<?= $credit ? '__credit' : e($exp['category']) ?>">

          <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl flex-shrink-0
                      <?= $credit ? 'bg-emerald-500/15' : 'bg-surface2' ?>">
            <?= $credit ? '↗️' : getCategoryIcon($exp['category']) ?>
          </div>

          <div class="flex-1 min-w-0">
            <p class="font-medium text-sm leading-tight truncate"><?= e($exp['title']) ?></p>
            <p class="text-xs mt-0.5 <?= $credit ? 'text-emerald-500' : 'text-gray-500' ?>">
              <?= $credit ? 'Credit · to receive' : e($exp['category']) ?>
            </p>
            <?php if ($exp['notes']): ?>
            <p class="text-xs text-gray-600 mt-0.5 truncate"><?= e($exp['notes']) ?></p>
            <?php endif; ?>
          </div>

          <div class="flex-shrink-0 text-right">
            <p class="font-bold text-sm num <?= $credit ? 'text-emerald-400' : 'text-red-400' ?>">
              <?= $credit ? '+' : '-' ?><?= formatMoneyShort((float)$exp['amount']) ?>
            </p>
            <div class="flex gap-2 mt-1.5 justify-end">
              <a href="<?= BASE_URL ?>/add-expense.php?edit=<?= $exp['id'] ?>"
                 class="text-[10px] text-violet-400 font-medium tap">Edit</a>
              <button onclick="askDelete(<?= $exp['id'] ?>)"
                      class="text-[10px] text-red-400 font-medium tap">Delete</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<!-- Delete Confirm Modal -->
<div id="del-modal" class="fixed inset-0 z-50 hidden items-end justify-center px-4 pb-8 modal-bg">
  <div class="absolute inset-0 bg-black/60" onclick="closeDelete()"></div>
  <div class="relative bg-surface rounded-3xl p-6 w-full max-w-sm shadow-2xl">
    <h3 class="text-lg font-bold mb-1">Delete Expense?</h3>
    <p class="text-gray-400 text-sm mb-6">This cannot be undone.</p>
    <div class="flex gap-3">
      <button onclick="closeDelete()"
              class="flex-1 bg-surface2 border border-bdr rounded-2xl py-3.5 font-semibold text-sm tap">Cancel</button>
      <button onclick="doDelete()"
              class="flex-1 bg-red-600 rounded-2xl py-3.5 font-semibold text-sm tap">Delete</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast"
     class="toast fixed z-50 flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl"
     style="bottom:calc(88px + env(safe-area-inset-bottom,0px));left:1rem;right:1rem;max-width:360px;margin:0 auto;background:#2c2c2e;border:1px solid #3a3a3c">
  <span id="toast-icon">✓</span>
  <p id="toast-msg" class="font-medium text-sm"></p>
</div>

<?php require_once __DIR__ . '/includes/nav.php'; ?>

<script>
let deleteTarget = null;

function askDelete(id) {
  deleteTarget = id;
  const m = document.getElementById('del-modal');
  m.classList.remove('hidden');
  m.classList.add('flex');
}
function closeDelete() {
  deleteTarget = null;
  const m = document.getElementById('del-modal');
  m.classList.add('hidden');
  m.classList.remove('flex');
}

async function doDelete() {
  if (!deleteTarget) return;
  const id = deleteTarget;
  closeDelete();
  try {
    const res = await fetch('<?= BASE_URL ?>/api/delete_expense.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({id})
    });
    const data = await res.json();
    if (data.success) {
      const el = document.querySelector(`.expense-item[data-id="${id}"]`);
      el?.remove();
      showToast('✅','Deleted');
    } else {
      showToast('❌', data.error || 'Failed');
    }
  } catch { showToast('❌','Network error'); }
}

// Search
document.getElementById('search-input').addEventListener('input', applyFilters);

let activeCat = 'all';
function filterCat(btn) {
  activeCat = btn.dataset.cat;
  document.querySelectorAll('.cat-chip').forEach(b => {
    b.classList.toggle('bg-violet-600', b === btn);
    b.classList.toggle('text-white', b === btn);
    b.classList.toggle('bg-surface2', b !== btn);
    b.classList.toggle('text-gray-400', b !== btn);
    b.classList.toggle('border-bdr', b !== btn);
    b.classList.toggle('border', b !== btn);
  });
  applyFilters();
}

function applyFilters() {
  const q = document.getElementById('search-input').value.toLowerCase().trim();
  document.querySelectorAll('.expense-item').forEach(el => {
    const matchSearch = !q || el.dataset.search.includes(q);
    const matchCat    = activeCat === 'all' || el.dataset.cat === activeCat;
    el.style.display  = matchSearch && matchCat ? '' : 'none';
  });
  // Hide empty date groups
  document.querySelectorAll('.date-group').forEach(g => {
    const visible = [...g.querySelectorAll('.expense-item')].some(e => e.style.display !== 'none');
    g.style.display = visible ? '' : 'none';
  });
}

function showToast(icon, msg) {
  document.getElementById('toast-icon').textContent = icon;
  document.getElementById('toast-msg').textContent = msg;
  const t = document.getElementById('toast');
  t.classList.add('show');
  clearTimeout(window._tt);
  window._tt = setTimeout(() => t.classList.remove('show'), 2500);
}
</script>
</body>
</html>
