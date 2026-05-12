-- ============================================================================
-- CineAll Seed Data
-- ============================================================================

USE cineall;

-- ============================================================================
-- Insert Platforms
-- ============================================================================
INSERT INTO platforms (platform_key, name, hue, abbr) VALUES
('streamline', 'Streamline', 8, 'SL'),
('vista', 'Vista+', 220, 'V+'),
('orbit', 'Orbit TV', 280, 'OR'),
('monogram', 'Monogram', 150, 'MG'),
('lumen', 'Lumen', 50, 'LM'),
('bluechannel', 'Blue Channel', 200, 'BC'),
('attic', 'The Attic', 25, 'AT'),
('cinedeck', 'CineDeck', 340, 'CD');

-- ============================================================================
-- Insert Genres
-- ============================================================================
INSERT INTO genres (name) VALUES
('Drama'), ('Thriller'), ('Sci-Fi'), ('Comedy'), ('Romance'),
('Documentary'), ('Horror'), ('Animation'), ('Crime'), ('Western'),
('Musical'), ('Fantasy');

-- ============================================================================
-- Insert Movies
-- ============================================================================
INSERT INTO movies (movie_key, title, year, runtime, rating, director, critic_score, audience_score, synopsis, tagline, scheme_color_1, scheme_color_2, accent_color) VALUES
('m01', 'The Quiet Hour', 2024, 118, 'PG-13', 'Ana Volkov', 92, 81,
'A retired translator returns to her childhood village to discover that the language she once spoke has been deliberately forgotten. As she pieces together fragments of a buried summer, the past refuses to stay buried.',
'Some silences are inherited.',
'oklch(0.32 0.06 30)', 'oklch(0.18 0.04 50)', 'oklch(0.78 0.14 70)'),

('m02', 'Northwind', 2025, 96, 'R', 'Calder Mathis', 78, 86,
'A weather-station technician on a remote arctic island intercepts a transmission that shouldn\'t exist. As the storm closes in, so does something else.',
'The signal came from inside.',
'oklch(0.28 0.05 220)', 'oklch(0.14 0.03 250)', 'oklch(0.82 0.12 220)'),

('m03', 'Saltwater Year', 2023, 132, 'PG-13', 'Imani Foster', 88, 90,
'Twelve months on a tidal island, told through the letters two estranged siblings refuse to send. A meditation on memory, distance, and the weight of what we keep.',
'Twelve tides. Two letters never sent.',
'oklch(0.42 0.08 200)', 'oklch(0.22 0.05 220)', 'oklch(0.85 0.10 190)'),

('m04', 'Vessel', 2025, 142, 'PG-13', 'Tanaka Reiji', 94, 79,
'In 2087, a deep-space cargo pilot wakes from cryosleep three centuries late. The ship is intact, the crew is gone, and the cargo has begun to sing.',
'The cargo has begun to sing.',
'oklch(0.30 0.07 280)', 'oklch(0.16 0.04 300)', 'oklch(0.80 0.12 290)'),

('m05', 'Last Light Cafe', 2024, 102, 'PG', 'Pilar Vega', 71, 92,
'A failing 24-hour diner becomes the unlikely meeting place for a night cleaner, a freelance obituary writer, and a man who keeps ordering the same impossible thing.',
'Open all night. Mostly.',
'oklch(0.45 0.10 60)', 'oklch(0.25 0.06 40)', 'oklch(0.85 0.13 70)'),

('m06', 'The Cartographer\'s Wife', 2023, 124, 'R', 'Soren Brandt', 84, 77,
'In 1911, a Norwegian cartographer\'s wife inherits her husband\'s unfinished map of an island that may not exist. To complete it, she must walk a coastline he never returned from.',
'A map of an island that may not exist.',
'oklch(0.34 0.05 100)', 'oklch(0.18 0.04 120)', 'oklch(0.82 0.10 100)'),

('m07', 'Concrete Garden', 2025, 89, 'PG-13', 'Yuki Aramaki', 96, 88,
'Five years inside the rooftop community gardens of Osaka, told through the four seasons and the woman who tends them after her son stops calling.',
'Four seasons. One rooftop.',
'oklch(0.40 0.08 140)', 'oklch(0.22 0.05 160)', 'oklch(0.82 0.12 140)'),

