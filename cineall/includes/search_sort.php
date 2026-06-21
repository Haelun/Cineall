<?php
/**
 * ============================================================================
 * CineAll - SEARCHING & SORTING ALGORITHMS
 * ============================================================================
 * This file does the "smart" part of the search page using simple algorithms
 * we wrote by hand (instead of letting the database do it). It is written in a
 * plain, easy-to-follow style.
 *
 * What is inside:
 *   1. relevanceScore()      - gives a movie a score for how well it matches
 *   2. searchMoviesRanked()  - SEARCH: keeps only the movies that match
 *   3. compareMovies()       - says which of two movies should come first
 *   4. quickSort()           - SORT (quick sort)
 *   5. mergeSort()           - SORT (merge sort, used for A-Z)
 *   6. sortMovies()          - picks the right sort for the chosen mode
 *   7. binarySearchByTitle() - finds one movie by exact title (binary search)
 * ============================================================================
 */


// ---------------------------------------------------------------------------
// 1. RELEVANCE SCORE
//    Look at one movie and the words the user typed, and give points.
//    More points = a better match. 0 points = not a match at all.
// ---------------------------------------------------------------------------
function relevanceScore($movie, $query) {
    $query = strtolower(trim($query));
    if ($query === '') {
        return 0;
    }

    $title    = strtolower($movie['title']);
    $director = strtolower($movie['director']);

    // genres and cast can arrive as a comma string or an array - make them arrays
    $genres = isset($movie['genres']) ? $movie['genres'] : array();
    if (is_string($genres)) {
        $genres = $genres === '' ? array() : explode(',', $genres);
    }
    $cast = isset($movie['cast_members']) ? $movie['cast_members'] : array();
    if (is_string($cast)) {
        $cast = $cast === '' ? array() : explode(',', $cast);
    }

    $score = 0;

    // ---- check the title (the most important match) ----
    if ($title === $query) {
        $score = $score + 100;                 // exact title
    } elseif (str_starts_with($title, $query)) {
        $score = $score + 60;                  // title starts with the query
    } elseif (strpos($title, $query) !== false) {
        $score = $score + 40;                  // query appears somewhere in the title
    }

    // ---- check the director ----
    if (strpos($director, $query) !== false) {
        $score = $score + 25;
    }

    // ---- check the cast list ----
    foreach ($cast as $person) {
        if (strpos(strtolower(trim($person)), $query) !== false) {
            $score = $score + 15;
            break; // one match is enough
        }
    }

    // ---- check the genres ----
    foreach ($genres as $genre) {
        if (strpos(strtolower(trim($genre)), $query) !== false) {
            $score = $score + 10;
            break;
        }
    }

    return $score;
}


// ---------------------------------------------------------------------------
// 2. SEARCH ALGORITHM (linear search)
//    Go through every movie one by one, score it, and keep the ones that
//    match (score greater than 0). We remember the score in "_score" so we
//    can sort by it later.
// ---------------------------------------------------------------------------
function searchMoviesRanked($movies, $query) {
    // No search text -> everything is a candidate, give them all score 0.
    if (trim($query) === '') {
        $all = array();
        foreach ($movies as $movie) {
            $movie['_score'] = 0;
            $all[] = $movie;
        }
        return $all;
    }

    $matched = array();
    foreach ($movies as $movie) {
        $score = relevanceScore($movie, $query);
        if ($score > 0) {
            $movie['_score'] = $score;
            $matched[] = $movie;
        }
    }
    return $matched;
}


// ---------------------------------------------------------------------------
// 3. COMPARE TWO MOVIES
//    Decide which of two movies should come first for a given sort mode.
//    Returns a number:
//       negative -> movie A comes first
//       positive -> movie B comes first
//       zero     -> they are equal
// ---------------------------------------------------------------------------
function compareMovies($a, $b, $mode) {
    if ($mode === 'rating') {
        // higher critic score first
        return (int)$b['critic_score'] - (int)$a['critic_score'];
    }

    if ($mode === 'year') {
        // newest year first
        return (int)$b['year'] - (int)$a['year'];
    }

    if ($mode === 'oldest') {
        // oldest year first
        return (int)$a['year'] - (int)$b['year'];
    }

    if ($mode === 'title') {
        // A to Z by title (strcmp compares text)
        return strcmp(strtolower($a['title']), strtolower($b['title']));
    }

    // default mode is 'relevance':
    // best search score first, and if equal, higher critic score first
    $scoreA = isset($a['_score']) ? $a['_score'] : 0;
    $scoreB = isset($b['_score']) ? $b['_score'] : 0;
    if ($scoreA !== $scoreB) {
        return $scoreB - $scoreA;
    }
    return (int)$b['critic_score'] - (int)$a['critic_score'];
}


