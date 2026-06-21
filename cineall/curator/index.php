<?php
/**
 * ============================================================================
 * DASHBOARD - Main landing page
 * ============================================================================
 * Shows overview statistics and recent activity
 */

require_once __DIR__ . '/php/bootstrap.php';

// Fetch dashboard data
$db = getDB();

// Get total counts
$stmt = $db->query("SELECT COUNT(*) as count FROM movies");
$total_movies = $stmt->fetch()['count'];

$stmt = $db->query("SELECT COUNT(*) as count FROM reviews WHERE status = 'approved'");
$total_reviews = $stmt->fetch()['count'];

$stmt = $db->query("SELECT COUNT(*) as count FROM home_rows WHERE is_active = 1");
$live_rows = $stmt->fetch()['count'];

// Get recent activity
$stmt = $db->prepare("
    SELECT * FROM activity_log
    ORDER BY created_at DESC
    LIMIT 10
");
$stmt->execute();
$recent_activity = $stmt->fetchAll();

// Get homepage status
$stmt = $db->query("SELECT is_published FROM homepage_settings ORDER BY id DESC LIMIT 1");
$homepage_status = $stmt->fetch();

include 'includes/header.php';
?>

<div id="app">
  <?php include 'includes/sidebar.php'; ?>

  <main class="main-content">
    <?php include 'includes/top-header.php'; ?>

    <div class="page-content">
      <!-- Page Header -->
      <div class="page-header">
        <div class="page-kicker">Overview</div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">
          Content operations overview — films, reviews, and user engagement at a glance.
        </p>
      </div>

      <!-- KPI Cards -->
      <div class="kpi-grid">
        <div class="card">
          <div class="kpi-label">Total films</div>
          <div class="kpi-value"><?php echo $total_movies; ?></div>
        </div>

        <div class="card">
          <div class="kpi-label">Active reviews</div>
          <div class="kpi-value"><?php echo $total_reviews; ?></div>
        </div>

        <div class="card">
          <div class="kpi-label">Homepage rows</div>
          <div class="kpi-value"><?php echo $live_rows; ?></div>
          <div class="kpi-delta" style="color: var(--amuted)">
            <?php echo $homepage_status && $homepage_status['is_published'] ? 'Published' : 'Unpublished'; ?>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Recent Activity</h3>
          <p class="card-subtitle">Latest content operations and changes</p>
        </div>

        <table class="data-table">
          <thead>
            <tr>
              <th>Action</th>
              <th>Entity</th>
              <th>Time</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recent_activity)): ?>
              <tr>
                <td colspan="4" style="text-align: center; padding: 40px; color: var(--amuted);">
                  No recent activity
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($recent_activity as $activity): ?>
                <tr>
                  <td><?php echo escape($activity['action']); ?></td>
                  <td>
                    <?php if ($activity['target']): ?>
                      <span class="badge badge-neutral">
                        <?php echo escape($activity['target']); ?>
                      </span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo timeAgo($activity['created_at']); ?></td>
                  <td>
                    <span class="badge badge-accent">Completed</span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-2" style="margin-top: 32px;">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Pending Reviews</h3>
          </div>
          <?php
            $stmt = $db->query("SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'");
            $pending_reviews = $stmt->fetch()['count'];
          ?>
          <p style="font-size: 36px; font-weight: 500; margin-bottom: 12px;">
            <?php echo $pending_reviews; ?>
          </p>
          <p class="text-muted">reviews awaiting moderation</p>
          <div style="margin-top: 16px;">
            <a href="pages/reviews.php" class="btn btn-primary btn-sm">Review Now</a>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Homepage Status</h3>
          </div>
          <p style="font-size: 36px; font-weight: 500; margin-bottom: 12px;">
            <?php echo $live_rows; ?> / <?php
              $stmt = $db->query("SELECT COUNT(*) as count FROM home_rows");
              echo $stmt->fetch()['count'];
            ?>
          </p>
          <p class="text-muted">rows currently live</p>
          <div style="margin-top: 16px;">
            <a href="pages/editorial.php" class="btn btn-primary btn-sm">Edit Homepage</a>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<?php include 'includes/footer.php'; ?>
