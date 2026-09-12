-- Al Omran Blog CMS Database Schema
-- MySQL 8+ / utf8mb4

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','editor') NOT NULL DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Articles
CREATE TABLE IF NOT EXISTS articles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    author_id INT UNSIGNED NOT NULL,
    featured_image_id INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Article Translations
CREATE TABLE IF NOT EXISTS article_translations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id INT UNSIGNED NOT NULL,
    language ENUM('en','ar') NOT NULL DEFAULT 'en',
    title VARCHAR(255) NOT NULL DEFAULT '',
    slug VARCHAR(255) NOT NULL DEFAULT '',
    excerpt TEXT,
    content LONGTEXT,
    seo_title VARCHAR(70) DEFAULT NULL,
    meta_description VARCHAR(170) DEFAULT NULL,
    canonical_url VARCHAR(500) DEFAULT NULL,
    index_status ENUM('index','noindex') DEFAULT 'index',
    follow_status ENUM('follow','nofollow') DEFAULT 'follow',
    focus_keyword VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_article_lang (article_id, language),
    UNIQUE KEY unique_slug_lang (slug, language),
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEO Metadata (extended per-article SEO data)
CREATE TABLE IF NOT EXISTS seo_metadata (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id INT UNSIGNED NOT NULL,
    language ENUM('en','ar') NOT NULL DEFAULT 'en',
    seo_title VARCHAR(70) DEFAULT NULL,
    meta_description VARCHAR(170) DEFAULT NULL,
    focus_keyword VARCHAR(255) DEFAULT NULL,
    canonical_url VARCHAR(500) DEFAULT NULL,
    index_status ENUM('index','noindex') DEFAULT 'index',
    follow_status ENUM('follow','nofollow') DEFAULT 'follow',
    og_title VARCHAR(255) DEFAULT NULL,
    og_description VARCHAR(300) DEFAULT NULL,
    og_image VARCHAR(500) DEFAULT NULL,
    twitter_title VARCHAR(255) DEFAULT NULL,
    twitter_description VARCHAR(300) DEFAULT NULL,
    twitter_image VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_seo_lang (article_id, language),
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Keywords
CREATE TABLE IF NOT EXISTS keywords (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id INT UNSIGNED NOT NULL,
    language ENUM('en','ar') NOT NULL DEFAULT 'en',
    keyword VARCHAR(255) NOT NULL,
    type ENUM('primary','secondary','related') NOT NULL DEFAULT 'secondary',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    INDEX idx_article_lang (article_id, language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Media
CREATE TABLE IF NOT EXISTS media (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size INT UNSIGNED NOT NULL DEFAULT 0,
    width INT UNSIGNED DEFAULT NULL,
    height INT UNSIGNED DEFAULT NULL,
    alt_text_en VARCHAR(255) DEFAULT NULL,
    alt_text_ar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mime (mime_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Slug History
CREATE TABLE IF NOT EXISTS slug_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id INT UNSIGNED NOT NULL,
    language ENUM('en','ar') NOT NULL,
    old_slug VARCHAR(255) NOT NULL,
    new_slug VARCHAR(255) NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    INDEX idx_slug_lang (old_slug, language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Redirects
CREATE TABLE IF NOT EXISTS redirects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    language ENUM('en','ar') NOT NULL,
    old_path VARCHAR(500) NOT NULL,
    new_path VARCHAR(500) NOT NULL,
    status_code INT NOT NULL DEFAULT 301,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_old_path (old_path(191), language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Careers
CREATE TABLE IF NOT EXISTS careers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    status ENUM('draft','published','closed') NOT NULL DEFAULT 'draft',
    author_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Career Translations
CREATE TABLE IF NOT EXISTS career_translations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    career_id INT UNSIGNED NOT NULL,
    language ENUM('en','ar') NOT NULL DEFAULT 'en',
    title VARCHAR(255) NOT NULL DEFAULT '',
    slug VARCHAR(255) NOT NULL DEFAULT '',
    location VARCHAR(255) DEFAULT NULL,
    employment_type ENUM('full-time','part-time','contract','internship') DEFAULT 'full-time',
    description LONGTEXT,
    requirements LONGTEXT,
    benefits LONGTEXT,
    seo_title VARCHAR(70) DEFAULT NULL,
    meta_description VARCHAR(170) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_career_lang (career_id, language),
    UNIQUE KEY unique_career_slug_lang (slug, language),
    FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings
CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics Events
CREATE TABLE IF NOT EXISTS analytics_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id INT UNSIGNED DEFAULT NULL,
    career_id INT UNSIGNED DEFAULT NULL,
    event_type VARCHAR(50) NOT NULL DEFAULT 'view',
    language ENUM('en','ar') DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    referrer VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_article (article_id),
    INDEX idx_event_type (event_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
