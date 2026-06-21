<?php

/**
 * Curator — Movies API (unified schema).
 * Returns movies with movie_id = movie_key so the existing JS keeps working,
 * and reads genres/cast/availability from the normalized tables.
 */
require_once __DIR__ . '/../bootstrap.php';
json_api_init();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

function cur_movie_genres($db, $movieId) {
    $s = $db->prepare("
        SELECT g.name FROM genres g
        JOIN movie_genres mg ON g.id = mg.genre_id
        WHERE mg.movie_id = ? ORDER BY g.name
    ");
    $s->execute([$movieId]);
    return array_column($s->fetchAll(), 'name');
}
function cur_movie_cast($db, $movieId) {
    $s = $db->prepare("SELECT name FROM cast_members WHERE movie_id = ? ORDER BY display_order ASC");
    $s->execute([$movieId]);
    return array_column($s->fetchAll(), 'name');
}
function cur_movie_availability($db, $movieId) {
    $s = $db->prepare("
        SELECT a.*, p.platform_key, p.name AS platform_name, p.hue, p.abbr
        FROM availability a
        JOIN platforms p ON a.platform_id = p.id
        WHERE a.movie_id = ? ORDER BY a.kind ASC
    ");
    $s->execute([$movieId]);
    return $s->fetchAll();
}
/** Add the key alias + enrichments the curator UI expects. */
function cur_shape_movie($db, $m) {
    $intId = (int)$m['id'];
    $m['movie_id']    = $m['movie_key'];        // string key for the JS
    $m['genres']      = cur_movie_genres($db, $intId);
    $m['cast']        = cur_movie_cast($db, $intId);
    $m['availability']= cur_movie_availability($db, $intId);
    return $m;
}

try {
    $db = getDB();

    switch ($action) {
        case 'list':
            $search = $_GET['search'] ?? '';
            $limit  = (int)($_GET['limit'] ?? 100);
            $offset = (int)($_GET['offset'] ?? 0);

            $sql = "SELECT * FROM movies WHERE 1=1";
            $params = [];
            if ($search) {
                $sql .= " AND (title LIKE ? OR director LIKE ?)";
                $params[] = "%$search%"; $params[] = "%$search%";
            }
            $sql .= " ORDER BY year DESC, title ASC LIMIT ? OFFSET ?";
            $params[] = $limit; $params[] = $offset;

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $movies = $stmt->fetchAll();
            foreach ($movies as &$m) { $m = cur_shape_movie($db, $m); }
            jsonResponse(['success' => true, 'data' => $movies]);
            break;

        case 'get':
            $key = $_GET['id'] ?? '';
            if (!$key) jsonResponse(['success' => false, 'error' => 'Movie ID required'], 400);
            $stmt = $db->prepare("SELECT * FROM movies WHERE movie_key = ?");
            $stmt->execute([$key]);
            $movie = $stmt->fetch();
            if (!$movie) jsonResponse(['success' => false, 'error' => 'Movie not found'], 404);
            jsonResponse(['success' => true, 'data' => cur_shape_movie($db, $movie)]);
            break;

        case 'update':
            if ($method !== 'POST') jsonResponse(['success' => false, 'error' => 'POST method required'], 405);
            $data = json_decode(file_get_contents('php://input'), true) ?: [];
            $key = $data['movie_id'] ?? '';
            if (!$key) jsonResponse(['success' => false, 'error' => 'Movie ID required'], 400);

            $allowed = ['title','year','runtime','rating','director','synopsis','tagline','critic_score','audience_score'];
            $fields = []; $params = [];
            foreach ($allowed as $f) {
                if (isset($data[$f])) { $fields[] = "$f = ?"; $params[] = $data[$f]; }
            }
            if (!$fields) jsonResponse(['success' => false, 'error' => 'No valid fields to update'], 400);
            $params[] = $key;
            $db->prepare("UPDATE movies SET " . implode(', ', $fields) . " WHERE movie_key = ?")->execute($params);
            logActivity('Updated movie', 'movie', $key, $data);
            jsonResponse(['success' => true, 'message' => 'Movie updated successfully']);
            break;

        default:
            jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
