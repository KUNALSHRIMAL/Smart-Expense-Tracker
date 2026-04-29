<?php
// $activePage must be set before including: 'dashboard' | 'history' | 'settings'
$activePage = $activePage ?? 'dashboard';
$navItems = [
    ['page' => 'dashboard', 'href' => BASE_URL . '/dashboard.php', 'label' => 'Home',
     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
    ['page' => 'history',   'href' => BASE_URL . '/history.php',   'label' => 'History',
     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>'],
    ['page' => 'settings',  'href' => BASE_URL . '/settings.php',  'label' => 'Settings',
     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
];
?>

<!-- FAB – Add Expense -->
<a href="<?= BASE_URL ?>/add-expense.php"
   class="fixed z-50 flex items-center justify-center w-14 h-14 rounded-full tap"
   style="background:linear-gradient(135deg,#a78bfa,#7c3aed);box-shadow:0 4px 24px rgba(139,92,246,.5);bottom:calc(24px + env(safe-area-inset-bottom,0px));left:50%;transform:translateX(-50%)">
  <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
  </svg>
</a>

<!-- Bottom Nav -->
<nav class="fixed bottom-0 left-0 right-0 z-40"
     style="background:rgba(18,18,20,.97);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border-top:1px solid rgba(58,58,60,.8);padding-bottom:env(safe-area-inset-bottom,0px)">
  <div class="flex items-center justify-around h-16 max-w-lg mx-auto px-2">
    <?php foreach ($navItems as $i => $item):
      $active = ($activePage === $item['page']);
      $color  = $active ? '#a78bfa' : '#636366';
      // Insert FAB spacer in the middle
      if ($i === 1): ?>
        <div class="w-14 flex-shrink-0"></div>
      <?php endif; ?>
      <a href="<?= $item['href'] ?>"
         class="flex flex-col items-center gap-0.5 flex-1 py-2 transition-colors <?= $active ? 'text-violet-400' : 'text-gray-500' ?>">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="<?= $active ? '2' : '1.75' ?>">
          <?= $item['icon'] ?>
        </svg>
        <span class="text-[10px] font-medium leading-none"><?= $item['label'] ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</nav>
