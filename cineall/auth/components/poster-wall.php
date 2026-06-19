<?php
/**
 * Poster Wall Component
 * Animated background for auth pages
 */

// Sample movie data for posters
$movies = [
    ['title' => 'The Quiet Hour', 'year' => '2024', 'director' => 'Volkov',
     'gradient' => 'linear-gradient(165deg, oklch(0.32 0.06 30) 0%, oklch(0.18 0.04 50) 100%)',
     'accent' => 'oklch(0.78 0.14 70)'],
    ['title' => 'Northwind', 'year' => '2025', 'director' => 'Mathis',
     'gradient' => 'linear-gradient(165deg, oklch(0.28 0.05 220) 0%, oklch(0.14 0.03 250) 100%)',
     'accent' => 'oklch(0.82 0.12 220)'],
    ['title' => 'Saltwater Year', 'year' => '2023', 'director' => 'Foster',
     'gradient' => 'linear-gradient(165deg, oklch(0.42 0.08 200) 0%, oklch(0.22 0.05 220) 100%)',
     'accent' => 'oklch(0.85 0.10 190)'],
    ['title' => 'Vessel', 'year' => '2025', 'director' => 'Reiji',
     'gradient' => 'linear-gradient(165deg, oklch(0.30 0.07 280) 0%, oklch(0.16 0.04 300) 100%)',
     'accent' => 'oklch(0.80 0.12 290)'],
    ['title' => 'Last Light Cafe', 'year' => '2024', 'director' => 'Vega',
     'gradient' => 'linear-gradient(165deg, oklch(0.45 0.10 60) 0%, oklch(0.25 0.06 40) 100%)',
     'accent' => 'oklch(0.85 0.13 70)'],
    ['title' => 'Cartographer', 'year' => '2023', 'director' => 'Brandt',
     'gradient' => 'linear-gradient(165deg, oklch(0.34 0.05 100) 0%, oklch(0.18 0.04 120) 100%)',
     'accent' => 'oklch(0.82 0.10 100)'],
];

// Create 7 columns
$columns = [];
for ($c = 0; $c < 7; $c++) {
    $columnMovies = [];
    for ($r = 0; $r < 6; $r++) {
        $columnMovies[] = $movies[($c * 6 + $r) % count($movies)];
    }
    $columns[] = ['movies' => $columnMovies, 'animate' => $c % 2 === 0 ? 'up' : 'down'];
}
?>

<div class="poster-wall">
    <div class="poster-wall-inner">
        <?php foreach ($columns as $col): ?>
            <div class="poster-wall-column animate-<?php echo $col['animate']; ?>">
                <?php
                // Duplicate for infinite scroll effect
                $allMovies = array_merge($col['movies'], $col['movies']);
                foreach ($allMovies as $movie):
                ?>
                    <div class="poster">
                        <div class="poster-gradient" style="background: <?php echo $movie['gradient']; ?>"></div>
                        <div class="poster-noise"></div>
                        <div class="poster-vignette"></div>
                        <div class="poster-line" style="background: <?php echo $movie['accent']; ?>"></div>
                        <div class="poster-title"><?php echo htmlspecialchars($movie['title']); ?></div>
                        <div class="poster-meta">
                            <span><?php echo htmlspecialchars($movie['director']); ?></span>
                            <span><?php echo htmlspecialchars($movie['year']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
