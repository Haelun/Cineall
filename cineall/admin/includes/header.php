<?php
/**
 * CineAll Admin — Header.
 * (Auth + session are handled by includes/bootstrap.php, included by each page.)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = $page_title ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - CineAll Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,300..700;1,6..72,300..700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Shared design tokens, then admin-specific styles -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/variables.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/components.css">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <div class="main-content">
            <header class="main-header">
                <div class="breadcrumb">
                    Admin / <span class="current"><?php echo $page_title; ?></span>
                </div>
                <div class="header-spacer"></div>
                <div class="search-box">
                    <span>⌕</span>
                    <input type="text" placeholder="Search films, users, reviews…" id="global-search">
                    <span class="kbd">⌘K</span>
                </div>
                <a class="btn btn-ghost" href="<?php echo APP_URL; ?>/index.php">↗ View site</a>
                <a class="btn-primary" href="film-editor.php" style="text-decoration:none;">+ New film</a>
            </header>

            <div class="page-content">
