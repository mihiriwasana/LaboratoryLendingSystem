<?php
/**
 * MANAGE BORROWERS view (shown by BorrowerController::index), inside layouts/staff.php.
 *
 * Sections, top to bottom:
 *   1. Heading + alert messages
 *   2. Search box + "Register Borrower" button
 *   3. Borrower table (Edit / History)
 *   4. Register / Edit borrower form
 */
$isEdit = $form['id'] > 0;
$err = $form['errors'];
?>
<?php /* ===== 1. Heading + alert messages ===== */ ?>
<h1 class="page-title">Manage Borrowers</h1>
<p class="page-subtitle">Register and update students and staff who borrow equipment</p>

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

<?php /* ===== 2. Search box + Register button ===== */ ?>
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
    <form action="<?= url('staff/borrowers') ?>" method="GET" class="d-flex gap-2 flex-grow-1" style="max-width: 560px;">
        <input type="search" id="borrower-search" name="q" class="form-control"
               placeholder="Search by name or student/staff ID" autocomplete="off"
               value="<?= e($search) ?>" aria-label="Search borrowers">
        <button type="submit" class="btn btn-outline-secondary px-5">Search</button>
    </form>
    <a href="<?= url('staff/borrowers?add=1') ?>#borrower-form" class="btn btn-brand px-4">+ Register Borrower</a>
</div>

<?php /* ===== 3. Borrower table ===== */ ?>
<div class="table-card mb-4">
    <table id="borrower-table" class="table table-hover align-middle mb-0">
        <thead>
            <tr><th>Student/Staff ID</th><th>Full Name</th><th>Email</th><th>Contact Number</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (!$borrowers): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">
                <?= $search !== '' ? 'No borrowers match your search.' : 'No borrowers yet. Click "Register Borrower" to add one.' ?>
            </td></tr>
        <?php endif; ?>
        <?php foreach ($borrowers as $b): ?>
            <tr data-search="<?= e(strtolower($b['full_name'] . ' ' . $b['student_staff_id'])) ?>">
                <td><?= e($b['student_staff_id']) ?></td>
                <td><?= e($b['full_name']) ?></td>
                <td><?= e($b['email']) ?></td>
                <td><?= e($b['contact_number'])?> </td>
                <td>
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <a class="btn btn-link p-0" href="<?= url('staff/borrowers?edit=' . (int) $b['borrower_id']) ?>#borrower-form">Edit</a>
                        <?php /* History opens the Borrower History page (UC12) */ ?>
                        <a class="btn btn-link p-0" href="<?=url ('staff/history?borrower='.(int) $b['borrower_id']) ?>">History</a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr id="no-match-row" hidden>
            <td colspan="5" class="text-center text-muted py-4">No borrowers match your search.</td>
        </tr>
        </tbody>
    </table>
</div>

<?php /* ===== 4. Register / Edit form (only shown after Register, Edit, or a failed save) ===== */ ?>
<?php if ($form['open']): ?>
<div class="form-card" id="borrower-form">
    <h2 class="form-card-title"><?= $isEdit ? 'Edit Borrower' : 'Register Borrower' ?></h2>
    <form action="<?= url('staff/borrowers/save') ?>" method="POST" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="borrower_id" value="<?= (int) $form['id'] ?>">

        <div class="row g-3">
            <div class="col-md-6">
                <label for="student_staff_id" class="form-label required">Student/Staff ID</label>
                <input type="text" class="form-control <?= isset($err['student_id']) ? 'is-invalid' : '' ?>"
                       id="student_staff_id" name="student_staff_id" maxlength="20" required
                       value="<?= e($form['student_id']) ?>">
                <div class="invalid-feedback field-error">
                    <?= e($err['student_id'] ?? 'Please enter the student/staff ID.') ?>
                </div>
            </div>

            <div class="col-md-6">
                <label for="full_name" class="form-label required">Full Name</label>
                <input type="text" class="form-control <?= isset($err['name']) ? 'is-invalid' : '' ?>"
                       id="full_name" name="full_name" maxlength="100" required placeholder="Enter full name"
                       value="<?= e($form['name']) ?>">
                <div class="invalid-feedback field-error">
                    <?= e($err['name'] ?? 'Please enter the full name.') ?>
                </div>
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label required">Email address</label>
                <input type="email" class="form-control <?= isset($err['email']) ? 'is-invalid' : '' ?>"
                       id="email" name="email" maxlength="150" required placeholder="name@university.lk"
                       value="<?= e($form['email']) ?>">
                <div class="invalid-feedback field-error">
                    <?= e($err['email'] ?? 'Please enter a valid email address.') ?>
                </div>
            </div>

            <div class="col-md-6">
                <label for="contact_number" class="form-label required">Contact Number</label>
                <input type="text" class="form-control <?= isset($err['contact']) ? 'is-invalid' : '' ?>"
                       id="contact_number" name="contact_number" maxlength="12" required pattern="(0[0-9]{9}|\+94[0-9]{9})" placeholder="e.g. 0711234567"
                       value="<?= e($form['contact']) ?>">
                <div class="invalid-feedback field-error">
                    <?= e($err['contact'] ?? 'Enter a valid contact number, e.g. 0771234567.') ?>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-brand px-4">Save</button>
            <a href="<?= url('staff/borrowers') ?>" class="btn btn-outline-secondary px-4 d-flex align-items-center">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>
