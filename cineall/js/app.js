/**
 * Main Application Module
 */

const App = {
    currentPage: 'home',
    state: {
        searchQuery: '',
        filters: {},
        watchlist: [],
        subscriptions: [],
        mySvcOnly: false,
        user: null
    },

    async init() {
        console.log('Initializing CineAll...');
        this.setupNavigation();
        this.setupSearch();
        this.setupAuth();
        await this.checkAuth();
        this.navigateTo('home');
    },

    async checkAuth() {
        try {
            const result = await API.auth.check();
            if (result.logged_in) {
                this.state.user = result.user;
                this.updateAuthButton();
                await this.loadUserData();
            } else {
                this.updateAuthButton();
            }
        } catch (e) {
            this.updateAuthButton();
        }
    },

    updateAuthButton() {
        const btn = document.querySelector('[data-nav="account"]');
        if (!btn) return;
        if (this.state.user) {
            const name = this.state.user.display_name || this.state.user.username;
            const initials = name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
            btn.textContent = initials;
            btn.title = 'Login sebagai ' + name;
            btn.style.background = 'var(--accent)';
            btn.style.color = 'var(--bg)';
        } else {
            btn.textContent = 'Login';
            btn.title = 'Login';
            btn.style.background = '';
            btn.style.color = '';
        }
    },

    setupAuth() {
        const modal = document.createElement('div');
        modal.id = 'auth-modal';
        modal.style.cssText = 'display:none;position:fixed;inset:0;z-index:1000;background:rgba(10,9,8,0.85);backdrop-filter:blur(4px);align-items:center;justify-content:center;';
        modal.innerHTML = `
            <div style="background:var(--surface);border:1px solid var(--border-color);padding:40px;width:360px;position:relative;">
                <div style="font-family:var(--font-mono);font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--accent);margin-bottom:8px;">CineAll · Account</div>
                <div id="auth-modal-title" style="font-family:var(--font-serif);font-size:32px;font-weight:400;color:var(--fg);margin-bottom:32px;">Sign in.</div>
                <div id="login-form">
                    <div style="margin-bottom:16px;">
                        <label style="font-family:var(--font-mono);font-size:9px;letter-spacing:1.6px;text-transform:uppercase;color:var(--muted);display:block;margin-bottom:6px;">Username atau Email</label>
                        <input id="login-username" type="text" placeholder="demo" style="width:100%;padding:10px 12px;background:var(--bg);border:1px solid var(--border-color);color:var(--fg);font-family:var(--font-mono);font-size:13px;outline:none;box-sizing:border-box;">
                    </div>
                    <div style="margin-bottom:24px;">
                        <label style="font-family:var(--font-mono);font-size:9px;letter-spacing:1.6px;text-transform:uppercase;color:var(--muted);display:block;margin-bottom:6px;">Password</label>
                        <input id="login-password" type="password" placeholder="••••••••" style="width:100%;padding:10px 12px;background:var(--bg);border:1px solid var(--border-color);color:var(--fg);font-family:var(--font-mono);font-size:13px;outline:none;box-sizing:border-box;">
                    </div>
                    <div id="login-error" style="color:oklch(0.65 0.18 30);font-family:var(--font-mono);font-size:11px;margin-bottom:16px;display:none;"></div>
                    <button id="login-submit" class="btn btn-primary" style="width:100%;padding:12px;">Masuk</button>
                </div>
                <div id="logout-form" style="display:none;">
                    <div id="logged-in-info" style="font-family:var(--font-mono);font-size:12px;color:var(--muted);margin-bottom:24px;"></div>
                    <button id="logout-submit" class="btn btn-secondary" style="width:100%;padding:12px;">Keluar</button>
                </div>
                <button id="auth-modal-close" style="position:absolute;top:16px;right:16px;background:none;border:none;color:var(--muted);font-size:24px;cursor:pointer;line-height:1;">×</button>
            </div>
        `;
        document.body.appendChild(modal);

        const accountBtn = document.querySelector('[data-nav="account"]');
        if (accountBtn) {
            accountBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.openAuthModal();
            });
        }

        document.getElementById('auth-modal-close').addEventListener('click', () => this.closeAuthModal());
        modal.addEventListener('click', (e) => { if (e.target === modal) this.closeAuthModal(); });
        document.getElementById('login-submit').addEventListener('click', () => this.doLogin());
        document.getElementById('login-password').addEventListener('keydown', (e) => { if (e.key === 'Enter') this.doLogin(); });
        document.getElementById('logout-submit').addEventListener('click', () => this.doLogout());
    },

    openAuthModal() {
        const modal = document.getElementById('auth-modal');
        modal.style.display = 'flex';
        if (this.state.user) {
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('logout-form').style.display = 'block';
            document.getElementById('auth-modal-title').textContent = 'Halo, ' + (this.state.user.display_name || this.state.user.username) + '.';
            document.getElementById('logged-in-info').textContent = 'Login sebagai @' + this.state.user.username;
        } else {
            document.getElementById('login-form').style.display = 'block';
            document.getElementById('logout-form').style.display = 'none';
            document.getElementById('auth-modal-title').textContent = 'Sign in.';
            document.getElementById('login-error').style.display = 'none';
            document.getElementById('login-username').value = '';
            document.getElementById('login-password').value = '';
            setTimeout(() => document.getElementById('login-username').focus(), 100);
        }
    },

    closeAuthModal() {
        document.getElementById('auth-modal').style.display = 'none';
    },

    async doLogin() {
        const username = document.getElementById('login-username').value.trim();
        const password = document.getElementById('login-password').value;
        const errorEl  = document.getElementById('login-error');
        const btn      = document.getElementById('login-submit');

        if (!username || !password) {
            errorEl.textContent = 'Username dan password wajib diisi.';
            errorEl.style.display = 'block';
            return;
        }

        btn.textContent = 'Masuk...';
        btn.disabled = true;
        errorEl.style.display = 'none';

        try {
            const result = await API.auth.login(username, password);
            if (result.success) {
                this.state.user = result.data.user;
                this.updateAuthButton();
                await this.loadUserData();
                this.closeAuthModal();
                this.loadPage(this.currentPage);
            } else {
                errorEl.textContent = result.error || 'Login gagal.';
                errorEl.style.display = 'block';
            }
        } catch (e) {
            errorEl.textContent = 'Login gagal. Coba lagi.';
            errorEl.style.display = 'block';
        } finally {
            btn.textContent = 'Masuk';
            btn.disabled = false;
        }
    },

    async doLogout() {
        await API.auth.logout();
        this.state.user = null;
        this.state.watchlist = [];
        this.state.subscriptions = [];
        this.updateAuthButton();
        this.closeAuthModal();
        this.navigateTo('home');
    },

    setupNavigation() {
        document.querySelectorAll('[data-nav]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = e.currentTarget.dataset.nav;
                if (page === 'account') return;
                this.navigateTo(page);
            });
        });

        const logo = document.querySelector('.top-nav__logo');
        if (logo) logo.addEventListener('click', () => this.navigateTo('home'));

        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.page) this.loadPage(e.state.page, e.state.params);
        });
    },

    setupSearch() {
        const searchInput   = document.getElementById('search-input');
        const searchForm    = document.getElementById('search-form');
        const suggestionsEl = document.getElementById('search-suggestions');

        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();
                if (query.length < 2) { suggestionsEl.classList.add('hidden'); return; }
                searchTimeout = setTimeout(async () => await this.showSearchSuggestions(query), 300);
            });
            searchInput.addEventListener('focus', () => {
                if (searchInput.value.trim().length >= 2) suggestionsEl.classList.remove('hidden');
            });
            searchInput.addEventListener('blur', () => {
                setTimeout(() => suggestionsEl.classList.add('hidden'), 200);
            });
        }

        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const query = searchInput.value.trim();
                if (query) { this.navigateTo('search', { query }); suggestionsEl.classList.add('hidden'); }
            });
        }
    },

    async showSearchSuggestions(query) {
        try {
            const result = await API.movies.search(query, 5);
            const suggestionsEl = document.getElementById('search-suggestions');
            if (!result.movies || result.movies.length === 0) { suggestionsEl.classList.add('hidden'); return; }

            suggestionsEl.innerHTML = result.movies.map(movie => `
                <button class="search-suggestion" data-movie-id="${movie.id}">
                    ${Components.poster(movie, { width: 40, height: 58, showMeta: false })}
                    <div>
                        <div style="font-family:var(--font-serif);font-size:14px;">${movie.title}</div>
                        <div style="font-family:var(--font-mono);font-size:9px;letter-spacing:1.4px;text-transform:uppercase;color:var(--muted);margin-top:2px;">
                            ${movie.year} · ${movie.director} · ${movie.runtime}m
                        </div>
                    </div>
                    <div style="display:flex;gap:3px;">
                        ${movie.availability ? movie.availability.slice(0, 3).map(a => Components.platformChip(a, 14)).join('') : ''}
                    </div>
                </button>
            `).join('');

            suggestionsEl.querySelectorAll('.search-suggestion').forEach(btn => {
                btn.addEventListener('click', () => this.navigateTo('detail', { id: btn.dataset.movieId }));
            });
            suggestionsEl.classList.remove('hidden');
        } catch (error) {
            console.error('Error loading search suggestions:', error);
        }
    },

    async loadUserData() {
        try {
            const [watchlistResult, subscriptionsResult] = await Promise.all([
                API.user.getWatchlist(),
                API.user.getSubscriptions()
            ]);
            this.state.watchlist = watchlistResult.watchlist || [];
            this.state.subscriptions = subscriptionsResult.subscriptions
                .filter(s => s.subscribed).map(s => s.platform_key);
        } catch (error) {
            console.warn('Tidak bisa memuat data user (belum login?)');
        }
    },

    navigateTo(page, params = {}) {
        document.querySelectorAll('[data-nav]').forEach(link => {
            link.classList.toggle('active', link.dataset.nav === page);
        });
        const url = params.id ? `?page=${page}&id=${params.id}` :
                    params.query ? `?page=${page}&q=${params.query}` : `?page=${page}`;
        history.pushState({ page, params }, '', url);
        this.loadPage(page, params);
    },

    async loadPage(page, params = {}) {
        this.currentPage = page;
        const contentEl = document.getElementById('main-content');
        if (!contentEl) return;
        contentEl.innerHTML = Components.loading();

        try {
            switch (page) {
                case 'home':       await Pages.home(contentEl); break;
                case 'search':     await Pages.search(contentEl, params.query || ''); break;
                case 'detail':     await Pages.detail(contentEl, params.id); break;
                case 'genres':     await Pages.genres(contentEl); break;
                case 'watchlist':  await Pages.watchlist(contentEl); break;
                case 'account':    await Pages.account(contentEl); break;
                case 'compare':    await Pages.compare(contentEl); break;
                case 'recs':       await Pages.recommendations(contentEl); break;
                default:           contentEl.innerHTML = '<div class="empty-state">Page not found</div>';
            }
        } catch (error) {
            console.error('Error loading page:', error);
            contentEl.innerHTML = `<div class="empty-state">Error loading page. Please try again.</div>`;
        }
    },

    async toggleWatchlist(movieId) {
        if (!this.state.user) {
            this.openAuthModal();
            return false;
        }
        const isInWatchlist = this.state.watchlist.some(m => m.id == movieId);
        try {
            if (isInWatchlist) {
                await API.user.removeFromWatchlist(movieId);
                this.state.watchlist = this.state.watchlist.filter(m => m.id != movieId);
            } else {
                await API.user.addToWatchlist(movieId);
                const result = await API.user.getWatchlist();
                this.state.watchlist = result.watchlist || [];
            }
            return !isInWatchlist;
        } catch (error) {
            console.error('Error toggling watchlist:', error);
            throw error;
        }
    },

    isInWatchlist(movieId) {
        return this.state.watchlist.some(m => m.id == movieId);
    }
};

document.addEventListener('DOMContentLoaded', () => { App.init(); });