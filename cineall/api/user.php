<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
cineall_session_start();

$db = Database::getInstance();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

$userId = currentUserId();

if ($userId === null) {
    sendError('You must be signed in to do that.', 401);
}

try {
    switch ($action) {
        case 'get_watchlist':
            getWatchlist($db, $userId);
            break;

        case 'add_to_watchlist':
            addToWatchlist($db, $userId);
            break;

        case 'remove_from_watchlist':
            removeFromWatchlist($db, $userId);
            break;

        case 'get_subscriptions':
            getSubscriptions($db, $userId);
            break;

        case 'toggle_subscription':
            toggleSubscription($db, $userId);
            break;

        case 'get_preferences':
            getPreferences($db, $userId);
            break;

        case 'update_preferences':
            updatePreferences($db, $userId);
            break;

        default:
            sendError('Invalid action', 400);
    }
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}

function getWatchlist($db, $userId) {
    $sql = "SELECT m.*, w.added_at,
            GROUP_CONCAT(DISTINCT g.name) as genres
            FROM watchlist w
            JOIN movies m ON w.movie_id = m.id
            LEFT JOIN movie_genres mg ON m.id = mg.movie_id
            LEFT JOIN genres g ON mg.genre_id = g.id
            WHERE w.user_id = ?
            GROUP BY m.id
            ORDER BY w.added_at DESC";

    $watchlist = $db->query($sql, [$userId]);

    foreach ($watchlist as &$movie) {
        $movie['genres'] = $movie['genres'] ? explode(',', $movie['genres']) : [];

        $availSql = "SELECT a.*, p.platform_key, p.name as platform_name, p.hue, p.abbr
                     FROM availability a
                     JOIN platforms p ON a.platform_id = p.id
                     WHERE a.movie_id = ?";
        $movie['availability'] = $db->query($availSql, [$movie['id']]);
    }

    sendSuccess(['watchlist' => $watchlist]);
}

function addToWatchlist($db, $userId) {
    $movieId = $_POST['movie_id'] ?? 0;

    if (empty($movieId)) {
        sendError('Movie ID required', 400);
    }

    $checkSql = "SELECT id FROM watchlist WHERE user_id = ? AND movie_id = ?";
    $exists = $db->queryOne($checkSql, [$userId, $movieId]);

    if ($exists) {
        sendSuccess(['message' => 'Already in watchlist']);
        return;
    }

    $sql = "INSERT INTO watchlist (user_id, movie_id) VALUES (?, ?)";
    $result = $db->execute($sql, [$userId, $movieId]);

    if ($result) {
        sendSuccess(['message' => 'Added to watchlist']);
    } else {
        sendError('Failed to add to watchlist', 500);
    }
}

function removeFromWatchlist($db, $userId) {
    $movieId = $_POST['movie_id'] ?? $_GET['movie_id'] ?? 0;

    if (empty($movieId)) {
        sendError('Movie ID required', 400);
    }

    $sql = "DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?";
    $result = $db->execute($sql, [$userId, $movieId]);

    sendSuccess(['message' => 'Removed from watchlist']);
}

function getSubscriptions($db, $userId) {
    $sql = "SELECT p.*, us.created_at as subscribed_at
            FROM platforms p
            LEFT JOIN user_subscriptions us ON p.id = us.platform_id AND us.user_id = ?
            ORDER BY p.name";

    $platforms = $db->query($sql, [$userId]);

    foreach ($platforms as &$platform) {
        $platform['subscribed'] = !empty($platform['subscribed_at']);
    }

    sendSuccess(['subscriptions' => $platforms]);
}

function toggleSubscription($db, $userId) {
    $platformId = $_POST['platform_id'] ?? 0;

    if (empty($platformId)) {
        sendError('Platform ID required', 400);
    }

    $checkSql = "SELECT id FROM user_subscriptions WHERE user_id = ? AND platform_id = ?";
    $exists = $db->queryOne($checkSql, [$userId, $platformId]);

    if ($exists) {
        $sql = "DELETE FROM user_subscriptions WHERE user_id = ? AND platform_id = ?";
        $db->execute($sql, [$userId, $platformId]);
        sendSuccess(['message' => 'Subscription removed', 'subscribed' => false]);
    } else {
        $sql = "INSERT INTO user_subscriptions (user_id, platform_id) VALUES (?, ?)";
        $db->execute($sql, [$userId, $platformId]);
        sendSuccess(['message' => 'Subscription added', 'subscribed' => true]);
    }
}

function getPreferences($db, $userId) {
    $sql = "SELECT * FROM user_preferences WHERE user_id = ?";
    $prefs = $db->queryOne($sql, [$userId]);

    if (!$prefs) {
        $insertSql = "INSERT INTO user_preferences (user_id) VALUES (?)";
        $db->execute($insertSql, [$userId]);
        $prefs = $db->queryOne($sql, [$userId]);
    }

    sendSuccess(['preferences' => $prefs]);
}

function updatePreferences($db, $userId) {
    $notify_leaving = isset($_POST['notify_leaving']) ? (int)$_POST['notify_leaving'] : 1;
    $email_digest = isset($_POST['email_digest']) ? (int)$_POST['email_digest'] : 1;
    $critic_score_first = isset($_POST['critic_score_first']) ? (int)$_POST['critic_score_first'] : 0;
    $hide_watched = isset($_POST['hide_watched']) ? (int)$_POST['hide_watched'] : 1;
    $surface_festival = isset($_POST['surface_festival']) ? (int)$_POST['surface_festival'] : 0;
    $use_audience_score = isset($_POST['use_audience_score']) ? (int)$_POST['use_audience_score'] : 0;

    $sql = "UPDATE user_preferences SET
            notify_leaving = ?,
            email_digest = ?,
            critic_score_first = ?,
            hide_watched = ?,
            surface_festival = ?,
            use_audience_score = ?
            WHERE user_id = ?";

    $db->execute($sql, [
        $notify_leaving,
        $email_digest,
        $critic_score_first,
        $hide_watched,
        $surface_festival,
        $use_audience_score,
        $userId
    ]);

    sendSuccess(['message' => 'Preferences updated']);
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
