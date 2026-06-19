<?php
/**
 * Curator — Reviews API (unified schema).
 * The unified reviews table stores user_name/snippet/status(pending,approved,
 * rejected)+is_flagged. The curator UI works with username/review_text and a
 * derived status of pending/approved/flagged — this API maps between them.
 */
require_once __DIR__ . '/../bootstrap.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

/** Derived curator-facing status from unified columns. */
function cur_review_status($row) {
    if ((int)$row['is_flagged'] === 1) return 'flagged';
    return $row['status'] === 'rejected' ? 'flagged' : $row['status'];
}

try {
    $db = getDB();

    switch ($action) {
        case 'list':
            $status = $_GET['status'] ?? 'all';
            $limit  = (int)($_GET['limit'] ?? 50);
            $offset = (int)($_GET['offset'] ?? 0);

            $sql = "
                SELECT r.id, r.review_key, m.movie_key AS movie_id, m.title AS movie_title,
                       r.user_name AS username, r.rating, r.snippet AS review_text,
                       r.status, r.is_flagged, r.created_at, r.updated_at
                FROM reviews r
                JOIN movies m ON r.movie_id = m.id
                WHERE 1=1
            ";
            $params = [];
            if ($status === 'flagged') {
                $sql .= " AND (r.is_flagged = 1 OR r.status = 'rejected')";
            } elseif ($status !== 'all') {
                $sql .= " AND r.status = ? AND r.is_flagged = 0";
                $params[] = $status;
            }
            $sql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit; $params[] = $offset;

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $reviews = $stmt->fetchAll();
            foreach ($reviews as &$r) { $r['status'] = cur_review_status($r); }
            jsonResponse(['success' => true, 'data' => $reviews]);
            break;

        case 'update_status':
            if ($method !== 'POST') jsonResponse(['success' => false, 'error' => 'POST method required'], 405);
            $data = json_decode(file_get_contents('php://input'), true) ?: [];
            $reviewId = $data['review_id'] ?? '';
            $status   = $data['status'] ?? '';
            if (!$reviewId || !in_array($status, ['approved', 'flagged', 'pending'], true)) {
                jsonResponse(['success' => false, 'error' => 'Invalid review ID or status'], 400);
            }
            // Map curator status onto unified columns
            if ($status === 'flagged') {
                $db->prepare("UPDATE reviews SET is_flagged = 1 WHERE id = ?")->execute([$reviewId]);
            } elseif ($status === 'approved') {
                $db->prepare("UPDATE reviews SET status = 'approved', is_flagged = 0 WHERE id = ?")->execute([$reviewId]);
            } else { // pending
                $db->prepare("UPDATE reviews SET status = 'pending', is_flagged = 0 WHERE id = ?")->execute([$reviewId]);
            }
            logActivity('Updated review status', 'review', $reviewId, ['status' => $status]);
            jsonResponse(['success' => true, 'message' => 'Review status updated successfully']);
            break;

        default:
            jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
