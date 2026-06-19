<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME . ' — Film Aggregator'; ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,300..700;1,6..72,300..700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- CSS Files -->
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/pages.css">
</head>
<body>
    <!-- Top Navigation -->
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
        </div>

        <div class="top-nav__actions">
            <!-- Search -->
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

            <!-- My Services Toggle -->
            <button class="btn btn-small" id="my-services-toggle">
                My services
            </button>

            <!-- Account Button -->
            <button class="btn-icon" data-nav="account">L</button>
        </div>
    </nav>
