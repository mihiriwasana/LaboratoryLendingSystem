<?php
/**
 * LOGIN / LOGOUT PAGE controller.
 *
 * Handles login (UC01) and logout (UC03).
 * Page: /login   View: app/views/auth/login.php
 */
class AuthController extends Controller
{
    /* =====================================================================
     * LOGIN PAGE - show the form
     * ===================================================================== */

    public function showLogin(): void
    {
        // Show the login form; if already logged in, go straight to the user's own dashboard
        if (Auth::check()) {
            $this->redirect(Auth::homePath(Auth::user()['role']));
        }

        $this->view('auth/login', [
            'title' => 'Log In',
            'error' => flash('error'),
            'success' => flash('success'),
            'oldEmail' => flash('old_email'),
        ], 'auth');
    }

    /* =====================================================================
     * LOGIN PAGE - process the submitted form
     * ===================================================================== */

    public function login(): void
    {
        // Check CSRF token, validate input, verify password, then start the session
        if (!verify_csrf()) {
            flash('error', 'Your session has expired. Please try again.');
            $this->redirect('login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Server-side validation
        if ($email === '' || $password === '') {
            $this->failLogin('Email address and password are required.', $email);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->failLogin('Please enter a valid email address.', $email);
        }

        $user = (new User())->findByEmail($email);

                // Unknown email: generic message
        if (!$user) {
            $this->failLogin('Invalid email or password.', $email);
        }

        // FR02: deactivated accounts cannot log in (checked BEFORE the password)
        if (!$user['is_active']) {
            $this->failLogin('Your account is inactive. Contact the Administrator.', $email);
        }

        // Wrong password
        if (!password_verify($password, $user['password_hash'])) {
            $this->failLogin('Invalid email or password.', $email);
        }


        Auth::login($user);
        $this->redirect(Auth::homePath($user['role']));
    }

    /* =====================================================================
     * LOGOUT (UC03)
     * ===================================================================== */

    public function logout(): void
    {
        // End the session and return to the login page with a "logged out" message
        if (verify_csrf()) {
            Auth::logout();
            flash('success', 'You have been logged out.');
        }
        $this->redirect('login');
    }

    /* =====================================================================
     * Helper
     * ===================================================================== */

    private function failLogin(string $message, string $email): void
    {
        // Save the error and the typed email, then send the user back to the login form
        flash('error', $message);
        flash('old_email', $email);
        $this->redirect('login');
    }
}
