<?php
/**
 * ============================================================================
 * FILMS - Movie Catalogue
 * ============================================================================
 */

require_once __DIR__ . '/../php/bootstrap.php';

$current_page = 'films';

// Get all movies
$db = getDB();
$stmt = $db->query("
    SELECT m.*, m.movie_key AS movie_id, GROUP_CONCAT(g.name) as genre_list
    FROM movies m
    LEFT JOIN movie_genres mg ON m.id = mg.movie_id
    LEFT JOIN genres g ON mg.genre_id = g.id
    GROUP BY m.id
    ORDER BY m.year DESC, m.title ASC
");
$movies = $stmt->fetchAll();

include '../includes/header.php';
?>

<div id="app">
  <?php include '../includes/sidebar.php'; ?>

  <main class="main-content">
    <?php include '../includes/top-header.php'; ?>

    <div class="page-content">
      <div class="page-header">
        <div class="page-kicker">Catalogue</div>
        <h1 class="page-title">Films</h1>
        <p class="page-subtitle">
          Browse and edit film metadata. Curators can update details but cannot add or remove films.
        </p>
      </div>

      <div class="card">
        <table class="data-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Director</th>
              <th>Year</th>
              <th>Genres</th>
              <th>Runtime</th>
              <th>Scores</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($movies as $movie): ?>
              <tr>
                <td><strong><?php echo escape($movie['title']); ?></strong></td>
                <td><?php echo escape($movie['director']); ?></td>
                <td><?php echo escape($movie['year']); ?></td>
                <td><?php echo escape($movie['genre_list'] ?? ''); ?></td>
                <td><?php echo escape($movie['runtime']); ?> min</td>
                <td>
                  <span class="badge badge-good">C: <?php echo $movie['critic_score']; ?></span>
                  <span class="badge badge-accent">A: <?php echo $movie['audience_score']; ?></span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<?php include '../includes/footer.php'; ?>
