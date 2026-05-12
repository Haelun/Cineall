-- ============================================================================
-- CineAll Database Schema
-- ============================================================================

CREATE DATABASE IF NOT EXISTS cineall CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cineall;

-- ============================================================================
-- Platforms Table
-- ============================================================================
CREATE TABLE platforms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform_key VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    hue INT NOT NULL,
    abbr VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================================
-- Genres Table
-- ============================================================================
CREATE TABLE genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================================
-- Movies Table
-- ============================================================================
CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_key VARCHAR(50) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    year INT NOT NULL,
    runtime INT NOT NULL,
    rating VARCHAR(10) NOT NULL,
    director VARCHAR(255) NOT NULL,
    critic_score INT NOT NULL,
    audience_score INT NOT NULL,
    synopsis TEXT NOT NULL,
    tagline VARCHAR(255),
    scheme_color_1 VARCHAR(50) NOT NULL,
    scheme_color_2 VARCHAR(50) NOT NULL,
    accent_color VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_year (year),
    INDEX idx_critic_score (critic_score),
    INDEX idx_title (title)
) ENGINE=InnoDB;

-- ============================================================================
-- Cast Table
-- ============================================================================
CREATE TABLE cast_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    INDEX idx_movie (movie_id)
) ENGINE=InnoDB;

-- ============================================================================
-- Movie-Genre Relationship Table
-- ============================================================================
CREATE TABLE movie_genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    genre_id INT NOT NULL,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE,
    UNIQUE KEY unique_movie_genre (movie_id, genre_id)
) ENGINE=InnoDB;

-- ============================================================================
-- Availability Table
-- ============================================================================
CREATE TABLE availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    platform_id INT NOT NULL,
    kind ENUM('subscription', 'rent', 'buy') NOT NULL,
    price_from DECIMAL(10,2) NULL,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (platform_id) REFERENCES platforms(id) ON DELETE CASCADE,
    INDEX idx_movie (movie_id),
    INDEX idx_platform (platform_id)
) ENGINE=InnoDB;

-- ============================================================================
-- Users Table
-- ============================================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================================
-- User Subscriptions Table
-- ============================================================================
CREATE TABLE user_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    platform_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (platform_id) REFERENCES platforms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_platform (user_id, platform_id)
) ENGINE=InnoDB;

-- ============================================================================
-- Watchlist Table
-- ============================================================================
CREATE TABLE watchlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_movie (user_id, movie_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- ============================================================================
-- User Preferences Table
-- ============================================================================
CREATE TABLE user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    notify_leaving BOOLEAN DEFAULT TRUE,
    email_digest BOOLEAN DEFAULT TRUE,
    critic_score_first BOOLEAN DEFAULT FALSE,
    hide_watched BOOLEAN DEFAULT TRUE,
    surface_festival BOOLEAN DEFAULT FALSE,
    use_audience_score BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================================
-- Home Rows Configuration Table
-- ============================================================================
CREATE TABLE home_rows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    row_key VARCHAR(50) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    kicker VARCHAR(255) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================================
-- Home Row Movies Table
-- ============================================================================
CREATE TABLE home_row_movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    row_id INT NOT NULL,
    movie_id INT NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (row_id) REFERENCES home_rows(id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    INDEX idx_row (row_id)
) ENGINE=InnoDB;
