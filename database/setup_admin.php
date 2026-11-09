<?php
/**
 * One-time setup script: creates the first Administrator account.
 *
 * Usage (from the project folder):
 *   C:\xampp\php\php.exe database\setup_admin.php "Full Name" admin@university.lk "Password"
 *
 * It refuses to run if an Administrator already exists.
 */

if (PHP_SAPI !== 'cli') {
    exit('This script can only be run from the command line.');
}

define('APP_ROOT', dirname(__DIR__) . '/app');
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/core/Database.php';
require_once APP_ROOT . '/models/User.php';

function fail(string $message): void
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

[, $fullName, $email, $password] = $argv + [null, null, null, null];

if (!$fullName || !$email || !$password) {
    fail('Usage: php database/setup_admin.php "Full Name" email@university.lk "Password"');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Please provide a valid email address.');
}
if (strlen($password) < 8) {
    fail('The password must be at least 8 characters long.');
}

try {
    $userModel = new User();
} catch (PDOException $e) {
    fail('Could not connect to the database. Import database/schema.sql first. (' . $e->getMessage() . ')');
}

if ($userModel->adminExists()) {
    fail('An Administrator account already exists. Setup is not needed.');
}
if ($userModel->findByEmail($email)) {
    fail('This email address is already registered.');
}

$userModel->create(trim($fullName), $email, $password, User::ROLE_ADMIN);
echo "Administrator account created for $email" . PHP_EOL;
