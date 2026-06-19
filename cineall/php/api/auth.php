<?php
/**
 * Auth API Endpoint
 * Handles login, logout, and session check
 */

require_once '../../config/config.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

session_name(SESSION_NAME);
session_start();

$db = Database::getInstance();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'login':
            login($db);
            break;
        case 'logout':
            logout();
            break;
        case 'check':
            checkSession();
            break;
        default:
            sendError('Invalid action', 400);
    }
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}

function login($db) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        sendError('Username dan password wajib diisi', 400);
    }

    // Cari user berdasarkan username atau email
    $sql = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
    $user = $db->queryOne($sql, [$username, $username]);

    if (!$user) {
        sendError('Username atau password salah', 401);
    }

    // Verifikasi password menggunakan password_verify (bcrypt)
    if (!password_verify($password, $user['password_hash'])) {
        sendError('Username atau password salah', 401);
    }

    // Simpan ke session
    $_SESSION['user_id']      = $user['id'];
    $_SESSION['username']     = $user['username'];
    $_SESSION['display_name'] = $user['display_name'] ?? $user['username'];

    sendSuccess([
        'user' => [
            'id'           => $user['id'],
            'username'     => $user['username'],
            'display_name' => $user['display_name'] ?? $user['username'],
            'email'        => $user['email'],
        ]
    ]);
}

function logout() {
    $_SESSION = [];
    session_destroy();
    sendSuccess(['message' => 'Logged out']);
}

function checkSession() {
    if (!empty($_SESSION['user_id'])) {
        sendSuccess([
            'logged_in' => true,
            'user' => [
                'id'           => $_SESSION['user_id'],
                'username'     => $_SESSION['username'],
                'display_name' => $_SESSION['display_name'],
            ]
        ]);
    } else {
        sendSuccess(['logged_in' => false]);
    }
}

function sendSuccess($data) {
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}
?>