('m08', 'Pale Hour', 2024, 108, 'R', 'Helena Krause', 82, 74,
'A sleep researcher\'s subjects begin describing the same dream — not the same theme, the same dream, frame by frame. The institute decides to stop her work. She decides to keep going.',
'They are not having the same dream. They are having THE dream.',
'oklch(0.22 0.04 320)', 'oklch(0.10 0.02 340)', 'oklch(0.78 0.13 330)'),

('m09', 'Hum', 2025, 78, 'G', 'Rosa Quintero', 91, 95,
'A hand-drawn fable about a small village whose residents wake up sharing one collective tune they cannot place. To find its source, a girl walks until the song gets quieter.',
'Walk until it gets quieter.',
'oklch(0.55 0.13 30)', 'oklch(0.32 0.08 50)', 'oklch(0.88 0.13 50)'),

('m10', 'Bureau of Common Things', 2024, 113, 'PG-13', 'Jonas Weller', 86, 89,
'In a fictional European city, a low-ranking civil servant is tasked with cataloguing every "ordinary" object in the country. By month three, no one can agree on what ordinary means.',
'Define: a spoon.',
'oklch(0.42 0.07 80)', 'oklch(0.24 0.05 90)', 'oklch(0.85 0.12 85)'),

('m11', 'Riverline', 2023, 156, 'R', 'Demetrius Hale', 89, 84,
'Three generations of a family running an inland-river ferry are upended when a body surfaces upstream — and the only witness has vowed silence for forty years.',
'Forty years of nothing said.',
'oklch(0.26 0.05 50)', 'oklch(0.14 0.03 70)', 'oklch(0.78 0.14 60)'),

('m12', 'Soft Machine', 2025, 101, 'PG-13', 'Lena Park', 73, 88,
'A near-future love story between a museum conservator and the AI restoring the museum\'s damaged 20th-century film prints — told one frame at a time.',
'One frame at a time.',
'oklch(0.40 0.10 320)', 'oklch(0.22 0.06 340)', 'oklch(0.85 0.13 320)');

-- ============================================================================
-- Insert Cast Members
-- ============================================================================
INSERT INTO cast_members (movie_id, name, display_order) VALUES
(1, 'Lila Marsden', 1), (1, 'Henrik Osei', 2), (1, 'Sun Park', 3), (1, 'Beatriz Almeida', 4),
(2, 'Rune Hellström', 1), (2, 'Marin Cole', 2), (2, 'Yusra Bekele', 3),
(3, 'Ines Quintero', 1), (3, 'Jude Hassan', 2), (3, 'Margo Lee', 3),
(4, 'Eun-Bi Park', 1), (4, 'Mateus Vidal', 2), (4, 'Ola Hartmann', 3), (4, 'Kira Iwasaki', 4),
(5, 'Fern Calloway', 1), (5, 'Dmitri Sava', 2), (5, 'Wren Okafor', 3),
(6, 'Ingrid Sahlberg', 1), (6, 'Tomás Reyes', 2), (6, 'Aram Petrosyan', 3),
(7, 'Featuring Kazue Mori', 1),
(8, 'Mira Voss', 1), (8, 'Adesh Nair', 2), (8, 'Otto Lind', 3),
(9, 'Voice cast led by Nia Brown', 1),
(10, 'Petra Lindqvist', 1), (10, 'Matei Florescu', 2), (10, 'Anouk Beaumont', 3),
(11, 'Rashida Boone', 1), (11, 'Esteban Reyes', 2), (11, 'Cassius Wren', 3),
(12, 'Jia Chen', 1), (12, 'Voiced by Asa Lindqvist', 2), (12, 'Felix Mboya', 3);

-- ============================================================================
-- Insert Movie-Genre Relationships
-- ============================================================================
INSERT INTO movie_genres (movie_id, genre_id) VALUES
(1, 1), (1, 2),  -- The Quiet Hour: Drama, Thriller
(2, 2), (2, 9),  -- Northwind: Thriller, Crime
(3, 1), (3, 5),  -- Saltwater Year: Drama, Romance
(4, 3), (4, 1),  -- Vessel: Sci-Fi, Drama
(5, 4), (5, 5),  -- Last Light Cafe: Comedy, Romance
(6, 1), (6, 5),  -- The Cartographer's Wife: Drama, Romance
(7, 6),          -- Concrete Garden: Documentary
(8, 7), (8, 2),  -- Pale Hour: Horror, Thriller
(9, 8), (9, 1),  -- Hum: Animation, Drama
(10, 4), (10, 1),-- Bureau of Common Things: Comedy, Drama
(11, 9), (11, 1),-- Riverline: Crime, Drama
(12, 3), (12, 5);-- Soft Machine: Sci-Fi, Romance

