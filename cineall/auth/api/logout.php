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

json_api_init();

try {

// Destroy session
destroySession();

successResponse([], 'Logged out successfully');

} catch (Exception $e) {
    // Something went wrong (for example, the database is not set up yet).
    errorResponse('Server error. Please try again later.', 500);
}
