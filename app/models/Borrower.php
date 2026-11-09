<?php
/**
 * Borrower model - all database access for the borrowers table.
 *
 * Sections in this file:
 *   1. MANAGE BORROWERS PAGE - list, search, find, create, update (FR05-FR08)
 */
class Borrower
{
    private PDO $db;

    public function __construct()
    {
        // Get the shared database connection
        $this->db = Database::connect();
    }

    /* =====================================================================
     * SECTION 1: MANAGE BORROWERS PAGE  (used by BorrowerController)
     * ===================================================================== */

    public function search(string $term = ''): array
    {
        // FR07: list borrowers; if a term is given, match name or student/staff ID
        if ($term === '') {
            return $this->db->query('SELECT * FROM borrowers ORDER BY full_name')->fetchAll();
        }
        $stmt = $this->db->prepare(
            'SELECT * FROM borrowers
             WHERE full_name LIKE ? OR student_staff_id LIKE ?
             ORDER BY full_name'
        );
        $like = '%' . $term . '%';
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        // Get one borrower for the Edit form (null if not found)
        $stmt = $this->db->prepare('SELECT * FROM borrowers WHERE borrower_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function idTaken(string $studentStaffId, int $exceptId = 0): bool
    {
        // FR05: is this student/staff ID already used by ANOTHER borrower? (uniqueness check)
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM borrowers WHERE student_staff_id = ? AND borrower_id <> ?'
        );
        $stmt->execute([$studentStaffId, $exceptId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(string $studentStaffId, string $name, string $email, string $contact): void
    {
        // FR05: register a new borrower
        $stmt = $this->db->prepare(
            'INSERT INTO borrowers (student_staff_id, full_name, email, contact_number) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$studentStaffId, $name, $email, $contact]);
    }

    public function update(int $id, string $studentStaffId, string $name, string $email, string $contact): void
    {
        // FR08: edit an existing borrower
        $stmt = $this->db->prepare(
            'UPDATE borrowers
             SET student_staff_id = ?, full_name = ?, email = ?, contact_number = ?
             WHERE borrower_id = ?'
        );
        $stmt->execute([$studentStaffId, $name, $email, $contact, $id]);
    }
}
