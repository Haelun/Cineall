<?php
/**
 * ============================================================================
 * API: Resend 2FA Code
 * ============================================================================
 * POST /api/resend-2fa.php
 * Body: { tempToken }
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

$tempToken = $input['tempToken'] ?? '';

if (empty($tempToken)) {
    errorResponse('Token is required');
}

// Get existing 2FA record
$db = getDB();
$stmt = $db->prepare("
    SELECT * FROM two_factor_codes
    WHERE temp_token = ? AND verified = 0 AND expires_at > NOW()
");

$stmt->execute([$tempToken]);
$record = $stmt->fetch();

if (!$record) {
    errorResponse('Invalid or expired token', 401);
}

// Generate new code
$code = generate2FACode();
$expiresAt = date('Y-m-d H:i:s', time() + TWO_FACTOR_EXPIRY);

// Update record with new code
$stmt = $db->prepare("
    UPDATE two_factor_codes
    SET code = ?, expires_at = ?
    WHERE id = ?
");

$stmt->execute([$code, $expiresAt, $record['id']]);

// In production, send new code via email/SMS
if (APP_ENV === 'development') {
    // Log code for development
    error_log("New 2FA Code: {$code}");
}

successResponse([], 'New code has been sent');
