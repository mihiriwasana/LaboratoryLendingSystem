<?php
/**
 * ADMIN PAGE view (shown by AdminController::dashboard), inside layouts/admin.php.
 *
 * Sections, top to bottom:
 *   1. Page heading + alert messages
 *   2. Summary cards (Total / Active / Inactive)
 *   3. Search box + "Add Staff Account" button
 *   4. Staff table (Edit, Deactivate/Activate, Reset Password)
 *   5. Add / Edit staff form
 *   6. Reset Password pop-up (modal)
 */
$isEdit = $form['id'] > 0;
$err = $form['errors'];
?>
<?php /* ===== 1. Page heading + alert messages ===== */ ?>
<h1 class="page-title">Administrator Dashboard</h1>
<p class="page-subtitle">Welcome back, <?= e($user['name']) ?>. Manage Laboratory Staff accounts below.</p>

<?php if ($error): ?>
    <div class="alert alert-danger alert-icon alert-dismissible fade show py-2 small" role="alert">
        <span><?= e($error) ?></span>
        <button type="button" class="btn-close small" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success alert-icon alert-dismissible fade show py-2 small" data-auto-dismiss="5000" role="alert">
        <span><?= e($success) ?></span>
        <button type="button" class="btn-close small" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php /* ===== 2. Summary cards ===== */ ?>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="stat-card stat-total">
        <div class="stat-label">Total Staff</div><div class="stat-value"><?= $counts['total'] ?></div>
    </div></div>
    <div class="col-md-4"><div class="stat-card stat-active">
        <div class="stat-label">Active</div><div class="stat-value"><?= $counts['active'] ?></div>
    </div></div>
    <div class="col-md-4"><div class="stat-card stat-inactive">
        <div class="stat-label">Inactive</div><div class="stat-value"><?= $counts['inactive'] ?></div>
    </div></div>
</div>

<?php /* ===== 3. Search box + Add button ===== */ ?>
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
    <form action="<?= url('admin/dashboard') ?>" method="GET" class="d-flex gap-2 flex-grow-1" style="max-width: 560px;">
        <input type="search" id="staff-search" name="q" class="form-control" placeholder="Search by name or email"
              autocomplete="off" value="<?= e($search) ?>" aria-label="Search staff">
        <button type="submit" class="btn btn-outline-secondary px-5">Search</button>
    </form>
    <a href="<?= url('admin/dashboard?add=1') ?>#staff-form" class="btn btn-brand px-4">+ Add Staff Account</a>
</div>

<?php /* ===== 4. Staff table ===== */ ?>
<div class="table-card mb-4">
    <table id ="staff-table" class="table table-hover align-middle mb-0">
        <thead>
            <tr><th>Full Name</th><th>Email</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (!$staff): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">
                <?= $search !== '' ? 'No staff accounts match your search.' : 'No staff accounts yet. Click "Add Staff Account" to create one.' ?>
            </td></tr>
        <?php endif; ?>
        <?php foreach ($staff as $s): ?>
            <tr data-search="<?= e(strtolower($s['full_name'] . ' ' . $s['email'])) ?>">
                <td><?= e($s['full_name']) ?></td>
                <td><?= e($s['email']) ?></td>
                <td>
                    <span class="status-pill <?= $s['is_active'] ? 'status-active' : 'status-inactive' ?>">
                        <?= $s['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
                <td>
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <a class="btn btn-link p-0" href="<?= url('admin/dashboard?edit=' . (int) $s['user_id']) ?>#staff-form">Edit</a>

        <button type="button" class="btn btn-link p-0 action-status" data-bs-toggle="modal"
        data-bs-target="#statusModal"
        data-user-id="<?= (int) $s['user_id'] ?>"
        data-user-name="<?= e($s['full_name']) ?>"
        data-active="<?= $s['is_active'] ? '1' : '0' ?>"><?= $s['is_active'] ? 'Deactivate' : 'Activate' ?>
        </button>


                        <button type="button" class="btn btn-link p-0 " data-bs-toggle="modal"
                                data-bs-target="#resetModal"
                                data-user-id="<?= (int) $s['user_id'] ?>"
                                data-user-name="<?= e($s['full_name']) ?>">Reset Password</button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr id="no-match-row" hidden>
            <td colspan="4" class="text-center text-muted py-4">No staff accounts match your search.</td>
        </tr>
        </tbody>
    </table>
</div>

<?php /* ===== 5. Add / Edit staff form (only shown after clicking Add or Edit) ===== */ ?>
<?php if ($form['open']): ?>
<div class="form-card" id="staff-form">
    <h2 class="form-card-title"><?= $isEdit ? 'Edit Staff Account' : 'Add Staff Account' ?></h2>
    <form action="<?= url('admin/staff/save') ?>" method="POST" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="user_id" value="<?= (int) $form['id'] ?>">

        <div class="row g-3">
            <div class="col-md-6">
                <label for="full_name" class="form-label required">Full Name</label>
                <input type="text" class="form-control <?= isset($err['full_name']) ? 'is-invalid' : '' ?>"
                       id="full_name" name="full_name" maxlength="100" required
                       value="<?= e($form['name']) ?>">
                <div class="invalid-feedback field-error">
                    <?= e($err['full_name'] ?? 'Please enter the full name.') ?>
                </div>
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label required">Email address</label>
                <input type="email" class="form-control <?= isset($err['email']) ? 'is-invalid' : '' ?>"
                       id="email" name="email" maxlength="150" required
                       value="<?= e($form['email']) ?>">
                <div class="invalid-feedback field-error">
                    <?= e($err['email'] ?? 'Please enter a valid email address.') ?>
                </div>
            </div>

            <?php if (!$isEdit): ?>
            <div class="col-md-6">
                <label for="password" class="form-label required">Initial Password</label>
                <input type="password" class="form-control <?= isset($err['password']) ? 'is-invalid' : '' ?>"
                       id="password" name="password" minlength="8" required autocomplete="new-password">
                <div class="invalid-feedback field-error">
                    <?= e($err['password'] ?? 'Password must be at least 8 characters.') ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-brand px-4">Save</button>
            <a href="<?= url('admin/dashboard') ?>" class="btn btn-outline-secondary px-4 d-flex align-items-center">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<?php /* ===== 6. Reset Password pop-up (filled in by app.js) ===== */ ?>
<div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content needs-validation" action="<?= url('admin/staff/reset-password') ?>" method="POST" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" id="reset-user-id">
            <div class="modal-header">
                <h2 class="modal-title h5" id="resetModalLabel">Reset Password</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Set a new password for <strong id="reset-user-name"></strong>.</p>
                <label for="reset-password" class="form-label required">New Password</label>
                <input type="password" class="form-control" id="reset-password" name="password"
                       minlength="8" required autocomplete="new-password">
                <div class="invalid-feedback field-error">Password must be at least 8 characters.</div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-brand px-4">Reset Password</button>
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<?php /* ===== 7. Activate / Deactivate pop-up (filled in by app.js) ===== */ ?>
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" action="<?= url('admin/staff/status') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" id="status-user-id">
            <div class="modal-header">
                <h2 class="modal-title h5" id="statusModalLabel"><span id="status-title">Deactivate</span> Staff Account</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="status-message"></p>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-brand px-4" id="status-submit">Deactivate</button>
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

