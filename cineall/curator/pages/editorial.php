<?php
/**
 * ============================================================================
 * EDITORIAL - Homepage Composer
 * ============================================================================
 * Drag-and-drop interface for arranging the discover homepage
 */

require_once __DIR__ . '/../php/bootstrap.php';

$current_page = 'editorial';

include '../includes/header.php';
?>

<div id="app">
  <?php include '../includes/sidebar.php'; ?>

  <main class="main-content">
    <?php include '../includes/top-header.php'; ?>

    <!-- Curator Workspace -->
    <div id="curator-workspace" class="curator-workspace">
      <!-- Loading state -->
      <div id="loading-state" style="display: flex; align-items: center; justify-content: center; height: 400px;">
        <div class="spinner spinner-lg"></div>
      </div>

      <!-- Main content will be rendered by JavaScript -->
      <div id="curator-content" style="display: none;"></div>
    </div>

  </main>
</div>

<!-- Curator-specific JavaScript -->
<script>
/**
 * ============================================================================
 * CURATOR PAGE - Homepage Composer
 * ============================================================================
 * EASY TO EDIT: All functions are clearly labeled
 */

// Global state
let STATE = {
    movies: [],
    hero: null,
    heroTagline: '',
    rows: [],
    activeRowId: null,
    preview: false,
    dirty: false,
    librarySearch: ''
};

// ============================================================================
// INITIALIZATION
// ============================================================================

document.addEventListener('DOMContentLoaded', async () => {
    await loadData();
    render();
});

/**
 * Load all data from API
 */
async function loadData() {
    try {
        // Load movies
        const moviesResult = await CineAllApp.apiCall('movies.php?action=list');
        if (moviesResult.success) {
            STATE.movies = moviesResult.data;
        }

        // Load homepage settings
        const settingsResult = await CineAllApp.apiCall('homepage.php?action=get_settings');
        if (settingsResult.success) {
            STATE.hero = settingsResult.data.hero_movie_id;
            STATE.heroTagline = settingsResult.data.hero_tagline || '';
        }

        // Load homepage rows
        const rowsResult = await CineAllApp.apiCall('homepage.php?action=get_rows');
        if (rowsResult.success) {
            STATE.rows = rowsResult.data.map(row => ({
                row_id: row.row_id,
                title: row.title,
                kicker: row.kicker,
                enabled: Boolean(row.enabled),
                movie_ids: row.movie_ids || []
            }));

            if (STATE.rows.length > 0 && !STATE.activeRowId) {
                STATE.activeRowId = STATE.rows[0].row_id;
            }
        }

    } catch (error) {
        console.error('Failed to load data:', error);
    }
}

/**
 * Update state and re-render
 */
function updateState(updates) {
    Object.assign(STATE, updates);
    render();
}

/**
 * Mark as dirty (unsaved changes)
 */
function markDirty() {
    STATE.dirty = true;
    render();
}

// ============================================================================
// RENDERING
// ============================================================================

/**
 * Main render function
 */
function render() {
    const workspace = document.getElementById('curator-workspace');
    const loading = document.getElementById('loading-state');
    const content = document.getElementById('curator-content');

    // Hide loading
    loading.style.display = 'none';
    content.style.display = 'flex';
    content.style.flexDirection = 'column';
    content.style.flex = '1';

    // Clear content
    content.innerHTML = '';

    // Render header
    content.appendChild(renderHeader());

    // Render main content
    if (STATE.preview) {
        content.appendChild(renderPreview());
    } else {
        content.appendChild(renderComposer());
    }
}

/**
 * Render curator header
 */
