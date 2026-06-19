<?php
/**
 * Admin movie helpers.
 * The admin UI was written for a denormalized movies table (JSON genres/cast,
 * a varchar movie_id key, scheme_color_a/b). The unified database is normalized
 * per the paperwork (movie_key, movie_genres, cast_members, scheme_color_1/2).
 * These helpers bridge the two: they read/write the normalized tables but hand
 * the admin UI the shape it expects.
 */

/**
 * Decorate a raw movies row with the fields the admin UI expects.
 */
function admin_shape_movie($db, array $m) {
    // alias the key + colour scheme to the names the JS/markup uses
    $m['movie_id']       = $m['movie_key'];
    $m['scheme_color_a'] = $m['scheme_color_1'];
    $m['scheme_color_b'] = $m['scheme_color_2'];

    // genres (array of names)
    $genres = $db->prepare("
        SELECT g.name FROM movie_genres mg
        JOIN genres g ON mg.genre_id = g.id
        WHERE mg.movie_id = ? ORDER BY g.name
    ");
    $genres->execute([$m['id']]);
    $m['genres']       = $genres->fetchAll(PDO::FETCH_COLUMN);
    $m['genres_array'] = $m['genres'];

    // cast (array of names)
    $cast = $db->prepare("SELECT name FROM cast_members WHERE movie_id = ? ORDER BY display_order");
    $cast->execute([$m['id']]);
    $m['cast']       = $cast->fetchAll(PDO::FETCH_COLUMN);
    $m['cast_array'] = $m['cast'];

    // platforms this movie is available on (array of platform ids)
    $plat = $db->prepare("SELECT DISTINCT platform_id FROM availability WHERE movie_id = ?");
    $plat->execute([$m['id']]);
    $m['platforms'] = array_map('intval', $plat->fetchAll(PDO::FETCH_COLUMN));

    return $m;
}

/** Fetch all movies (optionally filtered by title/director), in admin shape. */
function admin_fetch_movies($db, $search = '') {
    if ($search !== '') {
        $like = '%' . $search . '%';
        $stmt = $db->prepare("SELECT * FROM movies WHERE title LIKE ? OR director LIKE ? ORDER BY created_at DESC");
        $stmt->execute([$like, $like]);
    } else {
        $stmt = $db->query("SELECT * FROM movies ORDER BY created_at DESC");
    }
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) { $r = admin_shape_movie($db, $r); }
    return $rows;
}

/** Fetch one movie by its key (movie_key), in admin shape, or null. */
function admin_fetch_movie($db, $movieKey) {
    $stmt = $db->prepare("SELECT * FROM movies WHERE movie_key = ?");
    $stmt->execute([$movieKey]);
    $m = $stmt->fetch();
    return $m ? admin_shape_movie($db, $m) : null;
}

/** Turn a title into a url-safe movie_key. */
function admin_make_key($title) {
    $key = strtolower(trim($title));
    $key = preg_replace('/[^a-z0-9]+/', '-', $key);
    $key = trim($key, '-');
    return $key !== '' ? substr($key, 0, 40) : ('movie-' . substr(uniqid(), -6));
}

/** Resolve a genre name to its id, creating the genre if needed. */
function admin_genre_id($db, $name) {
    $name = trim($name);
    if ($name === '') return null;
    $stmt = $db->prepare("SELECT id FROM genres WHERE name = ?");
    $stmt->execute([$name]);
    $id = $stmt->fetchColumn();
    if ($id) return (int)$id;
    $db->prepare("INSERT INTO genres (name) VALUES (?)")->execute([$name]);
    return (int)$db->lastInsertId();
}

/** Replace a movie's genre links from an array of names. */
function admin_set_genres($db, $movieId, array $names) {
    $db->prepare("DELETE FROM movie_genres WHERE movie_id = ?")->execute([$movieId]);
    $ins = $db->prepare("INSERT IGNORE INTO movie_genres (movie_id, genre_id) VALUES (?, ?)");
    foreach ($names as $n) {
        $gid = admin_genre_id($db, $n);
        if ($gid) $ins->execute([$movieId, $gid]);
    }
}

/** Replace a movie's cast from an array of names. */
function admin_set_cast($db, $movieId, array $names) {
    $db->prepare("DELETE FROM cast_members WHERE movie_id = ?")->execute([$movieId]);
    $ins = $db->prepare("INSERT INTO cast_members (movie_id, name, display_order) VALUES (?, ?, ?)");
    $order = 0;
    foreach ($names as $n) {
        $n = trim($n);
        if ($n !== '') $ins->execute([$movieId, $n, $order++]);
    }
}

/**
 * Create or update a movie from the editor form data.
 * $data keys: title, year, runtime, director, rating, tagline, synopsis,
 *             critic_score, audience_score, genres (array), cast (array),
 *             scheme_color_a/b, accent_color (optional)
 * Returns the movie_key.
 */
function admin_save_movie($db, array $data, $movieKey = null) {
    $genres = $data['genres'] ?? [];
    $cast   = $data['cast'] ?? [];

    if ($movieKey) {
        $stmt = $db->prepare("
            UPDATE movies SET
                title = ?, year = ?, runtime = ?, director = ?, rating = ?,
                tagline = ?, synopsis = ?, critic_score = ?, audience_score = ?,
                updated_at = NOW()
            WHERE movie_key = ?
        ");
        $stmt->execute([
            $data['title'], (int)$data['year'], (int)$data['runtime'], $data['director'],
            $data['rating'] ?? '', $data['tagline'] ?? '', $data['synopsis'] ?? '',
            (int)($data['critic_score'] ?? 0), (int)($data['audience_score'] ?? 0),
            $movieKey
        ]);
        $idStmt = $db->prepare("SELECT id FROM movies WHERE movie_key = ?");
        $idStmt->execute([$movieKey]);
        $movieId = (int)$idStmt->fetchColumn();
    } else {
        $movieKey = admin_make_key($data['title']);
        // guarantee uniqueness
        $check = $db->prepare("SELECT COUNT(*) FROM movies WHERE movie_key = ?");
        $check->execute([$movieKey]);
        if ($check->fetchColumn() > 0) {
            $movieKey .= '-' . substr(uniqid(), -4);
        }
        $stmt = $db->prepare("
            INSERT INTO movies
            (movie_key, title, year, runtime, director, rating, tagline, synopsis,
             critic_score, audience_score, scheme_color_1, scheme_color_2, accent_color)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $movieKey, $data['title'], (int)$data['year'], (int)$data['runtime'], $data['director'],
            $data['rating'] ?? '', $data['tagline'] ?? '', $data['synopsis'] ?? '',
            (int)($data['critic_score'] ?? 0), (int)($data['audience_score'] ?? 0),
            $data['scheme_color_a'] ?? 'oklch(0.32 0.06 30)',
            $data['scheme_color_b'] ?? 'oklch(0.18 0.04 50)',
            $data['accent_color']   ?? 'oklch(0.78 0.14 70)'
        ]);
        $movieId = (int)$db->lastInsertId();
    }

    admin_set_genres($db, $movieId, (array)$genres);
    admin_set_cast($db, $movieId, (array)$cast);
    return $movieKey;
}

/** Delete a movie by key (availability/genres/cast cascade via FK). */
function admin_delete_movie($db, $movieKey) {
    $stmt = $db->prepare("DELETE FROM movies WHERE movie_key = ?");
    $stmt->execute([$movieKey]);
    return $stmt->rowCount() > 0;
}
