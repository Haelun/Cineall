<?php
/**
 * ============================================================================
 * CineAll - Forgot Password Page
 * ============================================================================
 */

define('CINEALL_APP', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
cineall_session_start();

if (isAuthenticated()) {
    redirect('/index.php');
}

$pageTitle = 'Reset Password';
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
            <div class="auth-kicker">Account recovery</div>
            <div class="auth-quote">"A map of an island that may not exist."</div>
            <div class="auth-attribution">The Cartographer's Wife · 2023</div>
        </div>
    </div>

    <!-- Right Panel -->
    <div class="auth-panel-right">
        <div class="auth-form-container">
            <h1 class="auth-title">Reset password</h1>
            <p class="auth-subtitle">We'll send a recovery link to your email.</p>

            <!-- Form -->
            <div class="forgot-form">
                <form id="forgotPasswordForm">
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-input"
                               placeholder="you@example.com" autocomplete="email" required>
                        <span class="form-error"></span>
                    </div>

                    <div class="form-error mb-md" id="error-message" style="display: none;"></div>

                    <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
                </form>
            </div>

            <!-- Success Message -->
            <div class="forgot-success" style="display: none;">
                <div class="alert alert-success">
                    <p><strong>Check your email</strong></p>
                    <p>If an account exists for that email address, a reset link is on its way.</p>
                </div>
                <a href="<?php echo AUTH_URL; ?>/index.php" class="btn btn-success btn-block mt-md">
                    Back to Sign In
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/auth-footer.php'; ?>
