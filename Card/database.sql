-- AstitvaHub fresh-install database schema
-- Admin seed: admin@astitvahub.local / Admin@12345

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE DATABASE IF NOT EXISTS astitvahub
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE astitvahub;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS admin_actions;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS subscriptions;
DROP TABLE IF EXISTS qr_codes;
DROP TABLE IF EXISTS contact_downloads;
DROP TABLE IF EXISTS profile_views;
DROP TABLE IF EXISTS analytics;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS testimonials;
DROP TABLE IF EXISTS business_hours;
DROP TABLE IF EXISTS gallery_images;
DROP TABLE IF EXISTS videos;
DROP TABLE IF EXISTS profile_documents;
DROP TABLE IF EXISTS portfolios;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS certificates;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS education;
DROP TABLE IF EXISTS skills;
DROP TABLE IF EXISTS social_links;
DROP TABLE IF EXISTS profile_fields;
DROP TABLE IF EXISTS profiles;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS remember_tokens;
DROP TABLE IF EXISTS email_verifications;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS themes;
DROP TABLE IF EXISTS plans;
DROP TABLE IF EXISTS category_fields;
DROP TABLE IF EXISTS categories;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(80) NOT NULL,
    description VARCHAR(255) NULL,
    icon_class VARCHAR(80) NULL,
    fields_json JSON NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY categories_slug_unique (slug),
    KEY categories_active_sort_index (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE category_fields (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    section VARCHAR(100) NOT NULL DEFAULT 'Profile',
    field_key VARCHAR(100) NOT NULL,
    label VARCHAR(140) NOT NULL,
    field_type ENUM('text','textarea','email','tel','url','number','date','time','select','multiselect','file','image') NOT NULL DEFAULT 'text',
    placeholder VARCHAR(190) NULL,
    options_json JSON NULL,
    validation_json JSON NULL,
    help_text VARCHAR(255) NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 0,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT category_fields_category_fk
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY category_fields_category_key_unique (category_id, field_key),
    KEY category_fields_sort_index (category_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    currency CHAR(3) NOT NULL DEFAULT 'INR',
    billing_type ENUM('free','lifetime','monthly','yearly') NOT NULL DEFAULT 'lifetime',
    features_json JSON NULL,
    max_links INT UNSIGNED NULL,
    max_gallery_items INT UNSIGNED NULL,
    analytics_enabled TINYINT(1) NOT NULL DEFAULT 0,
    custom_themes_enabled TINYINT(1) NOT NULL DEFAULT 0,
    remove_branding_enabled TINYINT(1) NOT NULL DEFAULT 0,
    custom_domain_enabled TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY plans_slug_unique (slug),
    KEY plans_active_sort_index (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE themes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    accent_color VARCHAR(20) NOT NULL DEFAULT '#7c3aed',
    secondary_color VARCHAR(20) NOT NULL DEFAULT '#06b6d4',
    background_style VARCHAR(80) NOT NULL DEFAULT 'aurora',
    custom_css TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY themes_slug_unique (slug),
    KEY themes_active_sort_index (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(120) NOT NULL,
    setting_value TEXT NULL,
    setting_type ENUM('text','textarea','email','url','number','boolean','json','password') NOT NULL DEFAULT 'text',
    is_public TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY settings_key_unique (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(40) NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    status ENUM('pending','active','banned','deleted') NOT NULL DEFAULT 'pending',
    email_verified_at DATETIME NULL,
    last_login_at DATETIME NULL,
    last_login_ip VARCHAR(45) NULL,
    remember_token_version INT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    CONSTRAINT users_category_fk
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    UNIQUE KEY users_email_unique (email),
    KEY users_role_status_index (role, status),
    KEY users_category_index (category_id),
    KEY users_created_index (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(190) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    requested_ip VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT password_resets_user_fk
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY password_resets_token_unique (token_hash),
    KEY password_resets_email_index (email),
    KEY password_resets_expires_index (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE email_verifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(190) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT email_verifications_user_fk
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY email_verifications_token_unique (token_hash),
    KEY email_verifications_user_index (user_id),
    KEY email_verifications_expires_index (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE remember_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    selector CHAR(24) NOT NULL,
    validator_hash CHAR(64) NOT NULL,
    user_agent_hash CHAR(64) NULL,
    ip_hash CHAR(64) NULL,
    expires_at DATETIME NOT NULL,
    last_used_at DATETIME NULL,
    revoked_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT remember_tokens_user_fk
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY remember_tokens_selector_unique (selector),
    KEY remember_tokens_user_index (user_id),
    KEY remember_tokens_expires_index (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    last_attempt_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY login_attempts_email_ip_unique (email, ip_address),
    KEY login_attempts_locked_index (locked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    username VARCHAR(40) NOT NULL,
    display_name VARCHAR(140) NOT NULL,
    headline VARCHAR(190) NULL,
    bio TEXT NULL,
    profile_photo VARCHAR(255) NULL,
    cover_image VARCHAR(255) NULL,
    phone VARCHAR(40) NULL,
    public_email VARCHAR(190) NULL,
    website VARCHAR(255) NULL,
    company_name VARCHAR(160) NULL,
    address VARCHAR(255) NULL,
    whatsapp VARCHAR(40) NULL,
    booking_link VARCHAR(500) NULL,
    google_maps_embed TEXT NULL,
    resume_path VARCHAR(255) NULL,
    business_pdf_path VARCHAR(255) NULL,
    theme_slug VARCHAR(120) NOT NULL DEFAULT 'aurora',
    theme_color VARCHAR(20) NOT NULL DEFAULT '#7c3aed',
    dark_mode TINYINT(1) NOT NULL DEFAULT 1,
    remove_branding TINYINT(1) NOT NULL DEFAULT 0,
    custom_domain VARCHAR(255) NULL,
    seo_title VARCHAR(190) NULL,
    seo_description VARCHAR(255) NULL,
    seo_keywords VARCHAR(255) NULL,
    og_image VARCHAR(255) NULL,
    meta_json JSON NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT profiles_user_fk
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT profiles_category_fk
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    UNIQUE KEY profiles_user_unique (user_id),
    UNIQUE KEY profiles_username_unique (username),
    UNIQUE KEY profiles_custom_domain_unique (custom_domain),
    KEY profiles_category_index (category_id),
    KEY profiles_published_index (is_published),
    KEY profiles_created_index (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE profile_fields (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    category_field_id BIGINT UNSIGNED NULL,
    field_key VARCHAR(100) NOT NULL,
    field_value LONGTEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT profile_fields_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    CONSTRAINT profile_fields_category_field_fk
        FOREIGN KEY (category_field_id) REFERENCES category_fields(id) ON DELETE SET NULL,
    UNIQUE KEY profile_fields_profile_key_unique (profile_id, field_key),
    KEY profile_fields_category_field_index (category_field_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE social_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    platform VARCHAR(80) NOT NULL,
    label VARCHAR(120) NOT NULL,
    url VARCHAR(500) NOT NULL,
    icon_class VARCHAR(80) NULL,
    click_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT social_links_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    KEY social_links_profile_sort_index (profile_id, is_active, sort_order),
    KEY social_links_platform_index (platform)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE skills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    level TINYINT UNSIGNED NULL,
    category VARCHAR(80) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT skills_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    KEY skills_profile_sort_index (profile_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE education (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    institution VARCHAR(180) NOT NULL,
    university VARCHAR(180) NULL,
    degree VARCHAR(180) NULL,
    course VARCHAR(180) NULL,
    start_year VARCHAR(20) NULL,
    end_year VARCHAR(20) NULL,
    grade VARCHAR(80) NULL,
    description TEXT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT education_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    KEY education_profile_sort_index (profile_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    role VARCHAR(120) NULL,
    description TEXT NULL,
    url VARCHAR(500) NULL,
    image_path VARCHAR(255) NULL,
    started_at DATE NULL,
    ended_at DATE NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT projects_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    KEY projects_profile_sort_index (profile_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE certificates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    issuer VARCHAR(180) NULL,
    credential_id VARCHAR(190) NULL,
    credential_url VARCHAR(500) NULL,
    file_path VARCHAR(255) NULL,
    issued_on DATE NULL,
    expires_on DATE NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT certificates_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    KEY certificates_profile_sort_index (profile_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    price_label VARCHAR(80) NULL,
    duration_label VARCHAR(80) NULL,
    cta_label VARCHAR(80) NULL,
    cta_url VARCHAR(500) NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT services_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    KEY services_profile_sort_index (profile_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NULL,
    currency CHAR(3) NOT NULL DEFAULT 'INR',
    image_path VARCHAR(255) NULL,
    product_url VARCHAR(500) NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT products_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    KEY products_profile_sort_index (profile_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE portfolios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    client_name VARCHAR(160) NULL,
    description TEXT NULL,
    url VARCHAR(500) NULL,
    image_path VARCHAR(255) NULL,
    tags VARCHAR(255) NULL,
    completed_on DATE NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT portfolios_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    KEY portfolios_profile_sort_index (profile_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE profile_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    document_type ENUM('resume','business_pdf','catalog','brochure','certificate','other') NOT NULL DEFAULT 'other',
    title VARCHAR(180) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(190) NULL,
    mime_type VARCHAR(120) NOT NULL DEFAULT 'application/pdf',
    file_size BIGINT UNSIGNED NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT profile_documents_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    KEY profile_documents_profile_type_index (profile_id, document_type, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE videos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    url VARCHAR(500) NOT NULL,
    embed_url VARCHAR(500) NULL,
    thumbnail_path VARCHAR(255) NULL,
    platform VARCHAR(80) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT videos_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    KEY videos_profile_sort_index (profile_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE gallery_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption VARCHAR(180) NULL,
    alt_text VARCHAR(180) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT gallery_images_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    KEY gallery_images_profile_sort_index (profile_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE business_hours (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    day_of_week TINYINT UNSIGNED NOT NULL,
    opens_at TIME NULL,
    closes_at TIME NULL,
    is_closed TINYINT(1) NOT NULL DEFAULT 0,
    note VARCHAR(160) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT business_hours_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    UNIQUE KEY business_hours_profile_day_unique (profile_id, day_of_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE testimonials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    author_name VARCHAR(160) NOT NULL,
    author_title VARCHAR(160) NULL,
    company VARCHAR(160) NULL,
    quote TEXT NOT NULL,
    rating TINYINT UNSIGNED NULL,
    avatar_path VARCHAR(255) NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT testimonials_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    KEY testimonials_profile_sort_index (profile_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    reviewer_name VARCHAR(160) NOT NULL,
    reviewer_email VARCHAR(190) NULL,
    rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
    review_text TEXT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT reviews_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    KEY reviews_profile_status_index (profile_id, status),
    KEY reviews_created_index (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE analytics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    event_type ENUM('profile_view','unique_view','qr_scan','contact_download','whatsapp_click','social_click','share_click','link_click','login','signup','payment','custom') NOT NULL,
    event_label VARCHAR(160) NULL,
    source VARCHAR(80) NULL,
    platform VARCHAR(80) NULL,
    target_type VARCHAR(80) NULL,
    target_id BIGINT UNSIGNED NULL,
    target_url VARCHAR(500) NULL,
    ip_address VARCHAR(45) NULL,
    ip_hash CHAR(64) NULL,
    visitor_hash CHAR(64) NULL,
    session_key VARCHAR(128) NULL,
    user_agent VARCHAR(500) NULL,
    device_type VARCHAR(60) NULL,
    browser VARCHAR(120) NULL,
    os VARCHAR(120) NULL,
    referrer VARCHAR(500) NULL,
    country VARCHAR(120) NULL,
    region VARCHAR(120) NULL,
    city VARCHAR(120) NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    metadata_json JSON NULL,
    occurred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT analytics_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    CONSTRAINT analytics_user_fk
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    KEY analytics_profile_event_index (profile_id, event_type, occurred_at),
    KEY analytics_user_event_index (user_id, event_type, occurred_at),
    KEY analytics_occurred_index (occurred_at),
    KEY analytics_source_index (source),
    KEY analytics_visitor_index (profile_id, visitor_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE profile_views (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    source ENUM('direct','qr','social','search','referral','admin','unknown') NOT NULL DEFAULT 'direct',
    view_date DATE NOT NULL,
    ip_address VARCHAR(45) NULL,
    ip_hash CHAR(64) NULL,
    visitor_hash CHAR(64) NULL,
    session_key VARCHAR(128) NULL,
    user_agent VARCHAR(500) NULL,
    device_type VARCHAR(60) NULL,
    browser VARCHAR(120) NULL,
    os VARCHAR(120) NULL,
    referrer VARCHAR(500) NULL,
    country VARCHAR(120) NULL,
    region VARCHAR(120) NULL,
    city VARCHAR(120) NULL,
    is_unique TINYINT(1) NOT NULL DEFAULT 0,
    viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT profile_views_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    KEY profile_views_profile_date_index (profile_id, view_date),
    KEY profile_views_source_index (profile_id, source, viewed_at),
    KEY profile_views_visitor_index (profile_id, visitor_hash),
    KEY profile_views_viewed_index (viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_downloads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    source VARCHAR(80) NULL,
    ip_address VARCHAR(45) NULL,
    ip_hash CHAR(64) NULL,
    visitor_hash CHAR(64) NULL,
    session_key VARCHAR(128) NULL,
    user_agent VARCHAR(500) NULL,
    device_type VARCHAR(60) NULL,
    browser VARCHAR(120) NULL,
    os VARCHAR(120) NULL,
    referrer VARCHAR(500) NULL,
    country VARCHAR(120) NULL,
    city VARCHAR(120) NULL,
    downloaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT contact_downloads_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    KEY contact_downloads_profile_date_index (profile_id, downloaded_at),
    KEY contact_downloads_visitor_index (profile_id, visitor_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE qr_codes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id BIGINT UNSIGNED NOT NULL,
    qr_url VARCHAR(500) NOT NULL,
    image_path VARCHAR(255) NULL,
    format ENUM('png','svg') NOT NULL DEFAULT 'png',
    scan_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
    last_generated_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT qr_codes_profile_fk
        FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    UNIQUE KEY qr_codes_profile_unique (profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,
    status ENUM('active','expired','cancelled','pending') NOT NULL DEFAULT 'active',
    starts_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ends_at DATETIME NULL,
    is_lifetime TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT subscriptions_user_fk
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT subscriptions_plan_fk
        FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE RESTRICT,
    KEY subscriptions_user_status_index (user_id, status),
    KEY subscriptions_plan_index (plan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,
    plan_name VARCHAR(120) NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    currency CHAR(3) NOT NULL DEFAULT 'INR',
    status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    gateway VARCHAR(80) NULL,
    transaction_id VARCHAR(190) NULL,
    payer_email VARCHAR(190) NULL,
    payload_json JSON NULL,
    payment_date DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT payments_user_fk
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT payments_plan_fk
        FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE RESTRICT,
    UNIQUE KEY payments_transaction_unique (transaction_id),
    KEY payments_user_status_index (user_id, status),
    KEY payments_plan_index (plan_id),
    KEY payments_date_index (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    title VARCHAR(160) NOT NULL,
    body TEXT NULL,
    type ENUM('info','success','warning','danger','system') NOT NULL DEFAULT 'info',
    action_url VARCHAR(500) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT notifications_user_fk
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY notifications_user_read_index (user_id, is_read, created_at),
    KEY notifications_created_index (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_actions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_user_id BIGINT UNSIGNED NULL,
    target_user_id BIGINT UNSIGNED NULL,
    action VARCHAR(120) NOT NULL,
    entity_type VARCHAR(80) NULL,
    entity_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    metadata_json JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT admin_actions_admin_fk
        FOREIGN KEY (admin_user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT admin_actions_target_user_fk
        FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL,
    KEY admin_actions_admin_index (admin_user_id, created_at),
    KEY admin_actions_target_index (target_user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (id, name, slug, description, icon_class, fields_json, sort_order) VALUES
(1, 'Student', 'student', 'College, university, course, skills, projects, certificates, resume, GitHub and LinkedIn.', 'fa-solid fa-graduation-cap',
 '[{"name":"college","label":"College","type":"text","section":"Academic"},{"name":"university","label":"University","type":"text","section":"Academic"},{"name":"course","label":"Course","type":"text","section":"Academic"},{"name":"skills_summary","label":"Skills","type":"textarea","section":"Skills"},{"name":"projects_summary","label":"Projects","type":"textarea","section":"Projects"},{"name":"certificates_summary","label":"Certificates","type":"textarea","section":"Credentials"},{"name":"resume_path","label":"Resume","type":"file","section":"Credentials"},{"name":"github","label":"GitHub","type":"url","section":"Links"},{"name":"linkedin","label":"LinkedIn","type":"url","section":"Links"}]', 10),
(2, 'Business', 'business', 'Business name, services, products, PDF catalog, WhatsApp, Google Maps, business hours, gallery and reviews.', 'fa-solid fa-store',
 '[{"name":"business_name","label":"Business Name","type":"text","section":"Business"},{"name":"services_summary","label":"Services","type":"textarea","section":"Business"},{"name":"products_summary","label":"Products","type":"textarea","section":"Business"},{"name":"business_pdf_path","label":"Business PDF / Catalog","type":"file","section":"Business"},{"name":"whatsapp","label":"WhatsApp","type":"tel","section":"Contact"},{"name":"google_maps_embed","label":"Google Maps Embed","type":"textarea","section":"Location"},{"name":"business_hours","label":"Business Hours","type":"textarea","section":"Location"},{"name":"gallery_intro","label":"Gallery","type":"textarea","section":"Media"},{"name":"reviews_intro","label":"Reviews","type":"textarea","section":"Trust"}]', 20),
(3, 'Freelancer', 'freelancer', 'Skills, pricing packages, portfolio, projects and testimonials.', 'fa-solid fa-briefcase',
 '[{"name":"skills_summary","label":"Skills","type":"textarea","section":"Expertise"},{"name":"pricing_packages","label":"Pricing / Packages","type":"textarea","section":"Offers"},{"name":"portfolio_summary","label":"Portfolio","type":"textarea","section":"Work"},{"name":"projects_summary","label":"Projects","type":"textarea","section":"Work"},{"name":"testimonials_summary","label":"Testimonials","type":"textarea","section":"Proof"}]', 30),
(4, 'Creator', 'creator', 'Instagram, YouTube, videos, gallery and donation links.', 'fa-solid fa-video',
 '[{"name":"instagram","label":"Instagram","type":"url","section":"Channels"},{"name":"youtube","label":"YouTube","type":"url","section":"Channels"},{"name":"videos_summary","label":"Videos","type":"textarea","section":"Content"},{"name":"gallery_intro","label":"Gallery","type":"textarea","section":"Media"},{"name":"donation_links","label":"Donation Links","type":"textarea","section":"Support"}]', 40),
(5, 'Professional', 'professional', 'Experience, qualifications, booking link and office address.', 'fa-solid fa-user-tie',
 '[{"name":"experience_summary","label":"Experience","type":"textarea","section":"Profile"},{"name":"qualifications","label":"Qualifications","type":"textarea","section":"Profile"},{"name":"booking_link","label":"Appointments / Booking Link","type":"url","section":"Contact"},{"name":"office_address","label":"Office Address","type":"textarea","section":"Contact"}]', 50),
(6, 'Job Seeker', 'job-seeker', 'Resume, skills, experience, education and portfolio.', 'fa-solid fa-file-signature',
 '[{"name":"resume_path","label":"Resume","type":"file","section":"Resume"},{"name":"skills_summary","label":"Skills","type":"textarea","section":"Skills"},{"name":"experience_summary","label":"Experience","type":"textarea","section":"Experience"},{"name":"education_summary","label":"Education","type":"textarea","section":"Education"},{"name":"portfolio_summary","label":"Portfolio","type":"textarea","section":"Portfolio"}]', 60);

INSERT INTO category_fields (category_id, section, field_key, label, field_type, placeholder, help_text, is_required, is_public, sort_order) VALUES
(1, 'Academic', 'college', 'College', 'text', 'Your college name', NULL, 0, 1, 10),
(1, 'Academic', 'university', 'University', 'text', 'Affiliated university', NULL, 0, 1, 20),
(1, 'Academic', 'course', 'Course', 'text', 'B.Tech CSE, B.Com, MBA...', NULL, 0, 1, 30),
(1, 'Skills', 'skills_summary', 'Skills', 'textarea', 'List your strongest skills', NULL, 0, 1, 40),
(1, 'Projects', 'projects_summary', 'Projects', 'textarea', 'Highlight academic or personal projects', NULL, 0, 1, 50),
(1, 'Credentials', 'certificates_summary', 'Certificates', 'textarea', 'Awards, workshops, certificates', NULL, 0, 1, 60),
(1, 'Credentials', 'resume_path', 'Resume', 'file', NULL, 'PDF, DOC or DOCX', 0, 1, 70),
(1, 'Links', 'github', 'GitHub', 'url', 'https://github.com/username', NULL, 0, 1, 80),
(1, 'Links', 'linkedin', 'LinkedIn', 'url', 'https://linkedin.com/in/username', NULL, 0, 1, 90),
(2, 'Business', 'business_name', 'Business Name', 'text', 'Your registered or public business name', NULL, 0, 1, 10),
(2, 'Business', 'services_summary', 'Services', 'textarea', 'What do you offer?', NULL, 0, 1, 20),
(2, 'Business', 'products_summary', 'Products', 'textarea', 'Featured products or catalog details', NULL, 0, 1, 30),
(2, 'Business', 'business_pdf_path', 'Business PDF / Catalog', 'file', NULL, 'Upload a PDF catalog, menu, company profile or brochure', 0, 1, 40),
(2, 'Contact', 'whatsapp', 'WhatsApp', 'tel', '+91...', NULL, 0, 1, 50),
(2, 'Location', 'google_maps_embed', 'Google Maps Embed', 'textarea', '<iframe ...></iframe>', NULL, 0, 1, 60),
(2, 'Location', 'business_hours', 'Business Hours', 'textarea', 'Mon-Fri 10 AM - 7 PM', NULL, 0, 1, 70),
(2, 'Media', 'gallery_intro', 'Gallery', 'textarea', 'Short intro for your gallery', NULL, 0, 1, 80),
(2, 'Trust', 'reviews_intro', 'Reviews', 'textarea', 'Short review section intro', NULL, 0, 1, 90),
(3, 'Expertise', 'skills_summary', 'Skills', 'textarea', 'Design, development, writing...', NULL, 0, 1, 10),
(3, 'Offers', 'pricing_packages', 'Pricing / Packages', 'textarea', 'Starter, Pro, Retainer...', NULL, 0, 1, 20),
(3, 'Work', 'portfolio_summary', 'Portfolio', 'textarea', 'Best work and case studies', NULL, 0, 1, 30),
(3, 'Work', 'projects_summary', 'Projects', 'textarea', 'Recent projects', NULL, 0, 1, 40),
(3, 'Proof', 'testimonials_summary', 'Testimonials', 'textarea', 'Client feedback highlights', NULL, 0, 1, 50),
(4, 'Channels', 'instagram', 'Instagram', 'url', 'https://instagram.com/username', NULL, 0, 1, 10),
(4, 'Channels', 'youtube', 'YouTube', 'url', 'https://youtube.com/@username', NULL, 0, 1, 20),
(4, 'Content', 'videos_summary', 'Videos', 'textarea', 'Featured videos and series', NULL, 0, 1, 30),
(4, 'Media', 'gallery_intro', 'Gallery', 'textarea', 'Describe your visual work', NULL, 0, 1, 40),
(4, 'Support', 'donation_links', 'Donation Links', 'textarea', 'UPI, Patreon, Buy Me a Coffee...', NULL, 0, 1, 50),
(5, 'Profile', 'experience_summary', 'Experience', 'textarea', 'Years, roles, achievements', NULL, 0, 1, 10),
(5, 'Profile', 'qualifications', 'Qualifications', 'textarea', 'Degrees, certifications, licenses', NULL, 0, 1, 20),
(5, 'Contact', 'booking_link', 'Appointments / Booking Link', 'url', 'https://calendly.com/yourname', NULL, 0, 1, 30),
(5, 'Contact', 'office_address', 'Office Address', 'textarea', 'Clinic, office or consultation address', NULL, 0, 1, 40),
(6, 'Resume', 'resume_path', 'Resume', 'file', NULL, 'PDF, DOC or DOCX', 0, 1, 10),
(6, 'Skills', 'skills_summary', 'Skills', 'textarea', 'Tools, technologies and strengths', NULL, 0, 1, 20),
(6, 'Experience', 'experience_summary', 'Experience', 'textarea', 'Roles, internships and achievements', NULL, 0, 1, 30),
(6, 'Education', 'education_summary', 'Education', 'textarea', 'Degrees, institutions and grades', NULL, 0, 1, 40),
(6, 'Portfolio', 'portfolio_summary', 'Portfolio', 'textarea', 'Work samples and project links', NULL, 0, 1, 50);

INSERT INTO plans (id, name, slug, price, currency, billing_type, features_json, max_links, max_gallery_items, analytics_enabled, custom_themes_enabled, remove_branding_enabled, custom_domain_enabled, is_active, sort_order) VALUES
(1, 'Free', 'free', 0.00, 'INR', 'free', '["Basic Profile","QR Code","Save Contact","1 Theme","Limited Links"]', 5, 3, 0, 0, 0, 0, 1, 10),
(2, 'Smart', 'smart', 299.00, 'INR', 'lifetime', '["Unlimited Links","Gallery","Analytics","Custom Themes","Portfolio","Remove Branding"]', NULL, 25, 1, 1, 1, 0, 1, 20),
(3, 'Premium', 'premium', 999.00, 'INR', 'lifetime', '["Everything Unlimited","Portfolio","Products","Resume","Priority Support","Custom Domain"]', NULL, NULL, 1, 1, 1, 1, 1, 30);

INSERT INTO themes (id, name, slug, accent_color, secondary_color, background_style, sort_order) VALUES
(1, 'Aurora', 'aurora', '#7c3aed', '#06b6d4', 'aurora', 10),
(2, 'Graphite', 'graphite', '#64748b', '#14b8a6', 'mesh', 20),
(3, 'Emerald', 'emerald', '#10b981', '#38bdf8', 'aurora', 30),
(4, 'Rose', 'rose', '#f43f5e', '#f59e0b', 'mesh', 40),
(5, 'Indigo', 'indigo', '#6366f1', '#22d3ee', 'linear', 50);

INSERT INTO settings (setting_key, setting_value, setting_type, is_public) VALUES
('site_name', 'AstitvaHub', 'text', 1),
('site_tagline', 'One Link. Your Complete Digital Identity.', 'text', 1),
('site_description', 'AstitvaHub is a Universal Digital Identity Platform for profiles, QR codes, contact cards and analytics.', 'textarea', 1),
('site_url', 'https://astitvahub.com', 'url', 1),
('contact_email', 'GRAPHIETYOFFICIALL@GMAIL.COM', 'email', 1),
('contact_phones', '9619448959,9619448955,9076461179', 'text', 1),
('default_currency', 'INR', 'text', 0),
('payment_gateway', 'cashfree', 'text', 0),
('payment_key_id', '', 'password', 0),
('payment_key_secret', '', 'password', 0),
('smtp_host', '', 'text', 0),
('smtp_port', '587', 'number', 0),
('smtp_username', '', 'text', 0),
('smtp_password', '', 'password', 0);

INSERT INTO users (id, category_id, name, email, phone, password_hash, role, status, email_verified_at, created_at, updated_at) VALUES
(1, NULL, 'AstitvaHub Admin', 'admin@astitvahub.local', NULL, '$2y$10$P4rnGKBK/MFoRIeuhOiEzuiVSYN.g6CecqIfiUJ4MoTluImeunYgG', 'admin', 'active', NOW(), NOW(), NOW());

INSERT INTO subscriptions (user_id, plan_id, status, starts_at, is_lifetime) VALUES
(1, 3, 'active', NOW(), 1);
