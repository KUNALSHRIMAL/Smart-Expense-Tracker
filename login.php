<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        }
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-[#080808] text-white min-h-screen flex items-center justify-center px-5">

  <div class="w-full max-w-sm slide-up">

    <!-- Logo / Brand -->
    <div class="text-center mb-10">
      <div class="w-20 h-20 rounded-3xl mx-auto mb-5 flex items-center justify-center text-4xl"
           style="background:linear-gradient(135deg,#a78bfa,#7c3aed);box-shadow:0 8px 32px rgba(139,92,246,.4)">
        ₹
      </div>
      <h1 class="text-3xl font-bold tracking-tight">Smart Expense Tracker</h1>
      <p class="text-gray-500 mt-1 text-sm">Your personal money manager</p>
    </div>

    <!-- Login Card -->
    <div class="bg-surface rounded-3xl p-6">
      <?php if ($error): ?>
      <div class="bg-red-500/10 border border-red-500/30 rounded-2xl px-4 py-3 mb-5 flex items-center gap-3">
        <span class="text-lg">⚠️</span>
        <p class="text-red-400 text-sm font-medium"><?= e($error) ?></p>
      </div>
      <?php endif; ?>

      <form method="POST" action="" class="space-y-4" autocomplete="on">
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-2 uppercase tracking-wide">Username</label>
          <input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>"
                 class="w-full bg-surface2 border border-bdr rounded-2xl px-4 py-3.5 text-white text-base placeholder-gray-600 focus:border-violet-500 transition-colors"
                 placeholder="Enter username" autocomplete="username" required>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-400 mb-2 uppercase tracking-wide">Password</label>
          <div class="relative">
            <input type="password" name="password" id="pw-field"
                   class="w-full bg-surface2 border border-bdr rounded-2xl px-4 py-3.5 text-white text-base placeholder-gray-600 focus:border-violet-500 transition-colors pr-12"
                   placeholder="Enter password" autocomplete="current-password" required>
            <button type="button" onclick="togglePw()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 tap">
              <svg id="eye-icon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </div>
        </div>

        <button type="submit"
                class="w-full py-4 rounded-2xl font-semibold text-white text-base tap mt-2"
                style="background:linear-gradient(135deg,#a78bfa,#7c3aed);box-shadow:0 4px 20px rgba(139,92,246,.35)">
          Sign In
        </button>
      </form>
    </div>

    <p class="text-center text-gray-500 text-sm mt-6">
      Don't have an account?
      <a href="<?= BASE_URL ?>/register.php" class="text-violet-400 font-medium">Create one</a>
    </p>
  </div>

  <script>
  function togglePw() {
    const f = document.getElementById('pw-field');
    f.type = f.type === 'password' ? 'text' : 'password';
  }
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('<?= BASE_URL ?>/sw.js').catch(() => {});
  }
  </script>
</body>
</html>
