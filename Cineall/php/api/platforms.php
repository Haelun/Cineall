<?php

require_once '../../config/config.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

$db = Database::getInstance();
$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            getPlatformsList($db);
            break;

        case 'stats':
            getPlatformStats($db);
            break;

        default:
            sendError('Invalid action', 400);
    }
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}

function getPlatformsList($db) {
    $sql = "SELECT * FROM platforms ORDER BY name";
    $platforms = $db->query($sql);
    sendSuccess(['platforms' => $platforms]);
}

function getPlatformStats($db) {
    $sql = "SELECT
                p.*,
                COUNT(DISTINCT a.movie_id) as total_titles,
                SUM(CASE WHEN a.kind = 'subscription' THEN 1 ELSE 0 END) as subscription_count,
                SUM(CASE WHEN a.kind = 'rent' THEN 1 ELSE 0 END) as rent_count,
                SUM(CASE WHEN a.kind = 'buy' THEN 1 ELSE 0 END) as buy_count
            FROM platforms p
            LEFT JOIN availability a ON p.id = a.platform_id
            GROUP BY p.id
            ORDER BY total_titles DESC, p.name";

    $stats = $db->query($sql);

    foreach ($stats as &$platform) {
        $sampleSql = "SELECT m.id, m.movie_key, m.title, m.scheme_color_1, m.scheme_color_2
                      FROM movies m
                      JOIN availability a ON m.id = a.movie_id
                      WHERE a.platform_id = ?
                      LIMIT 4";

        $platform['sample_movies'] = $db->query($sampleSql, [$platform['id']]);
    }

    sendSuccess(['platforms' => $stats]);
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
