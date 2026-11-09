<?php
/*  Loan model - all db access for the loan tables
 * sections in this file : 
 * 1. Staff Dashboard- overdue and due loans counts
*/
class Loan
{
    private PDO $db;

    public function __construct()
    {
        //Get the shared database connection 
        $this->db = Database::connect();
    }

/** 1. section 1 : Staff Dashboard- used by staffDashboardController/ */
public function refreshoverdueStatus(): void
{
    //Any active loan whose due data has passed become overdue 
    $this->db->exec(
        "UPDATE loans SET status = 'Overdue' WHERE status='Active' And due_date < CURDaTE()"
    );
}

public function countByStatus(string $status): int
{
    // Number of loans with this status (used for the Overdue card)
    $stmt = $this->db->prepare('SELECT COUNT(*) FROM loans WHERE status = ?');
    $stmt->execute([$status]);
    return (int) $stmt->fetchcolumn();
}

    public function countDueSoon(int $days): int
    {
        // Number of Active loans due from today up to $days days ahead (Due Soon card)
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM loans
             WHERE status = 'Active'
               AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)"
        );
        $stmt->execute([$days]);
        return (int) $stmt->fetchColumn();
    }

    public function overdueList(): array
    {
        // Rows for the Overdue Loans table, oldest due date first, with days overdue worked out
        $sql = "SELECT l.loan_id, b.full_name, b.student_staff_id,
                       e.name AS equipment_name, e.equipment_code, l.due_date,
                       DATEDIFF(CURDATE(), l.due_date) AS days_overdue
                FROM loans l
                JOIN borrowers b ON b.borrower_id = l.borrower_id
                JOIN equipment e ON e.equipment_id = l.equipment_id
                WHERE l.status = 'Overdue'
                ORDER BY l.due_date ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function dueSoonList(int $days): array
    {
        // Rows for the Due Soon table: Active loans due within the next $days days (FR17)
        $stmt = $this->db->prepare(
            "SELECT l.loan_id, b.full_name, b.student_staff_id,
                    e.name AS equipment_name, e.equipment_code, l.due_date
             FROM loans l
             JOIN borrowers b ON b.borrower_id = l.borrower_id
             JOIN equipment e ON e.equipment_id = l.equipment_id
             WHERE l.status = 'Active'
               AND l.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
             ORDER BY l.due_date ASC"
        );
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
}