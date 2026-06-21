<?php
/**
 * ============================================================================
 * CineAll - Login Page
 * ============================================================================
 */

define('CINEALL_APP', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
cineall_session_start();

// Redirect if already logged in
if (isAuthenticated()) {
    $session = validateSession();
    $redirectMap = [
        'admin' => '/admin/index.php',
        'curator' => '/curator/index.php',
        'user' => '/index.php'
    ];
    redirect($redirectMap[$session['role']] ?? '/index.php');
}

$pageTitle = 'Sign In';
include __DIR__ . '/components/auth-header.php';
?>

<div class="auth-container">
    <!-- Left Panel - Branding -->
    <div class="auth-panel-left">
        <?php include __DIR__ . '/components/poster-wall.php'; ?>

        <div class="auth-panel-content">
            <div class="auth-header">
                <div class="wordmark">
                    CineAll<span class="wordmark-accent">.</span>
                </div>
            </div>
        </div>

        <div class="auth-panel-content auth-quote-section">
            <div class="auth-kicker">Sign in to CineAll</div>
            <div class="auth-quote">"One library, every service."</div>
            <div class="auth-attribution">CineAll · Vol. 04</div>
        </div>
    </div>

    <!-- Right Panel - Login Form -->
    <div class="auth-panel-right">
        <div class="auth-form-container">
            <h1 class="auth-title">Welcome</h1>
            <p class="auth-subtitle">One door. We'll take you to the right place.</p>

            <!-- Social Login -->
            <div class="social-btns">
                <button type="button" class="btn-social" data-provider="google">
                    <span class="btn-social-icon">G</span>
                    Continue with Google
                </button>
                <button type="button" class="btn-social" data-provider="apple">
                    <span class="btn-social-icon"></span>
                    Continue with Apple
                </button>
            </div>

            <!-- Divider -->
            <div class="divider">
                <span class="divider-text">or with email</span>
            </div>

            <!-- Login Form -->
            <form id="loginForm">
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input"
                           placeholder="you@example.com" autocomplete="email" required>
                    <span class="form-error"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="••••••••" autocomplete="current-password" required>
                    <span class="form-error"></span>
                </div>

                <div class="form-error mb-md" id="error-message" style="display: none;"></div>

                <div class="text-right mb-md">
                    <a href="<?php echo AUTH_URL; ?>/forgot-password.php" class="verify-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Continue</button>

                <p class="text-center text-muted mt-md">
                    New here? <a href="<?php echo AUTH_URL; ?>/signup.php">Create an account</a>
                </p>
            </form>

            <!-- Demo Credentials (Development Only - Remove in Production) -->
            <?php if (APP_ENV === 'development'): ?>
            <div class="card mt-lg" style="background: rgba(244,239,230,0.02);">
                <div class="form-label text-accent">Demo Credentials (Dev Only)</div>
                <div style="font-family: var(--font-mono); font-size: 11px; color: var(--text-muted);">
                    <div style="margin-bottom: 4px;">
                        <button type="button" onclick="fillTestCredentials('admin@cineall.com', 'password123')"
                                class="verify-link" style="margin-right: 8px;">Fill</button>
                        admin@cineall.com / password123
                        <span class="badge badge-curator" style="margin-left: 8px;">Admin</span>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <button type="button" onclick="fillTestCredentials('curator@cineall.com', 'password123')"
                                class="verify-link" style="margin-right: 8px;">Fill</button>
                        curator@cineall.com / password123
                        <span class="badge badge-curator" style="margin-left: 8px;">Curator</span>
                    </div>
                    <div style="opacity:.8;">
                        Regular users: <a href="<?php echo AUTH_URL; ?>/signup.php" class="verify-link">create an account</a>.
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/auth-footer.php'; ?>
