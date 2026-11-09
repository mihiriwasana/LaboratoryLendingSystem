<?php
/**
 * Route table: URL + HTTP method => Controller@method
 */

// ===== LOGIN PAGE (AuthController) - UC01 login, UC03 logout =====
$router->get('',        'AuthController@showLogin');
$router->get('login',   'AuthController@showLogin');
$router->post('login',  'AuthController@login');
$router->post('logout', 'AuthController@logout');

// ===== ADMIN PAGE (AdminController) - Manage Staff Accounts =====
$router->get('admin/dashboard', 'AdminController@dashboard');
$router->post('admin/staff/save', 'AdminController@saveStaff');
$router->post('admin/staff/status', 'AdminController@toggleStatus');
$router->post('admin/staff/reset-password', 'AdminController@resetPassword');

// ===== STAFF DASHBOARD PAGE (StaffDashboardController) =====
$router->get('staff/dashboard', 'StaffDashboardController@dashboard');

// ===== MANAGE BORROWERS PAGE (BorrowerController) - FR05-FR08 =====
$router->get('staff/borrowers',       'BorrowerController@index');
$router->post('staff/borrowers/save', 'BorrowerController@save');


// ===== MANAGE EQUIPMENT PAGE (EquipmentController) - UC06, UC07, FR06-FR08 =====
$router->get('staff/equipment',       'EquipmentController@index');
$router->post('staff/equipment/save', 'EquipmentController@save');