// ---------------------------------------------------------------------------
// 4. QUICK SORT
//    Pick one "pivot" movie, put the ones that come before it on the left and
//    the ones that come after it on the right, then sort each side the same
//    way and join everything back together.
// ---------------------------------------------------------------------------
function quickSort($movies, $mode) {
    // A list with 0 or 1 items is already sorted.
    if (count($movies) <= 1) {
        return $movies;
    }

    // Use the middle item as the pivot.
    $middle = intdiv(count($movies), 2);
    $pivot  = $movies[$middle];

    $left  = array(); // come before the pivot
    $right = array(); // come after the pivot
    $same  = array(); // equal to the pivot

    for ($i = 0; $i < count($movies); $i++) {
        $result = compareMovies($movies[$i], $pivot, $mode);
        if ($result < 0) {
            $left[] = $movies[$i];
        } elseif ($result > 0) {
            $right[] = $movies[$i];
        } else {
            $same[] = $movies[$i];
        }
    }

    // Sort the left and right parts, then put it all back together.
    $sortedLeft  = quickSort($left, $mode);
    $sortedRight = quickSort($right, $mode);
    return array_merge($sortedLeft, $same, $sortedRight);
}


// ---------------------------------------------------------------------------
// 5. MERGE SORT
//    Split the list in half, sort each half, then merge the two sorted halves
//    back together in order. We use this for the A-Z sort.
// ---------------------------------------------------------------------------
function mergeSort($movies, $mode) {
    if (count($movies) <= 1) {
        return $movies;
    }

    // Split into two halves.
    $middle    = intdiv(count($movies), 2);
    $leftHalf  = mergeSort(array_slice($movies, 0, $middle), $mode);
    $rightHalf = mergeSort(array_slice($movies, $middle), $mode);

    // Merge the two sorted halves.
    $result = array();
    $i = 0; // position in left half
    $j = 0; // position in right half

    while ($i < count($leftHalf) && $j < count($rightHalf)) {
        if (compareMovies($leftHalf[$i], $rightHalf[$j], $mode) <= 0) {
            $result[] = $leftHalf[$i];
            $i++;
        } else {
            $result[] = $rightHalf[$j];
            $j++;
        }
    }

    // Add whatever is left over in each half.
    while ($i < count($leftHalf)) {
        $result[] = $leftHalf[$i];
        $i++;
    }
    while ($j < count($rightHalf)) {
        $result[] = $rightHalf[$j];
        $j++;
    }

    return $result;
}


// ---------------------------------------------------------------------------
// 6. SORT MOVIES
//    Choose which sort to use for the mode the user picked.
//    Modes: relevance, rating, year (newest), oldest, title (A-Z)
// ---------------------------------------------------------------------------
function sortMovies($movies, $sortBy) {
    if ($sortBy === 'title') {
        // A-Z uses merge sort.
        return mergeSort($movies, 'title');
    }
    // Everything else uses quick sort.
    return quickSort($movies, $sortBy);
}


// ---------------------------------------------------------------------------
// 7. BINARY SEARCH (find one movie by its exact title)
//    The list MUST already be sorted A-Z by title first. We keep cutting the
//    list in half until we find the movie (or run out of list).
// ---------------------------------------------------------------------------
function binarySearchByTitle($sortedMovies, $title) {
    $target = strtolower(trim($title));
    $low  = 0;
    $high = count($sortedMovies) - 1;

    while ($low <= $high) {
        $middle = intdiv($low + $high, 2);
        $current = strtolower($sortedMovies[$middle]['title']);

        if ($current === $target) {
            return $sortedMovies[$middle]; // found it
        } elseif ($current < $target) {
            $low = $middle + 1;            // look in the right half
        } else {
            $high = $middle - 1;           // look in the left half
        }
    }

    return null; // not found
}
