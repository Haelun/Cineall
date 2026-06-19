<?php
/**
 * ============================================================================
 * TOP HEADER
 * ============================================================================
 * Header bar with breadcrumb and action buttons
 */

$page_labels = [
    'index' => 'Dashboard',
    'analytics' => 'Analytics',
    'films' => 'Films',
    'availability' => 'Availability',
    'editorial' => 'Homepage',
    'reviews' => 'Reviews',
];

$current_label = $page_labels[$current_page] ?? ucfirst($current_page);
?>

<header class="top-header">
  <div class="header-breadcrumb">
    Curator / <span class="current"><?php echo escape($current_label); ?></span>
  </div>

  <div class="header-spacer"></div>

  <!-- Search (placeholder for now) -->
  <div class="header-search">
    <span>⌕</span>
    <span>Search films, users, reviews…</span>
    <span style="margin-left: auto; padding: 1px 5px; border: 1px solid var(--aborder); border-radius: 2px; font-size: 9px">⌘K</span>
  </div>

  <!-- Action Button -->
  <a class="btn btn-primary" href="<?php echo APP_URL; ?>/index.php" style="text-decoration:none;">Preview site ↗</a>
</header>
