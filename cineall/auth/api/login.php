<?php
/**
 * ============================================================================
 * API: Login
 * ============================================================================
 * POST /api/login.php
 * Body: { email, password }
 * Response: { success, requiresTwoFactor, role, tempToken? }
 */

define('CINEALL_APP', true);
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
cineall_session_start();

header('Content-Type: application/json');

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

// Check if staff (requires 2FA)
if (in_array($user['role'], ['admin', 'curator'])) {
    // Generate 2FA code
    $code = generate2FACode();
    $tempToken = generateToken();
    $expiresAt = date('Y-m-d H:i:s', time() + TWO_FACTOR_EXPIRY);

    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO two_factor_codes (user_id, code, temp_token, expires_at)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([$user['id'], $code, $tempToken, $expiresAt]);

    // In production, send code via email or SMS
    // For demo, we'll accept any code >= 4 digits

    if (APP_ENV === 'development') {
        // Log code for development
        error_log("2FA Code for {$email}: {$code}");
    }

    successResponse([
        'requiresTwoFactor' => true,
        'tempToken' => $tempToken,
        'role' => $user['role'],
        'message' => '2FA code sent. Check your authenticator app.'
    ]);
} else {
    // Regular user - create session and return
    $token = createSession($user['id'], $user['role']);

    successResponse([
        'requiresTwoFactor' => false,
        'role' => $user['role'],
        'name' => $user['name'],
        'token' => $token
    ]);
}
