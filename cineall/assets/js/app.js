const App = {
    currentPage: 'home',
    state: {
        searchQuery: '',
        filters: {},
        watchlist: [],
        subscriptions: []
    },

    async init() {
        console.log('Initializing CineAll...');

        this.setupNavigation();

        this.setupSearch();

        await this.loadUserData();

        this.navigateTo('home');
    },

    setupNavigation() {
        document.querySelectorAll('[data-nav]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = e.currentTarget.dataset.nav;
                this.navigateTo(page);
            });
        });

        const logo = document.querySelector('.top-nav__logo');
        if (logo) {
            logo.addEventListener('click', () => this.navigateTo('home'));
        }

        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.page) {
                this.loadPage(e.state.page, e.state.params);
            }
        });
    },

    setupSearch() {
        const searchInput = document.getElementById('search-input');
        const searchForm = document.getElementById('search-form');
        const suggestionsEl = document.getElementById('search-suggestions');

        if (searchInput) {
            let searchTimeout;

            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();

                if (query.length < 2) {
                    suggestionsEl.classList.add('hidden');
                    return;
                }

                searchTimeout = setTimeout(async () => {
                    await this.showSearchSuggestions(query);
                }, 300);
            });

            searchInput.addEventListener('focus', () => {
                if (searchInput.value.trim().length >= 2) {
                    suggestionsEl.classList.remove('hidden');
                }
            });

            searchInput.addEventListener('blur', () => {
                setTimeout(() => suggestionsEl.classList.add('hidden'), 200);
            });
        }

        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const query = searchInput.value.trim();
                if (query) {
                    this.navigateTo('search', { query });
                    suggestionsEl.classList.add('hidden');
                }
            });
        }
    },

    async showSearchSuggestions(query) {
        try {
            const result = await API.movies.search(query, 5);
            const suggestionsEl = document.getElementById('search-suggestions');

            if (!result.movies || result.movies.length === 0) {
                suggestionsEl.classList.add('hidden');
                return;
            }

            suggestionsEl.innerHTML = result.movies.map(movie => `
                <button class="search-suggestion" data-movie-id="${movie.id}">
                    ${Components.poster(movie, { width: 40, height: 58, showMeta: false })}
                    <div>
                        <div style="font-family: var(--font-serif); font-size: 14px;">
                            ${movie.title}
                        </div>
                        <div style="font-family: var(--font-mono); font-size: 9px; letter-spacing: 1.4px; text-transform: uppercase; color: var(--muted); margin-top: 2px;">
                            ${movie.year} · ${movie.director} · ${movie.runtime}m
                        </div>
                    </div>
                    <div style="display: flex; gap: 3px;">
                        ${movie.availability ? movie.availability.slice(0, 3).map(a =>
                            Components.platformChip(a, 14)
                        ).join('') : ''}
                    </div>
                </button>
            `).join('');

            suggestionsEl.querySelectorAll('.search-suggestion').forEach(btn => {
                btn.addEventListener('click', () => {
                    const movieId = btn.dataset.movieId;
                    this.navigateTo('detail', { id: movieId });
                });
            });

            suggestionsEl.classList.remove('hidden');
        } catch (error) {
            console.error('Error loading search suggestions:', error);
        }
    },

    async loadUserData() {
        // Only registered (signed-in) users have saved data.
        if (!this.isLoggedIn()) {
            this.state.watchlist = [];
            this.state.subscriptions = [];
            return;
        }
        try {
            const [watchlistResult, subscriptionsResult] = await Promise.all([
                API.user.getWatchlist(),
                API.user.getSubscriptions()
            ]);

            this.state.watchlist = watchlistResult.watchlist || [];
            this.state.subscriptions = (subscriptionsResult.subscriptions || [])
                .filter(s => s.subscribed)
                .map(s => s.platform_key);

        } catch (error) {
            console.error('Error loading user data:', error);
            this.state.watchlist = [];
            this.state.subscriptions = [];
        }
    },

    isLoggedIn() {
        return !!(window.CINEALL && window.CINEALL.user);
    },

    // Lightweight toast notification (auto-dismisses).
    notify(message, type = 'info') {
        let host = document.getElementById('toast-host');
        if (!host) {
            host = document.createElement('div');
            host.id = 'toast-host';
            host.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:9999;display:flex;flex-direction:column;gap:8px;align-items:center;';
            document.body.appendChild(host);
        }
        const toast = document.createElement('div');
        const accent = type === 'error' ? 'var(--accent, #c0563b)'
                     : type === 'success' ? 'var(--good, #4a7c59)'
                     : 'var(--fg, #e8e4da)';
        toast.style.cssText = `background:var(--bg-elev,#1a1814);color:var(--fg,#e8e4da);border:1px solid ${accent};border-left:3px solid ${accent};padding:12px 18px;border-radius:4px;font-family:var(--font-serif,serif);font-size:14px;box-shadow:0 8px 24px rgba(0,0,0,.4);max-width:360px;`;
        toast.innerHTML = message;
        host.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity .3s'; }, 2600);
        setTimeout(() => toast.remove(), 3000);
    },

    // Require login for an action; if guest, nudge to sign in. Returns true if OK.
    requireLogin(actionText = 'do that') {
        if (this.isLoggedIn()) return true;
        const url = (window.CINEALL && window.CINEALL.authUrl) ? window.CINEALL.authUrl + '/index.php' : 'auth/index.php';
        this.notify(`Please <a href="${url}" style="color:var(--accent);text-decoration:underline;">sign in</a> to ${actionText}.`, 'error');
        return false;
    },

    navigateTo(page, params = {}) {
        document.querySelectorAll('[data-nav]').forEach(link => {
            if (link.dataset.nav === page) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });

        const url = params.id ? `?page=${page}&id=${params.id}` :
                   params.query ? `?page=${page}&q=${params.query}` :
                   `?page=${page}`;

        history.pushState({ page, params }, '', url);

        this.loadPage(page, params);
    },

    async loadPage(page, params = {}) {
        this.currentPage = page;
        const contentEl = document.getElementById('main-content');

        if (!contentEl) {
            console.error('Main content element not found');
            return;
        }

        contentEl.innerHTML = Components.loading();

        try {
            switch (page) {
                case 'home':
                    await Pages.home(contentEl);
                    break;
                case 'search':
                    await Pages.search(contentEl, params.query || '');
                    break;
                case 'detail':
                    await Pages.detail(contentEl, params.id);
                    break;
                case 'genres':
                    await Pages.genres(contentEl);
                    break;
                case 'watchlist':
                    await Pages.watchlist(contentEl);
                    break;
                case 'account':
                    await Pages.account(contentEl);
                    break;
                case 'compare':
                    await Pages.compare(contentEl);
                    break;

                default:
                    contentEl.innerHTML = '<div class="empty-state">Page not found</div>';
            }
        } catch (error) {
            console.error('Error loading page:', error);
            contentEl.innerHTML = `<div class="empty-state">Error loading page. Please try again.</div>`;
        }
    },

    async toggleWatchlist(movieId) {
        if (!this.requireLogin('save films to your watchlist')) {
            throw new Error('not-logged-in');
        }
        const isInWatchlist = this.state.watchlist.some(m => m.id == movieId);

        try {
            if (isInWatchlist) {
                await API.user.removeFromWatchlist(movieId);
                this.state.watchlist = this.state.watchlist.filter(m => m.id != movieId);
                this.notify('Removed from your watchlist.', 'info');
            } else {
                await API.user.addToWatchlist(movieId);
                const result = await API.user.getWatchlist();
                this.state.watchlist = result.watchlist || [];
                this.notify('Added to your watchlist.', 'success');
            }

            return !isInWatchlist;
        } catch (error) {
            console.error('Error toggling watchlist:', error);
            this.notify('Could not update your watchlist. Please try again.', 'error');
            throw error;
        }
    },

    isInWatchlist(movieId) {
        return this.state.watchlist.some(m => m.id == movieId);
    },

    getYouTubeEmbedUrl(url) {
        if (!url) return '';
        let videoId = '';
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        const match = url.match(regExp);
        if (match && match[2].length === 11) {
            videoId = match[2];
        } else {
            return url;
        }
        return `https://www.youtube.com/embed/${videoId}?autoplay=1`;
    },

    showTrailer(title, url) {
        const embedUrl = this.getYouTubeEmbedUrl(url);
        if (!embedUrl) return;

        // Remove existing trailer modal if it exists for some reason
        const existing = document.getElementById('trailer-modal-backdrop');
        if (existing) existing.remove();

        const backdrop = document.createElement('div');
        backdrop.id = 'trailer-modal-backdrop';
        backdrop.className = 'modal-backdrop';
        backdrop.innerHTML = `
            <div class="modal" style="max-width: 800px; padding: 24px;">
                <button class="modal__close" id="trailer-modal-close" style="top: 16px; right: 16px;">&times;</button>
                <div style="font-family: var(--font-serif); font-size: 18px; margin-bottom: 16px; color: var(--fg); font-weight: 500; padding-right: 40px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                    ${title}
                </div>
                <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: var(--radius-sm); background: #000;">
                    <iframe 
                        src="${embedUrl}" 
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        `;

        document.body.appendChild(backdrop);

        const closeBtn = document.getElementById('trailer-modal-close');
        const closeModal = () => {
            backdrop.remove();
            document.removeEventListener('keydown', handleKeyDown);
        };

        const handleKeyDown = (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        };

        closeBtn.addEventListener('click', closeModal);
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) {
                closeModal();
            }
        });
        document.addEventListener('keydown', handleKeyDown);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    App.init();
});
