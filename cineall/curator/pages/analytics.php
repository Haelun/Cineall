<?php
/**
 * ============================================================================
 * ANALYTICS - real catalogue numbers (counted from the database)
 * ============================================================================
 */
require_once __DIR__ . '/../php/bootstrap.php';
$current_page = 'analytics';

$db = getDB();

// real totals
$totalFilms     = (int)$db->query("SELECT COUNT(*) FROM movies")->fetchColumn();
$totalReviews   = (int)$db->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$totalPlatforms = (int)$db->query("SELECT COUNT(*) FROM platforms")->fetchColumn();

// films available per platform (real)
$byPlatform = $db->query("
    SELECT p.name, COUNT(DISTINCT a.movie_id) AS films
    FROM platforms p
    LEFT JOIN availability a ON a.platform_id = p.id
    GROUP BY p.id
    ORDER BY films DESC, p.name
")->fetchAll();

// reviews grouped by status (real)
$reviewRows = $db->query("SELECT status, COUNT(*) AS c FROM reviews GROUP BY status")->fetchAll();
$reviewCounts = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
foreach ($reviewRows as $r) { $reviewCounts[$r['status']] = (int)$r['c']; }

include '../includes/header.php';
?>

<div id="app">
  <?php include '../includes/sidebar.php'; ?>

  <main class="main-content">
    <?php include '../includes/top-header.php'; ?>

    <div class="page-content">
      <div class="page-header">
        <div class="page-kicker">Insights</div>
        <h1 class="page-title">Catalogue analytics</h1>
        <p class="page-subtitle">
          Real numbers counted from the database — films, platforms, and reviews.
        </p>
      </div>

      <div class="kpi-grid">
        <div class="card">
          <div class="kpi-label">Total films</div>
          <div class="kpi-value"><?php echo $totalFilms; ?></div>
        </div>
        <div class="card">
          <div class="kpi-label">Platforms</div>
          <div class="kpi-value"><?php echo $totalPlatforms; ?></div>
        </div>
        <div class="card">
          <div class="kpi-label">Reviews</div>
          <div class="kpi-value"><?php echo $totalReviews; ?></div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Films available per platform</h3>
          <p class="card-subtitle">How many films are assigned to each service</p>
        </div>
        <table class="data-table">
          <thead>
            <tr><th>Rank</th><th>Platform</th><th>Films</th></tr>
          </thead>
          <tbody>
            <?php foreach ($byPlatform as $i => $row): ?>
              <tr>
                <td><?php echo $i + 1; ?></td>
                <td><strong><?php echo escape($row['name']); ?></strong></td>
                <td><?php echo (int)$row['films']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="card" style="margin-top: 32px;">
        <div class="card-header">
          <h3 class="card-title">Reviews by status</h3>
        </div>
        <table class="data-table">
          <thead>
            <tr><th>Status</th><th>Count</th></tr>
          </thead>
          <tbody>
            <tr><td><span class="badge badge-neutral">Pending</span></td><td><?php echo $reviewCounts['pending']; ?></td></tr>
            <tr><td><span class="badge badge-accent">Approved</span></td><td><?php echo $reviewCounts['approved']; ?></td></tr>
            <tr><td><span class="badge badge-bad">Rejected / flagged</span></td><td><?php echo $reviewCounts['rejected']; ?></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
