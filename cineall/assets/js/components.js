const Components = {
    poster(movie, options = {}) {
        const {
            width = 220,
            height = 320,
            compact = false,
            showMeta = true
        } = options;

        const [color1, color2] = [movie.scheme_color_1, movie.scheme_color_2];
        const accent = movie.accent_color;
        const fontSize = width < 140 ? 11 : width < 200 ? 14 : 18;
        const metaFontSize = width < 140 ? 8 : 10;
        const hasPoster = movie.poster_url && movie.poster_url.trim() !== '';

        return `
            <div class="poster ${compact ? 'poster--compact' : ''}"
                 style="width: ${width}px; height: ${height}px; position: relative; overflow: hidden;">
                ${hasPoster ? `
                    <img
                        src="${movie.poster_url}"
                        alt="${movie.title} poster"
                        loading="lazy"
                        style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block; border-radius: inherit;"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                    />
                    <div style="display:none; position:absolute; inset:0;">
                        <div class="poster__gradient"
                             style="background: linear-gradient(165deg, ${color1} 0%, ${color2} 100%);"></div>
                        <div class="poster__noise"></div>
                        <div class="poster__vignette"></div>
                        <div class="poster__accent-line" style="background: ${accent};"></div>
                        ${showMeta ? `
                            <div class="poster__title" style="font-size: ${fontSize}px;">${movie.title}</div>
                            <div class="poster__meta" style="font-size: ${metaFontSize}px;">
                                <span>${movie.director.split(' ').pop()}</span>
                                <span>${movie.year}</span>
                            </div>
                        ` : ''}
                    </div>
                ` : `
                    <div class="poster__gradient"
                         style="background: linear-gradient(165deg, ${color1} 0%, ${color2} 100%);"></div>
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
                `}
            </div>
        `;
    },

    platformChip(platform, size = 22) {
        const isUnavailable = platform.kind === null;
        return `
            <div class="platform-chip"
                 title="${platform.platform_name || platform.name}"
                 style="width: ${size}px;
                        height: ${size}px;
                        background: oklch(0.45 0.10 ${platform.hue});
                        color: oklch(0.95 0.04 ${platform.hue});
                        font-size: ${size * 0.42}px;
                        filter: ${isUnavailable ? 'grayscale(100%) opacity(0.4)' : 'none'};">
                ${platform.abbr}
            </div>
        `;
    },

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

    sectionHeader(kicker, title, actionText = null, actionOnClick = null) {
        return `
            <div class="section-header">
                <div>
                    <div class="section-header__kicker">${kicker}</div>
                    <div class="section-header__title">${title}</div>
                </div>
                ${actionText ? `
                    <button class="section-header__action" onclick="${actionOnClick || ''}">${actionText} →</button>
                ` : ''}
            </div>
        `;
    },

    /**
     * platformRow: renders one row per platform.
     * Clicking the entire row opens the streaming service in a new tab.
     * Layout (matches reference image): [chip] [name + subtitle] [badge]
     * @param {object|object[]} availability - a single availability object OR
     *   an array of grouped availability objects for the same platform.
     * @param {boolean} isPrimary - highlight the first row (unused visually now).
     */
    platformRow(availability, isPrimary = false) {
        const entries = Array.isArray(availability) ? availability : [availability];
        const base = entries[0];

        const isAvailable = entries.some(e => e.kind !== null && e.kind !== undefined);
        const availableEntries = entries.filter(e => e.kind !== null && e.kind !== undefined);

        // Subtitle logic matching mockup:
        let subtitle;
        if (!isAvailable) {
            subtitle = 'Buy · from $null';
        } else {
            const hasSub = availableEntries.some(e => e.kind === 'subscription');
            if (hasSub) {
                subtitle = 'Included - with subscription';
            } else {
                const kindStr = availableEntries.map(e => e.kind === 'rent' ? 'Rent' : 'Buy').join(' · ');
                const priceStr = availableEntries.map(e => `from $${e.price_from}`).join(' · ');
                subtitle = `${kindStr} · ${priceStr}`;
            }
        }

        // Badge logic matching mockup:
        let badgesHtml;
        if (!isAvailable) {
            badgesHtml = `<span class="platform-row__badge" style="background:rgba(244,239,230,0.04); color:var(--muted);">NULL</span>`;
        } else {
            badgesHtml = availableEntries.map(e => {
                const isSubscription = e.kind === 'subscription';
                const badgeBg  = isSubscription ? 'rgba(120,180,100,0.12)' : 'rgba(244,239,230,0.07)';
                const badgeClr = isSubscription ? 'oklch(0.80 0.12 140)'   : 'var(--muted)';
                const label    = e.kind ? e.kind.toUpperCase() : 'NULL';
                return `<span class="platform-row__badge" style="background:${badgeBg}; color:${badgeClr};">${label}</span>`;
            }).join('');
        }

        const watchEntry = availableEntries.find(e => e.kind === 'subscription')
                        || availableEntries.find(e => e.kind === 'rent')
                        || availableEntries[0];
        const watchUrl = isAvailable && watchEntry?.url?.trim() ? watchEntry.url : null;
        const tag = watchUrl ? 'a' : 'div';
        const linkAttrs = watchUrl
            ? `href="${watchUrl}" target="_blank" rel="noopener noreferrer"`
            : '';

        return `
            <${tag} class="platform-row"
                    ${linkAttrs}
                    data-platform="${base.platform_key}"
                    style="${!isAvailable ? 'opacity:0.45; pointer-events:none;' : ''}">
                ${this.platformChip(base, 44)}
                <div class="platform-row__body">
                    <div class="platform-row__name">${base.platform_name || base.name}</div>
                    <div class="platform-row__meta">${subtitle}</div>
                </div>
                <div class="platform-row__badges">
                    ${badgesHtml}
                </div>
            </${tag}>
        `;
    },

    loading() {
        return '<div class="loading"></div>';
    },

    emptyState(message) {
        return `
            <div class="empty-state">
                ${message}
            </div>
        `;
    }
};
