<?php
/**
 * ============================================================================
 * CineAll — SHARED HELPER FUNCTIONS  (auth, validation, responses)
 * ============================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// ============================================================================
// AUTHENTICATION
// ============================================================================

function hashPassword($password) {
    return password_hash($password, HASH_ALGO, ['cost' => HASH_COST]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function generate2FACode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Create a server-side session row and populate $_SESSION.
 * Assumes a PHP session has already been started (cineall_session_start()).
 */
function createSession($userId, $role) {
    $db = getDB();
    $token = generateToken();
    $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);

    $stmt = $db->prepare("
        INSERT INTO sessions (user_id, token, ip_address, user_agent, expires_at)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $userId,
        $token,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $expiresAt
    ]);

    $_SESSION['user_id'] = $userId;
    $_SESSION['role']    = $role;
    $_SESSION['token']   = $token;

    return $token;
}

function validateSession() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
        return false;
    }
    $db = getDB();
    $stmt = $db->prepare("
        SELECT s.*, u.role, u.is_active, u.name
        FROM sessions s
        JOIN users u ON s.user_id = u.id
        WHERE s.token = ? AND s.expires_at > NOW()
    ");
    $stmt->execute([$_SESSION['token']]);
    $session = $stmt->fetch();

    if (!$session || !$session['is_active']) {
        return false;
    }
    return $session;
}

function destroySession() {
    if (isset($_SESSION['token'])) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM sessions WHERE token = ?");
        $stmt->execute([$_SESSION['token']]);
    }
    $_SESSION = [];
    session_unset();
    session_destroy();
}

function isAuthenticated() {
    return validateSession() !== false;
}

/** The logged-in user's id, or null for a guest. */
function currentUserId() {
    return isAuthenticated() ? (int)$_SESSION['user_id'] : null;
}

function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: ' . AUTH_URL . '/index.php');
        exit;
    }
}

/** Require one of the given roles, else bounce to the public site. */
function requireRole($allowedRoles) {
    requireAuth();
    $session = validateSession();
    if (!in_array($session['role'], (array)$allowedRoles, true)) {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
    return $session;
}

// ============================================================================
// USERS
// ============================================================================

function getUserByEmail($email) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function getUserById($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function createUser($name, $email, $password, $role = 'user') {
    $db = getDB();
    if (getUserByEmail($email)) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
    }
    $hashed = hashPassword($password);
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role, display_name) VALUES (?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$name, $email, $hashed, $role, $name]);
        return ['success' => true, 'user_id' => $db->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to create user'];
    }
}

function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    return getUserById($_SESSION['user_id']);
}

function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// ============================================================================
// ACTIVITY LOG  (shared by admin + curator)
// ============================================================================

function logActivity($actor, $action, $target) {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO activity_log (actor, action, target, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$actor, $action, $target, $_SERVER['REMOTE_ADDR'] ?? null]);
    } catch (PDOException $e) {
        // logging must never break the request
    }
}

// ============================================================================
// VALIDATION
// ============================================================================

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim((string)$data)));
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken(16);
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ============================================================================
// JSON RESPONSES
// ============================================================================

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function errorResponse($message, $statusCode = 400) {
    jsonResponse(['success' => false, 'message' => $message], $statusCode);
}

function successResponse($data = [], $message = 'Success') {
    jsonResponse(array_merge(['success' => true, 'message' => $message], $data));
}

function redirect($path) {
    header('Location: ' . APP_URL . $path);
    exit;
}
