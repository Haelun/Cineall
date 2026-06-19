<?php
/**
 * CineAll Admin - Dashboard Page
 *
 * Main dashboard with KPIs, charts, and overview
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$page_title = 'Dashboard';
include __DIR__ . '/../includes/header.php';

// Fetch analytics data
$db = getDB();

// Get latest analytics
$stmt = $db->prepare("SELECT * FROM analytics ORDER BY date DESC LIMIT 14");
$stmt->execute();
$analytics = $stmt->fetchAll();

// Calculate KPIs
$latest = $analytics[0] ?? null;
$previous = $analytics[7] ?? null;

function calculateDelta($current, $previous) {
    if (!$previous || $previous == 0) return 0;
    return round((($current - $previous) / $previous) * 100);
}

// Get activity log
$stmt = $db->prepare("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$activities = $stmt->fetchAll();

// Get counts for "needs attention"
$stmt = $db->query("SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'");
$pending_reviews = $stmt->fetch()['count'];
?>

<!-- Page Header -->
<div class="page-head">
    <div class="page-head-info">
        <div class="page-kicker">Operations · last 14 days</div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Daily pulse of the catalogue, the audience, and the click-throughs out to streaming partners.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-ghost">Export PDF</button>
        <button class="btn btn-primary">Live view ↗</button>
    </div>
</div>

<!-- KPI Cards -->
<div class="card-grid card-grid-4 mb-24">
    <?php if ($latest): ?>
        <div class="kpi-card">
            <div class="kpi-label">Visits</div>
            <div class="kpi-value">
                <?php echo number_format($latest['visits']); ?>
            </div>
            <div class="kpi-delta <?php echo ($latest['visits'] > $previous['visits']) ? 'up' : 'down'; ?>">
                <?php
                $delta = calculateDelta($latest['visits'], $previous['visits']);
                echo ($delta > 0 ? '▲' : '▼') . ' ' . abs($delta) . '% vs last week';
                ?>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Searches</div>
            <div class="kpi-value">
                <?php echo number_format($latest['searches']); ?>
            </div>
            <div class="kpi-delta <?php echo ($latest['searches'] > $previous['searches']) ? 'up' : 'down'; ?>">
                <?php
                $delta = calculateDelta($latest['searches'], $previous['searches']);
                echo ($delta > 0 ? '▲' : '▼') . ' ' . abs($delta) . '% vs last week';
                ?>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Click-throughs</div>
            <div class="kpi-value" style="color: var(--accent);">
                <?php echo number_format($latest['clickthroughs']); ?>
            </div>
            <div class="kpi-delta <?php echo ($latest['clickthroughs'] > $previous['clickthroughs']) ? 'up' : 'down'; ?>">
                <?php
                $delta = calculateDelta($latest['clickthroughs'], $previous['clickthroughs']);
                echo ($delta > 0 ? '▲' : '▼') . ' ' . abs($delta) . '% vs last week';
                ?>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">New sign-ups</div>
            <div class="kpi-value">
                <?php echo $latest['signups']; ?>
            </div>
            <div class="kpi-delta <?php echo ($latest['signups'] > $previous['signups']) ? 'up' : 'down'; ?>">
                <?php
                $delta = calculateDelta($latest['signups'], $previous['signups']);
                echo ($delta > 0 ? '▲' : '▼') . ' ' . abs($delta) . '% vs last week';
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Charts and Top Searches -->
<div class="card-grid card-grid-2 mb-24" style="grid-template-columns: 2fr 1fr;">
    <!-- Sparkline Chart -->
    <div class="card">
        <div class="kpi-label mb-8">Click-throughs to streamers</div>
        <div class="text-serif" style="font-size: 22px; font-weight: 500; margin-bottom: 16px;">Daily — bridge in action</div>
        <div id="sparkline-container"></div>
        <div class="flex justify-between text-mono" style="font-size: 9px; color: var(--amuted); margin-top: 8px;">
            <span>14 days ago</span>
            <span>today</span>
        </div>
    </div>

    <!-- Top Searches -->
    <div class="card">
        <div class="kpi-label mb-8">Top searches</div>
        <div class="text-serif" style="font-size: 22px; font-weight: 500; margin-bottom: 16px;">What people want</div>
        <div id="top-searches-container"></div>
    </div>
</div>

<!-- Activity and Attention -->
<div class="card-grid card-grid-2">
    <!-- Recent Activity -->
    <div class="card">
        <div class="kpi-label mb-16">Recent activity</div>
        <div class="flex flex-col">
            <?php foreach ($activities as $index => $activity): ?>
                <div class="activity-item" <?php echo ($index === count($activities) - 1) ? 'style="border-bottom: none;"' : ''; ?>>
                    <?php
                    $badgeType = 'neutral';
                    if (in_array($activity['action'], ['banned', 'deleted'])) $badgeType = 'bad';
                    elseif (in_array($activity['action'], ['approved', 'created'])) $badgeType = 'good';
                    ?>
                    <span class="badge badge-<?php echo $badgeType; ?>"><?php echo $activity['action']; ?></span>
                    <span class="activity-target"><?php echo htmlspecialchars($activity['target']); ?></span>
                    <span class="activity-time"><?php echo date('g:i A', strtotime($activity['created_at'])); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Needs Attention -->
    <div class="card">
        <div class="kpi-label mb-16">Needs your attention</div>
        <div class="flex flex-col gap-12">
            <div class="needs-item">
                <div class="needs-icon bad">3</div>
                <div>
                    <div class="needs-title">Broken platform links</div>
                    <div class="needs-detail">Detected by health-check. Auto-retrying.</div>
                </div>
                <button class="btn btn-sm btn-ghost">Open →</button>
            </div>

            <div class="needs-item">
                <div class="needs-icon warn">4</div>
                <div>
                    <div class="needs-title">Films leaving in 7 days</div>
                    <div class="needs-detail">Editorial may want to surface these.</div>
                </div>
                <button class="btn btn-sm btn-ghost">Open →</button>
            </div>

            <div class="needs-item">
                <div class="needs-icon warn"><?php echo $pending_reviews; ?></div>
                <div>
                    <div class="needs-title">Reviews pending moderation</div>
                    <div class="needs-detail">Including flagged items.</div>
                </div>
                <button class="btn btn-sm btn-ghost">Open →</button>
            </div>

            <div class="needs-item" style="border-bottom: none;">
                <div class="needs-icon neutral">3</div>
                <div>
                    <div class="needs-title">Films with missing metadata</div>
                    <div class="needs-detail">Posters, trailers, or subtitle lists.</div>
                </div>
                <button class="btn btn-sm btn-ghost">Open →</button>
            </div>
        </div>
    </div>
</div>

<script>
// Render sparkline
const clickthroughData = <?php echo json_encode(array_column(array_reverse($analytics), 'clickthroughs')); ?>;
document.getElementById('sparkline-container').innerHTML = Components.Sparkline.render(clickthroughData, {
    height: 120,
    color: 'var(--accent)'
});

// Render top searches (mock data for now - you can fetch from database)
const topSearches = [
    { q: 'vessel', n: 1840, change: +112 },
    { q: 'tanaka reiji', n: 920, change: +88 },
    { q: 'documentary', n: 720, change: +4 },
    { q: 'sci-fi 2025', n: 680, change: +24 },
    { q: 'saltwater year', n: 610, change: -12 },
    { q: 'horror', n: 540, change: +8 }
];

let searchesHTML = '<div class="flex flex-col gap-8">';
topSearches.forEach((s, i) => {
    searchesHTML += `
        <div class="search-item">
            <span class="search-rank">${String(i + 1).padStart(2, '0')}</span>
            <span class="search-query">${s.q}</span>
            <span class="search-count">${s.n.toLocaleString()}</span>
            <span class="search-change ${s.change > 0 ? 'up' : 'down'}">${s.change > 0 ? '+' : ''}${s.change}</span>
        </div>
    `;
});
searchesHTML += '</div>';
document.getElementById('top-searches-container').innerHTML = searchesHTML;
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
