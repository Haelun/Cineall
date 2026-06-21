-- ============================================================================
-- CineAll — UNIFIED DATABASE SCHEMA
-- ============================================================================
-- One database for the whole platform: public site, auth, admin, curator.
-- Naming follows the project paperwork (movie_key, availability, home_rows,
-- scheme_color_1/2), with the auth tables and the admin/curator auxiliary
-- tables folded in.
--
-- HOW TO INSTALL (XAMPP):
--   1. Start Apache + MySQL in the XAMPP control panel.
--   2. Open http://localhost/phpmyadmin
--   3. Import this file (it creates the `cineall` database for you).
--
-- Seeded accounts (password for both is: password123)
--   admin@cineall.com    -> admin
--   curator@cineall.com  -> curator
-- Regular users sign up themselves (or are inserted manually — see seed.sql).
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `cineall`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cineall`;

-- Drop in dependency order for a clean reinstall ---------------------------
DROP TABLE IF EXISTS `home_row_movies`;
DROP TABLE IF EXISTS `home_rows`;
DROP TABLE IF EXISTS `homepage_settings`;
DROP TABLE IF EXISTS `watchlist`;
DROP TABLE IF EXISTS `user_subscriptions`;
DROP TABLE IF EXISTS `user_preferences`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `availability`;
DROP TABLE IF EXISTS `movie_genres`;
DROP TABLE IF EXISTS `cast_members`;
DROP TABLE IF EXISTS `movies`;
DROP TABLE IF EXISTS `genres`;
DROP TABLE IF EXISTS `platforms`;
DROP TABLE IF EXISTS `two_factor_codes`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `activity_log`;
DROP TABLE IF EXISTS `analytics`;
DROP TABLE IF EXISTS `users`;

-- ============================================================================
-- USERS  (single account table for every role)
-- ----------------------------------------------------------------------------
-- Combines what auth, admin and curator each needed:
--   auth    -> name, email, password, role, is_active, email_verified
--   admin   -> plan, status (services/watchlist counts are computed by JOIN)
--   curator -> role, display_name
-- ============================================================================
CREATE TABLE `users` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`           VARCHAR(255) NOT NULL,
  `email`          VARCHAR(255) NOT NULL UNIQUE,
  `password`       VARCHAR(255) NOT NULL,
  `role`           ENUM('user','admin','curator') NOT NULL DEFAULT 'user',
  `display_name`   VARCHAR(100) NULL,
  `plan`           ENUM('Free','Premium') NOT NULL DEFAULT 'Free',
  `status`         ENUM('active','idle','flagged','banned') NOT NULL DEFAULT 'active',
  `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- AUTH SUPPORT TABLES
-- ============================================================================
CREATE TABLE `sessions` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `token`      VARCHAR(255) NOT NULL UNIQUE,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `expires_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `password_resets` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `token`      VARCHAR(255) NOT NULL UNIQUE,
  `used`       TINYINT(1) NOT NULL DEFAULT 0,
  `expires_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PLATFORMS  (Table 3.1)
-- ============================================================================
CREATE TABLE `platforms` (
  `id`           INT NOT NULL AUTO_INCREMENT,
  `platform_key` VARCHAR(50) NOT NULL UNIQUE,
  `name`         VARCHAR(100) NOT NULL,
  `hue`          INT NOT NULL,
  `abbr`         VARCHAR(10) NOT NULL,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- GENRES  (Table 3.2)
-- ============================================================================
CREATE TABLE `genres` (
  `id`         INT NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(50) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- MOVIES  (Table 3.3)  — normalized; genres & cast live in their own tables.
-- `status` is an additive column used by the admin/curator panels.
-- ============================================================================
CREATE TABLE `movies` (
  `id`             INT NOT NULL AUTO_INCREMENT,
  `movie_key`      VARCHAR(50) NOT NULL UNIQUE,
  `title`          VARCHAR(255) NOT NULL,
  `year`           INT NOT NULL,
  `runtime`        INT NOT NULL,
  `rating`         VARCHAR(10) NOT NULL,
  `director`       VARCHAR(255) NOT NULL,
  `critic_score`   INT NOT NULL,
  `audience_score` INT NOT NULL,
  `synopsis`       TEXT NOT NULL,
  `tagline`        VARCHAR(255) NULL,
  `poster_url`     VARCHAR(500) NULL,
  `trailer_url`    VARCHAR(500) NULL,
  `scheme_color_1` VARCHAR(50) NOT NULL,
  `scheme_color_2` VARCHAR(50) NOT NULL,
  `accent_color`   VARCHAR(50) NOT NULL,
  `status`         ENUM('draft','published','archived') NOT NULL DEFAULT 'published',
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_title` (`title`),
  KEY `idx_year` (`year`),
  KEY `idx_critic` (`critic_score`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CAST_MEMBERS  (Table 3.4)
-- ============================================================================
CREATE TABLE `cast_members` (
  `id`            INT NOT NULL AUTO_INCREMENT,
  `movie_id`      INT NOT NULL,
  `name`          VARCHAR(255) NOT NULL,
  `display_order` INT DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_movie` (`movie_id`),
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- MOVIE_GENRES  (Table 3.5)
-- ============================================================================
CREATE TABLE `movie_genres` (
  `id`       INT NOT NULL AUTO_INCREMENT,
  `movie_id` INT NOT NULL,
  `genre_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_movie_genre` (`movie_id`,`genre_id`),
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`genre_id`) REFERENCES `genres`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- AVAILABILITY  (Table 3.6)
-- ============================================================================
CREATE TABLE `availability` (
  `id`          INT NOT NULL AUTO_INCREMENT,
  `movie_id`    INT NOT NULL,
  `platform_id` INT NOT NULL,
  `kind`        ENUM('subscription','rent','buy') NOT NULL,
  `price_from`  DECIMAL(10,2) NULL,
  `url`         VARCHAR(500) NULL,
  `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_movie` (`movie_id`),
  KEY `idx_platform` (`platform_id`),
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`platform_id`) REFERENCES `platforms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- USER_SUBSCRIPTIONS  (Table 3.8)
-- ============================================================================
CREATE TABLE `user_subscriptions` (
  `id`          INT NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED NOT NULL,
  `platform_id` INT NOT NULL,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_platform` (`user_id`,`platform_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`platform_id`) REFERENCES `platforms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- WATCHLIST  (Table 3.9)
-- ============================================================================
CREATE TABLE `watchlist` (
  `id`       INT NOT NULL AUTO_INCREMENT,
  `user_id`  INT UNSIGNED NOT NULL,
  `movie_id` INT NOT NULL,
  `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_movie` (`user_id`,`movie_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- USER_PREFERENCES  (Table 3.10)
-- ============================================================================
CREATE TABLE `user_preferences` (
  `id`                 INT NOT NULL AUTO_INCREMENT,
  `user_id`            INT UNSIGNED NOT NULL UNIQUE,
  `notify_leaving`     TINYINT(1) DEFAULT 1,
  `email_digest`       TINYINT(1) DEFAULT 1,
  `critic_score_first` TINYINT(1) DEFAULT 0,
  `hide_watched`       TINYINT(1) DEFAULT 1,
  `surface_festival`   TINYINT(1) DEFAULT 0,
  `use_audience_score` TINYINT(1) DEFAULT 0,
  `created_at`         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- HOME_ROWS  (Table 3.11)  — homepage curation rows
-- ============================================================================
CREATE TABLE `home_rows` (
  `id`            INT NOT NULL AUTO_INCREMENT,
  `row_key`       VARCHAR(50) NOT NULL UNIQUE,
  `title`         VARCHAR(255) NOT NULL,
  `kicker`        VARCHAR(255) NOT NULL,
  `display_order` INT DEFAULT 0,
  `is_active`     TINYINT(1) DEFAULT 1,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- HOME_ROW_MOVIES  (Table 3.12)
-- ============================================================================
CREATE TABLE `home_row_movies` (
  `id`            INT NOT NULL AUTO_INCREMENT,
  `row_id`        INT NOT NULL,
  `movie_id`      INT NOT NULL,
  `display_order` INT DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_row` (`row_id`),
  FOREIGN KEY (`row_id`) REFERENCES `home_rows`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- HOMEPAGE_SETTINGS  (curator panel — publish state for the homepage)
-- ============================================================================
CREATE TABLE `homepage_settings` (
  `id`            INT NOT NULL AUTO_INCREMENT,
  `hero_movie_id` VARCHAR(50) NULL,
  `hero_tagline`  TEXT NULL,
  `is_published`  TINYINT(1) DEFAULT 1,
  `updated_by`    VARCHAR(100) NULL,
  `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- REVIEWS  (admin + curator moderation queue)
-- movie_id references movies(id) (INT) — unified with the rest of the schema.
-- ============================================================================
CREATE TABLE `reviews` (
  `id`         INT NOT NULL AUTO_INCREMENT,
  `review_key` VARCHAR(50) NOT NULL UNIQUE,
  `movie_id`   INT NOT NULL,
  `user_name`  VARCHAR(255) NOT NULL,
  `rating`     INT NOT NULL,
  `snippet`    TEXT NOT NULL,
  `is_flagged` TINYINT(1) DEFAULT 0,
  `status`     ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ACTIVITY_LOG  (shared audit trail for admin + curator)
-- ============================================================================
CREATE TABLE `activity_log` (
  `id`         INT NOT NULL AUTO_INCREMENT,
  `actor`      VARCHAR(255) NOT NULL,
  `action`     VARCHAR(100) NOT NULL,
  `target`     TEXT NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ============================================================================
-- CineAll — SEED DATA  (real films + real platforms with watch links)
-- Import schema.sql first, or just import install.sql (schema + this).
-- NOTE: streaming availability is representative and varies by region/time;
--       edit it any time from the Admin or Curator panel.
-- ============================================================================
USE `cineall`;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `home_row_movies`; TRUNCATE TABLE `home_rows`; TRUNCATE TABLE `homepage_settings`;
TRUNCATE TABLE `availability`; TRUNCATE TABLE `movie_genres`; TRUNCATE TABLE `cast_members`;
TRUNCATE TABLE `reviews`; TRUNCATE TABLE `movies`; TRUNCATE TABLE `genres`; TRUNCATE TABLE `platforms`;
TRUNCATE TABLE `activity_log`;
TRUNCATE TABLE `user_preferences`; TRUNCATE TABLE `user_subscriptions`; TRUNCATE TABLE `watchlist`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- USERS — only the two staff accounts ship with the project (password: password123)
INSERT INTO `users` (`id`,`name`,`email`,`password`,`role`,`display_name`,`plan`,`status`,`is_active`,`email_verified`) VALUES
(1,'Admin User','admin@cineall.com','$2y$10$FPnWRoutjduBSDR6K.7Xwu5cR9PElme0eipm.O.6wei0GQYDgVKaG','admin','Admin','Premium','active',1,1),
(2,'Curator User','curator@cineall.com','$2y$10$FPnWRoutjduBSDR6K.7Xwu5cR9PElme0eipm.O.6wei0GQYDgVKaG','curator','Curator','Free','active',1,1);

-- PLATFORMS (real services)
INSERT INTO `platforms` (`id`,`platform_key`,`name`,`hue`,`abbr`) VALUES
(1,'netflix','Netflix',5,'NF'),
(2,'disney','Disney+',230,'D+'),
(3,'prime','Prime Video',205,'PV'),
(4,'hbo','HBO Max',270,'MAX'),
(5,'apple','Apple TV+',0,'TV+');

INSERT INTO `genres` (`id`,`name`) VALUES
(1,'Sci-Fi'),(2,'Adventure'),(3,'Action'),(4,'Crime'),(5,'Comedy'),(6,'Animation'),(7,'Mystery'),(8,'Thriller'),(9,'Drama'),(10,'History'),
(11,'Romance'),(12,'Documentary'),(13,'Horror'),(14,'Sport'),(15,'Fantasy'),(16,'Musical');

INSERT INTO `movies`
(`id`,`movie_key`,`title`,`year`,`runtime`,`rating`,`director`,`critic_score`,`audience_score`,`synopsis`,`tagline`,`poster_url`,`trailer_url`,`scheme_color_1`,`scheme_color_2`,`accent_color`,`status`) VALUES
(1,'dune-part-two','Dune: Part Two',2024,166,'PG-13','Denis Villeneuve',92,95,'Paul Atreides unites with the Fremen to wage war against House Harkonnen and avenge his fallen family, torn between the love of his life and the fate of the known universe.','Long live the fighters.','https://image.tmdb.org/t/p/w500/1pdfLvkbY9ohJlCjQH2CZjjYVvJ.jpg','https://www.youtube.com/watch?v=Way9Dexny3w','oklch(0.32 0.09 264)','oklch(0.18 0.05 280)','oklch(0.80 0.13 70)','published'),
(2,'the-batman','The Batman',2022,176,'PG-13','Matt Reeves',85,87,'In his second year of fighting crime, Batman uncovers corruption in Gotham City that ties to his own family as he pursues the Riddler, a killer targeting the city''s elite.','Unmask the truth.','https://image.tmdb.org/t/p/w500/74xTEgt7R36Fpooo50r9T25onhq.jpg','https://www.youtube.com/watch?v=mqqft2x_Aa4','oklch(0.28 0.06 220)','oklch(0.15 0.03 230)','oklch(0.82 0.10 220)','published'),
(3,'barbie','Barbie',2023,114,'PG-13','Greta Gerwig',88,83,'Barbie suffers a crisis that leads her to question her world and her existence, journeying into the real world to discover what it truly means to be human.','She''s everything. He''s just Ken.','https://image.tmdb.org/t/p/w500/iuFNMS8U5cb6xfzi51Dbkovj7vM.jpg','https://www.youtube.com/watch?v=pBk4NYhWNMM','oklch(0.30 0.10 30)','oklch(0.16 0.05 40)','oklch(0.78 0.15 40)','published'),
(4,'guardians-of-the-galaxy-vol-3','Guardians of the Galaxy Vol. 3',2023,150,'PG-13','James Gunn',82,94,'The Guardians embark on a dangerous mission to protect one of their own, confronting Rocket''s tragic past and the powerful enemy determined to finish what he started.','Once more with feeling.','https://image.tmdb.org/t/p/w500/r2J02Z2OpNTctfOSN1Ydgii51I3.jpg','https://www.youtube.com/watch?v=u3V5KDHRQvk','oklch(0.31 0.08 150)','oklch(0.17 0.04 160)','oklch(0.84 0.12 150)','published'),
(5,'inside-out-2','Inside Out 2',2024,96,'PG','Kelsey Mann',91,96,'As Riley enters her teenage years, the emotions at Headquarters undergo a sudden renovation to make room for new arrivals — led by the unpredictable Anxiety.','Make room for new feelings.','https://image.tmdb.org/t/p/w500/vpnVM9B6NMmQpWeZvzLvDESb2QY.jpg','https://www.youtube.com/watch?v=LEjhY15eCx0','oklch(0.27 0.07 320)','oklch(0.14 0.04 330)','oklch(0.80 0.13 320)','published'),
(6,'avatar-the-way-of-water','Avatar: The Way of Water',2022,192,'PG-13','James Cameron',76,92,'Jake Sully and Neytiri must protect their family when a familiar threat returns to finish what it started, fleeing their home to the reefs of the Metkayina clan.','Return to Pandora.','https://image.tmdb.org/t/p/w500/t6HIqrRAclMCA60NsSmeqe9RmNV.jpg','https://www.youtube.com/watch?v=d9MyW72ELq0','oklch(0.30 0.07 90)','oklch(0.16 0.04 80)','oklch(0.84 0.13 90)','published'),
(7,'glass-onion','Glass Onion: A Knives Out Mystery',2022,139,'PG-13','Rian Johnson',92,91,'Detective Benoit Blanc travels to a Greek island to unravel a murder mystery among a tech billionaire''s eccentric and tangled circle of friends.','Everyone''s a suspect.','https://image.tmdb.org/t/p/w500/vDGr1YdrlfbU9wxTOdpf3zChmv9.jpg','https://www.youtube.com/watch?v=gj5ibYSz8C0','oklch(0.32 0.09 264)','oklch(0.18 0.05 280)','oklch(0.80 0.13 70)','published'),
(8,'the-gray-man','The Gray Man',2022,122,'PG-13','Anthony & Joe Russo',46,87,'A skilled CIA mercenary becomes the target of a deadly global manhunt led by a sadistic former colleague after he accidentally uncovers dark agency secrets.','Off the books. Out of control.','https://image.tmdb.org/t/p/w500/8cXbitsS6dWQ5gfMTZdorpAAzEH.jpg','https://www.youtube.com/watch?v=BmllggGO4pM','oklch(0.28 0.06 220)','oklch(0.15 0.03 230)','oklch(0.82 0.10 220)','published'),
(9,'society-of-the-snow','Society of the Snow',2023,144,'R','J.A. Bayona',90,96,'When a plane crashes high in the Andes, the survivors must do the unthinkable to stay alive across 72 days, in one of history''s most harrowing true stories.','To return is just the beginning.','https://image.tmdb.org/t/p/w500/2e853FDVSIso600RqAMunPxiZjq.jpg','https://www.youtube.com/watch?v=pDak4qLyF4Q','oklch(0.30 0.10 30)','oklch(0.16 0.05 40)','oklch(0.78 0.15 40)','published'),
(10,'killers-of-the-flower-moon','Killers of the Flower Moon',2023,206,'R','Martin Scorsese',93,84,'Members of the Osage Nation are murdered under mysterious circumstances in 1920s Oklahoma, sparking a major early FBI investigation into a sprawling conspiracy.','Built on betrayal.','https://image.tmdb.org/t/p/w500/dB6Krk806zeqd0YNp2ngQ9zXteH.jpg','https://www.youtube.com/watch?v=EP34Yoxs3FQ','oklch(0.31 0.08 150)','oklch(0.17 0.04 160)','oklch(0.84 0.12 150)','published'),
(11,'napoleon','Napoleon',2023,158,'R','Ridley Scott',60,63,'An epic that details the swift, ruthless rise of Napoleon Bonaparte through the lens of his addictive and volatile relationship with the empress Josephine.','He came from nothing. He conquered everything.','https://image.tmdb.org/t/p/w500/jE5o7y9K6pZtWNNMEw3IdpHuncR.jpg','https://www.youtube.com/watch?v=OAZWXUkrjPc','oklch(0.27 0.07 320)','oklch(0.14 0.04 330)','oklch(0.80 0.13 320)','published'),
(12,'saltburn','Saltburn',2023,131,'R','Emerald Fennell',71,77,'A scholarship student becomes captivated by an aristocratic classmate and is drawn into his family''s sprawling estate for a summer that will never be forgotten.','This summer, everyone wants a little taste.','https://image.tmdb.org/t/p/w500/zGTfMwG112BC66mpaveVxoWPOaB.jpg','https://www.youtube.com/watch?v=lALMdJf6UUE','oklch(0.30 0.07 90)','oklch(0.16 0.04 80)','oklch(0.84 0.13 90)','published'),
(13,'the-tomorrow-war','The Tomorrow War',2021,138,'PG-13','Chris McKay',52,79,'A family man is drafted to fight in a future war where the fate of humanity relies on his ability to confront a terrifying alien threat alongside the next generation.','The future needs you.','https://image.tmdb.org/t/p/w500/34nDCQZwaEvsy4CFO5hkGRFDCVU.jpg','https://www.youtube.com/watch?v=QPistcpGB8o','oklch(0.32 0.09 264)','oklch(0.18 0.05 280)','oklch(0.80 0.13 70)','published'),
(14,'oppenheimer','Oppenheimer',2023,180,'R','Christopher Nolan',93,91,'The story of J. Robert Oppenheimer and his pivotal role in the development of the atomic bomb during World War II, and the moral reckoning that followed.','The world forever changes.','https://image.tmdb.org/t/p/w500/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg','https://www.youtube.com/watch?v=uYPbbksJxIg','oklch(0.28 0.05 40)','oklch(0.14 0.03 50)','oklch(0.82 0.14 60)','published'),

(15,'poor-things','Poor Things',2023,141,'R','Yorgos Lanthimos',91,78,'Bella Baxter is brought back to life by a brilliant scientist and sets out on a wild journey across Europe, determined to learn everything she can about the world.','Dare to explore.','https://image.tmdb.org/t/p/w500/kCGlIMHnOm8JPXq3rXM6c5wMxcT.jpg','https://www.youtube.com/watch?v=RlbR5N6veqw','oklch(0.30 0.09 180)','oklch(0.16 0.05 190)','oklch(0.80 0.13 180)','published'),

(16,'the-zone-of-interest','The Zone of Interest',2023,105,'PG-13','Jonathan Glazer',93,68,'A Nazi commandant and his wife build their dream life next to Auschwitz, in a chilling examination of mundane evil and willful blindness to atrocity.','Happiness is a choice.','https://image.tmdb.org/t/p/w500/hUu9zyZmKuTLpmKzosKGZiQFsgc.jpg','https://www.youtube.com/watch?v=uo7_dOIBCjg','oklch(0.24 0.04 90)','oklch(0.12 0.02 100)','oklch(0.78 0.10 100)','published'),

(17,'anatomy-of-a-fall','Anatomy of a Fall',2023,150,'R','Justine Triet',96,82,'A writer is suspected of her husband''s murder when his body is discovered at their remote Alpine chalet, setting off a riveting courtroom investigation.','The truth is complicated.','https://image.tmdb.org/t/p/w500/tpQnDeHY15To3o4Aw5HmIxrpEBo.jpg','https://www.youtube.com/watch?v=GGnAkpnSBBs','oklch(0.29 0.06 220)','oklch(0.15 0.03 230)','oklch(0.82 0.10 220)','published'),

(18,'maestro','Maestro',2023,129,'R','Bradley Cooper',78,81,'A look at the life and music of Leonard Bernstein, focusing on his complex 25-year marriage to actress Felicia Montealegre and his rise as America''s greatest conductor.','A love story.','https://image.tmdb.org/t/p/w500/qOVFjDwDSUj7lWJGKhjzPM1vkup.jpg','https://www.youtube.com/watch?v=t7DUxbq6dUo','oklch(0.27 0.05 320)','oklch(0.13 0.03 330)','oklch(0.80 0.10 320)','published'),

(19,'american-fiction','American Fiction',2023,117,'R','Cord Jefferson',90,88,'A frustrated novelist adopts a pen name to write a satirical "Black" book that becomes a surprise bestseller, exposing the absurdity of racial stereotyping in publishing.','Write what they want.','https://image.tmdb.org/t/p/w500/peFHDJoB9AOMYtBJkEAlbf4EMGM.jpg','https://www.youtube.com/watch?v=AoQDpzSokE0','oklch(0.30 0.08 30)','oklch(0.16 0.04 40)','oklch(0.78 0.14 30)','published'),

(20,'past-lives','Past Lives',2023,106,'PG-13','Celine Song',96,91,'Childhood sweethearts Nora and Hae Sung reconnect after 24 years apart, forcing each to confront what their lives could have been — and the love they left behind.','Some things can''t be undone.','https://image.tmdb.org/t/p/w500/k3waqVXSnQKEgAqpwqFoWGVMCmR.jpg','https://www.youtube.com/watch?v=E_ikTCEWvC8','oklch(0.28 0.07 250)','oklch(0.14 0.04 260)','oklch(0.80 0.12 250)','published'),

(21,'priscilla','Priscilla',2023,113,'R','Sofia Coppola',74,68,'The story of Priscilla Presley''s life with Elvis, tracing how a sheltered teenager became the wife of the world''s biggest star — and ultimately found the courage to leave.','Her story.','https://image.tmdb.org/t/p/w500/rLnjSEGCL1FHxhN5CaAnTamIaGo.jpg','https://www.youtube.com/watch?v=Yd3LG1pI31Y','oklch(0.30 0.07 10)','oklch(0.16 0.03 20)','oklch(0.80 0.10 10)','published'),

(22,'the-holdovers','The Holdovers',2023,133,'R','Alexander Payne',88,95,'A crusty history teacher at a New England boarding school is forced to babysit the few students who can''t go home over the Christmas break, and ends up forming an unlikely bond.','Everyone deserves a second chance.','https://image.tmdb.org/t/p/w500/VHmqSLpKBer8GGoSRjJQYEe2O1.jpg','https://www.youtube.com/watch?v=AhKEpHrFEEg','oklch(0.29 0.06 40)','oklch(0.14 0.03 50)','oklch(0.82 0.12 40)','published'),

(23,'wonka','Wonka',2023,116,'PG','Paul King',83,91,'A young Willy Wonka arrives in a new city with dreams, a big hat, and a suitcase of magical chocolates, determined to open the world''s most extraordinary chocolate shop.','Every good thing in this world started with a dream.','https://image.tmdb.org/t/p/w500/qhb1qOilapbapxWQn9jtRkFAiNT.jpg','https://www.youtube.com/watch?v=otNh9bTjXWg','oklch(0.30 0.10 280)','oklch(0.16 0.05 290)','oklch(0.80 0.14 280)','published'),

(24,'aquaman-and-the-lost-kingdom','Aquaman and the Lost Kingdom',2023,124,'PG-13','James Wan',33,82,'Aquaman must forge an uneasy alliance with his imprisoned brother Orm to protect Atlantis and the world from a powerful ancient force.','The ocean is not what it seems.','https://image.tmdb.org/t/p/w500/7lTnXOy0iNtBAdRP3TZvaKJ77F6.jpg','https://www.youtube.com/watch?v=6UoHFHxFV0g','oklch(0.29 0.08 200)','oklch(0.15 0.04 210)','oklch(0.80 0.12 200)','published'),

(25,'anyone-but-you','Anyone But You',2023,103,'R','Will Gluck',43,86,'Two seemingly perfect strangers discover that their initial attraction was a mistake — but must fake being a couple at a friend''s Australian wedding, leading to real feelings.','Sometimes opposites just need a second chance.','https://image.tmdb.org/t/p/w500/lurEK87kukWNaHd0zYnsi3yzJrs.jpg','https://www.youtube.com/watch?v=RaBTfGSH-7E','oklch(0.28 0.08 330)','oklch(0.14 0.04 340)','oklch(0.82 0.12 330)','published'),

(26,'migration','Migration',2023,83,'PG','Benjamin Renner',71,89,'A family of ducks convinces their overprotective father to embark on an epic migration with other fowl, leading them on a wild adventure across Jamaica.','The world is bigger than your pond.','https://image.tmdb.org/t/p/w500/ldfCF9RhR40mppkzmftxapaHeTo.jpg','https://www.youtube.com/watch?v=GpJBq_ZHNdo','oklch(0.30 0.09 150)','oklch(0.16 0.05 160)','oklch(0.80 0.14 150)','published'),

(27,'the-beekeeper','The Beekeeper',2024,105,'R','David Ayer',68,88,'A beekeeper''s powerful past is revealed when he discovers those he cares for have been victimized by a ruthless crime syndicate, unleashing an unstoppable response.','His hive. His rules.','https://image.tmdb.org/t/p/w500/A7EByudX0eOzlkQ2FIbogzyazm2.jpg','https://www.youtube.com/watch?v=dKzG9xGLYAI','oklch(0.29 0.07 50)','oklch(0.15 0.03 60)','oklch(0.82 0.13 50)','published'),

(28,'argylle','Argylle',2024,139,'PG-13','Matthew Vaughn',33,57,'A reclusive spy novelist is thrust into the world of real espionage when she discovers that her fictional stories have begun to mirror actual covert operations.','The world''s greatest spy never existed.','https://image.tmdb.org/t/p/w500/95BVMbzJ6fHNL33XSRE0HFXrLK1.jpg','https://www.youtube.com/watch?v=F0L8fQWNy90','oklch(0.28 0.06 320)','oklch(0.14 0.03 330)','oklch(0.80 0.12 320)','published'),

(29,'dune-prophecy-prequel','Monkey Man',2024,113,'R','Dev Patel',82,90,'An anonymous young man seeks revenge on the corrupt leaders who murdered his mother by slowly building a criminal career in a seedy underground fighting club.','They will know his name.','https://image.tmdb.org/t/p/w500/95BVMbzJ6fHNL33XSRE0HFXrLK1.jpg','https://www.youtube.com/watch?v=SXrjsicDLGE','oklch(0.28 0.07 30)','oklch(0.14 0.04 40)','oklch(0.80 0.12 30)','published'),

(30,'civil-war','Civil War',2024,109,'R','Alex Garland',82,74,'A team of war journalists travel across a fractured America during a second civil war, documenting the conflict while struggling to stay alive and impartial.','What side are you on?','https://image.tmdb.org/t/p/w500/sh7Rg8Er3tFcN9BpKIPU9Mpo8FH.jpg','https://www.youtube.com/watch?v=pM6P6PSi6gI','oklch(0.26 0.04 200)','oklch(0.13 0.02 210)','oklch(0.82 0.08 200)','published'),

(31,'challengers','Challengers',2024,131,'R','Luca Guadagnino',87,82,'A former tennis prodigy turned coach watches as her husband and ex-boyfriend battle it out on the court — and in life — in a tense romantic triangle.','Love is a competition.','https://image.tmdb.org/t/p/w500/H6oFyDz7pjBBNQoOgSwi4MH1jFW.jpg','https://www.youtube.com/watch?v=SZCFDfcMhRs','oklch(0.30 0.09 40)','oklch(0.16 0.05 50)','oklch(0.80 0.14 40)','published'),

(32,'i-saw-the-tv-glow','I Saw the TV Glow',2024,100,'PG-13','Jane Schoenbrun',82,62,'Two teenagers bond over a beloved supernatural television show, and when it suddenly disappears from the air, one of them is pulled into a world between reality and fiction.','Something''s wrong with the TV.','https://image.tmdb.org/t/p/w500/qn0WIY7XKdz7RYGsUVoC1TaZFHY.jpg','https://www.youtube.com/watch?v=SBhSQ-j-wYg','oklch(0.26 0.06 280)','oklch(0.13 0.03 290)','oklch(0.78 0.12 280)','published'),

(33,'furiosa','Furiosa: A Mad Max Saga',2024,148,'R','George Miller',90,84,'The origin story of Furiosa, from her capture by the biker horde of Dementus to her quest for revenge and her journey back home.','Witness her.','https://image.tmdb.org/t/p/w500/iADOJ8Zymht2JPMoy3R7xceZpRC.jpg','https://www.youtube.com/watch?v=XJMuhwVlca4','oklch(0.28 0.06 30)','oklch(0.14 0.03 40)','oklch(0.82 0.11 30)','published'),

(34,'hit-man','Hit Man',2024,115,'R','Richard Linklater',96,90,'A mild-mannered professor moonlights as a fake hitman for police sting operations until a woman asks him to kill her abusive husband — and he falls for her instead.','For hire. Not for killing.','https://image.tmdb.org/t/p/w500/1126gjZ4Q8ub4NhHNnPHg8pciEN.jpg','https://www.youtube.com/watch?v=H8Nh2nPHI4s','oklch(0.30 0.08 200)','oklch(0.16 0.04 210)','oklch(0.80 0.13 200)','published'),

(35,'longlegs','Longlegs',2024,101,'R','Osgood Perkins',68,52,'An FBI agent in the 1990s is tasked with catching a serial killer who leaves cryptic notes linked to the occult, and discovers he may be connected to her own past.','He leaves no trace.','https://image.tmdb.org/t/p/w500/hnHNSJELCQiIAiS0SJbxKQ3IlnU.jpg','https://www.youtube.com/watch?v=mOkBt2xBVmY','oklch(0.24 0.04 260)','oklch(0.12 0.02 270)','oklch(0.78 0.10 260)','published'),

(36,'alien-romulus','Alien: Romulus',2024,119,'R','Fede Álvarez',81,84,'A group of young colonists scavenging a derelict space station discover the most terrifying life form in the universe — Xenomorphs — in an isolated corner of space.','Do not look away.','https://image.tmdb.org/t/p/w500/b33nnKl1GSFbao4l3fZDDqsMx0F.jpg','https://www.youtube.com/watch?v=YA9JzLLkNF0','oklch(0.24 0.05 220)','oklch(0.12 0.03 230)','oklch(0.80 0.10 220)','published'),

(37,'deadpool-and-wolverine','Deadpool & Wolverine',2024,128,'R','Shawn Levy',79,94,'Deadpool is recruited by the TVA and joins forces with a reluctant Wolverine to stop a global threat — in what becomes the wildest team-up in Marvel history.','Save the universe. Wear the suit.','https://image.tmdb.org/t/p/w500/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg','https://www.youtube.com/watch?v=73_1biulkYk','oklch(0.30 0.10 10)','oklch(0.16 0.05 20)','oklch(0.80 0.15 10)','published'),

(38,'alien-romulus-2','Twisters',2024,122,'PG-13','Lee Isaac Chung',75,90,'A group of storm chasers ventures into Oklahoma Tornado Alley to test a new experimental system for stopping the deadly storms, facing increasingly powerful twisters.','Nature fights back.','https://image.tmdb.org/t/p/w500/2qbMcAzaHaMvCFMfFwIMf0PTJBS.jpg','https://www.youtube.com/watch?v=pJSg2rS_sMg','oklch(0.28 0.06 200)','oklch(0.14 0.03 210)','oklch(0.82 0.12 200)','published'),

(39,'speak-no-evil','Speak No Evil',2024,111,'R','James Watkins',82,73,'A couple accepts an invitation to spend the weekend with another couple they met on holiday, but discover their hosts have disturbing intentions.','Don''t say what you''re thinking.','https://image.tmdb.org/t/p/w500/siB5m6aXN9GxRJhBfvYxkQ6T3di.jpg','https://www.youtube.com/watch?v=4dPjYjT4EoA','oklch(0.26 0.05 150)','oklch(0.13 0.03 160)','oklch(0.78 0.10 150)','published'),

(40,'transformers-one','Transformers One',2024,104,'PG','Josh Cooley',81,89,'The origin story of Optimus Prime and Megatron, two Cybertronians who were once close friends before they became arch-enemies.','Before the war, there was a friendship.','https://image.tmdb.org/t/p/w500/qbkn2SKiEqKLJMNMylL6cFyiipk.jpg','https://www.youtube.com/watch?v=gqf9CK_GNME','oklch(0.30 0.09 220)','oklch(0.16 0.05 230)','oklch(0.80 0.13 220)','published'),

(41,'wicked','Wicked',2024,160,'PG','Jon M. Chu',89,90,'The untold story of the witches of Oz — Elphaba and Glinda — before Dorothy''s arrival: two unlikely friends who discover the true meaning of power, love, and identity.','Defy gravity.','https://image.tmdb.org/t/p/w500/xDGbZ0JJ3mYaGKy4Nzd9Kph6SCQ.jpg','https://www.youtube.com/watch?v=6COmYeLsz4c','oklch(0.29 0.08 130)','oklch(0.15 0.04 140)','oklch(0.80 0.12 130)','published'),

(42,'gladiator-2','Gladiator II',2024,148,'R','Ridley Scott',70,81,'Lucius, the nephew of Commodus, witnesses the destruction of his home by Roman generals and rises through the ranks of the Colosseum to challenge the corrupt emperors of Rome.','Forged in fire. Tempered by revenge.','https://image.tmdb.org/t/p/w500/2cxhvwyEwRlysAmRH4iodkvo0z5.jpg','https://www.youtube.com/watch?v=aTbxPWlVqgk','oklch(0.29 0.06 40)','oklch(0.15 0.03 50)','oklch(0.82 0.12 40)','published'),

(43,'moana-2','Moana 2',2024,100,'PG','David Derrick Jr.',82,88,'Moana sets out on an epic voyage to discover a distant Motunui island, navigating dangerous new waters and rediscovering ancient forgotten worlds of Oceania.','Beyond the reef, a new world awaits.','https://image.tmdb.org/t/p/w500/4YZpsylmjHbqeWzjKpUEF8gcLNW.jpg','https://www.youtube.com/watch?v=EBrJLakp6TE','oklch(0.29 0.08 200)','oklch(0.15 0.04 210)','oklch(0.80 0.13 200)','published'),

(44,'conclave','Conclave',2024,120,'PG','Edward Berger',91,81,'After the death of the Pope, one of the world''s most secretive and powerful events begins — the election of a new Pope — and a cardinal discovers a shocking secret.','The truth has a price.','https://image.tmdb.org/t/p/w500/m9EtP1Yrzv6v7dMaC9mRaGhd9tI.jpg','https://www.youtube.com/watch?v=aFCJhMbHGI0','oklch(0.27 0.05 40)','oklch(0.13 0.03 50)','oklch(0.82 0.10 40)','published'),

(45,'the-substance','The Substance',2024,140,'R','Coralie Fargeat',86,81,'A fading celebrity discovers a black-market drug that creates a younger, better version of herself — but the substance comes with terrifying side effects.','A new you. At any cost.','https://image.tmdb.org/t/p/w500/lqoMzCcZYEFK729d6qzt349fB4o.jpg','https://www.youtube.com/watch?v=pWuNQwJ4cJU','oklch(0.28 0.06 320)','oklch(0.14 0.03 330)','oklch(0.80 0.12 320)','published'),

(46,'anora','Anora',2024,139,'R','Sean Baker',96,84,'A young sex worker from Brooklyn marries the son of a Russian oligarch and finds her fantasy quickly unravels when his parents arrive to have the marriage annulled.','Cinderella, but not.','https://image.tmdb.org/t/p/w500/3zNGPjMSQ50vbBBUVFfpA7OAVpD.jpg','https://www.youtube.com/watch?v=EYg70VJzJrI','oklch(0.29 0.07 290)','oklch(0.15 0.04 300)','oklch(0.80 0.11 290)','published'),

(47,'nosferatu','Nosferatu',2024,132,'R','Robert Eggers',83,79,'A young woman''s obsession with a mysterious, ancient nobleman unleashes a terrifying plague when he follows her to Germany from Transylvania.','A shadow grows.','https://image.tmdb.org/t/p/w500/5qGIxdEO841C0tdY8vKDyKzkMa7.jpg','https://www.youtube.com/watch?v=q3fKHXRjPKg','oklch(0.22 0.04 260)','oklch(0.10 0.02 270)','oklch(0.78 0.10 260)','published'),

(48,'mufasa-the-lion-king','Mufasa: The Lion King',2024,118,'PG','Barry Jenkins',73,85,'A prequel to The Lion King that tells the origin story of Mufasa, how he came to be king, and the brother who betrayed him.','Before the kingdom, there was a king.','https://image.tmdb.org/t/p/w500/mB9NNMbZKnxkHfW1JcGj59mCGHK.jpg','https://www.youtube.com/watch?v=kFIpTFOBkjg','oklch(0.29 0.08 50)','oklch(0.15 0.04 60)','oklch(0.80 0.13 50)','published'),

(49,'sonic-the-hedgehog-3','Sonic the Hedgehog 3',2024,110,'PG','Jeff Fowler',77,90,'Sonic, Knuckles, and Tails face their greatest challenge yet when Shadow the Hedgehog — a powerful new adversary — is unleashed upon the world.','Faster than ever.','https://image.tmdb.org/t/p/w500/d8Ryb8AunYAuycVKDp5HpdWPKgC.jpg','https://www.youtube.com/watch?v=JnFhFWZ_A1I','oklch(0.30 0.09 180)','oklch(0.16 0.05 190)','oklch(0.80 0.13 180)','published'),

(50,'a-complete-unknown','A Complete Unknown',2024,140,'R','James Mangold',83,86,'The early years of Bob Dylan''s rise from obscurity, from his arrival in New York City in 1961 to his shocking electric performance at the Newport Folk Festival in 1965.','They''d never seen anything like him.','https://image.tmdb.org/t/p/w500/plFZCFKEG6dxrN8bwfEfAGJBfMh.jpg','https://www.youtube.com/watch?v=7s3kBHCX1xY','oklch(0.27 0.05 40)','oklch(0.13 0.03 50)','oklch(0.82 0.10 40)','published'),

(51,'babygirl','Babygirl',2024,114,'R','Halina Reijn',74,68,'A high-powered CEO risks her career, marriage, and reputation when she begins a torrid affair with her much younger intern, who tests the limits of their dangerous dynamic.','Some risks are worth taking.','https://image.tmdb.org/t/p/w500/A25GiJX7hILKGq1ym38lBHO3OJq.jpg','https://www.youtube.com/watch?v=VAnTEOhxKmc','oklch(0.28 0.06 300)','oklch(0.14 0.03 310)','oklch(0.80 0.12 300)','published'),

(52,'nickel-boys','Nickel Boys',2024,140,'R','RaMell Ross',98,82,'Two young Black men at a reform school in the segregated South forge a friendship that transforms their lives forever, as one becomes a historian haunted by what happened there.','They could not forget.','https://image.tmdb.org/t/p/w500/oPtcQZBRFCGx2VLjvnuN5bNOd1r.jpg','https://www.youtube.com/watch?v=aFOAfSQ9EoU','oklch(0.27 0.05 40)','oklch(0.13 0.03 50)','oklch(0.82 0.10 40)','published'),

(53,'mid90s-2024','September 5',2024,94,'R','Tim Fehlbaum',96,79,'The untold story of the ABC Sports team that covered the 1972 Munich Olympics massacre live as it unfolded, a pivotal moment in live television news history.','The world watched.','https://image.tmdb.org/t/p/w500/iLDMJfWXkU1ydQBqMDXuN5MEFH4.jpg','https://www.youtube.com/watch?v=G_6tpFUzLG8','oklch(0.27 0.05 200)','oklch(0.13 0.03 210)','oklch(0.82 0.10 200)','published'),

(54,'captain-america-brave-new-world','Captain America: Brave New World',2025,118,'PG-13','Julius Onah',67,76,'Sam Wilson steps into the role of Captain America and finds himself in the middle of an international incident, uncovering a global conspiracy that puts his new identity to the test.','A new hero for a new world.','https://image.tmdb.org/t/p/w500/pzIddUEMWhWzfvLI3TwxUG2wGoi.jpg','https://www.youtube.com/watch?v=RlzRuCFa6vE','oklch(0.29 0.07 210)','oklch(0.15 0.04 220)','oklch(0.80 0.12 210)','published'),

(55,'snow-white-2025','Snow White',2025,110,'PG','Marc Webb',42,32,'A live-action reimagining of the classic fairy tale, following the young princess as she overcomes a tyrannical stepmother and discovers her own strength.','The fairest of them all.','https://image.tmdb.org/t/p/w500/oMi1HVUaFBMmHhTHgidLjSqh3Sn.jpg','https://www.youtube.com/watch?v=4Sji7RK8tCw','oklch(0.30 0.08 10)','oklch(0.16 0.04 20)','oklch(0.80 0.12 10)','published'),

(56,'mission-impossible-8','Mission: Impossible – The Final Reckoning',2025,169,'PG-13','Christopher McQuarrie',90,95,'Ethan Hunt embarks on the most dangerous mission of his career to stop a powerful AI entity from taking control of all global intelligence systems.','Every mission ends here.','https://image.tmdb.org/t/p/w500/b3s6QDo1BTPNZK43vMAjTBGLGgj.jpg','https://www.youtube.com/watch?v=7gKGFJSVX4E','oklch(0.26 0.05 220)','oklch(0.13 0.03 230)','oklch(0.82 0.10 220)','published'),

(57,'sinners','Sinners',2025,137,'R','Ryan Coogler',97,96,'Twin brothers return to the Jim Crow South to start fresh, only to discover that the town they left behind harbors a supernatural evil more dangerous than anything they fled.','Blood calls to blood.','https://image.tmdb.org/t/p/w500/yh64qwwGtoUWhMmEXBmHOo9lbkt.jpg','https://www.youtube.com/watch?v=gqHEEFsm0P8','oklch(0.26 0.06 30)','oklch(0.13 0.03 40)','oklch(0.82 0.12 30)','published'),

(58,'thunderbolts','Thunderbolts*',2025,127,'PG-13','Jake Schreier',76,88,'A group of Marvel''s most dangerous and morally compromised antiheroes are brought together to face a threat that may destroy the world — if they don''t destroy each other first.','The worst of the best.','https://image.tmdb.org/t/p/w500/mEBEJbFgXJUXVGsYHiEQ8CXPlLG.jpg','https://www.youtube.com/watch?v=kMEvSKVxfhM','oklch(0.28 0.06 220)','oklch(0.14 0.03 230)','oklch(0.80 0.12 220)','published'),

(59,'novocaine','Novocaine',2025,110,'R','Dan Berk',72,82,'A mild-mannered office worker with a rare condition that makes him feel no pain discovers he''s uniquely suited to take on the criminals who kidnapped his girlfriend.','Pain is overrated.','https://image.tmdb.org/t/p/w500/4v4aMKEurqwvDhXfk7UrF5yXFR2.jpg','https://www.youtube.com/watch?v=pN_OEXkIHGk','oklch(0.28 0.06 30)','oklch(0.14 0.03 40)','oklch(0.80 0.11 30)','published'),

(60,'warfare','Warfare',2025,95,'R','Alex Garland',92,90,'A visceral, real-time account of a Navy SEAL platoon''s harrowing mission in Ramadi, Iraq in 2006, told through the unfiltered experiences of the soldiers on the ground.','Real soldiers. Real stories.','https://image.tmdb.org/t/p/w500/sFJNgubHaUdoZSqoTj3kADMEEXM.jpg','https://www.youtube.com/watch?v=HNXNO87Gdrk','oklch(0.24 0.04 100)','oklch(0.12 0.02 110)','oklch(0.80 0.08 100)','published'),

(61,'mickey-17','Mickey 17',2025,137,'R','Bong Joon-ho',86,82,'A disposable employee on a dangerous space mission allows himself to be killed over and over again to serve his crew, until he comes back from the dead to find his replacement already walking around.','Dying is easy. Coming back is hard.','https://image.tmdb.org/t/p/w500/hWpZnQyWlaTJVGhyXRjd4FE9TPC.jpg','https://www.youtube.com/watch?v=ZYIMJ5_ZQUI','oklch(0.28 0.07 220)','oklch(0.14 0.04 230)','oklch(0.80 0.12 220)','published'),

(62,'final-destination-bloodlines','Final Destination: Bloodlines',2025,110,'R','Zach Lipovsky',82,89,'A college student haunted by recurring visions of a deadly disaster that kills her family must work to unravel the terrifying mystery connected to a decades-old tragedy.','Death has a new design.','https://image.tmdb.org/t/p/w500/6WxhEvFsauuACfv8HyoVX6mZKFj.jpg','https://www.youtube.com/watch?v=Zt1ixr5BSkw','oklch(0.24 0.05 10)','oklch(0.12 0.03 20)','oklch(0.82 0.11 10)','published'),

(63,'jurassic-world-rebirth','Jurassic World Rebirth',2025,119,'PG-13','Gareth Edwards',78,84,'Five years after the fall of Biosyn, a covert operations team journeys deep into the jungles of a remote island to retrieve genetic material from the three largest dinosaurs ever to walk the earth.','The world remembers.','https://image.tmdb.org/t/p/w500/wRQCgKKYmCNw0I2SN9PJHmSR2F4.jpg','https://www.youtube.com/watch?v=BudaLXxUqHk','oklch(0.28 0.07 130)','oklch(0.14 0.04 140)','oklch(0.80 0.12 130)','published');

INSERT INTO `movie_genres` (`movie_id`,`genre_id`) VALUES
(1,1),(1,2),(2,3),(2,4),(3,5),(3,2),(4,3),(4,1),(5,6),(5,5),(6,1),(6,2),(7,7),(7,5),(8,3),(8,8),(9,9),(9,10),(10,4),(10,9),(11,9),(11,10),(12,9),(12,8),(13,3),(13,1),
-- Oppenheimer (Drama, History)
(14,9),(14,10),
-- Poor Things (Sci-Fi, Comedy)
(15,1),(15,5),
-- Zone of Interest (Drama, History)
(16,9),(16,10),
-- Anatomy of a Fall (Mystery, Drama)
(17,7),(17,9),
-- Maestro (Drama, Romance)
(18,9),(18,11),
-- American Fiction (Comedy, Drama)
(19,5),(19,9),
-- Past Lives (Romance, Drama)
(20,11),(20,9),
-- Priscilla (Drama, Romance)
(21,9),(21,11),
-- The Holdovers (Comedy, Drama)
(22,5),(22,9),
-- Wonka (Adventure, Comedy)
(23,2),(23,5),
-- Aquaman 2 (Action, Adventure)
(24,3),(24,2),
-- Anyone But You (Romance, Comedy)
(25,11),(25,5),
-- Migration (Animation, Adventure)
(26,6),(26,2),
-- The Beekeeper (Action, Thriller)
(27,3),(27,8),
-- Argylle (Action, Comedy)
(28,3),(28,5),
-- Monkey Man (Action, Drama)
(29,3),(29,9),
-- Civil War (Thriller, Drama)
(30,8),(30,9),
-- Challengers (Drama, Romance)
(31,9),(31,11),
-- I Saw the TV Glow (Horror, Sci-Fi)
(32,13),(32,1),
-- Furiosa (Action, Adventure)
(33,3),(33,2),
-- Hit Man (Comedy, Crime)
(34,5),(34,4),
-- Longlegs (Horror, Thriller)
(35,13),(35,8),
-- Alien: Romulus (Horror, Sci-Fi)
(36,13),(36,1),
-- Deadpool & Wolverine (Action, Comedy)
(37,3),(37,5),
-- Twisters (Action, Adventure)
(38,3),(38,2),
-- Speak No Evil (Horror, Thriller)
(39,13),(39,8),
-- Transformers One (Animation, Action)
(40,6),(40,3),
-- Wicked (Musical, Fantasy)
(41,16),(41,15),
-- Gladiator II (Action, Drama)
(42,3),(42,9),
-- Moana 2 (Animation, Adventure)
(43,6),(43,2),
-- Conclave (Drama, Mystery)
(44,9),(44,7),
-- The Substance (Horror, Sci-Fi)
(45,13),(45,1),
-- Anora (Drama, Comedy)
(46,9),(46,5),
-- Nosferatu (Horror, Thriller)
(47,13),(47,8),
-- Mufasa (Animation, Adventure)
(48,6),(48,2),
-- Sonic 3 (Action, Comedy)
(49,3),(49,5),
-- A Complete Unknown (Drama, History)
(50,9),(50,10),
-- Babygirl (Drama, Thriller)
(51,9),(51,8),
-- Nickel Boys (Drama, History)
(52,9),(52,10),
-- September 5 (Drama, History)
(53,9),(53,10),
-- Captain America (Action, Sci-Fi)
(54,3),(54,1),
-- Snow White (Fantasy, Adventure)
(55,15),(55,2),
-- Mission Impossible (Action, Thriller)
(56,3),(56,8),
-- Sinners (Horror, Drama)
(57,13),(57,9),
-- Thunderbolts (Action, Sci-Fi)
(58,3),(58,1),
-- Novocaine (Action, Comedy)
(59,3),(59,5),
-- Warfare (Action, Drama)
(60,3),(60,9),
-- Mickey 17 (Sci-Fi, Comedy)
(61,1),(61,5),
-- Final Destination: Bloodlines (Horror, Thriller)
(62,13),(62,8),
-- Jurassic World Rebirth (Action, Adventure)
(63,3),(63,2);

INSERT INTO `cast_members` (`movie_id`,`name`,`display_order`) VALUES
(1,'Timothée Chalamet',0),
(1,'Zendaya',1),
(1,'Rebecca Ferguson',2),
(1,'Austin Butler',3),
(2,'Robert Pattinson',0),
(2,'Zoë Kravitz',1),
(2,'Paul Dano',2),
(2,'Colin Farrell',3),
(3,'Margot Robbie',0),
(3,'Ryan Gosling',1),
(3,'America Ferrera',2),
(4,'Chris Pratt',0),
(4,'Zoe Saldaña',1),
(4,'Bradley Cooper',2),
(4,'Karen Gillan',3),
(5,'Amy Poehler',0),
(5,'Maya Hawke',1),
(5,'Lewis Black',2),
(6,'Sam Worthington',0),
(6,'Zoe Saldaña',1),
(6,'Sigourney Weaver',2),
(7,'Daniel Craig',0),
(7,'Edward Norton',1),
(7,'Janelle Monáe',2),
(7,'Kate Hudson',3),
(8,'Ryan Gosling',0),
(8,'Chris Evans',1),
(8,'Ana de Armas',2),
(9,'Enzo Vogrincic',0),
(9,'Matías Recalt',1),
(9,'Agustín Pardella',2),
(10,'Leonardo DiCaprio',0),
(10,'Robert De Niro',1),
(10,'Lily Gladstone',2),
(11,'Joaquin Phoenix',0),
(11,'Vanessa Kirby',1),
(11,'Tahar Rahim',2),
(12,'Barry Keoghan',0),
(12,'Jacob Elordi',1),
(12,'Rosamund Pike',2),
(13,'Chris Pratt',0),
(13,'Yvonne Strahovski',1),
(13,'J.K. Simmons',2),
(14,'Cillian Murphy',0),(14,'Emily Blunt',1),(14,'Matt Damon',2),(14,'Robert Downey Jr.',3),
(15,'Emma Stone',0),(15,'Mark Ruffalo',1),(15,'Willem Dafoe',2),
(16,'Christian Friedel',0),(16,'Sandra Hüller',1),
(17,'Sandra Hüller',0),(17,'Swann Arlaud',1),
(18,'Bradley Cooper',0),(18,'Carey Mulligan',1),(18,'Matt Bomer',2),
(19,'Jeffrey Wright',0),(19,'Tracee Ellis Ross',1),(19,'John Ortiz',2),
(20,'Greta Lee',0),(20,'Teo Yoo',1),(20,'John Magaro',2),
(21,'Cailee Spaeny',0),(21,'Jacob Elordi',1),
(22,'Paul Giamatti',0),(22,'Da''Vine Joy Randolph',1),(22,'Dominic Sessa',2),
(23,'Timothée Chalamet',0),(23,'Olivia Colman',1),(23,'Hugh Grant',2),
(24,'Jason Momoa',0),(24,'Patrick Wilson',1),(24,'Amber Heard',2),
(25,'Sydney Sweeney',0),(25,'Glen Powell',1),
(26,'Kumail Nanjiani',0),(26,'Elizabeth Banks',1),(26,'Danny DeVito',2),
(27,'Jason Statham',0),(27,'Jeremy Irons',1),(27,'Josh Hutcherson',2),
(28,'Bryce Dallas Howard',0),(28,'Henry Cavill',1),(28,'Sam Rockwell',2),
(29,'Dev Patel',0),(29,'Sharlto Copley',1),(29,'Sobhita Dhulipala',2),
(30,'Kirsten Dunst',0),(30,'Wagner Moura',1),(30,'Cailee Spaeny',2),
(31,'Zendaya',0),(31,'Josh O''Connor',1),(31,'Mike Faist',2),
(32,'Justice Smith',0),(32,'Brigette Lundy-Paine',1),
(33,'Anya Taylor-Joy',0),(33,'Chris Hemsworth',1),(33,'Tom Burke',2),
(34,'Glen Powell',0),(34,'Adria Arjona',1),(34,'Austin Amelio',2),
(35,'Maika Monroe',0),(35,'Nicolas Cage',1),(35,'Blair Underwood',2),
(36,'Cailee Spaeny',0),(36,'David Jonsson',1),(36,'Archie Renaux',2),
(37,'Ryan Reynolds',0),(37,'Hugh Jackman',1),(37,'Emma Corrin',2),
(38,'Daisy Edgar-Jones',0),(38,'Glen Powell',1),(38,'Anthony Ramos',2),
(39,'James McAvoy',0),(39,'Mackenzie Davis',1),(39,'Scoot McNairy',2),
(40,'Chris Hemsworth',0),(40,'Brian Tyree Henry',1),(40,'Scarlett Johansson',2),
(41,'Cynthia Erivo',0),(41,'Ariana Grande',1),(41,'Jonathan Bailey',2),
(42,'Paul Mescal',0),(42,'Pedro Pascal',1),(42,'Connie Nielsen',2),
(43,'Auli''i Cravalho',0),(43,'Dwayne Johnson',1),(43,'Rachel House',2),
(44,'Ralph Fiennes',0),(44,'Stanley Tucci',1),(44,'John Lithgow',2),
(45,'Demi Moore',0),(45,'Margaret Qualley',1),(45,'Dennis Quaid',2),
(46,'Yura Borisov',0),(46,'Mikey Madison',1),(46,'Karren Karagulian',2),
(47,'Lily-Rose Depp',0),(47,'Bill Skarsgård',1),(47,'Willem Dafoe',2),
(48,'Aaron Pierre',0),(48,'Kelvin Harrison Jr.',1),(48,'Seth Rogen',2),
(49,'Ben Schwartz',0),(49,'Idris Elba',1),(49,'Keanu Reeves',2),
(50,'Timothée Chalamet',0),(50,'Elle Fanning',1),(50,'Monica Barbaro',2),
(51,'Nicole Kidman',0),(51,'Harris Dickinson',1),(51,'Antonio Banderas',2),
(52,'Ethan Herisse',0),(52,'Brandon Wilson',1),(52,'Hamish Linklater',2),
(53,'Peter Sarsgaard',0),(53,'John Magaro',1),(53,'Ben Chaplin',2),
(54,'Anthony Mackie',0),(54,'Harrison Ford',1),(54,'Danny Ramirez',2),
(55,'Rachel Zegler',0),(55,'Gal Gadot',1),(55,'Andrew Burnap',2),
(56,'Tom Cruise',0),(56,'Hayley Atwell',1),(56,'Ving Rhames',2),
(57,'Michael B. Jordan',0),(57,'Miles Caton',1),(57,'Hailee Steinfeld',2),
(58,'Florence Pugh',0),(58,'Sebastian Stan',1),(58,'David Harbour',2),
(59,'Jack Quaid',0),(59,'Amber Midthunder',1),
(60,'D''Pharaoh Woon-A-Tai',0),(60,'Joseph Quinn',1),(60,'Charles Melton',2),
(61,'Robert Pattinson',0),(61,'Naomi Ackie',1),(61,'Mark Ruffalo',2),
(62,'Teo Briones',0),(62,'Kaitlyn Santa Juana',1),(62,'Tony Todd',2),
(63,'Scarlett Johansson',0),(63,'Mahershala Ali',1),(63,'Jonathan Bailey',2);

INSERT INTO `availability` (`movie_id`,`platform_id`,`kind`,`price_from`,`url`) VALUES
(1,4,'subscription',NULL,'https://play.max.com/movie/8fc22e6a-4339-4de5-8a52-7c7f62975c12'),
(1,3,'rent',3.99,'https://www.amazon.com/Dune-Part-Two/dp/B0CW43YLFV'),
(1,5,'buy',14.99,'https://tv.apple.com/us/movie/dune-part-two/umc.cmc.6wlh48cwt6i4nxg5c4t62ays0'),
(2,4,'subscription',NULL,'https://play.max.com/movie/49d11cef-81a5-4cba-9e84-6f6e3f831e4e'),
(2,3,'rent',3.99,'https://www.amazon.com/Batman-Robert-Pattinson/dp/B09SXVZ8MT'),
(2,5,'buy',14.99,'https://tv.apple.com/us/movie/the-batman/umc.cmc.4rrfq5eamqvai2r7rbafnpfv2'),
(3,4,'subscription',NULL,'https://play.max.com/movie/9e9e0f72-1da4-4e49-9a31-f3e7c46b3a02'),
(3,3,'rent',3.99,'https://www.amazon.com/Barbie-Margot-Robbie/dp/B0C9MNDM8B'),
(3,5,'buy',14.99,'https://tv.apple.com/us/movie/barbie/umc.cmc.3ljjzs0jwduwksmhslpevixkz'),
(4,2,'subscription',NULL,'https://www.disneyplus.com/movies/guardians-of-the-galaxy-vol-3/2NymGFB9fDba'),
(4,3,'rent',3.99,'https://www.amazon.com/Guardians-Galaxy-Vol-Chris-Pratt/dp/B0C4G2QKSP'),
(4,5,'buy',14.99,'https://tv.apple.com/us/movie/guardians-of-the-galaxy-vol-3/umc.cmc.2b7w7hxbyudwk6ziqkwq1zxbf'),
(5,2,'subscription',NULL,'https://www.disneyplus.com/movies/inside-out-2/50ltGqCEYlpX'),
(5,3,'rent',3.99,'https://www.amazon.com/Inside-Out-Amy-Poehler/dp/B0D3BFQMXL'),
(5,5,'buy',14.99,'https://tv.apple.com/us/movie/inside-out-2/umc.cmc.59k53p3srnkp2y15kvnbz5jhe'),
(6,2,'subscription',NULL,'https://www.disneyplus.com/movies/avatar-the-way-of-water/19n21g2FXSCM'),
(6,3,'rent',3.99,'https://www.amazon.com/Avatar-Way-Water-Sam-Worthington/dp/B0BR1NH73F'),
(6,5,'buy',14.99,'https://tv.apple.com/us/movie/avatar-the-way-of-water/umc.cmc.7j2y6zrvzogv9rfhitkbxg5zy'),
(7,1,'subscription',NULL,'https://www.netflix.com/title/81458416'),
(7,3,'rent',3.99,'https://www.amazon.com/Glass-Onion-Knives-Mystery/dp/B0BRX6P5BD'),
(7,5,'buy',14.99,'https://tv.apple.com/us/movie/glass-onion-a-knives-out-mystery/umc.cmc.2rjl0rq0pftb9ixf2uwi9mipz'),
(8,1,'subscription',NULL,'https://www.netflix.com/title/81459665'),
(8,3,'rent',3.99,'https://www.amazon.com/Gray-Man-Ryan-Gosling/dp/B0B5KDKN7Y'),
(8,5,'buy',14.99,'https://tv.apple.com/us/movie/the-gray-man/umc.cmc.4hg5jyudufv7q42q9v81k2z0b'),
(9,1,'subscription',NULL,'https://www.netflix.com/title/81609096'),
(9,3,'rent',3.99,'https://www.amazon.com/Society-Snow-Enzo-Vogrincic/dp/B0CTR7MRJW'),
(9,5,'buy',14.99,'https://tv.apple.com/us/movie/society-of-the-snow/umc.cmc.6dh5v3wh4f4n3e0i6lf7e1e8p'),
(10,5,'subscription',NULL,'https://tv.apple.com/us/movie/killers-of-the-flower-moon/umc.cmc.3x4pcch6bh3n5u9x4j01c5fxq'),
(10,3,'rent',3.99,'https://www.amazon.com/Killers-Flower-Moon-Leonardo-DiCaprio/dp/B0CKW6NRGD'),
(10,3,'buy',14.99,'https://www.amazon.com/Killers-Flower-Moon-Leonardo-DiCaprio/dp/B0CKW6NRGD'),
(11,5,'subscription',NULL,'https://tv.apple.com/us/movie/napoleon/umc.cmc.4c2b3n4k3dqv3vuhh1z7aeep7'),
(11,3,'rent',3.99,'https://www.amazon.com/Napoleon-Joaquin-Phoenix/dp/B0CP7ZXVQP'),
(11,3,'buy',14.99,'https://www.amazon.com/Napoleon-Joaquin-Phoenix/dp/B0CP7ZXVQP'),
(12,3,'subscription',NULL,'https://www.amazon.com/Saltburn-Barry-Keoghan/dp/B0CNMCN73Z'),
(12,5,'buy',14.99,'https://tv.apple.com/us/movie/saltburn/umc.cmc.25q2oap2r0k8z4tlyf3d4cz8a'),
(12,5,'rent',3.99,'https://tv.apple.com/us/movie/saltburn/umc.cmc.25q2oap2r0k8z4tlyf3d4cz8a'),
(13,3,'subscription',NULL,'https://www.amazon.com/Tomorrow-War-Chris-Pratt/dp/B097MWZJBW'),
(13,5,'buy',14.99,'https://tv.apple.com/us/movie/the-tomorrow-war/umc.cmc.6nqq3u0ql9swb6yc4jqd0yh19'),
(13,5,'rent',3.99,'https://tv.apple.com/us/movie/the-tomorrow-war/umc.cmc.6nqq3u0ql9swb6yc4jqd0yh19'),
-- Oppenheimer (Peacock/Netflix/Prime)
(14,1,'subscription',NULL,'https://www.netflix.com/title/81728991'),
(14,3,'rent',3.99,'https://www.amazon.com/Oppenheimer-Cillian-Murphy/dp/B0CFTM6QVC'),
(14,4,'subscription',NULL,'https://play.max.com/movie/oppenheimer'),

-- Poor Things (Disney+/Prime)
(15,2,'subscription',NULL,'https://www.disneyplus.com/movies/poor-things/6DXRH9kfJPVn'),
(15,3,'rent',3.99,'https://www.amazon.com/Poor-Things/dp/B0CR1T3JKV'),

-- Zone of Interest (Prime/Apple TV+)
(16,3,'rent',3.99,'https://www.amazon.com/Zone-of-Interest/dp/B0CW3GX6S4'),
(16,5,'rent',3.99,'https://tv.apple.com/us/movie/the-zone-of-interest/umc.cmc.zone'),

-- Anatomy of a Fall (Netflix/Prime)
(17,1,'subscription',NULL,'https://www.netflix.com/title/81590731'),
(17,3,'rent',3.99,'https://www.amazon.com/Anatomy-Fall/dp/B0CV2KZGHP'),

-- Maestro (Netflix)
(18,1,'subscription',NULL,'https://www.netflix.com/title/81561458'),

-- American Fiction (Prime)
(19,3,'subscription',NULL,'https://www.amazon.com/American-Fiction-Jeffrey-Wright/dp/B0CRXJ1Z4R'),

-- Past Lives (Prime/Apple TV+)
(20,3,'subscription',NULL,'https://www.amazon.com/Past-Lives-Greta-Lee/dp/B0C8BZR7GY'),
(20,5,'buy',14.99,'https://tv.apple.com/us/movie/past-lives/umc.cmc.pastlives'),

-- Priscilla (Apple TV+/Prime)
(21,5,'subscription',NULL,'https://tv.apple.com/us/movie/priscilla/umc.cmc.priscilla'),
(21,3,'rent',3.99,'https://www.amazon.com/Priscilla-Cailee-Spaeny/dp/B0CQP5R2YM'),

-- The Holdovers (Prime/Netflix)
(22,3,'subscription',NULL,'https://www.amazon.com/Holdovers-Paul-Giamatti/dp/B0CRKBQ41R'),
(22,1,'subscription',NULL,'https://www.netflix.com/title/81758296'),

-- Wonka (Netflix/Max)
(23,1,'subscription',NULL,'https://www.netflix.com/title/81686215'),
(23,4,'subscription',NULL,'https://play.max.com/movie/wonka'),

-- Aquaman 2 (Max)
(24,4,'subscription',NULL,'https://play.max.com/movie/aquaman-and-the-lost-kingdom'),
(24,3,'rent',3.99,'https://www.amazon.com/Aquaman-Lost-Kingdom/dp/B0CR1JH5ML'),

-- Anyone But You (Netflix/Prime)
(25,1,'subscription',NULL,'https://www.netflix.com/title/81680839'),
(25,3,'rent',3.99,'https://www.amazon.com/Anyone-But-You/dp/B0CW1L45MK'),

-- Migration (Prime/Apple TV+)
(26,3,'subscription',NULL,'https://www.amazon.com/Migration-Kumail-Nanjiani/dp/B0CSC84SNS'),
(26,5,'buy',14.99,'https://tv.apple.com/us/movie/migration/umc.cmc.migration'),

-- The Beekeeper (Prime/Netflix)
(27,3,'rent',3.99,'https://www.amazon.com/Beekeeper-Jason-Statham/dp/B0CVTFM7RT'),
(27,1,'subscription',NULL,'https://www.netflix.com/title/81758943'),

-- Argylle (Apple TV+)
(28,5,'subscription',NULL,'https://tv.apple.com/us/movie/argylle/umc.cmc.argylle'),

-- Monkey Man (Prime/Netflix)
(29,3,'subscription',NULL,'https://www.amazon.com/Monkey-Man-Dev-Patel/dp/B0CYGM14T8'),
(29,1,'subscription',NULL,'https://www.netflix.com/title/81758301'),

-- Civil War (Prime/Netflix)
(30,3,'subscription',NULL,'https://www.amazon.com/Civil-War-Kirsten-Dunst/dp/B0D1WBW5RZ'),
(30,1,'subscription',NULL,'https://www.netflix.com/title/81758296'),

-- Challengers (Prime/Netflix)
(31,3,'subscription',NULL,'https://www.amazon.com/Challengers-Zendaya/dp/B0D3PRQXKG'),
(31,1,'subscription',NULL,'https://www.netflix.com/title/81733420'),

-- I Saw the TV Glow (Prime/Netflix)
(32,3,'rent',3.99,'https://www.amazon.com/I-Saw-TV-Glow/dp/B0D5PRQXKG'),
(32,1,'subscription',NULL,'https://www.netflix.com/title/81758944'),

-- Furiosa (Netflix/Prime)
(33,1,'subscription',NULL,'https://www.netflix.com/title/81786340'),
(33,3,'rent',3.99,'https://www.amazon.com/Furiosa/dp/B0D5GH1WBK'),

-- Hit Man (Netflix)
(34,1,'subscription',NULL,'https://www.netflix.com/title/81758945'),

-- Longlegs (Prime/Netflix)
(35,3,'rent',3.99,'https://www.amazon.com/Longlegs-Maika-Monroe/dp/B0D9PRQXKG'),
(35,1,'subscription',NULL,'https://www.netflix.com/title/81786341'),

-- Alien: Romulus (Disney+/Prime)
(36,2,'subscription',NULL,'https://www.disneyplus.com/movies/alien-romulus/3TLxBGP2J7L2'),
(36,3,'rent',3.99,'https://www.amazon.com/Alien-Romulus/dp/B0DB4JNTQS'),

-- Deadpool & Wolverine (Disney+/Prime)
(37,2,'subscription',NULL,'https://www.disneyplus.com/movies/deadpool-and-wolverine/3mQSHHt4Klt4'),
(37,3,'rent',3.99,'https://www.amazon.com/Deadpool-Wolverine/dp/B0DB4JNTQS'),

-- Twisters (Netflix/Prime)
(38,1,'subscription',NULL,'https://www.netflix.com/title/81786342'),
(38,3,'rent',3.99,'https://www.amazon.com/Twisters-Daisy-Edgar-Jones/dp/B0DCPRQXKG'),

-- Speak No Evil (Prime/Netflix)
(39,3,'rent',3.99,'https://www.amazon.com/Speak-No-Evil/dp/B0DM4JNTQS'),
(39,1,'subscription',NULL,'https://www.netflix.com/title/81786343'),

-- Transformers One (Prime/Apple TV+)
(40,3,'subscription',NULL,'https://www.amazon.com/Transformers-One/dp/B0DJPRQXKG'),
(40,5,'buy',14.99,'https://tv.apple.com/us/movie/transformers-one/umc.cmc.transformers'),

-- Wicked (Netflix/Prime)
(41,1,'subscription',NULL,'https://www.netflix.com/title/81786344'),
(41,3,'rent',3.99,'https://www.amazon.com/Wicked-Cynthia-Erivo/dp/B0DNPRQXKG'),

-- Gladiator II (Prime/Netflix/Apple TV+)
(42,3,'rent',3.99,'https://www.amazon.com/Gladiator-II/dp/B0DQPRQXKG'),
(42,1,'subscription',NULL,'https://www.netflix.com/title/81786345'),
(42,5,'buy',14.99,'https://tv.apple.com/us/movie/gladiator-ii/umc.cmc.gladiator2'),

-- Moana 2 (Disney+)
(43,2,'subscription',NULL,'https://www.disneyplus.com/movies/moana-2/1HGXmJL4KXYZ'),

-- Conclave (Prime/Netflix)
(44,3,'subscription',NULL,'https://www.amazon.com/Conclave-Ralph-Fiennes/dp/B0DR4JNTQS'),
(44,1,'subscription',NULL,'https://www.netflix.com/title/81786346'),

-- The Substance (MUBI/Prime)
(45,3,'rent',3.99,'https://www.amazon.com/Substance-Demi-Moore/dp/B0DT4JNTQS'),
(45,1,'subscription',NULL,'https://www.netflix.com/title/81786347'),

-- Anora (Prime/Netflix)
(46,3,'subscription',NULL,'https://www.amazon.com/Anora/dp/B0DU4JNTQS'),
(46,1,'subscription',NULL,'https://www.netflix.com/title/81786348'),

-- Nosferatu (Prime/Apple TV+)
(47,3,'rent',3.99,'https://www.amazon.com/Nosferatu-2024/dp/B0DV4JNTQS'),
(47,5,'buy',14.99,'https://tv.apple.com/us/movie/nosferatu/umc.cmc.nosferatu2024'),

-- Mufasa (Disney+/Prime)
(48,2,'subscription',NULL,'https://www.disneyplus.com/movies/mufasa-the-lion-king/mufasa'),
(48,3,'rent',3.99,'https://www.amazon.com/Mufasa-Lion-King/dp/B0DW4JNTQS'),

-- Sonic 3 (Prime/Netflix)
(49,3,'subscription',NULL,'https://www.amazon.com/Sonic-Hedgehog-3/dp/B0DX4JNTQS'),
(49,1,'subscription',NULL,'https://www.netflix.com/title/81786349'),

-- A Complete Unknown (Netflix/Prime)
(50,1,'subscription',NULL,'https://www.netflix.com/title/81786350'),
(50,3,'rent',3.99,'https://www.amazon.com/Complete-Unknown-Dylan/dp/B0DY4JNTQS'),

-- Babygirl (Prime/Netflix)
(51,3,'rent',3.99,'https://www.amazon.com/Babygirl-Nicole-Kidman/dp/B0DZ4JNTQS'),
(51,1,'subscription',NULL,'https://www.netflix.com/title/81786351'),

-- Nickel Boys (Prime/Netflix)
(52,3,'subscription',NULL,'https://www.amazon.com/Nickel-Boys/dp/B0E14JNTQS'),
(52,1,'subscription',NULL,'https://www.netflix.com/title/81786352'),

-- September 5 (Prime/Apple TV+)
(53,3,'rent',3.99,'https://www.amazon.com/September-5-2024/dp/B0E24JNTQS'),
(53,5,'buy',14.99,'https://tv.apple.com/us/movie/september-5/umc.cmc.sept5'),

-- Captain America: Brave New World (Disney+/Prime)
(54,2,'subscription',NULL,'https://www.disneyplus.com/movies/captain-america-brave-new-world/cnabw'),
(54,3,'rent',3.99,'https://www.amazon.com/Captain-America-Brave-New-World/dp/B0E34JNTQS'),

-- Snow White (Disney+/Prime)
(55,2,'subscription',NULL,'https://www.disneyplus.com/movies/snow-white-2025/snowwhite25'),
(55,3,'rent',3.99,'https://www.amazon.com/Snow-White-2025/dp/B0E44JNTQS'),

-- Mission Impossible Final (Prime/Netflix/Apple TV+)
(56,3,'rent',3.99,'https://www.amazon.com/Mission-Impossible-Final-Reckoning/dp/B0E54JNTQS'),
(56,1,'subscription',NULL,'https://www.netflix.com/title/81786353'),
(56,5,'buy',19.99,'https://tv.apple.com/us/movie/mission-impossible-the-final-reckoning/umc.cmc.mi8'),

-- Sinners (Max/Prime)
(57,4,'subscription',NULL,'https://play.max.com/movie/sinners-2025'),
(57,3,'rent',3.99,'https://www.amazon.com/Sinners-2025/dp/B0E64JNTQS'),

-- Thunderbolts (Disney+/Prime)
(58,2,'subscription',NULL,'https://www.disneyplus.com/movies/thunderbolts/thunderbolts2025'),
(58,3,'rent',3.99,'https://www.amazon.com/Thunderbolts/dp/B0E74JNTQS'),

-- Novocaine (Prime/Netflix)
(59,3,'rent',3.99,'https://www.amazon.com/Novocaine-2025/dp/B0E84JNTQS'),
(59,1,'subscription',NULL,'https://www.netflix.com/title/81786354'),

-- Warfare (Prime/Apple TV+)
(60,3,'subscription',NULL,'https://www.amazon.com/Warfare-2025/dp/B0E94JNTQS'),
(60,5,'buy',14.99,'https://tv.apple.com/us/movie/warfare/umc.cmc.warfare2025'),

-- Mickey 17 (Max/Prime)
(61,4,'subscription',NULL,'https://play.max.com/movie/mickey-17'),
(61,3,'rent',3.99,'https://www.amazon.com/Mickey-17/dp/B0EA4JNTQS'),

-- Final Destination: Bloodlines (Max/Prime)
(62,4,'subscription',NULL,'https://play.max.com/movie/final-destination-bloodlines'),
(62,3,'rent',3.99,'https://www.amazon.com/Final-Destination-Bloodlines/dp/B0EB4JNTQS'),

-- Jurassic World Rebirth (Netflix/Prime/Apple TV+)
(63,1,'subscription',NULL,'https://www.netflix.com/title/81786355'),
(63,3,'rent',3.99,'https://www.amazon.com/Jurassic-World-Rebirth/dp/B0EC4JNTQS'),
(63,5,'buy',14.99,'https://tv.apple.com/us/movie/jurassic-world-rebirth/umc.cmc.jwr2025');

INSERT INTO `home_rows` (`id`,`row_key`,`title`,`kicker`,`display_order`,`is_active`) VALUES
(1,'featured','Featured this week','Editor''s pick',1,1),
(2,'new-releases','Fresh on every service','New & notable',2,1),
(3,'critically-acclaimed','Critics can''t stop talking','Top rated',3,1);

INSERT INTO `home_row_movies` (`row_id`,`movie_id`,`display_order`) VALUES
(1,1,0),(1,10,1),(1,3,2),(1,7,3),(1,14,4),(1,37,5),(1,42,6),(1,43,7),(1,49,8),(1,29,9),
(2,5,0),(2,1,1),(2,9,2),(2,12,3),(2,23,4),(2,33,5),(2,38,6),(2,41,7),(2,48,8),(2,56,9),
(3,10,0),(3,1,1),(3,7,2),(3,9,3),(3,5,4),(3,15,5),(3,16,6),(3,17,7),(3,22,8),(3,45,9),(3,46,10),(3,44,11);

INSERT INTO `homepage_settings` (`id`,`hero_movie_id`,`hero_tagline`,`is_published`,`updated_by`) VALUES
(1,'dune-part-two','Long live the fighters.',1,'Curator User');

INSERT INTO `reviews` (`review_key`,`movie_id`,`user_name`,`rating`,`snippet`,`is_flagged`,`status`) VALUES
('rv001',1,'Mara V.',5,'Villeneuve outdid himself — the scale and sound design are overwhelming in the best way.',0,'approved'),
('rv002',2,'Leo T.',4,'Pattinson is a brooding, brilliant Batman. The Riddler genuinely unsettled me.',0,'approved'),
('rv003',8,'anon_4471',1,'Total garbage, whoever greenlit this should be ashamed!!!',1,'pending'),
('rv004',9,'Greer M.',5,'Cried for the last twenty minutes. A devastating, beautiful true story.',0,'pending'),
('rv005',7,'Sam B.',5,'Daniel Craig is having the time of his life. Twistier than the first.',0,'approved');


-- ACTIVITY LOG
INSERT INTO `activity_log` (`actor`,`action`,`target`,`ip_address`) VALUES
('Admin User','published film','Dune: Part Two (2024)','127.0.0.1'),
('Curator User','reordered row','Featured this week','127.0.0.1'),
('Admin User','approved review','rv001 on Dune: Part Two','127.0.0.1'),
('Curator User','flagged review','rv003 on The Gray Man','127.0.0.1'),
('Admin User','updated availability','Glass Onion','127.0.0.1'),
('Curator User','published homepage','Homepage v5','127.0.0.1');

-- ---------------------------------------------------------------------------
-- To add a user MANUALLY (the hash below is for the password "password123"):
--   INSERT INTO `users` (`name`,`email`,`password`,`role`,`display_name`) VALUES
--   ('New Member','member@example.com',
--    '$2y$10$9nsKllnoK0o8Rc.YwgwON.PirYa7hkWHjXsN34CyrHRJTTQdyCVp6','user','New Member');
-- ---------------------------------------------------------------------------

-- CineAll Unified Database Updates (Post-seeding corrections)
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/hUu9zyZmDd8VZegKi1iK1Vk0RYS.jpg' WHERE id = 16;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/1ho0d4LNZw3Y0voeKmSvPSgJOJ2.jpg' WHERE id = 17;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/ckkTjgfDqfm7amTPODPMvjkIhh1.jpg' WHERE id = 18;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/57MFWGHarg9jid7yfDTka4RmcMU.jpg' WHERE id = 19;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/k3waqVXSnvCZWfJYNtdamTgTtTA.jpg' WHERE id = 20;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/uDCeELWWpsNq7ErM61Yuq70WAE9.jpg' WHERE id = 21;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/VHSzNBTwxV8vh7wylo7O9CLdac.jpg' WHERE id = 22;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/qhb1qOilapbapxWQn9jtRCMwXJF.jpg' WHERE id = 23;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/siduVKgOnABO4WH4lOwPQwaGwJp.jpg' WHERE id = 28;
UPDATE movies SET movie_key = 'monkey-man', poster_url = 'https://image.tmdb.org/t/p/w500/4lhR4L2vzzjl68P1zJyCH755Oz4.jpg' WHERE id = 29;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/sh7Rg8Er3tFcN9BpKIPOMvALgZd.jpg' WHERE id = 30;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/H6vke7zGiuLsz4v4RPeReb9rsv.jpg' WHERE id = 31;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/hS4GYkYpN1rfl4GIxyc02sCyfAj.jpg' WHERE id = 32;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/vjyrLHDKNWBUFQitdUemYfMrr8T.jpg' WHERE id = 33;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/oil3EZwKFp3CWxZnfGfGglesvm9.jpg' WHERE id = 34;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/1EwNyiiNFd863H4e8nWEzutnZD7.jpg' WHERE id = 35;
UPDATE movies SET movie_key = 'twisters', poster_url = 'https://image.tmdb.org/t/p/w500/pjnD08FlMAIXsfOLKQbvmO0f0MD.jpg' WHERE id = 38;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/dA4N6uWOnEMgbxXwFX7qX7adzs8.jpg' WHERE id = 39;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/iRCgqpdVE4wyLQvGYU3ZP7pAtUc.jpg' WHERE id = 40;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/xDGbZ0JJ3mYaGKy4Nzd9Kph6M9L.jpg' WHERE id = 41;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/m5x8D0bZ3eKqIVWZ5y7TnZ2oTVg.jpg' WHERE id = 44;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/cgXk2tNYhJZLXdBDO5DidAVzQ82.jpg' WHERE id = 46;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/5qGIxdEO841C0tdY8vOdLoRVrr0.jpg' WHERE id = 47;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/jbOSUAWMGzGL1L4EaUF8K6zYFo7.jpg' WHERE id = 48;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/llWl3GtNoXosbvYboelmoT459NM.jpg' WHERE id = 50;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/ilwO6elz3mLV9CToT7C8pjVeKX0.jpg' WHERE id = 51;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/lu2vmmtStmTNMmSZl2LgrrQpLZo.jpg' WHERE id = 52;
UPDATE movies SET movie_key = 'september-5', poster_url = 'https://image.tmdb.org/t/p/w500/3kcQOLwYKGPwyjiynFsvP8vHvRn.jpg' WHERE id = 53;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/oLxWocqheC8XbXbxqJ3x422j9PW.jpg' WHERE id = 55;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/jXJxMcVoEuXzym3vFnjqDW4ifo6.jpg' WHERE id = 56;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/fWPgbnt2LSqkQ6cdQc0SZN9CpLm.jpg' WHERE id = 57;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/hqcexYHbiTBfDIdDWxrxPtVndBX.jpg' WHERE id = 58;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/xmMHGz9dVRaMY6rRAlEX4W0Wdhm.jpg' WHERE id = 59;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/l39TlELomwysfsr37vrvCV6rmaQ.jpg' WHERE id = 60;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/edKpE9B5qN3e559OuMCLZdW1iBZ.jpg' WHERE id = 61;
UPDATE movies SET poster_url = 'https://image.tmdb.org/t/p/w500/1RICxzeoNCAO5NpcRMIgg1XT6fm.jpg' WHERE id = 63;

