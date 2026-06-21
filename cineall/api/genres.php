<?php


require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

json_api_init();
header('Content-Type: application/json');

$db = Database::getInstance();
$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            getGenresList($db);
            break;

        default:
            sendError('Invalid action', 400);
    }
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}

function getGenresList($db) {
    $sql = "SELECT
                g.*,
                COUNT(mg.movie_id) as movie_count
            FROM genres g
            LEFT JOIN movie_genres mg ON g.id = mg.genre_id
            GROUP BY g.id
            ORDER BY g.name";

    $genres = $db->query($sql);
    sendSuccess(['genres' => $genres]);
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