-- ============================================================================
-- Insert Availability
-- ============================================================================
INSERT INTO availability (movie_id, platform_id, kind, price_from) VALUES
-- The Quiet Hour
(1, 1, 'subscription', NULL),
(1, 2, 'rent', 4.99),
(1, 8, 'buy', 14.99),
-- Northwind
(2, 3, 'subscription', NULL),
(2, 5, 'rent', 3.99),
-- Saltwater Year
(3, 4, 'subscription', NULL),
(3, 2, 'rent', 4.99),
(3, 7, 'subscription', NULL),
-- Vessel
(4, 1, 'subscription', NULL),
(4, 8, 'rent', 5.99),
(4, 8, 'buy', 19.99),
-- Last Light Cafe
(5, 5, 'subscription', NULL),
(5, 7, 'subscription', NULL),
-- The Cartographer's Wife
(6, 4, 'subscription', NULL),
(6, 6, 'rent', 3.99),
-- Concrete Garden
(7, 7, 'subscription', NULL),
(7, 2, 'rent', 2.99),
-- Pale Hour
(8, 3, 'subscription', NULL),
(8, 8, 'rent', 4.99),
-- Hum
(9, 4, 'subscription', NULL),
(9, 5, 'subscription', NULL),
-- Bureau of Common Things
(10, 1, 'subscription', NULL),
(10, 6, 'subscription', NULL),
-- Riverline
(11, 8, 'subscription', NULL),
(11, 3, 'rent', 4.99),
-- Soft Machine
(12, 2, 'subscription', NULL),
(12, 7, 'rent', 3.99);

-- ============================================================================
-- Insert Home Rows
-- ============================================================================
INSERT INTO home_rows (row_key, title, kicker, display_order) VALUES
('trending', 'Trending this week', 'CineAll Pulse', 1),
('editorial', 'Slow cinema, after midnight', 'Editor\'s desk', 2),
('newdrops', 'New this week', 'Just landed', 3),
('leaving', 'Leaving streamers in 7 days', 'Last chance', 4),
('genrespotlight', 'A short course in unease', 'Genre study', 5);

-- ============================================================================
-- Insert Home Row Movies
-- ============================================================================
INSERT INTO home_row_movies (row_id, movie_id, display_order) VALUES
-- Trending
(1, 4, 1), (1, 2, 2), (1, 7, 3), (1, 12, 4), (1, 8, 5), (1, 5, 6), (1, 11, 7),
-- Editorial
(2, 3, 1), (2, 1, 2), (2, 6, 3), (2, 7, 4), (2, 9, 5),
-- New drops
(3, 2, 1), (3, 12, 2), (3, 7, 3), (3, 4, 4), (3, 9, 5),
-- Leaving
(4, 11, 1), (4, 6, 2), (4, 10, 3), (4, 8, 4),
-- Genre spotlight
(5, 8, 1), (5, 2, 2), (5, 1, 3), (5, 11, 4), (5, 4, 5);

-- ============================================================================
-- Insert Demo User (password: demo123)
-- ============================================================================
INSERT INTO users (username, email, password_hash, display_name) VALUES
('demo', 'demo@cineall.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L. Marin');

-- ============================================================================
-- Insert Demo User Subscriptions
-- ============================================================================
INSERT INTO user_subscriptions (user_id, platform_id) VALUES
(1, 1), -- Streamline
(1, 2), -- Vista+
(1, 7); -- The Attic

-- ============================================================================
-- Insert Demo User Watchlist
-- ============================================================================
INSERT INTO watchlist (user_id, movie_id) VALUES
(1, 3), (1, 7), (1, 8), (1, 11);

-- ============================================================================
-- Insert Demo User Preferences
-- ============================================================================
INSERT INTO user_preferences (user_id, notify_leaving, email_digest, critic_score_first, hide_watched, surface_festival, use_audience_score) VALUES
(1, TRUE, TRUE, FALSE, TRUE, FALSE, FALSE);
