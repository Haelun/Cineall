<?php
/**
 * CineAll Admin - Film Editor Page
 *
 * Add or edit a film
 */

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
$platforms = $db->query("SELECT * FROM platforms")->fetchAll();

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
        'genres'         => array_filter(array_map('trim', explode(',', $_POST['genres'] ?? ''))),
        'cast'           => array_filter(array_map('trim', explode(',', $_POST['cast'] ?? ''))),
    ];

    try {
        if ($movieId) {
            admin_save_movie($db, $data, $movieId);
            logActivity($ADMIN_USER['name'] ?? 'Admin', 'updated film', $data['title']);
            $message = 'Film updated successfully!';
            $movie = admin_fetch_movie($db, $movieId);
        } else {
            $newKey = admin_save_movie($db, $data, null);
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
