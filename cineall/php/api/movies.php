<?php

require_once '../../config/config.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

$db = Database::getInstance();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            getMoviesList($db);
            break;

        case 'detail':
            getMovieDetail($db);
            break;

        case 'search':
            searchMovies($db);
            break;

        case 'by_genre':
            getMoviesByGenre($db);
            break;

        case 'home_rows':
            getHomeRows($db);
            break;

        default:
            sendError('Invalid action', 400);
    }
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}

function getMoviesList($db) {
    $genreFilter = $_GET['genres'] ?? '';
    $platformFilter = $_GET['platforms'] ?? '';
    $yearMin = intval($_GET['year_min'] ?? 0);
    $ratingMin = intval($_GET['rating_min'] ?? 0);
    $kindFilter = $_GET['kind'] ?? '';
    $sort = $_GET['sort'] ?? 'relevance';
    $limit = intval($_GET['limit'] ?? ITEMS_PER_PAGE);
    $offset = intval($_GET['offset'] ?? 0);

    $sql = "SELECT DISTINCT m.*,
            GROUP_CONCAT(DISTINCT g.name ORDER BY g.name) as genres,
            GROUP_CONCAT(DISTINCT c.name ORDER BY c.display_order) as cast_members
            FROM movies m
            LEFT JOIN movie_genres mg ON m.id = mg.movie_id
            LEFT JOIN genres g ON mg.genre_id = g.id
            LEFT JOIN cast_members c ON m.id = c.movie_id
            WHERE 1=1";

    $params = [];

    if (!empty($genreFilter)) {
        $genres = explode(',', $genreFilter);
        $placeholders = str_repeat('?,', count($genres) - 1) . '?';
        $sql .= " AND m.id IN (
            SELECT mg2.movie_id FROM movie_genres mg2
            JOIN genres g2 ON mg2.genre_id = g2.id
            WHERE g2.name IN ($placeholders)
        )";
        $params = array_merge($params, $genres);
    }

    if (!empty($platformFilter)) {
        $platforms = explode(',', $platformFilter);
        $placeholders = str_repeat('?,', count($platforms) - 1) . '?';
        $sql .= " AND m.id IN (
            SELECT a.movie_id FROM availability a
            JOIN platforms p ON a.platform_id = p.id
            WHERE p.platform_key IN ($placeholders)
        )";
        $params = array_merge($params, $platforms);
    }

    if ($yearMin > 0) {
        $sql .= " AND m.year >= ?";
        $params[] = $yearMin;
    }

    if ($ratingMin > 0) {
        $sql .= " AND m.critic_score >= ?";
        $params[] = $ratingMin;
    }

    if (!empty($kindFilter)) {
        $kinds = explode(',', $kindFilter);
        $placeholders = str_repeat('?,', count($kinds) - 1) . '?';
        $sql .= " AND m.id IN (
            SELECT movie_id FROM availability WHERE kind IN ($placeholders)
        )";
        $params = array_merge($params, $kinds);
    }

    $sql .= " GROUP BY m.id";

    switch ($sort) {
        case 'rating':
            $sql .= " ORDER BY m.critic_score DESC";
            break;
        case 'year':
            $sql .= " ORDER BY m.year DESC";
            break;
        case 'title':
            $sql .= " ORDER BY m.title ASC";
            break;
        default:
            $sql .= " ORDER BY m.id DESC";
    }

    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $movies = $db->query($sql, $params);

    foreach ($movies as &$movie) {
        $movie['availability'] = getMovieAvailability($db, $movie['id']);
        $movie['cast_members'] = $movie['cast_members'] ? explode(',', $movie['cast_members']) : [];
        $movie['genres'] = $movie['genres'] ? explode(',', $movie['genres']) : [];
    }

    sendSuccess(['movies' => $movies]);
}

