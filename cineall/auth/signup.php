<?php
/**
 * ============================================================================
 * CineAll - Sign Up Page
 * ============================================================================
 */

define('CINEALL_APP', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
cineall_session_start();

// Redirect if already logged in
if (isAuthenticated()) {
    redirect('/index.php');
}

$pageTitle = 'Sign Up';
include __DIR__ . '/components/auth-header.php';
?>

<div class="auth-container">
    <!-- Left Panel -->
    <div class="auth-panel-left">
        <?php include __DIR__ . '/components/poster-wall.php'; ?>

        <div class="auth-panel-content">
            <div class="auth-header">
                <a href="<?php echo AUTH_URL; ?>/index.php" class="auth-back-btn">← Sign in</a>
                <div class="wordmark">
                    CineAll<span class="wordmark-accent">.</span>
                </div>
            </div>
        </div>

        <div class="auth-panel-content auth-quote-section">
            <div class="auth-kicker">Create account</div>
            <div class="auth-quote">"Walk until it gets quieter."</div>
            <div class="auth-attribution">Hum · 2025</div>
        </div>
    </div>

    <!-- Right Panel -->
    <div class="auth-panel-right">
        <div class="auth-form-container">
            <h1 class="auth-title">Join CineAll</h1>
            <p class="auth-subtitle">One account across every service.</p>

            <!-- Social Signup -->
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

            <div class="divider"><span class="divider-text">or with email</span></div>

            <!-- Signup Form -->
            <form id="signupForm">
                <div class="form-group">
                    <label class="form-label" for="name">Name</label>
                    <input type="text" id="name" name="name" class="form-input"
                           placeholder="Your name" autocomplete="name" required>
                    <span class="form-error"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input"
                           placeholder="you@example.com" autocomplete="email" required>
                    <span class="form-error"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="At least 8 characters" autocomplete="new-password" required>
                    <span class="form-help">Minimum 8 characters</span>
                    <span class="form-error"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input"
                           placeholder="Re-enter password" autocomplete="new-password" required>
                    <span class="form-error"></span>
                </div>

                <div class="form-error mb-md" id="error-message" style="display: none;"></div>

                <button type="submit" class="btn btn-primary btn-block">Create Account</button>

                <p class="text-center text-muted mt-md">
                    Already have one? <a href="<?php echo AUTH_URL; ?>/index.php">Sign in</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/auth-footer.php'; ?>
