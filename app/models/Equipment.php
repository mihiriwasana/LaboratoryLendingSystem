<?php
/**
 * Equipment model - all database access for the equipment table.
 *
 * Sections in this file:
 *   1. STAFF DASHBOARD       - counts for the Available / On Loan cards
 *   2. MANAGE EQUIPMENT PAGE - list, filter, find, create, update (FR06-FR08, UC06-UC07)
 */


class Equipment
{   
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    
    }

/** 1. section 1 : Staff Dashboard- used by staffDashboardController/ */
    public function countByAvailability(string $status): int
    {
        //howmany equip,ent ites are available
        $stmt = $this->db->prepare('SELECT count(*) from equipment where availability_status = ?');
        $stmt->execute([$status]);
        return (int) $stmt->fetchcolumn();
    }

        /* =====================================================================
     * SECTION 2: MANAGE EQUIPMENT PAGE  (used by EquipmentController)
     * ===================================================================== */

    public function search(string $term, string $category, string $availability): array
    {
        // FR06/FR07: list equipment, filtered by text / category / availability,
        // with the current borrower and loan details for items that are on loan
        $sql = "SELECT e.*, l.issue_date, l.due_date, l.status AS loan_status,
                       b.full_name AS holder_name, b.student_staff_id AS holder_id
                FROM equipment e
                LEFT JOIN loans l ON l.equipment_id = e.equipment_id AND l.status IN ('Active', 'Overdue')
                LEFT JOIN borrowers b ON b.borrower_id = l.borrower_id
                WHERE 1 = 1";
        $params = [];

        if ($term !== '') {
            $sql .= ' AND (e.equipment_code LIKE ? OR e.name LIKE ?)';
            $params[] = '%' . $term . '%';
            $params[] = '%' . $term . '%';
        }
        if ($category !== '') {
            $sql .= ' AND e.category = ?';
            $params[] = $category;
        }
        if ($availability !== '') {
            $sql .= ' AND e.availability_status = ?';
            $params[] = $availability;
        }
        $sql .= ' ORDER BY e.equipment_code';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        // Get one equipment item for the Edit form (null if not found)
        $stmt = $this->db->prepare('SELECT * FROM equipment WHERE equipment_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function codeTaken(string $code, int $exceptId = 0): bool
    {
        // UC06 5a: is this equipment code already used by ANOTHER item? (uniqueness check)
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM equipment WHERE equipment_code = ? AND equipment_id <> ?'
        );
        $stmt->execute([$code, $exceptId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(string $code, string $name, string $category, string $condition, int $days): void
    {
        // FR06: register new equipment (availability starts as Available - set by the system, FR08)
        $stmt = $this->db->prepare(
            'INSERT INTO equipment (equipment_code, name, category, condition_status, default_loan_days)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$code, $name, $category, $condition, $days]);
    }

    public function update(int $id, string $code, string $name, string $category, string $condition, int $days): void
    {
        // FR06: edit an equipment item (availability is NOT changed here - only issue/return change it)
        $stmt = $this->db->prepare(
            'UPDATE equipment
             SET equipment_code = ?, name = ?, category = ?, condition_status = ?, default_loan_days = ?
             WHERE equipment_id = ?'
        );
        $stmt->execute([$code, $name, $category, $condition, $days, $id]);
    }



}