function renderHeader() {
    const header = document.createElement('div');
    header.className = 'curator-header';

    const enabledCount = STATE.rows.filter(r => r.enabled).length;

    header.innerHTML = `
        <span class="curator-header-label">
            Arranging Discover · ${enabledCount} live rows · raw film data untouched
        </span>
        <div style="flex: 1"></div>
        ${STATE.dirty ? '<span class="curator-header-label" style="color: var(--accent)">● Unpublished</span>' : ''}
    `;

    // Preview button
    const previewBtn = document.createElement('button');
    previewBtn.className = 'curator-header-btn' + (STATE.preview ? ' active' : '');
    previewBtn.textContent = STATE.preview ? '✦ Editing view' : '▶ Preview as user';
    previewBtn.onclick = () => updateState({ preview: !STATE.preview });
    header.appendChild(previewBtn);

    // Publish button
    const publishBtn = document.createElement('button');
    publishBtn.className = 'curator-header-btn publish';
    publishBtn.textContent = 'Publish to Discover';
    publishBtn.onclick = publishChanges;
    header.appendChild(publishBtn);

    return header;
}

/**
 * Render composer mode
 */
function renderComposer() {
    const layout = document.createElement('div');
    layout.className = 'curator-layout';

    // Film library sidebar
    layout.appendChild(renderFilmLibrary());

    // Composition area
    layout.appendChild(renderCompositionArea());

    return layout;
}

/**
 * Render film library sidebar
 */
function renderFilmLibrary() {
    const sidebar = document.createElement('div');
    sidebar.className = 'film-library';

    // Header
    const header = document.createElement('div');
    header.className = 'library-header';
    header.innerHTML = `
        <div class="library-title">Film library</div>
        <div class="library-description">
            Click a film to add it to the active row. Click again to remove.
        </div>
    `;

    const searchInput = document.createElement('input');
    searchInput.className = 'library-search';
    searchInput.placeholder = 'Search the catalogue…';
    searchInput.value = STATE.librarySearch;
    searchInput.oninput = CineAllApp.debounce((e) => {
        updateState({ librarySearch: e.target.value });
    }, 300);
    header.appendChild(searchInput);

    sidebar.appendChild(header);

    // Films grid
    const gridContainer = document.createElement('div');
    gridContainer.className = 'library-grid';

    const grid = document.createElement('div');
    grid.className = 'library-films';

    const filteredMovies = CineAllApp.filterMovies(STATE.movies, STATE.librarySearch);

    filteredMovies.forEach(movie => {
        const activeRow = STATE.rows.find(r => r.row_id === STATE.activeRowId);
        const inActive = activeRow && activeRow.movie_ids.includes(movie.movie_id);
        const inAnyCount = STATE.rows.filter(r => r.movie_ids.includes(movie.movie_id)).length;

        const filmBtn = document.createElement('button');
        filmBtn.className = 'library-film';
        filmBtn.onclick = () => toggleFilmInActiveRow(movie.movie_id);

        const posterWrap = document.createElement('div');
        posterWrap.className = 'library-film-poster' + (inActive ? ' selected' : '');
        posterWrap.appendChild(CineAllApp.createPoster(movie, 120, 172, { showMeta: false }));

        if (inActive) {
            const check = document.createElement('div');
            check.className = 'film-check';
            check.textContent = '✓';
            posterWrap.appendChild(check);
        } else if (inAnyCount > 0) {
            const count = document.createElement('div');
            count.className = 'film-count';
            count.textContent = inAnyCount;
            posterWrap.appendChild(count);
        }

        filmBtn.appendChild(posterWrap);

        const title = document.createElement('div');
        title.className = 'library-film-title';
        title.textContent = movie.title;
        filmBtn.appendChild(title);

        const meta = document.createElement('div');
        meta.className = 'library-film-meta';
        const genres = Array.isArray(movie.genres) ? movie.genres[0] : movie.genres;
        meta.textContent = `${movie.year} · ${genres}`;
        filmBtn.appendChild(meta);

        grid.appendChild(filmBtn);
    });

    gridContainer.appendChild(grid);
    sidebar.appendChild(gridContainer);

    return sidebar;
}

/**
 * Render composition area
 */
function renderCompositionArea() {
    const area = document.createElement('div');
    area.className = 'composition-area';

    // Hero section
    area.appendChild(renderHeroSection());

    // Rows section
    area.appendChild(renderRowsSection());

    return area;
}

