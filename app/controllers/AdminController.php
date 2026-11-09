<?php
/**
 * ADMIN PAGE controller - Manage Staff Accounts.
 *
 * Requirements: UC02 Manage Staff Accounts, FR04 (create, view, search, edit, activate,
 * deactivate, reset password; unique email). Also NFR02 (server-side validation, prepared
 * statements), NFR04 (role checked on every action), NFR06 (messages next to the field),
 * NFR10 (accounts are deactivated, never deleted).
 *
 * Page: /admin/dashboard   View: app/views/admin/dashboard.php   Layout: layouts/admin.php
 *
 * Sections in this file:
 *   1. DASHBOARD PAGE       - staff list, summary cards, search, Add / Edit form
 *   2. ADD / EDIT STAFF     - save the form (create or update)
 *   3. ACTIVATE / DEACTIVATE
 *   4. RESET PASSWORD
 */
class AdminController extends Controller
{
    // Minimum length for staff passwords
    const MIN_PASSWORD_LENGTH = 8;

    /* =====================================================================
     * SECTION 1: DASHBOARD PAGE
     * ===================================================================== */

    /** Staff list, summary cards, search, and the Add / Edit form. */
    public function dashboard(?array $form = null): void
    {
        // Show the page. $form is only passed in when saving failed validation (to show errors)
        $this->requireRole(User::ROLE_ADMIN);

        $users = new User();
        $search = trim($_GET['q'] ?? '');

        // Decide whether the Add / Edit form is open
        if ($form === null) {
            $form = ['open' => false, 'id' => 0, 'name' => '', 'email' => '', 'errors' => []];
            if (isset($_GET['add'])) {
                $form['open'] = true;
            } elseif (isset($_GET['edit'])) {
                $staff = $users->findStaffById((int) $_GET['edit']);
                if ($staff) {
                    $form = ['open' => true, 'id' => (int) $staff['user_id'],
                             'name' => $staff['full_name'], 'email' => $staff['email'], 'errors' => []];
                }
            }
        }

        $this->view('admin/dashboard', [
            'title'   => 'Administrator Dashboard',
            'user'    => Auth::user(),
            'staff'   => $users->listStaff($search),
            'counts'  => $users->staffCounts(),
            'search'  => $search,
            'form'    => $form,
            'success' => flash('success'),
            'error'   => flash('error'),
        ], 'admin');
    }

    /* =====================================================================
     * SECTION 2: ADD / EDIT STAFF ACCOUNT
     * ===================================================================== */

    /** Create a new staff account, or update an existing one. */
    public function saveStaff(): void
    {
        // Validate the form; on errors re-show it, otherwise create (no user_id) or update (has user_id)
        $this->requireRole(User::ROLE_ADMIN);
        $this->requireCsrf();

        $users = new User();
        $id = (int) ($_POST['user_id'] ?? 0);
        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];

        if ($id > 0 && !$users->findStaffById($id)) {
            flash('error', 'That staff account no longer exists.');
            $this->redirect('admin/dashboard');
        }

        if ($name === '') {
            $errors['full_name'] = 'Full name is required.';
        } elseif (mb_strlen($name) > 100) {
            $errors['full_name'] = 'Full name must be 100 characters or fewer.';
        }

        if ($email === '') {
            $errors['email'] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif ($users->emailTaken($email, $id)) {
            $errors['email'] = 'This email address is already registered.';
        }

        if ($id === 0) {
            if ($password === '') {
                $errors['password'] = 'Initial password is required.';
            } elseif (strlen($password) < self::MIN_PASSWORD_LENGTH) {
                $errors['password'] = 'Password must be at least ' . self::MIN_PASSWORD_LENGTH . ' characters.';
            }
        }

        if ($errors) {
            $this->dashboard(['open' => true, 'id' => $id, 'name' => $name, 'email' => $email, 'errors' => $errors]);
            return;
        }

        if ($id > 0) {
            $users->updateStaff($id, $name, $email);
            flash('success', 'Staff account updated.');
        } else {
            $users->create($name, $email, $password, User::ROLE_STAFF);
            flash('success', 'Staff account created.');
        }
        $this->redirect('admin/dashboard');
    }

    /* =====================================================================
     * SECTION 3: ACTIVATE / DEACTIVATE
     * ===================================================================== */

    /** Activate or deactivate a staff account. */
    public function toggleStatus(): void
    {
        // Flip the account between Active and Inactive (inactive staff cannot log in)
        $this->requireRole(User::ROLE_ADMIN);
        $this->requireCsrf();

        $users = new User();
        $staff = $users->findStaffById((int) ($_POST['user_id'] ?? 0));
        if (!$staff) {
            flash('error', 'That staff account no longer exists.');
            $this->redirect('admin/dashboard');
        }

        $activate = !$staff['is_active'];
        $users->setActive((int) $staff['user_id'], $activate);
        flash('success', $staff['full_name'] . ($activate ? ' has been activated.' : ' has been deactivated.'));
        $this->redirect('admin/dashboard');
    }

    /* =====================================================================
     * SECTION 4: RESET PASSWORD
     * ===================================================================== */

    /** Set a new password for a staff account. */
    public function resetPassword(): void
    {
        // Check the new password length, then store it hashed
        $this->requireRole(User::ROLE_ADMIN);
        $this->requireCsrf();

        $users = new User();
        $staff = $users->findStaffById((int) ($_POST['user_id'] ?? 0));
        $password = $_POST['password'] ?? '';

        if (!$staff) {
            flash('error', 'That staff account no longer exists.');
        } elseif (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            flash('error', 'Password must be at least ' . self::MIN_PASSWORD_LENGTH . ' characters.');
        } else {
            $users->resetPassword((int) $staff['user_id'], $password);
            flash('success', 'Password reset for ' . $staff['full_name'] . '.');
        }
        $this->redirect('admin/dashboard');
    }

    private function requireCsrf(): void
    {
        // Stop the request if the form's security token is missing or wrong
        if (!verify_csrf()) {
            flash('error', 'Your session has expired. Please try again.');
            $this->redirect('admin/dashboard');
        }
    }
}
