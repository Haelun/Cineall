/**
 * ============================================================================
 * CineAll - Authentication JavaScript
 * ============================================================================
 * Handles login, signup, password reset, and 2FA
 */

// ============================================================================
// LOGIN HANDLER
// ============================================================================
async function handleLogin(event) {
    event.preventDefault();

    const form = event.target;
    const email = form.querySelector('#email').value.trim();
    const password = form.querySelector('#password').value;
    const submitBtn = form.querySelector('button[type="submit"]');

    // Validation
    let isValid = true;

    if (!validateEmail(email)) {
        showError('error-message', 'Please enter a valid email address');
        isValid = false;
    } else if (!password) {
        showError('error-message', 'Please enter your password');
        isValid = false;
    } else {
        hideError('error-message');
    }

    if (!isValid) return;

    // Submit
    setButtonLoading(submitBtn, true);

    try {
        const response = await apiRequest('login.php', {
            email: email,
            password: password
        });

        if (response.success) {
            if (response.requiresTwoFactor) {
                // Store temporary data for 2FA
                store('temp_2fa', {
                    email: email,
                    role: response.role,
                    tempToken: response.tempToken
                });

                // Redirect to 2FA page
                redirect('/verify-2fa.php');
            } else {
                // Successful login - redirect based on role
                const redirectUrl = {
                    'admin': '/admin/index.php',
                    'curator': '/curator/index.php',
                    'user': '/index.php'
                }[response.role] || '/index.php';

                window.location.href = APP_URL + redirectUrl;
            }
        }
    } catch (error) {
        showError('error-message', error.message || 'Login failed. Please try again.');
    } finally {
        setButtonLoading(submitBtn, false);
    }
}

// ============================================================================
// SIGNUP HANDLER
// ============================================================================
async function handleSignup(event) {
    event.preventDefault();

    const form = event.target;
    const name = form.querySelector('#name').value.trim();
    const email = form.querySelector('#email').value.trim();
    const password = form.querySelector('#password').value;
    const confirmPassword = form.querySelector('#confirm_password')?.value;
    const submitBtn = form.querySelector('button[type="submit"]');

    // Validation
    let isValid = true;

    if (!name || name.length < 2) {
        showError('error-message', 'Please enter your name');
        isValid = false;
    } else if (!validateEmail(email)) {
        showError('error-message', 'Please enter a valid email address');
        isValid = false;
    } else if (!password || password.length < 8) {
        showError('error-message', 'Password must be at least 8 characters');
        isValid = false;
    } else if (confirmPassword && password !== confirmPassword) {
        showError('error-message', 'Passwords do not match');
        isValid = false;
    } else {
        hideError('error-message');
    }

    if (!isValid) return;

    // Submit
    setButtonLoading(submitBtn, true);

    try {
        const response = await apiRequest('signup.php', {
            name: name,
            email: email,
            password: password
        });

        if (response.success) {
            // Redirect to app
            window.location.href = APP_URL + '/index.php';
        }
    } catch (error) {
        showError('error-message', error.message || 'Signup failed. Please try again.');
    } finally {
        setButtonLoading(submitBtn, false);
    }
}

// ============================================================================
// FORGOT PASSWORD HANDLER
// ============================================================================
async function handleForgotPassword(event) {
    event.preventDefault();

    const form = event.target;
    const email = form.querySelector('#email').value.trim();
    const submitBtn = form.querySelector('button[type="submit"]');

    // Validation
    if (!validateEmail(email)) {
        showError('error-message', 'Please enter a valid email address');
        return;
    }

    hideError('error-message');

    // Submit
    setButtonLoading(submitBtn, true);

    try {
        const response = await apiRequest('forgot-password.php', {
            email: email
        });

        if (response.success) {
            // Show success message
            document.querySelector('.forgot-form').style.display = 'none';
            document.querySelector('.forgot-success').style.display = 'block';
        }
    } catch (error) {
        // For security, show success even if email doesn't exist
        document.querySelector('.forgot-form').style.display = 'none';
        document.querySelector('.forgot-success').style.display = 'block';
    } finally {
        setButtonLoading(submitBtn, false);
    }
}

