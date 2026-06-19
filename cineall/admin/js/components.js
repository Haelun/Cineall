/**
 * CineAll Admin - UI Components JavaScript
 *
 * JavaScript for rendering specific UI components like posters, sparklines, etc.
 */

// ==================== POSTER COMPONENT ====================
const Poster = {
    render: function(movie, options = {}) {
        const {
            width = 40,
            height = 58,
            showMeta = false,
            size = 'sm'
        } = options;

        const colorA = movie.scheme_color_a || 'oklch(0.32 0.06 30)';
        const colorB = movie.scheme_color_b || 'oklch(0.18 0.04 50)';
        const accent = movie.accent_color || 'oklch(0.78 0.14 70)';

        return `
            <div class="poster poster-${size}" style="width: ${width}px; height: ${height}px;">
                <div class="poster-gradient" style="background: linear-gradient(165deg, ${colorA} 0%, ${colorB} 100%);"></div>
                <div class="poster-noise"></div>
                <div class="poster-vignette"></div>
                <div class="poster-rule" style="background: ${accent};"></div>
                ${showMeta ? `
                    <div class="poster-title">${movie.title}</div>
                    <div class="poster-meta">
                        <span>${movie.director.split(' ').pop()}</span>
                        <span>${movie.year}</span>
                    </div>
                ` : ''}
            </div>
        `;
    }
};

// ==================== PLATFORM CHIP COMPONENT ====================
const PlatformChip = {
    colors: {
        'streamline': { hue: 8, abbr: 'SL' },
        'vista': { hue: 220, abbr: 'V+' },
        'orbit': { hue: 280, abbr: 'OR' },
        'monogram': { hue: 150, abbr: 'MG' },
        'lumen': { hue: 50, abbr: 'LM' },
        'bluechannel': { hue: 200, abbr: 'BC' },
        'attic': { hue: 25, abbr: 'AT' },
        'cinedeck': { hue: 340, abbr: 'CD' }
    },

    render: function(platformId, size = 'md') {
        const platform = this.colors[platformId];
        if (!platform) return '';

        const sizeClass = `platform-chip-${size}`;
        const hue = platform.hue;
        const abbr = platform.abbr;

        return `
            <div class="platform-chip ${sizeClass}"
                 style="background: oklch(0.45 0.10 ${hue}); color: oklch(0.95 0.04 ${hue});"
                 title="${platformId}">
                ${abbr}
            </div>
        `;
    }
};

// ==================== SPARKLINE COMPONENT ====================
const Sparkline = {
    render: function(data, options = {}) {
        const {
            width = 100,
            height = 56,
            color = 'var(--accent)'
        } = options;

        const max = Math.max(...data);
        const min = Math.min(...data);
        const range = max - min || 1;

        const points = data.map((value, index) => {
            const x = (index / (data.length - 1)) * width;
            const y = height - ((value - min) / range) * height * 0.85 - height * 0.075;
            return `${x.toFixed(2)},${y.toFixed(2)}`;
        });

        const linePath = `M ${points.join(' L ')}`;
        const areaPath = `${linePath} L ${width},${height} L 0,${height} Z`;

        return `
            <svg viewBox="0 0 ${width} ${height}" preserveAspectRatio="none" class="sparkline">
                <path d="${areaPath}" fill="${color}" opacity="0.10"/>
                <path d="${linePath}" fill="none" stroke="${color}" stroke-width="1.2"/>
            </svg>
        `;
    }
};

// ==================== BADGE COMPONENT ====================
const Badge = {
    render: function(text, type = 'neutral') {
        return `<span class="badge badge-${type}">${text}</span>`;
    }
};

// ==================== DATA TABLE ====================
const DataTable = {
    render: function(config) {
        const { columns, data, gridTemplate } = config;

        let html = `
            <div class="data-table">
                <div class="table-header" style="grid-template-columns: ${gridTemplate};">
        `;

        // Render headers
        columns.forEach(col => {
            html += `<div>${col.header || ''}</div>`;
        });

        html += `</div>`;

        // Render rows
        data.forEach(row => {
            html += `<div class="table-row" style="grid-template-columns: ${gridTemplate};">`;

            columns.forEach(col => {
                const value = col.render ? col.render(row) : row[col.field];
                html += `<div>${value}</div>`;
            });

            html += `</div>`;
        });

        html += `</div>`;

        return html;
    }
};

// ==================== PROGRESS BAR ====================
const ProgressBar = {
    render: function(percentage) {
        return `
            <div class="progress-bar">
                <div class="progress-fill" style="width: ${percentage}%;"></div>
            </div>
        `;
    }
};

// ==================== FORM BUILDER ====================
const FormBuilder = {
    textField: function(label, name, value = '', options = {}) {
        return `
            <div class="form-group">
                <label class="form-label">${label}</label>
                <input type="text"
                       name="${name}"
                       value="${value}"
                       class="form-control"
                       ${options.placeholder ? `placeholder="${options.placeholder}"` : ''}
                       ${options.required ? 'required' : ''}>
            </div>
        `;
    },

    textArea: function(label, name, value = '', options = {}) {
        return `
            <div class="form-group">
                <label class="form-label">${label}</label>
                <textarea name="${name}"
                          class="form-control"
                          rows="${options.rows || 4}"
                          ${options.required ? 'required' : ''}>${value}</textarea>
            </div>
        `;
    },

    select: function(label, name, options, selected = '') {
        let html = `
            <div class="form-group">
                <label class="form-label">${label}</label>
                <select name="${name}" class="form-control">
        `;

        options.forEach(opt => {
            const value = typeof opt === 'object' ? opt.value : opt;
            const text = typeof opt === 'object' ? opt.text : opt;
            const isSelected = value === selected ? 'selected' : '';
            html += `<option value="${value}" ${isSelected}>${text}</option>`;
        });

        html += `
                </select>
            </div>
        `;

        return html;
    }
};

// ==================== SEARCH WITH DEBOUNCE ====================
const SearchBox = {
    init: function(inputId, callback, delay = 300) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const debouncedCallback = Utils.debounce(callback, delay);

        input.addEventListener('input', function(e) {
            debouncedCallback(e.target.value);
        });
    }
};

// ==================== PAGINATION ====================
const Pagination = {
    render: function(currentPage, totalPages, onChange) {
        let html = '<div class="pagination flex gap-8 items-center">';

        // Previous button
        if (currentPage > 1) {
            html += `<button class="btn btn-sm btn-ghost" onclick="${onChange}(${currentPage - 1})">← Prev</button>`;
        }

        // Page numbers
        html += `<span class="text-mono">Page ${currentPage} of ${totalPages}</span>`;

        // Next button
        if (currentPage < totalPages) {
            html += `<button class="btn btn-sm btn-ghost" onclick="${onChange}(${currentPage + 1})">Next →</button>`;
        }

        html += '</div>';
        return html;
    }
};

// ==================== LOADER ====================
const Loader = {
    show: function(targetId) {
        const target = document.getElementById(targetId);
        if (!target) return;

        target.innerHTML = '<div class="flex items-center justify-center" style="padding: 40px;"><div class="spinner"></div></div>';
    },

    hide: function(targetId) {
        const target = document.getElementById(targetId);
        if (!target) return;

        target.innerHTML = '';
    }
};

// ==================== EXPORT ====================
window.Components = {
    Poster,
    PlatformChip,
    Sparkline,
    Badge,
    DataTable,
    ProgressBar,
    FormBuilder,
    SearchBox,
    Pagination,
    Loader
};
