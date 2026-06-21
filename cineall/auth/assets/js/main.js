/**
 * ============================================================================
 * CineAll - Main JavaScript
 * ============================================================================
 * Global utilities and helper functions
 * Easy to extend and customize
 */

// ============================================================================
// CONFIGURATION
// ============================================================================
const APP_URL = window.APP_URL || '/cineall';
const API_URL = window.API_URL || (APP_URL + '/auth/api');

// ============================================================================
// API HELPER FUNCTIONS
// ============================================================================

/**
 * Make API request
 * @param {string} endpoint - API endpoint (e.g., 'login.php')
 * @param {object} data - Data to send
 * @param {string} method - HTTP method (default: POST)
 * @returns {Promise}
 */
async function apiRequest(endpoint, data = {}, method = 'POST') {
    try {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
        };

        if (method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(`${API_URL}/${endpoint}`, options);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Request failed');
        }

        return result;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// ============================================================================
// FORM HANDLING UTILITIES
// ============================================================================

/**
 * Show loading state on button
 * @param {HTMLElement} button - Button element
 * @param {boolean} loading - Loading state
 */
function setButtonLoading(button, loading) {
    if (loading) {
        button.dataset.originalText = button.textContent;
        button.innerHTML = '<span class="spinner"></span> Loading...';
        button.disabled = true;
    } else {
        button.textContent = button.dataset.originalText || button.textContent;
        button.disabled = false;
    }
}

/**
 * Show error message
 * @param {string} elementId - ID of error message element
 * @param {string} message - Error message
 */
function showError(elementId, message) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = message;
        element.style.display = 'block';
    }
}

/**
 * Hide error message
 * @param {string} elementId - ID of error message element
 */
function hideError(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.style.display = 'none';
    }
}

/**
 * Show alert
 * @param {string} message - Alert message
 * @param {string} type - Alert type (success, error, info, warning)
 */
function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;

    // Insert at the top of the main content
    const container = document.querySelector('.auth-form-container') ||
                     document.querySelector('.container') ||
                     document.body;

    container.insertBefore(alert, container.firstChild);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

/**
 * Validate email format
 * @param {string} email - Email address
 * @returns {boolean}
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validate form field
 * @param {HTMLElement} input - Input element
 * @param {Function} validator - Validator function
 * @param {string} errorMessage - Error message
 * @returns {boolean}
 */
function validateField(input, validator, errorMessage) {
    const value = input.value.trim();
    const isValid = validator(value);

    if (!isValid) {
        input.classList.add('error');
        const errorEl = input.parentElement.querySelector('.form-error');
        if (errorEl) {
            errorEl.textContent = errorMessage;
        }
    } else {
        input.classList.remove('error');
        const errorEl = input.parentElement.querySelector('.form-error');
        if (errorEl) {
            errorEl.textContent = '';
        }
    }

    return isValid;
}

/**
 * Clear form errors
 * @param {HTMLElement} form - Form element
 */
function clearFormErrors(form) {
    const inputs = form.querySelectorAll('.form-input, .form-select, .form-textarea');
    inputs.forEach(input => {
        input.classList.remove('error');
    });

    const errors = form.querySelectorAll('.form-error');
    errors.forEach(error => {
        error.textContent = '';
    });
}

// ============================================================================
// LOCAL STORAGE UTILITIES
// ============================================================================

/**
 * Store data in localStorage
 * @param {string} key - Storage key
 * @param {any} value - Value to store
 */
function store(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch (e) {
        console.error('LocalStorage error:', e);
    }
}

/**
 * Get data from localStorage
 * @param {string} key - Storage key
 * @returns {any}
 */
function retrieve(key) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : null;
    } catch (e) {
        console.error('LocalStorage error:', e);
        return null;
    }
}

/**
 * Remove data from localStorage
 * @param {string} key - Storage key
 */
function removeStored(key) {
    try {
        localStorage.removeItem(key);
    } catch (e) {
        console.error('LocalStorage error:', e);
    }
}

// ============================================================================
// URL UTILITIES
// ============================================================================

/**
 * Get URL parameter
 * @param {string} name - Parameter name
 * @returns {string|null}
 */
function getUrlParam(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

/**
 * Redirect to URL
 * @param {string} path - Path to redirect to
 */
function redirect(path) {
    window.location.href = APP_URL + path;
}

// ============================================================================
// ANIMATION UTILITIES
// ============================================================================

/**
 * Fade in element
 * @param {HTMLElement} element - Element to fade in
 * @param {number} duration - Duration in ms
 */
function fadeIn(element, duration = 300) {
    element.style.opacity = 0;
    element.style.display = 'block';

    let start = null;
    function animate(timestamp) {
        if (!start) start = timestamp;
        const progress = timestamp - start;
        element.style.opacity = Math.min(progress / duration, 1);

        if (progress < duration) {
            requestAnimationFrame(animate);
        }
    }

    requestAnimationFrame(animate);
}

/**
 * Fade out element
 * @param {HTMLElement} element - Element to fade out
 * @param {number} duration - Duration in ms
 */
function fadeOut(element, duration = 300) {
    let start = null;
    function animate(timestamp) {
        if (!start) start = timestamp;
        const progress = timestamp - start;
        element.style.opacity = 1 - Math.min(progress / duration, 1);

        if (progress < duration) {
            requestAnimationFrame(animate);
        } else {
            element.style.display = 'none';
        }
    }

    requestAnimationFrame(animate);
}

// ============================================================================
// DEBOUNCE UTILITY
// ============================================================================

/**
 * Debounce function
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in ms
 * @returns {Function}
 */
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ============================================================================
// INITIALIZATION
// ============================================================================

// Run when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('CineAll App Initialized');

    // Add input event listeners for real-time validation
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            // Clear error on input
            this.classList.remove('error');
            const errorEl = this.parentElement.querySelector('.form-error');
            if (errorEl) {
                errorEl.textContent = '';
            }
        });
    });

    // Handle Enter key on forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.click();
                }
            }
        });
    });
});

// ============================================================================
// EXPORT (if using modules)
// ============================================================================
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        apiRequest,
        setButtonLoading,
        showError,
        hideError,
        showAlert,
        validateEmail,
        validateField,
        clearFormErrors,
        store,
        retrieve,
        removeStored,
        getUrlParam,
        redirect,
        fadeIn,
        fadeOut,
        debounce
    };
}
