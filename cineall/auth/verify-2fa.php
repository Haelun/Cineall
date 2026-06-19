<?php
/**
 * ============================================================================
 * CineAll - 2FA Verification Page
 * ============================================================================
 */

define('CINEALL_APP', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
cineall_session_start();

// Redirect if already authenticated
if (isAuthenticated()) {
    redirect('/index.php');
}

$pageTitle = 'Verify Identity';
include __DIR__ . '/components/auth-header.php';
?>

<div class="verify-container">
    <div class="verify-bg"></div>

    <div class="verify-card">
        <div class="verify-header">
            <div class="wordmark">
                CineAll<span class="wordmark-accent">.</span>
            </div>
            <span class="badge badge-curator">Step 2 · Staff</span>
        </div>

        <div class="verify-progress">
            <span class="verify-progress-bar complete"></span>
            <span class="verify-progress-bar active"></span>
            <span class="verify-progress-text">2 / 2</span>
        </div>

        <h1 class="verify-title">Verify it's you</h1>
        <p class="verify-subtitle">Password accepted. Staff accounts need a second factor.</p>

        <div class="verify-info">
            <span class="verify-info-dot"></span>
            <span id="user-email">Verifying staff account</span>
        </div>

        <form id="verify2FAForm">
            <div class="form-group">
                <label class="form-label" for="code">Authentication Code</label>
                <input type="text" id="code" name="code" class="form-input"
                       placeholder="6-digit code" autocomplete="one-time-code" required
                       pattern="[0-9]{4,6}" maxlength="6">
                <span class="form-help">Enter the code from your authenticator app</span>
                <span class="form-error"></span>
            </div>

            <div class="form-error mb-md" id="error-message" style="display: none;"></div>

            <button type="submit" class="btn btn-block" style="background: var(--accent-curator); color: var(--bg-primary);">
                Verify & Enter →
            </button>

            <div class="verify-actions">
                <a href="<?php echo AUTH_URL; ?>/index.php" class="verify-link">← Back to sign in</a>
                <button type="button" id="resend2FA" class="verify-link">Resend code</button>
            </div>

            <p class="text-center text-muted mt-md" style="font-family: var(--font-mono); font-size: 9px;">
                For demo: Enter any 4+ digits (e.g., "123456")
            </p>
        </form>
    </div>
</div>

<?php include __DIR__ . '/components/auth-footer.php'; ?>
