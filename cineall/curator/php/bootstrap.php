<?php
/**
 * Curator bootstrap — replaces the old php/config.php.
 * Uses the shared platform layer (one DB, one session) and provides the
 * helper functions the curator pages/APIs expect, adapted to the unified
 * schema. No dev auto-login: a real curator/admin session is required.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

cineall_session_start();

// Curator panel also reachable by admins.
if (!defined('CURATOR_ALLOWED_ROLES')) {
    define('CURATOR_ALLOWED_ROLES', 'curator,admin');
}

// ---------------------------------------------------------------------------
// Helpers expected by the curator pages/APIs (curator-local; no collision with
// the shared functions.php, which is intentionally NOT loaded here).
// ---------------------------------------------------------------------------
function escape($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, name AS username, email, role, display_name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function isCurator() {
    $u = getCurrentUser();
    return $u && in_array($u['role'], ['curator', 'admin'], true);
}

function isSuperAdmin() {
    $u = getCurrentUser();
    return $u && $u['role'] === 'admin';
}

function curatorName() {
    $u = getCurrentUser();
    return $u ? ($u['display_name'] ?: $u['name']) : 'Curator';
}

function redirect($page) {
    header('Location: ' . CURATOR_URL . '/' . ltrim($page, '/'));
    exit;
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Activity log — curator's 4-arg signature, written to the unified
 * activity_log (actor, action, target).
 */
function logActivity($action, $entityType = null, $entityId = null, $details = null) {
    if (!isLoggedIn()) return;
    try {
        $db = getDB();
        $target = trim(($entityType ?? '') . ($entityId ? ' ' . $entityId : ''));
        if ($target === '' && $details) {
            $target = is_string($details) ? $details : json_encode($details);
        }
        $stmt = $db->prepare("INSERT INTO activity_log (actor, action, target, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([curatorName(), $action, $target, $_SERVER['REMOTE_ADDR'] ?? null]);
    } catch (PDOException $e) { /* never break the request */ }
}

function formatDate($date, $format = 'M j, Y g:i A') {
    return date($format, strtotime($date));
}

function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)       return $diff . ' seconds ago';
    if ($diff < 3600)     return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400)    return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800)   return floor($diff / 86400) . ' days ago';
    if ($diff < 2592000)  return floor($diff / 604800) . ' weeks ago';
    if ($diff < 31536000) return floor($diff / 2592000) . ' months ago';
    return floor($diff / 31536000) . ' years ago';
}

// ---------------------------------------------------------------------------
// AUTH GUARD — require a real curator (or admin) session.
// ---------------------------------------------------------------------------
$__curUser = getCurrentUser();
if (!$__curUser || !in_array($__curUser['role'], explode(',', CURATOR_ALLOWED_ROLES), true)) {
    header('Location: ' . AUTH_URL . '/index.php');
    exit;
}
$CURATOR_USER = $__curUser;
