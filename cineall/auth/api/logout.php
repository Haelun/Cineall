<?php
/**
 * ============================================================================
 * API: Logout
 * ============================================================================
 * POST /api/logout.php
 * Response: { success }
 */

define('CINEALL_APP', true);
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
cineall_session_start();

header('Content-Type: application/json');

// Destroy session
destroySession();

successResponse([], 'Logged out successfully');
