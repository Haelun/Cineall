<?php
/**
 * Curator — Homepage Management API (unified schema).
 * Preserves the original JS contract (string row_id / movie_id keys,
 * enabled, order_index, movie_ids) while reading/writing the normalized
 * home_rows / home_row_movies / homepage_settings tables.
 */
require_once __DIR__ . '/../bootstrap.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

/** row_key -> home_rows.id */
function row_id_of($db, $rowKey) {
    $s = $db->prepare("SELECT id FROM home_rows WHERE row_key = ?");
    $s->execute([$rowKey]);
    $id = $s->fetchColumn();
    return $id ? (int)$id : null;
}
/** movie_key -> movies.id */
function movie_id_of($db, $movieKey) {
    $s = $db->prepare("SELECT id FROM movies WHERE movie_key = ?");
    $s->execute([$movieKey]);
    $id = $s->fetchColumn();
    return $id ? (int)$id : null;
}

try {
    $db = getDB();

    switch ($action) {

        case 'get_settings':
            $stmt = $db->query("SELECT * FROM homepage_settings ORDER BY id DESC LIMIT 1");
            $settings = $stmt->fetch();
            if (!$settings) {
                $db->prepare("INSERT INTO homepage_settings (hero_movie_id, hero_tagline, is_published) VALUES (?, ?, 0)")
                   ->execute(['galactic-convergence', 'The sky was only the beginning.']);
                $settings = $db->query("SELECT * FROM homepage_settings ORDER BY id DESC LIMIT 1")->fetch();
            }
            jsonResponse(['success' => true, 'data' => $settings]);
            break;

        case 'update_hero':
            if ($method !== 'POST') jsonResponse(['success' => false, 'error' => 'POST method required'], 405);
            $data = json_decode(file_get_contents('php://input'), true) ?: [];
            $heroMovieId = $data['hero_movie_id'] ?? '';
            $heroTagline = $data['hero_tagline'] ?? '';
            $stmt = $db->prepare("
                UPDATE homepage_settings
                SET hero_movie_id = ?, hero_tagline = ?, is_published = 0, updated_by = ?
                WHERE id = (SELECT id FROM (SELECT id FROM homepage_settings ORDER BY id DESC LIMIT 1) t)
            ");
            $stmt->execute([$heroMovieId, $heroTagline, curatorName()]);
            logActivity('Updated homepage hero', 'homepage', null, $data);
            jsonResponse(['success' => true, 'message' => 'Hero updated successfully']);
            break;

        case 'get_rows':
            $rows = $db->query("
                SELECT id, row_key AS row_id, title, kicker, is_active AS enabled, display_order AS order_index
                FROM home_rows ORDER BY display_order ASC
            ")->fetchAll();
            foreach ($rows as &$row) {
                $s = $db->prepare("
                    SELECT m.movie_key
                    FROM home_row_movies hrm
                    JOIN movies m ON hrm.movie_id = m.id
                    WHERE hrm.row_id = ?
                    ORDER BY hrm.display_order ASC
                ");
                $s->execute([$row['id']]);
                $row['movie_ids'] = array_column($s->fetchAll(), 'movie_key');
            }
            jsonResponse(['success' => true, 'data' => $rows]);
            break;

        case 'create_row':
            if ($method !== 'POST') jsonResponse(['success' => false, 'error' => 'POST method required'], 405);
            $data = json_decode(file_get_contents('php://input'), true) ?: [];
            $rowKey = $data['row_id'] ?? ('row-' . substr(uniqid(), -6));
            $title  = $data['title'] ?? 'Untitled row';
            $kicker = $data['kicker'] ?? 'New section';
            $next = (int)$db->query("SELECT COALESCE(MAX(display_order),0)+1 FROM home_rows")->fetchColumn();
            $db->prepare("INSERT INTO home_rows (row_key, title, kicker, is_active, display_order) VALUES (?, ?, ?, 1, ?)")
               ->execute([$rowKey, $title, $kicker, $next]);
            logActivity('Created homepage row', 'homepage_row', $rowKey);
            jsonResponse(['success' => true, 'message' => 'Row created successfully', 'data' => ['row_id' => $rowKey]]);
            break;

        case 'update_row':
            if ($method !== 'POST') jsonResponse(['success' => false, 'error' => 'POST method required'], 405);
            $data = json_decode(file_get_contents('php://input'), true) ?: [];
            $rowKey = $data['row_id'] ?? '';
            if (!$rowKey) jsonResponse(['success' => false, 'error' => 'Row ID required'], 400);
            $fields = []; $params = [];
            if (isset($data['title']))   { $fields[] = "title = ?";       $params[] = $data['title']; }
            if (isset($data['kicker']))  { $fields[] = "kicker = ?";      $params[] = $data['kicker']; }
            if (isset($data['enabled'])) { $fields[] = "is_active = ?";   $params[] = $data['enabled'] ? 1 : 0; }
            if (!$fields) jsonResponse(['success' => false, 'error' => 'No fields to update'], 400);
            $params[] = $rowKey;
            $db->prepare("UPDATE home_rows SET " . implode(', ', $fields) . " WHERE row_key = ?")->execute($params);
            logActivity('Updated homepage row', 'homepage_row', $rowKey, $data);
            jsonResponse(['success' => true, 'message' => 'Row updated successfully']);
            break;

        case 'delete_row':
            if ($method !== 'POST') jsonResponse(['success' => false, 'error' => 'POST method required'], 405);
            $data = json_decode(file_get_contents('php://input'), true) ?: [];
            $rowKey = $data['row_id'] ?? '';
            if (!$rowKey) jsonResponse(['success' => false, 'error' => 'Row ID required'], 400);
            // home_row_movies cascade-deletes via FK
            $db->prepare("DELETE FROM home_rows WHERE row_key = ?")->execute([$rowKey]);
            logActivity('Deleted homepage row', 'homepage_row', $rowKey);
            jsonResponse(['success' => true, 'message' => 'Row deleted successfully']);
            break;

        case 'reorder_rows':
            if ($method !== 'POST') jsonResponse(['success' => false, 'error' => 'POST method required'], 405);
            $data = json_decode(file_get_contents('php://input'), true) ?: [];
            $rowOrder = $data['row_order'] ?? [];
            if (!$rowOrder) jsonResponse(['success' => false, 'error' => 'Row order required'], 400);
            $db->beginTransaction();
            try {
                $stmt = $db->prepare("UPDATE home_rows SET display_order = ? WHERE row_key = ?");
                foreach ($rowOrder as $i => $rowKey) { $stmt->execute([$i + 1, $rowKey]); }
                $db->commit();
                logActivity('Reordered homepage rows', 'homepage', null, ['row_order' => $rowOrder]);
                jsonResponse(['success' => true, 'message' => 'Rows reordered successfully']);
            } catch (Exception $e) { $db->rollBack(); throw $e; }
            break;

        case 'update_row_movies':
            if ($method !== 'POST') jsonResponse(['success' => false, 'error' => 'POST method required'], 405);
            $data = json_decode(file_get_contents('php://input'), true) ?: [];
            $rowKey   = $data['row_id'] ?? '';
            $movieKeys = $data['movie_ids'] ?? [];
            if (!$rowKey) jsonResponse(['success' => false, 'error' => 'Row ID required'], 400);
            $rowId = row_id_of($db, $rowKey);
            if (!$rowId) jsonResponse(['success' => false, 'error' => 'Row not found'], 404);
            $db->beginTransaction();
            try {
                $db->prepare("DELETE FROM home_row_movies WHERE row_id = ?")->execute([$rowId]);
                if ($movieKeys) {
                    $ins = $db->prepare("INSERT INTO home_row_movies (row_id, movie_id, display_order) VALUES (?, ?, ?)");
                    foreach ($movieKeys as $i => $mk) {
                        $mid = movie_id_of($db, $mk);
                        if ($mid) $ins->execute([$rowId, $mid, $i + 1]);
                    }
                }
                $db->commit();
                logActivity('Updated row movies', 'homepage_row', $rowKey, ['movie_count' => count($movieKeys)]);
                jsonResponse(['success' => true, 'message' => 'Row movies updated successfully']);
            } catch (Exception $e) { $db->rollBack(); throw $e; }
            break;

        case 'publish':
            if ($method !== 'POST') jsonResponse(['success' => false, 'error' => 'POST method required'], 405);
            $db->query("
                UPDATE homepage_settings SET is_published = 1
                WHERE id = (SELECT id FROM (SELECT id FROM homepage_settings ORDER BY id DESC LIMIT 1) t)
            ");
            logActivity('Published homepage changes', 'homepage');
            jsonResponse(['success' => true, 'message' => 'Homepage published successfully']);
            break;

        default:
            jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
