<?php
/* ============================================================
   Chapter 1 — Admin Login
   ============================================================ */
session_start();

// ── Credentials (change these!) ────────────────────────────
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', '$2y$12$eImiTXuWVxfM37uY4JANjQ=='); // placeholder hash
// To generate a real hash run: php -r "echo password_hash('yourpassword', PASSWORD_DEFAULT);"

// Hardcoded fallback (plain comparison for easy setup)
define('ADMIN_USER_PLAIN', 'admin');
define('ADMIN_PASS_PLAIN', 'chapter1@2024');

// Already logged in → go to dashboard
if (!empty($_SESSION['c1_admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token check
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === ADMIN_USER_PLAIN && $password === ADMIN_PASS_PLAIN) {
            session_regenerate_id(true);
            $_SESSION['c1_admin_logged_in'] = true;
            $_SESSION['c1_admin_user']      = htmlspecialchars($username);
            header('Location: index.php');
            exit;
        } else {
            // Timing-safe delay to slow brute force
            sleep(1);
            $error = 'Incorrect username or password.';
        }
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Admin Login — Chapter 1</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="admin.css" />
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' fill='%23111A2E' rx='6'/><text x='50%' y='62%' font-size='18' text-anchor='middle' fill='%23B99452' font-family='Georgia' font-weight='bold'>C</text></svg>" />
</head>
<body class="admin-login-body">

<div class="admin-login">

  <!-- Brand -->
  <div class="admin-login__brand">
    <a href="../index.html" class="admin-login__logo-link" aria-label="Back to Chapter 1 website">
      <img src="../assets/images/logo.png" alt="Chapter 1" class="admin-login__logo" width="140" height="48"
           onerror="this.style.display='none'" />
    </a>
    <p class="admin-login__brand-sub">Admin Panel</p>
  </div>

  <!-- Card -->
  <div class="admin-card admin-login__card" role="main">

    <div class="admin-login__header">
      <h1 class="admin-login__title">Welcome back</h1>
      <p class="admin-login__desc">Sign in to manage your gallery.</p>
    </div>

    <?php if ($error): ?>
    <div class="admin-alert admin-alert--error" role="alert">
      <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="admin-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />

      <div class="admin-form__group">
        <label class="admin-form__label" for="username">Username</label>
        <div class="admin-form__input-wrap">
          <span class="admin-form__input-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </span>
          <input
            type="text"
            id="username"
            name="username"
            class="admin-form__input"
            placeholder="admin"
            autocomplete="username"
            required
            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
          />
        </div>
      </div>

      <div class="admin-form__group">
        <label class="admin-form__label" for="password">Password</label>
        <div class="admin-form__input-wrap">
          <span class="admin-form__input-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input
            type="password"
            id="password"
            name="password"
            class="admin-form__input"
            placeholder="••••••••"
            autocomplete="current-password"
            required
          />
          <button type="button" class="admin-form__pw-toggle" aria-label="Toggle password visibility" data-target="password">
            <svg class="icon-eye" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="icon-eye-off" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
          </button>
        </div>
      </div>

      <button type="submit" class="admin-btn admin-btn--primary admin-btn--full">
        Sign In
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
      </button>

    </form>

    <p class="admin-login__back">
      <a href="../index.html">← Back to website</a>
    </p>

  </div>

  <p class="admin-login__footer">
    Chapter 1 Reading Room &amp; Study Space &nbsp;·&nbsp; Admin Portal
  </p>

</div>

<script>
// Toggle password visibility
document.querySelectorAll('.admin-form__pw-toggle').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = document.getElementById(btn.dataset.target);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.querySelector('.icon-eye').style.display     = isText ? '' : 'none';
    btn.querySelector('.icon-eye-off').style.display = isText ? 'none' : '';
  });
});
</script>
</body>
</html>
