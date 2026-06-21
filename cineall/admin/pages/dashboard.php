<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$db = getDB();

// ---- real counts from the database ----
$totalFilms     = (int)$db->query("SELECT COUNT(*) FROM movies")->fetchColumn();
$totalPlatforms = (int)$db->query("SELECT COUNT(*) FROM platforms")->fetchColumn();
$totalUsers     = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$totalReviews   = (int)$db->query("SELECT COUNT(*) FROM reviews")->fetchColumn();

$pendingReviews = (int)$db->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();
$filmsNoLinks   = (int)$db->query("SELECT COUNT(*) FROM movies m WHERE NOT EXISTS (SELECT 1 FROM availability a WHERE a.movie_id = m.id)")->fetchColumn();
$filmsNoCast    = (int)$db->query("SELECT COUNT(*) FROM movies m WHERE NOT EXISTS (SELECT 1 FROM cast_members c WHERE c.movie_id = m.id)")->fetchColumn();

// films available on each platform (real)
$byPlatform = $db->query("
    SELECT p.name, COUNT(DISTINCT a.movie_id) AS films
    FROM platforms p
    LEFT JOIN availability a ON a.platform_id = p.id
    GROUP BY p.id
    ORDER BY films DESC, p.name
")->fetchAll();

// recent activity (real)
$activities = $db->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 8")->fetchAll();

$page_title = 'Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
    <div class="page-head-info">
        <div class="page-kicker">Operations</div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">A live count of the catalogue, taken straight from the database.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="<?php echo APP_URL; ?>/index.php" target="_blank" style="text-decoration:none;">View site ↗</a>
    </div>
</div>

<!-- KPI Cards (all real counts) -->
<div class="card-grid card-grid-4 mb-24">
    <div class="kpi-card">
        <div class="kpi-label">Films</div>
        <div class="kpi-value"><?php echo $totalFilms; ?></div>
        <div class="kpi-delta" style="color: var(--amuted);">in the catalogue</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Platforms</div>
        <div class="kpi-value"><?php echo $totalPlatforms; ?></div>
        <div class="kpi-delta" style="color: var(--amuted);">streaming services</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Registered users</div>
        <div class="kpi-value"><?php echo $totalUsers; ?></div>
        <div class="kpi-delta" style="color: var(--amuted);">accounts signed up</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Reviews</div>
        <div class="kpi-value" style="color: var(--accent);"><?php echo $totalReviews; ?></div>
        <div class="kpi-delta" style="color: var(--amuted);"><?php echo $pendingReviews; ?> pending</div>
    </div>
</div>

<div class="card-grid card-grid-2 mb-24" style="grid-template-columns: 1fr 1fr;">
    <!-- Films per platform (real) -->
    <div class="card">
        <div class="kpi-label mb-16">Films available per platform</div>
        <table class="data-table" style="width:100%;">
            <thead><tr><th>Platform</th><th>Films</th></tr></thead>
            <tbody>
            <?php foreach ($byPlatform as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td class="text-mono"><?php echo (int)$row['films']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Needs attention (real) -->
    <div class="card">
        <div class="kpi-label mb-16">Needs your attention</div>
        <div class="flex flex-col gap-12">
            <div class="needs-item">
                <div class="needs-icon <?php echo $pendingReviews ? 'warn' : 'neutral'; ?>"><?php echo $pendingReviews; ?></div>
                <div>
                    <div class="needs-title">Reviews pending moderation</div>
                    <div class="needs-detail">Waiting for approve / flag.</div>
                </div>
                <a class="btn btn-sm btn-ghost" href="<?php echo CURATOR_URL; ?>/pages/reviews.php">Open →</a>
            </div>
            <div class="needs-item">
                <div class="needs-icon <?php echo $filmsNoLinks ? 'warn' : 'neutral'; ?>"><?php echo $filmsNoLinks; ?></div>
                <div>
                    <div class="needs-title">Films with no watch links</div>
                    <div class="needs-detail">Assign them to a platform in the film editor.</div>
                </div>
                <a class="btn btn-sm btn-ghost" href="films.php">Open →</a>
            </div>
            <div class="needs-item" style="border-bottom: none;">
                <div class="needs-icon <?php echo $filmsNoCast ? 'warn' : 'neutral'; ?>"><?php echo $filmsNoCast; ?></div>
                <div>
                    <div class="needs-title">Films with no cast listed</div>
                    <div class="needs-detail">Add the main actors in the film editor.</div>
                </div>
                <a class="btn btn-sm btn-ghost" href="films.php">Open →</a>
            </div>
        </div>
    </div>
</div>

<!-- Recent activity (real) -->
<div class="card">
    <div class="kpi-label mb-16">Recent activity</div>
    <div class="flex flex-col">
        <?php if (empty($activities)): ?>
            <div class="text-muted" style="padding:12px;">No activity yet.</div>
        <?php else: ?>
            <?php foreach ($activities as $index => $activity): ?>
                <div class="activity-item" <?php echo ($index === count($activities) - 1) ? 'style="border-bottom: none;"' : ''; ?>>
                    <span class="badge badge-neutral"><?php echo htmlspecialchars($activity['action']); ?></span>
                    <span class="activity-target"><?php echo htmlspecialchars($activity['target']); ?></span>
                    <span class="activity-time"><?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
