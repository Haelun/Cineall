<?php
/**
 * ============================================================================
 * API: Forgot Password
 * ============================================================================
 * POST /api/forgot-password.php
 * Body: { email }
 * Response: { success }
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

// Validation
if (!validateEmail($email)) {
    // For security, always return success even if email doesn't exist
    successResponse([], 'If an account exists, a reset link has been sent');
}

// Get user
$user = getUserByEmail($email);

if ($user) {
    // Generate reset token
    $token = generateToken();
    $expiresAt = date('Y-m-d H:i:s', time() + PASSWORD_RESET_EXPIRY);

    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO password_resets (user_id, token, expires_at)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([$user['id'], $token, $expiresAt]);

    // In production, send email with reset link
    // $resetLink = APP_URL . '/reset-password.php?token=' . $token;

    if (APP_ENV === 'development') {
        // Log reset link for development
        error_log("Password reset link for {$email}: /reset-password.php?token={$token}");
    }

    // TODO: Send email with reset link
    // sendPasswordResetEmail($email, $resetLink);
}

// Always return success for security
successResponse([], 'If an account exists, a reset link has been sent');
