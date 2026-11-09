<?php
/**
 * CORE: Session-based authentication - who is logged in, and for how long.
 *
 * Used by: AuthController (login / logout) and every page that needs the current user.
 */
class Auth
{
    // NFR03: sessions expire after 30 minutes of inactivity
    const TIMEOUT_SECONDS = 1800;

    public static function login(array $user): void
    {
        // Start a session for this user and remember who they are
        // NFR03: new session ID at each login (prevents session fixation)
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'    => (int) $user['user_id'],
            'name'  => $user['full_name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];
        $_SESSION['last_activity'] = time();
    }

    public static function logout(): void
    {
        // Clear the session so the user is logged out
        $_SESSION = [];
        session_regenerate_id(true);
    }

    public static function check(): bool
    {
        // True if someone is logged in
        return !empty($_SESSION['user']);
    }

    public static function user(): ?array
    {
        // The logged-in user's id, name, email and role (null if nobody is logged in)
        return $_SESSION['user'] ?? null;
    }

    /** End the session if the user has been inactive too long; otherwise refresh the timer. */
    public static function enforceTimeout(): void
    {
        // Runs on every request: log out after 30 minutes idle, otherwise reset the idle timer
        if (!self::check()) {
            return;
        }

        if (time() - ($_SESSION['last_activity'] ?? 0) > self::TIMEOUT_SECONDS) {
            self::logout();
            flash('error', 'Your session has expired due to inactivity. Please log in again.');
            return;
        }

        $_SESSION['last_activity'] = time();
    }

    /** The page each role lands on after logging in (FR01). */
    public static function homePath(string $role): string
    {
        // Administrator goes to the admin page, Laboratory Staff to the staff dashboard
        return $role === User::ROLE_ADMIN ? 'admin/dashboard' : 'staff/dashboard';
    }
}
