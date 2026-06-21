<?php
/**
 * CineAll — public site entry point.
 * The page shell loads here; the JS app (assets/js/app.js) fetches data
 * from /api and renders everything inside #main-content.
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

cineall_session_start();

$currentUser = getCurrentUser();          // null for guests
$pageTitle   = 'Discover';

include __DIR__ . '/includes/header.php';
?>

<main id="main-content">
    <div style="display: flex; align-items: center; justify-content: center; min-height: 50vh;">
        <div class="loading"></div>
    </div>
</main>

<?php
include __DIR__ . '/includes/footer.php';
