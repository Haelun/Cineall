<?php
/**
 * CineAll Admin - Footer Component
 *
 * Closes the page content and includes JavaScript files
 */
?>
            </div>
            <!-- Page Content Ends Here -->
        </div>
        <!-- Main Content Ends Here -->
    </div>
    <!-- Admin Layout Ends Here -->

    <!-- JavaScript -->
    <script src="../js/main.js"></script>
    <script src="../js/components.js"></script>

    <!-- Page-specific JavaScript -->
    <?php if (isset($page_js)): ?>
        <script src="../js/<?php echo $page_js; ?>"></script>
    <?php endif; ?>
</body>
</html>
