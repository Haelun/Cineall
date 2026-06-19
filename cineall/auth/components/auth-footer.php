<?php
/**
 * Authentication Page Footer — shared across all auth pages.
 */
?>

<!-- JavaScript base URLs (single source of truth; main.js reads these) -->
<script>
    window.APP_URL = '<?php echo APP_URL; ?>';        // project root, e.g. http://localhost/cineall
    window.API_URL = '<?php echo AUTH_URL; ?>/api';   // auth API endpoints
</script>
<script src="<?php echo AUTH_URL; ?>/assets/js/main.js"></script>
<script src="<?php echo AUTH_URL; ?>/assets/js/auth.js"></script>

<?php if (isset($additionalJS)): ?>
    <?php foreach ((array)$additionalJS as $js): ?>
        <script src="<?php echo AUTH_URL . '/' . $js; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($inlineJS)): ?>
    <script><?php echo $inlineJS; ?></script>
<?php endif; ?>

</body>
</html>
