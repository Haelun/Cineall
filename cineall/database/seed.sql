-- ============================================================================
-- CineAll — SEED DATA
-- Run this AFTER schema.sql (or import schema.sql then this file).
-- ============================================================================
USE `cineall`;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `home_row_movies`;
TRUNCATE TABLE `home_rows`;
TRUNCATE TABLE `homepage_settings`;
TRUNCATE TABLE `availability`;
TRUNCATE TABLE `movie_genres`;
TRUNCATE TABLE `cast_members`;
TRUNCATE TABLE `reviews`;
TRUNCATE TABLE `movies`;
TRUNCATE TABLE `genres`;
TRUNCATE TABLE `platforms`;
TRUNCATE TABLE `analytics`;
TRUNCATE TABLE `activity_log`;
TRUNCATE TABLE `user_preferences`;
TRUNCATE TABLE `user_subscriptions`;
TRUNCATE TABLE `watchlist`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- USERS  (password for all three is: password123)
-- ---------------------------------------------------------------------------
INSERT INTO `users` (`id`,`name`,`email`,`password`,`role`,`display_name`,`plan`,`status`,`is_active`,`email_verified`) VALUES
(1,'John Doe','user@cineall.com','$2y$10$9nsKllnoK0o8Rc.YwgwON.PirYa7hkWHjXsN34CyrHRJTTQdyCVp6','user','Johnny','Premium','active',1,1),
(2,'Admin User','admin@cineall.com','$2y$10$9nsKllnoK0o8Rc.YwgwON.PirYa7hkWHjXsN34CyrHRJTTQdyCVp6','admin','Admin','Premium','active',1,1),
(3,'Curator User','curator@cineall.com','$2y$10$9nsKllnoK0o8Rc.YwgwON.PirYa7hkWHjXsN34CyrHRJTTQdyCVp6','curator','Curator','Free','active',1,1),
(4,'Mara Vinci','mara@example.com','$2y$10$9nsKllnoK0o8Rc.YwgwON.PirYa7hkWHjXsN34CyrHRJTTQdyCVp6','user','Mara','Free','active',1,1),
(5,'Leo Tanaka','leo@example.com','$2y$10$9nsKllnoK0o8Rc.YwgwON.PirYa7hkWHjXsN34CyrHRJTTQdyCVp6','user','Leo','Premium','idle',1,1);

-- ---------------------------------------------------------------------------
-- PLATFORMS
-- ---------------------------------------------------------------------------
INSERT INTO `platforms` (`id`,`platform_key`,`name`,`hue`,`abbr`) VALUES
(1,'netflix','Netflix',5,'NF'),
(2,'disney','Disney+',230,'D+'),
(3,'prime','Prime Video',205,'PV'),
(4,'hbo','HBO Max',270,'HBO'),
(5,'apple','Apple TV+',0,'TV+');

-- ---------------------------------------------------------------------------
-- GENRES
-- ---------------------------------------------------------------------------
INSERT INTO `genres` (`id`,`name`) VALUES
(1,'Sci-Fi'),(2,'Drama'),(3,'Thriller'),(4,'Action'),
(5,'Horror'),(6,'Comedy'),(7,'Romance'),(8,'Documentary');

