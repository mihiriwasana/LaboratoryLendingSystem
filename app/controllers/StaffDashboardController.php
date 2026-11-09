<?php
/**
 * STAFF DASHBOARD controller - Laboratory Staff home page (placeholder until the UI is built).
 *
 * Page: /staff/dashboard   View: app/views/staff/dashboard.php
 * (The administrator's page is AdminController -> /admin/dashboard.)
 */
class StaffDashboardController extends Controller
{
    //"Due soon" means due within this many days (FR17). This is a constant so it can be changed in one place if needed.
     const DUE_SOON_DAYS = 3;

    public function dashboard(): void
    {
        // Show the staff dashboard; only logged-in Laboratory Staff are allowed
        $this->requireRole(User::ROLE_STAFF);

        $loans = new Loan();
        $equipment = new Equipment();

        //update active loans  that are past their due date to overdue (FR16)
        $loans->refreshoverdueStatus();


        $this->view('staff/dashboard', [
            'title' => 'Staff Dashboard',
            'crumb' => 'Dashboard',
            'active' => 'dashboard',   // highlights Dashboard in the staff sidebar
            'user'  => Auth::user(),
            //summary cards
            'availableCount' => $equipment->countByAvailability('Available'),
            'onLoanCount'    => $equipment->countByAvailability('OnLoan'),
            'overdueCount'   => $loans->countByStatus('Overdue'),
            'dueSoonCount'   => $loans->countDueSoon(self::DUE_SOON_DAYS),
            //tables
            'overdueLoans' => $loans->overdueList(),
            'dueSoonLoans' => $loans->dueSoonList(self::DUE_SOON_DAYS),


    ],'staff'); //staff <layout-staff class="php"></layout-staff>
    }
}
