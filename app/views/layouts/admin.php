<?php
/**
 * ADMIN LAYOUT - sidebar + top bar wrapped around the admin page.
 * Used by: AdminController (passes 'admin' as the layout name).
 */

// Build the initials (e.g. "A. Fernando" -> "AF") shown in the avatar circle
$initials = '';
foreach (preg_split('/\s+/', trim($user['name'])) as $part) {
    $initials .= mb_strtoupper(mb_substr($part, 0, 1));
}
$initials = mb_substr($initials, 0, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? '') ?> | Laboratory Lending System</title>
    <link href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body class="app-body">
<div class="app-shell">

    <!-- SIDEBAR: logo and navigation links -->

    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <span class="sidebar-logo">&#127891;</span>
            <div>
                <div class="sidebar-brand-title">Lab Equipment</div>
                <div class="sidebar-brand-sub">Lending System</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="<?= url('admin/dashboard') ?>" class="sidebar-link active">Dashboard</a>
        </nav>
    </aside>

    <!-- MAIN COLUMN -->
    <div class="app-main">
        <!-- TOP BAR: breadcrumb, logged-in user, Log Out -->
        <header class="app-topbar">
            <nav aria-label="breadcrumb" class="small">
                <span class="text-muted">Home</span> &rsaquo; <strong><?= e($crumb ?? 'Dashboard') ?></strong>
            </nav>
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="user-avatar"><?= e($initials) ?></span>
                    <div class="lh-sm">
                        <div class="fw-semibold small"><?= e($user['name']) ?></div>
                        <div class="text-muted topbar-role">Administrator</div>
                    </div>
                </div>
                <form action="<?= url('logout') ?>" method="POST" class="mb-0">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-underline">Log Out</button>
                </form>
            </div>
        </header>

        <!-- PAGE CONTENT: the admin page view is inserted here -->
        <main class="app-content">
            <?= $content ?>
        </main>
    </div>
</div>

<script src="<?= asset('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
