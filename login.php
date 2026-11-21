<?php
// Login page and server-side database authentication handler.
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';

// Load local development settings without overriding environment variables.
$envFile = __DIR__ . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

initSession();
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$fieldError = [];
$username = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verifyCsrf((string)($_POST['csrf_token'] ?? ''))) {
        $error = 'Your sign-in request expired. Refresh the page and try again.';
    } else {
        $usernameInput = $_POST['username'] ?? '';
        $passwordInput = $_POST['password'] ?? '';
        $username = is_string($usernameInput) ? trim($usernameInput) : '';
        $password = is_string($passwordInput) ? $passwordInput : '';

        if ($username === '') $fieldError['username'] = 'Username is required.';
        if ($password === '') $fieldError['password'] = 'Password is required.';

        if (!$fieldError) {
            if (strlen($username) > 100 || strlen($password) > 255) {
                $error = 'Invalid username or password.';
            } else {
                try {
                    $pdo = getDatabaseConnection();
                    $stmt = $pdo->prepare(
                        'SELECT id, username, password_hash FROM users WHERE username = :username LIMIT 1'
                    );
                    $stmt->execute([':username' => $username]);
                    $user = $stmt->fetch();

                    if ($user && password_verify($password, $user['password_hash'])) {
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['login_at'] = date('Y-m-d H:i:s');
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        header('Location: dashboard.php');
                        exit;
                    }

                    // Keep the response identical for an unknown user and a wrong password.
                    $error = 'Invalid username or password.';
                    usleep(300000);
                } catch (RuntimeException $e) {
                    $error = 'A server error occurred. Please try again later.';
                    error_log($e->getMessage());
                }
            }
        }
    }
}

$pageTitle = 'Sign In — Luma';
$bodyClass = 'page-login';
$csrfToken = csrfToken();
include __DIR__ . '/includes/header.php';
?>

<div class="split-layout">
    <main class="login-panel" id="main-content">
        <section class="login-panel__inner" aria-labelledby="login-heading">
            <header class="login-panel__header">
                <span class="lab-badge">LUMA · LAB 11</span>
                <h1 class="login-panel__heading" id="login-heading">Welcome Back</h1>
                <p class="login-panel__subtext">Sign in to continue to your account.</p>
            </header>

            <?php if ($error): ?>
                <div class="alert alert--error" role="alert" aria-live="polite">
                    <span class="alert__icon" aria-hidden="true">!</span>
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="login.php" id="loginForm" aria-label="Login form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-group <?= isset($fieldError['username']) ? 'form-group--error' : '' ?>">
                    <label class="form-label" for="username">Username</label>
                    <div class="input-wrapper">
                        <span class="input-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M5 21a7 7 0 0 1 14 0"/></svg>
                        </span>
                        <input type="text" id="username" name="username" class="form-input"
                            value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Enter your username" autocomplete="username" maxlength="100"
                            required aria-required="true" <?= isset($fieldError['username']) ? 'aria-describedby="username-error"' : '' ?>>
                    </div>
                    <?php if (isset($fieldError['username'])): ?>
                        <span class="form-error" id="username-error" role="alert"><?= htmlspecialchars($fieldError['username'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group <?= isset($fieldError['password']) ? 'form-group--error' : '' ?>">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="10" width="18" height="11" rx="2"/><path d="M7 10V7a5 5 0 0 1 10 0v3"/></svg>
                        </span>
                        <input type="password" id="password" name="password" class="form-input"
                            placeholder="Enter your password" autocomplete="current-password" maxlength="255"
                            required aria-required="true" <?= isset($fieldError['password']) ? 'aria-describedby="password-error"' : '' ?>>
                        <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password" aria-pressed="false">
                            <svg class="eye-icon eye-icon--show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-icon eye-icon--hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.9M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.2 0 2.3-.3 3.3-.7"/></svg>
                        </button>
                    </div>
                    <?php if (isset($fieldError['password'])): ?>
                        <span class="form-error" id="password-error" role="alert"><?= htmlspecialchars($fieldError['password'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn--primary btn--full" id="submitBtn">
                    <span class="btn__text">Sign In</span><span class="btn__spinner" aria-hidden="true"></span>
                </button>
            </form>
            <p class="login-note">Protected with secure, database-backed sign in.</p>
        </section>
    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
