<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error = 'Username must be 3–50 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Username may only contain letters, numbers, and underscores.';
    } elseif (strlen($password) < 4) {
        $error = 'Password must be at least 4 characters.';
    } elseif ($password !== $password2) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'That username is already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)")
                ->execute([$username, $hash]);
            $success = 'Account created! You can now sign in.';
        }
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
      <h1 class="text-3xl font-bold tracking-tight">Create Account</h1>
      <p class="text-gray-500 mt-1 text-sm">Your personal money manager</p>
    </div>

    <!-- Register Card -->
    <div class="bg-surface rounded-3xl p-6">

      <?php if ($error): ?>
      <div class="bg-red-500/10 border border-red-500/30 rounded-2xl px-4 py-3 mb-5 flex items-center gap-3">
        <span class="text-lg">⚠️</span>
        <p class="text-red-400 text-sm font-medium"><?= e($error) ?></p>
      </div>
      <?php endif; ?>

      <?php if ($success): ?>
      <div class="bg-green-500/10 border border-green-500/30 rounded-2xl px-4 py-3 mb-5 flex items-center gap-3">
        <span class="text-lg">✅</span>
        <p class="text-green-400 text-sm font-medium"><?= e($success) ?></p>
      </div>
      <?php endif; ?>

      <?php if (!$success): ?>
      <form method="POST" action="" class="space-y-4" autocomplete="off">

        <div>
          <label class="block text-xs font-medium text-gray-400 mb-2 uppercase tracking-wide">Username</label>
          <input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>"
                 class="w-full bg-surface2 border border-bdr rounded-2xl px-4 py-3.5 text-white text-base placeholder-gray-600 focus:border-violet-500 transition-colors"
                 placeholder="Choose a username" autocomplete="off" required>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-400 mb-2 uppercase tracking-wide">Password</label>
          <div class="relative">
            <input type="password" name="password" id="pw-field"
                   class="w-full bg-surface2 border border-bdr rounded-2xl px-4 py-3.5 text-white text-base placeholder-gray-600 focus:border-violet-500 transition-colors pr-12"
                   placeholder="Choose a password" autocomplete="new-password" required>
            <button type="button" onclick="togglePw('pw-field')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 tap">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </div>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-400 mb-2 uppercase tracking-wide">Confirm Password</label>
          <div class="relative">
            <input type="password" name="password2" id="pw2-field"
                   class="w-full bg-surface2 border border-bdr rounded-2xl px-4 py-3.5 text-white text-base placeholder-gray-600 focus:border-violet-500 transition-colors pr-12"
                   placeholder="Repeat your password" autocomplete="new-password" required>
            <button type="button" onclick="togglePw('pw2-field')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 tap">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </div>
        </div>

        <button type="submit"
                class="w-full py-4 rounded-2xl font-semibold text-white text-base tap mt-2"
                style="background:linear-gradient(135deg,#a78bfa,#7c3aed);box-shadow:0 4px 20px rgba(139,92,246,.35)">
          Create Account
        </button>
      </form>
      <?php else: ?>
      <a href="<?= BASE_URL ?>/login.php"
         class="block w-full py-4 rounded-2xl font-semibold text-white text-base text-center tap"
         style="background:linear-gradient(135deg,#a78bfa,#7c3aed);box-shadow:0 4px 20px rgba(139,92,246,.35)">
        Go to Sign In
      </a>
      <?php endif; ?>

    </div>

    <p class="text-center text-gray-500 text-sm mt-6">
      Already have an account?
      <a href="<?= BASE_URL ?>/login.php" class="text-violet-400 font-medium">Sign in</a>
    </p>

  </div>

  <script>
  function togglePw(id) {
    const f = document.getElementById(id);
    f.type = f.type === 'password' ? 'text' : 'password';
  }
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('<?= BASE_URL ?>/sw.js').catch(() => {});
  }
  </script>
</body>
</html>
