<?php
/**
 * MANAGE EQUIPMENT view (shown by EquipmentController::index), inside layouts/staff.php.
 *
 * Sections, top to bottom:
 *   1. Heading + alert messages
 *   2. Filters (category, availability, search) + "Register Equipment" button
 *   3. Equipment table (Edit / View Loan)
 *   4. Register / Edit equipment form
 *   5. Loan Details pop-up (modal) for items on loan
 */
$isEdit = $form['id'] > 0;
$err = $form['errors'];
?>
<?php /* ===== 1. Heading + alert messages ===== */ ?>
<h1 class="page-title">Manage Laboratory Equipment</h1>
<p class="page-subtitle">Availability is updated by the system when items are issued or returned</p>

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

<?php /* ===== 2. Filters + Register button (the two drop-downs apply themselves; see app.js) ===== */ ?>
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
    <form action="<?= url('staff/equipment') ?>" method="GET" class="d-flex flex-wrap gap-2 flex-grow-1" style="max-width: 760px;">
        <select name="category" class="form-select w-auto" data-auto-submit aria-label="Filter by category">
            <option value="">All categories</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= e($c) ?>" <?= $category === $c ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="availability" class="form-select w-auto" data-auto-submit aria-label="Filter by availability">
            <option value="">All availability</option>
            <option value="Available" <?= $availability === 'Available' ? 'selected' : '' ?>>Available</option>
            <option value="OnLoan" <?= $availability === 'OnLoan' ? 'selected' : '' ?>>On Loan</option>
        </select>
        <input type="search" id="equipment-search" name="q" class="form-control w-auto flex-grow-1" placeholder="Search by code or name" data-auto-clear
               autocomplete="off" value="<?= e($q) ?>" aria-label="Search equipment">
    </form>
    <a href="<?= url('staff/equipment?add=1') ?>#equipment-form" class="btn btn-brand px-4">+ Register Equipment</a>
</div>

<?php /* ===== 3. Equipment table ===== */ ?>
<div class="table-card mb-4">
    <table id="equipment-table" class="table table-hover align-middle mb-0">
        <thead>
            <tr><th>Code</th><th>Name</th><th>Category</th><th>Condition</th><th>Availability</th>
                <th>Current Holder</th><th>Loan Days</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (!$items): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">
                <?= ($q !== '' || $category !== '' || $availability !== '')
                    ? 'No equipment matches your search or filters.'
                    : 'No equipment yet. Click "Register Equipment" to add one.' ?>
            </td></tr>
        <?php endif; ?>
        <?php foreach ($items as $i): $onLoan = $i['availability_status'] === 'OnLoan'; ?>
            <tr data-search="<?= e(strtolower($i['equipment_code']) . ' ' . strtolower($i['name'])) ?>">
                <td><?= e($i['equipment_code']) ?></td>
                <td><?= e($i['name']) ?></td>
                <td><?= e($i['category']) ?></td>
                <td><?= e($i['condition_status']) ?></td>
                <td>
                    <span class="status-pill <?= $onLoan ? 'pill-onloan' : 'pill-available' ?>">
                        <?= $onLoan ? 'On Loan' : 'Available' ?>
                    </span>
                </td>
                <td><?= $onLoan && $i['holder_name'] ? e($i['holder_name']) : '&mdash;' ?></td>
                <td><?= (int) $i['default_loan_days'] ?></td>
                <td>
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <a class="btn btn-link p-0" href="<?= url('staff/equipment?edit=' . (int) $i['equipment_id']) ?>#equipment-form">Edit</a>
                        <?php if ($onLoan && $i['holder_name']): ?>
                            <button type="button" class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#loanModal"
                                    data-equipment="<?= e($i['name'] . ' (' . $i['equipment_code'] . ')') ?>"
                                    data-holder="<?= e($i['holder_name'] . ' (' . $i['holder_id'] . ')') ?>"
                                    data-issued="<?= e($i['issue_date']) ?>"
                                    data-due="<?= e($i['due_date']) ?>"
                                    data-status="<?= e($i['loan_status']) ?>">View Loan</button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr id="no-match-row" hidden>
            <td colspan="8" class="text-center text-muted py-4">No equipment matches your search.</td>
        </tr>

        </tbody>
    </table>
