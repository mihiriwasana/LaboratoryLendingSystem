<?php
/**
 * STAFF DASHBOARD view (shown by StaffDashboardController::dashboard), inside layouts/staff.php.
 *
 * Sections, top to bottom:
 *   1. Heading + Issue Loan button
 *   2. Summary cards (Available / On Loan / Due Soon / Overdue)
 *   3. Overdue Loans table
 *   4. Due Soon table
 */
?>
<?php /* ===== 1. Heading + Issue Loan button (the button is built with the Issue Loan page) ===== */ ?>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back, <?= e($user['name']) ?>. Here is today's lending overview.</p>
    </div>
    <a href="#" class="btn btn-brand px-4 disabled" aria-disabled="true">+ Issue Loan</a>
</div>

<?php /* ===== 2. Summary cards ===== */ ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="stat-card stat-available">
        <div class="stat-label">Available</div><div class="stat-value"><?= $availableCount ?></div>
    </div></div>
    <div class="col-6 col-lg-3"><div class="stat-card stat-onloan">
        <div class="stat-label">On Loan</div><div class="stat-value"><?= $onLoanCount ?></div>
    </div></div>
    <div class="col-6 col-lg-3"><div class="stat-card stat-duesoon">
        <div class="stat-label">Due Soon (<?= StaffDashboardController::DUE_SOON_DAYS ?> days)</div>
        <div class="stat-value"><?= $dueSoonCount ?></div>
    </div></div>
    <div class="col-6 col-lg-3"><div class="stat-card stat-overdue">
        <div class="stat-label">Overdue</div><div class="stat-value text-danger"><?= $overdueCount ?></div>
    </div></div>
</div>

<?php /* ===== 3. Overdue Loans table ===== */ ?>
<h2 class="section-title">Overdue Loans</h2>
<div class="table-card mb-4">
    <table class="table align-middle mb-0">
        <thead>
            <tr><th>Borrower</th><th>Equipment</th><th>Due Date</th><th>Days Overdue</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (!$overdueLoans): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No overdue loans.</td></tr>
        <?php endif; ?>
        <?php foreach ($overdueLoans as $l): ?>
            <tr>
                <td><?= e($l['full_name']) ?> (<?= e($l['student_staff_id']) ?>)</td>
                <td><?= e($l['equipment_name']) ?> (<?= e($l['equipment_code']) ?>)</td>
                <td><?= e($l['due_date']) ?></td>
                <td><span class="status-pill pill-overdue"><?= (int) $l['days_overdue'] ?> days</span></td>
                <td class="small">
                    <?php /* View and Send Reminder are built with their own use cases (UC12 / UC13) */ ?>
                    <span class="text-muted">View</span> &nbsp; <span class="text-muted">Send Reminder</span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php /* ===== 4. Due Soon table ===== */ ?>
<h2 class="section-title">Due Soon (next <?= StaffDashboardController::DUE_SOON_DAYS ?> days)</h2>
<div class="table-card mb-4">
    <table class="table align-middle mb-0">
        <thead>
            <tr><th>Borrower</th><th>Equipment</th><th>Due Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (!$dueSoonLoans): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No loans are due soon.</td></tr>
        <?php endif; ?>
        <?php foreach ($dueSoonLoans as $l): ?>
            <tr>
                <td><?= e($l['full_name']) ?> (<?= e($l['student_staff_id']) ?>)</td>
                <td><?= e($l['equipment_name']) ?> (<?= e($l['equipment_code']) ?>)</td>
                <td><span class="status-pill pill-duesoon"><?= e($l['due_date']) ?></span></td>
                <td class="small"><span class="text-muted">View</span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
