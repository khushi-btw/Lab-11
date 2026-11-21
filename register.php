<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';

loadLocalEnvironment();
initSession();

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$fieldError = [];
$username = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!is_string($submittedToken) || !verifyCsrf($submittedToken)) {
        $error = 'Your request expired. Refresh the page and try again.';
    } else {
        $usernameInput = $_POST['username'] ?? '';
        $passwordInput = $_POST['password'] ?? '';
        $confirmInput = $_POST['confirm_password'] ?? '';
        $username = is_string($usernameInput) ? trim($usernameInput) : '';
        $password = is_string($passwordInput) ? $passwordInput : '';
        $confirmPassword = is_string($confirmInput) ? $confirmInput : '';

        if ($username === '') {
            $fieldError['username'] = 'Username is required.';
        } elseif (!preg_match('/\A[a-zA-Z0-9][a-zA-Z0-9._-]{2,31}\z/', $username)) {
            $fieldError['username'] = 'Use 3–32 letters, numbers, dots, underscores, or hyphens.';
        }

        if ($password === '') {
            $fieldError['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $fieldError['password'] = 'Use at least 8 characters.';
        } elseif (strlen($password) > 72) {
            $fieldError['password'] = 'Password must be 72 bytes or fewer.';
        }

        if ($confirmPassword === '') {
            $fieldError['confirm_password'] = 'Please confirm your password.';
        } elseif ($password !== $confirmPassword) {
            $fieldError['confirm_password'] = 'Passwords do not match.';
        }

        if (!$fieldError) {
            try {
                $pdo = getDatabaseConnection();
                $check = $pdo->prepare('SELECT 1 FROM users WHERE username = :username LIMIT 1');
                $check->execute([':username' => $username]);

                if ($check->fetchColumn()) {
                    $fieldError['username'] = 'That username is already taken.';
                } else {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $insert = $pdo->prepare(
                        'INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)'
                    );
                    $insert->execute([
                        ':username' => $username,
                        ':password_hash' => $passwordHash,
                    ]);

                    $_SESSION['flash_success'] = 'Account created successfully. Please sign in.';
                    header('Location: login.php');
                    exit;
                }
            } catch (PDOException $e) {
                // The database unique constraint also protects concurrent requests.
                if ($e->getCode() === '23505') {
                    $fieldError['username'] = 'That username is already taken.';
                } else {
                    error_log('Account registration failed. SQLSTATE: ' . $e->getCode());
                    $error = 'We could not create your account right now. Please try again.';
                }
            } catch (RuntimeException $e) {
                $error = 'We could not create your account right now. Please try again.';
            }
        }
    }
}

$pageTitle = 'Create account — Luma';
$bodyClass = 'page-register';
$csrfToken = csrfToken();
include __DIR__ . '/includes/header.php';
?>

<div class="split-layout">
    <main class="login-panel" id="main-content">
        <section class="login-panel__inner" aria-labelledby="register-heading">
            <header class="login-panel__header">
                <span class="brand-badge">LUMA</span>
                <h1 class="login-panel__heading" id="register-heading">Create your account</h1>
                <p class="login-panel__subtext">A few details and you're all set.</p>
            </header>

            <?php if ($error): ?>
                <div class="alert alert--error" role="alert" aria-live="polite">
                    <span class="alert__icon" aria-hidden="true">!</span>
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="register.php" id="registerForm" aria-label="Create account">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-group <?= isset($fieldError['username']) ? 'form-group--error' : '' ?>">
                    <label class="form-label" for="username">Username</label>
                    <div class="input-wrapper">
                        <span class="input-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M5 21a7 7 0 0 1 14 0"/></svg>
                        </span>
                        <input type="text" id="username" name="username" class="form-input"
                            value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Choose a username" autocomplete="username" maxlength="32"
                            pattern="[A-Za-z0-9][A-Za-z0-9._-]{2,31}" required aria-required="true"
                            <?= isset($fieldError['username']) ? 'aria-describedby="username-error"' : '' ?>>
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
                            placeholder="Create a password" autocomplete="new-password" maxlength="72"
                            required aria-required="true" aria-describedby="password-hint<?= isset($fieldError['password']) ? ' password-error' : '' ?>">
                        <button type="button" class="toggle-password" id="togglePassword" data-password-toggle aria-label="Show password" aria-pressed="false">
                            <svg class="eye-icon eye-icon--show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-icon eye-icon--hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.9M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.2 0 2.3-.3 3.3-.7"/></svg>
                        </button>
                    </div>
                    <span class="form-hint" id="password-hint">At least 8 characters.</span>
                    <?php if (isset($fieldError['password'])): ?>
                        <span class="form-error" id="password-error" role="alert"><?= htmlspecialchars($fieldError['password'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group <?= isset($fieldError['confirm_password']) ? 'form-group--error' : '' ?>">
                    <label class="form-label" for="confirm_password">Confirm password</label>
                    <div class="input-wrapper">
                        <span class="input-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="10" width="18" height="11" rx="2"/><path d="M7 10V7a5 5 0 0 1 10 0v3"/><path d="m9 16 2 2 4-4"/></svg>
                        </span>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input"
                            placeholder="Enter your password again" autocomplete="new-password" maxlength="72"
                            required aria-required="true" <?= isset($fieldError['confirm_password']) ? 'aria-describedby="confirm-password-error"' : '' ?>>
                    </div>
                    <?php if (isset($fieldError['confirm_password'])): ?>
                        <span class="form-error" id="confirm-password-error" role="alert"><?= htmlspecialchars($fieldError['confirm_password'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn--primary btn--full" id="submitBtn">
                    <span class="btn__text">Create account</span><span class="btn__spinner" aria-hidden="true"></span>
                </button>
            </form>
            <p class="auth-switch">Already have an account? <a href="login.php">Sign in</a></p>
        </section>
    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