-- ---------------------------------------------------------------------------
-- MOVIES
-- ---------------------------------------------------------------------------
INSERT INTO `movies`
(`id`,`movie_key`,`title`,`year`,`runtime`,`rating`,`director`,`critic_score`,`audience_score`,`synopsis`,`tagline`,`scheme_color_1`,`scheme_color_2`,`accent_color`,`status`) VALUES
(1,'galactic-convergence','Galactic Convergence: Invasion Earth',2024,142,'PG-13','Ava Renner',88,91,'When a silent signal from deep space reaches Earth, a fractured coalition of nations must decide whether the visitors come as saviours or conquerors before the convergence is complete.','The sky was only the beginning.','oklch(0.32 0.09 264)','oklch(0.18 0.05 280)','oklch(0.78 0.14 70)','published'),
(2,'beneath-the-stars','Beneath the Stars',2023,118,'PG','Daniel Okafor',79,84,'Two estranged siblings retrace their late father''s summer road trip across the desert, learning that the constellations he loved were a map to something he never told them.','Some maps lead home.','oklch(0.30 0.07 230)','oklch(0.16 0.04 250)','oklch(0.80 0.12 200)','published'),
(3,'forbidden-bogis','Forbidden Bogis',2024,109,'R','Sianne Marsh',72,68,'A disgraced food critic infiltrates an underground supper club whose menu hides a conspiracy that reaches the highest tables in the city.','Every course has a price.','oklch(0.30 0.10 30)','oklch(0.16 0.05 40)','oklch(0.75 0.15 40)','published'),
(4,'fight-for-survival','Fight for Survival',2022,131,'R','Marcus Cole',81,88,'Stranded after an avalanche, a search-and-rescue medic must keep a wounded stranger alive through the night while something else moves through the snow.','The mountain keeps its dead.','oklch(0.28 0.06 220)','oklch(0.15 0.03 230)','oklch(0.82 0.10 220)','published'),
(5,'betrayal-in-the-shadows','Betrayal in the Shadows',2023,126,'PG-13','Priya Nadkarni',76,73,'An intelligence analyst discovers her mentor may be the mole she''s been hunting for a decade, and the only proof is buried in a city that no longer exists on any map.','Trust is the deepest cover.','oklch(0.26 0.05 300)','oklch(0.14 0.03 310)','oklch(0.78 0.13 320)','published'),
(6,'reckoning','Reckoning',2025,148,'R','Ava Renner',90,86,'A small-town sheriff confronts the family empire that raised her when a decades-old disappearance resurfaces with her own name attached to it.','The past always sends an invoice.','oklch(0.27 0.07 20)','oklch(0.14 0.04 25)','oklch(0.76 0.15 35)','published'),
(7,'paper-lanterns','Paper Lanterns',2021,97,'PG','Mei Lin',83,89,'In a fading seaside town, a widowed lantern-maker and a runaway teenager strike an unlikely friendship across one long, luminous festival season.','Light travels farther than grief.','oklch(0.31 0.08 90)','oklch(0.17 0.04 80)','oklch(0.84 0.13 90)','published'),
(8,'the-last-broadcast','The Last Broadcast',2024,112,'PG-13','Theo Abara',69,77,'The overnight host of a dying radio station starts receiving calls from listeners describing a town that doesn''t exist — until the callers start naming streets she knows.','Stay tuned. Don''t answer.','oklch(0.25 0.05 150)','oklch(0.13 0.03 160)','oklch(0.80 0.12 160)','published');

-- ---------------------------------------------------------------------------
-- MOVIE_GENRES
-- ---------------------------------------------------------------------------
INSERT INTO `movie_genres` (`movie_id`,`genre_id`) VALUES
(1,1),(1,4),(1,3),
(2,2),(2,7),
(3,3),(3,2),
(4,4),(4,3),
(5,3),(5,1),
(6,2),(6,3),
(7,2),(7,7),
(8,5),(8,3);

-- ---------------------------------------------------------------------------
-- CAST_MEMBERS
-- ---------------------------------------------------------------------------
INSERT INTO `cast_members` (`movie_id`,`name`,`display_order`) VALUES
(1,'Helena Cruz',0),(1,'Idris Vane',1),(1,'Tomas Reyhan',2),
(2,'Clara Ode',0),(2,'Samir Bell',1),
(3,'Nora Quill',0),(3,'Felix Brandt',1),
(4,'June Hale',0),(4,'Anders Vik',1),
(5,'Priya Anand',0),(5,'Marcus Holt',1),
(6,'Helena Cruz',0),(6,'Will Reeves',1),
(7,'Mei Sato',0),(7,'Daniel Park',1),
(8,'Ola Finch',0),(8,'Greer Mott',1);

-- ---------------------------------------------------------------------------
-- AVAILABILITY  (kind: subscription / rent / buy)
-- ---------------------------------------------------------------------------
INSERT INTO `availability` (`movie_id`,`platform_id`,`kind`,`price_from`) VALUES
(1,1,'subscription',NULL),(1,3,'rent',3.99),(1,5,'buy',12.99),
(2,2,'subscription',NULL),(2,3,'subscription',NULL),
(3,1,'subscription',NULL),(3,4,'rent',4.49),
(4,3,'subscription',NULL),(4,5,'rent',3.99),
(5,4,'subscription',NULL),(5,1,'buy',9.99),
(6,5,'subscription',NULL),(6,3,'rent',5.99),
(7,2,'subscription',NULL),(7,4,'subscription',NULL),
(8,1,'rent',2.99),(8,3,'buy',8.99);

