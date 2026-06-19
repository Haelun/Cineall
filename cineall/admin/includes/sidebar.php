<?php
/**
 * CineAll Admin — Sidebar. Links only to pages that exist.
 */
$current_page = basename($_SERVER['PHP_SELF']);
$adminName  = isset($ADMIN_USER) && $ADMIN_USER ? ($ADMIN_USER['display_name'] ?: $ADMIN_USER['name']) : 'Admin';
$adminEmail = isset($ADMIN_USER) && $ADMIN_USER ? $ADMIN_USER['email'] : 'admin@cineall.com';

$navigation = [
    [
        'group' => 'Overview',
        'items' => [
            ['label' => 'Dashboard', 'glyph' => '◐', 'file' => 'dashboard.php'],
        ]
    ],
    [
        'group' => 'Catalogue',
        'items' => [
            ['label' => 'Films',     'glyph' => '⚏', 'file' => 'films.php'],
            ['label' => 'New film',  'glyph' => '✦', 'file' => 'film-editor.php'],
        ]
    ],
    [
        'group' => 'Community',
        'items' => [
            ['label' => 'Users',     'glyph' => '◉', 'file' => 'users.php'],
        ]
    ],
];
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            CineAll<span class="accent">.</span>
            <span class="sidebar-badge">Admin</span>
        </div>
        <div class="sidebar-version">v<?php echo APP_VERSION; ?> · <?php echo APP_ENV; ?></div>
    </div>

    <nav class="sidebar-nav">
        <?php foreach ($navigation as $group): ?>
            <div class="nav-group">
                <div class="nav-group-title"><?php echo $group['group']; ?></div>
                <?php foreach ($group['items'] as $item): ?>
                    <?php $isActive = ($current_page === $item['file']) ? 'active' : ''; ?>
                    <a href="<?php echo $item['file']; ?>" class="nav-item <?php echo $isActive; ?>">
                        <span class="nav-item-icon"><?php echo $item['glyph']; ?></span>
                        <span><?php echo $item['label']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <div class="nav-group">
            <div class="nav-group-title">Account</div>
            <a href="<?php echo APP_URL; ?>/index.php" class="nav-item">
                <span class="nav-item-icon">↗</span><span>View site</span>
            </a>
            <a href="<?php echo AUTH_URL; ?>/logout.php" class="nav-item">
                <span class="nav-item-icon">⏻</span><span>Sign out</span>
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="user-avatar"><?php echo strtoupper(substr($adminName, 0, 1)); ?></div>
        <div class="user-info">
            <div class="user-email"><?php echo htmlspecialchars($adminEmail); ?></div>
            <div class="user-role">Administrator</div>
        </div>
    </div>
</aside>
