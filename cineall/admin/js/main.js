/**
 * CineAll Admin - Main JavaScript
 *
 * Core utilities and helper functions for the admin panel
 */

// ==================== THEME MANAGEMENT ====================
const Theme = {
    get: function() {
        return localStorage.getItem('theme') || 'dark';
    },

    set: function(theme) {
        localStorage.setItem('theme', theme);
        document.documentElement.setAttribute('data-theme', theme);
    },

    toggle: function() {
        const current = this.get();
        const next = current === 'dark' ? 'light' : 'dark';
        this.set(next);
        return next;
    },

    init: function() {
        const saved = this.get();
        document.documentElement.setAttribute('data-theme', saved);
    }
};

// ==================== API HELPER ====================
const API = {
    baseUrl: '/api',

    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}/${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'API request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    },

    post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
};

// ==================== TOAST NOTIFICATIONS ====================
const Toast = {
    container: null,

    init: function() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
            `;
            document.body.appendChild(this.container);
        }
    },

    show: function(message, type = 'info', duration = 3000) {
        this.init();

        const toast = document.createElement('div');
        toast.className = `alert alert-${type}`;
        toast.style.cssText = `
            min-width: 300px;
            animation: slideIn 0.3s ease-out;
        `;
        toast.textContent = message;

        this.container.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    success: function(message) {
        this.show(message, 'success');
    },

    error: function(message) {
        this.show(message, 'error');
    },

    warning: function(message) {
        this.show(message, 'warning');
    },

    info: function(message) {
        this.show(message, 'info');
    }
};

// Add animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ==================== MODAL ====================
const Modal = {
    create: function(title, content, buttons = []) {
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        `;

        const modal = document.createElement('div');
        modal.style.cssText = `
            background: var(--abg2);
            border: 1px solid var(--aborder);
            border-radius: 4px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow: auto;
        `;

        const header = document.createElement('div');
        header.style.cssText = `
            padding: 20px;
            border-bottom: 1px solid var(--aborder);
            font-family: var(--font-serif);
            font-size: 24px;
            font-weight: 500;
        `;
        header.textContent = title;

        const body = document.createElement('div');
        body.style.cssText = `padding: 20px;`;
        body.innerHTML = content;

        const footer = document.createElement('div');
        footer.style.cssText = `
            padding: 20px;
            border-top: 1px solid var(--aborder);
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        `;

        buttons.forEach(btn => {
            const button = document.createElement('button');
            button.className = btn.class || 'btn btn-ghost';
            button.textContent = btn.text;
            button.onclick = () => {
                if (btn.onClick) btn.onClick();
                this.close(overlay);
            };
            footer.appendChild(button);
        });

        modal.appendChild(header);
        modal.appendChild(body);
        modal.appendChild(footer);
        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                this.close(overlay);
            }
        });

        return overlay;
    },

    close: function(overlay) {
        overlay.remove();
    },

    confirm: function(title, message, onConfirm) {
        return this.create(title, `<p>${message}</p>`, [
            { text: 'Cancel', class: 'btn btn-ghost' },
            { text: 'Confirm', class: 'btn btn-primary', onClick: onConfirm }
        ]);
    }
};

// ==================== UTILITIES ====================
const Utils = {
    // Format number with commas
    formatNumber: function(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    },

    // Format date
    formatDate: function(date) {
        const d = new Date(date);
        return d.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    // Debounce function
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Copy to clipboard
    copyToClipboard: function(text) {
        navigator.clipboard.writeText(text).then(() => {
            Toast.success('Copied to clipboard');
        }).catch(() => {
            Toast.error('Failed to copy');
        });
    },

    // Generate star rating HTML
    starRating: function(rating, max = 5) {
        const filled = '★'.repeat(rating);
        const empty = '☆'.repeat(max - rating);
        return filled + empty;
    }
};

// ==================== INITIALIZE ====================
document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme
    Theme.init();

    // Theme toggle button
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const newTheme = Theme.toggle();
            this.textContent = newTheme === 'dark' ? '☀ Light' : '☾ Dark';
        });
    }

    // Active nav item
    const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';
    document.querySelectorAll('.nav-item').forEach(item => {
        const href = item.getAttribute('href');
        if (href && href.includes(currentPage)) {
            item.classList.add('active');
        }
    });

    console.log('CineAll Admin initialized');
});
