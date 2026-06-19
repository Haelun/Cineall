/**
 * Pages Module
 *
 * Functions to render different page layouts
 */

const Pages = {
    /**
     * Home Page
     */
    async home(container) {
        try {
            const result = await API.movies.homeRows();
            const rows = result.rows || [];

            const hero = rows[0]?.movies[0];

            let html = '';

            // Hero Section
            if (hero) {
                html += `
                    <div class="hero">
                        <div class="hero__background"
                             style="background: radial-gradient(60% 80% at 80% 30%, ${hero.scheme_color_1} 0%, transparent 60%), var(--bg);"></div>
                        <div class="hero__content">
                            <div>
                                <div class="hero__kicker">⌬ Volume 04 · The week in cinema</div>
                                <div class="hero__title">
                                    ${hero.title}
                                </div>
                                <div class="hero__description">
                                    ${hero.tagline} <br/><br/>
                                    directed by ${hero.director} . ${hero.year} 
                                </div>
                                <div class="hero__actions">
                                    <button class="btn btn-primary" onclick="App.navigateTo('detail', {id: '${hero.id}'})">
                                        Read the dossier →
                                    </button>
                                    <button class="btn btn-secondary btn-small">▶ Trailer · 2:14</button>
                                    <div style="font-family: var(--font-mono); font-size: 10px; letter-spacing: 1.6px; text-transform: uppercase; color: var(--muted); display: flex; gap: 8px; align-items: center;">
                                        Streaming on
                                        ${hero.availability ? hero.availability.slice(0, 2).map(a =>
                                            Components.platformChip(a, 20)
                                        ).join('') : ''}
                                    </div>
                                </div>
                            </div>
                            <div class="hero__poster-wrapper">
                                <div class="hero__poster">
                                    ${Components.poster(hero, { width: 300, height: 440 })}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Stats Strip
            html += `
                <div class="stats-strip">
                    <div><span class="stat__value">1,247</span> films catalogued</div>
                    <div><span class="stat__value">8</span> services unified</div>
                    <div><span class="stat__value">43</span> new this week</div>
                    <div><span class="stat__value">17</span> leaving in 7 days</div>
                </div>
            `;

            // Movie Rows
            html += '<div style="padding: 48px;">';
            rows.forEach(row => {
                html += `
                    <section style="margin-bottom: 56px;">
                        ${Components.sectionHeader(row.kicker, row.title, 'See all')}
                        <div class="movie-row">
                            ${row.movies.map(m =>
                                Components.movieCard(m, { width: 200 })
                            ).join('')}
                        </div>
                    </section>
                `;
            });
            html += '</div>';

            container.innerHTML = html;

            // Add click handlers
            this.attachMovieCardHandlers();

        } catch (error) {
            console.error('Error loading home page:', error);
            container.innerHTML = Components.emptyState('Error loading content');
        }
    },

    /**
     * Search/Browse Page
     */
    async search(container, query = '') {
        try {
            // Load genres and platforms for filters
            const [genresResult, platformsResult] = await Promise.all([
                API.genres.list(),
                API.platforms.list()
            ]);

            const genres = genresResult.genres || [];
            const platforms = platformsResult.platforms || [];

            // Load movies
            const filters = { ...App.state.filters };
            if (query) filters.query = query;

            const moviesResult = await API.movies.list(filters);
            const movies = moviesResult.movies || [];

            const html = `
                <div class="search-layout">
                    <!-- Filters Sidebar -->
                    <aside class="search-sidebar">
                        <div class="search-sidebar__title">Refine</div>

                        <!-- Genre Filter -->
                        <div class="filter-group">
                            <div class="filter-group__title">Genre</div>
                            ${genres.slice(0, 8).map(g => `
                                <label class="filter-row">
                                    <input type="checkbox" value="${g.name}" class="genre-filter">
                                    <span class="filter-row__label">${g.name}</span>
                                    <span class="filter-row__count">${g.movie_count}</span>
                                </label>
                            `).join('')}
                        </div>

                        <!-- Platform Filter -->
                        <div class="filter-group">
                            <div class="filter-group__title">Streaming on</div>
                            ${platforms.map(p => `
                                <label class="filter-row">
                                    <input type="checkbox" value="${p.platform_key}" class="platform-filter">
                                    ${Components.platformChip(p, 14)}
                                    <span class="filter-row__label">${p.name}</span>
                                </label>
                            `).join('')}
                        </div>
                    </aside>

                    <!-- Results -->
                    <main class="search-content">
                        <div class="search-header">
                            <div class="search-header__info">
                                <div class="search-header__count">
                                    ${movies.length} results ${query ? `for "${query}"` : ''}
                                </div>
                                <div class="search-header__title">
                                    ${query ? `"${query}"` : 'Browse the library'}
                                </div>
                            </div>
                            <div class="search-header__sort">
                                <button class="btn btn-small" data-sort="relevance">Relevance</button>
                                <button class="btn btn-small" data-sort="rating">Rating</button>
                                <button class="btn btn-small" data-sort="year">Newest</button>
                                <button class="btn btn-small" data-sort="title">A–Z</button>
                            </div>
                        </div>

                        <div class="movie-grid" id="search-results">
                            ${movies.length > 0 ?
                                movies.map(m => Components.movieCard(m, { width: 200 })).join('') :
                                Components.emptyState('Nothing in the catalogue matches that yet.')
                            }
                        </div>
                    </main>
                </div>
            `;

            container.innerHTML = html;
            this.attachMovieCardHandlers();

        } catch (error) {
            console.error('Error loading search page:', error);
            container.innerHTML = Components.emptyState('Error loading search');
        }
    },

    /**
     * Movie Detail Page
     */
    async detail(container, movieId) {
        try {
            const movie = await API.movies.detail(movieId);
            const inWatchlist = App.isInWatchlist(movieId);

            const html = `
                <!-- Backdrop -->
                <div class="detail-backdrop"
                     style="background: linear-gradient(180deg, ${movie.scheme_color_1} 0%, var(--bg) 100%);">
                    <div class="detail-backdrop__pattern"></div>
                    <div class="detail-backdrop__gradient"></div>
                    <div class="detail-backdrop__breadcrumb">
                        ← Back to discover · Vol. 04 · ${movie.year}
                    </div>
                </div>

                <div class="detail-content">
                    <div class="detail-layout">
                        <!-- Poster -->
                        <div class="detail-sidebar">
                            ${Components.poster(movie, { width: 320, height: 464 })}
                            <div style="margin-top: 16px; display: flex; gap: 8px;">
                                <button class="btn btn-secondary btn-small" style="flex: 1;"
                                        id="watchlist-btn">
                                    ${inWatchlist ? '✓ In watchlist' : '+ Watchlist'}
                                </button>
                                <button class="btn btn-secondary btn-small" style="flex: 1;">
                                    ▶ Trailer
                                </button>
                            </div>
                        </div>

                        <!-- Main Info -->
                        <div class="detail-main">
                            <div class="detail-info__kicker">Directed by ${movie.director}</div>
                            <div class="detail-info__title">${movie.title}</div>
                            <div class="detail-info__tagline">"${movie.tagline}"</div>

                            <div class="detail-info__meta">
                                <span>${movie.year}</span>
                                <span class="detail-info__meta-dot"></span>
                                <span>${Math.floor(movie.runtime / 60)}h ${movie.runtime % 60}m</span>
                                <span class="detail-info__meta-dot"></span>
                                <span>${movie.rating}</span>
                                <span class="detail-info__meta-dot"></span>
                                <span>${Array.isArray(movie.genres) ? movie.genres.join(' · ') : movie.genres}</span>
                            </div>

                            <div style="margin-bottom: 32px;">
                                ${Components.scoreSplit(movie.critic_score, movie.audience_score, 'lg')}
                            </div>

                            <div class="detail-info__synopsis">
                                <span class="detail-info__synopsis-dropcap">${movie.synopsis[0]}</span>
                                ${movie.synopsis.slice(1)}
                            </div>

                            <div class="detail-info__table">
                                <div class="detail-info__table-label">Director</div>
                                <div>${movie.director}</div>
                                <div class="detail-info__table-label">Cast</div>
                                <div>${Array.isArray(movie.cast_members) ? movie.cast_members.join(', ') : movie.cast_members}</div>
                                <div class="detail-info__table-label">Genre</div>
                                <div>${Array.isArray(movie.genres) ? movie.genres.join(', ') : movie.genres}</div>
                            </div>
                        </div>

                        <!-- Where to Watch -->
                        <aside class="detail-sidebar">
                            <div class="watch-widget">
                                <div class="watch-widget__kicker">Where to watch</div>
                                <div class="watch-widget__title">Pick a service</div>
                                <div class="watch-widget__options">
                                    ${movie.availability.map((a, i) =>
                                        Components.platformRow(a, i === 0)
                                    ).join('')}
                                </div>
                                <div class="watch-widget__disclaimer">
                                    Prices in USD. Subscription required where indicated. CineAll links you out — we don't host any video.
                                </div>
                            </div>
                        </aside>
                    </div>

                    <!-- Related Movies -->
                    ${movie.related && movie.related.length > 0 ? `
                        <section style="margin-top: 80px;">
                            ${Components.sectionHeader('If this resonates', `More from ${movie.genres[0]}`)}
                            <div class="movie-row">
                                ${movie.related.map(m =>
                                    Components.movieCard(m, { width: 200 })
                                ).join('')}
                            </div>
                        </section>
                    ` : ''}
                </div>
            `;

            container.innerHTML = html;

            // Add watchlist button handler
            const watchlistBtn = document.getElementById('watchlist-btn');
            if (watchlistBtn) {
                watchlistBtn.addEventListener('click', async () => {
                    try {
                        const newState = await App.toggleWatchlist(movieId);
                        watchlistBtn.textContent = newState ? '✓ In watchlist' : '+ Watchlist';
                    } catch (error) {
                        console.error('Error toggling watchlist:', error);
                    }
                });
            }

            this.attachMovieCardHandlers();

        } catch (error) {
            console.error('Error loading movie detail:', error);
            container.innerHTML = Components.emptyState('Error loading movie details');
        }
    },

    /**
     * Genres Page
     */
    async genres(container) {
        try {
            const genresResult = await API.genres.list();
            const genres = genresResult.genres || [];

            // Load movies for first genre
            const activeGenre = genres[0]?.name || 'Drama';
            const movies = await API.movies.byGenre(activeGenre);

            const html = `
                <div style="padding: 48px;">
                    <div style="font-family: var(--font-mono); font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: var(--accent); margin-bottom: 12px;">
                        Browse the library by
                    </div>
                    <div style="font-family: var(--font-serif); font-size: 56px; font-weight: 400; letter-spacing: -1px; color: var(--fg); margin-bottom: 32px;">
                        Genre <span style="font-style: italic; color: var(--accent);">&</span> mood
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 48px;" id="genre-buttons">
                        ${genres.map(g => `
                            <button class="btn ${g.name === activeGenre ? 'btn-primary' : 'btn-secondary'}"
                                    data-genre="${g.name}">
                                ${g.name} <span style="font-family: var(--font-mono); font-size: 10px; margin-left: 6px; opacity: 0.6;">${g.movie_count}</span>
                            </button>
                        `).join('')}
                    </div>
                    <div class="movie-grid" id="genre-movies">
                        ${movies.movies.map(m => Components.movieCard(m, { width: 200 })).join('')}
                    </div>
                </div>
            `;

            container.innerHTML = html;
            this.attachMovieCardHandlers();

        } catch (error) {
            console.error('Error loading genres page:', error);
            container.innerHTML = Components.emptyState('Error loading genres');
        }
    },

    /**
     * Watchlist Page
     */
    async watchlist(container) {
        const watchlist = App.state.watchlist;

        const html = `
            <div style="padding: 48px;">
                <div style="font-family: var(--font-mono); font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: var(--accent); margin-bottom: 12px;">
                    Saved by you
                </div>
                <div style="font-family: var(--font-serif); font-size: 56px; font-weight: 400; letter-spacing: -1px; color: var(--fg); margin-bottom: 8px;">
                    Watchlist <span style="font-style: italic; color: var(--muted);">· ${watchlist.length}</span>
                </div>
                <div style="font-family: var(--font-serif); font-size: 16px; color: var(--muted); margin-bottom: 48px; font-style: italic; max-width: 580px;">
                    Films you've put aside. Sorted by when they're leaving the services you have.
                </div>

                ${watchlist.length === 0 ?
                    Components.emptyState('Nothing saved yet. Browse the library and tap "+ Watchlist" on any film.') :
                    `<div style="display: flex; flex-direction: column; gap: 16px;">
                        ${watchlist.map(m => `
                            <div style="display: grid; grid-template-columns: 80px 1fr auto auto auto; gap: 24px; align-items: center; padding: 16px; border: 1px solid var(--border-color); border-radius: 4px;">
                                <div style="cursor: pointer;" onclick="App.navigateTo('detail', {id: '${m.id}'})">
                                    ${Components.poster(m, { width: 80, height: 116, showMeta: false })}
                                </div>
                                <div style="cursor: pointer;" onclick="App.navigateTo('detail', {id: '${m.id}'})">
                                    <div style="font-family: var(--font-serif); font-size: 22px; color: var(--fg);">${m.title}</div>
                                    <div style="font-family: var(--font-mono); font-size: 10px; letter-spacing: 1.4px; text-transform: uppercase; color: var(--muted); margin-top: 6px;">
                                        ${m.director} · ${m.year} · ${m.runtime}m · ${Array.isArray(m.genres) ? m.genres.join(' / ') : m.genres}
                                    </div>
                                </div>
                                ${Components.scoreSplit(m.critic_score, m.audience_score, 'sm')}
                                <div style="display: flex; gap: 4px;">
                                    ${m.availability.map(a => Components.platformChip(a, 22)).join('')}
                                </div>
                                <button class="btn btn-secondary btn-small" data-remove="${m.id}">Remove</button>
                            </div>
                        `).join('')}
                    </div>`
                }
            </div>
        `;

        container.innerHTML = html;

        // Add remove handlers
        container.querySelectorAll('[data-remove]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const movieId = btn.dataset.remove;
                await App.toggleWatchlist(movieId);
                await this.watchlist(container); // Reload page
            });
        });
    },

    /**
     * Account Page
     */
    async account(container) {
        const subscriptionsResult = await API.user.getSubscriptions();
        const subscriptions = subscriptionsResult.subscriptions || [];

        const html = `
            <div style="padding: 48px; max-width: 920px; margin: 0 auto;">
                <div style="font-family: var(--font-mono); font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: var(--accent); margin-bottom: 12px;">
                    Account · L. Marin
                </div>
                <div style="font-family: var(--font-serif); font-size: 56px; font-weight: 400; letter-spacing: -1px; color: var(--fg); margin-bottom: 8px;">
                    Tune the noise.
                </div>
                <div style="font-family: var(--font-serif); font-size: 16px; color: var(--muted); margin-bottom: 48px; font-style: italic; max-width: 600px;">
                    CineAll only shows what you can actually watch. Tell us what you have.
                </div>

                <section style="margin-bottom: 48px;">
                    <div style="font-family: var(--font-mono); font-size: 11px; letter-spacing: 1.6px; text-transform: uppercase; color: var(--fg); margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--border-color);">
                        Your services
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
                        ${subscriptions.map(p => `
                            <button class="btn ${p.subscribed ? 'btn-primary' : 'btn-secondary'}"
                                    style="display: grid; grid-template-columns: 32px 1fr 16px; gap: 12px; align-items: center; padding: 14px; text-align: left;"
                                    data-toggle-subscription="${p.id}">
                                ${Components.platformChip(p, 32)}
                                <div>
                                    <div style="font-family: var(--font-serif); font-size: 14px;">${p.name}</div>
                                    <div style="font-family: var(--font-mono); font-size: 9px; letter-spacing: 1.4px; text-transform: uppercase; color: var(--muted); margin-top: 2px;">
                                        ${p.subscribed ? 'Subscribed' : 'Not linked'}
                                    </div>
                                </div>
                                <div style="width: 14px; height: 14px; border-radius: 50%; background: ${p.subscribed ? 'var(--accent)' : 'transparent'}; border: 1px solid ${p.subscribed ? 'var(--accent)' : 'var(--border-color)'};"></div>
                            </button>
                        `).join('')}
                    </div>
                </section>
            </div>
        `;

        container.innerHTML = html;

        // Add subscription toggle handlers
        container.querySelectorAll('[data-toggle-subscription]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const platformId = btn.dataset.toggleSubscription;
                await API.user.toggleSubscription(platformId);
                await this.account(container); // Reload page
                await App.loadUserData(); // Refresh user data
            });
        });
    },

    /**
     * Compare Services Page
     */
    async compare(container) {
        try {
            const result = await API.platforms.stats();
            const platforms = result.platforms || [];

            const maxCount = Math.max(...platforms.map(p => p.total_titles));

            const html = `
                <div style="padding: 48px;">
                    <div style="font-family: var(--font-mono); font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: var(--accent); margin-bottom: 12px;">
                        Where the films live
                    </div>
                    <div style="font-family: var(--font-serif); font-size: 56px; font-weight: 400; letter-spacing: -1px; color: var(--fg); margin-bottom: 8px;">
                        Service <span style="font-style: italic; color: var(--accent);">by service</span>, what's actually there.
                    </div>
                    <div style="font-family: var(--font-serif); font-size: 16px; color: var(--muted); margin-bottom: 48px; font-style: italic; max-width: 640px;">
                        Coverage across the CineAll catalogue — broken out by what's included, what's rentable, and what's only for sale.
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0;">
                        <!-- Header -->
                        <div style="display: grid; grid-template-columns: 180px 1fr 80px 80px 80px 200px; gap: 24px; padding: 0 0 12px; border-bottom: 1px solid var(--border-color-hover); font-family: var(--font-mono); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: var(--muted);">
                            <div>Service</div>
                            <div>Coverage</div>
                            <div>Sub.</div>
                            <div>Rent</div>
                            <div>Buy</div>
                            <div>Sample</div>
                        </div>

                        <!-- Rows -->
                        ${platforms.map(p => {
                            const percentage = (p.total_titles / maxCount) * 100;
                            return `
                                <div style="display: grid; grid-template-columns: 180px 1fr 80px 80px 80px 200px; gap: 24px; padding: 20px 0; border-bottom: 1px solid var(--border-color); align-items: center;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        ${Components.platformChip(p, 36)}
                                        <div>
                                            <div style="font-family: var(--font-serif); font-size: 16px;">${p.name}</div>
                                            <div style="font-family: var(--font-mono); font-size: 9px; letter-spacing: 1.4px; color: var(--muted);">${p.total_titles} TITLES</div>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="height: 8px; background: var(--overlay-strong); flex: 1; border-radius: 1px; overflow: hidden;">
                                            <div style="height: 100%; width: ${percentage}%; background: oklch(0.65 0.10 ${p.hue});"></div>
                                        </div>
                                        <div style="font-family: var(--font-serif); font-size: 22px; min-width: 32px; text-align: right; font-style: italic;">${p.total_titles}</div>
                                    </div>
                                    <div style="font-family: var(--font-mono); font-size: 13px;">${p.subscription_count}</div>
                                    <div style="font-family: var(--font-mono); font-size: 13px; color: var(--muted);">${p.rent_count}</div>
                                    <div style="font-family: var(--font-mono); font-size: 13px; color: var(--muted);">${p.buy_count}</div>
                                    <div style="display: flex; gap: 6px;">
                                        ${p.sample_movies.map(m => `
                                            <div style="cursor: pointer;" onclick="App.navigateTo('detail', {id: '${m.id}'})">
                                                ${Components.poster(m, { width: 32, height: 46, showMeta: false })}
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;

            container.innerHTML = html;

        } catch (error) {
            console.error('Error loading compare page:', error);
            container.innerHTML = Components.emptyState('Error loading comparison');
        }
    },

    /**
     * Recommendations Page (Placeholder)
     */
    async recommendations(container) {
        container.innerHTML = `
            <div style="padding: 48px;">
                <div style="font-family: var(--font-mono); font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: var(--accent); margin-bottom: 12px;">
                    Tuned to you · L. Marin
                </div>
                <div style="font-family: var(--font-serif); font-size: 56px; font-weight: 400; letter-spacing: -1px; color: var(--fg); margin-bottom: 8px;">
                    For <span style="font-style: italic; color: var(--accent);">you</span>, this week.
                </div>
                <div style="font-family: var(--font-serif); font-size: 16px; color: var(--muted); margin-bottom: 48px; font-style: italic; max-width: 640px;">
                    Based on what you've saved, watched, and the services you're paying for.
                </div>
                ${Components.emptyState('Recommendations feature coming soon...')}
            </div>
        `;
    },

    /**
     * Attach click handlers to movie cards
     */
    attachMovieCardHandlers() {
        document.querySelectorAll('.movie-card').forEach(card => {
            card.addEventListener('click', () => {
                const movieId = card.dataset.movieId;
                if (movieId) {
                    App.navigateTo('detail', { id: movieId });
                }
            });
        });
    }
};