/**
 * Render hero section
 */
function renderHeroSection() {
    const section = document.createElement('div');

    const label = document.createElement('div');
    label.className = 'section-label';
    label.textContent = 'Hero banner · the spotlight at the entrance';
    section.appendChild(label);

    const heroMovie = CineAllApp.getMovieById(STATE.hero, STATE.movies);
    if (!heroMovie) return section;

    const block = document.createElement('div');
    block.className = 'hero-block';

    block.appendChild(CineAllApp.createPoster(heroMovie, 120, 172));

    const info = document.createElement('div');
    info.innerHTML = `
        <div class="hero-label">Now featured</div>
        <div class="hero-title">${heroMovie.title}</div>
    `;

    const taglineInput = document.createElement('input');
    taglineInput.className = 'hero-tagline';
    taglineInput.value = STATE.heroTagline;
    taglineInput.oninput = (e) => {
        STATE.heroTagline = e.target.value;
        markDirty();
    };
    info.appendChild(taglineInput);

    info.innerHTML += '<div class="hero-hint">↑ click to edit the hero tagline</div>';
    block.appendChild(info);

    const changeBtn = document.createElement('button');
    changeBtn.className = 'btn';
    changeBtn.textContent = 'Change hero';
    changeBtn.onclick = changeHero;
    block.appendChild(changeBtn);

    section.appendChild(block);
    return section;
}

/**
 * Render rows section
 */
function renderRowsSection() {
    const section = document.createElement('div');

    const header = document.createElement('div');
    header.className = 'rows-header';

    const label = document.createElement('div');
    label.className = 'section-label';
    label.textContent = 'Themed rows · drag ⋮⋮ to reorder · the order users scroll';
    header.appendChild(label);

    const addBtn = document.createElement('button');
    addBtn.className = 'btn btn-primary';
    addBtn.textContent = '+ New themed row';
    addBtn.onclick = addNewRow;
    header.appendChild(addBtn);

    section.appendChild(header);

    const list = document.createElement('div');
    list.className = 'rows-list';

    STATE.rows.forEach((row, index) => {
        list.appendChild(renderRowCard(row, index));
    });

    section.appendChild(list);
    return section;
}

/**
 * Render a single row card
 */
