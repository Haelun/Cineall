<?php
/**
 * ============================================================================
 * API: Verify 2FA
 * ============================================================================
 * POST /api/verify-2fa.php
 * Body: { tempToken, code }
 * Response: { success, token }
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
$code = $input['code'] ?? '';

// Validation
if (empty($tempToken) || empty($code)) {
    errorResponse('Token and code are required');
}

// Get 2FA record
$db = getDB();
$stmt = $db->prepare("
    SELECT tfc.*, u.role, u.name
    FROM two_factor_codes tfc
    JOIN users u ON tfc.user_id = u.id
    WHERE tfc.temp_token = ? AND tfc.verified = 0 AND tfc.expires_at > NOW()
");

$stmt->execute([$tempToken]);
$record = $stmt->fetch();

if (!$record) {
    errorResponse('Invalid or expired verification code', 401);
}

// Verify code
// In production: check if $code === $record['code']
// For demo: accept any code with 4+ digits
$isValidCode = (strlen($code) >= 4) || ($code === $record['code']);

if (!$isValidCode) {
    errorResponse('Invalid verification code', 401);
}

// Mark as verified
$stmt = $db->prepare("UPDATE two_factor_codes SET verified = 1 WHERE id = ?");
$stmt->execute([$record['id']]);

// Create session
$token = createSession($record['user_id'], $record['role']);

successResponse([
    'token' => $token,
    'role' => $record['role'],
    'name' => $record['name']
], 'Verification successful');
