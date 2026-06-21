<?php
/**
 * Admin bootstrap — included at the top of every admin page/endpoint.
 * Loads the shared platform layer, enforces the admin role, and exposes
 * the normalized movie helpers.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/movie_helpers.php';

cineall_session_start();

// Only admins may access this section.
$ADMIN_SESSION = requireRole('admin');
$ADMIN_USER    = getCurrentUser();