function renderRowCard(row, index) {
    const isActive = row.row_id === STATE.activeRowId;

    const card = document.createElement('div');
    card.className = 'row-card' + (isActive ? ' active' : '') + (!row.enabled ? ' disabled' : '');
    card.onclick = () => updateState({ activeRowId: row.row_id });
    card.draggable = true;

    // Drag events
    card.ondragstart = (e) => {
        e.dataTransfer.setData('text/plain', row.row_id);
        card.classList.add('dragging');
    };
    card.ondragend = () => card.classList.remove('dragging');
    card.ondragover = (e) => e.preventDefault();
    card.ondrop = (e) => {
        e.preventDefault();
        const draggedId = e.dataTransfer.getData('text/plain');
        reorderRow(draggedId, row.row_id);
    };

    const layout = document.createElement('div');
    layout.className = 'row-layout';

    // Drag column
    const dragCol = document.createElement('div');
    dragCol.className = 'row-drag';
    dragCol.innerHTML = `
        <div class="row-drag-handle">⋮⋮</div>
        <button class="row-move-btn" onclick="event.stopPropagation(); moveRow('${row.row_id}', -1)">▲</button>
        <button class="row-move-btn" onclick="event.stopPropagation(); moveRow('${row.row_id}', 1)">▼</button>
    `;
    layout.appendChild(dragCol);

    // Content column
    const content = document.createElement('div');
    content.className = 'row-content';

    const kickerInput = document.createElement('input');
    kickerInput.className = 'row-kicker-input';
    kickerInput.value = row.kicker;
    kickerInput.onclick = (e) => e.stopPropagation();
    kickerInput.oninput = (e) => patchRow(row.row_id, { kicker: e.target.value });
    content.appendChild(kickerInput);

    const titleInput = document.createElement('input');
    titleInput.className = 'row-title-input';
    titleInput.value = row.title;
    titleInput.onclick = (e) => e.stopPropagation();
    titleInput.oninput = (e) => patchRow(row.row_id, { title: e.target.value });
    content.appendChild(titleInput);

    const filmsContainer = document.createElement('div');
    filmsContainer.className = 'row-films';

    if (row.movie_ids.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'row-empty';
        empty.textContent = 'Empty — select this row, then click films on the left to add them.';
        filmsContainer.appendChild(empty);
    } else {
        row.movie_ids.forEach(movieId => {
            const movie = CineAllApp.getMovieById(movieId, STATE.movies);
            if (!movie) return;

            const filmBtn = document.createElement('button');
            filmBtn.className = 'row-film';
            filmBtn.title = 'Click to remove';
            filmBtn.onclick = (e) => {
                e.stopPropagation();
                removeFilmFromRow(row.row_id, movieId);
            };

            filmBtn.appendChild(CineAllApp.createPoster(movie, 50, 72, { showMeta: false, muted: true }));

            const overlay = document.createElement('div');
            overlay.className = 'row-film-overlay';
            overlay.innerHTML = '<span style="color: #fff; font-family: var(--font-mono); font-size: 14px">×</span>';
            filmBtn.appendChild(overlay);

            filmsContainer.appendChild(filmBtn);
        });
    }

    content.appendChild(filmsContainer);
    layout.appendChild(content);

    // Controls column
    const controls = document.createElement('div');
    controls.className = 'row-controls';

    const toggleBtn = document.createElement('button');
    toggleBtn.className = 'row-toggle';
    toggleBtn.onclick = (e) => {
        e.stopPropagation();
        patchRow(row.row_id, { enabled: !row.enabled });
    };

    const toggleLabel = document.createElement('span');
    toggleLabel.className = 'row-toggle-label ' + (row.enabled ? 'enabled' : 'disabled');
    toggleLabel.textContent = row.enabled ? 'Live' : 'Hidden';
    toggleBtn.appendChild(toggleLabel);

    const toggleSwitch = document.createElement('div');
    toggleSwitch.className = 'toggle-switch ' + (row.enabled ? 'enabled' : 'disabled');
    const knob = document.createElement('div');
    knob.className = 'toggle-knob ' + (row.enabled ? 'enabled' : 'disabled');
    toggleSwitch.appendChild(knob);
    toggleBtn.appendChild(toggleSwitch);
    controls.appendChild(toggleBtn);

    const count = document.createElement('div');
    count.className = 'row-count';
    count.textContent = `${row.movie_ids.length} films`;
    controls.appendChild(count);

    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'row-delete';
    deleteBtn.textContent = 'Delete';
    deleteBtn.onclick = (e) => {
        e.stopPropagation();
        deleteRow(row.row_id);
    };
    controls.appendChild(deleteBtn);

    layout.appendChild(controls);
    card.appendChild(layout);

    return card;
}

/**
 * Render preview mode
 */
function renderPreview() {
    const preview = document.createElement('div');
    preview.className = 'preview-mode';
    preview.innerHTML = '<div style="text-align: center; padding: 60px; color: var(--cmuted);">Preview mode coming soon. Click "✦ Editing view" to return to the composer.</div>';
    return preview;
}

// ============================================================================
// USER ACTIONS
// ============================================================================

/**
 * Toggle film in active row
 */
function toggleFilmInActiveRow(movieId) {
    const activeRow = STATE.rows.find(r => r.row_id === STATE.activeRowId);
    if (!activeRow) return;

    if (activeRow.movie_ids.includes(movieId)) {
        activeRow.movie_ids = activeRow.movie_ids.filter(id => id !== movieId);
    } else {
        activeRow.movie_ids.push(movieId);
    }

    markDirty();
    saveRowMovies(activeRow);
}

/**
 * Patch row properties
 */
