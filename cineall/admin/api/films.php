<?php
/**
 * CineAll Admin — Films API (normalized).
 * GET    ?search=&limit=&offset=   list films
 * POST   {title, year, runtime, director, ...}    create film
 * PUT    {movie_id, ...}                           update film (movie_id = movie_key)
 * DELETE ?id=movie_key                             delete film
 */
require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json');

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];

function sendResponse($success, $data = null, $message = '') {
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit;
}

try {
    if ($method === 'GET') {
        $search = $_GET['search'] ?? '';
        $limit  = (int)($_GET['limit'] ?? 50);
        $offset = (int)($_GET['offset'] ?? 0);

        $all = admin_fetch_movies($db, $search);
        $total = count($all);
        $movies = array_slice($all, $offset, $limit);

        // shape availability for each (admin UI expects an availability array)
        foreach ($movies as &$m) {
            $av = $db->prepare("SELECT * FROM availability WHERE movie_id = ?");
            $av->execute([$m['id']]);
            $m['availability'] = $av->fetchAll();
        }
        sendResponse(true, ['movies' => $movies, 'total' => $total, 'limit' => $limit, 'offset' => $offset]);
    }

    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        foreach (['title', 'year', 'runtime', 'director'] as $f) {
            if (empty($data[$f])) sendResponse(false, null, "Missing required field: $f");
        }
        $data['genres'] = $data['genres'] ?? [];
        $data['cast']   = $data['cast'] ?? [];
        $key = admin_save_movie($db, $data, null);
        logActivity($ADMIN_USER['name'] ?? 'Admin', 'created film', $data['title']);
        sendResponse(true, ['movie_id' => $key], 'Film created successfully');
    }

    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        if (empty($data['movie_id'])) sendResponse(false, null, 'Missing movie_id');
        $data['genres'] = $data['genres'] ?? [];
        $data['cast']   = $data['cast'] ?? [];
        admin_save_movie($db, $data, $data['movie_id']);
        logActivity($ADMIN_USER['name'] ?? 'Admin', 'updated film', $data['title'] ?? $data['movie_id']);
        sendResponse(true, null, 'Film updated successfully');
    }

    elseif ($method === 'DELETE') {
        $key = $_GET['id'] ?? '';
        if (empty($key)) sendResponse(false, null, 'Missing movie_id');
        admin_delete_movie($db, $key);
        logActivity($ADMIN_USER['name'] ?? 'Admin', 'deleted film', $key);
        sendResponse(true, null, 'Film deleted successfully');
    }

    else {
        sendResponse(false, null, 'Method not allowed');
    }
} catch (PDOException $e) {
    sendResponse(false, null, 'Database error: ' . $e->getMessage());
}
