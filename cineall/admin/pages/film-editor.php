<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$page_title = 'Film Editor';
$db = getDB();

// Get film by key from URL (?id=movie_key)
$movieId = $_GET['id'] ?? null;   // this is the movie_key
$movie = $movieId ? admin_fetch_movie($db, $movieId) : null;
if ($movie) {
    $page_title = 'Editing: ' . $movie['title'];
}

// All platforms (for availability UI)
$platforms = $db->query("SELECT * FROM platforms ORDER BY name")->fetchAll();

// Existing availability rows (for editing)
$availability = $movie ? admin_get_availability($db, (int)$movie['id']) : [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title'          => $_POST['title'] ?? '',
        'year'           => $_POST['year'] ?? 0,
        'runtime'        => $_POST['runtime'] ?? 0,
        'director'       => $_POST['director'] ?? '',
        'rating'         => $_POST['rating'] ?? '',
        'tagline'        => $_POST['tagline'] ?? '',
        'synopsis'       => $_POST['synopsis'] ?? '',
        'critic_score'   => $_POST['critic_score'] ?? 0,
        'audience_score' => $_POST['audience_score'] ?? 0,
        'trailer_url'    => $_POST['trailer_url'] ?? '',
        'poster_url'     => $_POST['poster_url'] ?? '',
        'genres'         => array_filter(array_map('trim', explode(',', $_POST['genres'] ?? ''))),
        'cast'           => array_filter(array_map('trim', explode(',', $_POST['cast'] ?? ''))),
    ];

    // Collect availability rows from the editor
    $availRows = [];
    $aPlat  = $_POST['avail_platform'] ?? [];
    $aKind  = $_POST['avail_kind'] ?? [];
    $aPrice = $_POST['avail_price'] ?? [];
    $aUrl   = $_POST['avail_url'] ?? [];
    foreach ($aPlat as $i => $pid) {
        if (!$pid) continue;
        $availRows[] = [
            'platform_id' => $pid,
            'kind'        => $aKind[$i] ?? 'subscription',
            'price_from'  => $aPrice[$i] ?? '',
            'url'         => $aUrl[$i] ?? '',
        ];
    }

    try {
        if ($movieId) {
            admin_save_movie($db, $data, $movieId);
            $mid = admin_movie_id($db, $movieId);
            admin_set_availability($db, $mid, $availRows);
            logActivity($ADMIN_USER['name'] ?? 'Admin', 'updated film', $data['title']);
            $message = 'Film updated successfully!';
            $movie = admin_fetch_movie($db, $movieId);
            $availability = admin_get_availability($db, $mid);
        } else {
            $newKey = admin_save_movie($db, $data, null);
            $mid = admin_movie_id($db, $newKey);
            admin_set_availability($db, $mid, $availRows);
            logActivity($ADMIN_USER['name'] ?? 'Admin', 'created film', $data['title']);
            header('Location: film-editor.php?id=' . urlencode($newKey));
            exit;
        }
    } catch (PDOException $e) {
        $error = 'Error saving film: ' . $e->getMessage();
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="page-head">
    <div class="page-head-info">
        <div class="page-kicker"><?php echo $movieId ? "Editing · $movieId" : "Creating new film"; ?></div>
        <h1 class="page-title"><?php echo $movie ? htmlspecialchars($movie['title']) : 'New Film'; ?></h1>
        <?php if ($movie): ?>
            <p class="page-subtitle">Last edited <?php echo date('M d, Y g:i A', strtotime($movie['updated_at'])); ?></p>
        <?php endif; ?>
    </div>
    <div class="page-actions">
        <button class="btn btn-ghost" onclick="window.location.href='films.php'">← Back</button>
        <?php if ($movieId): ?>
            <button class="btn btn-danger">Retire</button>
        <?php endif; ?>
        <button class="btn btn-primary" form="film-form">Save changes</button>
    </div>
</div>

<?php if (isset($message)): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Film Editor Form -->
<form id="film-form" method="POST" action="">
    <div class="card-grid card-grid-2" style="grid-template-columns: 1fr 320px; align-items: start;">
        <!-- Main Form -->
        <div class="flex flex-col gap-16">
            <!-- Identity Section -->
            <div class="card">
                <div class="section-header">§ Identity</div>

                <div class="card-grid card-grid-3">
                    <div class="form-group" style="grid-column: 1 / 3;">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control"
                               value="<?php echo htmlspecialchars($movie['title'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" class="form-control" style="font-family: var(--font-mono);"
                               value="<?php echo $movie['year'] ?? ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Runtime (min)</label>
                        <input type="number" name="runtime" class="form-control" style="font-family: var(--font-mono);"
                               value="<?php echo $movie['runtime'] ?? ''; ?>" required>
                    </div>

                    <div class="form-group" style="grid-column: 1 / 3;">
                        <label class="form-label">Director</label>
                        <input type="text" name="director" class="form-control"
                               value="<?php echo htmlspecialchars($movie['director'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Rating</label>
                        <input type="text" name="rating" class="form-control" style="font-family: var(--font-mono);"
                               value="<?php echo htmlspecialchars($movie['rating'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tagline</label>
                    <input type="text" name="tagline" class="form-control"
                           value="<?php echo htmlspecialchars($movie['tagline'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Synopsis</label>
                    <textarea name="synopsis" class="form-control" rows="4" required><?php echo htmlspecialchars($movie['synopsis'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Cast (comma separated)</label>
                    <input type="text" name="cast" class="form-control"
                           value="<?php echo htmlspecialchars(implode(', ', $movie['cast_array'] ?? [])); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Genres (comma separated)</label>
                    <input type="text" name="genres" class="form-control"
                           value="<?php echo htmlspecialchars(implode(', ', $movie['genres_array'] ?? [])); ?>">
                </div>
            </div>

            <!-- Scores Section -->
            <div class="card">
                <div class="section-header">§ Scores</div>

                <div class="card-grid card-grid-2">
                    <div class="form-group">
                        <label class="form-label">Critic score (0–100)</label>
                        <input type="number" name="critic_score" class="form-control" style="font-family: var(--font-mono);"
                               min="0" max="100" value="<?php echo $movie['critic_score'] ?? 0; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Audience score (0–100)</label>
                        <input type="number" name="audience_score" class="form-control" style="font-family: var(--font-mono);"
                               min="0" max="100" value="<?php echo $movie['audience_score'] ?? 0; ?>">
                    </div>
                </div>
            </div>

            <!-- Media Section -->
            <div class="card">
                <div class="section-header">§ Media</div>

                <div class="form-group">
                    <label class="form-label">Trailer URL
                        <span class="text-muted" style="font-weight:400;font-size:11px;margin-left:6px;">YouTube watch link — e.g. https://www.youtube.com/watch?v=XXXXXXXXXXX</span>
                    </label>
                    <input type="url" name="trailer_url" id="trailer_url" class="form-control"
                           placeholder="https://www.youtube.com/watch?v=…"
                           value="<?php echo htmlspecialchars($movie['trailer_url'] ?? ''); ?>">
                    <!-- Live preview embed -->
                    <div id="trailer-preview" style="margin-top:12px;display:none;">
                        <iframe id="trailer-iframe"
                                width="100%" height="240"
                                style="border:0;border-radius:8px;background:#000;"
                                allowfullscreen
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                        </iframe>
                    </div>
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label class="form-label">Poster URL
                        <span class="text-muted" style="font-weight:400;font-size:11px;margin-left:6px;">TMDB or direct image link — e.g. https://image.tmdb.org/t/p/w500/…</span>
                    </label>
                    <input type="url" name="poster_url" id="poster_url" class="form-control"
                           placeholder="https://image.tmdb.org/t/p/w500/…"
                           value="<?php echo htmlspecialchars($movie['poster_url'] ?? ''); ?>">
                    <!-- Thumbnail preview -->
                    <div id="poster-thumb-preview" style="margin-top:12px;display:<?php echo !empty($movie['poster_url']) ? 'block' : 'none'; ?>;">
                        <img id="poster-thumb-img"
                             src="<?php echo htmlspecialchars($movie['poster_url'] ?? ''); ?>"
                             alt="Poster preview"
                             style="height:160px;width:auto;border-radius:6px;object-fit:cover;border:1px solid var(--border);">
                    </div>
                </div>

                <script>
                    // ── Trailer preview ──────────────────────────────────────
                    (function () {
                        const input   = document.getElementById('trailer_url');
                        const wrapper = document.getElementById('trailer-preview');
                        const iframe  = document.getElementById('trailer-iframe');

                        function getYtId(url) {
                            try {
                                const u = new URL(url);
                                if (u.hostname.includes('youtube.com')) return u.searchParams.get('v');
                                if (u.hostname === 'youtu.be') return u.pathname.slice(1);
                            } catch (e) {}
                            return null;
                        }

                        function updateTrailerPreview() {
                            const vid = getYtId(input.value.trim());
                            if (vid) {
                                iframe.src = `https://www.youtube.com/embed/${vid}`;
                                wrapper.style.display = 'block';
                            } else {
                                iframe.src = '';
                                wrapper.style.display = 'none';
                            }
                        }

                        input.addEventListener('input', updateTrailerPreview);
                        updateTrailerPreview(); // run once on load
                    })();

                    // ── Poster thumbnail preview ─────────────────────────────
                    (function () {
                        const input   = document.getElementById('poster_url');
                        const wrapper = document.getElementById('poster-thumb-preview');
                        const img     = document.getElementById('poster-thumb-img');

                        function updatePosterPreview() {
                            const url = input.value.trim();
                            if (url) {
                                img.src = url;
                                wrapper.style.display = 'block';
                            } else {
                                img.src = '';
                                wrapper.style.display = 'none';
                            }
                        }

                        input.addEventListener('input', updatePosterPreview);
                        img.addEventListener('error', () => { wrapper.style.display = 'none'; });
                    })();
                </script>
            </div>

            <!-- Where to watch (availability) -->
            <div class="card">
                <div class="section-header">§ Where to watch</div>
                <p class="text-muted" style="font-size:12px;margin-bottom:14px;">
                    Assign this film to streaming platforms. For each one, pick how it's offered
                    (subscription / rent / buy), an optional price, and the link that opens this
                    film on that service. Need a service that isn't listed?
                    <a href="platforms.php" class="text-accent">Manage platforms →</a>
                </p>
                <div id="avail-rows" class="flex flex-col gap-12"></div>
                <button type="button" class="btn btn-sm btn-ghost" id="add-avail" style="margin-top:14px;">+ Add platform</button>
            </div>

            <script>
                const ADMIN_PLATFORMS = <?php echo json_encode($platforms); ?>;
                const EXISTING_AVAIL  = <?php echo json_encode($availability ?? []); ?>;

                function availRowHtml(row) {
                    row = row || {};
                    const opts = ADMIN_PLATFORMS.map(p =>
                        `<option value="${p.id}" ${String(row.platform_id) === String(p.id) ? 'selected' : ''}>${p.name}</option>`).join('');
                    const kinds = ['subscription', 'rent', 'buy'].map(k =>
                        `<option value="${k}" ${row.kind === k ? 'selected' : ''}>${k}</option>`).join('');
                    const wrap = document.createElement('div');
                    wrap.className = 'avail-row';
                    wrap.style.cssText = 'display:grid;grid-template-columns:1.2fr 1fr 0.7fr 2fr auto;gap:8px;align-items:center;';
                    wrap.innerHTML = `
                        <select name="avail_platform[]" class="form-control">${opts}</select>
                        <select name="avail_kind[]" class="form-control">${kinds}</select>
                        <input type="number" step="0.01" min="0" name="avail_price[]" class="form-control" placeholder="price" value="${row.price_from ?? ''}">
                        <input type="url" name="avail_url[]" class="form-control" placeholder="https://… link to this film" value="${(row.url || '').replace(/"/g, '&quot;')}">
                        <button type="button" class="btn btn-sm btn-ghost remove-avail" title="Remove">✕</button>`;
                    return wrap;
                }
                const availContainer = document.getElementById('avail-rows');
                function addAvailRow(row) { availContainer.appendChild(availRowHtml(row)); }
                EXISTING_AVAIL.forEach(addAvailRow);
                document.getElementById('add-avail').addEventListener('click', () => addAvailRow());
                availContainer.addEventListener('click', e => {
                    if (e.target.classList.contains('remove-avail')) e.target.closest('.avail-row').remove();
                });
            </script>
        </div>

        <!-- Sidebar -->
        <aside class="flex flex-col gap-16">
            <!-- Poster Preview -->
            <?php if ($movie): ?>
                <div class="card">
                    <div class="kpi-label mb-12">Poster</div>
                    <div id="poster-preview" class="mb-12"></div>
                    <button type="button" class="btn btn-sm btn-ghost">↑ Replace poster</button>

                    <script>
                        const movie = <?php echo json_encode($movie); ?>;
                        document.getElementById('poster-preview').innerHTML =
                            Components.Poster.render(movie, { width: 272, height: 394, showMeta: true, size: 'lg' });
                    </script>
                </div>
            <?php endif; ?>

            <!-- Status -->
            <div class="card">
                <div class="kpi-label mb-12">Status</div>
                <div class="flex flex-col gap-12" style="font-size: 13px;">
                    <div class="flex justify-between">
                        <span class="text-muted">Visibility</span>
                        <span class="badge badge-good">Published</span>
                    </div>
                    <?php if ($movie): ?>
                        <div class="flex justify-between">
                            <span class="text-muted">Created</span>
                            <span class="text-mono"><?php echo date('M d, Y', strtotime($movie['created_at'])); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">Updated</span>
                            <span class="text-mono"><?php echo date('M d, Y', strtotime($movie['updated_at'])); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