// ============================================================================
// 2FA VERIFICATION HANDLER
// ============================================================================
async function handleVerify2FA(event) {
    event.preventDefault();

    const form = event.target;
    const code = form.querySelector('#code').value.trim();
    const submitBtn = form.querySelector('button[type="submit"]');

    // Validation
    if (!code || code.length < 4) {
        showError('error-message', 'Please enter the verification code');
        return;
    }

    hideError('error-message');

    // Get temp data
    const tempData = retrieve('temp_2fa');
    if (!tempData) {
        showError('error-message', 'Session expired. Please login again.');
        setTimeout(() => redirect('/index.php'), 2000);
        return;
    }

    // Submit
    setButtonLoading(submitBtn, true);

    try {
        const response = await apiRequest('verify-2fa.php', {
            tempToken: tempData.tempToken,
            code: code
        });

        if (response.success) {
            // Clear temp data
            removeStored('temp_2fa');

            // Redirect based on role
            const redirectUrl = {
                'admin': '/admin/index.php',
                'curator': '/curator/index.php',
                'user': '/index.php'
            }[tempData.role] || '/index.php';

            window.location.href = APP_URL + redirectUrl;
        }
    } catch (error) {
        showError('error-message', error.message || 'Verification failed. Please try again.');
    } finally {
        setButtonLoading(submitBtn, false);
    }
}

// ============================================================================
// RESEND 2FA CODE
// ============================================================================
async function resend2FACode() {
    const tempData = retrieve('temp_2fa');
    if (!tempData) {
        showAlert('Session expired. Please login again.', 'error');
        return;
    }

    try {
        const response = await apiRequest('resend-2fa.php', {
            tempToken: tempData.tempToken
        });

        if (response.success) {
            showAlert('A new code has been sent.', 'success');
        }
    } catch (error) {
        showAlert(error.message || 'Failed to resend code.', 'error');
    }
}

// ============================================================================
// LOGOUT HANDLER
// ============================================================================
async function handleLogout() {
    try {
        await apiRequest('logout.php', {}, 'POST');
    } catch (error) {
        console.error('Logout error:', error);
    } finally {
        // Always redirect to login even if API fails
        window.location.href = APP_URL + '/index.php';
    }
}

// ============================================================================
// SOCIAL AUTH (Placeholder - implement based on your OAuth provider)
// ============================================================================
function handleSocialAuth(provider) {
    // Redirect to OAuth provider
    // Example: window.location.href = '/auth/google';
    alert(`Social authentication with ${provider} would be implemented here.\\n\\nYou need to set up OAuth with the provider.`);
}

// ============================================================================
// AUTO-FILL FOR TESTING (Development only - remove in production)
// ============================================================================
function fillTestCredentials(email, password) {
    const emailInput = document.querySelector('#email');
    const passwordInput = document.querySelector('#password');

    if (emailInput) emailInput.value = email;
    if (passwordInput) passwordInput.value = password;
}

// ============================================================================
// INITIALIZATION
// ============================================================================
document.addEventListener('DOMContentLoaded', function() {
    // Attach form handlers
    const loginForm = document.querySelector('#loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    const signupForm = document.querySelector('#signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', handleSignup);
    }

    const forgotForm = document.querySelector('#forgotPasswordForm');
    if (forgotForm) {
        forgotForm.addEventListener('submit', handleForgotPassword);
    }

    const verify2FAForm = document.querySelector('#verify2FAForm');
    if (verify2FAForm) {
        verify2FAForm.addEventListener('submit', handleVerify2FA);
    }

    // Resend 2FA button
    const resendBtn = document.querySelector('#resend2FA');
    if (resendBtn) {
        resendBtn.addEventListener('click', (e) => {
            e.preventDefault();
            resend2FACode();
        });
    }

    // Logout buttons
    const logoutBtns = document.querySelectorAll('.btn-logout');
    logoutBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                handleLogout();
            }
        });
    });

    // Social auth buttons
    const socialBtns = document.querySelectorAll('.btn-social');
    socialBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const provider = btn.dataset.provider;
            if (provider) {
                handleSocialAuth(provider);
            }
        });
    });
});
