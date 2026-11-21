<?php
require_once __DIR__ . '/includes/session.php';

initSession();
requireAuth();

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

$username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$initial = htmlspecialchars(strtoupper(substr($_SESSION['username'], 0, 1)), ENT_QUOTES, 'UTF-8');
$pageTitle = 'Welcome — Luma';
$bodyClass = 'page-dashboard';
include __DIR__ . '/includes/header.php';
?>

<div class="dashboard-layout">
    <nav class="dashboard-nav" aria-label="Main navigation">
        <a class="dashboard-nav__brand" href="dashboard.php" aria-label="Luma home">
            <span class="nav-logo" aria-hidden="true"><span class="nav-logo__dot"></span></span>
            <span class="dashboard-nav__title">LUMA</span>
        </a>
        <div class="dashboard-nav__right">
            <span class="nav-user" aria-label="Signed in as <?= $username ?>">
                <span class="nav-user__avatar" aria-hidden="true"><?= $initial ?></span>
                <span class="nav-user__name"><?= $username ?></span>
            </span>
            <a href="logout.php" class="btn btn--outline btn--sm" id="logoutBtn">Sign out</a>
        </div>
    </nav>

    <main class="dashboard-main" id="main-content">
        <section class="welcome-card" aria-labelledby="welcome-heading">
            <div class="welcome-orbit" aria-hidden="true">
                <span class="welcome-orbit__sparkle">✦</span>
                <span class="welcome-orbit__core"></span>
            </div>
            <p class="welcome-card__eyebrow">YOUR SPACE</p>
            <h1 class="welcome-card__heading" id="welcome-heading">Welcome back,<br><span class="highlight"><?= $username ?></span></h1>
            <p class="welcome-card__sub">Lovely to have you here.</p>
        </section>
    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
