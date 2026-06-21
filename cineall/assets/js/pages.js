const Pages = {
  async home(container) {
    try {
      const result = await API.movies.homeRows();
      const rows = result.rows || [];

      const hero = rows[0]?.movies[0];

      let html = "";

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
                                    ${hero.tagline}<br/><br/>
                                    Directed by ${hero.director} · ${hero.year}
                                </div>
                                <div class="hero__actions">
                                    <button class="btn btn-primary" onclick="App.navigateTo('detail', {id: '${hero.id}'})">
                                        Read the dossier →
                                    </button>
                                    ${hero.trailer_url
                                        ? `<button class="btn btn-secondary btn-small" onclick="App.showTrailer('${hero.title.replace(/'/g, "\\'")}', '${hero.trailer_url}')">▶ Trailer</button>`
                                        : `<button class="btn btn-secondary btn-small" disabled>▶ Trailer</button>`
                                    }
                                    <div style="font-family: var(--font-mono); font-size: 10px; letter-spacing: 1.6px; text-transform: uppercase; color: var(--muted); display: flex; gap: 8px; align-items: center;">
                                        Streaming on
                                        ${
                                          hero.availability
                                            ? hero.availability
                                                .slice(0, 2)
                                                .map((a) =>
                                                  Components.platformChip(
                                                    a,
                                                    20,
                                                  ),
                                                )
                                                .join("")
                                            : ""
                                        }
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

      html += `
                <div class="stats-strip">
                    <div><span class="stat__value">1,247</span> films catalogued</div>
                    <div><span class="stat__value">8</span> services unified</div>
                    <div><span class="stat__value">43</span> new this week</div>
                    <div><span class="stat__value">17</span> leaving in 7 days</div>
                </div>
            `;

      html += '<div style="padding: 48px;">';
      rows.forEach((row) => {
        html += `
                    <section style="margin-bottom: 56px;">
                        ${Components.sectionHeader(row.kicker, row.title, "See all", "App.navigateTo('genres')")}
                        <div class="movie-row">
                            ${row.movies
                              .map((m) =>
                                Components.movieCard(m, { width: 200 }),
                              )
                              .join("")}
                        </div>
                    </section>
                `;
      });
      html += "</div>";

      container.innerHTML = html;

      this.attachMovieCardHandlers();
    } catch (error) {
      console.error("Error loading home page:", error);
      container.innerHTML = Components.emptyState("Error loading content");
    }
  },

  async search(container, query = "") {
    try {
      const [genresResult, platformsResult] = await Promise.all([
        API.genres.list(),
        API.platforms.list(),
      ]);

      const genres = genresResult.genres || [];
      const platforms = platformsResult.platforms || [];

      // Current filter/sort state (persists across re-renders of this page)
      const f = App.state.filters || (App.state.filters = {});
      if (query) f.query = query; else query = f.query || "";
      const activeSort = f.sort || "relevance";
      const activeGenres = (f.genres ? f.genres.split(",") : []).filter(Boolean);
      const activePlatforms = (f.platforms ? f.platforms.split(",") : []).filter(Boolean);

      const moviesResult = await API.movies.list({ ...f });
      const movies = moviesResult.movies || [];
      const total = moviesResult.total != null ? moviesResult.total : movies.length;

      const sortBtn = (val, label) =>
        `<button class="btn btn-small ${activeSort === val ? "btn-primary" : "btn-secondary"}" data-sort="${val}">${label}</button>`;

      const html = `
                <div class="search-layout">
                    <aside class="search-sidebar">
                        <div class="search-sidebar__title">Refine</div>

                        <div class="filter-group">
                            <div class="filter-group__title">Genre</div>
                            ${genres.slice(0, 8).map((g) => `
                                <label class="filter-row">
                                    <input type="checkbox" value="${g.name}" class="genre-filter" ${activeGenres.includes(g.name) ? "checked" : ""}>
                                    <span class="filter-row__label">${g.name}</span>
                                    <span class="filter-row__count">${g.movie_count}</span>
                                </label>
                            `).join("")}
                        </div>

                        <div class="filter-group">
                            <div class="filter-group__title">Streaming on</div>
                            ${platforms.map((p) => `
                                <label class="filter-row">
                                    <input type="checkbox" value="${p.platform_key}" class="platform-filter" ${activePlatforms.includes(p.platform_key) ? "checked" : ""}>
                                    ${Components.platformChip(p, 14)}
                                    <span class="filter-row__label">${p.name}</span>
                                </label>
                            `).join("")}
                        </div>

                        ${(activeGenres.length || activePlatforms.length || query)
                          ? `<button class="btn btn-small" id="clear-filters" style="margin-top:12px;">Clear filters</button>` : ""}
                    </aside>

                    <main class="search-content">
                        <div class="search-header">
                            <div class="search-header__info">
                                <div class="search-header__count">
                                    ${total} result${total === 1 ? "" : "s"} ${query ? `for "${query}"` : ""}
                                </div>
                                <div class="search-header__title">
                                    ${query ? `"${query}"` : "Browse the library"}
                                </div>
                            </div>
                            <div class="search-header__sort">
                                ${sortBtn("relevance", "Relevance")}
                                ${sortBtn("rating", "Rating")}
                                ${sortBtn("year", "Newest")}
                                ${sortBtn("oldest", "Oldest")}
                                ${sortBtn("title", "A\u2013Z")}
                            </div>
                        </div>

                        <div class="movie-grid" id="search-results">
                            ${movies.length > 0
                                ? movies.map((m) => Components.movieCard(m, { width: 200 })).join("")
                                : Components.emptyState("Nothing in the catalogue matches that yet.")}
                        </div>
                    </main>
                </div>
            `;

      container.innerHTML = html;
      this.attachMovieCardHandlers();

      // --- wire the sort buttons (re-run search with the chosen ordering) ---
      container.querySelectorAll("[data-sort]").forEach((btn) => {
        btn.addEventListener("click", () => {
          App.state.filters.sort = btn.dataset.sort;
          Pages.search(container, query);
        });
      });

      // --- wire the genre filters ---
      container.querySelectorAll(".genre-filter").forEach((cb) => {
        cb.addEventListener("change", () => {
          const checked = [...container.querySelectorAll(".genre-filter:checked")].map((c) => c.value);
          App.state.filters.genres = checked.join(",");
          Pages.search(container, query);
        });
      });

      // --- wire the platform filters ---
      container.querySelectorAll(".platform-filter").forEach((cb) => {
        cb.addEventListener("change", () => {
          const checked = [...container.querySelectorAll(".platform-filter:checked")].map((c) => c.value);
          App.state.filters.platforms = checked.join(",");
          Pages.search(container, query);
        });
      });

      // --- clear filters ---
      const clearBtn = container.querySelector("#clear-filters");
      if (clearBtn) {
        clearBtn.addEventListener("click", () => {
          App.state.filters = {};
          Pages.search(container, "");
        });
      }
    } catch (error) {
      console.error("Error loading search page:", error);
      container.innerHTML = Components.emptyState("Error loading search");
    }
  },

  async detail(container, movieId) {
    try {
      const movie = await API.movies.detail(movieId);
      const inWatchlist = App.isInWatchlist(movieId);

      const html = `
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
                        <div class="detail-sidebar">
                            ${Components.poster(movie, { width: 320, height: 464 })}
                            <div style="margin-top: 16px; display: flex; gap: 8px;">
                                <button class="btn btn-secondary btn-small" style="flex: 1;"
                                        id="watchlist-btn">
                                    ${inWatchlist ? "✓ In watchlist" : "+ Watchlist"}
                                </button>
                                ${movie.trailer_url
                                    ? `<button class="btn btn-secondary btn-small" style="flex: 1;" onclick="App.showTrailer('${movie.title.replace(/'/g, "\\'")}', '${movie.trailer_url}')">▶ Trailer</button>`
                                    : `<button class="btn btn-secondary btn-small" style="flex: 1;" disabled>▶ Trailer</button>`
                                }
                            </div>
                        </div>

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
                                <span>${Array.isArray(movie.genres) ? movie.genres.join(" · ") : movie.genres}</span>
                            </div>

                            <div style="margin-bottom: 32px;">
                                ${Components.scoreSplit(movie.critic_score, movie.audience_score, "lg")}
                            </div>

                            <div class="detail-info__synopsis">
                                <span class="detail-info__synopsis-dropcap">${movie.synopsis[0]}</span>
                                ${movie.synopsis.slice(1)}
                            </div>

                            <div class="detail-info__table">
                                <div class="detail-info__table-label">Director</div>
                                <div>${movie.director}</div>
                                <div class="detail-info__table-label">Cast</div>
                                <div>${Array.isArray(movie.cast_members) ? movie.cast_members.join(", ") : movie.cast_members}</div>
                                <div class="detail-info__table-label">Genre</div>
                                <div>${Array.isArray(movie.genres) ? movie.genres.join(", ") : movie.genres}</div>
                            </div>
                        </div>

                        <aside class="detail-sidebar">
                            <div class="watch-widget">
                                <div class="watch-widget__kicker">Where to watch</div>
                                <div class="watch-widget__title">Pick a service</div>
                                <div class="watch-widget__options">
                                    ${(() => {
                                        // Group availability entries by platform_key
                                        const grouped = [];
                                        const seen = {};
                                        (movie.availability || []).forEach(a => {
                                            const key = a.platform_key;
                                            if (seen[key] !== undefined) {
                                                grouped[seen[key]].push(a);
                                            } else {
                                                seen[key] = grouped.length;
                                                grouped.push([a]);
                                            }
                                        });
                                        return grouped
                                            .map((group, i) => Components.platformRow(group, i === 0))
                                            .join('');
                                    })()}
                                </div>
                                <div class="watch-widget__disclaimer">
                                    Prices in USD. Subscription required where indicated. CineAll links you out — we don't host any video.
                                </div>
                            </div>
                        </aside>
                    </div>

                    ${
                      movie.related && movie.related.length > 0
                        ? `
                        <section style="margin-top: 80px;">
                            ${Components.sectionHeader("If this resonates", `More from ${movie.genres[0]}`)}
                            <div class="movie-row">
                                ${movie.related
                                  .map((m) =>
                                    Components.movieCard(m, { width: 200 }),
                                  )
                                  .join("")}
                            </div>
                        </section>
                    `
                        : ""
                    }
                </div>
            `;

      container.innerHTML = html;

      const watchlistBtn = document.getElementById("watchlist-btn");
      if (watchlistBtn) {
        watchlistBtn.addEventListener("click", async () => {
          try {
            const newState = await App.toggleWatchlist(movieId);
            watchlistBtn.textContent = newState
              ? "✓ In watchlist"
              : "+ Watchlist";
          } catch (error) {
            console.error("Error toggling watchlist:", error);
          }
        });
      }

      this.attachMovieCardHandlers();
    } catch (error) {
      console.error("Error loading movie detail:", error);
      container.innerHTML = Components.emptyState(
        "Error loading movie details",
      );
    }
  },

  async genres(container) {
    try {
      const genresResult = await API.genres.list();
      const genres = genresResult.genres || [];

      // Fetch movies for "All" or a specific genre, newest release date first.
      const fetchMovies = async (genre) => {
        const filters = { sort: "year", limit: 100 };
        if (genre && genre !== "__all__") filters.genres = genre;
        const res = await API.movies.list(filters);
        return res.movies || [];
      };

      // Default view = everything, ordered by release date.
      const allMovies = await fetchMovies("__all__");

      const html = `
                <div style="padding: 48px;">
                    <div style="font-family: var(--font-mono); font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: var(--accent); margin-bottom: 12px;">
                        Browse the library by
                    </div>
                    <div style="font-family: var(--font-serif); font-size: 56px; font-weight: 400; letter-spacing: -1px; color: var(--fg); margin-bottom: 8px;">
                        Genre <span style="font-style: italic; color: var(--accent);">&</span> mood
                    </div>
                    <div style="font-family: var(--font-serif); font-size: 15px; color: var(--muted); font-style: italic; margin-bottom: 32px;">
                        Newest releases first.
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 48px;" id="genre-buttons">
                        <button class="btn btn-primary" data-genre="__all__">
                            All <span style="font-family: var(--font-mono); font-size: 10px; margin-left: 6px; opacity: 0.6;">${allMovies.length}</span>
                        </button>
                        ${genres
                          .map(
                            (g) => `
                            <button class="btn btn-secondary"
                                    data-genre="${g.name}">
                                ${g.name} <span style="font-family: var(--font-mono); font-size: 10px; margin-left: 6px; opacity: 0.6;">${g.movie_count}</span>
                            </button>
                        `,
                          )
                          .join("")}
                    </div>
                    <div class="movie-grid" id="genre-movies">
                        ${allMovies.map((m) => Components.movieCard(m, { width: 200 })).join("")}
                    </div>
                </div>
            `;

      container.innerHTML = html;
      this.attachMovieCardHandlers();

      // Wire every button (All + each genre): clicking loads that set, date-sorted.
      const grid = container.querySelector("#genre-movies");
      const buttons = container.querySelectorAll("#genre-buttons [data-genre]");
      buttons.forEach((btn) => {
        btn.addEventListener("click", async () => {
          buttons.forEach((b) => {
            b.classList.remove("btn-primary");
            b.classList.add("btn-secondary");
          });
          btn.classList.add("btn-primary");
          btn.classList.remove("btn-secondary");

          grid.innerHTML = Components.loading();
          try {
            const list = await fetchMovies(btn.dataset.genre);
            grid.innerHTML = list.length
              ? list.map((m) => Components.movieCard(m, { width: 200 })).join("")
              : Components.emptyState("No films in this genre yet.");
            this.attachMovieCardHandlers();
          } catch (e) {
            console.error("Error loading genre:", e);
            grid.innerHTML = Components.emptyState("Error loading genre");
          }
        });
      });
    } catch (error) {
      console.error("Error loading genres page:", error);
      container.innerHTML = Components.emptyState("Error loading genres");
    }
  },

  async watchlist(container) {
    if (!App.isLoggedIn()) {
      const url = (window.CINEALL && window.CINEALL.authUrl) ? window.CINEALL.authUrl + '/index.php' : 'auth/index.php';
      container.innerHTML = `
        <div style="padding: 64px 48px; max-width: 600px;">
          <div style="font-family: var(--font-mono); font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: var(--accent); margin-bottom: 12px;">Saved by you</div>
          <div style="font-family: var(--font-serif); font-size: 48px; color: var(--fg); margin-bottom: 16px;">Your watchlist</div>
          <div style="font-family: var(--font-serif); font-size: 17px; color: var(--muted); font-style: italic; margin-bottom: 28px;">
            Sign in (or create an account) to save films to your watchlist.
          </div>
          <a class="btn btn-primary" href="${url}" style="text-decoration:none;">Sign in</a>
        </div>`;
      return;
    }
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

                ${
                  watchlist.length === 0
                    ? Components.emptyState(
                        'Nothing saved yet. Browse the library and tap "+ Watchlist" on any film.',
                      )
                    : `<div style="display: flex; flex-direction: column; gap: 16px;">
                        ${watchlist
                          .map(
                            (m) => `
                            <div style="display: grid; grid-template-columns: 80px 1fr auto auto auto; gap: 24px; align-items: center; padding: 16px; border: 1px solid var(--border-color); border-radius: 4px;">
                                <div style="cursor: pointer;" onclick="App.navigateTo('detail', {id: '${m.id}'})">
                                    ${Components.poster(m, { width: 80, height: 116, showMeta: false })}
                                </div>
                                <div style="cursor: pointer;" onclick="App.navigateTo('detail', {id: '${m.id}'})">
                                    <div style="font-family: var(--font-serif); font-size: 22px; color: var(--fg);">${m.title}</div>
                                    <div style="font-family: var(--font-mono); font-size: 10px; letter-spacing: 1.4px; text-transform: uppercase; color: var(--muted); margin-top: 6px;">
                                        ${m.director} · ${m.year} · ${m.runtime}m · ${Array.isArray(m.genres) ? m.genres.join(" / ") : m.genres}
                                    </div>
                                </div>
                                ${Components.scoreSplit(m.critic_score, m.audience_score, "sm")}
                                <div style="display: flex; gap: 4px;">
                                    ${m.availability.map((a) => Components.platformChip(a, 22)).join("")}
                                </div>
                                <button class="btn btn-secondary btn-small" data-remove="${m.id}">Remove</button>
                            </div>
                        `,
                          )
                          .join("")}
                    </div>`
                }
            </div>
        `;

    container.innerHTML = html;

    container.querySelectorAll("[data-remove]").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const movieId = btn.dataset.remove;
        await App.toggleWatchlist(movieId);
        await this.watchlist(container);
      });
    });
  },

  async account(container) {
    if (!App.isLoggedIn()) {
      const url = (window.CINEALL && window.CINEALL.authUrl) ? window.CINEALL.authUrl + '/index.php' : 'auth/index.php';
      container.innerHTML = `
        <div style="padding: 64px 48px; max-width: 600px;">
          <div style="font-family: var(--font-mono); font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: var(--accent); margin-bottom: 12px;">Account</div>
          <div style="font-family: var(--font-serif); font-size: 48px; color: var(--fg); margin-bottom: 16px;">Sign in to CineAll</div>
          <div style="font-family: var(--font-serif); font-size: 17px; color: var(--muted); font-style: italic; margin-bottom: 28px;">
            Sign in or create an account to manage your services, watchlist, and preferences.
          </div>
          <a class="btn btn-primary" href="${url}" style="text-decoration:none;">Sign in</a>
        </div>`;
      return;
    }
    const userName = (window.CINEALL && window.CINEALL.user) ? window.CINEALL.user.name : 'You';
    const subscriptionsResult = await API.user.getSubscriptions();
    const subscriptions = subscriptionsResult.subscriptions || [];

    const html = `
            <div style="padding: 48px; max-width: 920px; margin: 0 auto;">
                <div style="font-family: var(--font-mono); font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: var(--accent); margin-bottom: 12px;">
                    Account · ${userName}
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
                        ${subscriptions
                          .map(
                            (p) => `
                            <button class="btn ${p.subscribed ? "btn-primary" : "btn-secondary"}"
                                    style="display: grid; grid-template-columns: 32px 1fr 16px; gap: 12px; align-items: center; padding: 14px; text-align: left;"
                                    data-toggle-subscription="${p.id}">
                                ${Components.platformChip(p, 32)}
                                <div>
                                    <div style="font-family: var(--font-serif); font-size: 14px;">${p.name}</div>
                                    <div style="font-family: var(--font-mono); font-size: 9px; letter-spacing: 1.4px; text-transform: uppercase; color: var(--muted); margin-top: 2px;">
                                        ${p.subscribed ? "Subscribed" : "Not linked"}
                                    </div>
                                </div>
                                <div style="width: 14px; height: 14px; border-radius: 50%; background: ${p.subscribed ? "var(--accent)" : "transparent"}; border: 1px solid ${p.subscribed ? "var(--accent)" : "var(--border-color)"};"></div>
                            </button>
                        `,
                          )
                          .join("")}
                    </div>
                </section>
            </div>
        `;

    container.innerHTML = html;

    container.querySelectorAll("[data-toggle-subscription]").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const platformId = btn.dataset.toggleSubscription;
        await API.user.toggleSubscription(platformId);
        await this.account(container);
        await App.loadUserData();
      });
    });
  },

  async compare(container) {
    try {
      const result = await API.platforms.stats();
      const platforms = result.platforms || [];

      const maxCount = Math.max(...platforms.map((p) => p.total_titles));

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
                        <div style="display: grid; grid-template-columns: 180px 1fr 80px 80px 80px 200px; gap: 24px; padding: 0 0 12px; border-bottom: 1px solid var(--border-color-hover); font-family: var(--font-mono); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: var(--muted);">
                            <div>Service</div>
                            <div>Coverage</div>
                            <div>Sub.</div>
                            <div>Rent</div>
                            <div>Buy</div>
                            <div>Sample</div>
                        </div>

                        ${platforms
                          .map((p) => {
                            const percentage =
                              (p.total_titles / maxCount) * 100;
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
                                        ${p.sample_movies
                                          .map(
                                            (m) => `
                                            <div style="cursor: pointer;" onclick="App.navigateTo('detail', {id: '${m.id}'})">
                                                ${Components.poster(m, { width: 32, height: 46, showMeta: false })}
                                            </div>
                                        `,
                                          )
                                          .join("")}
                                    </div>
                                </div>
                            `;
                          })
                          .join("")}
                    </div>
                </div>
            `;

      container.innerHTML = html;
    } catch (error) {
      console.error("Error loading compare page:", error);
      container.innerHTML = Components.emptyState("Error loading comparison");
    }
  },



  attachMovieCardHandlers() {
    document.querySelectorAll(".movie-card").forEach((card) => {
      card.addEventListener("click", () => {
        const movieId = card.dataset.movieId;
        if (movieId) {
          App.navigateTo("detail", { id: movieId });
        }
      });
    });
  },
};
