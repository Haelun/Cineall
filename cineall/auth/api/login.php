<?php

/**
 * ============================================================================
 * API: Login
 * ============================================================================
 * POST /api/login.php
 * Body: { email, password }
 * Response: { success, role, name, token }
 */

define('CINEALL_APP', true);
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
cineall_session_start();

json_api_init();

try {

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    errorResponse('Invalid JSON');
}

$email = sanitize($input['email'] ?? '');
$password = $input['password'] ?? '';

// Validation
if (!validateEmail($email)) {
    errorResponse('Invalid email address');
}

if (empty($password)) {
    errorResponse('Password is required');
}

// Get user
$user = getUserByEmail($email);

if (!$user || !verifyPassword($password, $user['password'])) {
    errorResponse('Invalid email or password', 401);
}

// Check if user is active
if (!$user['is_active']) {
    errorResponse('Account is disabled. Please contact support.', 403);
}

// Create the session and sign in (no 2FA — direct login for every role)
$token = createSession($user['id'], $user['role']);

successResponse([
    'role'  => $user['role'],
    'name'  => $user['name'],
    'token' => $token
]);

} catch (Exception $e) {
    // Something went wrong (for example, the database is not set up yet).
    errorResponse('Server error. Please try again later.', 500);
}
