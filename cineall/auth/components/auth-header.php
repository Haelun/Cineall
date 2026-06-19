<?php
/**
 * Authentication Page Header
 * Common header for all auth pages
 */

if (!defined('CINEALL_APP')) {
    define('CINEALL_APP', true);
}

// Load Google Fonts
$fontLink = 'https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,300..700;1,6..72,300..700&family=JetBrains+Mono:wght@400;500;600&display=swap';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - CineAll' : 'CineAll'; ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="<?php echo $fontLink; ?>" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?php echo AUTH_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo AUTH_URL; ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?php echo AUTH_URL; ?>/assets/css/auth.css">

    <?php if (isset($additionalCSS)): ?>
        <?php foreach ((array)$additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo APP_URL . '/' . $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
