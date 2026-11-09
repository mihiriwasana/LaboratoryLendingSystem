<?php
/**
 * MANAGE BORROWERS controller - Laboratory Staff only.
 *
 * Page: /staff/borrowers   View: app/views/staff/borrowers.php
 * Covers: FR05 register, FR06 view, FR07 search, FR08 edit; NFR02/NFR04 security.
 */
class BorrowerController extends Controller
{
    /* =====================================================================
     * MANAGE BORROWERS PAGE - show list (+ Add / Edit form when requested)
     * ===================================================================== */

    public function index(?array $form = null): void
    {
        // Show the borrower table; open the form for ?add=1, ?edit=ID, or after a failed save
        $this->requireRole(User::ROLE_STAFF);

        $model  = new Borrower();
        $search = trim($_GET['q'] ?? '');

        // Default: form closed
        $form = $form ?? ['open' => false, 'id' => 0, 'student_id' => '', 'name' => '',
                          'email' => '', 'contact' => '', 'errors' => []];

        if ($form['open'] === false && isset($_GET['add'])) {
            $form['open'] = true;
        }
        if ($form['open'] === false && isset($_GET['edit'])) {
            $b = $model->findById((int) $_GET['edit']);
            if ($b) {
                $form = ['open' => true, 'id' => (int) $b['borrower_id'],
                         'student_id' => $b['student_staff_id'], 'name' => $b['full_name'],
                         'email' => $b['email'], 'contact' => (string) $b['contact_number'], 'errors' => []];
            }
        }

        $this->view('staff/borrowers', [
            'title'     => 'Manage Borrowers',
            'crumb'     => 'Borrowers',
            'active'    => 'borrowers',
            'user'      => Auth::user(),
            'borrowers' => $model->search($search),
            'search'    => $search,
            'form'      => $form,
            'success'   => flash('success'),
            'error'     => flash('error'),
        ], 'staff');
    }

    /* =====================================================================
     * MANAGE BORROWERS PAGE - save (register or edit)
     * ===================================================================== */

    public function save(): void
    {
        // Validate, check the ID is unique, then insert or update (FR05, FR08)
        $this->requireRole(User::ROLE_STAFF);
        if (!verify_csrf()) {
            flash('error', 'Your session has expired. Please try again.');
            $this->redirect('staff/borrowers');
        }

        $id        = (int) ($_POST['borrower_id'] ?? 0);
        $studentId = trim($_POST['student_staff_id'] ?? '');
        $name      = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $contact   = trim($_POST['contact_number'] ?? '');

        // ---- server-side validation (never trust the browser) ----
        $errors = [];
        $model  = new Borrower();

        if ($studentId === '' || strlen($studentId) > 20) {
            $errors['student_id'] = 'Please enter the student/staff ID (max 20 characters).';
        } elseif ($model->idTaken($studentId, $id)) {
            $errors['student_id'] = 'This student/staff ID is already registered.';   // uniqueness (FR05)
        }
        if ($name === '' || strlen($name) > 100) {
            $errors['name'] = 'Please enter the full name.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        // Sri Lankan number: 10 digits starting with 0 (0771234567) or +94 followed by 9 digits
        if (!preg_match('/^(0\d{9}|\+94\d{9})$/', $contact)) {
            $errors['contact'] = 'Enter a valid contact number, e.g. 0771234567.';
        }

        // ---- on errors: redisplay the form with what was typed ----
        if ($errors) {
            $this->index(['open' => true, 'id' => $id, 'student_id' => $studentId, 'name' => $name,
                          'email' => $email, 'contact' => $contact, 'errors' => $errors]);
            return;
        }

        // ---- save ----
       // $contact = $contact === '' ? null : $contact;   // contact number is optional
        if ($id > 0) {
            $model->update($id, $studentId, $name, $email, $contact);
            flash('success', 'Borrower updated successfully.');
        } else {
            $model->create($studentId, $name, $email, $contact);
            flash('success', 'Borrower registered successfully.');
        }
        $this->redirect('staff/borrowers');
    }
}
