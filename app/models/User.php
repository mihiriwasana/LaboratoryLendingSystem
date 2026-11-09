<?php
/**
 * User model - all database access for the users table.
 *
 * Sections in this file:
 *   1. LOGIN             - find a user by email so the login can check the password
 *   2. ADMIN PAGE        - manage Laboratory Staff accounts (list, add, edit, activate, reset password)
 *   3. SETUP             - used by the one-time admin setup script
 */
class User
{
    // The two roles stored in users.role
    const ROLE_ADMIN = 'Administrator';
    const ROLE_STAFF = 'LaboratoryStaff';

    private PDO $db;

    public function __construct()
    {
        // Get the shared database connection
        $this->db = Database::connect();
    }

    /* =====================================================================
     * SECTION 1: LOGIN  (used by AuthController)
     * ===================================================================== */

    public function findByEmail(string $email): ?array
    {
        // Look up one user by email; returns the row, or null if not found
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    /* =====================================================================
     * SECTION 2: ADMIN PAGE - Manage Staff Accounts  (used by AdminController)
     * ===================================================================== */

    /** Staff accounts (not administrators), optionally filtered by name or email. */
    public function listStaff(string $search = ''): array
    {
        // Rows for the staff table; the search box adds a LIKE filter on name/email
        $sql = 'SELECT user_id, full_name, email, is_active FROM users WHERE role = ?';
        $params = [self::ROLE_STAFF];
        if ($search !== '') {
            $sql .= ' AND (full_name LIKE ? OR email LIKE ?)';
            $like = '%' . addcslashes($search, '%_\\') . '%';
            array_push($params, $like, $like);
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY full_name');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @return array{total:int, active:int, inactive:int} */
    public function staffCounts(): array
    {
        // Numbers for the three summary cards (Total / Active / Inactive)
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS total, COALESCE(SUM(is_active), 0) AS active
             FROM users WHERE role = ?'
        );
        $stmt->execute([self::ROLE_STAFF]);
        $row = $stmt->fetch();
        $total = (int) $row['total'];
        $active = (int) $row['active'];
        return ['total' => $total, 'active' => $active, 'inactive' => $total - $active];
    }

    public function findStaffById(int $id): ?array
    {
        // Get one staff member by ID (used to fill the Edit form); null if not a staff account
        $stmt = $this->db->prepare(
            'SELECT user_id, full_name, email, is_active FROM users WHERE user_id = ? AND role = ?'
        );
        $stmt->execute([$id, self::ROLE_STAFF]);
        return $stmt->fetch() ?: null;
    }

    public function emailTaken(string $email, int $excludeId = 0): bool
    {
        // True if another user already has this email (excludeId skips the user being edited)
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND user_id <> ?');
        $stmt->execute([$email, $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(string $fullName, string $email, string $password, string $role): int
    {
        // Insert a new user; the password is hashed, never stored as plain text
        $stmt = $this->db->prepare(
            'INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$fullName, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        return (int) $this->db->lastInsertId();
    }

    public function updateStaff(int $id, string $fullName, string $email): void
    {
        // Save changes from the Edit form (name and email only)
        $stmt = $this->db->prepare(
            'UPDATE users SET full_name = ?, email = ? WHERE user_id = ? AND role = ?'
        );
        $stmt->execute([$fullName, $email, $id, self::ROLE_STAFF]);
    }

    public function setActive(int $id, bool $active): void
    {
        // Activate (true) or deactivate (false) a staff account
        $stmt = $this->db->prepare('UPDATE users SET is_active = ? WHERE user_id = ? AND role = ?');
        $stmt->execute([$active ? 1 : 0, $id, self::ROLE_STAFF]);
    }

    public function resetPassword(int $id, string $password): void
    {
        // Replace a staff member's password with a new hashed one
        $stmt = $this->db->prepare('UPDATE users SET password_hash = ? WHERE user_id = ? AND role = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id, self::ROLE_STAFF]);
    }

    /* =====================================================================
     * SECTION 3: SETUP  (used by database/setup_admin.php)
     * ===================================================================== */

    public function adminExists(): bool
    {
        // True if an Administrator account already exists (so setup is not run twice)
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE role = ?');
        $stmt->execute([self::ROLE_ADMIN]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