function patchRow(rowId, updates) {
    const row = STATE.rows.find(r => r.row_id === rowId);
    if (!row) return;

    Object.assign(row, updates);
    markDirty();

    // Debounced save
    clearTimeout(patchRow.timeout);
    patchRow.timeout = setTimeout(() => saveRow(row), 500);
}

/**
 * Remove film from row
 */
function removeFilmFromRow(rowId, movieId) {
    const row = STATE.rows.find(r => r.row_id === rowId);
    if (!row) return;

    row.movie_ids = row.movie_ids.filter(id => id !== movieId);
    markDirty();
    saveRowMovies(row);
}

/**
 * Move row up or down
 */
function moveRow(rowId, direction) {
    const index = STATE.rows.findIndex(r => r.row_id === rowId);
    const newIndex = index + direction;

    if (newIndex < 0 || newIndex >= STATE.rows.length) return;

    [STATE.rows[index], STATE.rows[newIndex]] = [STATE.rows[newIndex], STATE.rows[index]];
    markDirty();
    render();
    saveRowOrder();
}

/**
 * Reorder rows via drag-drop
 */
function reorderRow(draggedId, targetId) {
    if (draggedId === targetId) return;

    const fromIndex = STATE.rows.findIndex(r => r.row_id === draggedId);
    const toIndex = STATE.rows.findIndex(r => r.row_id === targetId);

    const [movedRow] = STATE.rows.splice(fromIndex, 1);
    STATE.rows.splice(toIndex, 0, movedRow);

    markDirty();
    render();
    saveRowOrder();
}

/**
 * Add new row
 */
async function addNewRow() {
    const rowId = 'row_' + Math.random().toString(36).slice(2, 7);

    const result = await CineAllApp.apiCall('homepage.php?action=create_row', 'POST', {
        row_id: rowId,
        title: 'Untitled row',
        kicker: 'New section'
    });

    if (result.success) {
        await loadData();
        updateState({ activeRowId: rowId });
    }
}

/**
 * Delete row
 */
async function deleteRow(rowId) {
    if (!confirm('Delete this row?')) return;

    const result = await CineAllApp.apiCall('homepage.php?action=delete_row', 'POST', { row_id: rowId });

    if (result.success) {
        STATE.rows = STATE.rows.filter(r => r.row_id !== rowId);
        render();
    }
}

/**
 * Change hero movie
 */
function changeHero() {
    const currentIndex = STATE.movies.findIndex(m => m.movie_id === STATE.hero);
    const nextMovie = STATE.movies[(currentIndex + 1) % STATE.movies.length];

    STATE.hero = nextMovie.movie_id;
    STATE.heroTagline = nextMovie.tagline || '';
    markDirty();
    render();

    // Save hero
    CineAllApp.apiCall('homepage.php?action=update_hero', 'POST', {
        hero_movie_id: STATE.hero,
        hero_tagline: STATE.heroTagline
    });
}

/**
 * Publish changes
 */
async function publishChanges() {
    const result = await CineAllApp.apiCall('homepage.php?action=publish', 'POST');

    if (result.success) {
        STATE.dirty = false;
        render();
        alert('Homepage published successfully!');
    }
}

// ============================================================================
// API SAVE FUNCTIONS
// ============================================================================

/**
 * Save row details
 */
async function saveRow(row) {
    await CineAllApp.apiCall('homepage.php?action=update_row', 'POST', {
        row_id: row.row_id,
        title: row.title,
        kicker: row.kicker,
        enabled: row.enabled
    });
}

/**
 * Save row movies
 */
async function saveRowMovies(row) {
    await CineAllApp.apiCall('homepage.php?action=update_row_movies', 'POST', {
        row_id: row.row_id,
        movie_ids: row.movie_ids
    });
}

/**
 * Save row order
 */
async function saveRowOrder() {
    await CineAllApp.apiCall('homepage.php?action=reorder_rows', 'POST', {
        row_order: STATE.rows.map(r => r.row_id)
    });
}

</script>

<?php include '../includes/footer.php'; ?>
