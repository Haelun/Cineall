/**
 * ============================================================================
 * CineAll Curator - Main JavaScript
 * ============================================================================
 * EASY TO EDIT: All functions are clearly labeled
 * Find what you need by searching for section headers
 */

// ============================================================================
// CONFIGURATION
// ============================================================================
const API_BASE = window.CURATOR_API || 'php/api';

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Fetch data from API
 */
async function apiCall(endpoint, method = 'GET', data = null) {
    const url = `${API_BASE}/${endpoint}`;
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json'
        }
    };

    if (data && method === 'POST') {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, options);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, error: error.message };
    }
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    // Simple alert for now - you can enhance this with a custom toast component
    if (type === 'error') {
        alert('Error: ' + message);
    } else if (type === 'success') {
        console.log('Success: ' + message);
    } else {
        console.log(message);
    }
}

/**
 * Create element helper
 */
function el(tag, className = '', content = '') {
    const element = document.createElement(tag);
    if (className) element.className = className;
    if (content) element.textContent = content;
    return element;
}

// ============================================================================
// POSTER COMPONENT
// ============================================================================

/**
 * Create a movie poster element
 * @param {Object} movie - Movie object with scheme colors
 * @param {number} width - Poster width in pixels
 * @param {number} height - Poster height in pixels
 * @param {Object} options - Additional options
 */
function createPoster(movie, width, height, options = {}) {
    const { showMeta = true, muted = false } = options;
    const [colorA, colorB] = [movie.scheme_color_a, movie.scheme_color_b];
    const accent = movie.accent_color;

    const poster = el('div', 'poster' + (muted ? ' muted' : ''));
    poster.style.width = width + 'px';
    poster.style.height = height + 'px';
    poster.style.background = `linear-gradient(165deg, ${colorA} 0%, ${colorB} 100%)`;

    const noise = el('div', 'poster-noise');
    poster.appendChild(noise);

    const vignette = el('div', 'poster-vignette');
    poster.appendChild(vignette);

    const rule = el('div', 'poster-rule');
    rule.style.background = accent;
    poster.appendChild(rule);

    if (showMeta) {
        const title = el('div', 'poster-title', movie.title);
        title.style.fontSize = width < 140 ? '11px' : width < 200 ? '14px' : '18px';
        poster.appendChild(title);

        const meta = el('div', 'poster-meta');
        meta.style.fontSize = width < 140 ? '8px' : '10px';
        const directorLastName = movie.director.split(' ').slice(-1)[0];
        meta.innerHTML = `<span>${directorLastName}</span><span>${movie.year}</span>`;
        poster.appendChild(meta);
    }

    return poster;
}

// ============================================================================
// PLATFORM CHIP COMPONENT
// ============================================================================

/**
 * Create a platform chip
 */
function createPlatformChip(platform, size = 22) {
    const chip = el('div', 'platform-chip');
    chip.title = platform.name || platform.platform_name;
    chip.style.width = size + 'px';
    chip.style.height = size + 'px';
    chip.style.background = `oklch(0.45 0.10 ${platform.hue})`;
    chip.style.color = `oklch(0.95 0.04 ${platform.hue})`;
    chip.style.fontSize = (size * 0.42) + 'px';
    chip.textContent = platform.abbr;

    return chip;
}

// ============================================================================
// DATA LOADING
// ============================================================================

let MOVIES_CACHE = null;

/**
 * Load all movies
 */
async function loadMovies() {
    if (MOVIES_CACHE) return MOVIES_CACHE;

    const result = await apiCall('movies.php?action=list');
    if (result.success) {
        MOVIES_CACHE = result.data;
        return MOVIES_CACHE;
    }
    return [];
}

/**
 * Get movie by ID
 */
function getMovieById(movieId, movies = null) {
    if (!movies) movies = MOVIES_CACHE || [];
    return movies.find(m => m.movie_id === movieId);
}

// ============================================================================
// SEARCH FUNCTIONALITY
// ============================================================================

/**
 * Filter movies by search term
 */
function filterMovies(movies, searchTerm) {
    if (!searchTerm) return movies;

    const term = searchTerm.toLowerCase();
    return movies.filter(movie =>
        movie.title.toLowerCase().includes(term) ||
        movie.director.toLowerCase().includes(term) ||
        (movie.genres && movie.genres.some(g => g.toLowerCase().includes(term)))
    );
}

// ============================================================================
// DEBOUNCE HELPER
// ============================================================================

/**
 * Debounce function calls
 */
function debounce(func, wait) {
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
// DRAG AND DROP HELPERS
// ============================================================================

/**
 * Make element draggable
 */
function makeDraggable(element, onDragStart, onDragEnd) {
    element.draggable = true;

    element.addEventListener('dragstart', (e) => {
        element.classList.add('dragging');
        if (onDragStart) onDragStart(e);
    });

    element.addEventListener('dragend', (e) => {
        element.classList.remove('dragging');
        if (onDragEnd) onDragEnd(e);
    });
}

/**
 * Make element a drop target
 */
function makeDropTarget(element, onDrop) {
    element.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    });

    element.addEventListener('drop', (e) => {
        e.preventDefault();
        if (onDrop) onDrop(e);
    });
}

// ============================================================================
// EXPORT
// ============================================================================

// Make functions available globally
window.CineAllApp = {
    apiCall,
    showToast,
    el,
    createPoster,
    createPlatformChip,
    loadMovies,
    getMovieById,
    filterMovies,
    debounce,
    makeDraggable,
    makeDropTarget
};
