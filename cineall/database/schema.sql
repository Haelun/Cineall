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
-- Test accounts (password for all three is: password123)
--   user@cineall.com     -> regular user
--   admin@cineall.com    -> admin     (2FA on login)
--   curator@cineall.com  -> curator   (2FA on login)
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
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `two_factor_codes`;
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

CREATE TABLE `two_factor_codes` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `code`       VARCHAR(10) NOT NULL,
  `temp_token` VARCHAR(255) NOT NULL UNIQUE,
  `verified`   TINYINT(1) NOT NULL DEFAULT 0,
  `expires_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_temp_token` (`temp_token`),
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
-- ANALYTICS  (admin dashboard — one row per day)
-- ============================================================================
CREATE TABLE `analytics` (
  `id`            INT NOT NULL AUTO_INCREMENT,
  `date`          DATE NOT NULL UNIQUE,
  `visits`        INT DEFAULT 0,
  `searches`      INT DEFAULT 0,
  `clickthroughs` INT DEFAULT 0,
  `signups`       INT DEFAULT 0,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
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
