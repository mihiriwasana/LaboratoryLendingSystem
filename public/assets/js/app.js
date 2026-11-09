/*
 * Laboratory Lending System - shared client-side scripts
 *
 * Sections in this file:
 *   1. ALERTS (login + admin pages)  - success messages close themselves
 *   2. ADMIN PAGE                    - live search (also used by Manage Borrowers), Reset Password + Activate/Deactivate pop-ups
 *   3. LOGIN + ADMIN FORMS           - required-field validation, double-submit protection
 *   4. MANAGE EQUIPMENT PAGE         - filters apply on change, Loan Details pop-up

 */

document.addEventListener('DOMContentLoaded', function () {

    // ===== SECTION 1: ALERTS =====
    // Auto-dismiss alerts that opt in, e.g. data-auto-dismiss="5000" (ms)
    document.querySelectorAll('.alert[data-auto-dismiss]').forEach(function (el) {
        setTimeout(function () {
            bootstrap.Alert.getOrCreateInstance(el).close();
        }, parseInt(el.dataset.autoDismiss, 10) || 5000);
    });

    // ===== SECTION 2: ADMIN PAGE =====

    // Live search: hide table rows that don't match what is typed
        // Works on the admin (staff table), borrowers and equipment pages

const searchInput = document.getElementById('staff-search') || document.getElementById('borrower-search') || document.getElementById('equipment-search');
const staffRows = document.querySelectorAll('#staff-table tr[data-search], #borrower-table tr[data-search], #equipment-table tr[data-search]');
const noMatchRow = document.getElementById('no-match-row');

/*admin search scenario */
if (searchInput && staffRows.length) {
    searchInput.addEventListener('input', function () {
        const term = searchInput.value.trim().toLowerCase();
        let visible = 0;

        staffRows.forEach(function (row) {
            const isMatch = row.dataset.search.includes(term);
            row.hidden = !isMatch;
            if (isMatch) visible++;
        });

        noMatchRow.hidden = visible !== 0;   // show "no match" message when nothing is left
    });
}

 
    // Reset-password modal: fill in which staff member it is for
    const resetModal = document.getElementById('resetModal');
    if (resetModal) {
        resetModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            document.getElementById('reset-user-id').value = trigger.dataset.userId;
            document.getElementById('reset-user-name').textContent = trigger.dataset.userName;
            const input = document.getElementById('reset-password');
            input.value = '';
            input.closest('form').classList.remove('was-validated');
        });
        resetModal.addEventListener('shown.bs.modal', function () {
            document.getElementById('reset-password').focus();
        });
    }

    // Activate / Deactivate pop-up: fill in the name and the wording
const statusModal = document.getElementById('statusModal');
if (statusModal) {
    statusModal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;                 // the button that was clicked
        const deactivating = trigger.dataset.active === '1';
        const verb = deactivating ? 'Deactivate' : 'Activate';

        document.getElementById('status-user-id').value = trigger.dataset.userId;
        document.getElementById('status-title').textContent = verb;
        document.getElementById('status-submit').textContent = verb;
        document.getElementById('status-message').textContent = deactivating
            ? trigger.dataset.userName + ' will no longer be able to log in. Do you want to continue?'
            : trigger.dataset.userName + ' will be able to log in again. Do you want to continue?';
    });
}
    // ===== SECTION 4: MANAGE EQUIPMENT PAGE =====
    // Category / availability drop-downs apply the filter as soon as they change
    document.querySelectorAll('select[data-auto-submit]').forEach(function (select) {
        select.addEventListener('change', function () {
            select.form.submit();
        });
    });
    
    // Search box: when the text is cleared (backspace or the little x), reload to show the full list again
    const equipmentSearch = document.querySelector('input[data-auto-clear]');
    if (equipmentSearch) {
        equipmentSearch.addEventListener('input', function () {
            // defaultValue = what the page was loaded with; only reload if that was a real search
            if (equipmentSearch.value.trim() === '' && equipmentSearch.defaultValue.trim() !== '') {
                equipmentSearch.form.submit();
            }
        });
    }


    // Loan Details pop-up: fill in the borrower and loan information of the clicked item (UC07)
    const loanModal = document.getElementById('loanModal');
    if (loanModal) {
        loanModal.addEventListener('show.bs.modal', function (event) {
            const t = event.relatedTarget;   // the "View Loan" button that was clicked
            document.getElementById('loan-equipment').textContent = t.dataset.equipment;
            document.getElementById('loan-holder').textContent    = t.dataset.holder;
            document.getElementById('loan-issued').textContent    = t.dataset.issued;
            document.getElementById('loan-due').textContent       = t.dataset.due;
            document.getElementById('loan-status').textContent    = t.dataset.status;
        });
    }





    // ===== SECTION 3: LOGIN + ADMIN FORMS =====
    // Bootstrap client-side validation for any form with .needs-validation
    document.querySelectorAll('form.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                // Prevent double submission
                const button = form.querySelector('button[type="submit"]');
                if (button) {
                    button.disabled = true;
                    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Please wait...';
                }
            }
            form.classList.add('was-validated');
        });
    });

});
