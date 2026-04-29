<?php
$activePage = 'settings';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$userId    = userId();
$month     = date('Y-m');
$budget    = getBudget($pdo, $userId, $month);
$shortcuts = getShortcuts($pdo, $userId);
$username  = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-[#080808] text-white">

<div class="max-w-lg mx-auto px-4 pt-5 pb-nav">

  <!-- Header -->
  <div class="flex items-center justify-between mb-7 slide-up">
    <h1 class="text-2xl font-bold tracking-tight">Settings</h1>
    <div class="w-10 h-10 rounded-full bg-violet-600/20 border border-violet-500/30 flex items-center justify-center text-violet-400 font-bold text-sm">
      <?= strtoupper(substr($username, 0, 1)) ?>
    </div>
  </div>

  <!-- Budget Section -->
  <section class="bg-surface rounded-3xl p-5 mb-4 slide-up">
    <h2 class="font-semibold text-gray-200 mb-1">Monthly Budget</h2>
    <p class="text-gray-500 text-xs mb-4">Current: <span class="text-white font-semibold num"><?= formatMoney((float)$budget['amount']) ?></span> for <?= monthLabel($month) ?></p>

    <div class="flex gap-3">
      <div class="flex-1 flex items-center gap-2 bg-surface2 border border-bdr rounded-2xl px-4 py-3">
        <span class="text-gray-400 text-sm font-bold">₹</span>
        <input type="number" id="budget-input" value="<?= (int)$budget['amount'] ?>"
               class="flex-1 bg-transparent text-white text-base font-semibold border-0 p-0 focus:ring-0 num min-w-0"
               min="100" step="100" inputmode="numeric">
      </div>
      <button onclick="saveBudget()"
              class="px-5 rounded-2xl font-semibold text-sm tap"
              style="background:linear-gradient(135deg,#a78bfa,#7c3aed)">
        Save
      </button>
    </div>
    <p class="text-gray-600 text-xs mt-2">Updates apply to the current month only if no budget was previously set, otherwise creates/updates the monthly record.</p>
  </section>

  <!-- Quick Shortcuts Section -->
  <section id="shortcuts" class="bg-surface rounded-3xl p-5 mb-4 slide-up">
    <h2 class="font-semibold text-gray-200 mb-4">Quick Shortcuts</h2>

    <!-- Existing shortcuts list -->
    <div id="shortcut-list" class="space-y-2 mb-4">
      <?php if (empty($shortcuts)): ?>
      <p class="text-gray-500 text-sm text-center py-3" id="no-shortcuts">No shortcuts yet. Add one below.</p>
      <?php else: ?>
      <?php foreach ($shortcuts as $sc): ?>
      <div class="shortcut-row flex items-center gap-3 bg-surface2 rounded-2xl px-4 py-3" data-id="<?= $sc['id'] ?>">
        <span class="text-xl"><?= getCategoryIcon($sc['category']) ?></span>
        <div class="flex-1 min-w-0">
          <p class="font-medium text-sm truncate"><?= e($sc['title']) ?></p>
          <p class="text-xs text-gray-500"><?= e($sc['category']) ?></p>
        </div>
        <p class="font-bold text-sm text-violet-400 num flex-shrink-0"><?= formatMoneyShort((float)$sc['amount']) ?></p>
        <button onclick="deleteShortcut(this, <?= $sc['id'] ?>)"
                class="text-gray-600 hover:text-red-400 transition-colors tap p-1 flex-shrink-0">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
        </button>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Add shortcut form -->
    <div class="border-t border-bdr pt-4">
      <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-3">Add Shortcut</p>
      <div class="space-y-3">
        <input type="text" id="sc-title" placeholder="Label (e.g. Tea)"
               class="w-full bg-surface2 border border-bdr rounded-2xl px-4 py-3 text-sm text-white placeholder-gray-600 focus:border-violet-500 transition-colors">
        <div class="flex gap-3">
          <div class="flex-1 flex items-center gap-2 bg-surface2 border border-bdr rounded-2xl px-4 py-3">
            <span class="text-gray-400 text-sm">₹</span>
            <input type="number" id="sc-amount" placeholder="Amount" inputmode="decimal" min="0.01"
                   class="flex-1 bg-transparent text-white text-sm border-0 p-0 focus:ring-0 num min-w-0">
          </div>
          <select id="sc-cat"
                  class="flex-1 bg-surface2 border border-bdr rounded-2xl px-3 py-3 text-sm text-white focus:border-violet-500 transition-colors">
            <?php foreach ($CATEGORIES as $cat => $icon): ?>
            <option value="<?= e($cat) ?>"><?= $icon ?> <?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button onclick="addShortcut()"
                class="w-full py-3.5 rounded-2xl text-sm font-semibold bg-surface2 border border-bdr text-gray-300 tap hover:border-violet-500 transition-colors">
          + Add Shortcut
        </button>
      </div>
    </div>
  </section>

  <!-- Password Section -->
  <section class="bg-surface rounded-3xl p-5 mb-4 slide-up-2">
    <h2 class="font-semibold text-gray-200 mb-4">Change Password</h2>
    <div class="space-y-3">
      <input type="password" id="pw-current" placeholder="Current password"
             class="w-full bg-surface2 border border-bdr rounded-2xl px-4 py-3 text-sm text-white placeholder-gray-600 focus:border-violet-500 transition-colors">
      <input type="password" id="pw-new" placeholder="New password"
             class="w-full bg-surface2 border border-bdr rounded-2xl px-4 py-3 text-sm text-white placeholder-gray-600 focus:border-violet-500 transition-colors">
      <input type="password" id="pw-confirm" placeholder="Confirm new password"
             class="w-full bg-surface2 border border-bdr rounded-2xl px-4 py-3 text-sm text-white placeholder-gray-600 focus:border-violet-500 transition-colors">
      <button onclick="changePassword()"
              class="w-full py-3.5 rounded-2xl text-sm font-semibold tap"
              style="background:linear-gradient(135deg,#a78bfa,#7c3aed)">
        Update Password
      </button>
    </div>
  </section>

  <!-- Data & Export Section -->
  <section class="bg-surface rounded-3xl p-5 mb-4 slide-up-2">
    <h2 class="font-semibold text-gray-200 mb-4">Data</h2>
    <a href="<?= BASE_URL ?>/api/export_csv.php?month=<?= date('Y-m') ?>"
       class="flex items-center gap-3 bg-surface2 border border-bdr rounded-2xl p-4 tap">
      <div class="w-10 h-10 rounded-xl bg-emerald-500/15 flex items-center justify-center">
        <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
      </div>
      <div>
        <p class="font-medium text-sm">Export This Month</p>
        <p class="text-xs text-gray-500"><?= monthLabel(date('Y-m')) ?> · CSV format</p>
      </div>
      <svg class="w-4 h-4 text-gray-600 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
      </svg>
    </a>
  </section>

  <!-- Account Section -->
  <section class="bg-surface rounded-3xl p-5 mb-4 slide-up-3">
    <h2 class="font-semibold text-gray-200 mb-4">Account</h2>
    <div class="flex items-center gap-3 mb-4 bg-surface2 rounded-2xl p-4">
      <div class="w-10 h-10 rounded-full bg-violet-600/30 flex items-center justify-center text-violet-400 font-bold">
        <?= strtoupper(substr($username, 0, 1)) ?>
      </div>
      <div>
        <p class="font-medium text-sm"><?= e($username) ?></p>
        <p class="text-xs text-gray-500">Logged in</p>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/logout.php"
       class="flex items-center justify-center gap-2 w-full py-3.5 rounded-2xl bg-red-600/15 border border-red-600/25 text-red-400 font-semibold text-sm tap">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
      </svg>
      Logout
    </a>
  </section>

  <p class="text-center text-gray-700 text-xs pb-4"><?= APP_NAME ?> · Built for mobile</p>
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
function showToast(icon, msg, type = 'info') {
  document.getElementById('toast-icon').textContent = icon;
  document.getElementById('toast-msg').textContent = msg;
  const t = document.getElementById('toast');
  t.style.background = type === 'error' ? '#3a1212' : '#2c2c2e';
  t.classList.add('show');
  clearTimeout(window._tt);
  window._tt = setTimeout(() => t.classList.remove('show'), 3000);
}

// ----- Budget -----
async function saveBudget() {
  const amount = parseFloat(document.getElementById('budget-input').value);
  if (!amount || amount < 100) return showToast('❌','Enter a valid amount (min ₹100)','error');
  try {
    const res = await fetch('<?= BASE_URL ?>/api/update_budget.php', {
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({amount})
    });
    const d = await res.json();
    d.success ? showToast('✅', `Budget updated to ₹${amount.toLocaleString('en-IN')}`) : showToast('❌', d.error,'error');
  } catch { showToast('❌','Network error','error'); }
}

// ----- Password -----
async function changePassword() {
  const cur = document.getElementById('pw-current').value;
  const nw  = document.getElementById('pw-new').value;
  const cf  = document.getElementById('pw-confirm').value;
  if (!cur || !nw || !cf) return showToast('❌','Fill all password fields','error');
  if (nw.length < 4)       return showToast('❌','New password must be ≥ 4 characters','error');
  if (nw !== cf)            return showToast('❌','Passwords do not match','error');
  try {
    const res = await fetch('<?= BASE_URL ?>/api/change_password.php', {
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({current:cur, new_password:nw})
    });
    const d = await res.json();
    if (d.success) {
      showToast('✅','Password changed!');
      ['pw-current','pw-new','pw-confirm'].forEach(id => document.getElementById(id).value='');
    } else { showToast('❌', d.error,'error'); }
  } catch { showToast('❌','Network error','error'); }
}

// ----- Shortcuts -----
async function addShortcut() {
  const title  = document.getElementById('sc-title').value.trim();
  const amount = parseFloat(document.getElementById('sc-amount').value);
  const cat    = document.getElementById('sc-cat').value;
  if (!title)            return showToast('❌','Enter a shortcut title','error');
  if (!amount || amount <= 0) return showToast('❌','Enter a valid amount','error');
  try {
    const res = await fetch('<?= BASE_URL ?>/api/add_shortcut.php', {
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({title, amount, category: cat})
    });
    const d = await res.json();
    if (d.success) {
      showToast('✅','Shortcut added!');
      document.getElementById('sc-title').value = '';
      document.getElementById('sc-amount').value = '';
      // Remove "no shortcuts" message if present
      document.getElementById('no-shortcuts')?.remove();
      // Append new row
      const list = document.getElementById('shortcut-list');
      const icons = <?= json_encode($CATEGORIES) ?>;
      const icon = icons[cat] || '📦';
      const row = document.createElement('div');
      row.className = 'shortcut-row flex items-center gap-3 bg-surface2 rounded-2xl px-4 py-3';
      row.dataset.id = d.id;
      row.innerHTML = `
        <span class="text-xl">${icon}</span>
        <div class="flex-1 min-w-0">
          <p class="font-medium text-sm truncate">${title}</p>
          <p class="text-xs text-gray-500">${cat}</p>
        </div>
        <p class="font-bold text-sm text-violet-400 num flex-shrink-0">₹${amount.toLocaleString('en-IN',{maximumFractionDigits:0})}</p>
        <button onclick="deleteShortcut(this,${d.id})" class="text-gray-600 hover:text-red-400 transition-colors tap p-1 flex-shrink-0">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
        </button>`;
      list.appendChild(row);
    } else { showToast('❌', d.error,'error'); }
  } catch { showToast('❌','Network error','error'); }
}

async function deleteShortcut(btn, id) {
  if (!confirm('Delete this shortcut?')) return;
  try {
    const res = await fetch('<?= BASE_URL ?>/api/delete_shortcut.php', {
      method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({id})
    });
    const d = await res.json();
    if (d.success) {
      btn.closest('.shortcut-row').remove();
      showToast('✅','Shortcut removed');
    } else { showToast('❌', d.error,'error'); }
  } catch { showToast('❌','Network error','error'); }
}

// Scroll to shortcuts section if URL has #shortcuts
if (location.hash === '#shortcuts') {
  setTimeout(() => document.getElementById('shortcuts')?.scrollIntoView({behavior:'smooth'}), 300);
}
</script>
</body>
</html>
