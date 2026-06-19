/**
 * UI Components Module
 *
 * Reusable component functions for rendering UI elements
 */

const Components = {
    /**
     * Render a movie poster
     */
    poster(movie, options = {}) {
        const { width = 220, height = 320, compact = false, showMeta = true } = options;
        const [color1, color2] = [movie.scheme_color_1, movie.scheme_color_2];
        const accent = movie.accent_color;
        const fontSize = width < 140 ? 11 : width < 200 ? 14 : 18;
        const metaFontSize = width < 140 ? 8 : 10;

        // ✅ TAMBAHAN: gunakan gambar jika tersedia
        const bgStyle = movie.poster_image
            ? `background-image: url('/${movie.poster_image}'); background-size: cover; background-position: center;`
            : `background: linear-gradient(165deg, ${color1} 0%, ${color2} 100%);`;

        return `
            <div class="poster ${compact ? 'poster--compact' : ''}"
                style="width: ${width}px; height: ${height}px;">
                <div class="poster__gradient" style="${bgStyle}"></div>
                <div class="poster__noise"></div>
                <div class="poster__vignette"></div>
                <div class="poster__accent-line" style="background: ${accent};"></div>
                ${showMeta ? `
                    <div class="poster__title" style="font-size: ${fontSize}px;">
                        ${movie.title}
                    </div>
                    <div class="poster__meta" style="font-size: ${metaFontSize}px;">
                        <span>${movie.director.split(' ').pop()}</span>
                        <span>${movie.year}</span>
                    </div>
                ` : ''}
            </div>
        `;
    },

    /**
     * Render a platform chip
     */
    platformChip(platform, size = 22) {
        return `
            <div class="platform-chip"
                 title="${platform.platform_name || platform.name}"
                 style="width: ${size}px;
                        height: ${size}px;
                        background: oklch(0.45 0.10 ${platform.hue});
                        color: oklch(0.95 0.04 ${platform.hue});
                        font-size: ${size * 0.42}px;">
                ${platform.abbr}
            </div>
        `;
    },

    /**
     * Render score split (critic/audience)
     */
    scoreSplit(criticScore, audienceScore, size = 'md') {
        const sizes = {
            sm: { num: 14, lab: 8, gap: 12 },
            md: { num: 20, lab: 9, gap: 16 },
            lg: { num: 28, lab: 9, gap: 24 }
        };

        const s = sizes[size];

        const renderScore = (value, label, glyph) => {
            const borderColor = value >= 80 ? 'var(--accent)' :
                              value >= 60 ? 'rgba(244,239,230,0.4)' :
                              'rgba(220,120,90,0.6)';

            const textColor = value >= 80 ? 'var(--accent)' : 'var(--fg)';

            return `
                <div class="score-item">
                    <div class="score-item__badge"
                         style="width: ${s.num + 8}px;
                                height: ${s.num + 8}px;
                                border: 1.5px solid ${borderColor};
                                font-size: ${s.num * 0.46}px;
                                color: ${textColor};">
                        ${value}
                    </div>
                    <div>
                        <div class="score-item__label" style="font-size: ${s.lab}px;">
                            ${label}
                        </div>
                        <div class="score-item__glyph" style="font-size: ${s.lab + 4}px;">
                            ${glyph}
                        </div>
                    </div>
                </div>
            `;
        };

        return `
            <div class="score-split" style="gap: ${s.gap}px;">
                ${renderScore(criticScore, 'Critics', 'reviewed')}
                ${renderScore(audienceScore, 'Audience', 'rated')}
            </div>
        `;
    },

    /**
     * Render a movie card
     */
    movieCard(movie, options = {}) {
        const { width = 200, showPlatforms = true, onClick } = options;
        const height = Math.round(width * 1.45);

        return `
            <div class="movie-card" style="width: ${width}px;" data-movie-id="${movie.id}">
                ${this.poster(movie, { width, height })}
                <div class="movie-card__info">
                    <div class="movie-card__title">${movie.title}</div>
                    <div class="movie-card__meta">
                        ${movie.year} · ${Array.isArray(movie.genres) ? movie.genres[0] : movie.genres.split(',')[0]} · ${movie.runtime}m
                    </div>
                    ${showPlatforms && movie.availability ? `
                        <div class="movie-card__platforms">
                            ${movie.availability.slice(0, 4).map(a =>
                                this.platformChip(a, 18)
                            ).join('')}
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    },

    /**
     * Render section header
     */
    sectionHeader(kicker, title, actionText = null) {
        return `
            <div class="section-header">
                <div>
                    <div class="section-header__kicker">${kicker}</div>
                    <div class="section-header__title">${title}</div>
                </div>
                ${actionText ? `
                    <button class="section-header__action">${actionText} →</button>
                ` : ''}
            </div>
        `;
    },

    /**
     * Render platform row (for watch widget)
     */
    platformRow(availability, isPrimary = false) {
        const kindLabel = availability.kind === 'subscription' ? 'Included' :
                         availability.kind === 'rent' ? 'Rent' : 'Buy';

        const priceLabel = availability.kind === 'subscription' ? 'with subscription' :
                          `from $${availability.price_from}`;

        const badgeBg = availability.kind === 'subscription' ?
                       'rgba(160,200,140,0.10)' : 'rgba(244,239,230,0.05)';

        const badgeColor = availability.kind === 'subscription' ?
                          'oklch(0.85 0.10 140)' : 'var(--muted)';

        return `
            <button class="platform-row ${isPrimary ? 'primary' : ''}"
                    data-platform="${availability.platform_key}">
                ${this.platformChip(availability, 36)}
                <div>
                    <div class="platform-row__name">${availability.platform_name}</div>
                    <div class="platform-row__meta">${kindLabel} · ${priceLabel}</div>
                </div>
                <div class="platform-row__badge"
                     style="background: ${badgeBg}; color: ${badgeColor};">
                    ${availability.kind}
                </div>
                <div class="platform-row__action">Watch →</div>
            </button>
        `;
    },

    /**
     * Render loading spinner
     */
    loading() {
        return '<div class="loading"></div>';
    },

    /**
     * Render empty state
     */
    emptyState(message) {
        return `
            <div class="empty-state">
                ${message}
            </div>
        `;
    }
};
