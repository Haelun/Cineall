<?php
/**
 * Logout (page). Destroys the session and returns to the public site.
 * Used by the "Sign out" link in the site header.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

cineall_session_start();
destroySession();

header('Location: ' . APP_URL . '/index.php');
exit;
