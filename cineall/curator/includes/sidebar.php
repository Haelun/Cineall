<?php
/**
 * Curator — sidebar navigation. Links are absolute (CURATOR_URL) so they work
 * from both the dashboard (root) and the pages/ subfolder.
 */
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$user = getCurrentUser();
$curName  = $user ? ($user['display_name'] ?: $user['name']) : 'Curator';
$curEmail = $user ? $user['email'] : 'curator@cineall.com';

$navigation = [
    'Overview' => [
        ['id' => 'index',     'label' => 'Dashboard', 'glyph' => '◐', 'file' => 'index.php'],
        ['id' => 'analytics', 'label' => 'Analytics', 'glyph' => '◔', 'file' => 'pages/analytics.php'],
    ],
    'Catalogue' => [
        ['id' => 'films',        'label' => 'Films',        'glyph' => '⚏', 'file' => 'pages/films.php'],
        ['id' => 'availability', 'label' => 'Availability', 'glyph' => '⚌', 'file' => 'pages/availability.php'],
        ['id' => 'editorial',    'label' => 'Homepage',     'glyph' => '✦', 'file' => 'pages/editorial.php'],
    ],
    'Community' => [
        ['id' => 'reviews', 'label' => 'Reviews', 'glyph' => '✎', 'file' => 'pages/reviews.php'],
    ],
];
?>
<aside class="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-title">
      CineAll<span style="color: var(--accent)">.</span>
      <span class="sidebar-badge">Curator</span>
    </div>
    <div class="sidebar-subtitle">presentation layer · read-only data</div>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($navigation as $group_name => $items): ?>
      <div class="nav-group">
        <div class="nav-group-label"><?php echo escape($group_name); ?></div>
        <?php foreach ($items as $item): ?>
          <a href="<?php echo CURATOR_URL . '/' . $item['file']; ?>"
             class="nav-item <?php echo $current_page === $item['id'] ? 'active' : ''; ?>">
            <span class="glyph"><?php echo $item['glyph']; ?></span>
            <span><?php echo escape($item['label']); ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    <div class="nav-group">
      <div class="nav-group-label">Account</div>
      <a href="<?php echo APP_URL; ?>/index.php" class="nav-item"><span class="glyph">↗</span><span>View site</span></a>
      <a href="<?php echo AUTH_URL; ?>/logout.php" class="nav-item"><span class="glyph">⏻</span><span>Sign out</span></a>
    </div>
  </nav>

  <div class="sidebar-user">
    <div class="user-avatar"><?php echo strtoupper(substr($curName, 0, 1)); ?></div>
    <div class="user-info">
      <div class="user-email"><?php echo escape($curEmail); ?></div>
      <div class="user-title">Content Curator</div>
    </div>
  </div>
</aside>
