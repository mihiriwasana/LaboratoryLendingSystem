<?php
/**
 * staff LAYOUT - sidebar + top bar wrapped around the staff page.
 * Used by: StaffDashBoardController
 
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
                <?php
        // Sidebar menu: key => [label, page path (null = not built yet), icon drawing]
        $menu = [
            'dashboard' => ['Dashboard',        'staff/dashboard', '<path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/>'],
            'borrowers' => ['Borrowers',        'staff/borrowers', '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7"/><path d="M18 14.3c2 .8 3.5 2.6 3.5 5.7"/>'],
            'equipment' => ['Equipment',        'staff/equipment','<path d="M9 3h6"/> <path d="M10 3v6L4.5 19a1.5 1.5 0 0 0 1.3 2h12.4a1.5 1.5 0 0 0 1.3-2L14 9V3"/>'],              
            'issue'     => ['Issue Loan',       null,              '<path d="M12 5v14M5 12h14"/>'],
            'history'   => ['Borrower History', null,              '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'],
            'notify'    => ['Notifications',    null,              '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>'],
            'reports'   => ['Reports',          null,              '<path d="M5 20V10M12 20V4M19 20v-7"/>'],
        ];
        ?>
        <nav class="sidebar-nav">
            <?php foreach ($menu as $key => [$label, $path, $icon]): ?>
                <?php $svg = '<svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icon . '</svg>'; ?>
                <?php if ($path): ?>
                    <a href="<?= url($path) ?>" class="sidebar-link <?= ($active ?? '') === $key ? 'active' : '' ?>"><?= $svg ?><span><?= e($label) ?></span></a>
                <?php else: ?>
                    <?php /* Page not built yet: shown greyed-out */ ?>
                    <span class="sidebar-link disabled" title="Coming soon" aria-disabled="true"><?= $svg ?><span><?= e($label) ?></span></span>
                <?php endif; ?>
            <?php endforeach; ?>
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
                        <div class="text-muted topbar-role">Laboratory Staff</div>
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