</div>

<?php /* ===== 4. Register / Edit form (only shown after Register, Edit, or a failed save.) ===== */ ?>
<?php if ($form['open']): ?>
<div class="form-card" id="equipment-form">
    <h2 class="form-card-title"><?= $isEdit ? 'Edit Equipment' : 'Register Equipment' ?></h2>
    <form action="<?= url('staff/equipment/save') ?>" method="POST" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="equipment_id" value="<?= (int) $form['id'] ?>">
        <input type="hidden" name="availability" value="<?= e($form['availability']) ?>">

        <div class="row g-3">
            <div class="col-md-6">
                <label for="equipment_code" class="form-label required">Equipment Code</label>
                <input type="text" class="form-control <?= isset($err['code']) ? 'is-invalid' : '' ?>"
                       id="equipment_code" name="equipment_code" maxlength="30" required placeholder="e.g. EQ-021"
                       value="<?= e($form['code']) ?>">
                <div class="invalid-feedback field-error">
                    <?= e($err['code'] ?? 'Please enter the equipment code.') ?>
                </div>
            </div>

            <div class="col-md-6">
                <label for="name" class="form-label required">Name</label>
                <input type="text" class="form-control <?= isset($err['name']) ? 'is-invalid' : '' ?>"
                       id="name" name="name" maxlength="100" required placeholder="Enter equipment name"
                       value="<?= e($form['name']) ?>">
                <div class="invalid-feedback field-error">
                    <?= e($err['name'] ?? 'Please enter the equipment name.') ?>
                </div>
            </div>

            <div class="col-md-6">
                <label for="category" class="form-label required">Category</label>
                <select class="form-select <?= isset($err['category']) ? 'is-invalid' : '' ?>" id="category" name="category" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= e($c) ?>" <?= $form['category'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback field-error">
                    <?= e($err['category'] ?? 'Please select a category.') ?>
                </div>
            </div>

            <div class="col-md-6">
                <label for="condition_status" class="form-label required">Condition</label>
                <select class="form-select <?= isset($err['condition']) ? 'is-invalid' : '' ?>" id="condition_status" name="condition_status" required>
                    <?php foreach ($conditions as $c): ?>
                        <option value="<?= e($c) ?>" <?= $form['condition'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback field-error">
                    <?= e($err['condition'] ?? 'Please select the condition.') ?>
                </div>
            </div>

            <div class="col-md-6">
                <label for="default_loan_days" class="form-label required">Default Loan Period (days)</label>
                <input type="number" class="form-control <?= isset($err['days']) ? 'is-invalid' : '' ?>"
                       id="default_loan_days" name="default_loan_days" min="1" max="365" step="1" required
                       value="<?= e($form['days']) ?>">
                <div class="invalid-feedback field-error">
                    <?= e($err['days'] ?? 'Enter the loan period as a whole number of days (1-365).') ?>
                </div>
            </div>

            <div class="col-md-6">
                <label for="availability_view" class="form-label">Availability (set by the system)</label>
                <input type="text" class="form-control" id="availability_view" disabled
                       value="<?= $form['availability'] === 'OnLoan' ? 'On Loan' : 'Available' ?>">
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-brand px-4">Save</button>
            <a href="<?= url('staff/equipment') ?>" class="btn btn-outline-secondary px-4 d-flex align-items-center">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<?php /* ===== 5. Loan Details pop-up (filled in by app.js) ===== */ ?>
<div class="modal fade" id="loanModal" tabindex="-1" aria-labelledby="loanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="loanModalLabel">Loan Details</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Equipment</dt><dd class="col-sm-8" id="loan-equipment"></dd>
                    <dt class="col-sm-4">Borrower</dt><dd class="col-sm-8" id="loan-holder"></dd>
                    <dt class="col-sm-4">Issued on</dt><dd class="col-sm-8" id="loan-issued"></dd>
                    <dt class="col-sm-4">Due date</dt><dd class="col-sm-8" id="loan-due"></dd>
                    <dt class="col-sm-4">Loan status</dt><dd class="col-sm-8 mb-0" id="loan-status"></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