-- ---------------------------------------------------------------------------
-- HOME ROWS  (homepage curation)
-- ---------------------------------------------------------------------------
INSERT INTO `home_rows` (`id`,`row_key`,`title`,`kicker`,`display_order`,`is_active`) VALUES
(1,'featured','Featured this week','Editor''s pick',1,1),
(2,'new-releases','Fresh on every service','New & notable',2,1),
(3,'critically-acclaimed','Critics can''t stop talking','Top rated',3,1);

INSERT INTO `home_row_movies` (`row_id`,`movie_id`,`display_order`) VALUES
(1,1,0),(1,6,1),(1,2,2),(1,7,3),
(2,8,0),(2,3,1),(2,1,2),(2,6,3),
(3,1,0),(3,6,1),(3,7,2),(3,4,3);

-- ---------------------------------------------------------------------------
-- HOMEPAGE SETTINGS
-- ---------------------------------------------------------------------------
INSERT INTO `homepage_settings` (`id`,`hero_movie_id`,`hero_tagline`,`is_published`,`updated_by`) VALUES
(1,'galactic-convergence','The sky was only the beginning.',1,'Curator User');

-- ---------------------------------------------------------------------------
-- SAMPLE WATCHLIST / SUBSCRIPTIONS / PREFERENCES for the demo user (id 1)
-- ---------------------------------------------------------------------------
INSERT INTO `user_subscriptions` (`user_id`,`platform_id`) VALUES (1,1),(1,3);
INSERT INTO `watchlist` (`user_id`,`movie_id`) VALUES (1,6),(1,2);
INSERT INTO `user_preferences` (`user_id`) VALUES (1);

-- ---------------------------------------------------------------------------
-- REVIEWS  (moderation queue for admin/curator)
-- ---------------------------------------------------------------------------
INSERT INTO `reviews` (`review_key`,`movie_id`,`user_name`,`rating`,`snippet`,`is_flagged`,`status`) VALUES
('rv001',1,'Mara Vinci',5,'Edge of my seat the whole time — the convergence sequence is unreal.',0,'approved'),
('rv002',6,'Leo Tanaka',4,'Slow burn but the payoff lands. Renner is on another level.',0,'approved'),
('rv003',3,'anon_4471',1,'Total garbage, whoever made this should be ashamed!!!',1,'pending'),
('rv004',8,'Greer M.',4,'Genuinely unsettling radio horror. Loved the sound design.',0,'pending'),
('rv005',2,'Sam B.',5,'Cried twice. The desert cinematography is gorgeous.',0,'approved');

-- ---------------------------------------------------------------------------
-- ANALYTICS  (last 14 days, newest first when ordered DESC)
-- ---------------------------------------------------------------------------
INSERT INTO `analytics` (`date`,`visits`,`searches`,`clickthroughs`,`signups`) VALUES
(CURDATE() - INTERVAL 13 DAY, 1820, 940, 410, 22),
(CURDATE() - INTERVAL 12 DAY, 1910, 980, 433, 19),
(CURDATE() - INTERVAL 11 DAY, 2040, 1010, 466, 27),
(CURDATE() - INTERVAL 10 DAY, 1985, 995, 451, 24),
(CURDATE() - INTERVAL 9 DAY, 2110, 1075, 489, 31),
(CURDATE() - INTERVAL 8 DAY, 2260, 1140, 512, 29),
(CURDATE() - INTERVAL 7 DAY, 2185, 1100, 498, 26),
(CURDATE() - INTERVAL 6 DAY, 2320, 1180, 540, 33),
(CURDATE() - INTERVAL 5 DAY, 2410, 1225, 566, 37),
(CURDATE() - INTERVAL 4 DAY, 2380, 1200, 553, 30),
(CURDATE() - INTERVAL 3 DAY, 2495, 1260, 590, 35),
(CURDATE() - INTERVAL 2 DAY, 2560, 1300, 612, 41),
(CURDATE() - INTERVAL 1 DAY, 2620, 1335, 631, 38),
(CURDATE(), 2705, 1390, 654, 44);

-- ---------------------------------------------------------------------------
-- ACTIVITY LOG
-- ---------------------------------------------------------------------------
INSERT INTO `activity_log` (`actor`,`action`,`target`,`ip_address`) VALUES
('Admin User','published film','Reckoning (2025)','127.0.0.1'),
('Curator User','reordered row','Featured this week','127.0.0.1'),
('Admin User','approved review','rv001 on Galactic Convergence','127.0.0.1'),
('Curator User','flagged review','rv003 on Forbidden Bogis','127.0.0.1'),
('Admin User','updated availability','Beneath the Stars','127.0.0.1'),
('Curator User','published homepage','Homepage v4','127.0.0.1');
