<?php
/**
 * CineAll Admin — Platforms.
 * Add, edit, and remove the streaming services films can be assigned to.
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$db = getDB();
$notice = null;

// ---- handle actions ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            $abbr = trim($_POST['abbr'] ?? '');
            $hue  = (int)($_POST['hue'] ?? 0);
            $key  = trim($_POST['platform_key'] ?? '');
            if ($name === '' || $abbr === '') {
                $notice = ['err', 'Name and short code are required.'];
            } else {
                admin_add_platform($db, $name, $abbr, $hue, $key);
                logActivity($ADMIN_USER['name'] ?? 'Admin', 'added platform', $name);
                $notice = ['ok', "Added “{$name}”."];
            }
        } elseif ($action === 'update') {
            admin_update_platform($db, (int)$_POST['id'], trim($_POST['name']), trim($_POST['abbr']), (int)$_POST['hue']);
            logActivity($ADMIN_USER['name'] ?? 'Admin', 'updated platform', trim($_POST['name']));
            $notice = ['ok', 'Platform updated.'];
        } elseif ($action === 'delete') {
            admin_delete_platform($db, (int)$_POST['id']);
            logActivity($ADMIN_USER['name'] ?? 'Admin', 'deleted platform', 'platform #' . (int)$_POST['id']);
            $notice = ['ok', 'Platform removed (its film availability was cleared too).'];
        }
    } catch (PDOException $e) {
        $notice = ['err', 'Error: ' . $e->getMessage()];
    }
}

// counts of films per platform
$platforms = $db->query("
    SELECT p.*, (SELECT COUNT(DISTINCT movie_id) FROM availability a WHERE a.platform_id = p.id) AS film_count
    FROM platforms p ORDER BY p.name
")->fetchAll();

$page_title = 'Platforms';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
    <div>
        <div class="page-head__kicker">Catalogue</div>
        <h1 class="page-head__title">Streaming platforms</h1>
        <p class="page-head__sub">The services films can be assigned to in the film editor.</p>
    </div>
</div>

<?php if ($notice): ?>
    <div class="card" style="border-color: <?php echo $notice[0] === 'ok' ? 'var(--good)' : 'var(--accent)'; ?>; margin-bottom:16px;">
        <?php echo htmlspecialchars($notice[1]); ?>
    </div>
<?php endif; ?>

<div class="card-grid card-grid-2" style="align-items:start;">
    <!-- existing platforms -->
    <div class="card">
        <div class="section-header">§ Current platforms</div>
        <table class="data-table" style="width:100%;">
            <thead>
                <tr><th>Platform</th><th>Code</th><th>Hue</th><th>Films</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($platforms as $p): ?>
                <tr>
                    <td>
                        <form method="POST" style="display:flex;gap:6px;align-items:center;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                            <span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:hsl(<?php echo (int)$p['hue']; ?> 60% 55%);"></span>
                            <input name="name" class="form-control" style="width:130px;" value="<?php echo htmlspecialchars($p['name']); ?>">
                    </td>
                    <td><input name="abbr" class="form-control" style="width:60px;" value="<?php echo htmlspecialchars($p['abbr']); ?>"></td>
                    <td><input name="hue" type="number" min="0" max="360" class="form-control" style="width:70px;" value="<?php echo (int)$p['hue']; ?>"></td>
                    <td class="text-mono"><?php echo (int)$p['film_count']; ?></td>
                    <td style="white-space:nowrap;">
                            <button class="btn btn-sm btn-ghost" type="submit">Save</button>
                        </form>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this platform? It will be unassigned from all films.');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                            <button class="btn btn-sm btn-ghost" type="submit" style="color:var(--accent);">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- add new -->
    <div class="card">
        <div class="section-header">§ Add a platform</div>
        <form method="POST" class="flex flex-col gap-12">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input name="name" class="form-control" placeholder="e.g. Paramount+" required>
            </div>
            <div class="card-grid card-grid-2">
                <div class="form-group">
                    <label class="form-label">Short code</label>
                    <input name="abbr" class="form-control" placeholder="e.g. P+" maxlength="10" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Colour hue (0–360)</label>
                    <input name="hue" type="number" min="0" max="360" class="form-control" value="210">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Key <span class="text-muted">(optional — auto-made from the name)</span></label>
                <input name="platform_key" class="form-control" placeholder="e.g. paramount">
            </div>
            <button class="btn btn-primary" type="submit">+ Add platform</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
