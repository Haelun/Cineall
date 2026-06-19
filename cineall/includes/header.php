<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME . ' — Film Aggregator'; ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,300..700;1,6..72,300..700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/pages.css">

    <script>
        // Shared front-end context (who is signed in, where things live)
        window.CINEALL = {
            user: <?php echo $currentUser
                ? json_encode([
                    'id'      => (int)$currentUser['id'],
                    'name'    => $currentUser['display_name'] ?: $currentUser['name'],
                    'email'   => $currentUser['email'],
                    'role'    => $currentUser['role'],
                  ])
                : 'null'; ?>,
            appUrl:  <?php echo json_encode(APP_URL); ?>,
            authUrl: <?php echo json_encode(AUTH_URL); ?>
        };
    </script>
</head>
<body>
    <nav class="top-nav">
        <div class="top-nav__brand">
            <div class="top-nav__logo">
                CineAll<span style="color: var(--accent);">.</span>
            </div>
            <div class="top-nav__tagline">
                Vol. 04 · One library, every service
            </div>
        </div>

        <div class="top-nav__menu">
            <a href="#" class="top-nav__link active" data-nav="home">Discover</a>
            <a href="#" class="top-nav__link" data-nav="genres">Genres</a>
            <a href="#" class="top-nav__link" data-nav="compare">Compare</a>
            <a href="#" class="top-nav__link" data-nav="recs">For you</a>
            <a href="#" class="top-nav__link" data-nav="watchlist">Watchlist</a>
            <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                <a href="<?php echo ADMIN_URL; ?>/" class="top-nav__link">Admin</a>
            <?php endif; ?>
            <?php if ($currentUser && in_array($currentUser['role'], ['curator', 'admin'], true)): ?>
                <a href="<?php echo CURATOR_URL; ?>/" class="top-nav__link">Curator</a>
            <?php endif; ?>
        </div>

        <div class="top-nav__actions">
            <form class="search-input" id="search-form">
                <div class="search-input__field">
                    <span class="search-input__icon">⌕</span>
                    <input type="text"
                           id="search-input"
                           placeholder="Search title, director, cast…"
                           autocomplete="off">
                    <span class="search-input__icon">↵</span>
                </div>
                <div id="search-suggestions" class="search-input__suggestions hidden"></div>
            </form>

            <button class="btn btn-small" id="my-services-toggle">
                My services
            </button>

            <?php if ($currentUser): ?>
                <button class="btn-icon" data-nav="account" title="<?php echo htmlspecialchars($currentUser['display_name'] ?: $currentUser['name']); ?>">
                    <?php echo strtoupper(substr($currentUser['display_name'] ?: $currentUser['name'], 0, 1)); ?>
                </button>
                <a class="btn btn-small" href="<?php echo AUTH_URL; ?>/logout.php">Sign out</a>
            <?php else: ?>
                <a class="btn btn-small btn-primary" href="<?php echo AUTH_URL; ?>/index.php">Sign in</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Main content is injected by index.php below this nav -->

