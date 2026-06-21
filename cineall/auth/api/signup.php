<?php

/**
 * ============================================================================
 * API: Sign Up
 * ============================================================================
 * POST /api/signup.php
 * Body: { name, email, password }
 * Response: { success, token }
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

$name = sanitize($input['name'] ?? '');
$email = sanitize($input['email'] ?? '');
$password = $input['password'] ?? '';

// Validation
if (empty($name) || strlen($name) < 2) {
    errorResponse('Name must be at least 2 characters');
}

if (!validateEmail($email)) {
    errorResponse('Invalid email address');
}

if (strlen($password) < PASSWORD_MIN_LENGTH) {
    errorResponse('Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters');
}

// Create user
$result = createUser($name, $email, $password, 'user');

if (!$result['success']) {
    errorResponse($result['message']);
}

// Create session
$user = getUserById($result['user_id']);
$token = createSession($user['id'], $user['role']);

successResponse([
    'token' => $token,
    'role' => $user['role'],
    'name' => $user['name']
], 'Account created successfully');

} catch (Exception $e) {
    // Something went wrong (for example, the database is not set up yet).
    errorResponse('Server error. Please try again later.', 500);
}
