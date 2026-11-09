<?php
/**
 * CORE: Base controller - shared helpers for all controllers.
 *
 * Used by: AuthController (login), AdminController (admin page), StaffDashboardController (staff page).
 */
class Controller
{
    /**
     * Render a view inside a layout.
     * $view is relative to app/views, e.g. 'auth/login'.
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        // Render a page view, then wrap it in a layout (auth / main / admin)
        extract($data);

        ob_start();
        require APP_ROOT . "/views/$view.php";
        $content = ob_get_clean();

        require APP_ROOT . "/views/layouts/$layout.php";
    }

    protected function redirect(string $path): void
    {
        // Send the browser to another page and stop running this request
        header('Location: ' . url($path));
        exit;
    }

    /**
     * Allow only logged-in users with the given role (FR01, NFR04).
     * Guests go to the login page; other roles get 403 Forbidden,
     * even if they type the URL directly.
     */
    protected function requireRole(string $role): void
    {
        // Access control: guests go to the login page, the wrong role gets a 403 page
        if (!Auth::check()) {
            flash('error', 'Please log in to continue.');
            $this->redirect('login');
        }

        if (Auth::user()['role'] !== $role) {
            http_response_code(403);
            require APP_ROOT . '/views/errors/403.php';
            exit;
        }
    }
}