function getMovieDetail($db) {
    $id = $_GET['id'] ?? '';
    $key = $_GET['key'] ?? '';

    if (empty($id) && empty($key)) {
        sendError('Movie ID or key required', 400);
    }

    $sql = "SELECT m.*,
            GROUP_CONCAT(DISTINCT g.name ORDER BY g.name) as genres,
            GROUP_CONCAT(DISTINCT c.name ORDER BY c.display_order) as cast_members
            FROM movies m
            LEFT JOIN movie_genres mg ON m.id = mg.movie_id
            LEFT JOIN genres g ON mg.genre_id = g.id
            LEFT JOIN cast_members c ON m.id = c.movie_id
            WHERE ";

    if (!empty($id)) {
        $sql .= "m.id = ?";
        $param = $id;
    } else {
        $sql .= "m.movie_key = ?";
        $param = $key;
    }

    $sql .= " GROUP BY m.id";

    $movie = $db->queryOne($sql, [$param]);

    if (!$movie) {
        sendError('Movie not found', 404);
    }

    $movie['availability'] = getMovieAvailability($db, $movie['id']);
    $movie['cast_members'] = $movie['cast_members'] ? explode(',', $movie['cast_members']) : [];
    $movie['genres'] = $movie['genres'] ? explode(',', $movie['genres']) : [];

    $relatedSql = "SELECT DISTINCT m2.*,
                   GROUP_CONCAT(DISTINCT g2.name) as genres
                   FROM movies m2
                   JOIN movie_genres mg2 ON m2.id = mg2.movie_id
                   JOIN genres g2 ON mg2.genre_id = g2.id
                   WHERE mg2.genre_id IN (
                       SELECT genre_id FROM movie_genres WHERE movie_id = ?
                   ) AND m2.id != ?
                   GROUP BY m2.id
                   LIMIT 5";

    $related = $db->query($relatedSql, [$movie['id'], $movie['id']]);
    foreach ($related as &$rel) {
        $rel['genres'] = $rel['genres'] ? explode(',', $rel['genres']) : [];
    }

    $movie['related'] = $related;

    sendSuccess($movie);
}

function searchMovies($db) {
    $query = $_GET['q'] ?? '';
    $limit = intval($_GET['limit'] ?? 10);

    if (empty($query)) {
        sendSuccess(['movies' => []]);
        return;
    }

    $searchTerm = "%$query%";

    $sql = "SELECT DISTINCT m.*,
            GROUP_CONCAT(DISTINCT g.name) as genres
            FROM movies m
            LEFT JOIN movie_genres mg ON m.id = mg.movie_id
            LEFT JOIN genres g ON mg.genre_id = g.id
            LEFT JOIN cast_members c ON m.id = c.movie_id
            WHERE m.title LIKE ?
               OR m.director LIKE ?
               OR c.name LIKE ?
               OR g.name LIKE ?
            GROUP BY m.id
            LIMIT ?";

    $movies = $db->query($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);

    foreach ($movies as &$movie) {
        $movie['availability'] = getMovieAvailability($db, $movie['id']);
        $movie['genres'] = $movie['genres'] ? explode(',', $movie['genres']) : [];
    }

    sendSuccess(['movies' => $movies]);
}

function getMoviesByGenre($db) {
    $genre = $_GET['genre'] ?? '';

    if (empty($genre)) {
        sendError('Genre required', 400);
    }

    $sql = "SELECT DISTINCT m.*,
            GROUP_CONCAT(DISTINCT g.name) as genres
            FROM movies m
            JOIN movie_genres mg ON m.id = mg.movie_id
            JOIN genres g ON mg.genre_id = g.id
            WHERE g.name = ?
            GROUP BY m.id";

    $movies = $db->query($sql, [$genre]);

    foreach ($movies as &$movie) {
        $movie['genres'] = $movie['genres'] ? explode(',', $movie['genres']) : [];
    }

    sendSuccess(['movies' => $movies]);
}

function getHomeRows($db) {
    $sql = "SELECT * FROM home_rows WHERE is_active = 1 ORDER BY display_order";
    $rows = $db->query($sql);

    foreach ($rows as &$row) {
        $moviesSql = "SELECT m.*, GROUP_CONCAT(DISTINCT g.name) as genres
                      FROM home_row_movies hrm
                      JOIN movies m ON hrm.movie_id = m.id
                      LEFT JOIN movie_genres mg ON m.id = mg.movie_id
                      LEFT JOIN genres g ON mg.genre_id = g.id
                      WHERE hrm.row_id = ?
                      GROUP BY m.id
                      ORDER BY hrm.display_order";

        $movies = $db->query($moviesSql, [$row['id']]);

        foreach ($movies as &$movie) {
            $movie['availability'] = getMovieAvailability($db, $movie['id']);
            $movie['genres'] = $movie['genres'] ? explode(',', $movie['genres']) : [];
        }

        $row['movies'] = $movies;
    }

    sendSuccess(['rows' => $rows]);
}

function getMovieAvailability($db, $movieId) {
    $sql = "SELECT a.*, p.platform_key, p.name as platform_name, p.hue, p.abbr
            FROM availability a
            JOIN platforms p ON a.platform_id = p.id
            WHERE a.movie_id = ?
            ORDER BY
                CASE a.kind
                    WHEN 'subscription' THEN 1
                    WHEN 'rent' THEN 2
                    WHEN 'buy' THEN 3
                END";

    return $db->query($sql, [$movieId]);
}

function sendSuccess($data) {
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    exit;
}

function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
    exit;
}
