<?php
/**
 * ============================================================================
 * AVAILABILITY - Platform availability
 * ============================================================================
 */

require_once __DIR__ . '/../php/bootstrap.php';

$current_page = 'availability';

$db = getDB();
$stmt = $db->query("
    SELECT m.title, p.name as platform_name, a.kind, a.price_from, a.updated_at
    FROM availability a
    INNER JOIN movies m ON a.movie_id = m.id
    INNER JOIN platforms p ON a.platform_id = p.id
    ORDER BY m.title, p.name
    LIMIT 100
");
$availability_records = $stmt->fetchAll();

include '../includes/header.php';
?>

<div id="app">
  <?php include '../includes/sidebar.php'; ?>

  <main class="main-content">
    <?php include '../includes/top-header.php'; ?>

    <div class="page-content">
      <div class="page-header">
        <div class="page-kicker">Catalogue</div>
        <h1 class="page-title">Availability</h1>
        <p class="page-subtitle">
          View which platforms carry each film and current pricing.
        </p>
      </div>

      <div class="card">
        <table class="data-table">
          <thead>
            <tr>
              <th>Film</th>
              <th>Platform</th>
              <th>Type</th>
              <th>Price</th>
              <th>Updated</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($availability_records as $record): ?>
              <tr>
                <td><?php echo escape($record['title']); ?></td>
                <td><?php echo escape($record['platform_name']); ?></td>
                <td>
                  <?php
                  $kindClass = $record['kind'] === 'subscription' ? 'badge-good' : 'badge-neutral';
                  ?>
                  <span class="badge <?php echo $kindClass; ?>">
                    <?php echo ucfirst($record['kind']); ?>
                  </span>
                </td>
                <td>
                  <?php
                  if ($record['price_from']) {
                      echo '$' . number_format($record['price_from'], 2);
                  } else {
                      echo 'Included';
                  }
                  ?>
                </td>
                <td><?php echo timeAgo($record['updated_at']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
