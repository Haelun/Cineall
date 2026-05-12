<?php
require_once 'config/config.php';

session_name(SESSION_NAME);
session_start();

$pageTitle = 'Discover';
include 'php/includes/header.php';
?>

<!-- Main Content Container -->
<main id="main-content">
    <!-- Content loaded dynamically by JavaScript -->
    <div style="display: flex; align-items: center; justify-content: center; min-height: 50vh;">
        <div class="loading"></div>
    </div>
</main>

<?php

// Include footer
include 'php/includes/footer.php';
?>
