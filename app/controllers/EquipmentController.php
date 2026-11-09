<?php
/**
 * MANAGE EQUIPMENT controller - Laboratory Staff only.
 *
 * Page: /staff/equipment   View: app/views/staff/equipment.php
 * Covers: FR06 register/view/search/filter/edit, FR07 availability + current borrower,
 *         FR08 availability is never edited here; UC06, UC07; NFR02/NFR04 security.
 */
class EquipmentController extends Controller
{
    // Choices for the Category and Condition drop-downs (change the list here if needed)
    const CATEGORIES = ['Electronics', 'Optics', 'Mechanical', 'Chemistry', 'Physics', 'Other'];
    const CONDITIONS = ['Good', 'Fair', 'Damaged'];

    /* =====================================================================
     * MANAGE EQUIPMENT PAGE - show list (+ Register / Edit form when requested)
     * ===================================================================== */

    public function index(?array $form = null): void
    {
        // Show the equipment table with filters; open the form for ?add=1, ?edit=ID, or after a failed save
        $this->requireRole(User::ROLE_STAFF);

        $model = new Equipment();

        // Filters from the URL (only accept known values)
        $q            = trim($_GET['q'] ?? '');
        $category     = in_array($_GET['category'] ?? '', self::CATEGORIES, true) ? $_GET['category'] : '';
        $availability = in_array($_GET['availability'] ?? '', ['Available', 'OnLoan'], true) ? $_GET['availability'] : '';

        // Default: form closed
        $form = $form ?? ['open' => false, 'id' => 0, 'code' => '', 'name' => '', 'category' => '',
                          'condition' => 'Good', 'days' => '7', 'availability' => 'Available', 'errors' => []];

        if ($form['open'] === false && isset($_GET['add'])) {
            $form['open'] = true;
        }
        if ($form['open'] === false && isset($_GET['edit'])) {
            $e = $model->findById((int) $_GET['edit']);
            if ($e) {
                $form = ['open' => true, 'id' => (int) $e['equipment_id'], 'code' => $e['equipment_code'],
                         'name' => $e['name'], 'category' => $e['category'], 'condition' => $e['condition_status'],
                         'days' => (string) $e['default_loan_days'], 'availability' => $e['availability_status'],
                         'errors' => []];
            }
        }

        $this->view('staff/equipment', [
            'title'        => 'Manage Equipment',
            'crumb'        => 'Equipment',
            'active'       => 'equipment',
            'user'         => Auth::user(),
            'items'        => $model->search($q, $category, $availability),
            'categories'   => self::CATEGORIES,
            'conditions'   => self::CONDITIONS,
            'q'            => $q,
            'category'     => $category,
            'availability' => $availability,
            'form'         => $form,
            'success'      => flash('success'),
            'error'        => flash('error'),
        ], 'staff');
    }

    /* =====================================================================
     * MANAGE EQUIPMENT PAGE - save (register or edit)
     * ===================================================================== */

    public function save(): void
    {
        // Validate, check the code is unique, then insert or update (UC06)
        $this->requireRole(User::ROLE_STAFF);
        if (!verify_csrf()) {
            flash('error', 'Your session has expired. Please try again.');
            $this->redirect('staff/equipment');
        }

        $id        = (int) ($_POST['equipment_id'] ?? 0);
        $code      = trim($_POST['equipment_code'] ?? '');
        $name      = trim($_POST['name'] ?? '');
        $category  = $_POST['category'] ?? '';
        $condition = $_POST['condition_status'] ?? '';
        $daysRaw   = trim($_POST['default_loan_days'] ?? '');

        // ---- server-side validation ----
        $errors = [];
        $model  = new Equipment();

        if ($code === '' || strlen($code) > 30) {
            $errors['code'] = 'Please enter the equipment code (max 30 characters).';
        } elseif ($model->codeTaken($code, $id)) {
            $errors['code'] = 'This equipment code is already registered.';       // uniqueness (UC06 5a)
        }
        if ($name === '' || strlen($name) > 100) {
            $errors['name'] = 'Please enter the equipment name.';
        }
        if (!in_array($category, self::CATEGORIES, true)) {
            $errors['category'] = 'Please select a category.';
        }
        if (!in_array($condition, self::CONDITIONS, true)) {
            $errors['condition'] = 'Please select the condition.';
        }
        $days = filter_var($daysRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 365]]);
        if ($days === false) {
            $errors['days'] = 'Enter the loan period as a whole number of days (1-365).';   // UC06 4a
        }

        // ---- on errors: redisplay the form with what was typed ----
        if ($errors) {
            $this->index(['open' => true, 'id' => $id, 'code' => $code, 'name' => $name, 'category' => $category,
                          'condition' => $condition, 'days' => $daysRaw, 'availability' => $_POST['availability'] ?? 'Available',
                          'errors' => $errors]);
            return;
        }

        // ---- save ----
        if ($id > 0) {
            $model->update($id, $code, $name, $category, $condition, $days);
            flash('success', 'Equipment updated successfully.');
        } else {
            $model->create($code, $name, $category, $condition, $days);
            flash('success', 'Equipment registered successfully.');
        }
        $this->redirect('staff/equipment');
    }
}
