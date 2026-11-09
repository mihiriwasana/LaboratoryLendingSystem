<?php
/**
 * CORE: Small global helper functions used in controllers and views.
 *
 * Sections: HTML output, URLs, flash messages, CSRF protection.
 */

/** Escape output for HTML. */
function e(?string $value): string
{
    // Make text safe to print in HTML (prevents XSS)
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Build an application URL, e.g. url('login'). */
function url(string $path = ''): string
{
    // Full link to a page, e.g. url('login')
    return BASE_URL . '/' . ltrim($path, '/');
}

/** Build a URL to a file in public/assets. */
function asset(string $path): string
{
    // Link to a CSS / JS file inside public/assets
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

/** Set a one-time message, or read (and clear) it when $message is null. */
function flash(string $key, ?string $message = null): ?string
{
    // Save a one-time message for the next page (e.g. "Staff account created"), or read and clear it
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

/* ---------- CSRF protection ---------- */

function csrf_token(): string
{
    // Create (once per session) the secret token that proves a form came from our site
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    // Hidden input that goes inside every POST form
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf(): bool
{
    // Check the submitted token matches the one stored in the session
    return isset($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);
}
