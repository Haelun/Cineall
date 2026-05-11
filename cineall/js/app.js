const App = {
    currentPage: 'home',
    state: {
        searchQuery: '',
        filters: {},
        watchlist: [],
        subscriptions: [],
        mySvcOnly: false
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
        try {
            const [watchlistResult, subscriptionsResult] = await Promise.all([
                API.user.getWatchlist(),
                API.user.getSubscriptions()
            ]);

            this.state.watchlist = watchlistResult.watchlist || [];
            this.state.subscriptions = subscriptionsResult.subscriptions
                .filter(s => s.subscribed)
                .map(s => s.platform_key);

        } catch (error) {
            console.error('Error loading user data:', error);
        }
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
                case 'recs':
                    await Pages.recommendations(contentEl);
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

document.addEventListener('DOMContentLoaded', () => {
    App.init();
});
