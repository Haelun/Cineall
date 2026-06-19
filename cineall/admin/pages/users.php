<?php
/**
 * CineAll Admin - Users Page
 *
 * Manage user accounts
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$page_title = 'Users';
include __DIR__ . '/../includes/header.php';

// Get users from database
$db = getDB();
$stmt = $db->query("
    SELECT u.*,
           u.id AS user_id,
           u.created_at AS joined_date,
           (u.status = 'banned') AS is_banned,
           (SELECT COUNT(*) FROM user_subscriptions s WHERE s.user_id = u.id) AS services_count,
           (SELECT COUNT(*) FROM watchlist w WHERE w.user_id = u.id) AS watchlist_count
    FROM users u ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

// Get user counts
$total_users = count($users);
$banned_count = count(array_filter($users, function($u) { return $u['is_banned']; }));
?>

<!-- Page Header -->
<div class="page-head">
    <div class="page-head-info">
        <div class="page-kicker"><?php echo $total_users; ?> accounts · <?php echo $banned_count; ?> banned</div>
        <h1 class="page-title">Users</h1>
        <p class="page-subtitle">Audience management. View watchlists, change plans, ban abusive accounts.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-ghost">↓ Export CSV</button>
        <button class="btn btn-primary">+ Invite admin</button>
    </div>
</div>

<!-- Users Table -->
<div class="data-table">
    <!-- Table Header -->
    <div class="table-header" style="grid-template-columns: 2fr 2fr 1fr 100px 80px 80px 100px 100px;">
        <div>Name</div>
        <div>Email</div>
        <div>Joined</div>
        <div>Plan</div>
        <div>Services</div>
        <div>Watchlist</div>
        <div>Status</div>
        <div></div>
    </div>

    <!-- Table Rows -->
    <?php foreach ($users as $user): ?>
        <?php
        // Generate avatar color based on user ID
        $hue = ((int)$user['id'] * 47) % 360;
        $avatarBg = "oklch(0.45 0.10 $hue)";

        // Determine badge type
        $statusBadge = 'good';
        if ($user['status'] === 'banned') $statusBadge = 'bad';
        elseif ($user['status'] === 'flagged') $statusBadge = 'warn';
        elseif ($user['status'] === 'idle') $statusBadge = 'neutral';

        $planBadge = $user['plan'] === 'Premium' ? 'accent' : 'neutral';
        ?>

        <div class="table-row" style="grid-template-columns: 2fr 2fr 1fr 100px 80px 80px 100px 100px; opacity: <?php echo $user['is_banned'] ? '0.5' : '1'; ?>;">
            <!-- Name with Avatar -->
            <div class="flex items-center gap-12">
                <div class="user-avatar-sm" style="background: <?php echo $avatarBg; ?>;">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <div class="text-serif"><?php echo htmlspecialchars($user['name']); ?></div>
            </div>

            <!-- Email -->
            <div class="text-mono" style="font-size: 11px; color: var(--amuted);">
                <?php echo htmlspecialchars($user['email']); ?>
            </div>

            <!-- Joined Date -->
            <div class="text-mono" style="font-size: 11px;">
                <?php echo date('Y-m-d', strtotime($user['joined_date'])); ?>
            </div>

            <!-- Plan -->
            <div>
                <span class="badge badge-<?php echo $planBadge; ?>"><?php echo $user['plan']; ?></span>
            </div>

            <!-- Services Count -->
            <div class="text-mono" style="font-size: 12px;">
                <?php echo $user['services_count']; ?>
            </div>

            <!-- Watchlist Count -->
            <div class="text-mono" style="font-size: 12px;">
                <?php echo $user['watchlist_count']; ?>
            </div>

            <!-- Status -->
            <div>
                <span class="badge badge-<?php echo $statusBadge; ?>"><?php echo ucfirst($user['status']); ?></span>
            </div>

            <!-- Actions -->
            <div class="flex gap-4">
                <button class="btn btn-sm btn-ghost" onclick="viewUser('<?php echo $user['user_id']; ?>')">View</button>
                <button class="btn btn-sm btn-<?php echo $user['is_banned'] ? 'ghost' : 'danger'; ?>"
                        onclick="toggleBan('<?php echo $user['user_id']; ?>', <?php echo $user['is_banned'] ? 'false' : 'true'; ?>)">
                    <?php echo $user['is_banned'] ? 'Unban' : 'Ban'; ?>
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
// View user details
function viewUser(userId) {
    Modal.create('User Details', `
        <p>Viewing details for user: <strong>${userId}</strong></p>
        <p>This is where you would show detailed user information.</p>
    `, [
        { text: 'Close', class: 'btn btn-ghost' }
    ]);
}

// Toggle ban status
function toggleBan(userId, shouldBan) {
    const action = shouldBan ? 'ban' : 'unban';
    const message = shouldBan
        ? `Are you sure you want to ban this user? They will lose access to the platform.`
        : `Unban this user and restore their access?`;

    Modal.confirm(
        `${action.charAt(0).toUpperCase() + action.slice(1)} User`,
        message,
        function() {
            // In a real application, this would make an API call
            // API.post('users.php', { user_id: userId, action: action })
            //     .then(() => {
            //         Toast.success(`User ${action}ned successfully`);
            //         location.reload();
            //     });

            Toast.success(`User ${action}ned successfully`);
            setTimeout(() => location.reload(), 1000);
        }
    );
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
