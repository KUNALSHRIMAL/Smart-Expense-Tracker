<?php
$activePage = 'dashboard';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$userId = userId();
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = $editId > 0;
$expense = null;

if ($editing) {
    $stmt = $pdo->prepare("SELECT * FROM expenses WHERE id=? AND user_id=?");
    $stmt->execute([$editId, $userId]);
    $expense = $stmt->fetch();
    if (!$expense) {
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
}

$errors = [];
$values = [
    'title'        => $expense['title']        ?? '',
    'amount'       => $expense['amount']        ?? '',
    'category'     => $expense['category']      ?? 'Other',
    'expense_date' => $expense['expense_date']  ?? date('Y-m-d'),
    'notes'        => $expense['notes']         ?? '',
    'is_credit'    => (int)($expense['is_credit'] ?? 0),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['title']        = trim($_POST['title']        ?? '');
    $values['amount']       = trim($_POST['amount']       ?? '');
    $values['category']     = trim($_POST['category']     ?? 'Other');
    $values['expense_date'] = trim($_POST['expense_date'] ?? '');
    $values['notes']        = trim($_POST['notes']        ?? '');
    $values['is_credit']    = isset($_POST['is_credit']) && $_POST['is_credit'] === '1' ? 1 : 0;

    if ($values['is_credit']) $values['category'] = 'Other';

    if ($values['title'] === '')       $errors[] = 'Title is required.';
    if (!is_numeric($values['amount']) || (float)$values['amount'] <= 0) $errors[] = 'Enter a valid amount.';
    if (!$values['is_credit'] && !in_array($values['category'], array_keys($CATEGORIES))) $errors[] = 'Invalid category.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $values['expense_date'])) $errors[] = 'Invalid date.';

    if (empty($errors)) {
        if ($editing) {
            $stmt = $pdo->prepare("UPDATE expenses SET title=?,amount=?,category=?,expense_date=?,notes=?,is_credit=? WHERE id=? AND user_id=?");
            $stmt->execute([
                $values['title'], (float)$values['amount'], $values['category'],
                $values['expense_date'], $values['notes'], $values['is_credit'],
                $editId, $userId,
            ]);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Entry updated!'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO expenses (user_id,title,amount,category,expense_date,notes,is_credit) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([
                $userId, $values['title'], (float)$values['amount'],
                $values['category'], $values['expense_date'], $values['notes'], $values['is_credit'],
            ]);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => $values['is_credit'] ? 'Credit recorded!' : 'Expense added!'];
        }
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
}

