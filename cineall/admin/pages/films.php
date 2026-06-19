<?php
/**
 * CineAll Admin - Films Page
 *
 * Manage the film catalogue
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$page_title = 'Films';
include __DIR__ . '/../includes/header.php';

// Get films from database (normalized -> admin shape)
$db = getDB();
$search = $_GET['search'] ?? '';
$movies = admin_fetch_movies($db, $search);
?>

<!-- Page Header -->
<div class="page-head">
    <div class="page-head-info">
        <div class="page-kicker">Catalogue · <?php echo count($movies); ?> titles</div>
        <h1 class="page-title">Films</h1>
        <p class="page-subtitle">Add, edit, retire, and bulk-edit availability across all services.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-ghost">↑ Import CSV</button>
        <button class="btn btn-ghost">↓ Export CSV</button>
        <button class="btn btn-primary" onclick="window.location.href='film-editor.php'">+ New film</button>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <form method="GET" style="display: contents;">
        <input type="text"
               name="search"
               placeholder="Filter by title or director…"
               value="<?php echo htmlspecialchars($search); ?>"
               id="film-search">
    </form>
    <button class="btn btn-ghost">Genre</button>
    <button class="btn btn-ghost">Year</button>
    <button class="btn btn-ghost">Platform</button>
    <button class="btn btn-ghost">Status</button>
</div>

<!-- Selection Bar (shown when items are selected) -->
<div id="selection-bar" class="selection-bar" style="display: none;">
    <span class="selection-count"><span id="selected-count">0</span> selected</span>
    <div class="selection-spacer"></div>
    <button class="btn btn-sm btn-ghost">Add to platform…</button>
    <button class="btn btn-sm btn-ghost">Set expiry…</button>
    <button class="btn btn-sm btn-ghost">Edit genres…</button>
    <button class="btn btn-sm btn-danger">Retire</button>
</div>

<!-- Films Table -->
<div class="data-table">
    <!-- Table Header -->
    <div class="table-header" style="grid-template-columns: 32px 60px 2.5fr 1fr 80px 1.2fr 80px 80px 60px;">
        <div><input type="checkbox" id="select-all"></div>
        <div></div>
        <div>Title</div>
        <div>Director</div>
        <div>Year</div>
        <div>On services</div>
        <div>Critic</div>
        <div>Status</div>
        <div></div>
    </div>

    <!-- Table Rows -->
    <?php foreach ($movies as $movie): ?>
        <div class="table-row movie-row">
            <div><input type="checkbox" class="film-checkbox" data-id="<?php echo $movie['movie_id']; ?>"></div>
            <div id="poster-<?php echo $movie['movie_id']; ?>"></div>
            <div>
                <div class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></div>
                <div class="movie-meta">
                    <?php echo $movie['movie_id']; ?> ·
                    <?php echo implode(', ', $movie['genres_array'] ?? []); ?>
                </div>
            </div>
            <div class="text-serif" style="font-size: 13px;"><?php echo htmlspecialchars($movie['director']); ?></div>
            <div class="text-mono" style="font-size: 12px;"><?php echo $movie['year']; ?></div>
            <div class="platform-row" id="platforms-<?php echo $movie['movie_id']; ?>"></div>
            <div class="text-mono" style="font-size: 13px; color: <?php echo $movie['critic_score'] >= 80 ? 'var(--good)' : 'var(--afg)'; ?>">
                <?php echo $movie['critic_score']; ?>
            </div>
            <div>
                <?php
                $badgeType = $movie['year'] == 2025 ? 'accent' : 'neutral';
                $badgeText = $movie['year'] == 2025 ? 'New' : 'Live';
                ?>
                <span class="badge badge-<?php echo $badgeType; ?>"><?php echo $badgeText; ?></span>
            </div>
            <div>
                <button class="btn btn-sm btn-ghost"
                        onclick="window.location.href='film-editor.php?id=<?php echo $movie['movie_id']; ?>'">
                    Edit
                </button>
            </div>
        </div>

        <!-- Render poster and platform chips with JavaScript -->
        <script>
            (function() {
                const movie = <?php echo json_encode($movie); ?>;

                // Render poster
                document.getElementById('poster-<?php echo $movie['movie_id']; ?>').innerHTML =
                    Components.Poster.render(movie, { width: 40, height: 58, size: 'sm' });

                // Render platform chips
                const platforms = <?php echo json_encode($movie['platforms']); ?>;
                let platformsHTML = '';
                platforms.forEach(platformId => {
                    platformsHTML += Components.PlatformChip.render(platformId, 'md');
                });
                document.getElementById('platforms-<?php echo $movie['movie_id']; ?>').innerHTML = platformsHTML;
            })();
        </script>
    <?php endforeach; ?>
</div>

<script>
// Handle select all
document.getElementById('select-all').addEventListener('change', function(e) {
    const checkboxes = document.querySelectorAll('.film-checkbox');
    checkboxes.forEach(cb => cb.checked = e.target.checked);
    updateSelectionBar();
});

// Handle individual checkboxes
document.querySelectorAll('.film-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectionBar);
});

// Update selection bar
function updateSelectionBar() {
    const checked = document.querySelectorAll('.film-checkbox:checked');
    const selectionBar = document.getElementById('selection-bar');
    const count = document.getElementById('selected-count');

    if (checked.length > 0) {
        selectionBar.style.display = 'flex';
        count.textContent = checked.length;
    } else {
        selectionBar.style.display = 'none';
    }
}

// Live search with debounce
const searchInput = document.getElementById('film-search');
if (searchInput) {
    searchInput.addEventListener('input', Utils.debounce(function(e) {
        const form = e.target.closest('form');
        form.submit();
    }, 500));
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
