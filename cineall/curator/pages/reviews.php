<?php
/**
 * ============================================================================
 * REVIEWS - Community Reviews Moderation
 * ============================================================================
 */

require_once __DIR__ . '/../php/bootstrap.php';

$current_page = 'reviews';

$db = getDB();
$stmt = $db->query("
    SELECT r.id, m.title AS movie_title, r.user_name AS username, r.rating,
           r.snippet AS review_text,
           CASE WHEN r.is_flagged = 1 THEN 'flagged' ELSE r.status END AS status,
           r.created_at
    FROM reviews r
    INNER JOIN movies m ON r.movie_id = m.id
    ORDER BY r.created_at DESC
    LIMIT 50
");
$reviews = $stmt->fetchAll();

include '../includes/header.php';
?>

<div id="app">
  <?php include '../includes/sidebar.php'; ?>

  <main class="main-content">
    <?php include '../includes/top-header.php'; ?>

    <div class="page-content">
      <div class="page-header">
        <div class="page-kicker">Community</div>
        <h1 class="page-title">Reviews</h1>
        <p class="page-subtitle">
          Moderate user reviews. Curators can approve or flag reviews but cannot delete them.
        </p>
      </div>

      <div class="card">
        <table class="data-table">
          <thead>
            <tr>
              <th>Film</th>
              <th>User</th>
              <th>Rating</th>
              <th>Review</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reviews as $review): ?>
              <tr>
                <td><?php echo escape($review['movie_title']); ?></td>
                <td><strong><?php echo escape($review['username']); ?></strong></td>
                <td>
                  <?php
                  $stars = str_repeat('⭐', $review['rating']);
                  echo $stars;
                  ?>
                </td>
                <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                  <?php echo escape(substr($review['review_text'], 0, 100)); ?>...
                </td>
                <td>
                  <?php
                  $statusClass = [
                      'approved' => 'badge-good',
                      'pending' => 'badge-accent',
                      'flagged' => 'badge-warn'
                  ][$review['status']] ?? 'badge-neutral';
                  ?>
                  <span class="badge <?php echo $statusClass; ?>">
                    <?php echo ucfirst($review['status']); ?>
                  </span>
                </td>
                <td><?php echo timeAgo($review['created_at']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
