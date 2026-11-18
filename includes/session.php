<?php
// includes/session.php
// Session management helpers.

// Secure session configuration
function initSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        // Harden cookies
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

// Check if user is authenticated; redirect to login if not
function requireAuth(): void
{
    initSession();
    if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
        header('Location: index.php');
        exit;
    }
}

// Generate or return the CSRF token for this session
function csrfToken(): string
{
    initSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate a submitted CSRF token
function verifyCsrf(string $submitted): bool
{
    initSession();
    $stored = $_SESSION['csrf_token'] ?? '';
    return hash_equals($stored, $submitted);
}
