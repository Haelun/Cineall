<?php
/**
 * ============================================================================
 * CineAll — SHARED CONFIGURATION
 * ============================================================================
 * One config for the whole platform: public site, auth, admin, curator.
 * Every section includes THIS file (no more per-app configs).
 *
 * If you move the project, the only thing you normally need to change is
 * APP_URL below.
 * ============================================================================
 */

if (defined('CINEALL_CONFIG')) {
    return; // already loaded
}
define('CINEALL_CONFIG', true);
define('CINEALL_APP', true); // legacy guard used by some auth files

// ---------------------------------------------------------------------------
// DATABASE (XAMPP defaults: user "root", empty password)
// ---------------------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'cineall');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ---------------------------------------------------------------------------
// APP IDENTITY / URLS
// ---------------------------------------------------------------------------
define('APP_NAME', 'CineAll');
define('SITE_NAME', 'CineAll');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // 'development' or 'production'

// Root URL of the whole project. Change this if you deploy elsewhere.
define('APP_URL', 'http://localhost/cineall');
define('AUTH_URL',    APP_URL . '/auth');
define('ADMIN_URL',   APP_URL . '/admin');
define('CURATOR_URL', APP_URL . '/curator');
define('SITE_URL', APP_URL); // alias used by admin/curator code

// ---------------------------------------------------------------------------
// FILESYSTEM PATHS  (config.php lives in <root>/config)
// ---------------------------------------------------------------------------
define('ROOT_PATH',     dirname(__DIR__));
define('BASE_PATH',     ROOT_PATH);
define('CONFIG_PATH',   ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('API_PATH',      ROOT_PATH . '/api');
define('UPLOAD_PATH',   ROOT_PATH . '/assets/uploads');

// ---------------------------------------------------------------------------
// SESSION  (one shared session across every section)
// ---------------------------------------------------------------------------
define('SESSION_NAME', 'cineall_session');
define('SESSION_LIFETIME', 86400); // 24h

// ---------------------------------------------------------------------------
// AUTH / SECURITY
// ---------------------------------------------------------------------------
define('HASH_ALGO', PASSWORD_BCRYPT);
define('HASH_COST', 10);
define('PASSWORD_MIN_LENGTH', 8);
define('TOKEN_EXPIRY', 3600);
define('TWO_FACTOR_EXPIRY', 300);       // 5 min
define('PASSWORD_RESET_EXPIRY', 1800);  // 30 min
define('SECRET_KEY', 'cineall-dev-secret-' . md5(ROOT_PATH));

// ---------------------------------------------------------------------------
// PAGINATION / UI
// ---------------------------------------------------------------------------
define('ITEMS_PER_PAGE', 20);
define('MAX_ITEMS_PER_PAGE', 100);
define('DEFAULT_THEME', 'dark');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// ---------------------------------------------------------------------------
// ERROR REPORTING
// ---------------------------------------------------------------------------
define('DEBUG_MODE', APP_ENV === 'development');
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

date_default_timezone_set('UTC');

// ---------------------------------------------------------------------------
// SHARED SESSION BOOTSTRAP
// ---------------------------------------------------------------------------
/**
 * Start the one shared CineAll session. Safe to call multiple times.
 * Every section (site/auth/admin/curator) uses the same session name and
 * cookie path, so logging in once is recognised everywhere.
 */
function cineall_session_start() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name(SESSION_NAME);
    if (PHP_SESSION_NONE === session_status()) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    session_start();
}
