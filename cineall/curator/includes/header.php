<?php
/**
 * Curator — HTML head + asset includes.
 * Bootstrap (DB/session/auth) is loaded by each page before this include.
 */
if (!defined('CINEALL_CONFIG')) {
    require_once dirname(__DIR__) . '/php/bootstrap.php';
}

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$page_titles = [
    'index' => 'Dashboard', 'editorial' => 'Homepage Composer', 'films' => 'Films Catalogue',
    'availability' => 'Availability', 'reviews' => 'Reviews', 'analytics' => 'Analytics',
];
$page_title = $page_titles[$current_page] ?? ucfirst($current_page);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title><?php echo escape($page_title); ?> - CineAll Curator</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,300..700;1,6..72,300..700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet" />

  <!-- Shared design tokens, then curator styles (absolute paths so they work from any page depth) -->
  <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/variables.css" />
  <link rel="stylesheet" href="<?php echo CURATOR_URL; ?>/css/base.css" />
  <link rel="stylesheet" href="<?php echo CURATOR_URL; ?>/css/layout.css" />
  <link rel="stylesheet" href="<?php echo CURATOR_URL; ?>/css/components.css" />
  <link rel="stylesheet" href="<?php echo CURATOR_URL; ?>/css/curator.css" />

  <script>
    // API base for the curator JS (absolute so it works from /curator and /curator/pages)
    window.CURATOR_API = '<?php echo CURATOR_URL; ?>/php/api';
  </script>
  <script src="<?php echo CURATOR_URL; ?>/js/app.js" defer></script>
</head>
<body>