$isCredit = (int)$values['is_credit'];
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-[#080808] text-white">

<div class="max-w-lg mx-auto px-4 pt-5 pb-nav">

  <!-- Header -->
  <div class="flex items-center gap-4 mb-7 slide-up">
    <a href="javascript:history.back()"
       class="w-10 h-10 rounded-2xl bg-surface flex items-center justify-center tap flex-shrink-0">
      <svg class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <h1 class="text-2xl font-bold tracking-tight" id="page-title">
      <?php if ($editing): ?>Edit Entry<?php elseif ($isCredit): ?>Add Credit<?php else: ?>Add Expense<?php endif; ?>
    </h1>
  </div>

  <!-- Errors -->
  <?php if (!empty($errors)): ?>
  <div class="bg-red-500/10 border border-red-500/25 rounded-2xl p-4 mb-5">
    <?php foreach ($errors as $err): ?>
    <p class="text-red-400 text-sm">• <?= e($err) ?></p>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Form -->
  <form method="POST" action="" class="space-y-4 slide-up">

    <!-- Type toggle (hidden when editing to preserve original type) -->
    <?php if (!$editing): ?>
    <div class="bg-surface rounded-2xl p-1 flex gap-1">
      <button type="button" id="btn-expense" onclick="setType(0)"
              class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition-colors
                     <?= !$isCredit ? 'bg-violet-600 text-white' : 'text-gray-400' ?>">
        Expense
      </button>
      <button type="button" id="btn-credit" onclick="setType(1)"
              class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition-colors
                     <?= $isCredit ? 'bg-emerald-600 text-white' : 'text-gray-400' ?>">
        ↗ Credit
      </button>
    </div>
    <?php endif; ?>

    <input type="hidden" name="is_credit" id="is_credit_field" value="<?= $isCredit ?>">

    <!-- Title -->
    <div class="bg-surface rounded-2xl p-4">
      <label id="title-label" class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">
        <?= $isCredit ? 'Given to *' : 'Expense Title *' ?>
      </label>
      <input type="text" name="title" id="title-input" value="<?= e($values['title']) ?>"
             class="w-full bg-transparent text-white text-base placeholder-gray-600 border-0 p-0 focus:ring-0"
             placeholder="<?= $isCredit ? 'e.g. Ramesh, Client name…' : 'e.g. Grocery shopping' ?>"
             autocomplete="off" required>
    </div>

    <!-- Amount -->
    <div class="bg-surface rounded-2xl p-4">
      <label class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Amount (₹) *</label>
      <div class="flex items-center gap-2">
        <span class="text-2xl font-bold text-gray-400">₹</span>
        <input type="number" name="amount" value="<?= e($values['amount']) ?>"
               class="flex-1 bg-transparent text-white text-3xl font-bold placeholder-gray-700 border-0 p-0 focus:ring-0 num"
               placeholder="0" step="0.01" min="0.01" inputmode="decimal" required>
      </div>
    </div>

    <!-- Category (hidden for credits) -->
    <div id="category-section" class="bg-surface rounded-2xl p-4 <?= $isCredit ? 'hidden' : '' ?>">
      <label class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-3">Category *</label>
      <div class="grid grid-cols-4 gap-2">
        <?php foreach ($CATEGORIES as $cat => $icon): ?>
        <label class="cat-label cursor-pointer">
          <input type="radio" name="category" value="<?= e($cat) ?>"
                 class="hidden" <?= $values['category'] === $cat ? 'checked' : '' ?>>
          <div class="cat-btn flex flex-col items-center gap-1 py-3 rounded-xl border transition-all
                      <?= $values['category'] === $cat ? 'border-violet-500 bg-violet-500/15' : 'border-bdr bg-surface2' ?>">
            <span class="text-xl"><?= $icon ?></span>
            <span class="text-[10px] font-medium text-gray-400 leading-tight text-center"><?= $cat ?></span>
          </div>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Date -->
    <div class="bg-surface rounded-2xl p-4">
      <label class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Date *</label>
      <input type="date" name="expense_date" value="<?= e($values['expense_date']) ?>"
             max="<?= date('Y-m-d') ?>"
             class="w-full bg-transparent text-white text-base border-0 p-0 focus:ring-0">
    </div>

    <!-- Notes -->
    <div class="bg-surface rounded-2xl p-4">
      <label class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Notes (Optional)</label>
      <textarea name="notes" rows="2"
                class="w-full bg-transparent text-white text-base placeholder-gray-600 border-0 p-0 focus:ring-0 resize-none"
                placeholder="Add a note..."><?= e($values['notes']) ?></textarea>
    </div>

    <!-- Credit info banner -->
    <div id="credit-info" class="<?= $isCredit ? '' : 'hidden' ?> bg-emerald-500/10 border border-emerald-500/25 rounded-2xl px-4 py-3 flex items-center gap-3">
      <span class="text-lg">↗️</span>
      <p class="text-emerald-400 text-xs font-medium">This amount will show as <strong>+positive</strong> in history and will <strong>not</strong> count against your budget.</p>
    </div>

    <!-- Submit -->
    <button type="submit" id="submit-btn"
            class="w-full py-4 rounded-2xl font-semibold text-white text-base tap mt-2"
            style="background:<?= $isCredit ? 'linear-gradient(135deg,#34d399,#059669)' : 'linear-gradient(135deg,#a78bfa,#7c3aed)' ?>;box-shadow:0 4px 20px rgba(<?= $isCredit ? '52,211,153' : '139,92,246' ?>,.35)"
            id="submit-btn">
      <?= $editing ? 'Save Changes' : ($isCredit ? 'Record Credit' : 'Add Expense') ?>
    </button>

    <?php if ($editing): ?>
    <input type="hidden" name="edit_id" value="<?= $editId ?>">
    <?php endif; ?>

  </form>
</div>

<?php require_once __DIR__ . '/includes/nav.php'; ?>

<script>
const editing = <?= $editing ? 'true' : 'false' ?>;

function setType(isCredit) {
  document.getElementById('is_credit_field').value = isCredit;

  const btnE = document.getElementById('btn-expense');
  const btnC = document.getElementById('btn-credit');
  btnE.className = 'flex-1 py-2.5 rounded-xl text-sm font-semibold transition-colors ' +
    (isCredit ? 'text-gray-400' : 'bg-violet-600 text-white');
  btnC.className = 'flex-1 py-2.5 rounded-xl text-sm font-semibold transition-colors ' +
    (isCredit ? 'bg-emerald-600 text-white' : 'text-gray-400');

  document.getElementById('title-label').textContent = isCredit ? 'Given to *' : 'Expense Title *';
  const ti = document.getElementById('title-input');
  ti.placeholder = isCredit ? 'e.g. Ramesh, Client name…' : 'e.g. Grocery shopping';

  document.getElementById('category-section').classList.toggle('hidden', !!isCredit);
  document.getElementById('credit-info').classList.toggle('hidden', !isCredit);

  if (!editing) {
    document.getElementById('page-title').textContent = isCredit ? 'Add Credit' : 'Add Expense';
    const btn = document.getElementById('submit-btn');
    btn.textContent = isCredit ? 'Record Credit' : 'Add Expense';
    btn.style.background = isCredit
      ? 'linear-gradient(135deg,#34d399,#059669)'
      : 'linear-gradient(135deg,#a78bfa,#7c3aed)';
    btn.style.boxShadow = isCredit
      ? '0 4px 20px rgba(52,211,153,.35)'
      : '0 4px 20px rgba(139,92,246,.35)';
  }
}

// Category pill selection
document.querySelectorAll('.cat-label input').forEach(radio => {
  radio.addEventListener('change', () => {
    document.querySelectorAll('.cat-btn').forEach(btn => {
      btn.classList.remove('border-violet-500','bg-violet-500/15');
      btn.classList.add('border-bdr','bg-surface2');
    });
    const selected = radio.parentElement.querySelector('.cat-btn');
    selected.classList.add('border-violet-500','bg-violet-500/15');
    selected.classList.remove('border-bdr','bg-surface2');
  });
});
</script>
</body>
</html>
