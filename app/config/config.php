<?php
/**
 * Application configuration
 */

// Database credentials live in env.php, which is excluded from Git (NFR01)
if (!file_exists(__DIR__ . '/env.php')) {
    exit('Missing app/config/env.php - copy env.example.php to env.php and set your database details.');
}
require_once __DIR__ . '/env.php';

define('APP_NAME', 'University Laboratory Equipment Lending Management System');

// Base URL, e.g. /LaboratoryLendingSystem (works no matter which folder the project is in)
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base = preg_replace('#/public$#', '', $base);
define('BASE_URL', rtrim($base, '/'));

date_default_timezone_set('Asia/Colombo');

// Show errors while developing - turn off before deployment
ini_set('display_errors', '1');
error_reporting(E_ALL);
